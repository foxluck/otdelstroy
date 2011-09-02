UploadDialog = function(dialogContentId) {
	this.dialogContentId = dialogContentId;
	this.strings = new Array ();
	this.state = "start";
	this.uploader = null;
	this.completeFilesCount = 0;
	this.errorFilesCount = 0;
	this.startQueueFilesCount = 0;
	this.fullErrorMessage = null;
	this.currentFileNum = 0;
	this.cancelByError = false;
}
UploadDialog.prototype = new CommonDialog;

UploadDialog.STATE_START = "START";
UploadDialog.STATE_PROCESS = "PROCESS";
UploadDialog.STATE_COMPLETED = "COMPLETED";
UploadDialog.STATE_LIMIT_ERROR = "LIMIT_ERROR";

/* state functions */
UploadDialog.prototype.resetState = function () {
	this.state =  UploadDialog.STATE_START;
//	this.cancelQueue ();
	this.completeFilesCount = 0;
	this.errorFilesCount = 0;
	this.currentFileNum = 0;
	this.cancelByError = false;
}

UploadDialog.prototype.cancelQueue = function () {
	this.uploader.stopUpload();
	var stats;
	
	if (this.uploader.getStats() != null) {
		do {
			stats = this.uploader.getStats();
			this.uploader.cancelUpload();
		} while (stats.files_queued !== 0);
	}
}

UploadDialog.prototype.startQueue = function () {
	this.state = UploadDialog.STATE_PROCESS;
	this.startQueueFilesCount = this.getQueueFilesCount();
	this.displayCurrentState ();
	this.uploader.startUpload ();
	Ext.getDom("FilesArea").scrollTop = 0;
}

UploadDialog.prototype.selectFiles = function () {
	//this.uploader.selectFiles ();
}

UploadDialog.prototype.upgradeFilesCount = function () {
	var currentQueueCount = this.getQueueFilesCount ();
	var currentFileNum = this.startQueueFilesCount - currentQueueCount+1;
	if (currentFileNum > this.startQueueFilesCount)
		currentFileNum = this.startQueueFilesCount;
		
	
	if (this.state == UploadDialog.STATE_PROCESS) {
		Ext.get("queueFilesCount").update (currentFileNum + "/" + this.startQueueFilesCount );
		Ext.get("filesSelectedStr").setDisplayed (false);
		Ext.get("filesUploadingStr").setDisplayed (true);
	} else {
		Ext.get("queueFilesCount").update(currentQueueCount);
		Ext.get("filesSelectedStr").setDisplayed (true);
		Ext.get("filesUploadingStr").setDisplayed (false);
	}
}

UploadDialog.prototype.flashInit = function () {
	
	var fileTypes = this.fileTypes ? this.fileTypes : "*.*";
	var fileTypesDesc = this.fileTypesDesc ? this.fileTypesDesc : "All Files";
	
	var uploadURL = this.uploadURL;
	if (Ext.isIE && this.ieUploadURL != null)
		uploadURL = this.ieUploadURL;		
	
	this.uploader = new SWFUpload({
		flash_url : this.swfURL,
		upload_url: uploadURL,	// Relative to the SWF file
		
		file_size_limit : "204800",
		file_types : fileTypes,
		file_types_description : "Image Files",
		file_upload_limit : "0",
		file_queue_limit :"0",
		
		debug: false,

		// Button settings
		button_image_url: "",	// Relative to the Flash file
		//button_width: "110",
		button_width: this.strings.btnUpload.length * 8,
		button_height: "25",
		button_placeholder_id: "btn-upload",
		button_text: ' ',
		button_cursor: SWFUpload.CURSOR.HAND,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT, 

		degraded_container_id : "degradedUI1",
		file_dialog_start_handler : fileDialogStart,
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_complete_handler : uploadComplete,
		file_complete_handler : fileComplete
		
		
	});

}



