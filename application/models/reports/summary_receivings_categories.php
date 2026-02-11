<?php
require_once("report.php");
class Summary_receivings_categories extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_category'), $this->lang->line('reports_subtotal'));
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('category, sum(quantity_purchased * item_cost_price) as subtotal', false);
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings_items.receiving_id = receivings.receiving_id');
		$this->db->join('items', 'receivings_items.item_id = items.item_id');
		$this->db->where('date(receiving_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		$this->db->group_by('category');
		$this->db->order_by('category');
		return $this->db->get()->result_array();
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select	(
							'	sum(quantity_purchased * item_cost_price) as subtotal,
							'
							);
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings_items.receiving_id = receivings.receiving_id');
		$this->db->where('date(receiving_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		
		return $this->db->get()->row_array();
	}
}
?>
