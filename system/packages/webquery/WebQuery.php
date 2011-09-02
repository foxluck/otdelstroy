<?php
	class WebQuery {
		static public $Params;
		static private $publishedUrl;

		public static function initialize() {
			// calculate published url relative current path
			
			$requestUri = preg_replace("/(\?|\#)(.*)/", "", $_SERVER["REQUEST_URI"]);
			if (Wbs::isHosted()) {
				if ($requestUri == "/webasyst/")
					self::$publishedUrl = $requestUri;
				else 
					return "/";
			}

			$scriptName = $_SERVER['PHP_SELF'];
			
			$matches = array ();
			if (preg_match_all("#(.*)\/published\/(.*)#", $scriptName, $matches)) {
				$relScriptName = $matches[2][0];
			
				$url = str_replace($relScriptName, "", $requestUri);
				self::$publishedUrl = $url;
			}
			
		}
		
		
		public static function getParam($paramName, $defaultValue = null) {
			$params = self::getParams();
			if (!isset($params[$paramName]))
				return $defaultValue;
			return $params[$paramName];
		}
		
		public static function getParams() {
			if (self::$Params)
				return self::$Params;
			
			self::$Params = array_merge($_GET, $_POST);
			return self::$Params;
		}
		
		public static function checkParam($paramName, $value) {
			return self::getParam($paramName) == $value;
		}
		
		public static function saveToSession() {
			throw new NotImplementedException();
		}
		
		public static function getFromSession() {
			throw new NotImplementedException();
		}
		
		public static function decodeParam($paramName) {
			$value = self::getParam($paramName);
			if (!$value)
				return $value;
			return base64_decode($value);
		}
		
		public static function getSubdomain() {
			print_r(debug_backtrace());
			$host = empty($_SERVER['HTTP_X_REAL_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_X_REAL_HOST'];
			$host = split("\.", $host);
			return $host[0];
		}
		
		public static function getPublishedUrl($url, $params = null, $withDomain = false) {
			//if (self::$publishedUrl)
				$url = self::clearUrl(self::$publishedUrl . "/".  $url);
			
			if($withDomain) {
				if (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS'])!='off')) {
					$scheme = "https";
				} else {
					$scheme = "http";
				}

				$host = empty($_SERVER['HTTP_X_REAL_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_X_REAL_HOST'];

				$url = $scheme."://" . self::clearUrl($host . "/" . $url); 
			}

				
			$url = self::getUrl($url, $params);
			
			return $url;
		}
		
		public static function getUrl ($url, $params) {
			$paramsStr = "";
			if ($params && is_array ($params)) {
				$paramsStrParts = array ();
				foreach ($params as $cKey => $cValue) {
					$paramsStrParts[] = rawurlencode($cKey). "=" . rawurlencode($cValue);
				}
				$paramsStr = join("&", $paramsStrParts);
			} elseif($params && is_string($params)) {
				$paramsStr = $params;
			}
			if ($paramsStr)
				$url .= "?". $paramsStr;
			return $url;
		}
		
		public static function clearUrl($url) {
			while (strpos($url, "//") !== false)
				$url = str_replace("//", "/", $url);
			return $url;						
		}
	}
?>
