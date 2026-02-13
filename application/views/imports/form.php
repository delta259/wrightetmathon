<?php $this->load->view("partial/header_popup"); ?>

<div open class="fenetre modale modal-md">
    <!-- Header -->
    <div class="fenetre-header">
        <span class="fenetre-title">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m14-7l-5-5-5 5m5-5v12"/>
            </svg>
            <?php echo $_SESSION['$title'] . ' ' . $this->lang->line('modules_imports'); ?>
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
                    echo form_open('imports/delete/'.$_SESSION['transaction_id'], array('id'=>'column_delete_form', 'style'=>'display:inline'));
                    echo form_submit(array(
                        'name' => 'delete',
                        'id' => 'delete',
                        'value' => $this->lang->line('imports_delete'),
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
                <legend><?php echo $this->lang->line('imports_column_info'); ?></legend>

                <div class="form-grid">
                    <!-- Row 1: Letter & Label -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 0 0 100px;">
                            <label class="form-label required"><?php echo $this->lang->line('imports_column_letter'); ?></label>
                            <?php echo form_input(array(
                                'name' => 'column_letter',
                                'id' => 'column_letter',
                                'class' => 'form-control',
                                'placeholder' => 'A, B, C...',
                                'value' => $_SESSION['transaction_info']->column_letter,
                                'maxlength' => 3
                            )); ?>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('imports_column_label'); ?></label>
                            <?php echo form_input(array(
                                'name' => 'column_label',
                                'id' => 'column_label',
                                'class' => 'form-control',
                                'placeholder' => 'Libellé de la colonne',
                                'value' => $_SESSION['transaction_info']->column_label
                            )); ?>
                        </div>
                    </div>

                    <!-- Row 2: Number & Data Type -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 0 0 100px;">
                            <label class="form-label required"><?php echo $this->lang->line('imports_column_number'); ?></label>
                            <?php echo form_input(array(
                                'name' => 'column_number',
                                'id' => 'column_number',
                                'type' => 'number',
                                'class' => 'form-control',
                                'placeholder' => '1, 2, 3...',
                                'value' => $_SESSION['transaction_info']->column_number
                            )); ?>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('imports_column_data_type'); ?></label>
                            <?php echo form_dropdown(
                                'column_data_type',
                                $_SESSION['C']->data_type_pick_list,
                                $_SESSION['transaction_info']->column_data_type,
                                'class="form-control"'
                            ); ?>
                        </div>
                    </div>

                    <!-- Row 3: Database Field -->
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label required"><?php echo $this->lang->line('imports_column_database_field_name'); ?></label>
                            <?php echo form_dropdown(
                                'column_database_field_name',
                                $_SESSION['C']->column_database_field_name_pick_list,
                                array_search($_SESSION['transaction_info']->column_database_field_name, $_SESSION['C']->column_database_field_name_pick_list),
                                'class="form-control"'
                            ); ?>
                        </div>
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
