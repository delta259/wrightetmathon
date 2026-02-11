


<!-- -->
<!-- Dispay the configuration screen -->
<!-- -->

<!-- output the header -->
<?php $this->load->view("partial/head"); ?>
<?php $this->load->view("partial/header_banner"); ?>

<div id="wrapper" class="wlp-bighorn-book">

    <?php $this->load->view("partial/header_menu"); ?>

    <div class="wlp-bighorn-book">

        <div class="wlp-bighorn-book-content">

            <main id="login_page" class="wlp-bighorn-page-unconnect" role="main">


                <!--Contenu background gris-->
                <div class="body_page" id="loginPage">
                    <div>

                        <div class="body_colonne">
                            <h2><?php echo $this->lang->line('modules_home');?></h2>
                            <div class="body_cadre_gris">


				<table class="table_center">			
					<tbody>
						<tr>
							<td align="center"><h3><a <?php echo site_url('home/user');?>><?php echo $this->lang->line('login_user_menu'); ?></a></h3></td>
						</tr>
						<tr>
							<?php
								if ($_SESSION['G']->login_employee_info->username == $this->config->item('admin_user_name') OR $_SESSION['G']->login_employee_info->username == $this->config->item('sys_admin_user_name'))
								{
									?>
									<td align="center"><h3><a <?php echo site_url('home/admin');?>><?php echo $this->lang->line('login_admin_menu'); ?></a></h3></td>
									<?php
								}
							?>
						</tr>
						<tr>
							<?php
								if ($_SESSION['G']->login_employee_info->username == $this->config->item('sys_admin_user_name'))
								{
									?>
									<td align="center"><h3><a <?php echo site_url('home/sysadmin');?>><?php echo $this->lang->line('login_sys_admin_menu'); ?></a></h3></td>
									<?php
								}
							?>
						</tr>
					</tbody>
				</table>

                            </div>
                        </div>

                    </div>
                </div>

            </main></div></div>
</div>
<?php
// show the footer
$this->load->view("partial/footer");
?>