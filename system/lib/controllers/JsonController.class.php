<?php

abstract class JsonController extends Controller implements iController
{
    protected $response = array();
    protected $errors = array();
    protected $adv = array();
    
    public function display()
    {
    	if (!$this->errors) {
    	    $data = array('status' => 'OK', 'error' => '', 'data' => $this->response);
    		if ($this->adv) {
    			$data['adv'] = $this->adv;
    		}    		
    		echo json_encode($data);
    	} else {

    		echo json_encode(array('status' => 'ERR', 'error' => $this->errors, 'data' => $this->response));	
    	}        
    }
}