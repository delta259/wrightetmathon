<?php
class Categories extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"4";
		
		
		// set data array
		$data															=	array();
		
		// manage session
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);
		
		// set list title if undelete
		switch ($_SESSION['undel'])
		{
			case	1:
					$data['title']										=	$this->lang->line('common_undelete');
			break;
				
			default:
					$data['title']										=	'';
			break;
		}
		
		// set up the pagination
		$config															=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 											= 	site_url('/categories/index');
		$config['total_rows'] 											= 	$this->Category->count_all();
		$this															->	pagination->initialize($config);
		
		// setup output data
		$data['links']													=	$this->pagination->create_links();	
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$create_headers													=	1;
		$data['manage_table']											=	get_categories_manage_table( $this->Category->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);
		
		$this->load->view('categories/manage',$data);
	}

	function search()
	{
		$search															=	$this->input->post('search');
		$create_headers													=	0;
		$data_rows														=	get_categories_manage_table($this->Category->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Category->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($category_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		
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
		
		// manage session
		$_SESSION['show_dialog']										=	1;

		// set data
		switch ($category_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
					$_SESSION['selected_update_sales_price']			=	'N';
					$_SESSION['selected_defect_indicator']				=	'N';
					$_SESSION['selected_offer_indicator']				=	'N';	
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Category->get_info($category_id);
					$_SESSION['selected_update_sales_price']			=	$_SESSION['transaction_info']->category_update_sales_price;
					$_SESSION['selected_defect_indicator']				=	$_SESSION['transaction_info']->category_defect_indicator;
					$_SESSION['selected_offer_indicator']				=	$_SESSION['transaction_info']->category_offer_indicator;

					switch ($_SESSION['undel'])
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->category_name;
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->category_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("categories");
	}
	
	function save()
	{		
		// load catagory data
		$_SESSION['transaction_info']->category_desc					=	$this->input->post('category_desc');
		$_SESSION['transaction_info']->category_update_sales_price		=	$this->input->post('category_update_sales_price');												
		$_SESSION['transaction_info']->category_defect_indicator		=	$this->input->post('category_defect_indicator');
		$_SESSION['transaction_info']->category_offer_indicator			=	$this->input->post('category_offer_indicator');
		$_SESSION['transaction_info']->category_pack_size				=	$this->input->post('category_pack_size');
		$_SESSION['transaction_info']->category_min_order_qty			=	$this->input->post('category_min_order_qty');
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');
		
		// manage session
		switch ($_SESSION['new'])
		{
			// add category
			case	1:
					$_SESSION['transaction_info']->category_id			=	NULL;
					$_SESSION['transaction_info']->category_name		=	$this->input->post('category_name');
			break;
			
			// update category
			default:
					$_SESSION['transaction_info']->category_id			=	$_SESSION['transaction_info']->category_id;
					$_SESSION['transaction_info']->category_name		=	$_SESSION['transaction_info']->category_name;
			break;
		}
		
		// do data verifications
		$this->															verify();
		
		// if here then all checks succeeded so do the update
		$this->															Category->save();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'])
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'00360';
					$this->												view($_SESSION['transaction_info']->category_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'00370';
					$this->												view($_SESSION['transaction_info']->category_id, $_SESSION['origin']);
			break;	
		}
	}

	function delete()
	{
		if($this->Category->delete($_SESSION['transaction_info']->category_id))
		{
			// set success message
			$_SESSION['error_code']										=	'00470';
			$_SESSION['del']											=	1;
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}
		
		redirect("categories");
	}
	
	function list_deleted()
	{
		// set flag to select deleted categories
		$_SESSION['undel']					=	1;
		redirect("categories");
	}
	
	function undelete()
	{
		if($this->Category->undelete($_SESSION['transaction_info']->category_id))
		{
			// set success message
			$_SESSION['error_code']										=	'00510';
			unset($_SESSION['undel']);
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}
		
		$_SESSION['$title']												=	$this->lang->line('common_edit').' => '.$_SESSION['transaction_info']->category_name;
		$this->															view($_SESSION['transaction_info']->category_id);
	}
	
	function verify()
	{
		// verify name entered if add
		if 	($_SESSION['new'] == 1 AND empty($_SESSION['transaction_info']->category_name))
		{
			// set message
			$_SESSION['error_code']										=	'01370';
			redirect("categories");
		}
		
			
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->category_desc)
			OR 	empty($_SESSION['transaction_info']->category_update_sales_price)
			OR 	empty($_SESSION['transaction_info']->category_defect_indicator)
			OR 	empty($_SESSION['transaction_info']->category_offer_indicator)
			OR 	empty($_SESSION['transaction_info']->category_pack_size)
			OR 	empty($_SESSION['transaction_info']->category_min_order_qty)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect("categories");
		}
		
		// verify pack size is numeric
		if (!is_numeric($_SESSION['transaction_info']->category_pack_size))
		{
			// set message
			$_SESSION['error_code']										=	'01350';
			redirect("categories");
		}
		
		// verify pack size is > 0
		if ($_SESSION['transaction_info']->category_pack_size <= 0)
		{
			// set message
			$_SESSION['error_code']										=	'01360';
			redirect("categories");
		}
		
		// verify min_order_qty is numeric
		if (!is_numeric($_SESSION['transaction_info']->category_min_order_qty))
		{
			// set message
			$_SESSION['error_code']										=	'01380';
			redirect("categories");
		}
		
		// verify min_order_qty is > 0
		if ($_SESSION['transaction_info']->category_min_order_qty <= 0)
		{
			// set message
			$_SESSION['error_code']										=	'01390';
			redirect("categories");
		}

		// check category name duplicate
		if (!$this	->	Category->check_duplicate($_SESSION['transaction_info']->category_id, $_SESSION['transaction_info']->category_name))
		{
			// set message
			$_SESSION['error_code']										=	'00170';
			redirect("categories");
		}
	}

}
?>
