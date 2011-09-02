<?php
	class metricloader {
    private static $instance;
    private $filename;
    private $handle;
    private $chunk;
    private $dblink;
    private $dbconf;
    
    private function __construct() {
	$this->filename = dirname(__FILE__) . "/../../temp/log/webasyst.log";
    	$this->handle = fopen($this->filename, 'r');
      //file reader settings
      $this->dbconf = 'COMMONLOGBASE';
      $dsn = $this->getDBsettings();
     $this->dblink = DB::connect($dsn, false);
      $error = $this->dblink->backtrace[0]['object']->message;
      if ( preg_match("#.*DB Error.*#", $error) ) {
          $this->errorState = true;
          print $this->dblink->message;
          self::__destruct();
      }
    }
    
    public static function getInstance() {
      if (!isset(self::$instance)) {
          self::$instance = new self;
          if (self::$instance->errorState === TRUE)
              return null;
      }
      return self::$instance;
    }
    
    private function getDBsettings() {
      $config = WBS_DIR . 'kernel/wbs.xml';
      if (file_exists($config))
          $sxml = simplexml_load_file($config);
      else return 'ERROR open config';
      
      $CONF = $this->dbconf;
			$dsn = array(
				'phptype' => 'mysql',
				'username' => (string)$sxml->$CONF->ADMIN_USERNAME,
				'password' => (string)$sxml->$CONF->ADMIN_PASSWORD,
				'hostspec' => (string)$sxml->$CONF->HOST,
				'port'     => (string)$sxml->$CONF->PORT,
				'database' => (string)$sxml->$CONF->METRICSDB,
			);
      
      return $dsn;
    }
    
 
    private function performQueries($lines) {
    $firstSql = 'INSERT INTO `ACTIONLOG` '.
    '(`dbkey`, `u_id`, `act_date`, `act_time`, `app_id`, `action_type`, `data`, `client_type`, `client_ip`) '.
    'VALUES ';
    $SQL = $firstSql;
    $offsetStr = "WA: ";
    $lineoffset = trim(strpos($lines[0], $offsetStr) + strlen($offsetStr));
    for ($i=0;$i<sizeof($lines); $i++)
        {
        list($dbkey, $u_id, $fulldate, $app_id, $action_type, $data, $client_type, $client_ip)
        = split ("\|", substr($lines[$i],$lineoffset));
        list($linedate,$linetime) = split(" ",$fulldate);
        if ($dbkey && strlen($dbkey . $u_id . $linedate . $linetime . $app_id . $action_type . $data . $client_type . $client_ip) > 0)
                $SQL.= sprintf (
                "('%s', '%s' ,'%s', '%s', '%s', '%s', '%s','%s', inet_aton('%s')), ",
                $dbkey, $u_id, $linedate, $linetime, $app_id, $action_type, $data, $client_type, $client_ip
                        );
        }
    if (strlen($SQL) > strlen($firstSql))
    	{	
 	  		return substr($SQL, 0, -2) ;
    	}
    }
    
    public function getActions() {
      $resource = db_query ( 'SELECT * FROM `ACTIONLOG` ORDER BY id', null, $this->dblink );
      
      while ($out = db_fetch_array($resource) )
          $ret[] = $out;
      return $ret;
    }
    
    
    public function readActions() 
    {
        global $count_line;
	$cnt_line_original = count(file($this->filename));
	$count_line=0;

	if(filesize($this->filename)==0)
	{
	    echo $this->filename;
	    echo filesize($this->filename);
	    echo "zero file size, nothing to do"; 
	    die;
	}
    $prev_line="";
    $count_line=0;
    while ($this->handle)
    {
    if( feof($this->handle))
        break;
    $line= trim(fgets($this->handle));

    if(preg_match("/last message repeated/",$line))
        {
    	    $temp_array=preg_split('/\s+/',$line);
            $repeat_count=$temp_array[7];
    	    if ($prev_line == "")
    	    {
    	        for($i=1;$i<=$repeat_count;$i++)
        	{
    		    $query_maxid = "select max(id) from ACTIONLOG";
		    $result_maxid = mysql_query($query_maxid);
		    $row_maxid = mysql_fetch_row($result_maxid);
		    $query_ins = "insert into ACTIONLOG (dbkey,u_id,act_date,act_time,app_id,action_type,data,client_type,client_ip) 
		    select dbkey,u_id,act_date,act_time,app_id,action_type,data,client_type,client_ip 
		    from ACTIONLOG where id=$row_maxid[0]";
		    mysql_query($query_ins);
		}
		$count_line++;
		continue;
    	    }
    		
        for($i=1;$i<=$repeat_count;$i++)
            {
                $for_insert[]="$prev_line";
                if(count($for_insert)==10)
                    {	
                        db_query( $this->performQueries($for_insert), null, $this->dblink );
                        $for_insert=array();
                    }
                $count_line++;
            }
    	    continue;
        }
	if( preg_match("/\sWA\:\s/",$line) == "0" )
            {
        	$errorlines = dirname(__FILE__) . "/../../temp/log/errorlines.log";
                $err_handle = fopen($errorlines,'a');
                fwrite($err_handle,"$line\n");
                fclose($err_handle);
    		continue;
	    }
	    
        $offsetStr = "WA: ";
        $lineoffset = trim(strpos($line, $offsetStr) + strlen($offsetStr));
        list($dbkey, $u_id, $fulldate, $app_id, $action_type, $data, $client_type, $client_ip)
            = split ("\|", substr($line,$lineoffset));
        if ( $dbkey == "" or preg_match("/web[1-2]/",$dbkey) == "1" or $fulldate == "" or $app_id == "" or $action_type == "" or $client_type == "" or $client_type == "")
    	    {
    		$errorlines = dirname(__FILE__) . "/../../temp/log/errorlines.log";
    		$err_handle = fopen($errorlines,'a');
    		fwrite($err_handle,"$line\n");
		fclose($err_handle);
    		continue;
    	    }

    $for_insert[]="$line";
    if(count($for_insert)==10)
        {
            db_query( $this->performQueries($for_insert), null, $this->dblink );
            $for_insert=array();
        }
    $count_line++;
    $prev_line=$line;
    }
    db_query( $this->performQueries($for_insert), null, $this->dblink );

    if($cnt_line_original > $count_line)
        {
            echo "string count ($count_line)is lesser then original count ($cnt_line_original)<br>";
            echo "stop parsing<br>";
            $ddd=time();
            exec("/bin/cp -b $this->filename $this->filename.parse_error.$ddd");
        }
    file_put_contents($this->filename,'');
    return($count_line);
    }

    function Aggregate($count_line) 
    {	
		
		$query = "select max(id) from ACTIONLOG";
		$result = mysql_query($query);
		while ($row = mysql_fetch_row($result))
		{
    		$end_row=$row[0];
    		$start_row=($end_row-$count_line)-100;
		}
		$query_act_dates = "select act_date from ACTIONLOG where id between $start_row and $end_row group by act_date";
		$result_act_dates = mysql_query($query_act_dates);
		
		while($act_date_row = mysql_fetch_array($result_act_dates))
		{
		    $aggr_date=$act_date_row[0];
    		    $query_new_row  = "select count(id),dbkey,act_date,app_id,action_type,client_type,sum(data) from ACTIONLOG
        				   where act_date='$aggr_date' GROUP BY dbkey,act_date,app_id,action_type,client_type";
		    $result_new_row = mysql_query($query_new_row);
		while ($new_row = mysql_fetch_array($result_new_row))
		{
    		$count = $new_row[0];
    		$dbkey=$new_row[1];
    		$act_date=$new_row[2];
    		$app_id=$new_row[3];
    		$action_type=$new_row[4];
    		$client_type=$new_row[5];
    		$sum_data=$new_row[6];    		
    		$query_checkexist = "select * from ACTIONLOG_AGGREGATE where (
            				    dbkey='$dbkey' 
            				and act_date='$act_date' 
                			and app_id='$app_id' 
                			and action_type='$action_type' 
                			and client_type='$client_type')";
    		$result_checkexist = mysql_query($query_checkexist);
    		if(mysql_num_rows($result_checkexist) > 0)
    		{
    			while ($update_row = mysql_fetch_array($result_checkexist))
        		{
        			$id_aggregate=$update_row[0];
            		$query_update = "UPDATE ACTIONLOG_AGGREGATE SET act_count=$count,act_sum=$sum_data WHERE id=$id_aggregate";
			        mysql_query($query_update);
        		}
    		}
    		else
    		{
        		$query_insertnew = "INSERT INTO ACTIONLOG_AGGREGATE 
                					(`dbkey`,`act_date`,`app_id`,`action_type`,`client_type`,`act_count`,`act_sum`)"."
                					VALUES ('$dbkey','$act_date','$app_id','$action_type','$client_type','$count','$sum_data')";
        		mysql_query($query_insertnew);
    		}

		}
		}
    }

    function __destruct() {
        fclose($this->handle);
    }
	}
?>