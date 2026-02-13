<style>
.unified-header, .unified-footer { display: none !important; }
.tz-form.md-modal-overlay { z-index: 10000; }
.tz-form .md-modal { max-height: none !important; max-width: 600px; overflow: visible !important; }
</style>

<?php
$is_new  = (($_SESSION['new'] ?? 0) == 1);
$is_del  = (($_SESSION['del'] ?? 0) == 1);
$is_undel = (($_SESSION['undel'] ?? 0) == 1);
$info    = $_SESSION['transaction_info'];
?>

<div class="md-modal-overlay tz-form">
<div class="md-modal" style="max-width:600px;">

<!-- ========== HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div style="background:var(--primary,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:8px;flex-shrink:0;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref"><?php echo htmlspecialchars($info->timezone_id ?? ''); ?></div>
            <h2 class="md-modal-name" style="font-size:1.1em;"><?php echo $this->lang->line('modules_timezones'); ?></h2>
        </div>
    </div>
    <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </a>
</div>

<!-- ========== MESSAGES ========== -->
<?php
$_tz_msg_class = '';
if (isset($_SESSION['error_code']) && $_SESSION['error_code'] !== '' && isset($_SESSION['G']->messages[$_SESSION['error_code']])) {
    $_tz_msg_class = $_SESSION['G']->messages[$_SESSION['error_code']][1] ?? '';
}
include('../wrightetmathon/application/views/partial/show_messages.php');
?>

<!-- ========== BODY ========== -->
<div class="md-modal-body">

<?php if (!$is_del && !$is_undel) {
    echo form_open($_SESSION['controller_name'].'/save/', array('id'=>'item_form'));
?>

    <div style="padding:16px 0;">
        <!-- Ligne 1: Nom + Description -->
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1;min-width:120px;">
                <label class="md-form-label required"><?php echo $this->lang->line('timezones_timezone_name'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'timezone_name',
                    'id'    => 'timezone_name',
                    'class' => 'md-form-input',
                    'value' => $info->timezone_name ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:2;min-width:150px;">
                <label class="md-form-label required"><?php echo $this->lang->line('timezones_timezone_description'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'timezone_description',
                    'id'    => 'timezone_description',
                    'class' => 'md-form-input',
                    'value' => $info->timezone_description ?? ''
                )); ?>
            </div>
        </div>

        <!-- Ligne 2: Continent + Ville + Offset -->
        <div class="md-form-row" style="margin-top:12px;">
            <div class="md-form-group" style="flex:1;min-width:100px;">
                <label class="md-form-label required"><?php echo $this->lang->line('timezones_timezone_continent'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'timezone_continent',
                    'id'    => 'timezone_continent',
                    'class' => 'md-form-input',
                    'value' => $info->timezone_continent ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:1;min-width:100px;">
                <label class="md-form-label required"><?php echo $this->lang->line('timezones_timezone_city'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'timezone_city',
                    'id'    => 'timezone_city',
                    'class' => 'md-form-input',
                    'value' => $info->timezone_city ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 120px;">
                <label class="md-form-label required"><?php echo $this->lang->line('timezones_timezone_GMT_offset'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'timezone_offset',
                    'id'    => 'timezone_offset',
                    'class' => 'md-form-input',
                    'style' => 'text-align:center;',
                    'value' => $info->timezone_offset ?? ''
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
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
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
</div><!-- /md-modal-overlay -->

<script type="text/javascript">
$(document).ready(function() {
    <?php if ($_tz_msg_class === 'success_message'): ?>
    setTimeout(function(){ window.location.href = '<?php echo site_url("timezones"); ?>'; }, 1000);
    <?php elseif ($is_new): ?>
    $('#timezone_name').focus();
    <?php else: ?>
    $('#timezone_name').focus();
    <?php endif; ?>
});
</script>
