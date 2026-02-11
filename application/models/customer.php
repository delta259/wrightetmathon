<?php
class Customer extends CI_Model
{	
	/*
	Determines if a given person_id is a customer
	*/
	function	exists				($person_id)
	{
		$this						->	db->from('customers');	
		$this						->	db->join('people', 'people.person_id = customers.person_id');
		$this						->	db->where('customers.person_id', $person_id);
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();
		
		return 						($query->num_rows()==1);
	}
	
	/*
	Returns all the customers
	*/
	function	get_all				($limit=10000, $offset=0, $order_by='last_name', $sequence='asc')
	{
		// select records based on whether normal or undelete mode
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$this						->	db->where('customers.deleted', 1);
			break;

			default:
					$this						->	db->where('customers.deleted', 0);
			break;
		}

		// Session-based sorting (same pattern as items)
		$sort_col = $_SESSION['customers_sort_col'] ?? 'last_name';
		$sort_dir = $_SESSION['customers_sort_dir'] ?? 'asc';
		$sort_map = array(
			'account_number'  => 'customers.account_number',
			'last_name'       => 'people.last_name',
			'first_name'      => 'people.first_name',
			'email'           => 'people.email',
			'phone_number'    => 'people.phone_number',
			'city'            => 'people.city',
			'sales_ht'        => 'customers.sales_ht',
			'fidelity_points' => 'customers.fidelity_points',
		);
		$real_col = isset($sort_map[$sort_col]) ? $sort_map[$sort_col] : 'people.last_name';
		$real_dir = ($sort_dir === 'desc') ? 'desc' : 'asc';

