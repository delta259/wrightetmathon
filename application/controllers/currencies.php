<?php
class Currencies extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"7";
		
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
		$config['base_url'] 											= 	site_url('/currencies/index');
		$config['total_rows'] 											= 	$this->Currency->count_all();
		$this															->	pagination->initialize($config);
			
		// setup output data
		$data['links']													=	$this->pagination->create_links();	
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['manage_table_data']										=	$this->Currency->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) );

		$this															->	load->view('currencies/manage',$data);
	}

	function search()
	{
		$search		=	$this->input->post('search');
		$results	=	$this->Currency->search($search);
		$html		=	'';
		if ($results && $results->num_rows() > 0)
		{
			foreach ($results->result() as $currency)
			{
				$side_label = ($currency->currency_side == 'R') ? 'Droite' : 'Gauche';
				$html .= '<tr class="clickable-row" data-href="'.site_url('currencies/view/'.$currency->currency_id).'" style="cursor:pointer;">';
				$html .= '<td>'.htmlspecialchars($currency->currency_name).'</td>';
				$html .= '<td>'.htmlspecialchars($currency->currency_description).'</td>';
				$html .= '<td style="text-align:center;">'.htmlspecialchars($currency->currency_code).'</td>';
				$html .= '<td style="text-align:center;">'.htmlspecialchars($currency->currency_sign).'</td>';
				$html .= '<td style="text-align:center;"><span class="badge badge-info">'.$side_label.'</span></td>';
				$html .= '<td style="text-align:center;">'.(int)$currency->currency_display_order.'</td>';
				$html .= '<td style="text-align:center;">';
				$html .= '<a href="#" onclick="if(confirm(\'Confirmation de la suppression ?\')){window.location=\''.site_url('currencies/delete/'.$currency->currency_id).'\';} return false;" title="Supprimer" style="text-decoration:none;">';
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
		$suggestions 													=	$this->Currency->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($currency_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$currency_id;
		
		// set up currency side dropdown
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
		switch ($currency_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Currency->get_info($currency_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->currency_name;
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->currency_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}
		
		redirect("currencies");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_currency_name']);
			$_SESSION['original_currency_name']							=	$_SESSION['transaction_info']->currency_name;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->currency_name					=	$this->input->post('currency_name');
		$_SESSION['transaction_info']->currency_description				=	$this->input->post('currency_description');												
		$_SESSION['transaction_info']->currency_code					=	$this->input->post('currency_code');
		$_SESSION['transaction_info']->currency_sign					=	$this->input->post('currency_sign');
		$_SESSION['transaction_info']->currency_side					=	$this->input->post('currency_side');
		$_SESSION['transaction_info']->currency_tax						=	$this->input->post('currency_tax');
		$_SESSION['transaction_info']->currency_display_order			=	$this->input->post('currency_display_order');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');		
		
		// strip spaces from currency name
		//$_SESSION['transaction_info']->currency_name					=	preg_replace('/\s+/', '', $_SESSION['transaction_info']->currency_name);
		
		// do data verifications
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Currency->save();
		
		// reload the global currencies
		$_SESSION['G']->currency_details								=	$this->Currency->get_info($this->config->item('currency'));
		
		// and pick list
		$this															->	Currency->load_pick_list();
		
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'05040';
					$this												->	view($_SESSION['transaction_info']->currency_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05050';
					$this												->	view($_SESSION['transaction_info']->currency_id, $_SESSION['origin']);
			break;	
		}
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->currency_name)
			OR 	empty($_SESSION['transaction_info']->currency_description)
			OR 	empty($_SESSION['transaction_info']->currency_code)
			OR 	empty($_SESSION['transaction_info']->currency_display_order)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect("currencies");
		}

		// check Currency code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->currency_name != $_SESSION['original_currency_name'])
		{
			$count														=	$this->Currency->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']									=	'5030';
				redirect("currencies");
			}
		}
		
		// verify display order is numeric
		if (!is_numeric($_SESSION['transaction_info']->currency_display_order))
		{
			// set message
			$_SESSION['error_code']										=	'02030';
			redirect("currencies");
		}
	}

	function delete($currency_id)
	{
		if ($this->Currency->delete($currency_id))
		{
			$_SESSION['error_code']											=	'01660';
		}
		else
		{
			$_SESSION['error_code']											=	'00350';
		}
		redirect("currencies");
	}
}
?>
