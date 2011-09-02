<?php
/**
 * class used to export varios data from database to file in CSV format
 * in future will be added XML and some other output formats
 *
 */
class ExportData{
	var $sqlQuery = '';
	var $sqlWhereClause = '';
	var $sqlGroupClause = '';
	var $sqlOrderClause = '';
	var $fieldsDescription = array();
	var $fileName = '';
	/**
	 * Charset for exported file
	 *
	 * @var string
	 */
	var $charset = 'utf-8';
	/**
	 * fields separator
	 *
	 * @var string
	 */
	var $separator =';';

	/**
	 * flat/xml/...
	 *
	 * @var string
	 */
	var $format = 'flat';
	var $format_params = array();
	private $rows = array();
	private $headers = array();
	private $rowHandler;
	private $rowParserHandler;
	private $fileHandler;
	private $totalCount;

	function __destruct()
	{
		$this->closeFile();
	}

	private function prepareQuery()
	{
		return $this->sqlQuery.
		(strlen($this->sqlWhereClause)?((strpos(strtoupper($this->sqlWhereClause),'WHERE')===false?' WHERE ':' ').$this->sqlWhereClause):'').
		(strlen($this->sqlOrderClause)?((strpos(strtoupper($this->sqlOrderClause),'ORDER BY')===false?' ORDER BY ':' ').$this->sqlOrderClause):'').
		(strlen($this->sqlGroupClause)?((strpos(strtoupper($this->sqlGroupClause),'GROUP BY')===false?' GROUP BY ':' ').$this->sqlGroupClause):'');
	}

	public function exportDataToFile($fileName)
	{
		$this->fileName = $fileName;
		$Register = &Register::getInstance();
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/*@var $DBHandler DataBase*/
			
		$query = $this->prepareQuery();
		if($safeMemory){
			$totalCount = $this->getTotalCount();

		}else{
			//$this->traceMemory('begin');
			$DBHandler->ph_query($query);
			$this->totalCount = $DBHandler->num_rows();
			$this->openFile();

			$this->writeHeader($this->headers);
			//$this->traceMemory('query');
			//		$count = 0;$DBHandler
			while($row = $DBHandler->fetch_assoc()){
				//		$this->traceMemory(++$count);
				$explain = $this->validateRow($row);
				if($row){
					$rows = $explain?$row:array($row);
					foreach($rows as $row){
						$res = $this->writeRow($row);
						if(PEAR::isError($res))break 2;
					}
				}
			}
			$DBHandler->freeResult();
			//$this->traceMemory('END');
			$this->closeFile();
			if(PEAR::isError($res))return $res;
		}
	}
	function getTotalCount()
	{
		return $this->totalCount;
	}

	private function validateRow(&$row)
	{
		$explain = false;
		if($this->rowHandler){
			$row = call_user_func($this->rowHandler,$row,&$explain);
		}
		return $explain;
	}

	private function parseRow(&$row)
	{
		switch($this->format){
			case 'flat':
				$row = implode($this->separator,array_map(array('ExportData','safeOutput'),$row)).$this->separator."\r\n";
				$this->convertRow($row);
				break;
			case 'xml':
				$row_name = isset($this->format_params['row_name'])?$this->format_params['row_name']:'row';
				$row = array_map(array('ExportData','safeXmlOutput'),$row);

				$row_items = array_combine($this->headers,$row);
				$row = "\n\t<{$row_name}>";
				foreach($row_items as $node=>$value){
					$row.="\n\t\t<{$node}>{$value}</{$node}>";
				}
				$row .= "\n\t</{$row_name}>";
				break;
		}
		return $row;
	}
	private function parseHeader($header)
	{
		return implode($this->separator,array_map(array('ExportData','safeOutput'),$header)).$this->separator."\r\n";
	}
	private function writeRow(&$row)
	{
		if(!$this->fileHandler){
			return PEAR::raiseError("Error use {$this->fileName} for writing");
		}
		fwrite($this->fileHandler,$this->parseRow($row));
	}
	private function writeHeader($header)
	{
		if(!$this->fileHandler){
			return PEAR::raiseError("Error use {$this->fileName} for writing");
		}
		switch($this->format){
			case 'flat':
				fwrite($this->fileHandler,$this->convertRow($this->parseHeader($header)));
				break;
		}
	}
	public function setRowHandler($handler)
	{
		if(function_exists($handler)){
			$this->rowHandler = $handler;
		}else{
			$this->rowHandler = create_function('$row,$explain = false',$handler);
		}
	}
	public function setHeaders($headers = array())
	{
		$this->headers = $headers;
	}
	private function openFile()
	{
		if($this->fileHandler)return true;
		$this->fileHandler = fopen($this->fileName,'w');
		if(!$this->fileHandler)return PEAR::raiseError("Error open {$this->fileName} for write");
		switch($this->format){
			case 'xml':
				fwrite($this->fileHandler,sprintf('<?xml version="1.0" encoding="%s"?>'."\n",strtoupper($this->charset?$this->charset:DEFAULT_CHARSET)));
				if(isset($this->format_params['header'])){
					fwrite($this->fileHandler,$this->format_params['header']."\n");
				}
				break;
		}
	}

	private function closeFile()
	{
		if($this->fileHandler)
		{
			switch($this->format){
				case 'xml':
					if(isset($this->format_params['footer'])){
						fwrite($this->fileHandler,"\n".$this->format_params['footer']);
					}
					break;
			}
			fclose($this->fileHandler);
			$this->fileHandler = null;
		}
	}
	private function convertFile()
	{
		if($this->charset && $this->charset != DEFAULT_CHARSET){
			iconv_file(DEFAULT_CHARSET, $this->charset, $this->fileName);
		}
	}
	private function convertRow(&$row)
	{
		if($this->charset && $this->charset != DEFAULT_CHARSET){
			$row = mb_convert_encoding($row,$this->charset,DEFAULT_CHARSET);
		}
		return $row;
	}

	static function safeOutput($field)
	{
		$field = str_replace(array('"','<br>','\r\n','\r','\n',"\r\n","\r","\n"),array('""',' '," "," ",' ',' ',' ',' '),$field);
		$field = str_replace('////n',"\r\n",$field);
		$field = preg_replace(array('/\s+$/','/(\s)\s+/'),array('','$1'),$field);
		return '"'.$field.'"';
	}
	static function safeXmlOutput($field)
	{
		$field = str_replace(array('&','\'','"','>','<'),
		array('&amp;','&apos;','&quot;','&gt;','&lt;'),$field);
		return $field;
	}

	private function traceMemory($msg = '')
	{
		static $prev;
		$current = memory_get_usage();
		$delta =  $current - $prev;
		$prev = $current;
		print sprintf('%01.2f kB delta %01.0f B %s<br>',$current/1048576,$delta, $msg);
	}
}
?>