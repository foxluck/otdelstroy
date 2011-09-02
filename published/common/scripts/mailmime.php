<?php
/**
 * The Mail_Mime class is used to create MIME E-mail messages
 *
 * The Mail_Mime class provides an OO interface to create MIME
 * enabled email messages. This way you can create emails that
 * contain plain-text bodies, HTML bodies, attachments, inline
 * images and specific headers.
 */

class Mail_mime
{
	/**
	 * Contains the plain text part of the email
	 *
	 * @var string
	 * @access private
	 */
	var $_txtbody;

	/**
	 * Contains the html part of the email
	 *
	 * @var string
	 * @access private
	 */
	var $_htmlbody;

	/**
	 * contains the mime encoded text
	 *
	 * @var string
	 * @access private
	 */
	var $_mime;

	/**
	 * contains the multipart content
	 *
	 * @var string
	 * @access private
	 */
	var $_multipart;

	/**
	 * list of the attached images
	 *
	 * @var array
	 * @access private
	 */
	var $_html_images = array();

	/**
	 * list of the attachements
	 *
	 * @var array
	 * @access private
	 */
	var $_parts = array();

	/**
	 * Build parameters
	 *
	 * @var array
	 * @access private
	 */
	var $_build_params = array();

	/**
	 * Headers for the mail
	 *
	 * @var array
	 * @access private
	 */
	var $_headers = array();

	/**
	 * End Of Line sequence (for serialize)
	 *
	 * @var string
	 * @access private
	 */
	var $_eol;

	/**
	 * Constructor function.
	 *
	 * @param string $crlf what type of linebreak to use.
	 *					 Defaults to "\r\n"
	 *
	 * @return void
	 *
	 * @access public
	 */
	function Mail_mime($crlf = "\r\n")
	{
		$this->_setEOL($crlf);
		$this->_build_params = array(
			'head_encoding' => 'base64',
			'text_encoding' => '8bit',
			'html_encoding' => '8bit',
			'7bit_wrap'	    => 998,
			'html_charset'  => 'utf-8',
			'text_charset'  => 'utf-8',
			'head_charset'  => 'utf-8'
			);
	}

	/**
	 * wakeup function called by unserialize. It re-sets the EOL constant
	 *
	 * @access private
	 * @return void
	 */
	function __wakeup()
	{
		$this->_setEOL($this->_eol);
	}

	/**
	 * Accessor function to set the body text. Body text is used if
	 * it's not an html mail being sent or else is used to fill the
	 * text/plain part that emails clients who don't support
	 * html should show.
	 *
	 * @param string $data   Either a string or
	 *						the file name with the contents
	 * @param bool   $isfile If true the first param should be treated
	 *						as a file name, else as a string (default)
	 * @param bool   $append If true the text or file is appended to
	 *						the existing body, else the old body is
	 *						overwritten
	 *
	 * @return mixed   true on success
	 * @access public
	 */
	function setTXTBody($data, $isfile = false, $append = false)
	{
		if (!$isfile) {
			if (!$append) {
				$this->_txtbody = $data;
			} else {
				$this->_txtbody .= $data;
			}
		} else {
			$cont = $this->_file2str($data);
			if (!$append) {
				$this->_txtbody = $cont;
			} else {
				$this->_txtbody .= $cont;
			}
		}
		return true;
	}

	/**
	 * Adds a html part to the mail.
	 *
	 * @param string $data   either a string or the file name with the
	 *						contents
	 * @param bool   $isfile a flag that determines whether $data is a
	 *						filename, or a string(false, default)
	 *
	 * @return bool	true on success
	 * @access public
	 */
	function setHTMLBody($data, $isfile = false)
	{
		if (!$isfile) {
			$this->_htmlbody = $data;
		} else {
			$cont = $this->_file2str($data);
			$this->_htmlbody = $cont;
		}
		return true;
	}

