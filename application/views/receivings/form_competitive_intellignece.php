<?php
$ci_data = $_SESSION['transaction_info_competitive_intelligence'] ?? new stdClass();
$item_id = $ci_data->item_id ?? 0;
$item_number = $ci_data->item_number ?? '';
$item_name = $ci_data->name ?? '';
$price_pos = $ci_data->price_pos ?? '';
?>

<style>
/* --- Competitive Intelligence modal (fci-) --- */
.fci-product-card {
    display: flex; align-items: center; gap: 0.6em; padding: 0.5em 0.7em; margin-bottom: 0.5em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px;
}
.fci-product-icon {
    width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
    background: color-mix(in srgb, var(--primary, #2563eb) 10%, transparent); color: var(--primary, #2563eb); flex-shrink: 0;
}
.fci-product-info { flex: 1; min-width: 0; }
.fci-product-name { font-size: 0.92em; font-weight: 600; color: var(--text-primary, #1e293b); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fci-product-ref { font-size: 0.78em; color: var(--text-secondary, #64748b); font-family: monospace; }

.fci-form-card {
    padding: 0.5em 0.7em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px;
}
.fci-form-group { margin-bottom: 0.5em; }
.fci-form-group:last-child { margin-bottom: 0; }
.fci-form-label {
    display: block; font-size: 0.72em; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.03em; color: var(--text-secondary, #64748b); margin-bottom: 0.25em;
}
.fci-form-input {
    width: 100%; padding: 0.4em 0.5em; font-size: 0.95em; font-weight: 600;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    font-variant-numeric: tabular-nums;
}
.fci-form-input:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
.fci-form-input.readonly { background: var(--bg-card, #f8fafc); color: var(--text-secondary, #64748b); cursor: default; }
.fci-form-textarea {
    width: 100%; padding: 0.4em 0.5em; font-size: 0.92em; font-weight: 500;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    resize: vertical; min-height: 80px; font-family: inherit;
}
.fci-form-textarea:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 520px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(245,158,11,0.12); color: #d97706; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;">Veille Concurrentielle</h2>
            <span class="md-modal-ref"><?php echo htmlspecialchars($item_number); ?></span>
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
<div class="md-modal-body" style="padding: 0.5em 0.8em;">

<?php echo form_open("items/competitive_intelligence_send/$item_id", array('id' => 'fci_form')); ?>

<!-- Product identity card -->
<div class="fci-product-card">
    <div class="fci-product-icon">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
            <line x1="12" y1="22.08" x2="12" y2="12"></line>
        </svg>
    </div>
    <div class="fci-product-info">
        <div class="fci-product-name"><?php echo htmlspecialchars($item_name); ?></div>
        <div class="fci-product-ref"><?php echo htmlspecialchars($item_number); ?></div>
    </div>
</div>

<!-- Hidden fields for item data -->
<input type="hidden" name="item_number" id="item_number" value="<?php echo htmlspecialchars($item_number); ?>">
<input type="hidden" name="name" id="name" value="<?php echo htmlspecialchars($item_name); ?>">

<!-- Price & competitive info -->
<div class="fci-form-card">
    <div class="fci-form-group">
        <label class="fci-form-label" for="competitive_intelligence_price_pos">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -1px;">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <?php echo $this->lang->line('common_competitive_intelligence_price_pos'); ?>
        </label>
        <input type="text" name="competitive_intelligence_price_pos" id="competitive_intelligence_price_pos"
               class="fci-form-input readonly" value="<?php echo htmlspecialchars($price_pos); ?>" readonly>
    </div>
    <div class="fci-form-group">
        <label class="fci-form-label" for="competitive_intelligence_price_concurrent">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -1px;">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <?php echo $this->lang->line('common_competitive_intelligence_price_concurrent'); ?>
        </label>
        <textarea name="competitive_intelligence_price_concurrent" id="competitive_intelligence_price_concurrent"
                  class="fci-form-textarea" rows="4" placeholder="Informations sur la concurrence"></textarea>
    </div>
</div>

<?php echo form_close(); ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.4em 0.8em;">
    <div class="md-modal-footer-left">
        <span class="md-required-note">
            <?php echo $this->lang->line('common_fields_required_message'); ?>
        </span>
    </div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
            <?php echo $this->lang->line('common_cancel') ?: 'Annuler'; ?>
        </a>
        <button type="submit" form="fci_form" name="submit" id="submit" class="md-btn md-btn-primary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
            <?php echo $this->lang->line('common_send'); ?>
        </button>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->
