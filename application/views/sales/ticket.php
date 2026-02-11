<?php

// this view prints a ticket on a thermal printer such as the Epson TM-T88V.
// it uses the Epson printing language ESC/POS to do so.
// any thermal printer that understands ESC/POS codes can be used.
// the paper must be 80mm wide.
// the ESC/POS language manual is in the Hardware directory under application

// initialse method

//$_SESSION['CSI']['SHV']->customer_formatted contient une chaîne de caractére avec "nom, prénom"
//Donc $prenom récupére le prénom du client à qui il faut envoyer le ticket de caisse par mail
list($nom, $prenom)=explode(', ',$_SESSION['CSI']['SHV']->customer_formatted);

//Affichage du logo de l'enseigne en fonction du nom: Sonrisa ou Yes store
if($_SESSION['url_image']=='images_sonrisa')
{
	$enseigne='SONRISA';
}
if($_SESSION['url_image']=='images_yes')
{
	$enseigne='YES STORE';
}

//Obtention et stockage du numéro de ticket et de l'id_client
$_SESSION['numero_ticket']=$_SESSION['CSI']['SHV']->sale_id;
$_SESSION['id_client']=$_SESSION['CSI']['SHV']->customer_id;

//=== EMAIL: structure HTML ===
$_SESSION['message_mail'] = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
$_SESSION['message_mail'] .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
$_SESSION['message_mail'] .= '<title>Ticket de caisse</title></head>';
$_SESSION['message_mail'] .= '<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">';
$_SESSION['message_mail'] .= '<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:20px 0;">';
$_SESSION['message_mail'] .= '<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">';

//=== EMAIL: Logo + salutation ===
$_SESSION['message_mail'] .= '<tr><td style="padding:30px 30px 15px;text-align:center;">';
if($_SESSION['url_image']=='images_sonrisa')
{
	$_SESSION['message_mail'] .= '<img src="https://www.sonrisa-smile.com/img/cms/Header.jpg" width="720" height="162" alt="Sonrisa" style="display:block;margin:0 auto 15px;max-width:100%;height:auto;" />';
}
if($_SESSION['url_image']=='images_yes')
{
	$_SESSION['message_mail'] .= '<img src="https://www.yesstore.fr/img/yes-store-logo-1617118651.jpg" width="184" height="140" alt="Yes Store" style="display:block;margin:0 auto 15px;" />';
}
$_SESSION['message_mail'] .= '<p style="font-size:16px;color:#333333;margin:10px 0 5px;">Bonjour ' . $prenom . ',</p>';
$_SESSION['message_mail'] .= '<p style="font-size:14px;color:#666666;margin:0;">Merci pour votre derni&egrave;re visite dans notre boutique ' . $enseigne . '.<br>Vous trouverez ci-dessous le d&eacute;tail de vos achats effectu&eacute;s ce jour.<br>&Agrave; tr&egrave;s vite dans l\'une de nos boutiques.</p>';
$_SESSION['message_mail'] .= '</td></tr>';


$ESC 																	=	"\x1b";
$GS																		= 	"\x1d";
$NULL																	= 	"\x00";
$this																	->	load->helper('text');

// we need to search and replace special language characters in order to get them to print.
// replace array is always the same while the search array depends on language
$replace																=	array('#', '$', '@', '[', '\\', ']', '^', '`', '{', '|', '}', '~', 'o');
$replace_mail																=	array('&#35', '&#36', '&#224', '&#176', '&#231', '&#167', '&#136', '&#8171', '&#233', '&#249', '&#232', '&#34', '&#244');


// initialise the printer
fwrite($_SESSION['CSI']['SHV']->ph, $ESC."@");

// set international character set; current only French is handled.
// to handle a different language add a case for that lanugauge.
switch ($this->config->item('language'))
{
	case	'French':
			$search														=	array('#', '$', 'à', '°', 'ç', '§', '^', '`', 'é', 'ù', 'è', '"', 'ô');
			fwrite($_SESSION['CSI']['SHV']->ph, $ESC."t".chr(4));								// set code page
			fwrite($_SESSION['CSI']['SHV']->ph, $ESC."R".chr(1));								// set international character set.
			break;

	default:
			break;
}


//
//
// print ticket header eg name, address, telephone etc - all defined in the confirguration

fwrite($_SESSION['CSI']['SHV']->ph, $ESC."a".chr(1)); 					// centre
fwrite($_SESSION['CSI']['SHV']->ph, $ESC."E".chr(1)); 					// bold on
fwrite($_SESSION['CSI']['SHV']->ph, $ESC."-".chr(1)); 					// underline on

//=== EMAIL: Informations société ===
$_SESSION['message_mail'] .= '<tr><td style="padding:15px 30px;text-align:center;border-bottom:1px solid #eeeeee;">';
$_SESSION['message_mail'] .= '<strong style="font-size:16px;">';

