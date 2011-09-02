<?php

class MailDecoder
{
	
	public function __construct()
	{	
	}
	
	protected function getHeaders($headers)
	{
		$result = array();
		if ($headers !== '') {
			$headers   = preg_replace("/\r?\n/", "\r\n", $headers);
			$headers   = preg_replace("/\r\n(\t| )+/", ' ', $headers);
			$headers = explode("\r\n", trim($headers));

			foreach ($headers as $value) {
				$name = substr($value, 0, $pos = strpos($value, ':'));
				$name = strtolower($name);
				$value = substr($value, $pos + 1);
				if (substr($value, 0, 1) == ' ') {
					$value = substr($value, 1);
				}
				if (isset($result[$name])) {
					if (!is_array($result[$name])) {
						$result[$name] = array($result[$name]);
					}
					$result[$name][] = $value;
				} else {
					$result[$name] = $value;
				}
			}
		} 
		return $result;		
	} 
	
	public function getHeaderValue($input)
	{
		if (is_array($input)) {
			$input = current($input);
		}
		$return = array('value' => '');
		if (($pos = strpos($input, ';')) !== false) {
			$return['value'] = trim(substr($input, 0, $pos));
			$input = trim(substr($input, $pos + 1));
			// @todo: Improve this code!
			if (strlen($input) > 0) {
				$split_regex = '/([^;\'"]*[\'"]([^\'"]*([^\'"]*)*)[\'"][^;\'"]*|([^;]+))(;|$)/';
				preg_match_all($split_regex, $input, $match);
				$parameters = array();
				for ($i=0; $i < count($match[0]); $i++) {
					$param = $match[0][$i];
					while (substr($param, -2) == '\;') {
						$param .= $match[0][++$i];
					}
					$parameters[] = $param;
				}

				for ($i = 0; $i < count($parameters); $i++) {
					$name  = trim(substr($parameters[$i], 0, $pos = strpos($parameters[$i], '=')), "'\";\t\\ ");
					$value = trim(str_replace('\;', ';', substr($parameters[$i], $pos + 1)), "'\";\t\\ ");
					if (substr($value, 0, 1) == '"') {
						$value = substr($value, 1, -1);
					}
					$return['other'][strtolower($name)] = $value;
				}
			}
		} else {
			$return['value'] = trim($input);
		}

		return $return;
	}
	
	public static function decodeHeader($str, $charset = false)
	{
		if (is_array($str)) {
			$str = array_shift($str);
		}
		if (preg_match("/=\?(.+)\?(B|Q)\?(.+)\?=?(.*)/i", $str)) {
			$new_str = mb_decode_mimeheader($str);
			if ($new_str === $str) {
				$str = iconv_mime_decode($str, 0, 'UTF-8');
			} else {
				$str = $new_str;
			}
		} elseif ($charset) {
			$str = iconv($charset, 'UTF-8', $str);
		}

		if (Wbs::getDbkeyObj()->getLanguage() == 'rus' && !preg_match('//u', $str)) {
			$s = iconv('CP1251', 'UTF-8', $str);
			if ($s) {
				$str = $s;
			}
		}
		
		return trim($str);
	}
	
	protected function decodeBody(&$text, $encoding = '7bit')
	{
		switch (strtolower($encoding)) {
			case '7bit':
				return $text;
			case 'quoted-printable':
				$text = preg_replace("/=\r?\n/", '', $text);
				return preg_replace('/=([a-f0-9]{2})/ie', "chr(hexdec('\\1'))", $text);
			case 'base64':
				return base64_decode($text);
			default:
				return $text;
		}
	}
	
	public function decode(&$message)
	{		
		$result = $this->decodePart($message['headers'], $message['body']);
		
		unset($message);
		
		$charset = false;
		if (isset($result['headers']['content-type']) && is_array($result['headers']['content-type'])) {
			$result['headers']['content-type'] = current($result['headers']['content-type']);
		}
		if (isset($result['headers']['content-type']) && preg_match('/^\s*"?([a-z\/]+)"?;\s* charset="?([a-z0-9-]+)"?/i',
			$result['headers']['content-type'], $match)) {
			$charset = $match[2];
		}
		
		$decoding_headers = array("from", "to", "subject", "reply-to", "cc");
		foreach ($decoding_headers as $h) {
			if (isset($result['headers'][$h])) {
				$result['headers'][$h] = $this->decodeHeader($result['headers'][$h], $charset);
			}
		}
		$result = $this->compose($result);
		
		if ($result['type'] != 'html') {			
			$result['text'] = $this->preparePlain($result['text']);
		} else {
			$result['text'] = $this->prepareHTML($result['text']);
		}	
		return $result;
	}
	
	
	protected function preparePlain($text)
	{
		$text = htmlspecialchars($text);
		$text = nl2br($text);
		return $text;
	}
	
