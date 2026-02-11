<?php
$suspended = $_SESSION['suspended_receives'] ?? array();
?>

<style>
/* --- Suspended PO modal (rsp-) --- */
.rsp-table { width: 100%; border-collapse: collapse; }
.rsp-table th {
    font-size: 0.68em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); padding: 0.3em 0.5em;
    border-bottom: 2px solid var(--border-color, #e2e8f0); white-space: nowrap;
}
.rsp-table td {
    padding: 0.3em 0.5em; font-size: 0.88em;
    border-bottom: 1px solid color-mix(in srgb, var(--border-color, #e2e8f0) 50%, transparent);
    color: var(--text-primary, #1e293b);
}
.rsp-table tr:last-child td { border-bottom: none; }
.rsp-table tr:hover td { background: color-mix(in srgb, var(--primary, #2563eb) 4%, transparent); }
.rsp-id { font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
.rsp-date { color: var(--text-secondary, #64748b); font-size: 0.9em; white-space: nowrap; }
.rsp-supplier { font-weight: 500; }
.rsp-comment { color: var(--text-secondary, #64748b); font-size: 0.9em; max-width: 200px; overflow: hidden; text-overflow: ellipsis; }
.rsp-actions { display: flex; gap: 0.4em; justify-content: center; }
.rsp-action-btn {
    display: flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc); color: var(--text-secondary, #64748b);
    text-decoration: none; transition: all 0.15s;
}
.rsp-action-btn:hover { border-color: var(--primary, #2563eb); color: var(--primary, #2563eb); background: var(--bg-container, #fff); }
.rsp-action-btn.rsp-delete:hover { border-color: var(--danger, #ef4444); color: var(--danger, #ef4444); }
.rsp-action-btn.rsp-merge:hover { border-color: var(--secondary, #8b5cf6); color: var(--secondary, #8b5cf6); }
.rsp-empty { text-align: center; padding: 1.5em; font-size: 0.88em; font-style: italic; color: var(--text-secondary, #94a3b8); }
</style>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 780px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(245,158,11,0.12); color: #d97706; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;"><?php echo $_SESSION['title']; ?></h2>
            <span class="md-modal-ref"><?php echo count($suspended); ?> commande<?php echo count($suspended) > 1 ? 's' : ''; ?></span>
        </div>
    </div>
    <div class="md-modal-header-actions">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>
    </div>
</div>

<!-- Messages -->
<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<!-- Body -->
<div class="md-modal-body" style="padding: 0.4em 0.8em; max-height: 80vh; overflow-y: auto;">

<?php if (!empty($suspended)) { ?>
<div class="md-card" style="padding: 0.4em 0.6em;">
    <table class="rsp-table">
        <thead>
            <tr>
                <th style="text-align: left;"><?php echo $this->lang->line('receivings_id') ?: 'ID'; ?></th>
                <th style="text-align: left;"><?php echo $this->lang->line('sales_date') ?: 'Date'; ?></th>
                <th style="text-align: left;"><?php echo $this->lang->line('recvs_supplier') ?: 'Fournisseur'; ?></th>
                <th style="text-align: left;"><?php echo $this->lang->line('recvs_comment') ?: 'Commentaire'; ?></th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($suspended as $row) {
                $supplier_name = '';
                if (isset($row['supplier_id'])) {
                    $sup = $this->Supplier->get_info($row['supplier_id']);
                    $supplier_name = $sup->first_name . ' ' . $sup->last_name;
                }
            ?>
            <tr>
                <td class="rsp-id">SUSP-<?php echo $row['receiving_id']; ?></td>
                <td class="rsp-date"><?php echo date('d/m/Y', strtotime($row['receiving_time'])); ?></td>
                <td class="rsp-supplier"><?php echo $supplier_name ?: '&mdash;'; ?></td>
                <td class="rsp-comment"><?php echo $row['comment']; ?></td>
                <td>
                    <div class="rsp-actions">
                        <a href="<?php echo site_url($_SESSION['controller_name'].'/unsuspend/'.$row['receiving_id']); ?>" class="rsp-action-btn" title="<?php echo $this->lang->line('receivings_reactivate') ?: 'R&eacute;activer'; ?>">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                        </a>
                        <a href="<?php echo site_url($_SESSION['controller_name'].'/delete/'.$row['receiving_id']); ?>" class="rsp-action-btn rsp-delete" title="<?php echo $this->lang->line('receivings_delete') ?: 'Supprimer'; ?>">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </a>
                        <a href="<?php echo site_url($_SESSION['controller_name'].'/merge_1/'.$row['receiving_id']); ?>" class="rsp-action-btn rsp-merge" title="<?php echo $this->lang->line('receivings_merge') ?: 'Fusionner'; ?>">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="18" r="3"></circle><circle cx="6" cy="6" r="3"></circle><path d="M13 6h3a2 2 0 0 1 2 2v7"></path><line x1="6" y1="9" x2="6" y2="21"></line></svg>
                        </a>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php } else { ?>
<div class="rsp-empty">Aucune commande en attente.</div>
<?php } ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.35em 0.8em;">
    <div class="md-modal-footer-left">
        <span style="font-size: 0.75em; color: var(--text-secondary, #94a3b8);">
            <?php echo count($suspended); ?> commande<?php echo count($suspended) > 1 ? 's' : ''; ?> en attente
        </span>
    </div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
            Fermer
        </a>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->
