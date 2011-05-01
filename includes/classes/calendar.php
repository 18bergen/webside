<?

class calendar extends calendar_basic {

	var $getvars = array('action','show_event',
		'calendarId','locationId','responsibleId','categoryId',
		'cal_page','group_filter'
	);
		
	var $invalidFields = array();
	var $edit_id;

	var $event_id = 0;
	
	var $entries_per_page = 200;
	var $page_no;
	var $entry_count;
	
	var $template_dir 					= '../includes/templates/';
	var $template_editeventform 		= 'calendar_editeventform.html';
	
	var $btn_yes_caption = "Ja";
	var $btn_no_caption = "Nei";
		
	var $start_caption = "Starts on:";
	var $end_caption = "Ends on:";
	
	var $chooseFromListCaption = "Choose from the list:";
	var $newentry_caption = "New calendar event";
	var $editentry_caption = "Endre hendelse";
	var $deleteentry_caption = "Slett hendelse";
	var $cancelentry_caption = "Avlys hendelse";
	var $dropcancelentry_caption = "Fjern avlysning";
	var $subscribe_caption = "Subscribe to this calendar";
	var $goback_caption = "Tilbake";
	var $timenotset_caption = "";
	var $deleteconfirm_caption = "Delete the calendar entry \"%TITLE\"?";
	var $cancelconfirm_caption = "Er du sikker på at du vil avlyse \"%TITLE\"?";
	var $dropcancelconfirm_caption = "Er du sikker på at du vil fjerne avlysningen av \"%TITLE\"?";
	var $othercreatornotice_caption = "<b>Notice</b>: 
		This event was not created by you. Please make sure the creator of this event 
		is informed about the change you are about to commit.";
	var $othercreatorwarning_caption = "<b>Warning</b>: 
		This calendarentry was not created by you. You do not have permissions to edit or delete it.)";

	var $editlink_template = ' <a href="%editurl%" class="icn" style="background-image:url(/images/icns/date_edit.png);">Endre hendelse</a>';
	var $deletelink_template = ' <a href="%deleteurl%" class="icn" style="background-image:url(/images/icns/date_delete.png);">Slett hendelse</a>';
	var $cancellink_template = ' <a href="%cancelurl%" class="icn" style="background-image:url(/images/icns/date_error.png);">Avlys hendelse</a>';
	var $dropcancellink_template = ' <a href="%dropcancelurl%" class="icn" style="background-image:url(/images/icns/calendar.png);">Fjern avlysning</a>';
	var $eventdetails_template = '
			
				
		';
		
	var $subscribeinfo_template = '
			<h3>Abonnér på / last ned terminliste</h3>
			<p>
				Enkelte programmer støtter abonnering på en kalender, mens andre kun støtter engangsimportering. Atter andre har problemer
				med å importere kalendere i det hele tatt. Abonnering er klart å foretrekke fremfor importering, da endringer blir reflektert
				i kalenderprogrammet umiddelbart.
			</p>
			<table cellpadding="4" cellspacing="0" border="1" style="background:#ffffff;">
				<tr><td><strong>Kalenderprogram / tjeneste</strong></td><td><strong>Støtte</strong></td></tr>
				<tr><td><a href="http://www.apple.com/macosx/features/ical/">Apple iCal</a></td><td style="background:#00FF00;">Støtter abonnement</td></tr>
				<tr><td><a href="http://www.mozilla.org/projects/calendar/sunbird/index.html">Mozilla Sunbird</a></td><td style="background:#00FF00;">Støtter abonnement</td></tr>
				<tr><td><a href="http://www.google.com/calendar/render">Google Calendar</a></td><td style="background:#00FF00;">Støtter abonnement</td></tr>
				<tr><td><a href="http://www.designtheworld.com/iwebcal/index.php">iWebCal</a></td><td style="background:#00FF00;">Støtter abonnement</td></tr>
				<tr><td><a href="http://www.microsoft.com/outlook/">Microsoft Outlook</a></td><td>Ukjent</td></tr>
				<tr><td><a href="http://www.microsoft.com/mac/products/entourage2004/entourage2004.aspx?pid=entourage2004">Microsoft Entourage</a></td><td style="background:#FFFF00;">Støtter importering (problem med tidssoner)</td></tr>
			</table>
			<p>
				Link: %downloadlink%
			</p>
			
			<p>
				<a href="%downloadlink%">Last ned terminliste</a>
			</p>
			
			<h3>Instruksjoner:</h3>
			<h4>Apple iCal</h4>
			<ol>
				<li>Trykk her: <a href="%abblink%" class="icn" alt="iCal" style="background-image:url(/images/ical16.png);">Abbonèr på terminliste</a></li>
			</ol>
			<h4>Mozilla Sunbird</h4>
			<ol>
				<li>Velg File->Subscribe to Remote Calendar... </li>
				<li>Velg "Remote". Trykk "Continue". </li>
				<li>Velg "WebDAV". I "Location" fyller du inn "%downloadlink%". Trykk "Continue".</li>
				<li>Skriv inn et valgfritt navn og trykk "Continue"</li>
			</ol>
			<h4>Google Calendar</h4>
			<ol>
				<li>Trykk på "Manage calendars"</li>
				<li>Trykk "Add calendar" under "Other calendars".</li>
				<li>Velg "Public calendar address"</li>
				<li>Fyll inn "%downloadlink%" og trykk "Add"</li>
			</ol>
			
			<h4>iWebCal</h4>
			<ol>
				<li>Trykk her: <a href="http://iwebcal.com/iwebcal.php?file=%abblink%" target="_blank">Vis i iWebCal</a></li>
			</ol>
			
			
			
			<p class="cal_link">
				<img src="%img_goback%" border="0" /> 
				<a href="%backlink%">Tilbake</a>
			</p>			
		';

	var $newentrylink_template = '<img src="images/editadd.gif" border="0" /> <a href="%newentryurl%">Legg til ny hendelse</a>';
	var $subscribelink_template = '<img src="images/newspaper.gif" border="0" />  <a href="%subscribeurl%">Abonnér på/Last ned terminliste</a>';
	var $noentries_template = '<i>Kalenderen inneholder ingen hendelser for %year%</i>';
	var $noentries_future_template = '<i>Kalenderen inneholder ingen fremtidige hendelser</i>';
	var $calview_template2 = '
		<p class="hidefromprint">
			%newentry%
			%settings%
			%subscribe%
		</p>
		<h3>%header%</h3>
		<table class="hidefromprint" width="100%"><tr><td>
			%view_options%
		</td><td align="right">
			<a href="%feedurl%"><img src="/images/feed.png" border="0"></a>
		</td></tr></table>
		<p>
			%cal_list%
		</p>
		%noentries%	%entries%
		<h3>Symbolforklaringer</h3>
		<table>
			<tr><td><img src="/images/task2.gif"></td><td>Arrangementet har påmelding</td></tr>
			<tr><td><img src="/images/camera.gif"></td><td>Bildearkivet vårt inneholder bilder fra arrangementet</td></tr>
			<tr><td><img src="/images/log.gif"></td><td>Det har blitt skrevet logg fra arrangementet</td></tr>
			<tr><td><img src="/images/lock4.gif"></td><td>Krever innlogging for å vise</td></tr>
		</table>
	';

	var $rss_entry_template = '
	<item rdf:about="%details_fullurl%">
		<title>%subject% %shortdate%</title>
		<link>%details_fullurl%</link>
		<description>%rsslead%</description>
		<dc:date>%rssdate%</dc:date>
	</item>
	';

	function calendar(){
		$this->calendar_basic();
	}
		
	function sitemapListAllPages(){
		$urls = array();
		
		$minYear = 2000;
		$maxYear = date("Y",time())+2;
		for ($i = $minYear; $i <= $maxYear; $i++) {
			$urls[] = array(
				'loc' => $this->generateCoolUrl("/$i"), 
				'changefreq' => 'monthly'
			);
		}
		
		$cals = array();
		$res = $this->query("SELECT id FROM $this->table_calendars WHERE default_cal_page=$this->page_id");
		while ($row = $res->fetch_assoc()) {
			$cals[] = intval($row['id']);
		}
		$res->close();
		if (count($cals) > 0) {
			// List articles
			$res = $this->query("SELECT id, slug, YEAR(dt_start) as year, lastmodified FROM $this->table_calendar WHERE calendar_id IN (".implode($cals,',').")");
			while ($row = $res->fetch_assoc()) {
				$id = intval($row['id']);
				$slug = stripslashes($row['slug']);
				$year = intval($row['year']);
				if (!empty($row['slug']))
					$u = $this->generateCoolURL("/$year/$slug");
				else
					$u = $this->generateUrl("view_event=$id");
				$urls[] = array(
					'loc' => $u, 
					'lastmod' => $row['lastmodified'],
					'changefreq' => 'monthly'
				);
			}
			$res->close();
		}
		
		return $urls;
	}
	
	function initialize() {

		@parent::initialize();
		
		// Stop here if using to create sitemap
		if (empty($this->lookup_member)) return;
		
	}
	
	
	function run() {
		
		$this->initialize();

		$this->setRssUrl($this->generateCoolURL("/rss"));

		$action = isset($_GET['action']) ? $_GET['action'] : '';

		$settingsPage = false;
		if (isset($this->coolUrlSplitted[0])) {
			if (is_numeric($this->coolUrlSplitted[0]) && isset($this->coolUrlSplitted[1])) {
				$year = intval($this->coolUrlSplitted[0]);
				$slug = addslashes($this->coolUrlSplitted[1]);
				$res = $this->query("SELECT id 
					FROM $this->table_calendar
					WHERE YEAR($this->table_calendar_field_startdate)=\"$year\" AND $this->table_calendar_field_slug=\"$slug\"");
				if ($res->num_rows != 1) $this->fatalError("The entry does not exist!");
				$row = $res->fetch_assoc();
				$this->event_id = intval($row['id']);
			} else {
				switch ($this->coolUrlSplitted[0]) {
					case 'settings':
						$settingsPage = true; break;
					case 'subscribe.ics':
						$action = 'viewVCal'; break;
					case 'rss':
						$action = 'viewRss'; break;
				}
			}
		}
					
		/* 
		##	Determine and execute requested action
		*/
		
		$settingsActions = array(
			'ajaxAddCategory','ajaxEditCategory','ajaxSaveCategory','ajaxPrintCategory',
			'ajaxAddResponsible','ajaxEditResponsible','ajaxSaveResponsible','ajaxPrintResponsible',
			'ajaxAddCalendar','ajaxEditCalendar','ajaxSaveCalendar','ajaxPrintCalendar',
			'ajaxAddLocation','ajaxEditLocation','ajaxSaveLocation','ajaxPrintLocation'
		);
		$calendarActions = array(
			'viewCalendar','editSettings','subscription','viewVCal','viewRss','newEvent','saveEvent',
			'ajaxAutocompleteSubject','ajaxAutocompleteLocation','toggleCal','ajaxGetAuthorList'
		);
		$eventActions = array(
			'viewEvent','editEvent','saveEvent','deleteEvent','deleteEventDo','cancelEvent','cancelEventDo',
			'ajaxAutocompleteSubject','ajaxAutocompleteLocation','ajaxGetAuthorList',
			'saveComment','deleteCommentDo','subscribeToThread','unsubscribeFromThread'
		);
		
		if ($settingsPage){
			if (!in_array($action,$settingsActions)) $action = 'editSettings';
		} else if ($this->event_id > 0){
			if (!in_array($action,$eventActions)) $action = 'viewEvent';
		} else {
			if (!in_array($action,$calendarActions)) $action = 'viewCalendar';
		}
		return call_user_func(array($this,$action));
		
	}
	
	function generateTimeField($form, $identifier, $value = "-1", $className = ""){

		$bfr = "<select name=\"".$identifier."_time\" id=\"".$identifier."_time\" size=\"1\" class=\"$className\" 
			onChange=\"checkForUnknownTime('$form', '$identifier', this);\">\n";
		// 01 represents ?? - unknown time
		$selc = (($value != "-1") && ('01' == date("i",$value))) ? $selc = " selected='selected'" : "";
		$bfr .= "  <option value='heledagen'".$selc."> </option>\n";
		for ($i = 6; $i < 24; $i++){ // 00.01 er reservert for ukjent tid!
			foreach (array('00','30') as $min) {
				$tid = $this->toSiffer($i).':'.$min;
				$selc = ($tid == date("H:i",$value)) ? $selc = " selected='selected'" : "";
				$bfr .= "  <option value='$tid'$selc>$tid</option>\n";
			}
		}
		$bfr .= "</select>";

		return $bfr;
	}
	
	function parseDateTimeFromPOST($identifier){	
		foreach (array('day','month','year','time') as $s) {
			if (!isset($_POST[$identifier.'_'.$s])) {
				$this->fatalError($identifier.'_'.$s.' not found in _POST');
			}
		}
		$ds_day = intval($_POST[$identifier.'_day']);
		$ds_month = intval($_POST[$identifier.'_month']);
		$ds_year = intval($_POST[$identifier.'_year']);
		if ($_POST[$identifier.'_time'] == 'heledagen') {
			$ds_h = 0; $ds_m = 0;
		} else {
			list($ds_h,$ds_m) = explode(':',$_POST[$identifier.'_time']);		
			$ds_h = intval($ds_h); $ds_m = intval($ds_m);
		}
		$tim = mktime($ds_h,$ds_m,0,$ds_month,$ds_day,$ds_year);
		return strftime("%F %T",$tim);
	}

	function toggleCal() {

		$id = $_GET['calendarId']; // This will on the format "pageId-calendarId"
		list($pageId,$calId) = explode("-",$id);
		$pageId = intval($pageId); $calId = intval($calId);
		if ($pageId <= 0 || $calId <= 0) $this->fatalError("Invalid input");
		
		if (isset($_SESSION['showcal-'.$id])) {
			$_SESSION['showcal-'.$id] = !$_SESSION['showcal-'.$id];
		}
		
		$this->redirect($this->generateUrl(""));
	}	
	
	/*************************************************************************************************
											CREATE/EDIT EVENT
	 *************************************************************************************************/

	/*
		Function: newEvent
	*/
	function newEvent() {
		$this->event_id = '_new';
		return $this->editEvent();
	}
	
	/*
		Function: editEvent
			id  - either int or '_new'
	*/
	function editEvent(){
	
		$isNewEvent = !(isset($this->event_id) && $this->event_id != 0);
		$id = $isNewEvent ? 0 : $this->event_id;
					
		/** CHECK PERMISSIONS **/
	
		if ($isNewEvent){
			if (!$this->allow_addentries) return $this->permissionDenied(); 
			$calendar_subtitle = $this->label_newentry;
		} else {
			$calendar_subtitle = $this->label_editentry;
			$res = $this->query(
				"SELECT 
					$this->table_calendar_field_creator as creator
				FROM 
					$this->table_calendar
				WHERE
					$this->table_calendar_field_id='$id'"
			);
			if ($res->num_rows != 1) $this->fatalError("The entry does not exist!");
			$row = $res->fetch_assoc();
			$allow_edit = (($this->allow_editownentries && $row['creator'] == $this->login_identifier) || $this->allow_editothersentries);
			if (!$allow_edit) return $this->permissionDenied();
		}
		
		/** FETCH DATA **/
		
		$errors = array();
			
		if (isset($_SESSION['errors'])){
		
			$errors = $_SESSION['errors'];

			$postdata = $_SESSION['postdata'];
			if (isset($postdata['startdate_year'])){
				$ptime = $postdata['startdate_time'];
				if ($ptime == 'heledagen') {
					$hour = 0; $min = 0;
				} else {
					list($hour,$min) = explode(':',$ptime);
				}
				$this->startdatetime_default = mktime($hour,$min,0,$postdata['startdate_month'],$postdata['startdate_day'],$postdata['startdate_year']);
			}
			if (isset($postdata['enddate_year'])){
				$ptime = $postdata['enddate_time'];
				if ($ptime == 'heledagen') {
					$hour = 0; $min = 0;
				} else {
					list($hour,$min) = explode(':',$ptime);
				}
				$this->enddatetime_default = mktime($hour,$min,0,$postdata['enddate_month'],$postdata['enddate_day'],$postdata['enddate_year']);
			}
			$this->subject_default 			= $postdata['cal_subject'];
			$this->slug_default 			= $postdata['cal_slug'];
			$this->location_default 		= ($this->show_location 		? $postdata['cal_location'] 		: "");
			$this->calendar_default 		= ($this->show_calendar 		? $postdata['cal_calendar'] 		: "");
			$this->responsible_default 		= ($this->show_responsible 		? $postdata['authors']		 		: "");
			$this->lead_default 			= ($this->show_lead 			? $postdata['cal_lead'] 			: "");				
			$this->body_default 			= ($this->show_body 			? $postdata['cal_body'] 			: "");				
			//$this->category_default 		= ($this->show_category 		? $postdata['cal_category'] 		: "");
			$this->private_default 			= ($this->show_options			? "1" 								: "0");
			
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);
		
		} elseif ($isNewEvent){

			$this->calendar_default = $this->def_calendar;
			$this->private_default = "0";
			$this->responsible_default = "$this->login_identifier";

		} else {
						
			// Make some short vars to make the code more readable...
			$tct = $this->table_calendar;
			$tlt = $this->table_locations;

			$res = $this->query(
				"SELECT 
					$tct.id as id,
						$tct.slug as slug,
						$tct.creator as creator,
						UNIX_TIMESTAMP($tct.dt_start) as start,
						UNIX_TIMESTAMP($tct.dt_end) as end,
						$tct.caption as caption
						".
						($this->show_location 	 	? ",$tlt.caption as location" : "").
						($this->show_lead 		 	? ",$tct.lead as lead" : "").
						($this->show_body 		 	? ",$tct.body as body" : "").
						($this->show_responsible 	? ",$tct.responsible as responsible" : "").
						($this->show_calendar 		? ",$tct.calendar_id as calendar_id" : "").
						($this->show_options 		? ",$tct.private as private" : "").
				" FROM 
					$tct, $tlt
				WHERE
					$tct.id=$id AND $tct.location_id = $tlt.id"
			);
			if ($res->num_rows != 1) $this->fatalError("The entry does not exist!");
			$row = $res->fetch_assoc();
			$this->startdatetime_default = $row['start'];
			$this->enddatetime_default = $row['end'];
			$this->subject_default = $row['caption'];
			$this->slug_default = $row['slug'];

			$this->location_default 		= ($this->show_location 		? $row['location'] 				: "");
			$this->calendar_default 		= ($this->show_calendar 		? $row['calendar_id'] 				: "");
			$this->responsible_default 		= ($this->show_responsible 		? $row['responsible'] 			: "");
			$this->lead_default 			= ($this->show_lead 			? stripslashes($row['lead'])					: "");				
			$this->body_default 			= ($this->show_body 			? stripslashes($row['body'])					: "");				
			$this->private_default			= ($this->show_options	 		? $row['private'] 				: "0");
			
		}

		$rowNo = 0;
		$row1 = " class='cal_editform_row1'";
		$row2 = " class='cal_editform_row2'";
		
		$r1a = array(); $r2a = array();
		$r1a[] = "%event_id%";					$r2a[] = $id;
		$r1a[] = "%post_url%";					$r2a[] = $this->generateURL('action=saveEvent');
		$r1a[] = "%ckfinder_uri%";				$r2a[] = LIB_CKFINDER_URI;
		$r1a[] = "%ckeditor_uri%";				$r2a[] = LIB_CKEDITOR_URI;
		$r1a[] = "%title%";						$r2a[] = $calendar_subtitle;
		
		
	/********************************************** SUBJECT *************************************************/		
				
		$field_invalid = in_array("cal_subject",$errors);
		$r1a[] = "%subject%";					$r2a[] = $this->subject_default;
		$r1a[] = "%error_subject%";				$r2a[] = $field_invalid ? " class='invalidField'" : "";
		$r1a[] = "%url_autocomplete_subject%";	$r2a[] = $this->generateURL('action=ajaxAutocompleteSubject');

	/********************************************** LOCATION *************************************************/		
		
		$field_invalid = in_array("cal_location",$errors);
		$r1a[] = "%location%";					$r2a[] = $this->location_default;
		$r1a[] = "%error_location%";			$r2a[] = $field_invalid ? " class='invalidField'" : "";
		$r1a[] = "%url_autocomplete_location%";	$r2a[] = $this->generateURL('action=ajaxAutocompleteLocation');

	/********************************************** DATE/TIME *************************************************/		
				
		$dt_start_invalid = ((in_array("cal1date",$errors)) || (in_array("cal_time1_h",$errors)) || (in_array("cal_time1_m",$errors)));
		$dt_end_invalid = ((in_array("cal2date",$errors)) || (in_array("cal_time2_h",$errors)) || (in_array("cal_time2_m",$errors)));

		$startdate_unix = $this->startdatetime_default;
		$startdate_code = $this->makeDateField("startdate", $startdate_unix, false).
			$this->generateTimeField('calendarform', 'startdate', $this->startdatetime_default);
		$startdate_js = strftime('{ day:%e, month:%m, year:%Y }',$startdate_unix);

		$enddate_unix = $this->enddatetime_default;
		$enddate_code = $this->makeDateField("enddate", $enddate_unix, false).
			$this->generateTimeField('calendarform', 'enddate', $this->enddatetime_default);
		$enddate_js = strftime('{ day:%e, month:%m, year:%Y }',$enddate_unix);

		$r1a[] = "%error_datetime%";			$r2a[] = ($dt_start_invalid | $dt_end_invalid) ? " class='invalidField'" : "";
		$r1a[] = "%startdate%";					$r2a[] = $startdate_code;
		$r1a[] = "%enddate%";					$r2a[] = $enddate_code;
		$r1a[] = "%startdate_js%";				$r2a[] = $startdate_js;
		$r1a[] = "%enddate_js%";				$r2a[] = $enddate_js;

		$dt_start = date("j.n.y",$startdate_unix);
		$dt_end = date("j.n.y",$enddate_unix);
		$datestamp = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";

	/********************************************** LEAD *************************************************/		
		
		$field_invalid = in_array("cal_lead",$errors);
		$r1a[] = "%lead%";						$r2a[] = $this->lead_default;
		$r1a[] = "%error_lead%";				$r2a[] = $field_invalid ? " class='invalidField'" : "";

	/***************************************** RESPONSIBLE ************************************************/		

		$namesArray = "";
		foreach ($this->getActiveMembersList(array('SortBy' => 'FullName')) as $user_id => $u) {
			$namesArray .= "<option id=\"$user_id\">".$u['FullName']."</option>\n";
		}
		$r1a[] = "%author_list%";				$r2a[] = $this->makeEditableAuthorList(explode(",",$this->responsible_default));
		$r1a[] = "%namesArray%";				$r2a[] = $namesArray;
		$r1a[] = "%updateAuthorListUrl%";		$r2a[] = $this->generateURL('action=ajaxGetAuthorList');
		$field_invalid = in_array("cal_responsible",$errors);
		$r1a[] = "%error_responsible%";			$r2a[] = $field_invalid ? " class='invalidField'" : "";


	/********************************************** BODY *************************************************/		

		$field_invalid = in_array("cal_body",$errors);
		$r1a[] = "%body%";						$r2a[] = $this->body_default;
		$r1a[] = "%error_body%";				$r2a[] = $field_invalid ? " class='invalidField'" : "";
		
	/********************************************** ADVANCED *************************************************/		
		
		$year = date("Y",time());
		$pre_slug = "http://www.".$_SERVER['SERVER_NAME'].ROOT_DIR.$this->generateCoolURL("/")."<span id='slugYear'>$year</span>/";
		$r1a[] = "%privateChecked%";			$r2a[] = ($this->private_default == "1") ? " checked=\"checked\"" : "";
		$r1a[] = "%pre_slug%";					$r2a[] = $pre_slug;
		$r1a[] = "%slug%";						$r2a[] = $this->slug_default;
		
		$field_invalid = in_array("cal_calendar",$errors);
		$options = "<option value='-1'>$this->label_choosefromlist</option>\n";
		$res = $this->query("SELECT id, caption FROM $this->table_calendars WHERE id IN ($this->show_calendars)");
		$cals = "";
		while ($row = $res->fetch_assoc()){
			$v = $row['id'];
			$d = ($v == $this->calendar_default) ? " selected=\"selected\"" : "";
			$cals .= "							<option value=\"$v\"$d>".$row['caption']."</option>\n";
		}
		$r1a[] = "%error_calendars%";			$r2a[] = $field_invalid ? " class='invalidField'" : "";			
		$r1a[] = "%calendars%";					$r2a[] = $cals;


	/********************************************** GENERATE OUTPUT *************************************************/		
		
		if ($isNewEvent){
			call_user_func(
				$this->add_to_breadcrumb, 
				'<a href="'.$this->generateURL('action=newEvent').'">Ny hendelse</a>'
			); 
		} else {
			call_user_func(
				$this->add_to_breadcrumb, 
				'<a href="'.$this->generateCoolURL('/'.($this->currentYear).'/').'">'.$this->currentYear.'</a>'
			); 
			call_user_func(
				$this->add_to_breadcrumb,
				"<a href=\"".$this->generateURL("")."\">$this->subject_default $datestamp</a>"
			);	
		}
		
		$this->setDefaultCKEditorOptions();
		$template = file_get_contents($this->template_dir.$this->template_editeventform);
		return str_replace($r1a, $r2a, $template);

	}
	
	function ajaxGetAuthorList() {
		$authors = explode(",",$_POST['authors']);
		print $this->makeAuthorList($authors);
		exit();	
	}

	/* 
		Function: makeEditableAuthorList
		Used by the function <editLog>
	*/
	function makeEditableAuthorList($authors) {		
		return "
			<div id='authorlist' class='authorlist'>
				".$this->makeAuthorList($authors)."			
			</div>
		";
	}
	
	/* 
		Function: makeAuthorList
		Used by the functions <makeEditableAuthorList>, <ajaxSaveAuthor>, 
		<ajaxCancelAuthor> and <ajaxRemoveAuthor>
	*/
	function makeAuthorList($authors) {
		$authorsJoined = implode("",$authors);
		if ($authorsJoined == "") {
			return "
					<input type='hidden' name='authors' id='authors' value='' />
					<em style=\"color:red\">Noen må ta ansvar… Du må legge til minst én ansvarlig person.</em>
			";		
		}
		$alist = "
				<input type='hidden' name='authors' id='authors' value=\"".implode(",",$authors)."\" />
				<ul class='memberlist_s'>\n";
		$udata = $this->getUserData($authors, array('FirstName','ProfilePicture'));
		foreach ($udata as $user_id => $u) {			
			$alist .= '
				<li>
					<table><tr><td>
					<img src="'.$u['ProfilePicture']['SmallThumb'].'" style="width:30px;" />
					</td><td>
					<div style="padding:2px;">'.$u['FirstName'].'</div>
					<div style="padding:2px;"><a href="#" style="background:url(/images/famfamfam_mini/action_stop.gif) no-repeat left;padding:2px 2px 2px 18px;" onclick="removeAuthor('.$user_id.'); return false;">Fjern</a>
					</div>
					</td></tr></table>
				</li> ';
		}
		$alist .= '
				</ul>
				';
		return $alist;
	}
	
	function makeSimpleAuthorList($authors) {
		$authorsJoined = implode("",$authors);
		if ($authorsJoined == "") {
			return "
					<em style=\"color:#999\">Ukjent</em>
			";		
		}
		$alist = "
				<ul class='simpleAuthorList'>\n";
		$udata = $this->getUserData($authors, array('FullName','ProfileUrl'));
		foreach ($udata as $user_id => $u) {
			$alist .= '
				<li>
					<a href="'.$u['ProfileUrl'].'">'.$u['FullName'].'</a>
				</li> ';
		}
		$alist .= '
				</ul>
				';
		return $alist;
	}
	
	/*************************************************************************************************
									VALIDATE AND SAVE EVENT
	 *************************************************************************************************/
	
	function saveEvent() {
	
		$isNewEvent = !(isset($this->event_id) && $this->event_id != 0);
		$id = $isNewEvent ? 0 : $this->event_id;
		
		// (1) CHECK PERMISSIONS
		
		// Creating new event
		if ($isNewEvent) {
            if (!$this->allow_addentries) return $this->permissionDenied(); 


		// Modifying existing event
		} else {
			$res = $this->query("SELECT creator FROM $this->table_calendar WHERE id=$id");
			if ($res->num_rows != 1) $this->fatalError("The entry does not exist!");
			$row = $res->fetch_assoc();
			$allow_edit = (($this->allow_editownentries && $row['creator'] == $this->login_identifier) || $this->allow_editothersentries);
			if (!$allow_edit){ return $this->permissionDenied();  }
		}
				
        if (empty($_POST)) {
            $this->logError("Ankom siden med tom POST-array... Kanskje refresha siden?");
            $this->redirect($this->generateURL(''));
        }

		// (2) PARSE INPUT
		
        //if ($this->show_responsible) $responsible = $_POST['cal_responsible'];

		$creator = $this->login_identifier;
		$subject = addslashes(strip_tags($_POST['cal_subject']));
		$location = addslashes(stripslashes($_POST['cal_location']));
		$slug = addslashes($_POST['cal_slug']);
		$calendarId = intval($_POST['cal_calendar']);
		$isprivate = (isset($_POST['cal_private']) && ($_POST['cal_private'] == 'on')) ? "1" : "0";
		$startdate = $this->parseDateTimeFromPOST('startdate');
		$startdate_unix = strtotime($startdate);
		$enddate = $this->parseDateTimeFromPOST('enddate');
		$enddate_unix = strtotime($enddate);
		$responsible = addslashes(strip_tags($_POST['authors']));
		
		$lead = addslashes(strip_tags($_POST['cal_lead']));
		$lead = str_replace("\r\n","\n",$lead);
		$lead = str_replace("\r","\n",$lead);

		$body = addslashes($_POST['cal_body']);
		$body = str_replace("\r\n","\n",$body);
		$body = str_replace("\r","\n",$body);
		
		// (3) VALIDATE
		
		$errors = array();
				
		if (empty($subject)) $errors[] = 'cal_subject';		
		if (empty($location)) $errors[] = 'cal_location';
		
		if (empty($responsible)) $errors[] = 'cal_responsible';

		if ($startdate_unix > $enddate_unix){ $errors[] = 'startdate'; $errors[] = 'enddate'; }	

		if (empty($slug)) {
			$errors[] = 'cal_slug';
		} else {

			$slug = mb_strtolower($slug,'UTF-8');
			$slug = str_replace(array("ø","æ","å"," ","--"),array("o","ae","a","-","-"),$slug);	
			$slug = preg_replace('/[^\w-]/','',$slug);
			$slug = trim($slug,"-");
			$try_no = 1;
			$orig_slug = $slug;
			$duplicate = true;
			$year = date('Y',$startdate_unix);
			while ($duplicate) {
				$res2 = $this->query("SELECT id,slug FROM $this->table_calendar 
				WHERE slug=\"".addslashes($slug)."\" AND YEAR(dt_start)=\"$year\" AND id!=\"$id\"");
				if ($res2->num_rows > 0) {
					$slug = $orig_slug.'-'.($try_no++);
					$duplicate = true;
				} else {
					$duplicate = false;		
				}
			}

			$res = $this->query("SELECT id 
					FROM $this->table_calendar
					WHERE YEAR(dt_start)=\"$year\" AND slug=\"$slug\"");
			if ($res->num_rows > 0) {
				$row = $res->fetch_assoc();
				if (intval($row['id']) != $id) $errors[] = 'cal_slug';
			}	
		}
		
		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			if ($isNewEvent) $url = $this->generateURL("action=newEvent"); 
			else $url = $this->generateURL("action=editEvent"); 
			$this->redirect($url,
				"Hendelsen ble ikke lagret pga. en eller flere feil. Sjekk verdien/-e i feltet/-ene markert med rødt og prøv å lagre igjen.",
				"error"
				);
		}
		
		
		// (4) GET ID OF THE LOCATION
		
		$res = $this->query("SELECT id FROM $this->table_locations WHERE caption = \"$location\"");
		if ($res->num_rows == 1){
			$row = $res->fetch_array();
			$locationId = intval($row[0]);
		} else {
			$this->query("INSERT INTO $this->table_locations (caption) VALUES (\"$location\")");
			$locationId = $this->insert_id();
		}
		
		// (5) SAVE TO DB
		
		if ($isNewEvent){
			
			$res = $this->query("INSERT INTO $this->table_calendar 
				(
					caption, location_id, calendar_id, dt_start, dt_end, lead, 
					body, slug, lastmodified, creator, responsible, private
				) VALUES (
					\"$subject\", $locationId, $calendarId, \"$startdate\", \"$enddate\", \"$lead\", 
					\"$body\", \"$slug\", NOW(), $creator, \"$responsible\", $isprivate
				)"
			);
			if (!$res) {
				print "database insert error";
			}
			$this->event_id = $this->insert_id();
			
			$url = $this->generateCoolURL("/$year/$slug");
			$this->addToActivityLog("opprettet hendelsen <a href=\"".$url."\">$subject</a>",false,'major');

            $this->subscribeToThread(false);

			$this->redirect($url,"Hendelsen er opprettet.");

		} else {

			$this->query("UPDATE $this->table_calendar 
				SET
					caption 		= \"$subject\",
					location_id		= $locationId,
					calendar_id		= $calendarId,
					dt_start		= \"$startdate\",
					dt_end 			= \"$enddate\",
					lead			= \"$lead\",
					body			= \"$body\",
					slug 			= \"$slug\",
					lastmodified 	= NOW(),
					responsible		= \"$responsible\",
					private 		= $isprivate
				WHERE id = $id"
			);

			$url = $this->generateCoolURL("/$year/$slug");
			$this->addToActivityLog("oppdaterte hendelsen <a href=\"".$url."\">$subject</a>",true,'update');
			$this->redirect($url,"Hendelsen er lagret.");
		
		}  
		
	}
	
		
	/*************************************************************************************************
										OUTPUT SINGLE EVENT
	 *************************************************************************************************/
	
	function viewEvent(){

		$id = $this->event_id;
		if ($id <= 0) $this->fatalError("Event not found");

		// Make some short vars to make the code more readable...
		$tct = $this->table_calendar;
		$tcct = $this->table_calendars;
		$tlt = $this->table_locations;
		$trt = $this->table_responsible;
			
		if (!is_numeric($id)) fatalError("Invalid input (2)!");
		$res = $this->query(
			"SELECT 
				$tct.id,
				$tct.caption,
				$tct.lead,
				$tct.body,
				$tct.slug,
				$tct.creator,
				$tct.cancelled,
				$tct.private,
				$tct.responsible,
				$tct.dt_start,
				$tct.dt_end,
				$tlt.caption as location_name,
				$tcct.caption as calendar_name,
				$tcct.id as calendar_id
			FROM 
				$tct,$tlt,$tcct 
			WHERE
				$tct.id = $id AND $tct.location_id = $tlt.id AND $tct.calendar_id = $tcct.id"
		);
		if ($res->num_rows == 0) $this->fatalError("The entry does not exist!");
		if ($res->num_rows > 1) $this->fatalError("Multiple entries exist! Database corruption?");
		$row = $res->fetch_assoc();
		
		// Sjekk tilgang
		$isprivate = $row['private'];
		if ($isprivate && !$this->isLoggedIn()) {
			 return $this->permissionDenied();
		}
		
		// Sjekk om avlyst
		$avlyst = false;
		if ($row['cancelled'] == '1') $avlyst = true;
		
		// Event title
		$str_su = $row['caption'];
		
		// Date / time
		$dt_start = $row['dt_start'];
		$dt_start_unix = strtotime($dt_start);
		$dt_end = $row['dt_end'];
		$dt_end_unix = strtotime($dt_end);

		$dsa = getdate($dt_start_unix);
		$dea = getdate($dt_end_unix);
		$isPastEvent = ($dt_start_unix < time());
		if (date('Y',$dt_start_unix) != date('Y',time())) {
			$str_ds = ucfirst(strftime('%A %e. %B %Y',$dt_start_unix));		
		} else {
			$str_ds = ucfirst(strftime('%A %e. %B',$dt_start_unix));		
		}
		if (date('Y',$dt_end_unix) != date('Y',time())) {
			$str_de = ucfirst(strftime('%A %e. %B %Y',$dt_end_unix));		
		} else {
			$str_de = ucfirst(strftime('%A %e. %B',$dt_end_unix));		
		}
		$str_ts = strftime('%H:%M',$dt_start_unix);
		$str_te = strftime('%H:%M',$dt_end_unix);
		if ($str_ds == $str_de) {
			// One-day event
			if ($str_ts == '00:00' || $str_ts == '00:01') {
				$datetime = $str_ds;
			} elseif ($str_te == '00:00' || $str_te == '00:01') {
				$datetime = $str_ds.', '.$str_ts;
			} else {
				$datetime = $str_ds.', '.$str_ts.' - '.$str_te;			
			}
		} else {
			// Multi-day event
			if ($str_ts == '00:00' || $str_ts == '00:01') {
				$datetime = $str_ds.' – '.$str_de;
			} elseif ($str_te == '00:00' || $str_te == '00:01') {
				$datetime = $str_ds.', '.$str_ts.' – '.$str_de;
			} else {
				$datetime = $str_ds.', '.$str_ts.' – '.$str_de.', '.$str_te;			
			}			
		}
		if ($avlyst) $datetime = "<del>$datetime</del>";

		// Responsible
		$responsible = isset($row['responsible']) ? explode(",",$row['responsible']) : "";
		$str_re = $this->makeSimpleAuthorList($responsible);
		
		// Location
		$str_lo = ($this->show_location ? $row['location_name'] : "");

		// Calendar
		if ($this->show_calendar) {
			$calendar = stripslashes($row['calendar_name']);
			$calendar_id = intval($row['calendar_id']);
		}

		// More info
		$str_le = ($this->show_lead ? nl2br(stripslashes($row['lead'])) : "");

		
		$str_bo = ($this->show_body ? stripslashes($row['body']) : "");
		if ($this->show_body) {
			$tmp = trim(strip_tags($str_bo));
			if (empty($tmp)){ 
				$str_bo = $str_le; $str_le = ""; 
			}
		}
		
		if (!empty($str_le)) {
			$str_le = '<div style="margin-bottom:10px;font-size:120%;">'.$str_le.'</div>';
		}
		
		$ilf = $this->imagelookup_function;
		
		$log_code = "<a href=\"".$this->log_instance->getLinkToNewLogForm($id)."\" class=\"icn\" style='background-image:url(/images/icns/script_add.png);'>Skriv logg fra dette arrangementet</a>";
		if (isset($this->log)){
			$log_id = $this->log_instance->getLogIdFromEventId($id);
			if ($log_id != 0) {
				$log_code =  $this->log_instance->getExcerpt($log_id);
				//"<a href=\"".$this->log_instance->getLinkToLog($log_id)."\" class=\"icn\" style='background-image:url(/images/icns/book_open.png);'>Vis logg</a>";
			}
		}
		
		$image_code = "Ingen bilder lastet opp enda. Gå til <a href=\"/bildearkiv\">bildearkivet</a> for å laste opp bilder.";
		if (isset($this->iarchive_instance)) {
			$ia = $this->iarchive_instance;
			$albumId = $ia->getAlbumIdFromEventId($id);
			if ($albumId != 0) {
				$albumCount = $ia->getAlbumPhotoCount($albumId);
				$albumUrl = $ia->getAlbumURL($albumId);
				$albumPhotos = $ia->getAlbumPhotos($albumId, 5, false, 80);

				$image_code = "
					$albumPhotos
					<div>
						<a href=\"$albumUrl\">Vis alle $albumCount bilder i bildearkivet</a>
					</div>
				";
			}
		}

		$allow_edit = (($this->allow_editownentries && $row['creator'] == $this->login_identifier) || $this->allow_editothersentries);
		$allow_delete = (($this->allow_deleteownentries && $row['creator'] == $this->login_identifier) || $this->allow_deleteothersentries);


		$enrolment_code = "Ingen påmelding nødvendig. ";

		if (isset($this->enrolment_instance)) {
			$ei = $this->enrolment_instance;
			$enrolment_id = $ei->getEnrolmentIdFromEventId($id);
			if ($enrolment_id == 0) {
				if ($allow_edit && !$isPastEvent) $enrolment_code .= "<br /><a href=\"".$ei->getLinkToNewEnrolmentForm($calendar_id,$id)."\" class=\"icn\" style='background-image:url(/images/icns/add.png);'>Opprett påmelding</a>";
			} else {			
				$closingdate = $ei->getEnrolmentClosingDate($enrolment_id);
				if ($dt_start_unix < time()) {
					$d = $ei->getEnrolmentParticipants($enrolment_id);
					$paameldte = implode(', ',array_values($d['members']));
					$g = ($d['guests'] > 0) ? " og ".$d['guests']." gjester" : ""; 
					$enrolment_code = '<a href="'.$this->enrolment_instance->getLinkToEnrolment($enrolment_id).'">Påmeldingen</a> til dette arrangementet er stengt. Påmeldte:<div style="font-size:10px; padding:4px;">'.$paameldte.$g.'</div>';
				} else if (($closingdate > time()) || ($closingdate == 0)) {
					$enrolment_code = 'Arrangementet er åpent for påmelding. 
						<a href="'.$ei->getLinkToEnrolment($enrolment_id).'"  class="icn" style="background-image:url(/images/icns/arrow_right.png);font-weight:bold;">Gå til påmelding</a>';
				} else {
					$enrolment_code = 'Påmeldingen til dette arrangementet er i utgangspunktet stengt. 
						<a href="'.$ei->getLinkToEnrolment($enrolment_id).'">Vis påmeldte</a>';
				}
				$enrolment_code = '<div class="whiteInfoBox"><div class="inner" style="background:url('.$this->image_dir.'task2.gif) no-repeat 25px 20px;">'.$enrolment_code.'</div></div>';
			}
		}
		

		
		if ($avlyst) $cancel_str = $this->dropcancellink_template;
		else $cancel_str = $this->cancellink_template;

		$topic = stripslashes($row['caption']);
		$dt_start_str = date("j.n.y",$dt_start_unix);
		$dt_end_str = date("j.n.y",$dt_end_unix);
		$datestamp = ($dt_start_str == $dt_end_str) ? $dt_start_str : "$dt_start_str - $dt_end_str";
		$this->document_title = $topic.' '.$datestamp;

		call_user_func(
			$this->add_to_breadcrumb, 
			'<a href="'.$this->generateCoolURL('/'.($this->currentYear).'/').'">'.$this->currentYear.'</a>'
		); 
		call_user_func(
			$this->add_to_breadcrumb,
			"<a href=\"".$this->generateURL("")."\">$topic $datestamp</a>"
		);		
		
		
		// Finn forrige arrangement
		$res = $this->query("
			SELECT $tct.id, $tct.slug, UNIX_TIMESTAMP($tct.dt_start) as start, $tct.caption
			FROM $tct,$tcct
			WHERE $tct.dt_start < \"$dt_start\" AND $tct.calendar_id = $tcct.id AND $tct.calendar_id IN (".$this->show_calendars.")
			GROUP BY $tct.id ORDER BY $tct.dt_start DESC LIMIT 1");
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$forrigeArr = '<a href="'.$this->generateCoolURL('/'.date('Y',$row['start']).'/'.$row['slug']).'" title="'.stripslashes($row['caption']).'" class="icn" style="background-image:url(/images/icns/arrow_left.png);">Forrige arrangement</a>';
		} else {
			$forrigeArr = '<span class="icn" style="color:#999; background-image:url(/images/icns/arrow_left.png);">Forrige arrangement</span>';		
		}
		
		// Finn neste arrangement
		$res = $this->query("
			SELECT $tct.id, $tct.slug, UNIX_TIMESTAMP($tct.dt_start) as start, $tct.caption
			FROM $tct,$tcct
			WHERE $tct.dt_start > \"$dt_start\" AND $tct.calendar_id = $tcct.id AND $tct.calendar_id IN (".$this->show_calendars.")
			GROUP BY $tct.id ORDER BY $tct.dt_start LIMIT 1");
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$nesteArr = '<a href="'.$this->generateCoolURL('/'.date('Y',$row['start']).'/'.$row['slug']).'" title="'.stripslashes($row['caption']).'" class="icn" style="background-image:url(/images/icns/arrow_right.png);">Neste arrangement</a>';
		} else {
			$nesteArr = '<span class="icn" style="color:#999; background-image:url(/images/icns/arrow_right.png);">Neste arrangement</span>';		
		}
		
		$avlyst_text = "";
		if ($avlyst) {
			$avlyst_text = "<p class='warning' style='color:red;'>Dette arrangementet er dessverre avlyst.</p>";
		}
		
		$r1a = array(); $r2a = array();
		$r1a[] = "%displayIfPastEventOnly%"; $r2a[] = $isPastEvent ? 'block' : 'none';
		$r1a[] = "%title%"; 			$r2a[] = "$str_su";
		$r1a[] = "%avlyst%"; 			$r2a[] = "$avlyst_text";
		$r1a[] = "%datetime_start%"; 	$r2a[] = "$str_ds$str_ts";
		$r1a[] = "%datetime_end%"; 		$r2a[] = "$str_de$str_te";
		$r1a[] = "%datetime%"; 			$r2a[] = "$datetime";
		$r1a[] = "%date_start%"; 		$r2a[] = "$str_ds";
		$r1a[] = "%date_end%"; 			$r2a[] = "$str_de";
		$r1a[] = "%time_start%"; 		$r2a[] = "$str_ts";
		$r1a[] = "%time_end%"; 			$r2a[] = "$str_te";
		$r1a[] = "%location%"; 			$r2a[] = "$str_lo";
		$r1a[] = "%lead%"; 				$r2a[] = "$str_le";
		$r1a[] = "%body%"; 				$r2a[] = "$str_bo";
		$r1a[] = "%responsible%"; 		$r2a[] = "$str_re";
	
		$r1a[] = "%photos%"; 			$r2a[] = $image_code;
		$r1a[] = "%editurl%"; 			$r2a[] = $this->generateURL("action=editEvent");
		$r1a[] = "%deleteurl%"; 		$r2a[] = $this->generateURL("action=deleteEvent");
		$r1a[] = "%cancelurl%"; 		$r2a[] = $this->generateURL("action=cancelEvent");
		$r1a[] = "%dropcancelurl%"; 	$r2a[] = $this->generateURL("action=cancelEvent");
		$r1a[] = "%img_edit%"; 			$r2a[] = $this->image_dir."edit.gif";
		$r1a[] = "%img_delete%"; 		$r2a[] = $this->image_dir."delete.gif";
		$r1a[] = "%editlink%"; 			$r2a[] = ($allow_edit ? str_replace($r1a, $r2a, $this->editlink_template) : "");
		$r1a[] = "%deletelink%"; 		$r2a[] = (($allow_delete && !$isPastEvent) ? str_replace($r1a, $r2a, $this->deletelink_template) : "");
		$r1a[] = "%cancellink%"; 		$r2a[] = (($allow_delete && !$isPastEvent) ? str_replace($r1a, $r2a, $cancel_str) : "");
		$r1a[] = "%year%"; 				$r2a[] = $this->currentYear;
		$r1a[] = "%log%";		 		$r2a[] = $log_code;
		$r1a[] = "%enrolment%";		 	$r2a[] = $enrolment_code;
		$r1a[] = "%back%";		 		$r2a[] = '<a href="'.$this->generateCoolURL('/').'"class="icn" style="background-image:url(/images/icns/calendar.png);">Tilbake til oversikten</a>';
		$r1a[] = "%prev%";		 		$r2a[] = $forrigeArr;
		$r1a[] = "%next%";		 		$r2a[] = $nesteArr;
		if ($this->show_calendar) {
			$r1a[] = "%calendar%";		 	$r2a[] = $calendar;
		}
		
		$this->eventdetails_template = '
		
			<p class="hidefromprint">
					%prev%
					%back%
					%next%
			</p>
				
			<a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php" style="float:right;margin-top:20px;">Del</a>
			<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
			
			<h3>%title%</h3>
			<p class="hidefromprint">
				%editlink%
				%cancellink%
				%deletelink%
			</p>
			
			%avlyst%
			
			<div class="infoSection">
				<table class="dataTable">
					<tr>
						<th class="label">Tidspunkt:</th>
						<td class="data">%datetime%</td>
					</tr> 
					<tr class="spacer"><td colspan="2"><hr /></td></tr>
					<tr>
						<th class="label">Sted:</th>
						<td class="data">%location%</td>
					</tr>
					<tr class="spacer"><td colspan="2"><hr /></td></tr>
					<tr>
						<th class="label">Ansvarlig:</th>
						<td>%responsible%</td>
					</tr>
					<tr class="spacer"><td colspan="2"><hr /></td></tr>
					<tr>
						<th class="label">Kalender:</th>
						<td class="data">%calendar%</td>
					</tr>
					<tr class="spacer"><td colspan="2"><hr /></td></tr>
					<tr>
						<th class="label">Påmelding:</th>
						<td class="data">%enrolment%</td>
					</tr>
					<tr class="spacer"><td colspan="2"><hr /></td></tr>
					<tr>
						<th class="label">Mer info:</th>
						<td class="data">
						
						%lead%
						%body%
						
						</td>
					</tr>
				</table>
			</div>

			<div class="infoSection" style="display:%displayIfPastEventOnly%">
				<table class="dataTable">
					<tr>
						<th class="label">Bilder:</th>
						<td class="data">%photos%</td>
					</tr> 
					<tr class="spacer"><td colspan="2"><hr /></td></tr>
					<tr>
						<th class="label">Logg:</th>
						<td class="data">%log%</td>
					</tr>
				</table>
			</div>				
		';

		$output = str_replace($r1a, $r2a, $this->eventdetails_template).'
				<script type="text/javascript">
				//<![CDATA[
					YAHOO.util.Event.onDOMReady(function() {
						Nifty("div.whiteInfoBox");
					});
				//]]>
				</script>
			';

		$this->comment_desc = 5;
		$output .= $this->printComments($id);
		
		return $output;

	}
	
	function subscription(){
		
		$url_root = "http://".$_SERVER['SERVER_NAME'].ROOT_DIR;
		
		$calurl = $url_root . $this->generateCoolURL("/subscribe.ics");
		$abbcalurl = str_replace("http:","webcal:",$calurl);
		
		$r1a = array(); $r2a = array();
		$r1a[0] = "%abblink%"; 			$r2a[0] = $abbcalurl;
		$r1a[1] = "%downloadlink%"; 	$r2a[1] = $calurl;
		$r1a[2] = "%backlink%"; 		$r2a[2] = $this->generateURL("");
		$r1a[3] = "%year%"; 			$r2a[3] = date("Y",time());
		$r1a[4] = "%header%"; 			$r2a[4] = $this->header;
		$r1a[5] = "%img_goback%"; 		$r2a[5] = $this->image_dir."goback.gif";
		
		return str_replace($r1a, $r2a, $this->subscribeinfo_template);
	}
	
	function deleteEvent(){

		$id = $this->event_id;
		if ($id <= 0) $this->fatalError("Event not found");

		$output = "
			<h3>$this->deleteentry_caption</h3>
		";
		
		$res = $this->query(
			"SELECT caption, creator FROM $this->table_calendar WHERE id='$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("The entry does not exist!");
		$row = $res->fetch_assoc();
		$entrySubject = stripslashes($row['caption']);
		$entryCreator = $row['creator'];
		
		$allow_delete = (($this->allow_deleteownentries && $row['creator'] == $this->login_identifier) || $this->allow_deleteothersentries);
		if ($allow_delete){
			if ($row['creator'] != $this->login_identifier){
				$output .= "<p class='cal_notice'>".$this->othercreatornotice_caption."</p>";
			}
			$output .= '
				<form action="'.$this->generateURL('action=deleteEventDo').'" method="post">
				<p>
					'.str_replace("%TITLE",$entrySubject,$this->deleteconfirm_caption).'
				</p>
				<input type="submit" name="btn_yes" value="    '.$this->btn_yes_caption.'    " /> 
				<input type="button" name="btn_no" value="    '.$this->btn_no_caption.'    " onclick=\'window.location="'.$this->generateURL('show_event=$id').'"\' />
			</form>
			';
		} else {
			$output .= $this->permissionDenied();
		}
		return $output;
	}

	function cancelEvent(){

		$id = $this->event_id;
		if ($id <= 0) $this->fatalError("Event not found");
		
		$res = $this->query(
			"SELECT caption,creator,cancelled FROM $this->table_calendar WHERE id='$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("The entry does not exist!");
		$row = $res->fetch_assoc();
		$entrySubject = stripslashes($row['caption']);
		$entryCreator = $row['creator'];
		$avlyst = ($row['cancelled'] == true);


		if ($avlyst) $output = "<h3>$this->dropcancelentry_caption</h3>";
		else $output = "<h3>$this->cancelentry_caption</h3>";
		
		$allow_delete = (($this->allow_deleteownentries && $row['creator'] == $this->login_identifier) || $this->allow_deleteothersentries);
		if ($allow_delete){
			if ($row['creator'] != $this->login_identifier){
				$output .= "<p class='cal_notice'>".$this->othercreatornotice_caption."</p>";
			}
			$output .= '<form action="'.$this->generateURL('action=cancelEventDo').'" method="post">
				<p>';
			if ($avlyst) $output .= str_replace('%TITLE',$entrySubject,$this->dropcancelconfirm_caption);
			else $output .= str_replace('%TITLE',$entrySubject,$this->cancelconfirm_caption);
			$output .= '</p>
			<input type="submit" name="btn_yes" value="    '.$this->btn_yes_caption.'    " /> 
			<input type="button" name="btn_no" value="    '.$this->btn_no_caption.'    " onclick=\'window.location="'.$this->generateURL('show_event='.$id).'"\' />
			</form>';
		} else {
			$output .= $this->permissionDenied();
		}
		return $output;
	}

	function deleteEventDo(){

		$id = $this->event_id;
		if ($id <= 0) $this->fatalError("Event not found");

		$res = $this->query(
			"SELECT caption, creator FROM $this->table_calendar WHERE id='$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("The entry does not exist!");
		$row = $res->fetch_assoc();
		$allow_delete = (($this->allow_deleteownentries && $row['creator'] == $this->login_identifier) || $this->allow_deleteothersentries);
		if (!$allow_delete) return $this->permissionDenied();

		$this->query("DELETE FROM $this->table_calendar WHERE id='$id'");	
		$this->redirect($this->generateCoolURL("/"),"Hendelsen ble slettet.");
	}

	function cancelEventDo(){

		$id = $this->event_id;
		if ($id <= 0) $this->fatalError("Event not found");

		$res = $this->query(
			"SELECT caption,creator,cancelled FROM $this->table_calendar WHERE id='$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("The entry does not exist!");
		$row = $res->fetch_assoc();
		$allow_delete = (($this->allow_deleteownentries && $row['creator'] == $this->login_identifier) || $this->allow_deleteothersentries);
		$avlyst = ($row['cancelled'] == true);
		if (!$allow_delete){
			$this->permissionDenied();
			return 0;
		}

		if ($avlyst) {
			$this->query("UPDATE $this->table_calendar SET cancelled=0 WHERE id='$id'");	
			$this->redirect($this->generateURL(""),"Avlysningen ble fjernet.");
		} else {
			$this->query("UPDATE $this->table_calendar SET cancelled=1 WHERE id='$id'");	
			$this->redirect($this->generateURL(""),"Hendelsen ble avlyst.");		
		}
	}

	/* ############################################ SETTINGS #########################################################*/
	
	function editSettings() {
	
		if (!$this->allow_editsettings) return $this->permissionDenied();
	
		call_user_func(
			$this->add_to_breadcrumb, 
			'<a href="'.$this->generateCoolURL('/settings/').'">Innstillinger</a>'
		); 		
				
		$url_add = $this->generateURL('action=ajaxAddCategory');
		$url_add_js = $this->generateURL('action=ajaxAddCategory',true);
		$url_add_js = "AjaxRequestData(\"category_list\",\"$url_add_js\"); alert(\"Kategorien legges til nederst i listen!\"); return false;";
		$kategorier = "
			<p><a href='$url_add' onclick='$url_add_js'>Legg til</a></p>
			<div id='category_list'>
				".$this->makeCategoryList()."
			</div>
		";
		
		$url_add = $this->generateURL('action=ajaxAddResponsible');
		$url_add_js = $this->generateURL('action=ajaxAddResponsible',true);
		$url_add_js = "AjaxRequestData(\"responsible_list\",\"$url_add_js\"); alert(\"Rollen legges til nederst i listen!\"); return false;";
		$ansvarlige = "
			<p><a href=\"$url_add\" onclick='$url_add_js'>Legg til</a></p>
			<div id='responsible_list'>
				".$this->makeResponsibleList()."
			</div>
		";
		
		$url_add = $this->generateURL('action=ajaxAddLocation');
		$url_add_js = $this->generateURL('action=ajaxAddLocation',true);
		$url_add_js = "AjaxRequestData(\"location_list\",\"$url_add_js\"); alert(\"Stedet legges til nederst i listen!\"); return false;";
		$steder = "
			<p><a href='$url_add' onclick='$url_add_js'>Legg til</a></p>
			<div id='location_list'>
				".$this->makeLocationList()."
			</div>
		";
		
		$url_add = $this->generateURL('action=ajaxAddCalendar');
		$url_add_js = $this->generateURL('action=ajaxAddCalendar',true);
		$url_add_js = "AjaxRequestData(\"calendar_list\",\"$url_add_js\"); alert(\"Kalenderen legges til nederst i listen!\"); return false;";
		$kalendere = "
			<p><a href='$url_add' onclick='$url_add_js'>Legg til</a></p>
			<div id='calendar_list'>
				".$this->makeCalendarList()."
			</div>
		";
		
		
		return '
			<h3>Innstillinger</h3>
			<ul id="maintab" class="shadetabs">
				<li class="selected"><a href="#" rel="tcontent1">Kalendere</a></li>
				<li><a href="#" rel="tcontent2">Steder</a></li>
			</ul>

			<div class="tabcontentstyle">
		
				<div id="tcontent1" class="tabcontent">
					'.$kalendere.'
				</div>
		
				<div id="tcontent2" class="tabcontent">
					'.$steder.'
				</div>
			
			</div>
	
			<script type="text/javascript">
			//<![CDATA[
			
				function onTabChange(newTab) {
					// ignore
				}
				
				//Start Tab Content script for UL with id="maintab" Separate multiple ids each with a comma.
				initializetabcontent("maintab")
			
			//]]>
			</script>
		';
		
	}
	
	/* ================================== SETTINGS: CALENDARS ================================== */		
		
	/*
		Function: makeCalendarList
		Utility function
	*/
	function makeCalendarList() {
	
		$res = $this->query("SELECT id,caption,color FROM $this->table_calendars");
		
		$r = "<ul id='calendar_list_ul' class='noBullets'>\n";
		while ($row = $res->fetch_assoc()) {
			$id =  $row['id'];			
			$r .= "
			<li>
				<div id='calendar_$id'>
					".$this->makeCalendar($row)."
				</div>
			</li>\n";
			
		}
		$r .= "</ul>\n";
		return $r;
		
	}
	
	/*
		Function: makeCalendar
		Utility function
	*/
	function makeCalendar($row) {
		$id =  $row['id'];
		$caption = stripslashes($row['caption']);
		$color = stripslashes($row['color']);
		
		$url_edit = $this->generateURL(array("action=ajaxEditCalendar","calendarId=$id"));
		$url_edit_js = $this->generateURL(array("action=ajaxEditCalendar","calendarId=$id"),true);
		$url_edit_js = 'AjaxRequestData("calendar_'.$id.'","'.$url_edit_js.'"); return false;';
		
		return '<div style="background:'.$color.';width:16px;height:16px;float:left;margin-right:5px;"><!-- --></div>
			<strong>'.$caption.'</strong> 
			[<a href="'.$url_edit.'" onclick=\''.$url_edit_js.'\'>Rediger</a>]
		';
	}

	/*
		Function: ajaxPrintCalendar
		AJAX function
	*/
	function ajaxPrintCalendar($id = 0) {

		$id = intval($id);
		if ($id == 0) $id = intval($_GET['calendarId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
	
		if (!is_numeric($id)) $this->fatalError("invalid input .1");
		
		$res = $this->query("SELECT id,caption,color FROM $this->table_calendars WHERE id=$id");
		$row = $res->fetch_assoc();
		header("Content-Type: text/html; charset=utf-8"); 
		print $this->makeCalendar($row);
		exit();
	
	}	
	
	/*
		Function: ajaxAddCalendar
		AJAX function
	*/
	function ajaxAddCalendar() {

		if (!$this->allow_editsettings) return $this->permissionDenied();
		
		$this->query("INSERT INTO $this->table_calendars (caption) VALUES (\"Kalender uten navn\")");
		$id = $this->insert_id();
		
		print $this->makeCalendarList();
		exit();
		
	}
	
	/*
		Function: ajaxEditCalendar
		AJAX function
	*/
	function ajaxEditCalendar() {

		if (!$this->allow_editsettings) return $this->permissionDenied();

		$id = intval($_GET['calendarId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
		
		$res = $this->query(
			"SELECT id,caption,color,default_cal_page
			FROM $this->table_calendars
			WHERE id = '$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("Kalenderen eksisterer ikke.");
		$row = $res->fetch_assoc();
		
		$caption = stripslashes($row['caption']);
		$color = stripslashes($row['color']);
		$default_cal_page = intval($row['default_cal_page']);

		$url_post= $this->generateURL(array("action=ajaxSaveCalendar","calendarId=$id"));
		$url_post_js = $this->generateURL(array("action=ajaxSaveCalendar","calendarId=$id"),true);
		$url_post_js = 'AjaxFormSubmit("calendar_'.$id.'","'.ROOT_DIR.$url_post_js.'",this); return false;';
		
		$url_cancel_js = $this->generateURL(array("action=ajaxPrintCalendar","calendarId=$id"),true);
		$url_cancel_js = 'AjaxRequestData("calendar_'.$id.'","'.ROOT_DIR.$url_cancel_js.'"); return false;';
			
		$page_list = $this->listCalendarPages($id);
		$page_list_html = "<select name='default_cal_page'>";
		foreach ($page_list as $p) {
			$sel = ($default_cal_page == intval($p['id'])) ? ' selected="selected"':'';
			$page_list_html .= '<option value="'.$p['id'].'"'.$sel.'>'.$p['header'].'</option>';
		}
		$page_list_html .= "</select>";
		
		print '
			<form method="post" action="'.ROOT_DIR.$url_post.'" onsubmit=\''.$url_post_js.'\'>
				Navn: <input type="text" name="caption" value="'.$caption.'" /><br />
				Farge (hex): <input type="text" name="color" value="'.$color.'" /><br />
				Default side: '.$page_list_html.'<br />
				<input type="submit" value="Lagre" /> 
				<input type="button" name="cancel" value="Avbryt" onclick=\''.$url_cancel_js.'\' />
			</form>
		';
		exit();
		
	}
	
	/*
		Function: ajaxSaveCalendar
		AJAX function
	*/
	function ajaxSaveCalendar() {

		if (!$this->allow_editsettings) return $this->permissionDenied();

		$id = intval($_GET['calendarId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
	
		$res = $this->query("SELECT id FROM $this->table_calendars WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("Stedet eksisterer ikke.");
		$row = $res->fetch_assoc();
		
		$caption = addslashes($_POST['caption']);
		$color = addslashes($_POST['color']);
		$default_cal_page = addslashes($_POST['default_cal_page']);
		
		$this->query(
			"UPDATE $this->table_calendars SET
				caption = \"$caption\",
				color = \"$color\",
				default_cal_page = \"$default_cal_page\"
			WHERE $this->table_calendars_field_id = '$id'"
		);
	
		$this->ajaxPrintCalendar($id);
		
	}
	
	/* ================================== SETTINGS: LOCATIONS ================================== */		
	
	/*
		Function: makeLocationList
		Utility function
	*/
	function makeLocationList() {
	
		$res = $this->query(
			"SELECT 
				$this->table_locations_field_id as id,
				$this->table_locations_field_caption as caption
			FROM 
				$this->table_locations"
		);
		
		$r = "<ul id='location_list_ul'>\n";
		while ($row = $res->fetch_assoc()) {
			$id =  $row['id'];			
			$r .= "
			<li>
				<div id='location_$id'>
					".$this->makeLocation($row)."
				</div>
			</li>\n";
			
		}
		$r .= "</ul>\n";
		return $r;
	}

	/*
		Function: ajaxAutocompleteLocation
		AJAX function for auto-completion
	*/
	function ajaxAutocompleteLocation() {
	
		/** DEBUG:
		print "<ul>";
		foreach ($_GET as $n => $v) 
			print "<li>GET: $n - $v</li>";
		foreach ($_POST as $n => $v) 
			print "<li>POST: $n - $v</li>";
		print "</ul>";
		exit();
		*/

		$val = addslashes($_POST['cal_location'])."%";
		$res = $this->query("SELECT caption FROM $this->table_locations WHERE caption LIKE \"$val\" LIMIT 20");

		print "<ul>\n";
		while ($row = $res->fetch_assoc()) {
			print "<li>".stripslashes($row['caption'])."</li>\n";
		}
		print "</ul>\n";
		exit();
	
	}
	
	/*
		Function: makeLocation
		Utility function
	*/
	function makeLocation($row) {
		$id =  $row['id'];
		$caption = stripslashes($row['caption']);
		
		$url_edit = $this->generateURL(array("action=ajaxEditLocation","locationId=$id"));
		$url_edit_js = $this->generateURL(array("action=ajaxEditLocation","locationId=$id"),true);
		$url_edit_js = "AjaxRequestData(\"location_$id\",\"$url_edit_js\"); return false;";
		
		return "<strong>$caption</strong> [<a href=\"$url_edit\" onclick='$url_edit_js'>Rediger</a>]";
	}
	
	/*
		Function: ajaxPrintLocation
		AJAX function
	*/
	function ajaxPrintLocation($id = 0) {
	
		$id = intval($id);
		if ($id == 0) $id = intval($_GET['locationId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
		
		$res = $this->query("SELECT id,caption FROM $this->table_locations WHERE id=$id");
		$row = $res->fetch_assoc();
		print $this->makeLocation($row);
		exit();
	
	}
	
	/*
		Function: ajaxAddLocation
		AJAX function
	*/
	function ajaxAddLocation() {

		if (!$this->allow_editsettings) return $this->permissionDenied();
		
		$this->query(
			"INSERT INTO $this->table_locations (caption) VALUES (\"Sted uten navn\")"
		);
		$id = $this->insert_id();
		
		print $this->makeLocationList();
		exit();
		
	}
	
	/*
		Function: ajaxEditLocation
		AJAX function
	*/
	function ajaxEditLocation() {

		if (!$this->allow_editsettings) return $this->permissionDenied();

		$id = intval($_GET['locationId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
		
		$res = $this->query("SELECT caption FROM $this->table_locations WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("Stedet eksisterer ikke.");
		$row = $res->fetch_assoc();
		
		$caption = stripslashes($row['caption']);

		$url_post= $this->generateURL(array("action=ajaxSaveLocation","locationId=$id"));
		$url_post_js = $this->generateURL(array("action=ajaxSaveLocation","locationId=$id"),true);
		$url_post_js = "AjaxFormSubmit('location_$id','$url_post_js',this); return false;";
		
		$url_cancel_js = $this->generateURL(array("action=ajaxPrintLocation","locationId=$id"),true);
		$url_cancel_js = "AjaxRequestData(\"location_$id\",\"$url_cancel_js\"); return false;";
			
		print "
			<form method=\"post\" action=\"$url_post\" onsubmit=\"$url_post_js\">
				<input type='text' name='caption' value=\"$caption\" /><br />
				<input type='submit' value='Lagre' /> 
				<input type='button' name='cancel' value='Avbryt' onclick='$url_cancel_js' />
			</form>
		";
		exit();
		
	}
	
	/*
		Function: saveLocation
		AJAX function
	*/
	function saveLocation() {

		if (!$this->allow_editsettings) return $this->permissionDenied();

		$id = intval($_GET['locationId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
	
		$res = $this->query("SELECT id FROM $this->table_locations WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("Stedet eksisterer ikke.");
		$row = $res->fetch_assoc();
		
		$caption = addslashes($_POST['caption']);

		$this->query("UPDATE $this->table_locations SET caption = \"$caption\" WHERE id = '$id'");
	
		$this->ajaxPrintLocation($id);
		
	}
	
	/* ================================== SETTINGS: SUBJECTS =================================== */		
	
	/*
		Function: ajaxAutocompleteSubject
		AJAX function for auto-completion
	*/
	function ajaxAutocompleteSubject() {
	
		/** DEBUG:
		print "<ul>";
		foreach ($_GET as $n => $v) 
			print "<li>GET: $n - $v</li>";
		foreach ($_POST as $n => $v) 
			print "<li>POST: $n - $v</li>";
		print "</ul>";
		exit();
		*/

		$val = addslashes($_POST['cal_subject'])."%";
		$res = $this->query(
			"SELECT id,caption FROM $this->table_calendar WHERE caption LIKE \"$val\"
			GROUP BY caption LIMIT 20"
		);
		header("Content-Type: text/html; charset=utf-8"); 
		print "<ul>\n";
		while ($row = $res->fetch_assoc()) {
			print "<li>".stripslashes($row['caption'])."</li>\n";
		}
		print "</ul>\n";
		exit();
	
	}

	/* ================================ SETTINGS: RESPONSIBLES ================================= */		
	
	/*
		Function: makeResponsibleList
		Utility function
	*/
	function makeResponsibleList() {
	
		$res = $this->query("SELECT id,caption FROM $this->table_responsible WHERE page=".$this->page_id);
		
		$r = "<ul id='responsible_list_ul'>\n";
		while ($row = $res->fetch_assoc()) {
			$id =  $row['id'];			
			$r .= "
			<li>
				<div id='responsible_$id'>
					".$this->makeResponsible($row)."
				</div>
			</li>\n";
			
		}
		$r .= "</ul>\n";
		return $r;
	}

	/*
		Function: makeResponsible
		Utility function
	*/
	function makeResponsible($row) {
		$id =  $row['id'];
		$caption = stripslashes($row['caption']);
		
		$url_edit = $this->generateURL(array("action=ajaxEditResponsible","responsibleId=$id"));
		$url_edit_js = $this->generateURL(array("action=ajaxEditResponsible","responsibleId=$id"),true);
		$url_edit_js = "AjaxRequestData(\"responsible_$id\",\"$url_edit_js\"); return false;";
		
		return "<strong>$caption</strong> [<a href=\"$url_edit\" onclick='$url_edit_js'>Rediger</a>]";
	}
	
	/*
		Function: ajaxPrintResponsible
		AJAX function
	*/
	function ajaxPrintResponsible($id = 0) {
		
		$id = intval($id);
		if ($id == 0) $id = intval($_GET['locationId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
		
		$res = $this->query("SELECT id,caption FROM $this->table_responsible WHERE id=$id");
		header("Content-Type: text/html; charset=utf-8"); 
		$row = $res->fetch_assoc();
		print $this->makeResponsible($row);
		exit();
	
	}
		
	/*
		Function: ajaxAddResponsible
		AJAX function
	*/
	function ajaxAddResponsible() {

		if (!$this->allow_editsettings) return $this->permissionDenied();
		
		$this->query(
			"INSERT INTO $this->table_responsible (page,caption) VALUES ($this->page_id,\"Ansvarlig uten navn\")"
		);
		$id = $this->insert_id();
		
		print $this->makeResponsibleList();
		exit();
		
	}
	
	/*
		Function: ajaxEditResponsible
		AJAX function
	*/
	function ajaxEditResponsible() {

		if (!$this->allow_editsettings) return $this->permissionDenied();

		$id = intval($_GET['responsibleId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
		
		$res = $this->query("SELECT id,caption FROM $this->table_responsible WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("Ansvarlig eksisterer ikke.");
		$row = $res->fetch_assoc();
		
		$caption = stripslashes($row['caption']);

		$url_post= $this->generateURL(array("action=ajaxSaveResponsible","responsibleId=$id"));
		$url_post_js = $this->generateURL(array("action=ajaxSaveResponsible","responsibleId=$id"),true);
		$url_post_js = "AjaxFormSubmit('responsible_$id','$url_post_js',this); return false;";
		
		$url_cancel_js = $this->generateURL(array("action=ajaxPrintResponsible","responsibleId=$id"),true);
		$url_cancel_js = "AjaxRequestData(\"responsible_$id\",\"$url_cancel_js\"); return false;";
			
		print "
			<form method=\"post\" action=\"$url_post\" onsubmit=\"$url_post_js\">
				<input type='text' name='caption' value=\"$caption\" /><br />
				<input type='submit' value='Lagre' /> 
				<input type='button' name='cancel' value='Avbryt' onclick='$url_cancel_js' />
			</form>
		";
		exit();
		
	}
	
	/*
		Function: ajaxSaveResponsible
		AJAX function
	*/
	function ajaxSaveResponsible() {

		if (!$this->allow_editsettings) return $this->permissionDenied();

		$id = intval($_GET['responsibleId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
	
		$res = $this->query("SELECT id FROM $this->table_responsible WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("Ansvarlig eksisterer ikke.");
		$row = $res->fetch_assoc();
		
		$caption = addslashes($_POST['caption']);

		$this->query("UPDATE $this->table_responsible SET caption=\"$caption\" WHERE id=$id");
	
		$this->printResponsibleFromID($id);
		
	}

	/* ================================ SETTINGS: CATEGORIES =================================== */		
	
	/*
		Function: makeResponsible
		Utility function
	*/
	function makeCategoryList() {		
		
		$res = $this->query(
			"SELECT 
				$this->table_categories.$this->category_field_id as id,
				$this->table_categories.$this->category_field_caption as caption,
				$this->table_categories.$this->category_field_defaultname as def_name,
				$this->table_categories.$this->category_field_defaulttime as def_time,
				$this->table_locations.$this->table_locations_field_caption as def_loc,
				$this->table_responsible.$this->responsible_field_caption as def_responsible
			FROM 
				$this->table_categories
			LEFT JOIN 
				$this->table_locations
			ON 
				$this->table_categories.$this->category_field_defaultlocation=$this->table_locations.$this->table_locations_field_id
			LEFT JOIN 
				$this->table_responsible
			ON 
				$this->table_categories.$this->category_field_defaultresponsible=$this->table_responsible.$this->responsible_field_id
			WHERE
				$this->table_categories.$this->category_field_page=".$this->page_id
		);
		
		$r = "<ul id='category_list_ul'>\n";
		while ($row = $res->fetch_assoc()) {
			$id =  $row['id'];			
			$r .= "
			<li>
				<div id='category_$id'>
					".$this->makeCategory($row)."
				</div>
			</li>\n";
			
		}
		$r .= "</ul>\n";
		return $r;
	}

	/*
		Function: makeCategory
		Utility function
	*/
	function makeCategory($row) {
		$id =  $row['id'];
		$caption = stripslashes($row['caption']);
		$def_name = stripslashes($row['def_name']);
		if (empty($def_name)) $def_name = "&lt;Ingen verdi&gt;";
		$def_time = stripslashes($row['def_time']);
		$def_responsible = stripslashes($row['def_responsible']);
		if (empty($def_responsible)) $def_responsible = "&lt;Ingen verdi&gt;";
		if (empty($def_time)) {
			$def_time = "&lt;Ingen verdi&gt;";
		} else {
			$def_time = explode("-",$def_time);
			$dt_start = date("H:i",$def_time[0]);
			$dt_end = date("H:i",$def_time[1]);
			if ($dt_start == "00:01") $dt_start = "?";
			if ($dt_end == "00:01") $dt_end = "?";
			$def_time = "$dt_start - $dt_end";
		}
		$def_loc = stripslashes($row['def_loc']);
		if (empty($def_loc)) $def_loc = "&lt;Ingen verdi&gt;";
		
		$url_edit = $this->generateURL(array("action=ajaxEditCategory","categoryId=$id"));
		$url_edit_js = $this->generateURL(array("action=ajaxEditCategory","categoryId=$id"),true);
		$url_edit_js = "AjaxRequestData(\"category_$id\",\"$url_edit_js\"); return false;";
		
		return "<strong>$caption</strong> [<a href=\"$url_edit\" onclick='$url_edit_js'>Rediger</a>]<br />
				<table style='font-size:10px; color: #666666; margin-bottom: 10px;'>
					<tr><td style='text-align:right;'>Default name: </td><td>$def_name</td></tr>
					<tr><td style='text-align:right;'>Default time: </td><td>$def_time</td></tr>
					<tr><td style='text-align:right;'>Default location: </td><td>$def_loc</td></tr>
					<tr><td style='text-align:right;'>Default responsible: </td><td>$def_responsible</td></tr>
				</table>
		";
	}	

	/*
		Function: ajaxPrintCategory
		AJAX function
	*/
	function ajaxPrintCategory($id = 0) {
	
		$id = intval($id);
		if ($id == 0) $id = intval($_GET['categoryId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
		
		$res = $this->query(
			"SELECT 
				$this->table_categories.$this->category_field_id as id,
				$this->table_categories.$this->category_field_caption as caption,
				$this->table_categories.$this->category_field_defaultname as def_name,
				$this->table_categories.$this->category_field_defaulttime as def_time,
				$this->table_locations.$this->table_locations_field_caption as def_loc,
				$this->table_responsible.$this->responsible_field_caption as def_responsible
			FROM 
				$this->table_categories
			LEFT JOIN 
				$this->table_locations
			ON 
				$this->table_categories.$this->category_field_defaultlocation=$this->table_locations.$this->table_locations_field_id
			LEFT JOIN 
				$this->table_responsible
			ON 
				$this->table_categories.$this->category_field_defaultresponsible=$this->table_responsible.$this->responsible_field_id
			WHERE 
				$this->table_categories.$this->category_field_id = '$id'"
		);

		$row = $res->fetch_assoc();
		print $this->makeCategory($row);
		exit();
	
	}
	
	/*
		Function: ajaxAddCategory
		AJAX function
	*/	
	function ajaxAddCategory() {

		if (!$this->allow_editsettings) return $this->permissionDenied();
		
		$this->query(
			"INSERT INTO $this->table_categories (page,caption) VALUES (".$this->page_id.",\"Kategori uten navn\")"
		);
		$id = $this->insert_id();
		
		print $this->makeCategoryList();
		exit();
		
	}
	
	/*
		Function: ajaxEditCategory
		AJAX function
	*/	
	function ajaxEditCategory() {

		if (!$this->allow_editsettings) return $this->permissionDenied();

		$id = intval($_GET['categoryId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
		
		$res = $this->query(
			"SELECT 
				$this->category_field_caption as caption,
				$this->category_field_defaultname as def_name,
				$this->category_field_defaulttime as def_time,
				$this->category_field_defaultlocation as def_loc,
				$this->category_field_defaultresponsible as def_responsible
			FROM 
				$this->table_categories
			WHERE
				$this->category_field_id = '$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("Kategorien eksisterer ikke.");
		$row = $res->fetch_assoc();
		
		$caption = stripslashes($row['caption']);
		$def_name = stripslashes($row['def_name']);
		$def_time = stripslashes($row['def_time']);
		$def_loc = stripslashes($row['def_loc']);
		$def_responsible = stripslashes($row['def_responsible']);
		
		$res2 = $this->query(
			"SELECT 
				$this->table_locations_field_id as id,
				$this->table_locations_field_caption as caption
			FROM 
				$this->table_locations"
		);
		$def_location_select = "<select name='def_loc'>\n";
		$def_location_select .= "<option value='-1'>Ingen</option>\n";
		while ($row2 = $res2->fetch_assoc()) {
			$loc_id = $row2['id'];
			$loc_caption = stripslashes($row2['caption']);
			$loc_selected = ($loc_id == $def_loc) ? " selected='selected'" : "";
			$def_location_select .= "<option value='$loc_id'$loc_selected>$loc_caption</option>\n";
		}
		$def_location_select .= "</select>\n";

		$res2 = $this->query(
			"SELECT 
				$this->responsible_field_id as id,
				$this->responsible_field_caption as caption
			FROM 
				$this->table_responsible 
			WHERE $this->table_responsible.$this->responsible_field_page=".$this->page_id
		);
		$def_responsible_select = "<select name='def_responsible'>\n";
		$def_responsible_select .= "<option value='-1'>Ingen</option>\n";
		while ($row2 = $res2->fetch_assoc()) {
			$loc_id = $row2['id'];
			$loc_caption = stripslashes($row2['caption']);
			$loc_selected = ($loc_id == $def_responsible) ? " selected='selected'" : "";
			$def_responsible_select .= "<option value='$loc_id'$loc_selected>$loc_caption</option>\n";
		}
		$def_responsible_select .= "</select>\n";

		
		
		if (strpos($def_time, "-") === false) {
			$def_timefield = $this->generateTimeField("categoryform_$id", 'def_time_start')." - ".$this->generateTimeField("categoryform_$id", 'def_time_end');
		} else {
			$def_time = explode("-",$def_time);
			$def_timefield = $this->generateTimeField("categoryform_$id", 'def_time_start', $def_time[0])." - ".$this->generateTimeField("categoryform_$id", 'def_time_end', $def_time[1]);		
		}
		
		$url_post= $this->generateURL(array("action=ajaxSaveCategory","categoryId=$id"));
		$url_post_js = $this->generateURL(array("action=ajaxSaveCategory","categoryId=$id"),true);
		$url_post_js = "AjaxFormSubmit('category_$id','$url_post_js',this); return false;";
		
		$url_cancel_js = $this->generateURL(array("action=ajaxPrintCategory","categoryId=$id"),true);
		$url_cancel_js = "AjaxRequestData(\"category_$id\",\"$url_cancel_js\"); return false;";
			
		print "
			<form method=\"post\" id=\"categoryform_$id\" name=\"categoryform_$id\" action=\"$url_post\" onsubmit=\"$url_post_js\">
				<input type='text' name='caption' value=\"$caption\" /><br />
				&nbsp;&nbsp;&nbsp;&nbsp;Default name: <input type='text' name='def_name' value=\"$def_name\" /><br />
				&nbsp;&nbsp;&nbsp;&nbsp;Default time: $def_timefield<br />
				&nbsp;&nbsp;&nbsp;&nbsp;Default location: $def_location_select<br />
				&nbsp;&nbsp;&nbsp;&nbsp;Default responsible: $def_responsible_select<br />
				<input type='submit' value='Lagre' /> 
				<input type='button' name='cancel' value='Avbryt' onclick='$url_cancel_js' />
			</form>";
		
		exit();
		
	}
	
	/*
		Function: ajaxSaveCategory
		AJAX function
	*/	
	function ajaxSaveCategory() {

		if (!$this->allow_editsettings) return $this->permissionDenied();

		$id = intval($_GET['categoryId']);
		if ($id <= 0) $this->fatalError("invalid input .1");
	
		$res = $this->query(
			"SELECT $this->category_field_id FROM $this->table_categories WHERE $this->category_field_id = '$id'"
		);
		if ($res->num_rows != 1) $this->fatalError("Kategorien eksisterer ikke.");
		$row = $res->fetch_assoc();
		
		$caption = addslashes($_POST['caption']);
		$def_name = addslashes($_POST['def_name']);
		if ($_POST['def_time_start_h'] == '00' && $_POST['def_time_start_m'] == '01' && $_POST['def_time_end_h'] == '00' && $_POST['def_time_end_m'] == '01') {
			$def_time = '';
		} else {
			$dts = $this->generateTimeStamp(0,0,0,$_POST['def_time_start_h'],$_POST['def_time_start_m']);
			$dte = $this->generateTimeStamp(0,0,0,$_POST['def_time_end_h'],$_POST['def_time_end_m']);
			$def_time = "$dts-$dte";
		}
		$def_loc = addslashes($_POST['def_loc']);
		if ($def_loc == "-1") $def_loc = "";		
		$def_responsible = addslashes($_POST['def_responsible']);
		if ($def_responsible == "-1") $def_responsible = "";		
		$this->query(
			"UPDATE $this->table_categories SET
				$this->category_field_caption = \"$caption\",
				$this->category_field_defaultname = \"$def_name\",
				$this->category_field_defaulttime = \"$def_time\",
				$this->category_field_defaultlocation = \"$def_loc\",
				$this->category_field_defaultresponsible = \"$def_responsible\"				
			WHERE $this->category_field_id = '$id'"
		);
		
		$this->ajaxPrintCategory($id);
		
	}

		
	/* ########################################## VCAL, RSS #######################################################*/

	
	function prepareForCalendar($str){
		$str = stripslashes($str);
		$str = parse_bbcode($str);
		$str = str_replace("\r\n","\n",$str);
		$str = str_replace("\r","\n",$str);
		$str = str_replace("\n","\\n",$str);
		$str = strip_tags($str);
		return $str;
	}
	
	function viewVCal(){
		
		if (isset($_GET['kalenderbruker'])){
			$bruker = $_GET['kalenderbruker'];
			if (!is_numeric($bruker)) exit;
		} else {
			if ($this->show_participants && $this->use_login_identifier)  exit;
		}

		$output = "BEGIN:VCALENDAR
PRODID:$this->vcal_prodid
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:$this->site_name
BEGIN:VTIMEZONE
TZID:Europe/Oslo
LAST-MODIFIED:20060522T184430Z
BEGIN:DAYLIGHT
DTSTART:20050327T010000
TZOFFSETTO:+0200
TZOFFSETFROM:+0000
TZNAME:CEST
END:DAYLIGHT
BEGIN:STANDARD
DTSTART:20051030T030000
TZOFFSETTO:+0100
TZOFFSETFROM:+0200
TZNAME:CET
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:20060326T030000
TZOFFSETTO:+0200
TZOFFSETFROM:+0100
TZNAME:CEST
END:DAYLIGHT
END:VTIMEZONE
";

		// Make some short vars to make the code more readable...
		$tct = $this->table_calendar;
		$tlt = $this->table_locations;
		$tcct = $this->table_calendars;
		
		$show_futureonly = false;

		$res = $this->query(
				"SELECT 
					$tct.$this->table_calendar_field_id as id,
					UNIX_TIMESTAMP($tct.$this->table_calendar_field_startdate) as start,
					UNIX_TIMESTAMP($tct.$this->table_calendar_field_enddate) as end,
					$tct.$this->table_calendar_field_caption as caption,
					$tct.$this->table_calendar_field_creator as creator".
					($this->show_notes    ? ",$tct.$this->table_calendar_field_lead as lead" : "").
					($this->show_location ? ",$tlt.$this->table_locations_field_caption as location" : "").
					($this->show_calendar ? ",$tcct.$this->table_calendars_field_id as cal_id
											 ,$tcct.$this->table_calendars_field_caption as cal_name
											 ,$tcct.$this->table_calendars_field_color as cal_color" : "").
				" FROM 
					$tct".
					($this->show_location ? ",$tlt":"").
					($this->show_calendars ? ",$tcct":"").
				" WHERE
					1=1".
					($this->show_location ? " AND $tct.$this->table_calendar_field_location = $tlt.$this->table_locations_field_id" : "").
					($show_futureonly ? " AND $tct.$this->table_calendar_field_enddate > NOW()" : "").
					($this->show_calendar ? " AND $tct.$this->table_calendar_field_calendar = $tcct.$this->table_calendars_field_id
											  AND $tct.$this->table_calendar_field_calendar IN (".$this->show_calendars.")" : "").
				" GROUP BY 
					$tct.$this->table_calendar_field_id
				ORDER BY 
					$tct.$this->table_calendar_field_startdate"
			);
						
		while ($row = $res->fetch_assoc()){

			$startdate = $row['start'];
			$time = date("H:i",$startdate);
			$startdate = ($time == "00:01" ? 
				";VALUE=DATE:".date("Ymd",$startdate) : 
				";TZID=Europe/Oslo:".date("Ymd\THis",$startdate)
			);
			$enddate = $row['end'];
			$time = date("H:i",$enddate);
			$enddate = ($time == "00:01" ? 
				";VALUE=DATE:".date("Ymd",($enddate + 60*60*24)) : 
				";TZID=Europe/Oslo:".date("Ymd\THis",$enddate)
			);
			$rid = $row['id'];
			$rcaption = $row['caption'];
			$uid = "00000$rid@".$this->server_name;
			$url_root = "http://".$_SERVER['SERVER_NAME'].ROOT_DIR;
			$uri = $url_root . $this->generateCoolURL('/','show_event='.$rid);
			$lead = $this->prepareForCalendar($row['lead']);
			$output .= "BEGIN:VEVENT
DTSTART$startdate
DTEND$enddate
UID:$uid
CATEGORIES:Speider
".(($this->show_location && isset($row['location'])) ? "LOCATION:".$row['location'] : "")."
SUMMARY:$rcaption
DESCRIPTION:$lead
URL;VALUE=URI:$uri
CLASS:PUBLIC
END:VEVENT
";
	
		}
		$output .= "END:VCALENDAR
";

		$output = str_replace("\r\n","\n",$output);
		$output = str_replace("\r","\n",$output);
		$output = str_replace("\n","\r\n",$output); // ics må ha DOS linebreaks :(
		
		header("Content-Type: text/calendar; encoding=utf-8");
	  	header("Content-Disposition: inline; filename=subscribe.ics");
		print $output;
		exit();
		
	}
	
	function viewRss() {
		
		$this->calview_template2 = "%entries%";		
		$this->calview_entry_template = $this->rss_entry_template;

		$rssTOC = ""; $rssItems = $this->viewCalendar();
		foreach ($this->rss_toc as $r) {
			$rssTOC .= "
                    <rdf:li resource=\"".str_replace("&","&amp;",$r)."\" />";
		}
		
		header("Content-Type: application/rss+xml; charset=utf-8");
		print '<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF 
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
    xmlns:dc="http://purl.org/dc/elements/1.1/" 
    xmlns:syn="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:admin="http://webns.net/mvcb/" 
    xmlns="http://purl.org/rss/1.0/"
>
    <channel rdf:about="http://'.$this->server_name.'/">
        <title>'.$this->site_name.': '.$this->header.'</title>
        <link>http://'.$this->server_name.'/</link>
        <image rdf:resource="http://'.$this->server_name.'/images/scoutlogo2.gif" />
        <items>
            <rdf:Seq>'.$rssTOC.'
            </rdf:Seq>
        </items>
    </channel>
    <image rdf:about="http://'.$this->server_name.'/images/scoutlogo2.gif">
        <title>'.$this->site_name.'</title>
        <link>http://'.$this->server_name.'</link>
        <url>http://'.$this->server_name.'/images/scoutlogo2.gif</url>
    </image>
    
    '.$rssItems.'

</rdf:RDF>
';
		exit();
		
	}

    /** COMMENTS **/
	
	function subscribeToThread($post_id = 0, $redirect = true) {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = $this->event_id;
	    @parent::subscribeToThread($post_id, $redirect);
	}

	function unsubscribeFromThread($post_id = 0, $redirect = true) {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = $this->event_id;
	    @parent::unsubscribeFromThread($post_id, $redirect);
	}

	function saveComment($post_id = 0, $context = '') {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = intval($this->event_id);
	    if ($post_id <= 0) { $this->fatalError("incorrect input!"); }
		
		$tc = $this->table_calendar;
		$res = $this->query("SELECT caption FROM $tc WHERE id=$post_id");
		if ($res->num_rows != 1) $this->fatalError("Artikkelen ble ikke funnet!");

		$row = $res->fetch_assoc();
		$context = 'hendelsen «'.stripslashes($row['topic']).'»';
	    @parent::saveComment($post_id, $context);
	}
	
}

?>
