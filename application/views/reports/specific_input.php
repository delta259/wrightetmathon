<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:700px;">

    <!-- Header fenetre -->
    <div class="fenetre-header">
        <span id="page_title" class="fenetre-title">
            <?php echo $this->lang->line('reports_report_input').' '.$_SESSION['transaction_info']->specific_input_name; ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <!-- Content -->
    <div class="fenetre-content" style="padding:16px 20px;">

        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php echo form_open($_SESSION['controller_name'].'/specific_report/'); ?>

        <div class="md-card" style="margin-bottom:12px;">
            <div class="md-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo $this->lang->line('reports_date_range'); ?>
            </div>
            <div style="display:flex;gap:24px;">
                <!-- Date debut -->
                <div style="flex:1;">
                    <label class="md-form-label" style="margin-bottom:6px;display:block;"><?php echo $this->lang->line('reports_start'); ?></label>
                    <div style="display:flex;gap:6px;">
                        <?php echo form_dropdown('start_day', $_SESSION['transaction_info']->days_pick_list, $_SESSION['transaction_info']->selected_day, 'class="md-form-select" style="flex:1"'); ?>
                        <?php echo form_dropdown('start_month', $_SESSION['transaction_info']->months_pick_list, $_SESSION['transaction_info']->selected_month, 'class="md-form-select" style="flex:1"'); ?>
                        <?php echo form_dropdown('start_year', $_SESSION['transaction_info']->years_pick_list, $_SESSION['transaction_info']->selected_year, 'class="md-form-select" style="flex:1"'); ?>
                    </div>
                </div>
                <!-- Date fin -->
                <div style="flex:1;">
                    <label class="md-form-label" style="margin-bottom:6px;display:block;"><?php echo $this->lang->line('reports_end'); ?></label>
                    <div style="display:flex;gap:6px;">
                        <?php echo form_dropdown('end_day', $_SESSION['transaction_info']->days_pick_list, $_SESSION['transaction_info']->selected_day, 'class="md-form-select" style="flex:1"'); ?>
                        <?php echo form_dropdown('end_month', $_SESSION['transaction_info']->months_pick_list, $_SESSION['transaction_info']->selected_month, 'class="md-form-select" style="flex:1"'); ?>
                        <?php echo form_dropdown('end_year', $_SESSION['transaction_info']->years_pick_list, $_SESSION['transaction_info']->selected_year, 'class="md-form-select" style="flex:1"'); ?>
                    </div>
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
                    <label class="md-form-label"><?php echo $_SESSION['transaction_info']->specific_input_name; ?></label>
                    <?php echo form_dropdown('specific_input_data', $_SESSION['transaction_info']->specific_pick_list, $_SESSION['transaction_info']->selected_specific, 'class="md-form-select"'); ?>
                </div>
            </div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label"><?php echo $this->lang->line('reports_sale_type'); ?></label>
                    <?php echo form_dropdown('transaction_subtype', $_SESSION['transaction_info']->options_pick_list, $_SESSION['transaction_info']->selected_option, 'class="md-form-select"'); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label"><?php echo $this->lang->line('reports_export_to_excel'); ?></label>
                    <?php echo form_dropdown('export_excel', $_SESSION['G']->oneorzero_pick_list, $_SESSION['selected_oneorzero'], 'class="md-form-select"'); ?>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <?php echo anchor('common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>','target="_self"'); ?>
            <?php echo form_submit(array('name'=>'submit','id'=>'submit','value'=>$this->lang->line('common_submit'),'class'=>'btsubmit sablier')); ?>
        </div>

        <?php echo form_close(); ?>
    </div>
</dialog>

<!-- Spinner -->
<div id="spinner" class="spinnerrapport" style="display:none;">
    <div id="floatingCirclesG">
        <div class="f_circleG" id="frotateG_01"></div>
        <div class="f_circleG" id="frotateG_02"></div>
        <div class="f_circleG" id="frotateG_03"></div>
        <div class="f_circleG" id="frotateG_04"></div>
        <div class="f_circleG" id="frotateG_05"></div>
        <div class="f_circleG" id="frotateG_06"></div>
        <div class="f_circleG" id="frotateG_07"></div>
        <div class="f_circleG" id="frotateG_08"></div>
    </div>
</div>
<script>$(document).ready(function(){ $('.sablier').click(function(){ $('#spinner').show(); }); });</script>
