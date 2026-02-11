<?php $this->load->view("partial/header"); ?>

<!-- Setup the route information -->
<?php
	$route				=	$this->session->userdata('route');
	$redirect			=	$this->Common_routines->determine_route($route);
	$this				->	session->unset_userdata('route');
?>

<div id="edit_sale_wrapper">
	<h1>	
			<?php 	echo $this->lang->line('reports_edit_transaction');
					echo '  ';
					echo $transaction_info['sale_id'];
					echo '  ';
					$lang_line = 'reports_'.$transaction_info['mode'];
					echo $this->lang->line($lang_line);
					echo '  ';
					echo anchor	(
								'sales/receipt/'.$transaction_info['sale_id'], 
								$code.'-'.$transaction_info['sale_id']
								);
			?>
	</h1>
	
	<?php
		echo form_open("sales/save_trans/".$transaction_info['sale_id'],array('id'=>'sales_edit_form')); 
	?>
	
	<fieldset>
		<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
		<ul id="error_message_box"></ul>
		<legend><?php echo $this->lang->line($lang_line); ?></legend>
		<ul id="error_message_box"></ul>
		
		<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('sales_customer').':', 'customer'); ?>
			<div class='form_field'>
				<?php echo form_dropdown('customer_id', $customers, $transaction_info['customer_id'], 'id="customer_id"');?>
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
		
		<?php echo form_close(); ?>

	</fieldset>

	<div style="display:flex; gap:12px; justify-content:space-between; align-items:center; margin-top:20px; padding-top:16px; border-top:1px solid var(--border-color, #e2e8f0);">
		<div>
			<?php
			echo form_open("sales/delete/".$transaction_info['sale_id'], array('id' => 'sales_delete_form', 'style' => 'display:inline;'));
			echo form_submit(array(
				'name'  => 'submit',
				'id'    => 'submit_delete',
				'value' => $this->lang->line('sales_delete_entire_sale'),
				'class' => 'delete_button'
			));
			echo form_close();
			?>
		</div>
		<div style="display:flex; gap:12px; align-items:center;">
			<?php
			echo anchor($redirect->route_path, $this->lang->line('common_logout'), 'class="btretour"');
			echo '<input type="submit" form="sales_edit_form" name="submit" value="'.$this->lang->line('common_submit').'" class="btsubmit" />';
			?>
		</div>
	</div>
			
</div>
<div id="feedback_bar"></div>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{	
	$('#date').datePicker({startDate: '01/01/1970'});
	$("#sales_delete_form").submit(function()
	{
		if (!confirm('<?php echo $this->lang->line("sales_delete_confirmation"); ?>'))
		{
			return false;
		}
	});
	
	$('#sales_edit_form').validate({
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
