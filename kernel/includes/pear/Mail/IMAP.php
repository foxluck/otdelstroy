<?php

// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt                                   |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Richard York <rich_y@php.net>                                |
// +----------------------------------------------------------------------+
//
// $Id

require_once 'PEAR.php';

// {{{ constants
// {{{ Mail_IMAP::connect action options
define('MAIL_IMAP_GET_INFO',        5);
define('MAIL_IMAP_NO_INFO',         6);
// }}}
// {{{ Mail_IMAP::getBody action options
define('MAIL_IMAP_BODY',            0);
define('MAIL_IMAP_LITERAL',         1);
define('MAIL_IMAP_LITERAL_DECODE',  2);
// }}}
// {{{ Mail_IMAP::setFlags action options
define('MAIL_IMAP_SET_FLAGS',       3);
define('MAIL_IMAP_CLEAR_FLAGS',     4);
// }}}
// {{{ Mail_IMAP::Mail_IMAP error reporting options
define('MAIL_IMAP_E_DEBUG',         100);
// }}}
// }}}

/**
* <p>Mail_IMAP provides a simplified backend for working with the c-client (IMAP) extension.
* It serves as an OO wrapper for commonly used c-client functions.
* It provides structure and header parsing as well as body retrieval.</p>
* <p>This package requires the c-client extension.  To download the latest version of
* the c-client extension goto: http://www.php.net/imap.</p>
*
* <b>PEAR PREREQUISITES:</p>
*   <ul>
*       <li>Net_URL (Required if you will be using a URI abstraction to connect.)</li>
*   </ul>
*
* <b>Known Bugs:</b>
*   <p>Potential bugs may arise from the detection of certain multipart/* messages.
*   Application parses and adjusts for anomalies in multipart/mixed,
*   multipart/related and multipart/alternative messages.  Bugs may arise from
*   untested and unincluded multipart message types, multipart/parallel,
*   multipart/report, multipart/signed and multipart/digest.</p>
*   <p>Have been told about a float conversion when passing a pid like 2.10 to
*   array_search, have not yet reproduced.</p>
*   <p>CID handling is not tested or yet perfected, some bugs may rise up there.</p>
*
* <b>What to do when a message bombs on Mail_IMAP:</b>
* <ol>
*   <li>File a bug report at http://pear.php.net/bugs</li>
*   <li>Mail a copy of the message preserving the structure as you now see it
*       e.g. don't send as an attachment of another message to
*       demo@smilingsouls.net.</li>
*   <li>Include "Mail_IMAP", the MIME type and a short summary of what's wrong in
*       the subject line, for instance if bombing on a multipart/related message,
*       include that MIME type, if you aren't sure what MIME type the message is,
*       don't worry about it :). Altering the subject line is important, otherwise
*       I may think the message is spam and delete it.</li>
* </ol>
*
* <b>Having trouble finding the example files?</b>
*   Examples are located in the PEAR install directory under /docs/Mail_IMAP/examples
*
* <b>Extended documentation available at:</b>
*   http://www.spicypeanut.net
*
* <b>The following URL will *sometimes* have a more recent version of IMAP.php than PEAR:</b>
*   <p>http://www.spicypeanut.net/index.html?content=373
*   This is where I post my working copy of Mail_IMAP between releases, use at your
*   own risk (any API changes made there may not make it into the official release).</p>
*
* @author       Richard York <rich_y@php.net>
* @category     Mail
* @package      Mail_IMAP
* @license      PHP
* @version      1.1.0
* @copyright    (c) Copyright 2004, Richard York, All Rights Reserved.
* @since        PHP 4.2.0
*
* @example      examples/IMAP.inbox.php
*   Mail_IMAP Inbox
*
* @example      examples/IMAP.message.php
*   Mail_IMAP Message
*
* @example      examples/IMAP.connection_wizard.php
*   Mail_IMAP Connection Wizard
*
* @example      examples/IMAP.connection_wizard_example.php
*   Mail_IMAP Connection Wizard
*
* @todo imap_mail_copy
* @todo imap_mail_move
*/

// {{{ class Mail_IMAP
class Mail_IMAP {

    // {{{ properties
    /**
     * Contains the imap resource stream.
     * @var     resource $mailbox
     * @access  public
     */
    var $mailbox;

    /**
     * Contains information about the current mailbox.
     * @var     array $mailboxInfo
     * @access  public
     */
    var $mailboxInfo;

    /**
     * Set flags for various imap_* functions.
     *
     * Use associative indices to indicate the imap_* function to set flags for,
     *  create the indice omitting the 'imap_' portion of the function name.
     *  see: Mail_IMAP::setOptions for more information.
     *
     * @var     array $option
     * @access  public
     */
    var $option;

    /**
     * (string) contains the various possible data types.
     * @var     array $_dataTypes
     * @access  private
     */
    var $_dataTypes = array('text', 'multipart', 'message', 'application', 'audio', 'image', 'video', 'other');

    /**
     * (string) Contains the various possible encoding types.
     * @var     array $_encodingTypes
     * @access  private
     */
    var $_encodingTypes = array('7bit', '8bit', 'binary', 'base64', 'quoted-printable', 'other');

    // --------------------------ALL MESSAGE PARTS-----------------------------
    /**
     * (object) Contains the object returned by {@link imap_fetchstructure}.
     * @var     array $_structure
     * @access  private
     */
    var $_structure;

    /**
     * (str) Contains all of the part ids for a given message.
     * @var     array $_pid
     * @access  private
     */
    var $_pid;

    /**
     * (string) Contains all of the part mime types for a given message.
     * @var     array $_ftype
     * @access  private
     */
    var $_ftype;

    /**
     * (int) Contains the file size in bytes for message parts.
     * @var     array $_fsize
     * @access  private
     */
    var $_fsize;

    /**
     * (str) Contains the original file name for a message part (if any).
     * @var     array $_fname
     * @access  private
     */
    var $_fname;

    /**
     * (string) Contains the part disposition inline | attachment.
     * @var     array $_disposition
     * @access  private
     */
    var $_disposition;

    /**
     * (str) Contains the part encoding.
     * @var     array $_encoding
     * @access  private
     */
    var $_encoding;

    /**
     * (str) Contains the part id for multipart/related (if any).
     * @var     array $_inlineId
     * @access  private
     */
    var $_inlineId;

    /**
     * (bool) Determines whether the current part has attachments.
     * @var     array $_hasAttachments
     * @access  private
     */
    var $_hasAttachments;

    /**
     * (str) contains the default PID.
     * @var     array $defaultPid
     * @access  public
     */
    var $defaultPid;

    // --------------------------INLINE MESSAGE PARTS-----------------------------
    /**
     * (str) Inline part id.
     * @var     array $inPid
     * @access  public
     */
    var $inPid;

    /**
     * (str) Inline part MIME type.
     * @var     array $inFtype
     * @access  public
     */
    var $inFtype;

    /**
     * (int) Inline file size of the part in bytes.
     * @var     array $inFsize
     * @access  public
     */
    var $inFsize;

    /**
     * (int) Inline file name of the part, if any.
     * @var     array $inFname
     * @access  public
     */
    var $inFname;

    /**
     * (bool) Inline part has attachments?
     * @var     array $inHasAttach
     * @access  public
     */
    var $inHasAttach;

    /**
     * (str) Inline CID for multipart/related.
     * @var     array $inInlineId
     * @access  public
     */
    var $inInlineId;

    // --------------------------ATTACHMENT MESSAGE PARTS-----------------------------
    /**
     * (str) Attachment part id.
     * @var     array $attachPid
     * @access  public
     */
    var $attachPid;

    /**
     * (str) Attachment MIME type.
     * @var     array $attachFtype
     * @access  public
     */
    var $attachFtype;

    /**
     * (int) Attachment file size in bytes.
     * @var     array $attachFsize
     * @access  public
     */
    var $attachFsize;

    /**
     * (str) Attachment original file name (if any, if not file name is empty string).
     * @var     array $attachFname
     * @access  public
     */
    var $attachFname;

    /**
     * (bool) Attachment has attachments?
     * @var     array $attachHasAttach
     * @access  public
     */
    var $attachHasAttach;

    // -------------------------- MESSAGE HEADERS -----------------------------
    /**
     * (str) Contains raw message headers fetched from {@link imap_fetchbody}
     * or {@link imap_fetchheader} depending on which message part is being retrieved.
     * @var     array $rawHeaders
     * @access  public
     */
    var $rawHeaders;

