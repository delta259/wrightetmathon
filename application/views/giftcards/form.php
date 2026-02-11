

<div id="required_fields_message" style="font-size:20px;font-weight:bold">
	<?php echo $this->lang->line('common_fields_required_message'); ?>
	<br>
	<br>
	<?php echo $this->lang->line('giftcards_strict'); ?>
</div>

<ul id="error_message_box"></ul>
<?php
echo form_open('giftcards/save/'.$giftcard_info->giftcard_id,array('id'=>'giftcard_form'));
?>
<fieldset id="giftcard_basic_info" style="padding: 5px;">
<legend><?php echo $this->lang->line("giftcards_basic_information"); ?></legend>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('giftcards_giftcard_number').':', 'name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'giftcard_number',
		'id'=>'giftcard_number',
		'value'=>$giftcard_info->giftcard_number)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('giftcards_card_value').':', 'value',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'value',
		'id'=>'value',
		'value'=>$giftcard_info->value)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('giftcards_sale_date').' : '.$this->lang->line('common_date_format'), 'sale_date',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'sale_date',
		'id'=>'sale_date',
		'value'=>$giftcard_info->sale_date)
	);?>
	</div>
</div>

<?php
echo form_submit(array(
	'name'=>'submit',
	'id'=>'submit',
	'value'=>$this->lang->line('common_submit'),
	'class'=>'submit_button float_right')
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
	$('#giftcard_form').validate({
		submitHandler:function(form)
		{
			form.submit();
		},
		
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules:
		{
			giftcard_number:
			{
				required:true,
				number:true
			},
			sale_date:
			{
				required:true	
			},
			value:
			{
				required:true,
				number:true
			}
   		},
		messages:
		{
			giftcard_number:
			{
				required:"<?php echo $this->lang->line('giftcards_number_required'); ?>",
				number:"<?php echo $this->lang->line('giftcards_number'); ?>"
			},
			value:
			{
				required:"<?php echo $this->lang->line('giftcards_value_required'); ?>",
				number:"<?php echo $this->lang->line('giftcards_value'); ?>"
			}
			sale_date:
			{
				required:"<?php echo $this->lang->line('giftcards_sale_date_required'); ?>",
			}
		}
	});
});
</script>
