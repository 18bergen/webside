<?

require_once('comments.php');

class calendar_basic extends comments {
		
	var $use_login_identifier = false;
	var $allow_addentries = false;
	var $allow_editownentries = false;
	var $allow_editothersentries = false;
	var $allow_deleteownentries = false;
	var $allow_deleteothersentries = false;
	var $allow_editsettings = false;
	var $current_group = 0;
	var $group_filter = false;

	/* Public database settings (for use by other classes) */	
	public $table_calendar = "";								// Required table
	public $table_calendar_field_startdate = "dt_start";			// Required field
	public $table_calendar_field_enddate = "dt_end";				// Required field
	public $table_calendar_field_caption = "caption";				// Required field
	
	var $table_logentries = 'blog';

	var $table_calendar_field_id = "id";						// Required field
	var $table_calendar_field_creator = "creator";				// Required field
	var $table_calendar_field_lead = "lead";					// Required field
	var $table_calendar_field_location = "location_id";			// Optional field
	var $table_calendar_field_calendar = "calendar_id";			// Optional field
	var $table_calendar_field_body = "body";					// Optional field
	var $table_calendar_field_category = "category";			// Optional field
	var $table_calendar_field_responsible = "responsible";		// Optional field
	var $table_calendar_field_log = "log";						// Optional field
	var $table_calendar_field_images = "images";				// Optional field
	var $table_calendar_field_cancelled = "cancelled";
	var $table_calendar_field_lastmodified = "lastmodified";	
	var $table_calendar_field_slug = "slug";	
	
	var $calview_template2 = "%noentries%	%entries%";
	
	var $table_locations = "";									// Optional table
	var $table_locations_field_id = "id";						// Required field if table_locations exists
	var $table_locations_field_caption = "caption";				// Required field if table_locations exists

	var $table_calendars = "";									// Optional table
	var $table_calendars_field_id = "id";						// Required field if table_calendars exists
	var $table_calendars_field_caption = "caption";				// Required field if table_calendars exists
	var $table_calendars_field_color = "color";
	var $use_iarchive = true;
	var $imagearchive;
	var $iarchive_instance;
	
	var $use_enrolment = false;
	var $enrolment;
	var $enrolment_instance;
	
	var $use_log = true;
	var $log;
	var $log_instance;
		
	var $table_memberships = "";			
	var $table_memberships_field_user="bruker";
	var $table_memberships_field_group="gruppe";
	var $table_memberships_field_enddate="til";

	var $imagelookup_function;

	var $subject_caption = "Subject:";
	var $subject_default = "";
	var $slug_default = "";
	
	var $label_choosefromlist = "Velg fra liste:";
	var $label_newentry = "Ny hendelse";
	var $label_editentry = "Endre hendelse";
	var $label_category = "Kategori";
	var $label_subject = "Emne";
	var $label_slug = "Url";
	var $label_location = "Sted";
	var $label_calendar = "Kalender";
	var $label_responsible = "Ansvarlig";
	var $label_goback = "Tilbake";
	var $label_dtstart = "Begynner";
	var $label_dtend = "Slutter";
	var $label_lead = "Inngress";
	var $label_body = "Brødtekst";
	var $label_options = "Valg";
	var $show_lead = true;
	var $show_body = true;
	var $show_options = true;
	
	var $show_category = true;
	var $category_caption = "Category:";
	var $category_values = array();
	var $category_valuesFromDB = true;
	var $table_categories;
	var $category_field_id = "id";
	var $category_field_page = "page";
	var $category_field_caption = "caption";
	var $category_field_defaultname = "defaultname";
	var $category_field_defaulttime = "defaulttime";
	var $category_field_defaultlocation = "defaultlocation";
	var $category_field_defaultresponsible = "defaultorganizer";
	var $category_onChangeFunction;
	var $category_default = -1;		
		
	var $show_location = false;
	var $location_caption = "Location:";
	var $location_default = "";

	var $show_calendar = false;
	var $calendar_default = "";
	
	var $show_responsible = false;
	var $responsible_caption = "Responsible:";
	var $responsible_values = array();
	var $responsible_valuesFromDB = true;
	var $table_responsible;
	var $responsible_field_id = "id";
	var $responsible_field_caption = "caption";
	var $responsible_field_page = "page";
	var $responsible_default = -1;

	var $invite_caption = "Invite:";
	var $invite_values = array();
	var $invite_valuesFromDB = false;
	var $invite_tablename;
	var $invite_field_id;
	var $invite_field_caption;

	var $show_notes = true;
	var $notes_default;
	
	var $prefs; // callback
	
	var $startdatetime_default;
	var $enddatetime_default;
	var $lead_default = "";
	var $body_default = "";
	
	var $noentries_template = "<em>Ingen hendelser</em>";
	
	var $currentYear;
	
	var $noteicon_template = "<img src='images/document.gif' alt='Denne hendelsen har et notat' title='Denne hendelsen har et notat' />";
	var $location_template = "<div class='cal_Item_line2'>%location%</div>";
	var $calview_entry_template = '