    /**
     * (array)(mixed) Associative array containing information gathered by {@link imap_headerinfo}
     * or {@link imap_rfc822_parse_headers}.
     * @var    header array $header
     */
    var $header;
    // }}}

    // {{{ constructor
    /**
    * Constructor. Optionally set the IMAP resource stream.
    *
    * If IMAP connection arguments are not supplied, returns NULL.  Accepts
    * a URI abstraction of the standard imap_open connection argument
    * (see {@link connect}) or the imap resource indicator.
    *
    * @param     string         $connect  (optional) server URL to connect to
    * @param     int            $options  (optional) options see imap_open (DEPRECATED, use $this->option['open'] instead)
    * @param     int            $error_reporting
    *   (optional), one of E_ALL or 0, tells Mail_IMAP to report more about the messages it is
    *   parsing and where hacks are being used, such as fallback PIDs. This level of
    *   error reporting can become annoying, to turn it off, set to 0.
    *
    * @access    public
    * @return    BOOL|NULL|PEAR_Error
    * @see       connect
    * @see       imap_open
    */
    function Mail_IMAP($connection = NULL, $options = NULL, $error_reporting = E_ALL)
    {
        if (!defined('MAIL_IMAP_ERROR_REPORTING')) {
            define('MAIL_IMAP_ERROR_REPORTING', $error_reporting);
        }

        if (is_resource($connection)) {
            if (get_resource_type($connection) == 'imap') {
                $this->mailbox = $connection;
                $ret = TRUE;
            } else {
                $ret = PEAR::raiseError('Mail_IMAP::Mail_IMAP: Supplied resource is not a valid IMAP stream.');
            }
        } else {
            $ret = ($connection == NULL)? NULL : Mail_IMAP::connect($connection, $options);
        }

        return $ret;
    }
    // }}}

    // {{{ connect()
    /**
    * Wrapper method for {@link imap_open}.  Accepts a URI abstraction in
    * the following format: imap://user:pass@mail.example.com:143/INBOX#notls
    * instead of the standard connection arguments used in imap_open.
    * Replace the protocol with one of pop3|pop3s imap|imaps nntp|nntps.
    * Place intial folder in the file path portion, and optionally append
    * tls|notls|novalidate-cert in the anchor portion of the URL.  A port
    * number is optional, however, leaving it off could lead to a serious
    * degradation in preformance.
    *
    * Examples of a well-formed connection argument:
    *
    * For IMAP:        imap://user:pass@mail.example.com:143/INBOX
    *
    * For IMAP SSL:    imaps://user:pass@example.com:993/INBOX
    *
    * For POP3:        pop3://user:pass@mail.example.com:110/INBOX
    *
    * For POP3 SSL:    pop3s://user:pass@mail.example.com:993/INBOX
    *
    * For NNTP:        nntp://user:pass@mail.example.com:119/comp.test
    *
    * For 'notls' OR 'novalidate-cert' append to the URL as an anchor.
    * For 'tls' use secure protocol and add the 'tls' option to the anchor.
    *
    * Examples:
    *
    * For notls:       imap://user:pass@mail.example.com:143/INBOX#notls
    *
    * For tls:         imaps://user:pass@mail.example.com:143/INBOX#tls
    *
    * tls no-validate: imaps://user:pass@mail.example.com:143/INBOX#tls/novalidate-cert
    *
    * ssl no-validate: imaps://user:pass@mail.example.com:143/INBOX#novalidate-cert
    *
    * If the username is an email address or contains invalid URL characters,
    * urlencode the username portion of the string before passing it.
    *
    * Use the IMAP.connection_wizard_example.php file to automatically detect
    * the correct URI to pass to this function.  This file is located in the
    * examples directory.
    *
    * @param    string           $connect   server URL
    * @param    int              (optional) options (DEPRECATED, use $this->option['open'] instead)
    *   As of Mail_IMAP 1.1.0 the $options argument accepts an action for
    *   retrieving various mailbox information. If set to MAIL_IMAP_GET_INFO (the default action)
    *   Mail_IMAP::connect will make a call to {@link getMailboxInfo}. If set to MAIL_IMAP_NO_INFO
    *   this call will not be made. In the upcoming Mail_IMAP 2.0.0 release the $options argument
    *   will be no longer specify optional flags for {@link imap_open} and will server
    *   exclusively as an action toggle for {@link getMailboxInfo}.
    *
    * @return   PEAR_Error|TRUE
    * @access   public
    * @see      imap_open
    * @see      debug
    * @see      getMailboxInfo
    */
    function connect($connect, $options = NULL)
    {
        if (!class_exists('Net_URL')) {
            if (!@include_once('Net/URL.php')) {
                return PEAR::raiseError('Mail_IMAP::connect: Inclusion of Net_URL not successful.');
            }
        }

        if (isset($this->option['open'])) {
            $options = $this->option['open'];
        }

        $url =new Net_URL($connect);

        $connect  = '{'.$url->host;

        if (!empty($url->port)) {
            $connect .= ':'.$url->port;
        }

        $secure   = ('tls' == substr($url->anchor, 0, 3))? '' : '/ssl';
        $connect .= ('s' == (substr($url->protocol, -1)))? '/'.substr($url->protocol, 0, 4).$secure : '/'.$url->protocol;

        if (!empty($url->anchor)) {
            $connect .= '/'.$url->anchor;
        }

        if ( isset($url->user) ) 
           $connect .= "/user=" . urldecode($url->user); 

        $connect .= '}';

        $this->mailboxInfo['host']   = $connect;

        // Trim off the leading slash '/'
        if (!empty($url->path)) {
            $this->mailboxInfo['folder'] = substr($url->path, 1, (strlen($url->path) - 1));
            $connect .= $this->mailboxInfo['folder'];
        }

        $this->mailboxInfo['user']   = urldecode($url->user);
        $ret = (FALSE === ($this->mailbox = @imap_open($connect, urldecode($url->user), $url->pass, $options)))? PEAR::raiseError('Mail_IMAP::connect: Unable to build a connection to the specified mail server.') : TRUE;

        // get mailbox info
        if ($options != MAIL_IMAP_NO_INFO) {
            Mail_IMAP::getMailboxInfo(FALSE);
        }

        // Do debugger
        if ((isset($_GET['dump_mid'])) && (MAIL_IMAP_ERROR_REPORTING == E_ALL || MAIL_IMAP_ERROR_REPORTING == MAIL_IMAP_E_DEBUG)) {
            Mail_IMAP::debug($_GET['dump_mid']);
        }

        return $ret;
    }
    // }}}

    // {{{ getMailboxInfo()
    /**
    * Adds to the {@link $mailboxInfo} member variable information about the current
    * mailbox from {@link imap_mailboxmsginfo}.
    *
    * Note: This method is automatically called on by default by {@link connect}.
    *
    * @param    string           $connect   server URL
    * @param    bool             $get_info
    *   (optional) TRUE by default. If TRUE, make a call to {@link getMailboxInfo}
    *   if FALSE do not call {@link getMailboxInfo}
    *
    * @return   PEAR_Error|TRUE
    * @access   public
    * @see      imap_open
    */
    function getMailboxInfo($ret = TRUE)
    {
        // It's possible that this function has already been called by Mail_IMAP::connect
        // If so, the 'Mailbox' indice will already exist and the user just wants
        // the contents of the mailboxInfo member variable.
        if (!isset($this->mailboxInfo['Mailbox'])) {
            $this->mailboxInfo = @array_merge($this->mailboxInfo, get_object_vars(imap_mailboxmsginfo($this->mailbox)));
        }

        if ($ret == TRUE) {
            return $this->mailboxInfo;
        }
    }
    // }}}

