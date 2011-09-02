<?php

/**
 * @example 
 * 	$m = Mailer::composeMessage();
 * 	$m->addTo('Mail To string here');
 *	$m->addSubject('Subject string here');
 *	$m->addContent('Content string here');
 *  try{ Mailer::send($m) } catch (Exception $e) {}
 */
class Mailer
{
    /**
     * @return MailMessage
     */
	public static function composeMessage()
	{	
		return new MailMessage();
	}

	public static function composeNotification()
	{
		return new NotificationMessage();
	}

	public function getMessage($mid, $parse=true)
	{
		$message = array();
		$db_model = new DbModel();
		$sql = "SELECT * FROM MMMESSAGE WHERE MMM_ID=i:mid AND MMM_USERID=s:uid";
		if(($message = $db_model->prepare($sql)->query(array('mid'=>$mid, 'uid'=>User::getId()))->fetch()) && $parse) {

			$attachments = array();
			$size = 0;
			if($message['MMM_ATTACHMENT']) {
				$sxml = new SimpleXMLElement(base64_decode($message['MMM_ATTACHMENT']));
				$i = 0;
				foreach($sxml->FILE as $file) {
					$fn = (string)$file->attributes()->FILENAME;
					$fs = (string)$file->attributes()->FILESIZE;
					$ft = (string)$file->attributes()->MIME_TYPE;
					$attachments[$i]['name'] = base64_decode($fn);
					$attachments[$i]['size'] = $fs;
					$attachments[$i]['type'] = $ft;
					$i++;
					$size += $fs;
				}
			}
			$message['attachments'] = $attachments;
			$message['att_size'] = $size;

			$images = array();
			$size = 0;
			if($message['MMM_IMAGES']) {
				$sxml = new SimpleXMLElement(base64_decode($message['MMM_IMAGES']));
				$i = 0;
				foreach($sxml->FILE as $file) {
					$fn = (string)$file->attributes()->FILENAME;
					$fs = (string)$file->attributes()->FILESIZE;
					$ft = (string)$file->attributes()->MIME_TYPE;
					$images[$i]['name'] = base64_decode($fn);
					$images[$i]['size'] = $fs;
					$images[$i]['type'] = $ft;
					$i++;
					$size += $fs;
				}
			}
			$message['images'] = $images;
			$message['img_size'] = $size;
		}
		return $message;
	}

	/**
	 * Creates a task for sending the message
	 * 
	 * @param MailMessage $message
	 * @param string $doSend
	 * @param bool $update
	 *
	 * @return int
	 */
	public static function send($message, $doSend='now', $update=false)
	{
		$hash = $message->getData($doSend);

		// Insert $messageDB into database and send it

		$db_model = new DbModel();
		$values = array();
		foreach($hash as $key => $val)
			$values[] = $key.' = s:'.$key;
		$sql = ($update ? 'UPDATE' : 'INSERT') . ' MMMESSAGE SET '
			.implode(', ', $values).
			($update ? ' WHERE MMM_ID = i:MMM_ID' : '');

		$db_model->prepare($sql)->exec($hash);

		$fileSize = 0;
		if($message->attachments)
		{
			$path = Wbs::getSystemObj()->files()->getDataPath()
				.DIRECTORY_SEPARATOR.$message->DB_KEY.DIRECTORY_SEPARATOR.'attachments'.DIRECTORY_SEPARATOR.'mm';

			foreach($message->attachments as $file) {
				if($file['type'] == 'images' && !strpos($hash['MMM_CONTENT'], base64_encode($file['name'])) && $file['path']) {
				
					@unlink($file['path']);
					continue;
				}
				if(!$sz = self::saveAttachment($file, $path, $message->MMM_ID)) {
					throw new Exception("Can't save attachment(s)");
				} else {
					$fileSize += $sz;
				}
			}
			if(strpos($hash['MMM_APP_ID'], '-') === false) {
				$dqm = new DiskQuotaManager();
				$dqm->addDiskUsageRecord('$SYSTEM', 'MM', $fileSize);
			}
		}

		if(!$doSend) {
			return $hash['MMM_ID'];
		} else {
			self::setSheduleTask($hash['MMM_DATETIME'], $message->DB_KEY);
			if($doSend == 'later')
				return $hash['MMM_ID'];
		}

		//
		//	Send Now ...
		//
		$parsed_url = parse_url(Url::get('/common/scripts/sendmail.php', true));
		$get = $parsed_url['path'].'?DB_KEY='.
			base64_encode($message->DB_KEY).'&MMM_ID='.
			base64_encode(serialize(array($message->MMM_ID)));
			
		$host = $parsed_url['host'];
		if(Wbs::isHosted()) {
			$host = preg_replace('/^([^\.]*)/i', 'webasyst', $_SERVER['HTTP_HOST']);
		}
		if($parsed_url['scheme'] == 'https') {
			$port = 443;
			$prefix = 'ssl://';
		} else {
			$port = 80;
			$prefix = '';
		}
		
		$fp = fsockopen($prefix.$host, $port, $errno, $error, 10);

		if(!$fp) {
			throw new RuntimeException("Connect error: $error");
		}

		$query = "GET $get HTTP/1.1\r\nHost: $host\r\nConnection: close\r\n\r\n";
		fputs($fp, $query);
		sleep(1);
		fclose($fp);

		return $hash['MMM_ID'];
	}

