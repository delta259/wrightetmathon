<?php
class Customers extends CI_controller
{	
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"9";

		// set data array
		$data = array();

		// manage session
		$_SESSION['controller_name']	=	strtolower(get_class($this));
		$_SESSION['module_info']		=	$this->Module->get_module_info($_SESSION['controller_name'])->row_array();
		unset($_SESSION['report_controller']);

		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$data['title']	=	$this->lang->line('common_undelete');
					$_SESSION['controller_name'] = $_SESSION['controller_name'] . '_inactifs';
			break;

			default:
					$data['title']	=	'';
					$_SESSION['controller_name'] = $_SESSION['controller_name'] . '_actifs';
			break;
		}

		// set up the pagination
		$config						=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 		= 	site_url('/customers/index');

		// override per_page from session if set
		if (isset($_SESSION['customers_per_page'])) {
			$config['per_page'] = ($_SESSION['customers_per_page'] == 0) ? 100000 : $_SESSION['customers_per_page'];
		}

		// Check filtre (search lock) state
		switch ($_SESSION['filtre'] ?? 0)
		{
			case 1:
				if (isset($_SESSION['filtre_recherche'])) {
					$data['customers'] = $this->Customer->search($_SESSION['filtre_recherche']);
				} else {
					$data['customers'] = $this->Customer->get_all($config['per_page'], $this->uri->segment($config['uri_segment']));
				}
			break;

			default:
				unset($_SESSION['filtre_recherche']);
				$data['customers'] = $this->Customer->get_all($config['per_page'], $this->uri->segment($config['uri_segment']));
			break;
		}

		// set up the pagination - phase 2
		$config['total_rows'] 		= 	$this->Customer->count_all();
		$this						->	pagination->initialize($config);
		$data['links']				=	$this->pagination->create_links();
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->get_form_width();

		// show data
		$this						->	load->view('customers/manage', $data);
	}
	
	/*
	Returns customer table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search						=	$this->input->post('search');
		$_SESSION['filtre_recherche'] = $search;

		// set module id
		$_SESSION['module_id']		=	"9";
		$_SESSION['controller_name']=	strtolower(get_class($this)) . '_actifs';

		// initialise
		$data						=	array();

		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$data['title']	=	$this->lang->line('common_undelete');
					$_SESSION['controller_name'] = strtolower(get_class($this)) . '_inactifs';
			break;
			default:
					$data['title']	=	'';
			break;
		}

		$data['customers']			=	$this->Customer->search($search);
		$data['links']				=	'';
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->get_form_width();

		$this						->	load->view('customers/manage', $data);
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Customer->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
	
	function sort($col = 'last_name', $dir = 'asc')
	{
		$allowed = array(
			'account_number', 'last_name', 'first_name', 'email',
			'phone_number', 'city', 'sales_ht', 'fidelity_points',
		);
		if (in_array($col, $allowed)) {
			$_SESSION['customers_sort_col'] = $col;
			$_SESSION['customers_sort_dir'] = ($dir === 'desc') ? 'desc' : 'asc';
		}
		redirect('customers');
	}

	function per_page($n = 20)
	{
		$allowed = array(20, 50, 100, 500, 0);
		$n = (int)$n;
		if (in_array($n, $allowed)) {
			$_SESSION['customers_per_page'] = $n;
		}
		redirect('customers');
	}

	function filtre()
	{
		switch ($_SESSION['filtre'] ?? 0)
		{
			case 1:
				$_SESSION['filtre'] = 0;
				unset($_SESSION['filtre_recherche']);
			break;

			default:
				$_SESSION['filtre'] = 1;
			break;
		}
		redirect("customers");
	}

	function toggle_deleted()
	{
		if (($_SESSION['undel'] ?? 0) == 1) {
			unset($_SESSION['undel']);
		} else {
			$_SESSION['undel'] = 1;
		}
		unset($_SESSION['origin']);
		redirect("customers");
	}

	function ajax_toggle_status($customer_id = null)
	{
		header('Content-Type: application/json');

		if (!$customer_id) {
			echo json_encode(array('success' => false, 'message' => 'ID client manquant'));
			return;
		}

		$customer = $this->Customer->get_info($customer_id);
		if (!$customer || !isset($customer->person_id) || $customer->person_id == '') {
			echo json_encode(array('success' => false, 'message' => 'Client non trouvé'));
			return;
		}

		$current_deleted = $customer->deleted;
		$new_deleted = ($current_deleted == 0) ? 1 : 0;

		$this->db->where('person_id', $customer_id);
		$this->db->where('customers.branch_code', $this->config->item('branch_code'));
		$this->db->update('customers', array('deleted' => $new_deleted));

		echo json_encode(array('success' => true, 'new_status' => $new_deleted));
	}

	/*
	AJAX: Returns sales history for a customer (last 50 sales)
	*/
	function ajax_customer_sales($person_id = null)
	{
		header('Content-Type: application/json');

		if (!$person_id) {
			echo json_encode(array('success' => false, 'message' => 'ID client manquant'));
			return;
		}

		// Load the specific_customer report model
		$this->load->model('reports/Specific_customer');

		// Get number format
		$pieces = explode("/", $this->config->item('numberformat'));
		$decimals = $pieces[0];
		$dec_point = $pieces[1];
		$thousands_sep = $pieces[2];

		// Set date range: last 3 years to today
		$inputs = array(
			'start_date'          => date('Y-m-d', strtotime('-3 years')),
			'end_date'            => date('Y-m-d'),
			'person_id'           => $person_id,
			'transaction_subtype' => 'sales$returns',
			'limit'               => 50
		);

		// Get sales data
		$data = $this->Specific_customer->getData($inputs);

		// Get summary totals
		$summary_totals = $this->Specific_customer->getSummaryData($inputs);

		// Get customer info
		$customer = $this->Customer->get_info($person_id);
		$customer_name = '';
		if ($customer && isset($customer->first_name)) {
			$customer_name = trim($customer->first_name . ' ' . $customer->last_name);
		}

		// Build response
		$sales = array();
		foreach ($data['summary'] as $key => $sale) {
			$sale_entry = array(
				'sale_id'        => $sale['transaction_id'],
				'mode'           => $sale['mode'] ?? 'sales',
				'date'           => $sale['transaction_date'],
				'employee'       => $sale['employee_name'],
				'subtotal_ht'    => number_format((float)($sale['subtotal_after_discount'] ?? 0), $decimals, $dec_point, $thousands_sep),
				'tax'            => number_format((float)($sale['overall_tax'] ?? 0), $decimals, $dec_point, $thousands_sep),
				'total_ttc'      => number_format((float)($sale['overall_total'] ?? 0), $decimals, $dec_point, $thousands_sep),
				'payment_type'   => $sale['payment_type'] ?? '',
				'comment'        => $sale['comment'] ?? '',
				'items'          => array()
			);

			// Add line items
			if (isset($data['details'][$key])) {
				foreach ($data['details'][$key] as $item) {
					$sale_entry['items'][] = array(
						'ref'      => $item['item_number'] ?? '',
						'name'     => $item['name'],
						'category' => $item['category'] ?? '',
						'qty'      => (int)$item['quantity_purchased'],
						'price'    => number_format((float)($item['item_unit_price'] ?? 0), $decimals, $dec_point, $thousands_sep),
						'total'    => number_format((float)($item['line_sales_after_discount'] ?? 0), $decimals, $dec_point, $thousands_sep),
						'discount' => $item['discount_percent'] ?? 0
					);
				}
			}

			$sales[] = $sale_entry;
		}

		echo json_encode(array(
			'success'       => true,
			'customer_name' => $customer_name,
			'invoice_count' => (int)($summary_totals['invoice_count'] ?? 0),
			'total_ht'      => number_format((float)($summary_totals['subtotal'] ?? 0), $decimals, $dec_point, $thousands_sep),
			'total_tax'     => number_format((float)($summary_totals['tax'] ?? 0), $decimals, $dec_point, $thousands_sep),
			'total_ttc'     => number_format((float)($summary_totals['total'] ?? 0), $decimals, $dec_point, $thousands_sep),
			'sales'         => $sales
		));
	}

	/*
	Loads the customer edit form
	*/
	function	view													($customer_id=-1, $origin='0', $preserve_error=false)
	{
		// Clear stale error codes from other modules (e.g., stock warnings from sales)
		if (!$preserve_error)
		{
			unset($_SESSION['error_code']);
		}

		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['customer_id']										=	$customer_id;

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
		switch ($customer_id) 
		{
//			case -2:
//				//import customer from other shop
//				$_SESSION['$title']								= $this->lang->line($_SESSION['controller_name'].'_new');
//				$_SESSION['new']								= 1;
//				$_SESSION['selected_on_stop_indicator']			= 'N';
//				$_SESSION['selected_taxable']					= 'Y';
//				$_SESSION['selected_fidelity_flag']				= 'Y';
//				$_SESSION['selected_profile_id']				= $this->config->item('profile_id');
//				$_SESSION['transaction_info']->fidelity_value	= $_SESSION['transaction_info']->fidelity_points * $this->config->item('fidelity_value');		
//
//			break;
//
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
					$_SESSION['selected_on_stop_indicator']				=	'N';
					$_SESSION['selected_taxable']						=	'Y';
					$_SESSION['selected_fidelity_flag']					=	'Y';
					$_SESSION['selected_profile_id']					=	$this->config->item('profile_id');
					$_SESSION['transaction_info']->fidelity_points		=	0;
					$_SESSION['transaction_info']->fidelity_value		=	0;
					$_SESSION['transaction_info']->dob_day				=	01;
					$_SESSION['transaction_info']->dob_month			=	01;
					$_SESSION['transaction_info']->dob_year				=	1970;
					
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Customer->get_info($customer_id);
					$_SESSION['selected_on_stop_indicator']				=	$_SESSION['transaction_info']->on_stop_indicator;
					$_SESSION['selected_taxable']						=	$_SESSION['transaction_info']->taxable;
					$_SESSION['selected_fidelity_flag']					=	$_SESSION['transaction_info']->fidelity_flag;
					$_SESSION['transaction_info']->fidelity_value		=	$_SESSION['transaction_info']->fidelity_points * $this->config->item('fidelity_value');	
					
					if (empty($_SESSION['transaction_info']->profile_id))
					{
						$_SESSION['selected_profile_id']				=	$this->config->item('profile_id');
					}
					else
					{
						$_SESSION['selected_profile_id']				=	$_SESSION['transaction_info']->profile_id;
					}
					
					$_SESSION['full_name_out']							=	$this->Common_routines->format_full_name($_SESSION['transaction_info']->last_name, $_SESSION['transaction_info']->first_name);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['full_name_out'];
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['full_name_out'];
						break;	
					}
					if($_SESSION['selected_fidelity_flag'] == 'Y')
					{
						//
						$_SESSION['solde_distributeur_vapeself'] = 0;    //$_SESSION['transaction_info']->solde_vs;
					}
					unset($_SESSION['new']);
			break;
		}
		if($_SESSION['transaction_info']->dob_day<10)
		{
			$_SESSION['transaction_info']->dob_day="0".$_SESSION['transaction_info']->dob_day;
		}
		if($_SESSION['transaction_info']->dob_month<10)
		{
			$_SESSION['transaction_info']->dob_month="0".$_SESSION['transaction_info']->dob_month;
		}

        //chargement du model vapeself 
		$this->load->model("Vapeself_model");
		
		// get the number format -->
	    $pieces = array();
	    $pieces = explode("/", $this->config->item('numberformat'));
		//récupération de la valeur du solde du client
		$vs_solde_card = $this->Vapeself_model->get_vs_solde_card($customer_id);
		$vs_solde_card_float = floatval($vs_solde_card[0]['solde']);
		$_SESSION['transaction_info']->vs_solde = number_format($vs_solde_card_float, $pieces[0], $pieces[1], $pieces[2]);


		if($_SESSION['variable_tampon_booleen']=='0' || $_SESSION['variable_tampon_booleen']=='1')
		{
		//unset($_SESSION['variable_tampon_booleen']);
			$_SESSION['origin']='ST';
			$_SESSION['controller_name']='sales';
			if(!isset($_POST['email']))
			{
				$_SESSION['blocage_de_l_impression_du_ticket_de_caisse']=3;
			}
		}
		redirect("customers");
	}
	
	function update_VS_client($customer_id=-1)
    {
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['show_dialog']										=	1;
    	// set data
		switch ($customer_id) 
		{
			// create new
			case	-1:
			/*
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
					$_SESSION['selected_on_stop_indicator']				=	'N';
					$_SESSION['selected_taxable']						=	'Y';
					$_SESSION['selected_fidelity_flag']					=	'Y';
					$_SESSION['selected_profile_id']					=	$this->config->item('profile_id');
					$_SESSION['transaction_info']->fidelity_points		=	0;
					$_SESSION['transaction_info']->fidelity_value		=	0;
					$_SESSION['transaction_info']->dob_day				=	01;
					$_SESSION['transaction_info']->dob_month			=	01;
					$_SESSION['transaction_info']->dob_year				=	1970;
					
			break;//*/
			$_SESSION['error_code']										=	'07340';
			redirect("customers");
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Customer->get_info($customer_id);
				/*	if(isset($_POST['code_carte_distributeur_vapeself']))
	            	{
                		//vérification que le code carte est composé de 4 caractéres		
                		$code_carte_distributeur_vapeself = $this->input->post('code_carte_distributeur_vapeself');
                		if((strlen($code_carte_distributeur_vapeself) == 4) && ($code_carte_distributeur_vapeself[0] != ' ') && ($code_carte_distributeur_vapeself[1] != ' ') && ($code_carte_distributeur_vapeself[2] != ' ') && ($code_carte_distributeur_vapeself[3] != ' '))
                		{
                			//
                			//echo 'coucou';
                		} 
                		else
                		{
                			//
                			redirect("customers");
                		}
	                }//*/
	/*				$_SESSION['selected_on_stop_indicator']				=	$_SESSION['transaction_info']->on_stop_indicator;
					$_SESSION['selected_taxable']						=	$_SESSION['transaction_info']->taxable;
					$_SESSION['selected_fidelity_flag']					=	$_SESSION['transaction_info']->fidelity_flag;
					$_SESSION['transaction_info']->fidelity_value		=	$_SESSION['transaction_info']->fidelity_points * $this->config->item('fidelity_value');	
					
					if (empty($_SESSION['transaction_info']->profile_id))
					{
						$_SESSION['selected_profile_id']				=	$this->config->item('profile_id');
					}
					else
					{
						$_SESSION['selected_profile_id']				=	$_SESSION['transaction_info']->profile_id;
					}
					
					$_SESSION['full_name_out']							=	$this->Common_routines->format_full_name($_SESSION['transaction_info']->last_name, $_SESSION['transaction_info']->first_name);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['full_name_out'];
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['full_name_out'];
						break;	
					}
					if($_SESSION['selected_fidelity_flag'] == 'Y')
					{
						//
						$_SESSION['solde_distributeur_vapeself'] = 0;    //$_SESSION['transaction_info']->solde_vs;
					}//*/
					unset($_SESSION['new']);
					$data_Client = array();
                    $data_Client['ADRESSE1'] = $_SESSION['transaction_info']->address_1;
                    $data_Client['ADRESSE2'] = $_SESSION['transaction_info']->address_2;
                    $data_Client['ADRESSE3'] = $_SESSION['transaction_info']->state;
                    $data_Client['CODECARTE'] = $_SESSION['transaction_info']->card_code;
                    $data_Client['CODEPOSTAL'] = $_SESSION['transaction_info']->zip;
                    $data_Client['EMAIL'] = $_SESSION['transaction_info']->email;
                    $data_Client['NOM'] = $_SESSION['transaction_info']->last_name;
                    $data_Client['PRENOM'] = $_SESSION['transaction_info']->first_name;
					$data_Client['NUMEROCARTE'] = $_SESSION['transaction_info']->profile_reference;
					switch($_SESSION['transaction_id']->profile_id)
					{
						//profile par defaut
						case 1:
						    $data_Client['REMISE'] = '0';
						break;
						
						//commercants
						case 2:
							$data_Client['REMISE'] = '15';
						break;

						//salaries
						case 3:
							$data_Client['REMISE'] = '30';
						break;
					}
                    $data_Client['REMISE'] = '0';
                    $data_Client['VILLE'] = $_SESSION['transaction_info']->city;
					$data_Client['VOTREID'] = $_SESSION['transaction_info']->person_id;
	//				$data_Client_datenaissance =  $_SESSION['transaction_info']->dob_day . '/' . $_SESSION['transaction_info']->dob_month . '/' . $_SESSION['transaction_info']->dob_year;
	                $data_Client['DATENAISSANCE'] = intval($_SESSION['transaction_info']->dob_day) . '/' . intval($_SESSION['transaction_info']->dob_month) . '/' . intval($_SESSION['transaction_info']->dob_year);
                    $data_Client['DATENAISSANCE'] = strval($_SESSION['transaction_info']->dob_day . '/' . $_SESSION['transaction_info']->dob_month . '/' . $_SESSION['transaction_info']->dob_year);
	//				$data_Client['DATENAISSANCE'] = $_SESSION['transaction_info']->dob_day . '-' . $_SESSION['transaction_info']->dob_month . '-' . $_SESSION['transaction_info']->dob_year;
					
	                if(strlen(strval($_SESSION['transaction_info']->dob_month)) != 2)
	                {
	                	$_SESSION['transaction_info']->dob_month = '0' . $_SESSION['transaction_info']->dob_month;
	                }
	                if(strlen(strval($_SESSION['transaction_info']->dob_day)) != 2)
					{
						$_SESSION['transaction_info']->dob_day = '0' . $_SESSION['transaction_info']->dob_day;
					}
					
	                $data_Client['DATENAISSANCE'] = $_SESSION['transaction_info']->dob_year . '/' . $_SESSION['transaction_info']->dob_month . '/' . $_SESSION['transaction_info']->dob_day;
	//                $data_Client['DATENAISSANCE'] = "$data_Client_datenaissance";
            break;
		}
		//chargement de la class vapeself 
		$this->load->library("../controllers/vapeself");

		//demande de token
		$token = $this->vapeself->get_token();
		
		//mise à jour ou inséretion du client
		$return_MajClient = $this->vapeself->post_MajClient($token, $data_Client);
		switch ($return_MajClient) 
		{
			case "Ok":
			    $_SESSION['error_code']		=	'07370';


            break;
			
			default:

			break;
		}
//		unset($_SESSION['show_dialog']);
		redirect("customers");

	}

	
	function view_set_credit($customer_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		//$_SESSION['customer_id']										=	$customer_id;
		$_SESSION['transaction_info']						=	$this->Customer->get_info($customer_id);
//		$_SESSION['CSI']['SHV']						=	$this->Customer->get_info($customer_id);
//		$_SESSION['CSI']['SHV']						=	$this->Customer->get_info($customer_id);
		
		/*
		// load pay methods drop down array
		$payment_methods												=	array();
		$payment_methods												=	$this->Sale->get_payment_methods();
		//*/
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
//		$_SESSION['show_dialog']										=	6;
		
		// set data
		switch ($customer_id) 
		{
			// create new
			case	-1:
					
			break;
			
			// update existing
			default:

			break;
		}


//        $solde_distributeur_vapeself = $_POST['solde_distributeur_vapeself'];
     //   $_SESSION[''] = 
//		$_SESSION['CSI']['SHV']->header_amount_due_TTC = $solde_distributeur_vapeself;

		$_SESSION['show_dialog'] = 6;
		redirect("customers");
	}


	function set_credit($customer_id = -1)
	{
		/*
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		//$_SESSION['customer_id']										=	$customer_id;
		$_SESSION['transaction_info']						=	$this->Customer->get_info($customer_id);
	//*/
		switch($customer_id)
		{
			case -1;
				//

			break;

			default:

			break;
		}

		$data_Credit_Client = array();
		$data_Credit_Client['IDClient'] = $customer_id;
		$data_Credit_Client['DateCredit'] = date("d-m-Y H:i:s");
		$data_Credit_Client['Montant'] = $_POST['solde_distributeur_vapeself'];
		$data_Credit_Client['Solde'] = 208.00;

		//chargement de la class vapeself 
		$this->load->library("../controllers/vapeself");

		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();
		$return_InsertCredit = $this->vapeself->post_InsertCredit($token, $data_Credit_Client);

		switch($return_InsertCredit)
		{
			case "Ok":
				$_SESSION['error_code'] = '07360';
			break;

			default:

			break;
		}
      //  $_SESSION['success_message'] = '07360';
		redirect("customers");
	}

	function solde_distributeur_vapeself()
	{
        //chargement de la class vapeself 
		$this->load->library("../controllers/vapeself");

		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();





		$data_Client = array();
        $data_Client['ADRESSE1'] = '';
        $data_Client['ADRESSE2'] = '';
        $data_Client['ADRESSE3'] = '';
        $data_Client['CODECARTE'] = '0124';
        $data_Client['CODEPOSTAL'] = '';
        $data_Client['EMAIL'] = '';
        $data_Client['NOM'] = 'Jean-pierre';
        $data_Client['PRENOM'] = 'Thimbaud';
        $data_Client['NUMEROCARTE'] = 'X002014';
        $data_Client['REMISE'] = '0';
        $data_Client['VILLE'] = '';
        $data_Client['VOTREID'] = '2014';
        $data_Client['DATENAISSANCE'] = '02/03/1962';
		//ajout ou maj du client
		$this->vapeself->post_MajClient($token, $data_Client);

		$this->vapeself->get_Credit_GetFromMachine($token);




		$data_Credit_Client = array();
		$data_Credit_Client['IDClient'] = "2014";
		$data_Credit_Client['DateCredit'] = date("d-m-Y H:i:s");
		$data_Credit_Client['Montant'] = 3.00;
		$data_Credit_Client['Solde'] = 208.00;
		
		 
		$this->vapeself->post_InsertCredit($token, $data_Credit_Client);
		echo '<br><br><br><br><br><br><br>';
		$this->vapeself->get_Credit_GetFromMachine($token);
//		https://vs2app.com:7060/api/CLIENT/InsertClient

		//redirect("customers");
		include("../wrightetmathon/application/views/customers/test_distributeur_vapeself.php");    //https://vs2app.com:7060/Token?grant_type=password&password=vs2compat&username=vapeself@yesstore.com
		///var/www/html/wrightetmathon/application/views/customers/test_distributeur_vapeself.php
	}
