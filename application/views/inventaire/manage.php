<?php $this->load->view("partial/header"); ?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        <?php echo $this->lang->line('inventaire_sessions'); ?>
    </h1>
    <div class="page-actions">
        <?php if (!$active_session): ?>
        <a href="<?php echo site_url('inventaire/create'); ?>" class="btn-action btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <?php echo $this->lang->line('inventaire_new_session'); ?>
        </a>
        <?php else: ?>
        <a href="#" class="btn-action btn-disabled" style="opacity:0.5;pointer-events:none;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <?php echo $this->lang->line('inventaire_new_session'); ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Messages -->
<?php if (!empty($success_message)): ?>
<div class="alert alert-success" style="background:#dcfce7;border:1px solid #22c55e;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-weight:500;">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:8px;"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <?php echo $success_message; ?>
</div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
<div class="alert alert-error" style="background:#fef2f2;border:1px solid #ef4444;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-weight:500;">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:8px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    <?php echo $error_message; ?>
</div>
<?php endif; ?>


<!-- Sessions Table -->
<div class="table-container">
    <div class="table-wrapper">
        <?php if ($sessions && $sessions->num_rows() > 0): ?>
        <table class="tablesorter" id="sessions_table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?php echo $this->lang->line('inventaire_date'); ?></th>
                    <th><?php echo $this->lang->line('inventaire_type'); ?></th>
                    <th><?php echo $this->lang->line('inventaire_created_by'); ?></th>
                    <th><?php echo $this->lang->line('inventaire_articles'); ?></th>
                    <th><?php echo $this->lang->line('inventaire_counted'); ?></th>
                    <th><?php echo $this->lang->line('inventaire_status'); ?></th>
                    <th><?php echo $this->lang->line('inventaire_actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $type_labels = array(
                    'full' => $this->lang->line('inventaire_type_full'),
                    'rolling' => $this->lang->line('inventaire_type_rolling'),
                    'partial' => $this->lang->line('inventaire_type_partial'),
                    'rolling_category' => $this->lang->line('inventaire_type_partial_category'),
                    'rolling_date' => $this->lang->line('inventaire_type_partial_date')
                );
                foreach ($sessions->result() as $s):
                    $type_label = isset($type_labels[$s->session_type]) ? $type_labels[$s->session_type] : $s->session_type;

                    // Add detail if partial
                    if ($s->session_type === 'partial' && !empty($s->category_name)) {
                        $type_label .= ' (' . htmlspecialchars($s->category_name) . ')';
                    } elseif ($s->session_type === 'partial' && !empty($s->supplier_name)) {
                        $type_label .= ' (' . htmlspecialchars($s->supplier_name) . ')';
                    } elseif ($s->session_type === 'partial' && !empty($s->notes) && strpos($s->notes, 'Recherche:') === 0) {
                        $search_display = trim(str_replace('Recherche:', '', explode('|', $s->notes)[0]));
                        $type_label .= ' (' . htmlspecialchars($search_display) . ')';
                    } elseif ($s->session_type === 'partial' && !empty($s->cutoff_date)) {
                        $type_label .= ' (< ' . date('d/m/Y', strtotime($s->cutoff_date)) . ')';
                    }

                    // Status badge
                    switch ($s->status) {
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
                            $badge_text = $s->status;
                    }

                    // Progress percentage
                    $progress_pct = ($s->total_items > 0) ? round(($s->items_counted / $s->total_items) * 100) : 0;
                ?>
                <tr<?php if ($s->status === 'in_progress'): ?> class="session-row-active" data-href="<?php echo site_url('inventaire/count/' . $s->id); ?>" style="cursor:pointer;"<?php endif; ?>>
                    <td><?php echo $s->id; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($s->started_at)); ?></td>
                    <td><?php echo $type_label; ?></td>
                    <td><?php echo htmlspecialchars($s->first_name . ' ' . $s->last_name); ?></td>
                    <td style="text-align:center;"><?php echo $s->total_items; ?></td>
                    <td style="text-align:center;">
                        <span><?php echo $s->items_counted; ?>/<?php echo $s->total_items; ?></span>
                        <div style="background:#e2e8f0;border-radius:4px;height:4px;margin-top:4px;width:100px;display:inline-block;vertical-align:middle;">
                            <div style="background:<?php echo ($progress_pct >= 100) ? '#22c55e' : '#3b82f6'; ?>;border-radius:4px;height:100%;width:<?php echo $progress_pct; ?>%;"></div>
                        </div>
                    </td>
                    <td><span style="<?php echo $badge_style; ?>padding:3px 10px;border-radius:12px;font-size:0.8rem;font-weight:500;"><?php echo $badge_text; ?></span></td>
                    <td style="white-space:nowrap;">
                        <?php if ($s->status === 'in_progress'): ?>
                        <a href="<?php echo site_url('inventaire/count/' . $s->id); ?>" title="<?php echo $this->lang->line('inventaire_count'); ?>" style="text-decoration:none;margin-right:4px;">
                            <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <a href="#" onclick="if(confirm('<?php echo addslashes($this->lang->line('inventaire_confirm_apply')); ?>')){window.location='<?php echo site_url('inventaire/apply/' . $s->id); ?>';} return false;" title="<?php echo $this->lang->line('inventaire_apply'); ?>" style="text-decoration:none;margin-right:4px;">
                            <svg width="18" height="18" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        </a>
                        <a href="#" onclick="if(confirm('<?php echo addslashes($this->lang->line('inventaire_confirm_cancel')); ?>')){window.location='<?php echo site_url('inventaire/cancel/' . $s->id); ?>';} return false;" title="<?php echo $this->lang->line('inventaire_cancel_session'); ?>" style="text-decoration:none;">
                            <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </a>
                        <?php else: ?>
                        <a href="<?php echo site_url('inventaire/view/' . $s->id); ?>" title="<?php echo $this->lang->line('inventaire_view'); ?>" style="text-decoration:none;">
                            <svg width="18" height="18" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align:center;padding:40px 20px;color:#64748b;">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:12px;opacity:0.5;">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p><?php echo $this->lang->line('inventaire_no_sessions'); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s;
    cursor: pointer;
    border: none;
}
.btn-primary {
    background: var(--primary, #2563eb);
    color: #fff;
}
.btn-primary:hover {
    background: #1d4ed8;
    color: #fff;
}
#sessions_table {
    width: 100%;
    border-collapse: collapse;
}
#sessions_table thead th {
    background: #4386a1cc;
    color: #fff;
    padding: 8px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
}
#sessions_table tbody td {
    padding: 8px 12px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.85rem;
}
#sessions_table tbody tr:hover {
    background: #f1f5f9;
}
</style>

