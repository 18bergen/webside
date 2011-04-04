/*
    class: BG18.autoBackup
    
*/

/*
    Constructor: autoBackup
    Initializes the object.
*/
BG18.autoBackup = function(backup_url) {

	this._backupurl = backup_url;
    
}

YAHOO.extend(BG18.autoBackup, BG18.base, {
        
    _className: "BG18.autoBackup",    
    _backupurl: '',
    _enabled: false,
    _editors: [],
    _backupInterval: 30,
    _timeSpent: 0,
    _timerInterval: 0,
    _firstStart: true,
    _prevData: [],
    _lastBackup: false,
    
    _standardInfoText: "Sikkerhetskopi blir tatt hvert 30de sekund hvis dokumentet har blitt endret.",

    /* Ids of html elements */
    _elStatusText: 'backupstatus',
    	
	addEditor: function(lang, instance) {
		this._editors.push({
			'lang': lang, 
			'editor': instance
		});
	},
	
	start: function() {
		if (!this._enabled) {
			this._enabled = true;
			this._timeSpent = 0;
			var self = this;
			this._timerInterval = window.setInterval(function() { self._tick(); }, 1000);	
			
			if (this._firstStart) {
				this._firstStart = false;
				this._prevData = this._getCKdata();
				$(this._elStatusText).innerHTML = this._standardInfoText;
			}
		}
	},

	stop: function() {
		if (this._enabled) {
			this._enabled = false;
			window.clearInterval(this._timerInterval);
		}
	},
	
	_twoDigits: function(n) {
		if (n < 10) return '0' + n;
		else return n;
	},
		
	_tick: function() {
		this._timeSpent++;
		var diff = this._backupInterval - this._timeSpent;
		var txt = this._standardInfoText + '<br />';
		if (this._lastBackup == false)
			txt += '<em>Status:</em> Sikkerhetskopi ikke tatt enda.';
		else
			txt += '<em>Status:</em> Nyeste sikkerhetskopi ble tatt klokken ' +
				this._twoDigits(this._lastBackup.getHours())+':'+this._twoDigits(this._lastBackup.getMinutes())+':'+this._twoDigits(this._lastBackup.getSeconds()) + '.';
		$(this._elStatusText).innerHTML = txt; 
		if (diff <= 0) this._backup();
	},
	
	_backup: function(){	
		this.stop();		
		var data = this._getCKdata();
		var dataChanged = false;
		for (var i = 0; i < data.length; i++) {
			if (data[i].changed) {
				dataChanged = true;
				console.log('Edition "'+data[i].lang+'" changed');
			}
		}
		this._prevData = data;
		if (dataChanged) {
			this._makeAjaxRequest(this._backupurl, data);
			$(this._elStatusText).innerHTML = this._standardInfoText + '<br />' + 
				'<em>Status:</em> Lagrer sikkerhetskopi…';
		} else {
			this.start();
		}
	},

	_getCKdata: function() {
		var data = [];
		var changed,xhtml;
		for (var i = 0; i < this._editors.length; i++) {
			xhtml = this._editors[i].editor.getData();
			changed = ((this._prevData.length == this._editors.length) && (this._prevData[i].content != xhtml));
			data.push({
				'lang':this._editors[i].lang, 
				'content': xhtml,
				'changed': changed
			});				
		}
		return data;
	},
	
	/*,

		var success = function(t){ 
			fastTekst2 = t.responseText;
			$(target).innerHTML = fastTekst + "<br />\\n" + fastTekst2;
			timeSpent = 0;
			this._timerInterval = setInterval(autoBackupTick, 1000);	
		}
		if (pars != oldPars) {
			$(target).innerHTML = fastTekst + "<br />\\nLagrer backup...";
			var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});
			oldPars = pars;
		} else {
			$(target).innerHTML = fastTekst + "<br />\\nIngen endringer siden sist.";
			timeSpent = 0;
			this._timerInterval = setInterval(autoBackupTick, 1000);	
		}
*/


	_ajaxSuccess: function(ajax) {
		if (ajax['error'] != '0') {
			$(this._elStatusText).innerHTML = '<div style="font-weight:bold;color:red;background:white;padding:10px;border: 1px solid red;">'+ajax['error']+'</div>';
		} else {
			var success = false;
			for (var i = 0; i < this._editors.length; i++) {
				success = false;
				for (var j = 0; j < ajax.backupsDone.length; j++) {
					if (this._editors[i].lang == ajax.backupsDone[j]) success = true;
				}
				if (success == false) {
					$(this._elStatusText).innerHTML = '<div style="font-weight:bold;color:red;background:white;padding:10px;border: 1px solid red;">Backup ble ikke tatt for språket "'+this._editors[i].lang+'"!</div>';
					return;
				}
			}
			this._lastBackup = new Date();
			this.start();
		}
	},
	
	_ajaxFailure: function(result) {
		$(this._elStatusText).innerHTML = '<div style="font-weight:bold;color:red;background:white;padding:10px;border: 1px solid red;">Det oppstod en feil: '+result+'</div>';
	},
	
	_makeAjaxRequest: function(url, parameters) {
		//console.info('Making AJAX request…');
		//$(this._elText).innerHTML = 'Oppdaterer…';
		var self = this;
		YAHOO.util.Connect.asyncRequest('POST',url, { 
			success: function(o){
				try {
					var json = YAHOO.lang.JSON.parse(o.responseText);
					self._ajaxSuccess(json);
				} catch (x) {
					self._ajaxFailure(x+'<br />'+o.responseText);
				}
			},
			failure: function(o){ self._ajaxFailure(o); }		
		}, JSON.encode(parameters));
	}
	
});
