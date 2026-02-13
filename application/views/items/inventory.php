
<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:1100px;">

    <div class="fenetre-header">
        <span id="page_title" class="fenetre-title">
            <?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).' '.$_SESSION['$title']; ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <div class="fenetre-content" style="padding:16px 20px;">

        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php include('../wrightetmathon/application/views/items/item_details.php'); ?>

        <?php echo form_open('items/save_inventory/'.$_SESSION['transaction_info']->item_id, array('id'=>'item_form')); ?>

        <div class="md-card" style="margin-bottom:12px;">
            <div class="md-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                <?php echo $this->lang->line('items_add_minus'); ?>
            </div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:0 0 200px;">
                    <label class="md-form-label required"><?php echo $this->lang->line('items_add_minus'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'newquantity','id'=>'newquantity',
                        'type'=>'number',
                        'class'=>'md-form-input required',
                        'value'=>0,
                        'autofocus'=>'autofocus'
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label"><?php echo $this->lang->line('items_inventory_comments'); ?></label>
                    <?php
                    $this->load->helper('date');
                    $now = time();
                    $value = $this->lang->line('reports_rolling').' - '.unix_to_human($now, TRUE, 'eu');
                    echo form_input(array(
                        'name'=>'trans_comment','id'=>'trans_comment',
                        'class'=>'md-form-input',
                        'value'=>$value
                    )); ?>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <?php echo anchor('common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>','target="_self"'); ?>
            <?php echo form_submit(array('name'=>'submit','id'=>'submit','value'=>$this->lang->line('common_submit'),'class'=>'btsubmit')); ?>
        </div>

        <?php echo form_close(); ?>

    </div>
</dialog>
