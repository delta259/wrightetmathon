<?php $this->load->view("partial/header"); ?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
// Modal overlay (after footer, same pattern as items/modal_wrapper)
$this->load->view("partial/header_popup");

$type_labels = array('full' => $this->lang->line('inventaire_type_full'), 'rolling' => $this->lang->line('inventaire_type_rolling'), 'partial' => $this->lang->line('inventaire_type_partial'));
$type_label = isset($type_labels[$session->session_type]) ? $type_labels[$session->session_type] : $session->session_type;
if ($session->session_type === 'partial' && !empty($session->category_name)) {
    $type_label .= ' (' . htmlspecialchars($session->category_name) . ')';
}
$progress_pct = ($session->total_items > 0) ? round(($session->items_counted / $session->total_items) * 100) : 0;
?>

<div class="md-modal-overlay">
<div class="md-modal" style="max-width:95%;">

<!-- ========== MODAL HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background:var(--bg-card, #f0f9ff);">
            <svg width="28" height="28" fill="none" stroke="var(--modal-primary, #0A6184)" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref">Session #<?php echo $session->id; ?> - <?php echo $type_label; ?></div>
            <h2 class="md-modal-name"><?php echo $this->lang->line('inventaire_count'); ?> - <?php echo date('d/m/Y H:i', strtotime($session->started_at)); ?></h2>
        </div>
    </div>
    <div class="md-modal-header-actions" style="display:flex;align-items:center;gap:8px;">
        <a href="<?php echo site_url('inventaire'); ?>" class="md-modal-close" title="<?php echo $this->lang->line('inventaire_back_to_list'); ?>">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </a>
    </div>
</div>

<!-- ========== MODAL BODY ========== -->
<div class="md-modal-body" id="inv-count-body-content" style="padding:16px 24px;">

    <!-- Progress Bar -->
    <div style="background:var(--bg-card, #f8fafc);border:1px solid var(--border-color, #e2e8f0);border-radius:10px;padding:10px 16px;margin-bottom:12px;display:flex;align-items:center;gap:16px;">
        <span style="font-weight:600;color:var(--text-primary, #1e293b);font-size:0.85rem;white-space:nowrap;">
            <?php echo $this->lang->line('inventaire_progress'); ?> :
            <span id="progress_counted"><?php echo $session->items_counted; ?></span>/<?php echo $session->total_items; ?>
            (<span id="progress_pct"><?php echo $progress_pct; ?>%</span>)
        </span>
        <div style="flex:1;background:#e2e8f0;border-radius:6px;height:8px;overflow:hidden;">
            <div id="progress_bar" style="background:<?php echo ($progress_pct >= 100) ? '#22c55e' : '#3b82f6'; ?>;height:100%;width:<?php echo $progress_pct; ?>%;transition:width 0.3s;border-radius:6px;"></div>
        </div>
    </div>

    <!-- Filters Bar -->
    <?php $filtered_count = ($items) ? $items->num_rows() : 0; ?>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
        <form method="get" action="<?php echo site_url('inventaire/count/' . $session->id); ?>" id="search_form" style="width:220px;flex-shrink:0;">
            <div style="position:relative;">
                <svg style="position:absolute;left:8px;top:50%;transform:translateY(-50%);opacity:0.4;" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                <input type="text" id="search" name="search" style="width:100%;padding:5px 24px 5px 28px;border:1px solid var(--modal-input-border, #cbd5e1);border-radius:6px;font-size:0.8rem;background:var(--modal-input-bg, #fff);color:var(--modal-text, #1e293b);"
                       placeholder="Ref. ou designation..." tabindex="5" value="<?php echo htmlspecialchars($search); ?>">
                <?php if (!empty($search)): ?>
                <a href="<?php echo site_url('inventaire/count/' . $session->id . '?filter=' . $filter); ?>" id="search_clear" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);color:var(--modal-text-muted, #94a3b8);text-decoration:none;font-size:14px;line-height:1;padding:2px;" title="Effacer">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </form>
        <div style="display:flex;gap:6px;align-items:center;">
            <?php
            $filters = array(
                'all' => $this->lang->line('inventaire_filter_all'),
                'uncounted' => $this->lang->line('inventaire_filter_uncounted'),
                'counted' => $this->lang->line('inventaire_filter_counted')
            );
            foreach ($filters as $f_key => $f_label):
                $active = ($filter === $f_key) ? 'background:var(--modal-primary, #0A6184);color:#fff;' : 'background:var(--bg-card, #e2e8f0);color:var(--modal-text-muted, #475569);';
            ?>
            <a href="<?php echo site_url('inventaire/count/' . $session->id . '?filter=' . $f_key . (!empty($search) ? '&search=' . urlencode($search) : '')); ?>"
               style="<?php echo $active; ?>padding:5px 12px;border-radius:16px;font-size:0.78rem;font-weight:500;text-decoration:none;transition:all 0.15s;">
                <?php echo $f_label; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <span style="font-size:0.8rem;color:var(--modal-text-muted, #64748b);margin-left:auto;white-space:nowrap;">
            <strong><?php echo $filtered_count; ?></strong> article<?php echo ($filtered_count > 1) ? 's' : ''; ?>
        </span>
    </div>

    <!-- Counting Table -->
    <?php if ($items && $items->num_rows() > 0): ?>
    <div class="table-container" style="margin:0;">
        <div class="table-wrapper" style="max-height:65vh;">
            <table class="data-table tablesorter" id="count_table">
                <colgroup>
                    <col style="width:100px;"><!-- Famille -->
                    <col style="width:100px;"><!-- Référence -->
                    <col><!-- Désignation (auto) -->
                    <col style="width:80px;"><!-- Stk théorique -->
                    <col style="width:110px;"><!-- Qté comptée -->
                    <col style="width:200px;"><!-- Commentaire -->
                    <col style="width:40px;"><!-- Valider -->
                </colgroup>
                <thead>
                    <tr>
                        <th class="sortable-server" data-col="0" data-type="text"><?php echo $this->lang->line('inventaire_category'); ?> <span class="sort-arrow"></span></th>
                        <th class="sortable-server" data-col="1" data-type="text"><?php echo $this->lang->line('inventaire_reference'); ?> <span class="sort-arrow"></span></th>
                        <th class="sortable-server col-name" data-col="2" data-type="text"><?php echo $this->lang->line('inventaire_designation'); ?> <span class="sort-arrow"></span></th>
                        <th class="sortable-server col-number" data-col="3" data-type="num"><?php echo $this->lang->line('inventaire_theoretical_stock'); ?> <span class="sort-arrow"></span></th>
                        <th class="sortable-server col-number" data-col="4" data-type="num"><?php echo $this->lang->line('inventaire_counted_qty'); ?> <span class="sort-arrow"></span></th>
                        <th><?php echo $this->lang->line('inventaire_comment'); ?></th>
                        <th class="col-action"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items->result() as $item):
                        $is_counted = !empty($item->counted_at);
                        $row_class = $is_counted ? 'counted-row' : '';
                    ?>
                    <tr id="row_<?php echo $item->item_id; ?>" class="<?php echo $row_class; ?>">
                        <td class="cell-category"><span class="badge-category"><?php echo htmlspecialchars($item->category_name ?? ''); ?></span></td>
                        <td class="cell-id">
                            <a href="#" class="item-detail-link" data-item-id="<?php echo $item->item_id; ?>"><span class="badge-ref"><?php echo htmlspecialchars($item->item_number); ?></span></a>
                        </td>
                        <td class="cell-name" title="<?php echo htmlspecialchars($item->item_name); ?>"><?php echo htmlspecialchars($item->item_name); ?></td>
                        <td class="cell-number"><?php echo number_format((float)$item->expected_quantity, 2, ',', ' '); ?></td>
                        <td class="cell-number">
                            <input type="number" id="qty_<?php echo $item->item_id; ?>" class="inv-qty-input" data-item-id="<?php echo $item->item_id; ?>" step="any" value="<?php echo $is_counted ? (float)$item->counted_quantity : ''; ?>">
                        </td>
                        <td>
                            <input type="text" id="comment_<?php echo $item->item_id; ?>" class="inv-comment-input" data-item-id="<?php echo $item->item_id; ?>" value="<?php echo htmlspecialchars($item->comment ?? ''); ?>" placeholder="">
                        </td>
                        <td class="cell-action">
                            <button type="button" id="btn_<?php echo $item->item_id; ?>" class="inv-validate-btn btn-icon" data-item-id="<?php echo $item->item_id; ?>" title="<?php echo $this->lang->line('inventaire_validate'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?php echo $is_counted ? '#22c55e' : 'currentColor'; ?>" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:40px 20px;color:var(--text-secondary, #64748b);">
        <p><?php echo $this->lang->line('inventaire_no_items'); ?></p>
    </div>
    <?php endif; ?>

</div><!-- /md-modal-body -->

<!-- ========== MODAL FOOTER ========== -->
<div class="md-modal-footer">
    <div class="md-modal-footer-left">
        <a href="<?php echo site_url('inventaire'); ?>" class="md-btn md-btn-secondary" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:6px;font-size:0.85rem;font-weight:500;text-decoration:none;background:var(--modal-bg, #fff);color:var(--modal-text, #1e293b);border:1px solid var(--modal-border, #e2e8f0);">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            <?php echo $this->lang->line('inventaire_back_to_list'); ?>
        </a>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<!-- Overlay for item detail (loaded via AJAX) -->
<div id="item-detail-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;">
    <div id="item-detail-backdrop" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);"></div>
    <div id="item-detail-content" style="position:relative;z-index:1;"></div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Focus first uncounted input
    var firstUncountedRow = $('#count_table tbody tr').not('.counted-row').first();
    if (firstUncountedRow.length) {
        var firstInput = firstUncountedRow.find('.inv-qty-input');
        if (firstInput.length) firstInput.focus();
    }

    // ─── Item detail AJAX modal ────────────────────────────────────────
    var _overlaySourceItemId = null;

    function closeItemOverlay() {
        $('#item-detail-overlay').hide();
        $('#item-detail-content').html('');
        // Focus back on the source row
        if (_overlaySourceItemId) {
            var $row = $('#row_' + _overlaySourceItemId);
            if ($row.length) {
                var $container = $('#inv-count-body-content');
                var rowTop = $row.position().top + $container.scrollTop() - $container.position().top;
                $container.animate({scrollTop: rowTop - 60}, 150, function() {
                    $('#qty_' + _overlaySourceItemId).focus();
                });
            }
            _overlaySourceItemId = null;
        }
    }

    $('#count_table').on('click', '.item-detail-link', function(e) {
        e.preventDefault();
        _overlaySourceItemId = $(this).data('item-id');
        loadItemOverlay(_overlaySourceItemId);
    });

    function loadItemOverlay(itemId) {
        var $content = $('#item-detail-content');
        $content.html('<div style="text-align:center;padding:60px;"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" style="animation:spin 1s linear infinite;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg></div>');
        $('#item-detail-overlay').show();

        $.ajax({
            url: '<?php echo site_url("items/ajax_view_item"); ?>/' + itemId,
            type: 'GET',
            cache: false,
            success: function(html) {
                initOverlayContent(html);
            },
            error: function() {
                $content.html('<div style="text-align:center;padding:40px;color:#fff;">Erreur de chargement</div>');
            }
        });
    }

    function initOverlayContent(html) {
        var $content = $('#item-detail-content');
        $content.html(html);
        // Override close button & common_exit links to just close overlay
        $content.find('.md-modal-close, a[href*="common_controller/common_exit"]').off('click').on('click', function(e) {
            e.preventDefault();
            closeItemOverlay();
        });
        // Intercept form submissions to stay on count page
        $content.find('form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var action = $form.attr('action');
            if (!action) return;
            $.ajax({
                url: action,
                type: $form.attr('method') || 'POST',
                data: $form.serialize(),
                success: function() {
                    // Reload the product detail after save
                    if (_overlaySourceItemId) {
                        loadItemOverlay(_overlaySourceItemId);
                    }
                },
                error: function() {
                    alert('Erreur lors de la sauvegarde');
                }
            });
        });
        // Init handlers inside the overlay
        if (typeof initModalAjaxTabs === 'function') initModalAjaxTabs();
        if (typeof initModalHandlers === 'function') initModalHandlers();
        if (typeof initToggleActive === 'function') initToggleActive();
        if (typeof initFuzzySearchMerge === 'function') initFuzzySearchMerge();
    }

    // Close overlay on backdrop click
    $('#item-detail-backdrop').on('click', function() {
        closeItemOverlay();
    });

    // Close overlay on Escape key
    $(document).on('keydown', function(e) {
        if (e.which === 27 && $('#item-detail-overlay').is(':visible')) {
            closeItemOverlay();
        }
    });

    // ─── Handle merge completion: update row in count table ─────────────
    window.onMergeComplete = function(resp) {
        var m = resp.merged;
        var $oldRow = $('#row_' + m.from_item_id);
        var qtyFormatted = parseFloat(m.to_quantity).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        if ($oldRow.length) {
            // Check if TO item already has a row (both items were in the session)
            var $existingRow = $('#row_' + m.to_item_id);
            if ($existingRow.length) {
                // TO row exists: remove the old FROM row, update TO row qty
                $oldRow.remove();
                $existingRow.find('td.cell-number').first().text(qtyFormatted);
            } else {
                // Swap: update the FROM row to become the TO row
                $oldRow.attr('id', 'row_' + m.to_item_id);
                $oldRow.find('.item-detail-link').attr('data-item-id', m.to_item_id);
                $oldRow.find('.badge-ref').text(m.to_item_number);
                $oldRow.find('.cell-name').attr('title', m.to_name).text(m.to_name);
                $oldRow.find('td.cell-number').first().text(qtyFormatted);
                $oldRow.find('.inv-qty-input').attr('id', 'qty_' + m.to_item_id).data('item-id', m.to_item_id);
                $oldRow.find('.inv-comment-input').attr('id', 'comment_' + m.to_item_id).data('item-id', m.to_item_id);
                $oldRow.find('.inv-validate-btn').attr('id', 'btn_' + m.to_item_id).data('item-id', m.to_item_id);
            }
        }

        // Reload overlay with the merged TO item's product detail
        _overlaySourceItemId = m.to_item_id;
        loadItemOverlay(m.to_item_id);
    };

    // Keyboard navigation: Enter in qty -> focus comment, then Enter in comment -> validate
    $('#count_table').on('keydown', '.inv-qty-input', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            var itemId = $(this).data('item-id');
            $('#comment_' + itemId).focus();
        }
    });

    $('#count_table').on('keydown', '.inv-comment-input', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            var itemId = $(this).data('item-id');
            submitCount(itemId);
        }
    });

    // Click validate button
    $('#count_table').on('click', '.inv-validate-btn', function() {
        var itemId = $(this).data('item-id');
        submitCount(itemId);
    });
});

