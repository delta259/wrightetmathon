<?php
/*
initialise
*/
function initialise(&$CI, &$parms, $controller)
{
	// initalise
	$CI 							=	get_instance();
	$parms							=	array();
	$pieces							=	array();
	$parms['CI']					=	$CI;
	$_SESSION['line_number']		=	0;
	
	// load appropriate models and controllers
	$CI								->	load->model('reports/Specific_customer');
	$CI								->	load->model('reports/Specific_employee');
	$CI								->	load->model('reports/Specific_item');
	$CI								->	load->model('Common_routines');
	$CI								->	load->model('Sale');
	$CI								->	load->model('Currency');
	$CI								->	load->library('../controllers/reports');
	
	// set up the number format
	$pieces							=	array();
	$pieces 						=	explode("/", $CI->config->item('numberformat'));
	$parms['decimals']				=	$pieces[0];
	$parms['dec_point']				=	$pieces[1];
	$parms['thousands_sep']			=	$pieces[2];
	
	$parms['controller_name']		=	strtolower(get_class($CI));
	$parms['width']					=	$CI->Common_routines->set_form_width();

	// need to set up the function call in reports based on the controller name
	// cannot use the controler_name as it has an 's' at the end, so rtrim it.
	$parms['specific_function']		=	'Specific_'.rtrim($parms['controller_name'], "s");

	if($parms['controller_name']=='categories')
	{
		$parms['specific_function']='Specific_category';
	}

	// set up parms for retrieving report data
	$parms['start_date']			=	date('Y-m-d', 0);
	$parms['end_date'] 				=	date('Y-m-d');
	$parms['transaction_subtype'] 	=	'sales';
	$parms['export_excel']			= 	0;
	$parms['history']				= 	3;
}

// Create the table
function create_table($headers, $things, $controller, $parms, $type)
{		
	// initialise the table

    $table='<div style=" overflow-y:scroll; overflow-x:hidden;max-height: 545px;
    min-height: 340px;width: 100%;border:#f5f5f5 1px solid;">
<table style ="font-size: 15px;"class="table table-striped table-bordered table-hover tablesorter" id="sortable_table">';

	// fill the header division
	$table.='<thead><tr>';
	
	foreach($headers as $header)
	{
		$table.="<th>$header</th>";
	}
	
	// fill the body
	$table.='</tr></thead><tbody>';
	
	// select the type to call the correct method
	switch ($type)
	{
		case	'people':
				$table.=get_people_manage_table_data_rows($things, $controller, $parms);
				break;

		case	'items':
				$table.=get_items_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'category':
				$table.=get_categories_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'supplier':
				$table.=get_supplier_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'employee':
				$table.=get_employee_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'branch':
				$table.=get_branches_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'warehouse':
				$table.=get_warehouses_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'paymethod':
				$table.=get_paymethods_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'currency_definition':
				$table.=get_currency_definitions_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'target':
				$table.=get_targets_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'message':
				$table.=get_messages_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'pricelist':
				$table.=get_pricelists_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'currency':
				$table.=get_currencies_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'customer_profile':
				$table.=get_customer_profiles_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'country':
				$table.=get_countries_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'timezone':
				$table.=get_timezones_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'module':
				$table.=get_modules_manage_table_data_rows($things, $controller, $parms);
				break;
				
		case	'import':
				$table.=get_imports_manage_table_data_rows($things, $controller, $parms);
				break;
		
		case	'tracker':
				$table.=get_trackers_manage_table_data_rows($things, $controller, $parms);
				break;
	}
	
	// close body and close table
	$table.='</tbody></table></div>';
		
	return $table;
}

/*
Gets the html table to manage people.
*/
function get_people_manage_table($people, $controller, $create_headers=0)
{	
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns - number depends on whether to show comments or not - person_show_comments
	if ($CI->config->item('person_show_comments' == 'Y'))
	{
		$parms['colspan']												=	10;
	}
	else
	{
		$parms['colspan']												=	9;
	}
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		if ($CI->config->item('person_show_comments' == 'Y'))
		{
			switch($_SESSION['G']->login_employee_info->autorisation)
			{
				case 0:
				    $headers = array		(
					    $CI->lang->line('common_id'),
					    $CI->lang->line('common_last_name'),
					    $CI->lang->line('common_first_name'),
					    $CI->lang->line('common_sex'),
					    $CI->lang->line('common_dob'),
					    $CI->lang->line('pricelists_pricelist_name'),
					    $CI->lang->line('common_comments'),
					    $CI->lang->line('items_total_sold_value')
					);
				break;
		
				case 1:
				    $headers = array		(
					    $CI->lang->line('common_id'),
					    $CI->lang->line('common_last_name'),
					    $CI->lang->line('common_first_name'),
					    $CI->lang->line('common_sex'),
					    $CI->lang->line('common_dob'),
					    $CI->lang->line('common_email'),
					    $CI->lang->line('common_phone_number'),
					    $CI->lang->line('pricelists_pricelist_name'),
					    $CI->lang->line('common_comments'),
					    $CI->lang->line('items_total_sold_value')
				);
				break;
		
				default:
				break;
			}
			
		}
		else
		{
			switch($_SESSION['G']->login_employee_info->autorisation)
			{
				case 0:
				    $headers = array		(
				    	$CI->lang->line('common_id'),
				    	$CI->lang->line('common_last_name'),
				    	$CI->lang->line('common_first_name'),
				    	$CI->lang->line('common_sex'),
				    	$CI->lang->line('common_dob'),
				    	$CI->lang->line('pricelists_pricelist_name'),
				    	$CI->lang->line('items_total_sold_value')
				    	);
				break;
		
				case 1:
				    $headers = array		(
				    	$CI->lang->line('common_id'),
				    	$CI->lang->line('common_last_name'),
				    	$CI->lang->line('common_first_name'),
				    	$CI->lang->line('common_sex'),
				    	$CI->lang->line('common_dob'),
				    	$CI->lang->line('common_email'),
				    	$CI->lang->line('common_phone_number'),
				    	$CI->lang->line('pricelists_pricelist_name'),
				    	$CI->lang->line('items_total_sold_value')
				    	);
				break;
		
				default:
				break;
			}
			
		}
	
		// Now 'create' the table
		$type 					=	'people';
		
		$things					=	array();
		$things					=	$people;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_people_manage_table_data_rows($people, $controller, $parms);
	}
	
	return $table;
}

