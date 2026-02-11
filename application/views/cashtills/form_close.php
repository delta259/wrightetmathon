<?php
$raw_currencies = $_SESSION['transaction_info'] ?? [];
$currencies     = is_array($raw_currencies) ? $raw_currencies : [];
$total_close    = $_SESSION['total_caisse_close'] ?? 0;
$first_time     = ($_SESSION['first_time'] ?? 0) == 1;
$confirm_val    = $_SESSION['confirm'] ?? 'N';
$correction     = ($_SESSION['correction'] ?? 0) == 1;
$correction_amt = $_SESSION['correction_amount'] ?? 0;
$submit_label   = $_SESSION['submit'] ?? $this->lang->line('common_submit');
$open_amount    = $_SESSION['close_open_amount'] ?? 0;
$cash_sales     = $_SESSION['close_cash_sales_today'] ?? 0;
$theoretical    = $_SESSION['close_theoretical_total'] ?? 0;
?>

<style>
/* --- Cash close modal (compact) --- */
.cc-denom-table { width: 100%; border-collapse: collapse; }
.cc-denom-table th {
    font-size: 0.7em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); padding: 0.2em 0.4em;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
}
.cc-denom-table td {
    padding: 0.05em 0.4em; font-size: 1.05em;
    border-bottom: 1px solid color-mix(in srgb, var(--border-color, #e2e8f0) 50%, transparent);
    color: var(--text-primary, #1e293b); line-height: 1.35;
}
.cc-denom-table tr:last-child td { border-bottom: none; }
.cc-denom-table .cc-name { font-weight: 500; }
.cc-denom-table .cc-mult { color: var(--text-secondary, #64748b); text-align: center; }
.cc-denom-table .cc-sep { color: var(--text-secondary, #94a3b8); text-align: center; width: 18px; font-size: 0.85em; }
.cc-denom-table .cc-total-cell { text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; }
.cc-qty-input {
    width: 54px; text-align: right; padding: 0.2em 0.4em;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    font-size: 0.95em; font-variant-numeric: tabular-nums;
}
.cc-qty-input:focus {
    outline: none; border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 2px rgba(37,99,235,0.1);
}

/* Total row */
.cc-total-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.35em 0.6em; margin-top: 0.3em;
    background: var(--danger, #ef4444); border-radius: 6px; color: #fff;
}
.cc-total-label { font-weight: 700; font-size: 0.85em; text-transform: uppercase; }
.cc-total-value { font-size: 1.15em; font-weight: 700; font-variant-numeric: tabular-nums; }

/* Confirm row */
.cc-confirm-row {
    display: flex; align-items: center; gap: 0.6em;
    padding: 0.35em 0.6em; margin-top: 0.3em;
    background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25);
    border-radius: 6px;
}
.cc-confirm-row svg { color: #d97706; flex-shrink: 0; }
.cc-confirm-row span { font-size: 0.82em; font-weight: 600; color: var(--text-primary, #1e293b); }
.cc-confirm-select {
    margin-left: auto; padding: 0.2em 0.5em;
    border: 2px solid #d97706; border-radius: 4px; font-weight: 600;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    cursor: pointer;
}

/* Correction row */
.cc-correction-row {
    display: flex; align-items: center; gap: 0.6em;
    padding: 0.35em 0.6em; margin-top: 0.3em;
    background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2);
    border-radius: 6px;
}
.cc-correction-row svg { color: #dc2626; flex-shrink: 0; }
.cc-correction-label { font-size: 0.82em; font-weight: 600; color: var(--text-primary, #1e293b); }
.cc-correction-input {
    width: 90px; text-align: right; padding: 0.25em 0.4em; font-size: 0.95em; font-weight: 600;
    border: 2px solid #dc2626; border-radius: 4px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    font-variant-numeric: tabular-nums;
}
.cc-correction-input:focus { outline: none; box-shadow: 0 0 0 2px rgba(239,68,68,0.15); }

/* Theoretical amount card */
.cc-theory-card {
    display: flex; align-items: center; gap: 0.6em; flex-wrap: wrap;
    padding: 0.4em 0.6em; margin-bottom: 0.4em;
    background: rgba(37,99,235,0.05); border: 1px solid rgba(37,99,235,0.15);
    border-radius: 6px;
}
.cc-theory-item {
    display: flex; flex-direction: column; align-items: center; gap: 0.05em; min-width: 70px;
}
.cc-theory-label { font-size: 0.62em; font-weight: 600; text-transform: uppercase; color: var(--text-secondary, #64748b); }
.cc-theory-value { font-size: 0.95em; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--text-primary, #1e293b); }
.cc-theory-op { font-size: 1em; font-weight: 700; color: var(--text-secondary, #94a3b8); }
.cc-theory-result {
    margin-left: auto; padding: 0.2em 0.6em;
    background: var(--primary, #2563eb); color: #fff; border-radius: 4px;
    font-size: 0.95em; font-weight: 700; font-variant-numeric: tabular-nums;
}
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 620px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(239,68,68,0.12); color: #dc2626; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;">Fermeture de caisse</h2>
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

<?php echo form_open($_SESSION['controller_name'].'/save_close/', array('id' => 'close_form')); ?>

<!-- Theoretical amount -->
<div class="cc-theory-card">
    <div class="cc-theory-item">
        <span class="cc-theory-label">Ouverture</span>
        <span class="cc-theory-value"><?php echo number_format($open_amount, 2, ',', ' '); ?></span>
    </div>
    <span class="cc-theory-op">+</span>
    <div class="cc-theory-item">
        <span class="cc-theory-label">Ventes esp&egrave;ces</span>
        <span class="cc-theory-value"><?php echo number_format($cash_sales, 2, ',', ' '); ?></span>
    </div>
    <span class="cc-theory-op">=</span>
    <span class="cc-theory-result" id="cc_theory_display"><?php echo number_format($theoretical, 2, ',', ' '); ?> &euro;</span>
</div>

<!-- Comptage des espèces -->
<div style="padding: 0.2em 0;">
    <table class="cc-denom-table">
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
                <td class="cc-name"><?php echo $row->display_name; ?></td>
                <td style="text-align: right;">
                    <input type="number" name="quantity[<?php echo $key; ?>]"
                           class="cc-qty-input" min="0" step="1"
                           value="<?php echo (int)($row->quantity ?? 0); ?>"
                           data-multiplier="<?php echo $row->multiplier ?? 0; ?>"
                           onchange="calcCloseTotal()" oninput="calcCloseTotal()">
                </td>
                <td class="cc-sep">&times;</td>
                <td class="cc-mult"><?php echo number_format((float)($row->multiplier ?? 0), 2, ',', ''); ?></td>
                <td class="cc-sep">=</td>
                <td class="cc-total-cell" id="cc_line_<?php echo $key; ?>"><?php echo number_format((float)($row->total ?? 0), 2, ',', ' '); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <!-- Total -->
    <div class="cc-total-row">
        <span class="cc-total-label"><?php echo $this->lang->line('common_total') ?: 'Total'; ?></span>
        <span class="cc-total-value" id="cc_total_display"><?php echo number_format($total_close, 2, ',', ' '); ?> &euro;</span>
        <input type="hidden" name="total_caisse_close" id="total_caisse_close" value="<?php echo $total_close; ?>">
    </div>

    <!-- Correction -->
    <?php if ($first_time && $correction) { ?>
    <div class="cc-correction-row">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
        <span class="cc-correction-label"><?php echo $this->lang->line('cashtills_correction_amount') ?: 'Correction'; ?></span>
        <input type="number" name="correction_amount" id="correction_amount"
               class="cc-correction-input" step="0.01"
               value="<?php echo $correction_amt; ?>">
        <?php echo form_dropdown(
            'confirm',
            $_SESSION['G']->YorN_pick_list ?? array('N' => 'Non', 'Y' => 'Oui'),
            $confirm_val,
            'class="cc-confirm-select"'
        ); ?>
    </div>
    <?php } ?>

    <!-- Confirmation (sans correction) -->
    <?php if ($first_time && !$correction) { ?>
    <div class="cc-confirm-row">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <span>Confirmer la fermeture ?</span>
        <?php echo form_dropdown(
            'confirm',
            $_SESSION['G']->YorN_pick_list ?? array('N' => 'Non', 'Y' => 'Oui'),
            $confirm_val,
            'class="cc-confirm-select"'
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
        <button type="submit" form="close_form" name="submit" id="submit" class="md-btn md-btn-primary" style="padding: 0.35em 0.8em; font-size: 0.85em; background: var(--danger, #ef4444);">
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
function calcCloseTotal() {
    var total = 0;
    var inputs = document.querySelectorAll('.cc-qty-input');
    inputs.forEach(function(input, idx) {
        var qty = parseInt(input.value) || 0;
        var mult = parseFloat(input.getAttribute('data-multiplier')) || 0;
        var lineTotal = qty * mult;
        total += lineTotal;
        var lineEl = document.getElementById('cc_line_' + idx);
        if (lineEl) {
            lineEl.textContent = lineTotal.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    });
    document.getElementById('cc_total_display').innerHTML = total.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' \u20ac';
    document.getElementById('total_caisse_close').value = total;
}
$(document).ready(function() {
    calcCloseTotal();
    var firstInput = document.querySelector('.cc-qty-input');
    if (firstInput) firstInput.focus();
});
</script>
