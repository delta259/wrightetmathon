<?php

if(!isset($_SESSION['notif_last_synchro']))
{
    $_SESSION['notif_last_synchro'] = 0;
}
//$notif_last_synchro = $this->App_config->get('notif_last_synchro');
//if(intval($notif_last_synchro) < intval(date("H")))
if(intval($_SESSION['notif_last_synchro']) < intval(date("H")))
{
   // $this->App_config->save('notif_last_synchro', date("H"));
   $_SESSION['notif_last_synchro'] = date("H");
    // script to mount hidrive
    exec('sudo /var/www/html/wrightetmathon/application/controllers/hidrive_by_shop_notification.sh');
}

?>