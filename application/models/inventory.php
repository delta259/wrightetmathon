<?php
class Inventory extends CI_Model 
{	
	function insert($inventory_data)
	{
		return $this->db->insert('inventory', $inventory_data);
	}
	
	function get_inventory_data_for_item($item_id)
	{
		$this->db->from('inventory');
		$this->db->where('trans_items',$item_id);
		$this->db->where('inventory.branch_code', $this->config->item('branch_code'));
		$this->db->order_by("trans_date", "desc");
		$this->db->order_by("trans_id", "desc");		
		//$this->db->limit(10);
		return $this->db->get();		
	}

	function get_date_last_inventory()
	{
        $sql = '
        
        (SELECT MAX(`trans_date`) as trans_last_date, `trans_date`, `ospos_employees`.`username`,`ospos_items`.`item_number`, `ospos_items`.`name`,`trans_stock_before`,`trans_inventory`,`trans_stock_after`,`trans_comment` 
        FROM `ospos_inventory`
        JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`
        WHERE `trans_comment` NOT LIKE "%Article AJOUTE par CENTRAL%" AND 
        `trans_comment` NOT LIKE "%Article créé%" AND 
        `trans_comment` NOT LIKE "%Article désactivé%" AND 
        `trans_comment` NOT LIKE "%Article réactivé%" AND 
        `trans_comment` NOT LIKE "%fusion%" AND 
        `trans_comment` NOT LIKE "%RECV-%" AND 
        `trans_comment` NOT LIKE "%SALE_%" AND 
        `trans_comment` NOT LIKE "%CMDE_%" AND 
        `trans_comment` NOT LIKE "%SART-%" AND 
        `trans_comment` NOT LIKE "%SUSP-%" AND 
        `trans_comment` NOT LIKE "%SUSR-%" AND 
        `trans_comment` NOT LIKE "%Article MAJ par CENTRAL%" AND
        (`trans_comment` LIKE "%Inventaire comptable%" OR
        `trans_comment` LIKE "%Stock Tournant%") AND
        `ospos_items`.`deleted` = "0"
        
        GROUP BY `ospos_inventory`.`trans_items`
        ORDER BY `ospos_items`.`name`)
        UNION 
        (SELECT
        MAX(`trans_date`) as trans_last_date, `trans_date`, `ospos_employees`.`username`,`ospos_items`.`item_number`, `ospos_items`.`name`,`trans_stock_before`,`trans_inventory`,`trans_stock_after`,`trans_comment` 
    FROM
        `ospos_inventory`
    JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
    JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`
    WHERE
        `trans_items` NOT IN(
        SELECT
            `trans_items`
        FROM
            `ospos_inventory`
        JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        WHERE
            (
                `trans_comment` LIKE "%Article créé%" OR
                `trans_comment` LIKE "%Article désactivé%" OR 
                `trans_comment` LIKE "%Article réactivé%" OR 
                `trans_comment` LIKE "%fusion%" OR 
                `trans_comment` LIKE "%RECV-%" OR 
                `trans_comment` LIKE "%SALE_%" OR 
                `trans_comment` LIKE "%CMDE_%" OR 
                `trans_comment` LIKE "%SART-%" OR 
                `trans_comment` LIKE "%SUSP-%" OR 
                `trans_comment` LIKE "%SUSR-%" OR 
                `trans_comment` LIKE "%Inventaire comptable%" OR
                `trans_comment` LIKE "%Stock Tournant%"
            ) AND 
            `trans_comment` NOT LIKE "%CENTRALE%" AND
            `ospos_items`.`deleted` = "0"
        GROUP BY
            `ospos_inventory`.`trans_items`
    ) AND `ospos_items`.`deleted` = "0"
        GROUP BY
            `ospos_inventory`.`trans_items`
    ORDER BY
        `ospos_items`.`name`)
        
        
        
        
        
        
        
        ';

$sql = '
SELECT
ref.`trans_date` as trans_last_date, ref.`username`, ref.`item_number`, ref.`name`, ref.`trans_stock_before`, ref.`trans_inventory`, ref.`trans_stock_after`, ref.`trans_comment` 
     
FROM
 (SELECT `trans_date`, `ospos_employees`.`username`,`ospos_items`.`item_number`, `ospos_items`.`name`, `trans_stock_before`, `trans_inventory`, `trans_stock_after`, `trans_comment`, `trans_items` FROM `ospos_inventory` JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
     JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`) as ref
,
 ((SELECT
     MAX(`trans_date`) AS max_date, `trans_items`
  FROM `ospos_inventory`
  JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
  JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`
  WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
  AND 
    `trans_comment` NOT LIKE "%Article AJOUTE par CENTRAL%" AND 
     `trans_comment` NOT LIKE "%Article créé%" AND 
     `trans_comment` NOT LIKE "%Article désactivé%" AND 
     `trans_comment` NOT LIKE "%Article réactivé%" AND 
     `trans_comment` NOT LIKE "%fusion%" AND 
     `trans_comment` NOT LIKE "%RECV-%" AND 
     `trans_comment` NOT LIKE "%SALE_%" AND 
     `trans_comment` NOT LIKE "%CMDE_%" AND 
     `trans_comment` NOT LIKE "%SART-%" AND 
     `trans_comment` NOT LIKE "%SUSP-%" AND 
     `trans_comment` NOT LIKE "%SUSR-%" AND 
     `trans_comment` NOT LIKE "%Article MAJ par CENTRAL%" AND
     (`trans_comment` LIKE "%Inventaire comptable%" OR
     `trans_comment` LIKE "%Stock Tournant%") AND
     `ospos_items`.`deleted` = "0"
  GROUP BY `ospos_inventory`.`trans_items`) UNION 
  (SELECT MAX(`trans_date`) AS max_date, `trans_items`  
   FROM `ospos_inventory`
  JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
    WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
  AND `trans_items` NOT IN(
     SELECT
         `trans_items`
     FROM
         `ospos_inventory`
     JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
     WHERE
         (
             `trans_comment` LIKE "%Article créé%" OR
             `trans_comment` LIKE "%Article désactivé%" OR 
             `trans_comment` LIKE "%Article réactivé%" OR 
             `trans_comment` LIKE "%fusion%" OR 
             `trans_comment` LIKE "%RECV-%" OR 
             `trans_comment` LIKE "%SALE_%" OR 
             `trans_comment` LIKE "%CMDE_%" OR 
             `trans_comment` LIKE "%SART-%" OR 
             `trans_comment` LIKE "%SUSP-%" OR 
             `trans_comment` LIKE "%SUSR-%" OR 
             `trans_comment` LIKE "%Inventaire comptable%" OR
             `trans_comment` LIKE "%Stock Tournant%"
         ) AND 
         `trans_comment` NOT LIKE "%CENTRALE%" AND
         `ospos_items`.`deleted` = "0"
     GROUP BY `ospos_inventory`.`trans_items`)
  GROUP BY `ospos_inventory`.`trans_items`
  )
 ) AS tab
WHERE
 tab.max_date = ref.`trans_date` AND tab.`trans_items` = ref.`trans_items`;';




