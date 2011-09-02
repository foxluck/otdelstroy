<?php

class LocalizationCompiler 
{
	public $domains = array(
		'ru' => 'webasyst',
		'en' => 'webasyst',
		'de' => 'webasyst'
	);
	// \t - tab
	public $source_path = "";
	public $backup_path = "";
	public $compile_path = "";
		
	public $files_include = ".+\.(js|php|html)";
	public $files_compile = ".+\.(js)";
	public $files_words = ".+\.(tpl|html)";
	public $dirs_exclude = "(\.svn)";
	
	public $split_on_subfolder = true;

	public $locale_path = "locale";
	
	public $debug = true;
	
	// Flags
	public $update_files = false;
	public $update_locale = false;
	public $update_complile = true;
	
	public $recursive = true; 
	
	public function __construct() 
	{

	}
	
	public function exec()
	{
		// Get files
		$lf = new LocalizationFiles();
		$lf->recursive = $this->recursive;
		$lf->dirs_exclude = $this->dirs_exclude;
		$lf->files_include = $this->files_include;
		$files = $lf->getFiles($this->source_path);
		
		$ld = new LocalizationDictionary($this->locale_path);
		$ld->domains = $this->domains;
		
		foreach ($files as $file) {
			$file_path = $this->source_path.$file[0].$file[1];
			// Find new words and mark them
			if ($this->update_files && preg_match("/^".$this->files_words."$/ui", $file[1])) {
				$text = file_get_contents($file_path);
				$lp = new LocalizationPrepare($text);
				if ($this->backup_path) {
				    $lf->save($this->backup_path.$file[0].$file[1], $text);
				}
				$lf->save($file_path, $lp->exec());
			}
			// Find all word and save them in localization dictionary
			if ($this->update_locale) {
				$ld->getWords($file_path);
			}
			
			if ($this->update_complile && preg_match("/^".$this->files_compile."$/ui", $file[1])) {
			    // Check the current dir is not compile dir
			    $dir = str_replace('\\', '/', $file[0]);
			    $dir = explode("/", trim($dir, "/"));
			    $dir = $dir[1];
			    if (isset($this->domains[$dir])) {
			        continue;
			    }
			    if (strpos($file[0], 'source') !== false) {
			        $file[0] = str_replace('source', ':LANG:', $file[0]);
			    } else {
    				$file[0] = preg_replace("/^(\\".DIRECTORY_SEPARATOR."[^\\".DIRECTORY_SEPARATOR."]*\\".DIRECTORY_SEPARATOR.")/ui", "$1:LANG:".DIRECTORY_SEPARATOR, $file[0]);			        
			    }
				$ld->translateFile($file_path, $this->compile_path.$file[0].$file[1]);				
			}
		}
		
		if ($this->update_locale) {
			$ld->save();
		}
	}	
	
	public function change($old, $new)
	{
		// Get files
		$lf = new LocalizationFiles();
		$lf->recursive = $this->recursive;
		$lf->dirs_exclude = $this->dirs_exclude;
		$lf->files_include = $this->files_include;
		$files = $lf->getFiles($this->source_path);
		
		$ld = new LocalizationDictionary($this->locale_path);
		$ld->domains = $this->domains;
		
		foreach ($files as $file) {
			$ld->changeFile($this->source_path.$file[0].$file[1], $old, $new, true);		
		}
		$ld->change($old, $new);
	}	
	
	
}

?>