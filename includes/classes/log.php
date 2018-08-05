<?php
class log extends comments {

	// Useful variable
	var $current_log = 0;

	var $allow_viewlogs = false;
	var $allow_addlog = false;
	var $allow_editotherslogs = false;
	var $allow_deleteownlogs = false;
	var $allow_deleteotherslogs = false;
	
	var $calendar;
	var $calendar_instance;
	var $imagearchive;
	var $imagearchive_instance;
	
	public $table_log = "blog";
	public $table_log_field_id = "id";
	public $table_log_field_eventid = "event_id";
	
	var $table_imagedirs = "cms_pages";
	
	var $table_calendars = "cal_calendars";
	
	var $table_images = "images";
	var $table_images_field_id = "id";
	var $table_images_field_extension = "extension";
	var $table_images_field_caption = "caption";
	var $table_images_field_width = "width";
	var $table_images_field_height = "height";
	var $table_images_field_category = "category";
	var $table_images_field_timestamp = "timestamp";
	var $table_images_field_size = "size";
	var $table_images_field_uploader = "uploader";
	var $table_images_field_parent = "parent";
	
	var $lead_image_dir;
	var $leads_per_page = 10;
	var $images_per_page = 20;
	var $page_no = 1;
	
	var $label_save = "Lagre";
	var $label_addlog = "Fortsett";
	var $label_addarticle = "Skriv logg/referat";
	var $label_editarticle = "Skriv logg/referat";
	var $label_writehelp = "Hvordan skrive logg?";
	var $label_articledoesntexist = "Artikkelen eksisterer ikke";
	var $label_readmore = "Les mer";
	var $label_editlog = "Rediger";
	var $label_deletelog = "Slett";
	var $label_pagexofy = "Side %x% av %y%";
	var $label_showincalendar = "Vis i terminliste";
	var $label_newer = "&lt;&lt; Nyere logger";
	var $label_older = "Eldre logger &gt;&gt;";
	
	var $template_leadlistheader = '
		<p>
			<a href="%addarticleurl%" class="icn" style="background-image:url(/images/icns/add.png);">%addarticle%</a> 
			<a href="%helpurl%" class="icn" style="background-image:url(/images/icns/help.png);">%writehelp%</a>
		</p>
	';
	
	var $template_leadlistfooter = "";
	
	var $template_leadlistitem = '
		<div class="%divclass%">
			<h2 class="post-title"><a href="%url%">%topic% %header%</a></h2>
			<h3 class="author">Loggført av %authors%</h3>%notpublishedyet%
			<div style="float:right;">%image%</div>
			<p>%lead%</p>%options%
			<div style="clear:both; height: 1px;"><!-- --></div>
		</div>
	';
	
	var $template_addlogform = '
		<h2>%addarticle%</h2>
		<form method="post" action="%posturl%">
			<p>
				Hva vil du skrive logg fra?
				<select name="event_id" id="event_id">
					%calendar_list%
				</select>
			</p>
			<p>
				<input type="submit" value="%submit%" />
			</p>
		</form>
	';
	

	var $template_dir 					= '../includes/templates/';
	var $template_editarticleform 		= 'log_editarticleform.html';
	
	var $template_viewarticle_short = '
		<div class="article">
			%authors%
			
			<h2 class="date-header">%header%</h2>
			<h2 class="post-title">%topic%</h2>
			<div style="font-size:10px;" class="hidefromprint">%calendar_link%</div>
			<div class="post">
			<div class="post-body">
				%lead%
				%images_location3%
				%images_location2%
			</div></div>			
		</div>	
		<p class="hidefromprint" style="text-align:center;">
				%prev%
				&nbsp;
				%next%
		</p>
	';

	var $template_viewarticle_long = '
		<div class="article">
			%authors%
			
			<h2 class="date-header">%header%</h2>
			<h2 class="post-title">%topic%</h2>
			<div style="font-size:10px;" class="hidefromprint">%calendar_link%</div>
			<div class="post">
			<div class="post-body">
				<p class="lead">%lead%</p>
				%images_location3%
				<p>%body%</p>
				%images_location2%
			</div></div>			
		</div>
		<p class="hidefromprint" style="text-align:center;">
				%prev%
				&nbsp;
				%next%
		</p>

	
	';
	
	function __construct() {
		$this->table_log = DBPREFIX.$this->table_log;
		$this->table_images = DBPREFIX.$this->table_images;
		$this->table_imagedirs = DBPREFIX.$this->table_imagedirs;
		$this->table_comments = DBPREFIX.'comments';
		$this->table_calendars = DBPREFIX.$this->table_calendars;
	}
	
	function initialize() {
	    @parent::initialize();
		
		// Stop here if using to create sitemap
		if (empty($this->lookup_member)) return;

		$this->initializeCalendar();
		$this->initializeImageArchive();

		array_push($this->getvars,'action','page','view_log','event_id');
		
		if ((isset($_GET['page'])) && (is_numeric($_GET['page']))){
			$this->page_no = ($_GET['page']); 
		} else { 
			$this->page_no = 1;
		}
	}
	
