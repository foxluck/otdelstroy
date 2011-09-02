<?php 

interface iView
{
    public function assign($name, $value = null);
    
    public function clear_assign($name);
    
    public function clear_all_assign();
       
    public function fetch($template);
    
    public function display($template);
}

?>