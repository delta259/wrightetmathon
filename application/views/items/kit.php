<?php

	// output header
	$this->load->view("partial/header");

	// get the number format -->
	$pieces =array();
	$pieces = explode("/", $this->config->item('numberformat'));

?>
<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>


<?php
// messages

if($success_or_failure == 'S')
	{
		echo "<div class='success_message'>".$message."</div>";
	}


if($success_or_failure == 'F')
	{
		echo "<div class='error_message'>".$message."</div>";
	}
?>

<fieldset id="item_basic_info">
	<legend><?php echo $this->lang->line("items_basic_information"); ?></legend>

	<table align="center" border="2" bgcolor="#CCCCCC">
		
		<div class="md-form-group">
			
			<!-- Output Item number -->
			<tr>
				<td>	
					<?php echo form_label($this->lang->line('items_item_number').':', 'item_number',array('class'=>'wide')); ?>
				</td>
				
				<td>
					<?php 
						$inumber = 	array	(
											'name'		=>	'item_number',
											'id'		=>	'item_number',
											'size'		=>	60,
											'value'		=>	$item_info->item_number,
											'style'		=>	'border:none',
											'readonly'	=>	'readonly'
											);
						echo form_input($inumber)
					?>
				</td>
			</tr>

			<!-- Output Item description -->
			<tr>
				<td>	
					<?php echo form_label($this->lang->line('items_name').':', 'name',array('class'=>'wide')); ?>
				</td>
				
				<td>	
					<?php 
						$iname = 	array		(
											'name'		=>	'name',
											'id'		=>	'name',
											'size'		=>	60,
											'value'		=>	$item_info->name,
											'style'		=>	'border:none',
											'readonly'	=>	'readonly'
											);
						echo form_input($iname);
					?>
				</td>
			</tr>

			<!-- Output Item category -->
			<tr>
				<td>	
					<?php echo form_label($this->lang->line('items_category').':', 'category',array('class'=>'wide')); ?>
				</td>
				
				<td>	
					<?php 
						$cat = 	array		(
											'name'		=>	'category',
											'id'		=>	'category',
											'size'		=>	60,
											'value'		=>	$item_info->category,
											'style'		=>	'border:none',
											'readonly'	=>	'readonly'
											);
						echo form_input($cat);
					?>
				</td>
			</tr>

			<!-- Output current qty on file -->
			<tr>
				<td>
					<?php echo form_label($this->lang->line('items_current_quantity').':', 'quantity',array('class'=>'wide')); ?>
				</td>
				
				<td>
					<?php 
						$qty = 	array		(
											'name'		=>	'quantity',
											'id'		=>	'quantity',
											'size'		=>	60,
											'value'		=>	$item_info->quantity,
											'style'		=>	'border:none',
											'readonly'	=>	'readonly'
											);
						echo form_input($qty);
					?>
				</td>
			</tr>
			
			<!-- Output kit reference -->
			<tr>
				<td>
					<?php echo form_label($this->lang->line('items_kit').':', 'kit_reference',array('class'=>'wide')); ?>
				</td>
				
				<td>
					<?php 
						$kitref = 	array		(
											'name'		=>	'kit_reference',
											'id'		=>	'kit_reference',
											'size'		=>	60,
											'value'		=>	$item_info->kit_reference,
											'style'		=>	'border:none',
											'readonly'	=>	'readonly'
											);
						echo form_input($kitref);
					?>
				</td>
			</tr>
		</div>	
	</table>
</fieldset>
	
<!-- show the kit structure -->
<fieldset style="margin-top:5px; border:1px solid #0A6184; border-radius:8px; box-shadow:0 0 15px #0A6184">
	<legend>
		<b>
			<?php
				echo $this->lang->line('common_manage').' '.$this->lang->line('items_kit_structure');
			?>
		</b>
	</legend>
	
	<table id="register">
		<thead>
			<tr>
				<th style="text-align:left;"><?php echo $this->lang->line('common_delete_short'); ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option'); ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option_type'); ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option_qty'); ?></th>
			</tr>
		</thead>
		<br>
		<tbody id="cart_contents">
			<?php
			foreach ($kit_structure as $row)
			{
			?>
			<tr>
				<td style="text-align:left;"><?php echo anchor('items/delete_kit_structure/'.$item_info->item_id.'/'.$row['kit_reference'].'/'.$row['kit_option'].'/'.$origin, '['.$this->lang->line('common_delete_short').']');?></td>
				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $row['kit_option'] ?></td>
				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $row['kit_option_type'] ?></td>
				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $row['kit_option_qty'] ?></td>
			</tr>
			<?php
			}
			?>
		</tbody>
	</table>
</fieldset>

<!-- allow data entry -->
<fieldset style="margin-top:5px; border:1px solid #0A6184; border-radius:8px; box-shadow:0 0 15px #0A6184">
	<legend>
		<b>
			<?php
				echo $this->lang->line('common_add').' '.$this->lang->line('items_kit_structure');
			?>
		</b>
	</legend>
	<table id="register">
		<thead>
			<tr>
				<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option'); ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option_type'); ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option_qty'); ?></th>
			</tr>
		</thead>
		<br>
		<tbody id="cart_contents">
			
			<!-- now allow data entry -->
			<tr>
				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo form_open('items/kit_button/'.$item_info->item_id.'/'.$item_info->kit_reference.'/'.$origin);
																						echo form_input	(array	(
																												'name'	=>	'new1_kit_option',
																												'id'	=>	'new1_kit_option',
																												'style'	=>	'text-align:center',
																												'size'	=>	'10'
																												)
																										);
																				?>

				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo form_input	(array	(
																												'name'	=>	'new1_kit_option_type',
																												'id'	=>	'new1_kit_option_type',
																												'style'	=>	'text-align:center',
																												'size'	=>	'1'
																												)
																										);
																				?>
																										
				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo form_input	(array	(
																												'name'	=>	'new1_kit_option_qty',
																												'id'	=>	'new1_kit_option_qty',
																												'style'	=>	'text-align:right',
																												'size'	=>	'6'
																												)
																										);
																				?></td>
			</tr>
		</tbody>
	</table>
			
	<?php 	$form_submit	=	array	(
										'name'		=>	'add_kit_structure',
										'id'		=>	'add_kit_structure',
										'value'		=>	$this->lang->line('common_submit'),
										'class'		=>	'btsubmit'
										);
			echo form_submit($form_submit);
	?>
				
