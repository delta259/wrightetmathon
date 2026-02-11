<?php
// Only include header_popup for non-AJAX requests (avoids duplicate CSS/popbg on tab switch)
if (!$this->input->is_ajax_request()) {
    $this->load->view("partial/header_popup");
}
?>

<?php
// Number format
$pieces = array();
$pieces = explode("/", $this->config->item('numberformat'));

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

<div class="md-modal-overlay">
<div class="md-modal">

<!-- ========== HEADER ========== -->
<?php include(APPPATH . 'views/items/partials/modal_header.php'); ?>

<!-- ========== TAB BAR ========== -->
<?php include(APPPATH . 'views/items/partials/tab_bar.php'); ?>

<!-- ========== BODY ========== -->
<div class="md-modal-body">

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
                        <th><?php echo $this->lang->line('items_supplier_item_number'); ?></th>
                        <th><?php echo $cost_price_label; ?></th>
                        <th><?php echo $this->lang->line('items_reorder_policy'); ?></th>
                        <th title="<?php echo $this->lang->line('items_reorder_pack_size'); ?>">Qt&eacute; lot</th>
                        <th title="<?php echo $this->lang->line('items_min_order_qty'); ?>">Min cde</th>
                        <th title="<?php echo $this->lang->line('items_min_stock_qty'); ?>">Min stock</th>
                        <th><?php echo $this->lang->line('items_barcode'); ?></th>
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

                        <!-- Item number -->
                        <td>
                            <?php echo form_input(array(
                                'name'  => 'supplier_item_number_' . $row->supplier_id,
                                'class' => 'md-form-input',
                                'style' => 'text-align:right;',
                                'value' => $row->supplier_item_number
                            )); ?>
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
                                'style' => 'text-align:right;',
                                'value' => $row->supplier_bar_code,
                                'size'  => 20
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

</div><!-- /md-modal-body -->

<!-- ========== FOOTER ========== -->
<div class="md-modal-footer">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
            Fermer
        </a>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<?php if (!$this->input->is_ajax_request()) { ?>
<script type="text/javascript">
$('#btn_show_add_supplier').click(function(){
    var card = $('#supplier_add_card');
    if (card.css('display') === 'none') {
        // Valeurs par defaut — toggles
        $('#add_supplier_preferred_toggle').attr('checked', true);
        $('#add_supplier_preferred').val('Y');
        $('#add_supplier_preferred_toggle').parents('.md-toggle-group').find('.md-toggle-value').text('Y');
        // Passer tous les fournisseurs existants en non-prefere (toggles + hidden selects)
        $('#supplier_update_form .md-toggle-group').each(function(){
            var sel = $(this).find('.md-toggle-select[name^="supplier_preferred_"]');
            if (sel.length) {
                sel.val('N');
                $(this).find('.md-toggle-input').attr('checked', false);
            }
        });
        $('#add_supplier_reorder_toggle').attr('checked', true);
        $('#add_supplier_reorder_policy').val('Y');
        $('#add_supplier_reorder_toggle').parents('.md-toggle-group').find('.md-toggle-value').text('Y');
        $('#add_supplier_reorder_pack_size').val('1');
        $('#add_supplier_min_order_qty').val('1');
        $('#add_supplier_min_stock_qty').val('1');
        // Reprendre le code barre d'un fournisseur existant
        var existingBarcode = '';
        $('input[name^="supplier_bar_code_"]').each(function(){
            var v = $(this).val();
            if (v && v !== '' && existingBarcode === '') {
                existingBarcode = v;
            }
        });
        if (existingBarcode !== '') {
            $('#add_supplier_bar_code').val(existingBarcode);
        }
        card.slideDown(200);
        $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line></svg> Annuler');
    } else {
        card.slideUp(200);
        $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> <?php echo $this->lang->line("common_add") . " " . $this->lang->line("suppliers_supplier"); ?>');
    }
});

// Formulaire AJOUT : Quand on active le réappro, mettre les valeurs à 1
$('#add_supplier_reorder_toggle').change(function(){
    var isChecked = $(this).is(':checked');
    $('#add_supplier_reorder_policy').val(isChecked ? 'Y' : 'N');
    if (isChecked) {
        // Si réappro = Oui, mettre 1 dans les champs si vides ou à 0
        var packSize = $('#add_supplier_reorder_pack_size').val();
        var minOrder = $('#add_supplier_min_order_qty').val();
        var minStock = $('#add_supplier_min_stock_qty').val();
        if (!packSize || packSize == '' || packSize == '0') {
            $('#add_supplier_reorder_pack_size').val('1');
        }
        if (!minOrder || minOrder == '' || minOrder == '0') {
            $('#add_supplier_min_order_qty').val('1');
        }
        if (!minStock || minStock == '' || minStock == '0') {
            $('#add_supplier_min_stock_qty').val('1');
        }
    }
});

// Formulaire MODIFICATION : Quand on active le réappro sur un fournisseur existant
$('#supplier_update_form').on('change', '.md-toggle-input', function(){
    var $row = $(this).closest('tr');
    var $select = $(this).closest('.md-toggle-group').find('.md-toggle-select');
    var selectName = $select.attr('name') || '';
    var isChecked = $(this).is(':checked');

    // Mettre à jour la valeur du select caché
    $select.val(isChecked ? 'Y' : 'N');

    // Si c'est le toggle reorder_policy et qu'on l'active
    if (selectName.indexOf('supplier_reorder_policy_') === 0 && isChecked) {
        var supplierId = selectName.replace('supplier_reorder_policy_', '');
        var $packSize = $row.find('input[name="supplier_reorder_pack_size_' + supplierId + '"]');
        var $minOrder = $row.find('input[name="supplier_min_order_qty_' + supplierId + '"]');
        var $minStock = $row.find('input[name="supplier_min_stock_qty_' + supplierId + '"]');

        // Mettre 1 si vide ou 0
        if (!$packSize.val() || $packSize.val() == '' || $packSize.val() == '0') {
            $packSize.val('1');
        }
        if (!$minOrder.val() || $minOrder.val() == '' || $minOrder.val() == '0') {
            $minOrder.val('1');
        }
        if (!$minStock.val() || $minStock.val() == '' || $minStock.val() == '0') {
            $minStock.val('1');
        }
    }
});
</script>
<?php } ?>