  /*      $sql = '
        SELECT MAX(`trans_date`) as trans_last_date,`ospos_employees`.`username`,`ospos_items`.`name`,`trans_stock_before`,`trans_inventory`,`trans_stock_after`,`trans_comment` 
        FROM `ospos_inventory`
        JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`
        WHERE 
        `trans_comment` NOT LIKE "%Article créé%" AND 
        `trans_comment` NOT LIKE "%Article désactivé%" AND 
        `trans_comment` NOT LIKE "%Article réactivé%" AND 
        `trans_comment` NOT LIKE "%fusion%" AND 
        `trans_comment` NOT LIKE "%RECV-%" AND 
        `trans_comment` NOT LIKE "%SALE_%" AND 
        `trans_comment` NOT LIKE "%CMDE_%" AND 
        `trans_comment` NOT LIKE "%SART-%" AND 
        `trans_comment` NOT LIKE "%SUSP-%" AND 
        `trans_comment` NOT LIKE "%SUSR-%" AND 
        (`trans_comment` LIKE "%Inventaire comptable%" OR
        `trans_comment` LIKE "%Stock Tournant%") AND
        `ospos_items`.`deleted` = "0"
        
        GROUP BY `ospos_inventory`.`trans_items`
        ORDER BY `ospos_items`.`name`;'; //*/
        
        

/*
        $sql = 'SELECT `ospos_inventory`.`trans_date` as trans_last_date, tab.`trans_date`, tab.`username`,tab.`item_number`, tab.`name`,`ospos_inventory`.`trans_stock_before`,`ospos_inventory`.`trans_inventory`,`ospos_inventory`.`trans_stock_after`,`ospos_inventory`.`trans_comment`
        FROM `ospos_inventory`, (SELECT MAX(`trans_date`) as trans_last_date, `trans_date`, `ospos_employees`.`username`,`ospos_items`.`item_number`, `ospos_items`.`name`,`trans_stock_before`,`trans_inventory`,`trans_stock_after`,`trans_comment`,  `ospos_items`.`deleted`, `trans_id`
                FROM `ospos_inventory`
                JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
                JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`
                WHERE `trans_comment` NOT LIKE "%Article AJOUTE par CENTRAL%" AND 
                `trans_comment` NOT LIKE "%Article créé%" AND 
                `trans_comment` NOT LIKE "%Article désactivé%" AND 
                `trans_comment` NOT LIKE "%Article réactivé%" AND 
                `trans_comment` NOT LIKE "%fusion%" AND 
                `trans_comment` NOT LIKE "%RECV-%" AND 
                `trans_comment` NOT LIKE "%SALE_%" AND 
                `trans_comment` NOT LIKE "%CMDE_%" AND 
                `trans_comment` NOT LIKE "%SART-%" AND 
                `trans_comment` NOT LIKE "%SUSP-%" AND 
                `trans_comment` NOT LIKE "%SUSR-%" AND 
                `trans_comment` NOT LIKE "%Article MAJ par CENTRAL%" AND
                (`trans_comment` LIKE "%Inventaire comptable%" OR
                `trans_comment` LIKE "%Stock Tournant%") AND
                `ospos_items`.`deleted` = "0" 
                
                GROUP BY `ospos_inventory`.`trans_items`) as tab,
                `ospos_items`
        WHERE tab.trans_last_date = `ospos_inventory`.`trans_date`
        AND `ospos_items`.`deleted` ="0"
        AND `ospos_items`.`item_id` = `ospos_inventory`.`trans_items`
        ; ';//*/

