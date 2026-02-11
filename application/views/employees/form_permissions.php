<?php
$is_del   = (($_SESSION['del'] ?? 0) == 1);
$is_undel = (($_SESSION['undel'] ?? 0) == 1);
$info     = $_SESSION['transaction_info'];
$modules  = $_SESSION['all_modules'] ?? array();

$fn = $info->first_name ?? '';
$ln = $info->last_name ?? '';
$full_name = ucfirst(strtolower($fn)) . ' ' . strtoupper($ln);
$initials  = strtoupper(mb_substr($fn, 0, 1) . mb_substr($ln, 0, 1));
?>

<style>
/* Permissions grid */
.perm-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.3em 1em;
}
.perm-item {
    display: flex; align-items: center; gap: 0.5em;
    padding: 0.4em 0.6em;
    border-radius: 6px;
    transition: background 0.12s;
    cursor: pointer;
}
.perm-item:hover { background: rgba(37,99,235,0.06); }
.perm-item input[type="checkbox"] {
    width: 16px; height: 16px; accent-color: var(--primary, #2563eb);
    cursor: pointer; flex-shrink: 0;
}
.perm-item label {
    font-size: 0.85em; color: var(--text-primary, #1e293b);
    cursor: pointer; user-select: none;
}
.perm-actions {
    display: flex; gap: 0.5em; margin-bottom: 0.5em;
}
.perm-actions button {
    background: none; border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 5px; padding: 3px 10px; font-size: 0.75em;
    color: var(--text-secondary, #64748b); cursor: pointer;
    transition: all 0.12s;
}
.perm-actions button:hover {
    background: var(--primary, #2563eb); color: #fff;
    border-color: var(--primary, #2563eb);
}
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 520px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.7em 1em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: var(--secondary, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1em; font-weight: 700; border-radius: 50%; width: 40px; height: 40px;">
            <?php echo $initials; ?>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1.05em;">Permissions</h2>
            <span class="md-modal-ref" style="font-size: 0.8em; color: var(--text-secondary, #64748b);"><?php echo $full_name; ?></span>
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
<div class="md-modal-body" style="padding: 0.8em 1.2em;">

<?php if (!$is_del) { ?>
<?php if (!$is_undel) { echo form_open($_SESSION['controller_name'].'/save_permissions/', array('id' => 'perm_form')); } ?>

<div class="md-card" style="padding: 0.7em 0.9em;">
    <div class="md-card-title" style="font-size: 0.82em;">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
        </svg>
        <?php echo $this->lang->line('employees_permission_info') ?: 'Acc&egrave;s aux modules'; ?>
    </div>

    <!-- Select all / none -->
    <div class="perm-actions">
        <button type="button" id="perm-select-all">Tout cocher</button>
        <button type="button" id="perm-select-none">Tout d&eacute;cocher</button>
    </div>

    <div class="perm-grid">
    <?php
    foreach ($modules as $module) {
        $mod_id   = $module['module_id'];
        $mod_name = $this->lang->line('modules_'.$module['module_name']) ?: $module['module_name'];
        $checked  = $this->Employee->has_permission($mod_id, $info->person_id ?? 0);
        $chk_id   = 'perm_' . $mod_id;
        ?>
        <div class="perm-item" onclick="var c=this.querySelector('input');c.checked=!c.checked;">
            <?php echo form_checkbox(array(
                'name'    => 'permissions[]',
                'id'      => $chk_id,
                'value'   => $mod_id,
                'checked' => $checked,
                'onclick' => 'event.stopPropagation();'
            )); ?>
            <label for="<?php echo $chk_id; ?>" onclick="event.stopPropagation();"><?php echo $mod_name; ?></label>
        </div>
        <?php
    }
    ?>
    </div>
</div>

<?php if (!$is_undel) { echo form_close(); } ?>
<?php } ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.5em 1em;">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
            <?php echo $this->lang->line('common_reset') ?: 'Annuler'; ?>
        </a>
        <?php if (!$is_undel && !$is_del) { ?>
        <button type="submit" form="perm_form" name="submit" id="submit" class="md-btn md-btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <?php echo $this->lang->line('common_submit') ?: 'Enregistrer'; ?>
        </button>
        <?php } ?>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<script>
$(document).ready(function() {
    $('#perm-select-all').on('click', function(e) {
        e.preventDefault();
        $('.perm-grid input[type="checkbox"]').prop('checked', true);
    });
    $('#perm-select-none').on('click', function(e) {
        e.preventDefault();
        $('.perm-grid input[type="checkbox"]').prop('checked', false);
    });
});
</script>