		<div class="calendar_element" onclick=\'window.location="%detailsurl%"\'>
			
			<table>
				<tr><td valign="top">
					<div style="width:10px;height:12px;background-image(%image_dir%calendar_red.gif);"><!-- --></div>
					<div style="width:10px;height:10px;background:%cal_color%; margin-top:3px;" title="%cal_name%"><!-- --></div>
				</td><td valign="top" style="width: 240px;">
					<em>%longdate%</em> %log_icon% %imagearchive_icon% %enrolment_icon% %private_icon%
					<h3><a href="%detailsurl%">%subject%</a></h3>
					<div style="padding-left:0px;color:#666;font-size:9px;">Ansvarlig: %responsible%</div>
				</td><td valign="top">
					<p>%lead%</p>
				</td></tr>
			</table>
			
		</div>
	';

	var $pagexofy_template = "Side %x% av %y%";
	var $str_prevpage = "Forrige side";
	var $str_nextpage = "Neste side";

	
	function calendar_basic(){
		$d = getdate(time());
		$this->startdatetime_default = mktime(0,1,0,$d['mon'],$d['mday'],$d['year']); 
		$this->enddatetime_default = mktime(0,1,0,$d['mon'],$d['mday'],$d['year']); 
		if (isset($_GET['errs'])) $this->invalidFields = explode("|",$_GET['errs']);
		$this->table_calendar = DBPREFIX."cal_events";
		$this->table_locations = DBPREFIX."cal_locations";
		$this->table_calendars = DBPREFIX."cal_calendars";
		$this->table_categories = DBPREFIX."cal_categories";
		$this->table_responsible = DBPREFIX."cal_responsibles";
		$this->table_logentries = DBPREFIX.'blog';
		$this->table_comments = DBPREFIX.'comments';
	}
	
	function initialize() {
		
		@parent::initialize();

		if ($this->use_log) $this->initializeLog();
		if ($this->use_iarchive) $this->initializeImageArchive();
		if ($this->use_enrolment) $this->initializeEnrolment();		
		if (isset($this->coolUrlSplitted[0]) && is_numeric($this->coolUrlSplitted[0])){
			$this->currentYear = $this->coolUrlSplitted[0];
		} else if (isset($_GET['year']) && is_numeric($_GET['year'])){
			$this->currentYear = $_GET['year'];
		}
		if ((isset($_GET['subscribe'])) && (isset($_GET['confirm']))) {
			header("Content-Type: text/calendar; encoding=utf-8");
		  	header("Content-Disposition: inline; filename=".$this->server_name.".ics");
	   		$this->printVCalendar();
	   		exit;
		}
		if (isset($_GET['kalenderbruker'])) exit;

		if ((isset($_GET['cal_page'])) && (is_numeric($_GET['cal_page']))){
			$this->page_no = ($_GET['cal_page']); 
		} else { 
			$this->page_no = "default"; 
		}
		$res = $this->query("SELECT COUNT(*) FROM $this->table_calendar");
		$count = $res->fetch_array(); 
		$this->entry_count = $count[0];

		if (isset($_GET['group_filter'])) $this->group_filter = true;

	}
	
	function initializeLog() {
		if (isset($this->prepare_classinstance) && isset($this->log) && !empty($this->log)) {
			$this->log_instance = new log(); 
			call_user_func($this->prepare_classinstance, $this->log_instance, $this->log);
		} else {
			$this->use_log = false;
		}
	}
	
	function initializeImageArchive() {
		if (isset($this->prepare_classinstance) && isset($this->imagearchive) && !empty($this->imagearchive)) {
			$this->iarchive_instance = new imagearchive(); 
			call_user_func($this->prepare_classinstance, $this->iarchive_instance, $this->imagearchive);
		} else {
			$this->use_iarchive = false;
		}
	}

	function initializeEnrolment() {
		if (isset($this->prepare_classinstance) && isset($this->enrolment) && !empty($this->enrolment)) {
			$this->enrolment_instance = new enrolments(); 
			call_user_func($this->prepare_classinstance, $this->enrolment_instance, $this->enrolment);
		} else {
			$this->use_enrolment = false;
		}
	}
	
	function toSiffer($t){
		if ($t < 10) $t = "0".$t;
		return $t;
	}
	
	function prepareForRss($str){
		$str = stripslashes($str);
		$str = parse_bbcode($str);
		$str = parse_emoticons($str);
		$str = strip_tags($str);
		$str = html_entity_decode($str,ENT_NOQUOTES,'utf-8');
		$str = str_replace("<","&lt;",$str);
		$str = str_replace(">","&gt;",$str);
		$str = str_replace("\r\n","\n",$str);
		$str = str_replace("\r","\n",$str);
		$str = str_replace("\n","\r\n",$str);
		$str = str_replace("<br />"," ",$str);
		return $str;
	}
	
