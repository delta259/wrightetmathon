<?php
require_once("report.php");
class Specific_category extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array('summary' => array($this->lang->line('reports_sale_id'), $this->lang->line('reports_date'), $this->lang->line('reports_items_purchased'), $this->lang->line('reports_sold_by'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'), $this->lang->line('reports_payment_type'), $this->lang->line('reports_comments')),
					'details' => array($this->lang->line('reports_name'), $this->lang->line('reports_category'),$this->lang->line('reports_serial_number'), $this->lang->line('reports_description'), $this->lang->line('reports_quantity_purchased'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'),$this->lang->line('reports_discount'))
		);		
	}
	
	public function getData(array $inputs)
	{		
		$this->db->select	('
							sales_items.sale_id as transaction_id,
							sales.customer_id,
							date(sale_time) as transaction_date, 
							CONCAT(employee.first_name," ",employee.last_name) as employee_name,
							CONCAT(customer.first_name," ",customer.last_name) as transaction_name,
							subtotal_before_discount,
							subtotal_discount_percentage_amount,
							subtotal_discount_amount_amount,
							subtotal_after_discount,
							overall_tax,
							overall_total,
							overall_tax_percentage,
							overall_tax_name,
							overall_cost,
							overall_profit,
							amount_change,
							payment_type, 
							comment,
							mode', 
							false
							);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales_items.sale_id = sales.sale_id');
		$this->db->join('people as employee', 'sales.employee_id = employee.person_id');
		$this->db->join('people as customer', 'sales.customer_id = customer.person_id', 'left');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'" and line_category_id='. $inputs['category_id']);
		
		$this->db->group_by('sales_items.sale_id');
		$this->db->order_by('sales_items.sale_id', 'desc');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select	('
								line,
								category,
								item_number,
								name,
								serialnumber,
								quantity_purchased,
								item_cost_price,
								item_unit_price,
								discount_percent,
								line_sales_before_discount,
								line_discount,
								line_sales_after_discount,
								line_tax,
								line_sales,
								line_cost,
								line_profit,
								line_tax_percentage,
								line_tax_name
								');
			$this->db->from('sales_items');
			$this->db->join('items', 'sales_items.item_id = items.item_id');
			$this->db->where('sale_id = '.$value['transaction_id']);
			$this->db->order_by('line',"asc");
			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		return $data;
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(overall_total) as total, 
							sum(overall_tax) as tax, 
							sum(subtotal_after_discount) as subtotal, 
							sum(overall_cost) as cost, 
							sum(overall_profit) as profit, 
							count(ospos_sales.sale_id) as invoice_count');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales_items.sale_id = sales.sale_id');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'" and line_category_id='.$inputs['category_id']);
		
		return $this->db->get()->row_array();
	}
	
	public function offered_count(array $inputs)
	{
		$this->db->select('count(discount_percent) as offered_count');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales_items.sale_id = sales.sale_id');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('discount_percent = 100');
		
		return $this->db->get()->row_array();
	}
}
?>
