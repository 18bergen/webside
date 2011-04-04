/*
    class: BG18.photoTagger
    
*/

/*
    Constructor: photoTagger
    Initializes the object.
*/
BG18.photoTagger = function(enable,tag_url,untag_url,myName,everybody) {

	this._enabled = (enable == true);
	this._tagurl = tag_url;
	this._untagurl = untag_url;
	this._myName = myName;
	this._everybody = everybody;
    
}

YAHOO.extend(BG18.photoTagger, BG18.base, {
        
    _className: "BG18.photoTagger",    
    _containedId: '',    
    _enabled: false,
    _tagurl: '',
    _untagurl: '',
    _myName: '',
    _everybody: [],
    _people: [],
    _dragging: false,
    _areaMouseOver: false,
    
    _frame: { x: 0, y: 0, width: 0, height: 0 },
        
    /* Ids of html elements */
    _elArea: 'pointer_div',
    _elCross: 'cross',
    _elText: 'tagged_users',
    _elForm: 'crossform',
    _elFormUser: 'tagged_user',
    _elFormBtnMe: 'btn_tagme',
    _elFormBtnCancel: 'btn_tagcancel',
    _elFormBtnSave: 'btn_tagsave',

    isUndefined: function(o) {
        return typeof o === 'undefined';
    },

	init: function(enable) {
		YAHOO.util.Event.onContentReady(this._elArea, this.onContentReady, this, true);
		//YAHOO.util.Event.onDOMReady(this.onContentReady,this,true);
	},
	
	getPersonById: function(uId) {
		for (var i = 0; i < this._people.length; i++) {
			if (this._people[i]['id'] == uId) return this._people[i];
		}
		return false;	
	},
	
	addPerson: function(uId, uName, uUrl, x, y, width, height) {
		this._people.push({
			'id': uId,
			'name': uName,
			'url': uUrl,
			'x': x,
			'y': y,
			'width': width,
			'height': height
		});
	},
	
	onContentReady: function(e) {

		YAHOO.util.Event.on(this._elFormBtnMe,'click',this.onBtnTagMeClick,this,true);
		YAHOO.util.Event.on(this._elFormBtnCancel,'click',this.onBtnCancelClick,this,true);
		YAHOO.util.Event.on(this._elFormBtnSave,'click',this.onBtnSaveClick,this,true);

		//YAHOO.util.Event.on(this._elArea,'click',this.onAreaClick,this,true);
		YAHOO.util.Event.on(this._elArea,'mouseover',this.handleMouseOver,this,true);
		YAHOO.util.Event.on(this._elArea,'mouseout',this.handleMouseOut,this,true);
		YAHOO.util.Event.on(this._elArea,'mousedown',this.handleMouseDown,this,true);
		YAHOO.util.Event.on(this._elArea,'mouseup',this.handleMouseUp,this,true);
		YAHOO.util.Event.on(this._elForm,'mouseover',function(event) {
			YAHOO.util.Event.stopEvent(event);
		});
		YAHOO.util.Event.on(this._elForm,'mouseout',function(event) {
			YAHOO.util.Event.stopEvent(event);
		});

		this.redrawText();
		
		this.makeAutoComplete();
		
	},
	
	makeAutoComplete: function() {
	
		var self = this;
		var oDS = new YAHOO.util.LocalDataSource(this._everybody);
		var acObj = new YAHOO.widget.AutoComplete(this._elFormUser, this._elFormUser+'_ac', oDS, {
			animVert: false,
			useIFrame: false,
			forceSelection: true,
			allowBrowserAutocomplete: false,
			useShadow: true
		});
        
		var self = this;
		this.addEditorKeyListener = new YAHOO.util.KeyListener(this._elFormUser, 
			{ keys:[YAHOO.util.KeyListener.KEY.ENTER, 
					YAHOO.util.KeyListener.KEY.ESCAPE] },
			{ fn:function(type, args, obj) {
				switch (args[0]) {
					case YAHOO.util.KeyListener.KEY.ENTER:
						self.savePerson();
						break;
					case YAHOO.util.KeyListener.KEY.ESCAPE:
						self.hideTagForm();
						break;
				}
			} } 
		);
		this.addEditorKeyListener.enable();
		acObj.containerExpandEvent.subscribe(this.addEditorACexpand, this, true);
		acObj.containerCollapseEvent.subscribe(this.addEditorACcollapse, this, true);	
	
	},

	addEditorACexpand: function() { this.addEditorKeyListener.disable(); },
	addEditorACcollapse: function() { this.addEditorKeyListener.enable(); },
	
	savePerson: function() {
		console.log("save person");	
	},
	
	redrawText: function() {
		var a,aDel,p,frame,innerframe,i;
		
		YAHOO.util.Event.purgeElement(this._elText, true);
		var elms = YAHOO.util.Dom.getElementsByClassName('tagged_person_frame','div',this._elArea);
		for (i = 0; i < elms.length; i++) {
			$(this._elArea).removeChild($(elms[i]));
		}		
		
		$(this._elText).innerHTML = '<span style="display:inline-block;vertical-align:middle;float:left;">På bildet: </span>';
		
		if (this._people.length == 0) {
			$(this._elText).innerHTML += '<em>Ukjent</em>';	
		}
		for (i = 0; i < this._people.length; i++) {
			p = this._people[i];

			a = document.createElement('a');
			a.setAttribute('id', 'ppl_'+p['id']);
			a.innerHTML = p['name'];
			a.setAttribute('href', p['url']);
			$(this._elText).appendChild(a);
			YAHOO.util.Dom.addClass(a,'tagged_person_link');

			aDel = document.createElement('a');
			aDel.innerHTML = 'X';
			aDel.setAttribute('href', '#');
			aDel.setAttribute('title', 'Fjern denne personen');
			a.appendChild(aDel);

			frame = document.createElement('div');
			frame.setAttribute('id', 'frame_'+p['id']);
//			YAHOO.util.Dom.setStyle(frame,'background','#fff');
			$(this._elArea).appendChild(frame);
			YAHOO.util.Dom.addClass(frame,'tagged_person_frame');
			YAHOO.util.Dom.setStyle(frame,'width',p['width']+'px');
			YAHOO.util.Dom.setStyle(frame,'height',p['height']+'px');
			
			innerframe = document.createElement('div');
			frame.appendChild(innerframe);
			YAHOO.util.Dom.setStyle(innerframe,'background','#fff');
			YAHOO.util.Dom.setStyle(innerframe,'opacity','.1');
			YAHOO.util.Event.addListener(innerframe,'mouseover',this.frameRollover,p['id'],this);
			YAHOO.util.Event.addListener(innerframe,'mouseout',this.frameRollout,p['id'],this);

			
			YAHOO.util.Event.addListener(a,'mouseover',this.personRollover,p['id'],this);
			YAHOO.util.Event.addListener(a,'mouseout',this.personRollout,p['id'],this);

			YAHOO.util.Event.addListener(a,'click',this.handlePersonClick,p['id'],this);
			YAHOO.util.Event.addListener(aDel,'click',this.removePerson,p['id'],this);
			
		}
		Nifty('a.tagged_person_link','small transparent');
	},
	
	frameRollover: function(event, uId) {
		var p = this.getPersonById(uId);		
		this.focusPerson(p);
		//console.log('  enter person '+uId);
		YAHOO.util.Event.stopEvent(event);		
	},

	frameRollout: function(event, uId) {
		var rel = YAHOO.util.Event.getRelatedTarget(event);
		if (this.isUndefined(rel) && rel.id == 'frame_'+uId) {}
		else if (this.isUndefined(rel.parentNode) && rel.parentNode.id == 'frame_'+uId) {}
		else {
			var p = this.getPersonById(uId);
			//console.log('  leave person '+uId);
			this.unfocusPerson(p);
		}		
	},
	
	personRollover: function(event, uId) {
		var p = this.getPersonById(uId);		
		this.showPerson(p);
	},

	personRollout: function(event, uId) {
		var p = this.getPersonById(uId);
		this.hidePerson(p);
	},
	
	handlePersonClick: function(event, uId) {
		//YAHOO.util.Event.stopEvent(event);	
	},
	
	removePerson: function(event, uId) {
		YAHOO.util.Event.stopEvent(event);
		this.untagMember(uId);
		var p = this.getPersonById(uId);
		this.hidePerson(p);
	},
	
	drawFrame: function(p) {
		//var p = this._people[uId];
		var areaXY = YAHOO.util.Dom.getXY(this._elArea);
		var xy = [areaXY[0]+p['x'], areaXY[1]+p['y']];
		
		YAHOO.util.Dom.setStyle('frame_'+p['id'],'visibility','visible');
		YAHOO.util.Dom.setStyle('frame_'+p['id'],'opacity','.4'); // "opacity" is normalized across modern browsers	
		YAHOO.util.Dom.setXY('frame_'+p['id'], xy);

		/*YAHOO.util.Dom.setStyle(this._elCross,'visibility','visible');
		YAHOO.util.Dom.setStyle(this._elCross,'width',width+'px');
		YAHOO.util.Dom.setStyle(this._elCross,'height',height+'px');
		YAHOO.util.Dom.setXY(this._elCross,[x,y]);		
		*/
	},
	
	clearFrame: function() {
		//YAHOO.util.Dom.setStyle(this._elCross,'visibility','hidden');
		for (var i = 0; i < this._people.length; i++) {
			YAHOO.util.Dom.setStyle('frame_'+this._people[i]['id'],'visibility','hidden');
		}
	},
	
	showAllFrames: function() {
		for (var i = 0; i < this._people.length; i++) {
			this.drawFrame(this._people[i]);
		}
	},
	
	hideAllFrames: function() {
		for (var i = 0; i < this._people.length; i++) {
			YAHOO.util.Dom.setStyle('frame_'+this._people[i]['id'],'visibility','hidden');
		}	
	},
	
	showPerson: function(p) {
		this.showAllFrames();
		this.focusPerson(p);	
	},
	
	hidePerson: function(p) {
		this.unfocusPerson(p);	
		this.hideAllFrames();
	},

	focusPerson: function(p) {		
		YAHOO.util.Dom.setStyle('frame_'+p['id'],'opacity','1'); // Normalizes "opacity" across modern browsers
		YAHOO.util.Dom.setStyle('name_float','visibility','visible');
		YAHOO.util.Dom.setStyle('name_float','opacity','.8'); // Normalizes "opacity" across modern browsers

		var areaXY = YAHOO.util.Dom.getXY(this._elArea);
		var xy = [areaXY[0]+p['x']+p['width']/2-140/2, areaXY[1]+p['y']+p['height']+15];
		YAHOO.util.Dom.setXY('name_float',xy);
		$('name_float').innerHTML = p['name'];
	},
	
	unfocusPerson: function(p) {
		YAHOO.util.Dom.setStyle('frame_'+p['id'],'opacity','.4'); // Normalizes "opacity" across modern browsers	
		YAHOO.util.Dom.setStyle('name_float','visibility','hidden');
	},
	
	handleMouseOver: function(event) {
		
		var target = YAHOO.util.Event.getTarget(event);
		var targetParent = target.parentNode;
		var rel = YAHOO.util.Event.getRelatedTarget(event);

		if (target != $(this._elArea)) return;

		/*console.group('mouseover');
		console.log(target);
		console.log(rel);
		console.log(rel.parentNode);
		*/
		/* NOTE: relatedTarget may be undefined */
		if (this.isUndefined(rel)) {
		
		} else if (YAHOO.util.Dom.hasClass(rel,'tagged_person_frame')) {
			/* Pointer was moved from a person to the background */
		//	console.log('from frame');
			//console.groupEnd();
			return;
		} else if (!this.isUndefined(rel.parentNode)) {
			//console.log(rel.parentNode);
			if (YAHOO.util.Dom.hasClass(rel.parentNode,'tagged_person_frame')) {
				/* Pointer was moved from a person to the background */
				//console.log('from frame inner');
				//console.groupEnd();
				return;		
			}
		}

		/* Pointer entered the photo from outside */
		console.info('Pointer entered photo');
		this._areaMouseOver = true;
		//console.groupEnd();
		this.showAllFrames();
		
	},
	
	handleMouseOut: function(event) {
		/* NOTE: relatedTarget may be undefined */
		var target = YAHOO.util.Event.getTarget(event);
		var targetParent = target.parentNode;
		var rel = YAHOO.util.Event.getRelatedTarget(event);

		if (target != $(this._elArea)) return;

		/*console.group('mouseout');
		console.log(target);
		console.log(rel);
		console.groupEnd();
		*/
		/* NOTE: relatedTarget may be undefined */
		if (this.isUndefined(rel)) {
		
		} else if (YAHOO.util.Dom.hasClass(rel,'tagged_person_frame')) {
			/* Pointer was moved from a person to the background */
			//console.log('from frame');
			//console.groupEnd();
			return;
		} else if (!this.isUndefined(rel.parentNode)) {
			//console.log(rel.parentNode);
			if (YAHOO.util.Dom.hasClass(rel.parentNode,'tagged_person_frame')) {
				/* Pointer was moved from a person to the background */
			//	console.log('from frame inner');
			//	console.groupEnd();
				return;		
			}
		}

		console.info("Pointer left photo");
		this._areaMouseOver = false;
		this.hideAllFrames();

	},
		
	handleMouseDown: function(event) {
		if (this._enabled) {

			var clickedItem = YAHOO.util.Event.getTarget(event);
			var xy = YAHOO.util.Event.getXY(event);

			if (clickedItem == $(this._elArea) || clickedItem == $(this._elCross)) {

				YAHOO.util.Dom.setStyle(this._elCross,'visibility','hidden');
				YAHOO.util.Dom.setStyle(this._elForm,'visibility','hidden');
				
				if (this._dragging) {
					this.endDrag(xy[0],xy[1]);
					return;
				}
						
				this.startDrag(xy[0], xy[1]);
			}
		}	
	},

	handleMouseMove: function(event) {
		var xy = YAHOO.util.Event.getXY(event);
		var width = xy[0] - this._startX;
		var height = xy[1] - this._startY;

		var x,y,w,h;		
		w = Math.abs(width);
		h = Math.abs(height);
		if (width < 0) x = xy[0]; else x = this._startX;
		if (height < 0) y = xy[1]; else y = this._startY;

		YAHOO.util.Dom.setStyle(this._elCross,'visibility','visible');
		YAHOO.util.Dom.setXY(this._elCross,[x,y]);
		YAHOO.util.Dom.setStyle(this._elCross,'width',w+'px');
		YAHOO.util.Dom.setStyle(this._elCross,'height',h+'px');
		
	},
	
	handleMouseUp: function(event) {
		if (!this._dragging) return;
		var xy = YAHOO.util.Event.getXY(event);
		this.endDrag(xy[0],xy[1]);
	},
	
	startDrag: function(x,y) {
		console.info('Start drag');
		this._dragging = true;
		this._startX = x;
		this._startY = y;
		YAHOO.util.Dom.setXY(this._elCross,[x,y]);
        YAHOO.util.Event.on(this._elArea, "mousemove", this.handleMouseMove, this, true);
	},
		
	endDrag: function(x,y) {
		console.info('End drag');
		YAHOO.util.Dom.setStyle(this._elCross,'visibility','visible');
		this._dragging = false;
        YAHOO.util.Event.removeListener(this._elArea, "mousemove", this.handleMouseMove);

		var width = x - this._startX;
		var height = y - this._startY;

		var x,y,w,h;		
		w = Math.abs(width);
		h = Math.abs(height);
		if (width < 0) x = x; else x = this._startX;
		if (height < 0) y = y; else y = this._startY;

		this.showTagForm(x,y,w,h);
	},
	
	onAreaClick: function(event) {		
		if (this._enabled) {

			var clickedItem = YAHOO.util.Event.getTarget(event);
			var xy = YAHOO.util.Event.getXY(event);
						
			if (clickedItem == $(this._elArea) || clickedItem == $(this._elCross)) {
				console.info("Vis form");
				this.showTagForm(xy[0], xy[1]);
			}
		}
	},
	
	onBtnCancelClick: function(event) {
		this.hideTagForm();
	},
	
	onBtnSaveClick: function(event) {
		YAHOO.util.Event.stopEvent(event);
		this.tagMember();
	},
	
	onBtnTagMeClick: function(event) {
		$(this._elFormUser).value = this._myName;
		this.tagMember();
	},
	
	showTagForm: function(X,Y,w,h) {
		var areaXY = YAHOO.util.Dom.getXY(this._elArea);
		var areaW = $(this._elArea).offsetWidth;
		var areaH = $(this._elArea).offsetHeight;
		//YAHOO.util.Dom.setStYle(this._elCross,'visibility','visible');
		if (w < 10 && h < 10) {
			w =  100;
			h = 100;
			X = X - w/2;
			Y = Y - h/2;
			if (X<areaXY[0]+1) X = areaXY[0]+1;
			if (Y<areaXY[1]+1) Y = areaXY[1]+1;
			if (X+w > areaXY[0]+areaW-2) X = areaXY[0]+areaW-w-2;
			if (Y+h > areaXY[1]+areaH-2) Y = areaXY[1]+areaH-h-2;
			YAHOO.util.Dom.setXY(this._elCross,[X,Y]);
			YAHOO.util.Dom.setStyle(this._elCross,'width',w+'pX');
			YAHOO.util.Dom.setStyle(this._elCross,'height',h+'pX');			
		}
		
		var absXY = YAHOO.util.Dom.getXY(this._elCross);
		YAHOO.util.Dom.setStyle(this._elForm,'visibility','visible');
		YAHOO.util.Dom.setXY(this._elForm,[
			absXY[0]+w/2-180/2,
			absXY[1]+h+5
		]);
		console.info(absXY);
		
		this._frame = { x: X-areaXY[0], y: Y-areaXY[1], width: w, height: h };
		$(this._elFormUser).value = "";
		$(this._elFormUser).focus();
	},
	
	hideTagForm: function() {
		Element.setStyle(this._elCross,{visibility:"hidden"});						
		Element.setStyle(this._elForm,{visibility:"hidden"});
		$(this._elFormUser).value = "";
	},
		
	tagMember: function() {
		YAHOO.util.Dom.setStyle(this._elCross,'visibility','hidden');
		YAHOO.util.Dom.setStyle(this._elForm,'visibility','hidden');	
		var pName = $(this._elFormUser).value;
		this.makeAjaxRequest(this._tagurl,{
			fullname: pName,
				frame: this._frame
		});
	},
	
	untagMember: function(id) {
		this.makeAjaxRequest(this._untagurl,{
			tag_uid: id
		});
	},
	
	ajaxSuccess: function(ajax) {
		//console.info(ajax['people']);
		$(this._elText).innerHTML = 'Med på bildet: ';
		this._people = ajax['people'];
		this.redrawText();
		if (ajax['error'] != '0') {
			var d = document.createElement('div');
			d.innerHTML = ajax['error'];
			$(this._elText).appendChild(d);
			YAHOO.util.Dom.setStyle(d,'color','#f00');
			YAHOO.util.Dom.setStyle(d,'padding','5px');
		}
		if (this._areaMouseOver) {
			this.showAllFrames();	
		}
	},
	
	ajaxFailure: function(result) {
		$(this._elText).innerHTML = 'Det oppstod en feil: '+result;
	},
	
	makeAjaxRequest: function(url, parameters) {
		console.info('Making AJAX request…');
		console.log(YAHOO.lang.JSON.stringify(parameters));
		//$(this._elText).innerHTML = 'Oppdaterer…';
		var self = this;
		YAHOO.util.Connect.asyncRequest('POST',url, { 
			success: function(o){
				try {
					var json = YAHOO.lang.JSON.parse(o.responseText);
					self.ajaxSuccess(json);
				} catch (x) {
					self.ajaxFailure(x+'<br />'+o.responseText);
				}
			},
			failure: function(o){ self.ajaxFailure(o); }		
		}, 'json='+YAHOO.lang.JSON.stringify(parameters));
	}
	
});
/*
		if (event.srcElement) {					// Internet Explorer
			clickedItem = event.srcElement.id;
		} else if (event.target) {			  	// Netscape and Firefox
			clickedItem = event.target.id;
		}
		
		if (this._enabled) {
			if (clickedItem == this._divArea) {
				pos_x = event.offsetX?(event.offsetX):event.pageX-$(this._divArea).offsetLeft;
				pos_y = event.offsetY?(event.offsetY):event.pageY-$(this._divArea).offsetTop;
				this.showTagForm(pos_x, pos_y);
			}
		}
*/