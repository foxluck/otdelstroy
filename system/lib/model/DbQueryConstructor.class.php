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

class DbQueryConstructor
{
    /**
     * Factory DbModel
     *
     * @var DbModel
     */
    private $context;
    /**
     * Filters
     *
     * @var array
     */
    protected $filters = array();
    /**
     * Fields of the result
     *
     * @var array
     */
    protected $select = array('*');
    /**
     * Class - parent QueryConstructor
     *
     * @var DbQueryConstructor
     */
    private $parent = null;
    /**
     * Join tables
     *
     * @var array
     */
    protected $join = array();
    /**
     * Name of the tablw
     *
     * @var string
     */
    protected $table;
    /**
     * The finished query
     *
     * @var unknown_type
     */
    protected $sql;
    protected $order = array();
    protected $useIndex;
    /**
     * Limit
     *
     * @var array
     */
    protected $limit = array();
    protected $binds = array();
    
    const INNER_JOIN = 1;
    const LEFT_JOIN = 2;
    const OUTER_JOIN = 3;
    const RIGHT_JOIN = 4;

    /**
     * Constructor
     *
     * @param DbModel $context
     */
    public function __construct (DbModel $context)
    {
        $this->context = $context;
        $this->table = $context->getTableName();
    }

    /**
     * Assembling a query
     * 
     * @return string
     */
    protected function assembling ()
    {
        $this->sql = "SELECT " . $this->_sqlOfSelect() . " FROM `{$this->table}` " . $this->_sqlOfTableDefinition() . $this->_sqlOfJoin() . " WHERE " . $this->_sqlOfWhere() . " " . $this->_sqlOfWhereBindings() . " " . $this->_sqlOfOrder() . " " . $this->_sqlOfLimit();
        return $this->sql;
    }

    protected function assemblingCount ()
    {
    	$where = $this->_sqlOfJoin();
    	if ($where) {
    		$where .= " WHERE " . $where . " " . $this->_sqlOfWhereBindings();
    	}
        $this->sql = "SELECT COUNT(*) as count FROM `{$this->table}` " . $this->_sqlOfJoin() . $where;
        return $this->sql;
    }

    /**
     * Set select fields
     * Mind! This method replaces fields.
     *
     * @param unknown_type $fields
     */
    public function fillSelect ($fields)
    {
        $this->select = (array) $fields;
    }

    public function getSQL ()
    {
        return $this->assembling();
    }

    /**
     * Returns name of the table
     *
     * @return string
     */
    public function getTableName ()
    {
        return $this->table;
    }

    public function _sqlOfTableDefinition ()
    {
        if ($this->useIndex) {
            return " USE INDEX(" . $this->useIndex . ") ";
        }
        return '';
    }

    /**
     * Returns SQL-query for the SELECT 
     *
     * @return string
     */
    public function _sqlOfSelect ()
    {
        $sql = '';
        if (sizeof($this->select)) {
            foreach ($this->select as $field) {
                $sql .= (mb_strpos($field, "(") === false ? "`{$this->table}`." : '') . $field . ", ";
            }
        }
        if (sizeof($this->join) && ! $this->hasParent()) {
            foreach ($this->join as &$join) {
                list ($ref, $type) = $join;
                $_join_select = $ref->_sqlOfSelect();
                if ($_join_select != '') {
                    $sql .= $_join_select;
                }
            }
        }
        return rtrim($sql, ", ");
    }

    public function _sqlOfWhereBindings ()
    {
        $sql = "";
        foreach ($this->binds as $bind) {
            list ($ref, $refield, $field, $operator) = $bind;
            switch ($operator) {
                case '=':
                case '!=':
                case '<>':
                case '>=':
                case '>':
                case '<=':
                case '<':
                    $sql .= " AND `{$this->table}`.`{$field}` {$operator} `" . $ref->getTableName() . "`.`{$refield}`";
                    break;
                default:
                    throw new Exception('Assembling binds failed. I don\'t know SQL operator "' . $operator . '".');
            }
        }
        return $sql;
    }

    /**
     * Returns SQL-query for the WHERE.
     *
     * @return string
     */
    public function _sqlOfWhere ()
    {
        $sql = "";
        foreach ($this->filters as $filter) {
            list ($field, $data, $operator) = $filter;
            if ($sql) {
                $sql .= " AND ";
            }
            switch (strtoupper($operator)) {
                case 'IN':
                    foreach ($data as &$value) {
                        $value = $this->context->escape($value);
                    }
                    $sql .= "`{$this->table}`.`{$field}` IN('" . implode("','", $data) . "')";
                    break;
                case 'NOT IN':
                    foreach ($data as &$value) {
                        $value = $this->context->escape($value);
                    }
                    $sql .= "`{$this->table}`.`{$field}` NOT IN('" . implode("','", $data) . "')";
                    break;
                case 'BETWEEN':
                    foreach ($data as $key => &$value) {
                        $value = '"' . $this->context->escape($value) . '"';
                    }
                    $sql .= "(`{$this->table}`.`{$field}` BETWEEN " . implode(' AND ', $data) . ")";
                    break;
                case '=':
                case '!=':
                case '>':
                case '>=':
                case '<':
                case '<=':
                case '<>':
                case 'IS':
                case 'IS NOT':
                case 'LIKE':
                    $value = ($data === null) ? 'NULL' : "'" . $this->context->escape($data) . "'";
                    $sql .= "`{$this->table}`.`{$field}` {$operator} " . $value;
                    break;
                default:
                    throw new Exception('Assembling filters failed. I don\'t know SQL operator "' . $operator . '".');
            }
        }
        return $sql;
    }

