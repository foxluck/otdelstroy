<?php

class STWidgetController
{
	protected $id;
	/**
	 * @var WbsSmarty
	 */
	protected $view;
	public function __construct($widget_id)
	{
		$this->id = $widget_id;	
	}
	
	public function exec()
	{
		if (Env::isPost() || Env::Post()) {
			$this->save();
		} else {
			$this->display();
		} 
	}
	
    protected function addRequest($request)
    {
        $request_id =  STRequest::add($request);
        if ($request_id){
	        $classes = Env::Post('classes');
	        if (!empty($classes[0])){
		        $requests_class_model = new STRequestClassModel();
		        $requests_class_model->add($request_id, $classes);
	        }
        }
        return $request_id;
    }
    
	protected function save()
    {
        $widget = new SupportForm($this->id);
        $info = $widget->getInfo();
        $params = $widget->getParam();
        $charset = Env::Post('charset', Env::TYPE_STRING, 'auto');
        $request['subject'] = mb_convert_encoding(Env::Post('summary'),'UTF-8',$charset);
        $request['text'] = mb_convert_encoding(Env::Post('text'),'UTF-8',$charset);
        
        $request['read'] = 0;
        $request['source_type'] = 'form';
        $request['priority'] = 0;
        $request['datetime'] = date("Y-m-d H:i:s");   
        $request['assigned_c_id'] = 0;
        $request['state_id'] = 0;
		$request['source'] = $this->id;
		
        $name = Env::Post('name');
        $from = Env::Post('email');
        $address = MailParser::address($from);
        $action_model = new STActionModel();
        
        $errors = array();
        
        $source_model = new STSourceModel();
        $source = $source_model->getById($params['SOURCEID']);

        $source_params = $source_model->getParams($params['SOURCEID']);
        if ($address) {
            $email = $address[0]['email'];
            $request['client_c_id'] = Contact::getByEmail($email);
            if (!$request['client_c_id']){
                if (!isset($source_params['confirm']) || !$source_params['confirm']) {
	                $request['client_c_id'] = Contact::addByNameEmail($name, $email, 'FORM', $errors);
                    $request['client_from'] = Contact::getName($request['client_c_id'], Contact::FORMAT_NAME_EMAIL, false, false);
                    $action = $action_model->getByType('ACCEPT', 'system');
                    if (!$action['state_id']) {
                        $action['state_id'] = 2;
                    }
                    $request['state_id'] = $action['state_id'];
                } else {
                    $request['client_from'] = $name .' <'.$email.'>';
                }
            } else {
                $request['client_from'] = Contact::getName($request['client_c_id'], Contact::FORMAT_NAME_EMAIL, false, false);
            }
            
            $request_id = $this->addRequest($request);
            if ($request_id) {
                $request['id'] = $request_id;
                
                if (!$request['client_c_id'] && isset($source_params['confirm']) && $source_params['confirm']){
                	$this->sendEmail($params['SOURCEID'], $request, $source_params, 'confirm');
	            } else {
	                // Execute auto accept of the request
                    $request_model = new STRequestModel();
	                $action = $action_model->getByType('ACCEPT', 'system');
	                if ($action['state_id']) {          
	                    $request_model->set($request_id, $action['state_id']);
	                }
					if (isset($source_params['receipt']) && $source_params['receipt']) {
						$this->sendEmail($params['SOURCEID'], $request, $source_params, 'receipt');
					}	                
	            }
            }
        } else {
            $errors[] = _s('Invalid e-mail');
        }
        echo '{"status":"OK","error":"","data":["id" : ' . $request['id'] . ']}';
    }
    
