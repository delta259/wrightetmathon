<?php
require_once("report.php");
class Summary_receivings_employees extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array($this->lang->line('reports_employee'), $this->lang->line('reports_subtotal'));
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('CONCAT(last_name, " ",first_name) as employee, sum(quantity_purchased * item_cost_price) as subtotal', false);
		$this->db->from('receivings');
		$this->db->join('receivings_items', 'receivings.receiving_id = receivings_items.receiving_id', 'left');
		$this->db->join('people', 'receivings.employee_id = people.person_id', 'left');
		$this->db->where('date(receiving_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		$this->db->group_by('employee_id');
		$this->db->order_by('last_name');
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
