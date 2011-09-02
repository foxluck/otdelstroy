<?php

abstract class Action implements iAction
{
    /**
     * @var View
     */
    protected $view;
    protected $app_id;
    
    protected $title = "";
    protected $template = null;
    
    public function __construct()
    {
        $this->view = View::getInstance();
    }
    
    public function prepare() {}
    
    public function getTitle()
    {
        return $this->title;
    }
    
    protected function getTemplate()
    {
        if ($this->template === null) {
	        $template = substr(get_class($this), 2, -6);
        } else {
        	$template = $this->template;
        }
        $match = array();
        preg_match("/^[A-Z][^A-Z]+/", $template, $match);
        $template = strtolower($match[0])."/".$template.View::POSTFIX;
        return $template;
    }
    
    public function display()
    {
        $this->prepare();
        // Set URLs to template
        $app_id = $this->app_id ? $this->app_id : User::getAppId();
        $url = array(
        	'published' => Url::get('/'),
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
        $result = $this->view->fetch($this->getTemplate());
        $this->view->clear_all_assign();
        return $result;
    }
}

?>