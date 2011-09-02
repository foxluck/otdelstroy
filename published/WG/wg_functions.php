<?php
if(!function_exists('getLinkPrefix')){
	
	function getLinkPrefix( $level ){
		
		$pagePath = $_SERVER['PHP_SELF'];
		$pageHost = empty($_SERVER['HTTP_X_REAL_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_X_REAL_HOST'];
		if (strtolower(Env::Server('HTTPS')) == 'on' || Env::Server('SERVER_PORT') == 443) {
    		$pageProtocol = 'https://'; 
    	} else {
    		$pageProtocol = 'http://';
    	}
	
		$URL = sprintf( "%s%s%s", $pageProtocol, $pageHost, $pagePath );
	
		$pathData = explodePath( $URL );
	
		if ( !strlen($pathData[count($pathData)-1]) )
			array_pop($pathData);
	
		if ( defined("WEB_CLIENT") )
			$level++;
	
		for ( $i = 1; $i <= $level; $i++ )
			array_pop( $pathData );
	
		return implode("/", $pathData);
	}
}
?>