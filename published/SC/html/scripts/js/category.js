function submitProductsComparison(){
	
	var objForm = getFormByElem(this);
	var cmpCtrls = getElementsByClass('ctrl_products_cmp');
	var cmpPrds = getElementByClass('comparison_products',objForm);
	cmpPrds.value = '';
	for(var cmp_k = cmpCtrls.length-1; cmp_k>=0; cmp_k--){
		
		if(!cmpCtrls[cmp_k].checked)continue;
		cmpPrds.value += ' '+cmpCtrls[cmp_k].value;
	}
	if ( cmpPrds.value != '' )objForm.submit();
}

Behaviour.register({
	'input.hndl_submit_prds_cmp': function(e){
		e.onclick = submitProductsComparison;
	}
});