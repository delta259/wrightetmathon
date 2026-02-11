<?php $this->load->view("partial/header_popup"); ?>

<?php
$customer_name = isset($_SESSION['CSI']['SHV']->customer_formatted) ? $_SESSION['CSI']['SHV']->customer_formatted : '';
$invoices = isset($_SESSION['CSI']['SHV']->CN_customer_invoices) ? $_SESSION['CSI']['SHV']->CN_customer_invoices : array();
$ajax_url = site_url('sales/ajax_sale_detail');
?>

<div class="md-modal-overlay">
<div class="md-modal cn-modal-wide">

    <!-- Header -->
    <div class="md-modal-header">
        <div class="md-modal-header-left">
            <div class="md-modal-avatar" style="background:linear-gradient(135deg,var(--warning),#d97706);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            </div>
            <div class="md-modal-header-info">
                <div class="md-modal-name">Avoir</div>
                <div class="md-modal-ref"><?php echo htmlspecialchars($customer_name); ?></div>
            </div>
        </div>
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </a>
    </div>

    <!-- Body: split layout -->
    <div class="cn-split-body">

        <!-- LEFT: Invoice list -->
        <div class="cn-list-pane">

            <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

            <!-- Manual entry -->
            <?php echo form_open("sales/CN_select_invoice_item", array('id' => 'cn_form')); ?>
            <div class="cn-search-bar">
                <label class="cn-search-label">N&deg; Facture :</label>
                <?php echo form_input(array(
                    'name'        => 'CN_original_invoice',
                    'id'          => 'CN_original_invoice',
                    'value'       => isset($_SESSION['CSI']['SHV']->CN_original_invoice) ? $_SESSION['CSI']['SHV']->CN_original_invoice : '',
                    'class'       => 'md-form-input cn-search-input',
                    'placeholder' => 'N° facture',
                    'autofocus'   => 'autofocus',
                )); ?>
                <button type="submit" class="md-btn md-btn-primary cn-search-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    Valider
                </button>
            </div>
            <?php echo form_close(); ?>

            <!-- Invoice list -->
            <?php if (!empty($invoices)): ?>
            <div class="cn-list-header">
                Factures r&eacute;centes (<?php echo count($invoices); ?>)
            </div>
            <div class="cn-list-scroll">
                <?php foreach ($invoices as $inv):
                    $payment_clean = strip_tags($inv->payment_type);
                    $payment_parts = explode(':', rtrim($payment_clean, " \t\n\r\0\x0B,;"));
                    $payment_short = trim($payment_parts[0]);
                    $amount_formatted = number_format($inv->overall_total, 2, ',', ' ');
                    $is_negative = ($inv->overall_total < 0);
                ?>
                    <div class="cn-inv-card<?php echo $is_negative ? ' cn-inv-negative' : ''; ?>" data-sale-id="<?php echo $inv->sale_id; ?>">
                        <div class="cn-inv-card-top">
                            <span class="cn-inv-id">#<?php echo $inv->sale_id; ?></span>
                            <span class="cn-inv-date"><?php echo date('d/m/Y H:i', strtotime($inv->sale_time)); ?></span>
                            <span class="cn-inv-amount<?php echo $is_negative ? ' cn-inv-amount-neg' : ''; ?>"><?php echo $amount_formatted; ?> &euro;</span>
                        </div>
                        <div class="cn-inv-card-bottom">
                            <span class="cn-inv-meta"><?php echo (int)$inv->item_count; ?> article<?php echo ((int)$inv->item_count > 1) ? 's' : ''; ?></span>
                            <span class="cn-inv-meta"><?php echo htmlspecialchars($payment_short); ?></span>
                            <span class="cn-inv-meta"><?php echo htmlspecialchars($inv->employee_name); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="cn-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Aucune facture trouv&eacute;e
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Detail pane -->
        <div class="cn-detail-pane" id="cn-detail-pane">
            <div class="cn-detail-empty" id="cn-detail-empty">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                </svg>
                <div>S&eacute;lectionnez une facture<br>pour voir le d&eacute;tail</div>
            </div>
            <div class="cn-detail-loading" id="cn-detail-loading" style="display:none;">
                <div class="cn-spinner"></div>
                Chargement...
            </div>
            <div class="cn-detail-content" id="cn-detail-content" style="display:none;">
                <!-- Filled via AJAX -->
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="md-modal-footer">
        <div class="md-modal-footer-left">
            <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour
            </a>
        </div>
        <div class="md-modal-footer-right">
            <button type="button" class="md-btn md-btn-primary" id="cn-select-btn" style="display:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span id="cn-select-btn-text">S&eacute;lectionner cette facture</span>
            </button>
        </div>
    </div>

