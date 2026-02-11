<?php $this->load->view("partial/header_popup"); ?>

<?php
$customer_name = isset($_SESSION['CSI']['SHV']->customer_formatted) ? $_SESSION['CSI']['SHV']->customer_formatted : '';
$invoice_id = isset($_SESSION['CSI']['SHV']->CN_original_invoice) ? $_SESSION['CSI']['SHV']->CN_original_invoice : '';
$invoice_items = isset($_SESSION['CSI']['SHV']->CN_original_invoice_items) ? $_SESSION['CSI']['SHV']->CN_original_invoice_items : array();
?>

<div class="md-modal-overlay">
<div class="md-modal" style="max-width:960px;width:95%;">

    <!-- Header -->
    <div class="md-modal-header">
        <div class="md-modal-header-left">
            <div class="md-modal-avatar" style="background:linear-gradient(135deg,var(--warning),#d97706);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            </div>
            <div class="md-modal-header-info">
                <div class="md-modal-name">Avoir - Facture #<?php echo htmlspecialchars($invoice_id); ?></div>
                <div class="md-modal-ref"><?php echo htmlspecialchars($customer_name); ?> &mdash; S&eacute;lectionnez les articles</div>
            </div>
        </div>
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </a>
    </div>

    <!-- Body -->
    <div class="md-modal-body" style="padding:0;">

        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php echo form_open("sales/CN_add_line", array('id' => 'cn_items_form')); ?>

        <!-- Items table -->
        <div style="max-height:440px;overflow-y:auto;padding:0;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:var(--bg-card,#f8fafc);border-bottom:2px solid var(--border-color,#e2e8f0);position:sticky;top:0;z-index:1;">
                        <th style="width:50px;text-align:center;padding:10px 8px;">
                            <input type="checkbox" id="cn-select-all" title="Tout s&eacute;lectionner" style="width:18px;height:18px;cursor:pointer;">
                        </th>
                        <th style="width:50px;text-align:center;padding:10px 8px;font-weight:600;color:var(--text-secondary,#64748b);font-size:11px;text-transform:uppercase;">SAV</th>
                        <th style="text-align:left;padding:10px 12px;font-weight:600;color:var(--text-secondary,#64748b);font-size:11px;text-transform:uppercase;">R&eacute;f&eacute;rence</th>
                        <th style="text-align:left;padding:10px 12px;font-weight:600;color:var(--text-secondary,#64748b);font-size:11px;text-transform:uppercase;">Article</th>
                        <th style="text-align:right;padding:10px 12px;font-weight:600;color:var(--text-secondary,#64748b);font-size:11px;text-transform:uppercase;">Qt&eacute;</th>
                        <th style="text-align:right;padding:10px 12px;font-weight:600;color:var(--text-secondary,#64748b);font-size:11px;text-transform:uppercase;">Prix TTC</th>
                        <th style="text-align:right;padding:10px 12px;font-weight:600;color:var(--text-secondary,#64748b);font-size:11px;text-transform:uppercase;">Rem.%</th>
                        <th style="text-align:right;padding:10px 12px;font-weight:600;color:var(--text-secondary,#64748b);font-size:11px;text-transform:uppercase;">Total TTC</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $line_index = 0;
                foreach ($invoice_items as $invoice_item):
                    if ($invoice_item->quantity_purchased <= 0) continue;
                    $line_total = $invoice_item->line_sales;
                    $discount = $invoice_item->discount_percent;
                ?>
                    <tr class="cn-item-row" style="border-bottom:1px solid var(--border-color,#e2e8f0);transition:background 0.15s;">
                        <td style="text-align:center;padding:10px 8px;">
                            <?php echo form_checkbox("invoice_items[]", $invoice_item->item_id, FALSE, 'class="cn-item-check" style="width:18px;height:18px;cursor:pointer;" ' . $invoice_item->sales_id); ?>
                        </td>
                        <td style="text-align:center;padding:10px 8px;">
                            <?php echo form_checkbox("sav[]", $invoice_item->item_id, FALSE, 'style="width:16px;height:16px;cursor:pointer;" ' . $invoice_item->sales_id); ?>
                        </td>
                        <td style="padding:10px 12px;color:var(--text-secondary,#64748b);font-family:monospace;font-size:12px;"><?php echo htmlspecialchars($invoice_item->line_item_number); ?></td>
                        <td style="padding:10px 12px;font-weight:500;color:var(--text-primary,#1e293b);"><?php echo htmlspecialchars($invoice_item->line_name); ?></td>
                        <td style="padding:10px 12px;text-align:right;font-weight:600;"><?php echo number_format($invoice_item->quantity_purchased, 0); ?></td>
                        <td style="padding:10px 12px;text-align:right;color:var(--text-secondary,#64748b);"><?php echo number_format($invoice_item->item_unit_price, 2, ',', ' '); ?> &euro;</td>
                        <td style="padding:10px 12px;text-align:right;color:<?php echo ($discount > 0) ? 'var(--danger,#ef4444)' : 'var(--text-secondary,#64748b)'; ?>;"><?php echo ($discount > 0) ? number_format($discount, 1, ',', '') . '%' : '-'; ?></td>
                        <td style="padding:10px 12px;text-align:right;font-weight:600;"><?php echo number_format($line_total, 2, ',', ' '); ?> &euro;</td>
                    </tr>
                <?php
                    $line_index++;
                endforeach;
                ?>
                </tbody>
            </table>
        </div>

        <!-- Full cancellation toggle -->
        <div style="padding:14px 24px;border-top:1px solid var(--border-color,#e2e8f0);display:flex;align-items:center;gap:10px;background:var(--bg-card,#f8fafc);">
            <label class="md-toggle-row" style="cursor:pointer;display:flex;align-items:center;gap:10px;margin:0;">
                <span class="md-toggle" style="flex-shrink:0;">
                    <input type="checkbox" name="annulation_facture" id="annulation_facture" class="md-toggle-input">
                    <span class="md-toggle-slider"></span>
                </span>
                <span style="font-weight:600;font-size:14px;color:var(--danger,#ef4444);">Annulation totale de la facture</span>
            </label>
        </div>

        <?php // form_close is after the footer buttons ?>

    </div>

    <!-- Footer -->
    <div class="md-modal-footer">
        <div class="md-modal-footer-left">
            <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour
            </a>
        </div>
        <div class="md-modal-footer-right">
            <button type="submit" class="md-btn md-btn-primary" id="cn_submit_btn" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Valider l'avoir
            </button>
        </div>
    </div>

    <?php echo form_close(); ?>

