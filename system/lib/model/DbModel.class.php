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

class DbModel
{
    protected $handler;
    
    /**
     * Table name
     *
     * @var string
     */
    protected $table    = false;
    protected $id;
    
    public static $queries = array();
    
    /**
     * @var DbCacher
     */
    private $cacher   = null;
    
    /**
     * @var array
     */
    private $cacheCleaners    = array();    
    
    public function __construct($code = 0)
    {
        $this->handler  = DbConnector::getConnection($code);
    }
    
    
    /**
     * Устанавливает кеширующий объект.
     *
     * @param DbCacher $cacher
     */
    public function setCacher(DBCacher $cacher = null)
    {
        $this->cacher = $cacher;
    }
    
    /**
     * Очищает кеш.
     *
     * @param $cacher
     */
    public function addCacheCleaner(DbCacher $cacher)
    {
        $this->cacheCleaners[] = $cacher;
    }
    
    /**
     * Очистка кешеров.
     *
     */
    private function cleanCache()
    {                          
        foreach ($this->cacheCleaners as $cacher) {
            $cacher->delete();
        }
        
        // Чистим список кешеров, что бы не отрабатывали повторно.
        $this->cacheCleaners = array();
    }    

    /**
     * Run query
     * 
     * @param string $sql - SQL query
     * @return mysql_result/boolean - result
     */
    private function run($sql, $unbuffer = false)
    {
        $sql = trim($sql);
        
        $t = microtime(true);
        if ($unbuffer) {
            $res = mysql_unbuffered_query($sql, $this->handler);
        } else {
            $res = mysql_query($sql, $this->handler);
        }
        if (mysql_errno($this->handler)) {
        	$error = "Query Error\nQuery: ".$sql.
        			 "\nError: ".mysql_errno($this->handler) .
        			 "\nMessage: ".mysql_error($this->handler);
            throw new MySQLException($error, mysql_errno($this->handler));
        }
        if (defined('DEVELOPER') && DEVELOPER) {
	        self::$queries[] = array(
	        	(microtime(true) - $t),
	        	$sql
	        );
        }
        return $res;
    }

    /**
     * Execute query without creating object of result
     * 
     * @param string $sql - SQL query
     * @return mysql_result/boolean - result of query
     */
    public function exec($sql)
    {
        
        $DbQueryAnalyzer = new DbQueryAnalyzer($sql);
        
        switch($DbQueryAnalyzer->getQueryType())
        {
            case 'update': case 'replace': case 'delete': case 'insert': $this->cleanCache();
        }
                
        return $this->run($sql);
    }

    /**
     * Execute the query and returns an object of result (itterator), depending on the type of query/ 
     * 
     * @param string $sql - SQL query
     * @return DbResultSelect|DbResultInsert - result
     */
    public function query($sql, $unbuffer = false)
    {
        // Get type of query
        $DbQueryAnalyzer = new DbQueryAnalyzer($sql);
        switch($DbQueryAnalyzer->getQueryType()) {
            case 'update': 
            case 'replace': 
            case 'delete': 
            case 'insert': 
            $this->cleanCache();
        }
        
        if (defined('CACHE_ENABLE') && CACHE_ENABLE && $this->cacher instanceof DbCacher && $DbQueryAnalyzer->getQueryType() == 'select') {
            // if not cached
            if(!$this->cacher->isCached()) {
                $result = $DbQueryAnalyzer->invokeResult($this->run($sql), $this->handler);
                // set cache
                $this->cacher->set($result); 
                $this->cacher = null; 
                
                return $result;
            }
            // Get from cache
            $cache  = $this->cacher->get();
            if (!$cache instanceof DbResultSelect) {
                $this->cacher->delete();
            } else {
                $this->cacher = null;
                return $cache;
            }
        }        
        return $DbQueryAnalyzer->invokeResult($this->run($sql, $unbuffer), $this->handler);
    }

    /**
     * Escapes data
     * 
     * @param mixed $data
     * @return string 
     */
    public function escape($data)
    {
        if(is_array($data)){
            foreach($data as $key => $value){
                $data[$key] = mysql_real_escape_string($value, $this->handler);
            }
            return $data;
        }
        return $data === null ? 'NULL' : mysql_real_escape_string($data, $this->handler);
    }
    
    /**
     * Returns constructor of sql-queries
     *
     * @return DbQueryConstructor
     */
    public function getQueryConstructor()
    {
        return new DbQueryConstructor($this);
    }
    
    /**
     * Get name of the current table
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->table;
    }
    
    /**
     * Get name of the current table
     *
     * @return string
     */
    public function getTableId()
    {
        return $this->id;
    }
    
    public function getById($value, $is_int = true)
    {
        return $this->getQueryConstructor()->findById($value, $is_int)->fetch();
    }
    
    public function getByKey($field, $value, $is_int = true, $all = false)
    {
        $result = $this->getQueryConstructor()->findByKey($field, $value, $is_int);
        if ($all) {
            return $result->fetchAll();
        } else {
            return $result->fetch();
        }
    }    
    
    
    /**
     * Returns prepare for the query
     *
     * @return DbStatement
     */
    public function prepare($sql)
    {
        return new DbStatement($this, $sql);
    }
    
}

?>