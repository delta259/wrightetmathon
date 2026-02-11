<?php
class Target extends CI_Model 
{	
	function	count_all			()
	{
		$this						->	db->from('sales_targets');
		$this						->	db->where('sales_targets.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	function	get_all				($year, $limit=10000, $offset=0)
	{
		$this						->	db->from('sales_targets');
		$this						->	db->where('target_year',$year);
		$this						->	db->where('sales_targets.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("target_year", "asc");
		$this						->	db->order_by("target_month", "asc");
		$this						->	db->order_by("target_day", "asc");
		$data						=	$this->db->get();

		return 						$data;
	}
		
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('sales_targets');
		$this						->	db->where("(
										target_year LIKE '%".$this->db->escape_like_str($search)."%' or
										target_month LIKE '%".$this->db->escape_like_str($search)."%' or
										target_shop_open_days LIKE '%".$this->db->escape_like_str($search)."%' or
										target_shop_turnover LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('sales_targets.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("target_year", "asc");
		$this						->	db->order_by("target_month", "asc");
		
		return 						$this->db->get();	
	}
	
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('sales_targets');

		$this						->	db->where("(
										target_year LIKE '%".$this->db->escape_like_str($search)."%' or
										target_month LIKE '%".$this->db->escape_like_str($search)."%' or
										target_shop_open_days LIKE '%".$this->db->escape_like_str($search)."%' or
										target_shop_turnover LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('sales_targets.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("target_year", "asc");
		$this						->	db->order_by("target_month", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->target_year;
		}

		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	function	get_info			($target_id)
	{
		$this						->	db->from('sales_targets');	
		$this						->	db->where('target_id', $target_id);
		$this						->	db->where('sales_targets.branch_code', $this->config->item('branch_code'));
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
	
	function	get_targets			($year, $month)
	{
		$this						->	db->from('sales_targets');	
		$this						->	db->where('target_year', $year);
		$this						->	db->where('target_month', $month);
		$this						->	db->where('sales_targets.branch_code', $this->config->item('branch_code'));
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
		$this						->	db->from('sales_targets');
		$this						->	db->where('target_year', $_SESSION['transaction_info']->target_year);
		$this						->	db->where('target_month', $_SESSION['transaction_info']->target_month);
		$this						->	db->where('sales_targets.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add category
			case	1:
					$this			->	db->insert('sales_targets', $_SESSION['transaction_info']);
					$_SESSION['transaction_id'] = $this->db->insert_id();
			break;
			
			// update category
			default:
					$this			->	db->where('target_id', $_SESSION['transaction_id']);
					$this			->	db->where('sales_targets.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('sales_targets', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
}
?>
