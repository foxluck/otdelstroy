/*
    Xeon System Library
*/
// Detect browser
var xs_is_ie = ( /msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent) );
var xs_is_ie5 = ( xs_is_ie && /msie 5\.0/i.test(navigator.userAgent) );
var xs_is_opera = /opera/i.test(navigator.userAgent);
var xs_is_khtml = /Konqueror|Safari|KHTML/i.test(navigator.userAgent);
var xs_is_ff2 = /Firefox\/2\.0/i.test(navigator.userAgent);

//alert(navigator.userAgent+','+xs_is_ff2);

// Get absolute position of element
// el - element returned with document.getElmentById();
// IMPORTANT!!! Work only if ALL page just loaded
function xs_getAbsolutePos(el, addScroll) {
	var SL = 0, ST = 0;
	
	if ((addScroll == true) && (addScroll != null)) {
    	var is_div = /^div$/i.test(el.tagName);
    	if (is_div && el.scrollLeft)
    		SL = el.scrollLeft;
    	if (is_div && el.scrollTop)
    		ST = el.scrollTop;
	}
    
    var r = { x: el.offsetLeft - SL, y: el.offsetTop - ST };
    
	if (el.offsetParent) {
		var tmp = xs_getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
}

// Move element to px, py
// el - element returned with document.getElmentById();
// px - integer value
// py - integer value
function xs_setAbsolutePosition(el, px, py) {
    if (el.style.position != 'absolute') el.style.position = 'absolute';
    el.style.left = px + 'px';
    el.style.top = py + 'px';
}

// Return true if element has this parent element (check by id)
function xs_rotateElement(el, t) {
    if (xs_is_ie) {
        el.style.filter="progid:DXImageTransform.Microsoft.BasicImage(rotation="+t+")";
    }
}

function xs_printObject(obj) {
    var str = '';
    var cur = '';
    for (key in obj) {
        str += key + ', ';
    }
    alert(str);
}

// Return true if element has this parent element (check by id)
function xs_checkParentElement(el, prnt) {
    if (el == null) {
        return null;
    }
    
    if (el.id) {
        var reg_exp = new RegExp("^" + prnt + ".*$", "i");
        if (reg_exp.test(el.id)) {
            return true;
        }
    }
    
    if (el.parentElement != null) {
        // IE
        return xs_checkParentElement(el.parentElement, prnt);
    }
    else if (el.parentNode != null) {
        // Mozilla
        return xs_checkParentElement(el.parentNode, prnt);
    }
}

// Return attribute value if it is set to el or it parents
function xs_checkParentAttribute(attr, el) {
    if ((el == null) || (el.getAttribute == null)) {
        return null;
    }
    
    var isAttr = el.getAttribute(attr);
    
    if (isAttr != null) {
        return el;
    }
    
    if (el.parentElement != null) {
        // IE
        return xs_checkParentAttribute(attr, el.parentElement);
    }
    else if (el.parentNode != null) {
        // Mozilla
        return xs_checkParentAttribute(attr, el.parentNode);
    }
}

function xs_checkChildAttribute(attr, el) {
    if ((el == null) || (el.getAttribute == null)) {
        return null;
    }
    
    var isAttr = el.getAttribute(attr);
    if (isAttr != null) {
        return el;
    }
    
    var elm = null;
    
    for(var i = 0; i < el.childNodes.length; i ++) {
        if (el.childNodes[i].getAttribute != null) {
            isAttr = el.childNodes[i].getAttribute(attr);
            if (isAttr != null) {
                elm = el.childNodes[i];
                break;
            }
            elm = xs_checkChildAttribute(attr, el.childNodes[i]);
            if (elm != null) {
                return elm;
            }
        }
    }
    
    return elm;
}