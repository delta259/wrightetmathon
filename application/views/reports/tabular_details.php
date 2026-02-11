<?php
//OJB: Check if for excel export process
if($export_excel == 1){
	ob_start();
	$this->load->view("partial/header_excel");
}else{
    // output the header
    $this->load->view("partial/head");
    $this->load->view("partial/header_banner");
}

// Number format
$pieces = explode("/", $this->config->item('numberformat'));
$nf_dec = $pieces[0];
$nf_pt  = $pieces[1];
$nf_sep = $pieces[2];

// Helper: detect numeric or currency-formatted values (e.g. "1 234,56" or "-12.50")
if (!function_exists('rpt_is_amount')) {
    function rpt_is_amount($val) {
        if (is_numeric($val)) return true;
        $clean = trim(strip_tags($val));
        // Match currency patterns: "1 234,56", "-12,50", "0,00", "1.234,56", "1,234.56"
        return (bool) preg_match('/^-?\d[\d\s.,]*\d$|^-?\d([.,]\d+)?$/', $clean);
    }
}
?>

<div id="wrapper" class="wlp-bighorn-book">
    <?php $this->load->view("partial/header_menu"); ?>
    <div class="wlp-bighorn-book">
        <div class="wlp-bighorn-book-content">
            <main id="login_page" class="wlp-bighorn-page-unconnect" role="main">
                <div class="body_page">
                    <div class="body_colonne">
                        <div class="body_cadre_gris rpt-host">

<!-- Report card -->
<div class="rpt-card">

    <!-- Header -->
    <div class="rpt-header">
        <div class="rpt-header-left">
            <div class="rpt-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div>
                <div class="rpt-title"><?php echo $title; ?></div>
                <div class="rpt-subtitle"><?php echo $subtitle; ?></div>
            </div>
        </div>
        <div class="rpt-header-right">
            <?php if (!empty($start_date) && !empty($end_date)): ?>
            <div class="rpt-period-filter" id="rpt-period-filter">
                <svg class="rpt-period-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="rpt-date-input" id="rpt-date-start" value="<?php echo $start_date; ?>">
                <span class="rpt-date-sep">&rarr;</span>
                <input type="date" class="rpt-date-input" id="rpt-date-end" value="<?php echo $end_date; ?>">
                <button type="button" class="rpt-btn-filter" id="rpt-btn-apply" title="Appliquer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </div>
            <?php endif; ?>
            <div class="rpt-amount-filter">
                <svg class="rpt-amount-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="rpt-filter-amount" class="rpt-amount-input" placeholder="Montant..." title="Rechercher par montant (ex: 25, >100, <50, 10-30)">
                <button type="button" class="rpt-amount-clear" id="rpt-clear-filters" title="Effacer" style="display:none;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <?php if (isset($_SESSION['G']->modules[$_SESSION['module_id']]['show_exit_button']) && $_SESSION['G']->modules[$_SESSION['module_id']]['show_exit_button'] == 1): ?>
            <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="rpt-btn-return">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <?php echo $this->lang->line('common_return'); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary totals -->
    <?php if (!empty($overall_summary_data)): ?>
    <div class="rpt-summary-bar">
        <?php foreach ($overall_summary_data as $name => $value): ?>
        <div class="rpt-summary-chip">
            <div class="rpt-chip-label"><?php echo $this->lang->line('reports_'.$name); ?></div>
            <div class="rpt-chip-value"><?php echo $value; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="rpt-toolbar">
        <div class="rpt-row-count">
            <span id="rpt-count"><?php echo count($summary_data); ?></span> <?php echo $this->lang->line('reports_results') ?: 'lignes'; ?>
        </div>
        <button type="button" class="rpt-btn-expand-all" id="rpt-expand-all" title="Tout d&eacute;plier / replier">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 8l5 5 5-5"/><path d="M7 14l5 5 5-5"/></svg>
            <span>Tout d&eacute;plier</span>
        </button>
    </div>

    <!-- Main table -->
    <div class="rpt-table-wrap">
        <table class="rpt-table" id="sortable_table">
            <thead>
                <tr>
                    <th class="rpt-th-expand"></th>
                    <?php $col_idx = 0; foreach ($headers['summary'] as $header): ?>
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
                <?php $row_idx = 0; foreach ($summary_data as $key => $row): ?>
                <tr class="rpt-summary-row <?php echo ($row_idx % 2 === 0) ? 'rpt-row-even' : 'rpt-row-odd'; ?>" data-key="<?php echo $key; ?>">
                    <td class="rpt-td-expand">
                        <button type="button" class="rpt-expand-btn" data-key="<?php echo $key; ?>">
                            <svg class="rpt-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </td>
                    <?php $ci = 0; foreach ($row as $cell): ?>
                    <?php if (rpt_is_amount($cell)): ?>
                    <td class="rpt-td-num"><?php echo is_numeric($cell) ? number_format($cell, $nf_dec, $nf_pt, $nf_sep) : $cell; ?></td>
                    <?php else: ?>
                    <td class="<?php echo ($ci >= 3) ? 'rpt-td-text' : ''; ?>"><?php echo $cell; ?></td>
                    <?php endif; ?>
                    <?php $ci++; endforeach; ?>
                </tr>
                <!-- Detail sub-table -->
                <tr class="rpt-detail-row" id="rpt-detail-<?php echo $key; ?>">
                    <td colspan="<?php echo count($headers['summary']) + 1; ?>">
                        <div class="rpt-detail-wrap">
                            <table class="rpt-detail-table">
                                <thead>
                                    <tr>
                                        <?php foreach ($headers['details'] as $header): ?>
                                        <th><?php echo $header; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $di = 0; foreach ($details_data[$key] as $row2): ?>
                                    <tr class="<?php echo ($di % 2 === 0) ? 'rpt-drow-even' : 'rpt-drow-odd'; ?>">
                                        <?php foreach ($row2 as $cell): ?>
                                        <?php if (rpt_is_amount($cell)): ?>
                                        <td class="rpt-td-num"><?php echo is_numeric($cell) ? number_format($cell, $nf_dec, $nf_pt, $nf_sep) : $cell; ?></td>
                                        <?php else: ?>
                                        <td><?php echo $cell; ?></td>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php $di++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <?php $row_idx++; endforeach; ?>
            </tbody>
        </table>
    </div>

