<?php
mysql_query("UPDATE `SC_local` SET `value`='Максимальная из рассчитанных скидок (кроме скидки по купону)' WHERE `lang_id`=1 AND `id`='cfg_calc_dsc_max'");
mysql_query("UPDATE `SC_local` SET `value`='Maximum of all applicable discounts (excluding discount by coupon)' WHERE `lang_id`=2 AND `id`='cfg_calc_dsc_max'");
if(class_exists('Language')){
	if(in_array('_dropCache',get_class_methods('Language'))){
		$language = new Language();
		$language->_dropCache();
	}
}

//fix bug with change default aux page slug
mysql_query("UPDATE `SC_divisions` SET `xName` = 'pgn_ap_1' WHERE `xName`='pgn_about_shoppingcart'");
mysql_query("UPDATE `SC_divisions` SET `xName` = 'pgn_ap_2' WHERE `xName`='pgn_shipping_payment'");

?>