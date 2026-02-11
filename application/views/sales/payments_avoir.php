<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:750px;">

<!-- Header fenetre -->
<div class="fenetre-header">
    <span id="page_title" class="fenetre-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).'  '.$this->lang->line('sales_add_payment'); ?>
    </span>
    <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
</div>

<!---CONTENT-->
<div class="fenetre-content">
    <div class="centrepage">
        <div class="blocformfond creationimmediate">

            <h3><?php echo $this->lang->line('sales_amount_due').' = '.to_currency($_SESSION['CSI']['SHV']->header_amount_due_TTC); ?></h3>

            <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

            <fieldset>
                <!-- Show payments selection form -->
                <div>
                    <?php
                    // Payment form is commented out for avoir mode
                    ?>

                    <br>

                    <!-- Show payments history -->
                    <?php
                    //if(count($_SESSION['CSI']['PD']) > 0)
                    //{
                    ?>
                        <table class="tablesorter report table table-striped table-bordered" width="100%">
                            <thead>
                                <tr>
                                    <th style="text-align:center"><?php echo $this->lang->line('sales_payment'); ?></th>
                                    <th style="text-align:right"><?php echo $this->lang->line('sales_amount_tendered'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                echo form_open("sales/edit_payment/$pmi", array('id' => 'edit_payment_form'.$pmi));
                                foreach ($_SESSION['CSI']['PD'] as $pmi => $payment)
                                {
                                ?>
                                    <tr>
                                        <td style="text-align:left"><?php echo $payment->payment_method_description; ?></td>
                                        <td style="text-align:right"><?php echo to_currency($payment->payment_amount_TTC); ?></td>
                                    </tr>
                                <?php
                                }
                                echo form_close();
                                ?>
                            </tbody>
                        </table>
                </div>

                    <?php
                    //}

                    // show complete button if amount due = 0
                    //if ($_SESSION['CSI']['SHV']->header_amount_due_TTC == 0)
                    //{
                    ?>

            </fieldset>
        </div>

        <div class="txt_milieu">
            <?php
            echo anchor(
                'common_controller/common_exit/',
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'
                .$this->lang->line('common_reset'),
                'class="btretour" target="_self"'
            );

            unset($_SESSION['blocage_de_l_impression_du_ticket_de_caisse']);
            ?>

            <!-- Valider la vente -->
            <a tabindex="100" href="<?php echo 'index.php/'.$_SESSION['controller_name'].'/confirm/invoice'; ?>" class="btsubmit" id="show_spinner">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <?php echo $this->lang->line('common_submit'); ?>
            </a>

            <!-- Envoyer le ticket par mail -->
            <a tabindex="101" href="<?php echo site_url("sales/Mail_Ticket"); ?>" class="btn-action-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <?php echo $this->lang->line('ticket_envoyer'); ?>
            </a>

            <!-- Vente sans ticket -->
            <a tabindex="102" href="<?php echo site_url("sales/sales_without_ticket"); ?>" class="btn-action-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 11-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                <?php echo $this->lang->line('common_sales_without_ticket'); ?>
            </a>
        </div>
    </div>
                    <?php
                    //}
                    ?>
        </div>
</div>

</dialog>
<?php
//unset($_SESSION['var_annulation_facture']);
?>

<script type="text/javascript">
$(document).ready(function() {
    $("#show_spinner").click(function() {
        $('#spinner_on_bar').show();
    });
});
</script>
