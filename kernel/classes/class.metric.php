<?php
	class metric {
    private static $instance;
    
    private $file;
    private $path;
    private $name;
    private $active;
    
    private function __construct() {
      $this->active = file_exists(dirname(__FILE__) . "/../../kernel/hosting_plans.php");
      if (!$this->active)
      	return;
      @openlog('WA', LOG_ODELAY, LOG_LOCAL0);
    }
    
    /**
     * Return instance
     * 
     * @return metric
     */
    public static function getInstance() {
      if (!isset(self::$instance)) {
          self::$instance = new self;
      }
      return self::$instance;
    }
    
    private function remoteAddr(){
      if(function_exists("getallheaders")){
        $request_headers = getallheaders();
        if(isset($request_headers['X-Real-IP']))
          return $request_headers['X-Real-IP'];
      }
      return $_SERVER["REMOTE_ADDR"];
    }
    
    function addAction ($dbkey, $u_id, $app_id, $action_type, $client_type = 'ACCOUNT', $data = '') {
        if (!$this->active)
        	return;
        if (!$dbkey || !$action_type)
        	return;
        $time = date('Y-m-d H:i:s', time());
	$act_date = date('Y-m-d', time());
        $act_time = date('H:i:s', time());
               
        $client_ip = $this->remoteAddr();
#        $logstring = join("|", array (strtoupper($dbkey), $u_id, $time, $app_id, $action_type, $data, $client_type, $client_ip));
        $logstring2 = "INSERT INTO ACTIONLOG (`dbkey`, `u_id`, `act_date`, `act_time`, `app_id`, `action_type`, `data`, `client_type`, `client_ip`) VALUES ('" . strtoupper($dbkey) . "', '$u_id', '$act_date', '$act_time', '$app_id', '$action_type', '$data', '$client_type', '$client_ip');";
        $metric_log = fopen("/var/log/metrics/webasyst.sql",'a');
        fwrite($metric_log,"$logstring2\n");
        fclose($metric_log);
#				syslog(LOG_INFO, $logstring);
    }
	}
?>