function submitCount(itemId) {
    var qtyInput = $('#qty_' + itemId);
    var commentInput = $('#comment_' + itemId);
    var btn = $('#btn_' + itemId);
    var row = $('#row_' + itemId);

    var qty = qtyInput.val();
    if (qty === '' || isNaN(qty)) {
        qtyInput.css('border-color', '#ef4444');
        qtyInput.focus();
        return;
    }

    btn.attr('disabled', 'disabled');
    qtyInput.attr('disabled', 'disabled');
    commentInput.attr('disabled', 'disabled');

    $.ajax({
        url: '<?php echo site_url("inventaire/save_count"); ?>',
        type: 'POST',
        dataType: 'json',
        data: {
            session_id: <?php echo $session->id; ?>,
            item_id: itemId,
            counted_qty: qty,
            comment: commentInput.val()
        },
        success: function(response) {
            if (response.success) {
                // Mark row as counted visually
                row.addClass('counted-row');
                qtyInput.removeAttr('disabled').css('border-color', '#22c55e');
                commentInput.removeAttr('disabled').css('border-color', '#22c55e');
                btn.removeAttr('disabled').find('svg').attr('stroke', '#22c55e');

                // Update progress
                $('#progress_counted').text(response.items_counted);
                var total = parseInt(response.total_items);
                var counted = parseInt(response.items_counted);
                var pct = total > 0 ? Math.round((counted / total) * 100) : 0;
                $('#progress_bar').css('width', pct + '%');
                $('#progress_pct').text(pct + '%');

                if (pct >= 100) {
                    $('#progress_bar').css('background', '#22c55e');
                }

                // Find next uncounted row (no counted-row class, excluding current)
                var nextRow = row.nextAll('tr').not('.counted-row').first();
                if (nextRow.length) {
                    var nextQtyInput = nextRow.find('.inv-qty-input');
                    if (nextQtyInput.length) {
                        var container = $('#inv-count-body-content');
                        var rowTop = nextRow.position().top + container.scrollTop() - container.position().top;
                        container.animate({scrollTop: rowTop - 60}, 200, function() {
                            nextQtyInput.focus();
                        });
                    }
                }
            } else {
                alert(response.message || 'Erreur');
                qtyInput.removeAttr('disabled');
                commentInput.removeAttr('disabled');
                btn.removeAttr('disabled');
            }
        },
        error: function() {
            alert('Erreur de communication avec le serveur');
            qtyInput.removeAttr('disabled');
            commentInput.removeAttr('disabled');
            btn.removeAttr('disabled');
        }
    });
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// ─── Column sorting ─────────────────────────────────────────────────────
(function() {
    var currentSort = { col: -1, asc: true };

    function getCellValue(row, col, type) {
        var td = row.children[col];
        if (!td) return '';
        // For columns with inputs, read the input value
        var input = td.querySelector('input');
        if (input && (type === 'num')) return input.value;
        // For columns with links, read the link text
        var a = td.querySelector('a');
        if (a) return a.textContent.trim();
        return td.textContent.trim();
    }

    function compareRows(a, b, col, type, asc) {
        var va = getCellValue(a, col, type);
        var vb = getCellValue(b, col, type);
        if (type === 'num') {
            va = parseFloat(va.replace(/\s/g, '').replace(',', '.')) || 0;
            vb = parseFloat(vb.replace(/\s/g, '').replace(',', '.')) || 0;
            return asc ? va - vb : vb - va;
        }
        va = va.toLowerCase();
        vb = vb.toLowerCase();
        if (va < vb) return asc ? -1 : 1;
        if (va > vb) return asc ? 1 : -1;
        return 0;
    }

    $(document).on('click', '#count_table th.sortable-server', function() {
        var col = parseInt($(this).data('col'));
        var type = $(this).data('type') || 'text';

        if (currentSort.col === col) {
            currentSort.asc = !currentSort.asc;
        } else {
            currentSort.col = col;
            currentSort.asc = true;
        }

        // Update arrows
        $('#count_table th.sortable-server').removeClass('sort-active');
        $('#count_table th.sortable-server .sort-arrow').html('');
        var arrow = currentSort.asc ? '\u25B2' : '\u25BC';
        $(this).addClass('sort-active').find('.sort-arrow').html(arrow);

        // Sort rows
        var tbody = $('#count_table tbody');
        var rows = tbody.find('tr').get();
        rows.sort(function(a, b) {
            return compareRows(a, b, col, type, currentSort.asc);
        });
        $.each(rows, function(i, row) {
            tbody.append(row);
        });
    });
})();
</script>

<style>
.popbg {
    pointer-events: none !important;
}

/* ─── Sortable headers (client-side, same visual as items/manage) ─── */
#count_table thead th.sortable-server {
    cursor: pointer;
    user-select: none;
}
#count_table thead th.sortable-server:hover {
    color: var(--accent-blue, #2563eb);
}
#count_table thead th.sortable-server.sort-active {
    color: var(--accent-blue, #2563eb);
}

