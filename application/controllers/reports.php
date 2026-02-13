<?php
class Reports extends CI_Controller
{	
	//Initial report listing screen
	function index()
	{		
		// set module id
		$_SESSION['module_id']											=	"21";
		
		// set session data
		unset($_SESSION['report_data']);
		unset($_SESSION['report_load']);
		unset($_SESSION['sel_item_id']);
		unset($_SESSION['origin']);
		unset($_SESSION['IL_supplier_id']);
		$_SESSION['report_controller'] = 1;

		// get module info
		$_SESSION['controller_name']=	strtolower(get_class($this));

		$data = array();
		$this->load->view('reports/listing', $data);
	}
	
	// ----------------------------------------------------------------------------------------------------				
	// Input date ranges, transaction subtypes (invoice, PO, etc) and transaction sortby (date, client, etc)
	// this input applies to tabular and graphical and ticket_z reports.
	// see routes.php - it routes all summary reports here.
	// ----------------------------------------------------------------------------------------------------
	
	function date_input_excel_export($error=' ')
	{
		$data 						=	$this->_get_common_report_data();
		
		// set the excel flag
		$data['export_to_excel_allowed'] = 'yes';
		
		$this						->	load->view("reports/date_input_excel_export", $data);

		//var_dump($this);
	 	//exit;
	}

	//Même fonction que function date_input_excel_export($error=' ')
	function date_input_excel_export_fournisseur($error=' ')
	{
		$data 						=	$this->_get_common_report_data();
		
		// set the excel flag
		$data['export_to_excel_allowed'] = 'yes';
		
		//Affichage de la vue pour choisir les paramétres du rapport détaillé par fournisseur 
		$this						->	load->view("reports/date_input_excel_export_fournisseur", $data);
	}

