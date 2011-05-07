<?

class newsletters extends base {

	var $getvars = array('writenew','previewletter','sendletter','newsletterarchive_page',
		'shownewsletter','prefs','saveprefs'
	);

	var $months = array("january","february","march","april","may","june","july","august","september","october","november","december");

	var $allow_viewarchive = false;
	var $allow_sendnewsletter = false;
	var $sendtoValues = array();
	var $tablename = "newsletters";
	var $newslettersPerPage = 10;
	var $pageno = 1;
	var $pagecount;
	var $newslettercount;
	var $memberlookup_function;
	var $grouplookup_function;
	var $memberdb;
	var $prefs;
	var $receive_newsletter = true; // CMS user_option 
	var $registration_code;
	var $wideMode = false;

	var $label_newer = "&lt;&lt; Nyere nyhetsbrev";
	var $label_older = "Eldre nyhetsbrev &gt;&gt;";

	var $errorMessages = array(
		'empty_subject'			=> "Du glemte å fylle inn emne.",
		'empty_body'			=> "Du glemte å fylle inn noe innhold.",
		'not_logged_in'			=> "Du er ikke innlogget. Dette kan skyldtes at innloggingen din gikk ut på tid mens du holdt på å skrive."
	);	
	
	var $mcenter_instance;
	
	function newsletters() {
		$this->tablename = DBPREFIX.$this->tablename;
		if (isset($_GET['previewletter']) || isset($_GET['shownewsletter'])){ 
			$this->wideMode = true;
		}
	}
	
	function initialize(){
		require_once("../htmlMimeMail5/htmlMimeMail5.php");
		$this->initialize_base();
	}

	
	function run(){
		$this->initialize();

		if (isset($_GET['prefs'])){
			return $this->viewPrefs();
		} else if (isset($_GET['saveprefs'])){
			return $this->savePrefs();
		} else if (isset($_GET['writenew'])){
			return $this->printNewNewsletterForm();
		} else if (isset($_GET['previewletter'])){
			return $this->previewNewsletter();
		} else if (isset($_GET['sendletter'])){
			return $this->sendNewsletter();
		} else if (isset($_GET['shownewsletter'])){
			return $this->showNewsletter($_GET['shownewsletter']);
		} else {
			return $this->showArchive();
		}
	}

