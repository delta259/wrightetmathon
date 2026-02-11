<?php
	// output header
	$this->load->view("partial/header");
?>

 <div class="body_cadre_gris">


<div id="title_bar">
	<!-- Title -->
	<div id="title" class="float_left">
		<?php echo $this->lang->line('customers_merge'); ?>
	</div>
</div>

<?php
	// get flash data
	$success_or_failure		=	$this->session->flashdata('success_or_failure');
	$message				=	$this->session->flashdata('message');
	$merge_from_client		=	$this->session->flashdata('merge_from_client');
	$merge_to_client		=	$this->session->flashdata('merge_to_client');

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

<fieldset style="margin-top:5px; border:1px solid #0A6184; border-radius:8px; box-shadow:0 0 15px #0A6184">

	<table id="register">
		<thead>
			<tr>
				<th style="text-align:center;"><?php echo $this->lang->line('customers_merge_from_client'); ?></th>
				<th style="text-align:center;"><?php echo ''; ?></th>
				<th style="text-align:center;"><?php echo $this->lang->line('customers_merge_to_client'); ?></th>
			</tr>
		</thead>
		<br>
		<tbody id="cart_contents">
			<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php 	echo form_open('customers/merge_do');
																					echo form_input	(array	(
																											'name'	=>	'merge_from_client',
																											'id'	=>	'merge_from_client',
																											'style'	=>	'text-align:center',
																											'size'	=>	'10',
																											'value'	=>	$merge_from_client
																											)
																									);
																			?>
			<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo '=>'; ?>
			<td style="text-align:center;font-weight:bold;color:#161FDA">	<?php	echo form_input	(array	(
																											'name'	=>	'merge_to_client',
																											'id'	=>	'merge_to_client',
																											'style'	=>	'text-align:center',
																											'size'	=>	'10',
																											'value'	=>	$merge_to_client
																											)
																									);
																		?>
            </td>	</tbody>
	</table>

	<?php 	$form_submit	=	array	(
										'name'		=>	'submit',
										'id'		=>	'submit',
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

			echo anchor('customer/index', ''.$this->lang->line('common_logout').' '.$this->lang->line('customers_merge').'');

		echo br(2);
	?>
</div>
</div>


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

<?php
	
	$this->load->view("partial/pre_footer");
?>
<?php
	
	$this->load->view("partial/footer");
?>
