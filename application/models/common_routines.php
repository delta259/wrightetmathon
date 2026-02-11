<?php
class Common_routines extends CI_Model
{
	// format the transaction customer / supplier / salesperson name
	function format_full_name($last_name_in, $first_name_in)
	{		
		$full_name_out = strtoupper($last_name_in).', '.ucfirst(strtolower($first_name_in));
		
		return $full_name_out;
	}
	
	// set up the pagination
	function set_up_pagination()
	{				
		$config						=	array();
		$config['per_page']			=	20;
		$config['uri_segment']		= 	3;
		$config['first_link']		=	$this->lang->line('common_first');
		$config['last_link']		=	$this->lang->line('common_last');
		$config['next_link']		=	$this->lang->line('common_next');
		$config['prev_link']		=	$this->lang->line('common_previous');	
		
		return $config;
	}
	
	// create the csv file
	function create_csv($data, $month_end, $specific)
	{
		if(isset($_SESSION['premier_rapport_today']))
		{
			switch($_SESSION['premier_rapport_today'])
			{
				case 1:
					//today
					// Create the CSV...
					$file_name 		=	date('Ymd - His', time()).' - '.trim(str_replace(array(' ', '/', '\\'), '', $data['title'])).' '.$this->db->database.'today.csv';
		
				break;
				case 2:
					//mois
					// Create the CSV...
					$file_name 		=	date('Ymd - His', time()).' - '.trim(str_replace(array(' ', '/', '\\'), '', $data['title'])).' '.$this->db->database.'mois.csv';
		
				break;
			}
		}
		else
		{
            // Create the CSV...
            $file_name 		=	date('Ymd - His', time()).' - '.trim(str_replace(array(' ', '/', '\\'), '', $data['title'])).' '.$this->db->database.'.csv';
		}
		$path_file_name	=	$this->config->item('RPsavepath').$file_name;
		
		// output the title to the file 
		file_put_contents($path_file_name,	$data['title'],			FILE_APPEND);
		file_put_contents($path_file_name,	';',					FILE_APPEND);
		file_put_contents($path_file_name,	"\n",					FILE_APPEND);
		
		// output the sub-title to the file
		file_put_contents($path_file_name,	$data['subtitle'],		FILE_APPEND);
		file_put_contents($path_file_name,	';',					FILE_APPEND);
		file_put_contents($path_file_name,	"\n",					FILE_APPEND);
		
		// output the column headers
		switch ($specific)
		{
			case 0:
				foreach ($data['headers'] as $header)
				{
					file_put_contents($path_file_name,	$header,			FILE_APPEND);
					file_put_contents($path_file_name,	';',				FILE_APPEND);				
				}
				file_put_contents($path_file_name,	"\n",					FILE_APPEND);
				break;
			
			case 1:
				foreach ($data['headers']['summary'] as $header)
				{
					file_put_contents($path_file_name,	$header,			FILE_APPEND);
					file_put_contents($path_file_name,	';',				FILE_APPEND);				
				}
				file_put_contents($path_file_name,	"\n",					FILE_APPEND);
				break;
			default:
				break;
		}
		
		// output the data - get each data row and then each cell in the row.
		switch ($specific)
		{
			case 0:
				foreach ($data['data'] as $row)
				{
					foreach ($row as $cell)
					{
						// determine if this cell contains a href and strip off html stuff
						$result			=	strstr($cell, 'href');
						if ($result != '')
						{
							$pieces1	=	array();
							$pieces1	=	explode('>', $cell);
							$pieces2	=	array();
							$pieces2	=	explode('<', $pieces1[1]);
							$cell		=	$pieces2[0];
						}
						
						// write the cell
						file_put_contents($path_file_name,	$cell,			FILE_APPEND);
						file_put_contents($path_file_name,	';',			FILE_APPEND);
					}
					file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				}
				
				// output the summary data
				file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				file_put_contents($path_file_name,	'--------------------------'. ';'.'---------------',			FILE_APPEND);
				file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				foreach($data['summary_data'] as $name=>$value) 
				{
					file_put_contents($path_file_name,	$this->lang->line('reports_'.$name). ';'.number_format($value, 2),			FILE_APPEND);
					file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				}
				break;
			case 1:
				foreach ($data['summary_data'] as $row)
				{
					foreach ($row as $cell)
					{
						// determine if this cell contains a href and strip off html stuff
						$result			=	strstr($cell, 'href');
						if ($result != '')
						{
							$pieces1	=	array();
							$pieces1	=	explode('>', $cell);
							$pieces2	=	array();
							$pieces2	=	explode('<', $pieces1[1]);
							$cell		=	$pieces2[0];
						}
						
						// write the cell
						file_put_contents($path_file_name,	$cell,			FILE_APPEND);
						file_put_contents($path_file_name,	';',			FILE_APPEND);
					}
					file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				}
				
				// output the summary data
				file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				file_put_contents($path_file_name,	'--------------------------'. ';'.'---------------',			FILE_APPEND);
				file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				foreach($data['overall_summary_data'] as $name=>$value) 
				{
					file_put_contents($path_file_name,	$this->lang->line('reports_'.$name). ';'.number_format($value, 2),			FILE_APPEND);
					file_put_contents($path_file_name,	"\n",				FILE_APPEND);
				}
				break;
			default:
				break;
		}
		
		// skip some lines
		file_put_contents($path_file_name,	"\n",				FILE_APPEND);
		file_put_contents($path_file_name,	"\n",				FILE_APPEND);
		
		// return to calling function
		if ($month_end != 1)
		{
			$message						=	' - Requested CSV report created';
			$data							=	array();
			$data['message']				=	$message;
			$this							->	load->view('reports/listing', $data);
		}
		else
		{
			return;
		}
	}
	
