<?php $this->load->view("partial/header_popup"); ?>

<div open class="fenetre modale modal-md">
    <!-- Header -->
    <div class="fenetre-header">
        <span class="fenetre-title">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
            <?php echo $_SESSION['$title'] . ' ' . $this->lang->line('modules_categories'); ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <!-- Content -->
    <div class="fenetre-content">
        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php if (($_SESSION['del'] ?? 0) != 1): ?>

            <?php if (($_SESSION['undel'] ?? 0) == 1): ?>
                <div style="text-align: left; margin-bottom: 16px;">
                    <?php
                    echo form_open('categories/undelete/'.$_SESSION['transaction_info']->category_id, array('id'=>'category_undelete_form', 'style'=>'display:inline'));
                    echo form_submit(array(
                        'name' => 'undelete',
                        'id' => 'undelete',
                        'value' => $this->lang->line('categories_undelete'),
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
                <legend><?php echo $this->lang->line('categories_category_info'); ?></legend>

                <div class="form-grid">
                    <?php if (($_SESSION['new'] ?? 0) == 1): ?>
                    <!-- Row: Category Name (only for new) -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('categories_category_name'); ?></label>
                            <?php echo form_input(array(
                                'name' => 'category_name',
                                'id' => 'category_name',
                                'class' => 'form-control',
                                'placeholder' => $this->lang->line('categories_category_name'),
                                'value' => $_SESSION['transaction_info']->category_name ?? ''
                            )); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Row: Pack Size & Min Order Qty -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('categories_pack_size'); ?></label>
                            <?php echo form_input(array(
                                'name' => 'category_pack_size',
                                'id' => 'category_pack_size',
                                'type' => 'number',
                                'class' => 'form-control',
                                'style' => 'text-align: right;',
                                'value' => $_SESSION['transaction_info']->category_pack_size ?? ''
                            )); ?>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('categories_min_order_qty'); ?></label>
                            <?php echo form_input(array(
                                'name' => 'category_min_order_qty',
                                'id' => 'category_min_order_qty',
                                'type' => 'number',
                                'class' => 'form-control',
                                'style' => 'text-align: right;',
                                'value' => $_SESSION['transaction_info']->category_min_order_qty ?? ''
                            )); ?>
                        </div>
                    </div>

                    <!-- Row: Description -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label"><?php echo $this->lang->line('categories_category_desc'); ?></label>
                            <?php echo form_textarea(array(
                                'name' => 'category_desc',
                                'id' => 'category_desc',
                                'class' => 'form-control',
                                'rows' => 3,
                                'placeholder' => $this->lang->line('categories_category_desc'),
                                'value' => $_SESSION['transaction_info']->category_desc ?? ''
                            )); ?>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="fieldset">
                <legend>Options</legend>

                <div class="form-grid">
                    <!-- Row: Update Sales Price & Defect Indicator -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('categories_update_sales_price'); ?></label>
                            <?php echo form_dropdown(
                                'category_update_sales_price',
                                $_SESSION['G']->YorN_pick_list,
                                $_SESSION['selected_update_sales_price'] ?? 'N',
                                'class="form-control"'
                            ); ?>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('categories_defect_indicator'); ?></label>
                            <?php echo form_dropdown(
                                'category_defect_indicator',
                                $_SESSION['G']->YorN_pick_list,
                                $_SESSION['selected_defect_indicator'] ?? 'N',
                                'class="form-control"'
                            ); ?>
                        </div>
                    </div>

                    <!-- Row: Offer Indicator -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('categories_offer_indicator'); ?></label>
                            <?php echo form_dropdown(
                                'category_offer_indicator',
                                $_SESSION['G']->YorN_pick_list,
                                $_SESSION['selected_offer_indicator'] ?? 'N',
                                'class="form-control"'
                            ); ?>
                        </div>
                        <div class="form-group" style="flex: 1;"></div>
                    </div>
                </div>
            </fieldset>

            <div class="txt_milieu">
                <?php if (($_SESSION['new'] ?? 0) != 1): ?>
                <?php
                echo form_open('categories/delete/'.$_SESSION['transaction_info']->category_id, array('id'=>'category_delete_form', 'style'=>'display:inline'));
                echo form_submit(array(
                    'name' => 'delete',
                    'id' => 'delete',
                    'value' => $this->lang->line('categories_delete'),
                    'class' => 'btmodification'
                ));
                echo form_close();
                ?>
                <?php endif; ?>
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
textarea.form-control {
    resize: vertical;
    min-height: 80px;
}
</style>
