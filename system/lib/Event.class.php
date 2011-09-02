<?php
/**
 *
 * @author WebAsyst Team
 *
 */
class Event
{
	/**
	 *
	 * @param $event string
	 * @param $arg1 mixed
	 * @param $arg2 mixed
	 * @return none
	 */
	public static function fireEvent($event)
	{
		$params = func_get_args();
		$event = array_shift($params);
		$event_handlers = array();
		try{
			$events_model = new EventsModel();
			$event_handlers = $events_model->getByEvent($event);


			foreach($event_handlers as $app_id=>$application_handlers)
			{
				if(is_array($application_handlers)&&$application_handlers){
					#get application handler
					$class = 'Event'.strtoupper($app_id);
					$path = WBS_DIR.'/published/'.strtoupper($app_id).'/'."{$class}.class.php";
					if(class_exists($class,false)||((include($path))&&class_exists($class,false))){

					}else{
						$class = __CLASS__;
					}

					try{
						$event_handler = new $class();
						if(($class!=__CLASS__)&&(get_parent_class($event_handler) != __CLASS__)){
							unset($event_handler);
							$event_handler = new self();
						}
						foreach($application_handlers as $application_handler){
							try{
								$event_handler->callEventHandler($event,$app_id,$application_handler,$params);
							}catch(Exception $exception){
								self::onException($exception);
							}
						}
					}catch(Exception $exception){
						self::onException($exception);
					}
				}
			}
		}catch(Exception $exception){
			self::onException($exception);
		}
	}

	/**
	 *
	 * @param $event
	 * @param $app_id string
	 * @param $handler
	 * @return boolean
	 * @throws MySQLException
	 */
	public static function registerEventHandler($event,$app_id,$handler)
	{
		$events_model = new EventsModel();
		return $events_model->registerEventHandler($event,$app_id,$handler);
	}

	/**
	 *
	 * @param $event
	 * @param $app_id string
	 * @param $handler
	 * @return boolean
	 * @throws MySQLException
	 */
	public static function unregisterEventHandler($event,$app_id,$handler)
	{
		$events_model = new EventsModel();
		return $events_model->unregisterEventHandler($event,$app_id,$handler);

	}

	/**
	 *
	 * @param $app_id string
	 * @return boolean
	 * @throws MySQLException
	 */
	public static function unregisterAppHandlers($app_id)
	{
		$events_model = new EventsModel();
		return $events_model->unregisterAppHandlers($app_id);
	}

	/**
	 * Dummy system event handler
	 *
	 * @param $event string
	 * @param $app_id string
	 * @param $handler string
	 * @return boolean
	 */
	protected function callEventHandler($event,$app_id,$handler,$params)
	{
		;//
	}

	/**
	 * Exception handler
	 *
	 * @param $exception Exception
	 * @return none
	 * @todo add exception workaround (write to log and etc.)
	 */
	protected static function onException($exception)
	{
		;//print '<div class="error_block">'.$exception->getMessage().'</div>';
	}
}
?>