<?php $this->load->view("partial/header"); ?>

<!-- Setup the route information -->
<?php
    $route    = $this->session->userdata('route');
    $redirect = $this->Common_routines->determine_route($route);
    $this->session->unset_userdata('route');
?>

<!-- Page header -->
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;margin-bottom:12px;">
    <div class="page-title" style="display:flex;align-items:center;gap:10px;font-size:1.15em;font-weight:700;color:var(--text-primary,#1e293b);">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        <?php
            echo $this->lang->line('reports_edit_transaction');
            echo ' &mdash; ';
            echo $transaction_info['sale_id'];
            echo ' &mdash; ';
            $lang_line = 'reports_'.$transaction_info['mode'];
            echo $this->lang->line($lang_line);
            echo ' &mdash; ';
            echo anchor('sales/receipt/'.$transaction_info['sale_id'], $code.'-'.$transaction_info['sale_id']);
        ?>
    </div>
</div>

<?php echo form_open("sales/save_trans/".$transaction_info['sale_id'], array('id'=>'sales_edit_form')); ?>

<div style="max-width:700px;">
    <div id="required_fields_message" class="obligatoire" style="margin-bottom:8px;">
        <?php echo $this->lang->line('common_fields_required_message'); ?>
    </div>
    <ul id="error_message_box" style="color:#ef4444;margin-bottom:8px;"></ul>

    <div class="md-card" style="margin-bottom:12px;">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?php echo $this->lang->line($lang_line); ?>
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label"><?php echo $this->lang->line('sales_customer'); ?></label>
                <?php echo form_dropdown('customer_id', $customers, $transaction_info['customer_id'], 'id="customer_id" class="md-form-select"'); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label"><?php echo $this->lang->line('sales_employee'); ?></label>
                <?php echo form_dropdown('employee_id', $employees, $transaction_info['employee_id'], 'id="employee_id" class="md-form-select"'); ?>
            </div>
        </div>
        <div class="md-form-group" style="margin-top:8px;">
            <label class="md-form-label"><?php echo $this->lang->line('sales_comment'); ?></label>
            <?php echo form_textarea(array(
                'name'=>'comment','id'=>'comment',
                'value'=>$transaction_info['comment'],
                'rows'=>'4',
                'class'=>'md-form-input',
                'style'=>'resize:vertical;'
            )); ?>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
        <div>
            <?php echo form_open("sales/delete/".$transaction_info['sale_id'], array('id'=>'sales_delete_form','style'=>'display:inline;')); ?>
                <?php echo form_submit(array(
                    'name'=>'submit','id'=>'submit_delete',
                    'value'=>$this->lang->line('sales_delete_entire_sale'),
                    'class'=>'delete_button'
                )); ?>
            <?php echo form_close(); ?>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <?php echo anchor($redirect->route_path, $this->lang->line('common_logout'), 'class="btretour"'); ?>
            <input type="submit" form="sales_edit_form" name="submit" value="<?php echo $this->lang->line('common_submit'); ?>" class="btsubmit" />
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<div id="feedback_bar"></div>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
$(document).ready(function() {
    $('#date').datePicker({startDate: '01/01/1970'});

    $("#sales_delete_form").submit(function() {
        if (!confirm('<?php echo $this->lang->line("sales_delete_confirmation"); ?>')) {
            return false;
        }
    });

    $('#sales_edit_form').validate({
        submitHandler: function(form) {
            $(form).ajaxSubmit({
                success: function(response) {
                    if (response.success) {
                        set_feedback(response.message, 'success_message', false);
                    } else {
                        set_feedback(response.message, 'error_message', true);
                    }
                },
                dataType: 'json'
            });
        },
        errorLabelContainer: "#error_message_box",
        wrapper: "li",
        rules: {},
        messages: {}
    });
});
</script>
