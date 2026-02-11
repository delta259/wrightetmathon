
<?php $this->load->view("partial/head"); ?>
<?php $this->load->view("partial/header_banner"); ?>



<div class="wlp-bighorn-book" style="min-height: 850px;">

    <div class="wlp-bighorn-book-content">

        <main  class="wlp-bighorn-page-unconnect" role="main">


            <div class="body_page">
<div class="body_colonne">
    <h2><?php echo $this->lang->line('login_welcome_message').' '.$this->config->item('company'); ?></h2>

                    <!-- div body gris colonne Droite -->
                    <div class="body_cadre_gris" >
                        <div class="span3">
                            <?php
                            // return to login
                            echo anchor	('login/rolling',
                                "<div class='btnew' style='float: left;'><span>".$this->lang->line('common_return')."</span></div>"
                            );
                            ?>

                        </div>



<!-- show the flah_info document -->

	<object data="flash_info_publish_me.pdf" type="application/pdf" width="1300px" height="800px" ></object>

                    </div>
                    </div>
            </div>
    </main>
</div>
</div>


<?php
// show the footer
$this->load->view("partial/footer");
?>