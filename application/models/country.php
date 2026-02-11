<?php
class Country extends CI_Model 
{	
	function	exists($country_id)
	{
		$this						->	db->from('countries');	
		$this						->	db->where('country_id', $country_id);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('countries.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('countries');
		$this						->	db->where('deleted', 0);	
		$this						->	db->where('countries.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("country_display_order", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('countries');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('countries.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on countries
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('countries');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										country_name LIKE '%".$this->db->escape_like_str($search)."%' or
										country_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('countries.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("country_display_order", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find countries
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('countries');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										country_name LIKE '%".$this->db->escape_like_str($search)."%' or
										country_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('countries.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("country_display_order", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->country_name;
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
	function	get_info			($country_id)
	{
		$this						->	db->from('countries');	
		$this						->	db->where('deleted', 0);
		$this						->	db->where('countries.country_id',$country_id);
		$this						->	db->where('countries.branch_code', $this->config->item('branch_code'));
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
		$this						->	db->from('countries');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('country_name', $_SESSION['transaction_info']->country_name);
		$this						->	db->where('countries.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	/*
	Inserts or updates
	*/
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add category
			case	1:
					$this			->	db->insert('countries', $_SESSION['transaction_info']);
					$_SESSION['transaction_info']->country_id	=	$this->db->insert_id();
			break;
			
			// update category
			default:
					$this			->	db->where('country_id', $_SESSION['transaction_id']);
					$this			->	db->where('countries.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('countries', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
	
	// load pick list
	function	load_pick_list()
	{
		unset($_SESSION['G']->country_pick_list);
		$country_pick_list												=	array();
		
		foreach($this->get_all()->result() as $row)
		{
			$country_pick_list[$row->country_id] 						=	$row->country_name;
		}
		$_SESSION['G']->country_pick_list								=	$country_pick_list;
		
		return;
	}
}
?>
