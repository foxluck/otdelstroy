<?php

	define('SEND_DEBUG', false);

	//	define('GET_DBKEY_FROM_URL', true);
	define('PUBLIC_AUTHORIZE', true);

	include_once '../../../system/init.php';
	include_once 'mailconsts.php';
	//	Wbs::publicAuthorize();

	$wbsPath = Wbs::getSystemObj()->files()->getWbsPath();

	define('TEMP_PATH', '../../../temp');

	$logPath = TEMP_PATH.'/log';
	$pointerPath = TEMP_PATH.'/sendmail';
	if(!is_dir($logPath))
		mkdir($logPath);
	if(!is_dir($pointerPath))
		mkdir($pointerPath);

	$logFile = fopen($logPath.'/send.log', 'a');

	require_once 'mailmime.php';
	require_once 'socketmail.php';
	require_once 'mailparse.php';

	global $log, $logFile, $DB_KEY;

	if(!$DB_KEY = base64_decode(WebQuery::getParam('DB_KEY'))) {
		exitOnError('No DB_KEY');
	}

	if($logFile) { fwrite($logFile, date('Y-m-d H:i:s').' -> get '.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']." ($DB_KEY)\n"); }

	if(Wbs::getDbkeyObj()->getDaysToSuspend() < 0) {
		if($logFile) { fwrite($logFile, "-> Error: account is suspended\n"); }
		exit('suspended');
	}

	$model = new DbModel();
	if($MMM_ID = WebQuery::getParam('MMM_ID')) {

		$msgIds = unserialize(base64_decode($MMM_ID));
		foreach($msgIds as $key=>$id) {
			$msgIds[$key] = $model->escape($id);
		}
		$sql = "SELECT * FROM MMMESSAGE WHERE (MMM_ID='".join("' OR MMM_ID='", $msgIds)."') AND MMM_STATUS='".MM_STATUS_SENDING
			."' ORDER BY MMM_DATETIME ASC";
		$docList = $model->prepare($sql)->query()->fetchAll();
	} else {

		$sql = "SELECT * FROM MMMESSAGE WHERE (MMM_STATUS='".MM_STATUS_PENDING."' OR MMM_STATUS='".MM_STATUS_SENDING
			."') AND MMM_DATETIME<=NOW() ORDER BY MMM_DATETIME ASC";
		$docList = $model->query($sql)->fetchAll();
		if($docList) {

			$sql = "UPDATE MMMESSAGE SET MMM_STATUS = ".MM_STATUS_SENDING." WHERE MMM_STATUS='".MM_STATUS_PENDING."' AND MMM_DATETIME<=NOW()";
			$model->prepare($sql)->exec();
		}
	}

	if(!$xml = @file_get_contents("$wbsPath/kernel/wbs.xml")) {
		exitOnError('XML open error');
	}

	$sxml = new SimpleXMLElement($xml);
	if(sizeof(@(array)$sxml->SMTP_SERVER->attributes()) == 0) {
		$host = $port = $user = $pass = $connect = false;
	} else {
		$host = (string)$sxml->SMTP_SERVER->attributes()->host;
		$port = (string)$sxml->SMTP_SERVER->attributes()->port;
		$user = (string)$sxml->SMTP_SERVER->attributes()->user;
		$pass = (string)$sxml->SMTP_SERVER->attributes()->password;

		global $connect;
		$connect = false;
	}

	if($logFile) { fwrite($logFile, "-> $DB_KEY docList count: ".count($docList)."\n"); }
	
	foreach($docList as $message) {
	
		$last_datetime = $message['MMM_DATETIME'];

		// try to get current pointer and test if another copy of this script is already working
		$pointerPath = TEMP_PATH."/sendmail/$DB_KEY~{$message['MMM_ID']}";
		$pointerTmpPath = TEMP_PATH."/sendmail/$DB_KEY~{$message['MMM_ID']}~tmp";

		@touch($pointerPath);
		@touch($pointerTmpPath);

		$pointer = @file_get_contents($pointerPath);
		sleep(1);
		if($pointer != @file_get_contents($pointerPath)) {
			continue;
		}

		if(!$pointer) {
			$pointer = 0;
		}

		if(preg_match('/^(.*)<(.*)>$/', $message['MMM_FROM'], $match)) {
			$from_name = trim($match[1]);
			$from_email = $match[2];
		} else {
			$from_name = '';
			$from_email = $message['MMM_FROM'];
		}
		$from_encoded = ltrim(EncodeHeader($from_name).' <'.$from_email.'>');

		if(preg_match('/^(.*)<(.*)>$/', $message['MMM_REPLY_TO'], $match)) {
			$reply_name = trim($match[1]);
			$reply_email = $match[2];
		} else {
			$reply_name = '';
			$reply_email = $message['MMM_FROM'];
		}
		$reply_encoded = ltrim(EncodeHeader($reply_name).' <'.$reply_email.'>');

		$message['MMM_CONTENT'] = str_replace("\n.", "\n .", $message['MMM_CONTENT']); // fix some mail servers bug;

		// Try to find variables for extraction and extract sender and company variables)
		if(preg_match('/\{[A-Z_]+\}/i', $message['MMM_SUBJECT'].$message['MMM_CONTENT'])) {
			MailMessage::extractSenderVars($message, $companyVariables);
			$message['doExtract'] = true;
		} else {
			$message['doExtract'] = false;
		}

		$tomore = $bounced = array();
		$addr = parseAddressString($message['MMM_TO'], false, true);
		$tomore['TO'] = $addr['accepted'];
		$bounced = $addr['bounced'];
		$addr = parseAddressString($message['MMM_CC'], false, true);
		$tomore['CC'] = $addr['accepted'];
		if($addr['bounced']) $bounced = array_merge($bounced, $addr['bounced']);
		$addr = parseAddressString($message['MMM_BCC'], false, true);
		$tomore['BCC'] = $addr['accepted'];
		if($addr['bounced']) $bounced = array_merge($bounced, $addr['bounced']);

		$send_to = $send_tomore = array();
		foreach($tomore as $item) {
			for($i=0; $i<count($item); $i++) {
				$addr_key = ltrim($item[$i]['name'].' <'.$item[$i]['email'].'>');
				if(empty($send_tomore[$addr_key])) {
					$send_to[] = $item[$i];
					$send_tomore[$addr_key] = 1;
				}
			}
		}

		$list_resource = false;

		if($lists = $message['MMM_LISTS']) {
			// get lists resource id
			$lists = explode(',', $lists);

			foreach($lists as $key=>$lst) {
				$lst = trim($lst);
// For plugins lists with not numeric id				
//				if(is_numeric($lst)) {
					$lists[$key] = $lst;
//				} else {
//					unset($lists[$key]);
//				}
			}
			if($lists) {
				$contacts_model = new ContactsModel();
				$offset = ($pointer > count($send_to)) ? $pointer - count($send_to) : 0;
				$list_resource = $contacts_model->getWithEmailsByLists($lists, $offset, $message['MMM_USERID']);
			}
		}

		$forceError = '';
		$errorStr = '';

		// start sending current message
		while(($pointer < count($send_to)) || ($list_resource && ($contact_info = $list_resource->fetchAssoc()))) {

			$errorStr = '';

			// check daily send limit for current hosting plan
			$sql = "SELECT MMS_COUNT FROM MMSENT WHERE MMS_DATE = '".date('Y-m-d')."' LIMIT 1";
			if(!$dailySent = $model->prepare($sql)->query()->fetchField('MMS_COUNT')) {
				$dailySent = 0;
			}
			if(MM_DAILY_SEND_LIMIT && ($pointer > (MM_DAILY_SEND_LIMIT - $dailySent))) {
				$forceError = 'Daily send limit is reached';
			}

			$currentMessage = $message;

			$to_out = array();

			if($pointer < count($send_to)) {
			
				$contact_info = array();

				if($message['doExtract']) {
					$contact_info = Contact::getByName($send_to[$pointer]['name'], $send_to[$pointer]['email']);
					if($contact_info) {
						$contact_info['C_FULLNAME'] = Contact::getName($contact_info['C_ID'], false, $contact_info, false);
					}
				}
				if(!$contact_info) {
					$contact_info = array('C_FULLNAME'=>$send_to[$pointer]['name'], 'C_EMAILADDRESS'=>$send_to[$pointer]['email']);
				}

				foreach($tomore as $key=>$item)
				{
					$enc = array();
					for($i=0; $i<count($item); $i++)
						$enc[] = ltrim(EncodeHeader($item[$i]['name']).' <'.$item[$i]['email'].'>');
					$to_out[$key] = join(', ', $enc);
				}

			} else {
				$contact_info['C_FULLNAME'] = Contact::getName($contact_info['C_ID'], false, $contact_info, false);
				if(isset($send_tomore[ltrim($contact_info['C_FULLNAME'].' <'.$contact_info['C_EMAILADDRESS'].'>')])) {
					$pointer++;
					file_put_contents($pointerTmpPath, $pointer);
					copy($pointerTmpPath, $pointerPath);
					continue;
				}
				$to_out['TO'] = ltrim(EncodeHeader($contact_info['C_FULLNAME']).' <'.$contact_info['C_EMAILADDRESS'].'>');
				$to_out['CC'] = '';
			}
			$to_email = $contact_info['C_EMAILADDRESS'];

			$unsubscribed = false;
			if(!preg_match('/[+-]u/i', $message['MMM_APP_ID'])) {
				$sql = 'SELECT * FROM UNSUBSCRIBER WHERE ENS_EMAIL = s:email';
				$unsubscribed = $model->prepare($sql)->query(array('email' => $to_email))->fetchRow();
			}

			if($unsubscribed) {

				$errorStr = $MMMST_STATUS = 'Unsubscribed';

			} else {

				if($message['doExtract']) {
					MailMessage::extractRecipientVars($currentMessage, $contact_info);
				}

				$currentContent = $currentMessage['MMM_CONTENT'];
				$currentSubject = EncodeHeader($currentMessage['MMM_SUBJECT']);
				
				$plainText = html2plain($currentContent);

				if(!preg_match('/<html.*>/i', $currentContent) && !preg_match('/<head.*>/i', $currentContent) &&
					!preg_match('/<body.*>/i', $currentContent))
					$currentContent = "<html>\n<head>\n<title>$currentSubject</title>\n"
						.'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
						."\n</head>\n<body>\n$currentContent\n</body>\n</html>\n";
			}
			
			if(!$forceError && !$errorStr) {

				if($host) {

					$crlf = "\r\n";
					$mime = new Mail_mime($crlf);

					$mime->setTXTBody($plainText.$crlf);
					$mime->setHTMLBody($currentContent.$crlf);

					if($message['MMM_ATTACHMENT']) {

						$xml = base64_decode($message['MMM_ATTACHMENT']);
						try {
							$sxml = new SimpleXMLElement($xml);
							$i=0;
							if(count((array)$sxml->FILE)) {
								while($file = (array)$sxml->FILE[$i]) {
									$fileName = base64_decode($file['@attributes']['FILENAME']);
									$mime->addAttachment(
										"$wbsPath/data/$DB_KEY/attachments/mm/attachments/{$message['MMM_ID']}/$fileName",
										$file['@attributes']['MIME_TYPE'],
										EncodeHeader($fileName)
									);
									$i++;
								}
							}
						}
						catch (Exception $e) {
							if($logFile) fwrite($logFile, "-> error: ".$e->getMessage()."\n");
						}
					}

					$header = array(
						'Reply-To' => $reply_encoded,
						'From'     => $from_encoded,
						'To'       => $to_out['TO'],
						'Cc'       => $to_out['CC'],
						'Date'     => date('r'),
						'Subject'  => $currentSubject,
						'X-Mailer' => 'WebAsyst [http://www.webasyst.net] MailMaster (s)',
						'X-Originating-IP' => '['.$_SERVER['REMOTE_ADDR'].']',
						'X-Priority' => $message['MMM_PRIORITY']
						);
						//'X-MSMail-Priority' => 'Normal' 
						// 'Importance' => 'Normal'
					if(empty($to_out['CC']))
					  unset($header['Cc']);
					$mime->headers($header);

					if(!$connect)
						$errorStr = socketSendOpen($host, $port, $user, $pass);

					if($connect) {
						if(SEND_DEBUG) {
							if($logFile) { fwrite($logFile, "\n-> debug (socket):\n".$mime->getMessage($crlf.$crlf)."\n"); }
							$res = array($to_email);
						} else {
							$res = socketSendMail($connect, $reply_email, array($to_email), $mime->getMessage($crlf.$crlf));
						}
						$method = 'socketMail()';
					}
				}
				if(!$host || !$connect)
				{
					$crlf = "\n";
					$mime = new Mail_mime($crlf);

					$mime->setTXTBody($plainText.$crlf);
					$mime->setHTMLBody($currentContent.$crlf);

					if($message['MMM_ATTACHMENT'])
					{
						$xml = base64_decode($message['MMM_ATTACHMENT']);
						try { $sxml = new SimpleXMLElement($xml); }
						catch (Exception $e) {}

						$i=0;
						if(count((array)$sxml->FILE))
							while($file = (array)$sxml->FILE[$i])
							{
								$fileName = base64_decode($file['@attributes']['FILENAME']);
								$mime->addAttachment(
									"$wbsPath/data/$DB_KEY/attachments/mm/attachments/{$message['MMM_ID']}/$fileName",
									$file['@attributes']['MIME_TYPE'],
									EncodeHeader($fileName)
								);
								$i++;
							}
					}

					$header = array(
						'Reply-To' => $reply_encoded,
						'From'     => $from_encoded,
						'Date'     => date('r'),
						'X-Mailer' => 'WebAsyst [http://www.webasyst.net] MailMaster',
						'X-Originating-IP' => '['.$_SERVER['REMOTE_ADDR'].']',
						'X-Priority' => $message['MMM_PRIORITY'],
						'Content-Transfer-Encoding' => '8bit'
						);
					$header = $mime->headers($header);

					$body = str_replace("\r", '', $mime->get());
					$txt_hdr = $mime->txtHeaders();
					$params = "-f $reply_email";
					
					$to_name = preg_replace('/[,;]/', '', $contact_info['C_FULLNAME']);

					$to_once = ltrim(EncodeHeader($to_name).' <'.$to_email.'>');

					if(SEND_DEBUG) {
						if($logFile) { fwrite($logFile, "\n-> debug (mail):\n$body\n"); }
						$res = array($to_email);
					} else {
						if (mail($to_once, $currentSubject, $body, $txt_hdr, $params) || mail($to_once, $currentSubject, $body, $txt_hdr)) {
							$res = array($to_email);
						} else {
							$res = $errorStr ? $errorStr : 'PHP mail() error';
						}
					}

					$method = 'PHP mail()';
				}

			} else {
				$res = $forceError ? $forceError : $errorStr;
			}

			if(is_array($res)) {

				$MMMST_STATUS = 0;
				if($logFile) { fwrite($logFile, '=> '.date('Y-m-d H:i:s').
					" $method DB_KEY=$DB_KEY MMM_ID=".$message['MMM_ID']." sent to <$to_email>\n"); }

			} else {

				$method = isset($method) ? $method : '';
				socketSendClose();
				$MMMST_STATUS = $errorStr = $res;
				if($logFile) { fwrite($logFile, '-> '.date('Y-m-d H:i:s')
					." $method DB_KEY=$DB_KEY MMM_ID=".$message['MMM_ID']." not sent to <$to_email>: $res\n"); }
			}

			if($connect && !(($pointer + 1) % 10)) {
				socketSendClose(); // SMTP server limit
			}


			// write message sent result to DB
			if(strpos($message['MMM_APP_ID'], '-') === false) {
				$sql = 'REPLACE INTO MMMSENTTO SET MMM_ID=i:id, MMMST_EMAIL=s:email, MMMST_STATUS=s:status';
				$model->prepare($sql)->exec(array('id'=>$message['MMM_ID'], 'email'=>$to_email, 'status'=>$MMMST_STATUS));
			}

			if($forceError) {
				break;
			}

			$pointer++;

			file_put_contents($pointerTmpPath, $pointer);
			copy($pointerTmpPath, $pointerPath);

		} // current copy of message is sent ***********************************

		if($pointer) {
			$MMM_STATUS = MM_STATUS_SENT;
		} else {
			$MMM_STATUS = MM_STATUS_ERROR;
		}

		$sql = "UPDATE MMMESSAGE SET MMM_STATUS=i:status WHERE MMM_ID=i:id";
		$model->prepare($sql)->exec(array('status'=>$MMM_STATUS, 'id'=>$message['MMM_ID']));

		// write count of sent messages to DB
		$sql = "REPLACE INTO MMSENT SET MMS_DATE='".date('Y-m-d')."', MMS_COUNT=i:count";
		$model->prepare($sql)->exec(array('count'=>$pointer));

		if(!$pointer)
		{

			// write fatal error message to sent result DB

			$MMM_ID = $message['MMM_ID'];

			if($bounced) {
				$err = 'Incorrect email address';
				for($i=0; $i<count($bounced); $i++) {
					$bounced[$i] = $model->escape($bounced[$i]);
					if($logFile) { fwrite($logFile, '-> '.date('Y-m-d H:i:s')
						." DB_KEY=$DB_KEY MMM_ID=".$message['MMM_ID']." $err: <{$bounced[$i]}>\n"); }
				}
				$sql = "REPLACE INTO MMMSENTTO (MMM_ID, MMMST_EMAIL, MMMST_STATUS) VALUES "
					."('$MMM_ID', '".join("', '$err'), ('$MMM_ID', '", $bounced)."', '$err')";
			}
			else {
				$err = $errorStr ? $errorStr : 'Fatal error';
				$sql = "REPLACE INTO MMMSENTTO (MMM_ID, MMMST_EMAIL, MMMST_STATUS) VALUES "
					."('$MMM_ID', 'error', '$err')";

				if($logFile) { fwrite($logFile, '-> '.date('Y-m-d H:i:s')
					." $err DB_KEY=$DB_KEY MMM_ID=".$message['MMM_ID']."\n"); }
			}
			$model->prepare($sql)->exec();
		}

		unlink($pointerPath);
		unlink($pointerTmpPath);

		if(strpos($message['MMM_APP_ID'], '-') !== false) {
			$sql = "DELETE FROM MMMESSAGE WHERE MMM_ID='".$message['MMM_ID']."' LIMIT 1";
			$model->query($sql);

			if($message['MMM_ATTACHMENT']) {
				$path = Wbs::getSystemObj()->files()->getDataPath().DIRECTORY_SEPARATOR.$DB_KEY.DIRECTORY_SEPARATOR.'attachments'
					.DIRECTORY_SEPARATOR.'mm'.DIRECTORY_SEPARATOR.'attachments'.DIRECTORY_SEPARATOR.$message['MMM_ID'];
				unlinkRecursive($path);
			}
		}

	} // one message from docList is sent **************************************

	socketSendClose();

	if(Wbs::getSystemObj()->getCommonLogBase()) {
//			$sql = "DELETE FROM SCHEDULE_TASK WHERE SCH_DBKEY=s:dbkey AND SCH_DATETIME<=s:datetime'";
//			$model->prepare($sql)->exec(array('dbkey'=>$DB_KEY, 'datetime'=>$message['MMM_DATETIME']);
		$sql = new CDeleteSqlQuery('SCHEDULE_TASK');
		$sql->addConditions("SCH_DBKEY='$DB_KEY'");
		$sql->addConditions("SCH_DATETIME<='$last_datetime'");
		Wbs::getSystemObj()->CommonLogBase->runQuery($sql);
	}

	echo 'Total messages: '.count($docList);


	// Encode a header string to B (base64) or none.
	function EncodeHeader($str)
	{
		if(preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches)) {
			return '=?utf-8?B?'.base64_encode($str).'?=';
		} else {
			return $str;
		}
	}

	function exitOnError($errorStr)
	{
		global $logFile, $DB_KEY;
		if($logFile) { fwrite($logFile, date('Y-m-d H:i:s')." DB_KEY=$DB_KEY $errorStr\n"); }
		exit($errorStr);
	}

	/**
	 * Recursively delete a directory
	 *
	 * @param string $dir Directory name
	 */
	function unlinkRecursive($dir)
	{
		if(!$dh = @opendir($dir.'/')) {
			return;
		}
		while(false !== ($obj = readdir($dh))) {

			if($obj == '.' || $obj == '..') {
				continue;
			}

			if (!@unlink($dir . '/' . $obj)) {
				unlinkRecursive($dir.'/'.$obj);
			}
		}
		closedir($dh);

		@rmdir($dir);

		return;
	}

?>