$formfeed																=	2;
$subject																=	$this->config->item('company');
$this																	->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);    //écriture du nom de la compagny: Yes Store Troyes
fwrite($_SESSION['CSI']['SHV']->ph, $ESC."-".chr(0)); 					// underline off
fwrite($_SESSION['CSI']['SHV']->ph, $ESC."E".chr(0)); 					// bold off

$_SESSION['message_mail'] .= '</strong>';

$_SESSION['CSI']['SHV']->message=$subject;


$formfeed																=	1;
$subject																=	$this->config->item('address');
$this																	->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);



$formfeed																=	1;
$subject																=	$this->config->item('phone');
$this																	->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);



$formfeed																=	1;
$subject																=	$this->lang->line('config_company_registration_number').$this->lang->line('common_colon').$this->config->item('siret');
$this																	->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);



$formfeed																=	2;
$subject																=	$this->lang->line('config_company_tva_number').$this->lang->line('common_colon').$this->config->item('tva');
$this																	->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);


$formfeed																=	1;
$subject																=	$this->lang->line('config_shop_open_hours');
$this																	->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);



$formfeed																=	2;
$subject																=	$this->config->item('open_hours');
$this																	->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);

$_SESSION['message_mail'] .= '</td></tr>';


//=== EMAIL: Informations vente ===
$_SESSION['message_mail'] .= '<tr><td style="padding:10px 30px;font-size:13px;">';

// employee number - comes from invoice. Invoice data is loaded in the sales controller and passed in the $data array to this view

//Pour l'affichage du nom du vendeur qui effectue la vente
list($nom_employee, $prenom_employee) = explode(", ", $_SESSION['CSI']['SHV']->employee_formatted);
if((isset($nom_employee)) && (isset($prenom_employee)))
{
    $employee_id_and_name=$_SESSION['CSI']['SHV']->employee_id . ' : ' . $prenom_employee;
}

$formfeed																=	1;
$subject																=	$this->lang->line('employees_soldby').$this->lang->line('common_colon').$employee_id_and_name;
$this																	->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);



if(isset($_SESSION['CSI']['SHV']->customer_id))
{
	$formfeed															=	1;
	$subject															=	$this->lang->line('customers_customer').$this->lang->line('common_colon').$_SESSION['CSI']['SHV']->customer_formatted;
	$this																->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);
}



// invoice number, date, time
$formfeed																=	2;
$subject																=	$_SESSION['CSI']['SHV']->sale_id.' '.$_SESSION['CSI']['SHV']->transaction_time;
$this																	->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);

$_SESSION['message_mail'] .= '</td></tr>';


// Prepare header data - head 1
fwrite($_SESSION['CSI']['SHV']->ph, $ESC."a".chr(0)); 					// flush left

//=== EMAIL: Tableau des articles ===
$_SESSION['message_mail'] .= '<tr><td style="padding:10px 30px;">';
$_SESSION['message_mail'] .= '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:13px;">';
$_SESSION['message_mail'] .= '<tr style="background-color:#f0f0f0;">';
$_SESSION['message_mail'] .= '<th align="left" style="padding:8px;border-bottom:2px solid #333333;">Code</th>';
$_SESSION['message_mail'] .= '<th align="left" style="padding:8px;border-bottom:2px solid #333333;">Article</th>';
$_SESSION['message_mail'] .= '<th align="right" style="padding:8px;border-bottom:2px solid #333333;">Qt&eacute;</th>';
$_SESSION['message_mail'] .= '<th align="right" style="padding:8px;border-bottom:2px solid #333333;">Prix TTC</th>';
$_SESSION['message_mail'] .= '<th align="right" style="padding:8px;border-bottom:2px solid #333333;">Rem.%</th>';
$_SESSION['message_mail'] .= '<th align="right" style="padding:8px;border-bottom:2px solid #333333;">Total TTC</th>';
$_SESSION['message_mail'] .= '</tr>';

// PRINTER: header line 1
$a																		=	sprintf("%-10s", str_replace($search, $replace, $this->lang->line('items_item_code')));
$b																		=	sprintf("%-30s", str_replace($search, $replace, $this->lang->line('items_item')));
$head1																	=	$a.$b;
fwrite($_SESSION['CSI']['SHV']->ph, $head1);
fwrite($_SESSION['CSI']['SHV']->ph,	$ESC."d".chr(1));

// PRINTER: header line 2
$a																		=	sprintf("%10s", str_replace($search, $replace, $this->lang->line('sales_quantity')));
$b																		=	sprintf("%10s", str_replace($search, $replace, $this->lang->line('sales_price').$this->lang->line('common_space').$this->lang->line('sales_TTC')));
$c																		=	sprintf("%10s", str_replace($search, $replace, $this->lang->line('sales_discount').$this->lang->line('common_percent')));
$d																		=	sprintf("%12s", str_replace($search, $replace, $this->lang->line('common_total').$this->lang->line('common_space').$this->lang->line('sales_TTC')));
$head2																	=	$a.$b.$c.$d;
fwrite($_SESSION['CSI']['SHV']->ph, $head2);
fwrite($_SESSION['CSI']['SHV']->ph,	$ESC."d".chr(1));