	//Même fonction que function date_input_excel_export($error=' ')
	function date_input_excel_export_category($error=' ')
	{
		$data 						=	$this->_get_common_report_data();
		
		// set the excel flag
		$data['export_to_excel_allowed'] = 'yes';
		
		//Affichage de la vue pour choisir les paramétres du rapport détaillé par Catégorie 
		$this						->	load->view("reports/date_input_excel_export_category", $data);
	}
	function date_input_excel_exportZ($error=' ')
	{
		$data 						=	$this->_get_common_report_dataZ();
		
		// set the excel flag
		$data['export_to_excel_allowed'] = 'yes';
		
		$this						->	load->view("reports/date_input_excel_exportZ", $data);

	    //var_dump($this);
	 	//exit;
	}
	
	
	// -----------------------------------------------------------------------------------------				
	// Summary transactions report
	// -----------------------------------------------------------------------------------------
	function summary_transactions($start_date, $end_date, $transaction_subtype, $transaction_sortby, $export_excel=0, $month_end=0)
	{				
		// clear data arrays
		$this->clear_arrays();

		// get report_type (SR or SM)
		$transaction_type												=	$this->Transaction->get_transaction_type($transaction_subtype);

		// load appropriate model parameters depending on report type
		// model_path, model_name and sortby_data are all passed back from this call
		$this															->	model_parms($transaction_type, $transaction_sortby, $model_path, $model_name, $sortby_data);
		
		// trap errors
		if ($model_name == 'none')
		{
			$this														->	error_routine($transaction_subtype, $transaction_sortby);
		}

		// get the report data
		$report_data 													=	$this->$model_name->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));

		// get column1 data
		$column_data 													=	$this->Transaction->get_transaction_column_data($transaction_sortby);

		foreach($report_data as $row)
		{			
			if ($transaction_sortby != 'payment')
			{
				if ($transaction_type == 'SR')
				{
					$column1 = $column_data['sales_column1'];
					eval ('$column1 = ' . $column1 . ';');
					$column2 = $column_data['sales_column2'];
					eval ('$column2 = ' . $column2 . ';');
					
					if ($column2 == 'none')
					{
						$tabular_data[]									=	array($column1, $row['subtotal'], $row['total'], $row['tax'], $row['profit']);
					}
					else
					{
						$tabular_data[]									=	array($column1, $column2, $row['subtotal'], $row['total'], $row['tax'], $row['profit']);	
					}
				}
				else
				{
					$column1 = $column_data['receivings_column1'];
					eval ('$column1 = ' . $column1 . ';');
					$column2 = $column_data['receivings_column2'];
					eval ('$column2 = ' . $column2 . ';');
					
					if ($column2 == 'none')
					{
						$tabular_data[]									=	array($column1, $row['subtotal']);
					}
					else
					{
						$tabular_data[]									=	array($column1, $column2, $row['subtotal']);
					}
				}
			}
			else // this is a payment sortby
			{
				$tabular_data[] 										=	array($row['payment_type'], $row['payment_amount'], round(($row['payment_amount'] / ((100 + $this->config->item('default_tax_1_rate'))/100)), 2));
			}
		}
		
		$data = array(
			"title" 		=> $this->lang->line('reports_summary_transactions_report').' '.$this->lang->line('common_by').' '.$this->lang->line('reports_sortby_'.$transaction_sortby).' '.$this->lang->line('common_for').' '.$this->db->database,
			"subtitle" 		=> $this->lang->line('reports_'.$transaction_subtype).' '.$this->lang->line('common_from') .' '.date($this->config->item('dateformat'), strtotime($start_date)) .' '.$this->lang->line('common_to').' '.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" 		=> $this->$model_name->getDataColumns(),
			"data" 			=> $tabular_data,
			"summary_data" 	=> $this->$model_name->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype)),
			"export_excel" 	=> $export_excel,
			"start_date"	=> $start_date,
			"end_date"		=> $end_date
		);
		$data2 = array(
			"summary_data" 	=> $this->$model_name->getSummaryDataz2(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype))
		  );
		
		// if sales report
		if ($transaction_type == 'SR')
		{
			// get offered count
			$data['summary_data']										+=	$this->$model_name->offered_count(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));

			// calculate average basket
			if ($data2['summary_data']['invoice_count']!=0)
			{
				// calculate average basket
			$data['summary_data']['average_basket']						=	$data['summary_data']['subtotal'] / $data2['summary_data']['invoice_count'];
			}
		}
		
		// test to see if CSV file is required
		if ($export_excel == 1)
		{
			$this->load->model('Common_routines');
			$this->Common_routines->create_csv($data, $month_end=0, $specific=0);
			
			// set message
			$_SESSION['error_code']										=	'05460';
			
			// return to correct place depending on calling function
			if (debug_backtrace()[1]['function'] == 'logout')
			{
				return;
			}
			else
			{
				if(isset($_SESSION['shutdown_all_indicator_part_2']))
				{
					//rien
					//redirect("reports");
					return;
				}
				else
				{
					redirect("reports");
				}
			}
		}
		else
		{
			$this->load->view("reports/tabular", $data);
		}
		
	}
    //------------------------------------------------------Necessaire
	// Summary  ticket_z
	//------------------------------------------------------Necessaire
	
	function summary_transations_ticket_z($start_date, $end_date, $transaction_subtype, $transaction_sortby, $export_excel=0, $month_end=0 )

	{

       // clear data arrays
		$this->clear_arrays();
		$tabular_data = array();

		// get report_type (SR or SM)
		$transaction_type												= $this->Transaction->get_transaction_type($transaction_subtype); 

		// load appropriate model parameters depending on report type
		// model_path, model_name and sortby_data are all passed back from this call
		$this															->	model_parms($transaction_type, $transaction_sortby, $model_path, $model_name, $sortby_data);
		
		// trap errors
		if ($model_name == 'none')
		{
			$this														->	error_routine($transaction_subtype, $transaction_sortby);
		}

		// get the report data 
		$report_data 													=	$this->$model_name->getDataz(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
		
      
		// get column1 data
		$column_data 													=	$this->Transaction->get_transaction_column_data($transaction_sortby);
		

		foreach($report_data as $row)
		{			
			if ($transaction_sortby != 'payment')
			{
				if ($transaction_type == 'SR')
				{
					$column1 = $column_data['sales_column1'];
					eval ('$column1 = ' . $column1 . ';');
					$column2 = $column_data['sales_column2'];
					eval ('$column2 = ' . $column2 . ';');
					
					if ($column2 == 'none')
					{
						$tabular_data[]									=	array($column1, $row['subtotal'], $row['total'], $row['tax'], $row['profit']);
					}
					else
					{
						$tabular_data[]									=	array($column1, $column2, $row['subtotal'], $row['total'], $row['tax'], $row['profit']);	
					}
				}
				else
				{
					$column1 = $column_data['receivings_column1'];
					eval ('$column1 = ' . $column1 . ';');
					$column2 = $column_data['receivings_column2'];
					eval ('$column2 = ' . $column2 . ';');
					
					if ($column2 == 'none')
					{
						$tabular_data[]									=	array($column1, $row['subtotal']);
					}
					else
					{
						$tabular_data[]									=	array($column1, $column2, $row['subtotal']);
					}
				}
			}
			else // this is a payment sortby
			{
				$ht = isset($row['payment_amount_ht']) ? round($row['payment_amount_ht'], 2) : round(($row['payment_amount'] / ((100 + $this->config->item('default_tax_1_rate'))/100)), 2);
				$tabular_data[] 										=	array($row['payment_type'], $row['payment_amount'], $ht);
			}
		}

		$data = array(
			"title" 		=> $this->lang->line('reports_summary_transations_ticket_z_report').' '.$this->lang->line('common_by').' '.$this->lang->line('reports_sortby_'.$transaction_sortby).' '.$this->lang->line('common_for').' '.$this->db->database,
			"subtitle" 		=> $this->lang->line('reports_'.$transaction_subtype).' '.$this->lang->line('common_from') .' '.date($this->config->item('dateformat'), strtotime($start_date)) .' '.$this->lang->line('common_to').' '.date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" 		=> $this->$model_name->getDataColumns(),
			"data" 			=> $tabular_data,
		    "summary_data" 	=> $this->$model_name->getSummaryDataz(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype)),
			"export_excel" 	=> $export_excel,
			"start_date"	=> $start_date,
			"end_date"		=> $end_date,
			"ticket_z"		=> true,
			"company_name"	=> $this->config->item('company'),
			"company_address" => $this->config->item('address'),
			"company_phone"	=> $this->config->item('phone')
		);
		$data2 = array(
		  "summary_data" 	=> $this->$model_name->getSummaryDataz2(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype))
		);
			
		//var_dump($data);
		//exit;

		// if sales report
		if ($transaction_type == 'SR')
		{
			// get offered count
			$offered = $this->$model_name->offered_countz(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
			if (!empty($offered) && !empty($data['summary_data']))
			{
				$data['summary_data'] += $offered;
			}

			$invoice_count = isset($data2['summary_data']['invoice_count']) ? $data2['summary_data']['invoice_count'] : 0;
			if ($invoice_count != 0 && isset($data['summary_data']['subtotal']))
			{
				// calculate average basket
				$data['summary_data']['average_basket'] = $data['summary_data']['subtotal'] / $invoice_count;
			}

			// tax breakdown by rate for Ticket Z
			if (method_exists($this->$model_name, 'getTaxBreakdownz'))
			{
				$data['tax_breakdown'] = $this->$model_name->getTaxBreakdownz(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
			}
		}

		// test to see if CSV file is required
		if ($export_excel == 1)
		{
			$this->load->model('Common_routines');
			$this->Common_routines->create_csv($data, $month_end=0, $specific=0);
			
			// set message
			$_SESSION['error_code']										=	'05460';
			
			// return to correct place depending on calling function
			if (debug_backtrace()[1]['function'] == 'logout')
			{
				return;
			}
			else
			{
				redirect("reports");
			}
		}
		else
		{
			$_SESSION["data"]	= $data ;
			$this->load->view("reports/tabular", $data);
		}

		
		
	}



	// -----------------------------------------------------------------------------------------				
	// Summary transactions graphical
	// -----------------------------------------------------------------------------------------

	function summary_transactions_graphical($start_date, $end_date, $transaction_subtype, $transaction_sortby, $export_excel=0)
	{
		// clear data arrays
		$this->clear_arrays();
		
		// get report_type (SR or SM)
		$transaction_type = $this->Transaction->get_transaction_type($transaction_subtype);
		
		// load appropriate model parameters depending on report type
		$this->model_parms($transaction_type, $transaction_sortby, $model_path, $model_name, $sortby_data);		
		
		if ($model_name == 'none')
		{
			$this->error_routine($transaction_subtype, $transaction_sortby);
		}
		else
		{
			$error = ' ';
		}
		
		// set variable graph data fields  - depends on transaction_type
		if ($transaction_type == 'SM')
		{
			$graph_yaxis	=	$sortby_data['receivings_graph_yaxis'];
			$graph_xaxis	=	$sortby_data['receivings_graph_xaxis'];
			$graph_type		=	$sortby_data['receivings_graph_type'];
			$graph_label	=	$sortby_data['receivings_graph_label'];
			$graph_value 	= 	$sortby_data['receivings_graph_value'];
		}
		else
		{
			$graph_yaxis	=	$sortby_data['sales_graph_yaxis'];
			$graph_xaxis	=	$sortby_data['sales_graph_xaxis'];
			$graph_type		=	$sortby_data['sales_graph_type'];
			$graph_label	=	$sortby_data['sales_graph_label'];
			$graph_value 	= 	$sortby_data['sales_graph_value'];
		}

		$data = array(
			"title" 				=> $this->lang->line('reports_summary_transactions_report').' '. $this->lang->line('common_by').' '.$this->lang->line('reports_sortby_'.$transaction_sortby).' '. $this->lang->line('common_for').' '.$this->db->database,
			"subtitle" 				=> $this->lang->line('reports_'.$transaction_subtype).' '. $this->lang->line('common_from').' '.date($this->config->item('dateformat'), strtotime($start_date)) .' '. $this->lang->line('common_to').' '.date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" 			=> $this->$model_name->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype)),
			"start_date" 			=> $start_date,
			"end_date" 				=> $end_date,
			"transaction_subtype" 	=> $transaction_subtype,
			"yaxis_label" 			=> $graph_yaxis,
			"xaxis_label" 			=> $graph_xaxis,
			"report_type" 			=> $graph_type,
			"model_path" 			=> $model_path,
			"model_name" 			=> $model_name,
			"label" 				=> $graph_label,
			"value" 				=> $graph_value
			);

		// setup the data file path - has to be fixed as there is a permission problem if base_url used
		$json_data_file = '/var/www/html/wrightetmathon/FirstChart/data.json';

		// populate the data file with header info. Data file is in JSON format
		file_put_contents($json_data_file, '{"chart":{"caption":');
		file_put_contents($json_data_file, '"'.$title.'",', 				FILE_APPEND);
		file_put_contents($json_data_file, '"subcaption":', 				FILE_APPEND);
		file_put_contents($json_data_file, '"'.$subtitle.'",', 				FILE_APPEND);
		file_put_contents($json_data_file, '"xaxisname":', 					FILE_APPEND);
		file_put_contents($json_data_file, '"'.$xaxis_label.'",', 			FILE_APPEND);
		file_put_contents($json_data_file, '"yaxisname":', 					FILE_APPEND);
		file_put_contents($json_data_file, '"'.$yaxis_label.'",', 			FILE_APPEND);
		file_put_contents($json_data_file, '"numberprefix":"€"},', 			FILE_APPEND);

		// populate the data file with detail info $row_element is passed in from the controller
		// as are $start_date, $end_date, $transaction_subtype
		file_put_contents($json_data_file, '"data":[', 						FILE_APPEND);

		//$this->load->model($model_path.$model_name);
		$report_data = $this->$model_name->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));

		foreach($report_data as $row)
			{
				file_put_contents($json_data_file, '{"label":', 			FILE_APPEND);
				file_put_contents($json_data_file, '"'.$row[$graph_label].'",', 	FILE_APPEND);
				file_put_contents($json_data_file, '"value":', 				FILE_APPEND);
				file_put_contents($json_data_file, '"'.$row[$graph_value].'"},', 	FILE_APPEND);
			}

		// populate data file with footer string	
		file_put_contents($json_data_file, '{}]}', 							FILE_APPEND);

		$this->load->view("reports/graphical", $data);
	}

	// -----------------------------------------------------------------------------				
	// common functions modif 
	// -----------------------------------------------------------------------------
	
	function _get_common_report_data()
	{		
		$data = array();
		$data['report_date_range_simple'] 	= get_simple_date_ranges();
		$data['months'] 					= get_months();
		$data['days'] 						= get_days();
		$data['years'] 						= get_years();
		$data['selected_month']				= date('n');
		$data['selected_day']				= date('d');
		$data['selected_year']				= date('Y');

		// load the transaction subtypes
		$data['options'] = $this->Transaction->get_transaction_modes();
        $data['selected_option'] = 'sales';
		// load the transaction sortby
		$data['sortby'] = $this->Transaction->get_transaction_sortby();
		
		return $data;
	}

	function _get_common_report_data_by_category()
	{		
		$data = array();
		$data['report_date_range_simple'] 	= get_simple_date_ranges();
		$data['months'] 					= get_months();
		$data['days'] 						= get_days();
		$data['years'] 						= get_years();
		$data['selected_month']				= date('n');
		$data['selected_day']				= date('d');
		$data['selected_year']				= date('Y');

		// load the transaction subtypes
		$data['options'] = $this->Transaction->get_transaction_modes();

        unset($data['options']['canceltransaction']);
        unset($data['options']['purchaseorder']);
        unset($data['options']['receive']);
        unset($data['options']['stockadhoc']);
        unset($data['options']['suspended']);
        unset($data['options']['suspendedreception']);
        unset($data['options']['stockreturns']);

/*		$data['options']['canceltransaction']=$data['options'][''];//*/


		// load the transaction sortby
		$data['sortby'] = $this->Transaction->get_transaction_sortby();
		$data['selected_option'] = $data['options']['sales'];
		
		return $data;
	}

    function _get_common_report_data_by_supplier()
	{		
		$data = array();
		$data['report_date_range_simple'] 	= get_simple_date_ranges();
		$data['months'] 					= get_months();
		$data['days'] 						= get_days();
		$data['years'] 						= get_years();
		$data['selected_month']				= date('n');
		$data['selected_day']				= date('d');
		$data['selected_year']				= date('Y');

		// load the transaction subtypes
		$data['options'] = $this->Transaction->get_transaction_modes();

        unset($data['options']['canceltransaction']);
        unset($data['options']['purchaseorder']);
        unset($data['options']['receive']);
        unset($data['options']['stockadhoc']);
        unset($data['options']['suspended']);
        unset($data['options']['suspendedreception']);
        unset($data['options']['stockreturns']);

		// load the transaction sortby
		$data['sortby'] = $this->Transaction->get_transaction_sortby();
		$data['selected_option'] = 'sales';
		return $data;
	}

     //uniquement pour le ticket_Z
	function _get_common_report_dataZ()
	{		
		$data = array();
		$data['report_date_range_simple'] 	= get_simple_date_ranges();
		$data['months'] 					= get_months();
		$data['days'] 						= get_days();
		$data['years'] 						= get_years();
		$data['selected_month']				= date('n');
		$data['selected_day']				= date('d');
		$data['selected_year']				= date('Y');

		// load the transaction subtypes
		$data['options'] = $this->Transaction->get_transaction_modesZ();
        $data['selected_option'] = 'sales';
		// load the transaction sortby
		$data['sortby'] = $this->Transaction->get_transaction_sortbyZ();
		
		return $data;

		
	}
		
		
	// get the report / graph parameters
	function model_parms($transaction_type, $transaction_sortby, &$model_path, &$model_name, &$sortby_data)
	{			
		// get model data
		$sortby_data = $this->Transaction->get_transaction_sortby_data($transaction_sortby);
		
		// load the appropriate model
		if ($transaction_type == 'SM')
		{
			$model_name = $sortby_data['receivings_transaction_model_name'];
			if ($model_name != 'none')
			{
				$this->load->model($sortby_data['receivings_transaction_model_path'].$sortby_data['receivings_transaction_model_name']);
			}
		}
		else
		{
			$model_name = $sortby_data['sales_transaction_model_name'];
			if ($model_name != 'none')
			{
				$this->load->model($sortby_data['sales_transaction_model_path'].$sortby_data['sales_transaction_model_name']);
			}
		}
	}
	
	function error_routine($transaction_subtype, $transaction_sortby)
	{
		$success_or_failure				=	'F';
		$message						= 	$this->lang->line('reports_invalid_selection').' -> '.$this->lang->line('reports_'.$transaction_subtype).' -> '.$this->lang->line('reports_sortby_'.$transaction_sortby);
		$this							->	setflash($success_or_failure, $message);
	}
	
	// -----------------------------------------------------------------------------------------				
	// specific reports - common routines
	// -----------------------------------------------------------------------------------------
	
	// common load data for the specific reports

	function load_data(&$report_data, &$summary_data, &$details_data, $origin='0')
	{
		// get summary line data
		foreach($report_data['summary'] as $key=>$row)
		{					
			// convert mode to type
			$transaction_type 											=	$this->Transaction->get_transaction_type($row['mode']);
			
			// get transaction code 
			$code														=	$this->Transaction->get_transaction_code($row['mode']);
			
			// load correct defaults for type of transaction
			switch ($transaction_type)
			{
				case 'SM':
					// set anchors
					$edit_file 											=	'receivings/edit/';
					$print_file 										=	'receivings/receipt/';
					// load summary data
					$summary_data[] = array				(				$code.$row['transaction_id'],
																		anchor	(
																				$print_file.$row['transaction_id'], 
																				$this->lang->line('common_yes')
																				),
																		$row['mode'],
																		date($this->config->item('dateformat'), strtotime($row['transaction_date'])),
																		$row['transaction_name'],	
																		$row['employee_name']
														);
					// get detailed data
					foreach($report_data['details'][$key] as $drow)
					{
						$line_total										=	round($drow['quantity_purchased'] * $drow['item_cost_price'], 2);
						$details_data[$key][] = array	(				$drow['line'],
																		$drow['category'],
																		$drow['item_number'],
																		$drow['name'],
																		$drow['quantity_purchased'],
																		$drow['item_cost_price'],
																		$line_total
														);
					} 
					break;

				
				case 'SR':
                    // set anchors
                    $edit_file 											=	'sales/edit/';
					$print_file											=	'sales/receipt/';
					$print_file											=	'sales/reprint_select/';
					$copy_sale											=	'sales/copy_sale/';
					$change_payment_method								=	'sales/change_payment_method/';
					
                    // test customer id for null and set to -1 if so
                    if ($row['customer_id'] == null)
                    {
                    	$row['customer_id']								=	-1;
                    	$row['transaction_name']						=	$this->lang->line('customers_customer');
                    }
                    // load summary data
                    $summary_data[] = array	   (					$code.$row['transaction_id'],
                    													anchor	(
                    															$print_file.$row['transaction_id'],
																				'<img src="images2/print.png" align="right" width="19px" height="19px" title="Ré-édition de facture">'
                    															),
                    													$row['mode'],
                    													date($this->config->item('dateformat'), strtotime($row['transaction_date'])) .' '.date($this->config->item('timeformat'), strtotime($row['transaction_date'])),
                    													//$row['transaction_date'],
                    													$row['transaction_name'],	
                    													$row['employee_name'], 
                    													to_currency_no_money($row['overall_total']), 
                    													to_currency_no_money($row['overall_tax']),
                    													to_currency_no_money($row['subtotal_after_discount']), 
                    													$row['payment_type'], 
																		anchor	(
																			$copy_sale.$row['transaction_id'],
																			"<img src='images2/copy.png' align='right' width='19px' height='19px' title='Copiez les articles de cette vente'>"
																			),
																		//anchor	(
                    													//	$change_payment_method.$row['transaction_id'],
																		//	'<img src="images2/copy.png" align="right" width="19px" height="19px" title="Modifier mode de paiement">'
                    													//		),
                    													$row['comment']
                    								);
                    // get detailed data
                    foreach($report_data['details'][$key] as $drow)
                    {
                    $line_TTC											=	round($drow['item_unit_price'] * ((100 + $drow['line_tax_percentage']) / 100), 2);
                    $details_data[$key][] = array	(					$drow['line'],
                    													$drow['category'],
                    													$drow['item_number'],
                    													$drow['name'],
                    													$drow['quantity_purchased'],
                    													$line_TTC,
                    													$drow['discount_percent'],
                    													$drow['line_sales'],
                    													$drow['line_sales_after_discount']
                    								);
                    }
                    break;


				default:
					// set anchors
					$edit_file 											=	'sales/edit/';
					$print_file											=	'sales/receipt/';
					// test customer id for null and set to -1 if so
					if ($row['customer_id'] == null)
					{
						$row['customer_id']								=	-1;
						$row['transaction_name']						=	$this->lang->line('customers_customer');
					}
					// load summary data
					$summary_data[] = array	   (					$code.$row['transaction_id'],
																		anchor	(
																				$print_file.$row['transaction_id'], 
																				$this->lang->line('common_yes')
																				),
																		$row['mode'],
																		date($this->config->item('dateformat'), strtotime($row['transaction_date'])),
																		$row['transaction_name'],	
																		$row['employee_name'], 
																		to_currency_no_money($row['overall_total']), 
																		to_currency_no_money($row['overall_tax']),
																		to_currency_no_money($row['subtotal_after_discount']), 
																		$row['payment_type'], 
																		$row['comment']
													);
					// get detailed data
					foreach($report_data['details'][$key] as $drow)
					{
					$line_TTC											=	round($drow['item_unit_price'] * ((100 + $drow['line_tax_percentage']) / 100), 2);
					$details_data[$key][] = array	(					$drow['line'],
																		$drow['category'],
																		$drow['item_number'],
																		$drow['name'],
																		$drow['quantity_purchased'],
																		$line_TTC,
																		$drow['discount_percent'],
																		$drow['line_sales'],
																		$drow['line_sales_after_discount']
													);
					}
					break;
			}
		}
	}

	function load_data_import_sales(&$report_data, &$summary_data, &$details_data, $origin='0')
	{
		// get summary line data
		foreach($report_data['summary'] as $key=>$row)
		{					
			// convert mode to type
			$transaction_type 											=	$this->Transaction->get_transaction_type($row['mode']);
			
			// get transaction code 
			$code														=	$this->Transaction->get_transaction_code($row['mode']);
			
			// load correct defaults for type of transaction
			switch ($transaction_type)
			{
				case 'SR':
                    // set anchors
                    $edit_file 											=	'sales/edit/';
					$print_file											=	'sales/receipt/';
					$print_file											=	'sales/reprint_select/';
					
                    // test customer id for null and set to -1 if so
                    if ($row['customer_id'] == null)
                    {
                    	$row['customer_id']								=	-1;
                    	$row['transaction_name']						=	$this->lang->line('customers_customer');
                    }
                    // load summary data
                    $summary_data[] = array	   (					$code.$row['transaction_id'],/*
                    													anchor	(
                    															$print_file.$row['transaction_id'],
                    															$this->lang->line('common_yes')
                    															),//*/
                    													$row['mode'],
                    													//date($this->config->item('dateformat'), strtotime($row['transaction_date'])),
                    													$row['transaction_date'],
                    													$row['transaction_name'],
                    													$row['employee_name'],
                    													to_currency_no_money($row['overall_total']),
                    													to_currency_no_money($row['overall_tax']),
                    													to_currency_no_money($row['subtotal_after_discount']),
                    													$row['payment_type'],
                    													$row['comment']
                    								);
                    // get detailed data
                    foreach($report_data['details'][$key] as $drow)
                    {
                    $line_TTC											=	round($drow['item_unit_price'] * ((100 + $drow['line_tax_percentage']) / 100), 2);
                    $details_data[$key][] = array	(					$drow['line'],
                    													$drow['category'],
                    													$drow['item_number'],
                    													$drow['name'],
                    													$drow['quantity_purchased'],
                    													$line_TTC,
                    													$drow['discount_percent'],
                    													$drow['line_sales'],
                    													$drow['line_sales_after_discount']
                    								);
                    }
                    break;


				default:
					// set anchors
					$edit_file 											=	'sales/edit/';
					$print_file											=	'sales/receipt/';
					// test customer id for null and set to -1 if so
					if ($row['customer_id'] == null)
					{
						$row['customer_id']								=	-1;
						$row['transaction_name']						=	$this->lang->line('customers_customer');
					}
					// load summary data
					$summary_data[] = array	   (					$code.$row['transaction_id'],/*
																		anchor	(
																				$print_file.$row['transaction_id'], 
																				$this->lang->line('common_yes')
																				), //*/
																		$row['mode'],
																		date($this->config->item('dateformat'), strtotime($row['transaction_date'])),
																		$row['transaction_name'],	
																		$row['employee_name'], 
																		to_currency_no_money($row['overall_total']), 
																		to_currency_no_money($row['overall_tax']),
																		to_currency_no_money($row['subtotal_after_discount']), 
																		$row['payment_type'], 
																		$row['comment']
													);
					// get detailed data
					foreach($report_data['details'][$key] as $drow)
					{
					$line_TTC											=	round($drow['item_unit_price'] * ((100 + $drow['line_tax_percentage']) / 100), 2);
					$details_data[$key][] = array	(					$drow['line'],
																		$drow['category'],
																		$drow['item_number'],
																		$drow['name'],
																		$drow['quantity_purchased'],
																		$line_TTC,
																		$drow['discount_percent'],
																		$drow['line_sales'],
																		$drow['line_sales_after_discount']
													);
					}
					break;
			}
		}
	}


	function load_data_by_supplier(&$report_data, &$details_data_info, $origin='0')
	{
		// get summary line data
		foreach($report_data as $key=>$row)
		{					
			// convert mode to type
			$transaction_type 											=	$this->Transaction->get_transaction_type($row['mode']);
			
			// get transaction code 
			//ne sert plus 
			//$code														=	$this->Transaction->get_transaction_code($row['mode']);
			
			// load correct defaults for type of transaction
			switch ($transaction_type)
			{
				case 'SM':
					// set anchors
					$edit_file 											=	'receivings/edit/';
					$print_file 										=	'receivings/receipt/';
				$details_data_info[] = array	(
					$row['category_name'],
					$row['item_number'], 
					$row['name'],
					$row['quantity_purchased'],
					round($row['quantity'],0),
					$row['item_unit_price'],
					$row['line_sales'],
					$row['line_tax'],
					$row['line_cost'],
					$row['line_profit'],
					$row['line_sales_after_discount']
				);
					break;
				case 'SR':
					switch($row['mode'])
					{
						case 'sales':
                            // set anchors
                            $edit_file 											=	'sales/edit/';
                            $print_file											=	'sales/receipt/';
                            
                            $details_data_info[] = array	(
                            	$row['category_name'],
                            	$row['item_number'], 
                            	$row['name'],
								$row['quantity_purchased'],
								round($row['quantity'],0),
                            	$row['item_unit_price'],
                            	$row['line_sales'],
                            	$row['line_tax'],
                            	$row['line_cost'],
                            	$row['line_profit'],
                            	$row['line_sales_after_discount']
							);
							break;

						case 'returns':
						// set anchors
						$edit_file 											=	'sales/edit/';
						$print_file											=	'sales/receipt/';
						
						$details_data_info[] = array	(
							$row['category'],
							$row['subtotal'], 
							$row['total'],
							$row['tax'],
							$row['profit']
						);
						break;



						
						default:
							//
							$details_data_info[] = array	(
                            	$row['category_name'],
                            	$row['item_number'], 
                            	$row['name'],
								$row['quantity_purchased'],
								round($row['quantity'],0),
                            	$row['item_unit_price'],
                            	$row['line_sales'],
                            	$row['line_tax'],
                            	$row['line_cost'],
                            	$row['line_profit'],
								$row['line_sales_after_discount']
							);
						break;
					}
                    
                    break;


				default:
					// set anchors
					$edit_file 											=	'sales/edit/';
					$print_file											=	'sales/receipt/';
					$line_TTC											=	round($row['item_unit_price'] * ((100 + $row['line_tax_percentage']) / 100), 2);
					$details_data_info[$key][] = array	(					$row['line'],
																		$row['category'],
																		$row['item_number'], 
																		$row['name'], 
																		$row['description'],
																		$row['quantity_purchased'],
																		round($row['quantity'],0),
																		$line_TTC,
																		$row['discount_percent'],
																		$row['line_sales'],
																		$row['line_sales_after_discount']
													);
					break;
			}
		}
	}
	
	// common output data load
	function output_load_data(&$data, $title, $subtitle, $summary_data, $details_data, $overall_summary_data, $export_excel, $start_date, $end_date, $sale_type, $report_type, $history=0)
	{		
		if ($history != 0)
		{
			$subtitle					= $this->lang->line('reports_'.$sale_type).'  '.$history.'  '.$this->lang->line('reports_last_months_history').' '.$this->lang->line('common_from').' '.date($this->config->item('dateformat'), strtotime($start_date)) .'  '.$this->lang->line('common_to').' '.date($this->config->item('dateformat'), strtotime($end_date));
		}
		else
		{
			$subtitle	 				= $this->lang->line('reports_'.$sale_type).' '.$this->lang->line('common_from').' '.date($this->config->item('dateformat'), strtotime($start_date)) .' '.$this->lang->line('common_to').' '.date($this->config->item('dateformat'), strtotime($end_date));
		}
		
		$data = array(

			"title" 				=> $title,
						"subtitle" 				=> $subtitle,
						"headers" 				=> $this->get_headers($report_type),
						"summary_data" 			=> $summary_data,
						"details_data" 			=> $details_data,
						"overall_summary_data" 	=> $overall_summary_data,
						"export_excel" 			=> $export_excel,
						"start_date"			=> $start_date,
						"end_date"				=> $end_date
					);
	}
	
	//$this->output_load_data_by_supplier($data, $title, $subtitle, $details_data_info, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);

	function output_load_data_by_supplier(&$data, $title, $subtitle, $details_data_info, $overall_summary_data, $export_excel, $start_date, $end_date, $sale_type, $report_type, $history=0)
	{		
		if ($history != 0)
		{
			$subtitle					= $this->lang->line('reports_'.$sale_type).'  '.$history.'  '.$this->lang->line('reports_last_months_history').' '.$this->lang->line('common_from').' '.date($this->config->item('dateformat'), strtotime($start_date)) .'  '.$this->lang->line('common_to').' '.date($this->config->item('dateformat'), strtotime($end_date));
		}
		else
		{
			$subtitle	 				= $this->lang->line('reports_'.$sale_type).' '.$this->lang->line('common_from').' '.date($this->config->item('dateformat'), strtotime($start_date)) .' '.$this->lang->line('common_to').' '.date($this->config->item('dateformat'), strtotime($end_date));
		}
		
		$data = array(
		
			"title" 				=> $title,
						"subtitle" 				=> $subtitle,
						"headers" 				=> $this->get_headers_by_supplier($report_type, $sale_type),
						"details_data_info" => $details_data_info,    //"summary_data" => $summary_data, "details_data" => $details_data,
						"overall_summary_data" 	=> $overall_summary_data,
						"export_excel" 			=> $export_excel
					);
	}
	
	// common clear arrays
	function clear_arrays(&$report_data=array(), &$summary_data=array(), &$details_data=array(), &$data=array(), &$tabular_data=array(), &$model_data=array())
	{
		$report_data	= array();
		$summary_data 	= array();
		$details_data 	= array();
		$data			= array();
		$tabular_data 	= array();
		$model_data 	= array();
	}
	
	// common headers
	function get_headers($report_type)
	{
		// test for report type
		switch($report_type)
		{	
			case 'FD':
				return array(
							'summary' => array	(
												$this->lang->line('fdj_sale_id'),
												$this->lang->line('fdj_sale_date'), 
												$this->lang->line('fdj_sale_total')
												),
							'details' => array	(
												$this->lang->line('fdj_sale_item_number'),
												$this->lang->line('fdj_sale_qty'),
												$this->lang->line('fdj_sale_value') 
												)
							);
				break;
			case 'SM':
				$column_title1 											= 'reports_supplied_by';
				$column_title2 											= 'reports_bought_by';
				return array(
							'summary' => array	(						$this->lang->line('reports_sale_id'),
																		$this->lang->line('reports_print_id'),
																		$this->lang->line('reports_type'),
																		$this->lang->line('reports_date'),
																		$this->lang->line($column_title1), 
																		$this->lang->line($column_title2)),
																		
							'details' => array	(						$this->lang->line('items_line'),   //Attention: pour les tests
																		$this->lang->line('reports_category'),
																		$this->lang->line('items_item_number'),
																		$this->lang->line('reports_name'),
																		$this->lang->line('reports_quantity_purchased'),
																		$this->lang->line('sales_price').' '.$this->lang->line('sales_HT'),
																		$this->lang->line('sales_total').' '.$this->lang->line('sales_HT'))
							);
				break;

			default:    //Pour obtenir les titres des colonnes du rapport
				$column_title1 											= 'reports_sold_to';
				$column_title2 											= 'reports_sold_by';
				return array(
							'summary' => array	(						$this->lang->line('reports_sale_id'),
																		$this->lang->line('reports_print_id'),
																		$this->lang->line('reports_type'),
																		$this->lang->line('reports_date'),
																		$this->lang->line($column_title1), 
																		$this->lang->line($column_title2), 
																		$this->lang->line('reports_total'),
																		$this->lang->line('reports_tax'),
																		$this->lang->line('reports_totalHT'), 
																		$this->lang->line('reports_payment_type'), 
																		$this->lang->line('reports_copy_sale'),
																		//$this->lang->line('reports_change_payment_method'),
																		$this->lang->line('reports_comments')),
																		
							'details' => array	(						$this->lang->line('items_line'),
																		$this->lang->line('reports_category'),
																		$this->lang->line('items_item_number'),
																		$this->lang->line('reports_name'),
																		$this->lang->line('reports_quantity_purchased'),
																		$this->lang->line('sales_price').' '.$this->lang->line('sales_TTC'),
																		$this->lang->line('sales_discount').' '.$this->lang->line('common_percent'),
																		$this->lang->line('sales_total').' '.$this->lang->line('sales_TTC'),
																		$this->lang->line('sales_total').' '.$this->lang->line('sales_HT'))
							);
				break;
		}		
	}

	function get_headers_import_sales($report_type)
	{
		// test for report type
		switch($report_type)
		{	
			default:    //Pour obtenir les titres des colonnes du rapport
				$column_title1 											= 'reports_sold_to';
				$column_title2 											= 'reports_sold_by';
				return array(
							'summary' => array	(						$this->lang->line('reports_sale_id'),
																	//	$this->lang->line('reports_print_id'),
																		$this->lang->line('reports_type'),
																		$this->lang->line('reports_date'),
																		$this->lang->line($column_title1), 
																		$this->lang->line($column_title2), 
																		$this->lang->line('reports_total'),
																		$this->lang->line('reports_tax'),
																		$this->lang->line('reports_totalHT'), 
																		$this->lang->line('reports_payment_type'), 
																		$this->lang->line('reports_comments')),
																		
							'details' => array	(						$this->lang->line('items_line'),
																		$this->lang->line('reports_category'),
																		$this->lang->line('items_item_number'),
																		$this->lang->line('reports_name'),
																		$this->lang->line('reports_quantity_purchased'),
																		$this->lang->line('sales_price').' '.$this->lang->line('sales_TTC'),
																		$this->lang->line('sales_discount').' '.$this->lang->line('common_percent'),
																		$this->lang->line('sales_total').' '.$this->lang->line('sales_TTC'),
																		$this->lang->line('sales_total').' '.$this->lang->line('sales_HT'))
							);
				break;
		}		
	}
	
	// common headers
	function get_headers_by_supplier($report_type, $transaction_subtype)
	{
		// test for report type
		switch($report_type)
		{	
			default:    //Pour obtenir les titres des colonnes du rapport
                
                //Pour faire la différence entre une facture et un avoir
                switch($transaction_subtype)
                {
                   case 'sales':
					   //Facture
					   
				       $column_title1 											= 'reports_sold_to';
				       $column_title2 											= 'reports_sold_by';
				       return array(																		
							        'details' => array	(	
															$this->lang->line('reports_category'),  //Famille
															$this->lang->line('items_item_number'),  //N°Article 
															$this->lang->line('reports_name'),  //Article
															//$this->lang->line('reports_description'), 
															$this->lang->line('reports_sales_vente'),  //Quantité
															$this->lang->line('module_receivings'),  //Stock
															$this->lang->line('reports_item_unit_price'),  //Prix TTC /unité
															$this->lang->line('reports_total'),  //Total TTC
															$this->lang->line('reports_tax'),  //TVA
															$this->lang->line('reports_cost'),  //Coût Total
															$this->lang->line('reports_profit_Marge'),  //Marge
															$this->lang->line('reports_totalHT'))  //Total HT
														);
                	   break;
                   case 'returns':
					   //Avoir
					   return array('details' => array	(
												$this->lang->line('reports_category'),  //Famille
												$this->lang->line('reports_subtotal'),  //Sous Total HT
												$this->lang->line('reports_total'),  //Total TTC
											    $this->lang->line('reports_tax'),  //TVA
											    $this->lang->line('reports_profit')  //Marge HT											    
											));
                	   break;
                   }
                

				break;
		}		
	}

	// -----------------------------------------------------------------------------------------				
	// specific reports - data entry
	// -----------------------------------------------------------------------------------------
	
	function specific_category_input($origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['specific_type']										=	'category';
		$_SESSION['show_dialog']										=	1;
		unset($_SESSION['undel']);
		
		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_category_id']);
			break;
			
			default:
					$_SESSION['origin']									=	$origin;
					$_SESSION['sel_category_id']						=	$category_id;
			break;
		}
		
		// set up dropdowns
		$this															->	dropdowns();
		
		// load items pick list
		$categories 													=	array();
		foreach($this->Category->get_all() as $category)
		{
			$categories[$category['category_id']] 						=	$category['category_name'];
		}
		$_SESSION['transaction_info']->specific_pick_list 				= 	$categories;
		$_SESSION['transaction_info']->selected_specific				=	NULL;

		// load title
		$_SESSION['transaction_info']->specific_input_name				=	$this->lang->line('reports_categories');
		
		// Show dialog
		redirect("reports");		
	}
	
	function specific_customer_input($origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['specific_type']										=	'customer';
		$_SESSION['show_dialog']										=	1;
		unset($_SESSION['undel']);
		
		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_person_id']);
			break;
			
			default:
					$_SESSION['origin']									=	$origin;
					$_SESSION['sel_person_id']							=	$person_id;
			break;
		}
		
		// set up dropdowns
		$this															->	dropdowns();
		
		// load items pick list
		$customers 														=	array();
		foreach($this->Customer->get_all()->result() as $customer)
		{
			$customers[$customer->person_id] 							=	strtoupper($customer->last_name).', '.ucfirst(strtolower($customer->first_name));
		}
		$_SESSION['transaction_info']->specific_pick_list 				= 	$customers;
		$_SESSION['transaction_info']->selected_specific				=	NULL;

		// load title
		$_SESSION['transaction_info']->specific_input_name				=	$this->lang->line('reports_customers');
		
		// Show dialog
		redirect("reports");		
	}
		
	function specific_employee_input($origin='0')
	{
				// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['specific_type']										=	'employee';
		$_SESSION['show_dialog']										=	1;
		unset($_SESSION['undel']);
		
		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_person_id']);
			break;
			
			default:
					$_SESSION['origin']									=	$origin;
					$_SESSION['sel_person_id']							=	$person_id;
			break;
		}
		
		// set up dropdowns
		$this															->	dropdowns();
		
		// load items pick list
		$employees 														=	array();
		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] 							=	strtoupper($employee->last_name).', '.ucfirst(strtolower($employee->first_name));
		}
		$_SESSION['transaction_info']->specific_pick_list 				= 	$employees;
		$_SESSION['transaction_info']->selected_specific				=	NULL;

		// load title
		$_SESSION['transaction_info']->specific_input_name				=	$this->lang->line('reports_employees');
		
		// Show dialog
		redirect("reports");
	}

	function specific_item_input($origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['specific_type']										=	'item';
		$_SESSION['show_dialog']										=	1;
		unset($_SESSION['undel']);
		
		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;
			
			default:
					$_SESSION['origin']							=	$origin;
					$_SESSION['sel_item_id']					=	$item_id;
			break;
		}
		
		// set up dropdowns
		$this													->	dropdowns();
		
		// load items pick list
		$items = array();
		foreach($this->Item->get_all()->result() as $item)
		{
			$items[$item->item_id] 								=	$item->name.', '.$item->item_number;
		}
		$_SESSION['transaction_info']->specific_pick_list 		= 	$items;
		$_SESSION['transaction_info']->selected_specific		=	NULL;
		
		// load title
		$_SESSION['transaction_info']->specific_input_name		=	$this->lang->line('reports_item');
		
		// Show dialog
		redirect("reports");	
	}

	function	dropdowns()
	{
		// drop down and selected data
		$_SESSION['transaction_info']->simple_pick_list					=	get_simple_date_ranges();

		$_SESSION['transaction_info']->months_pick_list 				=	get_months();
		$_SESSION['transaction_info']->days_pick_list					=	get_days();
		$_SESSION['transaction_info']->years_pick_list					=	get_years();

		$_SESSION['transaction_info']->selected_month					=	date('n');
		$_SESSION['transaction_info']->selected_day						=	date('d');
		$_SESSION['transaction_info']->selected_year					=	date('Y');
		
		// load the transaction subtypes
		$_SESSION['transaction_info']->options_pick_list				=	$this->Transaction->get_transaction_modes();
		$_SESSION['transaction_info']->selected_option				=	'sales';
		
		// load the transaction sortby
		$_SESSION['transaction_info']->sortby_pick_list					=	$this->Transaction->get_transaction_sortby();
        $_SESSION['transaction_info']->selected_option					=	'payment';
		
		// load the transaction subtypes
	//	$_SESSION['transaction_info']->options_pick_list				=	$this->Transaction->get_transaction_modesZ();
		$_SESSION['transaction_info']->selected_option				=	'sales';
		
		// load the transaction sortby
	//	$_SESSION['transaction_info']->sortby_pick_list					=	$this->Transaction->get_transaction_sortbyZ();
        $_SESSION['transaction_info']->selected_option					=	'payment';

        // load YorN pick list
		$_SESSION['selected_oneorzero']									=	'0';
	}

	// -----------------------------------------------------------------------------------------				
	// specific reports - set up the data and display
	// -----------------------------------------------------------------------------------------
	
	function specific_report()
	{
		// initialise
		$start_date														=	$this->input->post('start_year').'-'.$this->input->post('start_month').'-'.$this->input->post('start_day');
		$end_date														=	$this->input->post('end_year').'-'.$this->input->post('end_month').'-'.$this->input->post('end_day');
		$transaction_subtype											=	$this->input->post('transaction_subtype');
		$export_excel													=	$this->input->post('export_excel');
		$specific_input_data											=	$this->input->post('specific_input_data');
		
		// now select correct routine
		switch ($_SESSION['specific_type'])
		{
			case	'item':
					$this->specific_item($start_date, $end_date, $specific_input_data, $transaction_subtype, $export_excel);
			break;
			
			case	'customer':
					$this->specific_customer($start_date, $end_date, $specific_input_data, $transaction_subtype, $export_excel);
			break;
			
			case	'employee':
					$this->specific_employee($start_date, $end_date, $specific_input_data, $transaction_subtype, $export_excel);
			break;
			
			case	'category':
					$this->specific_category($start_date, $end_date, $specific_input_data, $transaction_subtype, $export_excel);
			break;		
		}
		
	}	
	
	function specific_customer($start_date, $end_date, $person_id, $transaction_subtype, $export_excel=0)
	{		
		// clear data arrays
		$this->clear_arrays();
		
		// get report_type (SR or SM)
		$transaction_type = $this->Transaction->get_transaction_type($transaction_subtype);
		
		// load appropriate model
		$this->load->model('reports/Specific_customer');
		
		// load report data
		$report_data = $this->Specific_customer->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'person_id'=>$person_id, 'transaction_subtype' => $transaction_subtype, 'limit'=>10000));
		
		// format data
		$this->load_data($report_data, $summary_data, $details_data);

		// load output data elements
		$customer_info = $this->Customer->get_info($person_id);
		$title = $this->lang->line('reports_report').' '.$this->lang->line('common_from_1').' '.strtoupper($customer_info->last_name).'  '.ucfirst(strtolower($customer_info->first_name));
		$overall_summary_data = $this->Specific_customer->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'person_id' =>$person_id, 'transaction_subtype' => $transaction_subtype));
		
		//$overall_summary_data = $this->Specific_customer->getSummaryDataZ(array('start_date'=>$start_date, 'end_date'=>$end_date, 'person_id' =>$person_id, 'transaction_subtype' => $transaction_subtype));

		$this->output_load_data($data, $title, $subtitle, $summary_data, $details_data, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);

		// show the data or create excel
		if ($export_excel == 1)
		{
			$this->load->model('Common_routines');
			$this->Common_routines->create_csv($data, $month_end=0, $specific=1);
			// set message
			$_SESSION['error_code']										=	'05460';
			unset($_SESSION['show_dialog']);
			redirect("reports");
		}
		else
		{
			$this->load->view("reports/tabular_details", $data);
		}
	}
	
	function specific_category($start_date, $end_date, $category_id, $transaction_subtype, $export_excel=0)
	{				
		// clear data arrays
		$this->clear_arrays();
		
		// get report_type (SR or SM)
		$transaction_type = $this->Transaction->get_transaction_type($transaction_subtype);
		
		// load appropriate model
		$this->load->model('reports/Specific_category');
		
		// load report data
		$report_data = $this->Specific_category->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'category_id'=>$category_id, 'transaction_subtype' => $transaction_subtype, 'limit'=>10000));
		
		// format data
		$this->load_data($report_data, $summary_data, $details_data);

		// load output data elements
		$category_info = $this->Category->get_info($category_id);
		$title = $category_info->category_name.' - '.$this->lang->line('reports_report');
		$overall_summary_data = $this->Specific_category->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'category_id'=>$category_id, 'transaction_subtype' => $transaction_subtype));
		//$overall_summary_data = $this->Specific_category->getSummaryDataZ(array('start_date'=>$start_date, 'end_date'=>$end_date, 'category_id'=>$category_id, 'transaction_subtype' => $transaction_subtype));


		$this->output_load_data($data, $title, $subtitle, $summary_data, $details_data, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);

		// show the data or create excel
		if ($export_excel == 1)
		{
			$this->load->model('Common_routines');
			$this->Common_routines->create_csv($data, $month_end=0, $specific=1);
			// set message
			$_SESSION['error_code']										=	'05460';
			unset($_SESSION['show_dialog']);
			redirect("reports");
		}
		else
		{
			$this->load->view("reports/tabular_details", $data);
		}
	}


	function specific_employee($start_date, $end_date, $person_id, $transaction_subtype, $export_excel=0)
	{
		// clear data arrays
		$this->clear_arrays();
		
		// get report_type (SR or SM)
		$transaction_type = $this->Transaction->get_transaction_type($transaction_subtype);
		
		// load appropriate model
		$this->load->model('reports/Specific_employee');
		
		// load report data
		$report_data = $this->Specific_employee->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'person_id'=>$person_id, 'transaction_subtype'=> $transaction_subtype));
		
		// format data
		$this->load_data($report_data, $summary_data, $details_data);

		// load output data elements
		$employee_info = $this->Employee->get_info($person_id);
		$title = strtoupper($employee_info->last_name).', '.ucfirst(strtolower($employee_info->first_name)).' - '.$this->lang->line('reports_report');
		$overall_summary_data = $this->Specific_employee->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'person_id'=>$person_id, 'transaction_subtype'=> $transaction_subtype));
		
		$this->output_load_data($data, $title, $subtitle, $summary_data, $details_data, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);

		// show the data or create excel
		if ($export_excel == 1)
		{
			$this->load->model('Common_routines');
			$this->Common_routines->create_csv($data, $month_end=0, $specific=1);
			// set message
			$_SESSION['error_code']										=	'05460';
			unset($_SESSION['show_dialog']);
			redirect("reports");
		}
		else
		{
			$this->load->view("reports/tabular_details", $data);
		}
	}
	
	function specific_item($start_date, $end_date, $item_id, $transaction_subtype, $export_excel=0, $history=0)
	{				
		// initialise
		$this															->	clear_arrays();
		unset($_SESSION['line']);
		$origin															=	'SI';
		
		// get report_type (SR or SM)
		$transaction_type 												=	$this->Transaction->get_transaction_type($transaction_subtype);
		
		// load appropriate model
		$this															->	load->model('reports/Specific_item');
		
		// set start date - depends on history, if 0 produce all
		if ($history != 0)
		{
			$start_date													=	date('Y-m-d', strtotime('-'.$history.' months'));
		}
		
		// load report data
		$report_data = $this->Specific_item->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'item_id' =>$item_id, 'transaction_subtype' => $transaction_subtype));
		
		// format data
		$this->load_data($report_data, $summary_data, $details_data, $origin);

		// load output data elements
		$item_info														=	$this->Item->get_info($item_id);
		$title															=	$item_info->name.', '.$item_info->item_number.' - '.$this->lang->line('reports_report');
		$overall_summary_data											=	$this->Specific_item->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date,'item_id' =>$item_id, 'transaction_subtype' => $transaction_subtype));
		
		$this->output_load_data($data, $title, $subtitle, $summary_data, $details_data, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type, $history);

		// show the data or create excel
		if ($export_excel == 1)
		{
			$this->load->model('Common_routines');
			$this->Common_routines->create_csv($data, $month_end=0, $specific=1);
			// set message
			$_SESSION['error_code']										=	'05460';
			unset($_SESSION['show_dialog']);
			redirect("reports");
		}
		else
		{
			$this->load->view("reports/tabular_details", $data);
		}
	}
		
	function detailed_transactions($start_date, $end_date, $transaction_subtype, $transaction_sortby, $export_excel=0, $month_end=0)								
	{		
		
		// clear data arrays
		$this->clear_arrays();
				
		// get report_type (SR or SM)    //SR: Sales    //facture
		$report_type 													=	$this->Transaction->get_transaction_type($transaction_subtype);
        // (Annuler Transaction) || (Stock -ap) || (Stock -re) : SM   (Avoir) || (Facture) : SR
		// load appropriate model parameters
		switch ($report_type)
		{
			case 'SM':
				// set model
				$model_path 											=	'reports/Detailed_receivings';
				$model 													=	'Detailed_receivings';
				break;
			default:
				// set model
				$model_path												=	'reports/Detailed_sales';
				$model													=	'Detailed_sales';
				break;
		}
		
		// need to load the model because it is not autoloaded in autoload.php
		$this->load->model($model_path);
		
		// load report data
		$report_data = $this->$model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));

		// format data
		$this->load_data($report_data, $summary_data, $details_data);
		
		// load output data elements
		$title = $this->lang->line('reports_detailed_transactions_report');
		
		//Total summary
		$overall_summary_data = $this->$model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
		$this->output_load_data($data, $title, $subtitle, $summary_data, $details_data, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);

		// show the data or create excel
		if ($export_excel == 1)
		{
			$this->load->model('Common_routines');
			$this->Common_routines->create_csv($data, $month_end=0, $specific=1);
			// set message
			$_SESSION['error_code']										=	'05460';
			unset($_SESSION['show_dialog']);
			redirect("reports");
		}
		else
		{
			$this->load->view("reports/tabular_details", $data);
			//$this->load->view("reports/tabular", $data);
		}
	}


	function transition()
	{
		//
		$_SESSION['rapport_detaille_fournisseur']='1';
		$data=$this-> _get_common_report_data_by_supplier();
		// set the excel flag
		$data['export_to_excel_allowed'] = 'yes';
		$this->load->view("reports/date_input_excel_export_fournisseur", $data);
	}
	function transition_category()
	{
		//
		$_SESSION['rapport_detaille_category']='1';
		$data=$this-> _get_common_report_data_by_category();
		// set the excel flag
		$data['export_to_excel_allowed'] = 'yes';
		$this->load->view("reports/date_input_excel_export_category", $data);
	}


	function rapport_detaille_fournisseur()    //$start_date, $end_date, $transaction_subtype, $transaction_sortby, $export_excel=0, $month_end=0)
	{
		if(!isset($_SESSION['rapport_detaille_fournisseur']))
        {
    		$_SESSION['rapport_detaille_fournisseur']='2';
    	}
    	switch($_SESSION['rapport_detaille_fournisseur'])
    	{
    		case '1':
    			 $_SESSION['rapport_detaille_fournisseur']='2';
    			 
    			// clear data arrays
    			$this->clear_arrays();    
    
    			switch($_POST['report_type'])
                {
    				case 'simple':
    					list($start_date, $end_date)=explode("/",$_POST['report_date_range_simple']);
    				    break;
    				case 'complex':
    				    //$start_date=$_POST['start_year']. '-' .$_POST['start_month']. '-' .$_POST['start_day'];    //annee-mois-jour
    					//$end_date=$_POST['end_year']. '-' .$_POST['end_month']. '-' .$_POST['end_day'];    //annee-mois-jour
						$start_date=$_POST['start'];
						$end_date=$_POST['end'];
						
						break;
    			}
                
    			$transaction_subtype=$_POST['transaction_subtype'];
    			$transaction_sortby=$_POST['transaction_sortby'];
    			
                $export_excel=0;
    			$month_end=0;
    
    			 // get report_type (SR or SM)    //SR: Sales    //facture
    			 $report_type 													=	$this->Transaction->get_transaction_type($transaction_subtype);
    
    			 //Pour les factures
    			 // load appropriate model parameters
    			 switch ($report_type)
    			 {
    				 case 'SM':
    					 // set model
    					 $model_path 											=	'reports/Detailed_receivings';
    					 $model 													=	'Detailed_receivings';
    					 break;
    				 default:
    					 // set model
    
    					 //Pour faire la différence entre une facture et un avoir
    					 switch($transaction_subtype)
    					 {
    						case 'sales':
    						    //Facture
    						    $model_path												=	'reports/Detailed_sales';
    						    $model													=	'Detailed_sales';
    						    break;
    						case 'returns':
    							//Avoir
    							$model_path = 'reports/summary_sales_categories';
    							$model = 'summary_sales_categories';
    							break;
    						}
    					 
    					 break;
    			 }
    			 // need to load the model because it is not autoloaded in autoload.php
    			 $this->load->model($model_path);
    
    			 // load report data
    			 $report_data = $this->$model->getData_by_suppliers(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype, 'transaction_sortby' => $transaction_sortby)); //, 'list_item_by_supplier' => $list_item_by_supplier));	 
    			 
    
    			 switch($transaction_subtype)
    			 {
    				case 'sales':
    					//Facture
    					//Load data 
    			        $details_data_info=array();
    			        $this->load_data_by_supplier($report_data, $details_data_info);
    			        
    			        // load output data elements
    			        $title = $this->lang->line('reports_detailed_transactions_report_by_supplier');
    	        
    			        //Total summary
    //			        $overall_summary_data = $this->$model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
    			        $overall_summary_data = $this->$model->getSummaryData_by_supplier(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype, 'transaction_sortby' => $transaction_sortby));
    			        
    			        //
    			        $this->output_load_data_by_supplier($data, $title, $subtitle, $details_data_info, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);
           
    			        // show the data or create excel
    			        if ($export_excel == 1)
    			        {
    			   	     $this->load->model('Common_routines');
    			   	     $this->Common_routines->create_csv($data, $month_end=0, $specific=1);
    			   	     // set message
    			   	     $_SESSION['error_code']										=	'05460';
    			   	     unset($_SESSION['show_dialog']);
    			   	     redirect("reports");
    			        }
    			        else
    			        {
    			   	     $this->load->view("reports/tabular_details_tests", $data);
    			        }
       
    			   		break;
    			   	case 'returns':
    					   //Avoir
    					   
    					$details_data_info=array();
    			        $this->load_data_by_supplier($report_data, $details_data_info);
                        // load output data elements
                        $title = $this->lang->line('reports_detailed_transactions_report_by_supplier');
                       	        
                        //Total summary
    //                    $overall_summary_data = $this->$model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
                        $overall_summary_data = $this->$model->getSummaryData_by_supplier(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype, 'transaction_sortby' => $transaction_sortby));
                        
                        //
                        $this->output_load_data_by_supplier($data, $title, $subtitle, $details_data_info, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);
                       
    		            // test to see if CSV file is required
    		            if ($export_excel == 1)
    		            {
    		            	$this->load->model('Common_routines');
    		            	$this->Common_routines->create_csv($data, $month_end=0, $specific=0);
    		            	
    		            	// set message
    		            	$_SESSION['error_code']										=	'05460';
    		            	
    		            	// return to correct place depending on calling function
    		            	if (debug_backtrace()[1]['function'] == 'logout')
    		            	{
    		            		return;
    		            	}
    		            	else
    		            	{
    		            		redirect("reports");
    		            	}
    		            }
    		            else
    		            {
    		            	//$this->load->view("reports/tabular", $data);
    					    $this->load->view("reports/tabular_details_tests", $data);
    					}
    			   		break;
    			}
            break;
    		case '2':
         		$_SESSION['rapport_detaille_fournisseur']='1';
    			$data=$this-> _get_common_report_data_by_supplier();
    		    // set the excel flag
    		    $data['export_to_excel_allowed'] = 'yes';
    	    	$this->load->view("reports/date_input_excel_export_fournisseur", $data);
            break;
    	}
    }

	function rapport_detaille_category()    //$start_date, $end_date, $transaction_subtype, $transaction_sortby, $export_excel=0, $month_end=0)
	{
		if(!isset($_SESSION['rapport_detaille_category']))
        {
    		$_SESSION['rapport_detaille_category']='2';
    	}
    	switch($_SESSION['rapport_detaille_category'])
    	{
    		case '1':
    			 $_SESSION['rapport_detaille_category']='2';
    			 
    			// clear data arrays
    			$this->clear_arrays();    
    
    			switch($_POST['report_type'])
                {
    				case 'simple':
    					list($start_date, $end_date)=explode("/",$_POST['report_date_range_simple']);
    				    break;
    				case 'complex':
    				    //$start_date=$_POST['start_year']. '-' .$_POST['start_month']. '-' .$_POST['start_day'];    //annee-mois-jour
    					//$end_date=$_POST['end_year']. '-' .$_POST['end_month']. '-' .$_POST['end_day'];    //annee-mois-jour
						$start_date=$_POST['start'];
						$end_date=$_POST['end'];
						
						break;
    			}
                
    			$transaction_subtype=$_POST['transaction_subtype'];
    			$transaction_sortby=$_POST['transaction_sortby'];
    			
                $export_excel=0;
    			$month_end=0;
    
    			 // get report_type (SR or SM)    //SR: Sales    //facture
    			 $report_type 													=	$this->Transaction->get_transaction_type($transaction_subtype);
    
    			 //Pour les factures
    			 // load appropriate model parameters
    			 switch ($report_type)
    			 {
    				 case 'SM':
    					 // set model
    					 $model_path 											=	'reports/Detailed_receivings';
    					 $model 													=	'Detailed_receivings';
    					 break;
    				 default:
    					 // set model
    
    					 //Pour faire la différence entre une facture et un avoir
    					 switch($transaction_subtype)
    					 {
    						case 'sales':
    						    //Facture
    						    $model_path												=	'reports/Detailed_sales';
    						    $model													=	'Detailed_sales';
    						    break;
    						case 'returns':
    							//Avoir
    							$model_path = 'reports/summary_sales_categories';
    							$model = 'summary_sales_categories';
    							break;
    						}
    					 
    					 break;
    			 }
    			 // need to load the model because it is not autoloaded in autoload.php
    			 $this->load->model($model_path);
    
    			 // load report data
				 $report_data = $this->$model->getData_by_category(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype, 'transaction_sortby' => $transaction_sortby)); //, 'list_item_by_supplier' => $list_item_by_supplier));	 
    			 
				 //$report_data = $this->$model->getData_by_suppliers(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype, 'transaction_sortby' => $transaction_sortby)); //, 'list_item_by_supplier' => $list_item_by_supplier));	 
    			 
    
    			 switch($transaction_subtype)
    			 {
    				case 'sales':
    					//Facture
    					//Load data 
    			        $details_data_info=array();
    			        $this->load_data_by_supplier($report_data, $details_data_info);
    			        
    			        // load output data elements
    			        $title = $this->lang->line('reports_detailed_transactions_report_by_category');
    	        
    			        //Total summary
    //			        $overall_summary_data = $this->$model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
    			        $overall_summary_data = $this->$model->getSummaryData_by_category(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype, 'transaction_sortby' => $transaction_sortby));
    			        
    			        //
    			        $this->output_load_data_by_supplier($data, $title, $subtitle, $details_data_info, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);
           
    			        // show the data or create excel
    			        if ($export_excel == 1)
    			        {
    			   	     $this->load->model('Common_routines');
    			   	     $this->Common_routines->create_csv($data, $month_end=0, $specific=1);
    			   	     // set message
    			   	     $_SESSION['error_code']										=	'05460';
    			   	     unset($_SESSION['show_dialog']);
    			   	     redirect("reports");
    			        }
    			        else
    			        {
    			   	     $this->load->view("reports/tabular_details_tests", $data);
    			        }
       
    			   		break;
    			   	case 'returns':
    					   //Avoir
    					   
    					$details_data_info=array();
    			        $this->load_data_by_supplier($report_data, $details_data_info);
                        // load output data elements
                        $title = $this->lang->line('reports_detailed_transactions_report_by_supplier');
                       	        
                        //Total summary
    //                    $overall_summary_data = $this->$model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
                        $overall_summary_data = $this->$model->getSummaryData_by_supplier(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype, 'transaction_sortby' => $transaction_sortby));
                        
                        //
                        $this->output_load_data_by_supplier($data, $title, $subtitle, $details_data_info, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);
                       
    		            // test to see if CSV file is required
    		            if ($export_excel == 1)
    		            {
    		            	$this->load->model('Common_routines');
    		            	$this->Common_routines->create_csv($data, $month_end=0, $specific=0);
    		            	
    		            	// set message
    		            	$_SESSION['error_code']										=	'05460';
    		            	
    		            	// return to correct place depending on calling function
    		            	if (debug_backtrace()[1]['function'] == 'logout')
    		            	{
    		            		return;
    		            	}
    		            	else
    		            	{
    		            		redirect("reports");
    		            	}
    		            }
    		            else
    		            {
    		            	//$this->load->view("reports/tabular", $data);
    					    $this->load->view("reports/tabular_details_tests", $data);
    					}
    			   		break;
    			}
            break;
    		case '2':
         		$_SESSION['rapport_detaille_category']='1';
    			$data=$this-> _get_common_report_data_by_supplier();
    		    // set the excel flag
    		    $data['export_to_excel_allowed'] = 'yes';
    	    	$this->load->view("reports/date_input_excel_export_category", $data);
            break;
    	}
    }	
