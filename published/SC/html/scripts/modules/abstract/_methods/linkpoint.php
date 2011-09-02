<?php
	//LinkPoint Connect payment module file
	//this routine can not be executed from browser by simple pointing. Only by POST method.
	if (!isset($_POST["oid"]) || !isset($_POST["chargetotal"])) exit;

	$orderID = (int) $_POST["oid"];
	if (!$orderID) exit;

	$order = ordGetOrder( $orderID );

	$postingURL = "https://www.linkpointcentral.com/lpc/servlet/lppay";

	if (isset($_POST['pSettingsAccordance']['CONF_PAYMENTMODULE_LINKPOINT_POSTING_URL']))
	{
		if (defined($_POST['pSettingsAccordance']['CONF_PAYMENTMODULE_LINKPOINT_POSTING_URL']))
		{
			$postingURL = constant($_POST['pSettingsAccordance']['CONF_PAYMENTMODULE_LINKPOINT_POSTING_URL']);
		}
	}
	echo		"<html>\n".
				"<body onLoad=\"LP_form.submit();\">\n".
				"<table width='100%'>\n".
				"	<tr>\n".
				"		<td align='center'>\n".
				"<form method='POST' name='LP_form' action='".$postingURL."'>\n".
				"<input type=\"hidden\" name=\"storename\" value=\"".constant($_POST['pSettingsAccordance']['CONF_PAYMENTMODULE_LINKPOINT_STORENAME'])."\">\n".
				"<input type=\"hidden\" name=\"txntype\" value=\"sale\">\n".
				"<input type=\"hidden\" name=\"chargetotal\" value=\"".(float)$_POST["chargetotal"]."\">\n";

				if ( (int) constant($_POST['pSettingsAccordance']['CONF_PAYMENTMODULE_LINKPOINT_INTEGRATION_TYPE']) > 1)
				{
					echo "<input type=hidden name=\"2000\" value=\"Submit\">\n";

					if ((int) constant($_POST['pSettingsAccordance']['CONF_PAYMENTMODULE_LINKPOINT_INTEGRATION_TYPE']) == 2) //fetch CC data from session
					{
						if (!isset($_SESSION["lp_cc_number"])) $_SESSION["lp_cc_number"] = "";
						if (!isset($_SESSION["lp_cc_holdername"])) $_SESSION["lp_cc_holdername"] = "";
						if (!isset($_SESSION["lp_cc_expires"])) $_SESSION["lp_cc_expires"] = "";
						if (!isset($_SESSION["lp_cc_cvv"])) $_SESSION["lp_cc_cvv"] = "";
						$order["cc_number"] = Crypt::CCNumberDeCrypt($_SESSION["lp_cc_number"], null);
						$order["cc_holdername"] = Crypt::CCHoldernameDeCrypt($_SESSION["lp_cc_holdername"], null);
						$order["cc_expires"] = Crypt::CCExpiresDeCrypt($_SESSION["lp_cc_expires"], null);
						$order["cc_cvv"] = Crypt::CCNumberDeCrypt($_SESSION["lp_cc_cvv"], null);
					}

					if (!isset($_SESSION["lp_cardtype"])) $_SESSION["lp_cardtype"] = "V";

					if ( strlen($order["cc_expires"]) == 4)
					{
						$expmonth = $order["cc_expires"][0].$order["cc_expires"][1];
						$expyear  = $order["cc_expires"][2].$order["cc_expires"][3];
					}
					else
					{
						$expmonth = "";
						$expyear  = "";
					}

					echo "<input type=\"hidden\" name=\"cardnumber\" value=\"".$order["cc_number"]."\">\n".
					"<input type=\"hidden\" name=\"cctype\" value=\"".$_SESSION["lp_cardtype"]."\">\n".
					"<input type=\"hidden\" name=\"expmonth\" value=\"".(int)$expmonth."\">\n".
					"<input type=\"hidden\" name=\"expyear\" value=\"".(int)(2000+$expyear)."\">\n".
					"<input type=\"hidden\" name=\"cvm\" value=\"".$order["cc_cvv"]."\">\n".
					"<input type=\"hidden\" name=\"bname\" value=\"".$order["cc_holdername"]."\">\n";

				}
				else
				{
					echo "<input type=\"hidden\" name=\"bname\" value=\"".$order["billing_firstname"]." ".$order["billing_lastname"]."\">\n";
				}

				//get billing country ISO 2-chars code
				$q = db_query("select country_iso_2 from ".COUNTRIES_TABLE." where country_name = '".$order["billing_country"]."';") or die (db_error());
				$row = db_fetch_row($q);
				if ($row)
				{
					$bcountry = $row[0];
				}
				else
				{
					$bcountry = "";
				}

				if ( strlen($bcountry) > 0 )
					echo "<input type=\"hidden\" name=\"bcountry\" value=\"".$bcountry."\">\n";

				echo "<input type=\"hidden\" name=\"baddr1\" value=\"".$order["billing_address"]."\">\n".
					"<input type=\"hidden\" name=\"bzip\" value=\"".$order["billing_zip"]."\">\n".
					"<input type=\"hidden\" name=\"bcity\" value=\"".$order["billing_city"]."\">\n";

				if ( $bcountry != "US" )
				{
					echo "<input type=\"hidden\" name=\"bstate2\" value=\"".$order["billing_state"]."\">\n";
				}
				else //US
				{
					//get state ISO2 code
					$q = db_query("select zone_code from ".ZONES_TABLE." where zone_name = '".$order["billing_state"]."';") or die (db_error());
					$row = db_fetch_row($q);
					if ($row)
					{
						$bstate = $row[0];
					}
					else
					{
						$bstate = "";
					}

					echo "<input type=\"hidden\" name=\"bstate\" value=\"".$bstate."\">\n";
				}

				//get shipping country ISO 2-chars code
				$q = db_query("select country_iso_2 from ".COUNTRIES_TABLE." where country_name = '".$order["shipping_country"]."';") or die (db_error());
				$row = db_fetch_row($q);
				if ($row)
				{
					$scountry = $row[0];
				}
				else
				{
					$scountry = "";
				}

				if ( strlen($scountry) > 0 )
					echo "<input type=\"hidden\" name=\"scountry\" value=\"".$scountry."\">\n";

				echo "<input type=\"hidden\" name=\"saddr1\" value=\"".$order["shipping_address"]."\">\n".
					"<input type=\"hidden\" name=\"szip\" value=\"".$order["shipping_zip"]."\">\n".
					"<input type=\"hidden\" name=\"scity\" value=\"".$order["shipping_city"]."\">\n".
					"<input type=\"hidden\" name=\"sname\" value=\"".$order["shipping_firstname"]." ".$order["shipping_lastname"]."\">\n";

				if ( $scountry != "US" )
				{
					echo "<input type=\"hidden\" name=\"sstate2\" value=\"".$order["shipping_state"]."\">\n";
				}
				else //US
				{
					//get state ISO2 code
					$q = db_query("select zone_code from ".ZONES_TABLE." where zone_name = '".$order["shipping_state"]."';") or die (db_error());
					$row = db_fetch_row($q);
					if ($row)
					{
						$sstate = $row[0];
					}
					else
					{
						$sstate = "";
					}
					echo "<input type=\"hidden\" name=\"sstate\" value=\"".$sstate."\">\n";
				}

				echo "<input type=\"hidden\" name=\"email\" value=\"".$order["customer_email"]."\">\n";


				echo "<input type=\"hidden\" name=\"oid\" value=\"".$orderID."\">\n".
				"		</form></td>\n".
				"	</tr>\n".
				"</table>\n\n";
				"</body></html>";

exit(1);
?>
