<?php
	class Tag extends Object {
		
		var $name = '';
		var $id = 0;
		var $weight = 0;
		
		function isTag($object){
			
			return ($object instanceof tag)?true:false;
		}
		
		function create($name){
			
			$TagMananger = &ClassManager::getInstance('tagmanager');
			/* @var $TagMananger TagManager */
			$Tag = $TagMananger->findTagByName($name);
			if(Tag::isTag($Tag)){
				return PEAR::raiseError('Tag with name "'.$name.'" exists');
			}
			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			$dbq = 'INSERT ?#TAGS_TBL (name) VALUES(?)';
			$DBRes = $DBHandler->ph_query($dbq, $name);
			$this->id = $DBRes->getInsertID();
			$this->name = $name;
		}
		
		/**
		 *
		 * @param int $connect_id
		 */
		function tagObject($object_type, $language_id, $object_id){
			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			$dbq = '
				INSERT ?#TAGGED_OBJECTS_TBL (tag_id, object_id, object_type, language_id) VALUES(?,?,?,?)
			';
			$DBHandler->ph_query($dbq, $this->id, $object_id, $object_type, $language_id);
		}
	}
?>