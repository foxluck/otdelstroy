<?php
	
	class _SecurePayCharge{
		
		####################################
		## Class Properties
		####################################
		
		var $custName; 			#The name on the credit card.		
		var $street; 			#The street address in the billing address for the credit card.		
		var $City; 				#The City in the billing address for the credit card.		
		var $state; 			#The State in the billing address for the credit card.		
		var $zip;				#The Zip code in the billing address for the credit card.		
		var $country;			#The Country in the billing address for the credit card.
		var $avsreq; 			#AVS type being requested.		
		var $custEmail; 		#The email of the customer making the purchase.
		var $merchID; 			#A unique Merchant identifier assigned to the Merchant by SecurePay.Com.		
		
		var $ccNum; 			#The account number of the credit card. No dashes or spaces.		
		var $month; 			#The 2 digit designation for the month of expiration on the card.
									#Example: 01 is January, 12 is December.		
		var $year;   			#The 2 digit designation for the year of expiration on the card.
									#Example: 00 is 2000, 01 is 2001, 02 is 2002.
		var $recurring;			#Add this transaction to recurring database. Valid values 'YES' or 'NO'
		var $recamount;			#Recurring Amount, as it may be different that initial charge
									#Follow this format xxxx.xx. No dollar sign eg 32.24
		var $timeframe;			#Time frame for recurring activity:
		var $cvv2;
								/*	
									Values:  
											  "MONTH" - monthly [default]
											  "WEEK" - weekly
											  "BIMONTH" - twice a month
											  "QUARTER"  - once a quarter
											  "6MONTH" - semi annually
											  "YEAR" - annually
											  "1AND15" - first and fifteenth of every month
											  "MANUAL" - save this transaction and you do it when you want
									
									Any time frame value that does not match the above list
									is interpreted as a monthly recurring entry.
							    */
		
		
		var $transType; 		#The type of transaction being processed. This variable can have two values. 
									/**************************************************************
										transType Explanation
											SALE - Indicates a charge to be placed against 
													a credit card account.
			
											CREDIT - Indicates a refund or "credit" to be 
													placed against a credit card account.
			
											PREAUTH - Indicates a pre-authorization on a 
													Credit Card. This is a temporary block on
													an Amount submitted by the Merchant. This 
													is not a qualified transaction and if not 
													closed with a FORCE transaction it will 
													release the block after 5-7 days depending
													on the card issuer. This can have a negative 
													impact on the funds availability of the 
													card holder and should be used appropriately.
			
											FORCE - Indicates a closure of a previously 
													PREAUTH (pre-authorized) transaction. A 
													FORCE  requires all data fields submitted 
													with the original pre-authorization plus the 
													additional variable named APPROVNUMBER. The 
													value of this field is the original 
													transaction Approval Number returned as the 
													value of Approv_Num.
				
											VOID - Indicates a reversal of a transaction conducted
													on the same business day. An additional variable 
													must be passed with a VOID transaction. That 
													variable is the original record number assigned 
													to the transaction. This number is passed with 
													each successful transaction as the "VoidRecNum".
											**************************************************************/

		var $ccMethod; 			#The type of transaction being presented. Either "DataEntry" or "Swiped"
		var $amount; 			#The amount of the charge to be processed. Follow this format xxxx.xx. No dollar sign eg 32.24
		var $swipeData; 		#SwipeData is the Magnetic Stripe Data from Track 1 on the Credit Card. Blank if not swiped transaction.


		#OPTIONAL
		var $comment1; 			#An optional field used by the Merchant to aid in managing transactions.
		var $comment2; 			#An second optional field used by the Merchant to aid in managing transactions.
		var $voidRecNum; 		#The VoidRecNum is passed back with the Approval Code and other return variables on each approved transaction. If the transaction is to be reversed on the same business day as the original transaction a VOID transaction type may be initiated provided the original Record Number is passed to SecurePay with all other original transaction data. The value of the Record Number to be passed to SecurePay is the value of the "VoidRecNum" assigned to the original transaction. The name of both the receiving variable and send variable is the same.

		var $origApprovNumber; 	#This is passed as a required variable when conducting a FORCE transaction. The value of the variable ApprovNumber is the original Approval Code from the pre-authorization. It is passed back in the response string from the transaction request sent to the COM object.

		
		#RETURN Variables
		
		var $returnCode;       	#Y= approved, N= host decline
		var $approvNum;        	#The Approv_Num can have 2 possible responses
							        #1. "XXXXXX", the Approval number of the transaction.
									#2. "NONE", When a transaction is declined.
		var $cardResponse; 		#Verbose text from processor: APPROVED, INVALID CARD NUMBER. 
								#The Card_Response can have many possible values. These are 
								#usually verbose descriptions of why a card was declined or 
								#the word "Approved" when transactions are accepted for processing.
		var $avsResponse;      #See documentation.
		var $recordNumber;     #Internal Securepay record number of transaciton used for 
									#transaction identification purposes. Uniquely 
									#identifies transaction.
		
		
		var $_errorNo;
		var $_errorData;
		var $_timeout;
		var $_debug;
		var $_sURL;
		

		####################################
		## END - Class Properties
		####################################
		
		/*
			Function: Constructor
			Purpose: Strat class off with proper settings
			Description: Pass any valid numeric value to set a timeout
							value other than 120 seconds
			Parameters: newTimeOut - Timeout of HTTP call
				
		*/
		function SecurePayCharge($testingOnly=0,$timeout=120,$URL=""){
			if(is_numeric($timeout))
				$this->_timeout=$timeout;
			else
				$this->_timeout=120;
			$this->returnCode="N";
			$this->approvNum="NOT APPROVED";
			$this->cardResponse="NOT APPROVED - ERROR";
			$this->avsResponse="NO DATA";
			$this->recordNumber="-1";
			$this->recurring="NO";
			$this->timeframe="MONTH";
			$this->_debug=$testingOnly;
			if($URL=="")
				$this->_sURL="https://www.securepay.com/secure1/index.asp";
			else
				$this->_sURL=$URL;
		}//END Constructor SecurePayCharge
		
		/*
			Function: BuildPostData
			Purpose: String together all passed variables
			Parameters: NONE
		*/
		function BuildPostData(){			
			$postData= "merch_id=" . urlencode($this->merchID);
			$postData.= "&amount=" . urlencode($this->amount);
			$postData.= "&name=" . urlencode($this->custName);
			$postData.= "&street=" . urlencode($this->street);
			$postData.= "&city=" . urlencode($this->city);
			$postData.= "&state=" . urlencode($this->state);
			$postData.= "&zip=" . urlencode($this->zip);
			$postData.= "&country=" . urlencode($this->country);
			$postData.= "&email=" . urlencode($this->custEmail);
			$postData.= "&avsreq=" . urlencode($this->avsreq);
			$postData.= "&tr_type=" . urlencode($this->transType);
			$postData.= "&cc_method=" . urlencode($this->ccMethod);
			$postData.= "&cvv2=" . urlencode($this->cvv2);
			
			if(strtoupper($this->recurring)=="YES"){
				$postData.= "&recurring=" . urlencode($this->recurring);
				$postData.= "&time_frame=" . urlencode($this->timeframe);
				$postData.= "&rec_amount=" . urlencode($this->recamount);
			}
			
			if(strtoupper($this->ccMethod)!="DATAENTRY"){
				$postData.= "&swipeData=" . urlencode($this->swipeData);
			}
			else{
				$postData.= "&cc_number=" . urlencode($this->ccNum);
				$postData.= "&month=" . urlencode($this->month);
				$postData.= "&year=" . urlencode($this->year);
			}
			if(strtoupper($this->transType)=="VOID"){				
				$postData.= "&voidRecNum=" . urlencode($this->voidRecNum);
			}
			if(strtoupper($this->transType)=="FORCE"){
				$postData.= "&app_num=" . urlencode($this->origApprovNumber);
			}
			
			$postData.= "&comment1=" . urlencode($this->comment1);
			$postData.= "&comment2=" . urlencode($this->comment2);
			if($this->_debug)echo "<BR><BR><b>Data posted to server:</b><BR><BR>$postData";
			return $postData;
		}// END BuildPostData		
		
		/*
			Function: TransmitHTTPRequest
			Purpose: Make actual post request wait on and return the result
			Parameters: postRequest
		*/
		function TransmitHTTPRequest($postRequest){	
			$ch=curl_init($this->_sURL);
			if(!$ch)die(sprintf('Error1 [%d]: %s',curl_errno($ch),curl_error($ch)));
			
			curl_setopt($ch, CURLOPT_POST, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS,$postRequest);
			curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);	
			curl_setopt($ch, CURLOPT_SSLVERSION,3);
			initCurlProxySettings($ch);
			
			$http_response = curl_exec($ch);
			
			if(!$http_response)die(sprintf('Error2 [%d]: %s',curl_errno($ch),curl_error($ch)));
			curl_close($ch);
			
			if($this->_debug)echo "<BR><BR><b>Data received from server:</b><BR><BR>$http_response";
			return $http_response;
		} //END TransmitHTTPRequest
		
		/*
			Function: InterpretResponse
			Purpose: interprest result of http call
			Parameters: the http response
		*/
		function InterpretResponse($http_response){
			
			if($http_response=="" || substr_count($http_response,",")!=5){
				$this->errorData="Unrecognizable response received.";
				$this->errorNo="400";
			}
			else{
				$retval=explode(",",$http_response);
				$this->returnCode=$retval[0];
				$this->approvNum=$retval[1];
				$this->cardResponse=$retval[2];
				$this->avsResponse=$retval[3];
				$this->recordNumber=$retval[4];
			}
		}
		
		/*
			Function: SubmitCharge
			Purpose: Manage Charge
			Parameters: NONE
		*/
		function SubmitCharge(){
			$this->InterpretResponse($this->TransmitHTTPRequest($this->BuildPostData()));
		}
		
	} //END Class SecurePay

