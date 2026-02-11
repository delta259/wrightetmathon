<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="

    width: 1005px;
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
                    <table style="border-collapse: separate;     border-spacing: 3px; width:100%">
                        <tbody>



                        <tr>
                            <td colspan="3" class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'module_name',
                                    'id'	=>	'module_name',
                                    'style'	=>	'font-size:15px;',
                                    'size'	=>	20,
                                    'class'	=>	'colorobligatoire',
                                    'placeholder'=>$this->lang->line('modules_module_name'),
                                    'value'	=>	$_SESSION['transaction_info']->module_name
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_name');?>"></a>

                            </td>

                            <td  colspan="2" class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'sort',
                                    'id'	=>	'sort',
                                    'style'	=>	' font-size:15px;',
                                    'size'	=>	10,
                                    'class'	=>	'colorobligatoire',
                                    'placeholder'=>$this->lang->line('modules_module_sort'),
                                    'value'	=>	$_SESSION['transaction_info']->sort
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_sort');?>"></a>

                            </td>

                        </tr>
                        <tr> <td colspan="3" ><?php echo form_label($this->lang->line('modules_module_name_lang_key'), 'name_lang_key', array('class'=>'required')); ?></td>

                            <td colspan="2" class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'name_lang_key',
                                    'id'	=>	'name_lang_key',
                                    'style'	=>	' font-size:15px;',
                                    'size'	=>	25,
                                    'class'	=>	'colorobligatoire',
                                    'value'	=>	$_SESSION['transaction_info']->name_lang_key
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_name_lang_key');?>"></a>

                            </td></tr>
                        <tr> <td colspan="3"><?php echo form_label($this->lang->line('modules_module_desc_lang_key'), 'desc_lang_key', array('class'=>'required')); ?></td>

                            <td colspan="2" class="zone_champ_saisie"><?php echo form_input	(	array	(
                                    'name'	=>	'desc_lang_key',
                                    'id'	=>	'desc_lang_key',
                                    'style'	=>	'font-size:15px;',
                                    'size'	=>	25,
                                    'class'	=>	'colorobligatoire',
                                    'value'	=>	$_SESSION['transaction_info']->desc_lang_key
                                ));?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_desc_lang_key');?>"></a>

                            </td></tr>
                        <tr>

                            <td colspan="2"><?php echo form_label($this->lang->line('modules_module_show_in_header'), 'show_in_header', array('class'=>'required')); ?></td>

                            <td colspan="1" class="zone_champ_obligatoire"><?php echo form_dropdown	(
                                    'show_in_header',
                                    $_SESSION['G']->YorN_pick_list,
                                    $_SESSION['transaction_info']->show_in_header,
                                    'style="  margin-left: 15px;font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_show_in_header');?>"></a>

                            </td>


                            <td colspan="1"><?php echo form_label($this->lang->line('modules_module_user_menu'), 'user_menu', array('class'=>'required')); ?></td>

                            <td colspan="1" class="zone_champ_saisie"><?php echo form_dropdown	(
                                    'user_menu',
                                    $_SESSION['G']->YorN_pick_list,
                                    $_SESSION['transaction_info']->user_menu,
                                    'style="margin-left: 15px;font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_user_menu');?>"></a>

                            </td>


                        </tr>


                        <tr>
                            <td colspan="2"><?php echo form_label($this->lang->line('modules_module_admin_menu'), 'module_clone_button',  array('class'=>'required')); ?></td>

                            <td colspan="1" class="zone_champ_saisie"><?php echo form_dropdown	(
                                    'admin_menu',
                                    $_SESSION['G']->YorN_pick_list,
                                    $_SESSION['transaction_info']->admin_menu,
                                    'style="margin-left: 15px; font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_admin_menu');?>"></a>

                            </td>

                            <td colspan="1"><?php echo form_label($this->lang->line('modules_module_sys_admin_menu'), 'module_merge_button', array('class'=>'required')); ?></td>

                            <td colspan="1" class="zone_champ_saisie"><?php echo form_dropdown	(
                                    'sys_admin_menu',
                                    $_SESSION['G']->YorN_pick_list,
                                    $_SESSION['transaction_info']->sys_admin_menu,
                                    'style=" margin-left: 15px;font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_sys_admin_menu');?>"></a>
                            </td>
                        </tr>

                        <tr></br></tr>

                        <tr>
                            <td align="center" colspan="5" ></br><?php echo $this->lang->line('modules_show_buttons').$this->lang->line('common_question'); ?></br></td>
                        </tr>



                        <tr>
                            <td class="zone_champ_saisie" align="center"><?php echo form_label($this->lang->line('modules_module_new_button'), 'module_new_button', array('class'=>'required')); ?>

                                <?php echo form_dropdown	(
                                    'show_new_button',
                                    $_SESSION['G']->oneorzero_pick_list,
                                    $_SESSION['transaction_info']->show_new_button,
                                    'style=" font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_new_button');?>"></a>
                            </td>
                            <td class="zone_champ_obligatoire" align="center"><?php echo form_label($this->lang->line('modules_module_exit_button'), 'module_exit_button', array('class'=>'required')); ?>

                                <?php echo form_dropdown	(
                                    'show_exit_button',
                                    $_SESSION['G']->oneorzero_pick_list,
                                    $_SESSION['transaction_info']->show_exit_button,
                                    'style=" font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_exit_button');?>"></a>
                            </td>

                            <td class="zone_champ_saisie" align="center"><?php echo form_label($this->lang->line('modules_module_clone_button'), 'module_clone_button',  array('class'=>'required')); ?>

                                <?php echo form_dropdown	(
                                    'show_clone_button',
                                    $_SESSION['G']->oneorzero_pick_list,
                                    $_SESSION['transaction_info']->show_clone_button,
                                    'style=" font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_clone_button') ;?>"></a>
                            </td>
                            <td class="zone_champ_saisie" align="center"><?php echo form_label($this->lang->line('modules_module_merge_button'), 'module_merge_button', array('class'=>'required')); ?>

                                <?php echo form_dropdown	(
                                    'show_merge_button',
                                    $_SESSION['G']->oneorzero_pick_list,
                                    $_SESSION['transaction_info']->show_merge_button,
                                    'style=" font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_merge_button') ;?>"></a>
                            </td>
                            <td class="zone_champ_saisie" align="center"><?php echo form_label($this->lang->line('modules_module_undel_button'), 'module_undel_button', array('class'=>'required')); ?>

                                <?php echo form_dropdown	(
                                    'show_undel_button',
                                    $_SESSION['G']->oneorzero_pick_list,
                                    $_SESSION['transaction_info']->show_undel_button,
                                    'style=" font-size:15px" class="colorobligatoire"'
                                );?>
                                <a class="btaide" title="<?php echo $this->lang->line('modules_module_undel_button') ;?>"></a>
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
