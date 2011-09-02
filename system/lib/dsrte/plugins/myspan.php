<?php
/**
 * Damn Small Rich Text Editor v0.2.3 for jQuery
 * by Roi Avidan <roi@avidansoft.com>
 * Demo: http://www.avidansoft.com/dsrte/
 * Released under the GPL License
 *
 * Sample SPAN injection Command class.
 */

class dsRTEMySpanPlugin extends dsRTECommandButton
{
    private static $scripted = false;
    
    /**
     * Default Constructor.
     */
    public function __construct()
    {
        parent::__construct( 'myspan', 'myspan', '', t( 'Insert My SPAN' ), '../common/html/res/dsrte/plugins/myspan.gif' );
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
            '<script type="text/javascript" src="../common/html/res/dsrte/plugins/myspan.js"></script>',
        ) );
    }
}

// Add this plugin to the editor
dsRTE::RegisterPlugin( 'myspan', new dsRTEMySpanPlugin() );

?>