<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     pd_shareControlPanel
 * Purpose:  Declares big image panel payout 
 * -------------------------------------------------------------
 */

function smarty_block_pd_shareControlPanel( $params, $content, &$smarty, &$repeat )
{
    $pdStrings = $smarty->get_template_vars('pdStrings');
    $slideShowURL = $smarty->get_template_vars('slideShowURL');
    $showThums = $smarty->get_template_vars('SHOW_THUMB_PANEL');
    $middleSize = $smarty->get_template_vars('middleSize');
    
    $result = '<div id="ControlDiv">';
    $i = 0;
    $countFiles = count($params["filesList"]);
    foreach ($params["filesList"] as $curFile) {
        $display = 'block';
        if ($i > 0) $display = "none";
        
        $result .= '
            <div id="fileDesc_'.$i.'" style="width: 100%; display: '.$display.';">
                <table cellpadding="0" cellspacing="3" border="0">
                <tr>
                    <td class="fileTitle" align="left">
                        '.$curFile["fileTitle"].'
                    </td>
                    <td class="fileTitle1" align="center">
                        &nbsp;('.($i + 1).' '.$pdStrings["add_screen_of"].' '.$countFiles.')
                    </td>
                </tr>
                </table>
            </div>
        ';
        $i ++;
    }
    
    $result .= '
        <script>
            var playButImg = new Image();
            playButImg.src = "../../../common/html/res/images/slideshow/play.gif";
            
            var pauseButImg = new Image();
            pauseButImg.src = "../../../common/html/res/images/slideshow/pause.gif";
            
            var nextButImg = new Image();
            nextButImg.src = "../../../common/html/res/images/slideshow/next.gif";
            
            var nextButImgDisabled = new Image();
            nextButImgDisabled.src = "../../../common/html/res/images/slideshow/next-dis.gif";
            
            var prevButImg = new Image();
            prevButImg.src = "../../../common/html/res/images/slideshow/prev.gif";
            
            var prevButImgDisabled = new Image();
            prevButImgDisabled.src = "../../../common/html/res/images/slideshow/prev-dis.gif";
            
            var firstButImg = new Image();
            firstButImg.src = "../../../common/html/res/images/slideshow/first.gif";
            
            var firstButImgDisabled = new Image();
            firstButImgDisabled.src = "../../../common/html/res/images/slideshow/first-dis.gif";
            
            var lastButImg = new Image();
            lastButImg.src = "../../../common/html/res/images/slideshow/last.gif";
            
            var lastButImgDisabled = new Image();
            lastButImgDisabled.src = "../../../common/html/res/images/slideshow/last-dis.gif";
        </script>
        <table width="100%" cellpadding="0" cellspacing="3" border="0" class="desk">
        <form>
        <tr>
            <td width="20%" class="fileTitle1" align="center">
                &nbsp;
            </td>
            <td align="center" rowspan="2">
                <table cellpadding="0" cellspacing="0">
                <tr>
                    <td>&nbsp;<input type="image" alt="'.$pdStrings['pd_slideshow_first_label'].'" id="firstBut" onClick="if (!isPlayStart) { goFirst(); }; return false;" src="../../../common/html/res/images/slideshow/first.gif">&nbsp;</td>
                    <td>&nbsp;<input type="image" alt="'.$pdStrings['pd_slideshow_prev_label'].'" id="incBut" onClick="if (!isPlayStart) { goToImage(-1); }; return false;" src="../../../common/html/res/images/slideshow/prev.gif">&nbsp;</td>
                    
                    <td>&nbsp;<input type="image" alt="'.$pdStrings['pd_slideshow_play_label'].'" id="playBut" onClick="onPlayClick(); return false;" src="../../../common/html/res/images/slideshow/play.gif">&nbsp;</td>
                    
                    <td>&nbsp;<input type="image" alt="'.$pdStrings['pd_slideshow_next_label'].'" id="decBut" onClick="if (!isPlayStart) { goToImage(1); }; return false;" src="../../../common/html/res/images/slideshow/next.gif">&nbsp;</td>
                    <td>&nbsp;<input type="image" alt="'.$pdStrings['pd_slideshow_last_label'].'" id="lastBut" onClick="if (!isPlayStart) { goLast(); }; return false;" src="../../../common/html/res/images/slideshow/last.gif">&nbsp;</td>
                </tr>
                </table>
            </td>
            <td width="20%" class="fileTitle1" align="center">
                '.$pdStrings['pd_slideshow_size_label'].' 
                    <select id="selSizeSelect" onchange="changeImagesSize(this.value);">
                        <option value="'.PD_SMALL_THUMB_SIZE.'" '.($middleSize == PD_SMALL_THUMB_SIZE ? 'selected' : '').'>'.$pdStrings['pd_slideshow_small_label'].'</option>
                        <option value="'.PD_MEDIUM_THUMB_SIZE.'" '.($middleSize == PD_MEDIUM_THUMB_SIZE ? 'selected' : '').'>'.$pdStrings['pd_slideshow_medium_label'].'</option>
                        <option value="'.PD_LARGE_THUMB_SIZE.'" '.($middleSize == PD_LARGE_THUMB_SIZE ? 'selected' : '').'>'.$pdStrings['pd_slideshow_large_label'].'</option>
                    </select>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="fileTitle1" align="right" style="padding-top: 5px;">
                <a href="javascript: void(0);" id="filmSelector" onClick="showFilmPanel();">
                '.(!$showThums ? $pdStrings['pd_slideshow_show_thumbs_label'] : $pdStrings['pd_slideshow_hide_thumbs_label']).'
                </a>
            </td>
        </tr>
        </form>
        </table>
    ';
    
    $result .= '</div>';
    
	return $result;
}

?>