<?php
$raw_currencies = $_SESSION['transaction_info'] ?? [];
$currencies     = is_array($raw_currencies) ? $raw_currencies : [];
$total_change   = $_SESSION['total_caisse_change'] ?? 0;
$first_time     = ($_SESSION['first_time'] ?? 0) == 1;
$confirm_val    = $_SESSION['confirm'] ?? 'N';
$submit_label   = $_SESSION['submit'] ?? $this->lang->line('common_submit');
$open_amount    = $_SESSION['change_open_amount'] ?? 0;
$cash_sales     = $_SESSION['change_cash_sales_today'] ?? 0;
$theoretical    = $_SESSION['change_theoretical_total'] ?? 0;
?>

<style>
/* --- Change employee modal (compact) --- */
.ch-denom-table { width: 100%; border-collapse: collapse; }
.ch-denom-table th {
    font-size: 0.7em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); padding: 0.2em 0.4em;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
}
.ch-denom-table td {
    padding: 0.05em 0.4em; font-size: 1.05em;
    border-bottom: 1px solid color-mix(in srgb, var(--border-color, #e2e8f0) 50%, transparent);
    color: var(--text-primary, #1e293b); line-height: 1.35;
}
.ch-denom-table tr:last-child td { border-bottom: none; }
.ch-denom-table .ch-name { font-weight: 500; }
.ch-denom-table .ch-mult { color: var(--text-secondary, #64748b); text-align: center; }
.ch-denom-table .ch-sep { color: var(--text-secondary, #94a3b8); text-align: center; width: 18px; font-size: 0.85em; }
.ch-denom-table .ch-total-cell { text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; }
.ch-qty-input {
    width: 54px; text-align: right; padding: 0.2em 0.4em;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    font-size: 0.95em; font-variant-numeric: tabular-nums;
}
.ch-qty-input:focus {
    outline: none; border-color: var(--secondary, #8b5cf6);
    box-shadow: 0 0 0 2px rgba(139,92,246,0.15);
}
/* Total row */
.ch-total-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.35em 0.6em; margin-top: 0.3em;
    background: var(--secondary, #8b5cf6); border-radius: 6px; color: #fff;
}
.ch-total-label { font-weight: 700; font-size: 0.85em; text-transform: uppercase; }
.ch-total-value { font-size: 1.15em; font-weight: 700; font-variant-numeric: tabular-nums; }
/* Confirm row */
.ch-confirm-row {
    display: flex; align-items: center; gap: 0.6em;
    padding: 0.35em 0.6em; margin-top: 0.3em;
    background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25);
    border-radius: 6px;
}
.ch-confirm-row svg { color: #d97706; flex-shrink: 0; }
.ch-confirm-row span { font-size: 0.82em; font-weight: 600; color: var(--text-primary, #1e293b); }
.ch-confirm-select {
    margin-left: auto; padding: 0.2em 0.5em;
    border: 2px solid #d97706; border-radius: 4px; font-weight: 600;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    cursor: pointer;
}

/* Theoretical amount card */
.ch-theory-card {
    display: flex; align-items: center; gap: 0.6em; flex-wrap: wrap;
    padding: 0.4em 0.6em; margin-bottom: 0.4em;
    background: rgba(139,92,246,0.05); border: 1px solid rgba(139,92,246,0.15);
    border-radius: 6px;
}
.ch-theory-item {
    display: flex; flex-direction: column; align-items: center; gap: 0.05em; min-width: 70px;
}
.ch-theory-label { font-size: 0.62em; font-weight: 600; text-transform: uppercase; color: var(--text-secondary, #64748b); }
.ch-theory-value { font-size: 0.95em; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--text-primary, #1e293b); }
.ch-theory-op { font-size: 1em; font-weight: 700; color: var(--text-secondary, #94a3b8); }
.ch-theory-result {
    margin-left: auto; padding: 0.2em 0.6em;
    background: var(--secondary, #8b5cf6); color: #fff; border-radius: 4px;
    font-size: 0.95em; font-weight: 700; font-variant-numeric: tabular-nums;
}
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 620px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(139,92,246,0.12); color: #7c3aed; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;">Changement employ&eacute;</h2>
        </div>
    </div>
    <div class="md-modal-header-actions">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>
    </div>
</div>

<!-- Messages -->
<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<!-- Body -->
<div class="md-modal-body" style="padding: 0.4em 0.8em; max-height: 88vh; overflow-y: auto;">

<?php echo form_open($_SESSION['controller_name'].'/save_change/', array('id' => 'change_form')); ?>

<!-- Theoretical amount -->
<div class="ch-theory-card">
    <div class="ch-theory-item">
        <span class="ch-theory-label">Ouverture</span>
        <span class="ch-theory-value"><?php echo number_format($open_amount, 2, ',', ' '); ?></span>
    </div>
    <span class="ch-theory-op">+</span>
    <div class="ch-theory-item">
        <span class="ch-theory-label">Ventes esp&egrave;ces</span>
        <span class="ch-theory-value"><?php echo number_format($cash_sales, 2, ',', ' '); ?></span>
    </div>
    <span class="ch-theory-op">=</span>
    <span class="ch-theory-result"><?php echo number_format($theoretical, 2, ',', ' '); ?> &euro;</span>
</div>

<!-- Comptage des especes -->
<div style="padding: 0.2em 0;">

    <table class="ch-denom-table">
        <thead>
            <tr>
                <th style="text-align: left;"><?php echo $this->lang->line('currencies_display_name') ?: 'D&eacute;nomination'; ?></th>
                <th style="text-align: right;"><?php echo $this->lang->line('currencies_quantity') ?: 'Qt&eacute;'; ?></th>
                <th></th>
                <th style="text-align: center;"><?php echo $this->lang->line('currencies_multiplier') ?: 'Valeur'; ?></th>
                <th></th>
                <th style="text-align: right;"><?php echo $this->lang->line('common_total') ?: 'Total'; ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($currencies)) { ?>
            <tr><td colspan="6" style="text-align: center; padding: 1em; color: var(--text-secondary, #94a3b8); font-style: italic;">
                Aucune d&eacute;nomination trouv&eacute;e.
            </td></tr>
        <?php } ?>
        <?php foreach ($currencies as $key => $row) { ?>
            <tr>
                <td class="ch-name"><?php echo $row->display_name; ?></td>
                <td style="text-align: right;">
                    <input type="number" name="quantity[<?php echo $key; ?>]"
                           class="ch-qty-input" min="0" step="1"
                           value="<?php echo (int)($row->quantity ?? 0); ?>"
                           data-multiplier="<?php echo $row->multiplier ?? 0; ?>"
                           onchange="calcChangeTotal()" oninput="calcChangeTotal()">
                </td>
                <td class="ch-sep">&times;</td>
                <td class="ch-mult"><?php echo number_format((float)($row->multiplier ?? 0), 2, ',', ''); ?></td>
                <td class="ch-sep">=</td>
                <td class="ch-total-cell" id="ch_line_<?php echo $key; ?>"><?php echo number_format((float)($row->total ?? 0), 2, ',', ' '); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <!-- Total -->
    <div class="ch-total-row">
        <span class="ch-total-label"><?php echo $this->lang->line('common_total') ?: 'Total'; ?></span>
        <span class="ch-total-value" id="ch_total_display"><?php echo number_format($total_change, 2, ',', ' '); ?> &euro;</span>
        <input type="hidden" name="total_caisse_change" id="total_caisse_change" value="<?php echo $total_change; ?>">
    </div>

    <!-- Confirmation -->
    <?php if ($first_time) { ?>
    <div class="ch-confirm-row">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <span>Confirmer le changement ?</span>
        <?php echo form_dropdown(
            'confirm',
            $_SESSION['G']->YorN_pick_list ?? array('N' => 'Non', 'Y' => 'Oui'),
            $confirm_val,
            'class="ch-confirm-select"'
        ); ?>
    </div>
    <?php } ?>

</div>

<?php echo form_close(); ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.35em 0.8em;">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
            <?php echo $this->lang->line('common_reset') ?: 'Annuler'; ?>
        </a>
        <button type="submit" form="change_form" name="submit" id="submit" class="md-btn md-btn-primary" style="padding: 0.35em 0.8em; font-size: 0.85em; background: var(--secondary, #8b5cf6);">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <?php echo $submit_label; ?>
        </button>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<script>
function calcChangeTotal() {
    var total = 0;
    var inputs = document.querySelectorAll('.ch-qty-input');
    inputs.forEach(function(input, idx) {
        var qty = parseInt(input.value) || 0;
        var mult = parseFloat(input.getAttribute('data-multiplier')) || 0;
        var lineTotal = qty * mult;
        total += lineTotal;
        var lineEl = document.getElementById('ch_line_' + idx);
        if (lineEl) {
            lineEl.textContent = lineTotal.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    });
    document.getElementById('ch_total_display').innerHTML = total.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' \u20ac';
    document.getElementById('total_caisse_change').value = total;
}
$(document).ready(function() {
    calcChangeTotal();
    var firstInput = document.querySelector('.ch-qty-input');
    if (firstInput) firstInput.focus();
});
</script>
