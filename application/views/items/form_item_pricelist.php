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

// Currency label
$currency_sign = $_SESSION['G']->currency_details->currency_sign;
$currency_side = $_SESSION['G']->currency_details->currency_side;
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
         CARD: Tarifs existants (update form)
         ============================================================ -->
    <?php if (isset($_SESSION['transaction_pricelist_info']) && count((array)$_SESSION['transaction_pricelist_info']) > 0) { ?>
    <div class="md-card">
        <div class="md-card-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <?php echo $this->lang->line('common_manage') . ' ' . $this->lang->line('pricelists_pricelist'); ?>
        </div>

        <form action="index.php/items/item_pricelist_update/" method="post" id="pricelist_update_form">
        <div style="overflow-x:auto;">
            <table class="md-table">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th><?php echo $this->lang->line('pricelists_pricelist_name'); ?></th>
                        <th><?php echo $this->lang->line('pricelists_pricelist_description'); ?></th>
                        <th title="<?php echo $this->lang->line('items_unit_price'); ?>">Prix HT</th>
                        <th title="<?php echo $this->lang->line('items_unit_price_with_tax'); ?>">Prix TTC</th>
                        <th title="<?php echo $this->lang->line('common_valid_from'); ?>">Du</th>
                        <th title="<?php echo $this->lang->line('common_valid_to'); ?>">Au</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    unset($_SESSION['pricelists_id']);
                    foreach ($_SESSION['transaction_pricelist_info'] as $key => $row)
                    {
                    ?>
                    <tr>
                        <!-- Delete -->
                        <td>
                            <a href="<?php echo site_url('items/item_pricelist_delete/' . $row->item_id . '/' . $row->pricelist_id); ?>"
                               class="md-delete-link" title="<?php echo $this->lang->line('common_delete'); ?>">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </a>
                        </td>

                        <!-- Pricelist name -->
                        <td style="font-weight:500;">
                            <?php echo htmlspecialchars($row->pricelist_name); ?>
                        </td>

                        <!-- Description -->
                        <td>
                            <?php echo htmlspecialchars($row->pricelist_description); ?>
                        </td>

                        <!-- Unit price HT (read-only) -->
                        <td style="text-align:right; color:var(--text-muted);">
                            <?php echo $row->unit_price; ?>
                        </td>

                        <!-- Unit price TTC (editable) -->
                        <td>
                            <?php echo form_input(array(
                                'name'  => 'unit_price_with_tax_' . $row->pricelist_id,
                                'class' => 'md-form-input required',
                                'style' => 'text-align:right; width:90px;',
                                'size'  => 10,
                                'value' => $row->unit_price_with_tax
                            )); ?>
                        </td>

                        <!-- Valid from -->
                        <td>
                            <?php if ($row->pricelist_id != 1) {
                                echo form_input(array(
                                    'name'  => 'valid_from_' . $row->pricelist_id,
                                    'type'  => 'date',
                                    'class' => 'md-form-input',
                                    'style' => 'width:130px;',
                                    'value' => $row->valid_from_year . '-' . str_pad($row->valid_from_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($row->valid_from_day, 2, '0', STR_PAD_LEFT)
                                ));
                            } ?>
                        </td>

                        <!-- Valid to -->
                        <td>
                            <?php if ($row->pricelist_id != 1) {
                                echo form_input(array(
                                    'name'  => 'valid_to_' . $row->pricelist_id,
                                    'type'  => 'date',
                                    'class' => 'md-form-input',
                                    'style' => 'width:130px;',
                                    'value' => $row->valid_to_year . '-' . str_pad($row->valid_to_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($row->valid_to_day, 2, '0', STR_PAD_LEFT)
                                ));
                            } ?>
                        </td>
                    </tr>
                    <?php
                        $_SESSION['pricelists_id'][$row->pricelist_id] = $row->pricelist_id;
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
        <button type="button" id="btn_show_add_pricelist" class="md-btn md-btn-success md-btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('pricelists_pricelist'); ?>
        </button>
    </div>

    <!-- ============================================================
         CARD: Ajouter un tarif (add form)
         ============================================================ -->
    <div class="md-card" id="pricelist_add_card" style="display:none;">
        <div class="md-card-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <?php echo $this->lang->line('common_add') . ' ' . $this->lang->line('pricelists_pricelist'); ?>
            <span style="font-weight:400; font-size:11px; color:var(--text-muted); margin-left:8px;">
                <?php echo $this->lang->line('common_1_asterisk') . ' ' . $this->lang->line('common_no_validity'); ?>
            </span>
        </div>

        <?php echo form_open('items/item_pricelist_add/', array('id' => 'pricelist_add_form')); ?>

        <!-- Row 1 : Tarif | Prix TTC | Valide du | Valide au | Bouton -->
        <div class="md-form-row" style="align-items:flex-end;">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label required"><?php echo $this->lang->line('pricelists_pricelist'); ?></label>
                <?php echo form_dropdown(
                    'pricelist_id',
                    $_SESSION['G']->pricelist_pick_list,
                    $_SESSION['transaction_add_pricelist_info']->pricelist_id,
                    'class="md-form-select required" id="add_pricelist_id"'
                ); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 90px;">
                <label class="md-form-label required"><?php echo $this->lang->line('items_unit_price_with_tax'); ?></label>
                <?php echo form_input(array(
                    'name'  => 'unit_price_with_tax',
                    'id'    => 'add_unit_price_with_tax',
                    'class' => 'md-form-input required',
                    'style' => 'text-align:right; width:90px;',
                    'value' => $_SESSION['transaction_add_pricelist_info']->unit_price_with_tax
                )); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 160px;">
                <label class="md-form-label"><?php echo $this->lang->line('common_valid_from') . ' *'; ?></label>
                <?php
                $vf_y = $_SESSION['transaction_add_pricelist_info']->valid_from_year;
                $vf_m = str_pad($_SESSION['transaction_add_pricelist_info']->valid_from_month, 2, '0', STR_PAD_LEFT);
                $vf_d = str_pad($_SESSION['transaction_add_pricelist_info']->valid_from_day, 2, '0', STR_PAD_LEFT);
                $vf_iso = ($vf_y != '0000') ? $vf_y . '-' . $vf_m . '-' . $vf_d : '';
                ?>
                <input type="date" id="add_valid_from_picker" class="md-form-input" style="width:160px;" value="<?php echo $vf_iso; ?>">
                <input type="hidden" name="valid_from" id="add_valid_from" value="<?php echo $_SESSION['transaction_add_pricelist_info']->valid_from_day . '/' . $_SESSION['transaction_add_pricelist_info']->valid_from_month . '/' . $_SESSION['transaction_add_pricelist_info']->valid_from_year; ?>">
            </div>
            <div class="md-form-group" style="flex:0 0 160px;">
                <label class="md-form-label"><?php echo $this->lang->line('common_valid_to') . ' *'; ?></label>
                <?php
                $vt_y = $_SESSION['transaction_add_pricelist_info']->valid_to_year;
                $vt_m = str_pad($_SESSION['transaction_add_pricelist_info']->valid_to_month, 2, '0', STR_PAD_LEFT);
                $vt_d = str_pad($_SESSION['transaction_add_pricelist_info']->valid_to_day, 2, '0', STR_PAD_LEFT);
                $vt_iso = ($vt_y != '0000') ? $vt_y . '-' . $vt_m . '-' . $vt_d : '';
                ?>
                <input type="date" id="add_valid_to_picker" class="md-form-input" style="width:160px;" value="<?php echo $vt_iso; ?>">
                <input type="hidden" name="valid_to" id="add_valid_to" value="<?php echo $_SESSION['transaction_add_pricelist_info']->valid_to_day . '/' . $_SESSION['transaction_add_pricelist_info']->valid_to_month . '/' . $_SESSION['transaction_add_pricelist_info']->valid_to_year; ?>">
            </div>
            <div style="flex:1;"></div>
            <div class="md-form-group" style="flex:0 0 auto; margin-bottom:16px;">
                <?php echo form_submit(array(
                    'name'  => 'submit',
                    'id'    => 'submit_add_pricelist',
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
// Sync date pickers (YYYY-MM-DD) to hidden fields (DD/MM/YYYY) on submit
$('#pricelist_add_form').submit(function(){
    var from = $('#add_valid_from_picker').val();
    if (from && from !== '') {
        var p = from.split('-');
        $('#add_valid_from').val(p[2] + '/' + p[1] + '/' + p[0]);
    } else {
        $('#add_valid_from').val('00/00/0000');
    }
    var to = $('#add_valid_to_picker').val();
    if (to && to !== '') {
        var p2 = to.split('-');
        $('#add_valid_to').val(p2[2] + '/' + p2[1] + '/' + p2[0]);
    } else {
        $('#add_valid_to').val('00/00/0000');
    }
});

$('#btn_show_add_pricelist').click(function(){
    var card = $('#pricelist_add_card');
    if (card.css('display') === 'none') {
        card.slideDown(200);
        $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line></svg> Annuler');
    } else {
        card.slideUp(200);
        $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> <?php echo $this->lang->line("common_add") . " " . $this->lang->line("pricelists_pricelist"); ?>');
    }
});
</script>
<?php } ?>
