<?php

/**
 * This class prepare text of the templates and other files for localization based on gettext.
 * It's developer version!
 * 
 * Tested on Windows and Linux platform
 * Used PHP 5.2.6
 * 
  * @example 
 * Source text:
 * <html>
 * <head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 * <title>Testing class LocalizationPrepare</title>
 * </head>
 * <body>
 * <a href="http://alexmuz.ru/localization" title="Localization based on gettext for the templates">Gettext localization</a>
 * Testing localization!
 * </body>
 * </html>
 * 
 * Result:
 * <html>
 * <head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 * <title>[`Testing class LocalizationPrepare`]</title>
 * </head>
 * <body>
 * <a href="http://alexmuz.ru/localization" title="[`Localization based on gettext for the templates`]">[`Gettext localization`]</a>
 * [`Testing localization!`]
 * </body>
 * </html>
 * 
 * @package Utils
 * @subpackage Localization
 * @author A. Muzychenko, alexmuz@gmail.com
 * @since January 2009
 */
class LocalizationPrepare
{
	protected $text;
	protected $offset = 0;
	protected $length;
	
	protected $prefix = '[`';
	protected $suffix = '`]';
	
	protected $chars_skip = "[\s\t\r\n]";
	
	protected $chars_allow = " qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM,.!\t()";
	
	protected $tag_current = false;
	protected $tag_open = false;
	protected $tags_open = array();
	/**
	 * Tags, allowed for parse  
	 * 
	 * @var array
	 */
	protected $tags_allow = array(
		'p',
		'b',
		'a',
		'title',
		'span',
		'body',
		'div',
		'td'
	);
	
	/**
	 * Tags, not closed  
	 * <br>, <img>, ...
	 */
	protected $tags_end = array(
		'meta',
		'br',
		'img'
	);
	
	/**
	 * Tags, which may be not closed:
	 * <table> 
	 * <tr>
	 * 	<td>1 column
	 * 	<td>2 column
	 *  <td>3 column
	 * </tr>
	 * </table>
	 * @var unknown_type
	 */
	protected $tags_single = array(
		'tr',
		'td',
		'li',
		'option'
	);
	
	protected $attr_current = false;
	protected $attr_quote = false;
	/**
	 * Attributes of the tags, which may contain parsed text
	 * 
	 * @var array
	 */
	protected $attrs_allow = array(
		//'title',
		//'alt'
	);
	
	public function __construct($text)
	{
		$this->text = $text;
		$this->offset = 0;	
		$this->length = mb_strlen($this->text);
		$this->chars_allow = $this->strToArray($this->chars_allow);
	}
	
	/**
	 * Returns array chars, contained in str
	 * 
	 * @param $str
	 * @return array
	 */
	protected function strToArray($str)
	{
		return preg_split("//u", $str, 0, PREG_SPLIT_NO_EMPTY);
	}
	
	/**
	 * Returns current context with $length length
	 * 
	 * @param $length
	 * @param $offset
	 * @return string
	 */
	protected function getCurrent($length = 1, $offset = 0) 
	{
		return mb_substr($this->text, $this->offset + $offset, $length);
	}

	/**
	 * Main function of the class
	 */
	public function exec()
	{
		// Find start of the parsed words
		while ($start = $this->getStartWords()) {
			// Get words
			$words = $this->getWords();
			if (!preg_match("/^[\(\)\.,!]*$/u", $words)) {
				// Save words in the text
				$this->saveWords($start, $words);
			}
		}
		return $this->text;		
	}
	
