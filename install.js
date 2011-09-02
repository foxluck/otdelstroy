/**
 * Create XML http request object
 * 
 * @return {object|null}
 */
function createRequestObject() {
	if (window.XMLHttpRequest) {
		try {
			return new XMLHttpRequest();
		} catch (e) {
		}
	} else if (window.ActiveXObject) {
		try {
			return new ActiveXObject('Msxml2.XMLHTTP');
		} catch (e) {
		}
		try {
			return new ActiveXObject('Microsoft.XMLHTTP');
		} catch (e) {
		}
	}
	return null;
}

var xmlReq = null;
var d = new Date();
var ProgressManager = {
	/**
	 * 
	 * @type HTMLDivElement
	 */
	progressBar : null,
	/**
	 * 
	 * @type HTMLSpanElement
	 */
	progressStripe : null,
	/**
	 * 
	 * @type HTMLSpanElement
	 */
	progressValue : null,
	form : null,
	debug : false,
	lookup : true,
	hasRestartTime : null,
	/**
	 * 
	 * @param {String}
	 *            progressBarId
	 * @param {String}
	 *            progressStripeId
	 * @param {String}
	 *            progressValueId
	 * @param {boolean}
	 *            debug
	 */
	initialize : function(progressBarId, progressStripeId, progressValueId,
			debug) {
		ProgressManager.progressBar = document.getElementById(progressBarId);
		ProgressManager.progressStripe = document
				.getElementById(progressStripeId);
		ProgressManager.progressValue = document
				.getElementById(progressValueId);
		ProgressManager.debug = debug ? true : false;
	},
	'getState' : function(prevState) {
		try {
			xmlReq = createRequestObject();
			xmlReq.onreadystatechange = ProgressManager.onGetState;
			xmlReq.open("GET", "ajaxhandler.php?action=getstate&source=ajax&req_time="+d.getTime(),
					true);
			xmlReq.setRequestHeader("If-Modified-Since",
					"Sat, 1 Jan 2000 00:00:00 GMT");
			xmlReq.send(null);
		} catch (e) {
			alert(e.message);
		}
	},

	'start' : function(/* Button Object */button) {
		ProgressManager.showProgress();
		setTimeout(ProgressManager.getState, 800);
		if (button) {
			// alert(button);
			button.style.display = 'none';
			ProgressManager.form = button.form;
		}
		return false;
	},
	'hideProgress' : function() {
		if (ProgressManager.progressValue)
			ProgressManager.progressValue.style.display = 'none';
		if (ProgressManager.progressBar)
			ProgressManager.progressBar.style.display = 'none';
	},
	'showLabel' : function(label) {
		if (ProgressManager.progressValue) {
			ProgressManager.progressValue.style.display = 'block';
			ProgressManager.progressValue.innerHTML = label;
		}
	},
	'showProgress' : function() {
		if (ProgressManager.progressValue)
			ProgressManager.progressValue.style.display = 'block';
		if (ProgressManager.progressBar)
			ProgressManager.progressBar.style.display = 'block';
	},
	'onProgress' : function(progress, description)// intVal
	{
		try {
			ProgressManager.showProgress();
			var style = '' + (2 * progress) + 'px';
			var value = '' + progress + '% ';// +progressManager.prevState;
			if (ProgressManager.progressStripe)
				ProgressManager.progressStripe.style.left = style;
			if (ProgressManager.progressValue)
				ProgressManager.progressValue.innerHTML = value;

		} catch (e) {
			alert(e.message);
		}
	},
	'onRestart' : function(restartDescription)// RESTART
	{

		try {
			var curDate = new Date();
			var curTime = curDate.getTime();
			if ((ProgressManager.hasRestartTime == null)
					|| ((curTime - ProgressManager.hasRestartTime) > 10000)) {
				ProgressManager.hasRestartTime = curTime;
				var chmod = getPermissions();
				var xmlReqRestart = createRequestObject();
				xmlReqRestart.open("GET",
						"ajaxhandler.php?action=extract&source=ajax"
								+ chmod, true);//&debug=true
				xmlReqRestart.setRequestHeader("If-Modified-Since",
						"Sat, 1 Jan 2000 00:00:00 GMT");
				xmlReqRestart.send(null);
			}
		} catch (e) {
			alert(e.message);
		}
	},
	'onComplete' : function()// ANOTHER
	{
		ProgressManager.hideProgress();
		ProgressManager.lookup = false;
		setTimeout(function() {
					if (ProgressManager.form)
						ProgressManager.form.submit();
				}, 1000);
	},
	'onGetState' : function() {
		try {
			if (xmlReq.readyState == 4) {// 4 = "loaded"
				if (xmlReq.status == 200) {// 200 = OK
					var responce = xmlReq.responseText.match(/^([^:]+):([^:]+):(.*)$/);
					// STATE_CODE:PROGRESS_VALUE:DESCRIPTION(Base64Encoded
					// Optional)
					if (responce) {
						switch (responce[1]) {// ...our code here...
							case 'RESTART' :
								ProgressManager.onRestart(responce[3]);
								break;
							case 'COMPLETE' :
								ProgressManager.onComplete();
								break;
							case 'PROGRESS' :
								var progress = responce[2];
								if (isNaN(progress)) {
									alert('unknown state '
											+ xmlReq.responseText);
								} else {
									ProgressManager.onProgress(progress,
											responce[3]);
								}
								break;
							default :
								ProgressManager.showLabel('unknown state: '
										+ responce[0]);
								break;
						}
						if (ProgressManager.debug) {
							alert('responce: ' + responce[0] + '\nSTATE '
									+ responce[1])
						}
					} else {
						alert(xmlReq.responseText);
					}
					xmlReq = null;
					if (ProgressManager.lookup) {
						setTimeout(ProgressManager.getState, 800);
					}
				} else {
					if (ProgressManager.lookup)
						setTimeout(ProgressManager.getState, 800);
				}
			}

		} catch (e) {
			alert(e.message);
			if (ProgressManager.lookup)
				setTimeout(ProgressManager.getState, 800);
		}
	}
};

