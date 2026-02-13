<?php $this->load->view("partial/header"); ?>

<?php
$is_new   = (($_SESSION['new'] ?? 0) == 1);
$is_del   = (($_SESSION['del'] ?? 0) == 1);
$is_undel = (($_SESSION['undel'] ?? 0) == 1);
$info     = $_SESSION['transaction_info'];

$month_names = array(1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
    7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre');

// Build month dropdown options
$month_options = array('' => '-- Mois --');
for ($i = 1; $i <= 12; $i++) { $month_options[$i] = str_pad($i,2,'0',STR_PAD_LEFT).' - '.$month_names[$i]; }

$title = $is_new ? 'Nouvel objectif' : 'Modifier objectif';
if (!$is_new) {
    $m = intval($info->target_month ?? 0);
    $title .= ' — ' . ($month_names[$m] ?? $m) . ' ' . ($info->target_year ?? '');
}

// Detect message class before show_messages unsets it
$_msg_class = '';
if (isset($_SESSION['error_code']) && $_SESSION['error_code'] !== '' && isset($_SESSION['G']->messages[$_SESSION['error_code']])) {
    $_msg_class = $_SESSION['G']->messages[$_SESSION['error_code']][1] ?? '';
}
?>

<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<div style="max-width:480px; margin:20px auto;">
<div class="md-modal" style="position:relative; max-height:none; overflow:visible;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.7em 1em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: var(--primary, #2563eb); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1em; font-weight: 700; border-radius: 50%; width: 40px; height: 40px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                <polyline points="13 2 13 9 20 9"></polyline>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1.05em;"><?php echo $title; ?></h2>
        </div>
    </div>
    <div class="md-modal-header-actions">
        <a href="<?php echo site_url('targets'); ?>" class="md-modal-close" title="Fermer">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>
    </div>
</div>

<!-- Body -->
<div class="md-modal-body" style="padding: 0.8em 1.2em;">

<?php if (!$is_del) { ?>
<?php if (!$is_undel) { echo form_open($_SESSION['controller_name'].'/save/', array('id' => 'item_form')); } ?>

<div class="md-card" style="padding: 0.7em 0.9em;">
    <div class="md-card-title" style="font-size: 0.82em;">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        P&eacute;riode
    </div>

    <div class="md-form-row" style="gap: 0.6em; margin-bottom: 0.4em; align-items: flex-end;">
        <div class="md-form-group" style="flex: 0 0 100px;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_year') ?: 'Ann&eacute;e'; ?></label>
            <?php if ($is_new) {
                echo form_input(array('name'=>'target_year','id'=>'target_year','class'=>'md-form-input required',
                    'value'=>$info->target_year,'placeholder'=>date('Y'),'style'=>'text-align:center;'));
            } else { ?>
                <div class="md-form-static" style="text-align:center; font-weight:700;"><?php echo $info->target_year; ?></div>
            <?php } ?>
        </div>
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_month') ?: 'Mois'; ?></label>
            <?php if ($is_new) {
                echo form_dropdown('target_month', $month_options, $info->target_month, 'class="md-form-select required" id="target_month"');
            } else { ?>
                <div class="md-form-static" style="font-weight:600;">
                    <?php $m = intval($info->target_month); echo ($month_names[$m] ?? $m); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="md-card" style="padding: 0.7em 0.9em; margin-top: 0.5em;">
    <div class="md-card-title" style="font-size: 0.82em;">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
        Objectifs
    </div>

    <div class="md-form-row" style="gap: 0.6em; margin-bottom: 0.4em; align-items: flex-end;">
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label required"><?php echo $this->lang->line('config_averagenumberopendays') ?: 'Jours ouverts'; ?></label>
            <?php echo form_input(array('name'=>'target_shop_open_days','id'=>'target_shop_open_days',
                'class'=>'md-form-input required','value'=>$info->target_shop_open_days,
                'placeholder'=>'0','style'=>'text-align:right;')); ?>
        </div>
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label required"><?php echo $this->lang->line('config_monthlysalestarget') ?: 'Objectif CA (&euro;)'; ?></label>
            <?php echo form_input(array('name'=>'target_shop_turnover','id'=>'target_shop_turnover',
                'class'=>'md-form-input required','value'=>$info->target_shop_turnover,
                'placeholder'=>'0','style'=>'text-align:right;')); ?>
        </div>
    </div>

    <?php
    // Show computed daily target
    $days = intval($info->target_shop_open_days ?? 0);
    $turn = floatval($info->target_shop_turnover ?? 0);
    $daily = ($days > 0) ? round($turn / $days) : 0;
    ?>
    <div style="text-align: right; font-size: 0.8em; color: var(--text-secondary, #64748b); margin-top: 0.3em;">
        CA / jour : <b id="tgt-daily-display"><?php echo number_format($daily, 0, ',', ' '); ?></b> &euro;
    </div>
</div>

<?php if (!$is_undel) { echo form_close(); } ?>
<?php } ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.5em 1em;">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('targets'); ?>" class="md-btn md-btn-secondary">
            <?php echo $this->lang->line('common_reset') ?: 'Annuler'; ?>
        </a>
        <?php if (!$is_undel && !$is_del) { ?>
        <button type="submit" form="item_form" name="submit" id="submit" class="md-btn md-btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            <?php echo $this->lang->line('common_submit') ?: 'Enregistrer'; ?>
        </button>
        <?php } ?>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /max-width wrapper -->

<script>
$(document).ready(function() {
    // Live compute daily target
    function updateDaily() {
        var days = parseInt($('#target_shop_open_days').val()) || 0;
        var turn = parseFloat($('#target_shop_turnover').val()) || 0;
        var daily = days > 0 ? Math.round(turn / days) : 0;
        $('#tgt-daily-display').text(daily.toLocaleString('fr-FR'));
    }
    $('#target_shop_open_days, #target_shop_turnover').on('input', updateDaily);

    <?php if ($_msg_class === 'success_message'): ?>
    // Auto-redirect to manage page after 1s on success
    setTimeout(function(){ window.location.href = '<?php echo site_url("targets"); ?>'; }, 1000);
    <?php elseif ($is_new): ?>
    $('#target_year').focus();
    <?php else: ?>
    $('#target_shop_open_days').focus();
    <?php endif; ?>
});
</script>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>
