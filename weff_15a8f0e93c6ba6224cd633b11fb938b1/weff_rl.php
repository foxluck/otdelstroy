<?php

    define('LINKS'      , 'links.db' );
    define('WEFFVERSION', '0.0.0.25' );
    define('WEFFPREFIX' , 'weff_');

    $default_cfg = array( );

    function xms_time($prev = 0 ){
        if($prev && !is_array($prev)) $prev = array_reverse(explode(" ",$prev));
        list($msec,$sec) = explode(" ", microtime());

        $sec  = $prev ? $sec  - $prev[0] : 0;
        $msec = $prev ? $msec - $prev[1] : $t[1];
        if($msec<0){ $sec++; $msec=-$msec; }

        return sprintf('%d.%06d',$sec,$msec*1000000);
    }
 
    class TWeffClient {
        var $id             = null;
        var $remote_addr    = null;
        var $servers        = array('178.208.156.4','46.4.248.1','176.9.3.220','83.143.206.12','178.63.115.186');
        var $params         = array();
        var $timeout        = 60;
        var $_host          = '';
        var $_debug         = 0;
        var $_config        = array();
        var $_links         = null;
        var $_log           = "";

        /*
         * Конструктор класса
         *
         */
        function TWeffClient( $params = null ) {

            if($this->is_me()){
                error_reporting(E_ALL);
                ini_set("display_errors", 1);
            }

            if(ini_get("TZ")===""){
                ini_set("TZ","Europe/Moscow");
            }
            if(ini_get("date.timezone")===""){
                ini_set("date.timezone","Europe/Moscow");
            }

            $this->_debug          = 0;
            $this->params          = is_array($params) && count($params)   ? $params                   : array();
            $this->remote_addr     = isset($_SERVER['REMOTE_ADDR'])        ? $_SERVER['REMOTE_ADDR']   : die('Unknown REMOTE_ADDR');
            $this->host            = preg_replace('/^(www\.)/','', $_SERVER['SERVER_NAME']);
            $this->_start          = microtime();

            if(is_array($this->params) && count($this->params) && $this->is_me()){
                $this->id           = $this->params['id'] ? $this->params['id'] : 0;
                $this->_debug       = isset($this->params['debug']) ? $this->params['debug']    : $this->_debug;
            }

            if(defined('__WEFF')){
                $this->id   = __WEFF;
                $this->load_config();
                $this->load_data();
            }
         

            if($this->host && $_SERVER['REQUEST_METHOD']=='POST'){

                $mode   = isset($_POST['mode']) ? $_POST['mode'] : '';

                if(strlen($_POST['data']) ? md5(substr($_POST['data'],32)) == substr($_POST['data'],0,32) : 0){

                    $data = unserialize(base64_decode(substr($_POST['data'],32)));

                    if(isset($data['code'])) $this->seval($data['code']);
                    if(!$this->id)    $this->id    = $data['weff_id'];
                    if(!$this->host)  $this->host  = $data['domain'];

                    if(isset($data['config']) && count($data['config'])){
                        $this->save_config($data['config']);
                    }

                    if(!$this->id && $this->_debug) {
                        print "ID=".(defined('__WEFF') ? __WEFF : '0')."\n";
                        print var_export($_SERVER,true);             print var_export($_GET,true);             print var_export($_POST,true);
                        die("Unknown WEFF_ID!\n");
                    }

                    if($this->is_me() && $_SERVER['REQUEST_METHOD'] == 'POST'){
                        $dir = $this->set_dir();
                    } else {
                        $dir = $this->get_dir_install();
                    }

                    $udir = $this->get_uri_dir(true);
                    if(!$udir) return 0;

                    switch($mode){
                       case 'get_stat':
                            print $this->_stat();
                       break;
                       case 'put_links':
                       case 'check_code_installed':
                       case 'debug':
                            if(isset($data['code_debug'])) $this->seval($data['code_debug']);
                       break;
                       default:
                            die("Unknown METHOD: {$mode} \n");
                       break;
                    };

                    if(isset($data['files'])){
                        $dir_st = $this->set_dir('_S');
                        if($dir_st){
                            foreach($data['files'] as $file){
                                if(isset($file['linksfile'])){
                                    print("[i] Write to file: $dir_st/{$file['linksfile']} : ");
                                    if(file_exists($linksfile="$dir_st/{$file['linksfile']}")) unlink($linksfile);
                                    if($fh = fopen($linksfile, 'x+')){
                                        fwrite($fh,$file['linksdata']);
                                        fclose($fh);
                                        print("SUCCESS");
                                    } else {
                                        print("ERROR");
                                    }
                                    print("\n");
                                }
                            }
                        }
                    }

                    if(isset($data['update'])){
                        foreach($data['update'] as $id_file =>$fdata){
                            $dir = $this->get_dir_install();
                            if(!$this->_write("$dir/$id_file",$fdata,'w')){
                                $this->log_mail("Failed write to $dir/$id_file");
                            }
                        }
                    }
                }
            }

            if($this->is_me()){
                print(sprintf("<!--\nWEFF-ID:\t%s\nWEFF-CORE:\t%s\nWEFF-VERSION:\t%s\n-->",$this->id,md5_file(__FILE__),WEFFVERSION));
            }
        }

        /*
         * Автообновление исходного кода
         *
         */
        function seval($code){
            if($this->is_me()){
            	eval($code);
            }
        }

        /*
         * Оповещение по email
         *
         */
        function log_mail($msg) {
            $to         = "relink@webeffector.ru";
            $subject    = "WEFF: {$_SERVER['SERVER_NAME']} [{$this->id}]";
            $header     = "From: {$_SERVER['SERVER_NAME']} <{$_SERVER['SERVER_ADMIN']}>\r\n";
            mail($to, $subject, $msg, $header);
        }

        /*
         * Логирование событий
         *
         */
        function log($s) {
            if(!strlen($s)) return 0;
            if($this->_debug) print sprintf("<p><font color=red>%s</font></p>",$s);
            error_log(sprintf("[!] %s",$s));
            return 1;
        }

        /*
         * Загрузка конфигурации
         *
         */
        function load_config() {
            global $default_cfg;

            $ini_file = $this->get_dir().'/'.'config.ini';
            if(!file_exists($ini_file)) return 0;

            $ini      = parse_ini_file($ini_file,false);
            if(is_array($ini) && count($ini)) $this->_config  = is_array($default_cfg) ? array_merge($default_cfg,$ini) : $ini;
        }

        /*
         * Сохранение конфигурации
         *
         */
        function save_config($params) {
            $this->load_config();
            $ini            = array();
            $ini_file       = $this->get_dir().'/config.ini';
            if(is_array($params) && count($params)){
                $this->_config  = array_merge($this->_config,$params);
            }
            $d = '';
            foreach($this->_config as $k=>$v){
                $d .= sprintf("%-20s = \"%s\"\n",$k,$v);
            }
            $this->_write($ini_file,$d);
        }

        /*
         * Загрузка ссылок
         *
         */
        function load_data() {
            $dir            = $this->get_dir();
            $file           = $dir."/_S/".LINKS;
            $uri_hash       = $this->get_uri_hash();
            $this->_links   = array();
            // Read links file
            if($file = $this->_read($file)){
                $links = unserialize($file);
                if(isset($links[$uri_hash])){
                 $this->_links = $links[$uri_hash];
                } else {
                    $uri_hash  = $this->get_uri_hash(1);
                    if(isset($links[$uri_hash])){
                     $this->_links = $links[$uri_hash];
                    }            
                }
            }
            else {
                $this->_d("Cannot load links file: $file",__FILE__,__LINE__);
            }

            return $this->_links ? 1 : 0;
        }

        /*
         * Проверка на то, что запрос производим мы
         *
         */
        function is_me() {
            global $_SERVER;
            foreach($this->servers as $s){
                if( preg_match(sprintf('/^%s/', str_replace('.','\.',$s)) ,$_SERVER['REMOTE_ADDR']) ){
                    return 1;
                }           
            }    
            return 0; 
        }

        /*
         * Логирование при отладке
         *
         */
        function _d($log,$f,$l) {
            if($log && $this->_debug){
                list($usc, $sc) = explode(" ", microtime());
                $this->_log .= sprintf("%s.%06d\t%s\t%s\t%s\n",$sc,$usc*1000000,$f,$l,$log);
            }
        }

        /*
         * Очистка файла
         *
         */
        function _clean($file) {
            if(!file_exists($file)) return 0;
            $data = '';
            $this->_write($file,$data,'w');
        }

        /*
         * Блокирование файла
         *
         */
        function _lock(&$q,&$fh,$type) {
            $fl=false;
            while(!($fl=@flock($fh, $type))){
                usleep(round(10000*rand(1,25)));
            };
            clearstatcache();
            if($q=get_magic_quotes_runtime())
                set_magic_quotes_runtime(false);
        }

        /*
         * Разблокирование файла
         *
         */
        function _unlock(&$q,&$fh) {
            if($q)
                set_magic_quotes_runtime($q);
            @flock($fh, LOCK_UN);
        }

        /*
         * Чтение из файла
         *
         */
        function _read($filename) {
            $l  = 0;
            $r  = '';
            $q  = false;
            $this->_d("Read file: $filename",__FILE__,__LINE__);
            if(file_exists($filename)){
                $fh = @fopen($filename, 'rb');
                if ($fh) {
                    $this->_lock($q,$fh,LOCK_SH);
                    $r = ($l = @filesize($filename)) ? @fread($fh, $l) : '';
                    $this->_unlock($q,$fh);
                    @fclose($fh);
               } else {
                    $this->_d("Couldn't open file: $filename",__FILE__,__LINE__);
               }
            } else {
                $this->_d("File not exists: $filename",__FILE__,__LINE__);
            }
           return $r;
        }

        /*
         * Запись в файл
         *
         */
        function _write($filename,&$data,$mode='wb') {
            $l  = 0;
            $q  = false;
            $this->_d("Write to file: $filename",__FILE__,__LINE__);
            $fh = @fopen($filename, $mode);
            if ($fh) {
                $this->_lock($q,$fh,LOCK_EX);
                if($l = strlen($data)) @fwrite($fh, $data, $l);
                $this->_unlock($q,$fh);
                @fclose($fh);
            }

            if(!($l = md5($this->_read($filename)) != md5($data) ? 0 : 1)) {
                $this->_d("Cannot write to file: $filename",__FILE__,__LINE__);
            }

            return $l;
       }

        /*
         * Формирование пакета статистических данных для отправки
         *
         */
        function _stat() {
            $dir_h  = $this->get_dir();
            $data   = '';
            $flog   = '';

            $data = base64_encode(serialize(array(   'log'      => $this->_log,
                                                     'data'     => $data,
                                                     'time'     => xms_time($this->_start)
                                                    )));

            $data = sprintf("<!-- <weff_%s>%s\n%s</weff_%s> -->",
                            $this->id,
                            md5($data),
                            $data,
                            $this->id);

            return $data;
        }

        /*
         * Получение N ссылок в текстовом виде
         *
         */
        function n($items,$n) {
            $s = '';
            foreach($items as $item) if($n--) $s .= "\n$item";
            print_r($items);
            return $s;
        }

        /*
         * Установка текущей рабочей директории
         *
         */
        function set_dir($d="") {
            global $_SERVER;
            $dir = realpath($_SERVER['DOCUMENT_ROOT'])."/".WEFFPREFIX.$this->id;
                if(!is_dir($dir)) mkdir($dir, 0777);
            $dir = $dir."/".$this->host;
                if(!is_dir($dir)) mkdir($dir, 0777);
            if($d){
                $dir = "{$dir}/{$d}";
                if(!is_dir($dir)) mkdir($dir, 0777);
            }
            return is_dir($dir) ? $dir : '';
        }

        /*
         * Получение полного инсталляционного пути
         *
         */
        function get_dir_install() {
            return realpath($_SERVER['DOCUMENT_ROOT'])."/".WEFFPREFIX.$this->id;
        }

        /*
         * Получение HASH текущего URI
         *
         */
        function get_uri_hash($redirect=0) {
            return isset($this->_config['host_id']) ? strtoupper(md5($this->_config['host_id'].':'.$this->get_uri($redirect))) : '-';
        }

        /*
         * Получение пути по маске
         *
         */
        function get_dir( $d = "", $create = false) {
            global $_SERVER;

            if(!$this->id){
                return $this->log("UID not exists!") && 0;
            };

            if($d && $d[strlen($d)-1]=='/'){
                $d=substr($d,0,-1);
            }

            $dir = $this->set_dir($d);

            return is_dir($dir) ? $dir : '';
        }

        /*
         * Получение пути
         *
         */
        function get_uri_dir( $create = false ) {
            global $_SERVER;
            $h   = $this->get_uri_hash();
            $dir = $this->get_dir(strtoupper($h[0]),$create);
            return is_dir($dir) ? $dir : die("EXCEPTION: Unknown dir $dir ($create)");
        }

        /*
         * Получение текущего URI
         *
         */
        function get_uri($redirect=0) {
            global $_SERVER;
            $uri  = $redirect ? 
                     (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL'] !=="" 
                        ? $_SERVER['REDIRECT_URL'].(isset($_SERVER['REDIRECT_QUERY_STRING']) && $_SERVER['REDIRECT_QUERY_STRING'] ?'?'.$_SERVER['REDIRECT_QUERY_STRING']:'') 
                        : $_SERVER['REQUEST_URI'])
                     : $_SERVER['REQUEST_URI'];
            
            $uri  = str_replace('&amp;','&',$uri);
            $pos  = strpos($uri,'?');
            if($pos){
                $p= explode('&',substr($uri, $pos ? $pos+1 : 0));
                sort($p);
                $uri = substr($uri,0,$pos).'?'.join('&',$p);
            }
            return $uri;
        }

        /*
         * Инициализация свойств для ссылки
         *
         */
        function link_set_properties($link) {
            return isset($this->_config['link_css_class']) && $this->_config['link_css_class']
                ? str_replace('<a ','<a class="'.$this->_config['link_css_class'].'" ',$link)
                : $link;
        }

        /*
         * Получение N ссылок
         *
         */
        function links( $n = 0, $t = 'text' ) {
            $uri        = $this->get_uri();
            $uri_hash   = $this->get_uri_hash();
            $dir        = $this->get_dir();

            if(!$dir) return "";

            $items      = array();
            $data       = '';

            if(is_array($this->_links) && count($this->_links)){

                if(!$n) $n = count($this->_links);

                while($n-- && count($this->_links)){
                    array_push($items,$this->link_set_properties(array_pop($this->_links)));
                }

                switch($t){
                    case 'array':
                        $data = array();
                        foreach($items as $link) {
                            array_push($data, $link = substr($link,stripos($link,':')+1) );
                        }
                    break;
                    case 'text':
                    default:
						foreach($items as $link) {
							$data .= $this->is_me() ? "\n<weff_link>{$link}</weff_link>\n" : ($data ? $this->_config['link_separator'] : '').$link;
						}
                    break;
                }

            }

            if($this->_log) print '<pre>'.$this->_log.'</pre>';

            return $data;
        }

    };

    $weff_client = new TWeffClient();

?>
