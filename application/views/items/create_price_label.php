<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<div id="required_fields_message"><?php echo $this->lang->line('items_enter_label_data'); ?></div>
<ul id="error_message_box"></ul>
<?php


echo form_open('items/create_label/'.$item_info->item_id, array('id'=>'item_form'));
?>
<fieldset id="item_basic_info">
<legend><?php echo $this->lang->line("items_basic_information"); ?></legend>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_item_number').':', 'name',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'item_number',
		'id'=>'item_number',
		'value'=>$item_info->item_number)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php 

	echo form_label($this->lang->line('items_name').':', 'name',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'name',
		'id'=>'name',
		'value'=>$item_info->name)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_category').':', 'category',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'category',
		'id'=>'category',
		'value'=>$item_info->category)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('sales_price').':', 'sales_price',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'sales_price',
		'size'=>'8',
		'id'=>'sales_price',
		'value'=>$sales_price)
	);?>
	</div>
</div>


<?php
echo form_submit	(array	(
								'name'=>'submit',
								'id'=>'submit',
								'value'=>$this->lang->line('common_submit'),
								'class'=>'btsubmit float_right'
							)
					);
?>
</fieldset>
<?php
	echo form_close();
?>



<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{	
	$('#item_form').validate({
		/*document.write(form);*/
		submitHandler:function(form)
		{
			form.submit();
		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules:
		{
			sales_price:
			{
				required:true,
				number:true
			}
		},
		messages:
		{
			sales_price:
			{
				required:"<?php echo $this->lang->line('items_sales_price_required'); ?>",
				number:"<?php echo $this->lang->line('items_sales_price_number'); ?>"
			}
		}
	});
});
</script>
