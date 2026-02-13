<?php
class imports extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"29";
		
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
		$config['total_rows'] 											= 	$this->Import->count_all();
		$this															->	pagination->initialize($config);	
	
		// setup output data
		$data['links']													=	$this->pagination->create_links();	
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['manage_table_data']										=	$this->Import->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) );

		$this															->	load->view('imports/manage',$data);
	}

	function search()
	{
		$search		=	$this->input->post('search');
		$results	=	$this->Import->search($search);
		$html		=	'';
		if ($results && $results->num_rows() > 0)
		{
			foreach ($results->result() as $col)
			{
				$data_type_label = isset($_SESSION['C']->data_type_pick_list[$col->column_data_type]) ? $_SESSION['C']->data_type_pick_list[$col->column_data_type] : $col->column_data_type;
				$html .= '<tr class="clickable-row" data-href="'.site_url('imports/view/'.$col->column_id).'" style="cursor:pointer;">';
				$html .= '<td style="text-align:center;">'.htmlspecialchars($col->column_letter).'</td>';
				$html .= '<td><strong>'.htmlspecialchars($col->column_label).'</strong></td>';
				$html .= '<td style="text-align:center;">'.(int)$col->column_number.'</td>';
				$html .= '<td style="text-align:center;"><span class="badge badge-info">'.$data_type_label.'</span></td>';
				$html .= '<td>'.htmlspecialchars($col->column_database_field_name).'</td>';
				$html .= '<td style="text-align:center;">';
				$html .= '<a href="#" onclick="if(confirm(\'Confirmation de la suppression ?\')){window.location=\''.site_url('imports/delete/'.$col->column_id).'\';} return false;" title="Supprimer" style="text-decoration:none;">';
				$html .= '<svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
				$html .= '</a></td>';
				$html .= '</tr>';
			}
		}
		echo $html;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													=	$this->Import->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($column_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$column_id;
		
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
		switch ($column_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Import->get_info($column_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->column_label;
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->column_label;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}
		
		redirect("imports");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for duplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_column_letter']);
			$_SESSION['original_column_letter']							=	$_SESSION['transaction_info']->column_letter;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->column_letter					=	$this->input->post('column_letter');
		$_SESSION['transaction_info']->column_label						=	$this->input->post('column_label');												
		$_SESSION['transaction_info']->column_number					=	$this->input->post('column_number');
		$_SESSION['transaction_info']->column_data_type					=	$this->input->post('column_data_type');
		$_SESSION['transaction_info']->column_database_field_name		=	$_SESSION['C']->column_database_field_name_pick_list[$this->input->post('column_database_field_name')];
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');		
		
		// do data verifications
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Import->save();
		
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'05530';
					$this												->	view($_SESSION['transaction_info']->column_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05540';
					$this												->	view($_SESSION['transaction_info']->column_id, $_SESSION['origin']);
			break;	
		}
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->column_letter)
			OR 	empty($_SESSION['transaction_info']->column_label)
			OR 	!isset($_SESSION['transaction_info']->column_number)
			)
		{			
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect("imports");
		}

		// check column code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->column_letter != $_SESSION['original_column_letter'])
		{
			$count														=	$this->Import->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']									=	'05550';
				redirect("imports");
			}
		}
		
		// verify column number is numeric
		if (!is_numeric($_SESSION['transaction_info']->column_number))
		{
			// set message
			$_SESSION['error_code']										=	'05560';
			redirect("imports");
		}
	}
	
	function	delete($column_id)
	{
		// delete the column completely
		$this															->	Import->delete($column_id);
		unset($_SESSION['show_dialog']);
		redirect("imports");
	}
}
?>
