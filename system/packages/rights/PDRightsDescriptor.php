<?php

class PDRightsDescriptor extends RightsDescriptor 
{
	
	public function exec(RightsModel $rights_model)
	{
		// Set main screen
		$rights_model->setScreen("CT");
		
		// Folders
		$section = new RightsSection(RightsModel::SECTION_FOLDERS, Locale::getStr("pd", "app_treefolders_text"));
		$section->addRight(
			new RightsItem("ROOT", Locale::getStr("pd", "app_treerootfolders_label")),
			new RightsItem(RightsItem::TITLE, Locale::getStr("pd", "app_treeavailflds_title"))
		);
		$rights_model->addSection($section);
	}
}

?>