jQuery.extend(jQuery.validator.messages, {
	maxlength: jQuery.validator.format("Number of characters can not exceed {0}."),
	year: jQuery.validator.format("Incorrect date."),
	primarymessage: ["Primary name must be filled.", "At least one of the primary name fields must be filled."],
	captcha: "Incorrect code."
});


jQuery.validator.addMethod("primary", function(value, element) {
	var form = jQuery(element).parents("form.wbs-sign-up");
	if (form.find("input.primary[value!='']").length > 0) {
		form.find("input.primary.error").removeClass('error');
		form.find('div.error').hide();
		return true;
	} else {
		form.find("input.primary").addClass('error');
		var i = form.find("input.primary").length > 1 ? 1 : 0;
		var message = jQuery.validator.messages.primarymessage[i];
		form.find('div.error').html(message).show();
		return false;	
	} 
}, " "); 

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

jQuery.validator.addMethod("year", function (value, element) {
	var d = jQuery(element).prev().prev();
	var m = jQuery(element).prev();
	var r = (this.optional(element) && !d.val() && !m.val()) || (d.val() && m.val() && value.length != 3 && /^\d+$/.test(value));
	if (r) {
		d.removeClass('error'); m.removeClass('error');
	} else {
		d.addClass('error'); m.addClass('error');
	}
	return r;
}, jQuery.validator.messages.year);

var wbs_captcha_checked = false;
var wbs_captcha_valid = false;

jQuery(document).ready(function() {
	var charset = document.charset || document.characterSet;	
	jQuery("form.wbs-sign-up").each(function (){
		jQuery(this).validate({onkeyup: false, focusCleanup: true, focusInvalid: false});
		if (jQuery(this).hasClass('user-iframe')) {
			jQuery(this).find("input.source").val(document.location.host);
		} else {
			jQuery(this).find("input.source").val(parent.document.location.host);
		}
	});
	jQuery("form.wbs-sign-up input.encoding").val(charset);
	jQuery("form.wbs-sign-up input.year").blur(function () {
		if (jQuery(this).valid()) {
			var value = this.value;
			value = parseInt(value);
			if (value < 10) {
				value = 2000 + value;
			} else if (value < 100) {
				value = 1900 + value;
			}
			jQuery(this).val(value);
		}
	});

	var d = false;
	jQuery("form.wbs-sign-up").submit(function () {
		if (!jQuery(this).valid()) {
			return false;
		}
		var self = jQuery(this);
		var id = 'wbs-form-iframe-' + Math.round(Math.random * 1000000 + 1);
		var url = self.attr('action');
		if (self.find("input.captcha").length > 0 && !wbs_captcha_checked) {
			var r = function (response) {
				wbs_captcha_checked = true;
				wbs_captcha_valid = response;
				self.submit();
			};
			if (url.length) {
				jQuery.getJSON(url.replace(/show\.php/, "checkcaptcha.php") + '&code=' + self.find("input.captcha").val() +'&callback=?', r);
			} else {
				jQuery.post('checkcaptcha.php', {code: self.find("input.captcha").val()}, r, "json");
			}
			return false;
		} 
		
		if (self.hasClass('use-iframe')) {
			var iframe = jQuery('<iframe id="' + id + '" name="' + id + '"></iframe>').css('display', 'none').appendTo('body');		
			url = url.replace(/show\.php/, "session.php");
			
			self.attr('target', iframe.attr('name'));
			setTimeout(function () {
				iframe.load(function(){
					self.find("input.submit").hide();
					self.find(".form-message").show();
					self.get(0).reset();
					setTimeout(function () {
						self.find("input.submit").show();
						self.find(".form-message").hide();
						iframe.remove();
						wbs_captcha_checked = false;
						if (self.find("img.captcha").length) {
							self.find("img.captcha").attr('src', self.find("img.captcha").attr('src') + '?_=' + Math.round(Math.random * 1000000 + 1));
						}
					}, 3000);
				});
			}, 1);
		}
	});
	
});