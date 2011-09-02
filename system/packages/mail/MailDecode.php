<?php
/**
 * The Mail MailDecode class is used to decode mail/mime messages
 *
 * This class will parse a raw mime email and return the structure.
 * Returned structure is similar to that returned by imap_fetchstructure().
 */
class MailDecode
{
	/**
	 * The raw email to decode
	 *
	 * @var	string
	 * @access private
	 */
	var $_input;

	/**
	 * The header part of the input
	 *
	 * @var	string
	 * @access private
	 */
	var $_header;

	/**
	 * The body part of the input
	 *
	 * @var	string
	 * @access private
	 */
	var $_body;

	/**
	 * If an error occurs, this is used to store the message
	 *
	 * @var	string
	 * @access private
	 */
	var $_error;

	/**
	 * Constructor.
	 *
	 * Sets up the object, initialise the variables, and splits and
	 * stores the header and body of the input.
	 *
	 * @param string The input to decode
	 * @access public
	 */
	function MailDecode($input)
	{
		list($header, $body) = $this->_splitBodyHeader($input);

		$this->_input  = $input;
		$this->_header = $header;
		$this->_body   = $body;
	}

	/**
	 * Begins the decoding process. If called statically
	 * it will create an object and call the decode() method of it.
	 *
	 * @return object Decoded results
	 * @access public
	 */
	function decode()
	{
		return $this->_decode($this->_header, $this->_body);
	}

