<?php
/** GARRISON MODIFIED 4/20/2013 **/
function to_currency($number)
{
	// add currency symbol to output depending on side
	if($number >= 0)
	{
		switch($_SESSION['G']->currency_details->currency_side)
		{
			case	'L':
					return $_SESSION['G']->currency_details->currency_sign.number_format($number, 2, '.', '');
					break;
					
			case	'R':		
					return number_format($number, 2, '.', '').$_SESSION['G']->currency_details->currency_sign;
					break;
		}
	}
    else
    {
    	switch($_SESSION['G']->currency_details->currency_side)
		{
			case	'L':
					return $_SESSION['G']->currency_details->currency_sign.number_format($number, 2, '.', '');
					break;
					
			case	'R':		
					return number_format($number, 2, '.', '').$_SESSION['G']->currency_details->currency_sign;
					break;
		}
    }
}
/** END MODIFIED **/

function to_currency_no_money($number)
{
	return number_format($number, 2, '.', '');
}
?>
