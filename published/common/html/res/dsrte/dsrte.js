/**
 * Damn Small Rich Text Editor v0.2.4 for jQuery
 * by Roi Avidan <roi@avidansoft.com>
 * Demo: http://www.avidansoft.com/dsrte/
 * Released under the GPL License
 *
 * Damn Small Rich Text Editor Javascript implementation.
 */


var dsRTE = function( dsrteIframe ) {

    var self = this;
    
    this.isHTML = false;

    this.modified = false;
    this.iframe = dsrteIframe;
    this.frame = $.browser.msie ? frames[dsrteIframe.id] : this.iframe.contentWindow;
    this.doc = $.browser.msie ? this.frame.document: this.iframe.contentDocument;
    if ($.browser.msie) this.window = this.frame.window;
    this.preloadedHtml = $( '#'+this.iframe.id+'-ta' ).text();

    // Activate Design Mode

    this.doc.designMode = 'on';
    try {
        this.doc.execCommand( 'useCSS', false, true);
    } catch (e) {
    	
    };


    // Allow plugins to modify content before we display it
    dsRTE.CallAllPlugins( this, 'OnLoad' );

    // Create document
    this.doc.open();
    this.doc.write( this.preloadedHtml );
    this.doc.close();
    $( 'head', this.doc ).append( '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><link rel="stylesheet" href="../common/html/res/dsrte/dsrte.frame.css" style="text/css" />' );
    $( 'body', this.doc ).css( 'backgroundColor', '#ffffff' );
    this.preloadedHtml = false;

    // Add keypress event handler for shortcut keys and modified flag updates
    if ( $.browser.msie ) {
    
        this.doc.onmouseup = function(e) {        
            self.updateIERange();
        };
        
        $(this.doc).keydown(function (e) {
          if (e.keyCode == 13 && !e.shiftKey) {
        	  self.doc.execCommand('RemoveFormat', false, null );
          }
        });
        this.doc.onkeyup = function(e) {
          self.updateIERange();
        };
    } else {
   		this.doc.execCommand( 'useCSS', false, true);
        this.doc.addEventListener( 'keypress', function( e ) {
            self.OnKeyPress( e );
        }, true );
    }

    // Add Resize handler if needed
    if ( $('#'+this.iframe.id+'-resize').size() > 0 ) {
        new dsrteResizer( this );
    }

    // Activate commands on panel
    this.PreparePanel();

    // Bind to form's submit handler
    $(this.iframe).parents( 'form:first' ).submit( function() {   
        dsRTE.CallAllPlugins( self, 'OnSubmit' );
        $('#'+self.iframe.id+'-ta').text( self.getDoc() );
    } );
    
    $('#'+this.iframe.id + "-cmd a.cmd span").hover(function () {
    	if (!$(this).hasClass('disable')) {
    		$(this).addClass('hover');
    	}
    }, function () {
    	if (!$(this).hasClass('disable')) {
    		$(this).removeClass('hover');
    	}    	
    });
};

/**
 * Retrieve current editing HTML.
 *
 * @return
 *   HTML string
 */
dsRTE.prototype.getDoc = function() {

    return typeof( this.preloadedHtml ) == 'string' ? this.preloadedHtml : (this.isHTML ? $(this.doc.body).text() : $(this.doc.body).html());
};

/**
 * Set current editing HTML.
 *
 * @param String html
 *   HTML string to apply to this document
 * @return
 *   Nothing
 */
dsRTE.prototype.setDoc = function( html ) {

    if ( typeof( this.preloadedHtml ) == 'string' )
        this.preloadedHtml = html;
    else
        this.doc.body.innerHTML = html;
};

/**
 * Hide all command panels (i.e. color panel, link panel, etc.)
 *
 * @return
 *   Nothing
 */
dsRTE.prototype.hidePanels = function() {
    $( this.iframe ).parents( 'table:first' ).find( '.panel' ).slideUp();
	$("div.select-container:visible").hide();
};

/**
 * Needed for IE compatibility.
 * Thanks to Eugene Minaev <eugene20237@gmail.com> for this!
 *
 * @param Event e
 *   Window Event object
 */
dsRTE.prototype.updateIERange = function() {
    if ( this.doc.selection && this.doc.selection.createRange ) {
    	var r = this.doc.selection.createRange();
    	if (r.duplicate) {
    		this.iframe.rng = r.duplicate();
    	}
    }
};

