<?php
/**
 * Tab content partial: Suppliers
 * Contains only the body content for the Suppliers tab
 * Used by modal_wrapper.php and AJAX tab loading
 */

// Currency label for cost price column
$cost_price_label = '';
switch ($_SESSION['G']->currency_details->currency_side) {
    case 'L':
        $cost_price_label = $_SESSION['G']->currency_details->currency_sign . ' ' . $this->lang->line('items_cost_price');
        break;
    case 'R':
        $cost_price_label = $this->lang->line('items_cost_price') . ' ' . $_SESSION['G']->currency_details->currency_sign;
        break;
}
?>

<!-- Messages -->
<?php include(APPPATH . 'views/partial/show_messages.php'); ?>

<!-- ============================================================
     CARD: Fournisseurs existants (update form)
     ============================================================ -->
<?php if (isset($_SESSION['transaction_supplier_info']) && count((array)$_SESSION['transaction_supplier_info']) > 0) { ?>
<div class="md-card">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
        <?php echo $this->lang->line('common_manage') . ' ' . $this->lang->line('suppliers_supplier'); ?>
    </div>

    <form action="index.php/items/item_supplier_update/" method="post" id="supplier_update_form">
    <div style="overflow-x:auto;">
        <table class="md-table">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th><?php echo $this->lang->line('items_supplier_preferred'); ?></th>
                    <th><?php echo $this->lang->line('suppliers_supplier'); ?></th>
                    <th><?php echo $cost_price_label; ?></th>
                    <th><?php echo $this->lang->line('items_reorder_policy'); ?></th>
                    <th title="<?php echo $this->lang->line('items_reorder_pack_size'); ?>">Qt&eacute; lot</th>
                    <th title="<?php echo $this->lang->line('items_min_order_qty'); ?>">Min cde</th>
                    <th title="<?php echo $this->lang->line('items_min_stock_qty'); ?>">Min stock</th>
                    <th><?php echo $this->lang->line('items_barcode'); ?></th>
                    <th><?php echo $this->lang->line('items_supplier_item_number'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                unset($_SESSION['suppliers_id']);
                foreach ($_SESSION['transaction_supplier_info'] as $row)
                {
                ?>
                <tr>
                    <!-- Delete -->
                    <td>
                        <a href="<?php echo site_url('items/item_supplier_delete/' . $row->item_id . '/' . $row->supplier_id); ?>"
                           class="md-delete-link" title="<?php echo $this->lang->line('common_delete'); ?>">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </a>
                    </td>

                    <!-- Preferred -->
                    <td>
                        <div class="md-toggle-group" style="padding:0; gap:6px;">
                            <label class="md-toggle">
                                <input type="checkbox" class="md-toggle-input"
                                    <?php echo ($row->supplier_preferred == 'Y') ? 'checked' : ''; ?>>
                                <span class="md-toggle-slider"></span>
                            </label>
                            <?php echo form_dropdown(
                                'supplier_preferred_' . $row->supplier_id,
                                $_SESSION['G']->YorN_pick_list,
                                $row->supplier_preferred,
                                'class="md-toggle-select" style="display:none;"'
                            ); ?>
                        </div>
                    </td>

                    <!-- Supplier name -->
                    <td style="font-weight:500;">
                        <?php echo htmlspecialchars($row->supplier_name); ?>
                    </td>

                    <!-- Cost price -->
                    <td>
                        <?php echo form_input(array(
                            'name'  => 'supplier_cost_price_' . $row->supplier_id,
                            'class' => 'md-form-input required',
                            'style' => 'text-align:right; width:90px;',
                            'value' => $row->supplier_cost_price,
                            'size'  => 8
                        )); ?>
                    </td>

                    <!-- Reorder policy -->
                    <td>
                        <div class="md-toggle-group" style="padding:0; gap:6px;">
                            <label class="md-toggle">
                                <input type="checkbox" class="md-toggle-input"
                                    <?php echo ($row->supplier_reorder_policy == 'Y') ? 'checked' : ''; ?>>
                                <span class="md-toggle-slider"></span>
                            </label>
                            <?php echo form_dropdown(
                                'supplier_reorder_policy_' . $row->supplier_id,
                                $_SESSION['G']->YorN_pick_list,
                                $row->supplier_reorder_policy,
                                'class="md-toggle-select" style="display:none;"'
                            ); ?>
                        </div>
                    </td>

                    <!-- Pack size -->
                    <td>
                        <?php echo form_input(array(
                            'name'  => 'supplier_reorder_pack_size_' . $row->supplier_id,
                            'class' => 'md-form-input',
                            'style' => 'text-align:right; width:70px;',
                            'value' => $row->supplier_reorder_pack_size,
                            'size'  => 5
                        )); ?>
                    </td>

                    <!-- Min order qty -->
                    <td>
                        <?php echo form_input(array(
                            'name'  => 'supplier_min_order_qty_' . $row->supplier_id,
                            'class' => 'md-form-input',
                            'style' => 'text-align:center; width:70px;',
                            'value' => $row->supplier_min_order_qty,
                            'size'  => 5
                        )); ?>
                    </td>

                    <!-- Min stock qty -->
                    <td>
                        <?php echo form_input(array(
                            'name'  => 'supplier_min_stock_qty_' . $row->supplier_id,
                            'class' => 'md-form-input',
                            'style' => 'text-align:right; width:70px;',
                            'value' => $row->supplier_min_stock_qty,
                            'size'  => 5
                        )); ?>
                    </td>

                    <!-- Barcode -->
                    <td>
                        <?php echo form_input(array(
                            'name'  => 'supplier_bar_code_' . $row->supplier_id,
                            'class' => 'md-form-input',
                            'style' => 'text-align:right; min-width:140px;',
                            'value' => $row->supplier_bar_code
                        )); ?>
                    </td>

                    <!-- Item number (code fournisseur) -->
                    <td>
                        <?php echo form_input(array(
                            'name'  => 'supplier_item_number_' . $row->supplier_id,
                            'class' => 'md-form-input',
                            'style' => 'text-align:right; min-width:140px;',
                            'value' => $row->supplier_item_number
                        )); ?>
                    </td>
                </tr>
                <?php
                    $_SESSION['suppliers_id'][$row->supplier_id] = $row->supplier_id;
                }
                ?>
            </tbody>
        </table>
    </div>

    <div style="text-align:right; margin-top:14px;">
        <button type="submit" name="submit_update" class="md-btn md-btn-primary md-btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Valider les modifications
        </button>
    </div>
    </form>
</div>
<?php } ?>

<!-- Bouton Ajouter -->
<div style="margin-bottom:16px;">
    <button type="button" id="btn_show_add_supplier" class="md-btn md-btn-success md-btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('suppliers_supplier'); ?>
    </button>
</div>

<!-- ============================================================
     CARD: Ajouter un fournisseur (add form)
     ============================================================ -->
<div class="md-card" id="supplier_add_card" style="display:none;">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="8.5" cy="7" r="4"></circle>
            <line x1="20" y1="8" x2="20" y2="14"></line>
            <line x1="23" y1="11" x2="17" y2="11"></line>
        </svg>
        <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('suppliers_supplier'); ?>
    </div>

    <?php echo form_open('items/item_supplier_add/', array('id' => 'supplier_add_form')); ?>

    <!-- Row 1 : Par défaut | Fournisseur | N° article | Code barre -->
    <div class="md-form-row">
        <div class="md-form-group" style="flex:0 0 auto;">
            <label class="md-form-label required"><?php echo $this->lang->line('items_supplier_preferred'); ?></label>
            <div class="md-toggle-group" style="padding:0;">
                <label class="md-toggle">
                    <input type="checkbox" class="md-toggle-input" id="add_supplier_preferred_toggle"
                        <?php echo ($_SESSION['transaction_add_supplier_info']->supplier_preferred == 'Y') ? 'checked' : ''; ?>>
                    <span class="md-toggle-slider"></span>
                </label>
                <?php echo form_dropdown(
                    'supplier_preferred',
                    $_SESSION['G']->YorN_pick_list,
                    $_SESSION['transaction_add_supplier_info']->supplier_preferred,
                    'class="md-toggle-select" id="add_supplier_preferred" style="display:none;"'
                ); ?>
            </div>
        </div>
        <div class="md-form-group" style="flex:1;">
            <label class="md-form-label required"><?php echo $this->lang->line('suppliers_supplier'); ?></label>
            <?php echo form_dropdown(
                'supplier_id',
                $_SESSION['supplier_pick_list'],
                $_SESSION['transaction_add_supplier_info']->supplier_id,
                'class="md-form-select required" id="add_supplier_id"'
            ); ?>
        </div>
        <div class="md-form-group" style="flex:0 0 140px;">
            <label class="md-form-label"><?php echo $this->lang->line('items_supplier_item_number'); ?></label>
            <?php echo form_input(array(
                'name'  => 'supplier_item_number',
                'id'    => 'add_supplier_item_number',
                'class' => 'md-form-input',
                'value' => $_SESSION['transaction_add_supplier_info']->supplier_item_number
            )); ?>
        </div>
        <div class="md-form-group" style="flex:0 0 160px;">
            <label class="md-form-label"><?php echo $this->lang->line('items_barcode'); ?></label>
            <?php echo form_input(array(
                'name'  => 'supplier_bar_code',
                'id'    => 'add_supplier_bar_code',
                'class' => 'md-form-input',
                'value' => $_SESSION['transaction_add_supplier_info']->supplier_bar_code
            )); ?>
        </div>
    </div>

    <!-- Row 2 : Prix achat HT | Réappro | Qté lot | Min cde | Min stock | Bouton -->
    <div class="md-form-row" style="align-items:flex-end;">
        <div class="md-form-group" style="flex:0 0 90px;">
            <label class="md-form-label required"><?php echo $cost_price_label; ?></label>
            <?php echo form_input(array(
                'name'  => 'supplier_cost_price',
                'id'    => 'add_supplier_cost_price',
                'class' => 'md-form-input required',
                'style' => 'text-align:right; width:90px;',
                'value' => $_SESSION['transaction_add_supplier_info']->supplier_cost_price,
                'size'  => 8
            )); ?>
        </div>
        <div class="md-form-group" style="flex:0 0 auto;">
            <label class="md-form-label required"><?php echo $this->lang->line('items_reorder_policy'); ?></label>
            <div class="md-toggle-group" style="padding:0;">
                <label class="md-toggle">
                    <input type="checkbox" class="md-toggle-input" id="add_supplier_reorder_toggle"
                        <?php echo ($_SESSION['transaction_add_supplier_info']->supplier_reorder_policy == 'Y') ? 'checked' : ''; ?>>
                    <span class="md-toggle-slider"></span>
                </label>
                <?php echo form_dropdown(
                    'supplier_reorder_policy',
                    $_SESSION['G']->YorN_pick_list,
                    $_SESSION['transaction_add_supplier_info']->supplier_reorder_policy,
                    'class="md-toggle-select" id="add_supplier_reorder_policy" style="display:none;"'
                ); ?>
            </div>
        </div>
        <div class="md-form-group" style="flex:0 0 90px;">
            <label class="md-form-label"><?php echo $this->lang->line('items_reorder_pack_size'); ?></label>
            <?php echo form_input(array(
                'name'  => 'supplier_reorder_pack_size',
                'id'    => 'add_supplier_reorder_pack_size',
                'class' => 'md-form-input',
                'style' => 'text-align:right;',
                'value' => $_SESSION['transaction_add_supplier_info']->supplier_reorder_pack_size
            )); ?>
        </div>
        <div class="md-form-group" style="flex:0 0 90px;">
            <label class="md-form-label"><?php echo $this->lang->line('items_min_order_qty'); ?></label>
            <?php echo form_input(array(
                'name'  => 'supplier_min_order_qty',
                'id'    => 'add_supplier_min_order_qty',
                'class' => 'md-form-input',
                'style' => 'text-align:right;',
                'value' => $_SESSION['transaction_add_supplier_info']->supplier_min_order_qty
            )); ?>
        </div>
        <div class="md-form-group" style="flex:0 0 90px;">
            <label class="md-form-label"><?php echo $this->lang->line('items_min_stock_qty'); ?></label>
            <?php echo form_input(array(
                'name'  => 'supplier_min_stock_qty',
                'id'    => 'add_supplier_min_stock_qty',
                'class' => 'md-form-input',
                'style' => 'text-align:right;',
                'value' => $_SESSION['transaction_add_supplier_info']->supplier_min_stock_qty
            )); ?>
        </div>
        <div style="flex:1;"></div>
        <div class="md-form-group" style="flex:0 0 auto; margin-bottom:16px;">
            <?php echo form_submit(array(
                'name'  => 'submit',
                'id'    => 'submit_add',
                'value' => $this->lang->line('common_submit'),
                'class' => 'md-btn md-btn-primary md-btn-sm'
            )); ?>
        </div>
    </div>

    <?php echo form_close(); ?>

    <!-- Required fields note -->
    <div class="md-required-note">
        <span>*</span> <?php echo $this->lang->line('common_fields_required_message'); ?>
    </div>
</div>
