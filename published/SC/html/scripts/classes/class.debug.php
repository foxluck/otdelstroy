<?php
/**
 * @author
 * @since May 2009
 * @version SVN: $Id: class.debug.php 1355 2010-07-29 13:26:48Z vlad $
 *
 */
class Debug
{
	const COMMON = 'common';
	const RECORDS_LIMIT = 1000;
	private static $instances = array();
	private $data = array();
	private $log = array();
	private $type_counter = array();
	private $file_name;
	private $name;
	private $call_time_stack = array();
	private $call_place_stack = array();
	private $call_memory_stack = array();
	private $call_files_stack = array();
	private $call_parent_stack = array();
	private $call_id = 0;
	private $total_time = 0;
	private $total_memory = 0;


	/**
	 * Enter description here...
	 *
	 * @param string $name instance and file names
	 * @return Debug
	 */
	static function &getInstance($name = self::COMMON){
		if(!isset(self::$instances[$name])){
			self::$instances[$name] = new Debug($name);
		}
		return self::$instances[$name];
	}

	function __construct($name)
	{
		$this->name = $name;
		$path = sprintf('%s/.profile',DIR_ROOT);
		if(file_exists($path)&&is_dir($path)){
			$this->file_name = sprintf('%s/.profile/%s.log',DIR_ROOT,$name);
		}
	}

	function start()
	{
		if($this->name!= self::COMMON){
			$common_debug = self::getInstance(self::COMMON);
			$common_debug->start();
		}
		$this->call_id++;
		array_push($this->call_time_stack,microtime(true));
		array_push($this->call_memory_stack,self::memory_get_usage());
		array_push($this->call_files_stack,count(get_included_files()));
		array_push($this->call_parent_stack,sprintf('%03d',$this->call_id));

	}

	static function memory_get_usage()
	{
		static $exist;
		if(!isset($exist)){
			$exist = function_exists('memory_get_usage');
		}
		return $exist?memory_get_usage(false):0;
	}

	static function memory_get_peak_usage()
	{
		static $exist;
		if(!isset($exist)){
			$exist = function_exists('memory_get_peak_usage');
		}
		return $exist?memory_get_peak_usage(false):0;
	}

	static function tree_sort($a,$b)
	{
		$res = 0;
		if(isset($a['tree'])&&isset($b['tree'])&&($a['tree']!=$b['tree'])){
			//$res = (($a['tree']-$b['tree'])>0)?1:(-1);
			$res = strcmp($a['tree'],$b['tree']);
		}
		return $res;
	}

	private function update_counter($type, $time = 0){
		if(!isset($this->type_counter[$type])){
			$this->type_counter[$type] = array('counter'=>0,'time'=>0);
		}
		$this->type_counter[$type]['counter']++;
		$this->type_counter[$type]['time'] += $time;
	}

	/**
	 * @param mixed $variables
	 * @param string $type
	 * @param boolean $recursive
	 * @return null
	 * @example $debug_instance->end(array('debug_param1'=>$debug_param1,'debug_param2'=>$debug_param2),__FUNCTION__);
	 */
	function end($variables = null,$type = 'general',$recursive = true)
	{
		if(!$variables){
			$variables = array();
		}elseif(!is_array($variables)){
			$variables = array($variables);
		}

		foreach ($variables as $id=>$value){
			if(is_int($id)){
				unset($variables[$id]);
				$id = sprintf('p%02d',$id+1);
				$variables[$id] = $value;
			}
			if(is_array($value)){
				$variables[$id] = preg_replace('/[\\n]+/','',var_export($value,true));
			}
		}

		if($this->name!= self::COMMON){
			$common_debug = self::getInstance(self::COMMON);
			$common_debug->end($variables,$type);
		}

		$time = microtime(true) - array_pop($this->call_time_stack);

		$total_memory = self::memory_get_usage();
		$memory = $total_memory - array_pop($this->call_memory_stack);
		$total_files = count(get_included_files());
		$files = $total_files - array_pop($this->call_files_stack);
		$tree = implode(':',$this->call_parent_stack);
		$id = array_pop($this->call_parent_stack);

		if(!count($this->call_time_stack)){
			$this->total_time += $time;
			$this->total_memory += $memory;
		}

		$level = count($this->call_time_stack);
		$this->data[] = array_merge(
		array(
		'id'=>$id,
		'tree'=>$tree,
		sprintf('%-21s','level')=>str_repeat('|',$level).'+'.str_repeat(' ',max(0,20-$level)),
		'time'=>sprintf('%03.3f ms',$time*1000),
		'files'=>sprintf('%+d/%d',$files,$total_files),
		'memory'=>sprintf('%+04.3f KB',$memory/1024),
		'total_memory'=>sprintf('%0.3f MB',$total_memory/1048576),
		'type'=>$type,
		),
		$variables);
		if(($type == 'SQL')&&($this->name== self::COMMON)){
			static $count = 0;
			global $debug_sql_query_stack;
			if(!isset($debug_sql_query_stack)){
				$debug_sql_query_stack = array();
				$debug_sql_query_stack[] = array('#','time','type','query');
			}
			$debug_sql_query_stack[] = array($count++,sprintf('%03.3fms',$time*1000),$variables['SQL type'],isset($variables['query'])?$variables['query']:'',);
		}
		$this->update_counter($type,$time);
	}


