<?php
class innlogging extends base {

	public $getvars = array("dologin","brukernavn","passord","dologout","sendpwd","passwordsent","loginfailed","sendlogininfo","lost_pass");

	/* PRIVATE */
	private $memberdb;
	private $loggedin = false;
	private $_loggedinUser = array();
	private $_sessionId = 'c18bg';
	private $table_globaloptions = 'cms_globaloptions';
	private $table_login = 'login';
	private $table_members = 'members';
	private $_minPwdLen = 4;
	private $_maxPwdLen = 15;

	/* INTERNALS: */
		var $passwordLengthLimit = 40;
		var $replaceContent;
	
	function __construct() {
		$this->table_globaloptions = DBPREFIX.$this->table_globaloptions;
		$this->table_login = DBPREFIX.$this->table_login;
		$this->table_members = DBPREFIX.$this->table_members;
	}
	
	public function setMemberDb($memberdb) { $this->memberdb = $memberdb; }
	public function isLoggedIn() { return $this->loggedin; }
	public function getMinPwdLen() { return $this->_minPwdLen; }
	public function getMaxPwdLen() { return $this->_maxPwdLen; }

	public function getUserId() { 
		if (!$this->loggedin) return 0;
		return $this->_loggedinUser['id'];
	}

	public function getUserRights() { 
		if (!$this->loggedin) return 0;
		return $this->_loggedinUser['rights']; 
	}
	
