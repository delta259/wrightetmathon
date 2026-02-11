<?php //if (!defined('BASEPATH')) exit('No direct script access allowed');
    
    require_once "/var/www/html/wrightetmathon/application/third_party/PHPExcel/PHPExcel.php";
 
	class Excel extends PHPExcel 
	{ 
		public function __construct() 
		{ 
			parent::__construct(); 
		} 
	}
?>
