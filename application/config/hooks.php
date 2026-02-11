<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/ 
// maintain the database
/*
$hook['post_controller_constructor'][] 	= 	array	(
													'class'    => '',
													'function' => 'maintain_database',
													'filename' => 'maintain_database.php',
													'filepath' => 'hooks'
													);
													
$hook['post_controller_constructor'][] 	= 	array	(
													'class'    => '',
													'function' => 'maintain_database_status_1',
													'filename' => 'maintain_database.php',
													'filepath' => 'hooks'
													);
*/
                         
// load the config data
$hook['post_controller_constructor'][] 	= 	array	(
													'class'    => '',
													'function' => 'load_config',
													'filename' => 'load_config.php',
													'filepath' => 'hooks'
													);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */
