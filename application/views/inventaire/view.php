<?php $this->load->view("partial/header"); ?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
// Modal overlay (after footer, same pattern as items/modal_wrapper)
$this->load->view("partial/header_popup");

$type_labels = array('full' => $this->lang->line('inventaire_type_full'), 'rolling' => $this->lang->line('inventaire_type_rolling'), 'partial' => $this->lang->line('inventaire_type_partial'));
$type_label = isset($type_labels[$session->session_type]) ? $type_labels[$session->session_type] : $session->session_type;
if ($session->session_type === 'partial' && !empty($session->category_name)) {
    $type_label .= ' (' . htmlspecialchars($session->category_name) . ')';
}
if ($session->session_type === 'partial' && !empty($session->cutoff_date)) {
    $type_label .= ' (< ' . date('d/m/Y', strtotime($session->cutoff_date)) . ')';
}

// Status badge
switch ($session->status) {
    case 'in_progress':
        $badge_style = 'background:#fef3c7;color:#92400e;border:1px solid #f59e0b;';
        $badge_text = $this->lang->line('inventaire_status_in_progress');
        break;
    case 'completed':
        $badge_style = 'background:#dcfce7;color:#166534;border:1px solid #22c55e;';
        $badge_text = $this->lang->line('inventaire_status_completed');
        break;
    case 'cancelled':
        $badge_style = 'background:#fef2f2;color:#991b1b;border:1px solid #ef4444;';
        $badge_text = $this->lang->line('inventaire_status_cancelled');
        break;
    default:
        $badge_style = '';
        $badge_text = $session->status;
}

$progress_pct = ($session->total_items > 0) ? round(($session->items_counted / $session->total_items) * 100) : 0;
?>

<div class="md-modal-overlay">
<div class="md-modal" style="max-width:95%;">

