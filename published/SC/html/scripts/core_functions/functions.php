<?php
if(!isset($_SERVER['REQUEST_URI'])){
	$req = $_SERVER['PHP_SELF'];
	if ( isset($_SERVER['QUERY_STRING']) && (strlen($_SERVER['QUERY_STRING']) > 0))
	$req .= '?'.$_SERVER['QUERY_STRING'];
	$_SERVER['REQUEST_URI'] = $GLOBALS['REQUEST_URI'] = $req;
}

//frequently used functions
function MagicQuotesRuntimeSetting()
{
	@ini_set("magic_quotes_runtime",0);
	if(function_exists('set_magic_quotes_runtime')&&!preg_match('/^5\.3/',PHP_VERSION)){
		@set_magic_quotes_runtime(false);
	}
}

function correct_URL( $url, $mode = "http" ){
	
	$URLprefix = trim( $url );
	$URLprefix = str_replace(array('http://', 'https://', 'index.php'),  '', $URLprefix);
	if ($URLprefix[ strlen($URLprefix)-1 ] == '/')
		$URLprefix = substr( $URLprefix, 0, strlen($URLprefix)-1 );

	return ($mode."://".$URLprefix."/");
}

/**
 * Sets access rights to files which uploaded with help move_uploaded_file
 * @param string $file_name 
 */ 
function SetRightsToUploadedFile( $file_name ){
	@chmod( $file_name, 0666);
}

function Redirect($url){
	$softwareInfo = getServerInfo();

	$winIIS = strstr(php_uname(), 'Windows') && ( $softwareInfo == 'IIS' );

	if ( $winIIS ){
		$str_redirect = "Refresh: 0;url=%s";
	}else{
		$str_redirect = "Location: %s";
	}
	header(sprintf($str_redirect, escapeCRLF($url)));
	exit(1);
	//header("location: ".escapeCRLF($url), true, 302);
	//exit(1);
}

function getServerInfo()
{
	$ssoft=strtolower($_SERVER["SERVER_SOFTWARE"]);
	if (strstr($ssoft,"apache")){
		$sos="Apache";
	}elseif (strstr($ssoft,"iis")){
		$sos="IIS";
	}else{
		$sos = "Apache";
	}
	return $sos;
}

function RedirectSQ($_params = '', $_url=''){
	Redirect(renderURL($_params,$_url));
}

/**
 * round float value to 0.01 precision
 * 
 * @param float $float_value
 * @return float
 */
function RoundFloatValue( $float_value ){
	return round (100*$float_value)/100;
}

// Purpose	round float value to 0.01 precision
// Inputs   $float_value - value to float
// Remarks	this function returns string value. 
//				Two digits locate after decimal point always.
// Returns	rounded value
function RoundFloatValueStr( $float_value )
{
	$str = RoundFloatValue( $float_value );
	$index = strpos($str,".");
	if ( $index === false )
		return $str.".00";
	else
	{
		if ( strlen($str)-1-$index == 1 )
			return $str."0";
		else
			return $str;
	}
}

// Purpose	gets all files in specified directory
// Inputs   $dir - full path directory
function GetFilesInDirectory( $dir, $extension = '',$name_template = null)
{
	if(!file_exists($dir))return array();

	$dh  = opendir($dir);
	$files = array();
	$pattern = '|'.($name_template?$name_template:'').'\.'.$extension.'$|msi';
	while (false !== ($filename = readdir($dh))) 
	{
		if ( !is_dir($dir.'/'.$filename) && $filename != '.' && $filename != '..' ){
			
			if(preg_match($pattern,$filename)){
				$files[] = $dir.'/'.$filename;
			}
		}
	}
	return $files;
}

/**
 * Show a number and selected currency sign
 *
 * @param float $price - is in universal currency
 * @param mixed $custom_currency - if $custom_currency != 0 show price this currency with ID = $custom_currency
 * @param boolean $priceInUC - notify about price is in UC format
 * @return string
 */
function show_price($price, $custom_currency = 0, $priceInUC = true){
	
	if($custom_currency){
		$currencyEntry = new Currency();
		$currencyEntry->loadByCID($custom_currency);
	}else{
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$currencyEntry = $Register->get('admin_mode')?Currency::getDefaultCurrencyInstance():Currency::getSelectedCurrencyInstance();
		/*@var $currencyEntry Currency*/
	}
	
	$price = $priceInUC?$currencyEntry->convertUnits($price):$price;

	return $currencyEntry->getView($price);
}

function ConvertPriceToUniversalUnit($priceWithOutUnit){
	
	$currencyEntry = Currency::getSelectedCurrencyInstance();
	return $currencyEntry->convertToUnits($priceWithOutUnit, true);
}

function show_priceWithOutUnit($price){
	
	$currencyEntry = Currency::getSelectedCurrencyInstance();
	
	return $currencyEntry->convertUnits($price, true);
}