/*
Gets the html data rows for the people.
*/
function get_people_manage_table_data_rows($people, $controller, $parms=array())
{
	$table_data_rows='';
	
	foreach($people->result() as $person)
	{
		// test line count to determine colour of line
		$line_colour						=	common_set_line_colour();
		
		$table_data_rows.=get_person_data_row($person, $controller, $parms);
	}
	
	if($people->num_rows()==0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}
	
	return $table_data_rows;
}

function get_person_data_row($person, $controller, $parms)
{
	// create the anchor
	$anchor 				= 	'/reports/'
								.$parms['specific_function']
								.'/'
								.$parms['start_date']
								.'/'
								.$parms['end_date']
								.'/'
								.$person->person_id
								.'/'
								.$parms['transaction_subtype']
								.'/'
								.$parms['export_excel'];

	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.$person->person_id.'</td>';
	$table_data_row.='<td align="left" >'.anchor	(
													$parms['controller_name'].'/view/'.$person->person_id, 
													strtoupper($person->last_name)
													).'</td>';
	$table_data_row.='<td align="left" >'.ucfirst(strtolower($person->first_name)).'</td>';
	switch ($person->sex)
	{
		case	'F':
				$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('common_female').'</td>';
				break;
				
		case	'M':
				$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('common_male').'</td>';
				break;
				
		default:
				$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('common_undefined').'</td>';
				break;
	}		
	$table_data_row.='<td align="center" >'.$person->dob_day.'/'.$person->dob_month.'/'.$person->dob_year.'</td>';

	switch($_SESSION['G']->login_employee_info->autorisation)
	{
		case 0:
		break;

		case 1:
		$table_data_row.='<td align="left" >'.mailto($person->email,character_limiter($person->email,22)).'</td>';
		$table_data_row.='<td align="center" >'.$person->phone_number.'</td>';
		break;

		default:
		break;
	}
	
	// get price list name given pricelist_id or if invalid use default price list	
	if ($person->pricelist_id == NULL OR $person->pricelist_id == 0)
	{
		$pricelist_data	=	$parms['CI']->Pricelist->get_info($parms['CI']->config->item('pricelist_id'));
	}
	else
	{
		$pricelist_data	=	$parms['CI']->Pricelist->get_info($person->pricelist_id);
	}
	
	$table_data_row.='<td align="center" >'.$pricelist_data->pricelist_name.'</td>';
	
	// show comments only if config says so
	if ($parms['CI']->config->item('person_show_comments' == 'Y'))
	{
		$table_data_row.='<td align="center" >'.$person->comments.'</td>';
	}
	
	$table_data_row.='<td align="right" >'.anchor	(
													$anchor,
													number_format($person->sales_ht, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
													).'</td>';		
	$table_data_row.='</tr>';
	
	return $table_data_row;
}

/*
Gets the html table to manage suppliers.
*/
function get_supplier_manage_table($suppliers, $controller, $create_headers=0)
{	
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	7;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('suppliers_company_name'),
								$CI->lang->line('common_last_name'),
								$CI->lang->line('common_first_name'),
								$CI->lang->line('common_email'),
								$CI->lang->line('common_phone_number'),
								$CI->lang->line('common_comments'),
								);
	
		// Now 'create' the table
		$type 					=	'supplier';
		
		$things					=	array();
		$things					=	$suppliers;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_supplier_manage_table_data_rows($suppliers, $controller, $parms);
	}
	
	return $table;
}

/*
Gets the html data rows for the supplier.
*/
function get_supplier_manage_table_data_rows($suppliers, $controller, $parms=array())
{
	$table_data_rows='';
	
	foreach($suppliers->result() as $supplier)
	{
		// test line count to determine colour of line
		$line_colour						=	common_set_line_colour();
		
		$table_data_rows.=get_supplier_data_row($supplier, $controller, $parms);
	}
	
	if($suppliers->num_rows()==0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}
	
	return $table_data_rows;
}

function get_supplier_data_row($supplier, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="left" >'.$supplier->person_id.'</td>';
	$table_data_row.='<td align="left" >'.anchor	(
													$parms['controller_name'].'/view/'.$supplier->person_id, 
													strtoupper($supplier->company_name)
													).'</td>';
	$table_data_row.='<td align="left" >'.ucfirst(strtoupper($supplier->first_name)).'</td>';
	$table_data_row.='<td align="left" >'.ucfirst(strtolower($supplier->first_name)).'</td>';
	$table_data_row.='<td align="left" >'.mailto($supplier->email,character_limiter($supplier->email,22)).'</td>';
	$table_data_row.='<td align="center" >'.$supplier->phone_number.'</td>';
	$table_data_row.='<td align="left" >'.$supplier->comments.'</td>';
	$table_data_row.='</tr>';
	
	return $table_data_row;
}

