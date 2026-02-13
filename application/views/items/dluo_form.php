<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:1000px;">

    <div class="fenetre-header">
        <span id="page_title" class="fenetre-title">
            <?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).'  '.$_SESSION['$title']; ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <div class="fenetre-content" style="padding:16px 20px;">

        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>
        <?php include('../wrightetmathon/application/views/items/item_details.php'); ?>

        <!-- Existing DLUO entries -->
        <div class="md-card" style="margin-bottom:12px;">
            <div class="md-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo $this->lang->line('common_manage').' '.$this->lang->line('items_dluo'); ?>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="font-size:0.75em;font-weight:600;text-transform:uppercase;color:#fff;background:#4386a1cc;padding:6px 10px;text-align:center;"><?php echo $this->lang->line('common_delete_short'); ?></th>
                        <th style="font-size:0.75em;font-weight:600;text-transform:uppercase;color:#fff;background:#4386a1cc;padding:6px 10px;text-align:center;"><?php echo $this->lang->line('common_year'); ?></th>
                        <th style="font-size:0.75em;font-weight:600;text-transform:uppercase;color:#fff;background:#4386a1cc;padding:6px 10px;text-align:center;"><?php echo $this->lang->line('common_month'); ?></th>
                        <th style="font-size:0.75em;font-weight:600;text-transform:uppercase;color:#fff;background:#4386a1cc;padding:6px 10px;text-align:center;"><?php echo $this->lang->line('common_quantity'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['item_info_dluo'] as $row) { ?>
                    <tr>
                        <td style="text-align:center;padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);">
                            <a href="<?php echo site_url('items/dluo_delete/'.$row['year'].'/'.$row['month']); ?>" style="text-decoration:none;">
                                <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                            </a>
                        </td>
                        <td style="text-align:center;padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);"><?php echo $row['year']; ?></td>
                        <td style="text-align:center;padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);"><?php echo $row['month']; ?></td>
                        <td style="text-align:center;padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);">
                            <?php echo form_open('items/dluo_edit/'.$row['year'].'/'.$row['month']);
                            echo number_format($row['dluo_qty'], 0);
                            echo form_close(); ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr style="font-weight:600;">
                        <td colspan="3" style="text-align:center;padding:6px 10px;border-top:2px solid var(--border-color,#e2e8f0);"><?php echo $this->lang->line('common_quantity'); ?></td>
                        <td style="text-align:center;padding:6px 10px;border-top:2px solid var(--border-color,#e2e8f0);"><?php echo number_format($_SESSION['dluo_total_qty'], 0); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add new DLUO -->
        <div class="md-card" style="margin-bottom:12px;">
            <div class="md-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <?php echo $this->lang->line('common_add').' '.$this->lang->line('items_dluo'); ?>
            </div>
            <?php echo form_open('items/dluo_add/'); ?>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('common_year'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'new1_add_year','id'=>'new1_add_year',
                        'class'=>'md-form-input required',
                        'style'=>'text-align:center;',
                        'value'=>$_SESSION['transaction_info']->dluo_year1
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('common_month'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'new1_add_month','id'=>'new1_add_month',
                        'class'=>'md-form-input required',
                        'style'=>'text-align:center;',
                        'value'=>$_SESSION['transaction_info']->dluo_month1
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('common_quantity'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'new1_add_qty','id'=>'new1_add_qty',
                        'class'=>'md-form-input required',
                        'style'=>'text-align:center;',
                        'value'=>$_SESSION['transaction_info']->dluo_qty1
                    )); ?>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:10px;">
                <?php echo anchor('common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>','target="_self"'); ?>
                <?php echo form_submit(array('name'=>'submit','id'=>'submit','value'=>$this->lang->line('common_submit'),'class'=>'btsubmit')); ?>
            </div>
            <?php echo form_close(); ?>
        </div>

    </div>
</dialog>
