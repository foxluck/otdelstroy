<?php
define('URL_PUBDATA_ROOT', WBS_INSTALL_PATH.'/published/publicdata');
$DB_KEY=SystemSettings::get('DB_KEY');
foreach(array('products_pictures','images','themes') as $fld)
{
    if(file_exists(DIR_ROOT.'/../../../..'.URL_PUBDATA_ROOT.'/'.$DB_KEY.'/attachments/SC/'.$fld))
    {
        define('URL_'.strtoupper($fld), URL_PUBDATA_ROOT.'/'.$DB_KEY.'/attachments/SC/'.$fld);
    }
    else
    {
        define('URL_'.strtoupper($fld), URL_PUBDATA_ROOT.'/__DEFAULT/attachments/SC/'.$fld);
    };
};
?>