	function run(){
		$this->initialize();

		/* 
		##	Determine current log
		*/
		$log_id = 0;
		if (isset($this->coolUrlSplitted[0]) && is_numeric($this->coolUrlSplitted[0]) && isset($this->coolUrlSplitted[1])) {
			$year = intval($this->coolUrlSplitted[0]);
			$slug = addslashes($this->coolUrlSplitted[1]);
			$this->calEvent = $this->calendar_instance->getEventDetails($year,$slug);
            $eventId = intval($this->calEvent['id']);
			
			$res = $this->query("SELECT id FROM $this->table_log WHERE event_id=$eventId");
			if ($res->num_rows != 1) return $this->notSoFatalError("Siden finnes ikke");
			$row = $res->fetch_assoc();
			$log_id = intval($row['id']);
		}		
		$this->current_log = $log_id;
		
		/* 
		##	Determine and execute requested action
		*/
		$overviewActions = array(
			'addLogForm','addLog','addLogDo','viewLogs','expandShortUrl'
		);
		
		$logActions = array(
			'editLog','saveLog','deleteLog','deleteLogDo','viewLog',
			'ajaxGetAuthorList',
			'ajaxGetPhotoAlbumForEventId',
			'saveComment','deleteCommentDo','subscribeToThread','unsubscribeFromThread'
		);
		
		$action = isset($_GET['action']) ? $_GET['action'] : '';
		if (isset($_GET['view_log'])) { $this->current_log = intval($_GET['view_log']); $action = "expandShortUrl"; }
		if ($log_id == 0){
			if (!in_array($action,$overviewActions)) $action = 'viewLogs';
		} else {
			if (!in_array($action,$logActions)) $action = 'viewLog';
		}
		return call_user_func(array($this,$action));
	}

	/*******************************************************************************************
		 Utility functions                                                       
		 **************************************************************************************/
	
	function initializeCalendar() {
		$this->calendar_instance = new calendar_basic(); 
		call_user_func($this->prepare_classinstance, $this->calendar_instance, $this->calendar);
		$this->calendar_instance->initialize_base();
	}
	
	function initializeImageArchive() {
		$this->iarchive_instance = new imagearchive(); 
		call_user_func($this->prepare_classinstance, $this->iarchive_instance, $this->imagearchive);
	}
	
	function findImageDir(){
		
		$res = $this->query("SELECT fullslug FROM $this->table_imagedirs WHERE id='$this->lead_image_dir'");
		$row = $res->fetch_assoc();
		$fullslug = $row['fullslug'];
		
		$f = explode("/",$_SERVER['SCRIPT_FILENAME']);
		array_pop($f);
		$image_dir = rtrim(implode("/",$f),"/").$this->pathToImages;
		$image_dir .= $fullslug;
		
		$virtual_image_dir = $this->pathToImages;
		$virtual_image_dir .= $fullslug;
		
		return array('virtual' => $virtual_image_dir, 'real' => $image_dir);
		
	}
	
	function getImageFilename($id, $extension){
		if (!is_numeric($id)) $this->fatalError("image id not int!");
		if ($id == -1) return "";
		return 'image'.$id.'_thumb490.'.$extension;
	}
	
	function makeDatestamp($startdate,$enddate) {
		$dts = getdate($startdate);
		$dte = getdate($enddate);
		if ($dts['mday'] == $dte['mday'] && $dts['mon'] == $dte['mon'] && $dts['year'] == $dte['year']) {
			$datestamp = $dts['mday'].". ".$this->months[$dts['mon']-1];
		} else if ($dts['mon'] == $dte['mon']) {
			$datestamp = $dts['mday'].". - ".$dte['mday'].". ".$this->months[$dts['mon']-1];			
		} else {
			$datestamp = $dts['mday'].". ".$this->months[$dts['mon']-1]." - ".$dte['mday'].". ".$this->months[$dte['mon']-1];
		}
		if ($dts['year'] != date("Y",time())) $datestamp .= " ".$dts['year'];
		return $datestamp;
	}
	
