<?php
$_SESSION['origin'] = "CA";

// Handle error code forwarding
if (($_SESSION['error_code_0'] ?? 0) == 1) {
    $_SESSION['error_code'] = '07320';
    unset($_SESSION['error_code_0']);
}

$n_up = $_SESSION['n_up'] ?? array('total' => 0, 'update' => 0, 'pb_line' => array());
$has_errors = false;
?>

<style>
/* --- Stock Inventory Verify modal (fsv-) --- */
.fsv-stats-card {
    padding: 0.6em 0.8em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px;
}
.fsv-stat-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.35em 0; border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.fsv-stat-row:last-child { border-bottom: none; }
.fsv-stat-label { font-size: 0.88em; font-weight: 500; color: var(--text-primary, #1e293b); }
.fsv-stat-value {
    font-size: 0.95em; font-weight: 700; color: var(--primary, #2563eb);
    font-variant-numeric: tabular-nums;
}
.fsv-errors-card {
    margin-top: 0.5em; padding: 0.5em 0.8em;
    background: color-mix(in srgb, var(--danger, #ef4444) 6%, transparent);
    border: 1px solid color-mix(in srgb, var(--danger, #ef4444) 25%, transparent); border-radius: 8px;
}
.fsv-errors-title {
    font-size: 0.78em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--danger, #ef4444); margin-bottom: 0.3em;
    display: flex; align-items: center; gap: 0.3em;
}
.fsv-error-line {
    font-size: 0.85em; color: var(--danger, #ef4444); padding: 0.15em 0;
}

</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 520px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(34,197,94,0.12); color: #16a34a; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;"><?php echo $_SESSION['title']; ?></h2>
            <span class="md-modal-ref">V&eacute;rification du fichier</span>
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
<?php if (isset($error)) { echo "<div style='padding:0 0.8em;'><div class='error_message'>".$error."</div></div>"; } ?>

<!-- Body -->
<div class="md-modal-body" style="padding: 0.5em 0.8em;">

<?php echo form_open_multipart("items/importation", array('id' => 'fsv_form')); ?>

<!-- Stats -->
<div class="fsv-stats-card">
    <div class="fsv-stat-row">
        <span class="fsv-stat-label">
            <?php echo $this->lang->line('common_number_items').' '.$this->lang->line('common_number_items_in_file'); ?>
        </span>
        <span class="fsv-stat-value"><?php echo $n_up['total']; ?></span>
    </div>
    <div class="fsv-stat-row">
        <span class="fsv-stat-label">
            <?php echo $this->lang->line('common_number_items').' '.$this->lang->line('common_number_items_who_update'); ?>
        </span>
        <span class="fsv-stat-value"><?php echo $n_up['update']; ?></span>
    </div>
</div>

<!-- Errors (if any) -->
<?php if (!empty($n_up['pb_line'])) { $has_errors = true; ?>
<div class="fsv-errors-card">
    <div class="fsv-errors-title">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        Erreurs
    </div>
    <?php foreach ($n_up['pb_line'] as $line) { ?>
        <div class="fsv-error-line">
            <?php echo $this->lang->line('common_error').' '.$this->lang->line('common_line').': '.$line; ?>
        </div>
    <?php } ?>
</div>
<?php } ?>

<?php echo form_close(); ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.4em 0.8em;">
    <div class="md-modal-footer-left">
        <?php if (!$has_errors) { ?>
        <span style="font-size: 0.75em; color: var(--success, #22c55e); font-weight: 600;">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -1px;">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Fichier valide
        </span>
        <?php } else { ?>
        <span style="font-size: 0.75em; color: var(--danger, #ef4444); font-weight: 600;">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -1px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            Erreurs d&eacute;tect&eacute;es
        </span>
        <?php } ?>
    </div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
            <?php echo $this->lang->line('common_reset') ?: 'Annuler'; ?>
        </a>
        <?php if (!$has_errors) { ?>
        <button type="submit" form="fsv_form" name="generate_report" id="generate_report" class="md-btn md-btn-primary" style="padding: 0.35em 0.8em; font-size: 0.85em; background: #16a34a; border-color: #16a34a;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <?php echo $this->lang->line('common_submit'); ?>
        </button>
        <?php } ?>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<script>
$(document).ready(function() {
    // Show page loading overlay on submit
    $('#generate_report').click(function() {
        if (typeof rcvShowLoading === 'function') rcvShowLoading();
    });
});
</script>
