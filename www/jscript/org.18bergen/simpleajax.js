/*
    class: BG18.simpleAjax
    
*/

/*
    Constructor: simpleAjax
    Initializes the object.
*/
BG18.simpleAjax = function() {

}

YAHOO.extend(BG18.simpleAjax, BG18.base, {
        
    _className: "BG18.simpleAjax",
    _reqs: [],
    
    newRequest: function(url, obj, callback) {
    	var transId = this.ajaxReq(url, callback, obj, {});
    	this._reqs[transId] = callback;
    },
    
    ajaxSuccess: function(transId, json) {
    	alert(transId);
    },
    
    ajaxFailure: function() {
    	alert("failure");    
    }
	
});
