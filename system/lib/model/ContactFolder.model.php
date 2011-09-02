<?php

class ContactFolderModel extends DbModel
{
	protected $table = "CFOLDER";
	
	public function getAll($user_id, $right = 1)
    {
    	// Get All Folders
    	$sql = "SELECT CF_ID ID, CF_ID_PARENT PARENT, CF_NAME NAME
    			FROM CFOLDER WHERE CF_STATUS >= 0";
    	$data = $this->query($sql);
    	// Get Right On Contacts
    	$result = array();
    	$rights = new Rights($user_id);
    	foreach ($data as $row) {
    		if ($with_right) {
    			$row['RIGHT'] = $rights->get('CM', Rights::FOLDERS, $row['ID'], true);
    			if ($row['RIGHT'][0] >= $with_right) {
    				$result[$row['ID']] = $row;
    			}	
    		} else {
    			$result[$row['ID']] = $row;
    		}
	   	}
    	return $result;	
    }	
    
    public function get($folderId, $user_id = false) {
        $contact_id = User::getContactId($user_id);
        switch ($folderId) {
            case "PRIVATE".$contact_id:
        		$row = array(
        			'ID' => $folderId,
        			'NAME' => _s('Private'),
        		    'RIGHTS' => User::hasAccess('CM', 'FOLDERS', 'PRIVATE') ? Rights::RIGHT_WRITE : 0 
        		);
        		break;
            case "PUBLIC":
                $row = array(
        			'ID' => 'PUBLIC',
        			'NAME' => _s('Public'),
                    'RIGHTS' => Rights::RIGHT_WRITE
                );
                break;
            case "ALL":
                $row = array(
        			'ID' => 'ALL',
        			'NAME' => _s('All contacts'),
                    'RIGHTS' => Rights::RIGHT_WRITE
                );    
                break;            
            default:
                $sql = "SELECT CF_ID ID, CF_ID_PARENT PARENT, CF_NAME NAME
            			FROM CFOLDER WHERE CF_ID = s:id AND CF_STATUS >= 0";
            	$row = $this->prepare($sql)->query(array( 'id' => $folderId ))->fetchAssoc();
            	if ($row && $user_id) {
                	$rights = new Rights($user_id);
                	$row['RIGHTS'] = $rights->get('CM', Rights::FOLDERS, $row['ID'], Rights::MODE_ONE);
            	}                
        }
    	return $row;	        
    }
    
    public function rename($folder_id, $name) {
       $sql = "UPDATE ".$this->table." SET CF_NAME = s:name WHERE CF_ID = s:id";
        return $this->prepare($sql)->query(array('id' => $folder_id, 'name' => $name))->affectedRows();
        
    }
    
    /**
     * Returns next available id for the folder
     * Instead auto_increment
     * 
     * @param $parent
     * @return string
     */
    public function getNextId($parent = 'ROOT')
    {
    	$sql = "SELECT MAX(IF(CF_ID_PARENT = 'ROOT', CF_ID, SUBSTR(CF_ID, LENGTH(CF_ID_PARENT) + 1)) + 0) ID 
    			FROM CFOLDER WHERE CF_ID_PARENT = s:parent";
    	$id = $this->prepare($sql)->query(array('parent' => $parent))->fetchField('ID');
    	if ($id) {
    		$id++;
    		$id = ($parent == 'ROOT' ? '' : $parent) . $id . ".";
    	} elseif ($parent == 'ROOT') {
    		$id = '1.';
    	} else {
    		$id  = $parent. '1.';
    	}
    	return $id;
    }
    
    
    /**
     * Create folder and returns id of the new folder
     * 
     * @param $name
     * @param $parent
     * @param $type
     * @param $status
     * @return string
     */
    public function add($name, $parent = 'ROOT', $status = 0)     	
    {
    	$sql = "INSERT INTO CFOLDER 
    			SET CF_ID = s:id, 
    				CF_ID_PARENT = s:parent, 
    				CF_NAME = s:name, 
    				CF_STATUS = i:status";
    	$folder_id = $this->getNextId($parent);
    	$data = array(
    		'id' => $folder_id,
    		'parent' => $parent, 
    		'name' => $name,
    		'status' => $status
    	);
    	if ($this->prepare($sql)->exec($data)) {
    		return $folder_id;
    	} else {
    		throw new Exception(_("Error creating folders"));
    	}
    }    

    /**
     * Move folder and update ID in "contact" table
     * 
     * @param $old (from CF_ID)
     * @param $new (to CF_ID)
     * @return string
     */
	public function move($old, $new_parent)
	{
		$new = $this->getNextId($new_parent);
		
		// Folder
		$sql = "UPDATE ".$this->table." SET CF_ID = s:new, CF_ID_PARENT = s:new_parent WHERE CF_ID = s:old";
		$this->prepare($sql)->exec(array('new' => $new, 'new_parent' => $new_parent, 'old' => $old));
		
		// Children
		$sql = "UPDATE ".$this->table." 
				SET CF_ID = CONCAT('".$this->escape($new)."', SUBSTR(CF_ID, ".(strlen($old) + 1).")),
				CF_ID_PARENT = CONCAT('".$this->escape($new)."', SUBSTR(CF_ID_PARENT, ".(strlen($old) + 1).")) 
				WHERE CF_ID LIKE '".$this->escape($old)."%'";
		$this->exec($sql);
		
		return $new;
	}
    
    public function delete($folder_id)
    {
    	$folder_id .= "%";
        $sql = "DELETE FROM ".$this->table." WHERE CF_ID LIKE s:folder_id";
        return $this->prepare($sql)->exec(array('folder_id' => $folder_id));	
    }
}

?>