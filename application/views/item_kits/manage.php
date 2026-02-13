<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() {
    enable_row_selection();

    // Focus search field
    var searchField = document.getElementById("search");
    if (searchField) {
        searchField.focus();
        var len = searchField.value.length;
        if (searchField.setSelectionRange) {
            searchField.setSelectionRange(len, len);
        }
    }

    // Autocomplete for item search in add_item form
    if (typeof $.fn.autocomplete !== 'undefined') {
        $("#search_item_kit").autocomplete(
            '<?php echo site_url("sales/item_search"); ?>',
            {
                minChars: 2,
                max: 100,
                selectOnly: true,
                delay: 1,
                formatItem: function(row) {
                    return row[1];
                }
            }
        );
        $("#search_item_kit").result(function(event, data, formatted) {
            $("#item_kit_form_item_kit").submit();
        });
    }

    // Clickable rows
    $(document).on('click', '.clickable-row', function(e) {
        if ($(e.target).closest('a').length) return;
        var href = $(this).data('href');
        if (href) window.location = href;
    });
});
</script>

<?php
// Reset line number
$_SESSION['line_number'] = 0;
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <?php echo $this->lang->line('modules_item_kits'); ?>
    </h1>
    <div class="page-actions">
        <!-- Add Button -->
        <a href="<?php echo site_url("item_kits/view/-1"); ?>" class="btn-action btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Ajouter
        </a>

        <!-- Print Button -->
        <button type="button" class="btn-action" onclick="Printer.print(document.getElementById('sortable_table').innerHTML);" title="Imprimer">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Imprimer
        </button>

        <!-- Badge -->
        <span class="badge badge-info">
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
            </svg>
            <span id="kit-count-badge"><?php echo $this->Item_kit->count_all(); ?> kits</span>
        </span>
    </div>
</div>

<!-- Filters Bar -->
<div class="filters-bar">
    <?php echo form_open("item_kits/search", array('id' => 'search_form', 'class' => 'filters-form')); ?>
    <div class="filter-group">
        <div class="search-input-wrapper">
            <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input type="text" id="search" name="search" class="form-control search-field"
                   placeholder="Rechercher un kit..." tabindex="5" value="" autocomplete="off">
            <button type="button" id="clear_search" class="search-clear-btn" style="display:none;" title="Effacer la recherche">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
    </form>
</div>

<!-- Messages -->
<?php if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0): ?>
    <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>
<?php endif; ?>

