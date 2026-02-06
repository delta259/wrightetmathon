<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() {
    // Enable row selection and select all
    enable_row_selection();
    enable_select_all();

    // Focus search field and place cursor at end
    var searchField = document.getElementById("search");
    if (searchField) {
        searchField.focus();
        // Move cursor to end of text
        var len = searchField.value.length;
        searchField.setSelectionRange(len, len);
    }
});
</script>

<?php
// Get number format parameters
$pieces = explode("/", $this->config->item('numberformat'));
$parms['decimals'] = $pieces[0];
$parms['dec_point'] = $pieces[1];
$parms['thousands_sep'] = $pieces[2];

// Set up parms for retrieving report data
$parms['specific_function'] = 'Specific_' . rtrim($_SESSION['controller_name'], "s");
$parms['start_date'] = date('Y-m-d', 0);
$parms['end_date'] = date('Y-m-d');
$parms['transaction_subtype'] = 'sales';
$parms['export_excel'] = 0;
$parms['history'] = 3;

// Reset line number
$_SESSION['line_number'] = 0;

// Current sort state for column headers
$current_sort_col = $_SESSION['items_sort_col'] ?? 'name';
$current_sort_dir = $_SESSION['items_sort_dir'] ?? 'asc';
?>

        <!-- Page Header - YesAppro Style -->
        <div class="page-header">
            <h1 class="page-title">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <?php echo $this->lang->line('modules_' . $_SESSION['controller_name']); ?>
            </h1>
            <div class="page-actions">
                <!-- Action Buttons -->
                <?php include('../wrightetmathon/application/views/partial/show_buttons.php'); ?>

                <button type="button" class="btn-action" onclick="Printer.print(document.getElementById('sortable_table').innerHTML);" title="Imprimer">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Imprimer
                </button>

                <?php echo form_open($_SESSION['controller_name'].'/exportation', array('id' => 'exportation', 'style' => 'display:inline;')); ?>
                    <button type="submit" class="btn-action" title="Exporter">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Exporter
                    </button>
                </form>

                <!-- Badges -->
                <span class="badge badge-info">
                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                    </svg>
                    <span id="item-count-badge"><?php echo $this->Item->count_all(); ?> articles</span>
                </span>
            </div>
        </div>

        <!-- Filters Bar - YesAppro Style -->
        <div class="filters-bar">
            <!-- Search -->
            <div class="filter-group">
                <?php
                $has_server_search = isset($_SESSION['filtre_recherche']) && $_SESSION['filtre_recherche'] != '';
                echo form_open('items/search', array('id' => 'search_form'));
                ?>
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="search" name="search" class="form-control search-field <?php echo $has_server_search ? 'server-search-active' : ''; ?>"
                           placeholder=""
                           value="<?php echo $has_server_search ? htmlspecialchars($_SESSION['filtre_recherche']) : ''; ?>" tabindex="5" autocomplete="off">
                    <button type="button" id="clear_search" class="search-clear-btn" style="<?php echo $has_server_search ? 'display:flex;' : 'display:none;'; ?>" title="Effacer la recherche">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                </form>
            </div>

            <!-- Category & Supplier Filters -->
            <div class="filter-group">
                <div class="filter-buttons">
                    <select class="filter-select" onchange="window.location.href='<?php echo site_url('items/filter_category'); ?>/'+this.value;">
                        <option value="0">Famille...</option>
                        <?php if (isset($_SESSION['G']->category_pick_list) && is_array($_SESSION['G']->category_pick_list)):
                            foreach ($_SESSION['G']->category_pick_list as $cat_id => $cat_label):
                                if ($cat_id == 0) continue;
                        ?>
                            <option value="<?php echo $cat_id; ?>" <?php echo (isset($_SESSION['filter_category_id']) && $_SESSION['filter_category_id'] == $cat_id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat_label); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                    <select class="filter-select" onchange="window.location.href='<?php echo site_url('items/filter_supplier'); ?>/'+this.value;">
                        <option value="0">Fournisseur...</option>
                        <?php if (isset($_SESSION['G']->supplier_pick_list) && is_array($_SESSION['G']->supplier_pick_list)):
                            foreach ($_SESSION['G']->supplier_pick_list as $sup_id => $sup_label):
                                if ($sup_id == 0) continue;
                        ?>
                            <option value="<?php echo $sup_id; ?>" <?php echo (isset($_SESSION['filter_supplier_id']) && $_SESSION['filter_supplier_id'] == $sup_id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sup_label); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="filter-group">
                <div class="filter-buttons">
                    <!-- Toggle Nouveautés -->
                    <a href="<?php echo site_url("items/toggle_nouveautes"); ?>" class="toggle-switch-wrapper" title="<?php echo (($_SESSION['filtre_nouveautes'] ?? 0) == 1) ? 'Désactiver le filtre nouveautés' : 'Afficher les nouveautés (15 derniers jours)'; ?>">
                        <span class="toggle-switch <?php echo (($_SESSION['filtre_nouveautes'] ?? 0) == 1) ? 'active' : ''; ?>">
                            <span class="toggle-knob"></span>
                        </span>
                        <span class="toggle-label">Nouveautés</span>
                    </a>

                    <!-- Toggle Inactifs -->
                    <a href="<?php echo site_url("items/toggle_deleted"); ?>" class="toggle-switch-wrapper" title="<?php echo (($_SESSION['undel'] ?? 0) == 1) ? 'Afficher les articles actifs' : 'Afficher les articles inactifs'; ?>">
                        <span class="toggle-switch <?php echo (($_SESSION['undel'] ?? 0) == 1) ? 'active' : ''; ?>">
                            <span class="toggle-knob"></span>
                        </span>
                        <span class="toggle-label">Inactifs</span>
                    </a>

                    <!-- Toggle Filtre avancé -->
                    <a href="<?php echo site_url("items/items_avanced_search"); ?>" class="toggle-switch-wrapper" title="<?php echo (($_SESSION['items_avanced_search'] ?? 0) == 1) ? 'Désactiver la recherche avancée' : 'Activer la recherche avancée'; ?>">
                        <span class="toggle-switch <?php echo (($_SESSION['items_avanced_search'] ?? 0) == 1) ? 'active' : ''; ?>">
                            <span class="toggle-knob"></span>
                        </span>
                        <span class="toggle-label">Filtre avancé</span>
                    </a>

                    <!-- Toggle Verrou -->
                    <a href="<?php echo site_url("items/filtre"); ?>" class="toggle-switch-wrapper" title="<?php echo (($_SESSION['filtre'] ?? 0) == 1) ? 'Désactiver le verrou' : 'Activer le verrou'; ?>">
                        <span class="toggle-switch <?php echo (($_SESSION['filtre'] ?? 0) == 1) ? 'active' : ''; ?>">
                            <span class="toggle-knob"></span>
                        </span>
                        <span class="toggle-label">Verrou</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0): ?>
            <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>
        <?php endif; ?>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="data-table tablesorter" id="sortable_table">
                    <colgroup>
                        <col style="width: 32px;"><!-- Checkbox -->
                        <col style="width: 32px;"><!-- Toggle -->
                        <col style="width: 32px;"><!-- EAN -->
                        <col style="width: 88px;"><!-- Réf. -->
                        <col><!-- Désignation (auto = max) -->
                        <col style="width: 80px;"><!-- Famille -->
                        <col style="width: 48px;"><!-- Volume -->
                        <col style="width: 46px;"><!-- Nicotine -->
                        <col style="width: 72px;"><!-- PA HT -->
                        <col style="width: 72px;"><!-- PV TTC -->
                        <col style="width: 68px;"><!-- Marge -->
                        <col style="width: 70px;"><!-- Stock mag -->
                        <col style="width: 70px;"><!-- Stock ctr -->
                        <col style="width: 38px;"><!-- Mvt -->
                        <col style="width: 40px;"><!-- DLUO -->
                        <col style="width: 38px;"><!-- Kit -->
                        <col style="width: 60px;"><!-- Qté V. -->
                        <col style="width: 78px;"><!-- CA HT -->
                    </colgroup>
                    <thead>
                        <?php
                        // Helper to build sort header link
                        if (!function_exists('sort_header')) {
                            function sort_header($col, $label, $current_col, $current_dir, $class = '', $title = '') {
                                $is_active = ($current_col === $col);
                                $next_dir = ($is_active && $current_dir === 'asc') ? 'desc' : 'asc';
                                $arrow = '';
                                if ($is_active) {
                                    $arrow = ($current_dir === 'asc')
                                        ? ' <span class="sort-arrow">&#9650;</span>'
                                        : ' <span class="sort-arrow">&#9660;</span>';
                                }
                                $active_class = $is_active ? ' sort-active' : '';
                                $url = site_url("items/sort/$col/$next_dir");
                                $title_attr = $title ? ' title="'.$title.'"' : '';
                                return '<th class="'.$class.' sortable-server'.$active_class.'"'.$title_attr.'>'
                                     . '<a href="'.$url.'" class="sort-link" title="Trier par '.($title ? $title : $label).'">'
                                     . $label . $arrow . '</a></th>';
                            }
                        }
                        ?>
                        <tr>
                            <th class="col-checkbox"><input type="checkbox" id="select_all"></th>
                            <th class="col-action"></th>
                            <th class="col-icon" title="Code barre">EAN</th>
                            <?php echo sort_header('item_number', 'Réf.', $current_sort_col, $current_sort_dir, '', 'Référence'); ?>
                            <?php echo sort_header('name', 'Désignation', $current_sort_col, $current_sort_dir, 'col-name', 'Désignation'); ?>
                            <?php echo sort_header('category', 'Fam.', $current_sort_col, $current_sort_dir, '', 'Famille'); ?>
                            <?php echo sort_header('volume', 'Vol.', $current_sort_col, $current_sort_dir, 'col-number', 'Volume'); ?>
                            <?php echo sort_header('nicotine', 'Nic.', $current_sort_col, $current_sort_dir, 'col-number', 'Nicotine'); ?>
                            <?php echo sort_header('cost_price', 'PA HT', $current_sort_col, $current_sort_dir, 'col-price', "Prix d'Achat Hors Taxes"); ?>
                            <th class="col-price" title="Prix de Vente Toutes Taxes Comprises (tarif par défaut)">PV TTC</th>
                            <?php echo sort_header('margin', 'Marge', $current_sort_col, $current_sort_dir, 'col-number', 'Marge en pourcentage'); ?>
                            <?php echo sort_header('quantity', 'Stk', $current_sort_col, $current_sort_dir, 'col-number', 'Stock Magasin'); ?>
                            <?php echo sort_header('quantity_central', 'Ctr.', $current_sort_col, $current_sort_dir, 'col-number', 'Stock Central'); ?>
                            <th class="col-action" title="Mouvements de stock">Mvt</th>
                            <th class="col-action" title="Date Limite d&#39;Utilisation Optimale">DLUO</th>
                            <th class="col-action" title="Kit">Kit</th>
                            <?php echo sort_header('sales_qty', 'Qté V.', $current_sort_col, $current_sort_dir, 'col-number', 'Quantité(s) Vendue(s)'); ?>
                            <?php echo sort_header('sales_ht', 'CA HT', $current_sort_col, $current_sort_dir, 'col-price', "Chiffre d'Affaires Hors Taxes"); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items->result() as $item):
                            $_SESSION['line_number'] += 1;
                            $this->Common_routines->set_line_colour();

                            // Create anchor for reports
                            $anchor = '/reports/'
                                . $parms['specific_function'] . '/'
                                . $parms['start_date'] . '/'
                                . $parms['end_date'] . '/'
                                . $item->item_id . '/'
                                . $parms['transaction_subtype'] . '/'
                                . $parms['export_excel'] . '/'
                                . $parms['history'];

                            // Set item_number if NULL
                            if ($item->item_number == NULL) {
                                $item->item_number = $this->lang->line('common_edit');
                            }

                            // Get cost price from default supplier
                            $item_supplier_info = $this->Item->item_supplier_get_cost($item->item_id);
                            $cost_price = ($item_supplier_info == NULL) ? 0 : $item_supplier_info->supplier_cost_price;

                            // Get unit price from default price list
                            $item_price_info = $this->Item->get_info_item_price($item->item_id, $this->config->item('pricelist_id'));
                            $unit_price = 0;
                            if (count($item_price_info) == 1) {
                                foreach ($item_price_info as $item_price) {
                                    $unit_price = $item_price->unit_price_with_tax;
                                }
                            }

                            $tout = (string)$item->item_id . ":" . (string)$_SESSION['line_number'] . ":" . 'items';
                            $is_focused = (($_SESSION['autofocus_avec_item_id_manage'] ?? null) == $item->item_id);
                            $row_class = $is_focused ? 'row-focused' : '';
                            $row_class .= ($item->deleted == "1") ? ' row-inactive' : '';
                        ?>
                        <tr class="<?php echo $row_class; ?>" <?php if($is_focused) echo 'id="line_couleur"'; ?>>
                            <!-- Checkbox -->
                            <td class="cell-checkbox"><input type="checkbox" value="<?php echo $item->item_id; ?>"></td>
                            <!-- Toggle Active/Inactive (icône = action à effectuer) -->
                            <td class="cell-action">
                                <?php if ($item->deleted == "0"): ?>
                                    <!-- Article ACTIF: icône œil barré = cliquer pour désactiver -->
                                    <a href="#" class="btn-icon btn-action-deactivate btn-ajax-toggle" data-item-id="<?php echo $item->item_id; ?>" data-status="0" title="Désactiver">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                            <line x1="1" y1="1" x2="23" y2="23"></line>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <!-- Article INACTIF: icône œil ouvert = cliquer pour activer -->
                                    <a href="#" class="btn-icon btn-action-activate btn-ajax-toggle" data-item-id="<?php echo $item->item_id; ?>" data-status="1" title="Activer">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </td>

                            <!-- Barcode Icon -->
                            <td class="cell-action">
                                <?php if(isset($item->supplier_bar_code) && (strlen($item->supplier_bar_code) > 5)): ?>
                                    <a href="<?php echo site_url("items/codebarre/$tout"); ?>" class="btn-icon btn-barcode-ok" title="Code barre existant">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M3 5v14M6 5v14M9 5v14M12 5v14M15 5v14M18 5v14M21 5v14"/>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo site_url("items/codebarre/$tout"); ?>" class="btn-icon btn-barcode-missing" title="Code barre manquant">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M3 5v14M6 5v14M9 5v14M12 5v14M15 5v14M18 5v14M21 5v14"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </td>

                            <!-- Item Number -->
                            <td class="cell-id">
                                <?php echo anchor($_SESSION['controller_name'].'/view/'.$item->item_id."/IA", '<span class="badge-ref">'.$item->item_number.'</span>', 'title="'.htmlspecialchars($item->item_number).'"'); ?>
                                <?php if($is_focused): ?>
                                    <?php echo form_button(array('autofocus' => 'autofocus', 'type' => 'hidden')); ?>
                                <?php endif; ?>
                            </td>

                            <!-- Name -->
                            <td class="cell-name" title="<?php echo htmlspecialchars($item->name); ?>">
                                <?php echo $item->name; ?>
                            </td>

                            <!-- Category -->
                            <td class="cell-category">
                                <span class="badge-category"><?php echo $item->category; ?></span>
                            </td>

                            <!-- Volume -->
                            <td class="cell-number"><?php echo $item->volume; ?></td>

                            <!-- Nicotine -->
                            <td class="cell-number"><?php echo $item->nicotine; ?></td>

                            <!-- Cost Price -->
                            <td class="cell-price"><?php echo number_format($cost_price, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep']); ?></td>

                            <!-- Unit Price -->
                            <td class="cell-price">
                                <?php
                                $price_class = ($unit_price <= $cost_price) ? 'price-warning' : 'price-normal';
                                echo anchor(
                                    $_SESSION['controller_name'].'/label_form/'.$item->item_id,
                                    '<span class="'.$price_class.'">'.number_format($unit_price, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep']).'</span>'
                                );
                                ?>
                            </td>

                            <!-- Marge % -->
                            <td class="cell-number">
                                <?php
                                if ($cost_price > 0 && $unit_price > 0) {
                                    // Récupérer le taux de TVA par défaut (20% si non configuré)
                                    $default_tax = $this->config->item('default_tax_1_rate');
                                    $tax_rate = ($default_tax !== NULL && $default_tax !== '') ? floatval($default_tax) : 20.00;
                                    // Convertir PV TTC en PV HT
                                    $unit_price_ht = $unit_price / (1 + ($tax_rate / 100));
                                    // Calculer la marge : ((PV HT - PA HT) / PV HT) × 100
                                    $margin_pct = (($unit_price_ht - $cost_price) / $unit_price_ht) * 100;
                                    $margin_class = ($margin_pct < 0) ? 'price-warning' : (($margin_pct < 20) ? 'stock-low' : 'stock-ok');
                                    echo '<span class="'.$margin_class.'">'.number_format($margin_pct, 1, ',', '').'%</span>';
                                } else {
                                    echo '<span class="text-muted">-</span>';
                                }
                                ?>
                            </td>

                            <!-- Stock Receivings -->
                            <td class="cell-stock">
                                <?php
                                $qty = round($item->quantity, 0);
                                $stock_class = ($qty <= 0) ? 'stock-critical' : (($qty <= 5) ? 'stock-low' : 'stock-ok');
                                echo anchor($_SESSION['controller_name'].'/inventory/'.$item->item_id, '<span class="'.$stock_class.'">'.$qty.'</span>');
                                ?>
                            </td>

                            <!-- Stock Central -->
                            <td class="cell-stock">
                                <?php
                                $qty_central = round($item->quantity_central, 0);
                                $stock_class_c = ($qty_central <= 0) ? 'stock-critical' : (($qty_central <= 5) ? 'stock-low' : 'stock-ok');
                                ?>
                                <span class="<?php echo $stock_class_c; ?>"><?php echo $qty_central; ?></span>
                            </td>

                            <!-- Details -->
                            <td class="cell-action">
                                <?php echo anchor($_SESSION['controller_name'].'/count_details/'.$item->item_id, '<svg width="10" height="10" viewBox="0 0 10 10" class="icon-dot-blue"><circle cx="5" cy="5" r="5"/></svg>', 'title="Mouvement de stock"'); ?>
                            </td>

                            <!-- DLUO -->
                            <td class="cell-action">
                                <?php if ($item->dluo_indicator == 'Y'): ?>
                                    <?php echo anchor($_SESSION['controller_name'].'/dluo_form/'.$item->item_id, '<svg class="icon-check-green" width="16" height="16" fill="none" stroke="#10b981" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>', 'title="'.$this->lang->line('items_dluo').'"'); ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Kit -->
                            <td class="cell-action">
                                <?php if ($item->DynamicKit == 'Y'): ?>
                                    <?php echo anchor($_SESSION['controller_name'].'/kit/'.$item->item_id, '<span class="badge-kit">Kit</span>'); ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Total Sold Qty -->
                            <td class="cell-number">
                                <?php echo anchor($anchor, round($item->sales_qty, 0)); ?>
                            </td>

                            <!-- Total Sold Value -->
                            <td class="cell-price">
                                <?php echo anchor($anchor, number_format($item->sales_ht, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-footer">
                <div class="table-info">
                    <?php $current_per_page = $_SESSION['items_per_page'] ?? 20; ?>
                    <select id="per_page_select" class="per-page-select" onchange="window.location.href='<?php echo site_url('items/per_page'); ?>/'+this.value;">
                        <option value="20" <?php if($current_per_page == 20) echo 'selected'; ?>>20</option>
                        <option value="50" <?php if($current_per_page == 50) echo 'selected'; ?>>50</option>
                        <option value="100" <?php if($current_per_page == 100) echo 'selected'; ?>>100</option>
                        <option value="500" <?php if($current_per_page == 500) echo 'selected'; ?>>500</option>
                        <option value="0" <?php if($current_per_page == 0) echo 'selected'; ?>>Complet</option>
                    </select>
                    <span class="item-count"><?php echo $_SESSION['line_number']; ?> <?php echo $this->lang->line('items_items'); ?> affichés</span>
                </div>
                <?php if(($_SESSION['filtre'] ?? 0) != 1 && isset($links)): ?>
                    <div class="pagination-wrapper">
                        <?php echo $links; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<!-- Hidden form for bulk edit with selected items -->
<form id="bulk_edit_form" method="post" action="<?php echo site_url('items/bulk_action_1'); ?>" style="display:none;">
    <input type="hidden" name="selected_ids" id="selected_ids" value="">
    <input type="hidden" name="bulk_action_id" id="bulk_action_id_hidden" value="">
</form>

<script type="text/javascript">
function submitBulkEdit(actionId) {
    var ids = get_selected_values();
    if (ids.length === 0) {
        alert('Veuillez sélectionner au moins un article.');
        return;
    }
    document.getElementById('selected_ids').value = ids.join(',');
    if (actionId) {
        document.getElementById('bulk_action_id_hidden').value = actionId;
    }
    document.getElementById('bulk_edit_form').submit();
}

// Fix: direct checkbox click must not be double-toggled by row_click
$(document).ready(function() {
    $('#sortable_table :checkbox').unbind('click').click(function(e) {
        e.stopPropagation();
    });
    // Fix select_all
    $('#select_all').unbind('click').click(function() {
        var checked = this.checked;
        $("#sortable_table tbody :checkbox").each(function() {
            this.checked = checked;
        });
    });
    // Close dropdown when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).parents('.bulk-dropdown-wrapper').length && !$(e.target).hasClass('bulk-dropdown-wrapper')) {
            $('#bulk_dropdown_menu').removeClass('show');
        }
    });
});

function toggleBulkDropdown(e) {
    e.preventDefault();
    e.stopPropagation();
    var ids = get_selected_values();
    if (ids.length === 0) {
        // Aucun produit coché : comportement d'origine (modale de sélection)
        window.location.href = '<?php echo site_url("items/bulk_action_1"); ?>';
        return;
    }
    $('#bulk_dropdown_menu').toggleClass('show');
}
</script>

<!-- Print Script -->
<script type="text/javascript">
var Printer = {
    print: function(HTML) {
        var win = window.open('', '_blank');
        win.document.write(`
            <html>
            <head>
                <title>Articles - Impression</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 11px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
                    th { background: #f5f5f5; font-weight: bold; }
                    tr:nth-child(even) { background: #fafafa; }
                    h2 { margin-bottom: 10px; }
                    @media print { body { -webkit-print-color-adjust: exact; } }
                </style>
            </head>
            <body>
                <h2>Liste des Articles</h2>
                <table>${HTML}</table>
            </body>
            </html>
        `);
        win.document.close();
        win.print();
        win.close();
    }
};
</script>

<!-- Spinner -->
<div id="spinneritem" class="spinner-overlay" style="display:none;">
    <div class="spinner">
        <div class="spinner-ring"></div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('.sablier').click(function() {
        $('#spinneritem').show();
    });
});
</script>