    /**
     * Returns SQL-query for the JOIN.
     *
     * @return string
     */
    public function _sqlOfJoin ()
    {
        $sql = '';
        foreach ($this->join as &$join) {
            list ($ref, $type) = $join;
            if ($ref->getTableName() == $this->getTableName()) {
                throw new Exception('Assembling join SQL failed. I\'m not be suicide!');
            }
            switch ($type) {
                case self::INNER_JOIN:
                    $type = 'INNER JOIN';
                    break;
                case self::LEFT_JOIN:
                    $type = 'LEFT JOIN';
                    break;
                case self::OUTER_JOIN:
                    $type = 'OUTER JOIN';
                    break;
                case self::RIGHT_JOIN:
                    $type = 'RIGHT JOIN';
                    break;
                default:
                    $type = 'INNER JOIN';
            }
            $_join_on = $ref->_sqlOfWhere();
            $_join_on .= " " . $ref->_sqlOfWhereBindings();
            if ($_join_on == "1") {
                throw new Exception('Assembling join SQL failed. Empty condition in JOIN by "' . $ref->getTableName() . '"');
            }
            $sql .= "{$type} `" . $ref->getTableName() . "` ON " . $_join_on;
        }
        return $sql;
    }

    /**
     * Returns SQL-query for the ORDER.
     *
     * @return string
     */
    public function _sqlOfOrder ()
    {
        if (count($this->order)) {
            $sql = 'ORDER BY';
            foreach ($this->order as $field => $direct) {
                $sql .= " `$this->table`.`$field` $direct,";
            }
            return rtrim($sql, ',');
        }
        return '';
    }

    /**
     * Returns SQL-query for the LIMIT.
     *
     * @return string
     */
    public function _sqlOfLimit ()
    {
        if (sizeof($this->limit)) {
            return "LIMIT " . (int) $this->limit[0] . ", " . (int) $this->limit[1];
        }
    }

    /**
     * Adds filter
     *
     * @param string $field
     * @param mixed $value
     * @param enum $operator =,!=,<>,>,<,IN, NOT IN, BETWEEN, LIKE
     */
    public function filter ($field, $value, $operator = false)
    {
        // If value is array, then use operator "IN"
        $operator = (! $operator && is_array($value)) ? 'IN' : (! $operator ? '=' : $operator);
        $this->filters[] = array($field , $value , $operator);
    }

    /**
     * Join fields one QueryConstructor to other 
     *
     * @param DbQueryConstructor $ref
     * @param string $refield
     * @param string $field
     * @param string $operator
     */
    public function bind (DbQueryConstructor $ref, $refield, $field, $operator = '=')
    {
        $this->binds[] = array($ref , $refield , $field , $operator);
    }

    /**
     * Adds BETWEEN filter
     *
     * @param string $field
     * @param array $array
     */
    public function between ($field, array $between)
    {
        $this->filter($field, $between, 'BETWEEN');
    }

    /**
     * Compling query, execute it and returns result
     *
     * @return DbResultSelect
     */
    public function find ()
    {
        $this->assembling();
        return $this->context->query($this->sql);
    }

    /**
     * Returns count
     *
     * @return int
     */
    public function count ()
    {
        $this->assemblingCount();
        $result = $this->context->query($this->sql);
        return $result->fetchField('count', 0);
    }

    /**
     * Find record by it key
     *
     * @param int|string $value
     * @param string $field
     * @param bool $is_int cast to int or not, default true
     * @return DbResultSelect
     */
    function findByKey ($field, $value, $is_int = true)
    {
        if ($is_int) {
            $value = (int)$value;
        }
        $this->filter($field, $value);
        return $this->find();
    }
    
    /**
     * Find record by it id
     *
     * @param int|string $value
     * @param bool $is_int cast to int or not, default true
     * @return DbResultSelect
     */
    function findById ($value, $is_int = true)
    {
        if ($is_int) {
            $value = (int)$value;
        }
        $this->filter($this->context->getTableId(), $value);
        return $this->find();
    }    
    
    /**
     * Join table
     *
     * @param DbQueryConstructor $join
     * @param int $type
     */
    public function join (DbQueryConstructor $join, $type = self::INNER_JOIN)
    {
        $join->setParent($this);
        $this->join[] = array($join , $type);
    }

    /**
     * Add a definition of using keys for select 
     *
     * @param string $name
     */
    public function useIndex ($name)
    {
        $this->useIndex = '';
        if (! empty($name)) {
            $this->useIndex = "`" . $this->context->escape($name) . "`";
        }
    }

    /**
     *
     * @return boolean
     */
    public function hasParent ()
    {
        return ($this->parent instanceof DbQueryConstructor);
    }

    /**
     *
     * @param DbQueryConstructor $parent
     */
    public function setParent (DbQueryConstructor $parent)
    {
        $this->parent = $parent;
    }

    public function order ($field, $direct = 'ASC')
    {
        $this->order[$field] = $direct;
    }

    /**
     * Set limit to the result of select
     *
     * @param int $start
     * @param int $limit
     */
    public function limit ($start, $limit)
    {
        $this->limit = array($start , $limit);
    }

    /**
     * Cleaning filters
     *
     */
    public function clear ()
    {
        foreach ($this->join as &$join) {
            $join->clear();
        }
        $this->join = array();
        $this->filters = array();
        $this->limit = array();
        $this->order = array();
        $this->binds = array();
        $this->sql = '';
        $this->parent = null;
        $this->select = array('*');
    }
}
?>