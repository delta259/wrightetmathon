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
		$this->db->select('sales_payments.payment_type,
			SUM(payment_amount) as payment_amount,
			SUM(payment_amount / (1 + COALESCE(overall_tax_percentage, 0)/100)) as payment_amount_ht', false);

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
		sum(payment_amount / (1 + COALESCE(overall_tax_percentage, 0)/100)) as subtotal,
		sum(payment_amount) - sum(payment_amount / (1 + COALESCE(overall_tax_percentage, 0)/100)) as tax,
		sum(overall_cost) as cost,
		sum(overall_profit) as profit,
		count( ospos_sales.sale_id) as invoice_count', false);
		$this->db->join('sales', 'sales_payments.sale_id = sales.sale_id');
		$this->db ->from('sales_payments');
		$this->db ->where ("payment_method_code != 'FIDE'");
		$this->db->where("date(sale_time) BETWEEN '". $inputs['start_date']. "' and '". $inputs["end_date"]."'");
		//$this->db ->where ("mode != 'returns' AND mode != 'cancel'");
		//$this->db->where('mode', $inputs['transaction_subtype']);
		return $this->db->get()->row_array();

	}

	public function getSummaryDataz2(array $inputs)
	{
		$this->db->select('sum(payment_amount) as total,
		sum(payment_amount / (1 + COALESCE(overall_tax_percentage, 0)/100)) as subtotal,
		sum(payment_amount) - sum(payment_amount / (1 + COALESCE(overall_tax_percentage, 0)/100)) as tax,
		sum(overall_cost) as cost,
		sum(overall_profit) as profit,
		count( ospos_sales.sale_id) as invoice_count', false);
		$this->db->join('sales', 'sales_payments.sale_id = sales.sale_id');
		$this->db ->from('sales_payments');
		$this->db ->where ("payment_method_code != 'FIDE'");
		$this->db->where("date(sale_time) BETWEEN '". $inputs['start_date']. "' and '". $inputs["end_date"]."'");
		$this->db ->where ("payment_amount >0");
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
		$st = 'sales.payment_type != "Carte Fidelité"';
		$this->db->where($st." AND ". 'date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('discount_percent = 100');
		
		return $this->db->get()->row_array();
	}


	public function getTaxBreakdownz(array $inputs)
	{
		$this->db->select('
			si.line_tax_percentage AS tax_rate,
			SUM(si.item_unit_price * si.quantity_purchased - si.item_unit_price * si.quantity_purchased * si.discount_percent / 100) AS base_ht,
			SUM(si.line_tax) AS tax_amount', false);
		$this->db->from('sales_items si');
		$this->db->join('sales s', 'si.sale_id = s.sale_id');
		$this->db->where("s.sale_id IN (SELECT DISTINCT sale_id FROM ".$this->db->dbprefix('sales_payments')." WHERE payment_method_code != 'FIDE')");
		$this->db->where("date(s.sale_time) BETWEEN '".$inputs['start_date']."' AND '".$inputs['end_date']."'");
		$this->db->group_by('si.line_tax_percentage');
		$this->db->order_by('si.line_tax_percentage', 'DESC');

		return $this->db->get()->result_array();
	}

}
?>