<!-- Live Search Filter (Optimized) -->
<script type="text/javascript">
(function() {
    var searchInput = document.getElementById('search');
    var clearBtn = document.getElementById('clear_search');
    var table = document.getElementById('sortable_table');
    var debounceTimer = null;
    var minChars = 1;

    // Pre-built cache for fast filtering
    var rowCache = [];
    var originalRowCount = 0;

    // Exit early only if search input doesn't exist
    if (!searchInput) return;

    // If no table, we can still handle form submission
    var hasTable = !!table;

    // Decode HTML entities (pour rechercher "A&L" correctement)
    function decodeHtmlEntities(str) {
        var temp = document.createElement('div');
        temp.innerHTML = str;
        return temp.textContent || temp.innerText || '';
    }

    // Build cache once at page load - extract all searchable data
    function buildCache() {
        if (!hasTable) return;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var rows = tbody.getElementsByTagName('tr');
        var len = rows.length;
        rowCache = new Array(len);

        for (var i = 0; i < len; i++) {
            var row = rows[i];
            var cells = row.cells; // Faster than querySelectorAll
            var text = '';

            // Columns: 0=checkbox, 1=toggle, 2=barcode, 3=item_number, 4=name, 5=category
            if (cells.length > 5) {
                text = (cells[3].textContent || '') + ' ' +
                       (cells[4].textContent || '') + ' ' +
                       (cells[5].textContent || '');
            }

            // Décoder les entités HTML pour que & fonctionne correctement
            text = decodeHtmlEntities(text);

            rowCache[i] = {
                row: row,
                text: text.toLowerCase()
            };
        }
        originalRowCount = len;
    }

    // Build cache immediately
    if (hasTable) buildCache();

    // Optimized filter using cached data (only works if table exists)
    function filterTable(searchText) {
        if (!hasTable) return;

        // Décoder les entités HTML dans la recherche aussi
        var searchLower = decodeHtmlEntities(searchText).toLowerCase().trim();
        var isFiltering = searchLower.length >= minChars;
        var visibleCount = 0;
        var len = rowCache.length;

        // Update clear button
        if (clearBtn) {
            clearBtn.style.display = searchLower.length > 0 ? 'flex' : 'none';
        }

        // Visual feedback
        searchInput.classList.toggle('filter-active', isFiltering);

        // If empty, show all
        if (!isFiltering) {
            for (var i = 0; i < len; i++) {
                rowCache[i].row.style.display = '';
            }
            updateCount(len, false);
            return;
        }

        // Split terms once
        var terms = searchLower.split(/\s+/);
        var numTerms = terms.length;

        // Fast loop with cached data
        for (var i = 0; i < len; i++) {
            var cached = rowCache[i];
            var text = cached.text;
            var match = true;

            // Check all terms
            for (var t = 0; t < numTerms; t++) {
                if (text.indexOf(terms[t]) === -1) {
                    match = false;
                    break;
                }
            }

            if (match) {
                cached.row.style.display = '';
                visibleCount++;
            } else {
                cached.row.style.display = 'none';
            }
        }

        updateCount(visibleCount, true);
    }

    // Update count display
    function updateCount(count, isFiltered) {
        var countSpan = document.querySelector('.item-count');
        if (countSpan) {
            if (isFiltered) {
                countSpan.textContent = count + '/' + originalRowCount + ' (filtré)';
                countSpan.style.color = 'var(--primary)';
                countSpan.style.fontWeight = 'bold';
            } else {
                countSpan.textContent = count + ' <?php echo $this->lang->line("items_items"); ?> affichés';
                countSpan.style.color = '';
                countSpan.style.fontWeight = '';
            }
        }
    }

    // Clear local filter only
    function clearLocalFilter() {
        searchInput.value = '';
        filterTable('');
        searchInput.focus();
    }

    // Clear and reload (remove server filter)
    function clearAndReload() {
        // Redirect to items/index to clear the server-side search
        window.location.href = '<?php echo site_url("items/index"); ?>';
    }

    // Clear button - if there was a server search, reload; otherwise just clear local
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            <?php if (isset($_SESSION['filtre_recherche']) && $_SESSION['filtre_recherche'] != ''): ?>
            clearAndReload();
            <?php else: ?>
            clearLocalFilter();
            <?php endif; ?>
        });
    }

    // Show clear button if there's existing search value
    if (searchInput.value.length > 0 && clearBtn) {
        clearBtn.style.display = 'flex';
    }

    // Timers for debouncing
    var serverSearchTimer = null;
    var minCharsForServerSearch = 3; // Minimum characters to trigger server search
    var serverSearchDelay = 800; // ms to wait after typing stops

    // Submit server search
    function submitServerSearch(val) {
        var form = document.getElementById('search_form');
        if (val.trim().length >= minCharsForServerSearch && form) {
            form.submit();
        }
    }

    // Debounced input handler
    searchInput.addEventListener('input', function(e) {
        var val = e.target.value;

        // Update clear button immediately
        if (clearBtn) {
            clearBtn.style.display = val.length > 0 ? 'flex' : 'none';
        }

        // Clear both timers
        clearTimeout(debounceTimer);
        clearTimeout(serverSearchTimer);

        // Local filtering (fast, 150ms)
        debounceTimer = setTimeout(function() {
            filterTable(val);
        }, 150);

        // Server search after user stops typing (800ms)
        if (val.trim().length >= minCharsForServerSearch) {
            serverSearchTimer = setTimeout(function() {
                submitServerSearch(val);
            }, serverSearchDelay);
        }
    });

    // Keyboard: Enter = immediate server search, Escape = clear
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(serverSearchTimer); // Cancel pending auto-search
            var form = document.getElementById('search_form');
            if (searchInput.value.trim().length > 0 && form) {
                form.submit();
            }
        } else if (e.key === 'Escape') {
            clearTimeout(debounceTimer);
            clearTimeout(serverSearchTimer);
            clearLocalFilter();
        }
    });
})();
</script>

