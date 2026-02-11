<?php
class Paymethod extends CI_Model 
{	
	function	exists($payment_method_code)
	{
		$this						->	db->from('payment_methods');	
		$this						->	db->where('payment_method_code', $payment_method_code);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('payment_methods');
		$this						->	db->where('deleted', 0);	
		$this						->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("payment_method_display_order", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('payment_methods');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on paymethods
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('payment_methods');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										payment_method_code LIKE '%".$this->db->escape_like_str($search)."%' or
										payment_method_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("payment_method_code", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find paymethods
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('payment_methods');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										payment_method_code LIKE '%".$this->db->escape_like_str($search)."%' or
										payment_method_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("payment_method_display_order", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->payment_method_description;
		}

		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	/*
	Gets information about a particular payment method by its code
	*/
	function	get_info_by_code	($paymethod_code)
	{
		$this						->	db->from('payment_methods');	
		$this						->	db->where('deleted', 0);
		$this						->	db->where('payment_methods.payment_method_code',$paymethod_code);
		$this						->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();
		
		if($query->num_rows()	==	1)
		{
			return $query->row();
		}
		else
		{			
			return 					NULL;
		}
	}
	
	/*
	Gets information about a particular payment method
	*/
	function	get_info			($paymethod_id)
	{
		$this						->	db->from('payment_methods');	
		$this						->	db->where('deleted', 0);
		$this						->	db->where('payment_methods.payment_method_id',$paymethod_id);
		$this						->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();
		
		if($query->num_rows()	==	1)
		{
			return $query->row();
		}
		else
		{			
			return 					NULL;
		}
	}

	function get_info_with_input($input)
	{
		$this->db->from('payment_methods');	
		$this->db->where('deleted', 0);
		
		foreach($input as $key => $line)
		{
			$this->db->where($key, $line);
		}

		$this->db->where('payment_methods.branch_code', $this->config->item('branch_code'));
		
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	// check duplicate
	function 	check_duplicate		()
	{		
		$this						->	db->from('payment_methods');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('payment_method_code', $_SESSION['transaction_info']->payment_method_code);
		$this						->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	/*
	Inserts or updates a payment method
	*/
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add
			case	1:
					$this			->	db->insert('payment_methods', $_SESSION['transaction_info']);
			break;
			
			// update 
			default:
					$this			->	db->where('payment_method_id', $_SESSION['transaction_id']);
					$this			->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('payment_methods', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
}
?>
