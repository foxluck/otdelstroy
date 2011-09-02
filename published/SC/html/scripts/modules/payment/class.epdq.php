<?php
/**
 * @connect_module_class_name ePDQ
 * @package DynamicModules
 * @subpackage Payment
 */
	class ePDQ extends PaymentModule {

		var $type = PAYMTD_TYPE_CC;
		var $language = 'eng';
		
		function _initVars(){
			
			parent::_initVars();
			$this->log_mode = LOGMODE_NONE;
			$this->title = EPDQ_TTL;
			$this->description = EPDQ_DSCR;
			
			$this->Settings = array( 
					'CONF_EPDQ_HOST',
					'CONF_EPDQ_CLIENT_ID',
					'CONF_EPDQ_PASSPHRASE',
					'CONF_EPDQ_CHARGETYPE',
					'CONF_EPDQ_TRANSCURRENCY',
					'CONF_EPDQ_TRANSCURRENCYCODE',
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_EPDQ_HOST'] = array(
				'settings_value' 		=> 'secure2.mde.epdq.co.uk', 
				'settings_title' 			=> EPDQ_CFG_HOST_TTL,
				'settings_description' 	=> EPDQ_CFG_HOST_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			);
			$this->SettingsFields['CONF_EPDQ_CLIENT_ID'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> EPDQ_CFG_CLIENT_ID_TTL,
				'settings_description' 	=> EPDQ_CFG_CLIENT_ID_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			);
			$this->SettingsFields['CONF_EPDQ_PASSPHRASE'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> EPDQ_CFG_PASSPHRASE_TTL,
				'settings_description' 	=> EPDQ_CFG_PASSPHRASE_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			);
			$this->SettingsFields['CONF_EPDQ_CHARGETYPE'] = array(
				'settings_value' 		=> 'Auth', 
				'settings_title' 			=> EPDQ_CFG_CHARGETYPE_TTL,
				'settings_description' 	=> EPDQ_CFG_CHARGETYPE_DSCR,
				'settings_html_function' 	=> 'setting_SELECT_BOX(ePDQ::_getChargeTypeOptions(),', 
			);
			$this->SettingsFields['CONF_EPDQ_TRANSCURRENCYCODE'] = array(
				'settings_value' 		=> '826', 
				'settings_title' 			=> EPDQ_CFG_TRANSCURRENCYCODE_TTL,
				'settings_description' 	=> EPDQ_CFG_TRANSCURRENCYCODE_DSCR,
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			);
			$this->SettingsFields['CONF_EPDQ_TRANSCURRENCY'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> EPDQ_CFG_TRANSCURRENCY_TTL,
				'settings_description' 	=> EPDQ_CFG_TRANSCURRENCY_DSCR,
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			);
		}
		
		function _getChargeTypeOptions(){
			
			return array(
				array(
					'title' => EPDQ_TXT_AUTH,
					'value' => 'Auth'
					),
				array(
					'title' => EPDQ_TXT_PREAUTH,
					'value' => 'PreAuth'
					),
			);
		}
		
		function after_processing_html($order_id){
			
			$order = ordGetOrder($order_id);
			
			#define the remote cgi in readiness to call pullpage function 
			$server = $this->_getSettingValue('CONF_EPDQ_HOST');
			$url="/cgi-bin/CcxBarclaysEpdqEncTool.e";
			
			#the following parameters have been obtained earlier in the merchant's webstore
			#clientid, passphrase, oid, currencycode, total
			$params="clientid=".$this->_getSettingValue('CONF_EPDQ_CLIENT_ID');
			$params.="&password=".$this->_getSettingValue('CONF_EPDQ_PASSPHRASE');
			$params.="&oid=".$order_id;
			$params.="&chargetype=".$this->_getSettingValue('CONF_EPDQ_CHARGETYPE');
			$params.="&currencycode=".$this->_getSettingValue('CONF_EPDQ_TRANSCURRENCYCODE');
			$params.="&total=".RoundFloatValue($this->_convertCurrency($order['order_amount'],0, $this->_getSettingValue('CONF_EPDQ_TRANSCURRENCY')));
	
			#perform the HTTP Post
			$error_str = '';
			$response = $this->pullpage( $server,$url,$params, $error_str );
			   
			#split the response into separate lines
			$response_lines=explode("\n",$response);
			
			#for each line in the response check for the presence of the string 'epdqdata'
			#this line contains the encrypted string
			$response_line_count=count($response_lines);
			for ($i=0;$i<$response_line_count;$i++){
			    if (preg_match('/epdqdata/',$response_lines[$i])){
			        $strEPDQ=$response_lines[$i];
			    }
			}
			
			return '
			<form action="'.CONF_FULL_SHOP_URL.'epdq.php" method="post" style="text-align:center">
			'.($error_str?('<p style="color:red;font-weight:bold;">'.$error_str.'</p>'):'').
			$strEPDQ.'
			<input type="hidden" name="jsredirect" value="https://'.$this->_getSettingValue('CONF_EPDQ_HOST').'/cgi-bin/CcxBarclaysEpdq.e">
			<input type="hidden" name="returnurl" value="'.substr(xHtmlSpecialChars(preg_replace('/\/\w+\.php$/','/', CONF_FULL_SHOP_URL).'/epdq.php'),0,100).'">
			<input type="hidden" name="merchantdisplayname" value="'.substr(xHtmlSpecialChars(CONF_SHOP_NAME),0,30).'">
			<input type="submit" value="'.xHtmlSpecialChars(EPDQ_TXT_PURCHASE).'">
			</form>';
		}
	
		function pullpage( $host, $usepath, $postdata = "", &$error_str ) {
 
			# open socket to filehandle(epdq encryption cgi)
			 $fp = fsockopen( $host, 80, $errno, $errstr, 60 );
			
			#check that the socket has been opened successfully
			 if( !$fp ) {
			    $error_str = "$errstr ($errno)";
			    $this->_log(LOGTYPE_ERROR, $error_str);
			 }
			 else {
			
			    #write the data to the encryption cgi
			    fputs( $fp, "POST $usepath HTTP/1.0\n");
			    $strlength = strlen( $postdata );
			    fputs( $fp, "Content-type: application/x-www-form-urlencoded\n" );
			    fputs( $fp, "Content-length: ".$strlength."\n\n" );
			    fputs( $fp, $postdata."\n\n" );
			
			    #clear the response data
			   $output = "";
			 
			 
			    #read the response from the remote cgi 
			    #while content exists, keep retrieving document in 1K chunks
			    while( !feof( $fp ) ) {
			        $output .= fgets( $fp, 1024);
			    }
			
			    #close the socket connection
			    fclose( $fp);
			 }
			
			#return the response
			 return $output;
			}
	}
?>