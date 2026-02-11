<?php
require_once("report.php");
class Summary_sales extends Report
{
	function __construct()
	{
		parent::__construct();
	}

	public function getDataColumns()
	{
		return array($this->lang->line('reports_date'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'));
	}
	
	public function getData(array $inputs)
	{				
		$this->db->select	(
							'date(sale_time) as sale_date, 
							sum(subtotal_after_discount) as subtotal, 
							sum(overall_total) as total, 
							sum(overall_tax) as tax, 
							sum(overall_profit) as profit'
							);
		$this->db->from('sales');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);		
		$this->db->group_by('date(sale_time)');
		$this->db->order_by('date(sale_time)');
		return $this->db->get()->result_array();
	}

	public function getDataZ(array $inputs)
	{				
		$this->db->select	(
							'date(sale_time) as sale_date, sum(payment_amount) as total,
							sum(payment_amount)/1.2 as subtotal, 
							sum(payment_amount) - sum(payment_amount/1.20) as tax, 
							sum(overall_profit) as profit'
							);
		$this->db ->from('sales_payments');
		$this->db->join('sales', 'sales_payments.sale_id = sales.sale_id');
		$this->db ->where ("payment_method_code != 'FIDE'");   
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		//$this->db->where('mode', $inputs['transaction_subtype']);		
		$this->db->group_by('date(sale_time)');
		$this->db->order_by('date(sale_time)');
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
	
	public function getSummaryDataZ(array $inputs)
	{
	
		$this->db->select('sum(payment_amount) as total,
		sum(payment_amount / 1.2) as subtotal,
		sum(payment_amount) -
		sum(payment_amount / 1.2 ) as tax,
		sum(overall_cost) as cost,
		sum(overall_profit) as profit,
		count( ospos_sales.sale_id) as invoice_count');
		$this->db ->from('sales_payments');
		$this->db->join('sales', 'sales_payments.sale_id = sales.sale_id');
		$this->db ->where ("payment_method_code != 'FIDE'");   
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		//$this->db->where('mode', $inputs['transaction_subtype']);		
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

	public function offered_countZ(array $inputs)
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
