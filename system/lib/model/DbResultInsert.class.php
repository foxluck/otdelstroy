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
 * DbResultInsert
 */
class DbResultInsert extends DbResultDelete
{
    
    /**
     * @var int $insert_id
     */
    protected $insert_id;
    
    protected function onConstruct()
    {
        parent::onConstruct();
        $this->insert_id     = mysql_insert_id($this->mysql);
    }
    
    /**
     * @return int insert_id
     */
    public function lastInsertId()
    {
        return $this->insert_id;
    }
}
?>