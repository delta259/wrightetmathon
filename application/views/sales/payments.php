<?php
// Note: payments.php is always included from register.php (which provides full page structure)
// No need to load header_popup here

// Number format
$pieces = explode("/", $this->config->item('numberformat'));
$nf_dec = $pieces[0];
$nf_pt  = $pieces[1];
$nf_sep = $pieces[2];

$amount_due = $_SESSION['CSI']['SHV']->header_amount_due_TTC;
$force_form = !empty($_SESSION['force_payment_form']);
unset($_SESSION['force_payment_form']);
$is_paid    = ($amount_due == 0 && !$force_form);
$payments   = $_SESSION['CSI']['PD'];
$has_payments = (count($payments) > 0);

// Fidelity info for JS
$fidelity_value = isset($_SESSION['CSI']['SHV']->fidelity_value) ? (float)$_SESSION['CSI']['SHV']->fidelity_value : 0;
// Already used fidelity amount
$fidelity_used = 0;
foreach ($payments as $p) {
    if (isset($p->payment_method_fidelity_flag) && $p->payment_method_fidelity_flag == 'Y') {
        $fidelity_used += (float)$p->payment_amount_TTC;
    }
}
$fidelity_remaining = max(0, $fidelity_value - $fidelity_used);
// Fidelity payment method IDs (from already-loaded payments + known fidelity PDs)
$fidelity_pm_ids = array();
foreach ($payments as $pmi => $p) {
    if (isset($p->payment_method_fidelity_flag) && $p->payment_method_fidelity_flag == 'Y') {
        $fidelity_pm_ids[] = $pmi;
    }
}
// Also check all payment methods loaded by controller (query-free)
if (isset($_SESSION['CSI']['PM_fidelity'])) {
    $fidelity_pm_ids = array_unique(array_merge($fidelity_pm_ids, $_SESSION['CSI']['PM_fidelity']));
}

// Remove payment option 5 for partial invoice cancellation
if (($_SESSION['var_annulation_facture_partielle'] ?? 0) == 1) {
    unset($_SESSION['CSI']['PM']['5']);
}

// Default payment method
$default_pm = ($amount_due < 0) ? 2 : $_SESSION['CSI']['SHV']->default_payment_option;

// Calculate total paid
$total_paid = 0;
foreach ($payments as $p) {
    $total_paid += $p->payment_amount_TTC;
}
?>

<div class="md-modal-overlay">
<div class="md-modal" style="max-width: 650px;">

<!-- ========== HEADER ========== -->
<div class="md-modal-header" id="pay-header">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" id="pay-header-avatar" style="background: linear-gradient(135deg, <?php echo $is_paid ? '#22c55e, #16a34a' : '#2563eb, #1d4ed8'; ?>);">
            <?php if ($is_paid): ?>
            <svg class="pay-icon-ticket" width="24" height="24" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <svg class="pay-icon-payment" style="display:none" width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <?php else: ?>
            <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <?php endif; ?>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-name" id="pay-header-title"><?php echo $is_paid ? 'Ticket de caisse' : $this->lang->line('sales_add_payment'); ?></div>
            <div class="md-modal-ref" id="pay-header-ref"><?php echo $is_paid ? date('d/m/Y H:i') : $this->lang->line('modules_'.$_SESSION['controller_name']); ?></div>
        </div>
    </div>
    <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </a>
</div>

