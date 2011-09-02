DROP TABLE IF EXISTS `SC_aff_commissions`;
CREATE TABLE `SC_aff_commissions` (
  `cID` int(11) NOT NULL auto_increment,
  `Amount` float default NULL,
  `CurrencyISO3` varchar(3) default NULL,
  `xDateTime` datetime default NULL,
  `Description` varchar(255) default NULL,
  `OrderID` int(11) default NULL,
  `CustomerID` int(11) default NULL,
  PRIMARY KEY  (`cID`),
  KEY `CustomerID` (`CustomerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_aff_payments`;
CREATE TABLE `SC_aff_payments` (
  `pID` int(11) NOT NULL auto_increment,
  `CustomerID` int(11) default NULL,
  `Amount` float default NULL,
  `CurrencyISO3` varchar(3) default NULL,
  `xDate` date default NULL,
  `Description` varchar(255) default NULL,
  PRIMARY KEY  (`pID`),
  KEY `CustomerID` (`CustomerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_aux_pages`;
CREATE TABLE `SC_aux_pages` (
  `aux_page_ID` int(11) NOT NULL auto_increment,
  `aux_page_text_type` int(11) default NULL,
  `aux_page_name_en` varchar(64) default NULL,
  `aux_page_text_en` text,
  `aux_page_slug` VARCHAR( 64 )default NULL,
  `meta_keywords_en` varchar(255) default NULL,
  `meta_description_en` text,
  `aux_page_enabled` smallint(1) unsigned NOT NULL default '0',
  `aux_page_priority` int(10) unsigned NOT NULL default '0',
  `aux_page_name_ru` varchar(64) default NULL,
  `aux_page_text_ru` text,
  `meta_keywords_ru` varchar(255) default NULL,
  `meta_description_ru` text,
  PRIMARY KEY  (`aux_page_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_categories`;
CREATE TABLE `SC_categories` (
  `categoryID` int(11) NOT NULL auto_increment,
  `parent` int(11) default NULL,
  `products_count` int(11) default NULL,
  `picture` varchar(30) default NULL,
  `products_count_admin` int(11) default NULL,
  `sort_order` int(11) default '0',
  `viewed_times` int(11) default '0',
  `allow_products_comparison` int(11) default '0',
  `allow_products_search` int(11) default '1',
  `show_subcategories_products` int(11) default '1',
  `name_en` varchar(255) default NULL,
  `description_en` text,
  `meta_title_en` VARCHAR( 255 ) default NULL,
  `meta_description_en` varchar(255) default NULL,
  `meta_keywords_en` varchar(255) default NULL,
  `slug` varchar(255) NOT NULL default '',
  `name_ru` varchar(255) default NULL,
  `description_ru` text,
  `meta_title_ru` VARCHAR( 255 ) default NULL ,
  `meta_description_ru` varchar(255) default NULL,
  `meta_keywords_ru` varchar(255) default NULL,
  `vkontakte_type` INT DEFAULT 0,
  `id_1c` VARCHAR( 36 ),
  PRIMARY KEY  (`categoryID`),
  KEY `parent` (`parent`),
  KEY `slug` (`slug`),
  KEY `sort_order` (`sort_order`),
  KEY `name_en` (`name_en`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_category_product`;
CREATE TABLE `SC_category_product` (
  `productID` int(11) NOT NULL default '0',
  `categoryID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`productID`,`categoryID`),
  KEY `categoryID` (`categoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `SC_category_product_options__variants`;
CREATE TABLE `SC_category_product_options__variants` (
  `optionID` int(11) NOT NULL default '0',
  `categoryID` int(11) NOT NULL default '0',
  `variantID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`optionID`,`categoryID`,`variantID`),
  KEY `categoryID` (`categoryID`),
  KEY `variantID` (`variantID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_category__product_options`;
CREATE TABLE `SC_category__product_options` (
  `optionID` int(11) NOT NULL default '0',
  `categoryID` int(11) NOT NULL default '0',
  `set_arbitrarily` int(11) default '1',
  PRIMARY KEY  (`optionID`,`categoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_config_settings`;
CREATE TABLE `SC_config_settings` (
  `ModuleConfigID` int(10) unsigned NOT NULL default '0',
  `SettingName` varchar(30) default NULL,
  `SettingValue` varchar(255) default NULL,
  `SettingType` int(10) unsigned NOT NULL default '0',
  KEY `ModuleConfigID` (`ModuleConfigID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `SC_countries`;
CREATE TABLE `SC_countries` (
  `countryID` int(11) NOT NULL auto_increment,
  `country_iso_2` varchar(2) default NULL,
  `country_iso_3` varchar(3) default NULL,
  `country_name_en` varchar(64) default NULL,
  `country_name_ru` varchar(64) default NULL,
  PRIMARY KEY  (`countryID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_currency_types`;
CREATE TABLE `SC_currency_types` (
  `CID` int(11) NOT NULL auto_increment,
  `code` varchar(7) default NULL,
  `currency_value` double default NULL,
  `where2show` int(11) default NULL,
  `sort_order` int(11) default '0',
  `currency_iso_3` varchar(3) default NULL,
  `display_template_en` varchar(20) default NULL,
  `Name_en` varchar(30) default NULL,
  `decimal_symbol` char(1) NOT NULL default '',
  `decimal_places` int(10) unsigned NOT NULL default '0',
  `thousands_delimiter` char(1) NOT NULL default '',
  `Name_ru` varchar(30) default NULL,
  `display_template_ru` varchar(20) default NULL,
  PRIMARY KEY  (`CID`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_custgroups`;
CREATE TABLE `SC_custgroups` (
  `custgroupID` int(11) NOT NULL auto_increment,
  `custgroup_discount` float default '0',
  `sort_order` int(11) default '0',
  `custgroup_name_en` varchar(64) default NULL,
  `custgroup_name_ru` varchar(64) default NULL,
  PRIMARY KEY  (`custgroupID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_customers`;
CREATE TABLE `SC_customers` (
  `customerID` int(11) NOT NULL auto_increment,
  `Login` varchar(32) NOT NULL,
  `cust_password` varchar(255) NOT NULL default '',
  `Email` varchar(64) default NULL,
  `first_name` varchar(32) default NULL,
  `last_name` varchar(32) default NULL,
  `subscribed4news` int(11) default NULL,
  `custgroupID` int(11) default NULL,
  `addressID` int(11) default NULL,
  `reg_datetime` datetime default NULL,
  `CID` int(11) default NULL,
  `affiliateID` int(11) NOT NULL default '0',
  `affiliateEmailOrders` int(11) NOT NULL default '1',
  `affiliateEmailPayments` int(11) NOT NULL default '1',
  `ActivationCode` varchar(16) NOT NULL default '',
  `vkontakte_id` INT(11),
  PRIMARY KEY  (`customerID`),
  KEY `AFFILIATEID` (`affiliateID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_customer_addresses`;
CREATE TABLE `SC_customer_addresses` (
  `addressID` int(11) NOT NULL auto_increment,
  `customerID` int(11) NOT NULL default '0',
  `first_name` varchar(64) default NULL,
  `last_name` varchar(64) default NULL,
  `countryID` int(11) default NULL,
  `zoneID` int(11) default NULL,
  `zip` varchar(64) default NULL,
  `state` varchar(64) default NULL,
  `city` varchar(64) default NULL,
  `address` TEXT default NULL,
  PRIMARY KEY  (`addressID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_customer_reg_fields`;
CREATE TABLE `SC_customer_reg_fields` (
  `reg_field_ID` int(11) NOT NULL auto_increment,
  `reg_field_required` tinyint(1) default NULL,
  `sort_order` int(11) default NULL,
  `reg_field_name_en` varchar(32) default NULL,
  `reg_field_name_ru` varchar(32) default NULL,
  PRIMARY KEY  (`reg_field_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_customer_reg_fields_values`;
CREATE TABLE `SC_customer_reg_fields_values` (
  `reg_field_ID` int(11) NOT NULL default '0',
  `customerID` int(11) NOT NULL default '0',
  `reg_field_value` varchar(255) default NULL,
  UNIQUE KEY `UNQ_reg_cust` (`reg_field_ID`,`customerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `SC_customer_reg_fields_values_quickreg`;
CREATE TABLE `SC_customer_reg_fields_values_quickreg` (
  `reg_field_ID` int(11) NOT NULL default '0',
  `orderID` int(11) NOT NULL default '0',
  `reg_field_value` varchar(255) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_discount_coupons`;
CREATE TABLE `SC_discount_coupons` (
  `coupon_id` int(11) NOT NULL auto_increment,
  `coupon_code` char(10) character set utf8 collate utf8_bin NOT NULL,
  `is_active` enum('N','Y') character set utf8 collate utf8_bin NOT NULL default 'N',
  `coupon_type` enum('SU','MX','MN') character set utf8 collate utf8_bin NOT NULL default 'SU',
  `expire_date` int(11) NOT NULL,
  `discount_percent` decimal(12,2) NOT NULL default '0.00',
  `discount_absolute` decimal(12,2) NOT NULL default '0.00',
  `discount_type` enum('P','A') character set utf8 collate utf8_bin NOT NULL default 'P',
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY  (`coupon_id`),
  UNIQUE KEY `coupon_code` (`coupon_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_discussions`;
CREATE TABLE `SC_discussions` (
  `DID` int(11) NOT NULL auto_increment,
  `productID` int(11) default NULL,
  `Author` varchar(40) default NULL,
  `Body` text,
  `add_time` datetime default NULL,
  `Topic` varchar(255) default NULL,
  PRIMARY KEY  (`DID`),
  KEY `productID` (`productID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_divisions`;
CREATE TABLE `SC_divisions` (
  `xID` int(10) unsigned NOT NULL auto_increment,
  `xName` varchar(255) NOT NULL default '',
  `xKey` varchar(255) NOT NULL default '',
  `xUnicKey` varchar(255) NOT NULL default '',
  `xParentID` int(10) unsigned NOT NULL default '0',
  `xEnabled` tinyint(1) NOT NULL default '0',
  `xPriority` smallint(5) unsigned NOT NULL default '0',
  `xTemplate` varchar(100) NOT NULL default '',
  `xLinkDivisionUKey` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`xID`,`xParentID`),
  KEY `xUnicKey` (`xUnicKey`),
  KEY `xEnabled` (`xEnabled`),
  KEY `xPriority` (`xPriority`),
  KEY `xParentID` (`xParentID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_division_access`;
CREATE TABLE `SC_division_access` (
  `xDivisionID` int(11) NOT NULL default '0',
  `xU_ID` varchar(20) NOT NULL default '',
  `xID_TYPE` smallint(1) unsigned NOT NULL default '0',
  KEY `xDivisionID` (`xDivisionID`,`xU_ID`),
  KEY `xID_TYPE` (`xID_TYPE`),
  KEY `xU_ID` (`xU_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_division_custom_settings`;
CREATE TABLE `SC_division_custom_settings` (
  `xDivisionID` int(10) unsigned NOT NULL default '0',
  `xSettingID` int(10) unsigned NOT NULL auto_increment,
  `xKey` varchar(30) NOT NULL default '',
  `xName` varchar(30) NOT NULL default '',
  `xValue` varchar(255) NOT NULL default '',
  KEY `xDivisionID` (`xDivisionID`,`xSettingID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_division_interface`;
CREATE TABLE `SC_division_interface` (
  `xDivisionID` int(10) unsigned NOT NULL default '0',
  `xInterface` varchar(100) NOT NULL default '0',
  `xPriority` smallint(5) unsigned NOT NULL default '0',
  `xInheritable` tinyint(1) NOT NULL default '0',
  KEY `divisionID` (`xDivisionID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_htmlcodes`;
CREATE TABLE `SC_htmlcodes` (
  `key` varchar(20) NOT NULL default '',
  `title` varchar(30) NOT NULL default '',
  `code` text NOT NULL,
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_interface_interfaces`;
CREATE TABLE `SC_interface_interfaces` (
  `xInterfaceCaller` varchar(100) NOT NULL default '',
  `xInterfaceCalled` varchar(100) NOT NULL default '',
  `xPriority` smallint(5) unsigned NOT NULL default '0',
  KEY `xInterfaceCaller` (`xInterfaceCaller`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_language`;
CREATE TABLE `SC_language` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `enabled` tinyint(1) unsigned NOT NULL default '0',
  `thumbnail` varchar(50) NOT NULL default '',
  `iso2` varchar(2) default NULL,
  `priority` int(11) unsigned NOT NULL default '0',
  `direction` INT( 1 ) UNSIGNED DEFAULT '0' NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `enabled` (`enabled`),
  KEY `iso2` (`iso2`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_linkexchange_categories`;
CREATE TABLE `SC_linkexchange_categories` (
  `le_cID` int(11) NOT NULL auto_increment,
  `le_cName` varchar(100) default NULL,
  `le_cSortOrder` int(11) default NULL,
  PRIMARY KEY  (`le_cID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_linkexchange_links`;
CREATE TABLE `SC_linkexchange_links` (
  `le_lID` int(11) NOT NULL auto_increment,
  `le_lText` varchar(255) default NULL,
  `le_lURL` varchar(255) default NULL,
  `le_lCategoryID` int(11) default NULL,
  `le_lVerified` datetime default NULL,
  PRIMARY KEY  (`le_lID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_local`;
CREATE TABLE `SC_local` (
  `id` varchar(255) NOT NULL default '',
  `lang_id` int(11) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  `group` enum('hidden','front','back','general') NOT NULL default 'hidden',
  `subgroup` varchar(3) default NULL,
  PRIMARY KEY  (`id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  KEY `id` (`id`),
  KEY `group` (`group`),
  KEY `subgroup` (`subgroup`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_localgroup`;
CREATE TABLE `SC_localgroup` (
  `key` varchar(3) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `hidden` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`key`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_modules`;
CREATE TABLE `SC_modules` (
  `ModuleID` int(10) unsigned NOT NULL auto_increment,
  `ModuleVersion` float NOT NULL default '0',
  `ModuleClassName` varchar(30) NOT NULL default '',
  `ModuleClassFile` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ModuleID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_module_configs`;
CREATE TABLE `SC_module_configs` (
  `ModuleConfigID` int(10) unsigned NOT NULL auto_increment,
  `ModuleID` int(10) unsigned NOT NULL default '0',
  `ConfigKey` varchar(30) NOT NULL default '',
  `ConfigTitle` varchar(50) NOT NULL default '',
  `ConfigDescr` varchar(100) NOT NULL default '',
  `ConfigInit` smallint(5) unsigned NOT NULL default '0',
  `ConfigEnabled` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ModuleConfigID`),
  KEY `ModuleID` (`ModuleID`),
  KEY `ConfigKey` (`ConfigKey`),
  KEY `ConfigEnabled` (`ConfigEnabled`),
  KEY `ConfigInit` (  `ConfigInit` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_news_table`;
CREATE TABLE `SC_news_table` (
  `NID` int(11) NOT NULL auto_increment,
  `add_date` varchar(30) default NULL,
  `title_en` text,
  `title_ru` text,
  `picture` varchar(30) default NULL,
  `textToPublication_en` text,
  `textToPublication_ru` text,
  `textToMail` text,
  `add_stamp` int(11) default NULL,
  `priority` int(10) unsigned NOT NULL default '0',
  `emailed` tinyint(1) default NULL,
  PRIMARY KEY  (`NID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_ordered_carts`;
CREATE TABLE `SC_ordered_carts` (
  `itemID` int(11) NOT NULL default '0',
  `orderID` int(11) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `Price` float default NULL,
  `Quantity` int(11) default NULL,
  `tax` float default NULL,
  `load_counter` int(11) default '0',
  PRIMARY KEY  (`itemID`,`orderID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_orders`;
CREATE TABLE `SC_orders` (
  `orderID` int(11) NOT NULL auto_increment,
  `customerID` int(11) default NULL,
  `order_time` datetime default NULL,
  `customer_ip` varchar(15) default NULL,
  `shipping_type` varchar(30) default NULL,
  `shipping_module_id` int(10) unsigned NOT NULL default '0',
  `payment_type` varchar(30) default NULL,
  `payment_module_id` int(10) unsigned NOT NULL default '0',
  `customers_comment` text default NULL,
  `statusID` int(11) default NULL,
  `shipping_cost` double default NULL,
  `order_discount` double default NULL,
  `discount_description` varchar(255) NOT NULL,
  `order_amount` decimal(12,2) NOT NULL default '0.00',
  `currency_code` varchar(7) default NULL,
  `currency_value` double default NULL,
  `customer_firstname` varchar(64) default NULL,
  `customer_lastname` varchar(64) default NULL,
  `customer_email` varchar(50) default NULL,
  `shipping_firstname` varchar(64) default NULL,
  `shipping_lastname` varchar(64) default NULL,
  `shipping_country` varchar(64) default NULL,
  `shipping_state` varchar(64) default NULL,
  `shipping_zip` varchar(64) default NULL,
  `shipping_city` varchar(64) default NULL,
  `shipping_address` TEXT default NULL,
  `billing_firstname` varchar(64) default NULL,
  `billing_lastname` varchar(64) default NULL,
  `billing_country` varchar(64) default NULL,
  `billing_state` varchar(64) default NULL,
  `billing_zip` varchar(64) default NULL,
  `billing_city` varchar(64) default NULL,
  `billing_address` TEXT default NULL,
  `cc_number` varchar(255) default NULL,
  `cc_holdername` varchar(255) default NULL,
  `cc_expires` varchar(255) default NULL,
  `cc_cvv` varchar(255) default NULL,
  `affiliateID` int(11) default NULL,
  `shippingServiceInfo` varchar(255) default NULL,
  `google_order_number` varchar(50) NOT NULL default '',
  `source` enum('storefront','widgets','backend') NOT NULL default 'storefront',
  PRIMARY KEY  (`orderID`),
  KEY `google_order_number` (`google_order_number`),
  KEY `customerID` (`customerID`),
  KEY `statusID` (`statusID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_orders_discount_coupons`;
CREATE TABLE `SC_orders_discount_coupons` (
  `order_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  PRIMARY KEY  (`order_id`),
  KEY `coupon_id` (`coupon_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_order_price_discount`;
CREATE TABLE `SC_order_price_discount` (
  `discount_id` int(11) NOT NULL auto_increment,
  `discount_type` enum('A','O') NOT NULL,
  `price_range` float default NULL,
  `percent_discount` float default NULL,
  PRIMARY KEY  (`discount_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_order_status`;
CREATE TABLE `SC_order_status` (
  `statusID` int(11) NOT NULL auto_increment,
  `predefined` smallint(1) unsigned NOT NULL default '0',
  `color` varchar(6) NOT NULL default '',
  `bold` smallint(1) unsigned NOT NULL default '0',
  `italic` smallint(1) unsigned NOT NULL default '0',
  `sort_order` int(11) default NULL,
  `status_name_en` varchar(30) default NULL,
  `status_name_ru` varchar(30) default NULL,
  PRIMARY KEY  (`statusID`),
  KEY  `predefined` (  `predefined` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_order_status_changelog`;
CREATE TABLE `SC_order_status_changelog` (
  `orderID` int(11) default NULL,
  `status_name` varchar(255) default NULL,
  `status_change_time` datetime default NULL,
  `status_comment` TEXT default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_payment_types`;
CREATE TABLE `SC_payment_types` (
  `PID` int(11) NOT NULL auto_increment,
  `Enabled` int(11) default NULL,
  `calculate_tax` int(11) default NULL,
  `sort_order` int(11) default '0',
  `module_id` int(11) default NULL,
  `Name_en` varchar(30) default NULL,
  `description_en` varchar(255) default NULL,
  `email_comments_text_en` text,
  `Name_ru` varchar(30) default NULL,
  `description_ru` varchar(255) default NULL,
  `email_comments_text_ru` text,
  `logo` text,
  PRIMARY KEY  (`PID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_payment_types__shipping_methods`;
CREATE TABLE `SC_payment_types__shipping_methods` (
  `SID` int(11) NOT NULL default '0',
  `PID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`SID`,`PID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_products`;
CREATE TABLE `SC_products` (
  `productID` int(11) NOT NULL auto_increment,
  `categoryID` int(11) default NULL,
  `customers_rating` float default '0',
  `Price` float default NULL,
  `in_stock` int(11) default NULL,
  `customer_votes` int(11) default '0',
  `items_sold` int(11) NOT NULL default '0',
  `enabled` int(11) default NULL,
  `list_price` float default NULL,
  `product_code` varchar(25) default NULL,
  `sort_order` int(11) default '0',
  `default_picture` int(11) default NULL,
  `date_added` datetime default NULL,
  `date_modified` datetime default NULL,
  `viewed_times` int(11) default '0',
  `add2cart_counter` INT UNSIGNED DEFAULT '0',
  `eproduct_filename` varchar(255) default NULL,
  `eproduct_available_days` int(11) default '5',
  `eproduct_download_times` int(11) default '5',
  `weight` float default '0',
  `free_shipping` int(11) default '0',
  `min_order_amount` int(11) default '1',
  `shipping_freight` float default '0',
  `classID` int(11) default NULL,
  `name_en` varchar(255) default NULL,
  `brief_description_en` text NOT NULL,
  `description_en` text NOT NULL,
  `meta_title_en` VARCHAR( 255 ) default NULL,
  `meta_description_en` varchar(255) default NULL,
  `meta_keywords_en` varchar(255) default NULL,
  `ordering_available` int(11) NOT NULL default '0',
  `slug` varchar(255) NOT NULL default '',
  `name_ru` varchar(255) default NULL,
  `brief_description_ru` text,
  `description_ru` text,
  `meta_title_ru` VARCHAR( 255 ) default NULL,
  `meta_description_ru` varchar(255) default NULL,
  `meta_keywords_ru` varchar(255) default NULL,
  `vkontakte_update_timestamp` INT(11),
  `id_1c` VARCHAR( 74 ),
  PRIMARY KEY  (`productID`),
  KEY `categoryID` (`categoryID`),
  KEY `enabled` (`enabled`),
  KEY `name_en` (`name_en`),
  KEY `Price` (`Price`),
  KEY `customers_rating` (`customers_rating`),
  KEY `slug` (`slug`),
  KEY `sort_order` (`sort_order`),
  KEY `viewed_times` (`viewed_times`),
  KEY `product_code` (`product_code`),
  KEY `customer_votes` (`customer_votes`),
  KEY `items_sold` (`items_sold`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8  ;

DROP TABLE IF EXISTS `SC_products_opt_val_variants`;
CREATE TABLE `SC_products_opt_val_variants` (
  `variantID` int(11) NOT NULL auto_increment,
  `optionID` int(11) NOT NULL default '0',
  `sort_order` int(11) default '0',
  `option_value_en` varchar(255) default NULL,
  `option_value_ru` varchar(255) default NULL,
  PRIMARY KEY  (`variantID`),
  KEY `optionID` (  `optionID` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_product_list`;
CREATE TABLE `SC_product_list` (
  `id` varchar(20) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_product_list_item`;
CREATE TABLE `SC_product_list_item` (
  `list_id` varchar(20) NOT NULL default '',
  `productID` int(10) unsigned NOT NULL default '0',
  `priority` int(10) unsigned NOT NULL default '0',
  KEY `list_id` (`list_id`,`productID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_product_options`;
CREATE TABLE `SC_product_options` (
  `optionID` int(11) NOT NULL auto_increment,
  `sort_order` int(11) default '0',
  `name_en` varchar(50) default NULL,
  `name_ru` varchar(50) default NULL,
  PRIMARY KEY  (`optionID`),
  KEY `sort_order` (`sort_order`),
  KEY `name_en` (`name_en`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_product_options_set`;
CREATE TABLE `SC_product_options_set` (
  `productID` int(11) NOT NULL default '0',
  `optionID` int(11) NOT NULL default '0',
  `variantID` int(11) NOT NULL default '0',
  `price_surplus` float default '0',
  PRIMARY KEY  (`productID`,`optionID`,`variantID`),
  KEY `productID` (`productID`),
  KEY `optionID` (`optionID`),
  KEY `variantID` (`variantID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_product_options_values`;
CREATE TABLE `SC_product_options_values` (
  `optionID` int(11) NOT NULL default '0',
  `productID` int(11) NOT NULL default '0',
  `option_type` tinyint(1) default '0',
  `option_show_times` int(11) default '1',
  `variantID` int(11) default NULL,
  `option_value_en` varchar(255) default NULL,
  `option_value_ru` varchar(255) default NULL,
  PRIMARY KEY  (`optionID`,`productID`),
  KEY `productID` (`productID`),
  KEY `optionID` (`optionID`),
  KEY `variantID` (`variantID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_product_pictures`;
CREATE TABLE `SC_product_pictures` (
  `photoID` int(11) NOT NULL auto_increment,
  `productID` int(11) NOT NULL default '0',
  `filename` varchar(255) default NULL,
  `thumbnail` varchar(255) default NULL,
  `enlarged` varchar(255) default NULL,
  `priority` INT UNSIGNED DEFAULT '0',
  PRIMARY KEY  (`photoID`),
  KEY `productID` (`productID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_related_items`;
CREATE TABLE `SC_related_items` (
  `productID` int(11) NOT NULL default '0',
  `Owner` int(11) NOT NULL default '0',
  PRIMARY KEY  (`productID`,`Owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_rpost_zones`;
CREATE TABLE `SC_rpost_zones` (
  `module_id` int(10) unsigned NOT NULL default '0',
  `countryID` int(11) default NULL,
  `zoneID` int(11) default NULL,
  `zoneNumber` int(11) default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_settings`;
CREATE TABLE `SC_settings` (
  `settingsID` int(11) NOT NULL auto_increment,
  `settings_groupID` int(11) default NULL,
  `settings_constant_name` varchar(64) default NULL,
  `settings_value` text default NULL,
  `settings_title` varchar(128) default NULL,
  `settings_description` varchar(255) default NULL,
  `settings_html_function` varchar(255) default NULL,
  `sort_order` int(11) default '0',
  PRIMARY KEY  (`settingsID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8  ;

DROP TABLE IF EXISTS `SC_settings_groups`;
CREATE TABLE `SC_settings_groups` (
  `settings_groupID` int(11) NOT NULL auto_increment,
  `settings_group_name` varchar(64) default NULL,
  `sort_order` int(11) default '0',
  PRIMARY KEY  (`settings_groupID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_shipping_methods`;
CREATE TABLE `SC_shipping_methods` (
  `SID` int(11) NOT NULL auto_increment,
  `Enabled` int(11) default NULL,
  `module_id` int(11) default NULL,
  `sort_order` int(11) default '0',
  `Name_en` varchar(30) default NULL,
  `description_en` varchar(255) default NULL,
  `email_comments_text_en` text,
  `Name_ru` varchar(30) default NULL,
  `description_ru` varchar(255) default NULL,
  `email_comments_text_ru` text,
  `logo` text,  
  PRIMARY KEY  (`SID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_shopping_carts`;
CREATE TABLE `SC_shopping_carts` (
  `customerID` int(11) NOT NULL default '0',
  `itemID` int(11) NOT NULL default '0',
  `Quantity` int(11) default NULL,
  PRIMARY KEY  (`customerID`,`itemID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_shopping_cart_items`;
CREATE TABLE `SC_shopping_cart_items` (
  `itemID` int(11) NOT NULL auto_increment,
  `productID` int(11) default NULL,
  PRIMARY KEY  (`itemID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `SC_shopping_cart_items_content`;
CREATE TABLE `SC_shopping_cart_items_content` (
  `itemID` int(11) NOT NULL default '0',
  `variantID` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_spmodules`;
CREATE TABLE `SC_spmodules` (
  `module_id` int(11) NOT NULL auto_increment,
  `module_type` INT( 11 ),
  `module_name` varchar(255) default NULL,
  `ModuleClassName` varchar(255) default NULL,
  PRIMARY KEY  (`module_id`),
  KEY `module_type` (  `module_type` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_spmodules_settings`;
CREATE TABLE `SC_spmodules_settings` (
  `module_id` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `value` varchar(255) default NULL,
  PRIMARY KEY  (`module_id`,`field`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_subscribers`;
CREATE TABLE `SC_subscribers` (
  `MID` int(11) NOT NULL auto_increment,
  `Email` varchar(50) default NULL,
  `customerID` int(11) default NULL,
  PRIMARY KEY  (`MID`),
  KEY `customerID` (  `customerID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_system`;
CREATE TABLE `SC_system` (
  `varName` varchar(255) default NULL,
  `value` varchar(255) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_tagged_objects`;
CREATE TABLE `SC_tagged_objects` (
  `tag_id` int(10) unsigned NOT NULL default '0',
  `object_id` int(10) unsigned NOT NULL default '0',
  `object_type` enum('product') NOT NULL default 'product',
  `language_id` int(11) NOT NULL default '0',
  KEY `tag_id` (`tag_id`),
  KEY `tag_id_2` ( `object_id` , `tag_id` ),
  KEY `object_type` (`object_type`,`language_id`,`object_id`),
  KEY `language_id` (`language_id`),
  KEY `object_type_2` (`object_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_tags`;
CREATE TABLE `SC_tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_tax_classes`;
CREATE TABLE `SC_tax_classes` (
  `classID` int(11) NOT NULL auto_increment,
  `name` varchar(64) default NULL,
  `address_type` int(11) default NULL,
  PRIMARY KEY  (`classID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_tax_rates`;
CREATE TABLE `SC_tax_rates` (
  `classID` int(11) NOT NULL default '0',
  `countryID` int(11) NOT NULL default '0',
  `isGrouped` tinyint(1) default NULL,
  `value` float default NULL,
  `isByZone` tinyint(1) default NULL,
  PRIMARY KEY  (`classID`,`countryID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_tax_rates__zones`;
CREATE TABLE `SC_tax_rates__zones` (
  `classID` int(11) NOT NULL default '0',
  `zoneID` int(11) NOT NULL default '0',
  `value` float default NULL,
  `isGrouped` tinyint(1) default NULL,
  PRIMARY KEY  (`classID`,`zoneID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_tax_zip`;
CREATE TABLE `SC_tax_zip` (
  `tax_zipID` int(11) NOT NULL auto_increment,
  `classID` int(11) default NULL,
  `countryID` int(11) default NULL,
  `zip_template` varchar(255) default NULL,
  `value` float default NULL,
  PRIMARY KEY  (`tax_zipID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `SC_zones`;
CREATE TABLE `SC_zones` (
  `zoneID` int(11) NOT NULL auto_increment,
  `zone_code` varchar(64) default NULL,
  `countryID` int(11) default NULL,
  `zone_name_en` varchar(64) default NULL,
  `zone_name_ru` varchar(64) default NULL,
  PRIMARY KEY  (`zoneID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS SC__courier_rates;
CREATE TABLE SC__courier_rates (
  `module_id` int(10) unsigned NOT NULL default '0',
  `orderAmount` float default NULL,
  `rate` float default NULL,
  `isPercent` tinyint(1) default NULL,
  KEY `module_id` (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS SC__courier_rates2;
CREATE TABLE SC__courier_rates2 (
  `module_id` int(10) unsigned NOT NULL default '0',
  `orderAmount` float default NULL,
  `rate` float default NULL,
  `isPercent` tinyint(1) default NULL,
  KEY `module_id` (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS SC__intershipper_carriers;
CREATE TABLE SC__intershipper_carriers (
  `module_id` int(10) unsigned NOT NULL default '0',
  `carrierID` int(11) default NULL,
  `account` varchar(50) default NULL,
  `invoiced` tinyint(1) default NULL,
  KEY `module_id` (`module_id`,`carrierID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS SC__module_shipping_bycountries_byzones_rates;
CREATE TABLE SC__module_shipping_bycountries_byzones_rates (
  `module_id` int(10) unsigned NOT NULL default '0',
  `countryID` int(11) default NULL,
  `zoneID` int(11) default NULL,
  `shipping_rate` float default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS SC__module_shipping_bycountries_byzones_rates_percent;
CREATE TABLE SC__module_shipping_bycountries_byzones_rates_percent (
  `module_id` int(10) unsigned NOT NULL default '0',
  `countryID` int(11) default NULL,
  `zoneID` int(11) default NULL,
  `shipping_rate` float default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS SC__module_payment_invoice_jur;
CREATE TABLE SC__module_payment_invoice_jur (
  `module_id` int(10) unsigned default NULL,
  `orderID` int(11) default NULL,
  `company_name` varchar(64) default NULL,
  `company_inn` varchar(64) default NULL,
  `nds_included` int(11) default '0',
  `nds_rate` float default '0',
  `RUR_rate` float default '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS SC__module_payment_invoice_phys;
CREATE TABLE SC__module_payment_invoice_phys (
  `module_id` int(10) unsigned default NULL,
  `orderID` int(11) default NULL,
  `order_amount_string` varchar(64) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;