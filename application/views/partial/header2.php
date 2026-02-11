<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<base href="<?php echo base_url();?>" />
	<title><?php echo $this->config->item('company').' -- '.$this->lang->line('common_powered_by')?></title> <!--Modified HBW V1-->
	<link rel="stylesheet" rev="stylesheet" href="<?php echo base_url();?>css/ospos.css" />
	<link rel="stylesheet" rev="stylesheet" href="<?php echo base_url();?>css/ospos_print.css"  media="print"/>
	<script>BASE_URL = '<?php echo site_url(); ?>';</script>
	<!--<script src="<?php echo base_url();?>js/jquery-3.3.1.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->
	<script src="<?php echo base_url();?>js/jquery-1.2.6.min.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	<!--<script src="<?php echo base_url();?>js/jquery.color.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->
	<!--<script src="<?php echo base_url();?>js/jquery.metadata.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->
	<!--<script src="<?php echo base_url();?>js/jquery.form.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->
	<!--<script src="<?php echo base_url();?>js/jquery.tablesorter.min.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->
	<!--<script src="<?php echo base_url();?>js/jquery.ajax_queue.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->
	<!--<script src="<?php echo base_url();?>js/jquery.bgiframe.min.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->
	<script src="<?php echo base_url();?>js/jquery.autocomplete.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	<script src="<?php echo base_url();?>js/jquery.validate.min.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	<!--<script src="<?php echo base_url();?>js/jquery.jkey-1.1.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->
	<script src="<?php echo base_url();?>js/thickbox.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	<!--<script src="<?php echo base_url();?>js/common.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->
	<script src="<?php echo base_url();?>js/manage_tables.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	<script src="<?php echo base_url();?>js/date.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	<script src="<?php echo base_url();?>js/datepicker.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	
	<!-- -->
	<!-- Load check browser close -->
	<!-- --> 
	<script src="<?php echo base_url();?>js/check_browser_close.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	
	<!-- -->
	<!-- Load the chart library from FusionCharts -->
	<!-- --> 
	<script src="<?php echo base_url();?>Charts/FusionCharts.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
	
	


<!-- <style type="text/css">
html	{
		overflow: auto;
		}
</style> -->


</head>

<!-- -->
<!-- body -->
<!-- -->
<body>
	
<div id="menubar">
	<div id="menubar_container">
		<div id="menubar_company_info">
			<?php
				echo $this->config->item('branch_code');
			?> 
		</div>

		<div id="menubar_navigation">
			<?php
				foreach($_SESSION['G']->modules as $module_id => $module_info)
				{		
					// check user has permission to this module
					$has_permission			=	$this->Employee->has_permission($module_id, $_SESSION['G']->login_employee_id);
					
					if ($has_permission)
					{
						if ($module_info['show_in_header'] == 'Y')
						{
							// initialise
							$url			=	'';
							$target			=	'';
							
							switch ($module_info['module_name'])
							{
								case 'sonrisa':
									$url 	=	prep_url($this->config->item('website'));
									$target	=	'target="_blank"';	
									break;
									
								case 'drive':
									$url 	=	prep_url('drive.google.com/drive/#my-drive');
									$target	=	'target="_blank"';
									break;
									
								case 'print':
									$url	=	'';
									$target	=	'onClick="window.print()"';
									break;
									
								case 'catalogue':
									$url	=	prep_url('https://drive.google.com/open?id=0B2l4-1fUyaoKMmpiSHV2R2ota2s');
									$target	=	'target="_blank"';
									break;
									
								case 'copyight':
									$url	=	'';
									$target	=	'onClick="window.print()"';
									break;
									
								case 'security':
									$url	=	prep_url('drive.google.com/folderview?id=0B1DlcJN8YXtnZW9LUUdNaHoxdFU&usp=sharing');
									$target	=	'target="_blank"';
									break;
								
								default:
									$url 	=	$module_info['module_name'];
									$target	=	'target="_self"';
									break;
							}
									
							// create image array
							$img	=	array	(
												'src'	=>	base_url().'images/menubar/'.$module_info['module_name'].'.png',
												'border'=>	'0',
												'alt'	=>	$module_info['module_name']
												);
												
							// create the anchor
							echo	"<div class='menu_item'>".anchor ($url, img($img), $target)
							."<br>"
							.anchor ($url, $this->lang->line($module_info['name_lang_key']), $target)
							."</div>";
						}
					}	
				}
				?>
			<!-- div for spinner -->
			<div id="spinner_on_bar" class="spinner_on_bar" style="display:none;">
				<img id="img-spinner" src="<?php echo base_url();?>images/M&Wloader.gif" alt="Loading"/>
			</div>	
		</div>
	</div>

		<!-- Output User info -->
		<div id="menubar_footer">
			<?php 
				echo $this->lang->line('common_welcome').' '.$_SESSION['G']->login_employee_info->first_name.' | ';
				echo anchor("home/logout",$this->lang->line("common_logout"));
				echo ' | ';
				echo anchor("home/index",$this->lang->line("common_home"));
				echo ' | ';
				echo anchor("home/backup",$this->lang->line("common_backup"));
			?>
		</div>
					
		<!-- Output Security passwords -->
		<div id="menubar_codes">
			<?php
			echo $this->lang->line('config_Alarm_OK_code'). ' = ' .$this->config->item('Alarm_OK_code');
			echo '  |  ';
			echo $this->lang->line('config_Alarm_KO_code'). ' = ' .$this->config->item('Alarm_KO_code');
			?>
		</div>

		<div id="menubar_date">
		<?php echo date('d/m/Y') ?> <!--Modified HBW V1-->
		</div>

</div>

<div id="content_area_wrapper">
<div id="content_area">
