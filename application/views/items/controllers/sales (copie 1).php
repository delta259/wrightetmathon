<?php
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

		// load the data
		$this->_reload();
	}

	function item_search()
	{
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'),$this->input->post('limit'));
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
				if ($customer_id == NULL)
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

		// initialise
		$this															->	unset_customer();

		// get customer info
		$customer_info 													= 	$this->Customer->get_info($customer_id);

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
		$_SESSION['CSI']['SHV']->client_average_basket					=	round($_SESSION['CSI']['CI']->sales_ht / $_SESSION['CSI']['CI']->sales_number_of, 2);
		$_SESSION['CSI']['HH']											=	$this->reports->get_headers($transaction_type);
		$_SESSION['CSI']['HS']											=	$summary_data;
		$_SESSION['CSI']['HD']											=	$details_data;
		$_SESSION['CSI']['HO']											=	$overall_summary_data;

		// now reset each cart line
		foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			// set the discount
			$_SESSION['CSI']['CT'][$line]->line_discount				=	$this->discount_set();
			// set line offered
			$_SESSION['CSI']['CT'][$line]->line_offered 				= 	'N';
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
			$_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC			+=	$amt;
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
			// set payment amount
			$_SESSION['CSI']['PD'][$pmi]->payment_amount_TTC			=	$amt;
		}

		// reload
		$this															->	_reload();
	}

	function delete_payment($pmi)
	{
		unset($_SESSION['CSI']['PD'][$pmi]);
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
			// is this a kit item?
			if ($_SESSION['CSI']['CT'][$line]->kit_item == 'Y')
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

			// verifications passed
			// set new quantity
			$_SESSION['CSI']['CT'][$line]->line_quantity 				=	$this->input->post("line_quantity");
			// set out of stock indicator
			if ($_SESSION['CSI']['CT'][$line]->quantity < $_SESSION['CSI']['CT'][$line]->line_quantity)
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
		//	$_SESSION['CSI']['CT'][$line]->line_discount				=	100;
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
					if ($_SESSION['CSI']['CT'][$line]->line_offered == 'N' AND $_SESSION['CSI']['CT'][$line]->CN_line != 'Y')
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

		//
		// KITS
		//

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
		$_SESSION['CSI']['SHV']->transaction_code						= 	$this->Transaction->get_transaction_code($_SESSION['CSI']['SHV']->mode);
		$_SESSION['CSI']['SHV']->transaction_title						= 	$this->lang->line('reports_'.$_SESSION['CSI']['SHV']->mode);

		// get transaction updatestock indicator
		$_SESSION['CSI']['SHV']->transaction_update_stock				= 	$this->Transaction->get_transaction_updatestock($_SESSION['CSI']['SHV']->mode);

		// set transaction time
		$_SESSION['CSI']['SHV']->transaction_time						= 	date('d/m/Y H:i:s');

		// set comment
		if ($_SESSION['CSI']['SHV']->comment == NULL)
		{
			$_SESSION['CSI']['SHV']->comment							=	$_SESSION['CSI']['SHV']->transaction_title;
		}

// ALL GOOD, so SAVE sale to database

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
			// accumulate fidelity points for this sale
			$new_points													=	$_SESSION['CSI']['CI']->fidelity_points + floor($_SESSION['CSI']['SHV']->header_valueAD_TTC / $this->config->item('fidelity_rule'));

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
			$sales_payment_data 										= 	array	(
																					'sale_id'					=>	$_SESSION['CSI']['SHV']->sale_id,
																					'payment_method_code'		=>	$payment->payment_method_code,
																					'payment_type'				=>	$payment->payment_method_description,
																					'payment_amount'			=>	$payment->payment_amount_TTC,
																					'branch_code'				=>	$this->config->item('branch_code')
																					);
			// save the payment
			$this														->	Sale->save_sales_payment($sales_payment_data);

			// for clients with fidelity and this is a fidelity card payment
			// ...reduce client fidelity points
			if ($_SESSION['CSI']['CI']->fidelity_flag == 'Y' AND $payment->payment_method_fidelity_flag == 'Y')
			{
				// calculate pay points
				$pay_points												=	ceil($payment->payment_amount_TTC / $this->config->item('fidelity_value'));
				$new_pay_points 										=   (floor($_SESSION['CSI']['SHV']->header_valueAD_TTC - floor($payment->payment_amount_TTC) / $this->config->item('fidelity_rule')));

				// now reduce the fidelity points - $new_points has been calculated above for client attracting fidelity
				$new_points												=	$_SESSION['CSI']['CI']->fidelity_points - $pay_points  - $new_pay_points ;

				// test for less than zero points - set to 0 if so
				if ($new_points < 0)
				{
					$new_points											=	0;
				}

				// update fidelity value
				$_SESSION['CSI']['SHV']->fidelity_value					=	round($new_points * $this->config->item('fidelity_value'), 2);

				// update the client record
				$_SESSION['transaction_info']							=	new stdClass();
				$_SESSION['new']										=	0;
				$_SESSION['transaction_info']->person_id				=	$_SESSION['CSI']['SHV']->customer_id;
				$_SESSION['transaction_info']->fidelity_points			=	$new_points;

				$this													->	Customer->save_counts();
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
										'item_cost_price' 				=>	$cart_line->supplier_cost_price,
										'item_unit_price'				=>	$cart_line->line_priceHT,
										'discount_percent'				=>	$cart_line->line_discount,
										'line_sales_before_discount'	=>	$cart_line->line_valueBD_HT,
										'line_discount'					=>	$cart_line->line_discount,
										'line_sales_after_discount'		=>	$cart_line->line_valueAD_HT,
										'line_tax'						=>	$cart_line->line_taxAD,
										'line_sales'					=>	$cart_line->line_valueAD_TTC,
										'line_cost'						=>	$cart_line->line_cost_HT,
										'line_profit'					=>	$cart_line->line_profit_HT,
										'line_tax_percentage'			=>	$_SESSION['CSI']['SHV']->tax_percent,
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
				$_SESSION['transaction_info']->quantity					=	$cart_line->quantity - $cart_line->line_quantity;
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

				// update stock valuation records
				if ($_SESSION['transaction_info']->quantity < 0)
				{
					$this												->	Item->value_delete_item_id($cart_line->item_id);
					$valuation_data										=	array	(
																					'value_item_id'		=>	$cart_line->item_id,
																					'value_cost_price'	=>	$cart_line->supplier_cost_price,
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
																					'trans_stock_before'=>	$cart_line->quantity,
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

	// END - save the SALES ITEMS TAXES

	// START - POST database save operations

		// Print a ticket
		// open ticket printer
		$printer														=	$this->config->item('ticket_printer');
		$ph 															=	fopen($printer, "w");

		// if it opens, print a ticket
		if ($ph)
		{
			$_SESSION['CSI']['SHV']->ph									=	$ph;
			$this->load->view("sales/ticket");
		}

	// END - POST database save operations

	// ALL DONE - clear sale and return
		// unset the global Current sales info
		unset($_SESSION['CSI']);
		unset($_SESSION['show_dialog']);
		$_SESSION['error_code']											=	'05840';
		redirect("sales");
		return;
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

		// set up the lang line
		$lang_line 														= 	'reports_'.$transaction_info['mode'];

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
		$data['transaction_title']										=	$this->lang->line($lang_line);
		$data['transaction_time']										= 	date('d/m/Y H:i:s', strtotime($transaction_info['sale_time']));
		$data['payments']												=	$payments;
		$data['amount_change']											=	$transaction_info['amount_change'];
		$data['transaction_subtype'] 									= 	$transaction_info['mode'];
		$data['payment_type'] 											= 	$transaction_info['payment_type'];
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
	//-------------------------------------------------------------->
	// get all the data required for the targets area
	//-------------------------------------------------------------->
		//
		unset($_SESSION['CSI']['TT']);

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
		$_SESSION['CSI']['TT']->dailytarget								=	round(($_SESSION['CSI']['TT']->monthlytarget / $_SESSION['CSI']['TT']->averagenumberopendays), 0, PHP_ROUND_HALF_UP);

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

		// get the daily sales (& returns) data, need to get day by day as we need to count the number of days.
		$report_data 					= 	$this->Sale->get_sales_data(array('start_date'=>$start_date, 'end_date'=>$end_date, 'transaction_subtype' => $transaction_subtype));

		foreach($report_data as $row)
		{
			$count 						= 	$count + 1;
			$monthlyrealised			= 	$monthlyrealised + $row['subtotal'];
		}

		// Now calculate the realised to date fields
		$_SESSION['CSI']['TT']->monthlyrealised							=	round($monthlyrealised, 0, PHP_ROUND_HALF_UP);
		$_SESSION['CSI']['TT']->dailyrealised							=	round(($monthlyrealised / $count), 0, PHP_ROUND_HALF_UP);
		$_SESSION['CSI']['TT']->monthlyrealisedpercent					=	round(($monthlyrealised / $_SESSION['CSI']['TT']->monthlytarget * 100), 0, PHP_ROUND_HALF_UP);

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
		foreach($report_data as $row)
		{
			$dailydone													= 	$dailydone + $row['subtotal'];
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
				$_SESSION['CSI']['CT'][$line]->line_priceTTC			=	$prices['price_with_tax'];
				$_SESSION['CSI']['CT'][$line]->line_priceHT				=	$prices['price_no_tax'];
			}
			else
			{
				$_SESSION['CSI']['CT'][$line]->line_priceTTC			=	0;
				$_SESSION['CSI']['CT'][$line]->line_priceHT				=	0;
			}

			// calculate values
			$this														->	line_values($line);
		}

		// calculate totals
		$this															->	header_values();
		$this															->	payment_values();

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

		// output the view
		// set origin
		$this->load->view("sales/register", $data);
	}

    function cancel_sale()
    {
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
		$_SESSION['error_code']											=	'06030';
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
				if (round($_SESSION['CSI']['SHV']->header_amount_due_TTC, 2) != 0)
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
			$_SESSION['CSI']['SHV']->header_profit_HT					+=	$cart_line->line_profit_HT;
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
				$_SESSION['CSI']['SHV']->header_profit_HT_normal		+=	$cart_line->line_profit_HT;
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
				$_SESSION['CSI']['CT'][$line]->line_offered				=	'N';
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

		// set out of stock indicator
		if ($_SESSION['CSI']['CT'][$line]->quantity < $_SESSION['CSI']['CT'][$line]->line_quantity)
		{
			$_SESSION['CSI']['CT'][$line]->colour						=	'orange';
			$_SESSION['error_code']										=	'06010';
		}

		// return
		return;
	}

	function payments()
	{
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
						if ($_SESSION['CSI']['CT']->line_offered == 'Y')
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
					}
				}
			}
			else
			{
				// OK so load all other payment methods except giftcard payment method
				if ($payment_method['payment_method_giftcard_flag'] != 'Y')
				{
					$_SESSION['CSI']['PM'][$payment_method['payment_method_id']] =	$payment_method['payment_method_description'];
				}
			}
		}

		// add blank line to payment method dropdown to force user to select one without changing existing numeric keys
		// cannot use array_unshift for this as it re-numbers numeric keys,
		// so I'll just add the arrrays together
		$add_select 													=	array	(
																					0	=>	$this->lang->line('common_please_select')
																					);
		$_SESSION['CSI']['PM'] 											=	$add_select + $_SESSION['CSI']['PM'];

		// set default payment option = select message
		$_SESSION['CSI']['SHV']->default_payment_option					=	0;

		// reload
    	$this->_reload();
	}

	function add_kit_lines($master_line)
	{
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
		}
	}

	function CN_select_invoice()
	{
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

		// reload
    	$this->_reload();
	}

	function CN_select_invoice_item()
	{
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
			$_SESSION['error_code']										=	'07230';
			$this														->	_reload();
			return;
		}
		if ($invoice_header->customer_id != $_SESSION['CSI']['SHV']->customer_id)
		{
			$_SESSION['error_code']										=	'07230';
			$this														->	_reload();
			return;
		}

		// original invoice date cannot be older than one (three)  month ago.
		$one_month_ago													=	date("Y-m-d",strtotime("-3 month"));
		if (date($invoice_header->sale_time) < $one_month_ago)
		{
			$_SESSION['error_code']										=	'07240';
			$this														->	_reload();
			return;
		}

		// get invoice items
		$_SESSION['CSI']['SHV']->CN_original_invoice_items				=	$this->Sale->get_sale_items($_SESSION['CSI']['SHV']->CN_original_invoice)->result();
		if (!$_SESSION['CSI']['SHV']->CN_original_invoice_items)
		{
			$_SESSION['error_code']										=	'07250';
			$this														->	_reload();
			return;
		}

		// SECOND show items on invoice
		// initialise
		$_SESSION['show_dialog']										=	5;

		// reload
    	$this->_reload();
	}

	function CN_add_line()
	{
		// get selected items
		$_SESSION['CSI']['SHV']->CN_selected_invoice_items			 	=	$this->input->post("invoice_items")!=false ? $this->input->post("invoice_items"):array();

		// unset last line processed
		$this															->	unset_last_line();

		// read selected items
		foreach ($_SESSION['CSI']['SHV']->CN_selected_invoice_items as $selected_item_id)
		{
			// get invoice item details
			$invoice_item_details										=	$this->Sale->get_sale_item($_SESSION['CSI']['SHV']->CN_original_invoice, $selected_item_id)->row();

			// has a credit note already been applied to this invoice line?
			$serialnumber												=	$_SESSION['CSI']['SHV']->CN_original_invoice.'=> '.$invoice_item_details->line;
			$CN_already_applied											=	$this->Sale->CN_already_applied($serialnumber);
			if ($CN_already_applied)
			{
				$_SESSION['error_code']									=	'07260';
				$this													->	_reload();
				return;
			}

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
			$_SESSION['CSI']['CT'][$line]->kit_option					=	NULL;
			$_SESSION['CSI']['CT'][$line]->kit_option_type				=	NULL;
			$_SESSION['CSI']['CT'][$line]->kit_cart_line				=	NULL;

			// set quantity
			$_SESSION['CSI']['CT'][$line]->line_quantity				=	$invoice_item_details->quantity_purchased * -1;

			// set the discount and line offered
			$_SESSION['CSI']['CT'][$line]->line_discount				=	$this->discount_set();
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
			if (count($preferred_supplier_data) > 0)
			{
				$_SESSION['CSI']['CT'][$line]->supplier_cost_price		=	$preferred_supplier_data[0]['supplier_cost_price'];
			}
			else
			{
				$_SESSION['CSI']['CT'][$line]->supplier_cost_price		=	0;
			}

			// calculate values
			$this														->	line_values($line);

			// set last cart line processed
			$_SESSION['CSI']['CT'][$line]->last_line					=	TRUE;
			$_SESSION['CSI']['CT'][$line]->colour						=	'yellow';
		}

		// unset show dialog
		unset($_SESSION['show_dialog']);

		// reload
		$this->_reload();
	}
}
?>
