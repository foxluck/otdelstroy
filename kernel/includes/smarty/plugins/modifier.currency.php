<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty currency modifier plugin
 *
 * Type:     modifier<br>
 * Name:    currency<br>
 * Date:     Feb 24, 2003
 * Purpose:  print amount in currency display template. If currency iso3 is empty use current selected currency
 * Input:    string currency iso3 code 
 * Example:  {$var|currency:"USD"}
 */
function smarty_modifier_currency($amount, $currency_iso_3){

	if(!$currency_iso_3){
		global $selected_currency_details;
		$currency = $selected_currency_details;
	}else{
		$currency = currGetCurrencyByISO3($currency_iso_3);
	}
	return sprintf($currency['display_template'], $amount);
}

/* vim: set expandtab: */

?>