	protected function write($variables = null,$type = 'general')
	{
		if(!$variables){
			$variables = array();
		}elseif(!is_array($variables)){
			$variables = array($variables);
		}

		$total_memory = self::memory_get_usage();

		$this->log[] = array_merge(
		array(
		'time'=>0,
		'memory'=>0,
		'total_memory'=>sprintf('%0.3f MB',$total_memory/1048576),
		),
		$variables
		);
		$this->update_counter($type);
	}

	function __destruct()
	{
		if($this->file_name&&($fp = fopen($this->file_name,'a'))){
			fprintf($fp,"%s\t%s\r\n\r\n",date('c'),$_SERVER['QUERY_STRING']);
			if(($count = count($this->data))>self::RECORDS_LIMIT){
				$size = floor(self::RECORDS_LIMIT/2);
				$this->data = array_merge(array_slice($this->data,0,$size,true),array_slice($this->data,$count-$size,$size,true));
			}
			usort($this->data,'Debug::tree_sort');
			$headers = array();
			foreach($this->data as $counter=>$data_item){
				$headers = array_merge($headers,array_keys($data_item));
			}
			$headers = array_unique($headers);
			if($id = array_search('tree',$headers)){
				unset($headers[$id]);
			}
			if($id = array_search('level',$headers)){
				//$headers[$id] = sprintf('% 21s',$headers[$id]);
			}
			fprintf($fp,"%s\t%s\r\n",'##',implode("\t",$headers));

			foreach($this->data as $counter=>$data_item){
				unset($data_item['tree']);
				$data_items = array();
				foreach($headers as $header){
					$data_items[] = isset($data_item[$header])?$data_item[$header]:' ';
				}
				fprintf($fp,"+%03d\t%s\r\n",$counter+1,implode("\t",$data_items));
			}
			foreach($this->log as $counter=>$log_item){
				fprintf($fp,"log\t+%03d\t%s\r\n",$counter+1,implode("\t",$log_item));
			}

			foreach($this->type_counter as $type=>$info){
				fprintf($fp,"[%s]\t%d\t%0.3f ms\r\n",$type,$info['counter'],$info['time']*1000);
			}

			fprintf($fp,"total\t%0.3f ms\t%0.3f MB\t max: %0.3f MB\t%d files\r\n\r\n",$this->total_time*1000,$this->total_memory/1048576,self::memory_get_peak_usage()/1048576,count(get_included_files()));
			fclose($fp);
			$this->filesList();
		}
	}

	protected function filesList()
	{
		if($this->name!= self::COMMON){
			return false;
		}
		if(!($fp = fopen(sprintf('%s/.profile/%s.log',DIR_ROOT,'include_files'),'w'))){
			return false;
		}

		$path = str_replace(WBS_DIR,'',DIR_ROOT);
		$path = preg_replace('@([/\\\\]+)@','/',$path.'/');
		$app_path = preg_replace('@([/\\\\]+)@','/',realpath(DIR_ROOT).'/');
		$app_base_path =  preg_replace('@([/\\\\]+)@','/',DIR_ROOT.'/');
			
		$files = get_included_files();
		asort($files,SORT_STRING);
		$base_path = preg_replace('@([/\\\\]+)@','/',WBS_DIR.'/');
		$files_sorted = array('kernel/includes/pear/'=>array(),
		sprintf('kernel/includes/smarty/compiled/SC/%s/',SystemSettings::get('DB_KEY'))=>array(),
		'kernel/includes/smarty/'=>array(),
		$path.'modules/'=>array(),
		$path.'core_functions/'=>array(),
		$path.'classes/'=>array(),
		$path.'includes/'=>array(),
		$path.'cfg/'=>array(),
		$path.'html/scripts/'=>array(),
		'kernel'=>array(),
		'published'=>array(),
		sprintf('data/%s/attachments/SC/',SystemSettings::get('DB_KEY'))=>array(),
		'system/'=>array(),
		'shop/'=>array(),
		'wa-apps/'=>array(),
		'wa-cache/'=>array(),
		'wa-data/'=>array(),
		'wa-system/lib/smarty/plugins/'=>array(),
		'wa-system/lib/smarty/'=>array(),
		'wa-system/'=>array(),
		'wa-config'=>array(),
		);
		if($app_path != $app_base_path){
			$app_path = preg_replace('@^/@','',$app_path);
			foreach(array('modules/','core_functions/','lib/classes/','lib/smarty/','includes/','cfg/') as $part){
				$files_sorted[$app_path.$part] = &$files_sorted[$path.$part];
			}
		}
		$files_sorted['/'] = array();
		foreach($files as $file){
			$file = preg_replace('@([/\\\\]+)@','/',$file);
			$file = str_replace($base_path,'',$file);
			$file = preg_replace('|^/+|','',$file);
			foreach($files_sorted as $path=>&$path_files){
				if(strpos($file,$path)===0){
					$path_files[] = str_replace($path,'',$file);
					unset($path_files);
					continue(2);
				}
			}
			$files_sorted['/'][] = $file;
		}
		$total_count = 0;
		foreach($files_sorted as $path=>$names){
			$names = array_unique($names);
			$count = count($names);
			$total_count += $count;
			if(!$count){
				continue;
			}
			fprintf($fp, "\r\n%s [%d]\r\n",$path,$count);
			foreach($names as $name){
				fprintf($fp, "%s\r\n",$name);
			}

		}
		fprintf($fp, "Total: %d\r\n\r\n",$total_count);
		fclose($fp);
	}
}
?>