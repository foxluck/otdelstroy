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
 * class DbQueryAnalyzer
 *
 */
class DbQueryAnalyzer
{
    protected $query_string;
    protected $type = false;
    
    const QUERY_SELECT  = 'select';
    const QUERY_INSERT  = 'insert';
    const QUERY_REPLACE = 'replace';
    const QUERY_DELETE  = 'delete';
    const QUERY_UPDATE  = 'update';
    
    /**
     * Query benchmarking
     *
     * @var DbQueryBenchmark
     */
    //protected $DbQueryBenchmark;
    
    /**
     * mysql
     *
     * @var resource
     */
    protected $handler;
    
    function __construct($query)
    {
        $this->query_string = trim($query);
        $this->whatDaQuery();
    }
    
    private function whatDaQuery()
    {
        $known_queries = array('select', 'insert', 'replac', 'delete', 'update', 'trunca', 'alter ');
        $type = strtolower(mb_substr($this->query_string, 0, 6));
        if(!in_array($type, $known_queries)) {
            throw new Exception('Unknown or not supported query type: '.$type);
        }

        if ($type == 'replac') $type = 'replace';
        if ($type == 'trunca') $type = 'truncate';
        
        $this->type = trim($type);
        
        /**
        if($this->getQueryType() == self::QUERY_SELECT && !PRODUCTION && !defined('CLI_APPLICATION'))
        {
            $this->DbQueryBenchmark = new DbQueryBenchmark($this->query_string);
        }
		*/
    }
    
    function getQueryType()
    {
        return $this->type;
    }
    
    function isSelectCount()
    {
        if(preg_match("/^\s*SELECT\s+COUNT\(/ius", $this->query_string))
        {
            return true;
        }
        
        return false;
    }
    
    function invokeResult($query_result, $handler)
    {
        switch($this->type){            
            case 'select':{
                $result = new DbResultSelect($handler, $query_result);
                break;
            }
            case 'insert':{
                $result = new DbResultInsert($handler, $query_result);
                break;
            }
            case 'update':{
                $result = new DbResultUpdate($handler, $query_result);
                break;
            }
            case 'delete':{
                $result = new DbResultDelete($handler, $query_result);
                break;
            }
            case 'replace':{
                $result = new DbResultReplace($handler, $query_result);
                break;
            }
            default:{
                throw new Exception('Unknown result class name: DbResult'.ucfirst($this->type));
            }
        }
        
        /*
        if($this->getQueryType() == self::QUERY_SELECT && !PRODUCTION && !defined('CLI_APPLICATION')) {
            $this->DbQueryBenchmark->examine($handler);
            $this->DbQueryBenchmark->store();
        }
		*/
        return $result;
    }
}

?>