<?php
$smarty = &$Register->get(VAR_SMARTY);
/*@var $smarty Smarty*/
if(!defined('GOOGLE_ANALYTICS_ENABLE') || !GOOGLE_ANALYTICS_ENABLE){
	$smarty->assign('GOOGLE_ANALYTICS_CODE','');
	return ;
}

$Register = &Register::getInstance();
/*@var $Register Register*/
$GetVars = &$Register->get(VAR_GET);

$is_last_checkout_step = false;
if(isset($GetVars['step']) && $GetVars['step']=='success'){

	$divisionCheckout = &DivisionModule::getDivisionByUnicKey('checkout');
	$currentDivision = &$Register->get(VAR_CURRENTDIVISION);
	/*@var $currentDivision Division*/
	$is_last_checkout_step = $divisionCheckout->getID() == $currentDivision->getID() && isset($GetVars['orderID']);
}
/**
 * step=success&orderID=
 */

$GOOGLE_ANALYTICS_ECOMMERCE_FORM = '';

if($is_last_checkout_step){

	$orderID = $GetVars['orderID'];
	$Order = ordGetOrder($orderID);
	$OrderContent = ordGetOrderContent($orderID);
	//	$smarty->assign('GOOGLE_ANALYTICS_SET_TRANS',' onLoad="javascript:__utmSetTrans()"');

	$GOOGLE_ANALYTICS_ECOMMERCE_FORM =	'pageTracker._addTrans('.
	/*Order ID		*/ '"'.str_replace('"','\"',CONF_ORDERID_PREFIX.$Order['orderID']).'",'.
	/*Affiliation	*/ '"'.str_replace('"','\"',CONF_SHOP_NAME).'",'.
	/*Total			*/ '"'.RoundFloatValueStr(virtualModule::_convertCurrency($Order['order_amount'],0,GOOGLE_ANALYTICS_USD_CURRENCY)).'",'.
	/*Tax			*/ '"'.RoundFloatValueStr(virtualModule::_convertCurrency($Tax, 0, GOOGLE_ANALYTICS_USD_CURRENCY)).'",'.
	/*Shipping		*/ '"'.RoundFloatValueStr(virtualModule::_convertCurrency($Order['shipping_cost'],0,GOOGLE_ANALYTICS_USD_CURRENCY)).'",'.
	/*City			*/ '"'.str_replace('"','\"',$Order['shipping_city']).'",'.
	/*State			*/ '"'.str_replace('"','\"',$Order['shipping_state']).'",'.
	/*Country		*/ '"'.str_replace('"','\"',$Order['shipping_country']).'"'.
 ');'."\n";

	$TC = count($OrderContent);
	$Tax = 0;
	for ($j=0;$j<$TC;$j++){
			
		$ProductInfo = GetProduct(GetProductIdByItemId($OrderContent[$j]['itemID']));
		$CategoryInfo = catGetCategoryById($ProductInfo['categoryID']);
		$Tax += $OrderContent[$j]['Price']*$OrderContent[$j]['tax']/100;

		$GOOGLE_ANALYTICS_ECOMMERCE_FORM .=	'pageTracker._addItem('.
		// Order ID
		'"'.CONF_ORDERID_PREFIX.$Order['orderID'].'",'.
		// SKU
		'"'.str_replace('"','\"',$ProductInfo['product_code']).'",'.
		// Product Name
		'"'.str_replace('"','\"',$ProductInfo['name']).'",'.
		// Category
		'"'.str_replace('"','\"',$CategoryInfo['name']).'",'.
		// Price
		'"'.RoundFloatValueStr(virtualModule::_convertCurrency($OrderContent[$j]['Price'],0,GOOGLE_ANALYTICS_USD_CURRENCY)).'",'.
		// Quantity
		'"'.$OrderContent[$j]['Quantity'].
		'");'."\n";
	}

	$GOOGLE_ANALYTICS_ECOMMERCE_FORM .='pageTracker._trackTrans();'."\n";
	//</script>';
 //$smarty->assign('GOOGLE_ANALYTICS_ECOMMERCE_FORM',$GOOGLE_ANALYTICS_ECOMMERCE_FORM);


}
//pageTracker._addOrganic("name_of_searchengine","qvar");

