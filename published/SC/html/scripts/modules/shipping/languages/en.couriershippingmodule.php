<?php
define('COURIER_TTL', 'Ground shipping');
define('COURIER_DSCR', 'This module allows you to define shipping rates for a certain state/country based on order amount');

define('COURIER_CFG_COUNTRY_TTL', 'Country');
define('COURIER_CFG_COUNTRY_DSCR', 'Please select country from the list');

define('COURIER_CFG_ZONE_TTL', 'State');
define('COURIER_CFG_ZONE_DSCR', 'Select state where the module will function. For any other state this module restricts shipping (simply makes shipping unavailable to any other state)');

define('COURIER_CFG_RATES_TTL', 'Shipping charges');
define('COURIER_CFG_RATES_DSCR', 'Please define "pairs" (order_amount, shipping_charge). Each pair indicates applicable shipping_charge if order amount is lower than specified order_amount. For all orders with amount greater than the maximum provided, shipping charge will be suppressed');

define('COURIER_TXT_AMOUNT', 'Order amount (your default currency)');

define('COURIER_TXT_COST', 'Shipping charge applicable if order amount is lower than specified. You may input whether a fixed charge or a percent of order amount. E.g. "10" will indicate fixed shipping rate, and "10%" will calculate shipping charge as 10% of order amount.');
?>