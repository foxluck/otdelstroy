<?php
/**
 * JsHttpRequest: PHP backend for JavaScript DHTML loader.
 * (C) Dmitry Koterov, http://en.dklab.ru
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * See http://www.gnu.org/copyleft/lesser.html
 *
 * Do not remove this comment if you want to use the script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * This backend library also supports POST requests additionally to GET.
 *
 * @author Dmitry Koterov
 * @version 5.x $Id: class.jshttprequest.php 940 2010-02-18 13:23:15Z vlad $
 * @version SVN: $Id: class.jshttprequest.php 940 2010-02-18 13:23:15Z vlad $
 */

class JsHttpRequest
{
	var $SCRIPT_ENCODING = "utf-8";
	var $SCRIPT_DECODE_MODE = '';
	var $LOADER = null;
	var $ID = null;
	var $RESULT = null;

	// Internal; uniq value.
	var $_uniqHash;
	// Internal: response content-type depending on loader type.
	var $_contentTypes = array(
        "script" => "text/javascript",
        "xml"    => "text/plain", // In XMLHttpRequest mode we must return text/plain - stupid Opera 8.0. :(
        "form"   => "text/html",
		"json"   => "text/plain", // for json
        ""       => "text/plain", // for unknown loader
	);
	// Internal: conversion to UTF-8 JSON cancelled because of non-ascii key.
	var $_toUtfFailed = false;
	// Internal: list of characters 128...255 (for strpbrk() ASCII check).
	var $_nonAsciiChars = '';


	/**
	 * Constructor.
	 *
	 * Create new JsHttpRequest backend object and attach it
	 * to script output buffer. As a result - script will always return
	 * correct JavaScript code, even in case of fatal errors.
	 */
	function JsHttpRequest($enc)
	{
		// QUERY_STRING is in form of: PHPSESSID=<sid>&a=aaa&b=bbb&JsHttpRequest=<id>-<loader>
		// where <id> is a request ID, <loader> is a loader name, <sid> - a session ID (if present),
		// PHPSESSID - session parameter name (by default = "PHPSESSID").

		// Parse QUERY_STRING.
		if (preg_match('/^(.*)(?:&|^)JsHttpRequest=(?:(\d+)-)?([^&]+)((?:&|$).*)$/s', @$_SERVER['QUERY_STRING'], $m)) {
			$this->ID = $m[2];
			$this->LOADER = strtolower($m[3]);
			$_SERVER['QUERY_STRING'] = preg_replace('/^&+|&+$/s', '', preg_replace('/(^|&)'.session_name().'=[^&]*&?/s', '&', $m[1] . $m[4]));
			unset(
			$_GET['JsHttpRequest'],
			$_REQUEST['JsHttpRequest'],
			$_GET[session_name()],
			$_POST[session_name()],
			$_REQUEST[session_name()]
			);
		} else {
			$this->ID = 0;
			$this->LOADER = 'xml';
		}

		// Start OB handling early.
		$this->_uniqHash = md5(microtime() . getmypid());
		ini_set('error_prepend_string', ini_get('error_prepend_string') . $this->_uniqHash);
		ini_set('error_append_string',  ini_get('error_append_string') . $this->_uniqHash);
		ob_start(array(&$this, "_obHandler"));

		// Set up the encoding.
		$this->setEncoding($enc);

		// Check if headers are already sent (see Content-Type library usage).
		// If true - generate a debug message and exit.
		$file = $line = null;
		if (headers_sent($file, $line)) {
			trigger_error(
                "HTTP headers are already sent" . ($line !== null? " in $file on line $line" : "") . ". "
                . "Possibly you have an extra space (or a newline) before the first line of the script or any library. "
                . "Please note that JsHttpRequest uses its own Content-Type header and fails if "
                . "this header cannot be set. See header() function documentation for more details",
                E_USER_ERROR
                );
                exit();
		}
	}


	/**
	 * string getJsCode()
	 *
	 * Return JavaScript part of the library.
	 */
	function getJsCode()
	{
		return file_get_contents(dirname(__FILE__).'/JsHttpRequest.js');
	}


