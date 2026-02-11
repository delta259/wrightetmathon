<?php

// Maintain the database

	// Maintain tables
	function maintain_database()
	{
		
		
//---------------------------------------------------------------------------------------------------------------------------- 
// Section 0 - initialise
//---------------------------------------------------------------------------------------------------------------------------- 

		// get the instance
		$CI 										=&	get_instance();	

		// I am calling this routine from a post_controller_constructor hook, so it is called on every controller
		// but I only want to run this once per session 
		if (file_exists('/var/www/html/dummy.txt'))
		{
			return;
		}
		
		// load dbforge
		$CI											->	load->dbforge();
		
		// get the branch code
		$branch_code								=	$CI->config->item('branch_code');

//---------------------------------------------------------------------------------------------------------------------------- 
// Section 1 - create the tables if they don't exist
//---------------------------------------------------------------------------------------------------------------------------- 
		
		// get table names
		$CI											->	db->from('database_tables');
		$CI											->	db->order_by('table_sequence asc'); 	
		$tables										= 	$CI->db->get()->result_array();
		
		// read table names
		foreach ($tables as $table)
		{
			// initialise
			$i_created_a_table						=	0;
			$db_prefix_table_name					=	$CI->db->dbprefix($table['table_name']);
			$dummy_field							=	'field_dummy';
			
			// check table exists
			if (!$CI->db->table_exists($table['table_name']))
			{
				// table does not exist so create it
				$new_field							=	array	(
																$dummy_field	=> 	array	(
																							'type' 			=>	'VARCHAR',
																							'constraint' 	=> 	'100',
																							'default' 		=> 	'This is a dummy field. Delete it if you find it'
																							)
																);	
				
				// add the field
				$CI									->	dbforge->add_field($new_field);
				$i_created_a_dummy_field			=	1;
				
				// and create the table
				$CI									->	dbforge->create_table($table['table_name']);

				// set the create table indicator
				$i_created_a_table					=	1;

			}	
				
			// now the table exists so get the fields	
			$CI										->	db->from('database_tables_schema');
			$CI										->	db->where('table_name', $table['table_name']);
			$fields									=	$CI->db->get()->result_array();

			foreach ($fields as $field)
			{
				// if field does not exist, create it
				if (!$CI->db->field_exists($field['field_name'], $table['table_name']))
				{
					// codeigniter has limitations for certain field types ie TIMESTAMP and TEXT 
					// so they need to be handled separately
					switch ($field['field_type']) 
					{
						case "TIMESTAMP":
							$query					=	'ALTER TABLE `'.$db_prefix_table_name.'` ADD `'.$field['field_name'].'` TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
							break;
						case "TEXT":
							$query					=	'ALTER TABLE `'.$db_prefix_table_name.'` ADD `'.$field['field_name'].'` TEXT';
							break;
						case "VARCHAR":
							$query					=	'ALTER TABLE `'.$db_prefix_table_name.'` ADD `'.$field['field_name'].'` '.$field['field_type'].' ('.$field['field_constraint'].')';
							break;
						case "CHAR":
							$query					=	'ALTER TABLE `'.$db_prefix_table_name.'` ADD `'.$field['field_name'].'` '.$field['field_type'].' ('.$field['field_constraint'].')';
							break;
						default:
							$query					=	'ALTER TABLE `'.$db_prefix_table_name.'` ADD `'.$field['field_name'].'` '.$field['field_type'].' ('.$field['field_constraint'].') NOT NULL DEFAULT '.$field['field_default'];
					}
					
					// add the column
					$CI								->	db->query($query);
					
					// now remove the dummy field
					if ($i_created_a_dummy_field == 1)
					{	
						$query						=	'ALTER TABLE `'.$db_prefix_table_name.'` DROP `'.$dummy_field.'`';
						$CI							->	db->query($query);
						$i_created_a_dummy_field	=	0;
					}

					// check for key and add if required 
					switch ($field['field_key']) 
					{
						case "PRIMARY":
							$CI						->	db->query('ALTER TABLE '.$db_prefix_table_name.' ADD PRIMARY KEY (`'.$field['field_name'].'`)');
							break;
						case "SECONDARY":
							$CI						->	db->query('ALTER TABLE '.$db_prefix_table_name.' ADD KEY (`'.$field['field_name'].'`)');
							break;
					}	
					
					// check for auto increment and correct field definition if required
					if ($field['field_auto_increment'] == 'TRUE' AND $field['field_type'] == 'INT')
					{
						// make it auto increment
						$new_field					=	array	(
																$field['field_name']	=> 	array	(
																									'type' 				=> 	$field['field_type'],
																									'constraint' 		=> 	$field['field_constraint'],
																									'auto_increment'	=> 	TRUE
																									)
																);	
						$CI							->	dbforge->modify_column($table['table_name'], $new_field);
					}

					// check for NULL fields
					if ($field['field_null'] == 'TRUE')
					{
						//  make it allow null
						$new_field					=	array	(
																$field['field_name']	=> 	array	(
																									'type' 				=> 	$field['field_type'],
																									'constraint' 		=> 	$field['field_constraint'],
																									'null'				=> 	TRUE,
																									)
																);	
						$CI								->	dbforge->modify_column($table['table_name'], $new_field);
					}
				}
			}

//---------------------------------------------------------------------------------------------------------------------------- 
// Section 2 - Add the data
//---------------------------------------------------------------------------------------------------------------------------- 
	
			// now add data to the field from the schema file
			if ($i_created_a_table == 1 AND $table['table_initial_data'] == 1)
			{
				// check for external data file 
				if ($table['table_initial_data_file'] == NULL)
				{
					// external file is not specified, use schema initial data
					$index							=	0;
					$field_names					=	array();
					$column_data					=	array();
					
					foreach ($fields as $field)
					{
						$field_names[$index]		=	$field['field_name'];			
						$column_data[$index]		=	explode(':', $field['field_initial_data']);
						$index						=	$index	+	1;
					}
						
					// now get the array counts and calculate how many elements in inner array
					$outer_count					=	count($column_data);
					$inner_count					=	count($column_data, COUNT_RECURSIVE);
					$number_of_rows					=	ceil(($inner_count - $outer_count) / $outer_count);
					
					// loop through the column data to get row data, 
					// merge field names and row data to create output array
					// insert to table
					$inner_index					=	0;
					$row_data						=	array();
					$data							=	array();
					
					while ($inner_index < $number_of_rows)
					{	
						$row_data					=	array_column($column_data, $inner_index);						
						$data						=	array_combine($field_names, $row_data);
						
						$CI							->	db->insert($table['table_name'], $data); 
						$inner_index				=	$inner_index + 1;
					}	
				}
				else
				{
					// check file exists before continuing
					if (is_file($table['table_initial_data_file']))
					{
						// external data file name is specified, so get it
						include $table['table_initial_data_file'];
								
						// now use a variable variable to get the table name from the database and load the data
						foreach (${$table['table_initial_data_array_name']} as $data)
						{
							$CI						->	db->insert($table['table_name'], $data);
						}
					}
				}
			}
				
//---------------------------------------------------------------------------------------------------------------------------- 
// Section 3 - housekeeping
//---------------------------------------------------------------------------------------------------------------------------- 
						
			if ($i_created_a_table == 1)
			{
				// get all fields which have to be reset and update the table
				foreach ($fields as $field)
				{
					if ($field['field_initial_data_reset'] == 1)
					{
						$update_data				=	array	(
																''.$field['field_name'].''	=>	0
																);
						$CI							->	db->update($table['table_name'], $update_data);
					}		
				}
			}	
		}
		
		// now fix supplier ID in item table
		// get the supplier code
		$CI											->	db->from('suppliers');
		$CI											->	db->where('company_name', 'Sonrisa');
		$supplier_info								=	$CI->db->get()->row_array();
		
		// update the items
		$update_data								=	array	(
																'supplier_id'	=>	$supplier_info['person_id']
																);
		$CI											->	db->update('items', $update_data);
		
		// now delete blank rows in item table
		$CI											->	db->from('items');
		$items_info									=	$CI->db->get()->result_array();

		foreach ($items_info as $item)
		{
			if ($item['name'] == NULL)
			{
				$CI									->	db->where('item_id', $item['item_id']);
				$CI									->	db->delete('items');
			}
		}
		
		// now fix item_taxes to match items
		// get the default tax name
		$CI											->	db->from('app_config');
		$CI											->	db->where('key', 'default_tax_1_name');
		$tax_name_info								=	$CI->db->get()->row_array();
		$tax_name									=	$tax_name_info['value'];
		
		// get the default tax rate
		$CI											->	db->from('app_config');
		$CI											->	db->where('key', 'default_tax_1_rate');
		$tax_rate_info								=	$CI->db->get()->row_array();
		$tax_percent								=	$tax_rate_info['value'];
		
		// read items
		$CI											->	db->from('items');
		$items_info									=	$CI->db->get()->result_array();
		
		foreach ($items_info as $item)
		{
			// get tax record
			$CI										->	db->from('items_taxes');
			$CI										->	db->where('item_id', $item['item_id']);		
			$found									=	$CI->db->get()->row_array();
			
			// if not found, create it
			if (empty($found))		
			{
				$tax_data							=	array	(
																'item_id'	=>	$item['item_id'],
																'name'		=>	$tax_name,
																'percent'	=>	$tax_percent
																);
				$CI									->	db->insert('items_taxes', $tax_data);
			}
		}
		
		// now get rid of records in item_taxes where no item record exists
		// read items_taxes
		$CI											->	db->from('items_taxes');
		$items_taxes_info							=	$CI->db->get()->result_array();
		
		foreach ($items_taxes_info as $taxes)
		{
			$CI										->	db->from('items');
			$CI										->	db->where('item_id', $taxes['item_id']);		
			$found									=	$CI->db->get()->row_array();
			
			if (empty($found))		
			{
				$CI									->	db->where('item_id', $taxes['item_id']);
				$CI									->	db->delete('items_taxes');
			}
		}
	}

//---------------------------------------------------------------------------------------------------------------------------- 
// Routine to create a dummy file - the purpose is to stop the main routine from running on every controller call.
//---------------------------------------------------------------------------------------------------------------------------- 		
	function maintain_database_status_1()
	{		
		// write the file
		touch('/var/www/html/dummy.txt');
		
	}

?>