	public static function update($data)
	{
		$values = array();
		foreach($data as $key => $val) {
			if($key != 'MMM_ID') {
				$values[] = $key.' = s:'.$key;
			}
		}
		$sql = 'UPDATE MMMESSAGE SET '.implode(', ', $values).' WHERE MMM_ID = i:MMM_ID';

		$db_model = new DbModel();
		$db_model->prepare($sql)->exec($data);
	}

	private static function saveAttachment($file, $path, $MMM_ID)
	{
		$pathLevels = array($path, $file['type'], $MMM_ID);
		$path = '';
		foreach($pathLevels as $level) {

			$path .= $level.DIRECTORY_SEPARATOR;
			if(!is_dir($path))
				if(!@mkdir($path))
					return false;
		}
		$dest = $path.$file['name'];

		if($file['path']) {

			$source = urldecode($file['path']);
			if(is_file($source)) {
				@copy($source, $dest);
			}
		} else {
			if(!@file_put_contents($dest, getUploadedFileBody($file['name'], $file['type']))) { // TODO: send throw draft ---
				return false;
			}
		}
		return @filesize($dest);
	}

	private static function setSheduleTask($sqlDateTime, $DB_KEY)
	{
		if(!Wbs::getSystemObj()->getUrl())
		{
			$URL = sprintf('%s%s', Wbs::isHosted() ? end(explode('.', $_SERVER['HTTP_HOST'], 2)) : $_SERVER['HTTP_HOST'], Url::get(''));
			Wbs::getSystemObj()->setUrl($URL);
		}

		if(Wbs::getSystemObj()->getCommonLogBase())
		{
			$sql = new CInsertSqlQuery('SCHEDULE_TASK');
			$hash = array(
				'SCH_DBKEY' => $DB_KEY,
				'SCH_APP' => 'MM',
				'SCH_TASKNAME' => 'Subscribe',
				'SCH_DATETIME' => $sqlDateTime
			);
			$sql->addFields($hash, array_keys($hash));

			Wbs::getSystemObj()->CommonLogBase->runQuery($sql);
		}
	}
/*	
	public static function deleteDraft($mid)
	{
		$db_model = new DbModel();
		$sql = "DELETE FROM MMMESSAGE WHERE MMM_ID=i:mid AND MMM_USERID=s:uid AND MMM_APP_ID=s:app_id AND MMM_STATUS=0 LIMIT 1";
		$db_model->prepare($sql)->exec(array('mid'=>$mid, 'uid'=>User::getId(), 'app_id'=>User::getAppId()));
	}
*/

}

?>