/*
Gets the html table to manage items.
*/
function get_items_manage_table($items, $controller, $create_headers=0)
{
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	14;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array	(
							$CI->lang->line('items_category'),
							$CI->lang->line('items_item_number'),
							$CI->lang->line('items_name'),
							$CI->lang->line('items_volume'),
							$CI->lang->line('items_nicotine'),
							$CI->lang->line('items_cost_price').' '.$CI->lang->line('items_supplier_preferred'),
							$CI->lang->line('items_unit_price').' '.$CI->lang->line('pricelists_pricelist_default'),
							$CI->lang->line('items_quantity'),
							$CI->lang->line('common_det'),
							$CI->lang->line('items_dluo'),
							$CI->lang->line('items_kit'),
							$CI->lang->line('items_total_sold_quantity'),
							$CI->lang->line('items_total_sold_value')
							);
		
		// Now 'create' the table
		$type 	=	'items';
		$things	=	array();
		$things	=	$items;
		$table 	= 	create_table($headers, $things, $controller, $parms, $type);
		
	}
	else
	{
		$table = get_items_manage_table_data_rows($items, $controller, $parms);
	}

	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_items_manage_table_data_rows($items, $controller, $parms=array())
{	
	$table_data_rows='';
	
	foreach($items->result() as $item)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_item_data_row($item, $controller, $parms);
	}
	
	if($items->num_rows()==0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_item_data_row($item, $controller, $parms)
{
	// create the anchor
	$anchor 				= 	'/reports/'
								.$parms['specific_function']
								.'/'
								.$parms['start_date']
								.'/'
								.$parms['end_date']
								.'/'
								.$item->item_id
								.'/'
								.$parms['transaction_subtype']
								.'/'
								.$parms['export_excel']
								.'/'
								.$parms['history'];

	// set up the item_number to handle blanks
	if ($item->item_number == NULL) {$item->item_number = $parms['CI']->lang->line('common_edit');}
	$total_sold_quantity	= 0;
	// set up the table data row
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	
	$table_data_row.='<td align="center" >'.$item->category.'</td>';
	$table_data_row.='<td align="center" >'.anchor				(
																$parms['controller_name'].'/view/'.$item->item_id,
																$item->item_number
																).'</td>';
	$table_data_row.='<td align="left" >'.anchor				(
																$parms['controller_name'].'/remote_stock/'.$item->item_id,
																$item->name
																).'</td>';
	$table_data_row.='<td align="right" >'.$item->volume.'</td>';
	$table_data_row.='<td align="right" >'.$item->nicotine.'</td>';															
	
	// get cost price from default supplier
	$item_supplier_info											=	$parms['CI']->Item->item_supplier_get($item->item_id, $parms['CI']->config->item('default_supplier_id'));
	if ($item_supplier_info == NULL)
	{
		$cost_price												=	0;
	}
	else
	{
		$cost_price												=	$item_supplier_info->supplier_cost_price;
	}
	
	$table_data_row.='<td align="right" >'.number_format($cost_price, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep']).'</td>';
	
	// get unit price from default price list - no validity to consider as price is from default price list
	$item_price_info											=	$parms['CI']->Item->get_info_item_price($item->item_id, $parms['CI']->config->item('pricelist_id'));
	if 	(count($item_price_info) == 1)
	{
		foreach ($item_price_info as $item_price);
		{
			$unit_price											=	$item_price->unit_price;
		}
	}
	else
	{
		$unit_price												=	0;
	}
		
	$table_data_row.='<td align="right" >'.anchor				(
																$parms['controller_name'].'/label_form/'.$item->item_id,
																number_format($unit_price, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
																).'</td>';
	$table_data_row.='<td align="right" >'.anchor				(
																$parms['controller_name'].'/inventory/'.$item->item_id, 
																number_format($item->quantity, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
																).'</td>';
	$table_data_row.='<td align="center" >'.anchor				(
																$parms['controller_name'].'/count_details/'.$item->item_id.'/width:'.$parms['width'], 
																$parms['CI']->lang->line('common_det')
																).'</td>';
	// DLUO
	if ($item->dluo_indicator == 'Y')
		{
			$table_data_row.='<td align="center" >'.anchor		(
																$parms['controller_name'].'/dluo_form/'.$item->item_id, 
																$parms['CI']->lang->line('items_dluo')
																).'</td>';
		}
	else
		{
			$table_data_row.='<td align="left" >'.' '.'</td>';
		}
		
	// kit
	if ($item->DynamicKit == '1')
		{
			$table_data_row.='<td align="center" >'.anchor				(
																		$parms['controller_name'].'/kit/'.$item->item_id, 
																		$parms['CI']->lang->line('items_kit')
																		).'</td>';
		}
	else
		{
			$table_data_row.='<td align="left" >'.' '.'</td>';
		}	
	
	$table_data_row.='<td align="right" >'.anchor				(
																$anchor,
																number_format($item->sales_qty, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
																).'</td>';
	$table_data_row.='<td align="right" >'.anchor				(
																$anchor,
																number_format($item->sales_ht, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
																).'</td>';		
	$table_data_row.='</tr>';
	
	return $table_data_row;
}

/*
Gets the html table to manage giftcards.
*/
function get_giftcards_manage_table( $giftcards, $controller )
{
	$CI =& get_instance();
	
	$table='<table class="tablesorter" id="sortable_table">';
	
	$headers = array('<input type="checkbox" id="select_all" />', 
	$CI->lang->line('common_last_name'),
	$CI->lang->line('common_first_name'),
	$CI->lang->line('giftcards_giftcard_number'),
	$CI->lang->line('giftcards_card_value'),
	$CI->lang->line('giftcards_card_value_used'),
	$CI->lang->line('giftcards_card_value_balance'),
	$CI->lang->line('sales_id'),
	$CI->lang->line('giftcards_sale_date'),
	);
	
	$table.='<thead><tr>';
	foreach($headers as $header)
	{
		$table.="<th>$header</th>";
	}
	$table.='</tr></thead><tbody>';
	$table.=get_giftcards_manage_table_data_rows( $giftcards, $controller );
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the giftcard.
*/
function get_giftcards_manage_table_data_rows( $giftcards, $controller )
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($giftcards->result() as $giftcard)
	{
		// test line count to determine colour of line
		$line_colour						=	common_set_line_colour();
		
		$table_data_rows.=get_giftcard_data_row( $giftcard, $controller );
	}
	
	if($giftcards->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='11'><div class='warning_message' style='padding:7px;'>".$CI->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}
	
	return $table_data_rows;
}

/** GARRISON MODIFIED 4/25/2013 **/
function get_giftcard_data_row($giftcard,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	$width = $controller->get_form_width();

	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.="<td width='3%'><input type='checkbox' id='giftcard_$giftcard->giftcard_id' value='".$giftcard->giftcard_id."'/></td>";
	$table_data_row.='<td align="center">'.$giftcard->last_name.'</td>';
	$table_data_row.='<td align="center">'.$giftcard->first_name.'</td>';
	$table_data_row.='<td align="center">'.$giftcard->giftcard_number.'</td>';
	$table_data_row.='<td align="right">'.to_currency($giftcard->value).'</td>';
	$table_data_row.='<td align="right">'.to_currency($giftcard->value_used).'</td>';
	$table_data_row.='<td align="right">'.to_currency($giftcard->value - $giftcard->value_used).'</td>';
	$table_data_row.='<td align="center">'.anchor	(
													'sales/receipt/'.$giftcard->sale_id, 
													$giftcard->sale_id
													).'</td>';
	$table_data_row.='<td align="center">'.$giftcard->sale_date.'</td>';
	$table_data_row.='</tr>';
	return $table_data_row;
}
/** END GARRISON MODIFIED **/

/*
Gets the html table to manage item kits.
*/
function get_item_kits_manage_table( $item_kits, $controller )
{
	$CI =& get_instance();

	$table='<table class="data-table tablesorter" id="sortable_table">';
	$table.='<colgroup>';
	$table.='<col style="width: 40px;">';  // Toggle
	$table.='<col style="width: 40px;">';  // Barcode icon
	$table.='<col style="width: 150px;">'; // Name
	$table.='<col>';                        // Description (auto)
	$table.='<col style="width: 100px;">';  // Cost price
	$table.='<col style="width: 100px;">';  // Unit price
	$table.='<col style="width: 140px;">';  // Barcode
	$table.='</colgroup>';

	$table.='<thead><tr>';
	$table.='<th class="col-action"></th>';
	$table.='<th class="col-icon" title="Code barre">EAN</th>';
	$table.='<th>'.$CI->lang->line('item_kits_name').'</th>';
	$table.='<th>'.$CI->lang->line('item_kits_description').'</th>';
	$table.='<th class="col-price">'.$CI->lang->line('item_kits_cost_price').'</th>';
	$table.='<th class="col-price">'.$CI->lang->line('item_kits_unit_price_with_tax').'</th>';
	$table.='<th>'.$CI->lang->line('item_kits_code_bar').'</th>';
	$table.='</tr></thead><tbody>';
	$table.=get_item_kits_manage_table_data_rows( $item_kits, $controller );
	$table.='</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the item kits.
*/
function get_item_kits_manage_table_data_rows( $item_kits, $controller )
{
	$CI =& get_instance();
	$table_data_rows='';
	
	foreach($item_kits->result() as $item_kit)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_item_kit_data_row( $item_kit, $controller );
	}
	
	if($item_kits->num_rows()==0)
	{
		$table_data_rows.="<tr><td colspan='11'><div class='warning_message' style='padding:7px;'>".$CI->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}
	
	return $table_data_rows;
}

function get_item_kit_data_row($item_kit,$controller)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	$item_kit_id = $item_kit->item_kit_id;
	$tout=(string)$item_kit_id . ':' . '0' . ":" . 'item_kits';

	$row_class = ($item_kit->deleted == 1) ? ' class="row-inactive"' : '';
	$table_data_row = '<tr'.$row_class.'>';

	// Toggle Active/Inactive
	$table_data_row.='<td class="cell-action">';
	if($item_kit->deleted == 0)
	{
		$table_data_row.='<a href="'.site_url("item_kits/desactive/$tout").'" class="btn-icon btn-toggle-active" title="Désactiver">';
		$table_data_row.='<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
		$table_data_row.='</a>';
	}
	else
	{
		$table_data_row.='<a href="'.site_url("item_kits/desactive/$tout").'" class="btn-icon btn-toggle-inactive" title="Activer">';
		$table_data_row.='<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
		$table_data_row.='</a>';
	}
	$table_data_row.='</td>';

	// Barcode Icon
	$table_data_row.='<td class="cell-action">';
	if(isset($item_kit->barcode) && (strlen($item_kit->barcode) > 5))
	{
		$table_data_row.='<a href="'.site_url("item_kits/view/$item_kit_id").'" class="btn-icon btn-barcode-ok" title="Code barre existant">';
		$table_data_row.='<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 5v14M6 5v14M9 5v14M12 5v14M15 5v14M18 5v14M21 5v14"/></svg>';
		$table_data_row.='</a>';
	}
	else
	{
		$table_data_row.='<a href="'.site_url("item_kits/view/$item_kit_id").'" class="btn-icon btn-barcode-missing" title="Code barre manquant">';
		$table_data_row.='<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 5v14M6 5v14M9 5v14M12 5v14M15 5v14M18 5v14M21 5v14"/></svg>';
		$table_data_row.='</a>';
	}
	$table_data_row.='</td>';

	// Name
	$table_data_row.='<td class="cell-name">'.anchor($controller_name."/view/$item_kit_id", '<span class="badge-ref">'.htmlspecialchars($item_kit->name).'</span>', 'title="Éditer le kit"').'</td>';

	// Description
	$table_data_row.='<td class="cell-desc" title="'.htmlspecialchars($item_kit->description).'">'.htmlspecialchars($item_kit->description).'</td>';

	// Cost Price
	$cost = isset($item_kit->cost_kit) ? number_format((float)$item_kit->cost_kit, 2, ',', ' ') : '0,00';
	$table_data_row.='<td class="cell-price">'.$cost.' &euro;</td>';

	// Unit Price
	$price = isset($item_kit->unit_price_with_tax) ? number_format((float)$item_kit->unit_price_with_tax, 2, ',', ' ') : '0,00';
	$table_data_row.='<td class="cell-price">'.$price.' &euro;</td>';

	// Barcode
	$table_data_row.='<td class="cell-barcode">'.htmlspecialchars($item_kit->barcode ?? '').'</td>';

	$table_data_row.='</tr>';
	return $table_data_row;
}

/*
Gets the html table to manage people.
*/
function get_categories_manage_table($categories, $controller, $create_headers=0)
{		
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	8;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('common_last_name'),
								$CI->lang->line('categories_category_desc'),
								$CI->lang->line('categories_update_sales_price'),
								$CI->lang->line('categories_defect_indicator'),
								$CI->lang->line('items_reorder_pack_size'),
								$CI->lang->line('categories_min_order_qty'),
								$CI->lang->line('items_total_sold_quantity'),
								$CI->lang->line('items_total_sold_value')
								);
	
		// Now 'create' the table
		$type 					=	'category';
		
		$things					=	array();
		$things					=	$categories;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_categories_manage_table_data_rows($categories, $controller, $parms);
	}

	return $table;
}

function get_categories_manage_table_data_rows($categories, $controller, $parms=array())
{
	$table_data_rows='';

	foreach($categories as $category)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_category_data_row($category, $controller, $parms);
	}
	
	$row_count = is_array($categories) ? count($categories) : (is_object($categories) && method_exists($categories, 'num_rows') ? $categories->num_rows() : 0);
	if($row_count == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_category_data_row($category, $controller, $parms)
{
	// create the anchor
	$anchor 				= 	'/reports/'
								.$parms['specific_function']
								.'/'
								.$parms['start_date']
								.'/'
								.$parms['end_date']
								.'/'
								.$category['category_id']
								.'/'
								.$parms['transaction_subtype']
								.'/'
								.$parms['export_excel'];

	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.$category['category_id'].'</td>';
	$table_data_row.='<td align="left" >'.anchor	(
													$parms['controller_name'].'/view/'.$category['category_id'], 
													$category['category_name']
													).'</td>';
	$table_data_row.='<td align="left" >'.$category['category_desc'].'</td>';
	$table_data_row.='<td align="center" >'.$category['category_update_sales_price'].'</td>';
	$table_data_row.='<td align="center" >'.$category['category_defect_indicator'].'</td>';
	$table_data_row.='<td align="center" >'.$category['category_pack_size'].'</td>';
	$table_data_row.='<td align="left" >'.$category['category_min_order_qty'].'</td>';
	$table_data_row.='<td align="right" >'.anchor	(
													$anchor,
													number_format($category['category_sales_qty'], $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
													).'</td>';
	$table_data_row.='<td align="right" >'.anchor	(
													$anchor,
													number_format($category['category_sales_value'], $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
													).'</td>';		
	$table_data_row.='</tr>';

	return $table_data_row;
}

// common set table row colour
function 	common_set_line_colour			()
{
	// initialize line count if not set
	if (!isset($_SESSION['line_count'])) {
		$_SESSION['line_count'] = 0;
	}
	// initialize line colour if not set
	if (!isset($_SESSION['line_colour'])) {
		$_SESSION['line_colour'] = 'white';
	}
	// add 1 to line count
	$_SESSION['line_count']				=	$_SESSION['line_count']	+	1;

	// test line count for colour
	if ($_SESSION['line_count'] & 1)
	{
		//odd, set colour of line
		$_SESSION['line_colour']			=	'#EBF4F8';
	}
	else
	{
		//even, set colour of line
		$_SESSION['line_colour']			=	'white';
	}

	return;
}

function get_employee_manage_table($people, $controller, $create_headers=0)
{	
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	7;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('employees_username'),
								$CI->lang->line('common_last_name'),
								$CI->lang->line('common_first_name'),
								$CI->lang->line('common_email'),
								$CI->lang->line('common_phone_number'),
								$CI->lang->line('common_comments'),
								$CI->lang->line('items_total_sold_value')
								);
	
		// Now 'create' the table
		$type 					=	'employee';
		
		$things					=	array();
		$things					=	$people;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_employee_manage_table_data_rows($people, $controller, $parms);
	}
	
	return $table;
}

/*
Gets the html data rows for the people.
*/
function get_employee_manage_table_data_rows($people, $controller, $parms=array())
{
	$table_data_rows='';
	
	foreach($people->result() as $person)
	{
		// test line count to determine colour of line
		$line_colour						=	common_set_line_colour();
		
		$table_data_rows.=get_employee_data_row($person, $controller, $parms);
	}
	
	if($people->num_rows()==0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}
	
	return $table_data_rows;
}

function get_employee_data_row($person, $controller, $parms)
{
	// create the anchor
	$anchor 				= 	'/reports/'
								.$parms['specific_function']
								.'/'
								.$parms['start_date']
								.'/'
								.$parms['end_date']
								.'/'
								.$person->person_id
								.'/'
								.$parms['transaction_subtype']
								.'/'
								.$parms['export_excel'];

	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.$person->person_id.'</td>';
	$table_data_row.='<td align="left" >'.$person->username.'</td>';
	$table_data_row.='<td align="left" >'.anchor	(
													$parms['controller_name'].'/view/'.$person->person_id, 
													strtoupper($person->last_name)
													).'</td>';
	$table_data_row.='<td align="left" >'.ucfirst(strtolower($person->first_name)).'</td>';
	$table_data_row.='<td align="left" >'.mailto($person->email,character_limiter($person->email,22)).'</td>';
	$table_data_row.='<td align="center" >'.$person->phone_number.'</td>';
	$table_data_row.='<td align="left" >'.$person->comments.'</td>';
	$table_data_row.='<td align="right" >'.anchor	(
													$anchor,
													number_format($person->sales_ht, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])
													).'</td>';		
	$table_data_row.='</tr>';
	
	return $table_data_row;
}

/*
Gets the html table to manage branches.
*/
function get_branches_manage_table($branches, $controller, $create_headers=0)
{		
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	7;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('branches_branch_code'),
								$CI->lang->line('branches_branch_description'),
								$CI->lang->line('branches_branch_ip'),
								$CI->lang->line('branches_branch_user'),
								$CI->lang->line('branches_branch_database'),
								$CI->lang->line('branches_branch_allows_check'),
								$CI->lang->line('branches_branch_type'),
								);
	
		// Now 'create' the table
		$type 					=	'branch';
		
		$things					=	array();
		$things					=	$branches;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_branches_manage_table_data_rows($branches, $controller, $parms);
	}

	return $table;
}

function get_branches_manage_table_data_rows($branches, $controller, $parms=array())
{
	$table_data_rows='';

	foreach($branches->result() as $branch)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_branch_data_row($branch, $controller, $parms);
	}
	
	$row_count = is_array($branches) ? count($branches) : (is_object($branches) && method_exists($branches, 'num_rows') ? $branches->num_rows() : 0);
	if($row_count == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_branch_data_row($branch, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$branch->branch_code, 
													$branch->branch_code
													).'</td>';
	$table_data_row.='<td align="left" >'.$branch->branch_description.'</td>';
	$table_data_row.='<td align="center" >'.$branch->branch_ip.'</td>';
	$table_data_row.='<td align="center" >'.$branch->branch_user.'</td>';
	$table_data_row.='<td align="left" >'.$branch->branch_database.'</td>';
	$table_data_row.='<td align="center" >'.$branch->branch_allows_check.'</td>';
	switch ($branch->branch_type)
	{
		case 'I':	
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('branches_branch_type_I').'</td>';
		break;
		
		case 'F':
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('branches_branch_type_F').'</td>';
		break;	
	}
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage warehouses.
*/
function get_warehouses_manage_table($warehouses, $controller, $create_headers=0)
{		
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	10;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('warehouses_warehouse_code'),
								$CI->lang->line('warehouses_warehouse_description'),
								$CI->lang->line('warehouses_warehouse_row_start'),
								$CI->lang->line('warehouses_warehouse_row_end'),
								$CI->lang->line('warehouses_warehouse_section_start'),
								$CI->lang->line('warehouses_warehouse_section_end'),
								$CI->lang->line('warehouses_warehouse_shelf_start'),
								$CI->lang->line('warehouses_warehouse_shelf_end'),
								$CI->lang->line('warehouses_warehouse_bin_start'),
								$CI->lang->line('warehouses_warehouse_bin_end'),
								);
	
		// Now 'create' the table
		$type 					=	'warehouse';
		
		$things					=	array();
		$things					=	$warehouses;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_warehouses_manage_table_data_rows($warehouses, $controller, $parms);
	}

	return $table;
}

function get_warehouses_manage_table_data_rows($warehouses, $controller, $parms=array())
{
	// initialise
	$table_data_rows='';
	
	// load each line to the table
	foreach($warehouses->result() as $warehouse)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_warehouse_data_row($warehouse, $controller, $parms);
	}
	
	$row_count = is_array($warehouses) ? count($warehouses) : (is_object($warehouses) && method_exists($warehouses, 'num_rows') ? $warehouses->num_rows() : 0);
	if($row_count == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_warehouse_data_row($warehouse, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$warehouse->warehouse_code, 
													$warehouse->warehouse_code
													).'</td>';
	$table_data_row.='<td align="center" >'.$warehouse->warehouse_description.'</td>';
	$table_data_row.='<td align="center" >'.$warehouse->warehouse_row_start.'</td>';
	$table_data_row.='<td align="center" >'.$warehouse->warehouse_row_end.'</td>';
	$table_data_row.='<td align="center" >'.$warehouse->warehouse_section_start.'</td>';
	$table_data_row.='<td align="center" >'.$warehouse->warehouse_section_end.'</td>';
	$table_data_row.='<td align="center" >'.$warehouse->warehouse_shelf_start.'</td>';
	$table_data_row.='<td align="center" >'.$warehouse->warehouse_shelf_end.'</td>';
	$table_data_row.='<td align="center" >'.$warehouse->warehouse_bin_start.'</td>';
	$table_data_row.='<td align="center" >'.$warehouse->warehouse_bin_end.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage payment methods.
*/
function get_paymethods_manage_table($paymethods, $controller, $create_headers=0)
{		
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	5;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('paymethods_paymethod_code'),
								$CI->lang->line('paymethods_paymethod_description'),
								$CI->lang->line('paymethods_paymethod_include'),
								$CI->lang->line('paymethods_paymethod_display_order'),
								);
	
		// Now 'create' the table
		$type 					=	'paymethod';
		
		$things					=	array();
		$things					=	$paymethods;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_paymethods_manage_table_data_rows($paymethods, $controller, $parms);
	}

	return $table;
}

