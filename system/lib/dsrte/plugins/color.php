<?php
/**
 * Damn Small Rich Text Editor v0.2.3 for jQuery
 * by Roi Avidan <roi@avidansoft.com>
 * Demo: http://www.avidansoft.com/dsrte/
 * Released under the GPL License
 *
 * Color and Background Color commands class.
 */

class dsRTEColorPlugin extends dsRTECommandButton
{
    private static $scripted = false;
    
    // Color table for Foreground and Background colors
    private static $colors = array(
		"#ffffff","#c0c0c0","#999999","#666666","#000000",
		"#ffcccc","#ff0000","#cc0000","#990000","#330000",
		"#ffcc99","#ff9900","#ff6600","#cc6600","#663300",
		"#ffffcc","#ffff00","#ffcc00","#999900","#333300",
		"#99ff99","#33ff33","#33cc00","#009900","#003300",
		"#99ffff","#66cccc","#00cccc","#339999","#003333",
		"#ccffff","#33ccff","#3366ff","#3333ff","#000066",
		"#ccccff","#6666cc","#6633ff","#6600cc","#330099",
		"#ffccff","#cc66cc","#cc33cc","#993399","#330033"
    );

    // The color table should only appear once as a hidden div for both FG and BG commands!
    private static $panel_already_created = array();

    /**
     * This command has a special hidden div with the color table.
     * Create that DIV (only once!) and return it's HTML.
     */
    public function getPanelHTML()
    {
        $html = '';
        if ( !isset( self::$panel_already_created[$this->id] ) )
        {
            self::$panel_already_created[$this->id] = true;

            $html = '<div class="rte panel" id="'.$this->id.'-color">';
            $html .= '<table border="0" cellspacing="1" cellpadding="0" id="'.$this->id.'-color-table" class="color-table">';
            for ( $i = 0; $i < count( self::$colors ); $i++ )
            {
                if ( $i % 5 == 0 )
                    $html .= '<tr>';
                $html .= '<td bgcolor="'.self::$colors[$i].'"></td>';
                if ( ($i+1) % 5 == 0 )
                    $html .= '</tr>';
            }
            $html .= '</table>';
            $html .= '</div>';
        }

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
            '<script type="text/javascript" src="../common/html/res/dsrte/plugins/color.js"></script>',
        ) );
    }
}

?>