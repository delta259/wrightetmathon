<?php
class Home extends CI_Controller
{	
	function index()
	{
		// test browser close ajax call from JS check_browser_close.js
		if ($_GET['do'] === "logout")
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
	
	function sysadmin()
	{
		$this->load->view("home/sysadmin");
	}


	
	function logout()
	{
		// backup the database first
		$logout															=	'yes';
		$this->backup($logout);
		
		// run payment reports
		// set switches
		$transaction_subtype											=	'sales';
		$transaction_sortby												=	'payment';
		$export_excel													=	1;
		$month_end														=	1;
		
		// set end date = today
		$today															=	date('Y').'-'.date('n').'-'.date('d');
		
		// load controller
		$this															->	load->library('../controllers/reports');

		// run report for today
		$start															=	$today;
		$this															->	reports->summary_transactions($start, $today, $transaction_subtype, $transaction_sortby, $export_excel, $month_end);

		// run report for this month
		$start															=	date('Y').'-'.date('n').'-'.'01';
		$this															->	reports->summary_transactions($start, $today, $transaction_subtype, $transaction_sortby, $export_excel, $month_end);
		
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
}
?>