/* ─── Counted row state ─── */
#count_table tbody tr.counted-row {
    background: var(--success-bg, #f0fdf4) !important;
}
#count_table tbody tr.counted-row:hover {
    background: rgba(16, 185, 129, 0.25) !important;
}
#count_table tbody tr.counted-row > td:first-child {
    border-left: 4px solid var(--success, #22c55e);
}

/* ─── Inline inputs (qty + comment) ─── */
.inv-qty-input,
.inv-comment-input {
    width: 100%;
    padding: 4px 8px;
    border: 1px solid var(--border-color, #cbd5e1);
    border-radius: var(--radius-sm, 4px);
    background: var(--bg-card, #fff);
    color: var(--text-primary, #1e293b);
    font-size: 13px;
    font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.inv-qty-input {
    text-align: right;
    font-family: 'SF Mono', 'Consolas', monospace;
    font-weight: 600;
}
.inv-qty-input:focus,
.inv-comment-input:focus {
    outline: none;
    border-color: var(--primary, #2563eb) !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
.counted-row .inv-qty-input,
.counted-row .inv-comment-input {
    border-color: var(--success, #22c55e);
}

/* ─── Validate button ─── */
.inv-validate-btn {
    border: none;
    background: none;
    cursor: pointer;
}
.inv-validate-btn:hover svg {
    stroke: var(--success, #22c55e) !important;
}

/* ─── Item detail overlay ─── */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
#item-detail-content .md-modal-overlay {
    position: relative;
    background: none;
}
#item-detail-content .md-modal {
    max-height: 90vh;
}
</style>
