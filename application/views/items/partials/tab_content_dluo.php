<?php
/**
 * Tab content partial: DLUO
 * Manages DLUO (Date Limite d'Utilisation Optimale) entries for an item
 * Used by modal_wrapper.php and AJAX tab loading
 *
 * Expects: $_SESSION['item_info_dluo'], $_SESSION['dluo_total_qty'], $_SESSION['transaction_info']
 */

$item_id = $_SESSION['transaction_info']->item_id;
$current_qty = $_SESSION['transaction_info']->quantity;
$dluo_total = isset($_SESSION['dluo_total_qty']) ? $_SESSION['dluo_total_qty'] : 0;
$qty_match = ($dluo_total == $current_qty);
?>

<!-- Messages -->
<?php include(APPPATH . 'views/partial/show_messages.php'); ?>

<!-- ============================================================
     CARD: Resume DLUO
     ============================================================ -->
<div class="md-card">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <?php echo $this->lang->line('common_manage') . ' ' . $this->lang->line('items_dluo'); ?>
    </div>

    <div class="md-form-row" style="align-items:center; gap:24px; margin-bottom:8px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:13px; color:var(--text-secondary);">Stock :</span>
            <span class="md-price-value"><?php echo round($current_qty, 0); ?></span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:13px; color:var(--text-secondary);">Total DLUO :</span>
            <span class="md-price-value" style="color:<?php echo $qty_match ? 'var(--success-text)' : 'var(--danger-text)'; ?>;">
                <?php echo number_format($dluo_total, 0); ?>
            </span>
            <?php if (!$qty_match) { ?>
            <span style="font-size:11px; padding:2px 8px; background:var(--danger-bg); color:var(--danger-text); border-radius:var(--radius-sm);">
                Ecart
            </span>
            <?php } ?>
        </div>
    </div>
</div>

<!-- ============================================================
     CARD: DLUO existants
     ============================================================ -->
<?php if (isset($_SESSION['item_info_dluo']) && count($_SESSION['item_info_dluo']) > 0) { ?>
<div class="md-card">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
        </svg>
        DLUO enregistrees
    </div>

    <div style="overflow-x:auto;">
        <table class="md-table">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('common_year'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('common_month'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('common_quantity'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['item_info_dluo'] as $row) { ?>
                <tr>
                    <td>
                        <a href="<?php echo site_url('items/dluo_delete/' . $row['year'] . '/' . $row['month']); ?>"
                           class="md-delete-link" title="<?php echo $this->lang->line('common_delete'); ?>">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </a>
                    </td>
                    <td style="text-align:center; font-weight:500;"><?php echo $row['year']; ?></td>
                    <td style="text-align:center; font-weight:500;"><?php echo str_pad($row['month'], 2, '0', STR_PAD_LEFT); ?></td>
                    <td style="text-align:right; font-family:monospace; font-weight:600;"><?php echo number_format($row['dluo_qty'], 0); ?></td>
                </tr>
                <?php } ?>
                <tr style="background:var(--table-header-bg);">
                    <td></td>
                    <td colspan="2" style="text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; letter-spacing:0.3px;">
                        <?php echo $this->lang->line('common_quantity'); ?>
                    </td>
                    <td style="text-align:right; font-family:monospace; font-weight:700; font-size:15px;">
                        <?php echo number_format($dluo_total, 0); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>

<!-- Bouton Ajouter -->
<div style="margin-bottom:16px;">
    <button type="button" id="btn_show_add_dluo" class="md-btn md-btn-success md-btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('items_dluo'); ?>
    </button>
</div>

<!-- ============================================================
     CARD: Ajouter une DLUO
     ============================================================ -->
<div class="md-card" id="dluo_add_card" style="display:none;">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('items_dluo'); ?>
    </div>

    <?php echo form_open('items/dluo_add/', array('id' => 'dluo_add_form')); ?>

    <div class="md-form-row" style="align-items:flex-end;">
        <div class="md-form-group" style="flex:0 0 100px;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_year'); ?></label>
            <?php echo form_input(array(
                'name'  => 'new1_add_year',
                'id'    => 'new1_add_year',
                'class' => 'md-form-input required',
                'style' => 'text-align:center;',
                'value' => isset($_SESSION['transaction_info']->dluo_year1) ? $_SESSION['transaction_info']->dluo_year1 : date('Y'),
                'size'  => 4
            )); ?>
        </div>

        <div class="md-form-group" style="flex:0 0 80px;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_month'); ?></label>
            <?php echo form_input(array(
                'name'  => 'new1_add_month',
                'id'    => 'new1_add_month',
                'class' => 'md-form-input required',
                'style' => 'text-align:center;',
                'value' => isset($_SESSION['transaction_info']->dluo_month1) ? $_SESSION['transaction_info']->dluo_month1 : '',
                'size'  => 2
            )); ?>
        </div>

        <div class="md-form-group" style="flex:0 0 100px;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_quantity'); ?></label>
            <?php echo form_input(array(
                'name'  => 'new1_add_qty',
                'id'    => 'new1_add_qty',
                'class' => 'md-form-input required',
                'style' => 'text-align:right;',
                'value' => isset($_SESSION['transaction_info']->dluo_qty1) ? $_SESSION['transaction_info']->dluo_qty1 : '',
                'size'  => 6
            )); ?>
        </div>

        <div style="flex:1;"></div>

        <div class="md-form-group" style="flex:0 0 auto; margin-bottom:14px;">
            <?php echo form_submit(array(
                'name'  => 'submit',
                'id'    => 'submit_add_dluo',
                'value' => $this->lang->line('common_submit'),
                'class' => 'md-btn md-btn-primary md-btn-sm'
            )); ?>
        </div>
    </div>

    <?php echo form_close(); ?>

    <div class="md-required-note">
        <span>*</span> <?php echo $this->lang->line('common_fields_required_message'); ?>
    </div>
</div>
