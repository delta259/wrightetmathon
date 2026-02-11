<?php $this->load->view("partial/header_popup"); ?>

<div open class="fenetre modale modal-md">
    <!-- Header -->
    <div class="fenetre-header">
        <span class="fenetre-title">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>
            </svg>
            <?php echo $_SESSION['$title'] . ' ' . $this->lang->line('modules_paymethods'); ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <!-- Content -->
    <div class="fenetre-content">
        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php if (($_SESSION['del'] ?? 0) != 1): ?>

            <?php if (($_SESSION['new'] ?? 0) != 1 && ($_SESSION['undel'] ?? 0) != 1): ?>
                <div style="text-align: right; margin-bottom: 16px;">
                    <?php
                    echo form_open('paymethods/delete/'.$_SESSION['transaction_id'], array('id'=>'paymethod_delete_form', 'style'=>'display:inline'));
                    echo form_submit(array(
                        'name' => 'delete',
                        'id' => 'delete',
                        'value' => $this->lang->line('paymethods_delete'),
                        'class' => 'btmodification'
                    ));
                    echo form_close();
                    ?>
                </div>
            <?php endif; ?>

            <?php if (($_SESSION['undel'] ?? 0) != 1):
                echo form_open($_SESSION['controller_name'].'/save/', array('id'=>'item_form'));
            ?>

            <fieldset class="fieldset">
                <legend><?php echo $this->lang->line('paymethods_info'); ?></legend>

                <div class="form-grid">
                    <!-- Row 1: Code, Description & Order -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 0 0 120px;">
                            <label class="form-label required"><?php echo $this->lang->line('paymethods_paymethod_code'); ?></label>
                            <?php echo form_input(array(
                                'name' => 'payment_method_code',
                                'id' => 'payment_method_code',
                                'class' => 'form-control colorobligatoire',
                                'placeholder' => 'CB, ESP...',
                                'value' => $_SESSION['transaction_info']->payment_method_code,
                                'maxlength' => 10
                            )); ?>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('paymethods_paymethod_description'); ?></label>
                            <?php echo form_input(array(
                                'name' => 'payment_method_description',
                                'id' => 'payment_method_description',
                                'class' => 'form-control colorobligatoire',
                                'placeholder' => 'Carte bancaire, Espèces...',
                                'value' => $_SESSION['transaction_info']->payment_method_description
                            )); ?>
                        </div>
                        <div class="form-group" style="flex: 0 0 80px;">
                            <label class="form-label required"><?php echo $this->lang->line('paymethods_paymethod_display_order'); ?></label>
                            <?php echo form_input(array(
                                'name' => 'payment_method_display_order',
                                'id' => 'payment_method_display_order',
                                'type' => 'number',
                                'class' => 'form-control colorobligatoire',
                                'placeholder' => '1',
                                'value' => $_SESSION['transaction_info']->payment_method_display_order
                            )); ?>
                        </div>
                    </div>

                    <!-- Row 2: Include & Fidelity Flag -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('paymethods_paymethod_include'); ?></label>
                            <?php echo form_dropdown(
                                'payment_method_include',
                                $_SESSION['G']->YorN_pick_list,
                                $_SESSION['transaction_info']->payment_method_include,
                                'class="form-control colorobligatoire"'
                            ); ?>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('paymethods_paymethod_fidelity_flag'); ?></label>
                            <?php echo form_dropdown(
                                'payment_method_fidelity_flag',
                                $_SESSION['G']->YorN_pick_list,
                                $_SESSION['transaction_info']->payment_method_fidelity_flag,
                                'class="form-control colorobligatoire"'
                            ); ?>
                        </div>
                    </div>

                    <!-- Row 3: Giftcard Flag -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('paymethods_paymethod_giftcard_flag'); ?></label>
                            <?php echo form_dropdown(
                                'payment_method_giftcard_flag',
                                $_SESSION['G']->YorN_pick_list,
                                $_SESSION['transaction_info']->payment_method_giftcard_flag,
                                'class="form-control colorobligatoire"'
                            ); ?>
                        </div>
                        <div class="form-group" style="flex: 1;"></div>
                    </div>
                </div>
            </fieldset>

            <div id="required_fields_message" class="obligatoire">
                <?php echo $this->lang->line('common_fields_required_message'); ?>
            </div>

            <div class="txt_milieu">
                <?php echo anchor('common_controller/common_exit/', '<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>', 'target="_self"'); ?>
                <?php echo form_submit(array(
                    'name' => 'submit',
                    'id' => 'submit',
                    'value' => $this->lang->line('common_submit'),
                    'class' => 'btsubmit'
                )); ?>
            </div>

            <?php echo form_close(); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.form-grid { display: flex; flex-direction: column; gap: 16px; }
.form-row { display: flex; gap: 16px; flex-wrap: wrap; }
.form-group { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.form-label { font-size: 0.85rem; font-weight: 500; color: var(--text-secondary, #64748b); }
.form-label.required::after { content: ' *'; color: var(--danger, #ef4444); }
.form-control {
    padding: 10px 12px;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px;
    font-size: 0.9rem;
    background: var(--bg-input, #fff);
    color: var(--text-primary, #1e293b);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus {
    outline: none;
    border-color: var(--primary, #0A6184);
    box-shadow: 0 0 0 3px rgba(10, 97, 132, 0.15);
}
</style>