function rapport_detaille_famille1($start_date, $end_date, $transaction_subtype, $transaction_sortby, $export_excel=0, $month_end=0)								
{	
	
	// clear data arrays
	$this->clear_arrays();
			
	// get report_type (SR or SM)
	$report_type 													=	$this->Transaction->get_transaction_type($transaction_subtype);
	
	// load appropriate model parameters
	switch ($report_type)
	{
		case 'SM':
			// set model
			$model_path 											=	'reports/Detailed_receivings';
			$model 													=	'Detailed_receivings';
			break;
		default:
			// set model
			$model_path												=	'reports/Detailed_sales';
			$model													=	'Detailed_sales';
			break;
	}
	
	// need to load the model because it is not autoloaded in autoload.php
	$this->load->model($model_path);
	
	// load report data
	$report_data = $this->$model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));

	// format data
	$this->load_data($report_data, $summary_data, $details_data);
	
	// load output data elements
	$title = $this->lang->line('reports_detailed_transactions_report');
	$overall_summary_data = $this->$model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
	$this->output_load_data($data, $title, $subtitle, $summary_data, $details_data, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);

	// show the data or create excel
	if ($export_excel == 1)
	{
		$this->load->model('Common_routines');
		$this->Common_routines->create_csv($data, $month_end=0, $specific=1);
		// set message
		$_SESSION['error_code']										=	'05460';
		unset($_SESSION['show_dialog']);
		redirect("reports");
	}
	else
	{
		$this->load->view("reports/tabular_details", $data);
	}
	redirect("reports");
}

