<?php
class Pole_display
{
	var $CI;

  	function __construct()
	{
		$this->CI =& get_instance();
		}
	
	function open()
	{
		$usb 															=	'ttyUSB0';        
		$fh 															=	fopen("/dev/$usb", "w");
		return																$fh;
	}
	
	function clear_display($fh)
	{
		fwrite($fh, "\x0C"); 											// clear display and set cursor to home position = top left
		return;
	}
	
	function welcome($fh)
	{
		fwrite($fh, $this->CI->lang->line('common_welcome'));			// output welcome
		fwrite($fh, $this->CI->lang->line('common_space'));				// output space
		fwrite($fh, $this->CI->lang->line('common_to'));				// output to
		fwrite($fh, "\x1F\x24\1\2");									// set cursor to beginning second line
		fwrite($fh, $this->CI->config->item('company'));				// company
		fwrite($fh, $this->CI->lang->line('common_space'));				// output space	
		fwrite($fh, date("d/m/Y"));										// output date
		return;
	}
	
	function show_cart($fh)
	{
		$cart_last	=	array();										// initialise last item added array
		$cart_last	=	end($_SESSION['CSI']['CT']);					// get last item added
		$item_name	=	ellipsize($cart_last->name, 17, .5, '..');		// ellipsize name
		$item_ttc	=	round($cart_last->line_valueAD_TTC, 2);
		
		fwrite($fh, $item_name);										// output item name
		fwrite($fh, "\x1F\x24\1\2");									// set cursor to beginning second line
		fwrite($fh, $this->CI->lang->line('common_pole_qty'));			// output quantity text
		fwrite($fh, $cart_last->line_quantity);							// output quantity
		fwrite($fh, $this->CI->lang->line('common_space'));				// output space
		fwrite($fh, $this->CI->lang->line('common_pole_price'));		// output price text
		fwrite($fh, $item_ttc);											// output price		
		return;
	}
	
	function show_total($fh)
	{
		$cust_name	=	ellipsize($_SESSION['CSI']['SHV']->customer_formatted, 17, .5, '..');		// ellipsize name
		fwrite($fh, $cust_name);										// output customer name
		fwrite($fh, "\x1F\x24\1\2");									// set cursor to beginning second line
		fwrite($fh, $this->CI->lang->line('reports_total_only').$this->CI->lang->line('common_space').$this->CI->lang->line('reports_TTC'));				// output title
		fwrite($fh, $this->CI->lang->line('common_space'));				// output space
		fwrite($fh, round($_SESSION['CSI']['SHV']->header_valueAD_TTC, 2));									// cart total
		return;
	}
	
	function close($fh)
	{
		fclose($fh);
		return;
	}
	
	function language($fh)
	{
		fwrite($fh, "\x1B\x52\1"); 										// set language to local language
		fwrite($fh, "\x1B\x74\1"); 										// set language to local language
		return;
	}
}
?>
