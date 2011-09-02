<?php

function smarty_modifier_json($object)
{
	return json_encode($object);
}