<!-- ========== MODAL HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background:var(--bg-card, #f0f9ff);">
            <svg width="28" height="28" fill="none" stroke="var(--modal-primary, #0A6184)" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref">Session #<?php echo $session->id; ?> - <?php echo $type_label; ?></div>
            <h2 class="md-modal-name"><?php echo $this->lang->line('inventaire_view'); ?> - <?php echo date('d/m/Y H:i', strtotime($session->started_at)); ?></h2>
        </div>
    </div>
    <div class="md-modal-header-actions" style="display:flex;align-items:center;gap:8px;">
        <span style="<?php echo $badge_style; ?>padding:4px 12px;border-radius:12px;font-size:0.8rem;font-weight:500;"><?php echo $badge_text; ?></span>
        <a href="<?php echo site_url('inventaire'); ?>" class="md-modal-close" title="<?php echo $this->lang->line('inventaire_back_to_list'); ?>">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </a>
    </div>
</div>

<!-- ========== MODAL BODY ========== -->
<div class="md-modal-body" id="md-modal-body-content" style="padding:16px 24px;">

    <!-- Session Info -->
    <div style="background:var(--bg-card, #f8fafc);border:1px solid var(--border-color, #e2e8f0);border-radius:10px;padding:16px 20px;margin-bottom:16px;">
        <div style="display:flex;gap:32px;flex-wrap:wrap;margin-bottom:12px;">
            <div>
                <span style="font-size:0.75rem;color:var(--modal-text-muted, #64748b);text-transform:uppercase;letter-spacing:0.05em;">Type</span>
                <div style="font-weight:600;color:var(--modal-text, #1e293b);"><?php echo $type_label; ?></div>
            </div>
            <div>
                <span style="font-size:0.75rem;color:var(--modal-text-muted, #64748b);text-transform:uppercase;letter-spacing:0.05em;"><?php echo $this->lang->line('inventaire_created_by'); ?></span>
                <div style="font-weight:600;color:var(--modal-text, #1e293b);"><?php echo htmlspecialchars($session->first_name . ' ' . $session->last_name); ?></div>
            </div>
            <div>
                <span style="font-size:0.75rem;color:var(--modal-text-muted, #64748b);text-transform:uppercase;letter-spacing:0.05em;"><?php echo $this->lang->line('inventaire_date'); ?></span>
                <div style="font-weight:600;color:var(--modal-text, #1e293b);"><?php echo date('d/m/Y H:i', strtotime($session->started_at)); ?></div>
            </div>
            <?php if ($session->completed_at): ?>
            <div>
                <span style="font-size:0.75rem;color:var(--modal-text-muted, #64748b);text-transform:uppercase;letter-spacing:0.05em;">Termin&eacute; le</span>
                <div style="font-weight:600;color:var(--modal-text, #1e293b);"><?php echo date('d/m/Y H:i', strtotime($session->completed_at)); ?></div>
            </div>
            <?php endif; ?>
            <div>
                <span style="font-size:0.75rem;color:var(--modal-text-muted, #64748b);text-transform:uppercase;letter-spacing:0.05em;"><?php echo $this->lang->line('inventaire_progress'); ?></span>
                <div style="font-weight:600;color:var(--modal-text, #1e293b);"><?php echo $session->items_counted; ?>/<?php echo $session->total_items; ?> (<?php echo $progress_pct; ?>%)</div>
            </div>
        </div>
        <?php if ($session->applied == 1): ?>
        <div style="background:#dcfce7;border:1px solid #22c55e;border-radius:6px;padding:8px 14px;display:inline-flex;align-items:center;gap:8px;font-size:0.85rem;color:#166534;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <?php echo $this->lang->line('inventaire_applied'); ?>
            <?php if ($session->applied_at): ?>
            - <?php echo date('d/m/Y H:i', strtotime($session->applied_at)); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($session->notes)): ?>
        <div style="margin-top:12px;padding:10px 14px;background:var(--modal-header-bg, #f1f5f9);border-radius:6px;font-size:0.85rem;color:var(--modal-text-muted, #475569);">
            <strong><?php echo $this->lang->line('inventaire_notes'); ?> :</strong> <?php echo nl2br(htmlspecialchars($session->notes)); ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Items Table -->
    <?php if ($items && $items->num_rows() > 0): ?>
    <table id="view_table" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th><?php echo $this->lang->line('inventaire_category'); ?></th>
                <th><?php echo $this->lang->line('inventaire_reference'); ?></th>
                <th><?php echo $this->lang->line('inventaire_designation'); ?></th>
                <th style="text-align:right;"><?php echo $this->lang->line('inventaire_expected_stock'); ?></th>
                <th style="text-align:right;"><?php echo $this->lang->line('inventaire_counted_qty'); ?></th>
                <th style="text-align:right;"><?php echo $this->lang->line('inventaire_adjustment'); ?></th>
                <th><?php echo $this->lang->line('inventaire_comment'); ?></th>
                <th style="text-align:center;"><?php echo $this->lang->line('inventaire_applied'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items->result() as $item):
                $is_counted = !empty($item->counted_at);
                $adj = (float)($item->adjustment ?? 0);
                $adj_color = ($adj > 0) ? '#27DA16' : (($adj < 0) ? '#DA162B' : 'var(--modal-text-muted, #475569)');
                $adj_display = $is_counted ? number_format($adj, 2, ',', ' ') : '-';
                if ($adj > 0) $adj_display = '+' . $adj_display;
            ?>
            <tr style="<?php echo $is_counted ? '' : 'opacity:0.5;'; ?>">
                <td style="font-size:0.8rem;color:var(--modal-text-muted, #64748b);"><?php echo htmlspecialchars($item->category_name ?? ''); ?></td>
                <td style="font-weight:500;color:var(--modal-text, #1e293b);"><?php echo htmlspecialchars($item->item_number); ?></td>
                <td style="color:var(--modal-text, #1e293b);"><?php echo htmlspecialchars($item->item_name); ?></td>
                <td style="text-align:right;color:var(--modal-text, #1e293b);"><?php echo number_format((float)$item->expected_quantity, 2, ',', ' '); ?></td>
                <td style="text-align:right;font-weight:600;color:var(--modal-text, #1e293b);">
                    <?php echo $is_counted ? number_format((float)$item->counted_quantity, 2, ',', ' ') : '-'; ?>
                </td>
                <td style="text-align:right;font-weight:600;color:<?php echo $adj_color; ?>;">
                    <?php echo $adj_display; ?>
                </td>
                <td style="font-style:italic;color:var(--modal-text-muted, #64748b);font-size:0.85rem;"><?php echo htmlspecialchars($item->comment ?? ''); ?></td>
                <td style="text-align:center;">
                    <?php if ($item->applied == 1): ?>
                    <svg width="16" height="16" fill="none" stroke="#22c55e" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    <?php elseif ($is_counted): ?>
                    <svg width="16" height="16" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <?php else: ?>
                    <span style="color:#cbd5e1;">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="text-align:center;padding:40px 20px;color:var(--modal-text-muted, #64748b);">
        <p><?php echo $this->lang->line('inventaire_no_items'); ?></p>
    </div>
    <?php endif; ?>

</div><!-- /md-modal-body -->

<!-- ========== MODAL FOOTER ========== -->
<div class="md-modal-footer">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('inventaire'); ?>" class="md-btn md-btn-secondary" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:6px;font-size:0.85rem;font-weight:500;text-decoration:none;background:var(--modal-bg, #fff);color:var(--modal-text, #1e293b);border:1px solid var(--modal-border, #e2e8f0);">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Fermer
        </a>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<style>
#view_table thead th {
    background: #4386a1cc;
    color: #fff;
    padding: 8px 10px;
    text-align: left;
    font-weight: 600;
    font-size: 0.82rem;
    white-space: nowrap;
}
#view_table tbody td {
    padding: 6px 10px;
    border-bottom: 1px solid var(--modal-border, #e2e8f0);
    font-size: 0.85rem;
}
#view_table tbody tr:hover {
    background: var(--modal-header-bg, #f1f5f9);
}
</style>
