<?php


class DDRightsDescriptor extends RightsDescriptor 
{
	const SCREEN_ID = 'CT';
	
	public function exec(RightsModel $rights_model)
	{
	
		// Available functions
		$section = new RightsSection(RightsModel::SECTION_FUNCTIONS, _('Available Functions'));
		$section->addRight(
			new RightsItem(self::APP_CANTOOLS_RIGHTS, _("Has access to Recycle Bin and Settings")),
			new RightsItem(self::APP_CANREPORTS_RIGHTS, _("Can use Reports")),
			new RightsItem(self::APP_CANWIDGETS_RIGHTS, _("Can manage Widgets")) 
		);
		$rights_model->addSection($section);
		
		// Notifications
		$section = new RightsSection(RightsModel::SECTION_MESSAGES, _("Notifications"));
		$section->addRight(
			new RightsItem("ONFOLDERUPDATE", _("Is notified on folder update"))
		);
		$rights_model->addSection($section);
		
		// Folders
		$section = new RightsSection(RightsModel::SECTION_FOLDERS, _("Available Actions with Folders"));
		$section->addRight(
			new RightsItem("ROOT", _("Can create root folders")),
			new RightsItem("VIEWSHARES", _("Can see other users' permissions"))
		);
		$rights_model->addSection($section);
	}
	
	public function getScreen()
	{
		return self::SCREEN_ID;
	}
	
}

?>