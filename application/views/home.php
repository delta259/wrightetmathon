

<?php  $this->load->view("reports/inventory_rolling"); ?>


<!-- <?php /* $this->load->view("partial/header"); ?>
<br />
<fieldset class="fieldset">
				<table class="table_center">			
					<tbody>
						<tr>
							<td align="center"><h3><a href="<?php echo site_url('home/user');?>"><?php echo $this->lang->line('login_user_menu'); ?></a></h3></td>
						</tr>
						<tr>
							<?php
								if ($_SESSION['G']->login_employee_info->username == $this->config->item('admin_user_name') OR $_SESSION['G']->login_employee_info->username == $this->config->item('sys_admin_user_name'))
								{
									?>
									<td align="center"><h3><a href="<?php echo site_url('home/admin');?>"><?php echo $this->lang->line('login_admin_menu'); ?></a></h3></td>
									<?php
								}
							?>
						</tr>
						<tr>
							<?php
								if ($_SESSION['G']->login_employee_info->username == $this->config->item('sys_admin_user_name'))
								{
									?>
									<td align="center"><h3><a href="<?php echo site_url('home/sysadmin');?>"><?php echo $this->lang->line('login_sys_admin_menu'); ?></a></h3></td>
									<?php
								}
							?>
						</tr>
					</tbody>
				</table>
</fieldset>

<?php $this->load->view("partial/footer"); ?>

	<?php
		$module_list = array();	
		foreach($_SESSION['G']->modules as $module_id => $module_info)
		{		
			// check user has permission to this module
			$has_permission			=	$this->Employee->has_permission($module_id, $_SESSION['G']->login_employee_id);
			
			if ($has_permission)
			{
	?>
				<div class="module_item">
					
					<?php
						switch ($module_info['module_name'])
						{
							case	'drive':
					?>
									<a 
										href="https://drive.google.com/drive/#my-drive" target="_blank">
										<img src="<?php echo base_url().'images/menubar/'.$module_info['module_name'].'.png';?>" border="0" alt="Menubar Image"></a>
									
									<a 
										href="https://drive.google.com/drive/#my-drive" target="_blank">
										<?php echo $this->lang->line("module_".$module_info['module_name']) ?></a>
										--> <?php echo $this->lang->line('modules_'.$module_info['module_name'].'_desc');?>

					<?php
							break;
							
							case	'security':	
					?>
									<a 
										href="https://drive.google.com/folderview?id=0B1DlcJN8YXtnZW9LUUdNaHoxdFU&usp=sharing" target="_blank">
										<img src="<?php echo base_url().'images/menubar/'.$module_info['module_name'].'.png';?>" border="0" alt="Menubar Image"></a>
									
									<a 
										href="https://drive.google.com/folderview?id=0B1DlcJN8YXtnZW9LUUdNaHoxdFU&usp=sharing" target="_blank">
										<?php echo $this->lang->line("module_".$module_info['module_name']) ?></a>
										--> <?php echo $this->lang->line('modules_'.$module_info['module_name'].'_desc');?>
					<?php
							break;
							
							default:
					?>	
						
									<a 
										href="<?php echo site_url($module_info['module_name']);?>">
										<img src="<?php echo base_url().'images/menubar/'.$module_info['module_name'].'.png';?>" border="0" alt="Menubar Image"></a>
									
									<a 
										href="<?php echo site_url($module_info['module_name']);?>"><?php echo $this->lang->line("module_".$module_info['module_name']) ?></a>
										--> <?php echo $this->lang->line('modules_'.$module_info['module_name'].'_desc');?>
					<?php
							break;
						}
					?>
				</div>
		<?php
			}
		}
	?>
</div>
<?php $this->load->view("partial/footer"); */?>
-->