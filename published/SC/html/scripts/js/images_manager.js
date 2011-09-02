var swfu;

SWFUpload.onload = function() {
	var settings = {
		flash_url : window.swfurl,
		upload_url : window.phploadurl, // Relative to the SWF file
		post_params : {
			"source" : "swfupload",
			"action" : "upload_image",
			"swfuploader" : "swfuploader",
			"PHPSESSID" : window.session_id
		},
		file_size_limit : "5242880",// "5 MB",
		file_types : "*.bmp;*.eps;*.gif;*.hdr;*.jpeg;*.jpg;*.jpe;*.jp2;*.pcx;*.png;*.psd;*.raw;*.tga;*.tga;*.tpic;*.tiff;*.tif;*.wdp;*.hdp;*.xpm;*.pdn",
		file_types_description : "Images",
		file_upload_limit : 200,
		file_queue_limit : 0,
		custom_settings : {
			progressTarget : "fsUploadProgress",
			//cancelButtonId : "btnswfCancel",
			queueDivId : "divSWFUploadUI",
			swfLinkId : "imm_upload_swf_link_block"
		},
		//DEBUG
		debug : false,

		// Button Settings
		button_image_url : window.swfbtn, // Relative to the SWF file
		button_placeholder_id : "spanButtonPlaceholder",
		button_width : 61,
		button_height : 22,

		// The event handler functions are defined in handlers.js
		swfupload_loaded_handler : swfUploadLoaded,
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete, // Queue plugin event

		// SWFObject settings
		minimum_flash_version : "9.0.28",
		swfupload_pre_load_handler : swfUploadPreLoad,
		swfupload_load_failed_handler : swfUploadLoadFailed
	};

	swfu = new SWFUpload(settings);
}

// ////////////

function imm_selectTab(type){
	try{
		if(window.another_type&&type==window.another_type){
			return;
		}
		getLayer('tab-'+type+'-section').className += " current";
		if(window.another_type)getLayer('tab-'+another_type+'-section').className = getLayer('tab-'+another_type+'-section').className.replace(/current/, '');
	
		var links = getLayer('tab-'+type+'-link');
		if(links){
			links.style.backgroundColor = '#f5f0bb';
		}
		if(window.another_type){
			links = getLayer('tab-'+another_type+'-link');
			if(links){
				links.style.backgroundColor = '';
			}
		}
		
		if(window.another_type){
			getLayer('tab-'+another_type+'-section').style.display = "none";
		}
		getLayer('tab-'+type+'-section').style.display = "block";
		window.another_type = type;
	}catch(ex){
		console.debug(ex);
		alert(ex.message+'\n'+ex.fileName+'('+ex.lineNumber+')');
	}
}

Behaviour.register({
	'#tab-upload-link' : function(e) {
		e.onclick = function(event) {
			imm_selectTab('upload');
		};
	},
	'#tab-swfupload-link' : function(e) {
		e.onclick = function() {
			imm_selectTab('swfupload');
		}
	},
	'#tab-deleteall-link' : function(e) {
		e.onclick = function(ev) {
			imm_selectTab('deleteall');
		}
	},
	
	'#imm-img-view' : function(e) {
		e.onclick = function() {
			open_window(this.href, parseInt(this.getAttribute('img_width'))
							+ 50, parseInt(this.getAttribute('img_height'))
							+ 60);
			return false;
		}
	},
	'.imm_img_blck' : function(e) {
		e.onclick = function(ev) {

			ev = getEventObject(ev);
			if (ev.target.ignoreEvent2)
				return;

			var objMenu = getLayer('blck-img-menu');
			var objTbl = getElementByClass('', this, 'table');
			var objImg = getElementByClass('', this, 'img');

			var objView = getLayer('imm-img-view');
			objView.href = objImg.src;
			objView.setAttribute('img_width', objImg.getAttribute('img_width'));
			objView.setAttribute('img_height', objImg
							.getAttribute('img_height'));

			var objDelete = getLayer('imm-img-delete');
			objDelete.href = set_query('&img_id='
							+ objImg.getAttribute('img_id'), objDelete.href);
			objDelete.onclick = function() {
				if (window.confirm(this.getAttribute('title')))
					document.location.href = this.href;
				return false;
			}
			this.insertBefore(objMenu, objTbl);

			getLayer('imm-img-permalink-field-box').style.display = 'none';
			getLayer('imm-img-permalink-field').value = objImg.src;

			getLayer('imm-img-file').innerHTML = objImg
					.getAttribute('img_file');

			objMenu.style.display = "block";
			getLayer('imm-img-permalink-field').style.display = '';

			ev = getEventObject(ev);
			ev.target.ignoreEvent = true;
			return false;
		}
	},
	'#content' : function(e) {
		e.onclick = function(ev) {
			ev = getEventObject(ev);
			if (ev.target.ignoreEvent || ev.target.ignoreEvent2)
				return;

			var objMenu = getLayer('blck-img-menu');
			objMenu.style.display = "none";
		};
	},

	
	'a.imm_img_view' : function(e) {
		e.onclick = function() {
			open_window(this.href, parseInt(this.getAttribute('img_width'))
							+ 50, parseInt(this.getAttribute('img_height'))
							+ 60);
			return false;
		}
	}
});

getLayer('imm-img-permalink').onclick = function(ev) {
	var objPermField = getLayer('imm-img-permalink-field-box');

	objPermField.style.display = objPermField.style.display == 'block'
			? 'none'
			: 'block';
	ev = getEventObject(ev);
	ev.target.ignoreEvent2 = true;
	return false;
}

getLayer('blck-img-menu-sub').onclick = function(ev) {
	ev = getEventObject(ev);
	ev.target.ignoreEvent2 = true;
}

Nifty("li.tab", "top same-height");
