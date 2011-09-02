<?php 

class LocalizationDictionary
{
	
	public $path;

	public $domains = array(
		"en" => "default"
	);
	
    protected static $locale = array(
        'ru' => 'ru_RU',
        'en' => 'en_US',
        'de' => 'de_DE',
    	'fr' => 'fr_FR',
    	'it' => 'it_IT',
    	'uk' => 'uk_UA',
    	'es' => 'es_ES'    
    );	
	
	public $project = "webasyst";
	
	protected $words = array();
	
	public function __construct($path)
	{
		$this->path = $path;
	}
	
    public function bindTextDomain($lang) 
    {
    	$locale = isset(self::$locale[$lang]) ? self::$locale[$lang] : $lang;

    	putenv('LC_ALL='.$locale);
        putenv('LANG=' . $lang);
        putenv('LANGUAGE=' . $lang);
    	
		if (!setlocale (LC_ALL, $locale.".utf8", $locale.".utf-8", $locale.".UTF8", $locale.".UTF-8", $lang.'.UTF-8', $lang)) {
			// Set current locale
			setlocale(LC_ALL, '');
		}
        
        bindtextdomain($this->domains[$lang], $this->path);
        bind_textdomain_codeset($this->domains[$lang], 'UTF-8');
        textdomain($this->domains[$lang]);
    }
	
	
	public function create($lang)
	{
		$time = date("Y-m-d H:iO");
		$text = <<<TEXT
msgid ""
msgstr ""		
"Project-Id-Version: {$this->project}\\n"
"POT-Creation-Date: {$time}\\n"
"PO-Revision-Date: \n"
"Last-Translator:  {$this->project}\\n"
"Language-Team:  {$this->project} Team\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=utf-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"X-Poedit-Language: {$lang}\\n"
"X-Poedit-Country: {$lang}\\n"
"X-Poedit-SourceCharset: utf-8\\n"
"X-Poedit-Basepath: .\\n"
"X-Poedit-SearchPath-0: .\\n"
"X-Poedit-SearchPath-1: .\\n"

TEXT;
		
		$locale_path = $this->path.DIRECTORY_SEPARATOR.$lang.DIRECTORY_SEPARATOR."LC_MESSAGES";
		if (!file_exists($locale_path)) {
			mkdir($locale_path, 0777, true);
		}				
		$locale_file = $locale_path.DIRECTORY_SEPARATOR.$this->domains[$lang].".po";
		$f = fopen($locale_file, "w+");
		if (!$f) {
			throw new Exception("Could not create locale: ".$locale_file);
		}
		fwrite($f, $text);
		fclose($f);
	}
	
	/**
	 * 
	 * @param $words_info - array("words", "filename:line");
	 * @return unknown_type
	 */
	public function cache($words_info) 
	{
		$this->words[$words_info[0]] = $words_info[1];
	}
	
	
	public function change($old, $new)
	{
		foreach ($this->domains as $lang => $domain) {
			$locale_path = $this->path.DIRECTORY_SEPARATOR.$lang.DIRECTORY_SEPARATOR."LC_MESSAGES".DIRECTORY_SEPARATOR.$domain.".po";
			if (file_exists($locale_path)) {
				$this->changeFile($locale_path, $old, $new);
			}	
		}
	}
	
	public function changeFile($file, $old, $new, $template = false)
	{
		echo $file."<br />";
		$content = file_get_contents($file);
		if ($template) {
			$old = "[`".$old."`]";
			$new = "[`".$new."`]";
		} else {
			$old = '"'.str_replace('"', '\"', $old).'"';
			$new = '"'.str_replace('"', '\"', $new).'"';
		}
		$content = str_replace($old, $new, $content);
		file_put_contents($file, $content);
	}
	
	public function save()
	{
		foreach ($this->domains as $lang => $domain) {
			$locale_path = $this->path.DIRECTORY_SEPARATOR.$lang.DIRECTORY_SEPARATOR."LC_MESSAGES".DIRECTORY_SEPARATOR.$domain.".po";
			if (!file_exists($locale_path)) {
				$this->create($lang);
			}
			$locale_content = file_get_contents($locale_path);
	        $fh = fopen($locale_path, "a+");
	        flock($fh, LOCK_EX);		
			foreach ($this->words as $words => $lines) {
	        	/* Ищем вхождения текущей фразы */
	            if(strpos($locale_content, "msgid \"" . str_replace('"', '\\"',$words) . "\"") !== false) {
	            	continue;
	            }
	
	            /* Если не нашли - записываем */
	            fputs($fh, "\n#: ".$lines."\n");
	            fputs($fh, "msgid \"" . str_replace('"', '\\"', $words) . "\"\n");
	            fputs($fh, "msgstr \"\"\n");
			}
	        flock($fh, LOCK_UN);
	        fclose($fh);
		}			
	}
	
	public function getWords($file)
	{
		$text = file_get_contents($file);
		$matches = array();
		if (preg_match_all("/\[\`([^\`]+)\`\]/usi", $text, $matches, PREG_OFFSET_CAPTURE)) {
			foreach ($matches[1] as $match) {
				$this->cache(array($match[0], $file.":".$this->getLine($text, $match[1])));
			}			
		}
		if (preg_match_all("/_\(\"((\\\\\"|[^\"])+)\"\)/usi", $text, $matches, PREG_OFFSET_CAPTURE)) {
			foreach ($matches[1] as $match) {
				$this->cache(array($match[0], $file.":".$this->getLine($text, $match[1])));
			}			
		}
		if (preg_match_all("/_\('((\\\\'|[^'])+)'\)/usi", $text, $matches, PREG_OFFSET_CAPTURE)) {
			foreach ($matches[1] as $match) {
				$this->cache(array($match[0], $file.":".$this->getLine($text, $match[1])));
			}			
		}
	}
	
	protected function getLine($text, $pos) 
	{
		$lines = explode("\n", mb_substr($text, 0, $pos));
		return count($lines); //.":".mb_strlen(end($lines));
	}
	
	public function translateFile($source_file, $compile_file) 
	{
		foreach ($this->domains as $lang => $domain) {
			$this->bindTextDomain($lang);
			$text = file_get_contents($source_file);
			$text = preg_replace("/\[\`([^\`]+)\`\]/usie", "\$this->translate('$1')", $text);
			$dist_file = str_replace(":LANG:", $lang, $compile_file);
			if (!file_exists(dirname($dist_file))) {
				mkdir(dirname($dist_file), 0777, true);
			}
			$text = preg_replace("/\.(js|css)([\"'])/usi", ".$1?".time()."$2", $text);
			file_put_contents($dist_file, $text);
		}
	}
	
	public function translate($words)
	{
		$words = str_replace('\"', '"', $words);
		echo $words.":"._($words)."\n";
		return _($words);
	}
	
}

?>