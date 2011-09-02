<?php

class smsdummy
{
	var $params;

	function setParams( $parameters )
	{
		$this->params = $parameters;
	}

	function sendSMS( $to, $message, $sender="" )
	{
		return "TESTID";
	}

	function chargeSMS( $msgId )
	{
		$result = array(
					"COST"=>$this->getSMSCost(),
					"RESERVE"=>$this->getSMSReserve(),
				);


		$result["CHARGED"] = 1;
		$result["CHARGE"] = $result["COST"];
		$result["STATUS"] = SMS_STATUS_DELIVERED;
		$result["STATUS_TEXT"] = "Message delivered. (001)";

		if ( $this->params->get( "simcharge" ) != 0 )
		{
			$result["CHARGED"] = 0;
			$result["CHARGE"] = $result["RESERVE"];

			$result["STATUS"] = $this->params->get( "simcharge" ) != 1 ? SMS_STATUS_CANCELED : SMS_STATUS_PENDING;
			$result["STATUS_TEXT"] = $this->params->get( "simcharge" ) != 1 ? "Message canceled. (003)" : "Message pended. (002)";

			return $result;
		}

		return $result;
	}

	function getSMSLength( )
	{
		return 100;
	}

	function getSMSCost( )
	{
		return $this->params->get( "cost" );
	}

	function getSMSReserve( )
	{
		return $this->params->get( "reserve" );
	}
}

?>
