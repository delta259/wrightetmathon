<?php $this->load->view("partial/head"); ?>
<body>
<link rel="stylesheet" rev="stylesheet" href="<?php echo base_url();?>css/login.css"/>

<?php
if($this->config->item('custom1_name')=='Y') {
    $lien = 'www.yesstore.fr';
    $url_image = 'images_yes';
} else {
    $lien = 'www.sonrisa-smile.com';
    $url_image = 'images_sonrisa';
}
?>

<!-- Theme Toggle: auto-created by theme-toggle.js -->

<!-- Spinner Overlay -->
<div id="login-overlay" class="login-overlay" style="display:none;">
    <div class="login-overlay-card">
        <div class="login-spinner"></div>
        <div class="login-overlay-text">Connexion en cours...</div>
    </div>
</div>

<div id="container">
    <!-- Header with logo -->
    <div id="top">
        <a href="http://<?php echo $lien; ?>" target="_blank" title="Acc&eacute;der au site web" style="text-decoration:none;">
            <img src="<?php echo base_url() . $url_image; ?>/logo.png" alt="Logo" style="max-width:180px;height:auto;margin-bottom:8px;">
        </a>
        <div style="font-size:13px;opacity:0.8;font-weight:400;">
            <?php echo $this->config->item('company'); ?>
        </div>
    </div>

    <!-- Messages -->
    <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

    <!-- Login Form -->
    <div id="login_form">
        <?php
        echo form_open('login/validate');

        if (!isset($_SESSION['G']->login_employee_id))
        {
        ?>
            <!-- Username -->
            <div class="form_field">
                <label class="form_field_label" for="username">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:-2px;margin-right:4px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <?php echo $this->lang->line('login_username'); ?>
                </label>
                <?php echo form_input(array(
                    'name'        => 'username',
                    'id'          => 'username',
                    'placeholder' => $this->lang->line('login_username'),
                    'size'        => '20',
                    'class'       => 'md-form-input',
                    'autocomplete'=> 'username'
                )); ?>
            </div>

            <!-- Password -->
            <div class="form_field">
                <label class="form_field_label" for="password">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:-2px;margin-right:4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <?php echo $this->lang->line('login_password'); ?>
                </label>
                <?php echo form_password(array(
                    'name'        => 'password',
                    'id'          => 'password',
                    'placeholder' => $this->lang->line('login_password'),
                    'size'        => '20',
                    'class'       => 'md-form-input',
                    'autocomplete'=> 'current-password'
                )); ?>
            </div>

            <!-- Options -->
            <div class="login-options">
                <div class="login-option-title">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:-2px;margin-right:4px;"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Options
                </div>
                <label class="login-checkbox" style="opacity:0.45;pointer-events:none;">
                    <?php echo form_checkbox(array('name'=>'software_update','value'=>'Y','checked'=>FALSE,'disabled'=>'disabled')); ?>
                    <span class="login-checkbox-mark"></span>
                    <span class="login-checkbox-text">Mettre &agrave; jour le POS</span>
                </label>
                <label class="login-checkbox">
                    <?php echo form_checkbox('import_items_database', 'Y', FALSE); ?>
                    <span class="login-checkbox-mark"></span>
                    <span class="login-checkbox-text">Mettre &agrave; jour la base article</span>
                </label>
            </div>

            <!-- Submit -->
            <div id="submit_button">
                <?php echo form_submit(array(
                    'name'  => 'loginButton',
                    'id'    => 'loginButton',
                    'value' => $this->lang->line('common_submit'),
                    'class' => 'btsubmit'
                )); ?>
            </div>
        <?php
        }
        else
        {
        ?>
            <!-- Logout button when already logged in -->
            <div id="submit_button">
                <a href="<?php echo site_url('home/logout'); ?>" class="btsubmit login-logout-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:-2px;margin-right:6px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>D&eacute;connexion
                </a>
            </div>
        <?php
        }
        ?>

        <?php echo form_close(); ?>
    </div>

    <!-- Version info -->
    <div class="version_info">
        <?php echo $this->lang->line('login_version') . ' ' . $this->config->item('application_version'); ?>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $("#username").focus();
    $('#loginButton').click(function() {
        $('#login-overlay').show();
    });
});
</script>

</body>
</html>
