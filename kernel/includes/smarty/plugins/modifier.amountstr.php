<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty amountstr modifier plugin
 *
 * Type:     modifier<br>
 * Name:     amountstr<br>
 * Date:     Feb 24, 2003
 * Purpose:  Handle variable by function
 * Input:    amount, currency iso3
 */
function smarty_modifier_amountstr($Amount, $CurrencyISO3){

	return Currency::getCurrencyDisplayStr($Amount, $CurrencyISO3);
}

/* vim: set expandtab: */

?>