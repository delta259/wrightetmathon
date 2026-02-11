<?php $this->load->view("partial/header"); ?>

<!-- Messages -->
<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<!-- Action buttons -->
<div style="margin-bottom: 12px;">
    <?php include('../wrightetmathon/application/views/partial/show_buttons.php'); ?>
</div>

<?php if (isset($error)): ?>
<div class="rpi-error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Report parameter card -->
<div class="rpi-card">

    <!-- Header -->
    <div class="rpi-header">
        <div class="rpi-header-left">
            <div class="rpi-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div>
                <div class="rpi-title">Ticket Z</div>
                <div class="rpi-subtitle"><?php echo $this->lang->line('reports_report_input') ?: 'Paramètres du rapport'; ?></div>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div class="rpi-body">

        <!-- Date range section -->
        <div class="rpi-section">
            <div class="rpi-section-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo $this->lang->line('reports_date_range'); ?>
            </div>

            <!-- Simple date range -->
            <div class="rpi-radio-row" id="rpi_simple_row">
                <input type="radio" name="report_type" id="simple_radio" value="simple" checked="checked" class="rpi-radio"/>
                <span class="rpi-radio-label"><?php echo $this->lang->line('reports_date_range_simple') ?: 'Période prédéfinie'; ?></span>
                <?php echo form_dropdown('report_date_range_simple', $report_date_range_simple, '', 'id="report_date_range_simple" class="rpi-select rpi-select-wide"'); ?>
            </div>

            <!-- Complex date range -->
            <div class="rpi-radio-row" id="rpi_complex_row">
                <input type="radio" name="report_type" id="complex_radio" value="complex" class="rpi-radio"/>
                <span class="rpi-radio-label"><?php echo $this->lang->line('reports_date_range_complex') ?: 'Dates personnalisées'; ?></span>
                <div class="rpi-date-group">
                    <?php echo form_dropdown('start_month', $months, $selected_month, 'id="start_month" class="rpi-select"'); ?>
                    <?php echo form_dropdown('start_day', $days, $selected_day, 'id="start_day" class="rpi-select rpi-select-sm"'); ?>
                    <?php echo form_dropdown('start_year', $years, $selected_year, 'id="start_year" class="rpi-select"'); ?>
                    <span class="rpi-date-arrow">&rarr;</span>
                    <?php echo form_dropdown('end_month', $months, $selected_month, 'id="end_month" class="rpi-select"'); ?>
                    <?php echo form_dropdown('end_day', $days, $selected_day, 'id="end_day" class="rpi-select rpi-select-sm"'); ?>
                    <?php echo form_dropdown('end_year', $years, $selected_year, 'id="end_year" class="rpi-select"'); ?>
                </div>
            </div>
        </div>

        <!-- Transaction subtype -->
        <div class="rpi-section">
            <div class="rpi-section-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                <?php echo $this->lang->line('reports_transaction_subtype'); ?>
            </div>
            <?php echo form_dropdown('transaction_subtype', $options, 'all', 'id="transaction_subtype" class="rpi-select rpi-select-full"'); ?>
        </div>

        <!-- Sort by -->
        <div class="rpi-section">
            <div class="rpi-section-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h12M3 18h6"/></svg>
                <?php echo $this->lang->line('reports_transaction_sortby'); ?>
            </div>
            <?php echo form_dropdown('transaction_sortby', $sortby, 'all', 'id="transaction_sortby" class="rpi-select rpi-select-full"'); ?>
        </div>

        <!-- Excel export -->
        <?php if ($export_to_excel_allowed == 'yes'): ?>
        <div class="rpi-section">
            <div class="rpi-section-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/></svg>
                <?php echo $this->lang->line('reports_export_to_excel'); ?>
            </div>
            <div class="rpi-toggle-row">
                <label class="rpi-toggle-option">
                    <input type="radio" name="export_excel" id="export_excel_no" value="0" checked="checked" class="rpi-radio"/>
                    <span class="rpi-toggle-chip"><?php echo $this->lang->line('common_no'); ?></span>
                </label>
                <label class="rpi-toggle-option">
                    <input type="radio" name="export_excel" id="export_excel_yes" value="1" class="rpi-radio"/>
                    <span class="rpi-toggle-chip rpi-toggle-chip-excel">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <?php echo $this->lang->line('common_yes'); ?>
                    </span>
                </label>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <div class="rpi-footer">
        <button type="button" id="generate_report" class="rpi-btn-submit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            <?php echo $this->lang->line('common_run_report'); ?>
        </button>
    </div>

</div><!-- end .rpi-card -->

<!-- Spinner overlay -->
<div class="rpi-spinner-overlay" id="rpi_spinner" style="display:none;">
    <div class="rpi-spinner-content">
        <div class="rpi-spinner-ring"></div>
        <div class="rpi-spinner-text">Chargement du rapport...</div>
    </div>
</div>

<style>
/* ===== Report Parameter Input (rpi-) ===== */
.rpi-card {
    background: var(--bg-container, #fff);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
    overflow: hidden;
    margin: 10px auto 20px;
    max-width: 720px;
}

/* Header */
.rpi-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc);
}
.rpi-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.rpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.rpi-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
    line-height: 1.3;
}
.rpi-subtitle {
    font-size: 12px;
    color: var(--text-secondary, #64748b);
    margin-top: 2px;
}

/* Body */
.rpi-body {
    padding: 24px;
}

/* Sections */
.rpi-section {
    margin-bottom: 20px;
}
.rpi-section:last-child {
    margin-bottom: 0;
}
.rpi-section-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-secondary, #64748b);
    margin-bottom: 8px;
}

