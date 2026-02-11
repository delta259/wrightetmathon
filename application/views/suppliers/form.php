<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="width:1000px;">

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
                // show delete button - only if item exists already
                if (($_SESSION['new'] ?? 0) != 1)
                {
                    ?>


                    <div class="txt_droite">
                        <?php
                        // show delete button - only if item not undeleting
                        if (($_SESSION['undel'] ?? 0) != 1)
                        {
                            echo form_open			(
                                $_SESSION['controller_name'].'/delete/'.$_SESSION['transaction_info']->customer_id,
                                array('id'=>'customer_delete_form')
                            );

                            echo form_submit		(	array	(
                                    'name'		=>	'delete',
                                    'id'		=>	'delete',
                                    'value'		=>	$this->lang->line('suppliers_delete'),
                                    'class'		=>	'btmodification'
                                )
                            );
                            echo form_close();
                        }
                        ?>
                    </div>
                    <div class="txt_gauche">
                        <?php
                        // show undel button - only if undeleting
                        if (($_SESSION['undel'] ?? 0) == 1)
                        {
                            echo form_open			(
                                $_SESSION['controller_name'].'/undelete/'.$_SESSION['transaction_info']->customer_id,
                                array('id'=>'customer_delete_form')
                            );

                            echo form_submit		(	array	(
                                    'name'	=>	'undelete',
                                    'id'	=>	'undelete',
                                    'value'	=>	$this->lang->line('suppliers_undelete'),
                                    'class'	=>	'btmodification'
                                )
                            );
                            echo form_close();
                        }
                        ?>
                    </div>

                    <?php
                }?>
                <?php
                // show enter button - only if item not undeleting
                if (($_SESSION['undel'] ?? 0) != 1)
                {
                // when clicked use the controller, customers, selecting method save and passing it the item ID.
                echo form_open($_SESSION['controller_name'].'/save/', array('id'=>'item_form'));
                ?>
                <fieldset>
                    <table class="table_center"  style="border-spacing: 5px; border-collapse: separate;">
                        <tbody>
                        <?php
                        include('../wrightetmathon/application/views/people/form_basic_info.php');
                        ?>



                        <tr>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'company_name',
                                    'id'	=>	'company_name',
                                    'style'	=>	'font-size:15px;',
                                    'class'=>'colorobligatoire',
                                    'placeholder'=>$this->lang->line('suppliers_company_name'),
                                    'value'	=>	$_SESSION['transaction_info']->company_name
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('suppliers_company_name') ;?>"></a>
                            </td>
                        </tr>




                        <tr>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'account_number',
                                    'id'	=>	'account_number',
                                    'placeholder'=>$this->lang->line('suppliers_account_number'),
                                    'style'	=>	'font-size:15px;',
                                    'class'=>'colorobligatoire',
                                    'value'	=>	$_SESSION['transaction_info']->account_number
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('suppliers_account_number') ;?>"></a>
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
    </div></dialog>

<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/my_calendar2.js"></script>