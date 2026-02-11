<?php
// output header
$this->load->view("partial/header");
?>


<div class="body_cadre_gris">

    <div id="title_bar">
        <!-- Title -->
        <div id="title" class="float_left">
         <h3>   <?php echo $this->lang->line('recvs_reprint'); ?></h3>
        </div>
    </div>

    <?php
    // get userdata data
    $values					=	array();
    $values					=	$this->session->all_userdata();

    // messages
    if($values['success_or_failure'] == 'S')
    {
        echo "<div class='success_message'>".$values['message']."</div>";
    }


    if($values['success_or_failure'] == 'F')
    {
        echo "<div class='error_message'>".$values['message']."</div>";
    }
    ?>
   <!-- <div style="float:right">
        <?php
        // show the exit button
        /*echo br(1);

        echo anchor('items/index', ''.$this->lang->line('common_logout').' '.$this->lang->line('recvs_reprint').'');

        echo br(2);*/
        ?>
    </div>-->


    <div id="" style=" width:775px;">
        <div class="blocformfond creationimmediate">
            <fieldset >

                <table id="register">



                    <tbody id="cart_contents">
                    <td style="text-align:center;"><h4><?php echo $this->lang->line('recvs_enter_code'); ?></h4></td>
                    <td class="zone_champ_saisie" >	<?php 	echo form_open('receivings/reprint_check');
                        echo form_input	(array	(
                                'name'			=>	'reprint_code',
                                'id'			=>	'reprint_code',
                                'style'			=>	'text-align:left; margin-left:40px;',
                                'size'			=>	'15',
                                'autofocus'		=>	'autofocus',
                                'value'			=>	$values['reprint_code']
                            )
                        );
                        ?>
                    </td></tbody>
                </table>
                <br>

            </fieldset>
            <div class="txt_milieu">

                <?php
                $target	=	'target="_self"';
                echo anchor			(
                    'common_controller/common_exit/','<div class="btretour btlien ">'.$this->lang->line('common_reset').'</div>',
                    $target
                );
                ?>
                <?php 	$form_submit	=	array	(
                    'name'		=>	'reprint',
                    'id'		=>	'reprint',
                    'value'		=>	'RE-'.$this->lang->line('common_print'),
                    'class'		=>	'btsubmit'
                );
                echo form_submit($form_submit);
                ?>
            </div>
        </div>
    </div>




    <?php
    // show the footer
    $this->load->view("partial/pre_footer");
    $this->load->view("partial/footer");
    ?>


    <script type='text/javascript'>

        //validation and submit handling
        $(document).ready(function()
        {
            $('#item_form').validate(
                {
                    submitHandler:function(form)
                    {
                        form.submit();
                    },

                    errorLabelContainer: "#error_message_box",
                    wrapper: "li",
                    rules:
                        {
                            newquantity:
                                {
                                    required:true,
                                    number:true
                                }
                        },
                    messages:
                        {
                            newquantity:
                                {
                                    required:"<?php echo $this->lang->line('items_quantity_required'); ?>",
                                    number:"<?php echo $this->lang->line('items_quantity_number'); ?>"
                                }
                        }
                }
            );

            $('#dluo_form').validate(
                {
                    submitHandler:function(form)
                    {
                        form.submit();
                    },

                    errorLabelContainer: "#error_message_box",
                    wrapper: "li",
                    rules:
                        {
                            new_dluo_qty:
                                {
                                    required:true,
                                    number:true
                                }
                        },
                    messages:
                        {
                            new_dluo_qty:
                                {
                                    required:"<?php echo $this->lang->line('items_quantity_required'); ?>",
                                    number:"<?php echo $this->lang->line('items_quantity_number'); ?>"
                                }
                        }
                }
            );
        });
    </script>
