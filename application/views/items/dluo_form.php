<?php $this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale" style="    position: absolute;
    left: 50%;
    right: 50%;
    top: -20%;
    transform: translate(-50%,50%);
    width: 1000px;
    z-index: 101;">


    <!--HEADER-->
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

<table class="tablesorter report table table-striped table-bordered " border=2>
				<thead>
					<tr  align="center" style="font-weight:bold; ">
						<th colspan="4" style="text-align:center;"><?php echo $this->lang->line('common_manage').' '.$this->lang->line('items_dluo'); ?></th>	
					</tr>
					<tr>
						<th style="text-align:center;"><?php echo $this->lang->line('common_delete_short'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('common_year'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('common_month'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('common_quantity'); ?></th>
					</tr>
				</thead>

				<tbody id="cart_contents">
					<?php
					foreach ($_SESSION['item_info_dluo'] as $row)
					{
					?>
					<tr>
						<!--<td style="text-align:left;"><?php /*echo anchor('items/dluo_delete/'.$row['year'].'/'.$row['month'], '['.$this->lang->line('common_delete_short').']');*/?></td>-->

                        <td style="text-align: center;"><a href="<?php echo site_url('items/dluo_delete/'.$row['year'].'/'.$row['month']);?>">
                                <img src="<?php echo base_url().'images2/del.png';?>" width="18px" height="18px" alt="Suppression">
                            </a></td>
						<td style="text-align:center;">	<?php 	echo $row['year'] ?></td>
						<td style="text-align:center;">	<?php 	echo $row['month'] ?></td>
						<td style="text-align:center;" class="zone_champ_saisie">	<?php 	echo form_open('items/dluo_edit/'.$row['year'].'/'.$row['month']);
																								/*echo form_label	(array	(
																														'name'	=>	'new_dluo_qty',
																														'id'	=>	'new_dluo_qty',
																														'value'	=>	number_format($row['dluo_qty'], 0),
																														'style'	=>	'text-align:right; font-size:15px',
																														'size'	=>	'6'
																														)
																												);*/


                                                                                                    echo form_label	(number_format($row['dluo_qty'], 0),
                                                                                                            'name="new_dluo_qty"'

                                                                                                    );
																								echo form_close();
																						?></td>
					</tr>
					<?php
					}
					?>
					<tr>
						<td colspan="3" style="text-align:center;">	<?php	echo $this->lang->line('common_quantity'); ?></td>
						<td style="text-align:center;">				<?php 	echo number_format($_SESSION['dluo_total_qty'], 0);	 ?></td>
					</tr>
				</tbody>
			</table>

			<table class="table table-bordered"   border=2>
				<thead>
					<tr  align="center" style="font-weight:bold; ">
						<th colspan="3" style="text-align:center;"><?php echo $this->lang->line('common_add').' '.$this->lang->line('items_dluo'); ?></th>	
					</tr>
					<tr>
						<th style="text-align:center;"><?php echo $this->lang->line('common_year'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('common_month'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('common_quantity'); ?></th>
					</tr>
				</thead>

				<tbody id="cart_contents">
					
					<!-- now allow data entry -->
					<tr>
						<td class="zone_champ_saisie" style="text-align:center;font-weight:bold;">	<?php 	echo form_open('items/dluo_add/');
																								echo form_input	(array	(
																														'name'	=>	'new1_add_year',
																														'id'	=>	'new1_add_year',
																														'class'=> 'colorobligatoire',
																														'style'	=>	'text-align:center; font-size:15px',
																														'value'	=>	$_SESSION['transaction_info']->dluo_year1,
																														'size'	=>	'4'
																														)
																												);
                            ?></td>

						<td class="zone_champ_saisie" style="text-align:center;font-weight:bold;">	<?php	echo form_input	(array	(
																														'name'	=>	'new1_add_month',
																														'id'	=>	'new1_add_month',
																														'style'	=>	'text-align:center; font-size:15px',
                                    'class'=> 'colorobligatoire',
																														'value'	=>	$_SESSION['transaction_info']->dluo_month1,
																														'size'	=>	'2'
																														)
																												);
                            ?> </td>
																												
						<td class="zone_champ_saisie" style="text-align:center;font-weight:bold;">	<?php	echo form_input	(array	(
																														'name'	=>	'new1_add_qty',
																														'id'	=>	'new1_add_qty',
                                    'class'=> 'colorobligatoire',
																														'style'	=>	'text-align:center; font-size:15px',
																														'value'	=>	$_SESSION['transaction_info']->dluo_qty1,
																														'size'	=>	'6'
																														)
																												);
																						?></td>
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
            
			<?php 	$form_submit	=	array	(
												'name'		=>	'submit',
												'id'		=>	'submit',
												'value'		=>	$this->lang->line('common_submit'),
												'class'		=>	'btsubmit'
												);
					echo form_submit($form_submit);
			?>
			


</div></div></div>
