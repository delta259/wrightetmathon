<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="shortcut icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon" />
  <script>
  // Apply theme + background BEFORE any CSS loads to prevent color flash
  (function(){
    var t=localStorage.getItem('wm-theme');
    if(!t){t=(window.matchMedia&&matchMedia('(prefers-color-scheme:dark)').matches)?'dark':'light';}
    document.documentElement.setAttribute('data-theme',t);
    var bg=t==='dark'?'#1a1f2e':'#FAFAF8';
    document.documentElement.style.background=bg;
    document.write('<style>html,body{background:'+bg+'!important}</style>');
  })();
  </script>
	<base href="<?php echo base_url();?>" />
	<title><?php echo $this->config->item('company').' -- '.$this->lang->line('common_powered_by')?></title> <!--Modified HBW V1-->

<?php if($this->config->item('custom1_name')=='Y'){
    
   ?>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/css_yesstore.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/spinner_yesstore.css">

    <?php
}
else {
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>css2/css_sonrisa.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/spinner_sonrisa.css">

    <?php
}
?>

  <!--<link rel="stylesheet" rev="stylesheet" href="<?php /* echo base_url(); */?>css/ospos.css" /> -->
  <link rel="stylesheet" rev="stylesheet" href="<?php echo base_url();?>css/ospos_print.css"  media="print"/>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/b.css">
    <link rel="stylesheet" href="<?php echo base_url();?>/jquery-ui-1.12.1.custom/jquery-ui.theme.css">
    <link rel="stylesheet" href="<?php echo base_url();?>/jquery-ui-1.12.1.custom/jquery-ui.css">

  <!--  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css"> -->
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/layout.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/biblicnam-structure-sans.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/reset.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/table.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/clear.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/liens.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/forms.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/boutons.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/general.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/nav.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/colors.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/custom.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/window.css">

  <!-- Attention, mettre les scripts javascript dans les fenêtres principales .(pas dans les fenêtres modales) -->
  <script>var BASE_URL = '<?php echo site_url(); ?>';</script>
  <script src="<?php echo base_url();?>js/jquery-3.3.1.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
  <script type="text/javascript">
  /* $.browser polyfill for legacy plugins (autocomplete, validate, bgiframe, thickbox) */
  if (!jQuery.browser) {
    var ua = navigator.userAgent.toLowerCase();
    jQuery.browser = {
      msie: /msie/.test(ua) && !/opera/.test(ua),
      mozilla: /mozilla/.test(ua) && !/(compatible|webkit)/.test(ua),
      webkit: /webkit/.test(ua),
      opera: /opera/.test(ua),
      safari: /safari/.test(ua) && !/chrome/.test(ua),
      version: (ua.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [])[1]
    };
  }
  </script>

  <!-- For notification -->
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css2/notification_test.css">

  <!-- Modern Theme - YesAppro inspired (January 2026) -->
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/modern-theme.css">

  <!-- POS Register Modern Styles (loaded on sales pages) -->
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/pos-register.css">

  <!-- Theme Toggle (Light/Dark Mode) -->
  <script src="<?php echo base_url();?>js/theme-toggle.js" type="text/javascript"></script>

  <!-- Virtual Keyboard (Touch POS) — loaded only when touchscreen mode is enabled -->
  <?php if ($this->config->item('touchscreen') == '1') { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/virtual-keyboard.css">
  <script src="<?php echo base_url();?>js/virtual-keyboard.js" type="text/javascript"></script>
  <?php } ?>

    <script src="<?php echo base_url();?>js/jquery.color.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/jquery.metadata.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/jquery.form.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/jquery.tablesorter.min.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/jquery.ajax_queue.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/jquery.bgiframe.min.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/jquery.autocomplete.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/jquery.validate.min.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/jquery.jkey-1.1.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/thickbox.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/common.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/manage_tables.js" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <script src="<?php echo base_url();?>js/date.js" type="text/javascript" language="javascript" charset="UTF-8"></script>

    <script src="<?php echo base_url();?>js/datepicker.js" type="text/javascript" language="javascript" charset="UTF-8"></script>

    <!-- -->
    <!-- Load check browser close -->
    <!-- -->
   <!-- <script src="<?php /*echo base_url();*/?>js/check_browser_close.js" type="text/javascript" language="javascript" charset="UTF-8"></script>-->

    <!-- -->
    <!-- Load the chart library from FusionCharts -->
    <!-- -->
    <script src="<?php echo base_url();?>Charts/FusionCharts.js" type="text/javascript" language="javascript" charset="UTF-8"></script>




<!-- Auto-detect iframe mode (admin panel) — runs before <body> to prevent FOUC -->
<style>
html.iframe-admin .unified-header,
html.iframe-admin .unified-footer,
html.iframe-admin .theme-toggle,
html.iframe-admin #theme-toggle,
html.iframe-admin .theme-toggle-btn,
html.iframe-admin [class*="theme-toggle"] { display: none !important; }
html.iframe-admin, html.iframe-admin body {
    background: var(--bg-container, #fff) !important;
    background-image: none !important;
    margin: 0 !important;
}
html.iframe-admin[data-theme="dark"], html.iframe-admin[data-theme="dark"] body {
    background: var(--bg-container, #1e293b) !important;
}
html.iframe-admin .body_colonne > h2 { display: none; }
html.iframe-admin .body_page { padding: 8px 16px; }
html.iframe-admin #wrapper { background: transparent !important; }
</style>
<script>
(function(){
    if (window.self !== window.top) {
        document.documentElement.classList.add('iframe-admin');
        try {
            var pt = window.parent.document.documentElement.getAttribute('data-theme');
            if (pt) document.documentElement.setAttribute('data-theme', pt);
        } catch(e) {}
    }
})();
</script>

</head>
