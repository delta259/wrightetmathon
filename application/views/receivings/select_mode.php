<?php
	// output header
	$this->load->view("partial/header");
?>


<!--Contenu background gris-->
<div class="body_page" id="loginPage">
    <div>
        <!--<div class="r_cnx_maintenance"></div>-->
        <div class="body_colonne">
            <h2><?php echo $this->lang->line('modules_'.$_SESSION['controller_name']); ?></h2>
            <div class="body_cadre_gris">

<div id="title_bar">
	<!-- Title -->
	<div id="page_title" class="float_left">
		<?php 
			echo $this->lang->line('recvs_select_mode');
		?>
	</div>
</div>

<!-- show dropdown to select mode -->
<fieldset style="margin-top:5px; border:1px solid #0A6184; border-radius:8px; box-shadow:0 0 15px #0A6184">

<br>
		
<div id>	
		<?php 
			echo form_open("receivings/change_mode",array('id'=>'mode_form')); 
			echo form_dropdown	(
								'mode', 
								$_SESSION['G']->stock_actions_pick_list,
								$_SESSION['G']->stock_actions_pick_list[0],
								'style="font-size:15px"; onchange="$(\'#mode_form\').submit();"'
								);
		?>
	</form>
</div>

<br>
</fieldset>
		
<br>		
<?php
	// show the footer
	$this->load->view("partial/footer");
?>
