<?php
$_SESSION['origin'] = "CA";
$default_supplier = $this->config->item('default_supplier_id');
$default_historique = $this->config->item('historique');
$default_prevision = $this->config->item('nbre_jour_prevision_stock');

// Handle error code forwarding
if (($_SESSION['error_code_0'] ?? 0) == 1) {
    $_SESSION['error_code'] = '07320';
    unset($_SESSION['error_code_0']);
}
?>

<style>
/* --- Auto PO modal (apo-) --- */
.apo-form-group { margin-bottom: 0.5em; }
.apo-form-label {
    display: block; font-size: 0.72em; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.03em; color: var(--text-secondary, #64748b); margin-bottom: 0.25em;
}
.apo-select {
    width: 100%; padding: 0.4em 0.5em; font-size: 0.95em; font-weight: 600;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b); cursor: pointer;
}
.apo-select:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }

/* Parameters card */
.apo-params-card {
    display: flex; flex-direction: column; gap: 0.5em;
    padding: 0.5em 0.7em; margin-bottom: 0.5em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px;
}
.apo-param-row {
    display: flex; align-items: center; gap: 0.5em; flex-wrap: wrap;
}
.apo-param-label { font-size: 0.88em; font-weight: 500; color: var(--text-primary, #1e293b); flex: 1; min-width: 150px; }
.apo-param-input-group { display: flex; align-items: center; gap: 0.3em; }
.apo-param-input {
    width: 70px; text-align: right; padding: 0.3em 0.4em; font-size: 0.95em; font-weight: 600;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    font-variant-numeric: tabular-nums;
}
.apo-param-input:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
.apo-param-input.apo-required { border-color: var(--danger, #ef4444); }
.apo-param-input.apo-required:focus { box-shadow: 0 0 0 2px rgba(239,68,68,0.1); }
.apo-param-unit { font-size: 0.82em; color: var(--text-secondary, #64748b); font-weight: 500; }

/* Methodology card */
.apo-metho-card {
    padding: 0.5em 0.7em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px;
}
.apo-metho-title {
    font-size: 0.72em; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.03em; color: var(--text-secondary, #64748b); margin-bottom: 0.4em;
    display: flex; align-items: center; gap: 0.3em;
}
.apo-radio-group { display: flex; flex-direction: column; gap: 0.35em; }
.apo-radio-option {
    display: flex; align-items: center; gap: 0.5em; padding: 0.35em 0.5em;
    border-radius: 6px; cursor: pointer; transition: background 0.1s;
}
.apo-radio-option:hover { background: color-mix(in srgb, var(--primary, #2563eb) 4%, transparent); }
.apo-radio-option input[type="radio"] {
    accent-color: var(--primary, #2563eb); width: 16px; height: 16px; margin: 0; cursor: pointer;
}
.apo-radio-label { font-size: 0.88em; font-weight: 500; color: var(--text-primary, #1e293b); cursor: pointer; }
.apo-date-inputs {
    display: inline-flex; align-items: center; gap: 0.3em; margin-left: 0.3em;
}
.apo-date-input {
    padding: 0.2em 0.4em; font-size: 0.85em;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
}
.apo-date-input:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
.apo-date-sep { font-size: 0.85em; color: var(--text-secondary, #94a3b8); font-weight: 500; }

</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 560px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(34,197,94,0.12); color: #16a34a; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="12" y1="18" x2="12" y2="12"></line>
                <line x1="9" y1="15" x2="15" y2="15"></line>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;"><?php echo $_SESSION['title']; ?></h2>
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

<?php echo form_open("reports/inventory_low_validation_by_date", array('id' => 'apo_form')); ?>

<!-- Supplier -->
<div class="apo-form-group">
    <label class="apo-form-label">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -1px;">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>
        </svg>
        <?php echo $this->lang->line('recvs_supplier'); ?>
    </label>
    <?php echo form_dropdown(
        'supplier_id',
        $_SESSION['G']->supplier_pick_list,
        $default_supplier,
        'class="apo-select" id="supplier_id"'
    ); ?>
</div>

<!-- Parameters -->
<div class="apo-params-card">
    <div class="apo-param-row">
        <span class="apo-param-label"><?php echo $this->lang->line('recvs_ventes_sur_historique_correct'); ?></span>
        <div class="apo-param-input-group">
            <input type="number" id="historique_correct" name="historique_correct" min="1"
                   value="<?php echo $default_historique; ?>" class="apo-param-input">
            <span class="apo-param-unit"><?php echo $this->lang->line('recvs_jours'); ?></span>
        </div>
    </div>
    <div class="apo-param-row">
        <span class="apo-param-label"><?php echo $this->lang->line('recvs_commande_pour_nbre_jour_prevision_stock_correct'); ?></span>
        <div class="apo-param-input-group">
            <input type="number" id="nbre_jour_prevision_stock_correct" name="nbre_jour_prevision_stock_correct" min="1"
                   value="<?php echo $default_prevision; ?>" class="apo-param-input apo-required">
            <span class="apo-param-unit"><?php echo $this->lang->line('recvs_jours'); ?></span>
        </div>
    </div>
</div>

<!-- Methodology -->
<div class="apo-metho-card">
    <div class="apo-metho-title">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
            <polyline points="2 17 12 22 22 17"></polyline>
            <polyline points="2 12 12 17 22 12"></polyline>
        </svg>
        <?php echo $this->lang->line('Methodologie'); ?>
    </div>
    <div class="apo-radio-group">
        <!-- Stock / Ventes -->
        <label class="apo-radio-option">
            <input type="radio" name="metho" id="metho_by_stock" value="metho_by_stock" checked>
            <span class="apo-radio-label"><?php echo $this->lang->line('metho_by_stock'); ?></span>
        </label>
        <!-- Ventes entre dates -->
        <label class="apo-radio-option">
            <input type="radio" name="metho" id="metho_by_sales" value="metho_by_sales">
            <span class="apo-radio-label"><?php echo $this->lang->line('metho_by_sales'); ?></span>
            <div class="apo-date-inputs">
                <input type="date" name="date_start" value="<?php echo date('Y-m-d'); ?>" id="date_start" class="apo-date-input">
                <span class="apo-date-sep">&rarr;</span>
                <input type="date" name="date_end" value="<?php echo date('Y-m-d'); ?>" id="date_end" class="apo-date-input">
            </div>
        </label>
        <!-- Stock uniquement -->
        <label class="apo-radio-option">
            <input type="radio" name="metho" id="metho_by_only_stock" value="metho_by_only_stock">
            <span class="apo-radio-label"><?php echo $this->lang->line('recvs_metho_by_only_stock'); ?></span>
        </label>
    </div>
</div>

<?php echo form_close(); ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.4em 0.8em;">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
            <?php echo $this->lang->line('common_reset') ?: 'Annuler'; ?>
        </a>
        <button type="submit" form="apo_form" name="generate_report" id="generate_report" class="md-btn md-btn-primary apo-submit-btn" style="padding: 0.35em 0.8em; font-size: 0.85em; background: #16a34a; border-color: #16a34a;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <?php echo $this->lang->line('common_submit'); ?>
        </button>
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

    // Auto-select metho_by_sales when clicking date inputs
    $('#date_start, #date_end').focus(function() {
        $('#metho_by_sales').prop('checked', true);
    });
});
</script>
