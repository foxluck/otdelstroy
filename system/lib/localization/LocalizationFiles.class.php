<?php

/**
 * Class for the working with files system
 * 
 * @author A. Muzychenko, alexmuz@gmail.com
 *
 */
class LocalizationFiles
{
	/**
	 * Return array with files in dir
	 * array(
	 * 	subdir => filename,
	 * 	...
	 * )
	 * 
	 * @param $dir
	 * @param $recursive
	 * @return array
	 */
	
	public $files_include = ".*";
	public $dirs_exclude = ".svn";
	
	public $recursive = false;
	
	public function __construct()
	{
		
	}
	
	/**
	 * Returns array of files in the dir
	 * array(
	 * 	subdir => filename,
	 * 	...
	 * )
	 * 
	 * @param $dir
	 * @param $context
	 * @return unknown_type
	 */
	public function getFiles($dir, $context = DIRECTORY_SEPARATOR)
	{
		if (!file_exists($dir)) {
			return array();
		}
		$result = array();
		$dir_handler = opendir($dir);
		while ($file = readdir($dir_handler)) {
			if ($file == '.' || $file == '..') {
				continue;
			} 
			if ($this->recursive && is_dir($dir.DIRECTORY_SEPARATOR.$file) && !preg_match("/^".$this->dirs_exclude."$/ui", $file)) {
				$recursive = $this->recursive--;
				$result = array_merge($result, $this->getFiles($dir.DIRECTORY_SEPARATOR.$file, $context.$file.DIRECTORY_SEPARATOR));							
				$this->recursive = $recursive;
			} else {
				if (preg_match("/^".$this->files_include."$/ui", $file)) {
					$result[] = array($context, $file);
				}
			}
		}
		return $result;
	}
	
	public function save($filename, $text) 
	{
		if (!file_exists(dirname($filename))) {
			mkdir(dirname($filename), 0777, true);
		}
		file_put_contents($filename, $text);
	} 
}
?>