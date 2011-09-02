<?php

class Url
{
    /**
     * Returns link
     * 
     * @example Url::get('/common/', false) == '/wbs/published/common/'; 
     * 
     * @param string $url - relative link 
     * @param int $full - http://domain/... or /...  
     * @return string
     */   
    public static function get($url, $full = 0)
    {
        if (!Wbs::isHosted()) {
	       
	        $request_url = preg_replace("/\?(.*)/", "", Env::Server('REQUEST_URI'));
	        if (preg_match("/(.*)published(.*)/i", $request_url)) {
	        	$url = preg_replace("/(.*)published(.*)/i", "$1published".$url, $request_url);
	        } else {
	        	$url = Wbs::getSystemObj()->getWebUrl()."published".$url;
	        }    	
        }
            	
    	if ($full) {    	
	    	return self::getServerUrl(2 == (int)$full).$url;
    	} else {
    		return $url;
    	}
    }
    
    /**
     * Returns url to this host /
     * 
     * @return string
     */
    public static function getServerUrl($hosted_only = false)
    {   	
    	if (strtolower(Env::Server('HTTPS')) == 'on' || Env::Server('SERVER_PORT') == 443) {
    		$scheme = 'https://'; 
    	} else {
    		$scheme = 'http://';
    	}
    	if ($hosted_only && Wbs::isHosted() && ($dbname = Wbs::getDbName())) {
    	    $hosts = explode(".", Env::Server('HTTP_HOST'), 2);
    	    $host = Wbs::getDbName().".".$hosts[1];   
    	} else {
            $host = Env::Server('HTTP_HOST');
    	    if (Wbs::isHosted()) {
                if (function_exists("getallheaders")){
                    $request_headers = getallheaders();
                    if (isset($request_headers['X-Real-Host'])) {
                        $host = $request_headers['X-Real-Host'];
                    }
                }    	        
    	    } 
    	}
	    return $scheme.$host; 		   	
    }
    
    /**
     * Redirects to the url
     * Send header Location: $url
     * 
     * @param $url
     */
    public static function go($url, $is_full_url = false) 
    {
    	header("Location: ".($is_full_url ? $url : self::get($url)));
    	exit();	
    }
}

?>