	// determine route
	function determine_route($route_code)
	{
		$route_info					=	array();
		$this						->	db->from('routing');
		$this						->	db->where('route_code', $route_code);
		$route_info					=	$this->db->get();
		
		if($route_info->num_rows() == 1)
		{
			return $route_info->row();
		}
		else
		{
			return;
		}
	}
	
	// barcode
	function generate_barcode($barcode)
	{
		// set fonts
		$barcode_font				=	'/var/www/html/wrightetmathon/application/fonts/logitogo/Code-39-Logitogo.ttf';
		$plain_font					=	$this->config->item('default_label_font');

		// create the image
		$img						=	imagecreatetruecolor(400,80);

		// First call to imagecolorallocate is the background color
		$white						=	imagecolorallocate($img, 255, 255, 255);
		$black						=	imagecolorallocate($img, 0, 0, 0);
		
		imagefilledrectangle($img, 0, 0, 400, 80, $white);
		
		//	Create the barcode
		//	imagettftext			($img,	$fontsize,	$angle,	$xpos,	$ypos,	$color,	$fontfile,		$text);

			imagettftext			($img,	12,			0,		2,		20,		$black,	$barcode_font,	'*'.$barcode.'*');

		// create the text
			imagettftext			($img,	10,			0,		2,		40,		$black,	$plain_font,	'*'.$barcode.'*');

		// save image
		$image_path					=	'/var/www/html/wrightetmathon/barcodes/'.$barcode.'.jpeg';
		imagejpeg					($img, $image_path, 100);
		
		// save memory	
		imagedestroy				($img);
		
		// save image path for output
		$image_path					=	base_url().'barcodes/'.$barcode.'.jpeg';
		
		//return
		return						$image_path;
	}
	
	
	// barcode housekeeping. Called from login controller.
	function delete_barcodes()
	{
		array_map('unlink', glob("/var/www/html/wrightetmathon/barcodes/*"));
	}
	
	// label housekeeping. Called from login controller.
	function delete_labels()
	{
		array_map('unlink', glob("/var/www/html/wrightetmathon/label/*.png"));
		array_map('unlink', glob("/var/www/html/wrightetmathon/label/Etiquettes_Prix/*.png"));
	}
	
	// log housekeeping. Called from login controller.
	function delete_logs()
	{
		array_map('unlink', glob("/var/www/html/wrightetmathon/application/logs/log*"));
	}
	
	// backup housekeeping. Called from login controller.
	function delete_backups()
	{
		array_map('unlink', glob("/var/www/html/wrightetmathon_backup/backup*"));
	}
	
	// backup housekeeping. Called from login controller.
	function delete_weblogs()
	{
		array_map('unlink', glob($this->config->item('SPsavepath')."/*"));
	}
	
	// write log
	function write_log($action, $username)
	{
		// output to log		
		// first try to open it.
		$fh 											=	fopen("/var/www/html/wrightetmathon.log", "a");

		// if found output to it.
		if($fh)
		{
			// get date and time
			$now										=	date("Y/m/d - H:i:s");
			
			fwrite($fh, $now.' '.$username.' '.$action);
			fwrite($fh, "\n");
			fclose($fh);
		}
	}
	
	// set form width
	function set_form_width()
	{
		return	600;
	}
	
