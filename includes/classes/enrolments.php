<?
/*
 * Class:       enrolments
 * Description: The enrolments class can be thought of as consisting of two parts, one 
 *				being the enrolment list that lists all enrolments and allows for the 
 *				creation of new enrolments. The other part is the interface for an 
 *				individual enrolment.
 *
 * Table of contents:
 *   1. Variables
 *   2. Strings and templates
 *   3. Initialization methods
 *   4. Public methods for use by other classes:
 *   5. General actions   (for the `list of enrolments' part)
 *   6. Enrolment actions (for the `individual enrolment' part)
 *   7. Utility methods for use by other methods in this class
 *
 */

class enrolments extends base {

	/* ========================================================================================
		1. Variables
	   ======================================================================================== */

	private $action = 'viewList'; // default action
	
	/* Public database settings (for use by other classes) */
	public $table_enrolments = 'enrolments';
	public $table_enrolments_field_event = 'event_id';
	private $table_enrolments_user = 'enrolments_users';
	private $table_calendars = 'cal_calendars';
	private $table_calitems = 'cal_events';

	/* Default values for some variables */
	public $flagIfFewerDaysLeftThan = 4;
	public $closingDateDefaultHour = '23';
	private $enrolment_id = 0;
	private $event_id = 0;
	private $cal_id = 0;
	private $event_obj = null;
	private $cal_instance = null;

	/* All permissions set to false by default. These are set by the CMS. */	
	public $allow_addenrolment = false;
	public $allow_enrollall = false;
	public $allow_viewcomments = false;
    public $allow_enroll = false;

    /* Settings */
    public $force_calendar = 0;

	/* ========================================================================================
		2. Strings and templates
	   ======================================================================================== */

	public $errorMessages = array(
		'incomplete'				=> 'Du må fylle inn både kalender og arrangement.',
		'duplicate'					=> 'Det eksisterer allerede en påmelding for dette arrangementet.',
		'no_closingdate'			=> 'Du krysset av for at påmeldingen skulle ha en påmeldingsfrist, men valgte ikke noen dato.',
		'closingdate_inthepast' 	=> 'Påmeldingsfristen er allerede passert!',
		'closingdate_afterstart'	=> 'Påmeldingsfristen er etter arrangementet har startet!'
	);
	private $template_newPaamelding = '
			<h2>Opprett ny påmelding</h2>
			<p>
				Velg først en kalender (terminliste), deretter et arrangement i den valgte 
				kalenderen. Dersom du ikke finner arrangementet i listen, må du først opprette
				det i terminlisten.
			</p>
			<p>
				%errors%
			</p>
			<script type="text/javascript">
			//<![CDATA[
			
				function fetch_events() {					
					cal_id = $("cal_id").value;
					if (cal_id == "-") {
						setText("span_event", "");					
					} else {
						var pars = new Array();
						pars.push("cal_id="+cal_id);
						pars.push("event_id=%events_id%");
						pars = pars.join("&");
						setText("span_event", "Vent litt...");
						jQuery.ajax({
                            url: "%events_uri%",
                            type: "POST",
                            data: pars, 
                            dataType: "html",
                            success: function(responseText){ 
                                setText("span_event",responseText);
                            }
                        });
					}
				}
			
			//]]>
			</script>
			<form method="post" name="calendarform" id="calendarform" action="%post_uri%">
				<table>
				<tr><td style="font-weight:bold;text-align:right;padding-top:4px;padding-bottom:8px;">Kalender: </td><td valign="top"><select name="cal_id" id="cal_id" onchange="fetch_events();">%cal_opts%</select></td></tr>
				<tr><td style="font-weight:bold;text-align:right;padding-top:4px;padding-bottom:8px;">Arrangement: </td><td valign="top"><span id="span_event"></span></td></tr>
				<tr><td style="font-weight:bold;text-align:right;padding-top:6px;padding-bottom:8px;" valign="top">Påmeldingsfrist: </td><td valign="top">
					<div style="padding:5px;">	
						<input type="radio" name="frist" id="frist_ja" value="ja" checked="checked" /><label for="frist_ja">Påmeldingsfrist: </label>%closing_date%<br />
						<input type="radio" name="frist" id="frist_nei" value="nei" /><label for="frist_nei">Ingen påmeldingsfrist</label>
					</div>
				</td></tr>
				<tr><td style="font-weight:bold;text-align:right;padding-top:4px;padding-bottom:8px;" valign="top">Gjester: </td><td>
				  <input type="checkbox" name="guestsallowed" id="guestsallowed" /><label for="guestsallowed">Påmeldte kan ta med gjester</label>
				</td></tr>
				</table>
				<input type="submit" value="Opprett påmelding" />
			</form>
			<script type="text/javascript">
		    //<![CDATA[	

				function onYuiLoaderComplete() {
                    console.log("YUI loaded");
					YAHOO.util.Event.onContentReady("closingdate", function() {
						(new BG18.datePicker("closingdate", { selectedDate: %date_date_js% } )).init();
					});
					fetch_events();
				}

				loader.require("button","calendar");
				loader.insert();
			
			//]]>
			</script>
		';
	private $template_editPaamelding = '
			<h2>%title%</h2>
			<p>
				%errors%
			</p>
			<form method="post" name="enrolmentform" id="enrolmentform" action="%post_uri%">
				<fieldset>
				  <legend>Påmeldingsfrist</legend>
					<p>
						Dersom du velger en påmeldingsfrist, stenges påmeldingen kl. 23:00 på 
						datoen du velger. Etter dette kan man verken melde seg på eller av.
					</p>
						<div style="padding:5px;">
							
								<input type="radio" name="frist" id="frist_ja" value="ja" %def_yes% /><label for="frist_ja">Påmeldingsfrist: </label>%closing_date%<br />
								<input type="radio" name="frist" id="frist_nei" value="nei" %def_no% /><label for="frist_nei">Ingen påmeldingsfrist</label>
							
						</div>
				</fieldset>
				<fieldset>
				  <legend>Gjester</legend>
				  <input type="checkbox" name="guestsallowed" id="guestsallowed"%guestsallowed% /><label for="guestsallowed">Påmeldte kan ta med gjester</label>
				</fieldset>

				<div style="padding:10px;">
					<input type="button" value="Avbryt" onclick=\'window.location="%cancel_uri%"\' />
					<input type="submit" value="Lagre endringer" />
				</div>
			</form>
			<script type="text/javascript">
		    //<![CDATA[	

				function onYuiLoaderComplete() {
					YAHOO.util.Event.onContentReady("closingdate", function() {
						(new BG18.datePicker("closingdate", { selectedDate: %date_date_js%, maxDate: "%maxdate_js%" } )).init();
					});
				}

				loader.require("button","calendar");
				loader.insert();
			
			//]]>
			</script>
		';
	private $template_deletePaamelding = '
			<h2>Slette påmelding og alle påmeldte?</h2>
			
			<form method="post" name="calendarform" id="calendarform" action="%post_uri%">
				<p class="warning">
				  Er du helt sikker på at du vil kansellere og slette påmeldingen til arrangementet «%event_name%»? <br /><br />
				  Hvis du velger å fortsette vil 
				  påmeldingen slettes og <strong>alle</strong> påmeldte bli meldt av!
				</p>
				<input type="button" value="Avbryt" onclick=\'window.location="%cancel_uri%"\' />
				<input type="submit" value="Slett påmeldingen" />
			</form>
		';
	private $template_cancelEnrolment = '
		<h2>Avmelding</h2>
			<form method="post" action="%post_uri%">
				<p>
					Ønsker du virkelig å kansellere påmeldingen av <strong>%fullname%</strong> til <strong>%eventname%</strong>?
				</p>
				<p>
					Eventuelle kommentarer til avmeldingen: <br />
  					<textarea cols="60" rows="6" name="comment"></textarea>
				</p> 
				<p>
					<input type="hidden" name="registrationId" value="%registrationId%" />
					<input type="button" value="Avbryt" onclick=\'window.location="%cancel_uri%"\' />
					<input type="submit" value="Meld av" />
				</p>
			</form>
		';

	/* ========================================================================================
		3. Initialization methods
	   ======================================================================================== */
	
	// Constructor:
	function enrolments() {
		$this->table_enrolments = DBPREFIX.$this->table_enrolments;
		$this->table_enrolments_user = DBPREFIX.$this->table_enrolments_user;
		$this->table_calendars = DBPREFIX.$this->table_calendars;
		$this->table_calitems = DBPREFIX.$this->table_calitems;
	}
	
