<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="


    width:1125px;
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

        <br>

    </div>


    <!---CONTENT-->
    <div class="fenetre-content">



        <div class="centrepage">



            <div class="blocformfond creationimmediate">

        <?php
        include('../wrightetmathon/application/views/partial/show_messages.php');
        ?>
                <fieldset>
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
            <table class="table_center" style="width:100%; border-spacing: 5px; border-collapse: separate;">
                <tbody>

                    <table class="table_center" style="width:100%; border-spacing: 5px; border-collapse: separate;">


                        <tr>                            <td><?php echo form_label($this->lang->line('branches_branch_code'), 'branch_code', array('class'=>'required')); ?></td>

                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'branch_code',
                                    'id'	=>	'branch_code',
                                    'style'	=>	'font-size:16px;',
                                    'size'	=>	5,
                                    'class'=> 'colorobligatoire',
                                    'value'	=>	$_SESSION['transaction_info']->branch_code
                                ));?>
                            </td>
                            <td><?php echo form_label($this->lang->line('branches_branch_description'), 'branch_description', array('class'=>'required')); ?></td>

                            <td class="zone_champ_saisie">
                                <?php echo form_input	(	array	(
                                    'name'	=>	'branch_description',
                                    'id'	=>	'branch_description',
                                    'style'	=>	'font-size:16px;',
                                    'size'	=>	20,
                                    'class'=> 'colorobligatoire',
                                    'value'	=>	$_SESSION['transaction_info']->branch_description
                                ));?>
                            </td>
                            <td><?php echo form_label($this->lang->line('branches_branch_type'), 'branch_type', array('class'=>'required')); ?></td>

                            <td class="zone_champ_saisie"><?php echo form_dropdown	(
                                    'branch_type',
                                    $_SESSION['branch_type_pick_list'],
                                    $_SESSION['transaction_info']->branch_type,
                                    'style="font-size:18px" class="colorobligatoire"'
                                ); ?>
                            </td>
                        </tr>

                        <?php
                        // set attribtes to required if a new branch is being added
                        $password_label_attributes = ($_SESSION['new'] ?? 0) == 1 ? array('class'=>'required'):array();
                        ?>
                        <?php
                        // set attribtes to required if a new branch or if allows_check is Y
                        if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->branch_allows_check == 'Y')
                        {
                            $connection_label_attributes	=	array('class'=>'required');
                        }
                        else
                        {
                            $connection_label_attributes	=	NULL;
                        }
                        ?>

                        <tr>
                            <td><?php echo form_label($this->lang->line('branches_branch_allows_check'), 'branch_allows_check', $connection_label_attributes); ?></td>
                            <td><?php echo form_label($this->lang->line('branches_branch_ip'), 'branch_ip', $connection_label_attributes); ?></td>
                            <td><?php echo form_label($this->lang->line('branches_branch_user'), 'branch_user', $connection_label_attributes); ?></td>
                            <td><?php echo form_label($this->lang->line('branches_branch_password'), 'branch_password', $connection_label_attributes); ?></td>
                            <td><?php echo form_label($this->lang->line('branches_branch_database'), 'branch_database', $connection_label_attributes); ?></td>
                        </tr>

                        <tr>
                            <td class="zone_champ_saisie"><?php echo form_dropdown	(
                                    'branch_allows_check',
                                    $_SESSION['G']->YorN_pick_list,
                                    $_SESSION['transaction_info']->branch_allows_check,
                                    'style="font-size:18px"'
                                ); ?>
                            </td>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'branch_ip',
                                    'id'	=>	'branch_ip',
                                    'style'	=>	'font-size:16px;',
                                    'size'	=>	20,
                                    'value'	=>	$_SESSION['transaction_info']->branch_ip
                                ));?>
                            </td>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'branch_user',
                                    'id'	=>	'branch_user',
                                    'style'	=>	'font-size:16px;',
                                    'size'	=>	10,
                                    'value'	=>	$_SESSION['transaction_info']->branch_user
                                ));?>
                            </td>
                            <td class="zone_champ_saisie"><?php echo form_password	(	array	(
                                    'name'	=>	'branch_password',
                                    'id'	=>	'branch_password',
                                    'style'	=>	'font-size:16px;',
                                    'size'	=>	15,
                                    'value'	=>	$_SESSION['transaction_info']->branch_password
                                ));?>
                            </td>
                            <td class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'branch_database',
                                    'id'	=>	'branch_database',
                                    'style'	=>	'font-size:16px;',
                                    'size'	=>	15,
                                    'value'	=>	$_SESSION['transaction_info']->branch_database
                                ));?>
                            </td>
                        </tr>
                    </table>

                </tbody>
            </table>

    </fieldset>
                <div id="required_fields_message" class="obligatoire">
                    <a class="btobligatoire" title="<?php $this->lang->line('common_fields_required_message')?>"></a>
                    <?php echo $this->lang->line('common_fields_required_message'); ?>
                </div>
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
</dialog>
