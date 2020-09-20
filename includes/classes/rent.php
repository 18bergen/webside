<?php
class rent extends base {

	var $getvars = array();

	var $table_calendar = "rent_calendar";
	var $table_halls = "rent_halls";
	
	var $allow_send_application = false;
	var $allow_approve = false;
	
	function __construct() {
		$this->table_calendar = DBPREFIX.$this->table_calendar;
		$this->table_halls = DBPREFIX.$this->table_halls;
	}
	
	function initialize(){
	
		$this->initialize_base();
		
		if (count($this->coolUrlSplitted) > 0) 
			$this->action = $this->coolUrlSplitted[0];
		else 
			$this->action = "";
	
	}
	
	function run(){
		$this->initialize();

		$oldlocale = setlocale(LC_TIME, 0);
		setlocale(LC_TIME, 'norwegian');

		switch ($this->action) {

			case 'kalender':
				if (isset($_GET['hall']))
					$this->outputCal(intval($_GET['hall']));
				break;

			case 'listapp':
				$this->listApplications();
				break;

			case 'approve-applications':
				$this->approveApplications();
				break;

			case 'info':
				if (isset($_GET['hall']) && isset($_GET['year']) && isset($_GET['month']) && isset($_GET['day']))
					$this->displayDay(intval($_GET['hall']),intval($_GET['year']),intval($_GET['month']),intval($_GET['day']));
				break;

			case 'send-application':
				if (isset($_POST['hall']) && isset($_POST['year']) && isset($_POST['month']) && isset($_POST['day']))
					$this->sendApplication(intval($_POST['hall']),intval($_POST['year']),intval($_POST['month']),intval($_POST['day']));
				break;
							
			/******* DEFAULT *******/
			
			default:
				if (isset($_GET['hall']))
					$this->welcome(intval($_GET['hall']));
				else
					$this->welcome();
				break;
		
		}
		setlocale(LC_TIME, $oldlocale); 
	}
	
