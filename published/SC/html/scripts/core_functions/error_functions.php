<?php

/**
 * @param mixed $message
 * @param int $msg_type: MSG_SUCCESS|MSG_ERROR
 */
function throwMessage($message, $msg_type = MSG_ERROR){

	if(PEAR::isError($message)){
		/*@var $message PEAR_Error*/
		$message = $message->getMessage();
	}
	Message::raiseMessageRedirectSQ($msg_type, '', $message);
}


function error_handler($errno, $errstr, $errfile, $errline, $vars = null){
	log_error($errno, $errstr, $errfile, $errline);
}

/**
 * Error handler. Saves error information in the form of HTML-string within the file
 *
 * @param int $errno - error number
 * @param string $errstr - error text
 * @param string $errfile - file for storing
 * @param int $errline - number of line, where error occured
 */
function log_error( $errno, $errstr, $errfile, $errline )
{
	global $silentMode;

	if ( $silentMode ){
		//	return;
	}

	if (( defined( "WBS_DEBUGMODE" ) && WBS_DEBUGMODE != 0 ) || ( $errno != 2048 && $errno != E_NOTICE ) ){
		$errorLogName = getErrorLogName();
		$errorMessage = sprintf( "%s. %s File: %s Line: %s Error #: %s\r\n",
		date("Y-m-d H:i"),
		$errstr,
		$errfile,
		$errline,
		$errno);
		@error_log ($errorMessage, 3, $errorLogName, "\n");
	}
}
/**
 * Function for handling PEAR errors. Saves error message content within the file
 *
 * @param PEAR_Error $error
 */
function handlePEARError( $error )
{
	global $silentMode;
	if ( ( defined( "WBS_DEBUGMODE" ) && WBS_DEBUGMODE == 0 ) || ($error->getCode() >= ERRCODE_APPLICATION_ERR) || $silentMode ){
		return;
	}
	$errorLogName = getErrorLogName();
	if($fp = @fopen($errorLogName , "a" )){
		$file = $line = $function = "";
		$btLen = count($error->backtrace);
		if ( $btLen ) {
			$file = $error->backtrace[$btLen]["file"];
			$line = $error->backtrace[$btLen]["line"];
			$function = $error->backtrace[$btLen]["function"];
		}

		$errorMessage = sprintf( "%s. File: %s, Line %s, function %s. %s\r\n",
		date( "Y-m-d H:i" ),
		basename($file), $line, $function, $error->message );

		@fwrite( $fp, $errorMessage );
		@fclose( $fp );
	}
}
function getErrorLogName()
{
	if(defined('ERR_SCLOG_FILE')&&file_exists(dirname(constant('ERR_SCLOG_FILE')))){
		return constant('ERR_SCLOG_FILE');
	}else{
		return sprintf('%s/kernel/sc-error.log',WBS_DIR);
	}
}
?>