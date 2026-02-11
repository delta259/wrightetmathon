<?php
$bank_history = $_SESSION['bank_deposit_history'] ?? [];
$bank_history = is_array($bank_history) ? $bank_history : [];
$hist_year    = $_SESSION['bank_hist_year'] ?? date('Y');
$hist_month   = intval($_SESSION['bank_hist_month'] ?? 0);
$total_deposits = 0;
foreach ($bank_history as $dep) { $total_deposits += (float)($dep->cash_amount ?? 0); }

$months_list = array(
    0 => 'Toute l\'ann&eacute;e',
    1 => 'Janvier', 2 => 'F&eacute;vrier', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Ao&ucirc;t',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'D&eacute;cembre'
);
?>

<style>
/* --- Bank history modal --- */
.bh-filters {
    display: flex; align-items: end; gap: 0.6em; flex-wrap: wrap;
    padding: 0.5em 0.8em; margin-bottom: 0.5em;
    background: var(--bg-card, #f8fafc); border-radius: 8px;
    border: 1px solid var(--border-color, #e2e8f0);
}
.bh-filter-group { display: flex; flex-direction: column; gap: 0.15em; }
.bh-filter-group label {
    font-size: 0.68em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b);
}
.bh-filter-group select, .bh-filter-group input {
    padding: 0.35em 0.5em; font-size: 0.92em;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
}
.bh-filter-group select:focus, .bh-filter-group input:focus {
    outline: none; border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.bh-filter-btn {
    padding: 0.35em 0.8em; font-size: 0.85em; font-weight: 600;
    color: #fff; background: var(--primary, #2563eb);
    border: none; border-radius: 6px; cursor: pointer;
    transition: background 0.15s;
}
.bh-filter-btn:hover { background: #1d4ed8; }

.bh-table { width: 100%; border-collapse: collapse; }
.bh-table th {
    font-size: 0.7em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); padding: 0.35em 0.6em;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
    position: sticky; top: 0; background: var(--bg-container, #fff); z-index: 1;
}
.bh-table td {
    padding: 0.3em 0.6em; font-size: 0.9em;
    border-bottom: 1px solid color-mix(in srgb, var(--border-color, #e2e8f0) 50%, transparent);
    color: var(--text-primary, #1e293b); font-variant-numeric: tabular-nums;
}
.bh-table tr:last-child td { border-bottom: none; }
.bh-table .bh-amount { text-align: right; font-weight: 600; }
.bh-table .bh-ref { color: var(--text-secondary, #64748b); }
.bh-table .bh-emp { color: var(--text-secondary, #94a3b8); font-size: 0.88em; }
.bh-empty {
    text-align: center; padding: 2em; font-size: 0.9em; font-style: italic;
    color: var(--text-secondary, #94a3b8);
}
.bh-total-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.4em 0.6em; margin-top: 0.4em;
    background: var(--primary, #2563eb); border-radius: 6px; color: #fff;
}
.bh-total-label { font-weight: 700; font-size: 0.85em; text-transform: uppercase; }
.bh-total-value { font-size: 1.1em; font-weight: 700; font-variant-numeric: tabular-nums; }
.bh-total-count { font-size: 0.75em; opacity: 0.8; }
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 640px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.5em 1em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(37,99,235,0.12); color: #2563eb;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1.05em;">Historique d&eacute;p&ocirc;ts bancaires</h2>
            <span class="md-modal-ref"><?php echo $hist_year; ?><?php echo $hist_month > 0 ? ' &mdash; '.$months_list[$hist_month] : ''; ?></span>
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

<!-- Body -->
<div class="md-modal-body" style="padding: 0.6em 1em; max-height: 80vh; overflow-y: auto;">

<!-- Filters -->
<?php echo form_open('cashtills/bank_history', array('id' => 'bh_filter_form')); ?>
<div class="bh-filters">
    <div class="bh-filter-group">
        <label>Ann&eacute;e</label>
        <select name="hist_year" id="bh_year">
            <?php for ($y = intval(date('Y')); $y >= intval(date('Y')) - 4; $y--) { ?>
            <option value="<?php echo $y; ?>"<?php echo ($y == $hist_year) ? ' selected' : ''; ?>><?php echo $y; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="bh-filter-group">
        <label>Mois</label>
        <select name="hist_month" id="bh_month">
            <?php foreach ($months_list as $k => $v) { ?>
            <option value="<?php echo $k; ?>"<?php echo ($k == $hist_month) ? ' selected' : ''; ?>><?php echo $v; ?></option>
            <?php } ?>
        </select>
    </div>
    <button type="submit" class="bh-filter-btn">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -2px;">
            <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        Filtrer
    </button>
</div>
<?php echo form_close(); ?>

<!-- Results -->
<?php if (empty($bank_history)) { ?>
    <div class="bh-empty">Aucun d&eacute;p&ocirc;t bancaire pour cette p&eacute;riode.</div>
<?php } else { ?>
<table class="bh-table">
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
            <td><?php echo str_pad($dep->cash_day, 2, '0', STR_PAD_LEFT).'/'.str_pad($dep->cash_month, 2, '0', STR_PAD_LEFT).'/'.$dep->cash_year; ?></td>
            <td class="bh-ref"><?php echo htmlspecialchars($dep->cash_bank_deposit_reference ?? ''); ?></td>
            <td class="bh-amount"><?php echo number_format((float)($dep->cash_amount ?? 0), 2, ',', ' '); ?> &euro;</td>
            <td class="bh-emp"><?php echo htmlspecialchars($dep->employee_name ?? ''); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<!-- Total -->
<div class="bh-total-row">
    <span class="bh-total-label">Total <span class="bh-total-count">(<?php echo count($bank_history); ?> d&eacute;p&ocirc;t<?php echo count($bank_history) > 1 ? 's' : ''; ?>)</span></span>
    <span class="bh-total-value"><?php echo number_format($total_deposits, 2, ',', ' '); ?> &euro;</span>
</div>
<?php } ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.5em 1em;">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
            Fermer
        </a>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->
