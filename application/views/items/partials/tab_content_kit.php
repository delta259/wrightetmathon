<?php
/**
 * Tab content partial: Kit
 * Manages kit structure and kit detail for DynamicKit items
 * Used by modal_wrapper.php and AJAX tab loading
 *
 * Expects: $_SESSION['kit_info'] with ->item_info, ->kit_structure, ->option_type_pick_list
 *          $_SESSION['show_dialog'] (15 = structure, 16 = detail)
 */

$item_info = $_SESSION['kit_info']->item_info;
$item_id = $item_info->item_id;
$kit_reference = $item_info->kit_reference;
$active_dialog = isset($_SESSION['show_dialog']) ? $_SESSION['show_dialog'] : 15;
?>

<!-- Messages -->
<?php include(APPPATH . 'views/partial/show_messages.php'); ?>

<!-- ============================================================
     CARD: Info Kit
     ============================================================ -->
<div class="md-card">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
        </svg>
        Kit : <?php echo htmlspecialchars($kit_reference); ?>
    </div>

    <div class="md-form-row" style="align-items:center; gap:24px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:13px; color:var(--text-secondary);">Reference :</span>
            <span style="font-weight:600;"><?php echo htmlspecialchars($item_info->item_number); ?></span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:13px; color:var(--text-secondary);">Nom :</span>
            <span style="font-weight:500;"><?php echo htmlspecialchars($item_info->name); ?></span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:13px; color:var(--text-secondary);">Stock :</span>
            <span class="md-price-value"><?php echo round($item_info->quantity, 0); ?></span>
        </div>
    </div>
</div>

