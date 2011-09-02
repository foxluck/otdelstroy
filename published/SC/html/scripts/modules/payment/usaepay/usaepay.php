<?php
// USA ePay PHP Library.
//	v1.4.3 - November 17th, 2003
//
// 	Copyright (c) 2001-2005 USAePay
//	Written by Tim McEwen (tim@usaepay.com)
//

define("USAEPAY_VERSION", "1.4.3");


/**
 * USAePay Transaction Class
 *
 */
class umTransaction {
	
	// Required for all transactions
	var $key;			// Source key
	var $pin;     		// Source pin (optional)
	var $amount;		// the entire amount that will be charged to the customers card 
						// (including tax, shipping, etc)
	var $invoice;   	// invoice number.  must be unique.  limited to 10 digits.  use orderid if you need longer. 
	
	// Required for Commercial Card support
	var $ponum;			// Purchase Order Number
	var $tax;			// Tax
	
	// Amount details (optional)
	var $tip; 			// Tip
	var $shipping;		// Shipping charge
	var $discount; 		// Discount amount (ie gift certificate or coupon code)
	var $subtotal; 		// if subtotal is set, then 
						// subtotal + tip + shipping - discount + tax must equal amount 
						// or the transaction will be declined.  If subtotal is left blank
						// then it will be ignored
	
	// Required Fields for Card Not Present transacitons (Ecommerce)
	var $card;			// card number, no dashes, no spaces
	var $exp;			// expiration date 4 digits no /
	var $cardholder; 	// name of card holder
	var $street;		// street address
	var $zip;			// zip code
	
	// Fields for Card Present (POS) 
	var $magstripe;  	// mag stripe data.  can be either Track 1, Track2  or  Both  (Required if card,exp,cardholder,street and zip aren't filled in)
	var $cardpresent;   // Must be set to true if processing a card present transaction  (Default is false)
	var $termtype;  	// The type of terminal being used:  Optons are  POS - cash register, StandAlone - self service terminal,  Unattended - ie gas pump, Unkown  (Default:  Unknown)
	var $magsupport;  	// Support for mag stripe reader:   yes, no, unknown  (default is unknown unless magstripe has been sent)

	// fields required for check transactions
	var $account;		// bank account number
	var $routing;		// bank routing number
	var $ssn;			// social security number
	var $dlnum;			// drivers license number (required if not using ssn)
	var $dlstate;		// drivers license issuing state

	// Option parameters
	var $origauthcode;	// required if running postauth transaction.
	var $command;		// type of command to run; Possible values are: 
						// sale, credit, void, preauth, postauth, check and checkcredit. 
						// Default is sale.
	var $orderid;		// Unique order identifier.  This field can be used to reference 
						// the order for which this transaction corresponds to. This field 
						// can contain up to 64 characters and should be used instead of 
						// UMinvoice when orderids longer that 10 digits are needed.
	var $custid;   // Alpha-numeric id that uniquely identifies the customer.
	var $description;	// description of charge
	var $cvv2;			// cvv2 code
	var $custemail;		// customers email address
	var $custreceipt;	// send customer a receipt
	var $ip;			// ip address of remote host
	var $testmode;		// test transaction but don't process it
	var $timeout;       // transaction timeout.  defaults to 45 seconds
	var $gatewayurl;   	// url for the gateway
	var $ignoresslcerterrors;  // Bypasses ssl certificate errors.  It is highly recommended that you do not use this option.  Fix your openssl installation instead!
	var $cabundle;      // manually specify location of root ca bundle (useful of root ca is not in default location)
		
	// Card Authorization - Verified By Visa and Mastercard SecureCode
	var $cardauth;    	// enable card authentication
	var $pares; 		// 
	
	// Third Party Card Authorization
	var $xid;
	var $cavv;
	var $eci;

	// Recurring Billing
	var $recurring;		//  Save transaction as a recurring transaction:  yes/no
	var $schedule;		//  How often to run transaction: daily, weekly, biweekly, monthly, bimonthly, quarterly, annually.  Default is monthly.
	var $numleft; 		//  The number of times to run. Either a number or * for unlimited.  Default is unlimited.
	var $start;			//  When to start the schedule.  Default is tomorrow.  Must be in YYYYMMDD  format.
	var $end;			//  When to stop running transactions. Default is to run forever.  If both end and numleft are specified, transaction will stop when the ealiest condition is met.
	var $billamount;	//  Optional recurring billing amount.  If not specified, the amount field will be used for future recurring billing payments