	function printNewNewsletterForm(){
		//if (!$this->allow_sendnewsletter){ return $this->permissionDenied(); }
		$r = explode(",",$this->recipient_groups);
		$recipients = array();
		$recipients2 = array();
		$gruppeTitler = array();				
		$peopleCount = 0;
		$uniqueCount = 0;
		$missingEmail = 0;
		$totalCount = 0;
		$reserved = 0;
		foreach ($r as $g){
			$gg = call_user_func($this->lookup_group,$g);
            if (count($gg->members) > 0) {
				$user_settings = call_user_func($this->get_useroptions, $this, 'receive_newsletter', $gg->members);
				foreach ($user_settings as $m => $receive) {
					$totalCount++;
					if ($receive) {
						$mm = call_user_func($this->lookup_member,$m);
						if (!empty($mm->email)){
							if (!in_array($mm->ident,$recipients)) {
								$peopleCount++;
								$recipients[] = $mm->ident;
							}
							if (!in_array($mm->email,$recipients2)) {
								$uniqueCount++;
								$recipients2[] = $mm->email;
							}
						} else {
							$missingEmail++;
						}
					} else {
						$reserved++;
					}
				}
			}
			$gruppeTitler[] = "«".$gg->caption."»";
			
		}
		
		$output = "
			<h3>Skriv nytt nyhetsbrev</h3>
		";
		
		if (!$this->isLoggedIn()) {
			$output .= $this->notSoFatalError('Du må logge inn for å fortsette.',array('logError'=>false,'customHeader'=>'Viktig:'));
		}

		$emne = "";
		$melding = "";
		
		if (isset($_SESSION['errors'])){
			
			$errors = $_SESSION['errors'];
			$errstr = "<ul>";
			$numErrors = 0;
			foreach ($_SESSION['errors'] as $s){
				if ($s == 'not_logged_in' && $this->isLoggedIn()) { } else {
					$numErrors++;
					if (isset($this->errorMessages[$s]))
						$errstr.= "<li>".$this->errorMessages[$s]."</li>";
					else
						$errstr.= "<li>$s</li>";
				}
			}
			$errstr .= "</ul>";			
			if ($numErrors > 0) {
				$output .= $this->notSoFatalError($errstr,array('logError'=>false,'customHeader'=>'Nyhetsbrevet ble ikke sendt fordi:'));
			}
		
		}
		if (isset($_SESSION['postdata'])){

			$postdata = $_SESSION['postdata'];
			$emne = $postdata['emne'];
			$melding = $postdata['melding'];
			
			if ($this->isLoggedIn()) {
				unset($_SESSION['errors']);
				unset($_SESSION['postdata']);
			}
		}
		
		
		$lastupdt = array_pop($gruppeTitler);
		$grctp = (count($gruppeTitler) > 0) ? implode(", ",$gruppeTitler)." og ".$lastupdt : $lastupdt;
		$grctp = "<div>Aktive medlemmer av $grctp med registrert epostadresse<br />
			(totalt $peopleCount personer fordelt på $uniqueCount adresser)</div>";

		if ($uniqueCount < $peopleCount) {
			$output .= "
			<p>
				Merk: Vi sender kun ut ett brev per adresse. Fordi flere personer
				er registrert med den samme e-postadressen vil kun $uniqueCount
				kopier sendes ut, selv om det er $peopleCount mottakere av brevet.
			</p>
			";
		}
		$disabledCode = '';
		if (!$this->isLoggedIn()) {
			$disabledCode = ' disabled="disabled"';
		}
		$output .= '
			<p>
				Av totalt '.$totalCount.' mulige mottakere, mangler '.$missingEmail.' personer 
				e-postadresse og disse vil ikke motta nyhetsbrevet. '.$reserved.' personer
				har reservert seg mot å motta det.
			</p>
			
			<form method="post" action="'.$this->generateURL(array('previewletter')).'" style="margin-top:10px;" onsubmit="$(\'nsubtn\').value=\'Vennligst vent...\'; $(\'nsubtn\').disabled=true; return true;">
				<table class="skjema">
					<tr>
						<td valign="top"><strong>Mottakere: </strong></td>
						<td>'.$grctp.'</td>
					</tr><tr>
						<td><strong>Emne: </strong></td>
						<td><input type="text" name="emne" style="width: 400px;" value="'.$emne.'"'.$disabledCode.' /></td>
					</tr><tr>
						<td valign="top"><strong>Melding: </strong></td>
						<td><textarea name="melding" style="width: 400px; height: 200px;"'.$disabledCode.'>'.$melding.'</textarea></td>
					</tr><tr>
						<td>&nbsp;</td>
						<td align="right">
							<input type="submit" id="nsubtn" value="Forhåndsvis nyhetsbrev" style="width: 150px; height: 25px;"'.$disabledCode.' />
						</td>
					</tr>
				</table>
			</form>
			<p>
				Bruk ren tekst (ikke html). Internettadresser blir klikkbare.
				Det er dessverre ikke anledning til å sette inn bilder eller annet multimediainnhold.
				Dette er for å opprettholde kompabilitet med eldre epost-klienter. 
			</p>
			<hr />
			<a href="'.$this->generateURL('').'">Tilbake til listen</a>
		';
		
		return $output;
	}


