<?php
class RSSFeedGenerator
{
	var $SQL = '';
	var $limit = 20;
	var $channelImage = null;
	var $channel = null;
	var $additionalElementSource = null;
	var $itemElements = array('title','description','author','pubDate','link');
	private $file = null;
	private $itemHandler = null;
	
	function setChannel($title,$link,$description){
		$this->channel = array('title'=>$title,'link'=>$link,'description'=>$description);
	}
	
	function setImage($imageUrl,$title,$link){
		$this->channelImage = array('url'=>$imageUrl,'title'=>$title,'link'=>$link);
	}
	
	function generateFeed($fileName){
		
		$Register = &Register::getInstance();
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/* @var DBHandler DataBase*/
				
		if(!$this->openRSSFeed($fileName))return false;
		
		$DBHandler->query($this->SQL.($this->limit?(' LIMIT '.$this->limit):''));

		while($item = $DBHandler->fetch_assoc()){
			$this->writeItem($item);
		}
		$DBHandler->freeResult();
		$this->closeRSSFeed();
		return true;
	}
	
	private function writeItem($item){
		$this->validateItem($item);
		
		$xmlNode = '';
		foreach($this->itemElements as $itemName=>$itemElement){
			if(isset($item[$itemElement])){
				$itemName = is_string($itemName)?$itemName:$itemElement;
				$itemClose = explode(' ',$itemName);
				$itemClose = $itemClose[0];
				$xmlNode .= sprintf("\t\t\t<{$itemName}>%s</{$itemClose}>\n",$this->parseData($item[$itemElement],$itemName));
			}
		}
		if(!strlen($xmlNode))
			return;
		fwrite($this->file,sprintf("\t\t<item>\n%s\t\t</item>\n\n",$xmlNode));
	}
	private function parseData($data,$key = ''){
		switch($key){
			case 'pubDate':
				$data = date('r',$data);
				break;
			case 'description_':
				//$data = "<![CDATA[".strip_tags(trim(($data)))."]]>";
				$data = "<![CDATA[".htmlspecialchars(trim(($data)))."]]>";
				break;
			case 'description':				
			case 'content:encoded':
				$data = "<![CDATA[".trim(($data))."]]>";
				break;
			case 'link':
			case 'guid':
				$data = str_replace('&','&amp;',$data);
				break;
			default:
				$data = htmlspecialchars(trim(($data)));
		}
		
		return $data;
	}
	
	function setItemHandler($handler)
	{
		if(function_exists($handler)){
			$this->itemHandler = $handler;
		}elseif(strlen($handler)){
			$this->itemHandler = create_function('$item',$handler);
		}
	}
	
	private function validateItem(&$item)
	{
		if($this->itemHandler){
			$item = call_user_func($this->itemHandler,$item);
		}
	}
	
	private function openRSSFeed($fileName){
		if(file_exists($fileName)){
			unlink($fileName);
		}
		$file = fopen($fileName,'w');
		if(!$file)return false;
		$this->file = &$file;
		
		fwrite($file,"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
		$additionalElements = '';
		foreach($this->additionalElementSource as $element=>$source){
			 $additionalElements .=" {$element}=\"{$source}\"";
		}
		fwrite($file,sprintf("<rss version=\"2.0\"%s>\n",$additionalElements));
		
		fwrite($file,"\t<channel>\n");
		
		
		foreach($this->channel as $key=>$value){
			fwrite($file,sprintf("\t\t<{$key}>%s</{$key}>\n",$this->parseData($value,$key)));		
		}
		if(is_array($this->channelImage)){
			fwrite($file,"\t\t<image>\n");
			foreach($this->channelImage as $key=>$value){
				fwrite($file,sprintf("\t\t\t<{$key}>%s</{$key}>\n",$this->parseData($value,$key)));		
			}
			fwrite($file,"\t\t</image>\n");
		}
		
		fwrite($file,sprintf("\t\t<lastBuildDate>%s</lastBuildDate>\n\n",date('r')));		
		return true;
	}
	
	private function closeRSSFeed(){
		if($this->file);
		fwrite($this->file,"\t</channel>\n</rss>");
		fclose($this->file);
		$this->file = null;
	}
	
}
?>