<?

class regschema extends base {

	var $getvars = array("regconfirm","regview","reg","regid","reglist","regprocess","regdoprocess","regdelete");

	var $allow_acceptregistrations = false;
	var $allow_listregistrations = false;
	var $memberdb;
	var $member_url;
	var $group_url;
	
	var $template_dir 					= "../includes/templates/";
	var $template_start 				= "regschema_start.html";
	var $template_memberdetails	 		= "regschema_memberdetails.html";
	var $template_groupdetails_fast 	= "regschema_memberdetails_fastmedlem.html";
	var $template_memberdetails_self 	= "regschema_memberdetails_self.html";
	var $template_groupdetails_self 	= "regschema_groupdetails_self.html";
	var $template_memberdetails_retired	= "regschema_memberdetails_retired.html";
	var $template_firstparent			= "regschema_firstparent.html";
	var $template_secondparent			= "regschema_secondparent.html";
	var $template_review				= "regschema_review.html";
	var $template_membership			= "regschema_membership.html";
	var $template_oldmemberships		= "regschema_oldmemberships.html";
	var $template_oldfriends			= "regschema_oldfriends.html";
	var $template_confirm_self			= "regschema_confirm_self.html";
	var $template_confirm_parents		= "regschema_confirm_parents.html";
	var $template_confirm_retired		= "regschema_confirm_retired.html";
	
	var $weekDays = array("søndag","mandag","tirsdag","onsdag","torsdag","fredag","lørdag");
	var $months = array("januar","februar","mars","april","mai","juni","juli","august","september","oktober","november","desember");
	
	var $tablename = "registrations";
	var $table_addresslist = "addresslist";
	var $grouptable = "groups";
	
	var $show_no_group_preference = false;
	
	var $ids = array(
		'output_start' => 001,
		'submit_start' => 822,
		'output_memberdetails' => 155,
		'submit_memberdetails' => 309,
		'output_parent1' => 447,
		'submit_parent1' => 104,
		'output_parent2' => 539,
		'submit_parent2' => 627,
		'output_membership' => 360,
		'submit_membership' => 325,
		'output_oldmemberships' => 943,
		'submit_oldmemberships' => 962,
		'output_oldfriends' => 749,
		'submit_oldfriends' => 291,

		'output_confirm_self' => 827,
		'output_confirm_parents' => 898,
		'output_confirm_retired' => 811,
		'submit_confirm' => 873,

		'list_registrations' => 112,
		'view_registration' => 199,
		
		'output_process' => 724,
		'submit_process' => 196,
		'delete_reg' => 911

		
	);
	
	function regschema() {
		$this->tablename = DBPREFIX.$this->tablename;
		$this->grouptable = DBPREFIX.$this->grouptable;
		$this->table_addresslist = DBPREFIX.$this->table_addresslist;
	}
	
	function initialize() {
		$this->initialize_base();
	}

	function run() {
	
		$this->initialize();
		
		if (!isset($_GET['reg'])) $_GET['reg'] = $this->ids['output_start'];
		if (!is_numeric($_GET['reg'])) return $this->notSoFatalError("invalid input .1");
		
		$action = $_GET['reg'];
		
		if (isset($_GET['reglist'])) $action = $this->ids['list_registrations'];
		if (isset($_GET['regview'])) $action = $this->ids['view_registration'];
		if (isset($_GET['regprocess'])) $action = $this->ids['output_process'];
		if (isset($_GET['regdoprocess'])) $action = $this->ids['submit_process'];
		if (isset($_GET['regdelete'])) $action = $this->ids['delete_reg'];
		
		switch ($action) {
			case $this->ids['submit_start']:
				return $this->submitStart();
				break;
			case $this->ids['output_memberdetails']:
				return $this->outputMemberDetails();
				break;
			case $this->ids['submit_memberdetails']:
				return $this->submitMemberDetails();
				break;
			case $this->ids['output_parent1']:
				return $this->outputParent1();
				break;
			case $this->ids['submit_parent1']:
				return $this->submitParent1();
				break;
			case $this->ids['output_parent2']:
				return $this->outputParent2();
				break;
			case $this->ids['submit_parent2']:
				return $this->submitParent2();
				break;
			case $this->ids['output_membership']:
				return $this->outputMembership();
				break;
			case $this->ids['submit_membership']:
				return $this->submitMembership();
				break;
			case $this->ids['output_oldmemberships']:
				return $this->outputOldMemberships();
				break;
			case $this->ids['submit_oldmemberships']:
				return $this->submitOldMemberships();
				break;
			case $this->ids['output_oldfriends']:
				return $this->outputOldFriends();
				break;
			case $this->ids['submit_oldfriends']:
				return $this->submitOldFriends();
				break;

			case $this->ids['output_confirm_self']:
				return $this->outputConfirmSelf();
				break;
			case $this->ids['output_confirm_parents']:
				return $this->outputConfirmParents();
				break;
			case $this->ids['output_confirm_retired']:
				return $this->outputConfirmRetired();
				break;
			case $this->ids['submit_confirm']:
				return $this->submitConfirm();
				break;
				
			case $this->ids['list_registrations']:
				return $this->listRegistrations();
				break;
			case $this->ids['view_registration']:
				return $this->viewRegistration($_GET['regid']);
				break;
			case $this->ids['output_process']:
				return $this->processRegistrationForm($_GET['regid']);
				break;
			case $this->ids['submit_process']:
				return $this->processRegistration($_GET['regid']);
				break;
			case $this->ids['delete_reg']:
				return $this->deleteRegistration($_GET['regdelete']);
				break;

			default:
				return $this->outputStart();
				break;
		}
		
	}
	
	/****************************************************************************************************
		START REGISTRATION
		**************************************************************************************************/

	
	function outputStart() {
		
		$template = file_get_contents($this->template_dir.$this->template_start);
		
		$r1a[0]  = '%formaction%';		$r2a[0]  = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_start']));
		$r1a[1]  = '%image_dir%';		$r2a[1]  = $this->image_dir;

		return str_replace($r1a, $r2a, $template);
		
	}
	
