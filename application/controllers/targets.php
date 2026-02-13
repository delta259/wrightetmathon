<?php
class Targets extends CI_Controller
{
	function index($year = 0)
	{
		// set module id
		$_SESSION['module_id']											=	"25";

		// set data array
		$data = array();

		// manage session
		$_SESSION['controller_name']=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);

		// Determine which year to display
		if ($year > 0) {
			$display_year = intval($year);
		} elseif (!empty($_SESSION['targets_year'])) {
			$display_year = intval($_SESSION['targets_year']);
		} else {
			$display_year = intval(date('Y'));
		}
		$_SESSION['targets_year'] = $display_year;

		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$data['title']	=	$this->lang->line('common_undelete');
			break;

			default:
					$data['title']	=	'';
			break;
		}

		// setup output data (no pagination needed — max 12 months per year)
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->Common_routines->set_form_width();
		$data['display_year']		=	$display_year;
		$data['manage_table_data']	=	$this->Target->get_all($display_year, 12, 0);

		$this->load->view('targets/manage',$data);
	}

	function search()
	{
		$search						=	$this->input->post('search');
		$create_headers				=	0;
		$data_rows					=	get_targets_manage_table($this->Target->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Target->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($target_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_id']								=	$target_id;

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
			break;

			default:
					$_SESSION['origin']									=	$origin;
			break;
		}

		// set data
		switch ($target_id)
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
					$_SESSION['transaction_info']->target_year			=	date('Y');
					$_SESSION['transaction_info']->target_month			=	0;
					$_SESSION['transaction_info']->target_shop_open_days=	0;
					$_SESSION['transaction_info']->target_shop_turnover	=	0;
			break;

			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Target->get_info($target_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->target_year.'/'.$_SESSION['transaction_info']->target_month;
						break;

						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').'

								 '.$_SESSION['transaction_info']->target_year.'/'.$_SESSION['transaction_info']->target_month;
						break;
					}
					unset($_SESSION['new']);
			break;
		}

		$this->load->view('targets/form');
	}
	
	function save()
	{		
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_id']);
			$_SESSION['original_id']									=	$_SESSION['transaction_info']->target_id;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		if (($_SESSION['new'] ?? 0) == 1)
		{
			$_SESSION['transaction_info']->target_year						=	$this->input->post('target_year');
			$_SESSION['transaction_info']->target_month						=	$this->input->post('target_month');
		}
		$_SESSION['transaction_info']->target_day						=	0;											
		$_SESSION['transaction_info']->target_shop_open_days			=	$this->input->post('target_shop_open_days');
		$_SESSION['transaction_info']->target_shop_turnover				=	$this->input->post('target_shop_turnover');
		$_SESSION['transaction_info']->person_id						=	$_SESSION['G']->login_employee_id;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');
		
		// do data verifications
		$this->															verify();
		
		// if here then all checks succeeded so do the update
		$this->															Target->save();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'04080';
			break;

			default:
					// set message
					$_SESSION['error_code']								=	'04090';
			break;
		}

		// redirect back to manage page
		unset($_SESSION['new']);
		unset($_SESSION['first_time']);
		redirect("targets");
	}

	function delete()
	{
		$target_id = $_SESSION['transaction_info']->target_id ?? 0;
		if ($target_id > 0)
		{
			$this->db->where('target_id', $target_id);
			$this->db->delete('targets');
			$_SESSION['error_code']										=	'00470';
			$_SESSION['del']											=	1;
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}

		redirect("targets");
	}

	function list_deleted()
	{
		$_SESSION['undel']					=	1;
		redirect("targets");
	}

	function undelete()
	{
		$_SESSION['error_code']										=	'00350';
		redirect("targets");
	}
	
	function verify()
	{
		// verify required fields are entered
		if 	(	!is_numeric($_SESSION['transaction_info']->target_year)
			OR 	!is_numeric($_SESSION['transaction_info']->target_month)
			OR 	!is_numeric($_SESSION['transaction_info']->target_shop_open_days)
			OR 	!is_numeric($_SESSION['transaction_info']->target_shop_turnover)
			)
		{
			// set message
			$_SESSION['error_code']			=	'04060';
			redirect("targets/view/" . $_SESSION['transaction_id']);
		}

		// test if year is valid
		if ($_SESSION['transaction_info']->target_year < date("Y"))
		{
			// set message
			$_SESSION['error_code']			=	'03060';
			redirect("targets/view/" . $_SESSION['transaction_id']);
		}

		// test if month is valid
		if ($_SESSION['transaction_info']->target_month < 1 OR  $_SESSION['transaction_info']->target_month > 12)
		{
			// set message
			$_SESSION['error_code']			=	'03070';
			redirect("targets/view/" . $_SESSION['transaction_id']);
		}

		// test if shop open days is valid
		if ($_SESSION['transaction_info']->target_shop_open_days < 0 OR  $_SESSION['transaction_info']->target_shop_open_days > 31)
		{
			// set message
			$_SESSION['error_code']			=	'05000';
			redirect("targets/view/" . $_SESSION['transaction_id']);
		}

		// check year/month duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1)
		{
			$count							=	$this->Target->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']		=	'04070';
				redirect("targets/view/" . $_SESSION['transaction_id']);
			}
		}
	}
}
?>
