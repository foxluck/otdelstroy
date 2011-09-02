function open_printable_version(link) // opens new window
{
	var win = "menubar=no,location=no,resizable=yes,scrollbars=yes";
	newWin = window.open(link, 'printableWin', win);
	if(newWin){newWin.focus();}
}

function confirmUnsubscribe() // unsubscription confirmation
{

	temp = window.confirm(translate.cnfrm_unsubscribe);
	if (temp) // delete
	{
		window.location = "index.php?killuser=yes";
	}

}

function validate() // newsletter subscription form validation
{
	if (document.subscription_form.email.value.length < 1) {
		alert(translate.err_input_email);
		return false;
	}
	if (document.subscription_form.email.value == 'Email') {
		alert(translate.err_input_email);
		return false;
	}
	return true;
}
function validate_disc() // review form verification
{
	if (document.formD.nick.value.length < 1) {
		alert(translate.err_input_nickname);
		return false;
	}

	if (document.formD.topic.value.length < 1) {
		alert(translate.err_input_message_subject);
		return false;
	}

	return true;
}
function validate_search() {

	if (document.Sform.price1.value != ""
			&& ((document.Sform.price1.value < 0) || isNaN(document.Sform.price1.value))) {
		alert(translate.err_input_price);
		return false;
	}
	if (document.Sform.price2.value != ""
			&& ((document.Sform.price2.value < 0) || isNaN(document.Sform.price2.value))) {
		alert(translate.err_input_price);
		return false;
	}

	return true;
}

function validate_input_digit(e) {
	var keynum;
	var keychar;
	var numcheck;
	try {
		if (window.event) // IE
		{
			keynum = window.event.keyCode;
		} else if (e.which) // Netscape/Firefox/Opera
		{
			keynum = e.which;
		}
	} catch (exception) {
		alert(exception.message);
	}

	keynum = parseInt(keynum);
	if (keynum == 13)
		return false;// ignore Enter
	if (keynum >= 33 && keynum <= 40)
		return true;// skip navigation buttons
	if (keynum == 8)
		return true;// skip backspace
	if (keynum == 17)
		return true;// skip ctrl
	if (keynum == 45)
		return true;// skip insert
	if (keynum == 46)
		return true;// skip delete
	if (keynum >= 96 && keynum <= 105) {
		keynum -= 48;
	}
	keychar = String.fromCharCode(keynum);
	// alert('char '+keychar+'\n code '+keynum);
	numcheck = /\d/;
	return numcheck.test(keychar);
	var res = numcheck.test(keychar);
	// if(!res)alert('char '+keychar+'\n code '+keynum);
	return res;
}

