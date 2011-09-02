<?php

/*
 * WebAsyst Smarty Plugin
 * -------------------------------------------------------------
 * Type:     block
 * Name:     pd_shareJavaScriptCode
 * Purpose:  Print javascript code 
 * -------------------------------------------------------------
 */

function smarty_block_pd_shareJavaScriptCode( $params, $content, &$smarty, &$repeat ) {
    global $currentUser;
    $countFiles = count($params["filesList"]);
    $pdStrings = $smarty->get_template_vars('pdStrings');
    $result = '
        <script>
            var currentShowPicture = 0;
            var allFiles = '.$countFiles.';
            var isPlayStart = false;
            var interval = 0;
            var delay = 0;
            var isNextFileLoading = false;
            var firstLoad = false;
            var isControlsFrosen = false;
            
            // pagination
            
            var curThumbsPage = 0;
            var thumbsPerPage = '.PD_SLIDESHOW_DEFAULT_THUMBS_COUNT.';
            var maxThumbsPage = '.floor(($countFiles - 1) / PD_SLIDESHOW_DEFAULT_THUMBS_COUNT).';
            //alert(maxThumbsPage);
            
            function getImage(n) {
                this.num = n;
        		
        		this.tick = function() {
            		var req = new Subsys_JsHttpRequest_Js();
            		var obj = this;
            
            		req.onreadystatechange = function() {
            			if (req.readyState == 4){
            				if (req.responseJS){
            					if ( req.responseJS.state == "OK" ) {
            					    var im = document.getElementById("img_" + obj.num);
            					    var imDiv = document.getElementById("image_" + obj.num);
            					    im.src = req.responseJS.responseText;
            					    imDiv.setAttribute("emptyfile", 0);
            					    im.onload = function () {
            					        //pd_shareChangePicture(this.getAttribute("num"), 1);
            					        pd_shareChangeImage(this.getAttribute("num"));
            					    }
            					}
            					if ( req.responseJS.error != "" ) {
            						alert( req.responseJS.error );
            					}
            				}
            			}
            		}
            		
            		req.open("GET", "getimageurl.php", true);
            		req.send( { NUM: this.num, IMG_SIZE: currentThumbSize, XUSER: '.($currentUser ? '1' : '0').' } );
        		}
        	}
        	
        	function changeImagesSize(size) {
        	    currentThumbSize = size;
        	    for (i = 0; i < '.$countFiles.'; i ++) {
                    var nextImgDiv = document.getElementById("image_" + i);
                    nextImgDiv.setAttribute("emptyfile", 1);
                }
                var mainDesk = document.getElementById("mainDesk");
                
                if (size == '.PD_LARGE_THUMB_SIZE.') {
                    mainDesk.setAttribute("width", '.PD_LARGE_THUMB_SIZE.');
                    mainDesk.setAttribute("height", '.PD_LARGE_THUMB_SIZE.');
                }
                else {
                    mainDesk.setAttribute("width", 800);
                    mainDesk.setAttribute("height", '.PD_MEDIUM_THUMB_SIZE.');
                }
                pd_shareChangeImage(currentShowPicture);
        	}
        	
        	function getThumbnailPanel(page) {
                this.page = page;
        		
        		this.tick = function() {
            		var req = new Subsys_JsHttpRequest_Js();
            		var obj = this;
            
            		req.onreadystatechange = function() {
            			if (req.readyState == 4) {
            				if (req.responseJS) {
            					if ( req.responseJS.state == "OK" ) {
            					    //alert(req.responseJS.responseText);
            					    if (req.responseJS.responseText) {
            					        var thumbsPanel = document.getElementById("thumbsPanel");
            					        thumbsPanel.innerHTML = req.responseJS.responseText;
            					        pd_shareChangeImage(currentShowPicture);
            					        pd_markThumbnail(currentShowPicture);
            					    }
            					}
            					if ( req.responseJS.error != "" ) {
            						alert( req.responseJS.error );
            					}
            				}
            			}
            		}
            		
            		req.open("GET", "getthumbpanel.php", true);
            		req.send( { PAGE: this.page } );
        		}
        	}
            
            function pd_shareTemplateInit() {
                try {
                    //var imgDiv = document.getElementById("bigImageDiv");
                    //var canvasWidth = imgDiv.offsetWidth;
                    //var canvasHeight = imgDiv.offsetHeight;
                    //var imgDivPos = xs_getAbsolutePos(imgDiv);
                    
                    for (i = 0; i < '.$countFiles.'; i ++) {
                        var nextImgDiv = document.getElementById("image_" + i);
                        
                        /*
                        var rg, res;
                        rg = new RegExp("^([0-9]+)px$", "i");
                        res = rg.exec(nextImgDiv.style.width);
                        var offX = (canvasWidth / 2) - (res[1] / 2);
                        
                        res = rg.exec(nextImgDiv.style.height);
                        var offY = (canvasHeight / 2) - (res[1] / 2);
                        */
                        
                        //xs_setAbsolutePosition(nextImgDiv, imgDivPos.x + offX, imgDivPos.y + offY);
                        //xs_setAbsolutePosition(nextImgDiv, imgDivPos.x, imgDivPos.y);
                        
                        if (i == 0) {
                            nextImgDiv.style.display = ""; 
                        }
                    }
                    
                    //if (!firstLoad) {
                        if (currentShowPicture == 0) { 
                            pd_changeThumbPage(curThumbsPage);                   
                            firstLoad = true;
                        }
                    //}
                }
                catch(e){
                }    
            }
            
            function checkPageListers() {
                var el1 = document.getElementById("prevSel");
                var el2 = document.getElementById("nextSel");
                
                if (el1 && el2) {
                    el1.style.display = "block";
                    el2.style.display = "block";
                    
                    if (curThumbsPage == 0) {
                        el1.style.display = "none";
                    }
                    if (curThumbsPage == maxThumbsPage) {
                        el2.style.display = "none";
                    }
                }
            }
            
            function pd_changeThumbPage(page) {
                var listTo = parseInt(page);
                curThumbsPage += listTo;
                if (curThumbsPage < 0) {
                    curThumbsPage = 0;
                    return;
                }
                if (curThumbsPage > maxThumbsPage) {
                    curThumbsPage = maxThumbsPage;
                    return;
                }
                
                if (listTo >= 0) {
                    currentShowPicture = curThumbsPage * (thumbsPerPage - 1);
                }
                else {
                    var t = thumbsPerPage - 1;
                    currentShowPicture = (curThumbsPage * t) + t;
                }
		        if (currentShowPicture < 0) currentShowPicture = 0;
		        
		        checkPageListers();
		        
                var tp = new getThumbnailPanel(curThumbsPage);
                tp.tick();
            }
            
            function goFirst() {
                curThumbsPage = 0;
                currentShowPicture = 0;
                
                checkPageListers();
                
                var tp = new getThumbnailPanel(curThumbsPage);
                tp.tick();
            }
            
            function goLast() {
                curThumbsPage = maxThumbsPage;
                currentShowPicture = allFiles - 1;
                
                checkPageListers();
                
                var tp = new getThumbnailPanel(curThumbsPage);
                tp.tick();
            }
            
            function pd_shareShowPopUpThumb(n) {
                //var curThumb = document.getElementById("thumb1_div_" + n);
                //curThumb.style.display = "";
            }
            
            function pd_shareClosePopUpThumb(n) {
                //var curThumb = document.getElementById("thumb1_div_" + n);
                //curThumb.style.display = "none";
            }
            
            function pd_markThumbnail(n) {
                var cur = document.getElementById("thumb2_div_" + n);
                
                if (cur != null) {
                    cur.style.cssText = "border: 2px solid #FFFF00;";
                }
            }
            
            function pd_unmarkThumbnail(n) {
                var cur = document.getElementById("thumb2_div_" + n);
                
                if (cur != null) {
                    cur.style.cssText = "border: 2px solid #FFFFFF;";
                }
            }
            
            function pd_clearAllNotShow() {
                var curImgDiv;
                var curImgTitle;
                for (i = 0; i < '.$countFiles.'; i ++) {
                    var curImgDiv = document.getElementById("image_" + i);
                    var curImgTitle = document.getElementById("fileDesc_" + i);
                    curImgDiv.style.display = "none";
                    curImgTitle.style.display = "none";
                }
            }
            
            function pd_shareChangeImage(n) {
                if (!isPlayStart) {
                    froseControls(true);
                }
                
                var imDiv = document.getElementById("image_" + n);
                var emptyFile = imDiv.getAttribute("emptyfile");
                if (emptyFile == 1) {
                    if (isPlayStart) {
                        clearInterval(interval); 
                        isNextFileLoading = true;
                    }
                    
                    var im = new getImage(n);
                    im.tick();
                    return;
                }
                
                if (isNextFileLoading && isPlayStart && delay) {
                    interval = setInterval("goToImage(1);", delay * 1000);
                }
                isNextFileLoading = false;
                
                pd_clearAllNotShow();
                
                var curImgDiv = document.getElementById("image_" + currentShowPicture);
                var curImgTitle = document.getElementById("fileDesc_" + currentShowPicture);
                
                var newImgDiv = document.getElementById("image_" + n);
                var newImgTitle = document.getElementById("fileDesc_" + n);
                
                curImgDiv.style.display = "none";
                curImgTitle.style.display = "none";
                
                newImgDiv.style.display = "block";
                newImgTitle.style.display = "block";
                
                pd_unmarkThumbnail(currentShowPicture);
                pd_markThumbnail(n);
                
                currentShowPicture = n;
                
                if (!isPlayStart) {
                    froseControls(false);
                }
            }
            
            /*function pd_shareChangePicture(n, effect, ignoreN) {
                
                if (ignoreN == null) ignoreN = true;
                
                
                if (!ignoreN) {
                    if (n == currentShowPicture) return;
                }
                
                froseControls(true);
                
                var imDiv = document.getElementById("image_" + n);
                var emptyFile = imDiv.getAttribute("emptyfile");
                if (emptyFile == 1) {
                    if (isPlayStart) {
                        clearInterval(interval); 
                        isNextFileLoading = true;
                    }
                    
                    var im = new getImage(n, ignoreN);
                    im.tick();
                    return;
                }

                if (isNextFileLoading && isPlayStart && delay) {
                    interval = setInterval("goToImage(1);", delay * 1000);
                }
                isNextFileLoading = false;
                
                var curImgDiv = document.getElementById("image_" + currentShowPicture);
                var curImgTitle = document.getElementById("fileDesc_" + currentShowPicture);
                
                var newImgDiv = document.getElementById("image_" + n);
                var newImgTitle = document.getElementById("fileDesc_" + n);
                
                //newImgDiv.style.display = "block";

                if (!ignoreN) {
                    if (!effect || (effect == 1)) {
                        pd_shareShowEffect1("image_" + currentShowPicture, "image_" + n, 1);
                    }
                    
                    curImgDiv.style.display = "none";
                    curImgTitle.style.display = "none";
                    newImgTitle.style.display = "block";
                    
                    pd_markThumbnail(n);
                    pd_unmarkThumbnail(currentShowPicture);
                }
                else {
                    curImgTitle.style.display = "none";
                    newImgDiv.style.display = "block";
                    newImgTitle.style.display = "block";
                }
                                
                currentShowPicture = n;
                
                froseControls(false);
            }*/
            
            function pd_shareShowEffect1(from, to, start) {
                // Detect IE Browser (see file xsystem.js)
                if (start <= 0) return;
                var fr = document.getElementById(from);
                var t = document.getElementById(to);
                if (xs_is_ie) {
                    var fl1 = start * 100;
                    var fl2 = 100 - fl1;
                    fr.style.filter = "progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=" + fl1 + ")";
                    t.style.filter = "progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=" + fl2 + ")";
                }
                else {
                    var fl1 = start;
                    var fl2 = 1 - fl1;
                    fr.style.opacity = fl1;
                    t.style.opacity = fl2;
                }
                
                setTimeout("pd_shareShowEffect1(\'"+from+"\', \'"+to+"\', "+(start - 0.1)+")", 10);
            }
            
            function goToImage(n) {
                var i = parseInt(currentShowPicture) + parseInt(n);
                //alert(1);
                
                var start = curThumbsPage * (thumbsPerPage - 1);
                var end = start + thumbsPerPage;
                if (end > allFiles) end = allFiles;
                
                if (i >= end) {
                    pd_changeThumbPage(1);
                }
                else if (i <= start) {
                    pd_changeThumbPage(-1);
                }
                
                if (i >= allFiles || i < 0) {
                    if (isPlayStart) {
                        onPlayClick();
                    }
                    return;
                }
                //pd_shareChangePicture(i, 1);
                
                pd_shareChangeImage(i);
            }
            
            function froseControls(p) {
                var incB = document.getElementById("incBut");
                var decB = document.getElementById("decBut");
                var firstBut = document.getElementById("firstBut");
                var lastBut = document.getElementById("lastBut");
                var playB = document.getElementById("playBut");
                var selSizeSelect = document.getElementById("selSizeSelect");
                
                //isControlsFrosen = p;
                
                if (p) {
                    incB.disabled = true;
                    incB.src = prevButImgDisabled.src;
                    incB.style.cursor = "default";
                    incB.alt = "";
                    
                    decB.disabled = true;
                    decB.src = nextButImgDisabled.src;
                    decB.style.cursor = "default";
                    decB.alt = "";
                    
                    firstBut.disabled = true;
                    firstBut.src = firstButImgDisabled.src;
                    firstBut.style.cursor = "default";
                    firstBut.alt = "";
                    
                    lastBut.disabled = true;
                    lastBut.src = lastButImgDisabled.src;
                    lastBut.style.cursor = "default";
                    lastBut.alt = "";
                    
                    if (!isPlayStart) {
                        //playB.disabled = true;
                    }
                    
                    selSizeSelect.disabled = true;
                }
                else {
                    incB.disabled = false;
                    incB.src = prevButImg.src;
                    incB.style.cursor = "pointer";
                    incB.alt = "'.$pdStrings['pd_slideshow_prev_label'].'";
                    
                    decB.disabled = false;
                    decB.src = nextButImg.src;
                    decB.style.cursor = "pointer";
                    decB.alt = "'.$pdStrings['pd_slideshow_next_label'].'";
                    
                    firstBut.disabled = false;
                    firstBut.src = firstButImg.src;
                    firstBut.style.cursor = "pointer";
                    firstBut.alt = "'.$pdStrings['pd_slideshow_first_label'].'";
                    
                    lastBut.disabled = false;
                    lastBut.src = lastButImg.src;
                    lastBut.style.cursor = "pointer";
                    lastBut.alt = "'.$pdStrings['pd_slideshow_last_label'].'";
                    
                    if (!isPlayStart) {
                        //playB.disabled = false;
                    }
                    
                    selSizeSelect.disabled = false;
                }
                
                if (currentShowPicture == 0) {
                    incB.disabled = true;
                    incB.src = prevButImgDisabled.src;
                    incB.style.cursor = "default";
                    incB.alt = "";
                    
                    firstBut.disabled = true;
                    firstBut.src = firstButImgDisabled.src;
                    firstBut.style.cursor = "default";
                    firstBut.alt = "";
                }
                else if (currentShowPicture == (allFiles - 1)) {
                    decB.disabled = true;
                    decB.src = nextButImgDisabled.src;
                    decB.style.cursor = "default";
                    decB.alt = "";
                    
                    lastBut.disabled = true;
                    lastBut.src = lastButImgDisabled.src;
                    lastBut.style.cursor = "default";
                    lastBut.alt = "";
                }
            }

            function onPlayClick() {
                var playB = document.getElementById("playBut");
                if (!isPlayStart) {
                    /*var delayEl = document.getElementsByName("playDelay");
                    for (i = 0; i < delayEl.length; i ++) {
                        if (delayEl[i].checked) {
                            delay = delayEl[i].value;
                            break;
                        }
                    }*/
                    delay = 3;
                    
                    playB.src = pauseButImg.src;
                    playB.alt = "'.$pdStrings['pd_slideshow_pause_label'].'";
                    
                    froseControls(true);
                    
                    if (delay != 0) {
                        interval = setInterval("goToImage(1);", delay * 1000);
                        isPlayStart = true;
                    }
                }
                else {
                    clearInterval(interval);
                    playB.src = playButImg.src;
                    playB.alt = "'.$pdStrings['pd_slideshow_play_label'].'";
                    isPlayStart = false;
                    delay = 0;
                    froseControls(false);
                }
            }
            
            function showFilmPanel() {
                var filmPanel = document.getElementById("filmPanel");
                var filmSelector = document.getElementById("filmSelector");
                
                if (filmPanel.style.display == "none") {
                    filmPanel.style.display = "block";
                    filmSelector.innerHTML = "'.$pdStrings['pd_slideshow_hide_thumbs_label'].'";
                }
                else {
                    filmPanel.style.display = "none";
                    filmSelector.innerHTML = "'.$pdStrings['pd_slideshow_show_thumbs_label'].'";
                }
                
                var st = new setThumbnailStatus();
                st.tick();
            }
            
            function setThumbnailStatus() {
        		this.tick = function() {
            		var req = new Subsys_JsHttpRequest_Js();
            		var obj = this;
            
            		req.onreadystatechange = function() {
            			if (req.readyState == 4) {
            				if (req.responseJS) {
            					if ( req.responseJS.state == "OK" ) {
            					    //alert(req.responseJS.responseText);
            					}
            					if ( req.responseJS.error != "" ) {
            						alert( req.responseJS.error );
            					}
            				}
            			}
            		}
            		
            		req.open("GET", "changeshowthumbstatus.php", true);
            		req.send( { } );
        		}
        	}
        </script>
    ';
	return $result;
}

?>