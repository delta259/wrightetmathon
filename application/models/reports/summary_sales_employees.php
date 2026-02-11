<?php
require_once("report.php");
class Summary_sales_employees extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_employee'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'));
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('CONCAT(last_name, " ",first_name) as employee, sum(subtotal_after_discount) as subtotal, sum(overall_total) as total, sum(overall_tax) as tax, sum(overall_profit) as profit', false);
		$this->db->from('sales');
		$this->db->join('people', 'sales.employee_id = people.person_id', 'left');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		$this->db->group_by('employee_id');
		$this->db->order_by('last_name');
		return $this->db->get()->result_array();		
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(overall_total) as total, 
							sum(overall_tax) as tax, 
							sum(subtotal_after_discount) as subtotal, 
							sum(overall_cost) as cost, 
							sum(overall_profit) as profit, 
							count(sale_id) as invoice_count');
		$this->db->from('sales');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
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
