<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="
    width: 635px;
    ">
    <!-- Header fenetre -->
    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
		<?php
        include('../wrightetmathon/application/views/partial/show_title.php');
        ?>
	</span>

        <?php
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>


    </div>

    <!---CONTENT-->
    <div class="fenetre-content">



        <div class="centrepage">



            <div class="blocformfond creationimmediate">


	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');?>

		<?php

			// when clicked use the controller, items, selecting method save and passing it the item ID.
			echo form_open($_SESSION['controller_name'].'/view/-1');
		?>
<fieldset>
			<table class="table_center">
				<tbody>
					<tr>
					<!--	<td><?php /*echo form_label(, 'clone_from_id',array('class'=>'required wide')); ?></td>
						<td><?php echo form_label(, 'clone_to_id',array('class'=>'required wide'));*/ ?></td>-->
					</tr>

					<tr class="table-row">
						<td class="zone_champ_saisie"><?php echo form_input(array	(
														'name'		=>	'clone_from_id',
														'id'		=>	'clone_from_id',
														'style'		=>	'font-size:15px; margin-bottom:10px;',
                                                        'class'=>'colorobligatoire',
														'placeholder'=>$this->lang->line('items_clone_from_item'),
														'value'		=>	$_SESSION['transaction_info']->clone_from_id
														));?>
                            <a class="btaide" title="<?php echo $this->lang->line('items_clone_from_item');?>"></a>

                        </td>
						<td class="zone_champ_saisie"><?php echo form_input(array	(
														'name'		=>	'clone_to_id',
														'id'		=>	'clone_to_id',
														'style'		=>	'font-size:15px; margin-bottom:10px;',
                                                        'class'=>'colorobligatoire',
														'placeholder'=> $this->lang->line('items_clone_to_item'),
														'value'		=>	$_SESSION['transaction_info']->clone_to_id
														));?>
                            <a class="btaide" title="<?php echo $this->lang->line('items_clone_to_item');?>"></a>

                        </td>
					</tr>
				</tbody>
			</table>
</fieldset>
                <div id="required_fields_message" class="obligatoire">
                    <a class="btobligatoire" title="<?php $this->lang->line('common_fields_required_message')?>"></a>
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
			?>
            </div>
			<?php
			echo form_close();
			?>
        </div></div></dialog>
