<?php $this->load->view("partial/header_popup"); ?>

<div open class="fenetre modale modal-sm" style="position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); z-index:101;">
    <div class="fenetre-header">
        <span class="fenetre-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="12" y1="11" x2="12" y2="14"/><line x1="9" y1="18" x2="15" y2="18"/></svg>
            Connexion vendeur
        </span>
        <?php
        $target = 'target="_self"';
        echo anchor(
            'common_controller/common_exit/',
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
            $target
        );
        ?>
    </div>
    <div class="fenetre-content">
        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php echo form_open("sales/register_vendeur"); ?>

        <div class="vendor-login-form">
            <div class="vendor-login-field">
                <label for="pseudo"><?php echo $this->lang->line('sales_user'); ?></label>
                <input type="text" class="colorobligatoire" name="pseudo" id="pseudo" placeholder="<?php echo $this->lang->line('sales_user'); ?>" autofocus>
            </div>

            <div class="vendor-login-field">
                <label for="password"><?php echo $this->lang->line('sales_password'); ?></label>
                <input type="password" class="colorobligatoire" name="password" id="password" placeholder="<?php echo $this->lang->line('sales_password'); ?>">
            </div>
        </div>

        <div class="txt_milieu">
            <?php
            echo anchor(
                'common_controller/common_exit/',
                '<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>',
                'target="_self"'
            );
            echo form_submit(array(
                'name'  => 'submit',
                'id'    => 'submit',
                'value' => $this->lang->line('common_submit'),
                'class' => 'btsubmit'
            ));
            ?>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>
