<?php
class ExpressionField extends Expression 
{
    protected $name;
    
    public function __construct($name) 
    {
        $this->name = $name;
    }
        
    public function getValue($data) 
    {
        return isset($data[$this->name]) ? $data[$this->name] : false;
    }
        
    public function getFields()
    {
        return array($this->name);
    }
    
}
?>