function get_paymethods_manage_table_data_rows($paymethods, $controller, $parms=array())
{
	// initialise
	$table_data_rows='';
	
	// load each line to the table
	foreach($paymethods->result() as $paymethod)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_paymethod_data_row($paymethod, $controller, $parms);
	}
	
	$row_count = is_array($paymethods) ? count($paymethods) : (is_object($paymethods) && method_exists($paymethods, 'num_rows') ? $paymethods->num_rows() : 0);
	if($row_count == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_paymethod_data_row($paymethod, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$paymethod->payment_method_id, 
													$paymethod->payment_method_id
													).'</td>';
	$table_data_row.='<td align="center" >'.$paymethod->payment_method_code.'</td>';												
	$table_data_row.='<td align="center" >'.$paymethod->payment_method_description.'</td>';
	$table_data_row.='<td align="center" >'.$paymethod->payment_method_include.'</td>';
	$table_data_row.='<td align="center" >'.$paymethod->payment_method_display_order.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage currencies.
*/
function get_currency_definitions_manage_table($currency_definitions, $controller, $create_headers=0)
{		
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	5;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('currency_definitions_denomination'),
								$CI->lang->line('currency_definitions_display_name'),
								$CI->lang->line('currency_definitions_display_order'),
								$CI->lang->line('currency_definitions_type'),
								$CI->lang->line('currency_definitions_cashtill'),
								$CI->lang->line('currency_definitions_multiplier'),
								);
	
		// Now 'create' the table
		$type 					=	'currency_definition';
		
		$things					=	array();
		$things					=	$currency_definitions;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_currency_definitions_manage_table_data_rows($currency_definitions, $controller, $parms);
	}

	return $table;
}

