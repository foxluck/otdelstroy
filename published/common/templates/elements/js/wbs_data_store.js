/***********
	WbsDataStore - занимается хранением и обработкой данных, общается напрямую с reader.	
**********/
WbsDataStore = newClass(WbsObservable, {
	constructor: function (config) {
		this.records = new Array ();
		this.recordClass = config.recordClass ? config.recordClass : WbsRecord;
		this.offset = 0;
		this.config = config;
		this.params = {};
		if (this.config.reader) {
			this.reader = this.config.reader;
			this.reader.addListener("success", this.onRecordsLoaded, this);
		}	
		this.idProperty = this.config.idProperty;
		
		this.addEvents ({
			"dataChanged" : true,
			"recordChanged" : true,
			"startLoading" : true,
			"finishLoading" : true
		});
	},

	add: function (record) {
		record.addListener("modified", this.recordModified, this);
		this.records.push(record);
	},
	
	unshift: function (record) {
		this.records.unshift(record);
	},
		
	clear: function () {
		this.records = new Array ();
	},
		
	getLength: function () {
		return this.records.length;		
	},
		
	hasRecords: function () {
		return this.records.length > 0;
	},
		
	getRecordByIndex: function(index) {
		return this.records[index];
	},
		
	setSorting: function (column, direction) {
		this.reader.setSorting(column, direction);
	},
		
	setParams: function(params) {
		this.params = params;
	},
		
	setOffset: function (offset, limit) {
		this.offset = offset;
		this.reader.addBaseParams({offset: offset, limit: limit});
	},
		
	recordModified: function(record) {
		this.fireEvent("recordModified", record);
	},
		
	getOffset: function() {
		return this.offset;
	},
	
	getTotal: function() {
		return this.total;
	},
		
	onRecordsLoaded: function(responseData, records, data) {
		this.fireEvent("finishLoading");
		
		if (responseData.errorStr) {
			WbsCommon.showError(responseData);
			return false;
		}
		if (responseData)
			this.responseData = responseData;
		
		this.clear();
		if (data.total)
			this.total = data.total;
		if (records)
		for (var i = 0; i < records.length; i++) {
			records[i].id = records[i][this.idProperty];
			this.add(new this.recordClass(records[i]));
		}
		this.fireEvent("dataChanged", this);
	},
		
	load: function(params) {
		this.fireEvent("startLoading");
		if (!params)
			params = {};
		params.extend (this.params);
		this.reader.read(params);
	}
});