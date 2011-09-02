<?php

function smarty_modifier_strpos($str1, $str2)
{
    return (strpos($str1, $str2) !== false); 
}

?>