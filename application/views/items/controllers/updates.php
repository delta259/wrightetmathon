<?php
class Updates extends CI_Controller
{
	function index()
	{
		// get class
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		
		// show error message if not set elsewhere
		if (!isset($_SESSION['error_code']))
		{
			$_SESSION['error_code']										=	'05500';
		}
		
		// set view defaults
		$_SESSION['selected_update_sales_price']						=	'N';
		$_SESSION['selected_create']									=	'Y';
		
		// get data model
		$_SESSION['data_model']											=	$this->Import->get_all();
		
		// show view
		$this															->	load->view('updates/manage_items');
	}
	
	// This functions takes a CSV sheet as input and updates or creates the item record accordingly.
	// The essential data in the sheet is as follows,*
	// but see the import data model for currently defined model
	// column A 	= column 0		=	item_number
	// column C 	= column 2		=	item_name
	// column I		= column 8		=	supplier code 
	// column M 	= column 12		=	category, used to create the category and get its category_id
	// column U 	= column 20		=	purchase price HT
	// column W 	= column 22		=	sales price TTC
	// column AB 	= column 27		=	sales price HT
	// column BL 	= column 63		=	bar code
	// column BO 	= column 66		=	image file
	
	function manage_items_automatic()
	{			
		// get existing PHP ini values
		$memory_limit													=	ini_get('memory_limit');
		$max_execution_time												=	ini_get('max_execution_time');
		
		// set ini values to avoid out of memory
		ini_set('memory_limit', '2000M');
		ini_set('max_execution_time', '600');
		
		// initialise		
		if ($_SESSION['import_mode'] == 'manual')
		{
			$from_dir													=	$_SESSION['upload_path'];
			$input_file_name											=	$_SESSION['upload_file'];;
			$update_sales_price											=	$_SESSION['update_sales_price'];
			$create														=	$_SESSION['$create'];
			$redirect													=	'reports';
		}
		else
		{
			$from_dir													=	"/home/wrightetmathon/.hidrive.sonrisa/SONRISA_CENTRAL/ARTICLES/";
			$input_file_name											=	"BASE_ARTICLE.xls";
			$update_sales_price											=	'N';
			$create														=	'Y';
			$redirect													=	'return';
		}
		
		$infile															=	$from_dir.$input_file_name;		
		$now															=	date('Y-m-d H:i:s');		
		
		$number_of_records												=	0;
		$number_of_updates												=	0;
		$number_of_adds													=	0;
		$number_of_excludes												=	0;
		$number_of_errors												=	0;
		$number_of_unchanged											=	0;
		
		$search_for_uppercase											=	array('À', 'Â', 'Ä', 'È', 'É', 'Ê', 'Ë', 'Î', 'Ï', 'Ô', 'Œ', 'Ù', 'Û', 'Ü', 'Ÿ');
		$replace_by_uppercase											=	array('A', 'A', 'A', 'E', 'E', 'E', 'E', 'I', 'I', 'O', 'OE', 'U', 'U', 'U', 'Y');
		
		$search_for_lowercase											=	array('à', 'â', 'ä', 'è', 'é', 'ê', 'ë', 'î', 'ï', 'ô', 'œ', 'ù', 'û', 'ü', 'ÿ');
		$replace_by_lowercase											=	array('a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'oe', 'u', 'u', 'u', 'y');
		
		$search_for_specials											=	array('Ç', 'ç', '«', '»', '€', ',', '#', '$', '°', '§', '^', '`', '"', ';', ':');
		$replace_by_specials											=	array('C', 'c', '"', '"', '€', ' ', '#', '$', ' ', ' ', ' ', ' ', '"', ' ', ' ');
		
		$search_for_winker												=	array("'");
		$replace_by_winker												=	array(" ");
		
		$data_rows														=	array();

		require_once "/var/www/html/wrightetmathon/application/third_party/phpexcel/Classes/PHPExcel.php";
		require_once "/var/www/html/wrightetmathon/application/third_party/phpexcel/Classes/PHPExcel/IOFactory.php";

		$_SESSION['data_model']											=	$this->Import->get_all()->result();

		// get connection parameters and open connection
		$conn_parms														=	array();
		$conn_parms														=	$this->get_conn_parms($redirect);
		$conn															=	$this->open_db($conn_parms);
		if (!$conn)
		{
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);							//	connection failed, so exit. This is fatal.
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		// Setup report file
		$report_file													=	$this->report_file($conn, $now);
		$this															->	write_headers($report_file);
		
		// Output process started
		$message														=	'MAJ BASE ARTCLES COMMENCE => '.$now.' => '.$infile;
		$this															->	write_report($report_file, 'SUCCES', $message);
		
		// make sure input file exists, if not exit
		if (!file_exists($from_dir.$input_file_name)) 
		{
			$message													=	'BASE ARTICLES NON TROUVE => '.$from_dir.$input_file_name;
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);										// fatal error
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}

		// take a backup of the database
		$retval															=	$this->backup_db($conn_parms, $now);

		if ($retval != 0)
		{
			$message													=	'BACKUP NON EFFECTUE => '.$conn_parms['database'];
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
	
		// get default codes
		// get branch code
		$select_column													=	'value';
		$sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'branch_code'";
		$branch_code													=	$this->get($sql, $conn, $select_column);
		if ($branch_code == NULL)										//	no default admin, so exit
		{
			$message													=	'CODE SURCURRASALE NON TROUVE => ';
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		else
		{
			$message													=	'CODE SURCURRASALE => '.$branch_code;
			$this														->	write_report($report_file, 'SUCCES', $message);
		}
		
		// get default supplier code
		$select_column													=	'value';
		$sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'default_supplier_id'";
		$default_supplier_id											=	$this->get($sql, $conn, $select_column);
		if ($default_supplier_id == NULL)								//	no default supplier, so exit
		{
			$message													=	'CODE FOURNISSEUR PAR DEFAUT NON TROUVE => ';
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		else
		{
			$message													=	'CODE FOURNISSEUR PAR DEFAUT => '.$default_supplier_id;
			$this														->	write_report($report_file, 'SUCCES', $message);
		}
		
		// get default pricelist code
		$select_column													=	'value';
		$sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'pricelist_id'";
		$default_pricelist_id											=	$this->get($sql, $conn, $select_column);
		if ($default_pricelist_id == NULL)								//	no default pricelist, so exit
		{
			$message													=	'LISTE DE PRIX PAR DEFAUT NON TROUVE => ';
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		else
		{
			$message													=	'LISTE DE PRIX PAR DEFAUT => '.$default_pricelist_id;
			$this														->	write_report($report_file, 'SUCCES', $message);
		}
		
		// get default tax name
		$select_column													=	'value';
		$sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'default_tax_1_name'";
		$default_tax_name												=	$this->get($sql, $conn, $select_column);
		if ($default_tax_name == NULL)									//	no default tax name, so exit
		{
			$message													=	'NOM TVA NON TROUVE => ';
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		else
		{
			$message													=	'NOM TVA PAR DEFAUT => '.$default_tax_name;
			$this														->	write_report($report_file, 'SUCCES', $message);
		}
		
		// get default tax rate
		$select_column													=	'value';
		$sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'default_tax_1_rate'";
		$default_tax_rate												=	$this->get($sql, $conn, $select_column);
		if ($default_tax_rate == NULL)									//	no default tax rate, so exit
		{
			$message													=	'TAUX TVA NON TROUVE => ';
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		else
		{
			$message													=	'TAUX TVA PAR DEFAUT => '.$default_tax_rate;
			$this														->	write_report($report_file, 'SUCCES', $message);
		}
		
		// get admin name
		$select_column													=	'value';
		$sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'admin_user_name'";
		$admin_user_name												=	$this->get($sql, $conn, $select_column);
		if ($admin_user_name == NULL)									//	no default admin, so exit
		{
			$message													=	'UITISATUER ADMIN NON TROUVE => ';
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		else
		{
			$message													=	'UTILISATEUR ADMIN PAR DEFAUT => '.$admin_user_name;
			$this														->	write_report($report_file, 'SUCCES', $message);
		}
		
		// now get admin employee number
		$select_column													=	'person_id';
		$sql 															=	"SELECT $select_column FROM `ospos_employees` WHERE `username` = '".$admin_user_name."' AND `branch_code` = '".$branch_code."'";
		$admin_employee_id												=	$this->get($sql, $conn, $select_column);
		if ($admin_employee_id == NULL)									//	no admin employee id, so exit
		{
			$message													=	'ID EMPLOYE POUR UTILISATUER ADMIN NON TROUVE => ';
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		else
		{
			$message													=	'ID EMPLOYE UTILISATEUR ADMIN PAR DEFAUT => '.$admin_employee_id;
			$this														->	write_report($report_file, 'SUCCES', $message);
		}
		
		// get last modified date 
		$select_column													=	'value';
		$sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'import_items_last_modified_date'";
		$last_modified_date												=	$this->get($sql, $conn, $select_column);
		if ($last_modified_date == NULL)								//	no last modified date
		{
			$last_modified_date											=	0;
			$message													=	'DERNIERE MODIFICATION DATE BASE_ARTICLE NON TROUVEE, ASSUMANT 0  => ';
			$this														->	write_report($report_file, 'ERREUR', $message);
			$sql														=	"INSERT INTO `ospos_app_config`"
																		.	"(`key`,"
																		.	"`value`)"
																		.	"VALUES"						
																		.	"('import_items_last_modified_date',"
																		.	"0"
																		.	")";
			if ($conn->query($sql) === FALSE) 
			{
				$message												=	'ERREUR INSERTION DERNIERE MODIFICATION DATE => OSPOS_APP_CONFIG';
				$this													->	write_report($report_file, 'ERREUR', $message, $conn->error);
				$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);								// fatal error
				if ($redirect == 'return')
				{
					return;
				}
				else
				{
					redirect($redirect);
				}
			}															
		}
		else
		{
			$message													=	'DERNIERE MODIFICATION DATE BASE_ARTICLE => '.$last_modified_date;
			$this														->	write_report($report_file, 'SUCCES', $message);
		}

		// get input data
		// determine the filetype
		$filetype 														=	PHPExcel_IOFactory::identify($infile);
		$message														=	'TYPE DE FICHIER => '.$filetype;
		$this															->	write_report($report_file, 'SUCCES', $message);
	
		// create the reader
		$objReader														=	new stdClass();
		$objReader														=	PHPExcel_IOFactory::createReader($filetype);
		$objReader														->	setReadDataOnly(true);
		$message														=	'LECTURE OBJET CREE => '.$objReader;
		$this															->	write_report($report_file, 'SUCCES', $message);
	
		// Read the infile
		$objPHPExcel													=	new PHPExcel();
		$objPHPExcel													=	$objReader->load($infile);
		
		if (!$objPHPExcel)
		{
			$message													=	'PHP EXCEL OBJECT PAS CREE => '.$infile;
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		else
		{
			$message													=	'PHP EXCEL OBJECT CREE => '.$infile.' => DERNIERE MODIFICATION DATE = '.$objPHPExcel->getProperties()->getModified();
			$this														->	write_report($report_file, 'SUCCES', $message);
		}
		
		// no need to do anything if the file has not been changed
		if ($objPHPExcel->getProperties()->getModified() == $this->config->item('import_items_last_modified_date'))
		{
			$message													=	'RIEN A FAIRE - FICHIER BASE_ARTICLE.xls PAS CHANGE';
			$this														->	write_report($report_file, 'SUCCES', $message);

			$message													=	'MAJ BASE ARTCLES TERMINE => '.date('Y-m-d H:i:s');
			$this														->	write_report($report_file, 'SUCCES', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
				
		// get sheet
		try 
		{
			$sheet														=	$objPHPExcel->getSheet($objPHPExcel->getActiveSheetIndex());
		} 
		catch (Exception $e) 
		{
			$message													=	'FEUILLE NON LU=> '.$e->getMessage().' => Mise à jour interrompue';
			$this														->	write_report($report_file, 'ERREUR', $message);
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		
		if (!$sheet)
		{
			$message													=	'PHP EXCEL FEUILLE NON LU => '.$infile;
			$this														->	write_report($report_file, 'ERREUR', $message);
		}
		else
		{
			$message													=	'PHP EXCEL FEUILLE LU => '.$infile.' => Index feuille => '.$objPHPExcel->getActiveSheetIndex();
			$this														->	write_report($report_file, 'SUCCES', $message);
		}
		
		// get sheet dimensions
		$highestRow														=	$sheet->getHighestRow();
		$highestColumn													=	$sheet->getHighestColumn();
		$message														=	'DIMENSIONS EXCEL => '.$highestRow.' => LIGNES => '.$highestColumn.' COLONNES';
		$this															->	write_report($report_file, 'SUCCES', $message);
		
		// get column indexes from data model
		$col_indexes													=	array();
		foreach	($_SESSION['data_model'] as $column)
		{	
			$col_indexes[$column->column_database_field_name]			=	$this->get_column_index($column->column_database_field_name);
		}
		
		//  Loop through each row of the worksheet in turn starting with line 2 (to ignore titles)
		for ($row = 2; $row <= $highestRow; $row++) 
		{
			//  Read a row of data into an array
			$data_row													=	array();
			$data_row 													=	$sheet->rangeToArray('A'.$row.':'.$highestColumn.$row, NULL, TRUE, FALSE, FALSE);

			// initialise
			$item_data													=	array();
			$item_supp													=	array();
			$item_pric													=	array();
						
			// count records read
			$number_of_records											=	$number_of_records + 1;
			
			// scrub and clean data
			foreach	($_SESSION['data_model'] as $column)
			{
				if ($column->column_data_type ==	'N')
				{
					if (!is_numeric($data_row[0][$column->column_number]))
					{
						$message										=	'DONNEES NON NUMERIQUE => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$column->column_number].' => '.$column->column_letter.$row;
						$this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
						$data_row[0][$column->column_number]			=	0;
					}
				}
				else
				{
					$data_row[0][$column->column_number]				=	str_replace($search_for_uppercase, $replace_by_uppercase, $data_row[0][$column->column_number]);
					$data_row[0][$column->column_number]				=	str_replace($search_for_lowercase, $replace_by_lowercase, $data_row[0][$column->column_number]);
					$data_row[0][$column->column_number]				=	str_replace($search_for_specials, $replace_by_specials, $data_row[0][$column->column_number]);
					$data_row[0][$column->column_number]				=	str_replace($search_for_winker, $replace_by_winker, $data_row[0][$column->column_number]);
				}
			}
						
			// test for category and if not exists create it
			$select_column												=	'category_name';
			$sql 														=	"SELECT $select_column FROM `ospos_categories` WHERE `category_name` = '".$data_row[0][$col_indexes['category']]."' AND `branch_code` = '".$branch_code."'";
			$category_name												=	$this->get($sql, $conn, $select_column);
			
			// doesn't exist, so create it
			if ($category_name == NULL)
			{						
				// create sql
				$sql													=	"INSERT INTO `ospos_categories`"
																		.	"(`category_id`,"
																		.	"`category_name`,"
																		.	"`category_desc`,"
																		.	"`category_update_sales_price`,"
																		.	"`category_defect_indicator`,"
																		.	"`category_offer_indicator`,"
																		.	"`category_pack_size`,"
																		.	"`category_min_order_qty`,"
																		.	"`category_sales_qty`,"
																		.	"`category_sales_value`,"
																		.	"`deleted`,"
																		.	"`branch_code`)" 
																		.	"VALUES"						
																		.	"(NULL,"				// category_id
																		.	"'".$data_row[0][$col_indexes['category']]."',"	// category_name
																		.	"'".$data_row[0][$col_indexes['category']]."',"	// category_desc
																		.	"'Y',"					// category_update_sales_price
																		.	"'N',"					// category_defect_indicator
																		.	"'N',"					// category_offer_indicator
																		.	"1,"					// category_pack_size
																		.	"1,"					// category_min_order_qty
																		.	"0,"					// category_sales_qty
																		.	"0,"					// category_sales_value
																		.	"0,"					// deleted
																		.	"'".$branch_code."'"	// branch_code
																		.	")";
				// insert the record
				if ($conn->query($sql) === FALSE) 
				{
					$message											=	'ERREUR INSERTION OSPOS_CATEGORIES => '.$data_row[0][$col_indexes['category']].' => '.$data_row[0][$col_indexes['item_number']];
					$this												->	write_report($report_file, 'ERREUR', $message, $conn->error);
					$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);							// fatal error
					if ($redirect == 'return')
					{
						return;
					}
					else
					{
						redirect($redirect);
					}
				}				
				
				// write message to output file
				$message												=	'SUCCESS INSERTION OSPOS_CATEGORIES => '.$data_row[0][$col_indexes['category']].' => '.$data_row[0][$col_indexes['item_number']];
				$this													->	write_report($report_file, 'SUCCES', $message);
			}

			// get category id record for this item
			$select_column												=	'category_id';
			$sql 														=	"SELECT $select_column FROM `ospos_categories` WHERE `category_name` = '".$data_row[0][$col_indexes['category']]."' AND `branch_code` = '".$branch_code."'";
			$category_id												=	$this->get($sql, $conn, $select_column);
			if ($category_id == NULL)									//	no category record, so exit
			{
				$message												=	'ERREUR ID CATEGORIE NON TROUVE OSPOS_CATEGORIES => '.$data_row[0][$col_indexes['category']].' => '.$data_row[0][$col_indexes['item_number']];
				$this													->	write_report($report_file, 'ERREUR', $message, $conn->error);
				$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);								// fatal error
				if ($redirect == 'return')
				{
					return;
				}
				else
				{
					redirect($redirect);
				}
			}
	
			// load defaults based on in coming info
			// load DLUO indicator
			// if coloum TYPE_PRO, G, index 6,  = L its a liquid, so use DLUO
			// get column_index
			if ($data_row[0][6] == 'L')
			{
				if ($this->config->item('use_DLUO'))
				{
					$dluo												=	$this->config->item('use_DLUO');
				}
				else
				{
					$dluo												=	'Y';
				}
			}
			else
			{
				$dluo													=	'N';
			}	
				
			// see if aticle is desactivated
			if ($data_row[0][$col_indexes['deleted']] == 1)
			{	
				$reorder_policy											=	'N';
				$stock_rotate											=	1;
				$deleted												=	1;
			}
			else
			{
				$reorder_policy											=	'Y';
				$stock_rotate											=	0;
				$deleted												=	0;
			}
			
			// format nicotine					
			if (!is_numeric($data_row[0][$col_indexes['nicotine']]))
			{
				$nicotine												=	0;
			}
			else
			{
				$nicotine												=	$data_row[0][$col_indexes['nicotine']];
			}
			
			// format volume						
			if (!is_numeric($data_row[0][$col_indexes['volume']]))
			{
				$volume													=	0;
			}
			else
			{
				$volume													=	$data_row[0][$col_indexes['volume']];
			}
					
			// get current item id by item nummber	
			$sql 														=	"SELECT * FROM `ospos_items` WHERE `item_number` = '".$data_row[0][$col_indexes['item_number']]."' AND `branch_code` = '".$branch_code."'";
			$result 													=	$conn->query($sql);		

			// was item found?
			if ($result->num_rows > 0)								
			{
				// item record was found so load current item data
				$item_data 												=	$result->fetch_assoc();
				
				// now get current item/supplier record 
				$sql 													=	"SELECT * FROM `ospos_items_suppliers` WHERE `item_id` = '".$item_data['item_id']."' AND `supplier_id` = '".$default_supplier_id."' AND `branch_code` = '".$branch_code."'";						
				$result 												=	$conn->query($sql);
				
				// was an item/supplier record found?
				if ($result->num_rows == 0)								
				{
					// no record found add one
					$sql												=	"INSERT INTO `ospos_items_suppliers`"
																		.	"(`item_id`,"
																		.	"`supplier_id`,"
																		.	"`supplier_preferred`,"
																		.	"`supplier_item_number`,"
																		.	"`supplier_cost_price`,"
																		.	"`supplier_reorder_policy`,"
																		.	"`supplier_reorder_pack_size`,"
																		.	"`supplier_min_order_qty`,"
																		.	"`supplier_min_stock_qty`,"
																		.	"`supplier_bar_code`,"
																		.	"`supplier_reorder_level`,"
																		.	"`supplier_reorder_quantity`,"
																		.	"`deleted`,"
																		.	"`branch_code`)" 
																		.	"VALUES"						
																		.	"(".$item_data['item_id'].","		// item_id
																		.	$default_supplier_id.","			// supplier_id
																		.	"'Y',"								// supplier_preferred
																		.	"'',"								// supplier_item_number
																		.	$data_row[0][$col_indexes['supplier_cost_price']].","				// supplier_cost_price
																		.	"'".$reorder_policy."',"			// supplier_reorder_policy
																		.	"1,"								// supplier_reorder_pack_size
																		.	"1,"								// supplier_min_order_qty
																		.	"1,"								// supplier_min_stock_qty
																		.	"'".$data_row[0][$col_indexes['supplier_bar_code']]."',"			// supplier_bar_code
																		.	"0,"								// supplier_reorder_level
																		.	"0,"								// supplier_reorder_quantity
																		.	"0,"								// deleted
																		.	"'".$branch_code."'"				// branch_code
																		.	")";
					
					// insert the record
					if ($conn->query($sql) === FALSE) 
					{
						$message										=	'ERREUR INSERTION OSPOS_ITEMS_SUPPLIERS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
						$this											->	write_report($report_file, 'ERREUR', $message, $conn->error);		
						$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);						// fatal error
						if ($redirect == 'return')
						{
							return;
						}
						else
						{
							redirect($redirect);
						}
					}				
				
					// write message to output file
					$message											=	'SUCCESS INSERTION OSPOS_ITEMS_SUPPLIERS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
					$this												->	write_report($report_file, 'SUCCES', $message);
					
					// now get current item/supplier record again
					$sql 												=	"SELECT * FROM `ospos_items_suppliers` WHERE `item_id` = '".$item_data['item_id']."' AND `supplier_id` = '".$default_supplier_id."' AND `branch_code` = '".$branch_code."'";						
					$result 											=	$conn->query($sql);
				}
					
				// now load item/supplier data
				$item_supp 												=	$result->fetch_assoc();
				
				// now get current item/pricelist record 
				$sql 													=	"SELECT * FROM `ospos_items_pricelists` WHERE `item_id` = '".$item_data['item_id']."' AND `pricelist_id` = '".$default_pricelist_id."' AND `branch_code` = '".$branch_code."'";						
				$result 												=	$conn->query($sql);
				
				// was an item/supplier record found?
				if ($result->num_rows == 0)								
				{
					// no record found add one
					$sql												=	"INSERT INTO `ospos_items_pricelists`"
																		.	"(`item_id`,"
																		.	"`pricelist_id`,"
																		.	"`unit_price`,"
																		.	"`unit_price_with_tax`,"
																		.	"`valid_from_year`,"
																		.	"`valid_from_month`,"
																		.	"`valid_from_day`,"
																		.	"`valid_to_year`,"
																		.	"`valid_to_month`,"
																		.	"`valid_to_day`,"
																		.	"`deleted`,"
																		.	"`branch_code`)" 
																		.	"VALUES"						
																		.	"(".$item_data['item_id'].","		// item_id
																		.	$default_pricelist_id.","			// pricelist_id
																		.	$data_row[0][$col_indexes['unit_price']].","					// unit_price
																		.	$data_row[0][$col_indexes['unit_price_with_tax']].","					// unit_price_with_tax
																		.	"0,"								// valid_from_year
																		.	"0,"								// valid_from_month
																		.	"0,"								// valid_from_day
																		.	"0,"								// valid_to_year
																		.	"0,"								// valid_to_month
																		.	"0,"								// valid_to_day
																		.	"0,"								// deleted
																		.	"'".$branch_code."'"				// branch_code
																		.	")";
					
					// insert the record
					if ($conn->query($sql) === FALSE) 
					{
						$message										=	'ERREUR INSERTION OSPOS_ITEMS_PRICELISTS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
						$this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
						$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);						// fatal error
						if ($redirect == 'return')
						{
							return;
						}
						else
						{
							redirect($redirect);
						}
					}				
				
					// write message to output file
					$message											=	'SUCCESS INSERTION OSPOS_ITEMS_PRICELISTS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
					$this												->	write_report($report_file, 'SUCCES', $message);
					
					// now get current item/pricelist record again
					$sql 												=	"SELECT * FROM `ospos_items_suppliers` WHERE `item_id` = '".$item_data['item_id']."' AND `supplier_id` = '".$default_supplier_id."' AND `branch_code` = '".$branch_code."'";						
					$result 											=	$conn->query($sql);
				}
				
				// now load item/pricelist data
				$item_pric 												=	$result->fetch_assoc();
				
				// now I have all the data I need to continue
				// test if anything has changed
				if 	(	$item_data['name'] 								!=	$data_row[0][$col_indexes['name']]
					OR	$item_data['category_id'] 						!=	$category_id
					OR	$item_data['category'] 							!=	$data_row[0][$col_indexes['category']]
					OR	$item_data['barcode'] 							!=	$data_row[0][$col_indexes['supplier_bar_code']]
					OR	$item_data['image_file_name'] 					!=	$data_row[0][$col_indexes['image_file_name']]
					OR	$item_data['dluo_indicator']					!=	$dluo
					OR	$item_data['deleted']							!=	$deleted
					OR	$item_data['volume']							!=	$volume
					OR	$item_data['nicotine']							!=	$nicotine
					OR	$item_supp['supplier_cost_price']				!=	number_format($data_row[0][$col_indexes['supplier_cost_price']], 3)
					OR	($update_sales_price == 'Y' AND	$item_pric['unit_price'] 			!=	number_format($data_row[0][$col_indexes['unit_price']], 3))
					OR	($update_sales_price == 'Y' AND	$item_pric['unit_price_with_tax'] 	!=	number_format($data_row[0][$col_indexes['unit_price_with_tax']], 3))
					)
				{						
					// something changed so set up the data for update
					// if incoming barcode is blank use existing
					if ($data_row[0][$col_indexes['supplier_bar_code']] == '')
					{
						$data_row[0][$col_indexes['supplier_bar_code']]	=	$item_data['barcode'];
					}
					
					// if incoming image file is blank use existing
					if ($data_row[0][$col_indexes['image_file_name']] == '')
					{
						$data_row[0][$col_indexes['image_file_name']]	=	$item_data['image_file_name'];
					}
					
					// create update sql
					$sql 												=	"UPDATE `ospos_items` SET "
																		.	"`name`='".$data_row[0][$col_indexes['name']]."',"
																		.	"`category_id`=".$category_id.","
																		.	"`nicotine`=".$nicotine.","
																		.	"`volume`=".$volume.","
																		.	"`deleted`=".$deleted.","
																		.	"`category`='".$data_row[0][$col_indexes['category']]."',"
																		.	"`image_file_name`='".$data_row[0][$col_indexes['image_file_name']]."',"
																		.	"`dluo_indicator`='".$dluo."'"
																		.	" WHERE "
																		.	"`item_id`=".$item_data['item_id']
																		.	" AND "
																		.	"`branch_code`='".$branch_code."'";
		
					// do update and test result
					if ($conn->query($sql) === FALSE)
					{
						// if fail - output error message to report
						$message										=	'ERREUR MAJ OSPOS_ITEMS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
						$this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
						$number_of_errors								=	$number_of_errors + 1;
					}
					else
					{							
						// update was successful
						$number_of_updates								=	$number_of_updates + 1;
						
						// output to file
						$message										=	'SUCCESS MAJ OSPOS_ITEMS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
						$this											->	write_report($report_file, 'SUCCES', $message);
					
						// add inventory record							
						// set correct field values for inventory record file
						$trans_comment									=	'Article MAJ par CENTRAL';
					
						// create sql
						$sql											=	"INSERT INTO `ospos_inventory`"
																		.	"(`trans_id`,"
																		.	"`trans_items`,"
																		.	"`trans_user`,"
																		.	"`trans_date`,"
																		.	"`trans_comment`,"
																		.	"`trans_stock_before`,"
																		.	"`trans_inventory`,"
																		.	"`trans_stock_after`,"
																		.	"`branch_code`)" 
																		.	"VALUES"						
																		.	"(NULL,"						// trans_id
																		.	$item_data['item_id'].","		// trans_items
																		.	$admin_employee_id.","			// trans_user
																		.	"'".$now."',"					// trans_date
																		.	"'".$trans_comment."',"			// trans_comment
																		.	$item_data['quantity'].","		// trans_stock_before
																		.	"0".","							// trans_inventory
																		.	$item_data['quantity'].","		// trans_stock_after
																		.	"'".$branch_code."'"			// branch_code
																		.	")";
					
						// insert inventory record
						if ($conn->query($sql) === FALSE)
						{
							// if fail - output error message to report
							$message									=	'ERREUR INSERT OSPOS_INVENTORY => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
							$this										->	write_report($report_file, 'ERREUR', $message, $conn->error);
						}

						// update supplier record	
						$sql 											=	"UPDATE `ospos_items_suppliers` SET "
																		.	"`supplier_cost_price`=".$data_row[0][$col_indexes['supplier_cost_price']].","
																		.	"`supplier_reorder_policy`='".$reorder_policy."',"
																		.	"`supplier_bar_code`='".$data_row[0][$col_indexes['supplier_bar_code']]."'"
																		.	" WHERE "
																		.	"`item_id`=".$item_data['item_id']
																		.	" AND "
																		.	"`supplier_id`=".$default_supplier_id
																		.	" AND "
																		.	"`branch_code`='".$branch_code."'";
		
						// do update and test result
						if ($conn->query($sql) === FALSE)
						{
							// if fail - output error message to report
							$message									=	'ERREUR MAJ OSPOS_ITEMS_SUPPLIERS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
							$this										->	write_report($report_file, 'ERREUR', $message, $conn->error);
						}

						// update price list record	but only if allowed
						if ($update_sales_price == 'Y')
						{
							$sql 										=	"UPDATE `ospos_items_pricelists` SET "
																		.	"`unit_price`=".$data_row[0][$col_indexes['unit_price']].","
																		.	"`unit_price_with_tax`=".$data_row[0][$col_indexes['unit_price_with_tax']]
																		.	" WHERE "
																		.	"`item_id`=".$item_data['item_id']
																		.	" AND "
																		.	"`pricelist_id`=".$default_pricelist_id
																		.	" AND "
																		.	"`branch_code`='".$branch_code."'";
		
							// do update and test result
							if ($conn->query($sql) === FALSE)
							{
								// if fail - output error message to report
								$message								=	'ERREUR MAJ OSPOS_ITEMS_PRICELISTS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
								$this									->	write_report($report_file, 'ERREUR', $message, $conn->error);
							}
						}
					}


                    // create update sql2
                    $sql2 												=	"UPDATE `ospos_items` SET	`deleted`=0 WHERE `deleted`= 1 AND `quantity`>0";

                    // do update and test result
                    if ($conn->query($sql2) === FALSE)
                    {
                        // if fail - output error message to report
                        $message										=	'ERREUR REACTIVATION d\'articles au stock positif  '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
                        $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
                        $number_of_errors								=	$number_of_errors + 1;

                    }
                    else {
                        // output to file
                        $message										=	'SUCCESS REACTIVATION d\'articles au stock positif => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
                        $this											->	write_report($report_file, 'SUCCES', $message);

                    }
                }
				else
				{
					$number_of_unchanged								=	$number_of_unchanged	+	1;
				}
			}
			else
			{						
				// item not found, so add it						
				// set up sql
				$sql													=	"INSERT INTO `ospos_items`"
																		.	"(`name`,"
																		.	"`category_id`,"
																		.	"`category`,"
																		.	"`supplier_id`,"
																		.	"`supplier_item_number`,"
																		.	"`item_number`,"
																		.	"`description`,"
																		.	"`volume`,"
																		.	"`nicotine`,"
																		.	"`cost_price`,"
																		.	"`unit_price`,"
																		.	"`quantity`,"
																		.	"`reorder_policy`,"
																		.	"`reorder_level`,"
																		.	"`reorder_quantity`,"
																		.	"`reorder_pack_size`,"
																		.	"`min_stock_qty`,"
																		.	"`location`,"
																		.	"`item_id`,"
																		.	"`allow_alt_description`,"
																		.	"`is_serialized`,"
																		.	"`DynamicKit`,"
																		.	"`deleted`,"
																		.	"`sales_ht`,"
																		.	"`sales_qty`,"
																		.	"`custom1`,"
																		.	"`custom2`,"
																		.	"`custom3`,"
																		.	"`custom4`,"
																		.	"`custom5`,"
																		.	"`custom6`,"
																		.	"`custom7`,"
																		.	"`custom8`,"
																		.	"`custom9`,"
																		.	"`custom10`,"
																		.	"`rolling_inventory_indicator`,"
																		.	"`dluo_indicator`,"
																		.	"`giftcard_indicator`,"
																		.	"`offer_indicator`,"
																		.	"`kit_reference`,"
																		.	"`barcode`,"
																		.	"`export_to_franchise`,"
																		.	"`export_to_integrated`,"
																		.	"`export_to_other`,"
																		.	"`image_file_name`,"
																		.	"`branch_code`)" 
																		.	"VALUES ("						
																		.	"'".$data_row[0][$col_indexes['name']]."',"		// name
																		.	$category_id.","				// category_id
																		.	"'".$data_row[0][$col_indexes['category']]."',"		// category
																		.	"NULL".","						// supplier_id
																		.	"NULL".","						// supplier_item_number
																		.	"'".$data_row[0][$col_indexes['item_number']]."',"		// item_number
																		.	"'".$data_row[0][$col_indexes['name']]."',"		// description
																		.	$volume.","						// volume
																		.	$nicotine.","					// nicotine
																		.	"0".","							// cost_price
																		.	"0".","							// unit_price
																		.	"0".","							// quantity
																		.	"'".$reorder_policy."',"		// reorder_policy
																		.	"1".","							// reorder_level
																		.	"1".","							// reorder_quantity
																		.	"1".","							// reoder_pack_size
																		.	"1".","							// min_stock_qty
																		.	"NULL".","						// location
																		.	"NULL".","						// item_id
																		.	"0".","							// allow_alt_description
																		.	"0".","							// is_serialized
																		.	"'N'".","						// DynamicKit
																		.	$deleted.","					// deleted
																		.	"0".","							// sales_ht
																		.	"0".","							// sales_qty
																		.	"NULL".","						// custom1
																		.	"NULL".","						// custom2
																		.	"NULL".","						// custom3
																		.	"NULL".","						// custom4
																		.	"NULL".","						// custom5
																		.	"NULL".","						// custom6
																		.	"NULL".","						// custom7
																		.	"NULL".","						// custom8
																		.	"NULL".","						// custom9
																		.	"NULL".","						// custom10
																		.	$stock_rotate.","				// rolling_inventory_indicator
																		.	"'".$dluo."',"					// dluo_indicator
																		.	"'N'".","						// giftcard_indicator
																		.	"'N'".","						// offer_indicator
																		.	"NULL".","						// kit_reference
																		.	"'',"							// barcode
																		.	"'N'".","						// export_to_franchise
																		.	"'N'".","						// export_to_integrated
																		.	"'N'".","						// export_to_other
																		.	"'".$data_row[0][$col_indexes['image_file_name']]."',"		// image_file_name
																		.	"'".$branch_code."'"			// branch_code
																		.	")";
				// insert item record
				if ($conn->query($sql) === FALSE)
				{
					// if fail - output error message to report
					$message											=	'ERREUR INSERT OSPOS_ITEMS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
					$this												->	write_report($report_file, 'ERREUR', $message, $conn->error);
				}
				
				// if success - output success message to report
				$message												=	'SUCCES INSERT OSPOS_ITEMS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
				$this													->	write_report($report_file, 'SUCCES', $message, $conn->error);
						
				// increment added total
				$number_of_adds											=	$number_of_adds	+	1;
				
				// get item id of newly inserted item
				$inserted_item_id										=	$conn->insert_id;
				
				// add inventory record							
				// set correct field values for inventory record file
				$trans_comment											=	'Article AJOUTE par CENTRAL';
			
				// create sql
				$sql													=	"INSERT INTO `ospos_inventory`"
																		.	"(`trans_id`,"
																		.	"`trans_items`,"
																		.	"`trans_user`,"
																		.	"`trans_date`,"
																		.	"`trans_comment`,"
																		.	"`trans_stock_before`,"
																		.	"`trans_inventory`,"
																		.	"`trans_stock_after`,"
																		.	"`branch_code`)" 
																		.	"VALUES"						
																		.	"(NULL,"						// trans_id
																		.	$inserted_item_id.","			// trans_items
																		.	$admin_employee_id.","			// trans_user
																		.	"'".$now."',"					// trans_date
																		.	"'".$trans_comment."',"			// trans_comment
																		.	"0".","							// trans_stock_before
																		.	"0".","							// trans_inventory
																		.	"0".","							// trans_stock_after
																		.	"'".$branch_code."'"			// branch_code
																		.	")";
																
				if ($conn->query($sql) === FALSE)
				{
					// if fail - output error message to report
					$message											=	'ERREUR INSERT OSPOS_INVENTORY => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
					$this												->	write_report($report_file, 'ERREUR', $message, $conn->error);
				}
				
				// insert item_taxes record
				$sql													=	"INSERT INTO `ospos_items_taxes`"
																		.	"(`item_id`,"
																		.	"`name`,"
																		.	"`percent`,"
																		.	"`branch_code`)" 
																		.	"VALUES ("						
																		.	$inserted_item_id.","			// item_id
																		.	"'".$default_tax_name."',"		// name
																		.	$default_tax_rate.","			// percent
																		.	"'".$branch_code."'"			// branch_code
																		.	")";
				
				if ($conn->query($sql) === FALSE)
				{
					// if fail - output error message to report
					$message											=	'ERREUR INSERT OSPOS_ITEMS_TAXES => '.$inserted_item_id.' => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
					$this												->	write_report($report_file, 'ERREUR', $message, $conn->error);
				}	

				// insert items_suppliers
				$sql													=	"INSERT INTO `ospos_items_suppliers`"
																		.	"(`item_id`,"
																		.	"`supplier_id`,"
																		.	"`supplier_preferred`,"
																		.	"`supplier_item_number`,"
																		.	"`supplier_cost_price`,"
																		.	"`supplier_reorder_policy`,"
																		.	"`supplier_reorder_pack_size`,"
																		.	"`supplier_min_order_qty`,"
																		.	"`supplier_min_stock_qty`,"
																		.	"`supplier_bar_code`,"
																		.	"`supplier_reorder_level`,"
																		.	"`supplier_reorder_quantity`,"
																		.	"`deleted`,"
																		.	"`branch_code`)" 
																		.	"VALUES"						
																		.	"(".$inserted_item_id.","			// item_id
																		.	$default_supplier_id.","			// supplier_id
																		.	"'Y',"								// supplier_preferred
																		.	"'',"								// supplier_item_number
																		.	$data_row[0][$col_indexes['supplier_cost_price']].","				// supplier_cost_price
																		.	"'".$reorder_policy."',"			// supplier_reorder_policy
																		.	"1,"								// supplier_reorder_pack_size
																		.	"1,"								// supplier_min_order_qty
																		.	"1,"								// supplier_min_stock_qty
																		.	"'".$data_row[0][$col_indexes['supplier_bar_code']]."',"			// supplier_bar_code
																		.	"0,"								// supplier_reorder_level
																		.	"0,"								// supplier_reorder_quantity
																		.	"0,"								// deleted
																		.	"'".$branch_code."'"				// branch_code
																		.	")";
					
				// insert the record
				if ($conn->query($sql) === FALSE) 
				{
					$message											=	'ERREUR INSERTION OSPOS_ITEMS_SUPPLIERS => '.$inserted_item_id.' => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
					$this												->	write_report($report_file, 'ERREUR', $message, $conn->error);
				}				

				// insert items_pricelists
					$sql												=	"INSERT INTO `ospos_items_pricelists`"
																		.	"(`item_id`,"
																		.	"`pricelist_id`,"
																		.	"`unit_price`,"
																		.	"`unit_price_with_tax`,"
																		.	"`valid_from_year`,"
																		.	"`valid_from_month`,"
																		.	"`valid_from_day`,"
																		.	"`valid_to_year`,"
																		.	"`valid_to_month`,"
																		.	"`valid_to_day`,"
																		.	"`deleted`,"
																		.	"`branch_code`)" 
																		.	"VALUES"						
																		.	"(".$inserted_item_id.","			// item_id
																		.	$default_pricelist_id.","			// pricelist_id
																		.	$data_row[0][$col_indexes['unit_price']].","				// unit_price
																		.	$data_row[0][$col_indexes['unit_price_with_tax']].","				// unit_price_with_tax
																		.	"0,"								// valid_from_year
																		.	"0,"								// valid_from_month
																		.	"0,"								// valid_from_day
																		.	"0,"								// valid_to_year
																		.	"0,"								// valid_to_month
																		.	"0,"								// valid_to_day
																		.	"0,"								// deleted
																		.	"'".$branch_code."'"				// branch_code
																		.	")";
						
				// insert the record
				if ($conn->query($sql) === FALSE) 
				{
					$message											=	'ERREUR INSERTION OSPOS_ITEMS_PRICELISTS => '.$inserted_item_id.' => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
					$this												->	write_report($report_file, 'ERREUR', $message, $conn->error);
				}
			}
		}

		// write final line to output file
		$message														=	'Enregistrements Lu => '.$number_of_records;
		$this															->	write_report($report_file, 'SUCCES', $message);

		$message														=	'Mise a Jour => '.$number_of_updates;
		$this															->	write_report($report_file, 'SUCCES', $message);
		
		$message														=	'Erreurs => '.$number_of_errors;
		$this															->	write_report($report_file, 'ERREUR', $message);
					
		$message														=	'Articles inchange => '.$number_of_unchanged;	
		$this															->	write_report($report_file, 'SUCCES', $message);
		
		$message														=	'Ajoute => '.$number_of_adds;
		$this															->	write_report($report_file, 'SUCCES', $message);
		
		$message														=	'Exclu => '.$number_of_excludes;
		$this															->	write_report($report_file, 'SUCCES', $message);
		
		// write change date to app_config
		$sql															=	"UPDATE `ospos_app_config` SET "
																		.	"`value`=".$objPHPExcel->getProperties()->getModified()
																		.	" WHERE "
																		.	"`key`="."'import_items_last_modified_date'";
		if ($conn->query($sql) === FALSE) 
		{
			$message													=	'ERREUR MAJ DERNIERE MODIFICATION DATE => OSPOS_APP_CONFIG';
			$this														->	write_report($report_file, 'ERREUR', $message, $conn->error);
		}											
		
		// return
		$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
		if ($redirect == 'return')
		{
			return;
		}
		else
		{
			redirect($redirect);
		}
	}
	
	function manage_items_manual()
	{		
		// initialise
		$dir															=	"/var/www/html/wrightetmathon_uploads/";
		$upload_file_name												=	"BASE_ARTICLES_MANUAL.xls";
		$update_sales_price												=	$this->input->post('update_sales_price');
		$create															=	$this->input->post('create');

		// test upload dir exists
		if (!file_exists($dir))
		{
			// make it if not found
			mkdir($dir);
		}
		
		// test if upload dir is not empty
		if ((new \FilesystemIterator($dir))->valid() == TRUE)
		{
			// if it is not empty, delete any files in it
			if (!array_map('unlink', glob($dir."*")))
			{
				$_SESSION['error_code']									=	'05490';
				redirect($_SESSION['controller_name']);
			}
		}

		// config upload library for first upload
		$config = array	(
						'upload_path' 	=>	$dir,
						'allowed_types' =>	'*',
						'overwrite' 	=>	TRUE
						);
		$this->upload->initialize($config);
		
		// test file selected
		if(!$this->upload->do_upload('userfile'))
		{
			// set message depends on what is in the error string
			if (strpos($this->upload->display_errors(), 'select'))
			{
				$_SESSION['error_code']									=	'05470';
				redirect($_SESSION['controller_name']);
			}
		}
		
		// check file extension
		$data['upload_data']											=	array();
		$data['upload_data']											=	$this->upload->data();
		if ($data['upload_data']['file_ext'] != '.xls')
		{
			$_SESSION['error_code']										=	'05480';
			redirect($_SESSION['controller_name']);
		}
		
		// clear upload path
		array_map('unlink', glob($dir."*"));
		
		// If here then I am reasonably sure I have a valid and authentic file so initialise the upload library to 
		// convert file name to standard
		$config = array	(
						'file_name'		=>	$upload_file_name,
						'upload_path' 	=>	$dir,
						'allowed_types' =>	'*',
						'overwrite' 	=>	TRUE
						);
		$this->upload->initialize($config);
		
		// now upload the file again
		if(!$this->upload->do_upload('userfile'))
		{
			// set message depends on what is in the error string
			if (strpos($this->upload->display_errors(), 'select'))
			{
				$_SESSION['error_code']									=	'05470';
				redirect($_SESSION['controller_name']);
			}
		}
		
		// set session parms
		$_SESSION['upload_path']										=	$dir;
		$_SESSION['upload_file']										=	$upload_file_name;
		$_SESSION['update_sales_price']									=	$update_sales_price;
		$_SESSION['$create']											=	$create;
		$_SESSION['import_mode']										=	'manual';

		// now do update
		$this															->	manage_items_automatic();
		return;
	}
	
	function get_conn_parms($redirect)
	{
		// initialise 
		$config_file													=	"/var/www/html/wrightetmathon.ini";
		
		// find and set server name
		$found_flag														=	'N';
		$search															=	'hostname';
		$config															=	fopen($config_file, "r");
		if (!$config)
		{
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}	
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
		$config															=	fopen($config_file, "r");
		if (!$config)
		{
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		
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
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		$array															=	array();
		$array															=	explode('=', $line);
		$found															=	trim($array[1]);
		$found															=	str_replace('\'', '', $found);
		$user															=	str_replace(';', '', $found);
		
		// find and set password
		$found_flag														=	'N';
		$search															=	'$db[\'default\'][\'password\']';
		$config															=	fopen($config_file, "r");
		if (!$config)
		{
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
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
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
		$array															=	array();
		$array															=	explode('=', $line);
		$found															=	trim($array[1]);
		$found															=	str_replace('\'', '', $found);
		$password														=	str_replace(';', '', $found);
		
		// find and set database
		$found_flag														=	'N';
		$search															=	'database';
		$config															=	fopen("/var/www/html/wrightetmathon.ini", "r");
		if (!$config)
		{
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
		}
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
			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
			if ($redirect == 'return')
			{
				return;
			}
			else
			{
				redirect($redirect);
			}
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
	
	function	backup_db($conn_parms, $now)
	{
		$retval															=	FALSE;
		$dbhost															=	$conn_parms['server'];
		$dbuser															=	$conn_parms['user'];
		$dbpass															=	$conn_parms['password'];
		$database														=	$conn_parms['database'];
		$backup_path													=	"/var/www/html/wrightetmathon_backup/";
		if (!file_exists($backup_path)) mkdir($backup_path);
		$backup_file													=	"backup_".$database."_".$now;
		$command = "mysqldump --host=$dbhost --user=$dbuser --password='$dbpass' -B $database | gzip > '$backup_path$backup_file'";
		system($command, $retval);
		return $retval;
	}
	
	function	get($sql, $conn, $select_column)
	{
		$return_value													=	NULL;
		$result															=	$conn->query($sql);
		if ($result->num_rows == 1)
		{
			while($row = mysqli_fetch_assoc($result)) 
			{
				$return_value											=	$row[$select_column];
			}
		}
		return	$return_value;
	}
	
	function	report_file($conn, $now)
	{
		$report_file													=	NULL;
		
		// now get report save file
		$select_column													=	'value';
		$sql 															=	"SELECT `value` FROM `ospos_app_config` WHERE `key` = 'SPfilename'";
		$SPfilename														=	$this->get($sql, $conn, $select_column);
		if ($SPfilename == NULL) $SPfilename="Item_import_report.csv";	//	no report save file, so set it
		
		// now get report save path
		$select_column													=	'value';
		$sql 															=	"SELECT `value` FROM `ospos_app_config` WHERE `key` = 'SPsavepath'";
		$SPsavepath														=	$this->get($sql, $conn, $select_column);
		if ($SPsavepath == NULL) $SPsavepath="/var/www/html/";			//	no report save path, so set_it
		
		// set output report CSV name
		$pieces															=	explode(".", $SPfilename);
		$report_file 													=	$SPsavepath.$pieces[0].'_update_'.$now.'.csv';

		return $report_file;
	}
	
	function	write_headers($report_file)
	{
		file_put_contents($report_file,									'Action',					FILE_APPEND);
		file_put_contents($report_file,									';',						FILE_APPEND);
		file_put_contents($report_file,									'Message',					FILE_APPEND);
		file_put_contents($report_file,									';',						FILE_APPEND);
		file_put_contents($report_file,									"\n",						FILE_APPEND);
	}
	
	function	write_report($report_file, $type, $message, $conn_error=NULL)
	{
		file_put_contents($report_file,									$type,						FILE_APPEND);
		file_put_contents($report_file,									';',						FILE_APPEND);
		file_put_contents($report_file,									$message,					FILE_APPEND);
		file_put_contents($report_file,									';',						FILE_APPEND);
		file_put_contents($report_file,									$conn_error,				FILE_APPEND);
		file_put_contents($report_file,									';',						FILE_APPEND);
		file_put_contents($report_file,									"\n",						FILE_APPEND);
	}
	
	function	get_column_index($search_field)
	{
		// now find field_index in import data model to get column index
		$data_model_key 												=	array_search($search_field, array_column($_SESSION['data_model'], 'column_database_field_name'));

		// now get column number from the right index for this field
		$col_index														=	$_SESSION['data_model'][$data_model_key]->column_number;
		
		return $col_index;
	}
	
	function	redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file)
	{
		// close the data connections
		$conn->close();
		unset($objPHPExcel);
		unset($objReader);
		unset($_SESSION['import_mode']);
		
		// Output process finished
		$message														=	'MAJ BASE ARTCLES TERMINE => '.date('Y-m-d H:i:s').' => '.$infile;
		$this															->	write_report($report_file, 'SUCCES', $message);
		
		// re-set values
		ini_set('memory_limit', $memory_limit);
		ini_set('max_execution_time', $max_execution_time);
		
		// set message
		$_SESSION['error_code']											=	'05570';
	}
	
	// update the pos software
	function	software_update()
	{
		// sync the system software
		require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_software_sync.php";
		
		// return to login.
		return;
	}
	
	// update the version
	function	software_version()
	{
		// initialise
		$version_found_flag												=	'N';
		$version_file													=	"/var/www/html/wrightetmathon/version.ini";
		
		// open the file
		$verfile 														=	fopen($version_file, "r");

		// if file opened successfully
		if ($verfile)
		{
			// read the file line by line and search for 'version' keyword
			while(!feof($verfile) AND $version_found_flag == 'N') 
			{
				$line														=	fgets($verfile);
				if (strpos($line, 'version') !== false) 
				{
					$version_array											=	array();
					$version_array											=	explode('=', $line);
					$version_found_flag										=	'Y';
				}
			}
			
			// close the file
			fclose($verfile);

			// if keyword version found
			if ($version_found_flag == 'Y')
			{
				// set up the application version
				$application_version									=	trim($version_array[1]);
				$application_version									=	trim($application_version, '"');
				
				// write it to app_config
				$this													->	Appconfig->save('application_version', $application_version);													
			}
		}
		
		// return to login.
		return;
	}
	
	// update the branches
	function	branches()
	{
		// initialise
		$from_dir														=	"/home/wrightetmathon/.hidrive.sonrisa/SONRISA_CENTRAL/BRANCHES/";
		$input_file_name												=	"branches.ini";
		$user															=	"admin";
		$password														=	"Son@Risa&11";
		
		// truncate the existing table
		// this ensures that any branches that are in the local table but not in branches.ini will be removed
		$this															->	Branch->truncate();
		
		// read the branches file
		// this file is in sections
		// each branch has its own section starting with [START] and ending in [END]
		// so read file until the first [START], load data to variables for this branch until [END] reached, then update/insert branch file.
		
		// open the file, return if not opened
		$branches 														=	fopen("$from_dir$input_file_name", "r");
		if (! $branches)
		{
			return;
		}
		
		// read the file
		while(!feof($branches)) 
		{
			// get a line
			$line														=	fgets($branches);	
			
			// test line for [START]
			if (strpos($line, '[START]') !== false) 
			{
				// now load the parameters until [END] is reached
				while (strpos($line, '[END]') === false)
				{
					// read a line
					$line												=	fgets($branches);
					
					// explode the line to get the parameter and its value; array will have two elements
					$array												=	array();
					$array												=	explode('=', $line);
					
					// trim array[1]
					$value												=	trim($array[1]);
					
					// find out what I have and save it
					switch ($array[0])
					{
						case "branch_code":
							$_SESSION['transaction_info']->branch_code			=	$value;
							break;
						case "branch_description":
							$_SESSION['transaction_info']->branch_description	=	$value;
							break;
						case "branch_ip":
							$_SESSION['transaction_info']->branch_ip			=	$value;
							break;
						case "branch_database":
							$_SESSION['transaction_info']->branch_database		=	$value;
							break;
						case "branch_allows_check":
							$_SESSION['transaction_info']->branch_allows_check	=	$value;
							break;
						case "branch_type":
							$_SESSION['transaction_info']->branch_type			=	$value;
							break;
						case "deleted":
							$_SESSION['transaction_info']->deleted				=	$value;
							break;
					}
				}
					
				// so I have reached the end of the section for this shop.
				// set user and password
				$_SESSION['transaction_info']->branch_user				=	$user;
				$_SESSION['transaction_info']->branch_password			=	$password;

				// does branch exist in local branches table? Since the table is truncated at start of this method, branch should never already exist but...
				$exists													=	$this->Branch->branch_exists($_SESSION['transaction_info']->branch_code);
				if ($exists == 1)
				{
					// update
					$_SESSION['transaction_id']							=	$_SESSION['transaction_info']->branch_code;
					$_SESSION['new']									=	0;
				}
				else
				{
					// insert
					$_SESSION['new']									=	1;
				}
				
				// save branch
				$this													->	Branch->save();
			}
		}
		
		// all done
		return;
	}

    function	slides_ventes(){
		
		$arc = shell_exec("cp -r /home/wrightetmathon/.hidrive.sonrisa/SLIDES /var/www/html/wrightetmathon/");
		$arc = shell_exec("cp -r /home/wrightetmathon/.hidrive.sonrisa/SLIDES_VENTES /var/www/html/wrightetmathon/");
		
	
	return;
	
	}
	// show flash_info
	function	flash_info()
	{
		// initialise
		unset($_SESSION['flash_info_show']);
		
		// calculate file hash for the flash_info_publish_me.pdf file.
		$flash_info_hash												=	hash_file('md5', "/home/wrightetmathon/.hidrive.sonrisa/FLASH_INFO/flash_info_publish_me.pdf");
		// compare to existing hash
		if ($flash_info_hash != $this->config->item('flash_info_hash'))
		{
			// not the same, so the flash info file has changed
			// update the flash_info_hash
			$update_success												=	$this->Appconfig->save('flash_info_hash', $flash_info_hash);
			if (!$update_success)
			{
				// do nothing for the moment
			}
			// update the flash_info_count
			$update_success												=	$this->Appconfig->save('flash_info_count', 0);
			if (!$update_success)
			{
				// do nothing for the moment
			}
			// copy the document to local PC
			copy("/home/wrightetmathon/.hidrive.sonrisa/FLASH_INFO/flash_info_publish_me.pdf", "/var/www/html/wrightetmathon/flash_info_publish_me.pdf");			
		}
		
		// show the document if count less than displays
		if ($this->config->item('flash_info_count') <= $this->config->item('flash_info_displays'))
		{
			// increment the count
			$count														=	$this->config->item('flash_info_count') + 1;
			// and save it
			$update_success												=	$this->Appconfig->save('flash_info_count', $count);
			if (!$update_success)
			{
				// do nothing for the moment
			}
			// copy the document to local PC
			copy("/home/wrightetmathon/.hidrive.sonrisa/FLASH_INFO/flash_info_publish_me.pdf", "/var/www/html/wrightetmathon/flash_info_publish_me.pdf");
			// show the document
			$_SESSION['flash_info_show']								=	1;
		}
		
		// return
		return;
	}
	
	// this method checks the initial.sql for static tables and updates local database accordingly.
	function	database_configuration()
	{
		// 1) check transaction_type table
		$this															->	Transaction->transaction_type();
		// 2) check transaction_multiplier
		$this															->	Transaction->transaction_multiplier();
		
		return;
	}
}
?>
