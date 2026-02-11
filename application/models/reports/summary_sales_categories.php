<?php
require_once("report.php");
class Summary_sales_categories extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_category'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'));
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('line_category as category, sum(line_sales_after_discount) as subtotal, sum(line_sales) as total, sum(line_tax) as tax, sum(line_profit) as profit');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales_items.sale_id = sales.sale_id');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		$this->db->group_by('line_category');
		$this->db->order_by('line_category');

		return $this->db->get()->result_array();		
	}
	
	public function getData_by_suppliers(array $inputs)
	{
		$this->db->select('line_category as category, sum(line_sales_after_discount) as subtotal, sum(line_sales) as total, sum(line_tax) as tax, sum(line_profit) as profit, mode'); //, ospos_items_suppliers.supplier_id');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales_items.sale_id = sales.sale_id');
		$this->db->join('items_suppliers', 'sales_items.item_id = items_suppliers.item_id');
		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		$this->db->where('supplier_id', $inputs['transaction_sortby']);
		$this->db->group_by('line_category');
		$this->db->order_by('line_category');

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
	
	/*
	public function getSummaryData_by_supplier(array $inputs)
	{
		$this->db->select('sum(ospos_sales.overall_total) as total, 
							sum(ospos_sales.overall_tax) as tax, 
							sum(ospos_sales.subtotal_after_discount) as subtotal, 
							sum(ospos_sales.overall_cost) as cost, 
							sum(ospos_sales.overall_profit) as profit, 
							count(ospos_sales.sale_id) as invoice_count,
						');
//        $this->db->distinct();
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales_items.sale_id = sales.sale_id');    //, "FULL");    //, "INNER");
		$this->db->join('items_suppliers', 'sales_items.item_id = items_suppliers.item_id');    //, "FULL");    //, "INNER");

		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		$this->db->where('items_suppliers.supplier_id = ' . $inputs['transaction_sortby']);
		$this->db->where('items_suppliers.supplier_preferred', 'Y');
		return $this->db->get()->row_array();
	}//*/

	// Attention la jointure fait apparaître les lines en double, donc il faut faire la somme des lines des articles  
	public function getSummaryData_by_supplier(array $inputs)
	{
		$this->db->select('sum(ospos_sales_items.line_sales) as total, 
							sum(ospos_sales_items.line_tax) as tax, 
							sum(ospos_sales_items.line_sales_after_discount) as subtotal, 
							sum(ospos_sales_items.line_cost) as cost, 
							sum(ospos_sales_items.line_profit) as profit, 
							count(DISTINCT ospos_sales_items.sale_id) as invoice_count,
						');    //Nombre de factures distinctes
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales_items.sale_id = sales.sale_id');    //, "FULL");    //, "INNER");
		$this->db->join('items_suppliers', 'sales_items.item_id = items_suppliers.item_id');    //, "FULL");    //, "INNER");

		$this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		$this->db->where('items_suppliers.supplier_id = ' . $inputs['transaction_sortby']);
		$this->db->where('items_suppliers.supplier_preferred', 'Y');
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
