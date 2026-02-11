<?php
class Tracker extends CI_Model 
{	
	function	exists($tracker_id)
	{
		$this						->	db->from('tracker');	
		$this						->	db->where('tracker_id', $tracker_id);
		$this						->	db->where('tracker.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('tracker');
		$this						->	db->where('tracker.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("tracker_id", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('tracker');
		$this						->	db->where('tracker.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on tracker
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('tracker');
		$this						->	db->where("(
										tracker_subject LIKE '%".$this->db->escape_like_str($search)."%' or
										tracker_description LIKE '%".$this->db->escape_like_str($search)."%' or
										tracker_id LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('tracker.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("tracker_id", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find tracker
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('tracker');
		$this						->	db->where("(
										tracker_subject LIKE '%".$this->db->escape_like_str($search)."%' or
										tracker_description LIKE '%".$this->db->escape_like_str($search)."%' or
										tracker_id LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('tracker.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("tracker_display_order", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->tracker_subject;
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
	function	get_info			($tracker_id)
	{
		$this						->	db->from('tracker');	
		$this						->	db->where('tracker.tracker_id',$tracker_id);
		$this						->	db->where('tracker.branch_code', $this->config->item('branch_code'));
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
		$this						->	db->from('tracker');
		$this						->	db->where('tracker_subject', $_SESSION['transaction_info']->tracker_subject);
		$this						->	db->where('tracker.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	/*
	Inserts or updates
	*/
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add tracker
			case	1:
					$this			->	db->insert('tracker', $_SESSION['transaction_info']);
					$_SESSION['transaction_info']->tracker_id	=	$this->db->insert_id();
			break;
			
			// update tracker
			default:
					$this			->	db->where('tracker_id', $_SESSION['transaction_id']);
					$this			->	db->where('tracker.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('tracker', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
}
?>
