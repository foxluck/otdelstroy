<?php
/**
 * WebAsyst.net
 * 
 * @copyright Copyright (c) 2008, WebAsyst.net
 * @link http://webasyst.net
 * @package Lib
 * @subpackage Model
 * @since 12.12.2008
 */

class DbConnector
{
    private static $handlers = array();
    
    protected function __construct() {}
    
    /**
     * Returns connection to database
     *
     * @param int $code
     * @return res
     */
    public static function getConnection($code = 0)
    {
        $code = (int) $code;
        if (isset(self::$handlers[$code])) {
            return self::$handlers[$code];
        }
        else {
            if ($code) {
                throw new MySQLException("Unknown code of the DataBase Connection");
            } else {
                $dbconf = Wbs::getDbkeyObj()->getDbConfig();
                $handler = self::connect($dbconf["HOST"], $dbconf["PORT"], $dbconf["DB_USER"], $dbconf["DB_PASSWORD"], $dbconf["DB_NAME"], "utf8");
                self::$handlers[0] = $handler;
            }
            return $handler;
        }
    }

    /**
     * Connect to database and add connection's handler to array
     * Returns the code to connect in the future
     * 
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     * @param string $database
     * 
     * @return int - code
     */    
    public function addConnection($host, $port = 3306, $user, $password, $database, $charset = "utf8") 
    {
        $handler = self::connect($host, $port, $user, $password, $database, $charset);
        self::$handlers[] = $handler;
        
        return end(array_keys(self::$handlers));
    }
    
    /**
     * Connect to database and returns handler or false
     * 
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     * @param string $database
     * 
     * @return resource
     */
    protected static function connect($host, $port = 3306, $user, $password, $database, $charset = "utf8")
    {
        if ($port) {
            $host = $host . ":" . $port;        
        }
        // Try connect to database
        $handler = @mysql_pconnect($host, $user, $password);
        if (!$handler) {
            throw new MySQLException(mysql_error(), mysql_errno());
        }
        // Try select database
        if (!mysql_select_db($database, $handler)) {
            throw new MySQLException(mysql_error($handler), mysql_errno($handler));
        }
        
        // Set charset
        if ($charset) {
			@mysql_query ("SET NAMES '" . $charset . "' COLLATE '".$charset."_bin'", $handler);
        }
        
        return $handler;
    }

}
?>