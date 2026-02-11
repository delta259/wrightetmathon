<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() {
    enable_row_selection();
    enable_select_all();

    var searchField = document.getElementById("search");
    if (searchField) {
        searchField.focus();
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

// Reset line number
$_SESSION['line_number'] = 0;

// Current sort state for column headers
$current_sort_col = $_SESSION['customers_sort_col'] ?? 'last_name';
$current_sort_dir = $_SESSION['customers_sort_dir'] ?? 'asc';
?>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <?php echo $this->lang->line('modules_' . $_SESSION['controller_name']); ?>
            </h1>
            <div class="page-actions">
                <?php include('../wrightetmathon/application/views/partial/show_buttons.php'); ?>

                <button type="button" class="btn-action" onclick="Printer.print(document.getElementById('sortable_table').innerHTML);" title="Imprimer">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Imprimer
                </button>

                <span class="badge badge-info">
                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                    </svg>
                    <span id="item-count-badge"><?php echo $this->Customer->count_all(); ?> clients</span>
                </span>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="filters-bar">
            <!-- Search -->
            <div class="filter-group">
                <?php
                $has_server_search = isset($_SESSION['filtre_recherche']) && $_SESSION['filtre_recherche'] != '';
                echo form_open('customers/search', array('id' => 'search_form'));
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

            <!-- Filter Buttons -->
            <div class="filter-group">
                <div class="filter-buttons">
                    <!-- Toggle Inactifs -->
                    <a href="<?php echo site_url("customers/toggle_deleted"); ?>" class="toggle-switch-wrapper" title="<?php echo (($_SESSION['undel'] ?? 0) == 1) ? 'Afficher les clients actifs' : 'Afficher les clients inactifs'; ?>">
                        <span class="toggle-switch <?php echo (($_SESSION['undel'] ?? 0) == 1) ? 'active' : ''; ?>">
                            <span class="toggle-knob"></span>
                        </span>
                        <span class="toggle-label">Inactifs</span>
                    </a>

                    <!-- Toggle Verrou -->
                    <a href="<?php echo site_url("customers/filtre"); ?>" class="toggle-switch-wrapper" title="<?php echo (($_SESSION['filtre'] ?? 0) == 1) ? 'Désactiver le verrou' : 'Activer le verrou'; ?>">
                        <span class="toggle-switch <?php echo (($_SESSION['filtre'] ?? 0) == 1) ? 'active' : ''; ?>">
                            <span class="toggle-knob"></span>
                        </span>
                        <span class="toggle-label">Verrou</span>
                    </a>
                </div>
            </div>

            <!-- External Search Button -->
            <div class="filter-group">
                <a href="<?php echo site_url("customers/search_ext_customers"); ?>" class="btn-action" title="Recherche client dans les autres boutiques">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Recherche client
                </a>
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
                        <col style="width: 88px;"><!-- N° Client -->
                        <col><!-- Nom (auto) -->
                        <col><!-- Prénom (auto) -->
                        <col style="width: 180px;"><!-- Email -->
                        <col style="width: 120px;"><!-- Téléphone -->
                        <col style="width: 110px;"><!-- Ville -->
                        <col style="width: 80px;"><!-- CA HT -->
                        <col style="width: 70px;"><!-- Points -->
                    </colgroup>
                    <thead>
                        <?php
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
                                $url = site_url("customers/sort/$col/$next_dir");
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
                            <?php echo sort_header('account_number', 'N° Client', $current_sort_col, $current_sort_dir, '', 'Numéro de compte client'); ?>
                            <?php echo sort_header('last_name', 'Nom', $current_sort_col, $current_sort_dir, 'col-name', 'Nom de famille'); ?>
                            <?php echo sort_header('first_name', 'Prénom', $current_sort_col, $current_sort_dir, 'col-name', 'Prénom'); ?>
                            <?php echo sort_header('email', 'Email', $current_sort_col, $current_sort_dir, '', 'Adresse email'); ?>
                            <?php echo sort_header('phone_number', 'Téléphone', $current_sort_col, $current_sort_dir, '', 'Numéro de téléphone'); ?>
                            <?php echo sort_header('city', 'Ville', $current_sort_col, $current_sort_dir, '', 'Ville'); ?>
                            <?php echo sort_header('sales_ht', 'CA HT', $current_sort_col, $current_sort_dir, 'col-price', "Chiffre d'Affaires Hors Taxes"); ?>
                            <?php echo sort_header('fidelity_points', 'Points', $current_sort_col, $current_sort_dir, 'col-number', 'Points de fidélité'); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers->result() as $customer):
                            $_SESSION['line_number'] += 1;
                            $this->Common_routines->set_line_colour();

                            $row_class = ($customer->deleted == "1") ? 'row-inactive' : '';
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <!-- Checkbox -->
                            <td class="cell-checkbox"><input type="checkbox" value="<?php echo $customer->person_id; ?>"></td>

                            <!-- Toggle Active/Inactive -->
                            <td class="cell-action">
                                <?php if ($customer->deleted == "0"): ?>
                                    <a href="#" class="btn-icon btn-action-deactivate btn-ajax-toggle" data-customer-id="<?php echo $customer->person_id; ?>" data-status="0" title="Désactiver">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                            <line x1="1" y1="1" x2="23" y2="23"></line>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <a href="#" class="btn-icon btn-action-activate btn-ajax-toggle" data-customer-id="<?php echo $customer->person_id; ?>" data-status="1" title="Activer">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </td>

                            <!-- Account Number -->
                            <td class="cell-id">
                                <?php echo anchor('customers/view/'.$customer->person_id, '<span class="badge-ref">'.$customer->account_number.'</span>', 'title="'.htmlspecialchars($customer->account_number).'"'); ?>
                            </td>

                            <!-- Last Name -->
                            <td class="cell-name" title="<?php echo htmlspecialchars($customer->last_name); ?>">
                                <?php echo strtoupper($customer->last_name); ?>
                            </td>

                            <!-- First Name -->
                            <td class="cell-name" title="<?php echo htmlspecialchars($customer->first_name); ?>">
                                <?php echo ucfirst(strtolower($customer->first_name)); ?>
                            </td>

                            <!-- Email -->
                            <td class="cell-text"><?php echo htmlspecialchars($customer->email ?? ''); ?></td>

                            <!-- Phone Number -->
                            <td class="cell-text"><?php echo htmlspecialchars($customer->phone_number ?? ''); ?></td>

                            <!-- City -->
                            <td class="cell-text"><?php echo htmlspecialchars($customer->city ?? ''); ?></td>

                            <!-- CA HT (clickable) -->
                            <td class="cell-price cell-ca-ht" data-person-id="<?php echo $customer->person_id; ?>" data-customer-name="<?php echo htmlspecialchars($customer->first_name . ' ' . $customer->last_name); ?>" title="Voir les ventes"><?php echo number_format($customer->sales_ht ?? 0, $parms['decimals'], $parms['dec_point'], $parms['thousands_sep']); ?></td>

                            <!-- Fidelity Points -->
                            <td class="cell-number"><?php echo (int)($customer->fidelity_points ?? 0); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-footer">
                <div class="table-info">
                    <?php $current_per_page = $_SESSION['customers_per_page'] ?? 20; ?>
                    <select id="per_page_select" class="per-page-select" onchange="window.location.href='<?php echo site_url('customers/per_page'); ?>/'+this.value;">
                        <option value="20" <?php if($current_per_page == 20) echo 'selected'; ?>>20</option>
                        <option value="50" <?php if($current_per_page == 50) echo 'selected'; ?>>50</option>
                        <option value="100" <?php if($current_per_page == 100) echo 'selected'; ?>>100</option>
                        <option value="500" <?php if($current_per_page == 500) echo 'selected'; ?>>500</option>
                        <option value="0" <?php if($current_per_page == 0) echo 'selected'; ?>>Complet</option>
                    </select>
                    <span class="item-count"><?php echo $_SESSION['line_number']; ?> clients affichés</span>
                </div>
                <?php if(($_SESSION['filtre'] ?? 0) != 1 && isset($links) && $links): ?>
                    <div class="pagination-wrapper">
                        <?php echo $links; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<!-- Print Script -->
<script type="text/javascript">
var Printer = {
    print: function(HTML) {
        var win = window.open('', '_blank');
        win.document.write(
            '<html><head><title>Clients - Impression</title>' +
            '<style>' +
            'body { font-family: Arial, sans-serif; font-size: 11px; }' +
            'table { width: 100%; border-collapse: collapse; }' +
            'th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }' +
            'th { background: #f5f5f5; font-weight: bold; }' +
            'tr:nth-child(even) { background: #fafafa; }' +
            'h2 { margin-bottom: 10px; }' +
            '@media print { body { -webkit-print-color-adjust: exact; } }' +
            '</style></head><body>' +
            '<h2>Liste des Clients</h2>' +
            '<table>' + HTML + '</table>' +
            '</body></html>'
        );
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

<!-- Live Search Filter -->
<script type="text/javascript">
(function() {
    var searchInput = document.getElementById('search');
    var clearBtn = document.getElementById('clear_search');
    var table = document.getElementById('sortable_table');
    var debounceTimer = null;
    var minChars = 1;

    var rowCache = [];
    var originalRowCount = 0;

    if (!searchInput) return;

    var hasTable = !!table;

    function decodeHtmlEntities(str) {
        var temp = document.createElement('div');
        temp.innerHTML = str;
        return temp.textContent || temp.innerText || '';
    }

    function buildCache() {
        if (!hasTable) return;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var rows = tbody.getElementsByTagName('tr');
        var len = rows.length;
        rowCache = new Array(len);

        for (var i = 0; i < len; i++) {
            var row = rows[i];
            var cells = row.cells;
            var text = '';

            // Columns: 0=checkbox, 1=toggle, 2=account, 3=last_name, 4=first_name, 5=email, 6=phone, 7=city
            if (cells.length > 7) {
                text = (cells[2].textContent || '') + ' ' +
                       (cells[3].textContent || '') + ' ' +
                       (cells[4].textContent || '') + ' ' +
                       (cells[5].textContent || '') + ' ' +
                       (cells[6].textContent || '') + ' ' +
                       (cells[7].textContent || '');
            }

            text = decodeHtmlEntities(text);

            rowCache[i] = {
                row: row,
                text: text.toLowerCase()
            };
        }
        originalRowCount = len;
    }

    if (hasTable) buildCache();

    function filterTable(searchText) {
        if (!hasTable) return;

        var searchLower = decodeHtmlEntities(searchText).toLowerCase().trim();
        var isFiltering = searchLower.length >= minChars;
        var visibleCount = 0;
        var len = rowCache.length;

        if (clearBtn) {
            clearBtn.style.display = searchLower.length > 0 ? 'flex' : 'none';
        }

        searchInput.classList.toggle('filter-active', isFiltering);

        if (!isFiltering) {
            for (var i = 0; i < len; i++) {
                rowCache[i].row.style.display = '';
            }
            updateCount(len, false);
            return;
        }

        var terms = searchLower.split(/\s+/);
        var numTerms = terms.length;

        for (var i = 0; i < len; i++) {
            var cached = rowCache[i];
            var text = cached.text;
            var match = true;

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

    function updateCount(count, isFiltered) {
        var countSpan = document.querySelector('.item-count');
        if (countSpan) {
            if (isFiltered) {
                countSpan.textContent = count + '/' + originalRowCount + ' (filtré)';
                countSpan.style.color = 'var(--primary)';
                countSpan.style.fontWeight = 'bold';
            } else {
                countSpan.textContent = count + ' clients affichés';
                countSpan.style.color = '';
                countSpan.style.fontWeight = '';
            }
        }
    }

    function clearLocalFilter() {
        searchInput.value = '';
        filterTable('');
        searchInput.focus();
    }

    function clearAndReload() {
        window.location.href = '<?php echo site_url("customers/index"); ?>';
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            <?php if (isset($_SESSION['filtre_recherche']) && $_SESSION['filtre_recherche'] != ''): ?>
            clearAndReload();
            <?php else: ?>
            clearLocalFilter();
            <?php endif; ?>
        });
    }

    if (searchInput.value.length > 0 && clearBtn) {
        clearBtn.style.display = 'flex';
    }

    var serverSearchTimer = null;
    var minCharsForServerSearch = 3;
    var serverSearchDelay = 800;

    function submitServerSearch(val) {
        var form = document.getElementById('search_form');
        if (val.trim().length >= minCharsForServerSearch && form) {
            form.submit();
        }
    }

    searchInput.addEventListener('input', function(e) {
        var val = e.target.value;

        if (clearBtn) {
            clearBtn.style.display = val.length > 0 ? 'flex' : 'none';
        }

        clearTimeout(debounceTimer);
        clearTimeout(serverSearchTimer);

        debounceTimer = setTimeout(function() {
            filterTable(val);
        }, 150);

        if (val.trim().length >= minCharsForServerSearch) {
            serverSearchTimer = setTimeout(function() {
                submitServerSearch(val);
            }, serverSearchDelay);
        }
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(serverSearchTimer);
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
    var isInactifsMode = <?php echo (($_SESSION['undel'] ?? 0) == 1) ? 'true' : 'false'; ?>;

    $('.btn-ajax-toggle').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        var btn = $(this);
        var customerId = btn.attr('data-customer-id');
        var currentStatus = btn.attr('data-status');
        var row = btn.parents('tr:first');

        btn.css('pointer-events', 'none').css('opacity', '0.5');

        $.ajax({
            url: '<?php echo site_url("customers/ajax_toggle_status"); ?>/' + customerId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if ((!isInactifsMode && response.new_status == 1) ||
                        (isInactifsMode && response.new_status == 0)) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            updateCustomerCount();
                        });
                    } else {
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
            btn.removeClass('btn-action-deactivate').addClass('btn-action-activate');
            btn.attr('title', 'Activer');
            btn.html('<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>');
        } else {
            btn.removeClass('btn-action-activate').addClass('btn-action-deactivate');
            btn.attr('title', 'Désactiver');
            btn.html('<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>');
        }
    }

    function updateCustomerCount() {
        var visibleRows = $('#sortable_table tbody tr:visible').length;
        var countBadge = $('#item-count-badge');
        if (countBadge.length) {
            countBadge.text(visibleRows + ' clients');
        }
        var countSpan = $('.item-count');
        if (countSpan.length) {
            countSpan.text(visibleRows + ' clients affichés');
        }
    }
})();
</script>

<!-- Sales Detail Slide-in Panel -->
<div id="sales-panel-overlay" class="sales-panel-overlay" style="display:none;">
    <div id="sales-panel" class="sales-panel">
        <div class="sales-panel-header">
            <div class="sales-panel-title">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span id="sales-panel-customer-name"></span>
            </div>
            <button type="button" id="sales-panel-close" class="sales-panel-close" title="Fermer">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div id="sales-panel-summary" class="sales-panel-summary"></div>
        <div id="sales-panel-body" class="sales-panel-body">
            <div class="sales-panel-loading">
                <div class="spinner-ring"></div>
                Chargement...
            </div>
        </div>
    </div>
</div>

<style>
/* Sales Panel Slide-in */
.sales-panel-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.35);
    z-index: 9000;
    opacity: 0;
    transition: opacity 0.25s ease;
}
.sales-panel-overlay.visible { opacity: 1; }

.sales-panel {
    position: fixed;
    top: 0; right: -520px; bottom: 0;
    width: 520px;
    max-width: 90vw;
    background: var(--bg-container, #fff);
    box-shadow: -4px 0 20px rgba(0,0,0,0.15);
    z-index: 9001;
    display: flex;
    flex-direction: column;
    transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.sales-panel.open { right: 0; }

.sales-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc);
    flex-shrink: 0;
}
.sales-panel-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.05em;
    font-weight: 600;
    color: var(--text-primary, #1e293b);
}
.sales-panel-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    border-radius: 6px;
    color: var(--text-secondary, #64748b);
    transition: background 0.15s, color 0.15s;
}
.sales-panel-close:hover {
    background: var(--bg-hover, #f1f5f9);
    color: var(--danger, #ef4444);
}

.sales-panel-summary {
    display: flex;
    gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc);
    flex-shrink: 0;
    flex-wrap: wrap;
}
.sales-summary-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 14px;
    background: var(--bg-container, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
    flex: 1;
    min-width: 80px;
}
.sales-summary-chip .chip-label {
    font-size: 0.7em;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary, #64748b);
    margin-bottom: 2px;
}
.sales-summary-chip .chip-value {
    font-size: 1.05em;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
}

.sales-panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}
.sales-panel-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 60px 20px;
    color: var(--text-secondary, #64748b);
    font-size: 0.9em;
}

/* Sale card in the panel */
.sale-card {
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    padding: 0;
}
.sale-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 20px;
    cursor: pointer;
    transition: background 0.15s;
    gap: 8px;
}
.sale-card-header:hover {
    background: var(--bg-hover, #f1f5f9);
}
.sale-card-date {
    font-weight: 600;
    font-size: 0.88em;
    color: var(--text-primary, #1e293b);
    min-width: 80px;
}
.sale-card-id {
    font-size: 0.78em;
    color: var(--text-secondary, #64748b);
}
.sale-card-employee {
    font-size: 0.82em;
    color: var(--text-secondary, #64748b);
    flex: 1;
    text-align: center;
}
.sale-card-payment {
    display: inline-flex;
    gap: 4px;
    flex-wrap: wrap;
    align-items: center;
}
.sale-card-pay-badge {
    font-size: 0.78em;
    color: var(--text-secondary, #64748b);
    background: var(--bg-card, #f8fafc);
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px solid var(--border-color, #e2e8f0);
    white-space: nowrap;
}
.sale-card-total {
    font-weight: 700;
    font-size: 0.95em;
    color: var(--primary, #2563eb);
    min-width: 70px;
    text-align: right;
}
.sale-card-total.negative {
    color: var(--danger, #ef4444);
}
.sale-card-arrow {
    color: var(--text-secondary, #64748b);
    transition: transform 0.2s;
    flex-shrink: 0;
}
.sale-card-arrow.expanded {
    transform: rotate(90deg);
}

/* Sale items detail */
.sale-card-items {
    display: none;
    padding: 0 20px 12px 20px;
}
.sale-card-items.visible { display: block; }

.sale-items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82em;
}
.sale-items-table th {
    text-align: left;
    padding: 4px 8px;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    font-size: 0.9em;
}
.sale-items-table td {
    padding: 4px 8px;
    color: var(--text-primary, #1e293b);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.sale-items-table tr:last-child td { border-bottom: none; }
.sale-items-table .col-qty { text-align: center; width: 40px; }
.sale-items-table .col-price { text-align: right; width: 70px; }
.sale-items-table .col-ref { width: 80px; font-size: 0.85em; color: var(--text-secondary, #64748b); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 80px; }
.sale-items-table .col-discount { text-align: center; width: 50px; }
.sale-items-table .item-discount { color: var(--danger, #ef4444); font-size: 0.9em; }

/* Type badges (Facture / Avoir) */
.sale-card-type {
    font-size: 0.68em;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 2px 7px;
    border-radius: 4px;
    white-space: nowrap;
    flex-shrink: 0;
}
.sale-card-type-sale {
    color: var(--primary, #2563eb);
    background: rgba(37, 99, 235, 0.08);
    border: 1px solid rgba(37, 99, 235, 0.2);
}
.sale-card-type-return {
    color: var(--danger, #ef4444);
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

/* Clickable CA HT cells */
.cell-ca-ht {
    cursor: pointer;
    position: relative;
    transition: color 0.15s, background 0.15s;
}
.cell-ca-ht:hover {
    color: var(--primary, #2563eb) !important;
    background: rgba(37, 99, 235, 0.06);
}
.cell-ca-ht::after {
    content: '';
    position: absolute;
    bottom: 2px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: var(--primary, #2563eb);
    transition: width 0.2s;
    border-radius: 1px;
}
.cell-ca-ht:hover::after { width: 60%; }

/* No sales message */
.sales-panel-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary, #64748b);
}
.sales-panel-empty svg {
    margin-bottom: 12px;
    opacity: 0.4;
}
</style>

<!-- Sales Panel Script -->
<script type="text/javascript">
(function() {
    var overlay = document.getElementById('sales-panel-overlay');
    var panel = document.getElementById('sales-panel');
    var closeBtn = document.getElementById('sales-panel-close');
    var nameEl = document.getElementById('sales-panel-customer-name');
    var summaryEl = document.getElementById('sales-panel-summary');
    var bodyEl = document.getElementById('sales-panel-body');

    function openPanel(personId, customerName) {
        nameEl.textContent = customerName || 'Client';
        summaryEl.innerHTML = '';
        bodyEl.innerHTML = '<div class="sales-panel-loading"><div class="spinner-ring"></div>Chargement...</div>';

        overlay.style.display = 'block';
        // Force reflow before adding classes
        void overlay.offsetWidth;
        overlay.classList.add('visible');
        panel.classList.add('open');

        // Block body scroll
        document.body.style.overflow = 'hidden';

        // Fetch sales data
        $.ajax({
            url: '<?php echo site_url("customers/ajax_customer_sales"); ?>/' + personId,
            type: 'GET',
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    renderSummary(resp);
                    renderSales(resp.sales);
                } else {
                    bodyEl.innerHTML = '<div class="sales-panel-empty">' + (resp.message || 'Erreur') + '</div>';
                }
            },
            error: function() {
                bodyEl.innerHTML = '<div class="sales-panel-empty">Erreur de connexion</div>';
            }
        });
    }

    function closePanel() {
        panel.classList.remove('open');
        overlay.classList.remove('visible');
        document.body.style.overflow = '';
        setTimeout(function() { overlay.style.display = 'none'; }, 300);
    }

    function renderSummary(resp) {
        summaryEl.innerHTML =
            '<div class="sales-summary-chip"><span class="chip-label">Factures</span><span class="chip-value">' + resp.invoice_count + '</span></div>' +
            '<div class="sales-summary-chip"><span class="chip-label">CA HT</span><span class="chip-value">' + resp.total_ht + '</span></div>' +
            '<div class="sales-summary-chip"><span class="chip-label">TVA</span><span class="chip-value">' + resp.total_tax + '</span></div>' +
            '<div class="sales-summary-chip"><span class="chip-label">Total TTC</span><span class="chip-value">' + resp.total_ttc + '</span></div>';
    }

    function renderSales(sales) {
        if (!sales || sales.length === 0) {
            bodyEl.innerHTML = '<div class="sales-panel-empty">' +
                '<svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>' +
                '<div>Aucune vente trouvée</div></div>';
            return;
        }

        var html = '';
        var typeMap = { 'sales': 'Facture', 'returns': 'Avoir' };
        for (var i = 0; i < sales.length; i++) {
            var s = sales[i];
            var totalFloat = parseFloat(String(s.total_ttc).replace(/\s/g, '').replace(',', '.'));
            var negClass = (totalFloat < 0) ? ' negative' : '';
            var typeLabel = typeMap[s.mode] || s.mode || '';
            var typeClass = (s.mode === 'returns') ? 'sale-card-type-return' : 'sale-card-type-sale';

            // Parse payment_type: "Espèces: 20.00€<br />CB: 5.00€<br />"
            var payHtml = '';
            if (s.payment_type) {
                var payParts = s.payment_type.split('<br />').filter(function(p) { return p.trim() !== ''; });
                var payGrouped = {};
                for (var p = 0; p < payParts.length; p++) {
                    var clean = payParts[p].replace(/<[^>]*>/g, '').trim();
                    var match = clean.match(/^(.+?):\s*([\-\d,\.]+)\s*€?$/);
                    if (match) {
                        var pmName = match[1].trim();
                        var pmAmount = parseFloat(match[2].replace(',', '.'));
                        if (!payGrouped[pmName]) payGrouped[pmName] = 0;
                        payGrouped[pmName] += pmAmount;
                    }
                }
                for (var pm in payGrouped) {
                    payHtml += '<span class="sale-card-pay-badge" title="' + escHtml(pm) + '">' + escHtml(pm) + ': ' + payGrouped[pm].toFixed(2).replace('.', ',') + '€</span>';
                }
            }

            html += '<div class="sale-card">' +
                '<div class="sale-card-header" data-idx="' + i + '">' +
                    '<span class="sale-card-type ' + typeClass + '">' + typeLabel + '</span>' +
                    '<span class="sale-card-date">' + escHtml(s.date) + '</span>' +
                    '<span class="sale-card-id">#' + s.sale_id + '</span>' +
                    '<span class="sale-card-employee">' + escHtml(s.employee) + '</span>' +
                    '<span class="sale-card-payment">' + payHtml + '</span>' +
                    '<span class="sale-card-total' + negClass + '">' + s.total_ttc + '</span>' +
                    '<svg class="sale-card-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>' +
                '</div>';

            // Items sub-table
            if (s.items && s.items.length > 0) {
                html += '<div class="sale-card-items" data-items-idx="' + i + '">' +
                    '<table class="sale-items-table"><thead><tr>' +
                    '<th class="col-ref">Réf</th><th>Article</th><th class="col-qty">Qté</th><th class="col-price">PU</th><th class="col-discount">Rem.</th><th class="col-price">Total</th>' +
                    '</tr></thead><tbody>';
                for (var j = 0; j < s.items.length; j++) {
                    var it = s.items[j];
                    var discountStr = (it.discount > 0) ? '<span class="item-discount">-' + it.discount + '%</span>' : '';
                    html += '<tr>' +
                        '<td class="col-ref">' + escHtml(it.ref) + '</td>' +
                        '<td>' + escHtml(it.name) + '</td>' +
                        '<td class="col-qty">' + it.qty + '</td>' +
                        '<td class="col-price">' + it.price + '</td>' +
                        '<td class="col-discount">' + discountStr + '</td>' +
                        '<td class="col-price">' + it.total + '</td>' +
                        '</tr>';
                }
                html += '</tbody></table></div>';
            }
            html += '</div>';
        }
        bodyEl.innerHTML = html;

        // Bind expand/collapse
        $(bodyEl).on('click', '.sale-card-header', function() {
            var idx = $(this).attr('data-idx');
            var itemsDiv = $(bodyEl).find('[data-items-idx="' + idx + '"]');
            var arrow = $(this).find('.sale-card-arrow');
            itemsDiv.toggleClass('visible');
            arrow.toggleClass('expanded');
        });
    }

    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // Bind CA HT clicks
    $(document).on('click', '.cell-ca-ht', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var personId = $(this).attr('data-person-id');
        var name = $(this).attr('data-customer-name');
        if (personId) {
            openPanel(personId, name);
        }
    });

    // Close panel
    if (closeBtn) closeBtn.addEventListener('click', closePanel);
    if (overlay) overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closePanel();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && panel.classList.contains('open')) {
            closePanel();
        }
    });
})();
</script>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
// Checkbox fix
?>
<script type="text/javascript">
$(document).ready(function() {
    $('#sortable_table :checkbox').unbind('click').click(function(e) {
        e.stopPropagation();
    });
    $('#select_all').unbind('click').click(function() {
        var checked = this.checked;
        $("#sortable_table tbody :checkbox").each(function() {
            this.checked = checked;
        });
    });
});
</script>

<script type="text/javascript" language="javascript">
// Expand/collapse details of sales
$(document).ready(function() {
    $(".tablesorter a.expand").click(function(event) {
        $(event.target).parent().parent().next().find('.innertable').toggle();
        if ($(event.target).text() == '+') {
            $(event.target).text('-');
        } else {
            $(event.target).text('+');
        }
        return false;
    });
});
</script>

<?php
// Show dialogs based on show_dialog
switch ($_SESSION['show_dialog'] ?? 0)
{
    case 1:
        include('../wrightetmathon/application/views/customers/form.php');
        break;
    case 5:
        include('../wrightetmathon/application/views/customers/merge_form.php');
        break;
    case 6:
        include('../wrightetmathon/application/views/customers/form_solde.php');
        break;
    case 7:
        include('../wrightetmathon/application/views/customers/search_ext_customers.php');
        break;
    case 8:
        include('../wrightetmathon/application/views/customers/add_ext_customers.php');
        break;
    case 9:
        include('../wrightetmathon/application/views/customers/add_ext_customers_fidelity_points.php');
        break;
    case 10:
        include('../wrightetmathon/application/views/customers/add_ext_sales.php');
        break;
}
?>