// print details
foreach ($_SESSION['CSI']['CT'] as $line => $cart_line)
{
	//suppression des articles du kit  $_SESSION['CSI']['CT'][$line]->off = '1';
	if($_SESSION['CSI']['CT'][$line]->off != '1')
	{
    	// PRINTER: item number and description
    	$title																=	ellipsize($cart_line->name, 29, .5, '...');
    	$a																	=	sprintf("%-10s", $cart_line->item_number);
		$b																	=	sprintf("%-30s", $title);
		$b = str_replace($search, $replace, $b);
    	$line1																=	$a.$b;
    	fwrite($_SESSION['CSI']['SHV']->ph, $line1);
    	fwrite($_SESSION['CSI']['SHV']->ph,	$ESC."d".chr(1)); 				// form feed

    	// PRINTER: qty, unit price, discount, total
    	$a																	=	sprintf("%10.2f", $cart_line->line_quantity);
    	$b																	=	sprintf("%10.2f", $cart_line->line_priceTTC);
    	$c																	=	sprintf("%10.2f", $cart_line->line_discount);
    	$d																	=	sprintf("%12.2f", round($cart_line->line_valueAD_TTC, 2));
    	$line2																=	$a.$b.$c.$d;
    	fwrite($_SESSION['CSI']['SHV']->ph, $line2);
    	fwrite($_SESSION['CSI']['SHV']->ph,	$ESC."d".chr(1));

		// EMAIL: ligne article
		$_SESSION['message_mail'] .= '<tr style="border-bottom:1px solid #eeeeee;">';
		$_SESSION['message_mail'] .= '<td style="padding:6px 8px;font-size:12px;color:#999999;">' . $cart_line->item_number . '</td>';
		$_SESSION['message_mail'] .= '<td style="padding:6px 8px;">' . $cart_line->name . '</td>';
		$_SESSION['message_mail'] .= '<td align="right" style="padding:6px 8px;">' . number_format($cart_line->line_quantity, 2, ',', '') . '</td>';
		$_SESSION['message_mail'] .= '<td align="right" style="padding:6px 8px;">' . number_format($cart_line->line_priceTTC, 2, ',', ' ') . ' &euro;</td>';
		$_SESSION['message_mail'] .= '<td align="right" style="padding:6px 8px;">' . number_format($cart_line->line_discount, 2, ',', '') . '%</td>';
		$_SESSION['message_mail'] .= '<td align="right" style="padding:6px 8px;font-weight:bold;">' . number_format(round($cart_line->line_valueAD_TTC, 2), 2, ',', ' ') . ' &euro;</td>';
		$_SESSION['message_mail'] .= '</tr>';
	}
}

// EMAIL: fermer tableau articles
$_SESSION['message_mail'] .= '</table>';
$_SESSION['message_mail'] .= '</td></tr>';

// EMAIL: ouvrir section totaux
$_SESSION['message_mail'] .= '<tr><td style="padding:10px 30px;">';
$_SESSION['message_mail'] .= '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;">';

// Prepare total
$type																	=	'2';
$title																	=	' ';
$value																	=	'----------';
$this																	->	Sale->write_line($_SESSION['CSI']['SHV']->ph, $ESC, $title, $value, $type);



$type																	=	'1';
$title																	=	str_replace($search, $replace, $this->lang->line('reports_total'));
$value																	=	$_SESSION['CSI']['SHV']->header_valueAD_TTC;
$this																	->	Sale->write_line($_SESSION['CSI']['SHV']->ph, $ESC, $title, $value, $type);



$type																	=	'2';
$title																	=	' ';
$value																	=	'----------';
$this																	->	Sale->write_line($_SESSION['CSI']['SHV']->ph, $ESC, $title, $value, $type);



// Print HT
$type																	=	'1';
$title																	=	str_replace($search, $replace, $this->lang->line('reports_subtotal'));
$value																	=	$_SESSION['CSI']['SHV']->header_valueAD_HT;
$this																	->	Sale->write_line($_SESSION['CSI']['SHV']->ph, $ESC, $title, $value, $type);


foreach($_SESSION['TVA'] as $_percent_tax => $value_tax_tva)
{
	if(!empty($value_tax_tva))
	{
        // Print tax
        $type																	=	'1';
        $title																	=	str_replace($search, $replace, $_SESSION['CSI']['SHV']->tax_name).' '.$_percent_tax.$this->lang->line('common_percent');
        $value																	=	$value_tax_tva;  //$_SESSION['CSI']['SHV']->header_taxAD;
        $this																	->	Sale->write_line($_SESSION['CSI']['SHV']->ph, $ESC, $title, $value, $type);
    }
}

