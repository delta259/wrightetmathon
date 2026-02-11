<dialog open class="dialog">
	<div id="title_bar">
		<div id="page_title" class="float_left">
			<?php
				echo $this->lang->line('items_kit_structure').' => '.$_SESSION['kit_info']->item_info->kit_reference;
			?>
		</div>
	
		<div id="page_subtitle" class="float_left">
			<?php 
			echo $_SESSION['kit_info']->item_info->item_number.', '.$_SESSION['kit_info']->item_info->name; ?>
		</div>
	</div>
	
	<?php
		include('../wrightetmathon/application/views/partial/show_exit.php');
	?>
	
	<br>

	<div id="required_fields_message">
		<?php 
			echo $this->lang->line('common_fields_required_message');
		 ?>
	</div>
	
	<fieldset class="fieldset">
	
		<?php include('../wrightetmathon/application/views/partial/show_messages.php');?>
		
		<!-- show the kit structure -->
		<fieldset style="margin-top:5px; border:1px solid #0A6184; border-radius:8px; box-shadow:0 0 15px #0A6184">
			
			<table class="table_center">
				<caption style="text-align:center;font-size:20px;font-weight:bold;color:#161FDA"><?php echo $this->lang->line('common_manage').' '.$this->lang->line('items_kit_structure'); ?></caption>
				<thead>
					<tr>
						<th style="text-align:left;"><?php echo $this->lang->line('common_delete_short'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option_type'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option_qty'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('common_space'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($_SESSION['kit_info']->kit_structure as $row)
					{
					?>
					<tr>
						<td style="text-align:left;">	<?php echo anchor	(	'items/kit_structure_delete/'.$_SESSION['kit_info']->item_info->item_id.'/'.$row['kit_reference'].'/'.$row['kit_option'], 
																				'['.$this->lang->line('common_delete_short').']');
														?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo anchor	(	'items/kit_detail/'.$row['kit_reference'].'/'.$row['kit_option'].'/'.$row['kit_option_type'], 
																												$row['kit_option']);
																						?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	switch ($row['kit_option_type'])
																								{
																									case 'O':
																										echo $this->lang->line('items_kit_option_type_O');
																										break;
																									case 'F':
																										echo $this->lang->line('items_kit_option_type_F');
																										break;
																								}
																						?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $row['kit_option_qty'] ?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo $this->lang->line('common_space'); ?></td>
					</tr>
					<?php
					}
					?>
					<tr>
						<?php echo form_open('items/kit_structure_add/'.$_SESSION['kit_info']->item_info->item_id.'/'.$_SESSION['kit_info']->item_info->kit_reference); ?>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $this->lang->line('common_space'); ?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo form_input	(array	(
																														'name'	=>	'new1_kit_option',
																														'id'	=>	'new1_kit_option',
																														'value'	=>	$_SESSION['kit_info']->kit_option,
																														'style'	=>	'text-align:center'
																														)
																												);
																						?></td>

						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo form_dropdown		(
																														'new1_kit_option_type', 
																														$_SESSION['kit_info']->option_type_pick_list, 
																														$_SESSION['kit_info']->kit_option_type,
																														'style="font-size:18px"'
																														);
																						?></td>
																												
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo form_input	(array	(
																														'name'	=>	'new1_kit_option_qty',
																														'id'	=>	'new1_kit_option_qty',
																														'value'	=>	$_SESSION['kit_info']->kit_option_qty,
																														'style'	=>	'text-align:right',
																														'size'	=>	'3'
																														)
																												);
																						?></td>
																						
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	$form_submit	=	array	(
																														'name'		=>	'add_kit_structure',
																														'id'		=>	'add_kit_structure',
																														'value'		=>	$this->lang->line('common_submit'),
																														'class'		=>	'submit_button float_right'
																														);
																								echo form_submit($form_submit);
																						?></td>
					</tr>
				</tbody>
			</table>				
		</fieldset>
	</fieldset>
</dialog>