	function viewCalendar(){
			
		$output = "";

		if ($this->page_no == "default") $this->page_no = 1;
		
		// Make some short vars to make the code more readable...
		$tct = $this->table_calendar;
		$tcct = $this->table_calendars;
		$tlt = $this->table_locations;
		$trt = $this->table_responsible;

		if (!empty($this->currentYear)) {
			$minYear = 2000;
			$maxYear = date("Y",time())+2;
			if ($this->currentYear >= $minYear && $this->currentYear <= $maxYear) {
				$yearStart = mktime(0,0,0,1,1,$this->currentYear);
				$yearEnd = mktime(23,59,59,12,31,$this->currentYear);
			} else {
				$this->notSoFatalError("Terminlister er kun tilgjengelig i årsintervallet $minYear-$maxYear.");
				$this->currentYear = 0;
			}
		}
		
		if ($this->use_log) {
			$table_log = $this->log_instance->table_log;
			$log_id = $this->log_instance->table_log_field_id;
			$log_cal_id = $this->log_instance->table_log_field_eventid;
		}
		if ($this->use_iarchive) {
			$table_iarchive = $this->iarchive_instance->table_dirs;
			$iarchive_id = $this->iarchive_instance->table_dirs_field_id;
			$iarchive_cal_id = $this->iarchive_instance->table_dirs_field_cal_id;
			$iarchive_event_id = $this->iarchive_instance->table_dirs_field_event_id;
		}
		if ($this->use_enrolment) {
			$table_enrolments = $this->enrolment_instance->table_enrolments;
			$table_enrolments_event = $this->enrolment_instance->table_enrolments_field_event;
			$this_cal_page_id = $this->page_id;
		}

		// Spørringen fra helvete...:
		$sporringenFraHelvete = "
			SELECT 
				$tct.id,
				$tct.slug,
				UNIX_TIMESTAMP($tct.dt_start) as start,
				UNIX_TIMESTAMP($tct.dt_end) as end,
				$tct.caption,
				$tct.lead,
				$tct.body,
				$tct.cancelled,
				$tct.private,
				$tct.creator,
				$tct.responsible,
				$tlt.caption as location_name,
				$tcct.id as cal_id,
				$tcct.caption as cal_name,
				$tcct.color as cal_color
				".($this->use_log ? ",$table_log.id as log_id" : "")."
				".($this->use_iarchive ? ",$table_iarchive.id as iarchive_id" : "")."
				".($this->use_enrolment ? ",$table_enrolments.id as enrolment_id" : "")."
			FROM 
				$tct ".($this->use_log ?       " LEFT JOIN $table_log ON ($tct.id = $table_log.calendar_id)" : "")."
				     ".($this->use_iarchive ?  " LEFT JOIN $table_iarchive ON ($table_iarchive.cal_id=$this->page_id AND $tct.id = $table_iarchive.event_id) " : "")."
				     ".($this->use_enrolment ? " LEFT JOIN $table_enrolments ON ($tct.id = $table_enrolments.event_id)" : "")."
				,$tlt, $tcct
			WHERE
				".(empty($this->currentYear) ?
					"$tct.dt_end > NOW() " 
					:
					"(YEAR($tct.dt_start) = \"$this->currentYear\" OR
					  YEAR($tct.dt_end) = \"$this->currentYear\")
					")."
				AND $tct.location_id = $tlt.id
				AND $tct.calendar_id = $tcct.id
				AND $tct.calendar_id IN (".$this->show_calendars.")
			GROUP BY 
				$tct.id
			ORDER BY 
				$tct.dt_start
			LIMIT 
				".(($this->page_no-1)*$this->entries_per_page).",$this->entries_per_page";
		
		// DEBUG: 
		/*
		print "<pre style='z-index:998;'>";
		print $sporringenFraHelvete;
		print "</pre>";
		*/
		
		$res = $this->query($sporringenFraHelvete);
		
		if ($this->show_calendar) $calendars = array();
		
		if (!$this->use_login_identifier){
			//print "<p class='smalltext'>Viser hendelser for alle. Logg inn for personalisere kalenderen eller gjøre endringer.</p>";
		}
		$noEntries = ($res->num_rows == 0);
		$entriesStr = "";	
		$rNo = 2;
		$this->rss_toc = array();
		while ($row = $res->fetch_assoc()){
			$rNo = ($rNo == 2) ? 1 : 2;
			
			if ($this->show_calendar) {
				$cal_id = $row['cal_id'];
				$cal_color = $row['cal_color'];
				$cal_name = stripslashes($row['cal_name']);
			}
			$dsa = getdate($row['start']);
			$dea = getdate($row['end']);
			if ($dsa['mday'].".".$dsa['mon'].".".$dsa['year'] == $dea['mday'].".".$dea['mon'].".".$dea['year']){
				$ds = $this->weekdays[$dsa['wday']]." ".$dsa['mday'].". ".$this->months[$dsa['mon']-1];
				$dss = $dsa['mday'].".".$dsa['mon'];
			} else if ($dsa['mon'].".".$dsa['year'] == $dea['mon'].".".$dea['year']){
				$ds = $this->weekdays[$dsa['wday']]." ".$dsa['mday'].". - ".
					$this->weekdays[$dea['wday']]." ".$dea['mday'].". ".
					$this->months[$dsa['mon']-1];
				$dss = $dsa['mday'].".".$dsa['mon']." - ".$dea['mday'].".".$dea['mon'];
			} else {
				$ds = $this->weekdays[$dsa['wday']]." ".$dsa['mday'].". ".$this->months[$dsa['mon']-1]." - ".
					$this->weekdays[$dea['wday']]." ".$dea['mday'].". ".$this->months[$dea['mon']-1];

				$dss = $dsa['mday'].".".$dsa['mon']." - ".$dea['mday'].".".$dea['mon'];
			}
			$dateStringLong = $ds;
			$dateStringShort = $dss;
			$dateRssCompatible = date("c",$row['start']);
			$timeString = $this->tosiffer($dsa['hours']).".".$this->tosiffer($dsa['minutes']);
			$subjString = $row['caption'];
			if ($row['cancelled'] == '1'){
				$subjString = "<del>$subjString</del>";
				$dateStringShort = "<del>$dateStringShort</del>";
				$dateStringLong = "<del>$dateStringLong</del>";
			}
			$locaString = ($this->show_location ? $row['location_name'] : "" );
			
			$log = (!isset($row["log_id"]) || empty($row["log_id"])) ? '' : 
				'<img src="'.$this->image_dir.'log.gif" title="Hendelsen er loggført" alt="Hendelsen er loggført" style="float:right;" />';
			$iarchive = (!isset($row["iarchive_id"]) || empty($row["iarchive_id"])) ? '' : 
				'<img src="'.$this->image_dir.'camera.gif" title="Bildearkivet inneholder bilder fra hendelsen" alt="Bildearkivet inneholder bilder fra hendelsen" style="float:right;" />';
			$enrolmentIcon = (!isset($row["enrolment_id"]) || empty($row["enrolment_id"])) ? '' : 
				'<img src="'.$this->image_dir.'task2.gif" title="Hendelsen er åpen for påmelding" alt="Hendelsen er åpen for påmelding" style="float:right;" />';
			$privateIcon = ($row["private"] == "0") ? '' : 
				'<img src="'.$this->image_dir.'lock4.gif" title="Krever innlogging" alt="Krever innlogging" style="float:right;" />';
			
			$url_details = $this->generateCoolURL("/","show_event=".$row['id']);
			if (!empty($row['slug'])) $url_details = $this->generateCoolURL("/".$dsa['year']."/".stripslashes($row['slug']));
			
			$fullurl = 'http://'.$_SERVER['SERVER_NAME'].ROOT_DIR.$url_details;
			$this->rss_toc[] = $fullurl;
			
			if (empty($row['responsible'])) {
				$responsible = "Ukjent";
			} else {				
				$responsible_a = explode(',',$row['responsible']);
				$udata = $this->getUserData($responsible_a, array('FirstName'));
				$responsible_a = array();
				foreach ($udata as $user_id => $u) {
					$responsible_a[] = $u['FirstName'];
				}
				if (count($responsible_a) == 1) {
					$responsible = $responsible_a[0];
				} elseif (count($responsible_a) > 1) {
					$responsible = implode(', ',array_slice($responsible_a,0,count($responsible_a)-1)).' og '.$responsible_a[count($responsible_a)-1];
				}
			}

			$r1a = array(); $r2a = array();
			$r1a[] = "%detailsurl%"; 			$r2a[] = $url_details;
			$r1a[] = "%details_fullurl%"; 		$r2a[] = $fullurl;
			$r1a[] = "%subject%"; 				$r2a[] = $subjString;
			$r1a[] = "%edit%"; 					$r2a[] = '<a href="'.$this->generateURL("edit_event=".$row['id']).'"><img src="'.$this->image_dir.'edit.gif" border="0" title="Rediger denne hendelsen" /></a>';
			$r1a[] = "%noteicon%"; 				$r2a[] = (!$this->show_notes || $row['body'] == "") ? "" : $this->noteicon_template;
			$r1a[] = "%longdate%"; 				$r2a[] = $dateStringLong;
			$r1a[] = "%shortdate%"; 			$r2a[] = $dateStringShort;
			$r1a[] = "%rssdate%"; 				$r2a[] = $dateRssCompatible;
			$r1a[] = "%formattedlocation%";		$r2a[] = str_replace($r1a, $r2a, $this->location_template);
			$r1a[] = "%location%"; 				$r2a[] = $locaString;
			$r1a[] = "%rowno%"; 				$r2a[] = $rNo;
			$r1a[] = "%lead%"; 					$r2a[] = nl2br(stripslashes($row['lead']));
			$r1a[] = "%rsslead%"; 				$r2a[] = $this->prepareForRss($row['lead']);
			$r1a[] = "%image_dir%"; 			$r2a[] = $this->image_dir;
			$r1a[] = "%log_icon%"; 				$r2a[] = $log;
			$r1a[] = "%imagearchive_icon%"; 	$r2a[] = $iarchive;
			$r1a[] = "%enrolment_icon%";	 	$r2a[] = $enrolmentIcon;
			$r1a[] = "%private_icon%";	 		$r2a[] = $privateIcon;
			if ($this->show_responsible) {
				$r1a[] = "%responsible%";	 	$r2a[] = $responsible;		
			}
			if ($this->show_calendar) {
				$r1a[] = "%cal_color%";	 			$r2a[] = $cal_color;
				$r1a[] = "%cal_name%";	 			$r2a[] = $cal_name;
			}	
			if ($this->show_calendar) {
				if (!isset($_SESSION['showcal-'.$this->page_id."-".$cal_id]))
					$_SESSION['showcal-'.$this->page_id."-".$cal_id] = true;
	
				if ($_SESSION['showcal-'.$this->page_id."-".$cal_id])
					$entriesStr .= str_replace($r1a, $r2a, $this->calview_entry_template);
				
				
				$calFound = false;
				foreach ($calendars as $c) {
					if ($cal_id == $c['id']) $calFound = true;
				}
				if (!$calFound) {
					$calendars[] = array(
						'id' => $cal_id,
						'name' => $cal_name,
						'color' => $cal_color
					);
				}
			} else {
				$entriesStr .= str_replace($r1a, $r2a, $this->calview_entry_template);			
			}
		}
		
		$cp = $this->page_no;
		$tp = ceil($this->entry_count/$this->entries_per_page);
		$xofy = str_replace(array("%x%","%y%"),array($cp,$tp),$this->pagexofy_template);
		$lp = ($cp == 1)   ? $this->str_prevpage : '<a href="'.$this->generateURL('cal_page='.($cp-1)).'">'.$this->str_prevpage.'</a>';
		$np = ($cp == $tp) ? $this->str_nextpage   : '<a href="'.$this->generateURL('cal_page='.($cp+1)).'">'.$this->str_nextpage.'</a>';

		
		$url_upcoming = $this->generateCoolURL("/");
		$url_currentyear = $this->generateCoolURL("/".date("Y",time())."/");
		$url_prevyear = $this->generateCoolURL("/".($this->currentYear-1)."/");
		$url_nextyear = $this->generateCoolURL("/".($this->currentYear+1)."/");
		$url_subscribe = $this->generateCoolURL("/","action=subscription");
		$url_new  = $this->generateCoolURL("/","action=newEvent");
		$url_settings = $this->generateCoolURL("/settings");
		$url_currentgroup = $this->generateURL("group_filter=1");
		$url_feed = $this->generateCoolUrl("/rss");

		if (empty($this->currentYear)) {
			$subheader = "Kommende hendelser:";
//			$this->document_title = 'Bilder av '.$member->firstname;
		} else {
			$subheader = "Hendelser i $this->currentYear:";
			$this->document_title = $this->currentYear;
		}
		
		if (empty($this->currentYear)) 
			$viewOptions = ' <a href="'.$url_currentyear.'">Vis hele '.date('Y',time()).'</a>';
		else
			$viewOptions = ' <a href="'.$url_upcoming.'">Vis bare kommende hendelser</a> | 
				<a href="'.$url_prevyear.'" style="background:url('.$this->image_dir.'icns/bullet_arrow_down.png) left no-repeat;padding-left:16px;">Vis forrige år</a> | 
				<a href="'.$url_nextyear.'" style="background:url('.$this->image_dir.'icns/bullet_arrow_up.png) left no-repeat;padding-left:16px;">Vis neste år</a>
			';
		
		/*
		if (!empty($this->current_group))  {
			$g = call_user_func($this->lookup_group,$this->current_group);
			if ($this->group_filter)
				$viewOptions .= " | <a href='".$this->generateURL("")."'>Vis alle hendelser</a>";
			else
				$viewOptions .= " | <a href='$url_currentgroup'>Vis bare hendelser for $g->caption</a>";
		}
		*/
		
		
		if ($this->show_calendar) {
			if (count($calendars) == 1) {
				$calList = "";
			} else {
				$calList = "<div><div style='float:left;padding:4px;'>Vis: </div>";
				foreach ($calendars as $c) {
					if (!isset($_SESSION['showcal-'.$this->page_id."-".$c['id']]))
						$_SESSION['showcal-'.$this->page_id."-".$c['id']] = true;
					$checked = ($_SESSION['showcal-'.$this->page_id."-".$c['id']] ? " checked='checked' " : "");
					$calList .= "
						<div style='float:left; margin:3px; background:white;'>
							<input type='checkbox' id='check".$c['id']."' name='check".$c['id']."' style='float:left;' onchange=\"toggleCal('".$this->page_id."-".$c['id']."')\" $checked />
							<label for='check".$c['id']."' title='Vis/skjul hendelser fra denne terminlisten'>
							   <span style='width:10px; height:10px; background:".$c['color']."; float:left; margin:3px;'><!-- --></span> 
							  ".$c['name']."
							</label>
						</div>";
				}
				$calList .= "<div style='clear:both;'><!-- --></div>";
				$calList .= "</div>";
			}
		}
		
		$r1a = array(); $r2a = array();
		$r1a[] = "%entries%"; 		$r2a[] = $entriesStr;
		$r1a[] = "%newentryurl%"; 	$r2a[] = $url_new;
		$r1a[] = "%subscribeurl%"; 	$r2a[] = $url_subscribe;
		$r1a[] = "%newentry%"; 		$r2a[] = ($this->allow_addentries ? ' <a href="'.$url_new.'" class="icn" style="background-image:url(/images/icns/calendar_add.png);">Legg til hendelse</a> ' : '');
		$r1a[] = "%subscribe%"; 	$r2a[] = ' <a href="'.$url_subscribe.'" class="icn" style="background-image:url(/images/ical16.png);">Abonnér / Last ned</a> ';
		$r1a[] = "%lastpagelink%"; 	$r2a[] = $lp;
		$r1a[] = "%nextpagelink%"; 	$r2a[] = $np;
		$r1a[] = "%pagexofy%"; 		$r2a[] = $xofy;
		$r1a[] = "%pagexofy%"; 		$r2a[] = $xofy;
		$r1a[] = "%header%"; 		$r2a[] = $subheader;
		$r1a[] = "%noentries%";		$r2a[] = ($noEntries ? (empty($this->currentYear) ? $this->noentries_future_template : str_replace($r1a, $r2a, $this->noentries_template)) : "");
		$r1a[] = "%view_options%"; 	$r2a[] = $viewOptions;
		$r1a[] = "%feedurl%";	 	$r2a[] = $url_feed;
		$r1a[] = "%settings%";	 	$r2a[] = ($this->allow_editsettings ? ' <a href="'.$url_settings.'" class="icn" style="background-image:url(/images/icns/wrench.png);">Innstillinger</a> ' : '');
		$r1a[] = "%year%";	 		$r2a[] = $this->currentYear;
		if ($this->show_calendar) {
			$r1a[] = "%cal_list%"; 		$r2a[] = $calList;
		}
		$outp = str_replace($r1a, $r2a, $this->calview_template2);
		
		if ($this->show_calendar && strpos($this->calview_template2,'%cal_list%')!==false) {
			$output .= "
				<script type='text/javascript'>
				//<![CDATA[
					function toggleCal(id) {
						window.location = \"".$this->generateUrl(array("action=toggleCal","calendarId="),true)."\"+id;
					}
				//]]>
				</script>
			";
		}
		
		$output .= $outp;
		
		if (!empty($this->currentYear)) {
			call_user_func(
				$this->add_to_breadcrumb, 
				'<a href="'.$this->generateCoolURL('/'.($this->currentYear).'/').'">'.$this->currentYear.'</a>'
			); 
		}
		
		return $output;
	}
		
