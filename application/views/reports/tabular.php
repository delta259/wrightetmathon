<?php $this->load->view("partial/header"); ?>

<?php
    // get the number format
    $pieces = array();
    $pieces = explode("/", $this->config->item('numberformat'));
?>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                </svg>
                <?php echo $title; ?>
            </h1>
            <div class="page-actions">
                <?php if (isset($_SESSION['inline_inventory_mode']) && $_SESSION['inline_inventory_mode'] == '1'): ?>
                <button type="button" class="btn-action" onclick="PrinterIR.print();" title="Imprimer fiche inventaire">
                <?php else: ?>
                <button type="button" class="btn-action" onclick="Printer.print(document.getElementById('sortable_table').innerHTML);" title="Imprimer">
                <?php endif; ?>
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Imprimer
                </button>

                <?php if (isset($_SESSION['inline_inventory_mode']) && $_SESSION['inline_inventory_mode'] == '1'): ?>
                    <label id="toggle-done-label" style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;margin:0 12px;font-size:13px;color:var(--text-secondary,#666);user-select:none;">
                        <span id="toggle-done-switch" style="position:relative;display:inline-block;width:34px;height:18px;background:#ccc;border-radius:9px;transition:background 0.2s;">
                            <span id="toggle-done-knob" style="position:absolute;top:2px;left:2px;width:14px;height:14px;background:#fff;border-radius:50%;transition:left 0.2s;box-shadow:0 1px 2px rgba(0,0,0,0.2);"></span>
                        </span>
                        Afficher traités
                    </label>
                <?php endif; ?>

                <?php if (isset($_SESSION['tabular_articles_yes']) && $_SESSION['tabular_articles_yes'] == '1'): ?>
                    <span class="badge badge-info" id="ir-badge" data-displayed="<?php echo $_SESSION['compteur']; ?>" data-total="<?php echo isset($_SESSION['total_articles_ir']) ? $_SESSION['total_articles_ir'] : ''; ?>" data-treated="<?php echo isset($_SESSION['ir_treated_lines']) ? count($_SESSION['ir_treated_lines']) : 0; ?>">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                        </svg>
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

        <?php if (!isset($_SESSION['inline_inventory_mode']) || $_SESSION['inline_inventory_mode'] != '1'): ?>
        <!-- Subtitle -->
        <div class="filters-bar">
            <div class="filter-group">
                <span style="font-size: 14px; color: var(--text-secondary);">
                    <?php
                    if (isset($_SESSION['tabular_articles_yes']) && $_SESSION['tabular_articles_yes'] == '1') {
                        echo $subtitle . " ( " . $_SESSION['compteur'] . " " . $this->lang->line('items_items') . ")";
                    } else {
                        echo $subtitle;
                    }
                    ?>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Messages -->
        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php
        // Focus line setup
        if (isset($_SESSION['line_focus'])) {
            $_SESSION['line'] = $_SESSION['line_focus'];
        }
        ?>

        <?php if (isset($_SESSION['inline_inventory_mode']) && $_SESSION['inline_inventory_mode'] == '1'): ?>
        <style>
            #sortable_table { table-layout: fixed; width: 100%; border-collapse: separate !important; border-spacing: 0 !important; overflow: visible !important; }
            #sortable_table thead th { background: var(--table-header-bg, #f8f9fa) !important; }
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
            #sortable_table th { position: sticky; top: 0; z-index: 10; }
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

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="data-table tablesorter" id="sortable_table">
                    <thead>
                        <tr>
                            <?php if (isset($_SESSION['oeil_desactivation']) && $_SESSION['oeil_desactivation'] == '1'): ?>
                                <th class="col-action" style="text-align:center;">Désactiver</th>
                            <?php endif; ?>
                            <?php foreach ($headers as $header): ?>
                                <th><?php echo $header; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $line => $row):
                            // Determine if this is the focused line
                            $is_focused = (isset($_SESSION['line']) && $_SESSION['line'] == $line);
                            $row_class = $is_focused ? 'row-focused' : '';
                            // Mark treated lines for inventory rolling
                            $is_ir_treated = (isset($_SESSION['ir_treated_lines']) && in_array($line, $_SESSION['ir_treated_lines']));
                            if ($is_ir_treated) { $row_class .= ' ir-done'; }

                            // Get item info for eye icon (only when oeil_desactivation is active)
                            $request_for_deleted = array('deleted' => '0');
                            $request_for_quantity = array('quantity' => 1);
                            $tout = '';
                            if (isset($_SESSION['oeil_desactivation']) && $_SESSION['oeil_desactivation'] == '1') {
                                $cur_item_info = new stdClass();
                                $tableau_recuperation_item_id = array();
                                $tableau_recuperation_item_id = explode("/", $row[1]);
                                $cur_item_info->item_id = isset($tableau_recuperation_item_id[7]) ? $tableau_recuperation_item_id[7] : 0;
                                $data_item = $this->Item->get_info($cur_item_info->item_id);
                                $request_for_deleted = array('deleted' => $data_item->deleted);
                                $request_for_quantity = array('quantity' => $data_item->quantity);
                                $tout = (string)$cur_item_info->item_id . ':' . (string)$line . ':' . 'reports';
                            }
                        ?>
                        <tr class="<?php echo $row_class; ?>" <?php if ($is_focused) echo 'id="line_couleur"'; ?>>
                            <?php if (isset($_SESSION['oeil_desactivation']) && $_SESSION['oeil_desactivation'] == '1'): ?>
                                <td class="cell-action" style="text-align:center;">
                                    <?php if ($request_for_quantity['quantity'] <= 0): ?>
                                        <?php if ($request_for_deleted['deleted'] == '1'): ?>
                                            <!-- Article INACTIF: œil ouvert VERT = cliquer pour activer -->
                                            <a href="<?php echo site_url("receivings/desactive/$tout"); ?>" class="btn-icon btn-action-activate" title="Activer">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                        <?php else: ?>
                                            <!-- Article ACTIF: œil barré ROUGE = cliquer pour désactiver -->
                                            <a href="<?php echo site_url("receivings/desactive/$tout"); ?>" class="btn-icon btn-action-deactivate" title="Désactiver">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>

                            <?php foreach ($row as $cell):
                                if (is_numeric($cell)) {
                                    if (is_int($cell)) {
                                        $cell = intval($cell);
                                    }
                                    if (is_float($cell)) {
                                        $cell = floatval($cell);
                                    }
                                    $cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);
                                    $align_class = 'cell-price';
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

            <!-- Table Footer with Summary -->
            <div class="table-footer">
                <div class="table-info">
                    <?php foreach ($summary_data as $name => $value): ?>
                        <span class="item-count" style="margin-right: 16px;">
                            <?php echo $this->lang->line('reports_' . $name) . ': ' . number_format($value, $pieces[0], $pieces[1], $pieces[2]); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

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
        var subtitle = document.querySelector('.filters-bar .filter-group span') ? document.querySelector('.filters-bar .filter-group span').innerHTML : '';
        var resume = document.getElementById('report_summary') ? document.getElementById('report_summary').innerHTML : '';
        win.document.write(`
            <html>
            <head>
                <title>${document.title}</title>
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
                <h2>${magasin}</h2>
                <h3>${subtitle}</h3>
                <table>${HTML}</table>
                <br/><br/>${resume}
            </body>
            </html>
        `);
        win.document.close();
        win.print();
        win.close();
    }
};
</script>

