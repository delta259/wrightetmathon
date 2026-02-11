<?php
// Check if loaded in modal mode
$is_modal_mode = isset($_GET['modal']) && $_GET['modal'] == '1';
$modal_theme = isset($_GET['theme']) ? $_GET['theme'] : 'light';
?>
<?php $this->load->view("partial/head"); ?>

<?php if ($is_modal_mode): ?>
<!-- Modal Mode: Simplified layout -->
<style type="text/css">
/* Force white background - override everything */
html.modal-html, html.modal-html body, body.modal-mode {
    background: #fff !important;
    background-color: #fff !important;
    background-image: none !important;
    margin: 0 !important;
    padding: 0 !important;
}
html.modal-html[data-theme="dark"], html.modal-html[data-theme="dark"] body, html[data-theme="dark"] body.modal-mode {
    background: #1e293b !important;
    background-color: #1e293b !important;
}
.modal-page-wrapper {
    padding: 16px;
    background: #fff !important;
    min-height: 100%;
}
[data-theme="dark"] .modal-page-wrapper { background: #1e293b !important; }
/* Hide layout elements and theme toggle */
.unified-header, .unified-footer, .header_banner, .wlp-bighorn-book, #wrapper,
.navigation, .menubar, .topbar, nav, header, footer, #login_page,
.theme-toggle, #theme-toggle, .theme-toggle-btn, [class*="theme-toggle"] { display: none !important; }
/* Form styling */
.modal-page-wrapper h2 { font-size: 1rem; font-weight: 600; margin: 0 0 16px 0; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); }
.modal-page-wrapper .tablesorter { width: 100%; font-size: 0.85rem; }
.modal-page-wrapper .fieldset { border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; margin-bottom: 15px; }
.modal-page-wrapper .btsubmit { background: var(--primary); color: #fff; border: none; border-radius: 6px; padding: 10px 20px; cursor: pointer; }
.modal-page-wrapper .delete_button { background: var(--danger); color: #fff; border: none; border-radius: 6px; padding: 8px 16px; cursor: pointer; }
</style>
<script>
document.documentElement.classList.add('modal-html');
document.documentElement.setAttribute('data-theme','<?php echo htmlspecialchars($modal_theme); ?>');
// Intercept return/cancel buttons to close parent modal
document.addEventListener('click', function(e) {
    var target = e.target.closest('.btretour, .btlien, a[href*="common_exit"], a[href*="/admin"], .cancel-btn, .btn-cancel');
    if (target && window.parent && window.parent.closeModal) {
        e.preventDefault();
        window.parent.closeModal();
    }
});
</script>
<body class="modal-mode">
<div class="modal-page-wrapper">
<?php else: ?>
<!-- Normal Mode: Full layout -->
<?php $this->load->view("partial/header_banner"); ?>

<div id="wrapper" class="wlp-bighorn-book">

    <!-- Navigation menu is now integrated into header_banner.php -->

    <div class="wlp-bighorn-book">

        <div class="wlp-bighorn-book-content">

            <main id="login_page" class="wlp-bighorn-page-unconnect" role="main">

                <div class="body_page" >

                    <div class="body_colonne">
					<?php

					$num_customer = $this->Customer->count_all();
					$controller_name = $_SESSION['controller_name'] ?? '';
					if($controller_name == "customers")
					{
					?>
                        <h2><?php echo($this->lang->line('modules_'.$controller_name)).' ('.$num_customer.')' ;?></h2>
					<?php
					} else {
					?>
						<h2><?php echo($this->lang->line('modules_'.$controller_name)) ;?></h2>
					<?php
					}


if(($controller_name=='items_desactives') || ($controller_name=='items_actifs') || ($controller_name=='items_news'))
{
	$_SESSION['controller_name']='items';
}
?>
<?php endif; ?>




<!--<?php
/*
<!-- -->
<!-- body -->
<!-- -->
<body>

<div id="menubar">
	<div id="menubar_container">
		<div id="menubar_company_info">
			<?php
				echo $this->config->item('branch_code');
			?>
		</div>

		<div id="menubar_navigation">
			<?php
				foreach($_SESSION['G']->modules as $module_id => $module_info)
				{
					// check user has permission to this module
					$has_permission			=	$this->Employee->has_permission($module_id, $_SESSION['G']->login_employee_id);

					if ($has_permission)
					{
						if ($module_info['show_in_header'] == 'Y')
						{
							// initialise
							$url			=	'';
							$target			=	'';

							switch ($module_info['module_name'])
							{
								case 'sonrisa':
									$url 	=	prep_url($this->config->item('website'));
									$target	=	'target="_blank"';
									break;

								case 'drive':
									$url 	=	prep_url('drive.google.com/drive/#my-drive');
									$target	=	'target="_blank"';
									break;

								case 'print':
									$url	=	'';
									$target	=	'onClick="window.print()"';
									break;

								case 'catalogue':
									$url	=	prep_url('https://drive.google.com/open?id=0B2l4-1fUyaoKMmpiSHV2R2ota2s');
									$target	=	'target="_blank"';
									break;

								case 'copyight':
									$url	=	'';
									$target	=	'onClick="window.print()"';
									break;

								case 'security':
									$url	=	prep_url('drive.google.com/folderview?id=0B1DlcJN8YXtnZW9LUUdNaHoxdFU&usp=sharing');
									$target	=	'target="_blank"';
									break;

								default:
									$url 	=	$module_info['module_name'];
									$target	=	'target="_self"';
									break;
							}

							// create image array
							$img	=	array	(
												'src'	=>	base_url().'images/menubar/'.$module_info['module_name'].'.png',
												'border'=>	'0',
												'alt'	=>	$module_info['module_name']
												);

							// create the anchor
							echo	"<div class='menu_item'>".anchor ($url, img($img), $target)
							."<br>"
							.anchor ($url, $this->lang->line($module_info['name_lang_key']), $target)
							."</div>";
						}
					}
				}
				?>
			<!-- div for spinner -->
			<div id="spinner_on_bar" class="spinner_on_bar" style="display:none;">
				<img id="img-spinner" src="<?php echo base_url();?>images/M&Wloader.gif" alt="Loading"/>
			</div>
		</div>
	</div>

		<!-- Output User info -->
		<div id="menubar_footer">
			<?php
				echo $this->lang->line('common_welcome').' '.$_SESSION['G']->login_employee_info->first_name.' | ';
				echo anchor("home/logout",$this->lang->line("common_logout"));
				echo ' | ';
				echo anchor("home/index",$this->lang->line("common_home"));
				echo ' | ';
				echo anchor("home/backup",$this->lang->line("common_backup"));
			?>
		</div>

		<!-- Output Security passwords -->
		<div id="menubar_codes">
			<?php
			echo $this->lang->line('config_Alarm_OK_code'). ' = ' .$this->config->item('Alarm_OK_code');
			echo '  |  ';
			echo $this->lang->line('config_Alarm_KO_code'). ' = ' .$this->config->item('Alarm_KO_code');
			?>
		</div>

		<div id="menubar_date">
		<?php echo date('d/m/Y') ?> <!--Modified HBW V1-->
		</div>

</div>

<!--<div id="content_area_wrapper">
<div id="content_area">-->
*/?> -->
