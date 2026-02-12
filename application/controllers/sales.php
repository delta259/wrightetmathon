<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class Sales extends CI_Controller
{
	function index()
	{

		
		// set module id
		$_SESSION['module_id']											=	"22";

		// initialise
		$this->load->library('sale_lib');
		unset($_SESSION['transaction_info']);

		// manage session
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);
		unset($_SESSION['show_dialog']);
		unset($_SESSION['confirm_what']);

		// all data for the current sale is held in the session
		// structure of the Current Sale Info (CSI) is,
		// $_SESSION['CSI']['EI']										=	Employee Info
		// $_SESSION['CSI']['CI']										=	Customer Info
		// $_SESSION['CSI']['HH']										=	History Headers
		// $_SESSION['CSI']['HS']										=	History Summary
		// $_SESSION['CSI']['HD']										=	History Details
		// $_SESSION['CSI']['HO']										=	History Overall
		// $_SESSION['CSI']['PI']										=	Pricelist Info
		// $_SESSION['CSI']['CuI']										=	Currency Info
		// $_SESSION['CSI']['CPI']										=	Customer Profile Info
		// $_SESSION['CSI']['TT']										=	Targets
		// $_SESSION['CSI']['SHV']										=	Sales Header Values
		// $_SESSION['CSI']['CT']										=	Cart details
		// $_SESSION['CSI']['PD']										=	Payment Details
		// $_SESSION['CSI']['PM']										=	Payment Methods

		// is the cashtill open for this date?
			// set cash code
			$cash_code													=	'OPEN';

			// test to see if cash open record exists for this date
			$count														=	$this->Cashtill->exists(date("Y"), date("m"), date("d"), $cash_code);

			// if not open, issue warning meesage
			if ($count	==	0)
			{
				$_SESSION['cashtill_not_open']							=	1;
			}
			else
			{
				$_SESSION['cashtill_not_open']							=	0;
			}

		// is the cashtill closed for this date?
			// set cash code
			$cash_code								=	'CLOSE_FINAL';

			// test to see if cash open record exists for this date
			$count									=	$this->Cashtill->exists(date("Y"), date("m"), date("d"), $cash_code);

			// if closed, issue error meesage
			if ($count	==	1)
			{
				$_SESSION['cashtill_closed']		=	1;
			}
			else
			{
				$_SESSION['cashtill_closed']		=	0;

				// if no final close record, then check user has not started the close.
				// set cash code
				$cash_code							=	'CLOSE_BEFORE_DEPOSIT';

				// test to see if cash open record exists for this date
				$count								=	$this->Cashtill->exists(date("Y"), date("m"), date("d"), $cash_code);

				// if close started, issue error meesage
				if ($count	==	1)
				{
					$_SESSION['cashtill_closed']	=	1;
				}
				else
				{
					$_SESSION['cashtill_closed']	=	0;
				}
			}

		// HiDrive sync : skip if davfs2 not installed (avoids 20s blocking on mount/rsync)
		if (is_dir('/home/wrightetmathon/.hidrive.sonrisa') && @exec('which mount.davfs 2>/dev/null')) {
			require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_mount.php";
			require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_by_shop_notification_compare.php";
		}
		
		// load the data
		$this->_reload();
	}

        function item_search()
	{
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'),$this->input->post('limit'));
//		$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions($this->input->post('q'),$this->input->post('limit')));

		echo implode("\n",$suggestions);
	}
	/*function item_search_test()
	{
		//$selected = "description";
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions($this->input->post('q'),$this->input->post('limit')));

		echo implode("\n",$suggestions); 
	}*/
	// description
	function item_search_description() {
		$suggestions = $this->Item->get_item_search_suggestions_description($this->input->post('q'), $this->input->post('limit'));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions($this->input->post('q'),$this->input->post('limit')));

		echo implode("\n",$suggestions); 
	}
	// by article
	function item_search_article() {
		$suggestions = $this->Item->get_item_search_suggestions_article($this->input->post('q'), $this->input->post('limit'));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions($this->input->post('q'),$this->input->post('limit')));

		echo implode("\n",$suggestions); 
	}

	// bay code barS
	function item_search_code() {
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions($this->input->post('q'),$this->input->post('limit')));

		echo implode("\n",$suggestions); 
	}


	function customer_search()
	{
		$suggestions = $this->Customer->get_customer_search_suggestions($this->input->post('q'),$this->input->post('limit'));

		echo implode("\n",$suggestions);
	}

	function customer_select($origin=NULL)
	{
		// get customer input
		// called from _reload routine
		// called when selecting a customer
		// this routine is also called when returning from the customer controller.
		// if returning from the customer controller $_SESSION['CSI']['SHV']->customer_id will be set there
		// if coming from customer select use the input customer id
		switch ($origin)
		{
			// coming from customer select
			case 'SC':
				$customer_id											=	$this->input->post("customer");
				if(!empty($customer_id) && ($customer_id[0]=='X') && isset($customer_id[1]) && is_numeric($customer_id[1]))
				{
					//Si la saisie commence par un X alors c'est surement une carte de fidélité
					//Récupération du customer_id à partir de la carte de fidélité dans les tables ospos_people et ospos_customers
                    $input['profile_reference'] = $_POST['customer'];
                    $data_customer = $this->Customer->get_info_with_parameters($input);
					$customer_id = $data_customer[0]['person_id'];
				} 
				$bool =is_numeric($customer_id);

				if ($customer_id == NULL or $bool == FALSE)
				{
					$this												->	unset_customer();
					$this												->	customer_set_defaults();
					$this												->	_reload();
					return;
				}
				break;
			// coming from customer controller
			case 'RC':
				if (!isset($_SESSION['CSI']['SHV']->customer_id))
				{
					$this												->	unset_customer();
					$this												->	customer_set_defaults();
					$this												->	_reload();
					return;
				}
				else
				{
					if($_SESSION['CSI']['SHV']->customer_id == "-1")
					{
						$this												->	unset_customer();
					    $this												->	customer_set_defaults();
					    $this												->	_reload();
					    return;
					}
					$customer_id										=	$_SESSION['CSI']['SHV']->customer_id;
				}
				break;
			// coming from unsuspend sale
			case 'US':
				$customer_id											=	$_SESSION['CSI']['SHV']->customer_id;
				break;
			// coming from _reload
			case 'RE':
				if (!isset($_SESSION['CSI']['SHV']->customer_id))
				{
					$this												->	unset_customer();
					$this												->	customer_set_defaults();
					return;
				}
				else
				{
					return;
				}
				break;
			default:
				break;
		}
		$_SESSION['customer_id'] = $_SESSION['CSI']['SHV']->customer_id;
		// initialise
		$this															->	unset_customer();

		// get customer info
		$customer_info 													= 	$this->Customer->get_info($customer_id);
		
		//vérification 
		//(email LIKE "%@%") || (phone_number != "") (people)
		//fidelity_flag=='Y' (customers)
		if((($customer_info->email == "") && ($customer_info->phone_number == "")) && ($customer_info->fidelity_flag == 'Y'))
		{
			//
			$_SESSION['customer_info_not_complete']=1;
		}
		else
		{
			$_SESSION['customer_info_not_complete']=0;
		}

		// test if something went wrong - should never happen, since user is selecting from a dropdown.
		if (!$customer_info)
		{
			$_SESSION['error_code']										=	'00100';
			$this														->	_reload();
			return;
		}

		// set header info
		$_SESSION['CSI']['SHV']->pricelist_id							=	$customer_info->pricelist_id;
		$_SESSION['CSI']['SHV']->profile_id								=	$customer_info->profile_id;
		$_SESSION['CSI']['SHV']->mode									=	'sales';

		// get price list info
		$_SESSION['CSI']['PI']											=	$this->Pricelist->get_info($_SESSION['CSI']['SHV']->pricelist_id);

		// get currency information
		$_SESSION['CSI']['CuI']											=	$this->Currency->get_info($_SESSION['CSI']['PI']->pricelist_currency);

		// get customer profile info
		$_SESSION['CSI']['CPI']											=	$this->Customer_profile->get_info($_SESSION['CSI']['SHV']->profile_id);

		// set default profile flag
		if ($_SESSION['CSI']['SHV']->profile_id == $this->config->item('profile_id'))
		{
			$_SESSION['CSI']['SHV']->default_profile_flag				=	1;
		}
		else
		{
			$_SESSION['CSI']['SHV']->default_profile_flag				=	0;
			unset($_SESSION['CSI']['SHV']->overall_discount);
		}

		// get and format customer sales history
		// load appropriate model, controller and get data - the temp sales file already exists, need to reset start_date to beginning of time
		// get report_type (SR or SM)
		$transaction_subtype											=	'sales';
		$transaction_type 												=	$this->Transaction->get_transaction_type($transaction_subtype);

		// load appropriate model and controller
		$this															->	load->model('reports/Specific_customer');
		$this															->	load->library('../controllers/reports');

		// load report data for this customer - limit number of returns to 10 last sales
		$start_date 													=	date('Y-m-d', 0);
		$end_date														= 	date('Y-m-d');
		$transaction_subtype											=	'sales$returns';
		$limit															=	10;
		$report_data													=	$this->Specific_customer->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'person_id'=>$customer_id, 'transaction_subtype'=> $transaction_subtype, 'limit'=>$limit));

		// format data
		$summary_data													=	array();
		$details_data													=	array();
		$this															->	reports->load_data($report_data, $summary_data, $details_data);

		// set data
		$_SESSION['CSI']['CI']											=	$customer_info;
		$_SESSION['CSI']['SHV']->customer_id							=	$customer_id;
		$_SESSION['CSI']['SHV']->customer_formatted						=	$this->Common_routines->format_full_name($_SESSION['CSI']['CI']->last_name, $_SESSION['CSI']['CI']->first_name);
		$_SESSION['CSI']['SHV']->pricelist_id							=	$_SESSION['CSI']['CI']->pricelist_id;
		$_SESSION['CSI']['SHV']->profile_id								=	$_SESSION['CSI']['CI']->profile_id;
		$_SESSION['CSI']['SHV']->tax_name								=	$this->config->item('default_tax_1_name');
		$_SESSION['CSI']['SHV']->tax_percent							=	$this->config->item('default_tax_1_rate');
		$_SESSION['CSI']['SHV']->fidelity_value							=	round($_SESSION['CSI']['CI']->fidelity_points * $this->config->item('fidelity_value'), 2);
		$_SESSION['CSI']['SHV']->client_average_basket					=	($_SESSION['CSI']['CI']->sales_number_of > 0) ? round($_SESSION['CSI']['CI']->sales_ht / $_SESSION['CSI']['CI']->sales_number_of, 2) : 0;
		$_SESSION['CSI']['HH']											=	$this->reports->get_headers($transaction_type);
		$_SESSION['CSI']['HS']											=	$summary_data;
		$_SESSION['CSI']['HD']											=	$details_data;
		$_SESSION['CSI']['HO']											=	array();

		// get top purchased items for this customer
		$_SESSION['CSI']['TOP']											=	$this->Sale->get_customer_top_items($customer_id, 5, 4);

		//set day of b
		$_SESSION['CSI']['SHV']->dob_year = $customer_info->dob_year;
		$_SESSION['CSI']['SHV']->dob_month = $customer_info->dob_month;
		$_SESSION['CSI']['SHV']->dob_day = $customer_info->dob_day;

		// update session customer_id with current (not previous) customer
		$_SESSION['customer_id'] = $customer_id;

		// now reset each cart line
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			// set the discount
			$_SESSION['CSI']['CT'][$line]->line_discount				=	$this->discount_set();
			// set line offered
		//	$_SESSION['CSI']['CT'][$line]->line_offered 				= 	'N';
		}

		// return depends on origin
		switch ($origin)
		{
			// coming from unsuspend
			case 'US':
				return;
				break;
			default:
				$this													->	_reload();
				break;
		}
	}

	function change_mode()
	{
		$mode 						=	$this->input->post("mode");
		$this						->	sale_lib->set_mode($mode);
		$this						->	_reload();
	}

	function set_comment()
	{
		$this->sale_lib->set_comment($this->input->post('comment'));
	}

	function overall_discount()
	{
		// unset last line processed
		$this															->	unset_last_line();

		// get discount
		$_SESSION['CSI']['SHV']->overall_discount						=	$this->input->post('overall_discount_percentage');

		// is discount numeric?
		if (!is_numeric($_SESSION['CSI']['SHV']->overall_discount))
		{
			$_SESSION['error_code']										=	'05310';
			$this														->	_reload();
			return;
		}

		// is discount in range?
		if ($_SESSION['CSI']['SHV']->overall_discount < 0 OR $_SESSION['CSI']['SHV']->overall_discount > 100)
		{
			$_SESSION['error_code']										=	'05720';
			$this														->	_reload();
			return;
		}

		// now reset each cart line
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			if ($_SESSION['CSI']['SHV']->default_profile_flag == 1 AND $_SESSION['CSI']['CT'][$line]->line_offered == 'N')
			{
				$_SESSION['CSI']['CT'][$line]->line_discount			=	$_SESSION['CSI']['SHV']->overall_discount;
			}
		}

		// reload
		$this															->	_reload();
		return;
	}

	function force_price()
	{
		// get force_price
		$force_price			 										= 	$this->input->post('force_price');

		// only do this if force_price is not zero
		if ($force_price != 0)
		{
			// initialise
			$_SESSION['force_price_flag']								=	1;
			$data														=	array();
			$cart														=	array();

			// validate the data
			$this->form_validation->set_rules('force_price', 'lang:sales_force_price', 'numeric');

			if ($this->form_validation->run() == FALSE)
			{
				$data['error']											=	$this->lang->line('sales_must_enter_numeric_force_price');
				$this->_reload($data);
				return;
			}

			// get cart
			$cart														=	$this->sale_lib->get_cart();

			// Force price is not cumulative with either line discounts or total discounts, reset cart back to original state.
			foreach($cart as $key => $item)
			{
				// reset cart fields
				$cart[$key]['discount']									=	0;
				$cart[$key]['price_no_tax']								=	$item['original_no_tax'];
				$cart[$key]['price_with_tax']							=	$item['original_with_tax'];
				if ($_SESSION['price_with_tax'] == 'Y')
				{
					$cart[$key]['price']								=	$item['original_with_tax'];
				}
				else
				{
					$cart[$key]['price']								=	$item['original_no_tax'];
				}
			}

			// set the cart
			$this														->	sale_lib->set_cart($cart);

			// calculate subtotals
			$subtotal_no_tax											=	$this->sale_lib->get_subtotal();
			$subtotal_with_tax											=	$this->sale_lib->get_subtotal_with_tax();

			// calculate discount percentage and then mark each line with this discount percentage
			// 1) find difference between existing subtotal and force_price. force_price is with tax
			$price_difference											=	$subtotal_with_tax - $force_price;

			// 2) calculate percentage difference
			$_SESSION['percentage_difference']							=	number_format(($price_difference / $subtotal_with_tax * 100), 6);

			// 3) remove , etc to make it a number format
			$_SESSION['percentage_difference']							=	preg_replace("/[^0-9\.\-]/","", $_SESSION['percentage_difference']);

			// apply this percentage as an overall discount
			$this														->	overall_discount();
		}

		// reload the data
		$force_price													=	0;
		$data['overall_discount_percentage']							=	$_SESSION['percentage_difference'];
		$data['overall_discount_amount']								=	0;
		$this															->	_reload($data);
 		return;
	}

	function add_payment()
	{
		// initialise
		$pmi															=	$this->input->post('payment_method_id');
		$amt															=	$this->input->post('amount_tendered');

		// check for payment method
		if ($pmi == 0)
		{
			$_SESSION['error_code']										=	'05940';
			$this														->	_reload();
			return;
		}

		// check for numeric amount
		if (!is_numeric($amt))
		{
			$_SESSION['error_code']										=	'05850';
			$_SESSION['substitution_parms']								=	array($amt);
			$this														->	_reload();
			return;
		}

		// check for payment amount
		if ($amt == 0)
		{
			$_SESSION['error_code']										=	'05970';
			$this														->	_reload();
			return;
		}

		// is payment method ID already used?
		if (isset($_SESSION['CSI']['PD'][$pmi]))
		{
			// if so, add new amount to this payment amount
			if(($pmi==2) && ($amt < 0))
			{
				//ne pas fusionner les montant si le paiment s'effectue en espéce
				$pmi_remise=10;
				if (!isset($_SESSION['CSI']['PD'][$pmi_remise])) {
					$_SESSION['CSI']['PD'][$pmi_remise] = new stdClass();
					$_SESSION['CSI']['PD'][$pmi_remise]->payment_amount_TTC = 0;
				}
				$_SESSION['CSI']['PD'][$pmi_remise]->payment_amount_TTC			+=	$amt;
				$_SESSION['CSI']['PD'][$pmi_remise]->payment_method_description ="Espèces";
				$pmi=2;
			}
			else
			{
				if (isset($_SESSION['CSI']['PD'][$pmi]->payment_method_fidelity_flag) && $_SESSION['CSI']['PD'][$pmi]->payment_method_fidelity_flag == 'Y' && isset($_SESSION['CSI']['SHV']->customer_id))
				{
					// Fidélité : vérifier que le total cumulé ne dépasse pas la valeur disponible
					$new_total = $_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC + $amt;
					if (round($_SESSION['CSI']['SHV']->fidelity_value, 2) < round($new_total, 2))
					{
						$_SESSION['error_code']								=	'05910';
						$_SESSION['substitution_parms']						=	array($_SESSION['CSI']['SHV']->fidelity_value, $_SESSION['CSI']['CuI']->currency_sign, $new_total);
						$this												->	_reload();
						return;
					}
					$_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC = $new_total;
				}
				else
				{
					$_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC += $amt;
				}
			}
		}
		else
		{
			// else load payment to array
			$_SESSION['CSI']['PD'][$pmi]								=	$this->Paymethod->get_info($pmi);
			// check payment method has been found
			if (!$_SESSION['CSI']['PD'][$pmi])
			{
				$_SESSION['error_code']									=	'05930';
				$this													->	_reload();
				return;
			}
			// if this is a fidelity card payment and the client is set
			if ($_SESSION['CSI']['PD'][$pmi]->payment_method_fidelity_flag == 'Y' AND isset($_SESSION['CSI']['SHV']->customer_id))
			{
				// test there is enough fidelity value on this client for this fidelity payment
				if (round($_SESSION['CSI']['SHV']->fidelity_value, 2) < round($amt, 2))
				{
					unset($_SESSION['CSI']['PD'][$pmi]);
					$_SESSION['error_code']								=	'05910';
					$_SESSION['substitution_parms']						=	array($_SESSION['CSI']['SHV']->fidelity_value, $_SESSION['CSI']['CuI']->currency_sign, $amt);
					$this												->	_reload();
					return;
				}
			}
			if(!isset($_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC_2))
			{
				$_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC			=	$amt;
			}
			// set payment amount
		}

		// keep payment modal open
		$_SESSION['show_dialog']										=	3;

		// reload
		$this															->	_reload();
	}

	function delete_payment($pmi)
	{
		unset($_SESSION['CSI']['PD'][$pmi]);
		$_SESSION['show_dialog']										=	3;
		$this															->	_reload();
	}

	function add()
	{
		// set item id
		$item_id								 						= 	$this->input->post("item");

		// set the input qty
		$input_qty 														= 	$this->input->post("input_qty");
		if (empty($input_qty) OR !is_numeric($input_qty) OR $input_qty == 0)
		{
			$input_qty													=	1;
		}

		// add line
		$origin															=	'sales_add';
		$this															->	add_line($item_id, $input_qty, $discount=0, $origin);

		// reload
		$this->_reload();
	}

	function edit_item($line)
	{
		// unset last line processed
		$this															->	unset_last_line();
		// unset cart in error
		unset($_SESSION['CSI']['SHV']->cart_in_error);
		// set last cart line processed and default colour
		$_SESSION['CSI']['CT'][$line]->last_line						=	TRUE;
		$_SESSION['CSI']['CT'][$line]->colour							=	'yellow';

		// has quantity changed?
		if ($_SESSION['CSI']['CT'][$line]->line_quantity != $this->input->post("line_quantity"))
		{
			// do verifications
			// is quantity numeric?
			if (!is_numeric($this->input->post("line_quantity")))
			{
				$_SESSION['CSI']['CT'][$line]->colour					=	'red';
				$_SESSION['error_code']									=	'00890';
				$_SESSION['CSI']['SHV']->cart_in_error					=	TRUE;
				$this													->	_reload();
				return;
			}
			// is quantity zero?
			if ($this->input->post("line_quantity") == 0 and $_SESSION['CSI']['CT'][$line]->kit_item == 'N')
			{
				$_SESSION['CSI']['CT'][$line]->colour					=	'red';
				$_SESSION['error_code']									=	'06060';
				$_SESSION['CSI']['SHV']->cart_in_error					=	TRUE;
				$this													->	_reload();
				return;
			}



			/*
			// is this a kit item?
			if ($_SESSION['CSI']['CT'][$line]->kit_item == 'Y')               //Kit
			{
				// test for too much quantity
				// zero total quantity
				$kit_option_quantity									=	$this->input->post("line_quantity") - $_SESSION['CSI']['CT'][$line]->line_quantity;
				// get all cart items...
				foreach ($_SESSION['CSI']['CT'] as $cart_line)
				{
					// if same kit option...
					if ($_SESSION['CSI']['CT'][$line]->kit_option == $cart_line->kit_option)
					{
						// ...accumulate quantities
						$kit_option_quantity							+=	$cart_line->line_quantity;
					}
				}
				// get kit structure option
				$kit_structure_option									=	$this->Item->get_kit_structure_option($_SESSION['CSI']['CT'][$_SESSION['CSI']['CT'][$line]->kit_cart_line]->kit_reference, $_SESSION['CSI']['CT'][$line]->kit_option)->result();
				// test accumulated quantity against quantity this kit structure option
				if ($kit_option_quantity > $kit_structure_option[0]->kit_option_qty)
				{
					// error because qty in cart is greater than quantity for the kit structure option
					$_SESSION['CSI']['CT'][$line]->colour				=	'red';
					$_SESSION['error_code']								=	'07200';
					$_SESSION['substitution_parms']						=	array($this->input->post("line_quantity"));
					$_SESSION['CSI']['SHV']->cart_in_error				=	TRUE;
					$this												->	_reload();
					return;
				}
				// test for quanity on more than one kit line
				$kit_lines_with_quantity								=	1;
				foreach ($_SESSION['CSI']['CT'] as $kit_line => $cart_line)
				{
					// if same kit option...
					if ($_SESSION['CSI']['CT'][$line]->kit_option == $cart_line->kit_option)
					{
						// ...accumulate lines with quantity (exclude this line)
						if ($line != $kit_line and $cart_line->line_quantity != 0)
						{
							$kit_lines_with_quantity					+=	1;
						}
					}
				}
				// test for greater than 1 line
				if ($kit_lines_with_quantity > 1)
				{
					// error because qty is on multiple line in this option
					$_SESSION['CSI']['CT'][$line]->colour				=	'red';
					$_SESSION['error_code']								=	'07210';
					$_SESSION['CSI']['SHV']->cart_in_error				=	TRUE;
					$this												->	_reload();
					return;
				}

			}
//*/
            // is this a kit item?
            if ($_SESSION['CSI']['CT'][$line]->DynamicKit == 'Y')               //Kit
            {
            	// test for too much quantity
            	// zero total quantity
	//			$kit_option_quantity = $this->input->post("line_quantity") - $_SESSION['CSI']['CT'][$line]->line_quantity;
				
				$kit_item_quantity = $this->input->post("line_quantity");

				$kit_items = $this->Item_kit->get_item_kit_items($_SESSION['CSI']['CT'][$line]->item_id);
				


				foreach($kit_items as $key_line => $ligne)
				{
                    // get all cart items...
            	    foreach($_SESSION['CSI']['CT'] as $key_line_2 => $cart_line)
            	    {
						if((intval($kit_items[$key_line]['item_id']) == intval($_SESSION['CSI']['CT'][$key_line_2]->item_id)) && ($_SESSION['CSI']['CT'][$key_line_2]->kit_cart_line == $line) )
						{
							$_SESSION['CSI']['CT'][$key_line_2]->line_quantity = $kit_items[$key_line]['quantity'] * $this->input->post("line_quantity");
						}
				    }
				}






            	
				//	$_SESSION['CSI']['CT']






					/*
            		// if same kit option...
            		if ($_SESSION['CSI']['CT'][$line]->kit_option == $cart_line->kit_option)
            		{
            			// ...accumulate quantities
            			$kit_option_quantity					+=	$cart_line->line_quantity;
            		}//*/
            	//}
            	// get kit structure option
				$kit_structure_option = $this->Item->get_kit_structure_option($_SESSION['CSI']['CT'][$_SESSION['CSI']['CT'][$line]->kit_cart_line]->kit_reference, $_SESSION['CSI']['CT'][$line]->kit_option)->result();
				
				/*
            	// test for quanity on more than one kit line
            	$kit_lines_with_quantity								=	1;
            	foreach ($_SESSION['CSI']['CT'] as $kit_line => $cart_line)
            	{
            		// if same kit option...
            		if ($_SESSION['CSI']['CT'][$line]->kit_option == $cart_line->kit_option)
            		{
            			// ...accumulate lines with quantity (exclude this line)
            			if ($line != $kit_line and $cart_line->line_quantity != 0)
            			{
            				$kit_lines_with_quantity					+=	1;
            			}
            		}
            	}//*/
            }











			// verifications passed
			// set new quantity
			$_SESSION['CSI']['CT'][$line]->line_quantity 				=	$this->input->post("line_quantity");
			// set out of stock indicator (skip for credit notes / negative quantities)
			if ($_SESSION['CSI']['CT'][$line]->line_quantity > 0 && $_SESSION['CSI']['CT'][$line]->quantity < $_SESSION['CSI']['CT'][$line]->line_quantity)
			{
				$_SESSION['CSI']['CT'][$line]->colour					=	'orange';
				$_SESSION['error_code']									=	'06010';
			}
		}

		// has line offered changed?
		if ($_SESSION['CSI']['CT'][$line]->line_offered != $this->input->post("line_offered"))
		{
			// do verifications
			// there are none
			// verifications passed
			// set new line offered
			$_SESSION['CSI']['CT'][$line]->line_offered					=	$this->input->post("line_offered");
			if($_SESSION['CSI']['CT'][$line]->offer == 'N')
			{
				$_SESSION['CSI']['CT'][$line]->line_discount				=	100;
			}
		}

		// has discount changed? discount is already set in add item routine,
		// so only do this if it is zero in cart (ie not already set) and not empty incoming from user
		if ($_SESSION['CSI']['CT'][$line]->line_discount == 0 AND !empty($this->input->post("line_discount")))
		{
			// do verifications
			// is discount numeric?
			if (!is_numeric($this->input->post("line_discount")))
			{
				$_SESSION['CSI']['CT'][$line]->colour					=	'red';
				$_SESSION['error_code']									=	'05310';
				$_SESSION['CSI']['SHV']->cart_in_error					=	TRUE;
				$this													->	_reload();
				return;
			}
			// is discount in range?
			if ($this->input->post("line_discount") < 0 OR $this->input->post("line_discount") > 100)
			{
				$_SESSION['CSI']['CT'][$line]->colour					=	'red';
				$_SESSION['error_code']									=	'05720';
				$_SESSION['CSI']['SHV']->cart_in_error					=	TRUE;
				$this													->	_reload();
				return;
			}
			// test loss per line
			$cart_control												=	"loss_line_before_update";
			$loss														=	$this->cart_control($cart_control, $line, $this->input->post("line_discount"));
			if ($loss == TRUE)
			{
				$this													->	unset_last_line();
				$_SESSION['CSI']['CT'][$line]->last_line				=	TRUE;
				$_SESSION['CSI']['CT'][$line]->colour					=	'red';
				$_SESSION['error_code']									=	'05760';
				$_SESSION['substitution_parms']							=	array($this->input->post("line_discount"));
				$_SESSION['CSI']['SHV']->cart_in_error					=	TRUE;
				$this													->	_reload();
				return;
			}

			// verifications passed
			// set new line discount
			$_SESSION['CSI']['CT'][$line]->line_discount				=	$this->input->post("line_discount");
		}

		// calculate values
		$this															->	line_values($line);

		// reload
		$this															->	_reload();
	}

	function delete_item($master_line)
	{
		// now that we are handling kits in the cart, we need to delete all the child lines of the kit as well
		// as the master kit line
		// so, read the cart to find all kit lines
		foreach($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			// test if kit_cart_line is equal master line, if so delete it
			if ($_SESSION['CSI']['CT'][$line]->kit_item == 'Y' AND $cart_line->kit_cart_line == $master_line)
			{
				unset($_SESSION['CSI']['CT'][$line]);
			}
		}

		// Now delete the master row
		unset($_SESSION['CSI']['CT'][$master_line]);

		// unset last line processed
		$this															->	unset_last_line();

		// reload
		$this															->	_reload();
	}

	function cart_control($control_type, $line, $discount=0)
	{
		// control the cart for errors depending on control type
		// return TRUE if error
		switch ($control_type)
		{
			// test for overall loss
			case "loss_overall":
				if ($_SESSION['CSI']['SHV']->header_profit_HT_normal < 0)
				{
					// unset last line processed
				$this												->	unset_last_line();
				$_SESSION['error_code']								=	'06070';
				$_SESSION['CSI']['SHV']->cart_in_error				=	TRUE;
				return												TRUE;
				}
				break;

			// test for a line loss	after update
			case "loss_line":
				foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
				{
					if ($_SESSION['CSI']['CT'][$line]->line_offered == 'N' AND $_SESSION['CSI']['CT'][$line]->CN_line != 'Y'  && $_SESSION['CSI']['CT'][$line]->off != '1')
					{
						if ($_SESSION['CSI']['CT'][$line]->line_profit_HT < 0)
						{
							// unset last line processed
						$this										->	unset_last_line();
						$_SESSION['CSI']['CT'][$line]->last_line	=	TRUE;
						$_SESSION['CSI']['CT'][$line]->colour		=	'red';
						$_SESSION['error_code']						=	'05760';
						$_SESSION['substitution_parms']				=	array($_SESSION['CSI']['CT'][$line]->line_discount);
						$_SESSION['CSI']['SHV']->cart_in_error		=	TRUE;
						return										TRUE;
						}
					}
				}
				break;

			// test for a line loss	before update
			case "loss_line_before_update":
				if ($_SESSION['CSI']['CT'][$line]->line_offered == 'N' AND $_SESSION['CSI']['CT'][$line]->CN_line != 'Y')
				{
					$line_value											=	round(($_SESSION['CSI']['CT'][$line]->line_valueBD_HT * (100 - $discount) / 100), 2);
					$line_profit										=	round($line_value - $_SESSION['CSI']['CT'][$line]->line_cost_HT, 2);
					if ($line_profit < 0)
					{
						return											TRUE;
					}
				}
				break;
			default:
				break;
		}
		// if no errors return FALSE
		return															FALSE;
	}

	function unset_customer()
	{
		unset($_SESSION['CSI']['SHV']->customer_id);
		unset($_SESSION['CSI']['SHV']->customer_formatted);
		unset($_SESSION['CSI']['SHV']->pricelist_id);
		unset($_SESSION['CSI']['SHV']->profile_id);
		unset($_SESSION['CSI']['CI']);
		unset($_SESSION['CSI']['HH']);
		unset($_SESSION['CSI']['HS']);
		unset($_SESSION['CSI']['HD']);
		unset($_SESSION['CSI']['HO']);
		unset($_SESSION['CSI']['TOP']);
	}

	function customer_remove()
	{
		// are there any payments? if so customer cannot be removed
		if (count($_SESSION['CSI']['PD']) != 0)
		{
			$this														->	unset_last_line();
			$_SESSION['error_code']										=	'07110';
			$this														->	_reload();
			return;
		}

		// is this a credit note
		if ($_SESSION['CSI']['SHV']->CN_original_invoice != NULL)
		{
			$this														->	unset_last_line();
			$_SESSION['error_code']										=	'07270';
			$this														->	_reload();
			return;
		}

		// unset and re-select the customer
		unset($_SESSION['CSI']['SHV']->customer_id);
		$this															->	customer_select('RE');
		// unset overall discount
		unset($_SESSION['CSI']['SHV']->overall_discount);
		// now reset each cart line
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			// set the discount
			$_SESSION['CSI']['CT'][$line]->line_discount				=	$this->discount_set();
			// set line offered flag
			$_SESSION['CSI']['CT'][$line]->line_offered					=	'N';
		}
		$this															->	_reload();
		return;
	}

	function complete()
	{

		unset($_SESSION['var_annulation_facture']);
		unset($_SESSION['var_annulation_facture_partielle']);
		//
		// KITS
		//

		
		$_SESSION['sales_offer_value'] = 0.0;
		$vapeself = 0;
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			if($_SESSION['CSI']['CT'][$line]->item_number == '123456789')
			{
				$vapeself = 1;
		//		$credit_vapeself = $_SESSION['CSI']['CT'][$line]->
			}
			if(!isset($cart_line->offer_value))
			{
				$cart_line->offer_value=0;
			}
			$cart_line->offer_value = ($cart_line->offer_value) * $_SESSION['CSI']['CT'][$line]->line_quantity;
			//if ($_SESSION['CSI']['CT'][$line]->export_to_other == 'Y') { // Ne pas compter de fidélité pour ce produit
            //    $prices = $this->get_price($_SESSION['CSI']['CT'][$line]->item_id, $_SESSION['CSI']['SHV']->pricelist_id);
		    //    $line_priceTTC = $prices['price_with_tax'];
		    //    $line_priceHT = $prices['price_no_tax'];
			//	$cart_line->offer_value = $line_priceTTC * $_SESSION['CSI']['CT'][$line]->line_quantity;
            //}	
			
			$valflotante=floatval($cart_line->offer_value);
            $_SESSION['sales_offer_value'] = $_SESSION['sales_offer_value'] + $valflotante;
            
		}
		if($vapeself == 1)
		{
	//		$data_vs_credit_client = get_solde($_SESSION['CSI']['SHV']->customer_id)
			$data_Credit_Client = array();
			$data_Credit_Client['IDClient'] = $_SESSION['CSI']['SHV']->customer_id;
			$data_Credit_Client['DateCredit'] = date("d-m-Y H:i:s");
			$data_Credit_Client['Montant'] = $_SESSION['CSI']['SHV']->header_valueAD_TTC;
			$data_Credit_Client['Solde'] = 208.00;	
	
			//chargement de la class vapeself 
			$this->load->library("../controllers/vapeself");
	
			//obtention du jeton Token Bearer
		//	$token = $this->vapeself->get_Token();
		//	$return_InsertCredit = $this->vapeself->post_InsertCredit($token, $data_Credit_Client);
		}
		/*
		// Test kit reference / kit option quantities if dynamic kit
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			// test for Dynamic kit
			if ($cart_line->DynamicKit == 1)
			{
				// get kit structure
				$kit_structure											=	$this->Item->get_kit_structure($cart_line->kit_reference)->result_array();

				// now read kit structure
				foreach ($kit_structure as $kit)
				{
					$cart_kit_option_qty								=	0;

					// search the cart for reference/option/kit_cart_line combination
					foreach ($_SESSION['CSI']['CT'] as $kit_row)
					{
						if ($kit_row['kit_item'] == 'Y' AND $cart_line->kit_reference == $kit_row['kit_reference'] AND $cart_line->kit_option == $kit_row['kit_option'] AND $cart_row['line'] == $kit_row['kit_cart_line'])
						{
							$cart_kit_option_qty						=	$cart_kit_option_qty + $kit_row['quantity'];
						}
					}

					// now test entered quantities to quantity allowed on option
					if ($cart_kit_option_qty	!=	$kit['kit_option_qty'])
					{
						$data['error']									=	$this->lang->line('sales_kit_option_quantity_error').$kit['kit_option'];
						$this											->	_reload($data);
						return;
					}
				}
			}
		}//*/

		// Test kit reference / kit option quantities if dynamic kit
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			//only for the invoice items
			if($cart_line->line_quantity < 0)
			{
			    //récupération de la valeur de la remise pour les avoirs
			    if(isset($_SESSION['CSI']['SHV']->CN_original_invoice_items))
			    {
			    	foreach($_SESSION['CSI']['SHV']->CN_original_invoice_items as $item_sale => $line_item_sale)
			    	{
			    		if(intval($_SESSION['CSI']['SHV']->CN_original_invoice_items[$item_sale]->line_discount) != 0)
			    		{
							if($_SESSION['CSI']['CT'][$line]->item_id == $_SESSION['CSI']['SHV']->CN_original_invoice_items[$item_sale]->item_id)
							{
			    			    $_SESSION['CSI']['CT'][$line]->line_discount = $_SESSION['CSI']['SHV']->CN_original_invoice_items[$item_sale]->line_discount;
			    			    $this->line_values($line);
						    }
			    		}
					}
			    }
		    }
			// test for Dynamic kit
			if ($cart_line->DynamicKit == 'Y')
			{
				$indicator_kit = 1;
				// get kit structure
				$kit_structure = $this->Item_kit->get_item_kit_items($cart_line->item_id);


                
                foreach($kit_structure as $line_item => $kit_structure_item)
                {
                    $kit_structure[$line_item]['quantity'] = $kit_structure[$line_item]['quantity'] * $_SESSION['CSI']['CT'][1]->line_quantity;
                }
				/*
				// now read kit structure
				foreach ($kit_structure as $kit)
				{
					$cart_kit_option_qty = 0;


					// search the cart for reference/option/kit_cart_line combination
					foreach ($_SESSION['CSI']['CT'] as $kit_row)
					{
						if ($kit_row['kit_item'] == 'Y' AND $cart_line->kit_reference == $kit_row['kit_reference'] AND $cart_line->kit_option == $kit_row['kit_option'] AND $cart_row['line'] == $kit_row['kit_cart_line'])
						{
							$cart_kit_option_qty = $cart_kit_option_qty + $kit_row['quantity'];
						}
					}
				}//*/
			}
		}






		// remove zero quantity lines from cart if a kit item
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			if ($cart_line->kit_item == 'Y' AND $cart_line->line_quantity == 0)
			{
				unset($_SESSION['CSI']['CT'][$line]);
			}
		}

	// load output data
		// get the transaction info
		// transaction_mode = something like "sales"
		// transaction code = something like "SALE-"

		//Affecte la valeur  "SALE-" à la variable $_SESSION['CSI']['SHV']->transaction_code 
		$_SESSION['CSI']['SHV']->transaction_code						= 	$this->Transaction->get_transaction_code($_SESSION['CSI']['SHV']->mode);
		
		//$_SESSION['CSI']['SHV']->transaction_title="Facture"
		$_SESSION['CSI']['SHV']->transaction_title						= 	$this->lang->line('reports_'.$_SESSION['CSI']['SHV']->mode);

		// get transaction updatestock indicator
		//$_SESSION['CSI']['SHV']->transaction_update_stock="Y"
		$_SESSION['CSI']['SHV']->transaction_update_stock				= 	$this->Transaction->get_transaction_updatestock($_SESSION['CSI']['SHV']->mode);

		// set transaction time
		//$_SESSION['CSI']['SHV']->transaction_time="date heure"
		$_SESSION['CSI']['SHV']->transaction_time						= 	date('d/m/Y H:i:s');

		// set comment
		if ($_SESSION['CSI']['SHV']->comment == NULL)
		{
			$_SESSION['CSI']['SHV']->comment							=	$_SESSION['CSI']['SHV']->transaction_title;
		}

