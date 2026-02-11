<?php
class Home extends CI_Controller
{	
	function index()
	{
		// test browser close ajax call from JS check_browser_close.js
		if (isset($_GET['do']) && $_GET['do'] === "logout")
		{
			$this->logout();
			return;
		}
		else
		{
			$this->load->view("home/home");
		}
	}
	
	function user()
	{
		$this->load->view("home/user");
	}
	
	function admin()
	{
		$this->load->view("home/admin");
	}

	// Deactivate items with stock=0 and no sales in last 3 months
	function deactivate_inactive_items()
	{
		// Check if confirmation was submitted
		if (isset($_POST['confirm_deactivate']) && $_POST['confirm_deactivate'] == 'yes')
		{
			$count = $this->Item->deactivate_inactive_zero_stock(3);
			$_SESSION['success_message'] = sprintf($this->lang->line('modules_deactivate_items_success'), $count);
		}
		else
		{
			// Count items that would be affected
			$count = $this->Item->count_inactive_zero_stock(3);
			$_SESSION['deactivate_items_count'] = $count;
		}

		$this->load->view("home/admin");
	}
	
	function sysadmin()
	{
		$this->load->view("home/sysadmin");
	}

	//link to help
	function help()
	{
		$this->load->view("home/help");
	}

	function display_help_procedures_pdf($help_id)
	{
		
		unset($_SESSION['display_help_procedures_pdf']);
        unset($_SESSION['display_help_regles_pdf']);

		$_SESSION['display_help_procedures_pdf'] = $help_id;

		switch($help_id)
		{
			case '1':
			    $_SESSION['display_help_procedures_pdf'] = '1';
			break;

			case '2':
			    $_SESSION['display_help_procedures_pdf'] = '2';
			break;
			
			case '3':
			    $_SESSION['display_help_procedures_pdf'] = '3';
			break;
			
			case '4':
			    $_SESSION['display_help_procedures_pdf'] = '4';
			break;

			case '5':
			    $_SESSION['display_help_procedures_pdf'] = '5';
			break;

			case '6':
			    $_SESSION['display_help_procedures_pdf'] = '6';
			break;
			
			case '7':
			    $_SESSION['display_help_procedures_pdf'] = '7';
			break;
			
			case '8':
			    $_SESSION['display_help_procedures_pdf'] = '8';
			break;

			case '9':
			    $_SESSION['display_help_procedures_pdf'] = '9';
			break;

			case '10':
			    $_SESSION['display_help_procedures_pdf'] = '10';
			break;

			case '11':
			    $_SESSION['display_help_procedures_pdf'] = '11';
			break;

			case '12':
			    $_SESSION['display_help_procedures_pdf'] = '12';
			break;

			default:
			    $_SESSION['display_help_procedures_pdf'] = '0';
		}
		$this->help();
	}



	function display_help_regles_pdf($help_id)
	{
		
		unset($_SESSION['display_help_procedures_pdf']);
        unset($_SESSION['display_help_regles_pdf']);

		$_SESSION['display_help_regles_pdf'] = $help_id;

		switch($help_id)
		{
			case '1':
			    $_SESSION['display_help_regles_pdf'] = '1';
			break;


        	default:
        	$_SESSION['display_help_regles_pdf'] = '0';
        }
        $this->help();
    }

