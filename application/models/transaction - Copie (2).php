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
		
		//correction des profits
		$sql_corrections = $this->db->query("
		SELECT tab.`sale_id`, tab.diff, tab.profit 
		FROM (SELECT `ospos_sales`.`sale_id`, (`ospos_sales`.`overall_profit` - SUM(`ospos_sales_items`.`line_profit`)) as diff, SUM(`ospos_sales_items`.`line_profit`) as profit 
			  FROM `ospos_sales`, `ospos_sales_items` 
			  WHERE `ospos_sales_items`.`sale_id` = `ospos_sales`.`sale_id` 
			  GROUP BY `ospos_sales`.`sale_id`) as tab 
		WHERE tab.diff <> 0;");
        $corrections = $sql_corrections->result_array();
		foreach($corrections as $line => $correction)
		{
			$corrections = $this->db->query("UPDATE `ospos_sales` SET `ospos_sales`.`overall_profit` = " . $correction['profit'] . " WHERE `ospos_sales`.`sale_id` = " . $correction['sale_id'] . " ");
		}



//ALTER TABLE `ospos_receivings` ADD `number_day_sale` INT(15) NULL DEFAULT NULL AFTER `mode`;


		//MAJ DB for ospos_receivings
        $sql_ospos_receivings_number_day_sale =  $this->db->query("SHOW COLUMNS FROM `ospos_receivings` LIKE 'number_day_sale';");
        $sql_ospos_receivings_number_day_sale = $sql_ospos_receivings_number_day_sale->result_array();
        
        if(!isset($sql_ospos_receivings_number_day_sale[0]['Field']))
        {
        	$sql = $this->db->query("ALTER TABLE `ospos_receivings` ADD `number_day_sale` INT(15) NULL DEFAULT NULL AFTER `mode`;");
		}
		
		$sql_ospos_receivings_number_day_prevision_stock =  $this->db->query("SHOW COLUMNS FROM `ospos_receivings` LIKE 'number_day_prevision_stock';");
        $sql_ospos_receivings_number_day_prevision_stock = $sql_ospos_receivings_number_day_prevision_stock->result_array();
        
        if(!isset($sql_ospos_receivings_number_day_prevision_stock[0]['Field']))
        {
        	$sql = $this->db->query("ALTER TABLE `ospos_receivings` ADD `number_day_prevision_stock` INT(15) NULL DEFAULT NULL AFTER `number_day_sale`;");
		}

		//    END    MAJ DB for ospos_receivings

		//MAJ DB for ospos_employees

        $sql_ospos_employees_autorisation =  $this->db->query("SHOW COLUMNS FROM `ospos_employees` LIKE 'autorisation';");
        $sql_ospos_employees_autorisation = $sql_ospos_employees_autorisation->result_array();
        
        if(!isset($sql_ospos_employees_autorisation[0]['Field']))
        {
        	$sql = $this->db->query("ALTER TABLE `ospos_employees` ADD `autorisation` BOOLEAN NOT NULL DEFAULT TRUE AFTER `sales_number_of`;");
        }

		//    END    MAJ DB for ospos_employees

        //MAJ DB for ospos_sales_suspended

		$sql_ospos_sales_suspended_cancel_indicator =  $this->db->query("SHOW COLUMNS FROM `ospos_sales_suspended` LIKE 'cancel_indicator';");
		$sql_ospos_sales_suspended_cancel_indicator = $sql_ospos_sales_suspended_cancel_indicator->result_array();
		
		if(!isset($sql_ospos_sales_suspended_cancel_indicator[0]['Field']))
		{
			$sql = $this->db->query("ALTER TABLE `ospos_sales_suspended` ADD `cancel_indicator` VARCHAR(50) NULL DEFAULT 'N' AFTER `payment_type`;");
		}
        
		//    END    MAJ DB for ospos_sales_suspended
        
        
		//MAJ DB for ospos_item_kits
	
		$sql_ospos_item_kits_kit_item_id = $this->db->query("SHOW COLUMNS FROM `ospos_item_kits` LIKE 'kit_item_id';");
		$sql_ospos_item_kits_kit_item_id = $sql_ospos_item_kits_kit_item_id->result_array();
		
		if(!isset($sql_ospos_item_kits_kit_item_id[0]['Field']))
		{
			$sql = $this->db->query("ALTER TABLE `ospos_item_kits` ADD `kit_item_id` INT(15) NOT NULL DEFAULT '0' AFTER `item_kit_id`;");
		}

		$sql_ospos_item_kits_item_kit_id =  $this->db->query("SHOW COLUMNS FROM `ospos_item_kits` LIKE 'item_kit_id';");
		$sql_ospos_item_kits_item_kit_id = $sql_ospos_item_kits_item_kit_id->result_array();
		
		if($sql_ospos_item_kits_item_kit_id[0]['Key'] == 'PRI')
		{
			$sql = $this->db->query('ALTER TABLE `ospos_item_kits` CHANGE `item_kit_id` `item_kit_id` INT(11) NOT NULL;');
		}

		$sql_ospos_item_kits_kit_item_id =  $this->db->query("SHOW COLUMNS FROM `ospos_item_kits` LIKE 'kit_item_id';");
		$sql_ospos_item_kits_kit_item_id = $sql_ospos_item_kits_kit_item_id->result_array();
		
		if(!isset($sql_ospos_item_kits_kit_item_id[0]['Default']))
		{
			$sql = $this->db->query("ALTER TABLE `ospos_item_kits` CHANGE `kit_item_id` `kit_item_id` INT(15) NOT NULL DEFAULT '0';");
		}

		$sql_ospos_item_kits_barcode =  $this->db->query("SHOW COLUMNS FROM `ospos_item_kits` LIKE 'barcode';");
		$sql_ospos_item_kits_barcode = $sql_ospos_item_kits_barcode->result_array();
		
		if(!isset($sql_ospos_item_kits_barcode[0]['Field']))
		{
			$sql = $this->db->query("ALTER TABLE `ospos_item_kits` ADD `barcode` VARCHAR(500) NOT NULL AFTER `description`;");
		}
		$sql_ospos_item_kits_delted = $this->db->query("SHOW COLUMNS FROM `ospos_item_kits` LIKE 'deleted'");
		$sql_ospos_item_kits_delted = $sql_ospos_item_kits_delted->result_array();
		if(!isset($sql_ospos_item_kits_delted[0]['Field']))
		{    //add deleted colonne if not exist
			$sql = $this->db->query("ALTER TABLE `ospos_item_kits` ADD `deleted` INT(1) NOT NULL DEFAULT '0' AFTER `barcode`;");
		}	

		//    END    MAJ DB for ospos_item_kits

		
		//MAJ DB for ospos_items


		

		$sql_ospos_item_kit =  $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'kit';");
		$sql_ospos_item_kit = $sql_ospos_item_kit->result_array();
		
		if(!isset($sql_ospos_item_kit[0]['Field']))
		{
			$sql = $this->db->query('ALTER TABLE `ospos_items` ADD `kit` INT(15) NULL AFTER `kit_reference`;');
		}


		$sql_ospos_items_quantity_central = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'quantity_central';");
		$sql_ospos_items_quantity_central = $sql_ospos_items_quantity_central->result_array();
		
		if(!isset($sql_ospos_items_quantity_central[0]['Field']))
		{
            $sql = $this->db->query('ALTER TABLE `ospos_items` ADD `quantity_central` INT(15) NOT NULL DEFAULT "0" AFTER `quantity`;');
		}

		$sql_ospos_items_custom1 = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'custom1';");
		$sql_ospos_items_offer_value = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'offer_value';");
		$sql_ospos_items_custom1 = $sql_ospos_items_custom1->result_array();
		$sql_ospos_items_offer_value = $sql_ospos_items_offer_value->result_array();
		
		if(isset($sql_ospos_items_custom1[0]['Field']) && (!isset($sql_ospos_items_offer_value[0]['Field'])))
		{
			//$sql=$this->db->query("ALTER TABLE `ospos_items` CHANGE `custom1` `offer_value` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
			//$sql=$this->db->query("ALTER TABLE `ospos_items` CHANGE `offer_value` `offer_value` DOUBLE(12,2) NULL DEFAULT '0';");
			$sql = $this->db->query("ALTER TABLE `ospos_items` CHANGE `custom1` `offer_value` DOUBLE(12,2) NULL DEFAULT '0.00';");
			$sql = $this->db->query('UPDATE `ospos_items` SET `offer_value` = 0;');
		}
		if(!isset($sql_ospos_items_custom1[0]['Field']) && (!isset($sql_ospos_items_offer_value[0]['Field'])))
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` ADD `offer_value` DOUBLE(12,2) NULL DEFAULT '0.00' AFTER `sales_qty`;");
			$sql = $this->db->query('UPDATE `ospos_items` SET `offer_value` = 0;');
			
		}

		$sql_ospos_items_custom2 = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'custom2';");
		$sql_ospos_items_emplacement = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'emplacement';");
		$sql_ospos_items_custom2 = $sql_ospos_items_custom2->result_array();
		$sql_ospos_items_emplacement = $sql_ospos_items_emplacement->result_array();
		
		if(isset($sql_ospos_items_custom2[0]['Field']) && (!isset($sql_ospos_items_emplacement[0]['Field'])))
		{
			//$sql=$this->db->query("ALTER TABLE `ospos_items` CHANGE `custom1` `offer_value` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
			//$sql=$this->db->query("ALTER TABLE `ospos_items` CHANGE `offer_value` `offer_value` DOUBLE(12,2) NULL DEFAULT '0';");
			$sql = $this->db->query("UPDATE `ospos_items` SET `custom2` = NULL;");
			$sql = $this->db->query("ALTER TABLE `ospos_items` CHANGE `custom2` `emplacement` INT(15) NULL DEFAULT NULL;");
		}
		if(!isset($sql_ospos_items_custom2[0]['Field']) && (!isset($sql_ospos_items_emplacement[0]['Field'])))
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` ADD `emplacement` INT(15) NULL DEFAULT NULL AFTER `offer_value`;");
		}
		
		$sql = $this->db->query('UPDATE `ospos_items` SET `custom3` = 0;');
		$sql = $this->db->query('UPDATE `ospos_items` SET `custom4` = 0;');
		$sql = $this->db->query('UPDATE `ospos_items` SET `custom5` = 0;');
		$sql = $this->db->query('UPDATE `ospos_items` SET `custom6` = 0;');
		$sql = $this->db->query('UPDATE `ospos_items` SET `custom7` = 0;');
		$sql = $this->db->query('UPDATE `ospos_items` SET `custom8` = 0;');
		$sql = $this->db->query('UPDATE `ospos_items` SET `custom9` = 0;');
		$sql = $this->db->query('UPDATE `ospos_items` SET `custom10` = 0;');
		



		//ALTER TABLE `ospos_items` ADD `vs_nom` VARCHAR(50) NOT NULL AFTER `custom10`, 
		//ADD `vs_marque` VARCHAR(50) NOT NULL AFTER `vs_nom`, 
		//ADD `vs_category` VARCHAR(50) NOT NULL AFTER `vs_marque`, 
		//ADD `vs_param_1` VARCHAR(50) NOT NULL AFTER `vs_category`, 
		//ADD `vs_param_2` VARCHAR(50) NOT NULL AFTER `vs_param_1`, 
		//ADD `vs_param_3` VARCHAR(50) NOT NULL AFTER `vs_param_2`;
		$sql_ospos_items_vs_nom = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_nom';");
		$sql_ospos_items_vs_nom = $sql_ospos_items_vs_nom->result_array();
		//ALTER TABLE `ospos_items` ADD `vs_nom` VARCHAR(50) NOT NULL AFTER `custom10`, ADD `vs_marque` VARCHAR(50) NOT NULL AFTER `vs_nom`, ADD `vs_category` VARCHAR(50) NOT NULL AFTER `vs_marque`, ADD `vs_param_1` VARCHAR(50) NOT NULL AFTER `vs_category`, ADD `vs_param_2` VARCHAR(50) NOT NULL AFTER `vs_param_1`, ADD `vs_param_3` VARCHAR(50) NOT NULL AFTER `vs_param_2`;
		if(!isset($sql_ospos_items_vs_nom[0]['Field']))
		{
			$sql = $this->db->query('ALTER TABLE `ospos_items` ADD `vs_nom` VARCHAR(50) NULL AFTER `custom10`');
		}
        $sql_ospos_items_vs_marque = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_marque';");
		$sql_ospos_items_vs_marque = $sql_ospos_items_vs_marque->result_array();
		if(!isset($sql_ospos_items_vs_marque[0]['Field']))
		{
			$sql = $this->db->query('ALTER TABLE `ospos_items` ADD `vs_marque` VARCHAR(50) NULL AFTER `vs_nom`;');
		}

		$sql_ospos_items_vs_category = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_category';");
		$sql_ospos_items_vs_category = $sql_ospos_items_vs_category->result_array();
		
		if(!isset($sql_ospos_items_vs_category[0]['Field']))
		{
			$sql = $this->db->query('ALTER TABLE `ospos_items` ADD `vs_category` VARCHAR(50) NULL AFTER `vs_marque`;');
		}

		$sql_ospos_items_vs_param_1 = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_param_1';");
		$sql_ospos_items_vs_param_1 = $sql_ospos_items_vs_param_1->result_array();
		
		if(!isset($sql_ospos_items_vs_param_1[0]['Field']))
		{
			$sql = $this->db->query('ALTER TABLE `ospos_items` ADD `vs_param_1` VARCHAR(50) NULL AFTER `vs_category`;');
		}
		
		$sql_ospos_items_vs_param_2 = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_param_2';");
		$sql_ospos_items_vs_param_2 = $sql_ospos_items_vs_param_2->result_array();
		
		if(!isset($sql_ospos_items_vs_param_2[0]['Field']))
		{
			$sql = $this->db->query('ALTER TABLE `ospos_items` ADD `vs_param_2` VARCHAR(50) NULL AFTER `vs_param_1`;');
		}
		
		$sql_ospos_items_vs_param_3 = $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_param_3';");
		$sql_ospos_items_vs_param_3 = $sql_ospos_items_vs_param_3->result_array();
		
		if(!isset($sql_ospos_items_vs_param_3[0]['Field']))
		{
			$sql = $this->db->query('ALTER TABLE `ospos_items` ADD `vs_param_3` VARCHAR(50) NULL AFTER `vs_param_2`;');
		}

		$sql_ospos_item_vs_nom_image =  $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_nom_image';");
		$sql_ospos_item_vs_nom_image = $sql_ospos_item_vs_nom_image->result_array();
		
		if(!isset($sql_ospos_item_vs_nom_image[0]['Field']))
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` ADD `vs_nom_image` VARCHAR(500) NOT NULL DEFAULT '' AFTER `vs_param_3`;");
		}

		$sql_ospos_item_vs_vs_nom =  $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_nom';");
		$sql_ospos_item_vs_vs_nom = $sql_ospos_item_vs_vs_nom->result_array();
		
		if($sql_ospos_item_vs_vs_nom[0]['NULL']='NO' || $sql_ospos_item_vs_vs_nom[0]['Default']='NULL')
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` CHANGE `vs_nom` `vs_nom` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';");
		}

		$sql_ospos_item_vs_vs_marque =  $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_marque';");
		$sql_ospos_item_vs_vs_marque = $sql_ospos_item_vs_vs_marque->result_array();		
		
		if($sql_ospos_item_vs_vs_marque[0]['NULL']='NO' || $sql_ospos_item_vs_vs_marque[0]['Default']='NULL')
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` CHANGE `vs_marque` `vs_marque` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';");
		}

		$sql_ospos_item_vs_category_type =  $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_category_type';");
		$sql_ospos_item_vs_category_type = $sql_ospos_item_vs_category_type->result_array();
		
		if($sql_ospos_item_vs_category_type[0]['NULL']='NO' || $sql_ospos_item_vs_category_type[0]['Default']='NULL')
		{
	//		$sql = $this->db->query("");
		}

		$sql_ospos_item_vs_category =  $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_category';");
		$sql_ospos_item_vs_category = $sql_ospos_item_vs_category->result_array();
		
		if($sql_ospos_item_vs_category[0]['NULL']='NO' || $sql_ospos_item_vs_category[0]['Default']='NULL')
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` CHANGE `vs_category` `vs_category` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';");
		}

		$sql_ospos_item_vs_vs_param_1 =  $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_param_1';");
		$sql_ospos_item_vs_vs_param_1 = $sql_ospos_item_vs_vs_param_1->result_array();
		
		if($sql_ospos_item_vs_vs_param_1[0]['NULL']='NO' || $sql_ospos_item_vs_vs_param_1[0]['Default']='NULL')
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` CHANGE `vs_param_1` `vs_param_1` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';");
		}

		$sql_ospos_item_vs_vs_param_2 =  $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_param_2';");
		$sql_ospos_item_vs_vs_param_2 = $sql_ospos_item_vs_vs_param_2->result_array();
		
		if($sql_ospos_item_vs_vs_param_2[0]['NULL']='NO' || $sql_ospos_item_vs_vs_param_2[0]['Default']='NULL')
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` CHANGE `vs_param_2` `vs_param_2` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';");
		}
		$sql_ospos_item_vs_vs_param_3 =  $this->db->query("SHOW COLUMNS FROM `ospos_items` LIKE 'vs_param_3';");
		$sql_ospos_item_vs_vs_param_3 = $sql_ospos_item_vs_vs_param_3->result_array();
		
		if($sql_ospos_item_vs_vs_param_3[0]['NULL']='NO' || $sql_ospos_item_vs_vs_param_3[0]['Default']='NULL')
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` CHANGE `vs_param_3` `vs_param_3` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';");
		}

		
		if(!isset($sql_ospos_item_vs_category_type[0]['Field']))
		{
			$sql = $this->db->query("ALTER TABLE `ospos_items` ADD `vs_category_type` VARCHAR(500) NULL DEFAULT '' AFTER `vs_marque`;");
		}
		
		//    END    MAJ DB for ospos_items
		


		//AJout de Colonne Colisage pour l'importation des produits
		$sql_ospos_items_imports = $this->db->query("SELECT * FROM `ospos_items_imports` WHERE `column_letter` = 'P'");
		$sql_ospos_items_imports = $sql_ospos_items_imports->result_array();
		
		if (!isset($sql_ospos_items_imports[0]))
		{
			$sql=$this->db->query("INSERT INTO `ospos_items_imports` (`column_letter`, `column_label`,`column_number`,`column_data_type`,`column_database_field_name`,`branch_code`) 
			VALUES ('P', 'Colisage','35','N','reorder_quantity','".$this->config->item('branch_code')."' )");
		}
		//AJout de Colonne Prix de vente forcé pour l'importation des produits
        $sql_ospos_items_imports = $this->db->query("SELECT * FROM `ospos_items_imports` WHERE `column_letter` = 'E'");
        $sql_ospos_items_imports = $sql_ospos_items_imports->result_array();
        
        if (!isset($sql_ospos_items_imports[0]))
        {
            $sql=$this->db->query("INSERT INTO `ospos_items_imports` (`column_letter`, `column_label`,`column_number`,`column_data_type`,`column_database_field_name`,`branch_code`) 
            VALUES ('E', 'DPT_VENTE','4','N','forced','".$this->config->item('branch_code')."' )");
        }

        //MAJ DB for ospos_app_config

		//Récupération de la valeur pour l'ajout d'une machine VapeSelf
		$sql_ospos_app_config_VapeSelf = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'distributeur_vapeself' ");
		$sql_ospos_app_config_VapeSelf = $sql_ospos_app_config_VapeSelf->result_array();
		
		

		//Si ces 2 valeurs ne sont pas dans la table ospos_app_config alors elles sont insérer avec des valuers par défaut
		if(($sql_ospos_app_config_VapeSelf[0]['value']=="" && $sql_ospos_app_config_VapeSelf[0]['key']=="historique") || !isset($sql_ospos_app_config_VapeSelf[0]))
		{
			if($sql_ospos_app_config_VapeSelf[0]['key']=="historique")
			{
                $sql=$this->db->query("UPDATE `ospos_app_config` SET `value` = 'N' WHERE `key` = 'distributeur_vapeself'");
			}
			else
			{
				$sql=$this->db->query("INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('distributeur_vapeself', 'N')");
			}
		}

		//Modification du calcul des points de fidélité pour les magasins intégré passage à 0.3€
        if ($_SESSION['branchtype']=='I') {
            $sql_fidelity_value = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'fidelity_value' ");
            $sql_fidelity_value = $sql_fidelity_value->result_array();
            if (isset($sql_fidelity_value[0]['key'])) {
                $fidelity_value = $sql_fidelity_value[0]['value'];
                if ($fidelity_value >0 && $fidelity_value  <> 0.3) {
                    $sql=$this->db->query("UPDATE `ospos_customers` set `fidelity_points` = round((`fidelity_points`/ 0.3 )  *  ".$fidelity_value.",2)");
					$sql=$this->db->query("UPDATE `ospos_items`  set `offer_value` = 0 ");
					$sql=$this->db->query("UPDATE `ospos_app_config` set value = 2.99 WHERE `key` = 'fidelity_minimum'");
					$sql=$this->db->query("UPDATE `ospos_app_config` set value = 0.3 WHERE `key` = 'fidelity_value'");
				}
			}
			$sql_fax = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'fax' ");
			$sql_fax  = $sql_fax ->result_array();
            if (isset($sql_fax[0]['key'])) {
                $fax_value = $sql_fax[0]['value'];
                if ($fax_value == 0 ) {
                    $sql=$this->db->query("UPDATE `ospos_customers` set `fidelity_points` = round(((((`fidelity_points` * .4) / .3 ) *.4) /.3) ,2)");
					$sql=$this->db->query("UPDATE `ospos_sales`,`ospos_sales_payments`,`ospos_customers`  set 
							`fidelity_points` =round(`fidelity_points` + IFNULL(`ospos_sales_payments`.`payment_amount`/.4 ,0),2)  
						WHERE    `ospos_sales`.`sale_id` = `ospos_sales_payments`.`sale_id` AND 
						`payment_method_code` LIKE 'FIDE' AND 
						`sale_time` > '2020-10-10' AND 
						`ospos_customers`.`person_id` = `ospos_sales`.`customer_id` AND 
						`ospos_sales_payments`.`payment_amount` > 0");
					$sql=$this->db->query("UPDATE `ospos_app_config` set value = 1 WHERE `key` = 'fax'");
                }
            }

			
	
              
        }

		//Récupération des valeurs pour l'historique et pour le nombre de jours de prévision de stock
		$sql_historique = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'historique' ");
		$sql_historique = $sql_historique->result_array();
		$sql_nbre_jour_prevision_stock = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'nbre_jour_prevision_stock' ");
		$sql_nbre_jour_prevision_stock = $sql_nbre_jour_prevision_stock->result_array();
		
		//Si ces 2 valeurs ne sont pas dans la table ospos_app_config alors elles sont insérer avec des valuers par défaut
		if(($sql_historique[0]['value']=="" && $sql_historique[0]['key']=="historique") || !isset($sql_historique[0]))
		{
			if($sql_historique[0]['key']=="historique")
			{
                $sql=$this->db->query("UPDATE `ospos_app_config` SET `value` = '45' WHERE `key` = 'historique'");
			}
			else
			{
				$sql=$this->db->query("INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('historique', '45')");
			}
		}
		if(($sql_nbre_jour_prevision_stock[0]['value']=="" && $sql_nbre_jour_prevision_stock[0]['key']=="nbre_jour_prevision_stock") || !isset($sql_nbre_jour_prevision_stock[0]))
		{
			if($sql_nbre_jour_prevision_stock[0]['key']=="nbre_jour_prevision_stock")
			{
				$sql=$this->db->query("UPDATE `ospos_app_config` SET `value` = '20' WHERE `key` = 'nbre_jour_prevision_stock'");
			}
			else
			{
                $sql=$this->db->query("INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('nbre_jour_prevision_stock', '20')");
			}
		}	

		$sql_multi_vendeur = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'multi_vendeur' ");
		$sql_multi_vendeur = $sql_multi_vendeur->result_array();
		if(!isset($sql_multi_vendeur[0]['key']))
		{
			$sql=$this->db->query("INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('multi_vendeur', 'N')");
		}

        //    END    MAJ DB for ospos_app_config


		//Création des tables
		//ospos_remote_stock
		$magasin = 'sonrisa' . $this->config->item('branch_code');
		$mag = '`' . $magasin .'`'; 
		$request_sql = 'CREATE TABLE IF NOT EXISTS' . $mag .  '.`ospos_remote_stock` ( `CODE_PRODUIT` VARCHAR(50) NOT NULL , `LIBELLE` VARCHAR(81) NULL DEFAULT NULL , `STOCK_REEL` INT(15) NULL DEFAULT NULL , `STOCK_DISPONIBLE` INT(15) NULL DEFAULT NULL , PRIMARY KEY (`CODE_PRODUIT`)) ENGINE = InnoDB;';
		$sql = $this->db->query($request_sql);


		if(file_exists("/var/www/html/wrightetmathon/STOCK/STOCK_LOGISTIQUE.csv"))
		{
        //load /var/www/html/wrightetmathon/STOCK/STOCK_LOGISTIQUE.csv for remote_stock
		$this->db->query("truncate table `ospos_remote_stock`;");
	    $this->db->query("load data LOW_PRIORITY infile '/var/www/html/wrightetmathon/STOCK/STOCK_LOGISTIQUE.csv' into table ospos_remote_stock FIELDS TERMINATED BY ';' IGNORE 1 LINES;");
		$this->db->query('UPDATE `ospos_items` SET `ospos_items`.`quantity_central` = 0');
        $this->db->query('UPDATE  `ospos_items` ,`ospos_remote_stock` SET `ospos_items`.`quantity_central` = `ospos_remote_stock`.`STOCK_DISPONIBLE` WHERE `ospos_items`.`item_number` = `ospos_remote_stock`.`CODE_PRODUIT` AND `ospos_remote_stock`.`STOCK_DISPONIBLE` > 0');
        }
        else
        {}

	    //Table ospos_vs_sales with text
	    $sql=$this->db->query('CREATE TABLE IF NOT EXISTS' . $mag . '.`ospos_vs_sales` ( `vs_sale_id` INT(15) NOT NULL AUTO_INCREMENT , `id_vente` INT(10) NOT NULL , `datevente` DATETIME NOT NULL , `id_client` VARCHAR(50) NOT NULL , `totalttc` FLOAT(15,2) NOT NULL , `remise` FLOAT(15,2) NOT NULL , `recredit` FLOAT(15,2) NOT NULL , `emplacement` VARCHAR(50) NOT NULL , `liste` TEXT NOT NULL , `modifie` INT(11) NOT NULL , `mon_id` INT(11) NOT NULL , `date_add_table` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `validate` BOOLEAN NOT NULL , PRIMARY KEY (`vs_sale_id`)) ENGINE = InnoDB;');
		
		$sql_ospos_vs_sales_modereglement = $this->db->query("SHOW COLUMNS FROM `ospos_vs_sales` LIKE 'liste';");
        $sql_ospos_vs_sales_modereglement = $sql_ospos_vs_sales_modereglement->result_array();
        
        if(isset($sql_ospos_vs_sales_modereglement[0]['Type']) && ($sql_ospos_vs_sales_modereglement[0]['Type'] != 'text'))
        {
        	$sql = $this->db->query('ALTER TABLE `ospos_vs_sales` CHANGE `liste` `liste` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;');
		}
		
		//ospos_vs_sales
		//$sql=$this->db->query('CREATE TABLE IF NOT EXISTS' . $mag . '.`ospos_vs_sales` ( `vs_sale_id` INT(15) NOT NULL AUTO_INCREMENT , `id_vente` INT(10) NOT NULL , `datevente` DATETIME NOT NULL , `id_client` VARCHAR(50) NOT NULL , `totalttc` FLOAT(15,2) NOT NULL , `remise` FLOAT(15,2) NOT NULL , `recredit` FLOAT(15,2) NOT NULL , `emplacement` VARCHAR(50) NOT NULL , `liste` JSON NOT NULL , `modifie` INT(11) NOT NULL , `mon_id` INT(11) NOT NULL , `date_add_table` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `validate` BOOLEAN NOT NULL , PRIMARY KEY (`vs_sale_id`)) ENGINE = InnoDB;');
        	
        //ospos_vs_credit
		$sql=$this->db->query("CREATE TABLE IF NOT EXISTS" . $mag . ".`ospos_vs_credit` ( `vs_sale_id` INT(15) NOT NULL AUTO_INCREMENT , `id_credit` INT(10) NOT NULL , `votreid` VARCHAR(50) NOT NULL DEFAULT '' , `datecredit` DATETIME NOT NULL , `montant` FLOAT(15,2) NOT NULL , `solde` FLOAT(15,2) NOT NULL , `date_add_table` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `validate` BOOLEAN NOT NULL , PRIMARY KEY (`vs_sale_id`)) ENGINE = InnoDB;");
			
		$sql_ospos_vs_sales_modereglement = $this->db->query("SHOW COLUMNS FROM `ospos_vs_sales` LIKE 'modereglement';");
        $sql_ospos_vs_sales_modereglement = $sql_ospos_vs_sales_modereglement->result_array();
        
        if(!isset($sql_ospos_vs_sales_modereglement[0]['Field']))
        {
        	$sql = $this->db->query('ALTER TABLE `ospos_vs_sales` ADD `modereglement` VARCHAR(50) NOT NULL AFTER `mon_id`;');
		}
		
        //    END    Création des tables

		// ospos_payment_methods  : ajout Carte Sans Contact
		$sql_payment_code = $this->db->query("SELECT `payment_method_id` FROM `ospos_payment_methods` WHERE `payment_method_id` = 9 ");
		$sql_payment_code = $sql_payment_code->result_array();
		if (!isset($sql_payment_code[0]['payment_method_id']))
        {
			$sql=$this->db->query('INSERT INTO `ospos_payment_methods`(`payment_method_id`, `payment_method_code`, `payment_method_description`, `payment_method_include`, `payment_method_display_order`, `payment_method_fidelity_flag`, `payment_method_giftcard_flag`, `deleted`, `branch_code`) VALUES(9, "CARDSC", "CB Sans Contact", "Y", 5, "N", "N", 0, "' . $this->config->item('branch_code') . '");');
		}
		else
		{
			$sql=$this->db->query('UPDATE `ospos_payment_methods` set `payment_method_display_order` = 5  where `payment_method_code` = "CARDSC"; ');
		}
		if ($this->config->item('branch_code') != 'SORIN')
		{
			$sql=$this->db->query('UPDATE `ospos_payment_methods` set `payment_method_include` = "N"  where `payment_method_code` = "VSCP" or `payment_method_code` = "VSCB" ; ');
		}
		// End of  ospos_payment_methods  


		//MAJ DB for ospos_customers
        
        $sql_ospos_customers_card_code = $this->db->query("SHOW COLUMNS FROM `ospos_customers` LIKE 'card_code';");
        $sql_ospos_customers_card_code = $sql_ospos_customers_card_code->result_array();
        
        if(!isset($sql_ospos_customers_card_code[0]['Field']))
        {
        	$sql = $this->db->query('ALTER TABLE `ospos_customers` ADD `card_code` VARCHAR(4) NULL DEFAULT ' . "''" . ' AFTER `fidelity_points`;');
        }







        //ospos_payment_methods


		$sql_payment_vapeself_code = $this->db->query("SELECT `payment_method_id` FROM `ospos_payment_methods` WHERE `payment_method_id` = 10 ");
		$sql_payment_vapeself_code = $sql_payment_vapeself_code->result_array();
		if(($sql_payment_vapeself_code[0]['payment_method_id'] == 10))
        {
            //update 
            $sql = $this->db->query('UPDATE `ospos_payment_methods` SET `payment_method_code` = "VSCP", `payment_method_description` = "Carte client distributeur" WHERE `payment_method_id` = 10 ');
		}
		if(!isset($sql_payment_vapeself_code[0]['payment_method_id']))
        {
			//insert distributeur_vapeself_code
			$sql=$this->db->query('INSERT INTO `ospos_payment_methods`(`payment_method_id`, `payment_method_code`, `payment_method_description`, `payment_method_include`, `payment_method_display_order`, `payment_method_fidelity_flag`, `payment_method_giftcard_flag`, `deleted`, `branch_code`) VALUES(10, "VSCP", "Carte client distributeur", "Y", 26, "N", "N", 0, "' . $this->config->item('branch_code') . '");');
		}

		$sql_payment_vapeself_code = $this->db->query("SELECT `payment_method_id` FROM `ospos_payment_methods` WHERE `payment_method_id` = 11 ");
		$sql_payment_vapeself_code = $sql_payment_vapeself_code->result_array();
		if(!isset($sql_payment_vapeself_code[0]['payment_method_id']))
        {
			//insert distributeur_vapeself_code
			$sql=$this->db->query('INSERT INTO `ospos_payment_methods`(`payment_method_id`, `payment_method_code`, `payment_method_description`, `payment_method_include`, `payment_method_display_order`, `payment_method_fidelity_flag`, `payment_method_giftcard_flag`, `deleted`, `branch_code`) VALUES(11, "VSCB", "CB distributeur", "Y", 63, "N", "N", 0, "' . $this->config->item('branch_code') . '");');
		}
		// create indexes if not exist
		$sql_ospos_item_name = $this->db->query("SHOW INDEXES FROM ospos_items where Column_name like 'name'");
		$sql_ospos_item_name = $sql_ospos_item_name->result_array();
		if(!isset($sql_ospos_item_name[0]['Column_name']))
		{    //add name index if not exist
			$sql = $this->db->query("ALTER TABLE `ospos_items` ADD FULLTEXT( `name`);");
		}	
		$sql_ospos_people_first_name = $this->db->query("SHOW INDEXES FROM ospos_people where Column_name like 'first_name'");
		$sql_ospos_people_first_name = $sql_ospos_people_first_name->result_array();
		if(!isset($sql_ospos_people_first_name[0]['Column_name']))
		{    //add name index if not exist
			$sql = $this->db->query("ALTER TABLE `ospos_people` ADD FULLTEXT( `first_name`);");
		}	
		$sql_ospos_people_last_name = $this->db->query("SHOW INDEXES FROM ospos_people where Column_name like 'last_name'");
		$sql_ospos_people_last_name = $sql_ospos_people_last_name->result_array();
		if(!isset($sql_ospos_people_last_name[0]['Column_name']))
		{    //add name index if not exist
			$sql = $this->db->query("ALTER TABLE `ospos_people` ADD FULLTEXT( `last_name`);");
		}	
		$sql_ospos_inventory = $this->db->query("SHOW INDEXES FROM ospos_inventory where Column_name like 'trans_comment'");
		$sql_ospos_inventory = $sql_ospos_inventory->result_array();
		if(!isset($sql_ospos_inventory[0]['Column_name']))
		{    //add name index if not exist
			$sql = $this->db->query("ALTER TABLE `ospos_inventory` CHANGE `trans_comment` `trans_comment` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
			$sql = $this->db->query("ALTER TABLE `ospos_inventory` ADD INDEX( `trans_comment`);");
		}	



/*

		//update stock entrepôt
		/*
		truncate table `table 81`

load data local infile 'c:/tmp/STOCK_LOGISTIQUE.csv' into table `table 81`//*/
//CREATE TABLE `sonrisaSORIN`.`ospos_remote_stock` ( `item_number` VARCHAR(50) NOT NULL , `stock` INT(15) NOT NULL ) ENGINE = InnoDB;
/*        $request_sql = 'CREATE TABLE ' . '`sonrisa' . $this->config->item('branch_code') .  '`.`ospos_remote_stock`  ( `item_number` VARCHAR(50) NOT NULL , `stock` INT(15) NOT NULL ) ENGINE = InnoDB;';
		$sql=$this->db->query($request_sql);//*/
/*	    $sql_quantity_central = $this->db->query("SELECT * FROM `ospos_items` LIMIT 1 ");
		$sql_quantity_central = $sql_quantity_central->result_array();
		$magasin = 'sonrisa' . $this->config->item('branch_code');
		$mag = '`' . $magasin .'`'; 
	    if(!isset($sql_quantity_central[0]['quantity_central']))
	    {		
			//$request_sql = 'CREATE TABLE ' . '`sonrisa' . $this->config->item('branch_code') .  '`.`ospos_remote_stock`  ( `item_number` VARCHAR(50) NOT NULL , `stock` INT(15) NOT NULL ) ENGINE = InnoDB;';
			//$sql=$this->db->query($request_sql);

//			$sql = $this->db->query('ALTER TABLE `ospos_remote_stock` ADD `LIBELLE` VARCHAR(50) NULL DEFAULT NULL AFTER `CODE_PRODUIT`, ADD `STOCK_REEL` INT(15) NULL DEFAULT NULL AFTER `LIBELLE`;');
//			$sql = $this->db->query('ALTER TABLE `ospos_remote_stock` CHANGE `LIBELLE` `LIBELLE` VARCHAR(81) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;');
//			$sql = $this->db->query('ALTER TABLE `ospos_remote_stock` ADD PRIMARY KEY(`CODE_PRODUIT`);');
			
            $request_sql = 'CREATE TABLE IF NOT EXISTS' . $mag .  '.`ospos_remote_stock` ( `CODE_PRODUIT` VARCHAR(50) NOT NULL , `LIBELLE` VARCHAR(81) NULL DEFAULT NULL , `STOCK_REEL` INT(15) NULL DEFAULT NULL , `STOCK_DISPONIBLE` INT(15) NULL DEFAULT NULL , PRIMARY KEY (`CODE_PRODUIT`)) ENGINE = InnoDB;';
            $sql = $this->db->query($request_sql); //'CREATE TABLE `sonrisaSORIN`.`table` ( `CODE_PRODUIT` VARCHAR(50) NOT NULL , `LIBELLE` VARCHAR(81) NULL DEFAULT NULL , `STOCK_REEL` INT(15) NULL DEFAULT NULL , `STOCK_DISPONIBLE` INT(15) NULL DEFAULT NULL , PRIMARY KEY (`CODE_PRODUIT`)) ENGINE = InnoDB;');
            $sql = $this->db->query('ALTER TABLE `ospos_items` ADD `quantity_central` INT(15) NOT NULL DEFAULT "0" AFTER `quantity`;');
            //CREATE TABLE `sonrisaSORIN`.`table` ( `CODE_PRODUIT` VARCHAR(50) NOT NULL , `LIBELLE` VARCHAR(81) NULL DEFAULT NULL , `STOCK_REEL` INT(15) NULL DEFAULT NULL , `STOCK_DISPONIBLE` INT(15) NULL DEFAULT NULL , PRIMARY KEY (`CODE_PRODUIT`)) ENGINE = InnoDB;
		}

	    $this->db->query("truncate table `ospos_remote_stock`;");
	    $this->db->query("load data LOW_PRIORITY infile '/var/www/html/wrightetmathon/STOCK/STOCK_LOGISTIQUE.csv' into table ospos_remote_stock FIELDS TERMINATED BY ';' IGNORE 1 LINES;");
		//load data LOW_PRIORITY infile '/var/www/html/wrightetmathon/STOCK_LOGISTIQUE.csv' into table ospos_remote_stock FIELDS TERMINATED BY ';' IGNORE 1 LINES;



        //CREATE TABLE `sonrisaSORIN`.`ospos_vs_sales` ( `vs_sale_id` INT(15) NOT NULL AUTO_INCREMENT , `id_vente` INT(10) NOT NULL , `datevente` DATETIME NOT NULL , `id_client` VARCHAR(50) NOT NULL , `totalttc` FLOAT(15,2) NOT NULL , `remise` FLOAT(15,2) NOT NULL , `recredit` FLOAT(15,2) NOT NULL , `emplacement` VARCHAR(50) NOT NULL , `liste` JSON NOT NULL , `modifie` INT(11) NOT NULL , `mon_id` INT(11) NOT NULL , `date_add_table` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `validate` BOOLEAN NOT NULL , PRIMARY KEY (`vs_sale_id`)) ENGINE = InnoDB;
        //CREATE TABLE `sonrisaSORIN`.`ospos_vs_credit` ( `vs_sale_id` INT(15) NOT NULL AUTO_INCREMENT , `id_credit` INT(10) NOT NULL , `votreid` VARCHAR(50) NOT NULL DEFAULT '' , `datetime` DATETIME NOT NULL , `montant` FLOAT(15,2) NOT NULL , `solde` FLOAT(15,2) NOT NULL , `date_add_table` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `validate` BOOLEAN NOT NULL , PRIMARY KEY (`vs_sale_id`)) ENGINE = InnoDB;
        //Récupération de la valeur pour l'ajout du mode de payment VS: Distributeur Vapeself poue la machine VapeSelf
        $sql_tables = $this->db->query("show TABLES");
        $sql_tables = $sql_tables->result_array();
        $sql_table_code_vs_sales = 0;
        $sql_table_code_vs_credit = 0;
        
        foreach($sql_tables as $cle => $line)
        {
        	if($line['Tables_in_sonrisaSORIN'] == 'ospos_vs_sales' )
        	{
        		$sql_table_code_vs_sales = 1;
        	}
        	if($line['Tables_in_sonrisaSORIN'] == 'ospos_vs_credit')
        	{
        		$sql_table_code_vs_credit = 1;
        	}
        }
        $sql_vs_sales = $this->db->query("SELECT * FROM `ospos_vs_sales` LIMIT 1");
        $sql_vs_sales = $sql_vs_sales->result_array();
        if(!isset($sql_vs_sales[0]['vs_sale_id']) && ($this->config->item('branch_code') == 'SORIN') && (!isset($sql_tables[0]['ospos_vs_sales']))) //($sql_item_emplacement[0]['custom6'] != "1"))
        {
        	//créer les 2 tables pour enregistrer les ventes de la machine vapeself
        	if($sql_table_code_vs_sales != 1)
        	{
        		$sql=$this->db->query('CREATE TABLE IF NOT EXISTS' . $mag . '.`ospos_vs_sales` ( `vs_sale_id` INT(15) NOT NULL AUTO_INCREMENT , `id_vente` INT(10) NOT NULL , `datevente` DATETIME NOT NULL , `id_client` VARCHAR(50) NOT NULL , `totalttc` FLOAT(15,2) NOT NULL , `remise` FLOAT(15,2) NOT NULL , `recredit` FLOAT(15,2) NOT NULL , `emplacement` VARCHAR(50) NOT NULL , `liste` JSON NOT NULL , `modifie` INT(11) NOT NULL , `mon_id` INT(11) NOT NULL , `date_add_table` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `validate` BOOLEAN NOT NULL , PRIMARY KEY (`vs_sale_id`)) ENGINE = InnoDB;');
        	}
        	if($sql_table_code_vs_credit != 1)
        	{
        		$sql=$this->db->query("CREATE TABLE IF NOT EXISTS" . $mag . ".`ospos_vs_credit` ( `vs_sale_id` INT(15) NOT NULL AUTO_INCREMENT , `id_credit` INT(10) NOT NULL , `votreid` VARCHAR(50) NOT NULL DEFAULT '' , `datecredit` DATETIME NOT NULL , `montant` FLOAT(15,2) NOT NULL , `solde` FLOAT(15,2) NOT NULL , `date_add_table` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `validate` BOOLEAN NOT NULL , PRIMARY KEY (`vs_sale_id`)) ENGINE = InnoDB;");
        	}
        	//			$sql = $this->db->query('UPDATE `ospos_items` SET `custom6` = "1" ');
        }

		$sql_distributeur_vapeself_code = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'distributeur_vapeself_code' ");
		$sql_distributeur_vapeself_code = $sql_distributeur_vapeself_code->result_array();
		if(!isset($sql_distributeur_vapeself_code[0]['value']))
        {
			//insert distributeur_vapeself_code
			$sql=$this->db->query("INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('distributeur_vapeself_code', '0')");
		}
	//	$sql=$this->db->query('INSERT INTO `ospos_payment_methods`(`payment_method_id`, `payment_method_code`, `payment_method_description`, `payment_method_include`, `payment_method_display_order`, `payment_method_fidelity_flag`, `payment_method_giftcard_flag`, `deleted`, `branch_code`) VALUES(10, "VS", "Distributeur VapeSelf", "Y", 26, "N", "N", 0, "' . $this->config->item('branch_code') . '");');
	
	    $sql_item_emplacement = $this->db->query("SELECT * FROM `ospos_items` LIMIT 1 ");
	    $sql_item_emplacement = $sql_item_emplacement->result_array();
	    if(!isset($sql_item_emplacement[0]['emplacement']) && ($sql_item_emplacement[0]['custom3'] != "1"))
	    {
			$sql=$this->db->query('ALTER TABLE `ospos_items` CHANGE `custom2` `emplacement` INT(15) NULL DEFAULT NULL;');
			$sql = $this->db->query('UPDATE `ospos_items` SET `custom3` = "1" ');
		}
		//ALTER TABLE `ospos_vs_sales` ADD `modereglement` VARCHAR(50) NOT NULL AFTER `mon_id`;
  
/*
ALTER TABLE `ospos_items` ADD `vs_nom` VARCHAR(50) NOT NULL AFTER `custom10`, ADD `vs_marque` VARCHAR(50) NOT NULL AFTER `vs_nom`, ADD `vs_category` VARCHAR(50) NOT NULL AFTER `vs_marque`, ADD `vs_param_1` VARCHAR(50) NOT NULL AFTER `vs_category`, ADD `vs_param_2` VARCHAR(50) NOT NULL AFTER `vs_param_1`, ADD `vs_param_3` VARCHAR(50) NOT NULL AFTER `vs_param_2`;
//*/
/*		if($sql_item_emplacement[0]['custom10'] != '1')
		{
			$sql = $this->db->query('ALTER TABLE `ospos_items` ADD `vs_nom` VARCHAR(50) NOT NULL AFTER `custom10`, ADD `vs_marque` VARCHAR(50) NOT NULL AFTER `vs_nom`, ADD `vs_category` VARCHAR(50) NOT NULL AFTER `vs_marque`, ADD `vs_param_1` VARCHAR(50) NOT NULL AFTER `vs_category`, ADD `vs_param_2` VARCHAR(50) NOT NULL AFTER `vs_param_1`, ADD `vs_param_3` VARCHAR(50) NOT NULL AFTER `vs_param_2`;');
		    $sql = $this->db->query('UPDATE `ospos_items` SET `custom10` = "1" ');
		}
		/*
		if($sql_item_emplacement[0]['custom9'] != '1')
		{
			$sql = $this->db->query('ALTER TABLE `ospos_vs_sales` ADD `modereglement` VARCHAR(50) NOT NULL AFTER `mon_id`;');
		    $sql = $this->db->query('UPDATE `ospos_items` SET `custom9` = "1" ');
		}//*/

/*		$sql_payment_vapeself_code = $this->db->query("SELECT `payment_method_id` FROM `ospos_payment_methods` WHERE `payment_method_id` = 10 ");
		$sql_payment_vapeself_code = $sql_payment_vapeself_code->result_array();
		if(($sql_payment_vapeself_code[0]['payment_method_id'] == 10) && ($sql_item_emplacement[0]['custom4'] != "1"))
        {
            //update 
            $sql = $this->db->query('UPDATE `ospos_payment_methods` SET `payment_method_code` = "VSCP", `payment_method_description` = "Carte client distributeur" WHERE `payment_method_id` = 10 ');
			$sql = $this->db->query('UPDATE `ospos_items` SET `custom4` = "1" ');    
		}
		$sql_payment_vapeself_code = $this->db->query("SELECT `payment_method_id` FROM `ospos_payment_methods` WHERE `payment_method_id` = 10 ");
		$sql_payment_vapeself_code = $sql_payment_vapeself_code->result_array();
		if(!isset($sql_payment_vapeself_code[0]['payment_method_id']) && ($sql_item_emplacement[0]['custom5'] != "1"))
        {
			//insert distributeur_vapeself_code
			$sql=$this->db->query('INSERT INTO `ospos_payment_methods`(`payment_method_id`, `payment_method_code`, `payment_method_description`, `payment_method_include`, `payment_method_display_order`, `payment_method_fidelity_flag`, `payment_method_giftcard_flag`, `deleted`, `branch_code`) VALUES(10, "VSCP", "Carte client distributeur", "Y", 26, "N", "N", 0, "' . $this->config->item('branch_code') . '");');
		    $sql = $this->db->query('UPDATE `ospos_items` SET `custom5` = "1" ');
		}

		$sql_payment_vapeself_code = $this->db->query("SELECT `payment_method_id` FROM `ospos_payment_methods` WHERE `payment_method_id` = 11 ");
		$sql_payment_vapeself_code = $sql_payment_vapeself_code->result_array();
		if(!isset($sql_payment_vapeself_code[0]['payment_method_id']))
        {
			//insert distributeur_vapeself_code
			$sql=$this->db->query('INSERT INTO `ospos_payment_methods`(`payment_method_id`, `payment_method_code`, `payment_method_description`, `payment_method_include`, `payment_method_display_order`, `payment_method_fidelity_flag`, `payment_method_giftcard_flag`, `deleted`, `branch_code`) VALUES(11, "VSCB", "CB distributeur", "Y", 63, "N", "N", 0, "' . $this->config->item('branch_code') . '");');
		}


		
		
		/*
		//Récupération des valeurs pour l'historique et pour le nombre de jours de prévision de stock
		$sql_historique = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'historique' ");
		$sql_historique = $sql_historique->result_array();
		$sql_nbre_jour_prevision_stock = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'nbre_jour_prevision_stock' ");
		$sql_nbre_jour_prevision_stock = $sql_nbre_jour_prevision_stock->result_array();
		
		//Si ces 2 valeurs ne sont pas dans la table ospos_app_config alors elles sont insérer avec des valuers par défaut
		if(($sql_historique[0]['value']=="" && $sql_historique[0]['key']=="historique") || !isset($sql_historique[0]))
		{
			if($sql_historique[0]['key']=="historique")
			{
                $sql=$this->db->query("UPDATE `ospos_app_config` SET `value` = '45' WHERE `key` = 'historique'");
			}
			else
			{
				$sql=$this->db->query("INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('historique', '45')");
			}
		}
		if(($sql_nbre_jour_prevision_stock[0]['value']=="" && $sql_nbre_jour_prevision_stock[0]['key']=="nbre_jour_prevision_stock") || !isset($sql_nbre_jour_prevision_stock[0]))
		{
			if($sql_nbre_jour_prevision_stock[0]['key']=="nbre_jour_prevision_stock")
			{
				$sql=$this->db->query("UPDATE `ospos_app_config` SET `value` = '20' WHERE `key` = 'nbre_jour_prevision_stock'");
			}
			else
			{
                $sql=$this->db->query("INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('nbre_jour_prevision_stock', '20')");
			}
		}	//*/

		//Récupération de la valeur pour l'ajout du mode de payment VS: Distributeur Vapeself poue la machine VapeSelf
/*		$sql_payment_method_VapeSelf = $this->db->query("SELECT * FROM `ospos_payment_methods` WHERE `payment_method_id` = 10 ");
		$sql_payment_method_VapeSelf = $sql_payment_method_VapeSelf->result_array();
		if(!isset($sql_payment_method_VapeSelf[0]['payment_method_id']))
		{
			//
			$sql=$this->db->query('INSERT INTO `ospos_payment_methods`(`payment_method_id`, `payment_method_code`, `payment_method_description`, `payment_method_include`, `payment_method_display_order`, `payment_method_fidelity_flag`, `payment_method_giftcard_flag`, `deleted`, `branch_code`) VALUES(10, "VS", "Distributeur VapeSelf", "Y", 26, "N", "N", 0, "' . $this->config->item('branch_code') . '");');
		}
		
		//Récupération de la valeur de la card_code
		$sql_card_code_customers = $this->db->query("SELECT * FROM `ospos_customers` LIMIT 1 ");
		$sql_card_code_customers = $sql_card_code_customers->result_array();
		if(!isset($sql_card_code_customers[0]['card_code']))
		{
			//
	//		$sql=$this->db->query('ALTER TABLE `ospos_customers` ADD `card_code` VARCHAR(4) NOT NULL AFTER `fidelity_points`;');		
	//	    ALTER TABLE `ospos_customers` ADD `card_code_0` VARCHAR(4) NULL AFTER `fidelity_points`;

        	$sql=$this->db->query('ALTER TABLE `ospos_customers` ADD `card_code` VARCHAR(4) NULL DEFAULT ' . "''" . ' AFTER `fidelity_points`;');
		}


		//Récupération de la valeur pour l'ajout d'une machine VapeSelf
		$sql_VapeSelf = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'distributeur_vapeself' ");
		$sql_VapeSelf = $sql_VapeSelf->result_array();
		
		//Si ces 2 valeurs ne sont pas dans la table ospos_app_config alors elles sont insérer avec des valuers par défaut
		if(($sql_VapeSelf[0]['value']=="" && $sql_VapeSelf[0]['key']=="historique") || !isset($sql_VapeSelf[0]))
		{
			if($sql_VapeSelf[0]['key']=="historique")
			{
                $sql=$this->db->query("UPDATE `ospos_app_config` SET `value` = 'N' WHERE `key` = 'distributeur_vapeself'");
			}
			else
			{
				$sql=$this->db->query("INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('distributeur_vapeself', 'N')");
			}
		}
		
		

/*ALTER TABLE `ospos_items` CHANGE `custom1` `offer_value` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `ospos_items` CHANGE `offer_value` `offer_value` DOUBLE(12,2) NULL DEFAULT '0';

ALTER TABLE `ospos_items` CHANGE `custom1` `offer_value` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `ospos_items` CHANGE `offer_value` `offer_value` DOUBLE(12,2) NULL DEFAULT '0';

UPDATE `ospos_items` SET `offer_value` = 0;
//*/
 /*      $sql_change_name = $this->db->query("SELECT * FROM `ospos_items` LIMIT 1");
        $sql_change_name = $sql_change_name->result_array();
		if(!isset($sql_change_name[0]['offer_value']))
		{
			$sql=$this->db->query("ALTER TABLE `ospos_items` CHANGE `custom1` `offer_value` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
			$sql=$this->db->query("ALTER TABLE `ospos_items` CHANGE `offer_value` `offer_value` DOUBLE(12,2) NULL DEFAULT '0';");
			$sql=$this->db->query("UPDATE `ospos_items` SET `offer_value` = 0;");
		}

		$sql_multi_vendeur = $this->db->query("SELECT * FROM `ospos_app_config` WHERE `key` = 'multi_vendeur' ");
		$sql_multi_vendeur = $sql_multi_vendeur->result_array();
		if(!isset($sql_multi_vendeur[0]['key']))
		{
			$sql=$this->db->query("INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('multi_vendeur', 'N')");
		}
		//*/
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