	/**
	 * Adds an image to the list of embedded images.
	 *
	 * @param string $file   the image file name OR image data itself
	 * @param string $c_type the content type
	 * @param string $name   the filename of the image.
	 *						Only used if $file is the image data.
	 * @param bool   $isfile whether $file is a filename or not.
	 *						Defaults to true
	 *
	 * @return bool		  true on success
	 * @access public
	 */
	function addHTMLImage($file, $c_type='application/octet-stream',
						  $name = '', $isfile = true)
	{
		$filedata = ($isfile === true) ? $this->_file2str($file) : $file;
		if ($isfile === true) {
			$filename = ($name == '' ? $file : $name);
		} else {
			$filename = $name;
		}
		$this->_html_images[] = array(
			'body'   => $filedata,
			'name'   => $filename,
			'c_type' => $c_type,
			'cid'    => md5(uniqid(time()))
			);
		return true;
	}

	/**
	 * Adds a file to the list of attachments.
	 *
	 * @param string $file		The file name of the file to attach
	 *							 OR the file contents itself
	 * @param string $c_type	  The content type
	 * @param string $name		The filename of the attachment
	 *							 Only use if $file is the contents
	 * @param bool   $isfile	  Whether $file is a filename or not
	 *							 Defaults to true
	 * @param string $encoding	The type of encoding to use.
	 *							 Defaults to base64.
	 *							 Possible values: 7bit, 8bit, base64, 
	 *							 or quoted-printable.
	 * @param string $disposition The content-disposition of this file
	 *							 Defaults to attachment.
	 *							 Possible values: attachment, inline.
	 * @param string $charset	 The character set used in the filename
	 *							 of this attachment.
	 * @param string $language	The language of the attachment
	 * @param string $location	The RFC 2557.4 location of the attachment
	 *
	 * @return mixed true on success
	 * @access public
	 */
	function addAttachment(
		$file,
		$c_type      = 'application/octet-stream',
		$name        = '',
		$isfile      = true,
		$encoding    = 'base64',
		$disposition = 'attachment',
		$charset     = '',
		$language    = '',
		$location    = '')
	{
		$filedata = ($isfile === true) ? $this->_file2str($file) : $file;
		if ($isfile === true) {
			// Force the name the user supplied, otherwise use $file
			$filename = (strlen($name)) ? $name : $file;
		} else {
			$filename = $name;
		}
		if (!strlen($filename)) {
			throw new RuntimeException("The supplied filename for the attachment can't be empty");
		}
//		$filename = basename($filename);

		$this->_parts[] = array(
			'body'        => $filedata,
			'name'        => $filename,
			'c_type'      => $c_type,
			'encoding'    => $encoding,
			'charset'     => $charset,
			'language'    => $language,
			'location'    => $location,
			'disposition' => $disposition
			);
		return true;
	}

	/**
	 * Get the contents of the given file name as string
	 *
	 * @param string $file_name path of file to process
	 *
	 * @return string  contents of $file_name
	 * @access private
	 */
	function &_file2str($file_name)
	{
		if (!is_readable($file_name)) {
			throw new RuntimeException('File is not readable ' . $file_name);
		}
		if (!$fd = fopen($file_name, 'rb')) {
			throw new RuntimeException('Could not open ' . $file_name);
		}
		$filesize = filesize($file_name);
		if ($filesize == 0) {
			$cont =  "";
		} else {
			if ($magic_quote_setting = get_magic_quotes_runtime()) {
				set_magic_quotes_runtime(0);
			}
			$cont = fread($fd, $filesize);
			if ($magic_quote_setting) {
				set_magic_quotes_runtime($magic_quote_setting);
			}
		}
		fclose($fd);
		return $cont;
	}

	/**
	 * Adds a text subpart to the mimePart object and
	 * returns it during the build process.
	 *
	 * @param mixed  &$obj The object to add the part to, or
	 *					  null if a new object is to be created.
	 * @param string $text The text to add.
	 *
	 * @return object  The text mimePart object
	 * @access private
	 */
	function &_addTextPart(&$obj, $text)
	{
		$params['content_type'] = 'text/plain';
		$params['encoding']	 = $this->_build_params['text_encoding'];
		$params['charset']	  = $this->_build_params['text_charset'];
		if (is_object($obj)) {
			$ret = $obj->addSubpart($text, $params);
			return $ret;
		} else {
			$ret = new Mail_mimePart($text, $params);
			return $ret;
		}
	}

