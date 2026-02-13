<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:700px;">

    <!-- Header fenetre -->
    <div class="fenetre-header">
        <span id="page_title" class="fenetre-title">
            <?php include('../wrightetmathon/application/views/partial/show_title.php'); ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <!-- Content -->
    <div class="fenetre-content" style="padding:16px 20px;">

        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php if (($_SESSION['del'] ?? 0) != 1) { ?>
        <?php if (($_SESSION['undel'] ?? 0) != 1) {
            echo form_open($_SESSION['controller_name'].'/save/', array('id'=>'item_form'));
        ?>

        <div class="md-card" style="margin-bottom:12px;">
            <div class="md-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                Identification
            </div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:0 0 140px;">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_code'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_code','id'=>'warehouse_code',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_code'),
                        'value'=>$_SESSION['transaction_info']->warehouse_code
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_description'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_description','id'=>'warehouse_description',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_description'),
                        'value'=>$_SESSION['transaction_info']->warehouse_description
                    )); ?>
                </div>
            </div>
        </div>

        <div class="md-card" style="margin-bottom:12px;">
            <div class="md-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Emplacements
            </div>

            <!-- Row / Section labels -->
            <div style="display:flex;gap:16px;margin-bottom:4px;">
                <div style="flex:1;font-size:0.8em;font-weight:600;color:var(--text-secondary,#64748b);text-transform:uppercase;letter-spacing:0.03em;">
                    <?php echo $this->lang->line('warehouses_warehouse_row_start').' / '.$this->lang->line('warehouses_warehouse_row_end'); ?>
                </div>
                <div style="flex:1;font-size:0.8em;font-weight:600;color:var(--text-secondary,#64748b);text-transform:uppercase;letter-spacing:0.03em;">
                    <?php echo $this->lang->line('warehouses_warehouse_section_start').' / '.$this->lang->line('warehouses_warehouse_section_end'); ?>
                </div>
            </div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_row_start'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_row_start','id'=>'warehouse_row_start',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_row_start'),
                        'value'=>$_SESSION['transaction_info']->warehouse_row_start
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_row_end'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_row_end','id'=>'warehouse_row_end',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_row_end'),
                        'value'=>$_SESSION['transaction_info']->warehouse_row_end
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_section_start'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_section_start','id'=>'warehouse_section_start',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_section_start'),
                        'value'=>$_SESSION['transaction_info']->warehouse_section_start
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_section_end'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_section_end','id'=>'warehouse_section_end',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_section_end'),
                        'value'=>$_SESSION['transaction_info']->warehouse_section_end
                    )); ?>
                </div>
            </div>

            <div style="display:flex;gap:16px;margin-bottom:4px;margin-top:8px;">
                <div style="flex:1;font-size:0.8em;font-weight:600;color:var(--text-secondary,#64748b);text-transform:uppercase;letter-spacing:0.03em;">
                    <?php echo $this->lang->line('warehouses_warehouse_shelf_start').' / '.$this->lang->line('warehouses_warehouse_shelf_end'); ?>
                </div>
                <div style="flex:1;font-size:0.8em;font-weight:600;color:var(--text-secondary,#64748b);text-transform:uppercase;letter-spacing:0.03em;">
                    <?php echo $this->lang->line('warehouses_warehouse_bin_start').' / '.$this->lang->line('warehouses_warehouse_bin_end'); ?>
                </div>
            </div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_shelf_start'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_shelf_start','id'=>'warehouse_shelf_start',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_shelf_start'),
                        'value'=>$_SESSION['transaction_info']->warehouse_shelf_start
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_shelf_end'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_shelf_end','id'=>'warehouse_shelf_end',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_shelf_end'),
                        'value'=>$_SESSION['transaction_info']->warehouse_shelf_end
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_bin_start'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_bin_start','id'=>'warehouse_bin_start',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_bin_start'),
                        'value'=>$_SESSION['transaction_info']->warehouse_shelf_start
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('warehouses_warehouse_bin_end'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'warehouse_bin_end','id'=>'warehouse_bin_end',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('warehouses_warehouse_bin_end'),
                        'value'=>$_SESSION['transaction_info']->warehouse_shelf_end
                    )); ?>
                </div>
            </div>
        </div>

        <div id="required_fields_message" class="obligatoire" style="margin-bottom:10px;">
            <?php echo $this->lang->line('common_fields_required_message'); ?>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <?php echo anchor('common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>','target="_self"'); ?>
            <?php echo form_submit(array('name'=>'submit','id'=>'submit','value'=>$this->lang->line('common_submit'),'class'=>'btsubmit')); ?>
        </div>

        <?php echo form_close(); } ?>
        <?php } ?>

    </div>
</dialog>

<script>$(document).ready(function(){ $('#warehouse_code').focus(); });</script>
