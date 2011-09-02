<?php

interface iLayout 
{
    public function invokeAction($name, Action $action, Decorator $decorator = null);
    
    public function display();
} 

?>