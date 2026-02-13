<?php
class Module extends CI_Model 
{
    function __construct()
    {
        parent::__construct();
    }
	
	function	exists($module_id)
	{
		$this						->	db->from('modules');	
		$this						->	db->where('module_id', $module_id);
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('modules');
		$this						->	db->order_by("modules.sort", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('modules');
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on modules
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('modules');
		$this						->	db->where("(
										module_name LIKE '%".$this->db->escape_like_str($search)."%'
										)");		
		$this						->	db->order_by("module_name", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find modules
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('modules');
		$this						->	db->where("(
										module_name LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->order_by("module_name", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->module_name;
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
	function	get_info			($module_id)
	{
		$this						->	db->from('modules');	
		$this						->	db->where('modules.module_id',$module_id);
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
		$this						->	db->from('modules');
		$this						->	db->where('module_name', $_SESSION['transaction_info']->module_name);
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
					$this			->	db->insert('modules', $_SESSION['transaction_info']);
					$_SESSION['transaction_info']->module_id	=	$this->db->insert_id();
			break;
			
			// update category
			default:
					$this			->	db->where('module_id', $_SESSION['transaction_id']);
					$this			->	db->update('modules', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}

	function delete($module_id)
	{
		$this->db->where('module_id', $module_id);
		$this->db->delete('modules');
		return ($this->db->affected_rows() > 0);
	}


	function	get_module_info			($module_name)
	{
		return							$this->db->get_where('modules',	array('module_name' => $module_name), 1);
	}
	
	function	get_module_name			($module_id)
	{
		$query						=	$this->db->get_where	('modules',	array('module_id' => $module_id), 1);
		
		if ($query->num_rows() ==1)
		{
			$row = $query->row();
			return $this->lang->line($row->name_lang_key);
		}
		
		return $this->lang->line('error_unknown');
	}
	
	function get_module_desc($module_id)
	{
		$query = $this->db->get_where('modules', array('module_id' => $module_id), 1);
		if ($query->num_rows() ==1)
		{
			$row = $query->row();
			return $this->lang->line($row->desc_lang_key);
		}
	
		return $this->lang->line('error_unknown');	
	}
	
	function get_all_modules()
	{
		$this->db->from('modules');
		$this->db->order_by("sort", "asc");
		return $this->db->get();		
	}	

	// load global modules to session
	function load_modules()
	{
		// initialise
		unset($_SESSION['G']->modules);
		$modules_list													=	array();
		
		// open definitions file
		$module_definitions												=	"/var/www/html/wrightetmathon/application/definitions/modules.def";
		$fp																=	fopen($module_definitions, "r");
		
		// test if file was opened
		if (!$fp)
		{
			$_SESSION['error_code']										=	'06080';
			$_SESSION['substitution_parms']								=	array($module_definitions);
			redirect("home");
		}
		
		// test EOF
		while(!feof($fp)) 
		{
			// read a line
			$line														=	fgets($fp);
			
			// ignore comment lines
			if (strpos($line, "//") === false) 
			{
				// explode the line to find the fields required
				// see modules.def for defintion of fields
				$modules												=	explode("->", $line);
				
				// test for definition line
				if ($modules[0] == "DEF")
				{
					// strip off first element - this makes the array have the same number of elements as the data lines
					// so the same key can be used.
					array_shift($modules);
					// store definition
					$definition											=	$modules;
				}
				else
				{
					// test controller really exists
					if (isset($modules[4]) && file_exists('../wrightetmathon/application/controllers/'.$modules[4].'.php'))
					{
						// process normal data lines
						// create associative array using definitions
						$module											=	array();
						foreach ($modules as $key => $value)
						{
							$module										+=	array("$definition[$key]" => $value);
						}
						// test active
						if ($module['deleted'] == '0')
						{
							// load the modules
							$modules_list[$module['module_id']]			=	$module;
						}
					}
				}
			}
		}
		
		// at EOF close the file
		fclose($fp);
		
		// load the modules to the session
		$_SESSION['G']->modules											=	$modules_list;
		
		// return
		return;
	}
}
?>
