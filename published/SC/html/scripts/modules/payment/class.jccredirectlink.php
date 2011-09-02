<?php
	/*
	** Date modified: 1st October 2004 20:09 GMT
	*
	** PHP implementation of the Secure Hash Algorithm ( SHA-1 )
	*
	** This code is available under the GNU Lesser General Public License:
	** http://www.gnu.org/licenses/lgpl.txt
	*
	** Based on the PHP implementation by Marcus Campbell
	** http://www.tecknik.net/sha-1/
	*
	** This is a slightly modified version by me Jerome Clarke ( sinatosk@gmail.com )
	** because I feel more comfortable with this
	*/
	
	function sha1_str2blks_SHA1($str)
	{
	   $strlen_str = strlen($str);
	  
	   $nblk = (($strlen_str + 8) >> 6) + 1;
	  
	   for ($i=0; $i < $nblk * 16; $i++) $blks[$i] = 0;
	  
	   for ($i=0; $i < $strlen_str; $i++)
	   {
	       $blks[$i >> 2] |= ord(substr($str, $i, 1)) << (24 - ($i % 4) * 8);
	   }
	  
	   $blks[$i >> 2] |= 0x80 << (24 - ($i % 4) * 8);
	   $blks[$nblk * 16 - 1] = $strlen_str * 8;
	  
	   return $blks;
	}
	
	function sha1_safe_add($x, $y)
	{
	   $lsw = ($x & 0xFFFF) + ($y & 0xFFFF);
	   $msw = ($x >> 16) + ($y >> 16) + ($lsw >> 16);
	  
	   return ($msw << 16) | ($lsw & 0xFFFF);
	}
	
	function sha1_rol($num, $cnt)
	{
	   return ($num << $cnt) | sha1_zeroFill($num, 32 - $cnt);   
	}
	
	function sha1_zeroFill($a, $b)
	{
	   $bin = decbin($a);
	  
	   $strlen_bin = strlen($bin);
	  
	   $bin = $strlen_bin < $b ? 0 : substr($bin, 0, $strlen_bin - $b);
	  
	   for ($i=0; $i < $b; $i++) $bin = '0'.$bin;
	  
	   return bindec($bin);
	}
	
	function sha1_ft($t, $b, $c, $d)
	{
	   if ($t < 20) return ($b & $c) | ((~$b) & $d);
	   if ($t < 40) return $b ^ $c ^ $d;
	   if ($t < 60) return ($b & $c) | ($b & $d) | ($c & $d);
	  
	   return $b ^ $c ^ $d;
	}
	
	function sha1_kt($t)
	{
	   if ($t < 20) return 1518500249;
	   if ($t < 40) return 1859775393;
	   if ($t < 60) return -1894007588;
	  
	   return -899497514;
	}
	
	function _sha1($str, $raw_output=FALSE)
	{
	   if ( $raw_output === TRUE ) return pack('H*', sha1($str));
	  
	   $x = sha1_str2blks_SHA1($str);
	   $a =  1732584193;
	   $b = -271733879;
	   $c = -1732584194;
	   $d =  271733878;
	   $e = -1009589776;
	  
	   $x_count = count($x);
	  
	   for ($i = 0; $i < $x_count; $i += 16)
	   {
	       $olda = $a;
	       $oldb = $b;
	       $oldc = $c;
	       $oldd = $d;
	       $olde = $e;
	      
	       for ($j = 0; $j < 80; $j++)
	       {
	           $w[$j] = ($j < 16) ? $x[$i + $j] : sha1_rol($w[$j - 3] ^ $w[$j - 8] ^ $w[$j - 14] ^ $w[$j - 16], 1);
	          
	           $t = sha1_safe_add(sha1_safe_add(sha1_rol($a, 5), sha1_ft($j, $b, $c, $d)), sha1_safe_add(sha1_safe_add($e, $w[$j]), sha1_kt($j)));
	           $e = $d;
	           $d = $c;
	           $c = sha1_rol($b, 30);
	           $b = $a;
	           $a = $t;
	       }
	      
	       $a = sha1_safe_add($a, $olda);
	       $b = sha1_safe_add($b, $oldb);
	       $c = sha1_safe_add($c, $oldc);
	       $d = sha1_safe_add($d, $oldd);
	       $e = sha1_safe_add($e, $olde);
	   }
	  
	   return sprintf('%08x%08x%08x%08x%08x', $a, $b, $c, $d, $e);
	}

	/**
	 * @connect_module_class_name JCCRedirectLink
	 * @package DynamicModules
	 * @subpackage Payment
	 */
	class JCCRedirectLink extends PaymentModule {

		var $type = PAYMTD_TYPE_CC;
		var $language = 'eng';
		
		function _getCaptureOptions(){
			
			$Options = array();
			$Options[] = array(
				'title' => JCCRL_TXT_CAPTURE_A,
				'value' => 'A'
				);
			$Options[] = array(
				'title' => JCCRL_TXT_CAPTURE_M,
				'value' => 'M'
				);
			return $Options;
		}
		
		function _initVars(){
			
			$this->title = JCCRL_TTL;
			$this->description = JCCRL_DSCR;
			$this->sort_order = 1;
			
			$this->Settings = array( 
					'CONF_PAYMENTMODULE_JCCRL_URL',
					'CONF_PAYMENTMODULE_JCCRL_MERID',
					'CONF_PAYMENTMODULE_JCCRL_MERPWD',
					'CONF_PAYMENTMODULE_JCCRL_ACQID',
					'CONF_PAYMENTMODULE_JCCRL_CAPTURE',
					'CONF_PAYMENTMODULE_JCCRL_CUR_SHOP',
					'CONF_PAYMENTMODULE_JCCRL_CUR_ISONUM',
				);
		}
	
		function _initSettingFields(){
	
			$this->SettingsFields['CONF_PAYMENTMODULE_JCCRL_URL'] = array(
				'settings_value' 		=> 'https://test.jccsecure.com/SENTRY/PaymentGateway/Application/RedirectLink.aspx', 
				'settings_title' 			=> JCCRL_CFG_URL_TTL, 
				'settings_description' 	=> JCCRL_CFG_URL_DSCR, 
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PAYMENTMODULE_JCCRL_MERID'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> JCCRL_CFG_MERID_TTL, 
				'settings_description' 	=> JCCRL_CFG_MERID_DSCR, 
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PAYMENTMODULE_JCCRL_MERPWD'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> JCCRL_CFG_MERPWD_TTL, 
				'settings_description' 	=> JCCRL_CFG_MERPWD_DSCR, 
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PAYMENTMODULE_JCCRL_ACQID'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> JCCRL_CFG_ACQID_TTL, 
				'settings_description' 	=> JCCRL_CFG_ACQID_DSCR, 
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PAYMENTMODULE_JCCRL_CAPTURE'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> JCCRL_CFG_CAPTURE_TTL, 
				'settings_description' 	=> JCCRL_CFG_CAPTURE_DSCR, 
				'settings_html_function' 	=> 'setting_RADIOGROUP(JCCRedirectLink::_getCaptureOptions(),', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PAYMENTMODULE_JCCRL_CUR_SHOP'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> JCCRL_CFG_CUR_SHOP_TTL, 
				'settings_description' 	=> JCCRL_CFG_CUR_SHOP_DSCR, 
				'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
				'sort_order' 			=> 1,
			);
			$this->SettingsFields['CONF_PAYMENTMODULE_JCCRL_CUR_ISONUM'] = array(
				'settings_value' 		=> '', 
				'settings_title' 			=> JCCRL_CFG_CUR_ISONUM_TTL, 
				'settings_description' 	=> JCCRL_CFG_CUR_ISONUM_DSCR, 
				'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
				'sort_order' 			=> 1,
			);
		}
	
		function after_processing_html( $orderID, $active = true ){
			
			$order = ordGetOrder( $orderID );
			
			$currency = currGetCurrencyByID($this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_CUR_SHOP'));
			if(!is_null($currency)){
				
				$order_amount = round(100*$order['order_amount'] * $order['currency_value'])/100;
			}else{
				
				$order_amount = round(100*$order['order_amount'] * $currency['currency_value'])/100;
			}
			$order_amount = sprintf('%\'012s',$order_amount*100);
					
			$Signature = base64_encode(_sha1(
				$this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_MERPWD').
				$this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_MERID').
				$this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_ACQID').
				$orderID.$order_amount.$this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_CUR_ISONUM'),true));

			$res = '
				<table width="100%">
				<tr>
					<td align="center">
					<form id="jcc_form" method="post" action="'.xHtmlSpecialChars($this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_URL')).'">
						<input type="hidden" name="Version" value="1.0.0" />
						<input type="hidden" name="MerID" value="'.xHtmlSpecialChars($this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_MERID')).'" />
						<input type="hidden" name="AcqID" value="'.xHtmlSpecialChars($this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_ACQID')).'" />
						<input type="hidden" name="MerRespURL" value="'.xHtmlSpecialChars(str_replace('http://','https://',CONF_FULL_SHOP_URL.'jcc_response_handler.php')).'" />
						<input type="hidden" name="PurchaseAmt" value="'.xHtmlSpecialChars($order_amount).'" />
						<input type="hidden" name="PurchaseCurrency" value="'.xHtmlSpecialChars($this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_CUR_ISONUM')).'" />
						<input type="hidden" name="PurchaseCurrencyExponent" value="2" />
						<input type="hidden" name="OrderID" value="'.xHtmlSpecialChars($orderID).'" />
						<input type="hidden" name="SignatureMethod" value="SHA1" />
						<input type="hidden" name="Signature" value="'.xHtmlSpecialChars($Signature).'" />
						<input type="hidden" name="CaptureFlag" value="'.xHtmlSpecialChars($this->_getSettingValue('CONF_PAYMENTMODULE_JCCRL_CAPTURE')).'" />
						<input type="submit" value="'.xHtmlSpecialChars(JCCRL_SUBMIT_BTN).'" />
					</form>'.
					($active?
					'<script type="text/javascript"><!--
						document.getElementById("jcc_form").submit();
					//-->
					</script>':'').
					'</td>
				</tr>
				</table>';
			
			return $res;
		}
	}
?>