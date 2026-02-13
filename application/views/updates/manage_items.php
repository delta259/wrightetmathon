<?php $this->load->view("partial/header"); ?>

<div id="title_bar">
	<div id="page_title" style="margin-bottom:8px;"><?php 
															echo $this->lang->line('reports_report_input');
															echo $this->lang->line('common_connector');
															echo $this->lang->line('items_import');
															include('../wrightetmathon/application/views/partial/show_buttons.php');
													 ?>
	</div>
</div>

<fieldset class="fieldset">
	
<!-- output messages if not modal -->
<?php 
if (!isset($_SESSION['show_dialog']))
	{
		include('../wrightetmathon/application/views/partial/show_messages.php');
	}
?>

<?php

// when clicked use the controller, selecting method  
			echo form_open_multipart('updates/manage_items_manual');
			?>
				<table>			
					<tbody>
						<td>
							<table>	
								<tr>			
									<td align="center">	<?php 
												echo $this->lang->line('common_choose_file');
												echo $this->lang->line('common_space');
												echo form_upload	(	array	(
																				'name'	=>	'userfile',
																				'id'	=>	'userfile',
																				'size'	=>	20,
																				'style'	=>	'text-align:center; font-size:16px;'
																				)
																	);?>
									</td>
								</tr>
								<tr>			
									<td align="center">	<?php 
												echo $this->lang->line('common_edit');
												echo $this->lang->line('common_space');
												echo $this->lang->line('items_unit_price_with_tax');
												echo $this->lang->line('common_question');
												echo $this->lang->line('common_space');
												echo form_dropdown	(	
																	'update_sales_price',
																	$_SESSION['G']->YorN_pick_list, 
																	$_SESSION['selected_update_sales_price'],
																	'style="font-size:16px"'
																	);?>
									</td>
								</tr>
								<tr>			
									<td align="center">	<?php 
												echo $this->lang->line('items_create');
												echo $this->lang->line('common_question');
												echo $this->lang->line('common_space');
												echo form_dropdown	(	
																	'create',
																	$_SESSION['G']->YorN_pick_list, 
																	$_SESSION['selected_create'],
																	'style="font-size:16px"'
																	);?>
									</td>
								</tr>
							</table>
						</td>
						
						<td>
							<table>
								<tr>
									<td align="center" style="font-weight: bold; background-color:powderblue" colspan="5"><?php echo $this->lang->line('imports_data_model'); ?></td>
								</tr>
								<tr>
									<td align="center" style="font-weight: bold"><?php echo $this->lang->line('imports_column_letter'); ?></td>
									<td align="center" style="font-weight: bold"><?php echo $this->lang->line('imports_column_label'); ?></td>
									<td align="center" style="font-weight: bold"><?php echo $this->lang->line('imports_column_number'); ?></td>
									<td align="center" style="font-weight: bold"><?php echo $this->lang->line('imports_column_data_type'); ?></td>
									<td align="center" style="font-weight: bold"><?php echo $this->lang->line('imports_column_database_field_name'); ?></td>
								</tr>
								
								<?php
									foreach ($_SESSION['data_model']->result() as $column)
									{
										?>
										<tr>
											<td align="center"><?php echo $column->column_letter; ?></td>
											<td align="center"><?php echo $column->column_label; ?></td>
											<td align="center"><?php echo $column->column_number; ?></td>
											<td align="center"><?php echo $_SESSION['C']->data_type_pick_list[$column->column_data_type]; ?></td>
											<td align="center"><?php echo $column->column_database_field_name; ?></td>
											<?php
										}
										?>
										</tr>
							</table>
						</td>
					</tbody>
				</table>
</fieldset>
<div class="txt_milieu">

<?php
$target	=	'target="_self"';
echo anchor			(
    'common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>',
    $target
);
?>
<?php
echo form_submit					(	array	(
												'name'	=>	'generate_report',
												'id'	=>	'generate_report',
												'value'	=>	$this->lang->line('items_import'),
												'class'	=>	'btsubmit'
												)
									);
?>
</div>
<?php
	echo form_close();
?>

<div>
	<br/>
	<?php $this->load->view("partial/pre_footer"); $this->load->view("partial/footer"); ?>
</div>

</script>