// Print line
$type																	=	'2';
$title																	=	' ';
$value																	=	'----------';
$this																	->	Sale->write_line($_SESSION['CSI']['SHV']->ph, $ESC, $title, $value, $type);


if(isset($_SESSION['CSI']['PD'][10]) && isset($_SESSION['CSI']['PD'][2]))
{
	$_SESSION['CSI']['PD'][2]->payment_amount_TTC = $_SESSION['CSI']['PD'][2]->payment_amount_TTC - $_SESSION['CSI']['PD'][10]->payment_amount_TTC;
}

// Print payments
foreach ($_SESSION['CSI']['PD'] as $pmi => $payment)
{
	$type																=	'1';
	$title																=	str_replace($search, $replace, $payment->payment_method_description);
	$value																=	$payment->payment_amount_TTC;
	$this																->	Sale->write_line($_SESSION['CSI']['SHV']->ph, $ESC, $title, $value, $type);
}

// EMAIL: fermer section totaux
$_SESSION['message_mail'] .= '</table>';
$_SESSION['message_mail'] .= '</td></tr>';


fwrite($_SESSION['CSI']['SHV']->ph,	$ESC."d".chr(1)); 					// form feed



// center text
fwrite($_SESSION['CSI']['SHV']->ph, $ESC."a".chr(1)); 					// centre

//=== EMAIL: Messages de bas de ticket ===
$_SESSION['message_mail'] .= '<tr><td style="padding:15px 30px;text-align:center;font-size:13px;color:#666666;">';

// print season message
if (!empty($this->config->item('season_message')))
{
	fwrite($_SESSION['CSI']['SHV']->ph, $ESC."E".chr(1)); 				// bold on
	fwrite($_SESSION['CSI']['SHV']->ph, $ESC."-".chr(1)); 				// underline on
	$formfeed															=	2;
	$subject															=	$this->config->item('season_message');
	$this																->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);
	fwrite($_SESSION['CSI']['SHV']->ph, $ESC."-".chr(0)); 				// underline off
	fwrite($_SESSION['CSI']['SHV']->ph, $ESC."E".chr(0)); 				// bold off
}


// print polite message
if (!empty($this->config->item('polite_message')))
{
	$formfeed															=	2;
	$subject															=	$this->config->item('polite_message');
	$this																->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);
}


// print fidelity amount this client if eligible or fidelity message
if ($_SESSION['CSI']['CI']->fidelity_flag == 'Y')
{
	$type																=	'1';
	$title																=	str_replace($search, $replace, $this->lang->line('sales_fidelity_status').' '.$_SESSION['CSI']['CuI']->currency_name);
	$value																=	$_SESSION['CSI']['SHV']->fidelity_value;
	$this																->	Sale->write_line($_SESSION['CSI']['SHV']->ph, $ESC, $title, $value, $type);
	fwrite($_SESSION['CSI']['SHV']->ph,	$ESC."d".chr(1)); 				// form feed
}
else
{
	if (!empty($this->config->item('fidelity_message')) AND $_SESSION['CSI']['SHV']->default_profile_flag == 1)
	{
		$formfeed														=	2;
		$subject														=	$this->config->item('fidelity_message');
		$this															->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);
	}
}


// print returns policy
if (!empty($this->config->item('return_policy')))
{
	$formfeed															=	2;
	$subject															=	$this->config->item('return_policy');
	$this																->	Sale->write_title($subject, $_SESSION['CSI']['SHV']->ph, $ESC, $search, $replace, $formfeed);
}

// EMAIL: fermer section messages
$_SESSION['message_mail'] .= '</td></tr>';

unset($_SESSION['CSI']['PD'][10]);


// print bar code
// set up printer for barcode
fwrite($_SESSION['CSI']['SHV']->ph, $GS."H".chr(2));					// print HRI below the bar code
fwrite($_SESSION['CSI']['SHV']->ph, $GS."h".chr(50));					// set height of barcode
fwrite($_SESSION['CSI']['SHV']->ph, $GS."w".chr(3));					// set width of barcode
fwrite($_SESSION['CSI']['SHV']->ph, $GS."k".chr(4).$_SESSION['CSI']['SHV']->sale_id.$NULL);	// print bar code



// cut paper
fwrite($_SESSION['CSI']['SHV']->ph,	$ESC."d".chr(10)); 					// form feed
fwrite($_SESSION['CSI']['SHV']->ph, $GS."V".chr(1)); 					// cut

// Initialise printer
fwrite($_SESSION['CSI']['SHV']->ph, $ESC."@"); 							// initialise printer
?>