<!-- ========== BODY ========== -->
<div class="md-modal-body" style="padding: 0;">

    <!-- Messages -->
    <?php include(APPPATH . 'views/partial/show_messages.php'); ?>

    <?php if ($is_paid): ?>
    <!-- ==================== RECEIPT PREVIEW ==================== -->
    <div id="receipt-section">
        <div class="rcpt-paper">
            <!-- Logo -->
            <div class="rcpt-logo">
                <img src="<?php echo base_url('images/yes-store-logo.jpg'); ?>" alt="Logo">
            </div>

            <!-- Store header -->
            <div class="rcpt-header">
                <div class="rcpt-company"><?php echo htmlspecialchars($this->config->item('company')); ?></div>
                <div class="rcpt-address"><?php echo nl2br(htmlspecialchars($this->config->item('address'))); ?></div>
                <?php if ($this->config->item('phone')): ?>
                <div class="rcpt-phone">Tel: <?php echo htmlspecialchars($this->config->item('phone')); ?></div>
                <?php endif; ?>
            </div>

            <div class="rcpt-divider"></div>

            <!-- Sale info -->
            <div class="rcpt-info">
                <div class="rcpt-info-row">
                    <span>Date</span>
                    <span><?php echo date('d/m/Y H:i'); ?></span>
                </div>
                <?php if (isset($_SESSION['CSI']['EI']->last_name)): ?>
                <div class="rcpt-info-row">
                    <span>Vendeur</span>
                    <span><?php echo htmlspecialchars($_SESSION['CSI']['EI']->first_name.' '.$_SESSION['CSI']['EI']->last_name); ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['CSI']['SHV']->customer_formatted) && $_SESSION['CSI']['SHV']->customer_formatted): ?>
                <div class="rcpt-info-row">
                    <span>Client</span>
                    <span><?php echo htmlspecialchars($_SESSION['CSI']['SHV']->customer_formatted); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="rcpt-divider"></div>

            <!-- Items -->
            <?php foreach ($_SESSION['CSI']['CT'] as $line => $cart_line):
                if ($cart_line->kit_item == 'Y') continue;
            ?>
            <div class="rcpt-item">
                <div class="rcpt-item-name"><?php echo htmlspecialchars($cart_line->name); ?></div>
                <div class="rcpt-item-detail">
                    <span><?php echo round($cart_line->line_quantity); ?> x <?php echo number_format($cart_line->line_priceTTC, $nf_dec, $nf_pt, $nf_sep); ?><?php echo ($cart_line->line_discount > 0) ? ' -'.$cart_line->line_discount.'%' : ''; ?></span>
                    <span class="rcpt-item-total"><?php echo number_format($cart_line->line_valueAD_TTC, $nf_dec, $nf_pt, $nf_sep); ?></span>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="rcpt-divider-double"></div>

            <!-- Totals -->
            <div class="rcpt-totals">
                <div class="rcpt-total-row">
                    <span>Sous-total HT</span>
                    <span><?php echo number_format($_SESSION['CSI']['SHV']->header_valueAD_HT, $nf_dec, $nf_pt, $nf_sep); ?></span>
                </div>
                <div class="rcpt-total-row">
                    <span>TVA</span>
                    <span><?php echo number_format($_SESSION['CSI']['SHV']->header_taxAD, $nf_dec, $nf_pt, $nf_sep); ?></span>
                </div>
            </div>

            <div class="rcpt-divider-thick"></div>

            <div class="rcpt-grand-total">
                <span>TOTAL TTC</span>
                <span><?php echo number_format($_SESSION['CSI']['SHV']->header_valueAD_TTC, $nf_dec, $nf_pt, $nf_sep); ?> <?php echo $this->config->item('currency_symbol'); ?></span>
            </div>

            <div class="rcpt-divider-thick"></div>

            <!-- Payments -->
            <div class="rcpt-payments">
                <?php foreach ($payments as $pmi => $payment): ?>
                <div class="rcpt-total-row">
                    <span><?php echo htmlspecialchars($payment->payment_method_description); ?></span>
                    <span><?php echo number_format($payment->payment_amount_TTC, $nf_dec, $nf_pt, $nf_sep); ?> <?php echo $this->config->item('currency_symbol'); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($this->config->item('return_policy')): ?>
            <div class="rcpt-divider"></div>
            <div class="rcpt-policy"><?php echo nl2br(htmlspecialchars($this->config->item('return_policy'))); ?></div>
            <?php endif; ?>

            <div class="rcpt-footer-text">Merci de votre visite !</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== PAYMENT FORM (always rendered when is_paid, for toggle) ==================== -->
    <div id="payment-section" style="<?php echo $is_paid ? 'display:none' : ''; ?>">

        <!-- Amount due banner -->
        <div class="pay-amount-banner <?php echo ($amount_due < 0) ? 'pay-amount-negative' : ''; ?>">
            <span class="pay-amount-label"><?php echo $this->lang->line('sales_amount_due'); ?></span>
            <span class="pay-amount-value"><?php echo to_currency($amount_due); ?></span>
        </div>

        <!-- Payment form -->
        <div class="pay-form-section">
            <?php echo form_open("sales/add_payment", array('class' => 'pay-form')); ?>
                <div class="pay-form-row">
                    <div class="pay-form-field pay-form-method">
                        <label class="pay-form-label"><?php echo $this->lang->line('sales_payment'); ?></label>
                        <?php echo form_dropdown(
                            'payment_method_id',
                            $_SESSION['CSI']['PM'],
                            $default_pm,
                            'class="md-form-select"'
                        ); ?>
                    </div>
                    <div class="pay-form-field pay-form-amount">
                        <label class="pay-form-label"><?php echo $this->lang->line('sales_amount_tendered'); ?></label>
                        <?php echo form_input(array(
                            'name'      => 'amount_tendered',
                            'id'        => 'amount_tendered',
                            'value'     => to_currency_no_money($amount_due),
                            'class'     => 'md-form-input pay-input-amount',
                            'data-vk'   => 'numeric',
                            'autofocus' => 'autofocus',
                        )); ?>
                        <div id="fidelity-info" style="display:none;font-size:11px;color:var(--primary,#2563eb);font-weight:600;margin-top:3px;">
                            Solde fidélité : <?php echo number_format($fidelity_remaining, 2, ',', ' '); ?> &euro;
                        </div>
                    </div>
                    <div class="pay-form-field pay-form-submit">
                        <?php echo form_submit(array(
                            'name'  => 'add_payment_submit',
                            'id'    => 'add_payment_submit',
                            'value' => $this->lang->line('common_add'),
                            'class' => 'md-btn md-btn-primary pay-btn-add',
                        )); ?>
                    </div>
                </div>
            <?php echo form_close(); ?>
        </div>

        <!-- Payment history -->
        <?php if ($has_payments): ?>
        <div class="pay-history">
            <div class="pay-history-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                Paiements enregistrés
            </div>
            <?php foreach ($payments as $pmi => $payment): ?>
            <div class="pay-history-row">
                <div class="pay-history-method">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <?php echo htmlspecialchars($payment->payment_method_description); ?>
                </div>
                <div class="pay-history-right">
                    <span class="pay-history-amount"><?php echo to_currency($payment->payment_amount_TTC); ?></span>
                    <a href="<?php echo site_url("sales/delete_payment/$pmi"); ?>" class="pay-history-delete" title="<?php echo $this->lang->line('common_delete_short'); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="pay-history-subtotal">
                <span>Total payé</span>
                <span><?php echo to_currency($total_paid); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========== FOOTER ========== -->