</fieldset>
			
<!-- show the kit detail -->
<fieldset style="margin-top:5px; border:1px solid #0A6184; border-radius:8px; box-shadow:0 0 15px #0A6184">
	<legend>
		<b>
			<?php
				echo $this->lang->line('common_manage').' '.$this->lang->line('items_kit_detail');
			?>
		</b>
	</legend>
	
	<table id="register">
		<thead>
			<tr>
				<th style="text-align:left;"><?php echo $this->lang->line('common_delete_short'); ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option'); ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('items_item_number'); ?></th>
								<th style="text-align:center;"><?php echo $this->lang->line('items_name'); ?></th>
			</tr>
		</thead>
		<br>
		<tbody id="cart_contents">
			<?php
			foreach ($kit_detail as $rowdet)
			{
			?>
			<tr>
				<td style="text-align:left;"><?php echo anchor('items/delete_kit_detail/'.$item_info->item_id.'/'.$rowdet['kit_reference'].'/'.$rowdet['kit_option'].'/'.$rowdet['item_number'].'/'.$origin, '['.$this->lang->line('common_delete_short').']');?></td>
				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $rowdet['kit_option'] ?></td>
				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $rowdet['item_number'] ?></td>
				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo $rowdet['name'] ?></td>
			</tr>
			<?php
			}
			?>
		</tbody>
	</table>
</fieldset>

<!-- allow data entry -->
<fieldset style="margin-top:5px; border:1px solid #0A6184; border-radius:8px; box-shadow:0 0 15px #0A6184">
	<legend>
		<b>
			<?php
				echo $this->lang->line('common_add').' '.$this->lang->line('items_kit_detail');
			?>
		</b>
	</legend>
	<table id="register">
		<thead>
			<tr>
				<th style="text-align:left;"><?php echo $this->lang->line('common_add'); ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('items_kit_option'); ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('items_item_number'); ?></th>
			</tr>
		</thead>
		<br>
		<tbody id="cart_contents">
			
			<!-- now allow data entry -->
			<tr>
				<td style="text-align:left;"><?php echo $this->lang->line('common_add');?></td>
				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo form_open('items/kit_button/'.$item_info->item_id.'/'.$item_info->kit_structure.'/'.$origin);
																						echo form_input	(array	(
																												'name'	=>	'new2_kit_option',
																												'id'	=>	'new2_kit_option',
																												'style'	=>	'text-align:center',
																												'size'	=>	'10'
																												)
																										);
																				?>

				<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo form_input	(array	(
																												'name'	=>	'new2_item_number',
																												'id'	=>	'new2_item_number',
																												'style'	=>	'text-align:center',
																												'size'	=>	'10'
																												)
																										);
																				?></td>
			</tr>
		</tbody>
	</table>
			
	<?php 	$form_submit	=	array	(
										'name'		=>	'add_kit_detail',
										'id'		=>	'add_kit_detail',
										'value'		=>	$this->lang->line('common_submit'),
										'class'		=>	'btsubmit'
										);
			echo form_submit($form_submit);
	?>
				
</fieldset>


<div style="float:right">
	<?php
		// show the exit button	
		echo br(1);

		switch ($origin) 
		{
			case "DL":
				echo anchor('reports/dluo_qty_error', '['.$this->lang->line('common_logout').' '.$this->lang->line('items_kit').']');
				break;
			case "DD":
				echo anchor('reports/dluo_past_date', '['.$this->lang->line('common_logout').' '.$this->lang->line('items_kit').']');
				break;
			case "DR":
				echo anchor('receivings/index', '['.$this->lang->line('common_logout').' '.$this->lang->line('items_kit').']');
				break;
			default:
				echo anchor('items/index', '['.$this->lang->line('common_logout').' '.$this->lang->line('items_kit').']');
		}
		
		echo br(2);
	?>
</div>

<?php
	// show the footer
	$this->load->view("partial/footer");
?>


<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{		
	$('#item_form').validate(
		{
		submitHandler:function(form)
								{
									form.submit();
								},
								
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules: 
		{
			newquantity:
			{
				required:true,
				number:true
			}
   		},
		messages: 
		{
			newquantity:
			{
				required:"<?php echo $this->lang->line('items_quantity_required'); ?>",
				number:"<?php echo $this->lang->line('items_quantity_number'); ?>"
			}
		}
		}
	);
	
	$('#dluo_form').validate(
		{
		submitHandler:function(form)
								{
									form.submit();
								},
								
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules: 
		{
			new_dluo_qty:
			{
				required:true,
				number:true
			}
   		},
		messages: 
		{
			new_dluo_qty:
			{
				required:"<?php echo $this->lang->line('items_quantity_required'); ?>",
				number:"<?php echo $this->lang->line('items_quantity_number'); ?>"
			}
		}
		}
	);
});
</script>
