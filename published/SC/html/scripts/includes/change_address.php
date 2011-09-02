<?php
// *****************************************************************************
// Purpose			
// Call condition   
//					index.php?order2=yes&shippingAddressID=<address ID>
// Include PHP		index.php -> [change_address.php]
// Uses TPL			change_address.tpl.html
// Remarks


	if ( isset($change_address) )
	{

		function _copyDataFromPostToPage( & $smarty )
		{
			$smarty->hassign("receiver_first_name", $_POST["receiver_first_name"] );
			$smarty->hassign("receiver_last_name", $_POST["receiver_last_name"] );
			$smarty->hassign("countryID", $_POST["countryID"] );
			if ( isset($_POST["state"]) && !isset($_POST["zoneID"]) )
			{
				$smarty->hassign("state", $_POST["state"] );
			}
			else
			{
				$smarty->hassign("zoneID", $_POST["zoneID"]);
			}
			$smarty->hassign("zip", $_POST["zip"]);
			$smarty->hassign("city", $_POST["city"]);
			$smarty->hassign("address", $_POST["address"]); 
			$zones = znGetZonesById( $_POST["countryID"] );
			if ( isset($_POST["address_radiobutton"]) )
				$smarty->hassign( "addressID", $_POST["address_radiobutton"]);
			$smarty->hassign("zones",$zones);
		}

		function _getAllAddresses()
		{
			$addresses = regGetAllAddressesByLogin( $_SESSION["log"] );
			$res = array();

			foreach( $addresses as $address )
			{
				$strAddress = regGetAddressStr( $address["addressID"] );
				$res[] = array(
								"strAddress"	=> $strAddress,
								"addressID"		=> $address["addressID"]
					);
			}
			return $res;
		}


		$returnParamStr = "";
		$orderStage		= "";
		$addressID = 0;
		foreach( $_GET as $key => $value )
		{
			if ( $key == "change_address" )
				continue;
			if ( $key == "shippingAddressID" && $addressID == 0 )
			{
				$orderStage = "order2_shipping=yes";
				$addressID = $value;
			}
			if ( $key == "billingAddressID" )
			{
				$orderStage = "order3_billing=yes";
				$addressID = $value;
			}
			if ( $returnParamStr != "" )
				$returnParamStr .= "&";
			$returnParamStr .= $key."=".$value;
		}

//		if ( $addressID == 0 )
//			Redirect( "index.php?page_not_found=yes" );

		if (  isset($_POST["select_address"]) )
		{
			if ( !isset($_POST["zoneID"]) ) $_POST["zoneID"] = 0;
			if ( !isset($_POST["state"]) ) $_POST["state"] = "";
		}
		else
		{
			$zones = znGetZonesById( CONF_DEFAULT_COUNTRY );
			$smarty->hassign("zones",$zones);
		}


		if ( isset($_POST["select_address"]) )
		{
			if ( $_POST["address_radiobutton"] == 0 )
			{
				$error_message = regVerifyAddress(  
						$_POST["receiver_first_name"], $_POST["receiver_last_name"], 
						$_POST["countryID"], $_POST["zoneID"], $_POST["state"], $_POST["zip"], 
						$_POST["city"], $_POST["address"]	);

				if ( $error_message != "" )
				{
					$smarty->assign( "error_message", $error_message );
					_copyDataFromPostToPage( $smarty );
				}
				else
				{
					$errorCode = "";
					$_POST["address_radiobutton"] = 
						regAddAddress(  
							$_POST["receiver_first_name"], $_POST["receiver_last_name"], 
							$_POST["countryID"], $_POST["zoneID"], $_POST["state"], $_POST["zip"], 
							$_POST["city"], $_POST["address"], $_SESSION["log"], $errorCode );
				}
			}

			if ( $_POST["address_radiobutton"] != 0 )
				Redirect( "index.php?".$orderStage."&".$returnParamStr.
					"&selectedNewAddressID=".$_POST["address_radiobutton"] );

		}

		$addresses = _getAllAddresses();

		$count_row = 0;
		$countries = cnGetCountries( array(), $count_row, null );

		if ( !isset($_POST["address_radiobutton"]) )
			$smarty->assign( "addressID", $addressID );
		$smarty->assign( "addresses", $addresses );
		$smarty->hassign( "countries", $countries );
		$smarty->assign( "main_content_template", "change_address.tpl.html" );
	}

?>