/**
 * Keypress handler for editor.
 * This helps us execute shortcut keys on Mozilla and keep track of
 * text modification.
 *
 * @param Event e
 *   Window Event object
 */
dsRTE.prototype.OnKeyPress = function( e ) {

    // Update document modification flag
    this.modified = true;
    
    if (e.keyCode == 13) {
    	if (!e.shiftKey) {
    		if ($.browser.safari) {
    			return true;
    		}
    		this.frame.focus();
    		this.doc.execCommand('RemoveFormat', false, null );
/*
    		var rng = this.frame.getSelection().getRangeAt(0);

    		start = rng.startContainer;
			end = rng.endContainer;
			
 		    root = rng.commonAncestorContainer;
 		    var is_p = false;
 		    while (true) {
 		    	if (start.previousSibling && start.previousSibling.nodeName.toLowerCase() == 'p') {
 		    		break;
 		    	}
 		    	if (start.parentNode && start.parentNode.nodeName.toLowerCase() == 'p') {
 		    		is_p = true;
 		    	}
 		    	if (start.parentNode && start.parentNode.nodeName.toLowerCase() != 'body') {
 		    		start = start.parentNode;
 		    	} else if (start.previousSibling) {
					start = start.previousSibling;
				} else {
					break;
				}
 		    }
 		    if (start.parentNode && start.parentNode.nodeName.toLowerCase() == 'body') {
 		    	rng.setStartBefore(start);
 		    } else if (is_p) {
 		    	rng.setStart(start.parentNode, false);
 		    } else {
 		    	rng.setStart(start, false);
 		    }
    		if (end.nodeName == '#text' && end.parentNode.nodeName.toLowerCase() != 'body' && !end.nextSibling) {
    			rng.setEndAfter(end.parentNode);
    		}

    		var p = document.createElement("p");
    		if (is_p) {
    			var div = document.createElement("div");
    			div.appendChild(rng.extractContents());
    			p.innerHTML = $(div).children(p).get(0).innerHTML;;
    		} else {
	    	    p.appendChild(rng.extractContents());
    		}
    	    
    	    rng.deleteContents();
    	    var space = document.createTextNode("");
    		rng.insertNode(space);
    		rng.insertNode(p);
    		rng.selectNodeContents(space);
    		rng.collapse(true);

    		this.frame.focus();
    		
    		e.preventDefault();
    		e.stopPropagation();   		
*/    		
    	}
    }
    // Handle shortcut keys, if necessary
    if ( e.ctrlKey ) {
        var k = String.fromCharCode( e.charCode ).toLowerCase();
        var c = '';
        if ( k == ctrlb )
            c = 'bold';
        else if ( k == ctrli )
            c = 'italic';
        else if ( k == ctrlu )
            c = 'underline';
        else
            return;

        // Apply shortcut
        this.frame.focus();
        this.doc.execCommand( c, false, null );
        
        this.frame.focus();

        // Stop event propagation for this shortcut
        e.preventDefault();
        e.stopPropagation();
    }
	if (typeof(autoSave) == 'function') autoSave();
};

/**
 * Prepare the command panels - buttons, selects and special command hidden divs (i.e. for color).
 */
