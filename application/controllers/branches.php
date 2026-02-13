<?php
class Branches extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"1";
		
		// set data array
		$data = array();
		
		// manage session
		$_SESSION['controller_name']=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);
		
		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$data['title']	=	$this->lang->line('common_undelete');
			break;
				
			default:
					$data['title']	=	'';
			break;
		}
		
		// set up the pagination
		$config						=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 		= 	site_url('/branches/index');
		$config['total_rows'] 		= 	$this->Branch->count_all();
		$this						->	pagination->initialize($config);
		
		// setup output data
		$data['links']				=	$this->pagination->create_links();
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->Common_routines->set_form_width();
		$data['branch_data']		=	$this->Branch->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ));
		
		$this->load->view('branches/manage',$data);
	}

	function search()
	{
		$search		= $this->input->post('search');
		$branches	= $this->Branch->search($search);
		$html		= '';

		foreach ($branches->result() as $branch)
		{
			$type_label = '';
			if ($branch->branch_type === 'I') { $type_label = $this->lang->line('branches_branch_type_I'); }
			elseif ($branch->branch_type === 'F') { $type_label = $this->lang->line('branches_branch_type_F'); }

			$allows = ($branch->branch_allows_check === 'Y')
				? '<span style="background:#dcfce7;color:#166534;border:1px solid #22c55e;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">Oui</span>'
				: '<span style="background:#fef2f2;color:#991b1b;border:1px solid #ef4444;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">Non</span>';

			$html .= '<tr class="branch-row" data-href="'.site_url('branches/view/'.$branch->branch_code).'" style="cursor:pointer;">';
			$html .= '<td style="text-align:center;">'.anchor('branches/view/'.$branch->branch_code, htmlspecialchars($branch->branch_code)).'</td>';
			$html .= '<td>'.htmlspecialchars($branch->branch_description).'</td>';
			$html .= '<td style="text-align:center;">'.htmlspecialchars($branch->branch_ip).'</td>';
			$html .= '<td style="text-align:center;">'.htmlspecialchars($branch->branch_user).'</td>';
			$html .= '<td>'.htmlspecialchars($branch->branch_database).'</td>';
			$html .= '<td style="text-align:center;">'.$allows.'</td>';
			$html .= '<td style="text-align:center;"><span style="background:#eff6ff;color:#1e40af;border:1px solid #3b82f6;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">'.$type_label.'</span></td>';
			$html .= '<td style="text-align:center;white-space:nowrap;">';
			$html .= '<a href="#" onclick="if(confirm(\''.addslashes($this->lang->line('branches_confirm_delete')).'\')){window.location=\''.site_url('branches/delete/'.$branch->branch_code).'\';} return false;" title="'.htmlspecialchars($this->lang->line('branches_delete')).'" style="text-decoration:none;">';
			$html .= '<svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
			$html .= '</a></td>';
			$html .= '</tr>';
		}

		if ($branches->num_rows() == 0)
		{
			$html .= '<tr><td colspan="8" style="text-align:center;padding:20px;color:#64748b;">'.$this->lang->line('common_no_persons_to_display').'</td></tr>';
		}

		echo $html;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Branch->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($branch_code=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_id']								=	$branch_code;

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
			break;

			default:
					$_SESSION['origin']							=	$origin;
			break;
		}

		// load branch type pick list
		$_SESSION['branch_type_pick_list']						=	array('I'=>$this->lang->line('branches_branch_type_I'), 'F'=>$this->lang->line('branches_branch_type_F'));

		// set data
		switch ($branch_code)
		{
			// create new
			case	-1:
					$_SESSION['$title']							=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']							=	1;
					$_SESSION['transaction_info']->branch_user		=	$this->db->username;
					$_SESSION['transaction_info']->branch_password	=	$this->db->password;
			break;

			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Branch->get_info($branch_code);
					if (!$_SESSION['transaction_info']) { $_SESSION['transaction_info'] = new stdClass(); $_SESSION['transaction_info']->branch_description = ''; }

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->branch_description;
						break;

						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->branch_description;
						break;
					}
					unset($_SESSION['new']);
			break;
		}

		$this->load->view('branches/form');
	}
	
	function save()
	{		
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_branch_code']);
			$_SESSION['original_branch_code']							=	$_SESSION['transaction_info']->branch_code;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->branch_code						=	$this->input->post('branch_code');
		$_SESSION['transaction_info']->branch_description				=	$this->input->post('branch_description');												
		$_SESSION['transaction_info']->branch_type						=	$this->input->post('branch_type');
		$_SESSION['transaction_info']->branch_allows_check				=	$this->input->post('branch_allows_check');
		$_SESSION['transaction_info']->branch_ip						=	$this->input->post('branch_ip');
		$_SESSION['transaction_info']->branch_user						=	$this->input->post('branch_user');
		$_SESSION['transaction_info']->branch_password					=	$this->input->post('branch_password');
		$_SESSION['transaction_info']->branch_database					=	$this->input->post('branch_database');
		$_SESSION['transaction_info']->deleted							=	0;
		
		// do data verifications
		$this->															verify();
		
		// if here then all checks succeeded so do the update
		$this->															Branch->save();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					$_SESSION['error_code']								=	'01640';
			break;

			default:
					$_SESSION['error_code']								=	'01650';
			break;
		}

		// redirect back to manage page
		unset($_SESSION['new']);
		unset($_SESSION['first_time']);
		redirect("branches");
	}

	function delete($branch_code)
	{
		if ($this->Branch->delete($branch_code))
		{
			$_SESSION['error_code']										=	'01655';
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}

		redirect("branches");
	}
	
	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->branch_code)
			OR 	empty($_SESSION['transaction_info']->branch_description)
			OR 	empty($_SESSION['transaction_info']->branch_type)
			OR 	empty($_SESSION['transaction_info']->branch_allows_check)
			)
		{
			$_SESSION['error_code']			=	'00030';
			redirect("branches/view/" . $_SESSION['transaction_id']);
		}

		// check branch code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->branch_code != $_SESSION['original_branch_code'])
		{
			$count							=	$this->Branch->check_duplicate();
			if ($count > 0)
			{
				$_SESSION['error_code']		=	'01620';
				redirect("branches/view/" . $_SESSION['transaction_id']);
			}
		}

		// if allow-check = Y test entries
		if ($_SESSION['transaction_info']->branch_allows_check == 'Y')
		{
			// check required fields
			if 	(	empty($_SESSION['transaction_info']->branch_ip)
				OR 	empty($_SESSION['transaction_info']->branch_user)
				OR 	empty($_SESSION['transaction_info']->branch_password)
				OR 	empty($_SESSION['transaction_info']->branch_database)
				)
			{
				$_SESSION['error_code']			=	'00030';
				redirect("branches/view/" . $_SESSION['transaction_id']);
			}

			// check branch ip duplicate
			if (($_SESSION['new'] ?? 0) == 1 OR (isset($_SESSION['transaction_info_original']) && $_SESSION['transaction_info']->branch_ip != $_SESSION['transaction_info_original']->branch_ip))
			{
				if (!$this	->	Branch->check_duplicate_ip())
				{
					$_SESSION['error_code']			=	'01630';
					redirect("branches/view/" . $_SESSION['transaction_id']);
				}
			}
		}

		// if allows_check is N, blank fields
		if ($_SESSION['transaction_info']->branch_allows_check == 'N')
		{
			$_SESSION['transaction_info']->branch_ip		=	NULL;
			$_SESSION['transaction_info']->branch_user		=	NULL;
			$_SESSION['transaction_info']->branch_password	=	NULL;
			$_SESSION['transaction_info']->branch_database	=	NULL;
		}
	}

}
?>
