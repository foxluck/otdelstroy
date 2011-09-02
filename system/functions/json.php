<?php

/**
 * Returns the JSON representation of a value
 * 
 * @param mixed $string
 * @return string
 */
function json_encode($value) 
{
	$json = new Services_JSON();
	return $json->encode($value);
}

/**
 * Decodes a JSON string
 * 
 * @param string $string
 * @param bool $assoc
 * @return object|array
 */
function json_decode($string, $assoc = false) 
{
	$json = new Services_JSON();
	if ($assoc) {
		$json->use = SERVICES_JSON_LOOSE_TYPE;
	} 
	return $json->decode($string);
}

?>
