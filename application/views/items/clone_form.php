<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:540px;">
    <!-- Header -->
    <div class="fenetre-header">
        <span id="page_title" class="fenetre-title">
            <?php include('../wrightetmathon/application/views/partial/show_title.php'); ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <!-- Content -->
    <div class="fenetre-content" style="padding:20px;">

        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php echo form_open($_SESSION['controller_name'].'/view/-1', array('id'=>'clone_form')); ?>

        <div class="md-card" style="margin-bottom:16px;">
            <div class="md-card-title" style="font-size:0.95rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                <?php echo $this->lang->line('items_clone'); ?>
            </div>

            <!-- Article source -->
            <div class="md-form-group">
                <label class="md-form-label"><?php echo $this->lang->line('items_clone_from_item'); ?></label>
                <?php echo form_input(array(
                    'name'        => 'clone_from_id',
                    'id'          => 'clone_from_id',
                    'class'       => 'md-form-input',
                    'placeholder' => $this->lang->line('items_clone_from_item'),
                    'value'       => $_SESSION['transaction_info']->clone_from_id ?? '',
                    'required'    => 'required'
                )); ?>
            </div>

            <!-- Article destination -->
            <div class="md-form-group">
                <label class="md-form-label"><?php echo $this->lang->line('items_clone_to_item'); ?></label>
                <?php echo form_input(array(
                    'name'        => 'clone_to_id',
                    'id'          => 'clone_to_id',
                    'class'       => 'md-form-input',
                    'placeholder' => $this->lang->line('items_clone_to_item'),
                    'value'       => $_SESSION['transaction_info']->clone_to_id ?? '',
                    'required'    => 'required'
                )); ?>
            </div>

            <!-- Note explicative -->
            <div style="padding:10px 14px;background:var(--info-bg, #eff6ff);border:1px solid var(--info, #3b82f6);border-radius:6px;font-size:0.8rem;color:var(--text-secondary, #64748b);line-height:1.5;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <?php echo $this->lang->line('items_clone_notes'); ?>
            </div>
        </div>

        <!-- Boutons -->
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <?php echo anchor('common_controller/common_exit/', '<span>'.$this->lang->line('common_reset').'</span>', 'class="btretour btlien" style="display:inline-flex;align-items:center;padding:8px 20px;"'); ?>
            <?php echo form_submit(array(
                'name'  => 'submit',
                'id'    => 'submit',
                'value' => $this->lang->line('items_clone'),
                'class' => 'btsubmit'
            )); ?>
        </div>

        <?php echo form_close(); ?>

    </div>
</dialog>

<script type="text/javascript">
$(document).ready(function() {
    // Focus sur le premier champ vide
    var $from = $('#clone_from_id'), $to = $('#clone_to_id');
    if ($from.val() !== '') {
        $to.focus();
    } else {
        $from.focus();
    }
});
</script>
