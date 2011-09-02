function niftyClean(el){
	var d = getElementsByClass("niftycorners",el);
	for(var k=0; k<d.length; k++){
		var element = d[k];
		if(element && element.parentNode){
			element.parentNode.removeChild(element); 
		}
		/*
		d[k].style.color = "transparent";
		d[k].style.backgroundColor = "transparent";
		var b = getElementsByClass("btest", d[k]);
		for(var j=0; j<b.length; j++){
			
		b[j].style.color = "transparent";
		b[j].style.backgroundColor = "transparent";
		*/
	}
}

function AppendedParentsTableHideTable(){
	var res=AppendedParentsTable.style.display == 'none';
	AppendedParentsTable.style.display = res?'block':'none';
	document.MainForm.AppendedParentsTableHideTable_hidden.value=res?'1':'0';
}

function ConfiguratorHideTable(){
	var obj = getLayer('ConfiguratorTable');
	obj.style.display = obj.style.display == 'none'?'block':'none';
	return false;
}

function SetOptionValueTypeRadioButton( id, radioButtonState) {
	if ( radioButtonState == "UN_DEFINED" )
		document.all["option_radio_type_"+id][0].click();
	else if ( radioButtonState == "ANY_VALUE" )
		document.all["option_radio_type_"+id][1].click();
	else if ( radioButtonState == "N_VALUES" )
		document.all["option_radio_type_"+id][2].click();
}

function AddProductAndOpen_option_value_configurator(optionID){
	document.MainForm.optionID.value = optionID;
	document.MainForm.AddProductAndOpenConfigurator.click();
}

function PhotoHideTable(){
	var res=PhotoTable.style.display == 'none';
	PhotoTable.style.display = res?'block':'none';
	document.MainForm.PhotoHideTable_hidden.value=res?'1':'0';
}

function prdset_selectTab(type){

	niftyClean(getLayer('tab-'+type+'-options'));
	if(window.another_type){
		niftyClean(getLayer('tab-'+another_type+'-options'));
	}
	getLayer('tab-'+type+'-options').className += " current";
	if(window.another_type)getLayer('tab-'+another_type+'-options').className = getLayer('tab-'+another_type+'-options').className.replace(/current/, '');

	
	Nifty("li.tab","top same-height");

	setCookie('prdset_tab', type);
	if(window.another_type){
		getLayer('container-'+another_type+'-options').style.display = "none";
	}
	getLayer('container-'+type+'-options').style.display = "block";
	
	window.another_type = type;
}

function ProductIsProgramHandler(){
	
	document.getElementById('FileNameTable').style.display = document.MainForm.ProductIsProgram.checked?'block':'none';
	document.MainForm.eproduct_filename.disabled = !document.MainForm.ProductIsProgram.checked;
	document.MainForm.eproduct_available_days.disabled = !document.MainForm.ProductIsProgram.checked;
	document.MainForm.eproduct_download_times.disabled = !document.MainForm.ProductIsProgram.checked;
}

function picts_removeImage(photoID){

	getLayer("product-pictures-container").deleteRow(getLayer("picture-container-"+photoID).rowIndex);
}

function picts_setDefault(photoID){
	
	var old_default_container = getElementsByClass('default_picture', getLayer("product-pictures-container"),'td');
	if(old_default_container.length){
		
		old_default_container[0].className = old_default_container[0].className.replace(/default_picture/, '');
	}
	
	getLayer('picture-container-'+photoID).className += " default_picture";
}