function ShowNavigator($a, $offset, $q, $path, &$out)
{ 	
		//shows navigator [prev] 1 2 3 4 � [next]
		//$a - count of elements in the array, which is being navigated
		//$offset - current offset in array (showing elements [$offset ... $offset+$q])
		//$q - quantity of items per page
		//$path - link to the page (f.e: "index.php?categoryID=1&")

		if ($a > $q) //if all elements couldn't be placed on the page
		{

			//[prev]
			if ($offset>0) $out .= "<a class=no_underline href=\"".xHtmlSetQuery($path."&offset=".($offset-$q))."\">&lt;&lt; ".translate("str_previous")."</a> &nbsp;&nbsp;";

			//digital links
			$k = $offset / $q;

			//not more than 4 links to the left
			$min = $k - 5;
			if ($min < 0) { $min = 0; }
			else {
				if ($min >= 1)
				{ //link on the 1st page
					$out .= "<a class=no_underline href=\"".xHtmlSetQuery($path."&offset=0")."\">1</a> &nbsp;&nbsp;";
					if ($min != 1) { $out .= "... &nbsp;"; };
				}
			}

			for ($i = $min; $i<$k; $i++)
			{
				$m = $i*$q + $q;
				if ($m > $a) $m = $a;

				$out .= "<a class=no_underline href=\"".xHtmlSetQuery($path."&offset=".($i*$q))."\">".($i+1)."</a> &nbsp;&nbsp;";
			}

			//# of current page
			if (strcmp($offset, "show_all"))
			{
				$min = $offset+$q;
				if ($min > $a) $min = $a;
				$out .= "<font class=faq><b>".($k+1)."</b></font> &nbsp;&nbsp;";
			}
			else
			{
				$min = $q;
				if ($min > $a) $min = $a;
				$out .= "<a class=no_underline href=\"".xHtmlSetQuery($path."&offset=0")."\">1</a> &nbsp;&nbsp;";
			}

			//not more than 5 links to the right
			$min = $k + 6;
			if ($min > ceil($a/$q)) { $min = ceil($a/$q); };
			for ($i = $k+1; $i<$min; $i++)
			{
				$m = $i*$q+$q;
				if ($m > $a) $m = $a;

				$out .= "<a class=no_underline href=\"".xHtmlSetQuery($path."&offset=".($i*$q))."\">".($i+1)."</a> &nbsp;&nbsp;";
			}

			if ($min*$q < $a) { //the last link
				if ($min*$q < $a-$q) $out .= " ... &nbsp;&nbsp;";
				if (!($a%$q == 0))
				 $out .= "<a class=no_underline href=\"".xHtmlSetQuery($path."&offset=".($a-$a%$q))."\">".(floor($a/$q)+1)."</a> &nbsp;&nbsp;";
				else //$a is divided by $q
				 $out .= "<a class=no_underline href=\"".xHtmlSetQuery($path."&offset=".($a-$q))."\">".(floor($a/$q))."</a> &nbsp;&nbsp;";
			}

			//[next]
			if (strcmp($offset, "show_all"))
				if ($offset<$a-$q) $out .= "<a class=no_underline href=\"".xHtmlSetQuery($path."&offset=".($offset+$q))."\">".translate("str_next")." &gt;&gt;</a> ";

			//[show all]
			if(SHOWALL_ALLOWED_RECORDS_NUM >= $a || (!SystemSettings::is_hosted()&&SystemSettings::is_backend())){
				if (strcmp($offset, "show_all"))
					$out .= " |&nbsp; <a class=no_underline href=\"".xHtmlSetQuery($path."&offset=&show_all=yes")."\">".translate("str_showall")."</a>";
				else
					$out .= " |&nbsp; <B>".translate("str_showall")."</B>";
			}
		}
}

function GetNavigatorHtml(	$url, $countRowOnPage = CONF_PRODUCTS_PER_PAGE, 
				$callBackFunction, $callBackParam, &$tableContent,
				&$offset, &$count )
{
	if ( isset($_GET["offset"]) )
		$offset = (int)$_GET["offset"];
	else
		$offset = 0;
	$offset -= $offset % $countRowOnPage;//CONF_PRODUCTS_PER_PAGE;
	if ( $offset < 0 ) $offset = 0;
	$count = 0;

	$url = preg_replace('@^[^\?\&]+@', '', $url);
	
	$Register = &Register::getInstance();
	if ( !$Register->is_set("show_all") || !$Register->get('show_all')) //show 'CONF_PRODUCTS_PER_PAGE' products on this page
	{
		$tableContent = $callBackFunction( $callBackParam, $count, 
					array( 
						"offset" => $offset, 
						"CountRowOnPage" => $countRowOnPage 
					     ) 
				);
	}else{ //show all products
	
		$tableContent = $callBackFunction( $callBackParam, $count, null );
		$offset = "show_all";
	}

	ShowNavigator( $count, $offset, $countRowOnPage, 
		$url, $out);
	return $out;
}

function moveCartFromSession2DB() //all products in shopping cart, which are in session vars, move to the database
{
	if (  isset($_SESSION["gids"]) && isset($_SESSION["log"])  )
	{

		$customerID = regGetIdByLogin( $_SESSION["log"] );
		$q = db_query( "select itemID from ".SHOPPING_CARTS_TABLE." where customerID=".$customerID );
		$items = array();
		while ( $item = db_fetch_row($q) )
			$items[] = $item["itemID"];

		//$i=0;
		foreach( $_SESSION["gids"] as $key => $productID )
		{
			if ( $productID == 0 )
				continue;

			// search product in current user's shopping cart content
			$itemID = null;
			for( $j=0; $j<count($items); $j++ )
			{
				$q = db_query( "select count(*) from ".SHOPPING_CART_ITEMS_TABLE." where productID=".$productID." AND ".
								" itemID=".$items[$j] );
				$count = db_fetch_row($q);
				$count = $count[0];
				if ( $count != 0 )
				{
					// compare configuration
					$configurationFromSession = $_SESSION["configurations"][$key];
					$configurationFromDB = GetConfigurationByItemId( $items[$j] );
					if ( CompareConfiguration($configurationFromSession, $configurationFromDB) )
					{
						$itemID = $items[$j];
						break;
					}
						$itemID = $items[$j];

				}
			}


			if ( $itemID == null )
			{
				// create new item
				db_query( "insert into ".SHOPPING_CART_ITEMS_TABLE.
					" (productID) values('".$productID."')\n" );
				$itemID = db_insert_id();

				// set content item
				foreach( $_SESSION["configurations"][$key] as $var )
				{
					db_query("insert into ".
						SHOPPING_CART_ITEMS_CONTENT_TABLE." ( itemID, variantID ) ".
						" values( '".$itemID."', '".$var."' )\n" );
				}

				// insert item into cart
				db_query("insert ".SHOPPING_CARTS_TABLE.
					"(customerID, itemID, Quantity)".
					"values( '".$customerID."', '".$itemID."', '".$_SESSION["counts"][$key].
						"' )\n" );
			}
			else
			{
				db_query( "update ".SHOPPING_CARTS_TABLE.
					" set Quantity=Quantity + ".$_SESSION["counts"][$key]." ".
					" where customerID=".$customerID." and itemID=".$itemID."\n" );
			}

		}

 		unset($_SESSION["gids"]);
		unset($_SESSION["counts"]);
		unset($_SESSION["configurations"]);
		if(!preg_match('/^5\.3/',PHP_VERSION)){
			session_unregister("gids"); //calling session_unregister() is required since unset() may not work on some systems
			session_unregister("counts");
			session_unregister("configurations");
		}
	}
}

/**
 * Reprganize array from array('hello_<some>'=>123) to array(<some>=>array('hello'=>123))
 *
 * @param array $a
 * @param array|string $varnames
 * @return array
 */
function scanArrayKeysForID($a, $varnames){
	
	if(!is_array($varnames)){
		$varnames = array($varnames);
	}
	$data = array();
	foreach($varnames as $name){
		foreach($a as $key => $value){
			
			if (preg_match("/^({$name})_/", $key, $kp)){
							
				$key = preg_replace("/^{$name}_/","",$key);
				$data[$key][$kp[1]] = $value;
			}
		}
	}
	return $data;
}

define('URLRENDMODE_MODIFY', 1);
define('URLRENDMODE_RESET', 2);

