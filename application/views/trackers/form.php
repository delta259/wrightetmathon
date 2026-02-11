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
                        <td>
                            <table class="table_center">


                                <tr>
                                    <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                            'name'	=>	'tracker_subject',
                                            'id'	=>	'tracker_subject',
                                            'size'	=>	30,
                                            'class' => 'colorobligatoire',
                                            'placeholder'=>$this->lang->line('trackers_tracker_subject'),
                                            'value'	=>	$_SESSION['transaction_info']->tracker_subject
                                        ));?>
                                        <a class="btaide"  title="<?php echo $this->lang->line('trackers_tracker_subject') ;?>"></a>
                                    </td>
                                    <td><?php echo $this->lang->line('trackers_tracker_status'); ?></td>
                                    <td class="zone_champ_saisie"><?php echo form_dropdown	(
                                            'tracker_status',
                                            $_SESSION['G']->tracker_status_pick_list,
                                            $_SESSION['transaction_info']->tracker_status,
                                            'class="colorobligatoire"'
                                        );?>
                                        <a class="btaide"  title="<?php echo $this->lang->line('trackers_tracker_status') ;?>"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="zone_champ_saisie" colspan="3"><?php echo form_input	(	array	(
                                            'name'	=>	'tracker_commit_summary',
                                            'id'	=>	'tracker_commit_summary',
                                            'size'	=>	30,
                                            'class' => 'colorobligatoire',
                                            'placeholder'=>$this->lang->line('trackers_tracker_commit_summary'),
                                            'value'	=>	$_SESSION['transaction_info']->tracker_commit_summary
                                        ));?>
                                        <a class="btaide"   title="<?php echo $this->lang->line('trackers_tracker_commit_summary') ;?>"></a>
                                    </td>
                                </tr>



                                <tr>
                                    <td class="zone_champ_saisie" colspan="3" ><?php echo form_textarea	(	array	(
                                            'name'	=>	'tracker_description',
                                            'id'	=>	'tracker_description',
                                            'rows'	=>	4,
                                            'class' => 'colorobligatoire',
                                            'placeholder'=>$this->lang->line('trackers_tracker_description'),
                                            'cols'	=>	30,
                                            'value'	=>	$_SESSION['transaction_info']->tracker_description
                                        ));?>
                                        <a class="btaide" id="" title="<?php echo $this->lang->line('trackers_tracker_description') ;?>"></a></td>
                                </tr>
                            </table>
                        </td>
                        </tbody>
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
        </div></div></div>
