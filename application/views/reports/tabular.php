<?php $this->load->view("partial/header"); ?>

<?php
    // get the number format
    $pieces = array();
    $pieces = explode("/", $this->config->item('numberformat'));

    // Focus line setup
    if (isset($_SESSION['line_focus'])) {
        $_SESSION['line'] = $_SESSION['line_focus'];
    }

    $is_inline_inv = (isset($_SESSION['inline_inventory_mode']) && $_SESSION['inline_inventory_mode'] == '1');
    $has_oeil      = (isset($_SESSION['oeil_desactivation']) && $_SESSION['oeil_desactivation'] == '1');
    $has_articles  = (isset($_SESSION['tabular_articles_yes']) && $_SESSION['tabular_articles_yes'] == '1');
?>

<style>
/* ===== Report card ===== */
.rpt-card {
    background: var(--bg-container, #fff);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
    overflow: hidden;
    max-width: 1100px;
    margin: 10px auto 20px;
}

/* Header */
.rpt-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc);
}
.rpt-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.rpt-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary, #2563eb), var(--secondary, #8b5cf6));
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.rpt-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
    line-height: 1.3;
}
.rpt-subtitle {
    font-size: 12px;
    color: var(--text-secondary, #64748b);
    margin-top: 2px;
}
.rpt-header-right {
    display: flex;
    align-items: center;
    gap: 10px;
}
/* Period filter */
.rpt-period-filter {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 6px 4px 12px;
    border-radius: 10px;
    background: rgba(37,99,235,0.06);
    border: 1px solid rgba(37,99,235,0.15);
}
.rpt-period-icon {
    color: var(--primary, #2563eb);
    flex-shrink: 0;
}
.rpt-date-input {
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px;
    padding: 5px 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-primary, #1e293b);
    background: var(--bg-container, #fff);
    outline: none;
    transition: border-color 0.15s;
    width: 130px;
}
.rpt-date-input:focus {
    border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 2px rgba(37,99,235,0.12);
}
.rpt-date-arrow {
    color: var(--text-secondary, #94a3b8);
    font-size: 13px;
    font-weight: 600;
}
.rpt-btn-filter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 7px;
    border: none;
    background: var(--primary, #2563eb);
    color: #fff;
    cursor: pointer;
    transition: all 0.15s;
    flex-shrink: 0;
}
.rpt-btn-filter:hover {
    background: var(--secondary, #8b5cf6);
    transform: scale(1.05);
}
.rpt-btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-container, #fff);
    color: var(--text-secondary, #64748b);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.rpt-btn-action:hover {
    border-color: var(--primary, #2563eb);
    color: var(--primary, #2563eb);
    background: rgba(37,99,235,0.04);
}
.rpt-btn-return {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-container, #fff);
    color: var(--text-secondary, #64748b);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s;
}
.rpt-btn-return:hover {
    border-color: var(--primary, #2563eb);
    color: var(--primary, #2563eb);
    background: rgba(37,99,235,0.04);
}

/* Summary bar */
.rpt-summary-bar {
    display: flex;
    gap: 1px;
    background: var(--border-color, #e2e8f0);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.rpt-summary-chip {
    flex: 1;
    padding: 12px 16px;
    text-align: center;
    background: var(--bg-container, #fff);
}
.rpt-chip-label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary, #94a3b8);
    margin-bottom: 2px;
}
.rpt-chip-value {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
}

/* Toolbar */
.rpt-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-container, #fff);
}
.rpt-row-count {
    font-size: 12px;
    color: var(--text-secondary, #94a3b8);
    font-weight: 500;
}
.rpt-row-count span {
    font-weight: 700;
    color: var(--text-primary, #1e293b);
}
.rpt-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}
.rpt-toggle-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    font-size: 12px;
    color: var(--text-secondary, #64748b);
    user-select: none;
}
.rpt-toggle-switch {
    position: relative;
    display: inline-block;
    width: 34px;
    height: 18px;
    background: #ccc;
    border-radius: 9px;
    transition: background 0.2s;
}
.rpt-toggle-knob {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 14px;
    height: 14px;
    background: #fff;
    border-radius: 50%;
    transition: left 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
.rpt-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 12px;
    background: rgba(37,99,235,0.08);
    color: var(--primary, #2563eb);
    font-size: 11px;
    font-weight: 600;
}

/* Table */
.rpt-table-wrap {
    overflow-x: auto;
}
.rpt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
.rpt-table thead th {
    padding: 10px 12px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary, #64748b);
    background: var(--bg-card, #f8fafc);
    border-bottom: 2px solid var(--border-color, #e2e8f0);
    text-align: left;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 2;
}

/* Sortable columns */
.rpt-sortable {
    cursor: pointer;
    user-select: none;
    transition: color 0.12s;
}
.rpt-sortable:hover {
    color: var(--primary, #2563eb);
}
.rpt-th-inner {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.rpt-sort-icon {
    opacity: 0.3;
    transition: opacity 0.15s;
    flex-shrink: 0;
}
.rpt-sortable:hover .rpt-sort-icon {
    opacity: 0.6;
}
.rpt-sortable .rpt-sort-asc,
.rpt-sortable .rpt-sort-desc {
    opacity: 0.3;
}
.rpt-sortable.rpt-sort-active-asc .rpt-sort-icon,
.rpt-sortable.rpt-sort-active-desc .rpt-sort-icon {
    opacity: 1;
    color: var(--primary, #2563eb);
}
.rpt-sortable.rpt-sort-active-asc .rpt-sort-asc { opacity: 1; }
.rpt-sortable.rpt-sort-active-asc .rpt-sort-desc { opacity: 0.15; }
.rpt-sortable.rpt-sort-active-desc .rpt-sort-desc { opacity: 1; }
.rpt-sortable.rpt-sort-active-desc .rpt-sort-asc { opacity: 0.15; }

.rpt-table tbody td {
    padding: 9px 12px;
    color: var(--text-primary, #1e293b);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    white-space: nowrap;
}
.rpt-table tbody tr {
    transition: background 0.12s;
}
.rpt-table tbody tr:hover {
    background: var(--bg-hover, rgba(37,99,235,0.03));
}
.rpt-td-num {
    text-align: right !important;
    font-variant-numeric: tabular-nums;
    font-weight: 500;
}
.rpt-row-focused {
    background: rgba(37,99,235,0.08) !important;
}
.rpt-td-action {
    padding: 4px 8px !important;
}

/* Eye icon buttons */
.rpt-eye-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    transition: all 0.15s;
}
.rpt-eye-activate {
    color: var(--success, #22c55e);
}
.rpt-eye-activate:hover {
    background: rgba(34,197,94,0.1);
}
.rpt-eye-deactivate {
    color: var(--danger, #ef4444);
}
.rpt-eye-deactivate:hover {
    background: rgba(239,68,68,0.1);
}

/* Links in table */
.rpt-table a {
    color: var(--primary, #2563eb);
    text-decoration: none;
    transition: opacity 0.12s;
}
.rpt-table a:hover {
    opacity: 0.75;
}
.rpt-table a img {
    vertical-align: middle;
    opacity: 0.7;
    transition: opacity 0.12s;
}
.rpt-table a:hover img {
    opacity: 1;
}

/* Dark mode */
[data-theme="dark"] .rpt-card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
[data-theme="dark"] .rpt-period-filter {
    background: rgba(59,130,246,0.1);
    border-color: rgba(59,130,246,0.2);
}
[data-theme="dark"] .rpt-date-input {
    background: var(--bg-card, #334155);
    border-color: var(--border-color, #475569);
    color: var(--text-primary, #f1f5f9);
    color-scheme: dark;
}
[data-theme="dark"] .rpt-badge {
    background: rgba(59,130,246,0.15);
    color: var(--primary, #3b82f6);
}
</style>

<?php if ($is_inline_inv): ?>
<style>
    #sortable_table { table-layout: fixed; }
    #sortable_table th:nth-child(1), #sortable_table td:nth-child(1) { width: 32px; }
    #sortable_table th:nth-child(2), #sortable_table td:nth-child(2) { width: 45px; white-space: nowrap; padding: 4px 2px; }
    #sortable_table th:nth-child(3), #sortable_table td:nth-child(3) { width: 100px; white-space: nowrap; padding: 4px 2px; }
    #sortable_table th:nth-child(4), #sortable_table td:nth-child(4) { width: auto; overflow: hidden; text-overflow: ellipsis; }
    #sortable_table th:nth-child(5), #sortable_table td:nth-child(5) { width: 50px; font-family: 'SF Mono', 'Consolas', monospace; font-size: 13px; text-align: right; white-space: nowrap; }
    #sortable_table th:nth-child(6), #sortable_table td:nth-child(6) { width: 120px; white-space: nowrap; }
    #sortable_table th:nth-child(7), #sortable_table td:nth-child(7) { width: 300px; white-space: nowrap; }
    .inline-real-qty {
        font-family: 'SF Mono', 'Consolas', monospace !important;
        font-size: 13px;
        -moz-appearance: textfield;
    }
    .inline-real-qty::-webkit-inner-spin-button,
    .inline-real-qty::-webkit-outer-spin-button {
        margin-left: 8px;
    }
    .inline-inv-comment { width: 250px !important; }
    #sortable_table th .col-resizer {
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        cursor: col-resize;
        background: transparent;
    }
    #sortable_table th .col-resizer:hover,
    #sortable_table th .col-resizer.active {
        background: var(--accent-blue, #4a90d9);
        opacity: 0.5;
    }
    #sortable_table tr.ir-done { background-color: var(--bg-hover, #f0f0f0) !important; color: var(--text-secondary, #666); }
    #sortable_table.hide-done tr.ir-done { display: none; }
</style>
<?php endif; ?>

<!-- Messages -->
<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<!-- Report card -->
<div class="rpt-card">

    <!-- Header -->
    <div class="rpt-header">
        <div class="rpt-header-left">
            <div class="rpt-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
            </div>
            <div>
                <div class="rpt-title"><?php echo $title; ?></div>
                <div class="rpt-subtitle"><?php
                    if ($has_articles) {
                        echo $subtitle . " ( " . $_SESSION['compteur'] . " " . $this->lang->line('items_items') . ")";
                    } else {
                        echo $subtitle;
                    }
                ?></div>
            </div>
        </div>
        <div class="rpt-header-right">
            <?php if (isset($start_date) && isset($end_date) && !empty($start_date) && !empty($end_date)): ?>
            <div class="rpt-period-filter" id="rpt-period-filter">
                <svg class="rpt-period-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="rpt-date-input" id="rpt-date-start" value="<?php echo $start_date; ?>">
                <span class="rpt-date-arrow">&rarr;</span>
                <input type="date" class="rpt-date-input" id="rpt-date-end" value="<?php echo $end_date; ?>">
                <button type="button" class="rpt-btn-filter" id="rpt-btn-apply" title="Appliquer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($is_inline_inv): ?>
            <button type="button" class="rpt-btn-action" onclick="PrinterIR.print();" title="Imprimer fiche inventaire">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimer
            </button>
            <?php else: ?>
            <button type="button" class="rpt-btn-action" onclick="Printer.print(document.getElementById('sortable_table').innerHTML);" title="Imprimer">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimer
            </button>
            <?php endif; ?>

            <?php if (isset($_SESSION['G']->modules[$_SESSION['module_id']]['show_exit_button']) && $_SESSION['G']->modules[$_SESSION['module_id']]['show_exit_button'] == 1): ?>
            <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="rpt-btn-return">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <?php echo $this->lang->line('common_return'); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary totals -->
    <?php if (!empty($summary_data)): ?>
    <div class="rpt-summary-bar">
        <?php foreach ($summary_data as $name => $value): ?>
        <div class="rpt-summary-chip">
            <div class="rpt-chip-label"><?php echo $this->lang->line('reports_' . $name); ?></div>
            <div class="rpt-chip-value"><?php echo number_format($value, $pieces[0], $pieces[1], $pieces[2]); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="rpt-toolbar">
        <div class="rpt-row-count">
            <span id="rpt-count"><?php echo is_array($data) ? count($data) : 0; ?></span> <?php echo $this->lang->line('reports_results') ?: 'lignes'; ?>
        </div>
        <div class="rpt-toolbar-actions">
            <?php if ($is_inline_inv): ?>
            <label id="toggle-done-label" class="rpt-toggle-label">
                <span id="toggle-done-switch" class="rpt-toggle-switch">
                    <span id="toggle-done-knob" class="rpt-toggle-knob"></span>
                </span>
                Afficher trait&eacute;s
            </label>
            <?php endif; ?>

            <?php if ($has_articles): ?>
            <span class="rpt-badge" id="ir-badge" data-displayed="<?php echo $_SESSION['compteur']; ?>" data-total="<?php echo isset($_SESSION['total_articles_ir']) ? $_SESSION['total_articles_ir'] : ''; ?>" data-treated="<?php echo isset($_SESSION['ir_treated_lines']) ? count($_SESSION['ir_treated_lines']) : 0; ?>">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/></svg>
                <span id="ir-badge-text"><?php
                    if (isset($_SESSION['total_articles_ir'])) {
                        echo $_SESSION['compteur'] . '/' . $_SESSION['total_articles_ir'];
                    } else {
                        echo $_SESSION['compteur'];
                    }
                ?> <?php echo $this->lang->line('items_items'); ?></span>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main table -->
    <div class="rpt-table-wrap">
        <table class="rpt-table" id="sortable_table">
            <thead>
                <tr>
                    <?php if ($has_oeil): ?>
                    <th class="rpt-th-action" style="width:40px;text-align:center;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </th>
                    <?php endif; ?>
                    <?php $col_idx = 0; foreach ($headers as $header): ?>
                    <th class="rpt-sortable" data-col="<?php echo $col_idx; ?>">
                        <div class="rpt-th-inner">
                            <span><?php echo $header; ?></span>
                            <svg class="rpt-sort-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path class="rpt-sort-asc" d="M18 11l-6-6-6 6"/><path class="rpt-sort-desc" d="M6 13l6 6 6-6"/></svg>
                        </div>
                    </th>
                    <?php $col_idx++; endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $line => $row):
                    $is_focused = (isset($_SESSION['line']) && $_SESSION['line'] == $line);
                    $row_class = $is_focused ? 'rpt-row-focused' : '';
                    $is_ir_treated = (isset($_SESSION['ir_treated_lines']) && in_array($line, $_SESSION['ir_treated_lines']));
                    if ($is_ir_treated) { $row_class .= ' ir-done'; }

                    $request_for_deleted = array('deleted' => '0');
                    $request_for_quantity = array('quantity' => 1);
                    $tout = '';
                    if ($has_oeil) {
                        $cur_item_info = new stdClass();
                        $tableau_recuperation_item_id = explode("/", $row[1]);
                        $cur_item_info->item_id = isset($tableau_recuperation_item_id[7]) ? $tableau_recuperation_item_id[7] : 0;
                        $data_item = $this->Item->get_info($cur_item_info->item_id);
                        $request_for_deleted = array('deleted' => $data_item->deleted);
                        $request_for_quantity = array('quantity' => $data_item->quantity);
                        $tout = (string)$cur_item_info->item_id . ':' . (string)$line . ':' . 'reports';
                    }
                ?>
                <tr class="<?php echo $row_class; ?>" <?php if ($is_focused) echo 'id="line_couleur"'; ?>>
                    <?php if ($has_oeil): ?>
                    <td class="rpt-td-action" style="text-align:center;">
                        <?php if ($request_for_quantity['quantity'] <= 0): ?>
                            <?php if ($request_for_deleted['deleted'] == '1'): ?>
                            <a href="<?php echo site_url("receivings/desactive/$tout"); ?>" class="rpt-eye-btn rpt-eye-activate" title="Activer">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <?php else: ?>
                            <a href="<?php echo site_url("receivings/desactive/$tout"); ?>" class="rpt-eye-btn rpt-eye-deactivate" title="D&eacute;sactiver">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>

                    <?php foreach ($row as $cell):
                        if (is_numeric($cell)) {
                            if (is_int($cell)) { $cell = intval($cell); }
                            if (is_float($cell)) { $cell = floatval($cell); }
                            $cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);
                            $align_class = 'rpt-td-num';
                        } else {
                            $align_class = '';
                        }
                    ?>
                    <td class="<?php echo $align_class; ?>">
                        <?php
                        if (isset($_SESSION['autofocus_avec_item_id_tabular']) && $_SESSION['autofocus_avec_item_id_tabular'] == $cell) {
                            echo $cell;
                            echo form_button(array('autofocus' => 'autofocus', 'type' => 'hidden'));
                        } else {
                            echo $cell;
                        }
                        ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div><!-- end .rpt-card -->

<!-- Hidden summary for print -->
<div id="report_summary" style="display:none;">
    <?php foreach ($summary_data as $name => $value): ?>
        <div><?php echo $this->lang->line('reports_' . $name) . ': ' . number_format($value, $pieces[0], $pieces[1], $pieces[2]); ?></div>
    <?php endforeach; ?>
</div>

<script type="text/javascript">
var Printer = {
    print: function(HTML) {
        var win = window.open('', '_blank');
        var magasin = '<?php echo addslashes($title); ?>';
        var subtitleEl = document.querySelector('.rpt-subtitle');
        var subtitle = subtitleEl ? subtitleEl.innerHTML : '';
        var resume = document.getElementById('report_summary') ? document.getElementById('report_summary').innerHTML : '';
        win.document.write(
            '<html><head><title>' + document.title + '</title>' +
            '<style>' +
                'body { font-family: Arial, sans-serif; font-size: 11px; }' +
                'table { width: 100%; border-collapse: collapse; }' +
                'th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }' +
                'th { background: #f5f5f5; font-weight: bold; }' +
                'tr:nth-child(even) { background: #fafafa; }' +
                'h2 { margin-bottom: 10px; }' +
                '@media print { body { -webkit-print-color-adjust: exact; } }' +
            '</style></head><body>' +
            '<h2>' + magasin + '</h2>' +
            '<h3>' + subtitle + '</h3>' +
            '<table>' + HTML + '</table>' +
            '<br/><br/>' + resume +
            '</body></html>'
        );
        win.document.close();
        win.print();
        win.close();
    }
};
</script>

<?php if (!empty($ticket_z)): ?>

<!-- Tax breakdown data for Ticket Z print -->
<div id="tz-tax-data" style="display:none;">
<?php if (!empty($tax_breakdown)): ?>
<?php foreach ($tax_breakdown as $tb): ?>
<div class="tz-tax-row" data-rate="<?php echo $tb['tax_rate']; ?>" data-ht="<?php echo $tb['base_ht']; ?>" data-tax="<?php echo $tb['tax_amount']; ?>"></div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<script type="text/javascript">
// Override printer for Ticket Z - lightweight receipt
Printer.print = function() {
    var win = window.open('', '_blank');
    var now = new Date();
    var printDate = now.toLocaleDateString('fr-FR') + ' \u00e0 ' + now.toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});

    var company = <?php echo json_encode($company_name ?? ''); ?>;
    var address = <?php echo json_encode(str_replace("\n", "\n", $company_address ?? '')); ?>;
    var phone = <?php echo json_encode($company_phone ?? ''); ?>;

    var subtitleEl = document.querySelector('.rpt-subtitle');
    var subtitle = subtitleEl ? subtitleEl.textContent.replace(/^\s+|\s+$/g, '') : '';

    // Number formatter
    function fmt(n) {
        return parseFloat(n).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    function dotline(label, value, bold) {
        var b = bold ? 'font-weight:700;' : '';
        return '<tr style="' + b + '"><td>' + label + '</td><td style="text-align:right;font-variant-numeric:tabular-nums;">' + value + '</td></tr>';
    }

    // Payment rows from table (3 cols: label, TTC, HT)
    var payRows = '';
    var trs = document.querySelectorAll('#sortable_table tbody tr');
    for (var i = 0; i < trs.length; i++) {
        var tds = trs[i].querySelectorAll('td');
        if (tds.length < 2) continue;
        var label = tds[0].textContent.replace(/^\s+|\s+$/g, '');
        var ttc = tds[1] ? tds[1].textContent.replace(/^\s+|\s+$/g, '') : '';
        var ht  = tds[2] ? tds[2].textContent.replace(/^\s+|\s+$/g, '') : '';
        payRows += '<tr><td>' + label + '</td><td class="right">' + ht + '</td><td class="right">' + ttc + '</td></tr>';
    }

    // Summary chips
    var chips = document.querySelectorAll('.rpt-summary-chip');
    var total = '', subtotalHT = '', taxTotal = '', profit = '', invoiceCount = '', avgBasket = '', offered = '';
    for (var c = 0; c < chips.length; c++) {
        var lbl = (chips[c].querySelector('.rpt-chip-label') || {}).textContent || '';
        var val = (chips[c].querySelector('.rpt-chip-value') || {}).textContent || '';
        lbl = lbl.replace(/^\s+|\s+$/g, '').toLowerCase();
        val = val.replace(/^\s+|\s+$/g, '');
        if (lbl.indexOf('total') > -1 && lbl.indexOf('ht') === -1 && lbl.indexOf('tax') === -1 && lbl.indexOf('tva') === -1) total = val;
        else if (lbl.indexOf('subtotal') > -1 || lbl.indexOf('ht') > -1) subtotalHT = val;
        else if (lbl.indexOf('tax') > -1 || lbl.indexOf('tva') > -1) taxTotal = val;
        else if (lbl.indexOf('profit') > -1 || lbl.indexOf('b\u00e9n\u00e9fice') > -1 || lbl.indexOf('benefice') > -1) profit = val;
        else if (lbl.indexOf('invoice') > -1 || lbl.indexOf('facture') > -1 || lbl.indexOf('ticket') > -1) invoiceCount = val;
        else if (lbl.indexOf('basket') > -1 || lbl.indexOf('panier') > -1) avgBasket = val;
        else if (lbl.indexOf('offer') > -1) offered = val;
    }

    // Tax breakdown rows
    var taxRows = '';
    var taxEls = document.querySelectorAll('#tz-tax-data .tz-tax-row');
    for (var t = 0; t < taxEls.length; t++) {
        var rate = parseFloat(taxEls[t].getAttribute('data-rate'));
        var ht   = parseFloat(taxEls[t].getAttribute('data-ht'));
        var tax  = parseFloat(taxEls[t].getAttribute('data-tax'));
        taxRows += '<tr>' +
            '<td style="text-align:center;">' + fmt(rate) + '%</td>' +
            '<td style="text-align:right;">' + fmt(ht) + '</td>' +
            '<td style="text-align:right;">' + fmt(tax) + '</td>' +
            '<td style="text-align:right;">' + fmt(ht + tax) + '</td>' +
        '</tr>';
    }

    var html =
    '<html><head><title>Ticket Z</title>' +
    '<style>' +
        '@page { size: A4 portrait; margin: 20mm 25mm; }' +
        '* { box-sizing: border-box; margin: 0; padding: 0; }' +
        'body { font-family: Arial, sans-serif; color: #222; font-size: 11pt; }' +
        '.tz { max-width: 520px; margin: 0 auto; }' +
        '.tz-head { text-align: center; padding-bottom: 10px; border-bottom: 2px solid #222; margin-bottom: 12px; }' +
        '.tz-company { font-size: 14pt; font-weight: 700; }' +
        '.tz-addr { font-size: 9pt; color: #555; white-space: pre-line; }' +
        '.tz-title { font-size: 16pt; font-weight: 800; letter-spacing: 0.1em; margin: 10px 0 4px; }' +
        '.tz-period { font-size: 9pt; color: #555; }' +
        'h3 { font-size: 9pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #666; margin: 14px 0 6px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }' +
        'table { width: 100%; border-collapse: collapse; }' +
        'td { padding: 2px 0; font-size: 10pt; }' +
        '.sep { border: none; border-top: 1px dashed #aaa; margin: 10px 0; }' +
        '.sep-bold { border: none; border-top: 2px solid #222; margin: 10px 0; }' +
        '.big { font-size: 13pt; font-weight: 700; }' +
        '.right { text-align: right; font-variant-numeric: tabular-nums; }' +
        '.center { text-align: center; }' +
        '.light { color: #777; font-size: 9pt; }' +
        '.tax-tbl th { font-size: 8pt; font-weight: 600; text-transform: uppercase; color: #666; padding: 3px 0; border-bottom: 1px solid #ccc; }' +
        '.tax-tbl td { padding: 2px 0; font-size: 9.5pt; }' +
        '@media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }' +
    '</style></head><body>' +
    '<div class="tz">' +

    // Header
    '<div class="tz-head">' +
        (company ? '<div class="tz-company">' + company + '</div>' : '') +
        (address ? '<div class="tz-addr">' + address + '</div>' : '') +
        (phone ? '<div class="tz-addr">' + phone + '</div>' : '') +
        '<div class="tz-title">TICKET Z</div>' +
        '<div class="tz-period">' + subtitle + '</div>' +
    '</div>' +

    // Payments
    '<h3>R\u00e8glement</h3>' +
    '<table>' +
        '<tr><td class="light"></td><td class="right light" style="font-size:8pt;text-transform:uppercase;">HT</td><td class="right light" style="font-size:8pt;text-transform:uppercase;">TTC</td></tr>' +
        payRows +
    '</table>' +

    // Separator + Totals
    '<hr class="sep-bold"/>' +
    '<table>' +
        '<tr><td>Total HT</td><td class="right">' + subtotalHT + '</td></tr>' +
        '<tr><td>Total TVA</td><td class="right">' + taxTotal + '</td></tr>' +
        '<tr class="big"><td>TOTAL TTC</td><td class="right">' + total + '</td></tr>' +
    '</table>' +

    // Tax breakdown
    (taxRows ?
        '<h3>D\u00e9tail TVA</h3>' +
        '<table class="tax-tbl">' +
            '<tr><th class="center">Taux</th><th class="right">Base HT</th><th class="right">TVA</th><th class="right">TTC</th></tr>' +
            taxRows +
        '</table>'
    : '') +

    '<hr class="sep"/>' +

    // Stats
    '<table>' +
        (invoiceCount ? '<tr><td>Nb tickets</td><td class="right">' + invoiceCount + '</td></tr>' : '') +
        (avgBasket ? '<tr><td>Panier moyen</td><td class="right">' + avgBasket + '</td></tr>' : '') +
        (offered ? '<tr><td>Articles offerts</td><td class="right">' + offered + '</td></tr>' : '') +
    '</table>' +

    (profit ?
        '<hr class="sep"/>' +
        '<table><tr><td>B\u00e9n\u00e9fice</td><td class="right">' + profit + '</td></tr></table>'
    : '') +

    // Footer
    '<hr class="sep-bold"/>' +
    '<div class="center light">Imprim\u00e9 le ' + printDate + '</div>' +

    '</div></body></html>';

    win.document.write(html);
    win.document.close();
    setTimeout(function() { win.print(); }, 200);
};
</script>
<?php endif; ?>

<script type="text/javascript">
$(document).ready(function() {

    // ---- Period filter ----
    $('#rpt-btn-apply').on('click', function() {
        applyDateFilter();
    });
    $('#rpt-date-start, #rpt-date-end').on('keydown', function(e) {
        if (e.keyCode === 13) { e.preventDefault(); applyDateFilter(); }
    });
    function applyDateFilter() {
        var newStart = $('#rpt-date-start').val();
        var newEnd   = $('#rpt-date-end').val();
        if (!newStart || !newEnd) return;
        var base = '<?php echo site_url(); ?>';
        var path = window.location.pathname;
        var basePath = new URL(base).pathname.replace(/\/+$/, '');
        var route = path.replace(basePath, '').replace(/^\/+/, '');
        var segments = route.split('/');
        if (segments.length >= 4) {
            segments[2] = newStart;
            segments[3] = newEnd;
            window.location.href = basePath + '/' + segments.join('/');
        }
    }

    // ---- Column sorting ----
    var currentCol = -1;
    var currentDir = 'none';
    var hasOeil = <?php echo $has_oeil ? 'true' : 'false'; ?>;
    var colOffset = hasOeil ? 1 : 0; // offset for oeil column

    $('.rpt-sortable').on('click', function() {
        var colIdx = parseInt($(this).data('col'));

        if (colIdx !== currentCol) {
            currentDir = 'asc';
        } else if (currentDir === 'asc') {
            currentDir = 'desc';
        } else {
            currentDir = 'none';
        }
        currentCol = colIdx;

        $('.rpt-sortable').removeClass('rpt-sort-active-asc rpt-sort-active-desc');
        if (currentDir !== 'none') {
            $(this).addClass('rpt-sort-active-' + currentDir);
        }

        var $tbody = $('#sortable_table tbody');
        var rows = [];
        $tbody.find('tr').each(function(idx) {
            var $row = $(this);
            var cellText = $.trim($row.find('td').eq(colIdx + colOffset).text());
            rows.push({ $row: $row, val: cellText, origIdx: idx });
        });

        if (currentDir === 'none') {
            rows.sort(function(a, b) { return a.origIdx - b.origIdx; });
        } else {
            rows.sort(function(a, b) {
                var va = a.val, vb = b.val;
                var na = parseFloat(va.replace(/\s/g, '').replace(',', '.'));
                var nb = parseFloat(vb.replace(/\s/g, '').replace(',', '.'));
                if (!isNaN(na) && !isNaN(nb)) {
                    return currentDir === 'asc' ? na - nb : nb - na;
                }
                var da = parseDate(va), db = parseDate(vb);
                if (da && db) {
                    return currentDir === 'asc' ? da - db : db - da;
                }
                var cmp = va.localeCompare(vb, 'fr', { sensitivity: 'base' });
                return currentDir === 'asc' ? cmp : -cmp;
            });
        }

        for (var i = 0; i < rows.length; i++) {
            $tbody.append(rows[i].$row);
        }
    });

    function parseDate(str) {
        var m = str.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?/);
        if (m) return new Date(m[3], m[2]-1, m[1], m[4]||0, m[5]||0);
        m = str.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (m) return new Date(m[1], m[2]-1, m[3]);
        return null;
    }
});
</script>

<?php if ($is_inline_inv): ?>
<script type="text/javascript">
var PrinterIR = {
    print: function() {
        var win = window.open('', '_blank');
        var magasin = '<?php echo addslashes($title); ?>';
        var subtitleEl = document.querySelector('.rpt-subtitle');
        var subtitle = subtitleEl ? subtitleEl.innerHTML : '';
        var today = new Date();
        var dateStr = today.toLocaleDateString('fr-FR');

        var rows = [];
        var trs = document.getElementById('sortable_table').getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            var tds = trs[i].getElementsByTagName('td');
            if (!tds || tds.length < 7) continue;
            var ref = tds[2] ? tds[2].textContent.replace(/^\s+|\s+$/g, '') : '';
            var designation = tds[3] ? tds[3].textContent.replace(/^\s+|\s+$/g, '') : '';
            var stk = tds[4] ? tds[4].textContent.replace(/^\s+|\s+$/g, '') : '';
            rows.push('<tr><td>' + ref + '</td><td>' + designation + '</td><td style="text-align:right;font-family:Consolas,monospace;">' + stk + '</td><td style="width:80px;">&nbsp;</td><td style="width:160px;">&nbsp;</td></tr>');
        }

        win.document.write(
            '<html><head><title>Inventaire Tournant - ' + dateStr + '</title>' +
            '<style>' +
                '@page { size: A4 portrait; margin: 12mm; }' +
                'body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; }' +
                'h2 { font-size: 16px; margin: 0 0 4px 0; }' +
                'h3 { font-size: 12px; margin: 0 0 2px 0; font-weight: normal; color: #555; }' +
                '.date-line { font-size: 11px; margin: 0 0 8px 0; color: #777; }' +
                'table { width: 100%; border-collapse: collapse; }' +
                'th { background: #e9e9e9; font-weight: bold; padding: 4px 6px; border: 1px solid #999; text-align: left; font-size: 10px; }' +
                'td { padding: 3px 6px; border: 1px solid #bbb; font-size: 10px; }' +
                'tr:nth-child(even) { background: #f7f7f7; }' +
                '@media print { body { -webkit-print-color-adjust: exact; } }' +
            '</style></head><body>' +
            '<h2>' + magasin + '</h2>' +
            '<h3>' + subtitle + '</h3>' +
            '<div class="date-line">Imprim\u00e9 le ' + dateStr + '</div>' +
            '<table>' +
            '<thead><tr><th>R\u00e9f.</th><th>D\u00e9signation</th><th style="text-align:right;">Stk</th><th>Stock R\u00e9el</th><th>Commentaire</th></tr></thead>' +
            '<tbody>' + rows.join('') + '</tbody>' +
            '</table>' +
            '<div style="margin-top:12px;font-size:10px;color:#777;">Total articles : ' + rows.length + '</div>' +
            '</body></html>'
        );
        win.document.close();
        win.print();
        win.close();
    }
};
</script>
<script type="text/javascript">
(function() {
    var table = document.getElementById('sortable_table');
    if (!table) return;
    var thead = table.getElementsByTagName('thead')[0];
    if (!thead) return;
    var ths = thead.getElementsByTagName('th');
    var startX, startW, activeTh, activeHandle;

    function doMouseMove(e) {
        if (!activeTh) return;
        var diff = e.clientX - startX;
        var newW = startW + diff;
        if (newW < 20) newW = 20;
        activeTh.style.width = newW + 'px';
        e.preventDefault();
    }

    function doMouseUp() {
        if (activeHandle) activeHandle.className = 'col-resizer';
        activeTh = null;
        activeHandle = null;
        document.onmousemove = null;
        document.onmouseup = null;
    }

    for (var i = 0; i < ths.length; i++) {
        var handle = document.createElement('div');
        handle.className = 'col-resizer';
        ths[i].style.position = 'relative';
        ths[i].appendChild(handle);

        (function(th, h) {
            h.onmousedown = function(e) {
                e = e || window.event;
                activeTh = th;
                activeHandle = h;
                startX = e.clientX;
                startW = th.offsetWidth;
                h.className = 'col-resizer active';
                document.onmousemove = doMouseMove;
                document.onmouseup = doMouseUp;
                if (e.preventDefault) e.preventDefault();
                if (e.stopPropagation) e.stopPropagation();
                return false;
            };
        })(ths[i], handle);
    }
})();
</script>
<script type="text/javascript">
$(document).ready(function() {
    var ajaxUrl = '<?php echo site_url("items/save_inventory_inline"); ?>';

    function submitInlineForm(form) {
        var itemId = form.getAttribute('data-item-id');
        var theoreticalQty = parseFloat(form.getAttribute('data-theoretical-qty'));
        var dluoIndicator = form.getAttribute('data-dluo-indicator');
        var inputField = $("input[name='real_qty']", form);
        var realQty = parseFloat(inputField.val());

        if (isNaN(realQty)) {
            inputField.css('border', '2px solid red');
            inputField.focus();
            return;
        }

        var commentField = $("input.inline-inv-comment[data-item-id='" + itemId + "']");
        var transComment = commentField.length ? commentField.val() : '';

        var adjustment = realQty - theoreticalQty;
        var btn = $(".inline-inv-btn[data-item-id='" + itemId + "']");
        btn.attr('disabled', 'disabled');
        inputField.attr('readonly', 'readonly');
        if (commentField.length) commentField.attr('readonly', 'readonly');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                item_id: itemId,
                newquantity: adjustment,
                real_quantity: realQty,
                dluo_indicator: dluoIndicator,
                trans_comment: transComment
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    var td = form.parentNode;
                    var tr = td.parentNode;
                    tr.className = (tr.className ? tr.className + ' ' : '') + 'ir-done';
                    $(tr).css('background-color', '').css('color', '');
                    var sign = resp.adjustment >= 0 ? '+' : '';
                    $(td).html(realQty + ' &#10003; (' + sign + resp.adjustment + ')');
                    if (resp.dluo_redirect) {
                        var link = document.createElement('a');
                        link.href = resp.dluo_redirect;
                        link.target = '_blank';
                        link.style.cssText = 'color:#dc3545;margin-left:6px;font-weight:bold;';
                        link.innerHTML = '&#9888; DLUO';
                        td.appendChild(link);
                    }
                    var badge = document.getElementById('ir-badge');
                    if (badge) {
                        var displayed = parseInt(badge.getAttribute('data-displayed'), 10) - 1;
                        var treated = parseInt(badge.getAttribute('data-treated') || '0', 10) + 1;
                        badge.setAttribute('data-displayed', displayed);
                        badge.setAttribute('data-treated', treated);
                        updateBadgeText();
                    }
                    var allInputs = $("input.inline-real-qty");
                    var found = false;
                    allInputs.each(function(i) {
                        if (found) return false;
                        if (!this.readOnly) {
                            this.focus();
                            found = true;
                        }
                    });
                } else {
                    inputField.removeAttr('readonly');
                    btn.removeAttr('disabled');
                    if (commentField.length) commentField.removeAttr('readonly');
                    inputField.css('border', '2px solid red');
                    alert(resp.message || 'Erreur');
                }
            },
            error: function() {
                inputField.removeAttr('readonly');
                btn.removeAttr('disabled');
                if (commentField.length) commentField.removeAttr('readonly');
                inputField.css('border', '2px solid red');
                alert('Erreur de communication avec le serveur');
            }
        });
    }

    $(".inline-inv-btn").click(function() {
        var btnItemId = this.getAttribute('data-item-id');
        var form = $("form.inline-inv-form[data-item-id='" + btnItemId + "']")[0];
        if (form) submitInlineForm(form);
    });

    $("input.inline-real-qty").each(function() {
        var input = this;
        input.onkeydown = function(e) {
            e = e || window.event;
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                if (e.preventDefault) e.preventDefault();
                var form = input.parentNode;
                var itemId = form.getAttribute('data-item-id');
                var allComments = document.getElementsByClassName('inline-inv-comment');
                for (var i = 0; i < allComments.length; i++) {
                    if (allComments[i].getAttribute('data-item-id') === itemId) {
                        allComments[i].focus();
                        break;
                    }
                }
                return false;
            }
        };
    });

    $("input.inline-inv-comment").keydown(function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            var itemId = this.getAttribute('data-item-id');
            var form = $("form.inline-inv-form[data-item-id='" + itemId + "']")[0];
            if (!form) return;
            var realInput = $("input[name='real_qty']", form);
            var val = realInput.val();
            if (val !== '' && !isNaN(parseFloat(val))) {
                submitInlineForm(form);
            }
        }
    });

    var showDone = false;
    function updateBadgeText() {
        var badge = document.getElementById('ir-badge');
        var badgeText = document.getElementById('ir-badge-text');
        if (!badge || !badgeText) return;
        var displayed = badge.getAttribute('data-displayed');
        var total = badge.getAttribute('data-total');
        var treated = badge.getAttribute('data-treated') || '0';
        if (showDone) {
            badgeText.innerHTML = treated + '/' + total + ' trait\u00e9s';
        } else {
            badgeText.innerHTML = displayed + '/' + total + ' <?php echo $this->lang->line('items_items'); ?>';
        }
    }

    var tableEl = document.getElementById('sortable_table');
    var toggleLabel = document.getElementById('toggle-done-label');
    var toggleSwitch = document.getElementById('toggle-done-switch');
    var toggleKnob = document.getElementById('toggle-done-knob');
    if (tableEl) {
        tableEl.className = tableEl.className + ' hide-done';
    }
    if (toggleLabel) {
        toggleLabel.onclick = function() {
            showDone = !showDone;
            if (showDone) {
                tableEl.className = tableEl.className.replace(/\s*hide-done/g, '');
                toggleSwitch.style.background = '#28a745';
                toggleKnob.style.left = '18px';
            } else {
                tableEl.className = tableEl.className + ' hide-done';
                toggleSwitch.style.background = '#ccc';
                toggleKnob.style.left = '2px';
            }
            updateBadgeText();
        };
    }

    var firstInput = $("input.inline-real-qty:first");
    if (firstInput.length) {
        firstInput.focus();
    }
});
</script>
<?php endif; ?>

<?php
unset($_SESSION['oeil_desactivation']);
unset($_SESSION['reactivation']);
unset($_SESSION['tabular_articles_yes']);
unset($_SESSION['inline_inventory_mode']);
unset($_SESSION['total_articles_ir']);
unset($_SESSION['ir_treated_lines']);
?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>