	/**
	 * void setEncoding(string $encoding)
	 *
	 * Set an active script encoding & correct QUERY_STRING according to it.
	 * Examples:
	 *   "windows-1251"          - set plain encoding (non-windows characters,
	 *                             e.g. hieroglyphs, are totally ignored)
	 *   "windows-1251 entities" - set windows encoding, BUT additionally replace:
	 *                             "&"         ->  "&amp;"
	 *                             hieroglyph  ->  &#XXXX; entity
	 */
	function setEncoding($enc)
	{
		// Parse an encoding.
		preg_match('/^(\S*)(?:\s+(\S*))$/', $enc, $p);
		$this->SCRIPT_ENCODING    = strtolower(!empty($p[1])? $p[1] : $enc);
		$this->SCRIPT_DECODE_MODE = !empty($p[2])? $p[2] : '';
		// Manually parse QUERY_STRING because of damned Unicode's %uXXXX.
		$this->_correctSuperglobals();
	}


	/**
	 * string quoteInput(string $input)
	 *
	 * Quote a string according to the input decoding mode.
	 * If entities are used (see setEncoding()), no '&' character is quoted,
	 * only '"', '>' and '<' (we presume that '&' is already quoted by
	 * an input reader function).
	 *
	 * Use this function INSTEAD of htmlspecialchars() for $_GET data
	 * in your scripts.
	 */
	function quoteInput($s)
	{
		if ($this->SCRIPT_DECODE_MODE == 'entities')
		return str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $s);
		else
		return htmlspecialchars($s);
	}


	/**
	 * Convert a PHP scalar, array or hash to JS scalar/array/hash. This function is
	 * an analog of json_encode(), but it can work with a non UTF-8 input and does not
	 * analyze the passed data. Output format must be fully JSON compatible.
	 *
	 * @param mixed $a   Any structure to convert to JS.
	 * @return string    JavaScript equivalent structure.
	 */
	function php2js($a=false)
	{
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a)) {
			if (is_float($a)) {
				// Always use "." for floats.
				$a = str_replace(",", ".", strval($a));
			}
			// All scalars are converted to strings to avoid indeterminism.
			// PHP's "1" and 1 are equal for all PHP operators, but
			// JS's "1" and 1 are not. So if we pass "1" or 1 from the PHP backend,
			// we should get the same result in the JS frontend (string).
			// Character replacements for JSON.
			static $jsonReplaces = array(
			array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
			array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"')
			);
			return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
		}
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
			if (key($a) !== $i) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if ($isList) {
			foreach ($a as $v) {
				$result[] = JsHttpRequest::php2js($v);
			}
			return '[ ' . join(', ', $result) . ' ]';
		} else {
			foreach ($a as $k => $v) {
				$result[] = JsHttpRequest::php2js($k) . ': ' . JsHttpRequest::php2js($v);
			}
			return '{ ' . join(', ', $result) . ' }';
		}
	}



	/**
	 * Internal methods.
	 */

	/**
	 * Parse & decode QUERY_STRING.
	 */
	function _correctSuperglobals()
	{
		// In case of FORM loader we may go to nirvana, everything is already parsed by PHP.
		if ($this->LOADER == 'form') return;

		// ATTENTION!!!
		// HTTP_RAW_POST_DATA is only accessible when Content-Type of POST request
		// is NOT default "application/x-www-form-urlencoded"!!!
		// Library frontend sets "application/octet-stream" for that purpose,
		// see JavaScript code.
		if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$post = !empty($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : null;
		} else {
			$post = implode("\r\n", file('php://input'));
		}
		$source = array(
			'_GET' => !empty($_SERVER['QUERY_STRING'])? $_SERVER['QUERY_STRING'] : null, 
			'_POST'=> !empty($post)?$post:null//!empty($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : null,
		);
		foreach ($source as $dst=>$src) {
			// First correct all 2-byte entities.
			$s = preg_replace('/%(?!5B)(?!5D)([0-9a-f]{2})/si', '%u00\\1', $src);
			// Now we can use standard parse_str() with no worry!
			$data = null;
			parse_str($s, $data);
			$GLOBALS[$dst] = $this->_ucs2EntitiesDecode($data);
		}
		$GLOBALS['HTTP_GET_VARS'] = $_GET; // deprecated vars
		$GLOBALS['HTTP_POST_VARS'] = $_POST;
		$_REQUEST =
		(isset($_COOKIE)? $_COOKIE : array()) +
		(isset($_POST)? $_POST : array()) +
		(isset($_GET)? $_GET : array());
		if (ini_get('register_globals')) {
			// TODO?
		}
	}


	/**
	 * Called in case of error too!
	 */
	function _obHandler($text)
	{
		// Check for error.
		if (preg_match('{'.$this->_uniqHash.'(.*?)'.$this->_uniqHash.'}sx', $text)) {
			$text = str_replace($this->_uniqHash, '', $text);
		}

		// Make a resulting hash.
		if (!isset($this->RESULT)) {
			$this->RESULT = isset($GLOBALS['_RESULT'])? $GLOBALS['_RESULT'] : null;
		}
		$encoding = $this->SCRIPT_ENCODING;
		$result = array(
            'id'   => $this->ID,
            'js'   => $this->RESULT,
            'text' => $text,
		);
		if (function_exists('array_walk_recursive') && function_exists('iconv') && function_exists('json_encode')) {
			//encoding transformation commented out
            $encoding = "UTF-8";
			$this->_nonAsciiChars = join("", array_map('chr', range(128, 255)));
			$this->_toUtfFailed = false;
			array_walk_recursive($result, array(&$this, '_toUtf8_callback'), $this->SCRIPT_ENCODING);
			if (!$this->_toUtfFailed) {
				// If some key contains non-ASCII character, convert everything manually.
				$text = json_encode($result);
			} else {
				$text = $this->php2js($result);
			}
			//*/$text = json_encode($result);
		} else {
			$text = $this->php2js($result);
		}
		
		// Content-type header.
		// In XMLHttpRequest mode we must return text/plain - damned stupid Opera 8.0. :(
		$ctype = !empty($this->_contentTypes[$this->LOADER])? $this->_contentTypes[$this->LOADER] : $this->_contentTypes[''];
		$encoding = strtolower($encoding);
		header("Content-type: {$ctype}; charset={$encoding}");

		if ($this->LOADER != "xml") {
			// In non-XML mode we cannot use plain JSON. So - wrap with JS function call.
			// If top.JsHttpRequestGlobal is not defined, loading is aborted and
			// iframe is removed, so - do not call dataReady().
			$text = ""
			. ($this->LOADER == "form"? 'top && top.JsHttpRequestGlobal && top.JsHttpRequestGlobal' : 'JsHttpRequest')
			. ".dataReady(" . $text . ")\n"
			. "";
			if ($this->LOADER == "form") {
				$text = '<script type="text/javascript" language="JavaScript"><!--' . "\n$text" . '//--></script>';
			}
		}

		return $text;
	}


	/**
	 * Internal function, used in array_walk_recursive() before json_encode() call.
	 * If a key contains non-ASCII characters, this function sets $this->_toUtfFailed = true,
	 * becaues array_walk_recursive() cannot modify array keys.
	 */
	function _toUtf8_callback(&$v, $k, $fromEnc)
	{
		if ($this->_toUtfFailed || strpbrk($k, $this->_nonAsciiChars) !== false) {
			$this->_toUtfFailed = true;
		} else {
			$v = iconv($fromEnc, 'UTF-8', $v);
		}
	}


	/**
	 * Decode all %uXXXX entities in string or array (recurrent).
	 * String must not contain %XX entities - they are ignored!
	 */
	function _ucs2EntitiesDecode($data)
	{
		if (is_array($data)) {
			$d = array();
			foreach ($data as $k=>$v) {
				$d[$this->_ucs2EntitiesDecode($k)] = $this->_ucs2EntitiesDecode($v);
			}
			return $d;
		} else {
			if (strpos($data, '%u') !== false) { // improve speed
				$data = preg_replace_callback('/%u([0-9A-F]{1,4})/si', array(&$this, '_ucs2EntitiesDecodeCallback'), $data);
			}
			return $data;
		}
	}


	/**
	 * Decode one %uXXXX entity (RE callback).
	 */
	function _ucs2EntitiesDecodeCallback($p)
	{
		$hex = $p[1];
		$dec = hexdec($hex);
		if ($dec === "38" && $this->SCRIPT_DECODE_MODE == 'entities') {
			// Process "&" separately in "entities" decode mode.
			$c = "&amp;";
		} else {
			if (is_callable('iconv')) {
				$c = @iconv('UCS-2BE', $this->SCRIPT_ENCODING, pack('n', $dec));
			} else {
				$c = $this->_decUcs2Decode($dec, $this->SCRIPT_ENCODING);
			}
			if (!strlen($c)) {
				if ($this->SCRIPT_DECODE_MODE == 'entities') {
					$c = '&#'.$dec.';';
				} else {
					$c = '?';
				}
			}
		}
		return $c;
	}


	/**
	 * If there is no ICONV, try to decode 1-byte characters manually
	 * (for most popular charsets only).
	 */

	/**
	 * Convert from UCS-2BE decimal to $toEnc.
	 */
	function _decUcs2Decode($code, $toEnc)
	{
		if ($code < 128) return chr($code);
		if (isset($this->_encTables[$toEnc])) {
			// TODO: possible speedup by using array_flip($this->_encTables) and later hash access in the constructor.
			$p = array_search($code, $this->_encTables[$toEnc]);
			if ($p !== false) return chr(128 + $p);
		}
		return "";
	}


	/**
	 * UCS-2BE -> 1-byte encodings (from #128).
	 */
	var $_encTables = array(
        'windows-1251' => array(
	0x0402, 0x0403, 0x201A, 0x0453, 0x201E, 0x2026, 0x2020, 0x2021,
	0x20AC, 0x2030, 0x0409, 0x2039, 0x040A, 0x040C, 0x040B, 0x040F,
	0x0452, 0x2018, 0x2019, 0x201C, 0x201D, 0x2022, 0x2013, 0x2014,
	0x0098, 0x2122, 0x0459, 0x203A, 0x045A, 0x045C, 0x045B, 0x045F,
	0x00A0, 0x040E, 0x045E, 0x0408, 0x00A4, 0x0490, 0x00A6, 0x00A7,
	0x0401, 0x00A9, 0x0404, 0x00AB, 0x00AC, 0x00AD, 0x00AE, 0x0407,
	0x00B0, 0x00B1, 0x0406, 0x0456, 0x0491, 0x00B5, 0x00B6, 0x00B7,
	0x0451, 0x2116, 0x0454, 0x00BB, 0x0458, 0x0405, 0x0455, 0x0457,
	0x0410, 0x0411, 0x0412, 0x0413, 0x0414, 0x0415, 0x0416, 0x0417,
	0x0418, 0x0419, 0x041A, 0x041B, 0x041C, 0x041D, 0x041E, 0x041F,
	0x0420, 0x0421, 0x0422, 0x0423, 0x0424, 0x0425, 0x0426, 0x0427,
	0x0428, 0x0429, 0x042A, 0x042B, 0x042C, 0x042D, 0x042E, 0x042F,
	0x0430, 0x0431, 0x0432, 0x0433, 0x0434, 0x0435, 0x0436, 0x0437,
	0x0438, 0x0439, 0x043A, 0x043B, 0x043C, 0x043D, 0x043E, 0x043F,
	0x0440, 0x0441, 0x0442, 0x0443, 0x0444, 0x0445, 0x0446, 0x0447,
	0x0448, 0x0449, 0x044A, 0x044B, 0x044C, 0x044D, 0x044E, 0x044F,
	),
        'koi8-r' => array(
	0x2500, 0x2502, 0x250C, 0x2510, 0x2514, 0x2518, 0x251C, 0x2524,
	0x252C, 0x2534, 0x253C, 0x2580, 0x2584, 0x2588, 0x258C, 0x2590,
	0x2591, 0x2592, 0x2593, 0x2320, 0x25A0, 0x2219, 0x221A, 0x2248,
	0x2264, 0x2265, 0x00A0, 0x2321, 0x00B0, 0x00B2, 0x00B7, 0x00F7,
	0x2550, 0x2551, 0x2552, 0x0451, 0x2553, 0x2554, 0x2555, 0x2556,
	0x2557, 0x2558, 0x2559, 0x255A, 0x255B, 0x255C, 0x255d, 0x255E,
	0x255F, 0x2560, 0x2561, 0x0401, 0x2562, 0x2563, 0x2564, 0x2565,
	0x2566, 0x2567, 0x2568, 0x2569, 0x256A, 0x256B, 0x256C, 0x00A9,
	0x044E, 0x0430, 0x0431, 0x0446, 0x0434, 0x0435, 0x0444, 0x0433,
	0x0445, 0x0438, 0x0439, 0x043A, 0x043B, 0x043C, 0x043d, 0x043E,
	0x043F, 0x044F, 0x0440, 0x0441, 0x0442, 0x0443, 0x0436, 0x0432,
	0x044C, 0x044B, 0x0437, 0x0448, 0x044d, 0x0449, 0x0447, 0x044A,
	0x042E, 0x0410, 0x0411, 0x0426, 0x0414, 0x0415, 0x0424, 0x0413,
	0x0425, 0x0418, 0x0419, 0x041A, 0x041B, 0x041C, 0x041d, 0x041E,
	0x041F, 0x042F, 0x0420, 0x0421, 0x0422, 0x0423, 0x0416, 0x0412,
	0x042C, 0x042B, 0x0417, 0x0428, 0x042d, 0x0429, 0x0427, 0x042A
	),
	);
}
?>