	function makeNewsletter($d, $compabilityMode = false) {
	
		$subject = $d['subject'];
		$raw_body = $d['raw_body'];
		$timestamp_string = $d['timestamp'];
		$author_name = $d['author_name'];
		$author_addr = $d['author_addr'];
		$recipients_string = $d['recipients'];		
		
		$newsletter_name = $this->body_header;
		
		$url_root = "http://".$_SERVER['SERVER_NAME'].ROOT_DIR."/";
		$url_self = $url_root . $this->fullslug . "/";
		
		if ($compabilityMode) {
			$htmlBody = $raw_body;
			$plainBody = strip_tags($raw_body);
		} else {
			$htmlBody = str_replace("\n","<br />\n",$raw_body);
			$htmlBody = $this->makeHtmlUrls($htmlBody,60,"...");
			$plainBody = $raw_body;
		}	
			
		$r1a   = array();							$r2a   = array();
		$r1a[] = "%url_main%";						$r2a[] = $url_root;
		$r1a[] = "%url_archive%";					$r2a[] = $url_self;
		$footer = str_replace($r1a, $r2a, $this->news_footer_html);
		
		$headerImg = rtrim($url_root,"/")."/".trim($this->image_dir,"/")."/email_header3.png";
		
		$templateHtml = file_get_contents('../includes/templates/newsletter.html');
		$s1 = array();					$s2 = array();
		$s1[] = "%newsletter_name%";	$s2[] = $newsletter_name;
		$s1[] = "%header_image%";		$s2[] = $headerImg;
		$s1[] = "%author_name%";		$s2[] = $author_name;
		$s1[] = "%author_addr%";		$s2[] = $author_addr;
		$s1[] = "%subject%";			$s2[] = $subject;
		$s1[] = "%body%";				$s2[] = $htmlBody;
		$s1[] = "%timestamp%";			$s2[] = $timestamp_string;
		$s1[] = "%recipients%";			$s2[] = empty($recipients_string) ? '' : '<b>Mottakere: </b> '.$recipients_string.'<br />';
		$s1[] = "%url_main%";			$s2[] = $url_root;
		$s1[] = "%url_archive%";		$s2[] = $url_self;
		$s1[] = "%url_unsubscribe%";	$s2[] = $url_self."?prefs";
		$htmlLetter = str_replace($s1,$s2,$templateHtml);

		$templatePlain = file_get_contents('../includes/templates/newsletter.txt');
		$s1 = array();					$s2 = array();
		$s1[] = "%newsletter_name%";	$s2[] = strtoupper($newsletter_name);
		$s1[] = "%header_image%";		$s2[] = $headerImg;
		$s1[] = "%author_name%";		$s2[] = $author_name;
		$s1[] = "%author_addr%";		$s2[] = $author_addr;
		$s1[] = "%subject%";			$s2[] = $subject;
		$s1[] = "%body%";				$s2[] = $plainBody;
		$s1[] = "%timestamp%";			$s2[] = $timestamp_string;
		$s1[] = "%recipients%";			$s2[] = empty($recipients_string) ? '' : '<b>Mottakere: </b> '.$recipients_string.'<br />';
		$s1[] = "%url_main%";			$s2[] = $url_root;
		$s1[] = "%url_archive%";		$s2[] = $url_self;
		$s1[] = "%url_unsubscribe%";	$s2[] = $url_self."?prefs";
		$plainLetter = str_replace($s1,$s2,$templatePlain);

		return array($plainLetter,$htmlLetter);
	}
	
	function prepareRecipientLists() {
		// List mottakere
		$r = explode(",",$this->recipient_groups);
		$recipients = array();
		$ToList = array();
		$gruppeTitler = array();
				
		foreach ($r as $g){
			$gg = call_user_func($this->lookup_group,$g);
			if (count($gg->members) > 0) {
				$user_settings = call_user_func($this->get_useroptions, $this, 'receive_newsletter', $gg->members);
				$c = 0;
				foreach ($user_settings as $m => $receive) {
					if ($receive) {
						$mm = call_user_func($this->lookup_member,$m);
						if (!empty($mm->email)){
							if (!in_array($mm->ident,$recipients)) {
								$c++;
								$recipients[] = $mm->ident;
								$ToList[$mm->ident] = array('name' => $mm->fullname, 'email' => $mm->email);
							}
						}
					}
				}
			}
			$gruppeTitler[] = $gg->caption; //." ($c personer)";
		}
		
		return array(
			'recipients' => $recipients,
			'to_list' => $ToList,
			'group_list' => $gruppeTitler
		);
		
	}	
	
