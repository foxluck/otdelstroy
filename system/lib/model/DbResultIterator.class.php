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
 * DbResultIterator
 */
class DbResultIterator implements Iterator
{
    /**
     * Current element
     * 
     * @var mixid
     */
    protected $current    = null;
    
    /**
     * Current key
     * 
     * @var int
     */
    protected $key        = 0;
    
    /**
     * Resource
     *
     * @var mysql_result
     */
    private $result;

    /**
     *
     * @param mysql_result $result
     */
    public function __construct($result)
    {
        $this->result   = $result;
    }
    
    /**
     * Returns current element
     * 
     * @return mixed
     */
    public function current()
    {
        return $this->current;
    }
    
    /**
     * Returns current key
     * 
     * @return int
     */
    public function key()
    {
        if($this->key >= $this->count())
        {
            $this->rewind();
        }
        
        return $this->key;
    }
    
    /**
     * Shift key to the next element
     * 
     * @return array
     */
    public function next()
    {
        $this->key++;
        $this->current = mysql_fetch_assoc($this->result);
        return $this->current;
    }
    
    /**
     * Reset key
     * 
     */
    public function rewind()
    {
        $this->current = null;
        $this->key     = 0;
        
        if ($this->count()) {
            $this->seek(0);
            $this->current  = mysql_fetch_assoc($this->result);
        }
        
        return $this->current;
    }
    
    /**
     * Check on valid result
     * Example:
     * <code>
     * while($result->valid()){
     *      print_r($result->next());
     * }
     * </code>
     * 
     * @return bool
     */
    public function valid()
    {
        if (!$this->count()) {
            return false;
        }
        
        return $this->key < $this->count();
    }
    
    /**
     * Seek on mysql_result
     * 
     * @return bool
     */
    public function seek($offset = 0)
    {
        if($offset < $this->count()) {
            return mysql_data_seek($this->result, $offset);
        }
        
        return false;
    }
    
    /**
     * Returns result of the count
     *
     * @return int
     */
    public function count()
    {
        return mysql_num_rows($this->result);
    }
    
    public function fetch($mode = MYSQL_ASSOC)
    {
        return mysql_fetch_array($this->result, $mode);
    }
    
    /**
     * Export data
     *
     * @return array
     */
    public function export($key = null, $normalize = false)
    {
        $rows   = array();
        if($key) {
            foreach ($this as $row) {
            	$index = $row[$key];
            	if ($normalize) {
            		unset($row[$key]);
            		if (count($row) == 1) {
            			$row = array_shift($row);
            		}
            	}
                $rows[$index] = $row;
            }
        } elseif ($normalize) {
            foreach ($this as $row) {
                $rows[] = array_shift($row);
            }
        } else {
            foreach ($this as $row) {
                $rows[] = $row;
            }
        }
        
        return (array) $rows;
    }
    
    /**
     * Free resource
     * 
     * @return boolean
     */
    public function free()
    {
        mysql_free_result($this->result);
    }
}

?>