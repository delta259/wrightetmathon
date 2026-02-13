<?php $this->load->view("partial/header"); ?>

<?php
$is_new  = (($_SESSION['new'] ?? 0) == 1);
$is_del  = (($_SESSION['del'] ?? 0) == 1);
$is_undel = (($_SESSION['undel'] ?? 0) == 1);
$info    = $_SESSION['transaction_info'];
?>

<!-- Messages -->
<?php
$_tracker_msg_class = '';
if (isset($_SESSION['error_code']) && $_SESSION['error_code'] !== '' && isset($_SESSION['G']->messages[$_SESSION['error_code']])) {
    $_tracker_msg_class = $_SESSION['G']->messages[$_SESSION['error_code']][1] ?? '';
}
include('../wrightetmathon/application/views/partial/show_messages.php');
?>

<div style="max-width:600px; margin:20px auto;">
<div class="md-modal" style="position:relative; max-height:none; overflow:visible;">

<!-- ========== HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div style="background:var(--primary,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:8px;flex-shrink:0;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
                <path d="M9 14l2 2 4-4"/>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref">#<?php echo htmlspecialchars($info->tracker_id ?? ''); ?></div>
            <h2 class="md-modal-name" style="font-size:1.1em;"><?php echo $this->lang->line('modules_trackers'); ?></h2>
        </div>
    </div>
    <a href="<?php echo site_url('trackers'); ?>" class="md-modal-close" title="Fermer">
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
        <!-- Ligne 1: Sujet + Statut -->
        <div class="md-form-row">
            <div class="md-form-group" style="flex:2;min-width:200px;">
                <label class="md-form-label required"><?php echo $this->lang->line('trackers_tracker_subject'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'tracker_subject',
                    'id'    => 'tracker_subject',
                    'class' => 'md-form-input',
                    'value' => $info->tracker_subject ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 160px;">
                <label class="md-form-label required"><?php echo $this->lang->line('trackers_tracker_status'); ?></label>
                <?php echo form_dropdown(
                    'tracker_status',
                    $_SESSION['G']->tracker_status_pick_list ?? array(),
                    $info->tracker_status ?? '',
                    'id="tracker_status" class="md-form-select"'
                ); ?>
            </div>
        </div>

        <!-- Ligne 2: Resume commit -->
        <div class="md-form-row" style="margin-top:12px;">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('trackers_tracker_commit_summary'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'tracker_commit_summary',
                    'id'    => 'tracker_commit_summary',
                    'class' => 'md-form-input',
                    'value' => $info->tracker_commit_summary ?? ''
                )); ?>
            </div>
        </div>

        <!-- Ligne 3: Description (textarea) -->
        <div class="md-form-row" style="margin-top:12px;">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label required"><?php echo $this->lang->line('trackers_tracker_description'); ?></label>
                <?php echo form_textarea(array(
                    'name'  => 'tracker_description',
                    'id'    => 'tracker_description',
                    'class' => 'md-form-input',
                    'rows'  => 4,
                    'style' => 'resize:vertical;',
                    'value' => $info->tracker_description ?? ''
                )); ?>
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
        <a href="<?php echo site_url('trackers'); ?>" class="md-btn md-btn-secondary">
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
</div><!-- /max-width wrapper -->

<script type="text/javascript">
$(document).ready(function() {
    <?php if ($_tracker_msg_class === 'success_message'): ?>
    setTimeout(function(){ window.location.href = '<?php echo site_url("trackers"); ?>'; }, 1000);
    <?php elseif ($is_new): ?>
    $('#tracker_subject').focus();
    <?php else: ?>
    $('#tracker_subject').focus();
    <?php endif; ?>
});
</script>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>
