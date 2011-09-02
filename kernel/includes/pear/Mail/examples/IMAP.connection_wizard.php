<?php

    /**
     * @author      Richard York <rich_y@php.net>
     * @copyright   (c) Copyright 2004, Richard York, All Rights Reserved.
     * @package     Mail_IMAP
     * @subpackage  examples
     **
    */

    require_once 'Mail/IMAP.php';

    /**
     * Attempts to find the appropriate URI for the user based on common 
     * configurations and port settings.  This function is meant to serve 
     * as a utility helper to find the correct URI syntax to pass to 
     * {@link connect}.
     *
     * @param     string   $server     Remote mail server to connect to.
     * @param     string   $user       Remote mail server user name.
     * @param     string   $pass       Remote mail server password.
     * @param     string   $protocol   
     *      (optional) Mail server protocol, one of imap|pop3|nntp
     * @param     int      $port
     *      (optional) Mail server port.
     *
     * @return    string|FALSE
     * @todo add SSL/TLS URI testing
     * @see       connect
     **
    */
    function Mail_IMAP_connection_wizard($server, $user, $pass, $protocol = NULL, $port = 0)
    {
        // Building/or/attempting to build the connection can take lots of time
        ini_set('max_execution_time', 120);

        // imap_open throws lots of errors on failed attempts
//        error_reporting(0);

        $mail =new Mail_IMAP();

        $base_uri = urlencode($user).':'.$pass.'@'.$server;

        if ($protocol == NULL) {

            $protocol = array('imap', 'pop3', 'nntp');
            $port     = array(143, 110, 119);

            for ($i = 0; $i < count($protocol); $i++) {

                $uri = $protocol[$i].'://'.$base_uri.':'.$port[$i].'/INBOX';

                if (PEAR::isError($mail->connect($uri))) {

                    if (!PEAR::isError($mail->connect($uri.'#notls'))) {
                        return $uri;
                    }

                } else {
                    return $uri;
                }
            }

        } else {

            switch($protocol) {
                case 'imap':    $base_uri .= ($port == 0)? ':143' : ':'.$port;  break;
                case 'pop3':    $base_uri .= ($port == 0)? ':110' : ':'.$port;  break;
                case 'nntp':    $base_uri .= ($port == 0)? ':119' : ':'.$port;  break;
                default:        return FALSE;
            }

            $uri = $protocol.'://'.$base_uri;

            if (PEAR::isError($mail->connect($uri))) {

                if (!PEAR::isError($mail->connect($uri.'#notls'))) {
                    return $uri;
                }

            } else {
                return $uri;
            }
        }

        return FALSE;
    }

?>