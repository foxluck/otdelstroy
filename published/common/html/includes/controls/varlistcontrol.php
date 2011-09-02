<?php

	//
	// Variable list control
	//
	// Use varlist.js and varlistcontrol.htm files with this class
	//

	class vlc_item
	//
	// Variable list control item
	//
	{
		var $varName;
		var $varDesc;

		function vlc_item( $varName, $varDesc )
		{
			$this->varName = $varName;
			$this->varDesc = $varDesc;
		}
	}

?>