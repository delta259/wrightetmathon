<?php
$total_sa_year  = $_SESSION['total_set_aside_year'] ?? 0;
$total_sa_month = $_SESSION['total_set_aside_month'] ?? 0;
$total_bk_year  = $_SESSION['total_bank_year'] ?? 0;
$total_bk_month = $_SESSION['total_bank_month'] ?? 0;
$bal_year       = $_SESSION['balance_year'] ?? 0;
$bal_month      = $_SESSION['balance_month'] ?? 0;
$cash_day       = $_SESSION['cash_day'] ?? date('d');
$cash_month     = $_SESSION['cash_month'] ?? date('m');
$cash_year      = $_SESSION['cash_year'] ?? date('Y');
$reference      = $_SESSION['reference'] ?? '';
$deposit_amount = $_SESSION['deposit_amount'] ?? 0;
$submit_label   = $_SESSION['submit'] ?? $this->lang->line('common_submit');
$bank_history   = $_SESSION['bank_deposit_history'] ?? [];
$bank_history   = is_array($bank_history) ? $bank_history : [];
?>

<style>
/* --- Bank deposit modal --- */
.bk-summary-table { width: 100%; border-collapse: collapse; }
.bk-summary-table th {
    font-size: 0.7em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); padding: 0.3em 0.5em;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
}
.bk-summary-table td {
    padding: 0.4em 0.5em; font-size: 0.95em;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    color: var(--text-primary, #1e293b); text-align: right;
    font-variant-numeric: tabular-nums;
}
.bk-summary-table tr:last-child td { border-bottom: none; }
.bk-summary-table .bk-row-label { text-align: left; font-weight: 500; }
.bk-summary-table .bk-op { text-align: center; color: var(--text-secondary, #94a3b8); width: 24px; font-size: 0.85em; }
.bk-summary-table .bk-balance { font-weight: 700; }
.bk-balance-positive { color: #16a34a; }
.bk-balance-negative { color: #dc2626; }
.bk-balance-zero { color: var(--text-secondary, #94a3b8); }

.bk-form-row {
    display: flex; gap: 0.6em; align-items: end; flex-wrap: wrap;
}
.bk-form-group { display: flex; flex-direction: column; gap: 0.2em; }
.bk-form-group label {
    font-size: 0.72em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b);
}
.bk-form-group input {
    padding: 0.4em 0.5em; font-size: 1em;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    font-variant-numeric: tabular-nums;
}
.bk-form-group input:focus {
    outline: none; border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.bk-form-group input.required { border-color: #ef4444; }
.bk-form-group .bk-date-input { width: 48px; text-align: center; }
.bk-form-group .bk-year-input { width: 64px; text-align: center; }
.bk-form-group .bk-ref-input { width: 140px; }
.bk-form-group .bk-amount-input { width: 100px; text-align: right; font-weight: 600; font-size: 1.1em; }
.bk-date-sep {
    font-size: 1.1em; color: var(--text-secondary, #94a3b8); padding-bottom: 0.35em; font-weight: 500;
}

/* History toggle */
.bk-history-toggle {
    display: inline-flex; align-items: center; gap: 0.4em;
    padding: 0.3em 0.7em; margin-top: 0.6em;
    font-size: 0.78em; font-weight: 600;
    color: var(--primary, #2563eb); background: none; border: 1px solid var(--primary, #2563eb);
    border-radius: 6px; cursor: pointer; transition: all 0.15s;
}
.bk-history-toggle:hover {
    background: rgba(37,99,235,0.08);
}
.bk-history-toggle svg { transition: transform 0.2s; }
.bk-history-toggle.open svg { transform: rotate(180deg); }

/* History table */
.bk-history-section {
    display: none; margin-top: 0.5em;
    max-height: 260px; overflow-y: auto;
}
.bk-history-section.open { display: block; }
.bk-history-table { width: 100%; border-collapse: collapse; }
.bk-history-table th {
    font-size: 0.68em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); padding: 0.3em 0.5em;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
    position: sticky; top: 0; background: var(--bg-container, #fff); z-index: 1;
}
.bk-history-table td {
    padding: 0.25em 0.5em; font-size: 0.85em;
    border-bottom: 1px solid color-mix(in srgb, var(--border-color, #e2e8f0) 50%, transparent);
    color: var(--text-primary, #1e293b);
    font-variant-numeric: tabular-nums;
}
.bk-history-table tr:last-child td { border-bottom: none; }
.bk-history-table .bk-hist-amount { text-align: right; font-weight: 600; }
.bk-history-table .bk-hist-ref { color: var(--text-secondary, #64748b); font-size: 0.9em; }
.bk-history-table .bk-hist-emp { color: var(--text-secondary, #94a3b8); font-size: 0.85em; }
.bk-history-empty {
    text-align: center; padding: 1em; font-size: 0.85em; font-style: italic;
    color: var(--text-secondary, #94a3b8);
}
.bk-history-count {
    font-size: 0.7em; font-weight: 400; color: var(--text-secondary, #94a3b8);
    margin-left: 0.3em;
}
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 600px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.5em 1em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(37,99,235,0.12); color: #2563eb;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 21h18"></path><path d="M3 10h18"></path>
                <path d="M12 3l9 7H3l9-7z"></path>
                <path d="M5 10v11"></path><path d="M19 10v11"></path>
                <path d="M9 10v11"></path><path d="M15 10v11"></path>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1.05em;"><?php echo $this->lang->line('cashtills_bank') ?: 'Banque'; ?></h2>
        </div>
    </div>
    <div class="md-modal-header-actions">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>
    </div>
</div>

<!-- Messages -->
<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<!-- Body -->
<div class="md-modal-body" style="padding: 0.8em 1em; max-height: 85vh; overflow-y: auto;">

<?php echo form_open($_SESSION['controller_name'].'/save_bank', array('id' => 'bank_form')); ?>

<!-- Card: Récapitulatif -->
<div class="md-card" style="padding: 0.6em 0.8em; margin-bottom: 0.8em;">
    <div class="md-card-title" style="font-size: 0.8em; margin-bottom: 0.4em;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>
        </svg>
        R&eacute;capitulatif
    </div>

    <table class="bk-summary-table">
        <thead>
            <tr>
                <th style="text-align: left;"></th>
                <th style="text-align: right;"><?php echo $this->lang->line('cashtills_set_aside') ?: 'Versement'; ?></th>
                <th></th>
                <th style="text-align: right;"><?php echo $this->lang->line('cashtills_bank_deposit') ?: 'D&eacute;p&ocirc;t'; ?></th>
                <th></th>
                <th style="text-align: right;"><?php echo $this->lang->line('cashtills_balance') ?: 'Solde'; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="bk-row-label"><?php echo $this->lang->line('common_year') ?: 'Ann&eacute;e'; ?></td>
                <td><?php echo number_format($total_sa_year, 2, ',', ' '); ?></td>
                <td class="bk-op">&minus;</td>
                <td><?php echo number_format($total_bk_year, 2, ',', ' '); ?></td>
                <td class="bk-op">=</td>
                <td class="bk-balance <?php echo $bal_year > 0 ? 'bk-balance-positive' : ($bal_year < 0 ? 'bk-balance-negative' : 'bk-balance-zero'); ?>">
                    <?php echo number_format($bal_year, 2, ',', ' '); ?> &euro;
                </td>
            </tr>
            <tr>
                <td class="bk-row-label"><?php echo $this->lang->line('common_month') ?: 'Mois'; ?></td>
                <td><?php echo number_format($total_sa_month, 2, ',', ' '); ?></td>
                <td class="bk-op">&minus;</td>
                <td><?php echo number_format($total_bk_month, 2, ',', ' '); ?></td>
                <td class="bk-op">=</td>
                <td class="bk-balance <?php echo $bal_month > 0 ? 'bk-balance-positive' : ($bal_month < 0 ? 'bk-balance-negative' : 'bk-balance-zero'); ?>">
                    <?php echo number_format($bal_month, 2, ',', ' '); ?> &euro;
                </td>
            </tr>
        </tbody>
    </table>

    <!-- History toggle button -->
    <button type="button" class="bk-history-toggle" id="bk_history_btn" onclick="toggleBankHistory()">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
        Historique d&eacute;p&ocirc;ts <?php echo date('Y'); ?>
        <span class="bk-history-count">(<?php echo count($bank_history); ?>)</span>
    </button>

    <!-- History table (collapsible) -->
    <div class="bk-history-section" id="bk_history_section">
        <?php if (empty($bank_history)) { ?>
            <div class="bk-history-empty">Aucun d&eacute;p&ocirc;t bancaire enregistr&eacute; cette ann&eacute;e.</div>
        <?php } else { ?>
        <table class="bk-history-table">
            <thead>
                <tr>
                    <th style="text-align: left;">Date</th>
                    <th style="text-align: left;">R&eacute;f&eacute;rence</th>
                    <th style="text-align: right;">Montant</th>
                    <th style="text-align: left;">Par</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bank_history as $dep) { ?>
                <tr>
                    <td><?php echo str_pad($dep->cash_day, 2, '0', STR_PAD_LEFT).'/'.str_pad($dep->cash_month, 2, '0', STR_PAD_LEFT).'/'.substr($dep->cash_year, -2); ?></td>
                    <td class="bk-hist-ref"><?php echo htmlspecialchars($dep->cash_bank_deposit_reference ?? ''); ?></td>
                    <td class="bk-hist-amount"><?php echo number_format((float)($dep->cash_amount ?? 0), 2, ',', ' '); ?> &euro;</td>
                    <td class="bk-hist-emp"><?php echo htmlspecialchars($dep->employee_name ?? ''); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
</div>

<!-- Card: Nouveau dépôt -->
<div class="md-card" style="padding: 0.6em 0.8em;">
    <div class="md-card-title" style="font-size: 0.8em; margin-bottom: 0.5em;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
        <?php echo $this->lang->line('cashtills_bank_deposit') ?: 'D&eacute;p&ocirc;t bancaire'; ?>
    </div>

    <!-- Date row -->
    <div class="bk-form-row" style="margin-bottom: 0.6em;">
        <div class="bk-form-group">
            <label>Jour</label>
            <input type="text" name="day" id="day" class="bk-date-input" maxlength="2"
                   value="<?php echo htmlspecialchars($cash_day); ?>">
        </div>
        <span class="bk-date-sep">/</span>
        <div class="bk-form-group">
            <label>Mois</label>
            <input type="text" name="month" id="month" class="bk-date-input" maxlength="2"
                   value="<?php echo htmlspecialchars($cash_month); ?>">
        </div>
        <span class="bk-date-sep">/</span>
        <div class="bk-form-group">
            <label>Ann&eacute;e</label>
            <input type="text" name="year" id="year" class="bk-year-input" maxlength="4"
                   value="<?php echo htmlspecialchars($cash_year); ?>">
        </div>
        <div class="bk-form-group" style="flex: 1;">
            <label><?php echo $this->lang->line('cashtills_reference') ?: 'R&eacute;f&eacute;rence'; ?></label>
            <input type="text" name="reference" id="reference" class="bk-ref-input required" style="width: 100%;"
                   placeholder="N&deg; bordereau"
                   value="<?php echo htmlspecialchars($reference); ?>">
        </div>
    </div>

    <!-- Amount row -->
    <div class="bk-form-row">
        <div class="bk-form-group" style="flex: 1;">
            <label><?php echo $this->lang->line('cashtills_amount') ?: 'Montant'; ?></label>
            <input type="number" name="deposit_amount" id="deposit_amount" class="bk-amount-input required" style="width: 100%;"
                   step="0.01" min="0" placeholder="0,00"
                   value="<?php echo $deposit_amount != 0 ? htmlspecialchars($deposit_amount) : ''; ?>">
        </div>
    </div>
</div>

<?php echo form_close(); ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.5em 1em;">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
            <?php echo $this->lang->line('common_reset') ?: 'Annuler'; ?>
        </a>
        <button type="submit" form="bank_form" name="submit" id="submit" class="md-btn md-btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <?php echo $submit_label; ?>
        </button>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<script>
function toggleBankHistory() {
    var section = document.getElementById('bk_history_section');
    var btn = document.getElementById('bk_history_btn');
    var isOpen = section.classList.toggle('open');
    btn.classList.toggle('open', isOpen);
}
$(document).ready(function() {
    var ref = document.getElementById('reference');
    if (ref && !ref.value) { ref.focus(); }
    else {
        var amt = document.getElementById('deposit_amount');
        if (amt) amt.focus();
    }
});
</script>
