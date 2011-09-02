/*
 * Demo Note: This demo uses a FileProgress class that handles the UI for
 * displaying the file name and percent complete. The FileProgress class is not
 * part of SWFUpload.
 */

/*******************************************************************************
 * Event Handlers These are my custom event handlers to make my web application
 * behave the way I went when SWFUpload completes different tasks. These aren't
 * part of the SWFUpload package. They are part of my application. Without these
 * none of the actions SWFUpload makes will show up in my application.
 ******************************************************************************/
var missedFilesCount;

function swfUploadPreLoad() {
	var self = this;
	var loading = function() {
		// document.getElementById("divSWFUploadUI").style.display = "none";
		// document.getElementById("divLoadingContent").style.display = "";

		/*showStatusMessage('SWFUpload is loading. Please wait a moment...',
				'comment_block');*/

		var longLoad = function() {
			showStatusMessage('SWFUpload is taking a long time to'
							+ ' load or the load has failed.'
							+ ' Please make sure that the Flash Plugin is'
							+ ' enabled and that a working version of the'
							+ ' Adobe Flash Player is installed.',
					'error_block');
			// document.getElementById("divLoadingContent").style.display =
			// "none";
			// document.getElementById("divLongLoading").style.display = "";
		};
	/*	this.customSettings.loadingTimeout = setTimeout(function() {
					longLoad.call(self)
				}, 15 * 1000);*/

		document.getElementById(this.customSettings.progressTarget).style.display = "none";
	};

	this.customSettings.loadingTimeout = setTimeout(function() {
				loading.call(self);
			}, 1 * 1000);
	//imm_selectTab('upload');
}
function swfUploadLoaded() {
	var self = this;
	clearTimeout(this.customSettings.loadingTimeout);
	showStatusMessage(false);
	// document.getElementById("divSWFUploadUI").style.visibility = "visible";
	// document.getElementById("divSWFUploadUI").style.display = "block";
	// document.getElementById("divLoadingContent").style.display = "none";
	// document.getElementById("divLongLoading").style.display = "none";
	// document.getElementById("divAlternateContent").style.display = "none";
	var item;
	item = document.getElementById(this.customSettings.queueDivId);
	if (item)
		item.style.display = "block";

	// document.getElementById("btnBrowse").onclick = function () {
	// self.selectFiles(); };
	var btnCancel = document.getElementById(this.customSettings.cancelButtonId);
		if(btnCancel){
		btnCancel.style.display = "";
		btnCancel.onclick = function() {
			self.cancelQueue();
		};
	};

	var links = getElementsByClass(this.customSettings.swfLinkId);
	if (links.length) {
		for (var i = 0; i < links.length; i++) {
			links[i].style.display = 'inline';
		//	links[i].style.backgroundColor = '#f5f0BB';
		}
	}
	imm_selectTab('swfupload');
}

function swfUploadLoadFailed() {
	clearTimeout(this.customSettings.loadingTimeout);
	// document.getElementById("divSWFUploadUI").style.display = "none";
	document.getElementById("divLoadingContent").style.display = "none";
	document.getElementById("divLongLoading").style.display = "none";
	document.getElementById("divAlternateContent").style.display = "";
}