	/**
	 * Adds a html subpart to the mimePart object and
	 * returns it during the build process.
	 *
	 * @param mixed &$obj The object to add the part to, or
	 *					 null if a new object is to be created.
	 *
	 * @return object The html mimePart object
	 * @access private
	 */
	function &_addHtmlPart(&$obj)
	{
		$params['content_type'] = 'text/html';
		$params['encoding']	 = $this->_build_params['html_encoding'];
		$params['charset']	  = $this->_build_params['html_charset'];
		if (is_object($obj)) {
			$ret = $obj->addSubpart($this->_htmlbody, $params);
			return $ret;
		} else {
			$ret = new Mail_mimePart($this->_htmlbody, $params);
			return $ret;
		}
	}

	/**
	 * Creates a new mimePart object, using multipart/mixed as
	 * the initial content-type and returns it during the
	 * build process.
	 *
	 * @return object The multipart/mixed mimePart object
	 * @access private
	 */
	function &_addMixedPart()
	{
		$params				 = array();
		$params['content_type'] = 'multipart/mixed';
		
		//Create empty multipart/mixed Mail_mimePart object to return
		$ret = new Mail_mimePart('', $params);
		return $ret;
	}

	/**
	 * Adds a multipart/alternative part to a mimePart
	 * object (or creates one), and returns it during
	 * the build process.
	 *
	 * @param mixed &$obj The object to add the part to, or
	 *					 null if a new object is to be created.
	 *
	 * @return object  The multipart/mixed mimePart object
	 * @access private
	 */
	function &_addAlternativePart(&$obj)
	{
		$params['content_type'] = 'multipart/alternative';
		if (is_object($obj)) {
			return $obj->addSubpart('', $params);
		} else {
			$ret = new Mail_mimePart('', $params);
			return $ret;
		}
	}

	/**
	 * Adds a multipart/related part to a mimePart
	 * object (or creates one), and returns it during
	 * the build process.
	 *
	 * @param mixed &$obj The object to add the part to, or
	 *					 null if a new object is to be created
	 *
	 * @return object  The multipart/mixed mimePart object
	 * @access private
	 */
	function &_addRelatedPart(&$obj)
	{
		$params['content_type'] = 'multipart/related';
		if (is_object($obj)) {
			return $obj->addSubpart('', $params);
		} else {
			$ret = new Mail_mimePart('', $params);
			return $ret;
		}
	}

	/**
	 * Adds an html image subpart to a mimePart object
	 * and returns it during the build process.
	 *
	 * @param object &$obj  The mimePart to add the image to
	 * @param array  $value The image information
	 *
	 * @return object  The image mimePart object
	 * @access private
	 */
	function &_addHtmlImagePart(&$obj, $value)
	{
		$params['content_type'] = $value['c_type'];
		$params['encoding']	 = 'base64';
		$params['disposition']  = 'inline';
		$params['dfilename']	= $value['name'];
		$params['cid']		  = $value['cid'];
		
		$ret = $obj->addSubpart($value['body'], $params);
		return $ret;
	
	}

	/**
	 * Adds an attachment subpart to a mimePart object
	 * and returns it during the build process.
	 *
	 * @param object &$obj  The mimePart to add the image to
	 * @param array  $value The attachment information
	 *
	 * @return object  The image mimePart object
	 * @access private
	 */
	function &_addAttachmentPart(&$obj, $value)
	{
		$params['dfilename'] = $value['name'];
		$params['encoding']  = $value['encoding'];
		if ($value['charset']) {
			$params['charset'] = $value['charset'];
		}
		if ($value['language']) {
			$params['language'] = $value['language'];
		}
		if ($value['location']) {
			$params['location'] = $value['location'];
		}
		$params['content_type'] = $value['c_type'];
		$params['disposition']  = isset($value['disposition']) ? 
								  $value['disposition'] : 'attachment';
		$ret = $obj->addSubpart($value['body'], $params);
		return $ret;
	}

