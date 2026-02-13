
<?php
	$this->load->view("partial/header");
?>

<style>
/* ===== Reports listing (rpl-) ===== */
.rpl-page { max-width: 1300px; margin: 0 auto; padding: 0 1em; }

.rpl-header {
    display: flex; align-items: center; gap: 14px;
    padding: 18px 0 14px; margin-bottom: 10px;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
}
.rpl-header-icon {
    width: 44px; height: 44px; border-radius: 10px;
    background: linear-gradient(135deg, var(--primary, #2563eb), var(--secondary, #8b5cf6));
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.rpl-header-title { font-size: 1.25em; font-weight: 700; color: var(--text-primary, #1e293b); }
.rpl-header-sub { font-size: 0.8em; color: var(--text-secondary, #64748b); }

/* Messages */
.rpl-messages { margin-bottom: 10px; }

/* Grid */
.rpl-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    align-items: start;
}
@media (max-width: 1000px) { .rpl-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 650px)  { .rpl-grid { grid-template-columns: 1fr; } }

/* Section card */
.rpl-card {
    background: var(--bg-container, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 10px;
    overflow: hidden;
}
.rpl-card-header {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 14px;
    background: var(--bg-card, #f8fafc);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.rpl-card-icon {
    width: 28px; height: 28px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.rpl-card-title {
    font-size: 0.78em; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
    color: var(--text-primary, #1e293b);
}

/* Links list */
.rpl-links { list-style: none; margin: 0; padding: 4px 0; }
.rpl-links li { border-bottom: 1px solid color-mix(in srgb, var(--border-color, #e2e8f0) 50%, transparent); }
.rpl-links li:last-child { border-bottom: none; }
.rpl-links a {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px;
    font-size: 0.85em; font-weight: 500;
    color: var(--text-primary, #1e293b);
    text-decoration: none;
    transition: background 0.12s, color 0.12s;
}
.rpl-links a:hover {
    background: color-mix(in srgb, var(--primary, #2563eb) 5%, transparent);
    color: var(--primary, #2563eb);
}
.rpl-links a svg { color: var(--text-secondary, #94a3b8); flex-shrink: 0; transition: color 0.12s; }
.rpl-links a:hover svg { color: var(--primary, #2563eb); }
.rpl-link-slow { color: var(--warning, #d97706) !important; }
.rpl-link-slow:hover { color: var(--warning, #b45309) !important; }

/* Spinner overlay */
.rpl-spinner-overlay {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: color-mix(in srgb, var(--bg-container, #fff) 70%, transparent);
    align-items: center; justify-content: center;
}
.rpl-spinner-overlay.active { display: flex; }
.rpl-spinner {
    width: 36px; height: 36px; border: 3px solid var(--border-color, #e2e8f0);
    border-top-color: var(--primary, #2563eb); border-radius: 50%;
    animation: rpl-spin 0.7s linear infinite;
}
@keyframes rpl-spin { to { transform: rotate(360deg); } }

/* Dark mode */
[data-theme="dark"] .rpl-card { box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
</style>

<!-- Messages -->
<div class="rpl-messages">
    <?php if (!empty($error)) { ?>
        <div class="error_message"><?php echo $error; ?></div>
    <?php } ?>
    <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>
</div>

<div class="rpl-page">

<!-- Header -->
<div class="rpl-header">
    <div class="rpl-header-icon">
        <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
    </div>
    <div>
        <div class="rpl-header-title"><?php echo $this->lang->line('reports_reports'); ?></div>
        <div class="rpl-header-sub"><?php echo $this->config->item('company'); ?></div>
    </div>
</div>

<!-- Grid -->
<div class="rpl-grid">

<!-- ==================== COLUMN 1 ==================== -->
<div style="display:flex;flex-direction:column;gap:14px;">

    <!-- Rapports Synthétiques -->
    <div class="rpl-card">
        <div class="rpl-card-header">
            <div class="rpl-card-icon" style="background:rgba(34,197,94,0.1);color:#22c55e;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
            </div>
            <div class="rpl-card-title"><?php echo $this->lang->line('reports_summary_reports'); ?></div>
        </div>
        <ul class="rpl-links">
            <li><a href="<?php echo site_url('reports/summary_transations_ticket_z'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="18" rx="2"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="16" y2="11"/><line x1="8" y1="15" x2="12" y2="15"/></svg>
                <?php echo $this->lang->line('reports_summary_ticket_z_report'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/summary_transactions'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <?php echo $this->lang->line('reports_summary_transactions_report'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/summary_transactions_graphical'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <?php echo $this->lang->line('reports_graphical_reports'); ?>
            </a></li>
        </ul>
    </div>

    <!-- Rapports Détaillés -->
    <div class="rpl-card">
        <div class="rpl-card-header">
            <div class="rpl-card-icon" style="background:rgba(37,99,235,0.1);color:#2563eb;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <div class="rpl-card-title"><?php echo $this->lang->line('reports_detailed_reports'); ?></div>
        </div>
        <ul class="rpl-links">
            <li><a href="<?php echo site_url('reports/specific_customer_input'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?php echo $this->lang->line('reports_customer'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/specific_employee_input'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>
                <?php echo $this->lang->line('reports_employee'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/specific_category_input'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                <?php echo $this->lang->line('reports_category'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/detailed_transactions'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <?php echo $this->lang->line('reports_detailed_reports'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/transition'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Rapport d&eacute;taill&eacute; par Fournisseur
            </a></li>
            <li><a href="<?php echo site_url('reports/transition_category'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Rapport d&eacute;taill&eacute; par Famille
            </a></li>
        </ul>
    </div>

    <!-- Data Mining -->
    <div class="rpl-card">
        <div class="rpl-card-header">
            <div class="rpl-card-icon" style="background:rgba(139,92,246,0.1);color:#8b5cf6;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <div class="rpl-card-title"><?php echo $this->lang->line('reports_data_mining'); ?></div>
        </div>
        <ul class="rpl-links">
            <li><a href="<?php echo site_url('reports/inventory_nosale'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                <?php echo $this->lang->line('reports_inventory_nosale'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/inventory_slowmoving'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo $this->lang->line('reports_inventory_slowmoving'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/inventory_invalid_item_number'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <?php echo $this->lang->line('reports_invalid_item_number'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/top_items_by_value'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <?php echo $this->lang->line('reports_top_items_by_value'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/top_items_by_quantity'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                <?php echo $this->lang->line('reports_top_items_by_quantity'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/customer_sales_profile'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <?php echo $this->lang->line('reports_client_sales_profile'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/top_clients'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <?php echo $this->lang->line('reports_top_clients'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/top_employees'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15l-2 5l9-11h-5l2-5L7 15z"/></svg>
                <?php echo $this->lang->line('reports_top_employees'); ?>
            </a></li>
        </ul>
    </div>

</div><!-- end column 1 -->

<!-- ==================== COLUMN 2 ==================== -->
<div style="display:flex;flex-direction:column;gap:14px;">

    <!-- Fin de mois -->
    <div class="rpl-card">
        <div class="rpl-card-header">
            <div class="rpl-card-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="rpl-card-title">Fin de mois</div>
        </div>
        <ul class="rpl-links">
            <li><a href="<?php echo site_url('reports/month_end_routines'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Cl&ocirc;ture fin de mois
            </a></li>
            <li><a href="<?php echo site_url('reports/cogs'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Ex&eacute;cution du rapport d&eacute;taill&eacute; COGS
            </a></li>
            <li><a href="<?php echo site_url('reports/rapport_configurations_produits'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="18" rx="2"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="16" y2="11"/><line x1="8" y1="15" x2="12" y2="15"/></svg>
                Rapport configurations produits
            </a></li>
            <li><a href="<?php echo site_url('reports/inventory_change_tracking_report'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Suivi des modifications de stock
            </a></li>
        </ul>
    </div>

    <!-- Quotidien -->
    <div class="rpl-card">
        <div class="rpl-card-header">
            <div class="rpl-card-icon" style="background:rgba(239,68,68,0.1);color:#ef4444;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="rpl-card-title"><?php echo $this->lang->line('reports_daily'); ?></div>
        </div>
        <ul class="rpl-links">
            <li><a href="<?php echo site_url('reports/inventory_rolling'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                <?php echo $this->lang->line('reports_rolling'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/dluo_qty_error'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <?php echo $this->lang->line('reports_dluo_qty_error'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/inventory_negative_stock'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <?php echo $this->lang->line('reports_negative_stock'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/inventory_value_record_integrity'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                V&eacute;rifier et corriger les quantit&eacute;s en stock
            </a></li>
        </ul>
    </div>

    <!-- Inventaire -->
    <div class="rpl-card">
        <div class="rpl-card-header">
            <div class="rpl-card-icon" style="background:rgba(6,182,212,0.1);color:#06b6d4;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            </div>
            <div class="rpl-card-title"><?php echo $this->lang->line('reports_inventory_reports'); ?></div>
        </div>
        <ul class="rpl-links">
            <li><a href="<?php echo site_url('reports/inventory_low_get_data'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <?php echo $this->lang->line('reports_low_inventory'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/inventory_summary'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                <?php echo $this->lang->line('reports_inventory_summary'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/specific_item_input'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <?php echo $this->lang->line('reports_item'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/dluo_past_date'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo $this->lang->line('reports_dluo_date_past'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/choix_du_nombre_de_mois'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo $this->lang->line('reports_dluo_date_future_choix_mois'); ?>
            </a></li>
            <li><a href="<?php echo site_url('receivings/reprint'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <?php echo $this->lang->line('recvs_reprint'); ?>
            </a></li>
            <li><a href="<?php echo site_url('reports/date_last_inventory'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo $this->lang->line('reports_date_last_inventory'); ?>
            </a></li>
        </ul>
    </div>

</div><!-- end column 2 -->

<!-- ==================== COLUMN 3 (Admin) ==================== -->
<div style="display:flex;flex-direction:column;gap:14px;">

<?php if ($_SESSION['G']->login_employee_info->admin == 1) { ?>
    <div class="rpl-card">
        <div class="rpl-card-header">
            <div class="rpl-card-icon" style="background:rgba(239,68,68,0.1);color:#ef4444;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div class="rpl-card-title">Administration DB</div>
        </div>
        <ul class="rpl-links">
            <li><a href="<?php echo site_url('updates/index'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Charger donn&eacute;es feuille de calcul
            </a></li>
            <li><a href="<?php echo site_url('reports/updatedb_sales_items'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update sales items
            </a></li>
            <li><a href="<?php echo site_url('reports/updatedb_sales_headers'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update sales headers
            </a></li>
            <li><a href="<?php echo site_url('reports/updatedb_customers_sales_total'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update customer sales totals
            </a></li>
            <li><a href="<?php echo site_url('reports/updatedb_employees_sales_total'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update employee sales totals
            </a></li>
            <li><a href="<?php echo site_url('reports/updatedb_items_sales_total'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update item sales totals
            </a></li>
            <li><a href="<?php echo site_url('reports/updatedb_branch_code'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                Update branch code
            </a></li>
            <li><a href="<?php echo site_url('reports/update_sales_items_CV'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update Sales Items CV
            </a></li>
            <li><a href="<?php echo site_url('reports/update_stock_valuation_records'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                Create stock valuation records
            </a></li>
            <li><a href="<?php echo site_url('reports/update_category_records'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                Create category records
            </a></li>
            <li><a href="<?php echo site_url('reports/update_items_category_id'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update category_id in ITEMS
            </a></li>
            <li><a href="<?php echo site_url('reports/update_sales_items_category_id'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update category_id in SALES_ITEMS
            </a></li>
            <li><a href="<?php echo site_url('defects/create_defects'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Create DEFECT item codes
            </a></li>
            <li><a href="<?php echo site_url('reports/updatedb_volume'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update VOLUME field
            </a></li>
            <li><a href="<?php echo site_url('reports/updatedb_nicotine'); ?>" class="rpl-sablier">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Update NICOTINE field
            </a></li>
        </ul>
    </div>
<?php } ?>

</div><!-- end column 3 -->

</div><!-- end rpl-grid -->
</div><!-- end rpl-page -->

<?php
// Show the dialogs
switch ($_SESSION['show_dialog']) {
    case 1: include('../wrightetmathon/application/views/reports/specific_input.php'); break;
    case 2: include('../wrightetmathon/application/views/items/clone_form.php'); break;
    case 3: include('../wrightetmathon/application/views/items/inventory.php'); break;
    case 4: include('../wrightetmathon/application/views/items/count_details.php'); break;
    case 5: include('../wrightetmathon/application/views/items/merge_form.php'); break;
    case 6: include('../wrightetmathon/application/views/items/dluo_form.php'); break;
    case 7: include('../wrightetmathon/application/views/items/label_form.php'); break;
    case 8: include('../wrightetmathon/application/views/items/shutdown_warning.php'); break;
    default: break;
}
?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<!-- Spinner -->
<div class="rpl-spinner-overlay" id="rpl_spinner">
    <div class="rpl-spinner"></div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('.rpl-sablier').click(function() {
        $('#rpl_spinner').addClass('active');
    });
});
</script>