function renderGetVars($URL){
	
	$GetVars = array();
	$parsedURL = parse_url($URL);
	
	if(isset($parsedURL['query'])&&$parsedURL['query']){
		
		$r_TokenStrs = explode('&', $parsedURL['query']);
		
		foreach ($r_TokenStrs as $TokenStr){
			
			$r_Token = explode('=', $TokenStr,2);
			if(isset($r_Token[1])){
				$GetVars[$r_Token[0]] = $r_Token[1];
			}
		}
	}
	return $GetVars;
}

function renderURL($_vars = '', $_request = '', $_store = false, $furl = null,$external = false){
	
	$RenderedURL = '';
	
	
	if(!$_request){
		
		$_request = $_SERVER['REQUEST_URI'];
		$GetVars = $_GET;
		if(SystemSettings::is_hosted()){
			$_request = preg_replace('@^/webasyst/@msi','/',$_request);
		}
	}else{
		
		$GetVars = renderGetVars($_request);
	}

	if(!MOD_REWRITE_SUPPORT)
	{
    	if(strpos($_request, 'index.php') === false &&!$external)
    	{
    	    if(strpos($_request, '?')) $_request = str_replace('?','index.php?',$_request);
    	    else $_request .= 'index.php';
    	};
    	if(preg_match("/^\?categoryID=(\d+)\&category_slug=[a-z0-9_]+$/i", $_vars, $matches))
    	{
    	    $_vars = '?categoryID='.$matches[1];
    	};
    	
    	if(preg_match("/^\?ukey=product\&productID=(\d+)\&product_slug=[a-z0-9_\-]+$/i", $_vars, $matches))
    	{
    	    $_vars = '?productID='.$matches[1];
    	};
	};
	
	$anchor = preg_match('@(#[^#]*)$@', $_request, $sp)?$sp[1]:'';
	$anchor = preg_match('@(#[^#]*)$@', $_vars, $sp)?$sp[1]:$anchor;
	
	/**
	 * Set render mode
	 */
	if(strpos($_vars,'?')!==false){
		
		$Mode = URLRENDMODE_RESET;
		$_vars = substr($_vars, 1, strlen($_vars)-1).'&lang_iso2=';
	}else{
		
		$Mode = URLRENDMODE_MODIFY;
	}
	
	/**
	 * trim first ampersand
	 */
	if(strpos($_vars,'&')===0)$_vars = substr($_vars, 1, strlen($_vars)-1);
		
	/**
	 * Render new get-tokens
	 */
	$ReceivedTokens = array();
	$r_TokenStrs = explode('&', $_vars);
	$widgets_token = false;
	foreach ($r_TokenStrs as $TokenStr){
		
		$r_Token = explode('=', $TokenStr,2);
		if($r_Token[0]=='widgets')$widgets_token = true;
		if($r_Token[0]=='store_mode')$mode_token = true;
		if(isset($r_Token[1])&& strlen($r_Token[1])){
			
			$ReceivedTokens[$r_Token[0]] = $r_Token[1];
			if($Mode == URLRENDMODE_MODIFY){
				
				$GetVars[$r_Token[0]] = $r_Token[1];
			}
		}else {
			
			switch ($Mode){
				case URLRENDMODE_MODIFY:
					
					if(isset($GetVars[$r_Token[0]]))
						unset($GetVars[$r_Token[0]]);
					break;
				case URLRENDMODE_RESET:
					if(isset($GetVars[$r_Token[0]]) && $r_Token[0]!='product_slug' && $r_Token[0]!='category_slug')
						$ReceivedTokens[$r_Token[0]] = $GetVars[$r_Token[0]];
					break;
			}
		}
	}
	/**
	 * Render URL
	 */
	$newGetVars = array();
	switch ($Mode){
		case URLRENDMODE_MODIFY:
			$newGetVars = &$GetVars;
			break;
		case URLRENDMODE_RESET:
			$newGetVars = &$ReceivedTokens;
			break;
	}
	
	if(!$mode_token){
		$Register = &Register::getInstance();
		if($store_mode = $Register->get('store_mode')){
			$newGetVars['store_mode'] = $store_mode;
		}
	}

	if(!MOD_REWRITE_SUPPORT){
		foreach(array('product_slug','category_slug') as $param){
			if(isset($newGetVars[$param])){
				unset($newGetVars[$param]);
			}
		}
	}
	if(!$widgets_token && count($newGetVars)){
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		if($Register->get('widgets'))$newGetVars['widgets'] = 1;
	}
	
	if($_store){
		
		$_GET = $newGetVars;
	}
	if(class_exists('fURL')&& $furl !== false)
		fURL::convertGetToPath($_request, $newGetVars);
		
	foreach ($newGetVars as $TokenName=>$TokenValue){
		
		$newGetVars[$TokenName] = $TokenName.'='.$TokenValue;
	}
	$RenderedURL = implode('&', $newGetVars);
	if(strpos($_request, '?')!==false){
		
		$RenderedURL = preg_replace('/\?.*$/','?'.$RenderedURL,$_request);
	}else {
		
		$RenderedURL = $_request.'?'.$RenderedURL;
	}
	
	$RenderedURL = preg_replace('@[\?\&]{1,2}$@', '', $RenderedURL);
	$RenderedURL = preg_replace('@^/{2,}@','/',$RenderedURL);
	
	if(strlen($anchor)>1)$RenderedURL = preg_replace('@#[^#]*$@', '', $RenderedURL).$anchor; 
	/**
	 * Strore URL
	 */
	if($_store){
		
//		$_SERVER['REQUEST_URI'] = $RenderedURL;
	}

	return $RenderedURL;
}

function set_query($_vars='', $_request = '', $_store = false, $external = false){
	
	return renderURL($_vars, $_request, $_store,null,$external);
}

function xHtmlSetQuery($_vars='', $_request = '', $_store = false){
	
	return xHtmlSpecialChars(renderURL($_vars, $_request, $_store));
}

function getListerRange($_pagenumber, $_totalpages, $_lister_num = 20){

	if($_pagenumber<=0) return array('start'=>1, 'end'=>1);
	$lister_start=$_pagenumber-floor($_lister_num/2);
	$lister_start=($lister_start+$_lister_num<=$_totalpages?$lister_start:$_totalpages-$_lister_num+1);
	$lister_start=($lister_start>0?$lister_start:1);
	$lister_end=$lister_start+$_lister_num-1;
	$lister_end=($lister_end<=$_totalpages?$lister_end:$_totalpages);
	return array('start'=>$lister_start, 'end'=>$lister_end);
}