	/*
		Function: makeEventsDropDown
		options:
			pastEventsOnly	 		- bool
			futureEventsOnly 		- bool
			eventsWithoutLogOnly	- bool
			numEvents				- int
			defaultEvent			- int
	*/
	public function makeEventsDropDown($options) { // $options, $limit, $defaultVal = -1, $pastOnly = false, $futureOnly = false
		$tct = $this->table_calendar;
		$tcct = $this->table_calendars;
		$tlt = $this->table_logentries;
		
		// Parse options
		$pastEventsOnly = (isset($options['pastEventsOnly']) && ($options['pastEventsOnly']));
		$futureEventsOnly = (!$pastEventsOnly && (isset($options['futureEventsOnly']) && ($options['futureEventsOnly'])));		
		$eventsWithoutLogOnly = (isset($options['eventsWithoutLogOnly']) && ($options['eventsWithoutLogOnly']));
		$numEvents = isset($options['numEvents']) ? intval($options['numEvents']) : 10;
		$defaultEvent = isset($options['defaultEvent']) ? intval($options['defaultEvent']) : 0;

		// Build query
		$whereClause = "WHERE $tct.cancelled=0";
		if ($pastEventsOnly) $whereClause .= " AND $tct.dt_start < NOW()";
		if ($futureEventsOnly) $whereClause .= " AND $tct.dt_end > NOW()";
		//if ($eventsWithoutLogOnly) $whereClause .= " AND $tct.log = '0'";
		if ($this->show_calendar) $whereClause .= " AND $tct.calendar_id = $tcct.id AND $tct.calendar_id IN($this->show_calendars)";
		$res = $this->query("SELECT
				$tct.id, $tct.caption, $tlt.id as log_id,
				UNIX_TIMESTAMP($tct.dt_start) as start, UNIX_TIMESTAMP($tct.dt_end) as end
			FROM $tct LEFT JOIN $tlt ON $tlt.event_id=$tct.id".($this->show_calendar ? ",$tcct" : "")."
			$whereClause
			ORDER BY $tct.dt_start ".($futureEventsOnly ? "" : "DESC")."
			LIMIT $numEvents"
		);

		// Build list
		$clist = ""; $foundDef = false; $cur_mon = 0;
		while ($row = $res->fetch_assoc()){
			$caption = stripslashes($row["caption"]);
			$dt_start = date("j.n.y",$row['start']);
			$dt_end = date("j.n.y",$row['end']);
			$dt = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
			$caption = "$caption ($dt)";
			if ($cur_mon != date("n",$row['start'])) {
				if ($cur_mon != 0) $clist .= "</optgroup>";
				$cur_mon = date("n",$row['start']);
				$clist .= "<optgroup label='".ucfirst($this->months[$cur_mon-1])." ".date("Y",$row['start'])."'>";
			}
			$isDefault = ($defaultEvent == $row['id']);
			$clist .= '<option value="'.$row["id"].'"'.
				($isDefault ? ' selected="selected"' : '').
				((!$isDefault && $eventsWithoutLogOnly && isset($row['log_id']) && ($row['log_id'] != '0')) ? ' disabled="disabled"' : '').
				">$caption</option>\n				";
			if ($isDefault) $foundDef = true;
		}
		$clist .= "</optgroup>";
		
		// If the selected default event is very old, it may not be present in the list created. 
		// If so, we have to add it manually to the list:
		if ($defaultEvent != -1 && $defaultEvent != 0 && !$foundDef) {
			$res = $this->query("SELECT
				id, caption, UNIX_TIMESTAMP(dt_start) as ds, UNIX_TIMESTAMP(dt_end) as de
				FROM $this->table_calendar WHERE id = $defaultEvent"
			);
			$row = $res->fetch_assoc();
			$caption = stripslashes($row['caption']);
			$dt_start = date("j.n.y",$row['ds']);
			$dt_end = date("j.n.y",$row['de']);
			$dt = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
			$caption = "$caption ($dt)";
			$clist = "<option value='$defaultEvent' selected='selected'>$caption</option>\n				";
		}
		
		// Return the cake
		return $clist;	
	}
	
	
	/*
		Function getCalendarEvents
		Utility function that only requires page inspecific initialization
		options: { 
		    selected: int, 
		    onlyFutureEvents:bool, 
		    onlyPastEvents:bool, 
		    noLogExists:bool, 
		    addSelectedIfNotFound:bool,
		    maxFutureDays: int
		}
	*/
	function getCalendarEvents($cal_id = 0, $options = array()) {
		$tct = $this->table_calendar;
		$tcct = $this->table_calendars;
		$cal_id = intval($cal_id);

		$whereClause = "WHERE $tct.cancelled=0";
		$whereClause .= " AND $tct.calendar_id = $tcct.id";
		if ($cal_id != 0) $whereClause .= " AND $tct.calendar_id=$cal_id";
		
		if (isset($options['onlyPastEvents']) && ($options['onlyPastEvents'])) {
			$whereClause .= " AND $tct.dt_start < NOW()";
		} else {
		    if (isset($options['onlyFutureEvents']) && ($options['onlyFutureEvents'])) 
			    $whereClause .= " AND $tct.dt_end > NOW()";
			if (isset($options['maxFutureDays']) && ($options['maxFutureDays'])) 
			    $whereClause .= " AND $tct.dt_start < DATE_ADD(NOW(), INTERVAL ".intval($options['maxFutureDays'])." DAY)";
        }
		if (isset($options['noLogExists']) && ($options['noLogExists'])) 
			$whereClause .= " AND $tct.log = '0'";
		
		$orderBy = "$tct.dt_start";
		$limit = "";
		// ".($futureOnly ? "" : "DESC")."
		$res = $this->query("SELECT
				$tct.id,
				UNIX_TIMESTAMP($tct.dt_start) as startdate,
				UNIX_TIMESTAMP($tct.dt_end) as enddate,
				$tct.caption as caption,
				$tct.slug as slug,
				$tct.calendar_id,
				$tcct.caption as cal_name_short,
				$tcct.flag,
				$tcct.default_cal_page
			FROM 
				$tct,$tcct
			$whereClause
			ORDER BY $orderBy
			$limit
			"
		);
		
		$rlist = array();
		while ($row = $res->fetch_assoc()){
			$year = date('Y',$row['startdate']);
			$row['uri'] = $this->getUrlToPage($row['default_cal_page'])."/$year/".$row['slug'];
		    $rlist[] = $row;
		}
		return $rlist;
	}	
	
