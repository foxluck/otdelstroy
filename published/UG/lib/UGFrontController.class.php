<?php

/**
 * FrontController for UG service
 */
class UGFrontController 
{
    protected $title = '';
    protected $content = '';
    protected $controller;
    protected $ajax = false;
    protected $prefix = 'UG';
    protected $contact_modules = array(
        'analytics', 'construct', 'contacts', 'folders', 'lists', 'widgets', 'duplicates'
    );
    
    public function __construct()
    {
        // Get controller
		$this->controller = $this->getController();
		try {
			// Exec controller and actions
			$this->exec();
		} catch (Exception $e) {
		    if ($this->ajax) {
		        echo json_encode(array("status" => "ERR", "error" => $e->getMessage(), "data" => ""));
		    } else {
		        if ($e instanceof UserException) {
                	error_log($e->getLogMessage());
                	echo new HTMLExceptionDecorator($e);
		        } else {
		        	if (defined('DEVELOPER') && DEVELOPER) {
		    	    	echo $e;
		        	} else {
		        		echo _("Error");
		        	}
		        }
		        //$controller = new ErrorsController($e);
		    }
		}
    }
    
    protected function getController()
    {
	    $module = Env::Get("mod", Env::TYPE_STRING, "index");
	    $action = Env::Get("act", Env::TYPE_STRING, "index");
	    $action = $action ? $action : "index"; 
	    if (Env::Get('ajax') || Env::Post('ajax')) {
	        $this->ajax = true;
	    }
	    if (in_array($module, $this->contact_modules)) {
	        $this->prefix = 'CM';
	    } else {
	        $this->prefix = 'UG';
	    }
	    return $this->prefix.ucfirst($module).ucfirst($action)."Controller";
    }

    /**
     * Execute controller
     */
    protected function exec()
    {
        $action = false;
        // Check the controller is exists
        if (!class_exists($this->controller, true)) {
            $action = str_replace('Controller', 'Action', $this->controller);
            if ($this->ajax) {
                $action = str_replace($this->prefix, $this->prefix.'Ajax', $action);
            }
            if (class_exists($action)) {
                $this->controller = 'UGController';    
            } else {
                throw new Exception('The requested URL, <a href="' . Env::Server('REQUEST_URI') . '">'.Url::getServerUrl().Env::Server('REQUEST_URI').'</a>, was not found on this server.', 404);
            }        
        }

        $controller = new $this->controller($this->ajax, $action);
    }
    
}
?>