    // {{{ setOptions()
    /**
    * Set the $option member variable, which is used to specify optional imap_* function
    * arguments (labeled in the manual as flags or options e.g. FT_UID, OP_READONLY, etc).
    *
    * Example:
    *    $msg->setOptions(array('body', 'fetchbody', 'fetchheader'), 'FT_UID');
    *
    * This results in imap_body, imap_fetchbody and imap_fetchheader being passed the FT_UID
    * option in the flags/options argument where ever these are called on by Mail_IMAP.
    *
    * Note: this method only sets arguments labeled as flags/options.
    *
    * @param    array          $option_set - function names to pass the arugument to
    * @param    string         $constant   - constant name to pass.
    * @return   PEAR_Error|TRUE
    * @access   public
    * @see      $option
    */
    function setOptions($option_set, $constant)
    {
        if (is_array($option_set) && !empty($option_set)) {
            foreach ($option_set as $value) {
                if (!$this->option[$value] = @constant($constant)) {
                    return PEAR::raiseError('Mail_IMAP::setOptions: The constant: '.$constant.' is not defined!');
                }
            }
        } else {
            return PEAR::raiseError('Mail_IMAP::setOptions: The first argument must be an array.');
        }

        return TRUE;
    }
    // }}}

    // {{{ close()
    /**
    * Wrapper method for {@link imap_close}.  Close the IMAP resource stream.
    *
    * @param    int           $options    (optional) sets the second argument of imap_close (DEPRECATED, use $this->option['close'] instead)
    * @return   PEAR_Error|TRUE
    * @access   public
    * @see      imap_close
    */
    function close($options = NULL)
    {
        if (isset($this->option['close'])) {
            $options = $this->option['close'];
        }

        return (@imap_close($this->mailbox, $options))? TRUE : PEAR::raiseError('Mail_IMAP::close: Unable to close the connection to the mail server.');
    }
    // }}}

    // {{{ messageCount()
    /**
    * Wrapper method for {@link imap_num_msg}.
    *
    * @return   int mailbox message count
    * @access   public
    * @see      imap_num_msg
    */
    function messageCount()
    {
        return @imap_num_msg($this->mailbox);
    }
    // }}}

    // {{{ _declareParts()
    /**
    * Gather message information returned by {@link imap_fetchstructure} and recursively iterate
    * through each parts array.  Concatenate part numbers in the following format `1.1`
    * each part id is separated by a period, each referring to a part or subpart of a
    * multipart message.  Create part numbers as such that they are compatible with
    * {@link imap_fetchbody}.
    *
    * @param    int           &$mid         message id
    * @param    array         $sub_part     recursive
    * @param    string        $sub_pid      recursive parent part id
    * @param    int           $n            recursive counter
    * @param    bool          $is_sub_part  recursive
    * @param    bool          $skip_part    recursive
    * @return   mixed
    * @access   private
    * @see      imap_fetchstructure
    * @see      imap_fetchbody
    */
    function _declareParts(&$mid, $sub_part = NULL, $sub_pid = NULL, $n = 0, $is_sub_part = FALSE, $skip_part = FALSE)
    {
        if (!is_array($sub_part)) {
            $this->_structure[$mid] = (isset($this->option['fetchstructure']))? @imap_fetchstructure($this->mailbox, $mid, $this->option['fetchstructure']) : @imap_fetchstructure($this->mailbox, $mid);
        }

        if (isset($this->_structure[$mid]->parts) || is_array($sub_part)) {

            if ($is_sub_part == FALSE) {
                $parts = $this->_structure[$mid]->parts;
            } else {
                $parts = $sub_part;
                $n++;
            }

            for ($p = 0, $i = 1; $p < count($parts); $n++, $p++, $i++) {
                // Skip the following...
                // multipart/mixed!
                // subsequent multipart/alternative if this part is message/rfc822
                // multipart/related
                //
                // Have noticed the existence of several other multipart/* types of messages
                // but have yet had the opportunity to test on those.
                $ftype        = (empty($parts[$p]->type))?    $this->_dataTypes[0].'/'.strtolower($parts[$p]->subtype) : $this->_dataTypes[$parts[$p]->type].'/'.strtolower($parts[$p]->subtype);
                $skip_next    = ($ftype == 'message/rfc822')? TRUE : FALSE;

                if ($ftype == 'multipart/mixed' || $skip_part == TRUE && $ftype == 'multipart/alternative' || $ftype == 'multipart/related' && count($parts) == 1) {
                    $n--;
                    $skipped = TRUE;
                } else {

                    $skipped = FALSE;

                    $this->_pid[$mid][$n] = ($is_sub_part == FALSE)? (string) "$i" : (string) "$sub_pid.$i";

                    $this->_ftype[$mid][$n]     = $ftype;
                    $this->_encoding[$mid][$n]  = (empty($parts[$p]->encoding))? $this->_encodingTypes[0] : $this->_encodingTypes[$parts[$p]->encoding];
                    $this->_fsize[$mid][$n]     = (!isset($parts[$p]->bytes) || empty($parts[$p]->bytes))? 0 : $parts[$p]->bytes;

                    // Force inline disposition if none is present
                    if ($parts[$p]->ifdisposition == TRUE) {

                        $this->_disposition[$mid][$n] = strtolower($parts[$p]->disposition);

                        if ($parts[$p]->ifdparameters == TRUE) {

                            $params = $parts[$p]->dparameters;

                            foreach ($params as $param) {

                                if (strtolower($param->attribute) == 'filename') {
                                    $this->_fname[$mid][$n] = $param->value;
                                    break;
                                }
                            }
                        }

                    } else {
                        $this->_disposition[$mid][$n] = 'inline';
                    }

                    if ($parts[$p]->ifid == TRUE) {
                        $this->_inlineId[$mid][$n] = $parts[$p]->id;
                    }
                }

                if (isset($parts[$p]->parts) && is_array($parts[$p]->parts)) {
                    if ($skipped == FALSE) {
                        $this->_hasAttachments[$mid][$n] = TRUE;
                    }

                    $n = Mail_IMAP::_declareParts($mid, $parts[$p]->parts, $this->_pid[$mid][$n], $n, TRUE, $skip_next);

                } else if ($skipped == FALSE) {
                    $this->_hasAttachments[$mid][$n] = FALSE;
                }
            }

            if ($is_sub_part == TRUE) {
                return $n;
            }

         } else {

             // $parts is not an array... message is flat
            $this->_pid[$mid][0] = 1;

            if (empty($this->_structure[$mid]->type)) {
                $this->_structure[$mid]->type        = (int) 0;
            }

            if (isset($this->_structure[$mid]->subtype)) {
                $this->_ftype[$mid][0]               = $this->_dataTypes[$this->_structure[$mid]->type].'/'.strtolower($this->_structure[$mid]->subtype);
            }

            if (empty($this->_structure[$mid]->encoding)) {
                $this->_structure[$mid]->encoding    = (int) 0;
            }

            $this->_encoding[$mid][0]                = $this->_encodingTypes[$this->_structure[$mid]->encoding];

            if (isset($this->_structure[$mid]->bytes)) {
                $this->_fsize[$mid][0]               = strtolower($this->_structure[$mid]->bytes);
            }

            $this->_disposition[$mid][0]             = 'inline';
            $this->_hasAttachments[$mid][0]          = FALSE;
        }

        return;
    }
    // }}}

    // {{{ _checkIfParsed()
    /**
    * Checks if the part has been parsed, if not calls on _declareParts to
    * parse the message.
    *
    * @param    int          &$mid         message id
    * @param    bool         $checkPid
    * @return   void
    * @access   private
    */
    function _checkIfParsed(&$mid, $checkPid = TRUE)
    {
        if (!isset($this->_pid[$mid])) {
           Mail_IMAP::_declareParts($mid);
        }

        if ($checkPid == TRUE && !isset($this->defaultPid[$mid])) {
           Mail_IMAP::getDefaultPid($mid);
        }
        return;
    }
    // }}}