<!-- AJAX Toggle Active/Inactive -->
<script type="text/javascript">
(function() {
    var isNouveautesMode = <?php echo (($_SESSION['filtre_nouveautes'] ?? 0) == 1) ? 'true' : 'false'; ?>;
    var isInactifsMode = <?php echo (($_SESSION['undel'] ?? 0) == 1) ? 'true' : 'false'; ?>;

    // jQuery 1.2.6 compatible: use click() instead of on()
    $('.btn-ajax-toggle').click(function(e) {
        e.preventDefault();
        e.stopPropagation(); // Empêcher la propagation vers la ligne (checkbox)
        var btn = $(this);
        var itemId = btn.attr('data-item-id');
        var currentStatus = btn.attr('data-status');
        var row = btn.parents('tr:first');

        // Disable button during request
        btn.css('pointer-events', 'none').css('opacity', '0.5');

        $.ajax({
            url: '<?php echo site_url("items/ajax_toggle_status"); ?>/' + itemId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // En mode Nouveautés ou liste normale avec désactivation: supprimer la ligne
                    if ((isNouveautesMode && response.new_status == 1) ||
                        (!isInactifsMode && response.new_status == 1)) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            updateItemCount();
                        });
                    }
                    // En mode Inactifs avec activation: supprimer la ligne
                    else if (isInactifsMode && response.new_status == 0) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            updateItemCount();
                        });
                    }
                    // Sinon: basculer l'apparence
                    else {
                        toggleButtonAppearance(btn, response.new_status);
                        btn.attr('data-status', response.new_status);
                        if (response.new_status == 1) {
                            row.addClass('row-inactive');
                        } else {
                            row.removeClass('row-inactive');
                        }
                        btn.css('pointer-events', '').css('opacity', '');
                    }
                } else {
                    alert(response.message);
                    btn.css('pointer-events', '').css('opacity', '');
                }
            },
            error: function() {
                alert('Erreur de connexion');
                btn.css('pointer-events', '').css('opacity', '');
            }
        });
    });

    function toggleButtonAppearance(btn, newStatus) {
        if (newStatus == 1) {
            // Article maintenant INACTIF: afficher œil ouvert vert = cliquer pour activer
            btn.removeClass('btn-action-deactivate').addClass('btn-action-activate');
            btn.attr('title', 'Activer');
            btn.html('<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>');
        } else {
            // Article maintenant ACTIF: afficher œil barré = cliquer pour désactiver
            btn.removeClass('btn-action-activate').addClass('btn-action-deactivate');
            btn.attr('title', 'Désactiver');
            btn.html('<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>');
        }
    }

    function updateItemCount() {
        var visibleRows = $('#sortable_table tbody tr:visible').length;
        var countBadge = $('#item-count-badge');
        if (countBadge.length) {
            countBadge.text(visibleRows + ' articles');
        }
        var countSpan = $('.item-count');
        if (countSpan.length) {
            countSpan.text(visibleRows + ' articles affichés');
        }
    }
})();
</script>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
// Show dialogs based on show_dialog
// Cases using unified modal_wrapper with AJAX tab switching
switch ($_SESSION['show_dialog'] ?? 0) {
    case 1:  // Article tab
    case 3:  // Stock (inventory)
    case 4:  // Stock (count details)
    case 6:  // DLUO
    case 9:  // Suppliers tab
    case 11: // Pricelists tab
    case 15: // Kit structure
    case 16: // Kit detail
    case 17: // Stock (inventory alternate)
    case 18: // Article tab (alternate)
    case 19: // Sales tab
        $this->load->view('items/modal_wrapper');
        break;
    case 2:
        include('../wrightetmathon/application/views/items/clone_form.php');
        break;
    case 5:
        include('../wrightetmathon/application/views/items/merge_form.php');
        break;
    case 7:
        include('../wrightetmathon/application/views/items/label_form.php');
        break;
    case 8:
        include('../wrightetmathon/application/views/items/form_remote_stock.php');
        break;
    case 10:
        include('../wrightetmathon/application/views/items/form_item_warehouse.php');
        break;
    case 12:
        include('../wrightetmathon/application/views/items/form_bulk_actions.php');
        break;
    case 13:
        include('../wrightetmathon/application/views/items/form_bulk_actions_select.php');
        break;
    case 14:
        include('../wrightetmathon/application/views/items/form_bulk_actions_confirm.php');
        break;
}
?>
