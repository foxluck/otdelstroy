<?php 

class UGViewAction 
{
    /**
     * @var Smarty
     */
    protected $smarty;
    protected $template = true;
    public $show = true;
    protected $right = array();
    protected $app_id = false;
    protected $errors = array();
    public $title;
    
    public function __construct()
    {
    	if ($this->app_id) {
    		User::setApp($this->app_id);
    	}
    	if ($this->right) {
    		list($app_id, $section_id, $object_id) = $this->right;
    		if ($app_id == User::getAppId() && !User::hasAccess($app_id, $section_id, $object_id)) {
    			Url::go('/common/html/access_denied.html');
    		}
    	}
        $this->smarty = Registry::get("UGSmarty");
    }
    
    
    public function addError($error)
    {
        $this->errors[] = $error;
    }
    
    public function prepareData()
    {
        
    }
    
    public function getTemplate()
    {
        if ($this->template === true) {
            $this->template = substr(get_class($this).".html", 2);
            $this->template = str_replace(array('Action.html', '.html'), '.html', $this->template);
        } 
    }
    
    public function checkLimits()
    {
        
    }
    
    public function getResponse()
    {
        $this->getTemplate();
        if ($this->show) {
            $this->prepareData();

            $this->smarty->assign('errors', $this->errors);
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
            
            
            $this->smarty->assign('user_lang', User::getLang());
	        $this->smarty->assign("url", $url);
	        $this->smarty->assign("tab", Env::Get('tab'));

	        $back = Env::Server('HTTP_REFERER', 'index.php');
			if (strpos($back, '?') === false) {
				$back .= "?p=".Env::Get('p', Env::TYPE_INT, 0);
			} else {
				$back .= "&p=".Env::Get('p', Env::TYPE_INT, 0);
			}
			$this->smarty->assign('back', $back);	        
        		        
            return $this->smarty->fetch($this->template);
        } else {
            return "";
        }
    }
    
    
}

?>