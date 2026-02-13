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
		$fh = @fopen("/dev/ttyUSB0", "w");
		return $fh;
	}

	function clear_display($fh)
	{
		fwrite($fh, "\x1b\x40");  // ESC @ init
		fwrite($fh, "\x0c");      // clear
		return;
	}

	function welcome($fh)
	{
		fwrite($fh, $this->CI->lang->line('common_welcome'));
		fwrite($fh, $this->CI->lang->line('common_space'));
		fwrite($fh, $this->CI->lang->line('common_to'));
		fwrite($fh, "\x1F\x24\x01\x02");  // cursor line 2
		fwrite($fh, $this->CI->config->item('company'));
		fwrite($fh, $this->CI->lang->line('common_space'));
		fwrite($fh, date("d/m/Y"));
		return;
	}

	function show_cart($fh)
	{
		$cart_last	=	array();
		$cart_last	=	end($_SESSION['CSI']['CT']);
		$item_name	=	ellipsize($cart_last->name, 17, .5, '..');
		$item_ttc	=	round($cart_last->line_valueAD_TTC, 2);

		fwrite($fh, $item_name);
		fwrite($fh, "\x1F\x24\x01\x02");
		fwrite($fh, $this->CI->lang->line('common_pole_qty'));
		fwrite($fh, $cart_last->line_quantity);
		fwrite($fh, $this->CI->lang->line('common_space'));
		fwrite($fh, $this->CI->lang->line('common_pole_price'));
		fwrite($fh, $item_ttc);
		return;
	}

	function show_total($fh)
	{
		$cust_name	=	ellipsize($_SESSION['CSI']['SHV']->customer_formatted, 17, .5, '..');
		fwrite($fh, $cust_name);
		fwrite($fh, "\x1F\x24\x01\x02");
		fwrite($fh, $this->CI->lang->line('reports_total_only').$this->CI->lang->line('common_space').$this->CI->lang->line('reports_TTC'));
		fwrite($fh, $this->CI->lang->line('common_space'));
		fwrite($fh, round($_SESSION['CSI']['SHV']->header_valueAD_TTC, 2));
		return;
	}

	function close($fh)
	{
		fclose($fh);
		return;
	}

	function language($fh)
	{
		// ESC/POS mode — character set handled by ESC @ init in clear_display()
		return;
	}
}
?>
