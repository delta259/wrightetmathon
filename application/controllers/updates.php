<?php
class Updates extends CI_Controller
{
    private $report_buffer = '';
    private $report_file_path = null;
    public $_progress_id = null;

    public function index()
    {
        // get class
        $_SESSION['controller_name']									=	strtolower(get_class($this));
        
        // show error message if not set elsewhere
        if (!isset($_SESSION['error_code'])) {
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
    
    public function manage_items_automatic()
    {
        // get existing PHP ini values
        $memory_limit													=	ini_get('memory_limit');
        $max_execution_time												=	ini_get('max_execution_time');
        
        // set ini values to avoid out of memory
        ini_set('memory_limit', '2000M');
        ini_set('max_execution_time', '1000');
        
        // initialise
        if (isset($_SESSION['import_mode']) && $_SESSION['import_mode'] == 'manual') {
            $from_dir													=	$_SESSION['upload_path'];
            $input_file_name											=	$_SESSION['upload_file'];
            ;
            $update_sales_price											=	$_SESSION['update_sales_price'];
            $create														=	$_SESSION['$create'];
            $redirect													=	'reports';
        } else {
            $from_dir													=	"/home/wrightetmathon/articles_local/";
            $input_file_name											=	"BASE_ARTICLE.xls";
            $input_file_name_xlsx										=	"BASE_ARTICLE.xlsx";
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
        if (!$conn) {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);							//	connection failed, so exit. This is fatal.
            if ($redirect == 'return') {
                return;
            } else {
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
        if (!file_exists($from_dir.$input_file_name_xlsx)) {
            if (!file_exists($from_dir.$input_file_name)) {
                $message													=	'BASE ARTICLES NON TROUVE => '.$from_dir.$input_file_name;
                $this														->	write_report($report_file, 'ERREUR', $message);
                $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);										// fatal error
                if ($redirect == 'return') {
                    return;
                } else {
                    redirect($redirect);
                }
            } else {
                $infile			=	$from_dir.$input_file_name;
            }
        } else {
            $infile			=	$from_dir.$input_file_name_xlsx;
        }

        // take a backup of the database
        $this->write_import_progress('backup', 'Sauvegarde de la base de donnees...', 0, 1);
        $retval															=	$this->backup_db($conn_parms, $now);
        $this->write_import_progress('backup', 'Sauvegarde terminee', 1, 1);

        if ($retval != 0) {
            $message													=	'BACKUP NON EFFECTUE => '.$conn_parms['database'];
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->write_import_progress('error', 'Erreur sauvegarde base de donnees', 0, 0);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }

        // get default codes
        // get branch code
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'branch_code'";
        $branch_code													=	$this->get($sql, $conn, $select_column);
        if ($branch_code == null) {										//	no default admin, so exit
            $message													=	'CODE SURCURRASALE NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'CODE SURCURRASALE => '.$branch_code;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // get default supplier code
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'default_supplier_id'";
        $default_supplier_id											=	$this->get($sql, $conn, $select_column);
        if ($default_supplier_id == null) {								//	no default supplier, so exit
            $message													=	'CODE FOURNISSEUR PAR DEFAUT NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'CODE FOURNISSEUR PAR DEFAUT => '.$default_supplier_id;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // get default pricelist code
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'pricelist_id'";
        $default_pricelist_id											=	$this->get($sql, $conn, $select_column);
        if ($default_pricelist_id == null) {								//	no default pricelist, so exit
            $message													=	'LISTE DE PRIX PAR DEFAUT NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'LISTE DE PRIX PAR DEFAUT => '.$default_pricelist_id;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // get default tax name
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'default_tax_1_name'";
        $default_tax_name												=	$this->get($sql, $conn, $select_column);
        if ($default_tax_name == null) {									//	no default tax name, so exit
            $message													=	'NOM TVA NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'NOM TVA PAR DEFAUT => '.$default_tax_name;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // get default tax rate
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'default_tax_1_rate'";
        $default_tax_rate												=	$this->get($sql, $conn, $select_column);
        if ($default_tax_rate == null) {									//	no default tax rate, so exit
            $message													=	'TAUX TVA NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'TAUX TVA PAR DEFAUT => '.$default_tax_rate;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // get admin name
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'admin_user_name'";
        $admin_user_name												=	$this->get($sql, $conn, $select_column);
        if ($admin_user_name == null) {									//	no default admin, so exit
            $message													=	'UITISATUER ADMIN NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'UTILISATEUR ADMIN PAR DEFAUT => '.$admin_user_name;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // now get admin employee number
        $select_column													=	'person_id';
        $sql 															=	"SELECT $select_column FROM `ospos_employees` WHERE `username` = '".$admin_user_name."' AND `branch_code` = '".$branch_code."'";
        $admin_employee_id												=	$this->get($sql, $conn, $select_column);
        if ($admin_employee_id == null) {									//	no admin employee id, so exit
            $message													=	'ID EMPLOYE POUR UTILISATUER ADMIN NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'ID EMPLOYE UTILISATEUR ADMIN PAR DEFAUT => '.$admin_employee_id;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // get last modified date
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'import_items_last_modified_date'";
        $last_modified_date												=	$this->get($sql, $conn, $select_column);
        if ($last_modified_date == null) {								//	no last modified date
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
            if ($conn->query($sql) === false) {
                $message												=	'ERREUR INSERTION DERNIERE MODIFICATION DATE => OSPOS_APP_CONFIG';
                $this													->	write_report($report_file, 'ERREUR', $message, $conn->error);
                $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);								// fatal error
                if ($redirect == 'return') {
                    return;
                } else {
                    redirect($redirect);
                }
            }
        } else {
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
        //$message														=	'LECTURE OBJET CREE => '.$objReader;
        //$this															->	write_report($report_file, 'SUCCES', $message);
    
        // Read the infile
        $objPHPExcel													=	new PHPExcel();
        $objPHPExcel													=	$objReader->load($infile);
        
        if (!$objPHPExcel) {
            $message													=	'PHP EXCEL OBJECT PAS CREE => '.$infile;
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'PHP EXCEL OBJECT CREE => '.$infile.' => DERNIERE MODIFICATION DATE = '.$objPHPExcel->getProperties()->getModified();
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // no need to do anything if the file has not been changed
        if ($objPHPExcel->getProperties()->getModified() == $this->config->item('import_items_last_modified_date')) {
            $message													=	'RIEN A FAIRE - FICHIER BASE_ARTICLE.xls PAS CHANGE';
            $this														->	write_report($report_file, 'SUCCES', $message);

            $message													=	'MAJ BASE ARTCLES TERMINE => '.date('Y-m-d H:i:s');
            $this														->	write_report($report_file, 'SUCCES', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
                
        // get sheet
        try {
            $sheet														=	$objPHPExcel->getSheet($objPHPExcel->getActiveSheetIndex());
        } catch (Exception $e) {
            $message													=	'FEUILLE NON LU=> '.$e->getMessage().' => Mise à jour interrompue';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
        
        if (!$sheet) {
            $message													=	'PHP EXCEL FEUILLE NON LU => '.$infile;
            $this														->	write_report($report_file, 'ERREUR', $message);
        } else {
            $message													=	'PHP EXCEL FEUILLE LU => '.$infile.' => Index feuille => '.$objPHPExcel->getActiveSheetIndex();
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // get sheet dimensions
        $highestRow														=	$sheet->getHighestRow();
        $highestColumn													=	$sheet->getHighestColumn();
        $message														=	'DIMENSIONS EXCEL => '.$highestRow.' => LIGNES => '.$highestColumn.' COLONNES';
        $this															->	write_report($report_file, 'SUCCES', $message);

        // read all data at once (much faster than per-row rangeToArray)
        $allData														=	$sheet->toArray(null, true, false, false);
        $this->write_import_progress('import_articles', 'Import BASE_ARTICLE...', 0, $highestRow);

        // get column indexes from data model
        $col_indexes													=	array();
        foreach ($_SESSION['data_model'] as $column) {
            $col_indexes[$column->column_database_field_name]			=	$this->get_column_index($column->column_database_field_name);
        }

        // pre-load caches for performance
        $category_cache													=	array();
        $result = $conn->query("SELECT `category_id`, `category_name`, `category_update_sales_price` FROM `ospos_categories` WHERE `branch_code` = '".$branch_code."'");
        if ($result) {
            while ($r = $result->fetch_assoc()) {
                $category_cache[$r['category_name']] = $r;
            }
            $result->free();
        }

        $items_cache													=	array();
        $result = $conn->query("SELECT * FROM `ospos_items` WHERE `branch_code` = '".$branch_code."'");
        if ($result) {
            while ($r = $result->fetch_assoc()) {
                $items_cache[$r['item_number']] = $r;
            }
            $result->free();
        }

        $suppliers_cache												=	array();
        $result = $conn->query("SELECT * FROM `ospos_items_suppliers` WHERE `supplier_id` = '".$default_supplier_id."' AND `branch_code` = '".$branch_code."'");
        if ($result) {
            while ($r = $result->fetch_assoc()) {
                $suppliers_cache[$r['item_id']] = $r;
            }
            $result->free();
        }

        $pricelists_cache												=	array();
        $result = $conn->query("SELECT * FROM `ospos_items_pricelists` WHERE `pricelist_id` = '".$default_pricelist_id."' AND `branch_code` = '".$branch_code."'");
        if ($result) {
            while ($r = $result->fetch_assoc()) {
                $pricelists_cache[$r['item_id']] = $r;
            }
            $result->free();
        }

        $message														=	'CACHES CHARGES => items:'.count($items_cache).' categories:'.count($category_cache).' suppliers:'.count($suppliers_cache).' pricelists:'.count($pricelists_cache);
        $this															->	write_report($report_file, 'SUCCES', $message);

        // start transaction for batch processing
        $conn->autocommit(false);
        $conn->begin_transaction();

        //  Loop through each row of the worksheet in turn starting with line 2 (to ignore titles)
        for ($row = 2; $row <= $highestRow; $row++) {
            //  Read a row of data from pre-loaded array
            $data_row													=	array($allData[$row - 1]);

            // initialise
            $item_data													=	array();
            $item_supp													=	array();
            $item_pric													=	array();
                        
            // count records read
            $number_of_records											=	$number_of_records + 1;
            
            // scrub and clean data
            foreach ($_SESSION['data_model'] as $column) {
                if ($column->column_data_type ==	'N') {
                    if (!is_numeric($data_row[0][$column->column_number])) {
                        $message										=	'DONNEES NON NUMERIQUE => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$column->column_number].' => '.$column->column_letter.$row;
                        $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
                        $data_row[0][$column->column_number]			=	0;
                    }
                } else {
                    $data_row[0][$column->column_number]				=	str_replace($search_for_uppercase, $replace_by_uppercase, $data_row[0][$column->column_number]);
                    $data_row[0][$column->column_number]				=	str_replace($search_for_lowercase, $replace_by_lowercase, $data_row[0][$column->column_number]);
                    $data_row[0][$column->column_number]				=	str_replace($search_for_specials, $replace_by_specials, $data_row[0][$column->column_number]);
                    $data_row[0][$column->column_number]				=	str_replace($search_for_winker, $replace_by_winker, $data_row[0][$column->column_number]);
                }
            }
                        
            // test for category and if not exists create it (using cache)
            $cat_name													=	$data_row[0][$col_indexes['category']];

            if (!isset($category_cache[$cat_name])) {
                // doesn't exist in cache, so create it
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
                                                                        .	"'".$cat_name."',"		// category_name
                                                                        .	"'".$cat_name."',"		// category_desc
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
                if ($conn->query($sql) === false) {
                    $message											=	'ERREUR INSERTION OSPOS_CATEGORIES => '.$cat_name.' => '.$data_row[0][$col_indexes['item_number']];
                    $this												->	write_report($report_file, 'ERREUR', $message, $conn->error);
                    $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);							// fatal error
                    if ($redirect == 'return') {
                        return;
                    } else {
                        redirect($redirect);
                    }
                }

                // add to cache
                $category_cache[$cat_name] = array(
                    'category_id' => $conn->insert_id,
                    'category_name' => $cat_name,
                    'category_update_sales_price' => 'Y'
                );

                // write message to output file
                $message												=	'SUCCESS INSERTION OSPOS_CATEGORIES => '.$cat_name.' => '.$data_row[0][$col_indexes['item_number']];
                $this													->	write_report($report_file, 'SUCCES', $message);
            }

            // get category id and update_sales_price from cache
            $category_id												=	$category_cache[$cat_name]['category_id'];
            $update_sales_price											=	$category_cache[$cat_name]['category_update_sales_price'];
            if ($_SESSION['branchtype']=='F') {
                $update_sales_price = 'N';
            }
    
            // load defaults based on in coming info
            // load DLUO indicator
            // if coloum TYPE_PRO, G, index 6,  = L its a liquid, so use DLUO
            // get column_index
            if ($data_row[0][6] == 'L') {
                if ($this->config->item('use_DLUO')) {
                    $dluo												=	$this->config->item('use_DLUO');
                } else {
                    $dluo												=	'Y';
                }
            } else {
                $dluo													=	'N';
            }
                
            // see if aticle is desactivated
            if ($data_row[0][$col_indexes['deleted']] == 1) {
                $reorder_policy											=	'N';
                $stock_rotate											=	1;
                $deleted												=	1;
            } else {
                $reorder_policy    									=	'Y';
                $stock_rotate											=	0;
                $deleted												=	0;
            }
            
            // format nicotine
            if (!is_numeric($data_row[0][$col_indexes['nicotine']])) {
                $nicotine												=	0;
            } else {
                $nicotine												=	$data_row[0][$col_indexes['nicotine']];
            }
            
            // format volume
            if (!is_numeric($data_row[0][$col_indexes['volume']])) {
                $volume													=	0;
            } else {
                $volume													=	$data_row[0][$col_indexes['volume']];
            }

            // forinfo prix de vente ttc forcé
            if ($data_row[0][$col_indexes['forced']] =='0' ) {
                $forced													=	'Y';
            } else {
                $forced													=	'N';
            }
                    
            // get current item from cache
            $current_item_number										=	$data_row[0][$col_indexes['item_number']];

            // was item found in cache?
            if (isset($items_cache[$current_item_number])) {
                // item record was found so load current item data
                $item_data 												=	$items_cache[$current_item_number];
                
                // check supplier record in cache
                // was an item/supplier record found?
                if (!isset($suppliers_cache[$item_data['item_id']])) {
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
                                                                        .	"'N',"								// supplier_preferred
                                                                        .	"'',"								// supplier_item_number
                                                                        .	$data_row[0][$col_indexes['supplier_cost_price']].","				// supplier_cost_price
                                                                        .	"'N',"			// supplier_reorder_policy
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
                    if ($conn->query($sql) === false) {
                        $message										=	'ERREUR INSERTION OSPOS_ITEMS_SUPPLIERS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
                        $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
                        $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);						// fatal error
                        if ($redirect == 'return') {
                            return;
                        } else {
                            redirect($redirect);
                        }
                    }
                
                    // write message to output file
                    $message											=	'SUCCESS INSERTION OSPOS_ITEMS_SUPPLIERS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
                    $this												->	write_report($report_file, 'SUCCES', $message);

                    // add to cache
                    $suppliers_cache[$item_data['item_id']] = array(
                        'item_id' => $item_data['item_id'],
                        'supplier_id' => $default_supplier_id,
                        'supplier_cost_price' => $data_row[0][$col_indexes['supplier_cost_price']],
                        'supplier_bar_code' => $data_row[0][$col_indexes['supplier_bar_code']],
                        'branch_code' => $branch_code
                    );
                }

                // load item/supplier data from cache
                $item_supp 												=	$suppliers_cache[$item_data['item_id']];

                // check pricelist record in cache
                if (!isset($pricelists_cache[$item_data['item_id']])) {
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
                    if ($conn->query($sql) === false) {
                        $message										=	'ERREUR INSERTION OSPOS_ITEMS_PRICELISTS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
                        $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
                        $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);						// fatal error
                        if ($redirect == 'return') {
                            return;
                        } else {
                            redirect($redirect);
                        }
                    }
                
                    // write message to output file
                    $message											=	'SUCCESS INSERTION OSPOS_ITEMS_PRICELISTS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
                    $this												->	write_report($report_file, 'SUCCES', $message);

                    // add to cache
                    $pricelists_cache[$item_data['item_id']] = array(
                        'item_id' => $item_data['item_id'],
                        'pricelist_id' => $default_pricelist_id,
                        'unit_price' => $data_row[0][$col_indexes['unit_price']],
                        'unit_price_with_tax' => $data_row[0][$col_indexes['unit_price_with_tax']],
                        'branch_code' => $branch_code
                    );
                } else {
                    $item_sale_pric 										=	$pricelists_cache[$item_data['item_id']];
                    
                    if (($update_sales_price == 'Y' and	number_format($item_sale_pric['unit_price'], 3) > number_format($data_row[0][$col_indexes['unit_price']], 3))
                    or	($update_sales_price == 'Y' and	number_format($item_sale_pric['unit_price_with_tax'], 3) 	>	number_format($data_row[0][$col_indexes['unit_price_with_tax']], 3))
                    or	(number_format($item_sale_pric['unit_price_with_tax'], 3) == number_format(0, 3))
                    or	($update_sales_price == 'Y')and ($forced=='Y')) {
                        $sql												=	"UPDATE `ospos_items_pricelists`"
                                                                        .	" SET "
                                                                        .	" unit_price = ".$data_row[0][$col_indexes['unit_price']].","
                                                                        .	" unit_price_with_tax = ".$data_row[0][$col_indexes['unit_price_with_tax']]." "
                                                                        .	" WHERE "
                                                                        .	" item_id = ".$item_data['item_id']." AND "		// item_id
                                                                        .	" pricelist_id = ".$default_pricelist_id." AND "			// pricelist_id
                                                                        .	" branch_code = '".$branch_code."'" ;

                        // update the record
                        if ($conn->query($sql) === false) {
                            $message										=	'ERREUR UPDATE OSPOS_ITEMS_PRICELISTS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
                            $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
                            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);						// fatal error
                            if ($redirect == 'return') {
                                return;
                            } else {
                                redirect($redirect);
                            }
                        }
                
                        // write message to output file
                        $message											=	'SUCCESS UPDATE OSPOS_ITEMS_PRICELISTS => '.$item_data['item_id'].' => '.$data_row[0][$col_indexes['item_number']];
                        $this												->	write_report($report_file, 'SUCCES', $message);
                    }
                }
                // load item/pricelist data from cache
                $item_pric 												=	$pricelists_cache[$item_data['item_id']];
                
                // now I have all the data I need to continue
                // test if anything has changed
                if ($item_data['name'] 								!=	$data_row[0][$col_indexes['name']]
                    or	$item_data['category_id'] 						!=	$category_id
                    or	$item_data['category'] 							!=	$data_row[0][$col_indexes['category']]
                    or	$item_data['barcode'] 							!=	$data_row[0][$col_indexes['supplier_bar_code']]
                    or	$item_data['image_file_name'] 					!=	$data_row[0][$col_indexes['image_file_name']]
                    or	$item_data['dluo_indicator']					!=	$dluo
                    or	$item_data['deleted']							!=	$deleted
                    or	$item_data['volume']							!=	$volume
                    or	$item_data['nicotine']							!=	$nicotine
                    or	$item_supp['supplier_cost_price']				!=	number_format($data_row[0][$col_indexes['supplier_cost_price']], 3)
                    or	($update_sales_price == 'Y' and	$item_pric['unit_price'] 			>	number_format($data_row[0][$col_indexes['unit_price']], 3))
                    or	($update_sales_price == 'Y' and	$item_pric['unit_price_with_tax'] 	>	number_format($data_row[0][$col_indexes['unit_price_with_tax']], 3))
                    ) {
                    // something changed so set up the data for update
                    // if incoming barcode is blank use existing
                    // DDE 12/10/2018
                    // if ($data_row[0][$col_indexes['supplier_bar_code']] == '')
                    if ($item_data['barcode'] != "") {
                        $data_row[0][$col_indexes['supplier_bar_code']]	=	$item_data['barcode'];
                    }
                    
                    // if incoming image file is blank use existing
                    if ($data_row[0][$col_indexes['image_file_name']] == '') {
                        $data_row[0][$col_indexes['image_file_name']]	=	$item_data['image_file_name'];
                    }
                    
                    if ($data_row[0][$col_indexes['forced']] =='2' ) // Libellé produit mis à jour si 2 dans la colonne DPT_VENTE
                    {
                        $sql =	"UPDATE `ospos_items` SET "
                                    .	"`name`='".$data_row[0][$col_indexes['name']]."' "
                                    .	" WHERE "
                                    .	"`item_id`=".$item_data['item_id']
                                    .	" AND "
                                    .	"`branch_code`='".$branch_code."'";

                        // do update and test result
                        if ($conn->query($sql) === false) {
                            // if fail - output error message to report
                            $message										=	'ERREUR MAJ OSPOS_ITEMS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
                            $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
                            $number_of_errors								=	$number_of_errors + 1;
                        }
                    }

                    // create update sql
                    $sql 												=	"UPDATE `ospos_items` SET "
                    //                                                    .	"`name`='".$data_row[0][$col_indexes['name']]."',"
                                                                        .	"`category_id`=".$category_id.","
                                                                        .	"`nicotine`=".$nicotine.","
                                                                        .	"`volume`=".$volume.","
                    //													.	"`deleted`= `deleted` || ".$deleted.","
                                                                        .	"`category`='".$data_row[0][$col_indexes['category']]."',"
                                                                        .	"`image_file_name`='".$data_row[0][$col_indexes['image_file_name']]."',"
                                                                        .	"`dluo_indicator`='".$dluo."'"
                                                                        .	" WHERE "
                                                                        .	"`item_id`=".$item_data['item_id']
                                                                        .	" AND "
                                                                        .	"`branch_code`='".$branch_code."'";
        
                    // do update and test result
                    if ($conn->query($sql) === false) {
                        // if fail - output error message to report
                        $message										=	'ERREUR MAJ OSPOS_ITEMS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
                        $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
                        $number_of_errors								=	$number_of_errors + 1;
                    } else {
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
                        if ($conn->query($sql) === false) {
                            // if fail - output error message to report
                            $message									=	'ERREUR INSERT OSPOS_INVENTORY => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
                            $this										->	write_report($report_file, 'ERREUR', $message, $conn->error);
                        }

                        // update supplier record
                        // .	"`supplier_reorder_policy`='".$reorder_policy."',"
                        $sql 											=	"UPDATE `ospos_items_suppliers` SET "
                                                                        .	"`supplier_cost_price`=".$data_row[0][$col_indexes['supplier_cost_price']].","
                                                                        .	"`supplier_bar_code`='".$data_row[0][$col_indexes['supplier_bar_code']]."'"
                                                                        .	" WHERE "
                                                                        .	"`item_id`=".$item_data['item_id']
                                                                        .	" AND "
                                                                        .	"`supplier_id`=".$default_supplier_id
                                                                        .	" AND "
                                                                        .	"`branch_code`='".$branch_code."'";
        
                        // do update and test result
                        if ($conn->query($sql) === false) {
                            // if fail - output error message to report
                            $message									=	'ERREUR MAJ OSPOS_ITEMS_SUPPLIERS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
                            $this										->	write_report($report_file, 'ERREUR', $message, $conn->error);
                        }

                        // update price list record	but only if allowed
                        if ($update_sales_price == 'Y' and
                            (($item_pric['unit_price'] 			>	number_format($data_row[0][$col_indexes['unit_price']], 3))
                            or	$item_pric['unit_price_with_tax'] 	>	number_format($data_row[0][$col_indexes['unit_price_with_tax']], 3))
                            ) {
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
                            if ($conn->query($sql) === false) {
                                // if fail - output error message to report
                                $message								=	'ERREUR MAJ OSPOS_ITEMS_PRICELISTS => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
                                $this									->	write_report($report_file, 'ERREUR', $message, $conn->error);
                            }
                        }
                    }
                } else {
                    $number_of_unchanged								=	$number_of_unchanged	+	1;
                }
            } else {
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
                                                                        .	"`offer_value`,"
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
                                                                        .   "0".","                         // offer_value
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
                if ($conn->query($sql) === false) {
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
                                                                
                if ($conn->query($sql) === false) {
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
                
                if ($conn->query($sql) === false) {
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
                if ($conn->query($sql) === false) {
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
                if ($conn->query($sql) === false) {
                    $message											=	'ERREUR INSERTION OSPOS_ITEMS_PRICELISTS => '.$inserted_item_id.' => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
                    $this												->	write_report($report_file, 'ERREUR', $message, $conn->error);
                }
            }

            // periodic commit and flush every 500 rows
            if ($row % 500 == 0) {
                $conn->commit();
                $conn->begin_transaction();
                $this->flush_report();
                $this->write_import_progress('import_articles', 'Import BASE_ARTICLE... ' . $row . '/' . $highestRow, $row, $highestRow);
            }
        }

        // final commit
        $conn->commit();
        $conn->autocommit(true);
        $this->flush_report();
        $this->write_import_progress('import_articles', 'Import BASE_ARTICLE termine', $highestRow, $highestRow);

        // free memory
        unset($allData);
        unset($items_cache);
        unset($suppliers_cache);
        unset($pricelists_cache);
        unset($category_cache);

        //update items tax
        $sql_item_tax = 'UPDATE `ospos_items_pricelists`,`ospos_items_taxes`
                         SET `ospos_items_pricelists`.`unit_price` = ROUND (`unit_price_with_tax` / (1 + (`ospos_items_taxes`.`percent` / 100)),3)
                         WHERE
                             `ospos_items_pricelists`.`item_id` = `ospos_items_taxes`.`item_id` AND
                             `ospos_items_pricelists`.`unit_price` != ROUND(`unit_price_with_tax` /(1 +(percent / 100)),3)';
        if ($conn->query($sql_item_tax) === false) {
            $message = 'ERREUR UPDATE OSPOS_ITEMS_PRICELISTS';
            $this->write_report($report_file, 'ERREUR', $message, $conn->error);
        } else {
            $message = 'SUCCESS UPDATE tax for item';
            $this->write_report($report_file, 'SUCCES', $message);
        }

        // create update sql2
        $sql2 												=	"UPDATE `ospos_items` SET	`deleted` = 0 WHERE `deleted`= 1 AND `quantity` > 0 ";

        // do update and test result
        if ($conn->query($sql2) === false) {
            // if fail - output error message to report
            $message										=	'ERREUR REACTIVATION d\'articles au stock positif  '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
            $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
            $number_of_errors								=	$number_of_errors + 1;
        } else {
            // output to file
            $message										=	'SUCCESS REACTIVATION d\'articles au stock positif => '.$data_row[0][$col_indexes['item_number']].' => '.$data_row[0][$col_indexes['name']];
            $this											->	write_report($report_file, 'SUCCES', $message);
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
        if ($conn->query($sql) === false) {
            $message													=	'ERREUR MAJ DERNIERE MODIFICATION DATE => OSPOS_APP_CONFIG';
            $this														->	write_report($report_file, 'ERREUR', $message, $conn->error);
        }
        
        // return
        $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
        if ($redirect == 'return') {
            return;
        } else {
            redirect($redirect);
        }
    }    // fin Fonction du début du fichier
    
    public function manage_items_kit_automatic()
    {
        
        //file
        //PK kit        SO item        quantity

        // get existing PHP ini values
        $memory_limit				 = ini_get('memory_limit');
        $max_execution_time			 = ini_get('max_execution_time');
        
        // set ini values to avoid out of memory
        ini_set('memory_limit', '2000M');
        ini_set('max_execution_time', '1000');
        
        // initialise
        /*
        if (isset($_SESSION['import_mode']) && $_SESSION['import_mode'] == 'manual')
        {
            $from_dir													=	$_SESSION['upload_path'];
            $input_file_name											=	$_SESSION['upload_file'];;
            $update_sales_price											=	$_SESSION['update_sales_price'];
            $create														=	$_SESSION['$create'];
            $redirect													=	'reports';
        }
        else
        {//*/
        $from_dir				= "/home/wrightetmathon/articles_local/";
        $input_file_name		= "BASE_KIT.xls";
        $input_file_name_xlsx	= "BASE_KIT.xlsx";
        $update_sales_price		= 'N';
        $create					= 'Y';
        $redirect				= 'return';
        /*s	}//*/
        
        $infile															= $from_dir.$input_file_name;
        $now															= date('Y-m-d H:i:s');
        
        $number_of_records		 = 0;
        $number_of_updates		 = 0;
        $number_of_adds			 = 0;
        $number_of_excludes		 = 0;
        $number_of_errors		 = 0;
        $number_of_unchanged	 = 0;
        
        $search_for_uppercase	 = array('À', 'Â', 'Ä', 'È', 'É', 'Ê', 'Ë', 'Î', 'Ï', 'Ô', 'Œ', 'Ù', 'Û', 'Ü', 'Ÿ');
        $replace_by_uppercase	 = array('A', 'A', 'A', 'E', 'E', 'E', 'E', 'I', 'I', 'O', 'OE', 'U', 'U', 'U', 'Y');
        
        $search_for_lowercase	 = array('à', 'â', 'ä', 'è', 'é', 'ê', 'ë', 'î', 'ï', 'ô', 'œ', 'ù', 'û', 'ü', 'ÿ');
        $replace_by_lowercase	 = array('a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'oe', 'u', 'u', 'u', 'y');
        
        $search_for_specials	 = array('Ç', 'ç', '«', '»', '€', ',', '#', '$', '°', '§', '^', '`', '"', ';', ':');
        $replace_by_specials	 = array('C', 'c', '"', '"', '€', ' ', '#', '$', ' ', ' ', ' ', ' ', '"', ' ', ' ');
        
        $search_for_winker		 = array("'");
        $replace_by_winker		 = array(" ");
        
        $data_rows				 = array();

        require_once "/var/www/html/wrightetmathon/application/third_party/phpexcel/Classes/PHPExcel.php";
        require_once "/var/www/html/wrightetmathon/application/third_party/phpexcel/Classes/PHPExcel/IOFactory.php";

        $_SESSION['data_model']											= $this->Import->get_all()->result();

        // get connection parameters and open connection
        $conn_parms				 = array();
        $conn_parms				 = $this->get_conn_parms($redirect);
        $conn					 = $this->open_db($conn_parms);
        if (!$conn) {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);							//	connection failed, so exit. This is fatal.
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }

        // Setup report file
        $report_file													=	$this->report_file($conn, $now);
        $this															->	write_headers($report_file);
        
        // Output process started
        $message														=	'MAJ BASE KIT COMMENCE => '.$now.' => '.$infile;
        $this															->	write_report($report_file, 'SUCCES', $message);
        
        // make sure input file exists, if not exit
        if (!file_exists($from_dir.$input_file_name_xlsx)) {
            if (!file_exists($from_dir.$input_file_name)) {
                $message													=	'BASE KIT NON TROUVE => '.$from_dir.$input_file_name;
                $this														->	write_report($report_file, 'ERREUR', $message);
                $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);										// fatal error
                if ($redirect == 'return') {
                    return;
                } else {
                    redirect($redirect);
                }
            } else {
                $infile			=	$from_dir.$input_file_name;
            }
        } else {
            $infile			=	$from_dir.$input_file_name_xlsx;
        }
        $infile = $from_dir.$input_file_name_xlsx;

        // get default codes
        // get branch code
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'branch_code'";
        $branch_code													=	$this->get($sql, $conn, $select_column);
        if ($branch_code == null) {										//	no default admin, so exit
            $message													=	'CODE SURCURRASALE NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'CODE SURCURRASALE => '.$branch_code;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }

        // get default supplier code
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'default_supplier_id'";
        $default_supplier_id											=	$this->get($sql, $conn, $select_column);
        if ($default_supplier_id == null) {								//	no default supplier, so exit
            $message													=	'CODE FOURNISSEUR PAR DEFAUT NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'CODE FOURNISSEUR PAR DEFAUT => '.$default_supplier_id;
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
        //$objReader														->	setReadDataOnly(true);
        //$message														=	'LECTURE OBJET CREE => '.$objReader;
        //$this															->	write_report($report_file, 'SUCCES', $message);
        
        // Read the infile
        $objPHPExcel													=	new PHPExcel();
        $objPHPExcel													=	$objReader->load($infile);

        if (!$objPHPExcel) {
            $message													=	'PHP EXCEL OBJECT PAS CREE => '.$infile;
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'PHP EXCEL OBJECT CREE => '.$infile.' => DERNIERE MODIFICATION DATE = '.$objPHPExcel->getProperties()->getModified();
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        /*
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
        }//*/
                
        // get sheet
        try {
            $sheet														=	$objPHPExcel->getSheet($objPHPExcel->getActiveSheetIndex());
        } catch (Exception $e) {
            $message													=	'FEUILLE NON LU=> '.$e->getMessage().' => Mise à jour interrompue';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }

        if (!$sheet) {
            $message													=	'PHP EXCEL FEUILLE NON LU => '.$infile;
            $this														->	write_report($report_file, 'ERREUR', $message);
        } else {
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
        foreach ($_SESSION['data_model'] as $column) {
            $col_indexes[$column->column_database_field_name]			=	$this->get_column_index($column->column_database_field_name);
        }

        $this->write_import_progress('import_kits', 'Import BASE_KIT...', 0, $highestRow);

        //  Loop through each row of the worksheet in turn starting with line 2 (to ignore titles)
        for ($row = 2; $row <= $highestRow; $row++) {
            //  Read a row of data into an array
            $data_row													=	array();
            $data_row 													=	$sheet->rangeToArray('A'.$row.':'.$highestColumn.$row, null, true, false, false);

            $item_kit_pk = $data_row[0][0];
            $item_kit_item_so = $data_row[0][1];
            $item_kit_item_quantity = $data_row[0][2];
    

            // initialise
            $item_data													=	array();
            $item_supp													=	array();
            $item_pric													=	array();
                
            // count records read
            $number_of_records											=	$number_of_records + 1;

            //$item_kit_pk                   PK..... code pack kit
            //$item_kit_item_so              SO..... code item
            //$item_kit_item_quantity        quantity item dans le kit
            // get current item id by item nummber
            $sql 	 = "SELECT * FROM `ospos_items` WHERE `item_number` = '".$item_kit_pk."' AND `branch_code` = '".$branch_code."'";
            $result  = $conn->query($sql);

            // was item found?
            if ($result->num_rows > 0) {
                // item record was found so load current item data
                $item_data 		= $result->fetch_assoc();
    
                //put item like kit
                $sql_update_items = "UPDATE `ospos_items` SET `DynamicKit` = 'Y', `kit` = 1 WHERE `item_id` = '".$item_data['item_id']."' AND `branch_code` = '".$branch_code."'";
                $conn->query($sql_update_items);


                // get kit in ospos_item_kits
                $sql = "SELECT * FROM `ospos_item_kits` WHERE `item_kit_id` = '".$item_data['item_id']."' AND `branch_code` = '".$branch_code."'";
                $result = $conn->query($sql);
                $item_kit_data = $result->fetch_assoc();
        

                $sql_supplier = "SELECT * FROM `ospos_items_suppliers` WHERE `item_id` = '".$item_data['item_id']."' AND `branch_code` = '".$branch_code."'";
                $result_supplier = $conn->query($sql_supplier);
                $item_supplier_data = $result_supplier->fetch_assoc();
        
                // was an item record found?
                if ($result->num_rows == 0) {
                    // no record found add one
                    // insert item_kits if not exist
                    $sql = "INSERT INTO `ospos_item_kits`"
                   .	"(`item_kit_id`,"
                   .    "`kit_item_id`,"
                   .	"`name`,"
                   .	"`description`,"
                   .	"`barcode`,"
                   .	"`branch_code`)"
                    
                   .	"VALUES"
                   .	"(".$item_data['item_id'].","			        // item_kit_id
                   .    "0,"
                   .	"'" . $item_kit_pk."',"			                    // name
                   .    "'" . $item_data['name']."',"                          // description
                   .	"'" . $item_supplier_data['supplier_bar_code']."',"    // barcode
                   .	"'".$branch_code."'"				            // branch_code
                   .	")";
            
                    // insert the record
                    if ($conn->query($sql) === false) {
                        //			$message = 'ERREUR INSERTION OSPOS_ITEM_KITS => '.$item_data['item_id'].' => '.$item_kit_pk;
    //			$this->write_report($report_file, 'ERREUR', $message, $conn->error);
    //			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);						// fatal error
    //			if ($redirect == 'return')
    //			{
    //				return;
    //			}
    //			else
    //			{
    //				redirect($redirect);
    //			}
                    }
        
                    // write message to output file
                    $message = 'SUCCESS INSERTION OSPOS_ITEM_KITS => '.$item_data['item_id'].' => '.$item_kit_pk;
                    $this	 ->write_report($report_file, 'SUCCES', $message);
            
                // now get current item/supplier record again
            //$sql 	 = "SELECT * FROM `ospos_items` WHERE `item_number` = '".$data_row[0][$col_indexes['C_PRODUIT']]."' AND `branch_code` = '".$branch_code."'";
            //$result  = $conn->query($sql);
                } else { // kit ever exist
            if (
                ($item_kit_data['name'] != $item_kit_pk) //name PK
            ||	($item_kit_data['description'] != $item_data['name']) //description
            ||	($item_kit_data['barcode'] != $item_supplier_data['supplier_bar_code']) //barcode
            ||	($item_kit_data['branch_code'] != $branch_code) //branch_code
            ) {    //UPDATE
                // update item_kits if not exist
                $sql = "UPDATE `ospos_item_kits` SET "
                .	"`name`='".$item_kit_pk."',"
                .	"`description`='".$item_data['name']."',"
                .	"`barcode`='".$item_supplier_data['supplier_bar_code']."'"
                .	" WHERE "
                .	"`item_kit_id`='".$item_data['item_id']
                .   "'";
            }
                }

                $sql_item_kit_item = "SELECT * FROM `ospos_items` WHERE `item_number` = '".$item_kit_item_so."' AND `branch_code` = '".$branch_code."'";
                $result_item_kit_item = $conn->query($sql_item_kit_item);
                $item_kit_item = $result_item_kit_item->fetch_assoc();
        
                $sql_ospos_item_kit_items = "SELECT * FROM `ospos_item_kit_items` WHERE `item_kit_id` = '".$item_data['item_id']."' AND `item_id` = '".$item_kit_item['item_id']."' AND `branch_code` = '".$branch_code."'";
                $result_ospos_item_kit_items = $conn->query($sql_ospos_item_kit_items);
                $item_kit_data = $result_ospos_item_kit_items->fetch_assoc();

                // was an item record found?
                if ($result_ospos_item_kit_items->num_rows == 0) {
                    //$item_kit_item = $result_item_kit_item->fetch_assoc();
                    // no record found add one
                    // insert item_kits if not exist
        
                    // plusieurs items par kit
                    // insert item_kit_items if not exist
                    $sql = "INSERT INTO `ospos_item_kit_items`"
                   .	"(`item_kit_id`,"
                   .	"`item_id`,"
                   .	"`quantity`,"
                   .	"`branch_code`)"
                    
                   .	"VALUES"
                   .	"(".$item_data['item_id'].","          // item_kit_id
                   .	$item_kit_item['item_id'].","         // item_id
                   .	$item_kit_item_quantity.","   // quantity
                   .	"'".$branch_code."'"          // branch_code
                   .	")";
            
                    // insert the record
                    if ($conn->query($sql) === false) {
                        //			$message = 'ERREUR INSERTION OSPOS_ITEM_KIT_ITEMS => '.$item_data['item_id'].' => '.$item_kit_pk;
    //			$this->write_report($report_file, 'ERREUR', $message, $conn->error);
    //			$this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);						// fatal error
    //			if ($redirect == 'return')
    //			{
    //				return;
    //			}
    //			else
    //			{
    //				redirect($redirect);
    //			}
                    }
                    // write message to output file
                    $message = 'SUCCESS INSERTION OSPOS_ITEM_KIT_ITEMS => '.$item_data['item_id'].' => '.$item_kit_pk;
                    $this	 ->write_report($report_file, 'SUCCES', $message);
                } else { // kit ever exist
                    if (
                ($sql_ospos_item_kit_items['quantity'] != $item_kit_item_quantity) //branch_code
            ||	($sql_ospos_item_kit_items['branch_code'] != $branch_code) //branch_code
            ) {    //UPDATE
                // update item_kits if not exist
                $sql = "UPDATE `ospos_item_kits` SET "
                .	"`name`=".$item_kit_item_quantity.","
                .	"`barcode`=".$branch_code
                .	" WHERE "
                .	"`item_kit_id`='".$item_data['item_id']
                .	" AND "
                .	"`item_id`=".$sql_item_kit_item['item_id']
                .   "'";
                    }
                }
            } else {
                $number_of_unchanged								=	$number_of_unchanged	+	1;
            }
       
            $sql_item_kit_item = "UPDATE `ospos_items_suppliers` INNER JOIN `ospos_items`".
                    " ON `ospos_items`.`item_id` = `ospos_items_suppliers`.`item_id`".
                    " SET`supplier_reorder_pack_size` = ".$item_kit_item_quantity.
                    " WHERE `ospos_items`.`item_number` = '".$item_kit_item_so."' AND ".
                    " `ospos_items_suppliers`.`branch_code` = '".$branch_code."' AND ".
                    " `ospos_items`.`name` LIKE '%RESISTANCE%' AND `ospos_items_suppliers`.`supplier_id` = '" .$default_supplier_id."'";  ;
            $result_item_kit_item = $conn->query($sql_item_kit_item);

            // update progress every 100 rows
            if ($row % 100 == 0) {
                $this->write_import_progress('import_kits', 'Import BASE_KIT... ' . $row . '/' . $highestRow, $row, $highestRow);
            }
        }

        $this->write_import_progress('import_kits', 'Import BASE_KIT termine', $highestRow, $highestRow);

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
        if ($conn->query($sql) === false) {
            $message													=	'ERREUR MAJ DERNIERE MODIFICATION DATE => OSPOS_APP_CONFIG';
            $this														->	write_report($report_file, 'ERREUR', $message, $conn->error);
        }

        // return
        $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
        if ($redirect == 'return') {
            return;
        } else {
            redirect($redirect);
        }
    

        /*


                // update item_kits if not exist
                $sql = "UPDATE `ospos_item_kits` SET "
                .	"`name`=".$data_row[0][$col_indexes['unit_price']].","
                .	"`description`=".$data_row[0][$col_indexes['unit_price_with_tax']]
                .	"`barcode`=".$data_row[0][$col_indexes['unit_price_with_tax']]
                .	" WHERE "
                .	"`item_kit_id`='".$branch_code."'";

                // plusieurs items par kit
                // update item_kits if not exist
                $sql = "UPDATE `ospos_item_kit_items` SET "
                .	"`unit_price`=".$data_row[0][$col_indexes['unit_price']].","
                .	"`unit_price_with_tax`=".$data_row[0][$col_indexes['unit_price_with_tax']]
                .	" WHERE "
                .	"`item_id`=".$item_data['item_id']
                .	" AND "
                .	"`branch_code`='".$branch_code."'";



                // insert item_kits if not exist
                $sql = "INSERT INTO `ospos_item_kits`"
                           .	"(`item_kit_id`,"
                           .	"`name`,"
                           .	"`description`,"
                           .	"`barcode`,"
                           .	"`branch_code`)"

                           .	"VALUES"
                           .	"(".$inserted_item_id.","			                        // item_kit_id
                           .	$name.","			                                        // name
                           .	$data_row[0][$col_indexes['unit_price']].","				// description
                           .	$data_row[0][$col_indexes['unit_price_with_tax']].","	    // barcode
                           .	"'".$branch_code."'"				                        // branch_code
                           .	")";

                // plusieurs items par kit
                // insert item_kit_items if not exist
                $sql = "INSERT INTO `ospos_item_kit_items`"
                           .	"(`item_kit_id`,"
                           .	"`item_id`,"
                           .	"`quantity`,"
                           .	"`branch_code`)"

                           .	"VALUES"
                           .	"(".$inserted_item_id.","			                        // item_kit_id
                           .	$name.","			                                        // item_id
                           .	$data_row[0][$col_indexes['unit_price']].","				// quantity
                           .	"'".$branch_code."'"				                        // branch_code
                           .	")";//*/
    }

    public function manage_items_manual()
    {
        // initialise
        $dir															=	"/var/www/html/wrightetmathon_uploads/";
        $upload_file_name												=	"BASE_ARTICLES_MANUAL.xls";
        $update_sales_price												=	$this->input->post('update_sales_price');
        $create															=	$this->input->post('create');

        // test upload dir exists
        if (!file_exists($dir)) {
            // make it if not found
            mkdir($dir);
        }
        
        // test if upload dir is not empty
        if ((new \FilesystemIterator($dir))->valid() == true) {
            // if it is not empty, delete any files in it
            if (!array_map('unlink', glob($dir."*"))) {
                $_SESSION['error_code']									=	'05490';
                redirect($_SESSION['controller_name']);
            }
        }

        // config upload library for first upload
        $config = array(
                        'upload_path' 	=>	$dir,
                        'allowed_types' =>	'*',
                        'overwrite' 	=>	true
                        );
        $this->upload->initialize($config);
        
        // test file selected
        if (!$this->upload->do_upload('userfile')) {
            // set message depends on what is in the error string
            if (strpos($this->upload->display_errors(), 'select')) {
                $_SESSION['error_code']									=	'05470';
                redirect($_SESSION['controller_name']);
            }
        }
        
        // check file extension
        $data['upload_data']											=	array();
        $data['upload_data']											=	$this->upload->data();
        if ($data['upload_data']['file_ext'] != '.xls') {
            $_SESSION['error_code']										=	'05480';
            redirect($_SESSION['controller_name']);
        }
        
        // clear upload path
        array_map('unlink', glob($dir."*"));
        
        // If here then I am reasonably sure I have a valid and authentic file so initialise the upload library to
        // convert file name to standard
        $config = array(
                        'file_name'		=>	$upload_file_name,
                        'upload_path' 	=>	$dir,
                        'allowed_types' =>	'*',
                        'overwrite' 	=>	true
                        );
        $this->upload->initialize($config);
        
        // now upload the file again
        if (!$this->upload->do_upload('userfile')) {
            // set message depends on what is in the error string
            if (strpos($this->upload->display_errors(), 'select')) {
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

        // now do update		$this															->	manage_items_automatic();
        return;
    }
    
    public function get_conn_parms($redirect)
    {
        // initialise
        $config_file													=	"/var/www/html/wrightetmathon.ini";
        
        // find and set server name
        $found_flag														=	'N';
        $search															=	'hostname';
        $config															=	fopen($config_file, "r");
        if (!$config) {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
        while (!feof($config) and $found_flag == 'N') {
            $line														=	fgets($config);
            if (strpos($line, $search) !== false) {
                $found_flag												=	'Y';
            }
        }
        fclose($config);
        if ($found_flag == 'N') {
            $line = 'locathost';
        }
        $array															=	array();
        $array															=	explode('=', $line);
        $found															=	trim($array[1] ?? '');
        $found															=	str_replace('\'', '', $found);
        $server															=	str_replace(';', '', $found);

        // find and set user name
        $config_file													=	"/var/www/html/wrightetmathon/application/config/database.php";
        $found_flag														=	'N';
        $search															=	'$db[\'default\'][\'username\']';
        $config															=	fopen($config_file, "r");
        if (!$config) {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
        
        while (!feof($config) and $found_flag == 'N') {
            $line														=	fgets($config);
            if (strpos($line, $search) !== false) {
                $found_flag												=	'Y';
            }
        }
        fclose($config);
        if ($found_flag == 'N') {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
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
        if (!$config) {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
        while (!feof($config) and $found_flag == 'N') {
            $line														=	fgets($config);
            if (strpos($line, $search) !== false) {
                $found_flag												=	'Y';
            }
        }
        fclose($config);
        if ($found_flag == 'N') {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
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
        if (!$config) {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
        while (!feof($config) and $found_flag == 'N') {
            $line														=	fgets($config);
            if (strpos($line, $search) !== false) {
                $found_flag												=	'Y';
            }
        }
        fclose($config);
        if ($found_flag == 'N') {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
        $array															=	array();
        $array															=	explode('=', $line);
        $found															=	trim($array[1]);
        $found															=	str_replace('\'', '', $found);
        $database														=	str_replace(';', '', $found);
        
        // load return array
        $conn_parms														=	array(
                                                                                    'server'	=>	$server,
                                                                                    'user'		=>	$user,
                                                                                    'password'	=>	$password,
                                                                                    'database'	=>	$database
                                                                                    );
        return $conn_parms;
    }
    
    public function open_db($conn_parms)
    {
        // Create connection to DB
        $conn 															=	new mysqli($conn_parms['server'], $conn_parms['user'], $conn_parms['password'], $conn_parms['database']);
        
        // return connection info
        return $conn;
    }
    
    public function backup_db($conn_parms, $now)
    {
        $retval															=	false;
        $dbhost															=	$conn_parms['server'];
        $dbuser															=	$conn_parms['user'];
        $dbpass															=	$conn_parms['password'];
        $database														=	$conn_parms['database'];
        $backup_path													=	"/var/www/html/wrightetmathon_backup/";
        if (!file_exists($backup_path)) {
            mkdir($backup_path);
        }
        $backup_file													=	"backup_".$database."_".$now;
        $command = "mysqldump --host=$dbhost --user=$dbuser --password='$dbpass' -B $database | gzip > '$backup_path$backup_file'";
        system($command, $retval);
        return $retval;
    }
    
    public function get($sql, $conn, $select_column)
    {
        $return_value													=	null;
        $result															=	$conn->query($sql);
        if ($result->num_rows == 1) {
            while ($row = mysqli_fetch_assoc($result)) {
                $return_value											=	$row[$select_column];
            }
        }
        return	$return_value;
    }
    
    public function report_file($conn, $now)
    {
        $report_file													=	null;
        
        // now get report save file
        $select_column													=	'value';
        $sql 															=	"SELECT `value` FROM `ospos_app_config` WHERE `key` = 'SPfilename'";
        $SPfilename														=	$this->get($sql, $conn, $select_column);
        if ($SPfilename == null) {
            $SPfilename="Item_import_report.csv";
        }	//	no report save file, so set it
        
        // now get report save path
        $select_column													=	'value';
        $sql 															=	"SELECT `value` FROM `ospos_app_config` WHERE `key` = 'SPsavepath'";
        $SPsavepath														=	$this->get($sql, $conn, $select_column);
        if ($SPsavepath == null) {
            $SPsavepath="/var/www/html/";
        }			//	no report save path, so set_it
        
        // set output report CSV name
        $pieces															=	explode(".", $SPfilename);
        $report_file 													=	$SPsavepath.$pieces[0].'_update_'.$now.'.csv';

        return $report_file;
    }
    
    public function write_headers($report_file)
    {
        $this->report_file_path = $report_file;
        $this->report_buffer .= "Action;Message;\n";
    }
    
    public function write_report($report_file, $type, $message, $conn_error=null)
    {
        $this->report_file_path = $report_file;
        $this->report_buffer .= $type . ';' . $message . ';' . $conn_error . ";\n";
    }

    public function flush_report()
    {
        if ($this->report_buffer !== '' && $this->report_file_path !== null) {
            file_put_contents($this->report_file_path, $this->report_buffer, FILE_APPEND);
            $this->report_buffer = '';
        }
    }

    public function write_import_progress($step, $message, $current = 0, $total = 0)
    {
        if (!$this->_progress_id) {
            return;
        }
        $data = array(
            'step'    => $step,
            'message' => $message,
            'current' => (int)$current,
            'total'   => (int)$total,
            'percent' => ($total > 0) ? round(($current / $total) * 100) : 0,
            'time'    => time()
        );
        $file = '/tmp/import_progress_' . $this->_progress_id . '.json';
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    public function get_progress()
    {
        // release session lock immediately so we don't block
        session_write_close();

        $progress_id = isset($_GET['id']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['id']) : '';

        header('Content-Type: application/json');

        if (!$progress_id) {
            echo json_encode(array('step' => 'error', 'message' => 'ID manquant', 'percent' => 0));
            exit;
        }

        $file = '/tmp/import_progress_' . $progress_id . '.json';
        if (file_exists($file)) {
            readfile($file);
        } else {
            echo json_encode(array('step' => 'waiting', 'message' => 'En attente...', 'current' => 0, 'total' => 0, 'percent' => 0));
        }
        exit;
    }

    public function run_import()
    {
        // set error handler to catch fatal errors only (not deprecation warnings)
        set_error_handler(function($severity, $message, $file, $line) {
            // ignore deprecation warnings and notices
            if ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED || $severity === E_NOTICE || $severity === E_USER_NOTICE || $severity === E_WARNING) {
                return false; // let PHP handle it normally
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            // read parameters from POST (passed from progress page)
            // This avoids CI session validation issues with AJAX requests
            $import_items  = !empty($_POST['import_items']) && $_POST['import_items'] != '0';
            $import_kits   = !empty($_POST['import_kits']) && $_POST['import_kits'] != '0';
            $progress_id   = isset($_POST['progress_id']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_POST['progress_id']) : '';

            if (!$progress_id || (!$import_items && !$import_kits)) {
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'error', 'message' => 'Aucun import en attente'));
                exit;
            }

        // store progress_id as property so import functions can use it
        $this->_progress_id = $progress_id;

        // Clean up session flags using CI session BEFORE releasing lock
        // This prevents the need to reopen the session later (which causes CI session validation conflicts)
        $this->session->unset_userdata('pending_import_items');
        $this->session->unset_userdata('pending_import_kits');
        $this->session->unset_userdata('import_progress_id');
        $this->session->set_userdata('error_code', '05570');

        // release session lock so polling endpoint can respond
        session_write_close();

        // increase limits
        ini_set('memory_limit', '2000M');
        ini_set('max_execution_time', '1200');

        // clean up old progress files (older than 1 hour)
        foreach (glob('/tmp/import_progress_*.json') as $f) {
            if (filemtime($f) < time() - 3600) {
                @unlink($f);
            }
        }

        $this->write_import_progress('init', 'Initialisation...', 0, 0);

        // run imports
        if ($import_items) {
            $this->manage_items_automatic();
            $this->manage_items_kit_automatic();
        } else if ($import_kits) {
            $this->manage_items_kit_automatic();
        }

        $this->write_import_progress('done', 'Import termine', 0, 0);

        // destroy temp pid file (no need to reopen session - flags already cleaned above)
        @array_map('unlink', glob("/home/wrightetmathon/.app_running.txt"));

        header('Content-Type: application/json');
        echo json_encode(array('status' => 'done'));
        restore_error_handler();
        exit;

        } catch (Exception $e) {
            restore_error_handler();
            $this->write_import_progress('error', $e->getMessage(), 0, 0);
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()));
            exit;
        }
    }

    public function get_column_index($search_field)
    {
        // now find field_index in import data model to get column index
        $data_model_key 												=	array_search($search_field, array_column($_SESSION['data_model'], 'column_database_field_name'));

        // now get column number from the right index for this field
        $col_index														=	$_SESSION['data_model'][$data_model_key]->column_number;
        
        return $col_index;
    }
    
    public function redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file)
    {
        // commit any pending transaction before closing
        @$conn->commit();
        @$conn->autocommit(true);

        // close the data connections
        $conn->close();
        unset($objPHPExcel);
        unset($objReader);
        unset($_SESSION['import_mode']);
        
        // Output process finished
        $message														=	'MAJ BASE ARTCLES TERMINE => '.date('Y-m-d H:i:s').' => '.$infile;
        $this															->	write_report($report_file, 'SUCCES', $message);
        $this															->	flush_report();

        // re-set values
        ini_set('memory_limit', $memory_limit);
        ini_set('max_execution_time', $max_execution_time);
        
        // set message
        $_SESSION['error_code']											=	'05570';
    }
    
    // update the pos software
    public function software_update()
    {
        // sync the system software
        require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_software_sync.php";
        
        // return to login.
        return;
    }
    
    // update the version
    public function software_version()
    {
        // initialise
        $version_found_flag												=	'N';
        $version_file													=	"/var/www/html/wrightetmathon/version.ini";
        
        // open the file
        $verfile 														=	fopen($version_file, "r");

        // if file opened successfully
        if ($verfile) {
            // read the file line by line and search for 'version' keyword
            while (!feof($verfile) and $version_found_flag == 'N') {
                $line														=	fgets($verfile);
                if (strpos($line, 'version') !== false) {
                    $version_array											=	array();
                    $version_array											=	explode('=', $line);
                    $version_found_flag										=	'Y';
                }
            }
            
            // close the file
            fclose($verfile);

            // if keyword version found
            if ($version_found_flag == 'Y') {
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
    public function branches()
    {
        // initialise
        $from_dir														=	"/home/wrightetmathon/.hidrive.sonrisa/SONRISA_CENTRAL/BRANCHES/";
        $input_file_name												=	"branches.ini";
        $user															=	"admin";
        $password														=	"Son@Risa&11";
        
        // truncate the existing table
        // this ensures that any branches that are in the local table but not in branches.ini will be removed
        //$this															->	Branch->truncate();

        return; //Arrêt temporaire de la mise à jour des sucursales
        
        // read the branches file
        // this file is in sections
        // each branch has its own section starting with [START] and ending in [END]
        // so read file until the first [START], load data to variables for this branch until [END] reached, then update/insert branch file.
        
        // open the file, return if not opened
        $branches 														=	fopen("$from_dir$input_file_name", "r");
        if (! $branches) {
            return;
        }
        
        // read the file
        while (!feof($branches)) {
            // get a line
            $line														=	fgets($branches);
            
            // test line for [START]
            if (strpos($line, '[START]') !== false) {
                // now load the parameters until [END] is reached
                while (strpos($line, '[END]') === false) {
                    // read a line
                    $line												=	fgets($branches);
                    
                    // explode the line to get the parameter and its value; array will have two elements
                    $array												=	array();
                    $array												=	explode('=', $line);
                    
                    // trim array[1]
                    $value												=	trim($array[1]);
                    
                    // find out what I have and save it
                    switch ($array[0]) {
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
                if ($exists == 1) {
                    // update
                    $_SESSION['transaction_id']							=	$_SESSION['transaction_info']->branch_code;
                    $_SESSION['new']									=	0;
                } else {
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



    public function slides_ventes()
    {
        $arc = shell_exec("cp -r /home/wrightetmathon/.hidrive.sonrisa/SLIDES /var/www/html/wrightetmathon/");
        $arc = shell_exec("cp -r /home/wrightetmathon/.hidrive.sonrisa/SLIDES_VENTES /var/www/html/wrightetmathon/");

        // Store sync timestamp for cache-busting
        $_SESSION['slides_sync_time'] = time();

        return;
    }
    // show flash_info
    public function flash_info()
    {
        // initialise
        unset($_SESSION['flash_info_show']);
        
        // calculate file hash for the flash_info_publish_me.pdf file.
        $flash_info_hash												=	hash_file('md5', "/home/wrightetmathon/.hidrive.sonrisa/FLASH_INFO/flash_info_publish_me.pdf");
        // compare to existing hash
        if ($flash_info_hash != $this->config->item('flash_info_hash')) {
            // not the same, so the flash info file has changed
            // update the flash_info_hash
            $update_success												=	$this->Appconfig->save('flash_info_hash', $flash_info_hash);
            if (!$update_success) {
                // do nothing for the moment
            }
            // update the flash_info_count
            $update_success												=	$this->Appconfig->save('flash_info_count', 0);
            if (!$update_success) {
                // do nothing for the moment
            }
            // copy the document to local PC
            copy("/home/wrightetmathon/.hidrive.sonrisa/FLASH_INFO/flash_info_publish_me.pdf", "/var/www/html/wrightetmathon/flash_info_publish_me.pdf");
        }
        
        // show the document if count less than displays
        if ($this->config->item('flash_info_count') <= $this->config->item('flash_info_displays')) {
            // increment the count
            $count														=	$this->config->item('flash_info_count') + 1;
            // and save it
            $update_success												=	$this->Appconfig->save('flash_info_count', $count);
            if (!$update_success) {
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
    public function database_configuration()
    {
        // 1) check transaction_type table
        $this															->	Transaction->transaction_type();
        // 2) check transaction_multiplier
        $this															->	Transaction->transaction_multiplier();

        return;
    }


    public function db_config()
    {
        //clearstatcache();
        // update config
        $this															->	Transaction->update_config();
        return;
    }
    
    ///////////////////////////////////
    //load and update quantity items

    public function manage_items_stock_inventory_xls_verify()
    {
        //$this->manage_items_stock_inventory_xls();
        // get existing PHP ini values
        $memory_limit													=	ini_get('memory_limit');
        $max_execution_time												=	ini_get('max_execution_time');
        
        // set ini values to avoid out of memory
        ini_set('memory_limit', '2000M');
        ini_set('max_execution_time', '1000');

        $from_dir                = $_SESSION['upload_path'];
        $input_file_name         = $_SESSION['upload_file'];
        $upload_quantity         = $_SESSION['upload_quantity'];

        $infile			         = $from_dir.$input_file_name;
        $now			         = date('Y-m-d H:i:s');

        $number_of_records		 = 0;
        $number_of_updates		 = 0;
        $number_of_adds			 = 0;
        $number_of_excludes		 = 0;
        $number_of_errors		 = 0;
        $number_of_unchanged	 = 0;

        $n = array();
        $n['empty']     = 0;
        $n['not_empty'] = 0;
        $n['update']    = 0;
        $n['pb_line']   = array();
        

        $search_for_uppercase	 = array('À', 'Â', 'Ä', 'È', 'É', 'Ê', 'Ë', 'Î', 'Ï', 'Ô', 'Œ', 'Ù', 'Û', 'Ü', 'Ÿ');
        $replace_by_uppercase	 = array('A', 'A', 'A', 'E', 'E', 'E', 'E', 'I', 'I', 'O', 'OE', 'U', 'U', 'U', 'Y');
        $search_for_lowercase	 = array('à', 'â', 'ä', 'è', 'é', 'ê', 'ë', 'î', 'ï', 'ô', 'œ', 'ù', 'û', 'ü', 'ÿ');
        $replace_by_lowercase	 = array('a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'oe', 'u', 'u', 'u', 'y');
        $search_for_specials	 = array('Ç', 'ç', '«', '»', '€', ',', '#', '$', '°', '§', '^', '`', '"', ';', ':');
        $replace_by_specials	 = array('C', 'c', '"', '"', '€', ' ', '#', '$', ' ', ' ', ' ', ' ', '"', ' ', ' ');
        $search_for_winker		 = array("'");
        $replace_by_winker		 = array(" ");
        $data_rows				 = array();

        require_once "/var/www/html/wrightetmathon/application/third_party/phpexcel/Classes/PHPExcel.php";
        require_once "/var/www/html/wrightetmathon/application/third_party/phpexcel/Classes/PHPExcel/IOFactory.php";

        // get input data
        // determine the filetype

        $filetype 				 = PHPExcel_IOFactory::identify($infile);

        $message				 = 'TYPE DE FICHIER => '.$filetype;
        $this					 ->write_report($report_file, 'SUCCES', $message);
    
        // create the reader
        $objReader				 = new stdClass();
        $objReader				 = PHPExcel_IOFactory::createReader($filetype);
        //$objReader				 ->setReadDataOnly(true);
        //$message				 = 'LECTURE OBJET CREE => '.$objReader;
        //$this					 ->write_report($report_file, 'SUCCES', $message);
    
        // Read the infile
        $objPHPExcel			 = new PHPExcel();
        $objPHPExcel			 = $objReader->load($infile);


        // get sheet
        try {
            $sheet               = $objPHPExcel->getSheet($objPHPExcel->getActiveSheetIndex());
        } catch (Exception $e) {
            $message			 = 'FEUILLE NON LU=> '.$e->getMessage().' => Mise à jour interrompue';
            $this				 ->write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
        // get sheet dimensions
        $highestRow				 = $sheet->getHighestRow();
        $highestColumn			 = $sheet->getHighestColumn();
        $message				 = 'DIMENSIONS EXCEL => '.$highestRow.' => LIGNES => '.$highestColumn.' COLONNES';
        $this					 ->write_report($report_file, 'SUCCES', $message);

        //  Loop through each row of the worksheet in turn starting with line 2 (to ignore titles)
        for ($row = 2; $row <= $highestRow; $row++) {
            //  Read a row of data into an array
            $data_row													=	array();
            $data_row 													=	$sheet->rangeToArray('A'.$row.':'.$highestColumn.$row, null, true, false, false);

            // initialise
            $item_item_number = $data_row[0][0];
            $item_name = $data_row[0][1];
            $item_quantity = $data_row[0][2];
            
            if (($upload_quantity == "1") && ($item_quantity == '')) {
                $item_quantity = 0;
            }

            if (($item_quantity != 0) && (empty(intval($item_quantity)))) {
                $n['empty'] += 1;
                $n['pb_line'][] = $item_item_number.' :'.$item_name;
            } else {
                $n['not_empty'] += 1;
                if ($this->Item->exists_with_item_number($item_item_number) && isset($item_name)) {
                    $n['update'] += 1;
                } else {
                    $n['pb_line'][] = $item_item_number.' :'.$item_name;
                }
            }
        }
        $n['total'] = $n['empty'] + $n['not_empty'];
        $_SESSION['n_up'] = $n;
    }

    public function manage_items_stock_inventory_xls()
    {
        // get existing PHP ini values
        $memory_limit													=	ini_get('memory_limit');
        $max_execution_time												=	ini_get('max_execution_time');
        
        // set ini values to avoid out of memory
        ini_set('memory_limit', '2000M');
        ini_set('max_execution_time', '1000');
        
        // initialise
        if (isset($_SESSION['import_mode']) && $_SESSION['import_mode'] == 'manual') {
            $from_dir													=	$_SESSION['upload_path'];
            $input_file_name											=	$_SESSION['upload_file'];
            $upload_quantity                                            =   $_SESSION['upload_quantity'];
            $redirect													=	'reports';
        } else {
            $from_dir													=	"/home/wrightetmathon/articles_local/";
            $input_file_name											=	"BASE_ARTICLE.xls";
            $input_file_name_xlsx										=	"BASE_ARTICLE.xlsx";
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

        // get connection parameters and open connection
        $conn_parms														=	array();
        $conn_parms														=	$this->get_conn_parms($redirect);
        $conn															=	$this->open_db($conn_parms);
        if (!$conn) {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);							//	connection failed, so exit. This is fatal.
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
        // Setup report file
        $report_file													=	$this->report_file($conn, $now);
        $this															->	write_headers($report_file);
        
        // Output process started
        $message														=	'MAJ ARTCLES COMMENCE => '.$now.' => '.$infile;
        $message														=	'MAJ QUANTITÉ COMMENCE => '.$now.' => '.$infile;
        
        $this															->	write_report($report_file, 'SUCCES', $message);

        // take a backup of the database
        $retval															=	$this->backup_db($conn_parms, $now);

        if ($retval != 0) {
            $message													=	'BACKUP NON EFFECTUE => '.$conn_parms['database'];
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
    
        // get default codes
        // get branch code
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'branch_code'";
        $branch_code													=	$this->get($sql, $conn, $select_column);
        if ($branch_code == null) {										//	no default admin, so exit
            $message													=	'CODE SURCURRASALE NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'CODE SURCURRASALE => '.$branch_code;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        
        
        // get admin name
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'admin_user_name'";
        $admin_user_name												=	$this->get($sql, $conn, $select_column);
        if ($admin_user_name == null) {									//	no default admin, so exit
            $message													=	'UITISATUER ADMIN NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'UTILISATEUR ADMIN PAR DEFAUT => '.$admin_user_name;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // now get admin employee number
        $select_column													=	'person_id';
        $sql 															=	"SELECT $select_column FROM `ospos_employees` WHERE `username` = '".$admin_user_name."' AND `branch_code` = '".$branch_code."'";
        $admin_employee_id												=	$this->get($sql, $conn, $select_column);
        if ($admin_employee_id == null) {									//	no admin employee id, so exit
            $message													=	'ID EMPLOYE POUR UTILISATUER ADMIN NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'ID EMPLOYE UTILISATEUR ADMIN PAR DEFAUT => '.$admin_employee_id;
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
        //$objReader														->	setReadDataOnly(true);
        //$message														=	'LECTURE OBJET CREE => '.$objReader;
        //$this															->	write_report($report_file, 'SUCCES', $message);
    
        // Read the infile
        $objPHPExcel													=	new PHPExcel();
        $objPHPExcel													=	$objReader->load($infile);
        
        if (!$objPHPExcel) {
            $message													=	'PHP EXCEL OBJECT PAS CREE => '.$infile;
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'PHP EXCEL OBJECT CREE => '.$infile.' => DERNIERE MODIFICATION DATE = '.$objPHPExcel->getProperties()->getModified();
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // no need to do anything if the file has not been changed
        if ($objPHPExcel->getProperties()->getModified() == $this->config->item('import_items_last_modified_date')) {
            $message													=	'RIEN A FAIRE - FICHIER BASE_ARTICLE.xls PAS CHANGE';
            $this														->	write_report($report_file, 'SUCCES', $message);

            $message													=	'MAJ BASE ARTCLES TERMINE => '.date('Y-m-d H:i:s');
            $this														->	write_report($report_file, 'SUCCES', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
                
        // get sheet
        try {
            $sheet														=	$objPHPExcel->getSheet($objPHPExcel->getActiveSheetIndex());
        } catch (Exception $e) {
            $message													=	'FEUILLE NON LU=> '.$e->getMessage().' => Mise à jour interrompue';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }
        
        if (!$sheet) {
            $message													=	'PHP EXCEL FEUILLE NON LU => '.$infile;
            $this														->	write_report($report_file, 'ERREUR', $message);
        } else {
            $message													=	'PHP EXCEL FEUILLE LU => '.$infile.' => Index feuille => '.$objPHPExcel->getActiveSheetIndex();
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
        
        // get sheet dimensions
        $highestRow														=	$sheet->getHighestRow();
        $highestColumn													=	$sheet->getHighestColumn();
        $message														=	'DIMENSIONS EXCEL => '.$highestRow.' => LIGNES => '.$highestColumn.' COLONNES';
        $this															->	write_report($report_file, 'SUCCES', $message);
        
        //  Loop through each row of the worksheet in turn starting with line 2 (to ignore titles)
        for ($row = 2; $row <= $highestRow; $row++) {
            //  Read a row of data into an array
            $data_row													=	array();
            $data_row 													=	$sheet->rangeToArray('A'.$row.':'.$highestColumn.$row, null, true, false, false);

            // initialise
            $item_item_number = $data_row[0][0];
            $item_name = $data_row[0][1];
            $item_quantity = $data_row[0][2];

            if (($upload_quantity == "1") && ($item_quantity == '')) {
                $item_quantity = 0;
            }
            if (isset($item_quantity)) {
                
                // count records read
                $number_of_records											=	$number_of_records + 1;
                        
                // get current item id by item nummber
                $sql 				=	"SELECT * FROM `ospos_items` WHERE `item_number` = '".$item_item_number."' AND `branch_code` = '".$branch_code."'";
                $result 			=	$conn->query($sql);
    
                // was item found?
                if ($result->num_rows > 0) {
                    // item record was found so load current item data
                    $item_data 												=	$result->fetch_assoc();
                    
                    $sql_update = "UPDATE `ospos_items` SET `quantity` = " . $item_quantity . " WHERE `item_id` = ". $item_data['item_id'] ."";

                    // do update and test result
                    if ($conn->query($sql_update) === false) {
                        // if fail - output error message to report
                        $message										=	'ERREUR MAJ OSPOS_ITEMS => '.$data_row[0][0].' => '.$data_row[0][1];
                        $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
                        $number_of_errors								=	$number_of_errors + 1;
                    } else {
                        // update was successful
                        $number_of_updates								=	$number_of_updates + 1;
                        
                        // output to file
                        $message										=	'SUCCESS MAJ OSPOS_ITEMS => '.$data_row[0][0].' => '.$data_row[0][1];
                        $this											->	write_report($report_file, 'SUCCES', $message);
                    
                        // add inventory record
                        // set correct field values for inventory record file
                        $trans_comment									=	'Inventaire comptable';
                        $trans_inventory = $item_quantity - $item_data['quantity'];
                        // create sql
                        $sql	=	"INSERT INTO `ospos_inventory`"
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
                                .	$_SESSION['G']->login_employee_id.","			// trans_user
                                .	"'".$now."',"					// trans_date
                                .	"'".$trans_comment."',"			// trans_comment
                                .	$item_data['quantity'].","		// trans_stock_before
                                .	$trans_inventory.","							// trans_inventory
                                .	$item_quantity.","		// trans_stock_after
                                .	"'".$branch_code."'"			// branch_code
                                .	")";
                        //				    		$result = $conn->query($sql);
                        //				    		$item_data = $result->fetch_assoc();
                        // insert inventory record
                        if ($conn->query($sql) === false) {
                            // if fail - output error message to report
                            $message									=	'ERREUR INSERT OSPOS_INVENTORY => '.$data_row[0][0].' => '.$data_row[0][1];
                            $this										->	write_report($report_file, 'ERREUR', $message, $conn->error);
                        }
                    }
                

                    if ($item_data['DynamicKit'] == 'Y') {
                        $sql_kit_items = $this->Item_kit->get_item_kit_items($item_data['item_id']);

                        foreach ($sql_kit_items as $key => $item) {
                            $quantity_set = intval($item['quantity']) * intval($item_quantity);
                            $kit_item_data = "SELECT * FROM `ospos_items` WHERE `item_id` = '".$item['quantity']."' AND `branch_code` = '".$branch_code."'";
                            $result_kit_item_data = $conn->query($kit_item_data);
                            
                            $kit_item = $result_kit_item_data->fetch_assoc();
                            $sql_update_kit = "UPDATE `ospos_items` SET `quantity` = " . $quantity_set . " WHERE `item_id` = ". $item['item_id'] ."";
                             
                            // do update and test result
                            if ($conn->query($sql_update_kit) === false) {
                                // if fail - output error message to report
                                $message			=	'ERREUR MAJ OSPOS_ITEMS => '.$data_row[0][0].' => '.$data_row[0][1];
                                $this				->	write_report($report_file, 'ERREUR', $message, $conn->error);
                                $number_of_errors	=	$number_of_errors + 1;
                            } else {
                                // update was successful
                                $number_of_updates = $number_of_updates + 1;
                                
                                // output to file
                                $message = 'SUCCESS MAJ OSPOS_ITEMS => '.$data_row[0][0].' => '.$data_row[0][1];
                                $this	 ->	write_report($report_file, 'SUCCES', $message);
                                // add inventory record
                                // set correct field values for inventory record file
                                $trans_comment = 'Inventaire comptable';
                                $trans_inventory = $quantity_set - $kit_item['quantity'];
                                // create sql
                                $sql	=	"INSERT INTO `ospos_inventory`"
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
                                    .	$item['item_id'].","		// trans_items
                                    .	$_SESSION['G']->login_employee_id.","			// trans_user
                                    .	"'".$now."',"					// trans_date
                                    .	"'".$trans_comment."',"			// trans_comment
                                    .	$kit_item['quantity'].","		// trans_stock_before
                                    .	$trans_inventory.","							// trans_inventory
                                    .	$quantity_set.","		// trans_stock_after
                                    .	"'".$branch_code."'"			// branch_code
                                    .	")";
                                    
                                // insert inventory record
                                if ($conn->query($sql) === false) {
                                    // if fail - output error message to report
                                    $message = 'ERREUR INSERT OSPOS_INVENTORY => '.$data_row[0][0].' => '.$data_row[0][1];
                                    $this	 ->	write_report($report_file, 'ERREUR', $message, $conn->error);
                                }
                            }
                        }
                    }
                } else {
                    $number_of_unchanged								=	$number_of_unchanged	+	1;
                }
            }
        }
        


        // create update sql2
        $sql2 												=	"UPDATE `ospos_items` SET	`deleted` = 0 WHERE `deleted`= 1 AND `quantity` > 0 ";

        // do update and test result
        if ($conn->query($sql2) === false) {
            // if fail - output error message to report
            $message										=	'ERREUR REACTIVATION d\'articles au stock positif  '.$data_row[0][0].' => '.$data_row[0][1];
            $this											->	write_report($report_file, 'ERREUR', $message, $conn->error);
            $number_of_errors								=	$number_of_errors + 1;
        } else {
            // output to file
            $message										=	'SUCCESS REACTIVATION d\'articles au stock positif => '.$data_row[0][0].' => '.$data_row[0][1];
            $this											->	write_report($report_file, 'SUCCES', $message);
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
        
        array_map('unlink', glob("/var/www/html/wrightetmathon_uploads/*"));
        
        unset($_SESSION['show_dialog']);
        redirect("reports/inventory_rolling");
        //redirect('receivings');
    }



    public function manage_targets()
    {
        //file
        //PK kit        SO item        quantity

        // get existing PHP ini values
        $memory_limit				 = ini_get('memory_limit');
        $max_execution_time			 = ini_get('max_execution_time');
        
        // set ini values to avoid out of memory
        ini_set('memory_limit', '2000M');
        ini_set('max_execution_time', '1000');
        
        
        $from_dir				= "/home/wrightetmathon/.hidrive.sonrisa/SONRISA_CENTRAL/OBJECTIFS/";
        $input_file_name		= "OBJECTIFS.xlsx";
        $update_sales_price		= 'N';
        $create					= 'Y';
        $redirect				= 'return';
        
        $infile															= $from_dir.$input_file_name;
        $now															= date('Y-m-d H:i:s');
        
        $number_of_records		 = 0;
        $number_of_updates		 = 0;
        $number_of_adds			 = 0;
        $number_of_excludes		 = 0;
        $number_of_errors		 = 0;
        $number_of_unchanged	 = 0;
        
        $search_for_uppercase	 = array('À', 'Â', 'Ä', 'È', 'É', 'Ê', 'Ë', 'Î', 'Ï', 'Ô', 'Œ', 'Ù', 'Û', 'Ü', 'Ÿ');
        $replace_by_uppercase	 = array('A', 'A', 'A', 'E', 'E', 'E', 'E', 'I', 'I', 'O', 'OE', 'U', 'U', 'U', 'Y');
        
        $search_for_lowercase	 = array('à', 'â', 'ä', 'è', 'é', 'ê', 'ë', 'î', 'ï', 'ô', 'œ', 'ù', 'û', 'ü', 'ÿ');
        $replace_by_lowercase	 = array('a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'oe', 'u', 'u', 'u', 'y');
        
        $search_for_specials	 = array('Ç', 'ç', '«', '»', '€', ',', '#', '$', '°', '§', '^', '`', '"', ';', ':');
        $replace_by_specials	 = array('C', 'c', '"', '"', '€', ' ', '#', '$', ' ', ' ', ' ', ' ', '"', ' ', ' ');
        
        $search_for_winker		 = array("'");
        $replace_by_winker		 = array(" ");

        $data_rows				 = array();
        $objPHPExcel			 = null;
        $objReader				 = null;

        require_once "/var/www/html/wrightetmathon/application/third_party/phpexcel/Classes/PHPExcel.php";
        require_once "/var/www/html/wrightetmathon/application/third_party/phpexcel/Classes/PHPExcel/IOFactory.php";

        //$_SESSION['data_model']											= $this->Import->get_all()->result();

        // get connection parameters and open connection
        $conn_parms				 = array();
        $conn_parms				 = $this->get_conn_parms($redirect);
        $conn					 = $this->open_db($conn_parms);
        if (!$conn) {
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);							//	connection failed, so exit. This is fatal.
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }

        // Setup report file
        $report_file													=	$this->report_file($conn, $now);
        $this															->	write_headers($report_file);
        
        // Output process started
        $message														=	'MAJ OBJECTIFS => '.$now.' => '.$infile;
        $this															->	write_report($report_file, 'SUCCES', $message);
        
        // make sure input file exists, if not exit
        
        if (!file_exists($from_dir.$input_file_name)) {
            $message													=	'OBJECTIFS NON TROUVE => '.$from_dir.$input_file_name;
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);										// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $infile			=	$from_dir.$input_file_name;
        }
        
        
        // get default codes
        // get branch code
        $select_column													=	'value';
        $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'branch_code'";
        $branch_code													=	$this->get($sql, $conn, $select_column);
        if ($branch_code == null) {										//	no default admin, so exit
            $message													=	'CODE SURCURRASALE NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'CODE SURCURRASALE => '.$branch_code;
            $this														->	write_report($report_file, 'SUCCES', $message);
        }


        exec('/var/www/html/wrightetmathon/application/controllers/whoami.sh sonrisa_' .$branch_code );


         // get admin name
		 $select_column													=	'value';
		 $sql 															=	"SELECT $select_column FROM `ospos_app_config` WHERE `key` = 'admin_user_name'";
		 $admin_user_name												=	$this->get($sql, $conn, $select_column);
		 if ($admin_user_name == null) {									//	no default admin, so exit
			 $message													=	'UITISATUER ADMIN NON TROUVE => ';
			 $this														->	write_report($report_file, 'ERREUR', $message);
			 $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
			 if ($redirect == 'return') {
				 return;
			 } else {
				 redirect($redirect);
			 }
		 } else {
			 $message													=	'UTILISATEUR ADMIN PAR DEFAUT => '.$admin_user_name;
			 $this														->	write_report($report_file, 'SUCCES', $message);
		 }

		// now get admin employee number
        $select_column													=	'person_id';
        $sql 															=	"SELECT $select_column FROM `ospos_employees` WHERE `username` = '".$admin_user_name."' AND `branch_code` = '".$branch_code."'";
        $admin_employee_id												=	$this->get($sql, $conn, $select_column);
        if ($admin_employee_id == null) {									//	no admin employee id, so exit
            $message													=	'ID EMPLOYE POUR UTILISATUER ADMIN NON TROUVE => ';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);									// fatal error
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'ID EMPLOYE UTILISATEUR ADMIN PAR DEFAUT => '.$admin_employee_id;
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
        //$objReader														->	setReadDataOnly(true);
        //$message														=	'LECTURE OBJET CREE => '.$objReader;
        $this															->	write_report($report_file, 'SUCCES', $message);
        
        // Read the infile
        $objPHPExcel													=	new PHPExcel();
        $objPHPExcel													=	$objReader->load($infile);

        if (!$objPHPExcel) {
            $message													=	'PHP EXCEL OBJECT PAS CREE => '.$infile;
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        } else {
            $message													=	'PHP EXCEL OBJECT CREE => '.$infile.' => DERNIERE MODIFICATION DATE = '.$objPHPExcel->getProperties()->getModified();
            $this														->	write_report($report_file, 'SUCCES', $message);
        }
       
                
        // get sheet
        try {
            $sheet														=	$objPHPExcel->getSheet($objPHPExcel->getActiveSheetIndex());
        } catch (Exception $e) {
            $message													=	'FEUILLE NON LU=> '.$e->getMessage().' => Mise à jour interrompue';
            $this														->	write_report($report_file, 'ERREUR', $message);
            $this->redirect($redirect, $memory_limit, $max_execution_time, $conn, $objPHPExcel, $objReader, $infile, $report_file);
            if ($redirect == 'return') {
                return;
            } else {
                redirect($redirect);
            }
        }

        if (!$sheet) {
            $message													=	'PHP EXCEL FEUILLE NON LU => '.$infile;
            $this														->	write_report($report_file, 'ERREUR', $message);
        } else {
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
        foreach ($_SESSION['data_model'] as $column) {
            $col_indexes[$column->column_database_field_name]			=	$this->get_column_index($column->column_database_field_name);
        }

        //  Loop through each row of the worksheet in turn starting with line 2 (to ignore titles)
        for ($row = 2; $row <= $highestRow; $row++) {
            //  Read a row of data into an array
            $data_row													=	array();
            $data_row 													=	$sheet->rangeToArray('A'.$row.':'.$highestColumn.$row, null, true, false, false);

            $target_year= $data_row[0][0];
            $target_month = $data_row[0][1];
            $target_day = $data_row[0][2];
			$target_opendays = $data_row[0][3];
			$target_amount = $data_row[0][4];
			$target_branchcode = $data_row[0][5];
    

            // initialise
            $item_data													=	array();
            $item_supp													=	array();
            $item_pric													=	array();
                
            // count records read
            $number_of_records											=	$number_of_records + 1;

            // get current item id by item nummber
			if ($target_branchcode ==$branch_code) {
				$sql 	 = "SELECT * FROM `ospos_sales_targets` WHERE `target_year` = '".$target_year."' AND `target_month` = '".$target_month."' AND `target_day` = '".$target_day."' AND `branch_code` = '".$branch_code."'";
            	$result  = $conn->query($sql);

            	// was item found?
                if ($result->num_rows > 0) 
				{
					// no record found add one
                    // insert item_kits if not exist
                    $sql = "UPDATE `ospos_sales_targets`"
                   .	" set "
                   .	"`target_shop_open_days`=".$target_opendays
                   .	" ,`target_shop_turnover`=".$target_amount
				   .	" ,`person_id`=".$admin_employee_id
                   .	" WHERE "
				   .	"`target_year` =".$target_year." AND "
                   .    "`target_month` =".$target_month." AND "
                   .	"`target_day` =".$target_day." AND "
				   .	"`branch_code` = '".$target_branchcode."'";
            
                    // update the record
                    if ($conn->query($sql) === false) {
            			$message = 'ERREUR UPDATE OSPOS_SALES_TARGETS => '.$target_year.' => '.$target_month;
    					$this->write_report($report_file, 'ERREUR', $message, $conn->error);
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
        
                    // item record was found so load current item data
                    $item_data 		= $result->fetch_assoc();
                }
                else
				{
                    // no record found add one
                    // insert item_kits if not exist
                    $sql = "INSERT INTO `ospos_sales_targets`"
                   .	"(`target_year`,"
                   .    "`target_month`,"
                   .	"`target_day`,"
                   .	"`target_shop_open_days`,"
                   .	"`target_shop_turnover`,"
				   .	"`person_id`,"
                   .	"`branch_code`)" 
                   .	"VALUES"
                   .	"(".$target_year.","			        
                   .	"'" . $target_month."',"	
                   .    "'" . $target_day."',"     
                   .	"'" . $target_opendays."',"
				   .	"'" . $target_amount."',"
				   .	"'" . $admin_employee_id."',"
                   .	"'".$branch_code."'"		
                   .	")";
            
                    // insert the record
                    if ($conn->query($sql) === false) {
            			$message = 'ERREUR INSERTION OSPOS_SALES_TARGETS => '.$target_year.' => '.$target_month;
    					$this->write_report($report_file, 'ERREUR', $message, $conn->error);
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
                    $message = 'SUCCESS INSERTION OSPOS_SALES_TARGETS => '.$target_year.' => '.$target_month;
                    $this	 ->write_report($report_file, 'SUCCES', $message);
         
                }  
            }
			else {
			$number_of_unchanged								=	$number_of_unchanged	+	1;
			}
        }
    }
}
?>
