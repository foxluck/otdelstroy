<?php 

class UGController 
{
    public $actions = array();
    protected $action_name = false;
    
    public $ajax = false;
    
    public $layout = false;
    
    public $title = "";
    
    public $smarty = false;
    
    public $public = false;
    
    public function __construct($ajax = false, $action_name = false)
    {
        $this->action_name = $action_name;
    	if (!$this->public && !User::getId()) {
    		Wbs::logout();
    	}
        $this->ajax = $ajax;
        try {
        	$this->exec();
        } catch (Exception $e) {
        	if ($this->ajax) {
	        	$this->actions = array(
	        		new UGAjaxErrorsIndexAction($e)
	        	);        		
        	} else {
               	$this->actions = array(
                	new UGErrorsIndexAction($e)
            	);
        	}
        }
        if (!$this->title) {
        	if (isset($this->actions[0]->title)) {
        		$this->title = $this->actions[0]->title;
        	}
        }
        
        $this->display();
    }
    
    public function exec()
    {  
        if ($this->action_name) {
            $action_name = $this->action_name;
            $this->actions[] = new $action_name;
        }  
    }  
    
    public function prepareData() 
    {
    }

    public function display()
    {
        
        $content = "";
        foreach ($this->actions as $action) {
            try {
            	if (method_exists($action, 'checkLimis')) {
                	$action->checkLimits();
            	}
            } catch (Exception $e) {
                $action->addError($e->__toString());
            }   
            if (method_exists($action, 'getResponse')) {
            	$content .= $action->getResponse();
            } else {
            	$content .= $action->display();
            } 
        }
       
        if ($this->layout && !$this->ajax) {

        	$this->prepareData();
        	
        	if (!$this->smarty) {
            	$this->smarty = Registry::get("UGSmarty");
        	}    
	        
            $url = array(
                'common' => Url::get("/common/"),
                'published' => Url::get("/"),
                'app' => Url::get("/".User::getAppId()."/"),
                'css' => Url::get('/UG/css/'),
            	'img' => Url::get('/UG/img/'),
            );
            if (defined("USE_LOCALIZATION") && USE_LOCALIZATION) {
            	$lang = mb_substr(User::getLang(), 0, 2);
            	$url['js'] = Url::get('/UG/js/' . $lang . '/');
            } else {      
            	$url['js'] = Url::get('/UG/js/source/');
            }
	        $this->smarty->assign("url", $url);
	        $this->smarty->assign("time", microtime(true) - Registry::get('time'));
	        $this->smarty->assign('title', $this->title);                
	        $this->smarty->assign("content", $content);
	        $this->smarty->display("layouts/".$this->layout.".html");
        } else {
            echo $content;
        }
    }    
    
}

?>