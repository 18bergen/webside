var BG18 = {

    _className: "BG18"
    
}

/*
	Class: BG18.base
	BG18.base is a basis-class that most classes extend. It contain functionality that
	most or all classes need, like sending and receiving JSON objects over AJAX 
	and get localized strings.
*/

BG18.base = function() {
	this.ajaxCallback = {
    	ref: this,
        success: function(o){ this.ref.ajaxSuccess(o); }, 
        failure: function(o){ this.ref.ajaxFailure(o); }    	
    }
}
BG18.base.prototype = {
   
    /* _______________________________ BEGIN AJAX JSON FUNCTIONS _______________________________ */
    
    ajaxTransactions: [],
    ajaxStatusDiv: null,
    ajaxCallbacks: [],
    ajaxCallbackObjs: [],
    ajaxCallback: null,
    ajaxTransactionId: 0,
    
	/*
		Method: ajaxReq
		A method for doing Ajax requests with JSON objects.
		
		Parameters:
			url 		- (string) Request url
			callback 	- (string) Callback function
			postObj		- (object) (optional) JSON object to send to server
			callbackObj	- (object) (optional) Object to send to callback function
		
		Returns:
			Transaction id
	*/
    ajaxReq: function(url, callback, postObj, callbackObj) {
    	var self = this;
    	if (this.ajaxStatusDiv != null) {
    		YAHOO.util.Dom.setStyle(this.ajaxStatusDiv, 'display', 'block');
		}
		BG18.ajaxTransactionId++;
		// console.log('transaction id '+BG18.ajaxTransactionId+': '+action);
        this.ajaxCallbacks[BG18.ajaxTransactionId] = callback;
        this.ajaxCallbackObjs[BG18.ajaxTransactionId] = callbackObj;
        //if (postObj == null) postObj = { action: action };
        //else postObj.action = action;

        YAHOO.util.Connect.asyncRequest('POST', url, {
       	 	success: function(o){ self.ajaxSuccess(o); }, 
        	failure: function(o){ self.ajaxFailure(o); }    	
    	}, YAHOO.lang.JSON.stringify(postObj));

        return BG18.ajaxTransactionId;
    },
    
    /*
		Method: ajaxSuccess
		Called if our Ajax-transaction was successful.
	*/
    ajaxSuccess: function(o) {
    	//BG18.log('transaction id '+o.tId+' complete');
    	
    	if (this.statusDiv != null) {
    		YAHOO.util.Dom.setStyle(this.statusDiv, 'display', 'none');
		}
    	if (o.status == 200) {
            try {
            	//var json = eval('(' + o.responseText + ')');
            	var json = YAHOO.lang.JSON.parse(o.responseText);
            } catch(err) {
            	BG18.log(o.responseText,'warn');
            	var json = { error: 'Serveren returnerte et uleselig svar!' };
        	}
            if (json.error != 0) {
            	if (json.error == 'not_logged_in') {
		            BG18.log('Du er ikke lenger logget inn. Du kan ha blitt logget ut hvis du har vært inaktiv lenge.', 'error', true,true);
            	} else {
		            BG18.log('Forespørselen ga feilen '+json.error,'error');
		        }
            }
            if (o.tId > this.ajaxCallbacks.length-1) {
            	BG18.log('AJAX-transaksjonen hadde tId='+o.tId+', men høyeste forespørsel'+
            		' sendt hadde tId='+(this.ajaxCallbacks.length-1),'error');
            } else {
	            var myFunc = this.ajaxCallbacks[o.tId];
   	         	//console.log(myFunc);
   	         	if (this.ajaxCallbackObjs[o.tId] != null) {
	   	         	if (myFunc != null) this[myFunc](o.tId,json,this.ajaxCallbackObjs[o.tId]);
	   	        } else if (typeof myFunc == 'string'){
	   	         	if (myFunc != null) this[myFunc]([o.tId,json]);
	   	        } else {
	   	         	if (myFunc != null) myFunc(json);
	   	        }
   	         }
        } else {
            BG18.log('Serveren returnerte feil-koden '+o.status,'error');
        }
    },
    
    /*
		Method: ajaxSuccess
		Called if our Ajax-transaction failed.
	*/
    ajaxFailure: function(o) {
    	BG18.log('transaction id '+o.tId+' failed');
    	
        BG18.log('Serveren kunne ikke nåes pga. nettverksproblemer!','error');    
    	if (this.statusDiv != null) {
    		YAHOO.util.Dom.setStyle(this.statusDiv, 'display', 'none');
		}
		var json = {'error': 'Nettverksproblemer'};
		
		if (o.tId > this.ajaxCallbacks.length-1) {
			BG18.log('AJAX-transaksjonen hadde tId='+o.tId+', men høyeste forespørsel'+
				' sendt hadde tId='+(this.ajaxCallbacks.length-1),'error');
		} else {
			var myFunc = this.ajaxCallbacks[o.tId];
			//console.log(myFunc);
			if (this.ajaxCallbackObjs[o.tId] != null) {
				if (myFunc != null) this[myFunc](json,this.ajaxCallbackObjs[o.tId]);
			} else if (typeof myFunc == 'string'){
				if (myFunc != null) this[myFunc](json);	   	        
			} else {
				if (myFunc != null) myFunc(json);
			}
		}

    }

    /* ________________________________ END AJAX JSON FUNCTIONS ________________________________ */

}