        $sql='
        SELECT
   ref.`trans_date` as trans_last_date, ref.`username`, ref.`item_number`, ref.`name`, ref.`trans_stock_before`, ref.`trans_inventory`, ref.`trans_stock_after`, ref.`trans_comment` 
        
FROM
    (SELECT `trans_date`, `ospos_employees`.`username`,`ospos_items`.`item_number`, `ospos_items`.`name`, `trans_stock_before`, `trans_inventory`, `trans_stock_after`, `trans_comment`, `trans_items` FROM `ospos_inventory` JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`) as ref
   ,
    ((SELECT
        MAX(`trans_date`) AS max_date, `trans_items`
     FROM `ospos_inventory`
     JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
     JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`
     WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
     AND 
       `trans_comment` NOT LIKE "%Article AJOUTE par CENTRAL%" AND 
        `trans_comment` NOT LIKE "%Article créé%" AND 
        `trans_comment` NOT LIKE "%Article désactivé%" AND 
        `trans_comment` NOT LIKE "%Article réactivé%" AND 
        `trans_comment` NOT LIKE "%fusion%" AND 
        `trans_comment` NOT LIKE "%RECV-%" AND 
        `trans_comment` NOT LIKE "%SALE_%" AND 
        `trans_comment` NOT LIKE "%CMDE_%" AND 
        `trans_comment` NOT LIKE "%SART-%" AND 
        `trans_comment` NOT LIKE "%SUSP-%" AND 
        `trans_comment` NOT LIKE "%SUSR-%" AND 
        `trans_comment` NOT LIKE "%Article MAJ par CENTRAL%" AND
        (`trans_comment` LIKE "%Inventaire comptable%" OR
        `trans_comment` LIKE "%Stock Tournant%") AND
        `ospos_items`.`deleted` = "0"
     GROUP BY `ospos_inventory`.`trans_items`) UNION 
     (SELECT MAX(`trans_date`) AS max_date, `trans_items`  
      FROM `ospos_inventory`
     JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
       WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
     AND `trans_items` NOT IN(
        SELECT
            `trans_items`
        FROM
            `ospos_inventory`
        JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        WHERE
            ( 
                `trans_comment` LIKE "%Inventaire comptable%" OR
                `trans_comment` LIKE "%Stock Tournant%"
            ) AND 
            `trans_comment` NOT LIKE "%CENTRALE%" AND
            `ospos_items`.`deleted` = "0"
        GROUP BY `ospos_inventory`.`trans_items`)
     GROUP BY `ospos_inventory`.`trans_items`
     )
    ) AS tab
WHERE
    tab.max_date = ref.`trans_date` AND tab.`trans_items` = ref.`trans_items`;
        ';
		$query =$this->db->query($sql);
		$result = $query->result_array();
        return $result;
        
