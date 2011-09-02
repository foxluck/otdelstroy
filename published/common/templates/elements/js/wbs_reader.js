/**********
	Читает данные из источника. Пока в качестве источника реализован только Ajax-handler, потом нужно переписать
*********/

WbsReader = newClass(WbsObservable, {
	constructor: function(config) {
		this.config = config;
		this.baseParams = config.baseParams;
		if (!this.baseParams)
			this.baseParams = {};
		
		this.addEvents({
			"success" : true,
			"changeParams": true,
			"startReading" : true,
			"finishReading" : true
		});
	},
		
	addBaseParams: function (params) {
		var baseParams = this.baseParams;
		if (!baseParams)
			baseParams = [];
		for (var i in params) {
			baseParams[i] = params[i];
		}
		this.baseParams = baseParams;
		this.fireEvent("changeParams");
	},
	
	setSorting: function(column, direction) {
		this.addBaseParams({sortColumn: column, sortDirection: direction});
		this.baseParams.sortColumn = column;
	}, 
	
	read: function (addParams) {
		var params = {};
		for (var key in this.baseParams)
			params[key] = this.baseParams[key];
		if (addParams) {
			for (var key in addParams)
				params[key] = addParams[key];
			
		}		
		
		
		this.fireEvent("startReading");
		Ext.Ajax.request ({
			url: this.config.url,
			params: params,
			success: function (response) {
				var data = {};
				var responseData = Ext.decode(response.responseText);
				if (responseData.errorStr) {
					WbsCommon.showError(responseData);
					return false;
				}
				var records = (this.config.recordsProperty) ? responseData[this.config.recordsProperty] : responseData;
				if (this.config.totalProperty)
					data.total = responseData[this.config.totalProperty];
				this.fireEvent("success", responseData, records, data);
			},
			scope: this
		});		
	}
});