	public function initialize(){

		@parent::initialize();		
		array_push($this->getvars,'action','registrationId','userId','cal_id','event_id');

		// Determine requested action:
		
		$action = isset($_GET['action']) ? $_GET['action'] : '';

		if (isset($this->coolUrlSplitted[0])) {  // Are we on a specific enrolment page?
			$enrolmentId = intval($this->coolUrlSplitted[0]);
			$res = $this->query("SELECT id,calendar_id,event_id FROM $this->table_enrolments WHERE id=$enrolmentId");
			if ($res->num_rows != 1) $this->fatalError("The entry does not exist!");
			$row = $res->fetch_assoc();
			$this->enrolment_id = intval($row['id']);
			$this->cal_id = intval($row['calendar_id']);
			$this->event_id = intval($row['event_id']);
			$cal_page = $this->getCalPage($this->enrolment_id);
			$this->cal_instance = $this->initializeCalendarInstance($cal_page);
			$this->event_obj = $this->cal_instance->getEventDetails($this->event_id);
			call_user_func(
				$this->add_to_breadcrumb,
				'<a href="'.$this->generateCoolUrl("/$this->enrolment_id").'">'.$this->event_obj["caption"].'</a>'
			);
		} else {
			$this->cal_instance = $this->initializeCalendarInstance();
		}
		
		$generalActions = array(
			'addEnrolment','addEnrolmentDo','ajaxGetListOfEvents'
		);
		$enrolmentActions = array(
			'editEnrolment','editEnrolmentDo','deleteEnrolment','deleteEnrolmentDo','emailForm',
			'register','registerDo','reRegister','cancel','cancelDo','viewRegistration',
			'ajaxAddGuest','ajaxEditGuest','ajaxSaveGuest','ajaxRemoveGuest','ajaxListGuests'
		);
		
		if ($this->enrolment_id > 0){
			if (!in_array($action,$enrolmentActions)) $action = 'viewEnrolment';
		} else {
			if (!in_array($action,$generalActions)) $action = 'viewList';
		}
		
		$this->action = $action;
	}

	public function run(){
		$this->initialize();
		return call_user_func(array($this,$this->action));
	}

	/* ========================================================================================
		4. Public methods for use by other classes:
		   (such as the calendar class and the memberlist class)
	   ======================================================================================== */
	
	/*
		Function: getEnrolmentIdFromEventId
		Given a known calendar event id, this function will return the corresponding
		enrolment id. If no corresponding enrolment exists, the function will
		return zero (0).
	*/
	public function getEnrolmentIdFromEventId($event_id, $cal_id = 0) {
		$event_id = intval($event_id); $cal_id = intval($cal_id);
		if ($cal_id > 0) 
			$res = $this->query("SELECT id FROM $this->table_enrolments WHERE event_id=$event_id AND calendar_id=$cal_id");
		else
			$res = $this->query("SELECT id FROM $this->table_enrolments WHERE event_id=$event_id");
		if ($res->num_rows != 1) {
			return 0;
		}
		$row = $res->fetch_assoc();
		return intval($row['id']);	
	}
	
	public function getEnrolmentClosingDate($enrolment_id) {
		if (!is_numeric($enrolment_id)) return false;
		$res = $this->query("SELECT closing_date FROM $this->table_enrolments WHERE id=$enrolment_id");
		if ($res->num_rows != 1) return false;
		$row = $res->fetch_assoc();
		return strtotime($row['closing_date']);
	}
	
	public function getEnrolmentParticipants($id) {
		if (!is_numeric($id)) return false;
		$ppl = array();
		$res = $this->query("SELECT id,person,enrolledby,comment,cancelledby FROM $this->table_enrolments_user WHERE enrolment=$id AND cancelledby=0");
		$guests = 0;
		while ($row = $res->fetch_assoc()) {
			if ($row['person'] == 0) 
				$guests++;
			else
				$ppl[$row['person']] = call_user_func($this->make_memberlink,$row['person']);
		}
		return array(
			'members' => $ppl,
			'guests' => $guests
		);
	}
	
	public function getLinkToEnrolment($id) {
		return "/".$this->fullslug."/$id/";	
	}
	
	public function getLinkToNewEnrolmentForm($cal_id, $event_id) {
		return "/".$this->fullslug."?action=addEnrolment&amp;cal_id=$cal_id&amp;event_id=$event_id";		
	}
	
	/* ========================================================================================
		5. General actions:
		   Actions available at the page showing the list of enrolments
	   ======================================================================================== */

	private function viewList() {
		
		$calnames = $this->listCalendars();
		$output = "";
		if ($this->allow_addenrolment) { 
			$output .= '<p class="headerLinks"><a href="'.$this->generateCoolUrl("/","action=addEnrolment").'" class="add">Opprett ny påmelding</a></p>';
		}
		$te = $this->table_enrolments;
		$tu = $this->table_enrolments_user;
		$tc = $this->cal_instance->table_calendar;
		$tc_start = $this->cal_instance->table_calendar_field_startdate;
		$tc_end = $this->cal_instance->table_calendar_field_enddate;
		$tc_cap = $this->cal_instance->table_calendar_field_caption;
		
		$output .= "<h2>Aktive påmeldinger</h2><ul style='margin:0px;padding:5px;'>";
		$res = $this->query("SELECT 
			$te.id, $te.calendar_id, $te.event_id, $te.closing_date,
			$tc.$tc_start as dt_start, $tc.$tc_end as dt_end, $tc.$tc_cap as caption,
			COUNT($tu.id) as paameldte 
			FROM $te LEFT JOIN $tu ON ($te.id=$tu.enrolment AND $tu.cancelledby=0), $tc
            WHERE $te.page_id=$this->page_id 
                AND $te.event_id=$tc.id 
				AND (
					$te.closing_date > NOW() 
					OR (ISNULL($te.closing_date) AND $tc.dt_end > NOW())
				)
			GROUP BY $te.id
			ORDER BY $te.closing_date, $tc.dt_end"
		);
		if ($res->num_rows == 0) {
			$output .= '<li style="list-style-type: none; background:url(/images/icns/error.png) no-repeat top left; margin: 0px; padding: 1px 10px 4px 22px;">
				<em>Akkurat nå er det ingen aktive påmeldinger.</em>
				</li>
			';
		} else {
			while ($row = $res->fetch_assoc()) {
				$output .= $this->makeListEntry($calnames, $row);
			}
		}
		$output .= "</ul>";
		

		$output .= "<h2>Siste stengte påmeldinger</h2><ul style='margin:0px;padding:5px;'>";
		$res = $this->query("SELECT 
			$te.id, $te.calendar_id, $te.event_id, $te.closing_date,
			$tc.$tc_start as dt_start, $tc.$tc_end as dt_end, $tc.$tc_cap as caption,
			COUNT($tu.id) as paameldte 
			FROM $te LEFT JOIN $tu ON ($te.id=$tu.enrolment AND $tu.cancelledby=0), $tc
            WHERE $te.page_id=$this->page_id
                AND $te.event_id=$tc.id 
				AND (
					$te.closing_date < NOW() 
					OR (ISNULL($te.closing_date) AND $tc.dt_end < NOW())
				)
			GROUP BY $te.id
			ORDER BY $te.closing_date DESC, $tc.dt_end DESC
			LIMIT 5"
		);
		
		while ($row = $res->fetch_assoc()) {
			$output .= $this->makeListEntry($calnames, $row);
		}
		$output .= "</ul>";		
		
		return $output;
	}
	
	private function makeListEntry($calnames, $row) {
		$id = $row['id'];
		$cal_id = $row['calendar_id'];
		$event_id = $row['event_id'];
		$caption = $row['caption'];
		$dt_start = strtotime($row['dt_start']);
		$dt_start_str = strftime('%A %e. %B %Y',$dt_start);
		$dt_end = strtotime($row['dt_end']);
		$dt_end_str = strftime('%A %e. %B %Y',$dt_end);
		$dt = ($dt_start == $dt_end) ? $dt_start_str : "$dt_start_str - $dt_end_str";
		$datestr = ucfirst($dt);

		$icon = 'flag_green.png';
		if ($dt_start < time()) {
			$icon = 'flag_red.png';
		} else if ($dt_start < time()+36400*$this->flagIfFewerDaysLeftThan) { // Less than 3 days left
			$icon = 'flag_yellow.png';
			$datestr = '<span style="background:yellow;">'.$datestr.'</span>';
		}

		$cdat = $row['closing_date'];
		if ($cdat == '0000-00-00 00:00:00') { 
			$cdatstr = "<em>Ingen</em>";
		} else {
			$cdat = strtotime($cdat);
			$cdatstr = strftime('%A %e. %B %Y, kl. %H',$cdat);
			if ($cdat < time()) { 
				$icon = 'flag_red.png';
			} else if ($cdat < time()+36400*$this->flagIfFewerDaysLeftThan) { // Less than 3 days left
				$icon = 'flag_yellow.png';
				$cdatstr = '<span style="background:yellow;">'.$cdatstr.'</span>';
			}
		}
		
		$paameldte = $row['paameldte'];
		$urlDetails = $this->generateCoolUrl("/$id");
		return '
			<li style="list-style-type: none; background:url(/images/icns/'.$icon.') no-repeat top left; margin: 0px; padding: 0px 10px 4px 20px;">
				<div style="font-size:120%"><a href="'.$urlDetails.'">'.$caption.'</a></div>
				<div style="color:#333;">'.$datestr.' ('.$calnames[$cal_id]['caption'].'). '.$paameldte.' påmeldte.</div>
				<div>Påmeldingsfrist: '.$cdatstr.'. </div>
			</li>
		';
	}
	
	private function ajaxGetListOfEvents() {
		$cal_id = intval($_POST['cal_id']);
		$default_event_id = intval($_POST['event_id']);
		$this->cal_instance = $this->initializeCalendarInstance();
		$dropdown = $this->cal_instance->getCalendarEventsAsOptionList($cal_id, array(
			'onlyFutureEvents' => true,
			'selected' => $default_event_id,
			'addSelectedIfNotFound' => false
		));
		print "
			<select name='event_id'>
			".$dropdown."
			</select>
		";
		exit();
	}
	