	/**
	 * Returns the complete e-mail, ready to send using an alternative
	 * mail delivery method. Note that only the mailpart that is made
	 * with Mail_Mime is created. This means that,
	 * YOU WILL HAVE NO TO: HEADERS UNLESS YOU SET IT YOURSELF 
	 * using the $xtra_headers parameter!
	 * 
	 * @param string $separation   The separation etween these two parts.
	 * @param array  $build_params The Build parameters passed to the
	 *							 &get() function. See &get for more info.
	 * @param array  $xtra_headers The extra headers that should be passed
	 *							 to the &headers() function.
	 *							 See that function for more info.
	 * @param bool   $overwrite	Overwrite the existing headers with new.
	 *
	 * @return string The complete e-mail.
	 * @access public
	 */
	function getMessage(
		$separation   = null, 
		$build_params = null, 
		$xtra_headers = null, 
		$overwrite    = false
		)
	{
		if ($separation === null) {
			$separation = MAIL_MIME_CRLF;
		}
		$body = $this->get($build_params);
		$head = $this->txtHeaders($xtra_headers, $overwrite);
		$mail = $head . $separation . $body;
		return $mail;
	}

	/**
	 * Builds the multipart message from the list ($this->_parts) and
	 * returns the mime content.
	 *
	 * @param array $build_params Build parameters that change the way the email
	 *							 is built. Should be associative. Can contain:
	 *				head_encoding  -  What encoding to use for the headers. 
	 *								  Options: quoted-printable or base64
	 *								  Default is quoted-printable
	 *				text_encoding  -  What encoding to use for plain text
	 *								  Options: 7bit, 8bit,
	 *								  base64, or quoted-printable
	 *								  Default is 7bit
	 *				html_encoding  -  What encoding to use for html
	 *								  Options: 7bit, 8bit,
	 *								  base64, or quoted-printable
	 *								  Default is quoted-printable
	 *				7bit_wrap	  -  Number of characters before text is
	 *								  wrapped in 7bit encoding
	 *								  Default is 998
	 *				html_charset   -  The character set to use for html.
	 *								  Default is iso-8859-1
	 *				text_charset   -  The character set to use for text.
	 *								  Default is iso-8859-1
	 *				head_charset   -  The character set to use for headers.
	 *								  Default is iso-8859-1
	 *
	 * @return string The mime content
	 * @access public
	 */
	function &get($build_params = null)
	{
		if (isset($build_params)) {
			while (list($key, $value) = each($build_params)) {
				$this->_build_params[$key] = $value;
			}
		}
		
		if (isset($this->_headers['From'])){
			$domain = @strstr($this->_headers['From'],'@');
			//Bug #11381: Illegal characters in domain ID
			$domain = str_replace(array("<", ">", "&", "(", ")", " ", "\"", "'"), "", $domain);
			$domain = urlencode($domain);
			foreach($this->_html_images as $i => $img){
				$this->_html_images[$i]['cid'] = $this->_html_images[$i]['cid'] . $domain;
			}
		}
		
		if (count($this->_html_images) AND isset($this->_htmlbody)) {
			foreach ($this->_html_images as $key => $value) {
				$regex   = array();
				$regex[] = '#(\s)((?i)src|background|href(?-i))\s*=\s*(["\']?)' .
							preg_quote($value['name'], '#') . '\3#';
				$regex[] = '#(?i)url(?-i)\(\s*(["\']?)' .
							preg_quote($value['name'], '#') . '\1\s*\)#';

				$rep   = array();
				$rep[] = '\1\2=\3cid:' . $value['cid'] .'\3';
				$rep[] = 'url(\1cid:' . $value['cid'] . '\2)';

				$this->_htmlbody = preg_replace($regex, $rep, $this->_htmlbody);
				$this->_html_images[$key]['name'] = 
					basename($this->_html_images[$key]['name']);
			}
		}

		$null        = null;
		$attachments = count($this->_parts)                 ? true : false;
		$html_images = count($this->_html_images)           ? true : false;
		$html        = strlen($this->_htmlbody)             ? true : false;
		$text        = (!$html AND strlen($this->_txtbody)) ? true : false;

		switch (true) {
		case $text AND !$attachments:
			$message =& $this->_addTextPart($null, $this->_txtbody);
			break;

		case !$text AND !$html AND $attachments:
			$message =& $this->_addMixedPart();
			for ($i = 0; $i < count($this->_parts); $i++) {
				$this->_addAttachmentPart($message, $this->_parts[$i]);
			}
			break;

		case $text AND $attachments:
			$message =& $this->_addMixedPart();
			$this->_addTextPart($message, $this->_txtbody);
			for ($i = 0; $i < count($this->_parts); $i++) {
				$this->_addAttachmentPart($message, $this->_parts[$i]);
			}
			break;

		case $html AND !$attachments AND !$html_images:
			if (isset($this->_txtbody)) {
				$message =& $this->_addAlternativePart($null);
				$this->_addTextPart($message, $this->_txtbody);
				$this->_addHtmlPart($message);
			} else {
				$message =& $this->_addHtmlPart($null);
			}
			break;

		case $html AND !$attachments AND $html_images:
			$message =& $this->_addRelatedPart($null);
			if (isset($this->_txtbody)) {
				$alt =& $this->_addAlternativePart($message);
				$this->_addTextPart($alt, $this->_txtbody);
				$this->_addHtmlPart($alt);
			} else {
				$this->_addHtmlPart($message);
			}
			for ($i = 0; $i < count($this->_html_images); $i++) {
				$this->_addHtmlImagePart($message, $this->_html_images[$i]);
			}
			break;

		case $html AND $attachments AND !$html_images:
			$message =& $this->_addMixedPart();
			if (isset($this->_txtbody)) {
				$alt =& $this->_addAlternativePart($message);
				$this->_addTextPart($alt, $this->_txtbody);
				$this->_addHtmlPart($alt);
			} else {
				$this->_addHtmlPart($message);
			}
			for ($i = 0; $i < count($this->_parts); $i++) {
				$this->_addAttachmentPart($message, $this->_parts[$i]);
			}
			break;

		case $html AND $attachments AND $html_images:
			$message =& $this->_addMixedPart();
			if (isset($this->_txtbody)) {
				$alt =& $this->_addAlternativePart($message);
				$this->_addTextPart($alt, $this->_txtbody);
				$rel =& $this->_addRelatedPart($alt);
			} else {
				$rel =& $this->_addRelatedPart($message);
			}
			$this->_addHtmlPart($rel);
			for ($i = 0; $i < count($this->_html_images); $i++) {
				$this->_addHtmlImagePart($rel, $this->_html_images[$i]);
			}
			for ($i = 0; $i < count($this->_parts); $i++) {
				$this->_addAttachmentPart($message, $this->_parts[$i]);
			}
			break;
		}

		if (isset($message)) {
			$output = $message->encode();
			
			$this->_headers = array_merge($this->_headers, $output['headers']);
			$body = $output['body'];
			return $body;

		} else {
			$ret = false;
			return $ret;
		}
	}