</div>
</div>

<style>
/* Modal wider for split layout */
.cn-modal-wide {
    max-width: 1200px !important;
    width: 95% !important;
}

/* Split body */
.cn-split-body {
    display: flex;
    min-height: 480px;
    max-height: 70vh;
    border-top: 1px solid var(--border-color, #e2e8f0);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}

/* Left pane: invoice list */
.cn-list-pane {
    width: 440px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-container, #fff);
}

/* Search bar */
.cn-search-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.cn-search-label {
    font-weight: 600;
    font-size: 12px;
    white-space: nowrap;
    color: var(--text-secondary, #64748b);
}
.cn-search-input {
    flex: 1;
    font-size: 16px !important;
    font-weight: 600 !important;
    text-align: center;
    padding: 8px 10px !important;
}
.cn-search-btn {
    white-space: nowrap;
    padding: 8px 14px !important;
    font-size: 13px !important;
}

/* List header */
.cn-list-header {
    padding: 10px 16px 6px;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Scrollable list */
.cn-list-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 4px 10px 10px;
}

/* Invoice card */
.cn-inv-card {
    padding: 10px 12px;
    margin-bottom: 4px;
    border-radius: 8px;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.15s ease;
}
.cn-inv-card:hover {
    background: var(--bg-hover, rgba(37,99,235,0.05));
    border-color: var(--border-color, #e2e8f0);
}
.cn-inv-card.cn-inv-active {
    background: rgba(37,99,235,0.08);
    border-color: var(--accent-blue, #2563eb);
}
.cn-inv-card.cn-inv-negative {
    opacity: 0.6;
}
.cn-inv-card-top {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.cn-inv-id {
    font-weight: 700;
    font-size: 14px;
    color: var(--accent-blue, #2563eb);
}
.cn-inv-date {
    font-size: 12px;
    color: var(--text-secondary, #64748b);
}
.cn-inv-amount {
    margin-left: auto;
    font-weight: 700;
    font-size: 14px;
    color: var(--text-primary, #1e293b);
}
.cn-inv-amount-neg {
    color: var(--danger, #ef4444);
}
.cn-inv-card-bottom {
    display: flex;
    gap: 12px;
}
.cn-inv-meta {
    font-size: 11px;
    color: var(--text-tertiary, #94a3b8);
}

/* Right pane: detail */
.cn-detail-pane {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--bg-card, #f8fafc);
    overflow-y: auto;
}

/* Empty state */
.cn-detail-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    color: var(--text-secondary, #94a3b8);
    text-align: center;
    font-size: 14px;
    line-height: 1.5;
}
.cn-detail-empty svg {
    opacity: 0.3;
}

/* Loading */
.cn-detail-loading {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: var(--text-secondary, #94a3b8);
    font-size: 13px;
}
.cn-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid var(--border-color, #e2e8f0);
    border-top-color: var(--accent-blue, #2563eb);
    border-radius: 50%;
    animation: cn-spin 0.8s linear infinite;
}
@keyframes cn-spin {
    to { transform: rotate(360deg); }
}

/* Detail content */
.cn-detail-content {
    display: flex;
    flex-direction: column;
}

/* Detail header */
.cn-detail-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-container, #fff);
}
.cn-detail-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
    margin-bottom: 4px;
}
.cn-detail-subtitle {
    font-size: 12px;
    color: var(--text-secondary, #64748b);
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}
.cn-detail-subtitle span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Detail summary cards */
.cn-detail-summary {
    display: flex;
    gap: 10px;
    padding: 12px 20px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.cn-summary-chip {
    flex: 1;
    padding: 10px 12px;
    border-radius: 8px;
    background: var(--bg-container, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    text-align: center;
}
.cn-summary-chip-label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary, #94a3b8);
    margin-bottom: 2px;
}
.cn-summary-chip-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
}

/* Detail items table */
.cn-detail-items {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}
.cn-detail-items table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.cn-detail-items thead th {
    position: sticky;
    top: 0;
    background: var(--bg-card, #f8fafc);
    padding: 8px 10px;
    text-align: left;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary, #94a3b8);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.cn-detail-items tbody td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    color: var(--text-primary, #1e293b);
}
.cn-detail-items tbody tr:hover {
    background: rgba(37,99,235,0.03);
}

/* Payments section */
.cn-detail-payments {
    padding: 10px 20px 16px;
    border-top: 1px solid var(--border-color, #e2e8f0);
}
.cn-detail-payments-title {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-secondary, #94a3b8);
    margin-bottom: 6px;
}
.cn-payment-line {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    font-size: 13px;
}
.cn-payment-method {
    color: var(--text-secondary, #64748b);
}
.cn-payment-amount {
    font-weight: 600;
    color: var(--text-primary, #1e293b);
}

/* Empty list */
.cn-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 40px;
    color: var(--text-secondary, #64748b);
    text-align: center;
}
.cn-empty svg { opacity: 0.3; }
</style>

<script>
$(document).ready(function() {
    var ajaxUrl = '<?php echo $ajax_url; ?>';
    var currentSaleId = null;

    // Click on invoice card → load detail
    $('.cn-inv-card').on('click', function() {
        var saleId = $(this).data('sale-id');

        // Highlight active card
        $('.cn-inv-card').removeClass('cn-inv-active');
        $(this).addClass('cn-inv-active');

        // Update input field
        $('#CN_original_invoice').val(saleId);
        currentSaleId = saleId;

        // Show loading
        $('#cn-detail-empty').hide();
        $('#cn-detail-content').hide();
        $('#cn-detail-loading').show();
        $('#cn-select-btn').hide();

        // AJAX call
        $.ajax({
            url: ajaxUrl + '/' + saleId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (!data.success) {
                    $('#cn-detail-loading').hide();
                    $('#cn-detail-empty').show().find('div').html(data.message || 'Erreur');
                    return;
                }
                renderDetail(data);
            },
            error: function() {
                $('#cn-detail-loading').hide();
                $('#cn-detail-empty').show().find('div').html('Erreur de connexion');
            }
        });
    });

    function formatAmount(val) {
        var n = parseFloat(val) || 0;
        return n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' \u20ac';
    }

    function formatDate(dt) {
        if (!dt) return '';
        var d = new Date(dt.replace(' ', 'T'));
        var dd = ('0' + d.getDate()).slice(-2);
        var mm = ('0' + (d.getMonth()+1)).slice(-2);
        var yyyy = d.getFullYear();
        var hh = ('0' + d.getHours()).slice(-2);
        var mi = ('0' + d.getMinutes()).slice(-2);
        return dd + '/' + mm + '/' + yyyy + ' ' + hh + ':' + mi;
    }

    function renderDetail(data) {
        var html = '';

        // Header
        html += '<div class="cn-detail-head">';
        html += '<div class="cn-detail-title">Facture #' + data.sale_id + '</div>';
        html += '<div class="cn-detail-subtitle">';
        html += '<span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ' + formatDate(data.sale_time) + '</span>';
        html += '<span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> ' + (data.employee_name || '-') + '</span>';
        html += '</div></div>';

        // Summary chips
        html += '<div class="cn-detail-summary">';
        html += '<div class="cn-summary-chip"><div class="cn-summary-chip-label">Sous-total HT</div><div class="cn-summary-chip-value">' + formatAmount(data.subtotal) + '</div></div>';
        html += '<div class="cn-summary-chip"><div class="cn-summary-chip-label">TVA</div><div class="cn-summary-chip-value">' + formatAmount(data.overall_tax) + '</div></div>';
        html += '<div class="cn-summary-chip"><div class="cn-summary-chip-label">Total TTC</div><div class="cn-summary-chip-value" style="color:var(--accent-blue,#2563eb);">' + formatAmount(data.overall_total) + '</div></div>';
        html += '</div>';

        // Items table
        html += '<div class="cn-detail-items"><table>';
        html += '<thead><tr>';
        html += '<th>R\u00e9f\u00e9rence</th><th>Article</th><th style="text-align:right;">Qt\u00e9</th><th style="text-align:right;">P.U. TTC</th><th style="text-align:right;">Rem.%</th><th style="text-align:right;">Total TTC</th>';
        html += '</tr></thead><tbody>';

        if (data.items && data.items.length > 0) {
            for (var i = 0; i < data.items.length; i++) {
                var it = data.items[i];
                var disc = parseFloat(it.discount_percent) || 0;
                var discStr = disc > 0 ? disc.toFixed(1).replace('.',',') + '%' : '-';
                var discColor = disc > 0 ? 'var(--danger,#ef4444)' : 'inherit';
                html += '<tr>';
                html += '<td style="font-family:monospace;font-size:11px;color:var(--text-secondary,#64748b);">' + (it.line_item_number || '') + '</td>';
                html += '<td style="font-weight:500;">' + (it.line_name || '') + '</td>';
                html += '<td style="text-align:right;font-weight:600;">' + parseFloat(it.quantity_purchased || 0).toFixed(0) + '</td>';
                html += '<td style="text-align:right;color:var(--text-secondary,#64748b);">' + formatAmount(it.item_unit_price) + '</td>';
                html += '<td style="text-align:right;color:' + discColor + ';">' + discStr + '</td>';
                html += '<td style="text-align:right;font-weight:600;">' + formatAmount(it.line_sales) + '</td>';
                html += '</tr>';
            }
        } else {
            html += '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--text-secondary,#94a3b8);">Aucun article</td></tr>';
        }
        html += '</tbody></table></div>';

        // Payments
        if (data.payments && data.payments.length > 0) {
            html += '<div class="cn-detail-payments">';
            html += '<div class="cn-detail-payments-title">Paiements</div>';
            for (var p = 0; p < data.payments.length; p++) {
                var pm = data.payments[p];
                var pmName = pm.payment_type || pm.payment_method_code || 'Paiement';
                html += '<div class="cn-payment-line">';
                html += '<span class="cn-payment-method">' + pmName + '</span>';
                html += '<span class="cn-payment-amount">' + formatAmount(pm.payment_amount) + '</span>';
                html += '</div>';
            }
            html += '</div>';
        }

        // Comment
        if (data.comment && data.comment.trim() !== '' && data.comment !== '0') {
            html += '<div style="padding:8px 20px 14px;font-size:12px;color:var(--text-secondary,#64748b);font-style:italic;border-top:1px solid var(--border-color,#e2e8f0);">' + data.comment + '</div>';
        }

        $('#cn-detail-content').html(html);
        $('#cn-detail-loading').hide();
        $('#cn-detail-content').show();

        // Show select button
        $('#cn-select-btn-text').text('S\u00e9lectionner #' + data.sale_id);
        $('#cn-select-btn').show();
    }

    // Select button → submit form
    $('#cn-select-btn').on('click', function() {
        if (currentSaleId) {
            $('#CN_original_invoice').val(currentSaleId);
            $('#cn_form').submit();
        }
    });
});
</script>