    // {{{ getParts()
    /**
    * sets up member variables containing inline parts and attachments for a specific part
    * in member variable arrays beginning with 'in' and 'attach'.
    * If inline parts are present, sets {@link $inPid}, {@link $inFtype}, {@link $inFsize},
    * {@link $inHasAttach}, {@link $inInlineId} (if an inline CID is specified).
    * If attachments are present, sets, {@link $attachPid}, {@link $attachFsize}, {@link $attachHasAttach},
    * {@link $attachFname} (if a filename is present, empty string otherwise).
    *
    * Typically the text/html part is displayed by default by a message viewer, this part is
    * excluded from the inline member variable arrays thourgh $excludeMime by default.  If
    * $getInline is TRUE the text/plain alternative part will be returned in the inline array
    * and may be included as an attachment.  Useful for mail developement/debugging of multipart
    * messages.
    *
    * @param    int           &$mid         message id
    * @param    int           &$pid         part id
    * @param    string        $MIME
    *       (optional) values: text/plain|text/html, the part MIME type that will be
    *       retrieved by default.
    *
    * @param    bool          $getAlternative
    *       (optional) include the plain/text alternative part in the created inline parts
    *       array if $MIME is text/html, if $MIME is text/plain, include the text/html
    *       alternative part.
    *
    * @param    bool          $retrieve_all
    *       (optional) Instead of just finding parts relative to this part, get *all* parts
    *       using this option *all* sub parts are included in the $in* and $attach* variables.
    *
    * @return   bool
    * @access   public
    * @since    PHP 4.2.0
    */
    function getParts(&$mid, &$pid, $MIME = 'text/html', $getAlternative = TRUE, $retrieve_all = FALSE)
    {
        Mail_IMAP::_checkIfParsed($mid);

        if (count($this->_pid[$mid]) == 1) {
            return TRUE;
        }

        // retrieve key for this part, so that the information may be accessed
        if (FALSE !== ($i = array_search((string) $pid, $this->_pid[$mid]))) {
            if ($retrieve_all == TRUE) {
                Mail_IMAP::_scanMultipart($mid, $pid, $i, $MIME, 'add', 'none', 2, $getAlternative);
            } else {
                if ($pid == $this->defaultPid[$mid]) {
                    Mail_IMAP::_scanMultipart($mid, $pid, $i, $MIME, 'add', 'top', 2, $getAlternative);
                } else if ($this->_ftype[$mid][$i] == 'message/rfc822') {
                    Mail_IMAP::_scanMultipart($mid, $pid, $i, $MIME, 'add', 'all', 1, $getAlternative);
                }
            }
        } else {
            PEAR::raiseError('Mail_IMAP::getParts: Unable to retrieve a valid part id from the pid passed.', null, PEAR_ERROR_TRIGGER, E_USER_WARNING, 'mid: '.$mid.' pid: '.$pid);
            return FALSE;
        }

        return TRUE;
    }
    // }}}

    // {{{ _scanMultipart()
    /**
    * Finds message parts relevant to the message part currently being displayed or
    * looks through a message and determines which is the best body to display.
    *
    * @param    int           &$mid         message id
    * @param    int           &$pid         part id
    * @param    int           $i            offset indice correlating to the pid
    * @param    str           $MIME         one of text/plain or text/html the default MIME to retrieve.
    * @param    str           $action       one of add|get
    * @param    str           $lookAt       one of all|multipart|top|none
    * @param    int           $pidAdd       determines the level of nesting.
    * @param    bool          $getAlternative
    *   Determines whether the program retrieves the alternative part in a
    *   multipart/alternative message.
    *
    * @return   string|FALSE
    * @access   private
    */
    function _scanMultipart(&$mid, &$pid, &$i, $MIME, $action = 'add', $lookAt = 'all', $pidAdd = 1, $getAlternative = TRUE)
    {
        // Find subparts, create variables
        // Create inline parts first, and attachments second

        // Get all top level parts, with the exception of the part currently being viewed
        // If top level part contains multipart/alternative go into that subpart to
        // retrieve the other inline message part to display

        // If this part is message/rfc822 get subparts that begin with this part id
        // Skip multipart/alternative message part
        // Find the displayable message, get plain/text part if $getInline is TRUE

        if ($action == 'add') {

           $excludeMIME = $MIME;
           $MIME        = ($excludeMIME == 'text/plain')? 'text/html' : 'text/plain';
           $in          = 0;
           $a           = 0;

        } else if ($action == 'get') {

           $excludeMIME = NULL;
        }

        $pid_len      = strlen($pid);
        $this_nesting = count(explode('.', $pid));

        foreach ($this->_pid[$mid] as $p => $id) {

            // To look at the next level of nesting one needs to determine at which level
            // of nesting the program currently resides, this needs to be independent of the
            // part id length, since part ids can get into double digits (let's hope they
            // don't get into triple digits!)

            // To accomplish this we'll explode the part id on the dot to get a count of the
            // nesting, then compare the string with the next level in.

            $nesting = count(explode('.', $this->_pid[$mid][$p]));

            switch ($lookAt) {
                case 'all':
                {
                    $condition = (($nesting == ($this_nesting + 1)) && $pid == substr($this->_pid[$mid][$p], 0, $pid_len));
                    break;
                }
                case 'multipart':
                {
                    $condition = (($nesting == ($this_nesting + 1)) && ($pid == substr($this->_pid[$mid][$p], 0)));
                    break;
                }
                // Used if *all* parts are being retrieved
                case 'none':
                {
                    $condition = TRUE;
                    break;
                }
                // To gaurantee a top-level part, detect whether a period appears in the pid string
                case 'top':
                default:
                {
                    if (Mail_IMAP::_isMultipartRelated($mid)) {
                        $condition = (!strstr($this->_pid[$mid][$p], '.') || ($nesting == 2) && substr($this->defaultPid[$mid], 0, 1) == substr($this->_pid[$mid][$p], 0, 1));
                    } else {
                        $condition = (!strstr($this->_pid[$mid][$p], '.'));
                    }
                }
            }

            if ($condition == TRUE) {

                if ($this->_ftype[$mid][$p] == 'multipart/alternative') {

                    foreach ($this->_pid[$mid] as $mp => $mpid) {

                        // Part must begin with last matching part id and be two levels in

                        $sub_nesting = count(explode('.', $this->_pid[$mid][$p]));

                        if (( $this->_ftype[$mid][$mp] == $MIME &&
                              $getAlternative == TRUE &&
                              ($sub_nesting == ($this_nesting + $pidAdd)) &&
                              ($pid == substr($this->_pid[$mid][$mp], 0, strlen($this->_pid[$mid][$p])))
                           )) {

                            if ($action == 'add') {

                                 Mail_IMAP::_addInlinePart($in, $mid, $mp);
                                 break;

                            } else if ($action == 'get' && !isset($this->_fname[$mid][$mp]) && empty($this->_fname[$mid][$mp])) {

                                return $this->_pid[$mid][$mp];

                            }

                        } else if ($this->_ftype[$mid][$mp] == 'multipart/alternative' && $action == 'get') {

                            // Need to match this PID to next level in
                            $pid          = (string) $this->_pid[$mid][$mp];
                            $pid_len      = strlen($pid);
                            $this_nesting = count(explode('.', $pid));
                            $pidAdd       = 2;
                            continue;
                        }
                    }

                } else if ($this->_disposition[$mid][$p] == 'inline' && $this->_ftype[$mid][$p] != 'multipart/related') {

                    if (( $action == 'add' &&
                          $this->_ftype[$mid][$p] != $excludeMIME &&
                          $pid != $this->_pid[$mid][$p]
                       ) || (
                          $action == 'add' &&
                          $this->_ftype[$mid][$p] == $excludeMIME &&
                          isset($this->_fname[$mid][$p]) &&
                          $pid != $this->_pid[$mid][$p]
                       )) {

                        Mail_IMAP::_addInlinePart($in, $mid, $p);

                    } else if ($action == 'get' && $this->_ftype[$mid][$p] == $MIME && !isset($this->_fname[$mid][$p])) {

                        return $this->_pid[$mid][$p];
                    }

                } else if ($action == 'add' && $this->_disposition[$mid][$p] == 'attachment') {

                    Mail_IMAP::_addAttachment($a, $mid, $p);

                }

            }

        }

        return FALSE;
    }
    // }}}

    // {{{ _isMultipartRelated()
    /**
    * Determines whether a message contains a multipart/related part.
    * Only called on by Mail_IMAP::_scanMultipart
    *
    * @return   BOOL
    * @access   private
    * @see      _scanMultipart
    */
    function _isMultipartRelated($mid)
    {
        $ret = Mail_IMAP::extractMIME($mid, 'multipart/related');
        return (!empty($ret) && is_array($ret) && count($ret) >= 1)? TRUE : FALSE;
    }
    // }}}

