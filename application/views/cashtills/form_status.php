<?php
$trans_today  = $_SESSION['cash_trans_today'] ?? [];
$cash_day     = $_SESSION['cash_day'] ?? date('d');
$cash_month   = $_SESSION['cash_month'] ?? date('m');
$cash_year    = $_SESSION['cash_year'] ?? date('Y');
$date_display = $cash_day.'/'.$cash_month.'/'.$cash_year;
$submit_label = $_SESSION['submit'] ?? $this->lang->line('common_submit');
?>

<style>
/* --- Status modal --- */
.st-filter-row {
    display: flex; align-items: center; gap: 0.5em; flex-wrap: wrap;
    padding: 0.4em 0.6em; margin-bottom: 0.4em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px;
}
.st-filter-label {
    font-size: 0.75em; font-weight: 600; text-transform: uppercase;
    color: var(--text-secondary, #64748b);
}
.st-filter-input {
    width: 110px; text-align: center; padding: 0.3em 0.5em;
    font-size: 0.92em; font-weight: 600;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    cursor: pointer;
}
.st-filter-input:focus {
    outline: none; border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 2px rgba(37,99,235,0.1);
}
.st-filter-btn {
    padding: 0.3em 0.7em; font-size: 0.82em; font-weight: 600;
    color: #fff; background: var(--primary, #2563eb);
    border: none; border-radius: 4px; cursor: pointer; transition: background 0.15s;
}
.st-filter-btn:hover { background: #1d4ed8; }

/* Transactions table */
.st-trans-table { width: 100%; border-collapse: collapse; }
.st-trans-table th {
    font-size: 0.68em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); padding: 0.3em 0.4em;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
}
.st-trans-table td {
    padding: 0.25em 0.4em; font-size: 0.88em;
    border-bottom: 1px solid color-mix(in srgb, var(--border-color, #e2e8f0) 50%, transparent);
    color: var(--text-primary, #1e293b); font-variant-numeric: tabular-nums;
}
.st-trans-table tr:last-child td { border-bottom: none; }
.st-trans-action { text-align: center; color: var(--text-secondary, #94a3b8); width: 24px; }
.st-trans-amount { text-align: right; font-weight: 600; }
.st-trans-ref { color: var(--text-secondary, #64748b); font-size: 0.9em; }
.st-trans-emp { color: var(--text-secondary, #94a3b8); font-size: 0.9em; }
.st-trans-time { color: var(--text-secondary, #94a3b8); font-size: 0.85em; white-space: nowrap; }

/* Empty state */
.st-empty {
    text-align: center; padding: 1.5em; font-size: 0.88em; font-style: italic;
    color: var(--text-secondary, #94a3b8);
}
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 700px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(100,116,139,0.12); color: #475569; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;"><?php echo $this->lang->line('cashtills_status') ?: 'Situation'; ?></h2>
            <span class="md-modal-ref"><?php echo $date_display; ?></span>
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
<div class="md-modal-body" style="padding: 0.4em 0.8em; max-height: 85vh; overflow-y: auto;">

<!-- Date filter -->
<?php echo form_open($_SESSION['controller_name'].'/status', array('id' => 'status_form')); ?>
<div class="st-filter-row">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--text-secondary, #64748b);">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
    </svg>
    <span class="st-filter-label"><?php echo $this->lang->line('cashtills_status') ?: 'Situation'; ?> <?php echo $this->lang->line('common_for') ?: 'du'; ?></span>
    <input type="text" name="date" id="date" class="st-filter-input"
           placeholder="JJ/MM/AAAA"
           value="<?php echo $date_display; ?>" readonly>
    <button type="submit" class="st-filter-btn">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -1px;">
            <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        Afficher
    </button>
</div>
<?php echo form_close(); ?>

<!-- Transactions table -->
<?php if (!empty($trans_today)) { ?>
<div class="md-card" style="padding: 0.4em 0.6em; margin-top: 0.4em;">
    <table class="st-trans-table">
        <thead>
            <tr>
                <th style="text-align: left;"><?php echo $this->lang->line('cashtills_transaction') ?: 'Op&eacute;ration'; ?></th>
                <th style="text-align: left;"><?php echo $this->lang->line('cashtills_reference') ?: 'R&eacute;f&eacute;rence'; ?></th>
                <th></th>
                <th style="text-align: right;"><?php echo $this->lang->line('cashtills_amount') ?: 'Montant'; ?></th>
                <th style="text-align: left;"><?php echo $this->lang->line('employees_employee') ?: 'Employ&eacute;'; ?></th>
                <th style="text-align: left;"><?php echo $this->lang->line('common_timestamp') ?: 'Heure'; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($trans_today as $row) { ?>
            <tr>
                <td><?php echo $row->cash_transaction; ?></td>
                <td class="st-trans-ref"><?php echo $row->cash_bank_deposit_reference ?? ''; ?></td>
                <td class="st-trans-action"><?php echo $row->cash_action; ?></td>
                <td class="st-trans-amount"><?php echo number_format((float)($row->cash_amount ?? 0), 2, ',', ' '); ?></td>
                <td class="st-trans-emp"><?php echo $row->user_name ?? ''; ?></td>
                <td class="st-trans-time"><?php echo $row->timestamp ?? ''; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php } else { ?>
<div class="st-empty">Aucune op&eacute;ration pour cette date.</div>
<?php } ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.35em 0.8em;">
    <div class="md-modal-footer-left">
        <span style="font-size: 0.75em; color: var(--text-secondary, #94a3b8);">
            <?php echo count($trans_today); ?> op&eacute;ration<?php echo count($trans_today) > 1 ? 's' : ''; ?>
        </span>
    </div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
            Fermer
        </a>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<!-- jQuery UI datepicker -->
<script src="<?php echo base_url(); ?>jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="<?php echo base_url(); ?>jquery-ui-1.12.1.custom/my_calendar.js"></script>
