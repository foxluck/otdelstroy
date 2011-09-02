<?php
	header ("Cache-Control: no-cache, must-revalidate"); 
	header ("Pragma: no-cache");

	extract( $HTTP_GET_VARS );
	if ( !isset($list) )
		$list = null;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>WebAsyst Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
<!--
	.default {
		font-family: Geneva, Arial, Helvetica, sans-serif;
		font-size: 10pt;
	}
	h1 {
		font-family: Tahoma, Verdana, Arial;
		font-size: 16pt;
		font-weight: normal;
		border-bottom-width: 1px;
		border-bottom-style: solid;
		border-bottom-color: #CCCCCC;
		padding-bottom: 4px;
	
	
	}
	body {
		font-family: Tahoma, Verdana, Arial;
		font-size: 10pt;
		color: #000000;
	}
	p {
		font-family: Tahoma, Verdana, Arial;
		font-size: 10pt;
		font-weight: normal;
		color: #000000;
	}
	
	td {
		font-family: Tahoma, Verdana, Arial;
		font-size: 10pt;
		font-weight: normal;
		padding-left: 1px;
	
	}
	
	.error {
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 10pt;
		color: #333333;
		font-weight: normal;
		background-color: #FFBBBB;
	}
	.comment {
		font-family: Geneva, Arial, Helvetica, sans-serif;
		font-size: 11px;
		color: #666666;
	}
	a:link {
		text-decoration: none;
	}
	a:visited {
		text-decoration: none;
	}
	-->
</style>
</head>
<body bgcolor="#FFFFFF" leftmargin="5" topmargin="5" marginwidth="5" marginheight="5" class="default">
  <h1>WebAsyst Database List</h1>
  <ul>
    <?php
      $list = unserialize( base64_decode( $list ) );

      foreach( $list as $db_name )
        echo "<li>$db_name</li>";
    ?>
  </ul>
</body>
</html>