<!-- Table Container -->
<div class="table-container">
    <div class="table-wrapper">
        <table class="tablesorter" id="sortable_table">
            <colgroup>
                <col style="width:40px;">
                <col style="width:40px;">
                <col style="width:150px;">
                <col>
                <col style="width:100px;">
                <col style="width:100px;">
                <col style="width:140px;">
            </colgroup>
            <thead>
                <tr>
                    <th class="col-action"></th>
                    <th class="col-icon" title="Code barre">EAN</th>
                    <th><?php echo $this->lang->line('item_kits_name'); ?></th>
                    <th><?php echo $this->lang->line('item_kits_description'); ?></th>
                    <th class="col-price"><?php echo $this->lang->line('item_kits_cost_price'); ?></th>
                    <th class="col-price"><?php echo $this->lang->line('item_kits_unit_price_with_tax'); ?></th>
                    <th><?php echo $this->lang->line('item_kits_code_bar'); ?></th>
                </tr>
            </thead>
            <tbody id="table_contents">
            <?php if (!empty($manage_table_data)): ?>
                <?php foreach ($manage_table_data as $kit): ?>
                <?php
                    $kit_id = $kit->item_kit_id;
                    $tout = (string)$kit_id . ':0:item_kits';
                    $row_class = (isset($kit->deleted) && $kit->deleted == 1) ? ' class="row-inactive clickable-row"' : ' class="clickable-row"';
                    $cost = isset($kit->cost_kit) ? number_format((float)$kit->cost_kit, 2, ',', ' ') : '0,00';
                    $price = isset($kit->unit_price_with_tax) ? number_format((float)$kit->unit_price_with_tax, 2, ',', ' ') : '0,00';
                    $has_barcode = isset($kit->barcode) && strlen($kit->barcode) > 5;
                ?>
                <tr<?php echo $row_class; ?> data-href="<?php echo site_url('item_kits/view/'.$kit_id); ?>" style="cursor:pointer;">
                    <td class="cell-action">
                        <?php if (!isset($kit->deleted) || $kit->deleted == 0): ?>
                        <a href="<?php echo site_url('item_kits/desactive/'.$tout); ?>" class="btn-icon btn-toggle-active" title="Désactiver">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </a>
                        <?php else: ?>
                        <a href="<?php echo site_url('item_kits/desactive/'.$tout); ?>" class="btn-icon btn-toggle-inactive" title="Activer">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </a>
                        <?php endif; ?>
                    </td>
                    <td class="cell-action">
                        <?php if ($has_barcode): ?>
                        <a href="<?php echo site_url('item_kits/view/'.$kit_id); ?>" class="btn-icon btn-barcode-ok" title="Code barre existant">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 5v14M6 5v14M9 5v14M12 5v14M15 5v14M18 5v14M21 5v14"/></svg>
                        </a>
                        <?php else: ?>
                        <a href="<?php echo site_url('item_kits/view/'.$kit_id); ?>" class="btn-icon btn-barcode-missing" title="Code barre manquant">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 5v14M6 5v14M9 5v14M12 5v14M15 5v14M18 5v14M21 5v14"/></svg>
                        </a>
                        <?php endif; ?>
                    </td>
                    <td class="cell-name"><a href="<?php echo site_url('item_kits/view/'.$kit_id); ?>" title="Éditer le kit"><span class="badge-ref"><?php echo htmlspecialchars($kit->name); ?></span></a></td>
                    <td class="cell-desc" title="<?php echo htmlspecialchars($kit->description); ?>"><?php echo htmlspecialchars($kit->description); ?></td>
                    <td class="cell-price"><?php echo $cost; ?> &euro;</td>
                    <td class="cell-price"><?php echo $price; ?> &euro;</td>
                    <td class="cell-barcode"><?php echo htmlspecialchars($kit->barcode ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b;"><?php echo $this->lang->line('common_no_persons_to_display'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="table-info">
            <span class="item-count"></span>
        </div>
        <?php if (!empty($links)): ?>
        <div class="pagination-wrapper"><?php echo $links; ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- Live Search Filter -->
<script type="text/javascript">
(function() {
    var searchInput = document.getElementById('search');
    var clearBtn = document.getElementById('clear_search');
    var table = document.getElementById('sortable_table');
    var debounceTimer = null;
    var minChars = 1;

    if (!searchInput || !table) return;

    var rowCache = [];
    var originalRowCount = 0;

    function buildCache() {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var rows = tbody.getElementsByTagName('tr');
        var len = rows.length;
        rowCache = new Array(len);

        for (var i = 0; i < len; i++) {
            var row = rows[i];
            var cells = row.cells;
            var text = '';

            // Columns: 0=toggle, 1=barcode icon, 2=name, 3=description, 4=cost, 5=price, 6=barcode
            if (cells.length > 3) {
                text = (cells[2].textContent || '') + ' ' +
                       (cells[3].textContent || '') + ' ' +
                       (cells[6] ? cells[6].textContent : '');
            }

            rowCache[i] = {
                row: row,
                text: text.toLowerCase()
            };
        }
        originalRowCount = len;
        updateCount(len, false);
    }

    buildCache();

    function filterTable(searchText) {
        var searchLower = searchText.toLowerCase().trim();
        var isFiltering = searchLower.length >= minChars;
        var visibleCount = 0;
        var len = rowCache.length;

        if (clearBtn) {
            clearBtn.style.display = searchLower.length > 0 ? 'flex' : 'none';
        }

        searchInput.className = searchInput.className.replace(' filter-active', '');
        if (isFiltering) {
            searchInput.className += ' filter-active';
        }

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
                countSpan.textContent = count + '/' + originalRowCount + ' kits (filtré)';
                countSpan.style.color = 'var(--primary)';
                countSpan.style.fontWeight = 'bold';
            } else {
                countSpan.textContent = count + ' kits affichés';
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

    if (clearBtn) {
        clearBtn.onclick = clearLocalFilter;
    }

    searchInput.onkeyup = function(e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            filterTable(searchInput.value);
        }, 150);
    };

    searchInput.onkeydown = function(e) {
        if (e.keyCode === 27) { // Escape
            clearLocalFilter();
        } else if (e.keyCode === 13) { // Enter
            e.preventDefault();
            document.getElementById('search_form').submit();
        }
    };
})();
</script>

<!-- Print Script -->
<script type="text/javascript">
var Printer = {
    print: function(HTML) {
        var win = window.open('', '_blank');
        win.document.write(
            '<html>' +
            '<head>' +
                '<title>Kits - Impression</title>' +
                '<style>' +
                    'body { font-family: Arial, sans-serif; font-size: 11px; }' +
                    'table { width: 100%; border-collapse: collapse; }' +
                    'th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }' +
                    'th { background: #f5f5f5; font-weight: bold; }' +
                    'tr:nth-child(even) { background: #fafafa; }' +
                    'h2 { margin-bottom: 10px; }' +
                    '@media print { body { -webkit-print-color-adjust: exact; } }' +
                '</style>' +
            '</head>' +
            '<body>' +
                '<h2>Liste des Kits</h2>' +
                '<table>' + HTML + '</table>' +
            '</body>' +
            '</html>'
        );
        win.document.close();
        win.print();
        win.close();
    }
};
</script>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
// Show dialog depending on show_dialog
switch ($_SESSION['show_dialog'] ?? 0)
{
    case 1:
        include('../wrightetmathon/application/views/item_kits/form_new.php');
        break;
    case 2:
        include('../wrightetmathon/application/views/item_kits/add_item.php');
        break;
}
?>
