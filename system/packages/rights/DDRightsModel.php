<?php

class DDRightsModel extends RightsModel 
{

	public function __construct()
	{
		parent::__construct("DD", Locale::getStr("dd", "dd_screen_long_name"));
		$this->setDescriptor(new DDRightsDescriptor());
	}
	
	/**
	 * Load folders from database for the user
	 * Add add new folder's description to model
	 */	
	public function loadFolders()
	{
		// Get folders
		$sql = new CSelectSqlQuery("DOCFOLDER");
		$sql->addConditions("DF_STATUS", 0);
		$sql->setCustomOrderBy("DF_SPECIALSTATUS > 0 ASC, DF_NAME ASC");
		$sql->setSelectFields("DF_ID ID, DF_ID_PARENT ID_PARENT, DF_NAME NAME");
		
		$folders = Wdb::getData($sql);
		
		// Add description for the new Folders
		$section = $this->getSection(RightsModel::SECTION_FOLDERS);
		if ($section) {			
			foreach ($folders as $folder) {
				$section->addRight(new FolderRights($folder));
			}
		}
	}
	
}


?>