function getLister($_pagenumber, $_totalpages, $_lister_num = 20){

	if($_pagenumber<=0)return array(
		'CurrentPage'=>1,
		'LastPage' => 1,
		'Range' =>array(1),
		);
	$Lister = array(
		'CurrentPage'=>$_pagenumber,
		'LastPage' => $_totalpages,
		'Range' =>array(),
		);
	$lister_start=$_pagenumber-floor($_lister_num/2);
	$lister_start=($lister_start+$_lister_num<=$_totalpages?$lister_start:$_totalpages-$_lister_num+1);
	$lister_start=($lister_start>0?$lister_start:1);
	$lister_end=$lister_start+$_lister_num-1;
	$lister_end=($lister_end<=$_totalpages?$lister_end:$_totalpages);
	for (;$lister_start<=$lister_end;$lister_start++)
		$Lister['Range'][] = $lister_start;
	return $Lister;
}

/**
*Strip slashes if magic_quotes_gpc is On
*
*@param mixed
* return mixed
*/
function xStripSlashesGPC($_data){

	if(!get_magic_quotes_gpc())return $_data;
	if(is_array($_data)){
		
		foreach ($_data as $_ind => $_val){
			
			$_data[$_ind] = xStripSlashesGPC($_val);
		}
		return $_data;
	}
	return stripslashes($_data);
}

/**
 * mail txt message from template
 * @param string email
 * @param string email subject
 * @param string template name
 */
function xMailTxt($_Email, $_Subject, $_TemplateName, $_AssignArray = array(), $html = false){
	
	if(!$_Email)return 0;
	$mailSmarty = new ViewSC();
	foreach ($_AssignArray as $_var=>$_val){
		
		$mailSmarty->assign($_var, $_val);
	}
	$_t = $mailSmarty->fetch('email/'.$_TemplateName);
	ss_mail($_Email, $_Subject, $_t, true);
}

/**
 * replace newline symbols to &lt;br /&gt;
 * @param mixed data for action
 * @param array which elements test
 * @return mixed
 */
function xNl2Br($_Data, $_Key = array()){
	

	if (!is_array($_Data)){
		
		return nl2br($_Data);
	}
	
	if (!is_array($_Key))$_Key = array($_Key);
	foreach ($_Data as $__Key=>$__Data){
		
		if (count($_Key)&&!is_array($__Data)){
			
			if (in_array($__Key, $_Key)){
				
				$_Data[$__Key] = xNl2Br($__Data, $_Key);
			}
		}else $_Data[$__Key] = xNl2Br($__Data, $_Key);
		
	}
	return $_Data;
}

function xStrReplace($_Search, $_Replace, $_Data, $_Key=array()){
	
	if (!is_array($_Data)){
		
		return str_replace($_Search, $_Replace, $_Data);
	}
	
	if (!is_array($_Key))$_Key = array($_Key);
	foreach ($_Data as $__Key=>$__Data){
		
		if (count($_Key)&&!is_array($__Data)){
			
			if (in_array($__Key, $_Key)){
				
				$_Data[$__Key] = xStrReplace($_Search, $_Replace, $__Data, $_Key);
			}
		}else $_Data[$__Key] = xStrReplace($_Search, $_Replace, $__Data, $_Key);
		
	}
	return $_Data;
}

function xHtmlSpecialChars($_Data, $_Params = array(), $_Key = array()){
	
	
	if (!is_array($_Data)){
		
		return htmlspecialchars($_Data, ENT_QUOTES);
	}
	
	if (!is_array($_Key))$_Key = array($_Key);
	foreach ($_Data as $__Key=>$__Data){
		
		if (count($_Key)&&!is_array($__Data)){
			
			if (in_array($__Key, $_Key)){
				
				$_Data[$__Key] = xHtmlSpecialChars( $__Data, $_Params, $_Key);
			}
		}else $_Data[$__Key] = xHtmlSpecialChars( $__Data, $_Params, $_Key);
		
	}
	return $_Data;	
}

function xEscapeSQLstring ( $_Data, $_Params = array(), $_Key = array() ){
	
	if (!is_array($_Data)){
		
		return mysql_real_escape_string($_Data);
	}
	
	if (!is_array($_Key))$_Key = array($_Key);
	foreach ($_Data as $__Key=>$__Data){
		
		if (count($_Key)&&!is_array($__Data)){
			
			if (in_array($__Key, $_Key)){
				
				$_Data[$__Key] = xEscapeSQLstring( $__Data, $_Params, $_Key);
			}
		}else $_Data[$__Key] = xEscapeSQLstring( $__Data, $_Params, $_Key);
		
	}
	return $_Data;	
}

function xSaveData($_ID, $_Data, $_TimeControl = 0){
	if(!preg_match('/^5\.3/',PHP_VERSION)){
		if(!session_is_registered('_xSAVE_DATA')){
		
		session_register('_xSAVE_DATA');
		$_SESSION['_xSAVE_DATA'] = array();
		}
	}else if(!isset($_SESSION['_xSAVE_DATA'])){
		$_SESSION['_xSAVE_DATA'] = array();
	}
	
	if(intval($_TimeControl)){
	
		$_SESSION['_xSAVE_DATA'][$_ID] = array(
			$_ID.'_DATA' => $_Data,
			$_ID.'_TIME_CTRL' => array(
				'timetag' => time(),
				'timelimit' => $_TimeControl,
				),
			);
	}else{
		$_SESSION['_xSAVE_DATA'][$_ID] = $_Data;
	}
}

function xPopData($_ID){
	
	if(!isset($_SESSION['_xSAVE_DATA'][$_ID])){
		return null;
	}
		
	if(is_array($_SESSION['_xSAVE_DATA'][$_ID])){
	
		if(isset($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL'])){
		
			if( ($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timetag']+$_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timelimit']) < time() ){
				return null;
			}else{
			
				$Return = $_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_DATA'];
				unset($_SESSION['_xSAVE_DATA'][$_ID]);
				return $Return;
			}
		}
	}
	
	$Return = $_SESSION['_xSAVE_DATA'][$_ID];
	unset($_SESSION['_xSAVE_DATA'][$_ID]);
	return $Return;
}

function xDataExists($_ID){
	
	if(!isset($_SESSION['_xSAVE_DATA'][$_ID]))return 0;
	
	if(is_array($_SESSION['_xSAVE_DATA'][$_ID])){
	
		if(isset($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL'])){
		
			if( ($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timetag']+$_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timelimit']) >= time() ){
				return 1;
			}else{
				return 0;
			}
		}else{
			return 1;
		}
	}else{
		return 1;
	}
}

function xGetData($_ID){
	
	if(!isset($_SESSION['_xSAVE_DATA'][$_ID])){
		return null;
	}
		
	if(is_array($_SESSION['_xSAVE_DATA'][$_ID])){
	
		if(isset($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL'])){
		
			if( ($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timetag']+$_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timelimit']) < time() ){
				return null;
			}else{
			
				$Return = $_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_DATA'];
				return $Return;
			}
		}
	}
	
	$Return = $_SESSION['_xSAVE_DATA'][$_ID];
	return $Return;
}

