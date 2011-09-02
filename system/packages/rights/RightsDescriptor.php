<?php

/**
 * Parent class to describe  the system rights of application 
 * 
 * @example
 * public function exec($rights_model) {
 * 		// Set main screen
 * 		$right_model->setScreen("CT");
 * 
 * 		// New section
 * 		$section = new RightsSection($id, $name);
 * 		$section->addRight(
 * 			// Add a right to the section
 * 			new RightsItem($id, $name),
 * 			...
 * 		);
 * 
 * 		// Add the section to the description
 * 		$rights_model->addSection($section);
 * }
 *
 */
class RightsDescriptor 
{
	
	const APP_CANTOOLS_RIGHTS = 'CANTOOLS';
	const APP_CANREPORTS_RIGHTS = 'CANREPORTS';
	const APP_CANWIDGETS_RIGHTS = 'CANWIDGETS';
	
	
	/**
	 * @param RightsModel $rights_model
	 */
	public function exec($rights_model)
	{
		
	}
}

?>