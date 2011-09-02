<?php

abstract class ViewController extends Controller implements iController
{
    /**
     * @var Layout
     */
    protected $layout = null;
    
    protected $blocks = array();
    
    public function __construct()
    {

    }
    
    public function setLayout(Layout $layout)
    {
        $this->layout = $layout;
    }
    
    public function exec() {}
    
    
    /**
     * Adding action
     *  
     * @param Action $action
     * @param Decorator $decorator
     * @param string $name
     */
    public function invokeAction(Action $action, Decorator $decorator = null, $name = 'content') 
    {
        $content = $decorator ? $decorator->display($action) : $action->display();
        if (isset($this->blocks[$name])) {
            $this->blocks[$name] .= $content;
        } else {
            $this->blocks[$name] = $content;
        }
        
    }
    
    public function display()
    {
        if ($this->layout instanceof Layout) {
            foreach ($this->blocks as $name => $content) {
                $this->layout->setBlock($name, $content);
            }
            $this->layout->display();
        } else {
            foreach ($this->blocks as $name => $content) {
                echo $content;    
            }
        }
    }
    
    public function isAjax()
    {
    	return Env::Server('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';	
    }
    
}

?>