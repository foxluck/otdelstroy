<?php
    ini_set( 'session.cookie_lifetime', 2592000 );
    session_set_cookie_params( 2592000 );
    ini_set( 'session.use_only_cookies', 0 );
    
    include_once '../../../../system/init.php';
    
    $filename = Env::Get('basefile', Env::TYPE_BASE64, "");
    if (strpos(substr($filename, -5), '.') !== false && !in_array(substr($filename, 0, -4), array('.gif', '.jpg', '.png', 'jpeg'))) {
    	exit;
    }
    $ext = strtolower(Env::Get('ext', Env::TYPE_BASE64, ""));
    $size = Env::Get('size', Env::TYPE_INT, -1);
	
    if ( $ext == 'gif' ) {
	    header( 'Content-type: image/gif' );
    } elseif ( $ext == 'png' ) {
    	header( 'Content-type: image/png' );
    } elseif ( $ext == 'jpg' || $ext == 'jpeg') {
		header( 'Content-type: image/jpeg' );
    } elseif ($ext) {
    	// 404
    	exit;
    }

    if ($size) {
    	if (file_exists($filename.".".$size.".jpg")) {
    		readfile($filename.".".$size.".jpg");
    	} elseif (file_exists($filename.".".$size.".".$ext)) {
			readfile($filename.".".$size.".".$ext);    	  
    	} elseif (file_exists($filename.".".$ext)) {
			readfile($filename.".".$ext);
    	} elseif (file_exists($filename.".gif")) {
			readfile($filename.".gif");
    	}
    }
    else {
    	if (file_exists($filename)) {
    		readfile($filename);
    	} elseif (file_exists($filename.".".$ext)) {
			readfile($filename.".".$ext);    		
    	}
    }