	protected function getStartWords()
	{
		while ($this->offset < $this->length) {
			//echo $this->offset. $this->getCurrent(20). "<br />";
			// Skip existing selections, for example [`test`]
			if ($this->getCurrent(mb_strlen($this->prefix)) == $this->prefix) {
				while ($this->offset < $this->length && $this->getCurrent(mb_strlen($this->suffix)) != $this->suffix) {
					$this->offset++;
				}
				continue;
			}
			if ($this->getCurrent(2) == '<?') {
				while ($this->offset < $this->length && $this->getCurrent(2) != '?>') {
					$this->offset++;
				}
				$this->offset = $this->offset+2;
				continue;
			}
			if ($this->getCurrent() == '{') {
				while ($this->offset < $this->length && $this->getCurrent() != '}') {
					$this->offset++;
				}
				$this->offset++;
				continue;
			}
			
			if ($this->getCurrent(3) == '< ?') {
				while ($this->offset < $this->length && $this->getCurrent(3) != '? >') {
					$this->offset++;
				}
				$this->offset = $this->offset+3;
				continue;
			}		
			if ($this->getCurrent(7) == "<script") {
				while ($this->offset < $this->length && $this->getCurrent(9) != '</script>') {
					$this->offset++;
				}
				continue;
			}
			
			
			$char = $this->getCurrent();			
			switch ($char) {
				case '<': {
					// Skip comments and document definition
					if ($this->getCurrent(2) == '<!') {
						$this->offset += 2;
						while ($this->offset < $this->length && $this->getCurrent() != '>') {
							$this->offset++;
						}
					} elseif ($this->getCurrent(2) == '</') {
						$this->offset += 2;
						
						$tag = $this->getTag();
						while ($this->tags_open) {
							if ($tag == end($this->tags_open))  {
								array_pop($this->tags_open);
								break;
							} 
							array_pop($this->tags_open);
						}
						$this->tag_current = $this->tags_open ? end($this->tags_open) : false;
						$this->offset++;
					} else {
						$this->offset++;
						$tag = $this->getTag();
						array_push($this->tags_open, $tag);
						$this->tag_current = $tag;
						$this->tag_open = true;						
					}
					break;
				}
				case '>': {
					// Close tags <br>, <span/>					
					if ($this->tag_open && (in_array($this->tag_current, $this->tags_end) || $this->getCurrent(1, -1) == '/')) {
						array_pop($this->tags_open);
						$this->tag_current = end($this->tags_open);
					} 
					if ($this->tag_open) {
						$this->offset++;
						$this->tag_open = false;
						break;
					}
				}
				case '&': {
					if (strpos($this->getCurrent(10), ";") !== false) {
						while ($this->offset < $this->length && $this->getCurrent() != ';') {
							$this->offset++;
						}
						$this->offset++;
						break;
					}
				}
				default: {
					
					if ($this->tag_open && !$this->attr_current) {
						$attr = $this->getAttr();
						$this->attr_current = $attr;				
						while ($this->offset < $this->length && preg_match("/[\s\t\r\n]/u", $this->getCurrent())) {
							$this->offset++;	
						}
						if ($this->getCurrent() != '=') {
							$this->attr_current = false;
						} 
					}
					elseif ((
							(!$this->tag_open && in_array($this->tag_current, $this->tags_allow)) ||
							($this->tag_open && in_array($this->attr_current, $this->attrs_allow)) 
						) && 
						in_array($char, $this->chars_allow)
						) {
						if (preg_match("/[\r\n\s\t]/usi", $char)) {
							$this->offset++;
						} else {
							return $this->offset;
						}
					} else {
						if ($this->attr_current && $this->getCurrent() == '"') {
							if (!$this->attr_quote) {
								$this->attr_quote = $this->getCurrent();
							} else {
								if ($this->attr_quote == $this->getCurrent())  {
									$this->attr_quote = false;
									$this->attr_current = false;
								}
							}
						}
						$this->offset++;
					}
				}
			}
		}
		return false;
	}
	
	protected function getWords()
	{
		$words = "";
		while ($this->offset < $this->length && in_array($this->getCurrent(), $this->chars_allow)) {
			$words .= $this->getCurrent();
			$this->offset++;
		}
		return $words;
	}
	

	protected function getAttr()
	{
		$attr = "";

		while (preg_match("/[\n\r\s\t]/usi", $this->getCurrent())) {
			$this->offset++;
		}
		while (preg_match('/[a-zA-Z:-]/u', $this->getCurrent())) {
			$attr .= $this->getCurrent();
			$this->offset++;
		}
		
		return $attr;	
	}
	
	protected function getTag()
	{
		$tag = "";
		while (preg_match("/[\n\r\s\t]/usi", $this->getCurrent())) {
			$this->offset++;
		}
		while (preg_match('/[a-zA-Z]/u', $this->getCurrent())) {
			$tag .= $this->getCurrent();
			$this->offset++;
		}
		return $tag;	
	}
	
	
	protected function saveWords($start,  $words)
	{
		$this->text = mb_substr($this->text, 0, $start).$this->prefix.$words.$this->suffix.mb_substr($this->text, $this->offset);
		$this->offset += mb_strlen($this->prefix) + mb_strlen($this->suffix); 
		$this->length += mb_strlen($this->prefix) + mb_strlen($this->suffix);
	}
}

?>