$java_src = (URL::isHttps()? 'https://ssl.' : 'http://www.').'google-analytics.com/ga.js';
$java_include ='<script type="text/javascript" src = "'.$java_src.'"></script>';
$ga_account = 'UA-'.str_replace('UA-','',GOOGLE_ANALYTICS_ACCOUNT);

//LanguagesManager::getLanguages()

$smarty->assign('GOOGLE_ANALYTICS_CODE',
'
<script type="text/javascript" src = "'.$java_src.'"></script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("'.$ga_account.'");
'.

(defined('GOOGLE_ANALYTICS_CUSTOM_SE')?constant('GOOGLE_ANALYTICS_CUSTOM_SE'):'
// Google EMEA Image domains
pageTracker._addOrganic("images.google.co.uk","q");
pageTracker._addOrganic("images.google.es","q");
pageTracker._addOrganic("images.google.pt","q");
pageTracker._addOrganic("images.google.it","q");
pageTracker._addOrganic("images.google.fr","q");
pageTracker._addOrganic("images.google.nl","q");
pageTracker._addOrganic("images.google.be","q");
pageTracker._addOrganic("images.google.de","q");
pageTracker._addOrganic("images.google.no","q");
pageTracker._addOrganic("images.google.se","q");
pageTracker._addOrganic("images.google.dk","q");
pageTracker._addOrganic("images.google.fi","q");
pageTracker._addOrganic("images.google.ch","q");
pageTracker._addOrganic("images.google.at","q");
pageTracker._addOrganic("images.google.ie","q");
pageTracker._addOrganic("images.google.ru","q");
pageTracker._addOrganic("images.google.pl","q");

// Other Google Image search
pageTracker._addOrganic("images.google.com","q");
pageTracker._addOrganic("images.google.ca","q");
pageTracker._addOrganic("images.google.com.au","q");
pageTracker._addOrganic("images.google","q");

// Blogsearch
pageTracker._addOrganic("blogsearch.google","q");

// Google EMEA Domains
pageTracker._addOrganic("google.co.uk","q");
pageTracker._addOrganic("google.es","q");
pageTracker._addOrganic("google.pt","q");
pageTracker._addOrganic("google.it","q");
pageTracker._addOrganic("google.fr","q");
pageTracker._addOrganic("google.nl","q");
pageTracker._addOrganic("google.be","q");
pageTracker._addOrganic("google.de","q");
pageTracker._addOrganic("google.no","q");
pageTracker._addOrganic("google.se","q");
pageTracker._addOrganic("google.dk","q");
pageTracker._addOrganic("google.fi","q");
pageTracker._addOrganic("google.ch","q");
pageTracker._addOrganic("google.at","q");
pageTracker._addOrganic("google.ie","q");
pageTracker._addOrganic("google.ru","q");
pageTracker._addOrganic("google.pl","q");

// Yahoo EMEA Domains
pageTracker._addOrganic("uk.yahoo.com","p");
pageTracker._addOrganic("es.yahoo.com","p");
pageTracker._addOrganic("pt.yahoo.com","p");
pageTracker._addOrganic("it.yahoo.com","p");
pageTracker._addOrganic("fr.yahoo.com","p");
pageTracker._addOrganic("nl.yahoo.com","p");
pageTracker._addOrganic("be.yahoo.com","p");
pageTracker._addOrganic("de.yahoo.com","p");
pageTracker._addOrganic("no.yahoo.com","p");
pageTracker._addOrganic("se.yahoo.com","p");
pageTracker._addOrganic("dk.yahoo.com","p");
pageTracker._addOrganic("fi.yahoo.com","p");
pageTracker._addOrganic("ch.yahoo.com","p");
pageTracker._addOrganic("at.yahoo.com","p");
pageTracker._addOrganic("ie.yahoo.com","p");
pageTracker._addOrganic("ru.yahoo.com","p");
pageTracker._addOrganic("pl.yahoo.com","p");

// UK specific
pageTracker._addOrganic("hotbot.co.uk","query");
pageTracker._addOrganic("excite.co.uk","q");
pageTracker._addOrganic("bbc","q");
pageTracker._addOrganic("tiscali","query");
pageTracker._addOrganic("uk.ask.com","q");
pageTracker._addOrganic("blueyonder","q");
pageTracker._addOrganic("search.aol.co.uk","query");
pageTracker._addOrganic("ntlworld","q");
pageTracker._addOrganic("tesco.net","q");
pageTracker._addOrganic("orange.co.uk","q");
pageTracker._addOrganic("mywebsearch.com","searchfor");
pageTracker._addOrganic("uk.myway.com","searchfor");
pageTracker._addOrganic("searchy.co.uk","search_term");
pageTracker._addOrganic("msn.co.uk","q");
pageTracker._addOrganic("uk.altavista.com","q");
pageTracker._addOrganic("lycos.co.uk","query");

// NL specific
pageTracker._addOrganic("chello.nl","q1");
pageTracker._addOrganic("home.nl","q");
pageTracker._addOrganic("planet.nl","googleq=q");
pageTracker._addOrganic("search.ilse.nl","search_for");
pageTracker._addOrganic("search-dyn.tiscali.nl","key");
pageTracker._addOrganic("startgoogle.startpagina.nl","q");
pageTracker._addOrganic("vinden.nl","q");
pageTracker._addOrganic("vindex.nl","search_for");
pageTracker._addOrganic("zoeken.nl","query");
pageTracker._addOrganic("zoeken.track.nl","qr");
pageTracker._addOrganic("zoeknu.nl","Keywords");

// Extras
pageTracker._addOrganic("alltheweb","q");
pageTracker._addOrganic("ananzi","qt");
pageTracker._addOrganic("anzwers","search");
pageTracker._addOrganic("araby.com","q");
pageTracker._addOrganic("dogpile","q");
pageTracker._addOrganic("elmundo.es","q");
pageTracker._addOrganic("ezilon.com","q");
pageTracker._addOrganic("hotbot","query");
pageTracker._addOrganic("indiatimes.com","query");
pageTracker._addOrganic("iafrica.funnel.co.za","q");
pageTracker._addOrganic("mywebsearch.com","searchfor");
pageTracker._addOrganic("search.aol.com","encquery");
pageTracker._addOrganic("search.indiatimes.com","query");
pageTracker._addOrganic("searcheurope.com","query");
pageTracker._addOrganic("suche.web.de","su");
pageTracker._addOrganic("terra.es","query");
pageTracker._addOrganic("voila.fr","kw");

// Extras RU
pageTracker._addOrganic("mail.ru", "q");
pageTracker._addOrganic("rambler.ru", "words");
pageTracker._addOrganic("nigma.ru", "s");
pageTracker._addOrganic("blogs.yandex.ru", "text");
pageTracker._addOrganic("yandex.ru", "text");
pageTracker._addOrganic("webalta.ru", "q");
pageTracker._addOrganic("aport.ru", "r");
pageTracker._addOrganic("poisk.ru", "text");
pageTracker._addOrganic("km.ru", "sq");
pageTracker._addOrganic("liveinternet.ru", "ask");
pageTracker._addOrganic("gogo.ru", "q");
pageTracker._addOrganic("gde.ru", "keywords");
pageTracker._addOrganic("quintura.ru", "request");
pageTracker._addOrganic("price.ru", "pnam");
pageTracker._addOrganic("torg.mail.ru", "q");


// Extras BY
pageTracker._addOrganic("akavita.by", "z");
pageTracker._addOrganic("tut.by", "query");
pageTracker._addOrganic("all.by", "query");


// Extras UA
pageTracker._addOrganic("meta.ua", "q");
pageTracker._addOrganic("bigmir.net", "q");
pageTracker._addOrganic("i.ua", "q");
pageTracker._addOrganic("online.ua", "q");
pageTracker._addOrganic("a.ua", "s");
pageTracker._addOrganic("ukr.net", "search_query");
pageTracker._addOrganic("search.com.ua", "q");
pageTracker._addOrganic("search.ua", "query");')
.'
pageTracker._initData(); 
pageTracker._trackPageview();
'.
$GOOGLE_ANALYTICS_ECOMMERCE_FORM.
'
</script>
');
?>