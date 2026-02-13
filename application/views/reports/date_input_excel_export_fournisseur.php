<?php $this->load->view("partial/header"); ?>

<!-- Page header -->
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;margin-bottom:12px;">
    <div class="page-title" style="display:flex;align-items:center;gap:10px;font-size:1.15em;font-weight:700;color:var(--text-primary,#1e293b);">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <?php echo $this->lang->line('reports_report_input'); ?>
    </div>
    <div class="page-actions" style="display:flex;align-items:center;gap:10px;">
        <?php include('../wrightetmathon/application/views/partial/show_buttons.php'); ?>
    </div>
</div>

<?php if (isset($error)) { echo "<div class='error_message' style='padding:8px;margin-bottom:10px;border-radius:6px;text-align:center;'>".$error."</div>"; } ?>

<form action="<?php echo site_url("reports/rapport_detaille_fournisseur"); ?>" method="post">

<div style="max-width:700px;">
    <div class="md-card" style="margin-bottom:12px;">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?php echo $this->lang->line('reports_date_range'); ?>
        </div>
        <div class="md-form-group" style="margin-bottom:10px;">
            <label class="md-form-label"><?php echo $this->lang->line('reports_date_range'); ?> (simple)</label>
            <div style="display:flex;align-items:center;gap:8px;">
                <input type="radio" name="report_type" id="simple_radio" value="simple" checked="checked"/>
                <?php echo form_dropdown('report_date_range_simple', $report_date_range_simple, '', 'id="report_date_range_simple" class="md-form-select"'); ?>
            </div>
        </div>
        <div class="md-form-group">
            <label class="md-form-label"><?php echo $this->lang->line('reports_date_range'); ?> (personnalis&eacute;)</label>
            <div style="display:flex;align-items:center;gap:8px;">
                <input type="radio" name="report_type" id="complex_radio" value="complex"/>
                <?php echo form_input(array('type'=>'date','name'=>'start','id'=>'start','value'=>date('Y-m-d'),'class'=>'md-form-input','style'=>'flex:1')); ?>
                <span style="color:var(--text-secondary,#64748b);">-</span>
                <?php echo form_input(array('type'=>'date','name'=>'end','id'=>'end','value'=>date('Y-m-d'),'class'=>'md-form-input','style'=>'flex:1')); ?>
            </div>
        </div>
    </div>

    <div class="md-card" style="margin-bottom:12px;">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Filtres
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label"><?php echo $this->lang->line('reports_transaction_subtype'); ?></label>
                <?php echo form_dropdown('transaction_subtype', $options, 'all', 'id="transaction_subtype" class="md-form-select"'); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label"><?php echo $this->lang->line('reports_transaction_sortby'); ?></label>
                <?php echo form_dropdown('transaction_sortby', $_SESSION['G']->supplier_pick_list, 'all', 'id="transaction_sortby" class="md-form-select"'); ?>
            </div>
        </div>
        <?php if ($export_to_excel_allowed == 'yes') { ?>
        <div class="md-form-group" style="margin-top:8px;">
            <label class="md-form-label"><?php echo $this->lang->line('reports_export_to_excel'); ?></label>
            <div style="display:flex;align-items:center;gap:16px;padding:4px 0;">
                <label style="display:flex;align-items:center;gap:4px;cursor:pointer;"><input type="radio" name="export_excel" id="export_excel_yes" value="1"> <?php echo $this->lang->line('common_yes'); ?></label>
                <label style="display:flex;align-items:center;gap:4px;cursor:pointer;"><input type="radio" name="export_excel" id="export_excel_no" value="0" checked="checked"> <?php echo $this->lang->line('common_no'); ?></label>
            </div>
        </div>
        <?php } ?>
    </div>

    <div style="display:flex;justify-content:flex-end;">
        <?php echo form_submit(array('name'=>'generate_report','id'=>'generate_report','value'=>$this->lang->line('common_run_report'),'class'=>'btsubmit sablier')); ?>
    </div>
</div>

</form>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<!-- Spinner -->
<div id="spinner" class="spinnerrapport" style="display:none;">
    <div id="floatingCirclesG">
        <div class="f_circleG" id="frotateG_01"></div><div class="f_circleG" id="frotateG_02"></div>
        <div class="f_circleG" id="frotateG_03"></div><div class="f_circleG" id="frotateG_04"></div>
        <div class="f_circleG" id="frotateG_05"></div><div class="f_circleG" id="frotateG_06"></div>
        <div class="f_circleG" id="frotateG_07"></div><div class="f_circleG" id="frotateG_08"></div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('.sablier').click(function(){ $('#spinner').show(); });

    $("#generate_report").click(function() {
        $('#spinner_on_bar').show();
        var transaction_subtype = $("#transaction_subtype").val();
        var transaction_sortby = $("#transaction_sortby").val();
        var export_excel = 0;
        if ($("#export_excel_yes").attr('checked')) { export_excel = 1; }
        if ($("#simple_radio").attr('checked')) {
            window.location = window.location+'/'+$("#report_date_range_simple option:selected").val()+'/'+transaction_subtype+'/'+transaction_sortby+'/'+export_excel;
        } else {
            var start_date = $("#start_year").val()+'-'+$("#start_month").val()+'-'+$('#start_day').val();
            var end_date = $("#end_year").val()+'-'+$("#end_month").val()+'-'+$('#end_day').val();
            window.location = window.location+'/'+start_date+'/'+end_date+'/'+transaction_subtype+'/'+transaction_sortby+'/'+export_excel;
        }
    });

    $("#start_month, #start_day, #start_year, #end_month, #end_day, #end_year, #start, #end").click(function() {
        $("#complex_radio").attr('checked', 'checked');
    });
    $("#report_date_range_simple").click(function() {
        $("#simple_radio").attr('checked', 'checked');
    });
});
</script>
