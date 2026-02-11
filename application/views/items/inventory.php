
<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="     width: 1100px;
    ">

    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
		<?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).' '.$_SESSION['$title']; ?>
	</span>

		<?php
		//Croix pour fermer la petite fenêtre
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>


    </div>


    <div class="fenetre-content">



        <div class="centrepage">
            <div class="blocformfond creationimmediate">

	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');?>



<fieldset style="border-collapse: separate; border-spacing: 2px;">
    <?php
include('../wrightetmathon/application/views/items/item_details.php');?>
    <?php
    echo form_open('items/save_inventory/'.$_SESSION['transaction_info']->item_id, array('id'=>'item_form'));
    ?>
	<!-- create qty change field -->
	<div class="field_row clearfix txt_milieu" style="margin-bottom: 15px;">

		<div class="zone_champ_saisie">
            <?php echo form_label($this->lang->line('items_add_minus').':', 'newquantity', array('class'=>'required wide')); ?>
			<?php
				$newqty = 	array			(
											'name'		=>	'newquantity',
											'id'		=>	'newquantity',
											'type'		=>	'number',
											'value'		=>	0,
											'class'=>'colorobligatoire',
											'autofocus'	=>	'autofocus'
											);
				echo form_input($newqty);
			?> <a class="btaide" id="" title="<?php echo $this->lang->line('items_add_minus');?>"></a>
		</div>
	</div>

	<!-- create comments field -->
	<div class="field_row clearfix txt_milieu">




    <div class='zone_champ_saisie' style="margin-bottom: 15px;" >
        <?php echo form_label($this->lang->line('items_inventory_comments').':', 'trans_comment',array('class'=>'wide')); ?>

        <?php
				$this									->	load->helper('date');
				$now									=	time();
				$value									=	$this->lang->line('reports_rolling').' - '.unix_to_human($now, TRUE, 'eu');
				$newcmt = 	array			(
											'name'		=>	'trans_comment',
											'id'		=>	'trans_comment',
											'type'		=>	'text',
											'size'      =>	'32',
                    'class'=>'colornormal',
											'value'		=>	$value,
											);
				echo form_input($newcmt);
			?> <a class="btaide" id="" title="<?php echo $this->lang->line('items_inventory_comments');?>"></a>
		</div>
	</div>
</fieldset>
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
	</form>






	<!-- create submit button -->


<?php
echo form_close();
?>
</div>
