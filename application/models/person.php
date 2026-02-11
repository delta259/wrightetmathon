<?php
	// this controller is not used

class Person extends CI_Model 
{
	/*Determines whether the given person exists*/
	function	exists				($person_id)
	{
		$this						->	db->from('people');	
		$this						->	db->where('people.person_id',$person_id);
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return 						($query->num_rows()==1);
	}
	
	/*Gets all people*/
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('people');
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("last_name", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		return						$this->db->get();		
	}
	
	function	count_all			()
	{
		$this						->	db->from('people');
		$this						->	db->where('deleted',0);
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Gets information about a person as an array.
	*/
	function	get_info			($person_id)
	{
		$query						=	$this->db->get_where('people', array('person_id' => $person_id), 1);
		
		if($query->num_rows()==1)
		{
			return 					$query->row();
		}
		else
		{
			//create object with empty properties.
			$fields 				=	$this->db->list_fields('people');
			$person_obj				=	new stdClass;
			
			foreach ($fields as $field)
			{
				$person_obj->$field='';
			}
			
			return 					$person_obj;
		}
	}
	
	/*
	Get people with specific ids
	*/
	function	get_multiple_info	($person_ids)
	{
		$this						->	db->from('people');
		$this						->	db->where_in('person_id',$person_ids);
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("last_name", "asc");
		return						$this->db->get();		
	}
	
	/*
	Inserts or updates a person
	*/
	function	save				()
	{		
		// create the person data set
		$_SESSION['transaction_data_set']								=	new stdClass();
		$_SESSION['transaction_data_set']->first_name					=	$_SESSION['transaction_info']->first_name;
		$_SESSION['transaction_data_set']->last_name					=	$_SESSION['transaction_info']->last_name;
		$_SESSION['transaction_data_set']->zip							=	$_SESSION['transaction_info']->zip;
		$_SESSION['transaction_data_set']->phone_number					=	$_SESSION['transaction_info']->phone_number;
		$_SESSION['transaction_data_set']->email						=	$_SESSION['transaction_info']->email;
		$_SESSION['transaction_data_set']->address_1					=	$_SESSION['transaction_info']->address_1;
		$_SESSION['transaction_data_set']->address_2					=	$_SESSION['transaction_info']->address_2;			
		$_SESSION['transaction_data_set']->city							=	$_SESSION['transaction_info']->city;
		$_SESSION['transaction_data_set']->state						=	$_SESSION['transaction_info']->state;
		$_SESSION['transaction_data_set']->country_id					=	$_SESSION['transaction_info']->country_id;
		$_SESSION['transaction_data_set']->country						=	$_SESSION['G']->country_pick_list[$_SESSION['transaction_info']->country_id];
		$_SESSION['transaction_data_set']->comments						=	$_SESSION['transaction_info']->comments;
		$_SESSION['transaction_data_set']->sex							=	$_SESSION['transaction_info']->sex;
		$_SESSION['transaction_data_set']->dob_day						=	$_SESSION['transaction_info']->dob_day;
		$_SESSION['transaction_data_set']->dob_month					=	$_SESSION['transaction_info']->dob_month;
		$_SESSION['transaction_data_set']->dob_year						=	$_SESSION['transaction_info']->dob_year;
		$_SESSION['transaction_data_set']->branch_code					=	$_SESSION['transaction_info']->branch_code;

		
		switch ($_SESSION['new'])
		{
			case 2:

					$report_data = array();
					$this							->	db->select();
					$this							->	db->from('people, customers');
					$this							->	db->where('customers.person_id = `ospos_people`.person_id');
					$this							->	db->where('last_name = "'.$_SESSION['transaction_data_set']->last_name.'"');
					$this							->	db->where('first_name = "'.$_SESSION['transaction_data_set']->first_name.'"');
					$this							->	db->where('deleted = "0"');
					$report_data 					= 	$this->db->get()->result_array();
					$_SESSION['transaction_info']->person_id					=	$report_data[0]["person_id"];

					//si la personne existe, fusionner
					if(!empty($report_data))
					{

						$this	->	db->where('customers.person_id = "'.$report_data[0]["person_id"].'"');
						$fidelity_points = $report_data[0]['fidelity_points'] + $_SESSION['transaction_info']->fidelity_points;
						$this	->	db->update('customers', array('fidelity_points' => $fidelity_points));
						$_SESSION["exist"]= 1;
						return true;
					}
					
					//sinon, l'ajouter
					else
					{
						$_SESSION['transaction_data_set']->person_id = NULL;

						// add record
						if ($this->db->insert('people', $_SESSION['transaction_data_set']))
						{
							// insert succeeded - get person id for added record
							$_SESSION['transaction_info']->person_id = $this->db->insert_id();
							$_SESSION["exist"]= 2;
							return true;
						}
					}

					
							
					// insert failed
					return false;
			
			break;
			
			case	1:
					// load ID
					$_SESSION['transaction_data_set']->person_id		=	NULL;

					// add record
					if ($this->db->insert('people', $_SESSION['transaction_data_set']))
					{
						// insert succeeded - get person id for added record
						$_SESSION['transaction_info']->person_id		=	$this->db->insert_id();
						return 				true;
					}
					
					// insert failed
					return 					false;
			break;
					
			default:
					// load ID
					$_SESSION['transaction_data_set']->person_id		=	$_SESSION['transaction_info']->person_id;
					
					// update record
					$this->db->											where('person_id', $_SESSION['transaction_data_set']->person_id);
					$this->db->											where('people.branch_code', $this->config->item('branch_code'));
					$this->db->											update('people', $_SESSION['transaction_data_set']);
			break;	
		}
	}
/** GARRISON ADDED 4/25/2013 IN PROGRESS **/
	/*
	 Get search suggestions to find customers
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		$suggestions				=	array();
	
//		$this->db->select("person_id");
		$this->db->from('people');
//		$this->db->where('deleted',0);
//		$this->db->where('person_id',$this->db->escape($search));
//		$this->db->like('first_name',$this->db->escape_like_str($search));
//		$this->db->or_like('last_name',$this->db->escape_like_str($search));
//		$this->db->or_like("CONCAT(`first_name`,' ',`last_name`)",$this->db->escape_like_str($search));
//		$this->db->or_like('email',$search);
//		$this->db->or_like('phone_number',$search);
//		$this->db->order_by('last_name', "asc");
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$by_person_id = $this->db->get();

		foreach($by_person_id->result() as $row)
		{
			$suggestions[]=$row->person_id;
		}
	
		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
	}
	
	/*
	Deletes one Person (doesn't actually do anything)
	*/
	function	delete				($person_id)
	{
		return 						true;
	}
	
	/*
	Deletes a list of people (doesn't actually do anything)
	*/
	function	delete_list			($person_ids)
	{	
		return 						true;	
	}
//	SELECT `email` FROM `ospos_people` WHERE `email` LIKE "%@%" AND `person_id`="'. $_SESSION['id_client'] . '"
	function get_info_people($inputs)
	{
		$this->db->from('people');
		$this->db->like('email', '@');
		foreach($inputs as $key => $input)
		{
			$this->db->where($key, $input);
		}
		
		$data = $this->db->get()->result_array();
		return $data;

	}

	function update_people($update_people)
	{
		$this->db->where('person_id', $update_people->person_id);
		$this->db->where('people.branch_code', $this->config->item('branch_code'));
		return $this->db->update('people', $update_people);
	}

}
?>
