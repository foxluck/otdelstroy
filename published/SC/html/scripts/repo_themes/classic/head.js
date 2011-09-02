// JavaScript Document
var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari"
		}
	],
	dataOS : []
};

function roundElems(){
BrowserDetect.init();
if(BrowserDetect.browser == 'Safari'){
	var oldonload = window.onload;
	window.onload = function(){
		if(oldonload)oldonload();
Nifty("div.cpt_shopping_cart_info","");
Nifty("div.cpt_product_search","");
Nifty("div.bg-sidebar","br big");
Nifty("div.round-container","top big");
Nifty("div.col_header","transparent");
Nifty("div.cpt_product_search","transparent");
Nifty("div.news_subscribe","transparent");
Nifty("div.news_thankyou","transparent");


	}
}else{
Nifty("div.cpt_shopping_cart_info","");
Nifty("div.bg-sidebar","br big");
Nifty("div.round-container","top big");
Nifty("div#cat_advproduct_search","big");
Nifty("div.col_header","transparent");
Nifty("div.cpt_product_search","transparent");
Nifty("div.news_subscribe","transparent");
Nifty("div.news_thankyou","transparent");

}
}