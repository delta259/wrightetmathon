<?php
// load the header
$this->load->view("partial/head");
$this->load->view("partial/header_banner");

// set up short form text
$mode_short_text = ellipsize($_SESSION['title'], 10, .5);

// Adhoc stock: override prices from preferred supplier
foreach ($cart as $key => $item) {
    if ($_SESSION["receiving_title"] == "Mouvement de stock divers") {
        $this->db->select('supplier_cost_price');
        $this->db->from('items_suppliers');
        $this->db->where('items_suppliers.supplier_preferred', 'Y');
        $this->db->where('items_suppliers.item_id', $item['item_id']);
        $this->db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
        $price_data = $this->db->get()->result_array();
        if (!empty($price_data)) {
            $cart[$key]['price'] = $price_data[0]['supplier_cost_price'];
        }
    }
}

// Determine display settings
$stock_action = $_SESSION['stock_action_id_stock_choix_liste'] ?? 0;
$show_ventes = (($_SESSION['Stock_only'] ?? 1) != 1) && (($_SESSION['display_ventes'] ?? 0) == 1);
$is_po_action = in_array($stock_action, array(10, 40, 50));
?>

<style>
/* --- Receivings page (rcv-) --- */
.rcv-page { max-width: 1400px; margin: 0 auto; padding: 0 0.5em; }
.rcv-layout { display: grid; grid-template-columns: 1fr 280px; gap: 0.8em; align-items: start; }
@media (max-width: 900px) { .rcv-layout { grid-template-columns: 1fr; } }

