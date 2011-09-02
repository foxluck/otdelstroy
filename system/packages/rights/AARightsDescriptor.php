<?php

class AARightsDescriptor extends RightsDescriptor 
{

	const SCREEN_ID = "CP";
	/**
	 * @param RightsModel $rights_model
	 */
	public function exec($rights_model)
	{
		$section = new RightsSection(RightsModel::SECTION_SCREENS, "");		
		$section->addRight(
			new RightsItem(self::SCREEN_ID, _('This user can manage account settings, e.g. customize system parameters, upgrade/downgrade services, and even cancel account.'))
		);
		$rights_model->addSection($section);
	}
}

?>