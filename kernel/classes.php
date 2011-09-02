<?php

	require_once('class.classmanager.php');
	require_once( WBS_DIR."kernel/includes/modules/phpmailer/class.phpmailer.php" );
	require_once( WBS_DIR."kernel/includes/modules/phpmailer/class.smtp.php" );

	//
	// Kernel classes
	//

	define( 't_integer', 'int' );
	define( 't_float', 'float' );
	define( 't_string', 'string' );
	define( 't_time', 'time' );
	define( 't_datetime', 'datetime' );
	define( 't_date', 'date' );
	define( 't_color', 'color' );
	define( 't_select', 'select' );
	define( 't_radio', 'radio' );

	define( 's_database', 'database' );
	define( 's_form', 'form' );
	define( 's_datasource', 'source' );

	class cbReturnStatus
	{
		var $code;
		var $statusStr;

		function cbReturnStatus( $aCode, $aStatusStr )
		{
			$this->code = $aCode;
			$this->statusStr = $aStatusStr;
		}
	}

	class fieldDescriptor
	{
		var $fieldName;
		var $fieldType;
		var $fieldLength;
		var $isRequired;
		var $label;
		var $viewType;
		var $options;

		function fieldDescriptor( $fieldName, $fieldType, $fieldLength, $isRequired, $fieldLabel, $viewType, $options )
		{
			$this->fieldName = $fieldName;
			$this->fieldType = $fieldType;
			$this->fieldLength = $fieldLength;
			$this->isRequired = $isRequired;
			$this->label = $fieldLabel;
			$this->viewType = $viewType;
			$this->options = $options;
		}
	}

	class dataDescription
	{
		var $fields;

		function dataDescription()
		{
			$this->fields = array();
		}

		function addFieldDescription( $fieldName, $fieldType, $isRequired, $fieldLength = null, $fieldLabel = null, $viewType = "TEXT", $options = "" )
		{
			$this->fields[$fieldName] = new fieldDescriptor( $fieldName, $fieldType, $fieldLength, $isRequired, $fieldLabel, $viewType, $options );
		}

		function getFieldDescriptor( $fieldName )
		{
			if ( array_key_exists( $fieldName, $this->fields ) )
				return $this->fields[$fieldName];

			return null;
		}
	}

	class arrayAdaptedClass
	{
		var $dataDescrition;

		function loadFromArray( $array, $kernelStrings, $performDataChecking = true, $params = null )
		{
			global $_PEAR_default_error_mode;
			global $_PEAR_default_error_options;
			global $dateFormats;

			$thisFields = array_keys((array)$array);

			if ( !is_null($this->dataDescrition) && $performDataChecking )
				foreach( $thisFields as $field ) {
					$descriptor = $this->dataDescrition->getFieldDescriptor($field);
					//PEAR::raiseError( $field );

					if ( !is_null($descriptor) ) {
						if ( !array_key_exists( $field, $array ) )
							$value = null;
						else
							$value = $array[$field];

						switch ( $descriptor->fieldType ) {
							case t_integer :
											if ( strlen($value) && !isIntStr($value) ) {
												return PEAR::raiseError ( sprintf($kernelStrings[ERR_INVALIDNUMFORMAT], $value),
																			ERRCODE_INVALIDFIELD,
																			$_PEAR_default_error_mode,
																			$_PEAR_default_error_options, $field);
											}
											break;
							case t_float :
											if ( strlen($value) && !isFloatStr($value) ) {
												return PEAR::raiseError ( sprintf($kernelStrings[ERR_INVALIDNUMFORMAT], $value),
																			ERRCODE_INVALIDFIELD,
																			$_PEAR_default_error_mode,
																			$_PEAR_default_error_options, $field);
											}
											break;
							case t_time:
											if ( strlen($value) && !isTimeStr($value) )
												return PEAR::raiseError ( sprintf($kernelStrings['app_invtimeformat_message'], $value),
																			ERRCODE_INVALIDFIELD,
																			$_PEAR_default_error_mode,
																			$_PEAR_default_error_options, $field);

											break;

							case t_string :
											if ( is_null($descriptor->fieldLength) )
												break;

											if ( strlen($value) > $descriptor->fieldLength ) {
												return PEAR::raiseError ( sprintf($kernelStrings[ERR_TEXTLENGTH], $value),
																			ERRCODE_INVALIDFIELD,
																			$_PEAR_default_error_mode,
																			$_PEAR_default_error_options, $field);
											}

											break;

							case t_date :
											$timestamp = null;
											if ( strlen($value) && !validateInputDate( $value, $timestamp ) )
												return PEAR::raiseError ( sprintf($kernelStrings[ERR_DATEFORMAT], $dateFormats[DATE_DISPLAY_FORMAT]),
																			ERRCODE_INVALIDFIELD,
																			$_PEAR_default_error_mode,
																			$_PEAR_default_error_options, $field);


											break;

							case t_color :

											if ( strlen($value) && !isColorStr($value) )
												return PEAR::raiseError ( sprintf($kernelStrings['app_invcolorformat_message'], $value),
																			ERRCODE_INVALIDFIELD,
																			$_PEAR_default_error_mode,
																			$_PEAR_default_error_options, $field);

											break;

							case t_select :

											if ( in_array( $value, array_keys( $descriptor->options ) ) )
												break;

											return PEAR::raiseError ( sprintf($kernelStrings[ERR_INVALIDSELECTVALUE], $value),
																			ERRCODE_INVALIDFIELD,
																			$_PEAR_default_error_mode,
																			$_PEAR_default_error_options, $field);

											break;

							case t_radio :

											if ( in_array( $value, array_keys( $descriptor->options ) ) )
												break;

											return PEAR::raiseError ( sprintf($kernelStrings[ERR_INVALIDSELECTVALUE], $value),
																			ERRCODE_INVALIDFIELD,
																			$_PEAR_default_error_mode,
																			$_PEAR_default_error_options, $field);

											break;
						}

						if ( $descriptor->isRequired )
							if ( !strlen($value) ) {
								//PEAR::raiseError( $kernelStrings[ERR_REQUIREDFIELDS].' '.$field );
								return PEAR::raiseError ( $kernelStrings[ERR_REQUIREDFIELDS],
															ERRCODE_INVALIDFIELD,
															$_PEAR_default_error_mode,
															$_PEAR_default_error_options,
															$field);

							}

						if ( PEAR::isError( $res = $this->onValidateField( $array, $field, $value, $params ) ) ){
							PEAR::raiseError( "Error filed ". $field );
							return $res;
						}
					}
				}

			if ( PEAR::isError( $res = $this->onBeforeSet( $array, $params ) ) )
				return $res;

			foreach( $thisFields as $field )
			{
				if ( array_key_exists( $field, $array ) )
					$this->$field = $array[$field];
			}

			$this->onAfterSet( $array, $params );
		}

		function onBeforeSet( $array, $params = null )
		{
			return true;
		}

		function onAfterSet( $array, $params = null )
		{
			return true;
		}

		function onValidateField( $array, $fieldName, $fieldValue, &$params )
		{
			return true;
		}

	}


	class wbsParameters extends arrayAdaptedClass
	{
			var $xmlError = "wbsParameters: Error processing XML data";

			function wbsParameters( $params = null )
			{
				if ( $params != null )
				{
					if ( is_array( $params ) )
						$this->createDataDescriptionsArray( $params );
					else
						$this->createDataDescriptionsXML( $params );
				}
			}

			function set( $key, $value='' )
			{
				$this->$key = $value;

				return $value;
			 }

			function def( $key, $value='' )
			{
				return $this->set( $key, $this->get( $key, $value ) );
			}

			function get( $key, $default='' )
			{
				if (isset( $this->$key ))
				{
					return ( $this->$key == '' ) ? $default : $this->$key;
				} else {
					return $default;
				}
			}

			function createDataDescriptionsArray( $array )
			{
				$data = new dataDescription();

				foreach( $array as $name=>$attrs )
				{
						switch( strtolower( $attrs["TYPE"] ) )
						{
							case "text":
								$vType = "TEXT";
								$fType = t_string;
								break;

							case "url":
								$vType = "URL";
								$fType = t_string;
								break;

							case "memo":
								$vType = "MEMO";
								$fType = t_string;
								break;

							case "email":
								$vType = "EMAIL";
								$fType = t_string;
								break;

							case "numeric":
								$vType = "NUMERIC";
								$fType = t_integer;
								break;

							case "float":
								$vType = "FLOAT";
								$fType = t_float;
								break;

							case "time":
								$vType = "TIME";
								$fType = t_time;
								break;

							case "date":
								$vType = "DATE";
								$fType = t_date;
								break;

							case "color":
								$vType = "COLOR";
								$fType = t_color;
								break;

							case "select":
								$vType = "SELECT";
								$fType = t_select;
								break;

							case "radio":
								$vType = "RADIO";
								$fType = t_radio;
								break;

							default:
								$fType = t_string;
								$vType = "TEXT";
								break;
						}

						$options = array();

						if ( ( t_select == $fType || t_radio == $fType ) && isset( $attrs["OPTIONS"] ) )
						{
							foreach( $attrs["OPTIONS"] as $value )
								$options[$value["VALUE"]] = $value["NAME"];
						}

						$data->addFieldDescription(
													$name,
													$fType,
													isset( $attrs["REQ"] ) ? $attrs["REQ"] : 0 ,
													isset( $attrs["LEN"] ) ? $attrs["LEN"] : null,
													isset( $attrs["LABEL"] ) ? $attrs["LABEL"] : null,
													$vType,
													$options
												);

						$this->set( $name, isset( $attrs["DEFAULT"] ) ? $attrs["DEFAULT"] : null );
				}

				$this->dataDescrition = $data;
			}

			function createDataDescriptionsXML( $xml )
			{

				$dom = @domxml_open_mem( $xml );
				if ( !$dom )
					return PEAR::raiseError( $this->xmlError );

				$xpath = @xpath_new_context( $dom );

				$params = xpath_eval($xpath, "/wbsparams/param");


				return $this->createDataDescriptionsXMLPath( $params );
			}

			function createDataDescriptionsXMLPath( $params )
			{

				if ( !count($params->nodeset) )
					return false;

				$data = new dataDescription();

				foreach( $params->nodeset as $param )
				{
					$attrs= getAttributeValues( $param );

					if ( !isset( $attrs["name"] ) )
						continue;

					if ( !isset( $attrs["special"] ) )
					{

						switch( strtolower( $attrs["type"] ) )
						{
							case "text":
								$vType = "TEXT";
								$fType = t_string;
								break;

							case "url":
								$vType = "URL";
								$fType = t_string;
								break;

							case "memo":
								$vType = "MEMO";
								$fType = t_string;
								break;

							case "email":
								$vType = "EMAIL";
								$fType = t_string;
								break;

							case "numeric":
								$vType = "NUMERIC";
								$fType = t_integer;
								break;

							case "float":
								$vType = "FLOAT";
								$fType = t_float;
								break;

							case "time":
								$vType = "TIME";
								$fType = t_time;
								break;

							case "date":
								$vType = "DATE";
								$fType = t_date;
								break;

							case "color":
								$vType = "COLOR";
								$fType = t_color;
								break;

							case "select":
								$vType = "SELECT";
								$fType = t_select;
								break;

							case "radio":
								$vType = "RADIO";
								$fType = t_radio;
								break;

							default:
								$fType = t_string;
								$vType = "TEXT";
								break;
						}

						$options = array();

						if ( ( t_select == $fType || t_radio == $fType ) && $param->has_child_nodes() )
						{
							$child = $param->first_child();

							while ($child)
							{
								if ( "option" == strtolower( $child->node_name() ) )
								{
									$value = $child->get_attribute ( "value" );
									$options[$value ] = $child->get_content();
								}
								$child = $child->next_sibling();
							}

						}

						$data->addFieldDescription(	$attrs["name"],
												$fType,
												isset( $attrs["req"] ) ? $attrs["req"] : 0 ,
												isset( $attrs["len"] ) ? $attrs["len"] : null,
												isset( $attrs["label"] ) ? $attrs["label"] : null,
												$vType,
												$options
											);

						// $this->set( $name, isset( $attrs["DEFAULT"] ) ? $attrs["DEFAULT"] : null );
						if ( isset( $attrs["default"] ) )
							$this->set( $attrs["name"], $attrs["default"] );
					}
					else
					{
						$this->set( $attrs["name"], $param->get_content() );
					}
				}

				$this->dataDescrition = $data;
			}

			function loadFromXMLNodesArray( $params, $kernelStrings, $performDataChecking = true, $parameters = null )
			{
				$data = array();

				foreach( $params as $param )
				{
					$attrs= getAttributeValues( $param );

					if ( !isset( $attrs["name"] ) )
						continue;

					$data[$attrs["name"]] = $param->get_content();
				}

				return $this->loadFromArray( $data, $kernelStrings, $performDataChecking, $parameters );
			}

			function loadFromXMLPath( $params, $kernelStrings, $performDataChecking = true, $parameters = null )
			{
				if ( !count($params->nodeset) )
					return false;

				$data = array();

				foreach( $params->nodeset as $param )
				{
					$attrs= getAttributeValues( $param );

					if ( !isset( $attrs["name"] ) )
						continue;

					$data[$attrs["name"]] = $param->get_content();
				}

				return $this->loadFromArray( $data, $kernelStrings, $performDataChecking, $parameters );
			}

			function loadFromXML( $xml, $kernelStrings, $performDataChecking = true, $parameters = null )
			{
				$dom = @domxml_open_mem( $xml );
				if ( !$dom )
					return PEAR::raiseError( $this->xmlError );

				$xpath = @xpath_new_context( $dom );

				$params = xpath_eval($xpath, "/values/value");

				return $this->loadFromXMLPath( $params, $kernelStrings, $performDataChecking, $parameters );
			}

			function getValuesXML( )
			{
				$dom = @domxml_new_doc("1.0");

				if ( !$dom )
					return PEAR::raiseError( $this->xmlError );

				$this->addValuesToDOMNode( $dom, $dom );

				return $dom->dump_mem();
			}

			function addValuesToDOMNode( &$dom, &$parent )
			{
				$root = @create_addElement( $dom, $parent, "values" );

				$thisFields = array_keys((array)$this);

				foreach( $thisFields as $field )
				{
					$descriptor = is_object( $this->dataDescrition ) ? $this->dataDescrition->getFieldDescriptor( $field ) : null;

					if ( !is_null($descriptor) )
					{
						$val = @create_addElement( $dom, $root, "value" );

						$val->set_attribute( "name", $field );
						$val->set_content( $this->$field );
					}
				}

				return $root;
			}

			function getValuesArray( )
			{
				$thisFields = array_keys((array)$this);

				$result = array();

				foreach( $thisFields as $field )
				{

					$descriptor = is_object( $this->dataDescrition ) ? $this->dataDescrition->getFieldDescriptor( $field ) : null;

					if ( !is_null($descriptor) )
						$result[$field] = $this->$field;

				}

				return $result;
			}


			function getFieldsArray( )
			{
				$result = array();

				$thisFields = array_keys((array)$this);

				if ( !is_null($this->dataDescrition) )
					foreach( $thisFields as $field )
					{

						if ( !is_null( $descriptor = $this->dataDescrition->getFieldDescriptor($field) ) )
						{
							$res["NAME"] =  $descriptor->fieldName;
							$res["DBFIELD"] =  $descriptor->fieldName;

							$res["TYPE"] =  $descriptor->viewType;
							$res["FTYPE"] =  $descriptor->fieldType;

							$res["LEN"] = $descriptor->fieldLength;
							$res["REQ"] = $descriptor->isRequired;

							$res["LABEL"] = $descriptor->label;

							$res["OPTIONS"] =  $descriptor->options;

							$res["VALUE"] =  $this->$field;

							$result[$descriptor->fieldName] = $res;
						}

					}

				return $result;
			}
	}

	//
	// Mail classes
	//

	class mailComposer {
		var $mainboundary = "----=_NextPart_000_0107_01C47899.";
		var $boundary = "----=_NextPart_000_0107_01C47899.";
		var $images = "";
		var $fromName = "";
		var $fromReply = "";
		var $priority = "";
		var $codePage = "Windows-1251";
		var $attachImages = false;
		var $contentType = "";
		var $htmlEncode = HTMLE_BASE64;
		var $files = array();

		function mailComposer()
		{
			$this->mainboundary = $this->mainboundary . strtoupper(uniqid(rand()));
			$this->boundary = $this->boundary . strtoupper(uniqid(rand()));
		}

		function compose($html, $text = "")
		{
			if ( !strlen( trim($html) ) && strlen(trim($text)) ) {
				$this->contentType = "From: \"".$this->fromName."\" <".$this->fromReply.">\nReturn-path: \"".$this->fromName."\" <".$this->fromReply.">\nContent-Type: text/plain;charset=\"".$this->codePage."\"";

				$m = $text;

				if (count($this->files) > 0) {
					$m = "This is a multi-part message in MIME format.\n\n";

					$this->contentType = "From: \"".$this->fromName."\" <".$this->fromReply.">\nReturn-path: \"".$this->fromName."\" <".$this->fromReply.">\nContent-Type: multipart/mixed;\n	 boundary=\"" . $this->mainboundary . "\"\n\n";

					$m .= "--" . $this->mainboundary . "\nContent-Type: text/plain;\n	 charset=\"" . $this->codePage . "\"\nContent-Transfer-Encoding: quoted-printable\n\n";
					$m .= $this->quotedPrintableEncode($text);

					foreach($this->files as $name => $file)
						$m .= $this->attachDataEx(basename($name), $file);

					$m .= "--" . $this->mainboundary . "--\n\n";
				}

				return $m;
			}

			if ( isset($_SERVER["OS"]) && $_SERVER["OS"] == "Windows_NT")
				$this->images = str_replace("/", "\\", $this->images);

			$m = "This is a multi-part message in MIME format.\n\n";

			$m .= "--" . $this->mainboundary . "\nContent-Type: multipart/alternative;\n	 boundary=\"" . $this->boundary . "\"\n\n";
			$this->contentType = "From: \"".$this->fromName."\" <".$this->fromReply.">\nReturn-path: \"".$this->fromName."\" <".$this->fromReply.">\nContent-Type: multipart/mixed;\n	 boundary=\"" . $this->mainboundary . "\"\n\n";

			if ( trim($text) != "" ) {
				$m .= "--" . $this->boundary . "\nContent-Type: text/plain;\n	 charset=\"" . $this->codePage . "\"\nContent-Transfer-Encoding: quoted-printable\n\n";
				$m .= $this->quotedPrintableEncode($text);
			}

			if ($this->attachImages) {
				$img = array();
				$bg = array();
				$cid = array();

				// make images list
				if (preg_match_all("/src=[\"]*([_a-zA-Z\d\-\.\/]+)[\"]*[\s\>]*/ui", $html, $img)) {
					if (count($img) > 0) {
						$img = $img[1];
					} else {
						$img = array();
					}
				}
				if (preg_match_all("/background=[\"]*([_a-zA-Z\d\-\.\/]+)[\"]*[\s\>]*/ui", $html, $bg)) {
					if (count($bg) > 0) {
						$img = array_merge($img, $bg[1]);
					}
				}
				$img = array_unique($img);

				$i = 0;
				reset($img);
				while (list($key, $val) = each($img)) {
					@$cid[$val] = "001201c0f5a3790a33c00300a8c0" . $i++;
				}

				reset($cid);
				while (list($key, $val) = each($cid)) {
					$html = str_replace($key, "cid:" . $val, $html);
				}

				$m .= $this->attachData($html);

				reset($cid);
				while (list($key, $val) = each($cid)) {
					if (trim($key) == "") continue;
					if (!file_exists($this->images . $key)) continue;
					$m .= $this->attach($this->images . $key, $val);
				}
			} else {
				$m .= $this->attachData($html);
			}

			foreach($this->files as $name => $file) {
				$m .= $this->attachDataEx(basename($name), $file);
			}

			$m .= "--" . $this->mainboundary . "--\n\n";

			return $m;
		}

		function quotedPrintableEncode($input, $line_max = 76) {
			$lines  = explode( "\n", $input );
			$eol = "\n";
			$escape = '=';
			$output = '';

			while(list(, $line) = each($lines)) {
					$linlen	  = strlen($line);
					$newline = '';

					for ($i = 0; $i < $linlen; $i++) {
							$char = substr($line, $i, 1);
							$dec    = ord($char);

							if (($dec == 32) AND ($i == ($linlen - 1))){	    // convert space at eol only
									$char = '=20';

							} elseif($dec == 9) {
									; // Do nothing if a tab.
							} elseif(($dec == 61) OR ($dec < 32 ) OR ($dec > 126)) {
									$char = $escape . strtoupper(sprintf('%02s', dechex($dec)));
							}

							if ((strlen($newline) + strlen($char)) >= $line_max) {			  // MAIL_MIMEPART_CRLF is not counted
									$output .= $newline . $escape . $eol;									   // soft line break; " =\r\n" is okay
									$newline	= '';
							}
							$newline .= $char;
					} // end of for
					$output .= $newline . $eol;
			}
			$output = substr($output, 0, -1 * strlen($eol)); // Don't want last crlf
			return $output;
		}

		function attachData($data) {
			$enc = "quoted-printable";
			if ($this->htmlEncode == HTMLE_BASE64) {
				$enc = "base64";
			}
			$r = "\n\n--" . $this->boundary . "\nContent-Type: text/html;\n	 charset=\"" . $this->codePage . "\"\nContent-Transfer-Encoding: " . $enc . "\n\n";
			if (strtolower($this->codePage) == "koi8-r") {
				$data = convert_cyr_string($data, "w", "k");
			}
			if ($this->htmlEncode == HTMLE_QUOTEDPRINTABLE) {
				$r .= $this->quotedPrintableEncode($data) . "\n";
			} else {
				$r .= chunk_split(base64_encode($data)) . "\n";
			}
			$r .= "--" . $this->boundary . "--\n\n";
			return $r;
		}

		function attachDataEx($name, $data) {
			$r = "\n\n--" . $this->mainboundary . "\nContent-Type: application/octet-stream;\n	name=\"" . $name . "\"\nContent-Transfer-Encoding: base64\n\n";
			$r .= chunk_split(base64_encode($data)) . "\n";
			return $r;
		}

		function attachfile($file) {
			if (!file_exists($file))
				return;

			if ( isset($this->files[$file]) && strlen($this->files[$file]) )
				return;

			$fp = @fopen($file, "rb");
			$data = "";
			while (!feof($fp)) {
				$data .= fread($fp, 8192);
			}
			fclose($fp);

			$this->files[$file] = $data;
		}

		function clearfiles() {
			$this->files = array();
		}
	}

	class WBSMailer extends PHPMailer
	//
	// WebAsyst PHP Mailer class extension
	//
	{

		var $replaceBR = true;

		function WBSMailer( $replaceBR = true )
		{
			$this->replaceBR = $replaceBR;
			$this->SetLanguage("en", WBS_DIR.'kernel/includes/modules/phpmailer/');
			
			global $wbs_smtp_settings;
			$settings_map = array(
				'Host'=>'host',
				'Port'=>'port',
				//'Helo'=>'helo',
				//'Hostname'=>'SMTP_HELO',
				//'SMTPAuth'=>'SMTP_AUTH',
				'Username'=>'user',
				'Password'=>'password'
			);
			$this->CharSet = 'utf-8';
												
			if(isset($wbs_smtp_settings['host'])&&$wbs_smtp_settings['host']){
				$this->IsSMTP(true);
				foreach($settings_map as $setting => $param){
					if(isset($wbs_smtp_settings[$param])){
						$this->$setting = $wbs_smtp_settings[$param];
					}
				}
				$this->Helo = $_SERVER['HTTP_HOST'];
			}
			
			
		}

		function Send( $email = false, $leaveHeaders = false )
		{
			global $sendmail_enabled;
			
			if ( !$sendmail_enabled )
				return true;

			if ( $this->ContentType == "text/plain" && $this->replaceBR == true )
			{
				$this->Body = str_replace( "\n", "<br>", $this->Body );
				$this->AltBody = str_replace( '&nbsp;' ,' ', strip_tags( $this->AltBody ) );
			}
			//if (onWebAsystServer()) {
			//	$s_to=var_export($this->to, true);
			//	$f=fopen(WBS_DIR.'temp/wa_mail.log', 'a+');		
			//	fwrite($f, date("Y-m-d H:i:s")." ".$_SERVER["HTTP_HOST"].", Subject: ".$this->Subject."\nFrom: ".$this->From."\nTo: ".$s_to."---\n");
			//	fclose($f);
			//}
			$res = parent::Send( $email, $leaveHeaders );
			if(!$res){
				$logPath = WBS_DIR.'/temp/log';
				if(!is_dir($logPath)){
					mkdir($logPath);
				}
				
				if($fp = fopen($logPath.'/send.log','a')){
					global $DB_KEY;
					fwrite($fp,sprintf("%s DB_KEY=%s %s\n",date('Y-m-d H:i:s'),$DB_KEY,$this->ErrorInfo));
					fclose($fp);
				}
			}
			return $res;
		}

		function AddAddress( $address, $name = "" )
		{
			$recipientName = null;
			$recipientAddress = extractEmailAddress( $address, $recipientName );
			if ( strlen($recipientName) )
				$name = $recipientName;
			
			return parent::AddAddress( $recipientAddress, $name );
		}

		function AddBCC( $address, $name = "" )
		{
			$recipientName = null;
			$recipientAddress = extractEmailAddress( $address, $recipientName );
			if ( strlen($recipientName) )
				$name = $recipientName;

			return parent::AddBCC( $recipientAddress, $name );
		}

		function AddCC( $address, $name = "" )
		{
			$recipientName = null;
			$recipientAddress = extractEmailAddress( $address, $recipientName );
			if ( strlen($recipientName) )
				$name = $recipientName;

			return parent::AddCC( $recipientAddress, $name );
		}

		function AddFrom( $address, $name = "" )
		{
			$recipientName = null;
			$recipientAddress = extractEmailAddress( $address, $recipientName );

			if ( strlen($recipientName) )
				$name = $recipientName;

			$this->From = $recipientAddress;
			$this->FromName = $recipientName;

			return true;
		}

		function AddReplyTo( $address, $name = "" )
		{
			$recipientName = null;
			$recipientAddress = extractEmailAddress( $address, $recipientName );
			if ( strlen( $recipientName ) )
				$name = $recipientName;

			return parent::AddReplyTo($recipientAddress, $name );
		}

		function addWBSUserAddress($U_ID)
		{
			global $qr_selectUser;

			$senderName = "";
			$senderName = getUserName( $U_ID, true );

			$senderData = array( "U_ID"=>$U_ID );

			if ( PEAR::isError( exec_sql( $qr_selectUser, $senderData, $senderData, true ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$senderEmail = $senderData['C_EMAILADDRESS'];

			$this->AddAddress( $senderEmail, $senderName );
		}
	}

	//
	// Tree document-folder representation classes
	//

	define( 'LFU_UNIFORMLIST', 'UNIFORM' );
	define( 'LFU_GROUPSANDUSERS', 'GROUPSANDUSERS' );

	define( 'LFU_USERS', 'USERS' );
	define( 'LFU_GROUPS', 'GROUPS' );
	define( 'LFU_USERSTOTAL', 'USERSTOTAL' );
	define( 'LFU_GROUPSTOTAL', 'GROUPSTOTAL' );
	define( 'LFU_USERSLIMITED', 'USERSLIMITED' );
	define( 'LFU_GROUPSLIMITED', 'GROUPSLIMITED' );
	define( 'LFU_MYPERMISSIONS', 'MYPERMISSIONS' );

	define( 'ACCESSINHERITANCE_COPY', 'COPY' );
	define( 'ACCESSINHERITANCE_INHERIT', 'INHERIT' );

	function sortFolderUserList( $a, $b )
	{
		return strcmp( $a['USER_NAME'], $b['USER_NAME'] );
	}

	class treeFolderTableDescriptor
	{
		var $tableName;

		var $folder_id_field;
		var $folder_name_field;
		var $folder_parent_field;
		var $folder_status_field;
		
		var $folder_rights_path="";
		var $folder_order_by_str = "DF.FOLDER_NAME_FIELD";
		var $folder_specialstatus_field = null;

		function treeFolderTableDescriptor( $tableName, $folder_id_field, $folder_name_field, $folder_parent_field, $folder_status_field )
		{
			$this->tableName = $tableName;

			$this->folder_id_field = $folder_id_field;
			$this->folder_name_field = $folder_name_field;
			$this->folder_parent_field = $folder_parent_field;
			$this->folder_status_field = $folder_status_field;
		}

		function SetRightsPath( $rightsPath )
		{
			$this->folder_rights_path = $rightsPath;
		}

	}

	class treeFolderAccessTableDescriptor
	{
		var $tableName;

		var $user_id_field;
		var $access_right_field;

		function treeFolderAccessTableDescriptor( $tableName, $user_id_field, $access_right_field )
		{
			$this->tableName = $tableName;

			$this->user_id_field = $user_id_field;
			$this->access_right_field = $access_right_field;

		}
	}

	class treeFolderGroupAccessTableDescriptor
	{
		var $tableName;

		var $group_id_field;
		var $group_access_right_field;

		function treeFolderGroupAccessTableDescriptor( $tableName, $group_id_field, $group_access_right_field )
		{
			$this->tableName = $tableName;

			$this->group_id_field = $group_id_field;
			$this->group_access_right_field = $group_access_right_field;

		}
	}

	class treeDocumentsTableDescriptor
	{
		var $tableName;

		var $document_id_field;
		var $document_status_field;
		var $document_modifyuid_field;

		function treeDocumentsTableDescriptor( $tableName, $document_id_field, $document_status_field, $document_modifyuid_field = "" )
		{
			$this->tableName = $tableName;

			$this->document_id_field = $document_id_field;
			$this->document_status_field = $document_status_field;
			$this->document_modifyuid_field = $document_modifyuid_field;

		}
	}

	class genericDocumentFolderTree
	{
		//
		// Generic class for building folder/document type applications
		//

		//
		// Method list (see method implementation for details)
		//
		// function addmodFolder( $action, $U_ID, $ID_PARENT, $folderdata, $kernelStrings, $admin, $createCallback = null, $callbackParams = null, $propagateFolderRights = true, $suppressNotifications = false, $folderStatus = null, $checkFolderName = true, $onBeforeFolderCreate = null )
		// function applySQLObjectNames( $query )
		// function canCreateFolders( $U_ID, $kernelStrings )
		// function copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null, $callbackParams = null, $onFininshCopy = null, $accessInheritance = ACCESSINHERITANCE_COPY, $onBeforeFolderCreate = null )
		// function copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation = null, $callbackParams = null, $perFileCheck = true, $checkUserRights = true, $onFinishOperation = null, $suppressNotifications = false )
		// function deleteFolder( $ID, $U_ID, $kernelStrings, $admin, $deleteCallback = null, $callbackParams = null, $suppressNotifications = false, $changeStatusOnly = false )
		// function expandPathToFolder( $ID, $U_ID, $kernelStrings )
		// function filteravailableFolders( $folderList )
		// function folderDocumentCount( $ID, $U_ID, $kernelStrings )
		// function folderIsShared( $ID, $U_ID, $kernelStrings )
		// function genDocumentID( $kernelStrings )
		// function genFolderID( $ID_PARENT, $kernelStrings )
		// function getDocumentInfo( $ID, $kernelStrings )
		// function getChildrenNum( $ID, $kernelStrings )
		// function getFolderInfo( $ID, $kernelStrings )
		// function getFolderParentInfo( $ID, $kernelStrings, $allowAvailableFolderDummy = false )
		// function getFolderStatus( $ID, $kernelStrings )
		// function getPathToFolder( $ID, $kernelStrings )
		// function getSummaryStatistics( $U_ID, &$folderNum, &$documentNum, $kernelStrings )
		// function getUserDefaultFolder( $U_ID, $kernelStrings, $useCookies = false  )
		// function getIdentityFolderRights( $ID_ID, $ID, $kernelStrings, $IDT_Type = IDT_USER, $groupSummary = true, $groups = null, $forceGroupAccessMode = false, $accessType = ACCESS_SUMMARY )
		// function isChildOf( $child, $parent, $kernelStrings )
		// function isRootIdentity( $ID_ID, $kernelStrings, $ID_Type = IDT_USER, $groupSummary = true, $groups = null, $forceGroupAccessMode = false, $accessType = ACCESS_SUMMARY )
		// function listCollapsedFolders( $U_ID )
		// function listFolderDocuments( $ID, $U_ID, $sortStr, $kernelStrings, $entryProcessor = null, $ignoreUsers = false, $limitStart = null, $limitCount = null )
		// function listFolders( $U_ID, $ID_PARENT, $kernelStrings, $offset = 0, $includeRecycled = true, &$accessFolders, &$hierarchy, &$deletable, $minimalRights = null, $suppress_ID = null, $suppressIDChildren = false, $suppressParent = null, $addavailableFolders = false, $accessType = null, $countDocuments, $folderStatus )
		// function listFolderUsers( $ID, $kernelStrings, $LFU_TYPE = LFU_UNIFORMLIST, $U_ID = null, $limitList = null )
		// function listFolderUsersRights( $ID, $kernelStrings )
		// function moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null,  $onFolderDelete = null, $callbackParams = null, $checkUserRights = true, $accessInheritance = ACCESSINHERITANCE_COPY, $mostTopRightsSource = null )
		// function propagateFolderRights( $ID_PARENT, $ID, $kernelStrings )
		// function recursiveCheckFolderRights( $ID, $U_ID, $rights, $kernelStrings )
		// function recycledFolder( $kernelStrings, $U_ID )
		// function resetUserAccessRights( $kernelStrings, $U_ID )
		// function setFolderCollapseValue( $U_ID, $ID, $value, $kernelStrings )
		// function setFolderRights( $ID, $rightList, $groupRightList, $kernelStrings, $completeRights = false )
		// function setFolderStatus( $ID, $status, $kernelStrings )
		// function setUserDefaultFolder( $U_ID, $ID, $kernelStrings, $useCookies = false  )
		// function setIdentityRights( $ID_ID, $IDT_Type, $rightList, $kernelStrings, $completeRights = false )
		//

		var $folderDescriptor;
		var $accessDescriptor;
		var $documentDescriptor;

		var $checkRights = true;

		var $globalPrefix;

		function applySQLObjectNames( $query )
		//
		// Replaces SQL pseudo-names with real object names
		//
		//		Parameters:
		//			$query - query string
		//
		//		Returns string
		//
		{
			if (is_object($this->folderDescriptor)) {
				$query = str_replace( 'ORDER_BY_STR', $this->folderDescriptor->folder_order_by_str, $query);
				$query = str_replace( 'TREE_FOLDER_TABLE', $this->folderDescriptor->tableName, $query );
			}
			if (is_object($this->documentDescriptor)) {
				$query = str_replace( 'TREE_DOCUMENT_TABLE', $this->documentDescriptor->tableName, $query );
			}
			if (is_object($this->folderDescriptor)) {
				$query = str_replace( 'FOLDER_ID_FIELD', $this->folderDescriptor->folder_id_field, $query );
				$query = str_replace( 'FOLDER_NAME_FIELD', $this->folderDescriptor->folder_name_field, $query );
				$query = str_replace( 'FOLDER_PARENT_FIELD', $this->folderDescriptor->folder_parent_field, $query );
				$query = str_replace( 'FOLDER_STATUS_FIELD', $this->folderDescriptor->folder_status_field, $query );
			}
			if (is_object($this->documentDescriptor)) {
				$query = str_replace( 'DOCUMENT_ID_FIELD', $this->documentDescriptor->document_id_field, $query );
				$query = str_replace( 'DOCUMENT_STATUS_FIELD', $this->documentDescriptor->document_status_field, $query );
				$query = str_replace( 'DOCUMENT_MODIFIED_UID_FIELD', $this->documentDescriptor->document_modifyuid_field, $query );
			}
			if (is_object($this->folderDescriptor))
				$query = str_replace( 'RIGHTS_PATH', $this->folderDescriptor->folder_rights_path, $query );
			
//			if (empty($query)) $query = null;
			
			$query = str_replace( 'USER_ID_FIELD', "U_ID", $query );
			$query = str_replace( 'GROUP_ID_FIELD', "UG_ID", $query );

			return $query;
		}

		function recycledFolder( $kernelStrings, $U_ID )
		//
		// Returns object representing the recycled folder
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//			$U_ID - user identifier
		//
		//		Returns object
		//
		{
			$_access_right_field = $this->accessDescriptor->access_right_field;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_folder_parent_field = $this->folderDescriptor->folder_parent_field;
			$_folder_name_field = $this->folderDescriptor->folder_name_field;

			$recycled = array();

			$recycled[$_folder_id_field] = TREE_RECYCLED_FOLDER;
			$recycled[$_folder_parent_field] = TREE_ROOT_FOLDER;
			$recycled[$_folder_name_field] = $kernelStrings['app_treerecycled_name'];
			$recycled[TREE_ACCESS_RIGHTS] = TREE_WRITEREAD;
			$recycled['NAME'] = $recycled[$_folder_name_field];
			$recycled['TYPE'] = TREE_RECYCLED_FOLDER;
			$recycled['SUBFOLDERSNUM'] = 0;
			$recycled['DOCCOUNT'] = $this->folderDocumentCount( TREE_RECYCLED_FOLDER, $U_ID, $kernelStrings );
			$recycled['OFFSET'] = 0;
			$recycled['ALLOW_DELETE'] = 0;
			$recycled['OFFSET_STR'] = '';
			$recycled['ALLOW_MOVE'] = false;

			return (object)$recycled;
		}

		function folderDocumentCount( $ID, $U_ID, $kernelStrings )
		//
		// Returns document count in folder
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_tree_selectFolderDocCount;
			global $qr_tree_selectRecycledDocCount;

			$_document_status_field = $this->documentDescriptor->document_status_field;
			$_document_modifyuid_field = $this->documentDescriptor->document_modifyuid_field;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;

			$tree_selectFolderDocCount = $this->applySQLObjectNames( $qr_tree_selectFolderDocCount );
			$tree_selectRecycledDocCount = $this->applySQLObjectNames( $qr_tree_selectRecycledDocCount );

			if ( $ID != TREE_RECYCLED_FOLDER ) {
				$params = array( $_document_status_field => TREE_DLSTATUS_NORMAL, $_folder_id_field=>$ID );
				$result = db_query_result( $tree_selectFolderDocCount, DB_FIRST, $params );

				if ( PEAR::isError($result) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			} else {
				$params = array( $_document_status_field => TREE_DLSTATUS_DELETED, 'U_ID'=>$U_ID, $_document_modifyuid_field=>$U_ID );

				$result = db_query_result( $tree_selectRecycledDocCount, DB_FIRST, $params );
				if ( PEAR::isError($result) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			return $result;
		}

		function listFolders( $U_ID, $ID_PARENT, $kernelStrings, $offset = 0, $includeRecycled = true, &$accessFolders,
								&$hierarchy, &$deletable, $minimalRights = null, $suppress_ID = null, $suppressIDChildren = false,
								$suppressParent = null, $addavailableFolders = false, $accessType = null, $countDocuments = false,
								$folderStatus = TREE_FSTATUS_NORMAL )
		//
		// Returns list of folders in context of some parent folder
		//
		//		Parameters:
		//			$U_ID - user identifier. Can be null.
		//			$ID_PARENT - parent folder
		//			$kernelStrings - Kernel localization strings
		//			$offset - current folder offset (used internally for recursion)
		//			$includeRecycled - add recycled metafolder in result
		//			$accessFolders - internal variable. Must be be 0.
		//			$hierarchy - variable to put folder hierarchy array(ID1=>array(ID2=>array(),...),..)
		//			$deletable - true if branch may be deleted
		//			$minimalRights - return only folders with rights not less than this value
		//			$suppress_ID - folder with this identifier will be hidden if it possible or its rights will be set to TREE_NOACCESS
		//			$suppressIDChildren - hide all children of $suppress_ID folder
		//			$suppressParent - hide folder with this indentifier if it has no children
		//			$addavailableFolders - add available Folders metafolder
		//			$accessType - user access type (group, individual) flag. For internal use. Must be null.
		//			$countDocuments - count documents in folders
		//			$folderStatus - folder status
		//
		//		Returns array of objects or PEAR_Error
		//
		{
			global $qr_tree_selectAllParentFolders;
			global $qr_tree_selectAllUserSummaryFolders;
			global $qr_tree_selectAllUserSummaryFoldersWithLinked;
			global $qr_tree_countFoldersDocuments;
			global $UR_Manager;

			if ( $this->checkRights )
			{
				if ( !is_null($U_ID) )
					$globalAdmin = $UR_Manager->IsGlobalAdministrator( $U_ID );
				else
					$globalAdmin = true;

				$this->checkRights = !$globalAdmin;
			}

				$_folder_parent_field = $this->folderDescriptor->folder_parent_field;
				$_access_right_field = (is_object($this->accessDescriptor))?$this->accessDescriptor->access_right_field:null; //Trying to get property of non-object
				$_folder_id_field = $this->folderDescriptor->folder_id_field;
				$_folder_name_field = $this->folderDescriptor->folder_name_field;
				
			$tree_selectParentFolders = $this->applySQLObjectNames( $qr_tree_selectAllParentFolders );
			$tree_countFoldersDocuments = $this->applySQLObjectNames( $qr_tree_countFoldersDocuments );
			$tree_selectUserSummaryFolders = $this->applySQLObjectNames( $qr_tree_selectAllUserSummaryFoldersWithLinked );
			
			$result = array();
			$deletable = true;

			$userMode = strlen($U_ID);

			if ( $userMode )
			{
				if ( $this->checkRights )
					$sql = $tree_selectUserSummaryFolders;
				else
					$sql = $tree_selectParentFolders;
			} else
				$sql = $tree_selectParentFolders;

			$add_params = array();
			if ( method_exists( $this, "getListParentFoldersSQL" ) )
			{
				$sql = $this->getListParentFoldersSQL( $add_params );
				$fl = true;
			}
			
			// List this level folders
			//
			$params = array();
			$params[$this->folderDescriptor->folder_parent_field] = $ID_PARENT;
			$params["U_ID"] = $U_ID;
			$params[$this->folderDescriptor->folder_status_field] = $folderStatus;

			$params = array_merge( $params, $add_params );

			if ( $countDocuments )
			{
				if ( method_exists( $this, "getCountFoldersDocumentsSQL" ) )
					$tree_countFoldersDocuments = $this->getCountFoldersDocumentsSQL( $add_params );

				$params = array_merge( $params, $add_params );

				$qr = @db_query( $tree_countFoldersDocuments, $params );
				if ( PEAR::isError($qr) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$doccountArray = array();

				while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) )
					$doccountArray[$row->$_folder_id_field] = $row->DOCCOUNT;

				db_free_result($qr);
			}

			$qr = @db_query( $sql, $params );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$foldersArray = array();

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) )
			{
				if ( $countDocuments && isset( $doccountArray[$row->$_folder_id_field] ) )
					$row->DOCCOUNT = $doccountArray[$row->$_folder_id_field];
				
				$foldersArray[$row->$_folder_parent_field][] = $row;
			}

			db_free_result($qr);
			$res = $this->makeFolderEntry( $foldersArray, $U_ID, $ID_PARENT,
											$kernelStrings, $offset, $includeRecycled, $accessFolders,
											$hierarchy, $deletable, $minimalRights, $suppress_ID,
											$suppressIDChildren, $suppressParent, $addavailableFolders,
											$countDocuments, $folderStatus );

			return $res;

		}


		function makeFolderEntry( $rows, $U_ID, $ID_PARENT, $kernelStrings, $offset, $includeRecycled, &$accessFolders,
								&$hierarchy, &$deletable, $minimalRights, $suppress_ID, $suppressIDChildren,
								$suppressParent, $addavailableFolders, $countDocuments,
								$folderStatus )
		{
			$_folder_parent_field = $this->folderDescriptor->folder_parent_field;
			$_access_right_field = TREE_ACCESS_RIGHTS;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_folder_name_field = $this->folderDescriptor->folder_name_field;

			$result = array();
			$deletable = true;

			$nextOffset = $offset + 1;

			if ( isset( $rows[$ID_PARENT] ) && is_array( $rows[$ID_PARENT] ) )
			{
				foreach( $rows[$ID_PARENT] as $pKey=>$row )
				{

					$row->NAME = $row->$_folder_name_field;
					$row->OFFSET = $offset;
					$row->OFFSET_STR = str_repeat( " ", $offset );
					$row->TYPE = TREE_REGULAR_FOLDER;
					$row->ID = $row->$_folder_id_field;
					$row->ENC_ID = base64_encode($row->ID);

					$userMode = strlen($U_ID);

					$row->$_folder_parent_field = $ID_PARENT;

					if ( $this->checkRights )
						$row->$_access_right_field = isset( $row->USER_RIGHTS ) ? $row->USER_RIGHTS : TREE_NOACCESS;
					else
						$row->$_access_right_field = TREE_READWRITEFOLDER;

					$includeThisFolder = false;

					if ( $userMode && $this->checkRights )
					{
						if ( is_null($minimalRights) )
							$includeThisFolder = strlen($row->$_access_right_field) && $row->$_access_right_field != TREE_NOACCESS;
						else
							$includeThisFolder = strlen($row->$_access_right_field) &&  UR_RightsObject::CheckMask( $row->$_access_right_field, $minimalRights );

						$accessFolders = $accessFolders || $includeThisFolder;

						if ( !is_null($suppress_ID) )
							if ( $suppress_ID == $row->$_folder_id_field )
								$includeThisFolder = false;

						if ( !strlen($row->$_access_right_field) ||  $suppress_ID == $row->$_folder_id_field || !$accessFolders )
							$row->$_access_right_field = TREE_NOACCESS;
					}
					else
					if ( !$this->checkRights )
					{
						$includeThisFolder = true;
						$accessFolders = true;
						$hideBranch = false;
					}

					if ( $this->checkRights )
						$hideBranch = !is_null($suppress_ID) && $suppress_ID == $row->$_folder_id_field && $suppressIDChildren;

					if ( !$hideBranch )
					{
						// List child folders
						//
						$childAccess = false;
						$childHierarchy = null;
						$childDeletable = true;
						$nextLevelFolders = $this->makeFolderEntry( $rows, $U_ID, $row->$_folder_id_field,
																$kernelStrings, $nextOffset, $includeRecycled,
																$childAccess, $childHierarchy, $childDeletable,
																$minimalRights, $suppress_ID, $suppressIDChildren, $suppressParent,
																$addavailableFolders, $countDocuments,
																$folderStatus );

						if ( isset( $rows[$row->$_folder_id_field] ) )
							$row->SUBFOLDERSNUM = count( $rows[$row->$_folder_id_field] );

						if ( $userMode && $this->checkRights )
							$rowDeletable = $childDeletable && UR_RightsObject::CheckMask( $row->$_access_right_field, TREE_READWRITEFOLDER);
						else
							$rowDeletable = $childDeletable;

						$row->ALLOW_DELETE = $rowDeletable;
						$row->ALLOW_MOVE = $row->ALLOW_DELETE;
						$deletable = $deletable && $rowDeletable;

						if ( isset($row->$_access_right_field) )
							$row->RIGHT = $row->$_access_right_field;

						if ( !($userMode && !$accessFolders && !count($nextLevelFolders)) || ( !$this->checkRights && count($nextLevelFolders) ) )
						{
							if ( $childAccess )
								$accessFolders = true;

							$suppressThisParent = !is_null($suppressParent) && $row->$_folder_id_field == $suppressParent && !count($nextLevelFolders);
							$includeThisFolder = $includeThisFolder || ($accessFolders && count($nextLevelFolders));

							if ( !$userMode || ( $userMode && $includeThisFolder && !$suppressThisParent ) || !$this->checkRights )
							{
								$result[$row->$_folder_id_field] = $row;
								$hierarchy[$row->$_folder_id_field] = array();

								$result = array_merge( $result, $nextLevelFolders );
								$hierarchy[$row->$_folder_id_field] = $childHierarchy;
							}
						}
					}
				}
			}

			// Add available foldres to top-level folder
			//
			if ( $ID_PARENT == TREE_ROOT_FOLDER && $addavailableFolders ) {
				$availableFolders = array();

				$availableFolders[$_folder_id_field] = TREE_AVAILABLE_FOLDERS;
				$availableFolders[$_folder_parent_field] = TREE_ROOT_FOLDER;
				$availableFolders[$_folder_name_field] = $kernelStrings['app_treeavailfolders_name'];

				if ( $suppress_ID != TREE_ROOT_FOLDER && $suppressParent != TREE_ROOT_FOLDER )
					$availableFolders['RIGHT'] = ( $this->isRootIdentity( $U_ID, $kernelStrings ) ) ? TREE_READWRITEFOLDER : TREE_WRITEREAD;
				else
					$availableFolders['RIGHT'] = TREE_NOACCESS;

				$availableFolders[$_access_right_field] = $availableFolders['RIGHT'];

				$availableFolders['NAME'] = $availableFolders[$_folder_name_field];
				$availableFolders['TYPE'] = TREE_AVAILABLE_FOLDERS;
				$availableFolders['SUBFOLDERSNUM'] = 0;
				$availableFolders['DOCCOUNT'] = 0;
				$availableFolders['OFFSET'] = 0;
				$availableFolders['ALLOW_DELETE'] = 0;
				$availableFolders['OFFSET_STR'] = '';
				$availableFolders['ALLOW_MOVE'] = false;

				foreach( $result as $id=>$data ) {
					$data->OFFSET = $data->OFFSET + 1;
					$data->OFFSET_STR = str_repeat( " ", $data->OFFSET );
					$result[$id] = $data;
				}

				if ( ($suppress_ID != TREE_ROOT_FOLDER && $suppressParent != TREE_ROOT_FOLDER) || count($result) ) {
					$result = array_merge( array(TREE_AVAILABLE_FOLDERS=>(object)$availableFolders), $result );
					$hierarchy = array( TREE_AVAILABLE_FOLDERS=>$hierarchy );
				}
			}

			// Add recycled folder to top-level folder
			//
			if ( $ID_PARENT == TREE_ROOT_FOLDER && $includeRecycled ) {
				$result[TREE_RECYCLED_FOLDER] = $this->recycledFolder( $kernelStrings, $U_ID );
				$hierarchy[TREE_RECYCLED_FOLDER] = array();
			}

			return $result;
		}

		function listFoldersPlain( $folderStatus, $sortStr, $kernelStrings )
		//
		// Returns plain sorted folder list
		//
		//		Parameters:
		//			$folderStatus - folder status
		//			$sortStr - sorting string
		//			$kernelStrings - Kernel localization string
		//
		//		Returns array of objects
		//
		{
			global $qr_tree_selectfoldersplain;

			$tree_selectfoldersplain = $this->applySQLObjectNames( $qr_tree_selectfoldersplain );

			$_folder_status_field = $this->folderDescriptor->folder_status_field;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;

			$params = array( $_folder_status_field=>$folderStatus );

			$sql = sprintf( $tree_selectfoldersplain, $sortStr );

			$qr = db_query( $sql, $params );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();
			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				$result[$row->$_folder_id_field] = $row;
			}

			db_free_result($qr);

			return $result;
		}

		function filteravailableFolders( $folderList )
		//
		// Returns list of folders with right value != TREE_NOACCESS
		//
		//		Parameters:
		//			$folderList - list of folders, result of listFolders method
		//
		//		Returns array
		//
		{
			$result = array();

			$_access_right_field = $this->accessDescriptor->access_right_field;

			foreach( $folderList as $ID=>$folder )
				if ( $folder->TREE_ACCESS_RIGHTS != TREE_NOACCESS )
					$result[$ID] = $folder;

			return $result;
		}

		function listFolderUsers( $ID, $kernelStrings, $LFU_TYPE = LFU_UNIFORMLIST, $U_ID = null,
									$limitUserList = null, $limitGroupList = null )
		//
		// Returns list of users which has access to specified folder
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$kernelStrings - Kernel localization string
		//			$LFU_TYPE - list type - uniform list (individual users + groups users), or individual users and groups separately
		//			$U_ID - user identifier
		//			$limitUserList - limit user number with this value
		//			$limitGroupList - limit group number with this value
		//
		//		Returns array( U_ID=>array( USER_NAME=>username, RIGHTS=>userrights ) ) or PEAR_Error
		//
		{
			global $qr_tree_selectFolderUsers;
			global $qr_tree_selectFolderGroupUsers;
			global $qr_tree_selectFolderGroups;

			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_access_right_field = $this->accessDescriptor->access_right_field;

			$tree_selectFolderUsers = $this->applySQLObjectNames( $qr_tree_selectFolderUsers );
			$tree_selectFolderGroupUsers = $this->applySQLObjectNames( $qr_tree_selectFolderGroupUsers );
			$tree_selectFolderGroups = $this->applySQLObjectNames( $qr_tree_selectFolderGroups );

			// Load individual user list
			//
			$users = array();
			$usersLimited = false;

			$qr = db_query( $tree_selectFolderUsers, array( $_folder_id_field=>$ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$count = 0;
			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				// Skip "My Permissions" row for Groups And Users mode
				//
				if ( $LFU_TYPE != LFU_UNIFORMLIST && $row->U_ID == $U_ID )
					continue;

				$count ++;
				if ( !(!is_null($limitUserList) && $count > $limitUserList) && $row->AR_VALUE != UR_NO_RIGHTS )
				{
					$users[$row->AR_ID]['RIGHTS'] = $row->AR_VALUE;
					$users[$row->AR_ID]['USER_NAME'] = getArrUserName((array)$row, true);
				} else
					$usersLimited = true;
			}


			$usersTotal = $count;

			db_free_result($qr);

			if ( $LFU_TYPE == LFU_UNIFORMLIST )
			{
				// Load group user list
				//
				$qr = db_query( $tree_selectFolderGroupUsers, array( $_folder_id_field=>$ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
				if ( PEAR::isError($qr) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) )
				{
					if (  $row->GROUPRIGHTS != UR_NO_RIGHTS )
					{
						if ( !isset($users[$row->U_ID]) )
						{
							$users[$row->U_ID]['RIGHTS'] = $row->GROUPRIGHTS;
							$users[$row->U_ID]['USER_NAME'] = getArrUserName((array)$row, true);
						}
						else
						{
							$users[$row->U_ID]['RIGHTS'] |= $row->GROUPRIGHTS;
						}
					}
				}

				db_free_result($qr);
			}
			else
			{
				// Load groups
				//
				$groups = array();
				$groupsLimited = false;
				$count = 0;

				$qr = db_query( $tree_selectFolderGroups, array( $_folder_id_field=>$ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
				if ( PEAR::isError($qr) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) )
				{
					$count ++;
					if ( !(!is_null($limitGroupList) && $count > $limitGroupList)  && $row->AR_VALUE != UR_NO_RIGHTS  )
					{
						$groups[$row->UG_ID]['RIGHTS'] = $row->AR_VALUE;
						$groups[$row->UG_ID]['GROUP_NAME'] = $row->UG_NAME;
					}
					else
						$groupsLimited = true;
				}

				$groupsTotal = $count;
			}

			// Construct result
			//
			if ( $LFU_TYPE == LFU_UNIFORMLIST ) {
				uasort( $users, "sortFolderUserList" );
				$result = $users;
			} else {
				$result[LFU_USERS] = $users;
				$result[LFU_GROUPS] = $groups;

				if ( !is_null($U_ID) ) {
					$myRights = $this->getIdentityFolderRights( $U_ID, $ID, $kernelStrings );
					$result[LFU_MYPERMISSIONS] = $myRights;
				}

				$result[LFU_USERSTOTAL] = $usersTotal;
				$result[LFU_GROUPSTOTAL] = $groupsTotal;
				$result[LFU_USERSLIMITED] = $usersLimited;
				$result[LFU_GROUPSLIMITED] = $groupsLimited;
			}

			return $result;
		}

		function listFolderUsersRights( $ID, $kernelStrings )
		// 
		// Returns list of all system users and their access to specified folder
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$kernelStrings - Kernel localization string
		//
		//		Returns array( U_ID=>array( USER_NAME=>username, RIGHTS=>userrights ) ) or PEAR_Error
		//
		{
			global $qr_tree_selectSystemUsersFolderAccess;
			global $qr_tree_selectAccessrightsLink;

			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_user_id_field = $this->accessDescriptor->user_id_field;
			$_access_right_field = $this->accessDescriptor->access_right_field;
			
			// Find access rights link
			$linkRow = db_query_result( $qr_tree_selectAccessrightsLink, DB_ARRAY, array( "AR_OBJECT_ID"=>$ID, "AR_PATH"=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($linkRow) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			
			$selectSystemUsersFolderAccess = $this->applySQLObjectNames( $qr_tree_selectSystemUsersFolderAccess );
			
			if ($linkRow) {
				$searchId = $linkRow["LINK_AR_OBJECT_ID"];	
				$searchPath = $linkRow["LINK_AR_PATH"];
			} else {
				$searchId = $ID;
				$searchPath = $this->folderDescriptor->folder_rights_path;
			}
			
			$qr = db_query( $selectSystemUsersFolderAccess, array( $_folder_id_field=>$searchId, UR_PATH=>$searchPath ) );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				$right = ( strlen($row->AR_VALUE) ) ? $row->AR_VALUE : TREE_NOACCESS;

				$result[$row->U_ID]['RIGHTS'] = $right;
				$result[$row->U_ID]['USER_NAME'] = getArrUserName((array)$row, true);
			}

			db_free_result($qr);

			return $result;
		}

		function listFolderGroupsRights( $ID, $kernelStrings )
		//
		// Returns list of all system groups and their access to specified folder
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$kernelStrings - Kernel localization string
		//
		//		Returns array( UG_ID=>array( GROUP_NAME=>groupname, RIGHTS=>grouprights ) ) or PEAR_Error
		//
		{
			global $qr_tree_selectSystemUserGroupsFolderAccess;
			global $qr_tree_selectAccessrightsLink;

			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_user_id_field = $this->accessDescriptor->user_id_field;
			$_access_right_field = $this->accessDescriptor->access_right_field;
			
			// Find access rights link
			$linkRow = db_query_result( $qr_tree_selectAccessrightsLink, DB_ARRAY, array( "AR_OBJECT_ID"=>$ID, "AR_PATH"=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($linkRow) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );			

			$tree_selectSystemUserGroupsFolderAccess = $this->applySQLObjectNames( $qr_tree_selectSystemUserGroupsFolderAccess );
			
			if ($linkRow) {
				$searchId = $linkRow["LINK_AR_OBJECT_ID"];	
				$searchPath = $linkRow["LINK_AR_PATH"];
			} else {
				$searchId = $ID;
				$searchPath = $this->folderDescriptor->folder_rights_path;
			}

			$qr = db_query( $tree_selectSystemUserGroupsFolderAccess, array( $_folder_id_field=>$searchId, UR_PATH=>$searchPath ) );

			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();

			$result = array();

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				$right = ( strlen($row->AR_VALUE) ) ? $row->AR_VALUE : TREE_NOACCESS;

				$result[$row->UG_ID]['RIGHTS'] = $right;
				$result[$row->UG_ID]['GROUP_NAME'] = $row->UG_NAME;
				$result[$row->UG_ID]['COUNT'] = $row->USERCOUNT;
			}

			db_free_result($qr);

			return $result;
		}

		function getPathToFolder( $ID, $kernelStrings )
		//
		// Returns path to folder as array
		//
		//		Parameters:
		//			$ID - folder indentifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns array( ID1=>"root", ID2=>"subfolder 1", ID3=>"subfolder 2" ) or PEAR_Error
		//
		{
			global $qr_tree_selectFolder;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_folder_name_field = $this->folderDescriptor->folder_name_field;
			$_folder_parent_field = $this->folderDescriptor->folder_parent_field;

			if ( $ID == TREE_ROOT_FOLDER || $ID == TREE_RECYCLED_FOLDER )
				return array();

			$tree_selectFolder = $this->applySQLObjectNames( $qr_tree_selectFolder );

			$folderData = db_query_result( $tree_selectFolder, DB_ARRAY, array( $_folder_id_field=>$ID ) );
			if ( PEAR::isError($folderData) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$folderData = (object)$folderData;

			$result = array( $folderData->$_folder_id_field=>$folderData->$_folder_name_field );

			if ( $folderData->$_folder_parent_field != TREE_ROOT_FOLDER ) {
				if (!is_null($folderData->$_folder_parent_field)) {
				    $parentName = $this->getPathToFolder( $folderData->$_folder_parent_field, $kernelStrings );
				    if ( PEAR::isError($parentName) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				    $result = array_merge( $parentName, $result );
				}
			}

			return $result;
		}

		function getFolderInfo( $ID, $kernelStrings, $allowAvailableFolderDummy = false )
		//
		// Returns folder information - name, parent
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$kernelStrings - Kernel localization strings
		//			$allowAvaliableFolderDummy - return "Available folders" folder in case if ROOT passed as $ID
		//
		//		Returns array
		//
		{
			global $qr_tree_selectFolder;
			global $qr_tree_selectFolderCount;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_folder_name_field = $this->folderDescriptor->folder_name_field;

			if ( $ID == TREE_ROOT_FOLDER )
				if ( $allowAvailableFolderDummy ) {
					$result = array();
					$result[$_folder_id_field] = TREE_ROOT_FOLDER;
					$result[$_folder_name_field] = $kernelStrings['app_treeavailfolders_name'];
					return $result;
				} else
					return PEAR::raiseError( $kernelStrings['app_treefoldernotfound_message'], ERRCODE_APPLICATION_ERR );

			$tree_selectFolder = $this->applySQLObjectNames( $qr_tree_selectFolder );

			$params = array($_folder_id_field=>$ID);

			$add_params = array();
			if ( method_exists( $this, "getSelectFolderSQL" ) )
				$tree_selectFolder = $this->getSelectFolderSQL( $add_params );

			$params = array_merge( $params, $add_params );

			$tempval = array();
			$res = exec_sql( $tree_selectFolder, $params, $tempval, false );

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING]."".mysql_error() );

			if ( 0 == db_result_num_rows( $res ) )
				return PEAR::raiseError( $kernelStrings['app_treefoldernotfound_message'], ERRCODE_APPLICATION_ERR );

			return db_fetch_array( $res );
		}

		function getFolderParentInfo( $ID, $kernelStrings, $allowAvailableFolderDummy = false )
		//
		// Returns folder parent information - name, parent
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$kernelStrings - Kernel localization strings
		//			$allowAvaliableFolderDummy - return "Available folders" folder in case if folder parent is ROOT
		//
		//		Returns array or PEAR_Error
		//
		{
			$folderData = $this->getFolderInfo( $ID, $kernelStrings, $allowAvailableFolderDummy );
			if ( PEAR::isError($folderData) )
				return $folderData;

			return $this->getFolderInfo( $folderData[$this->folderDescriptor->folder_parent_field], $kernelStrings, $allowAvailableFolderDummy );
		}

		function addmodFolder( $action, $U_ID, $ID_PARENT, $folderdata, $kernelStrings, $admin, $createCallback = null, $callbackParams = null, $propagateFolderRights = true, $suppressNotifications = false, $folderStatus = null, $checkFolderName = true, $onBeforeFolderCreate = null )
		//
		// Adds/modifies folder
		//
		//		Parameters:
		//			$action - form mode - new/edit
		//			$U_ID - user identidier
		//			$ID_PARENT - parent folder indentifier
		//			$folderData - array with folder information
		//			$kernelStrings - Kernel localization strings
		//			$admin - true if user is administrator
		//			$createCallback - callback function to execute after folder creation
		//			$callbackParams - parameters array to pass to create callback
		//			$propagateFolderRights - copy folder rights from parent folder
		//			$suppressNotifications - don't send any notifications
		//			$folderStatus - set this status for new folder
		//			$onBeforeFolderCreate - callback function to execute before folder creation
		//
		//		Returns folder identifier or PEAR_Error
		//
		{
			global $qr_tree_insertFolder;
			global $qr_tree_updateFolderName;

			$_folder_name_field = $this->folderDescriptor->folder_name_field;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_folder_parent_field = $this->folderDescriptor->folder_parent_field;
			$_folder_status_field = $this->folderDescriptor->folder_status_field;

			$tree_insertFolder = $this->applySQLObjectNames( $qr_tree_insertFolder );
			$tree_updateFolderName = $this->applySQLObjectNames( $qr_tree_updateFolderName );

			$folderdata = trimArrayData( $folderdata );

			if ( $checkFolderName && ereg( "\\/|\\\|\\?|:|<|>|\\*", $folderdata[$_folder_name_field] ) )
				return PEAR::raiseError( $kernelStrings['app_treeinvfoldername_message'], ERRCODE_APPLICATION_ERR );

			$requiredFields = array( $_folder_name_field );

			if ( PEAR::isError( $invalidField = findEmptyField( $folderdata, $requiredFields ) ) ) {
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

				return $invalidField;
			}

			$invalidField = checkStringLengths($folderdata, array($_folder_name_field), array(255));
			if ( PEAR::isError($invalidField) ) {
				$invalidField->message = $kernelStrings['app_treeinvfolderlenname_message'];

				return $invalidField;
			}

			if ( !strlen($ID_PARENT) )
				$ID_PARENT = TREE_ROOT_FOLDER;

			if ( $action == ACTION_NEW )
			{
				if ( !$admin && $this->checkRights ) {
					if ( $ID_PARENT != TREE_ROOT_FOLDER ) {
						$rights = $this->getIdentityFolderRights( $U_ID, $ID_PARENT, $kernelStrings );
						if ( PEAR::isError($rights) )
							return $rights;

						if ( !UR_RightsObject::CheckMask( $rights, TREE_ONLYFOLDER ) )
							return PEAR::raiseError( $kernelStrings['app_treenofldrights_message'], ERRCODE_APPLICATION_ERR );
					} else {
						$isRootIdentity = $this->isRootIdentity( $U_ID, $kernelStrings );
						if ( PEAR::isError($isRootIdentity) )
							return $isRootIdentity;

						if ( !$isRootIdentity )
							return PEAR::raiseError( $kernelStrings['app_treenorootrights_message'], ERRCODE_APPLICATION_ERR );
					}
				}
				
				if ( !is_null($onBeforeFolderCreate) )
				{
					$callbackParams['ID_PARENT'] = $ID_PARENT;
					$callbackParams['U_ID'] = $U_ID;
					$callbackParams['suppressNotifications'] = $suppressNotifications;
					$callbackParams['kernelStrings'] = $kernelStrings;

					$res = call_user_func( $onBeforeFolderCreate, $callbackParams );
					if ( PEAR::isError($res) )
						return $res;
				}

				$ID = $this->genFolderID( $ID_PARENT, $kernelStrings );
				if ( PEAR::isError($ID) )
					return $ID;

				$folderdata[$_folder_id_field] = $ID;
				$folderdata[$_folder_parent_field] = $ID_PARENT;

				if ( is_null($folderStatus) )
					$folderdata[$_folder_status_field] = TREE_FSTATUS_NORMAL;
				else {
					$folderdata[$_folder_status_field] = $folderStatus;
				}
				
				$qr = db_query( $tree_insertFolder, $folderdata );
				if ( PEAR::isError($qr) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = null;
				if ( $ID_PARENT != TREE_ROOT_FOLDER ) {
					if ( $propagateFolderRights )
						$res = $this->propagateFolderRights( $ID_PARENT, $ID, $kernelStrings );
				} else
					$res = $this->setIdentityRights( $U_ID, IDT_USER, array( $ID=>TREE_READWRITEFOLDER ), $kernelStrings );

				if ( PEAR::isError($res) )
					return $res;

				if ( !is_null($createCallback) )
				{
					$callbackParams['ID_PARENT'] = $ID_PARENT;
					$callbackParams['U_ID'] = $U_ID;
					$callbackParams['suppressNotifications'] = $suppressNotifications;

					$res = call_user_func( $createCallback, $ID, $callbackParams );
					if ( PEAR::isError($res) )
						return $res;
				}
			}
			else
			{
				$ID = $folderdata[$_folder_id_field];

				if ( !$admin ) {
					$rights = $this->getIdentityFolderRights( $U_ID, $ID, $kernelStrings );

					if ( PEAR::isError($rights) )
						return $rights;

					if ( !UR_RightsObject::CheckMask( $rights, TREE_ONLYFOLDER ) )
						return PEAR::raiseError( $kernelStrings['app_treenofldmodrights_message'], ERRCODE_APPLICATION_ERR );
				}

				$qr = db_query( $tree_updateFolderName, $folderdata );
				if ( PEAR::isError($qr) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			return $ID;
		}

		function recursiveCheckFolderRights( $ID, $U_ID, $rights, $kernelStrings )
		//
		//	Checks if user has minimal rights to folder and its subfolders
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$U_ID - user identifier
		//			$rights - minimal user rights
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns boolean or PEAR_Error
		//
		{
			$userRights = $this->getIdentityFolderRights( $U_ID, $ID, $kernelStrings );
			if ( PEAR::isError($userRights) )
				return $userRights;

			if ( !UR_RightsObject::CheckMask( $userRights, $rights ) )
				return false;

			global $qr_tree_selectParentFolders;

			$_folder_parent_field = $this->folderDescriptor->folder_parent_field;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_folder_status_field = $this->folderDescriptor->folder_status_field;

			$tree_selectParentFolders = $this->applySQLObjectNames( $qr_tree_selectParentFolders );

			$result = true;

			$qr = db_query( $tree_selectParentFolders, array($_folder_parent_field=>$ID,$_folder_status_field=>TREE_FSTATUS_NORMAL) );
			if ( PEAR::isError($userRights) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) )
			{
				$res = $this->recursiveCheckFolderRights( $row->$_folder_id_field, $U_ID, $rights, $kernelStrings );
				if ( PEAR::isError($res) )
					return $res;

				if ( !$res ) {
					$result = false;
					break;
				}
			}

			db_free_result( $qr );

			return $result;
		}

		function deleteFolder( $ID, $U_ID, $kernelStrings, $admin, $deleteCallback = null, $callbackParams = null,
								$suppressNotifications = false, $changeStatusOnly = false )
		//
		// Deletes folder
		//
		//		Parameters
		//			$ID - folder identifier
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//			$admin - true if user is administrator
		//			$deleteCallback - callback function to execute before folder deletion
		//			$callbackParams - parameters array to pass to create callback
		//			$suppressNotifications - don't send any notifications
		//			$changeStatusOnly - do not delete folder physically, but change folder status to deleted
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_tree_deleteFolder;
			global $qr_tree_deleteFolderRights;
			global $qr_tree_deleteFolderRightsLinks;
			global $qr_tree_deleteFolderDocuments;
			global $qr_tree_selectParentFolders;
			global $qr_tree_deleteAllGroupFolderRights;
			global $qr_tree_update_folderStatus;

			$_folder_parent_field = $this->folderDescriptor->folder_parent_field;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_folder_status_field = $this->folderDescriptor->folder_status_field;

			if ( !$admin ) {
				$rights = $this->recursiveCheckFolderRights( $ID, $U_ID, TREE_READWRITEFOLDER, $kernelStrings );
				if ( PEAR::isError($rights) )
					return $rights;

				if ( !$rights )
					return PEAR::raiseError( $kernelStrings['app_treenoflddelrights_message'], ERRCODE_APPLICATION_ERR );
			}
			
			if ( !is_null($deleteCallback) ) {
				$curFolderData = $this->getFolderInfo( $ID, $kernelStrings );

				$callbackParams['deletedFolderData'] = $curFolderData;
				$callbackParams['kernelStrings'] = $kernelStrings;
				$callbackParams['operation'] = TREE_COPYFOLDER;
				$callbackParams['suppressNotifications'] = $suppressNotifications;
				$callbackParams['U_ID'] = $U_ID;
				$callbackParams['physicallyDelete'] = !$changeStatusOnly;
				$res = call_user_func( $deleteCallback, $ID, $callbackParams );
				if ( PEAR::isError($res) )
					return $res;
			}

			$tree_selectParentFolders = $this->applySQLObjectNames( $qr_tree_selectParentFolders );
			$params = array();
			$params[$_folder_parent_field] = $ID;
			$params[$_folder_status_field] = TREE_FSTATUS_NORMAL;

			$qr = @db_query( $tree_selectParentFolders, $params );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				$res = $this->deleteFolder( $row->$_folder_id_field, $U_ID, $kernelStrings, true, $deleteCallback,
											$callbackParams, true, $changeStatusOnly );
				if ( PEAR::isError($res) )
					return $res;
			}

			db_free_result( $qr );
			
			$tree_deleteFolderRights = $this->applySQLObjectNames( $qr_tree_deleteFolderRights );
			$tree_deleteFolderRightsLinks = $this->applySQLObjectNames( $qr_tree_deleteFolderRightsLinks );
			$tree_deleteAllGroupFolderRights = $this->applySQLObjectNames( $qr_tree_deleteAllGroupFolderRights );

			$res = db_query( $tree_deleteFolderRights, array( $_folder_id_field=>$ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			
			$res = db_query( $tree_deleteFolderRightsLinks, array( $_folder_id_field=>$ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$res = db_query( $tree_deleteAllGroupFolderRights, array( $_folder_id_field=>$ID, UR_PATH=>$this->folderDescriptor->folder_rights_path  ) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( !$changeStatusOnly )
			{
				$tree_deleteFolder = $this->applySQLObjectNames( $qr_tree_deleteFolder );
				$tree_deleteFolderDocuments = $this->applySQLObjectNames( $qr_tree_deleteFolderDocuments );

				$res = db_query( $tree_deleteFolder, array( $_folder_id_field=>$ID ) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( trim( $this->documentDescriptor->tableName ) != "" )
				{
					$res = db_query( $tree_deleteFolderDocuments, array( $_folder_id_field=>$ID ) );

					if ( PEAR::isError($res) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}
			}
			else
			{
				$tree_update_folderStatus = $this->applySQLObjectNames( $qr_tree_update_folderStatus );

				$res = db_query( $tree_update_folderStatus, array( $_folder_id_field=>$ID, $_folder_status_field=>TREE_FSTATUS_DELETED ) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			return null;
		}

		function propagateFolderRights( $ID_PARENT, $ID, $kernelStrings )
		//
		// Copies folder access rights from a parent folder
		//
		//		Parameters:
		//			$ID_PARENT - parent folder identifier
		//			$ID - folder identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			// Propagate users folder rights
			//
			$parentRights = $this->listFolderUsersRights( $ID_PARENT, $kernelStrings );
			if ( PEAR::isError( $parentRights ) )
					return $parentRights;
			
			foreach( $parentRights as $U_ID=>$rightsData ) {
				$rights = $rightsData['RIGHTS'];

				$res = $this->setIdentityRights( $U_ID, IDT_USER, array( $ID=>$rights ), $kernelStrings );
				if ( PEAR::isError( $res ) )
					return $res;
			}

			// Propagate groups folder rights
			//
			$parentRights = $this->listFolderGroupsRights( $ID_PARENT, $kernelStrings );
			if ( PEAR::isError( $parentRights ) )
					return $parentRights;

			foreach( $parentRights as $UG_ID=>$rightsData ) {
				$rights = $rightsData['RIGHTS'];

				$res = $this->setIdentityRights( $UG_ID, IDT_GROUP, array( $ID=>$rights ), $kernelStrings );
				if ( PEAR::isError( $res ) )
					return $res;
			}

			return null;
		}

		function genFolderID( $ID_PARENT, $kernelStrings )
		//
		// Generates new folder ID
		//
		//		Parameters:
		//			$ID_PARENT - parent folder indentifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns new folder identifier or PEAR_Error
		//
		{
			global $qr_tree_selectmax_ID;

			$_folder_parent_field = $this->folderDescriptor->folder_parent_field;

			$tree_selectmax_ID = $this->applySQLObjectNames( $qr_tree_selectmax_ID );

			$ID = db_query_result( $tree_selectmax_ID, DB_FIRST, array( $_folder_parent_field=>$ID_PARENT ) );
			if ( PEAR::isError($ID) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if( strlen($ID) ) {
				$match = null;
				ereg( "([[:digit:]]*).$", $ID, $match );

				$num = $match[1];
				$num = $num + 1;
				$num = $num.".";
				$ID = ereg_replace( "[[:digit:]]+.$", $num, $ID );
			} else {
				$ID = "1.";

				if ( $ID_PARENT != TREE_ROOT_FOLDER )
					$ID = $ID_PARENT.$ID;
			}

			return $ID;
		}

		function setIdentityRights( $ID_ID, $ID_Type, $rightList, $kernelStrings, $completeRights = false )
		//
		// Sets identity rights for folder
		//
		//		Paremeters:
		//			$ID_ID - identity identifier
		//			$ID_Type - identity type (IDT_USER, IDT_GROUP)
		//			$rightList - array of user rights in format array( ID1=>RIGHT1,... )
		//			$completeRights - set this value to true if $rightList contains rights for all folders
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_tree_deleteUserRights;
			global $qr_tree_insertUserRights;
			global $qr_tree_deleteUserFolderRights;

			global $qr_tree_deleteGroupRights;
			global $qr_tree_insertGroupRights;
			global $qr_tree_deleteGroupFolderRights;

			$_user_id_field = 'U_ID';
			$_group_id_field = "UG_ID";
			$_folder_id_field = $this->folderDescriptor->folder_id_field;

			$tree_deleteUserRights = $this->applySQLObjectNames( $qr_tree_deleteUserRights );
			$tree_insertUserRights = $this->applySQLObjectNames( $qr_tree_insertUserRights );
			$tree_deleteUserFolderRights = $this->applySQLObjectNames( $qr_tree_deleteUserFolderRights );

			$tree_deleteGroupRights = $this->applySQLObjectNames( $qr_tree_deleteGroupRights );
			$tree_insertGroupRights = $this->applySQLObjectNames( $qr_tree_insertGroupRights );
			$tree_deleteGroupFolderRights = $this->applySQLObjectNames( $qr_tree_deleteGroupFolderRights );

			if ( $completeRights ) {
				if ( $ID_Type == IDT_USER )
					$res = db_query( $tree_deleteUserRights, array($_user_id_field=>$ID_ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
				else
					$res = db_query( $tree_deleteGroupRights, array($_group_id_field=>$ID_ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );

				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			foreach( $rightList as $ID=>$RIGHT ) {
				$params = array( UR_PATH=>$this->folderDescriptor->folder_rights_path );

				if ( $ID_Type == IDT_USER ) {
					$params[$_user_id_field] = $ID_ID;
					$params[UR_VALUE] = $RIGHT;
				} else {
					$params[$_group_id_field] = $ID_ID;
					$params[UR_VALUE] = $RIGHT;
				}

				$params[$_folder_id_field] = $ID;
				if ( !$completeRights )
				{
					if ( $ID_Type == IDT_USER )
						$res = db_query( $tree_deleteUserFolderRights, $params );
					else
						$res = db_query( $tree_deleteGroupFolderRights, $params );

					if ( PEAR::isError($res) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}

				if ( $RIGHT != TREE_NOACCESS )
				{
					if ( $ID_Type == IDT_USER )
						$res = db_query( $tree_insertUserRights, $params );
					else
						$res = db_query( $tree_insertGroupRights, $params );


					if ( PEAR::isError($res) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}
			}

			return null;
		}

		function setFolderRights( $ID, $rightList, $groupRightList, $kernelStrings, $completeRights = false )
		//
		// Sets users rights for folder
		//
		//		Paremeters:
		//			$ID - folder identifier
		//			$rightList - array of user rights in format array( U_ID1=>RIGHT1,... )
		//			$groupRightList - array of group rights in format array( UG_ID1=>RIGHT1,... )
		//			$completeRights - set this value to true if $rightList contains rights for all users
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_tree_deleteFolderRights;
			global $qr_tree_insertUserRights;
			global $qr_tree_deleteUserFolderRights;
			global $qr_tree_deleteGroupFolderRights;
			global $qr_tree_deleteAllGroupFolderRights;
			global $qr_tree_insertGroupRights;

			$_user_id_field = 'U_ID';
			$_group_id_field = "UG_ID";
			$_folder_id_field = $this->folderDescriptor->folder_id_field;

			$tree_deleteFolderRights = $this->applySQLObjectNames( $qr_tree_deleteFolderRights );
			$tree_insertUserRights = $this->applySQLObjectNames( $qr_tree_insertUserRights );
			$tree_deleteUserFolderRights = $this->applySQLObjectNames( $qr_tree_deleteUserFolderRights );
			$tree_deleteGroupFolderRights = $this->applySQLObjectNames( $qr_tree_deleteGroupFolderRights );
			$tree_deleteAllGroupFolderRights = $this->applySQLObjectNames( $qr_tree_deleteAllGroupFolderRights );
			$tree_insertGroupRights = $this->applySQLObjectNames( $qr_tree_insertGroupRights );

			if ( $completeRights ) {
				$res = db_query( $tree_deleteFolderRights, array($_folder_id_field=>$ID, UR_PATH=>$this->folderDescriptor->folder_rights_path) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = db_query( $tree_deleteAllGroupFolderRights, array($_folder_id_field=>$ID, UR_PATH=>$this->folderDescriptor->folder_rights_path) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			// Set individual user rights
			//

			foreach( $rightList as $U_ID=>$RIGHT )
			{
				$params = array(UR_PATH=>$this->folderDescriptor->folder_rights_path);
				$params[$_user_id_field] = $U_ID;
				$params[$_folder_id_field] = $ID;
				$params[UR_VALUE] = $RIGHT;

				if ( !$completeRights ) {
					$res = db_query( $tree_deleteUserFolderRights, $params );
					if ( PEAR::isError($res) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}

				if ( $RIGHT != TREE_NOACCESS ) {
					$res = db_query( $tree_insertUserRights, $params );
					if ( PEAR::isError($res) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING]  );
				}
			}

			// Set group rights
			//
			foreach( $groupRightList as $UG_ID=>$RIGHT ) {
				$params = array(UR_PATH=>$this->folderDescriptor->folder_rights_path);
				$params[$_group_id_field] = $UG_ID;
				$params[$_folder_id_field] = $ID;
				$params[UR_VALUE] = $RIGHT;

				if ( !$completeRights ) {
					$res = db_query( $tree_deleteUserFolderRights, $params );
					if ( PEAR::isError($res) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}

				if ( $RIGHT != TREE_NOACCESS ) {
					$res = db_query( $tree_insertGroupRights, $params );
					if ( PEAR::isError($res) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}
			}

			return null;
		}

		function listCollapsedFolders( $U_ID )
		//
		// Returns list of collapsed folders
		//
		//		Parameters:
		//			$U_ID - user identifier
		//
		//		Returns array of folder identifiers
		//
		{
			$varName = $this->globalPrefix."COLLAPSEDFOLDERS";
			$folders = readUserCommonSetting( $U_ID, $varName );

			if ( strlen($folders) )
				$folders = explode( ';', $folders );
			else
				$folders = array();

			$result = array();
			foreach( $folders as $key=>$keyDF_ID )
				$result[$keyDF_ID] = 1;

			return $result;
		}

		function setFolderCollapseValue( $U_ID, $ID, $value, $kernelStrings )
		//
		// Saves folder collapse value
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$ID - folder identifier
		//			$value - collapse value
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null
		//
		{
			$varName = $this->globalPrefix."COLLAPSEDFOLDERS";
			$folders = readUserCommonSetting( $U_ID, $varName );

			if ( strlen($folders) )
				$folders = explode( ';', $folders );
			else
				$folders = array();

			$keys = array();
			foreach( $folders as $key=>$key_ID )
				$keys[$key_ID] = 1;

			if ( !$value ) {
				if ( isset($keys[$ID]) )
					unset($keys[$ID]);
			} else
				$keys[$ID] = 1;

			$folders = implode( ";", array_keys($keys) );

			writeUserCommonSetting( $U_ID, $varName, $folders, $kernelStrings );
		}

		function getIdentityFolderRights( $ID_ID, $ID, $kernelStrings, $IDT_Type = IDT_USER, $groupSummary = true, $groups = null, $forceGroupAccessMode = false, $accessType = ACCESS_SUMMARY )
		//
		// Returns user or group rights in specified folder
		//
		//		Parameters:
		//			$ID_ID - identity identifier
		//			$ID - folder identidier
		//			$kernelStrings - Kernel localization strings
		//			$IDT_Type - identity type (IDT_USER, IDT_GROUP)
		//			$groupSummary - calculate group summary rights. Applies only to user identities
		//			$groups - list of user groups to use instead of database assigned groups
		//			$forceGroupAccessMode - do not perform user access type check
		//			$accessType - user access type
		//
		//		Returns rights value
		//
		{
			global $UR_Manager;

			if ( $ID == TREE_RECYCLED_FOLDER )
				return TREE_WRITEREAD;

			if ( !$this->checkRights )
				return TREE_READWRITEFOLDER;

			if ( $IDT_Type == IDT_USER )
				return $UR_Manager->GetUserRightValue( $ID_ID, $this->folderDescriptor->folder_rights_path."/".$ID );
			else
				return $UR_Manager->GetGroupRightValue( $ID_ID, $this->folderDescriptor->folder_rights_path."/".$ID );

			return $res;
		}

		function getUserDefaultFolder( $U_ID, $kernelStrings, $useCookies = false, $recycled = true )
		//
		// Returns identifier of last folder accessed by user.
		//		If this folder is not available, returns identifier
		//		of first available folder. If no folders available, returns null.
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//			$useCookies - use cookies instead of database
		//
		//		Returns string, null, or PEAR_Error
		//
		{
			$varName = $this->globalPrefix."LASTFOLDER";

			$isAdministrator = isAdministratorID($U_ID);
			if ( !$isAdministrator ) {
				$ID = readUserCommonSetting( $U_ID, $varName, $useCookies );
			} else {
				if ( isset($_SESSION[$varName]) )
					$ID = $_SESSION[$varName];
				else
					$ID = null;
			}

			if ( !strlen($ID) )
				$ID = null;

			// Obtain access type value
			//
			if ( strlen($ID) )
				$status = $this->getFolderStatus($ID, $kernelStrings);

			if ( !strlen($ID) ||
					(!$isAdministrator && strlen($ID) && $this->getIdentityFolderRights( $U_ID, $ID, $kernelStrings, IDT_USER ) == TREE_NOACCESS) ||
					(strlen($ID) && ($status == TREE_FSTATUS_DELETED || !strlen($status)) ) ) {

				$access = null;
				$hierarchy = null;
				$deletable = null;
				$folders = $this->listFolders( $U_ID, TREE_ROOT_FOLDER, $kernelStrings, 0, $recycled, $access, $hierarchy, $deletable, null, null, false, null, true );

				if ( PEAR::isError( $folders ) )
					return $folders;

				if ( count($folders) ) {
					$folders = $this->filteravailableFolders( $folders );

					$folderIDs = array_keys($folders);
					$firstID = $folderIDs[0];

					return $firstID;
				}
					else
						return null;
			} else
				return $ID;
		}

		function setUserDefaultFolder( $U_ID, $ID, $kernelStrings, $useCookies = false )
		//
		// Sets user last accessed folder ID
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$ID - folder identifier
		//			$kernelStrings - Kernel localization strings
		//			$useCookies - use cookies instead of database
		//
		//		Returns null
		//
		{
			$varName = $this->globalPrefix."LASTFOLDER";

			if ( !isAdministratorID($U_ID) )
				writeUserCommonSetting( $U_ID, $varName, $ID, $kernelStrings, $useCookies );
			else
				$_SESSION[$varName] = $ID;

			return null;
		}

		function listFolderDocuments( $ID, $U_ID, $sortStr, $kernelStrings, $entryProcessor = null, $ignoreUsers = false, $limitStart = null, $limitCount = null, $folderAccessLevel = null )
		//
		// Returns list of documents in specified folder
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$U_ID - user identifier
		//			$sortStr - sorting string
		//			$kernelStrings - Kernel localization strings
		//			$entryProcessor - callback function to process document entry. Can be null
		//			$ignoreUsers - list all documents in spite of users rights
		//			$limitStart, $limitCount - limit query result by this interval
		//			$folderAccessLevel - user access level for this folder. This value will be passed to document objects
		//
		//		Returns array of object representing document table rows
		//
		{
			global $qr_tree_selectRecycledDocuments;
			global $qr_tree_selectAllRecycledDocuments;
			global $qr_tree_selectFolderDocumentsIgnoreUser;
			global $qr_limit_clause;

			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_document_id_field = $this->documentDescriptor->document_id_field;
			$_document_status_field = $this->documentDescriptor->document_status_field;
			$_user_id_field = (is_object($this->accessDescriptor))?$this->accessDescriptor->user_id_field:null;
			$_document_modifyuid_field = $this->documentDescriptor->document_modifyuid_field;
			$_access_right_field = TREE_ACCESS_RIGHTS;

			$limitStr = is_null($limitStart) ? '' : sprintf( $qr_limit_clause, $limitStart, $limitCount );

			if ( $ID != TREE_RECYCLED_FOLDER ) {
				$sql = $this->applySQLObjectNames( $qr_tree_selectFolderDocumentsIgnoreUser );

				$status = TREE_DLSTATUS_NORMAL;
			} else {
				if ( !$ignoreUsers )
					$sql = $this->applySQLObjectNames( $qr_tree_selectRecycledDocuments );
				else
					$sql = $this->applySQLObjectNames( $qr_tree_selectAllRecycledDocuments );

				$status = TREE_DLSTATUS_DELETED;
			}

			$sql = sprintf( $sql, $sortStr, $limitStr );

			$qr = db_query( $sql, array( $_folder_id_field=>$ID, $_document_status_field=>$status, $_user_id_field=>$U_ID, $_document_modifyuid_field=>$U_ID ) );

			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result = array();

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				if ( !is_null($folderAccessLevel) )
					$row->$_access_right_field = $folderAccessLevel;

				if ( is_null( $entryProcessor ) )
					$result[$row->$_document_id_field] = $row;
				else
					$result[$row->$_document_id_field] = call_user_func( $entryProcessor, $row );
			}

			db_free_result( $qr );

			return $result;
		}

		function getDocumentInfo( $ID, $kernelStrings )
		//
		// Returns document information - document record from database as array
		//
		//		Parameters:
		//			$ID - document identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns array
		//
		{
			global $qr_tree_selectDocument;

			$_document_id_field = $this->documentDescriptor->document_id_field;

			$tree_selectDocument = $this->applySQLObjectNames( $qr_tree_selectDocument );
			$result = db_query_result( $tree_selectDocument, DB_ARRAY, array( $_document_id_field=>$ID ) );
			if ( PEAR::isError($result) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $result;
		}

		function genDocumentID( $kernelStrings )
		//
		// Generates next document identifier
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns new identifier or PEAR_Error
		//
		{
			global $qr_tree_getMaxDocID;

			$tree_getMaxDocID = $this->applySQLObjectNames( $qr_tree_getMaxDocID );

			$ID = db_query_result( $tree_getMaxDocID, DB_FIRST, array() );
			if ( PEAR::isError($ID) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return incID($ID);
		}

		function copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation = null, $callbackParams = null, $perFileCheck = true, $checkUserRights = true, $onFinishOperation = null, $suppressNotifications = false )
		//
		// Copies or moves documents to another folder
		//
		//		Parameters:
		//			$documentList - list of document IDs to copy
		//			$destID - destination folder
		//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//			$onAfterOperation - callback function to execute after copy or move operation
		//			$onBeforeOperation - callback function to execute before copy or move operation
		//			$callbackParams - parameters to pass to callback functions
		//			$perFileCheck - perform rights checking for each file
		//			$checkUserRights - variable for internal use, must be true
		//			$onFinishOperation - callback function to execute after operation completion
		//			$suppressNotifications - don't send any notifications
		//
		//		Returns null or PEAR_Error
		//
		{
			global $_PEAR_default_error_mode;
			global $_PEAR_default_error_options;

			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_document_id_field = $this->documentDescriptor->document_id_field;

			if ( !strlen($destID) )
				return PEAR::raiseError( $kernelStrings['app_treecopymovedest_message'], ERRCODE_APPLICATION_ERR );

			if ( $checkUserRights ) {
				// Check user folder rights
				//
				$rights = $this->getIdentityFolderRights( $U_ID, $destID, $kernelStrings );
				if ( PEAR::isError($rights) )
					return $rights;

				if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
					return PEAR::raiseError( $kernelStrings['app_treenocopymoverights_message'], ERRCODE_APPLICATION_ERR );
			}

			$resultDestFileList = array();
			$resultSrcFileList = array();

			foreach( $documentList as $DOC_ID ) {
				// Check user document rights
				//
				$docData = $this->getDocumentInfo( $DOC_ID, $kernelStrings );
				if ( PEAR::isError($docData) )
					return $docData;

				// Destination file list
				//
				$resultDestFileList[$destID][] = $docData;

				$srcID = $docData[$_folder_id_field];
				$resultSrcFileList[$srcID][] = $docData;

				if ( $perFileCheck ) {
					$rights = $this->getIdentityFolderRights( $U_ID, $srcID, $kernelStrings );
					if ( PEAR::isError($rights) )
						return $rights;

					if ( !UR_RightsObject::CheckMask( $rights, TREE_ONLYREAD ) && $operation == TREE_COPYDOC )
						return PEAR::raiseError( $kernelStrings['app_treenocopyfromrights_message'],
													ERRCODE_APPLICATION_ERR,
													$_PEAR_default_error_mode,
													$_PEAR_default_error_options,
													$DOC_ID );
					else
						if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) && $operation == TREE_MOVEDOC )
							return PEAR::raiseError( $kernelStrings['app_treenomovefromrights_message'],
														ERRCODE_APPLICATION_ERR,
														$_PEAR_default_error_mode,
														$_PEAR_default_error_options,
														$DOC_ID );
				}

				// Prepare parameters for "after operation" callback
				//

				$params = $docData;

				if ( $operation == TREE_COPYDOC ) {
					$new_ID = $this->genDocumentID( $kernelStrings );
					if ( PEAR::isError($new_ID) )
						return $new_ID;

					$params[$_document_id_field] = $new_ID;
					$params[$_folder_id_field] = $destID;
				} else {

					$params[$_folder_id_field] = $destID;
					$params[$_document_id_field] = $DOC_ID;
				}

				$params['old_doc_id'] = $DOC_ID;

				// Call "before operation" callback
				//
				if ( !is_null($onBeforeOperation) ) {
					$beforeOpParams = array_merge( $params, $callbackParams );
					$opParams = call_user_func_array( $onBeforeOperation, array($DOC_ID, $kernelStrings, $srcID, $destID, $operation, &$docData, $beforeOpParams) );
					if ( PEAR::isError($opParams) )
						return $opParams;

					if ( $operation == TREE_COPYDOC ) {
						$params = $docData;
						$params[$_document_id_field] = $new_ID;
						$params[$_folder_id_field] = $destID;
					}
				} else
					$opParams = array();

				// Call "after operation" callback
				//
				$afterOpParams = array_merge( $opParams, $callbackParams );

				$res = call_user_func( $onAfterOperation, $kernelStrings, $U_ID, $params, $operation, $afterOpParams );
				if ( PEAR::isError($res) )
					return $res;
			}

			if ( !is_null($onFinishOperation) ) {
				$callbackParams['suppressNotifications'] = $suppressNotifications;
				$res = call_user_func( $onFinishOperation, $kernelStrings, $U_ID, $resultDestFileList, $resultSrcFileList, $operation, $callbackParams );
				if ( PEAR::isError($res) )
					return $res;
			}

			return null;
		}

		function copyFolderBranch( $U_ID, $destID, $srcID, $folderList, $hierarchy, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null, $callbackParams = null, $accessInheritance = ACCESSINHERITANCE_COPY, $inheritUserRights = null, $inheritGroupRights = null, $onBeforeFolderCreate=null, $checkFolderName = true, $copyChilds = true )
		//
		// Internal funcions. Copies folder and subfolders to another location
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$destID - destination folder identifier
		//			$srcID - source folder identifier
		//			$folderList - array of folders to copy
		//			$hierarchy - folder hierarchy array
		//			$kernelStrings - Kernel localization strings
		//			$onAfterDocumentOperation - callback function to execute after document copy or move operation
		//			$onBeforeDocumentOperation - callback function to execute before document copy or move operation
		//			$onFolderCreate - callback function to execute after folder creation
		//			$callbackParams - parameters to pass to callback functions
		//			$checkUserRights - variable for internal use, must be true
		//			$accessInheritance - access inheritance behavior
		//			$inheritUserRights - array containing users rights to inherit
		//			$inheritGroupRights - array containing groups rights to inherit
		//			$onBeforeFolderCreate - callback function to execute before folder creation
		//
		//		Returns identifier of new folder or PEAR_Error
		//
		{
			// Create folder copy in destination folder
			//
			$srcFolderInfo = $folderList[$srcID];

			$callbackParams['originalData'] = $srcFolderInfo;

			$newDF_ID = $this->addmodFolder( ACTION_NEW, $U_ID, $destID, (array)$srcFolderInfo, $kernelStrings, true,
												$onFolderCreate, $callbackParams, false, true, null, $checkFolderName, $onBeforeFolderCreate );
			if ( PEAR::isError($newDF_ID) )
				return $newDF_ID;

			// Set user folder rights
			//
			if ( $accessInheritance == ACCESSINHERITANCE_COPY ) {
				$parentRights = $this->listFolderUsersRights( $srcID, $kernelStrings );
				if ( PEAR::isError( $parentRights ) )
					return $parentRights;
			} else
				$parentRights = $inheritUserRights;

			foreach( $parentRights as $rU_ID=>$rightsData ) {
				$rights = $rightsData['RIGHTS'];

				$res = $this->setIdentityRights( $rU_ID, IDT_USER, array( $newDF_ID=>$rights ), $kernelStrings );
				if ( PEAR::isError( $res ) )
					return $res;
			}

			// Set group folder rights
			//
			if ( $accessInheritance == ACCESSINHERITANCE_COPY ) {
				$parentRights = $this->listFolderGroupsRights( $srcID, $kernelStrings );
				if ( PEAR::isError( $parentRights ) )
					return $parentRights;
			} else
				$parentRights = $inheritGroupRights;

			foreach( $parentRights as $UG_ID=>$rightsData )
			{
				$rights = $rightsData['RIGHTS'];

				$res = $this->setIdentityRights( $UG_ID, IDT_GROUP, array( $newDF_ID=>$rights ), $kernelStrings );
				if ( PEAR::isError( $res ) )
					return $res;
			}

			// Copy or move files to new folder
			//
			if ( $srcFolderInfo->RIGHT > TREE_NOACCESS && trim( $this->documentDescriptor->tableName ) != "" )
			{
				$_document_id_field = $this->documentDescriptor->document_id_field;
				$sortStr = sprintf( "%s ASC", $_document_id_field );

				$documents = $this->listFolderDocuments( $srcID, $U_ID, $sortStr, $kernelStrings );
				if ( PEAR::isError($documents) )
					return $documents;

				$documentList = array_keys($documents);
				$res = $this->copyMoveDocuments( $documentList, $newDF_ID, TREE_COPYDOC, $U_ID, $kernelStrings,
													$onAfterDocumentOperation, $onBeforeDocumentOperation,
													$callbackParams, false, false, null, true );

				if ( PEAR::isError($res) )
					return $res;
			}

			if ( $copyChilds && count( $hierarchy ) ) {
				foreach( $hierarchy as $hDF_ID=>$cHierarchy ) {
					$res = $this->copyFolderBranch( $U_ID, $newDF_ID, $hDF_ID, $folderList, $cHierarchy, $kernelStrings, $onAfterDocumentOperation,
												$onBeforeDocumentOperation, $onFolderCreate, $callbackParams, $accessInheritance,
												$inheritUserRights, $inheritGroupRights, $onBeforeFolderCreate, $checkFolderName );

					if ( PEAR::isError($res) )
						return $res;
				}
			}

			return $newDF_ID;
		}

		function copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null, $callbackParams = null, $onFininshCopy = null, $accessInheritance = ACCESSINHERITANCE_COPY, $onBeforeFolderCreate = null, $checkFolderName = true, $copyChilds = true, $checkUserRights = true )
		//
		//	Copies folder to another folder
		//
		//		Parameters:
		//			$srcID - source folder identifier
		//			$destID - destination folder identifier
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//			$onAfterDocumentOperation - callback function to execute after document copy or move operation
		//			$onBeforeDocumentOperation - callback function to execute before document copy or move operation
		//			$onFolderCreate - callback function to execute after folder creation
		//			$callbackParams - parameters to pass to callback functions
		//			$checkUserRights - variable for internal use, must be true
		//			$onFininshCopy - callback function to execute after finishing operation
		//			$accessInheritance - access inheritance behavior
		//			$onBeforeFolderCreate - callback function to execute before folder creation
		//			$checkFolderName
		//			$copyChilds
		//			$checkUserRights - indicates whether user rights must be checked before operation
		//
		//		Returns identifier of new folder or PEAR_Error
		//
		{
			if ( !strlen($destID) )
				return PEAR::raiseError( $kernelStrings['app_treecopymovedest_message'], ERRCODE_APPLICATION_ERR );

			if ( $destID == TREE_AVAILABLE_FOLDERS )
				$destID = TREE_ROOT_FOLDER;

			// Check rights on destination folder
			//
			if ( $checkUserRights ) {
					$rights = $this->getIdentityFolderRights( $U_ID, $destID, $kernelStrings );
					if ( PEAR::isError($rights) )
						return $rights;

					if ( !UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER ) )
						return PEAR::raiseError( $kernelStrings['app_treenocopytorights_message'], ERRCODE_APPLICATION_ERR );

				// Check rights on source folder
				//
				$rights = $this->getIdentityFolderRights( $U_ID, $srcID, $kernelStrings );
				if ( PEAR::isError($rights) )
					return $rights;

				if ( !UR_RightsObject::CheckMask( $rights, TREE_ONLYREAD ) )
					return PEAR::raiseError( $kernelStrings['app_treenocopyfldrights_message'], ERRCODE_APPLICATION_ERR );
			} else
				$rights = TREE_READWRITEFOLDER;

			// Prepare list of folers to copy
			//
			$access = null;
			$hierarchy = null;
			$deletable = null;
			$folders = $this->listFolders( $U_ID, $srcID, $kernelStrings, 0, false, $access, $hierarchy, $deletable, null, null, false, null, false );
			if ( PEAR::isError($folders) )
				return $folders;

			// Add source folder data to list
			//
			$curFolderData = $this->getFolderInfo( $srcID, $kernelStrings );
			if ( PEAR::isError($curFolderData) )
				return $curFolderData;

			$curFolderData['RIGHT'] = $rights;

			$folders = array_merge( array( $srcID=>(object)$curFolderData ), $folders );
			$hierarchy = array( $srcID => $hierarchy );

			// Load destination users and groups rights for access inheritance mode
			//
			if ( $accessInheritance == ACCESSINHERITANCE_INHERIT ) {
				$inheritUserRights = $this->listFolderUsersRights( $destID, $kernelStrings );
				if ( PEAR::isError( $inheritUserRights ) )
					return $inheritUserRights;

				$inheritGroupRights = $this->listFolderGroupsRights( $destID, $kernelStrings );
				if ( PEAR::isError( $inheritGroupRights ) )
					return $inheritGroupRights;
			} else {
				$inheritGroupRights = array();
				$inheritUserRights = array();
			}

			// Perform copy operation
			//
			$newFolderID = $this->copyFolderBranch( $U_ID, $destID, $srcID, $folders, $hierarchy[$srcID], $kernelStrings, $onAfterDocumentOperation,
										$onBeforeDocumentOperation, $onFolderCreate, $callbackParams, $accessInheritance, $inheritUserRights, $inheritGroupRights, $onBeforeFolderCreate, $checkFolderName, $copyChilds );

			if ( PEAR::isError($newFolderID) )
				return $newFolderID;

			if ( !is_null($onFininshCopy) ) {
				$callbackParams['destID'] = $destID;
				$callbackParams['newID'] = $newFolderID;
				$res = call_user_func( $onFininshCopy, $kernelStrings, $U_ID, TREE_COPYFOLDER, $callbackParams );
			}

			return $newFolderID;
		}

		function moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null,
								$onFolderDelete = null, $callbackParams = null, $onFinishMove = null, $checkUserRights = true,
								$topLevel = true, $accessInheritance = ACCESSINHERITANCE_COPY, $mostTopRightsSource = null,
								$folderStatus = TREE_FSTATUS_NORMAL, $plainMove = false, $checkFolderName = true )
		//
		// Moves folder to another folder
		//
		//		Parameters:
		//			$srcID - source folder identifier
		//			$destID - destination folder identifier
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//			$onAfterDocumentOperation - callback function to execute after document copy or move operation
		//			$onBeforeDocumentOperation - callback function to execute before document copy or move operation
		//			$onFolderCreate - callback function to execute after folder creation
		//			$onFolderDelete - callback function to execute after folder deletion
		//			$callbackParams - parameters to pass to callback functions
		//			$onFinishMove - callback function to execute after move operation
		//			$checkUserRights - variable for internal use, must be true
		//			$topLevel - variable for internal use, must be true
		//			$accessInheritance - access inheritance behavior
		//			$mostTopRightsSource - folder identifier to copy access rights from
		//			$folderStatus - set this status for moved folders
		//			$plainMove - move all folders, including child folders, to the same folder
		//
		//		Returns identifier of new folder or PEAR_Error
		//
		{

			if ( !strlen($destID) )
				return PEAR::raiseError( $kernelStrings['app_treecopymovedest_message'], ERRCODE_APPLICATION_ERR );

			if ( $destID == TREE_AVAILABLE_FOLDERS )
				$destID = TREE_ROOT_FOLDER;

			if ( $destID == $srcID )
				return PEAR::raiseError( $kernelStrings['app_treenomovetofldrights_message'], ERRCODE_APPLICATION_ERR );

			if ( $checkUserRights ) {
				// Check rights on destination folder
				//
				$rights = $this->getIdentityFolderRights( $U_ID, $destID, $kernelStrings );
				if ( PEAR::isError($rights) )
					return $rights;

				if ( !UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER ) )#
					return PEAR::raiseError( $kernelStrings['app_treenomovetofldrights_message'], ERRCODE_APPLICATION_ERR );

				// Check rights on source folder
				//
				$rights = $this->recursiveCheckFolderRights( $srcID, $U_ID, TREE_READWRITEFOLDER, $kernelStrings );
				if ( PEAR::isError($rights) )
					return $rights;

				if ( !$rights )
					return PEAR::raiseError( $kernelStrings['app_treenomovetosubfldrights_message'], ERRCODE_APPLICATION_ERR );
			}

			$folderUsers = $this->listFolderUsers( $srcID, $kernelStrings );
			if ( PEAR::isError($folderUsers) )
				return $folderUsers;

			if ( $topLevel ) {
				$curFolderData = $this->getFolderInfo( $srcID, $kernelStrings );
				$callbackParams['deletedFolderData'] = $curFolderData;
			}

			// Create folder copy in destination folder
			//
			$srcFolderInfo = $this->getFolderInfo( $srcID, $kernelStrings );
			if ( PEAR::isError($srcFolderInfo) )
				return $srcFolderInfo;

			$callbackParams['move'] = true;
			$callbackParams['originalData'] = $srcFolderInfo;
			$newDF_ID = $this->addmodFolder( ACTION_NEW, $U_ID, $destID, $srcFolderInfo, $kernelStrings, true,
												$onFolderCreate, $callbackParams, false, true, $folderStatus, $checkFolderName );

			if ( PEAR::isError($newDF_ID) )
				return $newDF_ID;

			if ( $topLevel ) {
				$curFolderData = $this->getFolderInfo( $newDF_ID, $kernelStrings );
				$callbackParams['newFolderData'] = $curFolderData;
			}

			// Set folder rights
			//
			if ( $topLevel )
				$mostTopRightsSource = $destID;

			if ( $accessInheritance == ACCESSINHERITANCE_COPY )
				$res = $this->propagateFolderRights( $srcID, $newDF_ID, $kernelStrings );
			else
				$res = $this->propagateFolderRights( $mostTopRightsSource, $newDF_ID, $kernelStrings );

			if ( PEAR::isError($res) )
				return $res;

			// Copy or move files to new folder
			//
			if ( trim( $this->documentDescriptor->tableName ) != ""  )
			{
				$fileOperation = TREE_MOVEDOC;

				$_document_id_field = $this->documentDescriptor->document_id_field;
				$sortStr = sprintf( "%s ASC", $_document_id_field );

				$documents = $this->listFolderDocuments( $srcID, $U_ID, $sortStr, $kernelStrings );
				if ( PEAR::isError($documents) )
				return $documents;

				$documentList = array_keys($documents);
				$res = $this->copyMoveDocuments( $documentList, $newDF_ID, $fileOperation, $U_ID, $kernelStrings,
												$onAfterDocumentOperation, $onBeforeDocumentOperation,
												$callbackParams, false, false, null, true );

				if ( PEAR::isError($res) )
					return $res;
			}

			// Copy chlidren folders
			//
			global $qr_tree_selectParentFoldersAnyStatus;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;
			$_folder_parent_field = $this->folderDescriptor->folder_parent_field;
			$_folder_status_field = $this->folderDescriptor->folder_status_field;

			$tree_selectParentFoldersAnyStatus = $this->applySQLObjectNames( $qr_tree_selectParentFoldersAnyStatus );
			$params = array();
			$params[$_folder_parent_field] = $srcID;
			$params[$_folder_status_field] = TREE_FSTATUS_NORMAL;

			$qr = @db_query( $tree_selectParentFoldersAnyStatus, $params );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
				$destFolderID = (!$plainMove) ? $newDF_ID : $destID;

				$res = $this->moveFolder( $row->$_folder_id_field, $destFolderID, $U_ID, $kernelStrings,
												$onAfterDocumentOperation, $onBeforeDocumentOperation,
												$onFolderCreate, $onFolderDelete, $callbackParams, $onFinishMove,
												false, false, $accessInheritance, $mostTopRightsSource, $folderStatus, $plainMove, $checkFolderName );
				if ( PEAR::isError($res) )
					return $res;
			}

			db_free_result( $qr );

			// Delete folder
			//
			$res = $this->deleteFolder( $srcID, $U_ID, $kernelStrings, true, $onFolderDelete, $callbackParams, true, false ); // �������� false
			if ( PEAR::isError($res) )
				return $res;

			// Finish callback
			//
			if ( $topLevel ) {
				if ( !is_null($onFinishMove) ) {
					$callbackParams['kernelStrings'] = $kernelStrings;
					if ( !isset($callbackParams['suppressNotifications']) )
						$callbackParams['suppressNotifications'] = false;
					$callbackParams['U_ID'] = $U_ID;
					$callbackParams['operation'] = TREE_MOVEFOLDER;
					$callbackParams['folderUsers'] = $folderUsers;

					$res = call_user_func( $onFinishMove, $callbackParams );
				}
			}

			return $newDF_ID;
		}

		function getChildrenNum( $ID, $kernelStrings )
		//
		// Returns the number of immediate children of the folder
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			global $qr_tree_selectChildrenNum;

			$_folder_id_field = $this->folderDescriptor->folder_id_field;

			$tree_selectChildrenNum = $this->applySQLObjectNames( $qr_tree_selectChildrenNum );

			$result = db_query_result( $tree_selectChildrenNum, DB_FIRST, array( $_folder_id_field=>$ID ) );
			if ( PEAR::isError($result) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $result;
		}

		function isRootIdentity( $ID_ID, $kernelStrings, $ID_Type = IDT_USER, $groupSummary = true, $groups = null, $forceGroupAccessMode = false, $accessType = ACCESS_SUMMARY )
		//
		// Returns true if user or group can create root folders
		//
		//		Parameters:
		//			$ID_ID - identity identifier
		//			$kernelStrings - Kernel localization strings
		//			$ID_Type - identity type (IDT_USER, IDT_GROUP)
		//			$groupSummary - calculate group summary rights. Applies only to user identities
		//			$groups - list of user groups to use instead of database assigned groups
		//			$forceGroupAccessMode - do not perform user access type check
		//			$accessType - user access type
		//
		//		Returns boolean or PEAR_Error
		//
		{
			if ( !$this->checkRights )
				return true;

			$res = $this->getIdentityFolderRights( $ID_ID, TREE_ROOT_FOLDER, $kernelStrings, $ID_Type, $groupSummary, $groups, $forceGroupAccessMode, $accessType );
			if ( PEAR::isError($res) )
				return $res;

			return UR_RightsObject::CheckMask($res, TREE_READWRITEFOLDER);
		}

		function getSummaryStatistics( $U_ID, &$folderNum, &$documentNum, $kernelStrings )
		//
		// Returns number of folders and documents available to user
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$folderNum - number of accessible folders
		//			$documentNum - number of accessible files
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_tree_selectUsersFoldersIds;
			global $qr_tree_selectGroupFoldersIds;
			global $qr_tree_selectUserSummaryFoldersIds;
			global $qr_tree_selectUserDocumentsCount;

			$_folder_status_field = $this->folderDescriptor->folder_status_field;

			$tree_selectUserFoldersIds = $this->applySQLObjectNames( sprintf( $qr_tree_selectUserSummaryFoldersIds, $qr_tree_selectUsersFoldersIds, $qr_tree_selectGroupFoldersIds ) );
			$tree_selectUserDocumentsCount = $this->applySQLObjectNames( sprintf( $qr_tree_selectUserDocumentsCount, $qr_tree_selectUsersFoldersIds, $qr_tree_selectGroupFoldersIds ) );

			$fldQr = db_query( $tree_selectUserFoldersIds, array( 'U_ID'=>$U_ID, $_folder_status_field => TREE_FSTATUS_NORMAL, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($fldQr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$folderNum = db_result_num_rows( $fldQr );

			db_free_result( $fldQr );

			$fldQr = db_query_result( $tree_selectUserDocumentsCount, DB_FIRST, array( 'U_ID'=>$U_ID, $_folder_status_field => TREE_FSTATUS_NORMAL, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($fldQr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$documentNum = $fldQr;


			return null;
		}

		function isChildOf( $child, $parent, $kernelStrings )
		{
			if ( $child == TREE_AVAILABLE_FOLDERS )
				$child = TREE_ROOT_FOLDER;

			if ( $parent == TREE_AVAILABLE_FOLDERS )
				$parent = TREE_ROOT_FOLDER;

			$path = $this->getPathToFolder( $child, $kernelStrings );
			if ( PEAR::isError($path) )
				return $path;

			return array_key_exists( $parent, $path );
		}

		function canCreateFolders( $U_ID, $kernelStrings )
		//
		// Checks if user can create folders somewhere
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{

			global $qr_tree_selectUserRightTotalMask;
			global $UR_Manager;

			$res = db_query_result( $qr_tree_selectUserRightTotalMask, DB_FIRST, array( 'U_ID'=>$U_ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return UR_RightsObject::CheckMask( $res, TREE_ONLYFOLDER ) || $UR_Manager->GetUserRightValue( $U_ID, $this->folderDescriptor->folder_rights_path."/".TREE_ROOT_FOLDER );
		}

		function expandPathToFolder( $ID, $U_ID, $kernelStrings )
		//
		// Expands all parent folders to make folder visible
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			$path = $this->getPathToFolder( $ID, $kernelStrings );
			if ( PEAR::isError($path) )
				return $path;

			foreach ( $path as $ID=>$data  ) {
				$res = $this->setFolderCollapseValue( $U_ID, $ID, false, $kernelStrings );
				if ( PEAR::isError($res) )
					return $res;
			}

			return null;
		}

		function resetUserAccessRights( $kernelStrings, $U_ID )
		//
		// Completely removes user folder access rights
		//
		//		Parameters:
		//			$kernelStrings - Kernel localization strings
		//			$U_ID - user identifier
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_tree_deleteUserRights;

			$_user_id_field = $this->accessDescriptor->user_id_field;

			$tree_deleteUserRights = $this->applySQLObjectNames( $qr_tree_deleteUserRights );

			$res = db_query( $tree_deleteUserRights, array($_user_id_field=>$U_ID, UR_PATH=>$this->folderDescriptor->folder_rights_path) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return null;
		}

		function folderIsShared( $ID, $U_ID, $kernelStrings )
		//
		// Returns true if user shares folder with other users or groups
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_tree_selectFolderUserCount;
			global $qr_tree_selectFolderGroupCount;

			// Load individual user count
			//
			$userCnt = db_query_result( $qr_tree_selectFolderUserCount, DB_FIRST, array( UR_OBJECTID=>$ID, 'U_ID'=>$U_ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($userCnt) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			// Load folder group count
			//
			$groupCnt = db_query_result( $qr_tree_selectFolderGroupCount, DB_FIRST, array( UR_OBJECTID=>$ID, UR_PATH=>$this->folderDescriptor->folder_rights_path ) );
			if ( PEAR::isError($groupCnt) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			// Return true if user > 1 or group count > 0
			//
			return $userCnt > 0 || $groupCnt;
		}

		function getFolderStatus( $ID, $kernelStrings )
		//
		// Returns folder status
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns int or PEAR_Error
		//
		{
			global $qr_tree_select_folderStatus;

			$tree_select_folderStatus = $this->applySQLObjectNames( $qr_tree_select_folderStatus );

			$_folder_status_field = $this->folderDescriptor->folder_status_field;
			$_folder_id_field = $this->folderDescriptor->folder_id_field;

			$params = array( $_folder_id_field=>$ID );

			$status = db_query_result( $tree_select_folderStatus, DB_FIRST,$params );
			if ( PEAR::isError($status) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return $status;
		}
	}

	//
	// generic_collection class
	//

	class generic_collection
	//
	// Base class for collections
	//
	{
		var $items = array();
		var $itemClass = null;

		var $loadAsArrays = false;

		function getItemCount()
		{
			return count($this->items);
		}

		function getItemByKey( $key )
		{
			if ( array_key_exists($key, $this->items) )
				return $this->items[$key];

			return null;
		}

		function getItemByIndex( $index )
		{
			$itemCount = $this->getItemCount();

			$keys = array_keys( $this->items );

			if ( !array_key_exists($index, $keys) )
				return null;

			$key = $keys[$index];

			return $this->items[$key];
		}

		function loadFromDatabase( $queryProvider, &$providerParams, &$kernelStrings, &$callbackParams, $itemCallBack = null, $firstIndex = null, $count = null )
		//
		// Loads collection from database
		//
		//		Parameters:
		//			$queryProvider - method name for providing SQL query
		//			$providerParams - parameters for query provider
		//			$kernelStrings - Kernel localization strings
		//			$callbackParams - item callback parameters
		//			$itemCallBack - item callback function name
		//			$firstIndex - fetch items from this index
		//			$count - fetch this count of items
		//
		//		Returns null or PEAR_Error
		//
		{
			$sql = $this->$queryProvider( $providerParams );

			if ( !is_null($firstIndex) && !is_null($count) )
				$sql .= " LIMIT $firstIndex, $count";

			$res = db_query( $sql, $providerParams );

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], SOAPROBOT_ERR_QUERYEXECUTING );

			while ( $row = db_fetch_array($res) ) {
				if ( !$this->loadAsArrays ) {
					$item = new $this->itemClass();

					$item->loadFromArray( $row, $kernelStrings, false, array( s_datasource=>s_database ) );

					$collectionId = $item->getCollectionID();

					if ( !is_null($itemCallBack) )
						$item = eval( "return $itemCallBack( \$callbackParams, \$item );" );

					$this->items[$collectionId] = $item;
				} else {
					$item = $row;

					if ( !is_null($itemCallBack) )
						$item = eval( "return $itemCallBack( \$callbackParams, \$item );" );

					$this->items[] = $row;
				}
			}

			db_free_result( $res );

			return null;
		}

		function getQueryRowNumber( $queryProvider, &$providerParams, &$kernelStrings )
		//
		// Returns number of rows returned by query generated by query provider
		//
		//		Parameters:
		//			$queryProvider - method name for providing SQL query
		//			$providerParams - parameters for query provider
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns integer or PEAR_Error
		//
		{
			$sql = $this->$queryProvider( $providerParams );

			$res = db_query( $sql, $providerParams );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$res = mysql_num_rows($res->result);

			db_free_result($res);

			return $res;
		}
	}

?>
