<?php
class Fdj extends CI_Model
{		
	function __construct()
	{
		parent::__construct('Fdj');
	}
	
	function	fdj_get_info		($item_number)
	{
		$this						->	db->from('fdj_items');
		$this						->	db->where('item_number', $item_number);
		$this						->	db->where('fdj_items.branch_code', $this->config->item('branch_code'));
		return 							$this->db->get();
	}
	
	function	fdj_update			($update_data, $item_number)
	{
		$this						->	db->where('item_number', $item_number);
		$this						->	db->where('fdj_items.branch_code', $this->config->item('branch_code'));
		$this						->	db->update('fdj_items', $update_data);
		return;
	}
	
	function	fdj_save			($values)
	{
		//Run these inserts as a transaction, we want to make sure we do all or nothing
		$this						->	db->trans_start();
		
		// Create the sale header
		$save_data					=	array	(
												'sale_total'	=>	$values['total'],
												'branch_code'	=>	$this->config->item('branch_code')
												);
		$this						->	db->insert('fdj_sales', $save_data);
		$sale_id					=	$this->db->insert_id();
		
		// Create the sales detail and update total items
		if (!empty($values['one']))
		{
			$save_data				=	array();
			$save_data				=	array	(
												'sale_id'		=>	$sale_id,
												'sale_item_number'=>	'fdj_1',
												'sale_qty'		=>	$values['one'],
												'sale_value'	=>	$values['one'] * 1,
												'branch_code'	=>	$this->config->item('branch_code')
												);
			$this					->	db->insert('fdj_sales_items', $save_data);
			
			// get and update the item record
			$this				->	update($save_data);
		}
		
		if (!empty($values['two']))
		{
			$save_data				=	array();
			$save_data				=	array	(
												'sale_id'		=>	$sale_id,
												'sale_item_number'=>	'fdj_2',
												'sale_qty'		=>	$values['two'],
												'sale_value'	=>	$values['two'] * 2,
												'branch_code'	=>	$this->config->item('branch_code')
												);
			$this					->	db->insert('fdj_sales_items', $save_data);
			
			// get and update the item record
			$this				->	update($save_data);
		}
		
		if (!empty($values['three']))
		{
			$save_data				=	array();
			$save_data				=	array	(
												'sale_id'		=>	$sale_id,
												'sale_item_number'=>	'fdj_3',
												'sale_qty'		=>	$values['three'],
												'sale_value'	=>	$values['three'] * 3,
												'branch_code'	=>	$this->config->item('branch_code')
												);
			$this					->	db->insert('fdj_sales_items', $save_data);
			
			// get and update the item record
			$this				->	update($save_data);
		}
		
		if (!empty($values['five']))
		{
			$save_data				=	array();
			$save_data				=	array	(
												'sale_id'		=>	$sale_id,
												'sale_item_number'=>	'fdj_5',
												'sale_qty'		=>	$values['five'],
												'sale_value'	=>	$values['five'] * 5,
												'branch_code'	=>	$this->config->item('branch_code')
												);
			$this					->	db->insert('fdj_sales_items', $save_data);
			
			// get and update the item record
			$this				->	update($save_data);
		}
		
		if (!empty($values['ten']))
		{
			$save_data				=	array();
			$save_data				=	array	(
												'sale_id'		=>	$sale_id,
												'sale_item_number'=>	'fdj_10',
												'sale_qty'		=>	$values['ten'],
												'sale_value'	=>	$values['ten'] * 10,
												'branch_code'	=>	$this->config->item('branch_code')
												);
			$this					->	db->insert('fdj_sales_items', $save_data);
			
			// get and update the item record
			$this				->	update($save_data);
		}
		
		// commit to DB and test
		$this						->	db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		{
			$status					=	-1;
		}
		else
		{	
			$status					=	1;
		}
		
		// return
		return						$status;
	}
	
	function	fdj_getData			(array $inputs)
	{
		// get summary line data
		$this->db->select	(
							'sale_id,
							date(sale_time),
							sale_total',
							false
							);				
		$this->db->from('fdj_sales');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('fdj_sales.branch_code', $this->config->item('branch_code'));
		$this->db->group_by('sale_id');
		$this->db->order_by('sale_id', 'desc');
		
		// load summary data to array
		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		
		// get details for each summary
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select	('
								sale_id,
								sale_item_number,
								sale_value,
								sale_qty
								');
			$this->db->from('fdj_sales_items');
			$this->db->where('sale_id = '.$value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}
		return $data;		
	}
	
	function update($save_data)
	{
		$fdj_item_info			=	array();
		$fdj_item_info			=	$this->fdj_get_info($save_data['sale_item_number'])->row_array();
		$update_data			=	array	(
											'sales_ht'		=>	$fdj_item_info['sales_ht'] + $save_data['sale_value'],
											'sales_qty'		=>	$fdj_item_info['sales_qty'] + $save_data['sale_qty']
											);
		$this					->	fdj_update($update_data, $save_data['sale_item_number']);
	}
}
?>
