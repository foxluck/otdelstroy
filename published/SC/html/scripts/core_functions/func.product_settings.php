<?php

	// Purpose	gets size
	function GetPictureSize( $filename )
	{
		$size_info=getimagesize(DIR_PRODUCTS_PICTURES."/".$filename);
		return ((string)($size_info[0] + 40 )).", ".((string)($size_info[1] + 40 ));
	}

	// Purpose	gets client JavaScript to open in new window 
	function OpenConfigurator($optionID, $productID)
	{
		echo("<script language='JavaScript'>\n");
		echo("		w=400; \n");
		echo("		h=400; \n");
		echo("		link='".set_query("?ukey=option_value_configurator&optionID=".$optionID."&productID=".$productID)."'; \n");
		echo("		var win = 'width='+w+',height='+h+',menubar=no,location=no,resizable=yes,scrollbars=yes';\n");
		echo("		wishWin = window.open(link,'wishWin',win);\n");
		echo("</script>\n");
	}
?>