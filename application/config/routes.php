<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/
$route['default_controller'] = "login";
$route['no_access/(:any)'] = "no_access/index/$1";

$route['reports/(summary_transactions)'] = "reports/date_input_excel_export";
$route['reports/(summary_transactions_graphical)'] = "reports/date_input_excel_export";

//testmodif
//ligne modifiée
$route['reports/(summary_transations_ticket_z)'] = "reports/date_input_excel_exportZ";
//ligne modifiée

//$route['reports/(inventory_nosale)'] = "reports/excel_export";
//$route['reports/(inventory_slowmoving)'] = "reports/excel_export";
//$route['reports/(inventory_invalid_item_number)'] = "reports/excel_export";

$route['reports/detailed_transactions'] = "reports/date_input_excel_export";

$route['reports/fdj_reporting'] = "reports/date_input_excel_export";

//$route['reports/specific_customer'] = "reports/specific_customer_input";
//$route['reports/specific_employee'] = "reports/specific_employee_input";
//$route['reports/specific_item'] = "reports/specific_item_input";

$route['scaffolding_trigger'] = "";

// API Mobile routes
$route['api_mobile/session/(:num)/item'] = 'api_mobile/session_item/$1';
$route['api_mobile/session/(:num)/complete'] = 'api_mobile/session_action/$1/complete';
$route['api_mobile/session/(:num)/cancel'] = 'api_mobile/session_action/$1/cancel';
$route['api_mobile/session/(:num)'] = 'api_mobile/session/$1';

$route['404_override'] = 'errors/page_missing';

/* End of file routes.php */
/* Location: ./application/config/routes.php */
