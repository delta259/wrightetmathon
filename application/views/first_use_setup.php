<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" rev="stylesheet" href="<?php echo base_url();?>css/login.css"/>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title> <?php echo $this->lang->line('login_title').' - '.$this->lang->line('login_first_use_setup'); ?></title> 
<script src="<?php echo base_url();?>js/jquery-1.2.6.min.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
<script type="text/javascript">
$(document).ready(function()
{
	$("#login_form input:first").focus();
});
</script>
</head>


<body>
<!--Modified HBW V1-->
<h1> <?php echo $this->lang->line('login_title').' - '.$this->config->item('application_version').' - '.$this->lang->line('login_first_use_setup'); ?></h1>

<fieldset class="fieldset">
	
	<?php 
		// show messages
			echo $_SESSION['transaction_info']->message;
	?>
		<?php
			// when clicked use the controller, selecting method save and passing it the item ID. 
			echo form_open('login/first_use_setup');
			?>
				<table class="table_center">			
					<tbody>
						<td>
							<table class="table_center">
								<tr>
									<td align="center"><?php echo form_label($this->lang->line('branches_branch_code'), 'branch_code', array('class'=>'required')); ?></td>
									<td align="center"><?php echo form_label($this->lang->line('branches_branch_description'), 'branch_description', array('class'=>'required')); ?></td>
									<td align="center"><?php echo form_label($this->lang->line('branches_branch_type'), 'branch_type', array('class'=>'required')); ?></td>
								</tr>
								
								<tr>							
									<td align="center"><?php echo form_input	(	array	(
																	'name'	=>	'branch_code',
																	'id'	=>	'branch_code',
																	'style'	=>	'text-align:center; font-size:16px;',
																	'size'	=>	5,
																	'value'	=>	$_SESSION['transaction_info']->branch_code
																			));?>
									</td>
									<td align="center"><?php echo form_input	(	array	(
																	'name'	=>	'branch_description',
																	'id'	=>	'branch_description',
																	'style'	=>	'text-align:center; font-size:16px;',
																	'size'	=>	60,
																	'value'	=>	$_SESSION['transaction_info']->branch_description
																			));?>
									</td>
									<td align="center"><?php echo form_dropdown	(
																	'branch_type', 
																	$_SESSION['branch_type_pick_list'], 
																	$_SESSION['transaction_info']->branch_type,
																	'style="font-size:18px"'
																	); ?>
									</td>
								</tr>
								
								<tr>
									<td align="center"><?php echo form_label($this->lang->line('login_password'), 'password', array('class'=>'required')); ?></td>
									<td align="center"><?php echo form_label($this->lang->line('login_password_repeat'), 'password_repeat', array('class'=>'required')); ?></td>
								</tr>
								
								<tr>							
									<td align="center"><?php echo form_password	(	array	(
																	'name'	=>	'password',
																	'id'	=>	'password',
																	'style'	=>	'text-align:center; font-size:16px;',
																	'size'	=>	20,
																	'value'	=>	NULL
																			));?>
									</td>
									<td align="center"><?php echo form_password	(	array	(
																	'name'	=>	'password_repeat',
																	'id'	=>	'password_repeat',
																	'style'	=>	'text-align:center; font-size:16px;',
																	'size'	=>	20,
																	'value'	=>	NULL
																			));?>
									</td>
								</tr>
									
							</table>
						</td>
					</tbody>
				</table>
		
		
<?php
				echo form_submit					(	array	(
																'name'	=>	'submit',
																'id'	=>	'submit',
																'value'	=>	$this->lang->line('common_submit'),
																'class'	=>	'customer_submit_button'
																)
													);
			?>

<?php echo form_close(); ?>
</fieldset>
</body>
</html>
