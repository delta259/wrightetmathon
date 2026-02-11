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

<div id="container">
    <div id="top">
        <a href="http://<?php echo $lien; ?>" target="_blank" title="Acc&eacute;der au site web" style="text-decoration:none;">
            <img src="<?php echo base_url() . $url_image; ?>/logo.png" alt="Logo" style="max-width:180px;height:auto;margin-bottom:8px;">
        </a>
        <div style="font-size:13px;opacity:0.8;font-weight:400;">
            <?php echo $this->config->item('company'); ?>
        </div>
    </div>

    <div id="login_form" class="exit-fallback" style="display:none;">
        <div style="text-align:center;padding:20px 0;">
            <svg width="48" height="48" fill="none" stroke="var(--login-primary)" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:16px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <div style="font-size:15px;font-weight:600;color:var(--login-text-primary);margin-bottom:8px;">D&eacute;connexion effectu&eacute;e</div>
            <div style="font-size:13px;color:var(--login-text-muted);margin-bottom:20px;">Vous pouvez fermer cet onglet ou vous reconnecter.</div>
            <a href="<?php echo site_url('login'); ?>" class="btsubmit" style="display:inline-block;padding:12px 32px;text-decoration:none;font-size:14px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:8px;"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Se reconnecter
            </a>
        </div>
    </div>

    <div class="version_info">
        <?php echo $this->lang->line('login_version') . ' ' . $this->config->item('application_version'); ?>
    </div>
</div>

<script type="text/javascript">
window.close();
setTimeout(function() {
    var fb = document.querySelector('.exit-fallback');
    if (fb) { fb.style.display = 'block'; }
}, 300);
</script>

</body>
</html>