<?php if ($active_dialog == 15) { ?>
<!-- ============================================================
     CARD: Structure du Kit (show_dialog = 15)
     ============================================================ -->
<div class="md-card">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
        </svg>
        <?php echo $this->lang->line('common_manage') . ' ' . $this->lang->line('items_kit_structure'); ?>
    </div>

    <?php if (isset($_SESSION['kit_info']->kit_structure) && count($_SESSION['kit_info']->kit_structure) > 0) { ?>
    <div style="overflow-x:auto;">
        <table class="md-table">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th><?php echo $this->lang->line('items_kit_option'); ?></th>
                    <th><?php echo $this->lang->line('items_kit_option_type'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('items_kit_option_qty'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['kit_info']->kit_structure as $row) { ?>
                <tr>
                    <td>
                        <a href="<?php echo site_url('items/kit_structure_delete/' . $item_id . '/' . $row['kit_reference'] . '/' . $row['kit_option']); ?>"
                           class="md-delete-link" title="<?php echo $this->lang->line('common_delete'); ?>">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo site_url('items/kit_detail/' . $row['kit_reference'] . '/' . $row['kit_option'] . '/' . $row['kit_option_type']); ?>"
                           style="font-weight:600; color:var(--accent-blue); text-decoration:none;">
                            <?php echo htmlspecialchars($row['kit_option']); ?>
                        </a>
                    </td>
                    <td>
                        <?php
                        switch ($row['kit_option_type']) {
                            case 'O': echo $this->lang->line('items_kit_option_type_O'); break;
                            case 'F': echo $this->lang->line('items_kit_option_type_F'); break;
                            default: echo htmlspecialchars($row['kit_option_type']);
                        }
                        ?>
                    </td>
                    <td style="text-align:right; font-family:monospace; font-weight:600;">
                        <?php echo $row['kit_option_qty']; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } else { ?>
    <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
        Aucune option de kit definie.
    </div>
    <?php } ?>
</div>

<!-- Bouton Ajouter structure -->
<div style="margin-bottom:16px;">
    <button type="button" id="btn_show_add_kit_structure" class="md-btn md-btn-success md-btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('items_kit_structure'); ?>
    </button>
</div>

<!-- CARD: Ajouter structure -->
<div class="md-card" id="kit_structure_add_card" style="display:none;">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('items_kit_structure'); ?>
    </div>

    <?php echo form_open('items/kit_structure_add/' . $item_id . '/' . $kit_reference, array('id' => 'kit_structure_add_form')); ?>

    <div class="md-form-row" style="align-items:flex-end;">
        <div class="md-form-group" style="flex:0 0 150px;">
            <label class="md-form-label required"><?php echo $this->lang->line('items_kit_option'); ?></label>
            <?php echo form_input(array(
                'name'  => 'new1_kit_option',
                'id'    => 'new1_kit_option',
                'class' => 'md-form-input required',
                'style' => 'text-align:center;',
                'value' => isset($_SESSION['kit_info']->kit_option) ? $_SESSION['kit_info']->kit_option : ''
            )); ?>
        </div>

        <div class="md-form-group" style="flex:0 0 180px;">
            <label class="md-form-label required"><?php echo $this->lang->line('items_kit_option_type'); ?></label>
            <?php echo form_dropdown(
                'new1_kit_option_type',
                $_SESSION['kit_info']->option_type_pick_list,
                isset($_SESSION['kit_info']->kit_option_type) ? $_SESSION['kit_info']->kit_option_type : 'F',
                'class="md-form-select" id="new1_kit_option_type"'
            ); ?>
        </div>

        <div class="md-form-group" style="flex:0 0 100px;">
            <label class="md-form-label required"><?php echo $this->lang->line('items_kit_option_qty'); ?></label>
            <?php echo form_input(array(
                'name'  => 'new1_kit_option_qty',
                'id'    => 'new1_kit_option_qty',
                'class' => 'md-form-input required',
                'style' => 'text-align:right;',
                'value' => isset($_SESSION['kit_info']->kit_option_qty) ? $_SESSION['kit_info']->kit_option_qty : '',
                'size'  => 3
            )); ?>
        </div>

        <div style="flex:1;"></div>

        <div class="md-form-group" style="flex:0 0 auto; margin-bottom:14px;">
            <?php echo form_submit(array(
                'name'  => 'add_kit_structure',
                'id'    => 'add_kit_structure',
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

<?php } elseif ($active_dialog == 16) { ?>
<!-- ============================================================
     CARD: Detail du Kit (show_dialog = 16)
     ============================================================ -->
<?php
$kit_option = isset($_SESSION['kit_info']->kit_option) ? $_SESSION['kit_info']->kit_option : '';
$kit_option_type = isset($_SESSION['kit_info']->kit_option_type) ? $_SESSION['kit_info']->kit_option_type : 'F';
$option_type_text = ($kit_option_type == 'O') ? $this->lang->line('items_kit_option_type_O') : $this->lang->line('items_kit_option_type_F');
?>

<div class="md-card">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
        </svg>
        <?php echo $this->lang->line('common_manage') . ' ' . $this->lang->line('items_kit_detail'); ?>
        <span style="font-weight:400; font-size:12px; color:var(--text-muted); margin-left:8px;">
            <?php echo htmlspecialchars($kit_option) . ' &mdash; ' . $option_type_text; ?>
        </span>
    </div>

    <div style="margin-bottom:12px;">
        <a href="<?php echo site_url('items/kit/' . $item_id); ?>" class="md-btn md-btn-secondary md-btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Retour a la structure
        </a>
    </div>

    <?php if (isset($_SESSION['kit_info']->kit_detail) && count($_SESSION['kit_info']->kit_detail) > 0) { ?>
    <div style="overflow-x:auto;">
        <table class="md-table">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th><?php echo $this->lang->line('items_item_number'); ?></th>
                    <th><?php echo $this->lang->line('items_name'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['kit_info']->kit_detail as $row) { ?>
                <tr>
                    <td>
                        <a href="<?php echo site_url('items/kit_detail_delete/' . $row['kit_reference'] . '/' . $row['kit_option'] . '/' . $row['item_number'] . '/' . $kit_option_type); ?>"
                           class="md-delete-link" title="<?php echo $this->lang->line('common_delete'); ?>">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </a>
                    </td>
                    <td style="font-weight:600; color:var(--accent-blue);"><?php echo htmlspecialchars($row['item_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } else { ?>
    <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
        Aucun article dans cette option.
    </div>
    <?php } ?>
</div>

<!-- Bouton Ajouter detail -->
<div style="margin-bottom:16px;">
    <button type="button" id="btn_show_add_kit_detail" class="md-btn md-btn-success md-btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('items_kit_detail'); ?>
    </button>
</div>

<!-- CARD: Ajouter detail -->
<div class="md-card" id="kit_detail_add_card" style="display:none;">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('items_kit_detail'); ?>
    </div>

    <?php echo form_open('items/kit_detail_add/' . $kit_reference . '/' . $kit_option . '/' . $kit_option_type, array('id' => 'kit_detail_add_form')); ?>

    <div class="md-form-row" style="align-items:flex-end;">
        <div class="md-form-group" style="flex:0 0 200px;">
            <label class="md-form-label required"><?php echo $this->lang->line('items_item_number'); ?></label>
            <?php echo form_input(array(
                'name'  => 'new1_item_number',
                'id'    => 'new1_item_number',
                'class' => 'md-form-input required',
                'style' => 'text-align:center;',
                'value' => isset($_SESSION['kit_info']->kit_item_number) ? $_SESSION['kit_info']->kit_item_number : ''
            )); ?>
        </div>

        <div style="flex:1;"></div>

        <div class="md-form-group" style="flex:0 0 auto; margin-bottom:14px;">
            <?php echo form_submit(array(
                'name'  => 'add_kit_detail',
                'id'    => 'add_kit_detail',
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

<?php } /* end show_dialog check */ ?>