<div class="md-modal-footer" id="pay-footer">
    <?php if ($is_paid): ?>
    <?php unset($_SESSION['blocage_de_l_impression_du_ticket_de_caisse']); ?>
    <!-- Footer: ticket preview mode -->
    <div class="md-modal-footer-left" id="footer-receipt-left">
        <button type="button" class="md-btn md-btn-secondary" id="btn-back-to-payments">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </button>
    </div>
    <div class="md-modal-footer-right" id="footer-receipt-right">
        <a href="<?php echo site_url("sales/Mail_Ticket"); ?>" class="md-btn md-btn-secondary" title="Envoyer la facture par email">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Envoyer
        </a>
        <a href="<?php echo site_url("sales/sales_without_ticket"); ?>" class="md-btn md-btn-secondary pay-btn-no-ticket" id="show_spinner_no_ticket" title="Valider sans imprimer de ticket">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/><rect x="5" y="3" width="14" height="18" rx="1" fill="none"/></svg>
            Sans ticket
        </a>
        <a href="<?php echo 'index.php/'.$_SESSION['controller_name'].'/confirm/invoice'; ?>" class="md-btn md-btn-success" id="show_spinner">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="3" width="14" height="18" rx="1"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/></svg>
            Imprimer
        </a>
    </div>

    <!-- Footer: payment selection mode (hidden initially) -->
    <div class="md-modal-footer-left pay-footer-payments" style="display:none">
        <button type="button" class="md-btn md-btn-secondary" id="btn-back-to-receipt">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="3" width="14" height="18" rx="1"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/></svg>
            Ticket
        </button>
    </div>
    <div class="md-modal-footer-right pay-footer-payments" style="display:none">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">Fermer</a>
    </div>

    <?php else: ?>
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">Fermer</a>
        <?php if ($amount_due == 0): ?>
        <form action="<?php echo site_url('sales/payments'); ?>" method="post" style="display:inline;">
            <button type="submit" class="md-btn md-btn-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Valider
            </button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