function get_currency_definitions_manage_table_data_rows($currency_definitions, $controller, $parms=array())
{
	// initialise
	$table_data_rows='';
	
	// load each line to the table
	foreach($currency_definitions->result() as $currency_definition)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_currency_definition_data_row($currency_definition, $controller, $parms);
	}
	
	$row_count = is_array($currency_definitions) ? count($currency_definitions) : (is_object($currency_definitions) && method_exists($currency_definitions, 'num_rows') ? $currency_definitions->num_rows() : 0);
	if($row_count == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_currency_definition_data_row($currency_definition, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$currency_definition->denomination, 
													$currency_definition->denomination
													).'</td>';
	$table_data_row.='<td align="center" >'.$currency_definition->display_name.'</td>';
	$table_data_row.='<td align="center" >'.$currency_definition->display_order.'</td>';
	switch ($currency_definition->type)
	{
		case 'N':	
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('currency_definitions_type_N').'</td>';
		break;
		
		case 'C':
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('currency_definitions_type_C').'</td>';
		break;	
	}
	$table_data_row.='<td align="center" >'.$currency_definition->cashtill.'</td>';
	$table_data_row.='<td align="center" >'.$currency_definition->multiplier.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage sales targets.
*/
function get_targets_manage_table($targets, $controller, $create_headers=0)
{			
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	5;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('common_year'),
								$CI->lang->line('common_month'),
								$CI->lang->line('config_averagenumberopendays'),
								$CI->lang->line('config_monthlysalestarget'),
								);
	
		// Now 'create' the table
		$type 					=	'target';
		
		$things					=	array();
		$things					=	$targets;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_targets_manage_table_data_rows($targets, $controller, $parms);
	}

	return $table;
}

