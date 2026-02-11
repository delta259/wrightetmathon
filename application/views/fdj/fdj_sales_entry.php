<?php
	// output header
	$this->load->view("partial/header");

	// get userdata data
	$values					=	array();
	$values					=	$this->session->all_userdata();

	// output messages
	switch ($values['success_or_failure'])
	{
		case	"S":
			echo 	"<div class='success_message'>".$values['message']."</div>";
			break;
		case	"F":
			echo 	"<div class='error_message'>".$values['message']."</div>";
			break;
		default:
			echo 	' ';
	}
?>

<!-- spacers -->
<br>
<br>

<!-- Test for data entry or confirmation
<?php
	if ($values['confirm'] != 'Y')
	{
	?>
		<!--show the inut fields
		<!--one € -->
		<div class="fdj_1">
			<?php 	echo '1€';
					echo 	form_open('fdjs/calculate');
					echo	form_input	(array	(
												'name'	=>	'one_euro_qty',
												'id'	=>	'one_euro_qty',
												'value'	=>	$values['one'],
												'style'	=>	'text-align:center;font-size:30px;font-weight:bold',
												'size'	=>	'6'
												)
										);
			?>
		</div>

		<!-- two € -->
		<div class="fdj_2">
			<?php 	echo '2€';
					echo form_input	(array	(
											'name'	=>	'two_euro_qty',
											'id'	=>	'two_euro_qty',
											'value'	=>	$values['two'],
											'style'	=>	'text-align:center;font-size:30px;font-weight:bold',
											'size'	=>	'6'
											)
									);
			?>									
																						
		</div>

		<!-- three € -->
		<div class="fdj_3">
			<?php 	echo '3€';
					echo form_input	(array	(
											'name'	=>	'three_euro_qty',
											'id'	=>	'three_euro_qty',
											'value'	=>	$values['three'],
											'style'	=>	'text-align:center;font-size:30px;font-weight:bold',
											'size'	=>	'6'
											)
									);
			?>									
																						
		</div>

		<!-- five € -->
		<div class="fdj_5">
			<?php 	echo '5€';
					echo form_input	(array	(
											'name'	=>	'five_euro_qty',
											'id'	=>	'five_euro_qty',
											'value'	=>	$values['five'],
											'style'	=>	'text-align:center;font-size:30px;font-weight:bold',
											'size'	=>	'6'
											)
									);
			?>									
																						
		</div>

		<!-- ten € -->
		<div class="fdj_10">
			<?php 	echo '10€';
					echo form_input	(array	(
											'name'	=>	'ten_euro_qty',
											'id'	=>	'ten_euro_qty',
											'value'	=>	$values['ten'],
											'style'	=>	'text-align:center;font-size:30px;font-weight:bold',
											'size'	=>	'6'
											)
									);
			?>									
																						
		</div>	

		<!-- submit the form -->
		<div>
			<?php 	
				$form_submit	=	array	(
											'name'	=>	'submit',
											'id'	=>	'submit',
											'value'	=>	$this->lang->line('common_submit'),
											'class'	=>	'fdj_go'
											);
				echo form_submit($form_submit);
			?>
		</div>

		<!-- Close the form -->
		<?php //echo form_close();?>
		</form>
	<?php
	}
	else
	{
	?>
		<!-- show confirmation screen -->
		<div class="fdj_1">
			<?php 	echo '1€';
					echo br();
					echo $values['one'];
			?>
		</div>

		<!-- two € -->
		<div class="fdj_2">
			<?php 	echo '2€';
					echo br();
					echo $values['two'];
			?>																											
		</div>

		<!-- three € -->
		<div class="fdj_3">
			<?php 	echo '3€';
					echo br();
					echo $values['three'];
			?>																										
		</div>

		<!-- five € -->
		<div class="fdj_5">
			<?php 	echo '5€';
					echo br();
					echo $values['five'];
			?>																			
		</div>

		<!-- ten € -->
		<div class="fdj_10">
			<?php 	echo '10€';
					echo br();
					echo $values['ten'];	
			?>																		
		</div>
		
		<!-- show total -->
		<div class="fdj_total">
			<?php 	echo 'Total = ';
					echo $values['total'];
			?>																	
		</div>

		<!-- submit the form -->
		<div>
			<?php
				echo br();
				echo anchor('fdjs/confirm', '['.$this->lang->line('fdj_confirm').']', array('class' => 'fdj_confirm'));
			?>
		</div>
	<?php
	}
?>

		<ul>
			<br/>
			<li style="display:inline; list-style:none; margin:150px;"><a href="<?php echo site_url('fdjs/cancel');?>"><?php echo $this->lang->line('common_delete'); ?></a></li>
			<li style="display:inline; list-style:none; margin:150px;"><a href="<?php echo site_url('reports/fdj_reporting');?>"><?php echo $this->lang->line('fdj_reporting'); ?></a></li>
			<li style="display:inline; list-style:none; margin:150px;"><a href="<?php echo site_url('home/index');?>"><?php echo $this->lang->line('common_home'); ?></a></li>
			<br/>
			<br/>
		</ul>

<?php
	// show the footer
	$this->load->view("partial/footer");
?>