/*
	function refrech_data_sales_distributeur()
	{
		//chargement de la class vapeself 
		$this->load->library("../controllers/vapeself");
		
		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();

		//récupération de toutes les nouvelles ventes et stockage dans une variable session 
		$_SESSION['ventes_VS_json'] = $this->vapeself->get_GetVentes($token);

		$file = '/var/www/html/wrightetmathon/ventes.txt';
		file_put_contents($file, $_SESSION['ventes_VS_json'], FILE_APPEND);

        //juste pour les tests 
/*		$_SESSION['ventes_VS_json'] = '[
			{"ID_VENTE":18,"DATEVENTE":"2019-12-06T14:05:39.853","ID_CLIENT":"1474","TOTALTTC":5.90,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"6301\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":16},
			{"ID_VENTE":19,"DATEVENTE":"2019-12-06T14:06:57.23","ID_CLIENT":"1474","TOTALTTC":5.90,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"624\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":17},
			{"ID_VENTE":20,"DATEVENTE":"2019-12-06T14:10:34.86","ID_CLIENT":"1474","TOTALTTC":5.90,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"7777\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":18}
			]';//*/
//		$_SESSION['ventes_VS_json'] ='[{"ID_VENTE":21,"DATEVENTE":"2019-12-09T15:53:55.897","ID_CLIENT":"1474","TOTALTTC":11.80,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"565\",\"Quantite\":1},{\"VotreID\":\"7791\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":19}]';
/*		if(strlen($_SESSION['ventes_VS_json']) < 10)
		{
			//
			$_SESSION['ventes_VS_maj'] = 0;
		}
		if(strlen($_SESSION['ventes_VS_json']) > 10)
		{
			$_SESSION['ventes_VS_maj'] = 1;
		}

		switch($_SESSION['ventes_VS_maj'])
		{
			case 0:
				//pas de nouvelle ventes
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

       	//redirection vers la page des ventes
		//redirect("sales");
	}
	
	function refrech_data_credit_client_distributeur()
	{
		//supprimé des parasites dans les variables
		//unset($_SESSION['credit_VS_json']);
		//$_SESSION['credit_VS_maj']

		//chargement de la class vapeself 
		$this->load->library("../controllers/vapeself");
		
		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();

		//récupération de toutes les nouvelles ventes et stockage dans une variable session 
		$_SESSION['credit_VS_json'] = $this->vapeself->get_Credit_GetFromMachine($token);

		$file = '/var/www/html/wrightetmathon/credit.txt';
		file_put_contents($file, $_SESSION['credit_VS_json'], FILE_APPEND);

        //juste pour les tests 
/*		$_SESSION['credit_VS_json'] = '[
	        {"ID_CREDIT":91,"VOTREID":"1474","DATECREDIT":"2019-12-06T14:05:40.043","MONTANT":-5.90,"SOLDE":44.10},
			{"ID_CREDIT":92,"VOTREID":"1474","DATECREDIT":"2019-12-06T14:06:57.307","MONTANT":-5.90,"SOLDE":38.20},
			{"ID_CREDIT":93,"VOTREID":"1474","DATECREDIT":"2019-12-06T14:10:34.937","MONTANT":-5.90,"SOLDE":32.30}]';			
			//*/

