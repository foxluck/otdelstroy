<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     pd_shareThumbPanel
 * Purpose:  Declares thumbnails panel payout 
 * -------------------------------------------------------------
 */

function smarty_block_pd_shareThumbPanel( $params, $content, &$smarty, &$repeat )
{
    $pdStrings = $smarty->get_template_vars("pdStrings");
    
    $countFiles = count($params["filesList"]);
    
	$result = '
	    <table cellpadding="0" cellspacing="0" border="0">
	    <tr>
	        '.($countFiles > PD_SLIDESHOW_DEFAULT_THUMBS_COUNT ? '
	        <td id="prevSel" valign="top" style="display: none; padding-top: 12px;">
	            <input type="image" src="../../../common/html/res/images/film-left.gif" value="<" onClick="pd_changeThumbPage(-1);">&nbsp;
	        </td>
	        ' : '').'
	        <td>
	        
	        <div id="thumbsPanel"></div>';
    
    /*
	switch($params["outputView"]) {
	    case 1:
	        $cur = 0;
	        foreach ($params["filesList"] as $curFile) {
	            $file1 = $curFile["fileURL"]."&SIZE=".$params["thumbHoverSize"];
	            $file2 = $curFile["fileURL"]."&SIZE=".$params["thumbSize"];
	            $result .= '
	                    <div id="thumb1_div_'.$cur.'" style="display: none; z-index: 999;">
	                        <img src="'.$file1.'" border="0">
	                    </div>
	                    <a href="javascript: void(0)"><img src="'.$file2.'" border="0" id="thumb2_div_'.$cur.'" onMouseOver="pd_shareShowPopUpThumb('.$cur.');" onMouseOut="pd_shareClosePopUpThumb('.$cur.');" onClick="if (!isPlayStart) { pd_shareChangePicture('.$cur.', 1); }" style="border: 2px solid #FFFFFF;";></a>
	                ';
	            $cur ++;
	        }
	        
	        break;
	    case 2:
	        
	        $cur = 0;
	        foreach ($params["filesList"] as $curFile) {
	            $file1 = $curFile["fileURL"]."&SIZE=".$params["thumbHoverSize"];
	            $file2 = $curFile["fileURL"]."&SIZE=".$params["thumbSize"];
	            $result .= '
	                    <div id="thumb1_div_'.$cur.'" style="display: none; z-index: 999;"></div>
	                    <a href="javascript: void(0)"><img src="'.$file1.'" border="0" id="thumb2_div_'.$cur.'" onMouseOver="pd_shareShowPopUpThumb('.$cur.');" onMouseOut="pd_shareClosePopUpThumb('.$cur.');" onClick="if (!isPlayStart) { pd_shareChangePicture('.$cur.', 1); }" style="border: 2px solid #FFFFFF;";></a>
	                ';
	            $cur ++;
	        }
	        
	        break;
	}
	*/
	
	$result .= '
            </td>
            '.($countFiles > PD_SLIDESHOW_DEFAULT_THUMBS_COUNT ? '
            <td id="nextSel" valign="top" style="padding-top: 12px;">
	            &nbsp;<input type="image" src="../../../common/html/res/images/film-right.gif" value=">" onClick="pd_changeThumbPage(1);">
	        </td>
            ' : '').'
    	</tr>
    	</table>
	';
    
	return $result;
}

?>