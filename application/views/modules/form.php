<?php $this->load->view("partial/header"); ?>

<?php
$is_new  = (($_SESSION['new'] ?? 0) == 1);
$is_del  = (($_SESSION['del'] ?? 0) == 1);
$is_undel = (($_SESSION['undel'] ?? 0) == 1);
$info    = $_SESSION['transaction_info'];
?>

<?php
$_msg_class = '';
if (isset($_SESSION['error_code']) && $_SESSION['error_code'] !== '' && isset($_SESSION['G']->messages[$_SESSION['error_code']])) {
    $_msg_class = $_SESSION['G']->messages[$_SESSION['error_code']][1] ?? '';
}
include('../wrightetmathon/application/views/partial/show_messages.php');
?>

<div style="max-width:700px; margin:20px auto;">
<div class="md-modal" style="position:relative; max-height:none; overflow:visible;">

<!-- ========== HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div style="background:var(--primary,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:8px;flex-shrink:0;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref">#<?php echo htmlspecialchars($info->module_id ?? ''); ?></div>
            <h2 class="md-modal-name" style="font-size:1.1em;">Module</h2>
        </div>
    </div>
    <a href="<?php echo site_url('modules'); ?>" class="md-modal-close" title="Fermer">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </a>
</div>

<!-- ========== BODY ========== -->
<div class="md-modal-body">

<?php if (!$is_del && !$is_undel) {
    echo form_open($_SESSION['controller_name'].'/save/', array('id'=>'item_form'));
?>

    <div style="padding:16px 0;">
        <!-- Ligne 1: Nom + Ordre -->
        <div class="md-form-row">
            <div class="md-form-group" style="flex:2;min-width:200px;">
                <label class="md-form-label required"><?php echo $this->lang->line('modules_module_name'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'module_name',
                    'id'    => 'module_name',
                    'class' => 'md-form-input',
                    'value' => $info->module_name ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 100px;">
                <label class="md-form-label required"><?php echo $this->lang->line('modules_module_sort'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'sort',
                    'id'    => 'sort',
                    'class' => 'md-form-input',
                    'style' => 'text-align:center;',
                    'value' => $info->sort ?? ''
                )); ?>
            </div>
        </div>

        <!-- Ligne 2: Clé lang nom + Clé lang desc -->
        <div class="md-form-row" style="margin-top:12px;">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label required"><?php echo $this->lang->line('modules_module_name_lang_key'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'name_lang_key',
                    'id'    => 'name_lang_key',
                    'class' => 'md-form-input',
                    'value' => $info->name_lang_key ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label required"><?php echo $this->lang->line('modules_module_desc_lang_key'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'desc_lang_key',
                    'id'    => 'desc_lang_key',
                    'class' => 'md-form-input',
                    'value' => $info->desc_lang_key ?? ''
                )); ?>
            </div>
        </div>

        <!-- Ligne 3: Visibilité (4 dropdowns) -->
        <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-color, #e2e8f0);">
            <div style="font-weight:600;font-size:0.85rem;margin-bottom:8px;color:var(--text-secondary,#64748b);"><?php echo $this->lang->line('modules_show_buttons') ?? 'Visibilité'; ?></div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('modules_module_show_in_header'); ?></label>
                    <?php echo form_dropdown(
                        'show_in_header',
                        $_SESSION['G']->YorN_pick_list ?? array(),
                        $info->show_in_header ?? 'N',
                        'id="show_in_header" class="md-form-select"'
                    ); ?>
                </div>
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('modules_module_user_menu'); ?></label>
                    <?php echo form_dropdown(
                        'user_menu',
                        $_SESSION['G']->YorN_pick_list ?? array(),
                        $info->user_menu ?? 'N',
                        'id="user_menu" class="md-form-select"'
                    ); ?>
                </div>
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('modules_module_admin_menu'); ?></label>
                    <?php echo form_dropdown(
                        'admin_menu',
                        $_SESSION['G']->YorN_pick_list ?? array(),
                        $info->admin_menu ?? 'N',
                        'id="admin_menu" class="md-form-select"'
                    ); ?>
                </div>
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('modules_module_sys_admin_menu'); ?></label>
                    <?php echo form_dropdown(
                        'sys_admin_menu',
                        $_SESSION['G']->YorN_pick_list ?? array(),
                        $info->sys_admin_menu ?? 'N',
                        'id="sys_admin_menu" class="md-form-select"'
                    ); ?>
                </div>
            </div>
        </div>

        <!-- Ligne 4: Boutons (5 dropdowns) -->
        <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border-color, #e2e8f0);">
            <div style="font-weight:600;font-size:0.85rem;margin-bottom:8px;color:var(--text-secondary,#64748b);"><?php echo $this->lang->line('modules_show_buttons') . $this->lang->line('common_question'); ?></div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('modules_module_new_button'); ?></label>
                    <?php echo form_dropdown(
                        'show_new_button',
                        $_SESSION['G']->oneorzero_pick_list ?? array(),
                        $info->show_new_button ?? '0',
                        'id="show_new_button" class="md-form-select"'
                    ); ?>
                </div>
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('modules_module_exit_button'); ?></label>
                    <?php echo form_dropdown(
                        'show_exit_button',
                        $_SESSION['G']->oneorzero_pick_list ?? array(),
                        $info->show_exit_button ?? '0',
                        'id="show_exit_button" class="md-form-select"'
                    ); ?>
                </div>
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('modules_module_clone_button'); ?></label>
                    <?php echo form_dropdown(
                        'show_clone_button',
                        $_SESSION['G']->oneorzero_pick_list ?? array(),
                        $info->show_clone_button ?? '0',
                        'id="show_clone_button" class="md-form-select"'
                    ); ?>
                </div>
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('modules_module_merge_button'); ?></label>
                    <?php echo form_dropdown(
                        'show_merge_button',
                        $_SESSION['G']->oneorzero_pick_list ?? array(),
                        $info->show_merge_button ?? '0',
                        'id="show_merge_button" class="md-form-select"'
                    ); ?>
                </div>
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('modules_module_undel_button'); ?></label>
                    <?php echo form_dropdown(
                        'show_undel_button',
                        $_SESSION['G']->oneorzero_pick_list ?? array(),
                        $info->show_undel_button ?? '0',
                        'id="show_undel_button" class="md-form-select"'
                    ); ?>
                </div>
            </div>
        </div>
    </div>

<?php echo form_close(); } ?>

</div><!-- /md-modal-body -->

<!-- ========== FOOTER ========== -->
<?php if (!$is_del && !$is_undel): ?>
<div class="md-modal-footer">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('modules'); ?>" class="md-btn md-btn-secondary">
            <?php echo $this->lang->line('common_reset'); ?>
        </a>
        <button type="submit" form="item_form" name="submit" id="submit" class="md-btn md-btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
            </svg>
            <?php echo $this->lang->line('common_submit'); ?>
        </button>
    </div>
</div>
<?php endif; ?>

</div><!-- /md-modal -->
</div>

<script type="text/javascript">
$(document).ready(function() {
    <?php if ($_msg_class === 'success_message'): ?>
    setTimeout(function(){ window.location.href = '<?php echo site_url("modules"); ?>'; }, 1000);
    <?php elseif ($is_new): ?>
    $('#module_name').focus();
    <?php else: ?>
    $('#module_name').focus();
    <?php endif; ?>
});
</script>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>