    // {{{ unsetParts()
    /**
    * Destroys variables set by {@link getParts} and _declareParts.
    *
    * @param    integer  &$mid   message id
    * @return   void
    * @access   public
    * @see      getParts
    */
    function unsetParts(&$mid)
    {
        unset($this->inPid[$mid]);
        unset($this->inFtype[$mid]);
        unset($this->inFsize[$mid]);
        unset($this->inHasAttach[$mid]);
        unset($this->inInlineId[$mid]);

        unset($this->attachPid[$mid]);
        unset($this->attachFtype[$mid]);
        unset($this->attachFsize[$mid]);
        unset($this->attachFname[$mid]);
        unset($this->attachHasAttach[$mid]);

        unset($this->_structure[$mid]);
        unset($this->_pid[$mid]);
        unset($this->_disposition[$mid]);
        unset($this->_encoding[$mid]);
        unset($this->_ftype[$mid]);
        unset($this->_fsize[$mid]);
        unset($this->_fname[$mid]);
        unset($this->_inlineId[$mid]);
        unset($this->_hasAttachments[$mid]);

        return;
    }
    // }}}

    // {{{ _addInlinePart()
    /**
    * Adds information to the member variable inline parts arrays.
    *
    * @param    int     &$in   offset inline counter
    * @param    int     &$mid  message id
    * @param    int     &$i    offset structure reference counter
    * @return   void
    * @access   private
    */
    function _addInlinePart(&$in, &$mid, &$i)
    {
        $this->inFname[$mid][$in] = (isset($this->_fname[$mid][$i]) && !empty($this->_fname[$mid][$i]))? $this->_fname[$mid][$i] : '';

        $this->inPid[$mid][$in]            = $this->_pid[$mid][$i];
        $this->inFtype[$mid][$in]          = $this->_ftype[$mid][$i];
        $this->inFsize[$mid][$in]          = $this->_fsize[$mid][$i];
        $this->inHasAttach[$mid][$in]      = $this->_hasAttachments[$mid][$i];

        if (isset($this->_inlineId[$mid][$i])) {
            $this->inInlineId[$mid][$in]   = $this->_inlineId[$mid][$i];
        }

        $in++;

        return;
    }
    // }}}

    // {{{ _addAttachment()
    /**
    * Adds information to the member variable attachment parts arrays.
    *
    * @param    int     &$a    offset attachment counter
    * @param    int     &$mid  message id
    * @param    int     &$i    offset structure reference counter
    * @return   void
    * @access   private
    */
    function _addAttachment(&$a, &$mid, &$i)
    {
        if (!isset($this->_fname[$mid][$i])) {
            $this->_fname[$mid][$i] = '';
        }

        $this->attachPid[$mid][$a]         = $this->_pid[$mid][$i];
        $this->attachFtype[$mid][$a]       = $this->_ftype[$mid][$i];
        $this->attachFsize[$mid][$a]       = $this->_fsize[$mid][$i];
        $this->attachFname[$mid][$a]       = $this->_fname[$mid][$i];
        $this->attachHasAttach[$mid][$a]   = $this->_hasAttachments[$mid][$i];

        $a++;

        return;
    }
    // }}}

    // {{{ getRawMessage()
    /**
    * Returns entire unparsed message body.  See {@link imap_body} for options.
    *
    * @param    int     &$mid      message id
    * @param    int     $options   flags       (DEPRECATED, use $this->option['body'] instead)
    * @return   void
    * @access   public
    * @see      imap_body
    */
    function getRawMessage(&$mid, $options = NULL)
    {
        if (isset($this->option['body'])) {
            $options = $this->option['body'];
        }

        return imap_body($this->mailbox, $mid, $options);
    }
    // }}}

