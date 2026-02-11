<?php
class Common_controller extends CI_Controller
{
	function index()
	{


	}

	// manage SESSION and return to controller
	function common_exit()
	{
		unset($_SESSION['error_code']);
		unset($_SESSION['show_dialog']);
		unset($_SESSION['next_action']);
		unset($_SESSION['new']);
		unset($_SESSION['clone']);
		unset($_SESSION['merge']);
		unset($_SESSION['merge_ok']);
		unset($_SESSION['del']);
		unset($_SESSION['undel']);
		unset($_SESSION['show_permissions']);
		unset($_SESSION['all_modules']);
		unset($_SESSION['permissions_data']);
		unset($_SESSION['first_time']);
		unset($_SESSION['correction']);
		unset($_SESSION['supplier_view']);
		unset($_SESSION['confirm_what']);
		if(isset($_SESSION['var_annulation_facture']))
		{
			unset($_SESSION['var_annulation_facture']);
		}
		if(isset($_SESSION['var_annulation_facture_partielle']))
		{
			unset($_SESSION['var_annulation_facture_partielle']);
		}
		$_SESSION['submit']	=	$this->lang->line('common_submit');	
		
		// redirect depending on origin
		switch ($_SESSION['origin'])
		{
            // from administration systeme
            case	'AS':
                unset($_SESSION['origin']);
                redirect("home/admin");
                return;
                break;

            // from Invalid Item (article invalid)
            case	'II':
                unset($_SESSION['origin']);
                redirect("/reports/inventory_invalid_item_number");
                return;
                break;

			// from receivings
			case	'DR':
					unset($_SESSION['origin']);
					redirect("receivings/reload");
					return;
			break;
			
			// from recevings / stock action
			case	'SA':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
                    unset($_SESSION['stock_action_id']);
					redirect("/reports/inventory_rolling");
					return;
			break;
			
			// from recevings / inventory low
			case	'CA':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					unset($_SESSION['stock_action_id']);
					redirect("receivings");
					return;
			break;
			
			// from sales
			case	'SS':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					unset($_SESSION['transaction_data_set']);
					$_SESSION['CSI']['SHV']->customer_id				=	$_SESSION['customer_id'];
					redirect("sales/customer_select/".'RC');
					return;
			break;
			
			// from sales
			case	'CN':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					unset($_SESSION['transaction_data_set']);
					$_SESSION['CSI']['SHV']->customer_id				=	$_SESSION['customer_id'];
					
					redirect("sales/reload/");
					return;
			break;
			
			// from rolling inventory report
			case	'IR':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']								=	"21";
					redirect("/reports/inventory_rolling");
					return;
			break;
			
			// from dluo quantity errors
			case	'DL':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']								=	"21";
					redirect("/reports/dluo_qty_error");
					return;
			break;
			
			// from dluo negative stock
			case	'NS':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']								=	"21";
					redirect("/reports/inventory_negative_stock");
					return;
			break;
			
			// from dluo past date
			case	'DD':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']								=	"21";
					redirect("/reports/dluo_past_date");
					return;
			break;
			
			// from dluo past date + 2
			case	'DF':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']								=	"21";
					redirect("/reports/dluo_future_date");
					return;
			break;
			
			// from stock summary
			case	'IS':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']								=	"21";
					redirect("/reports/inventory_summary");
					return;
			break;
			
			// from specific Item
			case	'SI':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']								=	"21";
					redirect("/reports/specific_item");
					return;
			break;
			
			// from low inventory
			case	'IL':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']								=	"21";
					redirect("reports/inventory_low");
					return;
			break;
			
			// from kit detail 
			case	'KD':
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']								=	"15";
					redirect("items/kit/".$_SESSION['kit_info']->item_info->item_id);
					return;
			break;

			case    'RR':
         			unset($_SESSION['title']);
					unset($_SESSION['origin']);
					$_SESSION['module_id']                              =    "15";
					redirect('receivings');
					return;

			// form items
			case 'IA':
				unset($_SESSION['title']);
			    unset($_SESSION['origin']);
			    redirect('items');
			    return;

			// from categories
			case 'CG':
				unset($_SESSION['title']);
				unset($_SESSION['origin']);
				redirect('categories');
				return;

			// from item_kits
			case 'IK':
				unset($_SESSION['title']);
				unset($_SESSION['origin']);
				redirect('item_kits');
				return;

			/*//Si le client veut recevoir le ticket de caisse par mail et que son mail n'est pas dans la base de donée,
			//alors le vendeur s'inscrit et la page est redirigé vers la vente en cours    //*/

			case 'IV':
				unset($_SESSION['title']);
				unset($_SESSION['origin']);
				redirect('inventaire');
				return;

			case    'ST':
			        unset($_SESSION['variable_tampon_booleens']);
                    unset($_SESSION['title']);
					unset($_SESSION['origin']);
					redirect('sales/payments');



			default:
					unset($_SESSION['title']);
					unset($_SESSION['origin']);
					if ($_SESSION['report_controller'] == 1)
					{
						unset($_SESSION['report_controller']);
						$_SESSION['controller_name']					=	'reports';
						$_SESSION['module_id']							=	"21";
					}	
					if(($_SESSION['controller_name']=='items_desactives') || ($_SESSION['controller_name']=='items_actifs') || ($_SESSION['controller_name']=='items_news') )
					{
						$_SESSION['controller_name']='items';
					}
					if(($_SESSION['controller_name']=='customers_actifs') || ($_SESSION['controller_name']=='customers_inactifs') )
					{
						$_SESSION['controller_name']='customers';
					}
					if($_SESSION['redirection']== "sales" )
					{
						unset($_SESSION['redirection']);
					    redirect("sales");
					}
					if($_SESSION['redirection']== "receivings" )
					{
						unset($_SESSION['redirection']);
					    redirect("receivings");
					}
					
					redirect($_SESSION['controller_name']);
					return;
			break;
		}	
	}
}
?>