	/*
		Function getCalendarEventsAsOptionList
		Utility function that only requires page inspecific initialization
		options: { selected: int, onlyFutureEvents:bool, onlyPastEvents:bool, noLogExists:bool, addSelectedIfNotFound:bool }
	*/
	function getCalendarEventsAsOptionList($cal_id = 0, $options = array()) {
		$cal_id = intval($cal_id);
		$events = $this->getCalendarEvents($cal_id, $options);

		$selected = -1;
		if (isset($options['selected'])) $selected = intval($options['selected']);
		$addSelectedIfNotFound = true;
		if (isset($options['addSelectedIfNotFound']))
			$addSelectedIfNotFound = ($options['addSelectedIfNotFound'] == true);
				
		$clist = "";
		$foundDef = false;
		$cur_mon = 0;
		
		/*if ($res->num_rows == 0) {
			return '<option disabled="disabled" value="-1">Ingen hendelser</option>';
		}*/
		
		foreach ($events as $event) {
			$caption = stripslashes($event["caption"]);
			$dt_start = date("j.n.y",$event['startdate']);
			$dt_end = date("j.n.y",$event['enddate']);
			$dt = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
			$caption = "$caption ($dt)";
			if ($cur_mon != date("n",$event['startdate'])) {
				if ($cur_mon != 0) $clist .= "</optgroup>";
				$cur_mon = date("n",$event['startdate']);
				$clist .= "<optgroup label='".strftime('%B %Y',$event['startdate'])."'>";
			}
			$clist .= "<option value='".$event["id"]."' ".(($selected == $event['id']) ? "selected='selected'" : "").">$caption</option>\n				";			
			if ($selected == $event['id']) $foundDef = true;
		}
		$clist .= "</optgroup>";
		
		/*
			Hvis default-hendelsen ikke ble funnet, legger vi den til manuelt.
			Måten dette gjøres på bør forbedres.
		*/
		if ($addSelectedIfNotFound) {
			if ($selected != -1 && $selected != 0 && !$foundDef) {
				$res = $this->query("SELECT
						id,
						UNIX_TIMESTAMP(dt_start) as startdate,
						UNIX_TIMESTAMP(dt_end) as enddate,
						caption
					FROM 
						$tct
					WHERE id = '$selected'"
				);
				$row = $res->fetch_assoc();
				$caption = stripslashes($row["caption"]);
				$dt_start = date("j.n.y",$row['startdate']);
				$dt_end = date("j.n.y",$row['enddate']);
				$dt = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
				$caption = "$caption ($dt)";
				$clist .= "<option value='".$row["id"]."' selected='selected'>$caption</option>\n				";
			}
		}
		return $clist;	
	}	
	