/* Radio rows */
.rpi-radio-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 6px;
    cursor: pointer;
    transition: background 0.15s;
    flex-wrap: wrap;
}
.rpi-radio-row:hover {
    background: var(--bg-card, #f8fafc);
}
.rpi-radio {
    accent-color: var(--primary, #2563eb);
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
.rpi-radio-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary, #1e293b);
    min-width: 140px;
}

/* Select inputs */
.rpi-select {
    padding: 7px 10px;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px;
    font-size: 13px;
    color: var(--text-primary, #1e293b);
    background: var(--bg-container, #fff);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    cursor: pointer;
}
.rpi-select:focus {
    border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 2px rgba(37,99,235,0.12);
}
.rpi-select-wide {
    min-width: 200px;
}
.rpi-select-full {
    width: 100%;
}
.rpi-select-sm {
    min-width: 60px;
}

/* Date group */
.rpi-date-group {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
    width: 100%;
    padding-left: 24px;
    margin-top: 6px;
}
.rpi-date-arrow {
    color: var(--text-secondary, #94a3b8);
    font-size: 14px;
    font-weight: 600;
    margin: 0 4px;
}

/* Toggle row (yes/no) */
.rpi-toggle-row {
    display: flex;
    gap: 8px;
}
.rpi-toggle-option {
    cursor: pointer;
}
.rpi-toggle-option input {
    display: none;
}
.rpi-toggle-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid var(--border-color, #e2e8f0);
    color: var(--text-secondary, #64748b);
    background: var(--bg-container, #fff);
    transition: all 0.15s;
}
.rpi-toggle-option input:checked + .rpi-toggle-chip {
    background: var(--primary, #2563eb);
    color: #fff;
    border-color: var(--primary, #2563eb);
}
.rpi-toggle-option input:checked + .rpi-toggle-chip-excel {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border-color: #22c55e;
}

/* Footer */
.rpi-footer {
    display: flex;
    justify-content: flex-end;
    padding: 16px 24px;
    border-top: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc);
}
.rpi-btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 28px;
    border-radius: 8px;
    border: none;
    background: linear-gradient(135deg, var(--primary, #2563eb), var(--secondary, #8b5cf6));
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(37,99,235,0.25);
}
.rpi-btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(37,99,235,0.35);
}
.rpi-btn-submit:active {
    transform: translateY(0);
}

/* Error */
.rpi-error {
    background: rgba(239,68,68,0.08);
    border: 1px solid rgba(239,68,68,0.3);
    border-radius: 8px;
    padding: 12px 16px;
    color: var(--danger, #ef4444);
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 12px;
    max-width: 720px;
    margin-left: auto;
    margin-right: auto;
}

/* Spinner overlay */
.rpi-spinner-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0,0,0,0.35);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
}
.rpi-spinner-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}
.rpi-spinner-ring {
    width: 44px;
    height: 44px;
    border: 4px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: rpi-spin 0.8s linear infinite;
}
@keyframes rpi-spin {
    to { transform: rotate(360deg); }
}
.rpi-spinner-text {
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    text-shadow: 0 1px 4px rgba(0,0,0,0.3);
}

/* Dark mode */
[data-theme="dark"] .rpi-card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
[data-theme="dark"] .rpi-select {
    background: var(--bg-card, #334155);
    border-color: var(--border-color, #475569);
    color: var(--text-primary, #f1f5f9);
}
[data-theme="dark"] .rpi-toggle-chip {
    background: var(--bg-card, #334155);
    border-color: var(--border-color, #475569);
    color: var(--text-secondary, #94a3b8);
}
[data-theme="dark"] .rpi-toggle-option input:checked + .rpi-toggle-chip {
    background: var(--primary, #3b82f6);
    border-color: var(--primary, #3b82f6);
    color: #fff;
}
</style>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
$(document).ready(function() {

    // Generate report
    $("#generate_report").click(function() {
        $('#rpi_spinner').show();
        $('#spinner_on_bar').show();

        var transaction_subtype = $("#transaction_subtype").val();
        var transaction_sortby = $("#transaction_sortby").val();

        var export_excel = 0;
        if ($("#export_excel_yes").prop('checked')) {
            export_excel = 1;
        }

        if ($("#simple_radio").prop('checked')) {
            var dateRange = $("#report_date_range_simple").val();
            window.location = window.location + '/' + dateRange + '/' + transaction_subtype + '/' + transaction_sortby + '/' + export_excel;
        } else {
            var start_date = $("#start_year").val() + '-' + $("#start_month").val() + '-' + $('#start_day').val();
            var end_date = $("#end_year").val() + '-' + $("#end_month").val() + '-' + $('#end_day').val();
            window.location = window.location + '/' + start_date + '/' + end_date + '/' + transaction_subtype + '/' + transaction_sortby + '/' + export_excel;
        }
    });

    // Auto-select radio on interaction
    $("#start_month, #start_day, #start_year, #end_month, #end_day, #end_year").on('click focus', function() {
        $("#complex_radio").prop('checked', true);
    });

    $("#report_date_range_simple").on('click focus', function() {
        $("#simple_radio").prop('checked', true);
    });

    // Click on radio label row selects the radio
    $("#rpi_simple_row .rpi-radio-label, #rpi_simple_row .rpi-radio").on('click', function() {
        $("#simple_radio").prop('checked', true);
    });
    $("#rpi_complex_row .rpi-radio-label, #rpi_complex_row .rpi-radio").on('click', function() {
        $("#complex_radio").prop('checked', true);
    });
});
</script>
