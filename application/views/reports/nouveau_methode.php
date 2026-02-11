<?php /*$this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale" style="
position: absolute;
left: 50%;
    right: 50%;
    top: 0%;
    transform: translate(-50%,50%);
width: 1090px;
z-index: 101;">


    <!-- Header fenetre -->
    <div class="fenetre-header">
	<span id="confirm_title" class="fenetre-title">
		<?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).'  '.$this->lang->line('sales_CN_select_invoice_items'); ?>
    </span>
        <?php
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>


    </div>



    <!-- Contenu de la fenetre-->
    <div class="fenetre-content">



        <div class="centrepage">


            <div class="blocformfond creationimmediate">

	
	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');
	?>
	
	<fieldset >
		<!-- show enter invoice -->
		<div>
			<?php 
				echo form_open("sales/CN_add_line");
				?>
					<table class="tablesorter report table table-striped table-bordered ">
						<thead>
							<tr>
								<th><?php echo $this->lang->line('common_please_select'); ?></th>
								<th><?php echo $this->lang->line('items_item_number'); ?></th>
								<th><?php echo $this->lang->line('items_name'); ?></th>
							</tr>
						</thead>
						
						<?php
						// read the records
						foreach($_SESSION['CSI']['SHV']->CN_original_invoice_items as $invoice_item)
						{
							?>
							<tbody>
								<tr>	
									<?php
									if ($invoice_item->quantity_purchased > 0)
									{
									?>
									<td style='text-align:center'><?php echo form_checkbox	(
																							"invoice_items[]", 										// data
																							$invoice_item->item_id, 								// value
																							FALSE,												 	// whether checked
																							$invoice_item->sales_id);								// extra
																				?></td>
									<td style='text-align:center'><?php echo $invoice_item->line_item_number; ?></td>
									<td style='text-align:center'><?php echo $invoice_item->line_name; ?></td>  
									<?php } ?>
								</tr>
							</tbody>
							<?php
						}
						?>	
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
					?>
                    </div>
				<?php
				echo form_close();
			?>
		</div>
    </div></div>

</div>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	$("#show_spinner").click(function()
	{		
		$('#spinner_on_bar').show();
	});	
});
</script>