/*


SELECT
   ref.`trans_date` as trans_last_date, ref.`username`, ref.`item_number`, ref.`name`, ref.`trans_stock_before`, ref.`trans_inventory`, ref.`trans_stock_after`, ref.`trans_comment`    
FROM
    (SELECT 
     `trans_date`, `ospos_employees`.`username`,`ospos_items`.`item_number`, `ospos_items`.`name`, `trans_stock_before`, `trans_inventory`, `trans_stock_after`, `trans_comment`, `trans_items`
     FROM `ospos_inventory` 
     JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
     JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`) as ref
   ,
    (SELECT
        MAX(`trans_date`) AS max_date,
        `trans_items`
     FROM `ospos_inventory`
     WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
     GROUP BY `ospos_inventory`.`trans_items`) AS tab
WHERE
    tab.max_date = ref.`trans_date` AND tab.`trans_items` = ref.`trans_items` AND ref.`trans_items` = 11267;
    
    SELECT
   ref.`trans_date` as trans_last_date, ref.`username`, ref.`item_number`, ref.`name`, ref.`trans_stock_before`, ref.`trans_inventory`, ref.`trans_stock_after`, ref.`trans_comment` 
        
FROM
    (SELECT `trans_date`, `ospos_employees`.`username`,`ospos_items`.`item_number`, `ospos_items`.`name`, `trans_stock_before`, `trans_inventory`, `trans_stock_after`, `trans_comment`, `trans_items` FROM `ospos_inventory` JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`) as ref
   ,
    (SELECT
        MAX(`trans_date`) AS max_date, `trans_items`
     FROM `ospos_inventory`
     JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
     JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`
     WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
     AND 
       `trans_comment` NOT LIKE "%Article AJOUTE par CENTRAL%" AND 
        `trans_comment` NOT LIKE "%Article créé%" AND 
        `trans_comment` NOT LIKE "%Article désactivé%" AND 
        `trans_comment` NOT LIKE "%Article réactivé%" AND 
        `trans_comment` NOT LIKE "%fusion%" AND 
        `trans_comment` NOT LIKE "%RECV-%" AND 
        `trans_comment` NOT LIKE "%SALE_%" AND 
        `trans_comment` NOT LIKE "%CMDE_%" AND 
        `trans_comment` NOT LIKE "%SART-%" AND 
        `trans_comment` NOT LIKE "%SUSP-%" AND 
        `trans_comment` NOT LIKE "%SUSR-%" AND 
        `trans_comment` NOT LIKE "%Article MAJ par CENTRAL%" AND
        (`trans_comment` LIKE "%Inventaire comptable%" OR
        `trans_comment` LIKE "%Stock Tournant%") AND
        `ospos_items`.`deleted` = "0"
     GROUP BY `ospos_inventory`.`trans_items`) AS tab
WHERE
    tab.max_date = ref.`trans_date` AND tab.`trans_items` = ref.`trans_items`;
    
    SELECT
   ref.`trans_date` as trans_last_date, ref.`username`, ref.`item_number`, ref.`name`, ref.`trans_stock_before`, ref.`trans_inventory`, ref.`trans_stock_after`, ref.`trans_comment` 
        
FROM
    (SELECT `trans_date`, `ospos_employees`.`username`,`ospos_items`.`item_number`, `ospos_items`.`name`, `trans_stock_before`, `trans_inventory`, `trans_stock_after`, `trans_comment`, `trans_items` FROM `ospos_inventory` JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`) as ref
   ,
    ((SELECT
        MAX(`trans_date`) AS max_date, `trans_items`
     FROM `ospos_inventory`
     JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
     JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`
     WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
     AND 
       `trans_comment` NOT LIKE "%Article AJOUTE par CENTRAL%" AND 
        `trans_comment` NOT LIKE "%Article créé%" AND 
        `trans_comment` NOT LIKE "%Article désactivé%" AND 
        `trans_comment` NOT LIKE "%Article réactivé%" AND 
        `trans_comment` NOT LIKE "%fusion%" AND 
        `trans_comment` NOT LIKE "%RECV-%" AND 
        `trans_comment` NOT LIKE "%SALE_%" AND 
        `trans_comment` NOT LIKE "%CMDE_%" AND 
        `trans_comment` NOT LIKE "%SART-%" AND 
        `trans_comment` NOT LIKE "%SUSP-%" AND 
        `trans_comment` NOT LIKE "%SUSR-%" AND 
        `trans_comment` NOT LIKE "%Article MAJ par CENTRAL%" AND
        (`trans_comment` LIKE "%Inventaire comptable%" OR
        `trans_comment` LIKE "%Stock Tournant%") AND
        `ospos_items`.`deleted` = "0"
     GROUP BY `ospos_inventory`.`trans_items`) UNION 
     (SELECT MAX(`trans_date`) AS max_date, `trans_items`  
      FROM `ospos_inventory`
     JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
       WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
     AND `trans_items` NOT IN(
        SELECT
            `trans_items`
        FROM
            `ospos_inventory`
        JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        WHERE
            (
                `trans_comment` LIKE "%Article créé%" OR
                `trans_comment` LIKE "%Article désactivé%" OR 
                `trans_comment` LIKE "%Article réactivé%" OR 
                `trans_comment` LIKE "%fusion%" OR 
                `trans_comment` LIKE "%RECV-%" OR 
                `trans_comment` LIKE "%SALE_%" OR 
                `trans_comment` LIKE "%CMDE_%" OR 
                `trans_comment` LIKE "%SART-%" OR 
                `trans_comment` LIKE "%SUSP-%" OR 
                `trans_comment` LIKE "%SUSR-%" OR 
                `trans_comment` LIKE "%Inventaire comptable%" OR
                `trans_comment` LIKE "%Stock Tournant%"
            ) AND 
            `trans_comment` NOT LIKE "%CENTRALE%" AND
            `ospos_items`.`deleted` = "0"
        GROUP BY `ospos_inventory`.`trans_items`)
     GROUP BY `ospos_inventory`.`trans_items`
     )
    ) AS tab
WHERE
    tab.max_date = ref.`trans_date` AND tab.`trans_items` = ref.`trans_items`;

    SELECT
   ref.`trans_date` as trans_last_date, ref.`username`, ref.`item_number`, ref.`name`, ref.`trans_stock_before`, ref.`trans_inventory`, ref.`trans_stock_after`, ref.`trans_comment` 
        
FROM
    (SELECT `trans_date`, `ospos_employees`.`username`,`ospos_items`.`item_number`, `ospos_items`.`name`, `trans_stock_before`, `trans_inventory`, `trans_stock_after`, `trans_comment`, `trans_items` FROM `ospos_inventory` JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`) as ref
   ,
    ((SELECT
        MAX(`trans_date`) AS max_date, `trans_items`
     FROM `ospos_inventory`
     JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
     JOIN `ospos_employees` ON `ospos_inventory`.`trans_user` = `ospos_employees`.`person_id`
     WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
     AND 
       `trans_comment` NOT LIKE "%Article AJOUTE par CENTRAL%" AND 
        `trans_comment` NOT LIKE "%Article créé%" AND 
        `trans_comment` NOT LIKE "%Article désactivé%" AND 
        `trans_comment` NOT LIKE "%Article réactivé%" AND 
        `trans_comment` NOT LIKE "%fusion%" AND 
        `trans_comment` NOT LIKE "%RECV-%" AND 
        `trans_comment` NOT LIKE "%SALE_%" AND 
        `trans_comment` NOT LIKE "%CMDE_%" AND 
        `trans_comment` NOT LIKE "%SART-%" AND 
        `trans_comment` NOT LIKE "%SUSP-%" AND 
        `trans_comment` NOT LIKE "%SUSR-%" AND 
        `trans_comment` NOT LIKE "%Article MAJ par CENTRAL%" AND
        (`trans_comment` LIKE "%Inventaire comptable%" OR
        `trans_comment` LIKE "%Stock Tournant%") AND
        `ospos_items`.`deleted` = "0"
     GROUP BY `ospos_inventory`.`trans_items`) UNION 
     (SELECT MAX(`trans_date`) AS max_date, `trans_items`  
      FROM `ospos_inventory`
     JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
       WHERE `trans_items` IN(SELECT `item_id` FROM `ospos_items` WHERE `ospos_items`.`deleted` = "0")
     AND `trans_items` NOT IN(
        SELECT
            `trans_items`
        FROM
            `ospos_inventory`
        JOIN `ospos_items` ON `ospos_inventory`.`trans_items` = `ospos_items`.`item_id`
        WHERE
            ( 
                `trans_comment` LIKE "%Inventaire comptable%" OR
                `trans_comment` LIKE "%Stock Tournant%"
            ) AND 
            `trans_comment` NOT LIKE "%CENTRALE%" AND
            `ospos_items`.`deleted` = "0"
        GROUP BY `ospos_inventory`.`trans_items`)
     GROUP BY `ospos_inventory`.`trans_items`
     )
    ) AS tab
WHERE
    tab.max_date = ref.`trans_date` AND tab.`trans_items` = ref.`trans_items`;

        //*/
	}
}
?>
