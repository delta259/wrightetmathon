<?php
class Transaction extends CI_Model
{
	// get the transaction code given the transaction subtype, ie code = 'CMDE-' if mode = 'purchaseorder' 
	public function	get_transaction_code	($mode)
	{		
		$query = $this->db->query	(
									"SELECT 	`transaction_code` 
									FROM  		".$this->db->dbprefix('transaction_type')." 
									WHERE  		transaction_subtype = '$mode'
									");
		
		foreach ($query->result_array() as $row)
		{
			$code = $row['transaction_code'];
		}
		
		return $code;
	}

	// get the transaction subtypes 
	// this routine returns a list of options for dropdown 
	public function get_transaction_modes()
	{	
		$options = array();
		
		$query = $this->db->query	(
									"SELECT 	`transaction_subtype` 
									FROM  		".$this->db->dbprefix('transaction_type')."
									ORDER BY 	transaction_subtype"
									);
		
		foreach ($query->result() as $row)
		{
			$options[$row->transaction_subtype] = $this->lang->line('reports_'.$row->transaction_subtype);
		}
		
		return $options;
	}
		
	
	public function get_transaction_modesZ()
	{	
		$optionsZ = array();
		
		$query = $this->db->query	(
									"SELECT 	`transaction_subtype` 
									FROM  		".$this->db->dbprefix('transaction_type')."
									WHERE    transaction_subtype = 'sales'
									ORDER BY 	transaction_subtype"
									);
		
		foreach ($query->result() as $row)
		{
			$optionsZ[$row->transaction_subtype] = $this->lang->line('reports_'.$row->transaction_subtype);
		}
		
		return $optionsZ;
	}
	//for le ticket_Z
		
	// load stock actions pick list
	function load_stock_actions_pick_list()
	{
		// initialise
		unset($_SESSION['G']->stock_actions_pick_list);
		$stock_actions_pick_list										=	array();
		
		// define file name
		$file_name														=	"/var/www/html/wrightetmathon/application/definitions/stock_actions.def";
		
		// open definitions file
		$fp																=	fopen($file_name, "r");
		
		// test if file was opened
		if (!$fp)
		{
			$_SESSION['error_code']										=	'05680';
			$_SESSION['substitution_parms']								=	array($file_name);
			redirect("items");
		}
		
		// test EOF
		while(!feof($fp)) 
		{
			// read a line
			$line														=	fgets($fp);
			
			// ignore comment lines
			if (strpos($line, "//") === false) 
			{
				// explode the line to find the fields required
				// $bulk_actions[0] = bulk_action_id
				// $bulk_actions[1] = bulk_action_name
				// $bulk_actions[2] = bulk_action_language_file
				// $bulk_actions[3] = deleted
				// $bulk_actions[4] = unused, should be blank
				$stock_actions											=	explode("->", $line);
				
				// test active
				if ($stock_actions[3] == '0')
				{
					// load the bulk actions pick list
					$stock_actions_pick_list[$stock_actions[0]] 		=	$this->lang->line($stock_actions[2].'_'.$stock_actions[1]);
				}
			}
		}
		
		// at EOF close the file
		fclose($fp);
		
		// load the bulk action pick list to the session
		$_SESSION['G']->stock_actions_pick_list							=	$stock_actions_pick_list;
		
		// return
		return;
	}

	// get the transaction subtypes 
	// this routine returns a list of options for dropdown 
	public function get_transaction_modes_specific($transaction_type)
	{	
		$options = array();
		
		$query = $this->db->query	(
									"SELECT 	`transaction_subtype` 
									FROM  		".$this->db->dbprefix('transaction_type')."
									WHERE		transaction_type = '$transaction_type'
									ORDER BY 	transaction_subtype"
									);
		
		foreach ($query->result() as $row)
		{
			$options[$row->transaction_subtype] = $this->lang->line('reports_'.$row->transaction_subtype);
		}

		return $options;
	}
	
	// get the transaction type given the transaction subtype ie subtype = 'purchaseorder', type = 'SM'
	function get_transaction_type($transaction_subtype)
	{		
		$query = $this->db->query	(
									"SELECT 	`transaction_type`
									FROM  		".$this->db->dbprefix('transaction_type')." 
									WHERE  		`transaction_subtype` = '$transaction_subtype'"
									);
									
		foreach ($query->result_array() as $trow)
			{
				$report_type = $trow['transaction_type'];
			}
			
		return $report_type;
	}
	
	// get the transaction muliplier given the transaction subtype/transaction_code pair
	function get_transaction_multiplier($transaction_subtype, $transaction_code)
	{				
		$query = $this->db->query	(
									"SELECT 	`transaction_multiplier`
									FROM  		".$this->db->dbprefix('transaction_multiplier')." 
									WHERE  		`transaction_subtype` = '$transaction_subtype' AND `transaction_code` =  '$transaction_code'"
									);
									
		foreach ($query->result_array() as $trow)
			{
				$transaction_multiplier = $trow['transaction_multiplier'];
			}
			
		return $transaction_multiplier;
	}
    function update_config(){
        // create update sql
        $this->db->query("UPDATE `ospos_app_config` SET `value`='2/,/ ' WHERE `key`='numberformat'");

    }
	// get the transaction updatestock given the transaction subtype
	function get_transaction_updatestock($transaction_subtype)
	{				
		$query = $this->db->query	(
									"SELECT 	`transaction_updatestock`
									FROM  		".$this->db->dbprefix('transaction_type')." 
									WHERE  		`transaction_subtype` = '$transaction_subtype'"
									);
									
		foreach ($query->result_array() as $trow)
			{
				$transaction_updatestock = $trow['transaction_updatestock'];
			}
		
		return $transaction_updatestock;
	}
	
	// get the transaction sortby 
	// this routine returns a list of sortby for dropdown 
	
	public function get_transaction_sortbyZ()

	{	
		$optionsZ = array();
		
		$query = $this->db->query	(
									"SELECT 	`transaction_sortby` 
									FROM  		".$this->db->dbprefix('transaction_sortby')."
									WHERE transaction_sortby = 'payment'  OR transaction_sortby = 'date' 
									ORDER BY	transaction_sortby desc
									"
									);
		
		foreach ($query->result() as $row)
		{
			$sortbyZ[$row->transaction_sortby] = $this->lang->line('reports_sortby_'.$row->transaction_sortby);
		}
		
		return $sortbyZ;
	}
    //for le ticket_z

	public function get_transaction_sortby()
	{	
		$options = array();
		
		$query = $this->db->query	(
									"SELECT 	`transaction_sortby` 
									FROM  		".$this->db->dbprefix('transaction_sortby')."
									ORDER BY	transaction_sortby"
									);
		
		foreach ($query->result() as $row)
		{
			$sortby[$row->transaction_sortby] = $this->lang->line('reports_sortby_'.$row->transaction_sortby);
		}
		
		return $sortby;
	}
	
	// get the transaction model path and model name given the sortby
	function get_transaction_sortby_data($transaction_sortby)
	{				
		$query = $this->db->query	(
									"SELECT 	
											sales_transaction_model_path, 
											sales_transaction_model_name,
											sales_graph_yaxis, 
											sales_graph_xaxis, 
											sales_graph_type, 
											sales_graph_label, 
											sales_graph_value, 
											receivings_transaction_model_path, 
											receivings_transaction_model_name,
											receivings_graph_yaxis, 
											receivings_graph_xaxis, 
											receivings_graph_type, 
											receivings_graph_label, 
											receivings_graph_value 
											
									FROM  	".$this->db->dbprefix('transaction_sortby')." 
									WHERE  	`transaction_sortby` = '$transaction_sortby'"
									);
									
		foreach ($query->result_array() as $trow)
			{
				$sortby_data['sales_transaction_model_path'] 		= $trow['sales_transaction_model_path'];
				$sortby_data['sales_transaction_model_name'] 		= $trow['sales_transaction_model_name'];
				$sortby_data['sales_graph_yaxis']					= $trow['sales_graph_yaxis'];
				$sortby_data['sales_graph_xaxis']					= $trow['sales_graph_xaxis'];
				$sortby_data['sales_graph_type']					= $trow['sales_graph_type'];
				$sortby_data['sales_graph_label']					= $trow['sales_graph_label'];
				$sortby_data['sales_graph_value']					= $trow['sales_graph_value'];
				$sortby_data['receivings_transaction_model_path'] 	= $trow['receivings_transaction_model_path'];
				$sortby_data['receivings_transaction_model_name'] 	= $trow['receivings_transaction_model_name'];
				$sortby_data['receivings_graph_yaxis']				= $trow['receivings_graph_yaxis'];
				$sortby_data['receivings_graph_xaxis']				= $trow['receivings_graph_xaxis'];
				$sortby_data['receivings_graph_type']				= $trow['receivings_graph_type'];
				$sortby_data['receivings_graph_label']				= $trow['receivings_graph_label'];
				$sortby_data['receivings_graph_value']				= $trow['receivings_graph_value'];
			}
		
		return $sortby_data;
	}
	
	// get the transaction model path and model name given the sortby
	function get_transaction_column_data($transaction_sortby)
	{				
		$query = $this->db->query	(
									"SELECT 	sales_column1, receivings_column1, sales_column2, receivings_column2
									FROM  		".$this->db->dbprefix('transaction_sortby')." 
									WHERE  		`transaction_sortby` = '$transaction_sortby'"
									);
									
		foreach ($query->result_array() as $trow)
		{
			$column_data['sales_column1'] 		= $trow['sales_column1'];
			$column_data['receivings_column1'] 	= $trow['receivings_column1'];
			$column_data['sales_column2'] 		= $trow['sales_column2'];
			$column_data['receivings_column2'] 	= $trow['receivings_column2'];
		}
		
		return $column_data;
	}
	
	function array_msort($array, $cols)
	{
		$colarr = array();
		foreach ($cols as $col => $order) {
			$colarr[$col] = array();
			foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
		}
		$eval = 'array_multisort(';
		foreach ($cols as $col => $order) {
			$eval .= '$colarr[\''.$col.'\'],'.$order.',';
		}
		$eval = substr($eval,0,-1).');';
		eval($eval);
		$ret = array();
		foreach ($colarr as $col => $arr) {
			foreach ($arr as $k => $v) {
				$k = substr($k,1);
				if (!isset($ret[$k])) $ret[$k] = $array[$k];
				$ret[$k][$col] = $array[$k][$col];
			}
		}
		return $ret;
	}
	
	function transaction_type()
	{
		// hidrive is open
		// initialise
		$file_found														=	'N';
		
		// open initial.sql for the software folder I am using
		$file															=	"/home/wrightetmathon/.hidrive.sonrisa/POS_SYSTEM/DATABASE_INITIAL_SETUP/".$_SESSION['software_folder_name']."/initial.sql";
		$infile 														=	fopen($file, "r") or die("Unable to open initial.sql file! You have a major problem. Contact your system administrator.");
		
		// read the file to find the transaction_type file
		while(!feof($infile)) 
		{
			// read a line until I find what I am looking for
			$line														=	fgets($infile);
			
			// test it to see if I found what I am looking for
			if (strpos($line, 'INSERT INTO `ospos_transaction_type`') !== false) 
			{
				// read next line = first line of values 
				$line													=	fgets($infile);
				
				// now load the values until end of values is reached
				while (strpos($line, '(') !== false)
				{					
					// read the values into an array
					$insert_row_array									=	array();
					$insert_row_array									=	explode(',', $line);

					// ltrim the ID
					$transaction_ID										=	ltrim($insert_row_array[0], "(");
					
					// get the record
					$transaction_info									=	$this->get_transaction_id($transaction_ID)->result_array();

					// test found
					if (!$transaction_info)
					{
						// set up fields for insertion
						// transaction_type
						$transaction_type								=	trim($insert_row_array[1]); 		// remove spaces
						$transaction_type								=	trim($transaction_type, "'"); 		// remove winkers
						// transaction_subtype
						$transaction_subtype							=	trim($insert_row_array[2]); 		// remove spaces
						$transaction_subtype							=	trim($transaction_subtype, "'"); 	// remove winkers
						// transaction_code
						$transaction_code								=	trim($insert_row_array[3]); 		// remove spaces
						$transaction_code								=	trim($transaction_code, "'"); 		// remove winkers
						// update_stock						
						$update_stock									=	trim($insert_row_array[4]); 		// remove spaces
						$update_stock									=	rtrim($update_stock, ");");			// remove );
						$update_stock									=	trim($update_stock, "'");			// remove winkers
						
						// set up insert data arrray
						$insert_data									=	array();
						$insert_data									=	array	(
																					transaction_ID 			=>	$transaction_ID,
																					transaction_type		=>	$transaction_type,
																					transaction_subtype		=>	$transaction_subtype,
																					transaction_code		=>	$transaction_code,
																					transaction_updatestock	=>	$update_stock
																					);
						
						// insert the record
						$this											->	insert_transaction_type($insert_data);	
					}
					else
					{
						// trim subtype
						$transaction_subtype							=	trim($insert_row_array[2]); 		// remove spaces
						$transaction_subtype							=	trim($transaction_subtype, "'"); 	// remove winkers
						// transaction_code
						$transaction_code								=	trim($insert_row_array[3]); 		// remove spaces
						$transaction_code								=	trim($transaction_code, "'"); 		// remove winkers
						// test if transaction_subtype has changed
						if ($transaction_info[0]['transaction_subtype'] != $transaction_subtype OR $transaction_info[0]['transaction_code'] != $transaction_code)
						{
							// set up update array
							$update_data								=	array	(
																					transaction_subtype		=>	$transaction_subtype,
																					transaction_code		=>	$transaction_code
																					);
							// update the transaction type row
							$this										->	update_transaction_subtype($transaction_ID, $update_data);
							
							// now update the receivings header file
							$update_mode								=	array	(
																					mode					=>	$transaction_subtype
																					);
							$this										->	Receiving->update_mode($update_mode, $transaction_info[0]['transaction_subtype']);
						}
					}
					
					// now read next line
					$line												=	fgets($infile);
				}
				
				// if here I am end of values list so shut down
				fclose($infile);
				return;
			}
		}

		// if here then EOF reached so shut down
		fclose($infile);
		return;
	}
	
	function exists_transaction_type($transaction_ID)
	{
		$this															->	db->from('transaction_type');
		$this															->	db->where('transaction_ID',$transaction_ID);
		$query															=	$this->db->get();

		return 															($query->num_rows()==1);
	}
	
	function insert_transaction_type($insert_data)
	{
		$this															->	db->insert('transaction_type', $insert_data);

		return;
	}
	
	function update_transaction_subtype($transaction_ID, $update_data)
	{
		$this															->	db->where('transaction_id', $transaction_ID);
		$this															->	db->update('transaction_type', $update_data);

		return;
	}
	
	function transaction_multiplier()
	{
		// FIXME
		return;
	}
	
	function get_transaction_id($transaction_ID)
	{
		$this															->	db->from('transaction_type');
		$this															->	db->where('transaction_ID',$transaction_ID);
		$query															=	$this->db->get();

		return 															$query;
	}
}

