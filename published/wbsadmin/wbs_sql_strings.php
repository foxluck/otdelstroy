<?php
$SQLstrings=array(
array('select'=>'SELECT `Amount`, `CurrencyISO3`, `CustomerID`, `Description`, `OrderID`, `cID`, `xDateTime` FROM `__temp_SS_aff_commissions`','insert'=>'INSERT INTO `SC_aff_commissions` (`Amount`, `CurrencyISO3`, `CustomerID`, `Description`, `OrderID`, `cID`, `xDateTime`) VALUES','data'=>array(0,1,0,1,0,0,1,),'prepare'=>'TRUNCATE `SC_aff_commissions`'),
array('select'=>'SELECT `Amount`, `CurrencyISO3`, `CustomerID`, `Description`, `pID`, `xDate` FROM `__temp_SS_aff_payments`','insert'=>'INSERT INTO `SC_aff_payments` (`Amount`, `CurrencyISO3`, `CustomerID`, `Description`, `pID`, `xDate`) VALUES','data'=>array(0,1,0,1,0,0,),'prepare'=>'TRUNCATE `SC_aff_payments`'),
array('select'=>'SELECT `aux_page_ID`,`aux_page_ID` as `aux_page_slug`, `aux_page_text_type`, `aux_page_name`, `aux_page_text`, `meta_description`, `meta_keywords`, 1 as \'aux_page_enabled\' FROM `__temp_SS_aux_pages`','insert'=>'INSERT INTO `SC_aux_pages` (`aux_page_ID`,`aux_page_slug`, `aux_page_text_type`, `aux_page_name_%DEF_LANG%`, `aux_page_text_%DEF_LANG%`, `meta_description_%DEF_LANG%`, `meta_keywords_%DEF_LANG%`,`aux_page_enabled`) VALUES','data'=>array(0,1,0,1,1,1,1,0,),'prepare'=>array('TRUNCATE `SC_aux_pages`','DELETE FROM `SC_divisions` WHERE `xUnicKey` LIKE \'auxpage_%\'','DELETE FROM `SC_division_interface` WHERE `xInterface` LIKE \'%_auxpage_%\'')),
array('select'=>'SELECT `allow_products_comparison`, `allow_products_search`, `categoryID`, `parent`, `picture`, `products_count`, `products_count_admin`, `show_subcategories_products`, `sort_order`, `viewed_times`, `description`, `meta_description`, `meta_keywords`, `name` FROM `__temp_SS_categories`','insert'=>'INSERT INTO `SC_categories` (`allow_products_comparison`, `allow_products_search`, `categoryID`, `parent`, `picture`, `products_count`, `products_count_admin`, `show_subcategories_products`, `sort_order`, `viewed_times`, `description_%DEF_LANG%`, `meta_description_%DEF_LANG%`, `meta_keywords_%DEF_LANG%`, `name_%DEF_LANG%`) VALUES','data'=>array(0,0,0,0,1,0,0,0,0,0,1,1,1,1,),'prepare'=>'TRUNCATE `SC_categories`'),
array('select'=>'SELECT `categoryID`, `optionID`, `set_arbitrarily` FROM `__temp_SS_category__product_options`','insert'=>'INSERT INTO `SC_category__product_options` (`categoryID`, `optionID`, `set_arbitrarily`) VALUES','data'=>array(0,0,0,),'prepare'=>'TRUNCATE `SC_category__product_options`'),
array('select'=>'SELECT `categoryID`, `productID` FROM `__temp_SS_category_product`','insert'=>'INSERT INTO `SC_category_product` (`categoryID`, `productID`) VALUES','data'=>array(0,0,),'prepare'=>'TRUNCATE `SC_category_product`'),
array('select'=>'SELECT `categoryID`, `optionID`, `variantID` FROM `__temp_SS_category_product_options__variants`','insert'=>'INSERT INTO `SC_category_product_options__variants` (`categoryID`, `optionID`, `variantID`) VALUES','data'=>array(0,0,0,),'prepare'=>'TRUNCATE `SC_category_product_options__variants`'),
array('select'=>'SELECT `countryID`, `country_iso_2`, `country_iso_3`, `country_name` FROM `__temp_SS_countries`','insert'=>'INSERT INTO `SC_countries` (`countryID`, `country_iso_2`, `country_iso_3`, `country_name_%DEF_LANG%`) VALUES','data'=>array(0,1,1,1,),'prepare'=>'TRUNCATE `SC_countries`'),
array('select'=>'SELECT `CID`, `code`, `currency_iso_3`, `currency_value`, `sort_order`, `where2show`, `Name`, IF(`where2show`=1,CONCAT(\'{value}\',`code`),CONCAT(`code`,\'{value}\')) as display_template FROM `__temp_SS_currency_types`','insert'=>'INSERT INTO `SC_currency_types` (`CID`, `code`, `currency_iso_3`, `currency_value`, `sort_order`, `where2show`, `Name_%DEF_LANG%`,`display_template_%DEF_LANG%`) VALUES','data'=>array(0,1,1,0,0,0,1,1,),'prepare'=>'TRUNCATE `SC_currency_types`'),
array('select'=>'SELECT `custgroupID`, `custgroup_discount`, `sort_order`, `custgroup_name` FROM `__temp_SS_custgroups`','insert'=>'INSERT INTO `SC_custgroups` (`custgroupID`, `custgroup_discount`, `sort_order`, `custgroup_name_%DEF_LANG%`) VALUES','data'=>array(0,0,0,1,),'prepare'=>'TRUNCATE `SC_custgroups`'),
array('select'=>'SELECT `address`, `addressID`, `city`, `countryID`, `customerID`, `first_name`, `last_name`, `state`, `zip`, `zoneID` FROM `__temp_SS_customer_addresses`','insert'=>'INSERT INTO `SC_customer_addresses` (`address`, `addressID`, `city`, `countryID`, `customerID`, `first_name`, `last_name`, `state`, `zip`, `zoneID`) VALUES','data'=>array(1,0,1,0,0,1,1,1,1,0,),'prepare'=>'TRUNCATE `SC_customer_addresses`'),
array('select'=>'SELECT `reg_field_ID`, `reg_field_required`, `sort_order`, `reg_field_name` FROM `__temp_SS_customer_reg_fields`','insert'=>'INSERT INTO `SC_customer_reg_fields` (`reg_field_ID`, `reg_field_required`, `sort_order`, `reg_field_name_%DEF_LANG%`) VALUES','data'=>array(0,0,0,1,),'prepare'=>'TRUNCATE `SC_customer_reg_fields`'),
array('select'=>'SELECT `customerID`, `reg_field_ID`, `reg_field_value` FROM `__temp_SS_customer_reg_fields_values`','insert'=>'INSERT INTO `SC_customer_reg_fields_values` (`customerID`, `reg_field_ID`, `reg_field_value`) VALUES','data'=>array(0,0,1,),'prepare'=>'TRUNCATE `SC_customer_reg_fields_values`'),
array('select'=>'SELECT `orderID`, `reg_field_ID`, `reg_field_value` FROM `__temp_SS_customer_reg_fields_values_quickreg`','insert'=>'INSERT INTO `SC_customer_reg_fields_values_quickreg` (`orderID`, `reg_field_ID`, `reg_field_value`) VALUES','data'=>array(0,0,1,),'prepare'=>'TRUNCATE `SC_customer_reg_fields_values_quickreg`'),
array('select'=>'SELECT `ActivationCode`, `CID`, `Email`, `Login`, `addressID`, `affiliateEmailOrders`, `affiliateEmailPayments`, `affiliateID`, `cust_password`, `custgroupID`, `customerID`, `first_name`, `last_name`, `reg_datetime`, `subscribed4news` FROM `__temp_SS_customers`','insert'=>'INSERT INTO `SC_customers` (`ActivationCode`, `CID`, `Email`, `Login`, `addressID`, `affiliateEmailOrders`, `affiliateEmailPayments`, `affiliateID`, `cust_password`, `custgroupID`, `customerID`, `first_name`, `last_name`, `reg_datetime`, `subscribed4news`) VALUES','data'=>array(1,0,1,1,0,0,0,0,1,0,0,1,1,1,0,),'prepare'=>'TRUNCATE `SC_customers`'),
array('select'=>'SELECT `Author`, `Body`, `DID`, `Topic`, `add_time`, `productID` FROM `__temp_SS_discussions`','insert'=>'INSERT INTO `SC_discussions` (`Author`, `Body`, `DID`, `Topic`, `add_time`, `productID`) VALUES','data'=>array(1,1,0,1,1,0,),'prepare'=>'TRUNCATE `SC_discussions`'),
array('select'=>'SELECT `le_cID`, `le_cName`, `le_cSortOrder` FROM `__temp_SS_linkexchange_categories`','insert'=>'INSERT INTO `SC_linkexchange_categories` (`le_cID`, `le_cName`, `le_cSortOrder`) VALUES','data'=>array(0,1,0,),'prepare'=>'TRUNCATE `SC_linkexchange_categories`'),
array('select'=>'SELECT `le_lCategoryID`, `le_lID`, `le_lText`, `le_lURL`, `le_lVerified` FROM `__temp_SS_linkexchange_links`','insert'=>'INSERT INTO `SC_linkexchange_links` (`le_lCategoryID`, `le_lID`, `le_lText`, `le_lURL`, `le_lVerified`) VALUES','data'=>array(0,0,1,1,1,),'prepare'=>'TRUNCATE `SC_linkexchange_links`'),
array('select'=>'SELECT `NID`, `add_date`, `add_stamp`, `emailed`, `picture`, `priority`, `textToMail`, `textToPublication`, `title` FROM `__temp_SS_news_table`','insert'=>'INSERT INTO `SC_news_table` (`NID`, `add_date`, `add_stamp`, `emailed`, `picture`, `priority`, `textToMail`, `textToPublication_%DEF_LANG%`, `title_%DEF_LANG%`) VALUES','data'=>array(0,1,0,0,1,0,1,1,1,),'prepare'=>'TRUNCATE `SC_news_table`'),
array('select'=>'SELECT `discount_id`, `percent_discount`, `price_range` FROM `__temp_SS_order_price_discount`','insert'=>'INSERT INTO `SC_order_price_discount` (`discount_id`, `percent_discount`, `price_range`) VALUES','data'=>array(0,0,0,),'prepare'=>'TRUNCATE `SC_order_price_discount`'),
array('select'=>'SELECT `orderID`, `status_change_time`, `status_comment`, `status_name` FROM `__temp_SS_order_status_changelog`','insert'=>'INSERT INTO `SC_order_status_changelog` (`orderID`, `status_change_time`, `status_comment`, `status_name`) VALUES','data'=>array(0,1,1,1,),'prepare'=>'TRUNCATE `SC_order_status_changelog`'),
array('select'=>'SELECT `Price`, `Quantity`, `itemID`, `load_counter`, `name`, `orderID`, `tax` FROM `__temp_SS_ordered_carts`','insert'=>'INSERT INTO `SC_ordered_carts` (`Price`, `Quantity`, `itemID`, `load_counter`, `name`, `orderID`, `tax`) VALUES','data'=>array(0,0,0,0,1,0,0,),'prepare'=>'TRUNCATE `SC_ordered_carts`'),
array(
'select'=>'SELECT `affiliateID`, `billing_address`, `billing_city`, `billing_country`, `billing_firstname`, `billing_lastname`, `billing_state`, `billing_zip`, `cc_cvv`, `cc_expires`, `cc_holdername`, `cc_number`, `currency_code`, `currency_value`, `customerID`, `customer_email`, `customer_firstname`, `customer_ip`, `customer_lastname`, `customers_comment`, `orderID`, `order_amount`, `order_discount`, \'\' AS `discount_description`, `order_time`, `payment_type`, `shippingServiceInfo`, `shipping_address`, `shipping_city`, `shipping_cost`, `shipping_country`, `shipping_firstname`, `shipping_lastname`, `shipping_state`, `shipping_type`, `shipping_zip`, `statusID` FROM `__temp_SS_orders`',
'insert'=>'INSERT INTO `SC_orders` (`affiliateID`, `billing_address`, `billing_city`, `billing_country`, `billing_firstname`, `billing_lastname`, `billing_state`, `billing_zip`, `cc_cvv`, `cc_expires`, `cc_holdername`, `cc_number`, `currency_code`, `currency_value`, `customerID`, `customer_email`, `customer_firstname`, `customer_ip`, `customer_lastname`, `customers_comment`, `orderID`, `order_amount`, `order_discount`, `discount_description`, `order_time`, `payment_type`, `shippingServiceInfo`, `shipping_address`, `shipping_city`, `shipping_cost`, `shipping_country`, `shipping_firstname`, `shipping_lastname`, `shipping_state`, `shipping_type`, `shipping_zip`, `statusID`) VALUES',
'data'=>array(0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,1,1,1,1,1,0,0,0,1,1,1,1,1,1,0,1,1,1,1,1,1,0,),
'prepare'=>'TRUNCATE `SC_orders`',
),
array('select'=>'SELECT `PID`, `SID` FROM `__temp_SS_payment_types__shipping_methods`','insert'=>'INSERT INTO `SC_payment_types__shipping_methods` (`PID`, `SID`) VALUES','data'=>array(0,0,),'prepare'=>'TRUNCATE `SC_payment_types__shipping_methods`'),
array('select'=>'SELECT `optionID`, `sort_order`, `name` FROM `__temp_SS_product_options`','insert'=>'INSERT INTO `SC_product_options` (`optionID`, `sort_order`, `name_%DEF_LANG%`) VALUES','data'=>array(0,0,1,),'prepare'=>'TRUNCATE `SC_product_options`'),
array('select'=>'SELECT `optionID`, `price_surplus`, `productID`, `variantID` FROM `__temp_SS_product_options_set`','insert'=>'INSERT INTO `SC_product_options_set` (`optionID`, `price_surplus`, `productID`, `variantID`) VALUES','data'=>array(0,0,0,0,),'prepare'=>'TRUNCATE `SC_product_options_set`'),
array('select'=>'SELECT `optionID`, `option_show_times`, `option_type`, `productID`, `variantID`, `option_value` FROM `__temp_SS_product_options_values`','insert'=>'INSERT INTO `SC_product_options_values` (`optionID`, `option_show_times`, `option_type`, `productID`, `variantID`, `option_value_%DEF_LANG%`) VALUES','data'=>array(0,0,0,0,0,1,),'prepare'=>'TRUNCATE `SC_product_options_values`'),
array('select'=>'SELECT `enlarged`, `filename`, `photoID`, `productID`, `thumbnail` FROM `__temp_SS_product_pictures`','insert'=>'INSERT INTO `SC_product_pictures` (`enlarged`, `filename`, `photoID`, `productID`, `thumbnail`) VALUES','data'=>array(1,1,0,0,1,),'prepare'=>'TRUNCATE `SC_product_pictures`'),
array('select'=>'SELECT `Price`, `categoryID`, `classID`, `customer_votes`, `customers_rating`, CONCAT(DATE(`date_added`),\' \',TIME(`date_added`)) AS `date_added`, `date_modified`, `default_picture`, `enabled`, `eproduct_available_days`, `eproduct_download_times`, `eproduct_filename`, `free_shipping`, `in_stock`, `items_sold`, `list_price`, `min_order_amount`, `productID`, `product_code`, `shipping_freight`, `sort_order`, `viewed_times`, `weight`, `brief_description`, `description`, `meta_description`, `meta_keywords`, `name`,1 FROM `__temp_SS_products`','insert'=>'INSERT INTO `SC_products` (`Price`, `categoryID`, `classID`, `customer_votes`, `customers_rating`, `date_added`, `date_modified`, `default_picture`, `enabled`, `eproduct_available_days`, `eproduct_download_times`, `eproduct_filename`, `free_shipping`, `in_stock`, `items_sold`, `list_price`, `min_order_amount`, `productID`, `product_code`, `shipping_freight`, `sort_order`, `viewed_times`, `weight`, `brief_description_%DEF_LANG%`, `description_%DEF_LANG%`, `meta_description_%DEF_LANG%`, `meta_keywords_%DEF_LANG%`, `name_%DEF_LANG%`, `ordering_available`) VALUES','data'=>array(0,0,0,0,0,2,2,0,0,0,0,1,0,0,0,0,0,0,1,0,0,0,0,1,1,1,1,1,0,),'prepare'=>array('TRUNCATE `SC_products`','TRUNCATE `SC_tags`',)),
array('select'=>'SELECT `optionID`, `sort_order`, `variantID`, `option_value` FROM `__temp_SS_products_opt_val_variants`','insert'=>'INSERT INTO `SC_products_opt_val_variants` (`optionID`, `sort_order`, `variantID`, `option_value_%DEF_LANG%`) VALUES','data'=>array(0,0,0,1,),'prepare'=>'TRUNCATE `SC_products_opt_val_variants`'),
array('select'=>'SELECT `Owner`, `productID` FROM `__temp_SS_related_items`','insert'=>'INSERT INTO `SC_related_items` (`Owner`, `productID`) VALUES','data'=>array(0,0,),'prepare'=>'TRUNCATE `SC_related_items`'),
//array('select'=>'SELECT `settingsID`, `settings_constant_name`, `settings_description`, `settings_groupID`, `settings_html_function`, `settings_title`, `settings_value`, `sort_order` FROM `__temp_SS_settings`','insert'=>'INSERT INTO `SC_settings` (`settingsID`, `settings_constant_name`, `settings_description`, `settings_groupID`, `settings_html_function`, `settings_title`, `settings_value`, `sort_order`) VALUES','data'=>array(0,1,1,0,1,1,1,0,),'prepare'=>'TRUNCATE `SC_settings`'),
//array('select'=>'SELECT `settings_groupID`, `settings_group_name`, `sort_order` FROM `__temp_SS_settings_groups`','insert'=>'INSERT INTO `SC_settings_groups` (`settings_groupID`, `settings_group_name`, `sort_order`) VALUES','data'=>array(0,1,0,),'prepare'=>'TRUNCATE `SC_settings_groups`'),
array('select'=>'SELECT `itemID`, `productID` FROM `__temp_SS_shopping_cart_items`','insert'=>'INSERT INTO `SC_shopping_cart_items` (`itemID`, `productID`) VALUES','data'=>array(0,0,),'prepare'=>'TRUNCATE `SC_shopping_cart_items`'),
array('select'=>'SELECT `itemID`, `variantID` FROM `__temp_SS_shopping_cart_items_content`','insert'=>'INSERT INTO `SC_shopping_cart_items_content` (`itemID`, `variantID`) VALUES','data'=>array(0,0,),'prepare'=>'TRUNCATE `SC_shopping_cart_items_content`'),
array('select'=>'SELECT `Quantity`, `customerID`, `itemID` FROM `__temp_SS_shopping_carts`','insert'=>'INSERT INTO `SC_shopping_carts` (`Quantity`, `customerID`, `itemID`) VALUES','data'=>array(0,0,0,),'prepare'=>'TRUNCATE `SC_shopping_carts`'),
array('select'=>'SELECT `Email`, `MID`, `customerID` FROM `__temp_SS_subscribers`','insert'=>'INSERT INTO `SC_subscribers` (`Email`, `MID`, `customerID`) VALUES','data'=>array(1,0,0,),'prepare'=>'TRUNCATE `SC_subscribers`'),
array('select'=>'SELECT `value`, `varName` FROM `__temp_SS_system`','insert'=>'INSERT INTO `SC_system` (`value`, `varName`) VALUES','data'=>array(1,1,),'prepare'=>'TRUNCATE `SC_system`'),
array('select'=>'SELECT `address_type`, `classID`, `name` FROM `__temp_SS_tax_classes`','insert'=>'INSERT INTO `SC_tax_classes` (`address_type`, `classID`, `name`) VALUES','data'=>array(0,0,1,),'prepare'=>'TRUNCATE `SC_tax_classes`'),
array('select'=>'SELECT `classID`, `countryID`, `isByZone`, `isGrouped`, `value` FROM `__temp_SS_tax_rates`','insert'=>'INSERT INTO `SC_tax_rates` (`classID`, `countryID`, `isByZone`, `isGrouped`, `value`) VALUES','data'=>array(0,0,0,0,0,),'prepare'=>'TRUNCATE `SC_tax_rates`'),
array('select'=>'SELECT `classID`, `isGrouped`, `value`, `zoneID` FROM `__temp_SS_tax_rates__zones`','insert'=>'INSERT INTO `SC_tax_rates__zones` (`classID`, `isGrouped`, `value`, `zoneID`) VALUES','data'=>array(0,0,0,0,),'prepare'=>'TRUNCATE `SC_tax_rates__zones`'),
array('select'=>'SELECT `classID`, `countryID`, `tax_zipID`, `value`, `zip_template` FROM `__temp_SS_tax_zip`','insert'=>'INSERT INTO `SC_tax_zip` (`classID`, `countryID`, `tax_zipID`, `value`, `zip_template`) VALUES','data'=>array(0,0,0,0,1,),'prepare'=>'TRUNCATE `SC_tax_zip`'),
array('select'=>'SELECT `countryID`, `zoneID`, `zone_code`, `zone_name` FROM `__temp_SS_zones`','insert'=>'INSERT INTO `SC_zones` (`countryID`, `zoneID`, `zone_code`, `zone_name_%DEF_LANG%`) VALUES','data'=>array(0,0,1,1,),'prepare'=>'TRUNCATE `SC_zones`'),
//Special offers
array('select'=>"SELECT 'specialoffers' AS 'list_id', `productID`, `sort_order` FROM `__temp_SS_special_offers`",'insert'=>'INSERT INTO `SC_product_list_item` (`list_id`, `productID`, `priority`) VALUES','data'=>array(1,0,0,),'prepare'=>array('TRUNCATE `SC_product_list_item`','TRUNCATE `SC_product_list`',"INSERT INTO `SC_product_list` (`id`,`name`) VALUES ('specialoffers','Special offers list')",)),
///////
// Order statuses
////
//custom


///////Move

array(
	'select'=>'SELECT DISTINCT `__temp_SS_modules`.`module_id` , `__temp_SS_modules`.`ModuleClassName` , `__temp_SS_modules`.`module_name` FROM `__temp_SS_modules` , `__temp_SS_shipping_methods`,`__temp_SS_payment_types` WHERE (`__temp_SS_modules`.`module_id` = `__temp_SS_shipping_methods`.`module_id`)OR(`__temp_SS_modules`.`module_id` = `__temp_SS_payment_types`.`module_id`)',
	'insert'=>'INSERT INTO `SC_spmodules` (`module_id`,`ModuleClassName`,`module_name`) VALUES',
	'data'=>array(0,1,1,),
	'prepare'=>'TRUNCATE `SC_spmodules`'),
array(
	'select'=>'SELECT `Enabled`, `SID`, `module_id`, `sort_order`, `Name`, `description`, `email_comments_text` FROM `__temp_SS_shipping_methods`',
	'insert'=>'INSERT INTO `SC_shipping_methods` (`Enabled`, `SID`, `module_id`, `sort_order`, `Name_%DEF_LANG%`, `description_%DEF_LANG%`, `email_comments_text_%DEF_LANG%`) VALUES',
	'data'=>array(0,0,0,0,1,1,1,),
	'prepare'=>'TRUNCATE `SC_shipping_methods`'),

array(
	'select'=>'SELECT `Enabled`, `PID`, `calculate_tax`, `module_id`, `sort_order`, `Name`, `description`, `email_comments_text` FROM `__temp_SS_payment_types`',
	'insert'=>'INSERT INTO `SC_payment_types` (`Enabled`, `PID`, `calculate_tax`, `module_id`, `sort_order`, `Name_%DEF_LANG%`, `description_%DEF_LANG%`, `email_comments_text_%DEF_LANG%`) VALUES',
	'data'=>array(0,0,0,0,0,1,1,1,),
	'prepare'=>'TRUNCATE `SC_payment_types`'),

array(
	'select'=>'SELECT `settings_constant_name`, `settings_description`, `settings_groupID`, `settings_html_function`, `settings_title`, `settings_value`, `sort_order` FROM `__temp_SS_settings` WHERE `settings_groupID`=1',
	'insert'=>'INSERT INTO `SC_settings` (`settings_constant_name`, `settings_description`, `settings_groupID`, `settings_html_function`, `settings_title`, `settings_value`, `sort_order`) VALUES ',
	'data'=>array(1,1,0,1,1,1,0,),
	'prepare'=>'DELETE FROM `SC_settings` WHERE `settings_groupID`=1'),

array('select'=>"SELECT 'ShopScript default currency' AS 'Name', 1 AS 'currency_value', '{value}' as 'display_template'
FROM `__temp_SS_currency_types`
WHERE (
SELECT IF (
(SELECT COUNT( * ) FROM `__temp_SS_currency_types` WHERE `currency_value` =1
) =0, TRUE, FALSE
)
) = TRUE
LIMIT 1",'insert'=>'INSERT INTO `SC_currency_types` (`Name_%DEF_LANG%`, `currency_value`,`display_template_%DEF_LANG%`) VALUES','data'=>array(1,0,1,),'prepare'=>null,)
);

$SQLstatusStrings=array(
'selectPre'=>array(
2=>'SELECT `__temp_SS_order_status`.`statusID`,`__temp_SS_order_status`.`sort_order` , `__temp_SS_order_status`.`status_name`
FROM `__temp_SS_order_status`
WHERE 
`statusID` = (
SELECT `__temp_SS_settings`.`settings_value`
FROM `__temp_SS_settings`
WHERE `__temp_SS_settings`.`settings_constant_name` LIKE \'CONF_NEW_ORDER_STATUS\' )',
////////
5=>'SELECT `__temp_SS_order_status`.`statusID`,`__temp_SS_order_status`.`sort_order` , `__temp_SS_order_status`.`status_name`
FROM `__temp_SS_order_status`
WHERE 
`statusID` = (
SELECT `__temp_SS_settings`.`settings_value`
FROM `__temp_SS_settings`
WHERE `__temp_SS_settings`.`settings_constant_name` LIKE \'CONF_COMPLETED_ORDER_STATUS\' )',
////////
3=>'SELECT `__temp_SS_order_status`.`statusID`,`__temp_SS_order_status`.`sort_order` , `__temp_SS_order_status`.`status_name`
FROM `__temp_SS_order_status`
WHERE 
(`__temp_SS_order_status`.`status_name` LIKE \'Processing\')
OR (LOWER(`__temp_SS_order_status`.`status_name`) LIKE CONVERT(CHAR(226,32,238,225,240,224,225,238,242,234,229) USING utf8))',
),

'selectCustom'=>'SELECT `__temp_SS_order_status`.`statusID`,`__temp_SS_order_status`.`sort_order` , `__temp_SS_order_status`.`status_name`
FROM `__temp_SS_order_status`
WHERE (
`statusID` <>1
)
AND (
`statusID` <> (
SELECT `__temp_SS_settings`.`settings_value`
FROM `__temp_SS_settings`
WHERE `__temp_SS_settings`.`settings_constant_name` LIKE \'CONF_NEW_ORDER_STATUS\' )
)
AND (
`statusID` <> (
SELECT `__temp_SS_settings`.`settings_value`
FROM `__temp_SS_settings`
WHERE `__temp_SS_settings`.`settings_constant_name` LIKE \'CONF_COMPLETED_ORDER_STATUS\' )
)
AND NOT (`__temp_SS_order_status`.`status_name` LIKE \'Processing\')
AND NOT (LOWER(`__temp_SS_order_status`.`status_name`)LIKE CONVERT(CHAR(226,32,238,225,240,224,225,238,242,234,229) USING utf8))',
'insert'=>'INSERT INTO `SC_order_status` (`predefined`, `sort_order`, `status_name_%DEF_LANG%`) VALUES (0,%s,\'%s\')',
'prepare'=>'DELETE FROM `SC_order_status` WHERE `predefined`<>1',
);



$SQLslugStrings=array(
array('select'=>'SELECT `productID`,`name_%DEF_LANG%` FROM `SC_products`','update'=>'UPDATE `SC_products` SET `slug`=\'%s\' WHERE `productID`=%s'),
array('select'=>'SELECT `categoryID`,`name_%DEF_LANG%` FROM `SC_categories`','update'=>'UPDATE `SC_categories` SET `slug`=\'%s\' WHERE `categoryID`=%s'),
);

$SettingsCopy=array(
	'select'=>'SELECT `settings_value` FROM `__temp_SS_settings` WHERE `settings_constant_name`=\'%s\'',
	'update'=>'UPDATE `SC_settings` SET `settings_value`=\'%s\' WHERE `settings_constant_name`=\'%s\'',
	'params'=>array(
		'CONF_DEFAULT_CURRENCY',
		'CONF_DEFAULT_CUSTOMER_GROUP',
		'CONF_DEFAULT_TAX_CLASS',
		'CONF_DEFAULT_TITLE',
		'CONF_ENABLE_CONFIRMATION_CODE',
		'CONF_ENABLE_REGCONFIRMATION',
		'CONF_EXACT_PRODUCT_BALANCE',
		'CONF_GENERAL_EMAIL',
		'CONF_HOMEPAGE_META_DESCRIPTION',
		'CONF_HOMEPAGE_META_KEYWORDS',
		'CONF_MINIMAL_ORDER_AMOUNT',
		'CONF_ORDERING_REQUEST_BILLING_ADDRESS',
		'CONF_ORDERS_EMAIL',
		'CONF_PRODUCT_SORT',
		'CONF_PRODUCTS_PER_PAGE',
		'CONF_PROTECTED_CONNECTION',
		'CONF_SHOP_NAME',
		'CONF_SHOP_URL',
		'CONF_SHOW_ADD2CART',
//		'CONF_UPDATE_GCV',
		'CONF_WEIGHT_UNIT',
		'CONF_ZONE',
		'GOOGLE_ANALYTICS_ACCOUNT',
		'GOOGLE_ANALYTICS_ENABLE',
		'GOOGLE_ANALYTICS_USD_CURRENCY',
),
	'ml_params' =>array(
		'CONF_DEFAULT_TITLE',
		'CONF_SHOP_NAME',
		'CONF_HOMEPAGE_META_DESCRIPTION',
		'CONF_HOMEPAGE_META_KEYWORDS',
),
);

$SS_tables=array(
'SS_aff_commissions',
'SS_aff_payments',
'SS_aux_pages',
'SS_categories',
'SS_category__product_options',
'SS_category_product',
'SS_category_product_options__variants',
'SS_countries',
'SS_currency_types',
'SS_cusomer_log',
'SS_custgroups',
'SS_customer_addresses',
'SS_customer_reg_fields',
'SS_customer_reg_fields_values',
'SS_customer_reg_fields_values_quickreg',
'SS_customers',
'SS_discussions',
'SS_linkexchange_categories',
'SS_linkexchange_links',
'SS_modules',
'SS_news_table',
'SS_order_price_discount',
'SS_order_status',
'SS_order_status_changelog',
'SS_ordered_carts',
'SS_orders',
'SS_payment_types',
'SS_payment_types__shipping_methods',
'SS_product_options',
'SS_product_options_set',
'SS_product_options_values',
'SS_product_pictures',
'SS_products',
'SS_products_opt_val_variants',
'SS_related_items',
'SS_settings',
'SS_settings_groups',
'SS_shipping_methods',
'SS_shopping_cart_items',
'SS_shopping_cart_items_content',
'SS_shopping_carts',
'SS_special_offers',
'SS_subscribers',
'SS_system',
'SS_tax_classes',
'SS_tax_rates',
'SS_tax_rates__zones',
'SS_tax_zip',
'SS_zones',
)

?>