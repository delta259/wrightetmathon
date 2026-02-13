<?php
class countries extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"6";

		// set data array
		$data 															=	array();

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
		$config['base_url'] 											= 	site_url("/".$_SESSION['controller_name']."/index");
		$config['total_rows'] 											= 	$this->Country->count_all();
		$this															->	pagination->initialize($config);

		// setup output data
		$data['links']													=	$this->pagination->create_links();
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['country_data']											=	$this->Country->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) );

		$this															->	load->view('countries/manage',$data);
	}

	function search()
	{
		$search		= $this->input->post('search');
		$countries	= $this->Country->search($search);
		$html		= '';

		foreach ($countries->result() as $country)
		{
			$html .= '<tr class="country-row" data-href="'.site_url('countries/view/'.$country->country_id).'" style="cursor:pointer;">';
			$html .= '<td>'.htmlspecialchars($country->country_name).'</td>';
			$html .= '<td>'.htmlspecialchars($country->country_description).'</td>';
			$html .= '<td style="text-align:center;">'.htmlspecialchars($country->country_display_order).'</td>';
			$html .= '<td style="text-align:center;white-space:nowrap;">';
			$html .= '<a href="#" onclick="if(confirm(\'Supprimer ce pays ?\')){window.location=\''.site_url('countries/delete/'.$country->country_id).'\';} return false;" title="Supprimer" style="text-decoration:none;">';
			$html .= '<svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
			$html .= '</a></td>';
			$html .= '</tr>';
		}

		if ($countries->num_rows() == 0)
		{
			$html .= '<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b;">'.$this->lang->line('common_no_persons_to_display').'</td></tr>';
		}

		echo $html;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													=	$this->Country->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($country_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$country_id;

		// set up Country side dropdown
		$_SESSION['LorR_pick_list']										=	array('R'=>$this->lang->line('common_right'), 'L'=>$this->lang->line('common_left'));

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

		// manage session
		$_SESSION['show_dialog']										=	1;

		// set data
		switch ($country_id)
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;

			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Country->get_info($country_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->country_name;
						break;

						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->country_name;
						break;
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("countries");
	}

	function save()
	{
		// save orignal data but only first time through
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_country_name']);
			$_SESSION['original_country_name']							=	$_SESSION['transaction_info']->country_name;
			$_SESSION['first_time']										=	1;
		}

		// load input data
		$_SESSION['transaction_info']->country_name						=	$this->input->post('country_name');
		$_SESSION['transaction_info']->country_description				=	$this->input->post('country_description');
		$_SESSION['transaction_info']->country_display_order			=	$this->input->post('country_display_order');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');

		// do data verifications
		$this															->	verify();

		// if here then all checks succeeded so do the update
		$this															->	Country->save();

		// load pick list
		$this															->	Country->load_pick_list();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					$_SESSION['error_code']								=	'05380';
					$this												->	view($_SESSION['transaction_info']->country_id, $_SESSION['origin']);
			break;

			default:
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05390';
					$this												->	view($_SESSION['transaction_info']->country_id, $_SESSION['origin']);
			break;
		}
	}

	function delete($country_id)
	{
		if ($this->Country->delete($country_id))
		{
			$_SESSION['error_code']										=	'01655';
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}

		// reload pick list
		$this->Country->load_pick_list();
		redirect("countries");
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->country_name)
			OR 	empty($_SESSION['transaction_info']->country_description)
			OR 	empty($_SESSION['transaction_info']->country_display_order)
			)
		{
			$_SESSION['error_code']										=	'00030';
			$_SESSION['show_dialog']									=	1;
			redirect("countries");
		}

		// check Country code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->country_name != $_SESSION['original_country_name'])
		{
			$count														=	$this->Country->check_duplicate();
			if ($count > 0)
			{
				$_SESSION['error_code']									=	'05370';
				$_SESSION['show_dialog']								=	1;
				redirect("countries");
			}
		}

		// verify display order is numeric
		if (!is_numeric($_SESSION['transaction_info']->country_display_order))
		{
			$_SESSION['error_code']										=	'02030';
			$_SESSION['show_dialog']									=	1;
			redirect("countries");
		}
	}
}
?>