function get_targets_manage_table_data_rows($targets, $controller, $parms=array())
{
	$table_data_rows='';

	foreach($targets->result() as $target)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_target_data_row($target, $controller, $parms);
	}
	
	$row_count = is_array($targets) ? count($targets) : (is_object($targets) && method_exists($targets, 'num_rows') ? $targets->num_rows() : 0);
	if($row_count == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_target_data_row($target, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$target->target_id, 
													$target->target_id
													).'</td>';
	$table_data_row.='<td align="center" >'.$target->target_year.'</td>';
	$table_data_row.='<td align="center" >'.$target->target_month.'</td>';
	$table_data_row.='<td align="center" >'.$target->target_shop_open_days.'</td>';
	$table_data_row.='<td align="center" >'.$target->target_shop_turnover.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage messages.
*/
function get_messages_manage_table($messages, $controller, $create_headers=0)
{			
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	7;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('messages_message_class'),
								$CI->lang->line('messages_message_text'),
								$CI->lang->line('messages_message_info_1'),
								$CI->lang->line('messages_message_info_2'),
								$CI->lang->line('messages_message_var_1'),
								$CI->lang->line('messages_message_var_2')
								);
	
		// Now 'create' the table
		$type 					=	'message';
		
		$things					=	array();
		$things					=	$messages;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_messages_manage_table_data_rows($messages, $controller, $parms);
	}

	return $table;
}

