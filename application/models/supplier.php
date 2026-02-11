<?php
class Supplier extends Person
{	
	/*
	Determines if a given person_id is a supplier
	*/
	function	exists				($person_id)
	{
		$this						->	db->from('suppliers');	
		$this						->	db->join('people', 'people.person_id = suppliers.person_id');
		$this						->	db->where('suppliers.person_id', $person_id);
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();
		
		return 						($query->num_rows()==1);
	}
	
	/*
	Returns all the suppliers
	*/
	function	get_all				($limit=10000, $offset=0, $order_by='last_name', $sequence='asc')
	{
		// select records based on whether normal or undelete mode
		switch ($_SESSION['undel'])
		{
			case	1:
					$this						->	db->where('suppliers.deleted', 1);
			break;
			
			default:
					$this						->	db->where('suppliers.deleted', 0);
			break;
		}
		
		$this						->	db->from('suppliers');
		$this						->	db->join('people','suppliers.person_id=people.person_id');			
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by($order_by, $sequence);
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();
		return 						$data;		
	}
	
	function	get_all_deleted		($limit=10000, $offset=0, $order_by='last_name', $sequence='asc')
	{
		$this						->	db->from('suppliers');
		$this						->	db->join('people','suppliers.person_id=people.person_id');			
		$this						->	db->where('suppliers.deleted', 1);
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by($order_by, $sequence);
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();
		return 						$data;		
	}
	