function calcRelativeURL(page) {
	var URL = new String(document.URL);

	lastSlash = URL.lastIndexOf("/");
	URL = URL.substring(0, lastSlash) + "/" + page;

	return URL;
}

function makeLinkURL(page, link) {
	var URL = calcRelativeURL(page);
	document.write(URL);

	for (i = 0; i < document.links.length; i++) {
		if (document.links[i].name == link) {
			document.links[i].href = URL;

			break;
		}
	}

}

function makeHiddenURL(page, varName) {
	var URL = calcRelativeURL(page);
	var form = document.forms[0];

	for (i = 0; i < form.elements.length; i++) {
		if (form.elements[i].name == varName) {
			form.elements[i].value = URL;
			break;
		}
	}

}

function onloadprepare() {
	var form = document.forms[0];

	if (form == null)
		return;

	for (i = 0; i < form.elements.length; i++) {
		if (form.elements[i].name == "nojs") {
			form.elements[i].value = "0";
			break;
		}
	}

}
var mode = null;
function onContinue(button, step) {
	var obj = document.getElementById("btn_continue");

	if (obj)
		obj.style.display = "none";
	if (parseInt(step) == 3) {
		mode = getInstallMode(button);
		setTimeout(function() {
					var obj;
					obj = document.getElementById("main_comment");
					if (obj)
						obj.style.display = "none";
					obj = document.getElementById("mode_select");
					if (obj)
						obj.style.display = "none";

					// var mode = getInstallMode();
					obj = document.getElementById("img_continue_" + mode);
					if (obj)
						obj.style.display = "block";
				}, 50);
		var chmod = null;
		if (button.onclick)
			button.onclick = null;
		if (mode != "2") {
			return ProgressManager.start(button, chmod);// (mode == "2") ||
		} else {
			return false;
		}
		/*
		 * setTimeout(function() { var obj; obj =
		 * document.getElementById("main_comment"); if (obj) obj.style.display =
		 * "none"; obj = document.getElementById("mode_select"); if (obj)
		 * obj.style.display = "none";
		 * 
		 * var mode = getInstallMode(); obj =
		 * document.getElementById("img_continue_" + mode); if (obj)
		 * obj.style.display = "block"; }, 1000); var mode = "2"; var radioGroup =
		 * window.document.installForm.mode; for (i = 0; i <= radioGroup.length;
		 * i++) { if (radioGroup[i].checked) { mode = radioGroup[i].value break; } }
		 */
		/*
		 * if (parseInt(mode) < 2) setTimeout(function() { obj =
		 * document.getElementById("progress_window"); if (obj) obj.src =
		 * "progress.html"; }, 8000);
		 */
		// return (mode != "2");
	} else {
		setTimeout(function() {
					obj = document.getElementById("img_continue");
					if (obj)
						obj.style.display = "block";
				}, 1000);
		return true;

	}
}

function getPermissions() {
	var enabled = document.getElementById('chmod_enabled[0]');
	
	if (enabled && enabled.checked) {
		var value = document.getElementById('chmod[0]');
		if (value && value.value) {
			var res = '&chmod=' + value.value;
			return res;
		}
	}
	return '';
}

function getInstallMode(button) {
	var mode = "2";
	try {
		var radioGroup = button.form.mode;
		for (i = 0; i <= radioGroup.length; i++) {
			if (radioGroup[i].checked) {
				mode = radioGroup[i].value
				break;
			}
		}
	} catch (e) {
	}
	return mode;

}