<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="      position: absolute;

    width: 785px;
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


    </div>

    <!---CONTENT-->
    <div class="fenetre-content">



        <div class="centrepage">



            <div class="blocformfond creationimmediate">

	
	<?php	include('../wrightetmathon/application/views/partial/show_messages.php'); ?>
		
	<?php
	echo form_open('items/bulk_action_2');
	?>
    <fieldset class="fieldset">
		<!-- show action dropdown picklist-->
		<table style="border-collapse: separate; border-spacing:5px;">
			<thead>
				<tr>
                    <th align="center"><h2><?php echo form_label($this->lang->line('items_bulk_picklist'), ' ', array('class'=>'')); ?></h2></th>
				</tr>
			</thead>
			<br>
			<tbody id="cart_contents">
				<!-- show bulk actions pick list -->
				<tr>
					<td align="center"><?php	echo form_dropdown	(
																	'bulk_action_id', 
																	$_SESSION['G']->bulk_actions_pick_list,
																	$_SESSION['G']->bulk_actions_pick_list[0],
																	'style="font-size:16px;margin-bottom:15px"'
																	);?>
					</td>
				</tr>
			</tbody>
		</table>
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
    ?></div>
<!--	</form>
</fieldset>-->

<?php 
echo form_close();
?>
</div>