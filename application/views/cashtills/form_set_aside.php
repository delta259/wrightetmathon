<?php
$trans_today    = $_SESSION['cash_trans_today'] ?? [];
$set_aside_amt  = $_SESSION['set_aside_amount'] ?? 0;
$submit_label   = $_SESSION['submit'] ?? $this->lang->line('common_submit');
?>

<style>
/* --- Set aside modal (compact) --- */
.sa-trans-table { width: 100%; border-collapse: collapse; }
.sa-trans-table th {
    font-size: 0.68em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); padding: 0.25em 0.4em;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
}
.sa-trans-table td {
    padding: 0.2em 0.4em; font-size: 0.92em;
    border-bottom: 1px solid color-mix(in srgb, var(--border-color, #e2e8f0) 50%, transparent);
    color: var(--text-primary, #1e293b); font-variant-numeric: tabular-nums;
}
.sa-trans-table tr:last-child td { border-bottom: none; }
.sa-trans-action { text-align: center; color: var(--text-secondary, #94a3b8); width: 24px; }
.sa-trans-amount { text-align: right; font-weight: 600; }

/* Input row */
.sa-input-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.4em 0.6em; margin-top: 0.3em;
    background: rgba(245,158,11,0.06); border: 1px solid rgba(245,158,11,0.2);
    border-radius: 6px;
}
.sa-input-label { font-size: 0.88em; font-weight: 700; color: var(--text-primary, #1e293b); }
.sa-input-field {
    width: 110px; text-align: right; padding: 0.3em 0.5em;
    font-size: 1.05em; font-weight: 700; font-variant-numeric: tabular-nums;
    border: 2px solid #d97706; border-radius: 6px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
}
.sa-input-field:focus {
    outline: none; border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,0.15);
}

/* Discreet success message */
.sa-msg-discreet {
    display: flex; align-items: center; gap: 0.4em;
    padding: 0.3em 0.6em; margin-bottom: 0.4em;
    background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.2);
    border-radius: 6px; font-size: 0.8em; font-weight: 500;
    color: #16a34a;
}
.sa-msg-discreet svg { flex-shrink: 0; }
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 580px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(245,158,11,0.12); color: #d97706; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;"><?php echo $this->lang->line('cashtills_set_aside') ?: 'Versement'; ?></h2>
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
<?php
if (isset($_SESSION['error_code']) && $_SESSION['error_code'] !== '' && isset($_SESSION['G']->messages[$_SESSION['error_code']])) {
    $message = $_SESSION['G']->messages[$_SESSION['error_code']];
    if (isset($message[1]) && isset($message[2])) {
        if ($message[1] === 'success_message') {
            echo '<div style="padding: 0 0.8em;"><div class="sa-msg-discreet"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>'.$message[2].'</div></div>';
        } else {
            echo '<div style="padding: 0 0.8em;"><div class="'.$message[1].'">'.$message[2].'</div></div>';
        }
    }
    unset($_SESSION['error_code']);
    $_SESSION['substitution_parms'] = array();
}
?>

<!-- Body -->
<div class="md-modal-body" style="padding: 0.4em 0.8em; max-height: 88vh; overflow-y: auto;">

<?php echo form_open($_SESSION['controller_name'].'/save_set_aside/', array('id' => 'sa_form')); ?>

<!-- Today's transactions -->
<?php if (!empty($trans_today)) { ?>
<div class="md-card" style="padding: 0.4em 0.6em; margin-bottom: 0.4em;">
    <div class="md-card-title" style="font-size: 0.75em; margin-bottom: 0.25em;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
        </svg>
        Op&eacute;rations du jour
    </div>
    <table class="sa-trans-table">
        <thead>
            <tr>
                <th style="text-align: left;">Op&eacute;ration</th>
                <th></th>
                <th style="text-align: right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($trans_today as $row) { ?>
            <tr>
                <td><?php echo $row->cash_transaction; ?></td>
                <td class="sa-trans-action"><?php echo $row->cash_action; ?></td>
                <td class="sa-trans-amount"><?php echo number_format((float)($row->cash_amount ?? 0), 2, ',', ' '); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php } ?>

<!-- Set aside input -->
<div class="sa-input-row">
    <span class="sa-input-label">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -2px; color: #d97706;">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
        <?php echo $this->lang->line('cashtills_set_aside') ?: 'Versement'; ?>
    </span>
    <input type="number" name="set_aside_amount" id="set_aside_amount"
           class="sa-input-field" step="0.01" min="0"
           value="<?php echo $set_aside_amt; ?>">
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
        <button type="submit" form="sa_form" name="submit" id="submit" class="md-btn md-btn-primary" style="padding: 0.35em 0.8em; font-size: 0.85em; background: #d97706;">
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
$(document).ready(function() {
    var input = document.getElementById('set_aside_amount');
    if (input) { input.focus(); input.select(); }
});
</script>
