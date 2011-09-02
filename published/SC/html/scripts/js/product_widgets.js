Behaviour.register({
	'a.pwgt_hndl_widget': function(e){
		e.onclick = function(){
			var productID = this.getAttribute('rel');
			var objPrdWgtBlock = getLayer('pwgt-prdwgt-block-'+productID);
			var collapsed = objPrdWgtBlock.className.search(/\s?collapsed/)!=-1;
			
/*			var objPrdRow = getLayer('pwgt-prdrow-'+productID);
*/			
			if(!collapsed){
				objPrdWgtBlock.className += " collapsed";
			}else{
			
				var objWgtRow = getLayer('pwgt-wgtrow-'+productID);
				var objPrdTextArea = getElementByClass('prd', objWgtRow, 'textarea');
				var objPrdDivPreview = getElementByClass('prd', objWgtRow, 'div');
				objPrdTextArea.value = '<iframe src="'+this.href+'" frameborder="0" width="'+iframe_width+'" height="'+iframe_height+'"></iframe>';
				objPrdDivPreview.innerHTML = objPrdTextArea.value;
				objPrdWgtBlock.className = objPrdWgtBlock.className.replace(/\s?collapsed/, '');
			}
			
			return false;
		}
	}
});