function get_messages_manage_table_data_rows($messages, $controller, $parms=array())
{
	$table_data_rows='';

	foreach($messages->result() as $message)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_message_data_row($message, $controller, $parms);
	}

	if ($messages->num_rows() == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_message_data_row($message, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$message->message_code, 
													$message->message_code
													).'</td>';
	$table_data_row.='<td align="center" >'.$message->message_class.'</td>';
	$table_data_row.='<td align="center" >'.$message->message_text.'</td>';
	$table_data_row.='<td align="center" >'.$message->message_info_1.'</td>';
	$table_data_row.='<td align="center" >'.$message->message_info_2.'</td>';
	$table_data_row.='<td align="center" >'.$message->message_var_1.'</td>';
	$table_data_row.='<td align="center" >'.$message->message_var_2.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage pricelists
*/
function get_pricelists_manage_table($pricelists, $controller, $create_headers=0)
{			
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	5;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('pricelists_pricelist_name'),
								$CI->lang->line('pricelists_pricelist_description'),
								$CI->lang->line('pricelists_pricelist_currency'),
								$CI->lang->line('pricelists_pricelist_default'),
								);
	
		// Now 'create' the table
		$type 					=	'pricelist';
		
		$things					=	array();
		$things					=	$pricelists;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_pricelists_manage_table_data_rows($pricelists, $controller, $parms);
	}

	return $table;
}

function get_pricelists_manage_table_data_rows($pricelists, $controller, $parms=array())
{	
	$table_data_rows='';

	foreach($pricelists->result() as $pricelist)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_pricelist_data_row($pricelist, $controller, $parms);
	}

	if ($pricelists->num_rows() == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

	function get_pricelist_data_row($pricelist, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$pricelist->pricelist_id, 
													$pricelist->pricelist_id
													).'</td>';
	$table_data_row.='<td align="center" >'.$pricelist->pricelist_name.'</td>';
	$table_data_row.='<td align="center" >'.$pricelist->pricelist_description.'</td>';
	$currency_info						=	new stdClass();
	$currency_info						=	$parms['CI']->Currency->get_info($pricelist->pricelist_currency);
	$table_data_row.='<td align="center" >'.$currency_info->currency_name.'</td>';
	$table_data_row.='<td align="center" >'.$pricelist->pricelist_default.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage currencies
*/
function get_currencies_manage_table($currencies, $controller, $create_headers=0)
{			
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	7;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('currencies_currency_name'),
								$CI->lang->line('currencies_currency_description'),
								$CI->lang->line('currencies_currency_code'),
								$CI->lang->line('currencies_currency_sign'),
								$CI->lang->line('currencies_currency_side'),
								$CI->lang->line('currencies_currency_display_order'),
								);
	
		// Now 'create' the table
		$type 					=	'currency';
		
		$things					=	array();
		$things					=	$currencies;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_currencies_manage_table_data_rows($currencies, $controller, $parms);
	}

	return $table;
}

function get_currencies_manage_table_data_rows($currencies, $controller, $parms=array())
{	
	$table_data_rows='';

	foreach($currencies->result() as $currency)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_currency_data_row($currency, $controller, $parms);
	}

	if ($currencies->num_rows() == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_currency_data_row($currency, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$currency->currency_id, 
													$currency->currency_id
													).'</td>';
	$table_data_row.='<td align="center" >'.$currency->currency_name.'</td>';
	$table_data_row.='<td align="center" >'.$currency->currency_description.'</td>';
	$table_data_row.='<td align="center" >'.$currency->currency_code.'</td>';
	$table_data_row.='<td align="center" >'.$currency->currency_sign.'</td>';
	switch ($currency->currency_side)
	{
		case 'R':	
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('common_right').'</td>';
		break;
		
		case 'L':
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('common_left').'</td>';
		break;	
	}
	$table_data_row.='<td align="center" >'.$currency->currency_display_order.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage customer profiles
*/
function get_customer_profiles_manage_table($profiles, $controller, $create_headers=0)
{				
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	5;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('customer_profiles_profile_name'),
								$CI->lang->line('customer_profiles_profile_description'),
								$CI->lang->line('customer_profiles_profile_discount'),
								$CI->lang->line('customer_profiles_profile_fidelity'),
								);
	
		// Now 'create' the table
		$type 					=	'customer_profile';
		
		$things					=	array();
		$things					=	$profiles;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_customer_profiles_manage_table_data_rows($profiles, $controller, $parms);
	}

	return $table;
}

function get_customer_profiles_manage_table_data_rows($profiles, $controller, $parms=array())
{	
	$table_data_rows='';

	foreach($profiles->result() as $profile)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_customer_profile_data_row($profile, $controller, $parms);
	}

	if ($profiles->num_rows() == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

	function get_customer_profile_data_row($profile, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$profile->profile_id, 
													$profile->profile_id
													).'</td>';
	$table_data_row.='<td align="center" >'.$profile->profile_name.'</td>';
	$table_data_row.='<td align="center" >'.$profile->profile_description.'</td>';
	$table_data_row.='<td align="center" >'.$profile->profile_discount.'</td>';
	switch ($profile->profile_fidelity)
	{
		case 'Y':	
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('common_yes').'</td>';
		break;
		
		case 'N':
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('common_no').'</td>';
		break;	
	}
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage countries
*/
function get_countries_manage_table($countries, $controller, $create_headers=0)
{			
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	4;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('countries_country_name'),
								$CI->lang->line('countries_country_description'),
								$CI->lang->line('countries_country_display_order'),
								);
	
		// Now 'create' the table
		$type 					=	'country';
		
		$things					=	array();
		$things					=	$countries;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_countries_manage_table_data_rows($countries, $controller, $parms);
	}

	return $table;
}

function get_countries_manage_table_data_rows($countries, $controller, $parms=array())
{	
	$table_data_rows='';

	foreach($countries->result() as $country)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_country_data_row($country, $controller, $parms);
	}

	if ($countries->num_rows() == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_country_data_row($country, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$country->country_id, 
													$country->country_id
													).'</td>';
	$table_data_row.='<td align="center" >'.$country->country_name.'</td>';
	$table_data_row.='<td align="center" >'.$country->country_description.'</td>';
	$table_data_row.='<td align="center" >'.$country->country_display_order.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage timezones
*/
function get_timezones_manage_table($timezones, $controller, $create_headers=0)
{			
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	6;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('timezones_timezone_name'),
								$CI->lang->line('timezones_timezone_description'),
								$CI->lang->line('timezones_timezone_continent'),
								$CI->lang->line('timezones_timezone_city'),
								$CI->lang->line('timezones_timezone_GMT_offset'),
								);
	
		// Now 'create' the table
		$type 					=	'timezone';
		
		$things					=	array();
		$things					=	$timezones;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_timezones_manage_table_data_rows($timezones, $controller, $parms);
	}

	return $table;
}

