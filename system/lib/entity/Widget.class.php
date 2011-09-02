<?php

class Widget
{
    protected $id ;
    protected $data = array();
    protected $params = array();
    
    public $param_fields = array();
    
    const EMBED_WIDTH = 200;
	const EMBED_HEIGHT = 200;    
    
    public function __construct($id)
    {
        $this->id = $id;
        $this->load();
    }
    
    public function load()
    {
        $widgets_model = new WidgetsModel();
        $this->data = $widgets_model->getById($this->id);
        if (!$this->data) {
            throw new Exception(_s("Widget not found."));
        }
        $widget_params_model = new WidgetParamsModel();
        $this->params = $widget_params_model->getByWidget($this->id);
    }
    
    public function getInfo($name = false)
    {
        if (!$name) {
            return $this->data;
        }
        return isset($this->data[$name]) ? $this->data[$name] : false; 
    }
    
    public function issetParam($name)
    {
        return isset($this->params[$name]);
    }
    
    public function getParam($name = false, $default = false)
    {
	    if (!$name) {
	        if (!$this->param_fields) {
	            return $this->params;
	        }
	        // Default parameters must be on widget's language
	        GetText::load($this->getInfo('WG_LANG'), SYSTEM_PATH . "/locale", 'system', false);
            $result = array();
            foreach ($this->param_fields as $param_id => $param_info) {
                $result[$param_id] = $this->getParam($param_id);
            }
            // Restore system language
            GetText::load(User::getLang(), SYSTEM_PATH . "/locale", 'system', false);
            return $result;
        } elseif (isset($this->params[$name])) {
        	return $this->params[$name];
        }       
        
        // Get default
        if ($default) {
            return $default;
        }
        
        if (isset($this->param_fields[$name]['default'])) {
            if (isset($this->param_fields[$name]['gettext']) && $this->param_fields[$name]['gettext']) {
                return _s($this->param_fields[$name]['default']);
            } else {
                return $this->param_fields[$name]['default'];
            }
        }
        return false;
    }
    
    public function getSrc($params = "")
    {
        $q = base64_encode(Wbs::getDbKey()) . "-" . $this->data["WG_FPRINT"];
		$src= Url::get("/WG/show.php?q=".$q, true) ;
		if ($params) {
			$src .= "&" . $params;
		}
		return $src;
	}
    
	public function getHeight()
	{
	    return $this->getParam('HEIGHT', self::EMBED_HEIGHT); 
	}
    
	public function getEmbedInfo () 
	{
		$scrolling = "NO";
		$widthAdd = 25;
		$heightAdd = 30;
		$styleStr = "";

		$width = $this->getParam('WIDTH', self::EMBED_WIDTH);
		$height = $this->getHeight();
		
		$src = $this->getSrc();
		
		if (is_numeric($width)) {
			$width += $widthAdd;
		}
		if (is_numeric($height)) {
			$height += $heightAdd;
		}
			
		$info["src"] = $src;
		$info["previewSrc"] = $this->getSrc("mode=preview");
		$info["previewEditSrc"] =  $this->getSrc("mode=previewEdit");
		$info["editSrc"] =  $this->getSrc("mode=edit");
		$info["typepadSrc"] =  $this->getSrc("mode=typepad");
		$info["igoogleSrc"] = rawurlencode( $this->getSrc("mode=igoogle"));
					
		$info["width"] = $width;
		$info["height"] = $height;
		$info["realCode"] = '<iframe allowtransparency="true" scrolling="'.$scrolling.'" width="'.$width.'" height="'.$height.'" frameborder="0" src="'.$src.'" '.$styleStr.'></iframe>';
		$info["code"] = htmlspecialchars($info["realCode"]);
		
		return $info;
	}    
}

?>