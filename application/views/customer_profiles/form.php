<?php $this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale" style="      position: absolute;
    left: 50%;
    right: 50%;
    top: 0%;
    transform: translate(-50%,50%);
    width: 645px;
    z-index: 101;">

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
                    <table style="border-collapse: separate; border-spacing:5px;">
                        <tbody>

                        <tr>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'profile_name',
                                    'id'	=>	'profile_name',
                                    'style'	=>	' font-size:15px;',
                                    'size'	=>	13,
                                    'class'	=>	'colorobligatoire',
                                    'placeholder'=>$this->lang->line('customer_profiles_profile_name'),
                                    'value'	=>	$_SESSION['transaction_info']->profile_name
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('customer_profiles_profile_name');?>"></a>

                            </td>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'profile_description',
                                    'id'	=>	'profile_description',
                                    'style'	=>	' font-size:15px;',
                                    'size'	=>	25,
                                    'class'	=>	'colorobligatoire',
                                    'placeholder'=>$this->lang->line('customer_profiles_profile_description'),
                                    'value'	=>	$_SESSION['transaction_info']->profile_description
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('customer_profiles_profile_description');?>"></a>

                            </td>
                        </tr>

                        <tr>



                            <td class="zone_champ_saisie">

                                <?php echo form_input	(	array	(
                                    'name'	=>	'profile_discount',
                                    'id'	=>	'profile_discount',
                                    'style'	=>	' text-align:right; font-size:15px;',
                                    'size'	=>	5,
                                    'class'	=>	'colorobligatoire',
                                    'placeholder'=>$this->lang->line('customer_profiles_profile_discount').$this->lang->line('common_percent'),
                                    'value'	=>	$_SESSION['transaction_info']->profile_discount
                                ));?>
                                <?php echo $this->lang->line('common_percent')?>
                                <a class="btaide" title="<?php echo $this->lang->line('customer_profiles_profile_discount').$this->lang->line('common_percent');?>"></a>

                            </td>

                            <td class="zone_champ_saisie"><?php echo form_label($this->lang->line('customer_profiles_profile_fidelity').$this->lang->line('common_question'), 'profile_fidelity', array('class'=>'required')); ?>


                                <?php echo form_dropdown	(
                                    'profile_fidelity',
                                    $_SESSION['G']->YorN_pick_list,
                                    $_SESSION['transaction_info']->profile_fidelity,
                                    'style=" font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('customer_profiles_profile_fidelity');?>"></a>

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