	function	count_all			()
	{
		$this						->	db->from('suppliers');
		$this						->	db->where('deleted',0);
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	function	count_all_deleted	()
	{
		$this						->	db->from('suppliers');
		$this						->	db->where('deleted',1);
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Gets information about a particular supplier
	*/
	function	get_info			($supplier_id)
	{
		$this						->	db->from('suppliers');
		$this						->	db->join('people', 'people.person_id = suppliers.person_id');
		$this						->	db->where('suppliers.person_id',$supplier_id);
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();
		
		if($query->num_rows()	==	1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $supplier_id is NOT a supplier
			$person_obj				=	parent::get_info(-1);
			
			//Get all the fields from supplier table
			$fields 				= 	$this->db->list_fields('suppliers');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$person_obj->$field	=	'';
			}
			
			return 					$person_obj;
		}
	}
	
	/*
	Gets information about multiple suppliers
	*/
	function	get_multiple_info	($supplier_ids)
	{
		$this						->	db->from('suppliers');
		$this						->	db->join('people', 'people.person_id = suppliers.person_id');		
		$this						->	db->where_in('suppliers.person_id',$supplier_ids);
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("last_name", "asc");
		return 						$this->db->get();		
	}
	
	/*
	Inserts or updates a supplier
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
		$_SESSION['transaction_data_set']->account_number				=	$_SESSION['transaction_info']->account_number;
		$_SESSION['transaction_data_set']->company_name					=	$_SESSION['transaction_info']->company_name;
		$_SESSION['transaction_data_set']->branch_code					=	$_SESSION['transaction_info']->branch_code;
		
		// test for new record
		switch ($_SESSION['new'])
		{
			case	1:
					// add record
					$this->db->											insert('suppliers', $_SESSION['transaction_data_set']);
			break;
					
			default:
					// update record
					$this->db->											where('person_id', $_SESSION['transaction_data_set']->person_id);
					$this->db->											where('suppliers.branch_code', $this->config->item('branch_code'));
					$this->db->											update('suppliers', $_SESSION['transaction_data_set']);
			break;	
		}
		
		$this->db->														trans_complete();		
		return;
	}
	
	/*
	Deletes one supplier
	*/
	function	delete				($person_id = null)
	{
		$this->db->														where('person_id', $_SESSION['transaction_info']->person_id);
		$this->db->														where('suppliers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->							update('suppliers', array('deleted' => 1));
	}
	
	/*
	Undeletes one supplier
	*/
	function	undelete			()
	{
		$this->db->														where('person_id', $_SESSION['transaction_info']->person_id);
		$this->db->														where('suppliers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->							update('suppliers', array('deleted' => 0));
	}
	
	/*
	Deletes a list of suppliers
	*/
	function	delete_list			($customer_ids)
	{
		$this						->	db->where_in('person_id', $customer_ids);
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->update('suppliers', array('deleted' => 1));
 	}
 	
 	/*
	Get search suggestions to find suppliers
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();

		// set search on deleted or not items
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('suppliers.deleted', 1);
			break;
			
			default:
					$this			->	db->where('suppliers.deleted', 0);
			break;
		}
		
		// search on names
		$this						->	db->from('suppliers');
		$this						->	db->join('people','suppliers.person_id=people.person_id');	
		$this						->	db->where("(
										company_name LIKE '%".$this->db->escape_like_str($search)."%' or
										first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										last_name LIKE '%".$this->db->escape_like_str($search)."%')");
		$this						->	db->order_by("company_name", "asc");
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));	
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));	
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->company_name;
		}
		
		// search on emails
		$this						->	db->from('suppliers');
		$this						->	db->join('people','suppliers.person_id=people.person_id');	
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));		
		$this						->	db->like("email",$search);
		$this						->	db->order_by("email", "asc");		
		$by_email					=	$this->db->get();
		foreach($by_email->result() as $row)
		{
			$suggestions[]=$row->email;		
		}
		
		// search on phone number
		$this						->	db->from('suppliers');
		$this						->	db->join('people','suppliers.person_id=people.person_id');	
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));	
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));	
		$this						->	db->like("phone_number",$search);
		$this						->	db->order_by("phone_number", "asc");		
		$by_phone					=	$this->db->get();
		foreach($by_phone->result() as $row)
		{
			$suggestions[]=$row->phone_number;		
		}
		
		// search on account number
		$this						->	db->from('suppliers');
		$this						->	db->join('people','suppliers.person_id=people.person_id');	
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
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
	Get search suggestions to find suppliers
	*/
	function	get_supplier_search_suggestions($search,$limit=25)
	{
		// initialise
		$suggestions = array();
		
		// set search on deleted or not items
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('suppliers.deleted', 1);
			break;
			
			default:
					$this			->	db->where('suppliers.deleted', 0);
			break;
		}
		
		
		// search on names
		$this						->	db->from('suppliers');
		$this						->	db->join('people','suppliers.person_id=people.person_id');	
		$this						->	db->where("(
										company_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										)");
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("last_name", "asc");
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->person_id.'|'.$row->company_name;		
		}
		
		// search on account number
		$this						->	db->from('suppliers');
		$this						->	db->join('people','suppliers.person_id=people.person_id');	
		$this						->	db->where('deleted',0);
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
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
	Preform a search on suppliers
	*/
	function	search				($search)
	{
		// set search on deleted or not items
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('suppliers.deleted', 1);
			break;
			
			default:
					$this			->	db->where('suppliers.deleted', 0);
			break;
		}
		
		// search by everything
		$this						->	db->from('suppliers');
		$this						->	db->join('people','suppliers.person_id=people.person_id');		
		$this						->	db->where("(
										company_name LIKE '%".$this->db->escape_like_str($search)."%' or
										first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										email LIKE '%".$this->db->escape_like_str($search)."%' or 
										phone_number LIKE '%".$this->db->escape_like_str($search)."%' or 
										account_number LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('suppliers.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("company_name", "asc");
		
		return 						$this->db->get();	
	}
	
	// load supplier pick list
	function load_pick_list()
	{
		unset($_SESSION['G']->supplier_pick_list);
		$supplier_pick_list												=	array();
		
		foreach($this->get_all()->result() as $row)
		{
			$supplier_pick_list[$row->person_id] 						=	$row->company_name.' => '.$row->last_name.', '.$row->first_name;
		}
		// add blank line
		$supplier_pick_list[0] 											=	' ';    //rajout une ligne vide dans le tableau
		$_SESSION['G']->supplier_pick_list								=	$supplier_pick_list;
		
		return;
	}
	
	function get_account_number($inputs)
	{
		$this->db->from('suppliers');
		$this->db->join('people','suppliers.person_id=people.person_id');
		
		//where avec tout ce qui est passé en paramètre dans le tableau
		foreach($inputs as $key => $input)
		{
			$this->db->where($key, $input);
		}
		
		$data = $this->db->get()->result_array();
		return $data;
	}

	function get_supplier_sonrisa()
	{
		$this->db->from('suppliers');
		$this->db->like('company_name', "Sonrisa");
		$this->db->limit('1');

		return $this->db->get()->row();
	}
}
?>