function xCall($func_name, $data, $params = null){
	
	if (!is_array($data))return call_user_func_array($func_name, array($data, $params));
	
	foreach ($data as $k=>$v)$data[$k] = xCall($func_name, $v, $params);
	return $data;	
}

function isWindows(){
	if(defined('IS_WINDOWS')){
		return constant('IS_WINDOWS');
	}	
	if(isset($_SERVER["WINDIR"]) || isset($_SERVER["windir"]))return true;
	if(isset($_SERVER['SERVER_SOFTWARE'])&&(strpos(strtolower($_SERVER['SERVER_SOFTWARE']),'microsoft')!==false)) return true;
	return false;
}

function generateRndCode($_RndLength, $_RndCodes = 'qwertyuiopasdfghjklzxcvbnm0123456789'){
	
	$l_name='';
	$top = strlen($_RndCodes)-1;
	srand((double) microtime()*1000000);
	for($j=0; $j<$_RndLength; $j++)$l_name .= $_RndCodes{rand(0,$top)};
	return $l_name;
}

function get_NOTempty_elements_count($arr) //required for excel import
//gets how many NOT NULL (not empty strings) elements are there in the $arr
{
	$n = 0;
	for ($i=0;$i<count($arr);$i++)
		if (trim($arr[$i]) != "") $n++;
	return $n;
} //get_NOTempty_elements_count

function mark_as_selected($a,$b) //required for excel import
//returns " selected" if $a == $b
{
	return !strcmp($a,$b) ? " selected" : "";

} //mark_as_selected

/**
 * Authorized access check
 *
 */
function checkLogin(){
	
	//authorized access check
	if (isset($_SESSION["log"])){ //look for user in the database

		$sql = '
			SELECT cust_password FROM ?#CUSTOMERS_TABLE WHERE Login=?
		';
		
		$row = db_phquery_fetch(DBRFETCH_ROW, $sql, $_SESSION["log"]); //found customer - check password
	
		if (!$row || !isset($_SESSION["pass"]) || strcmp($row[0], $_SESSION["pass"] )) //unauthorized access
		{
			unset($_SESSION["log"]);
			unset($_SESSION["pass"]);
			if(!preg_match('/^5\.3/',PHP_VERSION)){
				session_unregister("log"); //calling session_unregister() is required since unset() may not work on some systems
				session_unregister("pass");
			}
		}
	
	}
}

function isHTTPS(){
	
	return isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS'])!='off');
}

function escapeCRLF($str){
	
	return str_replace(array("\r\n",'%0d%0a', "\n",'%0a', "\r",'%0d'),'',$str);
}

/**
 * Cut string
 *
 * @param string $String - source
 * @param int $Length - target length
 * @param string $EndString
 */
function str_cut($String, $Length, $EndString = '...'){
	
	$origlength = strlen($String);
	if($origlength<=$Length)return $String;
	$String = substr($String,0,$Length);
	$lastspace_i = strrpos($String,' ');
	if($lastspace_i !== false)
		$String  = substr($String,0,$lastspace_i);
	return $String.($origlength>$Length?$EndString:'');
}

function getUniqueWDataID($Length = 4){
	
	$ID = '';
	do {
		
		$ID = rand_name($Length);
	}while(issetWData($ID));
	return $ID;
}

function rand_name($_length = 4){
	
	$rand_simb = "qwertyuiopasdfghjklzxcvbnm0123456789";
	$l_name='';
	$top = strlen($rand_simb)-1;
	srand((double) microtime()*1000000);
	for($j=0; $j<$_length; $j++)$l_name .= $rand_simb{rand(0,$top)};
	return $l_name;
}

function getUnicFile($_length = 4, $_tpl = "%s", $_path = "./"){
	
	$fname = $_tpl;
	$limit = 0;
	do{
		
		$fname = sprintf($_tpl, rand_name($_length));
	}while (file_exists($_path.$fname)&&300<$limit++);
	return $fname;
}

function issetWData($VarName){

	return isset($_SESSION['xPOST'][$VarName]);
}

function storeWData($_VarName, $_VarData){
	
	storePOST($_VarName, $_VarData);
}

function loadWData($_VarName){
	
	return loadPOST($_VarName);
}

function unsetWData($VarName){
	
	unset($_SESSION['xPOST'][$VarName]);
}

function popWData($VarName){
	
	$WData = loadWData($VarName);
	unsetWData($VarName);
	return $WData;
}

function storePOST($_VarName, $_VarData){

	if(!preg_match('/^5\.3/',PHP_VERSION)){
		if(!session_is_registered('xPOST')){
			session_register('xPOST');
		}
	}else if(!isset($_SESSION['xPOST'])){
		$_SESSION['xPOST'] = array();
	}
	$_SESSION['xPOST'][$_VarName] = $_VarData;
}

function loadPOST($_VarName){
	
	if(!isset($_SESSION['xPOST'][$_VarName])) return null;
	return $_SESSION['xPOST'][$_VarName];
}

function unsetPOST(){
	
	if(isset($_SESSION['xPOST']))
		unset($_SESSION['xPOST']);
}

/**
 * is used all around the software
 * $headers = array('From'=>'from_value','FromName'=>'FromName_value','Sender'=>'Sender_value')
 *
 * @param string $email
 * @param string $subject
 * @param string $text
 * @param boolean $is_html
 * @param array $headers
 * @return boolean
 */
function ss_mail($email, $subject, $text, $is_html = true,$headers = array()){
	static $sleep;
	if($sleep){
		//uncomment if you want to send mail with a delay
		//sleep(1);
	}else {
		$sleep = true;
	}

	//$mailer = new PHPMailer();
	$mailer = new SSMailer();
	if(isset($headers['From']))
		$mailer->From = $headers['From'];
	else 
		$mailer->From = CONF_GENERAL_EMAIL;
	if(isset($headers['Sender']))
		$mailer->Sender = $headers['Sender'];
	else 	
		$mailer->Sender = CONF_GENERAL_EMAIL;
	if(isset($headers['FromName']))
		$mailer->FromName = $headers['FromName'];
	else
		$mailer->FromName = CONF_SHOP_NAME;
	$emails = explode(',',$email);
	$emails = array_map('trim',$emails);
	$emails = array_filter($emails,'strlen');
	foreach($emails as $email){
		$mailer->AddAddress($email);
	}
	$mailer->Subject = $subject;
	$mailer->Body = $text;
	
	$mailer->CharSet = 'utf-8';
	$mailer->IsHTML($is_html === true || $is_html === 2);
	if($is_html === true){
		
		$mailer->AltBody = str_replace("\r", '', strip_tags($text));
	}

	return $mailer->Send();
}

