<script type="text/javascript">
$(document).ready	(
						function()
						{
							// this allows search
							$("#search").autocomplete			(
																	'<?php echo site_url("sales/item_search"); ?>',
																	{
																		minChars:2,
																		max:100,
																		selectOnly: true,
																		delay:10,
																		formatItem: function(row) 
																		{
																			return row[1];
																		}
																	}
																);
							
							$("#search").result					(
																	function(event, data, formatted)
																	{
																		$("#search_form").submit();
																	}
																);
						}
					);
</script>

<dialog open class="dialog">
	<div id="title_bar">
		<div id="page_title" class="float_left">
			<?php
				switch ($_SESSION['kit_info']->kit_option_type)
				{
					case 'O':
						$option_type_text								=	$this->lang->line('items_kit_option_type_O');
						break;
					case 'F':
						$option_type_text								=	$this->lang->line('items_kit_option_type_F');
						break;
				}
				echo $this->lang->line('items_kit_detail').' => '.$_SESSION['kit_info']->kit_reference.' => '.$_SESSION['kit_info']->kit_option.' => '.$option_type_text;
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
			
			<table>
				<caption style="text-align:center;font-size:20px;font-weight:bold;color:#161FDA"><?php echo $this->lang->line('common_manage').' '.$this->lang->line('items_kit_detail'); ?></caption>
				<thead>
					<tr>
						<th style="text-align:left;"><?php echo $this->lang->line('common_delete_short'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('items_item_number'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('items_name'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('common_space'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($_SESSION['kit_info']->kit_detail as $row)
					{
					?>
					<tr>
						<td style="text-align:left;">	<?php echo anchor	(	'items/kit_detail_delete/'.'/'.$row['kit_reference'].'/'.$row['kit_option'].'/'.$row['item_number'].'/'.$_SESSION['kit_info']->kit_option_type, 
																				'['.$this->lang->line('common_delete_short').']');
														?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $row['item_number'] ?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $row['name'] ?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo $this->lang->line('common_space'); ?></td>
					</tr>
					<?php
					}
					?>
					<tr>
						<?php echo form_open('items/kit_detail_add/'.$_SESSION['kit_info']->kit_reference.'/'.$_SESSION['kit_info']->kit_option.'/'.$_SESSION['kit_info']->kit_option_type); ?>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $this->lang->line('common_space'); ?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo form_input	(array	(
																														'name'	=>	'new1_item_number',
																														'id'	=>	'new1_item_number',
																														'value'	=>	$_SESSION['kit_info']->kit_item_number,
																														'style'	=>	'text-align:center'
																														)
																												);
																						?></td>
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $this->lang->line('common_space'); ?></td>															
						<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	$form_submit	=	array	(
																														'name'		=>	'add_kit_detail',
																														'id'		=>	'add_kit_detail',
																														'value'		=>	$this->lang->line('common_submit'),
																														'class'		=>	'btsubmit'
																														);
																								echo form_submit($form_submit);
																								echo form_close();
																						?></td>
					</tr>
				</tbody>
			</table>				
		</fieldset>
	</fieldset>
</dialog>
