<body>
<?php if (!isset($_SESSION['url_image'])) { $_SESSION['url_image'] = 'images_sonrisa'; } ?>
<header class="wlp-bighorn-header" role="banner">

    <div class="wlp-bighorn-layout wlp-bighorn-layout-flow">


        <div class="wlp-bighorn-layout-cell wlp-bighorn-layout-flow-horizontal wlp-bighorn-layout-flow-first" style="height: auto">

            <div></div>

            <div class="wlp-bighorn-theme wlp-bighorn-theme-borderless">

                <div id="Header" class="wlp-bighorn-window  ">

                    <div class="wlp-bighorn-menu wlp-bighorn-window-content wlp-bighorn-menu-multi" role="navigation">


                        <div class="tetiere">
                            <div class="bandeau">

                                <h1><?php if(isset($_SESSION['G']->login_employee_id)){

                                        echo $this->config->item('branch_code');

                                    }

                                    ?>
                                  </h1>


                                <div class="liens">
                                    <div class="wlp-bighorn-menu-menu-panel">
                                        <ul>
                                            <?php if(isset($_SESSION['G']->login_employee_id)){

                                                echo '<li>
                                                Compte : ';

                                                echo $_SESSION['G']->login_employee_info->first_name ;

                                                echo '  </li>    &nbsp;|&nbsp;'; 
                                            }

                                            ?>
                                            <li>   <!-- <a target="_blank" href="book.pdf" title="Aide"> <!--  -->
                                                    <a href="<?php echo site_url("home/help"); ?>" title="Aide">
                                                    <?php echo $this->lang->line("common_help"); ?>

                                                </a> </li> &nbsp;|&nbsp;

                                            <li>  <a target="_blank"  href="https://www.hidrive.strato.com/share/9uowzrqhm9"  title="certificats"><?php echo $this->lang->line("modules_security"); ?>
                                                </a></li>

                                            <?php if(isset($_SESSION['G']->login_employee_id)){
                                            if ($_SESSION['G']->login_employee_info->admin == 1){
                                                echo '<li> &nbsp;|&nbsp;';
                                                ?>
                                                <li class=""><img src="<?php echo base_url();echo $_SESSION['url_image']?>/params.png" style ="vertical-align: middle;" width="20px">

                                                    <ul style="      width: 211px;
    margin-left: -185px;
    z-index: 102;">
                                                        <li><a style="
    padding: 7px;
    z-index: 102;" href="<?php echo site_url("home/admin");?>"><?php echo $this->lang->line("modules_admin_sys"); ?> </a></li>

                                                        <li><a style="
    padding: 7px;
    z-index: 102;" href="<?php echo site_url("config");?>" ><?php echo $this->lang->line("config_info"); ?></a> </li>

                                                    </ul>
                                                </li>
                                            <?php }
                                            } ?>
                                            <?php if(isset($_SESSION['G']->login_employee_id)){

                                                ?>

                                                &nbsp;|&nbsp;
                                                <li>
                                                    <a style="" href="<?php echo site_url("home/backup");?>" id="lien"><img src="<?php echo base_url(); echo $_SESSION['url_image'];?>/save_db.png" style ="vertical-align: middle;" width="20px" title="<?php echo $this->lang->line("common_backup"); ?>"></a>
                                                </li>


                                                &nbsp;|&nbsp;
                                                <li>


                                                    <?php echo anchor("home/logout",$this->lang->line("common_logout"),"id='sablier'");?>

                                                </li>

                                                &nbsp;|&nbsp;
                                                <li>
                                                    <a style="" href="<?php echo site_url("home/shutdown");?>" id="shutdown"><img src="<?php echo base_url(); echo $_SESSION['url_image'];?>/eteindre.png" style ="vertical-align: middle;" width="20px" title="<?php echo $this->lang->line('common_shutdown_title'); ?>"></a>
                                                </li>
                                               
                                            <?php
                                            } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                <?php if($this->config->item('custom1_name')=='Y') {
                    $lien = 'www.yesstore.fr';
                    $_SESSION['url_image']='images_yes';
                }
                else {
                    $lien = 'www.sonrisa-smile.com';
                    $_SESSION['url_image']='images_sonrisa';
                }
                ?>

                <a tabindex="-1" class="lien_image" href="http://<?php echo $lien ?>" title="Accéder au site web " target="_blank">
                    <img src="<?php echo base_url(); echo $_SESSION['url_image'];?>/logo.png" class="logoam" alt="Logo">
                </a>

                        </div>
                
