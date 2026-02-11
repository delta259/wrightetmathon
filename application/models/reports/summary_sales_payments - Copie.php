<?php
require_once("report.php");
class Summary_sales_payments extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_payment_type'), $this->lang->line('reports_total'), $this->lang->line('reports_subtotal'));
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('sales_payments.payment_type, SUM(payment_amount) as payment_amount', false);

		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		
		$this->db->group_by("payment_type");
		return $this->db->get()->result_array();
	}
	
	public function getDataz(array $inputs)
	{
		$this->db->select('sales_payments.payment_type, SUM(payment_amount) as payment_amount', false);

		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$st = 'sales_payments.payment_method_code != "FIDE"';
		//$this->db->where('mode', $inputs['transaction_subtype']);	
		$this->db->where($st." AND ".'date(sale_time) BETWEEN "'. $inputs['start_date'].'" and "'. $inputs['end_date'].'"');
		$this->db->group_by("payment_type");

		return $this->db->get()->result_array();
		
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(overall_total) as total, 
						   sum(overall_total / (1 + (overall_tax_percentage/100 ) ) )as tax, 
							sum(subtotal_after_discount) as subtotal, 
							sum(overall_cost) as cost, 
							sum(overall_profit) as profit, 
							count(sale_id) as invoice_count');
		$this->db->from('sales');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		
		return $this->db->get()->row_array();
	}
	

	public function getSummaryDataz(array $inputs)
	{

		$this->db->select('sum(payment_amount) as total,
		sum(payment_amount / (1 + (overall_tax_percentage/100 ) )) as subtotal,
		sum(payment_amount) - sum(payment_amount / (1 + (overall_tax_percentage/100 ) ) ) as tax,
		sum(overall_cost) as cost,
		sum(overall_profit) as profit,
		count( ospos_sales.sale_id) as invoice_count');
		$this->db->join('sales', 'sales_payments.sale_id = sales.sale_id');
		$this->db ->from('sales_payments');
		$this->db ->where ("payment_method_code != 'FIDE'"); 
		$this->db->where("date(sale_time) BETWEEN '". $inputs['start_date']. "' and '". $inputs["end_date"]."'");
		$this->db ->where ("mode != 'returns' AND mode != 'cancel'"); 
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


	public function offered_countz(array $inputs)
	{
		$this->db->select('count(discount_percent) as offered_count');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales_items.sale_id = sales.sale_id');
		$st = 'sales.payment_type != "Carte Fidelit�"';
		$this->db->where($st." AND ". 'date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('discount_percent = 100');
		
		return $this->db->get()->row_array();
	}


}
?>
