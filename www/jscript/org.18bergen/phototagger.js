/*
	class: PhotoTagger
	
*/
function PhotoTagger(enabled, tag_url, untag_url, my_name, everybody) {

	var _people = [],
		_dragging = false,
		_areaMouseOver = false,
		
		_frame = { x: 0, y: 0, width: 0, height: 0 },

		_startX,
		_startY,
			
		/* Ids of html elements */
		_elArea = '#pointer_div',
		_elCross = '#cross',
		_elText = '#tagged_users',
		_elForm = '#crossform',
		_elFormUser = '#tagged_user',
		_elFormBtnMe = '#btn_tagme',
		_elFormBtnCancel = '#btn_tagcancel',
		_elFormBtnSave = '#btn_tagsave';

	enabled = (enabled == true);

	this.addPerson = function(uId, uName, uUrl, x, y, width, height) {
		_people.push({
			id: uId,
			name: uName,
			url: uUrl,
			left: x,
			top: y,
			width: width,
			height: height
		});
	};
	
	this.init = function() {

		$(_elFormBtnMe).on('click', onBtnTagMeClick);
		$(_elFormBtnCancel).on('click', onBtnCancelClick);
		$(_elFormBtnSave).on('click', onBtnSaveClick);

		//$(_elArea).on('click',this.onAreaClick);
		$(_elArea).on('mouseover', handleMouseOver)
				  .on('mouseout', handleMouseOut)
				  .on('mousedown', handleMouseDown)
				  .on('mouseup', handleMouseUp)
				  .on('mouseover', function(evt) { evt.preventDefault(); })	
				  .on('mouseout', function(evt) { evt.preventDefault(); });

		redrawText();
		
		makeAutoComplete();
		
	};

	function getPersonById(uId) {
		for (var i = 0; i < _people.length; i++) {
			if (_people[i].id === uId) {
				return _people[i];
			}
		}
		return false;	
	}
		
	function makeAutoComplete() {
		$(_elFormUser).autocomplete({
			source: everybody,
			minLength: 2
		});
	}	
	
	function redrawText() {
		var a,aDel,p,frame,innerframe,i;
		
		$(_elText+', '+_elText+' *').off();

		$(_elArea + ' div.tagged_person_frame').remove();
		$(_elText).html('<span style="display:inline-block;vertical-align:middle;float:left;">På bildet: </span>');

		if (_people.length === 0) {
			$(_elText).append('<em>Ukjent</em>');
		}
		for (i = 0; i < _people.length; i++) {
			p = _people[i];

			a = '<a id="ppl_' + p.id + '" href="' + p.url + '" class="tagged_person_link">' + p.name + ' <i class="delete" title="Fjern denne personen">X</i></a>';
			$(_elText).append(a);

			frame = '<div id="frame_' + p.id + '" class="tagged_person_frame" style="width:' + p.width + 'px; height: ' + p.height + 'px;"><div style="background-color:rgba(255,255,255,.4);"></div></div>';
			$(_elArea).append(frame);
			
		}

		$('.tagged_person_frame')
			.on('mouseover', frameRollover)
			.on('mouseout', frameRollout);

		$('.tagged_person_link')
			.on('mouseover', personRollover)
			.on('mouseout', personRollout)
			.on('click', handlePersonClick);

		Nifty('.tagged_person_link','small transparent');
	}

	function frameRollover(event) {
		var id = parseInt($(event.currentTarget).attr('id').split('_')[1], 10);
		//console.info('  enter person ' + id);
		var p = getPersonById(id);
		focusPerson(p);
		event.preventDefault();		
	}

	function frameRollout(event) {
		var id = parseInt($(event.currentTarget).attr('id').split('_')[1], 10);
		var rel = event.relatedTarget;
		if (rel && rel.id === 'frame_'+id) {

		} else if (rel && rel.parentNode && rel.parentNode.id === 'frame_'+id) {

		} else {
			var p = getPersonById(id);
			//console.info('  leave person ' + id);
			unfocusPerson(p);
		}		
	}

	function personRollover(event) {
		var id = parseInt($(event.currentTarget).attr('id').split('_')[1], 10);
		showPerson(getPersonById(id));
	}

	function personRollout(event) {
		var id = parseInt($(event.currentTarget).attr('id').split('_')[1], 10);
		hidePerson(getPersonById(id));
	}
	
	function handlePersonClick(event) {
		var id = parseInt($(event.currentTarget).attr('id').split('_')[1], 10);
		//console.log(id);
		if ($(event.target).hasClass('delete')) {
			event.preventDefault();
			removePerson(id);
		}
	}
	
	function removePerson(id) {
		untagMember(id);
		hidePerson(getPersonById(id));
	}
	
	function drawFrame(p) {
		var areaXY = $(_elArea).offset(),
			xy = {
				left: areaXY.left + p.left, 
				top: areaXY.top + p.top
			};
		
		$('#frame_' + p.id)
			.show()
			.css('opacity', '.4')
			.offset(xy);

	}
	
	function clearFrame() {
		for (var i = 0; i < _people.length; i++) {
			$('#frame_'+_people[i].id).hide();
		}
	}
	
	function showAllFrames() {
		for (var i = 0; i < _people.length; i++) {
			drawFrame(_people[i]);
		}
	}
	
	function hideAllFrames() {
		for (var i = 0; i < _people.length; i++) {
			$('#frame_'+_people[i].id).hide();
		}	
	}
	
	function showPerson(p) {
		showAllFrames();
		focusPerson(p);	
	}
	
	function hidePerson(p) {
		unfocusPerson(p);	
		hideAllFrames();
	}

	function focusPerson(p) {

		var areaXY = $(_elArea).offset();
		var xy = {
			left: areaXY.left + p.left + p.width/2 - 140/2, 
			top: areaXY.top + p.top + p.height + 15
		};
		$('#frame_' + p.id).css('opacity', '1');
		$('#name_float').show()
						.css('opacity', '.8')
						.offset(xy)
						.html(p.name);
	}
	
	function unfocusPerson(p) {
		$('#frame_'+p.id).css('opacity','.4'); // Normalizes "opacity" across modern browsers	
		$('#name_float').hide();
	}
	
	function handleMouseOver(event) {
		
		var target = event.target;
		var targetParent = $(target).parent();
		var rel = event.relatedTarget;

		if (target !== $(_elArea).get(0)) return;

		/* NOTE: relatedTarget may be undefined */
		if (rel) {
		
			if ($(rel).hasClass('tagged_person_frame')) {
				/* Pointer was moved from a person to the background */
			//	console.log('from frame');
				//console.groupEnd();
				return;
			} else if ( $(rel).parent() ) {
				//console.log(rel.parentNode);
				if ($(rel).parent().hasClass('tagged_person_frame')) {
					/* Pointer was moved from a person to the background */
					//console.log('from frame inner');
					//console.groupEnd();
					return;		
				}
			}
		}

		/* Pointer entered the photo from outside */
		//console.info('Pointer entered photo');
		_areaMouseOver = true;
		//console.groupEnd();
		showAllFrames();
		
	}
	
	function handleMouseOut(event) {
		/* NOTE: relatedTarget may be undefined */
		var target = event.target;
		var targetParent = $(target).parent();
		var rel = event.relatedTarget;

		if (target !== $(_elArea).get(0)) return;

		/* NOTE: relatedTarget may be undefined or null */
		if (rel) {
		
			if ($(rel).hasClass('tagged_person_frame')) {
				/* Pointer was moved from a person to the background */
				return;
			} else if ( $(rel).parent() ) {
				//console.log(rel.parentNode);
				if ($(rel).parent().hasClass('tagged_person_frame')) {
					/* Pointer was moved from a person to the background */
					return;		
				}
			}
		}

		if (_dragging) {
			return;
		}

		//console.info("Pointer left photo");
		_areaMouseOver = false;
		hideAllFrames();

	}
		
	function handleMouseDown(event) {
		if (enabled) {

			var clickedItem = event.target;
			var xy = {left: event.pageX, top: event.pageY};

			if (clickedItem == $(_elArea).get(0) || clickedItem == $(_elCross).get(0)) {

				$(_elCross).hide();
				$(_elForm).hide();

				if (_dragging) {
					endDrag(xy.left, xy.top);
					return;
				}

				startDrag(xy.left, xy.top);
			}
		}	
	}

	function handleMouseMove(event) {
		var left, top, width, height;
		var xy = {left: event.pageX, top: event.pageY};
		
		width = xy.left - _startX;
		height = xy.top - _startY;

		left = (width < 0) ? xy.left : _startX;
		top = (height < 0) ? xy.top : _startY;

		$(_elCross)
			.show()
			.css('width', Math.abs(width)+'px')
			.css('height', Math.abs(height)+'px')
			.offset({left: left, top: top});

	}
	
	function handleMouseUp(event) {
		if (!_dragging) return;
		var xy = {left: event.pageX, top: event.pageY};
		endDrag(xy.left, xy.top);
	}
	
	function startDrag(x, y) {
		//console.info('Start drag');
		_dragging = true;
		_startX = x;
		_startY = y;
		$(_elCross).offset({left: x, top: y});
		$(_elArea).on('mousemove', handleMouseMove);
		showAllFrames();
	}
		
	function endDrag(x,y) {
		var left, top, width, height;		
		//console.info('End drag');
		$(_elCross).show();
		_dragging = false;
		$(_elArea).off("mousemove");

		width = x - _startX;
		height = y - _startY;

		left = (width < 0) ? x : _startX;
		top = (height < 0) ? y : _startY;

		showTagForm(left, top, Math.abs(width), Math.abs(height));
	}
	
	function onAreaClick(event) {		
		if (enabled) {

			var clickedItem = event.target;
			var xy = {left: event.pageX, top: event.pageY};
						
			if (clickedItem == $(_elArea) || clickedItem == $(_elCross)) {
				//console.info("Vis form");
				showTagForm(xy.left, xy.top);
			}
		}
	}
	
	function onBtnCancelClick(event) {
		hideTagForm();
	}
	
	function onBtnSaveClick(event) {
		event.preventDefault();
		tagMember();
	}
	
	function onBtnTagMeClick(event) {
		$(_elFormUser).val(my_name);
		tagMember();
	}
	
	function showTagForm(X,Y,w,h) {
		var areaXY = $(_elArea).offset();
		var areaW = $(_elArea).offsetWidth;
		var areaH = $(_elArea).offsetHeight;
		//YAHOO.util.Dom.setStYle(_elCross,'visibility','visible');
		if (w < 10 && h < 10) {
			w =  100;
			h = 100;
			X = X - w/2;
			Y = Y - h/2;
			if (X<areaXY.left+1) X = areaXY.left+1;
			if (Y<areaXY.top+1) Y = areaXY.top+1;
			if (X+w > areaXY.left+areaW-2) X = areaXY.left+areaW-w-2;
			if (Y+h > areaXY.top+areaH-2) Y = areaXY.top+areaH-h-2;
			$(_elCross).offset({left: X, top: Y});
			$(_elCross).css('width',w+'pX');
			$(_elCross).css('height',h+'pX');			
		}
		
		var absXY = $(_elCross).offset();
		$(_elForm).show();
		$(_elForm).offset({
			left: absXY.left+w/2-180/2,
			top: absXY.top+h+5
		});
		//console.info(absXY);
		
		_frame = { x: X-areaXY.left, y: Y-areaXY.top, width: w, height: h };
		$(_elFormUser).val('');
		$(_elFormUser).focus();
	}
	
	function hideTagForm() {
		$(_elCross).hide();						
		$(_elForm).hide();
		$(_elFormUser).val('');
	}
		
	function tagMember() {
		$(_elCross).hide();
		$(_elForm).hide();	
		var pName = $(_elFormUser).val();
		$.post(tag_url, { fullname: pName, frame: _frame })
		 .done(ajaxSuccess)
		 .error(ajaxFailure);
	}
	
	function untagMember(id) {
		$.post(untag_url, { tag_uid: id })
		 .done(ajaxSuccess)
		 .error(ajaxFailure);
	}
	
	function ajaxSuccess(ajax) {
		//console.info(ajax['people']);
		_people = ajax['people'];
		$(_elText).innerHTML = 'Med på bildet: ';
		redrawText();
		if (ajax.error != '0') {
			$(_elText).append('<div>' + ajax.error + '</div>');
			$(d).css('color','#f00');
			$(d).css('padding','5px');
		}
		if (_areaMouseOver) {
			showAllFrames();	
		}
	}
	
	function ajaxFailure(result) {
		$(_elText).innerHTML = 'Det oppstod en feil: '+result;
	}

}