Behaviour.register({

	'input.input_message' : function(e) {

		e.onfocus = function() {

			this.className = this.className.replace(/input_message/, '')
					+ ' input_message_focus';

			var value = this.getAttribute('rel');
			if(!value){
				value = this.getAttribute('title');
			}
			if (this.value != value)
				return;
			this.value = '';
		}

		e.onblur = function() {
			if (this.value != '')
				return;
			this.className = this.className.replace(/input_message_focus/, '')
					+ ' input_message';
			var value = this.getAttribute('rel');
			if(!value){
				value = this.getAttribute('title');
			}
			this.value = value;
		}
	},
	'.add2cart_handler' : function(element) {

		element.onclick = function() {

			var objForm = getFormByElem(this);
			if (!objForm)
				return true;

			var r_productParam = getElementsByClass('product_option', objForm);

			var query = '';
			for (var i = r_productParam.length - 1; i >= 0; i--) {

				if (!parseInt(r_productParam[i].value))
					continue;

				if (r_productParam[i].name)
					query += '&' + r_productParam[i].name + '='
							+ parseInt(r_productParam[i].value);
			}

			var r_productQty = getElementByClass('product_qty', objForm);
			if (r_productQty) {
				r_productQty = parseInt(r_productQty.value);
				if (r_productQty > 1) {
					query += '&product_qty=' + r_productQty;
				}
			}

			var url = ORIG_LANG_URL
					+ set_query('?ukey=cart&view=noframe&action=add_product&'
									+ query + '&productID='
									+ objForm.getAttribute('rel'), '');
			openFadeIFrame(url);
			return false;
		}
	},
	'.product_option' : function(e) {
		e.onchange = function() {
			if(formatPrice&&defaultCurrency&&defaultCurrency.getView){
				var objForm = getFormByElem(this);
				if (!objForm)
					return true;
	
				var r_productParam = getElementsByClass('product_option', objForm);
				var price = parseFloat(getElementByClass('product_price', objForm).value.replace(/,/,'.'));
				var list_price = 0;
				var obj = getElementByClass('product_list_price', objForm);
				if (obj)
					list_price = parseFloat(obj.value.replace(/,/,'.'));
	
				for (var i = r_productParam.length - 1; i >= 0; i--) {
	
					var option = select_getCurrOption(r_productParam[i]);
					if (!option)
						continue;
	
					price += parseFloat(option.getAttribute('rel').replace(/,/,'.'));
					list_price += parseFloat(option.getAttribute('rel').replace(/,/,'.'));
				}
	
				getElementByClass('totalPrice', objForm).innerHTML = formatPrice(price);
				var obj = getElementByClass('regularPrice', objForm);
				if (obj)
					obj.innerHTML = formatPrice(list_price);
				var obj = getElementByClass('youSavePrice', objForm);
				if (obj)
					obj.innerHTML = formatPrice(list_price - price)
							+ ' ('
							+ Math
									.round((list_price - price) / list_price * 100,
											2) + '%)';
			}
		}
	},
	'.hndl_proceed_checkout' : function(e) {
		e.onclick = function() {
			openFadeIFrame(ORIG_LANG_URL + set_query('?ukey=cart&view=noframe'));
			return false;
		}
	},
	'input.goto' : function(e) {
		e.onclick = function() {
			if (this.className.search(/confirm/) !== -1
					&& !window.confirm(this.getAttribute('title')))
				return
			document.location.href = this.getAttribute('rel');
		}
	},
	'.gofromfade' : function(e) {
		e.onclick = function() {

			parent.document.location.href = this.href;
			parent.closeFadeIFrame();
			return false;
		}
	},
	'input.digit' : function(e) {
		e.onkeydown = function(event) {
			return validate_input_digit(event);
		}
	}
});

Behaviour.addLoadEvent(function() {
	setTimeout(function(){
		if(formatPrice&&defaultCurrency&&defaultCurrency.getView){
			var totalPrices = getElementsByClass('totalPrice');
			for (var k = totalPrices.length - 1; k >= 0; k--) {
		
				var objForm = getFormByElem(totalPrices[k]);
				if (!objForm)
					continue;;
		
				var r_productParam = getElementsByClass('product_option', objForm);
				var price = parseFloat(getElementByClass('product_price', objForm).value.replace(/,/,'.'));
				var list_price = 0;
				var obj = getElementByClass('product_list_price', objForm);
				if (obj)
					list_price = parseFloat(obj.value.replace(/,/,'.'));
		
				for (var i = r_productParam.length - 1; i >= 0; i--) {
		
					var option = select_getCurrOption(r_productParam[i]);
					if (!option)
						continue;
		
					price += parseFloat(option.getAttribute('rel'));
					list_price += parseFloat(option.getAttribute('rel'));
				}
				var totalPrice = (''+formatPrice(price));
				if(totalPrice.length>0){
					totalPrices[k].innerHTML = totalPrice;
				}
				var obj = getElementByClass('regularPrice', objForm);
				if (obj){
					obj.innerHTML = formatPrice(list_price);
				}
				var obj = getElementByClass('youSavePrice', objForm);
				if (obj){
					obj.innerHTML = formatPrice(list_price - price) + ' ('
							+ Math.round((list_price - price) / list_price * 100, 2)
							+ '%)';
				}
			}
		}
	},500);
});