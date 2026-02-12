<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:600px;">
    <!-- Header -->
    <div class="fenetre-header">
        <span id="page_title" class="fenetre-title">
            Recherche Client
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <!-- Content -->
    <div class="fenetre-content" style="padding:20px;">

        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php echo form_open('customers/search_ext_customers', array('id' => 'search_ext_form')); ?>

        <div class="md-card" style="margin-bottom:16px;">
            <div class="md-card-title" style="font-size:0.95rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Recherche Client
            </div>

            <!-- Ligne 1 : Nom + Prénom -->
            <div style="display:flex;gap:12px;">
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('common_last_name'); ?></label>
                    <?php echo form_input(array(
                        'name'        => 'last_name',
                        'id'          => 'last_name',
                        'class'       => 'md-form-input',
                        'placeholder' => $this->lang->line('common_last_name'),
                        'value'       => $_SESSION['transaction_info']->last_name ?? ''
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('common_first_name'); ?></label>
                    <?php echo form_input(array(
                        'name'        => 'first_name',
                        'id'          => 'first_name',
                        'class'       => 'md-form-input',
                        'placeholder' => $this->lang->line('common_first_name'),
                        'value'       => $_SESSION['transaction_info']->first_name ?? ''
                    )); ?>
                </div>
            </div>

            <!-- Ligne 2 : Email + Téléphone -->
            <div style="display:flex;gap:12px;">
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('common_email'); ?></label>
                    <?php echo form_input(array(
                        'name'        => 'email',
                        'id'          => 'email',
                        'class'       => 'md-form-input',
                        'placeholder' => $this->lang->line('common_email'),
                        'value'       => $_SESSION['transaction_info']->email ?? ''
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1;">
                    <label class="md-form-label"><?php echo $this->lang->line('common_phone_number'); ?></label>
                    <?php echo form_input(array(
                        'name'        => 'phone_number',
                        'id'          => 'phone_number',
                        'class'       => 'md-form-input',
                        'placeholder' => $this->lang->line('common_phone_number'),
                        'value'       => $_SESSION['transaction_info']->phone_number ?? ''
                    )); ?>
                </div>
            </div>

            <!-- Ligne 3 : Boutique -->
            <div class="md-form-group">
                <label class="md-form-label">Boutique</label>
                <?php echo form_dropdown(
                    'branch_ipv4',
                    $_SESSION['G']->branch_description_pick_list,
                    '',
                    'class="md-form-select"'
                ); ?>
            </div>
        </div>

        <!-- Boutons -->
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <?php echo anchor('common_controller/common_exit/', '<span>'.$this->lang->line('common_reset').'</span>', 'class="btretour btlien" style="display:inline-flex;align-items:center;padding:8px 20px;"'); ?>
            <?php echo form_submit(array(
                'name'  => 'submit',
                'id'    => 'submit',
                'value' => $this->lang->line('common_submit'),
                'class' => 'btsubmit'
            )); ?>
        </div>

        <?php echo form_close(); ?>

    </div>
</dialog>

<script type="text/javascript">
$(document).ready(function() {
    $('#last_name').focus();
});
</script>
