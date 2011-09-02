<?php
define('COURIER2_TTL', 'Ground shipping (by weight)');
define('COURIER2_DSCR', 'This module allows you to define shipping rates for a certain state/country based on order total weight');

define('COURIER2_CFG_COUNTRY_TTL', 'Country');
define('COURIER2_CFG_COUNTRY_DSCR', 'Please select country from the list');

define('COURIER2_CFG_ZONE_TTL', 'State');
define('COURIER2_CFG_ZONE_DSCR', 'Select state where the module will function. For any other state this module restricts shipping (simply makes shipping unavailable to any other state)');

define('COURIER2_CFG_RATES_TTL', 'Shipping charges');
define('COURIER2_CFG_RATES_DSCR', 'Please define "pairs" (order_weight, shipping_charge). Each pair indicates applicable shipping_charge if order weight is lower than specified order_weight. For all orders with amount greater than the maximum provided, shipping charge will be suppressed');

define('COURIER2_TXT_AMOUNT', 'Order weight');

define('COURIER2_TXT_COST', 'Shipping charge applicable if order weight is lower than specified. You may input whether a fixed charge or a percent of order amount. E.g. "10" will indicate shipping rate in your default currency, and "10%" will calculate shipping charge as 10% of order amount.');
?>