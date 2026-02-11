<?php
class Cashtill extends CI_Model 
{		
	function	exists($year, $month, $day, $cash_code)
	{
		$this						->	db->from('cash_till');
		$this						->	db->where('cash_year', $year);
		$this						->	db->where('cash_month', $month);
		$this						->	db->where('cash_day', $day);
		$this						->	db->where('cash_code', $cash_code);
		$this						->	db->where('cash_till.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	function	reference_exists($reference, $cash_code)
	{
		$this						->	db->from('cash_till');
		$this						->	db->where('cash_bank_deposit_reference', $reference);
		$this						->	db->where('cash_code', $cash_code);
		$this						->	db->where('cash_till.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	function	insert($cash_till_data)
	{
		$this->db->insert('cash_till', $cash_till_data);
	}
	
	function	get($year, $month, $day, $cash_code)
	{
		$this						->	db->from('cash_till');
		$this						->	db->where('cash_year', $year);
		$this						->	db->where('cash_month', $month);
		$this						->	db->where('cash_day', $day);
		$this						->	db->where('cash_code', $cash_code);
		$this						->	db->where('cash_till.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get();
	}
	
	function	get_total_year($year, $cash_code)
	{
		$this						->	db->from('cash_till');
		$this						->	db->where('cash_year', $year);
		$this						->	db->where('cash_code', $cash_code);
		$this						->	db->where('cash_till.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get();
	}
	
	function	get_total_month($year, $month, $cash_code)
	{
		$this						->	db->from('cash_till');
		$this						->	db->where('cash_year', $year);
		$this						->	db->where('cash_month', $month);
		$this						->	db->where('cash_code', $cash_code);
		$this						->	db->where('cash_till.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get();
	}
	
	function	delete($year, $month, $day, $cash_code)
	{
		$this						->	db->where('cash_year', $year);
		$this						->	db->where('cash_month', $month);
		$this						->	db->where('cash_day', $day);
		$this						->	db->where('cash_code', $cash_code);
		$this						->	db->where('cash_till.branch_code', $this->config->item('branch_code'));
		return 						$this->db->delete('cash_till');
	}
	
	function	get_all_today($year, $month, $day)
	{
		$this						->	db->from('cash_till');
		$this						->	db->where('cash_year', $year);
		$this						->	db->where('cash_month', $month);
		$this						->	db->where('cash_day', $day);
		$this						->	db->where('cash_till.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by('timestamp', 'asc');
		$this						->	db->order_by('cash_sequence', 'asc');
		return 						$this->db->get();
	}
	
	function	get_all_lastday($year, $month, $day)
	{
		$this						->	db->from('cash_till');
		//$this						->	db->where('cash_year', $year);
	    //$this						->	db->where('cash_month', $month);
		//$this						->	db->where('cash_day', $day);
		$this						->	db->where('cash_till.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('cash_sequence','60');
		$this						->	db->order_by('cash_year', 'desc');
		$this						->	db->order_by('cash_month', 'desc');
		$this						->	db->order_by('cash_day', 'desc');
		$this						->	db->order_by('timestamp', 'asc');
		$this						->	db->order_by('cash_sequence', 'asc');
		$this                       ->  db->limit(1);
		return 						$this->db->get();
	} 
	function	get_all_day($year, $month, $day)
	{
		$this						->	db->from('cash_till');
		$this						->	db->where('cash_year', $year);
	    $this						->	db->where('cash_month', $month);
		$this						->	db->where('cash_day', $day);
		$this						->	db->where('cash_till.branch_code', $this->config->item('branch_code'));
        $close = array('10','20');
		$this						->	db->where_in('cash_sequence',$close);
		$this						->	db->order_by('cash_year', 'desc');
		$this						->	db->order_by('cash_month', 'desc');
		$this						->	db->order_by('cash_day', 'desc');
		$this						->	db->order_by('timestamp', 'asc');
		$this						->	db->order_by('cash_sequence', 'asc');
		$this                       ->  db->limit(2);
		return 						$this->db->get();
	} 
	function	get_last_close($cash_code)
	{
		$this						->	db->from('cash_till');
		$this						->	db->select('cash_year');
		$this						->	db->select('cash_month');
		$this						->	db->select('cash_day');
		$this						->	db->select('cash_amount');
		$this						->	db->where('cash_code', $cash_code);
		$this						->	db->order_by('cash_year', 'desc');
		$this						->	db->order_by('cash_month', 'desc');
		$this						->	db->order_by('cash_day', 'desc');
		$this						->	db->limit(1);
		
		return 						$this->db->get();
	}

	function get_cash_amount_sum($inputs)
	{
		$this->db->select('SUM(cash_amount) as total');
		$this->db->from('cash_till');
		
		foreach($inputs as $key => $input)
		{
			$this->db->where($key, $input);
		}
		
		$this->db->order_by('timestamp', 'ASC');
		
		$data = $this->db->get()->result_array();
		return $data;
	}

	function get_bank_deposits_year($year, $month = 0)
	{
		$prefix = $this->db->dbprefix;
		$this->db->select($prefix.'cash_till.*, CONCAT('.$prefix.'people.first_name, " ", '.$prefix.'people.last_name) as employee_name', FALSE);
		$this->db->from('cash_till');
		$this->db->join('people', $prefix.'people.person_id = '.$prefix.'cash_till.person_id', 'left');
		$this->db->where('cash_year', $year);
		if ($month > 0) {
			$this->db->where('cash_month', $month);
		}
		$this->db->where('cash_code', 'BANK_DEPOSIT');
		$this->db->where($prefix.'cash_till.branch_code', $this->config->item('branch_code'));
		$this->db->order_by('timestamp', 'desc');
		return $this->db->get();
	}

	function get_movements_by_day($year, $month = 0)
	{
		$this->db->select('cash_day, cash_month, cash_code, SUM(cash_amount) as total_amount', FALSE);
		$this->db->from('cash_till');
		$this->db->where('cash_year', $year);
		if ($month > 0) {
			$this->db->where('cash_month', $month);
		}
		$this->db->where_in('cash_code', array('CASH_SALES', 'SET_ASIDE', 'BANK_DEPOSIT'));
		$this->db->where('branch_code', $this->config->item('branch_code'));
		$this->db->group_by(array('cash_month', 'cash_day', 'cash_code'));
		$this->db->order_by('cash_month', 'asc');
		$this->db->order_by('cash_day', 'asc');
		return $this->db->get();
	}

	function get_movements_monthly($year)
	{
		$this->db->select('cash_month, cash_code, SUM(cash_amount) as total_amount', FALSE);
		$this->db->from('cash_till');
		$this->db->where('cash_year', $year);
		$this->db->where_in('cash_code', array('CASH_SALES', 'SET_ASIDE', 'BANK_DEPOSIT'));
		$this->db->where('branch_code', $this->config->item('branch_code'));
		$this->db->group_by(array('cash_month', 'cash_code'));
		$this->db->order_by('cash_month', 'asc');
		return $this->db->get();
	}

	function delete_line($inputs)
	{
		foreach($inputs as $key => $input)
		{
			$this->db->where($key, $input);
		}
		$this->db->order_by('timestamp', 'ASC');
		$this->db->limit(1);
		
		return $this->db->delete('cash_till');
	}
}
?>