	function run(){
		$this->initialize_base();
		$this->setGlobalOptions();
	
		$this->replaceContent = false;
		if ((isset($_GET['loginfailed'])) || (isset($_GET['sendlogininfo'])) || (isset($_GET['passwordsent'])) || (isset($_GET['lost_pass']))){
			$this->replaceContent = true;
		}
		
		if (isset($_GET['dologin'])){
			$rem = (isset($_POST['huskmeg']) && ($_POST['huskmeg'] == 'on'));
			$status = $this->doLogin($_POST['brukernavn'],$_POST['passord'],$rem);
			$this->localRedirect($status);
		}
		if (isset($_GET['dologout'])){
			$this->doLogout();
		}
		if (isset($_GET['sendpwd'])){
			$this->sendPassword();
		}
		
		/* Sjekk om bruker er innlogget */
		$this->loggedin = false;
		$this->_loggedinUser = null;
		if (isset($_SESSION[$this->_sessionId]) && !empty($_SESSION[$this->_sessionId])){
			$this->verifyLogin($_SESSION[$this->_sessionId]);
		} else if (isset($_COOKIE[$this->_sessionId]) && !empty($_COOKIE[$this->_sessionId])){
			$this->verifyLogin($_COOKIE[$this->_sessionId]);
		}

		/* Logg ut brukere som ikke har vært aktive på 3 timer */		
		$this->query("UPDATE $this->table_login 
			SET session_ip='', session_id='', save_login=0, logout_reason='timeout'
			WHERE save_login=0 AND session_id != '' AND TIMESTAMPDIFF(HOUR,login_time,NOW()) > 3");
	}
	
	/* If logged in, this function will return information about the user from the db */
	private function verifyLogin($session_id){
		$profile = NULL; $tl = $this->table_login; $tm = $this->table_members;
		
		$res = $this->query("SELECT $tl.user_id AS id, $tm.rights, $tm.firstname,$tm.middlename,$tm.lastname,$tm.slug
			FROM $tl,$tm
			WHERE $tl.session_id=\"".addslashes($session_id)."\"
			AND $tl.user_id=$tm.ident"
		);			
		/* I used to also verify that the client's ip address had not changed
				AND session_ip='".ip2long($_SERVER['REMOTE_ADDR'])."'", 
		   but then I discovered that some wireless internet providers, like internet cafées, 
		   actually change their clients ip addresses during a single session. Therefore we cannot
		   require that the ip address of the client is the same during the whole session. */

		if ($res->num_rows != 1){
			// Not logged in!
		} else {
		
		
			$row = $res->fetch_assoc();
			/*foreach ($row as $variable => $value){
				//$tempArray = array($variable => $value);
				$profile->$variable = $value;
			}*/
		
			$this->loggedin = true;
			$id = intval($row['id']);
			$mid = empty($row['middlename']) ? '' : mb_substr(stripslashes($row['middlename']),0,1,'UTF-8').". ";
			$this->_loggedinUser = array(
				'id' => $id,
				'name' => stripslashes($row['firstname'])." ".$mid.stripslashes($row['lastname']),
				'rights' => intval($row['rights']),
				'slug' => stripslashes($row['slug']),
				'groupcaptions' => $this->memberdb->groupsToStringList($id)
			);

			/* Oppdater login_time for å hindre ufrivillig utlogging */
			$this->query("UPDATE $this->table_login set login_time=NOW() WHERE user_id=".$this->_loggedinUser['id']); 			
			
		}
		unset($res);
		return $profile;
	}

	function doLogin($username, $password, $remberLogin){
		global $crypto;
		if (strlen($password) > $this->passwordLengthLimit){
			return "GENERAL_ERROR";
		}
		
		$res = $this->query("
			SELECT user_id, pwd, sperret 
			FROM $this->table_login
			WHERE username=\"".addslashes($username)."\" 
				AND (pwd=\"".addslashes($crypto->encrypt($password))."\" OR pwd='NOT_SET')"
		);
		// DEBUG: print "Hello $username,$password,".base64_encode($password);
		//exit();
		if ($res->num_rows == 1){
			$row = $res->fetch_assoc();
			$ident = intval($row['user_id']);
			if (count($this->memberdb->getMemberById($ident)->memberof) == 0){
				return "NO_MEMBERSHIPS";
			} else if ($row['sperret'] == '1'){
				return "ACCOUNT_LOCKED";			
			} else if ($row['pwd'] == 'NOT_SET'){
				return "PWD_NOT_SET";
			} else {
				/* Vi lager en unik session_id, som vi deler ut som cookie og lagrer i tabellen 
				   så lenge brukeren er innlogget. */
				$uniqueid = false;
				$session_id = addslashes($this->randStr(18));
				do {
					$res2 = $this->query("SELECT user_id FROM $this->table_login WHERE session_id='$session_id'");
					if ($res2->num_rows == 0){ $uniqueid = true; } else { $session_id = addslashes($this->randStr(18)); }
				} while ($uniqueid == false);
				//$_SESSION['ltim'] = time();
				if ($remberLogin) {
					setcookie($this->_sessionId,$session_id,time()+60*60*24*30,'/'); // 30 days
				} else {
					$_SESSION[$this->_sessionId] = $session_id;
				}
				//print $_SERVER['REMOTE_ADDR']." - ".ip2long($_SERVER['REMOTE_ADDR']);
				$this->query("UPDATE $this->table_login 
					SET login_time=NOW(),
					    logout_reason='',
						session_id=\"$session_id\",
						session_ip=\"".ip2long($_SERVER['REMOTE_ADDR'])."\",
						save_login=".($remberLogin?1:0)."
					WHERE user_id='$ident'");
					//exit();
				return "LOGIN_OK";
			}
		} else {
			return "INCORRECT_LOGIN";
		}
	}
	
	public function logout() {
		$sId = '';
		if (isset($_SESSION[$this->_sessionId]) && !empty($_SESSION[$this->_sessionId])) $sId = $_SESSION[$this->_sessionId];
		if (isset($_COOKIE[$this->_sessionId]) && !empty($_COOKIE[$this->_sessionId])) $sId = $_COOKIE[$this->_sessionId];
		if (!empty($sId)) {
			$this->query("UPDATE $this->table_login 
				SET session_id='', session_ip='', save_login=0, logout_reason='normal'
				WHERE session_id=\"".addslashes($sId)."\"");
		}
		unset($_SESSION[$this->_sessionId]);
	}
	private function doLogout(){
		$this->logout();
		$this->redirect($this->generateURL('',true));
	}
	
	public function hasUsername($user_id) {
		$rs = $this->query("SELECT username FROM $this->table_login WHERE user_id=".intval($user_id));
		if ($rs->num_rows != 1) return false;
		$row = $rs->fetch_assoc();
		return ($row['username'] != '');
	}
	
	public function hasPassword($user_id) {
		$rs = $this->query("SELECT pwd FROM $this->table_login WHERE user_id=".intval($user_id));
		if ($rs->num_rows != 1) return false;
		$row = $rs->fetch_assoc();
		return ($row['pwd'] != 'NOT_SET');
	}

	public function getUsername($user_id = 0) {
		if ($user_id == 0 && $this->loggedin) $user_id = $this->_loggedinUser->id;
		$rs = $this->query("SELECT username FROM $this->table_login WHERE user_id=".intval($user_id));
		if ($rs->num_rows != 1) return false;
		$row = $rs->fetch_assoc();
		return stripslashes($row['username']);
	}
	
	public function getLoginTime($user_id = 0) {
		if ($user_id == 0 && $this->loggedin) $user_id = $this->_loggedinUser->id;
		$rs = $this->query("SELECT login_time FROM $this->table_login WHERE user_id=".intval($user_id));
		if ($rs->num_rows != 1) return false;
		$row = $rs->fetch_assoc();
		$login_time = strtotime($row['login_time']);
		if ($login_time < strtotime('1900-01-01')) $login_time = 0;
		return $login_time;
	}
	
	public function sendLoginDetails($user_id) {
		$user_id = intval($user_id);
		if ($user_id <= 0) $this->fatalError('invalid user');
		if (!$this->isLoggedIn()) $this->fatalError('du er ikke logget inn');

		$rs = $this->query("SELECT username,pwd FROM $this->table_login WHERE user_id=".$user_id);
		if ($rs->num_rows != 1) $this->fatalError('invalid user');
		$row = $rs->fetch_assoc();
		if ($row['pwd'] == 'NOT_SET') return false;

		$m = $this->memberdb->getMemberById($user_id);
		$url_root = "https://".$_SERVER['SERVER_NAME'].ROOT_DIR."/";
		$rcptmail = $m->email;
		$rcptname = $m->fullname;
		$plainBody = $this->_loggedinUser->name." har initiert en automatisk utsending av dine innloggingsdetaljer.\r\n\r\n";
		$plainBody .= "  Brukernavn: ".stripslashes($row['username'])."\r\n";
		$plainBody .= "  Passord: ".base64_decode(strrev(stripslashes($row['pwd'])));
		$plainBody .="\r\n\r\n-- \r\nDette er en auto-generert utsendelse.\r\n$url_root";
		
		$recipients = array($rcptmail);

		// Send mail
		$message = (new Swift_Message())
			->setSubject("[$this->mailSenderName] Innloggingsopplysninger")
			->setFrom([$this->mailSenderAddr => $this->mailSenderName])
			->setTo($recipients)
			->setBody($plainBody);
		$transport = (new Swift_SmtpTransport($this->smtpHost, $this->smtpPort, 'ssl'))
			->setUsername($this->smtpUser)
			->setPassword($this->smtpPass);
		$mailer = new Swift_Mailer($transport);
		$mailer->send($message);

		return true;
	}

	
	public function assignUsername($user_id,$username) {
		$username = addslashes($username);
		$errors = array();
		$res = $this->query("SELECT user_id FROM $this->table_login WHERE username=\"$username\"");
		if ($res->num_rows != 0){ array_push($errors,"username_already_exists"); } 
		if (strlen($username) < 3) array_push($errors,"username_too_short");
		if (strlen($username) > 10) array_push($errors,"username_too_long");
		if (preg_match("/[^a-z0-9]/",$username)){
			array_push($errors,"username_contain_specials");
		}
		if (count($errors) == 0){
			$this->query("
				UPDATE $this->table_login
				SET username=\"$username\"
				WHERE user_id='$user_id'"
			);
		}
		return $errors;
	}
	
	public function releaseUsername($user_id) {
		$user_id = intval($user_id);
		$this->query("UPDATE $this->table_login SET username='', pwd='NOT_SET', deregister_time=NOW() WHERE user_id=$user_id");
		if ($this->affected_rows() != 1) {
			$this->fatalError("Could not release username for $user_id!");
		}
	}
	
	public function addUser($user_id) {
	 	$unique_string = addslashes($this->randStr(18));
		$isUnique = false;
		do {
			$rs = $this->query("SELECT user_id FROM $this->table_login WHERE unique_string=\"$unique_string\"");
			if ($rs->num_rows == 0){ $isUnique = true; } else { $unique_string = addslashes($this->randStr(18)); }
		} while ($isUnique == false);
		
		$this->query("INSERT INTO $this->table_login (user_id,unique_string,username,pwd,register_time) 
			VALUES($user_id,\"$unique_string\",\"\",\"NOT_SET\",NOW())");
	}
	
	public function getUserIdFromUniqueString($unique_string) {
		$rs = $this->query("SELECT user_id FROM $this->table_login WHERE unique_string=\"".addslashes($unique_string)."\"");
		if ($rs->num_rows != 1) return false;
		$row = $rs->fetch_assoc();
		return intval($row['user_id']);
	}
	
	public function getUniqueString($user_id) {
		$rs = $this->query("SELECT unique_string FROM $this->table_login WHERE user_id=".intval($user_id));
		if ($rs->num_rows != 1) return false;
		$row = $rs->fetch_assoc();
		return $row['unique_string'];	
	}
	
	public function setPassword($user_id, $pwd) {
		global $crypto;
		$user_id = intval($user_id);
		
		if (mb_strlen($pwd,'UTF-8') < $this->_minPwdLen) return array('pwd_tooshort'); 
		if (mb_strlen($pwd,'UTF-8') > $this->_maxPwdLen) return array('pwd_toolong'); 

		// "Krypter" passord
		$pwd = addslashes($crypto->encrypt($pwd));

		// Lagre passord
		$this->query("UPDATE $this->table_login SET pwd=\"$pwd\" WHERE user_id='$user_id'");	
		return array();
	}
	
	function localRedirect($status){
		switch ($status){
			case "LOGIN_OK":
				$this->redirect($this->generateURL('',true));
				break;
			default:
				$_SESSION['brukernavn'] = $_POST['brukernavn'];
				$this->redirect($this->generateURL(array('loginfailed='.$status),true));
		}
		exit();
	}

	function makeContent(){
		$output = "";
		if (isset($_GET['sendlogininfo'])){
			$output .= $this->selectUserForm($_GET['sendlogininfo']);
		} else if (isset($_GET['lost_pass'])){
			$output .= $this->lostPassword($_GET['lost_pass']);
		} else if (isset($_GET['loginfailed'])){
			$output .= "
				<h1>Innloggingen ble avbrutt</h1>
			";
			switch ($_GET['loginfailed']) {
				case "PWD_NOT_CONFIRMED":
					$output .= "
						<h2>Passordet ditt er ikke bekreftet</h2>
						<p>
							Du har ikke bekreftet passordet ditt enda. Du skal ha mottatt en epost med en 
							link som du må trykke på for å bekrefte passordet ditt. Om du ikke har det, ta
							kontakt med peffen din eller webmaster, så kan vi sende den på nytt.
						</p>
					";			
					break;
				case "PWD_NOT_SET":
					$output .= '
						<h2>Du har ikke laget passord enda</h2>
						<p>
							Brukerkontoen din er opprettet, men du må lage ditt eget passord for å kunne logge inn.
							En epost med instruksjoner for å gjøre dette har blitt sendt deg tidligere. Dersom du ikke
							har fått denne kan du trykke på linken under for å få tilsendt en ny.
						</p>
						<p class="headerLinks">
							<a href="'.$this->generateURL('sendlogininfo='.$_SESSION['brukernavn']).'" class="icn readmore" >Send instruksjoner på nytt</a>
						</p>
					';
					break;
				case "ACCOUNT_LOCKED":
					$output .= "
						<h2>Kontoen din er sperret</h2>
						<p>
							Kontoen din er sperret, kanskje pga. misbruk. Ta kontakt med webmaster for å
							få fjernet sperringen.
						</p>
					";
					break;
				case "NO_MEMBERSHIPS":
					$output .= "
						<h2>Manglende tilknytning</h2>
						<p>
							Brukeren din er ikke knyttet til noe medlemskap.
							Dette skyldes antakelig at du har sluttet i speideren.
							Om dette ikke er tilfelle, ta kontakt med peffen din, troppsleder eller webmaster.
						</p>
						<p>
							Hvis du har vært med i gruppen tidligere og er nysgjerrig på hvordan det går med gruppen
							nå, kan vi legge deg til som medlem i en egen gruppe for pensjonerte speidere. 
							Ta kontakt med gruppeleder eller webmaster hvis du er interessert i dette.
						</p>
					";
					break;
				default:
					if (isset($_SESSION['brukernavn'])) {
						$output .= '
							<h2>Brukernavnet eller passordet du skrev inn var feil</h2>
							<p>
								<a href="'.$this->generateURL('sendlogininfo='.$_SESSION['brukernavn']).'">Trykk her</a> 
								hvis du har glemt innloggingsopplysningene dine.
							</p>
						';
						unset($_SESSION['brukernavn']);
					} else {
						$output .= '
							<h2>Brukernavnet eller passordet du skrev inn var feil</h2>
							<p>
								<a href="'.$this->generateURL('sendlogininfo=').'">Trykk her</a> 
								hvis du har glemt innloggingsopplysningene dine.
							</p>
						';					
					}
					break;
			}
		} else if (isset($_GET['passwordsent'])){
			$output .= '
				<h1>Passord sendt!</h1>
				<p>
					Passordet ditt er sendt til '.$_GET['passwordsent'].' og vil antakelig være fremme ganske raskt. 
					NB! Det kan være det havner i mappen "Useriøs epost", "Junk mail" eller lignende. 
					Du bør derfor sjekke disse hvis du ikke får eposten i innboksen din.
				</p>
				<p>
					<a href="'.$this->generateURL("").'">Fortsett surfing</a>
				</p>
			';
		}
		return $output;
	}
	
	/* ___________________________ RETRIEVE PASSWORD STEP 1 _____________________________ */
	
	function selectUserForm($username){
		global $memberdb;
		$def = -1;
		if (!empty($username)) {
			$res = $this->query("SELECT user_id FROM $this->table_login WHERE username=\"".addslashes($username)."\"");
			if ($res->num_rows == 1){
				$row = $res->fetch_assoc();
				$def = $row['user_id'];
			}
		}
		if ($def != -1) {
			if (!$this->hasPassword($def)) {
				$this->redirect("/medlemsliste/medlemmer/$def?resendwelcomemail");				
			}			
			$this->redirect($this->generateURL("lost_pass=$def"));
			$info = '<input type="hidden" name="lost_pass" value="'.$def.'" />';
		} else {
			$info = 'Hvem er du? '.$this->memberdb->generateMemberSelectBox('lost_pass',$def);
		}
		return '
			<h1>Glemt brukernavn og/eller passord</h1>
			<form method="get" action="'.$this->generateURL("").'">
				<p>
					'.$info.'
				</p>
				<p>
					<input type="submit" value="Fortsett">
				</p>
			</form>
		';
	}

	/* ___________________________ RETRIEVE PASSWORD STEP 2 _____________________________ */
	
	function lostPassword($id) {
		if ($id == 0) {
			return $this->notSoFatalError('Du må velge et medlem',array('logError'=>false));
		}
		if (!is_numeric($id)) $this->fatalError("invalid input .3");
		if (!$this->memberdb->isUser($id)) $this->fatalError("invalid input .4");
		$m = $this->memberdb->getMemberById($id);
		if (!$this->hasUsername($id)){
			return '
				<h1>Hjelp til innlogging</h1>
				<h2>Ops, du mangler brukernavn</h2>
				<p>
					Du er registrert i vårt system, men du ikke har ikke blitt tildelt et brukernavn til
					å logge inn med enda. <a href="/kontakt/webmaster" class="icn" style="background-image:url(/images/icns/email.png);">Kontakt webmaster</a> for å få tildelt 
					brukernavn. Vi beklager brydderiet.
				</p>
			';			
		}
		if (!$this->hasPassword($id)){
			return '
				<h1>Hjelp til innlogging</h1>
				<h2>Du har ikke laget passord enda</h2>
				<p>
					Brukerkontoen din er opprettet, men du må lage ditt eget passord for å kunne logge inn.
					En epost med instruksjoner for å gjøre dette har blitt sendt deg tidligere. Dersom du ikke
					har fått denne kan du trykke på linken under for å få tilsendt en ny.
				</p>
				<p class="headerLinks">
				<a href="'.$this->generateURL('sendlogininfo='.$this->getUsername($id)).'" class="icn readmore" >Send instruksjoner på nytt</a>
				</p>
			';			
		}
		$output = '
			<h1>Glemt brukernavn og/eller passord</h1>
			<form method="post" action="'.$this->generateURL(array('noprint=true','sendpwd=true')).'">
				<p>
					Vi kan sende brukernavn og passord til e-postadressen registrert på
					din medlemsprofil. 
				</p>
				<p>
					Dersom denne adressen ikke virker lenger, kan vi
					sende det til en av dine registrerte foresatte. Dersom du ikke
					har noen registrerte foresatte, <a href="/kontakt/">ta kontakt med webmaster</a>,
					som kan legge inn din nye e-postadresse.
				</p>
				<p>
					Send innloggingsopplysninger for '.$m->fullname.' til:
				</p>
				<p>
					&nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="sendtil" id="egen" value="'.$m->ident.'" checked="checked" /><label for="egen">'.$m->fullname.'</label><br />
		';
		foreach ($m->guardians as $g) {
			$gu = $this->memberdb->getMemberById($g);
			$output .= "
					&nbsp;&nbsp;&nbsp;&nbsp; <input type='radio' name='sendtil' id='foresatt$gu->ident' value='$gu->ident' /><label for='foresatt$gu->ident'>$gu->fullname (foresatt)</label><br />
			";
		}
		$output .= '
				</p>
				<p>
					<img src="/verify.php" alt="Verification image" /><br />
					Skriv inn teksten du ser i bildet over 
					(du trenger ikke tenke på store og små bokstaver):<br />
					<input name="random" type="text" value="" />
				</p>
				<p>
					<input type="hidden" name="user_id" value="'.$id.'" />
					<input type="submit" value="Send innloggingsopplysninger" />
				</p>
			</form>
		';
		
		return $output;
		
	}
	
	/* ___________________________ RETRIEVE PASSWORD STEP 3 _____________________________ */

	function sendPassword(){
		global $crypto;
		
		if (!isset($_POST['random'])) $this->fatalError("invalid input .1.0");
		if (!isset($_POST['user_id'])) $this->fatalError("invalid input .1.1");
		if (!isset($_POST['sendtil'])) $this->fatalError("invalid input .1.2");
		$id = intval($_POST['user_id']);
		$sendtil = intval($_POST['sendtil']);
		$random = $_POST['random'];
		if (empty($random)) $this->fatalError("invalid input .2.0");
		if (!is_numeric($id)) $this->fatalError("invalid input .2.1");
		if (!is_numeric($sendtil)) $this->fatalError("invalid input .2.2");
		if (!$this->memberdb->isUser($id)) $this->fatalError("invalid input .3.1");
		if (!$this->memberdb->isUser($sendtil)) $this->fatalError("invalid input .3.2");
		$m = $this->memberdb->getMemberById($id);
		$g = $this->memberdb->getMemberById($sendtil);
		
		if (strtoupper($random) != $_SESSION['stv18bg']) {
			$this->redirect($this->generateURL("lost_pass=$id"), "Du skrev ikke inn riktig tekst fra kodebildet. Prøv igjen.", "error");
		}
		
		if ($sendtil != $id) {
			if (!in_array($sendtil,$m->guardians)) $this->fatalError("invalid input .4");
		}		
		
		$from_name = $this->mailSenderName;
		$from_addr = $this->mailSenderAddr;

		$rcptmail = $g->email;
		$rcptname = $g->fullname;
		
		$res = $this->query("SELECT username,pwd FROM $this->table_login WHERE user_id=$id");
		if ($res->num_rows != 1) $this->fatalError('Ugyldig antall personer eksisterer for gitt kriterium');
		$row = $res->fetch_assoc();
		if ($row['pwd'] == 'NOT_SET') {
			$this->redirect($this->generateURL('loginfailed=PWD_NOT_SET'));
		}		
		$subject = "Innloggingsopplysninger for ".$this->server_name;
		$url_root = 'https://'.$_SERVER['SERVER_NAME'].ROOT_DIR.'/';
		$plainBody = "Navn: ".$m->fullname."\nBrukernavn: ".stripslashes($row['username'])."\r\n".
			"Passord: ".$crypto->decrypt(stripslashes($row['pwd'])).
			"\r\n\r\n".
			"-- \r\nDette er en auto-generert utsendelse.\r\n$url_root";
		

        // Send mail
		$mail = array(
		    'sender_name' => $from_name,
		    'sender_email' => $from_addr,
		    'rcpt_name' => $rcptname,
		    'rcpt_email' => $rcptmail,
		    'subject' => $subject,
		    'plain_body' => $plainBody
		);
		
		$mailer = $this->initialize_mailer();
		$res = $mailer->add_to_queue($mail);
		if (empty($res['errors'])) {
		    $res = $mailer->send_from_queue();
		    if ($res['mail_sent'] == 'true') {
        		$this->redirect($this->generateURL("passwordsent=$rcptmail"));        
		    } else {
		        $this->fatalError("Mailen kunne ikke sendes. Kanskje vi har feil epostadresse på deg. Ta kontakt med webmaster.");
		    }
		}		
		
	}

	/* ___________________________ UTILITY FUNCTIONS _____________________________ */

	function setGlobalOptions() {

		$res = $this->query(
			"SELECT 
				$this->table_globaloptions.name,
				$this->table_globaloptions.value,
				$this->table_globaloptions.prefix,
				$this->table_globaloptions.required
			FROM 
				$this->table_globaloptions
			"
		);
		while ($row = $res->fetch_assoc()) {
			$name = stripslashes($row['name']);
			$prefix = stripslashes($row['prefix']);
			$value = stripslashes($row['value']);
			$this->$name = $prefix.$value;
		}
		return true;
	}
	
	function randStr($length){ 
		// Lag en string basert på tilfeldige bokstaver (maks 36 bokstaver). Brukes til genering av brukerid.
		if ( $length > 36 ){ 
			return "ERROR"; 
		} else { 
			$str = md5(time());
			$cutoff = 31 - $length; 
			$start = rand(0, $cutoff); 
			return substr($str, $start, $length); 
		} 
	} 
	
	function printNoAccessFull(){
		// Ingen tilgang. Hovedtemplate er ikke skrevet ut, så vi skriver ut en hel side.
		print("<html><head><title>Ingen tilgang</title></head><body bgcolor=\"#CCCCCC\"><div style=\"background:#FFFFFF; border:#FF0000 1px solid; margin:10px; padding:10px; color:#FF0000\"><table><tr><td valign='top' style=\"font:14px 'Tahoma';  color:#FF0000;\"><b>Beklager, ingen tilgang</b><br /><br />Denne siden kunne ikke vises, enten fordi du ikke er logget inn eller fordi du av andre grunner ble nektet å vise den.<br /><br /><a href=\"".$_SERVER['HTTP_REFERER']."\">Tilbake</a></td><td valign='top'><img src='images/stop.gif' width='150' height='140' alt='Stop!' /></td></tr></table></div></body></html>");
	}
	
	function printNoAccess(){
		// Ingen tilgang. Hovedtemplate er skrevet ut, så vi skriver bare ut en melding.
		if ($this->loggedin){
			return '
				<div class="errorBox" style="background:#fff;">
				<div style="background:url(/images/login.jpg) no-repeat; padding-left:180px; padding-top:0px; padding-bottom: 30px; padding-right: 20px; font:14px \'Tahoma\';">
					<img src="/images/stop.gif" width="150" height="140" alt="Stop!" style="float:right;" />	
					<p>
						<strong>Beklager, ingen tilgang</strong>
					</p>
					<p>
						Du har prøvd å vise en side som du ikke har tilgang til. Siden kan tilhøre en bestemt gruppe eller et bestemt medlem. 
						Om du mener du har fått denne meldingen urettmessig, ta gjerne kontakt.
					</p>
					<div style="clear:both;"></div>
				</div></div>
				<script type="text/javascript">
				//<![CDATA[
					$(document).ready(function() {
						Nifty("div.errorBox");
					});
				//]]>
				</script>
				';
		} else {
			return $this->printNoAccessNotLoggedInDlg();
		}
			
	}
	
	/* Public */
	function printNoAccessNotLoggedInDlg() {
		return '
				<div class="whiteInfoBox">
					<div class="inner" style="background:url(/images/icns/lock.png) no-repeat 22px 20px;">
						<p>
							<strong>Du må logge inn for å vise denne siden</strong>
						</p>
						<p>
							Logg inn med brukernavn og passord i boksen øverst på siden. 
							Hvis du ikke har brukernavn og passord kan du 
							<a href="/registrering">registrere deg her</a>. Hvis du har registrert deg, 
							men mangler brukernavn/passord, kan du
							<a href="/kontakt/webmaster">kontakte webmaster</a>.
							Hvis du har glemt brukernavn og/eller passord, 
							<a href="/?sendlogininfo">trykk her</a>.
						</p>
					</div>
				</div>
				<script type="text/javascript">
				//<![CDATA[
					$(document).ready(function() {
						Nifty("div.whiteInfoBox");
					});
				//]]>
				</script>
		';
	}

	function outputLoginField(){
		global $memberdb;
		
		if ($this->loggedin == true){
			$url = '/medlemsliste/medlemmer/'.$this->_loggedinUser['id'];
			if (!empty($this->_loggedinUser['slug'])) $url = '/medlemsliste/'.$this->_loggedinUser['slug'];
			$role = $this->getUserRole($this->_loggedinUser['id']);
			$roleStr = in_array($role['role'],array('annet','ukjent')) ? '' : $role['role'];
			if (in_array($role['roleAbbr'],array('SP','SM'))) {
				if ($role['group'] > 0) 
					$grp = "<a href=\"".$memberdb->getGroupUrl($role['group'])."\">".$memberdb->getGroupById($role['group'])->caption."</a>"; 
				else 
					$grp = "ukjent";
				$roleStr = "$roleStr i $grp";
			}			
			if (!empty($roleStr)) $roleStr = ", $roleStr";
			return '
				<div id="login_info" class="hidefromprint">
					Innlogget som
					<a href="'.$url.'">'.$this->_loggedinUser['name'].'</a>'.$roleStr.'.
				    [<a href="'.$this->generateURL(array('dologout=true','noprint=true')).'">Logg ut</a>]
				</div>
			';	
		} else {
			if (isset($_GET['goto'])){
				$goto = $_GET['goto'];
			} else {
				$goto = "";
			}
			return '
				<form id="login_form" method="post" action="'.$this->generateURL(array("noprint=true","dologin=true")).'">
					<div style="float:left;">
						<label for="brukernavn">
							Bruker:	<input type="text" name="brukernavn" id="brukernavn" class="txt" />
						</label>
						<label for="passord">
							Passord: <input type="password" name="passord" id="passord" class="txt" />
						</label>
					</div>
					<div style="float:left;" id="huskdiv">
						<label for="huskmeg" id="huskmeglabel">
							<input type="checkbox" name="huskmeg" id="huskmeg" />
							Husk meg
						</label>
						<input type="submit" id="logg_inn_btn" value="Logg inn" class="btn" />
					</div>
					<div style="float:left; margin-left:10px;padding:4px;">
						<a href="/registrering">Ny bruker?</a> <br />
						<a href="'.$this->generateURL("sendlogininfo").'">Problemer?</a> 
					</div>
				</form>
				<script type="text/javascript">
			    //<![CDATA[	
			    	$(document).ready(function() {
						// var tt1 = new YAHOO.widget.Tooltip("tt1", { 
						// 	context:"huskmeglabel",
      //                       showdelay: 0,
      //                       zIndex: 10,
						// 	autodismissdelay: 20000,
						// 	text:"Krysser du av her forblir du innlogget i 30 dager<br /> med mindre du logger ut. Du bør derfor ikke krysse<br /> av her hvis denne maskinen også brukes av andre." 
						// });
			    	});
				//]]>
				</script>
				
				';
		}
	}
}

?>
