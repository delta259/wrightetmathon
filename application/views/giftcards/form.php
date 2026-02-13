<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:500px;">

    <!-- Header fenetre -->
    <div class="fenetre-header">
        <span id="page_title" class="fenetre-title">
            <?php echo $_SESSION['$title'] ?? $this->lang->line('giftcards_new'); ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <!-- Content -->
    <div class="fenetre-content" style="padding:16px 20px;">

        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php
        $gc_info = $_SESSION['giftcard_info'] ?? new stdClass();
        echo form_open('giftcards/save/'.($gc_info->giftcard_id ?? -1), array('id'=>'giftcard_form'));
        ?>

        <div class="md-card" style="margin-bottom:12px;">
            <div class="md-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                <?php echo $this->lang->line('giftcards_basic_information'); ?>
            </div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('giftcards_giftcard_number'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'giftcard_number','id'=>'giftcard_number',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('giftcards_giftcard_number'),
                        'value'=>$gc_info->giftcard_number ?? ''
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('giftcards_card_value'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'value','id'=>'value',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('giftcards_card_value'),
                        'value'=>$gc_info->value ?? ''
                    )); ?>
                </div>
            </div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:0 0 200px;">
                    <label class="md-form-label required"><?php echo $this->lang->line('giftcards_sale_date'); ?> (JJ/MM/AAAA)</label>
                    <?php echo form_input(array(
                        'name'=>'sale_date','id'=>'sale_date',
                        'class'=>'md-form-input required',
                        'placeholder'=>'JJ/MM/AAAA',
                        'value'=>$gc_info->sale_date ?? ''
                    )); ?>
                </div>
            </div>
        </div>

        <div id="required_fields_message" class="obligatoire" style="margin-bottom:10px;">
            <?php echo $this->lang->line('common_fields_required_message'); ?>
            <br><?php echo $this->lang->line('giftcards_strict'); ?>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <?php echo anchor('common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>','target="_self"'); ?>
            <?php echo form_submit(array('name'=>'submit','id'=>'submit','value'=>$this->lang->line('common_submit'),'class'=>'btsubmit')); ?>
        </div>

        <?php echo form_close(); ?>

    </div>
</dialog>

<script>
$(document).ready(function() {
    $('#giftcard_number').focus();
});
</script>
