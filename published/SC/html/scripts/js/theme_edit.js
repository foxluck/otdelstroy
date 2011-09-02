var olayer = getLayer('btn-open-fullscreen');
if(olayer){
	olayer.onclick = function(){
		if(getLayer('hidden1').style.display != 'none'){
			getLayer('hidden1').style.display = 'none';
			getLayer('hidden2').style.display = 'none';
			getLayer('btn-open-fullscreen').value = translate.thm_close_fullscreen;
			if(getLayer('smpl-iframe')){
				saveSmplTempTemplate(function(_msg){ if(!_msg.isError()){ window.openFullscreen(); } });
			}else{
				saveAdvTempTemplate(function(_msg){ if(!_msg.isError()){ window.openFullscreen(); } });
			}
		}else{
			if(fswindow && !fswindow.closed){
				with(fswindow){
					fswindow.dontcheck_onclose = true;
					if(getLayer('smpl-iframe', fswindow)){
						saveSmplTempTemplate( function(_msg){ if(!_msg.isError()){ window.opener.location.reload(); window.close(); } });
					}else{
						saveAdvTempTemplate(function(_msg){ if(!_msg.isError()){ window.opener.location.reload(); window.close(); } });
					}
				}
			}else{
				window.location.reload();
			}
		}
	};
}

olayer = getLayer('btn-close-fullscreen');
if(olayer){
	olayer.onclick = closeFullScreen;
}

var _close_timer = null;
function checkFullScreen(ontimer){
	
	if(!fswindow || fswindow.closed){
		
		if(_close_timer){
			clearInterval(_close_timer);
		}
		if(!ontimer){
			window.location.reload();
		}
	}else{
		if((ontimer === true)&&!_close_timer){
			_close_timer = setInterval(function(){checkFullScreen(false);},200);
		}
	}	
}

function closeFullScreen(){

	window.dontcheck_onclose = true;
	var pwnd = window.opener;

	if(pwnd && getLayer('hidden1',pwnd)){
		if(getLayer('smpl-iframe')){
			saveSmplTempTemplate( function(_msg){ if(!_msg.isError()){ window.opener.location.reload(); window.close(); } });
		}else{
			saveAdvTempTemplate(function(_msg){ if(!_msg.isError()){window.opener.location.href += '&aaa=1'; window.close(); } });
		}
	}
	return false;
}

function saveAdvTempTemplate(handler){

	window.__templateChanged = beforeUnloadHandler_contentChanged; 
	cpt_saveTemplate(true, handler);
}

function saveSmplTempTemplate(handler){

	var objIframe = getLayer('smpl-iframe');
	var smpl_window = objIframe.contentWindow;
	with(smpl_window){
		smpl_window.__templateChanged = smpl_window.beforeUnloadHandler_contentChanged;
		cpt_saveSlip(true, handler);
	}
}

function openFullscreen(){

	var wnd_params = getFullscreenWindowParams();

	fswindow = window.open(set_query('fullscreen=1',document.location.href),"ThemeEditFullscreen", wnd_params);
	if(fswindow === null || !fswindow){
		alert(translate.thm_allow_popups);
		window.location.reload();
		return;
	}
	fswindow.moveTo(0,0);
}

function getFullscreenWindowParams() {

	if ( typeof( screen.availWidth ) == 'number' ) {
		xMax = screen.availWidth;
		yMax = screen.availHeight;
	}
	else if ( typeof( screen.width ) == 'number' ) {
		xMax = screen.width;
		yMax = screen.height;
	} else if ( typeof( window.outerWidth ) == 'number' ) {
		xMax = window.outerWidth;
		yMax = window.outerHeight;
	} else if ( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		xMax = document.documentElement.clientWidth;
		yMax = document.documentElement.clientHeight;
	} else if ( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		xMax = document.body.clientWidth;
		yMax = document.body.clientHeight;
	} else {
		xMax = 640;
		yMax = 480;
	}
	var isMac = (navigator.appVersion.indexOf("Mac") != -1) ? true : false;
	if ( isMac ) yMax = yMax - 20;

	return "minimizable=no, width=" + xMax + ", height="+ yMax + ", resizable=no,"
	               + " fullscreen=yes, titlebar=no, directories=no, channelmode=no, scrollbars=no,"
	               + " status=no, toolbar=no, menubar=no, location=no";
}
