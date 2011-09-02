WbsRecordset = newClass(null, {
	records: [],
	constructor: function(records) {
		this.records = (records) ? records : [];
	},
		
	add: function(record) {
		this.records.push(record);
	},
		
	getRecords: function() {
		return this.records;
	},
		
	getRecord: function(index) {
		return this.records[index];
	},
		
	getCount: function() {
		return this.records.length;
	},
		
	isEmpty: function() {
		return this.getCount() < 1;		
	},
	
	getIds: function() {
		var ids = new Array ();
		for (var i = 0; i < this.records.length; i++) {
			ids.push(this.records[i].getId());
		}
		return ids;
	},
		
	getMinRights: function() {
		var minRights = 7;
		for (var i = 0; i < this.records.length; i++) {
			if (this.records[i].Rights < minRights)
				minRights = this.records[i].Rights;
		}
		return minRights;
	},
		
	canWrite: function() {
		return WbsRightsMask.canWrite(this.getMinRights());
	}
});