	function welcome($hall = 0) {
	
		if ($this->allow_approve) {
			$res = $this->query("SELECT id FROM $this->table_calendar WHERE approved=0");
			$ubehandlet = $res->num_rows;
			if ($ubehandlet > 0) {
				print "<p class='calHelp'>Det foreligger $ubehandlet ubehandlede søknader. <a href='".$this->generateCoolUrl("/listapp/")."'>Behandle søknader</a></p>";
			}
		}
		
		print "<p>Velforeningen har i lengre tid arbeidet med å opparbeide lokalene «Loftet» og «Stallen» til aktivitetslokaler og selskapslokaler for utleie til spesielle anledninger. Det gjenstår ennå en del dugnadsarbeid før lokalene er klare til utleie. Bestillingsordningen på denne siden er derfor foreløpig kun til utprøving. Vi håper å kunne åpne lokalene i løpet av høsten 2007.</p><p>PS: Dersom du kunne tenke deg å delta i dugnadsarbeidet er vi svært takknemlig. Send i så fall en mail til styret.</p>";
	
		print "<p><select name='lokale' id='lokale' onchange='velgLokale(this.options[this.selectedIndex].value);'>
			<option value='0'>Velg lokale:</option>\n";
		$res = $this->query("SELECT id,caption FROM $this->table_halls");
		while ($row = $res->fetch_assoc()) {
			$sel = ($hall == $row['id']) ? " selected='selected'" : "";
			print "		<option value=\"".$row['id']."\"$sel>".stripslashes($row['caption'])."</option>\n";
		}
		$cal = "";
		if ($hall > 0) {
			$cal = $this->outputCal($hall,false);
		}
		print "</select></p>
		<div id='leieKalender'>
			$cal
		</div>
		<script type=\"text/javascript\">
			function velgLokale(nr) {
				jQuery('#leieKalender').html(\"Et øyeblikk...\");
				jQuery.ajax({
					    url: \"".$this->generateCoolUrl("/")."kalender?noprint=true&hall=\"+nr,
					    dataType: \"html\",
					    success: function(responseText){  
                            jQuery(\"#leieKalender\").html(responseText);
                        }                    
                });				
			}
		</script>
		";
	}
	function outputCal($hall, $output = true) {
		if ($hall < 1) { print "&nbsp;"; return; }
		$day_name_length = 3;
		$month_ref = NULL;
		$first_day = 1;
		$cal = "<p class='calHelp'>
					<strong>Fargekoder: </strong>
					<span class='free-day' style='padding:1px 4px;'>Ikke utleid</span> 
					<span class='part-day' style='padding:1px 4px;'>Utleid deler av dagen</span>
					<span class='full-day' style='padding:1px 4px;'>Utleid hele dagen</span>
				</p>
				<table><tr>";
		$year = date("Y",time());
		$month = date("n",time());
		for ($a = 1; $a <= 6; $a++) {
			$firstDay = mktime(0,0,0,$month,1,$year);
			$days = array();
			for ($i = 1; $i <= date('t',$firstDay); $i++) {
				$days[$i] = array($this->generateCoolUrl("/info/","hall=$hall&year=$year&month=$month&day=$i"),'free-day');
			}
			$res = $this->query("SELECT DAY(startdate) AS startday, DAY(enddate) as endday, allday
				FROM $this->table_calendar 
				WHERE hall=$hall 
				AND YEAR(startdate)  <= $year AND YEAR(enddate) >= $year
				AND MONTH(startdate) <= $month AND MONTH(enddate) >= $month 
			");
			while ($row = $res->fetch_assoc()) {
				if ($row['allday']) {
					$days[$row['startday']][1] = 'full-day';
					$days[$row['endday']][1] = 'full-day';
				} else {
					$days[$row['startday']][1] = 'part-day';
					$days[$row['endday']][1] = 'part-day';			
				}
			}
			$cal .= "<td>".$this->generate_calendar($year,$month,$days,$day_name_length,$month_ref,$first_day)."</td>";
			if ($a%3 == 0) $cal .= "</tr><tr>";
			$month++;
			if ($month > 12) { $month = 1; $year++; }
		}
		$cal .= "</tr></table>";
		if ($output) 
			print $cal;
		else
			return $cal;
	}
	
	function displayDay($hall, $year, $month, $day) {
		$res = $this->query("SELECT caption FROM $this->table_halls WHERE id=$hall");
		$row = $res->fetch_assoc();
		$hallName = stripslashes($row['caption']);
		print "
			<a href=\"".$this->generateCoolUrl("/","hall=$hall")."\">&lt;&lt; Tilbake</a>
			<h2>$hallName - $day. ".$this->months[$month-1]."</h2>
			<div id='soknader'>
		";
		print $this->fetchApplications($hall,$year,$month,$day);
		print "</div>";
		if (!$this->allow_send_application) {
			print "
				<p>
					<em>For å sende en søknad om å leie lokalet må du være innlogget.
					Dersom du allerede har brukernavn og passord som fast eller assosiert medlem,
					logger du inn med dette. Dersom du ikke har dette, kan du 
					<a href='/registrering/'>registrere deg</a> som leietaker.</em>
				</p>
			";
			return;
		}
		print "
			<p>
				<div id='bookLink'>
					<a onclick=\"toggleForm(true); return false;\" href='".$this->generateCoolUrl("/book/","hall=$hall&amp;month=$month&amp;day=$day")."'>Ny søknad</a>
				</div>
				<div id='bookForm' style='display:none;'>
					<h2>Ny søknad om leie av lokale</h2>
					<table><tr><td valign='top'><strong>Tidspunkt:</strong></td><td valign='top'>
						<input type='checkbox' name='allday' id='allday' onchange='allDay(this.checked)' /><label for='allday'>Hele dagen</label>
						<div id='timeSelector'>
							<select name='startHour' id='startHour'>
								<option value='8'>08</option>
								<option value='9'>09</option>
								<option value='10'>10</option>
								<option value='11'>11</option>
								<option value='12'>12</option>
								<option value='13'>13</option>
								<option value='14'>14</option>
								<option value='15'>15</option>
								<option value='16'>16</option>
								<option value='17'>17</option>
								<option value='18' selected='selected'>18</option>
								<option value='19'>19</option>
								<option value='20'>20</option>
								<option value='21'>21</option>
								<option value='22'>22</option>
								<option value='23'>23</option>
							</select>:<select name='startMin' id='startMin'>
								<option value='0'>00</option>
								<option value='30'>30</option>
							</select> - <select name='endHour' id='endHour'>
								<option value='8'>08</option>
								<option value='9'>09</option>
								<option value='10'>10</option>
								<option value='11'>11</option>
								<option value='12'>12</option>
								<option value='13'>13</option>
								<option value='14'>14</option>
								<option value='15'>15</option>
								<option value='16'>16</option>
								<option value='17'>17</option>
								<option value='18'>18</option>
								<option value='19'>19</option>
								<option value='20' selected='selected'>20</option>
								<option value='21'>21</option>
								<option value='22'>22</option>
								<option value='23'>23</option>
							</select>:<select name='endMin' id='endMin'>
								<option value='0'>00</option>
								<option value='30'>30</option>
							</select>
						</td></tr><tr><td valign='top'><strong>Evt. kommentarer:</strong></td><td valign='top'>
							<textarea name='kommentar' id='kommentar' cols='40' rows='3'></textarea>
						</td></tr></table>
						<p>
							Alle søknader blir sendt til styret for godkjenning.
						</p>
						<input type='button' value='Avbryt' onclick='toggleForm(false)' /> 
						<input type='button' value='Send søknad' onclick='sendSoknad()' />
					</div>
				</div>
			</p>
			<script type=\"text/javascript\">
				function toggleForm(vis) {
					\$('bookLink').style.display = vis ? 'none' : 'block'; 
					\$('bookForm').style.display = vis ? 'block' : 'none'; 
				}
				function allDay(isChecked) {
					document.getElementById('timeSelector').style.visibility = isChecked ? 'hidden' : 'visible'; 
				}
				function sendSoknad() {
					document.getElementById('soknader').innerHTML = \"Et øyeblikk...\";
					var allday = jQuery('#allday').val();
					var sh = jQuery('#startHour').val();
					var sm = jQuery('#startMin').val();
					var eh = jQuery('#endHour').val();
					var em = jQuery('#endMin').val();
					var comment = jQuery('#kommentar').serialize();
					var allday = jQuery('#allday').serialize();
					toggleForm(false);
					var params = \"hall=$hall&year=$year&month=$month&day=$day&sh=\"+sh+\"&sm=\"+sm+\"&eh=\"+eh+\"&em=\"+em+\"&\"+comment+\"&\"+allday;
					
					jQuery.ajax({
                            url: \"".$this->generateCoolUrl("/send-application/","noprint=true")."\",
                            dataType: \"html\",
                            data: params,
                            type: \"POST\",
                            success: function(responseText){  
                                jQuery(\"#soknader\").html(responseText);
                            }                    
                    });
				}
			</script>
		";	
	}
	
	function tos($n) {
		if ($n < 10) return '0'.$n; else return $n;
	}
	
	function fetchApplications($hall, $year, $month, $day) {
		$mysqlDateStr = "$year-".$this->tos($month)."-".$this->tos($day);
		$res = $this->query("SELECT * FROM $this->table_calendar 
			WHERE hall=$hall 
			AND DATE(startdate) <= '$mysqlDateStr'
			AND DATE(enddate) >= '$mysqlDateStr'
			ORDER BY startdate
		");
		if ($res->num_rows == 0) {
			return "<p><em>Ingen bookinger denne dagen</em></p>";
		} else {
			$output = "
				<p class='calHelp'>
				<strong>Forklaring: </strong><span class='confirmed-app' style='padding:1px 4px;'>Godkjent</span> <span class='unconfirmed-app' style='padding:1px 4px;'>Venter på behandling</span>
				</p>
				<p>Merk at søknader som venter på behandling kan bli avslått. I så fall vil denne tiden bli frigjort.</p>
			";
			while ($row = $res->fetch_assoc()) {
				$sd = date("H:i",strtotime($row['startdate']));
				$ed = date("H:i",strtotime($row['enddate']));
				$mLink = call_user_func($this->make_memberlink,$row['user']);
				if ($row['allday'] == 1) {
					if ($row['approved']) {
						$output .= "<div class='confirmed-app'>Lokalet er utleid til $mLink hele dagen</div>";
					} else {
						$output .= "<div class='unconfirmed-app'>$mLink ønsker å leie lokalet hele dagen</div>";				
					}
				} else {
					if ($row['approved']) {
						$output .= "<div class='confirmed-app'>$sd-$ed er lokalet utleid til $mLink</div>";
					} else {
						$output .= "<div class='unconfirmed-app'>$sd-$ed ønsker $mLink å leie lokalet</div>";				
					}
				}
			}
			return $output;
		}
	}	
	
	function sendApplication($hall, $year, $month, $day) {
		if (!$this->allow_send_application) { $this->permissionDenied(); return false; }
		$res = $this->query("SELECT caption FROM $this->table_halls WHERE id=$hall");
		$row = $res->fetch_array();
		$hallName = stripslashes($row[0]);
		$member = call_user_func($this->lookup_member, $this->login_identifier);

		if (isset($_POST['allday']) && $_POST['allday'] == 'on') {
			$allday = 1;
			$mysqlStartDate = "$year-".$this->tos($month)."-".$this->tos($day)." 00:00";
			$mysqlEndDate = "$year-".$this->tos($month)."-".$this->tos($day)." 23:59";		
		} else {
			$allday = 0;
			$startHour = intval($_POST['sh']);
			$startMin = intval($_POST['sm']);
			$endHour = intval($_POST['eh']);
			$endMin = intval($_POST['em']);
			$mysqlStartDate = "$year-".$this->tos($month)."-".$this->tos($day)." ".$this->tos($startHour).":".$this->tos($startMin).":00";
			$mysqlEndDate = "$year-".$this->tos($month)."-".$this->tos($day)." ".$this->tos($endHour).":".$this->tos($endMin).":00";
		}
		if (strtotime($mysqlStartDate) >= strtotime($mysqlEndDate)) {
			print $this->fetchApplications($hall, $year, $month, $day);
			print "<p style='color:red;font-weight:bold;'>Tidsintervallet du forsøkte å booke er ugyldig!</p>";
			return;		
		}
		if (strtotime($mysqlStartDate) <= time()) {
			print $this->fetchApplications($hall, $year, $month, $day);
			print "<p style='color:red;font-weight:bold;'>Tidsintervallet du forsøkte å booke er ugyldig!</p>";
			return;
		}
		$res = $this->query("SELECT * FROM $this->table_calendar 
			WHERE hall=$hall 
			AND startdate < '$mysqlEndDate' AND enddate > '$mysqlStartDate'
		");
		if ($res->num_rows > 0) {
			print $this->fetchApplications($hall, $year, $month, $day);
			print "<p style='color:red;font-weight:bold;'>Tidsintervallet du forsøkte å booke er ikke ledig!</p>";
			return;
		}
		$comment = addslashes(nl2br($_POST['kommentar']));
		$this->query("INSERT INTO $this->table_calendar (hall,startdate,enddate,comment,user,allday) VALUES (
			$hall, '$mysqlStartDate','$mysqlEndDate',\"$comment\",$this->login_identifier,$allday)"
		);
		
		$this->mailNotificationNewApp($hallName, $member, $mysqlStartDate, $mysqlEndDate, $allday, $_POST['kommentar']);
		print $this->fetchApplications($hall, $year, $month, $day);
		exit();
	}
	
	function listApplications() {
		if (!$this->allow_approve) { $this->permissionDenied(); return false; }
		print "<a href=\"".$this->generateCoolUrl("/")."\">&lt;&lt; Tilbake</a>";
		print "<h2>Ubehandlede søknader</h2>";
		$res = $this->query("SELECT $this->table_calendar.id,$this->table_calendar.startdate,$this->table_calendar.enddate,$this->table_calendar.user,$this->table_calendar.comment,$this->table_calendar.allday,$this->table_halls.caption FROM $this->table_calendar,$this->table_halls 
			WHERE approved=0 AND $this->table_calendar.hall=$this->table_halls.id ORDER BY $this->table_calendar.startdate
		");
		if ($res->num_rows == 0) {
			print "<p><em>Ingen søknader venter på behandling</em></p>";
		} else {
			print "
				<p class='calHelp'>
					<strong>Tips: </strong>
					Kryss av under <em>enten</em> «Godkjenn» <em>eller</em> «Avslå». Dersom du avslår
					en søknad, bør du skrive en kort begrunnelse. Denne blir sendt til søkeren.
				</p>
				<form method='post' action='".$this->generateCoolUrl("/approve-applications/","noprint=true")."'>
				<table>
				<tr><th>Godkjenn</th><th>Avslå</th><th>Begrunnelse</th><th>Søknad</th></tr>
			";
			while ($row = $res->fetch_assoc()) {
				$id = $row['id'];
				$hall = stripslashes($row['caption']);
				$tid = $this->formatDateTime($row['startdate'],$row['enddate'],$row['allday']);
				$mLink = call_user_func($this->make_memberlink,$row['user']);
				print "<tr>
					<td><input type='checkbox' name='accept$id' /></td>
					<td><input type='checkbox' name='deny$id' /></td>
					<td><input type='text' name='comment$id' /></td>
					<td>$mLink ønsker å leie $hall<br />$tid.<br />$comment</td>
					</tr>";
			}
			print "
				</table>
				<input type='submit' value='Ok' />
				</form>
			";			
		}
	}
	
	function formatDateTime($startdate, $enddate, $allday) {
		$startdate = strtotime($startdate);
		$enddate = strtotime($enddate);
		if (strftime("%D",$startdate) == strftime("%D",$enddate)) {
			if ($allday) $tid = "hele ".strftime("%A %e. %b %Y",$startdate);
			else $tid = strftime("%A %e. %b %Y, kl. %R",$startdate) . strftime("-%R",$enddate);			
		} else {
			if ($row['allday'] == 1) $tid = strftime("%a %e. %b %Y",$startdate)."-".strftime("%a %e. %b %Y",$enddate);
			else $tid = strftime("%a %e. %b %Y, %R ",$startdate)."-".strftime(" %a %e. %b %Y, %R",$enddate);
		}
		return $tid;
	}
	
	function approveApplications() {
		$res = $this->query("SELECT $this->table_calendar.id,$this->table_calendar.user,$this->table_calendar.startdate,$this->table_calendar.enddate,$this->table_calendar.allday,$this->table_halls.caption,$this->table_halls.id as hallId FROM $this->table_calendar,$this->table_halls 
			WHERE approved=0 AND $this->table_calendar.hall=$this->table_halls.id ORDER BY $this->table_calendar.startdate
		");
		$cnt = 0;
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$startdate = $row['startdate'];
			$enddate = $row['enddate'];
			$allday = $row['allday'];
			$hall = stripslashes($row['caption']);
			$hallId = $row['hallId'];
			$begrunnelse = $_POST["comment$id"];
			$user = call_user_func($this->lookup_member,$row['user']);
			if (isset($_POST["accept$id"]) && $_POST["accept$id"] == 'on') {
				$this->query("UPDATE $this->table_calendar SET approved=1 WHERE id=$id");
				$this->addToActivityLog("godkjente en søknad om leie av $hall $startdate-$enddate.");
				$this->mailNotification($hallId,$hall,$user,$row['startdate'],$row['enddate'],$row['allday'], true, $begrunnelse);
				$cnt++;
			} else if (isset($_POST["deny$id"]) && $_POST["deny$id"] == 'on') {
				$this->query("DELETE FROM $this->table_calendar WHERE id=$id LIMIT 1");				
				$this->addToActivityLog("avslo en søknad om leie av $hall $startdate-$enddate.");
				$this->mailNotification($hallId,$hall,$user,$row['startdate'],$row['enddate'],$row['allday'], false, $begrunnelse);
				$cnt++;
			}
		}
		$this->redirect($this->generateCoolUrl("/listapp/"),"$cnt søknader ble behandlet");
	}
	
	function mailNotification($hallId, $hall, $member, $startdate, $enddate, $allday, $approved, $comment){
	
		$timeint = $this->formatDateTime($startdate,$enddate, $allday);
		$year = date("Y",strtotime($startdate));
		$month = date("n",strtotime($startdate));
		$day = date("j",strtotime($startdate));
		$server = "https://".$_SERVER['SERVER_NAME'];
		$url = $server.$this->generateCoolUrl("/info/","hall=$hallId&year=$year&month=$month&day=$day");
		
		$from_name = $this->mailSenderName;
		$from_addr = $this->mailSenderAddr;
		
		$to_name = $member->fullname;
		$to_addr = $member->email;
		$recipients = array($to_addr => $to_name);	
	
		$plainBody = "$member->firstname,

Din søknad om å leie $hall $timeint er ".($approved ? "godkjent":"avslått").".

$comment

$url

-- 
$this->site_name
$server
";

		// Send mail
		$message = (new Swift_Message())
			->setSubject("$this->site_name - Søknad behandlet")
			->setFrom([$from_addr => $from_name])
			->setTo($recipients)
			->setBody($plainBody);

		$transport = (new Swift_SmtpTransport($this->smtpHost,$this->smtpPort, 'tls'))
			->setUsername($this->smtpUser)
			->setPassword($this->smtpPass);
		$mailer = new Swift_Mailer($transport);
		$mailer->send($message);
	}
	
	function mailNotificationNewApp($hall, $member, $startdate, $enddate, $allday, $comment){
	
		$timeint = $this->formatDateTime($startdate,$enddate, $allday);
		$year = date("Y",strtotime($startdate));
		$month = date("n",strtotime($startdate));
		$day = date("j",strtotime($startdate));
		$server = "https://".$_SERVER['SERVER_NAME'];
		$url = $server.$this->generateCoolUrl("/listapp/");
		
		$from_name = $this->mailSenderName;
		$from_addr = $this->mailSenderAddr;
		
		$behandler = call_user_func($this->lookup_member, $this->reg_behandler);
		$to_name = $behandler->fullname;
		$to_addr = $behandler->email;
		$recipients = array($to_addr => $to_name);	
	
		$plainBody = "
$member->fullname ønsker å leie $hall $timeint.
$comment

Behandle søknaden ved å gå til denne adressen:
$url

-- 
$this->site_name
$server
";

		// Send mail		
		$message = (new Swift_Message())
			->setSubject("$this->site_name - Søknad om leie av lokale")
			->setFrom([$from_addr => $from_name])
			->setTo($recipients)
			->setBody($plainBody);
		$transport = (new Swift_SmtpTransport($this->smtpHost, $this->smtpPort, 'tls'))
			->setUsername($this->smtpUser)
			->setPassword($this->smtpPass);
		$mailer = new Swift_Mailer($transport);
		$mailer->send($message);
	}
	
	# PHP Calendar (version 2.3), written by Keith Devens
	# http://keithdevens.com/software/php_calendar
	#  see example at http://keithdevens.com/weblog
	# License: http://keithdevens.com/software/license
	
	function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array()){

		$first_of_month = gmmktime(0,0,0,$month,1,$year);
		#remember that mktime will automatically correct if invalid dates are entered
		# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
		# this provides a built in "rounding" feature to generate_calendar()
	
		$day_names = array(); #generate all the day names according to the current locale
		for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
			$day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name
	
		list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
		$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
		$title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;  #note that some locales don't capitalize month and day names
	
		#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
		@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
		if($p) $p = '<span class="calendar-prev">'.($pl ? '<a href="'.htmlspecialchars($pl).'">'.$p.'</a>' : $p).'</span>&nbsp;';
		if($n) $n = '&nbsp;<span class="calendar-next">'.($nl ? '<a href="'.htmlspecialchars($nl).'">'.$n.'</a>' : $n).'</span>';
		$calendar = '<table class="calendar">'."\n".
			'<caption class="calendar-month">'.$p.($month_href ? '<a href="'.htmlspecialchars($month_href).'">'.$title.'</a>' : $title).$n."</caption>\n<tr>";
	
		if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
			#if day_name_length is >3, the full name of the day will be printed
			foreach($day_names as $d)
				$calendar .= '<th abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
			$calendar .= "</tr>\n<tr>";
		}
	
		if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
		for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
			if($weekday == 7){
				$weekday   = 0; #start a new week
				$calendar .= "</tr>\n<tr>";
			}
			if(isset($days[$day]) and is_array($days[$day])){
				@list($link, $classes, $content) = $days[$day];
				if(is_null($content))  $content  = $day;
				$calendar .= '<td'.($classes ? ' class="'.htmlspecialchars($classes).'">' : '>').
					($link ? '<a href="'.htmlspecialchars($link).'">'.$content.'</a>' : $content).'</td>';
			}
			else $calendar .= "<td>$day</td>";
		}
		if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days

		return $calendar."</tr>\n</table>\n";
	}

}
?>
