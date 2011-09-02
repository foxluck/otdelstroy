<?php
/**
 * Static class registry
 *
 */
class Registry 
{
    
    protected static $store = array();

    /**
     * Security on new Registry
     *
     */
    protected function __construct() {}

    /**
     * Check data exists by key
     *
     * @param string $name
     * @return bool
     */
    public static function exists($name) 
    {
    	return isset(self::$store[$name]);
    }
    
    /**
     * Returns data by key or false if data not exists
     *
     * @param string $name
     * @return unknown
     */
    public static function get($name) 
    {
        return (isset(self::$store[$name])) ? self::$store[$name] : false;
    }

    /**
     * Save data in store
     *
     * @param string $name
     * @param unknown $obj
     * @return unknown
     */
    public static function set($name, $obj) 
    {
        return self::$store[$name] = $obj;
    }
}

?>