	protected function prepareHTML($text)
	{
		/*
		$text = preg_replace("!<[^>]*script[^>]*>.*?</script[^>]*>!uism", "", $text);
		$text = preg_replace("!<style.*?>.*?</style>!uism", "", $text);
		$text = preg_replace("!<([^>]*)(href|src)[^=]*=(\"|')?javascript:[^>]*>!uism", "<$1>", $text);
		*/
    	$text = preg_replace('!(&#0*[0-9]+;)!e', 'html_entity_decode("\\1", ENT_NOQUOTES, "UTF-8")', $text);
    	$text = preg_replace('!(&#x0*[0-9a-f]+;)!e', 'html_entity_decode("\\1", ENT_NOQUOTES, "UTF-8")', $text);
    	$text = preg_replace('!<title>.*?</title>!is', '', $text);		
		return $text;
	}
	
	protected function compose(&$message)
	{
		$flag = true;
		if (isset($message['parts']) && $message['parts'])	{
			foreach ($message['parts'] as $i => $part) {
				$type = strtolower($part['type']['primary']);
				if ($type == 'multipart') {
					$part = $this->compose($part);
					$fields = array('text', 'type', 'charset', 'attachments');
					foreach ($fields as $f) {
						if (isset($part[$f])) {
							$message[$f] = $part[$f];
						}
					}
					$flag = false;
				} elseif ($type == 'image' || $type == 'message' || (isset($part['disposition']['value']) && ($part['disposition']['value'] != 'inline') || $type != 'text')) {
					$file = array();
					if (isset($part['disposition']['parameters']['filename'])) {
						$file['name'] = $part['disposition']['parameters']['filename'];
					} elseif (isset($part['type']['parameters']['name'])) {
						$file['name'] = $part['type']['parameters']['name'];
					} elseif (isset($part['type']['parameters']['name*'])) {
						$file['name'] = urldecode($part['type']['parameters']['name*']);
						$params = explode("''", $file['name']);
						if (count($params) > 1) {
							$file['name'] = iconv($params[0], 'UTF-8', urldecode($params[1]));
						}  
					} 
						
					if (isset($file['name'])) {
						$file['name'] = $this->decodeHeader($file['name']);
					}			
					$file['type'] = $part['type']['primary'].'/'.$part['type']['secondary'];
					if (isset($part['headers']['content-id'])) {
						$file['content-id'] =  $part['headers']['content-id'];
						if (substr($file['content-id'], 0, 1) == '<' &&  substr($file['content-id'], -1) == '>') {
							$file['content-id'] = substr($file['content-id'], 1, -1);
						}
					} 
					if (isset($part['disposition']['value'])) {
						$file['disposition'] = $part['disposition']['value'];
					}
					$file['file'] = uniqid(); 
					$path = WBS_DIR."temp/mail_part";
					if (!file_exists($path)) {
						@mkdir($path, 0775, true);
					}
					@file_put_contents($path."/".$file['file'], $part['body']);
					$file['size'] = @filesize($path."/".$file['file']);
					$message['attachments'][] = $file;
				} elseif ($type == 'text') {
					$message['text'] = $part['body'];
					$message['type'] = $part['type'];					
				}
			}
			unset($message['parts']);
		} elseif (isset($message['body'])) {
			$message['text'] = $message['body'];
			unset($message['body']);
		}		
		if ($flag && isset($message['text'])) {
			if (isset($message['type']['parameters']['charset'])) {
				$charset = $message['type']['parameters']['charset'];
				if ($charset && (strtolower($charset) != 'utf-8') && (strtolower($charset) != 'utf8')) {
					$message['text'] = iconv($charset, 'UTF-8', $message['text']);
					$message['charset'] = strtolower($charset);
				}
			} elseif (!preg_match("//u", $message['text'])) {
				if (iconv('windows-1251', 'windows-1251', $message['text']) == $message['text']) {
					$message['text'] = iconv('windows-1251', 'utf-8', $message['text']);
					$message['charset'] = 'windows-1251';
				}
			}
			$message['type'] = strtolower($message['type']['secondary']);
		}		
		if (!isset($message['text'])) {
			$message['text'] = '';
		}
		return $message;
	}
	
	
	protected function split(&$text, $boundary)
	{
		$boundary_possible = substr($boundary, 2, -2);
		if ($boundary == '\"' . $boundary_possible . '\"') {
			$boundary = $boundary_possible;
		}
		$result = explode('--' . $boundary, $text);
		if (count($result) == 1) {
			return $result;
		}
		// Delete first and last elements
		array_shift($result);
		array_pop($result);
		return $result;
	}
	
	
	protected function decodePart($headers, &$body, $default_content_type = 'text/plain') 
	{
		$result = array();
		$headers = $this->getHeaders($headers);
		if (isset($headers['from']) && is_array($headers['from'])) {
			foreach ($headers['from'] as $f) {
				if (strpos($f, '@') !== false) {
					$headers['from'] = $f; 
					break;
				}
			}
			if (is_array($headers['from'])) {
				$headers['from'] = array_shift($headers['from']);
			}
		} 
		$result['headers'] = $headers;	
		$content_type = $content_disposition = $content_transfer_encoding = array();
		if (isset($headers['content-type'])) {
			$content_type = $this->getHeaderValue($headers['content-type']);
			if (preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]*)/i', $content_type['value'], $match)) {
				$result['type'] = array(
					'primary' => $match[1],
					'secondary' => $match[2]
				);
			}
			if (isset($content_type['other'])) {
				foreach($content_type['other'] as $name => $value) {
					$result['type']['parameters'][$name] = $value;
				}
			}			
		} 
		
		if (isset($headers['content-disposition'])) {
			$content_disposition = $this->getHeaderValue($headers['content-disposition']);
			$result['disposition'] = array('value' => $content_disposition['value']);
			if (isset($content_disposition['other'])) {
				foreach($content_disposition['other'] as $name => $value) {
					/*
					if (substr($name, -1) === '*') {
						$name = substr($name, 0, -1);
					}
					*/
					$result['disposition']['parameters'][$name] = $value;
				}
			}
		} 
		
		if (isset($headers['content-transfer-encoding'])) {
			$content_transfer_encoding = $this->getHeaderValue($headers['content-transfer-encoding']);
		} 
		
		
		if ($content_type) {
			switch (strtolower($content_type['value'])) {
				case 'text/plain':
				case 'text/html':					
					$encoding = isset($content_transfer_encoding['value']) ? $content_transfer_encoding['value'] : '7bit';
					$result['body'] = $this->decodeBody($body, $encoding);
					break;

				case 'multipart/parallel':
				case 'multipart/appledouble':
				case 'multipart/report': 
				case 'multipart/signed': 
				case 'multipart/digest':
				case 'multipart/alternative':
				case 'multipart/related':
				case 'multipart/mixed':

					if (!isset($content_type['other']['boundary'])){
						return false;
					}
					$default_content_type = (strtolower($content_type['value']) === 'multipart/digest') ? 'message/rfc822' : 'text/plain';
					$parts = $this->split($body, $content_type['other']['boundary']);
					unset($body);
					$n = count($parts);
					for ($i = 0; $i < $n; $i++) {
						if (preg_match("/^(.+?)\r?\n\r?\n(.*)/s", $parts[$i], $match)) {
							unset($parts[$i]);
							$result['parts'][] = $this->decodePart($match[1], $match[2], $default_content_type); 
						} else {
							unset($parts[$i]);
						}
					}
					break;

				case 'message/rfc822':
					$result['body'] = $body;
					break;

				default:
					if (!isset($content_transfer_encoding['value'])) {
						$content_transfer_encoding['value'] = '7bit';
					}
					$result['body'] = $this->decodeBody($body, $content_transfer_encoding['value']);
					break;
			}

		} else {
			$content_type = explode('/', $default_content_type);
			$result['type'] = array(
				'primary' => $content_type[0],
				'secondary' => $content_type[1],
			);
			$result['body'] = $this->decodeBody($body);
		}
		
		return $result;		
	}

	public static function format_msgbody($msg)
	{
		$m = MailParsers::cut_scripts($msg['text']);

		if($msg['type'] == 'plain')
			return self::format_plain($m);

		$m = str_replace('<a ', '<a target="_blank" ', $m);

		return trim($m);
	}

	public static function format_plain($str)
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

	public static function getMimeType($filename)
	{
		preg_match('/\.(.*?)$/', $filename, $match);
		switch (strtolower($match[1])) {
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
