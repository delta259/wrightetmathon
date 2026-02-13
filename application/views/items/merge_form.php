<?php $this->load->view("partial/header_popup"); ?>

<dialog open class="fenetre modale cadre" style="width:800px;">

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

        <?php echo form_open($_SESSION['controller_name'].'/merge_do/'); ?>

        <?php switch ($_SESSION['merge_ok']) { case 1: ?>

        <!-- Confirmation step -->
        <div class="md-card" style="margin-bottom:12px;">
            <div class="md-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>
                Confirmation fusion
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="font-size:0.75em;font-weight:600;text-transform:uppercase;color:#fff;background:#4386a1cc;padding:6px 10px;text-align:center;"><?php echo $this->lang->line('items_merge_from_item'); ?></th>
                        <th style="width:30px;background:#4386a1cc;"></th>
                        <th style="font-size:0.75em;font-weight:600;text-transform:uppercase;color:#fff;background:#4386a1cc;padding:6px 10px;text-align:center;"><?php echo $this->lang->line('items_merge_to_item'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding:6px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);"><?php echo $_SESSION['transaction_from']->item_number; ?></td>
                        <td style="text-align:center;border-bottom:1px solid var(--border-color,#e2e8f0);color:var(--text-secondary,#64748b);">&rarr;</td>
                        <td style="padding:6px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);"><?php echo $_SESSION['transaction_to']->item_number; ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);"><?php echo $_SESSION['transaction_from']->name; ?></td>
                        <td style="text-align:center;border-bottom:1px solid var(--border-color,#e2e8f0);color:var(--text-secondary,#64748b);">&rarr;</td>
                        <td style="padding:6px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);"><?php echo $_SESSION['transaction_to']->name; ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 10px;"><?php echo $_SESSION['transaction_from']->category; ?></td>
                        <td style="text-align:center;color:var(--text-secondary,#64748b);">&rarr;</td>
                        <td style="padding:6px 10px;"><?php echo $_SESSION['transaction_to']->category; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <?php echo anchor('common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>','target="_self"'); ?>
            <?php
            $_SESSION['merge_ok'] = 2;
            echo form_submit(array('name'=>'submit','id'=>'submit','value'=>$this->lang->line('common_confirm'),'class'=>'btsubmit'));
            ?>
        </div>

        <?php break; default: ?>

        <!-- Input step -->
        <div class="md-card" style="margin-bottom:12px;">
            <div class="md-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>
                Fusion d'articles
            </div>
            <div class="md-form-row">
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('items_merge_from_item'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'merge_from_id','id'=>'merge_from_id',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('items_merge_from_item'),
                        'value'=>$_SESSION['transaction_info']->merge_from_id
                    )); ?>
                </div>
                <div class="md-form-group" style="flex:1">
                    <label class="md-form-label required"><?php echo $this->lang->line('items_merge_to_item'); ?></label>
                    <?php echo form_input(array(
                        'name'=>'merge_to_id','id'=>'merge_to_id',
                        'class'=>'md-form-input required',
                        'placeholder'=>$this->lang->line('items_merge_to_item'),
                        'value'=>$_SESSION['transaction_info']->merge_to_id
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

        <?php break; } ?>

        <?php echo form_close(); ?>

    </div>
</dialog>