	// Billing Fields
	var $billfname;
	var $billlname;
	var $billcompany;
	var $billstreet;
	var $billstreet2;
	var $billcity;
	var $billstate;
	var $billzip;
	var $billcountry;
	var $billphone;
	var $email;
	var $fax;
	var $website;

	// Shipping Fields
	var $shipfname;
	var $shiplname;
	var $shipcompany;
	var $shipstreet;
	var $shipstreet2;
	var $shipcity;
	var $shipstate;
	var $shipzip;
	var $shipcountry;
	var $shipphone;

	var $software; // Allows developers to identify their application to the gateway (for troubleshooting purposes)

	
	// response fields
	var $rawresult;		// raw result from gateway
	var $result;		// full result:  Approved, Declined, Error
	var $resultcode; 	// abreviated result code: A D E
	var $authcode;		// authorization code
	var $refnum;		// reference number
	var $batch;		// batch number
	var $avs_result;		// avs result
	var $avs_result_code;		// avs result
	var $avs;  					// obsolete avs result
	var $cvv2_result;		// cvv2 result
	var $cvv2_result_code;		// cvv2 result
	
	// Cardinal Response Fields
	var $acsurl;	// card auth url
	var $pareq;		// card auth request
	var $cctransid; // cardinal transid

	
	// Errors Response Feilds
	var $error; 		// error message if result is an error
	var $errorcode; 	// numerical error code
	var $blank;			// blank response
	var $curlerror; 	// curl error
	
	
	// Constructor
	function umTransaction()
	{
		// Set default values.
		$this->command="sale";
		$this->result="Error";
		$this->resultcode="E";
		$this->error="Transaction not processed yet.";
		$this->timeout=45;
		$this->cardpresent=false;
		if(isset($_SERVER['REMOTE_ADDR'])) $this->ip=$_SERVER['REMOTE_ADDR'];
		$this->software="USAePay PHP API v" . USAEPAY_VERSION;

	}

	/**
	 * Verify that all required data has been set
	 *
	 * @return string
	 */
	function CheckData()
	{
		if(!$this->key) return "Source Key is required";
		if($this->command=="capture")
		{
			if(!$this->refnum) return "Reference Number is required";		
		}  else {
			if($this->command=="check" || $this->command=="checkcredit") {
					if(!$this->account) return "Account Number is required";
					if(!$this->routing) return "Routing Number is required";
			} else {
				if(!$this->magstripe) {
					if(!$this->card) return "Credit Card Number is required";
					if(!$this->exp) return "Expiration Date is required";
				}
			}
			$this->amount=ereg_replace("[^[:digit:].]","",$this->amount);
			if(!$this->amount) return "Amount is required";
			if(!$this->invoice && !$this->orderid) return "Invoice number or Order ID is required";
			if(!$this->magstripe) {
				if(!$this->cardholder) return "Cardholder Name is required";
				if(!$this->street) return "Street Address is required";
				if(!$this->zip) return "Zipcode is required";
			}
		}
		return 0;		
	}
	
