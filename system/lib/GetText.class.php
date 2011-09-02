<?php 

/**
 * Class for loading localization 
 * 
 * @copyright WebAsyst © 2009
 * @author WebAsyst Team
 * @version SVN: $Id$
 */
class GetText 
{
    
    protected static $locale = array(
        'ru' => 'ru_RU',
        'en' => 'en_US',
        'de' => 'de_DE',
    	'fr' => 'fr_FR',
    	'it' => 'it_IT',
    	'uk' => 'uk_UA',
    	'es' => 'es_ES'
    );
    
    protected static $lang = "en";
    protected static $domain = "";
    protected static $locale_path = "";
    
    public static function getLocale($lang)
    {
		// Format $lang
		if (strlen($lang) > 2) {
			$lang = substr($lang, 0, 2);
		}
        return isset(self::$locale[$lang]) ? self::$locale[$lang] : $lang;
    }
    
    
	/**
	 * Load localization
	 * 
	 * @param $lang - lang
	 * @param $locale_path - full path to locale directory
	 * @param $domain - domain
	 * @param $textdomain - call textdomain($domain) or not 
	 */
	public static function load($lang, $locale_path, $domain, $textdomain = true)
	{
		// Format $lang
		if (strlen($lang) > 2) {
			$lang = substr($lang, 0, 2);
		}
		self::$lang = $lang;
		
		//var_dump(self::getLocale($lang), $lang.'.UTF-8', $lang);
		
		$locale = self::getLocale($lang);

		// Put LANG to environment
		putenv('LC_ALL='.$locale);
		putenv('LANG='.$lang);
		putenv('LANGUAGE='.$lang);
		
		// Set locale
		if (!setlocale (LC_ALL, $locale.".utf8", $locale.".utf-8", $locale.".UTF8", $locale.".UTF-8", $lang.'.UTF-8', $lang)) {
			// Set current locale
			setlocale(LC_ALL, '');
		}
		// Bind domain 
		bindtextdomain($domain, $locale_path);
		self::$locale_path = $locale_path;
		bind_textdomain_codeset($domain, 'UTF-8');
		// Set default domain
		if ($textdomain) {
		    self::$domain = $domain;
			textdomain($domain);
		}		
	}
	
	/**
	 * Return current language
	 * 
	 * @return string
	 */
	public static function getLang()
	{
	    return self::$lang;
	}
}

?>