		$this						->	db->from('customers');
		$this						->	db->join('people','customers.person_id=people.person_id');
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by($real_col, $real_dir);
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();
		return 						$data;
	}
	
	function	get_all_deleted		($limit=10000, $offset=0, $order_by='last_name', $sequence='asc')
	{
		$this						->	db->from('customers');
		$this						->	db->join('people','customers.person_id=people.person_id');			
		$this						->	db->where('customers.deleted', 1);
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by($order_by, $sequence);
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();
		return 						$data;		
	}
	
	function	count_all			()
	{
		$this						->	db->from('customers');
		$this						->	db->where('deleted',0);
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	function	count_all_deleted	()
	{
		$this						->	db->from('customers');
		$this						->	db->where('deleted',1);
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Gets information about a particular customer
	*/
	function	get_info			($customer_id)
	{
		$this						->	db->from('customers');	
		$this						->	db->join('people', 'people.person_id = customers.person_id');
		$this						->	db->where('customers.person_id',$customer_id);
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();

		if($query->num_rows()	==	1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $customer_id is NOT a customer
			//$person_obj				=	parent::get_info(-1);
			$person_obj				=	new stdClass();
			
			//Get all the fields from customer table
			$fields 				= 	$this->db->list_fields('customers');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$person_obj->$field	=	'';
			}
			
			return 					$person_obj;
		}
	}
	
	/*
	Gets information about multiple customers
	*/
	function	get_multiple_info	($customer_ids)
	{
		$this						->	db->from('customers');
		$this						->	db->join('people', 'people.person_id = customers.person_id');		
		$this						->	db->where_in('customers.person_id',$customer_ids);
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("last_name", "asc");
		return 						$this->db->get();		
	}
	
	/*
	updates sales counts for existing customer
	*/
	function	save_counts			()
	{
		$this															->	db->where('person_id', $_SESSION['transaction_info']->person_id);
		$this															->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this															->	db->update('customers', $_SESSION['transaction_info']);
	}

	//save_counts_for_vs
	function save_counts_vs_sale($update_custumer_counts)
	{
		$this															->	db->where('person_id', $update_custumer_counts['person_id']);
		$this															->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this															->	db->update('customers', $update_custumer_counts);
	}

	/*
	Inserts or updates a customer
	*/
	function	save				()
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->														trans_start();
		
		// save the data to the people file
		$this->															Person->save();
			
		// if OK, save to customer file
		// create data set
		$_SESSION['transaction_data_set']								=	new stdClass();
		$_SESSION['transaction_data_set']->person_id					=	$_SESSION['transaction_info']->person_id;
		$_SESSION['transaction_data_set']->account_number				=	$_SESSION['transaction_info']->person_id;
		$_SESSION['transaction_data_set']->taxable						=	$_SESSION['transaction_info']->taxable;
		$_SESSION['transaction_data_set']->on_stop_indicator			=	$_SESSION['transaction_info']->on_stop_indicator;
		$_SESSION['transaction_data_set']->on_stop_amount				=	$_SESSION['transaction_info']->on_stop_amount;
		$_SESSION['transaction_data_set']->on_stop_reason				=	$_SESSION['transaction_info']->on_stop_reason;
		$_SESSION['transaction_data_set']->pricelist_id					=	$_SESSION['transaction_info']->pricelist_id;
		$_SESSION['transaction_data_set']->profile_id					=	$_SESSION['transaction_info']->profile_id;
		$_SESSION['transaction_data_set']->profile_reference			=	$_SESSION['transaction_info']->profile_reference;
		$_SESSION['transaction_data_set']->fidelity_flag				=	$_SESSION['transaction_info']->fidelity_flag;
		$_SESSION['transaction_data_set']->card_code					=	$_SESSION['transaction_info']->card_code;
		$_SESSION['transaction_data_set']->branch_code					=	$_SESSION['transaction_info']->branch_code;
		
		// test for new record
		switch ($_SESSION['new'])
		{
			case 2:
			    
				//$this->db->insert('customers', $_SESSION['transaction_data_set']);
				$_SESSION['customer_id'] = $_SESSION['transaction_data_set']->person_id;

				if($_SESSION["exist"] == 1)
				{
					$this->db->											where('person_id', $_SESSION['transaction_data_set']->person_id);
					$this->db->											where('customers.branch_code', $this->config->item('branch_code'));
					$this->db->											update('customers', $_SESSION['transaction_data_set']);
				}
				else
				{
					$_SESSION['transaction_data_set']->fidelity_points				=	$_SESSION['transaction_info']->fidelity_points;
					$this->db->											insert('customers', $_SESSION['transaction_data_set']);
					$_SESSION['customer_id']							=	$_SESSION['transaction_data_set']->person_id;
				}
			break;

			case	1:
					// add record
					$_SESSION['transaction_data_set']->fidelity_points				=	$_SESSION['transaction_info']->fidelity_points;
					$this->db->											insert('customers', $_SESSION['transaction_data_set']);
					$_SESSION['customer_id']							=	$_SESSION['transaction_data_set']->person_id;
			break;
					
			default:
				
					// update record
					$_SESSION['transaction_data_set']->fidelity_points				=	$_SESSION['transaction_info']->fidelity_points;
					$this->db->											where('person_id', $_SESSION['transaction_data_set']->person_id);
					$this->db->											where('customers.branch_code', $this->config->item('branch_code'));
					$this->db->											update('customers', $_SESSION['transaction_data_set']);
			break;	
		}
		
		$this->db->														trans_complete();		
		return;
	}
	
	/*
	Deletes one customer
	*/
	function	delete				()
	{
		$this->db->														where('person_id', $_SESSION['transaction_info']->person_id);
		$this->db->														where('customers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->							update('customers', array('deleted' => 1));
	}
	
	/*
	Undeletes one customer
	*/
	function	undelete			()
	{
		$this->db->														where('person_id', $_SESSION['transaction_info']->person_id);
		$this->db->														where('customers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->							update('customers', array('deleted' => 0));
	}
	
	/*
	Deletes a list of customers
	*/
	function	delete_list			($customer_ids)
	{
		$this						->	db->where_in('person_id', $customer_ids);
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->update('customers', array('deleted' => 1));
 	}
 	
 	/*
	Get search suggestions to find customers
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();

		// set search on deleted or not items
		switch ($_SESSION['undel'] ?? 0)
		{
			case 	1:
					$this			->	db->where('customers.deleted', 1);
			break;
			
			default:
					$this			->	db->where('customers.deleted', 0);
			break;
		}
		
		// search on names
		$this						->	db->from('customers');
		$this						->	db->join('people','customers.person_id=people.person_id');	
		$this						->	db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%')");
		$this						->	db->order_by("last_name", "asc");
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));	
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));	
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$this->Common_routines->format_full_name($row->last_name, $row->first_name);
		}
		
		// search on emails
		$this						->	db->from('customers');
		$this						->	db->join('people','customers.person_id=people.person_id');	
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));		
		$this						->	db->like("email",$search);
		$this						->	db->order_by("email", "asc");		
		$by_email					=	$this->db->get();
		foreach($by_email->result() as $row)
		{
			$suggestions[]=$row->email;		
		}
		
		// search on phone number
		$this						->	db->from('customers');
		$this						->	db->join('people','customers.person_id=people.person_id');	
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));	
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));	
		$this						->	db->like("phone_number",$search);
		$this						->	db->order_by("phone_number", "asc");		
		$by_phone					=	$this->db->get();
		foreach($by_phone->result() as $row)
		{
			$suggestions[]=$row->phone_number;		
		}
		
		// search on account number
		$this						->	db->from('customers');
		$this						->	db->join('people','customers.person_id=people.person_id');	
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->like("account_number",$search);
		$this						->	db->order_by("account_number", "asc");		
		$by_account_number			=	$this->db->get();
		foreach($by_account_number->result() as $row)
		{
			$suggestions[]=$row->account_number;		
		}
		
		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	/*
	Get search suggestions to find customers
	*/
	function	get_customer_search_suggestions($search,$limit=25)
	{
		// initialise
		$suggestions = array();

		// set search on deleted or not items
		switch ($_SESSION['undel'] ?? 0)
		{
			case 	1:
					$this			->	db->where('customers.deleted', 1);
			break;
			
			default:
					$this			->	db->where('customers.deleted', 0);
			break;
		}
		
		
		// search on names
		$this						->	db->from('customers');
		$this						->	db->join('people','customers.person_id=people.person_id');	
		$this						->	db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%' or
										CONCAT(`last_name`,' ',`first_name`) LIKE '%".$this->db->escape_like_str($search)."%')");
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("last_name", "asc");
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->person_id.'|'.$this->Common_routines->format_full_name($row->last_name, $row->first_name);		
		}
		
		// search on account number
		$this						->	db->from('customers');
		$this						->	db->join('people','customers.person_id=people.person_id');	
		$this						->	db->where('deleted',0);
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));		
		$this						->	db->like("account_number",$search);
		$this						->	db->order_by("account_number", "asc");		
		$by_account_number			=	$this->db->get();
		foreach($by_account_number->result() as $row)
		{
			$suggestions[]=$row->person_id.'|'.$row->account_number;
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		
		return						$suggestions;

	}
	/*
	Preform a search on customers
	*/
	function	search				($search)
	{
		// set search on deleted or not items
		switch ($_SESSION['undel'] ?? 0)
		{
			case 	1:
					$this			->	db->where('customers.deleted', 1);
			break;
			
			default:
					$this			->	db->where('customers.deleted', 0);
			break;
		}
		
		// search by everything
		$this						->	db->from('customers');
		$this						->	db->join('people','customers.person_id=people.person_id');		
		$this						->	db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										email LIKE '%".$this->db->escape_like_str($search)."%' or 
										phone_number LIKE '%".$this->db->escape_like_str($search)."%' or 
										account_number LIKE '%".$this->db->escape_like_str($search)."%' or 
										CONCAT(`last_name`,', ',`first_name`) LIKE '%".$this->db->escape_like_str($search)."%')");
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));

		// Session-based sorting
		$sort_col = $_SESSION['customers_sort_col'] ?? 'last_name';
		$sort_dir = $_SESSION['customers_sort_dir'] ?? 'asc';
		$sort_map = array(
			'account_number'  => 'customers.account_number',
			'last_name'       => 'people.last_name',
			'first_name'      => 'people.first_name',
			'email'           => 'people.email',
			'phone_number'    => 'people.phone_number',
			'city'            => 'people.city',
			'sales_ht'        => 'customers.sales_ht',
			'fidelity_points' => 'customers.fidelity_points',
		);
		$real_col = isset($sort_map[$sort_col]) ? $sort_map[$sort_col] : 'people.last_name';
		$real_dir = ($sort_dir === 'desc') ? 'desc' : 'asc';
		$this						->	db->order_by($real_col, $real_dir);

		return 						$this->db->get();
	}
	/*
	SELECT `person_id`, `profile_reference` FROM `ospos_customers` WHERE `profile_reference` = '" . $_SESSION['transaction_info']->profile_reference . "';
	SELECT `person_id` FROM `ospos_customers` WHERE `profile_reference` = "' . $_POST['customer'] . '"
	//*/
	function get_info_with_parameters($inputs)
	{
		$this->db->from('customers');
		
		foreach($inputs as $key => $input)
		{
			$this->db->where($key, $input);
		}
		
		$data = $this->db->get()->result_array();
		return $data;
	}

	function get_all_info_with_inputs($inputs)
	{
		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where($inputs['where']);
	//	$this->db->order_by("last_name", "asc");
		
		$data = $this->db->get();
		return $data;
	}

	function update_customer($update_custumer)
	{
//		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where('person_id', $update_custumer->person_id);
		$this->db->where('customers.branch_code', $this->config->item('branch_code'));
		return $this->db->update('customers', $update_custumer);
	}
	public function getData(array $inputs)
	{
		// set the transaction subtype
		if ($inputs['transaction_subtype'] == 'sales$returns')
		{
			$transaction_subtypes	=	array('sales', 'returns');
		}
		else
		{
			$transaction_subtypes	=	array($inputs['transaction_subtype']);
		}
		
		// get the summary data from sales file
		$this->db->select	('
							sale_id as transaction_id,
							sales.customer_id,
							date(sale_time) as transaction_date, 
							CONCAT(employee.first_name," ",employee.last_name) as employee_name,
							CONCAT(customer.first_name," ",customer.last_name) as transaction_name,
							subtotal_before_discount,
							subtotal_discount_percentage_amount,
							subtotal_discount_amount_amount,
							subtotal_after_discount,
							overall_tax,
							overall_total,
							overall_tax_percentage,
							overall_tax_name,
							amount_change,
							payment_type, 
							comment,
							mode', 
							false
							);
		$this->db->from('sales');
		$this->db->join('people as employee', 'sales.employee_id = employee.person_id');
		$this->db->join('people as customer', 'sales.customer_id = customer.person_id', 'left');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'" and customer_id='.$inputs['person_id']);
		$this->db->order_by('sale_id',"desc");
		$this->db->limit($inputs['limit']);

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		
		// get the details data from sales_items file
		$data['details'] = array();
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select	('
								line,
								category,
								item_number,
								name,
								sales_items.description,
								serialnumber,
								quantity_purchased,
								item_cost_price,
								item_unit_price,
								discount_percent,
								line_sales_before_discount,
								line_discount,
								line_sales_after_discount,
								line_tax,
								line_sales,
								line_tax_percentage,
								line_tax_name
								');
			$this->db->from('sales_items');
			$this->db->join('items', 'sales_items.item_id = items.item_id');
			$this->db->where('sale_id = '.$value['transaction_id']);
			$this->db->order_by('line',"asc");
			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		return $data;
	}
}
?>
