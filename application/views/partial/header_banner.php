<body>
<?php if (!isset($_SESSION['url_image'])) { $_SESSION['url_image'] = 'images_sonrisa'; } ?>
<?php
if($this->config->item('custom1_name')=='Y') {
    $lien = 'www.yesstore.fr';
    $_SESSION['url_image']='images_yes';
} else {
    $lien = 'www.sonrisa-smile.com';
    $_SESSION['url_image']='images_sonrisa';
}
?>

<header class="unified-header" role="banner">
    <!-- Logo - First element -->
    <a class="header-logo" href="http://<?php echo $lien ?>" title="Accéder au site web" target="_blank">
        <img src="<?php echo base_url(); echo $_SESSION['url_image'];?>/logo.png" alt="Logo">
    </a>

    <!-- Navigation Menu will be here (order 2 in CSS) -->

    <!-- Top Bar: Title + User Links -->
    <div class="header-top">
        <div class="header-top-content">
            <!-- Title -->
            <h1 class="header-title">
                <?php if(isset($_SESSION['G']->login_employee_id)){
                    echo $this->config->item('branch_code');
                } ?>
            </h1>

            <!-- User Links -->
            <div class="header-user-links">
                <?php if(isset($_SESSION['G']->login_employee_id)){ ?>
                    <?php if($this->config->item('multi_vendeur') == 'Y'){ ?>
                    <button class="user-name vendor-trigger" onclick="document.getElementById('vendor-modal').classList.add('open')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <?php echo $_SESSION['G']->login_employee_info->first_name; ?>
                        <svg class="vendor-chevron" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg>
                    </button>
                    <?php } else { ?>
                    <span class="user-name">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <?php echo $_SESSION['G']->login_employee_info->first_name; ?>
                    </span>
                    <?php } ?>
                <?php } ?>

                <a href="<?php echo site_url("home/help"); ?>" title="Aide">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg>
                    <?php echo $this->lang->line("common_help"); ?>
                </a>

                <a href="https://www.hidrive.strato.com/share/9uowzrqhm9" target="_blank" title="Certificats">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                    <?php echo $this->lang->line("modules_security"); ?>
                </a>

                <?php if(isset($_SESSION['G']->login_employee_id)){
                    if ($_SESSION['G']->login_employee_info->admin == 1){ ?>
                        <div class="dropdown">
                            <button class="dropdown-toggle">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.14 12.94c.04-.31.06-.63.06-.94 0-.31-.02-.63-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                            </button>
                            <div class="dropdown-menu">
                                <a href="<?php echo site_url("home/admin");?>"><?php echo $this->lang->line("modules_admin_sys"); ?></a>
                                <a href="<?php echo site_url("config");?>"><?php echo $this->lang->line("config_info"); ?></a>
                            </div>
                        </div>
                    <?php }
                } ?>

                <?php if(isset($_SESSION['G']->login_employee_id)){ ?>
                    <a href="<?php echo site_url("home/backup");?>" id="lien" title="<?php echo $this->lang->line("common_backup"); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 12v7H5v-7H3v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7h-2zm-6 .67l2.59-2.58L17 11.5l-5 5-5-5 1.41-1.41L11 12.67V3h2v9.67z"/></svg>
                    </a>

                    <?php echo anchor("home/logout",'<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg> '.$this->lang->line("common_logout"),"id='sablier' title='".$this->lang->line("common_logout")."'");?>

                    <a href="<?php echo site_url("home/shutdown");?>" id="shutdown" title="<?php echo $this->lang->line('common_shutdown_title'); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13 3h-2v10h2V3zm4.83 2.17l-1.42 1.42C17.99 7.86 19 9.81 19 12c0 3.87-3.13 7-7 7s-7-3.13-7-7c0-2.19 1.01-4.14 2.58-5.42L6.17 5.17C4.23 6.82 3 9.26 3 12c0 4.97 4.03 9 9 9s9-4.03 9-9c0-2.74-1.23-5.18-3.17-6.83z"/></svg>
                    </a>
                <?php } ?>

                <?php
                // Notifications
                $test_01_notif = @file_get_contents('/var/www/html/wrightetmathon/notifications/01_test_notif.php');
                $test_02_alert = @file_get_contents('/var/www/html/wrightetmathon/notifications/02_test_alerte.php');
                $test_03_new = @file_get_contents('/var/www/html/wrightetmathon/notifications/03_test_nouveau.php');
                $test_04_astuce = @file_get_contents('/var/www/html/wrightetmathon/notifications/04_test_astuce.php');
                $test = $test_01_notif . $test_02_alert . $test_03_new . $test_04_astuce;
                $occurence = substr_count($test, '<li ');
                if($occurence > 0) { ?>
                    <button type="button" class="notification-btn" onclick="function_notif()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                        <span class="notification-count"><?php echo $occurence; ?></span>
                    </button>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="header-nav" role="navigation">
        <ul class="nav-menu">
            <li>
                <a href="<?php echo site_url('reports/inventory_rolling');?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    <span><?php echo $this->lang->line('modules_home');?></span>
                </a>
            </li>

            <li class="has-submenu">
                <a href="<?php echo site_url('items'); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 2h2v2h-2V4zm0 4h2v2h-2V8zm-4-4h2v2H7V4zm0 4h2v2H7V8zm-1 10v-2h10v2H6zm11-4H7v-2h10v2zm0-4h-2V8h2v2zm0-4h-2V4h2v2z"/></svg>
                    <span>Produits</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?php echo site_url("categories");?>"><?php echo $this->lang->line('modules_categories');?></a></li>
                    <li><a href="<?php echo site_url("items");?>"><?php echo $this->lang->line('modules_items');?></a></li>
                    <li><a href="<?php echo site_url("item_kits");?>"><?php echo $this->lang->line('modules_item_kits');?></a></li>
                    <li><a href="<?php echo site_url("suppliers");?>"><?php echo $this->lang->line('modules_suppliers');?></a></li>
                    <li><a href="<?php echo site_url("inventaire");?>">Inventaire</a></li>
                </ul>
            </li>

            <li>
                <a href="<?php echo site_url("customers");?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    <span><?php echo $this->lang->line('modules_customers');?></span>
                </a>
            </li>

            <li class="has-submenu">
                <a href="<?php echo site_url('sales'); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.36 9l.6 3H5.04l.6-3h12.72M20 4H4v2h16V4zm0 3H4l-1 5v2h1v6h10v-6h4v6h2v-6h1v-2l-1-5zM6 18v-4h6v4H6z"/></svg>
                    <span>Boutique</span>
                </a>
                <ul class="submenu">
                    <li><a href="<?php echo site_url("cashtills");?>"><?php echo $this->lang->line('modules_cashtills');?></a></li>
                    <li><a href="<?php echo site_url("sales");?>"><?php echo $this->lang->line('modules_sales');?></a></li>
                    <li><a href="<?php echo site_url("receivings");?>"><?php echo $this->lang->line('modules_receivings');?></a></li>
                    <li><a href="<?php echo site_url("targets");?>"><?php echo $this->lang->line('modules_targets');?></a></li>
                </ul>
            </li>

            <?php if(isset($_SESSION['G']->login_employee_id)){
                if ($_SESSION['G']->login_employee_info->admin == 1){ ?>
                    <li>
                        <a href="<?php echo site_url("employees");?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5.9c1.16 0 2.1.94 2.1 2.1s-.94 2.1-2.1 2.1S9.9 9.16 9.9 8s.94-2.1 2.1-2.1m0 9c2.97 0 6.1 1.46 6.1 2.1v1.1H5.9V17c0-.64 3.13-2.1 6.1-2.1M12 4C9.79 4 8 5.79 8 8s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 9c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4z"/></svg>
                            <span><?php echo $this->lang->line('modules_employees');?></span>
                        </a>
                    </li>
            <?php }
            } ?>

            <li>
                <a href="<?php echo site_url("reports");?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
                    <span><?php echo $this->lang->line('modules_reports');?></span>
                </a>
            </li>

            <li>
                <a href="https://docs.google.com/spreadsheets/d/1KxVo0t5rct8eRoH-yu56IqVGBK_WAES8IZy5iWLjYG4/edit#gid=0" target="_blank">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/></svg>
                    <span><?php echo $this->lang->line('modules_carteKDO');?></span>
                </a>
            </li>
        </ul>
    </nav>
</header>

<!-- Vendor Selection Modal -->
<?php if(isset($_SESSION['G']->login_employee_id) && $this->config->item('multi_vendeur') == 'Y'){ ?>
<div class="vendor-modal-backdrop" id="vendor-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="vendor-modal">
        <div class="vendor-modal-header">
            <h3>Changer de vendeur</h3>
            <button class="vendor-modal-close" onclick="document.getElementById('vendor-modal').classList.remove('open')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="vendor-modal-body">
            <?php for ($v = 1; $v <= 4; $v++) {
                if(isset($_SESSION['numero_button_vendeur']['person_id_vendeur'][$v]) && $_SESSION['numero_button_vendeur']['person_id_vendeur'][$v] != '') {
                    $is_active = ($_SESSION['last_click'] == $v);
                    ?>
                    <a href="<?php echo site_url('sales/load_data_set_vendeur/'.$v); ?>" class="vendor-modal-item <?php echo $is_active ? 'vendor-modal-active' : ''; ?>">
                        <span class="vendor-modal-slot"><?php echo $v; ?></span>
                        <span class="vendor-modal-name"><?php echo $_SESSION['numero_button_vendeur']['person_id_vendeur'][$v]; ?></span>
                        <?php if($is_active){ ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <?php } ?>
                    </a>
                <?php } else { ?>
                    <a href="<?php echo site_url('sales/set_vendeur/'.$v); ?>" class="vendor-modal-item vendor-modal-empty">
                        <span class="vendor-modal-slot"><?php echo $v; ?></span>
                        <span class="vendor-modal-name">Vendeur <?php echo $v; ?></span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </a>
                <?php }
            } ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var m = document.getElementById('vendor-modal');
        if (m) m.classList.remove('open');
    }
});
</script>
<?php } ?>