// ALL GOOD, so SAVE sale to database

		// PHP 8 guard: ensure employee_id is a valid integer before INSERT
		if (empty($_SESSION['CSI']['SHV']->employee_id)) {
			$_SESSION['CSI']['EI'] = $this->Employee->get_logged_in_employee_info();
			$_SESSION['CSI']['SHV']->employee_id = $_SESSION['CSI']['EI']->person_id;
		}
		if (empty($_SESSION['CSI']['SHV']->employee_id)) {
			$_SESSION['error_code'] = '05830';
			$this->_reload();
			return;
		}

	// START - save the HEADER

		// Build payment types string
		$payment_types				=	'';
		foreach ($_SESSION['CSI']['PD'] as $pmi => $payment)
		{
			$payment_types												=	$payment_types.$payment->payment_method_description.': '.to_currency($payment->payment_amount_TTC).'<br />';
		}

		$sales_data = array	(
							'customer_id'								=>	$_SESSION['CSI']['SHV']->customer_id,
							'employee_id'								=>	$_SESSION['CSI']['SHV']->employee_id,
							'payment_type'								=>	$payment_types,
							'comment'									=>	$_SESSION['CSI']['SHV']->comment,
							'mode'										=>	$_SESSION['CSI']['SHV']->mode,
							'overall_discount_percentage'				=>	0,
							'overall_discount_amount'					=>	0,
							'subtotal_before_discount'					=>	$_SESSION['CSI']['SHV']->header_valueBD_HT,
							'subtotal_discount_percentage_amount'		=>	0,
							'subtotal_discount_amount_amount'			=>	0,
							'subtotal_after_discount'					=>	$_SESSION['CSI']['SHV']->header_valueAD_HT,
							'overall_tax'								=>	$_SESSION['CSI']['SHV']->header_taxAD,
							'overall_total'								=>	$_SESSION['CSI']['SHV']->header_valueAD_TTC,
							'overall_tax_percentage'					=>	$_SESSION['CSI']['SHV']->tax_percent,
							'overall_tax_name'							=>	$_SESSION['CSI']['SHV']->tax_name,
							'overall_cost'								=>	$_SESSION['CSI']['SHV']->header_cost_HT,
							'overall_profit'							=>	$_SESSION['CSI']['SHV']->header_profit_HT,
							'amount_change'								=>	$_SESSION['CSI']['SHV']->header_amount_due_TTC,
							'branch_code'								=>	$this->config->item('branch_code')
							);
		// save header to DB
		$sale_id														=	$this->Sale->save_sales_header($sales_data);

		// test for correct database entry - something went wrong if trans id = -1
		if ($sale_id == ('-1'))
		{
			$_SESSION['error_code']										=	'05830';
			$this														->	_reload();
			return;
		}

		// add sales_id to data array
		$_SESSION['CSI']['SHV']->sale_id								=	$sale_id;

		// update fidelity points if client fidelity flag turned on
		if ($_SESSION['CSI']['CI']->fidelity_flag == 'Y')
		{
			if($_SESSION['CSI']['SHV']->header_valueAD_TTC<0)
			{
                $new_points = $_SESSION['CSI']['CI']->fidelity_points + ceil(($_SESSION['CSI']['SHV']->header_valueAD_TTC - $_SESSION['sales_offer_value'])/ $this->config->item('fidelity_rule'));
			}
			if($_SESSION['CSI']['SHV']->header_valueAD_TTC>=0)
			{
				// accumulate fidelity points for this sale
			    $new_points = $_SESSION['CSI']['CI']->fidelity_points + floor(($_SESSION['CSI']['SHV']->header_valueAD_TTC - $_SESSION['sales_offer_value'])/ $this->config->item('fidelity_rule'));
			    //$new_points = $_SESSION['CSI']['CI']->fidelity_points + round(($_SESSION['CSI']['SHV']->header_valueAD_TTC / $this->config->item('fidelity_rule')),2);
			}
			// test number of points is not higher than limit, if so set to limit
			$limit_points												=	floor($this->config->item('fidelity_maximum') / $this->config->item('fidelity_value'));
			if ($new_points > $limit_points)
			{
				$new_points												=	$limit_points;
			}
		}

		// update fidelity value
		$_SESSION['CSI']['SHV']->fidelity_value							=	round($new_points * $this->config->item('fidelity_value'), 2);

		// update customer counts
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['new']												=	0;
		$_SESSION['transaction_info']->person_id						=	$_SESSION['CSI']['SHV']->customer_id;
		$_SESSION['transaction_info']->sales_ht							=	$_SESSION['CSI']['CI']->sales_ht + $_SESSION['CSI']['SHV']->header_valueAD_HT;
		$_SESSION['transaction_info']->sales_number_of					=	$_SESSION['CSI']['CI']->sales_number_of + 1;
		$_SESSION['transaction_info']->fidelity_points					=	$new_points;

		$this															->	Customer->save_counts();

		// update employee counts
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['new']												=	0;
		$_SESSION['transaction_info']->person_id						=	$_SESSION['CSI']['SHV']->employee_id;
		$_SESSION['transaction_info']->sales_ht							=	$_SESSION['CSI']['EI']->sales_ht + $_SESSION['CSI']['SHV']->header_valueAD_HT;
		$_SESSION['transaction_info']->sales_number_of					=	$_SESSION['CSI']['EI']->sales_number_of + 1;

		$this															->	Employee->save_counts();

	// END - save the HEADER

	// START - save the PAYMENTS

		// get the payments
		foreach ($_SESSION['CSI']['PD'] as $pmi => $payment)
		{
			if($pmi!=10)
			{
				if(isset($_SESSION['CSI']['PD'][10]) && ($pmi==2))
				{
					$total = floatval($payment->payment_amount_TTC) +  floatval($_SESSION['CSI']['PD'][10]->payment_amount_TTC);
				}
				else
				{
					$total = $payment->payment_amount_TTC; 
				}
			$sales_payment_data 										= 	array	(
																					'sale_id'					=>	$_SESSION['CSI']['SHV']->sale_id,
																					'payment_method_code'		=>	$payment->payment_method_code,
																					'payment_type'				=>	$payment->payment_method_description,
																					'payment_amount'			=>	$total, //$payment->payment_amount_TTC,
																					'branch_code'				=>	$this->config->item('branch_code')
																					);
			// save the payment
			$this														->	Sale->save_sales_payment($sales_payment_data);
				
			if(isset($_SESSION['CSI']['PD'][10]))
				{
					if($pmi==2 || $pmi==10)
					{
					    $payment->payment_amount_TTC = $total;
					}
				}


			// for clients with fidelity and this is a fidelity card payment
			// ...reduce client fidelity points
			if ($_SESSION['CSI']['CI']->fidelity_flag == 'Y' AND $payment->payment_method_fidelity_flag == 'Y')
			{
				// calculate pay points
				$pay_points												=	$payment->payment_amount_TTC / $this->config->item('fidelity_value');
				
				$new_pay_points = floor(($_SESSION['CSI']['SHV']->header_valueAD_TTC - $_SESSION['sales_offer_value']) - $payment->payment_amount_TTC) / $this->config->item('fidelity_rule');

				// now reduce the fidelity points - $new_points has been calculated above for client attracting fidelity
				if($_SESSION['CSI']['SHV']->header_valueAD_TTC<0)
			    {
					$new_points = $_SESSION['CSI']['CI']->fidelity_points - $pay_points + ceil($new_pay_points) ;
                }
			    if($_SESSION['CSI']['SHV']->header_valueAD_TTC>=0)
			    {
					$new_points = $_SESSION['CSI']['CI']->fidelity_points - $pay_points + floor($new_pay_points) ;
				}

				// test for less than zero points - set to 0 if so
				if ($new_points < 0)
				{
					$new_points											=	0;
				}

                // update fidelity value
				$_SESSION['CSI']['SHV']->fidelity_value					=	$new_points * $this->config->item('fidelity_value');

				// update the client record
				$_SESSION['transaction_info']							=	new stdClass();
				$_SESSION['new']										=	0;
				$_SESSION['transaction_info']->person_id				=	$_SESSION['CSI']['SHV']->customer_id;
				$_SESSION['transaction_info']->fidelity_points			=	$new_points;

				$this													->	Customer->save_counts();
			}
		}
	}
	// END - save the PAYMENTS

	// START - save the SALES ITEMS

		// read the cart
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			// set description
			if ($cart_line->CN_line == 'Y')
			{
				$description											=	$cart_line->description;
			}
			else
			{
				$description											=	$cart_line->name;
			}

			//    $_SESSION['CSI']['CT'][$line]->off = '1';
			if($_SESSION['CSI']['CT'][$line]->off =='1')
			{
				$cart_line->line_profit_HT = 0;
			}
			if(($cart_line->line_valueBD_HT == 0) && ($cart_line->item_unit_price == 0) && ($cart_line->line_valueAD_HT == 0) && ($cart_line->line_taxAD == 0) && ($cart_line->line_valueAD_TTC == 0) && ($_SESSION['CSI']['SHV']->mode == "returns") )
			{
                $cart_line->line_profit_HT = 0;
			}

			$tax = $this->Item_taxes->get_info($cart_line->item_id);

			// create update data set
			$sales_item_data	= 	array	(
										'sale_id'						=>	$_SESSION['CSI']['SHV']->sale_id,
										'item_id'						=>	$cart_line->item_id,
										'line_category_id'				=>	$cart_line->category_id,
										'line_category'					=>	$cart_line->category,
										'line_item_number'				=>	$cart_line->item_number,
										'line_name'						=>	$cart_line->name,
										'description'					=>	$description,
										'serialnumber'					=>	$cart_line->serialnumber,
										'line'							=>	$line,
										'quantity_purchased'			=>	$cart_line->line_quantity,
										'item_cost_price' 				=>	floatval($cart_line->supplier_cost_price),
										'item_unit_price'				=>	floatval($cart_line->line_priceHT),
										'discount_percent'				=>	$cart_line->line_discount,
										'line_sales_before_discount'	=>	$cart_line->line_valueBD_HT,
										'line_discount'					=>	$cart_line->line_discount,
										'line_sales_after_discount'		=>	$cart_line->line_valueAD_HT,
										'line_tax'						=>	$cart_line->line_taxAD,
										'line_sales'					=>	$cart_line->line_valueAD_TTC,
										'line_cost'						=>	$cart_line->line_cost_HT,
										'line_profit'					=>	$cart_line->line_profit_HT,
                                        'line_tax_percentage'			=>	floatval($tax[0]['percent']),
                                        'line_tax_name'					=>	$_SESSION['CSI']['SHV']->tax_name,
										'line_giftcard_number'			=>	0,
										'branch_code'					=>	$this->config->item('branch_code')
										);

			// save this line
			$this														->	Sale->save_sales_item($sales_item_data);

			// Update stock quantity if required and reset the rolling stock count indicator so that this item is recounted
			if ($_SESSION['CSI']['SHV']->transaction_update_stock == 'Y')
			{
				$_SESSION['transaction_info']							=	new stdClass();
				$_SESSION['new']										=	0;
				$_SESSION['transaction_info']->item_id					=	$cart_line->item_id;
				
				//Récupération de la valeur réelle présente en stock
                $quantity_stock_now = $this->Item->get_info($_SESSION['transaction_info']->item_id);
				$cart_line->quantity = $quantity_stock_now->quantity;
				$_SESSION['transaction_info']->quantity = $cart_line->quantity - intval($cart_line->line_quantity);
				$_SESSION['transaction_info']->rolling_inventory_indicator =	0;

				$this													->	Item->save();

				// update DLUO totals if DLUO is used on this item
				if ($cart_line->dluo_indicator == 'Y')
				{
					// set session
					$_SESSION['transaction_info']->item_id				=	$cart_line->item_id;

					// get DLUO records
					$item_info_dluo										=	array();
					$item_info_dluo										=	$this->Item->get_info_dluo($cart_line->item_id)->result_array();
					$dluo_remaining_qty									=	$cart_line->line_quantity;

					// read records
					foreach ($item_info_dluo as $row)
					{
						// test for qty left
						if ($dluo_remaining_qty != 0)
						{
							// test item qty against dluo qty this line
							if ($row['dluo_qty'] >= $dluo_remaining_qty)
							{
								// subtract item qty from dluo qty and update record
								$new_dluo_qty							=	$row['dluo_qty'] - $dluo_remaining_qty;

								// update or delete record
								if ($new_dluo_qty > 0)
								{
									$dluo_data 							= 	array	(
																					'dluo_qty'	=>	$new_dluo_qty
																					);
									$this								->	Item->dluo_edit($row['year'], $row['month'], $dluo_data);
								}
								else
								{
									$this								->	Item->dluo_delete($row['year'], $row['month']);
								}

								// zero the qty remaining
								$dluo_remaining_qty						=	0;
							}
							else
							{
								// if here dluo qty is < qty remaining
								// calculate the new remaining qty
								$dluo_remaining_qty						=	$dluo_remaining_qty - $row['dluo_qty'];

								// now delete this dluo record
								$this									->	Item->dluo_delete($row['year'], $row['month']);
							}
						}
					}
				}
				if ($cart_line->DynamicKit != 'Y')    //if l'item n'est pas un item_kit
				{
			    	// update stock valuation records
				    if ($_SESSION['transaction_info']->quantity < 0)
				    {
				    	$this												->	Item->value_delete_item_id($cart_line->item_id);
				    	$valuation_data										=	array	(
				    																	'value_item_id'		=>	$cart_line->item_id,
				    																	'value_cost_price'	=>	floatval($cart_line->supplier_cost_price),
				    																	'value_qty'			=>	$_SESSION['transaction_info']->quantity,
				    																	'value_trans_id'	=>	0,
				    																	'branch_code'		=>	$this->config->item('branch_code')
				    																	);
				    	$this												->	Item->value_write($valuation_data);
				    }
				    else
				    {
				    	$value_remaining_qty								=	$cart_line->line_quantity;
				    	$value_trans_id										=	$_SESSION['CSI']['SHV']->sale_id;
				    	$this												->	Item->value_update($value_remaining_qty, $cart_line->item_id, $cart_line->supplier_cost_price, $value_trans_id);
				    }
			    }

				// set stock after for inventory detail record
				$trans_stock_after										=	$_SESSION['transaction_info']->quantity;
			}
			else
			{
				$trans_stock_after										=	$cart_line->quantity;
			}

			// Inventory Count Details
			$qty_buy													=	-$cart_line->line_quantity;

			$sale_remarks												=	$_SESSION['CSI']['SHV']->transaction_code.$_SESSION['CSI']['SHV']->sale_id.' - '.$_SESSION['CSI']['SHV']->transaction_title;
			$inventory_data 											= 	array	(
																					'trans_items'		=>	$cart_line->item_id,
																					'trans_user'		=>	$_SESSION['CSI']['SHV']->employee_id,
																					'trans_comment'		=>	$sale_remarks,
																					'trans_stock_before'=>	floatval($cart_line->quantity),
																					'trans_inventory'	=>	$qty_buy,
																					'trans_stock_after'	=>	$trans_stock_after,
																					'branch_code'		=>	$this->config->item('branch_code')
																					);
			$this														->	Inventory->insert($inventory_data);

			// update item counts
			$_SESSION['transaction_info']								=	new stdClass();
			$_SESSION['new']											=	0;
			$_SESSION['transaction_info']->item_id						=	$cart_line->item_id;
			$_SESSION['transaction_info']->sales_ht						=	$cart_line->sales_ht + $cart_line->line_valueAD_HT;
			$_SESSION['transaction_info']->sales_qty					=	$cart_line->sales_qty + $cart_line->line_quantity;

			$this														->	Item->save();

			// update category counts
			$trans_data 												= 	$this->Category->get_info($cart_line->category_id);
			$new_total													=	$trans_data->category_sales_value + $cart_line->line_valueAD_HT;
			$new_total_number_of										=	$trans_data->category_sales_qty + $cart_line->line_quantity;

			$_SESSION['transaction_info']								=	new stdClass();
			$_SESSION['new']											=	0;
			$_SESSION['transaction_info']->category_id					=	$cart_line->category_id;
			$_SESSION['transaction_info']->category_sales_value			=	$new_total;
			$_SESSION['transaction_info']->category_sales_qty			=	$new_total_number_of;

			$this														->	Category->save();
		// get next sales item
		}

	// END - save the SALES ITEMS

	// START - save the SALES ITEMS TAXES

		// read the cart
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			foreach($this->Item_taxes->get_info($cart_line->item_id) as $row)
			{
				$sales_item_tax_data	= array	(
										'sale_id' 						=>	$_SESSION['CSI']['SHV']->sale_id,
										'item_id' 						=>	$cart_line->item_id,
										'line'      					=>	$line,
										'name'							=>	$row['name'],
										'percent' 						=>	$row['percent'],
										'branch_code'					=>	$this->config->item('branch_code')
										);

				// save this tax line
				$this													->	Sale->save_sales_item_tax($sales_item_tax_data);
			// get next tax record
			}
		// get next sales item
		}

		unset($_SESSION['TVA']);
		$_SESSION['TVA'] = array();
		$sale_id = $_SESSION['CSI']['SHV']->sale_id;
	    //research TVA for this current sale
	    $tva = $this->Sale->get_overall_tax_for_sale_with_sale_id($sale_id);
	    foreach($tva as $percent => $value)
	    {
	    	if(!empty($value))
	    	{
	    		$per = $value['line_tax_percentage'];
	    		$val = floatval($value['som']);
	    		$_SESSION['TVA']["$per"] = $val;
	    	}
	    }

	// END - save the SALES ITEMS TAXES
	// START - POST database save operations

		// Print a ticket
		// open ticket printer
		//$printer														=	$this->config->item('ticket_printer');
		//$ph 															=	fopen($printer, "w");

		//check option for ticket print
		if(!isset($_SESSION['sales_without_ticket']))
        {
        	
        	if(!isset($_SESSION['variable_tampon_booleen']) || ($_SESSION['blocage_de_l_impression_du_ticket_de_caisse']=='4'))
        	{
        		//Variable pour savoir si le mail peut être envoyé
        		//$_SESSION['variable_tampon_booleen']='1';
        		
        		$printer    =    $this->config->item('ticket_printer');
        	    $ph    =    @fopen($printer, "w");
        	}
        	if($_SESSION['variable_tampon_booleen']=='1')
        	{
        		//Variable pour savoir si le mail peut être envoyé
        	    //$_SESSION['variable_tampon_booleen']='1';

        	    //écriture du ticket dans un fichier "poubelle"
        		$ph_texte = @fopen("/var/www/html/wrightetmathon/ticket.txt", "w");
        		$ph =$ph_texte;
        		//unset($_SESSION['variable_tampon_booleen']);
        		$imprimante    =    $this->config->item('ticket_printer');
        		$ph_ouverture_de_caisse = @fopen($imprimante, "w");
			if ($ph_ouverture_de_caisse) {
                		fwrite ($ph_ouverture_de_caisse, chr (27) .chr (112) .chr (48) .chr (55) .chr (121));
				fclose($ph_ouverture_de_caisse);
			}
            }


            //fwrite ($ph, "texte à l'imprimante");
		if ($ph) {
        		fwrite ($ph, chr (27) .chr (112) .chr (48) .chr (55) .chr (121)); //La commande de l'imprimante Epson TM-T88V
		}
        	
    
        	// if it opens, print a ticket
        	if ($ph)
        	{
        		$_SESSION['CSI']['SHV']->ph									=	$ph;
        		//Pour le ticket de caisse: /var/www/html/wrightetmathon/application/views/sales/ticket.php
        		$this->load->view("sales/ticket");
        	}
		}
		if(isset($_SESSION['sales_without_ticket']))
		{
			$imprimante    =    $this->config->item('ticket_printer');
        		$ph_ouverture_de_caisse = @fopen($imprimante, "w");
			if ($ph_ouverture_de_caisse) {
                		fwrite ($ph_ouverture_de_caisse, chr (27) .chr (112) .chr (48) .chr (55) .chr (121));
				fclose($ph_ouverture_de_caisse);
			}
		}
		

	require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_mount.php";
	require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_by_shop_notification_compare.php";
	// END - POST database save operations

	// ALL DONE - clear sale and return
		// unset the global Current sales info
		
		if(isset($_SESSION['sales_without_ticket']))
        {
			unset($_SESSION['sales_without_ticket']);
		}
		unset($_SESSION['show_dialog']);
		$_SESSION['error_code']											=	'05840';
		unset($_SESSION['CSI']);
		unset($_SESSION['show_dialog']);
		$_SESSION['error_code']											=	'05840';
		

		if(!isset($_SESSION['variable_tampon_booleen']) || ($_SESSION['blocage_de_l_impression_du_ticket_de_caisse']=='4'))
		{
			if(isset($_SESSION['sales_without_ticket']))
			{
				unset($_SESSION['sales_without_ticket']);
				redirect("sales");
			}
			redirect("sales");
		}

		if($_SESSION['variable_tampon_booleen']=='1')
		{	
			unset($_SESSION['variable_tampon_booleen']);
		}
        if($_SESSION['variable_tampon_booleen']=='0')
		{	
			unset($_SESSION['variable_tampon_booleen']);
		}

		unset($_SESSION['blocage_de_l_impression_du_ticket_de_caisse']);
		unset($_SESSION['var_annulation_facture']);
		unset($_SESSION['var_annulation_facture_partielle']);
		unset($_SESSION['sales_offer_value']);
		unset($_SESSION['TVA']);
		return;
	}

	function import_sales($key = -1)
    {
		if($key == -1)
		{
			$_SESSION['show_dialog'] = 9;
			$this->load->view('people/manage');
		}
		// load appropriate model and controller
//		$this->load->model('reports/Specific_customer');
		$this->load->library('../controllers/reports');
//		$customer_id = $_SESSION['data_remote_list_customers'][$key]->person_id;
        $customer_id = $_SESSION['data_remote_list_customers'][$key]->customer_id;
        //import sales && make invoice
		$inputs = array();
		$inputs['start_date'] = date("Y-m-d", 0);
		$inputs['start_date'] = date("Y-m-d");
		$inputs['person_id'] = $customer_id;
		$inputs['transaction_subtype'] = 'sales$returns';
		$inputs['limit'] = 10;
	
		// load report data for this customer - limit number of returns to 10 last sales
//		$start_date 		 =	date('Y-m-d', 0);
//		$end_date			 = 	date('Y-m-d');
//		$transaction_subtype =	'sales$returns';
		$limit				 =	100;
//		$report_data		 =	$this->Specific_customer->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'person_id'=>$customer_id, 'transaction_subtype'=> $transaction_subtype, 'limit'=>$limit));
		$lien = 'person_id='.$customer_id.'&limit='.$limit;
		$url_get = urlencode($lien);
		
		$url = 'http://' . $_SESSION['ip_cible'] .'/wrightetmathon/index.php/apis/sales/' . $url_get;
        $header = array('Content-Type: application/json');
        $curl = curl_init();
        $test = curl_setopt($curl, CURLOPT_URL, $url);
        $test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $test = curl_setopt($curl, CURLOPT_POST, false);
        $test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $return_exec = curl_exec($curl);
 		$data_exec = json_decode($return_exec, TRUE);
		$info_error = curl_getinfo($curl);
		curl_close($curl);
		

//		echo '<br>'. $lien .'<br>';
	//	var_dump($data_exec);
$data['summary'] = $data_exec->summary;
$data['details'] = $data_exec->details;

    $titre['summary'] = array(
    	'Tran ID' => 'Tran ID',
    	'Impression?' => 'Impression?',
    	'Type' => 'Type',
    	'Date' => 'Date',
    	'Client' => 'Client',
    	'Vendu par' => 'Vendu par',
    	'Total TTC' => 'Total TTC',
    	'TVA' => 'TVA',
    	'Total HT' => 'Total HT',
    	'Moyen de paiment' => 'Moyen de paiment',
    	'Commentaires' => 'Commentaires'
    );

    $titre['details'] = array(
    	'Ligne' => 'Ligne',
    	'Famille' => 'Famille',
    	'N°Article' => 'N°Article',
    	'Article' => 'Article',
    	'Description' => 'Description',
    	'Quantité' => 'Quantité',
    	'Prix TTC' => 'PrixTTC',
    	'Remise %' => 'Remise %',
    	'Total TTC' => 'Total TTC',
    	'Total HT' => 'Total HT'
    );
 //   $transaction_subtype = 'sales';
    $transaction_type    = $this->Transaction->get_transaction_type($transaction_subtype);
 //   $start_date          = date('Y-m-d', 0);
 //   $end_date            = date('Y-m-d');
 //   $transaction_subtype = 'sales$returns';
 //   $limit               = 10;
 //   $report_data         = $this->Specific_customer->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'person_id'=>$customer_id, 'transaction_subtype'=> $transaction_subtype, 'limit'=>$limit));
	$report_data = $data_exec;
//	$report_data['summary'] = $data_exec->summary;
//    $report_data['details'] = $data_exec->details;

    // format data
    $summary_data = array();
    $details_data = array();
    $this->reports->load_data_import_sales($report_data, $summary_data, $details_data);
    $_SESSION['CSI']['HH'] = $this->reports->get_headers_import_sales($transaction_type);
	$_SESSION['CSI']['HS'] = $summary_data;
	$_SESSION['CSI']['HD'] = $details_data;

//	foreach($data_exec->summary as $cat1){foreach($cat1 as $cat2){echo $cat2.' ';}echo '<br>';}echo '<br>';foreach($data_exec->details as $cat){foreach($cat as $cat1){foreach($cat1 as $cat2){echo $cat2.'          ';}echo '<br>';}}
        $chaine = '';
		switch($chaine)
		{
						
			default:
				$_SESSION['show_dialog'] = 10;
				$this->load->view('people/manage', $data);
	//		    $this->load->view('people/manage');
			break;
		}
    }

	function test_tunnel_input()
	{
		$data = array('client' => 4 );
	    $data_json = json_encode($data);
		$postfields = $data_json;
		$_SESSION['ip_cible'] = '192.168.1.29';
		$id = 6;
		$url = 'http://' . $_SESSION['ip_cible'] .'/wrightetmathon/index.php/sales/test_tunnel_output/'.$id;
//        $header = array('Content-Type' => 'application/json', 'Authorization' => 'bearer ' . $token->access_token,);    //'Bearer ' . 
//        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 
        $header = array('Content-Type: application/json'); 
        $curl = curl_init();
        $test = curl_setopt($curl, CURLOPT_URL, $url);
        $test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $test = curl_setopt($curl, CURLOPT_POST, true);
//        $test = curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        $test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $test = curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        $return_exec = curl_exec($curl);
        $data_exec = json_decode($return_exec);
        $info_error = curl_getinfo($curl);
		curl_close($curl);
	//	$array = array('azerty' => 5);
	//	$return_exec = serialize($array);
		echo $return_exec;
		return $data_exec;

	}

	function test_input_symfony()
	{
//		$this->load->library('../controller/api');
    	$data = array('client' => 4 );
    	$data_json = json_encode($data);
    	$postfields = $data_json;
    	$_SESSION['ip_cible'] = '192.168.1.29';
		$id = 6;
//		/var/www/html/wrightetmathon/application/controllers/api.php
    	$url = 'http://' . $_SESSION['ip_cible'] .'/wrightetmathon/application/controllers/api/myAction/'; 
    //        $header = array('Content-Type' => 'application/json', 'Authorization' => 'bearer ' . $token->access_token,);    //'Bearer ' . 
    //        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 
    	$header = array('Content-Type: application/json'); 
    	$curl = curl_init();
    	$test = curl_setopt($curl, CURLOPT_URL, $url);
    	$test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    	$test = curl_setopt($curl, CURLOPT_POST, true);
    //        $test = curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
    	$test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    	$test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    	$test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    	$test = curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
    	$return_exec = curl_exec($curl);
    	$data_exec = json_decode($return_exec);
    	$info_error = curl_getinfo($curl);
    	curl_close($curl);
		echo $return_exec;
		
    	//return $data_exec;
	}

	function test_apis()
	{
		$get = 'first_name=patric&email=p';
		$url_get = urlencode($get);
		$_SESSION['ip_cible'] = '192.168.1.29';
//		$_SESSION['ip_cible'] = '88.187.90.233';
		
		$url = 'http://' . $_SESSION['ip_cible'] .'/wrightetmathon/index.php/apis/customers'.'/'. $url_get;
        $header = array('Content-Type: application/json'); 
        $curl = curl_init();
        $test = curl_setopt($curl, CURLOPT_URL, $url);
        $test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $test = curl_setopt($curl, CURLOPT_POST, false);
        $test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//        $test = curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        $return_exec = curl_exec($curl);
        $data_exec = json_decode($return_exec);
        $info_error = curl_getinfo($curl);
		curl_close($curl);
		echo $return_exec;
		return $data_exec;
	}

	function test_tunnel_output($id)
	{
/*
Réponse HTTP
<version du protocole utilisé> <code status> <equivalent textuel du code status><CRLF>
<entêtes (headers) une entete par ligne><CRLF>
<CRLF>
<body>
//*/



/*
$curlopts = [
	CURLOPT_URL => $url,
	CURLOPT_TCP_NODELAY => true,
	CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
	CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_MAXREDIRS => 0 < $options['max_redirects'] ? $options['max_redirects'] : 0,
	CURLOPT_COOKIEFILE => '', // Keep track of cookies during redirects
	CURLOPT_TIMEOUT => 0,
	CURLOPT_PROXY => $options['proxy'],
	CURLOPT_NOPROXY => $options['no_proxy'] ?? $_SERVER['no_proxy'] ?? $_SERVER['NO_PROXY'] ?? '',
	CURLOPT_SSL_VERIFYPEER => $options['verify_peer'],
	CURLOPT_SSL_VERIFYHOST => $options['verify_host'] ? 2 : 0,
	CURLOPT_CAINFO => $options['cafile'],
	CURLOPT_CAPATH => $options['capath'],
	CURLOPT_SSL_CIPHER_LIST => $options['ciphers'],
	CURLOPT_SSLCERT => $options['local_cert'],
	CURLOPT_SSLKEY => $options['local_pk'],
	CURLOPT_KEYPASSWD => $options['passphrase'],
	CURLOPT_CERTINFO => $options['capture_peer_cert_chain'],
];
//*/








		//$post = $_POST['client'];
		$result['1'] = "resultat";
		$result_json = json_encode($result);
//		return $result_json;

		$_SESSION['ip_cible'] = '192.168.1.29';
		$id = 6;
//        $header = array('Content-Type' => 'application/json', 'Authorization' => 'bearer ' . $token->access_token,);    //'Bearer ' . 
//        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 
        $header = array('Content-Type: application/json'); 
        $curl = curl_init();
//        $test = curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        $test = curl_setopt($curl, CURLOPT_HTT_VERSION, CURL_HTTP_VERSION_1_0);
		$test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		$test = curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		
		$test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		$test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		$test = curl_setopt($curl, CURLOPT_CRLT, true);
	//	$response = curl_exec($curl);




		$client = curl_init();  
		curl_setopt($client, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($client, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($client, CURLOPT_HEADER, 1);
		$response = curl_exec($client);
		//var_dump($response);
	//	return $response;

		$r = "HTTP/1.1 200 OK\r\nDate:Tue, 16 Jun 2020 11:18:38 GMT\r\nContent-Type: application/json\r\n\r\n".$result_json;
		//return $r;
		header("HTTP/1.1 200 OK");
		header("Content-Type: application/json");
		echo $result_json;
		return $result_json;
	}



	function fonction_tampon_pour_ajout_vs_exceptionnel()
	{
		$this->load->library("../controllers/Vapeself");

		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();

		/*
		$data_MajCatego['VotreId'] = '1';
		$data_MajCatego['Nom'] = 'ELiquide';
		$data_MajCatego['NomImage'] = 'picto1.jpg';
		$data_MajCatego['Type'] = 1;
		//*/
		/*
		$data_MajCatego['VotreId'] = '2';
		$data_MajCatego['Nom'] = 'Resistance';
		$data_MajCatego['NomImage'] = 'picto2.jpg';
		$data_MajCatego['Type'] = 2;
		//*/
		/*
		$data_MajCatego['VotreId'] = '3';
		$data_MajCatego['Nom'] = 'Kit et Batterie';
		$data_MajCatego['NomImage'] = 'picto3.jpg';
		$data_MajCatego['Type'] = 2;
		//*/
		/*
		$data_MajCatego['VotreId'] = '4';
		$data_MajCatego['Nom'] = 'Clearomiseur';
		$data_MajCatego['NomImage'] = 'picto4.jpg';
		$data_MajCatego['Type'] = 2;
		//*/
		/*
		$data_MajCatego['VotreId'] = '5';
		$data_MajCatego['Nom'] = 'Verre';
		$data_MajCatego['NomImage'] = 'picto5.jpg';
		$data_MajCatego['Type'] = 2;
		//*/


/*
        $data_MajCatego['VotreId'] = '6';
		$data_MajCatego['Nom'] = 'Nicodose';
		$data_MajCatego['NomImage'] = 'picto6.jpg';
		$data_MajCatego['Type'] = 1;
		
//*/

/*
$data_MajCatego['VotreId'] = '7';
$data_MajCatego['Nom'] = 'Classique';
$data_MajCatego['NomImage'] = 'picto7.jpg';
$data_MajCatego['Type'] = 1;

$data_MajCatego['VotreId'] = '8';
$data_MajCatego['Nom'] = 'Fraicheur';
$data_MajCatego['NomImage'] = 'picto8.jpg';
$data_MajCatego['Type'] = 1;

$data_MajCatego['VotreId'] = '9';
$data_MajCatego['Nom'] = 'Fruité';
$data_MajCatego['NomImage'] = 'picto9.jpg';
$data_MajCatego['Type'] = 1;

$data_MajCatego['VotreId'] = '10';
$data_MajCatego['Nom'] = 'Equilibré';
$data_MajCatego['NomImage'] = 'picto10.jpg';
$data_MajCatego['Type'] = 1;

$data_MajCatego['VotreId'] = '11';
$data_MajCatego['Nom'] = 'Gourmand';
$data_MajCatego['NomImage'] = 'picto11.jpg';
$data_MajCatego['Type'] = 1;
//*/
/*
$data_MajCatego['VotreId'] = '12';
$data_MajCatego['Nom'] = 'Cocktail';
$data_MajCatego['NomImage'] = 'picto12.jpg';
$data_MajCatego['Type'] = 1;
//*/

$reponse_return_message = $this->vapeself->post_MajProduit($token, $data_MajCatego);

		$reponse_return_message = $this->vapeself->post_MajCatego($token, $data_MajCatego);

		echo $reponse_return_message;
	} 

	function refrech_data_sales_distributeur()
	{
		//chargement de la class vapeself 
//    	$this->load->library("../controllers/Vapeself");
		
		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();

		//récupération de toutes les nouvelles ventes et stockage dans une variable session 
		$_SESSION['ventes_VS_json'] = $this->vapeself->get_GetVentes($token);

		$file = '/home/wrightetmathon/tmp/vs_distributeur/vs_ventes.txt'; ///var/www/html/wrightetmathon/vs_ventes.txt'; ///tmp/vs_distributeur/vs_ventes.txt';
		file_put_contents($file, $_SESSION['ventes_VS_json'], FILE_APPEND);

		if(strlen($_SESSION['ventes_VS_json']) < 10)
		{
			$_SESSION['ventes_VS_maj'] = 0;
		}
		if(strlen($_SESSION['ventes_VS_json']) > 10)
		{
			$_SESSION['ventes_VS_maj'] = 1;
		}

		unset($_SESSION['vs_new_sales_indicator']);

		switch($_SESSION['ventes_VS_maj'])
		{
			case 0:
				//pas de nouvelle ventes
				$_SESSION['vs_new_sales_indicator'] = 1;
			break;
			
			case 1:
				//insertion des nouvelles ventes
				$_SESSION['ventes_VS'] = json_decode($_SESSION['ventes_VS_json']);

				//appel de la fonction qui crée un tableau avec toutes les informations des ventes et insert les infos dans la table ospos_sales_distributeur
				$this->vapeself->add_ventes_into_ospos_vs_sales();

				$_SESSION['ventes_VS_json_old'] = $_SESSION['ventes_VS_json'];
				$_SESSION['ventes_VS_json'] = '[]';
			break;

			default:
            break;
		}
		unset($_SESSION['ventes_VS_maj']);
	}
	
	function refrech_data_credit_client_distributeur()
	{
		//chargement de la class vapeself 
//		$this->load->library("../controllers/Vapeself");
		
		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();

		//récupération de toutes les nouvelles ventes et stockage dans une variable session
		$_SESSION['credit_VS_json'] = $this->vapeself->get_Credit_GetFromMachine($token);
		
		$file = '/home/wrightetmathon/tmp/vs_distributeur/vs_credit.txt'; ///var/www/html/wrightetmathon/vs_credit.txt'; ///tmp/vs_distributeur/vs_credit.txt';
		file_put_contents($file, $_SESSION['credit_VS_json'], FILE_APPEND);

		if(strlen($_SESSION['credit_VS_json']) < 10)
		{
			$_SESSION['credit_VS_maj'] = 0;
		}
		if(strlen($_SESSION['credit_VS_json']) > 10)
		{
			$_SESSION['credit_VS_maj'] = 1;
		}

		switch($_SESSION['credit_VS_maj'])
		{
			case 0:
				//pas de nouvelle ventes
			break;
			
			case 1:
				//insertion des nouvelles ventes
				$_SESSION['credit_VS'] = json_decode($_SESSION['credit_VS_json']);

				//appel de la fonction qui crée un tableau avec toutes les informations des ventes et insert les infos dans la table ospos_sales_distributeur
				$this->vapeself->add_credit_into_ospos_vs_credit();

				$_SESSION['credit_VS_json_old'] = $_SESSION['credit_VS_json'];
				$_SESSION['credit_VS_json'] = '[]';
			break;

			default:
            break;
		}
		unset($_SESSION['credit_VS_maj']);
	}

	//synchronisation entre le distributeur et le POS
	function synchronisation_vs()
	{
        //Chargement de la librairie Customers
//		$this->load->library("../controllers/Customers");
		$mail_config =	array	(
			'protocol'							=>	'smtp',
			'smtp_host' 						=>	'ssl://mail.sonrisa-smile.com',
			'smtp_port' 						=>	'465',
			'smtp_user' 						=> 	$this->config->item('POemail'),
			'smtp_pass' 						=> 	$this->config->item('POemailpwd'),
			//'smtp_user' 						=>	'envoie-commande@sonrisa-smile.com',
			//'smtp_pass' 						=>	'J~?mbk+HhJ)W',
			'mailtype'  						=>	'html',
			'starttls'  						=>	FALSE,
			'wordwrap'							=>	TRUE,    // Dans le cas où nos lignes comportent plus de 70 caractères, nous les coupons en utilisant wordwrap() $message = wordwrap($message, 70, "\r\n");
			'smtp_timeout'						=>	60,
			'newline'   						=>	"\r\n"
			);
			
		//Chargement
		$this->load->library('email', $mail_config);
		$this->load->library("../controllers/Vapeself");

		//récupération des ventes et des crédits 
		//stockage dans les tables ospos_vs_sales et ospos_vs_credit
		$this->refrech_data_sales_distributeur();
		$this->refrech_data_credit_client_distributeur();

		//integration des ventes
		$this->integration_vs_sales();

		$_SESSION['error_code'] = '07390';
		//$_SESSION['show_dialog'] = 0;
		switch($_SESSION['vs_sales_compteur'])
		{
			case 0:
				$_SESSION['substitution_parms'][0] = '0 vente à intégrer';
			break;

			case 1:
			    $_SESSION['substitution_parms'][0] = '1 vente à intégrer';
			break;

			default:
			    $_SESSION['substitution_parms'][0] = $_SESSION['vs_sales_compteur'] . ' ventes ont été intégrées';
		}
		unset($_SESSION['show_dialog']);
		redirect("sales");
	}


	//////////////////////////////////////////////////////////////
	//fonctions pour l'intégration des ventes
	function integration_vs_sales()
	{
		//suppresion des variables à mettre à jour
		unset($_SESSION['probleme_price']);
        unset($_SESSION['vs_sales_compteur']);
		$_SESSION['vs_sales_compteur'] = 0;
		//chargement du model vapeself 
//		$this->load->model("Vapeself_model");		
    
        //récuperation de toutes les nouvelles ventes
		$vs_all_data = $this->Vapeself_model->get_vs_all_new_sale();

        //employé vapeself
//		$employee = 'vapeself';
		if($this->config->item('distributeur_vapeself_code') == '0')
		{
			redirect("reports");
		}
		$data_employee = $this->Employee->get_info($this->config->item('distributeur_vapeself_code'));
//		$employee_id = '1488';
		$employee = $data_employee->first_name;
		$employee_id = $data_employee->person_id;

		//chargement des paramètres pour la transaction
		//load parameters for transaction
		$mode = 'sales';
		$transaction_code = $this->Transaction->get_transaction_code($mode);
		$transaction_title = $this->lang->line('reports_'.$mode);
		$transaction_update_stock = $this->Transaction->get_transaction_updatestock($mode);
		$transaction_time = date('d/m/Y H:i:s');
		

		//boucle qui parcours les lignes de ospos_vs_sales 1 par 1
		foreach($vs_all_data as $line => $data_line)
		{
			unset($kit_indicator);
			$item_unit_price_ttc = floatval($vs_all_data[$line]['totalttc']);
			$tax_rate = $this->config->item('default_tax_1_rate');
			$tax_rate = (100 + $tax_rate) / 100;
			$item_unit_price_ht = round($item_unit_price_ttc / $tax_rate, 2);
			$vs_tax_line = $item_unit_price_ttc - $item_unit_price_ht;

			$_SESSION['vs_sales_compteur'] += 1 ;

			/*
			//Si les prix ne cohinside pas
			if($item_unit_price_ttc != $data_item_pricelist->unit_price_with_tax)
			{
				$_SESSION['error_code'] = '07370';
				redirect("sales");
			}//*/

			unset($data_sale_line);
			$data_sale_line = $this->integration_vs_sale_total(intval($vs_all_data[$line]['vs_sale_id']));
			$customer_id = intval($vs_all_data[$line]['vs_client_sales']);

	//		$payment = $vs_all_data[$line][''];
//			$payment = $this->Paymethod->get_info();
	//		$payment_types = 'CB-carte_fidelite';

			switch($vs_all_data[$line]['modereglement'])
			{
				case 'CB':    //VSCB
				$payment_types = 'Carte de Crédit';
				break;
				
				case 'CP':    //VSCP
				$payment_types = 'Carte Personnelle';
				break;
               
				default:
				break;
			}
			$total_cost = 0;
			foreach(json_decode($vs_all_data[$line]['liste']) as $key_0 => $liste_item_0)
			{
				//ajouter les kits
				$data_item = $this->Item->get_info($liste_item_0->VotreID);
				if($data_item->DynamicKit == "Y")
				{    //VotreID    Quantite
					$kit_indicator = 1;
					$item_kit_items = $this->Item_kit->get_item_kit_items_with_item_cost($liste_item_0->VotreID);
                    $total_cost = 0;
					foreach($item_kit_items as $by_item => $item)
					{
						$total_cost += floatval($item['supplier_cost_price']) * intval($liste_item_0->Quantite) * intval($item['quantity']);
					}
				}
			}

            //insert in ospos_sales
			$sales_data = array (
				'sale_time'                                 =>  $vs_all_data[$line]['datevente'],
				'customer_id'								=>	$customer_id,
				'employee_id'								=>	$employee_id,
				'payment_type'								=>	$payment_types . ': ' . $vs_all_data[$line]['totalttc'] . '</br>',
				'comment'									=>	$transaction_title,
				'mode'										=>	$mode,
				'overall_discount_percentage'				=>	0,
				'overall_discount_amount'					=>	0,
				'subtotal_before_discount'					=>	$item_unit_price_ht,
				'subtotal_discount_percentage_amount'		=>	0,
				'subtotal_discount_amount_amount'			=>	0,
				'subtotal_after_discount'					=>	$item_unit_price_ht,
				'overall_tax'								=>	$vs_tax_line,
				'overall_total'								=>	$vs_all_data[$line]['totalttc'],
				'overall_tax_percentage'					=>	$this->config->item('default_tax_1_rate'),
				'overall_tax_name'							=>	$this->config->item('default_tax_1_name'),
				'overall_cost'								=>	$data_sale_line['line_cost_HT'] + $total_cost,
				'overall_profit'							=>	$item_unit_price_ht - ($data_sale_line['line_cost_HT']+ $total_cost),
				'amount_change'								=>	0.0,
				'branch_code'								=>	$this->config->item('branch_code')
				);
            // save header to DB
            $sale_id														=	$this->Sale->save_sales_header($sales_data);
            
            // test for correct database entry - something went wrong if trans id = -1
            if ($sale_id == ('-1'))
            {
                $_SESSION['error_code']										=	'05830';
                $this														->	_reload();
                return;
			}

			$data_client = $this->Customer->get_info($customer_id);
    
		    // update fidelity points if client fidelity flag turned on
		    if ($data_client->fidelity_flag == 'Y' )
		    {
				// accumulate fidelity points for this sale
		    	$new_points = $data_client->fidelity_points + floor(floatval($vs_all_data[$line]['totalttc'])/ $this->config->item('fidelity_rule'));
                
		    	// test number of points is not higher than limit, if so set to limit
		    	$limit_points = floor($this->config->item('fidelity_maximum') / $this->config->item('fidelity_value'));
		    	if ($new_points > $limit_points)
		    	{
		    		$new_points = $limit_points;
		    	}
			}
			else
			{
				$new_points = 0;
			}

			$vs_sale_customer_employee = array(
				'customer_id' => $customer_id,
				'employee_id' => intval($employee_id),
				'sales_ht' => $item_unit_price_ht,
				'sales_number_of' => $data_sale_line['quantity'],
				'fidelity_points' => $new_points //data_client->fidelity_points + $new_points 
			);
			$this->integration_vs_sale_customer_and_employee($vs_sale_customer_employee);

			$sales_payment_data = array(
	    			'sale_id' => $sale_id,
	    			'payment_method_code' => 'VS' . $vs_all_data[$line]['modereglement'],    //CB[Carte Bancaire] CP[Carte Payment]
					'payment_type' => 'VS' . $payment_types,    //Carte Personnelle    Carte de Crédit
	    			'payment_amount' => $vs_all_data[$line]['totalttc'],
	    			'branch_code' => $this->config->item('branch_code')
				);
         	// save the payment
         	$this														->	Sale->save_sales_payment($sales_payment_data);

			$price = array();
			$price['vs_sale_id'] = $vs_all_data[$line]['vs_sale_id'];
			$price['sale_id'] = $sale_id;
			$price['real_price_pay'] = $vs_all_data[$line]['totalttc'];
			$price['calcul_price_sales_items'] = 0;


			$liste_json = $vs_all_data[$line]['liste'];

			$liste = json_decode($liste_json);
            
			foreach($liste as $key => $liste_item)
			{
				//ajouter les kits
				$data_item = $this->Item->get_info($liste[$key]->VotreID);
				if($data_item->DynamicKit == "Y")
				{    //VotreID    Quantite
					$kit_indicator = 1;
					$item_kit_items = $this->Item_kit->get_item_kit_items($liste[$key]->VotreID);
                    
					foreach($item_kit_items as $by_item => $item)
					{
						$item_kit_items[$by_item]['quantity'] = intval($item_kit_items[$by_item]['quantity']) * intval($liste[$key]->Quantite);
					}
				}
			}
			foreach($item_kit_items as $by_item => $item)
			{
				$key += 1; 
				$liste[$key]->VotreID = $item['item_id'];
				$liste[$key]->Quantite = intval($item['quantity']);
				$liste[$key]->kit_indicator = 1;
			}
            
            						
			$line_line = 0;

			//boucle qui parcours les articles vendu sur la même vente
			foreach($liste as $line_item => $item )
			{
				$line_line += 1;
				//chargement des données ...
				//... pour item
				$data_item_tax = $this->Item_taxes->get_info($liste[$line_item]->VotreID);    //array
				$data_item = $this->Item->get_info($liste[$line_item]->VotreID);    //stclass
				$data_item_pricelist = $this->Item->get_items_pricelists_item_id($liste[$line_item]->VotreID);    //stclass
                $data_item_supplier = $this->Item->get_supplier_id($liste[$line_item]->VotreID);    //array
				$data_category = $this->Category->get_info($data_item->category_id);

				$price['items_id'][] = intval($liste[$line_item]->VotreID);
				$price['libelle'][] = $data_item->name;

	//			integration_vs_sales_inventory($vs_all_data[$line]['vs_sale_id']);

				//... pour customers

				//insert in ospos_sales_items
			    $sales_item_data = array(
			    	'sale_id'						=>	$sale_id,
			    	'item_id'						=>	$liste[$line_item]->VotreID,
			    	'line_category_id'				=>	$data_item->category_id,
			    	'line_category'					=>	$data_category->category_name,
			    	'line_item_number'				=>	$data_item->item_number,
			    	'line_name'						=>	$data_item->name,
			    	'description'					=>	$data_item->description,
			    	'serialnumber'					=>	NULL,
			    	'line'							=>	$line_line,
			    	'quantity_purchased'			=>	$liste[$line_item]->Quantite,
			    	'item_cost_price' 				=>	floatval($data_item_supplier[0]['supplier_cost_price']),
			    	'item_unit_price'				=>	floatval($data_item_pricelist->unit_price),
			    	'discount_percent'				=>	0,
			    	'line_sales_before_discount'	=>	$data_item_pricelist->unit_price * $liste[$line_item]->Quantite,
			    	'line_discount'					=>	0,
			    	'line_sales_after_discount'		=>	$data_item_pricelist->unit_price * intval($liste[$line_item]->Quantite),
			    	'line_tax'						=>	($data_item_pricelist->unit_price_with_tax * intval($liste[$line_item]->Quantite)) - ($data_item_pricelist->unit_price * intval($liste[$line_item]->Quantite)),
			    	'line_sales'					=>	$data_item_pricelist->unit_price_with_tax * intval($liste[$line_item]->Quantite),
			    	'line_cost'						=>	floatval($data_item_supplier[0]['supplier_cost_price']) * intval($liste[$line_item]->Quantite),
			    	'line_profit'					=>	($data_item_pricelist->unit_price * intval($liste[$line_item]->Quantite)) - ($data_item_supplier[0]['supplier_cost_price'] * intval($liste[$line_item]->Quantite)),
			    	'line_tax_percentage'			=>	$this->config->item('default_tax_1_rate'),
			    	'line_tax_name'					=>	$this->config->item('default_tax_1_name'),
			    	'line_giftcard_number'			=>	0,
			    	'branch_code'					=>	$this->config->item('branch_code')
			    	);
	
					if(isset($item->kit_indicator) && ($item->kit_indicator == 1))
					{
						$sales_item_data['line_sales_before_discount'] = 0;
						$sales_item_data['line_sales_after_discount'] = 0;
						$sales_item_data['line_sales_after_discount'] = 0;
						$sales_item_data['line_tax'] = 0;
						$sales_item_data['line_sales'] = 0;
						$sales_item_data['line_profit'] = 0;
						$sales_item_data['item_unit_price'] = 0;
					}


                    // save this line
					$this->Sale->save_sales_item($sales_item_data);
					
					$price['calcul_price_sales_items'] += $sales_item_data['line_sales'];

				$vs_sale_item_category = array(
					'item_id' => intval($liste[$line_item]->VotreID),
					'sales_ht' => $sales_item_data['line_sales_after_discount'],
					'category_id' => intval($data_item->category_id),
                    'category_sales_value' => $sales_item_data['line_sales_after_discount'],
                    'category_sales_qty' => $sales_item_data['quantity_purchased']
				);
				$this->integration_vs_sale_item_and_category($vs_sale_item_category);
				
				$customer_id = intval($vs_all_data[$line]['vs_client_sales']);

			    // Set price
		        $prices = $this->get_price($liste[$line_item]->VotreID, $data_client->pricelist_id);
		        $line_priceTTC = $prices['price_with_tax'];
		        $line_priceHT = $prices['price_no_tax'];

				//récuperation de la quantité du produit vendu
				$line_quantity = $liste[$line_item]->Quantite;
				$quantity_stock = $data_item->quantity - $line_quantity;

				if ($quantity_stock < 0)
				{
					if($data_item->DynamicKit != 'Y')
					{
					    $this->Item->value_delete_item_id(intval($liste[$line_item]->VotreID));
					    $valuation_data = array(
					    			'value_item_id'		=>	intval($liste[$line_item]->VotreID),
					    			'value_cost_price'	=>	floatval($data_item_supplier[0]['supplier_cost_price']),
					    			'value_qty'			=>	$quantity_stock,
					    			'value_trans_id'	=>	0,
					    			'branch_code'		=>	$this->config->item('branch_code')
					    			);
					    $this->Item->value_write($valuation_data);
				    }
				}
				else
				{
					if($data_item->DynamicKit != 'Y')
					{
					    $value_remaining_qty = $line_quantity;
					    $value_trans_id = $sale_id;
					    $this->Item->value_update($value_remaining_qty, intval($liste[$line_item]->VotreID), $data_item_supplier['supplier_cost_price'], $value_trans_id);
					}
				}

				if($transaction_update_stock == 'Y')
				{
					//update quantity item
					$vs_item_data['item_id'] = intval($liste[$line_item]->VotreID);
					$vs_item_data['quantity'] = intval($data_item->quantity) - intval($line_quantity);
					$vs_item_data['rolling_inventory_indicator'] = 0;
					$this->update_item_quantity($vs_item_data);
				}
				
				//update quantity item
				$vs_sale_id_data['item_id'] = intval($liste[$line_item]->VotreID);
				$vs_sale_id_data['employee_id'] = intval($employee_id);
				$vs_sale_id_data['comment'] = $transaction_code . $sale_id . ' - ' . $transaction_title;  // 'SALE-' . $sale_id . ' - Facture'
				$vs_sale_id_data['stock_before'] = intval($data_item->quantity);
				$vs_sale_id_data['quantity_sale'] = -intval($line_quantity);
				$vs_sale_id_data['stock_after'] = intval($data_item->quantity - $line_quantity);
				$this->integration_vs_sales_inventory($vs_sale_id_data);

				$sales_item_tax_data = array(
					'sale_id' 		=> $sale_id,
					'item_id' 		=> intval($liste[$line_item]->VotreID),
					'line'      	=> $line_line,
					'name'			=> $this->config->item('default_tax_1_name'),
					'percent' 		=> $this->config->item('default_tax_1_rate'),
					'branch_code'	=> $this->config->item('branch_code')
					);

                // save this tax line
                $this->Sale->save_sales_item_tax($sales_item_tax_data);
		    }
		//	$customer_id = intval($vs_all_data[$line]['vs_client_sales']);		
			$this->integration_vs_sales_valide(intval($vs_all_data[$line]['vs_sale_id']));

//			$this->test_send_mail_email($price);
			//envoie d'un mail si les prix du distributeur et du POS sont différents
			if(intval($price['calcul_price_sales_items']) != intval($price['real_price_pay']))
			{
				//variable pour relever un problème
				$_SESSION['probleme_price'] = 1;

				//envoie d'un mail d'alerte
				$mail_config = array(
					'protocol'		=>	'smtp',
					'smtp_host' 	=>	'ssl://mail.sonrisa-smile.com',
					'smtp_port' 	=>	'465',
					'smtp_user' 	=> 	$this->config->item('POemail'),
					'smtp_pass' 	=> 	$this->config->item('POemailpwd'),
					'mailtype'  	=>	'html',
					'starttls'  	=>	FALSE,
					'wordwrap'		=>	TRUE,    // Dans le cas où nos lignes comportent plus de 70 caractères, nous les coupons en utilisant wordwrap() $message = wordwrap($message, 70, "\r\n");
					'smtp_timeout'	=>	60,
					'newline'   	=>	"\r\n"
					);
					
					//Chargement
					$this->load->library('email', $mail_config);
					$this->email->from($this->config->item('email'), $this->config->item('company'));    //Fonction avec l'email des Sorinières -> changement pour ne pas causer de problèmes
		
					//Envoie du mail à la personne consernée
					//$this->email->to('guillaume@yesstore.fr');
					$this->email->cc('david@syesstore.fr');

					$this->email->subject('Problème de cohérence entre les prix dans le POS et dans le distributeur vapeself');
					$message_mail = '';

					$message_mail = $message_mail . '<br>';
					$message_mail = $message_mail . 'Problème de cohérence entre les prix dans le POS et dans le distributeur';
					$message_mail = $message_mail . '<br>';
					$message_mail = $message_mail . '<table>';
					$message_mail = $message_mail . '<tr>';
					$message_mail = $message_mail . '<td>';
					$message_mail = $message_mail . 'Prènom client : ';
					$message_mail = $message_mail . '</td>';
					$message_mail = $message_mail . '<td>';
					$message_mail = $message_mail . $data_client->first_name;
					$message_mail = $message_mail . '</td>';
					$message_mail = $message_mail . '</tr>';
					$message_mail = $message_mail . '<tr>';
					$message_mail = $message_mail . '<td>';
					$message_mail = $message_mail . 'Nom client : ';
					$message_mail = $message_mail . '</td>';
					$message_mail = $message_mail . '<td>';
					$message_mail = $message_mail . $data_client->last_name;
					$message_mail = $message_mail . '</td>';
					$message_mail = $message_mail . '</tr>';
					$message_mail = $message_mail . '</table>';
					$message_mail = $message_mail . '<br>';
					$message_mail = $message_mail . '<br>';
					
					

					foreach($price['items_id'] as $key_num => $item_id)
					{

						$message_mail = $message_mail . '<table>';
						$message_mail = $message_mail . '<tr>';						
						$message_mail = $message_mail . '<td>';
						$message_mail = $message_mail . 'Libellé : ';
						$message_mail = $message_mail . '</td>';
						$message_mail = $message_mail . '<td>';
						$message_mail = $message_mail . $price['libelle'][$key_num];
						$message_mail = $message_mail . '</td>';
						$message_mail = $message_mail . '</tr>';						
						$message_mail = $message_mail . '<tr>';
						$message_mail = $message_mail . '<td>';
						$message_mail = $message_mail . 'Sale_id : ';
						$message_mail = $message_mail . '</td>';
						$message_mail = $message_mail . '<td>';
						$message_mail = $message_mail . $price['sale_id'];
						$message_mail = $message_mail . '</td>';
						$message_mail = $message_mail . '</tr>';
						$message_mail = $message_mail . '<tr>';
						$message_mail = $message_mail . '<td>';
						$message_mail = $message_mail . 'Vs_sale_id : ';
						$message_mail = $message_mail . '</td>';
						$message_mail = $message_mail . '<td>';
						$message_mail = $message_mail . $price['vs_sale_id'];
						$message_mail = $message_mail . '</td>';
						$message_mail = $message_mail . '</tr>';
						$message_mail = $message_mail . '<tr>';
						$message_mail = $message_mail . '<td>';
						$message_mail = $message_mail . 'Item_id : ';
						$message_mail = $message_mail . '</td>';
						$message_mail = $message_mail . '<td>';
						$message_mail = $message_mail . $item_id;
						$message_mail = $message_mail . '</td>';
						$message_mail = $message_mail . '</tr>';

						$message_mail = $message_mail . '<br>';
						$message_mail = $message_mail . '<br>';
						$message_mail = $message_mail . '<br>';
					}
					$message_mail = $message_mail . '</table>';
					$message_mail = $message_mail . '<br>';
					$message_mail = $message_mail . '<br>';
					$message_mail = $message_mail . '<table>';
					$message_mail = $message_mail . '<tr>';
					$message_mail = $message_mail . '<td>';
					$message_mail = $message_mail . 'Montant réellement payé: ';
					$message_mail = $message_mail . '</td>';
					$message_mail = $message_mail . '<td>';
					$message_mail = $message_mail . $price['real_price_pay'];
					$message_mail = $message_mail . '</td>';
					$message_mail = $message_mail . '</tr>';

					$message_mail = $message_mail . '<tr>';
					$message_mail = $message_mail . '<td>';
					$message_mail = $message_mail . 'Montant POS: ';
					$message_mail = $message_mail . '</td>';
					$message_mail = $message_mail . '<td>';
					$message_mail = $message_mail . $price['calcul_price_sales_items'];
					$message_mail = $message_mail . '</td>';
					$message_mail = $message_mail . '</tr>';
					
					$message_mail = $message_mail . '</table>';


					$message_mail = $message_mail . '</body>';
					$message_mail = $message_mail . '</html>';
					$this->email->message($message_mail);
				    $this->email->send();
			}
		}
		if(isset($_SESSION['probleme_price']))
		{
			if($_SESSION['probleme_price'] == 1)
			{
				$_SESSION['error_message'] = '07380'; 
			}
		}
		if(!isset($_SESSION['probleme_price']))
        {
			$_SESSION['success_message'] = '07390';
			$success = '07390';
		}

		//redirect("sales");
//		return $price;
	}

    function integration_vs_sale_customer_and_employee($vs_sale_customer_employee)
    {
		$data_client = $this->Customer->get_info($vs_sale_customer_employee['customer_id']);
		//update customer counts
        $update_custumer_counts = array(
			'person_id' => $vs_sale_customer_employee['customer_id'],
            'sales_ht' => floatval($data_client->sales_ht) + $vs_sale_customer_employee['sales_ht'],
			'sales_number_of' => intval($data_client->sales_number_of) + 1,    //$vs_sale_customer_employee['sales_number_of'],
			'fidelity_points' => $vs_sale_customer_employee['fidelity_points']
		);
		$this->Customer->save_counts_vs_sale($update_custumer_counts);

		$data_employee = $this->Employee->get_info($vs_sale_customer_employee['employee_id']);
		//update employee counts
        $update_employee_counts = array(
			'person_id' => $vs_sale_customer_employee['employee_id'],
            'sales_ht' => floatval($data_employee->sales_ht) + $vs_sale_customer_employee['sales_ht'],
			'sales_number_of' => intval($data_employee->sales_number_of) + 1,    //$vs_sale_customer_employee['sales_number_of']
		);
		$this->Employee->save_counts_vs_sale($update_employee_counts);

	}

	function integration_vs_sale_item_and_category($vs_sale_item_category)
	{
        $data_item = $this->Item->get_info($vs_sale_item_category['item_id']);
		//update employee counts
		$update_item = array(
			'item_id' => $vs_sale_item_category['item_id'],
			'sales_ht' => floatval($data_item->sales_ht) + $vs_sale_item_category['sales_ht'],
			'sales_qty' => intval($data_item->sales_qty) + 1    //$vs_sale_item_category['sales_number_of']
		);

    	$this->Item->save_vs_sale($update_item);
	
	    $data_category = $this->Category->get_info($data_item->category_id);
    	//update category counts
		$update_category = array(
			'category_id' => $data_category->category_id,
			'category_sales_value' => $data_category->category_sales_value + $vs_sale_item_category['category_sales_value'],
			'category_sales_qty' => $data_category->category_sales_qty + $vs_sale_item_category['category_sales_qty']
		);

		$this->Category->save_vs_sale($update_category);
		
    }

	function update_item_quantity($vs_item_data)
	{
		//Update stock (quantity) in table ospos_items
		$vs_quantity_item_data = array(
			'item_id' => $vs_item_data['item_id'],
			'quantity' => $vs_item_data['quantity'],
			'rolling_inventory_indicator' => $vs_item_data['rolling_inventory_indicator']
		);
		$this->Item->save_vs_sale($vs_quantity_item_data);
	}

	function integration_vs_sales_inventory($vs_sale_id_data)
	{ 		
		//insert in ospos_inventory	
		$vs_inventory_data = array(
			'trans_items' => $vs_sale_id_data['item_id'],
			'trans_user' => $vs_sale_id_data['employee_id'],
			'trans_comment' => $vs_sale_id_data['comment'],
			'trans_stock_before' => $vs_sale_id_data['stock_before'],
			'trans_inventory' => $vs_sale_id_data['quantity_sale'],
			'trans_stock_after' => $vs_sale_id_data['stock_after'],
			'branch_code' => $this->config->item('branch_code')
		);
		$this->Inventory->insert($vs_inventory_data); 
	}

	function integration_vs_sale_total($vs_sale_id)
	{
		unset($vs_sale_id_line);
		$vs_sale_id_line = $this->Vapeself_model->get_vs_sale_id($vs_sale_id);
		$vs_sale_total['line_cost_HT'] = 0;
		$vs_sale_total['quantity'] = 0;
		$liste_line_json = $vs_sale_id_line[0]['liste'];
        $liste_line = json_decode($liste_line_json);

		foreach($liste_line as $line_item => $item )
		{
			//chargement des données ...
			//... pour item
			$data_item_supplier = $this->Item->get_supplier_id($liste_line[$line_item]->VotreID);
	        $vs_sale_total['quantity'] += $liste_line[$line_item]->Quantite; 
		    $vs_sale_total['line_cost_HT'] += round($data_item_supplier[0]['supplier_cost_price'] * $liste_line[$line_item]->Quantite, 2);
	    }
		return $vs_sale_total;
	}

	function integration_vs_sales_valide($vs_sale_id)  //arg: vs_sale_id
	{
		//update validate in ospos_vs_sales
		//chargement du model vapeself 
		$this->load->model("Vapeself_model");

		$this->Vapeself_model->update_validate_sale_ok($vs_sale_id);
	}
	//////////////////////////////////////////////////////////////

	function reprint_select($sale_id)
	{
		$_SESSION['reprint'] = 1;

		// Load sale info for the reprint modal
		$sale_info = $this->Sale->get_info($sale_id)->row();
		$_SESSION['reprint_sale_info'] = $sale_info;
		$_SESSION['reprint_sale_items'] = $this->Sale->get_sale_items($sale_id)->result_array();
		$_SESSION['reprint_sale_payments'] = $this->Sale->get_sale_payments($sale_id)->result_array();
		// Get customer name
		$_SESSION['reprint_customer_name'] = '';
		if ($sale_info && !empty($sale_info->customer_id))
		{
			$this->db->select('first_name, last_name');
			$this->db->from('people');
			$this->db->where('person_id', $sale_info->customer_id);
			$cust = $this->db->get()->row();
			if ($cust) $_SESSION['reprint_customer_name'] = $cust->first_name . ' ' . $cust->last_name;
		}
		// Get employee name
		$_SESSION['reprint_employee_name'] = '';
		if ($sale_info && !empty($sale_info->employee_id))
		{
			$this->db->select('first_name, last_name');
			$this->db->from('people');
			$this->db->where('person_id', $sale_info->employee_id);
			$emp = $this->db->get()->row();
			if ($emp) $_SESSION['reprint_employee_name'] = $emp->first_name . ' ' . $emp->last_name;
		}

		$tcode = $this->Transaction->get_transaction_code($sale_info->mode);
		$data['sale_id'] = $tcode . $sale_id;
		$data['sale_id_raw'] = $sale_id;

		// Lightweight page: just header + modal (no full receipt rendering)
		$this->load->view("sales/reprint_page", $data);
	}
	function copy_sale($sale_id)
	{
		// Get all items from the original sale in one query
		$invoice_items = $this->Sale->get_sale_items($sale_id)->result_array();

		if (empty($invoice_items)) {
			$this->_reload();
			return;
		}

		// Unset last line
		$this->unset_last_line();

		foreach ($invoice_items as $invoice_item) {
			$selected_item_id = $invoice_item['item_id'];

			// Get next line number
			$line = $this->next_line_number();

			// Get item info (base data: tax, offer_indicator, etc.)
			$_SESSION['CSI']['CT'][$line] = $this->Item->get_info($selected_item_id);

			// Not a credit note
			$_SESSION['CSI']['CT'][$line]->CN_line = 'N';

			// Quantity (positive for copy)
			$_SESSION['CSI']['CT'][$line]->line_quantity = $invoice_item['quantity_purchased'];

			// Prices from original invoice (before discount)
			$unit_price_HT  = (float)$invoice_item['item_unit_price'];
			$tax_pct        = (float)$invoice_item['line_tax_percentage'];
			$unit_price_TTC = round($unit_price_HT * (1 + $tax_pct / 100), 2);

			$_SESSION['CSI']['CT'][$line]->line_priceTTC = $unit_price_TTC;
			$_SESSION['CSI']['CT'][$line]->line_priceHT  = $unit_price_HT;

			// Original discount from the invoice
			$_SESSION['CSI']['CT'][$line]->line_discount = (float)$invoice_item['discount_percent'];

			// Kit defaults
			$_SESSION['CSI']['CT'][$line]->kit_item        = 'N';
			$_SESSION['CSI']['CT'][$line]->kit_option      = null;
			$_SESSION['CSI']['CT'][$line]->kit_option_type = null;
			$_SESSION['CSI']['CT'][$line]->kit_cart_line   = null;

			// Line offered
			$_SESSION['CSI']['CT'][$line]->line_offered = isset($_SESSION['CSI']['CT'][$line]->offer_indicator) ? $_SESSION['CSI']['CT'][$line]->offer_indicator : 'N';

			// Item info from invoice
			$_SESSION['CSI']['CT'][$line]->name        = $invoice_item['line_name'];
			$_SESSION['CSI']['CT'][$line]->item_number = $invoice_item['line_item_number'];
			$_SESSION['CSI']['CT'][$line]->description = $invoice_item['description'];
			$_SESSION['CSI']['CT'][$line]->category_id = $invoice_item['line_category_id'];
			$_SESSION['CSI']['CT'][$line]->category    = $invoice_item['line_category'];
			$_SESSION['CSI']['CT'][$line]->serialnumber = '';

			// Supplier cost price from item info
			$_SESSION['CSI']['CT'][$line]->supplier_cost_price = isset($_SESSION['CSI']['CT'][$line]->cost_price) ? (float)$_SESSION['CSI']['CT'][$line]->cost_price : 0;

			// Calculate line values
			$this->line_values($line);
		}

		// Mark last cart line
		if (isset($line)) {
			$_SESSION['CSI']['CT'][$line]->last_line = TRUE;
			$_SESSION['CSI']['CT'][$line]->colour    = 'yellow';
		}

		// Reload sales page
		$this->_reload();
	}

	function reprint($sale_id)
	{
		$reprint_ticket = $this->input->post('reprint_ticket');
		$reprint_mail = $this->input->post('reprint_mail');

		// Sauvegarder l'état session actuel (vente en cours)
		$backup_CSI = isset($_SESSION['CSI']) ? $_SESSION['CSI'] : null;
		$backup_TVA = isset($_SESSION['TVA']) ? $_SESSION['TVA'] : null;

		//récupération des paramètres pour la reimpression du ticket de caisse
		$transaction_info      = array();
		$transaction_info      = $this->Sale->get_info($sale_id)->row_array();


		$payments_data = $this->Sale->get_sale_payments($sale_id)->result_array();

        //get payments for sale_id
        $sale_payment = $this->Sale->get_sale_payments($sale_id);
        $result_sale_payment = $sale_payment->result_array();

        //get all payments methods
        $payment_methods												=	array();
        $payment_methods												=	$this->Sale->get_payment_methods();

        //rapprochement pour obtenir le moyen de payment de la facture sale_id
        // et mise à jour de payment_amount
        foreach($result_sale_payment as $index_1=>$value_1)
        {
        	foreach($payment_methods as $index_2=>$value_2)
        	{
        		if(($value_1['payment_method_code'])==($value_2['payment_method_code']))
        		{
        			$pmi=$value_2['payment_method_id'];
        			$_SESSION['CSI']['PD'][$pmi] = $this->Paymethod->get_info($pmi);
        			if (!$_SESSION['CSI']['PD'][$pmi]) { $_SESSION['CSI']['PD'][$pmi] = new stdClass(); }
        			$_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC=$value_1['payment_amount'];
        		}
        	}
        }

		$tcode                 = $this->Transaction->get_transaction_code($transaction_info['mode']);
		//$_SESSION['CSI']['CT'] = $this->Sale->get_sale_items($sale_id)->result_array();
		$data_items = $this->Sale->get_sale_items($sale_id)->result_array();
		foreach($data_items as $key => $line)
		{
			$_SESSION['CSI']['CT'][$key] = $this->Sale->get_sale_item($sale_id, $line['item_id'])->row();
		}

		//$_SESSION['CSI']['CT'] = get_sale_item

        $data['overall_tax']   = $this->Sale->get_overall_tax_for_sale_with_sale_id($sale_id);    //TAX
		$customer_id           = $transaction_info['customer_id'];
//		if($customer_id != -1)
//		{
//			$person_info 	   = $this->Customer->get_info($customer_id);
//			$data['customer']  = $this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
//		}

		$customer_id = $transaction_info['customer_id'];

		$_SESSION['CSI']['SHV']->customer_id = $customer_id;
		//$_SESSION['CSI']['SHV'] = $this->Customer->get_info($customer_id);
		$customer_data = $this->Customer->get_info($customer_id);
		$_SESSION['CSI']['SHV']->last_name = $customer_data->last_name;
		$_SESSION['CSI']['SHV']->first_name = $customer_data->first_name;

		$_SESSION['CSI']['SHV']->customer_formatted = $this->Common_routines->format_full_name($_SESSION['CSI']['SHV']->last_name, $_SESSION['CSI']['SHV']->first_name);
		$_SESSION['CSI']['SHV']->default_profile_flag = (isset($_SESSION['CSI']['SHV']->profile_id) && $_SESSION['CSI']['SHV']->profile_id == $this->config->item('profile_id')) ? 1:0;


		$_SESSION['CSI']['SHV']->sale_id = $sale_id;
		$_SESSION['CSI']['SHV']->transaction_time = date('d/m/Y H:i:s', strtotime($transaction_info['sale_time']));

		$this->db->select('first_name, last_name, person_id');
		$this->db->from('people');
		$this->db->where('person_id', $transaction_info['employee_id']);
		$employee = $this->db->get()->row();
		$_SESSION['CSI']['SHV']->employee_id = $employee ? $employee->person_id : $transaction_info['employee_id'];
		$_SESSION['CSI']['SHV']->employee_formatted = $employee ? $this->Common_routines->format_full_name($employee->last_name, $employee->first_name) : '';


		$_SESSION['CSI']['SHV']->header_valueAD_TTC = $transaction_info['overall_total'];
		$_SESSION['CSI']['SHV']->header_valueAD_HT = $transaction_info['subtotal_after_discount'];

		unset($_SESSION['TVA']);
		$_SESSION['TVA'] = array();
		foreach($_SESSION['CSI']['CT'] as $line => $cart_item)
		{

			$item_data = $this->Item->get_info($_SESSION['CSI']['CT'][$line]->item_id);
			if($item_data->DynamicKit == 'Y')
			{
				//$_SESSION['CSI']['CT'][$line]->off = '1';
			}
			// calculate line TTC because its not stored in the DB
			$line_TTC = round($_SESSION['CSI']['CT'][$line]->item_unit_price * ((100 + $_SESSION['CSI']['CT'][$line]->line_tax_percentage) / 100), 2);
            
			$_SESSION['CSI']['CT'][$line]->name             = $_SESSION['CSI']['CT'][$line]->description;
			$_SESSION['CSI']['CT'][$line]->item_number      = $_SESSION['CSI']['CT'][$line]->line_item_number;
			$_SESSION['CSI']['CT'][$line]->line_quantity    = $_SESSION['CSI']['CT'][$line]->quantity_purchased;
			$_SESSION['CSI']['CT'][$line]->line_priceTTC    = $line_TTC;
			$_SESSION['CSI']['CT'][$line]->line_valueAD_TTC = $_SESSION['CSI']['CT'][$line]->line_sales;
					
			//$line_tax_percentage = $_SESSION['CSI']['CT'][$line]->line_tax_percentage;
			//$_SESSION['TVA']["$line_tax_percentage"] += $_SESSION['CSI']['CT'][$line]->line_tax;
			$test_empty = array();
			$test_empty['t1'] = floatval($_SESSION['CSI']['CT'][$line]->item_unit_price);
			$test_empty['t2'] = floatval($_SESSION['CSI']['CT'][$line]->line_sales);
			$test_empty['t3'] = floatval($_SESSION['CSI']['CT'][$line]->line_tax);
			$test_empty['t4'] = floatval($_SESSION['CSI']['CT'][$line]->line_sales_before_discount);
			$test_empty['t5'] = floatval($_SESSION['CSI']['CT'][$line]->line_sales_after_discount);
			
			$t1 = empty($test_empty['t1']);
			$t2 = empty($test_empty['t2']);
			$t3 = empty($test_empty['t3']);
			$t4 = empty($test_empty['t4']);
			$t5 = empty($test_empty['t5']);
			
			if($t1 && $t2 && $t3 && $t4 && $t5)
			{
				$_SESSION['CSI']['CT'][$line]->off = '1';
			}	
		}

		//research TVA for this current sale
		$tva = $this->Sale->get_overall_tax_for_sale_with_sale_id($sale_id);
		foreach($tva as $percent => $value)
		{
			if(!empty($value))
			{
				$per = $value['line_tax_percentage'];
				$val = floatval($value['som']);
				$_SESSION['TVA']["$per"] = $val;
			}
		}

		$ph_texte = fopen("/var/www/html/wrightetmathon/ticket.txt", "w");
		$ph =$ph_texte;
		if($reprint_ticket == "on")
		{
			//création des variables SESSION pour l'impression du ticket de caisse
			
			//appel de l'impression
			$printer = $this->config->item('ticket_printer');
			$ph = fopen($printer, "w");
			// if it opens, print a ticket
			if($ph)
			{
				$_SESSION['CSI']['SHV']->ph = $ph;
				$this->load->view("sales/ticket");
			}
		}
		if($reprint_mail == "on")
		{
			
			$ph_texte = fopen("/var/www/html/wrightetmathon/ticket.txt", "w");
		    $ph =$ph_texte;
			// if it opens, print a ticket
			if($ph)
			{
				$_SESSION['CSI']['SHV']->ph = $ph;
				$this->load->view("sales/ticket");
			}
			$input['person_id'] = $_SESSION['CSI']['SHV']->customer_id;
			$data_people_with_email = $this->Person->get_info_people($input);
			if(empty($data_people_with_email) || !isset($data_people_with_email[0]['email']) || empty($data_people_with_email[0]['email']))
            {
				//email de la personne non renseigné
				$_SESSION['error_code'] = '07290';
				redirect("sales");
            }
			$request = $data_people_with_email[0];

			//construction du mail
			/*Classe de traitement des exceptions et des erreurs*/
			require_once 'application/third_party/PHPMailer/src/Exception.php';
			/*Classe-PHPMailer*/
			require_once 'application/third_party/PHPMailer/src/PHPMailer.php';
			/*Classe SMTP nécessaire pour établir la connexion avec un serveur SMTP*/
			require_once 'application/third_party/PHPMailer/src/SMTP.php';
			/*Lors de la création d’un objet PHPMailer, passez le paramètre "true" pour activer les exceptions (messages en cas d’erreur)*/
			$mail = new PHPMailer(true);
			try {
				// Paramètres du serveur SMTP
				$mail->isSMTP();   // Utiliser SMTP
				if (strpos($this->config->item('POemail'), 'sonrisa') >0)
				{                                  
					$mail->Host = 'ssl://mail.sonrisa-smile.com';        
				}
				elseif (strpos($this->config->item('POemail'), 'yesstore') >0)	
				{                                   
					$mail->Host = 'ssl://mail.yesstore.fr';         
				}
				elseif (strpos($this->config->item('POemail'), 'gmail') >0)	
				{                                   
					$mail->Host = 'smtp.gmail.com';  // Serveur SMTP (par exemple Gmail)       
				}
				
				$mail->SMTPAuth = true;                               // Authentification SMTP activée
				$mail->Username = $this->config->item('POemail');             // Votre email
				$mail->Password = $this->config->item('POemailpwd');                // Votre mot de passe
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    // Chiffrement STARTTLS
				$mail->Charset = 'utf-8' ;
				$mail->Port = 465;                                    // Port SMTP
		
				// Expéditeur et destinataire
				$mail->setFrom($this->config->item('email'), $this->config->item('company'));
				$mail->addAddress($request['email'],$request['email']); // Ajouter un destinataire
				//$mail->addReplyTo('info@example.com', 'Information'); // Réponse à un autre email
				// Copie
				//$mail->addCC('info@exemple.fr');
				// Copie cachée
				//$mail->addBCC('info@exemple.fr', 'nom');

				// Contenu de l'email
				$mail->isHTML(true);                                  // Format HTML
				$mail->Subject = 'Ticket de caisse numéro: ' . $_SESSION['numero_ticket'];

			$_SESSION['message_mail']=$_SESSION['message_mail'] . '<strong><br><center>Merci de votre confiance</center></strong><br>';
	        $_SESSION['message_mail']=$_SESSION['message_mail'] . '</p>';
            $_SESSION['message_mail']=$_SESSION['message_mail'] . '</center>';
            $_SESSION['message_mail']=$_SESSION['message_mail'] . '</table>';
            $_SESSION['message_mail']=$_SESSION['message_mail'] . '</html>';

				$mail->Body    = $_SESSION['message_mail'];
								// Envoi de l'email
				$mail->send();
				//Affichage d'un message de succés de l'envoie
				$_SESSION['error_code']										=	'07300';
			} catch (Exception $e) {
				//Affichage d'un message de succés de l'envoie
				$_SESSION['error_code']										=	'07301';
			}
		}

		// Restaurer l'état session (vente en cours)
		if ($backup_CSI !== null) { $_SESSION['CSI'] = $backup_CSI; } else { unset($_SESSION['CSI']); }
		if ($backup_TVA !== null) { $_SESSION['TVA'] = $backup_TVA; } else { unset($_SESSION['TVA']); }
		unset($_SESSION['reprint']);
		unset($_SESSION['reprint_sale_info']);
		unset($_SESSION['reprint_sale_items']);
		unset($_SESSION['reprint_sale_payments']);
		unset($_SESSION['reprint_customer_name']);
		unset($_SESSION['reprint_employee_name']);
		redirect("common_controller/common_exit");
	}

	function invoice($sale_id)
	{
		// Sale data
		$transaction_info = $this->Sale->get_info($sale_id)->row_array();
		if (empty($transaction_info))
		{
			redirect("sales");
			return;
		}

		$cart = $this->Sale->get_sale_items($sale_id)->result_array();

		// Payments with description
		$payments_raw = $this->Sale->get_sale_payments($sale_id)->result_array();
		$payment_methods = $this->Sale->get_payment_methods();
		$pm_lookup = array();
		foreach ($payment_methods as $pm)
		{
			$pm_lookup[$pm['payment_method_code']] = $pm['payment_method_description'];
		}
		foreach ($payments_raw as &$p)
		{
			if (!isset($p['payment_method_description']) || empty($p['payment_method_description']))
			{
				$p['payment_method_description'] = isset($pm_lookup[$p['payment_method_code']]) ? $pm_lookup[$p['payment_method_code']] : $p['payment_method_code'];
			}
		}
		unset($p);

		// Transaction code
		$tcode = $this->Transaction->get_transaction_code($transaction_info['mode']);

		// Company data
		$data['company']   = $this->config->item('company') ?: '';
		$data['address']   = $this->config->item('address') ?: '';
		$data['phone']     = $this->config->item('phone') ?: '';
		$data['siret']     = $this->config->item('siret') ?: '';
		$data['tva_intra'] = $this->config->item('tva') ?: '';
		$data['rcs']       = $this->config->item('rcs') ?: '';

		// Customer data
		$customer_id = $transaction_info['customer_id'];
		$data['customer'] = null;
		if (!empty($customer_id) && $customer_id != -1)
		{
			$this->db->select('first_name, last_name, email, address_1, address_2, city, zip, state');
			$this->db->from('people');
			$this->db->where('person_id', $customer_id);
			$data['customer'] = $this->db->get()->row();
		}

		// Sale identifiers
		$data['sale_id']          = $tcode . $sale_id;
		$data['sale_id_raw']      = $sale_id;
		$data['cart']             = $cart;
		$data['transaction_info'] = $transaction_info;
		$data['payments']         = $payments_raw;
		$data['transaction_time'] = date('d/m/Y', strtotime($transaction_info['sale_time']));

		$this->load->view("sales/invoice", $data);
	}

	function reprint_session($sale_id)
	{
		$reprint_ticket = $this->input->post('reprint_ticket');
		$reprint_mail = $this->input->post('reprint_mail');

		// Sauvegarder l'état session actuel (vente en cours)
		$backup_CSI = isset($_SESSION['CSI']) ? $_SESSION['CSI'] : null;
		$backup_TVA = isset($_SESSION['TVA']) ? $_SESSION['TVA'] : null;

		//récupération des paramètres pour la reimpression du ticket de caisse
		$transaction_info      = array();
		$transaction_info      = //$this->Sale->get_info($sale_id)->row_array();

		$payments_data = $this->Sale->get_sale_payments($sale_id)->result_array();
		
        //get payments for sale_id
        $sale_payment = $this->Sale->get_sale_payments($sale_id);
        $result_sale_payment = $sale_payment->result_array();
        
        //get all payments methods
        $payment_methods												=	array();
        $payment_methods												=	$this->Sale->get_payment_methods();
        
        //rapprochement pour obtenir le moyen de payment de la facture sale_id
        // et mise à jour de payment_amount
        foreach($result_sale_payment as $index_1=>$value_1)
        {
        	foreach($payment_methods as $index_2=>$value_2)
        	{
        		if(($value_1['payment_method_code'])==($value_2['payment_method_code']))
        		{
        			$pmi=$value_2['payment_method_id'];
        			$_SESSION['CSI']['PD'][$pmi] = $this->Paymethod->get_info($pmi);
        			if (!$_SESSION['CSI']['PD'][$pmi]) { $_SESSION['CSI']['PD'][$pmi] = new stdClass(); }
        			$_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC=$value_1['payment_amount'];
        		}
        	}
        }

		$tcode                 = $this->Transaction->get_transaction_code($transaction_info['mode']);
		//$_SESSION['CSI']['CT'] = $this->Sale->get_sale_items($sale_id)->result_array();
		$data_items = $this->Sale->get_sale_items($sale_id)->result_array();
		foreach($data_items as $key => $line)
		{
			$_SESSION['CSI']['CT'][$key] = $this->Sale->get_sale_item($sale_id, $line['item_id'])->row();
		}


		//$_SESSION['CSI']['CT'] = get_sale_item

        $data['overall_tax']   = $this->Sale->get_overall_tax_for_sale_with_sale_id($sale_id);    //TAX
		$customer_id           = $transaction_info['customer_id'];
//		if($customer_id != -1)
//		{
//			$person_info 	   = $this->Customer->get_info($customer_id);
//			$data['customer']  = $this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
//		}

		$customer_id = $transaction_info['customer_id'];

		$_SESSION['CSI']['SHV']->customer_id = $customer_id;
		//$_SESSION['CSI']['SHV'] = $this->Customer->get_info($customer_id);
		$customer_data = $this->Customer->get_info($customer_id);
		$_SESSION['CSI']['SHV']->last_name = $customer_data->last_name;
		$_SESSION['CSI']['SHV']->first_name = $customer_data->first_name;

		$_SESSION['CSI']['SHV']->customer_formatted = $this->Common_routines->format_full_name($_SESSION['CSI']['SHV']->last_name, $_SESSION['CSI']['SHV']->first_name);
		$_SESSION['CSI']['SHV']->default_profile_flag = (isset($_SESSION['CSI']['SHV']->profile_id) && $_SESSION['CSI']['SHV']->profile_id == $this->config->item('profile_id')) ? 1:0;


		$_SESSION['CSI']['SHV']->sale_id = $sale_id;
		$_SESSION['CSI']['SHV']->transaction_time = date('d/m/Y H:i:s', strtotime($transaction_info['sale_time']));

		$this->db->select('first_name, last_name, person_id');
		$this->db->from('people');
		$this->db->where('person_id', $transaction_info['employee_id']);
		$employee = $this->db->get()->row();
		$_SESSION['CSI']['SHV']->employee_id = $employee ? $employee->person_id : $transaction_info['employee_id'];
		$_SESSION['CSI']['SHV']->employee_formatted = $employee ? $this->Common_routines->format_full_name($employee->last_name, $employee->first_name) : '';


		$_SESSION['CSI']['SHV']->header_valueAD_TTC = $transaction_info['overall_total'];
		$_SESSION['CSI']['SHV']->header_valueAD_HT = $transaction_info['subtotal_after_discount'];

		unset($_SESSION['TVA']);
		$_SESSION['TVA'] = array();
		foreach($_SESSION['CSI']['CT'] as $line => $cart_item)
		{

			$item_data = $this->Item->get_info($_SESSION['CSI']['CT'][$line]->item_id);
			if($item_data->DynamicKit == 'Y')
			{
				//$_SESSION['CSI']['CT'][$line]->off = '1';
			}
			// calculate line TTC because its not stored in the DB
			$line_TTC = round($_SESSION['CSI']['CT'][$line]->item_unit_price * ((100 + $_SESSION['CSI']['CT'][$line]->line_tax_percentage) / 100), 2);
            
			$_SESSION['CSI']['CT'][$line]->name             = $_SESSION['CSI']['CT'][$line]->description;
			$_SESSION['CSI']['CT'][$line]->item_number      = $_SESSION['CSI']['CT'][$line]->line_item_number;
			$_SESSION['CSI']['CT'][$line]->line_quantity    = $_SESSION['CSI']['CT'][$line]->quantity_purchased;
			$_SESSION['CSI']['CT'][$line]->line_priceTTC    = $line_TTC;
			$_SESSION['CSI']['CT'][$line]->line_valueAD_TTC = $_SESSION['CSI']['CT'][$line]->line_sales;
					
			//$line_tax_percentage = $_SESSION['CSI']['CT'][$line]->line_tax_percentage;
			//$_SESSION['TVA']["$line_tax_percentage"] += $_SESSION['CSI']['CT'][$line]->line_tax;
			$test_empty = array();
			$test_empty['t1'] = floatval($_SESSION['CSI']['CT'][$line]->item_unit_price);
			$test_empty['t2'] = floatval($_SESSION['CSI']['CT'][$line]->line_sales);
			$test_empty['t3'] = floatval($_SESSION['CSI']['CT'][$line]->line_tax);
			$test_empty['t4'] = floatval($_SESSION['CSI']['CT'][$line]->line_sales_before_discount);
			$test_empty['t5'] = floatval($_SESSION['CSI']['CT'][$line]->line_sales_after_discount);
			
			$t1 = empty($test_empty['t1']);
			$t2 = empty($test_empty['t2']);
			$t3 = empty($test_empty['t3']);
			$t4 = empty($test_empty['t4']);
			$t5 = empty($test_empty['t5']);
			
			if($t1 && $t2 && $t3 && $t4 && $t5)
			{
				$_SESSION['CSI']['CT'][$line]->off = '1';
			}	
		}

		//research TVA for this current sale
		$tva = $this->Sale->get_overall_tax_for_sale_with_sale_id($sale_id);
		foreach($tva as $percent => $value)
		{
			if(!empty($value))
			{
				$per = $value['line_tax_percentage'];
				$val = floatval($value['som']);
				$_SESSION['TVA']["$per"] = $val;
			}
		}

		$ph_texte = fopen("/var/www/html/wrightetmathon/ticket.txt", "w");
		$ph =$ph_texte;
		if($reprint_ticket == "on")
		{
			//création des variables SESSION pour l'impression du ticket de caisse
			
			//appel de l'impression
			$printer = $this->config->item('ticket_printer');
			$ph = fopen($printer, "w");
			// if it opens, print a ticket
			if($ph)
			{
				$_SESSION['CSI']['SHV']->ph = $ph;
				$this->load->view("sales/ticket");
			}
		}
		if($reprint_mail == "on")
		{
			$ph_texte = fopen("/var/www/html/wrightetmathon/ticket.txt", "w");
		    $ph =$ph_texte;
			// if it opens, print a ticket
			if($ph)
			{
				$_SESSION['CSI']['SHV']->ph = $ph;
				$this->load->view("sales/ticket");
			}
			$input['person_id'] = $_SESSION['CSI']['SHV']->customer_id;
			$data_people_with_email = $this->Person->get_info_people($input);
			if(empty($data_people_with_email) || !isset($data_people_with_email[0]['email']) || empty($data_people_with_email[0]['email']))
            {
				//email de la personne non renseigné
				$_SESSION['error_code'] = '07290';
				redirect("sales");
            }
			$request = $data_people_with_email[0];

			//construction du mail
			//send mail
			$mail_config = array(
				'protocol'							=>	'smtp',
				'smtp_host' 						=>	'ssl://mail.sonrisa-smile.com',
				'smtp_port' 						=>	'465',
				'smtp_user' 						=> 	$this->config->item('POemail'),    //Fonction avec l'email de Troyes -> changement pour ne pas causer de problèmes
				'smtp_pass' 						=> 	$this->config->item('POemailpwd'),    //Fonction avec l'email de Troyes -> changement pour ne pas causer de problèmes
	///*        //'smtp_user' 						=>	'envoie-commande@sonrisa-smile.com',
				//'smtp_pass' 						=>	'J~?mbk+HhJ)W',
				'mailtype'  						=>	'html',
				'starttls'  						=>	FALSE,
				'wordwrap'							=>	TRUE,    // Dans le cas où nos lignes comportent plus de 70 caractères, nous les coupons en utilisant wordwrap() $message = wordwrap($message, 70, "\r\n");
				'smtp_timeout'						=>	60,
				'newline'   						=>	"\r\n"
				);
				
				//Chargement
				$this														->	load->library('email', $mail_config);
				//$this->email												->	from($this->config->item('email'), $this->config->item('company'));    //Fonction avec l'email de Troyes -> changement pour ne pas causer de problèmes
				//Envoie du mail à partir de l'adresse suivante:
				$this->email												->	from($this->config->item('email'), $this->config->item('company'));    //Fonction avec l'email de Troyes -> changement pour ne pas causer de problèmes
				//Envoie du mail à la personne consernée
				$this->email												->	to($request['email']);
				//Copie du mail à quelqu'un
				//$this->email												->	cc('');
				//Entête, sujet du mail avec le numéro du ticket de caisse
				$this->email												->	subject('Ticket de caisse numéro: ' . $_SESSION['numero_ticket']); //$data['transaction_time']." - CY_".$data['receiving_id'].' - Commande Fournisseur');
				//Balise html à mettre à la fin de la chaîne de caractére dans le message
				$_SESSION['message_mail']=$_SESSION['message_mail'] . '<strong><br><center>Merci de votre confiance</center></strong><br>';
				$_SESSION['message_mail']=$_SESSION['message_mail'] . '</p></center></body></html>';
				$this->email												->	message($_SESSION['message_mail']); //$comment."\r\n"."\r\n".$this->config->item('POemailmsg'));
				//Envoie du ticket de caisse par mail
				$this->email												->	send();
		}

		// Restaurer l'état session (vente en cours)
		if ($backup_CSI !== null) { $_SESSION['CSI'] = $backup_CSI; } else { unset($_SESSION['CSI']); }
		if ($backup_TVA !== null) { $_SESSION['TVA'] = $backup_TVA; } else { unset($_SESSION['TVA']); }
		unset($_SESSION['reprint']);
		unset($_SESSION['reprint_sale_info']);
		unset($_SESSION['reprint_sale_items']);
		unset($_SESSION['reprint_sale_payments']);
		unset($_SESSION['reprint_customer_name']);
		unset($_SESSION['reprint_employee_name']);
		redirect("common_controller/common_exit");
	}


	function receipt($sale_id)
	{
		// get the transaction info
		$transaction_info												=	array();
		$transaction_info 												= 	$this->Sale->get_info($sale_id)->row_array();
		$payments														=	array();
		$payments														=	$this->Sale->get_sale_payments($sale_id)->result_array();

		// get the code from the mode
		$tcode 															=	$this->Transaction->get_transaction_code($transaction_info['mode']);

		
		// load output data array
		$data['sale_id'] 												= 	$tcode.$sale_id;
		$data['cart']													=	$this->Sale->get_sale_items($sale_id)->result_array();
		$data['subtotal_before_discount']								=	$transaction_info['subtotal_before_discount'];
		$data['subtotal_discount_percentage_amount']					=	$transaction_info['subtotal_discount_percentage_amount'];
		$data['subtotal_discount_amount_amount']						=	$transaction_info['subtotal_discount_amount_amount'];
		$data['subtotal_after_discount']								=	$transaction_info['subtotal_after_discount'];
		$data['overall_discount_percentage']							=	$transaction_info['overall_discount_percentage'];
		$data['overall_discount_amount']								=	$transaction_info['subtotal_discount_amount_amount'];
		$data['tax_amount']												=	$transaction_info['overall_tax'];
		$data['total']													=	$transaction_info['overall_total'];
		$data['cost']													=	$transaction_info['overall_cost'];
		$data['profit']													=	$transaction_info['overall_profit'];
		$data['taxes']													=	$transaction_info['overall_tax'];
		$data['transaction_title']										=	$this->lang->line('reports_'.$transaction_info['mode']);
		$data['transaction_time']										= 	date('d/m/Y H:i:s', strtotime($transaction_info['sale_time']));
		$data['payments']												=	$payments;
		$data['amount_change']											=	$transaction_info['amount_change'];
		$data['transaction_subtype'] 									= 	$transaction_info['mode'];
		$data['payment_type'] 											= 	$transaction_info['payment_type'];

/*
SELECT * FROM `ospos_sales_items` WHERE `sale_id` = (SELECT sale_id FROM `ospos_sales` ORDER By sale_time DESC LIMIT 1);
SELECT * FROM `ospos_sales` ORDER By sale_time DESC LIMIT 1;

SELECT SUM(`line_tax`), `line_tax_percentage`  FROM `ospos_sales_items` WHERE `sale_id` =(SELECT sale_id FROM `ospos_sales` ORDER By sale_time DESC LIMIT 1) GROUP BY `line_tax_percentage`;


		*/
		//SELECT SUM(`line_tax`), `line_tax_percentage`  FROM `ospos_sales_items` WHERE `sale_id` =(SELECT sale_id FROM `ospos_sales` ORDER By sale_time DESC LIMIT 1) GROUP BY `line_tax_percentage`;
		$data['overall_tax'] = $this->Sale->get_overall_tax_for_sale_with_sale_id($sale_id);
		$data['overall_tax_percentage'] 								= 	$transaction_info['overall_tax_percentage'];
		$data['overall_tax_name'] 										= 	$transaction_info['overall_tax_name'];
		$comment 														= 	$transaction_info['comment'];

		// get and format customer name
		$customer_id													=	$transaction_info['customer_id'];
		if($customer_id != -1)
		{
			$person_info 												= 	$this->Customer->get_info($customer_id);
			$data['customer'] 											= 	$this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
		}

		// get and format employee name
		$employee_id													=	$transaction_info['employee_id'];
		$person_info													=	$this->Employee->get_info($employee_id);
		$data['employee'] 												= 	$transaction_info['employee_id'];

		// generate barcode
		$data['image_path']												=	$this->Common_routines->generate_barcode($data['sale_id']);


		$this->load->view("sales/receipt", $data);
		unset($_SESSION['TVA']);
		unset($_SESSION['CSI']);
		$this->sale_lib->clear_all();
	}

	function edit($transaction_code)
	{
		$pieces 							=	explode("-", $transaction_code);
		$code 								=	$pieces[0];
		$transaction_id 					=	$pieces[1];

		$data 								=	array();

		// load customers
		$data['customers'] 					=	array('' => $this->lang->line('sales_no_customer'));
		foreach ($this->Customer->get_all()->result() as $person_info)
		{
			$data['customers'][$person_info->person_id] = $this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
		}

		// load employees
		$data['employees'] 					= 	array();
		foreach ($this->Employee->get_all()->result() as $person_info)
		{
			$data['employees'][$person_info->person_id] = $this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
		}

		$data['transaction_info'] 			= 	$this->Sale->get_info($transaction_id)->row_array();

		$data['code'] 						= 	$code;

		$this								->	load->view('sales/edit', $data);
	}

	function delete($sale_id)
	{
		$data = array();

		if ($this->Sale->delete($sale_id))
		{
			$data['success'] = true;
		}
		else
		{
			$data['success'] = false;
		}

		$this->load->view('sales/delete', $data);

	}

	function save_trans($transaction_id)
	{
		$transaction_data = array(
			//'sale_time' => date('Y-m-d', strtotime($this->input->post('date'))),
			'customer_id' => $this->input->post('customer_id') ? $this->input->post('customer_id') : null,
			'employee_id' => $this->input->post('employee_id'),
			'comment' => $this->input->post('comment'),
			'branch_code'	=>	$this->config->item('branch_code')
		);

		if ($this->Sale->update($transaction_data, $transaction_id))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('sales_successfully_updated')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('sales_unsuccessfully_updated')));
		}
	}

	function _payments_cover_total()
	{
		$total_payments = 0;

		foreach($this->sale_lib->get_payments() as $payment)
		{
			$total_payments += $payment['payment_amount'];
		}

		/* Changed the conditional to account for floating point rounding */
		//if ( ( $this->sale_lib->get_mode() == 'sales' ) && ( ( to_currency_no_money( $this->sale_lib->get_total() ) - $total_payments ) > 1e-6 ) )
		if ( ( ( to_currency_no_money( $this->sale_lib->get_total() ) - $total_payments ) > 1e-6 ) )
		{
			return false;
		}

		return true;
	}

 	function _reload($data=array())
	{

		// set the controller
		$_SESSION['controller_name']									=	strtolower(get_class($this));

		// PHP 8 compatibility: ensure CSI sub-objects exist before property assignment
		if (!isset($_SESSION['CSI']) || !is_array($_SESSION['CSI'])) { $_SESSION['CSI'] = []; }
		if (!isset($_SESSION['CSI']['SHV']) || !is_object($_SESSION['CSI']['SHV'])) { $_SESSION['CSI']['SHV'] = new stdClass(); }
		if (!isset($_SESSION['CSI']['CT']) || !is_array($_SESSION['CSI']['CT'])) { $_SESSION['CSI']['CT'] = []; }

	//-------------------------------------------------------------->
	// get all the data required for the targets area
	//-------------------------------------------------------------->
		//
		unset($_SESSION['CSI']['TT']);
		$_SESSION['CSI']['TT']				= new stdClass();

		//-------------------------------------------------------------->
		// Section 1 - targets
		//-------------------------------------------------------------->

		// get targets for this month
		$targets														=	$this->Target->get_targets(date("Y"), date("m"));
		if (!$targets)
		{
			$targets																										=	new stdClass();

			$targets->target_shop_open_days	=	0;
			$targets->target_shop_turnover	=	0;
		}

		$_SESSION['CSI']['TT']->monthlytarget							=	round($targets->target_shop_turnover, 0, PHP_ROUND_HALF_UP);
		$_SESSION['CSI']['TT']->averagenumberopendays					=	round($targets->target_shop_open_days, 0, PHP_ROUND_HALF_UP);
		$_SESSION['CSI']['TT']->dailytarget								=	$_SESSION['CSI']['TT']->averagenumberopendays > 0 ? round(($_SESSION['CSI']['TT']->monthlytarget / $_SESSION['CSI']['TT']->averagenumberopendays), 0, PHP_ROUND_HALF_UP) : 0;

		//-------------------------------------------------------------->
		// Section 2 - actuals
		//-------------------------------------------------------------->

		// load the sales realised data - get the sales by sales and by date
		// first create the temp sales data file
		$this->load->model('Sale');

		// set the parms for extracting the daily sales data
		$start_date 					= 	date('Y-m-d', mktime(0,0,0,date("m"),1,date("Y")));
		$end_date						= 	date('Y-m-d');
		$count 							= 	0;
		$monthlyrealised				= 	0;
		$monthlyrealised2				=	0;
		// get the daily sales (& returns) data, need to get day by day as we need to count the number of days.
		$report_data 					= 	$this->Sale->get_sales_data_notfide(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
		$report_data2 					= 	$this->Sale->get_averag_basket_data_notfide(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
		
		foreach($report_data as $row)
		{
			$count 						= 	$count + 1;
			$monthlyrealised			= 	$monthlyrealised + $row['subtotal'];
			$monthlyrealised2			= 	$monthlyrealised2 + $row['totalht'];
		}
		//$monthlyrealised2=0;
		foreach($report_data2 as $row)
		{
			$count2 						= 	$count2 + $row['cnt_invoice'];
			//$monthlyrealised2			= 	$monthlyrealised2 + $row['totalht'];
		}
		if ($count2>0)
		{
			$_SESSION['CSI']['TT']->monthlybasket				=	round(($monthlyrealised2 / $count2), 2, PHP_ROUND_HALF_UP);
		}
		$_SESSION['CSI']['TT']->count_invoice  					=$count2;
		// Now calculate the realised to date fields
		$_SESSION['CSI']['TT']->monthlyrealised							=	round($monthlyrealised, 0, PHP_ROUND_HALF_UP);
		$_SESSION['CSI']['TT']->dailyrealised							=	$count > 0 ? round(($monthlyrealised / $count), 0, PHP_ROUND_HALF_UP) : 0;
		$_SESSION['CSI']['TT']->monthlyrealisedpercent					=	$_SESSION['CSI']['TT']->monthlytarget > 0 ? round(($monthlyrealised / $_SESSION['CSI']['TT']->monthlytarget * 100), 0, PHP_ROUND_HALF_UP) : 0;

		// Now calculate what's left todo this month
		$_SESSION['CSI']['TT']->monthlytodo								=	round(($_SESSION['CSI']['TT']->monthlytarget - $monthlyrealised), 0, PHP_ROUND_HALF_UP);
		$daysleft														=	$_SESSION['CSI']['TT']->averagenumberopendays - $count;
		if ($daysleft <= 0)
		{
			$daysleft = 1;
		}
		$_SESSION['CSI']['TT']->dailytodo								=	round(($_SESSION['CSI']['TT']->monthlytodo / $daysleft), 0, PHP_ROUND_HALF_UP);


		//-------------------------------------------------------------->
		// Section 3 - sales today
		//-------------------------------------------------------------->
		$start_date 													=	date('Y-m-d');
		$report_data 													=	$this->Sale->get_sales_data(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));
		$dailydone 														= 	0;
		foreach($report_data as $row)
		{
			$dailydone													= 	$dailydone + $row['total'];
		}

		$_SESSION['CSI']['TT']->dailydone								=	round($dailydone, 0, PHP_ROUND_HALF_UP);

	//-------------------------------------------------------------->
	// load output data
	//-------------------------------------------------------------->

		// get employee info
		$_SESSION['CSI']['EI'] 											= 	$this->Employee->get_logged_in_employee_info();
		$_SESSION['CSI']['SHV']->employee_id							=	$_SESSION['CSI']['EI']->person_id;
		$_SESSION['CSI']['SHV']->employee_formatted						=	$this->Common_routines->format_full_name($_SESSION['CSI']['EI']->last_name, $_SESSION['CSI']['EI']->first_name);

		// set customer
		$this															->	customer_select('RE');

		// now that we have added the item to the cart,
		// we need to update the price for each cart item based on the client pricelist_id
		// so, get the cart and read through, getting price for each item_id, pricelist_id and
		// set the cart
		// This is required because the user may not enter the client until the end or at any stage during the sale.
		// Or if the user changes the client during the sale
		// Do this only if a price has NOT been forced at overall cart level.
		// If line price has been forced because of discount at line level, do not recalculate line

		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			// get price
			if ($cart_line->kit_item != 'Y')
			{
				$prices													=	$this->get_price($_SESSION['CSI']['CT'][$line]->item_id, $_SESSION['CSI']['SHV']->pricelist_id);
				
				if($_SESSION['CSI']['CT'][$line]->line_priceTTC != 0)
				{
					$_SESSION['CSI']['CT'][$line]->line_priceTTC = $prices['price_with_tax'];
					$_SESSION['CSI']['CT'][$line]->line_priceHT	 = $prices['price_no_tax'];	
				}
				//$_SESSION['CSI']['CT'][$line]->line_priceTTC = $prices['price_with_tax'];
				//$_SESSION['CSI']['CT'][$line]->line_priceHT	 = $prices['price_no_tax'];	
			}
			else
			{
				$_SESSION['CSI']['CT'][$line]->line_priceTTC = 0;
				$_SESSION['CSI']['CT'][$line]->line_priceHT	 = 0;
			}

			if($_SESSION['click_avoir'] != 2 )
			{
				// calculate values
				    $this         ->    line_values($line);
			}
		}

		// calculate totals
	    $this							->	header_values();
	    $this							->	payment_values();

		// test for loss, $loss = TRUE if overall loss
		// first test overall cart
		$loss															=	FALSE;
		$cart_control													=	"loss_overall";
		$line															=	0;
		$discount														=	0;
		$loss															=	$this->cart_control($cart_control, $line, $discount);

		// if not overall loss, test loss per line
		if ($loss == FALSE)
		{
			$cart_control												=	"loss_line";
			$line														=	0;
			$discount													=	0;
			$loss														=	$this->cart_control($cart_control, $line, $discount);
		}

		// Set route
		$this															->	session->set_userdata('route', 'SS');

		// output to the pole display
		// first try to open it.
		$fh																=	$this->pole_display->open();

		// if found output to it.
		if($fh)
		{
			// show welcome if no customer and no items in cart
			if (!isset($_SESSION['CSI']['SHV']->customer_id) AND !isset($_SESSION['CSI']['CT']))
			{
				$this													->	pole_display->language($fh);
				$this													->	pole_display->clear_display($fh);
				$this													->	pole_display->welcome($fh);
				$this													->	pole_display->close($fh);
			}
			else
			{
				// test for amount due
				if ($_SESSION['CSI']['SHV']->header_amount_due_TTC != 0)
				{
					$this												->	pole_display->language($fh);
					$this												->	pole_display->clear_display($fh);
					$this												->	pole_display->show_cart($fh);
					$this												->	pole_display->close($fh);
				}
				else
				{
					$this												->	pole_display->language($fh);
					$this												->	pole_display->clear_display($fh);
					$this												->	pole_display->show_total($fh);
					$this												->	pole_display->close($fh);
				}
			}
		}

		// load top 3 quick payment methods for sidebar buttons
		$all_pm = $this->Sale->get_payment_methods();
		$_SESSION['CSI']['QUICK_PM'] = array_slice($all_pm, 0, 3);

		// output the view
		// set origin
		$this->load->view("sales/register", $data);
	}

    function cancel_sale()
    {
		$_SESSION['cancel_indicator'] = 'Y';
		$this->suspend();
		unset($_SESSION['cancel_indicator']);
    	unset($_SESSION['CSI']);
    	$this->_reload();
    }

	function suspend()
	{
		// get stuff
		if ($this->input->post('comment') == NULL)
		{
			$_SESSION['CSI']['SHV']->comment							=	date('Y-m-d H:i:s').' '.$this->lang->line('sales_successfully_suspended_sale');
		}
		else
		{
			$_SESSION['CSI']['SHV']->comment							=	$this->input->post('comment');
		}

		$_SESSION['CSI']['SHV']->receipt_title							=	$this->lang->line('sales_receipt');

		// SAVE sale to suspend database
		$sale_id														=	'POS '.$this->Sale_suspended->save();

		// test for error
		if ($sale_id == 'POS -1')
		{
			unset($_SESSION['show_dialog']);
			unset($_SESSION['confirm_what']);
			$_SESSION['error_code']										=	'06020';
			$this														->	_reload();
			return;
		}

		// reload
		unset($_SESSION['CSI']);
		if($_SESSION['cancel_indicator'] != 'Y')
        {
			$_SESSION['error_code']                                       =   '06030';
		}
		unset($_SESSION['cancel_indicator']);
		redirect("sales");
	}

	function suspended()
	{
		// turn on modal dialog for suspended sales
		$_SESSION['show_dialog']										=	1;

		// get the suspended sales
		$_SESSION['suspended_sales']									=	array();
		$_SESSION['suspended_sales']									=	$this->Sale_suspended->get_all()->result_array();

		// set message if there are no suspended sales
		if (count($_SESSION['suspended_sales']) == 0)
		{
			$_SESSION['error_code']										=	'06090';
		}

		// show suspended sales
		$this->_reload();
	}

	function unsuspend($sale_id)
	{
		// initialise
		unset($_SESSION['show_dialog']);
		unset($_SESSION['confirm_what']);
		unset($_SESSION['CSI']);

		// PHP 8 compatibility: re-initialise CSI after unset
		$_SESSION['CSI'] = [];
		$_SESSION['CSI']['SHV'] = new stdClass();
		$_SESSION['CSI']['CT'] = [];

		// set origin
		$origin															=	'sales_unsuspend';

		// set customer
		$_SESSION['CSI']['SHV']->customer_id							=	$this->Sale_suspended->get_customer($sale_id)->person_id;
		$this															->	customer_select('US');

		// set overall discount
		$suspended_sale_info											=	$this->Sale_suspended->get_info($sale_id)->result();
		if ($suspended_sale_info[0]->payment_type != NULL)
		{
			$_SESSION['CSI']['SHV']->overall_discount					=	$suspended_sale_info[0]->payment_type;
		}

		// add items to cart
		foreach($this->Sale_suspended->get_sale_items($sale_id)->result() as $row)
		{
			$this														->	add_line($row->item_id,$row->quantity_purchased,$row->discount_percent, $origin);
		}

		// delete the suspended sale
		$this->Sale_suspended->delete($sale_id);

		// reload
    	$this->_reload();
	}

	function confirm($confirm_what)
	{
		//Affectation pour reconnaître si le vendeur a appuyé sur le bouton "VALIDER" ou si le vendeur a appuyé sur le bouton "Envoyer le ticket de caisse par mail
		$_SESSION['blocage_de_l_impression_du_ticket_de_caisse']=4;

		// initialse
		unset($_SESSION['show_dialog']);
		unset($_SESSION['confirm_what']);
		$this															->	unset_last_line();

		// Do final checks depending on confirm what
		switch ($confirm_what)
		{
			case 'invoice':
				// if cashtill is closed do not allow new sales
				if ($_SESSION['cashtill_closed'] == 1)
				{
					$_SESSION['error_code']								=	'05790';
					$this												->	_reload();
					return;
				}

				// customer must exist
				if (!isset($_SESSION['CSI']['SHV']->customer_id))
				{
					$_SESSION['error_code']								=	'05780';
					$this												->	_reload();
					return;
				}

				// test customer on-stop - if so refuse sale
				if ($_SESSION['CSI']['CI']->on_stop_indicator == 'Y')
				{
					$_SESSION['error_code']								=	'05800';
					$_SESSION['substitution_parms']						=	array($_SESSION['CSI']['CI']->on_stop_amount, $_SESSION['CSI']['CuI']->currency_sign, $_SESSION['CSI']['CI']->on_stop_reason);
					$this												->	_reload();
					return;
				}

				// check amount due is zero
				if ((round($_SESSION['CSI']['SHV']->header_amount_due_TTC, 2) != 0)&& (!isset($_SESSION['var_annulation_facture'])))
				{
					$_SESSION['error_code']								=	'05820';
					$this												->	_reload();
					return;
				}

				// test cart for item errors - why do this?
				$cart_error												=	0;
				foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
				{
					// test for blank item number - not sure why this could occur
					if ($cart_line->item_number == NULL)
					{
						$cart_error										=	1;
					}
				}

				// if error found output error message
				if ($cart_error == 1)
				{
					$_SESSION['error_code']								=	'05810';
					$this												->	_reload();
					return;
				}

				// test overall loss, $loss = TRUE if overall loss
				$loss													=	FALSE;
				$cart_control											=	"loss_overall";
				$line													=	0;
				$discount												=	0;
				$loss													=	$this->cart_control($cart_control, $line, $discount);
				if ($loss)
				{
					$this												->	_reload();
					return;
				}
				else
				{
					// if total invoice is not a loss, test each line for a loss
					$cart_control										=	"loss_line";
					$line												=	0;
					$discount											=	0;
					$loss												=	$this->cart_control($cart_control, $line, $discount);
					if ($loss)
					{
						$this											->	_reload();
						return;
					}
				}

				// if all tests pass, then complete
				$this													->	complete();
				break;

			case 'suspend':
				// cannot suspend of no client
				if (!isset($_SESSION['CSI']['SHV']->customer_id))
				{
					$_SESSION['error_code']								=	'05780';
					$this												->	_reload();
					return;
				}
				// cannot suspend if payments
				if (count($_SESSION['CSI']['PD']) != 0)
				{
					$_SESSION['error_code']								=	'07120';
					$this												->	_reload();
					return;
				}
				break;

			default:
				break;
		}

		// initialise
		$_SESSION['show_dialog']										=	2;
		$_SESSION['confirm_what']										=	$confirm_what;
		$_SESSION['CSI']['SHV']->comment								=	$this->input->post('comment');
		$_SESSION['origin']												=	'CN';

		// reload
    	$this->_reload();
	}

	function reload()
	{
		$this															->	_reload();
	}

	function unset_last_line()
	{
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			$_SESSION['CSI']['CT'][$line]->last_line					=	FALSE;
			unset($_SESSION['CSI']['CT'][$line]->last_line);
		}
	}

	function customer_set_defaults()
	{
		$_SESSION['CSI']['SHV']->pricelist_id							=	$this->config->item('pricelist_id');
		$_SESSION['CSI']['SHV']->profile_id								=	$this->config->item('profile_id');
		$_SESSION['CSI']['SHV']->tax_name								=	$this->config->item('default_tax_1_name');
		$_SESSION['CSI']['SHV']->tax_percent							=	$this->config->item('default_tax_1_rate');
		$_SESSION['CSI']['SHV']->default_profile_flag					=	1;

		$_SESSION['CSI']['PI']											=	$this->Pricelist->get_info($_SESSION['CSI']['SHV']->pricelist_id);
		$_SESSION['CSI']['CuI']											=	$this->Currency->get_info($_SESSION['CSI']['PI']->pricelist_currency);
	}

	function discount_set()
	{
		// set discount if not default profile
		if ($_SESSION['CSI']['SHV']->default_profile_flag == 0)
		{
			$discount													=	$_SESSION['CSI']['CPI']->profile_discount;
		}
		else
		{
			if (isset($_SESSION['CSI']['SHV']->overall_discount))
			{
				$discount						 						= 	$_SESSION['CSI']['SHV']->overall_discount;
			}
			else
			{
				$discount						 						= 	0;
			}
		}

		// return
		return																$discount;
	}

	function next_line_number()
	{
		// get the next line number
		// I can't just count the elements in the arry because the user has the ability to delete a line from anywhere in the array.
		// So, I need to read through the array to find the highest line and add one
		$highest_line													=	0;
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			if ($highest_line < $line)
			{
				$highest_line											=	$line;
			}
		}

		// now add one to it to get next_cart_line
		$next_cart_line													=	$highest_line + 1;

		// return
		return																$next_cart_line;
	}

	function line_values($line)
	{
		    $_SESSION['CSI']['CT'][$line]->line_valueBD_TTC				=	round($_SESSION['CSI']['CT'][$line]->line_priceTTC * $_SESSION['CSI']['CT'][$line]->line_quantity, 2);
		    $_SESSION['CSI']['CT'][$line]->line_valueBD_HT				=	round($_SESSION['CSI']['CT'][$line]->line_priceHT * $_SESSION['CSI']['CT'][$line]->line_quantity, 2);
		    $_SESSION['CSI']['CT'][$line]->line_taxBD					=	round($_SESSION['CSI']['CT'][$line]->line_valueBD_TTC - $_SESSION['CSI']['CT'][$line]->line_valueBD_HT, 2);
    
		    $_SESSION['CSI']['CT'][$line]->line_valueAD_TTC				=	round(($_SESSION['CSI']['CT'][$line]->line_valueBD_TTC * (100 - $_SESSION['CSI']['CT'][$line]->line_discount)) / 100, 2);
		    $_SESSION['CSI']['CT'][$line]->line_valueAD_HT				=	round(($_SESSION['CSI']['CT'][$line]->line_valueBD_HT * (100 - $_SESSION['CSI']['CT'][$line]->line_discount)) / 100, 2);
		    $_SESSION['CSI']['CT'][$line]->line_taxAD					=	round($_SESSION['CSI']['CT'][$line]->line_valueAD_TTC - $_SESSION['CSI']['CT'][$line]->line_valueAD_HT, 2);
    
		    $_SESSION['CSI']['CT'][$line]->line_cost_HT					=	round($_SESSION['CSI']['CT'][$line]->supplier_cost_price * $_SESSION['CSI']['CT'][$line]->line_quantity, 2);
		    $_SESSION['CSI']['CT'][$line]->line_profit_HT				=	round($_SESSION['CSI']['CT'][$line]->line_valueAD_HT - $_SESSION['CSI']['CT'][$line]->line_cost_HT, 2);

		return;
	}

	function header_values()
	{
		// initialise
		$_SESSION['CSI']['SHV']->header_valueBD_TTC						=	0;
		$_SESSION['CSI']['SHV']->header_valueBD_HT						=	0;
		$_SESSION['CSI']['SHV']->header_taxBD							=	0;
		$_SESSION['CSI']['SHV']->header_valueAD_TTC						=	0;
		$_SESSION['CSI']['SHV']->header_valueAD_HT						=	0;
		$_SESSION['CSI']['SHV']->header_taxAD							=	0;
		$_SESSION['CSI']['SHV']->header_cost_HT							=	0;
		$_SESSION['CSI']['SHV']->header_profit_HT						=	0;
		$_SESSION['CSI']['SHV']->header_profit_HT_offered				=	0;
		$_SESSION['CSI']['SHV']->header_profit_HT_CN					=	0;
		$_SESSION['CSI']['SHV']->header_profit_HT_normal                =   0;
		
		// calculate the totals and set line count
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			$_SESSION['CSI']['SHV']->header_valueBD_TTC					+=	$cart_line->line_valueBD_TTC;
			$_SESSION['CSI']['SHV']->header_valueBD_HT					+=	$cart_line->line_valueBD_HT;
			$_SESSION['CSI']['SHV']->header_taxBD						+=	$cart_line->line_taxBD;
			$_SESSION['CSI']['SHV']->header_valueAD_TTC					+=	$cart_line->line_valueAD_TTC;
			$_SESSION['CSI']['SHV']->header_valueAD_HT					+=	$cart_line->line_valueAD_HT;
			$_SESSION['CSI']['SHV']->header_taxAD						+=	$cart_line->line_taxAD;
			$_SESSION['CSI']['SHV']->header_cost_HT						+=	$cart_line->line_cost_HT;
	//		$_SESSION['CSI']['SHV']->header_profit_HT					+=	$cart_line->line_profit_HT;
			if ($_SESSION['CSI']['CT'][$line]->line_offered == 'Y' )
			{
				$_SESSION['CSI']['SHV']->header_profit_HT_offered		+=	$cart_line->line_profit_HT;
			}
			elseif ($_SESSION['CSI']['CT'][$line]->CN_line == 'Y')
			{
				$_SESSION['CSI']['SHV']->header_profit_HT_CN			+=	$cart_line->line_profit_HT;
			}
			else
			{
				if($_SESSION['CSI']['CT'][$line]->off != '1')
				{
				    $_SESSION['CSI']['SHV']->header_profit_HT_normal		+=	$cart_line->line_profit_HT;
					$_SESSION['CSI']['SHV']->header_profit_HT +=	$cart_line->line_profit_HT;
				}
			}
		}

		// set mode
		if ($_SESSION['CSI']['SHV']->header_valueAD_TTC < 0)
		{
			$_SESSION['CSI']['SHV']->mode								=	'returns';
		}
		else
		{
			$_SESSION['CSI']['SHV']->mode								=	'sales';
		}

		// return
		return;
	}

	function payment_values()
	{
		// initialise
		$_SESSION['CSI']['SHV']->header_payments_TTC					=	0;

		// calculate the totals
		foreach ($_SESSION['CSI']['PD'] as $pmi => $payment)
		{
			$_SESSION['CSI']['SHV']->header_payments_TTC				+=	$payment->payment_amount_TTC;
		}

		// set amount_due
		$_SESSION['CSI']['SHV']->header_amount_due_TTC					=	round($_SESSION['CSI']['SHV']->header_valueAD_TTC - $_SESSION['CSI']['SHV']->header_payments_TTC, 2);

		// return
		return;
	}

	function get_price($item_id, $pricelist_id)
	{
		// get price info from price list file
		$item_price_info												=	$this->Item->get_info_item_price($item_id, $pricelist_id);

		// check got record
		if (count($item_price_info) == 1)
		{
			foreach ($item_price_info as $item_price);
			{
				// check validity
				// check valid to for illimited
				if ($item_price->valid_to_day == 0 AND $item_price->valid_to_month == 0 AND $item_price->valid_to_year == 0)
				{
					$item_price->valid_to_day							=	31;
					$item_price->valid_to_month							=	12;
					$item_price->valid_to_year							=	9999;
				}
				// transform dates to real dates
				$vfrom													=	strtotime($item_price->valid_from_day.'-'.$item_price->valid_from_month.'-'.$item_price->valid_from_year);
				$vto													=	strtotime($item_price->valid_to_day.'-'.$item_price->valid_to_month.'-'.$item_price->valid_to_year);
				// get today
				$now													=	time();
				// test today within validity
				if ($now < $vfrom OR $now > $vto)
				{
					// outside validity so get default price
					$item_price_info									=	$this->Item->get_info_item_price($item_id, $this->CI->config->item('pricelist_id'));

					// test got record
					if (count($item_price_info) == 1)
					{
						foreach ($item_price_info as $item_price);
						{
							$price_no_tax								=	$item_price->unit_price;
							$price_with_tax								=	$item_price->unit_price_with_tax;
						}
					}
					else
					{
						$price_no_tax									=	0;
						$price_with_tax									=	0;
					}
				}
				else
				{
					$price_no_tax										=	$item_price->unit_price;
					$price_with_tax										=	$item_price->unit_price_with_tax;
				}
			}
		}
		else
		{
			$price_no_tax												=	0;
			$price_with_tax												=	0;
		}

		// load return array
		$prices															=	array	(
																					'price_no_tax'	=>	$price_no_tax,
																					'price_with_tax'=>	$price_with_tax
																					);

		return $prices;
	}

	function add_line($item_id, $quantity, $discount, $origin)
	{
		// get next line number
		$line															=	$this->next_line_number();

		// add line
		// get item info
		$_SESSION['CSI']['CT'][$line]									=	$this->Item->get_info($item_id);

		// Set price
		$prices															=	$this->get_price($_SESSION['CSI']['CT'][$line]->item_id, $_SESSION['CSI']['SHV']->pricelist_id);
		$_SESSION['CSI']['CT'][$line]->line_priceTTC					=	$prices['price_with_tax'];
		$_SESSION['CSI']['CT'][$line]->line_priceHT						=	$prices['price_no_tax'];

		// set kit related defaults for master line
		$_SESSION['CSI']['CT'][$line]->kit_item							=	'N';
		$_SESSION['CSI']['CT'][$line]->kit_option						=	NULL;
		$_SESSION['CSI']['CT'][$line]->kit_option_type					=	NULL;
		$_SESSION['CSI']['CT'][$line]->kit_cart_line					=	NULL;

		// set quantity
		$_SESSION['CSI']['CT'][$line]->line_quantity					=	$quantity;

		// set the discount and line offered
		switch ($origin)
		{
			case 'sales_add':
				$_SESSION['CSI']['CT'][$line]->line_discount			=	$this->discount_set();
				$_SESSION['CSI']['CT'][$line]->offer				=	$_SESSION['CSI']['CT'][$line]->offer_indicator; //'N';
				$_SESSION['CSI']['CT'][$line]->line_offered				=	$_SESSION['CSI']['CT'][$line]->offer_indicator; //'N';
				break;
			case 'sales_unsuspend':
				$_SESSION['CSI']['CT'][$line]->line_discount			=	$discount;
				if ($discount == 100)
				{
					$_SESSION['CSI']['CT'][$line]->line_offered			=	'Y';
				}
				else
				{
					$_SESSION['CSI']['CT'][$line]->line_offered			=	'N';
				}
			default:
				break;
		}

		// get supplier info and append to array
		$_SESSION['transaction_info']->item_id							=	$item_id;
		$preferred_supplier_data										=	array();
		$preferred_supplier_data										=	$this->Item->get_preferred_supplier()->result_array();
		// test returned array
		if (count($preferred_supplier_data) > 0)
		{
			$_SESSION['CSI']['CT'][$line]->supplier_cost_price			=	$preferred_supplier_data[0]['supplier_cost_price'];
		}
		else
		{
			$_SESSION['CSI']['CT'][$line]->supplier_cost_price			=	0;
		}

		// calculate values
		$this															->	line_values($line);

		// test for kit and add kit lines
		if ($_SESSION['CSI']['CT'][$line]->DynamicKit == 'Y')
		{
			$this														->	add_kit_lines($line);
		}

		// unset last line processed
		$this															->	unset_last_line();
		// set last cart line processed
		$_SESSION['CSI']['CT'][$line]->last_line						=	TRUE;
		$_SESSION['CSI']['CT'][$line]->colour							=	'yellow';

		// set out of stock indicator (skip for credit notes / negative quantities)
		if ($_SESSION['CSI']['CT'][$line]->line_quantity > 0 && $_SESSION['CSI']['CT'][$line]->quantity < $_SESSION['CSI']['CT'][$line]->line_quantity)
		{
			$_SESSION['CSI']['CT'][$line]->colour						=	'orange';
			$_SESSION['error_code']										=	'06010';
		}

		// return
		return;
	}

	function payments()
	{

		if(($_SESSION['var_annulation_facture'] ?? 0)==1)
		{
			$this->annulation_cancellation_facture();
			return;
		}

		// customer must exist
		if (!isset($_SESSION['CSI']['SHV']->customer_id))
		{
			$_SESSION['error_code']										=	'05780';
			$this														->	_reload();
			return;
		}

		// initialise
		$_SESSION['show_dialog']										=	3;

		// load pay methods drop down array
		$payment_methods												=	array();
		$payment_methods												=	$this->Sale->get_payment_methods();

//		unset($payment_methods[8]);
		
//		if($this->config->item('distributeur_vapeself') == "N")
//		{
//			unset($payment_methods[3]);
//		}
		if($this->config->item('distributeur_vapeself') == "Y")
		{
	        $all_param_client = $this->Customer->get_info($_SESSION['CSI']['SHV']->customer_id);
			if(($all_param_client->profile_reference[0] == 'X') && (is_numeric($all_param_client->profile_reference[1])))
			{
				//rien
			}
			else
			{
				//Le client ne bénéfici pas d'une carte fidélité / distributeur VapeSelf
				//$_SESSION['CSI']['PM']
				unset($_SESSION['CSI']['PM']);
				unset($payment_methods[3]);
			}
			
		}

		$_SESSION['CSI']['PM_fidelity'] = array();
		foreach ($payment_methods as $payment_method)
		{
			// check if fidelity card payment method, load only if fidelity applies to this client,
			if ($payment_method['payment_method_fidelity_flag'] == 'Y')
			{
				if ($_SESSION['CSI']['CI']->fidelity_flag == 'Y')
				{
					// test cart records to see if there is an offer item;
					// if found set flag and do not load fidelity card payment method
					$offer_flag											=	FALSE;
					foreach($_SESSION['CSI']['CT'] as $line => $cart_line)
					{
						if (isset($cart_line->line_offered) && $cart_line->line_offered == 'Y')
						{
							$offer_flag									=	TRUE;

							// offer line has been found so remove the payment from existing payments for this invoice (if any) list
							foreach ($_SESSION['CSI']['PD'] as $pmi => $payment)
							{
								// test to see if this a fidelity card payment
								if ($payment->payment_method_fidelity_flag == 'Y')
								{
									unset($_SESSION['CSI']['PD'][$pmi]);
								}
							}
						}
					}

					// test to see if there is enough on the card, if not do not load fidelity pay method.
					if ($_SESSION['CSI']['SHV']->fidelity_value > $this->config->item('fidelity_minimum') AND $offer_flag == FALSE)
					{
						$_SESSION['CSI']['PM'][$payment_method['payment_method_id']]	=	$payment_method['payment_method_description'];
						$_SESSION['CSI']['PM_fidelity'][] = (int)$payment_method['payment_method_id'];
					}
				}
			}
			else
			{
				// OK so load all other payment methods except giftcard payment method
				//if ($payment_method['payment_method_giftcard_flag'] != 'Y')
				//{
					$_SESSION['CSI']['PM'][$payment_method['payment_method_id']] =	$payment_method['payment_method_description'];
				//}
			}
		}


		// add blank line to payment method dropdown to force user to select one without changing existing numeric keys
		// cannot use array_unshift for this as it re-numbers numeric keys,
		// so I'll just add the arrrays together
		$add_select 													=	array	(
																					0	=>	$this->lang->line('common_please_select')
																					);
		$_SESSION['CSI']['PM'] 											=	$add_select + $_SESSION['CSI']['PM'];

		/*
		if($_SESSION['var_annulation_facture']==1)
		{
			$this->annulation_cancellation_facture();
			return;
		}//*/

		// set default payment option: pre-selected if clicked from quick-pay button, else "select" message
		$selected_pm = $this->input->post('selected_pm');
		$_SESSION['CSI']['SHV']->default_payment_option					=	(!empty($selected_pm) && isset($_SESSION['CSI']['PM'][$selected_pm])) ? $selected_pm : 0;

		// Force payment form display even when amount_due == 0 (user clicked a payment button)
		$_SESSION['force_payment_form'] = !empty($selected_pm) ? true : false;

		// reload
    	$this->_reload();
	}

	function add_kit_lines($master_line)
	{
		/*
		// get kit structure
		$kit_structure													=	array();
		$kit_structure													=	$this->Item->get_kit_structure($_SESSION['CSI']['CT'][$master_line]->kit_reference)->result_array();

		// and read it
		foreach ($kit_structure as $kit_option)
		{
			// get the detail
			$kit_option_details											=	$this->Item->get_kit_detail_option($_SESSION['CSI']['CT'][$master_line]->kit_reference, $kit_option['kit_option'])->result_array();

			// and read the detail
			foreach ($kit_option_details as $kit_option_detail)
			{
				// get next line number
				$line													=	$this->next_line_number();
				// get the item ID
				$item_id												=	$this->Item->get_item_id($kit_option_detail['item_number']);
				// get item detail
				$_SESSION['CSI']['CT'][$line]							=	$this->Item->get_info($item_id);
				// set kit cart line
				$_SESSION['CSI']['CT'][$line]->kit_cart_line			=	$master_line;
				$_SESSION['CSI']['CT'][$line]->kit_item					=	'Y';
				$_SESSION['CSI']['CT'][$line]->kit_option				=	$kit_option['kit_option'];
				$_SESSION['CSI']['CT'][$line]->kit_option_type			=	$kit_option['kit_option_type'];
				// set quantity
				if ($kit_option['kit_option_type'] == 'F')
				{
					$_SESSION['CSI']['CT'][$line]->line_quantity		=	$kit_option['kit_option_qty'] * $_SESSION['CSI']['CT'][$master_line]->line_quantity;
				}
				else
				{
					$_SESSION['CSI']['CT'][$line]->line_quantity		=	0;
				}
				// set price = 0
				$_SESSION['CSI']['CT'][$line]->line_priceTTC			=	0;
				$_SESSION['CSI']['CT'][$line]->line_priceHT				=	0;
				// set discount and offered flag
				$_SESSION['CSI']['CT'][$line]->line_offered				=	'N';
				$_SESSION['CSI']['CT'][$line]->line_discount			=	0;
				// calculate line values
				$this													->	line_values($line);
			}
		}//*/

		// get kit structure
		$kit_structure = array();
		//$kit_structure = $this->Item->get_kit_structure($_SESSION['CSI']['CT'][$master_line]->kit_reference)->result_array();
		$kit_structure = $this->Item_kit->get_item_kit_items($_SESSION['CSI']['CT'][$master_line]->item_id);
		

		// and read it
		foreach ($kit_structure as $kit_item_detail)
		{/*
			// and read the detail
			foreach ($kit_item as $kit_item_detail)
			{//*/
				// get next line number
				$line											= $this->next_line_number();
				// get the item ID
	//			$item_id										= $this->Item->get_info($kit_item_detail['item_id']);
				// get item detail
				$_SESSION['CSI']['CT'][$line]					= $this->Item->get_info($kit_item_detail['item_id']);    // $this->Item->get_info($item_id->item_id);
				// set kit cart line
				$_SESSION['CSI']['CT'][$line]->kit_cart_line	= $master_line;
				$_SESSION['CSI']['CT'][$line]->kit_item			= 'Y';
				$_SESSION['CSI']['CT'][$line]->kit_option		= '';
				$_SESSION['CSI']['CT'][$line]->kit_option_type	= '';
				
				// set quantity
				$_SESSION['CSI']['CT'][$line]->line_quantity		=	$kit_item_detail['quantity'] * $_SESSION['CSI']['CT'][$master_line]->line_quantity;
				
				// set price = 0
				$_SESSION['CSI']['CT'][$line]->line_priceTTC    = 0;
				$_SESSION['CSI']['CT'][$line]->line_priceHT     = 0;
				// set discount and offered flag
				$_SESSION['CSI']['CT'][$line]->line_offered     = 'N';
				$_SESSION['CSI']['CT'][$line]->line_discount    = 0;
				$_SESSION['CSI']['CT'][$line]->off              = '1';

				$item_suppliers_item = $this->Item->get_supplier_id($kit_item_detail['item_id']);
				$_SESSION['CSI']['CT'][$line]->supplier_cost_price = $item_suppliers_item[0]['supplier_cost_price'];
				
				// calculate line values
				$this													->	line_values($line);
			//}
		}
	}

	function ajax_sale_detail($sale_id)
	{
		// Clean any BOM or whitespace from output buffer
		if (ob_get_level()) ob_end_clean();
		header("Content-Type: application/json; charset=utf-8");

		if (empty($sale_id) || !is_numeric($sale_id))
		{
			echo json_encode(array('success' => false, 'message' => 'ID invalide'));
			return;
		}

		// Verify sale belongs to current customer
		$sale_info = $this->Sale->get_info($sale_id)->row();
		if (!$sale_info || $sale_info->customer_id != (isset($_SESSION['CSI']['SHV']->customer_id) ? $_SESSION['CSI']['SHV']->customer_id : 0))
		{
			echo json_encode(array('success' => false, 'message' => 'Facture introuvable'));
			return;
		}

		// Get sale items
		$items = $this->Sale->get_sale_items($sale_id)->result_array();

		// Get sale payments
		$payments = $this->Sale->get_sale_payments($sale_id)->result_array();

		// Get employee name
		$employee_name = '';
		$this->db->select('first_name, last_name');
		$this->db->from('people');
		$this->db->where('person_id', $sale_info->employee_id);
		$emp_row = $this->db->get()->row();
		if ($emp_row)
		{
			$employee_name = $emp_row->first_name . ' ' . $emp_row->last_name;
		}

		echo json_encode(array(
			'success'       => true,
			'sale_id'       => $sale_info->sale_id,
			'sale_time'     => $sale_info->sale_time,
			'overall_total' => (float)$sale_info->overall_total,
			'overall_tax'   => (float)$sale_info->overall_tax,
			'subtotal'      => (float)$sale_info->subtotal_after_discount,
			'payment_type'  => $sale_info->payment_type,
			'comment'       => $sale_info->comment,
			'employee_name' => $employee_name,
			'items'         => $items,
			'payments'      => $payments
		));
	}

	function CN_select_invoice()
	{
		$_SESSION['click_avoir'] = 1;
		//Suppression et réinitialisation de la variable de session pour l'avoir
		unset($_SESSION['var_annulation_facture']);

		// customer must exist
		if (!isset($_SESSION['CSI']['SHV']->customer_id))
		{
			$_SESSION['error_code']										=	'05780';
			$this														->	_reload();
			return;
		}

		// initialise
		$_SESSION['show_dialog']										=	4;
		unset($_SESSION['CSI']['SHV']->CN_original_invoice);

		// load recent invoices for this customer
		$_SESSION['CSI']['SHV']->CN_customer_invoices = $this->Sale->get_recent_sales_by_customer($_SESSION['CSI']['SHV']->customer_id, 50)->result();

		// reload
    	$this->_reload();
	}

	//ouvrir le tiroir caisse sans obligatoirement faire une vente
	function icone_tiroir()
	{
		$printer														=	$this->config->item('ticket_printer');
		$ph 															=	@fopen($printer, "w");
		if ($ph) {
			fwrite ($ph, chr (27) .chr (112) .chr (48) .chr (55) .chr (121)); //La commande de l'imprimante Epson TM-T88V
			fclose($ph);
		}
		if(isset($_SESSION['hidden']) && $_SESSION['hidden'] == 1)
		{
			$_SESSION['hidden'] = 0;
		}
		else
		{
			$_SESSION['hidden'] = 1;
		}
		redirect("sales");
	}

	function CN_select_invoice_item()
	{
		$_SESSION['var_annulation_facture_partielle']=1;
		// FIRST validate entered invoice number
		// get entered invoice number
		$_SESSION['CSI']['SHV']->CN_original_invoice					=	$this->input->post('CN_original_invoice');

		// original invoice cannot be empty
		if (empty($_SESSION['CSI']['SHV']->CN_original_invoice))
		{
			$_SESSION['error_code']										=	'07220';
			$this														->	_reload();
			return;
		}

		// original invoice must exist on this customer
		$invoice_header													=	$this->Sale->get_info($_SESSION['CSI']['SHV']->CN_original_invoice)->row();
		if (!$invoice_header)
		{

			unset($_SESSION['CSI']['SHV']->CN_original_invoice);
			$_SESSION['error_code']										=	'07230';
			$this														->	_reload();
			return;
		}
		if ($invoice_header->customer_id != $_SESSION['CSI']['SHV']->customer_id)
		{

			unset($_SESSION['CSI']['SHV']->CN_original_invoice);
			$_SESSION['error_code']										=	'07230';
			$this														->	_reload();
			return;
		}

		// original invoice date cannot be older than one (three)  month ago.
		// replace by 240 days
		$one_month_ago													=	date("Y-m-d",strtotime("-720 day"));
		if (date($invoice_header->sale_time) < $one_month_ago)
		{
			unset($_SESSION['CSI']['SHV']->CN_original_invoice);
			$_SESSION['error_code']										=	'07240';
			$this														->	_reload();
			return;
		}

		// get invoice items
		$_SESSION['CSI']['SHV']->CN_original_invoice_items				=	$this->Sale->get_sale_items($_SESSION['CSI']['SHV']->CN_original_invoice)->result();
		
		
		//pour les avoir avec les kits
		foreach($_SESSION['CSI']['SHV']->CN_original_invoice_items as $key_line => $line_item)
		{
		//	$_SESSION['CSI']['CT'][$key_line]->line_discount = $_SESSION['CSI']['SHV']->CN_original_invoice_items[$key_line]->line_discount;
			if($line_item->line_sales == 0)
			{
		//		unset($_SESSION['CSI']['SHV']->CN_original_invoice_items[$key_line]);
			}
			$item_detail = $this->Item->get_info($line_item->item_id);
			if($item_detail->DynamicKit == 'Y')
			{
		//		unset($_SESSION['CSI']['SHV']->CN_original_invoice_items[$key_line]);
			    $CN_original_invoice_items = 1;
			}

		}
		
		if (!$_SESSION['CSI']['SHV']->CN_original_invoice_items && $CN_original_invoice_items != 1)
		{
			$_SESSION['error_code']										=	'07250';
			$this														->	_reload();
			return;
		}
		
		unset($CN_original_invoice_items);

		// SECOND show items on invoice
		// initialise
		$_SESSION['show_dialog']										=	5;

		// reload
    	$this->_reload();
	}


	function save_sav_items($inputs)
	{
		//préparation des paramétres pourmettre à jour la base de donnée
		$receivings_items['item_id'] = $inputs['item_id'];
		$receivings_items['quantity_purchased'] = -abs(intval($inputs['quantity_purchased']));
		$receivings_items['receiving_id'] = $inputs['receiving_id'];
		$receivings_items['description'] = '0';
		$receivings_items['serialnumber'] = '0';
		$receivings_items['item_unit_price'] = 0.0;
		$receivings_items['discount_percent'] = 0;
		$receivings_items['branch_code'] = $this->config->item('branch_code');
		$receivings_items['line'] = '';
        $items_data = $this->Item->get_all_info_item($receivings_items['item_id']);
		$receivings_items['item_cost_price'] = $items_data->supplier_cost_price;
		
		$input['where'] = 'ospos_receivings_items.receiving_id = '.$receivings_items['receiving_id'].' AND ospos_receivings_items.item_id = '.$receivings_items['item_id'];
		$input['receiving_id'] = $receivings_items['receiving_id'];
		
		
		$receivings_items_data = $this->Receiving->get_info_with_where($input);
		$receivings_items_max_line = $this->Receiving->get_max_line_for_receivings_items($receivings_items['receiving_id']);
		
		if(!empty($receivings_items_data))
		{
			$transaction_data['quantity_purchased'] = intval($receivings_items_data[0]['quantity_purchased']+intval($receivings_items['quantity_purchased']));
			$id['receiving_id'] = $receivings_items['receiving_id'];
			$id['item_id'] = $receivings_items['item_id'];
			$this->Receiving->update_items($transaction_data, $id);
		}

		if(empty($receivings_items_data))
		{
			$receivings_items['line'] = intval($receivings_items_max_line->max) + 1;
			$this->Receiving->update_items($receivings_items, -1);
		}

		//update inventory
		$inventory_data = array();
		$inventory_data['trans_items'] = $receivings_items['item_id'];
		$inventory_data['trans_user'] = $_SESSION['G']->login_employee_id;
		$inventory_data['trans_comment'] = 'SUSP-'.$receivings_items['receiving_id'].'-Commande en attente';
		$inventory_data['trans_stock_before'] = $items_data->quantity;
		$inventory_data['trans_inventory'] = $receivings_items['quantity_purchased'];
		$inventory_data['trans_stock_after'] = $items_data->quantity;
		$inventory_data['branch_code'] = $this->config->item('branch_code');
		$this->Inventory->insert($inventory_data);
	}

	function sav()
	{
		$this->load->library('../controllers/receivings');
		$comment = 'S.A.V '. $this->config->item('company');
		$mode = 'suspended';
		//$count = 0;
		$transaction_data = array();
		$transaction_data['employee_id'] = $_SESSION['G']->login_employee_id;

		
//		$receiving_suspended = $this->Receiving->get_all_by_mode($mode);
		$receiving_suspended_id = $this->Receiving->check_id_with_mode($mode);
		if (!$receiving_suspended_id) { $receiving_suspended_id = new stdClass(); }
		$receiving_suspended_id->receiving_id = (empty($receiving_suspended_id->receiving_id)) ? '-1' : $receiving_suspended_id->receiving_id;

		//update or insert elements for ospos_receivings
		//update
        if($this->Receiving->exists($receiving_suspended_id->receiving_id))
        {
			$receiving_data = $this->Receiving->get_info($receiving_suspended_id->receiving_id);
			if($receiving_data->comment == '0')
			{
				$transaction_data['comment'] = $comment;
			}
			$this->Receiving->update($transaction_data, $receiving_suspended_id->receiving_id);
			$receiving_id = $receiving_suspended_id->receiving_id;
        }
		
		//insert
        if(!$this->Receiving->exists($receiving_suspended_id->receiving_id))
        {
			$supplier = $this->Supplier->get_supplier_sonrisa();
			$transaction_data['comment'] = $comment;
			$transaction_data['supplier_id'] = $supplier ? $supplier->person_id : null;
			$transaction_data['mode'] = $mode;
			$transaction_data['branch_code'] = $this->config->item('branch_code');
			
			$receiving_id = $this->Receiving->insert($transaction_data, $receiving_suspended_id->receiving_id);
		}
		
		//récupération des items de la transaction effectuée
		foreach ($_SESSION['CSI']['SHV']->CN_selected_invoice_items_sav as $selected_item_id_sav)
		{
			// Get the quantity from the original invoice (= the avoir quantity)
			$invoice_item_sav_details = $this->Sale->get_sale_item($_SESSION['CSI']['SHV']->CN_original_invoice, $selected_item_id_sav)->row();
			if (!$invoice_item_sav_details) { $invoice_item_sav_details = new stdClass(); $invoice_item_sav_details->quantity_purchased = 0; }
			$sav_quantity = abs(intval($invoice_item_sav_details->quantity_purchased));

			$data_items = $this->Item->get_info($selected_item_id_sav);
			if($data_items->DynamicKit != 'Y')
			{
                //update for simple items
                $inputs = array('item_id' => $selected_item_id_sav, 'quantity_purchased' => $sav_quantity, 'receiving_id' => $receiving_id);
                $this->save_sav_items($inputs);
			}
			if($data_items->DynamicKit == 'Y')
			{
				//update for kit items
				$data_kit = $this->Item_kit->get_item_kit_items($selected_item_id_sav);
				foreach($data_kit as $kit => $item)
				{
					$inputs = array('item_id' => $item['item_id'],
					    'quantity_purchased' => $sav_quantity * intval($item['quantity']),
					    'receiving_id' => $receiving_id);
					$this->save_sav_items($inputs);
				}
			}
		}
		


//receivings_items insert line or update line if exist

		//        $this->receivings->unsuspend($receiving_suspended_id->receiving_id);
		//UPDATE 
		//foreach($receiving_suspended as $key => $line)
		//{
        //    $count += check_id_with_mode($mode);
		//}
		//regarder si une commande "négative" existe déjà
			//si oui, alors ajouter les articles sav à la commande
			//si non, alors créer une commande sav
		
	}

	function CN_add_line()
	{
		if($_SESSION['click_avoir'] == 1)
		{
			$_SESSION['click_avoir'] = 2;
		}
		if(isset($_POST['annulation_facture']))
		{
			foreach($_SESSION['CSI']['SHV']->CN_original_invoice_items as $i=>$item)
			{
				$_SESSION['CSI']['SHV']->CN_selected_invoice_items[$i]=$_SESSION['CSI']['SHV']->CN_original_invoice_items[$i]->item_id;
			}
			
			$_SESSION['var_annulation_facture']=1;
			$this->annulation_cancellation_facture();
		}
		else
		{
		    // get selected items
		    $_SESSION['CSI']['SHV']->CN_selected_invoice_items			 	=	$this->input->post("invoice_items")!=false ? $this->input->post("invoice_items"):array();
		}
		$_SESSION['CSI']['SHV']->CN_selected_invoice_items_sav			 	=	$this->input->post("sav")!=false ? $this->input->post("sav"):array();
		if(!empty($_SESSION['CSI']['SHV']->CN_selected_invoice_items_sav))
		{
            $this->sav();
		}
		//CN_original_invoice sale_id
		// unset last line processed
		$this															->	unset_last_line();

		if($_SESSION['var_annulation_facture']==1 || $_SESSION['var_sale_copy']==1 )
		{
			//Si l'avoir correspond à l'annulation totale de la facture alors on change tous les produits dans un tableau (here: $invoice_item_details_tab) 
			$invoice_item_details_tab										=	$this->Sale->get_sale_items($_SESSION['CSI']['SHV']->CN_original_invoice)->result_array();
            foreach ($invoice_item_details_tab as $ligne => $invoice_item_details) {
                $selected_item_id = $invoice_item_details['item_id'];
                // has a credit note already been applied to this invoice line?
                $serialnumber												=	$_SESSION['CSI']['SHV']->CN_original_invoice.'=> '.$invoice_item_details['line'];
                $CN_already_applied											=	$this->Sale->CN_already_applied($serialnumber);
                //if ($CN_already_applied)
                //{
                //	$_SESSION['error_code']									=	'07260';
                //	$this													->	_reload();
                //	return;
                //}
    
                // get next line number
                $line														=	$this->next_line_number();
    
                // get item info
                $_SESSION['CSI']['CT'][$line]								=	$this->Item->get_info($selected_item_id);
    
                // add line
                // set CN line
                $_SESSION['CSI']['CT'][$line]->CN_line						=	'N';

				// set quantity
                $_SESSION['CSI']['CT'][$line]->line_quantity				=	$invoice_item_details['quantity_purchased'] ;
    
				if ($_SESSION['var_annulation_facture']==1) {
                    $_SESSION['CSI']['CT'][$line]->description					=	'Avoir pour facture => '.$_SESSION['CSI']['SHV']->CN_original_invoice.', ligne => '.$invoice_item_details['line'];
                    $_SESSION['CSI']['CT'][$line]->serialnumber					=	$_SESSION['CSI']['SHV']->CN_original_invoice.'=> '.$invoice_item_details['line'];
                    // set quantity
                    $_SESSION['CSI']['CT'][$line]->line_quantity				=	$invoice_item_details['quantity_purchased'] * -1;
					$_SESSION['CSI']['CT'][$line]->CN_line						=	'Y';
				}
                // Set price
                $_SESSION['CSI']['CT'][$line]->line_priceTTC				=	$invoice_item_details['line_sales'] /  $invoice_item_details['quantity_purchased'];
                $_SESSION['CSI']['CT'][$line]->line_priceHT					=	$invoice_item_details['line_sales_after_discount'] /  $invoice_item_details['quantity_purchased'];
    
                // set kit related defaults for master line
                $_SESSION['CSI']['CT'][$line]->kit_item						=	'N';
                $_SESSION['CSI']['CT'][$line]->kit_option					=	null;
                $_SESSION['CSI']['CT'][$line]->kit_option_type				=	null;
                $_SESSION['CSI']['CT'][$line]->kit_cart_line				=	null;
    
                
                
                // set the discount and line offered
                $_SESSION['CSI']['CT'][$line]->line_discount				=	$this->discount_set();

                if ($_SESSION['click_avoir'] == 2 && $_SESSION['var_annulation_facture']==1) {
                    $_SESSION['CSI']['CT'][$line]->line_discount = $_SESSION['CSI']['SHV']->CN_selected_invoice_items[$line]->line_discount;
                    if ($_SESSION['CSI']['CT'][$line]->line_discount == null) {
                        $_SESSION['CSI']['CT'][$line]->line_discount = 0;
                    }
                }
                $_SESSION['CSI']['CT'][$line]->line_offered					=	$_SESSION['CSI']['CT'][$line]->offer_indicator;//$invoice_item_details['line_offered']; 
                
                // set other stuff from invoice line
                $_SESSION['CSI']['CT'][$line]->name							=	$invoice_item_details['line_name'];
                $_SESSION['CSI']['CT'][$line]->item_number					=	$invoice_item_details['line_item_number'];
                $_SESSION['CSI']['CT'][$line]->description					=	$invoice_item_details['description'];
                $_SESSION['CSI']['CT'][$line]->category_id					=	$invoice_item_details['line_category_id'];
                $_SESSION['CSI']['CT'][$line]->category						=	$invoice_item_details['line_category'];
                $_SESSION['CSI']['CT'][$line]->serialnumber					=	$invoice_item_details['serialnumber'];
    
                // get supplier info and append to array
                $_SESSION['transaction_info']->item_id						=	$selected_item_id;
                $preferred_supplier_data									=	array();
                $preferred_supplier_data									=	$this->Item->get_preferred_supplier()->result_array();
                // test returned array
                if (count($preferred_supplier_data) > 0) {
                    $_SESSION['CSI']['CT'][$line]->supplier_cost_price		=	$preferred_supplier_data[0]['supplier_cost_price'];
                } else {
                    $_SESSION['CSI']['CT'][$line]->supplier_cost_price		=	0;
                }
               
        
                // calculate values
                $this														->	line_values($line);
            }
			// set last cart line processed
			$_SESSION['CSI']['CT'][$line]->last_line					=	TRUE;
			$_SESSION['CSI']['CT'][$line]->colour						=	'yellow';
    	
    	}
		else
		{
			// read selected items
            foreach ($_SESSION['CSI']['SHV']->CN_selected_invoice_items as $selected_item_id) {
                // get invoice item details
                $invoice_item_details										=	$this->Sale->get_sale_item($_SESSION['CSI']['SHV']->CN_original_invoice, $selected_item_id)->row();
                if (!$invoice_item_details) { continue; }
                // has a credit note already been applied to this invoice line?
                $serialnumber												=	$_SESSION['CSI']['SHV']->CN_original_invoice.'=> '.$invoice_item_details->line;
                $CN_already_applied											=	$this->Sale->CN_already_applied($serialnumber);
                //if ($CN_already_applied)
                //{
                //	$_SESSION['error_code']									=	'07260';
                //	$this													->	_reload();
                //	return;
                //}
    
                // get next line number
                $line														=	$this->next_line_number();
    
                // get item info
                $_SESSION['CSI']['CT'][$line]								=	$this->Item->get_info($selected_item_id);
    
                // add line
                // set CN line
                $_SESSION['CSI']['CT'][$line]->CN_line						=	'Y';
    
                // Set price
                $_SESSION['CSI']['CT'][$line]->line_priceTTC				=	$invoice_item_details->line_sales /  $invoice_item_details->quantity_purchased;
                $_SESSION['CSI']['CT'][$line]->line_priceHT					=	$invoice_item_details->line_sales_after_discount /  $invoice_item_details->quantity_purchased;
    
                // set kit related defaults for master line
                $_SESSION['CSI']['CT'][$line]->kit_item						=	'N';
                $_SESSION['CSI']['CT'][$line]->kit_option					=	null;
                $_SESSION['CSI']['CT'][$line]->kit_option_type				=	null;
                $_SESSION['CSI']['CT'][$line]->kit_cart_line				=	null;
    
                // set quantity
                $_SESSION['CSI']['CT'][$line]->line_quantity				=	$invoice_item_details->quantity_purchased * -1;
    
                // set the discount and line offered
                $_SESSION['CSI']['CT'][$line]->line_discount				=	$this->discount_set();

                if ($_SESSION['click_avoir'] == 2) {
                    $_SESSION['CSI']['CT'][$line]->line_discount = $_SESSION['CSI']['SHV']->CN_selected_invoice_items[$line]->line_discount;
                    if ($_SESSION['CSI']['CT'][$line]->line_discount == null) {
                        $_SESSION['CSI']['CT'][$line]->line_discount = 0;
                    }
                }
                
                $_SESSION['CSI']['CT'][$line]->line_offered					=	'N';
    
                // set other stuff from invoice line
                $_SESSION['CSI']['CT'][$line]->name							=	$invoice_item_details->line_name;
                $_SESSION['CSI']['CT'][$line]->item_number					=	$invoice_item_details->line_item_number;
                $_SESSION['CSI']['CT'][$line]->category_id					=	$invoice_item_details->line_category_id;
                $_SESSION['CSI']['CT'][$line]->category						=	$invoice_item_details->line_category;
                $_SESSION['CSI']['CT'][$line]->description					=	'Avoir pour facture => '.$_SESSION['CSI']['SHV']->CN_original_invoice.', ligne => '.$invoice_item_details->line;
                $_SESSION['CSI']['CT'][$line]->serialnumber					=	$_SESSION['CSI']['SHV']->CN_original_invoice.'=> '.$invoice_item_details->line;
    
                // get supplier info and append to array
                $_SESSION['transaction_info']->item_id						=	$selected_item_id;
                $preferred_supplier_data									=	array();
                $preferred_supplier_data									=	$this->Item->get_preferred_supplier()->result_array();
                // test returned array
                if (count($preferred_supplier_data) > 0) {
                    $_SESSION['CSI']['CT'][$line]->supplier_cost_price		=	$preferred_supplier_data[0]['supplier_cost_price'];
                } else {
                    $_SESSION['CSI']['CT'][$line]->supplier_cost_price		=	0;
                }
    
                // calculate values
                $this														->	line_values($line);
            }
			// set last cart line processed
			$_SESSION['CSI']['CT'][$line]->last_line					=	TRUE;
			$_SESSION['CSI']['CT'][$line]->colour						=	'yellow';
		    
	    }
		// unset show dialog
		unset($_SESSION['show_dialog']);

		// reload
		$this->_reload();
	}

	function annulation_cancellation_facture()
	{
		//get payments for sale_id
	    $sale_payment = $this->Sale->get_sale_payments($_SESSION['CSI']['SHV']->CN_original_invoice);
    	$result_sale_payment = $sale_payment->result_array();

		//get all payments methods
		$payment_methods												=	array();
		$payment_methods												=	$this->Sale->get_payment_methods();

		//rapprochement pour obtenir le moyen de payment de la facture sale_id
		// et mise à jour de payment_amount
		foreach($result_sale_payment as $index_1=>$value_1)
		{
			foreach($payment_methods as $index_2=>$value_2)
			{
				if(($value_1['payment_method_code'])==($value_2['payment_method_code']))
				{
					$pmi=$value_2['payment_method_id'];
					$_SESSION['CSI']['PD'][$pmi] = $this->Paymethod->get_info($pmi);
					$_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC=-$value_1['payment_amount'];
					$data = array('mode' => "cancel", 'comment' => "Paiement modifié");
					$this					->	db->where('sale_id', $value_1['sale_id']);
					$this					->	db->where('branch_code', $this->config->item('branch_code'));
					$this					->	db->update('sales', $data);
				}
			}
		}
		
		$_SESSION['show_dialog']										=	6;
		//unset($_SESSION['show_dialog']);

		$this->_reload();
	}

	function sales_without_ticket()
    {
		$_SESSION['sales_without_ticket'] = '1';
		$this->complete();
		unset($_SESSION['sales_without_ticket']);
    }
	
	function Mail_Ticket_test()
	{
		
		/*Classe de traitement des exceptions et des erreurs*/
		require 'application/third_party/PHPMailer/src/Exception.php';
		/*Classe-PHPMailer*/
		require 'application/third_party/PHPMailer/src/PHPMailer.php';
		/*Classe SMTP nécessaire pour établir la connexion avec un serveur SMTP*/
		require 'application/third_party/PHPMailer/src/SMTP.php';
		/*Lors de la création d’un objet PHPMailer, passez le paramètre "true" pour activer les exceptions (messages en cas d’erreur)*/
		$email = new PHPMailer(true);
		$mail = new PHPMailer(true);
	
		try {
		// Paramètres du serveur SMTP
		$mail->isSMTP();                                      // Utiliser SMTP
		$mail->Host = 'ssl://mail.sonrisa-smile.com';                        // Serveur SMTP (par exemple Gmail)
		$mail->SMTPAuth = true;                                // Authentification SMTP activée
		$mail->Username = 'envoie-commande@sonrisa-smile.com';             // Votre email
		$mail->Password = 'J~?mbk+HhJ)W';                // Votre mot de passe
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    // Chiffrement STARTTLS
		$mail->Port = 465;                                    // Port SMTP

		// Expéditeur et destinataire
		$mail->setFrom('david@sonrisa-smile.com', 'Yes Store info');
		$mail->addAddress('david@yesstore.fr', 'D Deschamps'); // Ajouter un destinataire
		//$mail->addReplyTo('info@example.com', 'Information'); // Réponse à un autre email

		// Contenu de l'email
		$mail->isHTML(true);                                  // Format HTML
		$mail->Subject = 'Test d\'envoi d\'email via PHPMailer';
		$mail->Body    = 'Ceci est un test d\'envoi d\'email avec <b>PHPMailer</b>.';

		// Envoi de l'email
		$mail->send();
		echo 'L\'email a été envoyé avec succès.';
	} catch (Exception $e) {
		echo "L'envoi de l'email a échoué. Erreur: {$mail->ErrorInfo}";
	}
}
	//Fonction qui envoie le ticket de caisse par mail appelé dans le fichier /var/www/html/wrightetmathon/application/views/sales/payments.php
	function Mail_Ticket()
	{
		// Guard: if sale data no longer in session, redirect to register
		if (!isset($_SESSION['CSI']['SHV']->customer_id))
		{
			$_SESSION['error_code'] = '07290';
			redirect("sales");
			return;
		}

		require_once 'application/third_party/PHPMailer/src/Exception.php';
		require_once 'application/third_party/PHPMailer/src/PHPMailer.php';
		require_once 'application/third_party/PHPMailer/src/SMTP.php';

		// Get customer email before completing the sale
		$input['person_id'] = $_SESSION['CSI']['SHV']->customer_id;
		$customer_id = $_SESSION['CSI']['SHV']->customer_id;
		$data_people_with_email = $this->Person->get_info_people($input);
		$request = !empty($data_people_with_email) ? $data_people_with_email[0] : null;

		// Check customer has email
		if (!isset($request['email']) || empty($request['email']))
		{
			$_SESSION['variable_tampon_booleen']='0';
			$_SESSION['blocage_de_l_impression_du_ticket_de_caisse']='2';
			$_SESSION['error_code'] = '07290';
			$_SESSION['origin']='CN';
			redirect("customers/view/" . $customer_id);
			return;
		}

		$customer_email = $request['email'];

		// Prepare session for ticket generation
		unset($_SESSION['numero_ticket']);
		unset($_SESSION['message_mail']);
		unset($_SESSION['id_client']);
		$_SESSION['message_mail'] = '';
		$_SESSION['id_client'] = $customer_id;
		$_SESSION['variable_tampon_booleen'] = '1';

		// Complete the sale (generates ticket, populates message_mail and numero_ticket, clears CSI)
		$this->complete();

		// Send email
		try {
			$mail = new PHPMailer(true);
			$mail->isSMTP();
			$mail->Timeout = 10;
			$mail->SMTPDebug = 0;

			if (strpos($this->config->item('POemail'), 'sonrisa') > 0)
			{
				$mail->Host = 'mail.sonrisa-smile.com';
			}
			elseif (strpos($this->config->item('POemail'), 'yesstore') > 0)
			{
				$mail->Host = 'mail.yesstore.fr';
			}
			elseif (strpos($this->config->item('POemail'), 'gmail') > 0)
			{
				$mail->Host = 'smtp.gmail.com';
			}

			$mail->SMTPAuth = true;
			$mail->Username = $this->config->item('POemail');
			$mail->Password = $this->config->item('POemailpwd');
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$mail->Charset = 'utf-8';
			$mail->Port = 465;

			$mail->setFrom($this->config->item('email'), $this->config->item('company'));
			$mail->addAddress($customer_email, $customer_email);

			$mail->isHTML(true);
			$mail->Subject = 'Ticket de caisse numéro: ' . ($_SESSION['numero_ticket'] ?? '');

			// Fermeture propre du HTML email
			$_SESSION['message_mail'] = ($_SESSION['message_mail'] ?? '');
			$_SESSION['message_mail'] .= '<tr><td style="padding:20px 30px;text-align:center;background-color:#f8f9fa;border-top:1px solid #eeeeee;">';
			$_SESSION['message_mail'] .= '<strong style="font-size:15px;color:#333333;">Merci de votre confiance</strong>';
			$_SESSION['message_mail'] .= '</td></tr>';
			$_SESSION['message_mail'] .= '</table></td></tr></table></body></html>';

			$mail->Body = $_SESSION['message_mail'];
			$mail->send();
			$_SESSION['error_code'] = '07300';
		} catch (\Exception $e) {
			$_SESSION['error_code'] = '07301';
		}

		// Cleanup and redirect
		unset($_SESSION['numero_ticket']);
		unset($_SESSION['message_mail']);
		unset($_SESSION['id_client']);
		unset($_SESSION['variable_tampon_booleen']);
		redirect("sales");
	}
	
    //création des variables pour l'utilisateur du POS en mode multi vendeur 
	function set_vendeur($numero_button_vendeur)
	{
		$_SESSION['show_dialog']= 7;
		
		switch($numero_button_vendeur)
		{
			case '1':
				$_SESSION['numero_button_vendeur']['numero_button']=1;
				$_SESSION['numero_button_vendeur']['person_id_vendeur']['1']='';

			break;
            
			case '2':
			    $_SESSION['numero_button_vendeur']['numero_button']=2;
                $_SESSION['numero_button_vendeur']['person_id_vendeur']['2']='';
			break;
			
			case '3':
			    $_SESSION['numero_button_vendeur']['numero_button']=3;
                $_SESSION['numero_button_vendeur']['person_id_vendeur']['3']='';
			break;
			
			case '4':
			    $_SESSION['numero_button_vendeur']['numero_button']=4;
                $_SESSION['numero_button_vendeur']['person_id_vendeur']['4']='';
    
			break;
		}

		$_SESSION['indicateur_changement_vendeur']=1;
		$this->_reload();
	}

	//affiliation du compte du vendeur au bouton
	function register_vendeur()
    {
		$username = $_POST['pseudo'];
		$password = $_POST['password'];
	    if(!$this->Employee->login($username, $password))
	    {
	    	$_SESSION['error_code']										=	'05755';
	    	redirect("sales");
	    }
		$_SESSION['show_dialog'] = 0;
		switch($_SESSION['numero_button_vendeur']['numero_button'])
		{
			case 1:
			    if(($_SESSION['numero_button_vendeur']['person_id_vendeur']['2']!=$username) && ($_SESSION['numero_button_vendeur']['person_id_vendeur']['3']!=$username) && ($_SESSION['numero_button_vendeur']['person_id_vendeur']['4']!=$username))
				{
					$_SESSION['numero_button_vendeur']['person_id_vendeur']['1'] = $username;
					$_SESSION['last_click'] = 1;
					$_SESSION['G']->login_employee_info = $this->Employee->get_load_data_set_vendeur($_SESSION['numero_button_vendeur']['person_id_vendeur']['1']);
					$_SESSION['G']->login_employee_id = $_SESSION['G']->login_employee_info->person_id;
					$_SESSION['G']->login_employee_username = $_SESSION['G']->login_employee_info->username;
				}
				else
				{
					$_SESSION['error_code'] = '00130';
					redirect('sales');
				}
			break;

			case 2:
    			if(($_SESSION['numero_button_vendeur']['person_id_vendeur']['1']!=$username) && ($_SESSION['numero_button_vendeur']['person_id_vendeur']['3']!=$username) && ($_SESSION['numero_button_vendeur']['person_id_vendeur']['4']!=$username))
    			{
					$_SESSION['numero_button_vendeur']['person_id_vendeur']['2'] = $username;
					$_SESSION['last_click'] = 2;
					$_SESSION['G']->login_employee_info = $this->Employee->get_load_data_set_vendeur($_SESSION['numero_button_vendeur']['person_id_vendeur']['2']);
					$_SESSION['G']->login_employee_id = $_SESSION['G']->login_employee_info->person_id;
					$_SESSION['G']->login_employee_username = $_SESSION['G']->login_employee_info->username;
    			}
				else
				{
					$_SESSION['error_code'] = '00130';
					redirect('sales');
				}
			break;

			case 3:
    			if(($_SESSION['numero_button_vendeur']['person_id_vendeur']['1']!=$username) && ($_SESSION['numero_button_vendeur']['person_id_vendeur']['2']!=$username) && ($_SESSION['numero_button_vendeur']['person_id_vendeur']['4']!=$username))
    			{
					$_SESSION['numero_button_vendeur']['person_id_vendeur']['3'] = $username;
					$_SESSION['last_click'] = 3;
					$_SESSION['G']->login_employee_info = $this->Employee->get_load_data_set_vendeur($_SESSION['numero_button_vendeur']['person_id_vendeur']['3']);
					$_SESSION['G']->login_employee_id = $_SESSION['G']->login_employee_info->person_id;
					$_SESSION['G']->login_employee_username = $_SESSION['G']->login_employee_info->username;
    			}
				else
				{
					$_SESSION['error_code'] = '00130';
					redirect('sales');
				} 
			break;

			case 4:
    			if(($_SESSION['numero_button_vendeur']['person_id_vendeur']['1']!=$username) && ($_SESSION['numero_button_vendeur']['person_id_vendeur']['2']!=$username) && ($_SESSION['numero_button_vendeur']['person_id_vendeur']['3']!=$username))
    			{
					$_SESSION['numero_button_vendeur']['person_id_vendeur']['4'] = $username;
					$_SESSION['last_click'] = 4;
					$_SESSION['G']->login_employee_info = $this->Employee->get_load_data_set_vendeur($_SESSION['numero_button_vendeur']['person_id_vendeur']['4']);
					$_SESSION['G']->login_employee_id = $_SESSION['G']->login_employee_info->person_id;
					$_SESSION['G']->login_employee_username = $_SESSION['G']->login_employee_info->username;
			    }
				else
				{
					$_SESSION['error_code'] = '00130';
					redirect('sales');
				}
			break;
		}
		unset($_SESSION['numero_button_vendeur']['numero_button']);
		$this->_reload();
	}

	//chargement du compte du vendeur affilié au bouton
	function load_data_set_vendeur($numero_bouton)
	{
		$_SESSION['show_dialog'] = 0;
		$_SESSION['last_click']=0;
		switch($numero_bouton)
		{
			case 1:
		    	$_SESSION['G']->login_employee_info = $this->Employee->get_load_data_set_vendeur($_SESSION['numero_button_vendeur']['person_id_vendeur']['1']);
			    $_SESSION['last_click'] = 1;
			break;

			case 2:
			    $_SESSION['G']->login_employee_info = $this->Employee->get_load_data_set_vendeur($_SESSION['numero_button_vendeur']['person_id_vendeur']['2']);
			    $_SESSION['last_click'] = 2;
			break;

			case 3:
			    $_SESSION['G']->login_employee_info = $this->Employee->get_load_data_set_vendeur($_SESSION['numero_button_vendeur']['person_id_vendeur']['3']);
                $_SESSION['last_click'] = 3;
			break;

			case 4:
			    $_SESSION['G']->login_employee_info = $this->Employee->get_load_data_set_vendeur($_SESSION['numero_button_vendeur']['person_id_vendeur']['4']);
			    $_SESSION['last_click'] = 4;
			break;
		}
		$_SESSION['G']->login_employee_id = $_SESSION['G']->login_employee_info->person_id;
        $_SESSION['G']->login_employee_username = $_SESSION['G']->login_employee_info->username;
        redirect('sales');
	}
}
?>
