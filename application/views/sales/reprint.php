<?php
$sale_info     = isset($_SESSION['reprint_sale_info']) ? $_SESSION['reprint_sale_info'] : null;
$customer_name = isset($_SESSION['reprint_customer_name']) ? $_SESSION['reprint_customer_name'] : '';
$sale_date     = $sale_info ? date('d/m/Y H:i', strtotime($sale_info->sale_time)) : '';
$sale_total    = $sale_info ? (float)$sale_info->overall_total : 0;
// Use raw numeric sale_id for form actions and URLs
$sale_id_num   = isset($sale_id_raw) ? $sale_id_raw : (isset($sale_info->sale_id) ? $sale_info->sale_id : $sale_id);
?>

<div class="md-modal-overlay" style="z-index:200;">
<div class="md-modal" style="max-width:720px;width:90%;">

    <!-- Header -->
    <div class="md-modal-header">
        <div class="md-modal-header-left">
            <div class="md-modal-avatar" style="background:linear-gradient(135deg,var(--primary,#2563eb),var(--secondary,#8b5cf6));">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            </div>
            <div class="md-modal-header-info">
                <div class="md-modal-name">R&eacute;&eacute;dition de facture #<?php echo htmlspecialchars($sale_id); ?></div>
                <div class="md-modal-ref"><?php echo $sale_date; ?><?php if (!empty($customer_name)): ?> &mdash; <?php echo htmlspecialchars($customer_name); ?><?php endif; ?> &mdash; <?php echo number_format($sale_total, 2, ',', ' '); ?> &euro;</div>
            </div>
        </div>
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </a>
    </div>

    <!-- Body -->
    <?php echo form_open("sales/reprint/".$sale_id_num, array('id' => 'reprint_form')); ?>
    <div class="md-modal-body" style="padding:16px 24px;">

        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <!-- Reprint options -->
        <div style="display:flex;gap:16px;">
            <div class="rp-option" id="rp-option-ticket">
                <input type="checkbox" name="reprint_ticket" id="reprint_ticket" class="rp-check">
                <div class="rp-option-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                </div>
                <div class="rp-option-text">
                    <div class="rp-option-title">Imprimer le ticket</div>
                    <div class="rp-option-desc">Imprimante ticket de caisse</div>
                </div>
            </div>
            <div class="rp-option" id="rp-option-mail">
                <input type="checkbox" name="reprint_mail" id="reprint_mail" class="rp-check">
                <div class="rp-option-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div class="rp-option-text">
                    <div class="rp-option-title">Envoyer par mail</div>
                    <div class="rp-option-desc">Ticket par email au client</div>
                </div>
            </div>
            <div class="rp-option" id="rp-option-invoice">
                <input type="checkbox" name="reprint_invoice" id="reprint_invoice" class="rp-check">
                <div class="rp-option-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div class="rp-option-text">
                    <div class="rp-option-title">Facture professionnelle</div>
                    <div class="rp-option-desc">Format A4, conforme r&eacute;glementation</div>
                </div>
            </div>
        </div>

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
            <button type="submit" class="md-btn md-btn-primary" id="rp-submit-btn" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span id="rp-submit-text">S&eacute;lectionnez une action</span>
            </button>
        </div>
    </div>
    <?php echo form_close(); ?>

</div>
</div>

<style>
.rp-option {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 10px;
    border: 2px solid var(--border-color, #e2e8f0);
    background: var(--bg-container, #fff);
    cursor: pointer;
    transition: all 0.15s ease;
}
.rp-option:hover {
    border-color: var(--primary, #2563eb);
    background: rgba(37,99,235,0.03);
}
.rp-option.rp-selected {
    border-color: var(--primary, #2563eb);
    background: rgba(37,99,235,0.06);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.rp-check {
    display: none;
}
.rp-option-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: var(--bg-card, #f8fafc);
    color: var(--text-secondary, #94a3b8);
    transition: all 0.15s ease;
}
.rp-option.rp-selected .rp-option-icon {
    background: var(--primary, #2563eb);
    color: #fff;
}
.rp-option-text {
    flex: 1;
}
.rp-option-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary, #1e293b);
    margin-bottom: 2px;
}
.rp-option-desc {
    font-size: 12px;
    color: var(--text-secondary, #94a3b8);
}
#rp-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script>
$(document).ready(function() {
    var invoiceUrl = '<?php echo site_url("sales/invoice/" . $sale_id_num); ?>';

    $('.rp-option').on('click', function() {
        $(this).toggleClass('rp-selected');
        updateSubmit();
    });

    function updateSubmit() {
        var ticket  = $('#rp-option-ticket').hasClass('rp-selected');
        var mail    = $('#rp-option-mail').hasClass('rp-selected');
        var invoice = $('#rp-option-invoice').hasClass('rp-selected');
        var any     = ticket || mail || invoice;

        $('#reprint_ticket').prop('checked', ticket);
        $('#reprint_mail').prop('checked', mail);
        $('#reprint_invoice').prop('checked', invoice);

        $('#rp-submit-btn').prop('disabled', !any);

        // Build label
        var parts = [];
        if (ticket) parts.push('Imprimer le ticket');
        if (mail) parts.push('Envoyer par mail');
        if (invoice) parts.push('G\u00e9n\u00e9rer la facture');

        if (parts.length === 0) {
            $('#rp-submit-text').html('S\u00e9lectionnez une action');
        } else {
            $('#rp-submit-text').text(parts.join(' + '));
        }
    }

    // Override form submit to handle invoice
    $('#reprint_form').on('submit', function(e) {
        var ticket  = $('#rp-option-ticket').hasClass('rp-selected');
        var mail    = $('#rp-option-mail').hasClass('rp-selected');
        var invoice = $('#rp-option-invoice').hasClass('rp-selected');

        // Open invoice in new tab if selected
        if (invoice) {
            window.open(invoiceUrl, '_blank');
        }

        // If ticket or mail selected, let form submit normally
        if (ticket || mail) {
            return true;
        }

        // Invoice only — no need to submit form
        e.preventDefault();
        return false;
    });

    updateSubmit();
});
</script>
