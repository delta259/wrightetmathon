<?php
class timezones extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"26";

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
		$config['total_rows'] 											= 	$this->Timezone->count_all();
		$this															->	pagination->initialize($config);

		// setup output data
		$data['links']													=	$this->pagination->create_links();
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['timezone_data']											=	$this->Timezone->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) );

		$this															->	load->view('timezones/manage',$data);
	}

	function search()
	{
		$search		= $this->input->post('search');
		$timezones	= $this->Timezone->search($search);
		$html		= '';

		foreach ($timezones->result() as $tz)
		{
			$html .= '<tr class="tz-row" data-href="'.site_url('timezones/view/'.$tz->timezone_id).'" style="cursor:pointer;">';
			$html .= '<td>'.htmlspecialchars($tz->timezone_name).'</td>';
			$html .= '<td>'.htmlspecialchars($tz->timezone_description).'</td>';
			$html .= '<td style="text-align:center;"><span style="background:#eff6ff;color:#1e40af;border:1px solid #3b82f6;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">'.htmlspecialchars($tz->timezone_continent).'</span></td>';
			$html .= '<td>'.htmlspecialchars($tz->timezone_city).'</td>';
			$html .= '<td style="text-align:center;">'.htmlspecialchars($tz->timezone_offset).'</td>';
			$html .= '<td style="text-align:center;white-space:nowrap;">';
			$html .= '<a href="#" onclick="if(confirm(\'Supprimer ce fuseau horaire ?\')){window.location=\''.site_url('timezones/delete/'.$tz->timezone_id).'\';} return false;" title="Supprimer" style="text-decoration:none;">';
			$html .= '<svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
			$html .= '</a></td>';
			$html .= '</tr>';
		}

		if ($timezones->num_rows() == 0)
		{
			$html .= '<tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b;">'.$this->lang->line('common_no_persons_to_display').'</td></tr>';
		}

		echo $html;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													=	$this->Timezone->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($timezone_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$timezone_id;

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
		switch ($timezone_id)
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;

			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Timezone->get_info($timezone_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->timezone_name;
						break;

						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->timezone_name;
						break;
					}
					unset($_SESSION['new']);
			break;
		}

		$this->load->view('timezones/form');
	}

	function save()
	{
		// save orignal data but only first time through
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_timezone_name']);
			$_SESSION['original_timezone_name']							=	$_SESSION['transaction_info']->timezone_name;
			$_SESSION['first_time']										=	1;
		}

		// load input data
		$_SESSION['transaction_info']->timezone_name					=	$this->input->post('timezone_name');
		$_SESSION['transaction_info']->timezone_description				=	$this->input->post('timezone_description');
		$_SESSION['transaction_info']->timezone_continent				=	$this->input->post('timezone_continent');
		$_SESSION['transaction_info']->timezone_city					=	$this->input->post('timezone_city');
		$_SESSION['transaction_info']->timezone_offset					=	$this->input->post('timezone_offset');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');

		// do data verifications
		$this															->	verify();

		// if here then all checks succeeded so do the update
		$this															->	Timezone->save();

		// reload pick list
		$this															->	Timezone->load_pick_list();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					$_SESSION['error_code']								=	'05410';
			break;

			default:
					$_SESSION['error_code']								=	'05420';
			break;
		}

		unset($_SESSION['new']);
		unset($_SESSION['first_time']);
		redirect("timezones");
	}

	function delete($timezone_id)
	{
		if ($this->Timezone->delete($timezone_id))
		{
			$_SESSION['error_code']										=	'01655';
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}

		$this->Timezone->load_pick_list();
		redirect("timezones");
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->timezone_name)
			OR 	empty($_SESSION['transaction_info']->timezone_description)
			OR 	empty($_SESSION['transaction_info']->timezone_continent)
			OR 	empty($_SESSION['transaction_info']->timezone_city)
			OR 	empty($_SESSION['transaction_info']->timezone_offset)
			)
		{
			$_SESSION['error_code']										=	'00030';
			redirect("timezones/view/" . $_SESSION['transaction_id']);
		}

		// check timezone code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->timezone_name != $_SESSION['original_timezone_name'])
		{
			$count														=	$this->Timezone->check_duplicate();
			if ($count > 0)
			{
				$_SESSION['error_code']									=	'5400';
				redirect("timezones/view/" . $_SESSION['transaction_id']);
			}
		}
	}
}
?>
