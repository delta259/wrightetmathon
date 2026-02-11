<?php
class Currency extends CI_Model 
{	
	function	exists($currency_id)
	{
		$this						->	db->from('currencies');	
		$this						->	db->where('currency_id', $currency_id);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('currencies.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('currencies');
		$this						->	db->where('deleted', 0);	
		$this						->	db->where('currencies.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("currency_display_order", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('currencies');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('currencies.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on currencies
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('currencies');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										currency_name LIKE '%".$this->db->escape_like_str($search)."%' or
										currency_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('currencies.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("currency_display_order", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find currencies
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('currencies');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										currency_name LIKE '%".$this->db->escape_like_str($search)."%' or
										currency_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('currencies.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("currency_display_order", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->currency_name;
		}

		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	/*
	Gets information about a particular pricelist
	*/
	function	get_info			($currency_id)
	{
		$this						->	db->from('currencies');	
		$this						->	db->where('deleted', 0);
		$this						->	db->where('currencies.currency_id',$currency_id);
		$this						->	db->where('currencies.branch_code', $this->config->item('branch_code'));
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
	
	// common check duplicate
	function 	check_duplicate		()
	{		
		$this						->	db->from('currencies');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('currency_name', $_SESSION['transaction_info']->currency_name);
		$this						->	db->where('currencies.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	/*
	Inserts or updates a pricelist
	*/
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add category
			case	1:
					$this			->	db->insert('currencies', $_SESSION['transaction_info']);
					$_SESSION['transaction_info']->currency_id	=	$this->db->insert_id();
			break;
			
			// update category
			default:
					$this			->	db->where('currency_id', $_SESSION['transaction_id']);
					$this			->	db->where('currencies.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('currencies', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
	
	// load global currency pick list
	function	load_pick_list()
	{
		unset($_SESSION['G']->currency_pick_list);
		$currency_pick_list												=	array();
		foreach($this->get_all()->result() as $row)
		{
			$currency_pick_list[$row->currency_id] 						=	$row->currency_name;
		}
		$_SESSION['G']->currency_pick_list								=	$currency_pick_list;

		return;
	}
}
?>
