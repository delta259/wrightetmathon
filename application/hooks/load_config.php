<?php
//Loads configuration from database into global CI config
function load_config()
{
	$CI =& get_instance();

	// Ensure session object keys are always initialised (PHP 8 compatibility)
	// PHP 7 silently auto-created stdClass on null; PHP 8 throws a fatal error
	foreach (array('G', 'C', 'M', 'kit_info') as $_sk) {
		if (!isset($_SESSION[$_sk]) || !is_object($_SESSION[$_sk])) {
			$_SESSION[$_sk] = new stdClass();
		}
	}
	unset($_sk);
	// transaction_info can be either stdClass or array (e.g. cashtills currency list)
	if (!isset($_SESSION['transaction_info'])) {
		$_SESSION['transaction_info'] = new stdClass();
	}
	// CSI is an array (Sales controller), not a stdClass
	if (!isset($_SESSION['CSI']) || !is_array($_SESSION['CSI'])) {
		$_SESSION['CSI'] = [];
	}
	// Ensure CSI sub-keys exist (PHP 8: cannot assign property on null / use [] on null)
	// stdClass sub-keys
	foreach (array('SHV', 'TT', 'EI') as $_ck) {
		if (!isset($_SESSION['CSI'][$_ck]) || !is_object($_SESSION['CSI'][$_ck])) {
			$_SESSION['CSI'][$_ck] = new stdClass();
		}
	}
	// Array sub-keys
	foreach (array('CT', 'PD', 'PM') as $_ck) {
		if (!isset($_SESSION['CSI'][$_ck]) || !is_array($_SESSION['CSI'][$_ck])) {
			$_SESSION['CSI'][$_ck] = [];
		}
	}
	unset($_ck);

	// Initialize common pick lists on $_SESSION['G'] if not set (PHP 8 compatibility)
	// These are normally set during login but may not exist for direct page access
	if (!isset($_SESSION['G']->YorN_pick_list)) {
		$_SESSION['G']->YorN_pick_list = array('Y' => 'Oui', 'N' => 'Non');
	}
	if (!isset($_SESSION['G']->oneorzero_pick_list)) {
		$_SESSION['G']->oneorzero_pick_list = array('1' => 'Oui', '0' => 'Non');
	}
	if (!isset($_SESSION['G']->sex_pick_list)) {
		$_SESSION['G']->sex_pick_list = array('F' => 'Féminin', 'M' => 'Masculin');
	}

	// Default session scalars used throughout the application (PHP 8 compatibility)
	$session_defaults = array(
		'undel' => 0,
		'del' => 0,
		'new' => 0,
		'show_dialog' => 0,
		'first_time' => 0,
		'line_count' => 0,
		'line_colour' => 'white',
		'controller_name' => '',
		'url_image' => 'images',
		'origin' => '',
		'module_id' => '',
		'transaction_id' => '',
		'$title' => '',
		'line_number' => 0,
		'error_code' => '',
		'report_controller' => '',
	);
	foreach ($session_defaults as $key => $default_value) {
		if (!isset($_SESSION[$key])) {
			$_SESSION[$key] = $default_value;
		}
	}

	foreach( $CI->Appconfig->get_all()->result() as $app_config )
	{
		$CI->config->set_item( $app_config->key, $app_config->value );
	}
	
	if ( $CI->config->item( 'language' ) )
	{
		$CI->config->set_item( 'language', $CI->config->item( 'language' ) );
    $loaded = $CI->lang->is_loaded;
    $CI->lang->is_loaded = array();

    foreach($loaded as $file)
    {
        $CI->lang->load( str_replace( '_lang.php', '', $file ) );    
    }
	}
	
	$tz = $CI->config->item( 'timezone' );
	if ( $tz && @date_default_timezone_set( $tz ) === false )
	{
		// Invalid timezone ID in database (e.g. '1') — fall back to default
		date_default_timezone_set( 'Europe/Paris' );
	}
	elseif ( !$tz )
	{
		date_default_timezone_set( 'Europe/Paris' );
	}
}
?>
