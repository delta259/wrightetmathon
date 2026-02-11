<!-- -->
<!-- called from = controllers->receivings->receipt
<!-- -->

<?php $this->load->view("partial/header"); ?>



<div class="body_cadre_gris">

    <div id="pr"   style="width: 1000px; background:white;">
        <?php
        if (isset($error_message))
        {
            echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
            exit;
        }

        ?>
<div id="receipt_wrapper">
    <br/>
	
	<div id="receipt_header">
		<div id="company_name"><h3><?php echo $this->config->item('company'); ?></h3></div>
		<div colspan="7">&nbsp;</div>
		<div id="company_address"><?php echo nl2br($this->config->item('address')); ?></div>
		<div id="company_phone"><?php echo $this->config->item('phone'); ?></div>
		<div colspan="7">&nbsp;</div> 
		<div id="sale_receipt" style='font-size:14pt;font-weight:bold;'><?php echo "Commande"; ?></div>
		<div colspan="7">&nbsp;</div>
		<div id="sale_time"><?php echo $transaction_time ?></div>
		<div colspan="7">&nbsp;</div>
	</div>
	
	<div id="receipt_general_info">
		<?php if(isset($supplier))
		{
		?>
			<div id="customer"><?php echo $this->lang->line('suppliers_supplier').": ".$supplier; ?></div>
		<?php
		}
		?>
		<div id="sale_id"><?php echo  $this->lang->line('receivings_id').": ".$receiving_id; ?></div>
		<div id="comment"><?php echo $this->lang->line('recvs_comment').": ".$comment; ?></div>
		<div id="employee"><?php echo $this->lang->line('employees_employee').": ".$employee; ?></div>
	</div>

<br/><br/>
            <table id="receipt_items" class="center" width="100%" class="table table-bordered" style="font-size: 15px; border-collapse: separate; border-spacing: 8px;">

		<thead>
			<th style="text-align:left;"><?php echo $this->lang->line('items_item_number'); ?></th>
			<th style="text-align:left;"><?php echo $this->lang->line('items_category'); ?></th>
			<th style="text-align:left;"><?php echo $this->lang->line('items_item'); ?></th>
			<th style="text-align:right;"><?php echo $this->lang->line('common_price'); ?></th>
			<th style="text-align:right;"><?php echo $this->lang->line('sales_quantity'); ?></th>
			
			<!-- if this is a PO print qty received header else print discount header -->
			<?php
				if ($transaction_subtype == 'purchaseorder')
				{
			?>
				<th style="text-align:right;"><?php echo $this->lang->line('sales_qtyrec'); ?></th>
				<th style="text-align:right;"><?php echo $this->lang->line('items_dluo_year'); ?></th>
				<th style="text-align:right;"><?php echo $this->lang->line('items_dluo_month'); ?></th>
			<?php
				}
				else
				{
			?>
				<th style="text-align:right;"><?php echo $this->lang->line('sales_discount'); ?></th>
			<?php
				}
			?>
			
			<th style="text-align:right;" ><?php echo $this->lang->line('sales_total'); ?></th>
		</thead>
			
		<?php
		// get each cart line and print it.
		foreach($cart as $line=>$item)
		{
		?>
		<tr>
			<td style='text-align:left;'><?php echo $item['item_number']; ?></td>
			<td style='text-align:left;'><?php echo $item['category']; ?></td>
			<td style='text-align:left;'><?php echo $item['name']; ?></td>
			<td style='text-align:right;'><?php echo to_currency($item['price']); ?></td>
			<td style='text-align:right;'><?php echo number_format($item['quantity'], 2); ?></td>
			
			<!-- if this is a PO print qty received entry line / DLUO entry -->
			<?php
			if ($transaction_subtype == 'purchaseorder')
			{
			?>
			<td style='text-align:right;'>____</td>
			<td style='text-align:right;'>____</td>
			<td style='text-align:right;'>____</td>
			<?php
			}
			else
			{
			?>
			<td style='text-align:right;'><?php echo $item['discount']; ?></td>
			<?php
			}
			?>		
			<td style='text-align:right;'><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></td>
		</tr>
		<?php
		}
		?>
			
		<?php
		if ($transaction_subtype == 'purchaseorder')
		{
		?>
			<tr>
				<td colspan="9">&nbsp;</td>
			</tr>
			
			<tr>
				<td colspan="8" style='text-align:right;font-size:16pt;font-weight:bold;'><?php echo 'Total '; echo $transaction_title; echo ' HT'; ?></td>
				<td colspan="1" style='text-align:right;font-size:16pt;font-weight:bold;border-top:2px solid #000000;border-bottom:2px solid #000000;' class="bttotal"><?php echo to_currency($total); ?></td>
			</tr>
			
			<tr>
				<td colspan="9">&nbsp;</td>
			</tr>
		<?php
		}
		else
		{
		?>
			<tr>
				<td colspan="7">&nbsp;</td>
			</tr>
			
			<tr>
				<td colspan="6" style='text-align:right;font-size:16pt;font-weight:bold;'><?php echo 'Total '; echo $transaction_title; echo ' HT'; ?></td>
				<td colspan="1" style='text-align:right;font-size:16pt;font-weight:bold;border-top:2px solid #000000;border-bottom:2px solid #000000;'><?php echo to_currency($total); ?></td>
			</tr>
			
			<tr>
				<td colspan="7">&nbsp;</td>
			</tr>
		<?php
		}
		?>
		
			
	</table>

	<div id='barcode'>
		<img src="<?php echo $image_path; ?>" alt="Barcode" style="float:left"/>
	</div>

	<?php if (!empty($is_partial_receive)) { ?>
	<div style="clear:both; margin-top:20px; padding:12px 16px; border:2px solid #f59e0b; border-radius:8px; background:#fffbeb;">
		<div style="font-weight:bold; font-size:13pt; color:#92400e; margin-bottom:4px;">
			&#9888; R&eacute;ception partielle &mdash; Reliquat cr&eacute;&eacute;
		</div>
		<div style="font-size:11pt; color:#78350f;">
			<?php echo $reliquat_count; ?> article(s) non r&eacute;ceptionn&eacute;(s) sauvegard&eacute;(s) en bon de commande en attente :
			<strong><?php echo $reliquat_receiving_id; ?></strong>
		</div>
	</div>
	<?php } ?>

</div>
</div>
<?php
if ($this->Appconfig->get('print_after_sale'))
{
    ?>
    <script type="text/javascript">
        $(window).load	(	function()
            {
                window.print();
            }
        );
    </script>
    <?php
}
?>

    </div>
<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>