    // {{{ getBody()
    /**
    * Searches parts array set in Mail_IMAP::_declareParts() for a displayable message.
    * If the part id passed is message/rfc822 looks in subparts for a displayable body.
    * Attempts to return a text/html inline message part by default. And will
    * automatically attempt to find a text/plain part if a text/html part could
    * not be found.
    *
    * Returns an array containing three associative indices; 'ftype', 'fname' and
    * 'message'.  'ftype' contains the MIME type of the message, 'fname', the original
    * file name, if any, empty string otherwise.  And 'message', which contains the
    * message body itself which is returned decoded from base64 or quoted-printable if
    * either of those encoding types are specified, returns untouched otherwise.
    * Returns FALSE on failure.
    *
    * @param    int     &$mid                    message id
    * @param    string  $pid                     part id
    * @param    int     $action
    *      (optional) options for body return.  Set to one of the following:
    *      MAIL_IMAP_BODY (default), if part is message/rfc822 searches subparts for a
    *      displayable body and returns the body decoded as part of an array.
    *      MAIL_IMAP_LITERAL, return the message for the specified $pid without searching
    *      subparts or decoding the message (may return unparsed message) body is returned
    *      undecoded as a string.
    *      MAIL_IMAP_LITERAL_DECODE, same as MAIL_IMAP_LITERAL, except message decoding is
    *      attempted from base64 or quoted-printable encoding, returns undecoded string
    *      if decoding failed.
    *
    * @param    string  $getPart
    *      (optional) one of text/plain or text/html, allows the specification of the default
    *      part to return from multipart messages, text/html by default.
    *
    * @param    int     $options
    *      (optional) allows the specification of the forth argument of imap_fetchbody
    *      (DEPRECATED, use $this->option['fetchbody'] instead)
    *
    * @return   array|string|FALSE
    * @access   public
    * @see      imap_fetchbody
    * @see      Mail_IMAP::getParts
    * @since    PHP 4.2.0
    */
    function getBody(&$mid, $pid = '1', $action = 0, $getPart = 'text/html', $options = NULL, $attempt = 1)
    {
        if (isset($this->option['fetchbody'])) {
            $options = $this->option['fetchbody'];
        }

        if ($action == MAIL_IMAP_LITERAL) {
            return ($options == NULL)? imap_fetchbody($this->mailbox, $mid, $pid) : imap_fetchbody($this->mailbox, $mid, $pid, $options);
        }

        Mail_IMAP::_checkIfParsed($mid);

        if (FALSE !== ($i = array_search((string) $pid, $this->_pid[$mid]))) {
            if ($action == MAIL_IMAP_LITERAL_DECODE) {
                $msg_body = imap_fetchbody($this->mailbox, $mid, $pid, $options);
                return Mail_IMAP::_decodeMessage($msg_body, $this->_encoding[$mid][$i]);
            }

            // If this is an attachment, and the part is message/rfc822 update the pid to the subpart
            // If this is an attachment, and the part is multipart/alternative update the pid to the subpart
            if ($this->_ftype[$mid][$i] == 'message/rfc822' || $this->_ftype[$mid][$i] == 'multipart/related' || $this->_ftype[$mid][$i] == 'multipart/alternative') {

                $new_pid = ($this->_ftype[$mid][$i] == 'message/rfc822' || $this->_ftype[$mid][$i] == 'multipart/related')? Mail_IMAP::_scanMultipart($mid, $pid, $i, $getPart, 'get', 'all', 1) : Mail_IMAP::_scanMultipart($mid, $pid, $i, $getPart, 'get', 'multipart', 1);

                // if a new pid for text/html couldn't be found, try again, this time look for text/plain
                switch(TRUE) {
                    case (!empty($new_pid)):                             $pid = $new_pid; break;
                    case (empty($new_pid) && $getPart == 'text/html'):   return ($attempt == 1)? Mail_IMAP::getBody($mid, $pid, $action, 'text/plain', $options, 2) : FALSE;
                    case (empty($new_pid) && $getPart == 'text/plain'):  return ($attempt == 1)? Mail_IMAP::getBody($mid, $pid, $action, 'text/html', $options, 2) : FALSE;
                }
            }

            // Update the key for the new pid
            if (!empty($new_pid)) {
                if (FALSE === ($i = array_search((string) $pid, $this->_pid[$mid]))) {
                    // Something's afoot!
                    PEAR::raiseError('Mail_IMAP::getBody: Unable to find a suitable replacement part ID for: '.$pid.'. Message: '.$mid.' may be poorly formed, corrupted, or not supported by the Mail_IMAP parser.', NULL, PEAR_ERROR_TRIGGER, E_USER_WARNING);
                    return FALSE;
                }
            }

            $msg_body = imap_fetchbody($this->mailbox, $mid, $pid, $options);

            if ($msg_body == NULL) {
                PEAR::raiseError('Mail_IMAP::getBody: Message body was NULL for pid: '.$pid.', is not a valid part number.', NULL, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
                return FALSE;
            }

            // Decode message.
            // Because the body returned may not correspond with the original PID, return
            // an array which also contains the MIME type and original file name, if any.
            $body['message'] = Mail_IMAP::_decodeMessage($msg_body, $this->_encoding[$mid][$i]);
            $body['ftype']   = $this->_ftype[$mid][$i];
            $body['fname']   = (isset($this->_fname[$mid][$i]))? $this->_fname[$mid][$i] : '';

            return $body;
        } else {
            PEAR::raiseError('Mail_IMAP::getBody: Unable to retrieve message body, invalid part id: '.$pid, NULL, PEAR_ERROR_TRIGGER, E_USER_WARNING);
            return FALSE;
        }

        return FALSE;
    }
    // }}}

    // {{{ _decodeMessage()
    /**
    * Decode a string from quoted-printable or base64 encoding.  If
    * neither of those encoding types are specified, returns string
    * untouched.
    *
    * @param    string  &$body           string to decode
    * @param    string  &$encoding       encoding to decode from.
    * @return   string
    * @access   private
    */
    function _decodeMessage(&$body, &$encoding)
    {
        switch ($encoding) {
            case 'quoted-printable':  return imap_qprint($body);
            case 'base64':            return imap_base64($body);
            default:                  return $body;
        }
    }
    // }}}

    // {{{ getDefaultPid()
    /**
    * Searches structure defined in Mail_IMAP::_declareParts for the top-level default message.
    * Attempts to find a text/html default part, if no text/html part is found,
    * automatically attempts to find a text/plain part. Returns the part id for the default
    * top level message part on success. Returns FALSE on failure.
    *
    * @param    int     &$mid           message id
    * @param    string  $getPart
    *     (optional) default MIME type to look for, one of text/html or text/plain
    *     text/html by default.
    * @return   string
    * @access   public
    */
    function getDefaultPid(&$mid, $getPart = 'text/html', $attempt = 1)
    {
        // Check to see if this part has already been parsed
        Mail_IMAP::_checkIfParsed($mid, FALSE);

        // Look for a text/html message part
        // If no text/html message part was found look for a text/plain message part
        $part = ($getPart == 'text/html')? array('text/html', 'text/plain') : array('text/plain', 'text/html');

        foreach ($part as $mime) {
            if (0 !== count($msg_part = @array_keys($this->_ftype[$mid], $mime))) {
                foreach ($msg_part as $i) {
                    if ($this->_disposition[$mid][$i] == 'inline' && !strstr($this->_pid[$mid][$i], '.')) {
                        $this->defaultPid[$mid] = $this->_pid[$mid][$i];
                        return $this->_pid[$mid][$i];
                    }
                }
            }
        }

        // If no text/plain or text/html part was found
        // Look for a multipart/alternative part
        $mp_nesting = 1;
        $pid_len    = 1;

        foreach ($this->_pid[$mid] as $p => $id) {
            $nesting = count(explode('.', $this->_pid[$mid][$p]));

            if (!isset($mpid)) {
                if ($nesting == 1 && $this->_ftype[$mid][$p] == 'multipart/related') {
                    $mp_nesting = 2;
                    $pid_len    = 3;
                    continue;
                }
                if ($nesting == $mp_nesting && $this->_ftype[$mid][$p] == 'multipart/alternative') {
                    $mpid = $this->_pid[$mid][$p];
                    continue;
                }
            }

            if (isset($mpid) && $nesting == ($mp_nesting + 1) && $this->_ftype[$mid][$p] == $getPart && $mpid == substr($this->_pid[$mid][$p], 0, $pid_len)) {
                $this->defaultPid[$mid] = $this->_pid[$mid][$p];
                return $this->_pid[$mid][$p];
            }
        }

        // if a text/html part was not found, call on the function again
        // and look for text/plain
        // if the application was unable to find a text/plain part
        switch ($getPart) {
            case 'text/html':  $ret = ($attempt == 1)? Mail_IMAP::getDefaultPid($mid, 'text/plain', 2) : FALSE;
            case 'text/plain': $ret = ($attempt == 1)? Mail_IMAP::getDefaultPid($mid, 'text/html', 2) : FALSE;
            default:           $ret = FALSE;
        }

        if ($ret == FALSE && MAIL_IMAP_ERROR_REPORTING == E_ALL && $attempt == 2) {
            PEAR::raiseError('Mail_IMAP::getDefaultPid: Fallback pid used for mid: '.$mid, NULL, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
        }

        $this->defaultPid[$mid] = ($ret == FALSE)? 1 : $ret;

        return $this->defaultPid[$mid];
    }

    // }}}

    // {{{ extractMIME()
    /**
    * Searches all message parts for the specified MIME type.  Use {@link getBody}
    * with $action option MAIL_IMAP_LITERAL_DECODE to view MIME type parts retrieved.
    * If you need to access the MIME type with filename use normal {@link getBody}
    * with no action specified.
    *
    * Returns an array of part ids on success.
    * Returns FALSE if MIME couldn't be found, or on failure.
    *
    * @param    int           &$mid           message id
    * @param    string|array  $MIME           mime type to extract
    * @return   array|FALSE
    * @access   public
    */
    function extractMIME(&$mid, $MIME)
    {
        Mail_IMAP::_checkIfParsed($mid);

        if (is_array($this->_ftype[$mid])) {
            if (!is_array($MIME)) {
                if (0 !== count($pids = array_keys($this->_ftype[$mid], $MIME))) {
                    foreach ($pids as $i) {
                        $rtn[] = $this->_pid[$mid][$i];
                    }
                } else {
                    $rtn = FALSE;
                }
            } else {
                foreach ($MIME as $mtype) {
                    if (0 !== count($pids = array_keys($this->_ftype[$mid], $mtype))) {
                        foreach ($pids as $i) {
                            $rtn[] = $this->_pid[$mid][$i];
                        }
                    } else {
                        $rtn = FALSE;
                    }
                }
            }
        } else {
            $rtn = FALSE;
        }

        return $rtn;
    }
    // }}}

    // {{{ getRawHeaders()
    /**
    * Set member variable {@link $rawHeaders} to contain Raw Header information
    * for a part.  Returns default header part id on success, returns FALSE on failure.
    *
    * @param    int     &$mid          message_id
    * @param    string  $pid           part id
    * @param    int     $options       flags/options for imap_fetchbody
    * @param    bool    $rtn           return the raw headers (returns the headers by default)
    * @return   string|FALSE
    * @access   public
    * @see      imap_fetchbody
    * @see      getHeaders
    */
    function getRawHeaders(&$mid, $pid = '0', $options = NULL, $rtn = TRUE)
    {
        if (FALSE !== ($pid = Mail_IMAP::_defaultHeaderPid($mid, $pid))) {
            if ($pid == '0') {
                $this->rawHeaders[$mid] = (isset($this->option['fetchheader']))? imap_fetchheader($this->mailbox, $mid, $this->option['fetchheader']) : imap_fetchheader($this->mailbox, $mid);
            } else {
                if (isset($this->option['fetchbody'])) {
                    $options = $this->option['fetchbody'];
                }
                $this->rawHeaders[$mid] = imap_fetchbody($this->mailbox, $mid, $pid, $options);
            }

            return ($rtn == TRUE)? $this->rawHeaders[$mid] : $pid;
        } else {
            PEAR::raiseError('Mail_IMAP::getRawHeaders: Unable to retrieve headers, invalid part id: '.$pid, NULL, PEAR_ERROR_TRIGGER, E_USER_WARNING);
            return FALSE;
        }
    }
    // }}}

    // {{{ getHeaders()
    /**
    * Set member variable containing header information.  Creates an array containing associative indices
    * referring to various header information.  Use {@link var_dump} or {@link print_r} on the {@link $header}
    * member variable to view information gathered by this function.
    *
    * Returns header information on success and FALSE on failure.
    *
    * @param    int     &$mid           message id
    * @param    string  &$pid           part id
    * @param    int     $from_length    (optional) from length for imap_headerinfo
    * @param    int     $subject_length (optional) subject length for imap_headerinfo
    * @param    string  $default_host   (optional) default host for imap_headerinfo & imap_rfc822_parse_headers
    * @param    int     $options        (optional) flags/options for imap_fetchbody (DEPRECATED, use $this->option['fetchbody'])
    * @return   Array|BOOL
    * @access   public
    * @see      getParts
    * @see      imap_fetchheader
    * @see      imap_fetchbody
    * @see      imap_headerinfo
    * @see      imap_rfc822_parse_headers
    */
    function getHeaders(&$mid, $pid = '0', $from_length = 1024, $subject_length = 1024, $default_host = NULL, $options = NULL)
    {
        if (FALSE === ($hpid = Mail_IMAP::getRawHeaders($mid, $pid, $options, FALSE))) {
            return FALSE;
        }

        // $default_host contains the host information for addresses where it is not
        // present.  Specify it or attempt to use SERVER_NAME
        if ($default_host == NULL && isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
            $default_host = $_SERVER['SERVER_NAME'];
        } else if ($default_host == NULL) {
            $default_host = 'UNSPECIFIED-HOST-NAME';
        }

        // Parse the headers
        $header_info = ($hpid == '0')? imap_headerinfo($this->mailbox, $mid, $from_length, $subject_length, $default_host) : imap_rfc822_parse_headers($this->rawHeaders[$mid], $default_host);

        // Since individual member variable creation might create extra overhead,
        // and having individual variables referencing this data and the original
        // object would be too much as well, we'll just copy the object into an
        // associative array, preform clean-up on those elements that require it,
        // and destroy the original object after copying.

        if (!is_object($header_info)) {
            PEAR::raiseError('Mail_IMAP::getHeaders: Unable to retrieve header object, invalid part id: '.$pid, NULL, PEAR_ERROR_TRIGGER, E_USER_WARNING);
            return FALSE;
        }

        $headers = get_object_vars($header_info);

        foreach ($headers as $key => $value) {
            if (!is_object($value) && !is_array($value)) {
                $this->header[$mid][$key] = $value;
            }
        }

        // copy udate or create it from date string.
        $this->header[$mid]['udate'] = (isset($header_info->udate) && !empty($header_info->udate))? $header_info->udate : strtotime($header_info->Date);

        // clean up addresses
        $line[] = 'from';
        $line[] = 'reply_to';
        $line[] = 'sender';
        $line[] = 'return_path';
        $line[] = 'to';
        $line[] = 'cc';
        $line[] = 'bcc';

        for ($i = 0; $i < count($line); $i++) {
            if (isset($header_info->$line[$i])) {
                Mail_IMAP::_parseHeaderLine($mid, $header_info->$line[$i], $line[$i]);
            }
        }

        // All possible information has been copied, destroy original object
        unset($header_info);

        return $this->header[$mid];
    }
    // }}}

    // {{{ _parseHeaderLine()
    /**
    * Parse header information from the given line and add it to the {@link $header}
    * array.  This function is only used by {@link getRawHeaders}.
    *
    * @param     string   &$line
    * @param     string   $name
    * @return    void
    * @access    private
    */
    function _parseHeaderLine(&$mid, &$line, $name) {
        if (isset($line) && count($line) >= 1) {
            $i = 0;
            foreach ($line as $object) {
                if (isset($object->personal)) {
                    $this->header[$mid][$name.'_personal'][$i] = $object->personal;
                }

                if (isset($object->mailbox) && isset($object->host)) {
                    $this->header[$mid][$name][$i] = $object->mailbox.'@'.$object->host;
                }
                $i++;
            }
        }
        return;
    }
    // }}}

    // {{{ _defaultHeaderPid()
    /**
    * Finds and returns a default part id for headers and matches any sub message part to
    * the appropriate headers.  Returns FALSE on failure and may return a value that
    * evaluates to false, use the '===' operator for testing this function's return value.
    *
    * @param    int     &$mid            message id
    * @param    string  $pid             part id
    * @return   string|FALSE
    * @access   private
    * @see      getHeaders
    * @see      getRawHeaders
    */
    function _defaultHeaderPid(&$mid, $pid)
    {
        // pid is modified in this function, so don't pass by reference (will create a logic error)
        Mail_IMAP::_checkIfParsed($mid);

        // retrieve key for this part, so that the information may be accessed
        if (FALSE !== ($i = array_search((string) $pid, $this->_pid[$mid]))) {

            // If this part is message/rfc822 display headers for this part
            if ($this->_ftype[$mid][$i] == 'message/rfc822') {

                $ret = (string) $pid.'.0';

            } else if ($pid == $this->defaultPid[$mid]) {

                $ret = (string) '0';

            } else {

                $pid_len = strlen($pid);
                $this_nesting = count(explode('.', $pid));

                // Deeper searching may be required, go back to this part's parent.
                if (!strstr($pid, '.') || ($this_nesting - 1) == 1) {

                    $ret = (string) '0';

                } else if ($this_nesting > 2) {

                    // Look at previous parts until a message/rfc822 part is found.
                    for ($pos = $this_nesting - 1; $pos > 0; $pos -= 1) {

                        foreach ($this->_pid[$mid] as $p => $aid) {

                            $nesting = count(explode('.', $this->_pid[$mid][$p]));

                            if ($nesting == $pos && ($this->_ftype[$mid][$p] == 'message/rfc822' || $this->_ftype[$mid][$p] == 'multipart/related')) {
                                // Break iteration and return!
                                return (string) $this->_pid[$mid][$p].'.0';
                            }
                        }
                    }

                    $ret = ($pid_len == 3)? (string) '0' : FALSE;

                } else {
                    $ret = FALSE;
                }
            }

            return $ret;

        } else {
            // Something's afoot!
            PEAR::raiseError('Mail_IMAP::_defaultHeaderPid: Unable to retrieve headers, invalid part id: '.$pid, NULL, PEAR_ERROR_TRIGGER, E_USER_WARNING);
            return FALSE;
        }
    }
    // }}}

    // {{{ unsetHeaders()
    /**
    * Destroys variables set by {@link getHeaders}.
    *
    * @param    int     &$mid            message id
    * @return   void
    * @access   public
    * @see      getHeaders
    */
    function unsetHeaders(&$mid)
    {
        unset($this->rawHeaders[$mid]);
        unset($this->header[$mid]);
        return;
    }
    // }}}

    // {{{ convertBytes()
    /**
    * Converts an integer containing the number of bytes in a file to one of Bytes, Kilobytes,
    * Megabytes, or Gigabytes, appending the unit of measurement.
    *
    * This method may be called statically.
    *
    * @param    int     $bytes
    * @return   string
    * @access   public
    * @static
    */
    function convertBytes($bytes)
    {
        switch (TRUE) {
            case ($bytes < pow(2,10)):                             return $bytes.' Bytes';
            case ($bytes >= pow(2,10) && $bytes < pow(2,20)):      return round($bytes / pow(2,10), 0).' KB';
            case ($bytes >= pow(2,20) && $bytes < pow(2,30)):      return round($bytes / pow(2,20), 1).' MB';
            case ($bytes > pow(2,30)):                             return round($bytes / pow(2,30), 2).' GB';
        }
    }
    // }}}

    // {{{ delete()
    /**
    * Wrapper function for {@link imap_delete}.  Sets the marked for deletion flag.  Note: POP3
    * mailboxes do not remember flag settings between connections, for POP3 mailboxes
    * this function should be used in addtion to {@link expunge}.
    *
    * @param    int     &$mid   message id
    * @return   TRUE|PEAR_Error
    * @access   public
    * @see      imap_delete
    * @see      expunge
    */
    function delete(&$mid, $separator = "<br />\n")
    {
        if (!is_array($mid)) {
            return (imap_delete($this->mailbox, $mid))? TRUE : PEAR::raiseError('Mail_IMAP::delete: Unable to mark message: '.$mid.' for deletion.');
        } else {
            foreach ($mid as $id) {
                if (!imap_delete($this->mailbox, $id)) {
                    $stack[] = 'Mail_IMAP::delete: Unable to mark message: '.$id."for deletion.";
                }
            }
            return (isset($stack) && is_array($stack))? PEAR::raiseError(implode($separator, $stack)) : TRUE;
        }
    }
    // }}}

    // {{{ expunge()
    /**
    * Wrapper function for {@link imap_expunge}.  Expunges messages marked for deletion.
    *
    * @return   TRUE|PEAR_Error
    * @access   public
    * @see      imap_expunge
    * @see      delete
    */
    function expunge()
    {
        return (imap_expunge($this->mailbox))? TRUE : PEAR::raiseError('Mail_IMAP::expunge: Unable to expunge mailbox.');
    }
    // }}}

    // {{{ errors()
    /**
    * Wrapper function for {@link imap_errors}.  Implodes the array returned by imap_errors,
    * (if any) and returns the error text.
    *
    * @param    string    $seperator     Characters to seperate each error message. '<br />\n' by default.
    * @return   string|FALSE
    * @access   public
    * @see      imap_errors
    * @see      alerts
    */
    function errors($seperator = "<br />\n")
    {
        $errors = imap_errors();
        return (is_array($errors) && !empty($errors))? implode($seperator, $errors) : FALSE;
    }
    // }}}

    // {{{ alerts()
    /**
    * Wrapper function for {@link imap_alerts}.  Implodes the array returned by imap_alerts,
    * (if any) and returns the text.
    *
    * @param    string    $seperator     Characters to seperate each alert message. '<br />\n' by default.
    * @return   string|FALSE
    * @access   public
    * @see      imap_alerts
    * @see      errors
    */
    function alerts($seperator = "<br />\n")
    {
        $alerts = imap_alerts();
        return (is_array($alerts) && !empty($alerts))? implode($seperator, $alerts) : FALSE;
    }
    // }}}

    // {{{ getQuota()
    /**
    * Retreives information about the current mailbox's quota.  Rounds up quota sizes and
    * appends the unit of measurment.  Returns information in a multi-dimensional associative
    * array.
    *
    * @param    string   $folder    Folder to retrieve quota for.
    * @return   array|PEAR_Error
    * @throws   Quota not available on this server.  Remedy: none.
    * @access   public
    * @see      imap_get_quotaroot
    */
    function getQuota($folder = NULL)
    {
        if (empty($folder) && !isset($this->mailboxInfo['folder'])) {
            $folder = 'INBOX';
        } else if (empty($folder) && isset($this->mailboxInfo['folder'])) {
            $folder = $this->mailboxInfo['folder'];
        }

        $quota = @imap_get_quotaroot($this->mailbox, $folder);

        // STORAGE Values are returned in KB
        // Convert back to bytes first
        // Then round these to the simpliest unit of measurement
        if (isset($quota['STORAGE']['usage']) && isset($quota['STORAGE']['limit'])) {
            $rtn['STORAGE']['usage'] = Mail_IMAP::convertBytes($quota['STORAGE']['usage'] * 1024);
            $rtn['STORAGE']['limit'] = Mail_IMAP::convertBytes($quota['STORAGE']['limit'] * 1024);
        }
        if (isset($quota['MESSAGE']['usage']) && isset($quota['MESSAGE']['limit'])) {
            $rtn['MESSAGE']['usage'] = Mail_IMAP::convertBytes($quota['MESSAGE']['usage']);
            $rtn['MESSAGE']['limit'] = Mail_IMAP::convertBytes($quota['MESSAGE']['limit']);
        }

        return (empty($quota['STORAGE']['usage']) && empty($quota['STORAGE']['limit']))? PEAR::raiseError('Mail_IMAP::getQuota: Quota not available for this server.') : $rtn;
    }
    // }}}

    // {{{ setFlags()
    /**
    * Wrapper function for {@link imap_setflag_full}.  Sets various message flags.
    * Accepts an array of message ids and an array of flags to be set.
    *
    * The flags which you can set are "\\Seen", "\\Answered", "\\Flagged",
    * "\\Deleted", and "\\Draft" (as defined by RFC2060).
    *
    * Warning: POP3 mailboxes do not remember flag settings from connection to connection.
    *
    * @param    array  $mids        Array of message ids to set flags on.
    * @param    array  $flags       Array of flags to set on messages.
    * @param    int    $action      Flag operation toggle one of MAIL_IMAP_SET_FLAGS (default) or
    *                               MAIL_IMAP_CLEAR_FLAGS.
    * @param    int    $options
    *   (optional) sets the forth argument of {@link imap_setflag_full} or {@imap_clearflag_full}.
    *
    * @return   BOOL|PEAR_Error
    * @throws   Message IDs and Flags are to be supplied as arrays.  Remedy: place message ids
    *           and flags in arrays.
    * @access   public
    * @see      imap_setflag_full
    * @see      imap_clearflag_full
    */
    function setFlags($mids, $flags, $action = 3, $options = NULL)
    {
        if (is_array($mids) && is_array($flags)) {
            if ($action == MAIL_IMAP_SET_FLAGS) {

                if (isset($this->option['setflag_full'])) {
                    $options = $this->option['setflag_full'];
                }

                return @imap_setflag_full($this->mailbox, implode(',', $mids), implode(' ', $flags), $options);
            } else {

                if (isset($this->option['clearflag_full'])) {
                    $options = $this->option['clearflag_full'];
                }

                return @imap_clearflag_full($this->mailbox, implode(',', $mids), implode(' ', $flags), $options);
            }
        } else {
            return PEAR::raiseError('Mail_IMAP::setFlags: First and second arguments must be arrays.');
        }
    }
    // }}}

    // {{{ debug()
    /**
    * Dumps various information about a message for debugging. Mail_IMAP::debug
    * is called automatically from Mail_IMAP::connect if $_GET['dump_mid'] isset
    * and MAIL_IMAP_ERROR_REPORTING == E_ALL || MAIL_IMAP_E_DEBUG.
    *
    * $_GET['dump_pid'] - var_dump the $this->_pid[$mid] variable.
    * $_GET['dump_structure'] - var_dump the structure returned by imap_fetchstructure.
    * $_GET['test_pid'] - output the body returned by imap_fetchbody.
    *
    * Calling on the debugger exits script execution after debugging operations
    * have been completed.
    *
    * @param    int  $mid         $mid to debug
    * @return   void
    * @access   public
    */
    function debug($mid = 0)
    {
        Mail_IMAP::_checkIfParsed($mid);

        if (isset($_GET['dump_cid'])) {
            Mail_IMAP::dump($this->_inlineId[$mid]);
        }
        if (isset($_GET['dump_pid'])) {
            Mail_IMAP::dump($this->_pid[$mid]);
        }
        if (isset($_GET['dump_ftype'])) {
            Mail_IMAP::dump($this->_ftype[$mid]);
        }
        if (isset($_GET['dump_structure'])) {
            Mail_IMAP::dump(imap_fetchstructure($this->mailbox, $mid, NULL));
        }
        if (isset($_GET['test_pid'])) {
            echo imap_fetchbody($this->mailbox, $mid, $_GET['test_pid'], NULL);
        }
        if (isset($_GET['dump_mb_list'])) {
            Mail_IMAP::dump(Mail_IMAP::getMailboxes());
        }
        if (isset($_GET['dump_mb_info'])) {
            Mail_IMAP::dump($this->mailboxInfo);
        }

        // Skip everything else in debug mode
        exit;
    }
    // }}}

    // {{{ dump()
    /**
    * Calls on var_dump and outputs with HTML <pre> tags.
    *
    * @param    mixed  $thing         $thing to dump.
    * @return   void
    * @access   public
    */
    function dump(&$thing)
    {
        echo "<pre>\n";
        var_dump($thing);
        echo "</pre><br />\n";
    }
    // }}}

    // {{{ getMailboxes()
    /**
    * Wrapper method for imap_list.  Calling on this function will return a list of mailboxes.
    * This method receives the host argument automatically via Mail_IMAP::connect in the
    * $this->mailboxInfo['host'] variable if a connection URI is used.
    *
    * @param    string  (optional) host name.
    * @return   array   list of mailboxes on the current server.
    * @access   public
    * @see      imap_list
    */
    function getMailboxes($host = NULL, $pattern = '*')
    {
        if (empty($host) && !isset($this->mailboxInfo['host'])) {
            return PEAR::raiseError('Mail_IMAP::getMailboxes: Supplied host is not valid!');
        } else if (empty($host) && isset($this->mailboxInfo['host'])) {
            $host = $this->mailboxInfo['host'];
        }

        if ($list = @imap_list($this->mailbox, $host, $pattern)) {
            if (is_array($list)) {
                foreach ($list as $val) {
                    $ret[] = str_replace($host, '', imap_utf7_decode($val));
                }
            }
        } else {
            $ret = PEAR::raiseError('Mail_IMAP::getMailboxes: Cannot fetch mailbox names.');
        }

        return $ret;
    }
    // }}}
}
// }}}
?>