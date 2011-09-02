<?php

class PDRightsModel extends RightsModel 
{
	public function __construct()
	{
		parent::__construct("PD", Locale::getStr("pd", "app_name_long"));
		$this->setDescriptor(new PDRightsDescriptor());
	}
	
	public function loadFolders()
	{
		// Get folders
		$sql = new CSelectSqlQuery("PIXFOLDER");
		$sql->addConditions("PF_STATUS", 0);
		$sql->setCustomOrderBy("PF_NAME ASC");
		$sql->setSelectFields("PF_ID ID, PF_ID_PARENT ID_PARENT, PF_NAME NAME");
		
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