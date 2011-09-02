<?php
/**
 * Damn Small Rich Text Editor v0.2.3 for jQuery
 * by Roi Avidan <roi@avidansoft.com>
 * Demo: http://www.avidansoft.com/dsrte/
 * Released under the GPL License
 *
 * Insert Link command class.
 */

class dsRTELinkPlugin extends dsRTECommandButton
{
    private static $scripted = false;
    
    /**
     * Prepare the Link command's special hidden div with a Target and URL fields.
     */
    public function getPanelHTML()
    {
        $html = '<div class="rte panel" id="'.$this->id.'-'.$this->arguments.'">';
        $html .= '<b>'.t( 'Edit link' ).'</b>';
        $html .= '<p>'.t( 'Enter web or email address' ).':<br />';
        $html .= '<input id="'.$this->id.'-'.$this->arguments.'-url" style="width:97%;" /></p>';
        $html .= '<p><input type="button" id="'.$this->id.'-'.$this->arguments.'-ok" value="'.t( 'Save' ).'" />';
        $html .= '<input type="button" value="'.t( 'Cancel' ).'" onclick="$(\'#'.$this->id.'-'.$this->arguments.'\').slideUp()" />';
        $html .= ' <span id="'.$this->id.'-unlink-ok" style="display:none;">'.t( 'or' );
		$html .= ' <a href="#" style="display:inline">'.t( 'remove this link' ).'</a></span></p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * This plugin requires additional JavaScript files to operate.
     * Return them for inclusion.
     */
    public function getScripts()
    {
        if ( self::$scripted )
            return '';
            
        self::$scripted = true;
        return implode( "\n", array(
            '<script type="text/javascript" src="../common/html/res/dsrte/plugins/link.js"></script>',
        ) );
    }
}

?>