	// set table line colour
	function set_line_colour()
	{
		// Initialize line_count if not set (PHP 8.3 compatibility)
		if (!isset($_SESSION['line_count'])) {
			$_SESSION['line_count'] = isset($_SESSION['line_number']) ? $_SESSION['line_number'] : 0;
		}

		// test line count for colour
		if ($_SESSION['line_count'] & 1)
		{
			//odd, set colour of line
			$_SESSION['line_colour']									=	'#EBF4F8';
		}
		else
		{
			//even, set colour of line
			$_SESSION['line_colour']									=	'white';
		}

		return;
	}
	
	// get transaction info
	function 	get_message_info		($error_code)
	{				
		$this						->	db->from('messages');
		$this						->	db->where('message_code', $error_code);
		$this						->	db->where('messages.branch_code', $this->config->item('branch_code'));
		return							$this->db->get();
	}
	
	// common check email format
	function 	check_email_format	()
	{
		if (filter_var($_SESSION['transaction_info']->email, FILTER_VALIDATE_EMAIL))
		{
			$success				=	TRUE;
		}
		else
		{
			$success				=	FALSE;
		}
		
		return						$success;
	}
	
	function 	common_check_duplicate			()
	{		
		// set dbparms if checking email duplicate
		if ($_SESSION['check_email_dup']								==	1)
		{
			$_SESSION['from_file']										=	'people';
			$_SESSION['needle']											=	$_SESSION['transaction_info']->email;
			$_SESSION['search_fields']									=	array('email');
			$_SESSION['action']											=	'search';
			$_SESSION['make_join']										=	0;
			$_SESSION['sequence']										=	'asc';
			$_SESSION['like_type']										=	'after';
			$_SESSION['limit']											=	10000;
			
			// set up the where select depending on adding or updating
			switch ($_SESSION['new'])
			{
				case	1:
						unset($_SESSION['where_select']);
				break;
						
				default:
						$_SESSION['where_select']						=	'person_id !='.$_SESSION['transaction_info']->person_id;
				break;	
			}
		}		
		
		// get results
		$results								=	array();
		$results								=	$this->common_searchsuggest();

		// reset dbparms
		$this									->	dbparms();

		// if there is a record then email is duplicate
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
	
	function	common_searchsuggest()
	{		
		// initialise
		$suggestions 				=	array();

		// get search results by search fields
		foreach ($_SESSION['search_fields'] as $search_field)
		{
			if ($_SESSION['action']	==	'suggest')
			{
				$this				->	db->select($search_field);
				$this				->	db->distinct();
			}
				
			$this					->	db->from($_SESSION['from_file']);

			if ($_SESSION['make_join'] == 1)
			{
				$this				->	db->join($_SESSION['join_file'], $_SESSION['join_condition']);	
			}

			if (!empty($_SESSION['where_select']))
			{
				$this				->	db->where($_SESSION['where_select']);
			}

			$this					->	db->where($_SESSION['from_file'].'.branch_code', $this->config->item('branch_code'));
			$this					->	db->order_by($search_field, $_SESSION['sequence']);
			$this					->	db->like($search_field, $_SESSION['needle'], $_SESSION['like_type']);
			
			$by_search_field		=	array();
			$by_search_field		=	$this->db->get()->result_array();	
			
			// load output array
			if (count($by_search_field) > 0)
			{
				switch ($_SESSION['action'])
				{
					case 'suggest':
						foreach($by_search_field as $row)
						{
							$suggestions[]	=	$row[$search_field];
						}
					break;
					case 'search':
						$suggestions		=	array_merge($suggestions, $by_search_field);
					break;
				}
			}
		}
		
		// check limit
		if(count($suggestions) > $_SESSION['limit'])
		{
			$suggestions			=	array_slice($suggestions, 0, $_SESSION['limit']);
		}
		
		// reset the action
		unset($_SESSION	['action']);
		
		// return the search results
		return 						$suggestions;	
	}
	
	// set dbparms for controller
	function 	dbparms				()
	{
		// initialise
		$search_fields							=	array();
		
		// set the dbparms depending on the controller
		switch ($_SESSION['controller_name'])
		{
			case 'items':
				$search_fields					=	array('item_number', 'name');
				
				$_SESSION['from_file']			=	$_SESSION['controller_name'];
				$_SESSION['from_field_name']	=	$_SESSION['controller_name'].'.item_id';
				$_SESSION['deleted_field_name']	=	$_SESSION['controller_name'].'.deleted';
				$_SESSION['where_select']		=	$_SESSION['controller_name'].".deleted = 0 AND ".$this->db->dbprefix($_SESSION['controller_name']).".category_id != 44";
				$_SESSION['search_fields']		=	$search_fields;																
				$_SESSION['join_file']			=	'categories';
				$_SESSION['order_by_1']			=	$_SESSION['controller_name'].'.item_number';
				$_SESSION['order_by_2']			=	NULL;
				$_SESSION['join_condition']		=	$_SESSION['controller_name'].'.category_id='.$_SESSION['join_file'].'.category_id';
				$_SESSION['route']				=	'IT';
				$_SESSION['like_type']			=	'both';
				$_SESSION['make_join']			=	1;	
				$_SESSION['primary_key']		=	'item_id';	
				$_SESSION['sequence_1']			=	'asc';
				$_SESSION['sequence_2']			=	'asc';														
			break;
			
			case 'categories':
				$search_fields		=	array('category_name');
				
				$_SESSION['from_file']			=	$_SESSION['controller_name'];
				$_SESSION['from_field_name']	=	$_SESSION['controller_name'].'.category_id';
				$_SESSION['deleted_field_name']	=	$_SESSION['controller_name'].'.deleted';
				$_SESSION['where_select']		=	$_SESSION['controller_name'].'.deleted = 0';
				$_SESSION['search_fields']		=	$search_fields;																
				$_SESSION['order_by_1']			=	'category_name';
				$_SESSION['order_by_2']			=	NULL;
				$_SESSION['route']				=	'TZ';
				$_SESSION['like_type']			=	'after';
				$_SESSION['make_join']			=	0;
				$_SESSION['primary_key']		=	'category_id';
				$_SESSION['sequence_1']			=	'asc';
				$_SESSION['sequence_2']			=	'asc';						
			break;
			
			case 'customers':
				$search_fields		=	array('last_name', 'first_name', 'email', 'phone_number', 'account_number');
				
				$_SESSION['from_file']			=	$_SESSION['controller_name'];
				$_SESSION['from_field_name']	=	$_SESSION['controller_name'].'.person_id';
				$_SESSION['deleted_field_name']	=	$_SESSION['controller_name'].'.deleted';
				$_SESSION['where_select']		=	$_SESSION['controller_name'].'.deleted = 0';
				$_SESSION['search_fields']		=	$search_fields;																
				$_SESSION['join_file']			=	'people';
				$_SESSION['order_by_1']			=	$_SESSION['join_file'].'.last_name';
				$_SESSION['order_by_2']			=	NULL;
				$_SESSION['join_condition']		=	$_SESSION['controller_name'].'.person_id='.$_SESSION['join_file'].'.person_id';
				$_SESSION['route']				=	'TZ';
				$_SESSION['like_type']			=	'after';
				$_SESSION['make_join']			=	1;
				$_SESSION['primary_key']		=	'person_id';
				$_SESSION['sequence_1']			=	'asc';
				$_SESSION['sequence_2']			=	'asc';						
			break;
			
			case 'suppliers':
				$search_fields		=	array('last_name', 'first_name', 'email', 'phone_number', 'account_number', 'company_name');
				
				$_SESSION['from_file']			=	$_SESSION['controller_name'];
				$_SESSION['from_field_name']	=	$_SESSION['controller_name'].'.person_id';
				$_SESSION['deleted_field_name']	=	$_SESSION['controller_name'].'.deleted';
				$_SESSION['where_select']		=	$_SESSION['controller_name'].'.deleted = 0';
				$_SESSION['search_fields']		=	$search_fields;																
				$_SESSION['join_file']			=	'people';
				$_SESSION['order_by_1']			=	$_SESSION['join_file'].'.last_name';
				$_SESSION['order_by_2']			=	NULL;
				$_SESSION['join_condition']		=	$_SESSION['controller_name'].'.person_id='.$_SESSION['join_file'].'.person_id';
				$_SESSION['route']				=	'TZ';
				$_SESSION['like_type']			=	'after';
				$_SESSION['make_join']			=	1;
				$_SESSION['primary_key']		=	'person_id';
				$_SESSION['sequence_1']			=	'asc';
				$_SESSION['sequence_2']			=	'asc';		
			break;
			
			case 'employees':
				$search_fields		=	array('last_name', 'first_name', 'email', 'phone_number');
				
				$_SESSION['from_file']			=	$_SESSION['controller_name'];
				$_SESSION['from_field_name']	=	$_SESSION['controller_name'].'.person_id';
				$_SESSION['deleted_field_name']	=	$_SESSION['controller_name'].'.deleted';
				$_SESSION['where_select']		=	$_SESSION['controller_name'].'.deleted = 0';
				$_SESSION['search_fields']		=	$search_fields;																
				$_SESSION['join_file']			=	'people';
				$_SESSION['order_by_1']			=	$_SESSION['join_file'].'.last_name';
				$_SESSION['order_by_2']			=	NULL;
				$_SESSION['join_condition']		=	$_SESSION['controller_name'].'.person_id='.$_SESSION['join_file'].'.person_id';
				$_SESSION['route']				=	'TZ';
				$_SESSION['like_type']			=	'after';
				$_SESSION['make_join']			=	1;
				$_SESSION['primary_key']		=	'person_id';
				$_SESSION['sequence_1']			=	'asc';
				$_SESSION['sequence_2']			=	'asc';					
			break;
			
			case 'giftcards':
				$search_fields		=	array('last_name', 'first_name', 'email', 'phone_number', 'giftcard_number');
				
				$_SESSION['from_file']			=	$_SESSION['controller_name'];
				$_SESSION['from_field_name']	=	$_SESSION['controller_name'].'.giftcard_id';
				$_SESSION['deleted_field_name']	=	$_SESSION['controller_name'].'.deleted';
				$_SESSION['where_select']		=	$_SESSION['controller_name'].'.deleted = 0';
				$_SESSION['search_fields']		=	$search_fields;																
				$_SESSION['join_file']			=	'people';
				$_SESSION['order_by_1']			=	$_SESSION['join_file'].'.last_name';
				$_SESSION['order_by_2']			=	NULL;
				$_SESSION['join_condition']		=	$_SESSION['controller_name'].'.customer_id='.$_SESSION['join_file'].'.person_id';
				$_SESSION['route']				=	'TZ';
				$_SESSION['like_type']			=	'after';
				$_SESSION['make_join']			=	1;
				$_SESSION['primary_key']		=	'giftcard_id';
				$_SESSION['sequence_1']			=	'asc';
				$_SESSION['sequence_2']			=	'asc';		
			break;
			
			case 'reports':
				$search_fields		=	array('report_group', 'report_name');
				
				$_SESSION['from_file']			=	$_SESSION['controller_name'];
				$_SESSION['from_field_name']	=	$_SESSION['controller_name'].'.report_id';
				$_SESSION['deleted_field_name']	=	$_SESSION['controller_name'].'.deleted';
				$_SESSION['where_select']		=	$_SESSION['controller_name'].'.deleted = 0';
				$_SESSION['search_fields']		=	$search_fields;																
				$_SESSION['join_file']			=	NULL;
				$_SESSION['order_by_1']			=	$_SESSION['controller_name'].'.report_group_sequence';
				$_SESSION['order_by_2']			=	$_SESSION['controller_name'].'.report_name_sequence';
				$_SESSION['join_condition']		=	NULL;
				$_SESSION['route']				=	'TZ';
				$_SESSION['like_type']			=	'after';
				$_SESSION['make_join']			=	0;
				$_SESSION['primary_key']		=	'report_id';
				$_SESSION['sequence_1']			=	'asc';
				$_SESSION['sequence_2']			=	'asc';
				if ($_SESSION['logged_in_user_name'] != 'admin')
				{
					$_SESSION['where_select']	.=	" AND report_admin_only = 'N'";
				}	
			break;
		}
	}
	
	// initialise application
	function 	initialise				()
	{
		// get details for this employee
		$_SESSION['G']->login_employee_info								=	$this->Employee->get_info($_SESSION['G']->login_employee_id);
		
		// if this an admin user set admin flag
		if ($_SESSION['G']->login_employee_info->username == 'admin' OR $_SESSION['G']->login_employee_info->username == 'sys_admin')
		{
			$_SESSION['G']->login_employee_info->admin					=	1;
		}
		else
		{
			$_SESSION['G']->login_employee_info->admin					=	0;
		}
		
		// write log
		$action															=	'login';
		$this															->	write_log($action, $_SESSION['G']->login_employee_username);
		
		// load module definitions to session
		$this															->	Module->load_modules();
		
		// load currency details
		$_SESSION['G']->currency_details								=	$this->Currency->get_info($this->config->item('currency'));
		
		// store whether to show prices with or without tax
		// note - default loaded here, but this can change if user changes to a different currency by changing the price list 
		// in sales module.
		$_SESSION['price_with_tax']										=	$_SESSION['G']->currency_details->currency_tax;
		
		// load messages to session
		$this															->	Message->load_messages();
		
		// load pick lists - loaded here, but can be reloaded if user adds a new element to the table.
		$this															->	Currency->load_pick_list();
		$this															->	Pricelist->load_pick_list();
		$this															->	Supplier->load_pick_list();
		$this															->	Category->load_pick_list();
		$this															->	Customer_profile->load_pick_list();
		$this															->	Country->load_pick_list();
		$this															->	Timezone->load_pick_list();
		$this															->	Import->load_pick_list();
		$this															->	Item->load_bulk_actions_pick_list();
		$this															->	Transaction->load_stock_actions_pick_list();
		$_SESSION['G']->YorN_pick_list									=	array('Y'=>$this->lang->line('common_yes'), 'N'=>$this->lang->line('common_no'));
		$_SESSION['G']->distributor									    =	array('1'=>$this->lang->line('common_emplacement_1'), '4'=>$this->lang->line('common_emplacement_4'), '6'=>$this->lang->line('common_emplacement_6'), '8'=>$this->lang->line('common_emplacement_8'), '10'=>$this->lang->line('common_emplacement_10'), '50'=>$this->lang->line('common_emplacement_50'));
		$_SESSION['G']->vs_category                                     =   array('1' => 'ELiquide', '2' => 'Resistance', '3' => 'Kit et Batterie', '4' => 'Clearomiseur', '5' => 'Verre', '6' => 'Nicodose', '7' => 'Classique', '8' => 'Fraicheur', '9' => 'Fruité', '10' => 'Equilibré', '11' => 'Gourmand', '12' => 'Cocktail');
		$_SESSION['G']->vs_category_all                                 =   array(
			'1' => array('votreid' => '1', 'nom' => 'ELiquide', 'nomimage' => '', 'type' => '1'),
	        '2' => array('votreid' => '2','nom' => 'Resistance','nomimage' => '','type' => '2'),
			'3' => array('votreid' => '3','nom' => 'Kit et Batterie','nomimage' => '','type' => '2'),
	        '4' => array('votreid' => '4','nom' => 'clearomiseur','nomimage' => '','type' => '2'),
			'5' => array('votreid' => '5','nom' => 'Verre','nomimage' => '','type' => '2'),
	        '6' => array('votreid' => '6','nom' => 'Nicodose','nomimage' => '','type' => '1'),
	        '7' => array('votreid' => '7','nom' => 'Classique','nomimage' => '','type' => '1'),
	        '8' => array('votreid' => '8','nom' => 'Fraicheur','nomimage' => '','type' => '1'),
	        '9' => array('votreid' => '9','nom' => 'Fruité','nomimage' => '','type' => '1'),
	        '10' => array('votreid' => '10','nom' => 'Equilibré','nomimage' => '','type' => '1'),
	        '11' => array('votreid' => '11','nom' => 'Gourmand','nomimage' => '','type' => '1'),
	        '12' => array('votreid' => '12','nom' => 'Cocktail','nomimage' => '','type' => '1')
			);
		$_SESSION['G']->vs_category_type                                =   array('1' => 'ELiquide', '2' => 'Materiel');
		$_SESSION['G']->oneorzero_pick_list								=	array('1'=>$this->lang->line('common_yes'), '0'=>$this->lang->line('common_no'));
		$_SESSION['G']->sex_pick_list									=	array('F'=>$this->lang->line('common_female'), 'M'=>$this->lang->line('common_male'));
		$_SESSION['G']->tracker_status_pick_list						=	array	(
																					'1'=>$this->lang->line('trackers_reported'), 
																					'2'=>$this->lang->line('trackers_open'),
																					'3'=>$this->lang->line('trackers_assigned'),
																					'4'=>$this->lang->line('trackers_fixed'),
																					'5'=>$this->lang->line('trackers_committed'),
																					'6'=>$this->lang->line('trackers_production')
																					);
		$_SESSION['G']->number = array('point' => '.', 'virgule' => ',');
		$this->Branch->load_pick_list();

		// housekeeping
		$this															->	delete_barcodes();
		$this															->	delete_labels();
		$this															->	delete_logs();
		$this															->	delete_backups();
		$this															->	delete_weblogs();
	}
	
	function get_conn_parms($redirect)
	{
		// initialise 
		$config_file													=	"/var/www/html/wrightetmathon/application/config/database.php";
		
		// find and set server name
		$found_flag														=	'N';
		$search															=	'hostname';
		$config															=	fopen($config_file, "r") or redirect($redirect);
		while(!feof($config) AND $found_flag == 'N') 
		{
			$line														=	fgets($config);
			if (strpos($line, $search) !== false)
			{
				$found_flag												=	'Y';
			}
		}
		fclose($config);
		if ($found_flag == 'N')
		{
			$line='localhost';
		}
		$array															=	array();
		$array															=	explode('=', $line);
		$found															=	trim($array[1]);
		$found															=	str_replace('\'', '', $found);
		$server															=	str_replace(';', '', $found);

		// find and set user name
		$config_file													=	"/var/www/html/wrightetmathon/application/config/database.php";
		$found_flag														=	'N';
		$search															=	'$db[\'default\'][\'username\']';
		$config															=	fopen($config_file, "r") or redirect($redirect);
		while(!feof($config) AND $found_flag == 'N') 
		{
			$line														=	fgets($config);
			if (strpos($line, $search) !== false)
			{
				$found_flag												=	'Y';
			}
		}
		fclose($config);
		if ($found_flag == 'N')
		{
			redirect($redirect);
		}
		$array															=	array();
		$array															=	explode('=', $line);
		$found															=	trim($array[1]);
		$found															=	str_replace('\'', '', $found);
		$user															=	str_replace(';', '', $found);
		
		// find and set password
		$found_flag														=	'N';
		$search															=	'$db[\'default\'][\'password\']';
		$config															=	fopen($config_file, "r") or redirect($redirect);
		while(!feof($config) AND $found_flag == 'N') 
		{
			$line														=	fgets($config);
			if (strpos($line, $search) !== false) 
			{
				$found_flag												=	'Y';
			}
		}
		fclose($config);
		if ($found_flag == 'N')
		{
			redirect($redirect);
		}
		$array															=	array();
		$array															=	explode('=', $line);
		$found															=	trim($array[1]);
		$found															=	str_replace('\'', '', $found);
		$password														=	str_replace(';', '', $found);
		
		// find and set database
		$found_flag														=	'N';
		$search															=	'database';
		$config															=	fopen("/var/www/html/wrightetmathon.ini", "r") or redirect($redirect);
		while(!feof($config) AND $found_flag == 'N') 
		{
			$line														=	fgets($config);
			if (strpos($line, $search) !== false) 
			{
				$found_flag												=	'Y';
			}
		}
		fclose($config);
		if ($found_flag == 'N')
		{
			redirect($redirect);
		}
		$array															=	array();
		$array															=	explode('=', $line);
		$found															=	trim($array[1]);
		$found															=	str_replace('\'', '', $found);
		$database														=	str_replace(';', '', $found);
		
		// load return array
		$conn_parms														=	array	(
																					'server'	=>	$server,
																					'user'		=>	$user,
																					'password'	=>	$password,
																					'database'	=>	$database
																					);
		return $conn_parms;
	}
	
	function	open_db($conn_parms)
	{
		// Create connection to DB
		$conn 															=	new mysqli($conn_parms['server'], $conn_parms['user'], $conn_parms['password'], $conn_parms['database']);
		
		// return connection info
		return $conn;
	}
	
	function software_folder_name()
	{
		unset($_SESSION['software_folder_name']);
		$software_folder_found_flag										=	'N';
		$inifile 														=	fopen("/var/www/html/wrightetmathon.ini", "r") or die("Unable to open wrightetmathon.ini file! You have a major problem. Contact your system administrator.");
		while(!feof($inifile) AND $software_folder_found_flag == 'N') 
		{
			$line														=	fgets($inifile);
			if (strpos($line, 'software_folder') !== false) 
			{
				$software_folder_array									=	array();
				$software_folder_array									=	explode('=', $line);
				$software_folder_found_flag								=	'Y';
			}
		}

		fclose($inifile);

		// if not found, assume CURRENT_VERSION
		if ($software_folder_found_flag == 'N')
		{
			$_SESSION['software_folder_name']							=	'CURRENT_VERSION';
		}
		// otherwise load software folder name
		else
		{
			// strip white space
			$_SESSION['software_folder_name']							=	trim($software_folder_array[1]);
			// strip double quotes
			$_SESSION['software_folder_name']							=	trim($_SESSION['software_folder_name'], "'");
		}
		
		// return
		return;													
	}

	function branchtype()
	{
		unset($_SESSION['branchtype']);
		$branchtype_found_flag										=	'N';
		$inifile 														=	fopen("/var/www/html/wrightetmathon.ini", "r") or die("Unable to open wrightetmathon.ini file! You have a major problem. Contact your system administrator.");
		while(!feof($inifile) AND $branchtype_found_flag == 'N') 
		{
			$line														=	fgets($inifile);
			if (strpos($line, 'branchtype') !== false) 
			{
				$branchtype_array									=	array();
				$branchtype_array									=	explode('=', $line);
				$branchtype_found_flag								=	'Y';
			}
		}

		fclose($inifile);

		// if not found, assume I ie Integrated
		if ($branchtype_found_flag == 'N')
		{
			$_SESSION['branchtype_name']							=	'I';
		}
		// otherwise load branchtype
		else
		{
			// strip white space
			$_SESSION['branchtype']							=	trim($branchtype_array[1]);
			// strip double quotes
			$_SESSION['branchtype']							=	trim($_SESSION['branchtype'], "'");
		}
		
		// return
		return;													
	}
	
	//Vérification pour savoir si il faut afficher les images des produits dans la fichier produit de l'article
	function show_image()
	{
		unset($_SESSION['show_image']);
		$show_image										=	'N';
		$inifile 														=	fopen("/var/www/html/wrightetmathon.ini", "r") or die("Unable to open wrightetmathon.ini file! You have a major problem. Contact your system administrator.");
		while(!feof($inifile) AND $show_image == 'N') 
		{
			$line														=	fgets($inifile);
			if (strpos($line, 'show_image') !== false) 
			{
				$show_image									=	array();
				$show_image									=	explode('=', $line);
				$show_image_flag								=	'Y';
			}
		}

		fclose($inifile);

		// if not found, assume I ie Integrated
		if ($show_image == 'N')
		{
			$_SESSION['show_image']							=	'';
		}
		// otherwise load branchtype
		else
		{
			// strip white space
			$_SESSION['show_image']							=	trim($show_image[1]);
			// strip double quotes
			$_SESSION['show_image']							=	trim($_SESSION['show_image'], "'");
		}
		
		// return
		return;													
	}
	
	//Obtention de l'adresse ip et du port du distributeur
	function ip_distributeur()
	{
		unset($_SESSION['ip_distributeur']);
		$ip_distributeur_flag										=	'N';
		$inifile 														=	fopen("/var/www/html/wrightetmathon.ini", "r") or die("Unable to open wrightetmathon.ini file! You have a major problem. Contact your system administrator.");
		while(!feof($inifile) AND $ip_distributeur_flag == 'N') 
		{
			$line														=	fgets($inifile);
			if (strpos($line, 'ip_distributeur') !== false) 
			{
				unset($ip_distributeur);
				$ip_distributeur									=	array();
				$ip_distributeur									=	explode('=', $line);
				$ip_distributeur_flag								=	'Y';
			}
		}

		fclose($inifile);

		// if not found, assume I ie Integrated
		if ($ip_distributeur_flag == 'N')
		{
			$_SESSION['ip_distributeur']							=	'';
		}
		// otherwise load branchtype
		else
		{
			// strip white space
			$_SESSION['ip_distributeur']							=	trim($ip_distributeur[1]);
			// strip double quotes
			$_SESSION['ip_distributeur']							=	trim($_SESSION['ip_distributeur'], "'");
		}
		
		// return
		return;													
	}

	function search_customers()
	{
		unset($_SESSION['search_customers']);
		$search_customers = 'N';
		$inifile = fopen("/var/www/html/wrightetmathon.ini", "r") or die("Unable to open wrightetmathon.ini file! You have a major problem. Contact your system administrator.");
		while(!feof($inifile) AND $search_customers == 'N') 
		{
			$line														=	fgets($inifile);
			if (strpos($line, 'search_customers') !== false) 
			{
				$search_customers		=	array();
				$search_customers		=	explode('=', $line);
				$search_customers_flag	=	'Y';
			}
		}

		fclose($inifile);

		// if not found, assume I ie Integrated
		if ($search_customers == 'N')
		{
			$_SESSION['search_customers'] = '';
		}
		// otherwise load branchtype
		else
		{
			// strip white space
			$_SESSION['search_customers'] = trim($search_customers[1]);
			// strip double quotes
			$_SESSION['search_customers'] = trim($_SESSION['search_customers'], "'");
		}
		
		// return
		return;		
	}
	function create_report_file()
	{
		unset($_SESSION['create_report_file']);
		$create_report_file_flag = 'N';
		$inifile = fopen("/var/www/html/wrightetmathon.ini", "r") or die("Unable to open wrightetmathon.ini file! You have a major problem. Contact your system administrator.");
		while(!feof($inifile) AND $create_report_file_flag == 'N') 
		{
			$line														=	fgets($inifile);
			if (strpos($line, 'create_report_file') !== false) 
			{
				$create_report_file		=	array();
				$create_report_file		=	explode('=', $line);
				$create_report_file_flag	=	'Y';
			}
		}

		fclose($inifile);

		// if not found, assume I ie Integrated
		if ($create_report_file_flag == 'N')
		{
			$_SESSION['create_report_file'] = 'N';
		}
		// otherwise load branchtype
		else
		{
			// strip white space
			$_SESSION['create_report_file'] = trim($create_report_file[1]);
			// strip double quotes
			$_SESSION['create_report_file'] = trim($_SESSION['create_report_file'], "'");
		}
		
		// return
		return;		
	}
	
}
