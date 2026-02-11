<?php
class Config extends CI_Controller
{
	function index()
	{
		$error 					=	$this->session->flashdata('error');
		$success				=	$this->session->flashdata('success');
		
		$data = array();

		if(isset($error))
		{
			$data['error'] 		= 	$error;
		}
		
		if(isset($success))
		{
			$data['success'] 	= 	$success;
		}
		
		// get currency code
		$data['currency_info']											=	$this->Currency->get_info($this->config->item('currency'));
		
		// show window
		$this															->	load->view('config', $data);
	}
		
	function save()
	{
		// load model
		$this					->	load->model('Branch');
		
		// check branch opened date
		$invalid				=	$this->Branch->branch_opened($this->input->post('branch_opened'));
		if ($invalid != '')
		{
			$error 				=	$this->lang->line('config_error_opened').' -> '.$this->input->post('branch_opened').' -> '.$invalid;
			$this				->	session->set_flashdata('error', $error);
			redirect('config');
			return;
		}		
		
		// check default_client_id - must exist
		if (!$this->Customer->exists($this->input->post('default_client_id')))
		{
			$error 				=	$this->lang->line('config_default_client_id_error').' -> '.$this->input->post('default_client_id');
			$this				->	session->set_flashdata('error', $error);
			redirect('config');
			return;
		}
		
		// check default_supplier_id - must exist
		if (!$this->Supplier->exists($this->input->post('default_supplier_id')))
		{
			$error 				=	$this->lang->line('config_default_supplier_id_error').' -> '.$this->input->post('default_supplier_id');
			$this				->	session->set_flashdata('error', $error);
			redirect('config');
			return;
		}
		
		// check no_supplier_id - must exist
		if (!$this->Supplier->exists($this->input->post('no_supplier_id')))
		{
			$error 				=	$this->lang->line('config_no_supplier_id_error').' -> '.$this->input->post('no_supplier_id');
			$this				->	session->set_flashdata('error', $error);
			redirect('config');
			return;
		}

		// check warehouse_code - must exist
		if (!$this->Warehouse->exists($this->input->post('default_warehouse_code')))
		{
			$error 				=	$this->lang->line('config_default_warehouse_code_error').' -> '.$this->input->post('default_warehouse_code');
			$this				->	session->set_flashdata('error', $error);
			redirect('config');
			return;
		}
		
		// check if label data is correct
		// 1) font
		if (!file_exists($this->input->post('default_label_font')))
		{
			$error 				=	$this->lang->line('config_label_font').' => '.$this->input->post('default_lable_font').' => '.$this->lang->line('common_invalid');
			$this				->	session->set_flashdata('error', $error);
			redirect('config');
			return;
		}
		
		// 2) image
		if (!file_exists($this->input->post('default_label_image')))
		{
			$error 				=	$this->lang->line('config_label_image').' => '.$this->input->post('default_lable_image').' => '.$this->lang->line('common_invalid');
			$this				->	session->set_flashdata('error', $error);
			redirect('config');
			return;
		}
		
		// 3) store
		if (!is_dir($this->input->post('default_label_store')))
		{
			$error 				=	$this->lang->line('config_label_store').' => '.$this->input->post('default_label_store').' => '.$this->lang->line('common_invalid');
			$this				->	session->set_flashdata('error', $error);
			redirect('config');
			return;
		}
		
		// flash_info_displays must be numeric
		if (!is_numeric($this->input->post('flash_info_displays')))
		{
			$error 				=	$this->lang->line('config_flash_info_displays_error').' => '.$this->input->post('flash_info_displays').' => '.$this->lang->line('common_invalid');
			$this				->	session->set_flashdata('error', $error);
			redirect('config');
			return;
		}
			
		// create the update array
		$batch_save_data=array	(
								'branch_opened'			=>	$this->input->post('branch_opened'),
								'company'				=>	$this->input->post('company'),
								'address'				=>	$this->input->post('address'),
								'phone'					=>	$this->input->post('phone'),
								'email'					=>	$this->input->post('email'),
								'fax'					=>	$this->input->post('fax'),
								'website'				=>	$this->input->post('website'),
								'default_tax_1_rate'	=>	$this->input->post('default_tax_1_rate'),		
								'default_tax_1_name'	=>	$this->input->post('default_tax_1_name'),		
								'default_tax_2_rate'	=>	$this->input->post('default_tax_2_rate'),	
								'default_tax_2_name'	=>	$this->input->post('default_tax_2_name'),		
								'currency'				=>	$this->input->post('currency'),
								'pricelist_id'			=>	$this->input->post('pricelist_id'),
								'profile_id'			=>	$this->input->post('profile_id'),
								'polite_message'		=>	$this->input->post('polite_message'),
								'season_message'		=>	$this->input->post('season_message'),
								'fidelity_message'		=>	$this->input->post('fidelity_message'),
								'return_policy'			=>	$this->input->post('return_policy'),
								'language'				=>	$this->input->post('language'),
								'timezone'				=>	$this->input->post('timezone'),
								'dateformat'			=>	$this->input->post('dateformat'),
								'timeformat'			=>	$this->input->post('timeformat'),
								'numberformat'			=>	$this->input->post('numberformat'),
								'Alarm_OK_code'			=>	$this->input->post('Alarm_OK_code'),
								'Alarm_KO_code'			=>	$this->input->post('Alarm_KO_code'),
								'POsavepath'			=>	$this->input->post('POsavepath'),
								'RPsavepath'			=>	$this->input->post('RPsavepath'),
								'BUsavepath'			=>	$this->input->post('BUsavepath'),
								'POemail'				=>	$this->input->post('POemail'),
								'POemailpwd'			=>	$this->input->post('POemailpwd'),
								'POemailmsg'			=>	$this->input->post('POemailmsg'),
								'print_after_sale'		=>	$this->input->post('print_after_sale'),
								'print_receipt_categories'		=>	$this->input->post('print_receipt_categories'),
								'monthlysalestarget'	=>	0,
								'averagenumberopendays'	=>	0,
								'PPsavepath'			=>	$this->input->post('PPsavepath'),
								'PPfilename'			=>	$this->input->post('PPfilename'),
								'SPsavepath'			=>	$this->input->post('SPsavepath'),
								'SPfilename'			=>	$this->input->post('SPfilename'),
								'default_label_font'	=>	$this->input->post('default_label_font'),
								'default_label_image'	=>	$this->input->post('default_label_image'),
								'default_label_store'	=>	$this->input->post('default_label_store'),
								'default_client_id'		=>	$this->input->post('default_client_id'),
								'default_supplier_id'	=>	$this->input->post('default_supplier_id'),
								'no_supplier_id'		=>	$this->input->post('no_supplier_id'),
								'default_warehouse_code'=>	$this->input->post('default_warehouse_code'),
								'createstockvaluationrecords'	=>	$this->input->post('createstockvaluationrecords'),
								'createcategoryrecords'	=>	$this->input->post('createcategoryrecords'),
								'import_items_database'	=>	$this->input->post('import_items_database'),
								'software_update'		=>	$this->input->post('software_update'),
								'catalogue_name'		=>	$this->input->post('catalogue_name'),
								'catalogue_path'		=>	$this->input->post('catalogue_path'),
								'browser_download_folder'		=>	$this->input->post('browser_download_folder'),
								'cashtill_check_total'	=>	$this->input->post('cashtill_check_total'),
								'cashtill_total'		=>	$this->input->post('cashtill_total'),
								'cashtill_allow_correction'		=>	$this->input->post('cashtill_allow_correction'),
								'cashtill_notification_email'	=>	$this->input->post('cashtill_notification_email'),
								'cashtill_notification_password'=>	$this->input->post('cashtill_notification_password'),
								'ticket_printer'		=>	$this->input->post('ticket_printer'),
								'siret'					=>	$this->input->post('siret'),
								'tva'					=>	$this->input->post('tva'),
								'open_hours'			=>	$this->input->post('open_hours'),
								'fidelity_rule'			=>	$this->input->post('fidelity_rule'),
								'fidelity_value'		=>	$this->input->post('fidelity_value'),
								'fidelity_minimum'		=>	$this->input->post('fidelity_minimum'),
								'fidelity_maximum'		=>	$this->input->post('fidelity_maximum'),
								'person_show_comments'	=>	$this->input->post('person_show_comments'),
								'use_DLUO'				=>	$this->input->post('use_DLUO'),
								'flash_info_displays'	=>	$this->input->post('flash_info_displays'),
								'custom1_name'			=>	$this->input->post('custom1_name'),
								'custom2_name'			=>	$this->input->post('custom2_name'),
								'custom3_name'			=>	$this->input->post('custom3_name'),
								'custom4_name'			=>	$this->input->post('custom4_name'),
								'custom5_name'			=>	$this->input->post('custom5_name'),
								'custom6_name'			=>	$this->input->post('custom6_name'),
								'custom7_name'			=>	$this->input->post('custom7_name'),
								'custom8_name'			=>	$this->input->post('custom8_name'),
								'custom9_name'			=>	$this->input->post('custom9_name'),
								'custom10_name'			=>	$this->input->post('custom10_name')
								);
		
		if ($this->Appconfig->batch_save($batch_save_data))
		{
			$success 													=	$this->lang->line('config_success');
			$this														->	session->set_flashdata('success', $success);
			
			// reload the global currencies
			$_SESSION['G']->currency_details							=	$this->Currency->get_info($this->input->post('currency'));

			// redirect
			redirect('config');
			return;
		}
	}
}
?>
