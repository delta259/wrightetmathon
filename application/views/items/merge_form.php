<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="
    width: 1000px;
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
			echo form_open($_SESSION['controller_name'].'/merge_do/');
		?>
	<?php
		switch ($_SESSION['merge_ok'])
		{
			case 	1:
	?>
            <fieldset>
					<table width="100%" style="border-spacing: 10px; border-collapse: separate;">
                        <thead>
                            <th class="txt_milieu"><?php echo $this->lang->line('items_merge_from_item'); ?></th>
                            <th><?php echo ' | '; ?></th>
                            <th class="txt_milieu"><?php echo $this->lang->line('items_merge_to_item'); ?></th>
                        </thead>
						<tbody>


							<tr>
								<td align="left"><?php echo $_SESSION['transaction_from']->item_number; ?></td>
								<td><?php echo ' | '; ?></td>
								<td align="left"><?php echo $_SESSION['transaction_to']->item_number; ?></td>
							</tr>

							<tr>
								<td><?php echo $_SESSION['transaction_from']->name; ?></td>
								<td><?php echo ' | '; ?></td>
								<td align="left"><?php echo $_SESSION['transaction_to']->name; ?></td>
							</tr>

							<tr>
								<td><?php echo $_SESSION['transaction_from']->category; ?></td>
								<td><?php echo ' | '; ?></td>
								<td align="left"><?php echo $_SESSION['transaction_to']->category; ?></td>
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
                ?>

                </div>
		<?php
			break;

			default:
		?>
                <fieldset>
					<table width="100%" >
						<tbody>
							<!--<tr>
								<td><?php /* echo form_label(, 'merge_from_id',array('class'=>'required wide')); ?></td>
								<td><?php echo form_label($, 'merge_to_id',array('class'=>'required wide')); */?></td>
							</tr>-->

							<tr class="table-row">
								<td class="zone_champ_saisie" align="center"><?php echo form_input(array	(
																'name'		=>	'merge_from_id',
																'id'		=>	'merge_from_id',
																'style'		=>	'font-size:15px; margin-bottom:10px;',
																'class'=>'colorobligatoire',
																'placeholder'=> $this->lang->line('items_merge_from_item'),
																'value'		=>	$_SESSION['transaction_info']->merge_from_id
																));?>
                                    <a class="btaide" title="<?php echo $this->lang->line('items_merge_from_item');?>"></a>
								</td>
								<td class="zone_champ_saisie" align="center"><?php echo form_input(array	(
																'name'		=>	'merge_to_id',
																'id'		=>	'merge_to_id',
																'placeholder'=> $this->lang->line('items_merge_to_item'),
																'style'		=>	'font-size:15px; margin-bottom:10px;',
                                        'class'=>'colorobligatoire',
																'value'		=>	$_SESSION['transaction_info']->merge_to_id
																));?>
                                    <a class="btaide" title="<?php echo $this->lang->line('items_merge_to_item');?>"></a>
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
			break;
		}
		?>
		<?php
		echo form_close();
		?>

</div>
