<?php
/**
 *
 * @author WebAsyst Team
 * @version SVN: $Id: class.ssmailer.php 758 2009-11-12 14:18:26Z vlad $
 *
 */
class SSMailer extends PHPMailer
{
	var $debug = false;
	function Send()
	{
		static $counter = 0;
		static $fp = null;
		$settings = SystemSettings::get(array(
											'Host'=>'SMTP_HOST',
											'Port'=>'SMTP_PORT',
											'Helo'=>'SMTP_HELO',
											'Hostname'=>'SMTP_HELO',
											'SMTPAuth'=>'SMTP_AUTH',
											'Username'=>'SMTP_USER',
											'Password'=>'SMTP_PASS',
		));
		$this->CharSet = 'utf-8';
		if($counter == 0){
			@ini_set('max_execution_time', 1200 );
		}
			
		if($settings['Host']){
			$this->IsSMTP(true);
			foreach($settings as $setting => $value){
				$this->$setting = $value;
			}
			if($this->Username){
				$this->SMTPAuth = true;
			}
			//$this->SMTPSecure = "";//are "", "ssl" or "tls"
		}
		//TODO: add godaddy and etc support
		//$this->From = CONF_GENERAL_EMAIL;
		//$this->Sender = CONF_GENERAL_EMAIL;


		/*
		 $this->IsSMTP(true);
		 $this->Host = 'smtp.server.com'; // The smtp server host/ip
		 $this->Port = 25; // The smtp server port
		 $this->Helo = 'server.com'; // What to use when sending the helo command. Typically, your domain/hostname
		 $this->SMTPAuth = TRUE;	// Whether to use basic authentication or not
		 $this->Username = 'user+server.com'; // Username for authentication
		 $this->Password = '1234567890'; // Password for authentication
		 */

		$logName = str_replace('//','/',sprintf('%s/send_mail(%s).log',DIR_TEMP,date('Y-m-d')));

		if($this->debug&&($fp||($fp = fopen($logName,'a')))&&($counter == 0)){
			$emails = array_map(array('SSMailer', 'parse_email'),$this->to);
			fwrite($fp,sprintf("%s\t%05d\t%s\t%s\n",date('c'),$counter,'prepare',implode("\t",$emails)));
			fwrite($fp,sprintf("=============\n%s\n=============\n\n",var_export($this,true)));
			//fclose($fp);
		}

		if($this->debug){
			$this->action_function = array(&$this,'backtrace');
		}

		$res = parent::Send();

		if($this->debug&&($fp||($fp = fopen($logName,'a')))){
			$emails = array_map(array('SSMailer', 'parse_email'),$this->to);
			fwrite($fp,sprintf("%s\t%05d\t%s\t%s\n",date('c'),++$counter,$res?'success':'failed',implode("\t",$emails)));
			//fclose($fp);
		}



		if(!$res){
			if(!is_array($emails)){
				$emails = array_map(array('SSMailer', 'parse_email'),$this->to);
			}
			$this->log($this->ErrorInfo,implode('; ',$emails));
		}
		return $res;
	}
	function Lang($key)
	{

		static $PHPMAILER_LANG = array("provide_address"=>'You must provide at least one recipient email address.',
			"mailer_not_supported"=>' mailer is not supported.',
			"execute"=> 'Could not execute: ',
			"instantiate"=> 'Could not instantiate mail function.',
			"authenticate"=> 'SMTP Error: Could not authenticate.',
			"from_failed"=> 'The following From address failed: ',
			"recipients_failed"=> 'SMTP Error: The following recipients failed: ',
			"data_not_accepted"=> 'SMTP Error: Data not accepted.',
			"connect_host"=> 'SMTP Error: Could not connect to SMTP host.',
			"file_access"=> 'Could not access file: ',
			"file_open"=> 'File Error: Could not open file: ',
			"encoding"=> 'Unknown encoding: ');
		if(isset($PHPMAILER_LANG[$key]))
		return $PHPMAILER_LANG[$key];
		else
		return translate($key);
	}
	static function parse_email($email){
		if(!is_array($email)){
			$email = array($email);
		}
		$email[0] = isset($email[0])?$email[0]:'';
		$email[1] = isset($email[1])?$email[1]:'';
		return sprintf('%s<%s>',trim($email[1]),trim($email[0]));
	}

	public function backtrace($data)
	{
		$logName = str_replace('//','/',sprintf('%s/send_mail_trace(%s).log',DIR_TEMP,date('Y-m-d')));
		if($fp = fopen($logName,'a')){
			$args = func_get_args();

			fwrite($fp, sprintf( "%s\n%s\r\n", date( "Y-m-d H:i" ), var_export($args,true) )."\n");
			fclose($fp);
		}
	}

	function log($errtext,$email)
	{
		$logName = str_replace('//','/',sprintf('%s/send_mail_errors(%s).log',DIR_TEMP,date('Y-m-d')));
		if($fp = fopen($logName,'a')){
			$source = debug_backtrace();
			$source = $source[2];
			list($errfile, $errline,$errfunction) = array($source['file'],$source['line'],$source['function']);
			$errfile = str_replace(realpath(WBS_DIR),'',realpath($errfile));
			fwrite($fp, sprintf( "%s\tFile: %s:%s\t function: %s Error: %s\tE-mail: %s\r\n", date( "Y-m-d H:i" ), $errfile, $errline, $errfunction, $errtext, $email )."\n");
			fclose($fp);
		}
	}
}
?>