</div><!-- end .rpt-card -->

<?php
if($export_excel == 1){
	$this->load->view("partial/footer_excel");
	$content = ob_end_flush();
	$filename = trim($filename);
	$filename = str_replace(array(' ', '/', '\\'), '', $title);
	$filename .= "_Export.xls";
	header('Content-type: application/ms-excel');
	header('Content-Disposition: attachment; filename='.$filename);
	echo $content;
	die();
}else{
?>

<style>
/* ===== Override old wrappers ===== */
.rpt-host {
    background: transparent !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
}
.rpt-host .rpt-card {
    margin: 0;
}

/* ===== Report card ===== */
.rpt-card {
    background: var(--bg-container, #fff);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
    overflow: hidden;
    margin: 10px 0 20px;
}

/* Header */
.rpt-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc);
    flex-wrap: wrap;
    gap: 12px;
}
.rpt-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
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
    flex-wrap: wrap;
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
.rpt-date-sep {
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
    flex-wrap: wrap;
}
.rpt-summary-chip {
    flex: 1;
    min-width: 100px;
    padding: 14px 16px;
    text-align: center;
    background: var(--bg-container, #fff);
}
.rpt-chip-label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary, #94a3b8);
    margin-bottom: 4px;
}
.rpt-chip-value {
    font-size: 16px;
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
.rpt-btn-expand-all {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 6px;
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-container, #fff);
    color: var(--text-secondary, #64748b);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    flex-shrink: 0;
}
.rpt-btn-expand-all:hover {
    border-color: var(--primary, #2563eb);
    color: var(--primary, #2563eb);
}

/* Amount filter (header) */
.rpt-amount-filter {
    display: inline-flex;
    align-items: center;
    gap: 0;
    position: relative;
    padding: 4px 6px 4px 12px;
    border-radius: 10px;
    background: rgba(37,99,235,0.06);
    border: 1px solid rgba(37,99,235,0.15);
}
.rpt-amount-icon {
    position: absolute;
    left: 10px;
    color: var(--primary, #2563eb);
    pointer-events: none;
    flex-shrink: 0;
}
.rpt-amount-input {
    padding: 5px 8px 5px 24px;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-primary, #1e293b);
    background: var(--bg-container, #fff);
    outline: none;
    width: 140px;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.rpt-amount-input:focus {
    border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 2px rgba(37,99,235,0.12);
}
.rpt-amount-input::placeholder {
    color: var(--text-secondary, #94a3b8);
    font-weight: 400;
}
.rpt-amount-input.rpt-filter-active {
    border-color: var(--primary, #2563eb);
    background: rgba(37,99,235,0.04);
}
.rpt-amount-clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 5px;
    border: none;
    background: transparent;
    color: var(--text-secondary, #94a3b8);
    cursor: pointer;
    transition: all 0.15s;
    padding: 0;
    margin-left: 2px;
}
.rpt-amount-clear:hover {
    color: #ef4444;
    background: rgba(239,68,68,0.08);
}

/* ===== Table ===== */
.rpt-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.rpt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
.rpt-table thead th {
    padding: 10px 10px;
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
.rpt-th-expand {
    width: 36px;
    min-width: 36px;
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

/* Body cells */
.rpt-table tbody td {
    padding: 8px 10px;
    color: var(--text-primary, #1e293b);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    white-space: nowrap;
}
.rpt-td-num {
    text-align: right !important;
    font-variant-numeric: tabular-nums;
    font-weight: 600;
    font-family: 'SF Mono', 'Consolas', 'Monaco', monospace;
    letter-spacing: -0.02em;
}
.rpt-td-text {
    white-space: normal;
    max-width: 200px;
    word-break: break-word;
}

/* Zebra striping */
.rpt-row-even {
    background: var(--bg-container, #fff);
}
.rpt-row-odd {
    background: var(--bg-zebra, #f8fafd);
}

/* Summary row */
.rpt-summary-row {
    cursor: pointer;
    transition: background 0.12s;
}
.rpt-summary-row:hover {
    background: var(--bg-hover, rgba(37,99,235,0.05)) !important;
}
.rpt-summary-row.rpt-open {
    background: rgba(37,99,235,0.07) !important;
    font-weight: 500;
}

/* Expand button */
.rpt-td-expand {
    text-align: center;
    padding: 6px 4px !important;
    width: 36px;
}
.rpt-expand-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-container, #fff);
    color: var(--text-secondary, #94a3b8);
    cursor: pointer;
    transition: all 0.15s;
    padding: 0;
}
.rpt-expand-btn:hover {
    border-color: var(--primary, #2563eb);
    color: var(--primary, #2563eb);
    background: rgba(37,99,235,0.04);
}
.rpt-expand-btn .rpt-chevron {
    transition: transform 0.25s ease;
}
.rpt-expand-btn.rpt-expanded .rpt-chevron {
    transform: rotate(90deg);
}
.rpt-expand-btn.rpt-expanded {
    background: var(--primary, #2563eb);
    border-color: var(--primary, #2563eb);
    color: #fff;
}

/* ===== Detail sub-table ===== */
.rpt-detail-row {
    display: none;
}
.rpt-detail-row td {
    padding: 0 !important;
    border-bottom: none !important;
    background: transparent;
}
.rpt-detail-wrap {
    padding: 6px 12px 14px 36px;
    background: linear-gradient(135deg, rgba(37,99,235,0.02), rgba(139,92,246,0.02));
    border-bottom: 2px solid var(--primary, #2563eb);
    animation: rpt-slide-in 0.2s ease-out;
}
@keyframes rpt-slide-in {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}
.rpt-detail-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-container, #fff);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.rpt-detail-table thead th {
    padding: 7px 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary, #64748b);
    background: var(--bg-card, #f1f5f9);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    text-align: center;
    white-space: nowrap;
}
.rpt-detail-table tbody td {
    padding: 7px 10px;
    color: var(--text-primary, #1e293b);
    border-bottom: 1px solid var(--border-color, #f1f5f9);
    white-space: nowrap;
    font-size: 12px;
}
.rpt-detail-table tbody tr:last-child td {
    border-bottom: none;
}
.rpt-detail-table .rpt-td-num {
    text-align: right !important;
    font-weight: 600;
    font-family: 'SF Mono', 'Consolas', 'Monaco', monospace;
}

/* Detail zebra */
.rpt-drow-even {
    background: var(--bg-container, #fff);
}
.rpt-drow-odd {
    background: var(--bg-zebra-detail, #f8fafc);
}
.rpt-detail-table tbody tr:hover {
    background: rgba(37,99,235,0.04);
}

/* ===== Action icons (print, copy) ===== */
.rpt-table a img {
    width: 18px;
    height: 18px;
    vertical-align: middle;
    opacity: 0.5;
    transition: all 0.15s;
    filter: grayscale(40%);
    border-radius: 3px;
}
.rpt-table a:hover img {
    opacity: 1;
    filter: none;
    transform: scale(1.15);
}
.rpt-table a {
    color: var(--primary, #2563eb);
    text-decoration: none;
    transition: color 0.12s;
}
.rpt-table a:hover {
    color: var(--secondary, #8b5cf6);
}

/* ===== Dark mode ===== */
[data-theme="dark"] .rpt-card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
[data-theme="dark"] .rpt-row-odd {
    background: rgba(255,255,255,0.02);
}
[data-theme="dark"] .rpt-detail-wrap {
    background: linear-gradient(135deg, rgba(37,99,235,0.06), rgba(139,92,246,0.06));
}
[data-theme="dark"] .rpt-detail-table {
    border-color: var(--border-color, #475569);
    background: var(--bg-card, #334155);
}
[data-theme="dark"] .rpt-drow-odd {
    background: rgba(255,255,255,0.03);
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
[data-theme="dark"] .rpt-table a img {
    filter: brightness(1.3) grayscale(30%);
}
[data-theme="dark"] .rpt-btn-expand-all {
    background: var(--bg-card, #334155);
    border-color: var(--border-color, #475569);
    color: var(--text-secondary, #94a3b8);
}
[data-theme="dark"] .rpt-amount-filter {
    background: rgba(59,130,246,0.1);
    border-color: rgba(59,130,246,0.2);
}
[data-theme="dark"] .rpt-amount-input {
    background: var(--bg-card, #334155);
    border-color: var(--border-color, #475569);
    color: var(--text-primary, #f1f5f9);
}
[data-theme="dark"] .rpt-amount-input.rpt-filter-active {
    background: rgba(59,130,246,0.1);
}
</style>

<script type="text/javascript">
$(document).ready(function() {

    // ---- Expand / Collapse ----
    $('.rpt-expand-btn').on('click', function(e) {
        e.stopPropagation();
        var key = $(this).data('key');
        var $detail = $('#rpt-detail-' + key);
        var $btn = $(this);
        var $row = $btn.closest('tr');
        if ($detail.is(':visible')) {
            $detail.slideUp(200, function() { $detail.hide(); });
            $btn.removeClass('rpt-expanded');
            $row.removeClass('rpt-open');
        } else {
            $detail.stop(true).css('display', 'table-row').hide().slideDown(200);
            $btn.addClass('rpt-expanded');
            $row.addClass('rpt-open');
        }
    });

    $('.rpt-summary-row').on('click', function(e) {
        if ($(e.target).closest('a').length) return;
        if ($(e.target).closest('.rpt-expand-btn').length) return;
        $(this).find('.rpt-expand-btn').trigger('click');
    });

    // ---- Expand / Collapse All ----
    var allExpanded = false;
    $('#rpt-expand-all').on('click', function() {
        allExpanded = !allExpanded;
        if (allExpanded) {
            $('.rpt-detail-row').stop(true).css('display', 'table-row');
            $('.rpt-expand-btn').addClass('rpt-expanded');
            $('.rpt-summary-row').addClass('rpt-open');
            $(this).find('span').text('Tout replier');
        } else {
            $('.rpt-detail-row').stop(true).hide();
            $('.rpt-expand-btn').removeClass('rpt-expanded');
            $('.rpt-summary-row').removeClass('rpt-open');
            $(this).find('span').text('Tout déplier');
        }
    });

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

        var pairs = [];
        $tbody.find('tr.rpt-summary-row').each(function() {
            var $sum = $(this);
            var $det = $sum.next('tr.rpt-detail-row');
            var cellText = $.trim($sum.find('td').eq(colIdx + 1).text());
            pairs.push({ $sum: $sum, $det: $det, val: cellText });
        });

        if (currentDir === 'none') {
            pairs.sort(function(a, b) {
                var ka = parseInt(a.$sum.data('key')) || 0;
                var kb = parseInt(b.$sum.data('key')) || 0;
                return ka - kb;
            });
        } else {
            pairs.sort(function(a, b) {
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

        // Re-apply zebra after sorting
        for (var i = 0; i < pairs.length; i++) {
            $tbody.append(pairs[i].$sum);
            $tbody.append(pairs[i].$det);
            pairs[i].$sum.removeClass('rpt-row-even rpt-row-odd').addClass(i % 2 === 0 ? 'rpt-row-even' : 'rpt-row-odd');
        }
    });

    function parseDate(str) {
        var m = str.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?/);
        if (m) return new Date(m[3], m[2]-1, m[1], m[4]||0, m[5]||0);
        m = str.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (m) return new Date(m[1], m[2]-1, m[3]);
        return null;
    }

    // ---- Amount filter ----
    var filterTimer = null;
    // Total TTC = col 6 (+1 for expand td)
    var COL_TOTAL = 7;

    function parseAmount(str) {
        var clean = $.trim(str).replace(/\s/g, '').replace(',', '.');
        var n = parseFloat(clean);
        return isNaN(n) ? null : n;
    }

    function matchAmount(cellVal, query) {
        query = $.trim(query);
        if (!query) return true;
        var amount = parseAmount(cellVal);
        if (amount === null) return false;

        // Range: "10-30"
        var rangeMatch = query.match(/^(-?\d[\d\s.,]*)\s*[-\u2013]\s*(-?\d[\d\s.,]*)$/);
        if (rangeMatch) {
            var lo = parseAmount(rangeMatch[1]);
            var hi = parseAmount(rangeMatch[2]);
            if (lo !== null && hi !== null) return amount >= lo && amount <= hi;
        }
        // Comparison: ">100", ">=50", "<20", "<=10"
        var cmpMatch = query.match(/^([<>]=?)\s*(-?\d[\d\s.,]*)$/);
        if (cmpMatch) {
            var op = cmpMatch[1], val = parseAmount(cmpMatch[2]);
            if (val !== null) {
                if (op === '>')  return amount > val;
                if (op === '>=') return amount >= val;
                if (op === '<')  return amount < val;
                if (op === '<=') return amount <= val;
            }
        }
        // Exact match
        var qVal = parseAmount(query);
        if (qVal !== null) {
            if (Math.abs(amount - qVal) < 0.005) return true;
            return cellVal.indexOf(query) !== -1;
        }
        return cellVal.indexOf(query) !== -1;
    }

    function applyFilters() {
        var amountQuery = $('#rpt-filter-amount').val();
        var hasFilter = $.trim(amountQuery) !== '';

        $('#rpt-filter-amount').toggleClass('rpt-filter-active', hasFilter);
        $('#rpt-clear-filters').toggle(hasFilter);

        var visibleCount = 0;
        $('tr.rpt-summary-row').each(function() {
            var $row = $(this);
            var $detail = $row.next('tr.rpt-detail-row');
            var totalText = $.trim($row.find('td').eq(COL_TOTAL).text());

            if (matchAmount(totalText, amountQuery)) {
                $row.show();
                $row.removeClass('rpt-row-even rpt-row-odd').addClass(visibleCount % 2 === 0 ? 'rpt-row-even' : 'rpt-row-odd');
                visibleCount++;
            } else {
                $row.hide();
                $detail.hide();
                $row.find('.rpt-expand-btn').removeClass('rpt-expanded');
                $row.removeClass('rpt-open');
            }
        });
        $('#rpt-count').text(visibleCount);
    }

    $('#rpt-filter-amount').on('input', function() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(applyFilters, 200);
    });
    $('#rpt-filter-amount').on('keydown', function(e) {
        if (e.keyCode === 13) { e.preventDefault(); clearTimeout(filterTimer); applyFilters(); }
        if (e.keyCode === 27) { $(this).val(''); clearTimeout(filterTimer); applyFilters(); }
    });
    $('#rpt-clear-filters').on('click', function() {
        $('#rpt-filter-amount').val('');
        applyFilters();
    });
});
</script>

<?php
    $this->load->view("partial/pre_footer");
	$this->load->view("partial/footer");
} // end if not is excel export
?>

                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
