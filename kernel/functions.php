<?php

	//
	// Non-DBMS application functions
	//


	//
	// I would like to thank my wife Nastia for her inspiration and
	//         consistent support during the whole project development period.
	//
	//                                  Alexey Bobkov, WebAsyst production manager.
	//

	//
	// String functions
	//
	function valid_email($email) {

	  // First, we check that there's one @ symbol, and that the lengths are right
	  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
	    // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
	    return false;
	  }
	  // Split it into sections to make life easier
	  $email_array = explode("@", $email);
	  $local_array = explode(".", $email_array[0]);
	  for ($i = 0; $i < sizeof($local_array); $i++) {
	     if (!ereg("^(([A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~-][A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
	      return false;
	    }
	  }
	  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
	    $domain_array = explode(".", $email_array[1]);
	    if (sizeof($domain_array) < 2) {
	        return false; // Not enough parts to domain
	    }
	    for ($i = 0; $i < sizeof($domain_array); $i++) {
	      if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
	        return false;
	      }
	    }
	  }
	  return true;
	}

	function prepareURLStr( $pageName, $parameters = null )
	//
	// Prepares URL string
	//
	//		Parameters:
	//			$pageName - page name
	//			$parameters - an associative array containing parameters
	//
	//		Returns URL string
	//
	{
		if ($parameters == null)
			$parameters = array();
		
		$str_link = (strpos($pageName,'?')===false)?"%s?%s":"%s&amp;%s";
		$paramArray = array();
		$paramStr = "";

		if ( ini_get('session.use_trans_sid') )
			$paramArray[] = ini_get( 'session.name' ).'='.session_id();
		
		$inplaceScreen = false;
		if (isset($_GET["inplaceScreen"]))
			$inplaceScreen = $_GET["inplaceScreen"];
		if (isset($_POST["inplaceScreen"]))
			$inplaceScreen = $_POST["inplaceScreen"];
		if ($inplaceScreen)
			$parameters["inplaceScreen"] = $inplaceScreen;

		if ( !is_array($parameters) )
			$parameters = array();

		while ( list( $key, $val ) = each ( $parameters ) )
			$paramArray[count( $paramArray )] = $key."=".$val;

		$paramStr = implode( "&amp;", $paramArray );

		return sprintf( $str_link, $pageName, $paramStr );
	}

	function checkIDSymbols( $str, $symbols )
	//
	// Checks if the string contains only symbols from string symbols
	//
	//		Parameters:
	//			$str - string
	//			$symbols - string, symbols list
	//
	//		Returns URL string
	//
	{
		for ( $i = 0; $i < strlen( $str ); $i++ ) {
			$found = false;
			for ( $j = 0; $j < strlen( $symbols ); $j++ )
				if ( strtoupper( $str[$i] ) == strtoupper( $symbols[$j] ) ) {
					$found = true;
					break;
				}
			if ( !$found ) {
				return false; }
		}

		return true;
	}

	function parseSortStr( $str )
	//
	// Get the column sort information from given string
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns associative array with fields: field=fieldName and order=sortOrder
	//
	{
		$curStatData = explode( " ", trim($str) );

		$statusFieldPart = $curStatData[0];
		$statusOrderPart = isset($curStatData[1])?$curStatData[1]:'';

		return array( "field"=>$statusFieldPart, "order"=>$statusOrderPart );
	}

	function strTruncate( $str, $length )
	//
	// Truncates string so its length is not more than certain value. If string is truncated, dots are added at the end
	//
	//		Parameters:
	//			$str - string
	//			$length - demanded length
	//
	//		Returns truncated string
	//
	{
		if ( is_null($length) )
			return $str;

		if ( strlen( $str ) > $length )
			return substr( $str, 0, $length )."...";
		else
			return $str;
	}

	function stripLineBreaks( $str )
	//
	// Strips string of line breaks
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns string
	//
	{
		$str = str_replace( "\r\n", " ", $str );
		return str_replace( "\n", " ", $str );
	}

	function isIntStr( $str )
	//
	// Checks if string is integer
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns value of boolean type
	//
	{
		return (is_numeric($str) && intval($str) == $str);
	}

	function isFloatStr( $str )
	//
	// Checks if string is integer
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns value of boolean type
	//
	{
		$chars = "1234567890.";

		$str = (string)$str;

		for ( $j = 0; $j < strlen($str); $j++ ) {
			$found = 0;
			for ( $i = 0; $i < strlen($chars); $i++ ) {
				if ( $str[$j] == $chars[$i] ) {
					$found = 1;
					break;
				}
			}
			if ( !$found ) return false;
		}

		return true;
	}

	function isTimeStr( $str )
	//
	// Checks if string is a time value (hh:mm)
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns boolean
	//
	{
		if ( !ereg("^([[:digit:]]{1,2}):([[:digit:]]{2})$",$str ) )
			return false;

		$parts = explode( ":", $str );

		return ($parts[0] >= 0 && $parts[0] <= 23) && ($parts[1] >= 0 && $parts[1] <= 59);
	}

	function isColorStr( $str )
	//
	// Checks if string is a color value (#xxxxxx)
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns boolean
	//
	{
		if ( !ereg("^#([[:xdigit:]]{1,6})$",$str ) )
			return false;

		return true;
	}

	function prepareStrToStore( $str )
	//
	// Prepares string to its safe storing in database
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns formated string
	//
	{
		if ( ini_get('magic_quotes_gpc') )
			$str = trim( stripslashes($str) );
		else
			$str = trim( $str );

		return $str;
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
	 * Escape string for store in database
	 *
	 * @param mixed $_Data
	 * @param array $_Params - 'esctype' posible values ESCSQLTYPE_GENERAL, ESCSQLTYPE_HOLDERS
	 * @return mixed escaped data
	 */
	function xEscapeSQLstring ( $_Data, $_Params = null, $_Key = array() ){

		if (!is_array($_Data)){

			if (!is_array($_Params)) $_Params = array('esctype'=>ESCSQLTYPE_GENERAL|ESCSQLTYPE_HOLDERS);
			if (!isset($_Params['esctype']))$_Params['esctype'] = ESCSQLTYPE_GENERAL|ESCSQLTYPE_HOLDERS;

			if($_Params['esctype']&ESCSQLTYPE_GENERAL)
				$_Data = mysql_real_escape_string ($_Data);
			if($_Params['esctype']&ESCSQLTYPE_HOLDERS){
				$_Data = str_replace( '&', '\&', $_Data );
				$_Data = str_replace( '?', '\?', $_Data );
			}

			return $_Data;
		}

		if (!is_array($_Key))$_Key = array($_Key);
		foreach ($_Data as $__Key=>$__Data){

			$ProcessFlag = false;
			if (count($_Key)&&!is_array($__Data)){

				if (in_array($__Key, $_Key)){

					$ProcessFlag = true;
				}
			}else{

				$ProcessFlag = true;
			}
			if($ProcessFlag){

				$_Data[$__Key] = xEscapeSQLstring( $__Data, $_Params, $_Key);
			}
		}
		return $_Data;
	}

	function utf2win1251( $utf8_string )
	//
	// Converts UTF-8 encoded string to windows-1251 encoded string
	//
	//		Parameters:
	//			$utf8_string - source string
	//
	//		Returns decoded string
	//
	{
		$out = "";
		$ns = strlen( $utf8_string );
		for ( $nn = 0; $nn < $ns; $nn++ ) {
			$ch = $utf8_string[$nn];
			$ii = ord($ch);

			if ( $ii < 128 )
				$out .= $ch;
		  else
			if ( $ii >>5 == 6 ) {
				$b1 = ($ii & 31);
				$nn++;
				$ch = $utf8_string[$nn];
				$ii = ord ($ch);
				$b2 = ($ii & 63);
				$ii = ($b1 * 64) + $b2;
				$ent = sprintf("&#%d;", $ii);
				$out .= $ent;
			} else
				if ( $ii >>4 == 14 ) {
					$b1 = ($ii & 31);
					$nn++;
					$ch = $utf8_string[$nn];
					$ii = ord($ch);
					$b2 = ($ii & 63);
					$nn++;
					$ch = $utf8_string [$nn];
					$ii = ord ($ch);
					$b3 = ($ii & 63);
					$ii = ((($b1 * 64) + $b2) * 64) + $b3;
					$ent = sprintf("&#%d;", $ii);
					$out .= $ent;
				}
					else
						if ($ii >>3 == 30) {
							$b1 = ($ii & 31);
							$nn++;
							$ch = $utf8_string [$nn];
							$ii = ord($ch);
							$b2 = ($ii & 63);
							$nn++;
							$ch = $utf8_string [$nn];
							$ii = ord($ch);
							$b3 = ($ii & 63);
							$nn++;
							$ch = $utf8_string [$nn];
							$ii = ord($ch);
							$b4 = ($ii & 63);
							$ii = ((((($b1 * 64) + $b2) * 64) + $b3) * 64) + $b4;
							$ent = sprintf("&#%d;", $ii);
							$out .= $ent;
						}
		}

		return $out;
	}

	function formatFloat($value, $decimals = 2, $separator = "," )
	//
	// Formats float number
	//
	//		Parameters:
	//			$value - number
	//			$decimals - number of signs after comma
	//			$separator - delimiter
	//
	//		Returns formated string
	//
	{
		if ( !strlen( $value ) )
			return "";

		$m = pow ( 10, $decimals );
		$value = round ( $value*$m+0.0001 )/$m;
		$format = sprintf("%%01.%sf", $decimals);

		$value = sprintf( $format, $value );
		return str_replace( ".", $separator, $value );
	}

	function prepareStrToDisplay( $str, $convertToHTML = false, $stripSlashes = false )
	//
	// Prepares string to its safe displaying in HTML
	//
	//		Parameters:
	//			$str - string
	//			$convertToHTML - if it is true, the function htmlspecialchars() is applied to the string
	//			$stripSlashes - it it is true, the function stripSlashes() is applied to the string
	//
	//		Returns formated string
	//
	{
		if ( is_array( $str ) )
			return $str;

		if ( $stripSlashes )
			$str = stripSlashes( $str );

		$str = str_replace( "&quot;", "\"", $str );
		$str = str_replace( "&apos;", "'", $str );
		$str = str_replace( "&bsl;", "\\", $str );

		if ( $convertToHTML )
			return nl2br( htmlspecialchars( $str ) );
		else
			return nl2br( $str );
	}

	function formatAddress( $street, $city, $country )
	//
	// Formats address
	//
	//		Parameters:
	//			$street - address
	//			$city - city
	//			$country - country
	//
	//		Returns formated string containing address
	//
	{
		$result = null;

		if (strlen( $street ))
			$result = $street;

		if (strlen( $city ))
			$result .= ", ". $city;

		if (strlen( $country ))
			$result .= ", ". $country;

		return $result;
	}

	function ra_cmp ($a, $b)
	//
	// Compares array elements
	//
	//		Parameters:
	//			$a - first element
	//			$b - second element
	//
	//		Returns result of comparance (-1, 0, 1)
	//
	{
		if ($a == $b)
			return 0;

		return ($a > $b) ? 1 : -1;
	}

	function resortArray( $array, $key, $pos )
	//
	// Reassorts array elements with regard for elements values
	//
	//		Parameters:
	//			$array - array
	//			$key - key of element, which position should be changed
	//			$pos - position of element, before which the element $key should be placed
	//
	//		Returns reassorted array
	//
	{
		$array[$key] = $pos - 0.1;

		uasort( $array, "ra_cmp" );

		$res_array = array();
		$index = 1;
		foreach ($array as $a_key => $a_pos) {
			$res_array[$a_key] = $index;

			$index++;
		}

		return $res_array;
	}

	function collapseArray( $array )
	//
	// Reassorts array elements deleting blanks in indexation
	//
	//		Parameters:
	//			$array - array
	//
	//		Returns reassorted array
	//
	{
		$curIndex = 1;

		foreach ($array as $a_key => $a_pos){
			$array[$a_key] = $curIndex;

			$curIndex++;
		}

		return $array;
	}

	function translit( $str, $do = false )
	//
	// Transforms cyrillic symbols that string contains into latin with regard for transliteration
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns transformed string
	//
	{
		
		if(!$do)return $str;
		$result = "";

		$compliances = array("а"=>"a", "б"=>"b","в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ё"=>"yo","ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"j", "к"=>"k",
								"л"=>"l", "м"=>"m", "н"=>"n","о"=>"o","п"=>"p", "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f", "х"=>"h","ц"=>"c", "ч"=>"ch",
								"ш"=>"sh", "щ"=>"sh", "ы"=>"y", "ь"=>"'", "ю"=>"ju", "я"=>"ja", "э"=>"e");

		$capitalCompliances = array("А"=>"A", "Б"=>"B","В"=>"V", "Г"=>"G", "Д"=>"D", "Е"=>"E", "Ё"=>"Yo","Ж"=>"Zh", "З"=>"Z", "И"=>"I", "Й"=>"J", "К"=>"K",
								"Л"=>"L", "М"=>"M", "Н"=>"N","О"=>"O","П"=>"P", "Р"=>"R", "С"=>"S", "Т"=>"T", "У"=>"U", "Ф"=>"F", "Х"=>"H","Ц"=>"C", "Ч"=>"Ch",
								"Ш"=>"Sh", "Щ"=>"Sh", "Ы"=>"Y", "Ь"=>"'", "Ю"=>"Ju", "Я"=>"Ja", "Э"=>"E");


		for ($i = 0; $i < strlen($str); $i++) {
			$symbol = substr($str,$i,1);

			$item = "";

			if ( array_key_exists($symbol, $compliances) )
				$item = $compliances[$symbol];
			else if (!strlen($item) && array_key_exists($symbol, $capitalCompliances) )
					$item = $capitalCompliances[$symbol];
			else
				$item = "";

			if (strlen($item))
				$result .= $item;
			else
				$result .= $symbol;
		}

		return $result;
	}

	function loginMessageRequired( $U_ID, $kernelStrings, &$infoStr )
	//
	// Checks if the message should be displayed during the login
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$infoStr - message text
	//
	//		Returns value of boolean type
	//
	{
		$QuotaManager = new DiskQuotaManager();

		$spaceUsed = $QuotaManager->GetAvailableSystemSpace($kernelStrings);

		if ( !is_null($spaceUsed) && $spaceUsed == 0 ) {
			$infoStr = $kernelStrings['app_dbsizelimit_message'];
			return true;
		}

		return false;
	}

	function isSystemFirstLogin()
	//
	// Checks if user login is a first login
	//
	//		Returns value of boolean type
	//
	{
		global $databaseInfo;

		$loginFlag = $databaseInfo[HOST_DBSETTINGS][HOST_FIRSTLOGIN];

		return !$loginFlag;
	}

	//
	// Array functions
	//

	function decodeObjectFields( $srcObject, $excludes = null )
	//
	// Decodes object fields with base64_decode and stores values to array
	//
	//		Parameters:
	//			$srcObject - object
	//			$excludes - array containing a list of excluding fields
	//
	//		Returns array
	//
	{
		if ( is_null( $excludes ) )
			$excludes = array();

		$srcObject = (array)$srcObject;
		$result = array();

		foreach( $srcObject as $key => $value )
			if ( !in_array( $key, $excludes ) )
				$result[$key] = base64_decode($value);
			else
				$result[$key] = $value;

		return $result;
	}

	function refinedSlice( $array, $offset, $length )
	//
	// Replacement for array_slice function.
	//		This function correctly processes numeric keys
	//
	//		Parameters:
	//			$array - source array
	//			$offset - offset
	//			$length - length
	//			(see array_slice for details)
	//
	//		Returns sliced array
	//
	{
		$replacement = array();
		foreach( $array as $key=>$value )
			$replacement[$key."_"] = $value;

		$replacement = array_slice( $replacement, $offset, $length );

		$array = array();
		foreach( $replacement as $key=>$value ) {
			$key = substr( $key, 0, strlen($key)-1 );
			$array[$key] = $value;
		}

		return $array;
	}

	function refinedMerge( $array1, $array2 )
	//
	// Replacement for array_merge function.
	//		This function correctly processes numeric keys
	//
	//		Parameters:
	//			$array1 - first array
	//			$array2 - second array
	//
	//		Returns merged array
	//
	{
		$result = $array1;

		foreach( $array2 as $key=>$data )
			$result[$key] = $data;

		return $result;
	}

	function array_keyIndex( $array, $key )
	//
	// Finds key in array and return its index
	//
	//		Parameters:
	//			$array - array
	//			$key - key to find
	//
	//		Returns key index or null
	//
	{
		$array_keys = array_keys($array);
		if ( in_array( $key, $array_keys ) )
			return array_search( $key, $array_keys );

		return null;
	}

	function getElementByIndex( $array, $index )
	//
	// Returns array element by numeric index
	//
	//		Parameters:
	//			$array - source array
	//			$index - numeric index, zero-based
	//
	//		Returns array element value or null
	//
	{
		$keys = array_keys( $array );
		if ( (count($keys) - 1) < $index )
			return null;

		return $array[$keys[$index]];
	}

	//
	// Callback functions support
	//

	function registerApplicationEvent( $APP_ID, $eventName )
	//
	// Registers application event in global event list
	//
	//		Parameters:
	//			$APP_ID - identifier of application that generates an event
	//			$eventName - event name
	//
	//		Returns null
	//
	{
		global $wbsEventTable;

		$applicationList = array_keys( $wbsEventTable );
		if ( !in_array( $APP_ID, $applicationList ) ) {
			$wbsEventTable[$APP_ID][$eventName] = array();

			return null;
		}

		$eventList = array_keys( $wbsEventTable[$APP_ID] );
		if ( !in_array( $eventName, $eventList ) )
			$wbsEventTable[$APP_ID][$eventName] = array();

		return null;
	}

	function registerEventHandler( $APP_ID, $eventName, $handler_APP_ID, $handlerName, $fileName )
	//
	// Registers event handler
	//
	//		Parameters:
	//			$APP_ID - identifier of application that generates an event
	//			$eventName - event name
	//			$handler_APP_ID - identifier of application that registers handler
	//			$handlerName - name of event handler
	//			$fileName - name of file that contains handler
	//
	//		Returns null
	//
	{
		global $wbsEventTable;

		$wbsEventTable[$APP_ID][$eventName][$handler_APP_ID] = array( $fileName, $handlerName );

		return null;
	}

	function checkHandlerList( $APP_ID, $eventName )
	//
	// Checks if all dependent applications have registered event handlers
	//
	//		Parameters:
	//			$APP_ID - identifier of application that generates an event
	//			$eventName - event name
	//
	//		Returns identifier of application that does not generate an event, or null
	//
	{
		global $wbsEventTable;

		$childApps = listChildApplications( $APP_ID );

		if ( !isset($wbsEventTable[$APP_ID][$eventName])) return null;
		if ( !is_array($wbsEventTable[$APP_ID][$eventName])) return null;

		$registeredApps = array_keys( $wbsEventTable[$APP_ID][$eventName] );

		for ( $i = 0; $i < count( $childApps ); $i++ )
			if ( !in_array( $childApps[$i], $registeredApps ) )
				return $childApps[$i];

		return null;
	}

	function saveVariable( $varName, $varValue )
	//
	// Makes variable global
	//
	//		Parameters:
	//			$varName - variable name
	//			$varValue - variable value
	//
	//		Returns null
	//
	{
		$tmpVar = $varValue;
		$GLOBALS[$varName]  = $varValue;

		return null;
	}

	function prepareFileContent( $filePath )
	//
	// Prepares file content to be carried out with the help of eval
	//
	//		Parameters:
	//			$filePath - path to file
	//
	//		Returns string containing file content
	//
	{
		$fileContent = file( $filePath );
		$fileContent = trim(implode("", $fileContent));

		return substr( $fileContent, 5, strlen($fileContent)-8 );
	}

	function saveVariables( $vars, $prefix )
	//
	// Makes variables, which start with certain prefix, global
	//
	//		Parameters:
	//			$vars - list of variables
	//			$prefix - prefix of variables' names
	//
	//		Returns null
	//
	{
		foreach ( $vars as $var_name=>$var_value )
			if ( substr($var_name, 0, strlen($prefix)) == $prefix )
				saveVariable( $var_name, $var_value );

		return null;
	}

	function handleEvent( $APP_ID, $eventName, $params, $language, $approveOnly = false, $simpleQuiz = false )
	//
	// Sequently implements all event handlers
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$eventName - event name
	//			$params - an array containing parameters of handler
	//			$language - user language
	//			$approveOnly - performs approving only if true
	//			$simpleQuiz - quiz mode. Returns first pass result without any processing
	//
	//		Returns null, or PEAR_Error.
	//			Erros can be of two types:
	//				- if handler is not found in any of child applications, the error code equals to ERRCODE_HANDLERNOTFOUND.
	//				- error returned by some handler.
	//			In both cases the further function execution aborting
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;
		global $loc_str;
		global $wbsEventTable;

		$kernelStrings = $loc_str[$language];

		$quizResult = array();

		if (!isset($wbsEventTable[$APP_ID]) || !is_array($wbsEventTable[$APP_ID]) )
			return null;

		if ( !is_null( $errApp = checkHandlerList($APP_ID, $eventName) ) )
			return PEAR::raiseError ( sprintf($kernelStrings[ERR_HANDLERNOTFOUND], getAppName($errApp, $language)),
										ERRCODE_HANDLERNOTFOUND );

		$handlerList = (!empty($wbsEventTable[$APP_ID][$eventName]))?
					$wbsEventTable[$APP_ID][$eventName]:null;

		if ( !is_array($handlerList) )
			 return null;

		$endPass = $approveOnly ? 1 : 2;

		for ( $passIndex = 1; $passIndex <= $endPass; $passIndex ++ ) {
			$params[CALL_TYPE] = ( $passIndex == 1 ) ? CT_APPROVING : CT_ACTION;
			$params[KERNEL_LOCSTRINGS] = $kernelStrings;

			foreach( $handlerList as $hanlerAPP_ID=>$handlerData ) {
				$handlerName = $handlerData[1];
				$handlerFile = $handlerData[0];

				$params[LANGUAGE] = getApplicationLanguage( $hanlerAPP_ID, $language );

				$appPrefix = strtolower($hanlerAPP_ID);
				$dir_name = sprintf( WBS_DIR."published/%s/", strtoupper($appPrefix) );
				$mainFileName = sprintf( "%s%s.php", $dir_name, $appPrefix );
				$fileName = $dir_name.$handlerFile;

				if ( !file_exists($fileName) )
					return PEAR::raiseError ( sprintf($kernelStrings[ERR_HANDLERNOTFOUND], getAppName($hanlerAPP_ID, $language)),
												ERRCODE_HANDLERNOTFOUND );

				@include_once( $fileName );

				$params[APP_SCRIPTPATH] = $mainFileName;
				$params[APP_DIRPATH] = dirname($mainFileName);

				if ( !function_exists($handlerName) )
					return PEAR::raiseError ( sprintf($kernelStrings[ERR_HANDLERNOTFOUND], getAppName($hanlerAPP_ID, $language)),
												ERRCODE_HANDLERNOTFOUND );

				$resultStatus = eval( "return $handlerName(\$params);" );

				if ( $passIndex == 1 ) {
					if ( !$simpleQuiz ) {
						if ( $resultStatus != EVENT_APPROVED )
							if ( PEAR::isError( $resultStatus ) )
								return PEAR::raiseError ( sprintf($kernelStrings[ERR_NOTAPPROVEDERR], $resultStatus->getMessage(), getAppName($hanlerAPP_ID, $language)),
														ERRCODE_NOTAPPROVEDERR );
							else
								return PEAR::raiseError ( sprintf($kernelStrings[ERR_NOTAPPROVED], getAppName($hanlerAPP_ID, $language)),
														ERRCODE_NOTAPPROVED );
					} else
						$quizResult[$hanlerAPP_ID] = $resultStatus;
				} else
					if ( PEAR::isError($resultStatus) )
						return PEAR::raiseError ( sprintf($kernelStrings[ERR_DEPENDENTERROR], getAppName($hanlerAPP_ID, $language), $resultStatus->getMessage()),
												ERRCODE_NOTAPPROVEDERR );
			}
		}

		if ( $simpleQuiz )
			return $quizResult;

		return null;
	}

	function listApplicationEvents( $APP_ID )
	//
	// Returns list of registered application events
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//
	//		Returns an array containing strings
	//
	{
		global $wbsEventTable;

		$handlerList = $wbsEventTable[$APP_ID];
		if ( !is_array($handlerList) )
			return null;

		return array_keys( $handlerList );
	}

	//
	// Quiz functions support
	//

	function registerQuizHanlder( $APP_ID, $eventName, $handler_APP_ID, $handlerName, $fileName )
	//
	// Registers quiz handler
	//
	//		Parameters:
	//			$APP_ID - identifier of application that generates an event
	//			$eventName - event name
	//			$handler_APP_ID - identifier of application that registers handler
	//			$handlerName - handler name
	//			$fileName - name of file that contains handler
	//
	//		Returns null
	//
	{
		global $wbsQuizList;

		$wbsQuizList[$APP_ID][$eventName][$handler_APP_ID] = array( $fileName, $handlerName );
	}

	function listApplicationQuizzes( $APP_ID )
	//
	// Returns list of registered application quizzes
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//
	//		Returns array containing strings
	//
	{
		global $wbsQuizList;

		$handlerList = $wbsQuizList[$APP_ID];
		if ( !is_array($handlerList) )
			return null;

		return array_keys( $handlerList );
	}

	function registerApplicationQuiz( $APP_ID, $eventName )
	//
	// Registers a quiz in global quiz list
	//
	//		Parameters:
	//			$APP_ID - identifier of application that contains a quiz
	//			$eventName - event name
	//
	//		Returns null
	//
	{
		global $wbsQuizList;

		$applicationList = array_keys( $wbsQuizList );
		if ( !in_array( $APP_ID, $applicationList ) ) {
			$wbsQuizList[$APP_ID][$eventName] = array();

			return null;
		}

		$eventList = array_keys( $wbsQuizList[$APP_ID] );
		if ( !in_array( $eventName, $eventList ) )
			$wbsQuizList[$APP_ID][$eventName] = array();

		return null;
	}

	function performQuiz( $APP_ID, $eventName, $params, $language )
	//
	// Performs quiz of applications
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$eventName - quiz name
	//			$params - an array containing handler parameters
	//			$language - user language
	//
	//		Returns an array containing answers:
	//			array( APP_ID1=>answerData1 )
	//
	{
		global $loc_str;
		global $wbsQuizList;

		$kernelStrings = $loc_str[$language];

		$result = array();

		if ( !is_array($wbsQuizList[$APP_ID]) )
			return $result;

		$handlerList = $wbsQuizList[$APP_ID][$eventName];
		if ( !is_array($handlerList) )
			 return $result;

		$params[LANGUAGE] = $language;
		$params[KERNEL_kernelStrings] = $kernelStrings;

		foreach( $handlerList as $hanlerAPP_ID=>$handlerData ) {
			$handlerName = $handlerData[1];
			$handlerFile = $handlerData[0];

			$appPrefix = $hanlerAPP_ID;
			$dir_name = sprintf( WBS_DIR."/kernel/applications/%s/", $appPrefix );
			$mainFileName = sprintf( "%s%s.php", $dir_name, $appPrefix );
			$fileName = $dir_name.$handlerFile;

			if ( !file_exists($fileName) )
				continue;

			@include_once( $fileName );

			$params[APP_SCRIPTPATH] = $mainFileName;
			$params[APP_DIRPATH] = dirname($mainFileName);

			if ( !function_exists($handlerName) )
				continue;

			$answer = eval( "return $handlerName(\$params);" );
			$result[$hanlerAPP_ID] = $answer;
		}

		return $result;
	}

	//
	// Date and time functions
	//

	function getFirstStrInt ( $str )
	//
	// Internal function. Searches first numerical part in string.
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns integer
	//
	{
		$sIndex = -1;

		for ( $j = 0; $j < strlen( $str ); $j++ ) {
			if ( !is_numeric( $str[$j] ) ) {
				$sIndex = $j;
				break;
			}
		}
		if ( $sIndex == -1 ) return $str;
		return substr( $str, 0, $sIndex );
	}

	function getFirstSeparatorPos( $str )
	//
	// Internal function. Searches for position, where numerical part of string ends.
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns integer
	//
	{

		for ( $j = 0; $j < strlen( $str ); $j++ )
			if ( !is_numeric( $str[$j] ) )
				return $j;

		return 0;
	}

	function getNextIntPart( $str, $start )
	//
	// Internal function. Searches numerical part of string, starting from the certain position
	//
	//		Parameters:
	//			$str - string
	//			$start - position, which is the start of search
	//
	//		Returns integer
	//
	{
		$sIndex = -1;
		for ( $j = $start; $j < strlen($str); $j++ ) {
			if ( is_numeric( $str[$j] ) && ($str[$j] != " " ) ) {
				$sIndex = $j;
				break;
			}
		}
		if ( $sIndex == -1 )
			return "";

		return substr( $str, $sIndex );
	}

	function extendNumber( $str )
	//
	// Internal function. Places "0" at the beginning of string, if its length less than 2
	//
	//		Parameters:
	//			$str - string
	//
	//		Returns modified string
	//
	{
		if ( strlen( $str ) < 2 )
			$str = "0".$str;

		return $str;
	}

	function extendYearStr( $year )
	//
	// Internal function. Extends string containing year to full format (four signs)
	//
	//		Parameters:
	//			$year - year
	//
	//		Returns modified string
	//
	{
		$currentYear = date("Y");

		if (strlen($year) == 1)
			$year = substr($currentYear , 0, 3).$year;
			else
				if (strlen($year) == 2)
					$year = substr($currentYear , 0, 2).$year;
				else
					if (strlen($year) == 3)
						$year = substr($currentYear , 0, 1).$year;
		return $year;
	}

	function validateGeneralDateStr($str, &$month, &$day, &$year, $format, $dateDelimiter )
	//
	// Internal function. Analyzes string, containing date, in accordance with format and examines date whether it contains syntax errors and whether it exists.
	//
	//		Parameters:
	//			$str - string containing date
	//			$month - month
	//			$day - day
	//			$year - year
	//			$format - expected date format
	//			$dateDelimiter - delimiter of date format
	//
	//		Returns true, if string with date does not contain any error
	//
	{
		$prevOffset = 0;
		$format = strtolower( $format );

		for ( $i = 0; $i < 3; $i++ ) {
			$str = getNextIntPart( $str, $prevOffset );

			$parts[$i] = getFirstStrInt( $str );
			$prevOffset = strlen( $parts[$i] );

			if ( !$prevOffset )
				return false;
		}

		$s1 = strpos( $format, $dateDelimiter, 0 );
		$s2 = strpos( $format, $dateDelimiter, $s1+1 );
		$types[0] = substr( $format, 0, 1);
		$types[1] = substr( $format, $s1+1, 1);
		$types[2] = substr( $format, $s2+1, 1);

		for ( $i = 0; $i < 3; $i++ ) {
			switch ($types[$i]) {
				case "y" : {
						$year = extendYearStr( $parts[$i] );
						break;
				}
				case "m" : {
						$month = extendNumber( $parts[$i] );
						break;
				}
				case "d" : {
						$day = extendNumber( $parts[$i] );
						break;
				}
			}
		}

		if (!is_numeric($month) || !is_numeric($day) || !is_numeric($year))
			return false;

		return checkdate($month, $day, $year);
	}

	function validateInputDate( $str, &$timestamp, $addTime=true )
	//
	// Validates entered date
	//
	//		Parameters:
	//			$str - string containing date
	//			$timestamp - linux timestamp
	//
	//		Returns true, if string with date does not contain any error and corresponds to the format DATE_DISPLAY_FORMAT
	//
	{
		global $dateDelimiters;

		$delimiter = $dateDelimiters[DATE_DISPLAY_FORMAT];

		if ( validateGeneralDateStr( $str, $month, $day, $year, DATE_DISPLAY_FORMAT, $delimiter ) ) {

                        if ( $addTime )
			 	$timestamp = strtotime( sprintf( "%s-%s-%s %s", $year, $month, $day, date( "H:i", time() ) ) );
			else
			 	$timestamp = strtotime( sprintf( "%s-%s-%s 00:00", $year, $month, $day ) );

			return true;
		}

		return false;
	}

	function validateInputDateNT( $str, &$dateObj )
	//
	// Validates entered date. No timestamp version.
	//
	//		Parameters:
	//			$str - string containing date
	//			$dateObj - PEAR Date package Date object
	//
	//		Returns true, if string with date does not contain any error and corresponds to the format DATE_DISPLAY_FORMAT
	//
	{
		global $dateDelimiters;

		$delimiter = $dateDelimiters[DATE_DISPLAY_FORMAT];

		if ( validateGeneralDateStr( $str, $month, $day, $year, DATE_DISPLAY_FORMAT, $delimiter ) ) {
			$dateObj = new Date( sprintf( "%s-%s-%s", $year, $month, $day ) );

			return true;
		}

		return false;
	}

	function convertToSqlDate( $timestamp, $enableConversion = false )
	//
	// Converts linux timestamp into format of storing date in database (based on constant DATE_SQL_INPUT_FORMAT)
	//
	//		Parameters:
	//			$timestamp - linux timestamp
	//
	//		Returns string containing date in format DATE_SQL_INPUT_FORMAT
	//
	{

		if ( SERVER_TZ && $enableConversion && defined('USER_TIME_ZONE_ID') )
		{
			$dt = new Date();

			$dt->setTZ( new Date_TimeZone( USER_TIME_ZONE_ID, USER_TIME_ZONE_DST ) );
			$dt->setDate($timestamp);

			$dt->convertTZ( new Date_TimeZone( SERVER_TIME_ZONE_ID, SERVER_TIME_ZONE_DST ) );
			$timestamp = $dt->getDate(DATE_FORMAT_UNIXTIME);
		}

		return date( DATE_SQL_INPUT_FORMAT, $timestamp );
	}

	function convertToSqlDateNT( $dateObj, $enableConversion = false )
	//
	// Converts linux timestamp into format of storing date in database. No timestamp version.
	//
	//		Parameters:
	//			$dateObj - PEAR Date package Date object
	//
	//		Returns string containing date SQL format
	//
	{
		if ( SERVER_TZ && $enableConversion && defined('USER_TIME_ZONE_ID') )
		{
			$dateObj->setTZ( new Date_TimeZone( USER_TIME_ZONE_ID, USER_TIME_ZONE_DST ) );
			$dateObj->convertTZ( new Date_TimeZone( SERVER_TIME_ZONE_ID, SERVER_TIME_ZONE_DST ) );
		}

		return sprintf( "%s-%02d-%02d", $dateObj->getYear(), $dateObj->getMonth(), $dateObj->getDay() );
	}

	function convertToSqlDateTime( $timestamp, $enableConversion = false )
	//
	// Converts linux timestamp into format of storing date and time in database
	//		(based on constants DATE_SQL_INPUT_FORMAT, TIME_SQL_INPUT_FORMAT)
	//
	//		Parameters:
	//			$timestamp - linux timestamp
	//
	//		Returns string in format DATE_SQL_INPUT_FORMAT TIME_SQL_INPUT_FORMAT
	//
	{
		if ( SERVER_TZ && $enableConversion && defined('USER_TIME_ZONE_ID') )
		{
			$dt = new Date();

			$dt->setTZ( new Date_TimeZone( USER_TIME_ZONE_ID, USER_TIME_ZONE_DST ) );
			$dt->setDate($timestamp);

			$dt->convertTZ( new Date_TimeZone( SERVER_TIME_ZONE_ID, SERVER_TIME_ZONE_DST ) );
			$timestamp = $dt->getDate(DATE_FORMAT_UNIXTIME);
		}

		return sprintf( "%s %s", date( DATE_SQL_INPUT_FORMAT, $timestamp ), date( TIME_SQL_INPUT_FORMAT, $timestamp ) );
	}

	function convertToDisplayDate( $str, $enableConversion = false )
	//
	// Converts date obtained from DBMS into date of user interface, based on constant DATE_DISPLAY_FORMAT
	//
	//		Parameters:
	//			$str - string containing date
	//
	//		Returns string containing date in format DATE_DISPLAY_FORMAT
	//
	{
		if ( validateGeneralDateStr( $str, $month, $day, $year, DATE_SQL_OUTPUT_FORMAT, DATE_SQL_OUTPUT_DELIMITER ) )
		{
			$timestamp = strtotime( sprintf( "%s-%s-%s", $year, $month, $day ) );


			if ( SERVER_TZ && $enableConversion && defined('USER_TIME_ZONE_ID') )
			{
				$dt = new Date();

				$dt->setTZ(new Date_TimeZone( SERVER_TIME_ZONE_ID, SERVER_TIME_ZONE_DST ) );
				$dt->setDate($timestamp);

				$dt->convertTZ(  new Date_TimeZone( USER_TIME_ZONE_ID, USER_TIME_ZONE_DST ) );
				$timestamp = $dt->getDate(DATE_FORMAT_UNIXTIME);
			}

			return date( DATE_DISPLAY_FORMAT, $timestamp );
		}

		return null;
	}
	
	function getUserDateString ($timestamp, $kernelStrings, $addTime = false) {
		$month = date( "M", $timestamp );
		$monthname = $kernelStrings["app_mon" . strtolower($month) . "short_name"];
		$timeStr = ($addTime) ? ", " . date("H:i", $timestamp) : "";
		
		if (DATE_DISPLAY_FORMAT == DATEFORMAT_RUSSIAN)
			$result = sprintf ("%d %s %d" . $timeStr, date("d", $timestamp), $monthname, date("Y", $timestamp));
		else
			$result = sprintf ("%s %d, %d" . $timeStr, $monthname, date("d", $timestamp), date("Y", $timestamp));
		return $result;
	}
	
	
	function convertToUserFriendlyDate ($str, $kernelStrings, $enableConversion = false, $isTime = false, $agoFormat = false)
	//
	// Converts date obtained from DBMS into user-friendly date of user interface, based on kernelStrings and DATE_DISPLAY_FORMAT
	//
	//		Parameters:
	//			$str - string containing date
	//
	//		Returns string containing date in format DATE_DISPLAY_FORMAT
	//
	{
		$timestamp = sqlTimestamp($str, $isTime);
		//if ( !validateGeneralDateStr( $str, $month, $day, $year, DATE_SQL_OUTPUT_FORMAT, DATE_SQL_OUTPUT_DELIMITER ) )
			//return null;
		
		//$timestamp = strtotime( sprintf( "%s-%s-%s", $year, $month, $day ) );
		$now = mktime ();
		
		if ( SERVER_TZ && $enableConversion && defined('USER_TIME_ZONE_ID') )
		{
			$timestamp = convertTimestamp2Local ($timestamp);
			$now = convertTimestamp2Local ($now);
		}
		
		$oneday = 60 * 60 * 24;
		$fullseconds = $now - $timestamp;
		
		
		if ($timestamp > $now) {
			$result = getUserDateString ($timestamp, $kernelStrings);
		} elseif ($isTime && $fullseconds < 60 * 5) {
			$result = $kernelStrings["app_justnow_text"];
			return $result;
		} elseif ($isTime && $fullseconds < 60 * 60) {
			$result = sprintf ($kernelStrings["app_minutesago_text"], round(($fullseconds) / 60));
			return $result;
		} elseif ($agoFormat && $isTime) {
			
			$minutes = round(($fullseconds / 60) % 60);
			$hours = round(($fullseconds / (60*60)) % 24);
			$days = round(($fullseconds / (60*60*24)) % 31);
			$months = round(($fullseconds / (60*60*24*31)) % 12);
			$years = round(($fullseconds / (60*60*24*31*12)));
			
			if ($fullseconds < 60 * 60 * 24) {
				return  sprintf ($kernelStrings["app_hoursminutesago_text"], $hours, $minutes );
			} elseif ($fullseconds < 60 * 60 * 24 * 7)  {
				return sprintf ($kernelStrings["app_dayshoursago_text"], $days, $hours);
			} elseif ($fullseconds < 60 * 60 * 24 * 31) {
				return sprintf ($kernelStrings["app_daysago_text"], $days);
			}	elseif ($fullseconds < 60 * 60 * 24 * 365) {
				return sprintf ($kernelStrings["app_monthsdaysago_text"], $months, $days);
			}	else {
				$yearDays = round(($fullseconds / (60*60*24)) % 365);
				return sprintf ($kernelStrings["app_yearsdaysago_text"], $years, $yearDays);
			}			
		} else {
			if(date("ymd", $now) == date("ymd", $timestamp)) {
				$result = $kernelStrings["app_today_text"];
			} elseif (date("ymd", $now-$oneday) == date("ymd", $timestamp)) {
				$result = $kernelStrings["app_yesterday_text"];
			} else {
				$result = getUserDateString ($timestamp, $kernelStrings);
			}
		}
		
		
		if ($isTime)
			$result.= ", " . date("H:i", $timestamp);
		return $result;
	}
	
	
	function convertToUserFriendlyDateTime ($str, $kernelStrings, $enableConversion = false, $agoFormat = false)
	//
	// Converts datetime obtained from DBMS into user-friendly datetime of user interface, based on kernelStrings and DATE_DISPLAY_FORMAT
	//
	//		Parameters:
	//			$str - string containing date
	//
	//		Returns string containing date in format DATE_DISPLAY_FORMAT
	//
	{
		return convertToUserFriendlyDate ($str, $kernelStrings, $enableConvresion, true, $agoFormat);
	}
	
	

	function makeNTDateFormat( $format )
	//
	// Make date format string for no timestamp date functions
	//
	//		Parameters:
	//			$format - date format
	//
	//		Returns string
	//
	{
		$format = "%".$format;
		$format = str_replace( "/", "/%", $format );
		$format = str_replace( ".", ".%", $format );

		return $format;
	}

	function convertToDisplayDateNT( $str, $enableConversion=false )
	//
	// Converts date obtained from DBMS into date of user interface, based on constant DATE_DISPLAY_FORMAT. No timestamp version
	//
	//		Parameters:
	//			$str - string containing date
	//
	//		Returns string containing date in format DATE_DISPLAY_FORMAT
	//
	{
		if ( validateGeneralDateStr( $str, $month, $day, $year, DATE_SQL_OUTPUT_FORMAT, DATE_SQL_OUTPUT_DELIMITER ) ) {
			$dateObj = new Date( sprintf( "%s-%s-%s", $year, $month, $day ) );

			if ( SERVER_TZ && $enableConversion && defined('USER_TIME_ZONE_ID') )
			{
				$dateObj->setTZ( new Date_TimeZone( USER_TIME_ZONE_ID, USER_TIME_ZONE_DST ) );
				$dateObj->convertTZ( new Date_TimeZone( SERVER_TIME_ZONE_ID, SERVER_TIME_ZONE_DST ) );
			}

			return $dateObj->format( makeNTDateFormat(DATE_DISPLAY_FORMAT) );
		}

		return null;
	}

	function sqlTimestamp( $str, $addTime = false )
	//
	// Returns Linux timestamp of date obtained from DBMS
	//
	//		Parameters:
	//			$str - string containing date
	//			$addTime - add time information to the result
	//
	//		Returns string, or null, if error of date modification occured
	//
	{
		if ( validateGeneralDateStr( $str, $month, $day, $year, DATE_SQL_OUTPUT_FORMAT, DATE_SQL_OUTPUT_DELIMITER ) ) {

			$timePart = null;

			if ( $addTime ) {
				preg_match( "/[0-9]*:[0-9]*/iu", $str, $timeParts );

				if ( count($timeParts) )
					$timePart = " ".$timeParts[0];
			}

			return strtotime( sprintf( "%s-%s-%s%s", $year, $month, $day, $timePart ) );
		}

		return null;
	}

	function convertToDisplayDateTime( $str, $showSeconds = false, $showDate = true, $enableConversion = false )
	//
	// Converts date and time obtained from DBMS into date and time of user interface, based on constant DATE_DISPLAY_FORMAT
	//
	//		Parameters:
	//			$str - string containing date and time
	//			$showSeconds - if it is true, time with seconds is returned
	//			$showDate - include date part
	//
	//		Returns string containing date in format DATE_DISPLAY_FORMAT
	//
	{

		if ( SERVER_TZ && $enableConversion && defined('USER_TIME_ZONE_ID') )
		{

			$dt = new Date();

			$dt->setTZ( new Date_TimeZone( SERVER_TIME_ZONE_ID, SERVER_TIME_ZONE_DST ) );
			$dt->setDate($str);

			$dt->convertTZ( new Date_TimeZone( USER_TIME_ZONE_ID, USER_TIME_ZONE_DST ) );
			$str = $dt->getDate(DATE_FORMAT_ISO);
		}

		$dateStr = convertToDisplayDate( $str );

		if ( is_null($dateStr) )
			return $dateStr;

		$timeOffset = 8;
		$timeLen = ($showSeconds) ? 8 : 5;

		if ( $showDate )
			return sprintf( "%s %s", $dateStr, substr($str, strlen($str)-$timeOffset, $timeLen ) );
		else
			return substr($str, strlen($str)-$timeOffset, $timeLen );
	}


	function convertTimestamp2Local( $timestamp, $timezoneId = null, $timezoneDst = null)
	{
		if ($timezoneId == null && defined('USER_TIME_ZONE_ID')) {
			$timezoneId = USER_TIME_ZONE_ID;
			$timezoneDst = USER_TIME_ZONE_DST;
		}						
		if ( SERVER_TZ && $timezoneId)
		{
			$dt = new Date();

			$dt->setTZ( new Date_TimeZone( SERVER_TIME_ZONE_ID, SERVER_TIME_ZONE_DST ) );
			$dt->setDate($timestamp);

			$dt->convertTZ( new Date_TimeZone( $timezoneId, $timezoneDst ) );
			$timestamp = $dt->getDate(DATE_FORMAT_UNIXTIME);
		}

		return $timestamp;
	}

	function displayDate( $timestamp )
	//
	// Returns date in format of input/output of interface dates
	//
	//		Parameters:
	//			$timestamp - date
	//
	//		Returns string containing date in format DATE_DISPLAY_FORMAT
	//
	{
		return date( DATE_DISPLAY_FORMAT, $timestamp );
	}

	function displayDateTime( $timestamp, $showSeconds = false )
	//
	// Returns date and time in format of input/output of interface dates
	//
	//		Parameters:
	//			$timestamp - date
	//			$showSeconds - if it is true, time with seconds is returned
	//
	//		Returns string containing date in format DATE_DISPLAY_FORMAT
	//
	{
		$format = ( $showSeconds ) ? sprintf( "%s H:i:s", DATE_DISPLAY_FORMAT ) : sprintf( "%s H:i", DATE_DISPLAY_FORMAT );

		return date( $format, $timestamp );
	}

	function textualDate( $date, $format, $kernelStrings )
	//
	// Returns date in text representation
	//
	//		Parameters:
	//			$date - date as timestamp
	//			$format - date format (on of DATEFORMAT_... constant values)
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns string
	//
	{
		global $monthFullNames;
		$result = null;

		$dateData = getdate( $date );

		switch ( $format ) {
			case DATEFORMAT_DMY :
								$monthName = $kernelStrings[$monthFullNames[$dateData['mon']-1]];
								return sprintf( "%s %s %s", $dateData['mday'], $monthName, $dateData['year'] );
		}

		return $result;
	}

	//
	// Form handling functions
	//

	function trimArrayData( $array )
	//
	// Applies function trim() to each element of associative array
	//
	//		Parameters:
	//			$array - an array
	//			$symbols - string, list of symbols
	//
	//		Returns associative array
	//
	{
		if ( !is_array($array) ) {
			return array();
		}

		foreach ($array as $key => $val) {
		    if (!is_array($val) && !is_null($val)) {
		        $array[$key] = trim($val);
		    }
		}

		return $array;
	}

	function findEmptyField( $inputArr, $indexNames, $excludeFields = null, $addTabID = null )
	//
	// Checks if blank elements exist in associative array
	//
	//		Parameters:
	//			$inputArr - an array
	//			$indexNames - array of indexes
	//			$excludeFields - fields to exclude from examination process
	//			$addTabID - add tab name to the invalid field name: FIELD|TAB
	//
	//		Returns null, or PEAR_Error with parameters:
	//			code = ERRCODE_EMPTYFIELD
	//			userinfo = index of empty field
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		if ( is_null($excludeFields) )
			$excludeFields = array();

		foreach( $indexNames as $value )
			if ( !in_array($value, $excludeFields) )
				if ( !isset($inputArr[$value]) || !strlen($inputArr[$value]) ) {
					$fieldName = $value;
					if ( strlen($addTabID) )
						$fieldName = sprintf( "%s|%s", $fieldName, $addTabID );

					return PEAR::raiseError ( "Empty field found", ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
												$_PEAR_default_error_options, $fieldName );
				}
		return null;
	}

	function checkIntegerFields( $inputArr, $indexNames, $kernelStrings, $float = false )
	//
	// Analyzes integer fields in array
	//
	//		Parameters:
	//			$inputArr - an array
	//			$indexNames - array of indexes
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null, or PEAR_Error with parameters:
	//			code = ERRCODE_INVALIDFIELD
	//			userinfo = index of field containing an error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		foreach( $indexNames as $value ) {
			$fieldValue = $inputArr[$value];
			if ( !strlen($fieldValue) )
				continue;

			if ($float) {
				if ( !isFloatStr($fieldValue) )
					return PEAR::raiseError ( sprintf($kernelStrings[ERR_INVALIDNUMFORMAT], $fieldValue), ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
													$_PEAR_default_error_options, $value);
			} else {
				if ( !isIntStr($fieldValue) )
					return PEAR::raiseError ( sprintf($kernelStrings[ERR_INVALIDNUMFORMAT], $fieldValue), ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
													$_PEAR_default_error_options, $value);
			}
		}

		return null;
	}

	function rescueElement( $array, $index, $value )
	//
	// Creates in array an element with index $index and value $value, if such element does not exist, or equals to NULL.
	//		Recommended to handle values of checkboxes, if they are not checked
	//
	//		Parameters:
	//			$array - an array
	//			$index - index
	//			$value - value
	//
	//		Returns an array
	//
	{
		if ( !isset($array[$index]) || is_null($array[$index]) )
			$array[$index] = $value;

		return $array;
	}

	function checkDateFields( $inputArr, $indexNames, &$sqlDatesArray, $enableConversion = false )
	//
	// Checks dates in array
	//
	//		Parameters:
	//			$inputArr - an array
	//			$indexNames - array of indexes
	//			$sqlDatesArray - an array, to which fields containing dates converted to SQL are added
	//
	//		Returns null, or PEAR_Error with parameters:
	//			code = ERRCODE_INVALIDDATE
	//			userinfo = index of field containing an error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		$sqlDatesArray = $inputArr;

		foreach( $indexNames as $value ) {
			if ( array_key_exists($value, $inputArr) )
				if ( strlen($inputArr[$value]) ) {
					if ( !validateInputDate($inputArr[$value], $timestamp) )
						return PEAR::raiseError ( "Invalid date field found", ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
													$_PEAR_default_error_options, $value);
					else
						$sqlDatesArray[$value] = convertToSqlDate( $timestamp, $enableConversion );
				} else
					$sqlDatesArray[$value] = null;
		}

		return null;
	}

	function checkDateFieldsNT( $inputArr, $indexNames, &$sqlDatesArray, $enableConversion = false )
	//
	// Checks dates in array. No timestamp version
	//
	//		Parameters:
	//			$inputArr - an array
	//			$indexNames - array of indexes
	//			$sqlDatesArray - an array, to which fields containing dates converted to SQL are added
	//
	//		Returns null, or PEAR_Error with parameters:
	//			code = ERRCODE_INVALIDDATE
	//			userinfo = index of field containing an error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		$sqlDatesArray = $inputArr;

		foreach( $indexNames as $value )
			if ( array_key_exists($value, $inputArr) ) {
				if ( strlen($inputArr[$value]) ) {
					if ( !validateInputDateNT($inputArr[$value], $dateObj) )
						return PEAR::raiseError ( "Invalid date field found", ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
													$_PEAR_default_error_options, $value);
					else
						$sqlDatesArray[$value] = convertToSqlDateNT( $dateObj, $enableConversion );
				} else
					$sqlDatesArray[$value] = null;
			}

		return null;
	}

	function checkStringLengths( $inputArr, $indexNames, $lengths )
	//
	// Checks lengths of array fields
	//
	//		Parameters:
	//			$inputArr - an array
	//			$indexNames - array of indexes
	//			$lenths - an array containing maximum lengths of fields
	//
	//		Returns null, or PEAR_Error with parameters:
	//			code = ERRCODE_INVALIDLENGTH
	//			userinfo = index of field containing an error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		for ( $i = 0; $i < count($indexNames); $i++ )
			if ( array_key_exists($indexNames[$i], $inputArr) && strlen($inputArr[$indexNames[$i]]) > $lengths[$i] )
				return PEAR::raiseError ( "Invalid field length", ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
											$_PEAR_default_error_options, $indexNames[$i]);

		return null;
	}

	function checkFieldInvalidSymbols( $inputArr, $indexNames, $symbols, $excludeIndexList = null )
	//
	// Checks strings for invalid symbols
	//
	//		Parameters:
	//			$inputArr - an array
	//			$indexNames - array of indexes
	//			$symbols - string containing allowed strings
	//			$excludeIndexList - array of indexes to exclude from character checking
	//
	//		Returns null, or PEAR_Error with parameters:
	//			code = ERRCODE_INVALIDLENGTH
	//			userinfo = index of field containing an error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		for ( $i = 0; $i < count($indexNames); $i++ )
			if ( is_null($excludeIndexList) || !in_array( $indexNames[$i], $excludeIndexList ) )
				if ( array_key_exists($indexNames[$i], $inputArr) && !checkIDSymbols($inputArr[$indexNames[$i]], $symbols) )
					return PEAR::raiseError ( "Invalid symbols", ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
												$_PEAR_default_error_options, $indexNames[$i]);

		return null;
	}

	//
	// Kernel initialization scripts
	//

	function systemInit()
	//
	// Performs system initialization
	//
	{
		global $databaseInfo;
		global $silentMode;
		global $wbsEventTable;
		global $wbsQuizList;
		global $global_screens;
		global $global_applications;
		global $global_notifications;
		global $init_required;
		global $host_applications;
		global $dateFormat;
		global $dbLimit;
		global $fileLimit;
		global $readOnly;
		global $phpDateFormats;
		global $DB_KEY;
		global $wbs_memoryLimit;
		global $appListToLoad;
		global $wbs_languages;
		
		$silentMode = false;

		PEAR::setErrorHandling( PEAR_ERROR_CALLBACK, 'handlePEARError' );
		set_error_handler("log_error");

		//
		// Global lists
		//

		$wbsEventTable = array();
		$wbsQuizList = array();
		$global_screens = array();
		$global_applications = array();
		$global_notifications = array();

		if ( !( isset($init_required) && !$init_required ) ) {
			if ( PEAR::isError($databaseIsAvailable = databaseIsAvailable( DB_NAME )) ){
				die($databaseIsAvailable->getMessage());
			
			}elseif($databaseIsAvailable){
				if ( PEAR::isError( db_connect() ) )
					die( "Unable to connect to database ".DB_NAME );
			}

			define( "WBS_ATTACHMENTS_DIR", sprintf( "%s/%s/attachments", WBS_DATA_DIR, $DB_KEY ) );
			define( "WBS_PUBLIC_ATTACHMENTS_DIR", sprintf( "%s/%s/attachments", WBS_PUBLICDATA_DIR, $DB_KEY ) );

			// Host applications table
			//
			if ( is_null($appListToLoad) )
				$host_applications = getHostApplications();
			else
				$host_applications = $appListToLoad;

			if ( PEAR::isError($host_applications) )
				die( "Unable load host application list" );
			
			if (file_exists("./../scripts/_app.php"))
				include_once("./../scripts/_app.php");
			$host_applications = array_unique(array_merge( $host_applications, array( MYWEBASYST_APP_ID, AA_APP_ID, WIDGETS_APP_ID, UG_APP_ID) ));

			// System information registering
			//
			foreach ( $host_applications as $APP_ID )
				if ( !performAppRegistration( $APP_ID ) )
					die( "Error registering applications ({$APP_ID})" );

			$dateFormat = $databaseInfo[HOST_DBSETTINGS][HOST_DATE_FORMAT];
			define( "DATE_DISPLAY_FORMAT", $phpDateFormats[$dateFormat] );

			$dbLimit = getApplicationResourceLimits( AA_APP_ID, 'SPACE' ); /*$databaseInfo[HOST_DBSETTINGS][HOST_DBSIZE_LIMIT];*/
			if ( !strlen($dbLimit) )
				$dbLimit = 0;

			if ( !defined("DATABASE_SIZE_LIMIT") )
				define( "DATABASE_SIZE_LIMIT", $dbLimit );

			/*if ( !isset( $databaseInfo[HOST_DBSETTINGS][HOST_MAXUSERCOUNT] ) ||
					!strlen($databaseInfo[HOST_DBSETTINGS][HOST_MAXUSERCOUNT]) )
				$maxUserCount = 0;
			else*/

			$maxUserCount = getApplicationResourceLimits( AA_APP_ID, 'USERS' ); /*$databaseInfo[HOST_DBSETTINGS][HOST_MAXUSERCOUNT]; */
			
			define( "MAX_USER_COUNT", $maxUserCount );

			if ( !isset( $databaseInfo[HOST_DBSETTINGS][HOST_RECIPIENTSLIMIT] ) ||
					!strlen($databaseInfo[HOST_DBSETTINGS][HOST_RECIPIENTSLIMIT]) )
				$recipientsLimit = null;
			else
			{
				$recipientsLimit = $databaseInfo[HOST_DBSETTINGS][HOST_RECIPIENTSLIMIT];
				if ( $recipientsLimit == PLAN_FREE )
					$recipientsLimit = $maxUserCount;
			}

			define( "MAX_RECIPIENT_NUM", $recipientsLimit );

			if ( !isset( $databaseInfo[HOST_DBSETTINGS][HOST_SMS_RECIPIENTSLIMIT] ) ||
					!strlen($databaseInfo[HOST_DBSETTINGS][HOST_SMS_RECIPIENTSLIMIT]) )
				$smsRecipientsLimit = null;
			else
			{
				$smsRecipientsLimit = $databaseInfo[HOST_DBSETTINGS][HOST_SMS_RECIPIENTSLIMIT];
				if ( $smsRecipientsLimit == PLAN_FREE )
					$smsRecipientsLimit = $maxUserCount;
			}
	 
			define( "MAX_SMS_RECIPIENT_NUM", $smsRecipientsLimit );
			
			if ( !isset( $databaseInfo[HOST_DBSETTINGS][HOST_DEFAULTENCODING] ) ||
					!strlen($databaseInfo[HOST_DBSETTINGS][HOST_DEFAULTENCODING]) )
				$defaultEncoding = null;
			else
				$defaultEncoding = $databaseInfo[HOST_DBSETTINGS][HOST_DEFAULTENCODING];
			
			if ( !is_null($defaultEncoding) )
				define( "DEFAULT_ENCODING", $defaultEncoding );
			else {
				
				$defaultEncoding = $wbs_languages[LANG_ENG][WBS_ENCODING];
				define( "DEFAULT_ENCODING", $defaultEncoding );
			}

			$readOnly = $databaseInfo[HOST_DBSETTINGS][HOST_READONLY];
			if ( $readOnly ) {
				define( "READ_ONLY", true );
				define( "ERR_QUERYEXECUTING", 'app_readonly_message' );
				$silentMode = true;
			} else {
				define( "READ_ONLY", false );
				define( "ERR_QUERYEXECUTING", 'app_queryerr_message' );
			}
		} else
				define( "ERR_QUERYEXECUTING", 'app_queryerr_message' );

		register_shutdown_function( 'systemShutdown' );

		// Set memory limit
		//
		ini_set( 'memory_limit', $wbs_memoryLimit."M" );
	}

	function systemShutdown()
	//
	// System shutdown function. Closes mySQL connection
	//
	{
		global $wbs_database;
		global $silentMode;

		$silentMode = true;

		if ( is_object($wbs_database) )
			@$wbs_database->disconnect();
	}

	function sliceLocalizaionArray( $arr, $index )
	//
	// Returns localization array containing only strings with given index
	//
	//		Parameters:
	//			$arr - array of strings
	//			$index - index
	//
	//		Returns sliced array
	//
	{
		if (!$arr)
			return null;
		$langList = array_keys( $arr );

		$result = array();
		foreach( $langList as $lang_id )
		{
			if ( isset( $arr[$lang_id][$index] ) )
			$result[$lang_id] = $arr[$lang_id][$index];
		}

		return $result;
	}

	function getAppLocalizationVarName( $APP_ID )
	//
	//	Returns application localization variable name
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//
	//		Returns string
	//
	{
		return ($APP_ID == AA_APP_ID) ? "loc_str" : sprintf("%s_loc_str", strtolower($APP_ID));
	}

	function &getApplicationLocalizationStrings( $APP_ID )
	//
	// Returns application localization strings
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//
	//		Returns array
	//
	{
		$varName = getAppLocalizationVarName( $APP_ID );
		$result = null;
		eval( "global \$$varName; \$result=\$$varName;" );

		return $result;
	}

	function performAppRegistration( $APP_ID )
	//
	// Loads application registration file and performs application registration
	//		Also loads application localization strings
	//
	//		Parameters:
	//			$APP_ID - application identifer
	//
	//		Returns boolean
	//
	{
		$filePath = WBS_PUBLISHED_DIR."/$APP_ID/".APP_REGISTER_FILE;

		$appInfo = loadApplicationRegisterData( $filePath );
		if ( !is_array($appInfo) )
			return false;
		
		$appStrings = null;
		$currentApps = (defined("CURRENT_APP")) ? split(",", CURRENT_APP) : array ();
		if ($APP_ID == AA_APP_ID || !$currentApps || in_array($APP_ID, $currentApps) ) {
			$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $APP_ID );
			$appStrings = loadLocalizationStrings( $localizationPath, strtolower($APP_ID) );
		}

		$globalVarName = getAppLocalizationVarName( $APP_ID );
		eval( "global \$$globalVarName; \$$globalVarName = \$appStrings;" );

		//
		// Register application data
		//

		$globalVarName = $APP_ID."_APP_ID";
		$curAPP_ID = $appInfo[APP_REG_APPLICATION][APP_REG_APP_ID];
		eval( "global \$$globalVarName; \$$globalVarName = \"$curAPP_ID\";" );

		$app_name = sliceLocalizaionArray( $appStrings, $appInfo[APP_REG_APPLICATION][APP_REG_NAME] );
		$app_ui_name = sliceLocalizaionArray( $appStrings, $appInfo[APP_REG_APPLICATION][APP_REG_UI_NAME] );
		$app_parents = $appInfo[APP_REG_PARENTS];
		$app_sortorder = $appInfo[APP_REG_APPLICATION][APP_REG_APP_SORTORDER];

		if ( isset( $appInfo[APP_REG_USERRIGHTS][APP_REG_TREEDOCUMENT]) && $appInfo[APP_REG_USERRIGHTS][APP_REG_TREEDOCUMENT][APP_REG_RIGHTS] != null )
		{
			$regRights = $appInfo[APP_REG_USERRIGHTS][APP_REG_TREEDOCUMENT][APP_REG_RIGHTS];

			$regRights[APP_REG_RIGHTS_ROOT] = sliceLocalizaionArray( $appStrings, $regRights[APP_REG_RIGHTS_ROOT] );
			$regRights[APP_REG_RIGHTS_ACCESS] = sliceLocalizaionArray( $appStrings, $regRights[APP_REG_RIGHTS_ACCESS] );
			$regRights[APP_REG_RIGHTS_ITEM] = sliceLocalizaionArray( $appStrings, $regRights[APP_REG_RIGHTS_ITEM] );
			$regRights[APP_REG_RIGHTS_ITEMNAME] = sliceLocalizaionArray( $appStrings, $regRights[APP_REG_RIGHTS_ITEMNAME] );
			$regRights[APP_REG_RIGHTS_READ] = sliceLocalizaionArray( $appStrings, $regRights[APP_REG_RIGHTS_READ] );
			$regRights[APP_REG_RIGHTS_WRITE] = sliceLocalizaionArray( $appStrings, $regRights[APP_REG_RIGHTS_WRITE] );
			$regRights[APP_REG_RIGHTS_FOLDER] = sliceLocalizaionArray( $appStrings, $regRights[APP_REG_RIGHTS_FOLDER] );

			$appInfo[APP_REG_USERRIGHTS][APP_REG_TREEDOCUMENT][APP_REG_RIGHTS] = $regRights;
		}