</div>
</div>

<style>
.cn-item-row:hover {
    background: var(--bg-hover, rgba(37,99,235,0.04)) !important;
}
.cn-item-row td {
    cursor: default;
}
#cn_submit_btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script>
$(document).ready(function() {
    // Update submit button state based on selections
    function updateSubmitState() {
        var hasChecked = $('.cn-item-check:checked').length > 0 || $('#annulation_facture').is(':checked');
        $('#cn_submit_btn').prop('disabled', !hasChecked);
    }

    // Select all / deselect all
    $('#cn-select-all').on('change', function() {
        var checked = $(this).is(':checked');
        $('.cn-item-check').prop('checked', checked);
        updateSubmitState();
    });

    // Individual checkbox changes
    $('.cn-item-check').on('change', function() {
        var total = $('.cn-item-check').length;
        var checked = $('.cn-item-check:checked').length;
        $('#cn-select-all').prop('checked', checked === total);
        updateSubmitState();
    });

    // Full cancellation toggle
    $('#annulation_facture').on('change', function() {
        if ($(this).is(':checked')) {
            $('.cn-item-check').prop('checked', true);
            $('#cn-select-all').prop('checked', true);
            $('.cn-item-check').prop('disabled', true);
            $('#cn-select-all').prop('disabled', true);
        } else {
            $('.cn-item-check').prop('disabled', false);
            $('#cn-select-all').prop('disabled', false);
        }
        updateSubmitState();
    });

    // Click on row to toggle checkbox
    $('.cn-item-row').on('click', function(e) {
        if ($(e.target).is('input[type="checkbox"]')) return;
        if ($('#annulation_facture').is(':checked')) return;
        var cb = $(this).find('.cn-item-check');
        cb.prop('checked', !cb.prop('checked')).trigger('change');
    });

    updateSubmitState();
});
</script>
