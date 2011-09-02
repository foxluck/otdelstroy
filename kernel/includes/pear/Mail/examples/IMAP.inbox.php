<?php

    /**
     * Demonstrates how to set-up a basic inbox using Mail_IMAP.
     * See {@link connect} for extended documentation on
     * how to set the connection URI.
     *
     * @author      Richard York <rich_y@php.net>
     * @copyright   (c) Copyright 2004, Richard York, All Rights Reserved.
     * @package     Mail_IMAP
     * @subpackage  examples
     **
    */

    require_once 'Mail/IMAP.php';

    // Set up class, initiate a mailbox connection
    $msg =new Mail_IMAP();

    // Open up a mail connection
    // pop3://user:pass@mail.example.com:110/INBOX#notls
    // Use an existing imap resource stream, or provide a URI abstraction.
    //
    // If you are unsure of the URI syntax to use here,
    // use the Mail_IMAP_connection_wizard to find the right URI.
    // Or see docs for Mail_IMAP::connect
    //
    // This argument must also be set in MAIL_IMAP.message.php.
    if (PEAR::isError($msg->connect('imap://user:pass@mail.server.net:143/INBOX'))) {
        echo "<span style='font-weight: bold;'>Error:</span> Unable to build a connection.";
    }


    //  Unread messages appear with LIGHTGRAY links
    //  Read messages appear with WHITE links
    //  If you are using an IMAP-protocol based mail server,
    //  POP3 doesn't remember flag settings

    // Retrieve message count
    $msgcount = $msg->messageCount();

    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
        <html>
            <head>
                <title> Mail_IMAP Inbox </title>
                <style type='text/css' media='screen'>
                    * {
                        font-family: Arial, sans-serif;
                        font-size: 100%;
                    }
                    body {
                        max-width: 1000px;
                        min-width: 500px;
                        margin: 10px auto;
                        border: thick solid black;
                    }
                    a {
                        text-decoration: none;
                        color: royalblue;
                        padding: 1px;
                    }
                    a:hover {
                        color: white !important;
                        background: black;
                    }
                    div#inboxbody {
                        background: lightgrey;
                        padding: 10px;
                    }
                    div#msgcount {
                        padding: 10px 0;
                        font-size: 120%;
                    }
                    table {
                        border: thin solid black;
                        background: white;
                        width: 100%;
                    }
                    #inboxheader {
                        border: medium solid black;
                    }
                    th {
                        background: gray;
                        vertical-align: baseline;
                        color: white;
                        font-weight: bold;
                    }
                    td.msgattach {
                        font-size: 80%;
                        text-decoration: none;
                        padding: 5px 5px 5px 40px;
                    }
                    td.msgcount, td.footer, td.msgitem {
                        padding: 5px;
                        font-size: 90%;
                    }
                    td.footer {
                        font-size: 8pt;
                        text-align: right;
                    }
                    h1 {
                        font-size: 200%;
                        padding-bottom: 10px;
                        margin: 0;
                    }
                    div#quota {
                        text-align: right;
                        padding: 10px 0;
                        font-size: 90%;
                    }
                    #header, #footer {
                        padding: 10px;
                        background: black;
                        color: white;
                        margin: 0;
                    }
                    #footer {
                        text-align: center;
                    }
                    #footer p {
                        margin: 0;
                    }
                </style>
            </head>
            <body>
                <div id='header'>
                    <h1>
                        PEAR :: Mail_IMAP
                    </h1>
                </div>
                <div id='inboxbody'>
                    <div id='msgcount'>
                            {$msg->mailboxInfo['folder']}: ($msgcount) messages total.
                    </div>
                <table class='msg'>
                    <tr>
                        <th>
                            subject
                        </th>
                        <th>
                            from
                        </th>
                        <th>
                            received
                        </th>
                    </tr>\n";

    if ($msgcount > 0)
    {
        /*
         * Each message of a mailbox is numbered offset from 1
         * Create the $mid (message id) and recursively gather
         * message information.
        */
        for ($mid = 1; $mid <= $msgcount; $mid++)
        {
            // Get the default part id
            $pid = $msg->getDefaultPid($mid);

            // Parse header information
            $msg->getHeaders($mid, $pid);

            $style = ((isset($msg->header[$mid]['Recent']) && $msg->header[$mid]['Recent'] == 'N') || (isset($msg->header[$mid]['Unseen']) && $msg->header[$mid]['Unseen'] == 'U'))? 'gray' : 'black';

            // Parse inline/attachment information specific to this part id
            //
            // See member variables begining with in or attach for
            // available information
            $msg->getParts($mid, $pid);

            if (!isset($msg->header[$mid]['subject']) || empty($msg->header[$mid]['subject']))
            {
                $msg->header[$mid]['subject'] = "<span style='font-style: italic;'>no subject provided</a>";
            }

            echo "                        <tr>\n",
                 "                            <td class='msgitem'><a href='IMAP.message.php?mid={$mid}&amp;pid={$pid}' target='_blank' style='color: {$style};'>{$msg->header[$mid]['subject']}</a></td>\n",
                 "                            <td class='msgitem'>\n",
                 "                              ", (isset($msg->header[$mid]['from_personal'][0]) && !empty($msg->header[$mid]['from_personal'][0]))? '<span title="'.str_replace('@', ' at ', $msg->header[$mid]['from'][0]).'">'.$msg->header[$mid]['from_personal'][0]."</span>" : str_replace('@', ' at ', $msg->header[$mid]['from'][0]), "\n",
                 "                            </td>\n",
                 "                            <td class='msgitem'>".date('D d M, Y h:i:s', $msg->header[$mid]['udate'])."</td>\n",
                 "                        </tr>\n",
                 "                        <tr>\n",
                 "                            <td colspan='3' class='msgattach'>\n";

            // Show inline parts first
            if (isset($msg->inPid[$mid]) && count($msg->inPid[$mid]) > 0)
            {
                foreach ($msg->inPid[$mid] as $i => $inid)
                {
                    echo "                              Inline part: <a href='IMAP.message.php?mid={$mid}&amp;pid={$msg->inPid[$mid][$i]}' target='_blank'>{$msg->inFname[$mid][$i]} {$msg->inFtype[$mid][$i]} ".$msg->convertBytes($msg->inFsize[$mid][$i])."</a><br />\n";
                }
            }

            // Now the attachments
            if (isset($msg->attachPid[$mid]) && count($msg->attachPid[$mid]) > 0)
            {
                foreach ($msg->attachPid[$mid] as $i => $aid)
                {
                    echo "                              Attachment: <a href='IMAP.message.php?mid={$mid}&amp;pid={$msg->attachPid[$mid][$i]}' target='_blank'>{$msg->attachFname[$mid][$i]} {$msg->attachFtype[$mid][$i]} ".$msg->convertBytes($msg->attachFsize[$mid][$i])."</a><br />\n";
                }
            }

            echo "                            </td>\n",
                 "                        </tr>\n";

            // Clean up left over variables
            $msg->unsetParts($mid);
            $msg->unsetHeaders($mid);
        }
    }
    else
    {
        echo "                        <tr>\n",
             "                            <td colspan='3' style='font-size: 30pt; text-align: center; padding: 50px 0px 50px 0px;'>No Messages</td>",
             "                        </tr>\n";
    }

    echo "                        <tr>\n",
         "                  </table>\n",
         "                  <div id='quota'>\n",
         "                      mailbox: {$msg->mailboxInfo['user']}<br />\n";

    // getQuota doesn't work for some servers
    if (!PEAR::isError($quota = $msg->getQuota()))
    {
        echo "                      Quota: {$quota['STORAGE']['usage']} used of {$quota['STORAGE']['limit']} total\n";
    }

    echo "                  </div>\n",
         "              </div>\n",
         "              <div id='footer'>\n",
         "                  <p>\n",
         "                      &copy; Copyright 2004 Richard York, All Rights Reserved.<br />\n",
         "                      Best viewed with <a href='http://www.mozilla.org'>Mozilla</a>. Visit the <a href='http://www.spicypeanut.net'>Mail_IMAP</a> homepage.\n",
         "                  </p>\n",
         "              </div>\n",
         "          </body>\n",
         "      </html>";

    // Close the stream
    $msg->close();
?>