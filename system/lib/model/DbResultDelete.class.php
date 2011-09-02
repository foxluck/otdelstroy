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
 * DbResultDelete
 * 
 */
class DbResultDelete extends DbResult
{
    /**
     * Returns numbers of the affected rows
     * 
     * @var int
     */
    protected $affected_rows;
    
    protected function onConstruct()
    {
        $this->affected_rows = mysql_affected_rows();
    }
    
    /**
     * @return int 
     */
    public function affectedRows()
    {
        return $this->affected_rows;
    }
    
    /**
     * @return bool
     */
    public function result()
    {
        return $this->result;
    }
}
?>