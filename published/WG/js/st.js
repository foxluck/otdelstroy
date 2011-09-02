jQuery().ajaxComplete(function(request, settings){
return false;
});

jQuery.extend(jQuery.validator.messages, {
	maxlength: jQuery.validator.format("Number of characters can not exceed {0}."),
	year: jQuery.validator.format("Incorrect date."),
	primarymessage: ["Primary name must be filled.", "At least one of the primary name fields must be filled."],
	captcha: "Incorrect code."
});
	
jQuery.validator.addMethod("captcha", function (value, element) {
	var r = !wbs_captcha_checked || wbs_captcha_valid;
	if (wbs_captcha_valid === 0) {
		wbs_captcha_valid = false;
	} else if (wbs_captcha_valid == false) {
		wbs_captcha_checked = false;
	} else {
		wbs_captcha_valid = true;
	}
	return r;
}, jQuery.validator.messages.captcha);

var wbs_captcha_checked = false;
var wbs_captcha_valid = false;
if (typeof(wbsFormSubmitCallback) != "function"){
	wbsFormSubmitCallback = function(){
		jQuery('.wbs-st-form-msg').html(jQuery('.wbs-st-form-msg').html().replace('%EMAIL%',jQuery('input.email').val()));
		jQuery('.st-request-form table').after(jQuery('.wbs-st-form-msg'));
		jQuery('.st-request-form table').hide();
		var REDIRECT = jQuery('#REDIRECT').val();
		if (REDIRECT == ''){
			jQuery('.wbs-st-form-msg').show()
		} else {
			if (jQuery('#NEWWINDOW').val() == '1'){
				if (parent){
				    parent.window.open(REDIRECT,"stformwindow");
				} else {
				    window.open(REDIRECT,"stformwindow"); 
				}
				jQuery('.wbs-st-form-msg').show();
			} else {
				if (parent){
				    parent.location.href = REDIRECT;
				} else {
				    location.href = REDIRECT;
				}
			}
		}
		jQuery('.wbs-st-form-msg').show();
	}
}
var session_id = 0;
jQuery(document).ready(function() {		
	var charset = document.charset || document.characterSet;	
	jQuery("form.wbs-st-form").each(function (){
		jQuery(this).validate({onkeyup: false, focusCleanup: true, focusInvalid: false});
		jQuery(this).find("input.charset").val(charset);			
		jQuery(this).find("input.source").val(parent ? parent.window.location.href : location.href);
		var self = $(this);
		if (self.find("input.captcha").length > 0) {
			url = self.attr('action').replace(/widget.php\?.*/, '');
			if (url.length) {
				jQuery.getJSON(url + "checkcaptcha.php?code=0&callback=?", function (response) {
					session_id = response;
					self.find("img.captcha").attr('src', url + "captcha.php?s=" + session_id).show();
				});
			} else {
				jQuery.post("checkcaptcha.php", {code:"0"}, function (response) {
					self.find("img.captcha").attr('src', "captcha.php").show();
				});			
			}		
		}		
	});
	jQuery("input.charset, input#CHARSET").val(charset);
	jQuery("input.charset, input#CHARSET").attr('name','charset');

	var d = false;
	jQuery("form.wbs-st-form").submit(function () {
		if (!jQuery(this).valid()) {
			return false;
		}
		var self = jQuery(this);
		var id = 'wbs-form-iframe-' + Math.round(Math.random() * 1000000 + 1);
		var url = self.attr('action');
		
		var submitByIframe = function(){
			var iframe = jQuery('<iframe id="' + id + '" name="' + id + '"></iframe>').css('display', 'none').appendTo('body');		
			url = url.replace(/widget\.php/, "session.php");
			
			self.attr('target', iframe.attr('name'));
			setTimeout(function () {
				iframe.load(function(){
					self.find("input.submit").hide();
					//self.submit();
					wbsFormSubmitCallback();
					self.get(0).reset();
					setTimeout(function () {
						self.find("input.submit").show();
						iframe.remove();
						wbs_captcha_checked = false;
						if (self.find("img.captcha").length) {
							wbs_captcha_checked = false;
							wbs_captcha_valid = false;							
							self.find("img.captcha").attr('src', self.find("img.captcha").attr('src') + '&_=' + Math.round(Math.random() * 10000 + 1));
						}
					}, 1000);
				});
				//self.submit();
			}, 100);
		} 
		
		if (self.find("input.captcha").length > 0 && !wbs_captcha_checked) {
			var r = function (response) {
				wbs_captcha_checked = true;
				wbs_captcha_valid = response;
				if (!wbs_captcha_valid) {
					self.valid();
				} else {
					self.submit();
					// jQuery.post(url, self.serialize(), wbsFormSubmitCallback);
				}
				//self.submit();
			};
			if (url.length) {
			//console.log(url.replace(/widget\.php/, "checkcaptcha.php") + '&code=' + self.find("input.captcha").val() +'&callback=?');
				jQuery.getJSON(url.replace(/widget\.php/, "checkcaptcha.php") + '&code=' + self.find("input.captcha").val() +'&s=' + session_id + '&callback=?', r);
			} else {
				jQuery.post('checkcaptcha.php', {code: self.find("input.captcha").val()}, r, "json");
			}
			return false;
		} else {
			submitByIframe()
			//if (!self.find("input.captcha").length)
			//	jQuery.post(url, self.serialize(), wbsFormSubmitCallback);
		}
	});
	
});