	private function addEnrolment() {
		if (!$this->allow_addenrolment) return $this->permissionDenied();

		$defaultCalId = 0;
		$defaultEventId = 0;
		if (isset($_GET['cal_id'])) $defaultCalId = intval($_GET['cal_id']);
		if (isset($_GET['event_id'])) $defaultEventId = intval($_GET['event_id']);

		$errstr = "";
		if (isset($_SESSION['errors'])){

			if (isset($_SESSION['postdata']['cal_id'])) $defaultCalId = $_SESSION['postdata']['cal_id'];
			if (isset($_SESSION['postdata']['event_id'])) $defaultEventId = $_SESSION['postdata']['event_id'];
					
			$errors = $_SESSION['errors'];
			$errstr = "<ul>";
			foreach ($_SESSION['errors'] as $s){
				if (isset($this->errorMessages[$s]))
					$errstr.= "<li>".$this->errorMessages[$s]."</li>";
				else
					$errstr.= "<li>$s</li>";				
			}
			$errstr .= "</ul>";
			$errstr = $this->notSoFatalError($errstr,array('logError'=>false,'customHeader'=>'Påmeldingen ble ikke opprettet fordi:'));
			
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);
			
		}

		$calendars = $this->listCalendars();
		$cal_opts = "";
		$cal_opts .= "<option value='-'>-- Velg fra listen --</option>\n";
		foreach ($calendars as $id => $c) {
            $selc = ($id == $defaultCalId) ? " selected='selected'" : "";
            $add_cal = true;
            if ($this->force_calendar == 0) {
			    $cal_opts .= "<option value='".$id."'$selc>".stripslashes($c['caption'])."</option>\n";
            } else {
                if ($id == $this->force_calendar) {
                    $cal_opts .= "<option value='".$id."' selected='selected'>".stripslashes($c['caption'])."</option>\n";
                }
            }
		}

		$date_code = $this->makeDateField("closingdate", 0, false);
		$date_date_js = 0;		
		$date_code .= ', klokken '.$this->generateTimeField('closingdate_time', $this->closingDateDefaultHour);

		$r1a = array(); 				$r2a = array();
		$r1a[] = "%errors%";			$r2a[] = $errstr;
		$r1a[] = "%post_uri%";			$r2a[] = $this->generateUrl('action=addEnrolmentDo');
		$r1a[] = "%cal_opts%";			$r2a[] = $cal_opts;
		$r1a[] = "%events_uri%";		$r2a[] = $this->generateUrl('action=ajaxGetListOfEvents',true);
		$r1a[] = "%closing_date%";		$r2a[] = $date_code;
		$r1a[] = "%date_date_js%";		$r2a[] = $date_date_js;
		$r1a[] = "%def_yes%";			$r2a[] = "checked='checked'";
		$r1a[] = "%def_no%";			$r2a[] = "";
		$r1a[] = "%events_id%";			$r2a[] = $defaultEventId;

