<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class escpos {
    
    function escpos()
    {
        $CI = & get_instance();
        log_message('Debug', 'escpos class is loaded.');
    }
 
    function load()
    {
        include_once APPPATH.'/third_party/escpos_php_master/autoload.php';
        return;
    }
}
