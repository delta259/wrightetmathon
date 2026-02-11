<!-- -->
<!-- called from = controllers->receivings->edit
<!-- -->

<?php $this->load->view("partial/header"); ?>
<div id="edit_sale_wrapper">
	
	<h1>	
			<?php 	echo $this->lang->line('reports_edit_transaction');
					echo ' => ';
					echo $transaction_info['receiving_id'];
					echo ' => ';
					$lang_line = 'reports_'.$transaction_info['mode'];
					echo $this->lang->line($lang_line);
					echo ' => ';
					echo anchor	(
								'receivings/receipt/'.$transaction_info['receiving_id'], 
								$code
								);
			?>
	</h1>
	
	<?php
			echo form_open("receivings/save_trans/".$transaction_info['receiving_id'], array('id'=>'transaction_edit_form')); 
	?>

	<fieldset id="config_info">
	<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
	<ul id="error_message_box"></ul>
	<legend><?php echo $this->lang->line($lang_line); ?></legend>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('suppliers_supplier').':', 'supplier'); ?>
		<div class='form_field'>
			<?php echo form_dropdown('supplier_id', $suppliers, $transaction_info['supplier_id'], 'id="supplier_id"');?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('sales_employee').':', 'employee'); ?>
		<div class='form_field'>
			<?php echo form_dropdown('employee_id', $employees, $transaction_info['employee_id'], 'id="employee_id"');?>
		</div>
	</div>
	
	<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('sales_comment').':', 'comment'); ?>
		<div class='form_field'>
			<?php echo form_textarea(array('name'=>'comment','value'=>$transaction_info['comment'],'rows'=>'4','cols'=>'60', 'id'=>'comment'));?>
		</div>
	</div>
	
	<?php
			echo form_submit	(array	(
										'name'=>'submit',
										'id'=>'submit',
										'value'=>$this->lang->line('common_submit'),
										'class'=>'submit_button float_left'
										)
								);
	?>
	</form>
	<?php echo form_open("receivings/delete/".$transaction_info['receiving_id'],array('id'=>'transaction_delete_form')); ?>
		<?php
			echo form_submit	(array	(
										'name'=>'submit',
										'id'=>'submit',
										'value'=>$this->lang->line('reports_delete_entire_transaction'),
										'class'=>'delete_button float_right'
										)
								);
		?>
		
	</form>
</fieldset>
</div>
<?php
echo form_close();
?>
<div id="feedback_bar"></div>


<script type="text/javascript" language="javascript">
$(document).ready(function()
{	
	$('#date').datePicker({startDate: '01/01/1970'});
	$("#transaction_delete_form").submit(function()
	{
		if (!confirm('<?php echo $this->lang->line("reports_delete_confirmation"); ?>'))
		{
			return false;
		}
	});
	
	
	$('#transaction_edit_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				if(response.success)
				{
					set_feedback(response.message,'success_message',false);
				}
				else
				{
					set_feedback(response.message,'error_message',true);
				}
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules: 
		{
   		},
		messages: 
		{
		}
	});
});
</script>
<?php $this->load->view("partial/footer"); ?>
