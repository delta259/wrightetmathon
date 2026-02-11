<?php $this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale modal-lg">


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
                include('../wrightetmathon/application/views/partial/show_messages.php');
                ?>

                <?php
                // show data entry but not if deleted
                if (($_SESSION['del'] ?? 0) != 1)
                {
                ?>
                <?php
                // show enter button - only if item not undeleting
                if (($_SESSION['undel'] ?? 0) != 1)
                {
                // when clicked use the controller, selecting method save and passing it the item ID.
                echo form_open($_SESSION['controller_name'].'/save/', array('id'=>'item_form'));
                ?>
                <fieldset>
                    <table class="table_center">
                        <tbody>



                        <tr>
                            <td colspan="2" class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_code',
                                    'id'	=>	'warehouse_code',
                                    'size'	=>	10,
                                    'class'=>'colorobligatoire',
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_code'),
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_code
                                ));?>

                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_code') ;?>"></a>

                            </td>
                            <td colspan="2" class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_description',
                                    'id'	=>	'warehouse_description',
                                    'size'	=>	25,
                                    'class'=>'colorobligatoire',
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_description'),
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_description
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_description') ;?>"></a>
                            </td>
                        </tr>



                        <tr>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_row_start',
                                    'id'	=>	'warehouse_row_start',
                                    'size'	=>	10,
                                    'class'=>'colorobligatoire',
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_row_start'),
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_row_start
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_row_start') ;?>"></a>
                            </td>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_row_end',
                                    'id'	=>	'warehouse_row_end',
                                    'size'	=>	10,
                                    'class'=>'colorobligatoire',
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_row_end'),
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_row_end
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_row_end') ;?>"></a>
                            </td>

                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_section_start',
                                    'id'	=>	'warehouse_section_start',
                                    'class'=>'colorobligatoire',
                                    'size'	=>	10,
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_section_start'),
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_section_start
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_section_start') ;?>"></a>
                            </td>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_section_end',
                                    'id'	=>	'warehouse_section_end',
                                    'class'=>'colorobligatoire',
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_section_end'),
                                    'size'	=>	10,
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_section_end
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_section_end') ;?>"></a>
                            </td>
                        </tr>



                        <tr>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_shelf_start',
                                    'id'	=>	'warehouse_shelf_start',
                                    'class'=>'colorobligatoire',
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_shelf_start'),
                                    'size'	=>	10,
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_shelf_start
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_shelf_start') ;?>"></a>
                            </td>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_shelf_end',
                                    'id'	=>	'warehouse_shelf_end',
                                    'size'	=>	10,
                                    'class'=>'colorobligatoire',
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_shelf_end'),
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_shelf_end
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_shelf_end') ;?>"></a>
                            </td>

                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_bin_start',
                                    'id'	=>	'warehouse_bin_start',
                                    'class'=>'colorobligatoire',
                                    'size'	=>	10,
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_bin_start'),
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_shelf_start
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_bin_start');?>"></a>
                            </td>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'warehouse_bin_end',
                                    'id'	=>	'warehouse_bin_end',
                                    'class'=>'colorobligatoire',
                                    'size'	=>	10,
                                    'placeholder'=>$this->lang->line('warehouses_warehouse_bin_end'),
                                    'value'	=>	$_SESSION['transaction_info']->warehouse_shelf_end
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('warehouses_warehouse_bin_end') ;?>"></a>
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
            }
            ?>


            <?php
            }
            ?>
        </div>
