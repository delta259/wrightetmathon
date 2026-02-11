<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="
    width: 650px;
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
				<table class="table_center" style="    width: 100%;
    border-collapse: separate;
    border-spacing: 8px;">
					<tbody>



									<tr>
										<td colspan="2" class="zone_champ_saisie"><?php echo form_input	(	array	(
																		'name'	=>	'pricelist_name',
																		'id'	=>	'pricelist_name',
																		'style'	=>	' font-size:15px;',
																		'size'	=>	8,
                                                'class '=>'colorobligatoire',
                                                'placeholder'=>$this->lang->line('pricelists_pricelist_name'),
																		'value'	=>	$_SESSION['transaction_info']->pricelist_name
																				));?>
                                            <a class="btaide" title="<?php $this->lang->line('pricelists_pricelist_name');?>"></a>

                                        </td>
										<td colspan="2" class="zone_champ_saisie"><?php echo form_input	(	array	(
																		'name'	=>	'pricelist_description',
																		'id'	=>	'pricelist_description',
																		'style'	=>	' font-size:15px;',
																		'size'	=>	25,
                                                'class '=>'colorobligatoire',
																		'placeholder'=>$this->lang->line('pricelists_pricelist_description'),
																		'value'	=>	$_SESSION['transaction_info']->pricelist_description
																				));?>
                                            <a class="btaide" title="<?php echo $this->lang->line('pricelists_pricelist_description');?>"></a>

                                        </td>
									</tr>



									<tr>												<td align="center"><?php echo form_label($this->lang->line('pricelists_pricelist_currency'), 'pricelist_currency', array('class'=>'required')); ?></td>

                                        <td  class="zone_champ_saisie"><?php echo form_dropdown	(
																		'pricelist_currency',
																		$_SESSION['currency_pick_list'],
																		$_SESSION['transaction_info']->pricelist_currency,
																		'style="font-size:15px" class="colorobligatoire"'
																		);?>
                                            <a class="btaide" title="<?php echo$this->lang->line('pricelists_pricelist_currency');?>"></a>

                                        </td>
                                        <td ><?php echo form_label($this->lang->line('pricelists_pricelist_default'), 'pricelist_currency', array('class'=>'required')); ?></td>

                                        <td class="zone_champ_saisie"><?php echo form_dropdown	(
																		'pricelist_default',
																		$_SESSION['G']->YorN_pick_list,
																		$_SESSION['transaction_info']->pricelist_default,
																		'style=" font-size:15px" class="colorobligatoire"'
																		); ?>
                                            <a class="btaide" title="<?php echo $this->lang->line('pricelists_pricelist_default');?>"></a>

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
