<?php
/**
 *
 * @author WebAsyst Team
 *
 */
class EventsModel extends DbModel
{
	/**
	 *
	 * @param $event string
	 * @return array array(APP_ID1=>array(handler1_1,handler1_2,...),APP_ID2=>arrayarray(handler2_1,handler2_2,...),...)
	 * @throws MySQLException
	 */
	public function getByEvent($event)
	{
		$event_handlers = array();
		$sql = 'SELECT `APP_ID`,`HANDLER` FROM `EVENTS` WHERE `EVENT` = s:event';
		$dbresult = $this->prepare($sql)->query(array('event' => $event));
		/*@var $dbresult DbResultSelect*/
		while($row = $dbresult->fetchAssoc()){
			$app_id = $row['APP_ID'];
			if(!is_array($event_handlers[$app_id])){
				$event_handlers[$app_id] = array();
			}
			$event_handlers[$app_id][] =  $row['HANDLER'];
		}
		return $event_handlers;
	}

	/**
	 *
	 * @param $event
	 * @param $app_id
	 * @param $handler
	 * @return unknown_type
	 * @throws MySQLException
	 */
	public function registerEventHandler($event,$app_id,$handler)
	{
		$sql = "INSERT IGNORE `EVENTS`
				SET `EVENT` = s:event, `APP_ID` = s:app_id, `HANDLER`=s:handler";
		return $this->prepare($sql)->exec(array(
			 	'event' => $event,
			 	'handler' => $handler,
			 	'app_id' => $app_id
		));

	}

	/**
	 *
	 * @param $event string
	 * @param $app_id string
	 * @param $handler string
	 * @return boolean
	 * @throws MySQLException
	 */
	public function unregisterEventHandler($event,$app_id,$handler)
	{
		$sql = "DELETE FROM `EVENTS`
				WHERE `EVENT` = s:event AND `APP_ID` = s:app_id AND `HANDLER`=s:handler";
		return $this->prepare($sql)->exec(array(
			 	'event' => $event,
			 	'handler' => $handler,
			 	'app_id' => $app_id
		));
	}

	/**
	 *
	 * @param $app_id string
	 * @return boolean
	 * @throws MySQLException
	 */
	public function unregisterAppHandlers($app_id)
	{
		$sql = "DELETE FROM `EVENTS` WHERE `APP_ID` = s:app_id";
		return $this->prepare($sql)->exec(array('app_id' => $app_id	));
	}
}
?>