	function display_help_info_pdf($help_id)
	{
		
		unset($_SESSION['display_info_procedures_pdf']);
        unset($_SESSION['display_info_regles_pdf']);

		$_SESSION['display_help_info_pdf'] = $help_id;

		switch($help_id)
		{
			case '1':
			    $_SESSION['display_help_info_pdf'] = '1';
			break;


        	default:
        	$_SESSION['display_help_info_pdf'] = '0';
        }
        $this->help();
    }

	
	function logout()
	{
		// backup the database first
		$logout = 'yes';
		if (!isset($_SESSION['hostname']) || $_SESSION['hostname'] == "") {
			$this->backup($logout);
		}
		if (!isset($_SESSION['create_report_file']) || $_SESSION['create_report_file'] != 'N') {
			// run payment reports
			// set switches
			$transaction_subtype = 'sales';
			$transaction_sortby = 'payment';
			$export_excel = 1;
			$month_end = 1;
			// set end date = today
			$today = date('Y').'-'.date('n').'-'.date('d');
			// load controller
			$this->load->library('../controllers/reports');
			$_SESSION['premier_rapport_today'] = 1;
			// run report for today
			$start = $today;
			$this->reports->summary_transactions($start, $today, $transaction_subtype, $transaction_sortby, $export_excel, $month_end);
			$_SESSION['premier_rapport_today'] = 2;
			// run report for this month
			$start = date('Y').'-'.date('n').'-'.'01';
			$this->reports->summary_transactions($start, $today, $transaction_subtype, $transaction_sortby, $export_excel, $month_end);
			unset($_SESSION['premier_rapport_today']);
		}
		// then logout
		$this->Employee->logout();
	}
	
	// backup the database
	function backup($logout='no')
	{
		// get the stuff required for the mysqldump command
		$dbhost = $this->db->hostname;
		$dbuser = $this->db->username;
		$dbpass = $this->db->password;
		$backup_path = $this->config->item('BUsavepath');
		$backup_file = date("Ymd-His").' - '.$this->db->database.'.gz';
		$database = $this->db->database;
		
		// create the command and run it
		$command = "mysqldump --host=$dbhost --user=$dbuser --password='$dbpass' -B $database | gzip > '$backup_path$backup_file'";
		system($command);
		
		// if not called from logout, load home page
		if ($logout == 'no')
		{
			$this->index();
		}
	}

	//Demande l'accord pour lancer la procédure d'extinction de l'ordinateur
	function shutdown()
	{
		$_SESSION['show_dialog'] = 8;

		$_SESSION['shutdown_all_indicator_part_2'] = '1';
		$transaction_subtype											=	'sales';
		$transaction_sortby												=	'payment';
		$export_excel													=	1;
		$month_end														=	1;
		$today															=	date('Y').'-'.date('n').'-'.date('d');

		$this															->	load->library('../controllers/reports');
        $_SESSION['premier_rapport_today']=1;
		$start															=	$today;
	    $this															->	reports->summary_transactions($start, $today, $transaction_subtype, $transaction_sortby, $export_excel, $month_end);
		$_SESSION['premier_rapport_today']=2;
		$start															=	date('Y').'-'.date('n').'-'.'01';
		$this															->	reports->summary_transactions($start, $today, $transaction_subtype, $transaction_sortby, $export_excel, $month_end);
		unset($_SESSION['premier_rapport_today']);
		
        redirect("reports");
	}

	//shutdown ordinateur après sauvegarde de la base et déconnexion du POS
	function shutdown_all()
	{
		if((isset($_POST['shutdown_all']) && $_POST['shutdown_all']=='no') && !isset($_SESSION['shutdown_all_indicator']))
		{
			unset($_SESSION['show_dialog']);
			redirect("sales");
		}
		if(isset($_POST['shutdown_all']) && $_POST['shutdown_all']=='yes')
		{
			$_SESSION['shutdown_all_indicator'] = '1';
			//archive de la base de données 
			$logout='yes';
			$this->backup($logout);
			//Montage du drive
			require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_mount.php";
			//Mise à jour POS
			$this->load->library('../controllers/updates');
			$this->updates->software_update();
			$this->updates->software_version();
			$this->updates->database_configuration();
			//Mise à jour base article
			$this->updates->manage_items_automatic();
			// write log
			$this->load->model('Common_routines');
			$action='logout';
			$username=$this->session->userdata('username');
			$this->Common_routines->write_log($action, $username);
			//logout Dans
			echo date("Y-m-d H:i:s");
			//extinction de l'ordinateur
			//sudo shutdown 15
			require_once "/var/www/html/wrightetmathon/application/controllers/shutdown_all.php";
		}
	}

}
?>