/**
 * @param string $string
 * @param bool $in_false_source - if true and translation not found, return original constant 
 * @return unknown
 */
function translate($string, $in_false_constant = true){
	
	/**
	 * Old language localization
	 */
	//if(defined($string))return constant($string);
	
	$Register = &Register::getInstance();
	$currlang_locals = &$Register->get('CURRLANG_LOCALS');
	if(isset($currlang_locals[$string])&&$currlang_locals[$string])return $currlang_locals[$string];
	$deflang_locals = &$Register->get('DEFLANG_LOCALS');
	if(isset($deflang_locals[$string])&&$deflang_locals[$string])return $deflang_locals[$string];
	
	
	//DEBUG:
	if(false&&($fp = fopen(DIR_TEMP.'/missed_locals.log','a'))){
		$backtrace = debug_backtrace();
		$backtrace = $backtrace[0];
		$file = str_replace(WBS_DIR,'',str_replace('\\','/',$backtrace['file']));
		fwrite($fp,"{$string}\t{$file}\t{$backtrace['line']}\n");
		fclose($fp);
	}
	
	return $in_false_constant?$string:'';
}

/**
 * Check safe mode and redirect to some page with message about safe mode
 *
 * @param bool $check - check safe mode
 * @param string $query - query for http redirect
 * @return false|null
 */
function safeMode($check, $query = ''){

	if(!$check || !CONF_BACKEND_SAFEMODE)return false;

	Message::raiseMessageRedirectSQ(MSG_ERROR, $query, translate("msg_safemode_warning"));
}

function pear_dump($data, $comment = ''){
	
	ob_start();
	print '<pre>';
	print_r($data);
	print '</pre>';
	PEAR::raiseError($comment.' - '.ob_get_contents());
	ob_end_clean();
}

function is_image($file){
	
	if(!preg_match('/\.(jpg|jpeg|jpe|gif|pcx|bmp|png)$/i', $file, $r))return false;
	
	return $r[1];
}

function checkPath($_path, $dont_check_path = WBS_DIR){
	
	if(file_exists($_path))return true;
	$dont_check_path = realpath($dont_check_path);
	
	$_path = str_replace('\\','/',$_path);
	$explFolders=explode('/',$_path);
	$fldNum=count($explFolders);
	for($wer=0;$wer<$fldNum;$wer++){
		
		$tPath='';
		for($qwe=0;$qwe<=$wer;$qwe++)$tPath.=$explFolders[$qwe].'/';
		
		if($dont_check_path && strpos($dont_check_path, $tPath) === 0 )continue;
		if(!file_exists($tPath) && $tPath){
			mkdir($tPath);
		}
	}
	return true;
}

function copy_dir( $src, $dest ){

	static $max;
	if(!isset($max))$max=0;
	$max++;
	if($max>25)return false;

	checkPath($dest);
	
	$handle = opendir($src);
$C = 0;
	while ( false !== ($file = readdir($handle)) && $C++<100) {
		
		if( $file == '.' || $file=='..')continue;
		
		if(is_dir($src.'/'.$file)){
			copy_dir($src.'/'.$file, $dest.'/'.$file);
		}else{
			copy($src.'/'.$file, $dest.'/'.$file);
		}
	}

	if($C>=100){
	print "$src, $dest<br>";
	}
	@closedir( $handle );
}

function delete_file($path){
	
	if(is_file($path)){
		unlink($path);
	}else{
		
		if ( !($handle = @opendir($path)) )
			return;
	
		while ( false !== ($file = readdir($handle)) ) {
			
			if ( $file == "." || $file == ".." ) continue;
			
			if(is_file($path.'/'.$file)){
				
				unlink($path.'/'.$file);
			}else{
				
				delete_file($path.'/'.$file);
			}
		}

		@closedir( $handle );
		
		rmdir($path);
	}
}

function getMonthDays($time){

	
	$time = strtotime(date('Y-m-15', $time));
	return date('d', strtotime('-1 day', strtotime(date('Y-m-01', strtotime('+1 month', $time)))));
}

function getWeekdayName($n){
	
	global $rWeekDays;

	return isset($rWeekDays[$n])?$rWeekDays[$n]:'';
}

/**
 * Transforms cyrillic symbols that string contains into latin with regard for transliteration
 *
 * @param string $str
 * @return string
 */
function translit($str){
	//if($UTF8)
		//$str = iconv("UTF-8", "WINDOWS-1251",$str);
	$result = "";

	$compliances = array("а"=>"a", "б"=>"b","в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ё"=>"yo","ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"j", "к"=>"k",
							"л"=>"l", "м"=>"m", "н"=>"n","о"=>"o","п"=>"p", "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f", "х"=>"h","ц"=>"c", "ч"=>"ch",
							"ш"=>"sh", "щ"=>"sh", "ы"=>"y", "ь"=>"'", "ю"=>"ju", "я"=>"ja", "э"=>"e",
							
							"А"=>"A", "Б"=>"B","В"=>"V", "Г"=>"G", "Д"=>"D", "Е"=>"E", "Ё"=>"Yo","Ж"=>"Zh", "З"=>"Z", "И"=>"I", "Й"=>"J", "К"=>"K",
							"Л"=>"L", "М"=>"M", "Н"=>"N","О"=>"O","П"=>"P", "Р"=>"R", "С"=>"S", "Т"=>"T", "У"=>"U", "Ф"=>"F", "Х"=>"H","Ц"=>"C", "Ч"=>"Ch",
							"Ш"=>"Sh", "Щ"=>"Sh", "Ы"=>"Y", "Ь"=>"'", "Ю"=>"Ju", "Я"=>"Ja", "Э"=>"E");
//Use ASCII Page codes
	/*$compliances = array(184=>'yo',168=>'Yo',
						192=>"A","B","V","G","D","E","Zh","Z","I","J","K",
							"L","M","N","O","P","R","S","T","U","F","H","C","Ch",
							"Sh","Sh","","Y","'","E","Ju","Ja",
							"a","b","v","g","d","e","zh","z","i","j","k",
							"l","m","n","o","p","r","s","t","u","f","h","c","ch",
							"sh","sh",'',"y","'","e","ju","ja");*/

							
	$strlen = mb_strlen($str,'UTF-8');
	for ($i = 0; $i < $strlen; $i++) {
		$char = mb_substr($str,$i,1,'UTF-8');
		//$symbol = ord($char);
		//$symbol_ = (int)$char;
		$result .= isset($compliances[$char])?$compliances[$char]:$char;
		
	}

	return $result;
}