	/**
	 * Returns an array with the headers needed to prepend to the email
	 * (MIME-Version and Content-Type). Format of argument is:
	 * $array['header-name'] = 'header-value';
	 *
	 * @param array $xtra_headers Assoc array with any extra headers.
	 *							 Optional.
	 * @param bool  $overwrite	Overwrite already existing headers.
	 * 
	 * @return array Assoc array with the mime headers
	 * @access public
	 */
	function &headers($xtra_headers = null, $overwrite = false)
	{
		// Content-Type header should already be present,
		// So just add mime version header
		$headers['MIME-Version'] = '1.0';
		if (isset($xtra_headers)) {
			$headers = array_merge($headers, $xtra_headers);
		}
		if ($overwrite) {
			$this->_headers = array_merge($this->_headers, $headers);
		} else {
			$this->_headers = array_merge($headers, $this->_headers);
		}
		return $this->_headers;
	}

	/**
	 * Get the text version of the headers
	 * (usefull if you want to use the PHP mail() function)
	 *
	 * @param array $xtra_headers Assoc array with any extra headers.
	 *							 Optional.
	 * @param bool  $overwrite	Overwrite the existing heaers with new.
	 *
	 * @return string  Plain text headers
	 * @access public
	 */
	function txtHeaders($xtra_headers = null, $overwrite = false)
	{
		$headers = $this->headers($xtra_headers, $overwrite);
		
		$ret = '';
		foreach ($headers as $key => $val) {
			$ret .= "$key: $val" . MAIL_MIME_CRLF;
		}
		return $ret;
	}

