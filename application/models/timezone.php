<?php
class timezone extends CI_Model 
{	
	function	exists($timezone_id)
	{
		$this						->	db->from('timezones');	
		$this						->	db->where('timezone_id', $timezone_id);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('timezones.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('timezones');
		$this						->	db->where('deleted', 0);	
		$this						->	db->where('timezones.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("timezone_offset", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('timezones');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('timezones.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on timezones
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('timezones');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										timezone_name LIKE '%".$this->db->escape_like_str($search)."%' or
										timezone_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('timezones.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("timezone_offset", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find timezones
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('timezones');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										timezone_name LIKE '%".$this->db->escape_like_str($search)."%' or
										timezone_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('timezones.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("timezone_offset", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->timezone_name;
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
	function	get_info			($timezone_id)
	{
		$this						->	db->from('timezones');	
		$this						->	db->where('deleted', 0);
		$this						->	db->where('timezones.timezone_id',$timezone_id);
		$this						->	db->where('timezones.branch_code', $this->config->item('branch_code'));
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
		$this						->	db->from('timezones');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('timezone_name', $_SESSION['transaction_info']->timezone_name);
		$this						->	db->where('timezones.branch_code', $this->config->item('branch_code'));
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
					$this			->	db->insert('timezones', $_SESSION['transaction_info']);
					$_SESSION['transaction_info']->timezone_id	=	$this->db->insert_id();
			break;
			
			// update category
			default:
					$this			->	db->where('timezone_id', $_SESSION['transaction_id']);
					$this			->	db->where('timezones.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('timezones', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
	
	// load pick list
	function	load_pick_list()
	{
		unset($_SESSION['G']->timezone_pick_list);
		$timezone_pick_list												=	array();
		
		foreach($this->get_all()->result() as $row)
		{
			$timezone_pick_list[$row->timezone_id] 						=	$row->timezone_name;
		}
		$_SESSION['G']->timezone_pick_list								=	$timezone_pick_list;
		
		return;
	}
}
?>