function utf8_bad_replace($str, $replace = '?') {
    $UTF8_BAD =
    '([\x00-\x7F]'.                          # ASCII (including control chars)
    '|[\xC2-\xDF][\x80-\xBF]'.               # non-overlong 2-byte
    '|\xE0[\xA0-\xBF][\x80-\xBF]'.           # excluding overlongs
    '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.    # straight 3-byte
    '|\xED[\x80-\x9F][\x80-\xBF]'.           # excluding surrogates
    '|\xF0[\x90-\xBF][\x80-\xBF]{2}'.        # planes 1-3
    '|[\xF1-\xF3][\x80-\xBF]{3}'.            # planes 4-15
    '|\xF4[\x80-\x8F][\x80-\xBF]{2}'.        # plane 16
    '|(.{1}))';                              # invalid byte
    ob_start();
    while (preg_match('/'.$UTF8_BAD.'/S', $str, $matches)) {
        if ( !isset($matches[2])) {
            echo $matches[0];
        } else {
            echo $replace;
        }
        $str = substr($str,strlen($matches[0]));
    }
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

function make_slug($str){

	$str = strtolower(translit($str));
	$str = preg_replace('/ /ui', '-', $str);
	$str = preg_replace('/[^a-z0-9\-\_]/ui', '', $str);
	$str = preg_replace('/\-+/u', '-', $str);
	$str = preg_replace('/\_+/u', '_', $str);
	return $str == '-'?'':$str;
}

function encodeArray( $src_array, $excludes = null )
{
	if ( is_null($excludes) )
		$excludes = array();

	$result = array();

	foreach( $src_array as $key=>$value )
		if ( !in_array( $key, $excludes ) )
			$result[$key] = base64_encode( $value );
		else
			$result[$key] = $value;

	return $result;
}

function decodeArray($src_array, $excludes = null){
	
	if ( is_null($excludes) )
		$excludes = array();

	$result = array();

	foreach( $src_array as $key=>$value )
		if ( !in_array( $key, $excludes ) )
			$result[$key] = base64_decode( $value );
		else
			$result[$key] = $value;

	return $result;
}


function valid_email($email) {
	
  // First, we check that there's one @ symbol, and that the lengths are right
  if (!preg_match('/^[^@]{1,64}@[^@]{1,255}$/', $email)) {
    // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
    return false;
  }
  // Split it into sections to make life easier
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
     if (!preg_match('@^(([A-Za-z0-9!#$%&#038;\'*+/=?^_`{|}~-][A-Za-z0-9!#$%&#038;\'*+/=?^_`{|}~\.-]{0,63})|("[^(\\|")]{0,62}"))$@', $local_array[$i])) {
      return false;
    }
  }  
  if (!preg_match('@^\[?[0-9\.]+\]?$@', $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
        return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if (!preg_match('@^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$@', $domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
}

/**
 * @param mixed $space - file size
 * @param string $from_units - file size units (B,KB,MB,GB)
 * @param string $to_units - display string units
 * @return string
 */
function getDisplayFileSize($space, $from_units, $to_units = null){
	
	$allowed_units = array('B', 'KB', 'MB', 'GB');
	$units_view = array('B' => 'bytes', 'KB' => 'Kb', 'MB' => 'Mb', 'GB' => 'Gb');
	if(!in_array($from_units, $allowed_units))return $space;
	if(!is_null($to_units) && !in_array($to_units, $allowed_units))return $space;
	
	if(is_null($to_units)){
		
		$begin_conversion = false;
		$last_units = $from_units;
		$last_space = $space;
		foreach ($allowed_units as $curr_units){
			
			if($begin_conversion){
				
				$tspace = $last_space/1000;
				if($tspace<1){
					break;
				}else{
					$last_units = $curr_units;
					$last_space = ceil($tspace*100)/100;
				}
			}elseif($curr_units == $from_units){
				
				$begin_conversion = true;
			}
		}
		return $last_space.' '.$units_view[$last_units];
	}
}

function detectPDA(){
	$container = $_SERVER['HTTP_USER_AGENT'];
	$useragents = array (
		'iPhone','iPod',"Elaine/3.0","Palm","EudoraWeb","Blazer","AvantGo","Windows CE","Cellphone","Small","MMEF20","Danger","hiptop"
		,"Proxinet","ProxiNet","Newt","PalmOS","NetFront","SHARP-TQ-GX10","SonyEricsson","SymbianOS","UP.Browser"
		,"UP.Link","TS21i-10","BlackBerry","MOT-V",'portalmmm','Nokia','DoCoMo','Opera Mini'
		,"Palm" ,"Handspring","Nokia","Kyocera","Samsung","Motorola","Mot" ,"Smartphone","Blackberry"
		,"WAP","PlayStation Portable","LG","MMP","OPWV","Symbian","EPOC"
		,"Android");
	$pda = false;
	foreach ( $useragents as $useragent ) {
		if (preg_match("@{$useragent}@i",$container)){
			$pda = true;
			break;
		}
	}
	return $pda;
}

function make_clean_slug($string,$prefix,$table,$slug_field,$id_field = '',$id = null){
	$slug = make_slug($string);
	$used_string = array();
	$Register = &Register::getInstance();
	$DBHandler = &$Register->get(VAR_DBHANDLER);
	/* @var DBHandler DataBase*/
	$query = "SELECT DISTINCT`{$slug_field}` as slug FROM {$table} WHERE `{$slug_field}` LIKE ?".($id?" AND NOT(`{$id_field}` LIKE ?)":'');			
	$DBHandler->ph_query($query,$prefix.$slug.'%',$id);
	while($row = $DBHandler->fetch_assoc()){
		$used_slug[]= $row['slug'];
	}
	$DBHandler->freeResult();

	$max_i = 100;$_slug = $slug;
	while(($max_i--)>0 && in_array($prefix.$_slug,$used_slug)){
		$_slug = $slug.'_'.rand_name(3);
	}
	
	return $_slug;
}

function initCurlProxySettings(&$ch){
	$options = getProxySettings();
		
	if (isset($options['host'])&&strlen($options['host'])) {
		curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
        curl_setopt($ch, CURLOPT_PROXY, sprintf("%s%s",$options['host'],(isset($options['port'])&&$options['port']) ? ':'.$options['port'] :''));        
      //  print(sprintf("%s%s",$options['host'],(isset($options['port'])&&($options['port'])) ? ':'.$options['port'] : '').'<br><hr>'); 
    
		if (isset($options['user'])&&strlen($options['user'])) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD,sprintf("%s:%s",$options['user'],$options['password']));
		//	print(sprintf("%s:%s",$options['user'],$options['password']).'<br><hr>');
		}
	}
	curl_setopt($ch, CURLOPT_USERAGENT,'WebAsyst CURL 1.0');
}

function getProxySettings(){
	$Register = &Register::getInstance();
	/* @var $Register Register */
	$options = $Register->get('PROXY');
	if(is_null($options)){
		$options = SystemSettings::get(array(
				'host'=>'PROXY_HOST','port'=>'PROXY_PORT',
				'user'=>'PROXY_USER','password'=>'PROXY_PASS'));
		$Register->set('PROXY',$options);
	}
	return $options;
}

function error404page($debug = null){
	global $error404;
	$register = Register::getInstance();
	$smarty = $register->get(VAR_SMARTY);
	$error404 = true;
	header("HTTP/1.1 404 Not Found;");
   	header("Status: 404 Not Found;");
	$smarty->assign('link404',$_SERVER['REDIRECT_URL']);
	$smarty->assign('page_not_found404',true);
}

function onPageComplete($page = false)
{
	global $debug_total_time;
	global $debug_total_sql_query;
	global $debug_sql_query_stack;
	$debug_total_string = '';
	if(isset($debug_total_time)&&$debug_total_time){

		$time = microtime(true)-$debug_total_time;
		$debug_total_memory = function_exists('memory_get_peak_usage')?memory_get_peak_usage():0;
		$debug_total_string .= sprintf('<div style="z-index:9999;position:fixed;right:50px;top:5px;height:23px;padding:0px;padding-bottom:2px;font-weight:bolder;color:green;background-color:#000033;opacity:0.8;border:1px dotted grey;"><span style="padding-left:10px;color:%s;">%0.3f s</span>'."\t",($time>0.5?($time>1?'red':'yellow'):'green'),$time);
		if($debug_total_memory){
			$debug_total_memory /= 1048576;
			$debug_total_string .= sprintf('<span style="padding-left:10px;color:%s;"> %0.3f MB</span>'."\t",($debug_total_memory>8?($debug_total_memory>16?'red':'yellow'):'green'),$debug_total_memory);
		}
		if(extension_loaded('eAccelerator')){
			$eaccelerator_info= eaccelerator_info();

			if($eaccelerator_info['cache']){
				$debug_total_string .= sprintf('<span style="padding-left:10px;color:%s;"> eAccelerator %s [%0.2fMB(%d%%)/%d scripts]</span>'."\t"."\n",'cyan',$eaccelerator_info['version'],$eaccelerator_info['memoryAllocated']/1048576,100*$eaccelerator_info['memoryAllocated']/$eaccelerator_info['memorySize'],$eaccelerator_info['cachedScripts']);
			}
		}elseif(extension_loaded('xCache')){
			if(isset($_GET['xcache'])){
				$pcnt = xcache_count(XC_TYPE_PHP);
				$total = array(
					'size'=>0,
					'avail'=>0,
					'cached'=>0,
					'slots'=>0,
				);
				$fields = array('size','avail','cached','slots');
				for ($i = 0; $i < $pcnt; $i ++) {
					$data = xcache_info(XC_TYPE_PHP, $i);
					foreach($fields as $field){
						$total[$field] += $data[$field];
					}
				}
				$total['used'] = $total['size'] - $total['avail'];
				$version = phpversion('xcache');
				$debug_total_string .= sprintf('<span style="padding-left:10px;color:%s;"> xCache %s [%0.2fMB(%d%%)/%d scripts]</span>'."\t"."\n",'cyan',$version,$total['used']/1048576,100*$total['used']/$total['size'],$total['cached']);
			}else{
				$version = phpversion('xcache');
				$debug_total_string .= sprintf('<span style="padding-left:10px;color:%s;"> xCache %s</span>'."\t"."\n",'cyan',$version);
			}
		}
		$debug_total_string .= sprintf('<span style="padding-left:10px;color:%s;"> %d SQL query</span>'."\t"."\n",($debug_total_sql_query>50?($debug_total_sql_query>100?'red':'yellow'):'green'),$debug_total_sql_query);
		
		$debug_total_string .= sprintf('<span style="padding-left:10px;"> %d File(s)</span>'."\t"."\n",count(get_included_files()));
		$page_size = ob_get_length();
		$debug_total_string .= sprintf('<span style="padding-left:10px;"> %0.3f KB page size</span>'."\t",($page_size?$page_size:strlen($page))/1024);
		//$debug_total_string .= sprintf('<span style="padding-left:10px;"> %s mode</span>',false?'DEV':'PRODUCTION');
		$debug_total_string .= '<img style="padding:0;padding-left:10px;margin:0;cursor:pointer;" src="'.URL_IMAGES_COMMON.'/close.gif" title="close" alt="close" onclick="this.parentNode.style.display=\'none\';"></div>';

		$firebug_enabled = false;

		if(class_exists('FirePHP')){
			$firebug_enabled = (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'firephp')===false)?false:true;
			$firebug = FirePHP::getInstance(true);
			$firebug_enabled = $firebug->detectClientExtension();
			$firebug->setEnabled($firebug_enabled);
			$firebug->info(strip_tags($debug_total_string),basename(__FILE__));
			if($debug_sql_query_stack && (isset($_COOKIE['debug'])&&($_COOKIE['debug'] == 'sql'))){
				$total = 0.0;
				foreach($debug_sql_query_stack as $stack_item){
					$total += $stack_item['time'];
				}
				$debug_sql_query_stack[] = array(
					'#'=>'TOTAL',
					'time'=>sprintf('%0.2f',$total),
					'query'=>'',
				);
				$firebug->table('SQL',$debug_sql_query_stack);
			}
		}
		if(!SystemSettings::is_hosted() && (isset($_COOKIE['debug']) && ($_COOKIE['debug'] == 'log')) && ($fp = @fopen(DIR_TEMP.'/access.'.date( "Y.m.d").'.log' , "a" ))){
			@fwrite( $fp,date( "Y-m-d H:i:s" )."\t".$_REQUEST['REQUEST_URI'].$_REQUEST['QUERY_STRING']."\t".preg_replace('/\s+/',' ',strip_tags($debug_total_string) )."\n");
			if($debug_sql_query_stack){
				foreach($debug_sql_query_stack as $query){
					@fwrite( $fp,"\t".implode("\t",$query)."\n\n");
				}
				@fwrite( $fp,"\n\n==========================================\n");
			}
			@fclose( $fp );
		}
	}
	session_write_close();
	if($page){
			if(!$firebug_enabled){
				print preg_replace('@</body>\s*</html>\s*$@','',$page);
				print $debug_total_string.'
	</body>
</html>';
			}else{
				print $page;
				return $debug_total_string;
			}
		}
}

?>