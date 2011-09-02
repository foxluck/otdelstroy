/**
 * Damn Small Rich Text Editor v0.2.4 for jQuery
 * by Roi Avidan <roi@avidansoft.com>
 * Demo: http://www.avidansoft.com/dsrte/
 * Released under the GPL License
 *
 * Sample SPAN injection Plugin
 */


var dsRTE_insertVar = function() {

    /**
     * Execute Plugin.
     */
    this.ExecuteCommand = function( dsrte, arguments, panel ) {

		var pos = $('#cmd-' + dsrte.iframe.id + '-insertvar span').position().left;
		$('#' + dsrte.iframe.id + '-insertvar').width(320);
		$('#' + dsrte.iframe.id + '-insertvar').css('left', (pos - 340 + $('#cmd-' + dsrte.iframe.id + '-insertvar span').width()) + 'px');

        panel.slideToggle();
        return true;
    };

    /**
     * Prepare Plugin.
     * Attach a Click handler on the Ok button
     */
    this.PrepareCommand = function( dsrte, arguments, panel, $this ) {
        var args = arguments;
        
    	$( '#'+dsrte.iframe.id+'-'+arguments+'-content a' ).click(
	        	function(){
	        		var variable = $('span', this).html();
	                dsrte.PasteHTML( variable, 1 );
	        		
	        	}
        	);
    	
        // signal callback was successfully processed.
        return true;
    };
};
// Register new plugin with dsRTE
dsRTE.RegisterPlugin( new dsRTE_insertVar(), 'insertvar' );

