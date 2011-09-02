<?php
/**
 * Damn Small Rich Text Editor v0.2.3 for jQuery
 * by Roi Avidan <roi@avidansoft.com>
 * Demo: http://www.avidansoft.com/dsrte/
 * Released under the GPL License
 *
 * Image Upload Command class.
 */

class dsRTEImagePlugin extends dsRTECommandButton
{
    private static $scripted = false;
    
    /**
     * Prepare the special hidden div for this command with a file browse and upload buttons.
     */
    public function getPanelHTML()
    {
        $this->attributes[] = '"path":"/dsrte/uploadhandler.php"';

        $html = '<div class="rte panel" id="'.$this->id.'-'.$this->arguments.'">';
        $html .= _s( 'Image' ).': ';
        $html .= '<input type="file" size="25" id="'.$this->id.'-'.$this->arguments.'-file" name="'.$this->arguments.'-file" />';
        $html .= '<input type="button" id="'.$this->id.'-'.$this->arguments.'-ok" value="'.t( 'Upload' ).'" />';
        $html .= '<input type="button" value="'.t( 'Cancel' ).'" onclick="$(\'#'.$this->id.'-'.$this->arguments.'\').slideUp()" />';
        $html .= '</div>';

        return $html;
    }
    
    public function getHTML()
    {
        $html = '<a class="cmd" id="cmd-'.$this->id.'-'.$this->command.'" title="'.$this->title
			.'">';
        if ( is_numeric( $this->icon_offset ) )
            $html .= '<span style="background-position:-'.$this->icon_offset.'px 0px"></span>';
        else
            $html .= '<span style="background:url('.$this->icon_offset.') no-repeat 0 0"></span>';

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
            '<script type="text/javascript" src="../common/html/res/dsrte/plugins/image.js"></script>',
        ) );
    }
}

?>