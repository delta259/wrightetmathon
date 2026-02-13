
<?php $this->load->view("partial/header_popup"); ?>





<div open class="fenetre modale" style="
    position: absolute;
   left: 50%;
    right: 50%;
    top: 0%;
    transform: translate(-50%,50%);
    width: 633px;
    z-index: 101;">

    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
		<?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).' '.$_SESSION['$title']; ?>
	</span>

        <?php
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>


    </div>





    <div class="fenetre-content">



        <div class="centrepage">
            <div class="blocformfond creationimmediate">

	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');?>

		<?php

			// when clicked use the controller, items, selecting method save and passing it the item ID.
			echo form_open($_SESSION['controller_name'].'/merge_do/');
		?>
	<?php
		switch ($_SESSION['merge_ok'])
		{
			case 	1:
	?>
            <fieldset>
					<table>
						<tbody>
							<tr>
								<td class="required"><?php echo $this->lang->line('customers_merge_from_client'); ?></td>
								<td><?php echo ' | '; ?></td>
								<td class="required" align="right"><?php echo $this->lang->line('customers_merge_to_client'); ?></td>
							</tr>

							<tr>
								<td><?php echo $_SESSION['transaction_from']->person_id; ?></td>
								<td><?php echo ' | '; ?></td>
								<td align="right"><?php echo $_SESSION['transaction_to']->person_id; ?></td>
							</tr>

							<tr>
								<td><?php echo $_SESSION['transaction_from']->last_name; ?></td>
								<td><?php echo ' | '; ?></td>
								<td align="right"><?php echo $_SESSION['transaction_to']->last_name; ?></td>
							</tr>

							<tr>
								<td><?php echo $_SESSION['transaction_from']->first_name; ?></td>
								<td><?php echo ' | '; ?></td>
								<td align="right"><?php echo $_SESSION['transaction_to']->first_name; ?></td>
							</tr>
						</tbody>
					</table>
            </fieldset>

            </div>

            <div class="txt_milieu">

                <?php
                $target	=	'target="_self"';
                echo anchor			(
                    'common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>',
                    $target
                );
                ?>
		<?php
			$_SESSION['merge_ok']	=	2;
			echo form_submit					(	array	(
															'name'	=>	'submit',
															'id'	=>	'submit',
															'value'	=>	$this->lang->line('common_confirm'),
															'class'	=>	'btsubmit'
															)
												);
                ?>	</div>
		<?php
			break;

			default:
		?>
            <fieldset>
					<table style="">
						<tbody>
							<!--<tr>
								<td><?php /*echo form_label($this->lang->line('items_merge_from_item'), 'merge_from_id',array('class'=>'required wide')); ?></td>
								<td><?php echo form_label($this->lang->line('items_merge_to_item'), 'merge_to_id',array('class'=>'required wide')); */?></td>
							</tr>-->

							<tr class="table-row">
								<td><?php echo form_input(array	(
																'name'			=>	'merge_from_id',
																'id'			=>	'merge_from_id',
																'style'			=>	'font-size:15px;',
																'placeholder'	=>	'Client ID 1 ',
																'value'			=>	$_SESSION['transaction_info']->merge_from_id
																));?>

								</td>
								<td><?php echo form_input(array	(
																'name'			=>	'merge_to_id',
																'id'			=>	'merge_to_id',
																'style'			=>	'font-size:15px;',
																'placeholder'	=>	'Client ID 2',
																'value'			=>	$_SESSION['transaction_info']->merge_to_id
																));?>

								</td>
							</tr>
						</tbody>
					</table>
            </fieldset>
            <div id="required_fields_message" class="obligatoire">
                <?php echo $this->lang->line('common_fields_required_message'); ?>
            </div>

        </div>
            <div class="txt_milieu">

                <?php
                $target	=	'target="_self"';
                echo anchor			(
                    'common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>',
                    $target
                );
                ?>
		<?php
			echo form_submit					(	array	(
															'name'	=>	'submit',
															'id'	=>	'submit',
															'value'	=>	$this->lang->line('common_submit'),
															'class'	=>	'btsubmit'
															)
												);
                ?></div>
		<?php
			break;
		}
		?>
		<?php
		echo form_close();
		?>

</div>
</div>
