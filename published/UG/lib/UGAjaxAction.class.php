<?php 

class UGAjaxAction 
{
    public $title;
    protected $right;
    protected $response = "";
    protected $errors = "";
    
    public function __construct()
    {
    	if ($this->right) {
    		list($app_id, $section_id, $object_id) = $this->right;
    		if ($app_id == User::getAppId() && !User::hasAccess($app_id, $section_id, $object_id)) {
    			echo json_encode(array('status' => 'ERR', 'error' => _s('Access Denied'), 'data' => ''));
    			exit;
    		}
    	}
    }
    
    protected function addError($error, $fields = "")
    {
        $this->errors[] = array(
			'text' => $error,
			'fields' => $fields
        );
    }
    
    public function checkLimits()
    {
    }
    
    
    public function prepareData()
    {
    }

    
    public function getResponse()
    {
        $this->prepareData();
		return json_encode(array(
			'status' => $this->errors ? 'ERR' : 'OK',
			'error' => $this->errors,
			'data' => $this->response
		));
    }
    
}

?>