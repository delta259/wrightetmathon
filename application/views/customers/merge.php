<?php $this->load->view("partial/header"); ?>

<!-- Page header -->
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;margin-bottom:12px;">
    <div class="page-title" style="display:flex;align-items:center;gap:10px;font-size:1.15em;font-weight:700;color:var(--text-primary,#1e293b);">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>
        <?php echo $this->lang->line('customers_merge'); ?>
    </div>
</div>

<?php
    $success_or_failure = $this->session->flashdata('success_or_failure');
    $message            = $this->session->flashdata('message');
    $merge_from_client  = $this->session->flashdata('merge_from_client');
    $merge_to_client    = $this->session->flashdata('merge_to_client');

    if ($success_or_failure == 'S') {
        echo "<div class='success_message' style='padding:8px;margin-bottom:10px;border-radius:6px;text-align:center;'>".$message."</div>";
    }
    if ($success_or_failure == 'F') {
        echo "<div class='error_message' style='padding:8px;margin-bottom:10px;border-radius:6px;text-align:center;'>".$message."</div>";
    }
?>

<?php echo form_open('customers/merge_do'); ?>

<div style="max-width:600px;">
    <div class="md-card" style="margin-bottom:12px;">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>
            <?php echo $this->lang->line('customers_merge'); ?>
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('customers_merge_from_client'); ?></label>
                <?php echo form_input(array(
                    'name'=>'merge_from_client','id'=>'merge_from_client',
                    'class'=>'md-form-input required',
                    'style'=>'text-align:center;',
                    'value'=>$merge_from_client
                )); ?>
            </div>
            <div style="display:flex;align-items:flex-end;padding-bottom:8px;color:var(--text-secondary,#64748b);font-size:1.2em;">&rarr;</div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('customers_merge_to_client'); ?></label>
                <?php echo form_input(array(
                    'name'=>'merge_to_client','id'=>'merge_to_client',
                    'class'=>'md-form-input required',
                    'style'=>'text-align:center;',
                    'value'=>$merge_to_client
                )); ?>
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:10px;">
        <?php echo anchor('customer/index', '<div class="btretour btlien">'.$this->lang->line('common_logout').' '.$this->lang->line('customers_merge').'</div>', 'target="_self"'); ?>
        <?php echo form_submit(array(
            'name'=>'submit','id'=>'submit',
            'value'=>$this->lang->line('common_submit'),
            'class'=>'btsubmit'
        )); ?>
    </div>
</div>

<?php echo form_close(); ?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>
