/**
  * @name  wbsPopup
  * @type  jQuery
  * @param String  target             	div
  * @param Hash    options            	additional options
  * @param Iteger  options[width]  	
  * @param Iteger  options[height]
  * @param String  options[backgroundColor] background css background-color
  * @param Float  options[opacity]
  * @param String  options[url]  url content
  * @param Function  options[loadComplite]  callback
  * @param Function  options[hidePopup]  callback
  * @param Hash  options[callback]  object Functions to callback (iframe use)
  */
(function($) {
	
	$.fn.wbsPopup = function(options) {
		
		var settings = {
            width		: 500,
            height		: 400,
            backgroundColor: '#000000',
            opacity		: 0.5,
            iframe		: false,
            close		: ''
        };
        
        if(options) {
            $.extend(settings, options);
        }
        
        var loadComplite = settings.loadComplite || function() {};
        var hidePopup = settings.hidePopup || function() {};
        var callback = settings.callback || {};
        
        return this.each(function() {
        	if ( !settings.iframe ) {
        		$(this).empty();
        	}
        	var $target = $(this);

        	var $body =  $('body');
    	
		    $(window).keydown(function(e){		 
		    	if (e.keyCode == 27) {
					if ( settings.iframe)
						window.closePopup(false);
					else { 
						$target.wbsPopupClose();
					}
		    	}
		    });
        	
        	var $background = $("<div id='wbs-popup-bg'></div>");
        	$body.append($background);
        	$background.css({
        		opacity: settings.opacity,
        		'z-index': 500,
        		position: 'absolute',
        		top: 0,
        		left: 0,
				height: '100%',
				width: '100%',
				'background-color': settings.backgroundColor
        	});
        	
        	$target.css({
        		marginLeft: '-' + parseInt((settings.width / 2),10) + 'px', 
        		width: settings.width + 'px',
        		position: 'absolute',
        		display: 'block',
        		height: settings.height,
        		width : settings.width,
        		left: '50%',
        		top: '50%',
        		'z-index': 502        		
        	});
			if ( !(jQuery.browser.msie)) { // take away IE6
				$target.css({
					marginTop: '-' + parseInt((settings.height / 2), 10) + 'px'
				});
			}
						
			if (settings.url) {
				if ( !settings.iframe ) {
					$target.load( settings.url, function(){
						
						if ( !this.settings.close == '' )							
							$(this.self).append('<a href="#" id="popupclose" onclick="$(this).parent().wbsPopupClose(); $(this).remove(); return false;" >'+this.settings.close+'</a>');
							
						this.loadComplite();
					}.bind({
						self: this,
						settings: settings,
						loadComplite: loadComplite
					}) );
				}
				else {
					$target.hidePopup = hidePopup;
					$target.loadComplite = loadComplite;
					$target.children().not('#popup').remove();
					var $iframe = $('<iframe id="popup_iframe" src="'+settings.url+'" style="width:100%; height:100%;"/>').appendTo($target);
					
					window.closePopup = function(isReload, id){
						var isReload = (isReload == undefined) ? true : isReload;
						this.wbsPopupClose();
						if (isReload)
							this.hidePopup(id);
							
						//this.loadComplite(id);
					}.bind($target);
					
					var self = this;
					var isFrist = true;
					var dd_ = loadComplite;
					$iframe.load(function(){
						if ( isFrist ) {
							isFrist = false;
							
							$iframe.contents().find('form').submit(function(){
								$iframe.hide();
								$('#progress').show();
							});
							
							return;
						}
						var response = $iframe.contents().find('body').html();
						if ( response.length > 0 )						
						try {
							var response = eval("(" + response + ")");
						} catch (e) {}
						
						dd_.apply(self, [response]);
						return true;
					});
				}
			}
			else {
				if ( !settings.close == '' )
					$(this).append('<a href="#" id="popupclose" onclick="$(this).parent().wbsPopupClose(); $(this).remove(); return false;" >'+settings.close+'</a>');
			}

			$target.show();	
        });
	};
	
	$.fn.wbsPopupRender = function( notBindEvent ) {		
		return this.each(function() {
			var $target = $(this);
			$target.css({
        		marginLeft: '-' + parseInt( ($(this).width() / 2), 10) + 'px',
        		marginTop: '-' + parseInt( ($(this).height() / 2), 10) + 'px'
        	});
        	if ( !notBindEvent )
        	$(window).resize(function(){
        		$target.wbsPopupRender( true );
        	});
        	
		});
	};
	
	$.fn.wbsPopupClose = function(target, options) {
		return this.each(function() {
			$(this).children('iframe').remove();
			$('#popup_iframe').remove();
			$('#wbs-popup-bg').remove();
			$(this).hide();
		});
	};
	
})(jQuery);