UploadDialog.prototype.initialize = function () {
	this.flashInit ();
	this.uploader.strings = new Array ();
	this.uploader.parentDialog = this;
	
	this.uploader.customSettings.progressTarget = "fsUploadProgress1";	// Add an additional setting that will later be used by the handler.
	
	this.uploader.afterCompleted = function () {
		var parentDlg = this.parentDialog;
		var sleep = (parentDlg.errorFilesCount > 0) ? 3000 : 300;
		window.setTimeout (function () {
			processAjaxButton ("refresh");
			if (parentDlg.state != UploadDialog.STATE_LIMIT_ERROR)
				parentDlg.state = UploadDialog.STATE_COMPLETED;
			parentDlg.displayCurrentState ();
			parentDlg.resetState ();
		}, sleep);
	}
	
	this.uploader.afterFileComplete = function (fileObj) {
		this.parentDialog.completeFilesCount++;
		this.parentDialog.upgradeFilesCount ();
	}
	
	this.uploader.afterFileError = function (fileObj, error_code, message) {
		var parentDlg = this.parentDialog;
		parentDlg.errorFilesCount++;
		
		// Limit error occurred
		if (!parentDlg.cancelByError && error_code == SWFUpload.UPLOAD_ERROR.HTTP_ERROR && parentDlg.fullMessages[message] != null) {
			parentDlg.state = UploadDialog.STATE_LIMIT_ERROR;
			parentDlg.fullErrorMessage = parentDlg.fullMessages[message];
			parentDlg.errorFilesCount += this.getStats().files_queued;
			parentDlg.cancelByError = true;
			parentDlg.cancelQueue ();
		}
		this.parentDialog.upgradeFilesCount ();
	}
	
	this.uploader.afterFilesSelected = function () {
		this.parentDialog.completeFilesCount = 0;
		this.parentDialog.state = UploadDialog.STATE_START;
		this.parentDialog.displayCurrentState ();
	}
	
	this.uploader.afterFileCancelled = function () {
		if (!this.parentDialog.cancelByError)
			this.parentDialog.displayCurrentState ();
	}
	
	this.resetState ();
}

UploadDialog.prototype.displayCurrentState = function () {
	var state = this.state;
	this.cancelByError = false;
	
	var elems = new Array (); 
	var blocks = new Array ();
	
	elems.selectMoreLink = Ext.get("selectMoreLink");
	elems.afterSelectMoreLink = Ext.get("afterSelectMoreLink");
	elems.completedFilesCount = Ext.get("completedFilesCount");
	elems.errorFilesCount = Ext.get("errorFilesCount");
	elems.errorFilesBlock = Ext.get("errorFilesBlock");
	
	blocks.afterUpload = Ext.get ("blockAfterUpload");
	blocks.limitError = Ext.get ("blockLimitError");
	blocks.noSelectedFiles = Ext.get ("blockNoSelectedFiles");
	blocks.selectedFiles = Ext.get ("blockSelectedFiles");
	
	//if (this.state == UploadDialog.STATE_PROCESS) 
		//elems.selectMoreLink.setDisplayed(false);
	
	var queueFilesCount = this.getQueueFilesCount ();
 	this.upgradeFilesCount ();
 	elems.completedFilesCount.update(this.completeFilesCount);
 	
 	if (this.startQueueFilesCount != null && this.completeFilesCount != null)
 		this.errorFilesCount = this.startQueueFilesCount - this.completeFilesCount;
 	elems.errorFilesCount.update(this.errorFilesCount);
 	elems.errorFilesBlock.setDisplayed (this.errorFilesCount > 0);
 	
 	if (queueFilesCount == 0 || state == UploadDialog.STATE_PROCESS) {
 		this.btnStart.setDisabled(true);
 		elems.selectMoreLink.setDisplayed(false);
 	} else {
 		this.btnStart.setDisabled(false);
 		elems.selectMoreLink.setDisplayed(true);
 	}
 	//elems.selectMoreLink.dom.style.display = "none";
 		
	if (state == UploadDialog.STATE_COMPLETED || state == UploadDialog.STATE_LIMIT_ERROR) {
		
		blocks.afterUpload.setDisplayed (true);
		blocks.noSelectedFiles.setDisplayed(false);
		blocks.selectedFiles.setDisplayed(false);
		
 		if (state == UploadDialog.STATE_LIMIT_ERROR) {
 			blocks.limitError.update(this.fullErrorMessage);
 			blocks.limitError.setDisplayed (true);
 			elems.afterSelectMoreLink.setDisplayed(false);
 		}
 		
 		if (state == UploadDialog.STATE_COMPLETED) {
 			blocks.limitError.setDisplayed (false);
 			elems.afterSelectMoreLink.setDisplayed(true);
 		}
 		
	} else {
	
		blocks.afterUpload.setDisplayed(false);
		if (queueFilesCount > 0) {
 			blocks.selectedFiles.setDisplayed(true);
 			blocks.noSelectedFiles.setDisplayed(false);
 			//elems.selectMoreLink.setDisplayed(true);
		} else {
			blocks.selectedFiles.setDisplayed(false);
			blocks.noSelectedFiles.setDisplayed(true);
		}
	}
}


UploadDialog.prototype.getQueueFilesCount = function () {
	if (document.upDlgVisible) {
		return (this.uploader.getStats() != null) ? this.uploader.getStats().files_queued : 0;
	}
	else {
		return 0;
	}	
}