/*		if(strlen($_SESSION['credit_VS_json']) < 10)
		{
			//
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

       	//redirection vers la page des ventes
	//	redirect("sales");

	}//*/

	function test($item_id)
	{
  //      $this->refrech_data_sales_distributeur();
		$this->refrech_data_credit_client_distributeur();




        //chargement de la class vapeself 
/*		$this->load->library("../controllers/vapeself");

        $this->vapeself->add_ventes_into_ospos_sales_distributeur();//*/

		//chargement de la class vapeself 
/*		$this->load->library("../controllers/vapeself");
        
		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();

		$_SESSION['data_Credit_GetFromMachine'] = $this->vapeself->get_Credit_GetFromMachine($token);

		$file = '/var/www/html/wrightetmathon/data_Credit_GetFromMachine.txt';    ///var/www/html/wrightetmathon/data_Credit_GetFromMachine.txt
		file_put_contents($file, $_SESSION['data_Credit_GetFromMachine'], FILE_APPEND);

		$_SESSION['ventes_VS'] = $this->vapeself->get_GetVentes($token);
		$file = '/var/www/html/wrightetmathon/ventes.txt';
		file_put_contents($file, $_SESSION['ventes_VS'], FILE_APPEND);
//*/
/*
		$_SESSION['distributeur_VS_token'] = array();
		foreach($token as $line => $row)
		{
			$_SESSION['distributeur_VS_token'][] = $row;
		}





		$data_InsertCatego = array();
		$data_InsertCatego['VotreID'] = "36";
		$data_InsertCatego['Nom'] = "Evolution";
		$data_InsertCatego['NomImage'] = "";
		$data_InsertCatego['Type'] = 5;
		
		$return_message = '0';

//*//*
		$data_Credit_Client = array();
		$data_Credit_Client['IDClient'] = $item_id;
		$data_Credit_Client['DateCredit'] = date("d-m-Y H:i:s");
		$data_Credit_Client['Montant'] = 50.00;    //float
		$data_Credit_Client['Solde'] = 50.00;    //float

		$return_message = $this->vapeself->post_InsertCredit($token, $data_Credit_Client);//*/
/*
		$data_GetQuantite = array();
		$data_GetQuantite['ID'] = 544;
		$data_GetQuantite['Emplacement'] = "vapeself";//*/
		
//		$tab_tests = array();
	/*	$tab_tests['ID_VENTE'] = 1;
		$tab_tests['DATEVENTE'] = '1900-01-01T01:01:01';
		$tab_tests['ID_CLIENT'] = "2014";
		$tab_tests['TOTALTTC'] = 35.00;
		$tab_tests['REMISE'] = 0.00;
		$tab_tests['RECREDIT'] = 1.00;
		$tab_tests['Emplacement'] = Machine;
		$tab_tests['ID_VENTE'] = 1;
		$tab_tests['ID_VENTE'] = 1;
		$tab_tests['ID_VENTE'] = 1;
		

		//*/

//	    $all_ventes = '';
		//Fonctionne correctement
		//$return_message = $this->vapeself->post_MajCatego($token, $data_InsertCatego); return le message d'erreur ou de success
		//$return_message = $this->vapeself->get_Credit_GetFromMachine($token);
		//$return_message = $this->vapeself->post_InsertCredit($token, $data_Credit_Client);
/*		$_SESSION['distributeur_VS_all_ventes'] = array();
		$return_message = $this->vapeself->get_GetVentes($token);

		$file = 'ventes_distributeur.txt';
        file_put_contents($file, $return_message, FILE_APPEND);


		foreach($return_message as $line_1 => $row)
		{
			$all_ventes .=  '\n';
			$_SESSION['distributeur_VS_all_ventes'][$line_1] = $row;
	//		$_SESSION['distributeur_VS_all_ventes'][] = $row;
        	$keys = array_keys($_SESSION['distributeur_VS_all_ventes'][$line_1]);
	//		foreach($_SESSION['distributeur_VS_all_ventes'][$line] as $line_2 => $attributs)
	        foreach($keys as $line_2 => $attributs)
	        {
				//
	
				
	//			$keys = array_keys($_SESSION['distributeur_VS_all_ventes'][$line]);
				$all_ventes .=  $attributs;
				$all_ventes .=  ': ';
				$all_ventes .=  '->';
				
				
			}
			$all_ventes .=  '\n';
			
		}
        

		echo $return_message . '<br>';
//		$return_message = $this->vapeself->get_GetQuantite($token, $data_GetQuantite);

		switch ($return_message)
		{
			case "Ok":
			    $_SESSION['error_code']		=	'07370';


            break;
			
			default:

			break;
		}

		//echo $return_message;
        print_r($return_message);

//*/



	}

    

	function test_GetVentes()
	{
/*		//chargement de la class vapeself 
		$this->load->library("../controllers/vapeself");

		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();

		$return_message = $this->vapeself->get_GetVentes($token);

		$file = '/var/www/html/wrightetmathon/connexion_megaupload_temporaire.txt';    ///var/www/html/wrightetmathon/connexion_megaupload_temporaire.txt
		file_put_contents($file, $return_message, FILE_APPEND);
		
//*/
        $test_ventes_brutes = '[{"ID_VENTE":18,"DATEVENTE":"2019-12-06T14:05:39.853","ID_CLIENT":"1474","TOTALTTC":5.90,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"6301\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":16},{"ID_VENTE":19,"DATEVENTE":"2019-12-06T14:06:57.23","ID_CLIENT":"1474","TOTALTTC":5.90,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"624\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":17},{"ID_VENTE":20,"DATEVENTE":"2019-12-06T14:10:34.86","ID_CLIENT":"1474","TOTALTTC":5.90,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"7777\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":18}]';
		$test_ventes_brutes_json_decode = json_decode($test_ventes_brutes);
		$_SESSION['ventes_VS'] = $test_ventes_brutes;
		$file_json_decode = '/var/www/html/wrightetmathon/donnee_brute_json_decode.txt';    ///var/www/html/wrightetmathon/donnee_brute_json_decode.txt
		file_put_contents($file_json_decode, $test_ventes_brutes_json_decode, FILE_APPEND);
		
		$all_ventes = '';

		foreach($test_ventes_brutes_json_decode as $line_vente_only => $row)
		{
	//		$all_ventes .=  '\n';
			$_SESSION['distributeur_VS_all_ventes'][$line_vente_only] = $row;
	//		$_SESSION['distributeur_VS_all_ventes'][] = $row;
	        
        	$keys = array_keys($row);
	//		foreach($_SESSION['distributeur_VS_all_ventes'][$line] as $line_2 => $attributs)
	        //foreach($_SESSION['distributeur_VS_all_ventes'][$line_vente_only] as $line_2 => $attributs)
	        //{
				//
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['ID_VENTE'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->ID_VENTE;
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['DATEVENTE'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->DATEVENTE;
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['ID_CLIENT'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->ID_CLIENT;
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['TOTALTTC'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->TOTALTTC;
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['REMISE'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->REMISE;
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['RECREDIT'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->RECREDIT;
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['EMPLACEMENT'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->EMPLACEMENT;
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['LISTE'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->LISTE;
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['MODIFIE'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->MODIFIE;
			$_SESSION['distributeur_VS_all_ventes_recuperer'][$line_vente_only]['MON_ID'] = $_SESSION['distributeur_VS_all_ventes'][$line_vente_only]->MON_ID;
								
	//			$keys = array_keys($_SESSION['distributeur_VS_all_ventes'][$line]);
	/*			$all_ventes .=  $attributs;
				$all_ventes .=  ': ';
				$all_ventes .=  '     ->     ';
				//*/
				
			//}
	//		$all_ventes .=  '\n';
			
		}
		$_SESSION['test_ventes_brutes'] = $test_ventes_brutes;
	}

	function save_vapeself()
	{
		$this->Vapeself->get_Token();
		/*
		// load person data
		$_SESSION['transaction_info']->first_name						=	$this->input->post('first_name');
		$_SESSION['transaction_info']->last_name						=	$this->input->post('last_name');
		$_SESSION['transaction_info']->email							=	$this->input->post('email');
		$_SESSION['transaction_info']->phone_number						=	$this->input->post('phone_number');
		$_SESSION['transaction_info']->address_1						=	$this->input->post('address_1');
		$_SESSION['transaction_info']->address_2						=	$this->input->post('address_2');			
		$_SESSION['transaction_info']->city								=	$this->input->post('city');
		$_SESSION['transaction_info']->state							=	$this->input->post('state');
		$_SESSION['transaction_info']->zip								=	$this->input->post('zip');
		$_SESSION['transaction_info']->country_id						=	$this->input->post('country_id');
		$_SESSION['transaction_info']->comments							=	$this->input->post('comments');
		$_SESSION['transaction_info']->sex								=	$this->input->post('sex');
		
		// load customer data
		$_SESSION['transaction_info']->account_number					=	$this->input->post('account_number');
		$_SESSION['transaction_info']->taxable							=	$this->input->post('taxable');
		$_SESSION['transaction_info']->on_stop_indicator				=	$this->input->post('on_stop_indicator');
		$_SESSION['transaction_info']->on_stop_amount					=	$this->input->post('on_stop_amount');
		$_SESSION['transaction_info']->on_stop_reason					=	$this->input->post('on_stop_reason');
		$_SESSION['transaction_info']->pricelist_id						=	$this->input->post('pricelist_id');
		$_SESSION['transaction_info']->profile_id						=	$this->input->post('profile_id');
		$_SESSION['transaction_info']->profile_reference				=	$this->input->post('profile_reference');    //Justificatif pour le code bar de la carte de fidélité
		$_SESSION['transaction_info']->fidelity_flag					=	$this->input->post('fidelity_flag');
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');

		//birnday
		$pieces 														=	explode("/", $this->input->post('dob'));
		$_SESSION['transaction_info']->dob_day							=	intval($pieces[0]);
		$_SESSION['transaction_info']->dob_month						=	$pieces[1];
		$_SESSION['transaction_info']->dob_year							=	$pieces[2];


        // manage session
		switch ($_SESSION['new'] ?? 0)
		{
			// add client
			case	1:
					// zero fidelity_points
					$_SESSION['transaction_info']->fidelity_points		=	0;
					// load id
					$_SESSION['transaction_info']->person_id			=	NULL;
			break;
			
			// update client
			default:
					// load id
					$_SESSION['transaction_info']->person_id			=	$_SESSION['transaction_info']->person_id;
			break;
		}
        $this->Vapeself->

		redirect("customers"); //*/
	}


	/*
	Inserts/updates a customer
	*/
	function	save	()
	{			
		// load person data
 		$_SESSION['transaction_info']->first_name						=	$this->input->post('first_name');
		$_SESSION['transaction_info']->last_name						=	$this->input->post('last_name');
		$_SESSION['transaction_info']->email							=	$this->input->post('email');
		$_SESSION['transaction_info']->phone_number						=	$this->input->post('phone_number');
		$_SESSION['transaction_info']->address_1						=	$this->input->post('address_1');
		$_SESSION['transaction_info']->address_2						=	$this->input->post('address_2');			
		$_SESSION['transaction_info']->city								=	$this->input->post('city');
		$_SESSION['transaction_info']->state							=	$this->input->post('state');
		$_SESSION['transaction_info']->zip								=	$this->input->post('zip');
		$_SESSION['transaction_info']->country_id						=	$this->input->post('country_id');
		$_SESSION['transaction_info']->comments							=	$this->input->post('comments');
		$_SESSION['transaction_info']->sex								=	$this->input->post('sex');
		
		// load customer data
		$_SESSION['transaction_info']->account_number					=	$this->input->post('account_number');
		$_SESSION['transaction_info']->taxable							=	$this->input->post('taxable');
		$_SESSION['transaction_info']->on_stop_indicator				=	$this->input->post('on_stop_indicator');
		$_SESSION['transaction_info']->on_stop_amount					=	$this->input->post('on_stop_amount');
		$_SESSION['transaction_info']->on_stop_reason					=	$this->input->post('on_stop_reason');
		$_SESSION['transaction_info']->pricelist_id						=	$this->input->post('pricelist_id');
		$_SESSION['transaction_info']->profile_id						=	$this->input->post('profile_id');
		$_SESSION['transaction_info']->profile_reference				=	$this->input->post('profile_reference');    //Justificatif pour le code bar de la carte de fidélité
		$_SESSION['transaction_info']->fidelity_flag					=	$this->input->post('fidelity_flag');
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');
		$_SESSION['transaction_info']->card_code				    	=	$this->input->post('code_carte_distributeur_vapeself');
		
//		if($_SESSION['transaction_info']->card_code == '')
//		{
//			$_SESSION['transaction_info']->card_code = NULL;
//		}
		if($_SESSION['transaction_info']->card_code != '')
		{
			if(isset($_POST['code_carte_distributeur_vapeself']))
			{
				//vérification que le code carte est composé de 4 caractéres		
				$code_carte_distributeur_vapeself = $this->input->post('code_carte_distributeur_vapeself');
				if((strlen($code_carte_distributeur_vapeself) == 4) && ($code_carte_distributeur_vapeself[0] != ' ') && ($code_carte_distributeur_vapeself[1] != ' ') && ($code_carte_distributeur_vapeself[2] != ' ') && ($code_carte_distributeur_vapeself[3] != ' '))
				{
					//
					//echo 'coucou';
				} 
				else
				{
					//
					$_SESSION['error_code']		=	'07350';
					
					redirect("customers");
				}
			}
		}

		// explode and load dob
/*		//$pieces 														=	explode("/", $this->input->post('dob_0'));
        $pieces 														=	explode("-", $this->input->post('dob_0'));
        $_SESSION['transaction_info']->dob = $this->input->post('dob_0');

		$_SESSION['transaction_info']->dob_day							=	$pieces[2];
		$_SESSION['transaction_info']->dob_month						=	$pieces[1];
		$_SESSION['transaction_info']->dob_year							=	$pieces[0];//*/
//*/
		$pieces 														=	explode("/", $this->input->post('dob'));
		
		$_SESSION['transaction_info']->dob_day							=	$pieces[0];
		$_SESSION['transaction_info']->dob_month						=	$pieces[1];
		$_SESSION['transaction_info']->dob_year							=	$pieces[2];
		
		if(!isset($pieces[2]))
		{
			$pieces 														=	explode("-", $this->input->post('dob'));
		
			$_SESSION['transaction_info']->dob_day							=	$pieces[2];
			$_SESSION['transaction_info']->dob_month						=	$pieces[1];
			$_SESSION['transaction_info']->dob_year							=	$pieces[0];		
		}

/*		// load customer data
		$_SESSION['transaction_info']->account_number					=	$this->input->post('account_number');
		$_SESSION['transaction_info']->taxable							=	$this->input->post('taxable');
		$_SESSION['transaction_info']->on_stop_indicator				=	$this->input->post('on_stop_indicator');
		$_SESSION['transaction_info']->on_stop_amount					=	$this->input->post('on_stop_amount');
		$_SESSION['transaction_info']->on_stop_reason					=	$this->input->post('on_stop_reason');
		$_SESSION['transaction_info']->pricelist_id						=	$this->input->post('pricelist_id');
		$_SESSION['transaction_info']->profile_id						=	$this->input->post('profile_id');
		$_SESSION['transaction_info']->profile_reference				=	$this->input->post('profile_reference');    //Justificatif pour le code bar de la carte de fidélité
		$_SESSION['transaction_info']->fidelity_flag					=	$this->input->post('fidelity_flag');
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');//*/

	/*	$data_Client = array();
        $data_Client['ADRESSE1'] = $_SESSION['transaction_info']->address_1;
        $data_Client['ADRESSE2'] = $_SESSION['transaction_info']->address_2;
        $data_Client['ADRESSE3'] = '';    //inexistant dans notre base de données
        $data_Client['CODECARTE'] = $_POST['code_carte_distributeur_vapeself'];    //
        $data_Client['CODEPOSTAL'] = $_SESSION['transaction_info']->zip;
        $data_Client['EMAIL'] = $_SESSION['transaction_info']->email;
        $data_Client['NOM'] = $_SESSION['transaction_info']->last_name;
        $data_Client['PRENOM'] = $_SESSION['transaction_info']->first_name;
		$data_Client['NUMEROCARTE'] = $_SESSION['transaction_info']->profile_reference;
		switch($_SESSION['transaction_info']->profile_id)
		{
			case 1:	// à completer plus tard break;
			case 2: break;
			case 3: break;
			default: break;
		}
        $data_Client['REMISE'] = '0';
        $data_Client['VILLE'] = $_SESSION['transaction_info']->city;
        $data_Client['VOTREID'] = $_SESSION['transaction_info']->account_number;    //
        $data_Client['DATENAISSANCE'] = $_SESSION['transaction_info']->dob_day . '/' . $_SESSION['transaction_info']->dob_month . '/' . $_SESSION['transaction_info']->dob_year;
		
        $this->load->library("../controllers/vapeself");    //*/
		// manage session
		switch ($_SESSION['new'] ?? 0)
		{
			case 2:
		//		$_SESSION['transaction_info']->
        		$_SESSION['transaction_info']->person_id = NULL;
        		$_SESSION['transaction_info']->sales_ht = floatval($_SESSION['transaction_info']->sales_ht);
				$_SESSION['transaction_info']->sales_number_of = floatval($_SESSION['transaction_info']->sales_number_of);
			break;
			// add client
			case	1:
					// zero fidelity_points
					$_SESSION['transaction_info']->fidelity_points		=	0;
					// load id
					$_SESSION['transaction_info']->person_id			=	NULL;
			break;
			
			// update client
			default:
					// load id
					$_SESSION['transaction_info']->person_id			=	$_SESSION['transaction_info']->person_id;
					/*
					//demande de token
					$token = $this->vapeself->get_token();

					$return_MajClient = $this->vapeself->post_MajClient($token, $data_Client);//*/
		/*			switch($return_MajClient)
					{
						//reponse de success
						//"Ok"
						//reponse de fail
						//"Rejected"
						case "OK":
							//Opération réalisée avec success
						break;
						
						case "Rejected":
							//Problème au niveau de la request
							$this->vapeself->post_InsertClient($token, $data_Client);
						break;
			
						default:
							//
						break;
					}//*/
    		break;
		}
									
		// do data verifications
		$this->															verify();
		
		// if here then all checks succeeded so do the update
		$this->															Customer->save();
			
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'00080';
					$this->												view($_SESSION['transaction_info']->person_id, $_SESSION['origin'], true);

			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'00090';
					$this->												view($_SESSION['transaction_info']->person_id, $_SESSION['origin'], true);
			break;	
		}
		//demande de token
//		$token = $this->vapeself->get_token();
		
		//mise à jour ou inséretion du client
//		$return_MajClient = $this->vapeself->post_MajClient($token, $data_Client);
	}
	
	// set the flash data
	function setflash($success_or_failure, $message, $id)
	{
		$this						->	session->set_flashdata('success_or_failure', $success_or_failure);
		$this						->	session->set_flashdata('message', $message);
		
		// get origin thru flash data
		$origin						=	$this->session->flashdata('origin');
		
		// redirect to origin
		if ($origin == 1)
		{
			$this					->	sale_lib->set_customer($id);
			redirect('sales');
		}
		else
		{
			redirect('customers');
		}
		
		return;
	}
	
	/*
	This deletes customers from the customers table
	*/
	function delete()
	{
		// check this is not the default client
		if ($_SESSION['transaction_info']->person_id == $this->config->item('default_client_id'))
		{
			// set error
			$_SESSION['error_code']										=	'00075';
			redirect("customers");
		}
		
		if($this->Customer->delete())
		{
			// set success message
			$_SESSION['error_code']			=	'00440';
			$_SESSION['del']				=	1;
		}
		else
		{
			$_SESSION['error_code']			=	'00350';
		}
		
		redirect("customers");
	}
	
	function list_deleted()
	{
		// set flag to select deleted customers
		$_SESSION['undel']					=	1;
		redirect("customers");
	}
	
	function undelete()
	{
		if($this->Customer->undelete())
		{
			// set success message
			$_SESSION['error_code']			=	'00480';
			unset($_SESSION['undel']);
		}
		else
		{
			$_SESSION['error_code']			=	'00350';
		}
		
		$_SESSION['full_name_out']			=	$this->Common_routines->format_full_name($_SESSION['transaction_info']->last_name, $_SESSION['transaction_info']->first_name);
		$_SESSION['$title']					=	$this->lang->line('common_edit').'  '.$_SESSION['full_name_out'];
		$this->									view($_SESSION['transaction_info']->person_id, '0', true);
	}
	
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{			
		return 800;
	}
	
	function comment($customer_id)
	{
		$person_info 				= 	$this->Customer->get_info($customer_id);
		$this						->	load->view('sales/customer_comment_popbox', $person_info);
	}
	
	function merge_form($merge_step=0)
	{			
		// function not available at this time							
		// redirect("customers");
		
		// set session data
		unset($_SESSION['transaction_info']->merge_from_id);
		unset($_SESSION['transaction_info']->merge_to_id);
		unset($_SESSION['merge_from_id']);
		unset($_SESSION['merge_to_id']);
		unset($_SESSION['new']);
		unset($_SESSION['clone']);
		unset($_SESSION['merge_ok']);
		$_SESSION['merge']					=	1;	
		$_SESSION['$title']					=	$this->lang->line($_SESSION['controller_name'].'_merge');
		
		// set dialog switch
		$_SESSION['show_dialog']			=	5;
		
		// show the data entry							
		redirect("customers");
	}
	
	function merge_do()
	{	
		// test confirm
		if ($_SESSION['merge_ok'] != 2)
		{
			// intialise
			$_SESSION['transaction_info']		=	new stdClass();
			$_SESSION['transaction_from']		=	new stdClass();
			$_SESSION['transaction_to']			=	new stdClass();
			
			// get data
			$_SESSION['transaction_info']->merge_from_id	=	$this->input->post('merge_from_id');
			$_SESSION['transaction_info']->merge_to_id		=	$this->input->post('merge_to_id');

			// verify input
			$this->verify_merge();
	
			// verifications are ok, so ask for confirmation
			$_SESSION['merge_ok']				=	1;
			redirect("customers");
		}

		// 	if here merge is confirmed so do updates
		
		// update DB
		$update_data				=	array	(
												'customer_id'	=>	$_SESSION['transaction_info']->merge_to_id
												);
												
		// 1) sales file												
		if (!$this->Sale->merge_customer($_SESSION['transaction_info']->merge_from_id, $update_data))
		{
			// set message
			$_SESSION['error_code']		=	'00580';
			redirect("customers/merge_form");
		}
		
		// 2) giftcards file
		if (!$this->Giftcard->merge_customer($_SESSION['transaction_info']->merge_from_id, $update_data))
		{
			// set message
			$_SESSION['error_code']		=	'00590';
			redirect("customers/merge_form");
		}
		
		
		// 3) sales_suspended
		if (!$this->Sale_suspended->merge_customer($_SESSION['transaction_info']->merge_from_id, $update_data))
		{
			// set message
			$_SESSION['error_code']		=	'00600';
			redirect("customers/merge_form");
		}
		
		// 3) reclculate sales value and number of sales for to client
		// zero the to client fields being updated
		$new_total						=	0;
		$new_total_number_of			=	0;
		$customer_data					=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of,
												    'fidelity_points'   =>  $_SESSION['transaction_from']->fidelity_points + $_SESSION['transaction_to']->fidelity_points	
												);
		$this							->	db->where('person_id', $_SESSION['transaction_info']->merge_to_id);
		$this							->	db->where('branch_code', $this->config->item('branch_code'));
		$this							->	db->update('customers', $customer_data);
		
		// get the sales data
		$data 							=	array();
		$this							->	db->select();
		$this							->	db->from('sales');
		$this							->	db->where('sales.customer_id', $_SESSION['transaction_info']->merge_to_id);
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
		
		// zero the from client fields being updated
		$new_total						=	0;
		$new_total_number_of			=	0;
		$customer_data					=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of,
													'fidelity_points'   =>  0
												);
		$this							->	db->where('person_id', $_SESSION['transaction_info']->merge_from_id);
		$this							->	db->where('branch_code', $this->config->item('branch_code'));
		$this							->	db->update('customers', $customer_data);
		
		// 4) delete the from client
		$_SESSION['transaction_info']->person_id =	$_SESSION['transaction_info']->merge_from_id;
		if (!$this->Customer->delete())
		{
			// set message
			$_SESSION['error_code']		=	'00640';
			redirect("customers/merge_form");
		}
		
		// set message
			$_SESSION['error_code']		=	'00650';
			redirect("customers/merge_form");
	}
	
	function verify()
	{		
					
		//check si le numero de la carte fidélité est correct (pas utilisé)
		if(!empty($_SESSION['transaction_info']->profile_reference))
		{
			$input['profile_reference'] = $_SESSION['transaction_info']->profile_reference;

			//Récupération de la valeur du nom du propriétaire de la carte de fidélité
			
			$data_customer = $this->Customer->get_info_with_parameters($input);

			$request_profile_reference	=	$data_customer[0];
			if(!isset($request_profile_reference))
			{

			}
			if(isset($request_profile_reference))
			{
				if($_SESSION['transaction_info']->person_id == NULL)
			    {
					$_SESSION['error_code']										=	'07310';
					redirect("customers");
				}

				if($_SESSION['transaction_info']->person_id != $request_profile_reference['person_id'])
				{
					if(isset($request_profile_reference['person_id']))
					{
						//redirect("customers");
						$_SESSION['error_code']										=	'07310';
						redirect("customers");
					}
				}
			}
		}
	
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->last_name)
			OR 	empty($_SESSION['transaction_info']->first_name)
			OR 	empty($_SESSION['transaction_info']->zip)
			OR 	empty($_SESSION['transaction_info']->sex)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect("customers");
		}
		
		// verify dob correct date
		if (!checkdate($_SESSION['transaction_info']->dob_month, $_SESSION['transaction_info']->dob_day, $_SESSION['transaction_info']->dob_year))
		{
			// set message
			$_SESSION['error_code']										=	'05100';
			redirect("customers");
		}
		
		// verify dob not in future
/*		$dobstr															=	str_replace('/', '-', $this->input->post('dob_0'));
		$dob_0															=	strtotime($dobstr);
//*/
		$dobstr															=	str_replace('/', '-', $this->input->post('dob'));
		$dob															=	strtotime($dobstr);
		$now															=	time();
		
/*		if ($dob_0 > $now)//*/
		if ($dob > $now)
		{
			// set message
			$_SESSION['error_code']										=	'05110';
			redirect("customers");
		}
		
		// verify dob > 18 years - under age client
		$underage														=	strtotime('-18 years');
/*		if ($dob_0 > $underage)//*/
		if ($dob > $underage)
		{
			// set message
			$_SESSION['error_code']										=	'05120';
			redirect("customers");
		}
		
		// verify input if on stop indicator is Y
		if ($_SESSION['transaction_info']->on_stop_indicator == 'Y')
		{
			// test on stop amount is numeric
			if (!is_numeric($_SESSION['transaction_info']->on_stop_amount))
			{
				// set message
				$_SESSION['error_code']			=	'00050';
				redirect("customers");
			}
			
			// test on stop amount is not zero
			if ($_SESSION['transaction_info']->on_stop_amount == 0)
			{
				// set message
				$_SESSION['error_code']			=	'00055';
				redirect("customers");
			}
			
			// test on stop reason is entered
			if (empty($_SESSION['transaction_info']->on_stop_reason))
			{
				// set message
				$_SESSION['error_code']			=	'00060';
				redirect("customers");
			}
		}
		
		// set fidelity points to 0 if fidelity flag 'N'
		if ($_SESSION['transaction_info']->fidelity_flag == 'N')
		{
			$_SESSION['transaction_info']->fidelity_points	=	0;
		}
		
		// if fidelity is Y then either the telphone or email must be entered
		if ($_SESSION['transaction_info']->fidelity_flag == 'Y')
		{
			if (empty($_SESSION['transaction_info']->phone_number) AND empty($_SESSION['transaction_info']->email))
			{
				// set message
				$_SESSION['error_code']									=	'05350';
				redirect("customers");
			}
		}
		
		// set on stop fields if on stop indicator = N
		if ($_SESSION['transaction_info']->on_stop_indicator == 'N')
		{
			$_SESSION['transaction_info']->on_stop_amount	=	0;
			$_SESSION['transaction_info']->on_stop_reason	=	NULL;
		}
		
		if($_SESSION['new'] != 2)
		{
		    // verify email, if entered
		    if (!empty($_SESSION['transaction_info']->email))
		    {
		    	// check email format			
		    	if (!$this							->	Common_routines->check_email_format())
		    	{
		    		// set message
		    		$_SESSION['error_code']			=	'00020';
		    		redirect("customers");
		    	}
    
		    	// check email duplicate
		    	$_SESSION['check_email_dup']		=	1;			
		    	if (!$this							->	Common_routines->common_check_duplicate())
		    	{
		    		// set message
		    		$_SESSION['error_code']			=	'00070';
		    		redirect("customers");
		    	}
			}
	    }
		
		// check profile data
		if ($_SESSION['transaction_info']->profile_id != $this->config->item('profile_id'))
		{
			if (empty($_SESSION['transaction_info']->profile_reference))
			{
				// set message
				$_SESSION['error_code']									=	'05340';
				redirect("customers");
			}
			
			if ($_SESSION['transaction_info']->fidelity_flag == 'Y')
			{
				// set message
				$_SESSION['error_code']									=	'05360';
				redirect("customers");
			}
		}
		else
		{
			//$_SESSION['transaction_info']->profile_reference			=	NULL;
		    $_SESSION['transaction_info']->profile_reference = $_SESSION['transaction_info']->profile_reference;
		}
		
	}
	
	function verify_merge()
	{
		// check input not blank
		if (empty($_SESSION['transaction_info']->merge_from_id) OR empty($_SESSION['transaction_info']->merge_to_id))
		{
			// set message
			$_SESSION['error_code']		=	'00030';
			redirect("customers");
		}
		
		// check input not same
		if ($_SESSION['transaction_info']->merge_from_id == $_SESSION['transaction_info']->merge_to_id)
		{
			// set message
			$_SESSION['error_code']		=	'00555';
			redirect("customers");
		}
		
		// check merge_from_customer valid
		if (!$this->Customer->exists($_SESSION['transaction_info']->merge_from_id))
		{
			// set message
			$_SESSION['error_code']		=	'00560';
			redirect("customers");
		}
		
		// check merge_from_customer not deleted		
		// get item info
		$_SESSION['transaction_from']	=	$this->Customer->get_info($_SESSION['transaction_info']->merge_from_id);
		
		// test deleted
		if ($_SESSION['transaction_from']->deleted == 1)
		{
			// set message
			$_SESSION['error_code']		=	'00565';
			redirect("customers");
		}
		
		// check merge_to_item valid
		if (!$this->Customer->exists($_SESSION['transaction_info']->merge_to_id))
		{
			// set message
			$_SESSION['error_code']		=	'00570';
			redirect("customers");
		}
		
		// check merge_to_customer not deleted		
		// get customer info
		$_SESSION['transaction_to']		=	$this->Customer->get_info($_SESSION['transaction_info']->merge_to_id);
		
		// test deleted
		if ($_SESSION['transaction_to']->deleted == 1)
		{
			// set message
			$_SESSION['error_code']		=	'00575';
			redirect("customers");
		}
	}

	function submit_sms()
	{
		$input['numero'] = '52';//$this->config->post('numero');
		$input['message'] = 'coucou';//$this->config->post('message');

	//	$code_returned = $this->send_sms($input);
		$code_returned = $this->send_sms($input);

		switch(strval($code_returned))
		{
			case '0000':
				//OK
				$_SESSION['substitution_parms'][0] = 'Tou c\'est bien déroulé';
				$_SESSION['error_code'] = '07440';
			break;

			case '102':
			    //Votre SMS dépasse les 160 caractères.
			    $_SESSION['substitution_parms'][0] = 'Votre SMS dépasse les 160 caractères.';
			break;

			case '103':
				//Il n’y a aucun destinataire à votre message.
				$_SESSION['substitution_parms'][0] = 'Il n\'y a aucun destinataire à votre message.';
			break;
			
			case '104':
				//Vous n’avez pas assez de crédit (vérifiez le nombre de vos contacts, ainsi que le nombre de SMS nécessaires à l’envoi de votre texte).
				$_SESSION['substitution_parms'][0] = 'Vous n\'avez pas assez de crédit (vérifiez le nombre de vos contacts, ainsi que le nombre de SMS nécessaires à l’envoi de votre texte).';
			break;
			
			case '105':
				//Vous n’avez pas assez de crédit, mais votre dernière commande est en attente de validation.
				$_SESSION['substitution_parms'][0] = 'Vous n\'avez pas assez de crédit, mais votre dernière commande est en attente de validation.';
			break;
			
			case '106':
				//Vous avez mal renseigné le Sender (émetteur peronnalisé). 3 à 11 caractères, choisis parmi 0 à 9, a à z, A à Z. Ni accent, ni espace, ni ponctuation.
				$_SESSION['substitution_parms'][0] = 'Vous avez mal renseigné le Sender (émetteur peronnalisé). 3 à 11 caractères, choisis parmi 0 à 9, a à z, A à Z. Ni accent, ni espace, ni ponctuation.'; 
			break;

			case '107':
				//Le texte de votre message est vide.
				$_SESSION['substitution_parms'][0] = 'Le texte de votre message est vide.';
			break;

			case '110':
				//Vous n’avez pas renseigné la liste de destinataires.
				$_SESSION['substitution_parms'][0] = 'Vous n\'avez pas renseigné la liste de destinataires.';
			break;
			
			case '117':
			    //Votre liste de destinataires ne contient aucun bon numéro. Avez-vous formaté vos numéros en les préfixant du format international ?
				$_SESSION['substitution_parms'][0] = 'Votre liste de destinataires ne contient aucun bon numéro. Avez-vous formaté vos numéros en les préfixant du format international ?';
			break;
			
			case '119':
			    //Vous ne pouvez pas envoyer de SMS de plus de 160 caractères pour ce type de SMS.
				$_SESSION['substitution_parms'][0] = 'Vous ne pouvez pas envoyer de SMS de plus de 160 caractères pour ce type de SMS.';
			break;

			case '125':
        		//Une erreur non définie est survenue.
				$_SESSION['substitution_parms'][0] = 'Une erreur non définie est survenue.';
			break;

			case '126':
			    //Une campagne SMS est déjà en attente de validation pour envoi. Vous devez la valider ou l’annuler pour pouvoir en lancer une autre.
				$_SESSION['substitution_parms'][0] = 'Une campagne SMS est déjà en attente de validation pour envoi. Vous devez la valider ou l\'annuler pour pouvoir en lancer une autre.';
			break;

			case '127':
			    //Une campagne SMS est déjà en attente d’estimation. Vous devez attendre que le calcul soit terminé pour en lancer une autre
				$_SESSION['substitution_parms'][0] = 'Une campagne SMS est déjà en attente d\'estimation. Vous devez attendre que le calcul soit terminé pour en lancer une autre.';
			break;

			case '128':
				//Trop de soumissions échouées pour cette campagne.
				$_SESSION['substitution_parms'][0] = 'Trop de soumissions échouées pour cette campagne.';
			break;

			case '153':
				//La route étant congestionnée, ce type de SMS ne permet pas un envoi immédiat. Si votre envoi est urgent, merci de bien vouloir utiliser un autre type de SMS.
				$_SESSION['substitution_parms'][0] = 'La route étant congestionnée, ce type de SMS ne permet pas un envoi immédiat. Si votre envoi est urgent, merci de bien vouloir utiliser un autre type de SMS.';
			break;

			case '209':
				//Vous n’êtes pas autorisé à envoyer des SMS à cet utilisateur.
				$_SESSION['substitution_parms'][0] = 'Vous n\'êtes pas autorisé à envoyer des SMS à cet utilisateur.';
			break;

			case '300':
				//Vous n’êtes pas autorisé à gérer vos listes par API.
				$_SESSION['substitution_parms'][0] = 'Vous n\'êtes pas autorisé à gérer vos listes par API.';
			break;

			case '301':
				//Vous avez atteint le nombre maximum de listes.
				$_SESSION['substitution_parms'][0] = 'Vous avez atteint le nombre maximum de listes.';
			break;

			case '304':
				//La liste est déjà pleine.
				$_SESSION['substitution_parms'][0] = 'La liste est déjà pleine.';
			break;

			case '305':
				//Il y a trop de contacts
				$_SESSION['substitution_parms'][0] = 'Il y a trop de contacts.';
			break;

			case '306':
				//L’actions demandée est inconnue.
				$_SESSION['substitution_parms'][0] = 'L\'actions demandée est inconnue.';
			break;

			case '500':
				//Impossible d’effecuter l’action demandée.
				$_SESSION['substitution_parms'][0] = 'Impossible d\'effecuter l\'action demandée.';
			break;

			case '501':
			    //Erreur de connexion. Merci de contacter notre support client.
				$_SESSION['substitution_parms'][0] = 'Erreur de connexion. Merci de contacter notre support client.';
			break;

			default:
				//autre code d'erreur
				$_SESSION['substitution_parms'][0] = 'autre code d\'erreur';

			break;
		}

		$_SESSION['substitution_parms'][1] = strval($code_returned);
		if(!isset($_SESSION['error_code']))
		{
            // set message
            $_SESSION['error_code'] = '07450';
		}
		redirect("sales");
	}

	function send_sms($input)
	{
/*
                             TEST BRUTE avec curl_setopt
//*/
	        //Login et clé pour l'utilisation de l'api web
	        $user_login = 'david@sonrisa-smile.com';
        	$api_key = 'FIzXwKBOB6Co6yNaL69bmTodeWx8VhLP';

			$user_login_urlencode = urlencode($user_login);
            $api_key_urlencode = urlencode($api_key);


			//liste des numéros de téléphone
		//	$phones = array();
		//	$phones[] = '00336........';
		//	$phones[] = '00336........';
//		    $phones = '00336........,00336........';

			//texte du corp du sms
            $text = 'test: 16h45: ' . date("Y-m-d H:i:s:v:u");//'YESSTORE:Bienvenue machin dans notre réseau YESSTORE, merci d\'avoir choisi notre boutique et de nous faire confiance.';

			//nom de l'émetteur pour la reception du sms 
			$sms_sender = 'YESSTORE';

			//ajout des paramètres pour l'envoie du sms
			$postfields = array(
                'user_login' => $user_login,
				'api_key' => $api_key,
				'sms_text' => $text .'STOP au XXXXX',
				'sms_recipients' => $phones,
				'sms_type' => 'FR',
				'sms_sender' => $sms_sender
			);

            //encodage des paramètres de la request url
			$postfields_encode_url = http_build_query($postfields);

            //lien de l'api web pour envoyer un sms
			$url_sms = 'https://www.octopush-dm.com/api/sms/?' . $postfields_encode_url;
			
            //request sur le port 80
			$port = '80';
            
            //$phones = '00336........';
            $phones_array = array('00336........', '00336........');

			$this->load->library('../controllers/SMS');
			
	//		$sms_recipients = $phones_array;
			$sms_text = 'test dans la classe SMS: ' . date('Y-m-d H:i:s:v:u');    //test corps message
			$sms_text = $input['message'];    //corps message
			$sms_text .= ' STOP au XXXXX';
			$sms_type = SMS_PREMIUM; // ou encore SMS_STANDARD,SMS_MONDE
			$sms_mode = INSTANTANE; // ou encore DIFFERE
			$sms_sender = 'YESSTORE';

			$sms = new SMS();

			$sms->set_user_login($user_login);
			$sms->set_api_key($api_key);
			$sms->set_sms_mode($sms_mode);
			$sms->set_sms_text($sms_text);
			$sms->set_sms_recipients($sms_recipients);
			$sms->set_sms_type($sms_type);
			$sms->set_sms_sender($sms_sender);
			$sms->set_sms_request_id(uniqid());
			$xml = $sms->send();
//            echo '<br>' . $xml . '<br>';
		
            //position de la première occurence de la balise <error_code> dans le message de retour
			$pos_1 = strpos($xml, '<error_code>');

			//position de la dernière occurence de la balise </error_code> dans le message de retour
			$pos_2 = strripos($xml, '</error_code>');
			
			$xml = substr($xml, $pos_1, $pos_2-strlen($xml));
			
			$pos_1 = strpos($xml, '>');
			
			//extraction de la partie de la chaîne qui contient les informatioms pertinentes
			$xml = substr($xml, ($pos_1 + 1));
			
            return $xml;


/*
			//création de la ressource
			$curl = curl_init();
			//ajout de l'url
			$test = curl_setopt($curl, CURLOPT_URL, $url_sms); 
			$test = curl_setopt($curl, CURLOPT_POST, true);
			$test = curl_setopt($curl, CURLOPT_PORT, $port);
			$test = curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
			$test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$return = curl_exec($curl);
			echo $return;
			$info_error = curl_getinfo($curl);
			curl_close($curl);
//*/
		//test pour envoyer des sms
			//0 03 36 01 01 01 01  0679650852
			//0 03 36 79 65 08 52
			//0033679650852
			//0 03 36 01 01 01 01
//lien url
//$lien_url = 'https://www.octopush-dm.com/api/sms/?user_login=david%40sonrisa-smile.com&api_key=FIzXwKBOB6Co6yNaL69bmTodeWx8VhLP&sms_text=test+bienvenue+chez+YESSTORESTOP au XXXXX&sms_recipients=0033679650852&sms_type=FR&sms_sender=Bonjour';
//	curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
//000 0.064 19.136 SMS345520005e297fe4aa7c5 1579778020 1 € 0033679650852 FR 0.064 1
/*
echo 'Message d\'erreur: <h1>' . print_r($info_error) . '</h1>';
//*/
	}
	
	function api_info()    //potentiellement utile pour vérifier la mise à jour du fichier api
	{
		$_SESSION['ip_cible'] = (isset($_SESSION['ip_cible'])) ? $_SESSION['ip_cible'] : '127.0.0.1';
		$url = 'http://' . $_SESSION['ip_cible'] .'/wrightetmathon/index.php/apis/';
        $header = array('Content-Type: application/json'); 
        $curl = curl_init();
        $test = curl_setopt($curl, CURLOPT_URL, $url);
        $test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $test = curl_setopt($curl, CURLOPT_POST, false);
        $test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $return_exec = curl_exec($curl);
		$data_exec = json_decode($return_exec);
		$info_error = curl_getinfo($curl);
		curl_close($curl);
	//	echo '<pre>'.$data_exec.'</pre>';
		var_dump($data_exec);
	}

	function verify_connection($ip)
	{
		//shell /bin/ping -c2 -q -w2 '.$ip.' | grep transmitted | cut -f3 -d"," | cut -f1 -d"," | cut -f1 -d"%"
		//$ping = shell_exec('ping -c1 -q -w1 '.$ip.' | grep transmitted | cut -f3 -d"," | cut -f1 -d"," | cut -f1 -d"%" >> /var/www/html/return.txt ');
	
		$url = 'http://' . $_SESSION['ip_cible'] .'/wrightetmathon/index.php/apis/ping/';
        $header = array('Content-Type: application/json'); 
        $curl = curl_init();
        $test = curl_setopt($curl, CURLOPT_URL, $url);
        $test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $test = curl_setopt($curl, CURLOPT_POST, false);
        $test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $return_exec = curl_exec($curl);
		$data_exec = json_decode($return_exec);
		$ping = intval($data_exec->ping);
		$info_error = curl_getinfo($curl);
		curl_close($curl);
		
		switch($ping)
		{
			case 1:
				return 1;  //good connection
			break;

			default:
			    return 0;
			break;
		}
		//if(intval($ping) == 0){return 1;} // 0% packet loss         good connection
		//else{return 0;}
	}

	function ssh_()
	{
		//
    //		$_SERVER['REMOTE_ADDR'] = '192.168.1.158';
    //		$_SERVER['HTTP_X_FORWADED_FOR'] = '192.168.1.158';
    /*
    mysql -u user -p -h$ip
    mysql -u root -pSon@Risa&11
    use test_users;
    SELECT users.new_irma_id, users.email, users.civilite, users.prenom, users.nom, users.activation_code, users.activated_at, users.is_suspended, users.mdp_code, users.created_at, users.updated_at FROM test_users WHERE users.email = \'$email\'';
//	//*/
//
//    $user = 'root';
//    $pwd = 'Son@Risa&11';
//    $ip = '127.0.0.1';    //'92.93.33.112';
//    $port = '80'; //'3306';
//    
//    //'mysql -u root --password="Son@Risa&11" '
//    $cmd  = 'mysql -u '.$user.' --password="'.$pwd.'" -P'.$port.' -h "'.$ip. '" ';
//    $cmd  = 'mysql -u '.$user.' --password="'.$pwd.'" -h'.$ip. ' ';
//    $cmd  = 'mysql -u '.$user.' --password="'.$pwd.'" --port '.$port.' -h "'.$ip. '" ';
//    //$cmd .= 'use '.$user;
//    $cmd .= '-e "show databases;"';
//		$pwd = 'sonrisa@PTAUD';
//		$user = 'wrightetmathon';
//	//	$connection = ssh2_connect($ip, $port); //,  array('hostkey'=>'ssh-rsa, ssh-dss'));
//	//	ssh2_auth_password($connection, $user, $pwd);
//    //    $ext = ssh2_exec($connection, $cmd);    //"mysql database -e 'query to run on table_name; more queries to run;'");
//		
//		
//		$param_ssh = array('login' => $user,
//							'password' => $pwd,
//							'ip' => $ip,
//							'port' => $port,
//							'command' => $cmd
//	                    );
//	//ssh -N -L 3336:127.0.0.1:3306 wrightetmathon@127.0.0.1
//
//
//
//	    $ext = exec("ssh -N -L 3336:127.0.0.1:3306 wrightetmathon@127.0.0.1");
//	    $ext = exec('mysql -u root --password="Son@Risa&11" --port 3306 -h "127.0.0.1";'); // '."\r\n".' show databases;');
//	    
//	
//	//ip DOLE
//	
//	$host = '92.93.33.112';
//		$port = 3306;
//	    
//
//
//
//
////		$pwd = 'Son@Risa&11';
////		$user = 'root';
////		$connection = ssh2_connect('92.93.33.112', 3306);
////		ssh2_auth_password($connection, $user, $pwd);
////		$stream = ssh2_exec($connection, '/usr/local/bin/php -i');
//
//       //     $connection = ssh2_connect('92.93.33.112', 22);
//    //    $connection = ssh2_connect('192.168.1.158', 22);
//        //   ssh2_auth_pubkey_file($connection, '/home/wrightetmathon/', '/id_dsa.pub', 'secret');
//    //    $tunnel = ssh2_tunnel($connection, '10.0.0.101', 12345);
//
//		$host = '192.168.1.158';
//		$port = 3306;
//		$port = 22;
//		//$methods = array(
//		//	'kex' => 'diffie-hellman-group1-sha1',
//		//	'client_to_server' => array(
//		//	'crypt'            => '3des-cbc',
//		//	'comp'             => 'none'),
//		//	'server_to_client' => array(
//		//	'crypt'            => 'aes256-cbc,aes192-cbc,aes128-cbc',
//		//	'comp'             => 'none'));
//
////		$methods = array('hostkey'=>'ssh-rsa, ssh-dss');
////
////		$callbacks = array('disconnect' => 'my_ssh_disconnect');
////	//	$ressource = ssh2_connect($host, $port, $methods, $callbacks);
////		$ressource = ssh2_connect($host, $port);    //, $methods, $callbacks);
////		$sql = 'mysql -h localhost -u root';
////		$request = 'SELECT * FROM `ospos_people` WHERE `first_name` LIKE "%da%" ';
////		$stream = ssh2_exec($connection, $request);
//
//		//ssh root@Host "mysql database -e 'query to run on table_name; more queries to run;'"
//
	}

	function search_ext_customers()
	{
		$last_name = $this->input->post('last_name');
		$first_name = $this->input->post('first_name');
		$email = $this->input->post('email');
		$phone_number = $this->input->post('phone_number');
		$shop_code = $this->input->post('branch_ipv4');
		
		//check if value is false
        $last_name = ($last_name === false) ? NULL : $last_name;
        $first_name = ($first_name === false) ? NULL : $first_name;
        $email = ($email === false) ? NULL : $email;
		$phone_number = ($phone_number === false) ? NULL : $phone_number;
		
		if(!isset($_SESSION['G']->branch_description_pick_list[$shop_code]['branch_ip']))
        {
			$this->Branch->load_pick_list();
		}

		if(!empty($last_name) || !empty($first_name) || !empty($email) || !empty($phone_number))
		{//
			$lien = '';
			$last_name = isset($last_name) ? $lien.='last_name='.$last_name.'&': '';
            $first_name = isset($first_name) ? $lien.='first_name='.$first_name.'&': '';
            $email = isset($email) ? $lien.='email='.$email.'&': '';
            $phone_number = isset($phone_number) ? $lien.='phone_number='.$phone_number.'&': '';
//		    $get = 'first_name=patric&email=p';
//            $url_get = urlencode($get);
            $url_get = urlencode($lien);
            //$_SESSION['ip_cible'] = '192.168.1.29';
			$_SESSION['ip_cible'] = $_SESSION['G']->branch_pick_list[$shop_code]['branch_ip'];


			//check connexion with other shop
			if($this->verify_connection($_SESSION['ip_cible']))
			{
				$_SESSION['substitution_parms'][1] = "connexion établie";
				$_SESSION['error_code'] = '07480';
			}
			else
			{
				//echec of connection
				$_SESSION['substitution_parms'][1] = "Connexion impossible à cette boutique";
				$_SESSION['error_code'] = '07490';
				redirect("customers");
			}
        

		    $url = 'http://' . $_SESSION['ip_cible'] .'/wrightetmathon/index.php/apis/customers'.'/'. $url_get;
            $header = array('Content-Type: application/json');
            $curl = curl_init();
            $test = curl_setopt($curl, CURLOPT_URL, $url);
            $test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$test = curl_setopt($curl, CURLOPT_POST, false);
			$test = curl_setopt($curl, CURLOPT_HTTPGET, true);
            $test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            $test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//            $test = curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
            $return_exec = curl_exec($curl);
            $data_exec = json_decode($return_exec);
			$_SESSION['data_remote_list_customers'] = $data_exec;
			$info_error = curl_getinfo($curl);
		    curl_close($curl);
	//		echo $return_exec;
	//		echo var_dump($data_exec);
//	        echo var_dump($info_error);
//			$data['manage_table']		=	get_people_manage_table($this->Customer->get_all($config['per_page'], $this->uri->segment($config['uri_segment'])), $this, $create_headers);
//			$this						->	load->view('people/manage', $data);
//			return $data_exec;
			$data =array();
			$data['headers'] = array(
				'last_name' => 'Nom',
				'first_name' => 'Prénom',
				'phone_number' => 'N°Téléphone',
				'email' => 'E-Mail',
			//	'profile_refere' => '',
				'dob' => '',
				'fidelity_points' => 'Point(s) Fidélité(s)',
				'import' => '',
				'ventes' => ''
				
			);
			$_SESSION['new'] = 2;
			foreach($_SESSION['data_remote_list_customers'] as $key => $customers)
			{
				$data['data_list_customers'][$key]->last_name = $customers->last_name;
				$data['data_list_customers'][$key]->first_name = $customers->first_name;
				$data['data_list_customers'][$key]->phone_number = $customers->phone_number;
				$data['data_list_customers'][$key]->email = $customers->email;
				$data['data_list_customers'][$key]->dob = $customers->dob_day.'/'.$customers->dob_month.'/'.$customers->dob_year;
				$data['data_list_customers'][$key]->fidelity_points = $customers->fidelity_points;
				$data['data_list_customers'][$key]->add = '<a href="'. site_url("customers/import_remote_fidelity_points/$key").'" >'. 'importer'.'</a>';
				$data['data_list_customers'][$key]->ventes = '<a href="'. site_url("sales/import_sales/$key").'" >'. 'ventes'.'</a>';
				$_SESSION['data_remote_list_customers'][$key]->customer_id = $_SESSION['data_remote_list_customers'][$key]->person_id;
				unset($_SESSION['data_remote_list_customers'][$key]->person_id);
			}
			
			$_SESSION['show_dialog'] = 8;
            $this->load->view('people/manage', $data);
		}
		else
		{
			$_SESSION['show_dialog'] = 7;
			$this->load->view('people/manage');
		}
	}

	function view_remote_customers($id_line)
	{
		// manage session
		$_SESSION['show_dialog'] = 1;
		redirect("customers");
	}

	function update_remote_customers($inputs)
	{
       
			$data_json = json_encode($inputs);
			$lien = 'customers:person_id='.$inputs['person_id'];
		    $url_get = $url_get = urlencode($lien);
			$url = 'http://' . $_SESSION['ip_cible'] .'/wrightetmathon/index.php/apis/customers'.'/'. $url_get;
            $header = array('Content-Type: application/json');
            $curl = curl_init();
            $test = curl_setopt($curl, CURLOPT_URL, $url);
            $test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $test = curl_setopt($curl, CURLOPT_POST, true);
            $test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            $test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $test = curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
            $return_exec = curl_exec($curl);
            $data_exec = json_decode($return_exec);
		//	$_SESSION['data_remote_list_customers'] = $data_exec;
			$info_error = curl_getinfo($curl);
		    curl_close($curl);
	}

	function update_remote_people($inputs)
	{
       
            $data_json = json_encode($inputs);
			$lien = 'people:person_id='.$inputs['person_id'];
		    $url_get = $url_get = urlencode($lien);
		    $url = 'http://' . $_SESSION['ip_cible'] .'/wrightetmathon/index.php/apis/customers'.'/'. $url_get;
            $header = array('Content-Type: application/json');
            $curl = curl_init();
            $test = curl_setopt($curl, CURLOPT_URL, $url);
            $test = curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $test = curl_setopt($curl, CURLOPT_POST, true);
            $test = curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            $test = curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $test = curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $test = curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
            $return_exec = curl_exec($curl);
            $data_exec = json_decode($return_exec);
		//	$_SESSION['data_remote_list_customers'] = $data_exec;
			$info_error = curl_getinfo($curl);
		    curl_close($curl);
	}

	function import_remote_fidelity_points($key = -1)
	{
		$yes = $this->input->post('fidelity_points_remove');
        $yes = ($yes === false) ? -1 : $yes;

		switch(intval($yes))
		{
			case 0:
    			$_SESSION['data_remote_list_customers'][$key]->fidelity_points = 0;
				$this->import_remote_customer($key);
				redirect("customers");

			break;
			
			case 1:

				
				
				//update fidelity_points
				$customer_id = $_SESSION['data_remote_list_customers'][$key]->customer_id;
				$fidelity_points = $_SESSION['data_remote_list_customers'][$key]->fidelity_points;
				$comments = $fidelity_points.' point(s) fidélité transféré à la boutique : '.$this->config->item('company');

				$this->update_remote_customers(array('person_id'=> intval($customer_id), 'fidelity_points' => 0 ));
				$this->update_remote_people(array('person_id'=> intval($customer_id), 'comments' =>$comments ));
				$this->import_remote_customer($key);
				
				redirect("customers");


				

			break;
			
			case -1:
		    	$data['key'] = $key;
	    		$_SESSION['show_dialog'] = 9;
	    		$this->load->view('people/manage', $data);
	    	break;
	
			default:
			    
			    $_SESSION['show_dialog'] = 9;
			    $this->load->view('people/manage');
			break;
		}
	}
	
	function import_remote_customer($key = -1)
	{
//		if($key == -1){}
		$_SESSION['transaction_info'] = $_SESSION['data_remote_list_customers'][$key];
        if($_SESSION['data_remote_list_customers'][$key]->on_stop_amount == 0 || $_SESSION['data_remote_list_customers'][$key]->on_stop_amount === NULL)
        {
			$_SESSION['selected_on_stop_indicator'] = 'N';
        }
		// manage session
		$_SESSION['show_dialog'] = 1;
		//$person_id = $_SESSION['data_remote_list_customers'][$key]->person_id;
		//$this->view($person_id, "0");
	}
}
?>