function tttt(){	
	/**********************************************************	
	Below is how to instantiate and use the securepay charge class
	**********************************************************/
	
	$debug=0; #to turn off debug mode, set to 1 if you want to see raw data exchange
	$timeout=120; #for custom timeout value in seconds, default 120 seconds
	$URL="https://www.blah.com"; //if different that default in documentation
								//current default https://www.securepay.com/secure1/index.asp
	$objSPCharge = new SecurePayCharge($debug /*, $timeout , $URL*/);
	
	$objSPCharge->merchID="35077"; #enter securepay id
	$objSPCharge->amount=".30";
	$objSPCharge->custName="C Ustomer";
	$objSPCharge->street="1400 USA Street";
	$objSPCharge->city="Dallas";
	$objSPCharge->state="TX";
	$objSPCharge->zip="75243";
	$objSPCharge->country="USA";
	$objSPCharge->custEmail="customer@isp.com";
	$objSPCharge->avsreq="1";
	$objSPCharge->transType="SALE";
	$objSPCharge->cvv2="123";

	
	#add this transaction to the recurring database
	#$objSPCharge->recurring="YES";
	#$objSPCharge->timeframe="WEEK";
	#$objSPCharge->recamount="23.32";
	
	#card transaction method either "DATAENTRY" or "SWIPED"
	$objSPCharge->ccMethod="DATAENTRY";
	
	#if data entry (most applications)
	$objSPCharge->ccNum="4111111111111111";
	$objSPCharge->month="05";
	$objSPCharge->year="05";
	#if swipe data
	#$objSPCharge->swipeData="";	
	#if void
	#$objSPCharge->voidRecNum="";
	#if force
	#$objSPCharge->origApprovNumber="";			
	#optional comments
	$objSPCharge->comment1="Test Comment One";
	$objSPCharge->comment2="Test Comment Two";	
	#run credit card transaction
	$objSPCharge->SubmitCharge();
	
	echo "<BR><BR>Return code: " . $objSPCharge->returnCode . "<BR>";
	echo "Approval Number: " . $objSPCharge->approvNum . "<BR>";
	echo "Card Response: " . $objSPCharge->cardResponse . "<BR>";
	echo "AVS Data: " . $objSPCharge->avsResponse . "<BR>";
	echo "Record Number: " . $objSPCharge->recordNumber . "<BR>";
}
?>