	/*
		Function getEventDetails
		Utility function that only requires page inspecific initialization
	*/	
	function getEventDetails($p1,$p2='') {
		// Make some short vars to make the code more readable...
		$tct = $this->table_calendar;
		$tlt = $this->table_locations;

		if (empty($p2)) {
			if (!is_numeric($p1)) fatalError("Invalid input (2)!");
			$id = intval($p1);
			$whereCriteria = "$tct.id = $id";
		} else {
			if (!is_numeric($p1)) fatalError("Invalid input (2)!");
			$year = intval($p1);
			$slug = addslashes($p2);
			$whereCriteria = "YEAR($tct.dt_start) = $year AND $tct.slug=\"$slug\"";
		}
	
		$res = $this->query(
			"SELECT 
				$tct.id,
				$tct.slug,
				$tct.creator,
				$tct.cancelled,
				YEAR($tct.dt_start) as year,
				UNIX_TIMESTAMP($tct.dt_start) as startdate,
				UNIX_TIMESTAMP($tct.dt_end) as enddate,
				$tct.caption,
				$tct.lead,
				$tct.body,
				$tlt.caption as location_name
			 FROM 
				$tct,$tlt
			WHERE $whereCriteria
				AND $tct.location_id = $tlt.id"
		);
		if ($res->num_rows != 1){
			$this->notSoFatalError("The calendar-event '$whereCriteria' does not exist!");
			return false;
		}
		$row = $res->fetch_assoc();
		return $row;
	}
	
