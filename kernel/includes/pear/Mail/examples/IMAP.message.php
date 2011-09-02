<?php

    /**
     * This example provides a basic demonstration of how Mail_IMAP can be used to
     * view multipart messages.  See {@link connect} for extended documentation on
     * how to set the connection URI.
     *
     * @author      Richard York <rich_y@php.net>
     * @copyright   (c) Copyright 2004, Richard York, All Rights Reserved.
     * @package     Mail_IMAP
     * @subpackage  examples
     **
    */
    require_once 'Mail/IMAP.php';

    // Import the message id and part id from the $_GET array
    $mid = $_GET['mid'];
    $pid = $_GET['pid'];

    // pop3://user:pass@mail.example.com:110/INBOX#notls

    $msg =new Mail_IMAP();

    // Open up a mail connection
    // pop3://user:pass@mail.example.com:110/INBOX#notls
    // Use an existing imap resource stream, or provide a URI abstraction.
    //
    // If you are unsure of the URI syntax to use here,
    // use the Mail_IMAP_connection_wizard to find the right URI.
    // Or see docs for Mail_IMAP::connect
    //
    // This argument must also be set in MAIL_IMAP.inbox.php.

    if (PEAR::isError($msg->connect('imap://user:pass@mail.server.net:143/INBOX'))) {
        echo "<span style='font-weight: bold;'>Error:</span> Unable to build a connection.";
    }

    // Gather header information
    // Sets the seen flag if this is a subpart of a multipart message
    $msg->getHeaders($mid, $pid);

    // Use this to *not* set the seen flag
    // $msg->getHeaders($mid, $pid, 1024, 1024, NULL, FT_PEEK);
    //
    // Must also use this in the call to getBody below.

    // Gather inline/attachment parts specific to this part
    $msg->getParts($mid, $pid);

    // Are there inline or attachment parts?
    if (count($msg->inPid[$mid]) > 0 || count($msg->attachPid[$mid]) > 0)
    {
        echo "              <table style='width: 100%; border: 1px solid black; background: white;'>\n",
             "                  <tr>\n",
             "                      <td style='font-size: 10px; font-weight: bold;'>\n",
             "                          attachments\n",
             "                      </td>\n",
             "                  </tr>\n",
             "                      <td style='padding: 5px;'>\n";
    }

    // Are there inline parts?
    if (count($msg->inPid[$mid]) > 0)
    {
        foreach ($msg->inPid[$mid] as $i => $inid)
        {
            echo "                          Inline part: <a href='IMAP.message.php?mid={$mid}&amp;pid={$msg->inPid[$mid][$i]}'>{$msg->inFname[$mid][$i]} {$msg->inFtype[$mid][$i]} ".$msg->convertBytes($msg->inFsize[$mid][$i])."</a><br />\n";
        }
    }

    // Are there attachments?
    if (count($msg->attachPid[$mid]) > 0)
    {
        foreach ($msg->attachPid[$mid] as $i => $aid)
        {
            echo "                          Attachment: <a href='IMAP.message.php?mid={$mid}&amp;pid={$msg->attachPid[$mid][$i]}'>{$msg->attachFname[$mid][$i]} {$msg->attachFtype[$mid][$i]} ".$msg->convertBytes($msg->attachFsize[$mid][$i])."</a><br />\n";
        }
    }

    if (count($msg->inPid[$mid]) > 0 || count($msg->attachPid[$mid]) > 0)
    {
        echo "                      </td>\n",
             "                  </tr>\n",
             "              </table>\n";
    }

        echo "              <table style='width: 100%; border: 1px solid black; background: white; margin-top: 5px;'>\n",
             "                  <tr>\n",
             "                     <td>\n",
             "                      <pre>\n",

                                         // Print the Raw Headers
                                         htmlspecialchars($msg->rawHeaders[$mid]),

             "                      </pre>\n",
             "                    </td>\n",
             "                  </tr>\n",
             "              </table>\n";

    // Retrieve the message body (sets the seen flag)
    $body = $msg->getBody($mid, $pid);

    // Use this to *not* set the seen flag
    // $body = $msg->getBody($mid, $pid, 0, 'text/html', FT_PEEK);
    //
    // Must also use this in the call to getHeaders above.

    if ($body['ftype'] == 'text/plain')
    {
        echo "              <table style='width: 100%; border: 1px solid black; background: white; margin-top: 5px;'>\n",
             "                  <tr>\n",
             "                      <td>\n",

                                         // If this is a plain/text part format it for display
                                         nl2br(htmlspecialchars($body['message'])),

             "                      </td>\n",
             "                  </tr>\n",
             "              </table>\n";
    }
    else
    {
        echo $body['message'];
    }

    // Close the stream
    $msg->close();

?>