	/**
	 * Sets the Subject header
	 *
	 * @param string $subject String to set the subject to.
	 *
	 * @return void
	 * @access public
	 */
	function setSubject($subject)
	{
		$this->_headers['Subject'] = $subject;
	}

	/**
	 * Set an email to the From (the sender) header
	 *
	 * @param string $email The email address to use
	 *
	 * @return void
	 * @access public
	 */
	function setFrom($email)
	{
		$this->_headers['From'] = $email;
	}

	/**
	 * Add an email to the Cc (carbon copy) header
	 * (multiple calls to this method are allowed)
	 *
	 * @param string $email The email direction to add
	 *
	 * @return void
	 * @access public
	 */
	function addCc($email)
	{
		if (isset($this->_headers['Cc'])) {
			$this->_headers['Cc'] .= ", $email";
		} else {
			$this->_headers['Cc'] = $email;
		}
	}

	/**
	 * Add an email to the Bcc (blank carbon copy) header
	 * (multiple calls to this method are allowed)
	 *
	 * @param string $email The email direction to add
	 *
	 * @return void
	 * @access public
	 */
	function addBcc($email)
	{
		if (isset($this->_headers['Bcc'])) {
			$this->_headers['Bcc'] .= ", $email";
		} else {
			$this->_headers['Bcc'] = $email;
		}
	}

	/**
	 * Set the object's end-of-line and define the constant if applicable.
	 *
	 * @param string $eol End Of Line sequence
	 *
	 * @return void
	 * @access private
	 */
	function _setEOL($eol)
	{
		$this->_eol = $eol;
		if (!defined('MAIL_MIME_CRLF')) {
			define('MAIL_MIME_CRLF', $this->_eol, true);
		}
	}

} // End of class

// *****************************************************************************

/**
 * The Mail_mimePart class is used to create MIME E-mail messages
 *
 * This class enables you to manipulate and build a mime email
 * from the ground up. The Mail_Mime class is a userfriendly api
 * to this class for people who aren't interested in the internals
 * of mime mail.
 * This class however allows full control over the email.
 */

class Mail_mimePart {

   /**
	* The encoding type of this part
	*
	* @var string
	* @access private
	*/
	var $_encoding;

   /**
	* An array of subparts
	*
	* @var array
	* @access private
	*/
	var $_subparts;

   /**
	* The output of this part after being built
	*
	* @var string
	* @access private
	*/
	var $_encoded;

   /**
	* Headers for this part
	*
	* @var array
	* @access private
	*/
	var $_headers;

   /**
	* The body of this part (not encoded)
	*
	* @var string
	* @access private
	*/
	var $_body;

