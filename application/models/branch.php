<?php
class Branch extends CI_Model 
{	
	function branch_exists($branch_code)
	{
		$this					->	db->from('branch');	
		$this					->	db->where('branch_code', $branch_code);
		$query					=	$this->db->get();
		
		return ($query->num_rows()==1);
	}

	function branch_opened($branch_opened)
	{
		// set error
		$error					=	'';
		
		
		// check slashes
		$slash_1				=	substr($branch_opened, 2, 1);
		$slash_2				=	substr($branch_opened, 5, 1);
		if ($slash_1 != '/')
		{
			$error				=	$this->lang->line('config_not_slash');
		}
		if ($slash_2 != '/')
		{
			$error				=	$this->lang->line('config_not_slash');
		}

		// check date
		$day 					=	(int) substr($branch_opened, 0, 2);
		$month 					=	(int) substr($branch_opened, 3, 2);
		$year 					=	(int) substr($branch_opened, 6, 4);
		
		if (!checkdate($month, $day, $year))
		{
			$error				=	$this->lang->line('config_date_invalid');
		}
		
		// make sure date is not in future
		$branch_opened			=	str_replace('/', '-', $branch_opened);
		$opened					=	strtotime($branch_opened);
		$now					=	time(); 
		
		if ($opened > $now)
		{
			$error				=	$this->lang->line('config_date_future');
		}
		
		return	$error;
	}
	
	function	get_all													($limit=10000, $offset=0)
	{
		$this															->	db->from('branch');		
		$this															->	db->order_by("branch_description", "asc");
		$this															->	db->limit($limit);
		$this															->	db->offset($offset);
		$this															->	db->where('deleted', 0);
		$data															=	$this->db->get();

		return 															$data;
	}
	
	function	get_all_incl_deleted($limit=10000, $offset=0)
	{
		$this						->	db->from('branch');		
		$this						->	db->order_by("branch_description", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('branch');
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on suppliers
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('branch');	
		$this						->	db->where("(
										branch_code LIKE '%".$this->db->escape_like_str($search)."%' or
										branch_description LIKE '%".$this->db->escape_like_str($search)."%' or 
										branch_ip LIKE '%".$this->db->escape_like_str($search)."%' or 
										branch_user LIKE '%".$this->db->escape_like_str($search)."%' or 
										branch_database LIKE '%".$this->db->escape_like_str($search)."%'
										)");		
		$this						->	db->order_by("branch_description", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find suppliers
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('branch');
		$this						->	db->where("(
										branch_code LIKE '%".$this->db->escape_like_str($search)."%' or
										branch_description LIKE '%".$this->db->escape_like_str($search)."%' or 
										branch_ip LIKE '%".$this->db->escape_like_str($search)."%' or 
										branch_user LIKE '%".$this->db->escape_like_str($search)."%' or 
										branch_database LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->order_by("branch_description", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->branch_description;
		}

		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	/*
	Gets information about a particular branch
	*/
	function	get_info			($branch_code)
	{
		$this						->	db->from('branch');	
		$this						->	db->where('branch.branch_code',$branch_code);
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
		$this						->	db->from('branch');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('branch_code', $_SESSION['transaction_info']->branch_code);
		return							$this->db->count_all_results();
	}
	
	// common check duplicate
	function 	check_duplicate_ip		()
	{		
		$results					=	array();
		$success					=	TRUE;
		
		$this						->	db->from('branch');
		$this						->	db->where('branch_ip', $_SESSION['transaction_info']->branch_ip);
		$this						->	db->order_by("branch_ip", "asc");
		$results					=	$this->db->get()->result_array();

		// if there is a record then branch is duplicate
		switch ($_SESSION['new'])
		{
			case	1:
				if (count($results) > 0)
				{
					$success		=	FALSE;
				}
			break;
			
			default:
				if (count($results) > 1)
				{
					$success		=	FALSE;
				}
			break;
		}
			
		return							$success;
	}
	
	/*
	Inserts or updates a branch
	*/
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add branch
			case	1:
					$this->db->insert('branch', $_SESSION['transaction_info']);
			break;
			
			// update branch
			default:
					$this->db->where('branch_code', $_SESSION['transaction_id']);
					$this->db->update('branch', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
	
	/*
	Soft-delete a branch (set deleted=1)
	*/
	function delete($branch_code)
	{
		$this->db->where('branch_code', $branch_code);
		return $this->db->update('branch', array('deleted' => 1));
	}

	/*
	truncate the branch table
	*/
	function truncate()
	{
        $this->db->truncate('branch');
        
        return true;
	}

	function get_all_ipv4()
	{
		$this->db->from('branch');
		$this->db->where("ospos_branch.branch_ip NOT LIKE '%127.0.0.1%' AND ospos_branch.branch_ip NOT LIKE '%192.168.%' AND ospos_branch.deleted = '0'");
		$data_ipv4 = $this->db->get();

		return $data_ipv4;
	}

	// load ipv4 boutique pick list
	function load_pick_list()
	{
		unset($_SESSION['G']->branch_pick_list);
		$branch_pick_list = array();
		$branch_description_pick_list = array();
		
		foreach($this->get_all_ipv4()->result() as $row)
		{
			$branch_pick_list[$row->branch_code] = array("branch_code" => $row->branch_code, "branch_description" => $row->branch_description, "branch_ip" => $row->branch_ip, "branch_database" => $row->branch_database);
			$branch_description_pick_list[$row->branch_code] = $row->branch_description;
		}

		$_SESSION['G']->branch_pick_list = $branch_pick_list;
		$_SESSION['G']->branch_description_pick_list = $branch_description_pick_list;
		
		return;
	}
}
?>