//////////////////////////////

	function rapport_detaille_famille()
	{
		redirect("reports");
	}
	
	// -----------------------------------------------------------------------------------------				
	// Inventory reports - set up the data and display
	// -----------------------------------------------------------------------------------------
	
	function inventory_low_get_data()
	{
		$this->load->view("reports/inventory_low");
	}
	
	function inventory_low_validation()
	{
		// initialise
		$supplier_id 													=	$this->input->post('supplier_id');
		$export_excel													=	0;
		$set_NM															=	0;
		$set_SM															=	0;
		$month_end														=	0;
		
		// if coming from the stok module, set the create PO to 'Y'
		if ($_SESSION['origin'] == "CA")
		{
			$create_PO													=	'Y';
		}
		else
		{
			$create_PO													=	$this->input->post('create_po');
		}
		
		// validate supplier
		if (!$this->Supplier->exists($supplier_id))
		{
			// set message
			$_SESSION['error_code']										=	'05260';
			redirect("reports/inventory_low_get_data");
		}
		
		// set create_PO
		if ($create_PO == 'Y')
		{
			$create_PO													=	1;
		}
		else
		{
			$create_PO													=	0;
		}
		
		// produce the report
		$this->															inventory_low($export_excel, $create_PO, $set_NM, $set_SM, $supplier_id, $month_end);
	}

	//création du bon de commande automatique en utilisant les ventes sur une période (entre 2 dates)
	function inventory_low_validation_by_date()
	{ 
		//delete variables 
		unset($_SESSION['Stock_only']);
		unset($_SESSION['display_ventes']);
		unset($_SESSION['report_data_receiving']);

/*		//Récupération des valeurs de configuration de historique et du nombre de jours de prévision
		$redirect = 'reports';
		$conn_parms = array();

		//initialisation des parametres pour la connexion
		$conn_parms		=	$this->Common_routines->get_conn_parms($redirect);
		$conn			=	$this->Common_routines->open_db($conn_parms);

   		//Requête SQL
		$sql_config_historique = "SELECT * FROM `ospos_app_config` WHERE `key` = 'historique' ";						
		$result_config_historique = $conn->query($sql_config_historique);
		$sql_nbre_jour_prevision_stock = "SELECT * FROM `ospos_app_config` WHERE `key` = 'nbre_jour_prevision_stock' ";
		$result_nbre_jour_prevision_stock = $conn->query($sql_nbre_jour_prevision_stock);

		//récuperation de la ligne
		$request_config_historique = $result_config_historique->fetch_assoc();
		$request_nbre_jour_prevision_stock = $result_nbre_jour_prevision_stock->fetch_assoc();
		//*/

		$input['key'] = 'historique';
		$data_historique_quantity = $this->Appconfig->get_app_config($input);
		$request_config_historique = $data_historique_quantity[0];

		$input['key'] = 'nbre_jour_prevision_stock';
		$data_nbre_jour_prevision_stock_quantity = $this->Appconfig->get_app_config($input);
		$request_nbre_jour_prevision_stock = $data_nbre_jour_prevision_stock_quantity[0];

		$_SESSION['historique'] = $request_config_historique['value'];
		$_SESSION['nbre_jour_prevision_stock'] = $request_nbre_jour_prevision_stock['value'];
		
		// initialise
		$supplier_id 													=	$this->input->post('supplier_id');
		$_SESSION['supplier_id']										=	$supplier_id;
		//Pour récupérer la méthodologie à utiliser afin de générer le bon de commande automatiquement
		
		$_SESSION['historique_correct'] = $_POST['historique_correct'];
		$_SESSION['historique_correct_stay'] = $_SESSION['historique_correct'];
		$_SESSION['nbre_jour_prevision_stock_correct'] = $_POST['nbre_jour_prevision_stock_correct'];
		$_SESSION['nbre_jour_prevision_stock_correct_stay'] = $_SESSION['nbre_jour_prevision_stock_correct'];
		
        if($_POST['nbre_jour_prevision_stock_correct']=="")
        {
			//$_SESSION['show_dialog'] = 0;
			$_SESSION['error_code'] = '07320';
			$_SESSION['error_code_0'] = 1;
        	redirect("receivings");
            return;
        }
        
		switch($_POST['metho'])
		{
			case 'metho_by_stock':
				
			    if($_POST['historique_correct']=="")
			    {
			    	//$_SESSION['show_dialog'] = 0;
			    	$_SESSION['error_code'] = '07320';
			    	redirect("receivings");
			    	return;
				}
				$_SESSION['display_ventes'] = 1;
			    //Si la méthodologie à adopter est celle lié au stock alors il faut exécuter l'ancienne foncion
			    $this->inventory_low_validation();
			break;

			case 'metho_by_sales':
			    //Si la méthodologie à adopter est celle qui dépant des ventes alors ... to be continus
				$date_start = $_POST['date_start'];
				$date_end = $_POST['date_end'];
				$_SESSION['display_ventes'] = 1;
			break;

			case 'metho_by_only_stock':
			    //Approvisionnement dans le but de mettre les stocks au minimum (seuil de stock minimum)
		    	$this->inventory_low_validation_only_stock();
			break;
		}
	
		$export_excel													=	0;
		$set_NM															=	0;
		$set_SM															=	0;
		$month_end														=	0;
		
		// if coming from the stok module, set the create PO to 'Y'
		if ($_SESSION['origin'] == "CA")
		{
			$create_PO													=	'Y';
		}
		else
		{
			$create_PO													=	$this->input->post('create_po');
		}
		
		// validate supplier
		if (!$this->Supplier->exists($supplier_id))
		{
			// set message
			$_SESSION['error_code']										=	'05260';
			redirect("reports/inventory_low_get_data");
		}
		
		// set create_PO
		if ($create_PO == 'Y')
		{
			$create_PO													=	1;
		}
		else
		{
			$create_PO													=	0;
		}
		
		// produce the report
		$this->															inventory_low_by_date($export_excel, $create_PO, $set_NM, $set_SM, $supplier_id, $month_end, $date_start, $date_end);
	}

	function inventory_low_validation_only_stock()
	{
		// initialise
		$supplier_id 													=	$_SESSION['supplier_id'];
		$export_excel													=	0;
		$set_NM															=	0;
		$set_SM															=	0;
		$month_end														=	0;
		$_SESSION['Stock_only'] = 1;
		
		// if coming from the stok module, set the create PO to 'Y'
		if ($_SESSION['origin'] == "CA")
		{
			$create_PO													=	'Y';
		}
		else
		{
			$create_PO													=	$this->input->post('create_po');
		}
		
		// validate supplier
		if (!$this->Supplier->exists($supplier_id))
		{
			// set message
			$_SESSION['error_code']										=	'05260';
			redirect("reports/inventory_low_get_data");
		}
		
		// set create_PO
		if ($create_PO == 'Y')
		{
			$create_PO													=	1;
		}
		else
		{
			$create_PO													=	0;
		}
		
		// produce the report
		$this->															inventory_low_only_stock($export_excel, $create_PO, $set_NM, $set_SM, $supplier_id, $month_end);
	}

	// Since we now have over 1 year of sales history we can use it to create the reorder quantities
	// by calculating the average weekly sales since shop opening. Remember PO cycle is weekly.
	
	function inventory_low($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $supplier_id=0, $month_end=0)
	{		
		// initialise
		$edit_file 							= 	'items/view/';
		$origin								=	'IL';
		$_SESSION['origin']					=	'IL';
		$tabular_data 						= 	array();
		$reorder_total 						= 	0;
		$cart_count 						= 	0;
		$inputs								=	array();
		$inputs['where']					= 	"ospos_items.deleted = 0 AND ospos_items_suppliers.supplier_reorder_policy = 'Y' AND ospos_items_suppliers.supplier_reorder_quantity > 0 AND ospos_items_suppliers.supplier_id = $supplier_id";
		$inputs['report']					=	'IL';
		if ($create_PO == 1)
		{
			$this							->	receiving_lib->clear_all();
			$this							->	receiving_lib->set_supplier($supplier_id);
		}
		$this								->load->library('../controllers/items');
		$history							=	$_SESSION['historique_correct']; //$_SESSION['historique'];
		//$start_date							=	date('Y/m/d', strtotime('-'.$history.' months'));
		$start_date							=	date('Y/m/d', strtotime('-'.$history.' day'));
		$start								= 	DateTime::createFromFormat('Y/m/d', $start_date);
		$today								=	date('Y/m/d');
		$now								= 	DateTime::createFromFormat('Y/m/d', $today);
		$weeks								=	floor($start->diff($now)->days/7);
		
		// initialise by zeroing reorder_level and reorder_quantity for this supplier
		$item_update_data									=	array	(
																		'supplier_reorder_level'		=>	0,
																		'supplier_reorder_quantity'		=>	0
																		);								
		$this															->	db->where('deleted', 0);
		$this															->	db->where('items_suppliers.supplier_id', $supplier_id);
		$this															->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		$this															->	db->update('items_suppliers', $item_update_data);
		
		// FIRST - calculate reorder quantity item by item and update items accordingly for items from the selected supplier
		
		// calculate the reorder quantity using the historic sales values per item.
		// get items
		// set switch for get_all routine
		$_SESSION['undel']												=	2;
		$_SESSION['supplier_id']										=	$supplier_id;
		// now get the items
		// this will join items_suppliers and select only active, repro policy = Y and items for this supplier
		$item_data														=	array();
		$item_data														=	$this->Item->get_all()->result_array();

		// read items
		foreach ($item_data as $item_row)
		{						
			// get historic sales data for period from $start_date to $today for this item
			$total														=	$this->Sale->get_sales_items_by_item_id_and_date($start_date, $today, $item_row['item_id'])->result_array();
			//$_SESSION['ventes_for_approv_qty']=$total[0]['total_sold_in_period'];

			// check sales qty and set reorder level and quantity accordingly
			if ($total[0]['total_sold_in_period'] <= 0)
			{
				// no historic sales in period so zero reorder level and reorder quantity
				// new items added will have to be manually handled in POs until sales happens.
				$reorder_level											=	0;
				$reorder_quantity										=	0;
			}
			else
			{
				// we have historic sales data so calculate average weekly sales * 3 for safety stock
//				$reorder_level											=	ceil(($total[0]['total_sold_in_period'] / $weeks) *3);
				$reorder_level											=	ceil(($total[0]['total_sold_in_period'] / $_SESSION['historique_correct']) *($_SESSION['nbre_jour_prevision_stock_correct']));
				
				// now subtract qty on hand - MAKE ZERO IF QUANTITY ON-HAND NEGATIVE
				if ($item_row['quantity'] < 0) {$item_row['quantity'] = 0;}
				$reorder_quantity										=	$reorder_level - $item_row['quantity'];
				
				// if reorder quantity is <= 0, then I have enough stock on hand, so don't order.
				if ($reorder_quantity <= 0)
				{
					$reorder_quantity									=	0;
				}
				
				// now check qty on hand + reorder qty against min_stock_qty
				if (($item_row['quantity'] + $reorder_quantity) < $item_row['supplier_min_stock_qty'])
				{
					$qty_to_min			=	$item_row['supplier_min_stock_qty'] - ($item_row['quantity'] + $reorder_quantity);
					$reorder_quantity	=	$reorder_quantity + $qty_to_min;
					//$reorder_quantity = $reorder_quantity + $item_row['supplier_min_stock_qty'] - $item_row['quantity'] -$reorder_quantity;
					//$reorder_quantity = $item_row['supplier_min_stock_qty'] - $item_row['quantity'];
				}
				
				// now check pack qty and adjust reorder quantity
				if ($reorder_quantity	>	0)
				{
					$number_packs										=	ceil($reorder_quantity / $item_row['supplier_reorder_pack_size']);
					$reorder_quantity									=	$number_packs * $item_row['supplier_reorder_pack_size'];
				}						
			}

			// update item record with reorder level and reorder quantity
			$item_update_data											=	array	(
																					'supplier_reorder_level'		=>	$reorder_level,
																					'supplier_reorder_quantity'		=>	$reorder_quantity
																					);							
			
			$this														->	db->where('item_id', $item_row['item_id']);
			$this														->	db->where('items_suppliers.supplier_id', $supplier_id);
			$this														->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
			$this														->	db->update('items_suppliers', $item_update_data);
		// get next item
		}
		
		// SECOND - produce the report
		
		// initialise
		$this															->	load->library('../controllers/items');
		$this															->	load->model('Item');

		// set up parms
		$limit															=	100000;
		$offset															=	0;
		
		// load data
		// this will join items_suppliers and select only active, repro policy = Y, items for this supplier and reorder qty > 0
		$_SESSION['report_data_receiving'] 										= 	$this->Stock_queries->getData($inputs);

		// read report data
		unset($_SESSION['line']);
		foreach($_SESSION['report_data_receiving'] as $index=>$row)
		{		
			$total														=	$this->Sale->get_sales_items_by_item_id_and_date($start_date, $today, $row['item_id'])->result_array();
			$_SESSION['ventes_for_approv_qty']=$total[0]['total_sold_in_period'];
	
			// calc output fields
			$reorder_value 												= 	$row['supplier_cost_price'] * $row['supplier_reorder_quantity'];
			$reorder_total 												= 	$reorder_total + $reorder_value;				
	
			// get category name
			$category_info												=	$this->Category->get_info($row['category_id']);
			
/*			// create the output data table
			$tabular_data[] 											= 	array	(
																					$category_info->category_name,	
																					$row['item_number'],
																					$row['name'],
																					$row['supplier_cost_price'], 
																					$row['quantity'],
																					$row['supplier_reorder_level'], 
																					$row['supplier_reorder_quantity'],
																					$reorder_value, 
																					$reorder_total
																					);//*/
			
			// now load the cart
			if ($create_PO == 1)
			{		
				$this->receiving_lib->add_item($row['item_id'], $row['supplier_reorder_quantity'], $row['supplier_cost_price']);
			}
		}
		
		// set subtitle
		$today_date														=	date('d/m/Y à H:i:s', time());
		if ($create_PO == 1)
		{
				$subtitle = $today_date.' '.$this->lang->line('common_for').'  '.$this->db->database.'  Total = '.$reorder_total.'€'.' '."<font color='red'>".$this->lang->line('reports_low_inventory_PO')."</font><br>";
		}
		else
		{
				$subtitle = $today_date.' '.$this->lang->line('common_for').'  '.$this->db->database.'  Total = '.$reorder_total.'€';
		}
		/*
		// load data array
		$data = array	(
						"title" 		=> $this->lang->line('reports_low_inventory_report'),
						"subtitle" 		=> $subtitle,
						"headers" 		=> $this->Stock_queries->getDataColumns($inputs),
						"data" 			=> $tabular_data,
						"summary_data" 	=> $this->Stock_queries->getSummaryData(array()),
						"export_excel" 	=> $export_excel
						);//*/
						
		// redirect to receivings module if PO create was ordered
		if ($create_PO == 1)
		{
			// set stock action to PO
			$_SESSION['stock_action_id']								=	10;
			// set title
			$_SESSION['title']											=	$this->lang->line('receivings_stock_create');
			// set mode
			$this														->	receiving_lib->set_mode("purchaseorder");
			// set comments to blank
			$this->session->set_userdata('comment', NULL);
			// set supplier to default if not already set
			$supplier_id												=	$this->receiving_lib->get_supplier();
			if ($supplier_id == -1)
			{
				$this													->	receiving_lib->set_supplier($this->config->item('default_supplier_id'));
			}
			// reset show_dialog
			$_SESSION['show_dialog']									=	0;
			// reload
			redirect													("receivings");
		}
		
		// redirect normally
		//$this															->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}
	
	function inventory_low_by_date($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $supplier_id=0, $month_end=0, $date_start, $date_end)
	{	
		//Récupération de tous les item_id et quantity_purchased de toutes les factures entre les 2 dates souhaitées
		$inputs_sql=array();
		$inputs= array('date_start' => $date_start , 'date_end' => $date_end); //, 'fournisseur' => $supplier_id);
		$articles_entre_2_dates = $this->Item->get_all_between_2_date($inputs);

		//Récupération du nombre de jours écoulés 
		$date_0 = strtotime($inputs['date_start']);
		$date_1 = strtotime($inputs['date_end']);
		$nombre_jour_historique=($date_1-$date_0)/(3600*24);


		// initialise
		$edit_file 							= 	'items/view/';
		$origin								=	'IL';
		$_SESSION['origin']					=	'IL';
		$tabular_data 						= 	array();
		$reorder_total 						= 	0;
		$cart_count 						= 	0;
		$inputs								=	array();

		//where pour avoir les articles actifs avec le paramétre 'Y' affiliés au fournisseur sélectionné
		//$inputs['where']					= 	"ospos_items.deleted = 0 AND ospos_items_suppliers.supplier_reorder_policy = 'Y' AND ospos_items_suppliers.supplier_reorder_quantity > 0 AND ospos_items_suppliers.supplier_id = $supplier_id";
		
		$inputs['report']					=	'IL';
		if ($create_PO == 1)
		{
			$this							->	receiving_lib->clear_all();
			$this							->	receiving_lib->set_supplier($supplier_id);
		}
		$this								->	load->library('../controllers/items');
	
		// initialise by zeroing reorder_level and reorder_quantity for this supplier
		// mise à jour des valeurs: reorder_level et reorder_quantity pour le fournisseur concerné
		$item_update_data									=	array	(
																		'supplier_reorder_level'		=>	0,
																		'supplier_reorder_quantity'		=>	0
																		);								
		$this															->	db->where('deleted', 0);
		$this															->	db->where('items_suppliers.supplier_id', $supplier_id);
		$this															->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		$this															->	db->update('items_suppliers', $item_update_data);
		
		// FIRST - calculate reorder quantity item by item and update items accordingly for items from the selected supplier
		
		// calculate the reorder quantity using the historic sales values per item.
		// get items
		// set switch for get_all routine
		$_SESSION['undel']												=	2;
		$_SESSION['supplier_id']										=	$supplier_id;

		// SECOND - produce the report
		
		// initialise
		$this															->	load->library('../controllers/items');
		$this															->	load->model('Item');

		// set up parms
		$limit															=	100000;
		$offset															=	0;
		
		foreach($articles_entre_2_dates as $index => $row)
		{
			$_SESSION['ventes_for_approv_qty']=$row['somme'];
		    $row['somme']=ceil(($row['somme']*$_SESSION['nbre_jour_prevision_stock_correct'])/$nombre_jour_historique);
			$item_all = $this->Item->get_info_by_date($row['item_id']);
			if(intval($item_all[0]['deleted']) == 0 && isset($item_all[0]))
			{
    		    $supplier_cost_price_by_date=$this->Item->get_info_supplier_cost_price_by_date($row['item_id']);
			    if($row['somme']>=$supplier_cost_price_by_date[0]['supplier_min_order_qty'])
			    {
                    if($supplier_cost_price_by_date[0]['supplier_min_order_qty'] != "0")
			    	{			
    		    		$compteur=1;
    		    		while($row['somme']>$supplier_cost_price_by_date[0]['supplier_reorder_pack_size'])
    		    		{
    		    			$compteur+=1;
    		    			$row['somme']=$row['somme']-$supplier_cost_price_by_date[0]['supplier_reorder_pack_size'];
    		    		}
    		    		if($compteur==1)
    		    		{
    		        		//if($row['somme']<=$supplier_cost_price_by_date[0]['supplier_reorder_pack_size'])
    		        		//{
    		        		$row['somme']=$supplier_cost_price_by_date[0]['supplier_reorder_pack_size'];
    		        		//}
    		    		}
    		    		if($compteur!=1)
    		    		{
    		        		if($row['somme']<=$supplier_cost_price_by_date[0]['supplier_reorder_pack_size'])
    		        		{
    		        			$row['somme']=$supplier_cost_price_by_date[0]['supplier_reorder_pack_size'] * $compteur;
    		        		}
    		    		}
    		    		
    		    	    if ($create_PO == 1)
    		    	    {
    		    	    	$this->receiving_lib->add_item_by_date($row['item_id'], $row['somme'], $supplier_cost_price_by_date[0]['supplier_cost_price']);
    		    		}
			        }
			    }
			}
		}

		// redirect to receivings module if PO create was ordered
		if ($create_PO == 1)
	    {
		    // set stock action to PO
		    $_SESSION['stock_action_id']								=	10;
		    // set title
		    $_SESSION['title']											=	$this->lang->line('receivings_stock_create');
		    // set mode
		    $this														->	receiving_lib->set_mode("purchaseorder");
		    // set comments to blank
		    $this->session->set_userdata('comment', NULL);
		    // set supplier to default if not already set
		    $supplier_id												=	$this->receiving_lib->get_supplier();
		    if ($supplier_id == -1)
		    {
		    	$this													->	receiving_lib->set_supplier($this->config->item('default_supplier_id'));
		    }
		    // reset show_dialog
		    $_SESSION['show_dialog']									=	0;
		    // reload
		    redirect("receivings");
	    }
	}
	

	function inventory_low_only_stock($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $supplier_id=0, $month_end=0)
	{		
		// initialise
		$edit_file 							= 	'items/view/';
		$origin								=	'IL';
		$_SESSION['origin']					=	'IL';
		$reorder_total 						= 	0;
		$cart_count 						= 	0;
		$inputs								=	array();
		$inputs['where']					= 	"ospos_items.deleted = 0 AND ospos_items_suppliers.supplier_reorder_policy = 'Y' AND ospos_items_suppliers.supplier_reorder_quantity > 0 AND ospos_items_suppliers.supplier_id = $supplier_id";
		$inputs['report']					=	'IL';
		$supplier_id = $_SESSION['supplier_id'];
		if ($create_PO == 1)
		{
			$this							->	receiving_lib->clear_all();
			$this							->	receiving_lib->set_supplier($supplier_id);
		}
		$this								->load->library('../controllers/items');

		$_SESSION['undel']												=	2;
		$_SESSION['supplier_id']										=	$supplier_id;
		$items														=	array();
		$items														=	$this->Item->get_items_only_stock()->result_array();

		$this															->	load->library('../controllers/items');
		$this															->	load->model('Item');

		foreach ($items as $item_row)
		{
		    //Si la quantité en stock est inférieur à la quantité minimum de stock
			if(intval($item_row['quantity']) < intval($item_row['supplier_min_stock_qty']))
		    {
				$new_quantity_seuil_negatif = 0;

				//Si la quantité en stock est négatif alors on récupére la valeur négative
				if(intval($item_row['quantity']) < 0)
				{
					$new_quantity_seuil_negatif = -intval($item_row['quantity']);
					$item_row['quantity'] = 0;
				}

				//Si la quantité en stock est positive ou nulle alors on récupére la quantité qu'il faut commander pour atteindre le stock minimum 
				if(intval($item_row['quantity']) >= 0)
				{
					$new_quantity_seuil = intval($item_row['supplier_min_stock_qty']) - intval($item_row['quantity']);
				}

				//Quantité total à commander
				$new_quantity_seuil_total = $new_quantity_seuil + $new_quantity_seuil_negatif;
				if($new_quantity_seuil_total < intval($item_row['supplier_reorder_pack_size'])) // && ($new_quantity_seuil_total > 0) ) //&& ($new_quantity_seuil_negatif == 0))
				{
					$item_row['supplier_reorder_quantity'] = $item_row['supplier_reorder_pack_size'];
				}
				else
				{
					$compteur = 0;
					while($new_quantity_seuil_total > 0 )
					{
						$compteur +=1;
						$new_quantity_seuil_total = $new_quantity_seuil_total - intval($item_row['supplier_reorder_pack_size']);
					}
					$item_row['supplier_reorder_quantity'] = intval($item_row['supplier_reorder_pack_size']) * $compteur;
				}
				/*
                $item_row['supplier_reorder_quantity'] = (intval($item_row['quantity']) < 0) ? -intval($item_row['quantity']) : intval($item_row['quantity']);
				do{	$item_row['supplier_reorder_quantity'] += $item_row['supplier_reorder_pack_size'];
				}while($item_row['supplier_reorder_quantity'] < intval($item_row['supplier_min_stock_qty']));
				//*/
				
				//$item_row['supplier_reorder_quantity'] = intval($item_row['supplier_min_stock_qty']) - intval($item_row['quantity']);
				
				if($item_row['supplier_min_order_qty'] <= $item_row['supplier_reorder_quantity'])
				{
				    // now load the cart
				    if ($create_PO == 1)
				    {		
				    	$this->receiving_lib->add_item($item_row['item_id'], $item_row['supplier_reorder_quantity'], $item_row['supplier_cost_price']);
				    }
			    }
			}
	    }

		// set subtitle
		$today_date														=	date('d/m/Y à H:i:s', time());
		if ($create_PO == 1)
		{
				$subtitle = $today_date.' '.$this->lang->line('common_for').'  '.$this->db->database.'  Total = '.$reorder_total.'€'.' '."<font color='red'>".$this->lang->line('reports_low_inventory_PO')."</font><br>";
		}
		else
		{
				$subtitle = $today_date.' '.$this->lang->line('common_for').'  '.$this->db->database.'  Total = '.$reorder_total.'€';
		}
						
		// redirect to receivings module if PO create was ordered
		if ($create_PO == 1)
		{
			// set stock action to PO
			$_SESSION['stock_action_id']								=	10;
			// set title
			$_SESSION['title']											=	$this->lang->line('receivings_stock_create');
			// set mode
			$this														->	receiving_lib->set_mode("purchaseorder");
			// set comments to blank
			$this->session->set_userdata('comment', NULL);
			// set supplier to default if not already set
			$supplier_id												=	$this->receiving_lib->get_supplier();
			if ($supplier_id == -1)
			{
				$this													->	receiving_lib->set_supplier($this->config->item('default_supplier_id'));
			}
			// reset show_dialog
			$_SESSION['show_dialog']									=	0;
			// reload
			redirect													("receivings");
		}
	}
	
	function inventory_summary($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{
		// initialise
		$this								->	load->library('../controllers/items');
		$this								->	load->model('Item');
		unset($_SESSION['line']);

		// set up parms
		$report_data						=	array();
		$edit_file 							= 	'items/view/';
		$inputs								=	array();
		$inputs['where']					= 	'ospos_items.quantity > 0 and ospos_items.deleted = 0 and ospos_items_suppliers.supplier_preferred=\'Y\' and ospos_items.offer_indicator=\'N\'';
		$inputs['report']					=	'IS';
		$origin								=	'IS';
		
		// set variables
		$tabular_data 						= 	array();
		$edit_file 							= 	'items/view/';
		$stock_total 						= 	0;
		
		// get the report data
		$report_data 						= 	$this->Stock_queries->getData($inputs);

		foreach($report_data as $index=>$row)
		{
			// get stock valuation records
			$stock_value_data				=	$this->Stock_queries->get_stock_value_data($row['item_id']);
			
			// read stock valuation data
			$item_value						=	0;
			$item_qty						=	0;
			
		    $cost_price			 	=	$this->get_item_supplier_cost($row['item_id']); //$this->config->item('default_supplier_id')

			foreach ($stock_value_data as $value_record)
			{
				$item_value					=	$item_value + ($cost_price * $value_record['value_qty']); //$cost_price==$value_record['value_cost_price']
				$item_qty					=	$item_qty + $value_record['value_qty'];
			}
			
			// test quantity correct
			$attn							=	'';
			if ($item_qty != $row['quantity'])
			{
				$attn						=	'ATTN';
			}
			
			$stock_value 					= 	$item_value;
			$stock_total 					= 	$stock_total + $stock_value;

			// set up the item_number to handle blanks
			if ($row['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
			
			// set cost price - cost price can vary depending on cost price at reception as held in stock value records
			// so average the price for this report
			$cost_price						=	round($stock_value / $item_qty, 2);
			
			// set up the output data
			$tabular_data[$index] = array		(
										$row['category'],
										anchor	(
												$edit_file.$row['item_id'].'/'.$origin, 
												$row['item_number']
												),
										$row['reorder_policy'],
										$row['name'], 
										$cost_price,
										$attn, 
										$row['quantity'],
										$item_qty, 
										$stock_value, 
										$stock_total
										);
										
			// Set the index for the line selected by the user
			if ($row['item_id'] == $_SESSION['sel_item_id'])
			{
				$_SESSION['line']		=	$index;
			}
		}
		
		// get date and time
		$today_date						=	date('d/m/Y à H:i:s', time());
		
		// get the number format -->
		$pieces	=	array();
		$pieces	= 	explode("/", $this->config->item('numberformat'));
		
		$data = array	(
						"title" 		=> 	$this->lang->line('reports_inventory_summary_report'),
						"subtitle" 		=> 	$today_date.' '.$this->lang->line('common_for').' '.$this->db->database.' = '.number_format($stock_total, $pieces[0], $pieces[1], $pieces[2]).' €',
						"headers"		=> 	$this->Stock_queries->getDataColumns($inputs),
						"data" 			=> 	$tabular_data,
						"summary_data" 	=> 	$this->Stock_queries->getSummaryData(array()),
						"export_excel" 	=> 	$export_excel
						);

		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
		
		return;	
	}
	
	function inventory_nosale($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{
		// articles where there is +ve stock and no sales since start of time.
		
		// load appropriate files
		$this							->	load->library('../controllers/items');
				
		// set up parameters for model
		$inputs							=	array();
		$inputs['where']				= 	"ospos_items.quantity > 0 and ospos_items.deleted = 0 and ospos_items_suppliers.supplier_preferred like 'Y'";
		$inputs['report']				=	'IS';
		$inputs['start_date']			= 	date('Y-m-d', 0);
		$inputs['end_date']				= 	date('Y-m-d');
		
		// set variables
		$tabular_data 					= 	array();
		$edit_file 						= 	'items/view/';
		$width 							= 	$this->items->get_form_width();
		$stock_total 					= 	0;
		$origin							=	'IN';
		
		// get the report data
		$report_data 					= 	$this->Stock_queries->getData($inputs);

		foreach($report_data as $row)
		{	
			// find out if there has been a sale for this item
			$inputs['item_id']			=	$row['item_id'];
			$item_sales_data 			= 	$this->Stock_queries->get_sales_item_Data($inputs);
			
			// test to see if item sales data is empty
			if (empty($item_sales_data['summary']))
			{
				// get item supplier data for item cost using default supplier
				$cost_price 			=	$this->get_item_supplier_cost($row['item_id']); //, $this->config->item('default_supplier_id')
				
				// calculate stock value
				$stock_value = $cost_price * $row['quantity'];
				$stock_total = $stock_total + $stock_value;

				// set up the item_number to handle blanks
				if ($row['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
								
				// update the reorder policy and quantity
				if ($set_NM == 1) {$this->set_reorder_policy($row['item_id'], $set_NM, $set_SM);}
				
				$tabular_data[] = array(
										$row['category'],
										anchor	(
												$edit_file.$row['item_id'].'/'.$origin.'/width:'.$width, 
												$row['item_number'],
												array('title'=>$this->lang->line('items_update'))
												),
										$row['reorder_policy'],
										$row['name'], 
										$cost_price, 
										'',
										$row['quantity'], 
										$stock_value, 
										'',
										$stock_total
										);
			}
		}

		// set subtitle
		$today_date						=	date('d/m/Y à H:i:s', time());
		if ($set_NM == 1)
		{
			$subtitle = $today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'. '."<font color='red'>".$this->lang->line('reports_set_reorder_policy_N')."</font><br>";
		}
		else
		{
			$subtitle = $today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.';
		}
		
		// load output data
		$data = array	(
						"title" 		=>	$this->lang->line('reports_inventory_nosale_report'),
						"subtitle"		=>	$subtitle,
						"headers"		=> 	$this->Stock_queries->getDataColumns($inputs),
						"data" 			=> 	$tabular_data,
						"summary_data" 	=> 	$this->Stock_queries->getSummaryData(array()),
						"export_excel" 	=> 	$export_excel
						);

		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}
	
	function inventory_slowmoving($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{
		// slow moving <= 3 sales in last 90 days with +ve stock
		// 90 days = defined between start date and end date
		// <=3 = defined by sales transaction count (not sold quantity)
		// ie this report will select all items where the number of sales transactions (invoices) is less than or equal to 3
		
		// load appropriate files
		$this							->	load->library('../controllers/items');
				
		// set up parameters for model
		$inputs							=	array();
		$inputs['where']				= 	"ospos_items.quantity > 0 and ospos_items.deleted = 0 and `ospos_items_suppliers`.supplier_preferred like 'Y' ";
		$inputs['report']				=	'IS';
		$inputs['start_date']			= 	date('Y-m-d', mktime(0,0,0,date("m"),date("d")-90,date("Y")));
		$inputs['end_date']				= 	date('Y-m-d');
		$start_date						= 	date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-90,date("Y")));
		$end_date 						= 	date($this->config->item('dateformat'));
		
		// set variables
		$tabular_data 					= 	array();
		$edit_file 						= 	'items/view/';
		$width 							= 	$this->items->get_form_width();
		$stock_total 					= 	0;
		$origin							=	'IM';
		
		// get the report data
		$report_data 					= 	$this->Stock_queries->getData($inputs);
		
		foreach($report_data as $row)
		{	
			// find out if there has been a sale for this item
			$inputs['item_id']			=	$row['item_id'];
			$item_sales_data 			= 	$this->Stock_queries->get_sales_item_Data($inputs);

			// test to see if item sales data is not empty, ie there has been a sales transaction
			if (!empty($item_sales_data['summary']))
			{
				// count number of sales
				$count = 0;
				foreach($item_sales_data['summary'] as $sale)
				{
					$count = $count + 1;
				}

				// if count is <= 3, output the line
				if ($count <= 3)
				{
					// get item supplier data for item cost using default supplier
					$cost_price			 	=	$this->get_item_supplier_cost($row['item_id']); //$this->config->item('default_supplier_id')
					
					// calculate stock value
					$stock_value 		=	$cost_price * $row['quantity'];
					$stock_total		=	$stock_total + $stock_value;

					// set up the item_number to handle blanks
					if ($row['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
					
					// update the reorder policy and quantity
					if ($set_SM == 1) {$this->set_reorder_policy($row['item_id'], $set_NM, $set_SM);}
					
					// set up the anchor to show the sales
					$anchor = '/reports/specific_item/'.$inputs['start_date'].'/'.$inputs['end_date'].'/'.$row['item_id'].'/'.$transaction_subtype.'/'.$export_excel;

					$tabular_data[] = array(
											$row['category'],
											anchor	(
													$edit_file.$row['item_id'].'/'.$origin.'/width:'.$width, 
													$row['item_number'],
													array('title'=>$this->lang->line('items_update'))
													),
											$row['reorder_policy'],
											anchor	(
													$anchor,
													$row['name']
													), 
											$cost_price, 
											'',
											$row['quantity'], 
											$stock_value, 
											'',
											$stock_total
											);
				}
			}
		}

		// set subtitle
		if ($set_SM == 1)
		{
			$subtitle = $start_date.' - '.$end_date.' '.$this->lang->line('common_for').' '.$this->db->database.'. '."<font color='red'>".$this->lang->line('reports_set_reorder_policy_S')."</font><br>";
		}
		else
		{
			$subtitle = $start_date.' - '.$end_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.';
		}

		// load output data
		$data = array	(
						"title" 		=> 	$this->lang->line('reports_inventory_slowmoving_report'),
						"subtitle" 		=> 	$subtitle,
						"headers" 		=> 	$this->Stock_queries->getDataColumns($inputs),
						"data" 			=> 	$tabular_data,
						"summary_data" 	=> 	$this->Stock_queries->getSummaryData(array()),
						"export_excel" 	=> 	$export_excel
						);

		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}
	
	function inventory_negative_stock($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{
		// load appropriate files
		$this							->	load->library('../controllers/items');
				
		// set up parameters for model
		$inputs							=	array();
		$inputs['where']				= 	'ospos_items.quantity < 0';    // and ospos_items.deleted = 0';
		$inputs['report']				=	'NS';
		
		// set variables
		$tabular_data 					= 	array();
		$edit_file 						= 	'items/view/';
		$origin							=	'NS';
		unset($_SESSION['line']);
		
		// get the report data
		$report_data 					= 	$this->Stock_queries->getData($inputs);
		
		foreach($report_data as $index=>$row)
		{
			// set up the item_number to handle blanks
			if ($row['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
			
			// get item supplier data for item cost using default supplier
			$cost_price 				=	$this->get_item_supplier_cost($row['item_id']); //$this->config->item('default_supplier_id')
			
			// load each line to the output array
			$tabular_data[$index] 		= array	(
												$row['category'],
												anchor	(
														$edit_file.$row['item_id'].'/'.$origin, 
														$row['item_number']
														),
												$row['name'],
												$cost_price,
												anchor	(
														'items/inventory/'.$row['item_id'].'/'.$origin, 
														$row['quantity'] 
														),
												);
											
			// Set the index for the line selected by the user
			if ($row['item_id'] == $_SESSION['sel_item_id'])
			{
				$_SESSION['line']		=	$index;
			}
		}
		
		// load data array for display
		$today_date						=	date('d/m/Y à H:i:s', time());
		$data = array	(
						"title" 		=> $this->lang->line('reports_negative_stock'),
						"subtitle" 		=> $today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 		=> $this->Stock_queries->getDataColumns($inputs),
						"data" 			=> $tabular_data,
						"summary_data" 	=> $this->Stock_queries->getSummaryData(array()),
						"export_excel" 	=> $export_excel
						);

		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
		
		return;
	}
	
	function inventory_invalid_item_number($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{
		// load appropriate files
		$this							->	load->library('../controllers/items');
				
		// set up parameters for model
		$inputs							=	array();
		$inputs['where']				= 	'ospos_items.deleted = 0';
		$inputs['report']				=	'II';
		
		// set variables
		$tabular_data 					= 	array();
		$edit_file 						= 	'items/view/';
		$width 							= 	$this->items->get_form_width();
		$origin							=	'II';
		
		// get the report data
		$report_data 					= 	$this->Stock_queries->getData($inputs);
		
		// and read the data
		foreach($report_data as $row)
		{
			// test the item number for valid starting string; load to output array if not valid
			if (substr($row['item_number'], 0, 2) !== "SO")
			{
				if ($row['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
				
				// get item supplier data for item cost using default supplier
				$cost_price			 	=	$this->get_item_supplier_cost($row['item_id']); //$this->config->item('default_supplier_id')
				
				// load each line to the output array
				$tabular_data[] 		= array	(
												$row['item_id'],

												anchor	(
														$edit_file.$row['item_id'].'/'.$origin.'/width:'.$width, 
														$row['item_number'],
														array('title'=>$this->lang->line('items_update'))
														),
												$row['name'],
                    							$row['category'],
												$cost_price, 
												$row['quantity']
												);
			}
		}
		
		// load data array for display
		$today_date						=	date('d/m/Y à H:i:s', time());
		$data = array	(
						"title" 		=> $this->lang->line('reports_invalid_item_number'),
						"subtitle" 		=> $today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 		=> $this->Stock_queries->getDataColumns($inputs),
						"data" 			=> $tabular_data,
						"summary_data" 	=> $this->Stock_queries->getSummaryData(array()),
						"export_excel" 	=> $export_excel
						);
						
		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}
	
	function inventory_rolling($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{
		$_SESSION['oeil_desactivation']='1';
		$_SESSION['global']=1;
		$_SESSION['tabular_articles_yes']=1;
		$_SESSION['inline_inventory_mode']='1';
		// load appropriate files
		$this							->	load->library('../controllers/items');
	
		// set up parameters for model
		$inputs							=	array();
		$inputs['where']				= 	"ospos_items.deleted = 0 AND ospos_items.rolling_inventory_indicator = 0 AND ospos_items.category != 'DEFECT'";
		$inputs['report']				=	'IR';
		
		// set variables
		$tabular_data 					= 	array();
		$edit_file 						= 	'items/view/';
		$count							=	0;
		$origin							=	'IR';
		
		// set up the number format
		$pieces							=	array();
		$pieces 						=	explode("/", $this->config->item('numberformat'));
		$parms['decimals']				=	$pieces[0];
		$parms['dec_point']				=	$pieces[1];
		$parms['thousands_sep']			=	$pieces[2];
		
		// get number of open days this month for this month
		$targets														=	$this->Target->get_targets(date("Y"), date("m"));
		if (!$targets)
		{
			$targets													=	new stdClass();
			$targets->target_shop_open_days								=	31;
			$targets->target_shop_turnover								=	0;
		}
		
		if ($targets->target_shop_open_days == 0)
		{
			$targets->target_shop_open_days								=	31;
		}
		
		// calculate limit = number of items to count per day = total records / open days + margin
		$where_select					=	"deleted = 0 AND category != 'DEFECT'";
		$total_records					=	$this->Stock_queries->count_all($where_select);
		if ($_SESSION['branchtype']=='I')
		{
			$count_per_day					=	$total_records / ($targets->target_shop_open_days+25);
		}
		else
		{
			$count_per_day					=	$total_records / $targets->target_shop_open_days;
		}
		$inputs['limit']				=	round(($count_per_day + 23), 0);

		// get the report data depending on report load status
		// this is to allow a return to the rolling inventory screen without data reload
		switch ($_SESSION['report_load'])
		{
			//Pour forcer le rechargement des données de la base de donnée ou pas
			case 	1:
					// do nothing, data is already loaded
			break;
			//*/
			default:
					// load data
					$_SESSION['report_data'] 		= 	$this->Stock_queries->getData_IR($inputs);
					$_SESSION['report_load']		=	1;
			break;
		}
		$_SESSION['total_articles_ir']	=	count($_SESSION['report_data']);

		// load table for each report line
		unset($_SESSION['line']);

        $_SESSION['compteur']=0;
		$_SESSION['ir_treated_lines'] = array();
		foreach($_SESSION['report_data'] as $index=>$row)
		{
			//check si l'article a été mis à jour
			if(!isset($_SESSION['report_data'][$index]['check_modif']))
			{
				$_SESSION['report_data'][$index]['check_modif']=array();
				$_SESSION['report_data'][$index]['check_modif']=0;
			}
			if(!isset($_SESSION['report_data'][$index]['focus']))
			{
				$_SESSION['report_data'][$index]['focus']=array();
				$_SESSION['report_data'][$index]['focus']=0;
			}

			// if item number is blank make it edit.
			if ($row['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}

			if($_SESSION['report_data'][$index]['check_modif']==1)
			{
				// article déjà traité : afficher en lecture seule avec checkmark
				$cur_item_info 					= 	$this->Item->get_info($row['item_id']);
				$row['quantity']				=	$cur_item_info->quantity;
				$_SESSION['report_data'][$index]['quantity'] = $row['quantity'];

				$done_display = number_format($row['quantity'], $parms['decimals'], $parms['dec_point'], $parms['thousands_sep']) . ' &#10003;';

				$tabular_data[] = array	(
										$row['category'],
										anchor	(
												$edit_file.$row['item_id'].'/'.$origin,
												$row['item_number']
												),
										$row['name'],
										anchor	(
												'items/inventory/'.$row['item_id'].'/'.$origin,
												number_format($row['quantity'], $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
												),
										$done_display,
										''
										);
				$_SESSION['ir_treated_lines'][] = count($tabular_data) - 1;
			}
			else
			{
				// test for changed item_id and update stock qty in table.
				if ($row['item_id'] == $_SESSION['transaction_info']->item_id)
				{
					$cur_item_info 					= 	$this->Item->get_info($row['item_id']);
					$row['quantity']				=	$cur_item_info->quantity;
					$_SESSION['report_data'][$index]['quantity'] = $row['quantity'];
					$_SESSION['line']	=$_SESSION['compteur'];  //			=	$index;
				}

				// load each line to the output array
				$inline_form = '<form class="inline-inv-form" data-item-id="'.$row['item_id'].'" data-theoretical-qty="'.$row['quantity'].'" data-dluo-indicator="'.($row['dluo_indicator'] == 'Y' ? 'Y' : 'N').'" style="display:inline;margin:0;padding:0;" onsubmit="return false;">'
					.'<input type="number" name="real_qty" class="inline-real-qty" style="width:100px;padding:2px 14px 2px 4px;text-align:right;" step="any" />'
					.'</form>';
				$inline_comment = '<input type="text" class="inline-inv-comment" data-item-id="'.$row['item_id'].'" style="width:250px;padding:2px 4px;" placeholder="" />'
					.' <button type="button" class="inline-inv-btn" data-item-id="'.$row['item_id'].'" style="border:none;background:none;cursor:pointer;padding:2px;vertical-align:middle;" title="Valider">'
					.'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>'
					.'</button>';
				$tabular_data[] = array	(
										$row['category'],
										anchor	(
												$edit_file.$row['item_id'].'/'.$origin,
												$row['item_number']
												),
										$row['name'],
										anchor	(
												'items/inventory/'.$row['item_id'].'/'.$origin,
												number_format($row['quantity'], $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
												),
										$inline_form,
										$inline_comment
										);
				$_SESSION['compteur']=$_SESSION['compteur']+1;
			}
		}
		
		// load data array for display
		$today_date						=	date($this->config->item('dateformat'));
		$data = array	(
						"title" 		=> 	$this->lang->line('reports_rolling'),
						"subtitle" 		=> 	$this->lang->line('reports_rolling_today').' '.$today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 		=> 	$this->Stock_queries->getDataColumns($inputs),
						"data" 			=> 	$tabular_data,
						"summary_data" 	=> 	$this->Stock_queries->getSummaryData(array()),
						"export_excel" 	=> 	$export_excel
						);
						
		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}	
	function list_new_product($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{		
		$_SESSION['oeil_desactivation']='1';
		$_SESSION['global']=1;
		$_SESSION['tabular_articles_yes']=1;
		// load appropriate files
		$this							->	load->library('../controllers/items');
	
		// set up parameters for model
		$inputs							=	array();
		$inputs['where']				= 	"ospos_items.deleted = 0 AND ospos_items.rolling_inventory_indicator = 0 AND ospos_items.category != 'DEFECT'";
		$inputs['report']				=	'IR';
		
		// set variables
		$tabular_data 					= 	array();
		$edit_file 						= 	'items/view/';
		$count							=	0;
		$origin							=	'IR';
		
		// set up the number format
		$pieces							=	array();
		$pieces 						=	explode("/", $this->config->item('numberformat'));
		$parms['decimals']				=	$pieces[0];
		$parms['dec_point']				=	$pieces[1];
		$parms['thousands_sep']			=	$pieces[2];
		
		// get number of open days this month for this month
		$targets														=	$this->Target->get_targets(date("Y"), date("m"));
		if (!$targets)
		{
			$targets													=	new stdClass();
			$targets->target_shop_open_days								=	31;
			$targets->target_shop_turnover								=	0;
		}
		
		if ($targets->target_shop_open_days == 0)
		{
			$targets->target_shop_open_days								=	31;
		}
		
		// calculate limit = number of items to count per day = total records / open days + margin
		$where_select					=	"deleted = 0 AND category != 'DEFECT'";
		$total_records					=	$this->Stock_queries->count_all($where_select);
		$count_per_day					=	$total_records / $targets->target_shop_open_days;
		$inputs['limit']				=	round(($count_per_day + 20), 0);
				
		// get the report data depending on report load status
		// this is to allow a return to the rolling inventory screen without data reload
		switch ($_SESSION['report_load'])
		{
			//Pour forcer le rechargement des données de la base de donnée ou pas
			case 	1:
					// do nothing, data is already loaded
			break;
			//*/
			default:
					// load data
					$_SESSION['report_data'] 		= 	$this->Stock_queries->getData_IR($inputs);
					$_SESSION['report_load']		=	1;
			break;
		}

		// load table for each report line
		unset($_SESSION['line']);

        $_SESSION['compteur']=0;
		foreach($_SESSION['report_data'] as $index=>$row)
		{	
			//check si l'article a été mis à jour			
			if(!isset($_SESSION['report_data'][$index]['check_modif']))
			{
				$_SESSION['report_data'][$index]['check_modif']=array();
				$_SESSION['report_data'][$index]['check_modif']=0;
			}
			if(!isset($_SESSION['report_data'][$index]['focus']))
			{
				$_SESSION['report_data'][$index]['focus']=array();
				$_SESSION['report_data'][$index]['focus']=0;
			}
			
			if($_SESSION['report_data'][$index]['check_modif']==1)
			{
				//ne pas afficher l'article / not display
			}
			else
			{

				// if item number is blank make it edit.
				if ($row['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
				
				// test for changed item_id and update stock qty in table.
				if ($row['item_id'] == $_SESSION['transaction_info']->item_id)
				{
					$cur_item_info 					= 	$this->Item->get_info($row['item_id']);
					$row['quantity']				=	$cur_item_info->quantity;
					$_SESSION['report_data'][$index]['quantity'] = $row['quantity'];
					$_SESSION['line']	=$_SESSION['compteur'];  //			=	$index;
				}
				
				// load each line to the output array
				$tabular_data[] = array	(
										$row['category'],
										anchor	(
												$edit_file.$row['item_id'].'/'.$origin, 
												$row['item_number']
												),
										$row['name'],
										anchor	(
												'items/inventory/'.$row['item_id'].'/'.$origin,
												number_format($row['quantity'], $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
												),
										'______________'
										);
				$_SESSION['compteur']=$_SESSION['compteur']+1;
			}
		}
		
		// load data array for display
		$today_date						=	date($this->config->item('dateformat'));
		$data = array	(
						"title" 		=> 	$this->lang->line('reports_rolling'),
						"subtitle" 		=> 	$this->lang->line('reports_rolling_today').' '.$today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 		=> 	$this->Stock_queries->getDataColumns($inputs),
						"data" 			=> 	$tabular_data,
						"summary_data" 	=> 	$this->Stock_queries->getSummaryData(array()),
						"export_excel" 	=> 	$export_excel
						);
						
		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}	
	function cogs($export_excel=0, $month_end=0, $detail=1)
	{
		// set the variables
		$start_date						=	date('Y-m-d', 0);
		$end_date						=	date('Y-m-d');
		$report_data					=	array();
		$total_sales_HT_T				=	0;
		$total_costs_HT_T				=	0;
		$total_profs_HT_T				=	0;
		$total_sales_HT_Y				=	0;
		$total_costs_HT_Y				=	0;
		$total_profs_HT_Y				=	0;
		$total_sales_HT_M				=	0;
		$total_costs_HT_M				=	0;
		$total_profs_HT_M				=	0;
		$current_year					=	0;
		$current_month					=	0;
		$first_loop						=	1;
		$headers						=	array();
		$headers						=	array	(
													'Sales_ID', 
													'Sales date and time',
													'Sales value HT',
													'Sales cost HT',
													'Sales profit HT',
													'Percentage profit'
													);
		$summary_data					=	array();
		
		// get the data
		$this							->	db->select();
		$this							->	db->from('sales');
		$this							->	db->where('date(sale_time) BETWEEN "'. $start_date. '" and "'. $end_date.'"');
		$this							->	db->order_by('date(sale_time)');
		$report_data					= 	$this->db->get()->result_array();
		
		foreach($report_data as $row)
		{
			// get year and month
			$sale_date_split			=	explode('-', date($row['sale_time']));
			
			// check if first time through$export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0
			if ($first_loop == 1)
			{
				$current_year					=	$sale_date_split[0];
				$current_month					=	$sale_date_split[1];
				$first_loop						=	0;
			}
			
			// check year change
			if ($sale_date_split[0] != $current_year)
			{
				// calculate overall percentage profit for month
				$percentage_profit				=	number_format(($total_profs_HT_M / $total_sales_HT_M * 100), 2);
				
				// load totals to array
				$tabular_data[] = 	array		(
												'Totals for month '.$current_year.' - '.$current_month,
												'HT',
												$total_sales_HT_M,
												$total_costs_HT_M,
												$total_profs_HT_M,
												$percentage_profit,
												'%'
												);
				
				// reset variables
				$total_sales_HT_M				=	0;
				$total_costs_HT_M				=	0;
				$total_profs_HT_M				=	0;
				$current_month					=	$sale_date_split[1];
				
				// calculate overall percentage profit for year
				$percentage_profit				=	number_format(($total_profs_HT_Y / $total_sales_HT_Y * 100), 2);
				
				// load totals to array
				$tabular_data[] = 	array		(
												'Totals for year '.$current_year,
												'HT',
												$total_sales_HT_Y,
												$total_costs_HT_Y,
												$total_profs_HT_Y,
												$percentage_profit,
												'%'
												);
				
				// reset variables
				$total_sales_HT_Y				=	0;
				$total_costs_HT_Y				=	0;
				$total_profs_HT_Y				=	0;
				$current_year					=	$sale_date_split[0];
			}
			
			// check month change
			if ($sale_date_split[1] != $current_month)
			{
				// calculate overall percentage profit for month
				$percentage_profit				=	number_format(($total_profs_HT_M / $total_sales_HT_M * 100), 2);
				
				// load totals to array
				$tabular_data[] = 	array		(
												'Totals for month '.$current_year.' - '.$current_month,
												'HT',
												$total_sales_HT_M,
												$total_costs_HT_M,
												$total_profs_HT_M,
												$percentage_profit,
												'%'
												);
				
				// reset variables
				$total_sales_HT_M				=	0;
				$total_costs_HT_M				=	0;
				$total_profs_HT_M				=	0;
				$current_month					=	$sale_date_split[1];
			}		
			
			// test of detail line output required
			if ($detail == 1)
			{
				// calculate line percentage profit
				$percentage_profit			=	number_format(($row['overall_profit'] / $row['subtotal_after_discount'] * 100), 2);
				
				// load each line to the output array
				$tabular_data[]	=	array	(
											$row['sale_id'],
											$row['sale_time'],
											$row['subtotal_after_discount'],
											$row['overall_cost'],
											$row['overall_profit'],
											$percentage_profit,
											);
			}
			
			// accumulate totals
			$total_sales_HT_T			=	$total_sales_HT_T + $row['subtotal_after_discount'];
			$total_costs_HT_T			=	$total_costs_HT_T + $row['overall_cost'];
			$total_profs_HT_T			=	$total_profs_HT_T + $row['overall_profit'];
			
			$total_sales_HT_Y			=	$total_sales_HT_Y + $row['subtotal_after_discount'];
			$total_costs_HT_Y			=	$total_costs_HT_Y + $row['overall_cost'];
			$total_profs_HT_Y			=	$total_profs_HT_Y + $row['overall_profit'];
			
			$total_sales_HT_M			=	$total_sales_HT_M + $row['subtotal_after_discount'];
			$total_costs_HT_M			=	$total_costs_HT_M + $row['overall_cost'];
			$total_profs_HT_M			=	$total_profs_HT_M + $row['overall_profit'];
		}
		
		// at end of loop, output monthly totals, yearly totals and everall total
		// monthly totals
		$percentage_profit				=	number_format(($total_profs_HT_M / $total_sales_HT_M * 100), 2);
		$tabular_data[] = 	array		(
										'Totals for month '.$current_year.' - '.$current_month,
										'HT',
										$total_sales_HT_M,
										$total_costs_HT_M,
										$total_profs_HT_M,
										$percentage_profit,
										'%'
										);
		
		// yearly totals
		$percentage_profit				=	number_format(($total_profs_HT_Y / $total_sales_HT_Y * 100), 2);
		$tabular_data[] = 	array		(
										'Totals for year '.$current_year,
										'HT',
										$total_sales_HT_Y,
										$total_costs_HT_Y,
										$total_profs_HT_Y,
										$percentage_profit,
										'%'
										);
										
		// overall totals
		$percentage_profit				=	number_format(($total_profs_HT_T / $total_sales_HT_T * 100), 2);
		$tabular_data[] = 	array		(
										'OVERALL TOTALS',
										'HT',
										$total_sales_HT_T,
										$total_costs_HT_T,
										$total_profs_HT_T,
										$percentage_profit,
										'%'
										);
									
		// load data array for display
		$today_date						=	date('d/m/Y à H:i:s', time());
		$data = array	(
						"title" 		=> 'Total Sales for period - '.$start_date.' - '.$end_date,
						"subtitle" 		=> $today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 		=> $headers,
						"data" 			=> $tabular_data,
						"summary_data" 	=> $summary_data,
						"export_excel" 	=> $export_excel
						);
						
		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}	

	function rapport_configurations_produits()
	{
        //Rapport configurations produits
		$tabular_data = array();                         //$_SESSION['G']->supplier_pick_list[$supplier]
		$inputs = array();

		//Création du tableau pour stocker les différentes dates
		$dates = array();
		$dates['today'] = date('Y/m/d');
		$dates['end_date'] = date('Y/m/d');
		$dates['start_date_30'] = date('Y/m/d', strtotime('-'. '1' .' month'));
		$dates['start_date_60'] = date('Y/m/d', strtotime('-'. '2' .' month'));
		$dates['start_date_90'] = date('Y/m/d', strtotime('-'. '3' .' month'));

		$this->load->library('../controllers/items');
		$this->load->model('Item');

		//Stockage des données des articles pour les afficher
		$items = $this->Item->get_rapport_configurations_produits($dates);
		foreach($items['result'] as $index =>$line_item)
		{
			
            $tabular_data[$index][] = $line_item['item_number'];    //Code article
			$tabular_data[$index][] = $line_item['name'];    //Libellé
			$tabular_data[$index][] = $line_item['company_name']; //$line_item['supplier'];    //Fournisseur
            $tabular_data[$index][] = intval($line_item['supplier_min_stock_qty']);    //Stock seuil
            $tabular_data[$index][] = intval($line_item['supplier_reorder_pack_size']);    //Lot
            $tabular_data[$index][] = intval($line_item['supplier_min_order_qty']);    //Min. Commande
            $tabular_data[$index][] = intval($line_item['quantity']);    //Stock réel
            $tabular_data[$index][] = intval($line_item['QteVendu30']);    //Ventes 30j
            $tabular_data[$index][] = intval($line_item['QteVendu60']);    //Ventes 60j
            $tabular_data[$index][] = intval($line_item['QteVendu90']);    //Ventes 90j
            $tabular_data[$index][] = $line_item['supplier_cost_price'];    //Prix d'achat
		}

		//Headers des colonnes
        $headers = array(
			'code_article' => 'Code article',
			'name' => 'Libellé',
			'supplier' => 'Fournisseur',
			'stock_min' => 'Stock seuil',
			'pack_size' => 'Lot',
            'commande_min' => 'Min. Commande',
			'stock_reel' => 'Stock réel',
			'vente_30' => 'Ventes -30j',
			'vente_60' => 'Ventes -60j',
			'vente_90' => 'Ventes -90j',
			'prix_supplier' => 'Prix d\'achat'
		);

		// load data array for display
		$today_date							=	date('d/m/Y à H:i:s', time());
		$data = array	(
			            "title" 			=>	$this->lang->line('reports_rapport_configurations_produits'),
						"subtitle" 			=>	$today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 			=> 	$headers,
						"data" 				=> 	$tabular_data
						);

		//Transmision des informatiques utilent à l'affichage à la page tabular_rapport_configurations_produits.php 
		$this->load->view("reports/tabular_rapport_configurations_produits", $data);
	}
	
	function inventory_change_tracking_report()
	{
		/*
		SQL Request

		SELECT `trans_date`, ospos_people.first_name, ospos_items.item_number, ospos_items.name, `trans_stock_before`, `trans_inventory`, `trans_stock_after`, `trans_comment`
		FROM `ospos_inventory`, ospos_people, ospos_items

		WHERE
		`ospos_inventory`.`trans_user` = ospos_people.person_id AND
		`ospos_inventory`.`trans_items` = ospos_items.item_id AND
		`trans_stock_before` <> `trans_stock_after` AND
		`trans_comment` NOT LIKE 'SALE%' AND
		`trans_comment` NOT LIKE 'SART%' AND
		`trans_comment` NOT LIKE 'Import%'
		`trans_comment` NOT LIKE 'RECV-%'

		ORDER BY `trans_date` DESC
		*/
		
		{
			
			// set the variables
			$report_data					=	array();
			$headers						=	array	(
														'trans_date' 			=> 'Date de transaction', 
														'first_name' 			=> 'Prénom',
														'item_number' 			=> 'N° Article',
														'name' 					=> 'Description',
														'trans_stock_before' 	=> 'Stock avant transaction',
														'trans_inventory' 		=> 'Transaction',
														'trans_stock_after' 	=> 'Stock après transaction',
														'trans_comment' 		=> 'Commentaire'
														);
			
			// get the data

			$today_date						=	date('d/m/Y à H:i:s', time());
			$one_month_ago					=	date('Y-m-d H:i:s',strtotime('-1 month'));

			$this							->	db->select('trans_date, first_name, item_number, name, trans_stock_before, trans_inventory, trans_stock_after, trans_comment');
			$this							->	db->from('inventory, people, items');
			$this							->	db->where('trans_items = item_id AND trans_user = person_id AND trans_stock_before <> trans_stock_after AND trans_comment NOT LIKE "SALE%" AND trans_comment NOT LIKE "SART%" AND trans_comment NOT LIKE "Import%" AND trans_comment NOT LIKE "RECV-%"');
			$this							->	db->order_by('trans_date','desc');
			$report_data					= 	$this->db->get()->result_array();
			
			foreach($report_data as $index=>$row)
			{	
				$trans_date[$index] = $row['trans_date'];

				// load each line to the output array (with a transaction < 1 month)
				if($one_month_ago < $trans_date[$index]){
					$tabular_data[$index][] = $row['trans_date'];
					$tabular_data[$index][] = $row['first_name'];
					$tabular_data[$index][] = $row['item_number'];
					$tabular_data[$index][] = $row['name'];
					$tabular_data[$index][] = $row['trans_stock_before'];
					$tabular_data[$index][] = $row['trans_inventory'];
					$tabular_data[$index][] = $row['trans_stock_after'];
					$tabular_data[$index][] = $row['trans_comment'];
				}
				elseif ($one_month_ago >= $trans_date[$index]){
					break;
				}
			}
										
			// load data array for display
			$data = array	(
							"title" 		=> $this->lang->line('reports_inventory_change_tracking_report'),
							"subtitle" 		=> $today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
							"headers" 		=> $headers,
							"data" 			=> $tabular_data,
							"export_excel" 	=> $export_excel
							);
		
			// file or display?
			$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
		}
	}
	

	function date_last_inventory()
	{
        //Rapport date dernier inventaire
		$tabular_data = array();
        $compteur = 0;

		//Stockage des données des articles pour les afficher
		$items_inv = $this->Inventory->get_date_last_inventory();
		foreach($items_inv as $index =>$line_item)
		{	
			$compteur += 1;
			$tabular_data[$index][] = $line_item['trans_last_date']; //Date
			$tabular_data[$index][] = $line_item['username']; //Vendeur
			$tabular_data[$index][] = $line_item['item_number']; //Code produit
            $tabular_data[$index][] = $line_item['name'];
            $tabular_data[$index][] = $line_item['trans_stock_before']; //Stock avant
            $tabular_data[$index][] = $line_item['trans_inventory']; //Stock mouvement
            $tabular_data[$index][] = $line_item['trans_stock_after']; //Stock après
            $tabular_data[$index][] = $line_item['trans_comment']; //Commentaire
		}

		//Headers des colonnes
        $headers = array(
			'trans_last_date' => 'Date',
			'username' => 'Vendeur',
			'item_number' => 'Code produit',
			'name' => 'Libellé',
			'trans_stock_before' => 'Stock avant',
			'trans_inventory' => 'Stock +/-',
			'trans_stock_after' => 'Stock après',
            'trans_comment' => 'Commentaire'
		);

		// load data array for display
		$today_date							=	date('d/m/Y à H:i:s', time());
		$data = array	(
			            "title" 			=>	$this->lang->line('reports_date_last_inventory'),
						"subtitle" 			=>	$today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'. ('.$compteur.' articles'.')',
						"headers" 			=> 	$headers,
						"data" 				=> 	$tabular_data
						);

		//Transmision des informatiques utilent à l'affichage à la page tabular_rapport_configurations_produits.php
		$this->load->view("reports/tabular_rapport_configurations_produits", $data);
	}

	// set the reorder policy and quantity - called from no sale and slow moving and performed at user request
	function set_reorder_policy($item_id, $set_NM, $set_SM)
	{
		// set session data
		unset($_SESSION['new']);
		
		if ($set_NM == '1')
		{
			$_SESSION['transaction_info']							=	new stdClass();
			$_SESSION['transaction_info']->item_id					=	$item_id;
			$_SESSION['transaction_info']->reorder_quantity			=	0;
			$_SESSION['transaction_info']->reorder_level			=	0;
			$_SESSION['transaction_info']->reorder_policy			=	'N';
		}
		
		if ($set_SM == '1')
		{
			$_SESSION['transaction_info']							=	new stdClass();
			$_SESSION['transaction_info']->item_id					=	$item_id;
			$_SESSION['transaction_info']->reorder_quantity			=	1;
			$_SESSION['transaction_info']->reorder_level			=	0;
			$_SESSION['transaction_info']->reorder_policy			=	'Y';
		}
		
		$this->Item->save();
	}
	
	function excel_export()
	{
		// get flash data for route_code
		$route_code					=	$this->session->flashdata('origin');

		// determine route depending on route_code
		$route_info					=	array();
		$route_info					=	$this->Common_routines->determine_route($route_code);

		if(!empty($route_info))
		{
			// go to correct routing in controller
			$this->{$route_info->route_path}();
		}
		else
		{
			// load default
			$this->load->view("reports/excel_export", array());
		}
	}
	
	function file_or_display($export_excel, $data, $month_end, $specific=0)
	{
		if ($export_excel == 1)
		{
			$this->load->model('Common_routines');
			$this->Common_routines->create_csv($data, $month_end, $specific);
		}
		else
		{
			unset($_SESSION['undel']);
			$this->load->view("reports/tabular", $data);
		}
	}
	
	// -----------------------------------------------------------------------------------------				
	// Data Mining
	// -----------------------------------------------------------------------------------------

	function top_clients()
	{
		// load required routines
		$this->load->model('Common_routines');
		$this->load->model('Customer');
		$this->load->library('../controllers/customers');
		
		// set up the pagination
		$config						=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 		= 	site_url('/customers/index');
		$config['total_rows'] 		= 	$this->Customer->count_all($where_select);
		$this						->	pagination->initialize($config);
			
		// setup output data
		$data['links']				=	$this->pagination->create_links();
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->customers->get_form_width();
		$data['title']				=	$this->lang->line('reports_top_clients');

		$create_headers				=	1;
		$order_by					=	'sales_ht';
		$sequence					=	'desc';
		
		$data['manage_table']		=	get_people_manage_table($this->Customer->get_all($config['per_page'], $this->uri->segment($config['uri_segment']), $order_by, $sequence), $this, $create_headers);

		// show data
		$this						->	load->view('people/manage', $data);
	}
	
	function customer_sales_profile($export_excel=0, $month_end=0, $detail=1)
	{
		// load required routines
		$this->load->model('Customer');
		$this->load->model('Sale');
		$this->load->library('../controllers/customers');
		
		// set up the number format
		$pieces							=	array();
		$pieces 						=	explode("/", $this->config->item('numberformat'));
		$parms['decimals']				=	$pieces[0];
		$parms['dec_point']				=	$pieces[1];
		$parms['thousands_sep']			=	$pieces[2];
		
		// set up dates
		$m								=	date('Ym');
		$m_1							=	date('Ym', strtotime(date('Ym')." -1 month"));
		$m_2							=	date('Ym', strtotime(date('Ym')." -2 month"));
		$m_3							=	date('Ym', strtotime(date('Ym')." -3 month"));
		
		// initialise headers
		$headers						=	array();
		$headers						=	array	(
													'Customer', 
													'Current Month',
													'Month - 1',
													'Month - 2',
													'Month - 3',
													'Month - X',
													'Total Customer',
													'Fidelity Score'
													);
		$start_date						=	date('Y-m-d', 0);
		$end_date						=	date('Y-m-d');
		$data_totals_array				=	array();
		$counts							=	array();
		$averages						=	array();
		
		// get all clients
		$customer_info					=	$this->Customer->get_all();
		$number_of_customers			=	count($customer_info);
		
		// read sales data for this client
		foreach ($customer_info->result_array() as $customer)
		{
			$sales_info					=	$this->Sale->get_info_by_customer($customer['person_id'])->result_array();
			
			// initialise data array
			$data_array					=	array();
			
			// read all sales data
			foreach ($sales_info as $sales)
			{
				$sale_date				=	date('Ym', strtotime($sales['sale_time']));
				
				// test date for bucket and create customer points
				if ($sale_date < $m_3)
				{
					$data_array[5]		=	$data_array[5] + $sales['subtotal_after_discount'];
				}
				if ($sale_date == $m_3)
				{
					$data_array[4]		=	$data_array[4] + $sales['subtotal_after_discount'];
				}
				if ($sale_date == $m_2)
				{
					$data_array[3]		=	$data_array[3] + $sales['subtotal_after_discount'];
				}
				if ($sale_date == $m_1)
				{
					$data_array[2]		=	$data_array[2] + $sales['subtotal_after_discount'];
				}
				if ($sale_date == $m)
				{
					$data_array[1]		=	$data_array[1] + $sales['subtotal_after_discount'];
				}
			}
			
			// calculate customer fidelity score
			if ($data_array[1] != 0)
			{
				$data_array[6]			=	$data_array[6] + 10;
			}
			if ($data_array[2] != 0)
			{
				$data_array[6]			=	$data_array[6] + 10;
			}
			if ($data_array[3] != 0)
			{
				$data_array[6]			=	$data_array[6] + 10;
			}
			if ($data_array[4] != 0)
			{
				$data_array[6]			=	$data_array[6] + 10;
			}
			if ($data_array[5] != 0)
			{
				$data_array[6]			=	$data_array[6] + 10;
			}
			
			// calculate fidelity spread
			if ($data_array[6]	==	50)
			{
				$fidelity[0]			=	$fidelity[0] + 1;	// high
			}
			if ($data_array[6]	==	40)
			{
				$fidelity[1]			=	$fidelity[1] + 1;	// medium to high
			}
			if ($data_array[6]	==	30)
			{
				$fidelity[2]			=	$fidelity[2] + 1;	// medium
			}
			if ($data_array[6]	==	20)
			{
				$fidelity[3]			=	$fidelity[3] + 1;	// low to medium
			}
			if ($data_array[6]	==	10)
			{
				$fidelity[4]			=	$fidelity[4] + 1;	// low
			}
			
			
			// load customer totals to array
			if ($detail == 1)
			{
				$last_name					=	strtoupper($customer['last_name']);
				$first_name					=	ucfirst(strtolower($customer['first_name']));
				$data_array[0]				=	$last_name.', '.$first_name;
				
				// create the anchor
				$transaction_subtype	 	=	'sales';
				$export_excel				= 	0;
				
				$anchor 					= 	'/reports/specific_customer'
												.'/'
												.$start_date
												.'/'
												.$end_date
												.'/'
												.$customer['person_id']
												.'/'
												.$transaction_subtype
												.'/'
												.$export_excel;
				
				$tabular_data[] 			= 	array	(
														anchor	(
																'customers/view/'.$customer['person_id'].'/width:'.$parms['width'], 
																$data_array[0],
																array	('title'=>$this->lang->line($parms['customers_update']))
																),
														$data_array[1],
														$data_array[2],
														$data_array[3],
														$data_array[4],
														$data_array[5],
														anchor	(
																$anchor,
																number_format($customer['sales_ht'], $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
																)
														,
														$data_array[6]
														);
			}
						
			// create overall totals
			$data_totals_array[0]		=	$data_totals_array[0] + $data_array[1];
			$data_totals_array[1]		=	$data_totals_array[1] + $data_array[2];
			$data_totals_array[2]		=	$data_totals_array[2] + $data_array[3];
			$data_totals_array[3]		=	$data_totals_array[3] + $data_array[4];
			$data_totals_array[4]		=	$data_totals_array[4] + $data_array[5];
			$data_totals_array[5]		=	$data_totals_array[0] + $data_totals_array[1] + $data_totals_array[2] + $data_totals_array[3] + $data_totals_array[4];
		
			// create the counts - count the number of customer purchases per month
			if ($data_array[1] > 0)
			{
				$counts[0]				=	$counts[0]	+	1;
			}
			if ($data_array[2] > 0)
			{
				$counts[1]				=	$counts[1]	+	1;
			}
			if ($data_array[3] > 0)
			{
				$counts[2]				=	$counts[2]	+	1;
			}
			if ($data_array[4] > 0)
			{
				$counts[3]				=	$counts[3]	+	1;
			}
			if ($data_array[5] > 0)
			{
				$counts[4]				=	$counts[4]	+	1;
			}
			$counts[5]					=	$counts[0] + $counts[1] + $counts[2] + $counts[3] + $counts[4];
		}
		
		// calculate average purchase per customer per month
		$averages[0]					=	$data_totals_array[0] / $counts[0];
		$averages[1]					=	$data_totals_array[1] / $counts[1];
		$averages[2]					=	$data_totals_array[2] / $counts[2];
		$averages[3]					=	$data_totals_array[3] / $counts[3];
		$averages[4]					=	$data_totals_array[4] / $counts[4];
		$averages[5]					=	$data_totals_array[5] / $counts[5];
		
		// calculate fidelity percentages
		$fidelity_percent[0]			=	$fidelity[0] / $number_of_customers * 100;
		$fidelity_percent[1]			=	$fidelity[1] / $number_of_customers * 100;
		$fidelity_percent[2]			=	$fidelity[2] / $number_of_customers * 100;
		$fidelity_percent[3]			=	$fidelity[3] / $number_of_customers * 100;
		$fidelity_percent[4]			=	$fidelity[4] / $number_of_customers * 100;	
		
		// load totals to array
		$tabular_data[] 				= 	array	(
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------'
													);
													
		$tabular_data[]					=	array	(	
													'OVERALL TOTALS',
													$data_totals_array[0],
													$data_totals_array[1],
													$data_totals_array[2],
													$data_totals_array[3],
													$data_totals_array[4],
													$data_totals_array[5]
													);
													
		$tabular_data[] 				= 	array	(
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------'
													);
													
		$tabular_data[] 				= 	array	(
													'OVERALL COUNTS',
													$counts[0],
													$counts[1],
													$counts[2],
													$counts[3],
													$counts[4],
													$counts[5]
													);
		
		$tabular_data[] 				= 	array	(
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------'
													);
													
		$tabular_data[] 				= 	array	(
													'AVERAGE PER MONTH',
													$averages[0],
													$averages[1],
													$averages[2],
													$averages[3],
													$averages[4],
													$averages[5]
													);
													
		$tabular_data[] 				= 	array	(
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------',
													'-------------------'
													);
													
		$tabular_data[] 				= 	array	(
													'-',
													'-',
													'-',
													'-',
													'-',
													'-',
													'-'
													);
													
		$tabular_data[] 				= 	array	(
													'FIDELITY SPREAD',
													'Total Customers',
													'High',
													'Medium to High',
													'Medium',
													'Low to Medium',
													'Low'
													);
		
		$tabular_data[] 				= 	array	(
													'Counts',
													$number_of_customers,
													$fidelity[0],
													$fidelity[1],
													$fidelity[2],
													$fidelity[3],
													$fidelity[4]
													);
													
		$tabular_data[] 				= 	array	(
													'Percentage',
													' ',
													$fidelity_percent[0],
													$fidelity_percent[1],
													$fidelity_percent[2],
													$fidelity_percent[3],
													$fidelity_percent[4]
													);
													
		$tabular_data[] 				= 	array	(
													'-',
													'-',
													'-',
													'-',
													'-',
													'-',
													'-'
													);
		
		// load data array for display
		$today_date						=	date('d/m/Y à H:i:s', time());
		$data = array	(
						"title" 		=> 'Customer Sales Profile - '.$start_date.' - '.$end_date,
						"subtitle" 		=> $today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 		=> $headers,
						"data" 			=> $tabular_data,
						"summary_data" 	=> ' ',
						"export_excel" 	=> $export_excel
						);
						
		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}

	function top_items_by_value()
	{


		/*
		SELECT 	ospos_sales.sale_time, ospos_sales_items.line_item_number,
				ospos_sales_items.description, ospos_items.category, ospos_items.volume,
				ospos_items.nicotine, ospos_sales_items.item_cost_price,
				ospos_sales_items.item_unit_price, ospos_sales_items.quantity_purchased,
				ospos_sales_items.line_sales, ospos_sales_items.line_profit

		FROM ospos_sales, ospos_sales_items, ospos_items

		WHERE 	`ospos_sales`.`sale_id` = ospos_sales_items.sale_id AND
				`ospos_items`.`item_number` = ospos_sales_items.line_item_number AND
        		 line_sales > 0 AND
        		`sale_time` > '2023-04-04 18:00:00'

		ORDER BY ospos_items.item_number DESC
		*/

		// set the variables
		$report_data					=	array();
		$headers						=	array	(
													'line_item_number' 		=> 'N° Article', 
													'description' 			=> 'Description',
													'category' 				=> 'Famille',
													'volume' 				=> 'Volume',
													'nicotine' 				=> 'Taux nicotine',
													'item_cost_price' 		=> 'Prix ​​Achat HT',
													'item_unit_price' 		=> 'Prix ​​Public HT',
													'quantity_purchased' 	=> 'Nb &nbsp;&nbsp;Ventes&nbsp;&nbsp;',
													'line_profit' 			=> '&nbsp;&nbsp;Profits&nbsp;&nbsp;',
													'line_sales' 			=> 'Ventes TTC'
													);

		// load required routines
		$this->load->model('Common_routines');
		$this->load->model('Item');
		$this->load->library('../controllers/items');
		
		// set up session
		unset($_SESSION['undel']);
		$config							=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 			= 	site_url('/items/index');
		$config['total_rows'] 			= 	$this->Item->count_all();
		$this							->	pagination->initialize($config);

		
		// get the data

		$today_date						=	date('d/m/Y à H:i:s', time());
		$one_month_ago					=	date('Y-m-d H:i:s',strtotime('-1 month'));

		$this							->	db->select('item_number, sales_items.description, category, volume, nicotine, item_cost_price, item_unit_price, quantity_purchased, line_profit, line_sales');
		$this							->	db->from('sales_items, sales, items');
		$this							->	db->where('sales.sale_id = `ospos_sales_items`.sale_id AND item_number = line_item_number AND line_sales > 0 AND sale_time > "'.$one_month_ago.'"');
		$this							->	db->order_by('item_number');
		$report_data					= 	$this->db->get()->result_array();


		$quantity_purchased[$index] = 0;
		$line_profit[$index] = 0;
		$line_sale[$index] = 0;
		foreach($report_data as $index=>$row)
		{
			$check[$index][0] = $report_data[$index]['item_number'];
			if($check[$index][0] != $check[$index-1][0])
			{
				$quantity_purchased[$index] += $report_data[$index]["quantity_purchased"];
				$line_profit[$index] += $report_data[$index]["line_profit"];
				$line_sale[$index] += $report_data[$index]["line_sales"];
				for($index2=$index+1; $index2<=sizeof($report_data); $index2++)
				{
					$check[$index][1] = $report_data[$index2]['item_number'];
					$check[$index][2] = $report_data[$index2]['quantity_purchased'];
					$check[$index][3] = $report_data[$index2]['line_profit'];
					$check[$index][4] = $report_data[$index2]['line_sales'];
					if($check[$index][0] == $check[$index][1])
					{
						$quantity_purchased[$index] += $check[$index][2];
						$line_profit[$index] += $check[$index][3];
						$line_sale[$index] += $check[$index][4];
					}
					else
					{
						break;
					}
				}
				$tabular_data[$index][] = $row['item_number'];
				$tabular_data[$index][] = $row['description'];
				$tabular_data[$index][] = $row['category'];
				$tabular_data[$index][] = $row['volume'];
				$tabular_data[$index][] = $row['nicotine'];
				$tabular_data[$index][] = $row['item_cost_price'];
				$tabular_data[$index][] = $row['item_unit_price'];
				$tabular_data[$index][] = $quantity_purchased[$index];
				$tabular_data[$index][] = $line_profit[$index];
				$tabular_data[$index][] = $line_sale[$index];
			}
		}
		function compare($a, $b)
		{
			return $b[9] - $a[9];
		}
		usort($tabular_data, "compare");
		for($i=50; $i<=sizeof($tabular_data); $i++)
		{
			for($j=0; $j<10; $j++)
			{
				unset($tabular_data[$i][$j]);

			}
		}
		$one_month_ago_					=	date('d/m/Y H:i:s',strtotime('-1 month'));
		$data = array	(
			"title" 		=> $this->lang->line('reports_top_items_by_value'),
			"subtitle" 		=> $this->lang->line('common_top_sale_since').' '.$one_month_ago_.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
			"headers" 		=> $headers,
			"data" 			=> $tabular_data,
			"export_excel" 	=> $export_excel
			);


		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}

	function top_items_by_quantity()
	{


		/*
		SELECT 	ospos_sales.sale_time, ospos_sales_items.line_item_number,
				ospos_sales_items.description, ospos_items.category, ospos_items.volume,
				ospos_items.nicotine, ospos_sales_items.item_cost_price,
				ospos_sales_items.item_unit_price, ospos_sales_items.quantity_purchased,
				ospos_sales_items.line_sales, ospos_sales_items.line_profit

		FROM ospos_sales, ospos_sales_items, ospos_items

		WHERE 	`ospos_sales`.`sale_id` = ospos_sales_items.sale_id AND
				`ospos_items`.`item_number` = ospos_sales_items.line_item_number AND
        		 line_sales > 0 AND
        		`sale_time` > '2023-04-04 18:00:00'

		ORDER BY ospos_items.item_number DESC
		*/

		// set the variables
		$report_data					=	array();
		$headers						=	array	(
													'line_item_number' 		=> 'N° Article', 
													'description' 			=> 'Description',
													'category' 				=> 'Famille',
													'volume' 				=> 'Volume',
													'nicotine' 				=> 'Taux nicotine',
													'item_cost_price' 		=> 'Prix ​​Achat HT',
													'item_unit_price' 		=> 'Prix ​​Public HT',
													'quantity_purchased' 	=> 'Nb &nbsp;&nbsp;Ventes&nbsp;&nbsp;',
													'line_profit' 			=> '&nbsp;&nbsp;Profits&nbsp;&nbsp;',
													'line_sales' 			=> 'Ventes TTC'
													);

		// load required routines
		$this->load->model('Common_routines');
		$this->load->model('Item');
		$this->load->library('../controllers/items');
		
		// set up session
		unset($_SESSION['undel']);
		$config							=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 			= 	site_url('/items/index');
		$config['total_rows'] 			= 	$this->Item->count_all();
		$this							->	pagination->initialize($config);

		
		// get the data

		$today_date						=	date('d/m/Y à H:i:s', time());
		$one_month_ago					=	date('Y-m-d H:i:s',strtotime('-1 month'));

		$this							->	db->select('item_number, sales_items.description, category, volume, nicotine, item_cost_price, item_unit_price, quantity_purchased, line_profit, line_sales');
		$this							->	db->from('sales_items, sales, items');
		$this							->	db->where('sales.sale_id = `ospos_sales_items`.sale_id AND item_number = line_item_number AND line_sales > 0 AND sale_time > "'.$one_month_ago.'"');
		$this							->	db->order_by('item_number');
		$report_data					= 	$this->db->get()->result_array();


		$quantity_purchased[$index] = 0;
		$line_profit[$index] = 0;
		$line_sale[$index] = 0;
		foreach($report_data as $index=>$row)
		{
			$check[$index][0] = $report_data[$index]['item_number'];
			if($check[$index][0] != $check[$index-1][0])
			{
				$quantity_purchased[$index] += $report_data[$index]["quantity_purchased"];
				$line_profit[$index] += $report_data[$index]["line_profit"];
				$line_sale[$index] += $report_data[$index]["line_sales"];
				for($index2=$index+1; $index2<=sizeof($report_data); $index2++)
				{
					$check[$index][1] = $report_data[$index2]['item_number'];
					$check[$index][2] = $report_data[$index2]['quantity_purchased'];
					$check[$index][3] = $report_data[$index2]['line_profit'];
					$check[$index][4] = $report_data[$index2]['line_sales'];
					if($check[$index][0] == $check[$index][1])
					{
						$quantity_purchased[$index] += $check[$index][2];
						$line_profit[$index] += $check[$index][3];
						$line_sale[$index] += $check[$index][4];
					}
					else
					{
						break;
					}
				}
				$tabular_data[$index][] = $row['item_number'];
				$tabular_data[$index][] = $row['description'];
				$tabular_data[$index][] = $row['category'];
				$tabular_data[$index][] = $row['volume'];
				$tabular_data[$index][] = $row['nicotine'];
				$tabular_data[$index][] = $row['item_cost_price'];
				$tabular_data[$index][] = $row['item_unit_price'];
				$tabular_data[$index][] = $quantity_purchased[$index];
				$tabular_data[$index][] = $line_profit[$index];
				$tabular_data[$index][] = $line_sale[$index];
			}
		}
		function compare($a, $b)
		{
			return $b[7] - $a[7];
		}
		usort($tabular_data, "compare");
		for($i=50; $i<=sizeof($tabular_data); $i++)
		{
			for($j=0; $j<10; $j++)
			{
				unset($tabular_data[$i][$j]);

			}
		}
		$one_month_ago_					=	date('d/m/Y H:i:s',strtotime('-1 month'));
		$data = array	(
			"title" 		=> $this->lang->line('reports_top_items_by_quantity'),
			"subtitle" 		=> $this->lang->line('common_top_sale_since').' '.$one_month_ago_.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
			"headers" 		=> $headers,
			"data" 			=> $tabular_data,
			"export_excel" 	=> $export_excel
			);


		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}
	
	
	function top_employees()
	{
		// load required routines
		$this->load->model('Common_routines');
		$this->load->model('Employee');
		$this->load->library('../controllers/employees');
		
		// set up the pagination
		$config						=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 		= 	site_url('/employees/index');
		$config['total_rows'] 		= 	$this->Employee->count_all($where_select);
		$this						->	pagination->initialize($config);
			
		// setup output data
		$data['links']				=	$this->pagination->create_links();
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->employees->get_form_width();
		$data['title']				=	$this->lang->line('reports_top_employees');

		$create_headers				=	1;
		$order_by					=	'sales_ht';
		$sequence					=	'desc';
		
		$data['manage_table']		=	get_people_manage_table($this->Employee->get_all($config['per_page'], $this->uri->segment($config['uri_segment']), $order_by, $sequence), $this, $create_headers);

		// show data
		$this						->	load->view('people/manage', $data);
	}

	// -----------------------------------------------------------------------------------------				
	// Update the db
	// -----------------------------------------------------------------------------------------
	
	// update ospos_sales_items
	function updatedb_sales_items()
	{
			// get the data
			$data 							=	array();
			$this							->	db->select();
			$this							->	db->from('sales_items');
			$this							->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
			$data 							= 	$this->db->get()->result_array();

			// calculate the values
			$row							=	array();
			foreach ($data as $row)
			{
				$line_sales_before_discount	=	$row['quantity_purchased'] * $row['item_unit_price'];
				$line_discount				=	$line_sales_before_discount * $row['discount_percent'] / 100;
				$line_sales_after_discount	=	$line_sales_before_discount - $line_discount;
				$line_tax_percentage		=	$this->config->item('default_tax_1_rate');
				$line_tax					=	$line_sales_after_discount * $line_tax_percentage / 100;
				$line_sales					=	$line_sales_after_discount + $line_tax;
				$line_cost					=	$row['quantity_purchased'] * $row['item_cost_price'];
				$line_profit				=	$line_sales_after_discount - $line_cost;
				$line_tax_name				=	$this->config->item('default_tax_1_name');
				
				$this						->	db->from('items');
				$this						->	db->where('item_id', $row['item_id']);
				$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
				$item_data 					= 	$this->db->get()->row_array();

				$line_category				=	$item_data['category'];
				$line_item_number			=	$item_data['item_number'];
				$line_name					=	$item_data['name'];
				
				// load the data
				$sales_items_data 			= 	array	(
														'sale_id'						=>	$row['sale_id'],
														'item_id'						=>	$row['item_id'],
														'line_category'					=>	$line_category,
														'line_item_number'				=>	$line_item_number,
														'line_name'						=>	$line_name,
														'description'					=>	$row['description'],
														'serialnumber'					=>	$row['serialnumber'],
														'line'							=>	$row['line'],
														'quantity_purchased'			=>	$row['quantity_purchased'],
														'item_cost_price' 				=> 	$row['item_cost_price'],
														'item_unit_price'				=>	$row['item_unit_price'],
														'discount_percent'				=>	$row['discount_percent'],
														'line_sales_before_discount'	=>	$line_sales_before_discount,
														'line_discount'					=>	$line_discount,
														'line_sales_after_discount'		=>	$line_sales_after_discount,
														'line_tax'						=>	$line_tax,
														'line_sales'					=>	$line_sales,
														'line_cost'						=>	$line_cost,
														'line_profit'					=>	$line_profit,
														'line_tax_percentage'			=>	$line_tax_percentage,
														'line_tax_name'					=>	$line_tax_name
														);

				// update the data
				$this						->	db->where('sale_id', $row['sale_id']);
				$this						->	db->where('line', $row['line']);
				$this						->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
				$this						->	db->update('sales_items', $sales_items_data);
			}
			
			// set flash data and return to controller
			$success_or_failure				=	'S';
			$message						=	'Sales Items update complete.';
			$this							->	setflash($success_or_failure, $message);
	}
	
	// update ospos_sales_items
	function updatedb_sales_headers()
	{
			// get the data
			$data 							=	array();
			$this							->	db->select();
			$this							->	db->from('sales');
			$this							->	db->where('sales.branch_code', $this->config->item('branch_code'));
			$data 							= 	$this->db->get()->result_array();

			// read the data
			$row							=	array();
			foreach ($data as $row)
			{
				// get the sales_items
				$this						->	db->from('sales_items');
				$this						->	db->where('sale_id', $row['sale_id']);
				$this						->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
				$sales_item_data 			= 	$this->db->get()->result_array();
				
				// read through the lines and sum line sales after discount
				// discount can be applied at two levels
				// - line level
				// - total level
				$subtotal_before_discount		=	0;
				$overall_cost					=	0;
				foreach ($sales_item_data as $line_row)
				{
					$subtotal_before_discount	=	$subtotal_before_discount + $line_row['line_sales_after_discount'];
					$overall_cost				=	$overall_cost + $line_row['line_cost'];
				}
				
				// calculate the values
				$subtotal_discount_percentage_amount	=	$subtotal_before_discount * $overall_discount_percentage / 100;
				$subtotal_discount_amount_amount		=	$overall_discount_amount;
				$subtotal_after_discount				=	$subtotal_before_discount - ($subtotal_discount_percentage_amount + $subtotal_discount_amount_amount);
				$overall_tax_percentage					=	$this->config->item('default_tax_1_rate');
				$overall_tax							=	$subtotal_after_discount * $overall_tax_percentage / 100;
				$overall_total							=	$subtotal_after_discount + $overall_tax;
				$overall_tax_name						=	$this->config->item('default_tax_1_name');
				$overall_profit							=	$subtotal_after_discount - $overall_cost;
				
				// load the data
				$sales_data = array	(
									'subtotal_before_discount'				=>	$subtotal_before_discount,
									'subtotal_discount_percentage_amount'	=>	$subtotal_discount_percentage_amount,
									'subtotal_discount_amount_amount'		=>	$subtotal_discount_amount_amount,
									'subtotal_after_discount'				=>	$subtotal_after_discount,
									'overall_tax'							=>	$overall_tax,
									'overall_total'							=>	$overall_total,
									'overall_tax_percentage'				=>	$overall_tax_percentage	,
									'overall_tax_name'						=>	$overall_tax_name,
									'overall_cost'							=>	$overall_cost,
									'overall_profit' 						=> 	$overall_profit,
									);

				// update the data
				$this						->	db->where('sale_id', $row['sale_id']);
				$this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
				$this						->	db->update('sales', $sales_data);
			}
			
			// set flash data and return to controller
			$success_or_failure				=	'S';
			$message						=	'Sales Headers update complete.';
			$this							->	setflash($success_or_failure, $message);
	}
	
	// update the customers file with latest sales data	
	function updatedb_customers_sales_total()
	{
		// zero the fields being updated
		$new_total						=	0;
		$new_total_number_of			=	0;
		$customer_data					=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of
													);
		$this							->	db->update('customers', $customer_data);
		
		// get the data
		$data 							=	array();
		$this							->	db->select();
		$this							->	db->from('sales');
		$this							->	db->where('sales.branch_code', $this->config->item('branch_code'));
		$data 							= 	$this->db->get()->result_array();

		// read the data and update customer file
		$row							=	array();
		foreach ($data as $row)
		{
			$this						->	db->from('customers');
			$this						->	db->where('person_id', $row['customer_id']);
			$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
			$trans_data 				= 	$this->db->get()->row_array();
			
			$new_total					=	$trans_data['sales_ht'] + $row['subtotal_after_discount'];
			$new_total_number_of		=	$trans_data['sales_number_of'] + 1;
			
			$customer_data				=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of
													);
													
			$this						->	db->where('person_id', $row['customer_id']);
			$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
			$this						->	db->update('customers', $customer_data);
		}
		
		// set flash data and return to controller
		$success_or_failure				=	'S';
		$message						=	'Customers Sales Totals update complete.';
		$this							->	setflash($success_or_failure, $message);
	}
	
	// update the employees file with latest sales data	
	function updatedb_employees_sales_total()
	{
		// zero the fields being updated
		$new_total						=	0;
		$new_total_number_of			=	0;
		$employee_data					=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of
													);
		$this							->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this							->	db->update('employees', $employee_data);
		
		// get the data
		$data 							=	array();
		$this							->	db->select();
		$this							->	db->from('sales');
		$this							->	db->where('sales.branch_code', $this->config->item('branch_code'));
		$data 							= 	$this->db->get()->result_array();

		// read the data and update customer file
		$row							=	array();
		foreach ($data as $row)
		{
			$this						->	db->from('employees');
			$this						->	db->where('person_id', $row['employee_id']);
			$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
			$trans_data 				= 	$this->db->get()->row_array();

			$new_total					=	$trans_data['sales_ht'] + $row['subtotal_after_discount'];
			$new_total_number_of		=	$trans_data['sales_number_of'] + 1;
			
			$employee_data				=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of
													);
													
			$this						->	db->where('person_id', $row['employee_id']);
			$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
			$this						->	db->update('employees', $employee_data);
		}
		
		// set flash data and return to controller
		$success_or_failure				=	'S';
		$message						=	'Employees Sales Totals update complete.';
		$this							->	setflash($success_or_failure, $message);
	}
	
	// update the items file with latest sales data	
	function updatedb_items_sales_total()
	{
		// zero the fields being updated
		$new_total						=	0;
		$new_total_qty					=	0;
		$item_data						=	array	(
													'sales_ht'	=>	$new_total,
													'sales_qty'	=>	$new_total_qty
													);
		$this							->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this							->	db->update('items', $item_data);
				
		// get the sales data
		$data 							=	array();
		$this							->	db->select();
		$this							->	db->from('sales_items');
		$this							->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
		$data 							= 	$this->db->get()->result_array();

		// read the data and update items file
		$row							=	array();
		foreach ($data as $row)
		{
			$this						->	db->from('items');
			$this						->	db->where('item_id', $row['item_id']);
			$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
			$trans_data 				= 	$this->db->get()->row_array();

			$new_total					=	$trans_data['sales_ht'] + $row['line_sales_after_discount'];
			$new_total_qty				=	$trans_data['sales_qty'] + $row['quantity_purchased'];
			
			$item_data					=	array	(
													'sales_ht'	=>	$new_total,
													'sales_qty'	=>	$new_total_qty
													);
													
			$this						->	db->where('item_id', $row['item_id']);
			$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
			$this						->	db->update('items', $item_data);
		}
		
		// set flash data and return to controller
		$success_or_failure				=	'S';
		$message						=	'Items Sales Totals update complete.';
		$this							->	setflash($success_or_failure, $message);
	}
	
	// update the branch code
	function updatedb_branch_code()
	{
		// get the branch code
		$branch_code					=	$this->config->item('branch_code');
				
		// read the tables file
		$this							->	db->from('database_tables');
		$this							->	db->order_by('table_sequence asc'); 	
		$tables							= 	$this->db->get()->result_array();
		
		// read table names
		foreach ($tables as $table)
		{
			// test branch code exists in table
			if ($this->db->field_exists('branch_code', $table['table_name']))
			{
				$item_data				=	array	(
													'branch_code'	=>	$branch_code,
													);
				$this					->	db->update($table['table_name'], $item_data);
			}
		}
		
		// set flash data and return to controller
		$success_or_failure				=	'S';
		$message						=	$this->lang->line('reports_branch_code_updated');
		$this							->	setflash($success_or_failure, $message);
	}
	
	// update volume
	function updatedb_volume()
	{
		// initilise
		$needle							=	'ML';
		$_SESSION['transaction_info']	=	new stdClass();
		unset($_SESSION['new']);
		unset($_SESSION['undel']);
		
		// get all items
		$items							=	$this->Item->get_all()->result_array();		

		// read item
		foreach ($items as $item)
		{			
			// reset update data
			unset($_SESSION['transaction_info']);
			
			// search for volume string
			$pos = strpos($item['name'], $needle);

			// test if found
			if ($pos !== false) 
			{
				$name_split				=	str_split($item['name'], 1);
				if ($name_split[$pos-1] == ' ')
				{
					$_SESSION['transaction_info']->volume				=	$name_split[$pos-3].$name_split[$pos-2];
				}
				else
				{
					$_SESSION['transaction_info']->volume				=	$name_split[$pos-2].$name_split[$pos-1];
				}
				
				// update items
				$_SESSION['transaction_info']->item_id					=	$item['item_id'];
				$this->Item->save();
				
				// increment count
				$count													=	$count	+	1;
			}
		}
		
		/// set message
		$_SESSION['transaction_info']->count							=	$count;
		$_SESSION['error_code']											=	'01690';
		redirect("reports/index");
	}
	
	// update volume
	function updatedb_nicotine()
	{
		echo ('processing');
		
		// initialise
		$needle							=	'MG';
		$_SESSION['transaction_info']	=	new stdClass();
		$count							=	0;
		unset($_SESSION['new']);
		unset($_SESSION['undel']);
		
		// get all items
		$items							=	$this->Item->get_all()->result_array();		

		// read item
		foreach ($items as $item)
		{			
			// reset update data
			unset($_SESSION['transaction_info']);
			
			// search for nicotine string
			$pos = strpos($item['name'], $needle);

			// test if found
			if ($pos !== false) 
			{
				$name_split				=	str_split($item['name'], 1);
				if ($name_split[$pos-1] == ' ')
				{
					$_SESSION['transaction_info']->nicotine				=	$name_split[$pos-3].$name_split[$pos-2];
				}
				else
				{
					$_SESSION['transaction_info']->nicotine				=	$name_split[$pos-2].$name_split[$pos-1];
				}
				
				// update items
				$_SESSION['transaction_info']->item_id					=	$item['item_id'];
				$this->Item->save();
				
				// increment count
				$count													=	$count	+	1;
			}
		}

		// set message
		$_SESSION['transaction_info']->count							=	$count;
		$_SESSION['error_code']											=	'01680';
		redirect("reports/index");
	}
	
	
	// -----------------------------------------------------------------------------------------				
	// Month End Routines
	// -----------------------------------------------------------------------------------------
	
	function month_end_routines()
	{
		// set month end switch
		$month_end														=	1;
		
		// set other switches
		$create_PO														=	0;
		$set_NM															=	0;
		$Set_SM															=	0;
		$export_excel													=	1;
		$details														=	0;
				
		// create stock inventory valuation report in excel format
		$this															->	inventory_summary($export_excel, $create_PO, $set_NM, $set_SM, $month_end);
		
		// create negative stock
		$this															->	inventory_negative_stock($export_excel, $create_PO, $set_NM, $set_SM, $month_end);
		
		// create invalid item numbers
		$this															->	inventory_invalid_item_number($export_excel, $create_PO, $set_NM, $set_SM, $month_end);
				
		// reset rolling inventory indicator
		$this															->	load->library('../controllers/items');
		$this															->	items->reset_rolling();
		
		// create COGS report
		$this															->	cogs($export_excel, $month_end, $details);
		
		// create customer profile report 
		$this															->	customer_sales_profile($export_excel, $month_end, $details);
		
		// set flash data and return to controller
		$success_or_failure												=	'S';
		$message														=	'Month End routine complete. See '.$this->config->item('RPsavepath').' for the reports.';
		$this															->	setflash($success_or_failure, $message);
	}
	
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{			
		return 500;
	}
	
	// set the flash data
	function setflash($success_or_failure, $message)
	{
		$this							->	session->set_flashdata('success_or_failure', $success_or_failure);
		$this							->	session->set_flashdata('message', $message);
		redirect('reports');
	}
	
	// -----------------------------------------------------------------------------------------				
	// Update purchase prices in the database from downloaded csv sheet
	// -----------------------------------------------------------------------------------------
	
	function update_purchase_prices()
	{
		// load model
		$this->load->model('Item');
		
		// initalise counts
		$number_of_records					=	0;
		$number_of_updates					=	0;
		$diff_stk_val						=	0;
		$total_diff							=	0;
				
		// set switches for stock valuation
		$month_end							=	0;
		$create_PO							=	0;
		$set_NM								=	0;
		$set_SM								=	0;
		$export_excel						=	1;
		$details							=	0;
		
		// stock valuation - before
		$this								->	inventory_summary($export_excel, $create_PO, $set_NM, $set_SM, $month_end);
			
		// set output CSV name - records updated are output here
		$this								->	load->helper('date');
		$now								=	time();
		$pieces								=	explode(".", $this->config->item('PPfilename'));
		$csv_data_file = $this->config->item('PPsavepath').$pieces[0].'_update_'.$now.'.csv';
		// write column headers
		file_put_contents($csv_data_file,	'Item_number',		FILE_APPEND);
		file_put_contents($csv_data_file,	';',				FILE_APPEND);
		file_put_contents($csv_data_file,	'Description',		FILE_APPEND);
		file_put_contents($csv_data_file,	';',				FILE_APPEND);
		file_put_contents($csv_data_file,	'Old Cost',			FILE_APPEND);
		file_put_contents($csv_data_file,	';',				FILE_APPEND);
		file_put_contents($csv_data_file,	'New Cost',			FILE_APPEND);
		file_put_contents($csv_data_file,	';',				FILE_APPEND);
		file_put_contents($csv_data_file,	'Diff Stk Val',		FILE_APPEND);
		file_put_contents($csv_data_file,	';',				FILE_APPEND);
		file_put_contents($csv_data_file,	"\n",				FILE_APPEND);
		
		// open the csv file containing new stuff 
		if (($handle = fopen($this->config->item('PPsavepath').$this->config->item('PPfilename'), "r")) === FALSE) 
		{
			$success_or_failure				=	'F';
			$message						=	'Purchase price update failed - file not found.';
			$this							->	setflash($success_or_failure, $message);
		}
		else
		{
		// now read it
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) 
			{
				// count records read
				$number_of_records				=	$number_of_records + 1;
				
				// get item record
				$item_id						=	$this->Item->get_item_id($data[2]);
				$item_info						=	$this->Item->get_info($item_id);
				
				// test data found
				if (strlen($item_info->item_id)	> 0)
				{
					// update item file if cost price has changed
					if ($item_info->cost_price != $data[6] OR $item_info->name != $data[5])
					{
						$item_data						=	array	(
																	'name'			=>	$data[5],
																	'cost_price'	=>	$data[6]
																	);
						$this							->	db->where('item_number', $data[2]);
						$this							->	db->where('items.branch_code', $this->config->item('branch_code'));
						
						if ($this	->	db->update('items', $item_data));
						{
							$number_of_updates			=	$number_of_updates + 1;
							$diff_stk_val				=	($item_info->quantity * $item_info->cost_price) - ($item_info->quantity * $data[6]);
							$total_diff					=	$total_diff + $diff_stk_val;
							
							file_put_contents($csv_data_file,	$data[2],				FILE_APPEND);
							file_put_contents($csv_data_file,	';',					FILE_APPEND);
							file_put_contents($csv_data_file,	$data[5],				FILE_APPEND);
							file_put_contents($csv_data_file,	';',					FILE_APPEND);
							file_put_contents($csv_data_file,	$item_info->cost_price,	FILE_APPEND);
							file_put_contents($csv_data_file,	';',					FILE_APPEND);
							file_put_contents($csv_data_file,	$data[6],				FILE_APPEND);
							file_put_contents($csv_data_file,	';',					FILE_APPEND);
							file_put_contents($csv_data_file,	$diff_stk_val,			FILE_APPEND);
							file_put_contents($csv_data_file,	';',					FILE_APPEND);
							file_put_contents($csv_data_file,	"\n",					FILE_APPEND);
						}
					}
				}
			}
			
			// output totals
			file_put_contents($csv_data_file,	"\n",					FILE_APPEND);
			file_put_contents($csv_data_file,	';;;',					FILE_APPEND);
			file_put_contents($csv_data_file,	'Total Diff.',			FILE_APPEND);
			file_put_contents($csv_data_file,	';',					FILE_APPEND);
			file_put_contents($csv_data_file,	$total_diff,			FILE_APPEND);
			file_put_contents($csv_data_file,	';',					FILE_APPEND);
			file_put_contents($csv_data_file,	"\n",					FILE_APPEND);
			
			// close the input file
			fclose($handle);
			
			// stock valuation - after
			$this							->	inventory_summary($export_excel, $create_PO, $set_NM, $set_SM, $month_end);
		
			// set flash data and return to controller
			$success_or_failure				=	'S';
			$message						=	'Purchase price update completed. See stock valuation reports for change to stock value. Records read -> '.$number_of_records.', number of updates -> '.$number_of_updates.'.';
			$this							->	setflash($success_or_failure, $message);
		}
	}
	
	// -----------------------------------------------------------------------------------------				
	// Report DLUO errors
	// -----------------------------------------------------------------------------------------
	
	function dluo_qty_error($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{
		// initialise
		$this								->	load->library('../controllers/items');
		$this								->	load->model('Item');
		$_SESSION['undel']					=	3;
		unset($_SESSION['line']);

		// set up parms
		$all_dluo_items						=	array();
		$limit								=	10000;
		$offset								=	0;
		$edit_file 							= 	'items/view/';
		$inputs								=	array();
		$inputs['where']					= 	'ospos_items.deleted = 0';
		$inputs['report']					=	'DL';
		$success_or_failure					=	'R';
		$message							=	'Hello';
		$origin								=	'DL';

		// get all dluo items
		$all_dluo_items						=	$this->Item->get_all($limit, $offset)->result_array();				

		// read all dluo items
		foreach	($all_dluo_items as $index=>$dluo_item)
		{
			// zero total dluo qty
			$dluo_total_qty					=	0;
			
			// get all dluo records this item_id
			$item_info_dluo					=	array();
			$item_info_dluo					=	$this->Item->get_info_dluo($dluo_item['item_id'])->result_array();

			// calculate total dluo qty
			foreach ($item_info_dluo as $row)
			{
				// accumulate total dluo quantity
				$dluo_total_qty				=	$dluo_total_qty + $row['dluo_qty'];
			}
			
			// now test dluo total qty against item qty
			if ($dluo_total_qty !=	$dluo_item['quantity'])
			{
				// fix blank item numbers
				if ($dluo_item['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
				
				// get item supplier data for item cost using default supplier
				$cost_price			 		=	$this->get_item_supplier_cost($dluo_item['item_id']); //$this->config->item('default_supplier_id')

				// load each line to the output array
				$tabular_data[$index] 		= array	(
													$dluo_item['category'],
													anchor	(
															$edit_file.$dluo_item['item_id'].'/'.$origin, 
															$dluo_item['item_number']
															),
													$dluo_item['name'],
													$cost_price,
													anchor	(
															'items/inventory/'.$dluo_item['item_id'].'/'.$origin, 
															$dluo_item['quantity'] 
															),
													anchor	(
															'items/dluo_form/'.$dluo_item['item_id'].'/'.$origin, 
															$dluo_total_qty
															),
													);
										
				// Set the index for the line selected by the user
				if ($dluo_item['item_id'] == $_SESSION['sel_item_id'])
				{
					$_SESSION['line']		=	$index;
				}
			}
		}
		
		// load data array for display
		$today_date							=	date('d/m/Y à H:i:s', time());
		$data = array	(
						"title" 			=> $this->lang->line('reports_dluo_qty_error'),
						"subtitle" 			=> $today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 			=> $this->Stock_queries->getDataColumns($inputs),
						"data" 				=> $tabular_data,
						"summary_data" 		=> $this->Stock_queries->getSummaryData(array()),
						"export_excel" 		=> $export_excel
						);
						
		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}
	
	function dluo_past_date($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{
		// initialise
		$this								->	load->library('../controllers/items');
		$this								->	load->model('Item');
		$_SESSION['undel']					=	3;
		unset($_SESSION['line']);
		$origin								=	'DD';
		$all_dluo_items						=	array();
		$inputs								=	array();
		$limit								=	10000;
		$offset								=	0;
		$edit_file 							= 	'items/view/';

		// set up parms		
		
		$inputs['where']					= 	'ospos_items.deleted = 0';
		$inputs['report']					=	'DD';
		$success_or_failure					=	'R';
		$message							=	'Hello';
		$this_year							=	date("Y");
		$this_month							=	date("m");
		
	
		// get all dluo items
		$all_dluo_items						=	$this->Item->get_all($limit, $offset)->result_array();				

		// read all dluo items
		foreach	($all_dluo_items as $index=>$dluo_item)
		{			
			// get all dluo records this item_id
			$item_info_dluo					=	array();
			$item_info_dluo					=	$this->Item->get_info_dluo($dluo_item['item_id'])->result_array();
			
			// compare dates
			foreach ($item_info_dluo as $row)
			{
				if (($row['year'] < $this_year) OR ($row['year'] == $this_year AND $row['month'] < $this_month))
				{
					// output to report
					if ($dluo_item['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
					
					// get item supplier data for item cost using default supplier
					$cost_price 			=	$this->get_item_supplier_cost($dluo_item['item_id']);//, $this->config->item('default_supplier_id')
					
					// load each line to the output array
					$tabular_data[$index.$row['year'].$row['month']] = array(
																			$dluo_item['category'],
																			anchor	(
																					$edit_file.$dluo_item['item_id'].'/'.$origin, 
																					$dluo_item['item_number']
																					),
																			$dluo_item['name'],
																			$cost_price, 
																			$dluo_item['quantity'],
																			anchor	(
																					'items/dluo_form/'.$dluo_item['item_id'].'/'.$origin, 
																					$row['dluo_qty']
																					),
																			);
											
					// Set the index for the line selected by the user
					if ($dluo_item['item_id'] == $_SESSION['sel_item_id'])
					{
						$_SESSION['line']		=	$index.$row['year'].$row['month'];
					}
				} 
			}
		}
		
		// load data array for display
		$today_date							=	date('d/m/Y à H:i:s', time());
		$data = array	(
						"title" 			=>	$this->lang->line('reports_dluo_date_past'),
						"subtitle" 			=>	$today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 			=> 	$this->Stock_queries->getDataColumns($inputs),
						"data" 				=> 	$tabular_data,
						"summary_data" 		=> 	$this->Stock_queries->getSummaryData(array()),
						"export_excel" 		=> 	$export_excel
						);
						
		// file or display?
		$this								->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}



	function choix_du_nombre_de_mois($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
    {
		//unset($_SESSION['choix_mois']);
		$_SESSION['choix_mois']=0;
		$_SESSION['varible_pour_choix_mois']=6;
		//$_SESSION['show_dialog']='8';
		$_SESSION['temps_DLUO']='1';
		$this->load->view('reports/Nouveau');
	//	$_SESSION['choix_mois']=$_POST['choix_mois'];
		$this->dluo_future_date($export_excel, $create_PO, $set_NM, $set_SM, $month_end);
		
    }	


    function dluo_future_date($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{

		$_SESSION['choix_mois']=$_POST['choix_mois'];
		$_SESSION['temps_DLUO']='1';
		$_SESSION['varible_pour_choix_mois']=6;
		$choix_mois=$_SESSION['choix_mois'];
		
		//Saisie du nombre de mois non blindé
		if(!is_numeric($_SESSION['choix_mois']))
		{
			//redirect("reports");
		}
		if(!is_int($_SESSION['choix_mois']))
		{
			//redirect("reports");
		}
		

		// initialise
		$this								->	load->library('../controllers/items');
		$this								->	load->model('Item');
		$_SESSION['undel']					=	3;
		unset($_SESSION['line']);
		$origin								=	'DF';
		$all_dluo_items						=	array();
		$inputs								=	array();
		$limit								=	10000;
		$offset								=	0;
		$edit_file 							= 	'items/view/';

		// set up parms		
		$inputs['where']					= 	'ospos_items.deleted = 0';
		$inputs['report']					=	'DD';
		$success_or_failure					=	'R';
		$message							=	'Hello';
		$this_year							=	date("Y");
		//$this_month							=	date("m") + 5;
		$this_month							=	date("m") + $_SESSION['choix_mois'];
		if ($this_month > 12)
		{
			$this_month = $this_month % 12;
			$this_year = $this_year + 1;
		}
	
		// get all dluo items
		$all_dluo_items						=	$this->Item->get_all($limit, $offset)->result_array();				

		// read all dluo items
		foreach	($all_dluo_items as $index=>$dluo_item)
		{			
			// get all dluo records this item_id
			$item_info_dluo					=	array();
			$item_info_dluo					=	$this->Item->get_info_dluo($dluo_item['item_id'])->result_array();
			
			// compare dates
			foreach ($item_info_dluo as $row)
			{
				//Ajout de la vérification pour afficher que les articles dont la DLUO est inférieur au nombre de mois rentrés en dynamique  
				if (($row['year'] < $this_year) OR ($row['year'] == $this_year AND $row['month'] < $this_month))
				{
					// output to report
					if ($dluo_item['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
					
					// get item supplier data for item cost using default supplier
					$cost_price 			=	$this->get_item_supplier_cost($dluo_item['item_id']);//, $this->config->item('default_supplier_id')
					
					// load each line to the output array
					$tabular_data[$index.$row['year'].$row['month']] = array(
																			$dluo_item['category'],
																			anchor	(
																					$edit_file.$dluo_item['item_id'].'/'.$origin, 
																					$dluo_item['item_number']
																					),
																			$dluo_item['name'],
																			$cost_price, 
																			$dluo_item['quantity'],
																			anchor	(
																					'items/dluo_form/'.$dluo_item['item_id'].'/'.$origin, 
																					$row['dluo_qty']
																					),
																			);
					// Set the index for the line selected by the user
					if ($dluo_item['item_id'] == $_SESSION['sel_item_id'])
					{
						$_SESSION['line']	=	$index.$row['year'].$row['month'];
					}
				} 
			}
		}
		
		// load data array for display
		$today_date							=	date('d/m/Y à H:i:s', time());
		$data = array	(
			//"title" 			=>	$this->lang->line('reports_dluo_date_future'),
			            "title" 			=>	$this->lang->line('reports_dluo_date_future_choix_mois') . ' ( < ' . $_SESSION['choix_mois'] . ' mois )',
						"subtitle" 			=>	$today_date.' '.$this->lang->line('common_for').' '.$this->db->database.'.',
						"headers" 			=> 	$this->Stock_queries->getDataColumns($inputs),
						"data" 				=> 	$tabular_data,
						"summary_data" 		=> 	$this->Stock_queries->getSummaryData(array()),
						"export_excel" 		=> 	$export_excel
						);
						
		// file or display?
		$this								->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}
	
	// ------------------------------------------------------------------------------------------------------
	// FDJ reporting
	// ------------------------------------------------------------------------------------------------------
	
	function	fdj_reporting($start_date, $end_date, $transaction_subtype, $export_excel=0)
	{
		// initialise
		$transaction_subtype			=	'FDJ';
		$report_type					=	'FD';
		
		// load report data
		$report_data 					= 	$this->Fdj->fdj_getData(array('start_date'=>$start_date, 'end_date'=>$end_date));
		
		// format data
		foreach($report_data['summary'] as $key=>$row)
		{								
			// load summary data
			$summary_data[] 			=	array	(
													$row['sale_id'],
													$row['date(sale_time)'],
													$row['sale_total']
													);
			// calculate overall total								
			$overall_sale_total			=	$overall_sale_total + $row['sale_total'];

			// get detailed data
			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$key][] 	=	array	(
													$drow['sale_item_number'],
													$drow['sale_qty'],
													to_currency_no_money($drow['sale_value']),
													);
				
				// get totals									
				switch($drow['sale_item_number'])
				{
					case 'fdj_1':
						$overall_fdj_1_qty	=	$overall_fdj_1_qty + $drow['sale_qty'];
						$overall_fdj_1_val	=	$overall_fdj_1_val + $drow['sale_value'];
						break;
					case 'fdj_2':
						$overall_fdj_2_qty	=	$overall_fdj_2_qty + $drow['sale_qty'];
						$overall_fdj_2_val	=	$overall_fdj_2_val + $drow['sale_value'];
						break;
					case 'fdj_3':
						$overall_fdj_3_qty	=	$overall_fdj_3_qty + $drow['sale_qty'];
						$overall_fdj_3_val	=	$overall_fdj_3_val + $drow['sale_value'];
						break;
					case 'fdj_5':
						$overall_fdj_5_qty	=	$overall_fdj_5_qty + $drow['sale_qty'];
						$overall_fdj_5_val	=	$overall_fdj_5_val + $drow['sale_value'];
						break;
					case 'fdj_10':
						$overall_fdj_10_qty	=	$overall_fdj_10_qty + $drow['sale_qty'];
						$overall_fdj_10_val	=	$overall_fdj_10_val + $drow['sale_value'];
						break;
					case 'fdj_gain':
						$overall_fdj_win_qty=	$overall_fdj_win_qty + $drow['sale_qty'];
						$overall_fdj_win_val=	$overall_fdj_win_val + $drow['sale_value'];
						break;
					default:
						break;
				}
			}
		}
		
		// load output data elements
		$title = $this->lang->line('fdj_detailed_transactions_report');
		$overall_summary_data				=	array	(
														'total'		=>	"$overall_sale_total €",
														'none'		=>	'---',
														'title'		=>	$this->lang->line('fdj_title'),
														'none1'		=>	'---',
														'1€'		=>	"$overall_fdj_1_qty => $overall_fdj_1_val €",
														'2€'		=>	"$overall_fdj_2_qty => $overall_fdj_2_val €",
														'3€'		=>	"$overall_fdj_3_qty => $overall_fdj_3_val €",
														'5€'		=>	"$overall_fdj_5_qty => $overall_fdj_5_val €",
														'10€'		=>	"$overall_fdj_10_qty => $overall_fdj_10_val €",
														'win'		=>	"$overall_fdj_win_qty => $overall_fdj_win_val €"
														);
		// load data
		$this->output_load_data($data, $title, $subtitle, $summary_data, $details_data, $overall_summary_data, $export_excel, $start_date, $end_date, $transaction_subtype, $report_type);
	
		// show the data
		$this->load->view("reports/tabular_details", $data);	
	}
	
	// ------------------------------------------------------------------------------------------------------
	// Create stock_valuation records first time
	// ------------------------------------------------------------------------------------------------------
	
	function	update_stock_valuation_records ()
	{
		// check if records are to be created
		if ($this->config->item('createstockvaluationrecords') == 'N')
		{
			// load model
			$this->load->model('Item');
			
			// if so empty the table
			$this->Item->value_delete_all();
			
			// read all items
			$item_data						=	array();
			$where_select					=	'deleted = 0';
			$item_data						=	$this->Item->get_all()->result_array();
			foreach ($item_data as $item)
			{
				// create stock valuation record
				$data						=	array	();
				$data						=	array	(
														'value_item_id'		=>	$item['item_id'],
														'value_cost_price'	=>	$item['cost_price'],
														'value_qty'			=>	$item['quantity'],
														'value_trans_id'	=>	0,
														'branch_code'		=>	$this->config->item('branch_code')
														);
				$this->Item->value_write($data);
			}
			
			// set flag to N
			$batch_save_data				=	array	(
														'createstockvaluationrecords'	=>	'N'
														);
			$this							->	Appconfig->batch_save($batch_save_data);
			
			// set flash data and return to controller
			$success_or_failure				=	'S';
			$message						=	'Stock valuation records created.';
			$this							->	setflash($success_or_failure, $message);
		}
		else
		{
			// set flash data and return to controller
			$success_or_failure				=	'F';
			$message						=	'Nothing to do; check createstockvaluationrecords tag in configuration.';
			$this							->	setflash($success_or_failure, $message);
		}
														
	}
	
	// ------------------------------------------------------------------------------------------------------
	// Verify the stock valuation qauntities to stock qty
	// ------------------------------------------------------------------------------------------------------
	
	function	inventory_value_record_integrity ($export_excel=0, $create_PO=0, $set_NM=0, $set_SM=0, $month_end=0)
	{
		// load appropriate files
		$this							->	load->library('../controllers/items');
				
		// set up parameters for model
		$inputs							=	array();
		$inputs['where']				= 	'ospos_items.deleted = 0';
		$inputs['report']				=	'VS';
		
		// set variables
		$tabular_data 					= 	array();
		$edit_file 						= 	'items/view/';
		$width 							= 	$this->items->get_form_width();
		$stock_total 					= 	0;
		$origin							=	'VS';
		
		// get the report data
		$report_data 					= 	$this->Stock_queries->getData($inputs);

		foreach($report_data as $row)
		{
			// if on hand qty = 0, delete all stock valuation records
			if ($row['quantity'] == 0)
			{
				$this					->	Item->value_delete_item_id($row['item_id']);
			}
						
			// get stock valuation records
			$stock_value_data			=	$this->Stock_queries->get_stock_value_data($row['item_id']);
			
			// read stock valuation data
			$item_qty					=	0;
			foreach ($stock_value_data as $value_record)
			{
				$item_qty				=	$item_qty + $value_record['value_qty'];
			}
			
			// test quantity correct, if not output record
			$attn						=	'';
			if ($item_qty != $row['quantity'])
			{
				// set flag
				$attn					=	'ATTN';
				
				// set up the item_number to handle blanks
				if ($row['item_number'] == NULL) {$row['item_number'] = $this->lang->line('common_edit');}
				
				// get item supplier data for item cost using default supplier
				$cost_price 			=	$this->get_item_supplier_cost($row['item_id']); //$this->config->item('default_supplier_id')
				
				$tabular_data[] = array		(
											$row['category'],
											anchor	(
													$edit_file.$row['item_id'].'/'.$origin.'/width:'.$width, 
													$row['item_number'],
													array('title'=>$this->lang->line('items_update'))
													),
											$row['reorder_policy'],
											$row['name'], 
											$cost_price,
											$attn, 
											$row['quantity'],
											$item_qty, 
											);
											
				// fix stock valuation records, -ne stock gives problems so handle separately
				if ($row['quantity'] >= 0)
				{	
					// qty on hand is +ve
					$value_remaining_qty	=	$item_qty - $row['quantity'];
					$value_trans_id			=	0;
					$this					->	Item->value_update($value_remaining_qty, $row['item_id'], $cost_price, $value_trans_id);
				}
				else
				{
					// qty on hand is -ne
					$this					->	Item->value_delete_item_id($row['item_id']);
					$data					=	array	(
														'value_item_id'		=>	$row['item_id'],
														'value_cost_price'	=>	$cost_price,
														'value_qty'			=>	$row['quantity'],
														'value_trans_id'	=>	0,
														'branch_code'		=>	$this->config->item('branch_code')
														);
					$this					->	Item->value_write($data);
				}
			
			}			
		}
		
		// get date and time
		$today_date						=	date('d/m/Y à H:i:s', time());
		
		// get the number format -->
		$pieces	=	array();
		$pieces	= 	explode("/", $this->config->item('numberformat'));
		
		$data = array	(
						"title" 		=> 	$this->lang->line('reports_value_record_integrity'),
						"subtitle" 		=> 	$today_date.' '.$this->lang->line('common_for').' '.$this->config->item('branch_code'),
						"headers"		=> 	$this->Stock_queries->getDataColumns($inputs),
						"data" 			=> 	$tabular_data,
						"summary_data" 	=> 	$this->Stock_queries->getSummaryData(array()),
						"export_excel" 	=> 	$export_excel
						);

		// file or display?
		$this							->	file_or_display($export_excel, $data, $month_end, $specific=0);
	}

	// ------------------------------------------------------------------------------------------------------
	// Create the CATEGORIES table
	// ------------------------------------------------------------------------------------------------------
	function	update_category_records ()
	{
		// check if records are to be created
		if ($this->config->item('createcategoryrecords') == 'Y')
		{
			// load model
			$this->load->model('Item');
			$this->load->model('Category');
			$this->load->model('Appconfig');
			
			// read all categories
			$category_data						=	array();
			$category_data						=	$this->Item->get_categories()->result_array();
			foreach ($category_data as $category)
			{				
				if (!$this->Category->exists_by_name($category['category']))
				{
					// create category record
					$data						=	array	();
					$data						=	array	(
															'category_name'		=>	$category['category'],
															'category_desc'		=>	'Auto created',
															'branch_code'		=>	$this->config->item('branch_code')
															);
					$this->db->insert('categories', $data);
				}
			}
			
			// set flag to N
			$batch_save_data				=	array	(
														'createcategoryrecords'	=>	'N'
														);
			$this							->	Appconfig->batch_save($batch_save_data);
			
			// set flash data and return to controller
			$success_or_failure				=	'S';
			$message						=	'Category records created.';
			$this							->	setflash($success_or_failure, $message);
		}
		else
		{
			// set flash data and return to controller
			$success_or_failure				=	'F';
			$message						=	'Nothing to do; check createcategoryrecords tag in configuration.';
			$this							->	setflash($success_or_failure, $message);
		}										
	}
	
	// ------------------------------------------------------------------------------------------------------
	// Update ITEMS table with category IDs
	// ------------------------------------------------------------------------------------------------------
	function	update_items_category_id()
	{
		// check category field exists - if not abandon
		if (!$this->db->field_exists('category', 'items'))
		{
			$success_or_failure			=	'F';
			$message					=	'ITEMS Category_IDs cannot be updated as the category column has been removed from the table. You don\'t need to run this.';
			$this						->	setflash($success_or_failure, $message);
		}

		// initialse
		$errors							=	0;
		
		// get all items
		$search_results					=	array();
		$search_results					=	$this->Item->get_all()->result_array();

		// read all items
		foreach ($search_results as $item)
		{
			// chg $item to object
			$_SESSION['transaction_info'] = (object)$item;

			// get category id given category from item file
			$_SESSION['transaction_info']->category_id	=	$this->Category->get_category_id($_SESSION['transaction_info']->category);

			// test category found
			if (!empty($_SESSION['transaction_info']->category_id))
			{
				// update item row in items table
				$this					->	Item->save();
			}
			else
			{
				$errors					=	$errors	+ 1;
			}
		}
					
		// set flash data and return to controller
		if ($errors != 0)
		{
			$success_or_failure			=	'F';
			$message					=	'ITEMS Category_IDs updated but with '.$errors.' errors. Some categories in the ITEMS table do not exist in the CATEGORIES table.';
		}
		else
		{
			$success_or_failure			=	'S';
			$message					=	'ITEMS Category_IDs updated.';	
		}
		
		$this							->	setflash($success_or_failure, $message);			
	}
	
	// ------------------------------------------------------------------------------------------------------
	// Update SALES_ITEMS table with category IDs
	// ------------------------------------------------------------------------------------------------------
	function	update_sales_items_category_id()
	{
		// check line_category field exists - if not abandon
		if (!$this->db->field_exists('line_category', 'sales_items'))
		{
			$success_or_failure			=	'F';
			$message					=	'SALES_ITEMS Category_IDs cannot be updated as the line_category column has been removed from the table. You don\'t need to run this.';
			$this						->	setflash($success_or_failure, $message);
		}
		
		// initialse		
		$errors							=	0;
		
		// get all sales items
		$search_results					=	array();
		$search_results					=	$this->Sale->get_all()->result_array();

		// read all items
		foreach ($search_results as $sales_item)
		{
			// get category id given category from item file
			$category_id				=	NULL;
			$category_id				=	$this->Category->get_category_id($sales_item['line_category']);
			
			// test category found
			if (!empty($category_id))
			{
				// update item row in items table
				$transaction_data		=	array	(
													'line_category_id'	=>	$category_id
													);
				$this					->	Sale->update_line($transaction_data, $sales_item['sale_id'], $sales_item['item_id'], $sales_item['line']);
			}
			else
			{
				$errors					=	$errors	+ 1;
			}
		}
					
		// set flash data and return to controller
		if ($errors != 0)
		{
			$success_or_failure			=	'F';
			$message					=	'SALES_ITEMS Category_IDs updated but with '.$errors.' errors. Some categories in the SALES_ITEMS table do not exist in the CATEGORIES table.';
		}
		else
		{
			$success_or_failure			=	'S';
			$message					=	'SALES_ITEMS Category_IDs updated.';	
		}
		
		$this							->	setflash($success_or_failure, $message);			
	}
	
	function	get_item_supplier_cost	($item_id)
	{
		// get item supplier data for item cost using default supplier
		$item_supplier_data 			=	$this->Item->item_supplier_get_cost($item_id); // $supplier_id
		
		// set cost price
		if ($item_supplier_data)
		{
			$cost_price					=	$item_supplier_data->supplier_cost_price;
		}
		else
		{
			$cost_price 				=	'ERR';
		}
		
		// send back cost price
		return							$cost_price;
	}					
}

?>

