<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="
    width: 1000px;

    ">


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

			<table class="table  table-bordered " border=2>
				<thead>
					<tr bgcolor="#FF0033" align="center" style="font-weight:bold; ">
						<th colspan="4" style="text-align:center;"><?php echo $this->lang->line('common_manage').' '.$this->lang->line('items_label'); ?></th>
					</tr>
					<tr>
						<th style="text-align:left;"><?php echo $this->lang->line('items_item_number'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('items_name'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('items_category'); ?></th>
						<th style="text-align:center;"><?php echo $this->lang->line('sales_price_ttc'); ?></th>
					</tr>
				</thead>
				<br>
				<tbody>
					<tr>
						<td style="text-align:center;">	<?php 	echo form_open('items/label_do/');
																								echo form_input	(array	(
																														'name'	=>	'item_number',
																														'id'	=>	'item_number',
																														'value'	=>	$_SESSION['transaction_info']->item_number,
																														'style'	=>	'text-align:center; font-size:15px',
																														'size'	=>	'10'
																														)
																												);
																						?></td>

						<td  > <?php 	echo form_input	(array	(
																														'name'	=>	'name',
																														'id'	=>	'name',
																														'value'	=>	$_SESSION['transaction_info']->name,
																														'style'	=>	' font-size:15px',
																														'size'	=>	'40'
																														)
																												);
																						?></td>
						<td  style="text-align:center;"> <?php 	echo form_input	(array	(
																														'name'	=>	'category',
																														'id'	=>	'category',
																														'value'	=>	$_SESSION['transaction_info']->category,
																														'style'	=>	'text-align:center; font-size:15px',
																														'size'	=>	'10'
																														)
																												);
																						?></td>
						<td style="text-align:center;"> <?php 	echo $_SESSION['transaction_info']->sales_price; ?></td>
					</tr>

				</tbody>
			</table>

		<?php
			if ($_SESSION['label_show'] == 1)
			{
			?>
				<table border=2>
					<tr>	<!-- src		="<?php /* echo base_url().$_SESSION['transaction_info']->server_image; */?>" -->
						<td style="text-align:center;"><img src="<?php echo base_url().'label/'.$_SESSION['transaction_info']->server_image;; ?>"
															alt		="<?php echo $this->lang->line('items_label')?>"
															style	="width:227px;height:134px;">
						</td>
					</tr>
				</table>
			<?php
			}
		?>
</fieldset></div>

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
            </div></div></div></dialog>
</div>
