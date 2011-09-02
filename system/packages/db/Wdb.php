<?php
	/*
		static class for working with one webasyst database
		Singleton, but instance creating by connect function(!)
	*/
	
	class Wdb {
			/**
			 * @var MysqlDb
			 */
			private static $instance;
			
			public static function connect ($host, $port, $user, $password, $dbName) {
				if (!self::$instance)
					self::$instance = new MysqlDb();
				self::$instance->connect ($host, $port, $user, $password, $dbName);
				return true;
			}
			
			/**
			 * @return MysqlDb
			 */
			private static function getInstance() {
				if (!self::$instance)
					throw new RuntimeException ("Try to use not connected database");
				return self::$instance;
			}
			
			public static function runQuery ($query) {
				$db = self::getInstance();
				return $db->runQuery($query);
			}		
			
			public static function getData ($query, $keyField = null) {
				$db = self::getInstance();
				return $db->getData($query, $keyField);
			}
			
			public static function getRow ($query) {
				$db = self::getInstance();
				return $db->getRow($query);
			}
			
			public static function insertId () {
				$db = self::getInstance();
				return $db->insertId();
			}		
			
			public static function getFirstField($query) {
				$db = self::getInstance();
				return $db->getFirstField($query);
			}
			
			public static function setCharset ($charset) {
				$db = self::getInstance();
				return $db->setCharset($charset);
			}
	}
	
?>