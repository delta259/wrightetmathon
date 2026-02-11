<?php $this->load->view("partial/header"); ?>



<?php
if (isset($error_message))
{
    echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
    exit;
}
?>


<?php if (($_SESSION['reprint'] ?? 0) == 1): ?>
<style>.body_cadre_gris, .pre_footer, #footer { display: none !important; }</style>
<?php endif; ?>

<div class="body_cadre_gris">

    <div id="pr"   style="width: 1000px; background:white;">
        <br/><br/>
         <div id="receipt_wrapper" >


            <div id="receipt_header" >

                <br/>
                <div id="company_name" style="text-align: left"><?php echo $this->config->item('company'); ?></div>
                <div id="company_address" style="text-align: left"><?php echo nl2br($this->config->item('address')); ?></div>
                <div id="company_phone" style="text-align: left"><?php echo $this->config->item('phone'); ?></div>
                <br/>
                <div id="sale_receipt" style='font-size:48pt;font-weight:bold; text-align: center;'><?php echo $transaction_title; ?></div>
                <br/>
                <div id="sale_time" style="text-align: left"><?php echo $sale_id.' '.$transaction_time; ?></div>


                <?php if(isset($customer))
                {
                    ?>
                    <div id="customer" style="text-align: left"><?php echo $this->lang->line('customers_customer')." : ".$customer; ?></div>
                    <?php
                }
                ?>

                <div id="employee" style="text-align: left"><?php echo $this->lang->line('employees_soldby')." : ".$employee; ?></div>

            </div>
<br/><br/>
            <table id="receipt_items" class="center" width="100%" class="table table-bordered" style=" font-size:15px;border-collapse: separate; border-spacing: 8px;">

                <!------------------------------------------------------------------>
                <!-- Receipt Items												---->
                <!------------------------------------------------------------------>
                <tr>
                    <th style="text-align:center;"><?php echo $this->lang->line('items_item_number'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('items_item'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('sales_quantity'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('sales_price').$this->lang->line('sales_TTC'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('sales_discount'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('sales_total').$this->lang->line('sales_TTC'); ?></th>
                </tr>

                <?php
                foreach($cart as $line=>$item)
                {
                    // calculate line TTC because its not stored in the DB
                    $line_TTC													=	round($item['item_unit_price'] * ((100 + $item['line_tax_percentage']) / 100), 2);
                    ?>
                    <tr>
                        <td style='text-align:center;'><?php echo $item['line_item_number']; ?></td>
                        <td style='text-align:center;'><?php echo $item['line_name']; ?></td>
                        <td style='text-align:right;'><?php echo number_format($item['quantity_purchased'], 2); ?></td>
                        <td style='text-align:right;'><?php echo number_format($line_TTC, 2); ?></td>
                        <td style='text-align:right;'><?php echo number_format($item['discount_percent'], 2).$this->lang->line('common_percent'); ?></td>
                        <td style='text-align:right;'><?php echo to_currency($item['line_sales']); ?></td>
                    </tr>
                    <?php
                }
                ?>

                <!------------------------------------------------------------------>
                <!-- Total														---->
                <!------------------------------------------------------------------>

                <tr><td colspan="6">&nbsp;</td></tr>
                <tr><td colspan="5" style='text-align:right;font-size:16pt;font-weight:bold;'><?php echo $this->lang->line('reports_total_only').' '.$transaction_title.' '.$this->lang->line('reports_TTC'); ?></td>
                <td colspan="1" style='text-align:right;border-top:2px solid #000000;border-bottom:2px solid #000000;font-size:16pt;font-weight:bold;'><?php echo to_currency($total); ?></td>
                </tr>

                <!------------------------------------------------------------------>
                <!-- Subtotal after discount									---->
                <!------------------------------------------------------------------>

                <tr><td colspan="6">&nbsp;</td></tr>
                <tr><td colspan="5" style='text-align:right;'><?php echo $this->lang->line('reports_subtotal'); ?></td>
                <td colspan="1" style='text-align:right;'><?php echo to_currency($subtotal_after_discount); ?></td>
                </tr>

                <!------------------------------------------------------------------>
                <!-- Tax														---->
                <!------------------------------------------------------------------>
                <?php
                foreach($overall_tax as $tax)
                {
                    ?>
                <tr>
                    <td colspan="5" style='text-align:right;'><?php echo $overall_tax_name.' '.$tax['line_tax_percentage'].$this->lang->line('common_percent'); ?></td>
                    <td colspan="1" style='text-align:right;'><?php echo to_currency($tax['som']); ?></td></tr>
                <!--  -->
                
                    <!--
                <tr>
                    <td colspan="5" style='text-align:right;'><?php echo $overall_tax_name.' '.$overall_tax_percentage.$this->lang->line('common_percent'); ?></td>
                    <td colspan="1" style='text-align:right;border-bottom:2px solid #000000'><?php echo to_currency($tax_amount); ?></td></tr>
                <tr><td colspan="6">&nbsp;</td></tr><!--  -->
                <?php 
                }
                ?>  

                <tr><td colspan="5"></td> <td colspan="1" style='text-align:right;border-bottom:2px solid #000000;' >&nbsp;</td></tr>
                <tr><td colspan="6">&nbsp;</td></tr>


                <!------------------------------------------------------------------>
                <!-- Payments													---->
                <!------------------------------------------------------------------>
                <?php
                foreach($payments as $payment_id=>$payment)
                { ?>
                    <tr>
                        <td colspan="5" style="text-align:right;"><?php echo $payment['payment_type']; ?> </td>
                        <td colspan="1" style="text-align:right;"><?php echo to_currency($payment['payment_amount']); ?>  </td>
                    </tr>
                    <?php
                }
                ?>
            </table>

            <div id="sale_return_policy" class="txt_milieu">
                <?php echo nl2br($this->config->item('return_policy')); ?>
            </div>

            <div id="barcode" >
                <img src="<?php echo $image_path; ?>" alt="Barcode" style="float:left"/>
            </div>

        </div>
    </div>

    <style>@media print { .no-print { display: none !important; } }</style>
    <div style="text-align:center; margin: 20px 0; padding: 20px;" class="no-print">
        <a href="<?php echo site_url('sales'); ?>" style="display:inline-block; padding:12px 40px; background:#2563eb; color:#fff; border-radius:8px; font-size:16px; font-weight:600; text-decoration:none;">
            Nouvelle vente
        </a>
    </div>

</div>


<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
      // Show modals
      switch ($_SESSION['reprint'] ?? 0)
      {
          case 1:
              // this is the modal dialog output when suspending.
              include('../wrightetmathon/application/views/sales/reprint.php');
              break;

          default:
          
              if($this->Appconfig->get('print_after_sale'))
              {
                  ?>
              
                  <script type="text/javascript">
                      $(window).load	(function()
                          {
                              window.print();
                          }
                      );
                  </script>
                  <?php
              }
              break;
      }
      ?>

<?php /*
if ($this->Appconfig->get('print_after_sale'))
{
    ?>

    <script type="text/javascript">
        $(window).load	(function()
            {
                window.print();
            }
        );
    </script>
    <?php
}//*/
?>



