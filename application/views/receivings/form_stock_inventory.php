<?php
$_SESSION['origin'] = "CA";

// Handle error code forwarding
if (($_SESSION['error_code_0'] ?? 0) == 1) {
    $_SESSION['error_code'] = '07320';
    unset($_SESSION['error_code_0']);
}
?>

<style>
/* --- Stock Inventory Import modal (fsi-) --- */
.fsi-upload-card {
    padding: 0.6em 0.8em; margin-bottom: 0.5em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px;
}
.fsi-upload-zone {
    display: flex; flex-direction: column; align-items: center; gap: 0.5em;
    padding: 1.2em 1em; border: 2px dashed var(--border-color, #e2e8f0); border-radius: 8px;
    background: var(--bg-container, #fff); cursor: pointer; transition: border-color 0.15s, background 0.15s;
}
.fsi-upload-zone:hover { border-color: var(--primary, #2563eb); background: color-mix(in srgb, var(--primary, #2563eb) 3%, transparent); }
.fsi-upload-icon { color: var(--text-secondary, #94a3b8); }
.fsi-upload-text { font-size: 0.88em; color: var(--text-secondary, #64748b); font-weight: 500; }
.fsi-upload-text strong { color: var(--primary, #2563eb); }
.fsi-file-input { display: none; }
.fsi-file-name {
    display: none; align-items: center; gap: 0.4em; padding: 0.3em 0.6em;
    background: color-mix(in srgb, var(--primary, #2563eb) 8%, transparent);
    border-radius: 6px; font-size: 0.82em; color: var(--primary, #2563eb); font-weight: 600;
}
.fsi-file-name.active { display: inline-flex; }

/* Option card */
.fsi-option-card {
    padding: 0.5em 0.8em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px;
}
.fsi-option-label { font-size: 0.88em; font-weight: 500; color: var(--text-primary, #1e293b); margin-bottom: 0.3em; }
.fsi-radio-group { display: flex; gap: 0.8em; }
.fsi-radio-option {
    display: flex; align-items: center; gap: 0.4em; padding: 0.3em 0.6em;
    border-radius: 6px; cursor: pointer; transition: background 0.1s;
}
.fsi-radio-option:hover { background: color-mix(in srgb, var(--primary, #2563eb) 4%, transparent); }
.fsi-radio-option input[type="radio"] { accent-color: var(--primary, #2563eb); width: 16px; height: 16px; margin: 0; cursor: pointer; }
.fsi-radio-label { font-size: 0.88em; font-weight: 500; color: var(--text-primary, #1e293b); cursor: pointer; }

</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 520px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(37,99,235,0.12); color: #2563eb; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;"><?php echo $_SESSION['title']; ?></h2>
            <span class="md-modal-ref">Import fichier inventaire</span>
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

<?php echo form_open_multipart("items/verification_for_importation", array('id' => 'fsi_form')); ?>

<!-- File Upload -->
<div class="fsi-upload-card">
    <label class="fsi-upload-zone" for="userfile" id="fsi_drop_zone">
        <div class="fsi-upload-icon">
            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="12" y1="18" x2="12" y2="12"></line>
                <line x1="9" y1="15" x2="15" y2="15"></line>
            </svg>
        </div>
        <span class="fsi-upload-text"><?php echo $this->lang->line('common_choose_file'); ?></span>
        <input type="file" name="userfile" id="userfile" class="fsi-file-input">
    </label>
    <div class="fsi-file-name" id="fsi_file_name">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
        </svg>
        <span id="fsi_file_label"></span>
    </div>
</div>

<!-- Option: remove quantities -->
<div class="fsi-option-card">
    <div class="fsi-option-label"><?php echo $this->lang->line('items_stock_import_inventory'); ?></div>
    <div class="fsi-radio-group">
        <label class="fsi-radio-option">
            <input type="radio" name="qauntity_remove" id="qauntity_remove_yes" value="1">
            <span class="fsi-radio-label"><?php echo $this->lang->line('common_yes'); ?></span>
        </label>
        <label class="fsi-radio-option">
            <input type="radio" name="qauntity_remove" id="qauntity_remove_no" value="0" checked>
            <span class="fsi-radio-label"><?php echo $this->lang->line('common_no'); ?></span>
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
        <button type="submit" form="fsi_form" name="generate_report" id="generate_report" class="md-btn md-btn-primary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
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

    // Show selected file name
    $('#userfile').change(function() {
        var fileName = this.files[0] ? this.files[0].name : '';
        if (fileName) {
            $('#fsi_file_label').text(fileName);
            $('#fsi_file_name').addClass('active');
        } else {
            $('#fsi_file_name').removeClass('active');
        }
    });
});
</script>
