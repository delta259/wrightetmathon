<?php
/**
 * Tab content partial: Stock
 * Combines inventory adjustment (show_dialog 3/17) and inventory history (show_dialog 4)
 * Used by modal_wrapper.php and AJAX tab loading
 */

$item_id = $_SESSION['transaction_info']->item_id;
$current_qty = $_SESSION['transaction_info']->quantity;
?>

<!-- Messages -->
<?php include(APPPATH . 'views/partial/show_messages.php'); ?>

<!-- ============================================================
     ROW: Stock actuel + Ajustement (côte à côte)
     ============================================================ -->
<div style="display:flex; gap:16px; margin-bottom:16px; flex-wrap:wrap;">
    <!-- CARD: Stock actuel -->
    <div class="md-card" style="flex:0 0 auto; min-width:180px; margin-bottom:0;">
        <div class="md-card-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Stock actuel
        </div>

        <div style="display:flex; align-items:center; gap:12px; padding:8px 0;">
            <span class="md-form-label" style="margin:0;"><?php echo $this->lang->line('items_current_quantity'); ?></span>
            <span class="md-price-value" style="font-size:24px; font-weight:bold;"><?php echo round($current_qty, 0); ?></span>
        </div>
    </div>

    <!-- CARD: Ajustement stock -->
    <div class="md-card" style="flex:1; min-width:300px; margin-bottom:0;">
        <div class="md-card-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                <polyline points="17 6 23 6 23 12"></polyline>
            </svg>
            <?php echo $this->lang->line('items_add_minus'); ?>
        </div>

        <?php echo form_open('items/save_inventory/' . $item_id, array('id' => 'inventory_form')); ?>

        <div class="md-form-row" style="align-items:flex-end;">
            <div class="md-form-group" style="flex:0 0 100px;">
                <label class="md-form-label required"><?php echo $this->lang->line('items_add_minus'); ?></label>
                <?php echo form_input(array(
                    'name'      => 'newquantity',
                    'id'        => 'newquantity',
                    'type'      => 'number',
                    'class'     => 'md-form-input required',
                    'style'     => 'text-align:right;',
                    'value'     => 0,
                    'autofocus' => 'autofocus'
                )); ?>
            </div>

            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('items_inventory_comments'); ?></label>
                <?php
                $this->load->helper('date');
                $now = time();
                $default_comment = $this->lang->line('reports_rolling') . ' - ' . unix_to_human($now, TRUE, 'eu');
                echo form_input(array(
                    'name'  => 'trans_comment',
                    'id'    => 'trans_comment',
                    'class' => 'md-form-input',
                    'value' => $default_comment
                ));
                ?>
            </div>

            <div class="md-form-group" style="flex:0 0 auto; margin-bottom:14px;">
                <button type="submit" name="submit" id="submit_inventory" class="md-btn md-btn-primary md-btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php echo $this->lang->line('common_submit'); ?>
                </button>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<!-- ============================================================
     CARD: Historique des mouvements
     ============================================================ -->
<?php if (isset($_SESSION['inventory_info']) && count($_SESSION['inventory_info']) > 0) { ?>
<div class="md-card">
    <div class="md-card-title">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        <?php echo $this->lang->line('items_inventory_tracking'); ?>
    </div>

    <div style="overflow-x:auto;">
        <table class="md-table">
            <thead>
                <tr>
                    <th><?php echo $this->lang->line('sales_date'); ?></th>
                    <th><?php echo $this->lang->line('sales_employee'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('items_stock_before'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('items_stock_movement'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('items_stock_after'); ?></th>
                    <th><?php echo $this->lang->line('sales_comments'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['inventory_info'] as $row) { ?>
                <tr>
                    <td style="white-space:nowrap;"><?php echo $row['trans_date']; ?></td>
                    <td>
                        <?php
                        $employee = $this->Employee->get_info($row['trans_user']);
                        echo htmlspecialchars($employee->first_name . ' ' . $employee->last_name);
                        ?>
                    </td>
                    <td style="text-align:right; font-family:monospace;"><?php echo $row['trans_stock_before']; ?></td>
                    <td style="text-align:right; font-family:monospace; font-weight:600; color:<?php echo ($row['trans_inventory'] >= 0) ? 'var(--success-text)' : 'var(--danger-text)'; ?>;">
                        <?php echo ($row['trans_inventory'] >= 0 ? '+' : '') . $row['trans_inventory']; ?>
                    </td>
                    <td style="text-align:right; font-family:monospace;"><?php echo $row['trans_stock_after']; ?></td>
                    <td style="color:var(--text-secondary); font-size:12px;"><?php echo htmlspecialchars($row['trans_comment']); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>
