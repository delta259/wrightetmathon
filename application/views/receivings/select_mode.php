<?php $this->load->view("partial/header"); ?>

<!-- Page header -->
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;margin-bottom:12px;">
    <div class="page-title" style="display:flex;align-items:center;gap:10px;font-size:1.15em;font-weight:700;color:var(--text-primary,#1e293b);">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        <?php echo $this->lang->line('recvs_select_mode'); ?>
    </div>
</div>

<div style="max-width:400px;">
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18m-7-7l7 7 7-7"/></svg>
            <?php echo $this->lang->line('recvs_select_mode'); ?>
        </div>
        <div class="md-form-group">
            <label class="md-form-label"><?php echo $this->lang->line('recvs_select_mode'); ?></label>
            <?php
            echo form_open("receivings/change_mode", array('id'=>'mode_form'));
            echo form_dropdown(
                'mode',
                $_SESSION['G']->stock_actions_pick_list,
                $_SESSION['G']->stock_actions_pick_list[0],
                'class="md-form-select" style="font-size:1em;" onchange="$(\'#mode_form\').submit();"'
            );
            echo form_close();
            ?>
        </div>
    </div>
</div>

<?php $this->load->view("partial/pre_footer"); $this->load->view("partial/footer"); ?>
