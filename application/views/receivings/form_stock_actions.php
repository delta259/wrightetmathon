<?php
$submit_label = $this->lang->line('common_submit');
$_SESSION['origin'] = "SA";
?>

<style>
/* --- Stock actions modal (rsa-) --- */
.rsa-action-list { display: flex; flex-direction: column; gap: 0.5em; }
.rsa-action-group {
    padding: 0.5em 0.7em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
}
.rsa-action-group label {
    display: block; font-size: 0.72em; font-weight: 600; text-transform: uppercase;
    color: var(--text-secondary, #64748b); margin-bottom: 0.3em;
}
.rsa-select {
    width: 100%; padding: 0.45em 0.6em; font-size: 1em; font-weight: 600;
    border: 2px solid var(--primary, #2563eb); border-radius: 6px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    cursor: pointer; appearance: auto;
}
.rsa-select:focus {
    outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
}
.rsa-remote-btn {
    display: flex; align-items: center; gap: 0.5em; justify-content: center;
    width: 100%; padding: 0.5em 0.8em; font-size: 0.85em; font-weight: 600;
    color: var(--text-primary, #1e293b); background: var(--bg-card, #f8fafc);
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px;
    text-decoration: none; cursor: pointer; transition: all 0.15s;
}
.rsa-remote-btn:hover {
    border-color: var(--primary, #2563eb);
    box-shadow: 0 2px 8px rgba(37,99,235,0.1);
    background: var(--bg-container, #fff);
}
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 480px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.5em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(37,99,235,0.12); color: #2563eb; width: 34px; height: 34px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
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

<!-- Body -->
<div class="md-modal-body" style="padding: 0.6em 0.8em;">

<?php echo form_open('receivings/stock_actions_2', array('id' => 'rsa_form')); ?>

<div class="rsa-action-list">
    <div class="rsa-action-group">
        <label>
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -1px;">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
            <?php echo $this->lang->line('receivings_stock_action_picklist') ?: 'Action stock'; ?>
        </label>
        <?php echo form_dropdown(
            'stock_action_id',
            $_SESSION['G']->stock_actions_pick_list,
            $_SESSION['G']->stock_actions_pick_list[0],
            'class="rsa-select" id="stock_action_id"'
        ); ?>
    </div>

    <a href="<?php echo site_url('receivings/load_stock_dosponible_centrale'); ?>" class="rsa-remote-btn">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="23 4 23 10 17 10"></polyline>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
        </svg>
        <?php echo $this->lang->line('common_update_remote_stock') ?: 'MAJ stock distant'; ?>
    </a>
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
        <button type="submit" form="rsa_form" name="submit" id="submit" class="md-btn md-btn-primary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
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
$('#rsa_form').submit(function() {
    $('.md-modal-overlay').hide();
});
</script>
