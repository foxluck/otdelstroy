<?php

class UGRightsDescriptor extends RightsDescriptor 
{

	const SCREEN_ID = "UNG";
	/**
	 * @param RightsModel $rights_model
	 */
	public function exec($rights_model)
	{
		$section = new RightsSection(RightsModel::SECTION_SCREENS, "");		
		$section->addRight(
			new RightsItem(self::SCREEN_ID, _('This user can manage other users and user groups.'))
		);
		$rights_model->addSection($section);
	}
}

?>