	/**
	 * Send transaction to the USAePay Gateway and parse response
	 *
	 * @return boolean
	 */
	function Process()
	{
		// check that we have the needed data
		$tmp=$this->CheckData();
		if($tmp)
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error=$tmp;
			$this->errorcode=10129;
			return false;
		}
		
		
		// check to make sure we have curl
		if(!function_exists("curl_version"))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Libary Error: CURL support not found";
			$this->errorcode=10130;
			return false;
		}
		
		//init the connection
		$ch = curl_init(($this->gatewayurl?$this->gatewayurl:"https://www.usaepay.com/secure/gate.php"));
		if(!is_resource($ch))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Libary Error: Unable to initialize CURL ($ch)";
			$this->errorcode=10131;
			return false;			
		}
		
		// set some options for the connection
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_TIMEOUT, ($this->timeout>0?$this->timeout:45));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

		// Bypass ssl errors - A VERY BAD IDEA
		if($this->ignoresslcerterrors)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		
		// apply custom ca bundle location
		if($this->cabundle)
		{	
			curl_setopt($ch, CURLOPT_CAINFO, $this->cabundle);				
		}
		
		// format the data
		$data = "UMkey=" . rawurlencode($this->key) . "&" . 
			"UMcommand=" . rawurlencode($this->command) . "&" .
			"UMauthCode=" . rawurlencode($this->origauthcode) . "&" .
			"UMcard=" . rawurlencode($this->card) . "&" .
			"UMexpir=" . rawurlencode($this->exp) . "&" .
			"UMbillamount=" . rawurlencode($this->billamount) . "&" . 
			"UMamount=" . rawurlencode($this->amount) . "&" . 
			"UMinvoice=" . rawurlencode($this->invoice) . "&" . 
			"UMorderid=" . rawurlencode($this->orderid) . "&" . 
			"UMponum=" . rawurlencode($this->ponum) . "&" . 
			"UMtax=" . rawurlencode($this->tax) . "&" . 
			"UMtip=" . rawurlencode($this->tip) . "&" . 
			"UMshipping=" . rawurlencode($this->shipping) . "&" . 
			"UMdiscount=" . rawurlencode($this->discount) . "&" . 
			"UMsubtotal=" . rawurlencode($this->subtotal) . "&" . 
			"UMname=" . rawurlencode($this->cardholder) . "&" .
			"UMstreet=" . rawurlencode($this->street) . "&" . 
			"UMzip=" . rawurlencode($this->zip) . "&" .
			"UMdescription=" . rawurlencode($this->description) . "&" .
			"UMcvv2=" . rawurlencode($this->cvv2) . "&" .
			"UMip=" . rawurlencode($this->ip) . "&" .
			"UMtestmode=" . rawurlencode($this->testmode) . "&" .
			"UMcustemail=" . rawurlencode($this->custemail) . "&" .
			"UMcustreceipt=" . rawurlencode($this->custreceipt) . "&" .
			"UMrouting=" . rawurlencode($this->routing) . "&" .
			"UMaccount=" . rawurlencode($this->account) . "&" .
			"UMssn=" . rawurlencode($this->ssn) . "&" .
			"UMdlstate=" . rawurlencode($this->dlstate) . "&" .
			"UMdlnum=" . rawurlencode($this->dlnum) . "&" .
			"UMrecurring=" . rawurlencode($this->recurring) . "&" .
			"UMbillamount=" . rawurlencode($this->billamount) . "&" .
			"UMschedule=" . rawurlencode($this->schedule) . "&" .
			"UMnumleft=" . rawurlencode($this->numleft) . "&" .
			"UMstart=" . rawurlencode($this->start) . "&" .
			"UMexpire=" . rawurlencode($this->end) . "&" .
			"UMbillfname=" . rawurlencode($this->billfname) . "&" .
			"UMbilllname=" . rawurlencode($this->billlname) . "&" .
			"UMbillcompany=" . rawurlencode($this->billcompany) . "&" .
			"UMbillstreet=" . rawurlencode($this->billstreet) . "&" .
			"UMbillstreet2=" . rawurlencode($this->billstreet2) . "&" .
			"UMbillcity=" . rawurlencode($this->billcity) . "&" .
			"UMbillstate=" . rawurlencode($this->billstate) . "&" .
			"UMbillzip=" . rawurlencode($this->billzip) . "&" .
			"UMbillcountry=" . rawurlencode($this->billcountry) . "&" .
			"UMbillphone=" . rawurlencode($this->billphone) . "&" .
			"UMemail=" . rawurlencode($this->email) . "&" .
			"UMfax=" . rawurlencode($this->fax) . "&" .
			"UMwebsite=" . rawurlencode($this->website) . "&" .
			"UMshipfname=" . rawurlencode($this->shipfname) . "&" .
			"UMshiplname=" . rawurlencode($this->shiplname) . "&" .
			"UMshipcompany=" . rawurlencode($this->shipcompany) . "&" .
			"UMshipstreet=" . rawurlencode($this->shipstreet) . "&" .
			"UMshipstreet2=" . rawurlencode($this->shipstreet2) . "&" .
			"UMshipcity=" . rawurlencode($this->shipcity) . "&" .
			"UMshipstate=" . rawurlencode($this->shipstate) . "&" .
			"UMshipzip=" . rawurlencode($this->shipzip) . "&" .
			"UMshipcountry=" . rawurlencode($this->shipcountry) . "&" .
			"UMshipphone=" . rawurlencode($this->shipphone) . "&" . 
			"UMcardauth=" . rawurlencode($this->cardauth) . "&" . 
			"UMpares=" . rawurlencode($this->pares) . "&" . 
			"UMxid=" . rawurlencode($this->xid) . "&" . 
			"UMcavv=" . rawurlencode($this->cavv) . "&" . 
			"UMeci=" . rawurlencode($this->eci) . "&" . 
			"UMcustid=" . rawurlencode($this->custid) . "&" . 
			"UMcardpresent=" . ($this->cardpresent?"1":"0") . "&" . 
			"UMmagstripe=" . rawurlencode($this->magstripe) . "&" . 
			"UMtermtype=" . rawurlencode($this->termtype) . "&" . 
			"UMmagsupport=" . rawurlencode($this->magsupport) . "&" . 
			"UMsoftware=" . rawurlencode($this->software) . "&" . 
			"UMrefNum=" . rawurlencode($this->refnum);

		// Append md5hash if pin has been set.
		if($this->pin)
		{
			$key=mktime();
			$data.="&UMmd5hash=" . rawurlencode(md5($this->command . ":" . $this->pin . ":" . $this->amount . ":" . $this->invoice . ":" . $key)) . "&UMmd5key=" . $key;
		}
		
		// attach the data
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		
		// run the transfer
		$result=curl_exec ($ch);
		
		//get the result and parse it for the response line.
		if(!strlen($result))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Error reading from card processing gateway.";
			$this->errorcode=10132;
			$this->blank=1;
			$this->curlerror=curl_error($ch);
			curl_close ($ch);
			return false;			
		}
		curl_close ($ch);
		$this->rawresult=$result;

		if(!$result) {
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Blank response from card processing gateway.";
			$this->errorcode=10132;
			return false;			
		}
		
		// result will be on the last line of the return
		$tmp=split("\n",$result);
		$result=$tmp[count($tmp)-1];		
		
		// result is in urlencoded format, parse into an array
		parse_str($result,$tmp);

		// check to make sure we received the correct fields
		if(!isset($tmp["UMversion"]) || !isset($tmp["UMstatus"]))
		{
			$this->result="Error";
			$this->resultcode="E";
			$this->error="Error parsing data from card processing gateway.";
			$this->errorcode=10132;
			return false;			
		}

		// Store results		
		$this->result=(isset($tmp["UMstatus"])?$tmp["UMstatus"]:"Error");	
		$this->resultcode=(isset($tmp["UMresult"])?$tmp["UMresult"]:"E");	
		$this->authcode=(isset($tmp["UMauthCode"])?$tmp["UMauthCode"]:"");
		$this->refnum=(isset($tmp["UMrefNum"])?$tmp["UMrefNum"]:"");
		$this->batch=(isset($tmp["UMbatch"])?$tmp["UMbatch"]:"");
		$this->avs_result=(isset($tmp["UMavsResult"])?$tmp["UMavsResult"]:"");
		$this->avs_result_code=(isset($tmp["UMavsResultCode"])?$tmp["UMavsResultCode"]:"");
		$this->cvv2_result=(isset($tmp["UMcvv2Result"])?$tmp["UMcvv2Result"]:"");
		$this->cvv2_result_code=(isset($tmp["UMcvv2ResultCode"])?$tmp["UMcvv2ResultCode"]:"");
		$this->error=(isset($tmp["UMerror"])?$tmp["UMerror"]:"");
		$this->errorcode=(isset($tmp["UMerrorcode"])?$tmp["UMerrorcode"]:"10132");

		// Obsolete variable (for backward compatibility) At some point they will no longer be set.
		$this->avs=(isset($tmp["UMavsResult"])?$tmp["UMavsResult"]:"");
		$this->cvv2=(isset($tmp["UMcvv2Result"])?$tmp["UMcvv2Result"]:"");

		
		if(isset($tmp["UMcctransid"])) $this->cctransid=$tmp["UMcctransid"];
		if(isset($tmp["UMacsurl"])) $this->acsurl=$tmp["UMacsurl"];
		if(isset($tmp["UMpayload"])) $this->pareq=$tmp["UMpayload"];
		
		if($this->resultcode == "A") return true;
		return false;
		
	}
	
	/**
	 * Verify Proper Installation of USAePay class and required PHP Support
	 *
	 */
	function Test()
	{
		$curl_version=false;
		?>
		<table border=1>
		<tr><th><b>Test</b></th><th><b>Result</b></th></tr>
		<tr><td valign="Top">Checking PHP Version</td>
		<td valign="top"><?php
			if(version_compare(phpversion(),"4.3.0")) {
				?><font color="green">Ok</font><br>
				PHP version <?php echo phpversion()?> on <?php echo PHP_OS?> detected. 
				<?php				
			} else {
				?><font color="red">Warning</font><br>
				PHP version <?php echo phpversion()?> detected. It is recommended that you
				upgrade to the most recent release of PHP.
				<?php				
			}
		?></td></tr>
		<tr><td valign="Top">Checking CURL Extension</td>
		<td valign="top"><?php
			if(function_exists("curl_version")) {	
				$tmp=curl_version();
				// PHP 5 returns an array,  version 4 returns a string
				if(is_array($tmp)) { 
					$curl_version=$tmp["version"];
					$curl_ssl_version=$tmp["ssl_version"];					
				} else {
					$tmp=explode(" ", $tmp);
					foreach($tmp as $piece)
					{
						list($lib,$version)=explode("/",$piece);
						if(stristr($lib,"curl")) $curl_version=$version;
						elseif(stristr($lib,"ssl")) $curl_ssl_version=$version;
					}
					
				}
				?><font color="green">Ok</font><br>
				Curl Extension (<?php echo $curl_version?>) detected.
				<?php
			} else {
				?><font color="red">Error</font><br>
				Your PHP installation does not include the Curl Extension. You must either enable the curl
				extension in your php.ini file by removing the semi-colon from the line ";extension=php_curl.dll" or recompile
				php with the configuration flag: "--with-curl"
				<?php
			}
		?>
		</td></tr>
		<tr><td valign="Top">Checking CURL SSL Support</td>
		<td valign="top"><?php
			if($curl_ssl_version) {	
				?><font color="green">Ok</font><br>
				SSL Version (<?php echo $curl_ssl_version?>) detected.
				<?php
			} else {
				?><font color="red">Error</font><br>
				It appears that your curl installation does not include support for the HTTPS (ssl) protocal
				Proper SSL installation is required to communicate with the USAePay gateway securely.  Please recompile curl
				with SSL support.
				<?php
			}
		?>					
		</td></tr>
		<tr><td valign="Top">Checking Communication with the Gateway</td>
		<td valign="top"><?php
			$ssl_failed=true;
			if(!$curl_version) {
				?><font color="red">Error</font><br>
				No curl support
				<?php				
			} else {
				$ch = curl_init(($this->gatewayurl?$this->gatewayurl:"https://www.usaepay.com/secure/gate.php") . "?VersionCheck=1&UMsoftware=" . rawurlencode($this->software));
				if(!$ch) {
					?><font color="red">Error</font><br>
					Curl failed to connect to server:  (NULL $ch returned)
					<?php
				} else {
					
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					if($this->cabundle) curl_setopt($ch, CURLOPT_CAINFO, $this->cabundle);				

					$result=curl_exec($ch);
					parse_str($result, $return);
					if($return["UMversion"]) {
						$ssl_failed=false;
						?><font color="green">Ok</font><br>
						Successfully connected to the gateway.  Detected version (<?php echo $return["UMversion"]?>) of the USAePay gateway API.
						<?php						
					} else {
						$ch = curl_init(($this->gatewayurl?$this->gatewayurl:"https://www.usaepay.com/secure/gate.php") . "?VersionCheck=1&UMsoftware=" . rawurlencode($this->software));
						if($this->cabundle) curl_setopt($ch, CURLOPT_CAINFO, $this->cabundle);				
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);				
						$result=curl_exec($ch);
						parse_str($result, $return);
						if($return["UMversion"]) {
							?><font color="red">Warning</font><br>
							
							Successfully connected to the gateway but could not verify SSL certificate.  This usually indicates that you
							do not have curl set up with the proper root CA certificate.  It is recommended that you install an up to date 
							root ca bundle.  As a <b>temporary</b> work around you may disable the ssl cert check by adding the following to your script:<br><br><tt>
							$tran->ignoresslcerterrors=true;<br>
							<br></tt>
							<?php						
							$ch = curl_init("https://www.verisign.com");
							if($this->cabundle) curl_setopt($ch, CURLOPT_CAINFO, $this->cabundle);				
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							$result=curl_exec($ch);
							if(strlen($result)) {
								?>
								SSL certificate for VeriSign was validated sucessfully. This would indicate that you have a root CA bundle installed but that it
								is outdated. See below.
								<?php
							} else {
								?>
								Unable to verify SSL certificate for VeriSign.  This would indicate that you do not have a root CA bundle installed. See below.
								<?php
							}
												
							
							
						} else {
							$ch = curl_init("https://216.133.244.70/secure/gate.php?VersionCheck=1&UMsoftware=" . rawurlencode($this->software));
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
							$result=curl_exec($ch);
							parse_str($result, $return);
							if($return["UMversion"]) {
								?><font color="red">Warning</font><br>
								
								Successfully connected to the gateway using a static IP but could not connect using hostname. This would indicate
								that you do not have DNS setup correctly.  Please correct your server configuration to include a valid
								DNS server. As a temporary work around you may add the following to your script:<br><br><tt>
								$tran->ignoresslcerterrors=true;<br>
								$tran->gatewayurl="https://216.133.244.70/gate.php";<br>
								<br></tt>
								<?php
							} else {
								?><font color="red">Failed</font><br>
								Unable to establish connection to the USAePay Gateway.  It is possible that you are firewalling
								outbound traffic from your server.  To correct this problem,  you must add an allow entry in
								your firewall for ip <?php echo gethostbyname("www.usaepay.com")?> on port 443;
								<?php								
								
							}
							
						}
						
					}
				}				
			}
		?>					
		</td></tr>
		<?php if($ssl_failed) {?>
		
		<tr><td valign="Top">Looking for root CA Bundle</td>
		<td valign="top"><?php
			if(strstr(PHP_OS,"WIN")!==false) {
				foreach(array('c:\windows\ca-bundle.crt','c:\windows\curl-ca-bundle.crt') as $certpath)
				{
					if(is_file($certpath)) break;
					else unset($certpath);
				}
				
				if(!$certpath) $certpath="c:\windows\curl-ca-bundle.crt";
			} else {
				
				foreach(array("/usr/share/curl/curl-ca-bundle.crt","/usr/local/share/curl/curl-ca-bundle.crt","/etc/curl/curl-ca-bundle.crt") as $certpath)
				{
					if(is_file($certpath)) break;
					else unset($certpath);
				}
				
				if(!$certpath) $certpath="/usr/share/curl/curl-ca-bundle.crt";
			}
		
		
			if(is_readable($certpath)) {	
				?><font color="green">Ok</font><br>
				A root CA bundle was found in "<?php echo $certpath?>".  Since the above test failed,  its possible that curl is not
				correctly configured to use this bundle.  You can correct the problem by add the following code to your script:<br><br><tt>
				$tran->cabundle='<?php echo $certpath?>';
				</tt>
				<br><br>
				It is also possible that your root CA bundle is out dated. Your root CA bundle was last updated <?php echo date("m/d/y", filemtime($certpath))?>.
				You can download a new file from:
				<a href="https://www.usaepay.com/topics/curl-ca-bundle.crt">https://www.usaepay.com/topics/curl-ca-bundle.crt</a>				
				</tt>  
				<?php
			} else {
				?><font color="red">Error</font><br>
				Unable to locate your root CA bundle file. You can correct this by downloading a bundle file from: 
				<a href="https://www.usaepay.com/topics/curl-ca-bundle.crt">https://www.usaepay.com/topics/curl-ca-bundle.crt</a>
				Once downloaded save this file to: <?php echo $certpath?> You may also need to add the following code:<br><br><tt>
				$tran->cabundle='<?php echo $certpath?>';
				</tt> 
				<br><br>
				<?php
			}
		?>					
		</td></tr>
		<?php } ?>
		
		</table>
		<?php
	}
}