<?php
//count number of notification: compte le nombre de balise "<li" > dans les fichiers du dossier notification
$test_01_notif = file_get_contents('/var/www/html/wrightetmathon/notifications/01_test_notif.php');
$test_02_alert = file_get_contents('/var/www/html/wrightetmathon/notifications/02_test_alerte.php');
$test_03_new = file_get_contents('/var/www/html/wrightetmathon/notifications/03_test_nouveau.php');
$test_04_astuce = file_get_contents('/var/www/html/wrightetmathon/notifications/04_test_astuce.php');
$test = $test_01_notif . $test_02_alert . $test_03_new . $test_04_astuce;
$occurence = substr_count($test, '<li ');
if($occurence > 0)
{
?>

                        <button type="button" class="button-default show-notifications  js-show-notifications" onclick="function_notif()">
  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="30" height="32" viewBox="0 0 30 32">
    <defs>
      <g id="icon-bell">
	      <path class="path1" d="M15.143 30.286q0-0.286-0.286-0.286-1.054 0-1.813-0.759t-0.759-1.813q0-0.286-0.286-0.286t-0.286 0.286q0 1.304 0.92 2.223t2.223 0.92q0.286 0 0.286-0.286zM3.268 25.143h23.179q-2.929-3.232-4.402-7.348t-1.473-8.652q0-4.571-5.714-4.571t-5.714 4.571q0 4.536-1.473 8.652t-4.402 7.348zM29.714 25.143q0 0.929-0.679 1.607t-1.607 0.679h-8q0 1.893-1.339 3.232t-3.232 1.339-3.232-1.339-1.339-3.232h-8q-0.929 0-1.607-0.679t-0.679-1.607q3.393-2.875 5.125-7.098t1.732-8.902q0-2.946 1.714-4.679t4.714-2.089q-0.143-0.321-0.143-0.661 0-0.714 0.5-1.214t1.214-0.5 1.214 0.5 0.5 1.214q0 0.339-0.143 0.661 3 0.357 4.714 2.089t1.714 4.679q0 4.679 1.732 8.902t5.125 7.098z" />
      </g>
    </defs>
    <g fill="#000000">
	    <use xlink:href="#icon-bell" transform="translate(0 0)"></use>
    </g>
  </svg>
  <div class="notifications-count js-count"><?php echo $occurence; ?></div>
</button>
    <script src="https://static.codepen.io/assets/common/stopExecutionOnTimeout-9bf952ccbbd13c245169a0a1190323a27ce073a3d304b8c0fdf421ab22794a58.js"></script>

<?php
//include notification
//$this->load->view("notification/notification");

?>
<div class="notifications" id="test_notif" >
        <h3>Notifications</h3>
        <ul class="notifications-list">
          <li class="item no-data"> ... </li>

<?php
   $test_01_notif = file_get_contents('/var/www/html/wrightetmathon/notifications/01_test_notif.php');
   echo $test_01_notif;

   $test_02_alert = file_get_contents('/var/www/html/wrightetmathon/notifications/02_test_alerte.php');
   echo $test_02_alert;

   $test_03_new = file_get_contents('/var/www/html/wrightetmathon/notifications/03_test_nouveau.php');
   echo $test_03_new;

   $test_04_astuce = file_get_contents('/var/www/html/wrightetmathon/notifications/04_test_astuce.php');
   echo $test_04_astuce;
?>
        </ul>
</div>
<?php
}
?>



<script>
function function_notif(){
    var element = document.getElementById("test_notif");
   element.classList.toggle("test_notif_css");
}
</script>

                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
    

<script type="text/javascript">
    $(document).ready(function()
    {
        $('#lien').click(function()
        {

            $('html').css('cursor','wait');
        });
    });
</script>


<!-- div for spinner -->
<div id="spinnerdeconnexion" class="spinnerdeconnexion" style="display:none;">
    <!--  <img id="img-spinner" src="<?php /*echo base_url();*/?>images/M&Wloader.gif" alt="Loading"/>-->

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

<!-- script for spinner -->
<script type="text/javascript">
    $(document).ready(function()
    {
        $('#sablier').click(function()
        {
            $('#spinnerdeconnexion').show();
        });
    });
</script>
