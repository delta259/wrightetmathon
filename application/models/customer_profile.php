<?php
class Customer_profile extends CI_Model 
{	
	function	exists													($profile_id)
	{
		$this->db														->	from('customer_profiles');	
		$this->db														->	where('profile_id', $profile_id);
		$this->db														->	where('deleted', 0);
		$this->db														->	where('customer_profiles.branch_code', $this->config->item('branch_code'));
		$query															=	$this->db->get();
		
		return 															($query->num_rows()==1);
	}
	
	function	get_all													($limit=10000, $offset=0)
	{		
		$this->db														->	from('customer_profiles');
		$this->db														->	where('deleted', 0);	
		$this->db														->	where('customer_profiles.branch_code', $this->config->item('branch_code'));	
		$this->db														->	order_by("profile_id", "asc");
		$this->db														->	limit($limit);
		$this->db														->	offset($offset);
		$data															=	$this->db->get();

		return 															$data;
	}
	
	function	count_all												()
	{
		$this						->	db->from('customer_profiles');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('customer_profiles.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on customer_profiles
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('customer_profiles');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										profile_name LIKE '%".$this->db->escape_like_str($search)."%' or
										profile_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('customer_profiles.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("profile_name", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find customer_profiles
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('customer_profiles');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										profile_name LIKE '%".$this->db->escape_like_str($search)."%' or
										profile_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('customer_profiles.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("profile_name", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->profile_name;
		}

		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	/*
	Gets information about a particular profile
	*/
	function	get_info			($profile_id)
	{
		$this						->	db->from('customer_profiles');	
		$this						->	db->where('deleted', 0);
		$this						->	db->where('customer_profiles.profile_id',$profile_id);
		$this						->	db->where('customer_profiles.branch_code', $this->config->item('branch_code'));
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
		$this						->	db->from('customer_profiles');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('profile_name', $_SESSION['transaction_info']->profile_name);
		$this						->	db->where('customer_profiles.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	/*
	Inserts or updates a customer profile
	*/
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add
			case	1:
					$this			->	db->insert('customer_profiles', $_SESSION['transaction_info']);
					$_SESSION['transaction_info']->profile_id	=	$this->db->insert_id();
			break;
			
			// update
			default:
					$this			->	db->where('profile_id', $_SESSION['transaction_id']);
					$this			->	db->where('customer_profiles.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('customer_profiles', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}

	function delete($profile_id)
	{
		$this->db->where('profile_id', $profile_id);
		$this->db->where('customer_profiles.branch_code', $this->config->item('branch_code'));
		$this->db->update('customer_profiles', array('deleted' => 1));
		return ($this->db->affected_rows() > 0);
	}

	// load pick list
	function	load_pick_list()
	{
		unset($_SESSION['G']->profile_pick_list);
		$profile_pick_list												=	array();
		
		foreach($this->get_all()->result() as $row)
		{
			$profile_pick_list[$row->profile_id] 						=	$row->profile_name;
		}
		$_SESSION['G']->profile_pick_list								=	$profile_pick_list;
		
		return;
	}
}
?>
