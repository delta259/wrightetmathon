<!-- -->
<!-- Called from = controllers->reports->detailed_receivings
<!-- -->


<?php
require_once("report.php");
class Detailed_receivings extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array('summary' => array($this->lang->line('reports_receiving_id'), $this->lang->line('reports_date'), $this->lang->line('reports_items_received'), $this->lang->line('reports_received_by'), $this->lang->line('reports_supplied_by'), $this->lang->line('reports_total'), $this->lang->line('reports_payment_type'), $this->lang->line('reports_comments')),
					'details' => array($this->lang->line('reports_name'), $this->lang->line('reports_category'), $this->lang->line('reports_quantity_purchased'), $this->lang->line('reports_total'), $this->lang->line('reports_discount'))
		);		
	}
	
	public function getData(array $inputs)
	{
		// initialise
		$data															=	array();
		
		// get summary line data
		$this->db->select		(										'receiving_id as transaction_id,
																		receivings.supplier_id,
																		date(receiving_time) as transaction_date, 
																		CONCAT(employee.first_name," ",employee.last_name) as employee_name,
																		CONCAT(supplier.first_name," ",supplier.last_name) as transaction_name,
																		comment,
																		mode', 
																		false
								);
							
		$this->db->from('receivings');
		$this->db->join('people as employee', 'receivings.employee_id = employee.person_id', 'left');
		$this->db->join('people as supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->where('date(receiving_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		
		$this->db->group_by('receiving_id');
		$this->db->order_by('receiving_id', 'desc');
		
		$data['summary'] = $this->db->get()->result_array();		
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select	(										'line,
																		category,
																		item_number,
																		name,
																		quantity_purchased,
																		item_cost_price,
																		item_unit_price'
								);
			$this->db->from('receivings_items');
			$this->db->join('items', 'receivings_items.item_id = items.item_id');
			$this->db->where('receiving_id = '.$value['transaction_id']);
			$this->db->order_by('line',"asc");
			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		// return
		return $data;
	}

	public function getData_by_supplier(array $inputs)
	{


		$this->db->select('
		categories.category_name,
		items.item_number,
		items.name,
		sum(ospos_sales_items.quantity_purchased) AS `quantity_purchased`,
		AVG(ospos_sales_items.item_unit_price) AS item_unit_price,
		sum(ospos_sales_items.line_sales) AS line_sales,
		sum(ospos_sales_items.line_tax) AS line_tax,
		sum(ospos_sales_items.line_cost) AS line_cost,
		sum(ospos_sales_items.line_profit) AS line_profit,
		sum(ospos_sales_items.line_sales_before_discount) AS line_sales_before_discount,
		mode'
	    );

	    $this->db->from('items_suppliers');
	
	    $this->db->join('sales_items', 'items_suppliers.item_id = sales_items.item_id', 'INNER');
	    $this->db->join('sales', 'sales_items.sale_id = sales.sale_id', 'INNER');
        $this->db->join('items', 'sales_items.item_id = items.item_id', 'INNER');
	    $this->db->join('categories', 'items.category_id = categories.category_id', 'INNER');
    
	    $this->db->where('items_suppliers.supplier_preferred', 'Y');
	    $this->db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
	    $this->db->where('items_suppliers.supplier_id = ' . $inputs['transaction_sortby']);
	    
	    $this->db->group_by('categories.category_name');    
        $this->db->group_by('items.item_number');
	    $this->db->group_by('items.name');    
        $this->db->group_by('item_unit_price');
      
        $this->db->order_by('categories.category_name');
	    $this->db->order_by('items.name');
	    
	    $data = $this->db->get()->result_array();
        return $data;



		// initialise
		$data															=	array();
		
		// get summary line data
		$this->db->select		(										'receiving_id as transaction_id,
																		receivings.supplier_id,
																		date(receiving_time) as transaction_date, 
																		CONCAT(employee.first_name," ",employee.last_name) as employee_name,
																		CONCAT(supplier.first_name," ",supplier.last_name) as transaction_name,
																		comment,
																		mode', 
																		false
								);
							
		$this->db->from('receivings');
		$this->db->join('people as employee', 'receivings.employee_id = employee.person_id', 'left');
		$this->db->join('people as supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->where('date(receiving_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		
		$this->db->group_by('receiving_id');
		$this->db->order_by('receiving_id', 'desc');
		
		$data['summary'] = $this->db->get()->result_array();		
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select	(										'line,
																		category,
																		item_number,
																		name,
																		quantity_purchased,
																		item_cost_price,
																		item_unit_price'
								);
			$this->db->from('receivings_items');
			$this->db->join('items', 'receivings_items.item_id = items.item_id');
			$this->db->where('receiving_id = '.$value['transaction_id']);
			$this->db->order_by('line',"asc");
//			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		// return
//		return $data;
	}

	public function getDataz(array $inputs)
	
	{
		// initialise
		$data															=	array();
		
		// get summary line data
		$this->db->select		(										'receiving_id as transaction_id,
																		receivings.supplier_id,
																		date(receiving_time) as transaction_date, 
																		CONCAT(employee.first_name," ",employee.last_name) as employee_name,
																		CONCAT(supplier.first_name," ",supplier.last_name) as transaction_name,
																		comment,
																		mode', 
																		false
								);
							
		$this->db->from('receivings');
		$this->db->join('people as employee', 'receivings.employee_id = employee.person_id', 'left');
		$this->db->join('people as supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->where('date(receiving_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this->db->where('mode', $inputs['transaction_subtype']);
		
		$this->db->group_by('receiving_id');
		$this->db->order_by('receiving_id', 'desc');
		
		$data['summary'] = $this->db->get()->result_array();		
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select	(										'line,
																		category,
																		item_number,
																		name,
																		quantity_purchased,
																		item_cost_price,
																		item_unit_price'
								);
			$this->db->from('receivings_items');
			$this->db->join('items', 'receivings_items.item_id = items.item_id');
			$this->db->where('receiving_id = '.$value['transaction_id']);
			$this->db->order_by('line',"asc");
			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		// return
		return $data;
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