//  umVerifyCreditCardNumber
//  Validates a credit card and returns the type of card.
//
//	Card Types:
//		 1	Mastercard
//		 2	Visa
//		 3	American Express
//		 4	Diners Club/Carte Blanche
//		10	Discover
//		20	enRoute	
//		28	JCB	


/**
 * Evaluates a creditcard number and if valid, returns the card type code
 *
 * @param ccnum string
 * @return int
 */
function umVerifyCreditCardNumber($ccnum)
{
	global $umErrStr;
	
	
	//okay lets do the stupid
	$ccnum=str_replace("-","",$ccnum);
	$ccnum=str_replace(" ","",$ccnum);
	$ccnum=str_replace("/","",$ccnum);

	if(!ereg("^[[:digit:]]{1,200}$", $ccnum)) {$umErrStr="Cardnumber contains characters that are not numbers";  return 0;}
	if(!ereg("^[[:digit:]]{13,16}$", $ccnum)) {$umErrStr="Cardnumber is not between 13 and 16 digits long";  return 0;}


	// Run Luhn Mod-10 to ensure proper check digit
	$total=0;
	$y=0;
	for($i=strlen($ccnum)-1; $i >= 0; $i--)
	{
		if($y==1) $y=2; else $y=1;         //multiply every other digit by 2
		$tmp=substr($ccnum,$i,1)*$y;
		if($tmp >9) $tmp=substr($tmp,0,1) + substr($tmp,1,1);
		$total+=$tmp;
	}
	if($total%10) {$umErrStr="Cardnumber fails Luhn Mod-10 check digit";  return 0;}


	switch(substr($ccnum,0,1))
	{
		case 2: //enRoute - First four digits must be 2014 or 2149. Only valid length is 15 digits
			if((substr($ccnum,0,4) == "2014" || substr($ccnum,0,4) == "2149") && strlen($ccnum) == 15) return 20;
			break;
		case 3: //JCB - Um yuck, read the if statement below, and oh by the way 300 through 309 overlaps with diners club.  bummer.
			if((substr($ccnum,0,4) == "3088" ||	substr($ccnum,0,4) == "3096" || substr($ccnum,0,4) == "3112" || substr($ccnum,0,4) == "3158" ||	substr($ccnum,0,4) == "3337" ||
				(substr($ccnum,0,8) >= "35280000" ||substr($ccnum,0,8) <= "358999999")) && strlen($ccnum)==16)  
			{
				return 28;
			} else { 
				switch(substr($ccnum,1,1))
				{
					case 4:
					case 7: // American Express - First digit must be 3 and second digit 4 or 7. Only Valid length is 15
						if(strlen($ccnum) == 15) return 3;
						break;
		     			case 0:
					case 6:
					case 8: //Diners Club/Carte Blanche - First digit must be 3 and second digit 0, 6 or 8. Only valid length is 14
						if(strlen($ccnum) == 14) return 4;
						break;
				}
			}
			break;
		case 4: // Visa - First digit must be a 4 and length must be either 13 or 16 digits.
			if(strlen($ccnum) == 13 || strlen($ccnum) == 16)
			{
				 return 2;
			}
			break;

		case 5: // Mastercard - First digit must be a 5 and second digit must be int the range 1 to 5 inclusive. Only valid length is 16
			if((substr($ccnum,1,1) >=1 && substr($ccnum,1,1) <=5) && strlen($ccnum) == 16)
			{
				return 1;
			}
			break;
   	case 6: // Discover - First four digits must be 6011. Only valid length is 16 digits.
			if(substr($ccnum,0,4) == "6011" && strlen($ccnum) == 16) return 10;
	}


	// couldn't match a card profile. time to call it quits and go home.  this goose is cooked.
	$umErrStr="Cardnumber did not match any known creditcard profiles";
	return 0;
}


?>