	function prepareLetterFromPost() {	

		$errors = array();
				
		$clean_subject = trim(htmlspecialchars($_POST["emne"]));
		if (empty($clean_subject)) array_push($errors,'empty_subject');
		$subject = $clean_subject;

		$raw_body = htmlspecialchars($_POST['melding']);
		if (empty($raw_body)) array_push($errors,'empty_body');
		$raw_body = str_replace("\r\n","\n",$raw_body);
		$raw_body = str_replace("\r","\n",$raw_body);

		if (!$this->isLoggedIn()) $errors[] = 'not_logged_in';		

		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$this->redirect($this->generateURL("writenew=true",true),"Du må rette opp en eller flere feil først.");
		}

		$me = call_user_func($this->lookup_member, $this->login_identifier);
				
		return array(
			'subject' => $subject,
			'raw_body' => $raw_body,
			'author_name' => $me->fullname,
			'author_addr' => $me->email,
		);
	}

	function previewNewsletter(){
	
		$newsletter = array_merge(
			$this->prepareLetterFromPost(),
			$this->prepareRecipientLists()
		);

		$_SESSION['postdata'] = $_POST;
				
		if (!$this->allow_sendnewsletter) return $this->permissionDenied();
		
		$tmp = $this->makeNewsletter(array(
			'subject' => $newsletter['subject'], 
			'raw_body' => $newsletter['raw_body'],
			'timestamp' => '<em>Ikke sendt enda</em>',
			'author_name' => $newsletter['author_name'],
			'author_addr' => $newsletter['author_addr'],
			'recipients' => implode(', ',$newsletter['group_list'])
		));
		$newsletter['plain_body'] = $tmp[0];
		$newsletter['html_body'] = $tmp[1];
		
		$output = '<h3>Forhåndsvisning</h3>'.
			'<p><strong>Dette er en forhåndsvisning! Nyhetsbrevet er ikke sendt enda.</strong></p> 
			<form method="post" action="'.$this->generateUrl('sendletter').'" style="padding:20px;">
				<table class="skjema"><tr><td>
					<input type="submit" name="submitletter" value="Send nyhetsbrev" style="width: 150px; height: 25px;" />
					<input type="submit" name="editletter" value="Rediger nyhetsbrev" style="width: 150px; height: 25px;" />
				</td></tr></table>
			</form>
			<p>Omtrent slik vil nyhetsbrevet se ut i moderne epostklienter (med html-støtte):</p>
			<div style="background:#fff;padding:10px 0px 20px 0px;">
				'.$newsletter['html_body'].'		
			</div>
			<p>
			Omtrent slik vil nyhetsbrevet se ut i enkle epostklienter uten html-støtte 
			(utseendet vil variere fra klient til klient):
			</p>
			<div style="background:#fff;padding:20px;">
				<pre style="white-space: pre-wrap;">'.$newsletter['plain_body'].'</pre>		
			</div>
			
		';
		
		return $output; 
		
		
	}
	
	function sendNewsletter(){
	
		if (isset($_POST['editletter'])) {
			$this->redirect($this->generateURL('writenew=true'));
		}

		if (!isset($_SESSION['postdata'])) $this->fatalError('Invalid session!');
		$_POST = $_SESSION['postdata'];
		unset($_SESSION['postdata']);

		$newsletter = array_merge(
			$this->prepareLetterFromPost(),
			$this->prepareRecipientLists()
		);
				
		if (!$this->allow_sendnewsletter) return $this->permissionDenied();
		
		$tmp = $this->makeNewsletter(array(
			'subject' => $newsletter['subject'], 
			'raw_body' => $newsletter['raw_body'],
			'timestamp' => strftime('%A %e. %B %Y',time()),
			'author_name' => $newsletter['author_name'],
			'author_addr' => $newsletter['author_addr'],
			'recipients' => implode(', ',$newsletter['group_list'])
		));
		$newsletter['plain_body'] = $tmp[0];
		$newsletter['html_body'] = $tmp[1];

		$from_name = $this->sender_name;
		$from_addr = $this->sender_email;

		$receive_html = call_user_func($this->get_useroptions, $this, 'newsletter_as_html', $newsletter['recipients']);
		
		// Send nyhetsbrev
		foreach ($newsletter['to_list'] as $ident => $udata){
			$mail = new htmlMimeMail5();
			$mail->setFrom("$from_name <$from_addr>");
			$mail->setReturnPath($from_addr);
			$mail->setSubject($this->subject_prefix.' '.$newsletter['subject']);
			$mail->setText($newsletter['plain_body']);
			if ($receive_html[$ident]) {
				$mail->setHTML($newsletter['html_body']);
			}
			$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
			//$mail->addEmbeddedImage(new fileEmbeddedImage($email_header, 'image/jpeg', new Base64Encoding()));
			//$mail->addAttachment(new fileAttachment('example.zip', 'application/zip', new Base64Encoding()));
			if (!$mail->send(array($udata['name']." <".$udata['email'].">"),$type = 'smtp')) {
				$this->addToErrorLog("Et nyhetsbrev kunne ikke sendes til ".$udata['name']." (".$udata['email']."). Feil: ".var_export($mail->errors,true));
			}
			unset($mail);
		}
		$this->query("INSERT INTO $this->tablename 
				(page,sender,recipients,subject,timestamp,body,version) 
				VALUES ('".$this->page_id."',
				'".addslashes($this->login_identifier)."',
				'".addslashes(implode(",",$newsletter['recipients']))."',
				'".addslashes($newsletter['subject'])."',
				'".addslashes(time())."',
				'".addslashes($newsletter['raw_body'])."',
				'2')"
		);
		$id = $this->insert_id();
		
		$this->mcenter_instance = new messagecenter(); 
		call_user_func($this->prepare_classinstance, $this->mcenter_instance, $this->messagecenter);
		$this->mcenter_instance->saveNewsletter($newsletter['recipients'], $newsletter['subject'], $newsletter['html_body']);

		$this->addToActivityLog("sendte nyhetsbrev til ".implode(", ",$newsletter['group_list']));

		$this->redirect($this->generateURL("shownewsletter=$id",true),"Nyhetsbrevet ble sendt til ".count($newsletter['recipients'])." personer!");
	}

	function showArchive(){
		if (!$this->allow_viewarchive) return $this->permissionDenied();
		
		if (isset($_GET['newsletterarchive_page'])){
			if (is_numeric($_GET['newsletterarchive_page'])){
				$this->pageno = intval($_GET['newsletterarchive_page']);
			}
		}
		
		$output = "";

		$res = $this->query("SELECT id FROM $this->tablename WHERE page=".$this->page_id." ORDER BY timestamp DESC");
		$this->newslettercount = $res->num_rows;

		$this->pagecount = ceil($this->newslettercount/$this->newslettersPerPage);
		$startAt = ($this->pageno-1)*$this->newslettersPerPage;
		
		$res = $this->query(
			"SELECT id,timestamp,sendto,recipients,subject,body
			FROM $this->tablename 
			WHERE page=".$this->page_id."
			ORDER BY timestamp DESC
			LIMIT $startAt,$this->newslettersPerPage"
		);
		if (!empty($this->login_identifier)){
			$output .= '<p><a href="'.$this->generateURL("prefs").'">Mine innstillinger</a>';
		} else {
			if (!empty($this->registration_code)) {
				$output .= $this->registration_code;
			}
			print "<p>";
		}
		if ($this->allow_sendnewsletter){
			$output .= ' | <a href="'.$this->generateURL("writenew=true").'">Send nytt nyhetsbrev</a>';
		}

		$output .= "</p>\n";
		
		$r = explode(",",$this->recipient_groups);
		$recipients = array();
		$recipients2 = array();
		$gruppeTitler = array();				
		$peopleCount = 0;
		$uniqueCount = 0;
		$missingEmail = 0;
		$reserved = 0;
		foreach ($r as $g){
			$gg = call_user_func($this->lookup_group,$g);
			if (count($gg->members) > 0) {
				$user_settings = call_user_func($this->get_useroptions, $this, 'receive_newsletter', $gg->members);
				foreach ($user_settings as $m => $receive) {
					if ($receive) {
						$mm = call_user_func($this->lookup_member,$m);
						if (!empty($mm->email)){
							if (!in_array($mm->ident,$recipients)) {
								$peopleCount++;
								$recipients[] = $mm->ident;
							}
							if (!in_array($mm->email,$recipients2)) {
								$uniqueCount++;
								$recipients2[] = $mm->email;
							}
						} else {
							$missingEmail++;
						}
					} else {
						$reserved++;
					}
				}
			}
			$gruppeTitler[] = "«".$gg->caption."»";
		}
		$lastupdt = array_pop($gruppeTitler);
		$gruppeTitler = (count($gruppeTitler) > 0) ? implode(", ",$gruppeTitler)." og ".$lastupdt : $lastupdt;
		$output .= "
			<p>
				Dette nyhetsbrevet sendes ut til alle medlemmer av gruppene
				$gruppeTitler. Totalt $peopleCount personer vil motta nyhetsbrevet.
			</p>
		";
		
		$output .= "<h3>Meldingsarkiv:</h3>";
		if ( $this->newslettercount > 0) {
			$output .= "
				<table class='forum' width='100%'>
					<tr>
						<td><b><i>Emne</i></b></td>
						<td align='center'><b><i>Mottakere</i></b></td>
						<td align='right'><b><i>Sendt nr</i></b></td>
					</tr>
			";
			$classNo = 1;
			while ($row = $res->fetch_assoc()){
				$classNo = !$classNo;
				if (empty($row['recipients'])) {
					$rcptcount = "(ukjent)";
				} else {
					$rcpts = explode(",",$row['recipients']);
					$rcptcount = count($rcpts);
				}
				$classname = "forum".($classNo+1);
				$output .= '
					<tr class="'.$classname.'" onclick=\'location="'.$this->generateURL("shownewsletter=".$row["id"]).'"\'>
						<td class="'.$classname.'"><a href="'.$this->generateURL("shownewsletter=".$row["id"]).'">'.stripslashes($row["subject"]).'</a></td>
						<td class="'.$classname.'" align="center">'.$rcptcount.' </td>
						<td class="'.$classname.'" align="right"><nobr>'.date("d M Y",$row["timestamp"]).'</nobr></td>
					</tr>';
			}
			$output .= "</table>";
			$forrigeSide = ($this->pageno == 1) ? $this->label_newer : '<a href="'.$this->generateURL("newsletterarchive_page=".($this->pageno-1)).'">'.$this->label_newer.'</a>';
			$nesteSide = ($this->pageno == $this->pagecount) ? $this->label_older : '<a href="'.$this->generateURL("newsletterarchive_page=".($this->pageno+1)).'">'.$this->label_older.'</a>';
			$output .= "<table width='100%'><tr><td>$forrigeSide</td><td><p align='center'>Side $this->pageno av $this->pagecount</p></td><td><p align='right'>$nesteSide</p></td></tr></table>";

		} else {
			$output .= "<em>Ingen nyhetsbrev har blitt sendt ut</em>";
		}
		return $output;
	}

	function showNewsletter($id){
		if (!is_numeric($id)) $this->fatalError("invalid input!");
		$res = $this->query("SELECT id,sender,timestamp,sendto,recipients,subject,body,version FROM $this->tablename WHERE id='$id'");
		$row = $res->fetch_assoc();
		$author = call_user_func($this->lookup_member, $row['sender']);
		$rcpts = explode(",",$row['recipients']);
		$rcptcount = count($rcpts);

		$rcptlists = $this->prepareRecipientLists();

		call_user_func(
			$this->add_to_breadcrumb,
			'<a href="'.$this->generateURL("shownewsletter=$id").'">'.stripslashes($row['subject']).'</a>'
		);
		
		$compabilityMode = false;
		$body = stripslashes($row['body']);
		if (intval($row['version']) == 1) $compabilityMode = true;
		
		$tmp = $this->makeNewsletter(array(
			'subject' => stripslashes($row['subject']), 
			'raw_body' => $body,
			'timestamp' => strftime('%A %e. %B %Y',$row['timestamp']),
			'author_name' => $author->fullname,
			'author_addr' => $author->email,
			'recipients' => implode(', ',$rcptlists['group_list'])
		),$compabilityMode);
		$newsletter['plain_body'] = $tmp[0];
		$newsletter['html_body'] = $tmp[1];
		
		return '
			<p>Dette nyhetsbrevet ble sendt ut til '.$rcptcount.' personer.</p>
			<div style="background:#fff;padding:10px 0px 20px 0px;">
				'.$newsletter['html_body'].'		
			</div>			
		';

	}
	
	function viewPrefs() {

		$user_id = $this->login_identifier;
		if (empty($user_id)){ $this->permissionDenied(); exit(); }
			
		$receive_newsletter_checked = $this->receive_newsletter ? "checked='checked' " : "";
		$newsletter_as_html_checked = $this->newsletter_as_html ? "checked='checked' " : "";
		$post_uri = $this->generateURL(array("noprint=true","saveprefs"));
				
		call_user_func(
			$this->add_to_breadcrumb, 
			'<a href="'.$this->generateURL("prefs").'"><strong>Mine innstillinger</strong></a>'
		);

		return '
			<h3>Mine innstillinger</h3>
			<form method="post" action="'.$post_uri.'">
				<p>
					<label for="receive_newsletter">
						<input type="checkbox" name="receive_newsletter" id="receive_newsletter" '.$receive_newsletter_checked.'/>
						Motta dette nyhetsbrevet.
					</label>
				</p>
				<p>
					<label for="newsletter_as_html">
						<input type="checkbox" name="newsletter_as_html" id="newsletter_as_html" '.$newsletter_as_html_checked.'/>
						Motta dette nyhetsbrevet som HTML (alternativt mottar du det som ren tekst).
					</label>
				</p>	
				<p>
					<input type="submit" value="Lagre innstillinger" />
				</p>
			</form>
		';
	}
	
	function savePrefs() {
		$page_id = $this->page_id;
		$user_id = $this->login_identifier;
		if (empty($user_id)){ $this->permissionDenied(); exit(); }
		
		// Save settings
		$opts = array('receive_newsletter','newsletter_as_html'); 
		foreach ($opts as $opt_name) {
			$value = (isset($_POST[$opt_name]) && ($_POST[$opt_name] == 'on')) ? 1 : 0;
			$res = $this->query("SELECT id FROM $this->table_useroptions 
				WHERE page='$page_id' AND user='$user_id' AND name='$opt_name'");
			if ($res->num_rows == 1) {
				$row = $res->fetch_assoc();
				$id = $row['id'];
				$this->query("UPDATE $this->table_useroptions SET value='$value' WHERE id='$id'");
			} else if ($res->num_rows == 0) {
				$this->query("INSERT INTO $this->table_useroptions (page,user,name,value)
					VALUES ('$page_id','$user_id','$opt_name','$value')");
			} else {
				$this->fatalError("Kunne ikke lagre innstillingen $opt_name fordi duplikat eksisterer for bruker $user_id på siden $page_id.");
			}
		}
 		 		
 		// Redirect
		$this->redirect($this->generateURL(""),'Dine innstillinger ble lagret');
	}

}

?>

