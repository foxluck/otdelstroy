<?php
//updates/update278.2.php:
//CLEAN DATA FOR ALTER TABLE
mysql_query('SET NAMES UTF8');
mysql_query("set character_set_client='UTF8'");
mysql_query("set character_set_results='UTF8'");
mysql_query("set collation_connection='UTF8_general_ci'");

mysql_query("UPDATE `SC_aux_pages` SET `aux_page_slug` = `aux_page_ID` WHERE `aux_page_slug` IS NULL OR `aux_page_slug` = ''");
//ALTER TABLES
//mysql_query("ALTER TABLE `SC_news_table` ADD INDEX ( `add_stamp` , `priority` ) ");
//mysql_query("ALTER TABLE `SC_htmlcodes` ADD INDEX ( `key` ) ");
//mysql_query("ALTER TABLE `SC_modules` ADD INDEX ( `ModuleID` ) ");
//mysql_query("ALTER TABLE `SC_module_configs` ADD INDEX ( `ModuleConfigID` , `ModuleID` ) ");
//mysql_query("ALTER TABLE `SC_divisions` ADD INDEX ( `xID` , `xUnicKey` ) ");
//mysql_query("ALTER TABLE `SC_local` ADD INDEX ( `id` , `lang_id` ) ");
mysql_query("ALTER TABLE `SC_customer_addresses` CHANGE `address` `address` TEXT DEFAULT NULL");
mysql_query("ALTER TABLE `SC_orders` CHANGE `customers_comment` `customers_comment` TEXT DEFAULT NULL");  
mysql_query("ALTER TABLE `SC_orders` CHANGE `shipping_address` `shipping_address` TEXT DEFAULT NULL ,
CHANGE `billing_address` `billing_address` TEXT DEFAULT NULL");

mysql_query("ALTER TABLE `SC_tagged_objects` ADD INDEX ( `object_id` , `tag_id` )");
mysql_query("ALTER TABLE `SC_language` ADD `direction` INT( 1 ) UNSIGNED DEFAULT '0' NOT NULL ");

//mysql_query("ALTER TABLE `SC_local` DROP PRIMARY KEY,   ADD PRIMARY KEY(`id`,`lang_id`)");
//INSERT SETTINGS


mysql_query("ALTER TABLE `SC_settings` CHANGE `settings_value` `settings_value` TEXT");
mysql_query("INSERT INTO `SC_settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES (4, 'CONF_ENABLE_PRODUCT_SKU', '0', 'cfg_enable_product_sku_title', 'cfg_enable_product_sku_description', 'setting_CHECK_BOX(', 5)");
mysql_query("INSERT INTO `SC_settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES (4, 'CONF_PICTRESIZE_QUALITY', '80', 'cfg_picture_resize_quality_title', 'cfg_picture_resize_quality_description', 'setting_TEXT_BOX(2,', 130)");
mysql_query("INSERT INTO `SC_settings` VALUES (NULL, 2, 'CONF_STOREFRONT_TIME_ZONE', '51', 'cfg_frontend_time_zone', 'cfg__frontend_time_zone_descr', 'Time::setting_SELECT_TIME_ZONE(', 61)");
mysql_query("INSERT INTO `SC_settings` VALUES (NULL, 2, 'CONF_STOREFRONT_TIME_ZONE_DST', '0', 'cfg_frontend_time_zone_dst', 'cfg__frontend_time_zone_dst_descr', 'setting_CHECK_BOX(', 62)");
mysql_query("INSERT INTO `SC_settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES (7, 'GOOGLE_ANALYTICS_CUSTOM_SE', '// Google EMEA Image domains\r\npageTracker._addOrganic(\"images.google.co.uk\",\"q\");\r\npageTracker._addOrganic(\"images.google.es\",\"q\");\r\npageTracker._addOrganic(\"images.google.pt\",\"q\");\r\npageTracker._addOrganic(\"images.google.it\",\"q\");\r\npageTracker._addOrganic(\"images.google.fr\",\"q\");\r\npageTracker._addOrganic(\"images.google.nl\",\"q\");\r\npageTracker._addOrganic(\"images.google.be\",\"q\");\r\npageTracker._addOrganic(\"images.google.de\",\"q\");\r\npageTracker._addOrganic(\"images.google.no\",\"q\");\r\npageTracker._addOrganic(\"images.google.se\",\"q\");\r\npageTracker._addOrganic(\"images.google.dk\",\"q\");\r\npageTracker._addOrganic(\"images.google.fi\",\"q\");\r\npageTracker._addOrganic(\"images.google.ch\",\"q\");\r\npageTracker._addOrganic(\"images.google.at\",\"q\");\r\npageTracker._addOrganic(\"images.google.ie\",\"q\");\r\npageTracker._addOrganic(\"images.google.ru\",\"q\");\r\npageTracker._addOrganic(\"images.google.pl\",\"q\");\r\n\r\n// Other Google Image search\r\npageTracker._addOrganic(\"images.google.com\",\"q\");\r\npageTracker._addOrganic(\"images.google.ca\",\"q\");\r\npageTracker._addOrganic(\"images.google.com.au\",\"q\");\r\npageTracker._addOrganic(\"images.google\",\"q\");\r\n\r\n// Blogsearch\r\npageTracker._addOrganic(\"blogsearch.google\",\"q\");\r\n\r\n// Google EMEA Domains\r\npageTracker._addOrganic(\"google.co.uk\",\"q\");\r\npageTracker._addOrganic(\"google.es\",\"q\");\r\npageTracker._addOrganic(\"google.pt\",\"q\");\r\npageTracker._addOrganic(\"google.it\",\"q\");\r\npageTracker._addOrganic(\"google.fr\",\"q\");\r\npageTracker._addOrganic(\"google.nl\",\"q\");\r\npageTracker._addOrganic(\"google.be\",\"q\");\r\npageTracker._addOrganic(\"google.de\",\"q\");\r\npageTracker._addOrganic(\"google.no\",\"q\");\r\npageTracker._addOrganic(\"google.se\",\"q\");\r\npageTracker._addOrganic(\"google.dk\",\"q\");\r\npageTracker._addOrganic(\"google.fi\",\"q\");\r\npageTracker._addOrganic(\"google.ch\",\"q\");\r\npageTracker._addOrganic(\"google.at\",\"q\");\r\npageTracker._addOrganic(\"google.ie\",\"q\");\r\npageTracker._addOrganic(\"google.ru\",\"q\");\r\npageTracker._addOrganic(\"google.pl\",\"q\");\r\n\r\n// Yahoo EMEA Domains\r\npageTracker._addOrganic(\"uk.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"es.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"pt.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"it.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"fr.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"nl.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"be.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"de.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"no.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"se.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"dk.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"fi.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"ch.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"at.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"ie.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"ru.yahoo.com\",\"p\");\r\npageTracker._addOrganic(\"pl.yahoo.com\",\"p\");\r\n\r\n// UK specific\r\npageTracker._addOrganic(\"hotbot.co.uk\",\"query\");\r\npageTracker._addOrganic(\"excite.co.uk\",\"q\");\r\npageTracker._addOrganic(\"bbc\",\"q\");\r\npageTracker._addOrganic(\"tiscali\",\"query\");\r\npageTracker._addOrganic(\"uk.ask.com\",\"q\");\r\npageTracker._addOrganic(\"blueyonder\",\"q\");\r\npageTracker._addOrganic(\"search.aol.co.uk\",\"query\");\r\npageTracker._addOrganic(\"ntlworld\",\"q\");\r\npageTracker._addOrganic(\"tesco.net\",\"q\");\r\npageTracker._addOrganic(\"orange.co.uk\",\"q\");\r\npageTracker._addOrganic(\"mywebsearch.com\",\"searchfor\");\r\npageTracker._addOrganic(\"uk.myway.com\",\"searchfor\");\r\npageTracker._addOrganic(\"searchy.co.uk\",\"search_term\");\r\npageTracker._addOrganic(\"msn.co.uk\",\"q\");\r\npageTracker._addOrganic(\"uk.altavista.com\",\"q\");\r\npageTracker._addOrganic(\"lycos.co.uk\",\"query\");\r\n\r\n// NL specific\r\npageTracker._addOrganic(\"chello.nl\",\"q1\");\r\npageTracker._addOrganic(\"home.nl\",\"q\");\r\npageTracker._addOrganic(\"planet.nl\",\"googleq=q\");\r\npageTracker._addOrganic(\"search.ilse.nl\",\"search_for\");\r\npageTracker._addOrganic(\"search-dyn.tiscali.nl\",\"key\");\r\npageTracker._addOrganic(\"startgoogle.startpagina.nl\",\"q\");\r\npageTracker._addOrganic(\"vinden.nl\",\"q\");\r\npageTracker._addOrganic(\"vindex.nl\",\"search_for\");\r\npageTracker._addOrganic(\"zoeken.nl\",\"query\");\r\npageTracker._addOrganic(\"zoeken.track.nl\",\"qr\");\r\npageTracker._addOrganic(\"zoeknu.nl\",\"Keywords\");\r\n\r\n// Extras\r\npageTracker._addOrganic(\"alltheweb\",\"q\");\r\npageTracker._addOrganic(\"ananzi\",\"qt\");\r\npageTracker._addOrganic(\"anzwers\",\"search\");\r\npageTracker._addOrganic(\"araby.com\",\"q\");\r\npageTracker._addOrganic(\"dogpile\",\"q\");\r\npageTracker._addOrganic(\"elmundo.es\",\"q\");\r\npageTracker._addOrganic(\"ezilon.com\",\"q\");\r\npageTracker._addOrganic(\"hotbot\",\"query\");\r\npageTracker._addOrganic(\"indiatimes.com\",\"query\");\r\npageTracker._addOrganic(\"iafrica.funnel.co.za\",\"q\");\r\npageTracker._addOrganic(\"mywebsearch.com\",\"searchfor\");\r\npageTracker._addOrganic(\"search.aol.com\",\"encquery\");\r\npageTracker._addOrganic(\"search.indiatimes.com\",\"query\");\r\npageTracker._addOrganic(\"searcheurope.com\",\"query\");\r\npageTracker._addOrganic(\"suche.web.de\",\"su\");\r\npageTracker._addOrganic(\"terra.es\",\"query\");\r\npageTracker._addOrganic(\"voila.fr\",\"kw\");\r\n\r\n// Extras RU\r\npageTracker._addOrganic(\"mail.ru\", \"q\");\r\npageTracker._addOrganic(\"rambler.ru\", \"words\");\r\npageTracker._addOrganic(\"nigma.ru\", \"s\");\r\npageTracker._addOrganic(\"blogs.yandex.ru\", \"text\");\r\npageTracker._addOrganic(\"yandex.ru\", \"text\");\r\npageTracker._addOrganic(\"webalta.ru\", \"q\");\r\npageTracker._addOrganic(\"aport.ru\", \"r\");\r\npageTracker._addOrganic(\"poisk.ru\", \"text\");\r\npageTracker._addOrganic(\"km.ru\", \"sq\");\r\npageTracker._addOrganic(\"liveinternet.ru\", \"ask\");\r\npageTracker._addOrganic(\"gogo.ru\", \"q\");\r\npageTracker._addOrganic(\"gde.ru\", \"keywords\");\r\npageTracker._addOrganic(\"quintura.ru\", \"request\");\r\npageTracker._addOrganic(\"price.ru\", \"pnam\");\r\npageTracker._addOrganic(\"torg.mail.ru\", \"q\");\r\n\r\n\r\n// Extras BY\r\npageTracker._addOrganic(\"akavita.by\", \"z\");\r\npageTracker._addOrganic(\"tut.by\", \"query\");\r\npageTracker._addOrganic(\"all.by\", \"query\");\r\n\r\n\r\n// Extras UA\r\npageTracker._addOrganic(\"meta.ua\", \"q\");\r\npageTracker._addOrganic(\"bigmir.net\", \"q\");\r\npageTracker._addOrganic(\"i.ua\", \"q\");\r\npageTracker._addOrganic(\"online.ua\", \"q\");\r\npageTracker._addOrganic(\"a.ua\", \"s\");\r\npageTracker._addOrganic(\"ukr.net\", \"search_query\");\r\npageTracker._addOrganic(\"search.com.ua\", \"q\");\r\npageTracker._addOrganic(\"search.ua\", \"query\");', 'cfg_ga_js_custom_se', 'cfg_ga_js_custom_se_description', 'setting_TEXT_AREA(', 4)");

//INSERT CUSTOM HTML CODES

mysql_query("INSERT IGNORE INTO `SC_htmlcodes` (`key`, `title`, `code`) VALUES
('2309usj8', 'appliance', '<div class=\"col_header lightbluebg\">{lbl_news}</div>'),
('5sck5tch', 'appliance', '<a href=\"\" class=\"apl_logo\"><span class=\"apl_l1\">YOUR</span><span class=\"apl_l2\">COMPANY</span><span class=\"apl_l3\">NAME</span></a>'),
('7055hfy8', 'appliance', '<div class=\"apl_slogan\"><span class=\"apl_s1\">New</span><span class=\"apl_s2\">Appliance</span><span class=\"apl_s3\">Shop</span></div>'),
('eiqt7wv3', 'appliance', '<div class=\"apl_lang\">{lbl_language}:</div>'),
('gou00yo7', 'appliance', '<div class=\"col_header bluebg\">{lbl_catalog}</div>'),
('j1zduv0p', 'appliance', '<div class=\"background1\" style=\"padding: 10px; font-size: 120%; font-weight: bold;\">{lbl_browse_by_category}</div>'),
('k5e43nju', 'appliance', '<div class=\"col_header bluebg\">{lbl_poll}</div>'),
('857zn7vi', 'aqua', '<div class=\"news_header\"><h3>{lbl_news}</h3></div>'),
('b5kq0gbj', 'aqua', '<div class=\"aqu_company\"><a href=\"\"><span class=\"light\">Company</span><span class=\"dark\">Name</span></a></div>'),
('hm1eo41h', 'aqua', '<div class=\"poll_header\"><h3>{lbl_poll}</h3></div>'),
('hyh8mor9', 'aqua', '<span class=\"aqu_tel\">(123) 555-1234\r\n</span>'),
('n026s0bl', 'aqua', '<div class=\"cat_header\"><h3>{lbl_catalog}</h3></div>'),
('whofto05', 'aqua', '<h1 class=\"aqu_browse\" class=\"mdr_main_header\">{lbl_browse_by_category}:</h1>'),
('ww71q5hv', 'aqua', '<div class=\"lang_label\">{lbl_language}:</div>'),
('1idtjfyd', 'city', '<div class=\"right_bg_pink\"> </div>'),
('2j3zx20a', 'city', '<a href=\"\"><span class=\"cty_l1\">YOUR</span><span class=\"cty_l2\">COMPANY</span><span class=\"cty_l3\">NAME</span></a>'),
('bfe2ltrx', 'city', '<div class=\"right_bg_red\"> </div>'),
('oil2iz4a', 'city', '<div class=\"left_bg_navy\"> </div>'),
('4j8ucbo7', 'classic', '<div class=\"col_header\">{lbl_catalog}</div>'),
('j4qyt14q', 'classic', '<div class=\"col_header\">{lbl_language}</div>'),
('t72gcmgp', 'classic', '<h1 style=\"color: white\">My Store</h1>'),
('uhyltsyy', 'classic', '<div class=\"col_header\">{lbl_poll}</div>'),
('wst55wn7', 'classic', '<div class=\"col_header\">{lbl_news}</div>'),
('1vxl4z1u', 'computer', '<div class=\"col_header\">{lbl_poll}</div>'),
('3qlymdjf', 'computer', 'test'),
('4u9yjvxt', 'computer', '<div class=\"col_header\">{lbl_news}</div>'),
('gnz1m6o2', 'computer', '<div class=\"col_header\"></div>'),
('o9lzcmgm', 'computer', '<div class=\"col_header\">&nbsp;</div>'),
('omphg9kb', 'computer', '<div class=\"col_header\">{lbl_catalog}</div>'),
('0b6u45d4', 'default', '<div class=\"news_header\"><h3>{lbl_news}</h3></div>'),
('3hmk7pem', 'default', '<label>{lbl_search}: </label>'),
('7tqa7d2d', 'default', '<div class=\"poll_header\"><h3>{lbl_poll}</h3></div>'),
('8g1gd6h8', 'default', '<h1 class=\"companyname\">My shop</h1>'),
('c7wj287f', 'default', '<div class=\"cat_header\"><h3>{lbl_catalog}</h3></div>'),
('de9hsbax', 'default', '<h1 class=\"mdr_main_header\">{lbl_browse_by_category}</h1>'),
('jymcwcmu', 'default', '<span class=\"tls_tel\">(123) 555-1234\r\n</span>'),
('ncxrvx57', 'default', '<div class=\"lang_label\">Language:</div>'),
('njr3gga6', 'default', '<h1 class=\"welcome\">{lbl_welcome_to_storefront} \"{\$smarty.const.CONF_SHOP_NAME}\"</h1>\r\n'),
('p5kgoddr', 'default', '<span class=\"lang_label\">Language:</span>'),
('j65towo9', 'demo', '<div class=\"col_header pink\">{lbl_poll}</div>'),
('rjxn8oml', 'demo', '<div class=\"col_header green\">{lbl_news}</div>'),
('1f4a22e4', 'flowers', '<div class=\"flw_bl\"></div>'),
('2tuzady5', 'flowers', '<div class=\"flw_br\"></div><div class=\"flw_bl\"></div><div class=\"flw_tl\"></div><div class=\"flw_tr\"></div>'),
('otcfncdy', 'flowers', '<div class=\"flw_company\"><a href=\"\"><span class=\"light\">Company</span><span class=\"dark\">Name</span></a></div>'),
('qt8jxz12', 'flowers', '<div class=\"lang_label\">{lbl_language}:</div>'),
('r0lm25kj', 'flowers', '<div class=\"flw_company\"><a href=\"\"><span class=\"light\">Company</span><span class=\"dark\">Name</span></a></div>'),
('wbsbuve7', 'flowers', '<div class=\"flw_tr\"></div>'),
('zigtewl3', 'flowers', '<div class=\"flw_tl\"></div>')");


mysql_query("INSERT IGNORE INTO `SC_htmlcodes` (`key`, `title`, `code`) VALUES
('1g2qude4', 'glamour', '<div class=\"col_header pink3bg r_header\">{lbl_currency}</div>'),
('6ey329o1', 'glamour', '<div class=\"col_header pinkbg r_header\">{lbl_language}</div>'),
('ea4wstp3', 'glamour', '<a href=\"\"><span class=\"glr_l1\">YOUR</span><span class=\"glr_l2\">COMPANY</span><span class=\"glr_l3\">NAME</span></a>'),
('fpneb9ck', 'glamour', '<div class=\"col_header pinkbg\">{lbl_search}</div>'),
('jlwqn5pj', 'glamour', '<div class=\"col_header pink2bg r_header\">{lbl_news}</div>'),
('ntj3gaot', 'glamour', '<div class=\"col_header purpurbg\">{lbl_cataloge}</div>'),
('zlpc2hvu', 'glamour', '<div class=\"darkpinkbg\"><div class=\"whiteborder\"><div class=\"purpurfolder\">&nbsp;</div></div></div>'),
('zyp0nrpq', 'glamour', '<div class=\"purpurbg\"><div class=\"whiteborder\"><div class=\"pinkfolder\">&nbsp;</div></div></div>'),
('2vgnavg7', 'green', '<div class=\"cpt_custom_html\"><a href=\"\"><span class=\"grn_l1\">YOUR</span><span class=\"grn_l2\">COMPANY</span><span class=\"grn_l3\">NAME</span></a></div>'),
('iek3eg75', 'green', '<div class=\"col_header\">{lbl_news}</div>'),
('iy000qa3', 'green', '<div class=\"col_header\">{lbl_catalog}</div>'),
('j1gq0b6t', 'green', '<div class=\"under_searchform\"> </div>'),
('o5fwylp5', 'green', '<div class=\"col_header_dark\">{lbl_poll}</div>'),
('uid5yfy7', 'green', '<a href=\"\"><span class=\"grn_l1\">YOUR</span><span class=\"grn_l2\">COMPANY</span><span class=\"grn_l3\">NAME</span></a>'),
('3vm6694u', 'modern', '<div class=\"col_header green\">{lbl_str_search}</div>'),
('gyfor9rz', 'modern', '<a href=\"/\"><span class=\"mdr_l1\">Your</span><span class=\"mdr_l2\">Company</span><span class=\"mdr_l3\">Name</span></a>'),
('lch82oy0', 'modern', '<div class=\"col_header\">{lbl_catalog}</div>'),
('n0oy9wvn', 'modern', '<div class=\"col_header green\">{lbl_poll}</div>'),
('fjuuxwn8', 'ocean', '<div class=\"ocn_left_wh\"><div class=\"col_header bluebg\">{lbl_language}</div></div>'),
('ieabmzcx', 'ocean', '<div class=\"ocn_left_wh\"><div class=\"col_header bluebg\">{lbl_catalog}</div>'),
('ixo6s12z', 'ocean', '<div class=\"ocn_left_wh\"><div class=\"col_header orangebg\">{lbl_search}</div></div>'),
('lxbqae3k', 'ocean', '<div class=\"ocn_right_wh\"><div class=\"ocn_rightrel\"><div class=\"ocn_guy\">  </div></div></div>'),
('np1b607u', 'ocean', '<div class=\"ocn_left_wh\"><div class=\"col_header bluebg\">{lbl_currency}</div></div>'),
('qigab725', 'ocean', '<a href=\"\" class=\"ocn_logo\"><span class=\"ocn_l1\">YOUR</span><span class=\"ocn_l2\">COMPANY</span><span class=\"ocn_l3\">NAME</span></a>'),
('tcfisslq', 'ocean', '<div class=\"ocn_left_wh\"><div class=\"col_header greenbg\">{lbl_news}</div></div>'),
('w0c87mi6', 'ocean', '<div class=\"ocn_left_wh\"><div class=\"col_header bluebg\">{lbl_poll}</div></div>'),
('4e9wmn6l', 'photo', '<div class=\"pht_white\"> <div class=\"pht-h-tl\"></div> <div class=\"pht-h-tr\"></div> <div class=\"pht-mainhead\">{lbl_browse_by_category}</div></div>'),
('8fh7g6tl', 'photo', '<div class=\"col_header\">{lbl_news}</div>'),
('dbjgyz5p', 'photo', '<div class=\"col_header\">{lbl_search}</div>'),
('hzl3kfaj', 'photo', '<div class=\"col_header first\">{lbl_catalog}</div>'),
('k9d8aq0c', 'photo', '<div class=\"col_header\">{lbl_poll}</div>'),
('mw2w3xyf', 'photo', '<div class=\"pht_lang\">{lbl_language}:</div>'),
('o2tr1rl5', 'photo', '<div class=\"pht_white\"> <div class=\"pht-h-tl\"></div> <div class=\"pht-h-tr\"></div> <div class=\"pht-mainhead\">{lbl_special_offers}</div></div>'),
('q11rslde', 'photo', '<div class=\"pht_main-pic\"><div class=\"pht_promo_slogan\">New <span>Special</span> Offers</div></div>'),
('0m8bt2r7', 'sale', '<span class=\"sale_tel\">(123) 555-1234\r\n</span>'),
('7chazboj', 'sale', '<div class=\"red_header\"><h3>{lbl_news}</h3></div>'),
('9yfp12we', 'sale', '<div class=\"red_header\"><h3>{lbl_poll}</h3></div>'),
('n6fo06i1', 'sale', '<div class=\"lang_label\">{lbl_language}:</div>'),
('uq5irul3', 'sale', '<div class=\"search_header\"><label for=\"searchstring\">{lbl_search}: </label></div>'),
('14fuvuhc', 'sci', '<div class=\"sci_box_right_b\"></div>')");


mysql_query("INSERT IGNORE INTO `SC_htmlcodes` (`key`, `title`, `code`) VALUES
('77i7m2cq', 'sci', '<div class=\"col_header\">{lbl_catalog}</div>'),
('gcrhhlwd', 'sci', '<div class=\"sci_box_left_b\"></div>'),
('kcb6pimm', 'sci', '<div class=\"col_header\">{lbl_search}</div>'),
('s98230gp', 'sci', '<div class=\"col_header\">{lbl_poll}</div>'),
('sgbhydje', 'sci', '<h1>Welcome</h1>'),
('umlp7cha', 'sci', '<a href=\"\"><span class=\"sci_l1\">Your</span><span class=\"sci_l2\">Company</span><span class=\"sci_l3\">Name</span></a>'),
('vubushuf', 'sci', '<div class=\"col_header\">{lbl_news}</div>'),
('0a5i24lc', 'shopping', '<div class=\"col_header bluebg\">{lbl_catalog}</div>'),
('1fkcmhu7', 'shopping', '<div class=\"col_header bluebg\">{lbl_news}</div>'),
('fq5rhkq1', 'shopping', '<div class=\"col_header middlebluebg\">{lbl_poll}</div>'),
('m2uhjlb1', 'shopping', '<div class=\"col_header pinkbg\">{lbl_language}</div>'),
('pv7too9w', 'shopping', '<a href=\"\" class=\"shp_logo\"><span class=\"shp_l1\">YOUR</span><span class=\"shp_l2\">COMPANY</span><span class=\"shp_l3\">NAME</span></a>'),
('3u28ilit', 'tableware', '<div class=\"news_header\"><h3>{lbl_news}</h3></div>'),
('9kt3luhk', 'tableware', '<div class=\"lang_label\">{lbl_language}:</div>'),
('cw9d10vf', 'tableware', '<div class=\"poll_header\"><h3>{lbl_poll}</h3></div>'),
('htv7izvs', 'tableware', '<div class=\"cat_header\"><h3>{lbl_catalog}</h3></div>'),
('pne5kpsa', 'tableware', '<div class=\"tbw_company_name\"><a href=\"\"><span class=\"light\">Company</span><span class=\"dark\">Name</span></a></div>'),
('wq7w3cb8', 'tableware', '<div class=\"tbw_company\"><a href=\"\"><span class=\"light\">Company</span><span class=\"dark\">Name</span></a></div>'),
('y46y0wg8', 'tableware', '<h2 class=\"tbw_category\">{lbl_browse_by_category}</h2>'),
('blnq0vma', 'time', '<h1>{lbl_special_offers}</h1>'),
('pdhnxqq7', 'time', '<div class=\"tim_logo\">Your Company</div>'),
('22wpr1g4', 'toys', '<h2>{lbl_browse_by_category}</h2>'),
('8ynhvcyo', 'toys', '<h2>{lbl_special_offers}</h2>'),
('bf3vztnb', 'toys', '<div class=\"tys_lang_label\">{lbl_language}:</div>'),
('ch251y70', 'toys', '<div class=\"tys_ltop4\"></div>'),
('w4a0p6wf', 'toys', '<div class=\"col_header tys_ltop2\">{lbl_news}</div>'),
('x15293g0', 'toys', '<div class=\"col_header tys_ltop1\">{lbl_catalog}</div>'),
('x7o1m64e', 'toys', '<div class=\"col_header tys_ltop3\">{lbl_poll}</div>')");


//INSERT LOCALIZATION STRINGS
//rus_delete
$___isRUS = false;
if($___res = mysql_query('SELECT COUNT(*) FROM `SC_language` WHERE `id`=1')){
	$___row = mysql_fetch_row($___res);
	if(intval($___row[0])==1)$___isRUS = true;

}
if($___isRUS){

	mysql_query("DELETE FROM `SC_local` WHERE `lang_id`=1 AND `id` IN ('cfg__frontend_time_zone_descr',
'cfg__frontend_time_zone_dst_descr',
'cfg_enable_product_sku_description',
'cfg_enable_product_sku_title',
'cfg_frontend_time_zone',
'cfg_frontend_time_zone_dst',
'cfg_ga_js_custom_se',
'cfg_ga_js_custom_se_description',
'cfg_picture_resize_quality_description',
'cfg_picture_resize_quality_title',
'cpt_lbl_request_product_count',
'goto_shopping',
'lbl_follow_link',
'loc_change_default_description',
'loc_lang_direction',
'loc_lang_ltr_descr',
'loc_lang_ltr_disabled',
'loc_lang_ltr_enabled',
'prdimport_csv_use_structure',
'prdimport_source_column',
'prdimport_target_column',
'prdimport_found_n_columns',
'imm_upload_link',
'imm_upload_swf_link',
'imm_images_count_info',
'imm_view_mode',
'imm_view_mode_list',
'imm_view_mode_thumbnails',
'prdcat_product_n_duplicated',
'prdcart_products_duplicate_selected',
'cpt_lbl_block_height',
'welcome_to_storefront',
'powered_by',
'powered_by_text',
'imm_del_confirmation')");


	//rus


	mysql_query("INSERT IGNORE INTO `SC_local` (`id`,`lang_id`,`value`,`group`,`subgroup`) VALUES ('cfg__frontend_time_zone_descr',1,'Укажите часовой пояс, согласно которому будет отображаться время в пользовательской части интернет-магазина. Пересчет будет производится относительно часового пояса сервера, который указывается в настройках WebAsyst Installer.<br />Настройка влияет только на пользовательскую часть. В режиме администрирования время отображается согласно часовому поясу, установленному в личных настройках пользователя (администратора).','back','cfg'),
('cfg__frontend_time_zone_dst_descr',1,'&nbsp;','back','cfg'),
('cfg_enable_product_sku_description',1,'Артикул, если он введен в свойствах продукта, используется для более удобного поиска и управления продуктами в администрировании. Если вы включите эту опцию, артикул будет также отображаться в пользовательской части вашего интернет-магазина.','back','cfg'),
('cfg_enable_product_sku_title',1,'Показывать артикул продукта в пользовательской части','back','cfg'),
('cfg_frontend_time_zone',1,'Часовой пояс интернет-магазина','back','cfg'),
('cfg_frontend_time_zone_dst',1,'Переход на летнее время','back','cfg'),
('cfg_ga_js_custom_se',1,'Дополнить код Google Analytics','back','cfg'),
('cfg_ga_js_custom_se_description',1,'Здесь вы можете указать произвольный код JavaScript, который будет добавлен в основной код Google Analytics (например, код, <a href=\"http://www.google.com/support/analytics/bin/answer.py?hl=ru&answer=57046\" target=\"_blank\">описывающий дополнительные поисковые системы</a> в отчетах о переходах).','back','cfg'),
('cfg_picture_resize_quality_description',1,'При загрузке изображений продуктов автоматически (с помощью библиотеки GD) создаются их уменьшенные копии и сохраняются в виде JPEG-файлов. Укажите качество изображений: 0 — меньше качество, меньше размер файла; 100 — выше качество, больше размер файла. Рекомендуемое значение — 80.','back','cfg'),
('cfg_picture_resize_quality_title',1,'Качество изображений продуктов после уменьшения размера (0 — хуже, 100 — лучше)','back','cfg'),
('cpt_lbl_request_product_count',1,'Запрашивать количество продуктов для добавления в корзину','back','cpt'),
('goto_shopping',1,'&laquo; вернуться к покупкам','general','gen'),
('lbl_follow_link',1,'Перейдите по ссылке:','general','gen'),
('loc_change_default_description',1,'<strong><span style=\"color: red;\">ВАЖНО:</span> Убедитесь, что вы перевели все данные на новый основной язык!</strong> Если перевод на новый язык, который вы выберите основным, не полный, то как в администрировании, так и в пользовательской части непереведенная информация (интерфейс, информация о продуктах и т.д.) будет показана некорректно (будут показаны либо пустые поля, либо ID строк).','back','loc'),
('loc_lang_direction',1,'Направление текста','back','loc'),
('loc_lang_ltr_descr',1,'Направление текста: LTR (слева-направо) или RTL (справа-налево).','back','loc'),
('loc_lang_ltr_disabled',1,'RTL','back','loc'),
('loc_lang_ltr_enabled',1,'LTR','back','loc'),
('prdimport_csv_use_structure',1,'Поиск соответствий продуктов в файле и базе данных осуществлять с учетом категорий (поиск только внутри категории)','back','imm'),
('prdimport_source_column',1,'Колонки в CSV-файле','back','imm'),
('prdimport_target_column',1,'Поля в базе данных','back','imm'),
('prdimport_found_n_columns',1,'В CSV-файле найдено %d колонок','back','imm'),
('imm_upload_link',1,'Загрузить по одному','back','imm'),
('imm_upload_swf_link',1,'Загрузить много изображений','back','imm'),
('imm_images_count_info',1,'Изображения: %d &mdash; %d из %d','back','imm'),
('imm_view_mode',1,'Вид','back','imm'),
('imm_view_mode_list',1,'списком','back','imm'),
('imm_view_mode_thumbnails',1,'эскизами','back','imm'),
('prdcat_product_n_duplicated',1,'Создано %d продуктов-дубликатов','back','prd'),
('prdcart_products_duplicate_selected',1,'Создать дубликаты','back','prd'),
('cpt_lbl_block_height',1,'Высота элемента li, в котором отображается продукт, в пикселях (оставьте пустым для автоматического расчета)','general','cfg'),
('welcome_to_storefront',1,'Интернет-магазин ','general','gen'),
('powered_by',1,'Работает на основе <a href=\"http://www.shop-script.ru/\" style=\"font-weight: normal\">скрипта интернет-магазина</a> <em>WebAsyst Shop-Script</em>','hidden','gen'),
('powered_by_text',1,'Работает на основе <em>WebAsyst Shop-Script</em>','hidden','gen'),
('imm_del_confirmation',1,'Вы уверены?','general','gen'),
('lbl_redirecting_to_idealbasic', 1, 'Сейчас Вы будете перенаправлены на сайт IdealBasic...', 'front', 'gen')");

	mysql_query("UPDATE `SC_local` SET `value` = 'Здесь вы можете создать совершенно произвольные характеристики (параметры), которые подходят продуктам вашего интернет-магазина - от цвета и размера, до мощности двигателя и тарифного плана. После добавления характеристики здесь вы можете заполнить ее значение для каждого вашего продукта.' WHERE CONVERT( `id` USING utf8 ) = 'prdopt_page_description' AND `lang_id` =1 LIMIT 1");
}

//eng_delete
$___isENG = false;
if($___res = mysql_query('SELECT COUNT(*) FROM `SC_language` WHERE `id`=2')){
	$___row = mysql_fetch_row($___res);
	if(intval($___row[0])==1)$___isENG = true;
}

if($___isENG){

	mysql_query("DELETE FROM `SC_local` WHERE `lang_id`=2 AND `id` IN ('cfg__frontend_time_zone_descr',
'cfg__frontend_time_zone_dst_descr',
'cfg_enable_product_sku_description',
'cfg_enable_product_sku_title',
'cfg_frontend_time_zone',
'cfg_frontend_time_zone_dst',
'cfg_ga_js_custom_se',
'cfg_ga_js_custom_se_description',
'cfg_picture_resize_quality_description',
'cfg_picture_resize_quality_title',
'cpt_lbl_request_product_count',
'goto_shopping',
'lbl_follow_link',
'loc_change_default_description',
'loc_lang_direction',
'loc_lang_ltr_descr',
'loc_lang_ltr_disabled',
'loc_lang_ltr_enabled',
'prdimport_csv_use_structure',
'prdimport_source_column',
'prdimport_target_column',
'prdimport_found_n_columns',
'imm_upload_link',
'imm_upload_swf_link',
'imm_images_count_info',
'imm_view_mode',
'imm_view_mode_list',
'imm_view_mode_thumbnails',
'prdcat_product_n_duplicated',
'prdcart_products_duplicate_selected',
'cpt_lbl_block_height',
'welcome_to_storefront',
'powered_by',
'powered_by_text',
'imm_del_confirmation')");


	//eng


	mysql_query("INSERT IGNORE INTO `SC_local` (`id`,`lang_id`,`value`,`group`,`subgroup`) VALUES ('cfg__frontend_time_zone_descr',2,'Select default time zone for displaying time in your public storefront according to your server time zone specified in WebAsyst Installer.<br />Time zone you select here is only for storefront. In backend time is displayed according to your user preferences (My settings screen).','back','cfg'),
('cfg__frontend_time_zone_dst_descr',2,'&nbsp;','back','cfg'),
('cfg_enable_product_sku_description',2,'Product code (SKU) is an optional product parameter, which can be useful for navigating and managing products in backend. If you enable this option, product code values will be displayed in your public storefront as well.','back','cfg'),
('cfg_enable_product_sku_title',2,'Show product codes (SKU) in storefront','back','cfg'),
('cfg_frontend_time_zone',2,'Default storefront time zone','back','cfg'),
('cfg_frontend_time_zone_dst',2,'Daylight saving time','back','cfg'),
('cfg_ga_js_custom_se',2,'Add custom JavaScript code to the Google Analytics tracking code','back','cfg'),
('cfg_ga_js_custom_se_description',2,'JavaScript code that you enter here will be added into Googe Analytics tracking code (e.g. you may add a code <a href=\"http://www.google.com/support/googleanalytics/bin/answer.py?answer=57046\" target=\"_blank\">identifying additional search engines</a> in the Referral reports).','back','cfg'),
('cfg_picture_resize_quality_description',2,'When uploading product images, thumbnails are created automatically (using GD extension). Enter thumbnails JPEG quality: 0 — worst quality, smaller file; 100 — best quality, biggest file. Recommended value — 80.','back','cfg'),
('cfg_picture_resize_quality_title',2,'Product thumbnails quality (0 — worst, 100 — best)','back','cfg'),
('cpt_lbl_request_product_count',2,'Request product quantity to be added to the shopping cart','back','cpt'),
('goto_shopping',2,'&laquo; back to shopping','general','gen'),
('lbl_follow_link',2,'Follow this link:','general','gen'),
('loc_change_default_description',2,'<strong><span style=\"color: red;\">IMPORTANT:</span> Make sure you have full translation of all data to the new default language!</strong> If you did not translate all info to the new default language (including product information and interface strings), then after you toggle default language, not translated data will be shown incorrectly.','back','loc'),
('loc_lang_direction',2,'Text direction','back','loc'),
('loc_lang_ltr_descr',2,'Choose between Left-to-right (LTR) and right-to-left (RTL) text direction.','back','loc'),
('loc_lang_ltr_disabled',2,'RTL','back','loc'),
('loc_lang_ltr_enabled',2,'LTR','back','loc'),
('prdimport_csv_use_structure',2,'When searching for products in the database by primary column, search only within current product category','back','imm'),
('prdimport_source_column',2,'Columns in your CSV files','back','imm'),
('prdimport_target_column',2,'Target fields in the database','back','imm'),
('prdimport_found_n_columns',2,'Found %d columns in your CSV file','back','imm'),
('imm_upload_link',2,'One-by-one upload','back','imm'),
('imm_upload_swf_link',2,'Bulk upload','back','imm'),
('imm_images_count_info',2,'Images: %d &mdash; %d of %d','back','imm'),
('imm_view_mode',2,'View mode','back','imm'),
('imm_view_mode_list',2,'list','back','imm'),
('imm_view_mode_thumbnails',2,'thumbnails','back','imm'),
('prdcat_product_n_duplicated',2,'%d duplicates successfully created','back','prd'),
('prdcart_products_duplicate_selected',2,'Duplicate','back','prd'),
('cpt_lbl_block_height',2,'Height of the product block element (li) in pixels (leave empty for auto value)','general','cfg'),
('welcome_to_storefront',2,'Welcome to online storefront ','general','gen'),
('powered_by',2,'Powered by WebAsyst Shop-Script <a href=\"http://www.shop-script.com/\" style=\"font-weight: normal\">shopping cart software</a>','hidden','gen'),
('powered_by_text',2,'Powered by <em>WebAsyst Shop-Script</em>','hidden','gen'),
('imm_del_confirmation',2,'Are you sure?','general','gen'),
('lbl_redirecting_to_idealbasic', 2, 'You are now being redirected to IdealBasic website...', 'front', 'gen')");
}

mysql_query("UPDATE `SC_local` SET `group` = 'general' WHERE `id` IN( 'msg_n_matches_found','prdset_product_code')");

mysql_query("DELETE FROM `SC_division_interface` WHERE `xInterface`= '-'");
//updates/update278.3.php:

/**
 * add print forms add google sitemap
 */
mysql_query("ALTER TABLE `SC_orders` ADD `shipping_module_id` INT( 10 ) AFTER `shipping_type`");
mysql_query("ALTER TABLE `SC_spmodules` ADD `module_type` INT( 11 ) AFTER `module_id`");
mysql_query("CREATE TABLE IF NOT EXISTS `SC_spmodules_settings` (
  `module_id` int(11) NOT NULL default '0',
  `field` varchar(255) NOT NULL default '',
  `value` text,
  PRIMARY KEY  (`module_id`,`field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

if($res = mysql_query("SELECT `xID` FROM `SC_divisions` WHERE `xName` = 'pgn_modules'")){
	if($id = mysql_fetch_row($res)){
		if($xID = $id[0]){
			$divisions = array('sitemap'=>0,'formmanagment'=>0);
			$res = mysql_query("SELECT `xUnicKey`, COUNT(*) FROM `SC_divisions` WHERE `xUnicKey` IN ('sitemap','formmanagment') AND `xParentID`= {$xID} GROUP BY `xUnicKey`");
			if($res){
				while($row = mysql_fetch_row($res)){
					$divisions[$row[0]] = $row[1];
				}
				if(($divisions['sitemap']==0)&&mysql_query("INSERT INTO `SC_divisions` (`xName`, `xKey`, `xUnicKey`, `xParentID`, `xEnabled`, `xPriority`, `xTemplate`, `xLinkDivisionUKey`) VALUES ('pgn_google_sitemap', '', 'sitemap', {$xID}, 1, 1, '', '')")){
					if($xID_sitemap = mysql_insert_id()){
						mysql_query("INSERT INTO `SC_division_interface` (`xDivisionID`, `xInterface`, `xPriority`, `xInheritable`) VALUES ({$xID_sitemap}, '48_google_sitemap', 0, 0)");
					}
				}
				if(($divisions['formmanagment']==0)&&mysql_query("INSERT INTO `SC_divisions` (`xName`, `xKey`, `xUnicKey`, `xParentID`, `xEnabled`, `xPriority`, `xTemplate`, `xLinkDivisionUKey`) VALUES ('pgn_printforms', '', 'formmanagment', {$xID}, 1, 0, '', '')")){
					if($xID_forms = mysql_insert_id()){
						mysql_query("INSERT INTO `SC_division_interface` (`xDivisionID`, `xInterface`, `xPriority`, `xInheritable`) VALUES ({$xID_forms}, '48_formmanagment', 0, 0)");
					}
				}

			}
		}else{
			print "division 'pgn_modules' not found 2\n";
		}
	}else{
		print "division 'pgn_modules' not found 1\n";
	}
}else{
	print "error ex query";
}

if($res = mysql_query("SELECT `xID` FROM `SC_divisions` WHERE `xUnicKey` = 'admin_orders_list'")){
	if($id = mysql_fetch_row($res)){
		if($xID = $id[0]){
			$divisions = array('admin_print_form'=>0);
			$res = mysql_query("SELECT `xUnicKey`, COUNT(*) FROM `SC_divisions` WHERE `xUnicKey` IN ('admin_print_form') AND `xParentID`= {$xID} GROUP BY `xUnicKey`");
			if($res){
				while($row = mysql_fetch_row($res)){
					$divisions[$row[0]] = $row[1];
				}
				if(($divisions['admin_print_form']==0)&&mysql_query("INSERT INTO `SC_divisions` (`xName`, `xKey`, `xUnicKey`, `xParentID`, `xEnabled`, `xPriority`, `xTemplate`, `xLinkDivisionUKey`) VALUES ('print_form', '', 'admin_print_form', {$xID}, 0, 0, '', '')")){
					if($xID_forms = mysql_insert_id()){
						mysql_query("INSERT INTO `SC_division_interface` (`xDivisionID`, `xInterface`, `xPriority`, `xInheritable`) VALUES ({$xID_forms}, '48_admin_print_form', 0, 0)");
					}
				}
			}
		}else{
			print "division 'pgn_modules' not found 2\n";
		}
	}else{
		print "division 'pgn_modules' not found 1\n";
	}
}else{
	print "error ex query";
}

$lang_strings = array(
'pgn_google_sitemap'=>array('group'=>'general','subgroup'=>'gen',
'value'=>array(1=>'Sitemaps',2=>'Sitemaps')),
'sitemap_full_description'=>array('group'=>'general','subgroup'=>'gen',
'value'=>array(1=>'<strong>Sitemaps</strong> — это XML-файл с информацией для поисковых систем (таких как Google, Yahoo, Ask.com, MSN, Яндекс) о страницах веб-сайта, которые подлежат индексации. Sitemaps может помочь поисковикам определить местонахождение страниц сайта, время их последнего обновления, частоту обновления и важность относительно других страниц сайта для того, чтобы поисковая машина смогла более разумно индексировать сайт. Подробнее о Sitemaps — в <a href="http://ru.wikipedia.org/wiki/Sitemaps" target="_blank">статье на Википедии</a> (в статье можно найти информацию о том, как добавить файл Sitemaps в различные поисковые системы).',2=>'<strong>Sitemaps</strong> file is an XML file that lists the URLs of your website. This allows search engines to crawl the site more intelligently. Learn more in the <a href="http://en.wikipedia.org/wiki/Sitemaps" target="_blank">Sitemaps article</a> at Wikipedia (this article also describes how to submit your Sitemaps file to various search engines).')),
'sitemap_url'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Адрес основного файла Sitemaps',2=>'Your storefront main Sitemaps file URL')),
'sitemap_update_title'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Обновить Sitemaps XML-файл',2=>'Update Sitemaps XML file')),
'sitemap_name'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Основные разделы файла Sitemaps',2=>'Sections of your Sitemaps files')),

'sitemap_base_url'=> array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Адрес главной страницы интернет-магазина',2=>'Storefront homepage URL')),

'sitemap_index_description'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Основная структура интернет-магазина',2=>'Core storefont structure')),
'sitemap_pagename'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Информационные страницы',2=>'Info pages')),
'btn_create'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Создать',2=>'Create')),
'sitemap_update_date'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Обновлен',2=>'Updated')),
);
$strings = array(1=>array(),2=>array());
$delete = array(1=>array(),2=>array());
foreach($lang_strings as $id=>$values){
	foreach($values['value'] as $lang_id=>$value){
		$delete[$lang_id][] = "'{$id}'";
		$value = str_replace('\'','\\\'',$value);
		$strings[$lang_id][] = " ('{$id}',{$lang_id},'{$value}','{$values['group']}','{$values['subgroup']}')";
	}
}

foreach($delete as $lang_id=>$constants){
	$sql = 'DELETE FROM `SC_local` WHERE `lang_id`='.$lang_id.' AND `id` IN ('.implode(',',$constants).')';
	if(!mysql_query($sql)){
		//print "failed: <font color=\"red\">".nl2br(htmlspecialchars($sql))."</font><br><br>";
	}
}
foreach($strings as $lang_id=>$sql_strings){
	$sql = 'INSERT IGNORE INTO `SC_local` (`id`,`lang_id`,`value`,`group`,`subgroup`) VALUES '. implode(",\n",$sql_strings);
	if(!mysql_query($sql)){
		//print "failed: <font color=\"red\">".nl2br(htmlspecialchars($sql))."</font><br><br>";
	}
}
//updates/update278.4.php:

if($res = mysql_query("SELECT `xID` FROM `SC_divisions` WHERE `xName` = 'pgn_mainpage'")){
	if($id = mysql_fetch_row($res)){
		if($xID = $id[0]){
			$divisions = array('print_form'=>0,'product_out_of_stock'=>0);
			$res = mysql_query("SELECT `xUnicKey`, COUNT(*) FROM `SC_divisions` WHERE `xUnicKey` IN ('print_form','product_out_of_stock') AND `xParentID`= {$xID} GROUP BY `xUnicKey`");
			if($res){
				while($row = mysql_fetch_row($res)){
					$divisions[$row[0]] = $row[1];
				}
				if(($divisions['print_form']==0)&&mysql_query("INSERT INTO `SC_divisions` (`xName`, `xKey`, `xUnicKey`, `xParentID`, `xEnabled`, `xPriority`, `xTemplate`, `xLinkDivisionUKey`) VALUES ('pgn_printforms', '', 'print_form', {$xID}, 0, 0, '', '')")){
					if($xID_sitemap = mysql_insert_id()){
						mysql_query("INSERT INTO `SC_division_interface` (`xDivisionID`, `xInterface`, `xPriority`, `xInheritable`) VALUES ({$xID_sitemap}, '25_print_form', 0, 0)");
					}
				}
				if(($divisions['product_out_of_stock']==0)&&mysql_query("INSERT INTO `SC_divisions` (`xName`, `xKey`, `xUnicKey`, `xParentID`, `xEnabled`, `xPriority`, `xTemplate`, `xLinkDivisionUKey`) VALUES ('prd_out_of_stock', '', 'product_out_of_stock', {$xID}, 0, 0, '', '')")){
					if($xID_sitemap = mysql_insert_id()){
						mysql_query("INSERT INTO `SC_division_interface` (`xDivisionID`, `xInterface`, `xPriority`, `xInheritable`) VALUES ({$xID_sitemap}, '25_htmlpage', 0, 0)");
					}
				}
			}
		}
	}
}

$lang_strings = array(
//Order Forms
'lsgr_printforms'=>array('group'=>'general','subgroup'=>'gen',
'value'=>array(1=>'Печатные формы',2=>'Order forms')),
'printforms_full_description'   =>array('group'=>'general','subgroup'=>'gen',
'value'=>array(1=>'Настройка сопроводительных документов к заказам (счета, квитанции и т.п.).',2=>'Order forms are static order accompanying forms that are available for viewing and printing directly from order information page.')),

'printforms_setup'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Настройки',2=>'Settings')),
'printforms_preview'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Образец',2=>'Preview')),
'pgn_printforms'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Печатные формы',2=>'Print forms')),
'print_form'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Печатная форма',2=>'Order form')),
'print_forms'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Печатные формы',2=>'Order forms')),
'print_form_not_found'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Печатная форма не установлена',2=>'Order form is not installed')),

//missed strings

'btn_open_invoice'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Версия для печати',2=>'Print version')),
'prdcat_products_duplicate_selected'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Создать дубликат(ы)',2=>'Duplicate')),

'imm_modify_time'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Изменен',2=>'Modified')),
'demoprd_name'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Демо-продукт',2=>'Demo product')),
'cpt_lbl_authorization'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Авторизация',2=>'Authorization')),
'cpt_lbl_category_info'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Информация о категории',2=>'Category info')),
'ord_bill_to'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Плательщик',2=>'Bill to')),
'ord_date_paid'=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'Оплачено',2=>'Date Paid')),
'pmnt_empty_name'=>array('group'=>'back','subgroup'=>'ord','value'=>array(1=>'Введите название способа оплаты',2=>'Enter payment type name')),
'prd_multiply_label'=>array('group'=>'back','subgroup'=>'prd','value'=>array(1=>'Умножить все цены на',2=>'Multiply all prices by')),
'prd_price_multiply'=>array('group'=>'back','subgroup'=>'prd','value'=>array(1=>'Умножить',2=>'Multiply')),

//''=>array('group'=>'general','subgroup'=>'gen','value'=>array(1=>'',2=>'')),
);

$strings = array(1=>array(),2=>array());
$delete = array(1=>array(),2=>array());
foreach($lang_strings as $id=>$values){
	if(!$id) continue;
	foreach($values['value'] as $lang_id=>$value){
		if(!$value) continue;
		$delete[$lang_id][] = "'{$id}'";
		$value = str_replace('\'','\\\'',$value);
		$strings[$lang_id][] = " ('{$id}',{$lang_id},'{$value}','{$values['group']}','{$values['subgroup']}')";
	}
}

foreach($delete as $lang_id=>$constants){
	$sql = 'DELETE FROM `SC_local` WHERE `lang_id`='.$lang_id.' AND `id` IN ('.implode(',',$constants).')';
	if(!mysql_query($sql)){
		//print "failed: <font color=\"red\">".htmlspecialchars($sql,ENT_QUOTES,'utf-8')."</font><br><br>";
	}

}
foreach($strings as $lang_id=>$sql_strings){
	$sql = 'INSERT IGNORE INTO `SC_local` (`id`,`lang_id`,`value`,`group`,`subgroup`) VALUES '. implode(",\n",$sql_strings);
	if(!mysql_query($sql)){
		//print "failed: <font color=\"red\">".nl2br(htmlspecialchars($sql,ENT_QUOTES,'utf-8'))."</font><br><br>";
	}
}


if(class_exists('Language')){//clean cache for SC
	$language = new Language();
	if(method_exists($language,'_dropCache')){
		$language->_dropCache();
	}
}
?>