		return str_replace($r1a, $r2a, $this->template_newPaamelding);
	}
	
	private function addEnrolmentDo() {
		if (!$this->allow_addenrolment) return $this->permissionDenied(); 

		$errors = array();

		if (!isset($_POST['cal_id']) || !isset($_POST['event_id'])) {

			array_push($errors,'incomplete');

		} else {

			$cal_id = intval($_POST['cal_id']);
			$event_id = intval($_POST['event_id']);		

			$closingdate = addslashes(sprintf('%04d-%02d-%02d %02d:00:00',
				intval($_POST['closingdate_year']),intval($_POST['closingdate_month']),intval($_POST['closingdate_day']),
				intval($_POST['closingdate_time_h'])
			));	
			$closingdate_unix = strtotime($closingdate);
			if ($_POST['frist'] == 'nei') {
				$closingdate = '0000-00-00 00:00:00';
			} else {
				if (intval($_POST['closingdate_year'])<=0) {
					array_push($errors,'no_closingdate');
				} else {
					if ($closingdate_unix <= time()) {
						array_push($errors,'closingdate_inthepast');					
					}
					$ci = $this->cal_instance;
                    $e = $ci->getEventDetails($event_id);
					if ($closingdate_unix > $e['startdate']) {
						array_push($errors,'closingdate_afterstart');					
					}

				}
			}
			
			$guestsallowed = 0;
			if (isset($_POST['guestsallowed']) && ($_POST['guestsallowed'] == 'on')) $guestsallowed = 1;
	
			if (($cal_id <= 0) || ($event_id <= 0)) {
				array_push($errors,'incomplete');
			}
	
			$res = $this->query("SELECT * FROM $this->table_enrolments WHERE calendar_id='$cal_id' AND event_id='$event_id'");
			if ($res->num_rows > 0) {
				array_push($errors,'duplicate');
			}
		}

		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$this->redirect($this->generateURL('action=addEnrolment'));
		}
		
		$this->query("INSERT INTO $this->table_enrolments 
			(calendar_id,event_id,closing_date,guestsallowed,page_id) VALUES ($cal_id,$event_id,\"$closingdate\",$guestsallowed,$this->page_id)"
		);
		$id = $this->insert_id();

		$this->cal_instance = $this->initializeCalendarInstance();
		$this->event_obj = $this->cal_instance->getEventDetails($event_id);
		$this->addToActivityLog("startet <a href=\"".$this->generateCoolUrl("/$id")."\">påmelding</a> til ".$this->event_obj['caption'].".");

		$this->redirect($this->generateCoolUrl("/$id"),"Påmeldingen er opprettet");

	}
	
	/* ========================================================================================
		6. Enrolment actions:
		   Actions available at an individual enrolment page
	   ======================================================================================== */
	
	private function editEnrolment() {
		if (!$this->allow_addenrolment) return $this->permissionDenied();
		$id = $this->enrolment_id;
		
		$res = $this->query("SELECT calendar_id,event_id,closing_date,guestsallowed FROM $this->table_enrolments WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();		

		$closingdateSet = ($row['closing_date'] != '0000-00-00 00:00:00');
		$closingdate = $closingdateSet ? strtotime($row['closing_date']) : 0;

		$guestsallowed = ($row['guestsallowed']) ? ' checked="checked"' : '';

		//$date_str = $closingdateSet ? strftime('%A %e. %B %Y',$closingdate) : '<em>Ikke satt</em>';
		$date_code = $this->makeDateField("closingdate", $closingdate, false);
		$date_date_js = $closingdateSet ? strftime('{ day:%e, month:%m, year:%Y }',$closingdate) : '0';
		$max_date_js = strftime('%m/%d/%Y',$this->event_obj['startdate']);
		
		$closingtime = $closingdateSet ? strftime('%H',$closingdate) : $this->closingDateDefaultHour;
		$date_code .= ', klokken '.$this->generateTimeField('closingdate_time', $closingtime);

		$errstr = "";
		if (isset($_SESSION['errors'])){
			$errors = $_SESSION['errors'];
			$errstr = "<ul>";
			foreach ($_SESSION['errors'] as $s){
				if (isset($this->errorMessages[$s]))
					$errstr.= "<li>".$this->errorMessages[$s]."</li>";
				else
					$errstr.= "<li>$s</li>";				
			}
			$errstr .= "</ul>";
			$errstr = $this->notSoFatalError($errstr,array('logError'=>false,'customHeader'=>'Påmeldingen ble ikke opprettet fordi:'));			
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);			
		}

		$r1a = array(); 				$r2a = array();
		$r1a[] = "%errors%";			$r2a[] = $errstr;
		$r1a[] = "%post_uri%";			$r2a[] = $this->generateUrl('action=editEnrolmentDo');
		$r1a[] = "%title%";				$r2a[] = 'Påmeldingsside for '.$this->event_obj['caption'];		
		$r1a[] = "%cancel_uri%";		$r2a[] = $this->generateUrl('');
		$r1a[] = "%closing_date%";		$r2a[] = $date_code;
		$r1a[] = "%date_date_js%";		$r2a[] = $date_date_js;
		$r1a[] = "%maxdate_js%";		$r2a[] = $max_date_js;
		$r1a[] = "%guestsallowed%";		$r2a[] = $guestsallowed;
		$r1a[] = "%def_yes%";			$r2a[] = $closingdateSet ? "checked='checked'" : "";
		$r1a[] = "%def_no%";			$r2a[] = $closingdateSet ? "" : "checked='checked'";

		return str_replace($r1a, $r2a, $this->template_editPaamelding);
	}
	
	private function editEnrolmentDo() {
		if (!$this->allow_addenrolment) return $this->permissionDenied();
		$id = $this->enrolment_id;
		
		$res = $this->query("SELECT calendar_id,event_id FROM $this->table_enrolments WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();
		$cal_id = $row['calendar_id'];
		$event_id = $row['event_id'];
		
		$errors = array();

		$closingdate = addslashes(sprintf('%04d-%02d-%02d %02d:00:00',
			intval($_POST['closingdate_year']),intval($_POST['closingdate_month']),intval($_POST['closingdate_day']),
			intval($_POST['closingdate_time_h'])
		));
		$closingdate_unix = strtotime($closingdate);
		if ($_POST['frist'] == 'nei') {
			$closingdate = '0000-00-00 00:00:00';
		} else {
			if (intval($_POST['closingdate_year'])<=0) {
				array_push($errors,'no_closingdate');
			} else {
				if ($closingdate_unix <= time()) {
					array_push($errors,'closingdate_inthepast');					
				}
				$ci = $this->cal_instance;
				$e = $ci->getEventDetails($event_id);
				if ($closingdate_unix > $e['startdate']) {
					array_push($errors,'closingdate_afterstart');					
				}

			}
		}
					
		$guestsallowed = 0;
		if (isset($_POST['guestsallowed']) && ($_POST['guestsallowed'] == 'on')) $guestsallowed = 1;

		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$this->redirect($this->generateURL('action=editEnrolment'));
		}
		
		
		$this->query("UPDATE $this->table_enrolments 
			SET closing_date=\"$closingdate\", guestsallowed=$guestsallowed
			WHERE id=$id" 
		);
		$this->addToActivityLog("oppdaterte <a href=\"".$this->generateCoolUrl("/$id")."\">påmeldingen</a> til ".$this->event_obj['caption'].".");
		
		$this->redirect($this->generateCoolUrl("/$id"),"Påmeldingen er oppdatert");		
	}
	
	private function deleteEnrolment() {
		if (!$this->allow_addenrolment) { $this->permissionDenied(); return; }
		$id = $this->enrolment_id;
		
		$res = $this->query("SELECT calendar_id,event_id,closing_date FROM $this->table_enrolments WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();		

		$r1a = array(); 				$r2a = array();
		$r1a[] = "%post_uri%";			$r2a[] = $this->generateUrl('action=deleteEnrolmentDo');
		$r1a[] = "%cancel_uri%";		$r2a[] = $this->generateUrl('');
		$r1a[] = "%event_name%";		$r2a[] = $this->event_obj['caption'];

		return str_replace($r1a, $r2a, $this->template_deletePaamelding);
	}
	
	private function deleteEnrolmentDo() {
		if (!$this->allow_addenrolment) return $this->permissionDenied(); 
		$id = $this->enrolment_id;
		
		$res = $this->query("SELECT calendar_id,event_id FROM $this->table_enrolments WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();
		$cal_id = $row['calendar_id'];
		$event_id = $row['event_id'];
		
		$this->query("DELETE FROM $this->table_enrolments WHERE id=$id");
		$this->query("DELETE FROM $this->table_enrolments_user WHERE enrolment=$id");
		
		$calLink = $this->cal_instance->getLinkToEvent($event_id, $this->event_obj['caption']);
		$this->addToActivityLog("fjernet påmeldingen til $calLink.");
		
		$this->redirect($this->generateCoolUrl("/"),"Påmeldingen ble fjernet");
	}
	
	private function emailForm() {

		$enrolment_id = $this->enrolment_id;		
		if (!is_numeric($enrolment_id)) $this->fatalError("invalid input");

		$dt_start = strftime('%A %e. %B %Y',$this->event_obj['startdate']);
		$dt_end = strftime('%A %e. %B %Y',$this->event_obj['enddate']);

		$people_and_parents = array();
		$res = $this->query("SELECT id,person,enrolledby,comment,cancelledby,guest_name,guest_email 
			FROM $this->table_enrolments_user WHERE enrolment=$enrolment_id");
				
		$ppl_list = array();
		
		while ($row = $res->fetch_assoc()) {
			if (!empty($row['person'])) {
				$m = call_user_func($this->lookup_member, $row['person']);
				if (empty($row['cancelledby'])){
					$people_and_parents[] = $row['person'];
					foreach ($m->guardians as $g) {
						$people_and_parents[] = $g;
					}
				}
			}
		}

		$dt_start = strftime('%A %e. %B %Y',$this->event_obj['startdate']);
		$dt_end = strftime('%A %e. %B %Y',$this->event_obj['enddate']);
		$dt = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";		
		$_SESSION['msgcenter_infomsg'] = "Mottakere er alle påmeldte til «".$this->event_obj['caption']."» ($dt) og foresatte til påmeldte. Evt. gjester er ikke inkludert.";
		
		$this->redirect($this->messageUrl."?recipients=".implode(",",$people_and_parents));
	}
	
	private function viewEnrolment() {
		global $memberdb;
		$id = $this->enrolment_id;		
		if (!is_numeric($id)) $this->fatalError("invalid input");
	
		$res = $this->query(
			"SELECT calendar_id, event_id, closing_date, guestsallowed 
			FROM $this->table_enrolments WHERE id=$id"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("Påmeldingen finnes ikke");
		$row = $res->fetch_assoc();
		
		$cal_id = $row['calendar_id'];
		$event_id = $row['event_id'];
		$guests_allowed = ($row['guestsallowed']==true);

		$calLink = $this->cal_instance->getLinkToEvent($event_id, '', true);
		$dt_start = strftime('%A %e. %B %Y',$this->event_obj['startdate']);
		$dt_end = strftime('%A %e. %B %Y',$this->event_obj['enddate']);
		$dt = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
		$output = '<h2>Påmeldingsside for '.$this->event_obj['caption'].'</h2>
		 '.ucfirst($dt).'. <span class="hidefromprint">Mer info finner du i <a href="'.$calLink.'" class="icn" style="background-image:url(/images/icns/calendar.png);">terminlisten</a>.</span>
		';
		
		$closingdate = ($row['closing_date'] == '0000-00-00 00:00:00') ? 0 : strtotime($row['closing_date']);
		$eventInTheFuture = ($this->event_obj['startdate'] > time());
		$enrolmentActive = ((empty($closingdate) && $eventInTheFuture) || $closingdate > time());
		
		$people_str = "";
		$people_ids = array();
		$people_and_parents = array();
		$res = $this->query("SELECT id,person,enrolledby,comment,cancelledby,guest_name,guest_email 
			FROM $this->table_enrolments_user WHERE enrolment=$id");
				
		$ppl_list = array();
		
		while ($row = $res->fetch_assoc()) {
			$enrolment_id = $row['id'];

			if (empty($row['person'])) {

				$str = $row['guest_name'].' ('.$row['guest_email'].') ';
				$r = call_user_func($this->lookup_member,$row['enrolledby']);
				$str .= ' <span style="color:#888;font-size:80%;">(påmeldt av '.$r->fullname.')</span>';				

				$people_str = "<li class='annet'>$str</li>\n";

				$kat = 'GU';
			
			} else {			

				$m = call_user_func($this->lookup_member, $row['person']);
				if (empty($row['cancelledby'])){
					$people_and_parents[] = $row['person'];
					$people_ids[] = $row['person'];
					foreach ($m->guardians as $g) {
						$people_and_parents[] = $g;
					}
				}

				$u = call_user_func($this->lookup_member,$row['person']);
				$url_details = $this->generateCoolUrl("/$id","action=viewRegistration&registrationId=$enrolment_id");
				$cancelled = ($row['cancelledby'] ? " style='text-decoration: line-through;'" : "");
				$str = '<a href="'.$url_details.'"'.$cancelled.'>'.$u->fullname.'</a>';
				if ($row['enrolledby'] != $row['person']) {
					$r = call_user_func($this->lookup_member,$row['enrolledby']);
					$str .= ' <span style="color:#888;font-size:80%;">(påmeldt av '.$r->fullname.')</span>';
				}

				$comment = stripslashes(nl2br($row['comment']));
				if (empty($row['cancelledby']) && $this->allow_viewcomments && !empty($comment)) 
					$str .= '<div class="smalltext" style="padding-left:10px;">'.$comment.'</div>';
	
				$people_str = "<li class='".$m->classname."'>$str</li>\n";
				
				$kat = $memberdb->getUserCategory($m->ident);
				if ($row['cancelledby']) $kat = 'AVM';
				
			}

			if (!isset($ppl_list[$kat])) $ppl_list[$kat] = array();
			$ppl_list[$kat][] = $people_str;
		}
		
		if ($this->allow_addenrolment) {
			if ($eventInTheFuture) {
				$output .= '
					<p class="headerLinks hidefromprint">
						<a href="'.$this->generateUrl('action=editEnrolment').'" class="edit">Innstillinger</a>
						<a href="'.$this->generateUrl('action=deleteEnrolment').'" class="delete">Slett påmelding</a><br />
					</p>
				';
			}
			if (count($people_and_parents) > 0) {
				$output .= '
				<p class="hidefromprint">
					<a href="'.$this->generateURL('action=emailForm').'" class="icn" style="background-image:url(/images/icns/email.png);">Send epost til påmeldte m/ foresatte</a>
				</p>
				';
			}
		}

		$str = "";
		
		if ($closingdate > 0) {
			
			$dt = getdate($closingdate);
			$dt2 = getdate(time());
			$daydiff = $dt['year']*365 + $dt['yday'] - $dt2['year']*365 - $dt2['yday']; 
			$diff = $closingdate - time();
			$str .= '<div style="background:url(/images/icns/error.png) 0px 1px no-repeat;padding:2px 2px 5px 20px;">';
			if ($diff > 0) {
				if ($daydiff <= 1) {
					$hours = floor($diff/3600);
					$min = floor(($diff%3600)/60);
					$daysleft = ($daydiff == 0) ? 'i dag' : 'i morgen';
					$diff = "<div style='padding-top:5px;padding-bottom:5px;color:#ee2222;'><strong>Påmeldingen stenger om $hours time".(($hours == 1) ? "":"r")." og $min minutter!</strong></div>";				
				} else {
					$diff = "<span style='color:#008800;'> (om $daydiff dag".(($daydiff == 1) ? "":"er").")</span>";
				}
			} else {
				if ($daydiff == 0) {
					$min = floor((-$diff%3600)/60);
					$diff = "<span style='color:#ff0000;'> (påmeldingen stengte for $min minutter siden)</span>";
				} else if ($daydiff == -1) {
					$diff = "<span style='color:#ff0000;'> (i går)</span>";
				} else {
					$diff = "<span style='color:#ff0000;'> (for ".(-$daydiff)." dager siden)</span>";				
				}
			}
			$str .= "
					Påmeldingsfrist: ".strftime('%A %e. %B %Y, kl. %H',$closingdate)." $diff
			";
			$str .= '</div>';
		}
		
		if ($this->isLoggedIn()) {
		
			$meEnrolled = false;
			
			$p = $this->login_identifier;
			$me = call_user_func($this->lookup_member,$p);
			$res2 = $this->query("SELECT id,person FROM $this->table_enrolments_user 
				WHERE enrolment=$id AND person=$p AND cancelledby=0");
			if ($res2->num_rows == 1) {
				$meEnrolled = true;
				$row2 = $res2->fetch_assoc();
				$meldav = $enrolmentActive ? "<a href=\"".$this->generateUrl('action=cancel&registrationId='.$row2['id'])."\">Meld meg av</a>" : ""; 
				$str .= '<div style="background:url(/images/icns/accept.png) 0px 2px no-repeat;padding:2px 2px 5px 20px;"> Du er påmeldt. '.$meldav.'</div>';
			} else {
				$meldpaa = $enrolmentActive ? "<a href=\"".$this->generateUrl('action=register&userId='.$p)."\" class=\"icn\" style=\"background-image:url(/images/icns/arrow_right.png);\">Meld meg på</a>" : ""; 
				$str .= '<div style="background:url(/images/icns/exclamation.png) 0px 2px no-repeat;padding:2px 2px 5px 20px;"> Du er ikke påmeldt '.$meldpaa.'</div>';
			}
			foreach ($me->guarded_by as $gu) {
				$pp = call_user_func($this->lookup_member,$gu);
				$res2 = $this->query("SELECT id,person FROM $this->table_enrolments_user WHERE enrolment=$id AND person=$gu AND cancelledby=0");
				if ($res2->num_rows == 1) {
					$row2 = $res2->fetch_assoc();
					$meldav = $enrolmentActive ? "<a href=\"".$this->generateUrl('action=cancel&registrationId='.$row2['id'])."\">Meld ".$pp->firstname." av</a>" : ""; 
					$str .= '<div style="background:url(/images/icns/accept.png) 0px 2px no-repeat;padding:2px 2px 5px 20px;"> '.$pp->fullname.' er påmeldt. '.$meldav.'</div>';
				} else {
					$meldpaa = $enrolmentActive ? "<a href=\"".$this->generateUrl('action=register&userId='.$gu)."\" class=\"icn\" style=\"background-image:url(/images/icns/arrow_right.png);\">Meld ".$pp->firstname." på</a>" : ""; 
					$str .= '<div style="background:url(/images/icns/exclamation.png) 0px 2px no-repeat;padding:2px 2px 5px 20px;"> '.$pp->fullname.' er ikke påmeldt '.$meldpaa.'</div>';
				}
			}
			
			if ($enrolmentActive && $this->allow_enrollall) {
				$str .= '<a href="'.$this->generateUrl('action=register').'" class="icn" style="background-image:url(/images/icns/user_add.png);">Meld på andre</a>';
			}
			
			if ($meEnrolled && $enrolmentActive && $guests_allowed) {
		
				$str .= '
					<div style="padding-top:10px;">
						Ønsker du å ta med gjester på dette arrangementet, f.eks. søsken?
						<div id="guest_list">
							'.$this->listGuests().'
						</div>
					</div>
				';
			}
			
		}
		if ($this->isLoggedIn() || $closingdate > 0) {
			$output .= "
				<div class='whiteInfoBox hidefromprint'>
					<div class='inner'>
						$str
					</div>
				</div>";
		}
		
		if (!$this->isLoggedIn() && $enrolmentActive) {
		
			$output .= '
				<div>&nbsp;</div>
				<div class="whiteInfoBox">
					<div class="inner" style="background:url('.$this->image_dir.'icns/lock.png) no-repeat 25px 21px;">
						<p>
							Dette arrangementet er åpent for påmelding, 
							men for å melde deg på må du være innlogget.
						</p>
						<p>
							Dersom du ikke har en brukerkonto på nettsiden vår er du velkommen
							til å <a href="/registrering" class="icn" style="background-image:url('.$this->image_dir.'icns/arrow_right.png);">registrere deg her</a>. 
						</p>
					</div>				
				</div>			
			';
		
		}
		

		$res = $this->query("SELECT COUNT(id) FROM $this->table_enrolments_user WHERE enrolment='$id' AND cancelledby=0");
		$row = $res->fetch_row();
		$paameldte = intval($row[0]);
		$res = $this->query("SELECT COUNT(id) FROM $this->table_enrolments_user WHERE enrolment='$id' AND cancelledby!=0");
		$row = $res->fetch_row();
		$avmeldte = intval($row[0]);

		if ($this->isLoggedIn()) {

			if ($paameldte == 0 && $avmeldte == 0) {
				$output .= "<p><i>Ingen påmeldte enda</i></p>";
			} else {
				$output .= "<h3>Totalt $paameldte påmeldte og $avmeldte avmeldte:</h3>\n";
				$output .= $this->outputEnrolled($ppl_list);
			}
		
		}
		
		$output .= '
			<script type="text/javascript">
			//<![CDATA[
				YAHOO.util.Event.onDOMReady(function() {
					Nifty("div.whiteInfoBox");
				});
			//]]>
			</script>
		';
				
		return $output;
	}
	
	
	private function viewRegistration() {
		$id = intval($_GET['registrationId']);
		if ($id <= 0) $this->fatalError("invalid input");

		$output = "";
		$e = $this->table_enrolments;
		$u = $this->table_enrolments_user;
		$res = $this->query("SELECT 
				$e.id,
				$e.calendar_id,
				$e.event_id,
				$e.closing_date,
				$u.person,
				$u.enrolledby,
				$u.comment,
				$u.enrolldate,
				$u.cancelledby,
				$u.canceldate,
				$u.cancelcomment
			FROM $e,$u 
			WHERE $u.id=$id AND $u.enrolment=$e.id"
		);
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();
		$enrolment_id = $row['id'];
		$cal_id = $row['calendar_id'];
		$event_id = $row['event_id'];

		$closingdate = ($row['closing_date'] == '0000-00-00 00:00:00') ? 0 : strtotime($row['closing_date']);
		$eventInTheFuture = ($this->event_obj['startdate'] > time());
		$enrolmentActive = ((empty($closingdate) && $eventInTheFuture) || $closingdate > time());

		$calLink = $this->cal_instance->getLinkToEvent($event_id, "Mer informasjon om dette arrangementet");
		$m = call_user_func($this->lookup_member,$row['person']);
		$mLink = call_user_func($this->make_memberlink,$row['person']);
		$r = call_user_func($this->lookup_member,$row['enrolledby']);
		$rLink = call_user_func($this->make_memberlink,$row['enrolledby']);
		if (!empty($row['cancelledby'])) $cLink = call_user_func($this->make_memberlink,$row['cancelledby']);
		if ($row['cancelledby'] != 0) $c = call_user_func($this->lookup_member,$row['cancelledby']);
		$output .="<h2>".$this->event_obj['caption'].": ".$m->fullname."</h2>";
		if ($row['person'] == $row['enrolledby']) 
			$output .="<p>$mLink meldte seg på den ".date("d M Y",$row['enrolldate'])."</p>";
		else
			$output .="<p>$mLink ble meldt på av $rLink den ".date("d M Y",$row['enrolldate'])."</p>";
			
		$comment = nl2br(stripslashes($row['comment']));
		if ($this->allow_viewcomments && !empty($comment)) $output .="<em>Kommentar:</em> $comment";

		if (!empty($row['cancelledby'])) {
			if ($row['person'] == $row['cancelledby']) 
				$output .="<p>$mLink meldte seg av den ".date("d M Y",$row['canceldate'])."</p>";
			else
				$output .="<p>$mLink ble meldt av av $cLink den ".date("d M Y",$row['canceldate'])."</p>";
			
			$comment = nl2br(stripslashes($row['cancelcomment']));
			if ($this->allow_viewcomments && !empty($comment)) $output .="<em>Kommentar:</em> $comment";
			
			$output .='<p><a href="'.$this->generateUrl('action=reRegister&registrationId='.$id).'">Meld på igjen</a></p>';

		} else {
			if ($enrolmentActive) {
				$output .='<p><img src="'.$this->image_dir.'false.gif" /> <a href="'.$this->generateUrl('action=cancel&registrationId='.$id).'">Meld av</a></p>';
			}
		}
		
		call_user_func(
			$this->add_to_breadcrumb,
			'<a href="'.$this->generateCoolUrl("/$enrolment_id","action=viewRegistration&registrationId=$id").'">'.$m->fullname.'</a>'
		);
		
		return $output;		
	}

	private function register() {
		global $memberdb;

		$id = $this->enrolment_id;
		$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;

		if (!$this->allow_enroll) return $this->permissionDenied(); 
		if (!isset($this->login_identifier)) return $this->permissionDenied(); 
		if (!is_numeric($this->login_identifier)) return $this->permissionDenied();
		if (!is_numeric($id)) $this->fatalError("invalid input");		
		if ($userId > 0 && !$this->allowEnrollPerson($userId)) return $this->permissionDenied(); 
		
		$res = $this->query("SELECT calendar_id,event_id,closing_date FROM $this->table_enrolments WHERE id=$id");
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();

		$closingdate = ($row['closing_date'] == '0000-00-00 00:00:00') ? 0 : strtotime($row['closing_date']);
		$eventInTheFuture = ($this->event_obj['startdate'] > time());
		$enrolmentActive = ((empty($closingdate) && $eventInTheFuture) || $closingdate > time());
		if (!$enrolmentActive) {
			return $this->notSoFatalError("Beklager, påmeldingen for dette arrangementet er stengt.");
		}
		
		$cal_id = $row['calendar_id'];
		$event_id = $row['event_id'];
		
		$output = "<h2>".$this->event_obj['caption']."</h2>";
		
		$urlPost = $this->generateUrl('action=registerDo');
		
		$me = call_user_func($this->lookup_member,$this->login_identifier);
		
		if ($this->allow_enrollall) {
		
			if ($userId > 0) 
				$optn = $memberdb->generateMemberSelectBox("who", $userId);
			else
				$optn = $memberdb->generateMemberSelectBox("who");
			
			$optn .= "<p class='notice'>Som leder har du mulighet til å melde på hvem som helst, men
			du står selvsagt ansvarlig hvis noen mener seg feilaktig påmeldt. Navnet ditt knyttes
			derfor til påmeldingen.</p>";

			
		} else {
		
			$ppl = array($me);
			foreach ($me->guarded_by as $gu) {
				$ppl[] = call_user_func($this->lookup_member,$gu);
			}
			$optn = "";
			foreach ($ppl as $p) {
				if ($userId > 0) {
					if ($p->ident == $userId) {
						$check = "checked='checked'";
					}
				}
				$check = (count($ppl) == 1) ? "checked='checked'" : "";
				$optn .= "
					<label for='who$p->ident'>
						<input type='radio' name='who' id='who$p->ident' value='$p->ident' $check />
						$p->fullname
					</label>
				";
			}
			
		}
		$output .= '
			<form method="post" action="'.$urlPost.'" id="regschema">
				<input type="hidden" name="enrolment" value="'.$id.'" />
				<p>
					Jeg ønsker å melde på følgende person til dette arrangementet:
				</p>
				<p>
					'.$optn.'
				</p>
				<p>
				Eventuelle kommentarer til påmeldingen: <br />
				<textarea cols="60" rows="6" name="comment"></textarea>
				</p> 
				<input type="button" value="Avbryt" onclick=\'window.location="'.$this->generateUrl('').'"\' />
				<input type="submit" value="Meld på" />
			</form>
		';
		
		return $output;
	}	
	private function registerDo() {
		global $memberdb;
		if (!$this->allow_enroll) return $this->permissionDenied(); 
		if (!isset($this->login_identifier)) return $this->permissionDenied(); 
		if (!is_numeric($this->login_identifier)) return $this->permissionDenied(); 
		
		$enrolment_id = $_POST['enrolment'];
		if (!is_numeric($enrolment_id)) $this->fatalError("invalid inp .1");
		$enrolment_id = intval($enrolment_id);

		$res = $this->query("SELECT calendar_id,event_id,closing_date FROM $this->table_enrolments WHERE id=$enrolment_id");
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();

		$closingdate = ($row['closing_date'] == '0000-00-00 00:00:00') ? 0 : strtotime($row['closing_date']);
		$eventInTheFuture = ($this->event_obj['startdate'] > time());
		$enrolmentActive = ((empty($closingdate) && $eventInTheFuture) || $closingdate > time());
		if (!$enrolmentActive) {
			print $this->notSoFatalError("Beklager, påmeldingen for dette arrangementet er stengt.");
			exit();
		}
		
		$cal_id = $row['calendar_id'];
		$event_id = $row['event_id'];
		$calCaption = $this->event_obj['caption'];

		$person = $_POST['who'];
		if (!is_numeric($person)) $this->fatalError("Du må velge personen du ønsker å melde på!");
		$responsible = $this->login_identifier;
		$enrolldate = time();
		$comment = addslashes($_POST['comment']);
		
		if (!$this->allowEnrollPerson($person)) return $this->permissionDenied();
		
		$res = $this->query("SELECT id,cancelledby FROM $this->table_enrolments_user WHERE enrolment=$enrolment_id AND person=$person");
		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			$eid = $row['id'];
			if ($row['cancelledby'] == 0) {
				$this->redirect($this->generateCoolUrl("/$enrolment_id"),"Personen er allerede påmeldt dette arrangementet!");		
			}
			$this->query("UPDATE $this->table_enrolments_user
				SET cancelledby=0,
					canceldate=0,
					cancelcomment=\"\",
					enrolldate=$enrolldate,
					enrolledby=$responsible
				WHERE id=$eid"
			);
			
			$this->mailReceipt($eid);
			
			if ($person == $this->login_identifier) {
				$this->addToActivityLog("meldte seg på <a href=\"".$this->generateCoolUrl("/$enrolment_id")."\">$calCaption</a>");
				$this->redirect($this->generateCoolUrl("/$enrolment_id"),"Du er nå påmeldt dette arrangementet!");
			} else {
				$this->addToActivityLog("meldte ".call_user_func($this->make_memberlink,$person)." på <a href=\"".$this->generateCoolUrl("/$enrolment_id")."\">$calCaption</a>");		
				$this->redirect($this->generateCoolUrl("/$enrolment_id"),$memberdb->getMemberById($person)->fullname." er nå påmeldt dette arrangementet!");
			}			
		}
		
		$this->query("INSERT INTO $this->table_enrolments_user 
			(enrolment,person,enrolledby,comment,enrolldate) 
			VALUES ($enrolment_id,$person,$responsible,\"$comment\",$enrolldate)"
		);
		
		$id = $this->insert_id();
		$this->mailReceipt($id);

		if ($person == $this->login_identifier) {
			$this->addToActivityLog("meldte seg på <a href=\"".$this->generateCoolUrl("/$enrolment_id")."\">$calCaption</a>");
			$this->redirect($this->generateCoolUrl("/$enrolment_id"),"Du er nå påmeldt dette arrangementet!");
		} else {
			$this->addToActivityLog("meldte ".call_user_func($this->make_memberlink,$person)." på <a href=\"".$this->generateCoolUrl("/$enrolment_id")."\">$calCaption</a>");		
			$this->redirect($this->generateCoolUrl("/$enrolment_id"),$memberdb->getMemberById($person)->fullname." er nå påmeldt dette arrangementet!");
		}

	}
	
	private function cancel() {		
		if (!$this->allow_enroll) return $this->permissionDenied(); 
		$registrationId = intval($_GET['registrationId']);
		if ($registrationId <= 0) $this->fatalError("invalid input");

		$e = $this->table_enrolments;
		$u = $this->table_enrolments_user;
		$res = $this->query("SELECT 
				$e.id as enrolment_id,
				$e.closing_date,
				$u.person
			FROM $e,$u 
			WHERE $u.id=$registrationId AND $u.enrolment=$e.id"
		);
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();

		$closingdate = ($row['closing_date'] == '0000-00-00 00:00:00') ? 0 : strtotime($row['closing_date']);
		$eventInTheFuture = ($this->event_obj['startdate'] > time());
		$enrolmentActive = ((empty($closingdate) && $eventInTheFuture) || $closingdate > time());
		if (!$enrolmentActive) {
			return $this->notSoFatalError("Beklager, påmeldingen for dette arrangementet er stengt.");
		}

		$enrolment_id = intval($row['enrolment_id']);
		if ($enrolment_id != $this->enrolment_id) $this->fatalError("This registration does not belong to this enrolment!");

		$personId = intval($row['person']);
		if (!$this->allowEnrollPerson($personId)) return $this->permissionDenied(); 
		$u = call_user_func($this->lookup_member,$personId);
	
		$r1a = array(); 				$r2a = array();
		$r1a[] = "%post_uri%";			$r2a[] = $this->generateUrl('action=cancelDo');
		$r1a[] = "%fullname%";			$r2a[] = $u->fullname;
		$r1a[] = "%eventname%";			$r2a[] = $this->event_obj['caption'];
		$r1a[] = "%cancel_uri%";		$r2a[] = $this->generateUrl('');
		$r1a[] = "%registrationId%";	$r2a[] = $registrationId;

		return str_replace($r1a, $r2a, $this->template_cancelEnrolment);
	}
	
	private function cancelDo() {
		global $memberdb;	
		if (!$this->isLoggedIn()) return $this->permissionDenied(); 
		if (!$this->allow_enroll) return $this->permissionDenied(); 
		$registrationId = intval($_POST['registrationId']);
		if ($registrationId <= 0) $this->fatalError("invalid input");


		$res = $this->query("SELECT closing_date FROM $this->table_enrolments WHERE id=$this->enrolment_id");
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();
		$closingdate = ($row['closing_date'] == '0000-00-00 00:00:00') ? 0 : strtotime($row['closing_date']);
		$eventInTheFuture = ($this->event_obj['startdate'] > time());
		$enrolmentActive = ((empty($closingdate) && $eventInTheFuture) || $closingdate > time());
		if (!$enrolmentActive) {
			print $this->notSoFatalError("Beklager, påmeldingen for dette arrangementet er stengt.");
			exit();
		}
				
		$responsible = $this->login_identifier;
		$canceldate = time();
		$comment = addslashes($_POST['comment']);
		
		$res = $this->query("SELECT id,person,enrolment,cancelledby FROM $this->table_enrolments_user WHERE id=$registrationId");
		if ($res->num_rows != 1) {
			$this->fatalError("eksisterer ikke -1");
		}
		$row = $res->fetch_assoc();
		$enrolment_id = intval($row['enrolment']);
		if ($enrolment_id != $this->enrolment_id) $this->fatalError("This registration does not belong to this enrolment!");
		$person = intval($row['person']);
		if (!$this->allowEnrollPerson($person)) return $this->permissionDenied(); 
		
		$res = $this->query("SELECT calendar_id,event_id FROM $this->table_enrolments WHERE id=$enrolment_id");
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();
		
		$calCaption = $this->event_obj['caption'];

		$this->query("UPDATE $this->table_enrolments_user
			SET cancelledby=$responsible,
				canceldate=$canceldate,
				cancelcomment=\"$comment\"
			WHERE id=$registrationId"
		);
		
		$this->mailReceiptCancel($registrationId);

		if ($person == $this->login_identifier) {
			$this->addToActivityLog("meldte seg av <a href=\"".$this->generateUrl('')."\">$calCaption</a>");
			$this->redirect($this->generateUrl(''),"Du er nå avmeldt dette arrangementet!");
		} else {
			$this->addToActivityLog("meldte ".call_user_func($this->make_memberlink,$person)." av <a href=\"".$this->generateUrl('')."\">$calCaption</a>");		
			$this->redirect($this->generateUrl(''),$memberdb->getMemberById($person)->fullname." er nå avmeldt dette arrangementet!");
		}
	
	}
	
	private function reRegister() {		
		if (!$this->allow_enroll) return $this->permissionDenied();

		$registrationId = intval($_GET['registrationId']);
		if ($registrationId <= 0) $this->fatalError("invalid input");

		$e = $this->table_enrolments;
		$u = $this->table_enrolments_user;
		$res = $this->query("SELECT 
				$e.id as enrolment_id,
				$e.calendar_id,
				$e.closing_date
				$e.event_id,
				$u.person,
				$u.enrolledby,
				$u.comment,
				$u.enrolldate,
				$u.cancelledby,
				$u.canceldate
			FROM $e,$u 
			WHERE $u.id=$registrationId AND $u.enrolment=$e.id"
		);
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();

		$closingdate = ($row['closing_date'] == '0000-00-00 00:00:00') ? 0 : strtotime($row['closing_date']);
		$eventInTheFuture = ($this->event_obj['startdate'] > time());
		$enrolmentActive = ((empty($closingdate) && $eventInTheFuture) || $closingdate > time());
		if (!$enrolmentActive) {
			print $this->notSoFatalError("Beklager, påmeldingen for dette arrangementet er stengt.");
			exit();
		}

		$person = intval($row['person']);
		if (!$this->allowEnrollPerson($person)) return $this->permissionDenied(); 
				
		$calCaption = $this->event_obj['caption'];
		$enrolment_id = intval($row['enrolment_id']);
		if ($enrolment_id != $this->enrolment_id) $this->fatalError("This registration does not belong to this enrolment!");

		$u = call_user_func($this->lookup_member,$person);
	
		if (!isset($this->login_identifier)) return $this->permissionDenied(); 
		if (!is_numeric($this->login_identifier)) return $this->permissionDenied(); 
		
		$responsible = $this->login_identifier;
		$enrolldate = time();
		
		$res = $this->query("SELECT id,enrolment,cancelledby FROM $this->table_enrolments_user WHERE id=$registrationId");
		if ($res->num_rows != 1) {
			$this->fatalError("eksisterer ikke -1");
		}
		$row = $res->fetch_assoc();
		$enrolment_id = $row['enrolment'];
		
		$this->query("UPDATE $this->table_enrolments_user
			SET cancelledby=0,
				canceldate=0,
				cancelcomment=\"\",
				enrolldate=$enrolldate,
				enrolledby=$responsible
			WHERE id=$registrationId"
		);
		
		$this->mailReceipt($registrationId);

		if ($person == $this->login_identifier) {
			$this->addToActivityLog("meldte seg på <a href=\"".$this->generateCoolUrl("/$enrolment_id")."\">$calCaption</a>");
			$this->redirect($this->generateCoolUrl("/$enrolment_id"),"Du er nå påmeldt dette arrangementet!");
		} else {
			$this->addToActivityLog("meldte ".call_user_func($this->make_memberlink,$person)." på <a href=\"".$this->generateCoolUrl("/$enrolment_id")."\">$calCaption</a>");		
			$this->redirect($this->generateCoolUrl("/$enrolment_id"),$memberdb->getMemberById($person)->fullname." er nå påmeldt dette arrangementet!");
		}	
	}	
	
	
	/*
		ajaxAddGuest
		AJAX function
	*/
	private function ajaxAddGuest() {
		header("Content-Type: text/html; charset=utf-8"); 
		print $this->listGuests(true);
		exit();
	}

	/*
		Function: ajaxEditGuest
		AJAX function
	*/
	private function ajaxEditGuest() {
		header("Content-Type: text/html; charset=utf-8"); 
		print $this->listGuests(true);
		exit();

	}
	
	private function ajaxSaveGuest() {

		if (!isset($this->login_identifier)) return $this->permissionDenied(); 
		$me = $this->login_identifier;
		
		$guest_id = intval($_POST['guest_id']);
		$guest_firstname = addslashes($_POST['fornavn']);
		$guest_lastname = addslashes($_POST['etternavn']);
		$guest_email = addslashes($_POST['epost']);

		$eid = $this->enrolment_id;
		if ($guest_id) {
			$this->query(
				"UPDATE $this->table_enrolments_user SET
					guest_name = \"guest_firstname $guest_lastname\",
					guest_email = \"$guest_email\"
				WHERE id = '$guest_id'"
			);
		} else {
			$this->query("INSERT INTO $this->table_enrolments_user 
				(enrolment,enrolledby,enrolldate,guest_name,guest_email)
				VALUES ($eid,\"$me\",\"".time()."\",\"$guest_firstname $guest_lastname\",\"$guest_email\")");
		}
		
		
		$this->ajaxListGuests();
	}
	
	private function ajaxRemoveGuest() {

		if (!$this->isLoggedIn()) return $this->permissionDenied(); 
		$me = $this->login_identifier;
		
		$guest_id = intval($_GET['guestId']);
		if (!is_numeric($guest_id) OR intval($guest_id) <= 0) $this->fatalError('invalid guest');

		$this->query(
			"DELETE FROM $this->table_enrolments_user WHERE id='$guest_id' AND enrolledby='$me'"
		);
		
		$this->ajaxListGuests();		
	}

	private function ajaxListGuests() {
		header("Content-Type: text/html; charset=utf-8"); 
		print $this->listGuests();
		exit();
	}
	
	/* ========================================================================================
		7. Utility methods for use by other methods in this class:
	   ======================================================================================== */

	function generateTimeField($identifier, $value = "00", $className = ""){

		$bfr = '<select name="'.$identifier.'_h" size="1" class="'.$className.'">'."\n";
		for ($i = 0; $i <= 23; $i++){
			$h = sprintf('%02d',$i);
			$selc = ($value == $h) ? $selc = ' selected="selected"' : '';
			$bfr .= "  <option value='$h'$selc>$h</option>\n";
		}
		$bfr .= "</select>";
		
		return $bfr;
	}
	
	private function listCalendars() {
		
		$lang = $this->preferred_lang;
		$calendars = array();
		$res = $this->query("SELECT id,caption,color
			FROM $this->table_calendars 
		");
		while ($row = $res->fetch_assoc()) {
			$calendars[intval($row['id'])] = array('caption' => $row['caption'], 'color' => $row['color']);
		}
		return $calendars;
		
	}
	
	private function outputEnrolled($l) {
		global $memberdb;
		$output = "<ul class='custom_icons'>
		";
		foreach ($l as $n => $v) {
			
			switch ($n) {
				case 'AVM': $kat = 'Avmeldte'; break;
				case 'GU': $kat = 'Gjester'; break;
				default: $kat = $memberdb->getCategoryByAbbr($n);
			}
			if (is_array($v)) $output .= "  <li class='group'><strong>$kat</strong>: ".$this->outputEnrolled($v)."</li>
			";
			else $output .= "  $v
			";
		}
		$output .= "</ul>";
		return $output;
	}
	
	private function getCalPage($enrolment_id) {
		$e = $this->table_enrolments;
		$c = $this->table_calendars;
		$res = $this->query("SELECT 
				$c.default_cal_page as defcpage,
				$e.cal_page as cpage
			FROM $e,$c 
			WHERE $e.id='$enrolment_id' AND $e.calendar_id=$c.id"
		);
		$row = $res->fetch_assoc();
		if (!empty($row['cpage'])) return intval($row['cpage']);
		else return intval($row['defcpage']);
	}
	

	private function allowEnrollPerson($person) {
		if (!$this->allow_enroll) return false;
		$responsible = $this->login_identifier;
		if ($person != $responsible) {
			if (!$this->allow_enrollall) {
				$me = call_user_func($this->lookup_member,$responsible);				
				$allowEnroll = false;
				foreach ($me->guarded_by as $gu) {
					$pp = call_user_func($this->lookup_member,$gu);
					if ($pp->ident == $person) $allowEnroll = true;
				}
				if (!$allowEnroll) {
					return false;
				}
			}
		}
		return true;
	}

	/*
		Function: makeEditGuestForm
		Utility function
	*/
	private function makeEditGuestForm($enrolment_id) {
		$guest_id = isset($_GET['edit_guest']) ? intval($_GET['edit_guest']) : 0; 
		if (!is_numeric($guest_id)) $this->fatalError("invalid input .1");
		
		if ($guest_id == 0) {
			
			$guest_firstname = '';
			$guest_lastname = '';
			$guest_email = '';
			
		} else {
		
			$res = $this->query(
				"SELECT guest_firstname,guest_lastname,guest_email
				FROM $this->table_enrolments_user
				WHERE id = '$guest_id'"
			);
			if ($res->num_rows != 1) $this->fatalError("Gjesten eksisterer ikke.");
			$row = $res->fetch_assoc();
		
			$guest_firstname = stripslashes($row['guest_firstname']);
			$guest_lastname = stripslashes($row['guest_lastname']);
			$guest_email = stripslashes($row['guest_email']);

		}

		$url_post_js = $this->generateURL('action=ajaxSaveGuest');
		$url_post_js = 'AjaxFormSubmit("guest_list","'.ROOT_DIR.$url_post_js.'",this); return false;';
		
		$url_cancel_js = $this->generateURL('action=ajaxListGuests');
		$url_cancel_js = 'AjaxRequestData("guest_list","'.ROOT_DIR.$url_cancel_js.'"); return false;';
					
		return '
			<form method="post" action="#" onsubmit=\''.$url_post_js.'\'>
				<input type="hidden" name="guest_id" value="'.$guest_id.'" />
				Fornavn: <input type="text" name="fornavn" value="'.$guest_firstname.'" /><br />
				Etternavn: <input type="text" name="etternavn" value="'.$guest_lastname.'" /><br />
				E-post: <input type="text" name="epost" value="'.$guest_email.'" /><br />
				<input type="submit" value="Lagre" /> 
				<input type="button" name="cancel" value="Avbryt" onclick=\''.$url_cancel_js.'\' />
			</form>
		';
		
	}
	
	private function listGuests($includeBlank = false) {
		if (!isset($this->login_identifier)) return $this->permissionDenied(); 
		$enrolment_id = intval($this->enrolment_id);
		$me = $this->login_identifier;
		$res = $this->query("SELECT id,guest_name,guest_email FROM $this->table_enrolments_user WHERE enrolment=$enrolment_id AND guest_name!='' AND enrolledby='$me'");
		$outp = "<ol id='guest_list_ul'>\n";
		while ($row = $res->fetch_assoc()) {
			$url_remove_js = $this->generateURL(array('action=ajaxRemoveGuest','guestId='.$row['id']),true);
			$url_remove_js = "AjaxRequestData(\"guest_list\",\"$url_remove_js\"); return false;";
			$outp .= sprintf("<li><div id='guest_%d'> %s (%s) [<a href='#' onclick='$url_remove_js'>Fjern</a>]</li>\n",$row['id'],stripslashes($row['guest_name']),stripslashes($row['guest_email']));
		}
		if ($includeBlank) {
			$outp .= '<li><div id="guest_0">'.$this->makeEditGuestForm($enrolment_id).'</div></li>';
		}
		$outp .= "</ol>\n";
		
		if (!$includeBlank) {
			$url_add_js = $this->generateURL('action=ajaxAddGuest');
			$url_add_js = "AjaxRequestData(\"guest_list\",\"$url_add_js\"); return false;";
			$outp .= "<p><a href='#' onclick='$url_add_js' class='icn' style='background-image:url(/images/icns/user_add.png);'>Registrér ny gjest</a></p>";
		}
		
		return $outp;
	}
	
	
	private function mailReceipt($enrolment){
		
		require_once("../htmlMimeMail5/htmlMimeMail5.php");

		$from_name = $this->mailSenderName; // global options
		$from_addr = $this->mailSenderAddr; // global options
		

		$res = $this->query("SELECT 
				$this->table_enrolments.id,
				$this->table_enrolments_user.person,
				$this->table_enrolments_user.enrolldate,
				$this->table_enrolments_user.comment
			FROM $this->table_enrolments,$this->table_enrolments_user 
			WHERE $this->table_enrolments_user.id=$enrolment
			AND $this->table_enrolments_user.enrolment=$this->table_enrolments.id"
		);
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();
		$id = $row['id'];
		$deltaker = call_user_func($this->lookup_member,$row['person']);		
		
		$caption = $this->event_obj['caption'];
		$dt_start = date("j.n.y",$this->event_obj['startdate']);
		$dt_end = date("j.n.y",$this->event_obj['enddate']);
		$dt = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
		$caption = "$caption ($dt)";
		
		$enrolldate = date("j. ",$row['enrolldate']).
		$this->months[date("n",$row['enrolldate'])-1].
		date(" Y",$row['enrolldate']);
		
		$mailto = array();
		if (!empty($deltaker->email)) $mailto[] = $deltaker;
		foreach ($deltaker->guardians as $g) {
			$gg = call_user_func($this->lookup_member,$g);
			if (!empty($gg->email)) $mailto[] = $gg;
		}
		
		$comments = $row['comment'];
		if (!empty($comments)) {
			$comments = " Kommentarer: ".$comments."\n";
		}
		foreach ($mailto as $u) {
			
			$to_name = $u->fullname;
			$to_addr = $u->email;
			$recipients = array($to_name => $to_addr);
		
			$server = "http://".$_SERVER['SERVER_NAME'];
		
			$plainBody = "$u->firstname,

Dette er en kvittering på at 18. Bergen har registrert følgende påmelding:

 Aktivitet: $caption
 Deltaker: ".$deltaker->fullname."
 Påmeldt den: ".$enrolldate."
$comments
Mer informasjon om påmeldingen og arrangementet finner du her:
".$server.$this->generateCoolUrl("/$id")."

Husk å følge med på nettsiden vår for oppdatert informasjon om arrangementet.

-- 
mvh
$this->site_name
$server/
";

			// Send mail		
			$mail = new htmlMimeMail5();
			$mail->setFrom("$from_name <$from_addr>");
			$mail->setReturnPath($from_addr);
			$mail->setSubject("Kvittering for påmelding til ".$this->event_obj['caption']);
			$mail->setText($plainBody);
			//$mail->setHTML($htmlBody);
			$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
			$mail->send($recipients,$type = 'smtp');		
		}
	}
	
	private function mailReceiptCancel($enrolment){
		
		require_once("../htmlMimeMail5/htmlMimeMail5.php");

		$from_name = $this->mailSenderName; // global options
		$from_addr = $this->mailSenderAddr; // global options
		

		$res = $this->query("SELECT 
				$this->table_enrolments.id,
				$this->table_enrolments_user.person,
				$this->table_enrolments_user.enrolldate,
				$this->table_enrolments_user.canceldate,
				$this->table_enrolments_user.cancelcomment
			FROM $this->table_enrolments,$this->table_enrolments_user 
			WHERE $this->table_enrolments_user.id=$enrolment
			AND $this->table_enrolments_user.enrolment=$this->table_enrolments.id"
		);
		if ($res->num_rows != 1) $this->fatalError("invalid entry");
		$row = $res->fetch_assoc();
		$id = $row['id'];
		$deltaker = call_user_func($this->lookup_member,$row['person']);		
		
		$caption = $this->event_obj['caption'];
		$dt_start = date("j.n.y",$this->event_obj['startdate']);
		$dt_end = date("j.n.y",$this->event_obj['enddate']);
		$dt = ($dt_start == $dt_end) ? $dt_start : "$dt_start - $dt_end";
		$caption = "$caption ($dt)";
		
		$enrolldate = date("j. ",$row['enrolldate']).
			$this->months[date("n",$row['enrolldate'])-1].
			date(" Y",$row['enrolldate']);
		$canceldate = date("j. ",$row['canceldate']).
			$this->months[date("n",$row['canceldate'])-1].
			date(" Y",$row['canceldate']);
		
		$mailto = array();
		if (!empty($deltaker->email)) $mailto[] = $deltaker;
		foreach ($deltaker->guardians as $g) {
			$gg = call_user_func($this->lookup_member,$g);
			if (!empty($gg->email)) $mailto[] = $gg;
		}
		
		$comments = $row['cancelcomment'];
		if (!empty($comments)) {
			$comments = " Kommentarer: ".$comments."\n";
		}
		foreach ($mailto as $u) {
			
			$to_name = $u->fullname;
			$to_addr = $u->email;
			$recipients = array($to_name => $to_addr);
		
			$server = "http://".$_SERVER['SERVER_NAME'];
		
			$plainBody = "$u->firstname,

Dette er en kvittering på at 18. Bergen har registrert følgende avmelding:

 Aktivitet: $caption
 Deltaker: ".$deltaker->fullname."
 Påmeldt den: ".$enrolldate."
 Avmeldt den: ".$canceldate."
$comments
Mer informasjon om arrangementet finner du her:
".$server.$this->generateCoolUrl("/$id")."

-- 
mvh
$this->site_name
$server/
";

			// Send mail		
			$mail = new htmlMimeMail5();
			$mail->setFrom("$from_name <$from_addr>");
			$mail->setReturnPath($from_addr);
			$mail->setSubject("Kvittering for avmelding til ".$this->event_obj['caption']);
			$mail->setText($plainBody);
			//$mail->setHTML($htmlBody);
			$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
			$mail->send($recipients,$type = 'smtp');				
		}
	}
	
}

?>