</div><!-- end .md-modal -->
</div><!-- end .md-modal-overlay -->

<style>
/* ===== Amount due banner ===== */
.pay-amount-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: linear-gradient(135deg, var(--bg-card, #f8fafc), var(--bg-hover, #f1f5f9));
    border-bottom: 1px solid var(--border-color, #c5cbd3);
}
.pay-amount-label {
    font-size: 0.9em;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.pay-amount-value {
    font-size: 1.6em;
    font-weight: 800;
    color: var(--primary, #2563eb);
    letter-spacing: -0.02em;
}
.pay-amount-negative .pay-amount-value {
    color: var(--danger, #ef4444);
}

/* ===== Payment form ===== */
.pay-form-section {
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-color, #c5cbd3);
}
.pay-form-row {
    display: flex;
    align-items: flex-end;
    gap: 12px;
}
.pay-form-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.pay-form-label {
    font-size: 0.78em;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.pay-form-method { flex: 1.5; }
.pay-form-amount { flex: 1; }
.pay-form-submit {
    flex: 0 0 auto;
    padding-bottom: 1px;
}
.pay-input-amount {
    text-align: right !important;
    font-size: 1.1em !important;
    font-weight: 700 !important;
    letter-spacing: 0.02em;
}
.pay-btn-add {
    white-space: nowrap;
    padding: 8px 20px !important;
    cursor: pointer;
}

/* ===== Payment history ===== */
.pay-history { padding: 0; }
.pay-history-title {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    font-size: 0.82em;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    border-bottom: 1px solid var(--border-color, #c5cbd3);
}
.pay-history-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 24px;
    border-bottom: 1px solid var(--border-color, #c5cbd3);
    transition: background 0.15s;
}
.pay-history-row:hover { background: var(--bg-hover, #f1f5f9); }
.pay-history-method {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9em;
    font-weight: 500;
    color: var(--text-primary, #1e293b);
}
.pay-history-method svg {
    color: var(--text-secondary, #64748b);
    flex-shrink: 0;
}
.pay-history-right {
    display: flex;
    align-items: center;
    gap: 14px;
}
.pay-history-amount {
    font-size: 1em;
    font-weight: 700;
    color: var(--primary, #2563eb);
    min-width: 80px;
    text-align: right;
}
.pay-history-delete {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    color: var(--text-secondary, #64748b);
    transition: all 0.15s;
}
.pay-history-delete:hover {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger, #ef4444);
}
.pay-history-subtotal {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 24px;
    font-size: 0.85em;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    background: var(--bg-hover, #f1f5f9);
    border-top: 1px solid var(--border-color, #c5cbd3);
}
.pay-history-subtotal span:last-child {
    font-weight: 700;
    color: var(--text-primary, #1e293b);
}

/* ========================================================= */
/* ===== Thermal Receipt Preview (TM-T88 80mm style) ====== */
/* ========================================================= */
#receipt-section {
    display: flex;
    justify-content: center;
    padding: 20px 16px;
    background: var(--bg-card, #f1f5f9);
    min-height: 200px;
}
.rcpt-paper {
    width: 302px;
    background: #fff;
    padding: 16px 14px 20px;
    border: 1px solid #d1d5db;
    border-radius: 2px;
    box-shadow:
        0 2px 8px rgba(0,0,0,0.08),
        0 0 0 1px rgba(0,0,0,0.03);
    font-family: 'Courier New', Courier, monospace;
    font-size: 11.5px;
    line-height: 1.35;
    color: #111;
    /* Simulated paper edge */
    position: relative;
}
.rcpt-paper::before {
    content: '';
    position: absolute;
    top: -6px;
    left: 0;
    right: 0;
    height: 6px;
    background: repeating-linear-gradient(
        90deg,
        transparent 0px,
        transparent 4px,
        #d1d5db 4px,
        #d1d5db 5px
    );
    border-radius: 2px 2px 0 0;
}

/* Logo */
.rcpt-logo {
    text-align: center;
    margin-bottom: 8px;
}
.rcpt-logo img {
    max-width: 120px;
    max-height: 60px;
    object-fit: contain;
}

/* Store header */
.rcpt-header {
    text-align: center;
    margin-bottom: 2px;
}
.rcpt-company {
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.rcpt-address {
    font-size: 10.5px;
    color: #444;
    line-height: 1.3;
}
.rcpt-phone {
    font-size: 10.5px;
    color: #444;
}

/* Dividers */
.rcpt-divider {
    border: none;
    border-top: 1px dashed #999;
    margin: 8px 0;
}
.rcpt-divider-double {
    border: none;
    border-top: 1px dashed #999;
    border-bottom: 1px dashed #999;
    height: 3px;
    margin: 8px 0;
}
.rcpt-divider-thick {
    border: none;
    border-top: 2px solid #111;
    margin: 6px 0;
}

/* Sale info */
.rcpt-info {
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.rcpt-info-row {
    display: flex;
    justify-content: space-between;
    font-size: 10.5px;
}
.rcpt-info-row span:first-child {
    color: #666;
}

/* Items */
.rcpt-item {
    margin-bottom: 4px;
}
.rcpt-item-name {
    font-weight: 600;
    font-size: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rcpt-item-detail {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #444;
    padding-left: 8px;
}
.rcpt-item-total {
    font-weight: 700;
    color: #111;
    white-space: nowrap;
}

/* Totals */
.rcpt-totals {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.rcpt-total-row {
    display: flex;
    justify-content: space-between;
    font-size: 11.5px;
}

/* Grand total */
.rcpt-grand-total {
    display: flex;
    justify-content: space-between;
    font-size: 16px;
    font-weight: 900;
    letter-spacing: 0.02em;
    padding: 4px 0;
}

/* Payments */
.rcpt-payments {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

/* Return policy */
.rcpt-policy {
    text-align: center;
    font-size: 9.5px;
    color: #666;
    line-height: 1.3;
    margin-top: 4px;
}

/* Thank you */
.rcpt-footer-text {
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    margin-top: 10px;
    letter-spacing: 0.05em;
}

/* Dark mode: receipt stays white (it's paper) */
[data-theme="dark"] #receipt-section {
    background: var(--bg-card, #334155);
}
[data-theme="dark"] .rcpt-paper {
    background: #fff;
    color: #111;
    border-color: #9ca3af;
}

/* ===== Footer buttons ===== */
.pay-btn-no-ticket {
    color: var(--text-secondary, #64748b) !important;
    border-color: var(--border-color, #e2e8f0) !important;
    background: transparent !important;
}
.pay-btn-no-ticket:hover {
    background: var(--bg-hover, #f1f5f9) !important;
    color: var(--text-primary, #1e293b) !important;
}
.md-btn-success {
    background: var(--success, #22c55e) !important;
    color: #fff !important;
    border-color: var(--success, #22c55e) !important;
    font-weight: 700;
}
.md-btn-success:hover {
    background: #16a34a !important;
    border-color: #16a34a !important;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    // Spinner on validate click
    $("#show_spinner, #show_spinner_no_ticket").click(function() {
        $('#spinner_on_bar').show();
    });

    // Auto-select amount on focus
    $("#amount_tendered").on('focus', function() {
        $(this).select();
    });

    // Fidelity: pre-fill amount with MIN(fidelity_remaining, amount_due)
    var fidelityPmIds = <?php echo json_encode(array_map('intval', $fidelity_pm_ids)); ?>;
    var fidelityRemaining = <?php echo $fidelity_remaining; ?>;
    var normalAmountDue = '<?php echo to_currency_no_money($amount_due); ?>';

    $('select[name="payment_method_id"]').on('change', function() {
        var selectedId = parseInt($(this).val());
        if (fidelityPmIds.indexOf(selectedId) !== -1) {
            // Fidelity selected: pre-fill with MIN(fidelity_remaining, amount_due)
            var fidelAmt = Math.min(fidelityRemaining, Math.abs(<?php echo (float)$amount_due; ?>));
            $('#amount_tendered').val(fidelAmt.toFixed(2).replace('.', '<?php echo $nf_pt; ?>'));
            $('#fidelity-info').show();
        } else {
            $('#amount_tendered').val(normalAmountDue);
            $('#fidelity-info').hide();
        }
        $('#amount_tendered').select();
    });

    // Init: check if default selected is fidelity
    var initPm = parseInt($('select[name="payment_method_id"]').val());
    if (fidelityPmIds.indexOf(initPm) !== -1) {
        var fidelAmt = Math.min(fidelityRemaining, Math.abs(<?php echo (float)$amount_due; ?>));
        $('#amount_tendered').val(fidelAmt.toFixed(2).replace('.', '<?php echo $nf_pt; ?>'));
        $('#fidelity-info').show();
    }

    // Toggle: receipt → payment selection
    $("#btn-back-to-payments").click(function() {
        $("#receipt-section").slideUp(200);
        $("#payment-section").slideDown(200);
        // Switch footer
        $("#footer-receipt-left, #footer-receipt-right").hide();
        $(".pay-footer-payments").show();
        // Switch header
        $("#pay-header-title").text("<?php echo addslashes($this->lang->line('sales_add_payment')); ?>");
        $("#pay-header-ref").text("<?php echo addslashes($this->lang->line('modules_'.$_SESSION['controller_name'])); ?>");
        $("#pay-header-avatar").css('background', 'linear-gradient(135deg, #2563eb, #1d4ed8)');
        $(".pay-icon-ticket").hide();
        $(".pay-icon-payment").show();
    });

    // Toggle: payment selection → receipt
    $("#btn-back-to-receipt").click(function() {
        $("#payment-section").slideUp(200);
        $("#receipt-section").slideDown(200);
        // Switch footer
        $(".pay-footer-payments").hide();
        $("#footer-receipt-left, #footer-receipt-right").show();
        // Switch header
        $("#pay-header-title").text("Ticket de caisse");
        $("#pay-header-ref").text("<?php echo date('d/m/Y H:i'); ?>");
        $("#pay-header-avatar").css('background', 'linear-gradient(135deg, #22c55e, #16a34a)');
        $(".pay-icon-payment").hide();
        $(".pay-icon-ticket").show();
    });
});
</script>
