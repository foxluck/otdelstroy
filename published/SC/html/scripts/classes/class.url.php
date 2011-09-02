<?php
	class URL{
		
		var $URI = '';
		var $Scheme = '';
		var $Host = '';
		var $Path = '';
		var $Query = '';
		var $Fragment = '';
		var $Port = '';
	
		/**
		 * Load uri info from server info
		 *
		 */
	    function isRealHost() {
				return isset($_SERVER["HTTP_X_REAL_HOST"]);
	    }
		 
		function loadFromServerInfo(){
			
			$this->Scheme = URL::isHttps()?'https':'http';
    		if ($this->isRealHost()){
				$this->Host = $_SERVER["HTTP_X_REAL_HOST"];
    		}else{ 
        		$this->Host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['HTTPS_HOST']/*Perhaps*/;
    		}
			//$this->Host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['HTTPS_HOST']/*Perhaps*/;
			//TODO use match on nonstandart ports
			$this->Host = preg_replace('/:([\d]+)$/','',$this->Host);
			$Parsed = parse_url($_SERVER['REQUEST_URI']);
			$this->Path = isset($Parsed['path'])?$Parsed['path']:'';
			$this->Query = isset($Parsed['query'])?$Parsed['query']:'';
			$this->Fragment = isset($Parsed['fragment'])?$Parsed['fragment']:'';

     		if ($this->isRealHost()){
				$this->Path = str_replace('/shop/', '/', $this->Path);
				$this->Path = str_replace('//', '/', $this->Path);
			}	
			$this->constructURI();
		}
		
		function set($url){
			
			$this->URI = $url;
			$parsed = parse_url($this->URI);
			
			$this->Scheme = isset($parsed['scheme'])?$parsed['scheme']:'';
			$this->Host = isset($parsed['host'])?$parsed['host']:'';
			$this->Path = isset($parsed['path'])?$parsed['path']:'';
			$this->Query = isset($parsed['query'])?$parsed['query']:'';
			$this->Fragment = isset($parsed['fragment'])?$parsed['fragment']:'';
		}
		
		/**
		 * Construct uri
		 *
		 */
		function constructURI(){
			
			$this->URI = $this->Scheme.'://'.$this->Host.$this->Path.($this->Query?'?'.$this->Query:'').($this->Fragment?'#'.$this->Fragment:'');
		}
		
		/**
		 * Return scheme
		 *
		 * @return string
		 */
		function getScheme(){
			return $this->Scheme;
		}
		
		/**
		 * Set scheme
		 *
		 * @param string $Scheme
		 */
		function setScheme($Scheme){
			
			$this->Scheme = $Scheme;
			$this->constructURI();
		}
		
		function getHost(){
			return $this->Host;
		}
		
		/**
		 * @param string $Host
		 */
		function setHost($Host){
			
			$this->Host = $Host;
			$this->constructURI();
		}
		
		function setPath($Path){
			
			if(substr($Path, 0, 1) == '/'){
				$this->Path = $Path;
			}else{
				$this->Path = preg_replace('@\/[^\/]*$@', '/', $this->Path).$Path;
			}
			
      if ($this->isRealHost()){
				$this->Path = str_replace('/shop/', '/', $this->Path);
				$this->Path = str_replace('//', '/', $this->Path);
			}	
			
			$this->constructURI();
		}
		
		/**
		 * Return uri
		 *
		 * @return string
		 */
		function getURI(){
			
			return $this->URI;
		}
	
		function setQuery($Query){
			
			$this->Query = str_replace('?','',renderURL($Query, '?'.$this->Query, false, false));
			$this->constructURI();
		}
		
		function redirect(){
			
			Redirect($this->getURI());
		}
		
		function getPath(){
			
			return $this->Path;
		}
		
		function getPathQuery(){
			
			return $this->Path.($this->Query?'?'.$this->Query:'');
		}
		
		static function isHttps()
		{
			$https = false;
			if(isset($_SERVER['HTTPS'])&&(strtolower($_SERVER['HTTPS'])!='off')){
				$https = true;
			}
			//some servers configured incorrect (some variables missed)
			if(isset($_SERVER['SCRIPT_URI'])&&preg_match('@^https://@',$_SERVER['SCRIPT_URI'])){
				$https = true;
			}
			//and some use proxy or smth. else and SSL mode detects by port
			if(isset($_SERVER['SCRIPT_URI'])&&preg_match('@^http://[^/^:]+:443/@',$_SERVER['SCRIPT_URI'])){
				$https = true;
			}
			//or by special headers
			if(isset($_SERVER['HTTP_X_SCHEME'])&&preg_match('@^https@i',$_SERVER['HTTP_X_SCHEME'])){
				$https = true;
			}
			//or by folder aliases
			if(isset($_SERVER['DOCUMENT_ROOT'])&&preg_match('@/httpsdocs/?@i',$_SERVER['DOCUMENT_ROOT'])){
				$https = true;
			}
			return $https;
		}
	}
?>