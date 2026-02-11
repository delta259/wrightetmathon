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

                <fieldset>

							<table class="table_center">

								<tr>
									<td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
																	'name'	=>	'timezone_name',
																	'id'	=>	'timezone_name',
																	'size'	=>	12,
                                            'class' => 'colorobligatoire',
                                            'placeholder'=> $this->lang->line('timezones_timezone_name'),
																	'value'	=>	$_SESSION['transaction_info']->timezone_name
																			));?>
                                        <a class="btaide"  id="" title="<?php echo $this->lang->line('timezones_timezone_name') ; ?>"></a>
									</td>
									<td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
																	'name'	=>	'timezone_description',
																	'id'	=>	'timezone_description',
																	'size'	=>	30,
																	'class' => 'colorobligatoire',
																	'placeholder'=>$this->lang->line('timezones_timezone_description'),
																	'value'	=>	$_SESSION['transaction_info']->timezone_description
																			));?>
                                        <a class="btaide" id="" title="<?php echo $this->lang->line('timezones_timezone_description'); ?>"></a>
									</td>

                                </tr>
                            </table>
                    <table class="table_center">
                                <tr>
									<td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
																	'name'	=>	'timezone_continent',
																	'id'	=>	'timezone_continent',
																	'size'	=>	10,
																	'class' => 'colorobligatoire',
																	'placeholder'=> $this->lang->line('timezones_timezone_continent'),
																	'value'	=>	$_SESSION['transaction_info']->timezone_continent
																			));?>
                                        <a class="btaide"  id="" title="<?php echo $this->lang->line('timezones_timezone_continent'); ?>"></a>
									</td>
									<td align="left"  class="zone_champ_saisie"><?php echo form_input	(	array	(
																	'name'	=>	'timezone_city',
																	'id'	=>	'timezone_city',
																	'size'	=>	10,
																	'class' => 'colorobligatoire',
																	'placeholder'=>$this->lang->line('timezones_timezone_city'),
																	'value'	=>	$_SESSION['transaction_info']->timezone_city
																			));?>
                                        <a class="btaide"  id="" title="<?php echo $this->lang->line('timezones_timezone_city'); ?>"></a>
									</td>
									<td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
																	'name'	=>	'timezone_offset',
																	'id'	=>	'timezone_offset',
																	'size'	=>	13,
																	'class' => 'colorobligatoire',
																	'placeholder'=>$this->lang->line('timezones_timezone_GMT_offset'),
																	'value'	=>	$_SESSION['transaction_info']->timezone_offset
																			));?>
                                        <a class="btaide"  id="" title="<?php echo $this->lang->line('timezones_timezone_GMT_offset') ;?>"></a>
									</td>
								</tr>
							</table>

    </fieldset>

                <div id="required_fields_message" class="obligatoire">
                    <a class="btobligatoire"  id="" title="<?php $this->lang->line('common_fields_required_message')?>"></a>
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
?></div>
    </div>
    </div>