	/**
	 * Constructor.
	 *
	 * Sets up the object.
	 *
	 * @param $body   - The body of the mime part if any.
	 * @param $params - An associative array of parameters:
	 *				  content_type - The content type for this part eg multipart/mixed
	 *				  encoding	 - The encoding to use, 7bit, 8bit, base64, or quoted-printable
	 *				  cid		  - Content ID to apply
	 *				  disposition  - Content disposition, inline or attachment
	 *				  dfilename	- Optional filename parameter for content disposition
	 *				  description  - Content description
	 *				  charset	  - Character set to use
	 * @access public
	 */
	function Mail_mimePart($body = '', $params = array())
	{
		if (!defined('MAIL_MIMEPART_CRLF')) {
			define('MAIL_MIMEPART_CRLF', defined('MAIL_MIME_CRLF') ? MAIL_MIME_CRLF : "\r\n", TRUE);
		}

		$contentType = array();
		$contentDisp = array();
		foreach ($params as $key => $value) {
			switch ($key) {
				case 'content_type':
					$contentType['type'] = $value;
					//$headers['Content-Type'] = $value . (isset($charset) ? '; charset="' . $charset . '"' : '');
					break;

				case 'encoding':
					$this->_encoding = $value;
					$headers['Content-Transfer-Encoding'] = $value;
					break;

				case 'cid':
					$headers['Content-ID'] = '<' . $value . '>';
					break;

				case 'disposition':
					$contentDisp['disp'] = $value;
					break;

				case 'dfilename':
					$contentDisp['filename'] = $value;
					$contentType['name'] = $value;
					break;

				case 'description':
					$headers['Content-Description'] = $value;
					break;

				case 'charset':
					$contentType['charset'] = $value;
					$contentDisp['charset'] = $value;
					break;

				case 'language':
					$contentType['language'] = $value;
					$contentDisp['language'] = $value;
					break;

				case 'location':
					$headers['Content-Location'] = $value;
					break;

			}
		}
		if (isset($contentType['type'])) {
			$headers['Content-Type'] = $contentType['type'];
			if (isset($contentType['name'])) {
				$headers['Content-Type'] .= ';' . MAIL_MIMEPART_CRLF . ' name="' . $contentType['name'] . '";';
			} elseif (isset($contentType['charset'])) {
				$headers['Content-Type'] .= "; charset=\"{$contentType['charset']}\"";
			}
		}

		if (isset($contentDisp['disp'])) {
			$headers['Content-Disposition'] = $contentDisp['disp'];
			if (isset($contentDisp['filename'])) {
				$headers['Content-Disposition'] .= ';' . MAIL_MIMEPART_CRLF . ' filename="' . $contentDisp['filename'] . '";';
			}
		}
		
		// Default content-type
		if (!isset($headers['Content-Type'])) {
			$headers['Content-Type'] = 'text/plain';
		}

		//Default encoding
		if (!isset($this->_encoding)) {
			$this->_encoding = '7bit';
		}

		// Assign stuff to member variables
		$this->_encoded  = array();
		$this->_headers  = $headers;
		$this->_body	 = $body;
	}

	/**
	 * encode()
	 *
	 * Encodes and returns the email. Also stores
	 * it in the encoded member variable
	 *
	 * @return An associative array containing two elements,
	 *		 body and headers. The headers element is itself
	 *		 an indexed array.
	 * @access public
	 */
	function encode()
	{
		$encoded =& $this->_encoded;

		if (count($this->_subparts)) {
			srand((double)microtime()*1000000);
			$boundary = '=_' . md5(rand() . microtime());
			$this->_headers['Content-Type'] .= ';' . MAIL_MIMEPART_CRLF . "\t" . 'boundary="' . $boundary . '"';

			// Add body parts to $subparts
			for ($i = 0; $i < count($this->_subparts); $i++) {
				$headers = array();
				$tmp = $this->_subparts[$i]->encode();
				foreach ($tmp['headers'] as $key => $value) {
					$headers[] = $key . ': ' . $value;
				}
				$subparts[] = implode(MAIL_MIMEPART_CRLF, $headers) . MAIL_MIMEPART_CRLF . MAIL_MIMEPART_CRLF . $tmp['body'] . MAIL_MIMEPART_CRLF;
			}

			$encoded['body'] = '--' . $boundary . MAIL_MIMEPART_CRLF . 
							   rtrim(implode('--' . $boundary . MAIL_MIMEPART_CRLF , $subparts), MAIL_MIMEPART_CRLF) . MAIL_MIMEPART_CRLF . 
							   '--' . $boundary.'--' . MAIL_MIMEPART_CRLF;

		} else {
			$encoded['body'] = $this->_getEncodedData($this->_body, $this->_encoding);
		}

		// Add headers to $encoded
		$encoded['headers'] =& $this->_headers;

		return $encoded;
	}

