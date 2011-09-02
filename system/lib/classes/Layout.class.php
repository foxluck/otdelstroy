<?php

class Layout implements iLayout
{
    
    protected $blocks = array();
    protected $template = null;
    /**
     * @var View
     */
    protected $view;
    
    public function __construct() {}
    
    
    public function setBlock($name, $content)
    {
        if (isset($this->blocks[$name])) {
            $this->blocks[$name] .= $content;
        } else {
            $this->blocks[$name] = $content;
        }
    }
    
    public function invokeAction($name, Action $action, Decorator $decorator = null)
    {
        $content = $decorator ? $decorator->display($action) : $action->display();
        $this->setBlock($name, $content);
    }
    
    protected function getTemplate()
    {
        if ($this->template === null) {
	        $template = substr(get_class($this), 2);
	        return 'layouts/' . str_replace('Layout', View::POSTFIX, $template);
        } else {
            return 'layouts/' . $this->template . View::POSTFIX;
        }        
    }
    
    public function display()
    {
        $this->view = View::getInstance();
        foreach ($this->blocks as $name => $content) {
            $this->view->assign($name, $content);
        }    
        $app_id = User::getAppId();
        $url = array(
            'common' => Url::get("/common/"),
            'app' => Url::get("/".$app_id."/"),
            'css' => Url::get('/'.$app_id.'/css/'),
            'img' => Url::get('/'.$app_id.'/img/'),
        );
        if (defined("USE_LOCALIZATION") && USE_LOCALIZATION) {
        	$lang = mb_substr(User::getLang(), 0, 2);
            $url['js'] = Url::get('/'.$app_id.'/js/' . $lang . '/');
        } else {      
            $url['js'] = Url::get('/'.$app_id.'/js/source/');
        }
	    $this->view->assign("url", $url);                
        $this->view->display($this->getTemplate());
    }
}

?>