<?php
class Category extends CI_Model
{
	/*
	Determines if a given category_id is an category
	*/
	function exists($category_id)
	{
		$this						->	db->from('categories');
		$this						->	db->where('category_id', $category_id);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('categories.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by('category_name', 'desc');
		$query 						=	$this->db->get();
		return 						($query->num_rows()==1);
	}
	
	function exists_by_name($category_name)
	{
		$this						->	db->from('categories');
		$this						->	db->where('category_name', $category_name);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('categories.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by('category_name', 'desc');
		$query 						=	$this->db->get();
		return 						($query->num_rows()==1);
	}

	function get_all($limit=10000, $offset=0)
	{
		$this						->	db->from('categories');
		
		// select records based on whether normal or undelete mode
		switch ($_SESSION['undel'])
		{
			case	1:
					$this						->	db->where('deleted', 1);
			break;
			
			default:
					$this						->	db->where('deleted', 0);
			break;
		}
		
		$this						->	db->where('categories.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by('category_name', 'asc');
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		return 						$this->db->get()->result_array();
	}
	
	function count_all()
	{
		$this						->	db->from('categories');
		$this						->	db->where('deleted',0);
		$this						->	db->where('categories.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by('category_name', 'desc');
		return 						$this->db->count_all_results();
	}

	function get_info($category_id)
	{
		$this						->	db->from('categories');
		$this						->	db->where('category_id',$category_id);
		$this						->	db->where('categories.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by('category_name', 'desc');
		$query						=	$this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $category_id is NOT an category
			$category_obj=new stdClass();

			//Get all the fields from categories table
			$fields = $this->db->list_fields('categories');

			foreach ($fields as $field)
			{
				$category_obj->$field='';
			}

			return $category_obj;
		}
	}

	function get_info_by_name($category_name)
	{
		$this->db->from('categories');
		$this->db->where('category_name', $category_name);
		$this->db->where('categories.branch_code', $this->config->item('branch_code'));
		$this->db->where('deleted', 0);
		$this						->	db->order_by('category_name', 'desc');
		return $this->db->get();
	}
	
	/*
	Get an category id given an category number
	*/
	function get_category_id($category_name)
	{
		$this->db->from('categories');
		$this->db->where('category_name',$category_name);
		$this->db->where('deleted',0);
		$this->	db->where('categories.branch_code', $this->config->item('branch_code'));
		$this->db->order_by('category_name', 'desc');
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row()->category_id;
		}

		return false;
	}

	/*
	Gets information about multiple categories
	*/
	function get_multiple_info($category_ids)
	{
		$this->db->from('categories');
		$this->db->where_in('category_id',$category_ids);
		$this->db->where('deleted',0);
		$this->db->where('categories.branch_code', $this->config->item('branch_code'));
		$this->db->order_by('category_name', 'desc');
		return $this->db->get();
	}

	// load category pick list
	function load_pick_list()
	{
		unset($_SESSION['G']->category_pick_list);
		$category_pick_list												=	array();
		$categories							=	$this->Category->get_all();
		foreach($categories as $row)
		{
			$category_pick_list[$row['category_id']] 						=	$row['category_name'].' : '.$row['category_desc'];
		}
		// add blank line
		$category_pick_list[0] 											=	' ';    //rajout une ligne vide dans le tableau
		$_SESSION['G']->category_pick_list								=	$category_pick_list;
		
		return;
	}
	
	/*
	Inserts or updates a category
	*/
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add category
			case	1:
					$this->db->											insert('categories', $_SESSION['transaction_info']);
					$_SESSION['transaction_info']->category_id 			=	$this->db->insert_id();
			break;
			
			// update category
			default:
					$this->db->											where('category_id', $_SESSION['transaction_info']->category_id);
					$this->db->											where('categories.branch_code', $this->config->item('branch_code'));
					$this->db->											update('categories', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}

	//save for vs
	function save_vs_sale($update_category)
	{
		$this->db->where('category_id', $update_category['category_id']);
		$this->db->where('categories.branch_code', $this->config->item('branch_code'));
		$this->db->update('categories', $update_category);
		return true;
	}

	/*
	Updates multiple categories at once
	*/
	function update_multiple($category_data,$category_ids)
	{
		$this->db->where_in('category_id',$category_ids);
		return $this->db->update('categories',$category_data);
	}

	/*
	Deletes one category
	*/
	function delete($category_id)
	{
		$this->db->where('category_id', $category_id);
		$this->db->where('categories.branch_code', $this->config->item('branch_code'));
		return $this->db->update('categories', array('deleted' => 1));
	}
	
	/*
	Undeletes one category
	*/
	function undelete($category_id)
	{
		$this->db->where('category_id', $category_id);
		$this->db->where('categories.branch_code', $this->config->item('branch_code'));
		return $this->db->update('categories', array('deleted' => 0));
	}

 	/*
	Get search suggestions to find categories
	*/
	function get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// set search on deleted or not items
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('categories.deleted', 1);
			break;
			
			default:
					$this			->	db->where('categories.deleted', 0);
			break;
		}
		
		$this->db->from('categories');
		$this->db->like('category_name', $search);
		$this->	db->where('categories.branch_code', $this->config->item('branch_code'));
		$this->db->order_by('category_name', 'desc');
		$by_number = $this->db->get();
		
		foreach($by_number->result() as $row)
		{
			$suggestions[]=$row->category_name;
		}
			
		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
	}
	
	function search($search)
	{
		// set search on deleted or not items
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('categories.deleted', 1);
			break;
			
			default:
					$this			->	db->where('categories.deleted', 0);
			break;
		}
		
		$this						->	db->from('categories');
		$this						->	db->like("category_name", $this->db->escape_like_str($search));
		$this						->	db->where('categories.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by('category_name', 'desc');
		return 						$this->db->get()->result_array();
	}
	
	// common check duplicate
	function 	check_duplicate		($category_id, $category_name)
	{		
		$results					=	array();
		
		switch ($_SESSION['new'])
		{
			case	1:
					$this			->	db->from('categories');
					$this			->	db->where("category_name = '".$category_name."'");
					$this			->	db->where('categories.branch_code', $this->config->item('branch_code'));
					$this			->	db->order_by('category_name', 'desc');
					$results		=	$this->db->get()->result_array();
			break;
			
			default:
					$this			->	db->from('categories');
					$this			->	db->where('category_id != '.$category_id);
					$this			->	db->where("category_name = '".$category_name."'");
					$this			->	db->where('categories.branch_code', $this->config->item('branch_code'));
					$this			->	db->order_by('category_name', 'desc');
					$results		=	$this->db->get()->result_array();
			break;
		}

		// if there is a record then category is duplicate
		if (count($results) 					> 0)
		{
			$success							=	FALSE;
		}
		else
		{
			$success							=	TRUE;
		}
			
		return									$success;
	}
}
?>