/* Header bar */
.rcv-header-bar {
    display: flex; align-items: center; justify-content: space-between; gap: 0.6em;
    padding: 0.5em 0; margin-bottom: 0.5em; flex-wrap: wrap;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
}
.rcv-title-group { display: flex; align-items: center; gap: 0.5em; }
.rcv-title-icon {
    width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
    background: rgba(37,99,235,0.1); color: var(--primary, #2563eb);
}
.rcv-title { font-size: 1.1em; font-weight: 700; color: var(--text-primary, #1e293b); margin: 0; }
.rcv-subtitle { font-size: 0.75em; color: var(--text-secondary, #64748b); }

/* Search bar */
.rcv-search-bar {
    display: flex; align-items: center; gap: 0.4em; padding: 0.4em 0.6em;
    background: var(--bg-card, #f8fafc); border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px; margin-bottom: 0.5em;
}
.rcv-search-input {
    flex: 1; border: none; background: transparent; font-size: 0.92em; padding: 0.3em 0.4em;
    color: var(--text-primary, #1e293b); outline: none;
}
.rcv-search-input::placeholder { color: var(--text-secondary, #94a3b8); }
.rcv-search-icon { color: var(--text-secondary, #94a3b8); flex-shrink: 0; }
.rcv-filter-btn {
    display: flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 6px;
    border: 1px solid var(--border-color, #e2e8f0); background: var(--bg-container, #fff);
    color: var(--text-secondary, #64748b); text-decoration: none; transition: all 0.15s;
}
.rcv-filter-btn:hover { border-color: var(--primary, #2563eb); color: var(--primary, #2563eb); }
.rcv-filter-btn.rcv-filter-active { background: var(--primary, #2563eb); color: #fff; border-color: var(--primary, #2563eb); }
.rcv-action-link {
    display: flex; align-items: center; gap: 0.3em; padding: 0.3em 0.7em;
    font-size: 0.82em; font-weight: 600; border-radius: 6px;
    background: var(--primary, #2563eb); color: #fff; text-decoration: none;
    transition: background 0.15s; white-space: nowrap;
}
.rcv-action-link:hover { background: #1d4ed8; }

/* Inline toast (next to title) */
.rcv-toast {
    display: inline-flex; align-items: center; gap: 0.4em;
    padding: 0.3em 0.7em; border-radius: 6px;
    font-size: 0.78em; font-weight: 500; line-height: 1.3;
    animation: rcv-toast-in 0.3s ease-out, rcv-toast-out 0.4s 8s ease-in forwards;
    cursor: pointer; white-space: nowrap; max-width: 100%;
}
@media (max-width: 700px) { .rcv-toast { white-space: normal; font-size: 0.72em; } }
.rcv-toast-error {
    background: color-mix(in srgb, var(--danger, #ef4444) 10%, var(--bg-container, #fff));
    color: var(--danger, #ef4444);
}
.rcv-toast-warning {
    background: color-mix(in srgb, var(--warning, #f59e0b) 10%, var(--bg-container, #fff));
    color: var(--warning, #d97706);
}
.rcv-toast-success {
    background: color-mix(in srgb, var(--success, #22c55e) 10%, var(--bg-container, #fff));
    color: var(--success, #16a34a);
}
.rcv-toast-icon { flex-shrink: 0; }
.rcv-toast-text { overflow: hidden; text-overflow: ellipsis; }
@keyframes rcv-toast-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes rcv-toast-out { from { opacity: 1; } to { opacity: 0; } }

/* Cart table */
.rcv-card { background: var(--bg-container, #fff); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; overflow: hidden; }
.rcv-scroll { max-height: 70vh; overflow-y: auto; overflow-x: auto; }
.rcv-table { width: 100%; border-collapse: collapse; }
.rcv-table th {
    font-size: 0.65em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); padding: 0.4em 0.5em;
    border-bottom: 2px solid var(--border-color, #e2e8f0); white-space: nowrap;
    cursor: pointer; user-select: none;
    position: sticky; top: 0; z-index: 2;
    background: var(--bg-container, #fff);
}
.rcv-table th:hover { color: var(--primary, #2563eb); }
.rcv-table td {
    padding: 0.2em 0.5em; font-size: 0.85em;
    border-bottom: 1px solid color-mix(in srgb, var(--border-color, #e2e8f0) 50%, transparent);
    color: var(--text-primary, #1e293b); vertical-align: middle;
}
.rcv-table tr:last-child td { border-bottom: none; }
.rcv-table tbody tr:hover td { background: color-mix(in srgb, var(--primary, #2563eb) 3%, transparent); }
.rcv-table tbody tr.rcv-highlight td { background: rgba(34,197,94,0.08); }
.rcv-table .rcv-col-actions { width: 30px; text-align: center; }
.rcv-table .rcv-col-num { text-align: right; font-variant-numeric: tabular-nums; }
.rcv-table .rcv-col-center { text-align: center; }
.rcv-del-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 4px; color: var(--danger, #ef4444);
    text-decoration: none; transition: background 0.15s;
}
.rcv-del-btn:hover { background: rgba(239,68,68,0.1); }
.rcv-edit-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; border-radius: 4px; border: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc); color: var(--primary, #2563eb); cursor: pointer;
}
.rcv-edit-btn:hover { border-color: var(--primary, #2563eb); background: var(--bg-container, #fff); }
.rcv-kit-badge { font-size: 0.7em; font-weight: 700; color: #2563eb; background: rgba(37,99,235,0.1); padding: 1px 5px; border-radius: 3px; }
.rcv-item-link { color: var(--primary, #2563eb); text-decoration: none; font-weight: 500; }
.rcv-item-link:hover { text-decoration: underline; }
.rcv-qty-input {
    width: 60px; text-align: right; padding: 0.2em 0.3em; font-size: 0.95em;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
    font-variant-numeric: tabular-nums;
}
.rcv-qty-input:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
.rcv-discount-input {
    width: 48px; text-align: right; padding: 0.2em 0.3em; font-size: 0.92em;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
}
.rcv-discount-input:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
.rcv-small-action {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 4px; color: var(--text-secondary, #94a3b8);
    text-decoration: none; transition: all 0.15s;
}
.rcv-small-action:hover { color: var(--primary, #2563eb); background: rgba(37,99,235,0.06); }
.rcv-empty-cart {
    text-align: center; padding: 2em; color: var(--text-secondary, #94a3b8);
    font-size: 0.92em; font-style: italic;
}

/* Sidebar */
.rcv-sidebar { display: flex; flex-direction: column; gap: 0.6em; }
.rcv-side-card {
    background: var(--bg-container, #fff); border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px; padding: 0.6em 0.8em;
}
.rcv-side-title {
    font-size: 0.7em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b); margin-bottom: 0.4em;
    display: flex; align-items: center; gap: 0.3em;
}
.rcv-supplier-name { font-size: 0.95em; font-weight: 700; color: var(--text-primary, #1e293b); }
.rcv-change-supplier-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 6px;
    border: 1px solid var(--border-color, #e2e8f0); background: var(--bg-card, #f8fafc);
    color: var(--text-secondary, #64748b); text-decoration: none; transition: all 0.15s; float: right;
}
.rcv-change-supplier-btn:hover { border-color: var(--primary, #2563eb); color: var(--primary, #2563eb); }
.rcv-supplier-select { width: 100%; padding: 0.3em 0.4em; font-size: 0.88em; border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px; background: var(--bg-container, #fff); color: var(--text-primary, #1e293b); }
.rcv-stat-row { display: flex; justify-content: space-between; align-items: center; padding: 0.2em 0; }
.rcv-stat-label { font-size: 0.82em; color: var(--text-secondary, #64748b); }
.rcv-stat-value { font-size: 1em; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--text-primary, #1e293b); }
.rcv-total-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.4em 0.6em; margin-top: 0.3em;
    background: var(--primary, #2563eb); color: #fff; border-radius: 6px;
}
.rcv-total-label { font-size: 0.82em; font-weight: 700; text-transform: uppercase; }
.rcv-total-value { font-size: 1.1em; font-weight: 700; font-variant-numeric: tabular-nums; }

/* Partial receive checkbox column */
.rcv-col-checkbox { width: 28px; text-align: center; }
.rcv-col-checkbox input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: #8b5cf6; }
.rcv-table th.rcv-col-checkbox, .rcv-table th.rcv-col-actions { cursor: default; }
.rcv-table th.rcv-col-checkbox:hover, .rcv-table th.rcv-col-actions:hover { color: var(--text-secondary, #64748b); }
.rcv-partial-received td:not(.rcv-col-checkbox) { background: color-mix(in srgb, #22c55e 8%, transparent) !important; }
.rcv-btn-partial {
    display: flex; align-items: center; gap: 0.4em; justify-content: center;
    padding: 0.5em 0.6em; font-size: 0.85em; font-weight: 600;
    border-radius: 6px; text-decoration: none; transition: all 0.15s; cursor: pointer; border: none;
    background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff;
}
.rcv-btn-partial:hover { background: linear-gradient(135deg, #7c3aed, #5b21b6); }

/* Action buttons */
.rcv-action-group { display: flex; flex-direction: column; gap: 0.4em; }
.rcv-btn {
    display: flex; align-items: center; gap: 0.4em; justify-content: center;
    padding: 0.5em 0.6em; font-size: 0.85em; font-weight: 600;
    border-radius: 6px; text-decoration: none; transition: all 0.15s; cursor: pointer; border: none;
}
.rcv-btn-complete { background: var(--success, #22c55e); color: #fff; }
.rcv-btn-complete:hover { background: #16a34a; }
.rcv-btn-cancel { background: var(--danger, #ef4444); color: #fff; }
.rcv-btn-cancel:hover { background: #dc2626; }
.rcv-btn-suspend { background: var(--warning, #f59e0b); color: #fff; }
.rcv-btn-suspend:hover { background: #d97706; }
.rcv-comment-textarea {
    width: 100%; padding: 0.3em 0.4em; font-size: 0.85em; font-family: inherit;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 4px; resize: vertical;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b); min-height: 50px; box-sizing: border-box;
}
.rcv-comment-textarea:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }

/* Page transition — progress bar + subtle dimmer, keeps page visible */
.rcv-progress-bar {
    position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9999;
    background: transparent; overflow: hidden; pointer-events: none;
    opacity: 0; transition: opacity 0.15s;
}
.rcv-progress-bar.active { opacity: 1; }
.rcv-progress-track {
    height: 100%; width: 30%;
    background: linear-gradient(90deg, transparent, var(--primary, #2563eb), var(--primary, #2563eb), transparent);
    border-radius: 0 2px 2px 0;
    animation: rcv-progress-slide 1.2s ease-in-out infinite;
}
@keyframes rcv-progress-slide { 0% { transform: translateX(-100%); } 100% { transform: translateX(400%); } }
.rcv-dimmer {
    position: fixed; inset: 0; z-index: 9997;
    background: color-mix(in srgb, var(--bg-container, #fff) 60%, transparent);
    pointer-events: all; opacity: 0; transition: opacity 0.2s;
}
.rcv-dimmer.active { opacity: 1; }
</style>

<div id="wrapper" class="wlp-bighorn-book">
<?php $this->load->view("partial/header_menu"); ?>

<div class="wlp-bighorn-book">
<div class="wlp-bighorn-book-content">
<main id="login_page" class="wlp-bighorn-page-unconnect" role="main">
<div class="body_page">
<div class="body_colonne">

<?php
// Build title with stock action details
$title_text = '';
if ($_SESSION['title'] != NULL) {
    $_SESSION['receiving_title'] = $_SESSION['title'];
}
$title_text = $_SESSION['receiving_title'];
$title_details = '';

if (($_SESSION['Stock_only'] ?? 1) != 1) {
    if (in_array($stock_action, array(10, 40, 50))) {
        if (!isset($_SESSION['nbre_jour_prevision_stock_correct']) || ($_SESSION['nbre_jour_prevision_stock_correct'] == 0)) {
            $_SESSION['nbre_jour_prevision_stock_correct'] = $this->config->item('nbre_jour_prevision_stock');
        }
        if (!isset($_SESSION['historique_correct']) || ($_SESSION['historique_correct'] == 0)) {
            $_SESSION['historique_correct'] = $this->config->item('historique');
        }
        $title_details = $this->lang->line('common_for') . ' ' . $_SESSION['nbre_jour_prevision_stock_correct'] . ' ' . $this->lang->line('recvs_jours') . ' ' . $this->lang->line('common_on_base_to_sur_la_base_de') . ' ' . $_SESSION['historique_correct'] . ' ' . $this->lang->line('recvs_jours') . ' ' . $this->lang->line('common_from_1') . ' ' . $this->lang->line('common_sales_ventes');
    }
}
?>

<!-- Header bar -->
<div class="rcv-header-bar">
    <div class="rcv-title-group">
        <div class="rcv-title-icon">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
        </div>
        <div>
            <h2 class="rcv-title"><?php echo $title_text; ?></h2>
            <?php if ($title_details) { ?>
            <div class="rcv-subtitle"><?php echo $title_details; ?></div>
            <?php } ?>
        </div>
    </div>
    <!-- Inline toast -->
    <?php
    $_rcv_toast_html = '';
    if (isset($error)) {
        $_rcv_toast_html = '<div class="rcv-toast rcv-toast-error" onclick="this.remove()">
            <svg class="rcv-toast-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            <span class="rcv-toast-text">' . htmlspecialchars($error) . '</span></div>';
    }
    if (empty($_SESSION['show_dialog']) && isset($_SESSION['error_code']) && $_SESSION['error_code'] !== '' && isset($_SESSION['G']->messages[$_SESSION['error_code']])) {
        $__msg = $_SESSION['G']->messages[$_SESSION['error_code']];
        if (isset($_SESSION['substitution_parms'])) {
            for ($__i = 0; $__i <= 2; $__i++) {
                if (isset($_SESSION['substitution_parms'][$__i])) {
                    $__msg[2] = str_replace("$" . $__i, $_SESSION['substitution_parms'][$__i], $__msg[2]);
                }
            }
        }
        if (isset($__msg[1]) && isset($__msg[2])) {
            $__type = 'error';
            if (strpos($__msg[1], 'success') !== false) $__type = 'success';
            elseif (strpos($__msg[1], 'warning') !== false) $__type = 'warning';
            $__icon = ($__type === 'success')
                ? '<polyline points="20 6 9 17 4 12"></polyline>'
                : (($__type === 'warning')
                    ? '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>'
                    : '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>');
            $_rcv_toast_html = '<div class="rcv-toast rcv-toast-' . $__type . '" onclick="this.remove()">
                <svg class="rcv-toast-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">' . $__icon . '</svg>
                <span class="rcv-toast-text">' . $__msg[2] . '</span></div>';
        }
        unset($_SESSION['error_code']);
        $_SESSION['substitution_parms'] = array();
    }
    echo $_rcv_toast_html;
    ?>

    <?php if (empty($cart)) { ?>
    <a href="<?php echo site_url($_SESSION['controller_name'].'/stock_actions_1/'); ?>" class="rcv-action-link">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
        </svg>
        <?php echo $this->lang->line('receivings_stock_actions'); ?>
    </a>
    <?php } ?>
</div>

<!-- Search bar -->
<div class="rcv-search-bar">
    <?php echo form_open("receivings/add", array('id' => 'add_item_form', 'style' => 'display:flex;align-items:center;flex:1;gap:0.4em;margin:0;')); ?>
    <svg class="rcv-search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
    <input id="item" name="item" class="rcv-search-input" placeholder="Recherche article..." type="text"
           tabindex="5">
    </form>
    <a href="<?php echo site_url("receivings/filtre"); ?>" class="rcv-filter-btn<?php echo ($_SESSION['filtre_receivings'] ?? 0) == 1 ? ' rcv-filter-active' : ''; ?>"
       title="<?php echo ($_SESSION['filtre_receivings'] ?? 0) == 1 ? 'Activer l\'ajout d\'articles' : 'Activer la modification des lignes'; ?>">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <?php if (($_SESSION['filtre_receivings'] ?? 0) == 1) { ?>
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            <?php } else { ?>
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
            <?php } ?>
        </svg>
    </a>
</div>

<!-- Main layout -->
<div class="rcv-layout">

<!-- LEFT: Cart table -->
<div class="rcv-card">
<div class="rcv-scroll">
<table class="rcv-table" id="sortable_table">
    <thead>
        <tr>
            <th class="rcv-col-checkbox"><input type="checkbox" id="rcv_select_all" title="Tout sélectionner"></th>
            <th class="rcv-col-actions"></th>
            <th class="rcv-col-center"><?php echo $this->lang->line('DynamicKit'); ?></th>
            <th><?php echo $this->lang->line('items_category'); ?></th>
            <th><?php echo $this->lang->line('items_item_number'); ?></th>
            <th>SKU</th>
            <th><?php echo $this->lang->line('sales_item_name'); ?></th>
            <th class="rcv-col-num"><?php echo $this->lang->line('recvs_cost'); ?></th>
            <th class="rcv-col-num">Stock</th>
            <th class="rcv-col-num"><?php echo $this->lang->line('module_central'); ?></th>
            <?php if ($show_ventes) { ?>
            <th class="rcv-col-num"><?php echo $this->lang->line('sales_items_periode'); ?></th>
            <?php } ?>
            <th class="rcv-col-num"><?php echo $this->lang->line('sales_quantity'); ?></th>
            <?php if ($mode != 'purchaseorder') { ?>
            <th class="rcv-col-center"><?php echo $this->lang->line('items_dluo'); ?></th>
            <?php } ?>
            <th class="rcv-col-num"><?php echo $this->lang->line('sales_discount'); ?></th>
            <th class="rcv-col-num"><?php echo $this->lang->line('sales_total'); ?></th>
            <th class="rcv-col-center">D&eacute;sact.</th>
            <th class="rcv-col-center">Veille</th>
        </tr>
    </thead>
    <tbody id="cart_contents">
    <?php
    $newcart = 'N';
    if (count($cart) == 0) {
        $newcart = 'Y';
    ?>
        <tr><td colspan="17" class="rcv-empty-cart">
            <?php echo $this->lang->line('sales_no_items_in_cart'); ?>
        </td></tr>
    <?php
    } else {
        // if entire receipt do not reorder the cart
        switch ($data['entire_receipt'] ?? 'N') {
            case 'Y': $foreach = $cart; break;
            case 'N': default: $foreach = array_reverse($cart, true);
        }

        foreach ($foreach as $line => $item) {
            $cur_item_info = $this->Item->get_info($item['item_id']);
            $DynamicKit_settext = ($cur_item_info->DynamicKit == 1) ? 'OUI' : '';

            echo form_open("receivings/edit_item/$line");

            // Supplier reorder pack size
            if ($is_po_action) {
                $input_sp = array('supplier_reorder_policy' => 'Y', 'supplier_id' => $_SESSION['supplier_id'], 'item_id' => $cur_item_info->item_id);
                $data_sp = $this->Item->get_supplier_reorder_pack_size($input_sp);
                $_SESSION['supplier_reorder_pack_size'] = intval($data_sp[0]['supplier_reorder_pack_size'] ?? 1);
            } else {
                $_SESSION['supplier_reorder_pack_size'] = 1;
            }
            // Supplier SKU
            $this->db->select('supplier_item_number');
            $this->db->from('items_suppliers');
            $this->db->where('item_id', $cur_item_info->item_id);
            $this->db->where('supplier_id', $_SESSION['supplier_id'] ?? 0);
            $sku_row = $this->db->get()->row();
            $supplier_sku = ($sku_row && $sku_row->supplier_item_number) ? $sku_row->supplier_item_number : '';

            // Determine if this is the active line
            $is_active_line = ($_SESSION['line'] ?? '') == $line;
            $row_class = $is_active_line ? 'rcv-highlight' : '';
    ?>
        <tr class="<?php echo $row_class; ?>" data-price="<?php echo $item['price']; ?>" data-line="<?php echo $line; ?>" <?php if ($is_active_line) echo 'id="line_couleur"'; ?>>
            <!-- Partial receive checkbox -->
            <td class="rcv-col-checkbox"><input type="checkbox" class="rcv-partial-chk" value="<?php echo $line; ?>"></td>
            <!-- Delete -->
            <td class="rcv-col-actions">
                <a href="<?php echo site_url("receivings/delete_item/$line"); ?>" class="rcv-del-btn" title="<?php echo $this->lang->line('common_delete'); ?>">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </a>
            </td>
            <!-- Dynamic Kit -->
            <td class="rcv-col-center"><?php if ($DynamicKit_settext) { ?><span class="rcv-kit-badge">KIT</span><?php } ?></td>
            <!-- Category -->
            <td><?php echo $item['category']; ?></td>
            <!-- Item number -->
            <td><a href="<?php echo site_url("items/view/$cur_item_info->item_id/RR"); ?>" class="rcv-item-link"><?php echo $item['item_number']; ?></a></td>
            <!-- SKU (supplier item number) -->
            <td style="font-size:0.8em;color:var(--text-secondary,#64748b);font-family:monospace;"><?php echo htmlspecialchars($supplier_sku); ?></td>
            <!-- Name -->
            <td><?php echo $item['name']; ?></td>
            <!-- Cost price -->
            <td class="rcv-col-num"><?php echo $item['price']; ?><?php echo form_hidden('price', $item['price']); ?></td>
            <!-- Stock -->
            <td class="rcv-col-num"><a href="<?php echo site_url("receivings/inventory/$cur_item_info->item_id"); ?>" class="rcv-item-link"><?php echo round($cur_item_info->quantity, 0); ?></a></td>
            <!-- Central -->
            <td class="rcv-col-num"><?php echo $item['quantity_central']; ?></td>
            <!-- Ventes -->
            <?php if ($show_ventes) { ?>
            <td class="rcv-col-num"><?php echo $item['ventes']; ?></td>
            <?php } ?>
            <!-- Quantity -->
            <?php
            // Handle title-based stock action override
            if ($_SESSION['title'] == 'Approvisionner') {
                $_SESSION['stock_action_id_stock_choix_liste'] = 10;
            }
            $step = max(1, intval($_SESSION['supplier_reorder_pack_size'] ?? 1));
            $min_val = -1000 * $step;
            ?>
            <td class="rcv-col-num">
                <?php echo form_input(array(
                    'type' => 'number',
                    'min' => $min_val,
                    'max' => '5001',
                    'step' => $step,
                    'name' => 'quantity',
                    'value' => $is_active_line ? $item['quantity'] : round($item['quantity'], 0),
                    'class' => 'rcv-qty-input',
                    'autofocus' => $is_active_line ? 'autofocus' : ''
                )); ?>
            </td>
            <!-- DLUO -->
            <?php if ($mode != 'purchaseorder') { ?>
            <td class="rcv-col-center">
                <?php if ($cur_item_info->dluo_indicator == 'Y') {
                    echo anchor('items/dluo_form/'.$cur_item_info->item_id.'/DR/'.$line, $this->lang->line('items_dluo_x'));
                } ?>
            </td>
            <?php } ?>
            <!-- Discount -->
            <td class="rcv-col-num">
                <?php echo form_input(array('type' => 'number', 'name' => 'discount', 'value' => intval($item['discount']), 'min' => '0', 'max' => '100', 'step' => '1', 'class' => 'rcv-discount-input')); ?>
            </td>
            <!-- Total -->
            <td class="rcv-col-num" style="font-weight:600;"><?php echo to_currency($item['price'] * $item['quantity'] - $item['price'] * $item['quantity'] * $item['discount'] / 100); ?></td>
            <!-- Deactivate -->
            <?php $tout = (string)$cur_item_info->item_id . ':' . (string)$line . ':receivings'; ?>
            <td class="rcv-col-center">
                <a href="<?php echo site_url("receivings/desactive/$tout"); ?>" class="rcv-small-action" title="D&eacute;sactiver">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                </a>
            </td>
            <!-- Competitive intelligence -->
            <td class="rcv-col-center">
                <a href="<?php echo site_url("items/competitive_intelligence/$cur_item_info->item_id"); ?>" class="rcv-small-action" title="Veille concurrentielle">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </a>
            </td>
        </tr>
        </form>
    <?php
        }
    }
    ?>
    </tbody>
</table>
</div>
</div><!-- /rcv-card -->

<!-- RIGHT: Sidebar -->
<div class="rcv-sidebar">
<div id="overall_sale">

    <!-- Supplier card -->
    <?php if ($_SESSION['receiving_title'] != "Mouvement de stock divers") { ?>
    <div class="rcv-side-card">
        <div class="rcv-side-title">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <?php echo $this->lang->line('recvs_supplier') ?: 'Fournisseur'; ?>
        </div>
        <?php if (isset($supplier)) { ?>
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span class="rcv-supplier-name"><?php echo $supplier; ?></span>
                <a href="<?php echo site_url('receivings/delete_supplier'); ?>" class="rcv-change-supplier-btn" title="<?php echo $this->lang->line('common_change').' de '.$this->lang->line('suppliers_supplier'); ?>">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                </a>
            </div>
        <?php } else { ?>
            <?php echo form_open("receivings/select_supplier", array('id' => 'select_supplier_form')); ?>
            <label id="supplier_label" for="supplier" style="font-size:0.8em; color:var(--text-secondary,#64748b);"><?php echo $this->lang->line('recvs_supplier'); ?></label>
            <?php echo form_dropdown('supplier_id', $_SESSION['G']->supplier_pick_list, 0, 'class="rcv-supplier-select" onchange="$(\'#select_supplier_form\').submit();"'); ?>
            </form>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- Totals card -->
    <div class="rcv-side-card">
        <div class="rcv-side-title">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            R&eacute;capitulatif
        </div>
        <?php
        $total = 0;
        $quantity = 0;
        foreach ($cart as $item) {
            $total += $item['quantity'] * $item['price'];
            $quantity += $item['quantity'];
        }
        ?>
        <div class="rcv-stat-row">
            <span class="rcv-stat-label">Nombre de produits</span>
            <span class="rcv-stat-value" id="rcv_sum_qty"><?php echo $quantity; ?></span>
        </div>
        <div class="rcv-stat-row">
            <span class="rcv-stat-label">R&eacute;f&eacute;rences</span>
            <span class="rcv-stat-value" id="rcv_sum_refs"><?php echo count($cart); ?></span>
        </div>
        <div class="rcv-total-row">
            <span class="rcv-total-label">Total HT</span>
            <span class="rcv-total-value" id="rcv_sum_total"><?php echo to_currency($total); ?></span>
        </div>
    </div>

    <!-- Actions card -->
    <?php if (count($cart) > 0) { ?>
    <div class="rcv-side-card">
        <div class="rcv-side-title">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            Actions
        </div>

        <?php echo form_open("receivings/complete", array('id' => 'finish_sale_form')); ?>

        <!-- Comment -->
        <label style="font-size:0.75em; color:var(--text-secondary,#64748b); margin-bottom:0.2em; display:block;"><?php echo $this->lang->line('common_comments'); ?></label>
        <?php echo form_textarea(array('name' => 'comment', 'id' => 'comment', 'value' => $comment, 'rows' => '2', 'class' => 'rcv-comment-textarea')); ?>

        <div class="rcv-action-group" style="margin-top: 0.5em;">
            <!-- Partial receive (hidden by default, shown when some items unchecked) -->
            <a href="#" id="rcv_partial_receive_btn" class="rcv-btn rcv-btn-partial" style="display:none;" onclick="rcvSubmitPartialReceive(); return false;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span id="rcv_partial_label">Réception partielle</span>
            </a>

            <!-- Complete -->
            <a href="<?php echo site_url($_SESSION['controller_name'].'/confirm/stocktransaction'); ?>" class="rcv-btn rcv-btn-complete">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <?php echo $this->lang->line('recvs_complete_receiving'); ?>
            </a>

            <!-- Cancel -->
            <a href="<?php echo site_url($_SESSION['controller_name'].'/confirm/canceltransaction'); ?>" class="rcv-btn rcv-btn-cancel">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                <?php echo $this->lang->line('recvs_cancel'); ?>
            </a>

            <!-- Suspend (PO or Reception) -->
            <?php
            switch ($_SESSION['stock_action_id'] ?? 0) {
                case 10: ?>
                    <a href="<?php echo site_url($_SESSION['controller_name'].'/confirm/suspendtransaction'); ?>" class="rcv-btn rcv-btn-suspend">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        <?php echo $this->lang->line('receivings_suspend'); ?>
                    </a>
                <?php break;
                case 20: ?>
                    <a href="<?php echo site_url($_SESSION['controller_name'].'/confirm/suspendreception'); ?>" class="rcv-btn rcv-btn-suspend">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        <?php echo $this->lang->line('receivings_suspend'); ?>
                    </a>
                <?php break;
            }
            ?>
        </div>
        </form>
    </div>
    <?php } ?>

</div><!-- /overall_sale -->
</div><!-- /rcv-sidebar -->

</div><!-- /rcv-layout -->

</div><!-- /body_colonne -->
</div><!-- /body_page -->
</main>
</div>
</div>
</div><!-- /wrapper -->

<?php
// Show dialog depending on show_dialog
switch ($_SESSION['show_dialog'] ?? 0) {
    case 1: include('../wrightetmathon/application/views/receivings/form_stock_actions.php'); break;
    case 2: include('../wrightetmathon/application/views/sales/confirm.php'); break;
    case 3: include('../wrightetmathon/application/views/receivings/suspended.php'); break;
    case 4: include('../wrightetmathon/application/views/receivings/suspended_receptions.php'); break;
    case 5: include('../wrightetmathon/application/views/receivings/suspended_merge.php'); break;
    case 6: include('../wrightetmathon/application/views/receivings/inventory_low_by_date.php'); break;
    case 7: include('../wrightetmathon/application/views/receivings/suspended_recep.php'); break;
    case 8: include('../wrightetmathon/application/views/receivings/form_stock_inventory.php'); break;
    case 9: include('../wrightetmathon/application/views/receivings/form_stock_inventory_after_verify.php'); break;
    case 10: include('../wrightetmathon/application/views/receivings/form_competitive_intellignece.php'); break;
}
unset($_SESSION['Stock_only']);
?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
$(document).ready(function() {
    // Item autocomplete
    $("#item").autocomplete('<?php echo site_url("receivings/item_search"); ?>', {
        minChars: 0, max: 100, delay: 10, selectFirst: true,
        formatItem: function(row) { return row[1]; }
    });
    $("#item").result(function(event, data, formatted) {
        $("#add_item_form").submit();
    });

    // Clear item input on click for easy re-entry
    $('#item').click(function() {
        $(this).val('');
    });

    // Supplier autocomplete
    $("#supplier").autocomplete('<?php echo site_url("receivings/supplier_search"); ?>', {
        minChars: 0, delay: 10, max: 100,
        formatItem: function(row) { return row[1]; }
    });
    $("#supplier").result(function(event, data, formatted) {
        $("#select_supplier_form").submit();
    });
    // Clear supplier input on click for easy re-entry
    $('#supplier').click(function() {
        $(this).val('');
    });

    // Comment auto-save
    $('#comment').change(function() {
        $.post('<?php echo site_url("receivings/set_comment"); ?>', {comment: $('#comment').val()});
    });

    // Tablesorter — disable sorting on checkbox (col 0) and actions (col 1) columns
    $("#sortable_table").tablesorter({
        headers: { 0: { sorter: false }, 1: { sorter: false } }
    });

    // Focus management — after autocomplete is bound, set focus via jQuery
    // so the plugin receives the focus event properly
    <?php if (($_SESSION['filtre_receivings'] ?? 0) != 1) { ?>
    $('#item').blur().focus();
    <?php } ?>

    // Live recalculation of summary when quantity or discount changes
    var currSign = '<?php echo $_SESSION['G']->currency_details->currency_sign ?? "€"; ?>';
    var currSide = '<?php echo $_SESSION['G']->currency_details->currency_side ?? "R"; ?>';
    function rcvFmtCurrency(n) {
        var s = n.toFixed(2);
        return currSide === 'L' ? currSign + s : s + currSign;
    }
    function rcvRecalcSummary() {
        var totalQty = 0, totalHT = 0, refs = 0;
        $('#sortable_table tbody tr').each(function() {
            var $row = $(this);
            var price = parseFloat($row.data('price')) || 0;
            var qty = parseFloat($row.find('.rcv-qty-input').val()) || 0;
            var disc = parseFloat($row.find('.rcv-discount-input').val()) || 0;
            totalQty += qty;
            totalHT += price * qty * (1 - disc / 100);
            refs++;
        });
        $('#rcv_sum_qty').text(Math.round(totalQty));
        $('#rcv_sum_refs').text(refs);
        $('#rcv_sum_total').text(rcvFmtCurrency(totalHT));
    }
    $(document).on('input change', '.rcv-qty-input, .rcv-discount-input', rcvRecalcSummary);

    // Auto-submit the row form when quantity or discount changes (replaces edit button)
    $(document).on('change', '.rcv-qty-input, .rcv-discount-input', function() {
        $(this).closest('form').submit();
    });

    // --- Partial receive checkbox management ---
    // Prevent tablesorter from intercepting checkbox clicks in the <th>
    $('#rcv_select_all').on('click', function(e) {
        e.stopPropagation();
    });

    // Select-all checkbox
    $('#rcv_select_all').on('change', function() {
        var checked = $(this).prop('checked');
        $('.rcv-partial-chk').prop('checked', checked);
        rcvUpdatePartialUI();
    });

    // Individual checkbox change
    $(document).on('change', '.rcv-partial-chk', function() {
        rcvUpdatePartialUI();
    });

    // Initial state
    rcvUpdatePartialUI();
});

function post_item_form_submit(response) {
    if (response.success) {
        $("#item").attr("value", response.item_id);
        $("#add_item_form").submit();
    }
}

function post_person_form_submit(response) {
    if (response.success) {
        $("#supplier").attr("value", response.person_id);
        $("#select_supplier_form").submit();
    }
}

// --- Partial receive UI logic ---
function rcvUpdatePartialUI() {
    var $chks = $('.rcv-partial-chk');
    var total = $chks.length;
    var checked = $chks.filter(':checked').length;
    var unchecked = total - checked;

    // Update select-all state
    var $selAll = $('#rcv_select_all');
    if (checked === total) {
        $selAll.prop('checked', true).prop('indeterminate', false);
    } else if (checked === 0) {
        $selAll.prop('checked', false).prop('indeterminate', false);
    } else {
        $selAll.prop('checked', false).prop('indeterminate', true);
    }

    // Show/hide partial button: visible only when some (not all, not zero) are checked
    // AND there is more than 1 item in cart
    var $btn = $('#rcv_partial_receive_btn');
    if (total > 1 && checked > 0 && checked < total) {
        $btn.show();
        $('#rcv_partial_label').text('Réception partielle (' + checked + '/' + total + ')');
    } else {
        $btn.hide();
    }

    // Toggle row visual state — highlight received (checked) rows
    $chks.each(function() {
        var $row = $(this).closest('tr');
        if ($(this).prop('checked')) {
            $row.addClass('rcv-partial-received');
        } else {
            $row.removeClass('rcv-partial-received');
        }
    });
}

function rcvSubmitPartialReceive() {
    var lines = [];
    $('.rcv-partial-chk:checked').each(function() {
        lines.push($(this).val());
    });
    if (lines.length === 0) return;

    // Create hidden form and submit
    var $form = $('<form>', {method: 'POST', action: '<?php echo site_url("receivings/confirm_partial_receive"); ?>'});
    $.each(lines, function(i, line) {
        $form.append($('<input>', {type: 'hidden', name: 'partial_lines[]', value: line}));
    });
    $('body').append($form);
    $form.submit();
}

// Show subtle loading indicator (progress bar + dimmer) — keeps page visible
function rcvShowLoading() {
    // Progress bar
    var bar = document.getElementById('rcv_progress');
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'rcv_progress'; bar.className = 'rcv-progress-bar';
        bar.innerHTML = '<div class="rcv-progress-track"></div>';
        document.body.appendChild(bar);
    }
    bar.classList.add('active');
    // Dimmer
    var dim = document.getElementById('rcv_dimmer');
    if (!dim) {
        dim = document.createElement('div');
        dim.id = 'rcv_dimmer'; dim.className = 'rcv-dimmer';
        document.body.appendChild(dim);
    }
    dim.classList.add('active');
}
// Intercept modal close/cancel links and action links that trigger page reload
$(document).on('click', 'a.md-modal-close, a.md-btn-secondary, a.rcv-action-link, a.rcv-filter-btn, a.rsa-remote-btn', function() {
    rcvShowLoading();
});
// Also intercept form submits
$(document).on('submit', 'form', function() {
    rcvShowLoading();
});
</script>
