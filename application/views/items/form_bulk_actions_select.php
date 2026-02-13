
<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="
    width:785px;
    ">

    <!--HEADER-->
    <div class="fenetre-header">
		<span id="page_title" class="fenetre-title">
			<?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).' '.$_SESSION['$title']; ?>
		</span>

		<?php
		include('../wrightetmathon/application/views/partial/show_exit.php');
		?>
    </div>


    <!---CONTENT-->
    <div class="fenetre-content">
        <div class="centrepage">
           <div class="blocformfond creationimmediate">

		<?php include('../wrightetmathon/application/views/partial/show_messages.php');?>
			<fieldset>
		<?php
		echo form_open('items/bulk_action_3');

		
		
		// ask for SET info depending on bulk action id
		switch ($_SESSION['bulk_action_id'])
		{
			// de-activate
			case	10:	
				
				break;
				
			// modify sales price TTC
			case	20:
				?>
				<table border=2  style="border-collapse: separate; border-spacing:5px;">
					<thead>
					<tr>
						<th align="center"><?php echo form_label($this->lang->line('pricelists_pricelist'), ' ', array('class'=>'required wide')); ?></th>
						<th align="center"><?php echo form_label($this->lang->line('items_unit_price_with_tax'), ' ', array('class'=>'required wide')); ?></th>
					</tr>
				</thead>

				<tbody id="cart_contents">
					
					<!-- now allow data entry -->
					<tr>
						<td align="center"><?php	echo form_open		('items/bulk_action_3');
													echo form_dropdown	(
																		'pricelist_id', 
																		$_SESSION['G']->pricelist_pick_list, 
																		$_SESSION['transaction_update_pricelist_info']->pricelist_id,
																		'style="font-size:15px" class="md-form-input"'
																		);?>
						</td>
						<td align="center"><?php	echo form_input(array	(
																		'name'		=>	'unit_price_with_tax',
																		'id'		=>	'unit_price_with_tax',
																		'style'		=>	'text-align:left; font-size:15px;',
																		'class'=>"md-form-input",
																		'value'		=>	$_SESSION['transaction_update_pricelist_info']->unit_price_with_tax,
																		));?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
			break;
			
		// Reorder Policy
		case	30:
			?>
			<table border=2  style="border-collapse: separate; border-spacing:5px;">
                <tbody id="cart_contents">


                <tr><td align="left"><?php echo form_label($this->lang->line('suppliers_supplier'), 		' ', array('class'=>'required wide')); ?></td>
                    <td align="left"><?php	echo form_open		('items/bulk_action_3');
                        echo form_dropdown	(
                            'supplier_id',
                            $_SESSION['G']->supplier_pick_list,
                            $_SESSION['transaction_update_supplier_info']->supplier_id,
                            'style="font-size:15px" class="md-form-input"'
                        );?>
                    </td>
                </tr>

					<!-- now allow data entry -->
					<tr>
                        <td align="left"><?php echo form_label($this->lang->line('items_reorder_policy'), 	' ', array('class'=>'required wide')); ?></td>

						<td align="right"><?php	echo form_dropdown	(
																		'supplier_reorder_policy', 
																		$_SESSION['G']->YorN_pick_list, 
																		$_SESSION['transaction_update_supplier_info']->supplier_reorder_policy,
																		'style=" font-size:15px" class="md-form-input"'
																		);?>
						</td>
                    </tr><tr>
                    <td align="left"><?php echo form_label($this->lang->line('items_reorder_pack_size'), 	' ', array('class'=>'required wide')); ?></td>

                    <td align="right"><?php	echo form_input(array	(
																		'name'		=>	'supplier_reorder_pack_size',
																		'style'		=>	'text-align:right; font-size:15px;',
																		'size'		=>	5,
																		'class'=>"md-form-input",
																		'value'		=>	$_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size
																		));?>		
						</td>
                </tr>
                <tr>
                    <td align="left"><?php echo form_label($this->lang->line('items_min_order_qty'), 		' ', array('class'=>'required wide')); ?></td>

                    <td align="right"><?php	echo form_input(array	(
																		'name'		=>	'supplier_min_order_qty',
																		'style'		=>	'text-align:right; font-size:15px;',
																		'size'		=>	5,
																		'class'=>"md-form-input",
																		'value'		=>	$_SESSION['transaction_update_supplier_info']->supplier_min_order_qty
																		));?>		
                    </td>
                </tr>

                <tr>
                    <td align="left"><?php echo form_label($this->lang->line('items_min_stock_qty'), 		' ', array('class'=>'required wide')); ?></td>

                    <td align="right"><?php	echo form_input(array	(
																		'name'		=>	'supplier_min_stock_qty',
																		'style'		=>	'text-align:right; font-size:15px;',
																		'size'		=>	5,
																		'class'=>"md-form-input",
																		'value'		=>	$_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty
																		));?>		
						</td>
					</tr>
				</tbody>
			</table>
			<?php
			break;
			
		// Re-activate
		case	40:	
			break;
			
		// DLUO
		case	50:
			?>
			<table border=2   style="border-collapse: separate; border-spacing:5px;">




				<tbody id="cart_contents">
					<!-- now allow data entry -->
					<tr>
                        <td align="center"><?php echo form_label($this->lang->line('items_dluo_indicator'), 	' ', array('class'=>'required wide')); ?></td>

                        <td align="center"><?php	echo form_dropdown	(
																		'dluo_indicator', 
																		$_SESSION['G']->YorN_pick_list, 
																		$_SESSION['transaction_info']->dluo_indicator,
																		'style="font-size:15px" class="md-form-input"'
																		);?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
			break;
		case	60:
			?>
			<table border=2  style="border-collapse: separate; border-spacing:5px;">
				<thead>
					<tr>
						<th align="center"><?php echo form_label($this->lang->line('items_nom_des_fournisseur'), ' ', array('class'=>'required wide')); ?></th>
						<th align="center"><?php echo form_label($this->lang->line('items_unit_price_without_tax'), ' ', array('class'=>'required wide')); ?></th>
					</tr>
				</thead>

				<tbody id="cart_contents">
					
					<!-- now allow data entry -->
					<tr>
						<td align="center"><?php	echo form_open		('items/bulk_action_3');
													echo form_dropdown	(
																		'pricelist_id', 
																		$_SESSION['G']->supplier_pick_list, 
																		$_SESSION['transaction_update_pricelist_info']->pricelist_id,
																		'style="font-size:15px" class="md-form-input"'
													);?>
						</td>
						<td align="center"><?php	echo form_input(array	(
																		'name'		=>	'unit_price_without_tax',
																		'id'		=>	'unit_price_without_tax',
																		'style'		=>	'text-align:left; font-size:15px;',
																		'class'=>"md-form-input",
																		'value'		=>	$_SESSION['transaction_update_pricelist_info']->unit_price_without_tax,
																		));?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
			break;

			//rattacher un lot d'article à un fournisseur
			case 70:
			?>
			<table border=2  style="border-collapse: separate; border-spacing:5px;">
                <tbody id="cart_contents">


                <tr><td align="left"><?php /*echo form_label($this->lang->line('suppliers_supplier'), 		' ', array('class'=>'required wide')); ?></td>
                    <td align="left"><?php	
                        echo form_dropdown	(
                            'supplier_id',
                            $_SESSION['G']->supplier_pick_list,
                            $_SESSION['transaction_update_supplier_info']->supplier_id,
                            'style="font-size:15px" class="md-form-input"'
                        );//*/    ?>
                    </td>
                </tr>

				<tr><td align="left"><?php echo form_label($this->lang->line('items_new_supplier'), 		' ', array('class'=>'required wide')); ?></td>
                    <td align="left"><?php	
                        echo form_dropdown(
                            'supplier_id_new',
                            $_SESSION['G']->supplier_pick_list,
                            $_SESSION['transaction_update_supplier_info']->supplier_id,
                            'style="font-size:15px" class="md-form-input"'
                        );?>
                    </td>
				</tr>
					<!-- now allow data entry -->
					<tr>
                        <td align="left"><?php echo form_label($this->lang->line('items_supplier_preferred'), 	' ', array('class'=>'required wide')); ?></td>

						<td align="right"><?php	echo form_dropdown(
																		'supplier_preferred', 
																		$_SESSION['G']->YorN_pick_list, 
																		$_SESSION['transaction_update_supplier_info']->supplier_preferred,
																		'style=" font-size:15px" class="md-form-input"'
																		);?>
						</td>
					</tr>
					<tr>
                    <td align="left"><?php echo form_label($this->lang->line('items_reorder_pack_size'), 	' ', array('class'=>'required wide')); ?></td>

                    <td align="right"><?php	echo form_input(array(
																		'name'		=>	'supplier_reorder_pack_size',
																		'style'		=>	'text-align:right; font-size:15px;',
																		'size'		=>	5,
																		'class'=>"md-form-input",
																		'value'		=>	$_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size
																		));?>		
						</td>
                </tr>
                <tr>
                    <td align="left"><?php echo form_label($this->lang->line('items_min_order_qty'), 		' ', array('class'=>'required wide')); ?></td>

                    <td align="right"><?php	echo form_input(array(
																		'name'		=>	'supplier_min_order_qty',
																		'style'		=>	'text-align:right; font-size:15px;',
																		'size'		=>	5,
																		'class'=>"md-form-input",
																		'value'		=>	$_SESSION['transaction_update_supplier_info']->supplier_min_order_qty
																		));?>		
                    </td>
                </tr>

                <tr>
                    <td align="left"><?php echo form_label($this->lang->line('items_min_stock_qty'), 		' ', array('class'=>'required wide')); ?></td>

                    <td align="right"><?php	echo form_input(array(
																		'name'		=>	'supplier_min_stock_qty',
																		'style'		=>	'text-align:right; font-size:15px;',
																		'size'		=>	5,
																		'class'=>"md-form-input",
																		'value'		=>	$_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty
																		));?>		
						</td>
					</tr>
				</tbody>
			</table>
			<br><br><br>
			<?php
			break;

		default:	
	}
	?>
	<!--  SELECT list  -->
	<?php
	if ($_SESSION['filtre']==1 && $_SESSION['bulk_action_id']!="40")
	{ ?>

	<tbody id="cart_contents">
	<input type="radio" name="metho" id="metho_by_select" value="metho_by_select" checked='checked' style="vertical-align:top">
	<label for="metho_by_select" > <?php echo form_label($this->lang->line('by_checked_list'), ' ', array('class'=>'required wide')); ?></label><br>
	
	<input type="radio" name="metho" id="metho_by_filtre" value="metho_by_filtre" style="vertical-align:top">
	<label for="metho_by_filtre"  > <?php echo form_label($this->lang->line('by_filtered_list'), ' ', array('class'=>'required wide')); ?></label><br>
	</tbody>
	<?php }
	?>
	<!-- now ask for SELECT info -->
	<table border=2  style="border-collapse: separate; border-spacing:5px;">
		<thead>

			<tr>
				<th align="center"><?php echo form_label($this->lang->line('common_and_or'), ' ', array('class'=>'required wide')); ?></th>
				<th align="center"><?php echo form_label($this->lang->line('common_column_name'), ' ', array('class'=>'required wide')); ?></th>
				<th align="center"><?php echo form_label($this->lang->line('common_operator'), ' ', array('class'=>'required wide')); ?></th>
				<th align="center"><?php echo form_label($this->lang->line('common_value'), ' ', array('class'=>'required wide')); ?></th>
			</tr>

		</thead>

		<tbody id="cart_contents">
			
			<!-- now ask for WHERE info -->
			<tr>
				<td align="center"><?php	echo ' '?></td>
				<td align="center"><?php	echo form_dropdown	(
																'column_id_1', 
																$_SESSION['M']->items_table_column_pick_list,
																$_SESSION['M']->items_table_column_pick_list[0],
																'style="font-size:15px" class="md-form-input"'
																);  ?>
				</td>
				<td align="center"><?php	echo form_dropdown	(
																'test_id_1', 
																$_SESSION['M']->test_pick_list,
																$_SESSION['M']->test_pick_list[0],
																'style="font-size:15px" class="md-form-input"'
																);?>
				</td>
				<td align="center"><?php	echo form_input(array	(
																'name'		=>	'value_1',
																'id'		=>	'value_1',
																'style'		=>	'text-align:left; font-size:15px;',
																'value'		=>	' ',
                        'class'=>'md-form-input',
																));?>
				</td>
			</tr>
			
			<tr>
				<td align="left"><?php	echo form_dropdown	(  'and_or_2',
																$_SESSION['M']->and_or_pick_list,
																$_SESSION['M']->test_pick_list[0],
																'style="font-size:15px" class="md-form-input"'
																);?>
				</td>
				<td align="center"><?php	echo form_dropdown	(
																'column_id_2', 
																$_SESSION['M']->items_table_column_pick_list,
																$_SESSION['M']->items_table_column_pick_list[0],
																'style="font-size:15px" class="md-form-input"'
																);?>
				</td>
				<td align="center"><?php	echo form_dropdown	(
																'test_id_2', 
																$_SESSION['M']->test_pick_list,
																$_SESSION['M']->test_pick_list[0],
																'style="font-size:15px" class="md-form-input"'
																);?>
				</td>
				<td align="center"><?php	echo form_input(array	(
																'name'		=>	'value_2',
																'id'		=>	'value_2',
																'style'		=>	'text-align:left; font-size:15px;',
																'value'		=>	' ',
                        'class'=>"md-form-input",
																));?>
				</td>
			</tr>
			<tr>
				<td align="left"><?php	echo form_dropdown	(  'and_or_3',
																$_SESSION['M']->and_or_pick_list,
																$_SESSION['M']->test_pick_list[0],
																'style="font-size:15px" class="md-form-input"'
																);?>
				</td>
				<td align="center"><?php	echo form_dropdown	(
																'column_id_3', 
																$_SESSION['M']->items_table_column_pick_list,
																$_SESSION['M']->items_table_column_pick_list[0],
																'style="font-size:15px" class="md-form-input"'
																);?>
				</td>
				<td align="center"><?php	echo form_dropdown	(
																'test_id_3', 
																$_SESSION['M']->test_pick_list,
																$_SESSION['M']->test_pick_list[0],
																'style="font-size:15px" class="md-form-input"'
																);?>
				</td>
				<td align="center"><?php	echo form_input(array	(
																'name'		=>	'value_3',
																'id'		=>	'value_3',
																'style'		=>	'text-align:left; font-size:15px;',
																'value'		=>	' ',
                        'class'=>"md-form-input",
																));?>
				</td>
			</tr>
		</tbody>
	</table>


</fieldset>
                <div id="required_fields_message" class="obligatoire">
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
	<?php 	$form_submit	=	array	(
										'name'		=>	'submit',
										'id'		=>	'submit',
										'value'		=>	$this->lang->line('common_submit'),
										'class'		=>	'btsubmit'
										);
			echo form_submit($form_submit);
			echo form_close();
	?>

    </div>
<?php 
echo form_close();
?>
 </dialog>