dsRTE.prototype.PreparePanel = function() {

    var dsrte = this;
    var id = dsrte.iframe.id;

    // Handle command buttons
    $( '#'+id+'-cmd .cmd' ).each( function() {
    
        var $this = $( this );

        // css hover fix for IE
        if ( $.browser.msie )
            $( 'div', $this ).mouseover(function() { 
            	this.className = 'hvr'; 
            }).mouseout(function() { 
            	this.className = ''; 
            });

        // Call Plugins' PrepareCommand method
        var a = $this.attr( 'args' );
        var pnl = $( '#'+id+'-'+a );
        
        if ( dsRTE.CallPlugin( dsrte, 'PrepareCommand', $this.attr( 'cmd' ), a, pnl, $this ) == false ) {
        
            // special case - Insert HTML command is not implemented as a plugin!
            if ( a == 'html' ) {
            
                $( '#'+id+'-html-ok' ).click( function() {
                
                    dsrte.frame.focus();
                    dsrte.PasteHTML( $( '#'+id+'-html-html' ).val() );
                    dsrte.frame.focus();
                    $( '#'+id+'-html' ).val( '' );
                    pnl.slideUp();
                } );
            }
        }

        // Handle command click events
        $this.click(function() {
        	
        	if ($this.children('span').hasClass('disable')) return false;

            var cmd = $this.attr('cmd');
            var args = $this.attr('args');
            var panel = $('#'+dsrte.iframe.id+'-'+args);

            // Hide all open panels
            dsrte.hidePanels();

            // Execute command
            dsrte.CommandClick( $this, cmd, args, panel );

            // Update modification flag
            dsrte.modified = true;
            return false;
        } );
    });

    // Handle comboboxes
    $( '#'+dsrte.iframe.id+'-cmd select' ).change( function() {
    		if (dsrte.isHTML) return false;
        	var $this = $(this);
        	if ( this.selectedIndex > 0 ) {
                dsrte.doc.execCommand( $this.attr( 'cmd' ), false, this.value );
                dsrte.modified = true;
            }
            this.selectedIndex = 0;
            dsrte.frame.focus();
    });
    
    // Handle div lists
    $( '#'+dsrte.iframe.id+'-cmd .select .cmd' ).click(function() {
		if (dsrte.isHTML) return false;
		dsrte.doc.execCommand($(this).attr( 'cmd' ), false, $(this).attr('title'));
		dsrte.modified = true;
		dsrte.frame.focus();

		var obj = $('#cmd-'+dsrte.iframe.id+'-'+$(this).attr( 'cmd' )+'-list li');
		obj.each( function(i) {
			obj[i].children[0].className = 'cmd';
		});
		$(this).addClass( 'selected' );
    });
    
    // Handle div lists buttons
    $( '#'+dsrte.iframe.id+'-cmd .list').click(function () {
    	if (dsrte.isHTML) return false;
    	$("#" + $(this).attr('id') + '-list').slideToggle().css('left', $(this).children('span').offset().left - 20);
    });
};

/**
 * Implement cross-browser HTML insertion.
 *
 * @param String html
 *   HTML to insert at current cursor position
 */
dsRTE.prototype.PasteHTML = function( html, type) {
	if ( $.browser.msie ) {
		if ( this.iframe && this.iframe.rng && this.iframe.rng.pasteHTML ) {
			this.iframe.rng.pasteHTML( html );
		}
    } else {
    	if ( this.doc.selection ) {
    		var rng = this.doc.selection.createRange();
    		rng.pasteHTML( html );
    	} else {
    		var rng = this.frame.getSelection().getRangeAt(0);
    		rng.deleteContents();
    		if (type == 1) {
    			var n = this.doc.createTextNode(html);
    		} else if (type == 2) {
    			var d = this.doc.createElement('div');
    			d.innerHTML = html;
    			var n = d.firstChild;
    		} else {
	    		var n = this.doc.createElement('span');
	    		n.innerHTML = html;
    		}
    		rng.insertNode(n);
    	}
    }
};

/**
 * Implement cross-browser user selection retrieval.
 *
 * @return
 *   User Selection.
 */
dsRTE.prototype.GetSelection = function() {

    var html = '';

    this.frame.focus();
    if ( $.browser.msie )
        this.iframe.rng.select();

    if ( this.doc.selection ) {
    
        var rng = this.doc.selection.createRange();
        html = rng.htmlText;
    } else {
    
        var rng = this.frame.getSelection().getRangeAt(0);
        var e = this.doc.createElement( 'div' );
        e.appendChild( rng.extractContents() );
        html = e.innerHTML;
    }

    return html;
};

/**
 * Handle command button click for built-in and plugins' commands.
 *
 * @param jQuery $this
 *   jQuery object of command div clicked
 * @param String cmd
 *   Command identifier
 * @param String args
 *   Additional command arguments
 * @param jQuery panel
 *   jQuery object of associated panel
 */
