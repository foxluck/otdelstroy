<?php

function smarty_modifier_division_id($ukey)
{
    return  DivisionModule::getDivisionIDByUnicKey($ukey);
};
?>