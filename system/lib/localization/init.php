<?php

function __autoload($class)
{
	include(dirname(__FILE__).DIRECTORY_SEPARATOR.$class.".class.php");
}

?>