<script type="text/javascript">
$(document).ready(function() {
    $("#sortable_table").tablesorter();
});
</script>

<?php if (isset($_SESSION['inline_inventory_mode']) && $_SESSION['inline_inventory_mode'] == '1'): ?>
<script type="text/javascript">
var PrinterIR = {
    print: function() {
        var win = window.open('', '_blank');
        var magasin = '<?php echo addslashes($title); ?>';
        var subtitle = document.querySelector('.filters-bar .filter-group span') ? document.querySelector('.filters-bar .filter-group span').innerHTML : '';
        var today = new Date();
        var dateStr = today.toLocaleDateString('fr-FR');

        // Build simplified table from screen data
        var rows = [];
        var trs = document.getElementById('sortable_table').getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            var tds = trs[i].getElementsByTagName('td');
            if (!tds || tds.length < 7) continue;
            // col 0=Desactiver, 1=Fam, 2=Ref, 3=Designation, 4=Stk, 5=StockReel, 6=Commentaire
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

        // read comment from the next td's input (same item_id)
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
                    // update badge counter
                    var badge = document.getElementById('ir-badge');
                    if (badge) {
                        var displayed = parseInt(badge.getAttribute('data-displayed'), 10) - 1;
                        var treated = parseInt(badge.getAttribute('data-treated') || '0', 10) + 1;
                        badge.setAttribute('data-displayed', displayed);
                        badge.setAttribute('data-treated', treated);
                        updateBadgeText();
                    }
                    // focus next visible inline-real-qty input
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

    // Bind click on validate buttons
    $(".inline-inv-btn").click(function() {
        var btnItemId = this.getAttribute('data-item-id');
        var form = $("form.inline-inv-form[data-item-id='" + btnItemId + "']")[0];
        if (form) submitInlineForm(form);
    });

    // Bind Enter key on real_qty inputs - move to comment field
    $("input.inline-real-qty").each(function() {
        var input = this;
        input.onkeydown = function(e) {
            e = e || window.event;
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                if (e.preventDefault) e.preventDefault();
                // Find the form (parent element)
                var form = input.parentNode;
                var itemId = form.getAttribute('data-item-id');
                // Find comment input with same item-id
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

    // Bind Enter key on comment inputs — validate if real_qty is filled
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

    // Badge update helper
    var showDone = false;
    function updateBadgeText() {
        var badge = document.getElementById('ir-badge');
        var badgeText = document.getElementById('ir-badge-text');
        if (!badge || !badgeText) return;
        var displayed = badge.getAttribute('data-displayed');
        var total = badge.getAttribute('data-total');
        var treated = badge.getAttribute('data-treated') || '0';
        if (showDone) {
            badgeText.innerHTML = treated + '/' + total + ' traités';
        } else {
            badgeText.innerHTML = displayed + '/' + total + ' <?php echo $this->lang->line('items_items'); ?>';
        }
    }

    // Toggle show/hide done rows
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

    // Auto-focus first input
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