function picts_addImage(picture){

var tableTag = getLayer("product-pictures-container");
var cntTag = createTag('tbody',tableTag);
//var trTag = document.createElement('tr');
var trTag = createTag('tr',cntTag);

//var trTag = cntTag.insertRow(cntTag.rows.length);

	cntTag.className = "dragable";
	trTag.className = "gridline1";
	
	trTag.setAttribute("height",tableTag.getAttribute("trheight"));
	
	trTag.id = "picture-container-"+picture.photoID;
	
	var tdTag = trTag.insertCell(0);
	tdTag.className = "img_container handle";

	var imgTag = createTag('img', tdTag);
	imgTag.src = picture['thumbnail_url'];
	
	var tdTag = trTag.insertCell(1);
	tdTag.className = "handle";
	if(is_null(picture.large_picture.url)){
		tdTag.innerHTML = picture.large_picture.file+'<br>'+translate.str_image_not_uploaded;
	}else{
		tdTag.innerHTML=picture.thumbnail_picture.file+'<br>'+picture.thumbnail_picture.width+'&times;'+picture.thumbnail_picture.height+'&nbsp;px<br>'+picture.thumbnail_picture.size;
	
	}
	var tdTag = trTag.insertCell(2);
	if(is_null(picture.large_picture.url)){
		tdTag.innerHTML = picture.large_picture.file+'<br>'+translate.str_image_not_uploaded;
	}else{
		var aTag = createTag('a', tdTag);
		aTag.href = picture.large_picture.url;
		aTag.className = "new_window bluehref";
		aTag.setAttribute("wnd_width", picture.large_picture.width);
		aTag.setAttribute("wnd_height", picture.large_picture.height);
		aTag.innerHTML=picture.large_picture.file;
		tdTag.innerHTML=tdTag.innerHTML+' '+picture.large_picture.width+'&times;'+picture.large_picture.height+'&nbsp;px<br>'+picture.large_picture.size;
	
	}
	var tdTag = trTag.insertCell(3);
	if(is_null(picture.enlarged_picture.url)){
		tdTag.innerHTML = picture.enlarged_picture.file+'<br>'+translate.str_image_not_uploaded;
	}else{
		var aTag = createTag('a', tdTag);
		aTag.href = picture.enlarged_picture.url;
		aTag.className = "new_window bluehref";
		aTag.setAttribute("wnd_width", picture.enlarged_picture.width);
		aTag.setAttribute("wnd_height", picture.enlarged_picture.height);
		aTag.innerHTML=picture.enlarged_picture.file;
		tdTag.innerHTML=tdTag.innerHTML+' '+picture.enlarged_picture.width+'&times;'+picture.enlarged_picture.height+'&nbsp;px<br>'+picture.enlarged_picture.size;
		
	}
	
	var tdTag = trTag.insertCell(4);
	//thumbnail td
	//thumbnail info td
	//enlarged picture info
	//picture info
	
	var aTag = createTag('a', tdTag);
	aTag.href = "#delete_picture";
	aTag.className = "delete_picture_handlers";
	aTag.setAttribute("photoID", picture.photoID);
	var imgTag = createTag('img', aTag);
	imgTag.src = './images/remove.gif';
	imgTag.alt = translate.prdset_btn_delete_pict;
	
	var iTag = createTag('span',tdTag);
	iTag.innerHTML = '<input type="hidden" class="field_priority" name="priority_'
					+picture.photoID
					+'" value="'
					+(tableTag.rows.length-2)
					+'"/>';
	
//	getLayer("product-pictures-container").insertBefore(trTag, getLayer("upload-picture-container"));

	Behaviour.apply();
	dragsort.makeListSortable(tableTag);
	
/*	return;
	//old code

	var liTag = document.createElement('li');
	
	liTag.id = "picture-container-"+picture.photoID;
	
	var tblTag = createTag('table', liTag);
	
	var trTag = tblTag.insertRow(tblTag.rows.length);
	var tdTag = trTag.insertCell(trTag.cells.length);
	tdTag.className = "img_container";

	var aTag = createTag('a', tdTag);
	aTag.href = picture.large_picture.url;
	aTag.className = "new_window";
	aTag.setAttribute("wnd_width", picture.large_picture.width);
	aTag.setAttribute("wnd_height", picture.large_picture.height);
	
	var imgTag = createTag('img', aTag);
	imgTag.src = picture['thumbnail_url'];

	var trTag = tblTag.insertRow(tblTag.rows.length);
	var tdTag = trTag.insertCell(trTag.cells.length);
	
	if(is_null(picture.is_default)){
		
		var aTag = createTag('a', tdTag);
		aTag.href = "#set_default";
		aTag.className = "set_default_picture_handlers";
		aTag.setAttribute("photoID", picture.photoID);
		aTag.innerHTML = translate.prdset_btn_setdefault_pict;

		var spanTag = createTag('span', tdTag);
		spanTag.innerHTML = ' ';
	}
	
	var aTag = createTag('a', tdTag);
	aTag.href = "#delete_picture";
	aTag.className = "delete_picture_handlers";
	aTag.setAttribute("photoID", picture.photoID);
	var imgTag = createTag('img', aTag);
	imgTag.src = './images/remove.gif';
	imgTag.alt = translate.prdset_btn_delete_pict;
	
	getLayer("product-pictures-container").insertBefore(liTag, getLayer("upload-picture-container"));

	Behaviour.apply();*/
}

