<?php
class ExpressionValue extends Expression 
{
    protected $value;
    protected $quote;
    
    public function __construct($value, $quote = false)
    {
        $this->value = $value;
        $this->quote = $quote;
    }
    
    public function getValue($data)
    {
        return $this->value;
    }
    
    public function isValue()
    {
        return true;
    }
    
}

?>