<?php

class Env
{
    const TYPE_INT = 1;
    const TYPE_STRING = 2;
    const TYPE_STRING_TRIM = 3;
    const TYPE_BASE64_INT = 4;
    const TYPE_BASE64 = 5;
    const TYPE_ARRAY_INT = 6;

    protected function __construct () {}

    /**
     * Cast value to the type
     * 
     * @param unknown $val
     * @param int $type
     * 
     */
    protected static function Cast ($val, $type = false)
    {
        switch ($type) {
            case self::TYPE_INT: {
                return intval($val);
            }
            case self::TYPE_BASE64: {
                return base64_decode($val);
            }
            case self::TYPE_BASE64_INT: {
                return intval(base64_decode($val));
            }
            case self::TYPE_STRING_TRIM: {
                return trim($val); 
            }
            case self::TYPE_ARRAY_INT: {
            	foreach ($val as &$v) {
            		$v = self::Cast($v, self::TYPE_INT);
            	} 
            	return $val;
            }
            case self::TYPE_STRING:
            default: {
                return $val;
            }
        }
    }

    
    public static function getData($data, $name = false, $type = false, $default = false)
    {
        if (!$name) {
            return $data;
        }
        if (preg_match('!^(.*?)\[(.*?)\]$!i', $name, $match)) {
        	$name1 = $match[1];
        	$name2 = $match[2];
        	$value = isset($data[$name1][$name2]) ? self::checkValue($data[$name1][$name2], $default) : self::getDefault($default);
        } else {
        	$value = isset($data[$name]) ? self::checkValue($data[$name], $default) : self::getDefault($default);
        }		
        return $type ? self::Cast($value, $type) : $value;    	
    }
    
    protected static function checkValue($value, $default) 
    {
    	if (is_array($default) && $default) {
    		if (in_array($value, $default)) {
    			return $value;
    		} else {
    			return $default[0];
    		}
    	} else {
    		return $value;
    	}
    }    
    
    protected static function getDefault($default) 
    {
    	return is_array($default) && $default ? $default[0] : $default;
    }
    
    /**
     * @param string $name
     * @param int $type
     * @param unknown $default
     */
    public static function Get($name = false, $type = false, $default = false)
    {
    	return self::getData($_GET, $name, $type, $default);
    }
    
    public static function Request($name = false, $type = false, $default = false)
    {
    	return self::getData($_REQUEST, $name, $type, $default);
    }

    public static function Files($name = false, $type = false, $default = false)
    {
    	return self::getData($_FILES, $name, $type, $default);
    }    
    
    
    /**
     * @param string $name
     * @param int $type
     * @param unknown $default
     */    
    public static function Post($name = false, $type = false, $default = false)
    {
    	return self::getData($_POST, $name, $type, $default);
    }
    
    /**
     * @param string $name
     * @param int $type
     * @param unknown $default
     */    
    public static function Cookie($name = false, $type = false, $default = false)
    {
    	return self::getData($_COOKIE, $name, $type, $default);
    }

    
    public static function setCookie ($name, $value, $expires = 0, $path = '/', $domain = false, $secure = false, $httponly = false)
    {
        return setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
    }
    
    
    /**
     * @param string $name 
     * @param unknown $default
     */
    public static function Session ($name, $default = false)
    {
        if (! $name) {
            return $_SESSION;
        }
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }

    public static function setSession ($name, $value)
    {
        $_SESSION[$name] = $value;
        return $value;
    }

    public static function unsetSession ($name = false)
    {
        if ($name) {
            unset($_SESSION[$name]);
        } else {
            $_SESSION = array();
            session_destroy();
        }
    }
    
    public static function Server($name = false, $default = false)
    {
        if (!$name) {
            return $_SERVER;
        }
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
    }
    
    public static function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) &&  $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    
    
	public static function getSubdomain() 
	{
		$host = split("\.", Env::Server("HTTP_HOST"));
		return $host[0];
	}
    
}

?>