	protected function sendEmail($source_id, $request, $params, $prefix)
	{
		
		$source_model = new STSourceModel();
		$source_info = $source_model->get($source_id);
		$from = isset($params[$prefix.'_email']) && $params[$prefix.'_email'] ? $params[$prefix.'_email'] : $params['email'];
		$from = $source_info['name'] .' <'.$from.">";
		
		$template = new STTemplate();
		$template->setRequest($request);
		if (isset($request['client_c_id']) && $request['client_c_id']) {
			$template->setContact(Contact::getInfo($request['client_c_id']));
		}
		$body = $template->get($params[$prefix.'_body']);
		
		$message = Mailer::composeMessage();
		$message->addTo($request['client_from']);
		$message->addSubject($template->get($params[$prefix.'_subject']));
		$message->addContent($body);
		$message->addFrom($from);
		$message->addAppID('-U');
		
		Mailer::send($message);
	}    
    
	public function display($return = false)
	{
		$widget = new SupportForm($this->id);
		$info = $widget->getInfo();
		$params = $widget->getParam();
		
		$params['LABELS'] = $widget->getParam('LABELS');
		$lang = substr($info['WG_LANG'], 0, 2);
		$view = new WbsSmarty(WBS_DIR . "published/WG/lib/ST/templates", false, $lang);
		
		$url = Url::get('/WG/', true);
        $scripts = <<<HTML
<script type="text/javascript" src="{$url}js/jquery.js"></script>
<script type="text/javascript" src="{$url}js/jquery.validate.js"></script>
<script type="text/javascript" src="{$url}js/st.js"></script>

HTML;
        if ($info['WG_LANG'] == 'rus') {
            $scripts .= <<<HTML
<script type="text/javascript" src="{$url}js/contacts-rus.js"></script>
HTML;
        }
        
        $view->assign('scripts', $scripts);
        
        $target = "";
        if ($widget->getParam('REDIRECT')) {
            if ($widget->getParam("NEWWINDOW")) {
                $target = ' target="_blank"';
            }
            $use_iframe = "";
        } else {
            $use_iframe = " use-iframe";
        }
        $form_tag = '<form class="wbs-st-form'.$use_iframe.'" method="post" action="'.$widget->getSrc().'" '.$target.'>';
        $view->assign('form_tag', $form_tag);

		if (!empty($params['CLASSES'])){
			$class_types_list = explode(';',$params['CLASSES']);
			
			$class_types = array();
	        $class_types_arr = array();
			$class_types_ids = array();
			foreach ($class_types_list as $class_types_item){
			    $item_arr = explode('=',$class_types_item);
			    @$class_types_arr[$item_arr[0]] = $item_arr[1];
	            $class_types_ids[] = $item_arr[0];
			}
			
	        if (!empty($class_types_ids)){
	            $class_type_model = new STClassTypeModel();
	            $class_model = new STClassModel();
	            array_pop($class_types_ids);
	            $class_types = $class_type_model->getByIds($class_types_ids);
	        
	            $classes = array();
		        foreach($class_types as &$class_type){
		            $class_type['is_menu'] = $class_types_arr[$class_type['id']];
		            $class_type['classes'] = $class_model->getByClassType($class_type['id']);
		            $classes[] = $class_type;
		        }
	        }
	        $view->assign('classes', $classes);
		}
        if (isset($params['FIELDS']['captcha'])){
            $url = Url::get("/common/img/loading.gif", true) ;
            $captcha = <<<HTML
<img style="vertical-align:middle" style="display:none" src="{$url}" class="captcha" />&rarr;<input style="width: 75px" id="wbs-field-captcha" name="CAPTCHA" type="text" class="captcha digits" maxlength="4" minlength="4" />
HTML;
            $view->assign('captcha', $captcha);
        }
        if (!isset($params['FIELDS'])){
            $params['FIELDS'] = array();
        }
        if (!isset($params['FIELDS']['name'])){
            $params['FIELDS']['name'] = '';
        }
        if (!isset($params['FIELDS']['email'])){
            $params['FIELDS']['email'] = '';
        }
        if (!isset($params['FIELDS']['summary'])){
            $params['FIELDS']['summary'] = '';
        }
        if (!isset($params['FIELDS']['text'])){
            $params['FIELDS']['text'] = '';
        }
        
        $view->assign('info', $info);
        $view->assign('params', $params);
		if ($return) {
			return $view->fetch('STWidget.html');
		} else {
			$view->display('STWidget.html');
		}
	}
}