	function submitStart() {
	
		if (!isset($_POST['kat']) || empty($_POST['kat'])) {
			$this->redirect($this->generateURL(""),
				"Du må velge en kategori før du går videre!"
			);
		}
		$kat = addslashes(htmlspecialchars($_POST['kat']));
		$now = time();
		$ip = ip2long($_SERVER['REMOTE_ADDR']);
		$this->query("INSERT INTO $this->tablename 
			(kategori, created, editable,ip) 
			VALUES
			('$kat', '$now', 1,'$ip')
		");
			
		$id = $this->insert_id();
		
		$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_memberdetails']),true));
		
	}
	
	/****************************************************************************************************
		GATHER MEMBER-DETAILS
		**************************************************************************************************/

	function makeDateField($identifier, $value, $showWeekday = true){
		if (empty($value)) {
			$currentMonth = 0;
			$currentDay = 0; 
			$currentYear = 0; 
			$currentDateJs = '0';
		} else {
			$currentMonth = strftime('%m',$value); 
			$currentDay = strftime('%d',$value); 
			$currentYear = strftime('%Y',$value); 
			$currentDateJs = '{ day: '.$currentDay.', month: '.$currentMonth.', year: '.$currentYear.' }';
		}
		$months = '';
		for ($i = 1; $i < 12; $i++) {
			$n = $i; if ($n < 10) $n = '0'.$n;
			$sel = ($n == $currentMonth) ? " selected='selected'" : "";
			$months .= "<option value='$n'$sel>$n</option>\n";
		}
		$days = '';
		for ($i = 1; $i < 31; $i++) {
			$n = $i; if ($n < 10) $n = '0'.$n;
			$sel = ($n == $currentDay) ? " selected='selected'" : "";
			$days .= "<option value='$n'$sel>$n</option>\n";
		}
		$year = strftime('%Y',$value);
		return '
			<span class="field" id="'.$identifier.'" style="display:inline-block;vertical-align:middle;">
				<select id="'.$identifier.'_month" name="'.$identifier.'_month">
					'.$months.'
				</select>	
				<select id="'.$identifier.'_day" name="'.$identifier.'_day">
					'.$days.'
				</select>
				<input type="text" id="'.$identifier.'_year" name="'.$identifier.'_year" value="'.$currentYear.'" style="width:50px;">
			</span>
			
		';
		
	}	
	
	function fetchAdrList() {

		$code = "<option value='0'>Velg en adresse</option>\n";
		$res = $this->query("SELECT * FROM $this->table_addresslist ORDER BY street,streetno");
		while ($row = $res->fetch_assoc()) {
			$code .= "<option value='".$row['kundenr']."'>".stripslashes($row['street'])." ".stripslashes($row['streetno'])."</option>\n";
		}
		return $code;
		
	}

	function outputMemberDetails() {
		

		$id = $_GET['regid'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 

		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
	
		$row = $res->fetch_assoc();	

		$addressList = "";
		switch ($row['kategori']) {
			case "GR":
				$template = file_get_contents($this->template_dir.$this->template_groupdetails_self);
				break;
			case "FA":
				$template = file_get_contents($this->template_dir.$this->template_groupdetails_fast);
				$addressList = $this->fetchAdrList();
				break;
			case "PE":
				$template = file_get_contents($this->template_dir.$this->template_memberdetails_retired);
				break;
			default:
				$template = file_get_contents($this->template_dir.$this->template_memberdetails_self);
				break;				
		}
		
		$r1a   = array(); 				$r2a   = array();
		$r1a[] = '%image_dir%';			$r2a[] = $this->image_dir;
		$r1a[] = '%id%';				$r2a[] = $id;
		$r1a[] = '%firstname%';			$r2a[] = stripslashes($row['s_firstname']);
		$r1a[] = '%middlename%';		$r2a[] = stripslashes($row['s_middlename']);
		$r1a[] = '%lastname%';			$r2a[] = stripslashes($row['s_lastname']);
		$r1a[] = '%street%';			$r2a[] = stripslashes($row['s_street']);
		$r1a[] = '%streetno%';			$r2a[] = stripslashes($row['s_streetno']);
		$r1a[] = '%postno%';			$r2a[] = stripslashes($row['s_postno']);
		$r1a[] = '%city%';				$r2a[] = stripslashes($row['s_city']);
		$r1a[] = '%homephone%';			$r2a[] = stripslashes($row['s_homephone']);
		$r1a[] = '%cellular%';			$r2a[] = stripslashes($row['s_cellular']);
		$r1a[] = '%email%';				$r2a[] = stripslashes($row['s_email']);
		$r1a[] = '%adressList%';		$r2a[] = $addressList;
		
		$selectedGroup = $row['s_group'];

		if (isset($_GET['regconfirm'])){
			$born_date = strtotime($row['s_birthday']);
			if ($born_date < strtotime('1900-01-01')) $born_date = 0;
			$r1a[] = '%formaction%';	$r2a[] = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_memberdetails'],"regconfirm"));
		} else {			
			$born_date = 0;		
			$r1a[]  = '%formaction%';	$r2a[]  = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_memberdetails']));
		}
		
		
		$born_str = '<em>Velg dato</em>';
		$field_born = $this->makeDateField("bursdag", $born_date, false);
		$max_date_js = strftime('%m/%d/%Y',time()-5*365*24*3600);
		$born_date_js = $born_date ? strftime('{ day:%e, month:%m, year:%Y }',$born_date) : 0;
		$field_born .= '<script type="text/javascript">
		    //<![CDATA[	
				
				function onYuiLoaderComplete() {
					YAHOO.util.Event.onContentReady("bursdag", function() {
						(new BG18.datePicker("bursdag", { selectedDate: '.$born_date_js.', maxDate: "'.$max_date_js.'" } )).init();
					});
				}

				loader.require("button","calendar");
				loader.insert();
			
			//]]>
			</script> Tips: trykk på årstallet for å skrive inn et annet år.';
		/*
		$field_born = "<a href=\"nojs.html\" onclick=\"cal3.showCalendar('anchor3',getObject('bursdag').value); return false;\" 
							name=\"anchor3\" id=\"anchor3\">
						<span id=\"cal3var\" style=\"width: 200px; border: 1px solid #888; padding: 3px; \">
							".date("j",$startDate).". ".$this->months[date("m",$startDate)-1]." ".date("Y",$startDate)."
							<input type=\"hidden\" name=\"bursdag\" id=\"bursdag\" value=\"".date("Y-m-d",$startDate)."\" />
						</span>
					</a>
		";*/
		
		$res = $this->query("SELECT 
				id, caption 
			FROM 
				$this->grouptable 
			WHERE 
				kategori='".$row['kategori']."'"
		);
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			$field_group = "<input type='hidden' name='group' id='group' value='".$row['id']."' />
				".stripslashes($row['caption']);
			$field_groupvisibility = "none";
		} else {
			$field_group = "<select name='group' id='group'> ";
			if ($this->show_no_group_preference)
				$field_group .= "<option value='-1'>Ingen preferanse</option> ";
			while ($row = $res->fetch_assoc()){
				$sel = (($selectedGroup == $row['id']) ? " selected='selected'" : ""); 
				$field_group .= "<option value='".$row['id']."'$sel>".stripslashes($row['caption'])."</option> ";
			}
			$field_group .= "</select> ";
			$field_groupvisibility = "block";
		}	
		$r1a[]  = '%field_born%';			$r2a[]  = $field_born;
		$r1a[]  = '%field_group%';			$r2a[]  = $field_group;
		$r1a[]  = '%group_visibility%';		$r2a[]  = $field_groupvisibility;
		
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function submitMemberDetails() {
	
		$firstname = addslashes(htmlspecialchars($_POST['firstname']));
		$middlename = addslashes(htmlspecialchars($_POST['middlename']));
		$lastname = addslashes(htmlspecialchars($_POST['lastname']));
		$homephone = addslashes(htmlspecialchars($_POST['homephone']));
		$cellular = addslashes(htmlspecialchars($_POST['cellular']));
		$email = addslashes(htmlspecialchars($_POST['email']));

		if (intval($_POST['bursdag_year']) < 1900) {
			$birthday = '0000-00-00';
		} else { 
			$birthday = $_POST['bursdag_year'].'-'.$_POST['bursdag_month'].'-'.$_POST['bursdag_day'];
			if (strlen($birthday) != 10) $birthday = '0000-00-00';
		}		
		$birthday = addslashes(htmlspecialchars($birthday));
		$group = addslashes(htmlspecialchars($_POST['group']));
		if (isset($_POST['address'])) {
			$address_id = addslashes(htmlspecialchars($_POST['address']));
			$res = $this->query("SELECT street,streetno,postno,city FROM $this->table_addresslist WHERE kundenr='$address_id'");
			if ($res->num_rows != 1) return $this->notSoFatalError("Adressen ble ikke funnet! (2)"); 
			$row = $res->fetch_assoc();
			$street = addslashes(stripslashes($row['street']));
			$streetno = addslashes(stripslashes($row['streetno']));
			$postno = addslashes(stripslashes($row['postno']));
			$city = addslashes(stripslashes($row['city']));		
		} else {
			$address_id = "0";
			$street = addslashes(htmlspecialchars($_POST['street']));
			$streetno = addslashes(htmlspecialchars($_POST['streetno']));
			$postno = addslashes(htmlspecialchars($_POST['postno']));
			$city = addslashes(htmlspecialchars($_POST['city']));
		}
		$now = time();

		$id = $_POST['id'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen $id eksisterer ikke eller er låst for redigering (1)"); 
		$res = $this->query("SELECT confirmed, kategori FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)");
		$row = $res->fetch_assoc();
		$confirmed = ($row['confirmed'] != '0');
		$kat = $row['kategori'];
	
		$this->query("UPDATE $this->tablename 
			SET
				s_firstname = '$firstname',
				s_middlename = '$middlename',
				s_lastname = '$lastname',
				s_street = '$street',
				s_streetno = '$streetno',
				s_postno = '$postno',
				s_city = '$city',
				s_homephone = '$homephone',
				s_cellular = '$cellular',
				s_email = '$email',
				s_birthday = '$birthday',
				s_group = '$group',
				s_membership = 'betalende',
				s_address_id = '$address_id'
			WHERE id='$id'"
		);

		if (isset($_GET['regconfirm'])){
			if ($confirmed){
				$this->redirect($this->generateURL(array("regid=$id","regprocess")));
			} else {
				switch ($kat) {
					case "PE":
						$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_confirm_retired']),true));
						break;
					case "SM":
					case "SP":
						$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_confirm_parents']),true));
						break;
					default:
						$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_confirm_self']),true));
						break;				
				}
			}
		}
		switch ($kat) {
			case "PE":
				$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_membership']),true));
				break;
			case "SM":
			case "SP":
				$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_parent1']),true));
				break;
			default:
				$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_confirm_self']),true));
				break;				
		}
		
	}
	
	/****************************************************************************************************
		GATHER PARENT-INFO
		**************************************************************************************************/

	
	function outputParent1() {

		$template = file_get_contents($this->template_dir.$this->template_firstparent);
		
		$id = $_GET['regid'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 

		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();	
		
		$res2 = $this->query("SELECT 
				id, caption 
			FROM 
				$this->grouptable 
			WHERE 
				kategori='FO'"
		);
		if ($res2->num_rows != 1) {
			$this->outputForm2_2();
			return;
		}
		$row2 = $res2->fetch_assoc();
		$gruppe = $row2['id'];
		
		
		$r1a = array(); $r2a = array();
		$r1a[] = '%image_dir%';			$r2a[] = $this->image_dir;
		$r1a[] = '%id%';				$r2a[] = $id;
		$r1a[] = '%group%';				$r2a[] = $gruppe;
		$r1a[] = '%firstname%';			$r2a[] = stripslashes($row['p1_firstname']);
		$r1a[] = '%middlename%';		$r2a[] = stripslashes($row['p1_middlename']);
		$r1a[] = '%lastname%';			$r2a[] = stripslashes($row['p1_lastname']);
		$r1a[] = '%cellular%';			$r2a[] = stripslashes($row['p1_cellular']);
		$r1a[] = '%email%';				$r2a[] = stripslashes($row['p1_email']);
		
		if (isset($_GET['regconfirm'])){
			$r1a[] = '%formaction%';	$r2a[] = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_parent1'],"regconfirm"));
		} else {			
			$r1a[] = '%formaction%';	$r2a[] = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_parent1']));
		}
		if (empty($row['p1_street'])) {
			$r1a[] = '%street%';		$r2a[] = stripslashes($row['s_street']);
			$r1a[] = '%streetno%';		$r2a[] = stripslashes($row['s_streetno']);
			$r1a[] = '%postno%';		$r2a[] = stripslashes($row['s_postno']);
			$r1a[] = '%city%';			$r2a[] = stripslashes($row['s_city']);
			$r1a[] = '%homephone%';		$r2a[] = stripslashes($row['s_homephone']);
		} else {
			$r1a[] = '%street%';		$r2a[] = stripslashes($row['p1_street']);
			$r1a[] = '%streetno%';		$r2a[] = stripslashes($row['p1_streetno']);
			$r1a[] = '%postno%';		$r2a[] = stripslashes($row['p1_postno']);
			$r1a[] = '%city%';			$r2a[] = stripslashes($row['p1_city']);
			$r1a[] = '%homephone%';		$r2a[] = stripslashes($row['p1_homephone']);
		}
		
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function submitParent1() {
	
		$id = $_POST['id'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 
		
		$res = $this->query("SELECT confirmed FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)");
		$row = $res->fetch_assoc();
		$confirmed = ($row['confirmed'] != '0');
		
		$firstname = addslashes(htmlspecialchars($_POST['firstname']));
		$middlename = addslashes(htmlspecialchars($_POST['middlename']));
		$lastname = addslashes(htmlspecialchars($_POST['lastname']));
		$street = addslashes(htmlspecialchars($_POST['street']));
		$streetno = addslashes(htmlspecialchars($_POST['streetno']));
		$postno = addslashes(htmlspecialchars($_POST['postno']));
		$city = addslashes(htmlspecialchars($_POST['city']));
		$homephone = addslashes(htmlspecialchars($_POST['homephone']));
		$cellular = addslashes(htmlspecialchars($_POST['cellular']));
		$email = addslashes(htmlspecialchars($_POST['email']));
		$group = addslashes(htmlspecialchars($_POST['group']));
		$now = time();
		
		$this->query("UPDATE $this->tablename 
			SET
				p1_firstname = '$firstname',
				p1_middlename = '$middlename',
				p1_lastname = '$lastname',
				p1_street = '$street',
				p1_streetno = '$streetno',
				p1_postno = '$postno',
				p1_city = '$city',
				p1_homephone = '$homephone',
				p1_cellular = '$cellular',
				p1_email = '$email',
				p1_group = '$group'
			WHERE id='$id'"
		);
		
		if (isset($_GET['regconfirm'])){
			if ($confirmed){
				$this->redirect($this->generateURL(array("regid=$id","regprocess"),true));
			} else {
				$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_confirm_parents']),true));
			}
		} else {
			$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_parent2']),true));
		}
		
	}
	
	function outputParent2() {

		$template = file_get_contents($this->template_dir.$this->template_secondparent);
		
		$id = $_GET['regid'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)");

		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)");
		$row = $res->fetch_assoc();
		
		$res2 = $this->query("SELECT 
				id, caption 
			FROM 
				$this->grouptable 
			WHERE 
				kategori='FO'"
		);
		if ($res2->num_rows != 1) {
			$this->notSoFatalError("Det eksisterer ikke en foreldregruppe tilknyttet denne enheten.");
			return;
		}
		$row2 = $res2->fetch_assoc();
		$gruppe = $row2['id'];
		
		
		$r1a = array(); $r2a = array();
		$r1a[] = '%image_dir%';			$r2a[] = $this->image_dir;
		$r1a[] = '%id%';				$r2a[] = $id;
		$r1a[] = '%group%';				$r2a[] = $gruppe;
		$r1a[] = '%firstname%';			$r2a[] = stripslashes($row['p2_firstname']);
		$r1a[] = '%middlename%';		$r2a[] = stripslashes($row['p2_middlename']);
		$r1a[] = '%lastname%';			$r2a[] = stripslashes($row['p2_lastname']);
		$r1a[] = '%cellular%';			$r2a[] = stripslashes($row['p2_cellular']);
		$r1a[] = '%email%';				$r2a[] = stripslashes($row['p2_email']);
		$r1a[] = '%ignore%';			$r2a[] = ($row['p2_ignore'] == '1') ? "checked='checked'" : "";
		$r1a[] = '%ignore_block%';		$r2a[] = ($row['p2_ignore'] == '1') ? "display:none;" : "";
		
		if (isset($_GET['regconfirm'])){
			$r1a[] = '%formaction%';	$r2a[] = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_parent2'],"regconfirm"));
		} else {			
			$r1a[] = '%formaction%';	$r2a[] = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_parent2']));
		}
		if (empty($row['p2_street'])) {
			$r1a[] = '%street%';		$r2a[] = stripslashes($row['s_street']);
			$r1a[] = '%streetno%';		$r2a[] = stripslashes($row['s_streetno']);
			$r1a[] = '%postno%';		$r2a[] = stripslashes($row['s_postno']);
			$r1a[] = '%city%';			$r2a[] = stripslashes($row['s_city']);
			$r1a[] = '%homephone%';		$r2a[] = stripslashes($row['s_homephone']);
		} else {
			$r1a[] = '%street%';		$r2a[] = stripslashes($row['p2_street']);
			$r1a[] = '%streetno%';		$r2a[] = stripslashes($row['p2_streetno']);
			$r1a[] = '%postno%';		$r2a[] = stripslashes($row['p2_postno']);
			$r1a[] = '%city%';			$r2a[] = stripslashes($row['p2_city']);
			$r1a[] = '%homephone%';		$r2a[] = stripslashes($row['p2_homephone']);
		}
		
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function submitParent2() {
	
		$id = $_POST['id'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 
		
		$res = $this->query("SELECT confirmed FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();
		$confirmed = ($row['confirmed'] != '0');
		
		$firstname = addslashes(htmlspecialchars($_POST['firstname']));
		$middlename = addslashes(htmlspecialchars($_POST['middlename']));
		$lastname = addslashes(htmlspecialchars($_POST['lastname']));
		$street = addslashes(htmlspecialchars($_POST['street']));
		$streetno = addslashes(htmlspecialchars($_POST['streetno']));
		$postno = addslashes(htmlspecialchars($_POST['postno']));
		$city = addslashes(htmlspecialchars($_POST['city']));
		$homephone = addslashes(htmlspecialchars($_POST['homephone']));
		$cellular = addslashes(htmlspecialchars($_POST['cellular']));
		$email = addslashes(htmlspecialchars($_POST['email']));
		$group = addslashes(htmlspecialchars($_POST['group']));
		$ignore = (isset($_POST['ignore']) && ($_POST['ignore'] == 'on')) ? 1 : 0;
		$now = time();
		
		$this->query("UPDATE $this->tablename 
			SET
				p2_ignore = '$ignore',
				p2_firstname = '$firstname',
				p2_middlename = '$middlename',
				p2_lastname = '$lastname',
				p2_street = '$street',
				p2_streetno = '$streetno',
				p2_postno = '$postno',
				p2_city = '$city',
				p2_homephone = '$homephone',
				p2_cellular = '$cellular',
				p2_email = '$email',
				p2_group = '$group'
			WHERE id='$id'"
		);
		
		if ($confirmed){
			$this->redirect($this->generateURL(array("regid=$id","regprocess"),true));
		} else {
			$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_confirm_parents']),true));
		}
		
	}
	
	/****************************************************************************************************
		GATHER EXTRA INFO FOR RETIRED SCOUTS
		**************************************************************************************************/
	
	function outputMembership() {

		$template = file_get_contents($this->template_dir.$this->template_membership);
		
		$id = $_GET['regid'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 

		$res = $this->query("SELECT s_membership FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)");
		$row = $res->fetch_assoc();
		
		if (isset($_GET['regconfirm'])) {
			$r1a[0]  = '%formaction%';		$r2a[0]  = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_membership'],"regconfirm"));
		} else {
			$r1a[0]  = '%formaction%';		$r2a[0]  = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_membership']));		
		}
		$r1a[1]  = '%id%';					$r2a[1]  = stripslashes($id);
		$r1a[2]  = '%image_dir%';			$r2a[2]  = $this->image_dir;
		$r1a[3]  = '%stotte%';				$r2a[3]  = ($row['s_membership'] == 'stottemedlem') ? " checked='checked'" : "";
		$r1a[4]  = '%pensjonert%';		$r2a[4]  = ($row['s_membership'] == 'pensjonert') ? " checked='checked'" : "";
		$r1a[5]  = '%none%';				$r2a[5]  = ($row['s_membership'] == 'none') ? " checked='checked'" : "";
		
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function submitMembership() {
	
		$id = $_POST['id'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 
		
		$res = $this->query("SELECT confirmed FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();
		$confirmed = ($row['confirmed'] != '0');
		
		if (!isset($_POST['membership'])){
			$this->fatalError("Du må velge et av valgene. Gå tilbake og prøv igjen.");
		}
		$membership = addslashes($_POST['membership']);
				
		$this->query("UPDATE $this->tablename 
			SET
				s_membership = '$membership'
			WHERE id='$id'"
		);
		
		if ($confirmed){
			$this->redirect($this->generateURL(array("regid=$id","regprocess"),true));
		} else if (isset($_GET['regconfirm'])){
			$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_confirm_retired']),true));		
		} else {
			$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_oldmemberships']),true));
		}
		
	}
	
	function outputOldMemberships() {

		$template = file_get_contents($this->template_dir.$this->template_oldmemberships);
		
		$id = $_GET['regid'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 

		$res = $this->query("SELECT s_about FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();
		
		if (isset($_GET['regconfirm'])) {
			$r1a[0]  = '%formaction%';		$r2a[0]  = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_oldmemberships'],"regconfirm"));
		} else {
			$r1a[0]  = '%formaction%';		$r2a[0]  = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_oldmemberships']));
		}
		$r1a[1]  = '%id%';				$r2a[1]  = stripslashes($id);
		$r1a[2]  = '%image_dir%';		$r2a[2]  = $this->image_dir;
		$r1a[3]  = '%about%';			$r2a[3]  = stripslashes($row['s_about']);
		
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function submitOldMemberships() {
	
		$id = $_POST['id'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 
		
		$res = $this->query("SELECT confirmed FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();
		$confirmed = ($row['confirmed'] != '0');
			
		$about = addslashes($_POST['about']);
		
		$jubile = (isset($_POST['jubile']) && ($_POST['jubile'] == 'on')) ? 'JA, MELD MEG PÅ 80-ÅRS JUBILÉET' : '';
		
		$this->query("UPDATE $this->tablename 
			SET
				s_about = '$jubile\n\n$about'
			WHERE id='$id'"
		);
		
		if ($confirmed){
			$this->redirect($this->generateURL(array("regid=$id","regprocess"),true));
		} else if (isset($_GET['regconfirm'])){
			$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_confirm_retired']),true));		
		} else {
			$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_oldfriends']),true));
		}
		
	}
	
	function outputOldFriends() {

		$template = file_get_contents($this->template_dir.$this->template_oldfriends);
		
		$id = $_GET['regid'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 

		$res = $this->query("SELECT s_oldfriends FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();
		
		$of = $this->explodeOldFriends($row['s_oldfriends']);
		$f1 = isset($of[0]) ? $of[0] : array('name' => '', 'phone' => '', 'email' => '');
		$f2 = isset($of[1]) ? $of[1] : array('name' => '', 'phone' => '', 'email' => '');
		$f3 = isset($of[2]) ? $of[2] : array('name' => '', 'phone' => '', 'email' => '');
		$f4 = isset($of[3]) ? $of[3] : array('name' => '', 'phone' => '', 'email' => '');
		$f5 = isset($of[4]) ? $of[4] : array('name' => '', 'phone' => '', 'email' => '');
		
		$r1a[] = '%formaction%';		$r2a[] = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_oldfriends']));
		$r1a[] = '%id%';				$r2a[] = stripslashes($id);
		$r1a[] = '%image_dir%';			$r2a[] = $this->image_dir;
		$r1a[] = '%friend1name%';		$r2a[] = $f1['name'];
		$r1a[] = '%friend2name%';		$r2a[] = $f2['name'];
		$r1a[] = '%friend3name%';		$r2a[] = $f3['name'];
		$r1a[] = '%friend4name%';		$r2a[] = $f4['name'];
		$r1a[] = '%friend5name%';		$r2a[] = $f5['name'];
		$r1a[] = '%friend1email%';		$r2a[] = $f1['email'];
		$r1a[] = '%friend2email%';		$r2a[] = $f2['email'];
		$r1a[] = '%friend3email%';		$r2a[] = $f3['email'];
		$r1a[] = '%friend4email%';		$r2a[] = $f4['email'];
		$r1a[] = '%friend5email%';		$r2a[] = $f5['email'];
		$r1a[] = '%friend1phone%';		$r2a[] = $f1['phone'];
		$r1a[] = '%friend2phone%';		$r2a[] = $f2['phone'];
		$r1a[] = '%friend3phone%';		$r2a[] = $f3['phone'];
		$r1a[] = '%friend4phone%';		$r2a[] = $f4['phone'];
		$r1a[] = '%friend5phone%';		$r2a[] = $f5['phone'];
		
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function submitOldFriends() {
	
		$id = $_POST['id'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 
		
		$res = $this->query("SELECT confirmed FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();
		$confirmed = ($row['confirmed'] != '0');
			
		$friend1 = $_POST['friend1_name']."|".$_POST['friend1_phone']."|".$_POST['friend1_email'];
		$friend2 = $_POST['friend2_name']."|".$_POST['friend2_phone']."|".$_POST['friend2_email'];
		$friend3 = $_POST['friend3_name']."|".$_POST['friend3_phone']."|".$_POST['friend3_email'];
		$friend4 = $_POST['friend4_name']."|".$_POST['friend4_phone']."|".$_POST['friend4_email'];
		$friend5 = $_POST['friend5_name']."|".$_POST['friend5_phone']."|".$_POST['friend5_email'];
		
		$oldfriends = "";
		if (strlen($friend1) > 3) $oldfriends .= $friend1."\n";
		if (strlen($friend2) > 3) $oldfriends .= $friend2."\n";
		if (strlen($friend3) > 3) $oldfriends .= $friend3."\n";
		if (strlen($friend4) > 3) $oldfriends .= $friend4."\n";
		if (strlen($friend5) > 3) $oldfriends .= $friend5."\n";
		
		$this->query("UPDATE $this->tablename 
			SET
				s_oldfriends = '$oldfriends'
			WHERE id='$id'"
		);
		
		if ($confirmed){
			$this->redirect($this->generateURL(array("regid=$id","regprocess"),true));
		} else {
			$this->redirect($this->generateURL(array("regid=$id","reg=".$this->ids['output_confirm_retired']),true));
		}
		
	}
	
	/****************************************************************************************************
		CONFIRM REGISTRATION
		**************************************************************************************************/
	
	function outputConfirmSelf() {
	
		$template = file_get_contents($this->template_dir.$this->template_confirm_self);
		
		$id = $_GET['regid'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 

		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();
		
		$ph = stripslashes($row['s_homephone']);
		$pm = stripslashes($row['s_cellular']);
		$pstr = "";
		if (!empty($ph)) $pstr .= "H: $ph ";
		if (!empty($pm)) $pstr .= "M: $pm ";
		
		if ($row['s_group'] == 0) {
			$s_grp = "Ingen preferanse";
		} else {
			$s_grp = call_user_func($this->make_grouplink,$row['s_group']);
		}
		
		$bday_unix = strtotime($row['s_birthday']);
		if ($bday_unix < strtotime('1900-01-01')) $bday_unix = 0;

		$r1a   = array();				$r2a   = array();	
		$r1a[] = '%formaction%';		$r2a[] = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_confirm']));
		$r1a[] = '%address_id%';		$r2a[] = stripslashes($row['s_address_id']);
		$r1a[] = '%id%';				$r2a[] = stripslashes($id);
		$r1a[] = '%image_dir%';			$r2a[] = $this->image_dir;		
		$r1a[] = '%fullname%';			$r2a[] = stripslashes($row['s_firstname'])." ".stripslashes($row['s_middlename'])." ".stripslashes($row['s_lastname']);
		$r1a[] = '%address%';			$r2a[] = stripslashes($row['s_street'])." ".stripslashes($row['s_streetno'])."<br />".stripslashes($row['s_postno'])." ".stripslashes($row['s_city']);
		$r1a[] = '%phone%';				$r2a[] = $pstr;
		$r1a[] = '%email%';				$r2a[] = stripslashes($row['s_email']);
		$r1a[] = '%born%';				$r2a[] = $bday_unix ? strftime('%e. %B %Y',$bday_unix):'<em>Ikke oppgitt</em>';
		$r1a[] = '%url_step1%';			$r2a[] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_memberdetails'],"regconfirm"));
		$r1a[] = '%group%';				$r2a[] = $s_grp;
			
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function outputConfirmRetired() {
	
		$template = file_get_contents($this->template_dir.$this->template_confirm_retired);
		
		$id = $_GET['regid'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 

		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();
		
		$ph = stripslashes($row['s_homephone']);
		$pm = stripslashes($row['s_cellular']);
		$pstr = "";
		if (!empty($ph)) $pstr .= "H: $ph ";
		if (!empty($pm)) $pstr .= "M: $pm ";
		
		switch ($row['s_membership']){
			case 'stottemedlem':
				$stotte = 'Ja, ønsker å tegne støttemedlemskap (200 kr/år).';
				break;
			case 'pensjonert':
				$stotte = 'Nei, ønsker kun å registrere personalia (gratis).';
				break;
			default:
				$stotte = 'Ukjent verdi';
				break;
		}

		$bday_unix = strtotime($row['s_birthday']);
		if ($bday_unix < strtotime('1900-01-01')) $bday_unix = 0;
		
		$r1a[0]  = '%formaction%';			$r2a[0]  = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_confirm']));
		$r1a[1]  = '%id%';					$r2a[1]  = stripslashes($id);
		$r1a[2]  = '%image_dir%';			$r2a[2]  = $this->image_dir;		
		$r1a[3]  = '%fullname%';			$r2a[3]  = stripslashes($row['s_firstname'])." ".stripslashes($row['s_middlename'])." ".stripslashes($row['s_lastname']);
		$r1a[4]  = '%address%';				$r2a[4]  = stripslashes($row['s_street'])." ".stripslashes($row['s_streetno'])."<br />".stripslashes($row['s_postno'])." ".stripslashes($row['s_city']);
		$r1a[5]  = '%phone%';				$r2a[5]  = $pstr;
		$r1a[6]  = '%email%';				$r2a[6]  = stripslashes($row['s_email']);
		$r1a[7]  = '%born%';				$r2a[7]  = $bday_unix ? strftime('%e. %B %Y',$bday_unix):'<em>Ikke oppgitt</em>';
		$r1a[8]  = '%about%';				$r2a[8]  = nl2br(stripslashes($row['s_about']));
		$r1a[9]  = '%stottemedlemsskap%';	$r2a[9]  = $stotte;
		$r1a[10] = '%url_step1%';			$r2a[10] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_memberdetails'],"regconfirm"));
		$r1a[11] = '%url_step2%';			$r2a[11] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_membership'],"regconfirm"));
		$r1a[12] = '%url_step3%';			$r2a[12] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_oldmemberships'],"regconfirm"));
		$r1a[13] = '%url_step4%';			$r2a[13] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_oldfriends'],"regconfirm"));
		$r1a[14] = '%oldfriends%';			$r2a[14] = $this->formatOldFriends($row['s_oldfriends']);
		
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function explodeOldFriends($inp) {
		$r = array();
		$e = explode("\n",stripslashes($inp));
		foreach ($e as $i) {
			if (strlen($i) > 3) {
				$j = explode("|",$i);
				$r[] = array('name' => $j[0], 'phone' => $j[1], 'email' => $j[2]);
			}
		}
		return $r;	
	}
	
	function formatOldFriends($inp) {
		$r = $this->explodeOldFriends($inp);
		if (count($r) == 0) {
			return "<i>Ingen oppgitt</i>";
		} else {
			$t = "";
			foreach ($r as $i) {
				$p = empty($i['phone']) ? "" : " [Tlf: ".$i['phone']."]";
				$m = empty($i['email']) ? "" : " [E-post: ".$i['email']."]";
				$t .= "<li>".$i['name']." $p $m</li>";
			}
			return "<ul>$t</ul>";
		}
		
	}
	
	function outputConfirmParents() {
	
		$template = file_get_contents($this->template_dir.$this->template_confirm_parents);
		
		$id = $_GET['regid'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 

		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		$row = $res->fetch_assoc();
		
		$ph = stripslashes($row['s_homephone']);
		$pm = stripslashes($row['s_cellular']);
		$pstr = "";
		if (!empty($ph)) $pstr .= "H: $ph ";
		if (!empty($pm)) $pstr .= "M: $pm ";
		
		$ph = stripslashes($row['p1_homephone']);
		$pm = stripslashes($row['p1_cellular']);
		$p1_pstr = "";
		if (!empty($ph)) $p1_pstr .= "H: $ph ";
		if (!empty($pm)) $p1_pstr .= "M: $pm ";

		$ph = stripslashes($row['p2_homephone']);
		$pm = stripslashes($row['p2_cellular']);
		$p2_pstr = "";
		if (!empty($ph)) $p2_pstr .= "H: $ph ";
		if (!empty($pm)) $p2_pstr .= "M: $pm ";
		
		$p2exists = !($row['p2_ignore'] == '1');

		$bday_unix = strtotime($row['s_birthday']);
		if ($bday_unix < strtotime('1900-01-01')) $bday_unix = 0;
			
		$r1a[0]  = '%formaction%';		$r2a[0]  = $this->generateURL(array("noprint=true","reg=".$this->ids['submit_confirm']));
		$r1a[1]  = '%id%';				$r2a[1]  = stripslashes($id);
		$r1a[2]  = '%image_dir%';		$r2a[2]  = $this->image_dir;
		
		$r1a[3]  = '%fullname%';		$r2a[3]  = stripslashes($row['s_firstname'])." ".stripslashes($row['s_middlename'])." ".stripslashes($row['s_lastname']);
		$r1a[4]  = '%address%';			$r2a[4]  = stripslashes($row['s_street'])." ".stripslashes($row['s_streetno'])."<br />".stripslashes($row['s_postno'])." ".stripslashes($row['s_city']);
		$r1a[5]  = '%phone%';			$r2a[5]  = $pstr;
		$r1a[6]  = '%email%';			$r2a[6]  = stripslashes($row['s_email']);
		$r1a[7]  = '%born%';			$r2a[7]  = $bday_unix ? strftime('%e. %B %Y',$bday_unix):'<em>Ikke oppgitt</em>'; 

		$r1a[8]  = '%p1_fullname%';		$r2a[8]  = stripslashes($row['p1_firstname'])." ".stripslashes($row['p1_middlename'])." ".stripslashes($row['p1_lastname']);
		$r1a[9]  = '%p1_address%';		$r2a[9]  = stripslashes($row['p1_street'])." ".stripslashes($row['p1_streetno'])."<br />".stripslashes($row['p1_postno'])." ".stripslashes($row['p1_city']);
		$r1a[10]  = '%p1_phone%';		$r2a[10]  = $p1_pstr;
		$r1a[11]  = '%p1_email%';		$r2a[11]  = stripslashes($row['p1_email']);

		$r1a[12]  = '%p2_fullname%';	$r2a[12]  = stripslashes($row['p2_firstname'])." ".stripslashes($row['p2_middlename'])." ".stripslashes($row['p2_lastname']);
		$r1a[13]  = '%p2_address%';		$r2a[13]  = stripslashes($row['p2_street'])." ".stripslashes($row['p2_streetno'])."<br />".stripslashes($row['p2_postno'])." ".stripslashes($row['p2_city']);
		$r1a[14]  = '%p2_phone%';		$r2a[14]  = $p2_pstr;
		$r1a[15]  = '%p2_email%';		$r2a[15]  = stripslashes($row['p2_email']);

		$r1a[16]  = '%url_step1%';		$r2a[16]  = $this->generateURL(array("regid=$id","reg=".$this->ids['output_memberdetails'],"regconfirm"));
		$r1a[17]  = '%url_step2%';		$r2a[17]  = $this->generateURL(array("regid=$id","reg=".$this->ids['output_parent1'],"regconfirm"));
		$r1a[18]  = '%url_step4%';		$r2a[18]  = $this->generateURL(array("regid=$id","reg=".$this->ids['output_parent2'],"regconfirm"));
		
		$r1a[19]  = '%displayforesatt2%';		$r2a[19]  = ($p2exists ? "block" : "none");
		$r1a[20]  = '%notdisplayforesatt2%';	$r2a[20]  = ($p2exists ? "none" : "block");
		
		if ($row['s_group'] == 0) {
			$s_grp = "Ingen preferanse";
		} else {
			$s_grp = call_user_func($this->make_grouplink,$row['s_group']);
		}
		if ($row['p1_group'] == 0) {
			$p1_grp = "Ingen preferanse";
		} else {
			$p1_grp = call_user_func($this->make_grouplink,$row['p1_group']);
		}
		if ($row['p2_group'] == 0) {
			$p2_grp = "Ingen preferanse";
		} else {
			$p2_grp = call_user_func($this->make_grouplink,$row['p2_group']);
		}

		$r1a[21]  = '%group%';			$r2a[21]  = $s_grp;
		$r1a[22]  = '%p1_group%';		$r2a[22]  = $p1_grp;
		$r1a[23]  = '%p2_group%';		$r2a[23]  = $p2_grp;
		
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function submitConfirm() {

		require_once("../www/libs/Rmail/Rmail.php");

		
		$id = $_POST['id'];
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (1)"); 
		
		$res = $this->query("SELECT id, kategori FROM $this->tablename WHERE id='$id' AND editable='1'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er låst for redigering (2)"); 
		
		$row = $res->fetch_assoc();
		if ($row['kategori'] == 'SM' || $row['kategori'] == 'SP') {
			if (isset($_POST['bekreft']) && $_POST['bekreft'] == "on") { } else {
				$this->fatalError("Du må bekrefte");
			}
		}
		
		$this->query("UPDATE $this->tablename 
			SET
				editable = '0',
				confirmed = '".time()."'
			WHERE id='$id'"
		);
				
		$url = $this->generateURL(array("regid=$id","regprocess"));
		
		$plainBody = "
$this->site_name

En søknad om registrering av et nytt medlem er mottatt.
Du behandler søknaden ved å gå til denne adressen:

http://".$_SERVER['SERVER_NAME']."$url

";
		
		$reg_behandler = call_user_func($this->lookup_member, $this->reg_behandler);		
		if (empty($reg_behandler->email)) {
			$this->fatalError("Fant ikke epostadressen til søknadsbehandler!");
		}
		$recipients = array($reg_behandler->email);

		// Send mail
		$mail = new Rmail();
		$mail->setFrom("$this->mailSenderName <$this->mailSenderAddr>");
		$mail->setReturnPath($this->mailSenderAddr);
		$mail->setSubject("[$this->mailSenderName] Registrering av nytt medlem");
		$mail->setText($plainBody);
		$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
		$mail->send($recipients,$type = 'smtp');		
		
		$this->redirect($this->generateURL(array("regview","regid=$id")),
			"Registreringen er sendt! Dersom du har oppgitt en e-postadresse, vil du vil motta informasjon når registreringen er godkjent."
		);
	
	}
	
	/****************************************************************************************************
		VIEW REGISTRATION-DETAILS
		**************************************************************************************************/

	function niceDate($t) {
		return date("j",$t).". ".
			$this->months[date("m",$t)-1]." ".
			date("Y",$t);
	}
	
	function viewRegistration($id) {
		global $login;
		
		$template = file_get_contents($this->template_dir.$this->template_review);
		
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke (1)"); 

		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke (2)"); 
		$row = $res->fetch_assoc();
		
		$ph = stripslashes($row['s_homephone']);
		$pm = stripslashes($row['s_cellular']);
		$pstr = "";
		if (!empty($ph)) $pstr .= "H: $ph ";
		if (!empty($pm)) $pstr .= "M: $pm ";
		
		$ph = stripslashes($row['p1_homephone']);
		$pm = stripslashes($row['p1_cellular']);
		$p1_pstr = "";
		if (!empty($ph)) $p1_pstr .= "H: $ph ";
		if (!empty($pm)) $p1_pstr .= "M: $pm ";

		$ph = stripslashes($row['p2_homephone']);
		$pm = stripslashes($row['p2_cellular']);
		$p2_pstr = "";
		if (!empty($ph)) $p2_pstr .= "H: $ph ";
		if (!empty($pm)) $p2_pstr .= "M: $pm ";	
		
		$s_fullname = stripslashes($row['s_firstname'])." ".stripslashes($row['s_middlename'])." ".stripslashes($row['s_lastname']);
		$s_address_id = stripslashes($row['s_address_id']);
		if ($row['s_ident'] > 0) $s_fullname = call_user_func($this->make_memberlink, $row['s_ident'], $s_fullname);

		$p1_fullname = stripslashes($row['p1_firstname'])." ".stripslashes($row['p1_middlename'])." ".stripslashes($row['p1_lastname']);
		if ($row['p1_ident'] > 0) $p1_fullname = call_user_func($this->make_memberlink, $row['p1_ident']);

		$p2_fullname = stripslashes($row['p2_firstname'])." ".stripslashes($row['p2_middlename'])." ".stripslashes($row['p2_lastname']);
		if ($row['p2_ident'] > 0) $p2_fullname = call_user_func($this->make_memberlink, $row['p2_ident']);
		
		switch ($row['s_membership']){
			case 'stottemedlem':
				$stotte = 'Ja, ønsker å tegne støttemedlemskap (200 kr/år).';
				break;
			case 'pensjonert':
				$stotte = 'Nei, ønsker kun å registrere personalia (gratis).';
				break;
			default:
				$stotte = 'Ukjent verdi';
				break;
		}

		$bday_unix = strtotime($row['s_birthday']);
		if ($bday_unix < strtotime('1900-01-01')) $bday_unix = 0;
			
		$r1a   = array();						$r2a   = array();
		$r1a[] = '%formaction%';				$r2a[] = $this->generateURL(array("noprint=true","submitpart5"));
		$r1a[] = '%id%';						$r2a[] = stripslashes($id);
		$r1a[] = '%image_dir%';					$r2a[] = $this->image_dir;
		
		$r1a[] = '%address_id%';				$r2a[] = $s_address_id;
		$r1a[] = '%fullname%';					$r2a[] = $s_fullname;
		$r1a[] = '%address%';					$r2a[] = stripslashes($row['s_street'])." ".stripslashes($row['s_streetno'])."<br />".stripslashes($row['s_postno'])." ".stripslashes($row['s_city']);
		$r1a[] = '%phone%';						$r2a[] = $pstr;
		$r1a[] = '%email%';						$r2a[] = stripslashes($row['s_email']);
		$r1a[] = '%born%';						$r2a[] = $bday_unix ? strftime('%e. %B %Y',$bday_unix):'<em>Ikke oppgitt</em>';

		$r1a[] = '%p1_fullname%';				$r2a[] = $p1_fullname;
		$r1a[] = '%p1_address%';				$r2a[] = stripslashes($row['p1_street'])." ".stripslashes($row['p1_streetno'])."<br />".stripslashes($row['p1_postno'])." ".stripslashes($row['p1_city']);
		$r1a[] = '%p1_phone%';					$r2a[] = $p1_pstr;
		$r1a[] = '%p1_email%';					$r2a[] = stripslashes($row['p1_email']);

		$r1a[] = '%p2_fullname%';				$r2a[] = $p2_fullname;
		$r1a[] = '%p2_address%';				$r2a[] = stripslashes($row['p2_street'])." ".stripslashes($row['p2_streetno'])."<br />".stripslashes($row['p2_postno'])." ".stripslashes($row['p2_city']);
		$r1a[] = '%p2_phone%';					$r2a[] = $p2_pstr;
		$r1a[] = '%p2_email%';					$r2a[] = stripslashes($row['p2_email']);

		$r1a[] = '%url_memberdetails%';			$r2a[] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_memberdetails'],"regconfirm"));
		$r1a[] = '%url_parent1%';				$r2a[] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_parent1'],"regconfirm"));
		$r1a[] = '%url_parent2%';				$r2a[] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_parent2'],"regconfirm"));

		$r1a[] = '%displayforesatt2%';			$r2a[] = ((empty($row['p2_firstname']) && empty($row['p2_middlename']) && empty($row['p2_lastname'])) ? "none" : "block");
		$r1a[] = '%notdisplayforesatt2%';		$r2a[] = ((empty($row['p2_firstname']) && empty($row['p2_middlename']) && empty($row['p2_lastname'])) ? "block" : "none");
		
		if (!empty($row['confirmed'])) {
			$r1a[] = '%status_received%';			$r2a[] = '<div style="background:url(/images/icns/information.png) left no-repeat;padding-left:20px;margin:2px;">Søknaden ble innsendt den '.$this->niceDate($row['confirmed']).'</div>';
		} else {
			$r1a[] = '%status_received%';			$r2a[] = '<div style="background:url(/images/icns/error.png) left no-repeat;padding-left:20px;margin:2px;">Søknaden er ikke sendt enda.</div>';
		}
		
		if (!empty($row['processed'])) {
			if ($row['s_ident'] > 0) {
				$r1a[] = '%status_processed%';			$r2a[] = '<div style="background:url(/images/icns/accept.png) left no-repeat;padding-left:20px;margin:2px;">Søknaden ble godkjent den '.$this->niceDate($row['processed']).'.</div>';
			} else {
				$r1a[] = '%status_processed%';			$r2a[] = '<div style="background:url(/images/icns/exclamation.png) left no-repeat;padding-left:20px;margin:2px;">Søknaden ble avslått den '.$this->niceDate($row['processed']).'.</div>';			
			}
		} else {
			$r1a[] = '%status_processed%';			$r2a[] = '<div style="background:url(/images/icns/error.png) left no-repeat;padding-left:20px;margin:2px;">Søknaden er ikke behandlet enda.</div>';
		}
		
		$r1a[] = '%closed%';					$r2a[] = ($row['editable'] ? "false" : "true");
		$r1a[] = '%allowedit%';					$r2a[] = ($row['editable'] ? "inline" : "none");
		
		if ($row['s_group'] == 0) {
			$s_grp = "Ingen preferanse";
		} else {
			$s_grp = call_user_func($this->make_grouplink,$row['s_group']);
		}
		if ($row['p1_group'] == 0) {
			$p1_grp = "Ingen preferanse";
		} else {
			$p1_grp = call_user_func($this->make_grouplink,$row['p1_group']);
		}
		if ($row['p2_group'] == 0) {
			$p2_grp = "Ingen preferanse";
		} else {
			$p2_grp = call_user_func($this->make_grouplink,$row['p2_group']);
		}

		$r1a[] = '%group%';			$r2a[] = $s_grp;
		$r1a[] = '%p1_group%';		$r2a[] = $p1_grp;
		$r1a[] = '%p2_group%';		$r2a[] = $p2_grp;
		
		switch ($row['kategori']) {
			case "PE":
				$r1a[] = '%display_foresatte%';			$r2a[] = "none";
				$r1a[] = '%display_bekreftelse%';		$r2a[] = "none";
				$r1a[] = '%display_membership%';		$r2a[] = "block";
				$r1a[] = '%display_about%';				$r2a[] = "block";
				$r1a[] = '%display_oldfriends%';		$r2a[] = "block";
				break;
			case "SM":
			case "SP":
				$r1a[] = '%display_foresatte%';			$r2a[] = "block";
				$r1a[] = '%display_bekreftelse%';		$r2a[] = "block";
				$r1a[] = '%display_membership%';		$r2a[] = "none";
				$r1a[] = '%display_about%';				$r2a[] = "none";
				$r1a[] = '%display_oldfriends%';		$r2a[] = "none";
				break;
			default:
				$r1a[] = '%display_foresatte%';			$r2a[] = "none";
				$r1a[] = '%display_bekreftelse%';		$r2a[] = "none";
				$r1a[] = '%display_membership%';		$r2a[] = "none";
				$r1a[] = '%display_about%';				$r2a[] = "none";
				$r1a[] = '%display_oldfriends%';		$r2a[] = "none";
				break;		
		}
		
		$r1a[] = '%stottemedlemsskap%';					$r2a[] = $stotte;
		$r1a[] = '%about%';								$r2a[] = nl2br(stripslashes($row['s_about']));
		$r1a[] = '%url_membership%';					$r2a[] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_membership'],"regconfirm"));
		$r1a[] = '%url_about%';							$r2a[] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_oldmemberships'],"regconfirm"));
		$r1a[] = '%url_oldfriends%';					$r2a[] = $this->generateURL(array("regid=$id","reg=".$this->ids['output_oldfriends'],"regconfirm"));
		$r1a[] = '%oldfriends%';						$r2a[] = $this->formatOldFriends($row['s_oldfriends']);
		
		$userTrue = '<div style="background:url(/images/icns/accept.png) left no-repeat;padding-left:20px;margin:2px;">Personen er tildelt brukerkonto!</div>';
		$userFalse = '<div style="background:url(/images/icns/error.png) left no-repeat;padding-left:20px;margin:2px;">Personen er ikke tildelt brukerkonto! <a href="%url%">Tildel brukerkonto</a></div>';
		$userMissingEmail = '<div style="background:url(/images/icns/error.png) left no-repeat;padding-left:20px;margin:2px;">Personen er ikke oppført med epost-adresse, og kan derfor ikke tildeles brukerkonto.</div>';
		if ($row['s_ident'] > 0) {
			$r1a[] = '%display_username%';				$r2a[] = "block";
			$member = call_user_func($this->lookup_member, $row['s_ident']);
			if ($login->hasUsername($member->ident)) {
				$r1a[] = '%status_username%';				$r2a[] = $userTrue;
			} else {
				if (empty($member->email)) {
					$r1a[] = '%status_username%';				$r2a[] = $userMissingEmail;
				} else {
					$url = "/medlemsliste/medlemmer/$member->ident/?assignusername";
					$r1a[] = '%status_username%';				$r2a[] = str_replace('%url%',$url,$userFalse);
				}
			}
		} else {
			$r1a[] = '%display_username%';				$r2a[] = "none";		
		}
		if ($row['p1_ident'] > 0) {
			$r1a[] = '%p1_display_username%';			$r2a[] = "block";
			$member = call_user_func($this->lookup_member, $row['p1_ident']);
			if ($login->hasUsername($member->ident)) {
				$r1a[] = '%p1_status_username%';				$r2a[] = $userTrue;
			} else {
				if (empty($member->email)) {
					$r1a[] = '%p1_status_username%';			$r2a[] = $userMissingEmail;
				} else {
					$url = "/medlemsliste/medlemmer/$member->ident/?assignusername";
					$r1a[] = '%p1_status_username%';			$r2a[] = str_replace('%url%',$url,$userFalse);;
				}
			}
		} else {
			$r1a[] = '%p1_display_username%';			$r2a[] = "none";		
		}
		if ($row['p2_ident'] > 0) {
			$r1a[] = '%p2_display_username%';			$r2a[] = "block";
			$member = call_user_func($this->lookup_member, $row['p2_ident']);
			if ($login->hasUsername($member->ident)) {
				$r1a[] = '%p2_status_username%';				$r2a[] = $userTrue;
			} else {
				if (empty($member->email)) {
					$r1a[] = '%p2_status_username%';			$r2a[] = $userMissingEmail;
				} else {
					$url = "/medlemsliste/medlemmer/$member->ident/?assignusername";
					$r1a[] = '%p2_status_username%';			$r2a[] = str_replace('%url%',$url,$userFalse);;
				}
			}
		} else {
			$r1a[] = '%p2_display_username%';			$r2a[] = "none";		
		}
		
		return str_replace($r1a, $r2a, $template);
		
	}
	
	function listRegistrations() {
		if (!$this->allow_listregistrations) return $this->permissionDenied(); 
		
		// print "<h1>Registreringer</h1>";

		$output = "
			<h2>Venter på behandling:</h2>
			<table width='100%' class='forum' cellpadding='1' cellspacing='0'>
			<tr><th>ID:</th><th>Dato:</th><th>Navn:</th><th>Handlinger:</th></tr>
		";
		$res = $this->query("SELECT * FROM $this->tablename WHERE processed='0' AND confirmed!='0' ORDER BY created DESC");
		$no = 1;
		while ($row = $res->fetch_assoc()) {
			$no = ($no == 1) ? 2 : 1;
			$id = $row['id'];
			$fullname = stripslashes($row['s_firstname'])." ".stripslashes($row['s_middlename'])." ".stripslashes($row['s_lastname']);
			$url = $this->generateURL(array("regid=$id","regprocess"));
			$url2 = $this->generateURL(array("regview","regid=$id"));
			$output .= "<tr class='forum$no'><td>$id</td><td>".date("j. M. Y",$row['created'])."</td><td>$fullname</td><td><a href=\"$url\">Behandle</a> | <a href=\"$url2\">Vis</a></td></tr>
			";
		}
		$output .= "</table>";
		
		$output .= "
			<h2>Behandlet (Siste 50):</h2>
			<table width='100%' class='forum' cellpadding='1' cellspacing='0'>
			<tr><th>ID:</th><th>Dato:</th><th>Navn:</th><th>Handlinger:</th></tr>
		";
		$res = $this->query("SELECT * FROM $this->tablename WHERE processed!='0' ORDER BY created DESC LIMIT 50");
		$no = 1;
		while ($row = $res->fetch_assoc()) {
			$no = ($no == 1) ? 2 : 1;
			$id = $row['id'];
			$fullname = stripslashes($row['s_firstname'])." ".stripslashes($row['s_middlename'])." ".stripslashes($row['s_lastname']);
			$url = $this->generateURL(array("regview","regid=$id"));
			$output .= "<tr class='forum$no'><td>$id</td><td>".date("j. M. Y",$row['created'])."</td><td>$fullname</td><td><a href=\"$url\">Vis</a></td></tr>
			";
		}
		$output .= "</table>";
		
		$output .= "
			<h2>Uferdige søknader:</h2>
			<table width='100%' class='forum' cellpadding='1' cellspacing='0'>
			<tr><th>ID:</th><th>Dato:</th><th>Navn:</th><th>Handlinger:</th></tr>
		";
		$res = $this->query("SELECT * FROM $this->tablename WHERE confirmed='0' ORDER BY created DESC");
		$no = 1;
		while ($row = $res->fetch_assoc()) {
			$no = ($no == 1) ? 2 : 1;
			$id = $row['id'];
			$fullname = stripslashes($row['s_firstname'])." ".stripslashes($row['s_middlename'])." ".stripslashes($row['s_lastname']);
			$url_vis = $this->generateURL(array("regview","regid=$id"));
			$url_slett = $this->generateURL("regdelete=$id");
			$output .= "<tr class='forum$no'><td>$id</td><td>".date("j. M. Y",$row['created'])."</td><td>$fullname</td><td><a href=\"$url_vis\">Vis</a> | <a href=\"$url_slett\">Slett</a></td></tr>
			";
		}
		$output .= "</table>";
		
		return $output;
	}
	
	/****************************************************************************************************
		PROCESS REGISTRATION
		**************************************************************************************************/

	function loadFullMemberlist() {
		global $login,$db;
 		require_once("memberlist_actions.php");
		$memberdb = new memberlist_actions($db);
		$memberdb->login_identifier = $login->getUserId;
		$memberdb->setEventlogInstance($this->_eventlog);	// base
		#$memberdb->eventlog_function = "addToEventLog";
		$memberdb->permission_denied_function = "permissionDenied";
		$memberdb->image_dir = ROOT_DIR.'/images';
		$memberdb->initialize();
		return $memberdb;
 	}
	
	function processRegistrationForm($id) {
	
		if (!$this->allow_acceptregistrations) return $this->permissionDenied(); 
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke (1)"); 
		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id' AND processed='0'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er allerede akspetert"); 
		$row = $res->fetch_assoc();
		
		$output = $this->viewRegistration($id);
		
		$output .= '<form method="post" action="'.$this->generateURL(array('noprint=true','regid='.$id,'regdoprocess')).'">
			<input type="submit" name="accept" value="Aksepter søknad" />
			<input type="submit" name="decline" value="Avvis søknad" />
			<input type="submit" name="toggle" value="Lås / Lås opp for redigering" />
		</form>';
		
		return $output;
	
	}
	
	function processRegistration($id) {
		require_once("../www/libs/Rmail/Rmail.php");
	
		if (!$this->allow_acceptregistrations) return $this->permissionDenied(); 
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke (1)"); 
		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke!"); 
		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id' AND processed='0'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen er allerede behandlet!"); 
		$row = $res->fetch_assoc();
		
		if (isset($_POST['toggle'])){
			if ($row['editable'] == '0'){
				$this->query("UPDATE $this->tablename SET editable='1' WHERE id='$id'");
				$this->redirect($this->generateURL(array("regid=$id","regprocess")),"Søknaden er åpnet for redigering.");
			} else {
				$this->query("UPDATE $this->tablename SET editable='0' WHERE id='$id'");
				$this->redirect($this->generateURL(array("regid=$id","regprocess")),"Søknaden er låst for redigering.");
			}
		}
		
		if (isset($_POST['accept'])){
		
			global $memberlist_page;
		
			$memberdb = new memberlist_actions();
			call_user_func($this->prepare_classinstance, $memberdb, $memberlist_page);
			$memberdb->initialize();
			
			$memberdb->allow_addmember = true;
			$memberdb->allow_editmemberships = true;
			$memberdb->allow_editprofile = true;
			$memberdb->allow_editforesatte = true;

			$recipients = array();
			
			// Tilføy medlem
			
			$firstname 		= stripslashes($row['s_firstname']);
			$middlename 	= stripslashes($row['s_middlename']);
			$lastname 		= stripslashes($row['s_lastname']);
			$email 			= stripslashes($row['s_email']);
			if ($row['s_membership'] == 'stottemedlem')
				$gruppe = array($row['s_group'], $this->stottemedlemgruppe);
			else
				$gruppe = $row['s_group'];
			$scout_ident = $memberdb->addMember($firstname,$middlename,$lastname,$gruppe);
			$memberdb->updateProfile($scout_ident, array(
				'nickname' 		=> stripslashes($row['s_firstname']),
				'street' 		=> stripslashes($row['s_street']),
				'streetno' 		=> stripslashes($row['s_streetno']),
				'postno' 		=> stripslashes($row['s_postno']),
				'city' 			=> stripslashes($row['s_city']),
				'homephone' 	=> stripslashes($row['s_homephone']),
				'cellular' 		=> stripslashes($row['s_cellular']),
				'email' 		=> $email,
				'bday' 		=> stripslashes($row['s_birthday']),
				'kategori' 		=> stripslashes($row['kategori']),
				'memberstatus'  => stripslashes($row['s_membership']),
				'address_id'	=> stripslashes($row['s_address_id'])
			));
			$memberdb->updateProfileNotes($scout_ident, $row['s_about']);
			
			if (!empty($email)) $recipients[] = $email;
			
			$q = "SET processed='".time()."', editable='0', s_ident='$scout_ident'";
			
			if ($row['kategori'] == 'SM' || $row['kategori'] == 'SP') {

				// Tilføy foresatt 1
			
				$firstname 		= stripslashes($row['p1_firstname']);
				$middlename 	= stripslashes($row['p1_middlename']);
				$lastname 		= stripslashes($row['p1_lastname']);
				$email 			= stripslashes($row['p1_email']);				
				$gruppe 		= $row['p1_group'];
				$parent1_ident = $memberdb->addMember($firstname,$middlename,$lastname,$gruppe);
				$memberdb->updateProfile($parent1_ident, array(
					'nickname' 		=> stripslashes($row['p1_firstname']),
					'street' 		=> stripslashes($row['p1_street']),
					'streetno' 		=> stripslashes($row['p1_streetno']),
					'postno' 		=> stripslashes($row['p1_postno']),
					'city' 			=> stripslashes($row['p1_city']),
					'homephone' 	=> stripslashes($row['p1_homephone']),
					'cellular' 		=> stripslashes($row['p1_cellular']),
					'email' 		=> $email,
					'kategori'		=> 'FO',
					'memberstatus'  => 'foresatt'
				));				 
				$memberdb->registerGuardian($scout_ident,$parent1_ident);				
				if (!empty($email)) $recipients[] = $email;
				$q .= ", p1_ident='$parent1_ident'";
				
				// Tilføy foresatt 2
				
				if ($row['p2_ignore'] == true){ 
					$parent2_ident = 0;
				} else {

					$firstname 		= stripslashes($row['p2_firstname']);
					$middlename 	= stripslashes($row['p2_middlename']);
					$lastname 		= stripslashes($row['p2_lastname']);
					$email 			= stripslashes($row['p2_email']);
					$gruppe 		= $row['p2_group'];
					
					$parent2_ident = $memberdb->addMember($firstname,$middlename,$lastname,$gruppe);
					$memberdb->updateProfile($parent2_ident, array(
						'nickname' 		=> stripslashes($row['p2_firstname']),
						'street' 		=> stripslashes($row['p2_street']),
						'streetno' 		=> stripslashes($row['p2_streetno']),
						'postno' 		=> stripslashes($row['p2_postno']),
						'city' 			=> stripslashes($row['p2_city']),
						'homephone' 	=> stripslashes($row['p2_homephone']),
						'cellular' 		=> stripslashes($row['p2_cellular']),
						'email' 		=> $email,
						'kategori'		=> 'FO',
						'memberstatus'  => 'foresatt'
					));								
					$memberdb->registerGuardian($scout_ident,$parent2_ident);					
					if (!empty($email)) $recipients[] = $email;
					$q .= ", p2_ident='$parent2_ident'";	
				}
			
			}
			
			$this->query("UPDATE $this->tablename $q WHERE id='$id'");
					
			$url = $this->generateURL(array("regview&regid=$id"));
			
			if (count($recipients) > 0) {
		
				$plainBody = "$this->site_name\n\n".
					"Søknaden om registrering av et nytt medlem er godkjent.\n\n".
					"http://".$_SERVER['SERVER_NAME']."$url\n\n";
	
				// Send mail		
				$mail = new Rmail();
				$mail->setFrom("$this->mailSenderName <$this->mailSenderAddr>");
				$mail->setReturnPath($this->mailSenderAddr);
				$mail->setSubject("[$this->mailSenderName] Registrering av nytt medlem");
				$mail->setText($plainBody);
				$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
				$mail->send($recipients,$type = 'smtp');		
			
			}
			
			$this->redirect($url,"Søknaden er godkjent.");
			
		}
		
		if (isset($_POST['decline'])){
			$this->query("UPDATE $this->tablename SET editable='0', processed='".time()."' WHERE id='$id'");
					
			$url = $this->generateURL(array("regview&regid=$id"));

			if (count($recipients) > 0) {
						
				$plainBody = "$this->site_name\n\n".
					"Søknaden om registrering av et nytt medlem er avslått.\n\n".
					"http://".$_SERVER['SERVER_NAME']."$url\n\n";
	
				// Send mail		
				$mail = new Rmail();
				$mail->setFrom("$this->mailSenderName <$this->mailSenderAddr>");
				$mail->setReturnPath($this->mailSenderAddr);
				$mail->setSubject("[$this->mailSenderName] Registrering av nytt medlem");
				$mail->setText($plainBody);
				$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
				$mail->send($recipients,$type = 'smtp');		
			
			}
			
			$this->redirect($url,"Søknaden er avslått.");
		}
	
	}
	
	/****************************************************************************************************
		DELETE REGISTRATION
		**************************************************************************************************/
	
	function deleteRegistration($id){
	
		if (!$this->allow_acceptregistrations) return $this->permissionDenied(); 
		if (!is_numeric($id)) return $this->notSoFatalError("Registreringen eksisterer ikke (1)"); 
		$res = $this->query("SELECT * FROM $this->tablename WHERE id='$id' AND processed='0'");
		if ($res->num_rows != 1) return $this->notSoFatalError("Registreringen eksisterer ikke eller er editerbar eller er allerede akspetert"); 
		$row = $res->fetch_assoc();
		
		$this->query("DELETE FROM $this->tablename WHERE id='$id'");
		
		$this->redirect($this->generateURL("reglist"),"Søknaden ble slettet.");
		
		
	}
	
	
}



?>