<?php
// Only include header_popup for non-AJAX requests
if (!$this->input->is_ajax_request()) {
    $this->load->view("partial/header_popup");
}

// Number format
$pieces = explode("/", $this->config->item('numberformat'));
$nf_dec = $pieces[0];
$nf_pt  = $pieces[1];
$nf_sep = $pieces[2];

$suspended = $_SESSION['suspended_sales'] ?? array();
$has_sales = (count($suspended) > 0);
?>

<div class="md-modal-overlay">
<div class="md-modal" style="max-width: 780px;">

<!-- ========== HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-name">Ventes suspendues</div>
            <div class="md-modal-ref"><?php echo $has_sales ? count($suspended) . ' vente(s) en attente' : 'Aucune vente suspendue'; ?></div>
        </div>
    </div>
    <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </a>
</div>

<!-- ========== BODY ========== -->
<div class="md-modal-body" style="padding: 0;">

    <!-- Messages -->
    <?php include(APPPATH . 'views/partial/show_messages.php'); ?>

    <?php if ($has_sales): ?>
    <div class="suspended-list">
        <?php foreach ($suspended as $sale):
            // Get customer info
            $cust_name = '';
            if (!empty($sale['customer_id'])) {
                $customer = $this->Customer->get_info($sale['customer_id']);
                if ($customer && isset($customer->first_name)) {
                    $cust_name = trim($customer->first_name . ' ' . $customer->last_name);
                }
            }

            // Get employee info
            $emp_name = '';
            if (!empty($sale['employee_id'])) {
                $emp = $this->Employee->get_info($sale['employee_id']);
                if ($emp && isset($emp->first_name)) {
                    $emp_name = trim($emp->first_name . ' ' . $emp->last_name);
                }
            }

            // Get items for this suspended sale
            $sale_items = $this->Sale_suspended->get_sale_items($sale['sale_id'])->result();
            $item_count = count($sale_items);
            $total_approx = 0;
            foreach ($sale_items as $si) {
                $line_total = $si->quantity_purchased * $si->item_unit_price * (1 - $si->discount_percent / 100);
                $total_approx += $line_total;
            }

            $sale_date = date('d/m/Y H:i', strtotime($sale['sale_time']));
        ?>
        <div class="suspended-card">
            <div class="suspended-card-header" data-susp-idx="<?php echo $sale['sale_id']; ?>">
                <div class="suspended-card-left">
                    <span class="suspended-card-id">#<?php echo $sale['sale_id']; ?></span>
                    <span class="suspended-card-date"><?php echo $sale_date; ?></span>
                    <?php if ($emp_name): ?>
                    <span class="suspended-card-employee"><?php echo htmlspecialchars($emp_name); ?></span>
                    <?php endif; ?>
                </div>
                <div class="suspended-card-right">
                    <?php if ($cust_name): ?>
                    <span class="suspended-card-customer">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <?php echo htmlspecialchars($cust_name); ?>
                    </span>
                    <?php endif; ?>
                    <span class="suspended-card-count"><?php echo $item_count; ?> art.</span>
                    <span class="suspended-card-total"><?php echo number_format($total_approx, $nf_dec, $nf_pt, $nf_sep); ?></span>
                    <svg class="suspended-card-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </div>

            <!-- Items detail (hidden) -->
            <?php if ($item_count > 0): ?>
            <div class="suspended-card-items" data-susp-items="<?php echo $sale['sale_id']; ?>">
                <table class="suspended-items-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th class="susp-col-qty">Qté</th>
                            <th class="susp-col-price">PU TTC</th>
                            <th class="susp-col-disc">Rem.</th>
                            <th class="susp-col-total">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sale_items as $si):
                            $line_total = $si->quantity_purchased * $si->item_unit_price * (1 - $si->discount_percent / 100);
                        ?>
                        <tr>
                            <td title="<?php echo htmlspecialchars($si->description); ?>"><?php echo htmlspecialchars($si->description); ?></td>
                            <td class="susp-col-qty"><?php echo (int)$si->quantity_purchased; ?></td>
                            <td class="susp-col-price"><?php echo number_format($si->item_unit_price, $nf_dec, $nf_pt, $nf_sep); ?></td>
                            <td class="susp-col-disc"><?php echo ($si->discount_percent > 0) ? '<span class="susp-disc-val">-'.$si->discount_percent.'%</span>' : ''; ?></td>
                            <td class="susp-col-total"><?php echo number_format($line_total, $nf_dec, $nf_pt, $nf_sep); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (!empty($sale['comment'])): ?>
                <div class="suspended-card-comment"><?php echo htmlspecialchars($sale['comment']); ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="suspended-card-actions">
                <a href="<?php echo site_url($_SESSION['controller_name'] . '/unsuspend/' . $sale['sale_id']); ?>" class="md-btn md-btn-primary md-btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    Reprendre
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="suspended-empty">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>Aucune vente suspendue</span>
    </div>
    <?php endif; ?>
