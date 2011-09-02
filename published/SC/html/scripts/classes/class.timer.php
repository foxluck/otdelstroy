<?php
	class Timer{
		var $timers;
		function Timer(){
			$timers=array();
			$timers['start']=array();
			$timers['stop']=array();
		}
		function timerStart($name='default'){
			$time_portions=explode(' ',microtime());
			$actual_time=$time_portions[1].substr($time_portions[0],1);
			$this->timers['start'][$name]=$actual_time;
		}
		
		function timerStop($name='default'){
			$time_portions=explode(' ',microtime());
			$this->timers['stop'][$name]=$time_portions[1].substr($time_portions[0],1);
			return function_exists('bcsub')?bcsub($this->timers['stop'][$name],$this->timers['start'][$name],6):(sprintf('%0.6f',$this->timers['stop'][$name]-$this->timers['start'][$name]));
		}
		function getElapsed($name='default'){
			return function_exists('bcsub')?bcsub($this->timers['stop'][$name],$this->timers['start'][$name],6):(sprintf('%0.6f',$this->timers['stop'][$name]-$this->timers['start'][$name]));
		}
	}
?>