<?php
class Login extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{		
		// set module id
		$_SESSION['module_id']											=	"99";
		
		// initialise
		unset($_SESSION['transaction_info']);
		
		// manage session
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);
		$_SESSION['show_dialog']										=	0;
		unset($_SESSION['confirm_what']);
		
		// output to pole display		
		// first try to open it.
		$fh																=	$this->pole_display->open();





		// if found output to it.
		if($fh)
		{
			$this->pole_display											->	language($fh);
			$this->pole_display											->	clear_display($fh);
			$this->pole_display											->	welcome($fh);
			$this->pole_display											->	close($fh);
		}		
		
		// load messages to session
		$this															->	Message->load_messages();
		
		// if the an employee is already logged in don't allow another log in.
		if(isset($_SESSION['G']->login_employee_id))
		{
			$_SESSION['error_code']										=	'05750';
			$_SESSION['substitution_parms']								=	array($_SESSION['G']->login_employee_id, $_SESSION['G']->login_employee_username);
			$this->load->view('login');
			return;
		}
		else
		{
			// show user name and password screen
			$this->load->view('login');
		}	
	}	
	
	function validate()
	{
		// get user input
		$username = $this->input->post("username");
		$password = $this->input->post("password");
        $software_update = $this->input->post("software_update");
        $import_items_database = $this->input->post("import_items_database");
		//$import_items_kit_database = $this->input->post("import_items_kit_database");
		
		// validate user input
		if(!$this->Employee->login($username, $password))
		{
			$_SESSION['error_code']										=	'05755';
			redirect("login");
			return;
		}	
	
		// login was successful

		// A note about how the session is used in this application
		// - autostarted in php.ini
		// - stored in var/www/html/wrightetmathon/session
		// - initialsed here
		// - levels of use
		//		- Global (G) - variables set in logn and persist until next login
		//		- Module (C) - variables set at start of module (class)
		//		- Method (M) - variables set at start of method or in class but which control how the method works
		//		- Display(D) - variables which control the display of the view
		// Note; global level implementated but others not.
		
		// load the updates library
		$CI																=	get_instance();
		$CI																->	load->library('../controllers/updates');
		
		// mount the hidrive
		$_SESSION['show_progress_message']								=	"Mounting Sonrisa remote drive";
		require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_mount.php";
		require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_remote_stock_logistique.php";
	//	require_once "/var/www/html/wrightetmathon/application/controllers/update_db.php";
        
		// get the installed software folder name
		$CI																->	Common_routines->software_folder_name();
		
		// initialise Branch Type I/F
		$CI																->	Common_routines->branchtype();
		
		$CI																->	Common_routines->show_image();

		$CI                                                             ->  Common_routines->ip_distributeur();
		
		// run software update if flag in on
		if ($software_update == 'Y')
		{
			// run the software update
			$_SESSION['show_progress_message']							=	"Updating POS software";
			$CI															->	updates->software_update();
			// update software version
			$_SESSION['show_progress_message']							=	"Updating software version";
			$CI															->	updates->software_version();
			// update database configuration
			$_SESSION['show_progress_message']							=	"Updating database configuration";
			$CI															->	updates->database_configuration();
		}
		require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_mount.php";
		require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_by_shop_notification.php";
		require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_mount.php";
		require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_remote_stock_logistique.php";
		unset($_SESSION['show_progress_switch']);
//		// initialise application
//		$CI																->	Common_routines->initialise();
		
		// update branches
		$CI																->	updates->branches();

        // update config
		$CI																->	updates->db_config();
		// initialise application
		$CI -> Common_routines->initialise();

        // update the articles database
		if ($import_items_database == 'Y')
		{
			$CI															->	updates->manage_items_automatic();
			$CI                                                         ->updates->manage_items_kit_automatic();    //update kit base
		}

		//if($import_items_kit_database == 'Y') //Mise à jour base kit 
		//{
		//	$CI ->updates->manage_items_kit_automatic();
		//}
		
        // change slides
        $CI																->	updates->slides_ventes();

		// show flash_info
		$CI																->	updates->flash_info();
		
		// destroy temp pid file
		array_map('unlink', glob("/home/wrightetmathon/.app_running.txt"));
		
		if ($_SESSION['flash_info_show'] == 1)
		{
			$this->load->view('flash_info');
			return;
		}
		else
		{
			$CI															->	rolling();
		}
	}
	
	function rolling()
	{
		// unmount the hidrive
		// this is done here because the auto items update redirects to here
		require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_unmount.php";
		
		// run rolling stock check listing
		$export_excel													=	0;
		$create_PO														=	0;
		$set_NM															=	0;
		$set_SM															=	0;
		$month_end														=	0;
		
		// need to get the instance because we load the updates controller and you can't load a second controller using $this->
		$CI																=	get_instance();
		$CI																->	load->library('../controllers/reports');
		$CI																->	reports->inventory_rolling($export_excel, $create_PO, $set_NM, $set_SM, $month_end);	
	}
	
	function exit()
	{
		$this->load->view('exit');
	}
		
}
?>
