<?php $this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale" style="        position: absolute;
    left: 50%;
    right: 50%;
    top: -20%;
    transform: translate(-50%,50%);
    width: 912px;
    z-index: 101;">

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
            <!-- show next actions link -->

            <div class="txt_milieu">
                <div class="btnp">
                    <a href="<?php echo site_url("items/view_pricelists");?>" class="" style="width: 180px; margin:0; height:38px; text-align: left;"><img style="margin-left: 5px;
                                  margin-bottom: -15px;
                                  "  width="35px" height ="35px" src="images/prix.png"> <?php echo $this->lang->line('pricelists_pricelist') ;?> </a>
                    <a href="<?php echo site_url("items/view_suppliers");?>" style="width: 180px; margin:0; height:38px; text-align: left;" class=""><img style="margin-left: 5px;
                                  margin-bottom: -15px;
                                  "  width="35px" height ="35px" src="images/fournisseur.png"> <?php echo $this->lang->line('items_supplier') ;?> </a>
                    <a href="<?php echo site_url("items/view/".$_SESSION['transaction_info']->item_id);?>" class="" style="width: 180px; margin:0; height:38px; text-align: left;"><img style="margin-left: 5px;
                                  margin-bottom: -15px;
                                  "  width="35px" height ="35px" src="images/article.png"> <?php echo $this->lang->line('modules_'.$_SESSION['controller_name']) ;?> </a>

                </div>
            </div>


        <!-- show next actions link -->
        <?php
      /*  // pricelists
        echo anchor(	'items/view_pricelists',
            '=> '.$this->lang->line('pricelists_pricelist'),
            array('class'	=>	'undelete_button float_right'));
        // article
        // suppliers
        echo anchor(	'items/view_suppliers',
            '=> '.$this->lang->line('items_supplier'),
            array('class'	=>	'undelete_button float_right'));
        // article
        echo anchor(	'items/view/'.$_SESSION['transaction_info']->item_id,
            '=> '.$this->lang->line('modules_'.$_SESSION['controller_name']),
            array('class'	=>	'undelete_button float_right'));*/
        ?>
	<?php
		// get the number format -->
		$pieces =array();
		$pieces = explode("/", $this->config->item('numberformat'));
	?>

                <div class="blocformfond creationimmediate">
	
		<?php
			include('../wrightetmathon/application/views/partial/show_messages.php');?>

    <fieldset>

		<table class="tablesorter report table table-striped table-bordered " border=2>
			<thead>
				<tr  align="center" style="font-weight:bold; ">
					<th colspan="7" style="text-align:center;"><?php echo $this->lang->line('common_manage').' '.$this->lang->line('warehouses_warehouse'); ?></th>	
				</tr>
				<tr>
					<th align="center"><?php echo $this->lang->line('common_delete_short'); ?></th>
					<th align="center"><?php echo $this->lang->line('warehouses_warehouse_preferred'); ?></th>
					<th align="center"><?php echo $this->lang->line('warehouses_warehouse_description'); ?></th>
					<th align="center"><?php echo $this->lang->line('warehouses_warehouse_row'); ?></th>
					<th align="center"><?php echo $this->lang->line('warehouses_warehouse_section'); ?></th>
					<th align="center"><?php echo $this->lang->line('warehouses_warehouse_shelf'); ?></th>
					<th align="center"><?php echo $this->lang->line('warehouses_warehouse_bin'); ?></th>
				</tr>
			</thead>
			<br>
			<tbody id="cart_contents">
				<?php
				foreach ($_SESSION['transaction_warehouse_info'] as $row)
				{
				?>
				<tr>
				<!--	<td align="left"><?php /*echo anchor('items/item_warehouse_delete/'.$row->item_id.'/'.$row->warehouse_code.'/'.$row->warehouse_row.'/'.$row->warehouse_section.'/'.$row->warehouse_shelf.'/'.$row->warehouse_bin, '['.$this->lang->line('common_delete_short').']');*/?></td>-->

                    <td> <a href="<?php echo site_url('items/item_warehouse_delete/'.$row->item_id.'/'.$row->warehouse_code.'/'.$row->warehouse_row.'/'.$row->warehouse_section.'/'.$row->warehouse_shelf.'/'.$row->warehouse_bin);?>" title="<?php echo $this->lang->line('common_delete'); ?>">
                            <svg width="16" height="16" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                        </a></td>


                    <td align="center">	<?php 	echo $row->location_preferred ?></td>
					<td align="center">	<?php 	echo $row->warehouse_description ?></td>
					<td align="center">	<?php 	echo $row->warehouse_row ?></td>
					<td align="center">	<?php 	echo $row->warehouse_section ?></td>
					<td align="center">	<?php 	echo $row->warehouse_shelf ?></td>
					<td align="center">	<?php 	echo $row->warehouse_row ?></td>
				</tr>
				<?php
				}
				?>
			</tbody>
		</table>
			
		<table class="tablesorter report table table-striped table-bordered " border=2>
			<thead>
				<tr align="center" style="font-weight:bold; ">
					<th colspan="6" align="center"><?php echo $this->lang->line('common_add').' '.$this->lang->line('warehouses_warehouse'); ?></th>	
				</tr>
				<tr>
					<th align="center"><?php echo form_label($this->lang->line('warehouses_warehouse_preferred'), ' ', array('class'=>'required wide')); ?></th>
					<th align="center"><?php echo form_label($this->lang->line('warehouses_warehouse'), ' ', array('class'=>'required wide')); ?></th>
					<th align="center"><?php echo form_label($this->lang->line('warehouses_warehouse_row'), ' ', array('class'=>'required wide')); ?></th>
					<th align="center"><?php echo form_label($this->lang->line('warehouses_warehouse_section'), ' ', array('class'=>'required wide')); ?></th>
					<th align="center"><?php echo form_label($this->lang->line('warehouses_warehouse_shelf'), ' ', array('class'=>'required wide')); ?></th>
					<th align="center"><?php echo form_label($this->lang->line('warehouses_warehouse_bin'), ' ', array('class'=>'required wide')); ?></th>
				</tr>
			</thead>
			<br>
			<tbody id="cart_contents">
				
				<!-- now allow data entry -->
				<tr>
					<td align="center"><?php	echo form_open		('items/item_warehouse_add/');
												echo form_dropdown	(
																	'location_preferred', 
																	$_SESSION['G']->YorN_pick_list, 
																	$_SESSION['transaction_add_warehouse_info']->location_preferred,
																	'style="font-size:15px"','class="md-form-input"'
																	); ?>
					</td>
					<td	align="center"><?php	echo form_dropdown	(
																	'warehouse_code', 
																	$_SESSION['warehouse_pick_list'],
																	$_SESSION['transaction_add_warehouse_info']->warehouse_code,
																	'style="font-size:15px"','class="md-form-input"'
																	);?>
					</td>
					
					<td align="center"><?php	echo form_input(array	(
																	'name'		=>	'warehouse_row',
																	'id'		=>	'warehouse_row',
                            'class'=>'md-form-input',
																	'style'		=>	'text-align:left; font-size:15px;',
																	'value'		=>	$_SESSION['transaction_add_warehouse_info']->warehouse_row
																	));?>		
					</td>
					
					<td align="center"><?php	echo form_input(array	(
																	'name'		=>	'warehouse_section',
																	'id'		=>	'warehouse_section',
                            'class'=>'md-form-input',
																	'style'		=>	'text-align:left; font-size:15px;',
																	'value'		=>	$_SESSION['transaction_add_warehouse_info']->warehouse_section,
																	'size'		=>	8,
																	));?>
					</td>
					
					<td align="center"><?php	echo form_input(array	(
																	'name'		=>	'warehouse_shelf',
																	'id'		=>	'warehouse_shelf',
																	'style'		=>	'text-align:left; font-size:15px;',
																	'size'		=>	5,
																	'class'=>'md-form-input',
																	'value'		=>	$_SESSION['transaction_add_warehouse_info']->warehouse_shelf
																	));?>		
					</td>
					
					<td align="center"><?php	echo form_input(array	(
																	'name'		=>	'warehouse_bin',
																	'id'		=>	'warehouse_bin',
																	'style'		=>	'text-align:left; font-size:15px;',
																	'size'		=>	5,
                            'class'=>'md-form-input',
																	'value'		=>	$_SESSION['transaction_add_warehouse_info']->warehouse_bin
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

        </div></div></div>
	

</div>