</div>

<!-- ========== FOOTER ========== -->
<div class="md-modal-footer">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">Fermer</a>
    </div>
</div>

</div><!-- end .md-modal -->
</div><!-- end .md-modal-overlay -->

<style>
/* Suspended sales list */
.suspended-list {
    max-height: 500px;
    overflow-y: auto;
}

.suspended-card {
    border-bottom: 1px solid var(--border-color, #c5cbd3);
}
.suspended-card:last-child { border-bottom: none; }

.suspended-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    cursor: pointer;
    transition: background 0.15s;
    gap: 12px;
}
.suspended-card-header:hover {
    background: var(--bg-hover, #f1f5f9);
}

.suspended-card-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.suspended-card-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.suspended-card-id {
    font-size: 0.78em;
    color: var(--text-secondary, #64748b);
    background: var(--bg-card, #f8fafc);
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px solid var(--border-color, #c5cbd3);
    font-weight: 600;
}
.suspended-card-date {
    font-weight: 600;
    font-size: 0.88em;
    color: var(--text-primary, #1e293b);
}
.suspended-card-employee {
    font-size: 0.82em;
    color: var(--text-secondary, #64748b);
}
.suspended-card-customer {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.82em;
    color: var(--primary, #2563eb);
    font-weight: 600;
}
.suspended-card-count {
    font-size: 0.78em;
    color: var(--text-secondary, #64748b);
    background: var(--bg-card, #f8fafc);
    padding: 2px 8px;
    border-radius: 4px;
}
.suspended-card-total {
    font-weight: 700;
    font-size: 0.95em;
    color: var(--primary, #2563eb);
    min-width: 65px;
    text-align: right;
}
.suspended-card-arrow {
    color: var(--text-secondary, #64748b);
    transition: transform 0.2s;
    flex-shrink: 0;
    opacity: 0.5;
}
.suspended-card-arrow.expanded {
    transform: rotate(90deg);
    opacity: 1;
}

/* Item details */
.suspended-card-items {
    display: none;
    padding: 0 20px 8px 20px;
    background: var(--bg-hover, #f1f5f9);
}
.suspended-card-items.visible { display: block; }

.suspended-items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82em;
}
.suspended-items-table th {
    text-align: left;
    padding: 4px 8px;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    border-bottom: 1px solid var(--border-color, #c5cbd3);
    font-size: 0.9em;
}
.suspended-items-table td {
    padding: 4px 8px;
    color: var(--text-primary, #1e293b);
    border-bottom: 1px solid var(--border-color, #c5cbd3);
}
.suspended-items-table tr:last-child td { border-bottom: none; }
.susp-col-qty { text-align: center; width: 40px; }
.susp-col-price { text-align: right; width: 70px; }
.susp-col-disc { text-align: center; width: 50px; }
.susp-col-total { text-align: right; width: 75px; font-weight: 600; }
.susp-disc-val { color: var(--danger, #ef4444); font-size: 0.9em; font-weight: 600; }

/* Comment */
.suspended-card-comment {
    margin-top: 6px;
    padding: 4px 10px;
    font-size: 0.82em;
    color: var(--text-secondary, #64748b);
    font-style: italic;
    border-left: 2px solid var(--warning, #f59e0b);
    background: var(--bg-card, #fff);
    border-radius: 0 4px 4px 0;
}

/* Actions bar */
.suspended-card-actions {
    display: flex;
    justify-content: flex-end;
    padding: 6px 20px 10px;
    border-bottom: 1px solid var(--border-color, #c5cbd3);
}
.suspended-card:last-child .suspended-card-actions { border-bottom: none; }

.md-btn-sm {
    padding: 4px 14px;
    font-size: 0.8em;
    gap: 5px;
}

/* Empty state */
.suspended-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 60px 20px;
    color: var(--text-secondary, #64748b);
    opacity: 0.5;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    // Expand/collapse items
    $('.suspended-card-header').click(function() {
        var idx = $(this).attr('data-susp-idx');
        var items = $('[data-susp-items="' + idx + '"]');
        var arrow = $(this).find('.suspended-card-arrow');
        items.toggleClass('visible');
        arrow.toggleClass('expanded');
    });
});
</script>