	/**
	 * Performs the decoding. Decodes the body string passed to it
	 * If it finds certain content-types it will call itself in a
	 * recursive fashion
	 *
	 * @param string Header section
	 * @param string Body section
	 * @return object Results of decoding process
	 * @access private
	 */
	function _decode($headers, $body, $default_ctype = 'text/plain')
	{
		$return = new stdClass;
		$return->headers = array();
		$headers = $this->_parseHeaders($headers);

		foreach($headers as $value) {
			if (isset($return->headers[strtolower($value['name'])]) AND !is_array($return->headers[strtolower($value['name'])])) {
				$return->headers[strtolower($value['name'])]   = array($return->headers[strtolower($value['name'])]);
				$return->headers[strtolower($value['name'])][] = $value['value'];

			} elseif (isset($return->headers[strtolower($value['name'])])) {
				$return->headers[strtolower($value['name'])][] = $value['value'];

			} else {
				$return->headers[strtolower($value['name'])] = $value['value'];
			}
		}

		reset($headers);
		foreach($headers as $key=>$value) {
			$headers[$key]['name'] = strtolower($headers[$key]['name']);
			switch ($headers[$key]['name']) {

				case 'content-type':
					$content_type = $this->_parseHeaderValue($headers[$key]['value']);

					if (preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]+)/i', $content_type['value'], $regs)) {
						$return->ctype_primary   = $regs[1];
						$return->ctype_secondary = $regs[2];
					}

					if (isset($content_type['other'])) {
						foreach($content_type['other'] as $p_name=>$p_value) {
							$return->ctype_parameters[$p_name] = $p_value;
						}
					}
					break;

				case 'content-disposition':
					$content_disposition = $this->_parseHeaderValue($headers[$key]['value']);
					$return->disposition   = $content_disposition['value'];
					if (isset($content_disposition['other'])) {
						foreach($content_disposition['other'] as $p_name=>$p_value) {
							$return->d_parameters[$p_name] = $p_value;
						}
					}
					break;

				case 'content-transfer-encoding':
					$content_transfer_encoding = $this->_parseHeaderValue($headers[$key]['value']);
					break;
			}
		}

		if (isset($content_type)) {
			switch (strtolower($content_type['value'])) {
				case 'text/plain':
					$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
					$return->body = $this->_decodeBody($body, $encoding);
					break;

				case 'text/html':
					$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
					$return->body = $this->_decodeBody($body, $encoding);
					break;

				case 'multipart/parallel':
				case 'multipart/appledouble': // Appledouble mail
				case 'multipart/report': // RFC1892
				case 'multipart/signed': // PGP
				case 'multipart/digest':
				case 'multipart/alternative':
				case 'multipart/related':
				case 'multipart/mixed':

					if(!isset($content_type['other']['boundary'])){
						$this->_error = 'No boundary found for ' . $content_type['value'] . ' part';
						return false;
					}

					$default_ctype = (strtolower($content_type['value']) === 'multipart/digest') ? 'message/rfc822' : 'text/plain';

					$parts = $this->_boundarySplit($body, $content_type['other']['boundary']);

					for($i = 0; $i < count($parts); $i++) {
						list($part_header, $part_body) = $this->_splitBodyHeader($parts[$i]);
						$part = $this->_decode($part_header, $part_body, $default_ctype);
						$return->parts[] = $part;
					}
					break;

				case 'message/rfc822':
					$obj = new MailDecode($body);
					$return->parts[] = $obj->decode();
					unset($obj);
					break;

				default:
					if(!isset($content_transfer_encoding['value']))
						$content_transfer_encoding['value'] = '7bit';
					$return->body = $this->_decodeBody($body, $content_transfer_encoding['value']);
					break;
			}

		} else {
			$ctype = explode('/', $default_ctype);
			$return->ctype_primary   = $ctype[0];
			$return->ctype_secondary = $ctype[1];
			$return->body = $this->_decodeBody($body);
		}

		return $return;
	}

	/**
	 * Given the output of the above function, this will return an
	 * array of references to the parts, indexed by mime number.
	 *
	 * @param  object $structure   The structure to go through
	 * @param  string $mime_number Internal use only.
	 * @return array			   Mime numbers
	 */
	function &getMimeNumbers(&$structure, $no_refs = false, $mime_number = '', $prepend = '')
	{
		$return = array();
		if (!empty($structure->parts)) {
			if ($mime_number != '') {
				$structure->mime_id = $prepend . $mime_number;
				$return[$prepend . $mime_number] = &$structure;
			}
			for ($i = 0; $i < count($structure->parts); $i++) {

			
				if (!empty($structure->headers['content-type']) AND substr(strtolower($structure->headers['content-type']), 0, 8) == 'message/') {
					$prepend	  = $prepend . $mime_number . '.';
					$_mime_number = '';
				} else {
					$_mime_number = ($mime_number == '' ? $i + 1 : sprintf('%s.%s', $mime_number, $i + 1));
				}

				$arr = &MailDecode::getMimeNumbers($structure->parts[$i], $no_refs, $_mime_number, $prepend);
				foreach ($arr as $key => $val) {
					$no_refs ? $return[$key] = '' : $return[$key] = &$arr[$key];
				}
			}
		} else {
			if ($mime_number == '') {
				$mime_number = '1';
			}
			$structure->mime_id = $prepend . $mime_number;
			$no_refs ? $return[$prepend . $mime_number] = '' : $return[$prepend . $mime_number] = &$structure;
		}
		
		return $return;
	}

	/**
	 * Given a string containing a header and body
	 * section, this function will split them (at the first
	 * blank line) and return them.
	 *
	 * @param string Input to split apart
	 * @return array Contains header and body section
	 * @access private
	 */
	function _splitBodyHeader($input)
	{
		if(preg_match("/^(.+?)\r?\n\r?\n(.*)/s", ltrim($input), $match)) {
			return array($match[1], $match[2]);
		}
		$this->_error = 'Could not split header and body';
		return false;
	}

	/**
	 * Parse headers given in $input and return
	 * as assoc array.
	 *
	 * @param string Headers to parse
	 * @return array Contains parsed headers
	 * @access private
	 */
	function _parseHeaders($input)
	{

		if ($input !== '') {
			// Unfold the input
			$input   = preg_replace("/\r?\n/", "\r\n", $input);
			$input   = preg_replace("/\r\n(\t| )+/", ' ', $input);
			$headers = explode("\r\n", trim($input));

			foreach ($headers as $value) {
				$hdr_name = substr($value, 0, $pos = strpos($value, ':'));
				$hdr_value = substr($value, $pos+1);
				if($hdr_value[0] == ' ')
					$hdr_value = substr($hdr_value, 1);

				$return[] = array(
								  'name'  => $hdr_name,
								  'value' => $hdr_value
								 );
			}
		} else {
			$return = array();
		}

		return $return;
	}

	/**
	 * Function to parse a header value,
	 * extract first part, and any secondary
	 * parts (after ;) This function is not as
	 * robust as it could be. Eg. header comments
	 * in the wrong place will probably break it.
	 *
	 * @param string Header value to parse
	 * @return array Contains parsed result
	 * @access private
	 */
	function _parseHeaderValue($input)
	{

		if (($pos = strpos($input, ';')) !== false) {

			$return['value'] = trim(substr($input, 0, $pos));
			$input = trim(substr($input, $pos+1));

			if (strlen($input) > 0) {

				// This splits on a semi-colon, if there's no preceeding backslash
				// Now works with quoted values; had to glue the \; breaks in PHP
				// the regex is already bordering on incomprehensible
				$splitRegex = '/([^;\'"]*[\'"]([^\'"]*([^\'"]*)*)[\'"][^;\'"]*|([^;]+))(;|$)/';
				preg_match_all($splitRegex, $input, $matches);
				$parameters = array();
				for ($i=0; $i<count($matches[0]); $i++) {
					$param = $matches[0][$i];
					while (substr($param, -2) == '\;') {
						$param .= $matches[0][++$i];
					}
					$parameters[] = $param;
				}

				for ($i = 0; $i < count($parameters); $i++) {
					$param_name  = trim(substr($parameters[$i], 0, $pos = strpos($parameters[$i], '=')), "'\";\t\\ ");
					$param_value = trim(str_replace('\;', ';', substr($parameters[$i], $pos + 1)), "'\";\t\\ ");
					if ($param_value[0] == '"') {
						$param_value = substr($param_value, 1, -1);
					}
					$return['other'][$param_name] = $param_value;
					$return['other'][strtolower($param_name)] = $param_value;
				}
			}
		} else {
			$return['value'] = trim($input);
		}

		return $return;
	}

	/**
	 * This function splits the input based
	 * on the given boundary
	 *
	 * @param string Input to parse
	 * @return array Contains array of resulting mime parts
	 * @access private
	 */
	function _boundarySplit($input, $boundary)
	{
		$parts = array();

		$bs_possible = substr($boundary, 2, -2);
		$bs_check = '\"' . $bs_possible . '\"';

		if ($boundary == $bs_check) {
			$boundary = $bs_possible;
		}

		$tmp = explode('--' . $boundary, $input);

		for ($i = 1; $i < count($tmp) - 1; $i++) {
			$parts[] = $tmp[$i];
		}

		return $parts;
	}

	/**
	 * Given a header, this function will decode it
	 * according to RFC2047. Probably not *exactly*
	 * conformant, but it does pass all the given
	 * examples (in RFC2047).
	 *
	 * @param string Input header value to decode
	 * @return string Decoded header value
	 * @access private
	 */
	function _decodeHeader($input)
	{
		// Remove white space between encoded-words
		$input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);

		// For each encoded-word...
		while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {

			$encoded  = $matches[1];
			$charset  = $matches[2];
			$encoding = $matches[3];
			$text	 = $matches[4];

			switch (strtolower($encoding)) {
				case 'b':
					$text = base64_decode($text);
					break;

				case 'q':
					$text = str_replace('_', ' ', $text);
					preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
					foreach($matches[1] as $value)
						$text = str_replace('='.$value, chr(hexdec($value)), $text);
					break;
			}

			$input = str_replace($encoded, $text, $input);
		}

		return $input;
	}

	/**
	 * Given a body string and an encoding type,
	 * this function will decode and return it.
	 *
	 * @param  string Input body to decode
	 * @param  string Encoding type to use.
	 * @return string Decoded body
	 * @access private
	 */
	function _decodeBody($input, $encoding = '7bit')
	{
		switch (strtolower($encoding)) {
			case '7bit':
				return $input;
				break;

			case 'quoted-printable':
				return $this->_quotedPrintableDecode($input);
				break;

			case 'base64':
				return base64_decode($input);
				break;

			default:
				return $input;
		}
	}

	/**
	 * Given a quoted-printable string, this
	 * function will decode and return it.
	 *
	 * @param  string Input body to decode
	 * @return string Decoded body
	 * @access private
	 */
	function _quotedPrintableDecode($input)
	{
		// Remove soft line breaks
		$input = preg_replace("/=\r?\n/", '', $input);

		// Replace encoded characters
		$input = preg_replace('/=([a-f0-9]{2})/ie', "chr(hexdec('\\1'))", $input);

		return $input;
	}

	/**
	 * Checks the input for uuencoded files and returns
	 * an array of them. Can be called statically, eg:
	 *
	 * $files =& MailDecode::uudecode($some_text);
	 *
	 * It will check for the begin 666 ... end syntax
	 * however and won't just blindly decode whatever you
	 * pass it.
	 *
	 * @param  string Input body to look for attahcments in
	 * @return array  Decoded bodies, filenames and permissions
	 * @access public
	 */
	function &uudecode($input)
	{
		// Find all uuencoded sections
		preg_match_all("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $input, $matches);

		for ($j = 0; $j < count($matches[3]); $j++) {

			$str	  = $matches[3][$j];
			$filename = $matches[2][$j];
			$fileperm = $matches[1][$j];

			$file = '';
			$str = preg_split("/\r?\n/", trim($str));
			$strlen = count($str);

			for ($i = 0; $i < $strlen; $i++) {
				$pos = 1;
				$d = 0;
				$len=(int)(((ord(substr($str[$i],0,1)) -32) - ' ') & 077);

				while (($d + 3 <= $len) AND ($pos + 4 <= strlen($str[$i]))) {
					$c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
					$c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
					$c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
					$c3 = (ord(substr($str[$i],$pos+3,1)) ^ 0x20);
					$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

					$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

					$file .= chr(((($c2 - ' ') & 077) << 6) |  (($c3 - ' ') & 077));

					$pos += 4;
					$d += 3;
				}

				if (($d + 2 <= $len) && ($pos + 3 <= strlen($str[$i]))) {
					$c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
					$c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
					$c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
					$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

					$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

					$pos += 3;
					$d += 2;
				}

				if (($d + 1 <= $len) && ($pos + 2 <= strlen($str[$i]))) {
					$c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
					$c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
					$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

				}
			}
			$files[] = array('filename' => $filename, 'fileperm' => $fileperm, 'filedata' => $file);
		}

		return $files;
	}

////////////////////////////////////////////////////////////////////////////////
// Mail parsers functions
////////////////////////////////////////////////////////////////////////////////

	function decodeHeaderLine($str, $charset=false)
	{
		if(preg_match("/=\?(.+)\?(B|Q)\?(.+)\?=?(.*)/i", $str, $matches))
			$str = iconv_mime_decode($str, 0, 'UTF-8');
		elseif($charset)
			$str = iconv($charset, 'UTF-8', $str);

		return trim($str);
	}


	function parseBody($obj, $num=0, $clear=true)
	{
		static $body; // output array for recurse
		if($clear) $body = array();
		static $msg_flag; // save part type for all subparts
		static $charset; // save found anywhere
		
		if(!empty($obj->parts))
		{
			for($i=0; $i<count($obj->parts); $i++) // see all elements
			{
				$type = $obj->parts[$i]->ctype_primary;

				if($type == 'multipart')
					self::parseBody($obj->parts[$i], 0, false);
				elseif($type == 'message') // ctype_secondary=>rfc822
				{
					$msg_flag = true; // text will be saved in this part (in $body['msg_text'])
					self::parseBody($obj->parts[$i], $num++, false);
				}
				elseif($type == 'text' &&
					(!isset($obj->parts[$i]->disposition) ||
					strtolower($obj->parts[$i]->disposition) == 'inline')) // attach may be text!
				{
					if(!$msg_flag) // save if parent is not attach
						$text = $obj->parts[$i];
					else // save last text (in [$num])
					{
						$body['msg_text'][$num] = $obj->parts[$i]->body;
						$body['msg_type'][$num] = strtolower($obj->parts[$i]->ctype_secondary);
						$body['msg_charset'][$num] = strtolower($obj->parts[$i]->ctype_parameters['charset']);
					}
				}
				elseif(($type == 'image') || // is this an image?
					(isset($obj->parts[$i]->disposition) &&
					($obj->parts[$i]->disposition == 'attachment'))) // is this an attach?
				{ // save attaches etc.
					if(!empty($obj->parts[$i]->d_parameters['filename']))
						$curName = self::decodeHeaderLine($obj->parts[$i]->d_parameters['filename']);
					elseif(!empty($obj->parts[$i]->ctype_parameters['name']))
						$curName = self::decodeHeaderLine($obj->parts[$i]->ctype_parameters['name']);
					else
						$curName = "attachment_$num";

					$curFile = array();
					$curFile['name'] = $curName;
					$curFile['type'] = $obj->parts[$i]->ctype_primary.'/'.$obj->parts[$i]->ctype_secondary;

					if(isset($obj->parts[$i]->headers['content-id']))
					$cid = isset($obj->parts[$i]->headers['content-id']) ? $obj->parts[$i]->headers['content-id'] : false;
					$disposition = isset($obj->parts[$i]->disposition) ? $obj->parts[$i]->disposition : false;

					$data_path = array(
						Wbs::getSystemObj()->files()->getDataPath(),
						Wbs::getDbkeyObj()->getDbkey(),
						'attachments',
						'st',
						'tmp'
						);

					if($cid && $disposition != 'attachment')
					{
						$curFile['cid'] = $cid; // for img only
						$data_path[] = 'images';
					}
					else
					{
						$curFile['cid'] = false;
						$data_path[] = 'attachments';
					}
					$destPath = join('/', $data_path);
					if(!self::forceDirPath($data_path)) {
						throw new Exception("Can't write to $destPath");
					}
					@file_put_contents($destPath.'/'.$curName, $obj->parts[$i]->body);

					$curFile['size'] = @filesize($destPath.'/'.$curName);

					$body['attached_files'][] = $curFile;
				}
			}
		}
		if(empty($text) && empty($body)) // no text in this and last parts
			$text = $obj; // find it in the end of current part

		if(!empty($text->body) && (empty($body['text']) || $body['type'] != 'html')) // return only last text (html has prioritry)
		{
			$body['text'] = $text->body; // rewrite on exist
			$body['type'] = strtolower($text->ctype_secondary);
			if(!empty($text->ctype_parameters['charset']))
				$charset = strtolower($text->ctype_parameters['charset']);
			if(!empty($charset))
				$body['text'] = iconv($charset, 'UTF-8', $body['text']);
			$body['charset'] = strtolower($charset);
			$body['text'] = self::format_msgbody($body);
		}
		return $body;
	}

	function format_msgbody($msg)
	{
		$m = MailParsers::cut_scripts($msg['text']);

		if($msg['type'] == 'plain')
			return self::format_plain($m);

		$m = str_replace('<a ', '<a target="_blank" ', $m);

		return trim($m);
	}

	function format_plain($str)
	{
		$str = str_replace("\n ", "\n&nbsp;", trim($str));
		$str = preg_replace("/\r?\n/", '<br>', $str);
		$str = "\n<div style = \"font-family: monospace\">\n"
			.str_replace('	', ' &nbsp;', $str)."\n</div>\n\n";
		$str = preg_replace("/([a-zA-Z0-9]+[\.\-_]?[a-zA-Z0-9]+)(@([a-z0-9]+[\.|\-]?[a-z0-9]+){1,4}\.[a-z]{2,4})/",
			"<a href=\"mailto:$1$2\" style=\"color:blue\">$1$2</a>", $str);
		$str = preg_replace("/((https?:\/\/www\.|https?:\/\/|www\.)[a-z0-9_\.\-]{2,}\.[a-z]{2,4}[a-z0-9_\.\-\/\?&=@:%]*)/i",
			"<a style=\"color:blue\" href=\"$1\" target=\"_blank\">$1</a>", $str);
		$str = self::parse_pre($str, true);

		return $str;
	}

	function parse_pre($str, $is_html) // change <pre> tags for <div>'s
	{
		if($is_html) $s = preg_replace("/<pre[^>]*>/i",'|�|', $str, 1); // html => find 1-st <pre>
		else $s = preg_replace("/<\/pre[^>]*>/i",'|�|', $str, 1); // plain => find 1-st </pre>

		$arr=explode('|�|',$s);

		if(isset($arr[1])) // found <pre> || </pre>
			return self::parse_pre($arr[0], $is_html).self::parse_pre($arr[1], !$is_html);
		else // there is not <pre> in this block
			if(!$is_html)
				$str = self::format_plain($str); // change
		return $str;
	}

	//
	// Formats and implode message text with it's attacments etc.
	//
	function prepare_body($body, $id, $account, $uid)
	{
		global $DB_KEY;
		$att = array();

		$prefix = ($_SERVER['SERVER_PORT'] == 43 ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
		$uri = $prefix.(onWebasystServer() ? '' : '/published');

		if(!empty($body['attached_files'])) // there are some attaches in this message
		{
			for($i=0; $i<count($body['attached_files']); $i++) // what is it?
			{
				if(!empty($body['attached_files'][$i]['cid'])) // this is a inline picture
				{ // src="cid:xxxx" -> src="image.php?...&rand"
					$cid = preg_replace("/\s*<(.+?)>\s*/", "\$1", $body['attached_files'][$i]['cid']);
					$pattern = '/cid:'.preg_quote($cid,"/").'/i'; // src="cid:xx$xx" -> xx\\$xx
					$path = prepareURLStr("$uri/common/html/scripts/getimage.php", array(
						'user'=>base64_encode($DB_KEY),
						'msg'=>$account.'~'.$uid,
						'file'=>base64_encode($body['attached_files'][$i]['name'])
					));
					$body['text'] = preg_replace($pattern, $path, $body['text']);
				}
				else
				{ // this is attached file
					$path = prepareURLStr( '../../2.0/getattach.php', array(
						'mid'=>$account.'~'.$uid,
						'file'=>urlencode(base64_encode($body['attached_files'][$i]['name']))
					));
					$att[$i] = '<a href="'.$path.'">'.MailParsers::cut_scripts($body['attached_files'][$i]['name'])
						.'</a> ('.formatFileSizeStr($body['attached_files'][$i]['size']).')';
				}
			}
		}
		$text = self::format_msgbody($body); // & cut_scripts

		if(!empty($body['msg_text'])) // there ara attached forwards here (see Yamail)
			for($i=0; $i<count($body['msg_text']); $i++) // add it
			{
				$t = iconv($body['msg_charset'][$i], 'UTF-8', $body['msg_text'][$i]);
				$t = self::format_msgbody($t, $body['msg_type'][$i]);
				$text .= "\n<hr size=1 noshade>\n$t";
			}

		if(!empty($att))
			$body['att_str'] = implode(' &nbsp;', $att);

		$body['text'] = $text;
	}

	public static function forceDirPath($parts)
	{
		$path = ''; 
		foreach($parts as $level)
		{
			$path .= "$level/";
			if(!is_dir($path))
				if(!@mkdir($path))
					return false;
		}
		return true;
	}

	function formatFileSize($fileSize) {
		if(!strlen($fileSize))
			return null;

		if(!$fileSize)
			return sprintf(_('%s KB'), '0.00');
		if($fileSize < 1024)
			$fileSize = 1024;
		if($fileSize >= 1000000000)
			return sprintf(_('%s GB'), round(ceil($fileSize)/1000000000, 2));
		elseif($fileSize >= 1000000)
			return sprintf(_('%s MB'), round(ceil($fileSize)/1000000, 2));
		else
			return sprintf(_('%s KB'), round(ceil($fileSize)/1024, 2));
	}

	function get_mime($filename)
	{
		preg_match('/\.(.*?)$/', $filename, $m);
		switch(strtolower($m[1]))
		{
			case 'jpg': case 'jpeg': case 'jpe': return 'image/jpg';
			case 'png': case 'gif': case 'bmp': case 'tiff' : return 'image/'.strtolower($m[1]);

			case 'doc': case 'docx': return 'application/msword';
			case 'xls': case 'xlt': case 'xlm': case 'xld': case 'xla': case 'xlc': case 'xlw': case 'xll': return 'application/vnd.ms-excel';
			case 'ppt': case 'pps': return 'application/vnd.ms-powerpoint';
			case 'rtf': return 'application/rtf';
			case 'txt': return 'text/plain';
			case 'pdf': return 'application/pdf';
			case 'html': case 'htm': case 'php': return 'text/html';
			case 'zip': return 'application/zip';
			case 'tar': return 'application/x-tar';
			case 'js': return 'application/x-javascript';
			case 'json': return 'application/json';
			case 'css': return 'text/css';
			case 'xml': return 'application/xml';
			case 'mpeg': case 'mpg': case 'mpe': return 'video/mpeg';
			case 'mp3': return 'audio/mpeg3';
			case 'wav': return 'audio/wav';
			case 'aiff': case 'aif': return 'audio/aiff';
			case 'avi': return 'video/msvideo';
			case 'wmv': return 'video/x-ms-wmv';
			case 'mov': return 'video/quicktime';
			case 'zip': return 'application/zip';
			case 'tar': return 'application/x-tar';
			case 'swf': return 'application/x-shockwave-flash';

			default: return 'application/octet-stream';
		}
	}

}
