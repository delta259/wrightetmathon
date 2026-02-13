<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:900px;">

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

            <?php if (($_SESSION['new'] ?? 0) != 1) { ?>
                <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                    <div>
                    <?php if (($_SESSION['undel'] ?? 0) == 1) {
                        echo form_open($_SESSION['controller_name'].'/undelete/'.$_SESSION['transaction_info']->customer_id, array('id'=>'customer_delete_form'));
                        echo form_submit(array('name'=>'undelete','id'=>'undelete','value'=>$this->lang->line('suppliers_undelete'),'class'=>'btmodification'));
                        echo form_close();
                    } ?>
                    </div>
                    <div>
                    <?php if (($_SESSION['undel'] ?? 0) != 1) {
                        echo form_open($_SESSION['controller_name'].'/delete/'.$_SESSION['transaction_info']->customer_id, array('id'=>'customer_delete_form'));
                        echo form_submit(array('name'=>'delete','id'=>'delete','value'=>$this->lang->line('suppliers_delete'),'class'=>'btmodification'));
                        echo form_close();
                    } ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (($_SESSION['undel'] ?? 0) != 1) {
                echo form_open($_SESSION['controller_name'].'/save/', array('id'=>'item_form'));
            ?>

            <div class="md-card" style="margin-bottom:12px;">
                <div class="md-card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Informations personnelles
                </div>
                <?php include('../wrightetmathon/application/views/people/form_basic_info.php'); ?>
            </div>

            <div class="md-card" style="margin-bottom:12px;">
                <div class="md-card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    Informations fournisseur
                </div>
                <div class="md-form-row">
                    <div class="md-form-group" style="flex:2">
                        <label class="md-form-label required"><?php echo $this->lang->line('suppliers_company_name'); ?></label>
                        <?php echo form_input(array(
                            'name'=>'company_name','id'=>'company_name',
                            'class'=>'md-form-input required',
                            'placeholder'=>$this->lang->line('suppliers_company_name'),
                            'value'=>$_SESSION['transaction_info']->company_name
                        )); ?>
                    </div>
                    <div class="md-form-group" style="flex:1">
                        <label class="md-form-label required"><?php echo $this->lang->line('suppliers_account_number'); ?></label>
                        <?php echo form_input(array(
                            'name'=>'account_number','id'=>'account_number',
                            'class'=>'md-form-input required',
                            'placeholder'=>$this->lang->line('suppliers_account_number'),
                            'value'=>$_SESSION['transaction_info']->account_number
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

<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/my_calendar2.js"></script>
