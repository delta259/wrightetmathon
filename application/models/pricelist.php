<?php
class Pricelist extends CI_Model 
{	
	function	exists($pricelist_id)
	{
		$this						->	db->from('pricelists');	
		$this						->	db->where('pricelist_id', $pricelist_id);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('pricelists.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('pricelists');
		$this						->	db->where('deleted', 0);	
		$this						->	db->where('pricelists.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("pricelist_order", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('pricelists');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('pricelists.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on pricelists
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('pricelists');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										pricelist_name LIKE '%".$this->db->escape_like_str($search)."%' or
										pricelist_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('pricelists.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("pricelist_name", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find pricelists
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('pricelists');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										pricelist_name LIKE '%".$this->db->escape_like_str($search)."%' or
										pricelist_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('pricelists.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("pricelist_name", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->pricelist_name;
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
	function	get_info			($pricelist_id)
	{
		$this						->	db->from('pricelists');	
		$this						->	db->where('deleted', 0);
		$this						->	db->where('pricelists.pricelist_id',$pricelist_id);
		$this						->	db->where('pricelists.branch_code', $this->config->item('branch_code'));
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
		$this						->	db->from('pricelists');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('pricelist_name', $_SESSION['transaction_info']->pricelist_name);
		$this						->	db->where('pricelists.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	// common check duplicate default
	function 	check_duplicate_default		()
	{		
		$this						->	db->from('pricelists');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('pricelist_default', $_SESSION['transaction_info']->pricelist_default);
		$this						->	db->where('pricelists.branch_code', $this->config->item('branch_code'));
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
					$this			->	db->insert('pricelists', $_SESSION['transaction_info']);
					$_SESSION['transaction_info']->pricelist_id	=	$this->db->insert_id();
			break;
			
			// update category
			default:
					$this			->	db->where('pricelist_id', $_SESSION['transaction_id']);
					$this			->	db->where('pricelists.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('pricelists', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}

	function delete($pricelist_id)
	{
		$this->db->where('pricelist_id', $pricelist_id);
		$this->db->where('pricelists.branch_code', $this->config->item('branch_code'));
		$this->db->update('pricelists', array('deleted' => 1));
		return ($this->db->affected_rows() > 0);
	}

	// reload pick list
	function	load_pick_list()
	{
		unset($_SESSION['G']->pricelist_pick_list);
		$pricelist_pick_list											=	array();
		
		foreach($this->get_all()->result() as $row)
		{
			$pricelist_pick_list[$row->pricelist_id] 					=	$row->pricelist_name;
		}
		$_SESSION['G']->pricelist_pick_list								=	$pricelist_pick_list;
		
		return;
	}
}
?>
