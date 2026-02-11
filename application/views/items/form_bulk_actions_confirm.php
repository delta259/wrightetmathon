
<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="
    width: 1100px;
    ">


    <!-- Header fenetre -->
    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
		<?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).'  '.$_SESSION['$title']; ?>
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
<fieldset>
                <?php
	echo form_open('items/bulk_action_4');
	?>
    <br/>
    <h1 style="color: #2270a1;"><?php echo $this->lang->line('common_attention').'   '.$_SESSION['bulk_num_rows'].$this->lang->line('common_space').$this->lang->line('items_bulk_select'); ?></h1>
    <h2><?php echo form_label($this->lang->line('items_bulk_sample')); ?></h2>
<br/>
<div style=" overflow-y:scroll; overflow-x:hidden;margin-top: 0px;    max-height: 515px;     min-height: 340px; width: 100%;border:#f5f5f5 1px solid;">
    <!-- now show SELECTED info -->
	<table class="table table-striped table-bordered table-hover" width="100%" style="/*border-collapse: separate; border-spacing:5px;*/">
		<thead>
        <tr>
				<th align="center"><?php echo form_label($this->lang->line('items_item_number'), ' ', array('class'=>'required wide')); ?></th>
				<th align="center"><?php echo form_label($this->lang->line('items_name'), ' ', array('class'=>'required wide')); ?></th>
				<th align="center"><?php echo form_label($this->lang->line('items_category'), ' ', array('class'=>'required wide')); ?></th>
				<th align="center"><?php echo form_label($this->lang->line('items_supplier'), ' ', array('class'=>'required wide')); ?></th>
			</tr>
		</thead>

		<tbody id="cart_contents">

			
			<?php 
			$count														=	0;
			foreach ($_SESSION['bulk_selection'] as $row)
			{
				//$count													=	$count + 1;
				//if ($count <= 10)
				//{
					?>
					<tr>
						<td align="center"><?php	echo $row['item_number'];?></td>
						<td align="left"><?php	echo $row['item_name'];?></td>
						<td align="center"><?php	echo $row['category_name'];?></td>
						<td align="left"><?php	echo $row['supplier_name'];?></td>
					</tr>
					<?php
				//}
			}
			?>	
		</tbody>
	</table>
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
	<?php 	$form_submit	=	array	(
										'name'		=>	'submit',
										'id'		=>	'submit',
										'value'		=>	$this->lang->line('common_confirm'),
										'class'		=>	'btsubmit'
										);
			echo form_submit($form_submit);
			echo form_close();
    ?></div>

    </div>
<?php 
echo form_close();
?>
</dialog>