<!-- QR Code Download APK -->
<?php
    $apk_url = 'https://files.catbox.moe/2wf4qm.apk';
    $qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&format=svg&data=' . urlencode($apk_url);
?>
<div style="margin-top:24px;padding:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;text-align:center;">
    <div style="display:inline-flex;align-items:center;gap:16px;flex-wrap:wrap;justify-content:center;">
        <img id="qr_apk" src="<?php echo $qr_api_url; ?>" alt="QR Code APK" width="150" height="150"
             style="border:1px solid #e2e8f0;border-radius:4px;"
             onerror="this.style.display='none';document.getElementById('qr_fallback').style.display='flex';">
        <div id="qr_fallback" style="display:none;width:150px;height:150px;border:1px solid #e2e8f0;border-radius:4px;background:#fff;align-items:center;justify-content:center;flex-direction:column;color:#94a3b8;font-size:0.8rem;">
            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:4px;opacity:0.5;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="3" height="3"/><rect x="18" y="18" width="3" height="3"/></svg>
            QR indisponible
        </div>
        <div style="text-align:left;">
            <div style="font-weight:600;font-size:1rem;color:#1e293b;margin-bottom:4px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                Application Mobile Inventaire
            </div>
            <div style="font-size:0.85rem;color:#64748b;margin-bottom:8px;">Scannez le QR code pour installer l'APK</div>
            <a href="<?php echo $apk_url; ?>" style="display:inline-flex;align-items:center;gap:4px;padding:6px 14px;background:#2563eb;color:#fff;border-radius:6px;text-decoration:none;font-size:0.85rem;font-weight:500;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Télécharger l'APK
            </a>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#sessions_table').on('click', 'tr.session-row-active td:not(:last-child)', function() {
        window.location = $(this).closest('tr').data('href');
    });
});
</script>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>