	/*
		Function getLinkToEvent
		Utility function that only requires page inspecific initialization
		Parameters:
			id: int
			customText: string
			urlOnly: bool
	*/	
	function getLinkToEvent($id, $customText = "", $urlOnly = false) {
		$d = $this->getEventDetails($id);
		//$url = "/".$this->fullslug."/".date("Y",$d['start'])."/?show_event=$id";
		$url = $this->generateCoolURL("/","show_event=".$id);
		$year = date("Y",$d['startdate']);
		if (!empty($d['slug'])) $url = $this->generateCoolURL("/$year/".stripslashes($d['slug']));
		if ($urlOnly) return $url;
		$txt = $d['caption'];
		if (!empty($customText)) $txt = $customText;
		return "<a href=\"$url\" class=\"icn\" style=\"background-image:url(/images/icns/calendar.png);\">$txt</a>";
	}
	
	function listCalendarPages($cal_id = -1) {

		$lang = $this->preferred_lang;
		$calpages = array();
		$res = $this->query("SELECT 
				$this->table_pages.id,
				$this->table_pages.fullslug,
				$this->table_pagelabels.value as header
			FROM 
				$this->table_pages 
			LEFT JOIN
				$this->table_pagelabels
				ON
				($this->table_pagelabels.page=$this->table_pages.id
				AND
				$this->table_pagelabels.label='page_header'
				AND
				$this->table_pagelabels.lang='$lang')
			WHERE 
				$this->table_pages.class=$this->class_id"
		);
		while ($row = $res->fetch_assoc()) {	
			if ($cal_id == -1) {
				$calpages[] = $row;
			} else {
				$page_id = intval($row['id']);
				$page_cals = $this->getPageOptionValue($page_id,'show_calendars',intval($this->class_id));
				error_log("pageid: $page_id, classid:$this->class_id, cal id:$cal_id, $page_cals");
				$page_cals = explode(",",$page_cals);
				if (in_array($cal_id,$page_cals)) {
					$calpages[] = $row;
				}			
			}	
		}
		return $calpages;
	}
	
}

?>
