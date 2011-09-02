<?php
define('DBTABLE_PREFIX', 'SC_');

define('SYSTEM_TABLE', DBTABLE_PREFIX.'system');
define('ORDERS_TABLE', DBTABLE_PREFIX.'orders');
define('ORDER_STATUSES_TABLE', DBTABLE_PREFIX.'order_status');
define('ORDERED_CARTS_TABLE', DBTABLE_PREFIX.'ordered_carts');
define('PRODUCTS_TABLE', DBTABLE_PREFIX.'products');
define('CATEGORIES_TABLE', DBTABLE_PREFIX.'categories');
define('CATEGORIY_PRODUCT_TABLE', DBTABLE_PREFIX.'category_product');
define('SHOPPING_CARTS_TABLE', DBTABLE_PREFIX.'shopping_carts');
define('NEWS_TABLE', DBTABLE_PREFIX.'news_table');
define('DISCUSSIONS_TABLE', DBTABLE_PREFIX.'discussions');
define('MAILING_LIST_TABLE', DBTABLE_PREFIX.'subscribers');
define('RELATED_PRODUCTS_TABLE', DBTABLE_PREFIX.'related_items');
define('PRODUCT_OPTIONS_TABLE', DBTABLE_PREFIX.'product_options');
define('PRODUCT_OPTIONS_VALUES_TABLE', DBTABLE_PREFIX.'product_options_values');
define('PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE', DBTABLE_PREFIX.'products_opt_val_variants');
define('PRODUCTS_OPTIONS_SET_TABLE', DBTABLE_PREFIX.'product_options_set');
define('CUSTOMERS_TABLE', DBTABLE_PREFIX.'customers');
define('CUSTGROUPS_TABLE', DBTABLE_PREFIX.'custgroups');
define('COUNTRIES_TABLE', DBTABLE_PREFIX.'countries');
define('ZONES_TABLE', DBTABLE_PREFIX.'zones');
define('CUSTOMER_ADDRESSES_TABLE', DBTABLE_PREFIX.'customer_addresses');
define('CUSTOMER_REG_FIELDS_TABLE', DBTABLE_PREFIX.'customer_reg_fields');
define('CUSTOMER_REG_FIELDS_VALUES_TABLE', DBTABLE_PREFIX.'customer_reg_fields_values');
define('CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG', DBTABLE_PREFIX.'customer_reg_fields_values_quickreg');
define('SHIPPING_METHODS_TABLE', DBTABLE_PREFIX.'shipping_methods');
define('PAYMENT_TYPES_TABLE', DBTABLE_PREFIX.'payment_types');
define('SHIPPING_METHODS_PAYMENT_TYPES_TABLE', DBTABLE_PREFIX.'payment_types__shipping_methods');
define('CURRENCY_TYPES_TABLE', DBTABLE_PREFIX.'currency_types');
define('SHOPPING_CART_ITEMS_TABLE', DBTABLE_PREFIX.'shopping_cart_items');
define('SHOPPING_CART_ITEMS_CONTENT_TABLE', DBTABLE_PREFIX.'shopping_cart_items_content');
define('PRODUCT_PICTURES', DBTABLE_PREFIX.'product_pictures');
define('AUX_PAGES_TABLE', DBTABLE_PREFIX.'aux_pages');
define('SETTINGS_GROUPS_TABLE', DBTABLE_PREFIX.'settings_groups');
define('SETTINGS_TABLE', DBTABLE_PREFIX.'settings');
define('CATEGORY_PRODUCT_OPTIONS_TABLE', DBTABLE_PREFIX.'category__product_options');
define('CATEGORY_PRODUCT_OPTION_VARIANTS', DBTABLE_PREFIX.'category_product_options__variants');
define('TAX_CLASSES_TABLE', DBTABLE_PREFIX.'tax_classes');
define('TAX_RATES_TABLE', DBTABLE_PREFIX.'tax_rates');
define('TAX_RATES_ZONES_TABLE', DBTABLE_PREFIX.'tax_rates__zones');
define('TAX_ZIP_TABLE', DBTABLE_PREFIX.'tax_zip');
define('MODULES_TABLE', DBTABLE_PREFIX.'spmodules');
define('ORDER_PRICE_DISCOUNT_TABLE', DBTABLE_PREFIX.'order_price_discount');
define('ORDER_STATUS_CHANGE_LOG_TABLE', DBTABLE_PREFIX.'order_status_changelog');
define('LINK_EXCHANGE_CATEGORIES_TABLE', DBTABLE_PREFIX.'linkexchange_categories');
define('LINK_EXCHANGE_LINKS_TABLE', DBTABLE_PREFIX.'linkexchange_links');
define('AFFILIATE_COMMISSIONS_TABLE', DBTABLE_PREFIX.'aff_commissions');
define('AFFILIATE_PAYMENTS_TABLE', DBTABLE_PREFIX.'aff_payments');

define('HTMLCODES_TABLE', DBTABLE_PREFIX.'htmlcodes');

define('TBL_MODULES', DBTABLE_PREFIX.'modules');
define('TBL_MODULE_CONFIGS', DBTABLE_PREFIX.'module_configs');
define('TBL_CONFIG_SETTINGS', DBTABLE_PREFIX.'config_settings');
define('TBL_INTERFACE_INTERFACES', DBTABLE_PREFIX.'interface_interfaces');

define('LANGUAGE_TABLE',DBTABLE_PREFIX.'language');
define('LOCALGROUP_TABLE',DBTABLE_PREFIX.'localgroup');
define('LOCAL_TABLE',DBTABLE_PREFIX.'local');

define('DIVISIONS_TBL', DBTABLE_PREFIX.'divisions');
define('DIVISION_INTERFACE_TBL', DBTABLE_PREFIX.'division_interface');
define('DIVISION_CSETTINGS_TBL', DBTABLE_PREFIX.'division_custom_settings');

define('TAGS_TBL', DBTABLE_PREFIX.'tags');
define('TAGGED_OBJECTS_TBL', DBTABLE_PREFIX.'tagged_objects');
define('TBL_DIVISION_ACCESS', DBTABLE_PREFIX.'division_access');

define('TBL_PRODUCT_LIST', DBTABLE_PREFIX.'product_list');
define('TBL_PRODUCT_LIST_ITEM', DBTABLE_PREFIX.'product_list_item');
?>