<!-- Notifications Panel -->
<?php if($occurence > 0) { ?>
<div class="notifications" id="test_notif">
    <h3>Notifications</h3>
    <ul class="notifications-list">
        <li class="item no-data">...</li>
        <?php
        echo $test_01_notif;
        echo $test_02_alert;
        echo $test_03_new;
        echo $test_04_astuce;
        ?>
    </ul>
</div>
<?php } ?>

<script>
function function_notif(){
    var element = document.getElementById("test_notif");
    if(element) element.classList.toggle("test_notif_css");
}
</script>

<!-- Spinner for logout -->
<div id="spinnerdeconnexion" class="spinnerdeconnexion" style="display:none;">
    <div id="floatingCirclesG">
        <div class="f_circleG" id="frotateG_01"></div>
        <div class="f_circleG" id="frotateG_02"></div>
        <div class="f_circleG" id="frotateG_03"></div>
        <div class="f_circleG" id="frotateG_04"></div>
        <div class="f_circleG" id="frotateG_05"></div>
        <div class="f_circleG" id="frotateG_06"></div>
        <div class="f_circleG" id="frotateG_07"></div>
        <div class="f_circleG" id="frotateG_08"></div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#lien').click(function() {
        $('html').css('cursor','wait');
    });
    $('#sablier').click(function() {
        $('#spinnerdeconnexion').show();
    });
});
</script>
