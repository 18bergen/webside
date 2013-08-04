/*
    class: AutoBackup
*/

function AutoBackup(backup_url) {

    var _className = "BG18.autoBackup",    
	    _enabled = false,
	    _editors = [],
	    _backupInterval = 30,
	    _timeSpent = 0,
	    _timerInterval = 0,
	    _firstStart = true,
	    _prevData = [],
	    _lastBackup = false,
	    _standardInfoText = "Sikkerhetskopi blir tatt hvert 30de sekund hvis dokumentet har blitt endret.",
	    _elStatusText = 'backupstatus',
	    that = this;
    	
	this.addEditor = function(lang, instance) {
		_editors.push({
			'lang': lang, 
			'editor': instance
		});
	};
	
	this.start = function() {
		if (!_enabled) {
			_enabled = true;
			_timeSpent = 0;
			_timerInterval = window.setInterval(function() { _tick(); }, 1000);	
			if (_firstStart) {
				_firstStart = false;
				_prevData = _getCKdata();
				$('#' + _elStatusText).html(_standardInfoText);
			}
		}
	};

	this.stop = function() {
		if (_enabled) {
			_enabled = false;
			window.clearInterval(_timerInterval);
		}
	};
	
	function _twoDigits(n) {
		if (n < 10) return '0' + n;
		else return n;
	};
		
	function _tick() {
		_timeSpent++;
		var diff = _backupInterval - _timeSpent;
		var txt = _standardInfoText + '<br />';
		if (_lastBackup == false)
			txt += '<em>Status:</em> Sikkerhetskopi ikke tatt enda.';
		else
			txt += '<em>Status:</em> Nyeste sikkerhetskopi ble tatt klokken ' +
				_twoDigits(_lastBackup.getHours())+':'+_twoDigits(_lastBackup.getMinutes())+':'+_twoDigits(_lastBackup.getSeconds()) + '.';
		$('#' + _elStatusText).html(txt); 
		if (diff <= 0) _backup();
	};

	function _getCKdata() {
		var data = [],
			changed,
			xhtml;
		for (var i = 0; i < _editors.length; i++) {
			xhtml = _editors[i].editor.getData();
			changed = ((_prevData.length == _editors.length) && (_prevData[i].content != xhtml));
			data.push({
				'lang':_editors[i].lang,
				'content': xhtml,
				'changed': changed
			});				
		}
		return data;
	};
		
	function _backup(){	
		that.stop();
		var request = { 'data': _getCKdata() };
		// @TODO: Tilpasse serveren for å motta objekt!!!!
		var dataChanged = false;
		for (var i = 0; i < request['data'].length; i++) {
			if (request['data'][i].changed) {
				dataChanged = true;
				console.info('Edition "'+request['data'][i].lang+'" changed');
			}
		}
		_prevData = request['data'];
		if (dataChanged) {
			$('#' + _elStatusText).html(_standardInfoText) + '<br />' + 
				'<em>Status:</em> Lagrer sikkerhetskopi…';
			console.info('Saving backup...');
			$.post(backup_url, request).done(_backupDone).error(_backupFailed);
		} else {
			that.start();
		}
	};

	function _backupDone(response) {
		console.info(response);
		var success = false;
		for (var i = 0; i < _editors.length; i++) {
			success = false;
			for (var j = 0; j < response.backupsDone.length; j++) {
				if (_editors[i].lang == response.backupsDone[j]) success = true;
			}
			if (success == false) {
				$('#' + _elStatusText).innerHTML = '<div style="font-weight:bold;color:red;background:white;padding:10px;border: 1px solid red;">Backup ble ikke tatt for språket "'+_editors[i].lang+'"!</div>';
				return;
			}
		}
		_lastBackup = new Date();
		that.start();
	};
	
	function _backupFailed(result) {
		$('#' + _elStatusText).html('<div style="font-weight:bold;color:red;background:white;padding:10px;border: 1px solid red;">Det oppstod en feil: '+result+'</div>');
	};
	
	
}
