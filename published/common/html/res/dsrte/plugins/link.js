/**
 * Damn Small Rich Text Editor v0.2.4 for jQuery
 * by Roi Avidan <roi@avidansoft.com>
 * Demo: http://www.avidansoft.com/dsrte/
 * Released under the GPL License
 *
 * Insert Link plugin.
 */

var dsRTE_insertLink = function() {

    /**
     * Execute Plugin.
     */
    this.ExecuteCommand = function( dsrte, arguments, panel ) {

		var pos = $('#cmd-' + dsrte.iframe.id + '-link span').position().left;
		$('#' + dsrte.iframe.id + '-link').width(270);
		$('#' + dsrte.iframe.id + '-link').css('left', pos + 'px');


		var rng = dsrte.frame.getSelection().getRangeAt(0);
		if(rng.startContainer.nodeName == '#text' && rng.startContainer.parentNode.nodeName.toLowerCase() == 'a') {

			if(typeof(rng.moveToElementText) == 'function') { // ie ??? see dsrte.js GetSelection()
		//		var rng = document.body.createTextRange();
				rng.moveToElementText(rng.startContainer);
				rng.select();
			} else {
				$('#' + dsrte.iframe.id + '-link-url').val(rng.startContainer.parentNode);
				rng.selectNodeContents(rng.startContainer);
			}
			$( '#'+dsrte.iframe.id+'-unlink-ok' ).show();
		} else {
			$('#' + dsrte.iframe.id + '-link-url').val('');
			$( '#'+dsrte.iframe.id+'-unlink-ok' ).hide();
		}

        panel.slideToggle();
        return true;
    };

    /**
     * Prepare Plugin.
     * Attach a Click handler on the Ok button
     */
    this.PrepareCommand = function( dsrte, arguments, panel, $this ) {
    
        var args = arguments;
        $( '#'+dsrte.iframe.id+'-'+arguments+'-ok' ).click( function() {

            var url = $( '#'+dsrte.iframe.id+'-'+args+'-url' ).val().replace(/^\s*(.*?)\s*$/,'$1');
            var tgt = $( '#'+dsrte.iframe.id+'-'+args+'-target' ).val();
			
            if ( url ) {
				if (!/mailto\:/i.test(url)) {
					if (/[\.\-_a-z0-9]+?@[\.\-a-z0-9]+?\.[a-z0-9]{2,}/i.test(url)) {
						url = 'mailto:' + url;
					} else if (!/^\s*https?:\/\//i.test(url)) {
						url = 'http://' + url;
					}
				}
				dsrte.doc.execCommand( 'unlink', false, '' );
                var html = dsrte.GetSelection().replace(/^\s*(.*?)\s*$/,'$1');
				html = html ? html : url;
                dsrte.PasteHTML( '<a href="'+url+'"'+(tgt ? ' target="'+tgt+'"' : '')+'>'+html+'</a>' );
            } else {
				dsrte.doc.execCommand( 'unlink', false, '' );
			}

            panel.slideUp();
        } );

        $( '#'+dsrte.iframe.id+'-unlink-ok a' ).click( function() {
			dsrte.doc.execCommand( 'unlink', false, '' );
            panel.slideUp();
        } );

        // signal callback was successfully processed.
        return true;
    };
};

// Register new plugin with dsRTE
dsRTE.RegisterPlugin( new dsRTE_insertLink(), 'link' );