	/* 
		Function: publishLog
		Used by the <saveLog> function
	*/
	function publishLog() {
		$id = $this->current_log;		
		$res = $this->query("SELECT event_id FROM $this->table_log WHERE id=$id");
		$row = $res->fetch_assoc();
		$cal_id = explode(",",$row['event_id']);
		$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
		$topic = $calEvent['caption'];
		$year = $calEvent['year'];
		$slug = $calEvent['slug'];
		$url = $this->generateCoolURL("/$year/$slug");
		$this->addToActivityLog("publiserte logg fra <a href=\"$url\">$topic</a>.",false,"major");
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

	/*******************************************************************************************
		 OVERVIEW ACTIONS                                                              
		 **************************************************************************************/
	
	function expandShortUrl() {
		$log_id = intval($this->current_log);
		$res = $this->query("SELECT event_id FROM $this->table_log WHERE id=$log_id");
		if ($res->num_rows == 0) $this->fatalError("Loggen finnes ikke.");
		$row = $res->fetch_assoc();
		$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
		$year = $calEvent['year'];
		$slug = $calEvent['slug'];	
		$this->redirect($this->generateCoolURL("/$year/$slug"));
	}
	
	function viewLogs() {
		
		$output = "";
		
		$cal = $this->calendar_instance->def_calendar;
		
		$r1a = array(); $r2a = array();
		$r1a[]  = "%addarticleurl%";		$r2a[]  = $this->generateURL('action=addLog');
		$r1a[]  = "%helpurl%";				$r2a[]  = "/hjelp/skrive-logg/";
		if ($this->allow_addlog) {
			$r1a[] = "%addarticle%";			$r2a[] = $this->label_addarticle;
			$r1a[] = "%writehelp%";				$r2a[] = $this->label_writehelp;

			$output .=  str_replace($r1a, $r2a, $this->template_leadlistheader);

		} else {
			$r1a[] = "%addarticle%";			$r2a[] = "";		
			$r1a[] = "%writehelp%";				$r2a[] = "";
		}
		
		$i = $this->findImageDir();
		$realDir = $i['real'];
		$virtDir = $i['virtual'];
		
		$ti = $this->table_images;
		$tl = $this->table_log;
		$tc = $this->table_comments;
		
		if ($this->page_no > 1) 
			$this->document_title = 'Side '.$this->page_no;

		
		$res = $this->query("SELECT
				$tl.id, $tl.authors, $tl.lead, $tl.body, $tl.lead_img,
				$tl.event_id, $tl.published,
				COUNT($tc.id) as commentcount
			FROM $tl
			LEFT JOIN $tc ON $tl.id=$tc.parent_id AND $tc.page_id=$this->page_id
			WHERE $tl.log_page = $this->page_id
			GROUP BY $tl.id ORDER BY $tl.created DESC
			LIMIT ".(($this->page_no-1)*$this->leads_per_page).",$this->leads_per_page"
		);
		
		$divclass = "first_log";

		$ia = $this->iarchive_instance;
		
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$lead_image = stripslashes($row['lead_img']);
			$image = "";
			$commentcount = intval($row['commentcount']);
			
			$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
			$year = $calEvent['year'];
			$slug = $calEvent['slug'];

			$topic = $calEvent['caption'];
			$datestamp = $this->makeDatestamp($calEvent['startdate'],$calEvent['enddate']);
			
			// Crop datestamp if title is very long..
			if (strlen($topic) > 30) $datestamp = "";

			if (!empty($lead_image)){

				$caption = "Ukjent";
				$image = sprintf('
					<div style="padding:0px 0px 0px 20px;">
						<div>
							<img src="%s" alt="%s" class="ingressbilde" />
					</div></div>
					',$lead_image,$caption);

				$image = '
					<div class="alpha-shadow"><div class="inner_div">
						<img src="'.$lead_image.'" alt="'.$caption.'" />
					</div></div>
				';
					
			}

			$albumId = $ia->getAlbumIdFromEventId($calEvent['id']);
			if ($albumId != 0) {
				$image = (($divclass == 'first_log') ?
					$this->iarchive_instance->getRepresentativeArchiveImage($albumId,'large',true) :
					$this->iarchive_instance->getRepresentativeArchiveImage($albumId,'small',true)
				);
			}			
			
			$authors = explode(",",$row['authors']);
			$allow_edit = (in_array($this->login_identifier, $authors) || $this->allow_editotherslogs);
			$allow_delete = (($this->allow_deleteownlogs && in_array($this->login_identifier, $authors)) || $this->allow_deleteotherslogs);
			
			for ($i = 0; $i < count($authors); $i++)
				$authors[$i] = call_user_func($this->make_memberlink,$authors[$i]);
			$lastauthor = array_pop($authors);
			if (count($authors) > 0) 
				$txt_authors = implode(", ",$authors)." og ".$lastauthor;
			else
				$txt_authors = $lastauthor;

			$use_opts = false;
			
			$url_readmore = $this->generateCoolURL("/$year/$slug");
			
			$lead = trim(stripslashes($row['lead']));
			$body = trim(stripslashes($row['body']));
			
			if (empty($lead)) {
				$lead = $body;
				$link_readmore = '';
			} else if (empty($body)) {
				$lead = parse_bbcode($lead);
				$link_readmore = '';
			} else {
				$lead = parse_bbcode($lead);
				$link_readmore = '<a href="'.$url_readmore.'" class="readmore">'.$this->label_readmore.'</a>';
			}			
			
			$link_editlog = '';			
			$link_deletelog = '';
			
			if ((!empty($link_readmore)) || (!empty($link_editlog))  || (!empty($link_deletelog))) {
				$use_opts = true;
			}
			
			if ($row['published'] == '1') {
				$notpublishedyet = "";
			} else {
				$notpublishedyet = "\n        <div style='background: #ffffbb; margin: 5px; padding: 3px;'>Denne loggen er ikke publisert enda.</div>";
			}
			
			$cclass = ($commentcount > 1) ? 'comments' : 'comment';
			if ($commentcount > 1) $comments_str = $commentcount.' kommentarer';
			else if ($commentcount == 1) $comments_str = '1 kommentar';
			else $comments_str = 'Vil du kommentere?';
			
			$options = '
				<p class="footerLinks hidefromprint">
					<a href="'.$url_readmore.'#respond" class="'.$cclass.'">'.$comments_str.'</a>
					'.$link_readmore.' '.$link_editlog.' '.$link_deletelog.'
				</p>
			';
			if ($allow_edit || ($row['published'] == '1')) {
			
				$r1a = array(); $r2a = array();
				$r1a[] = "%addarticle%";		$r2a[] = $this->label_addarticle;
				$r1a[] = "%topic%";				$r2a[] = $topic;
				$r1a[] = "%lead%";				$r2a[] = $lead;
				$r1a[] = "%url%";				$r2a[] = $url_readmore;
				$r1a[] = "%readmore%";			$r2a[] = $link_readmore;
				$r1a[] = "%editlog%";			$r2a[] = $link_editlog;
				$r1a[] = "%deletelog%";			$r2a[] = $link_deletelog;
				$r1a[] = "%divclass%";			$r2a[] = $divclass;
				$r1a[] = "%header%";			$r2a[] = $datestamp;
				$r1a[] = "%image%";				$r2a[] = $image;
				$r1a[] = "%notpublishedyet%";	$r2a[] = $notpublishedyet;
				$r1a[] = "%authors%";			$r2a[] = $txt_authors;
				$r1a[] = "%options%";			$r2a[] = $options;
	
				$output .= str_replace($r1a, $r2a, $this->template_leadlistitem);		
				$divclass = "other_log";
			
			}
			
		}
		
		$output .= $this->template_leadlistfooter;
		
		$res = $this->query("SELECT COUNT(id) FROM $this->table_log WHERE calendar_id = $cal");
		$count = $res->fetch_array(); 
		$this->item_count = $count[0];
		$cp = $this->page_no;
		$tp = ceil($this->item_count/$this->leads_per_page);
		$xofy = str_replace(array("%x%","%y%"),Array($cp,$tp),$this->label_pagexofy);
		$lp = ($cp == 1)   ? $this->label_newer : '<a href="'.$this->generateURL('page='.($cp-1)).'">'.$this->label_newer.'</a>';
		$np = ($cp == $tp) ? $this->label_older   : '<a href="'.$this->generateURL('page='.($cp+1)).'">'.$this->label_older.'</a>';
		$output .= "<table width='100%'><tr><td>$lp</td><td><p style='text-align:center;'>$xofy</p></td><td><p style='text-align:right'>$np</p></td></tr></table>\n\n";
		
		return $output;
	}
	
	function addLog() {
	
		if (!$this->allow_addlog) return $this->permissionDenied();
		
		$defaultEvent = 0;
		if (isset($_GET['event_id'])) $defaultEvent = intval($_GET['event_id']);

		$calendar_list = "<option value='0' disabled='disabled'>Velg hendelse</option>
			".$this->calendar_instance->makeEventsDropDown(array(
				'eventsWithoutLogOnly' => true,
				'numEvents' => $this->cal_events_limit,
				'pastEventsOnly' => true,
				'defaultEvent' => $defaultEvent
			));	
		$r1a = array(); $r2a = array();
		$r1a[1]  = "%addarticle%";			$r2a[1]  = $this->label_addarticle;
		$r1a[2]  = "%posturl%";				$r2a[2]  = $this->generateURL('action=addLogDo');
		$r1a[3]  = "%calendar_list%";		$r2a[3]  = $calendar_list;
		$r1a[4]  = "%submit%";				$r2a[4]  = $this->label_addlog;

