<?php

class View implements iView
{
    const POSTFIX = '.html';
    
    const TYPE_JSON = 1;
    const TYPE_HTML = 2;
    
    protected static $instance = null;
    
    
    /**
     * @var Smarty
     */
    protected $smarty;
     
    /**
     * 
     * @return Smarty
     */
    protected function __construct($application) 
    {
        $this->smarty = Registry::get($application.'smarty');
    } 
    
    /**
     * Singleton
     * 
     * @return View
     */
    public static function getInstance($application = false) 
    {
        if (self::$instance === null) {
          self::$instance = new self($application);
        }
        return self::$instance;
    }    
    
    public function assign($name, $value = null, $type = null)
    {
    	switch ($type) {
    		case self::TYPE_JSON:
    			$value = json_encode($value);
    			break;
    		case self::TYPE_HTML:
    			$value = htmlspecialchars($value);
    			break;
    	}
        $this->smarty->assign($name, $value);
    }
    
    public function clear_assign($name)
    {
        $this->smarty->clear_assign($name);       
    }
    
    public function clear_all_assign()
    {
        $this->smarty->clear_all_assign();
    }

    public function get_vars() 
    {
        return $this->smarty->get_template_vars();
    }
    
    public function fetch($template) 
    {
       return $this->smarty->fetch($template);        
    }

    public function display($template) 
    {
       return $this->smarty->display($template);        
    }    
}

?>