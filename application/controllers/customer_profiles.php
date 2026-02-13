<?php
class Customer_profiles extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"10";

		// set data array
		$data 															= array();

		// manage session
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);

		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$data['title']										=	$this->lang->line('common_undelete');
			break;

			default:
					$data['title']										=	'';
			break;
		}

		// set up the pagination
		$config															=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 											= 	site_url('/customer_profiles/index');
		$config['total_rows'] 											= 	$this->Customer_profile->count_all();
		$this															->	pagination->initialize($config);

		// setup output data
		$data['links']													=	$this->pagination->create_links();
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['profile_data']											=	$this->Customer_profile->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) );

		$this->load->view('customer_profiles/manage',$data);
	}

	function search()
	{
		$search		= $this->input->post('search');
		$profiles	= $this->Customer_profile->search($search);
		$html		= '';

		foreach ($profiles->result() as $p)
		{
			$fidelity_badge = ($p->profile_fidelity === 'Y')
				? '<span style="background:#dcfce7;color:#166534;border:1px solid #22c55e;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">Oui</span>'
				: '<span style="background:#fef2f2;color:#991b1b;border:1px solid #ef4444;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">Non</span>';

			$html .= '<tr class="profile-row" data-href="'.site_url('customer_profiles/view/'.$p->profile_id).'" style="cursor:pointer;">';
			$html .= '<td>'.htmlspecialchars($p->profile_name).'</td>';
			$html .= '<td>'.htmlspecialchars($p->profile_description).'</td>';
			$html .= '<td style="text-align:right;">'.htmlspecialchars($p->profile_discount).'%</td>';
			$html .= '<td style="text-align:center;">'.$fidelity_badge.'</td>';
			$html .= '<td style="text-align:center;white-space:nowrap;">';
			$html .= '<a href="#" onclick="if(confirm(\'Supprimer ce profil client ?\')){window.location=\''.site_url('customer_profiles/delete/'.$p->profile_id).'\';} return false;" title="Supprimer" style="text-decoration:none;">';
			$html .= '<svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
			$html .= '</a></td>';
			$html .= '</tr>';
		}

		if ($profiles->num_rows() == 0)
		{
			$html .= '<tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b;">'.$this->lang->line('common_no_persons_to_display').'</td></tr>';
		}

		echo $html;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													= $this->Customer_profile->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($profile_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$profile_id;

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
			break;

			default:
					$_SESSION['origin']									=	$origin;
			break;
		}

		// set data
		switch ($profile_id)
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;

			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Customer_profile->get_info($_SESSION['transaction_id']);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->profile_name;
						break;

						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->profile_name;
						break;
					}
					unset($_SESSION['new']);
			break;
		}

		$this->load->view('customer_profiles/form');
	}

	function save()
	{
		// save orignal data but only first time through
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_profile_name']);
			$_SESSION['original_profile_name']							=	$_SESSION['transaction_info']->profile_name;
			$_SESSION['first_time']										=	1;
		}

		// load input data
		$_SESSION['transaction_info']->profile_name						=	$this->input->post('profile_name');
		$_SESSION['transaction_info']->profile_description				=	$this->input->post('profile_description');
		$_SESSION['transaction_info']->profile_discount					=	$this->input->post('profile_discount');
		$_SESSION['transaction_info']->profile_fidelity					=	$this->input->post('profile_fidelity');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');

		// do data verifications
		$this															->	verify();

		// if here then all checks succeeded so do the update
		$this															->	Customer_profile->save();

		// load pick list
		$this															->	Customer_profile->load_pick_list();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					$_SESSION['error_code']								=	'05320';
			break;

			default:
					$_SESSION['error_code']								=	'05330';
			break;
		}

		unset($_SESSION['new']);
		unset($_SESSION['first_time']);
		redirect("customer_profiles");
	}

	function delete($profile_id)
	{
		if ($this->Customer_profile->delete($profile_id))
		{
			$_SESSION['error_code']										=	'01655';
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}

		$this->Customer_profile->load_pick_list();
		redirect("customer_profiles");
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->profile_name)
			OR 	empty($_SESSION['transaction_info']->profile_description)
			OR 	empty($_SESSION['transaction_info']->profile_fidelity)
			)
		{
			$_SESSION['error_code']										=	'00030';
			redirect("customer_profiles/view/" . $_SESSION['transaction_id']);
		}

		// check profile code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->profile_name != $_SESSION['original_profile_name'])
		{
			$count														=	$this->Customer_profile->check_duplicate();
			if ($count > 0)
			{
				$_SESSION['error_code']									=	'05300';
				redirect("customer_profiles/view/" . $_SESSION['transaction_id']);
			}
		}

		// check discount is numeric if entered
		if (!empty($_SESSION['transaction_info']->profile_discount))
		{
			if (!is_numeric($_SESSION['transaction_info']->profile_discount))
			{
				$_SESSION['error_code']									=	'05310';
				redirect("customer_profiles/view/" . $_SESSION['transaction_id']);
			}
		}
	}
}
?>
