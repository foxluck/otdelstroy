<?php

class Decorator implements iDecorator
{
    /**
     * 
     * @var View
     */
    protected $view;
    protected $template = null;
    public function __construct()
    {
        $this->view = View::getInstance();
    }
    
    protected function getTemplate()
    {
        if ($this->template === null) {
	        $template = substr(get_class($this), 2);
	        return 'decorators/'.str_replace('Decorator', View::POSTFIX, $template);
        } else {
            return 'decorators/'.$this->template.View::POSTFIX;
        }        
    }
    
    public function prepare() 
    {
    	
    }
    
    public function display(Action $action)
    {
        $content = $action->display();
        $this->prepare();
        $this->view->assign('title', $action->getTitle());
        $this->view->assign('content', $content);
        $result = $this->view->fetch($this->getTemplate());
        $this->view->clear_all_assign();
        return $result;
    }
}