function fileQueued(file) {
	try {
		var progress = new FileProgress(file,
				this.customSettings.progressTarget);
		progress.setStatus("Pending...");
		progress.toggleCancel(true, this);
		var progressItem = document.getElementById(this.customSettings.progressTarget);
		if (progressItem)
			progressItem.style.display = "";

	} catch (ex) {
		this.debug(ex);
	}

}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("You have attempted to queue too many files.\n"
					+ (message === 0
							? "You have reached the upload limit."
							: "You may select "
									+ (message > 1 ? "up to " + message
											+ " files." : "one file.")));
			return;
		}

		var progress = new FileProgress(file,
				this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
			case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT :
				progress.setStatus("File is too big.");
				this.debug("Error Code: File too big, File name: "
								+ file.name + ", File size: " + file.size
								+ ", Message: " + message);
				break;
			case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE :
				progress.setStatus("Cannot upload Zero Byte files.");
				this.debug("Error Code: Zero byte file, File name: "
						+ file.name + ", File size: " + file.size
						+ ", Message: " + message);
				break;
			case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE :
				progress.setStatus("Invalid File Type.");
				this.debug("Error Code: Invalid File Type, File name: "
						+ file.name + ", File size: " + file.size
						+ ", Message: " + message);
				break;
			default :
				if (file !== null) {
					progress.setStatus("Unhandled Error");
				}
				this.debug("Error Code: " + errorCode + ", File name: "
						+ file.name + ", File size: " + file.size
						+ ", Message: " + message);
				break;
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		missedFilesCount = 0;

		/* I want auto start the upload and I can do that here */
		this.startUpload();
		if (numFilesSelected > 0) {
			// document.getElementById(this.customSettings.cancelButtonId).style.display
			// = 'inline';
			// document.getElementById(this.customSettings.cancelButtonId).disabled
			// = false;
			// document.getElementById(this.customSettings.startButtonId).style.display
			// = 'inline';
			// document.getElementById(this.customSettings.startButtonId).disabled
			// = false;

			// document.getElementById(this.customSettings.queueDivId).style.display
			// = 'block';
		}

	} catch (ex) {
		this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		/*
		 * I don't want to do any file validation or anything, I'll just update
		 * the UI and return true to indicate that the upload should start. It's
		 * important to update the UI here because in Linux no uploadProgress
		 * events are called. The best we can do is say we are uploading.
		 */
		var progress = new FileProgress(file,
				this.customSettings.progressTarget);
		progress.setStatus("Uploading...");
		progress.toggleCancel(true, this);
	} catch (ex) {
	}

	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file,
				this.customSettings.progressTarget);
		progress.setProgress(percent);
		progress.setStatus("Uploading...");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
		var progress = new FileProgress(file,
				this.customSettings.progressTarget);
		var pageContent = new String(serverData);
		var responce_position = pageContent.search("OK:");
		this.debug('search "OK": not found'+responce_position+':'+(responce_position===false));
		if ((responce_position===false)||(responce_position>3)){
			progress.setError();
			progress.toggleCancel(false);
			if (pageContent.length > 4096)
				pageContent = "Cookie Error";
			progress.setStatus("Upload Error: " + pageContent);// alert(serverData);
			missedFilesCount++;
			file.filestatus = SWFUpload.FILE_STATUS.ERROR;
		} else {
			var info = pageContent.split(':');
			progress.setComplete({
						'url' : info[2],
						'name' : info[1],
						'width' : info[3],
						'height' : info[4]
					});
			progress.setStatus("Complete.");
			progress.toggleCancel(false);
		}

	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file,
				this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
			case SWFUpload.UPLOAD_ERROR.HTTP_ERROR :
				progress.setStatus("Upload Error: " + message);
				this.debug("Error Code: HTTP Error, File name: " + file.name
						+ ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED :
				progress.setStatus("Upload Failed.");
				this
						.debug("Error Code: Upload Failed, File name: "
								+ file.name + ", File size: " + file.size
								+ ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.IO_ERROR :
				progress.setStatus("Server (IO) Error");
				this.debug("Error Code: IO Error, File name: " + file.name
						+ ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR :
				progress.setStatus("Security Error");
				this.debug("Error Code: Security Error, File name: "
						+ file.name + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED :
				progress.setStatus("Upload limit exceeded.");
				this.debug("Error Code: Upload Limit Exceeded, File name: "
						+ file.name + ", File size: " + file.size
						+ ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED :
				progress.setStatus("Failed Validation.  Upload skipped.");
				this.debug("Error Code: File Validation Failed, File name: "
						+ file.name + ", File size: " + file.size
						+ ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED :
				// If there aren't any files left (they were all cancelled)
				// disable the cancel button
				if (this.getStats().files_queued === 0) {
					document.getElementById(this.customSettings.cancelButtonId).disabled = true;
				}
				progress.setStatus("Cancelled");
				progress.setCancelled();
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED :
				progress.setStatus("Stopped");
				break;
			default :
				progress.setStatus("Unhandled Error: " + errorCode);
				this.debug("Error Code: " + errorCode + ", File name: "
						+ file.name + ", File size: " + file.size
						+ ", Message: " + message);
				break;
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadComplete(file) {
	if (this.getStats().files_queued === 0) {
		var btnCancel = document.getElementById(this.customSettings.cancelButtonId);
		if(btnCancel)btnCancel.disabled = true;
	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
	try {
		uploadSWFmessage = (numFilesUploaded - missedFilesCount) + " file"
				+ ((numFilesUploaded - missedFilesCount) === 1 ? "" : "s")
				+ " uploaded." + refreshString;

		missedFilesCount = 0;
		progressTarget = this.customSettings.progressTarget;
		// showStatusMessage(message);
		var hideQueue = function() {
			document.getElementById(progressTarget).style.display = "none";
			showStatusMessage(uploadSWFmessage);
		};
		setTimeout(function() {
					hideQueue.call(self)
				}, 3 * 1000);
		var btnCancel;
		btnCancel = document.getElementById(this.customSettings.cancelButtonId);
		if (btnCancel) {
			btnCancel.style.display = "none";
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function showStatusMessage(message, msg_class) {
	try {
		var status = document.getElementById("divStatus");
		if (message) {
			status.style.display = 'block';
			status.className = msg_class ? msg_class : 'success_block';
			status.innerHTML = message;
		} else {
			status.style.display = 'none';
		}
	} catch (ex) {
		this.debug(ex);
	}
}