//		else
//			unset( $appInfo[APP_REG_USERRIGHTS][APP_REG_TREEDOCUMENT][APP_REG_RIGHTS] );

		$quotable = false;

		if ( isset($appInfo[APP_REG_APPLICATION][APP_REG_APP_QUOTABLE]) )
			$quotable = $appInfo[APP_REG_APPLICATION][APP_REG_APP_QUOTABLE];

		if ( isset($appInfo[APP_REG_APPLICATION][APP_REG_DEFAULT_LANGUAGE]) )
			$defLang = $appInfo[APP_REG_APPLICATION][APP_REG_DEFAULT_LANGUAGE];
		else
			$defLang = null;
		
		registerApplication( $curAPP_ID, array( APP_NAME=>$app_name,
												APP_UI_NAME=>$app_ui_name,
												APP_QUOTABLE=>$quotable,
												APP_PARENTS=>$app_parents,
												APP_SORTORDER=>$app_sortorder,
												APP_REG_DEFAULT_LANGUAGE=>$defLang,
												APP_REG_ROBOTS=>$appInfo[APP_REG_ROBOTS],
												APP_REG_USERRIGHTS=>$appInfo[APP_REG_USERRIGHTS] ) );

		foreach( $appInfo[APP_REG_EVENT_HANDLERS] as $HANLDER_DATA ) {
			$handlerApp = $HANLDER_DATA[APP_REG_APP_ID];
			$handlerName = $HANLDER_DATA[APP_REG_NAME];
			$handlerProc = $HANLDER_DATA[APP_REG_HANDLER_PROC];
			$handlerScript = $HANLDER_DATA[APP_REG_HANLDER_SCRIPT];

			registerEventHandler( $handlerApp, $handlerName, $curAPP_ID, $handlerProc, $handlerScript );
		}

		foreach( $appInfo[APP_REG_MAIL_NOTIFICATIONS] as $NOTIFICATION_INDEX=>$NOTIFICATION_DATA ) {
			$notification_id = $NOTIFICATION_DATA[APP_REG_ID];
			$notification_name = sliceLocalizaionArray( $appStrings, $NOTIFICATION_DATA[APP_REG_NAME] );

			registerMailNotification( $curAPP_ID, $notification_id, array( MN_NAME=>$notification_name ) );
		}

		foreach( $appInfo[APP_REG_EVENTS] as $EVENT_DATA ) {
			$event_id = $EVENT_DATA[APP_REG_ID];

			registerApplicationEvent( $curAPP_ID, $event_id );
		}

		return true;
	}

	function listPublishedApplications( $language, $allowPrivate = false )
	//
	// Returns array containing registration information of applications stored in "published" folder
	//		Extends application data with dependent applications list
	//
	//		Parameters:
	//			$language - language for application name
	//
	//		Returns array of applications: array( APP_ID1=>APP_DATA1 ),
	//			or false in case of error
	//
	{
		$result = array();

		$targetDir = WBS_PUBLISHED_DIR;

		if ( !($handle = opendir($targetDir)) )
			return false;

		while ( false !== ($name = readdir($handle)) ) {
			if ( $name != "." && $name != ".." ) {
				$dirname = $targetDir.'/'.$name;

				if ( !is_dir($dirname) || strlen($name) != 2 || $name == AA_APP_ID || $name == MYWEBASYST_APP_ID || $name == WIDGETS_APP_ID || $name == UG_APP_ID)
					continue;

				$regFilePath = sprintf( "%s/%s", $dirname, APP_REGISTER_FILE );

				if ( !file_exists($regFilePath) )
					continue;

				$appData = loadApplicationRegisterData( $regFilePath );

				if ( !is_array($appData) )
					return false;

				if ( !$allowPrivate && isset( $appData[APP_REG_APPLICATION][APP_REG_PRIVATE] ) && $appData[APP_REG_APPLICATION][APP_REG_PRIVATE] )
					continue;

				$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $name );
				$appStrings = loadLocalizationStrings( $localizationPath, strtolower($name) );

				$app_name = sliceLocalizaionArray( $appStrings, $appData[APP_REG_APPLICATION][APP_REG_NAME] );
				$app_ui_name = sliceLocalizaionArray( $appStrings, $appData[APP_REG_APPLICATION][APP_REG_UI_NAME] );

				$appData[APP_REG_APPLICATION][APP_REG_NAME] = $app_name;
				$appData[APP_REG_APPLICATION][APP_REG_UI_NAME] = $app_ui_name;

				$appLang = getApplicationLanguage( $name, $language, $appData[APP_REG_APPLICATION], $appStrings );

				$appData[APP_REG_APPLICATION][APP_REG_LOCAL_NAME] = $app_name[$appLang];
				$appData[APP_REG_APPLICATION][APP_REG_LOCAL_UI_NAME] = $app_ui_name[$appLang];

				$result[$name] = $appData;
			}
		}

		closedir( $handle );

		$app_ids = array_keys( $result );

		for( $i = 0; $i < count( $app_ids ); $i++ ) {
			$APP_ID = $app_ids[$i];
			$curParentApp = $result[$APP_ID];

			$dependences = array();

			for ( $j = 0; $j < count($app_ids); $j++ ) {
				$SUB_APP_ID = $app_ids[$j];
				$curSlaveApp = $result[$SUB_APP_ID];

				if ( $SUB_APP_ID != $APP_ID ) {
					$SUB_Dependences = $curSlaveApp[APP_REG_PARENTS];

					if ( is_array($SUB_Dependences) && in_array($APP_ID, $SUB_Dependences) )
						$dependences[] = $SUB_APP_ID;
				}
			}

			$result[$APP_ID][APP_REG_DEPENDENCES] = $dependences;
		}

		return $result;
	}

	function cmpPublishedApplSortOrders( $appData1, $appData2 )
	//
	// Internal function for published application sorting
	//
	//		Parameters:
	//			$appData1 - information about first application
	//			$appData2 - information about second application
	//
	//		Returns result of comparance
	//
	{
		if ( array_key_exists( APP_REG_APP_SORTORDER, $appData1[APP_REG_APPLICATION] ) )
			$sortOrder1 = $appData1[APP_REG_APPLICATION][APP_REG_APP_SORTORDER];
		else
			$sortOrder1 = 100;

		if ( array_key_exists( APP_REG_APP_SORTORDER, $appData2[APP_REG_APPLICATION] ) )
			$sortOrder2 = $appData2[APP_REG_APPLICATION][APP_REG_APP_SORTORDER];
		else
			$sortOrder2 = 100;

		if ( $sortOrder1 == $sortOrder2 )
			return 0;

		return ($sortOrder1 > $sortOrder2) ? 1 : -1;
	}

	function sortPublishedApplications( $appList )
	//
	// Assorts list of published applications in accordance with order of sorting - APP_SORTORDER
	//
	//		Parameters:
	//			$appList - list of applications obtained from listPublishedApplications() function
	//
	//		Returns sorted array
	//
	{
		if ( !is_array($appList) )
			return array();

		uasort( $appList, "cmpPublishedApplSortOrders" );

		return $appList;
	}

	function fixApplicationDependences( $appData )
	//
	// System composer function. Checks applications "CHECKED" flag and sets it to 1
	//		if there are dependent applications selected without parent
	//
	//		Parameters:
	//			$appData - applications list obtained from listPublishedApplications() function
	//
	//		Returns fixed $appData array
	//
	{
		$app_ids = array_keys( $appData );

		for( $i = 0; $i < count( $app_ids ); $i++ ) {
			$APP_ID = $app_ids[$i];
			$curAppData = $appData[$APP_ID];

			if ( $curAppData[APP_CHECKED] ) {
				for ( $j = 0; $j < count($curAppData[APP_REG_PARENTS]); $j++ ) {
					$parent_APP_ID = $curAppData[APP_REG_PARENTS][$j];

					$parentAppData = $appData[$parent_APP_ID];
					$parentAppData[APP_CHECKED] = 1;
					$appData[$parent_APP_ID] = $parentAppData;
				}
			}
		}

		return $appData;
	}
	
	function setLocalCacheValue ($section, $name, $value) {
		return null;
		$key = md5($section . $name);
		$_SESSION[$key] = serialize($value);
	}
	
	function getLocalCacheValue ($section, $name) {
		return null;
		$key = md5($section . $name);
		if ($_SESSION[$key])
			return unserialize($_SESSION[$key]);
		else
			return null;
	}
	
	
	function setGlobalCacheValue ($section, $name, $value) {
		$key = md5($section . $name);
		$cacheFileName = WBS_TEMP_DIR . "/.cache." . $key;
		$fh = @fopen($cacheFileName, "w");
		if ($fh) {
			fwrite ($fh, serialize($value));
			fclose ($fh);
		}
		//$_SESSION[$key] = serialize($value);
	}
	
	function getGlobalCacheValue ($section, $name) {
		if (defined("NOT_USE_GLOBAL_CACHE"))
			return null;
		$key = md5($section . $name);
		$cacheFileName = WBS_TEMP_DIR . "/.cache." . $key;
		
		if (file_exists($cacheFileName)) {
			return unserialize(file_get_contents($cacheFileName));
		} else
			return null;
		/*if ($_SESSION[$key])
			return unserialize($_SESSION[$key]);
		else
			return null;*/
	}
	
	
	

	function loadApplicationRegisterData( $filePath )
	//
	// Parses application registration xml-file and returns information as array
	//
	//		Parameters:
	//			$filePath - path to application registration file
	//
	//		Returns an array contaning application registration data or false in case of error
	//
	{
		if ($cacheRes = getGlobalCacheValue ("REG_DATA", $filePath)) {
			return $cacheRes;
		}
		
		if ( !file_exists($filePath) )
			return false;

		$dom = domxml_open_file( realpath($filePath) );
		if ( !$dom )
			return false;

		$xpath = xpath_new_context($dom);

		$result = array();
		if ( !($application = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_APPLICATION)) )
			return false;

		if ( !count($application->nodeset) )
			return false;

		$application = $application->nodeset[0];

		$result[APP_REG_APPLICATION] = getAttributeValues( $application );

		$appParents = array();
		if ( $parents = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_APPLICATION."/".APP_REG_PARENTS."/".APP_REG_PARENT) )
			foreach( $parents->nodeset as $parent )
				$appParents[] = $parent->get_attribute(APP_REG_APP_ID);

		$result[APP_REG_PARENTS] = $appParents;

		$eventHandlers = array();
		if ( $handlers = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_APPLICATION."/".APP_REG_EVENT_HANDLERS."/".APP_REG_HANDLER) )
			foreach( $handlers->nodeset as $handler )
				$eventHandlers[] = getAttributeValues( $handler );

		$result[APP_REG_EVENT_HANDLERS] = $eventHandlers;

		$mailNotifications = array();
		if ( $notifications = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_APPLICATION."/".APP_REG_MAIL_NOTIFICATIONS."/".APP_REG_NOTIFICATION) )
			foreach( $notifications->nodeset as $notification )
				$mailNotifications[] = getAttributeValues( $notification );

		$result[APP_REG_MAIL_NOTIFICATIONS] = $mailNotifications;

		$appScreens = array();
		if ( $screens = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_SCREENS."/".APP_REG_SCREEN) )
			foreach( $screens->nodeset as $screen )
				$appScreens[] = getAttributeValues( $screen );

		$result[APP_REG_SCREENS] = $appScreens;

		$appEvents = array();
		if ( $events = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_APPLICATION."/".APP_REG_EVENTS."/".APP_REG_EVENT) )
			foreach( $events->nodeset as $event )
				$appEvents[] = getAttributeValues( $event );

		$result[APP_REG_EVENTS] = $appEvents;

		$appRobots = array();
		if ( $robots = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_ROBOTS."/".APP_REG_ROBOT) )
			foreach( $robots->nodeset as $robot ) {
				$ID = $robot->get_attribute(APP_REG_ID);
				$password = $robot->get_attribute(APP_REG_PASSWORD);
				$services = $robot->get_attribute(APP_REG_SERVICES);
				$services = explode( ",", $services );

				foreach( $services as $index=>$value )
					$services[$index] = trim($value);

				$appRobots[$ID] = array( APP_REG_PASSWORD=>$password, APP_REG_SERVICES=>$services );
			}

		$result[APP_REG_ROBOTS] = $appRobots;

		// Load user rights presented by application
		//
		$userRights = array();
		if ( $rightsNodes = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_USERRIGHTS ) )
			if ( count($rightsNodes->nodeset) ) {
				if ( $treeDocRoghts = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_USERRIGHTS."/".APP_REG_TREEDOCUMENT ) ) {
					if ( $accessDescription = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_USERRIGHTS."/".APP_REG_TREEDOCUMENT."/".APP_REG_ACCESSTABLE ) ) {
						if ( count($accessDescription->nodeset) )
							$accessDescription = getAttributeValues( $accessDescription->nodeset[0] );
					} else
						return false;

					if ( $groupAccessDescription = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_USERRIGHTS."/".APP_REG_TREEDOCUMENT."/".APP_REG_GROUPACCESSTABLE ) ) {
						if ( count($groupAccessDescription->nodeset) )
							$groupAccessDescription = getAttributeValues( $groupAccessDescription->nodeset[0] );
					} else
						return false;

					if ( $folderDescription = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_USERRIGHTS."/".APP_REG_TREEDOCUMENT."/".APP_REG_FOLDERTABLE ) ) {
						if ( count($folderDescription->nodeset) )
							$folderDescription = getAttributeValues( $folderDescription->nodeset[0] );
					} else
						return false;

					if ( $documentDescription = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_USERRIGHTS."/".APP_REG_TREEDOCUMENT."/".APP_REG_DOCUMENTTABLE ) ) {
						if ( count($documentDescription->nodeset) )
							$documentDescription = getAttributeValues( $documentDescription->nodeset[0] );
					} else
						return false;

					if ( $rightsDescription = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_USERRIGHTS."/".APP_REG_TREEDOCUMENT."/".APP_REG_RIGHTS ) )
					{
						if ( count($rightsDescription->nodeset) )
							$rightsDescription = getAttributeValues( $rightsDescription->nodeset[0] );
						else
							$rightsDescription = null;
					} else
						$rightsDescription = null;

					$userRights[APP_REG_TREEDOCUMENT] = array( APP_REG_ACCESSTABLE=>$accessDescription,
																APP_REG_FOLDERTABLE=>$folderDescription,
																APP_REG_DOCUMENTTABLE=>$documentDescription,
																APP_REG_GROUPACCESSTABLE=>$groupAccessDescription,
																APP_REG_RIGHTS=>$rightsDescription
															);
				}

				$auxRights = array();
				if ( $auxRightsNodes = xpath_eval($xpath, "/".APP_REG_WBSAPPLICATION."/".APP_REG_USERRIGHTS."/".APP_REG_AUXRIGHTS."/".APP_REG_RIGHT ) ) {
					if ( count($auxRightsNodes->nodeset) )
						foreach( $auxRightsNodes->nodeset as $right )
							$auxRights[] = getAttributeValues( $right );

				$userRights[APP_REG_AUXRIGHTS] = $auxRights;
			}
		}

		$result[APP_REG_USERRIGHTS] = $userRights;
		
		setGlobalCacheValue ("REG_DATA", $filePath, $result);
		
		return $result;
	}

	function registerAppScreen( $APP_ID, $SCR_ID, $SCR_DATA )
	//
	// Registers application forms in global list
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$SCR_ID - form identifier
	//			$SCR_DATA - information about form
	//
	//		Returns null
	//
	{
		global $global_screens;

		$global_screens[$APP_ID][$SCR_ID] = $SCR_DATA;

		return null;
	}

	function registerApplication( $APP_ID, $APP_DATA )
	//
	// Registers screen in global list
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$SCR_DATA - information about application
	//
	//		Returns null
	//
	{
		global $global_applications;

		$global_applications[$APP_ID] = $APP_DATA;

		return null;
	}

	function listApplicationScreens( $APP_ID )
	//
	// Returns an array containing information about application forms
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//
	//		Returns an array of the following structure:
	//			array( SCR_ID1=>SCR_DATA1, SCR_ID2=>SCR_DATA2... )
	//
	{
		global $global_screens;

		if ( !is_array($global_screens) )
			return $global_screens;

		$result = array();
		foreach( $global_screens[$APP_ID] as $SCR_ID=>$SCR_DATA )
			$result[$SCR_ID] = $SCR_DATA;

		return $result;
	}

	function listGlobalScreens()
	//
	// Returns an array containing information about all screens
	//
	//		Returns an array of the following structure:
	//			array( SCR_ID1=>SCR_DATA1, SCR_ID2=>SCR_DATA2... )
	//
	{
		global $global_screens;
		global $global_applications;

		$result = array();

		foreach( $global_applications as $APP_ID=>$APP_DATA )
			$result = array_merge( $result, listApplicationScreens( $APP_ID ) );

		return $result;
	}

	function cmpScreenData( $scrData1, $scrData2 )
	//
	// Internal function for screen sorting.
	//
	//		Parameters:
	//			$scrData1 - information about first screen
	//			$scrData2 - information about second screen
	//
	//		Returns result of comparance
	//
	{
		global $scrcmpLanguage;

		if ( $scrData1[SCR_NAME][$scrcmpLanguage] == $scrData2[SCR_NAME][$scrcmpLanguage] )
			return 0;

		return ($scrData1[SCR_NAME][$scrcmpLanguage] > $scrData2[SCR_NAME][$scrcmpLanguage]) ? 1 : -1;
	}

	function sortScreenList( $APP_ID, $screenList )
	//
	// Assors list of screens in order of registration
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$screenList - list of screen obtained with the help of the function listApplicationScreens()
	//
	//		Returns assorted list
	//
	{
		$registeredScreens = listApplicationScreens( $APP_ID );

		$result = array();
		foreach( $registeredScreens as $SCR_ID=>$SCR_DATA )
			if ( array_key_exists( $SCR_ID, $screenList ) )
				$result[$SCR_ID] = $SCR_DATA;

		return $result;
	}

	function cmpApplSortOrders( $appData1, $appData2 )
	//
	// Internal function for application sorting.
	//
	//		Parameters:
	//			$appData1 - information about first application
	//			$appData2 - information about second application
	//
	//		Returns result of comparance
	//
	{
		global $global_applications;

		if ( !is_array($global_applications) )
			return 0;

		//if (  array_key_exists( APP_SORTORDER, $global_applications[$appData1] ) )
		if (isset($global_applications[$appData1][APP_SORTORDER]))
			$sortOrder1 = $global_applications[$appData1][APP_SORTORDER];
		else
			$sortOrder1 = 100;

		//if ( array_key_exists( APP_SORTORDER, $global_applications[$appData2] ) )
		if (isset($global_applications[$appData2][APP_SORTORDER]))
			$sortOrder2 = $global_applications[$appData2][APP_SORTORDER];
		else
			$sortOrder2 = 100;

		if ( $sortOrder1 == $sortOrder2 )
			return 0;

		return ($sortOrder1 > $sortOrder2) ? 1 : -1;
	}

	function sortAppScreenList( $screenList )
	//
	// Assorts list of applications and screens. Applications are sorted in accordance with order of sorting - APP_SORTORDER, screens - in order of registration.
	//
	//		Parameters:
	//			$screenList - list of applications and screen, obtained with the help of the function listUserScreens()
	//
	//		Returns assorted list
	//
	{
		if ( !is_array($screenList) )
			return array();

		uksort( $screenList, "cmpApplSortOrders" );

		$result = array();
		foreach( $screenList as $APP_ID=>$APP_SCREENS ) {
			$registeredScreens = listApplicationScreens( $APP_ID );

			foreach( $registeredScreens as $SCR_ID=>$SCR_DATA )
				if ( in_array( $SCR_ID, $APP_SCREENS ) )
					$result[$APP_ID][] = $SCR_ID;

		}

		return $result;
	}

	function sortApplicationList( $appList )
	//
	// Assorts list of applications in accordance with order of sorting - APP_SORTORDER
	//
	//		Parameters:
	//			$appList - list of applications
	//
	//		Returns assorted list
	//
	{
		if ( !is_array($appList) )
			return array();

		uksort( $appList, "cmpApplSortOrders" );

		return $appList;
	}

	function getScreenName( $APP_ID, $SCR_ID, $language )
	//
	// Returns name of form corresponding to identifier
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$SCR_ID - form identifier
	//			$language - user language
	//
	//		Returns form name
	//
	{
		global $global_screens;

		$app_lang = getApplicationLanguage( $APP_ID, $language );

		return $global_screens[$APP_ID][$SCR_ID][SCR_NAME][$app_lang];
	}

	function getScreenDescription( $APP_ID, $SCR_ID, $language )
	//
	// Returns description of form corresponding to identifier
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$SCR_ID - form identifier
	//			$language - user language
	//
	//		Returns form name
	//
	{
		global $global_screens;

		return $global_screens[$APP_ID][$SCR_ID][SCR_DESC][$language];
	}

	function getAppName( $APP_ID, $language, $uiName = false )
	//
	// Returns name of application corresponding to identifier
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$language - user language
	//			$uiName - if it is true, the short application name is returned
	//
	//		Returns name of application
	//
	{
		global $global_applications;

		$app_lang = getApplicationLanguage( $APP_ID, $language );

		if ( !$uiName )
			return $global_applications[$APP_ID][APP_NAME][$app_lang];
		else
			return $global_applications[$APP_ID][APP_UI_NAME][$app_lang];
	}

	function listChildApplications( $APP_ID )
	//
	// Returns list of child applications
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//
	//		Returns an array containing identifier of applications
	//
	{
		global $global_applications;

		$result = array();

		foreach( $global_applications as $key=>$APP_DATA ) {
			if (!is_array($APP_DATA))
				continue;

			if (!array_key_exists(APP_PARENTS, $APP_DATA))
				continue;

			$parents = $APP_DATA[APP_PARENTS];

			if ( !is_array($parents) )
				continue;

			if ( in_array($APP_ID, $parents) )
				$result[] = $key;
		}

		return $result;
	}

	function listUserStartScreens( $U_ID, $language, $kernelStrings )
	//
	// Returns list of screens which can be used as start page
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$language - user language
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array
	//
	{
		$pages = sortAppScreenList( listUserScreens( $U_ID ) );

		$result = array();
		$result[USE_BLANK] = sprintf( "&lt;%s&gt;", $kernelStrings['amu_blank_item'] );

		if ( SHOW_TIPSANDTRICKS )
			$result[USE_TIPSANDTRICKS] = sprintf( "&lt;%s&gt;", $kernelStrings['tt_pagetitle_item'] );

		$result[USE_LAST] = sprintf( "&lt;%s&gt;", $kernelStrings['amu_lastopened_item'] );

		foreach( $pages as $APP_ID=>$appScreens ) {
			$app_name = getAppName( $APP_ID, $language, true );

			for ( $i = 0; $i < count($appScreens); $i++ ) {
				$SCR_ID = $appScreens[$i];
				$pageID = sprintf( "%s/%s", $APP_ID, $SCR_ID );
				$result[$pageID] = sprintf( "%s", $app_name, getScreenName( $APP_ID, $SCR_ID, $language ) );
			}
		}

		return $result;
	}

	function listApplicationScreensAndNotifications( $language, &$kernelStrings )
	//
	// Returns list of application screens and email notifications
	//
	//		Parameters:
	//			$language - user language
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array
	//
	{
		global $global_applications;

		$appScreensList = array();

		$appList = sortApplicationList( $global_applications );
		foreach($appList as $app_id=>$app_data) {
			if ( $app_id == MYWEBASYST_APP_ID )
				continue;

			$appScreens = listApplicationScreens( $app_id );

			foreach($appScreens as $scr_id=>$scr_data )
				$appScreensList[$app_id][SECTION_PAGES][$scr_id] = 0;
		}

		$fullNotificationList = listMailNotifications( $language );
		foreach( $fullNotificationList as $mn_id =>$mn_data )
			$appScreensList[$mn_data[APP_ID]][SECTION_NOTIFICATIONS][$mn_id] = 1;

		return $appScreensList;
	}

	function listApplicationAuxRights( $userAppScreenData, &$kernelStrings, $language )
	//
	// Returns a list of applications auxiliary rights
	//
	//		Parameters:
	//			$userAppScreenData - array with applications screens and email notifications
	//									prepared with listApplicationScreensAndNotifications()
	//									function
	//			$kernelStrings - Kernel localization strings
	//			$language - user language
	//
	//		Returns array
	//
	{
		global $global_applications;

		foreach ( $global_applications as $APP_ID=>$APP_DATA ) {
			if ( $APP_ID == MYWEBASYST_APP_ID )
				continue;

			$appLocStrings = getApplicationLocalizationStrings( $APP_ID );
			$appLocStrings = $appLocStrings[$language];

			$appAuxRights = array();

			if ( isset($APP_DATA[APP_REG_USERRIGHTS]) && isset($APP_DATA[APP_REG_USERRIGHTS][APP_REG_AUXRIGHTS]) ) {
				foreach ( $APP_DATA[APP_REG_USERRIGHTS][APP_REG_AUXRIGHTS] as $right ) {
					$rightRecord = $right;
					$rightRecord[APP_REG_NAME] = $appLocStrings[$right[APP_REG_NAME]];
					$appAuxRights[$right[APP_REG_ID]] = $rightRecord;
				}
			}

			$userAppScreenData[$APP_ID][SECTION_AUXRIGHTS] = $appAuxRights;
		}

		return $userAppScreenData;
	}

	function registerMailNotification( $APP_ID, $notificationID, $notificationData )
	//
	// Registers mail notification
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$notificationID - notification identifier
	//			$notificationData - an associative array containing information about notification
	//
	{
		global $global_notifications;

		$global_notifications[$APP_ID][$notificationID] = $notificationData;
	}

	function getApplicationNotificationList( $APP_ID )
	//
	// Returns list of application registered mail notifications
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//
	//		Returns array( notificationID1 => notificationData1... )
	//
	{
		global $global_notifications;

		$result = array();

		if ( !array_key_exists( $APP_ID, $global_notifications ) )
			return $result;

		return $global_notifications[$APP_ID];
	}

	function getNotificationName( $APP_ID, $notificationID, $language )
	//
	// Returns name of mail notification
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$notificationID - notification identifier
	//			$language - user language
	//
	//		Returns string, or null, if notification is not registered
	//
	{
		global $global_notifications;

		if ( !array_key_exists( $APP_ID, $global_notifications ) )
			return null;

		if ( !array_key_exists( $notificationID, $global_notifications[$APP_ID] ) )
			return null;

		$notificationData = $global_notifications[$APP_ID][$notificationID];

		return $notificationData[MN_NAME][$language];
	}

	function mn_cmp ($a, $b)
	//
	// Compares elements of mail notification list
	//
	//		Parameters:
	//			$a - first element
	//			$b - second element
	//
	//		Returns result of comparance (-1, 0, 1)
	//
	{
		if ($a[MN_NAME] == $b[MN_NAME])
			return 0;

		return ($a[MN_NAME] > $b[MN_NAME]) ? 1 : -1;
	}

	function listMailNotifications( $language )
	//
	// Returns list of mail notifications, assorted by name
	//
	//		Parameters:
	//			$language - user language
	//
	//		Returns array( APP_ID1=>($notificationID1=>notificationName1, ... ), ... )
	//
	{
		global $global_notifications;

		$result = array();

		foreach( $global_notifications as $APP_ID => $APP_DATA )  {
			foreach( $APP_DATA as $MN_ID => $MN_DATA )
				$result[$MN_ID] = array( MN_NAME=>$MN_DATA[MN_NAME][$language], APP_ID=>$APP_ID );
		}

		uasort( $result, "mn_cmp" );

		return $result;
	}

	//
	// Robots and services
	//

	function authorizeServiceAccess( $APP_ID, $robotID, $robotPassword, $serviceName, $kernelStrings )
	//
	// Checks if robot have access to an application service
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$robotID - robot identifier
	//			$robotPassword - robot password
	//			$serviceName - service name
	//			$kernelStrings - kernel localization strings
	//
	//		Returns true or PEAR::Error
	//
	{
		global $global_applications;

		if ( !array_key_exists( $APP_ID, $global_applications ) )
			return PEAR::raiseError( $kernelStrings['app_invrobotlogin_message'], ERRCODE_APPLICATION_ERR );

		$application = $global_applications[$APP_ID];

		$robots = $application[APP_REG_ROBOTS];

		if ( !array_key_exists( $robotID, $robots ) )
			return PEAR::raiseError( $kernelStrings['app_invrobotlogin_message'], ERRCODE_APPLICATION_ERR );

		$robot = $robots[$robotID];

		if ( strtolower($robotPassword) != strtolower($robot[APP_REG_PASSWORD]) )
			return PEAR::raiseError( $kernelStrings['app_invrobotlogin_message'], ERRCODE_APPLICATION_ERR );

		$services = $robot[APP_REG_SERVICES];

		if ( !in_array( $serviceName, $services ) )
			return PEAR::raiseError( $kernelStrings['app_invrobotlogin_message'], ERRCODE_APPLICATION_ERR );

		return true;
	}

	//
	// Functions for work with XML-files
	//

	function getAttributeValue( &$node, $attrName )
	//
	// Searches for attribute and returns its value
	//
	//		Parameters:
	//			$node - node containing sought attribute
	//			$attrName - attribute name
	//
	//		Returns attribute value, or null, it attribute is not found
	//
	{
		$attrs = $node->attributes();

		if ( !is_array( $attrs ) )
			return null;

		for ( $i = 0; $i < count($attrs); $i++ ) {
			$attr = $attrs[$i];

			if ( $attr->name == $attrName )
				return $attr->value;
		}

		return null;
	}

	function getAttributeValues( &$node )
	//
	// Returns values of all node attributes as associative array
	//
	//		Parameters:
	//			$node - XML document node
	//
	//		Returns associative array
	//
	{
		$attrs = $node->attributes();

		$result = array();

		if ( !is_array( $attrs ) )
			return $result;

		for ( $i = 0; $i < count($attrs); $i++ ) {
			$attr = $attrs[$i];

			$result[$attr->name] = $attr->value;
		}

		return $result;
	}

	function getElementByTagname( &$dom, $tagName )
	//
	// Returns element corresponding to tag name
	//
	//		Parameters:
	//			$dom - document containing sought element
	//			$tagName - tag name
	//
	//		Returns element, or null, if element is not found
	//
	{
		$elements = $dom->get_elements_by_tagname($tagName);

		if ( !count($elements) )
			return null;

		return $elements[0];
	}

	function setApplicationsOptions( $DB_KEY, $options, &$kernelStrings )
		//
		// Sets the applications options
		//
		//		Parameters:
		//			$DB_KEY - database key
		//			$options - applications options
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			$filePath = fixPathSlashes( sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DB_KEY)) );

			if ( !file_exists($filePath) )
				return PEAR::raiseError( $kernelStrings[ERR_XML] );

			$dom = domxml_open_file( realpath($filePath) );
			if ( !$dom )
				return PEAR::raiseError( $kernelStrings[ERR_XML] );

			return $dom;

			$drop_uncomfirmed_flag = false;
			$xpath = xpath_new_context($dom);

			$applicationsNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_APPLICATIONS );
			if ( !$applicationsNode || !count($applicationsNode->nodeset) )
				return PEAR::raiseError( $kernelStrings[ERR_XML] );

			$applicationsNode = $applicationsNode->nodeset[0];
			foreach ( $options as $option )
			{
				if ( $option['APP_ID'] == 'KERNEL' )
				{
					$element = @getElementByTagname( $dom, HOST_DBSETTINGS );
					$element->set_attribute( $option['Name'], $option['Value'] );
				}
				else
				{
					// Find the application node
					//
					$applicationNode = &xpath_eval( $xpath, sprintf("APPLICATION[@APP_ID='%s']", $option['APP_ID']), $applicationsNode );
					if ( $applicationNode && count($applicationNode->nodeset) )
					{
						
						if($option['Name'] == 'PLAN' && $option['Value'] == 'PAID'){
							$drop_uncomfirmed_flag = true;
						}
						$applicationNode = $applicationNode->nodeset[0];

						// Find the settings node or create it
						//
						$settingsNode = &xpath_eval( $xpath, "SETTINGS", $applicationNode );
						if ( !$settingsNode || !count($settingsNode->nodeset) )
							$settingsNode = @create_addElement( $dom, $applicationNode, 'SETTINGS' );
						else
							$settingsNode = $settingsNode->nodeset[0];

						// Find the setting node or create it
						//
						$settingNode = &xpath_eval( $xpath, sprintf("OPTION[@NAME='%s']", $option['Name']), $settingsNode );
						if ( !$settingNode || !count($settingNode->nodeset) )
							$settingNode = @create_addElement( $dom, $settingsNode, 'OPTION' );
						else
							$settingNode = $settingNode->nodeset[0];

						$children = $settingNode->child_nodes();

						if ( count($children) )
							$settingNode->remove_child($children[0]);

						$settingNode->set_attribute( "NAME", $option['Name'] );
						$valueNode = $dom->create_text_node ($option['Value']);

						$settingNode->append_child($valueNode);
					}
				}
			}
			
			$filePath = fixPathSlashes( sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DB_KEY)) );

			@$dom->dump_file($filePath, false, true);
			
			 
			
			if($drop_uncomfirmed_flag){
				
				ClassManager::includeClass('LoginHash');
				$loginHash = new LoginHash;
				$loginHash->loadFirst($DB_KEY);
				$loginHash->deleteHash();
			}

		}
		
	function create_addElement( &$dom, &$parent, $tagName )
	//
	// Creates element and attaches it to parent node
	//
	//		Paramters:
	//			$dom - XML-document
	//			$parent - parent node
	//
	//		Returns element
	//
	{
		$element = $dom->create_element( $tagName );
		return $parent->append_child( $element );
	}

	//
	// Support of administrator registry record
	//

	function loadAdminInfo()
	//
	// Loads information from file containing settings of system administrator
	//
	//		Returns an array containing information from file in the following format:
	//			array(LANGUAGE=>"eng", TEMPLATE=>"classic", "password"=>"pwd",
	//					MENULINKS=>array("APP_ID"=>array("0"=>"SCR_ID", "1"=>"SCR_ID"), ...) )
	//
	//		If file fails to be opened, an empty array is returned
	//
	{
		global $databaseInfo;

		$result = array();
		$result[LANGUAGE] = $databaseInfo[HOST_ADMINISTRATOR][HOST_LANGUAGE];
		$result[TEMPLATE] = $databaseInfo[HOST_ADMINISTRATOR][HOST_TEMPLATE];
		$result[PASSWORD] = $databaseInfo[HOST_ADMINISTRATOR][HOST_PASSWORD];

		return $result;
	}

	function updateAdminInfo( $adminInfo, $kernelStrings )
	//
	// Updates file containing administrator settings
	//
	//		Parameters:
	//			$adminInfo - an array containing administrator settings (see loadAdminInfo())
	//			$kernelStrings - an array containing string stored in localization.php in specific language
	//
	//	Returns null, or PEAR_Error
	//
	{

		if ( isset($adminInfo[PASSWORD]) )
			if ( PEAR::isError( $res = writeHostDataFileParameter("/".HOST_DATABASE."/".HOST_ADMINISTRATOR, HOST_PASSWORD, $adminInfo[PASSWORD], $kernelStrings) ) )
				return $res;

		return null;
	}

	function isAdministratorID( $U_ID )
	//
	// Compares user identifier to identifier of WBS administrator
	//
	//		Parameters:
	//			$U_ID - user identifier
	//
	//		Returns result of comparance
	//
	{
		return (strtoupper($U_ID )== ADMIN_USERNAME)?true:false;
	}

	//
	// Languages support
	//

	function getApplicationLanguage( $APP_ID, $userLang, $appData = null, $kernelStrings = null )
	//
	// Returns application available language
	//
	//		Checks if user language in application is accessible.
	//		If it is not, default application language is returned
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$userLang - user language
	//			$appData - application data
	//			$kernelStrings - application localization strings
	//
	//		Returns language supported by application
	//
	{
		global $AA_APP_ID;
		global $global_applications;

		if ( is_null($appData) )
			$appData = $global_applications[$APP_ID];

		if ( is_null($kernelStrings) ) {
			$commonStrVarName = "loc_str";

			$appStringsVarName = ( $APP_ID == $AA_APP_ID ) ? $commonStrVarName :
						sprintf( "%s_%s", strtolower($APP_ID), $commonStrVarName );

			$kernelStrings = eval( "global \$$appStringsVarName; return \$$appStringsVarName;" );
		}

		$langList = is_array($kernelStrings)? array_keys( $kernelStrings ) : array();
		
		if ( @in_array( $userLang, $langList ) )
			return $userLang;
		
		if ( is_array($appData)&&array_key_exists( APP_REG_DEFAULT_LANGUAGE, $appData ) && strlen($appData[APP_REG_DEFAULT_LANGUAGE]) )
			$defLang = $appData[APP_REG_DEFAULT_LANGUAGE];
		else
			$defLang = LANG_ENG;

		if ( !is_null($defLang) )
			if ( @in_array( $defLang, $langList ) )
				return $defLang;

		if ( count($langList) )
			return $langList[0];
		
		return null;
	}

	function loadLocalizationStrings( $dirPath, $baseName, $type = null, $fullStringInfo = false, $useCache = true )
	//
	// Loads strings from localization files
	//
	//		Parameters:
	//			$dirPath - path to directory where localization files stored
	//			$baseName - base part of file name (without extension)
	//			$type - return strings which belongs to specified type
	//			$fullStringInfo - return all information about strings
	//
	//		Returns array: array( eng=>array( 1=>"String 1"... )... )
	//
	{
		$result = array();
		
		$locLoader = new LocalizationLoader ();
		return $locLoader->loadStrings( $dirPath, $baseName, $type = null, $fullStringInfo, $useCache);				
		
		

		if ( !($handle = opendir($dirPath)) )
			return $result;

		while ( false !== ($file = readdir($handle)) ) {
			if ( $file != "." && $file != ".." ) {
				$filename = $dirPath.'/'.$file;

				if ( !is_file($filename) )
					continue;

				$fileInfo = pathinfo( $filename );
				if ( !isset($fileInfo["extension"]) )
					continue;

				$extension = $fileInfo["extension"];
				$basePart = substr( $file, 0, strlen($file)-strlen($extension)-1 );
				if ( $basePart != $baseName )
					continue;

				$fileContent = file( $filename );
				for ( $i = 0; $i < count($fileContent); $i++ ) {
					if ( !strlen( trim($fileContent[$i]) ) )
						continue;

					$lineData = explode( "\t", $fileContent[$i] );
					if ( !is_null($type) )
						if ( $type != $lineData[1] )
							continue;

					if ( isset( $lineData[3] ) )
					$lineData[3] = rtrim($lineData[3]);

					if ( !$fullStringInfo )
						$result[$extension][$lineData[0]] = str_replace("\\n", "\n", isset( $lineData[3] ) ? $lineData[3] : "" );
					else
						$result[$extension][$lineData[0]] = $lineData;
				}
			}
		}

		closedir( $handle );

		if ( array_key_exists( LANG_ENG, $result ) ) {
			$engStrings = $result[LANG_ENG];
			$engKeys = array_keys($engStrings);

			foreach ( $result as $locLang=>$strings ) {
				if ( $locLang == LANG_ENG )
					continue;

				$langKeys = array_keys($strings);

				$engDiff = array_diff( $engKeys, $langKeys );

				foreach ( $engDiff as $key )
					$strings[$key] = $engStrings[$key];

				$result[$locLang] = $strings;
			}
		}

		return $result;
	}

	function getUserEncoding( $U_ID )
	//
	// Returns encoding corresponding with a user language
	//
	{
		global $wbs_languages;

		if ( strtoupper($U_ID ) == ADMIN_USERNAME ) {
			$adminInfo = loadAdminInfo();
			$language =	$adminInfo[LANGUAGE];
		} else
			$language = readUserCommonSetting( $U_ID, LANGUAGE );
		
		if ( $language == LANG_ENG )
			return DEFAULT_ENCODING;

		$encoding = null;

		foreach( $wbs_languages as $lang_data )
			if ( $lang_data[WBS_LANGUAGE_ID] == $language ) {
				$encoding = $lang_data[WBS_ENCODING];
				break;
			}

		return $encoding;
	}

	//
	// File and directory functions
	//

	function safeMakeDir( $dirPath, &$errStr )
	//
	// Creates folder, if it does not exist. In case of error occurence, return error text.
	//
	//		Parameters:
	//			$dirPath - path to folder to be created
	//			$errStr - variable for storing error text
	//
	//		Returns true in case of success. In case of failure, returns false and error text.
	//
	{

		if ( !@file_exists( $dirPath ) ) {
			$oldMask = @umask(0);
			if ( !@mkdir( $dirPath, 0777 ) ) {
				$errStr = sprintf( "Unable to create directory %s", $dirPath );
				@umask($oldMask);
				return false;
			}
			@umask($oldMask);
		}

		return true;
	}

	function fixDirPath( $dirPath )
	//
	// Removes unnecessary spaces from the beginning and from the end of path to folder.
	//		Also removes slashes from the end of path, if they exist.
	//
	//		Parameters:
	//			$dirPath - path to folder
	//
	//		Returns fixed path
	//
	{
		$dirPath = trim( $dirPath );

		$strlen = strlen( $dirPath );
		if ( $dirPath[$strlen-1] == "/" || $dirPath[$strlen-1] == "\\" )
			return substr( $dirPath, 0, $strlen-1 );

		return $dirPath;
	}

	function fixPathSlashes( $filePath )
	//
	// Converts backslashes to slashes
	//
	//		Parameters:
	//			$filePath - path
	//
	//		Returns fixed path
	//
	{
		return str_replace(array('\\','//'), '/', $filePath );
	}

	function explodePath( $filePath )
	//
	// Returns array of path parts separated by forward slashes
	//
	//		Parameters:
	//			$filePath - path
	//
	//		Returns array of strings
	//
	{
		$parts = explode('/', $filePath);
		$num = count($parts);

		if( $num % 2 == 0 ) {
			$parts[] = '';
			$num++;
		}

		for($i = 1; $i < $num; $i += 2)
			$$parts[$i] = $parts[$i+1];

		return $parts;
	}

	function forceDirPath( $dirPath, &$errStr, $baseDir = null )
	//
	// Creates all folders leading to $dirPath
	//
	//		Parameters:
	//			$dirPath - path to folder
	//			$errStr - variable for storing error message
	//			$baseDir - base directory
	//
	//	Returns true, in case of success
	//
	{
		if ( is_null($baseDir) )
			$baseDir = WBS_DIR;

		$baseDir = fixDirPath($baseDir);

		if ( substr($dirPath, 0, strlen($baseDir)) == $baseDir )
			$dirPath = substr( $dirPath, strlen($baseDir)+1 );

		$pathParts = array( basename( $dirPath ) );

		$prevPart = "";
		$curPart = $dirPath;

		while ( true ) {
			$prevPart = $curPart;
			$curPart = dirname( $curPart );

			if ( $prevPart == $curPart )
				break;

			$pathParts[] = basename($curPart);
		}

		$curPath = "";

		$currentDir = getcwd();
		chdir( $baseDir );

		for ( $i = count( $pathParts ) - 1; $i >= 0 ; $i-- ) {
			$curPath .= $pathParts[$i]."/";

			if ( !@safeMakeDir( $curPath, $errStr ) )
				return false;
		}

		chdir( $currentDir );

		return true;
	}

	function saveArrayToFile( $arr, $filePath )
	//
	// Saves array to file
	//
	//		Parameters:
	//			$arr - array to save
	//			$filePath - path to a file
	//
	//		Returns booleans
	//
	{
		$fp = @fopen( $filePath, "wt" );
		if ( !$fp )
			return false;

		$cn = count($arr);
		for ( $i = 0; $i < $cn; $i++ )  {
			if ( $i < ($cn - 1) )
				fwrite( $fp, $arr[$i]."\n" );
			else
				fwrite( $fp, $arr[$i] );
		}

		fclose( $fp );

		return true;
	}

	function applyFileNameMapping( $filePath, $nameMap )
	//
	// Replaces all occurrences of the name map keys in file with the corresponding values
	//
	//		Parameters:
	//			$filePath - path to a file
	//			$nameMap - array with replacements
	//
	//		Returns boolean
	//
	{
		if ( !file_exists($filePath) )
			return false;

		$fileContent = file($filePath);
		$contentLen = count($fileContent);

		if ( !$contentLen )
			return true;

		for( $i = 0; $i < $contentLen; $i++ )
			foreach( $nameMap as $key=>$value )
				$fileContent[$i] = str_replace($key, $value, trim($fileContent[$i]));

		return saveArrayToFile($fileContent, $filePath);
	}

	function dirInfo( $path, &$fileCount, &$totalSize )
	//
	// Returns folder size and number of files stored within
	//
	//		Parameters:
	//			$path - path to folder
	//			$fileCount - number of files
	//			$totalSize - total size of files
	//
	//		Returns null
	//
	{
		if ( !file_exists($path) )
			return null;

		if ( !($handle = opendir($path)) )
			return null;

		while ( false !== ($file = readdir($handle)) ) {
			if ( $file != "." && $file != ".." ) {
				$filename = $path.'/'.$file;

				if ( is_file($filename) ) {
					$fileCount++;
					$totalSize += filesize($filename);
				} else
					if ( is_dir($filename) )
						dirInfo( $filename, $fileCount, $totalSize );
			}
		}
		closedir( $handle );

		return null;
	}

	function getSystemSpaceUsed()
	//
	// Returns size of attached files folder, in bytes
	//
	//		Returns integer
	//
	{
		if ( DATABASE_SIZE_LIMIT > 0 ) {
			$dataPath = sprintf( WBS_ATTACHMENTS_DIR );
			dirInfo( $dataPath, $fileCount, $totalSize );

			dirInfo( WBS_PUBLIC_ATTACHMENTS_DIR, $fileCount, $publicDataTotalSize );

			$result = $totalSize + $publicDataTotalSize;

			return $result;
		} else
			return 0;
	}

	function spaceLimitExceeded( $curDbSize = null )
	//
	// Checks if sum of attached files folder size and size of database does not exceed maximum permitted value (DATABASE_SIZE_LIMIT)
	//
	//		Parameters:
	//			$curDbSize - current size of attached files folder, in megabytes
	//			If this parameter is null, size if determined in function.
	//
	//		Returns value of boolean type
	//
	{
		if ( DATABASE_SIZE_LIMIT == 0 )
			return false;

		if ( is_null( $curDbSize ) ) {
			$totalSize = getSystemSpaceUsed() + getDatabaseSize();

			$mbSize = $totalSize/MEGABYTE_SIZE;
		} else
			$mbSize = $curDbSize;

		return $mbSize >= DATABASE_SIZE_LIMIT;
	}

	function removeDir( $dirPath )
	//
	// Recursively deletes folder
	//
	//		Parameters:
	//			$dirPath - path to folder to be deleted
	//
	//		Returns null, or error message
	//
	{
		$fileList = array();

		clearstatcache();
		if ( !file_exists($dirPath) )
			return null;

		if ( !($dirHandle = @opendir($dirPath) ) )
			return "Unable to open directory ".basename($dirPath);

		$fileList = array();
		$dirList = array();

		while ( ( $tfile = readdir($dirHandle) ) )
			if ( $tfile != "." && $tfile != ".." )
				if ( !is_dir( $dirPath."/".$tfile ) )
					$fileList[] = $tfile;
				else
					$dirList[] = $tfile;

		@closedir( $dirHandle );

		for ( $i = 0; $i < count($fileList); $i++ ) {
			$filePath = $dirPath."/".$fileList[$i];

			if ( !@unlink( $filePath ) )
				return "Unable to delete file ".$fileList[$i];
		}

		for ( $i = 0; $i < count($dirList); $i++ ) {
			$filePath = $dirPath."/".$dirList[$i];

			if ( !is_null( $res = removeDir($filePath) ) )
				return $res;
		}

		if ( !@rmdir( $dirPath ) )
			return "Unable to delete directory ".basename($dirPath);

		return null;
	}

	function formatFileSizeStr( $fileSize )
	//
	// Forms string containing files size (123KB, 13 Byte(s))
	//
	//		Parameters:
	//			$fileSize - file size, in bytes
	//
	//		Returns string containing file size
	//
	{
		global $kernelStrings;
		return formatFileSizeStrloc($fileSize, $kernelStrings);
	}

	function formatFileSizeStrloc( $fileSize, &$kernelStrings )
	//
	// Forms string containing files size (123KB, 13 Byte(s))
	//
	//		Parameters:
	//			$fileSize - file size, in bytes
	//			$kernelStrings - localization
	//
	//		Returns string containing file size
	//
	{
		
		if (!count($kernelStrings))
			return null;
			
		if ( !strlen($fileSize) )
			return null;

		if ( !$fileSize )
			return sprintf( $kernelStrings['aa_kb_style'], "0.00"); 

		if ( $fileSize < 1024 )
			$fileSize = 1024;

		if ( $fileSize >= GIGABYTE_SIZE_RELATIVE )
			return sprintf( $kernelStrings['aa_gb_style'], round(ceil($fileSize)/GIGABYTE_SIZE_RELATIVE, 2) );
		elseif ( $fileSize >= MEGABYTE_SIZE )
			return sprintf( $kernelStrings['aa_mb_style'], round(ceil($fileSize)/MEGABYTE_SIZE, 2) );
		else
			return sprintf( $kernelStrings['aa_kb_style'], round(ceil($fileSize)/1024, 2) ); 
	}
	
	function cleanUpTemporaryDir()
	//
	// Removes files from temporary folder, which age, in hours, exceed value of constant TMP_FILES_LIFETIME
	//
	//		Returns null
	//
	{
		$tmpDir = fixDirPath( WBS_TEMP_DIR );
		$currentTime = time();
		$prefixLen = strlen( TMP_FILES_PREFIX );

		if ( is_dir($tmpDir) ) {
			if ( $dh = opendir($tmpDir) ) {

				while ( ($file = readdir($dh)) !== false ) {
					if ( substr($file, 0, $prefixLen) != TMP_FILES_PREFIX )
						continue;

					$filePath = $tmpDir."/".$file;

					if ( !is_dir($filePath) ) {
						$fileAge = ($currentTime - filemtime( $filePath ))/3600;

						if ( $fileAge >= TMP_FILES_LIFETIME )
							@unlink( $filePath );

					}
				}

				closedir($dh);
			}
		}
	}

	function directorySize( $path )
	//
	// Returns directory size in bytes
	//
	//		Parameters:
	//			$path - path to the directory
	//
	//		Returns integer
	//
	{
		$result = 0;

		if ( !file_exists($path) )
			return $result;

		$handle = @opendir($path);

		if ( !$handle )
			return $result;

		while ( $file = @readdir ($handle) ) {
			$filePath = $path."/".$file;
			if ( is_dir($filePath) )
				continue;

			$result = filesize($filePath);
		}

		@closedir($handle);

		return $result;
	}

	//
	// Attached files management functions
	//

	function getAttachedFileXMLObj( &$filelist, $fileName )
	//
	// Internal function. Searches for file with certain name in file list
	//
	//		Parameters:
	//			$filelist - DOM object corresponding to the node FILELIST in file list
	//			$fileName - names of files
	//
	//		Returns DOM object, or PEAR_Error
	//
	{
		$commonErrStr = "Error processing XML data";

		$fileName = base64_encode( $fileName );

		$files = $filelist->get_elements_by_tagname( AF_FILE );
		if ( !is_array($files) || !count($files) )
			return null;

		for ( $i = 0; $i < count($files); $i++ ) {
			$file = $files[$i];

			if ( $file->get_attribute(AF_FILENAME) == $fileName )
				return $file;
		}

		return null;
	}

	function addAttachedFile( $fileList, $fileinfo, $action = AAF_ADD )
	//
	// Adds file to the list of attached files
	//
	//		Parameters:
	//			$fileList - file list in XML format
	//			$fileInfo - information about file in the form of associative array with the following fields
	//				name - disk file name
	//				type - mime-type
	//				size - size, in bytes
	//				screenname - file name to be displayed in lists
	//				comment - file comments
	//				diskfilename - disk file name
	//				tmpfilename - file name in temporary folder
	//			$action - action. One of the following values:
	//				AAF_ADD - to add file to the list
	//				AAF_REPLACE - to keep in list only the file
	//
	//	Returns string containing file list in XML format, or PEAR_Error
	//
	{
		$commonErrStr = "Error processing XML data";

		$dom = null;

		if ( strlen($fileList) )
			$dom = @domxml_open_mem( $fileList );

		if ( !$dom )
			$dom = @domxml_new_doc("1.0");

		if ( !$dom )
			return PEAR::raiseError( $commonErrStr );

		$root = $dom->root();
		if ( !$root )
			$root = @create_addElement( $dom, $dom, AF_FILELIST );
		else
			if ( $action == AAF_REPLACE ) {
				$dom->remove_child( $root );
				$root = @create_addElement( $dom, $dom, AF_FILELIST );
			}

		if ( !$root )
			return PEAR::raiseError( $commonErrStr );

		$fileObj = getAttachedFileXMLObj( $root, $fileinfo["name"] );

		if ( !is_null($fileObj) )
			$root->remove_child( $fileObj );

		$fileObj = @create_addElement( $dom, $root, AF_FILE );
		if ( !$fileObj )
			return PEAR::raiseError( $commonErrStr );

		$fileinfo["name"] = base64_encode( $fileinfo["name"] );
		$fileObj->set_attribute( AF_FILENAME, $fileinfo["name"] );

		if ( !isset($fileinfo["screenname"]) || !strlen($fileinfo["screenname"]) )
			$fileinfo["screenname"] = $fileinfo["name"];
		else
			$fileinfo["screenname"] = base64_encode( $fileinfo["screenname"] );

		$fileObj->set_attribute( AF_SCREENFILENAME, $fileinfo["screenname"] );
		$fileObj->set_attribute( AF_MIME_TYPE, $fileinfo["type"] );
		$fileObj->set_attribute( AF_FILESIZE, $fileinfo["size"] );
		if ( array_key_exists("comment", $fileinfo) )
			$fileObj->set_attribute( AF_COMMENT, base64_encode($fileinfo["comment"]) );
		$fileObj->set_attribute( AF_DISKFILENAME, base64_encode($fileinfo["diskfilename"]) );

		if ( array_key_exists("tmpfilename", $fileinfo) )
			$fileObj->set_attribute( AF_TMPFILENAME, $fileinfo["tmpfilename"] );
		$fileObj->set_attribute( AF_FILEDATE, time() );

		return $dom->dump_mem();
	}

	function listAttachedFiles( $fileList )
	//
	// Returns list of files in the form of an array. Each element of array represents array with elements as in parameter fileInfo of the function addAttachedFile
	//
	//		Parametets:
	//			$fileList - file list in XML format
	//
	//		Returns an array
	//
	{
		$result = array();

		if ( !strlen($fileList) )
			return $result;

		$dom = @domxml_open_mem( $fileList );
		if ( !$dom )
			return $result;

		$root = $dom->root();
		if ( !$root )
			return $result;

		$files = $root->get_elements_by_tagname( AF_FILE );
		if ( !is_array($files) || !count($files) )
			return $result;

		for ( $i = 0; $i < count($files); $i++ ) {
			$file = $files[$i];

			$result[] = array( "name"=>base64_decode( @$file->get_attribute(AF_FILENAME) ),
								"type"=>@$file->get_attribute(AF_MIME_TYPE),
								"size"=>@$file->get_attribute(AF_FILESIZE),
								"screenname"=>base64_decode( @$file->get_attribute(AF_SCREENFILENAME) ),
								"comment"=>base64_decode( @$file->get_attribute(AF_COMMENT) ),
								"diskfilename"=>base64_decode( @$file->get_attribute(AF_DISKFILENAME) ),
								"tmpfilename"=>@$file->get_attribute(AF_TMPFILENAME),
								"filedate"=>convertTimestamp2Local( @$file->get_attribute(AF_FILEDATE) ) );
		}

		return $result;
	}

	function copyDir( $source , $destination )
	//
	// Copies directory recursively
	//
	//		Parameters:
	//			$source - source directory
	//			$destination - destination directory
	//
	//		Returns number of copied files
	//
	{
		if ( !file_exists($source) )
			return 0;

		$dossier = opendir($source);


		if (!file_exists($destination)){

			@mkdir( $destination, fileperms($source), true );
		}

		$total = 0;

		while ( $fichier = readdir($dossier) ) {
			$l = array('.', '..');
			if ( !in_array( $fichier, $l) ){
				if ( is_dir($source."/".$fichier) ) {
					$total += copydir( "$source/$fichier", "$destination/$fichier" );
				} else {
					copy("$source/$fichier", "$destination/$fichier");
					$total++;
				}
			}
		}

		return $total;
	}

	function applyPageAttachments( $PAGE_ATTACHED_FILES, $PAGE_DELETED_FILES, $attachmentsPath, $kernelStrings, $APP_ID, $deleteTemporary = true )
	//
	// Apply changes based on attached and removed files.
	//
	//		Parameters:
	//			$PAGE_ATTACHED_FILES - appended files
	//			$PAGE_DELETED_FILES - removed files
	//			$attachmentsPath - destination path of attached files
	//			$kernelStrings - array of localization strings for the current language (given from localization.php)
	//			$APP_ID - application identifier
	//			$deleteTemporary - delete temporary files
	//
	//		Returns: nothing or PEAR_Error
	//
	{
		$pageFiles = $PAGE_ATTACHED_FILES;
		$pageFileList = listAttachedFiles( $pageFiles );

		$tmpFolder = fixDirPath(WBS_TEMP_DIR);
		$pathPattern = "%s/%s";

		$res = @forceDirPath( $attachmentsPath, $fdError );
		if ( !$res )
			return PEAR::raiseError( $kernelStrings[ERR_CREATEDIRECTORY] );

		// Delete files
		//
		$delFiles = $PAGE_DELETED_FILES;
		$delFileList = listAttachedFiles( $delFiles );

		$QuotaManager = new DiskQuotaManager();

		for ( $i = 0; $i < count($delFileList); $i++ ) {
			$fileInfo = $delFileList[$i];
			$filePath = sprintf( $pathPattern, $attachmentsPath, $fileInfo["diskfilename"] );

			if ( file_exists($filePath) ) {
				$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $APP_ID, -1*filesize($filePath) );
				@unlink($filePath);
			}
		}

		for ( $i = 0; $i < count($pageFileList); $i++ ) {
			$fileInfo = $pageFileList[$i];
			$srcFilePath = sprintf( $pathPattern, $tmpFolder, $fileInfo["tmpfilename"] );

			$destFilePath = sprintf( $pathPattern, $attachmentsPath, $fileInfo["diskfilename"] );

			if ( !@copy($srcFilePath, $destFilePath) ) {
				$QuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( sprintf($kernelStrings[ERR_COPYFILE], $fileInfo["screenname"]) );
			}

			$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $APP_ID, filesize($destFilePath) );

			if ( $deleteTemporary )
				@unlink($srcFilePath);
		}

		$QuotaManager->Flush( $kernelStrings );
	}

	function getKernelAttachmentsDir()
	//
	// Returns a path where kernel attachment files are stored
	//
	//		Returns string
	//
	{
		return sprintf( "%s/%s", WBS_ATTACHMENTS_DIR, strtolower(AA_APP_ID) );
	}

	function checkLogoFile( $fileName, $kernelStrings )
	//
	// Examines company logo file
	//
	//		Parameters:
	//			$fileName - a name of logo file
	//			$kernelStrings - an array containin strings stored within localization.php in specific language
	//
	//		Returns null or PEAR_Error
	//
	{
		$info = pathinfo( $fileName );
		$ext = strtoupper($info["extension"]);

		if ( strtoupper($ext) != 'GIF' )
			return PEAR::raiseError( $kernelStrings['ci_invlogoformat_message'], ERRCODE_APPLICATION_ERR );

		return null;
	}

	function getMimeType( $filePath )
	//
	// Returns file mime-type
	//
	//		Parameters:
	//			$filePath - path to a file
	//
	//		Returns string
	//
	{
		$info = pathinfo( $filePath );

		if ( array_key_exists( "extension", $info ) )
			$ext = strtoupper($info["extension"]);
		else
			$ext = null;

		if ( !strlen($ext) )
			return "";

		$mimeFilePath = WBS_DIR."kernel/includes/mimetypes.dat";
		$types = file($mimeFilePath);

		foreach ($types as $type) {
			$type = explode( "\t", $type );
			if ( strtoupper($type[0]) == $ext )
				return trim($type[1]);
		}

		return "";
	}

	function getAttachedFileInfo( $fileList, $fileName )
	//
	// Returns information about file $fileName in the form of an array containing elements as in parameters fileInfo of the function addAttachedFile
	//
	//		Parameters:
	//			$fileList - file list in XML format
	//			$fileName - file name
	//
	//		Returns an array containing information about file, or null
	//
	{
		if ( !strlen($fileList) )
			return null;

		$dom = @domxml_open_mem( $fileList );
		if ( !$dom )
			return null;

		$root = $dom->root();
		if ( !$root )
			return null;

		$files = $root->get_elements_by_tagname( AF_FILE );
		if ( !is_array($files) || !count($files) )
			return null;

		$file = getAttachedFileXMLObj( $root, $fileName );
		if ( PEAR::isError( $file ) )
			return null;

		if( is_null($file) )
			return null;

		return array( "name"=>base64_decode( @$file->get_attribute(AF_FILENAME) ),
								"type"=>@$file->get_attribute(AF_MIME_TYPE),
								"size"=>@$file->get_attribute(AF_FILESIZE),
								"screenname"=>base64_decode( @$file->get_attribute(AF_SCREENFILENAME) ),
								"comment"=>base64_decode( @$file->get_attribute(AF_COMMENT) ),
								"diskfilename"=>base64_decode( @$file->get_attribute(AF_DISKFILENAME) ),
								"tmpfilename"=>@$file->get_attribute(AF_TMPFILENAME),
								"filedate"=>@$file->get_attribute(AF_FILEDATE) );
	}

	function removeAttachedFile( $fileList, $fileName )
	//
	// Removes file from the list of attached files
	//
	//		parameters:
	//			$fileList - file list in XML format
	//			$fileName - file name
	//
	//		Returns string containing file list in XML format, or PEAR_Error
	//
	{
		$commonErrStr = "Error processing XML data";

		$result = array();

		if ( !strlen($fileList) )
			return $fileList;

		$dom = @domxml_open_mem( $fileList );
		if ( !$dom )
			return PEAR::raiseError( $commonErrStr );

		$root = $dom->root();
		if ( !$root )
			return PEAR::raiseError( $commonErrStr );

		$files = $root->get_elements_by_tagname( AF_FILE );
		if ( !is_array($files) || !count($files) )
			return $fileList;

		$fileObj = getAttachedFileXMLObj( $root, $fileName );

		if ( !is_null($fileObj) )
			$root->remove_child( $fileObj );

		return $dom->dump_mem();
	}

	function removeAttachedFileList( $srcFileList, $fileList )
	//
	// Removes file, which exist in the list $fileList, from the initial list $srcFileList
	//
	//		Parameters:
	//			$srcFileList - initial file list
	//			$fileList - list of files to be added
	//
	//		Returns string containing file list in XML format, or PEAR_Error
	//
	{
		$commonErrStr = "Error processing XML data";

		$result = array();

		if ( !strlen($srcFileList) )
			return null;

		if ( !strlen($fileList) )
			return $srcFileList;

		$srcDom = @domxml_open_mem( $srcFileList );
		if ( !$srcDom )
			return PEAR::raiseError( $commonErrStr );

		$srcRoot = $srcDom->root();
		if ( !$srcRoot )
			return PEAR::raiseError( $commonErrStr );


		$dom = @domxml_open_mem( $fileList );
		if ( !$dom )
			return PEAR::raiseError( $commonErrStr );

		$root = $dom->root();
		if ( !$root )
			return PEAR::raiseError( $commonErrStr );

		$files = $root->get_elements_by_tagname( AF_FILE );
		if ( !is_array($files) || !count($files) )
			return $fileList;

		for ( $i = 0; $i < count($files); $i++ ) {
			$file = $files[$i];
			$fileName = base64_decode( @$file->get_attribute(AF_FILENAME) );

			$fileObj = getAttachedFileXMLObj( $srcRoot, $fileName );

			if ( !is_null($fileObj) )
				$srcRoot->remove_child( $fileObj );
		}

		return $srcDom->dump_mem();
	}

	function mergeAttachedFileLists( $srcFileList, $fileList )
	//
	// Adds files from one list to another.
	//		In case of names coincidence, information about file from initial list is rewritten with information from the list to be added.
	//
	//		Parameters:
	//			$srcFileList - initial file list
	//			$fileList - list of files to be added
	//
	//		Returns list of files in the form of string, or PEAR_Error
	//
	{
		$commonErrStr = "Error processing XML data";

		if ( !strlen($srcFileList) )
			return $fileList;

		$srcDom = @domxml_open_mem( $srcFileList );
		if ( !$srcDom )
			return $fileList;

		$srcRoot = $srcDom->root();
		if ( !$srcRoot )
			return $fileList;

		$srcFiles = $srcRoot->get_elements_by_tagname( AF_FILE );
		if ( !is_array($srcFiles) || !count($srcFiles) )
			return $fileList;

		if ( !strlen($fileList) )
			return $srcFileList;

		$dom = @domxml_open_mem( $fileList );
		if ( !$dom )
			return $srcFileList;

		$root = $dom->root();
		if ( !$root )
			return $srcFileList;

		$files = $root->get_elements_by_tagname( AF_FILE );
		if ( !is_array($files) || !count($files) )
			return $srcFileList;

		for ( $i = 0; $i < count($files); $i++ ) {
			$file = $files[$i];
			$fileName = base64_decode( @$file->get_attribute(AF_FILENAME) );

			$fileObj = getAttachedFileXMLObj( $srcRoot, $fileName );

			if( !is_null($fileObj) )
				$srcRoot->remove_child($fileObj);

			$fileObj = @create_addElement( $srcDom, $srcRoot, AF_FILE );
			if ( !$fileObj )
				return PEAR::raiseError( $commonErrStr );

			$fileObj->set_attribute( AF_FILENAME, @$file->get_attribute(AF_FILENAME) );
			$fileObj->set_attribute( AF_SCREENFILENAME, @$file->get_attribute(AF_SCREENFILENAME) );
			$fileObj->set_attribute( AF_MIME_TYPE, @$file->get_attribute(AF_MIME_TYPE) );
			$fileObj->set_attribute( AF_FILESIZE, @$file->get_attribute(AF_FILESIZE) );
			$fileObj->set_attribute( AF_COMMENT, @$file->get_attribute(AF_COMMENT) );
			$fileObj->set_attribute( AF_DISKFILENAME, @$file->get_attribute(AF_DISKFILENAME) );
			$fileObj->set_attribute( AF_FILEDATE, @$file->get_attribute(AF_FILEDATE) );
		}

		return $srcDom->dump_mem();
	}

	//
	// HTTP functions
	//

	function prepareArrayToDisplay( $array, $nlExcludesList = null, $stripSlashes = false )
	//
	// Applies function prepareStrToDisplay to each element of an array
	//
	//		Parameters:
	//			$array - an array
	//			$stripSlashes - if it is true, function stripSlashes() is applied to elements of an array
	//
	//		Returns an associative array
	//
	{
		if ( !is_array( $array ) )
			return $array;

		$resultArr = array();

		while ( list( $key, $val ) = each ( $array ) )
			if ( is_null($nlExcludesList) || !is_array($nlExcludesList) || !in_array( $key, $nlExcludesList ) )
				$resultArr = array_merge( $resultArr, array( $key=>prepareStrToDisplay( $val, true, $stripSlashes ) ) );
			else
				$resultArr = array_merge( $resultArr, array( $key=>$val ) );

		return $resultArr;
	}

	function prepareArrayToDisplayInControl( $array, $fieldList = null )
	//
	// Prepares array to be used by HTML forms
	//
	//		Parameters:
	//			$array - an array
	//			$fieldList - list of fields, which should be modified
	//
	//		Returns an associative array
	//
	{
		return $array;
	}

	function getMaxPostSize()
	//
	// Returns maximum size of data that can be transmitted with the help of POST method
	//
	//		Returns integer - size in bytes
	//
	{
		$postSize = (float)ini_get("post_max_size");
		$postSize *= MEGABYTE_SIZE;

		$sizeLimit = DATABASE_SIZE_LIMIT*MEGABYTE_SIZE;

		if ( $sizeLimit != 0 )
			return min( $sizeLimit, $postSize );
		else
			return $postSize;
	}

	function getMaxUploadSize()
	//
	// Returns maximum size of file that can be uploaded to the server
	//
	//		Returns integer - size in bytes
	//
	{
		$uploadSize = (float)ini_get("upload_max_filesize");
		$uploadSize *= MEGABYTE_SIZE;

		$postSize = (float)ini_get("post_max_size");
		$postSize *= MEGABYTE_SIZE;

		return min( $uploadSize, $postSize );
	}

	function getTotalAvailableSpace( &$kernelStrings )
	//
	// Returns available space
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns integer - space in bytes
	//
	{
		$QuotaManager = new DiskQuotaManager();

		return $QuotaManager->GetAvailableSystemSpace( $kernelStrings );

		/*
		$usedSpace = getSystemSpaceUsed() + getDatabaseSize();
		$limit = DATABASE_SIZE_LIMIT*MEGABYTE_SIZE;

		$result = $limit - $usedSpace;
		if ( $result < 0 )
			$result = 0;

		return $result; */
	}

	//
	// Host functions
	//

	
	function host_log( $DB_KEY, $U_ID, $ip, $client, $ID_error, $LN_error )
	//
	// Logs system connection
	//
	//		Parameters:
	//			$DB_KEY - customer database name
	//			$U_ID - user identifier
	//			$ip - client IP address
	//			$client - user client (web, soap and so on)
	//			$ID_error - invalid system ID
	//			$LN_error - invalid user login
	//
	//		Returns null
	//
	{
		//
		// Log format is: [datetime] hostname ip U_ID client ID_error LN_error
		//
		global $wbs_accounts_db;
		if (file_exists("../kernel/classes/class.accountname.php") && count($wbs_accounts_db) && !$ID_error && !$LN_error) {
			require_once( "../kernel/classes/class.accountname.php");
			$acc=new AccountName('');
			$acc->updateLoginDatetime($DB_KEY);
		}
		$filePath = sprintf( "%s/%s", WBS_DBLSIT_DIR, LOG_FILE_NAME );

		$fp = @fopen( $filePath, "at" );

		$datetime = convertToSqlDateTime( time() );

		$logStr = sprintf( "[%s]\t%s\t%s\t%s\t%s\t%d\t%d\n", $datetime, $DB_KEY, $ip, $U_ID, $client, $ID_error, $LN_error );
		fwrite( $fp, $logStr );

		@fclose( $fp );
	}


	function loadModulesFromNodeset( $nodeset, &$result )
	//
	// Read modules from nodeset and add it to global WBS_MODULES
	//
	//		Parameters:
	//			$nodeset - nodeset of MODULES from xml
	//			$result - resulting array
	//
	//		Returns array
	//
	{
		global $WBS_MODULES;
		if (is_array($nodeset)) {
			foreach ($nodeset as $module) {
				$params = getXMLAttributes( $module );
				$result[$params["CLASS"]]["ID"] = $params["ID"];
				$result[$params["CLASS"]]["DISABLED"] = 0;
				if (isset($params["ID"]) && $params["ID"]) {
					$WBS_MODULES->setDefaultModule( $params["CLASS"], $params["ID"] );
					$WBS_MODULES->enableClass( $params["CLASS"] );
				}
				
				if ( isset( $params["DISABLED"] ) && $params["DISABLED"] == 1 )
				{
					$WBS_MODULES->disableClass( $params["CLASS"] );
					$result[$params["CLASS"]]["DISABLED"] = 1;
				}
			}
		}
		return $result;
	}
		
	function loadHostDataFile( $DB_KEY, $kernelStrings = null )
	//
	// Loads host information xml file
	//
	//		Parameters:
	//			$DB_KEY - customer database name
	//			$kernelStrings - an array containing strings in specific language
	//
	//		Returns array containig data from host information file or PEAR_Error
	//
	{
		global $wbs_sqlServers;
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;
		global $WBS_MODULES;
		global $mt_hosting_plan_settings;
		global $mt_hosting_plan_limitstats_data,
			$mt_info_parameters,
			$mt_Price,
			$mt_commerce_applications,
			$mt_tariff_Price,
			$mt_tariff_groups,
			$mt_hosting_plan_extensions,
			$mt_hosting_plan_extensions_queries;

				
		$commonErrorMsg = "Error loading database description file";

		$filePath = fixPathSlashes( sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DB_KEY) ) );

		if ( !file_exists($filePath) )
			if ( !is_null($kernelStrings) )
				return PEAR::raiseError( $kernelStrings['app_dbkeynotfound_message']." {$DB_KEY}", ERRCODE_APPLICATION_ERR );
			else
				return PEAR::raiseError( "Database key is not found"." {$DB_KEY}" );

		$dom = domxml_open_file( realpath($filePath) );
		if ( !$dom )
			if ( !is_null($kernelStrings) )
				return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
			else
				return PEAR::raiseError( $commonErrorMsg );

		$xpath = xpath_new_context($dom);

		$result = array();
		if ( !($dbsettings = xpath_eval($xpath, "/".HOST_DATABASE."/".HOST_DBSETTINGS)) )
			if ( !is_null($kernelStrings) )
				return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
			else
				return PEAR::raiseError( $commonErrorMsg );

		if ( !count($dbsettings->nodeset) )
			if ( !is_null($kernelStrings) )
				return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
			else
				return PEAR::raiseError( $commonErrorMsg );

		$dbsettings = $dbsettings->nodeset[0];
		$result[HOST_DBSETTINGS] = getAttributeValues( $dbsettings );

		if ( array_key_exists(HOST_SQLSERVER, $result[HOST_DBSETTINGS]) && strlen($result[HOST_DBSETTINGS][HOST_SQLSERVER]) ) {
			$sqlServerName = $result[HOST_DBSETTINGS][HOST_SQLSERVER];

			if ( !array_key_exists($sqlServerName, $wbs_sqlServers) )
				if ( !is_null($kernelStrings) )
					return PEAR::raiseError( sprintf($kernelStrings['app_servernotfound_message'], $sqlServerName ),
												ERRCODE_SERVERNOTFOUND_ERR,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												$sqlServerName );
				else
					return PEAR::raiseError( sprintf("SQL Server \"%s\" is not found.", $sqlServerName ),
												ERRCODE_SERVERNOTFOUND_ERR,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												$sqlServerName );

			$sqlServerParams = $wbs_sqlServers[$sqlServerName];
		} else {
			$servers = array_keys($wbs_sqlServers);
			$firstServer = $servers[0];
			$sqlServerParams = $wbs_sqlServers[$firstServer];
		}
		
		// for new billing 
		//
		if(onWebAsystServer()){
			$result[HOST_DBSETTINGS]['RECIPIENTS_LIMIT'] = HOST_RECIPIENTS_LIMIT;
			$result[HOST_DBSETTINGS]['SMS_RECIPIENTS_LIMIT'] = HOST_SMS_RECIPIENTS_LIMIT;
		}
		
		if ( !array_key_exists(HOST_DBNAME, $result[HOST_DBSETTINGS]) ) $result[HOST_DBSETTINGS][HOST_DBNAME] = null;
		if ( !array_key_exists(HOST_DBPASSWORD, $result[HOST_DBSETTINGS]) ) $result[HOST_DBSETTINGS][HOST_DBPASSWORD] = null;
		if ( !array_key_exists(HOST_DBUSER, $result[HOST_DBSETTINGS]) ) $result[HOST_DBSETTINGS][HOST_DBUSER] = null;

		$adminPassword = ( isset($sqlServerParams[WBS_ADMIN_PASSWORD]) ) ? $sqlServerParams[WBS_ADMIN_PASSWORD] : "";
		$adminUser = $sqlServerParams[WBS_ADMIN_USERNAME];
		$dbHost = $sqlServerParams[WBS_HOST];
		$dbPort = $sqlServerParams[WBS_PORT];

		if ( array_key_exists( WBS_WEBASYSTHOST, $sqlServerParams ) )
			$dbWebasystHost = $sqlServerParams[WBS_WEBASYSTHOST];
		else
			$dbWebasystHost = 'localhost';

		if ( !strlen($dbWebasystHost) )
			$dbWebasystHost = 'localhost';

		if ( !defined("DB_ADMIN_PASSWORD") ) define( "DB_ADMIN_PASSWORD", $adminPassword );
		if ( !defined("DB_ADMIN_USER") ) define( "DB_ADMIN_USER", $adminUser );
		if ( !defined("DB_HOST") ) define( "DB_HOST", $dbHost );
		if ( !defined("DB_PORT") ) define( "DB_PORT", $dbPort );
		if ( !defined("DB_WEBASYSTHOST") ) define( "DB_WEBASYSTHOST", $dbWebasystHost );

		$result[HOST_APPLICATIONS] = array();
		
		
		
		// new billing 
		//

		if (!empty($result[HOST_DBSETTINGS][HOST_FREE_APPS]))
			{
			$app_list = explode(',', $result[HOST_DBSETTINGS][HOST_FREE_APPS] ); 
			foreach ( $app_list as $APP_ID ) 
				$result[HOST_APPLICATIONS][trim($APP_ID)] = array( HOST_APP_ID=> trim($APP_ID));
			}

		if (!empty($result[HOST_DBSETTINGS][HOST_CUSTOM_APPS]))
			{
			$app_list = explode(',', $result[HOST_DBSETTINGS][HOST_CUSTOM_APPS] ); 
			foreach ( $app_list as $APP_ID ) 
				$result[HOST_APPLICATIONS][trim($APP_ID)] = array( HOST_APP_ID=> trim($APP_ID));
			}
			
		$filePath = WBS_DIR."kernel/hosting_plans.php";

		if ( file_exists($filePath) )
			@include_once ($filePath);
				
		// get paid application from the hosting_plans.php file  
		//
		if(!empty($result[HOST_DBSETTINGS][HOST_PLAN_DB]) and $result[HOST_DBSETTINGS][HOST_PLAN_DB] != HOST_CUSTOM_PLAN and $result[HOST_DBSETTINGS][HOST_PLAN_DB] != HOST_DEFAULT_PLAN and isset($mt_hosting_plan_settings[$result[HOST_DBSETTINGS][HOST_PLAN_DB]])) 
			{
 
			foreach ($mt_hosting_plan_settings[$result[HOST_DBSETTINGS][HOST_PLAN_DB]] as $apple => $value) 
				{
				if ( $apple != AA_APP_ID )
					{
					$result[HOST_APPLICATIONS][trim($apple)] = array( HOST_APP_ID=> trim($apple));
					}
				}
 			}

		
		// for old billing and custom plan 
		//
		
		if ( !( $hostapplications = xpath_eval($xpath, "/".HOST_DATABASE."/".HOST_APPLICATIONS."/".HOST_APPLICATION) ) )
			if ( !is_null($kernelStrings) )
				return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
			else
				return PEAR::raiseError( $commonErrorMsg );

		
		foreach ( $hostapplications->nodeset as $node )
		{
			$APP_ID = $node->get_attribute(HOST_APP_ID);

			$appOptions = array();
			if ( ( $optionpath = xpath_eval($xpath, HOST_SETTINGS."/".HOST_OPTION, $node ) ) )
			{
				foreach ( $optionpath->nodeset as $optionNode )
					$appOptions[$optionNode->get_attribute(HOST_OPTION_NAME)] = $optionNode->get_content();
			}

			$result[HOST_APPLICATIONS][$APP_ID] = array( HOST_APP_ID=>$APP_ID, HOST_SETTINGS=>$appOptions );
		}
 
		if ( !( $administrator = xpath_eval($xpath, "/".HOST_DATABASE."/".HOST_ADMINISTRATOR) ) )
			if ( !is_null($kernelStrings) )
				return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
			else
				return PEAR::raiseError( $commonErrorMsg );

		if ( !count($administrator->nodeset) )
			if ( !is_null($kernelStrings) )
				return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
			else
				return PEAR::raiseError( $commonErrorMsg );

		$administrator = $administrator->nodeset[0];
		$result[HOST_ADMINISTRATOR] = getAttributeValues( $administrator );

		$firstLogin = xpath_eval($xpath, "/".HOST_DATABASE."/".HOST_FIRSTLOGIN);
		if (count($firstLogin->nodeset)) {
			$firstLogin = $firstLogin->nodeset[0];

			$result[HOST_FIRSTLOGIN] = getAttributeValues( $firstLogin );
		}

		// --- Added by John
		// Global modules from wbs.xml
		$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );
		$dom1 = @domxml_open_file( realpath($filePath) );
		if ( !$dom1 )
			return PEAR::raiseError( $kernelStrings['app_openverfile_message'], ERRCODE_APPLICATION_ERR );

		$xpath1 = xpath_new_context($dom1);
		if ( ( $modules = xpath_eval($xpath1, '/WBS/'.HOST_MODULES.'/'.HOST_ASSIGN) ) ) {
			loadModulesFromNodeset($modules->nodeset, $result[HOST_MODULES]);
			
		} else 
			$result[HOST_MODULES]	= array();
			
		// Load modules from dbkey
		if ($modules = xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_MODULES."/".HOST_ASSIGN ))
			loadModulesFromNodeset($modules->nodeset, $result[HOST_MODULES]);
		// ---
		
		// Commented out by John	