function get_timezones_manage_table_data_rows($timezones, $controller, $parms=array())
{	
	$table_data_rows='';

	foreach($timezones->result() as $timezone)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_timezone_data_row($timezone, $controller, $parms);
	}

	if ($timezones->num_rows() == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_timezone_data_row($timezone, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$timezone->timezone_id, 
													$timezone->timezone_id
													).'</td>';
	$table_data_row.='<td align="center" >'.$timezone->timezone_name.'</td>';
	$table_data_row.='<td align="center" >'.$timezone->timezone_description.'</td>';
	$table_data_row.='<td align="center" >'.$timezone->timezone_continent.'</td>';
	$table_data_row.='<td align="center" >'.$timezone->timezone_city.'</td>';
	$table_data_row.='<td align="center" >'.$timezone->timezone_offset.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage modules
*/
function get_modules_manage_table($modules, $controller, $create_headers=0)
{			
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	2;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('modules_module_name'),
								$CI->lang->line('modules_module_name_lang_key'),
								$CI->lang->line('modules_module_desc_lang_key'),
								$CI->lang->line('modules_module_sort'),
								);
	
		// Now 'create' the table
		$type 					=	'module';
		
		$things					=	array();
		$things					=	$modules;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_modules_manage_table_data_rows($modules, $controller, $parms);
	}

	return $table;
}

function get_modules_manage_table_data_rows($modules, $controller, $parms=array())
{	
	$table_data_rows='';

	foreach($modules->result() as $module)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_module_data_row($module, $controller, $parms);
	}

	if ($modules->num_rows() == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_module_data_row($module, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$module->module_id, 
													$module->module_id
													).'</td>';
	$table_data_row.='<td align="left" >'.$module->module_name.'</td>';
	$table_data_row.='<td align="left" >'.$module->name_lang_key.'</td>';
	$table_data_row.='<td align="left" >'.$module->desc_lang_key.'</td>';
	$table_data_row.='<td align="center" >'.$module->sort.'</td>';

	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage import columns
*/
function get_imports_manage_table($columns, $controller, $create_headers=0)
{			
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	6;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('imports_column_letter'),
								$CI->lang->line('imports_column_label'),
								$CI->lang->line('imports_column_number'),
								$CI->lang->line('imports_column_data_type'),
								$CI->lang->line('imports_column_database_field_name'),
								);
	
		// Now 'create' the table
		$type 					=	'import';
		
		$things					=	array();
		$things					=	$columns;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_imports_manage_table_data_rows($columns, $controller, $parms);
	}

	return $table;
}

function get_imports_manage_table_data_rows($columns, $controller, $parms=array())
{	
	$table_data_rows='';

	foreach($columns->result() as $column)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_import_data_row($column, $controller, $parms);
	}

	if ($columns->num_rows() == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_message' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_import_data_row($column, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$column->column_id, 
													$column->column_id
													).'</td>';
	$table_data_row.='<td align="center" >'.$column->column_letter.'</td>';
	$table_data_row.='<td align="center" >'.$column->column_label.'</td>';
	$table_data_row.='<td align="center" >'.$column->column_number.'</td>';
	$table_data_row.='<td align="center" >'.$_SESSION['C']->data_type_pick_list[$column->column_data_type].'</td>';
	$table_data_row.='<td align="center" >'.$column->column_database_field_name.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage trackers
*/
function get_trackers_manage_table($trackers, $controller, $create_headers=0)
{			
	// load appropriate models and controllers
	initialise($CI, $parms, $controller);

	// set up the number of columns
	$parms['colspan']			=	6;	
	
	// set up the headers if required
	if ($create_headers == 1)
	{
		$headers = array		(
								$CI->lang->line('common_id'),
								$CI->lang->line('trackers_tracker_subject'),
								$CI->lang->line('trackers_tracker_status'),
								$CI->lang->line('trackers_tracker_commit_summary'),
								$CI->lang->line('common_add_date'),
								$CI->lang->line('common_change_date')
								);
	
		// Now 'create' the table
		$type 					=	'tracker';
		
		$things					=	array();
		$things					=	$trackers;
		$table 					= 	create_table($headers, $things, $controller, $parms, $type);
	}
	else
	{
		$table 					=	get_trackers_manage_table_data_rows($trackers, $controller, $parms);
	}

	return $table;
}

function get_trackers_manage_table_data_rows($trackers, $controller, $parms=array())
{	
	$table_data_rows='';

	foreach($trackers->result() as $tracker)
	{
		// test line count to determine colour of line
		$line_colour			=	common_set_line_colour();
		
		$table_data_rows.=get_tracker_data_row($tracker, $controller, $parms);
	}

	if ($trackers->num_rows() == 0)
	{
		$table_data_rows.="	<tr><td colspan='".$parms['colspan']."'><div class='warning_messag' style='padding:7px;'>".$parms['CI']->lang->line('common_no_persons_to_display')."</div></tr></tr>";
	}

	return $table_data_rows;
}

function get_tracker_data_row($tracker, $controller, $parms)
{
	// set up table data
	$table_data_row = '';
	$table_data_row.='<tr style="background-color:'.$_SESSION['line_colour'].'">';
	$table_data_row.='<td align="center" >'.anchor	(
													$parms['controller_name'].'/view/'.$tracker->tracker_id, 
													$tracker->tracker_id
													).'</td>';
	$table_data_row.='<td align="center" >'.$tracker->tracker_subject.'</td>';
	switch ($tracker->tracker_status)
	{
		case '1':	
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('trackers_reported').'</td>';
		break;
		
		case '2':
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('trackers_open').'</td>';
		break;
		
		case '3':
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('trackers_assigned').'</td>';
		break;	
		
		case '4':
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('trackers_fixed').'</td>';
		break;	
		
		case '5':
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('trackers_committed').'</td>';
		break;	
		
		case '6':
			$table_data_row.='<td align="center" >'.$parms['CI']->lang->line('trackers_production').'</td>';
		break;		
	}
	$table_data_row.='<td align="center" >'.$tracker->tracker_commit_summary.'</td>';
	$table_data_row.='<td align="center" >'.$tracker->tracker_added.'</td>';
	$table_data_row.='<td align="center" >'.$tracker->tracker_changed.'</td>';
	
	$table_data_row.='</tr>';

	return $table_data_row;
}

?>
