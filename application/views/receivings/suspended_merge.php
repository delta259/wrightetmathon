<?php
$suspended = $_SESSION['suspended_receives'] ?? array();
?>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 680px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.4em 0.8em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(139,92,246,0.12); color: #7c3aed; width: 32px; height: 32px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="18" cy="18" r="3"></circle><circle cx="6" cy="6" r="3"></circle>
                <path d="M13 6h3a2 2 0 0 1 2 2v7"></path><line x1="6" y1="9" x2="6" y2="21"></line>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1em;"><?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).' '.$_SESSION['title']; ?></h2>
            <span class="md-modal-ref">Fusion de commandes</span>
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
                <th style="text-align: center;"><?php echo $this->lang->line('receivings_merge') ?: 'Fusionner'; ?></th>
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
                <td class="rsp-id">SUSR-<?php echo $row['receiving_id']; ?></td>
                <td class="rsp-date"><?php echo date('d/m/Y', strtotime($row['receiving_time'])); ?></td>
                <td class="rsp-supplier"><?php echo $supplier_name ?: '&mdash;'; ?></td>
                <td class="rsp-comment"><?php echo $row['comment']; ?></td>
                <td style="text-align: center;">
                    <a href="<?php echo site_url($_SESSION['controller_name'].'/merge_2/'.$row['receiving_id']); ?>" class="rsp-action-btn rsp-merge" title="Fusionner">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="18" r="3"></circle><circle cx="6" cy="6" r="3"></circle><path d="M13 6h3a2 2 0 0 1 2 2v7"></path><line x1="6" y1="9" x2="6" y2="21"></line></svg>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php } else { ?>
<div class="rsp-empty">Aucune commande disponible pour la fusion.</div>
<?php } ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.35em 0.8em;">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary" style="padding: 0.35em 0.8em; font-size: 0.85em;">
            Fermer
        </a>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->
