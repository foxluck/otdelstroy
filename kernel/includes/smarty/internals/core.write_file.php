<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * write out a file to disk
 *
 * @param string $filename
 * @param string $contents
 * @param boolean $create_dirs
 * @return boolean
 */
function smarty_core_write_file($params, &$smarty)
{
    $_dirname = dirname($params['filename']);

    if ($params['create_dirs']) {
        $_params = array('dir' => $_dirname);
        require_once(SMARTY_CORE_DIR . 'core.create_dir_structure.php');
        smarty_core_create_dir_structure($_params, $smarty);
        if (!file_exists($_dirname)||(!is_writable($_dirname)))
        {
        	$_dirname = $smarty->compile_dir;
        	$smarty->use_sub_dirs = false;        	        
        }
    }

    // write to tmp file, then rename it to avoid file locking race condition
    $_tmp_file = tempnam($_dirname, 'wrt');

    if (!($fd = @fopen($_tmp_file, 'wb'))) {
        $_tmp_file = $_dirname . DIRECTORY_SEPARATOR . uniqid('wrt');
        if (!($fd = @fopen($_tmp_file, 'wb'))) {
            $smarty->trigger_error("problem writing temporary file '$_tmp_file'");
            return false;
        }
    }

    fwrite($fd, $params['contents']);
    fclose($fd);

    if (DIRECTORY_SEPARATOR == '\\' || !@rename($_tmp_file, $params['filename'])) {
        // On platforms and filesystems that cannot overwrite with rename() 
        // delete the file before renaming it -- because windows always suffers
        // this, it is short-circuited to avoid the initial rename() attempt
        @unlink($params['filename']);
        if ( !@rename($_tmp_file, $params['filename']) ){
	    @copy($_tmp_file, $params['filename']);
            @unlink( $_tmp_file );
        }
    }
    @chmod($params['filename'], $smarty->_file_perms);

    return true;
}

/* vim: set expandtab: */

?>