	/**
	 * &addSubPart()
	 *
	 * Adds a subpart to current mime part and returns
	 * a reference to it
	 *
	 * @param $body   The body of the subpart, if any.
	 * @param $params The parameters for the subpart, same
	 *				as the $params argument for constructor.
	 * @return A reference to the part you just added. It is
	 *		 crucial if using multipart/* in your subparts that
	 *		 you use =& in your script when calling this function,
	 *		 otherwise you will not be able to add further subparts.
	 * @access public
	 */
	function &addSubPart($body, $params)
	{
		$this->_subparts[] = new Mail_mimePart($body, $params);
		return $this->_subparts[count($this->_subparts) - 1];
	}

	/**
	 * _getEncodedData()
	 *
	 * Returns encoded data based upon encoding passed to it
	 *
	 * @param $data	 The data to encode.
	 * @param $encoding The encoding type to use, 7bit, base64,
	 *				  or quoted-printable.
	 * @access private
	 */
	function _getEncodedData($data, $encoding)
	{
		switch ($encoding) {
			case '8bit':
			case '7bit':
				return $data;
				break;

			case 'quoted-printable':
				return $this->_quotedPrintableEncode($data);
				break;

			case 'base64':
				return rtrim(chunk_split(base64_encode($data), 76, MAIL_MIMEPART_CRLF));
				break;

			default:
				return $data;
		}
	}

	/**
	 * quotedPrintableEncode()
	 *
	 * Encodes data to quoted-printable standard.
	 *
	 * @param $input	The data to encode
	 * @param $line_max Optional max line length. Should
	 *				  not be more than 76 chars
	 *
	 * @access private
	 */
	function _quotedPrintableEncode($input , $line_max = 76)
	{
		$lines  = preg_split("/\r?\n/", $input);
		$eol	= MAIL_MIMEPART_CRLF;
		$escape = '=';
		$output = '';

		while (list(, $line) = each($lines)) {

			$line	= preg_split('||', $line, -1, PREG_SPLIT_NO_EMPTY);
			$linlen	 = count($line);
			$newline = '';

			for ($i = 0; $i < $linlen; $i++) {
				$char = $line[$i];
				$dec  = ord($char);

				if (($dec == 32) AND ($i == ($linlen - 1))) {	// convert space at eol only
					$char = '=20';

				} elseif (($dec == 9) AND ($i == ($linlen - 1))) {  // convert tab at eol only
					$char = '=09';
				} elseif ($dec == 9) {
					; // Do nothing if a tab.
				} elseif (($dec == 61) OR ($dec < 32 ) OR ($dec > 126)) {
					$char = $escape . strtoupper(sprintf('%02s', dechex($dec)));
				} elseif (($dec == 46) AND ($newline == '')) {
					//Bug #9722: convert full-stop at bol
					//Some Windows servers need this, won't break anything (cipri)
					$char = '=2E';
				}

				if ((strlen($newline) + strlen($char)) >= $line_max) {		// MAIL_MIMEPART_CRLF is not counted
					$output  .= $newline . $escape . $eol;					// soft line break; " =\r\n" is okay
					$newline  = '';
				}
				$newline .= $char;
			} // end of for
			$output .= $newline . $eol;
		}
		$output = substr($output, 0, -1 * strlen($eol)); // Don't want last crlf
		return $output;
	}

} // End of class
