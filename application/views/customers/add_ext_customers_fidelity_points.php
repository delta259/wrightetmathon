<?php $this->load->view("partial/header_popup"); ?>
<dialog open class="fenetre modale cadre" style=" width: 1000px;">
    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
        Ajout point(s) fidélité(s)
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
    <fieldset >
        <?php 
        echo form_open("customers/import_remote_fidelity_points/$key");
        ?>
        Voulez-vous importer les points de fidélités associés au client? <br>
        Si oui, les points de fidélité de la boutique d'origine seront réinitialisés. 
        <center>
        <br>
        <input type="radio" name="fidelity_points_remove" id="fidelity_points_remove_yes" value='1' checked='checked' > <?php echo $this->lang->line('common_yes'); ?>
        <br>
        <input type="radio" name="fidelity_points_remove" id="fidelity_points_remove_no" value='0' > <?php echo $this->lang->line('common_no'); ?>
        </center>
        <!--
    <table class="" >
        <thead>
            <th>
            </th>
        </thead>
        <tbody>
            <td align="center">
            <input type="radio" name="fidelity_points_remove" id="fidelity_points_remove_yes" value='1' checked='checked' > <?php echo $this->lang->line('common_yes'); ?>
            </td>
            <td align="center">
            <input type="radio" name="fidelity_points_remove" id="fidelity_points_remove_no" value='0' > <?php echo $this->lang->line('common_no'); ?>
            </td>         
        </tbody>
    </table><!-- -->
    </fieldset >
    </div>
                    <div class="txt_milieu">

                        <?php
                        $target	=	'target="_self"';
                        echo anchor			(
                            'common_controller/common_exit/','<div class="btretour btlien ">'.$this->lang->line('common_reset').'</div>',
                            $target
                        );
                        ?>
			<?php
			echo form_submit					(	array	(
															'name'	=>	'generate_report',
															'id'	=>	'generate_report',
															'value'	=>	$this->lang->line('common_submit'),
															'class'	=>	'btsubmit sablier'
															)
												);
                        ?></div>
		</form>
        </div>
</div></dialog>