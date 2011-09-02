<?php

    /**
     * A demonstration of how to use the connection wizard function.
     *
     * @author      Richard York <rich_y@php.net>
     * @copyright   (c) Copyright 2004, Richard York, All Rights Reserved.
     * @package     Mail_IMAP
     * @subpackage  examples
     **
    */

    require_once 'IMAP.connection_wizard.php';

    // Attempt to find the correct URI for any protocol based on common
    // port settings and configurations
    if (FALSE != ($url = Mail_IMAP_connection_wizard('mail.yourserver.net', 'mailuser', 'mailpass')))
    {
        echo $uri;
    }
    else
    {
        echo 'A suitable URI could not be detected.';
    }

    // Attempt to find the correct URI on a specific protocol and port setting
    //
    // Mail_IMAP_connection_wizard('mail.yourserver.net', 'mailuser', 'mailpass', 'imap', 143);
    //

?>