dsRTE.prototype.CommandClick = function( $this, cmd, args, panel ) {

    if ( dsRTE.CallPlugin( this, 'ExecuteCommand', cmd, args, panel ) == false ) {

		switch ( args ) {

			// Switch editor's text to normal text (show HTML tags)
            case 'text':
            	this.isHTML = true;
                $this.attr( 'args', 'wysiwyg');
                $("#" + this.iframe.id + "-cmd a.cmd span").addClass('disable');
                $this.children('span').removeClass().addClass('state');
                var html = $(this.doc.body).html();
                html = html.replace(/<br>/gi, "<br />\n");
                html = html.replace(/(<\/p>)/gi, "$1\n");
                html = $("<div></div>").text(html).html();
                html = html.replace(/\n/g, "<br />");
                $(this.doc.body).html(html);
                break;

            // Switch editor's text to WYSIWYG view (HTML view)
            case 'wysiwyg':
            	this.isHTML = false;
                $this.attr( 'args', 'text' );
                $("#" + this.iframe.id + "-cmd a.cmd span").removeClass('disable');
                $this.children('span').removeClass();
                var html = $(this.doc.body).text();
                $(this.doc.body).html(html);
                break;

            // Show Insert HTML panel
            case 'html':
                panel.slideToggle();
                break;

            case 'image':
               	break;
            // Execute internal browser command
            default:
                if ( $.browser.msie && this.iframe.rng)
                    this.iframe.rng.select();
                this.doc.execCommand( cmd, false, '' );
                this.frame.focus();
                break;
        }
    }
};

/**
 * Register a new Plugin class with dsRTE.
 * It is recommended to call this function before creating a dsRTE instance so the OnLoad event
 * may also be called.
 *
 * @param Object pluginObj
 *   Plugin instance.
 * @param String command
 *   command related to this plugin
 */
dsRTE.RegisterPlugin = function( pluginObj, command ) {

    if ( dsRTE.plugins == null )
        dsRTE.plugins = {};

    dsRTE.plugins[command] = pluginObj;
};

/**
 * Internal use.
 * Call a plugin's method to perform a specific action.
 * If the Plugin implements the called function, it MUST RETURN TRUE!
 * Otherwise, processing will continue and the results may be undefined.
 *
 * @param Object dsrte
 *   dsRTE object
 * @param String func
 *   Plugin function to execute
 * @param String command
 *   Command to execute (plugin-registered)
 * @param String arguments
 *   Command argumetns to pass to plugin
 * @param jQuery panel
 *   jQuery object of associated command panel (only for PrepareCommand call)
 * @param jQuery $this
 *   jQuery object of command div
 * @return
 *   True if command was handled, False otherwise
 */
dsRTE.CallPlugin = function( dsrte, func, command, arguments, panel, $this ) {

    return (dsRTE.plugins != null) && (dsRTE.plugins[command] != null) && (dsRTE.plugins[command][func] != null) && (dsRTE.plugins[command][func]( dsrte, arguments, panel, $this ) == true);
};

/**
 * Call all registered plugins to perform some action.
 *
 * @param Object dsrte
 *   dsRTE object
 * @param String func
 *   Plugin function to execute
 * @param String arguments
 *   Command arguments to pass to plugin
 * @return
 *   Nothing
 */
dsRTE.CallAllPlugins = function( dsrte, func, arguments ) {

    if ( dsRTE.plugins != null ) {
    
        $.each( dsRTE.plugins, function( cmd, obj ) {
        
            if ( obj[func] != null )
                obj[func]( dsrte, arguments );
        } );
    }
};

/**
 * Bind all the document's RTEs.
 */
var dsRTEObj = new Array();
var iframeObj = new Array();

$(document).ready(function () {
	iframeObj = $('table.rte iframe:visible');
	iframeObj.each( function(i) {
		if (!$(this).parents('.content').is(":hidden")) {
			dsRTEObj[i] = new dsRTE( this );
		}
	});
	// Hide all open panels
	iframeObj.contents().click(function(i) {
		$('.panel').hide();
    	$("div.select-container:visible").hide();
	});	
});
$(document).click(function () {
	var obj = $('.panel');
	obj.each( function(i) {
		if(
			(iframeObj[0] && obj[i].id != iframeObj[0].id + '-link' )
		&&
			(iframeObj[0] && obj[i].id != iframeObj[0].id + '-insertvar')
		) {
			$('#' + obj[i].id).hide();
		}
	});
});
$(document).keypress(function (e) {
	if(e.which == 13) {
		if($('#' + iframeObj[0].id + '-link').css('display') != 'none') {
			$( '#'+ iframeObj[0].id +'-link-ok' ).click();
			return false;
		}
	}
});
