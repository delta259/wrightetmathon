
<?php $this->load->view("partial/head"); ?>
<?php $this->load->view("partial/header_banner"); ?>



<div class="wlp-bighorn-book" style="min-height: 850px;">

    <div class="wlp-bighorn-book-content">

        <main  class="wlp-bighorn-page-unconnect" role="main">


            <div class="body_page">
<div class="body_colonne">
    <h2><?php echo $this->lang->line('login_welcome_message').' '.$this->config->item('company'); ?></h2>

                    <!-- div body gris colonne Droite -->


<!-- show the flah_info document -->

                        <object type="application/vnd.ms-excel" data="test_catalogue.xls" >
                            alt : <a href="test_catalogue.xls">test.xls</a>
                        </object>
                    </div>
            </div>
    </main>
</div>
</div>


<?php
// show the footer
$this->load->view("partial/footer");
?>