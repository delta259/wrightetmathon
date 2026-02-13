<?php
class trackers extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"61";

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
		$config['total_rows'] 											= 	$this->Tracker->count_all();
		$this															->	pagination->initialize($config);

		// setup output data
		$data['links']													=	$this->pagination->create_links();
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['tracker_data']											=	$this->Tracker->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) );

		$this															->	load->view('trackers/manage',$data);
	}

	function search()
	{
		$search		= $this->input->post('search');
		$trackers	= $this->Tracker->search($search);
		$html		= '';

		// status badge colors
		$status_list = $_SESSION['G']->tracker_status_pick_list ?? array();

		foreach ($trackers->result() as $t)
		{
			$status_label = $status_list[$t->tracker_status] ?? $t->tracker_status;
			$status_color = 'background:#eff6ff;color:#1e40af;border:1px solid #3b82f6;';
			if ($t->tracker_status == 5) { $status_color = 'background:#dcfce7;color:#166534;border:1px solid #22c55e;'; }
			elseif ($t->tracker_status == 1) { $status_color = 'background:#fef9c3;color:#854d0e;border:1px solid #eab308;'; }
			elseif ($t->tracker_status == 2) { $status_color = 'background:#fef2f2;color:#991b1b;border:1px solid #ef4444;'; }

			$html .= '<tr class="tracker-row" data-href="'.site_url('trackers/view/'.$t->tracker_id).'" style="cursor:pointer;">';
			$html .= '<td>'.htmlspecialchars($t->tracker_subject).'</td>';
			$html .= '<td style="text-align:center;"><span style="'.$status_color.'padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">'.htmlspecialchars($status_label).'</span></td>';
			$html .= '<td>'.htmlspecialchars($t->tracker_commit_summary ?? '').'</td>';
			$html .= '<td style="text-align:center;">'.htmlspecialchars($t->tracker_added ?? '').'</td>';
			$html .= '<td style="text-align:center;">'.htmlspecialchars($t->tracker_changed ?? '').'</td>';
			$html .= '<td style="text-align:center;white-space:nowrap;">';
			$html .= '<a href="#" onclick="if(confirm(\'Supprimer ce tracker ?\')){window.location=\''.site_url('trackers/delete/'.$t->tracker_id).'\';} return false;" title="Supprimer" style="text-decoration:none;">';
			$html .= '<svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
			$html .= '</a></td>';
			$html .= '</tr>';
		}

		if ($trackers->num_rows() == 0)
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
		$suggestions 													=	$this->Tracker->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($tracker_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$tracker_id;

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
		switch ($tracker_id)
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;

			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Tracker->get_info($tracker_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->tracker_subject;
						break;

						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->tracker_subject;
						break;
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("trackers");
	}

	function save()
	{
		// save orignal data but only first time through
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_tracker_subject']);
			$_SESSION['original_tracker_subject']						=	$_SESSION['transaction_info']->tracker_subject;
			$_SESSION['first_time']										=	1;
		}

		// load input data
		$_SESSION['transaction_info']->tracker_subject					=	$this->input->post('tracker_subject');
		$_SESSION['transaction_info']->tracker_description				=	$this->input->post('tracker_description');
		$_SESSION['transaction_info']->tracker_status					=	$this->input->post('tracker_status');
		$_SESSION['transaction_info']->tracker_commit_summary			=	$this->input->post('tracker_commit_summary');
		$_SESSION['transaction_info']->tracker_changed					=	date('Y-m-d H:i:s');
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');

		// do data verifications
		$this															->	verify();

		// if here then all checks succeeded so do the update
		$this															->	Tracker->save();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					$_SESSION['error_code']								=	'05600';
					$this												->	view($_SESSION['transaction_info']->tracker_id, $_SESSION['origin']);
			break;

			default:
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05610';
					$this												->	view($_SESSION['transaction_info']->tracker_id, $_SESSION['origin']);
			break;
		}
	}

	function delete($tracker_id)
	{
		if ($this->Tracker->delete($tracker_id))
		{
			$_SESSION['error_code']										=	'01655';
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}

		redirect("trackers");
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->tracker_subject)
			OR 	empty($_SESSION['transaction_info']->tracker_description)
			)
		{
			$_SESSION['error_code']										=	'00030';
			$_SESSION['show_dialog']									=	1;
			redirect("trackers");
		}

		// verify commit summary is entered if commit status
		if ($_SESSION['transaction_info']->tracker_status == 5 AND empty($_SESSION['transaction_info']->tracker_commit_summary))
		{
			$_SESSION['error_code']										=	'05590';
			$_SESSION['show_dialog']									=	1;
			redirect("trackers");
		}
	}
}
?>
