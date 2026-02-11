
<!-- output header -->
<?php $this->load->view("partial/header"); ?>

<!-- output page title -->
<div class="body_cadre_gris">

    <!-- output page title -->
    <div id="title_bar">
        <div id="page_title" class="float_left">
            <h3><?php
                echo $this->lang->line('reports_low_inventory');?></h3>

            <span style="margin-top: -45px">	<?php	include('../wrightetmathon/application/views/partial/show_buttons.php');
                ?>
        </span>
        </div>
    </div>

<!-- output error message -->
<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');
	?>
<?php
	if(isset($error))
	{
		echo "<div class='error_message'>".$error."</div>";
	}
?>

    <div id="centre">
        <div class="blocformfond creationimmediate">
            <fieldset>
<!-- open form -->
<?php echo form_open("reports/inventory_low_validation"); ?>

<!-- output create PO or not -->
<div>
	<label id="supplier_label" for="create_po"><?php echo $this->lang->line('reports_create_PO'); ?></label>
		<?php echo form_dropdown	(
									'create_po', 
									$_SESSION['G']->YorN_pick_list
									);?></td>
</div>
<br>
<div>
	<label id="supplier_label" for="supplier_id"><?php echo $this->lang->line('recvs_supplier'); ?></label>
		<?php echo form_dropdown	(
									'supplier_id',	 
									$_SESSION['G']->supplier_pick_list, 
									$this->config->item('default_supplier_id')
									); ?>
</div>
<br>
            </fieldset>
            <div class="txt_droite">

            
<?php
echo form_submit					(	array	(
												'name'	=>	'generate_report',
												'id'	=>	'generate_report',
												'value'	=>	$this->lang->line('common_run_report'),
												'class'	=>	'btsubmit'
												)
									);
?>
                </form></div>
        </div></div>
</div>

<?php $this->load->view("partial/pre_footer");?>
	<?php $this->load->view("partial/footer"); ?>


<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	$("#generate_report").click(function()
	{		
		$('#spinner_on_bar').show();
	});	
});
</script>
