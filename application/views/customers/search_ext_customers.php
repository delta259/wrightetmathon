<?php $this->load->view("partial/header_popup"); ?>
<dialog open class="fenetre modale cadre" style=" width: 1130px;">
    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
		<?php /*echo $this->lang->line('modules_'.'  '.$_SESSION['$title'].$_SESSION['controller_name']).'  '.$_SESSION['$title'];*/ ?>
        Recherche Client
	</span>
        <?php
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>
    </div>
    <div class="fenetre-content">
        <div class="centrepage">
            <div class="blocformfond creationimmediate">
	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');
	?>
<?php
	// show data entry but not if deleted
	if ($_SESSION['del'] != 1)
	{
?>
                <?php
                // show delete button - only if item exists already
                if ($_SESSION['new'] != 1)
                {
                    ?>

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
                                'value'	=>	$this->lang->line('customers_undelete'),
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
		if ($_SESSION['undel'] != 1)
		{
			// when clicked use the controller, customers, selecting method save and passing it the item ID.
			//echo form_open($_SESSION['controller_name'].'/search_ext_customers');
            echo form_open('customers/search_ext_customers');
            ?>
                <fieldset>
                    <table style="width:100%; border-collapse: separate; border-spacing: 5px;">
                        <tbody>
                        <tr>
    <td align="left" class="zone_champ_saisie "><?php echo form_input	(	array	(
            'name'		=>	'last_name',
            'id'		=>	'last_name',
            'style'		=>	'font-size:15px;',
            'size'		=>	30,
            'placeholder'=>$this->lang->line('common_last_name'),
            'value'		=>	$_SESSION['transaction_info']->last_name
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_last_name') ;?>"></a>
    </td>
    <td align="left" class="zone_champ_saisie "><?php echo form_input	(	array	(
            'name'		=>	'first_name',
            'id'		=>	'first_name',
            'style'		=>	'font-size:15px;',
            'size'		=>	30,
            'placeholder'=>$this->lang->line('common_first_name'),
            'value'		=>	$_SESSION['transaction_info']->first_name
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_first_name') ;?>"></a>
    </td>
    </tr>
    <tr>
    <td align="left" class="zone_champ_saisie "><?php 	echo form_input	(	array	(
            'name'		=>	'email',
            'id'		=>	'email',
            'style'		=>	'font-size:15px;',
            'size'		=>	30,
            'placeholder'=>$this->lang->line('common_email'),
            'value'		=>	$_SESSION['transaction_info']->email
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_email') ;?>"></a>
    </td>
    <td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
            'name'		=>	'phone_number',
            'id'		=>	'phone_number',
            'class'=>'colornormal',
            'style'		=>	'font-size:15px;',
            'placeholder'=>$this->lang->line('common_phone_number'),
            'size'		=>15,
            'value'		=>	$_SESSION['transaction_info']->phone_number
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_phone_number') ;?>"></a>
    </td>
    </tr>
    <tr>
    <td align="left" class="zone_champ_saisie"><?php echo form_dropdown(
                                    'branch_ipv4',
                                    $_SESSION['G']->branch_description_pick_list,
                                    '',
                                    'style="font-size:18px"'
                                ); ?>
        <a class="btaide" title="<?php echo '';?>"></a>
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
            </div>
            <div class="txt_milieu">

                <?php
                $target	=	'target="_self"';
                echo anchor			(
                    'common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>',
                    $target
                );
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
	}
	?>
        </div>
</div></dialog>