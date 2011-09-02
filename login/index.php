<?php
	if(file_exists('../published/login.php'))
	header( "Location: ../published/login.php" );
	else
		print 'WebAsyst not installed yet';

?>