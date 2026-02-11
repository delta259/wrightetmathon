<?php
class Fdjs extends CI_Controller
{
	function	index()
	{		
		$this->load->view('fdj/fdj_sales_entry');
	}

	function	calculate()
	{		
		// get the input data
		$values					=	array();
		$values					=	array	(
											'one'				=>	$this->input->post('one_euro_qty'),
											'two'				=>	$this->input->post('two_euro_qty'),
											'three'				=>	$this->input->post('three_euro_qty'),
											'five'				=>	$this->input->post('five_euro_qty'),
											'ten'				=>	$this->input->post('ten_euro_qty'),
											'success_or_failure'=>	'',
											'message'			=>	''
											);
		
		// set cookie data
		$this					->	session->set_userdata($values);
		
		// validate data - must be numeric
		$error					=	0;
		if (!empty($values['one']) AND !is_numeric($values['one']))
		{
			$error 				=	1;
		}
		if (!empty($values['two']) AND !is_numeric($values['two']))
		{
			$error 				=	1;
		}
		if (!empty($values['three']) AND !is_numeric($values['three']))
		{
			$error 				=	1;
		}
		if (!empty($values['five']) AND !is_numeric($values['five']))
		{
			$error 				=	1;
		}
		if (!empty($values['ten']) AND !is_numeric($values['ten']))
		{
			$error 				=	1;
		}
		
		if ($error == 1)
		{
			$this				->	session->set_userdata('success_or_failure', 'F');
			$this				->	session->set_userdata('message', $this->lang->line('fdj_not_numeric'));
			redirect('fdjs');
		}
		
		// calculate value
		$total					=	(1 * $values['one']) + (2 * $values['two']) + (3 * $values['three']) + (5 * $values['five']) + (10 * $values['ten']);
		$this					->	session->set_userdata('confirm', 'Y');
		$this					->	session->set_userdata('total', $total);
		redirect('fdjs');
	}
	
	function	confirm()
	{		
		// get userdata data
		$values					=	array();
		$values					=	$this->session->all_userdata();
				
		// create the sale records
		$status					=	$this->Fdj->fdj_save($values);
		if ($status == -1)
		{
			$this				->	session->set_userdata('success_or_failure', 'F');
			$this				->	session->set_userdata('message', $this->lang->line('fdj_DB_failure'));
			redirect('fdjs');
		}
		
		// unset the userdata and return
		$this					->	unset_user_data();
		redirect('fdjs');
	}
	
	function	cancel()
	{		
		// unset the userdata and return
		$this					->	unset_user_data();
		redirect('fdjs');
	}
	
	function	unset_user_data()
	{
		$values					=	array	(
											'one'					=>	'',
											'two'					=>	'',
											'three'					=>	'',
											'five'					=>	'',
											'ten'					=>	'',
											'total'					=>	'',
											'confirm'				=>	'',
											'success_or_failure'	=>	'',
											'message'				=>	''
											);
		$this					->	session->unset_userdata($values);
	}
}
?>
