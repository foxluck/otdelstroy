<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     pd_shareBigImagePanel
 * Purpose:  Declares big image panel payout 
 * -------------------------------------------------------------
 */

function smarty_block_pd_shareBigImagePanel( $params, $content, &$smarty, &$repeat )
{   
    $result = '<script>
        var bigImagesUrls = new Array();
        var currentThumbSize = '.$params["imageSize"].';
    </script>';
    $i = 0;
    
    //$result .= '<table cellpadding="0" cellspacing="0" border="1" align="center" id="bigImageDiv"><tr>';
    foreach ($params["filesList"] as $curFile) {
        $file = $_SESSION["imageUrls"][$params["imageSize"]][$i];

        $emptyfile = 0;
        
        if ($i > 0) {
            $file = '';
            $emptyfile = 1;
        }
        
        $result .= '
            <div emptyfile="'.$emptyfile.'" id="image_'.$i.'" style="display: none;" align="center">
                <img num="'.$i.'" src="'.$file.'" id="img_'.$i.'" style="display: block;">
            </div>
        ';
        $i ++;
    }
    
    //$result .= '</tr></table>';
    
	return $result;
}

?>