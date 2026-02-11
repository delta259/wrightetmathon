<?php $this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale modal-md">



    <!-- Header fenetre -->
    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
		<?php echo $_SESSION['$title'].' '.$this->lang->line('modules_'.$_SESSION['controller_name']); ?>
	</span>

        <?php
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>


    </div>


    <!-- Contenu de la fenetre-->
    <div class="fenetre-content">



        <div class="centrepage">


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


                    <div class="blocformfond creationimmediate">
                        <div class="message_erreur">
                            <?php
                            include('../wrightetmathon/application/views/partial/show_messages.php');
                            ?>
                        </div>

                        <fieldset class="fieldset">



                            <table class="table_center">


                                <tr>
                                    <td align="left" class=" zone_champ_saisie "><?php echo form_input	(	array	(
                                            'name'	=>	'country_name',
                                            'id'	=>	'code',
                                            'size'	=>	15,
                                            'title'=> $this->lang->line('countries_country_name'),
                                            'placeholder'=>$this->lang->line('countries_country_name'),
                                            'value'	=>	$_SESSION['transaction_info']->country_name,
                                            'class' => 'colorobligatoire'
                                        ));?>
                                        <a class="btaide"  title="<?php echo $this->lang->line('countries_country_name') ; ?>"></a>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="left" class=" zone_champ_saisie "><?php echo form_input	(	array	(
                                            'name'	=>	'country_description',
                                            'id'	=>	'country_description',
                                            'size'	=>	30,
                                            'class' => 'colorobligatoire',
                                            'title'=>$this->lang->line('countries_country_description'),
                                            'placeholder'=> $this->lang->line('countries_country_description'),
                                            'value'	=>	$_SESSION['transaction_info']->country_description
                                        ));?>
                                        <a class="btaide"  id="" title="<?php echo $this->lang->line('countries_country_description')  ; ?>"></a>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="left" class=" zone_champ_saisie "><?php echo form_input	(	array	(
                                            'name'	=>	'country_display_order',
                                            'id'	=>	'country_display_order',
                                            'size'	=>	15,
                                            'class' => 'colorobligatoire',
                                            'title'=> $this->lang->line('countries_country_display_order'),
                                            'placeholder'=>$this->lang->line('countries_country_display_order'),
                                            'value'	=>	$_SESSION['transaction_info']->country_display_order
                                        ));?>
                                        <a class="btaide"  title="<?php echo $this->lang->line('countries_country_display_order'); ?>"></a>
                                    </td>


                                </tr>
                            </table>



                        </fieldset>

                        <div id="required_fields_message" class="obligatoire">
                            <a class="btobligatoire" id="" title="<?php $this->lang->line('common_fields_required_message')?>"></a>
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
                                /*'disabled'=>'disabled',*/
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
            ?></div>
    </div>


</div>
