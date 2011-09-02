/**
 * Damn Small Rich Text Editor v0.2.4 for jQuery
 * by Roi Avidan <roi@avidansoft.com>
 * Demo: http://www.avidansoft.com/dsrte/
 * Released under the GPL License
 *
 * Image Upload Plugin.
 * Fixes for IE courtesy of Eugene Minaev <eugene20237@gmail.com>
 */

var dsRTE_insertImage = function() {

    /**
     * Execute Plugin.
     * Show the hidden panel.
     */
    this.ExecuteCommand = function( dsrte, arguments, panel ) {
        return false;
//        panel.slideToggle();
//        return true;
    };

    /**
     * Prepare Plugin.
     * Basically we need to attach a click handler on the Upload button.
     */
    this.PrepareCommand = function( dsrte, arguments, panel, $this ) {
    
        var args = arguments;        
        $('#'+dsrte.iframe.id+'-'+arguments+'-ok').click( function() {        
            dsrte.frame.focus();
        } );
        
    	var mid = $('#mid').val();

    	new Ajax_upload('#cmd-' + dsrte.iframe.id + '-image', {

    		hover: function () {
    			$('#cmd-' + dsrte.iframe.id + '-image').parent().prev().prev().children('span').removeClass('hover');
    			$('#cmd-' + dsrte.iframe.id + '-image span').addClass('hover');
    			$('#cmd-' + dsrte.iframe.id + '-image').parent().next().next().children('span').removeClass('hover');
    		},
    		unhover: function () {
    			$('#cmd-' + dsrte.iframe.id + '-image span').removeClass('hover');
    		},
    		action: 'index.php?mod=users&act=email&uploadimage=1&mid=' + mid + '&ajax=1',

    		onSubmit : function(file , ext){
    			if(!ext || !(/^(jpg|png|jpeg|gif)$/.test(ext))){
    				alert('[`Error: only images are allowed`]');
    				return false;
    			}
    		},
    		width: 25,
    		height: 25,
    		onComplete : function(file, response){
    			try {
    				var response = eval(response);
    			} catch (e) {
    				return false;
    			}
    			if(response) {
    				path = response[0].replace(/&amp;/gi, '&');
    				 if ( $.browser.msie && dsRTEObj[0].iframe.rng)
    					 dsRTEObj[0].iframe.rng.select();    				
    				dsRTEObj[0].frame.focus();
    				dsRTEObj[0].doc.execCommand('insertimage', false, path);
    				if(response[1]) {
    					$('#mid').val(response[1]);
    				}
    			}
    		}		
    	});
        
        

        // signal callback was successfully processed.
        return true;
    };
};

// Register new plugin with dsRTE
dsRTE.RegisterPlugin( new dsRTE_insertImage(), 'image' );

