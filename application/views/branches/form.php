<style>
.unified-header, .unified-footer { display: none !important; }
.branch-form.md-modal-overlay { z-index: 10000; }
.branch-form .md-modal { max-height: none !important; max-width: 700px; overflow: visible !important; }
.branch-form .md-form-row { align-items: flex-end; }
.branch-form .md-tab { display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
</style>

<?php
$is_new  = (($_SESSION['new'] ?? 0) == 1);
$is_del  = (($_SESSION['del'] ?? 0) == 1);
$is_undel = (($_SESSION['undel'] ?? 0) == 1);
$info    = $_SESSION['transaction_info'];
$title = 'Succursale';
?>

<div class="md-modal-overlay branch-form">
<div class="md-modal" style="max-width:700px;">

<!-- ========== HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div style="background:var(--primary,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:8px;flex-shrink:0;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="6" x2="9" y2="6.01"/><line x1="15" y1="6" x2="15" y2="6.01"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/><path d="M10 22v-4h4v4"/>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref"><?php echo htmlspecialchars($info->branch_code ?? ''); ?></div>
            <h2 class="md-modal-name" style="font-size:1.1em;"><?php echo $title; ?></h2>
        </div>
    </div>
    <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </a>
</div>

<!-- ========== MESSAGES ========== -->
<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<!-- ========== BODY ========== -->
<div class="md-modal-body">

<?php if (!$is_del && !$is_undel) {
    echo form_open($_SESSION['controller_name'].'/save/', array('id'=>'item_form'));
?>

    <!-- Onglets -->
    <div class="md-tab-bar">
        <div class="md-tab md-tab-active" data-tab="tab-info">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <?php echo $this->lang->line('branches_basic_information'); ?>
        </div>
        <div class="md-tab" data-tab="tab-connexion">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
            </svg>
            Connexion
        </div>
    </div>

    <!-- Panel : Information Succursale -->
    <div id="tab-info" class="branch-tab-panel" style="padding:16px 0;">
        <div class="md-form-row">
            <div class="md-form-group" style="flex:0 0 100px;">
                <label class="md-form-label required"><?php echo $this->lang->line('branches_branch_code'); ?></label>
                <?php echo form_input(array(
                    'name'      => 'branch_code',
                    'id'        => 'branch_code',
                    'class'     => 'md-form-input',
                    'maxlength' => '10',
                    'value'     => $info->branch_code ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:1;min-width:150px;">
                <label class="md-form-label required"><?php echo $this->lang->line('branches_branch_description'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'branch_description',
                    'id'    => 'branch_description',
                    'class' => 'md-form-input',
                    'value' => $info->branch_description ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 140px;">
                <label class="md-form-label required"><?php echo $this->lang->line('branches_branch_type'); ?></label>
                <?php echo form_dropdown(
                    'branch_type',
                    $_SESSION['branch_type_pick_list'],
                    $info->branch_type ?? '',
                    'id="branch_type" class="md-form-select"'
                ); ?>
            </div>
        </div>
    </div>

    <!-- Panel : Connexion -->
    <div id="tab-connexion" class="branch-tab-panel" style="display:none;padding:16px 0;">
        <div class="md-form-row">
            <div class="md-form-group" style="flex:0 0 120px;">
                <label class="md-form-label"><?php echo $this->lang->line('branches_branch_allows_check'); ?></label>
                <?php echo form_dropdown(
                    'branch_allows_check',
                    $_SESSION['G']->YorN_pick_list,
                    $info->branch_allows_check ?? 'N',
                    'id="branch_allows_check" class="md-form-select"'
                ); ?>
            </div>
            <div class="md-form-group" style="flex:1;min-width:130px;">
                <label class="md-form-label"><?php echo $this->lang->line('branches_branch_ip'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'branch_ip',
                    'id'    => 'branch_ip',
                    'class' => 'md-form-input',
                    'value' => $info->branch_ip ?? ''
                )); ?>
            </div>
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('branches_branch_user'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'branch_user',
                    'id'    => 'branch_user',
                    'class' => 'md-form-input',
                    'value' => $info->branch_user ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('branches_branch_password'); ?></label>
                <?php echo form_password(array(
                    'name'  => 'branch_password',
                    'id'    => 'branch_password',
                    'class' => 'md-form-input',
                    'value' => $info->branch_password ?? ''
                )); ?>
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('branches_branch_database'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'branch_database',
                    'id'    => 'branch_database',
                    'class' => 'md-form-input',
                    'value' => $info->branch_database ?? ''
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
    // Tab switching
    $('.branch-form .md-tab').on('click', function() {
        var target = $(this).data('tab');
        $('.branch-form .md-tab').removeClass('md-tab-active');
        $(this).addClass('md-tab-active');
        $('.branch-tab-panel').hide();
        $('#' + target).show();
    });

    <?php if ($is_new): ?>
    $('#branch_code').focus();
    <?php else: ?>
    $('#branch_description').focus();
    <?php endif; ?>
});
</script>
