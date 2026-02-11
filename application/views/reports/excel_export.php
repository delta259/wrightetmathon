
<!-- output header -->
<?php $this->load->view("partial/header"); ?>

<div class="body_cadre_gris">

<!-- output page title -->
<div id="title_bar">
	<div id="page_title" class="float_left">
		<?php 
			/*echo $this->lang->line('reports_report_input');*/
			include('../wrightetmathon/application/views/partial/show_buttons.php');
		?>
	</div>
</div>

<!-- output error message -->
<?php
	if(isset($error))
	{
		echo "<div class='error_message'>".$error."</div>";
	}
?>
    <div id="centre">
        <div class="blocformfond creationimmediate">
            <fieldset>
<!-- output to screen or excel -->
<div>
	<?php echo $this->lang->line('reports_export_to_excel'); ?>
	<br>
	<input type="radio" name="export_excel" id="export_excel_yes" 	value='1' 					> <?php echo $this->lang->line('common_yes');	?>
	<input type="radio" name="export_excel" id="export_excel_no" 	value='0' checked='checked' > <?php echo $this->lang->line('common_no'); 	?>
</div>
<br>

<!-- output create PO or not -->
<div>
	<?php echo $this->lang->line('reports_create_PO'); ?>
	<br>
	<input type="radio" name="create_PO" id="create_PO_yes" 	value='1' 					> <?php echo $this->lang->line('common_yes');	?>
	<input type="radio" name="create_PO" id="create_PO_no" 		value='0' checked='checked' > <?php echo $this->lang->line('common_no'); 	?>
	<label id="supplier_label" for="supplier"><?php echo $this->lang->line('recvs_supplier'); ?></label>
		<?php echo form_dropdown	(
									'supplier_id',	 
									$_SESSION['G']->supplier_pick_list, 
									0
									); ?>
</div>
<br>

<!-- output set reorder_policy to N if no_mover-->
<div>
	<?php echo $this->lang->line('reports_set_reorder_policy_no_movers'); ?>
	<br>
	<input type="radio" name="set_NM" id="set_NM_yes" 	value='1' 					> <?php echo $this->lang->line('common_yes');	?>
	<input type="radio" name="set_NM" id="set_NM_no" 	value='0' checked='checked' > <?php echo $this->lang->line('common_no'); 	?>
</div>
<br>

<!-- output set reorder_policy to Y and quantity 0 if slow mover -->
<div>
	<?php echo $this->lang->line('reports_set_reorder_policy_slow_movers'); ?>
	<br>
	<input type="radio" name="set_SM" id="set_SM_yes" 	value='1' 					> <?php echo $this->lang->line('common_yes');	?>
	<input type="radio" name="set_SM" id="set_SM_no" 	value='0' checked='checked' > <?php echo $this->lang->line('common_no'); 	?>
</div>
<br>
            </fieldset>
            <div class="txt_droite">

              


                <?php
echo form_submit					(	array	(
												'name'	=>	'generate_report',
												'id'	=>	'generate_report',
												'value'	=>	$this->lang->line('common_run_report'),
												'class'	=>	'customer_submit_button sablier'
												)
									);
?>

            </div>
        </div>

        <?php $this->load->view("partial/pre_footer"); ?>
	<?php $this->load->view("partial/footer"); ?>
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
	
<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	$("#generate_report").click(function()
	{		
		$('#spinner_on_bar').show();
		
		var export_excel = 0;
		if ($("#export_excel_yes").attr('checked'))
		{
			export_excel = 1;
		}
		
		var create_PO = 0;
		if ($("#create_PO_yes").attr('checked'))
		{
			create_PO = 1;
		}
		
		var set_NM = 0;
		if ($("#set_NM_yes").attr('checked'))
		{
			set_NM = 1;
		}
		
		var set_SM = 0;
		if ($("#set_SM_yes").attr('checked'))
		{
			set_SM = 1;
		}
		
		window.location = window.location + '/' + export_excel + '/' + create_PO + '/' + set_NM + '/' + set_SM + '/' + supplier_id;
	});	
});
</script>
