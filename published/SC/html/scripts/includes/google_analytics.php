<?php
//look like old unused code

/* @var $smarty Smarty */
if(!defined('GOOGLE_ANALYTICS_ENABLE'))return ;
if(GOOGLE_ANALYTICS_ENABLE){
	
	$smarty->assign('GOOGLE_ANALYTICS_CODE', 
'
	<script src="'.(!(isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS'])!='off'))?'http://www':'https://ssl').'.google-analytics.com/urchin.js" type="text/javascript">
	</script>
	<script type="text/javascript">
	  _uacct="UA-'.GOOGLE_ANALYTICS_ACCOUNT.'";
	  urchinTracker();
	</script>
');
}
if(GOOGLE_ANALYTICS_ENABLE &&(isset($_GET['order4_confirmation']) || isset($_GET['order4_confirmation_quick']))&&isset($_GET['order_success'])&&isset($_GET['orderID'])){

	$Order = ordGetOrder($_GET['orderID']);
	$OrderContent = ordGetOrderContent($_GET['orderID']);
	$smarty->assign('GOOGLE_ANALYTICS_SET_TRANS',' onLoad="javascript:__utmSetTrans()"');
	$GOOGLE_ANALYTICS_ECOMMERCE_FORM = '';
	$TC = count($OrderContent);
	$Tax = 0;
	for ($j=0;$j<$TC;$j++){
		
		$ProductInfo = GetProduct(GetProductIdByItemId($OrderContent[$j]['itemID']));
		$CategoryInfo = catGetCategoryById($ProductInfo['categoryID']);
		$Tax += $OrderContent[$j]['Price']*$OrderContent[$j]['tax']/100;
		$GOOGLE_ANALYTICS_ECOMMERCE_FORM .= 
'UTM:I|'.$Order['orderID'].'|'.$ProductInfo['product_code'].'|'.$ProductInfo['name'].'|'.$CategoryInfo['name'].'|'.RoundFloatValueStr(virtualModule::_convertCurrency($OrderContent[$j]['Price'],0,GOOGLE_ANALYTICS_USD_CURRENCY)).'|'.$OrderContent[$j]['Quantity'].'
';
	}
$GOOGLE_ANALYTICS_ECOMMERCE_FORM = 
'<form style="display:none;" name="utmform">
<textarea id="utmtrans">
UTM:T|'.$Order['orderID'].'|'.CONF_SHOP_NAME.'|'.RoundFloatValueStr(virtualModule::_convertCurrency($Order['order_amount'],0,GOOGLE_ANALYTICS_USD_CURRENCY)).'|'.RoundFloatValueStr(virtualModule::_convertCurrency($Tax, 0, GOOGLE_ANALYTICS_USD_CURRENCY)).'| '.RoundFloatValueStr(virtualModule::_convertCurrency($Order['shipping_cost'],0,GOOGLE_ANALYTICS_USD_CURRENCY)).'|'.$Order['shipping_city'].'|'.$Order['shipping_state'].'|'.$Order['shipping_country'].' 
'.$GOOGLE_ANALYTICS_ECOMMERCE_FORM.
'</textarea>
</form>
	';
	$smarty->assign('GOOGLE_ANALYTICS_ECOMMERCE_FORM',$GOOGLE_ANALYTICS_ECOMMERCE_FORM);
}
?>