Behaviour.register({
	'a.delete_picture_handlers': function(element){
		
		element.onclick = function(){

			if(!window.confirm(translate.prdset_msg_confirm_pict_delete))return false;
			var req = new JsHttpRequest();
			var photoID = this.getAttribute('photoID');

			picts_removeImage(photoID);
			
			req.onreadystatechange = function(){
				
    			if (req.readyState != 4)return;
				var pictures_container = getLayer("product-pictures-container");
		  		getLayer('set-default').value = pictures_container.rows.length>1?0:1;
  				if(req.responseText)alert(req.responseText);
  		
				if(!is_null(req.responseJS) && req.responseJS._AJAXMESSAGE){
					var msgEntry = new Message();
					msgEntry.init(req.responseJS._AJAXMESSAGE);
					if(!msgEntry.isSuccess()){
			  			alert(msgEntry.getMessage());
						return;
					}
 				}
 				
			};
		  
			try {
				req.open('GET', document.location.href.replace(/\#.*$/, '')+"&caller=1&initscript=ajaxservice", true);
				req.send({'action': 'delete_picture','photoID':photoID});
			} catch ( e ) {
				catchResult(e);
			} finally {	;}
			return false;
		};
	},
	'a.set_default_picture_handlers': function(element){
		
		element.onclick = function(){
			
		  var req = new JsHttpRequest();
		  var photoID = this.getAttribute('photoID');
		  
		  req.onreadystatechange = function(){
				
        		if (req.readyState != 4)return;
        
     			if(req.responseText)alert(req.responseText);
				if(!is_null(req.responseJS) && req.responseJS._AJAXMESSAGE){
    			
    				var msgEntry = new Message();
    				msgEntry.init(req.responseJS._AJAXMESSAGE);
    			
    				if(!msgEntry.isSuccess()){
      					alert(msgEntry.getMessage());
    					return;
    				}
    			}
    		
    			picts_setDefault(photoID);
			};
		  
		try {
			req.open(null, document.location.href.replace(/\#.*$/, '')+"&caller=1&initscript=ajaxservice", true);
			req.send({'action': 'set_default_picture','photoID':photoID, 'productID':getLayer('product-id').value});
		} catch ( e ) {
			catchResult(e);
		} finally {	;}
		
		return false;
		};
	},
	'.prd_metakeywords': function(element){
		
		element.onfocus = function(){
		
			if(this.value)return;
			
			var lang = this.getAttribute('lang');
			var objTags = getLayer("tags_"+lang);
			this.value = objTags.value;
		};
	},
	'#upload-url': function(element){
		
		element.onfocus = function(){
		
			if(this.value!='URL')return;
			this.value = '';
		};
		element.onblur = function(){
		
			if(this.value&&this.value!='http://')return;
			this.value = 'URL';
		};
	},
	'#image-source-file':function(element){
		element.onclick = function(){
			getLayer('upload-browse').disabled = !this.checked;
			getLayer('upload-url').disabled = this.checked;
		};
	},
	'#image-source-url':function(element){
		element.onclick = function(){
			getLayer('upload-browse').disabled = this.checked;
			getLayer('upload-url').disabled = !this.checked;
		};
	},
	'.prd_metadescription': function(element){
		
		element.onfocus = function(){
		
			if(this.value)return;
			
			var lang = this.getAttribute('lang');

			this.value = tinyMCE.getContent("mce_editor_description_"+lang).replace(/<\/?[^>]+>|\s+$/g, '');
		};
	},
	'.remove_appendedcategory_handler': function(element){
		
		element.onclick = function(){
		
			var catBlock = getLayer("appended-category-"+this.getAttribute('categoryID'));
			catBlock.parentNode.removeChild(catBlock);
			beforeUnloadHandler_contentChanged = true;
		};
	},
	'.remove_relatedproduct_handler': function(element){
		
		element.onclick = function(){
		
			var catBlock = getLayer("related-product-"+this.getAttribute('productID'));
			catBlock.parentNode.removeChild(catBlock);
			beforeUnloadHandler_contentChanged = true;
		};
	},
	'#tags-container * input': function(element){
		
		element.onfocus = function(){
		
			focused_tags_field = this;
		}
	},
	'#product-settings-form input': function(e){
		e.onchange = function(){
			if(this.id != 'upload-browse')beforeUnloadHandler_contentChanged = true;
		};
	},
	'#product-settings-form textarea': function(e){
		e.onchange = function(){
			beforeUnloadHandler_contentChanged = true;
		};
	},
	'#product-settings-form select': function(e){
		e.onchange = function(){
			beforeUnloadHandler_contentChanged = true;
		};
	},
	'#btn-duplicate-product': function(e){
		e.onclick = function(){
			getLayer('action-name').value = 'duplicate_product';
		}
	}
});

function prdset_addAppendedCategory(categoryID, name){
	
	if(getLayer('appended-category-'+categoryID)){
		
		var catBlock = getLayer('appended-category-'+categoryID);
		catBlock.parentNode.removeChild(catBlock);
		return false;
	}
	
	var objCatBlock = document.createElement('div');
	objCatBlock.id = 'appended-category-'+categoryID;
	var objSpan = createTag('span', objCatBlock);
	objSpan.innerHTML = name;
	var objInput = createTag('input', objCatBlock);
	objInput.name = 'appended_categories[]';
	objInput.value = categoryID;
	objInput.style.display = "none";
	var objA = createTag('a', objCatBlock);
	objA.href = '#remove_appendedcategory';
	objA.className = "remove_appendedcategory_handler";
	objA.setAttribute('categoryID', categoryID);
	objA.onclick = function(){
		
		var catBlock = getLayer("appended-category-"+this.getAttribute('categoryID'));
		catBlock.parentNode.removeChild(catBlock);
	};
	var objRemoveImg = createTag('img', objA);
	objRemoveImg.src = 'images/remove.gif';
	objRemoveImg.alt = translate.DELETE_BUTTON;
	objRemoveImg.border = 0;
	objRemoveImg.hspace = 6;
	
	getLayer("appendedcategories-block").appendChild(objCatBlock);
	
	return true;
}

function prdset_addRelatedProduct(productID, name){
	
	if(getLayer('related-product-'+productID)){
		var objBlock = getLayer('related-product-'+productID);
		objBlock.parentNode.removeChild(objBlock);
		return false;
	}
	
	var objCatBlock = document.createElement('div');
	objCatBlock.id = 'related-product-'+productID;
	var objSpan = createTag('span', objCatBlock);
	objSpan.innerHTML = name;
	var objInput = createTag('input', objCatBlock);
	objInput.name = 'related_products[]';
	objInput.value = productID;
	objInput.style.display = "none";
	var objA = createTag('a', objCatBlock);
	objA.href = '#remove_relatedproduct';
	objA.className = "remove_relatedproduct_handler";
	objA.setAttribute('productID', productID);
	objA.onclick = function(){
		
		var catBlock = getLayer("related-product-"+this.getAttribute('productID'));
		catBlock.parentNode.removeChild(catBlock);
	};
	var objRemoveImg = createTag('img', objA);
	objRemoveImg.src = 'images/remove.gif';
	objRemoveImg.alt = translate.DELETE_BUTTON;
	objRemoveImg.border = 0;
	objRemoveImg.hspace = 6;
	
	getLayer("related-products-container").appendChild(objCatBlock);
	return true;
}

var relatedProductList = {

	'products_ids': {},
	
	'load': function(){
		
		var objInputs = getElementsByClass('',getLayer('related-products-container'),'input');
		for(var j=objInputs.length-1; j>=0; j--){
		
			this.products_ids[objInputs[j].value] = 1;
		}
	},
	
	'checkedProduct': function(objProductLink){
	
		if(this.products_ids[objProductLink.getAttribute('productID')]){
		
			var p = objProductLink.parentNode;
			var wnd = objProductLink.wnd;
			var objChecked = wnd.document.createElement('img');
			objChecked.src = 'images_common/checked.gif';
			objChecked.hspace = 4;
			p.insertBefore(objChecked, objProductLink);
		}else{
		
			var p = objProductLink.parentNode;
			var objChecked = getElementsByClass('', p, 'img');
			if(objChecked.length){
				p.removeChild(objChecked[0]);
			}
		}
	}
};

function loadProductList(_node, _wnd, offset){
			  var req = new JsHttpRequest();
			  var node = _node;
			  var wnd = _wnd;
			  var productID = getLayer('product-id').value;
			  			  
			  req.onreadystatechange = function(){
					
	        	if (req.readyState != 4)return;
	        	
	        	node.hideLoadingMsg();
	     		if(req.responseText)alert(req.responseText);
				
				if(!req.responseJS.products || !req.responseJS.products.length)return;
					
				var objLi = getLayer(node.getID()+'_end', wnd);
				if(wnd.productsBubble && wnd.productsBubble.length){ 
					for(var _lp=wnd.productsBubble.length-1; _lp>=0; _lp--){
						if(wnd.productsBubble[_lp].parentNode)wnd.productsBubble[_lp].parentNode.removeChild(wnd.productsBubble[_lp]);
					}
					wnd.productsBubble = null;
				}

					var objDiv = wnd.document.createElement('div');
					objDiv.className = "productsBubble";
					
					chooseRelatedProduct = function(){
						
						var res = prdset_addRelatedProduct(this.getAttribute('productID'),this.getAttribute('productName'));
						relatedProductList.products_ids[this.getAttribute('productID')] = res?1:null;
						relatedProductList.checkedProduct(this);
					};
					with(req.responseJS){
						
						relatedProductList.load();
						var productID = getLayer('product-id').value;
						var cnt = 0;
						for(var k=0,k_max=products.length; k<k_max; k++){
						
							if(productID == products[k].productID)continue;
							cnt++;
							var _objDiv = createTag('div', objDiv,wnd);
							var objA = createTag('a', _objDiv,wnd);
							objA.className = 'relProduct';
							objA.innerHTML = products[k].name;
							objA.setAttribute('productID', products[k].productID);
							objA.setAttribute('productName', products[k].name);
							objA.onclick = chooseRelatedProduct;
							objA.wnd = wnd;
							
							relatedProductList.checkedProduct(objA);
						}
					}
					
					if(cnt>0){
						var objHidePrdsLink = wnd.document.createElement('input');
						objHidePrdsLink.type = 'button';
						objHidePrdsLink.style.fontSize = '70%';
						objHidePrdsLink.style.margin = '2px';
						objHidePrdsLink.onclick = function(){
							if(wnd.productsBubble && wnd.productsBubble.length){
								for(var _lp=wnd.productsBubble.length-1; _lp>=0; _lp--){
									if(wnd.productsBubble[_lp].parentNode)wnd.productsBubble[_lp].parentNode.removeChild(wnd.productsBubble[_lp]);
								}
								wnd.productsBubble = null;
							}
						};
						objHidePrdsLink.value = translate.prdset_btn_hide_products;
						objLi.innerHTML = '';
						objLi.style.display = 'inline';
						objLi.appendChild(objHidePrdsLink);
						objLi.parentNode.insertBefore(objDiv, objLi.nextSibling);
						wnd.productsBubble = [];
						wnd.productsBubble.push(objDiv);
						wnd.productsBubble.push(objHidePrdsLink);
						
						with(req.responseJS){
							if(req.responseJS.prev_offset || req.responseJS.next_offset){
							
								var objOffsetBlock = wnd.document.createElement('div');
								objOffsetBlock.style.marginLeft = '30px';
								if(req.responseJS.prev_offset){
									var objPrevOffset = wnd.document.createElement('input');
									objPrevOffset.type = 'button';
									objPrevOffset.style.fontSize = '70%';
									objPrevOffset.style.margin = '2px';
									objPrevOffset.setAttribute('offset',prev_offset);
									objPrevOffset.wnd = wnd;
									objPrevOffset.node = node;
									objPrevOffset.onclick = function(){
										loadProductList(this.node, this.wnd, this.getAttribute('offset'));
									};
									objPrevOffset.value = translate.prdset_btn_prev_products;
									objOffsetBlock.appendChild(objPrevOffset);
								}
								if(req.responseJS.next_offset){
									var objNextOffset = wnd.document.createElement('input');
									objNextOffset.type = 'button';
									objNextOffset.setAttribute('offset',next_offset);
									objNextOffset.style.fontSize = '70%';
									objNextOffset.style.margin = '2px';
									objNextOffset.wnd = wnd;
									objNextOffset.node = node;
									objNextOffset.onclick = function(){
										loadProductList(this.node, this.wnd, this.getAttribute('offset'));
									};
									objNextOffset.value = translate.prdset_btn_next_products;
									objOffsetBlock.appendChild(objNextOffset);
								}
								objLi.parentNode.insertBefore(objOffsetBlock, objDiv.nextSibling);
								wnd.productsBubble.push(objOffsetBlock);
							}
						}
					}
				};
			  
			  	node.showLoadingMsg(translate.prdset_msg_loading_products);
				try {
					req.open(null, wnd.document.location.href.replace(/\#.*$/, '')+"&caller=1&initscript=ajaxservice", true);
					req.send({'action': 'getCategoryProducts', 'categoryID': node.getSetting('categoryID'), 'productID': productID, 'offset': offset});
				} catch ( e ) {
					catchResult(e);
				} finally {;}
}

var categoryTreeManager = {
	
	'appended_categories': {},
	
	'isAppendedCategory': function(node, wnd){
	
		var catID = node.getSetting('categoryID');
		if(this.appended_categories[catID]){
			var objEnd = getLayer(node.ID+'_begin', wnd);
			objEnd.innerHTML = '<img src="images_common/checked.gif" alt="" />&nbsp;';
		}
	},
	
	'show_tree': function(action){
		
		if(action == 'add_appendedcategory'){
		
			this.appended_categories = {};
			var objInput = getElementsByClass('', getLayer('appendedcategories-block'),'input');
			for(var k=objInput.length-1; k>=0; k--){
			
				this.appended_categories[objInput[k].value] = 1;
			}		
		}
		var url = set_query('?ukey=category_tree&js_action='+action+'&productID=');
		 	
		sswgt_CartManager.shop_url = conf_full_shop_url;
		sswgt_CartManager.show(url, 550, 500); 	
	},
	'hide_tree': function(){
		
		sswgt_CartManager.hide();
	},
	
	'actions': {
		'choose_parentcategory': {
			'onclick': function(node){
				
				var breadCrumbs = node.getSetting('name');
				var p = node.ParentNode;
				while(p){
					breadCrumbs = p.getSetting('name')+"&nbsp;&gt;&nbsp;"+breadCrumbs;
					p = p.ParentNode;
				}
				getLayer('product-category-categoryID').value = node.getSetting('categoryID');
				getLayer('product-category-name').innerHTML = breadCrumbs;
				beforeUnloadHandler_contentChanged = true;
				
				categoryTreeManager.hide_tree();
			}
		},
		'add_appendedcategory': {
			'onclick': function(node, wnd){
				
				var breadCrumbs = node.getSetting('name');
				var p = node.ParentNode;
				while(p){
					breadCrumbs = p.getSetting('name')+"&nbsp;&gt;&nbsp;"+breadCrumbs;
					p = p.ParentNode;
				}
				var res = prdset_addAppendedCategory(node.getSetting('categoryID'), breadCrumbs);
				var objEnd = getLayer(node.ID+'_begin', wnd);
				objEnd.innerHTML = res?'<img src="images_common/checked.gif" alt="" />&nbsp;':'';
				beforeUnloadHandler_contentChanged = true;
			}
		},
		'add_relatedproducts': {
			'onclick': function(node, wnd){
				loadProductList(node, wnd, 0);
			}
		}
	},
	
	'eval': function(action, handler, node, wnd){
		
		this.actions[action][handler](node, wnd);
	}
};
	
var optionsSettingsManager = {
	
	settingsWnd: null,
	optionID: null,
	
	showSettings: function(objA){
		
		this.optionID = objA.getAttribute('optionID');
		getLayer('opt_nval_'+this.optionID).checked = true;

		sswgt_CartManager.shop_url = conf_full_shop_url;
		var productID = getLayer("product-id").value;
		var url = objA.href.replace(/&productID=\d+/,'');
		if(productID)url += '&productID='+productID;
		sswgt_CartManager.show(url, 700, 500);
		return false;
	},
	
	hideSettings: function(){
		
		sswgt_CartManager.hide();
	}, 
	
	setProductID: function(productID){
		
 		getLayer("product-id").value = productID;
	}, 
	
	setOptionValuesNum: function(num){
		
		getLayer('option-values-num-'+this.optionID).innerHTML = num;
	}
};

getLayer('lnk-simple-options').onclick = function(){ prdset_selectTab('simple');};
getLayer('lnk-advanced-options').onclick = function(){prdset_selectTab('advanced');};
getLayer('lnk-custom-params').onclick = function(){prdset_selectTab('customparams');};
getLayer('product-isprogram-handler').onclick = ProductIsProgramHandler;
if(getLayer('show-options-handler'))getLayer('show-options-handler').onclick = ConfiguratorHideTable;
getLayer('btn-save-product').onclick = function(){beforeUnloadHandler_contentChanged = false;};
getLayer('choose-parentcategory-handler').onclick = function(){categoryTreeManager.show_tree('choose_parentcategory');};
getLayer('add-appended-parent-handler').onclick = function(){categoryTreeManager.show_tree('add_appendedcategory');};
getLayer('add-related-product-handler').onclick = function(){categoryTreeManager.show_tree('add_relatedproducts');};
getLayer('do-upload-handler').onclick = function(){
			
	if(!getLayer("upload-browse").value&&((getLayer("upload-url").value=='URL')||(getLayer("upload-url").value=='http://')))return;
	//try {
	getLayer('field-skip-image-upload').value = "1";
	getLayer('do-upload-handler').style.display = "none";
	getLayer('processing-image').style.display = "";
	//SEE:
	var btn_save = getLayer('btn-save-product');
	if(btn_save){
		btn_save.disabled = true;
		btn_save.style.color = "#ccc";	
	}
		
		
    var req = new JsHttpRequest();
    req.onreadystatechange = function() {

       if (req.readyState == 4) {

				getLayer('action-name').value = 'save_product';
				getLayer('do-upload-handler').style.display = "";
				getLayer('processing-image').style.display = "none";
				//SEE:
				getLayer('btn-save-product').disabled = false;
				getLayer('btn-save-product').style.color = "black";
				getLayer('field-skip-image-upload').value = "0";
				
      		if(req.responseText)alert(req.responseText);
				if(is_null(req.responseJS))return;

      		if(req.responseJS._AJAXMESSAGE){
      			
      			var msgEntry = new Message();
      			msgEntry.init(req.responseJS._AJAXMESSAGE);
      			
      			if(!msgEntry.isSuccess()){
       			alert(msgEntry.getMessage());
      				return;
      			}
      			
      		}
     			
      		if(!req.responseJS.picture)return;
      		var pictures_container = getLayer("product-pictures-container");
      		getLayer('set-default').value = pictures_container.rows.length>1?0:1;

      		picts_addImage(req.responseJS.picture);
      		
      		var who = getLayer("upload-browse");	
			who2 = document.createElement('input');
			var att= new Array('type', 'size', 'id', 'name');
			for(i = 0;i< att.length;i++){
				who2.setAttribute(att[i],who.getAttribute(att[i]));
			}

			who.parentNode.replaceChild(who2,who);
     		
      		getLayer("product-id").value = req.responseJS.productID;
      		getLayer("make-slug-id").value = req.responseJS.productID;
      		getLayer("upload-url").value = 'URL';
      		

			}
    };
    //SEE:
	getLayer('action-name').value = 'upload_picture';
	getLayer('upload-priority').value = getLayer("product-pictures-container").rows.length-1;
	var old_default_container = getElementsByClass('default_picture', getLayer("product-pictures-container"),'tr');

	
	
	try {
		req.open('POST', document.location.href.replace(/\#.*$/, '')+"&caller=1&initscript=ajaxservice", true);
		req.send( { q: getLayer("product-settings-form") } );
	} catch ( e ) {
		catchResult(e);
		//getLayer('btn-save-product').disabled = false;
		getLayer('btn-save-product').style.color = "black";
		getLayer('field-skip-image-upload').value = "0";
	} finally {	;}
	
	return false;
};
