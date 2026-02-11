<?php
class Defects extends CI_Controller
{	
		// -----------------------------------------------------------------------------------------				
	// Update purchase prices in the database from downloaded csv sheet
	// -----------------------------------------------------------------------------------------
	
	function create_defects()
	{
		// load model
		$this->load->model('Item');
		
		// initalise counts
		$number_of_records						=	0;
		$number_of_updates						=	0;
			
		// set output CSV name - records updated are output here
		$this									->	load->helper('date');
		$now									=	time();
		$pieces									=	explode(".", $this->config->item('PPfilename'));
		$csv_data_file = $this->config->item('PPsavepath').'DEFECTS_update_'.$now.'.csv';
		
		// write column headers
		file_put_contents($csv_data_file,		'Item_number',		FILE_APPEND);
		file_put_contents($csv_data_file,		';',				FILE_APPEND);
		file_put_contents($csv_data_file,		'Description',		FILE_APPEND);
		file_put_contents($csv_data_file,		';',				FILE_APPEND);
		file_put_contents($csv_data_file,		"\n",				FILE_APPEND);
		
		// open the csv file containing new stuff 
		if (($handle = fopen($this->config->item('PPsavepath').'DEFECTS.csv', "r")) === FALSE) 
		{
			$success_or_failure					=	'F';
			$message							=	'DEFECTS update failed - file not found.';
			$this								->	setflash($success_or_failure, $message);
		}
		else
		{
		// now read it
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) 
			{
				// count records read
				$number_of_records				=	$number_of_records + 1;
				
				// test item exists
				$this							->	db->from('items');
				$this							->	db->where('item_number', $data[0]);
				$this							->	db->where('items.branch_code', $this->config->item('branch_code'));
				
				if (!$this->db->get()->row_array())
				{
					// item doesn't exist so create it
					$defect_data				=	array	(
															'name'					=>	$data[1],
															'description'			=>	' ',
															'category'				=>	'DEFECT',
															'supplier_id'			=>	'2',
															'item_number'			=>	$data[0],
															'cost_price'			=>	$data[2],
															'unit_price'			=>	0,
															'reorder_level'			=>	0,
															'reorder_quantity'		=>	0,
															'reorder_pack_size'		=>	0,
															'reorder_policy'		=>	'N',
															'location'				=>	' ',
															'allow_alt_description'	=>	'0',
															'is_serialized'			=>	'0',
															'DynamicKit'			=>	0,
															'custom1'				=>	' ',			
															'custom2'				=>	' ',
															'custom3'				=>	' ',
															'custom4'				=>	' ',
															'custom5'				=>	' ',
															'custom6'				=>	' ',
															'custom7'				=>	' ',
															'custom8'				=>	' ',
															'custom9'				=>	' ',
															'custom10'				=>	' ',
															'dluo_indicator'		=>	'N',
															'giftcard_indicator'	=>	'N',
															'kit_reference'			=>	' ',
															'barcode'				=>	' ',
															'branch_code'			=>	$this->config->item('branch_code')
															);										
					$this->db->insert('items', $defect_data);
				
					$number_of_updates			=	$number_of_updates + 1;
					
					file_put_contents($csv_data_file,	$data[0],				FILE_APPEND);
					file_put_contents($csv_data_file,	';',					FILE_APPEND);
					file_put_contents($csv_data_file,	$data[1],				FILE_APPEND);
					file_put_contents($csv_data_file,	';',					FILE_APPEND);
					file_put_contents($csv_data_file,	"\n",					FILE_APPEND);
				}
			}
			
			// close the input file
			fclose($handle);
		
			// set flash data and return to controller
			$success_or_failure				=	'S';
			$message						=	'DEFECT items update finished. Records read  '.$number_of_records.', number of updates  '.$number_of_updates.'.';
			$this							->	setflash($success_or_failure, $message);
		}
	}
	
	// set the flash data
	function setflash($success_or_failure, $message)
	{
		$this							->	session->set_flashdata('success_or_failure', $success_or_failure);
		$this							->	session->set_flashdata('message', $message);
		redirect('reports');
		return;
	}
}
?>	