		return str_replace($r1a, $r2a, $this->template_addlogform);
	
	}
	
	function addLogDo() {
	
		if (!$this->allow_addlog) return $this->permissionDenied();
		
		$authors = $this->login_identifier;
		$timestamp = time();
		$cal_id = $this->calendar_instance->def_calendar;
		$page_id = intval($this->page_id);
		$event_id = intval($_POST['event_id']);

		if ($event_id <= 0) {
			$this->fatalError("Du må velge en hendelse");
		}	
		
		$res = $this->query("SELECT id,event_id FROM $this->table_log 
			WHERE calendar_id=$cal_id AND event_id=$event_id");
		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
			$year = $calEvent['year'];
			$slug = $calEvent['slug'];
			$this->redirect($this->generateCoolURL("/$year/$slug"),"Denne loggen finnes allerede!",'warning');
		}

	
		$calEvent = $this->calendar_instance->getEventDetails($event_id);
		$year = $calEvent['year'];
		$slug = $calEvent['slug'];		
					
		$this->query("INSERT INTO $this->table_log 
			(authors,created,log_page,calendar_id,event_id)
			VALUES (\"$authors\",$timestamp,$page_id,$cal_id,$event_id)
		");
		
		$this->current_log = $this->insert_id();		
        $this->subscribeToThread(false);
		
		$this->redirect($this->generateCoolURL("/$year/$slug","action=editLog"));
	
	}

	/*******************************************************************************************
		 LOG ACTIONS                                                               
		 **************************************************************************************/
	
	function viewLog() {
		$id = $this->current_log;
		if (isset($_GET['view_log'])) $id = intval($_GET['view_log']);
		if ($id <= 0) return $this->notSoFatalError("Siden finnes ikke.");

		$output = "";
	
		if (!$this->allow_viewbodies) { $this->permissionDenied(); return; }		
		if (!is_numeric($id)) { $this->permissionDenied(); return; }
		
		$res = $this->query("SELECT id,authors,published,lead,body,event_id,
			lead_image,images_count,images_location,created
			FROM $this->table_log WHERE id = '$id'"
		);
		
		if ($res->num_rows != 1) { $this->notSoFatalError($this->label_articledoesntexist); return; }
		$row = $res->fetch_assoc();
		
		$eventId = intval($row['event_id']);
		
		$authors = explode(",",$row['authors']);
		$lead = parse_bbcode(stripslashes($row['lead']));
		$body = stripslashes($row['body']);
		$created = intval($row['created']);
		
		$images_count = $row['images_count'];
		$images_location = $row['images_location'];
		
		if (isset($this->calEvent)) $calEvent = $this->calEvent;
		else $calEvent = $this->calendar_instance->getEventDetails($eventId);

		$year = $calEvent['year'];
		$slug = $calEvent['slug'];

		$topic = $calEvent['caption'];
		
		$dt_start = date("j.n.y",$calEvent['startdate']);
		$dt_end = date("j.n.y",$calEvent['enddate']);
		$datestamp = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
		
		$ia = $this->iarchive_instance;
		$albumId = $ia->getAlbumIdFromEventId($eventId);
		$images = "";
		if ($albumId != 0) {
			$albumCount = $ia->getAlbumPhotoCount($albumId);
			$albumUrl = $ia->getAlbumURL($albumId);
			$albumPhotos = $ia->getAlbumPhotos($albumId, $images_count, true, 140);

			$images = "
				<div style='text-align:center;'>
				$albumPhotos
				</div>
				<div>
					<a href=\"$albumUrl\" class=\"icn smallicn\" style=\"font-size:80%;background-image:url(/images/icns/bullet_go.png);\">Vis alle $albumCount bilder i bildearkivet</a>
				</div>
			";
		}

		$calendar_link = (empty($row['event_id'])) ? "" :
			$this->calendar_instance->getLinkToEvent($row['event_id'], $this->label_showincalendar);

		$allow_edit = (in_array($this->login_identifier, $authors) || $this->allow_editotherslogs);
		if ($allow_edit) {
			$output .= $this->make_editlink($this->generateURL('action=editLog'), "Rediger logg");
		}

		if ($row['published'] == '1') {
			$output .= "";
		} else {
			$output .= "\n        <div style='background: #ffffbb; margin: 5px; padding: 10px; font-weight:bold;'>NB! Denne loggen er ikke publisert enda.</div>";
		}

		
		$w = count($authors) * 90;
		// background:#EDF0ED;
		$author_list = "
			<div style='float:right; width: ".$w."px;'>
			<div class='authors'><em>Skrevet av</em></div>";
		foreach ($authors as $a) {			
			$author_img = call_user_func($this->lookup_memberimage, $a);
			$author = call_user_func($this->lookup_member, $a);
			$author_uri = call_user_func($this->make_memberlink, $a, $author->firstname);
			$author_list .= '
			<div class="author">
					<div class="alpha-shadow noframe nomargin" style="margin:0px 2px 0px 10px !important;"><div class="inner_div">
						<img src="'.$author_img.'" alt="Forfatterbilde" />
					</div></div>
					<div style="padding-bottom:5px;">'.$author_uri.'</div>
			</div>
			';
		}	
		$author_list .= "
			</div>
		";
		
		$tl = $this->table_log;
		
		
		// Finn forrige logg
		$res = $this->query("SELECT $tl.id, $tl.event_id FROM $tl
			WHERE $tl.log_page = ".$this->page_id." AND $tl.published=1 AND $tl.created < $created
			ORDER BY $tl.created DESC LIMIT 1"
		);
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
			$year = $calEvent['year'];
			$slug = $calEvent['slug'];
			$forrigeArr = '<a href="'.$this->generateCoolURL("/$year/$slug").'" title="'.$calEvent['caption'].'" class="icn" style="background-image:url(/images/icns/arrow_left.png);">Forrige logg</a>';
		} else {
			$forrigeArr = '<span class="icn" style="color:#999; background-image:url(/images/icns/arrow_left.png);">Forrige logg</span>';		
		}
		
		// Finn neste logg
		$res = $this->query("SELECT $tl.id, $tl.event_id FROM $tl
			WHERE $tl.log_page = ".$this->page_id." AND $tl.published=1 AND $tl.created > $created
			ORDER BY $tl.created LIMIT 1"
		);
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
			$year = $calEvent['year'];
			$slug = $calEvent['slug'];
			$nesteArr = '<a href="'.$this->generateCoolURL("/$year/$slug").'" title="'.$calEvent['caption'].'" class="right_icn" style="background-image:url(/images/icns/arrow_right.png);">Neste logg</a>';
		} else {
			$nesteArr = '<span class="right_icn" style="color:#999; background-image:url(/images/icns/arrow_right.png);">Neste logg</span>';		
		}
		
		call_user_func(
			$this->add_to_breadcrumb,
			"<a href=\"".$this->generateCoolURL("/$year/$slug")."\">$topic $datestamp</a>"
		);		

		$this->document_title = "$topic $datestamp";
				
		$r1a = array(); $r2a = array();
		$r1a[] = "%editarticle%";			$r2a[] = $this->label_editarticle;
		$r1a[] = "%posturl%";				$r2a[] = $this->generateURL('action=saveLog');
		$r1a[] = "%topic%";					$r2a[] = $topic;
		$r1a[] = "%lead%";					$r2a[] = $lead;
		$r1a[] = "%body%";					$r2a[] = $body;
		$r1a[] = "%submit%";				$r2a[] = $this->label_save;
		$r1a[] = "%bodyeditheight%";		$r2a[] = $this->field_body_height;
		$r1a[] = "%id%";					$r2a[] = $id;
		$r1a[] = "%header%";				$r2a[] = $datestamp;
		$r1a[] = "%authors%";				$r2a[] = $author_list;
		$r1a[] = "%calendar_link%";			$r2a[] = $calendar_link;
		$r1a[] = "%images_location2%";		$r2a[] = (($images_location == '2') ? $images : "");
		$r1a[] = "%images_location3%";		$r2a[] = (($images_location == '3') ? $images : "");
		$r1a[] = "%prev%";		 			$r2a[] = $forrigeArr;
		$r1a[] = "%next%";		 			$r2a[] = $nesteArr;

		if (empty($body)) {
			$outp = str_replace($r1a, $r2a, $this->template_viewarticle_short);
		} else {
			$outp = str_replace($r1a, $r2a, $this->template_viewarticle_long);	
		}
		$output .= $outp;

		$this->comment_desc = 4;
		$output .= $this->printComments($id);
		
		return $output;
	}
	
	function editLog() {
		global $memberdb;
		
		$id = $this->current_log;
		if ($id <= 0) return $this->notSoFatalError("Siden finnes ikke.");

		/*** BEGIN INGRESSBILDE ***/
			/*
		$this->initializeImagesInstance();
		if (isset($_GET['ajax_image_action'])) {
			$this->imginstance->ajaxImageAction(
				$this->lead_image_dir,
				$this->images_per_page,
				'lead_image'
			);
			exit();
		}	*/		
			
		/*** END INGRESSBILDE ***/
		
		if (!$this->allow_addlog) return $this->permissionDenied();		
		if (!is_numeric($id)) return $this->permissionDenied(); 

		$res = $this->query("SELECT id,authors,lead,body,lead_img,event_id,
			published,images_location,images_count
			FROM $this->table_log WHERE id=$id"
		);
		
		if ($res->num_rows != 1) return $this->notSoFatalError($this->label_articledoesntexist); 
		$row = $res->fetch_assoc();
		
		$authors = explode(",",$row['authors']);
		if (!in_array($this->login_identifier, $authors) && !$this->allow_editotherslogs) {
			 return $this->permissionDenied(); 
		}
		
		if (isset($this->calEvent)) $calEvent = $this->calEvent;
		else $calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
		$year = $calEvent['year'];
		$slug = $calEvent['slug'];		
		$datestamp = $this->makeDatestamp($calEvent['startdate'],$calEvent['enddate']);

		$topic = $calEvent['caption'];
		$lead = stripslashes($row['lead']);
		$body = stripslashes($row['body']);
		$publishstatus = ($row['published'] == '1') ? " checked='checked'" : "";
		
		$calendar_list = "<option value='0'>Ikke knytt til noen aktivitet</option>
			".$this->calendar_instance->makeEventsDropDown(array(
				'eventsWithoutLogOnly' => true,
				'numEvents' => $this->cal_events_limit,
				'pastEventsOnly' => true,
				'defaultEvent' => $row['event_id']
			));
				
		$l = $row['images_location'];
		$d1 = ($l == '1') ? " selected='selected'" : "";
		$d2 = ($l == '2') ? " selected='selected'" : "";
		$d3 = ($l == '3') ? " selected='selected'" : "";
		$list_imglocations = "
							<option value='1'$d1>(Ikke vis bilder)</option>
							<option value='2'$d2>Under loggen</option>
							<option value='3'$d3>Over loggen, men under ingressen</option>
		";
		
		$images_count = $row['images_count'];
		
		$author_list = $this->makeEditableAuthorList($authors);
		
		/*
		$_SESSION['ajax_imageselector_defaultimage'] = $row['lead_image'];
		$_SESSION['ajax_imageselector_imagesperpage'] = $this->images_per_page;
		list($ingressbildeDialog,$ingressbilde) = $this->imginstance->makeAjaxImageSelector(
			$this->lead_image_dir,
			'lead_image'
		);*/
	
		
		$default_image = stripslashes($row['lead_img']);
		$pathToUserFiles = '/'.$this->userFilesDir;
		$pathToThumbs100 = $pathToUserFiles.'_thumbs140/';
		$ingressbilde = '
			<script type="text/javascript">
				var pathToUserFiles = "'.$pathToUserFiles.'";
				var pathToThumbs = "'.$pathToThumbs100.'";
			</script>
			<div style="width:150px;text-align:center;">
				<input type="hidden" name="lead_image" id="lead_image" value="'.$default_image.'" />
				<a href="#" onclick="BrowseServer(); return false;" style="display:block;width:150px;height:150px;border:1px dashed #666;">
					<span id="ingressbildespan">
						'.(empty($default_image) ? 
							'<strong>Velg bilde</strong> ' :
							'<img src="'.$default_image.'" border="0" alt="Velg bilde" style="margin:5px;" />'
						).'
					</span>
				</a>
			</div>
		';
				
		$namesArray = "";
		foreach ($this->getActiveMembersList(array('SortBy' => 'FullName')) as $user_id => $u) {
			$namesArray .= "<option value=\"$user_id\">".$u['FullName']."</option>\n";
		}
		
		$this->setDefaultCKEditorOptions();
		
		$r1a = array(); $r2a = array();
		$r1a[] = "%editarticle%";				$r2a[] = $topic." ".$datestamp; #$this->label_editarticle;
		$r1a[] = "%posturl%";					$r2a[] = $this->generateURL('action=saveLog');
		$r1a[] = "%topic%";						$r2a[] = $topic;
		$r1a[] = "%lead%";						$r2a[] = $lead;
		$r1a[] = "%body%";						$r2a[] = $body;
		$r1a[] = "%publiser%";					$r2a[] = "Lagre og publisér";
		$r1a[] = "%kladd%";						$r2a[] = "Lagre som kladd";
		$r1a[] = "%fckbasepath%";				$r2a[] = $this->pathToFCKeditor;
		$r1a[] = "%bodyeditheight%";			$r2a[] = $this->field_body_height;
		$r1a[] = "%id%";						$r2a[] = $id;
		$r1a[] = "%calendar_list%";				$r2a[] = $calendar_list;
		$r1a[] = "%author_list%";				$r2a[] = $author_list;
		$r1a[] = "%publishstatus%";				$r2a[] = $publishstatus;
		$r1a[] = "%imagearchive_locations%";	$r2a[] = $list_imglocations;
		$r1a[] = "%imagearchive_itemcount%";	$r2a[] = $images_count;
		$r1a[] = "%ingressbilde%";				$r2a[] = $ingressbilde;
		$r1a[] = "%image_dir%";					$r2a[] = $this->image_dir;
		$r1a[] = "%imagestartuppath%";			$r2a[] = $this->imagestartuppath;
		$r1a[] = "%ckfinder_uri%";				$r2a[]  = LIB_CKFINDER_URI;
		$r1a[] = "%ckeditor_uri%";				$r2a[]  = LIB_CKEDITOR_URI;
		$r1a[] = "%urlGetAlbumId%";				$r2a[]  = $this->generateURL('action=ajaxGetPhotoAlbumForEventId');
		$r1a[] = "%namesArray%";				$r2a[]  = $namesArray;
		$r1a[] = "%updateAuthorListUrl%";		$r2a[]  = $this->generateURL('action=ajaxGetAuthorList');
		
		$template = file_get_contents($this->template_dir.$this->template_editarticleform);

		call_user_func(
			$this->add_to_breadcrumb,
			"<a href=\"".$this->generateCoolURL("/$year/$slug")."\">$topic $datestamp</a>"
		);		

		//$ingressbildeDialog.
		return str_replace($r1a, $r2a, $template);
	}
	
	function saveLog() {
			
		$id = $this->current_log;
			
		if (!$this->allow_addlog) { $this->permissionDenied(); return; }		
		if ($id <= 0) { $this->permissionDenied(); return; }
		
		$res = $this->query("SELECT authors,images_count,images_location,published,lead_img
			FROM $this->table_log WHERE id = $id");
		
		if ($res->num_rows != 1) { $this->notSoFatalError($this->label_articledoesntexist); return; }
		$row = $res->fetch_assoc();
		
		$authors = explode(",",$row['authors']);
		if (!in_array($this->login_identifier, $authors) && !$this->allow_editotherslogs) {
			 $this->permissionDenied(); return; 
		}
		$alreadypublished = $row['published'];
		
		if (!isset($_POST['log_lead'])) $lead = "";
		else $lead = addslashes($_POST['log_lead']);
		
		$body = addslashes($_POST['log_body']);
		$event_id = intval($_POST['event_id']);

		$authors = addslashes($_POST['authors']);

		$calEvent = $this->calendar_instance->getEventDetails($event_id);
		$year = $calEvent['year'];
		$slug = $calEvent['slug'];
				
		$timestamp = time();
		$published = (isset($_POST['kladd'])) ? 0 : 1;
		
		if (!isset($_POST['imagearchive_count'])) $images_count = 3;
		else $images_count = intval($_POST['imagearchive_count']);
		
		if (!isset($_POST['imagearchive_location'])) $images_location = 1;
		$images_location = intval($_POST['imagearchive_location']);		
		
		if (!isset($_POST['lead_image'])) $lead_image = '';
		else $lead_image = addslashes($_POST['lead_image']);
		if (empty($lead_image)) $lead_image = $row['lead_img'];
		else {
			if (ROOT_DIR != '' && !empty($lead_image)){
				$lead_image = substr($lead_image,strlen(ROOT_DIR));
			}
		}

		$this->query("UPDATE $this->table_log SET
				lead = \"$lead\",
				body = \"$body\",
				event_id = $event_id,
				lastmodified = $timestamp,
				published = $published,
				images_count = $images_count,
				images_location = $images_location,
				lead_img = \"$lead_image\",
				authors = \"$authors\"
			WHERE id = $id"
		);
		
		$url = $this->generateCoolURL("/$year/$slug");
		
		if ($alreadypublished == '0' && $published == '1') {
			$this->publishLog();
			$this->redirect($url,"Loggen ble lagret og publisert");
		} else {
			$this->redirect($url,"Loggen ble lagret");
		}
	}

	function deleteLog(){
		$id = $this->current_log;
		if (!is_numeric($id)){ $this->fatalError("incorrect input!"); }
		$res = $this->query("SELECT event_id,authors FROM $this->table_log WHERE id=$id");
		if ($res->num_rows != 1) fatalError("can't delete non-existing entry");
		$row = $res->fetch_assoc();

		$authors = explode(",",$row['authors']);
		$allow_delete = (($this->allow_deleteownlogs && in_array($this->login_identifier, $authors)) || $this->allow_deleteotherslogs);
		
		$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
		$topic = $calEvent['caption'];

		if ($allow_delete){
			$this->query("DELETE FROM $this->table_log WHERE id=$id");
		}
		$this->addToActivityLog("slettet loggen fra \"$topic\"");	
		
		$this->redirect($this->generateCoolURL('/'), "Loggen ble slettet");
	}	
	
	function deleteLogDo(){
		$id = $this->current_log;
		if (!is_numeric($id)){ $this->fatalError("incorrect input!"); }
		$res = $this->query("SELECT event_id,authors FROM $this->table_log WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("Loggen eksisterer ikke!");
		$row = $res->fetch_assoc();
		$authors = explode(",",$row['authors']);
		$allow_delete = (($this->allow_deleteownlogs && in_array($this->login_identifier, $authors)) || $this->allow_deleteotherslogs);


		if ($allow_delete) {

			$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
			$topic = $calEvent['caption'];

			return '
				<h2>Bekreft sletting</h2>
				<p>Er du sikker på at du ønsker å slette loggen fra '.$topic.'?</p>
				<form method="post" action="'.$this->generateURL('action=deleteLogDo').'">
					<input type="submit" value="     Ja      " /> 
					<input type="button" value="     Nei      " onclick=\'window.location="'.$_SERVER["HTTP_REFERER"].'"\' />
				</form>
			';
		} else {
			return $this->permissionDenied();
		}	
	}
	
	function ajaxGetAuthorList() {
		$authors = explode(",",$_POST['authors']);
		print $this->makeAuthorList($authors);
		exit();	
	}
	
	function ajaxGetPhotoAlbumForEventId() {
		$event_id = intval($_POST['event_id']);
		$ia = $this->iarchive_instance;
		$albumId = $ia->getAlbumIdFromEventId($event_id);
		if ($albumId == 0) {
			header("Content-Type: application/json; charset=utf-8"); 
			print json_encode(array(
				'error' => 0,
				'hasAlbum' => false
			));
		} else {
			header("Content-Type: application/json; charset=utf-8"); 
			print json_encode(array(
				'error' => 0,
				'hasAlbum' => true,
				'photo_count' => $ia->getAlbumPhotoCount($albumId),
				'photos' => $ia->getAlbumPhotos($albumId,6,false,75,false),
			));
		}
		exit();
	}

	/*******************************************************************************************
		 Public functions for use by other classes                                                               
		 **************************************************************************************/
	
	/*
		Function: getLogIdFromEventId
		Given a known calendar event id, this function will return the corresponding
		log id. If no corresponding log exists, the function will return zero (0).
	*/
	public function getLogIdFromEventId($event_id) {
		$event_id = intval($event_id);
		$res = $this->query("SELECT id FROM $this->table_log WHERE event_id=$event_id");
		if ($res->num_rows == 0) return 0;
		$row = $res->fetch_assoc();
		return intval($row['id']);
	}
	
	public function getExcerpt($log_id) {
		$log_id = intval($log_id);
		$res = $this->query("SELECT id,event_id,lead,body FROM $this->table_log WHERE id=$log_id");
		if ($res->num_rows == 0) return "Loggen ble ikke funnet";
		$row = $res->fetch_assoc();
		$lead = trim(stripslashes($row['lead']));
		$body = trim(stripslashes($row['body']));
		$url = $this->getLinkToLog($log_id);
		if (empty($lead)) return "<a href=\"$url\" class=\"icn\" style='background-image:url(/images/icns/script.png);'>Les logg fra dette arrangementet</a>";
		else return $lead."<div>...<br /><a href=\"$url\" class=\"icn\" style='background-image:url(/images/icns/arrow_right.png);'>Les hele loggen</a></div>";
	}
		
	public function getLinkToNewLogForm($event_id) {
		return "/".$this->fullslug."?action=addLog&amp;event_id=$event_id";	
	}
	
	public function getLinkToLog($log_id) {
		return "/".$this->fullslug."?view_log=$log_id";
	}

	public function getLastLogs($count) {
		$cal = $this->calendar_instance->def_calendar;
		$res = $this->query("SELECT id,authors,lead,lead_img,event_id
			FROM $this->table_log
			WHERE calendar_id = $cal AND published='1'
			ORDER BY created DESC LIMIT $count"
		);
		$logs = array();
		while ($row = $res->fetch_assoc()) {
			$row['cal_event'] = $this->calendar_instance->getEventDetails($row['event_id']);
			$year = $row['cal_event']['year'];
			$slug = $row['cal_event']['slug'];
			$row['uri'] = $this->generateCoolURL("/$year/$slug");
			$logs[] = $row;
		}
		return $logs;		
	}
	
	public function getLastLogsGlobal($count) {
		$cal = $this->calendar_instance->def_calendar;
	    $tl = $this->table_log;
	    $tc = $this->table_calendars;
        $res = $this->query("SELECT $tl.id, $tl.authors, $tl.lead, $tl.lead_img,
                $tl.lastmodified, $tl.calendar_id, $tl.event_id,
                $tl.log_page, $tc.caption, $tc.flag
			FROM $tl,$tc
			WHERE $tl.published='1' AND $tl.calendar_id = $tc.id
			ORDER BY $tl.created DESC LIMIT $count
			"
		);
		$logs = array();
		while ($row = $res->fetch_assoc()) {
			$row['cal_event'] = $this->calendar_instance->getEventDetails($row['event_id']);
			$year = $row['cal_event']['year'];
			$slug = $row['cal_event']['slug'];
			$row['uri'] = $this->getUrlToPage($row['log_page'])."/$year/$slug";
			$logs[] = $row;
		}
		return $logs;		
	}	

	/*******************************************************************************************
		 Sitemap functions                                                               
		 **************************************************************************************/

	function sitemapListAllPages(){
		$urls = array();
		
		// List articles
		$res = $this->query("SELECT id,lastmodified FROM $this->table_log WHERE log_page=$this->page_id");
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$calEvent = $this->calendar_instance->getEventDetails($id);
			$year = $calEvent['year'];
			$slug = $calEvent['slug'];

			$urls[] = array(
				'loc' => $this->generateCoolURL("/$year/$slug"), 
				'lastmod' => $row['lastmodified'],
				'changefreq' => 'monthly'
			);
		}
		
		return $urls;
	}

	function getLinkToEntry($id) {
		$this->initializeCalendar();	
		$id = intval($id);
		$res = $this->query("SELECT event_id FROM $this->table_log WHERE id=$id");
		$row = $res->fetch_assoc();

		$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);
		$year = $calEvent['year'];
		$slug = $calEvent['slug'];

		return $this->generateCoolURL("/$year/$slug");
	}
	
	/** COMMENTS **/
	
	function subscribeToThread($post_id = 0, $redirect = true) {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = $this->current_log;
	    @parent::subscribeToThread($post_id, $redirect);
	}

	function unsubscribeFromThread($post_id = 0, $redirect = true) {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = $this->current_log;
	    @parent::unsubscribeFromThread($post_id, $redirect);
	}

	function saveComment($post_id = 0, $context = '') {
	    $post_id = intval($post_id);
	    if ($post_id == 0) $post_id = intval($this->current_log);
	    if ($post_id <= 0) { $this->fatalError("incorrect input!"); }
		
		$tl = $this->table_log;
		$res = $this->query("SELECT event_id FROM $tl WHERE id=$post_id");
		if ($res->num_rows != 1) $this->fatalError("Loggen ble ikke funnet!");
		$row = $res->fetch_assoc();
		$calEvent = $this->calendar_instance->getEventDetails($row['event_id']);

		$row = $res->fetch_assoc();
		$context = 'loggen «'.$calEvent['caption'].'»';
	    @parent::saveComment($post_id, $context);
	}
	
}


?>
