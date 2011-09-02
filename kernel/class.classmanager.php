<?php
	/**
	 * Class manager allow dynamically connect files with classes
	 *
	 * @author Gumenuk Aleksey
	 * @version 1.1
	 */
	class ClassManager{

		/**
		 * Include class file. If class doesnt exist die.
		 *
		 * @param string $ClassName
		 * @param string $SourceType
		 * @return null | false
		 */
		static function includeClass($ClassName, $SourceType = 'kernel'){

			if(!class_exists(strtolower($ClassName))){

				if(!ClassManager::isExistingClass($ClassName, $SourceType)){

					$ClassFilePath = ClassManager::getClassFilePath($ClassName, $SourceType);
					return false;
					die('Class '.$ClassName.' in '.$ClassFilePath.' doesnt exist!');
				}
			}
		}

		/**
		 * Get reference to new object of class
		 *
		 * @param string $ClassName
		 * @param string $SourceType
		 * @param array $Args could contain only simple types (integer, string, etc...)
		 * @return mixed
		 */
		function &getInstance($ClassName, $SourceType, $Args = array()){

			ClassManager::includeClass($ClassName, $SourceType);

			if(count($Args)){

				$func = create_function('$str', 'return str_replace("\'","\\\'",$str);');
				$SArgs = "'" . join( "','", array_map($func,$Args) ). "'";

				$return = "return new $ClassName($SArgs);";
PEAR::raiseError($return);
				return eval($return);
			}else{

				return new $ClassName();
			}
		}

		/**
		 * Check is class existing
		 *
		 * @param string $ClassName
		 * @param string $SourceType
		 * @return bool
		 */
		static function isExistingClass($ClassName, $SourceType){

			if(!class_exists(strtolower($ClassName))){

				$ClassFilePath = ClassManager::getClassFilePath($ClassName, $SourceType);
				if(!file_exists($ClassFilePath)){
					return false;
				}

				include_once($ClassFilePath);

				return class_exists(strtolower($ClassName));
			}else{

				return true;
			}
		}

		/**
		 * Get path to class file
		 *
		 * @param string $ClassName
		 * @param string $SourceType
		 * @return string
		 */
		static function getClassFilePath($ClassName, $SourceType){

			$ClassesPath = WBS_DIR;

			switch ($SourceType){
				case 'kernel':
					$ClassesPath .= '/kernel/classes';
					break;
				default:
					$ClassesPath .= '/published/'.$SourceType.'/classes';
					break;
			}

			return file_exists($ClassesPath.'/class.'.strtolower($ClassName).'.php')?$ClassesPath.'/class.'.strtolower($ClassName).'.php':$ClassesPath.'/'.strtolower($ClassName).'.php';
		}
	}
?>