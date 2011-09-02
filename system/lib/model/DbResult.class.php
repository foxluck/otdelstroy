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

/**
 * DbResult
 * 
 * Abstract class
 */
abstract class DbResult
{
    
    /**
     * @var mysql
     */
    protected $mysql;
    
    /**
     * @var mixed
     */
    protected $result;
    
    /**
     * 
     * @final
     * @param mysql $mysql
     * @param mixed $result
     */
    final function __construct($mysql, $result = false)
    {
        $this->mysql = $mysql;
        $this->result = $result;
        $this->onConstruct();
    }
    

    protected function onConstruct() {}
    
    /**
     * Overload calling of the methods for debug
     * 
     * @throws DbResultException
     * @param string $method
     * @param array $args
     */
    final public function __call($method, $args)
    {
        throw new Exception('Undefined method: '.$method);
    }
}
?>