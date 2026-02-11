<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="

   width: 1140px;
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
	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');?>

                <fieldset>
	<?php
		include('../wrightetmathon/application/views/items/item_details.php');?>

<!-- Output the table header -->
<table border="2" class="tablesorter report table table-striped table-bordered ">
    <thead>
	<tr  align="center" ><th colspan="6"><?php echo $this->lang->line('items_inventory_tracking');?></th></tr>
	<tr align="center" >
		<th><?php echo $this->lang->line('sales_date');?></th>
		<th><?php echo $this->lang->line('sales_employee');?></th>
		<th><?php echo $this->lang->line('items_stock_before');?></th>
		<th><?php echo $this->lang->line('items_stock_movement');?></th>
		<th><?php echo $this->lang->line('items_stock_after');?></th>
		<th><?php echo $this->lang->line('sales_comments');?></th>
	</tr>
    </thead>

	<!-- Output the table data -->
	<?php
	foreach($_SESSION['inventory_info']	 as $row)
	{
	?>
		<tr align="center">
		<td><?php echo $row['trans_date'];?></td>
		<td><?php
			$person_id = $row['trans_user'];
			$employee = $this->Employee->get_info($person_id);
			echo $employee->first_name." ".$employee->last_name;
			?>
		</td>
		<td><?php echo $row['trans_stock_before'];?></td>
		<td><?php echo $row['trans_inventory'];?></td>
		<td><?php echo $row['trans_stock_after'];?></td>
		<td style="text-align:left;"><?php echo $row['trans_comment'];?></td>
		</tr>

	<?php
	}
	?>
</table>

    </fieldset>
            </div></div></div></dialog>