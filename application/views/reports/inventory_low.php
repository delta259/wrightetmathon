
<!-- output header -->
<?php $this->load->view("partial/header"); ?>

<!-- Page header -->
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;margin-bottom:12px;">
    <div class="page-title" style="display:flex;align-items:center;gap:10px;font-size:1.15em;font-weight:700;color:var(--text-primary,#1e293b);">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <?php echo $this->lang->line('reports_low_inventory'); ?>
    </div>
    <div class="page-actions" style="display:flex;align-items:center;gap:10px;">
        <?php include('../wrightetmathon/application/views/partial/show_buttons.php'); ?>
    </div>
</div>

<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>
<?php if (isset($error)) { echo "<div class='error_message' style='padding:8px;margin-bottom:10px;border-radius:6px;text-align:center;'>".$error."</div>"; } ?>

<?php echo form_open("reports/inventory_low_validation"); ?>

<div style="max-width:500px;">
    <div class="md-card" style="margin-bottom:12px;">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Options
        </div>
        <div class="md-form-group" style="margin-bottom:10px;">
            <label class="md-form-label"><?php echo $this->lang->line('reports_create_PO'); ?></label>
            <?php echo form_dropdown('create_po', $_SESSION['G']->YorN_pick_list, '', 'class="md-form-select"'); ?>
        </div>
        <div class="md-form-group">
            <label class="md-form-label"><?php echo $this->lang->line('recvs_supplier'); ?></label>
            <?php echo form_dropdown('supplier_id', $_SESSION['G']->supplier_pick_list, $this->config->item('default_supplier_id'), 'class="md-form-select"'); ?>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;">
        <?php echo form_submit(array(
            'name'=>'generate_report','id'=>'generate_report',
            'value'=>$this->lang->line('common_run_report'),
            'class'=>'btsubmit sablier'
        )); ?>
    </div>
</div>

<?php echo form_close(); ?>

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
    $("#generate_report").click(function() { $('#spinner_on_bar').show(); });
});
</script>