/*			
		$result[HOST_MODULES] = array();
		$modules = xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_MODULES."/".HOST_ASSIGN )
		if ( count( $modules->nodeset ) )
		{
			foreach( $modules->nodeset as $module )
			{
				$params = getXMLAttributes( $module );
				$WBS_MODULES->setDefaultModule( $params["CLASS"], $params["ID"] );

				$result[HOST_MODULES][$params["CLASS"]]["ID"] = $params["ID"];
				$result[HOST_MODULES][$params["CLASS"]]["DISABLED"] = 0;

				$WBS_MODULES->enableClass( $params["CLASS"] );

				if ( isset( $params["DISABLED"] ) && $params["DISABLED"] == 1 )
				{
					$WBS_MODULES->disableClass( $params["CLASS"] );
					$result[HOST_MODULES][$params["CLASS"]]["DISABLED"] = 1;
				}
			}
		}
*/		
		$result[HOST_BALANCE] = array();
		$balance = xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_BALANCE."/".HOST_VALUE );

		if ( count( $balance->nodeset ) )
		{
			foreach( $balance->nodeset as $value )
			{
				$params = getXMLAttributes( $value );

				$result[HOST_BALANCE][$params["ID"]]["VALUE"] = ( $params["VALUE"] == "UNLIMITED" ) ? "UNLIMITED" : floatval( $params["VALUE"] );
			}
		}
		
		$loginHash = xpath_eval($xpath, "/".HOST_DATABASE."/" . HOST_LOGINHASHES . "/" . HOST_LOGINHASH . "[@UNCONFIRMED=1]");
		if (count($loginHash->nodeset)) {
			$result[HOST_UNCONFIRMED] = true;
		}
		
		// Load adv params
		$paramNodes = xpath_eval($xpath, "/".HOST_DATABASE."/" . HOST_ADVSETTINGS . "/" . HOST_ADVPARAM);
		$advsettings = array ();
		if ($paramNodes && count($paramNodes->nodeset)) {
			foreach ($paramNodes->nodeset as $cNode) {
				$name = $cNode->get_attribute("name");
				$value = $cNode->get_attribute("value");
				$advsettings[$name] = $value;
			}
		}
		$result[HOST_ADVSETTINGS] = $advsettings;
		return $result;
	}

	function writeHostDataFileParameter( $path, $attrName, $attrValue, $kernelStrings )
	//
	// Saves parameter to host information data file
	//
	//		Parameters:
	//			$path - path to document node
	//			$attrName - attribute name
	//			$attrValue - attribute value
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $DB_KEY;
		global $silentMode;
        
		$filePath = fixPathSlashes( sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DB_KEY)) );

		if ( !file_exists($filePath) )
			return PEAR::raiseError( $kernelStrings[ERR_XML]." ".$filePath );

		$dom = domxml_open_file( realpath($filePath) );
		if ( !$dom )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$xpath = xpath_new_context($dom);

		$prevModeValue = $silentMode;
		$silentMode = true;
		$node = &xpath_eval( $xpath, $path );
		$silentMode = $prevModeValue;

		if ( !$node || !isset($node->nodeset[0]) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$node->nodeset[0]->set_attribute( $attrName, $attrValue );

		@$dom->dump_file($filePath, false, true);

		return null;
	}

	function getHostApplications()
	//
	// Returns list of applications available for host
	//
	//		Returns array of application identifiers, or PEAR_Error
	//
	{
		global $databaseInfo;

		$appList = $databaseInfo[HOST_APPLICATIONS];
		
		$result = array();
		foreach( $appList as $APP_ID=>$APP_DATA )
			$result[] = $APP_ID;

		return $result;
	}

	function checkFormProfileData( $action, &$dbSettingsData, &$accountData, &$adminData, $dbCreateData, $kernelStrings, &$invalidArr, $skipAdminPassword = false, $DB_KEY = null )
	//
	// Checks database profile data entered by user. Prepares data for future use
	//
	//		Parameters:
	//			$action - action type - addition ($action = ACTION_NEW) or modification ($action = ACTION_EDIT)
	//			$accountData - user account information
	//			$dbSettingsData - database settings information
	//			$adminData - administrator account information
	//			$dbCreateData - database creation information
	//			$kernelStrings - Kernel localization strings
	//			$invalidArr - index of array containing invalid data (0 for dbSettingsData, 1 for accountData, 2 for adminData, 3 for dbCreateData)
	//			$skipAdminPassword - do not check if administrator password is filled
	//			$DB_KEY - database key
	//
	//		Returns null or PEAR::Error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;
		global $dateFormats;

		if ( $action == ACTION_NEW ) {
			if ( !isset($dbCreateData[HOST_CREATE_OPTION]) || !strlen($dbCreateData[HOST_CREATE_OPTION]) ) {
				$invalidArr = 3;
				$invalidField = HOST_CREATE_OPTION;

				return PEAR::raiseError( $kernelStrings['app_makedbnoopt_message'], SOAPROBOT_ERR_DATABASEOPTION, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
			}

			if ( $dbCreateData[HOST_CREATE_OPTION] == DB_CREATION_NEW ) {
				// Check database name parameter
				//
				$requiredFields = array(HOST_DATABASE_USER_NEW, /*HOST_PASSWORD_NEW, */HOST_DATABASE_NEW);

				if ( PEAR::isError( $invalidField = findEmptyField($dbCreateData, $requiredFields) ) ) {
					$invalidField->message = sprintf( $kernelStrings[ERR_REQUIREDFIELDS], $invalidField->getUserInfo() );
					$invalidField->code = SOAPROBOT_ERR_EMPTYFIELD;
					$invalidArr = 3;

					return $invalidField;
				}

				if ( $invalidField = checkStringLengths($dbCreateData, array(HOST_DATABASE_NEW), array(DB_MAXDBKEYLEN)) ) {
					$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];
					$invalidField->code = SOAPROBOT_ERR_INVALIDFIELDLENGTH;
					$invalidArr = 3;

					return $invalidField;
				}

				$userExists = dbUserExists( $dbCreateData[HOST_DATABASE_USER_NEW], $dbSettingsData[HOST_SQLSERVER], $kernelStrings );
				db_connect();
				if ( PEAR::isError($userExists) )
					return $userExists;

				if ( $userExists ) {
					$invalidArr = 3;

					return PEAR::raiseError( $kernelStrings['app_mysqluserexists_message'],
												SOAPROBOT_ERR_DATABASEOPTION, $_PEAR_default_error_mode, $_PEAR_default_error_options,
												HOST_DATABASE_USER_NEW );
				}

				if ( PEAR::isError( $invalidField = checkFieldInvalidSymbols($dbCreateData, array(HOST_DATABASE_EXISTING), KEY_SYMBOLS) ) ) {
					$invalidField->message = sprintf( $kernelStrings['app_invfieldchars_message'], $invalidField->getUserInfo() );
					$invalidField->code = SOAPROBOT_ERR_INVALIDCHARS;
					$invalidArr = 3;

					return $invalidField;
				}

				if ( !checkIDSymbols($dbCreateData[HOST_DATABASE_NEW][0], DBNAME_SYMBOLS) ) {
					$invalidArr = 3;

					return PEAR::raiseError ( $kernelStrings['app_invdbnameformat_message'],
												SOAPROBOT_ERR_DBKEYFORMAT,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												HOST_DATABASE_NEW );
				}

				$res = db_exists($dbCreateData[HOST_DATABASE_NEW], $kernelStrings, $dbSettingsData[HOST_SQLSERVER]);
				db_connect();
				if ( PEAR::isError($res) )
					return $res;

				if ( $res ) {
					$invalidArr = 3;

					return PEAR::raiseError( $kernelStrings['app_dbnameexists_message'],
												SOAPROBOT_ERR_DBEXISTS,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												HOST_DATABASE_NEW );
				}
			} else {
				// Check database name parameter
				//
				$requiredFields = array(HOST_DATABASE_EXISTING);

				if ( PEAR::isError( $invalidField = findEmptyField($dbCreateData, $requiredFields) ) ) {
					$invalidField->message = sprintf( $kernelStrings[ERR_REQUIREDFIELDS], $invalidField->getUserInfo() );
					$invalidField->code = SOAPROBOT_ERR_EMPTYFIELD;
					$invalidArr = 3;

					return $invalidField;
				}

				if ( $invalidField = checkStringLengths($dbCreateData, array(HOST_DATABASE_EXISTING), array(DB_MAXDBKEYLEN)) ) {
					$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];
					$invalidField->code = SOAPROBOT_ERR_INVALIDFIELDLENGTH;
					$invalidArr = 3;

					return $invalidField;
				}

				// Check if selected database is exists and empty
				//
				global $databaseInfo;

				$databaseInfo = array();
				$databaseInfo[HOST_DB_CREATE_OPTION] = $dbCreateData[HOST_CREATE_OPTION];
				$databaseInfo[HOST_DBUSER] = $dbCreateData[HOST_DATABASE_USER_EXISTING];
				$databaseInfo[HOST_DBPASSWORD] = $dbCreateData[HOST_PASSWORD_EXISTING];

				$databaseInfo[HOST_DBSETTINGS] = $databaseInfo;

				$res = metadata_exists( $dbCreateData[HOST_DATABASE_EXISTING], $kernelStrings, $dbSettingsData[HOST_SQLSERVER] );
				if ( PEAR::isError($res) ) {
					$invalidArr = 3;

					return PEAR::raiseError( $res->getMessage(),
												ERRCODE_APPLICATION_ERR,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												HOST_DATABASE_EXISTING );
				}

				if ( $res ) {
					$invalidArr = 3;

					return PEAR::raiseError( $kernelStrings['app_usingnotemptydb_message'],
												ERRCODE_APPLICATION_ERR,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												HOST_DATABASE_EXISTING );
				}
			}

			// Check Database Key parameter
			//
			if ( isset($dbSettingsData[HOST_DB_KEY]) && strlen($dbSettingsData[HOST_DB_KEY]) ) {
				$dbSettingsData[HOST_DB_KEY] = trim(strtoupper($dbSettingsData[HOST_DB_KEY]));

				if ( checkDBKey($dbSettingsData[HOST_DB_KEY], $kernelStrings ) ) {
					$invalidArr = 0;
					return PEAR::raiseError( $kernelStrings['app_dbkeyexists_message'],
												SOAPROBOT_ERR_DBKEYEXISTS,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												HOST_DB_KEY );
				}

				if ( PEAR::isError( $invalidField = checkFieldInvalidSymbols($dbSettingsData, array(HOST_DB_KEY), KEY_SYMBOLS) ) ) {
					$invalidField->message = sprintf( $kernelStrings['app_invfieldchars_message'], $invalidField->getUserInfo() );
					$invalidField->code = SOAPROBOT_ERR_INVALIDCHARS;
					$invalidArr = 0;

					return $invalidField;
				}

				if ( !checkIDSymbols($dbSettingsData[HOST_DB_KEY][0],DBNAME_SYMBOLS/* ALPHA_SYMBOLS*/) ) {
					$invalidArr = 0;
					$invalidField = HOST_DB_KEY;

					return PEAR::raiseError ( $kernelStrings['app_invdbkeyformat_message'], SOAPROBOT_ERR_DBKEYFORMAT, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
				}

			}

			if ( !$skipAdminPassword ) {
				/*$requiredFields = array("PASSWORD1", "PASSWORD2");

				if ( PEAR::isError( $invalidField = findEmptyField($adminData, $requiredFields) ) ) {
					$invalidField->message = sprintf( $kernelStrings[ERR_REQUIREDFIELDS], $invalidField->getUserInfo() );
					$invalidField->code = SOAPROBOT_ERR_EMPTYFIELD;
					$invalidArr = 2;

					return $invalidField;
				} */
			}
		} else {
			// Check database connection parameters
			//
			$existingDatabaseInfo = loadHostDataFile($DB_KEY, $kernelStrings);
			$createDate = $existingDatabaseInfo[HOST_DBSETTINGS][HOST_CREATEDATE];

			if ( strlen($createDate) ) {
				$res = checkMySQLConnectionParams( $dbSettingsData[HOST_DBUSER], $dbSettingsData[HOST_DBPASSWORD], $dbSettingsData[HOST_SQLSERVER], $kernelStrings );
				db_connect();

				if ( PEAR::isError($res) ) {
					$invalidArr = 3;

					return PEAR::raiseError ( $res->getMessage(),
												SOAPROBOT_ERR_DBKEYFORMAT,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												HOST_DBUSER );
				}
			} else {
				if(isset($dbSettingsData[HOST_DB_CREATE_OPTION])){
					$createOpt = $dbSettingsData[HOST_DB_CREATE_OPTION];
				}else{
					$createOpt = $existingDatabaseInfo[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION];
				}

				if ( $createOpt == DB_CREATION_NEW ) {
					$userExists = dbUserExists( $dbSettingsData[HOST_DBUSER], $dbSettingsData[HOST_SQLSERVER], $kernelStrings );
					db_connect();
					if ( PEAR::isError($userExists) )
						return $userExists;

					if ( $userExists ) {
						$invalidArr = 0;

						return PEAR::raiseError( $kernelStrings['app_mysqluserexists_message'],
													SOAPROBOT_ERR_DATABASEOPTION, $_PEAR_default_error_mode, $_PEAR_default_error_options,
													HOST_DBUSER );
					}
				} else {
					global $databaseInfo;

					$databaseInfo = array();
					$databaseInfo[HOST_DB_CREATE_OPTION] =$dbCreateData[HOST_CREATE_OPTION];
					$databaseInfo[HOST_DBUSER] = $dbCreateData[HOST_DATABASE_USER_EXISTING];
					$databaseInfo[HOST_DBPASSWORD] = $dbCreateData[HOST_PASSWORD_EXISTING];
					$databaseInfo[HOST_DBSETTINGS] = $databaseInfo;

					$res = metadata_exists( $dbCreateData[HOST_DATABASE_EXISTING], $kernelStrings, $dbSettingsData[HOST_SQLSERVER] );
					db_connect();
					if ( PEAR::isError($res) ) {
						$invalidArr = 3;

						return PEAR::raiseError( $res->getMessage(),
													ERRCODE_APPLICATION_ERR,
													$_PEAR_default_error_mode,
													$_PEAR_default_error_options,
													HOST_DATABASE_EXISTING );
					}

					if ( $res ) {
						$invalidArr = 3;

						return PEAR::raiseError( $kernelStrings['app_usingnotemptydb_message'],
													ERRCODE_APPLICATION_ERR,
													$_PEAR_default_error_mode,
													$_PEAR_default_error_options,
													HOST_DATABASE_EXISTING );
					}
				}
			}
		}

		if ( $action == ACTION_NEW || (strlen($adminData["PASSWORD1"]) || strlen($adminData["PASSWORD2"])) ) {
			if ( !$skipAdminPassword ) {
				if( $adminData["PASSWORD1"] != $adminData["PASSWORD2"] ) {
					$invalidArr = 2;
					$invalidField = "PASSWORD1";

					return PEAR::raiseError ( $kernelStrings['app_passmismatch_message'], SOAPROBOT_ERR_PASSWORDMISMATCH, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
				}

				if ( strlen( $adminData["PASSWORD1"] ) < MIN_PASSWORD_LEN ) {
					$invalidArr = 2;
					$invalidField = "PASSWORD1";

					return PEAR::raiseError ( sprintf($kernelStrings['app_invpwdlen_message'], MIN_PASSWORD_LEN), SOAPROBOT_ERR_PASSWORDLENGTH, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
				}
			}

			$adminData[HOST_PASSWORD] = md5( $adminData["PASSWORD1"] );
		} else
			$adminData[HOST_PASSWORD] = null;

		// Check user account data
		//
		//
		$requiredFields = array(HOST_LOGINNAME, HOST_FIRSTNAME, HOST_EMAIL, HOST_COMPANYNAME, /*HOST_LASTNAME,*/  );
		//$requiredFields = array( HOST_LOGINNAME );

		if ( $action == ACTION_NEW )
			$requiredFields = array_merge( $requiredFields, array("PASSWORD1", "PASSWORD2") );

		if ( PEAR::isError( $invalidField = findEmptyField($accountData, $requiredFields) ) ) {
			$invalidField->message = sprintf( $kernelStrings[ERR_REQUIREDFIELDS], $invalidField->getUserInfo() );
			$invalidField->code = SOAPROBOT_ERR_EMPTYFIELD;
			$invalidArr = 1;

			return $invalidField;
		}
		
		$requiredFields = array( HOST_LOGINNAME );

		if ( PEAR::isError( $invalidField = checkFieldInvalidSymbols( $accountData, $requiredFields, ID_SYMBOLS.",. ", array(HOST_EMAIL) ) ) ) {
			$invalidField->message = sprintf( $kernelStrings['app_invfieldchars_message'], $invalidField->getUserInfo() );
			$invalidField->code = SOAPROBOT_ERR_INVALIDCHARS;
			$invalidArr = 1;

			return $invalidField;
		}

		if ( !checkIDSymbols( $accountData[HOST_EMAIL], ID_SYMBOLS."@" ) ) {
			$errorStr = $hr_kernelStrings['app_invfieldchars_message'];
			$invalidField = HOST_EMAIL;
			$invalidArr = 1;

			return PEAR::raiseError ( $errorStr, SOAPROBOT_ERR_INVALIDCHARS, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
		}

		if ( !checkIDSymbols( $accountData[HOST_LOGINNAME], ID_SYMBOLS ) ) {
			$errorStr = $hr_kernelStrings[238];
			$invalidField = HOST_LOGINNAME;
			$invalidArr = 1;

			return PEAR::raiseError ( $errorStr, SOAPROBOT_ERR_INVALIDLOGINCHARS, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
		}

		if ( !checkIDSymbols($accountData[HOST_LASTNAME], ALPHA_SYMBOLS.'- ') || substr($accountData[HOST_LASTNAME], 0, 1) === '-' || substr($accountData[HOST_LASTNAME], 0, 1) === ' ' ) {
			$invalidArr = 1;
			$invalidField = HOST_LASTNAME;

			return PEAR::raiseError ( $kernelStrings['app_invnameformat_message'], SOAPROBOT_ERR_INVALIDNAMESTARTCHARS, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
		}

		if ( !checkIDSymbols($accountData[HOST_FIRSTNAME], ALPHA_SYMBOLS.'- ') || substr($accountData[HOST_FIRSTNAME], 0, 1) === '-' || substr($accountData[HOST_LASTNAME], 0, 1) === ' ' ) {
			$invalidArr = 1;
			$invalidField = HOST_FIRSTNAME;

			return PEAR::raiseError ( $kernelStrings['app_invnameformat_message'], SOAPROBOT_ERR_INVALIDNAMESTARTCHARS, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
		}

		$ownerFields = array( HOST_COMPANYNAME, HOST_FIRSTNAME, HOST_LASTNAME, HOST_EMAIL, HOST_LOGINNAME );
		$invalidField = checkFieldInvalidSymbols( $accountData, $ownerFields, ID_SYMBOLS."_.@-, &/\\'|[]{}" );
		if ( PEAR::isError($invalidField) ) {
			$invalidField->message = sprintf( $kernelStrings['app_invfieldchars_message'], $invalidField->getUserInfo() );
			$invalidField->code = SOAPROBOT_ERR_INVALIDCHARS;
			$invalidArr = 1;

			return $invalidField;
		}

		if ( $action == ACTION_NEW || (strlen($accountData["PASSWORD1"]) || strlen($accountData["PASSWORD2"])) ) {
			if( $accountData["PASSWORD1"] != $accountData["PASSWORD2"] ) {
				$invalidArr = 1;
				$invalidField = "PASSWORD1";

				return PEAR::raiseError ( $kernelStrings['app_passmismatch_message'], SOAPROBOT_ERR_PASSWORDMISMATCH, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
			}

			if ( strlen( $accountData["PASSWORD1"] ) < MIN_PASSWORD_LEN ) {
				$invalidArr = 1;
				$invalidField = "PASSWORD1";

				return PEAR::raiseError ( sprintf($kernelStrings['app_invpwdlen_message'], MIN_PASSWORD_LEN), SOAPROBOT_ERR_PASSWORDLENGTH, $_PEAR_default_error_mode, $_PEAR_default_error_options, $invalidField );
			} 

			$accountData[HOST_PASSWORD] = md5( $accountData["PASSWORD1"] );
		} else
			$accountData[HOST_PASSWORD] = null;

		// Check database settings data
		//
		$invalidField = checkDateFields( $dbSettingsData, array( HOST_EXPIRE_DATE ), $dbSettingsData );
		if ( PEAR::isError( $invalidField ) ) {
			$invalidField->message = sprintf($kernelStrings[ERR_DATEFORMAT], $dateFormats[DATE_DISPLAY_FORMAT]);
			$invalidField->code = SOAPROBOT_ERR_DATEFORMAT;
			$invalidArr = 0;

			return $invalidField;
		}

		$intFields = array( HOST_SMS_RECIPIENTSLIMIT );

		$invalidField = checkIntegerFields( $dbSettingsData, $intFields, $kernelStrings );
		if ( PEAR::isError($invalidField) ) {
			$invalidArr = 0;
			$invalidField->code = SOAPROBOT_ERR_INTFORMAT;

			return $invalidField;
		}

		return null;
	}

	function addModDBProfile( $action, $DB_KEY, $dbSettingsData, $accountData, $adminData, $dbCreateData, $kernelStrings, &$invalidArr, $appList, $signup_datetime = null, $signup_source = null, $skipAdminPassword = false )
	//
	// Creates/modifies database profile XML file
	//
	//		Parameters:
	//			$action - action type - addition ($action = ACTION_NEW) or modification ($action = ACTION_EDIT)
	//			$DB_KEY - database key
	//			$accountData - user account information
	//			$adminData - administrator account information
	//			$dbSettingsData - database settings information
	//			$dbCreateData - database creation information
	//			$kernelStrings - Kernel localization strings
	//			$invalidArr - index of array containing invalid data (0 for dbSettingsData, 1 for accountData, 2 for adminData)
	//			$appList - application list
	//			$signup_datetime - signup date and time. Used only in New mode
	//			$signup_source - record source (null for wbs admin)
	//			$skipAdminPassword - do not check if administrator password is filled
	//
	//		Returns new database key if action = new, null if action = edit and PEAR::Error in case of error
	//
	{
		$dbSettingsData = trimArrayData( $dbSettingsData );
		$accountData = trimArrayData( $accountData );
		$adminData = trimArrayData( $adminData );

		$invalidArr = 0;
		$res = checkFormProfileData( $action, $dbSettingsData, $accountData, $adminData, $dbCreateData, $kernelStrings, $invalidArr, $skipAdminPassword, $DB_KEY );
		if ( PEAR::isError($res) )
			return $res;

		$ip = null;
		if ( isset( $accountData['SIGNUP_IP'] ) )
			$ip = $accountData['SIGNUP_IP'];

		if ( $action == ACTION_EDIT ) {

			$res = modifyDatabaseProfile( $DB_KEY, $dbSettingsData, $accountData, $adminData, $kernelStrings, $appList, $ip );

			if ( PEAR::isError($res) )
				return $res;
		} else {
			$signup_datetime = time();

			return createDatabaseProfile( $dbSettingsData, $accountData, $adminData, $dbCreateData, $kernelStrings, $appList, $signup_datetime, $signup_source, $ip );
		}

		return null;
	}

	function genDatabaseKey( $dbSettingsData, $kernelStrings, $accountData, $signup_datetime )
	//
	// Generates new database key
	//
	//		Parameters:
	//			$dbSettingsData - database settings information
	//			$kernelStrings - Kernel localization strings
	//			$accountData - user account information
	//			$signup_datetime - signup date and time
	//
	//		Returns string or PEAR_Error
	//
	{
		clearstatcache();

		$prefix = "";
		$prefix = strtoupper( substr( $accountData[HOST_FIRSTNAME], 0, 1 ) );

		if ( strlen($accountData[HOST_LASTNAME]) )
			$prefix .= strtoupper( substr( $accountData[HOST_LASTNAME], 0, 1 ) );
		else
			$prefix .= chr(rand(65, 90));

		$timeData = localtime( $signup_datetime, true );

		$datePart = (int)$timeData["tm_mday"] + (int)$timeData["tm_mon"];
		if ( $datePart  < 10 )
			$datePart = "0".$datePart;

		$timePart = (int)$timeData["tm_hour"] + (int)$timeData["tm_min"];
		if ( $timePart < 10 )
			$timePart = "0".$timePart;

		$intPart = $datePart.$timePart;

		do {
			$result = $prefix.$intPart;
			$keyExists = checkDBKey( $result, $kernelStrings, $dbSettingsData );

			if ( $keyExists ) {
				$intPart++;
				if ( $intPart < 1000 )
					$intPart = "0".$intPart;
			}
		} while ( $keyExists );

		return $result;
	}

	function generateUserPassword( $length )
	//
	// Generates temporary user passwrod
	//
	//		Parameters:
	//			$length - password length
	//
	//		Returns string
	//
	{
		$strLen = strlen(ALPHA_SYMBOLS);
		$symbols = ALPHA_SYMBOLS;
		$result = "";

		for ( $i = 1; $i <= $length; $i++ ) {
			$charPos = rand( 0, $strLen-1 );
			$char = $symbols{$charPos};
			if ( rand(1,2) == 1  )
				$char = strtoupper($char);
			$result .= $char;
		}

		return $result;
	}

	function createDatabaseProfile( $dbSettingsData, $accountData, $adminData, $dbCreateData, $kernelStrings, $appList, $signup_datetime, $signup_source = null, $ip = null  )
	//
	// Creates database profile file
	//
	//		Parameters:
	//			$accountData - user account information
	//			$dbSettingsData - database settings information
	//			$adminData - administrator settings information
	//			$dbCreateData - database creation information
	//			$kernelStrings - Kernel localization strings
	//			$signup_datetime - signup date time
	//			$signup_source - record source (null for wbs admin)
	//			$ip - ip address of remote host
	//
	//		Returns new database key or PEAR_Error
	//
	{
		if ( isset($dbSettingsData[HOST_DB_KEY]) && strlen($dbSettingsData[HOST_DB_KEY]) ) {
			$dbSettingsData[HOST_DB_KEY] = trim(strtoupper($dbSettingsData[HOST_DB_KEY]));

			if ( checkDBKey( $dbSettingsData[HOST_DB_KEY], $kernelStrings ) )
				return PEAR::raiseError( $kernelStrings['app_dbkeyexists_message'], SOAPROBOT_ERR_DBKEYEXISTS );

			$DB_KEY = $dbSettingsData[HOST_DB_KEY];
		} else
			$DB_KEY = genDatabaseKey( $dbSettingsData, $kernelStrings, $accountData, $signup_datetime );

		if ( PEAR::isError( $DB_KEY ) )
			return PEAR::raiseError( $DB_KEY->getMessage(), SOAPROBOT_ERR_DBPROFILECREATE );

		$destFilePath = sprintf("%s/%s.xml", WBS_DBLSIT_DIR, $DB_KEY);
		$srcFilePath = realpath( sprintf("%skernel/includes/dbinfo.xml", WBS_DIR) );

		if ( !@copy( $srcFilePath, $destFilePath ) )
			return PEAR::raiseError( sprintf($kernelStrings['app_copyfile_message'], basename($destFilePath) ), SOAPROBOT_ERR_DBPROFILECREATE );

		if ( $dbCreateData[HOST_CREATE_OPTION] == DB_CREATION_NEW ) {
			$dbCreateData[HOST_DATABASE] = $dbCreateData[HOST_DATABASE_NEW];
			$dbCreateData[HOST_PASSWORD] = $dbCreateData[HOST_PASSWORD_NEW];

			if ( $dbCreateData[HOST_PASSWORD] == md5('') )
				$dbCreateData[HOST_PASSWORD] == md5( generateUserPassword(5) );

			$dbCreateData[HOST_DATABASE_USER] = $dbCreateData[HOST_DATABASE_USER_NEW];
		} else {
			$dbCreateData[HOST_DATABASE] = $dbCreateData[HOST_DATABASE_EXISTING];
			$dbCreateData[HOST_PASSWORD] = $dbCreateData[HOST_PASSWORD_EXISTING];
			$dbCreateData[HOST_DATABASE_USER] = $dbCreateData[HOST_DATABASE_USER_EXISTING];
		}

		if ( !isset($dbSettingsData[HOST_DEFAULTENCODING]) )
			$dbSettingsData[HOST_DEFAULTENCODING] = null;

		$signup_dt = convertToSqlDateTime( $signup_datetime );

		$userLang = isset($accountData[HOST_LANGUAGE]) && strlen($accountData[HOST_LANGUAGE]) ? $accountData[HOST_LANGUAGE] : HOST_DEF_LANGUAGE;

		if(!isset($dbSettingsData['MYSQL_CHARSET'])||!$dbSettingsData['MYSQL_CHARSET']){

			global 	$wbs_sqlServers;
			$sqlServerParams = $wbs_sqlServers[$dbSettingsData[HOST_SQLSERVER]];
			$dbSettingsData['MYSQL_CHARSET'] = $sqlServerParams[WBS_DBCHARSET];
		}

		$nameMap = array( "%DB_PWD%" => $adminData[HOST_PASSWORD],
							"%DB_SUDT%" =>$signup_dt, "%DB_CTD%" => "", "%DB_EPD%" => $dbSettingsData[HOST_EXPIRE_DATE],
							"%DB_RO%" => $dbSettingsData[HOST_READONLY],
							"%DB_DF%" => $dbSettingsData[HOST_DATE_FORMAT],
							"%DT_DBL%" => $dbSettingsData[HOST_DBSIZE_LIMIT],
							"%REC_LIMIT%" => $dbSettingsData[HOST_RECIPIENTSLIMIT],
							"%TEMPORARY%" => $dbSettingsData[HOST_TEMPORARY],
							"%SMS_REC_LIMIT%" => $dbSettingsData[HOST_SMS_RECIPIENTSLIMIT],
							"%DEF_ENCODING%" => $dbSettingsData[HOST_DEFAULTENCODING],
							"%DB_MUC%" => $dbSettingsData[HOST_MAXUSERCOUNT],
							
							"%SET_PLAN%" => $dbSettingsData[HOST_PLAN_NAME],
							"%INSTALL_APPS%" => $dbSettingsData[HOST_INSTALL_APPS],
							
							"%DB_SOURCE%" => $signup_source,
							"%ADM_PWD%" => $adminData[HOST_PASSWORD],
							"%ADM_TPL%" => HOST_DEF_TEMPLATE, "%ADM_LANG%" => $adminData[HOST_LANGUAGE],
							"%FL_CMN%" => htmlentities($accountData[HOST_COMPANYNAME]),
							"%FL_FN%" => $accountData[HOST_FIRSTNAME],
							"%FL_LN%" => $accountData[HOST_LASTNAME],
							"%FL_LOGIN%" => strtoupper($accountData[HOST_LOGINNAME]),
							"%FL_PWD%" => $accountData[HOST_PASSWORD],
							"%FL_TPL%" => HOST_DEF_TEMPLATE,
							"%FL_LANG%" => $userLang,
							"%FL_EMAIL%" => $accountData[HOST_EMAIL],
							"%DB_SRV%" => $dbSettingsData[HOST_SQLSERVER],
							"%DB_NAME%" => $dbCreateData[HOST_DATABASE],
							"%DB_PASSWORD%" => $dbCreateData[HOST_PASSWORD],
							"%DB_USER%" => $dbCreateData[HOST_DATABASE_USER],
							"%DB_CREATE_OPTION%" => $dbCreateData[HOST_CREATE_OPTION],
							"%MYSQL_CHARSET%" => $dbSettingsData['MYSQL_CHARSET']
						);

		if ( !@applyFileNameMapping( $destFilePath, $nameMap ) )
			return PEAR::raiseError( sprintf($kernelStrings['app_makedbprof_message'], basename($destFilePath) ), SOAPROBOT_ERR_DBPROFILECREATE );

		$dom = domxml_open_file( realpath($destFilePath) );
		if ( !$dom )
			return PEAR::raiseError( $kernelStrings[ERR_XML], SOAPROBOT_ERR_DBPROFILECREATE );

		$xpath = xpath_new_context( $dom );

		
// FIXME aaa
		if(!onWebAsystServer()){//Create application list for OS version
			$nodeset = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_APPLICATIONS );
			if ( !$nodeset || !isset($nodeset->nodeset[0]) )
				return PEAR::raiseError( $kernelStrings[ERR_XML], SOAPROBOT_ERR_DBPROFILECREATE );

			$apps_node = $nodeset->nodeset[0];

			if ( !is_array($appList) )
				$appList = array();

			foreach( $appList as $APP_ID ) {
				$app_node = @create_addElement( $dom, $apps_node, HOST_APPLICATION );
				$app_node->set_attribute( APP_ID, $APP_ID );
			}
		}

		if ( isset( $dbSettingsData[HOST_MODULES] ) )
		{
			// Create modules settings
			//
			$modulesNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_MODULES );
			if ( !$modulesNode )
				return PEAR::raiseError( $kernelStrings[ERR_XML], SOAPROBOT_ERR_DBPROFILECREATE );

			$modulesNode = $modulesNode->nodeset[0];

			foreach( $dbSettingsData[HOST_MODULES] as $key=>$MOD )
			{
				$mod_node = create_addElement( $dom, $modulesNode, HOST_ASSIGN );

				$mod_node->set_attribute( "CLASS", $key );
				$mod_node->set_attribute( "ID", $MOD["ID"] );
				$mod_node->set_attribute( "DISABLED", $MOD["DISABLED"] );
			}
		}

		if ( isset( $dbSettingsData[HOST_BALANCE] ) )
		{
			$balanceNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_BALANCE );
			if ( !$balanceNode )
				return PEAR::raiseError( $kernelStrings[ERR_XML], SOAPROBOT_ERR_DBPROFILECREATE );

			$balanceNode = $balanceNode->nodeset[0];

			foreach( $dbSettingsData[HOST_BALANCE] as $key=>$value )
			{

				$mod_node = create_addElement( $dom, $balanceNode, HOST_VALUE );

				$mod_node->set_attribute( "ID", $key );
				$mod_node->set_attribute( "VALUE", $value[HOST_VALUE] );
			}
		}

		@$dom->dump_file( $destFilePath, false, true );

		// Log record
		//
		global $_SERVER;
		if ( is_null($ip) )
			$ip = $_SERVER['REMOTE_ADDR'];

		@logAccountOperation( $DB_KEY, $kernelStrings, aop_signup, $ip, null, $signup_source );

		return $DB_KEY;
	}

	function modifyDatabaseProfile( $DB_KEY, $dbSettingsData, $accountData, $adminData, $kernelStrings, $appList, $ip = null )
	//
	// Internal function used by addModDBProfile(). Updates data in database profile file
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$accountData - user account information
	//			$dbSettingsData - database settings information
	//			$adminData - administrator settings information
	//			$kernelStrings - Kernel localization strings
	//			$appList - application list
	//			$ip - ip address of remote host
	//
	//		Returns null or PEAR_Error
	//
	{
		$oldData = loadHostDataFile( $DB_KEY, $kernelStrings );
		if ( PEAR::isError($oldData) )
			return $oldData;

		if ( isset($oldData[HOST_DBSETTINGS][HOST_STATUS]) && $oldData[HOST_DBSETTINGS][HOST_STATUS] == HOST_STATUS_DELETED )
			return PEAR::raiseError( $kernelStrings['app_editdeldb_message'], ERRCODE_APPLICATION_ERR );

		$filePath = fixPathSlashes( sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DB_KEY)) );

		if ( !file_exists($filePath) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$dom = domxml_open_file( realpath($filePath) );
		if ( !$dom )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$xpath = xpath_new_context($dom);

		// Update database info
		//
		$dbNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_DBSETTINGS );
		if ( !$dbNode || !isset($dbNode->nodeset[0]) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		if (!count($dbNode->nodeset))
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		if ( !isset($dbSettingsData[HOST_DEFAULTENCODING]) )
			$dbSettingsData[HOST_DEFAULTENCODING] = null;

		$dbNode = $dbNode->nodeset[0];

		$dbNode->set_attribute( HOST_EXPIRE_DATE, $dbSettingsData[HOST_EXPIRE_DATE] );
		$dbNode->set_attribute( HOST_READONLY, $dbSettingsData[HOST_READONLY] );
		$dbNode->set_attribute( HOST_RECIPIENTSLIMIT, $dbSettingsData[HOST_RECIPIENTSLIMIT] );
		$dbNode->set_attribute( HOST_SMS_RECIPIENTSLIMIT, $dbSettingsData[HOST_SMS_RECIPIENTSLIMIT] );
		$dbNode->set_attribute( HOST_DEFAULTENCODING, $dbSettingsData[HOST_DEFAULTENCODING] );
		$dbNode->set_attribute( HOST_DBSIZE_LIMIT, $dbSettingsData[HOST_DBSIZE_LIMIT] );
		$dbNode->set_attribute( HOST_DATE_FORMAT, $dbSettingsData[HOST_DATE_FORMAT] );
		$dbNode->set_attribute( HOST_MAXUSERCOUNT, $dbSettingsData[HOST_MAXUSERCOUNT] );
		$dbNode->set_attribute( HOST_SQLSERVER, $dbSettingsData[HOST_SQLSERVER] );
		$dbNode->set_attribute( HOST_DBUSER, $dbSettingsData[HOST_DBUSER] );
		$dbNode->set_attribute( HOST_DBPASSWORD, $dbSettingsData[HOST_DBPASSWORD] );
		$dbNode->set_attribute( HOST_DBNAME, $dbSettingsData[HOST_DBNAME] );
		if($dbSettingsData[HOST_DB_CREATE_OPTION]){
			$dbNode->set_attribute( HOST_DB_CREATE_OPTION, $dbSettingsData[HOST_DB_CREATE_OPTION]=='new'?'new':'use' );
		}
		//HOST_DBNAME
		
		if ( isset($dbSettingsData[HOST_BILLINGDATE]) )
			$dbNode->set_attribute( HOST_BILLINGDATE, $dbSettingsData[HOST_BILLINGDATE] );

		// Update first login info
		//
		$accountNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_FIRSTLOGIN );
		if ( !$accountNode )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		if (!count($accountNode->nodeset))
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$accountNode = $accountNode->nodeset[0];

		$accountNode->set_attribute( HOST_COMPANYNAME, $accountData[HOST_COMPANYNAME] );
		$accountNode->set_attribute( HOST_FIRSTNAME, $accountData[HOST_FIRSTNAME] );
		$accountNode->set_attribute( HOST_LASTNAME, $accountData[HOST_LASTNAME] );
		$accountNode->set_attribute( HOST_EMAIL, $accountData[HOST_EMAIL] );
		$accountNode->set_attribute( HOST_LOGINNAME, $accountData[HOST_LOGINNAME] );

		if ( strlen($accountData[HOST_PASSWORD]) )
			$accountNode->set_attribute( HOST_PASSWORD, $accountData[HOST_PASSWORD] );

		// Update administrator info
		//
		$adminNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_ADMINISTRATOR );
		if ( !$adminNode )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		if (!count($adminNode->nodeset))
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$adminNode = $adminNode->nodeset[0];
		$adminNode->set_attribute( HOST_LANGUAGE, $adminData[HOST_LANGUAGE] );

		if ( strlen($adminData[HOST_PASSWORD]) )
			$adminNode->set_attribute( HOST_PASSWORD, $adminData[HOST_PASSWORD] );

		// Update application list
		//
		$applicationsNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_APPLICATIONS );
		if ( !$applicationsNode )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		if (!count($applicationsNode->nodeset))
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$applicationsNode = $applicationsNode->nodeset[0];

		$applicatonNodes = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_APPLICATIONS."/*" );
		if ( !$applicatonNodes )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$existingApp = array();

		if (count($applicatonNodes->nodeset))
		{
			foreach( $applicatonNodes->nodeset as $application )
			{
				if ( in_array($application->get_attribute(APP_ID), $appList) )
				{
					$existingApp[] = $application->get_attribute(APP_ID);
					continue;
				}

				if ( !( $applicationsNode->remove_child($application) ) )
					return PEAR::raiseError( $kernelStrings[ERR_XML] );
			}
		}

		foreach( $appList as $APP_ID ) {
			if ( in_array($APP_ID, $existingApp) )
				continue;

			$app_node = @create_addElement( $dom, $applicationsNode, HOST_APPLICATION );
			$app_node->set_attribute( APP_ID, $APP_ID );
		}

		// Update modules settings
		//
		$modulesNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_MODULES );

		if ( !$modulesNode || !count( $modulesNode->nodeset ) )
		{
			$dbNode = &xpath_eval( $xpath, "/".HOST_DATABASE );

			if ( !$dbNode )
				return PEAR::raiseError( $kernelStrings[ERR_XML] );

			if (!count($dbNode->nodeset))
				return PEAR::raiseError( $kernelStrings[ERR_XML] );

			$dbNode = $dbNode->nodeset[0];

			$modulesNode = @create_addElement( $dom, $dbNode, HOST_MODULES );
		}
		else
			$modulesNode = $modulesNode->nodeset[0];


		$modulesNodes = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_MODULES."/*" );
		if ( !$modulesNodes )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		if (count($modulesNodes->nodeset))
			foreach( $modulesNodes->nodeset as $module )
				if ( !( $modulesNode->remove_child($module) ) )
					return PEAR::raiseError( $kernelStrings[ERR_XML] );


		if ( isset( $dbSettingsData[HOST_MODULES] ) && is_array( $dbSettingsData[HOST_MODULES] ) )
		{
			foreach( $dbSettingsData[HOST_MODULES] as $key=>$MOD )
			{
				$mod_node = create_addElement( $dom, $modulesNode, HOST_ASSIGN );

				$mod_node->set_attribute( "CLASS", $key );
				$mod_node->set_attribute( "ID", $MOD["ID"] );
				$mod_node->set_attribute( "DISABLED", $MOD["DISABLED"] );
			}
		}

		if ( isset( $dbSettingsData[HOST_BALANCE] ) )
		{
			$balanceNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_BALANCE );

			if ( !$balanceNode || !count( $balanceNode->nodeset ) )
			{
				$dbNode = &xpath_eval( $xpath, "/".HOST_DATABASE );

				if ( !$dbNode )
					return PEAR::raiseError( $kernelStrings[ERR_XML] );

				if (!count($dbNode->nodeset))
					return PEAR::raiseError( $kernelStrings[ERR_XML] );

				$dbNode = $dbNode->nodeset[0];

				$balanceNode = @create_addElement( $dom, $dbNode, HOST_BALANCE );
			}
			else
				$balanceNode = $balanceNode->nodeset[0];

			$balanceNodes = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_BALANCE."/*" );
			if ( !$balanceNodes )
				return PEAR::raiseError( $kernelStrings[ERR_XML] );

			if (count($balanceNodes->nodeset))
				foreach( $balanceNodes->nodeset as $module ) {
					if ( !( $balanceNode->remove_child($module) ) )
						return PEAR::raiseError( $kernelStrings[ERR_XML] );
			}

			foreach( $dbSettingsData[HOST_BALANCE] as $key=>$value )
			{
				$mod_node = create_addElement( $dom, $balanceNode, HOST_VALUE );

				$mod_node->set_attribute( "ID", $key );
				$mod_node->set_attribute( "VALUE", $value[HOST_VALUE] );
			}
		}

		@$dom->dump_file($filePath, false, true);

		if ( $oldData[HOST_DBSETTINGS][HOST_CREATEDATE] ) {
			$oldApplications = array_keys( $oldData[HOST_APPLICATIONS] );
			$newApplications = $appList;

			$installApps = array_diff( $newApplications, $oldApplications );
 
			if ( count($installApps) ) {
				$res = installApplications( $DB_KEY, $installApps, $kernelStrings, $oldData );
				if ( PEAR::isError($res) )
					return $res;
			}

			if ( $oldData[HOST_DBSETTINGS][HOST_READONLY] != $dbSettingsData[HOST_READONLY] )
				$res = updateDatabaseReadonlyFlag( $DB_KEY, $dbSettingsData[HOST_READONLY], $kernelStrings, $oldData[HOST_DBSETTINGS][HOST_PASSWORD] );
		}

		// Log record
		//
		global $_SERVER;
		if ( is_null($ip) )
			$ip = $_SERVER['REMOTE_ADDR'];

		@logAccountOperation( $DB_KEY, $kernelStrings, aop_modify, $ip, $oldData );

		return null;
	}

	function getApplicationVariable( $DB_KEY, $APP_ID, $varName, $kernelStrings)
	//
	// Gets application settings parameter
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$APP_ID - application ID
	//			$varName - name of parameter
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or parameter value
	//
	{
		$filePath = sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DB_KEY) );
		$dom = @domxml_open_file( realpath($filePath) );
		if ( !$dom )
			return null;

		$xpath = xpath_new_context($dom);

		if ( !( $optionpath = xpath_eval($xpath, "/".HOST_DATABASE."/".HOST_APPLICATIONS."/".HOST_APPLICATION."[@".HOST_APP_ID."='$APP_ID']/".HOST_SETTINGS."/".HOST_OPTION."[@".HOST_OPTION_NAME."='$varName']" ) ) )
			return null;

		foreach ( $optionpath->nodeset as $node )
		{
			if ( $node->get_attribute( HOST_OPTION_NAME )  == $varName )
				return $node->get_content();
		}

		return null;
	}

	function setDBProfileParameter( $DB_KEY, $kernelStrings, $path, $paramName, $paramValue )
	//
	// Writes parameter to database profile xml-file
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$kernelStrings - Kernel localization strings
	//			$path - path to parameter parent node
	//			$parameterName - the name of parameter
	//			$paramValue - parameter value
	//
	//		Returns null or PEAR_Error
	//
	{
		$filePath = fixPathSlashes( sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DB_KEY)) );

		if ( !file_exists($filePath) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$dom = domxml_open_file( realpath($filePath) );
		if ( !$dom )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$xpath = xpath_new_context($dom);

		$node = &xpath_eval( $xpath, $path );
		if ( !$node )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		if (!count($node->nodeset))
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$node = $node->nodeset[0];

		$node->set_attribute( $paramName, $paramValue );

		@$dom->dump_file($filePath, false, true);
		
		$cacheFilePath = fixPathSlashes( sprintf( "%s/scdb/.settings.%s", WBS_TEMP_DIR, strtoupper($DB_KEY)) );
		if(file_exists($cacheFilePath)){
			@unlink($cacheFilePath);
		}

		return null;
	}

	function deleteDbProfile( $DB_KEY, $kernelStrings )
	//
	// Marks database as deleted
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		$oldData = loadHostDataFile( $DB_KEY, $kernelStrings );
		if ( PEAR::isError($oldData) )
			return $oldData;

		if ( isset($oldData[HOST_DBSETTINGS][HOST_STATUS]) && $oldData[HOST_DBSETTINGS][HOST_STATUS] == HOST_STATUS_DELETED )
			return PEAR::raiseError( $kernelStrings['app_editdeldb_message'], ERRCODE_APPLICATION_ERR );

		global $_SERVER;
		$ip = $_SERVER['REMOTE_ADDR'];

		@logAccountOperation( $DB_KEY, $kernelStrings, aop_delete, $ip, null );

		return setDBProfileParameter( $DB_KEY, $kernelStrings, "/".HOST_DATABASE."/".HOST_DBSETTINGS, HOST_STATUS, HOST_STATUS_DELETED );
	}

	function restoreDbProfile( $DB_KEY, $kernelStrings )
	//
	// Clears database deleted flag
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $_SERVER;
		$ip = $_SERVER['REMOTE_ADDR'];

		@logAccountOperation( $DB_KEY, $kernelStrings, aop_restore, $ip, null );

		return setDBProfileParameter( $DB_KEY, $kernelStrings, "/".HOST_DATABASE."/".HOST_DBSETTINGS, HOST_STATUS, "" );
	}

	function setSessionExpireTime( $DB_KEY, $periodData, $kernelStrings )
	//
	// Sets session expiration period
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$periodData - information about period type and duration
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;
		global $_SESSION;

		if ( $periodData['type'] == 2 ) {
			$periodData['period'] = trim($periodData['period']);

			if ( PEAR::isError( $invalidField = findEmptyField($periodData, array('period')) ) ) {
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

				return $invalidField;
			}

			if ( !isFloatStr( $periodData['period'] ) )
				return PEAR::raiseError ( sprintf($kernelStrings[ERR_INVALIDNUMFORMAT], $periodData['period']),
											ERRCODE_INVALIDFIELD,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options, 'period');
		}

		if ( $periodData['type'] == 0 ) {
			$period = SESSION_USE_SYSTEM_TO;
			setcookie("onbrowsercloseexpire", true);
		}
		elseif ( $periodData['type'] == 1 )
			$period = null;
		else
			$period = $periodData['period']*60;

		setDBProfileParameter( $DB_KEY, $kernelStrings, "/".HOST_DATABASE."/".HOST_DBSETTINGS, HOST_SESS_EXPIRE_PERIOD, $period );

		$_SESSION[HOST_SESS_EXPIRE_PERIOD] = $period;
	}

	//
	// Account operations log support
	//

	function addOptionModificationRecord( $optionClass, $prevValue, $newValue, $optionName, &$parent, &$dom )
	//
	// Adds option modification record to an account operation log XML file
	//
	//		Parameters:
	//			$optionClass - option class
	//			$prevValue - previous option value
	//			$newValue - new option value
	//			$optionName - option name
	//			$parent - parent XML node
	//			$dom - XML document
	//
	//		Returns null
	//
	{
		$record = @create_addElement( $dom, $parent, AOPR_OPTION_MODIFICATION );
		if ( !$record )
			return;

		$record->set_attribute( AOPR_CLASS, $optionClass );
		$record->set_attribute( AOPR_NAME, $optionName );
		$record->set_attribute( AOPR_PREV, $prevValue );
		$record->set_attribute( AOPR_NEW, $newValue );
	}

	function logAccountOperation( $DB_KEY, $kernelStrings, $recordType, $ipAddress, $oldData, $source = null )
	//
	// Logs operation with account
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$kernelStrings - Kernel localization strings
	//			$recordType - type of operation: sign up, DB create, modify, delete, remove
	//			$ipAddress - IP address initiated account changes
	//			$olData - old account data
	//			$source - account source
	//
	//		Returns null or PEAR_Error
	//
	{
		
		return null;
		$filePath = sprintf( "%s/%s", WBS_DBLSIT_DIR, ACCOUNT_LOG_FILE_NAME );

		$dom = null;
		$logRootNode = null;

		if ( file_exists($filePath) ) {
			$dom = @domxml_open_file( realpath($filePath) );

			if ( !$dom )
				return PEAR::raiseError( $kernelStrings['app_erraccountlog_message'] );

			$xpath = @xpath_new_context($dom);

			$logRootNode = &xpath_eval( $xpath, "/".AOPR_LOG );

			if (!count($logRootNode->nodeset)) {
				$logRootNode = @create_addElement( $dom, $dom, AOPR_LOG );
				if ( !$logRootNode )
					return PEAR::raiseError( $kernelStrings['app_erraccountlog_message'] );
			}
				else
					$logRootNode = $logRootNode->nodeset[0];
		} else {
			$dom = @domxml_new_doc("1.0");

			if ( !$dom )
				return PEAR::raiseError( $kernelStrings['app_erraccountlog_message'] );

			$logRootNode = @create_addElement( $dom, $dom, AOPR_LOG );
		}

		if ( !$logRootNode )
			return PEAR::raiseError( $kernelStrings['app_erraccountlog_message'] );


		$xpath = @xpath_new_context($dom);

		$profileRecord = &xpath_eval($xpath, "/".AOPR_LOG."/".AOPR_ACCOUNT."[@DB_KEY='$DB_KEY']");
		if ( !$profileRecord || !count($profileRecord->nodeset) ) {
			$profileRecord = @create_addElement( $dom, $logRootNode, AOPR_ACCOUNT );
			$profileRecord->set_attribute( AOPR_DBKEY, $DB_KEY );
		}
		else
			if ( count($profileRecord->nodeset) )
				$profileRecord = $profileRecord->nodeset[0];

		$root = @create_addElement( $dom, $profileRecord, AOPR_LOGRECORD );

		$root->set_attribute( AOPR_TYPE, strtoupper($recordType) );
		$root->set_attribute( AOPR_DATETIME, convertToSqlDateTime( time() ) );
		$root->set_attribute( AOPR_IP, $ipAddress );
		$root->set_attribute( AOPR_SOURCE, $source );

		$profileModified = false;

		switch ( $recordType ) {
			case aop_modify :
								// Calculate changes between old and new data
								//
								$newData = @loadHostDataFile( $DB_KEY, $kernelStrings );
								if ( PEAR::isError($newData) )
									return PEAR::raiseError( $kernelStrings['app_erraccountlog_message'] );

								// Applications
								//
								$oldApplications = array_keys( $oldData[HOST_APPLICATIONS] );
								$newApplications = array_keys( $newData[HOST_APPLICATIONS] );

								$installedApplications = array_diff( $newApplications, $oldApplications );
								$removeddApplications = array_diff( $oldApplications, $newApplications );

								$modifications = @create_addElement( $dom, $root, AOPR_MODIFICATIONS );
								if ( !$modifications )
									return PEAR::raiseError( $kernelStrings['app_erraccountlog_message'] );

								$modifications->set_attribute( AOPR_APPLICATIONS_ADDED, implode( ',', $installedApplications ) );
								$modifications->set_attribute( AOPR_APPLICATIONS_REMOVED, implode( ',', $removeddApplications ) );

								$optionSets = array( HOST_DBSETTINGS, HOST_ADMINISTRATOR, HOST_FIRSTLOGIN );
								$optionList = array( HOST_DBSETTINGS=>array( HOST_EXPIRE_DATE, HOST_READONLY, HOST_DBSIZE_LIMIT,
														HOST_DATE_FORMAT, HOST_MAXUSERCOUNT, HOST_DBUSER, HOST_DBPASSWORD, HOST_RECIPIENTSLIMIT, HOST_SMS_RECIPIENTSLIMIT ),
													HOST_ADMINISTRATOR=>array( HOST_PASSWORD, HOST_LANGUAGE ),
													HOST_FIRSTLOGIN=>array( HOST_COMPANYNAME, HOST_LASTNAME, HOST_FIRSTNAME, HOST_EMAIL,
														HOST_LOGINNAME, HOST_PASSWORD ) );

								foreach( $optionSets as $optionSet ) {
									$oldSettings = $oldData[$optionSet];
									$newSettings = $newData[$optionSet];

									$options = $optionList[$optionSet];

									foreach( $options as $option )
										if ( $newSettings[$option] != $oldSettings[$option] ) {
											$profileModified = true;
											addOptionModificationRecord( $optionSet, $oldSettings[$option],
												$newSettings[$option], $option, $modifications, $dom );
										}

								}

								break;
		}

		@$dom->dump_file($filePath, false, true);

		return null;
	}

	function listAccountLogRecords( $DB_KEY, $kernelStrings, &$dom )
	//
	// Returns list of account log records
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$kernelStrings - Kernel localization strings
	//			$dom - DOM document
	//
	//		Returns array of XML nodes or PEAR_Error
	//
	{
		$result = array();

		$filePath = sprintf( "%s/%s", WBS_DBLSIT_DIR, ACCOUNT_LOG_FILE_NAME );

		if ( !file_exists($filePath) )
			return $result;

		$dom = @domxml_open_file( realpath($filePath) );

		if ( !$dom )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$xpath = @xpath_new_context($dom);

		$profileRecord = xpath_eval($xpath, "/".AOPR_LOG."/".AOPR_ACCOUNT."[@DB_KEY='$DB_KEY']");
		if ( !$profileRecord || !count($profileRecord->nodeset) )
			return $result;

		$profileRecord = $profileRecord->nodeset[0];

		if ( !( $records = xpath_eval($xpath, AOPR_LOGRECORD, $profileRecord) ) )
			return $result;

		foreach( $records->nodeset as $record )
			$result[] = $record;

		return $result;
	}

	function loadAccountLogRecord( $DB_KEY, $index, $kernelStrings )
	//
	// Returns record from account modification log
	//
	//		Parameters:
	//			$DB_KEY - database key
	//			$index of record
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array containing parsed record data, or null, or PEAR_Error
	//
	{
		$dom = null;
		$records = listAccountLogRecords( $DB_KEY, $kernelStrings, $dom );
		if ( PEAR::isError($records) )
			return $records;

		if ( isset($records[$index]) )
			$record = $records[$index];
		else
			return null;

		$xpath = @xpath_new_context($dom);

		if ( !$modifications = xpath_eval($xpath, AOPR_MODIFICATIONS, $record) )
			return null;

		if ( !count($modifications->nodeset) )
			return null;

		$modifications = $modifications->nodeset[0];

		$addedApplications = $modifications->get_attribute( AOPR_APPLICATIONS_ADDED );
		$removedApplications = $modifications->get_attribute( AOPR_APPLICATIONS_REMOVED );

		$optModifications = array();
		$optModifications[AOPR_OPTION_MODIFICATION] = array();
		if ( strlen($addedApplications) )
			$optModifications[AOPR_APPLICATIONS_ADDED] = explode( ',', $addedApplications );
		else
			$optModifications[AOPR_APPLICATIONS_ADDED] = array();

		if ( strlen($removedApplications) )
			$optModifications[AOPR_APPLICATIONS_REMOVED] = explode( ',', $removedApplications );
		else
			$optModifications[AOPR_APPLICATIONS_REMOVED] = array();

		if ( $opt_modifications = xpath_eval($xpath, AOPR_OPTION_MODIFICATION, $modifications ) )
			if ( count($opt_modifications->nodeset) )
				foreach( $opt_modifications->nodeset as $opt_modification )
					$optModifications[AOPR_OPTION_MODIFICATION][] = getAttributeValues( $opt_modification );

		return $optModifications;
	}

	//
	// CSV-files support
	//

	function getCSVSeparator( $filePath, $kernelStrings )
	//
	// Returns a separator character used in specified file
	//
	//		Parameters:
	//			$filePath - path to file to import
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns string or PEAR_Error
	//
	{
		global $csv_file_separators;

		$fileContent = file($filePath);
		if ( !$fileContent )
			return PEAR::raiseError($kernelStrings['app_emptycsv_message'], ERRCODE_APPLICATION_ERR);

		$fileLines = count($fileContent);

		if ( !$fileLines )
			return null;

		$lastSeparator = null;
		$lastLength = null;

		foreach( $csv_file_separators as $separator ) {
			$headers = explode($separator, $fileContent[0]);
			$quant = count($headers);

			if ( $quant > $lastLength ) {
				$lastSeparator = $separator;
				$lastLength = $quant;
			}
		}

		return $lastSeparator;
	}

	function getCSVHeaders( $filePath, $separator, $kernelStrings )
	//
	//	Returns a list of CSV file headers
	//
	//		Parameters:
	//			$filePath - path to file to import
	//			$kernelStrings - Kernel localization strings
	//			$separator - file separator
	//
	//		Returns array of strings or PEAR_Error
	//
	{
		$fileContent = file($filePath);
		if ( !$fileContent )
			return PEAR::raiseError($kernelStrings['app_emptycsv_message'], ERRCODE_APPLICATION_ERR);

		$fileLines = count($fileContent);

		if ( !$fileLines )
			return null;

		$handle = fopen ( $filePath, "r" );
		if ( !$handle )
			return PEAR::raiseError($kernelStrings['app_errreadingfile_mesage'], ERRCODE_APPLICATION_ERR);

		$headers = fgetcsv ($handle, 1000, $separator);

		fclose ($handle);

		if ( !$headers )
			return PEAR::raiseError($kernelStrings['app_errparsingfile_message'], ERRCODE_APPLICATION_ERR);

		return $headers;
	}

	function makeCSVImportScheme( $csvHeaders, $dbScheme, $separator, $headerDbLink, $importFirstLine, $kernelStrings )
	//
	// Makes scheme for importing custom CSV files
	//
	//		Parameters:
	//			$csvHeaders - headers from file (getCSVHeaders() function result)
	//			$dbScheme - database fields scheme
	//			$separator - file separator
	//			$headerDbLink - links between CSV headers and database fields
	//			$importFirstLine - import first line setting
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns scheme as array or PEAR_Error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		// Check for required fields
		//
		foreach ( $dbScheme as $dbFieldID => $dbFieldData ) {
			if ( $dbFieldData[CSV_DBREQUIRED] ) {
				$found = false;

				// Loop through links to find db field correspondence
				//
				foreach( $headerDbLink as $headerIndex => $headerLink ) {
					if ( $headerLink == $dbFieldID ) {
						$found = true;
						break;
					}
				}

				// Return error if no correspondence found
				//
				if ( !$found ) {
					$errStr = sprintf( $kernelStrings['app_importfielderr_message'], $dbFieldData[CSV_DBNAME] );
					return PEAR::raiseError ( $errStr,
												ERRCODE_INVALIDFIELD,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												$dbFieldID );
				}
			}
		}

		// Check for required field groups
		//
		$requiredGroups = array();
		foreach ( $dbScheme as $dbFieldID => $dbFieldData )
			// Prepare group list
			//
			if ( $dbFieldData[CSV_DBREQUIREDGROUP] ) {
				$groupName = $dbFieldData[CSV_DBREQUIREDGROUP];

				if ( !array_key_exists($groupName, $requiredGroups) )
					$requiredGroups[$groupName] = array();

				$requiredGroups[$groupName][$dbFieldID] = $dbFieldData;
			}

		// Loop through groups to find empty groups
		//
		foreach ( $requiredGroups as $groupName => $groupContent ) {

			$found = false;

			// Search empty group fields
			//
			foreach( $groupContent as $dbFieldID=>$dbFieldData ) {
				// Loop through links to find db field correspondence
				//
				foreach( $headerDbLink as $headerIndex => $headerLink ) {
					if ( $headerLink == $dbFieldID ) {
						$found = true;

						break 2;
					}
				}
			}

			// Empty group found - report error
			//
			if ( !$found ) {
				// Format group name
				//
				$groupName = array();

				foreach( $groupContent as $dbFieldID=>$dbFieldData )
					$groupName[] = $dbFieldData[CSV_DBNAME];

				$groupName = implode( ', ', $groupName );

				// Return error
				//
				$errStr = sprintf( $kernelStrings['app_importfieldgrouperr_message'], $groupName );
				return PEAR::raiseError ( $errStr, ERRCODE_INVALIDFIELD );
			}
		}

		// Assemble scheme
		//
		$importScheme = array();

		$importScheme[CSV_IMPORTFIRSLN] = $importFirstLine;
		$importScheme[CSV_DELIMITER] = $separator;
		$schemeItems = array();

		foreach( $headerDbLink as $headerIndex => $headerLink ) {
			// Skip item if no correspondence set
			//
			if ( !strlen($headerLink) )
				continue;

			$schemeItem = array();

			// Find out header index
			//
			if ( !$importFirstLine )
				$schemeItem[CSV_FILEFIELD] = $csvHeaders[$headerIndex];
			else
				$schemeItem[CSV_FILEFIELD] = $headerIndex;

			// Fill in other item parameters
			//
			$dbFieldData = $dbScheme[$headerLink];
			$schemeItem[CSV_DBFIELD] = $headerLink;

			$schemeItems[$headerLink] = $schemeItem;
		}

		$importScheme[CSV_LINKS] = $schemeItems;

		return $importScheme;
	}

	function applyCSVImportScheme( $filePath, $importScheme, $kernelStrings )
	//
	//	Applies import scheme to a importing file
	//
	//		Parameters:
	//			$filePath - path to file to import
	//			$importScheme - CSV file import scheme
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns processed file content
	//
	{
		// Open source file
		//
		$handle = fopen ( $filePath, "r" );
		if ( !$handle )
			return PEAR::raiseError($kernelStrings['app_errreadingfile_mesage'], ERRCODE_APPLICATION_ERR);


		// Read file entire contents
		//
		$first = true;
		$headerIndices = array();
		$result = array();
		$separator = $importScheme[CSV_DELIMITER];

		while ( ($line = fgetcsv ($handle, 1000, $separator)) !== FALSE ) {

			// Check if line is not empty
			//
			$dataFound = false;
			foreach( $line as $key=>$value ) {
				if ( strlen($value) ) {
					$dataFound = true;
					break;
				}
			}

			if ( !$dataFound )
				continue;

			// Skip first line if it is not required
			//
			if ( !$importScheme[CSV_IMPORTFIRSLN] && $first ) {
				$first = false;

				// Scan scheme fields and headers to find header index
				//
				foreach( $importScheme[CSV_LINKS] as $dbFleid=>$schemeItem ) {
					$headerIndex = $schemeItem[CSV_FILEFIELD];

					foreach( $line as $fileHeaderIndex=>$headerValue )
						if ( $headerValue == $headerIndex ) {
							$headerIndices[$dbFleid] = $fileHeaderIndex;

							break;
						}
				}

				continue;
			}

			// Loop through sheme fields
			//
			$resultItem = array();
			foreach( $importScheme[CSV_LINKS] as $dbFleid=>$schemeItem ) {

				// Find field index and value
				//
				if ( !$importScheme[CSV_IMPORTFIRSLN] ) {
					if ( isset($headerIndices[$dbFleid]) )
						$index = $headerIndices[$dbFleid];
					else
						$index = null;
				} else
					$index = $schemeItem[CSV_FILEFIELD];

				if ( array_key_exists($index, $line) ) {
					$value = $line[$index];
					$resultItem[$dbFleid] = $value;
				}
			}
			$result[] = $resultItem;
		}

		fclose ($handle);

		return $result;
	}

	function writeCSVLine( $data, $delimiter, $file )
	//
	// Writes line to the CSV file
	//
	//		Parameters:
	//			$data - data to write
	//			$delimiter - data delimiter
	//			$file - file handle
	//
	//		Returns null
	//
	{
		$lineData = array();
		foreach( $data as $item ) {
			$item = '"'.$item.'"';

			$lineData[] = $item;
		}

		$lineData = implode( $delimiter, $lineData );

		fwrite( $file, $lineData."\n" );

		return null;
	}

	function exportCSVFile( $importScheme, $recordList, $kernelStrings, $addU_IDField = true )
	//
	// Exports array content to the file
	//
	//		Parameters:
	//			$importScheme - CSV file import scheme
	//			$recordList - array of records to export
	//			$kernelStrings - Kernel localization strings
	//			$addU_IDField - add U_ID field into result
	//
	//
	//		Returns path to new file or PEAR_Error
	//
	{
		// Generate file name
		//
		$tmpFileName = uniqid( TMP_FILES_PREFIX );
		$destPath = WBS_TEMP_DIR."/".$tmpFileName;

		// Create file
		//
		$fp = @fopen( $destPath, 'wt' );
		if ( !$fp )
			return PEAR::raiseError( $kernelStrings['eul_tmpfilerr_message'] );

		// Write column headers
		//
		$exportItem = array();
		$addU_ID = false;
		if ( !$importScheme[CSV_IMPORTFIRSLN] ) {
			// Add ID field
			//
			if ( !array_key_exists( 'U_ID', $importScheme[CSV_LINKS] ) && $addU_IDField ) {
				$addU_ID = true;
				$exportItem[] = $kernelStrings['app_treeid_title'];
			}

			// Add other scheme fields
			//
			foreach( $importScheme[CSV_LINKS] as $schemeLink )
				$exportItem[] = $schemeLink[CSV_FILEFIELD];

			// Write headers to file
			//
			writeCSVLine( $exportItem, $importScheme[CSV_DELIMITER], $fp );
		}

		// Create file content
		//
		foreach( $recordList as $recordData ) {
			$exportItem = array();

			// Add ID field
			//
			if ( !$importScheme[CSV_IMPORTFIRSLN] )
				if ( $addU_ID && $addU_IDField )
					$exportItem[] = $recordData['U_ID'];

			// Build file line
			//
			foreach( $importScheme[CSV_LINKS] as $schemeLink )
				$exportItem[] = $recordData[$schemeLink[CSV_DBFIELD]];

			// Write line to file
			//
			writeCSVLine( $exportItem, $importScheme[CSV_DELIMITER], $fp );
		}

		fclose( $fp );

		return $destPath;
	}

	//
	// User list functions
	//

	function df_contactname( $userdata, $short = false, $addLineBreaks = false, $forceEmail = false )
	//
	// Display function for the user list Name column
	//
	//		Parameters:
	//			$userdata - array representing user data
	//			$short - return short name
	//			$addLineBreaks - add line breaks between name components
	//			$forceEmail - add email address after the name
	//
	//		Returns column value
	//
	{
		$result = array();

		if ( strlen($userdata['C_FIRSTNAME']) )
			$result[] = $userdata['C_FIRSTNAME'];

		if ( strlen($userdata['C_MIDDLENAME']) )
			$result[] = mb_substr( $userdata['C_MIDDLENAME'], 0, 1 ,'utf-8').".";

		if ( strlen($userdata['C_LASTNAME']) )
			$result[] = $userdata['C_LASTNAME'];

		$namePartsExists = count($result);

		$namePartsExists = count($result);

		if ( !$short ) {
			if ( isset($userdata['C_EMAILADDRESS']) && strlen($userdata['C_EMAILADDRESS']) )
				if ( !$namePartsExists )
					$result[] = $userdata['C_EMAILADDRESS'];
		} else
			if ( !$namePartsExists )
				if ( isset($userdata['C_EMAILADDRESS']) && strlen($userdata['C_EMAILADDRESS']) )
					$result[] = $userdata['C_EMAILADDRESS'];

		if ( $forceEmail )
			if ( isset($userdata['C_EMAILADDRESS']) && strlen($userdata['C_EMAILADDRESS']) )
				$result[] = '<'.$userdata['C_EMAILADDRESS'].'>';

		if ( !$addLineBreaks )
			$result = implode( " ", $result );
		else
			$result = implode( "\n", $result );

		return trim( $result );
	}

	function df_contactname_sort( $order )
	//
	// Sorting function for the user list Name column
	//
	//		Parameters:
	//			$order - sorting order
	//
	//		Returns sorting string
	//
	{
		global $qr_namesortclause;

		return sprintf( "%s %s", $qr_namesortclause, $order );
	}

	//
	// Refined lists view support
	//

	function getContactTypeFieldsSummary( $typeDescription, $kernelStrings, $addCustomFields = false, $addUserIdentifier = false )
	//
	// Returns contact type columns descriptions as a plain array
	//
	//		Parameters:
	//			$typeDescription - type description
	//			$kernelStrings - Kernel localization strings
	//			$addCustomFields - adds custom fields (NAME and so on)
	//			$addUserIdentifier - add user identifier field
	//
	//
	//		Returns array
	//
	{
		global $contactCustomFieldsDesc;

		$result = array();

		if ( $addUserIdentifier ) {
			$fieldDesc = array();
			$fieldDesc[CONTACT_FIELDID] = 'U_ID';
			$fieldDesc[CONTACT_DBFIELD] = 'U_ID';
			$fieldDesc[CONTACT_FIELDGROUP_LONGNAME] = $kernelStrings['app_uid_text'];
			$fieldDesc[CONTACT_FIELDGROUP_SHORTNAME]= $kernelStrings['app_uid_text'];
			$fieldDesc[CONTACT_GROUPID] = 'USER';
			$fieldDesc[CONTACT_FIELD_TYPE] = CONTACT_FT_TEXT;
			$fieldDesc[CONTACT_FIELDGROUPNAME] = $kernelStrings['app_user_text'];

			$result['U_ID'] = $fieldDesc;
		}

		if ( $addCustomFields )
			foreach( $contactCustomFieldsDesc as $fieldID=>$fieldDesc ) {
				$fieldDesc[CONTACT_FIELDGROUP_LONGNAME] = $kernelStrings[$fieldDesc[CONTACT_FIELDGROUP_LONGNAME]];
				$fieldDesc[CONTACT_FIELDGROUP_SHORTNAME] = $kernelStrings[$fieldDesc[CONTACT_FIELDGROUP_SHORTNAME]];
				$fieldDesc[CONTACT_FIELDGROUPID] = null;
				$fieldDesc[CONTACT_FIELDGROUPNAME] = null;

				$result[$fieldID] = $fieldDesc;
			}

		foreach( $typeDescription as $groupData )
			foreach( $groupData[CONTACT_FIELDS] as $fieldID=>$fieldData ) {
				$fieldData[CONTACT_FIELDGROUPID] = $groupData[CONTACT_GROUPID];
				$fieldData[CONTACT_FIELDGROUPNAME] = $groupData[CONTACT_FIELDGROUP_LONGNAME];
				$result[$fieldID] = $fieldData;
			}

		return $result;
	}

	function getContactImageFieldPropertieis( $fieldValue )
	//
	// Returns array representation of the contact image field
	//
	//		Parameters:
	//			$fieldValue - field XML description string
	//
	//		Returns array
	//
	{
		$result = array();

		$result[CONTACT_IMGF_FILENAME] = null;
		$result[CONTACT_IMGF_SIZE] = null;
		$result[CONTACT_IMGF_DISKFILENAME] = null;
		$result[CONTACT_IMGF_TYPE] = null;
		$result[CONTACT_IMGF_DATETIME] = null;
		$result[CONTACT_IMGF_MIMETYPE] = null;
		$result[CONTACT_IMGF_MODIFIED] = false;
		$result[CONTACT_IMGF_PREVFILENAME] = null;

		if ( !strlen($fieldValue) )
			return $result;

		$dom = @domxml_open_mem( $fieldValue );
		if ( !$dom )
			return $result;

		$root = $dom->root();
		if ( !$root )
			return $result;

		$result[CONTACT_IMGF_FILENAME] = base64_decode( @$root->get_attribute(CONTACT_IMGF_FILENAME) );
		$result[CONTACT_IMGF_SIZE] = @$root->get_attribute(CONTACT_IMGF_SIZE);
		$result[CONTACT_IMGF_DISKFILENAME] = @$root->get_attribute(CONTACT_IMGF_DISKFILENAME);
		$result[CONTACT_IMGF_TYPE] = @$root->get_attribute(CONTACT_IMGF_TYPE);
		$result[CONTACT_IMGF_DATETIME] = @$root->get_attribute(CONTACT_IMGF_DATETIME);
		$result[CONTACT_IMGF_MIMETYPE] = @$root->get_attribute(CONTACT_IMGF_MIMETYPE);
		$result[CONTACT_IMGF_PREVFILENAME] = @$root->get_attribute(CONTACT_IMGF_DISKFILENAME);

		return $result;
	}

	function clearContactImageField( $fieldValue )
	//
	// Cleares image contact field
	//
	//		Parameters:
	//			$fieldValue - field XML description string
	//
	//		Returns array
	//
	{
		$fieldValue[CONTACT_IMGF_FILENAME] = null;
		$fieldValue[CONTACT_IMGF_SIZE] = null;
		$fieldValue[CONTACT_IMGF_DISKFILENAME] = null;
		$fieldValue[CONTACT_IMGF_TYPE] = null;
		$fieldValue[CONTACT_IMGF_MIMETYPE] = null;
		$fieldValue[CONTACT_IMGF_DATETIME] = null;
		$fieldValue[CONTACT_IMGF_MODIFIED] = 1;

		return $fieldValue;
	}

	function getImageFieldAdditiveSize( &$fieldValue )
	//
	// Returns image field additive size
	//
	//		Parameters:
	//			$fieldValue - field description as array
	//
	//		Returns null or PEAR_Error
	//
	{
		if ( !$fieldValue[CONTACT_IMGF_MODIFIED] )
			return 0;

		$filesPath = getContactsAttachmentsDir();

		$existingSize = 0;

		if ( strlen($fieldValue[CONTACT_IMGF_PREVFILENAME]) ) {
			$srcPath = $filesPath."/".base64_decode( $fieldValue[CONTACT_IMGF_PREVFILENAME] );

			if ( file_exists($srcPath) )
				$existingSize = filesize($srcPath);
		}

		$srcPath = base64_decode( $fieldValue[CONTACT_IMGF_DISKFILENAME] );
		if ( file_exists($srcPath) ) {
			$newSize = filesize($srcPath);
		}
			else return 0;

		return $newSize - $existingSize;
	}

	function moveUpdateImageFieldFile( &$fieldValue, &$kernelStrings, &$QuotaManager )
	//
	// Moves image file from temporary directory to the attachemtns directory and updates image description parameters
	//
	//		Parameters:
	//			$fieldValue - field description as array
	//			$kernelStrings - Kernel localization string
	//			$QuotaManager - DiskQuotaManager object
	//
	//		Returns null or PEAR_Error
	//
	{
		$filesPath = getContactsAttachmentsDir();

		if ( !file_exists($filesPath) ) {
			$errStr = null;
			@forceDirPath( $filesPath, $errStr );
		}

		// Delete previous files
		//
		if ( strlen($fieldValue[CONTACT_IMGF_PREVFILENAME]) ) {
			$srcPath = $filesPath."/".base64_decode( $fieldValue[CONTACT_IMGF_PREVFILENAME] );

			$ext = null;
			$srcThumbFile = findThumbnailFile( $srcPath, $ext );
			if ( $srcThumbFile ) {
				if ( file_exists($srcThumbFile) ) {
					$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, 'CM', -1*filesize($srcThumbFile) );
					@unlink($srcThumbFile);
				}
			}

			if ( file_exists($srcPath) ) {
				$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, 'CM', -1*filesize($srcPath) );
				@unlink($srcPath);
			}
		}

		// Move image file
		//
		if ( strlen($fieldValue[CONTACT_IMGF_DISKFILENAME]) ) {
			$srcPath = base64_decode( $fieldValue[CONTACT_IMGF_DISKFILENAME] );
			$destFileName = uniqid( CONTACT_IMG_FILEPREFIX );
			$destPath = $filesPath."/".$destFileName;

			if ( !@copy($srcPath, $destPath) )
				return PEAR::raiseError( sprintf($kernelStrings[ERR_COPYFILE], $destFileName) );

			$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, 'CM', filesize($destPath) );

			// Move thumbnail
			//
			$ext = null;
			$srcThumbFile = findThumbnailFile( $srcPath, $ext );
			if ( $srcThumbFile ) {
				$destThumbFile = $destPath.".$ext";

				if ( !@copy( $srcThumbFile, $destThumbFile ) )
					return PEAR::raiseError( sprintf($kernelStrings[ERR_COPYFILE], basename($destFileName)) );

				$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, 'CM', filesize($destThumbFile) );

				@unlink($srcThumbFile);
			}

			@unlink($srcPath);

			$fieldValue[CONTACT_IMGF_DISKFILENAME] = base64_encode($destFileName);
		}

		return null;
	}

	function getContactsAttachmentsDir()
	//
	// Returns path to the Contacts attachments directory
	//
	//		Returns string
	//
	{
		return sprintf( WBS_ATTACHMENTS_DIR."/cm/contacts" );
	}

	function processImageFieldFile( $filePath, $originalFileName, $fileType, &$thumbnailError, &$kernelStrings, &$fieldDescription )
	//
	// Processes new image field file - checks image type and creates thumbnail
	//
	//		Parameters:
	//			$filePath - path to the new file
	//			$originalFileName - original file name
	//			$fileType - file mime type
	//			$thumbnailError - thumbnail creation error object
	//			$kernelStrings - Kernel localization strings
	//			$fieldDescription - image field description array
	//
	//		Returns null or PEAR_Error
	//
	{
		global $knownImageFieldFormats;

		// Check image type
		//
		$path_parts = pathinfo( $originalFileName );

		if ( isset($path_parts["extension"]) )
			$extension = $path_parts["extension"];
		else
			$extension = null;

		$extension = trim( strtolower($extension) );

		if ( !in_array($extension, $knownImageFieldFormats) )
			return PEAR::raiseError( sprintf( "%s %s", $kernelStrings['app_invalidimageformat_message'], implode(", ", $knownImageFieldFormats) ), ERRCODE_APPLICATION_ERR );

		// Create thumbnail
		//
		$thumbnailPath = null;
		$thumbnailError = null;

		$res = makeThumbnail( $filePath, $filePath, $extension, 96, $kernelStrings );
		if ( !PEAR::isError($res) )
			$thumbnailPath = $res;
		else
			$thumbnailError = $res;

		// Override image field parameters
		//
		$fieldDescription[CONTACT_IMGF_FILENAME] = $originalFileName;
		$fieldDescription[CONTACT_IMGF_SIZE] = filesize($filePath);
		$fieldDescription[CONTACT_IMGF_DISKFILENAME] = base64_encode($filePath);
		$fieldDescription[CONTACT_IMGF_TYPE] = $extension;
		$fieldDescription[CONTACT_IMGF_MIMETYPE] = $fileType;
		$fieldDescription[CONTACT_IMGF_DATETIME] = time();
		$fieldDescription[CONTACT_IMGF_MODIFIED] = 1;

		return null;
	}

	function getContactFieldValue( $fieldID, $data, $fieldDesc, $kernelStrings, $typeFormatters = null )
	//
	// Returns contact field value
	//
	//		Parameters:
	//			$fieldID - contact field ID
	//			$data - source array
	//			$fieldDesc - contact field description
	//			$kernelStrings - Kernel localization strings
	//			$typeFormatters - array of user callback functions to format display values of specific types
	//
	//		Returns scalar value
	//
	{
		global $kernelStrings;
		global $monthFullNames;

		$result = null;

		switch ( $fieldDesc[CONTACT_FIELD_TYPE] ) {
				case CONTACT_FT_DATE :
										$dbField = $fieldDesc[CONTACT_DBFIELD];

										if ( is_null($typeFormatters) || !isset($typeFormatters[CONTACT_FT_DATE]) )
											$result = convertToDisplayDateNT($data[$dbField]);
										else
											$result = call_user_func( $typeFormatters[CONTACT_FT_DATE], $data[$dbField] );

										break;
				case CONTACT_FT_NUMERIC :
										$dbField = $fieldDesc[CONTACT_DBFIELD];
										$decplaces = $fieldDesc[CONTACT_DECPLACES];

										if ( strlen($data[$dbField]) )
											$result = number_format($data[$dbField], $decplaces, '.', '');
										else
											$result = null;

										break;
				case CONTACT_FT_IMAGE :
										$dbField = $fieldDesc[CONTACT_DBFIELD];

										$result = getContactImageFieldPropertieis( $data[$dbField] );

										break;
							default:
										if ( isset($fieldDesc[CONTACT_DBFIELD]) ) {
											$dbField = $fieldDesc[CONTACT_DBFIELD];
											$result = $data[$dbField];
										} else
											$result = $data[$fieldID];
		}

		return $result;
	}

	function applyContactTypeDescription( $data, $visibleColumns, $columnDescData, $kernelStrings, $viewMode )
	//
	// Applies contact type description to a data array
	//
	//		Parameters:
	//			$data - source array
	//			$visibleColumns - array of visible columns
	//			$columnDescData - column descriptions (result of the getContactTypeFieldsSummary() function)
	//			$kernelStrings - Kernel localization strings
	//			$viewMode - contact list view mode
	//
	//		Returns corrected array
	//
	{
		// Contact primary name field
		//
		if ( in_array( CONTACT_NAMEFIELD, $visibleColumns ) || $viewMode == UL_LIST_VIEW )
			$data[CONTACT_NAMEFIELD] = df_contactname($data);

		if ( $viewMode == UL_GRID_VIEW ) {

			// Grid view mode - load only visible columns data
			//
			foreach( $visibleColumns as $column_id ) {

				// Find field description
				//
				if ( !array_key_exists( $column_id, $columnDescData ) )
					continue;

				// Find field value
				//
				$fieldDesc = $columnDescData[$column_id];

				$data[$column_id] = getContactFieldValue( $column_id, $data, $fieldDesc, $kernelStrings );
			}
		} else {
			// List vew mode - load all contact fields
			//
			foreach ( $columnDescData as $column_id => $fieldDesc )
				$data[$column_id] = getContactFieldValue( $column_id, $data, $fieldDesc, $kernelStrings );
		}

		return $data;
	}

	function getColumnSortString( $sortStr, $columnDescData )
	//
	// Returns column sorting SQL string
	//
	//		Parameters:
	//			$sortStr - sorting string (NAME asc)
	//			$columnDescData - column descriptions (result of the getContactTypeFieldsSummary() function)
	//
	//		Returns string
	//
	{
		$sortData = parseSortStr( $sortStr );
		$column = $sortData['field'];
		$order = $sortData['order'];

		// Contact primary name field
		//
		if ( $column == CONTACT_NAMEFIELD )
			return df_contactname_sort( $order );

		// Contact ID field
		//
		if ( $column == CONTACT_IDFIELD )
			return sprintf( "U_ID %s", $order  );

		// Fields declared in type description
		//
		$fieldDesc = null;

		if ( !array_key_exists( $column, $columnDescData ) )
			return null;

		$fieldDesc = $columnDescData[$column];

		return sprintf( "%s %s", $fieldDesc[CONTACT_DBFIELD], $order );
	}

	function checkContactRequiredGroups( $groupData, $contactData, $kernelStrings )
	//
	//	Checks contact group data for empty required field groups
	//
	//		Parameters:
	//			$groupData - group data
	//			$contactData - contact data, user input
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		// Prepare field groups
		//
		$requiredGroups = array();

		foreach ( $groupData[CONTACT_FIELDS] as $dbFieldID => $dbFieldData )
			if ( $dbFieldData[CONTACT_REQUIRED_GROUP] ) {
				$groupName = $dbFieldData[CONTACT_REQUIRED_GROUP];

				if ( !array_key_exists($groupName, $requiredGroups) )
					$requiredGroups[$groupName] = array();

				$requiredGroups[$groupName][$dbFieldID] = $dbFieldData;
			}

		// Loop through groups to find empty groups
		//
		foreach ( $requiredGroups as $groupName => $groupContent ) {

			$found = false;
			$firstField = null;

			// Search empty group fields
			//
			foreach( $groupContent as $dbFieldID=>$dbFieldData ) {
				// Loop through contact data to find empty fields
				//
				if ( is_null($firstField) )
					$firstField = $dbFieldID;

				if ( isset($contactData[$dbFieldID]) && strlen($contactData[$dbFieldID]) ) {
						$found = true;
						break 2;
				}
			}

			// Empty group found - report error
			//
			if ( !$found ) {
				// Format group name
				//
				$groupName = array();
				foreach( $groupContent as $dbFieldID=>$dbFieldData )
					$groupName[] = $dbFieldData[CONTACT_FIELDGROUP_LONGNAME];

				$groupName = implode( ', ', $groupName );

				// Return error
				//
				$errStr = sprintf( $kernelStrings['ul_emptycongroup_message'], $groupName );
				return PEAR::raiseError ( $errStr,
											ERRCODE_EMPTYREQGROUP,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options,
											$firstField."|".$groupData[CONTACT_GROUPID] );
			}
		}

		return null;
	}

	function fixContactInputValues( &$contactData, $columnDescData, $kernelStrings )
	//
	// Fixes contact input values, obtained from untrustworthy sources (e.g. imported)
	//
	//		Parameters:
	//			$columnDescData - column descriptions (result of the getContactTypeFieldsSummary() function)
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR::Error
	//
	{
		foreach( $contactData as $key=>$value ) {

			// Find type description for a contact column
			//
			if ( !isset($columnDescData[$key]) )
				continue;

			$fieldDesc = $columnDescData[$key];
			$fieldType = $fieldDesc[CONTACT_FIELD_TYPE];

			switch( $fieldType ) {
				case CONTACT_FT_DATE :
									//
									// Process date type
									//

									// First check if input date is in registered format
									//
									$sqlDates = array();
									if ( PEAR::isError(checkDateFields( $contactData, array($key), $sqlDates) ) ) {

										// Otherwise, try to guess date with strtotime function
										//
										$res = strtotime( $contactData[$key] );
										if ( $res != -1 )
											$contactData[$key] =  date( DATE_DISPLAY_FORMAT, $res );
										else
											$contactData[$key] = null;
									}
			}
		}

		return null;
	}

	//
	// Thumbnail support
	//

	function estimateThumnGeneratingPossibility( $fileFormat, $width, $height )
	//
	// Estimates possibility of generating thumbnail, basing on available memory
	//
	//		Parameters:
	//			$fileFormat - format of the file (file extension)
	//			$width - image width
	//			$height - image height
	//
	//		Returns true if thumbnail generating is possible
	//
	{
		global $wbs_memoryLimit;

		$memoryFactors = array( 'jpg'=>207792, 'gif'=>500000, 'png'=>500000 );

		// Calculate memory limit, in bytes
		//
		$limit = $wbs_memoryLimit*MEGABYTE_SIZE;

		// Estimate available memory
		//
		if ( function_exists('memory_get_usage') )
			$usedMemory = memory_get_usage();
		else
			$usedMemory = DEFUSED_MEMORY*MEGABYTE_SIZE;

		// Calculate available memory size
		//
		$availableMemory = $limit - $usedMemory;

		// Calculate memory size required for image processing
		//
		$memoryArea = $width*$height;

		$memoryFactor = $memoryFactors[$fileFormat];
		$requidedMem = $memoryArea/$memoryFactor*MEGABYTE_SIZE;

		// Return decision
		//
		return $requidedMem < $availableMemory;
	}

	function makeThumbnail( $filePath, $resultPath, $ext = null, $size = 96, $kernelStrings, $dumpToTheScreen = false )
	//
	// Creates a thumbnail frou source image file
	//
	//		Parameters:
	//			$filePath - source file path
	//			$resultPath - path to save thumbnail
	//			$ext - file extension
	//			$size - thumbnail size
	//			$kernelStrings - Kernel localization strings
	//			$dumpToTheScreen - output destination image to the output stream
	//
	//		Returns path to thumbnail or NULL, or PEAR_Error
	//
	{
		//
		// Check if file has supported format
		//
		if ( is_null($ext) ) {
			$fileInfo = pathinfo( $filePath );

			if ( isset($fileInfo["extension"]) )
				$ext = $fileInfo["extension"];
		}

		$ext = strtolower( $ext );

		// Override JPEG extension
		//
		if ( $ext == 'jpeg' )
			$ext = 'jpg';

		// Return null if file has unknown format
		//
		if ( !in_array($ext, array('jpg', 'gif', 'png')) )
			return null;

		// Return null if GD is unavailable
		//
		if ( !function_exists('gd_info') )
			return PEAR::raiseError( $kernelStrings['app_gdunavailable_message'], ERRCODE_APPLICATION_ERR );

		if ( !function_exists('imagecreatetruecolor') )
			return PEAR::raiseError( $kernelStrings['app_gdunavailable_message'], ERRCODE_APPLICATION_ERR );

		if ( !function_exists('imagecopyresized') )
			return PEAR::raiseError( $kernelStrings['app_gdunavailable_message'], ERRCODE_APPLICATION_ERR );

		$gdInfo = @gd_info();
		if ( !$gdInfo )
			return PEAR::raiseError( $kernelStrings['app_gdunavailable_message'], ERRCODE_APPLICATION_ERR );

		// Make sure what image resolution does not exceeds fixed limit
		//
		$sizes = @getimagesize( $filePath );

		$thumbPossible = estimateThumnGeneratingPossibility( $ext, $sizes[0], $sizes[1] );

		if ( !$thumbPossible )
			return PEAR::raiseError( $kernelStrings['app_gdresexceeds_message'], ERRCODE_APPLICATION_ERR );

		// Check if GIF or JPEG creation is available
		//
		if ( !$gdInfo['GIF Create Support'] && !$gdInfo['JPG Support'] && !$gdInfo['JPEG Support'] )
			return PEAR::raiseError( $kernelStrings['app_gdoutimgsupport_message'], ERRCODE_APPLICATION_ERR );

		// Create source image resource
		//
		$srcIm = false;

		switch ($ext) {
			case 'jpg' :
						if ( (!$gdInfo['JPG Support'] && !$gdInfo['JPEG Support']) || !function_exists('imagecreatefromjpeg') )
							return PEAR::raiseError( sprintf( $kernelStrings['app_gdunsupportedfmt_message'], 'JPEG' ), ERRCODE_APPLICATION_ERR );

						$srcIm = @imagecreatefromjpeg( $filePath );

						if ( !($srcIm) )
							return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );

						break;

			case 'gif' :
						if ( !$gdInfo['GIF Read Support'] || !function_exists('imagecreatefromgif') )
							return PEAR::raiseError( sprintf( $kernelStrings['app_gdunsupportedfmt_message'], 'GIF' ), ERRCODE_APPLICATION_ERR );

						$srcIm = @imagecreatefromgif( $filePath );
						if ( !($srcIm) )
							return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );

						break;

			case 'png' :
						if ( !$gdInfo['PNG Support'] || !function_exists('imagecreatefrompng') )
							return PEAR::raiseError( sprintf( $kernelStrings['app_gdunsupportedfmt_message'], 'PNG' ), ERRCODE_APPLICATION_ERR );

						$srcIm = @imagecreatefrompng( $filePath );
						if ( !($srcIm) )
							return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );

						break;
		}

		if ( is_null($srcIm) )
			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );

		//
		// Calculate thumbnail size
		//
		$srcWidth = $width = @imagesx($srcIm);
		$srcHeight = $height = @imagesy($srcIm);

		if ( !$width ) {
			@imagedestroy( $srcIm );
			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );
		}

		if ( !$height ) {
			@imagedestroy( $srcIm );
			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );
		}

		// Shrink image
		//
		if ( $width > $height ) {
			if ( $width > $size ) {
				$ratio = $width/$height;

				$height = $size/$ratio;
				$width = $size;
			}
		} else {
			if ( $height > $size ) {
				$ratio = $width/$height;

				$width = $size*$ratio;
				$height = $size;
			}
		}

		// Create image copy
		//
		$destImg = @imagecreatetruecolor( $width, $height );
		if ( !$destImg ) {
			@imagedestroy( $srcIm );

			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );
		}

		if ( function_exists('imagecopyresampled') )
			$res = @imagecopyresampled ( $destImg, $srcIm, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight );
		else
			$res = @imagecopyresized ( $destImg, $srcIm, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight );
		if ( !$res ) {
			@imagedestroy( $srcIm );
			@imagedestroy( $destImg );

			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );
		}

		// Output image
		//
		if ( $gdInfo['GIF Create Support'] ) {
			if ( !$dumpToTheScreen ) {
				$resultPath = $resultPath.'.gif';
				$res = @imagegif( $destImg, $resultPath );
			} else {
				$res = @imagegif( $destImg );
			}
		} else {
			if ( !$dumpToTheScreen ) {
				$resultPath = $resultPath.'.jpg';
				$res = @imagejpeg( $destImg, $resultPath );
			} else {
				$res = @imagejpeg( $destImg );
			}
		}

		@imagedestroy( $destImg );
		@imagedestroy( $srcIm );

		if ( $res )
			return $resultPath;
		else
			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );

		return null;
	}

	function findThumbnailFile( $filePath, &$ext )
	//
	// Returns path to the thumbnail file, if it exists, or null
	//
	//		Parameters:
	//			$filePath - path to the original document
	//			$ext - thumbnail extension
	//
	//		Returns null or string
	//
	{
		$ext = 'jpg';
		if (file_exists($filePath.".96.$ext")) {
			return $filePath.".96.$ext";
		}		
		else {
			$jpgFilePath = $filePath.".$ext";
			if ( @file_exists($jpgFilePath) )
				return $jpgFilePath;
		} 
		$ext = 'gif';
		if (file_exists($filePath.".96.$ext")) {
			return $filePath.".96.$ext";
		}		
		else {
			$jpgFilePath = $filePath.".$ext";
			if ( @file_exists($jpgFilePath) )
				return $jpgFilePath;
		} 
		return null;
	}

	//
	// Error handling
	//

	function log_error( $errno, $errstr, $errfile, $errline )
	//
	// Error handler. Saves error information in the form of HTML-string within the file
	//
	//		Parameters:
	//			$errno - error number
	//			$errstr - error text
	//			$errfile - file for storing
	//			$errline - number of line, where error occured
	//
	{
		global $silentMode;

		if ( $silentMode )
			return;

		if ( ( defined( "WBS_DEBUGMODE" ) && WBS_DEBUGMODE != 0 ) || ( $errno != 2048 && $errno != E_NOTICE ) )
			error_log ( sprintf( "%s. %s File: %s Line: %s Error #: %s\r\n", date( "Y-m-d H:i" ), $errstr, $errfile, $errline, $errno ) , 3, ERR_LOG_FILE, "\n");
	}

	function handlePEARError( $error )
	//
	// Function for handling PEAR errors. Saves error message content within the file
	//
	//		Parameters:
	//			$error - PEAR_Error
	//
	{
		global $silentMode;
		
        try {		
            throw new Exception($error->message, $error->getCode());
        } catch (Exception $e) {
            $fp = @fopen( ERR_LOG_FILE, "a" );    
			@fwrite( $fp, "\nURL: ".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n".$e->__toString());
			@fclose( $fp );            
        }
	}

	//
	// System updates
	//

	function getCurrentSystemVersion( $kernelStrings )
	//
	// Returns system curent version as integer
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns integer, or PEAR_Error
	//
	{
		$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );
		if ( !file_exists($filePath) )
			return PEAR::raiseError( $kernelStrings['app_noverfile_message'], ERRCODE_APPLICATION_ERR );

		$dom = @domxml_open_file( realpath($filePath) );
		if ( !$dom )
			return PEAR::raiseError( $kernelStrings['app_openverfile_message'], ERRCODE_APPLICATION_ERR );

		$xpath = xpath_new_context($dom);
		if ( !( $versionnode = xpath_eval($xpath, '/WBS') ) )
			return PEAR::raiseError( $kernelStrings['app_errxmlparsing_message'], ERRCODE_APPLICATION_ERR );

		if ( !count($versionnode->nodeset) )
			return PEAR::raiseError( $kernelStrings['app_invverfilestuct_message'], ERRCODE_APPLICATION_ERR );

		$versionnode = $versionnode->nodeset[0];

		$result = $versionnode->get_attribute( 'VERSION' );

		return $result;
	}

	//
	// User group functions
	//

	function isSystemGroup( $UG_ID )
	//
	// Checks if group is a system group (active, inactive or deleted users)
	//
	//		Parameters:
	//			$UG_ID - user group identifier
	//
	//		Returns boolean
	//
	{
		return in_array( $UG_ID, array(UGR_ACTIVE, UGR_INACTIVE, UGR_DELETED) );
	}

	//
	// Tree/document support functions
	//

	function createCustomTreeDocumentObj( $APP_ID )
	//
	// Creates instance of genericDocumentFolderTree class for a custom application.
	//		Application must support tree/document data model
	//
	//		Parameters:
	//			$appDescriptionData - application tree/document settings
	//
	//		Returns class instance
	//
	{
		global $global_applications;

		$appDescriptionData = $global_applications[$APP_ID];

		$TDDescriptor = $appDescriptionData[APP_REG_USERRIGHTS][APP_REG_TREEDOCUMENT];
		$ACDescriptor = $TDDescriptor[APP_REG_ACCESSTABLE];
		$FDDescriptor = $TDDescriptor[APP_REG_FOLDERTABLE];
		$DOCDescriptor = $TDDescriptor[APP_REG_DOCUMENTTABLE];
		$GACDescriptor = $TDDescriptor[APP_REG_GROUPACCESSTABLE];

		// Create application tree data class instance
		//
		$appTreeClass = new genericDocumentFolderTree();

		$appTreeClass->folderDescriptor = new treeFolderTableDescriptor( $FDDescriptor[APP_REG_TABLENAME],
																			$FDDescriptor[APP_REG_FOLDERID],
																			$FDDescriptor[APP_REG_FOLDERNAME],
																			$FDDescriptor[APP_REG_FOLDERPARENTID],
																			$FDDescriptor[APP_REG_FOLDERSTATUS] );

		$appTreeClass->accessDescriptor = new treeFolderAccessTableDescriptor( $ACDescriptor[APP_REG_TABLENAME],
																				$ACDescriptor[APP_REG_USERID],
																				$ACDescriptor[APP_REG_RIGHTS] );

		if ( isset($DOCDescriptor[APP_REG_TABLENAME]) ) {
			$appTreeClass->documentDescriptor = new treeDocumentsTableDescriptor( $DOCDescriptor[APP_REG_TABLENAME],
																					$DOCDescriptor[APP_REG_DOCUMENTID],
																					$DOCDescriptor[APP_REG_STATUS],
																					$DOCDescriptor[APP_REG_MODIFYUID] );
		} else {
			$appTreeClass->documentDescriptor = new treeDocumentsTableDescriptor( null, null, null, null );
		}

		$appTreeClass->groupAccessDescriptor = new treeFolderGroupAccessTableDescriptor(
																				$GACDescriptor[APP_REG_TABLENAME],
																				$GACDescriptor[APP_REG_GROUPID],
																				$GACDescriptor[APP_REG_RIGHTS] );

		$appTreeClass->globalPrefix = $APP_ID;

		return $appTreeClass;
	}

	//
	// Mail functions
	//

	function extractEmailAddress( $str, &$recipientName )
	//
	// Extracts email address from string
	//
	//		Parameters:
	//			$str - source string
	//			$recipientName - recipient name
	//
	//		Returns string
	//
	{
		preg_match( "/[^<]*<([^>]*)>/u", $str, $strParts );

		if ( !count($strParts) )
			return $str;

		preg_match( "/[^<]*/u", $strParts[0], $nameParts );
		$recipientName = trim($nameParts[0]);

		if ( strlen($recipientName) )
			if ( $recipientName{0} == "\"" )
				$recipientName = substr( $recipientName, 1, strlen($recipientName)-2 );

		return $strParts[1];
	}

	//
	// Billing functions
	//

	function getApplicationHostingPlan( $APP_ID = '')
	//
	// Returns an application hosting plan
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//
	//		Returns string or null
	//
	{
		global $databaseInfo;
		global $mt_hosting_plan_settings;
		global $mt_hosting_plan_limitstats_data;

		$filePath = WBS_DIR."kernel/hosting_plans.php";

		if ( !file_exists($filePath) )
			return null;

		@include_once( WBS_DIR."kernel/hosting_plans.php" );

		if ( $APP_ID != AA_APP_ID && !empty($APP_ID)) 
			{
			// for new billing 
			//
			if (stripos($databaseInfo[HOST_DBSETTINGS][HOST_FREE_APPS], $APP_ID) !== false) 
				{
				return HOST_DEFAULT_PLAN;
				}
			elseif ( isset($databaseInfo[HOST_DBSETTINGS][HOST_PLAN_DB]) )
				{
				return $databaseInfo[HOST_DBSETTINGS][HOST_PLAN_DB];	
				}
			elseif (!isset($databaseInfo[HOST_APPLICATIONS][$APP_ID][HOST_SETTINGS]['PLAN'])) 
				{
				// For old version
				//
				return $databaseInfo[HOST_APPLICATIONS][$APP_ID][HOST_SETTINGS]['PLAN'];
				}
			else 
				{
				return HOST_DEFAULT_PLAN;
				}
			}

		if ( !isset($databaseInfo[HOST_DBSETTINGS][HOST_PLAN_DB]) )
			return null;

		return $databaseInfo[HOST_DBSETTINGS][HOST_PLAN_DB];
	}

	function getHostingDateParts( $date )
	{
		$startDayParts = explode( "-", $date );

		$result = array();
		$result['Y'] = $startDayParts[0];
		$result['M'] = $startDayParts[1];
		$result['D'] = $startDayParts[2];

		return $result;
	}

	/**
	 * Returns an application resource limitations in accordancewith a billing plan.
	 * 
	 * @param string $APP_ID application identifier
	 * @param string [optional]$resource specifies the resource name
	 * @param string [optional]$plan specifies the resource name
	 * 
	 * @return integer or null
	 * 
	 */
	function getApplicationResourceLimits( $APP_ID, $resource = null , $plan = null)
	{
		global $databaseInfo;
		global $mt_hosting_plan_settings,$mt_Price;
		
		$for_return = null;
		 
		// Get setting for CUSTOM plan from DB XML
		//
		if ( isset($databaseInfo[HOST_APPLICATIONS][$APP_ID][HOST_SETTINGS][$APP_ID]) )
			{
			$for_return = $databaseInfo[HOST_APPLICATIONS][$APP_ID][HOST_SETTINGS][$APP_ID];
			}
		elseif (isset($databaseInfo[HOST_APPLICATIONS][$APP_ID][HOST_SETTINGS][$resource]) ) 
			{
			$for_return = $databaseInfo[HOST_APPLICATIONS][$APP_ID][HOST_SETTINGS][$resource];
			}
			
		if (!empty($for_return) && ($plan == HOST_CUSTOM_PLAN || is_null($plan)))
			{
			$for_return = ($for_return == -1) ? null : $for_return;
			if (is_string($for_return)&&( $resource != null)&&isset($mt_Price['eng'][$APP_ID][$for_return]['RESTRICTION'][$resource])) 
				{
				$for_return =  $mt_Price['eng'][$APP_ID][$for_return]['RESTRICTION'][$resource];
				}
			return $for_return;
			}
		
		if(is_null($plan))
			$plan = getApplicationHostingPlan( $APP_ID );
 		
		if($plan == null)
			{
			return null;
			}
			
		// Limit only for free plan	
		// 
   
		if ($APP_ID != AA_APP_ID && $plan == HOST_DEFAULT_PLAN && $plan != HOST_CUSTOM_PLAN)
			{
			if ( isset($mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID]) )
				{
				$limit = $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID];
				if (isset($mt_Price['eng'][$APP_ID][$limit]['RESTRICTION'][$resource]) && $resource != null) 
					{
					$for_return =  $mt_Price['eng'][$APP_ID][$limit]['RESTRICTION'][$resource];
					}
				else 
					{
					$for_return =  $limit;
					}
				}
			$for_return = ($for_return == -1) ? null : $for_return;
			return $for_return;
			}
 
		 
		// If may be this custom plan or old user
		// 
		
		if ($APP_ID != AA_APP_ID && ($plan == HOST_CUSTOM_PLAN || $plan == HOST_OLD_CUSTOM_PLAN))
			{

			if ( isset($databaseInfo[HOST_APPLICATIONS][$APP_ID][HOST_SETTINGS]['PLAN']) )
				{
				// for old user 
				//
				//$plan = $databaseInfo[HOST_APPLICATIONS][$APP_ID][HOST_SETTINGS]['PLAN'];
				$for_return = null;
				}
			else 
				{
				// for custom plan
				//
		
		 		$planValue = $mt_hosting_plan_settings[HOST_CUSTOM_PLAN][$APP_ID];
		 		$planValue = $resource != null ? $planValue[$resource] : $planValue;

		 		// have not restriction
		 		//
		 		
		 		if ($planValue == -1 && isset($mt_hosting_plan_settings[HOST_CUSTOM_PLAN][$APP_ID])) 
		 			{
		 			$for_return = -1;
		 			} 
		 		
				if ( !is_array($planValue))
					{
					
					// for hz 
					//
					/*if ( $planValue{0} == '@' )
						{
						$paramName = substr($planValue, 1);
						if ( isset($databaseInfo[HOST_DBSETTINGS][$paramName]) )
							return $databaseInfo[HOST_DBSETTINGS][$paramName];
		
						return null;
						}
					else*/
					if (empty($planValue)) 
						{
						if ( isset($mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID]) )
							$for_return =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID];
						}
					else 
						{
						$for_return =  $planValue;
						}
					
					}
				elseif ( is_array($planValue)) 
					{
					$for_return = $planValue[$APP_ID];
					}
				}
 
			if ( isset($mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID]) )
				$for_return =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID];
				
			$for_return = ($for_return == -1) ? null : $for_return;
			return $for_return;
			}
		
		// System settings 
		//
		if ( $APP_ID == AA_APP_ID && !is_null($resource) ) // FIXME !!!!!!!!!!!!!!!
			{
 
 			if($resource == 'SPACE')
 				$resource = HOST_DBSIZE_LIMIT;  
 				
 			if($resource == 'USERS')
 				$resource = HOST_MAXUSERCOUNT; 

			$settingName = $resource;// == 'USERS' ? HOST_MAXUSERCOUNT : HOST_DBSIZE_LIMIT;
			
			$paramValue = $databaseInfo[HOST_DBSETTINGS][$settingName];
			
			 
			// for old billing 
			//
			if ( $plan == HOST_DEFAULT_PLAN )
				{
 				$for_return =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID][$settingName];	
				}
			elseif ( $plan == HOST_CUSTOM_PLAN && !empty($paramValue))
				{
				$for_return =  $paramValue;	
				}
			elseif ( $plan == HOST_CUSTOM_PLAN && empty($paramValue))
				{
				if ( isset($mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID][$settingName]) )
					$for_return =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID][$settingName];
				else
					$for_return = 0;
				}
			else
				{
				// for other plans
				//
 
				if ( isset($mt_hosting_plan_settings[$plan][$APP_ID][$settingName]) )
					$for_return =  $mt_hosting_plan_settings[$plan][$APP_ID][$settingName];

				else// ( isset($mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID][$settingName]) )
					$for_return =  $mt_hosting_plan_settings[HOST_DEFAULT_PLAN][$APP_ID][$settingName];

				}
				
			$for_return = ($for_return == -1) ? null : $for_return;
			return $for_return;
			}
		
			
		if ($resource != null && isset($mt_hosting_plan_settings[$plan][$APP_ID])) 
			{
			$limit = $mt_hosting_plan_settings[$plan][$APP_ID];
			if(isset($mt_Price['eng'][$APP_ID][$limit]['RESTRICTION'][$resource]))
				{
				$for_return =  $mt_Price['eng'][$APP_ID][$limit]['RESTRICTION'][$resource];
				}
			}
		elseif (isset($mt_hosting_plan_settings[$plan][$APP_ID])) 
			{
			$for_return =  $mt_hosting_plan_settings[$plan][$APP_ID];
			}
			
		$for_return = ($for_return == -1) ? null : $for_return;
		return $for_return;
	}

	function isHostingAccount()
	//
	// Determines whether account is a WebAsyst hosting account
	//
	{
		global $databaseInfo;

		return isset($databaseInfo[HOST_DBSETTINGS][HOST_BILLINGDATE]) && strlen($databaseInfo[HOST_DBSETTINGS][HOST_BILLINGDATE]);
	}

	function showBillingAlert()
	//
	// Determines whether the billing alert is visible
	//
	//		Returns boolean
	//
	{
		global $databaseInfo;

		if ( !isHostingAccount() )
			return false;

		$days = getDaysBeforeSuspend();
 
		return $days <= BILLING_BEFORE_SUSPEND_DAYS && (!isset($databaseInfo[HOST_DBSETTINGS][HOST_AUTORENEW]) || !$databaseInfo[HOST_DBSETTINGS][HOST_AUTORENEW] );
	}

	function hasAccountInfoAccess( $U_ID )
	//
	// Checks whether the user has rights to access the Account Information screen
	//
	//		Parameters:
	//			$U_ID - user identifier
	//
	//		Returns boolean
	//
	{
		$screens = listUserScreens($U_ID);
		
		if ( !isset($screens['AA']) )
			return  false;

		return in_array('CP', $screens['AA']);
	}

	function onWebAsystServer()
	//
	// Determines whether the account resides on the WebAsyst server
	//
	//		Returns boolean
	//
	{
		return file_exists(WBS_DIR."kernel/hosting_plans.php");
	}

	function getDaysBeforeSuspend()
	//
	// Returns a number of days before the account will be suspended
	//
	//		Returns null
	//
	{
		global $databaseInfo;

		if ( !isset($databaseInfo[HOST_DBSETTINGS][HOST_BILLINGDATE]) || !strlen($databaseInfo[HOST_DBSETTINGS][HOST_BILLINGDATE]) )
			return null;

		require_once( WBS_DIR."kernel/includes/dateclass.php" );

		$span = new DateSpanClass();

		$billingDate = $databaseInfo[HOST_DBSETTINGS][HOST_BILLINGDATE];

		$suspendDate = date( 'Y-m-d', strtotime($billingDate) );

		return $span->Days( date('Y-m-d'), $suspendDate );
	}

	//
	// Debug and instrumentation functions
	//

	function getmicrotime()
	//
	// Returns time with microseconds
	//
	//		Returns float
	//
	{
		list($usec, $sec) = explode(" ", microtime());

		return ((float)$usec + (float)$sec);
	}

	function initTimeMarkers( $markerSet )
	//
	// Resets time markers counter
	//
	//		Parameters:
	//			$markerSet - marker set name
	//
	//		Returns null
	//
	{
		$GLOBALS[$markerSet] = getmicrotime();

		return null;
	}

	function putTimeMarker( $markerSet, $name, $br = "<br>" )
	//
	// Outputs time marker to the page
	//
	//		Parameters:
	//			$markerSet - marker set name
	//			$name - marker display name
	//
	//		Returns null
	//
	{
		$prevValue = $GLOBALS[$markerSet];

		$newValue = getmicrotime();
		$diff = $newValue - $prevValue;
		$GLOBALS[$markerSet] = $newValue;

		echo "$markerSet/$name: $diff $br";

		return null;
	}

	function getWBSHost(){
		
		if(preg_match('@(?:(?:^dev\.|^test\.|^qa\.|^www\.|(?<=\.)dev\.|(?<=\.)test\.|(?<=\.)qa\.|(?<=\.)dev\.yug\.|(?<=\.)test\.yug\.|(?<=\.)qa\.yug\.)webasyst\.net)@ui', $_SERVER['HTTP_HOST'], $p_results)) {
	
			if($p_results[0] == 'webasyst.net')$p_results[0] = 'www.'.$p_results[0];
			return $p_results[0];
		}else{
			return 'webasyst.net';
		}
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
	
	function renderURL($_vars = '', $_request = '', $_store = false){
		
		$RenderedURL = '';
		
		if(!$_request){
			
			$_request = $_SERVER['REQUEST_URI'];
			$GetVars = $_GET;
		}else{
			
			$GetVars = renderGetVars($_request);
		}
		
		if(!strlen($_vars))
			return $_request;
		
		/**
		 * Set render mode
		 */
		if(strpos($_vars,'?')!==false){
			
			$Mode = URLRENDMODE_RESET;
			$_vars = substr($_vars, 1, strlen($_vars)-1);
		}else{
			
			$Mode = URLRENDMODE_MODIFY;
		}
		
		/**
		 * trim first ampersand
		 */
		if($_vars[0]=='&')
			$_vars = substr($_vars, 1, strlen($_vars)-1);
		
		/**
		 * Render new get-tokens
		 */
		$ReceivedTokens = array();
		$r_TokenStrs = explode('&', $_vars);
		foreach ($r_TokenStrs as $TokenStr){
			
			$r_Token = explode('=', $TokenStr,2);
			if(isset($r_Token[1])&&$r_Token[1]){
				
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
						if(isset($GetVars[$r_Token[0]]))
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
		if($_store){
			
			$_GET = $newGetVars;
		}
		foreach ($newGetVars as $TokenName=>$TokenValue){
			
			$newGetVars[$TokenName] = $TokenName.'='.$TokenValue;
		}
		$RenderedURL = implode('&', $newGetVars);
		if(strpos($_request, '?')!==false){
			
			$RenderedURL = preg_replace('/\?.*$/u','?'.$RenderedURL,$_request);
		}else {
			
			$RenderedURL = $_request.'?'.$RenderedURL;
		}
		
		/**
		 * Strore URL
		 */
		if($_store){
			
			$_SERVER['REQUEST_URI'] = $RenderedURL;
		}
		
		return $RenderedURL;
	}

	/**
	 * function for smarty modificator |translate
	 *
	 * @param string $string
	 * @return string translated or self string
	 */
	function translate($string)
	{
		global $LocalizationStrings;
		if(isset($LocalizationStrings)&&is_array($LocalizationStrings)){
			return (isset($LocalizationStrings[$string])?$LocalizationStrings[$string]:$string);
		}
		/*global $kernelStrings;
		if(isset($kernelStrings)&&is_array($kernelStrings)){
			return (isset($kernelStrings[$string])?$kernelStrings[$string]:$string).':$kernelStrings';
		}*/
		return $string;
	}
?>