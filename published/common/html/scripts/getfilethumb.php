<?php
    ini_set( 'session.cookie_lifetime', 2592000 );
    session_set_cookie_params( 2592000 );
    ini_set( 'session.use_only_cookies', 0 );

    $session_started = ini_get( 'session.auto_start' );
    if ( !$session_started )
            @session_start();
    
    //$thumbPerms = $_SESSION['THUMBPERMS'];
    $basefile = $_GET['basefile'];
    $thumbPath = base64_decode( $basefile );
    $extension = base64_decode( isset($_GET['ext']) ?  $_GET['ext'] : false);
    
    $extension = strtolower( $extension );

    //if ( !in_array($thumbPath, $thumbPerms) )
    //	$thumbPath = null;

    define( 'WBS_DIR', null );
    
    include( '../../../../kernel/functions.php' );
    include( '../../../common/html/includes/httpcommon.php' );
    /*include( '../../../../kernel/classes/class.metric.php' );
    
    if ($S) //disable log services without $S(ervice) variable
    if ($GLOBALS['HTTP_SESSION_VARS']) {
        $currentUser = $GLOBALS['HTTP_SESSION_VARS']['wbs_username'];
        $DB_KEY = $GLOBALS['HTTP_SESSION_VARS']['wbs_dbkey'];
        $metric = metric::getInstance();
        $metric->addAction($DB_KEY, $currentUser, $S, 'GETTHUMB', 'ACCOUNT', '');
    }*/
    dumpFileThumbnail( $thumbPath, 'win', $extension );
    
?>