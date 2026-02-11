<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="

    width: 845px;

">





    <!-- Header fenetre -->
    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
		<?php
        echo $this->lang->line('reports_report_input').' '.$_SESSION['transaction_info']->specific_input_name;
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
		include('../wrightetmathon/application/views/partial/show_messages.php');?>
	
<!-- Output date selection -->

	<?php
	// when clicked use the controller, items, selecting method save and passing it the item ID
	echo form_open($_SESSION['controller_name'].'/specific_report/');
	?>
                <fieldset>
	
	<!-- load table -->
	<table class="table_center" width="100%" style="border-spacing: 3px;
    border-collapse: separate;">
		<tbody>
			<tr>
				<td><?php echo form_label($this->lang->line('reports_date_range'), 'report_date_range_label', array('class'=>'required wide')); ?></td>
			</tr>
			
			<tr>				
				<td>
					<table>			
						<tbody>
							<tr>
								<td colspan=2><?php echo form_label($this->lang->line('reports_start')); ?></td>
							</tr>
							<tr>
								<td><?php echo form_dropdown	(
																'start_day', 
																$_SESSION['transaction_info']->days_pick_list,
																$_SESSION['transaction_info']->selected_day,
																'style="font-size:15px" class="colorobligatoire"'
																);?>
								</td>
								<td><?php echo form_dropdown	(
																'start_month', 
																$_SESSION['transaction_info']->months_pick_list,
																$_SESSION['transaction_info']->selected_month,
																'style="font-size:15px;    margin-left: 10px;" class="colorobligatoire"'
																);?>
								</td>
								<td><?php echo form_dropdown	(
																'start_year', 
																$_SESSION['transaction_info']->years_pick_list,
																$_SESSION['transaction_info']->selected_year,
																'style="font-size:15px;    margin-left: 10px;" class="colorobligatoire"'
																);?>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
	
				<td>
					<table>			
						<tbody>
							<tr>
								<td colspan=2><?php echo form_label($this->lang->line('reports_end')); ?></td>
							</tr>
							<tr>
								<td><?php echo form_dropdown	(
																'end_day', 
																$_SESSION['transaction_info']->days_pick_list,
																$_SESSION['transaction_info']->selected_day,
																'style="font-size:15px" class="colorobligatoire"'
																);?>
								</td>
								<td><?php echo form_dropdown	(
																'end_month', 
																$_SESSION['transaction_info']->months_pick_list,
																$_SESSION['transaction_info']->selected_month,
																'style="font-size:15px; margin-left: 10px;" class="colorobligatoire"'
																);?>
								</td>
								<td><?php echo form_dropdown	(
																'end_year', 
																$_SESSION['transaction_info']->years_pick_list,
																$_SESSION['transaction_info']->selected_year,
																'style="font-size:15px;    margin-left: 10px;" class="colorobligatoire"'
																);?>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			
			<tr>
				<td style="visibility:hidden;"><?php echo '.'; ?></td>
			</tr>
			
			<tr>
				<td colspan=2><?php echo form_label($_SESSION['transaction_info']->specific_input_name, 'specific_input_name_label', array('class'=>'required wide')); ?></td>
			</tr>
			
			<tr>				
				<td colspan=2 class="zone_champ_saisie"><?php echo form_dropdown	(
												'specific_input_data', 
												$_SESSION['transaction_info']->specific_pick_list,
												$_SESSION['transaction_info']->selected_specific,
												'style="font-size:15px" class="colorobligatoire"'
												);?>
                    <a class="btaide" title="<?php echo $_SESSION['transaction_info']->specific_input_name;?>"></a>
                </td>
				</td>
			</tr>
			
			<tr>
				<td style="visibility:hidden;"><?php echo '.'; ?></td>
			</tr>
			
			<tr>
				<td><?php echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class'=>'required wide')); ?></td>
				<td><?php echo form_label($this->lang->line('reports_export_to_excel'), 'export_excel', array('class'=>'required wide')); ?></td>
			</tr>
			
			<tr>				
				<td><?php echo form_dropdown	(
												'transaction_subtype', 
												$_SESSION['transaction_info']->options_pick_list,
												$_SESSION['transaction_info']->selected_option,
												'style="font-size:15px" class="colorobligatoire"'
												);?>
                    <a class="btaide" title="<?php echo $this->lang->line('reports_sale_type');?>"></a>

                </td>
				<td><?php echo form_dropdown	(
												'export_excel', 
												$_SESSION['G']->oneorzero_pick_list, 
												$_SESSION['selected_oneorzero'],
												'style="font-size:15px"  class="colorobligatoire"'
												);?>
                    <a class="btaide" title="<?php echo $this->lang->line('reports_export_to_excel');?>"></a>

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
															'class'	=>	'btsubmit sablier'
															)
												);
			?>
            </div>
			<?php
			echo form_close();
			?>
	</div>
</div>
</dialog>

<!-- div for spinner -->
<div id="spinner" class="spinnerrapport" style="display:none;">
    <!--  <img id="img-spinner" src="<?php /*echo base_url();*/?>images/M&Wloader.gif" alt="Loading"/>-->

    <div id="floatingCirclesG">
        <div class="f_circleG" id="frotateG_01"></div>
        <div class="f_circleG" id="frotateG_02"></div>
        <div class="f_circleG" id="frotateG_03"></div>
        <div class="f_circleG" id="frotateG_04"></div>
        <div class="f_circleG" id="frotateG_05"></div>
        <div class="f_circleG" id="frotateG_06"></div>
        <div class="f_circleG" id="frotateG_07"></div>
        <div class="f_circleG" id="frotateG_08"></div>
    </div>
</div>

<!-- script for spinner -->
<script type="text/javascript">
    $(document).ready(function()
    {
        $('.sablier').click(function()
        {
            $('#spinner').show();
        });
    });
</script>