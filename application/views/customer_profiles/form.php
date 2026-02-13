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

<div style="max-width:500px; margin:20px auto;">
<div class="md-modal" style="position:relative; max-height:none; overflow:visible;">

<!-- ========== HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div style="background:var(--primary,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:8px;flex-shrink:0;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                <path d="M16 3.13a4 4 0 010 7.75"/>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref"><?php echo htmlspecialchars($info->profile_id ?? ''); ?></div>
            <h2 class="md-modal-name" style="font-size:1.1em;"><?php echo $this->lang->line('modules_customer_profiles'); ?></h2>
        </div>
    </div>
    <a href="<?php echo site_url('customer_profiles'); ?>" class="md-modal-close" title="Fermer">
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
        <!-- Ligne 1: Nom + Description -->
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1;min-width:120px;">
                <label class="md-form-label required"><?php echo $this->lang->line('customer_profiles_profile_name'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'profile_name',
                    'id'    => 'profile_name',
                    'class' => 'md-form-input',
                    'value' => $info->profile_name ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:2;min-width:150px;">
                <label class="md-form-label required"><?php echo $this->lang->line('customer_profiles_profile_description'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'profile_description',
                    'id'    => 'profile_description',
                    'class' => 'md-form-input',
                    'value' => $info->profile_description ?? ''
                )); ?>
            </div>
        </div>

        <!-- Ligne 2: Remise + Fidélité -->
        <div class="md-form-row" style="margin-top:12px;">
            <div class="md-form-group" style="flex:1;min-width:120px;">
                <label class="md-form-label"><?php echo $this->lang->line('customer_profiles_profile_discount') . $this->lang->line('common_percent'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'profile_discount',
                    'id'    => 'profile_discount',
                    'class' => 'md-form-input',
                    'style' => 'text-align:right;',
                    'value' => $info->profile_discount ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 160px;">
                <label class="md-form-label required"><?php echo $this->lang->line('customer_profiles_profile_fidelity'); ?></label>
                <?php echo form_dropdown(
                    'profile_fidelity',
                    $_SESSION['G']->YorN_pick_list ?? array(),
                    $info->profile_fidelity ?? 'N',
                    'id="profile_fidelity" class="md-form-select"'
                ); ?>
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
        <a href="<?php echo site_url('customer_profiles'); ?>" class="md-btn md-btn-secondary">
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
    setTimeout(function(){ window.location.href = '<?php echo site_url("customer_profiles"); ?>'; }, 1000);
    <?php elseif ($is_new): ?>
    $('#profile_name').focus();
    <?php else: ?>
    $('#profile_name').focus();
    <?php endif; ?>
});
</script>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>
