<?php

$rights['MW'] = array(
	'APP_ID' => 'MW',
	'ORDER' => 10,
	'TITLE' => _s("Personal settings"),
	'SCREEN_ID' => 'PF',
	'SECTIONS' => array(
		array(
			'ID' => 'FUNCTIONS',
			'TITLE' => _s('Available functions'),
			'OBJECTS' => array(
				array('TAB_CONTACT', _s("Can edit personal contact information")),
				array('TAB_USER', _s("Can edit personal user settings")),
			)
		)
	)
);
?>