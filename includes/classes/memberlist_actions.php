<?

/*

$this	-> members : Array	-> memberof : Array
							-> fullname : String

							-> ... databaseverdier

$this	-> groups : Array	-> members : Array

							-> id
							-> parent
							-> position
							-> caption
							-> position
							-> visgruppe
							-> defaultrang
							-> defaultrights
							-> slug
							-> kategori
							-> gruppesider

*/

class memberlist_actions extends memberlist {

	var $getvars = array('action', 'mlvisning', 'showhidden', 'cropForumImage','foresattil','guardian_id');
	/*
	"reviewprofile","viewprofile","editprofile","saveprofile","edituser",
		"showhidden","errs","deleteuser","dodeleteuser", "resendwelcomemail", "doresendwelcomemail",
		"fixrights","mlvisning","editmemberships","membership","membershipedit","membershipsave","editrights",
		"saverights","edittitle","savetitle","editpeff","savepeff","editass",
		"saveass","editgrouppositions","savegrouppositions","savegroupsettings","addmember","savenewmember",
		"memberadded","assignusername","releaseusername","doreleaseusername","saveusername","errors","username","tittel","email","usernameassigned","foresattil",
		"addforesatt","saveforesatt","foresatte","removeforesatt","ac","savepwd","pwdsaved","sendmail","addgroup","doaddgroup",
		"sendlogindetails","dosendlogindetails","history","history_iframe","history_xml",
		"crop_profileimage","docrop_profileimage","crop_forumimage","docrop_forumimage","editmembershiptype","savemembershiptype","makemembershiplist","images");
	*/
	/* ACCESS CONTROL VARS BEGIN */
	
	var $allow_viewlist				= false;
	var $allow_viewprofiledetails	= false;
	var $allow_viewmemberdetails	= false;
	var $allow_editprofile			= false;
	var $allow_addmember			= false;
	var $allow_editgroupsettings	= false;	
	var $allow_addgroup				= false;
	var $allow_editrights			= false;
	var $allow_addmembership		= false;
	var $allow_addcurrentmembership = false;
	var $allow_editmemberships		= false;
	var $allow_editverv				= false;
	var $allow_viewforesatte		= false;
	var $allow_editforesatte		= false;
	var $allow_editpeff				= false;
	var $allow_editass				= false;
	var $allow_edittitles			= false;
	var $allow_viewhiddengroups		= false;
	var $allow_moveuserstononpatruljer = false;
	var $allow_deletemember         = false;
	var $allow_editmembershiptype   = false;
	var $allow_editpassword         = false;

	/* ACCESS CONTROL VARS END */
	
	var $label_pwdChangeNotAllowed	= false;
	
	var $use_imagearchive = false;
	var $use_wordbox = false;
		
	var $template_dir 					= "../includes/templates/";
	var $template_welcome 				= "memberlist_welcome.txt";
	var $template_activationreminder 	= "memberlist_activationreminder.txt";
	var $template_editprofileform 		= "editprofileform.html";
		
	var $imagedir_members;
	var $imagedir_forums;
	var $wordbox_table;
		
	var $table_images_field_id = "id";
	var $table_images_field_caption = "caption";
	var $table_images_field_extension = "extension";
	var $table_images_field_parent = "parent";

	var $profile_image_dir;
	var $profile_image_width = 105;
	var $profile_image_height = 140;

	var $forum_image_dir;
	var $forum_image_width = 100;
	var $forum_image_height = 100;

	var $errors;
	
	var $current_template;

	var $showHiddenGroups;
	var $visning;
	
	var $mapurl_template = "http://www.gulesider.no/gsi/address.do?query=%street%+%streetno%,+%city%";
		
	var $reserved_slugs = array(
		"medlemmer",
		"grupper"
	);

	var $errorMessages = array(
		'empty_firstname'			=> "Du glemte å fylle inn fornavn.",
		'empty_lastname'			=> "Du glemte å fylle inn etternavn.",
		'empty_email'				=> "Du må fylle inn din e-postadresse.",
		'empty_birthday'			=> "Du glemte å fylle inn fødselsdato.",
		'empty_address'				=> "Du har ikke fyllt ut deler av eller hele adressen.",
		'invalid_email'				=> "Epostadressen du fylte inn er ikke gyldig. Den må være på formatet xxxxx@xxxxx.xxx og kun bestå av gyldige tegn (æ,ø,å og mellomrom er blant annet ikke tillatt)",
		'toolong_streetno'			=> "Gatenummeret ditt er for langt. Det kan maks bestå av 4 tegn.",
		'notint_postno'				=> "Postnummeret ditt kan kun inneholde tall.",
		'invalid_homephone'			=> "Hjemmetelefonnummeret ditt kan kun bestå av tallene 0-9 og tegnet \"+\".",
		'invalid_cellular'			=> "Mobiltelefonnummeret ditt kan kun bestå av tallene 0-9 og tegnet \"+\".",
		'username_contain_specials'	=> "Brukernavnet inneholder ugyldige tegn!<br />Merk at du bare kan bruke bokstavene a-z og tall. Du kan ikke bruke æ,ø, å, spesialtegn eller store bokstaver.",
		'empty_phone'				=> "Du må oppgi minst et telefonnr (hjemmetlf. eller mobil).",
		'username_already_exists'	=> "Brukernavnet du valgte er allerede i bruk. Vennligst velg et annet.",
		'no_username_entered'		=> "Du må fylle inn et brukernavn!",
		'no_firstname_entered'		=> "Du må fylle inn fornavnet til brukeren!",
		'no_lastname_entered'		=> "Du må fylle inn etternavnet til brukeren!",
		'no_email_entered'			=> "Du må fylle inn epostadressen til brukeren!",
		'username_too_short'		=> "Brukernavnet er for kort!",
		'username_too_long'			=> "Brukernavnet er for langt!",
		'pwd_notrepeated'			=> "Passordet ble ikke gjentatt likt.",
		'pwd_tooshort'				=> "Passordet er for kort.",
		'pwd_toolong'				=> "Passordet er for langt.",
		'empty_title'				=> 'Du må fylle ut noe i tittelfeltet',
		'empty_slug'				=> 'Du må fylle ut noe i adressefeltet',
		'slug_contain_specials'		=> 'Adressefeltet inneholder "ulovlige" tegn. Kun a-z, 0-9, bindestrek og nedestrek er tillatt. Kun små bokstaver.',
		'slug_notunique'			=> 'Verdien i adressefeltet er ikke unik. Det finnes allerede en side med denne verdien. Finn på en ny.',
		'title_notunique'			=> 'Verdien i tittelfeltet er ikke unik. Det finnes allerede en side med denne verdien. Finn på en ny.',
		'slug_reserved'				=> 'Verdien i adressefeltet er reservert for troppsportalen og kan ikke benyttes. Finn på en ny.'
	);
	
	var $nomemberships_template = '
		<div style="border: 1px solid #555555; background:#ffffff; padding: 10px;">
			<img src="%warningimage%" style="float:left; width: 60px; margin-right: 10px;" />
			<strong>NB!</strong><br />
			Denne personen har ikke lenger noen tilknytning til organisasjonen.<br />
			Brukeren er deaktivert for innlogging pga. manglende gruppemedlemskap.
			<hr style="clear:both; visibility:hidden; margin: 0px; padding:0px;" />
		</div>';
	
		
	var $hidecontactinfo_template = '<em>%label_onlyforloggedin%</em>';
	
	var $deleteuser_template = '
		<h3>Slett bruker</h3>
		<p>Er du sikker på at du vil SLETTE brukeren \"%name%\"? Dersom brukeren har sluttet i patruljen sin, skal du isteden <a href="index.php?s=0021&amp;u=%id%">avslutte alle medlemskap</a> for brukeren! Hvis du sletter en bruker, blir alle referansepunkter til brukeren ugyldig, noe som kan føre til feilmeldinger. Du bør KUN slette brukere som aldri har vært aktive på troppsportalen!</p>
		<form method="post" action="%posturl%">
			<input type="submit" value="     Ja      " /> 
			<input type="button" value="     Nei      " onclick="window.location=\'%referer%\'" />
		</form>
	';
	var $resendwelcomemail_template = '
		<h3>Send påminnelse om aktivering av brukerkonto</h3>
		<p>&nbsp;</p>
		<form method="post" action="%posturl%">
			<input type="hidden" name="usertoretr" value="%userid%" />
			<input type="submit" value="     Send e-post      " /> 
			<input type="button" value="     Avbryt      " onclick="window.location=\'%referer%\'"/>
		</form>
		<pre style="border: 1px solid #ddd; background: #fff; padding: 8px;">%mail_preview%</pre>
	';
	var $fixrights_template = '
		<h3>Reparer rang og rettigheter</h3>
		<p>Vil du sette rang og rettigheter til gruppe-default for %name%?</p>
		<form method="post" action="%posturl%">
			<input type="hidden" name="usertoretr" value="%userid%" />
			<input type="submit" value="     Ja      " /> 
			<input type="button" value="     Nei      " onclick="window.location=\'%referer%\'" />
		</form>
	';
	
	var $str_header = "Medlemsliste";
	var $str_header_edituser = "Endre bruker";
	
	var $activateNewUserStr;
	
	var $default_rang_classname = "troppsass";

	var $current_medlem;
	var $current_gruppe;
	var $run_action;
	
	function memberlist_actions() {
		$this->memberlist();
	}

	function initialize(){
		@parent::initialize();	
		
		if (count($this->coolUrlSplitted) > 1){
			if ($this->coolUrlSplitted[0] == "activate"){
				$this->activateNewUserStr = $this->coolUrlSplitted[1];
				if (!empty($this->activateNewUserStr)){
					$this->run_action = "activateaccount";
				}
			} else if ($this->coolUrlSplitted[0] == "medlemmer"){
				if ((is_numeric($this->coolUrlSplitted[1])) && ($this->isUser($this->coolUrlSplitted[1]))){ 			
					$this->current_medlem = $this->coolUrlSplitted[1];
				}
			} else if ($this->coolUrlSplitted[0] == "grupper"){
				if ((is_numeric($this->coolUrlSplitted[1])) && ($this->isGroup($this->coolUrlSplitted[1]))){ 		
					$this->current_gruppe = $this->coolUrlSplitted[1];
				}
			}
		}
		if (empty($this->current_medlem) && empty($this->current_gruppe) && count($this->coolUrlSplitted) > 0) {
			$s = addslashes($this->coolUrlSplitted[0]);				
			$res = $this->query("SELECT ident FROM $this->table_memberlist WHERE slug='$s'");
			if ($res->num_rows) {
				$row = $res->fetch_assoc();
				$this->current_medlem = $row['ident'];
			}
		}
		if (empty($this->current_medlem) && empty($this->current_gruppe) && count($this->coolUrlSplitted) > 0) {
			$s = addslashes($this->coolUrlSplitted[0]);
			$res = $this->query("SELECT id FROM $this->table_groups WHERE slug='$s'");
			if ($res->num_rows) {
				$row = $res->fetch_assoc();
				$this->current_gruppe = $row['id'];
			}		
		}
		
		if (isset($_GET['ac'])){
			$this->activateNewUserStr = $_GET['ac'];
			if (!empty($this->activateNewUserStr)){
				$this->run_action = "activateaccount";
			}
		}

		if (isset($_GET['medlem']) && is_numeric($_GET['medlem']) && $this->isUser($_GET['medlem'])){
			$this->current_medlem = $_GET['medlem'];
		} else if (isset($_GET['gruppe']) && is_numeric($_GET['gruppe']) && $this->isGroup($_GET['gruppe'])){
			$this->current_gruppe = $_GET['gruppe'];
		}
		
		
		
		if (isset($_GET['mlvisning'])){ 
			$this->visning = $_GET['mlvisning']; 
		} else { 
			$this->visning = "outline"; 
		}
		if (isset($_GET['showhidden']) && $this->allow_viewhiddengroups){ 
			$this->showHiddenGroups = (($_GET['showhidden'] == true) ? 1 : 0); 
		} else { 
			$this->showHiddenGroups = 0;
		}
		/*
		if (isset($_GET['history'])) $this->run_action = 'history';
		if (isset($_GET['history_iframe'])) $this->run_action = 'history_iframe';
		if (isset($_GET['history_xml'])) $this->run_action = 'history_xml';
		if (isset($_GET['makemembershiplist'])) $this->run_action = 'makemembershiplist';
		if (isset($_GET['editprofile'])) $this->run_action = 'editprofile';
		if (isset($_GET['saveprofile'])) $this->run_action = 'saveprofile';
		if (isset($_GET['edituser'])) $this->run_action = 'edituser';
		if (isset($_GET['deleteuser'])) $this->run_action = 'deleteuser';
		if (isset($_GET['dodeleteuser'])) $this->run_action = 'dodeleteuser';
		if (isset($_GET['resendwelcomemail'])) $this->run_action = 'resendwelcomemail';
		if (isset($_GET['doresendwelcomemail'])) $this->run_action = 'doresendwelcomemail';
		if (isset($_GET['fixrights'])) $this->run_action = 'fixrights';
		if (isset($_GET['dofixrights'])) $this->run_action = 'dofixrights';
		if (isset($_GET['editmemberships'])) $this->run_action = 'editmemberships';
		if (isset($_GET['editrights'])) $this->run_action = 'editrights';
		if (isset($_GET['saverights'])) $this->run_action = 'saverights';
		if (isset($_GET['edittitle'])) $this->run_action = 'edittitle';
		if (isset($_GET['savetitle'])) $this->run_action = 'savetitle';
		if (isset($_GET['editpeff'])) $this->run_action = 'editpeff';
		if (isset($_GET['savepeff'])) $this->run_action = 'savepeff';
		if (isset($_GET['editass'])) $this->run_action = 'editass';
		if (isset($_GET['saveass'])) $this->run_action = 'saveass';
		if (isset($_GET['editgrouppositions'])) $this->run_action = 'editgrouppositions';
		if (isset($_GET['savegrouppositions'])) $this->run_action = 'savegrouppositions';
		if (isset($_GET['savegroupsettings'])) $this->run_action = 'savegroupsettings';
		if (isset($_GET['addmember'])) $this->run_action = 'addmember';
		if (isset($_GET['savenewmember'])) $this->run_action = 'savenewmember';
		if (isset($_GET['memberadded'])) $this->run_action = 'memberadded';
		if (isset($_GET['assignusername'])) $this->run_action = 'assignusername';
		if (isset($_GET['releaseusername'])) $this->run_action = 'releaseusername';
		if (isset($_GET['doreleaseusername'])) $this->run_action = 'doreleaseusername';
		if (isset($_GET['saveusername'])) $this->run_action = 'saveusername';
		if (isset($_GET['usernameassigned'])) $this->run_action = 'usernameassigned';
		if (isset($_GET['addforesatt'])) $this->run_action = 'addforesatt';
		if (isset($_GET['saveforesatt'])) $this->run_action = 'saveforesatt';
		if (isset($_GET['foresatte'])) $this->run_action = 'foresatte';
		if (isset($_GET['removeforesatt'])) $this->run_action = 'removeforesatt';
		if (isset($_GET['savepwd'])) $this->run_action = 'savepwd';
		if (isset($_GET['pwdsaved'])) $this->run_action = 'pwdsaved';
		if (isset($_GET['sendmail'])) $this->run_action = 'sendmail';
		if (isset($_GET['addgroup'])) $this->run_action = 'addgroup';
		if (isset($_GET['doaddgroup'])) $this->run_action = 'doaddgroup';
		if (isset($_GET['sendlogindetails'])) $this->run_action = 'sendlogindetails';
		if (isset($_GET['dosendlogindetails'])) $this->run_action = 'dosendlogindetails';
		
		if (isset($_GET['membershipedit']) && ($_GET['membershipedit'] == 'nyttidl')) $this->run_action = 'addmembershipform';
		if (isset($_GET['membershipedit']) && ($_GET['membershipedit'] == 'nyttaktivt')) $this->run_action = 'addmembershipform';
		if (isset($_GET['membershipedit']) && ($_GET['membershipedit'] == 'slett')) $this->run_action = 'deletemembershipform';
		if (isset($_GET['membershipedit']) && ($_GET['membershipedit'] == 'stopp')) $this->run_action = 'stopmembershipform';
		if (isset($_GET['membershipedit']) && ($_GET['membershipedit'] == 'stoppalle')) $this->run_action = 'stopallmembershipsform';
		if (isset($_GET['membershipedit']) && ($_GET['membershipedit'] == 'innm')) $this->run_action = 'editinnmeldingform';
		if (isset($_GET['membershipedit']) && ($_GET['membershipedit'] == 'innutm')) $this->run_action = 'editinnutmeldingform';
		if (isset($_GET['membershipedit']) && ($_GET['membershipedit'] == 'move')) $this->run_action = 'movememberform';

		if (isset($_GET['membershipsave']) && ($_GET['membershipsave'] == 'nyttidl')) $this->run_action = 'addmembership';
		if (isset($_GET['membershipsave']) && ($_GET['membershipsave'] == 'nyttaktivt')) $this->run_action = 'addmembership';
		if (isset($_GET['membershipsave']) && ($_GET['membershipsave'] == 'slett')) $this->run_action = 'deletemembership';
		if (isset($_GET['membershipsave']) && ($_GET['membershipsave'] == 'stopp')) $this->run_action = 'stopmembership';
		if (isset($_GET['membershipsave']) && ($_GET['membershipsave'] == 'stoppalle')) $this->run_action = 'stopallmemberships';
		if (isset($_GET['membershipsave']) && ($_GET['membershipsave'] == 'innm')) $this->run_action = 'editinnmelding';
		if (isset($_GET['membershipsave']) && ($_GET['membershipsave'] == 'innutm')) $this->run_action = 'editinnutmelding';
		if (isset($_GET['membershipsave']) && ($_GET['membershipsave'] == 'move')) $this->run_action = 'movemember';
		
		if (isset($_GET['editmembershiptype'])) $this->run_action = "editmembershiptype";
		if (isset($_GET['savemembershiptype'])) $this->run_action = "savemembershiptype";
		if (isset($_GET['images'])) $this->run_action = "images";
		
		if (isset($_GET['crop_profileimage'])) $this->run_action = "crop_profileimage";
		else if (isset($_GET['docrop_profileimage'])) $this->run_action = "docrop_profileimage";
		else if (isset($_GET['crop_forumimage'])) $this->run_action = "crop_forumimage";
		else if (isset($_GET['docrop_forumimage'])) $this->run_action = "docrop_forumimage";
		*/
		
		if (!empty($this->login_identifier)){
		
		
			$wLookup = $this->lookup_webmaster;
			$webmaster = call_user_func($this->lookup_webmaster);
			
			if ($webmaster->ident == $this->login_identifier) {
				$this->allow_editpassword = true;			
			}


			/* Tillat endring av egen profil */
			if (isset($this->current_medlem) && ($this->login_identifier == $this->current_medlem)){
				$this->allow_editprofile = true;
				$this->allow_editmemberships = true;
				if ($this->members[$this->login_identifier]->rights >= 4) $this->allow_addcurrentmembership = true;
				$this->allow_editpassword = true;
			}

			/* Tillat endring av profil til egne barn */ 
			if (isset($this->current_medlem) && in_array($this->current_medlem,$this->foresattFor($this->login_identifier))){
				$this->allow_editprofile = true;
				$this->allow_editmemberships = true;		
			}
		
			/* Peff, ass */
			if (isset($this->current_medlem)){
				if ($this->myndighet_i($this->members[$this->current_medlem]->memberof)){	
					$this->allow_viewmemberdetails = true;
					$this->allow_editprofile = true;
					$this->allow_editmemberships = true;
					$this->allow_editforesatte = true;
				}
			} else if (isset($this->current_gruppe)){
				if ($this->myndighet_i($this->current_gruppe)){
					$this->allow_viewmemberdetails = true;
					$this->allow_editprofile = true;
					$this->allow_addmember = true;
					$this->allow_editmemberships = true;
					$this->allow_editforesatte = true;
					$this->allow_editass = true;
				}
			}
	
			/* Rover, leder */
			if ($this->members[$this->login_identifier]->rights >= 5){
				$this->allow_editrights = true;
				$this->allow_addcurrentmembership = true;
				$this->allow_moveuserstononpatruljer = true;
			}
			if ($this->members[$this->login_identifier]->rights >= 4){
			
				$this->allow_viewmemberdetails = true;
				$this->allow_editprofile = true;
				$this->allow_addmember = true;
				$this->allow_addgroup = true; // inkluderer flytting av grupper
				$this->allow_editgroupsettings = true;					
			
				$this->allow_editmemberships = true;
	
				$this->allow_editverv = true;
	
				$this->allow_editpeff = true;
				$this->allow_editass = true;
	
				$this->allow_viewforesatte = true;
				$this->allow_editforesatte = true;
				
				$this->allow_editmembershiptype = true;
				

			// $memberdb->allow_deletemember = true;
			// $memberdb->allow_deletegroup = true;

				$this->allow_edittitles = true;
			

		}
		
		
	}
		

	}

	function run(){
		$this->initialize();

		if (!empty($this->current_medlem)){
			
			call_user_func(
				$this->add_to_breadcrumb,
				call_user_func(
					$this->make_memberlink, 
					$this->current_medlem
				)
			);
			/*
			switch ($this->run_action){

				case 'makemembershiplist':
					return $this->printMembershipsList();
					break;
				case "editprofile":
					return $this->editProfileForm($this->current_medlem);
					break;
				case "saveprofile":
					return $this->saveProfile($this->current_medlem);
					break;
				case "edituser":
					return $this->editUser($this->current_medlem);
					break;
				case "deleteuser":
					return $this->printDeleteUserForm($this->current_medlem);
					break;
				case "dodeleteuser":
					return $this->deleteUser($this->current_medlem);
					break;
				case "resendwelcomemail":
					return $this->printResendWelcomeMailForm($this->current_medlem);
					break;
				case "doresendwelcomemail":
					return $this->resendWelcomeMail($this->current_medlem);
					break;
				case "fixrights":
					return $this->printFixRightsForm($this->current_medlem);
					break;
				case "dofixrights":
					return $this->fixRights($this->current_medlem);
					break;
				case "editrights":
					return $this->editRightsForm($this->current_medlem);
					break;
				case "saverights":
					return $this->saveRights($this->current_medlem);
					break;
				case "editmembershiptype":
					return $this->editMembershipTypeForm($this->current_medlem);
					break;
				case "savemembershiptype":
					return $this->saveMembershipType($this->current_medlem);
					break;
				case "edittitle":
					return $this->editTitleForm($this->current_medlem);
					break;
				case "savetitle":
					return $this->saveTitle($this->current_medlem);
					break;
	
				case "editmemberships":
					return $this->membershipOverview();
					break;
				case "addmembershipform":
					return $this->addMembershipForm($this->current_medlem,$_GET['membershipedit']);
					break;
				case "addmembership":
					return $this->addMembership($this->current_medlem,$_GET['membershipsave']);
					break;
				case "deletemembershipform":
					return $this->deleteMembershipForm($this->current_medlem,$_GET['membership']);
					break;
				case "deletemembership":
					return $this->deleteMembershipFromPOST();
					break;
				case "stopmembershipform":
					return $this->stopMembershipForm($this->current_medlem,$_GET['membership']);
					break;
				case "stopmembership":
					return $this->stopMembershipFromPOST();
					break;
				case "stopallmembershipsform":
					return $this->stopAllMembershipsForm($this->current_medlem);
					break;
				case "stopallmemberships":
					return $this->stopAllMemberships($this->current_medlem);
					break;
				case "editinnmeldingform":
					return $this->editMembershipInnmelding($this->current_medlem,$_GET['membership']);
					break;
				case "editinnmelding":
					return $this->saveMembershipInnmelding();
					break;
				case "editinnutmeldingform":
					return $this->editMembershipInnutmelding($this->current_medlem,$_GET['membership']);
					break;
				case "editinnutmelding":
					return $this->saveMembershipInnutmelding();
					break;
				case "memberadded":
					return $this->memberAdded($this->current_medlem);
					break;
				case "assignusername":
					return $this->assignUsername($this->current_medlem);
					break;
				case "saveusername":
					return $this->saveUsername($this->current_medlem);
					break;
				case "usernameassigned":
					return $this->usernameAssigned($this->current_medlem);
					break;
				case "releaseusername":
					return $this->releaseUsernameForm($this->current_medlem);
					break;
				case "doreleaseusername":
					return $this->releaseUsername($this->current_medlem);
					break;
				case "addforesatt":
					return $this->addForesattForm($this->current_medlem);
					break;
				case "saveforesatt":
					return $this->addForesatt($this->current_medlem);
					break;
				case "foresatte":
					return $this->viewForesatte($this->current_medlem);
					break;
				case "removeforesatt":
					return $this->removeForesatt($this->current_medlem);
					break;
				case "movememberform":
					return $this->moveMemberForm($this->current_medlem);
					break;
				case "movemember":
					return $this->moveMember($this->current_medlem);
					break;
				case "sendlogindetails":
					return $this->sendLoginDetailsForm($this->current_medlem);
					break;
				case "dosendlogindetails":
					return $this->sendLoginDetails($this->current_medlem);
					break;
				case "crop_profileimage":
					return $this->cropProfileImageForm($this->current_medlem);
					break;
				case "crop_forumimage":
					return $this->cropForumImageForm($this->current_medlem);
					break;
				case "docrop_profileimage":
					return $this->cropProfileImage($this->current_medlem);
					break;
				case "docrop_forumimage":
					return $this->cropForumImage($this->current_medlem);
					break;
				case "images":
					return $this->viewImages($this->current_medlem);
					break;
				default:
					return $this->viewProfile($this->current_medlem);
					break;
			}*/
		
		} else if (!empty($this->current_gruppe)){
			
			call_user_func(
				$this->add_to_breadcrumb,
				call_user_func(
					$this->make_grouplink, 
					$this->current_gruppe
				)
			);			
		
		} else {
			/*
			switch ($this->run_action){
				case "editgrouppositions":
					return $this->editGroupPositionsForm();
					break;
				case "savegrouppositions":
					return $this->saveGroupPositions();
					break;
				case "savepwd":
					return $this->saveNewUserPwd($this->activateNewUserStr);
					break;
				case "pwdsaved":
					return $this->pwdSaved();
					break;
				case "activateaccount":
					return $this->activateUserAccount($this->activateNewUserStr);
					break;
				case "addgroup":
					return $this->addGroupForm();
					break;
				case "doaddgroup":
					return $this->addGroup();
					break;
				default:
					return $this->printMemberList();
					break;
			}
			*/
		}


		/* 
		##	Determine and execute requested action
		*/
		$memberActions = array(
			'viewUserProfile', 'editUserProfile', 'saveUserProfile', 'userOverview', 
			'deleteUser', 'deleteUserDo',
			'resendWelcomeMail', 'resendWelcomeMailDo', 'fixRights', 'fixRightsDo', 
			'editUserRights', 'saveUserRights', 'editMembershipType', 'saveMembershipType',
			'editUserTitle', 'saveUserTitle', 'memberAdded', 'assignUsername', 'saveUsername',
			'releaseUsername', 'releaseUsernameDo', 
			
			'viewGuardians', 'addGuardian', 'addGuardianDo', 'removeGuardian', 'removeGuardianDo', 
			
			'moveUser', 'moveUserDo', 'sendLoginDetails', 'sendLoginDetailsDo', 
			'cropProfileImage', 'cropProfileImageDo', 'cropForumImage', 'cropForumImageDo',
			'viewUserPhotos',
			
			'ajaxEditMemberships'
		);
		
		$groupActions = array(
			'editPeff', 'savePeff', 'editAss', 'saveAss', 'saveGroupSettings', 'addMember',
			'addMemberDo', 'history', 'history_iframe', 'history_xml', 'viewGroup',
			'editGroupPositions', 'editGroupPositionsDo'
		);
		
		$generalActions = array(
			'activateAccount', 'assignUserPassword', 'userPasswordAssigned', 'addGroup', 'addGroupDo',
			'viewMemberlist', 'search'
		);
		
		$action = isset($_GET['action']) ? $_GET['action'] : '';
		if (isset($_GET['view_log'])) { $this->current_log = intval($_GET['view_log']); $action = "expandShortUrl"; }
		if (!empty($this->current_medlem)){
			if (!in_array($action,$memberActions)) $action = 'viewUserProfile';
		} else if (!empty($this->current_gruppe)){
			if (!in_array($action,$groupActions)) $action = 'viewGroup';
		} else {
			if (!in_array($action,$generalActions)) $action = 'viewMemberlist';
		}
		return call_user_func(array($this,$action));

	}

	/*******************************************************************************************
		 Utility functions                                                       
		 **************************************************************************************/

	function generateTimeStamp($d,$m,$y, $h,$i){
		// int mktime ( [int hour [, int minute [, int second [, int month [, int day [, int year [, int is_dst]]]]]]] )
		return $timestamp = adodb_mktime($h,$i,0,$m,$d,$y);
	}

	function isValidEmail($email_address) {
		$regex = '/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/';
		return (preg_match($regex, $email_address));
	}

	function invalidInput($errors,$querystr1,$querystr2 = 0){
		$errs = implode('|',array_unique($errors));
		$getdata = array();
		foreach ($_POST as $n => $v){
			array_push($getdata,"$n=".urlencode($v));
		}
		array_push($getdata,$querystr1,"errs=$errs");
		if ($querystr2 != 0) array_push($getdata,$querystr2);
		header("Location: ".$this->generateURL($getdata)."\n\n");
		exit;
	}
	
	function listMemberships($medlem, $asLinks = false) {
			
		$m = $this->members[$medlem];
		if (count($m->memberof) == 0){
			return "<i>(ingen)</i>";
		} else {
			$memberoftmp = array();
			foreach ($m->memberof as $g){
				if ($asLinks) {
					$memberoftmp[] = call_user_func($this->make_grouplink,$g);
				} else {
					$grp = call_user_func($this->lookup_group,$g);
					$memberoftmp[] = $grp->caption;
				}
			}
			$output = $memberoftmp[0];
			if (count($memberoftmp) > 1) {
				for ($i = 1; $i < count($memberoftmp)-1; $i++) {
					$output .= ", ".$memberoftmp[$i];
				}
				$output .= " og ".$memberoftmp[count($memberoftmp)-1];
			}
			return $output;
		}	
	}

	function addprefix($item1, $key, $prefix){
	   $item1 = "$prefix$item1";
	}

	function br2nl($str) {
		$str = str_replace("<br />","\n",$str);
		return $str;
	}
	function nl2br($str){
		$str = str_replace("\r\n","<br />",$str);
		$str = str_replace("\n","<br />",$str);
		return $str;
	}

	function resetMemberStatus($medlem, $silent = false) {
		if (!is_numeric($medlem)) $this->fatalError("variable medlem not integer!");
		if (!$this->isUser($medlem)){ $this->fatalError("Brukeren ".strip_tags($medlem)." eksisterer ikke!"); }
		if (count($this->members[$medlem]->memberof) == 1){
			switch ($this->groups[$this->members[$medlem]->memberof[0]]->kategori){
				case 'LE':
				case 'RO':
				case 'SP':
				case 'SM':
					$def_status = 'betalende';
					break;
				case 'FO':
					$def_status = 'foresatt';
					break;
				case 'PE':
					$def_status = 'pensjonert';
					break;
				case 'ST':
					$def_status = 'stottemedlem';
					break;
				default:
					$def_status = 'ukjent';
					break;			
			}
		} else {
			$def_status = 'ukjent';		
		}
		$this->query("UPDATE $this->table_memberlist SET memberstatus='$def_status' WHERE ident='$medlem'");
		if (!$silent) $this->addToActivityLog("Satt medlemsstatus for ".$this->members[$medlem]->fullname." til $def_status.");
	}
	
	function resetRights($medlem, $silent = false){
		if (!is_numeric($medlem)) $this->fatalError("variable medlem not integer!");
		if (!$this->isUser($medlem)){ $this->fatalError("Brukeren ".strip_tags($medlem)." eksisterer ikke!"); }
		
		if (count($this->members[$medlem]->memberof) == 1){
			$def_rights = $this->groups[$this->members[$medlem]->memberof[0]]->defaultrights;
			$def_rang = $this->groups[$this->members[$medlem]->memberof[0]]->defaultrang;
		} else if (count($this->members[$medlem]->memberof) == 0){
			$def_rights = 0;
			$def_rang = 0;
		} else {
			// We can't reset anything
			return false;
		}
		// if ($def_rang == 0) $def_rang = $this->pensjonert_rang_id;
		
		if ($def_rang == 0) {
			$this->query("UPDATE $this->table_memberlist SET rang='$def_rang' WHERE ident='$medlem' LIMIT 1");
			$this->query("UPDATE $this->table_memberlist SET rights='$def_rights' WHERE ident='$medlem'");
			return "(ingen tittel)";
		} else {
			$res = $this->query("
				SELECT tittel FROM $this->table_rang WHERE id='$def_rang'"
			);
			if ($res->num_rows != 1) $this->fatalError("Det eksisterer ingen rang med id $def_rang!");
			$row = $res->fetch_assoc();
			$tittel = $row['tittel'];
			$rights = $def_rights;
			$gammelrights = $this->members[$medlem]->rights;
			$gammeltittel = $this->members[$medlem]->tittel;
			if ($tittel != $gammeltittel || $rights != $gammelrights){
				$this->query("UPDATE $this->table_memberlist SET rang='$def_rang' WHERE ident='$medlem' LIMIT 1");
				$this->query("UPDATE $this->table_memberlist SET rights='$def_rights' WHERE ident='$medlem'");
				if (!$silent) $this->addToActivityLog("endret rolle fra $gammeltittel til $tittel og rettigheter fra $gammelrights til $rights for ".$this->makeMemberLink($medlem)." som følge av gruppebytte.");
			}
			return $tittel;
		}
	}

	function setRights($medlem, $level){
		global $db;
		if (!is_numeric($level)) $this->fatalError("variable level not integer!");
		if (!is_numeric($medlem)) $this->fatalError("variable medlem not integer!");
		$db->query("UPDATE $this->table_memberlist SET rights=$level WHERE ident=$medlem");
		$this->addToActivityLog("satt rettigheter for ".$this->makeMemberLink($medlem)." til $level.");
	}

	function setRang($medlem, $level){
		if (!is_numeric($level)) $this->fatalError("variable level not integer!");
		if (!is_numeric($medlem)) $this->fatalError("variable medlem not integer!");
		
		if ($level == 0) {
			$this->query("UPDATE $this->table_memberlist SET rang='$level' WHERE ident='$medlem' LIMIT 1");
			return;
		} else {		
			$res = $this->query("
				SELECT tittel FROM $this->table_rang WHERE id='$level'"
			);
			if ($res->num_rows != 1) $this->fatalError("Det eksisterer ingen rang med id $level!");
			$row = $res->fetch_assoc();
			$tittel = $row['tittel'];
			$gammeltittel = $this->members[$medlem]->tittel;
			if ($tittel != $gammeltittel){
				$this->query("UPDATE $this->table_memberlist SET rang='$level' WHERE ident='$medlem' LIMIT 1");
				$this->addToActivityLog("endret rolle for ".$this->makeMemberLink($medlem)." fra $gammeltittel til $tittel.");
			}
		}
	}

	function isForesatt($id){
		$m = $this->members[$id];
		foreach ($m->memberof as $g) {
			if ($this->groups[$g]->kategori == "FO") return true;
		}
		return false;
	}

	function foresatteTil($medlem){
		return $this->members[$medlem]->guardians;
	}

	function foresattFor($foresatt){
		return $this->members[$foresatt]->guarded_by;
	}

	function peff_i($gruppe){
		global $db;
		$vervObj = $this->vervredigeringInstance();
		$peffverv = $vervObj->getVervBySlug('peff');
		$res = $db->query("SELECT $this->table_memberlist.ident ".
			"FROM $this->table_verv,$this->table_vervhistorie,$this->table_memberlist ".
			"WHERE $this->table_verv.id=$peffverv ".
				"AND $this->table_vervhistorie.verv=$this->table_verv.id ".
				"AND $this->table_vervhistorie.gruppe=$gruppe ".
				"AND $this->table_vervhistorie.enddate='0000-00-00' ".
				"AND $this->table_vervhistorie.person=$this->table_memberlist.ident");
		if ($res->num_rows == 1){
			$row = $res->fetch_assoc();
			return $row['ident'];
		} else {
			return -1;
		}	
	}

	function ass_i($gruppe){
		global $db;
		$vervObj = $this->vervredigeringInstance();
		$assverv = $vervObj->getVervBySlug('ass');
		$res = $db->query("SELECT $this->table_memberlist.ident ".
			"FROM $this->table_verv,$this->table_vervhistorie,$this->table_memberlist ".
			"WHERE $this->table_verv.id=$assverv ".
				"AND $this->table_vervhistorie.verv=$this->table_verv.id ".
				"AND $this->table_vervhistorie.gruppe=$gruppe ".
				"AND $this->table_vervhistorie.enddate='0000-00-00' ".
				"AND $this->table_vervhistorie.person=$this->table_memberlist.ident");
		if ($res->num_rows == 1){
			$row = $res->fetch_assoc();
			return $row['ident'];
		} else {
			return -1;
		}	
	}

	function isRover($member){
		foreach ($this->members[$member]->memberof as $g){
			if ($this->groups[$g]->kategori == "RO") return true;
		}
		return false;
	}
	
	/* Sjekk om innlogget bruker er peff/ass i gruppen(e) $grupper eller har rettighetsnivå 2. 
	   $grupper kan enten være en streng eller en array                                        */
	function myndighet_i($grupper){
		if (empty($this->login_identifier)) return false;
		$myndighet = false;
		if ($this->members[$this->login_identifier]->rights > 4){
			$myndighet = true;
		} else if ($this->members[$this->login_identifier]->rights == 3){
			$leder_i_array = $this->isGroupLeader($this->login_identifier);
			$leder_i = $leder_i_array['group'];
			if (is_array($grupper)){
				if (in_array($leder_i,$grupper)){
					$myndighet = true;
				}
			} else {
				if ($leder_i == $grupper){
					$myndighet = true;
				}
			}
		}
		return $myndighet;		
	}

	function setGroupOptions($id,$data) {
				
		$query = "UPDATE $this->table_groups SET id=id";
		
		if (isset($data['caption'])) $query .= ", caption='".addslashes($data['caption'])."'";
		if (isset($data['parent'])) $query .= ", parent='".addslashes($data['parent'])."'";
		if (isset($data['position'])) $query .= ", position='".addslashes($data['position'])."'";
		if (isset($data['visgruppe'])) $query .= ", visgruppe='".addslashes($data['visgruppe'])."'";
		if (isset($data['defaultrights'])) $query .= ", defaultrights='".addslashes($data['defaultrights'])."'";
		if (isset($data['defaultrang'])) $query .= ", defaultrang='".addslashes($data['defaultrang'])."'";
		if (isset($data['slug'])) $query .= ", slug='".addslashes($data['slug'])."'";
		if (isset($data['kategori'])) $query .= ", kategori='".addslashes($data['kategori'])."'";
		if (isset($data['gruppesider'])) $query .= ", gruppesider='".addslashes($data['gruppesider'])."'";
		
		$query .= " WHERE id='$id'";
		
		$this->query($query);
	}
	
	// Send welcome-mail with username and instructions on password-creation to new users
	function sendWelcomeMail($id, $isReminder = false, $preview = false){
		global $login;

		if ($this->isLoggedIn()) {
			if (!$this->allow_addmember) return $this->permissionDenied();
		}

		if (!$this->isUser($id)){ $this->fatalError("Brukeren ".strip_tags($id)." eksisterer ikke!"); }
		$usr = $this->members[$id];
		$rcptmail = $usr->email;
		
		if ($isReminder)
			$template = file_get_contents($this->template_dir.$this->template_activationreminder);
		else
			$template = file_get_contents($this->template_dir.$this->template_welcome);
		
		$r1a = array(); $r2a = array();
		$r1a[] = '%username%';			$r2a[] = $login->getUsername($id);
		$r1a[] = '%recipient_name%';	$r2a[] = $usr->fullname;
		$r1a[] = '%sender_name%';		$r2a[] = $this->members[$this->login_identifier]->fullname;
		if ($preview) {
			$r1a[] = '%activation_url%';	$r2a[] = "[[URL vises ikke i forhåndsvisning]]";		
		} else {
			$r1a[] = '%activation_url%';	$r2a[] = $this->activation_url.$login->getUniqueString($id);
		}
		$plainBody = str_replace($r1a, $r2a, $template);
		
		if ($preview) return $plainBody;
		
		require_once("../www/libs/Rmail/Rmail.php");
		// Send mail		
		$mail = new Rmail();
		$mail->setFrom("$this->mailSenderName <$this->mailSenderAddr>");
		$mail->setReturnPath($this->mailSenderAddr);
		if ($isReminder)
			$mail->setSubject("Påminnelse om brukerkonto hos $this->mailSenderName");
		else
			$mail->setSubject("Velkommen til $this->mailSenderName");
		$mail->setText($plainBody);
		$recipients = array("$rcptmail");
		$mail->setSMTPParams($this->smtpHost,$this->smtpPort,null,true,$this->smtpUser,$this->smtpPass);
		$mail->send($recipients,$type = 'smtp');		
		
		if ($isReminder) 
			$this->addToActivityLog("sendte påminnelse om aktivering av brukernavn til ".$usr->fullname);
			$this->logEvent('ACCOUNT_REMINDER_SENT', $id);

		//else
		//	$this->addToActivityLog("Velkomstmail sendt til ".$usr->fullname);
		
		return $rcptmail;
	}
	

	function createMemberOfRegExp($ident){
		$regexp = "(";
		foreach ($this->members[$ident]->memberof as $g){
			if ($regexp != "(") $regexp .= "|";
			$regexp .= "^$g\$|^$g,|,$g,|,$g\$";

		}
		$regexp .= ")";
		return $regexp;
	}
	
	function getPathToImage($user_id, $directory, $filename = '', $size = 'original') {
		$baseUrl = BG_WWW_PATH.$this->userFilesDir."Medlemsfiler/$user_id/";
		switch ($size) {
			case 'small': return $baseUrl.'_thumbs140/'.$directory.'/'.$filename;
			case 'medium': return $baseUrl.'_thumbs490/'.$directory.'/'.$filename;
			default: return $baseUrl.$directory.'/'.$filename;
		}
	}
	
	function checkUserImageDir($user_id) {
		
		// Check if dir exists "physically":
		$dirs = array(
			$this->path_to_www.'/'.$this->userFilesDir.$this->memberImagesDir,
			$this->path_to_www.'/'.$this->userFilesDir.$this->memberImagesDir.$user_id,
			$this->path_to_www.'/'.$this->userFilesDir.$this->memberImagesDir.$user_id.'/Bilder',
			$this->path_to_www.'/'.$this->userFilesDir.$this->memberImagesDir.$user_id.'/_thumbs140',
			$this->path_to_www.'/'.$this->userFilesDir.$this->memberImagesDir.$user_id.'/_thumbs490'
		);
		foreach ($dirs as $d) { if (!file_exists($d)) if (!mkdir($d)) {
			print $this->notSoFatalError("Uh oh. Kunne ikke opprettet mappen: ".$d);
		}}

		// Check if dir exists in db:
		$res = $this->query("SELECT id FROM $this->table_pages WHERE fullslug=\"brukerfiler/Medlemsfiler/$user_id\"");
		if ($res->num_rows == 0) {

			// Get id of "Medlemsfiler":
			$res = $this->query("SELECT id,owner,ownergroup FROM $this->table_pages WHERE fullslug=\"brukerfiler/Medlemsfiler\"");
			$row = $res->fetch_assoc();
			$parentDirId = intval($row['id']);
			$owner = intval($row['owner']);
			$ownerGroup = intval($row['ownergroup']);

			// Insert new entry:
			$this->query("INSERT INTO $this->table_pages 
				(parent,class,pageslug,fullslug,owner,ownergroup,created,lastmodified) 
				VALUES($parentDirId,1,\"$user_id\",\"brukerfiler/Medlemsfiler/$user_id\",$owner,$ownerGroup,".time().",".time().")");
			if ($this->affected_rows() != 1) {
				$this->fatalError("Could not create user dir in db!");
			}
			$userfolder_id = intval($this->insert_id());
			
		} else {
			$row = $res->fetch_assoc();
			$userfolder_id = intval($row['id']);
		}
		
		// Check if images dir exists in db:
		$res = $this->query("SELECT id FROM $this->table_pages WHERE fullslug=\"brukerfiler/Medlemsfiler/$user_id/Bilder\"");
		if ($res->num_rows == 0) {

			// Insert new entry:
			$this->query("INSERT INTO $this->table_pages 
				(parent,class,pageslug,fullslug,owner,ownergroup,created,lastmodified) 
				VALUES($userfolder_id,1,\"Bilder\",\"brukerfiler/Medlemsfiler/$user_id/Bilder\",$owner,$ownerGroup,".time().",".time().")");
			if ($this->affected_rows() != 1) {
				$this->fatalError("Could not create user image dir in db!");
			}
			
		}
		
	}
	
	function initializeImageArchive() {
		$this->iarchive_instance = new imagearchive(); 
		call_user_func($this->prepare_classinstance, $this->iarchive_instance, $this->imagearchive);
	}


	function simpleDate($d) {
		if ($d == 0) return '';
		$d = getdate($d);
		return $d['mday'].". ".$this->months[$d['mon']-1]." ".$d['year'];
	}

	/*******************************************************************************************
		 Memberlist view utility functions                                                       
		 **************************************************************************************/

	function sortMembersByLastname($a, $b) {
		return strcmp($a->lastname, $b->lastname);
	}
	
	function sortMembersByFirstname($a, $b) {
		return strcmp($a->firstname, $b->firstname);
	}
	
	function sortMembersByAddressId($a, $b) {
		return strcmp($a->address_id, $b->address_id);
	}


	function print_group($id,$left_margin,$membersprinted){
		
		$output = "";
		
		if (($id == 0) || $this->groups[$id]->visgruppe || $this->showHiddenGroups){
			
			if ($this->visning == "bilder" && (!$this->groups[$id]->visbilder)) {
			
			} else {
				if ($id == 0) {
					$childs = array();
					foreach ($this->groups as $g) if ($g->parent == 0) $childs[] = $g->id;
				} else {
					$childs = $this->groups[$id]->children;
				}
				
				$hdr = false;
				if (count($childs) == 0) {
					if (count($this->groups[$id]->members) == 0) {
						if ($this->current_template['show_emptygroups']){
							$hdr = true;
							$output .= $this->printGroupHeader($id,$left_margin,false);
						}
					} else {
						$hdr = true;
						$output .=$this->printGroupHeader($id,$left_margin,true);
						list($tmp,$membersprinted) = $this->print_members($id,$left_margin+30,$membersprinted);
						$output .= $tmp;
						//$this->printGroupFooter($id,$left_margin);					
					}
				} else {
					if (count($this->groups[$id]->realmembers) != 0) {
						$hdr = true;
						$output .= $this->printGroupHeader($id,$left_margin,true);
						list($tmp,$membersprinted) = $this->print_members($id,$left_margin+30,$membersprinted);
						$output .= $tmp;
						//$this->printGroupFooter($id,$left_margin);					
					} else if ($this->current_template['show_emptygroups']) {
						$hdr = true;
						$output .= $this->printGroupHeader($id,$left_margin,false);
					}
					foreach ($childs as $c) {
						list($tmp,$membersprinted) = $this->print_group($c,$left_margin+30,$membersprinted);
						$output .= $tmp;
					}
	
				}
				if ($hdr) $output .= $this->printGroupFooter($id,$left_margin);	
				
				/*
				$res = $this->query("SELECT id,caption FROM grupper WHERE parent='$id' $sh ORDER BY position");
				if ($res->num_rows == 0){
					$this->print_members($id,$left_margin);
				} else {
					while ($row = $res->fetch_assoc()){
						
						//print "<div style='margin-top: 10px; margin-left:".$left_margin."px; font-weight: bold;'>".stripslashes($row['caption'])."</div>";
						$this->print_group($row['id'],$left_margin+30);
					}
				}
				*/
			}
		}
		return array($output,$membersprinted);
	}
	
	function print_members($id,$left_margin,$membersprinted){
		
		$output = $this->current_template['before_members'];
		
		$classNo = 0;
		if (empty($this->current_template['db_criteria'])) {
			$members = $this->groups[$id]->realmembers;
		} else {
			$db_criteria = $this->current_template['db_criteria'];
			$res = $this->query("SELECT ident FROM $this->table_memberlist WHERE $db_criteria");
			$members = array();
			while ($row = $res->fetch_assoc()) {
				$ident = $row['ident'];
				if ($this->isUser($ident) && in_array($id, $this->members[$ident]->memberof)) {
					$members[] = $ident;
				}
			}
		}
		foreach ($members as $m){
			$classNo = !$classNo;
			if (empty($this->current_template['groupheader']) && in_array($m, $membersprinted)) { } else {
				$output .= $this->printMemberListMember($m,0,$classNo,$id,$left_margin);
				$membersprinted[] = $m;
				if (!empty($this->current_template['pagebreak_after'])) {
					$t = count($membersprinted)/$this->current_template['pagebreak_after'];
					if ($t == round($t)) $output .= $this->current_template['pagebreak_item'];
				}
			}
		}
		
		$output .= $this->current_template['after_members'];
		return array($output,$membersprinted);
		
	}

	function printGroupHeader($id,$left_margin,$hasMembers){
	
		$gruppe = $this->groups[$id];
		$styl = ""; if ($gruppe->visgruppe == 0) $styl = "font-style: italic;";
		$gruppe_url = (empty($gruppe->slug)) ? 
			$this->generateCoolURL("/grupper/$id") : 
			$this->generateCoolURL("/$gruppe->slug");
		
		$r1a[] = '%imagedir%';			$r2a[] = $this->image_dir;
		$r1a[] = '%left_margin%';		$r2a[] = $left_margin;
		$r1a[] = '%caption%';			$r2a[] = $gruppe->caption;
		$r1a[] = '%group_url%';			$r2a[] = $gruppe_url;
		$r1a[] = '%morestyle%';			$r2a[] = "";
		$r1a[] = '%id%';				$r2a[] = $id;
		$r1a[] = '%displaycontent%';	$r2a[] = ($hasMembers ? "none" : "block");
		$r1a[] = '%displaycollapsed%';	$r2a[] = ($hasMembers ? "inline" : "none");
		$r1a[] = '%displayopen%';		$r2a[] = ($hasMembers ? "none" : "inline");
		
		return str_replace($r1a, $r2a, $this->current_template['groupheader']);
		
	}
	
	function printGroupFooter($id, $left_margin) {
		
		$gruppe = $this->groups[$id];
		$styl = ""; if ($gruppe->visgruppe == 0) $styl = "font-style: italic;";
		$gruppe_url = (empty($gruppe->slug)) ? 
			$this->generateCoolURL("/grupper/$id") : 
			$this->generateCoolURL("/$gruppe->slug");
		
		$r1a[] = '%bg_image%';			$r2a[] = $this->image_dir."user4.gif";
		$r1a[] = '%left_margin%';		$r2a[] = $left_margin;
		$r1a[] = '%caption%';			$r2a[] = $gruppe->caption;
		$r1a[] = '%group_url%';			$r2a[] = $gruppe_url;
		$r1a[] = '%morestyle%';			$r2a[] = "";
		$r1a[] = '%id%';				$r2a[] = $id;
		
		return str_replace($r1a, $r2a, $this->current_template['groupfooter']);
		
	}

	function printMemberListMember($id, $online, $classNo, $gruppe,$left_margin){
		global $login;
		
		$foresatteListe = $this->foresatteTil($id);
		$foresatte = "";  $foresatteWithPhone = "";
		foreach ($foresatteListe as $f){
			$foresatte .= "<div>".$this->members[$f]->fullname."</div>";
			$foresatteWithPhone .= "<div><a href=\"".$this->generateCoolURL("/medlemmer/".$this->members[$f]->ident)."\">".$this->members[$f]->fullname."</a> (Tlf: ".$this->members[$f]->homephone.", mobil: ".$this->members[$f]->cellular.")</div>";
		}
		if (empty($foresatte)) $foresatte = " - ";
		if (empty($foresatteWithPhone)) $foresatteWithPhone = " - ";
		$u = $this->members[$id];
		
		$memberof = array();
		$memberShortCategory = "-"; $mscid = 0;
		foreach ($u->memberof as $g){
			$grp = call_user_func($this->lookup_group,$g);
			$memberof[] = $grp->caption;
			if ($this->kategoriOrder[$grp->kategori] > $mscid) {
				$mscid = $this->kategoriOrder[$grp->kategori];;
				$memberShortCategory = $grp->kategori;
			}
		}
		if (isset($this->memberCount[$memberShortCategory])) $this->memberCount[$memberShortCategory]++;
		$memberof = implode(", ",$memberof);
		
		
		if (isset($this->mtypedescs[$u->memberstatus])) 
			if ($u->memberstatus == 'betalende') 
				$membershiptype = "<span style='color:#008800'>".$this->mtypedescs[$u->memberstatus]."</span>";
			else if ($u->memberstatus == 'stottemedlem') 
				$membershiptype = "<span style='color:#0000BB'>".$this->mtypedescs[$u->memberstatus]."</span>";
			else
				$membershiptype = "<span style='color:#9999bb'>".$this->mtypedescs[$u->memberstatus]."</span>";
		else
			$membershiptype = "<span style='color:#f00;'>Ukjent</span>";
					
		$r1a   = array(); 					$r2a   = array();
		if (strpos($this->current_template['member'],'%lastlogin%') !== false) {
			$accs = '';
			if ($login->hasPassword($u->ident)) $accs = 'Ok';
			else if ($login->hasUsername($u->ident)) $accs = 'Ikke aktivert. <a href="%memberlink%?resendwelcomemail">Send reg-mail på nytt</a>';
			$r1a[] = '%accountstatus%';			$r2a[] = $accs;
			$r1a[] = '%lastlogin%';				$r2a[] = $this->simpleDate($login->getLoginTime($u->ident));
		}		
		$r1a[] = '%firstname%';				$r2a[] = $u->firstname;
		$r1a[] = '%middlename%';			$r2a[] = $u->middlename;
		$r1a[] = '%lastname%';				$r2a[] = $u->lastname;
		$r1a[] = '%street%';				$r2a[] = $u->street;
		$r1a[] = '%streetno%';				$r2a[] = $u->streetno;
		$r1a[] = '%postno%';				$r2a[] = $u->postno;
		$r1a[] = '%city%';					$r2a[] = $u->city;
		$r1a[] = '%address_id%';			$r2a[] = $u->address_id;
		$r1a[] = '%fullname%';				$r2a[] = (!empty($this->login_identifier)) ? $u->fullname : $u->firstname;
		$r1a[] = '%epost%';					$r2a[] = $u->email;
		$r1a[] = '%classname%';				$r2a[] = $u->classname;
		$r1a[] = '%memberlink%';			$r2a[] = (empty($u->slug) ? 
															$this->generateCoolURL("/medlemmer/$u->ident") : 
															$this->generateCoolURL("/$u->slug")
														);
		$r1a[] = '%onlineindicator%';		$r2a[] = ($online ? ' <img src="'.$this->image_dir.'globe.gif" alt="Dette medlemmet er online!" />' : '');
		$r1a[] = '%firstname%';				$r2a[] = $u->firstname;
		$r1a[] = '%memberimage%';			$r2a[] = $this->getProfileImage($id);
		$r1a[] = '%memberimagewidth%';		$r2a[] = $this->profile_image_width;
		$r1a[] = '%memberimageheight%';		$r2a[] = $this->profile_image_height;
		$r1a[] = "%trclass%";				$r2a[] = "mlist".($classNo+1);
		$r1a[] = "%tdclass%";				$r2a[] = "mlist".($classNo+1);
		$r1a[] = "%gruppe%";				$r2a[] = $this->groups[$gruppe]->caption;
		$r1a[] = "%homephone%";				$r2a[] = $u->homephone;
		$r1a[] = "%cellular%";				$r2a[] = $u->cellular;
		$r1a[] = "%address%";				$r2a[] = $u->street." ".$u->streetno."<br />".$u->postno." ".$u->city;
		$r1a[] = '%foresatte%';				$r2a[] = $foresatte;
		$r1a[] = '%foresattewithphone%';	$r2a[] = $foresatteWithPhone;
		$r1a[] = '%leftmargin%';			$r2a[] = $left_margin;
		$r1a[] = '%middlename%';			$r2a[] = $u->middlename;
		$r1a[] = '%lastname%';				$r2a[] = $u->lastname;
		$r1a[] = '%birthday%';				$r2a[] = ($this->validDate($u->bday) ? strftime('%d.%m.%Y',$u->bday) : '<em>Ikke oppgitt</em>');
		$r1a[] = '%rights%';				$r2a[] = $u->rights;
		$r1a[] = '%memberof%';				$r2a[] = $memberof;
		$r1a[] = '%membershortcat%';		$r2a[] = $memberShortCategory;
		$r1a[] = '%tittel%';				$r2a[] = $u->tittel;
		$r1a[] = '%rang%';					$r2a[] = $u->rang;
		$r1a[] = '%rights%';				$r2a[] = $u->rights;
		//$r1a[] = '%username%';				$r2a[] = $u->username;
		$r1a[] = '%membershiptype%';		$r2a[] = $membershiptype;
		return str_replace($r1a, $r2a, $this->current_template['member']);
	}
	

	/*******************************************************************************************
		 General actions                                                       
		 **************************************************************************************/
	
	function search() {
		if (empty($_POST) || !isset($_POST['json'])) {
			print "invalid json request";
			exit();
		}
		$json = json_decode($_POST['json']);
		
		$gids = array();
		$uids = array();
		
		if (isset($json->freetext)) {
			if (preg_match('/rights=([0-9])/', $json->freetext, $matches)) {
				$uids = $this->findAllUsersWithRights($matches[1]);
			} else if (preg_match('/email=([\w@._\-]*)/', $json->freetext, $matches)) {
				$uids = $this->findAllUsersWithEmail($matches[1]);
			} else {
				$uids = $this->freeTextUserSearch($json->freetext);
				$gids = $this->freeTextGroupSearch($json->freetext);
			}
		}


		$ud = $this->getUserData($uids, array('UserId','FullName','ProfileUrl','ProfilePicture','Memberships','ActiveMemberships','Verv','AktiveVerv'));
		$users = array();
		foreach ($ud as $id => $u) {
			foreach ($u['Memberships'] as &$m) {
				$m['StartDate'] = strftime('%e. %B %Y',$m['StartDate']);
				$m['EndDate'] = strftime('%e. %B %Y',$m['EndDate']);
			}
			$users[] = $u;
		}
		usort($users, array("memberlist_actions", "sortSearchResultsUsers"));
		
		$groups = array();
		foreach ($gids as $gid) {
			$groups[] = $this->getGroupById($gid);
		}
		
		header("Content-Type: text/xml; charset=utf-8");
		print json_encode(array(
			'groups' => $groups,
			'users' => $users
		));
		exit();
	}
	
	function findAllUsersWithRights($r) {
		$r = intval($r);
		$res = $this->query('SELECT ident FROM '.$this->table_memberlist.' WHERE rights='.$r);
		$ids = array();
		while ($row = $res->fetch_assoc()) {
			$ids[] = intval($row['ident']);
		}
		return $ids;		
	}
	
	function findAllUsersWithEmail($e) {
		$e = addslashes($e);
		if (empty($e)) {
			$res = $this->query('SELECT ident FROM '.$this->table_memberlist.' WHERE email=""');
		} else {
			$res = $this->query('SELECT ident FROM '.$this->table_memberlist.' WHERE email LIKE "%'.$e.'%"');
		}
		$ids = array();
		while ($row = $res->fetch_assoc()) {
			$ids[] = intval($row['ident']);
		}
		return $ids;		
	}
	
	function freeTextGroupSearch($q) {
		$q = addslashes($q);		
		$res = $this->query('SELECT id FROM '.$this->table_groups.' WHERE caption LIKE "'.$q.'%"');
		$ids = array();
		while ($row = $res->fetch_assoc()) {
			$ids[] = intval($row['id']);
		}
		return $ids;
	}
	
	function freeTextUserSearch($q) {
		$q = addslashes($q);		
		$res = $this->query('SELECT ident FROM '.$this->table_memberlist.' 
			WHERE CONCAT(firstname," ",middlename," ",lastname) LIKE "'.$q.'%"
			OR CONCAT(firstname," ",lastname) LIKE "'.$q.'%"
			OR lastname LIKE "'.$q.'%"
		');
		$ids = array();
		while ($row = $res->fetch_assoc()) {
			$ids[] = intval($row['ident']);
		}
		return $ids;		
	}
	
	function sortSearchResultsUsers($a,$b) {
		if (count($a['ActiveMemberships']) == count($b['ActiveMemberships'])) {
			return strcmp($a['FullName'],$b['FullName']);		
		}
		return (count($a['ActiveMemberships']) > count($b['ActiveMemberships'])) ? -1 : 1;
	}
	
	function viewMemberList(){
	
		if (!$this->allow_viewlist) return $this->permissionDenied();
		
		$output = "
		<script type=\"text/javascript\">
	
			var allGroups = new Array();
	
			function expandAll() {
				for (var n = 0; n < allGroups.length; n++) {
					var id = allGroups[n];
					if (Element.getStyle('gruppe'+id,'display') == 'none') {
						Element.toggle('gruppe'+id);
						Element.toggle('indicator_c'+id);
						Element.toggle('indicator_o'+id);
					}
				}
			}
			function collapseAll() {
				for (var n = 0; n < allGroups.length; n++) {
					var id = allGroups[n];
					if (Element.getStyle('gruppe'+id,'display') != 'none') {
						Element.toggle('gruppe'+id);
						Element.toggle('indicator_c'+id);
						Element.toggle('indicator_o'+id);
					}
				}				
			}
		
			function toggleGroup(id) {
				new Effect.toggle('gruppe'+id, 'slide', {  
					duration: .4,
					afterFinish: function(obj){
						Element.toggle('indicator_c'+id);
						Element.toggle('indicator_o'+id);
					}
				});
			}
		
		</script>
		
		";
		
		// Toolbar:
		$output .= '<p class="hidefromprint">';
		if ($this->isLoggedIn()){
			$output .= '
				<a href="'.$this->getMemberUrl($this->login_identifier).'?action=editUserProfile" class="icn" 
				style="background-image:url(/images/icns/vcard_edit.png);background-color:#ffffdd;">Rediger min medlemsprofil</a>';
		}
		if ($this->allow_addgroup){
			$output .= ' 
				<a href="'.$this->generateURL('action=addGroup').'" class="icn" 
					style="background-image:url(/images/icns/group_add.png);">Opprett gruppe</a> 
				<a href="/registrering/?reglist" class="icn" 
					style="background-image:url(/images/icns/report.png);">Vis registreringer</a>';
		} 
		$output .= '</p>';
		
		if (($this->visning == "tabell1") || ($this->visning == "tabell2") || ($this->visning == "tabell3") || ($this->visning == "tabell4") || ($this->visning == "tabell5")){
			if (!$this->isLoggedIn()){
				$output .= '<p class="cal_notice">Du må logge inn for å se på den valgte visningen. Viser normalvisning isteden</p>';
				$this->visning = "outline";
			}
		}
		$options = "";
		$res = $this->query("SELECT 
				id, identifier, title, show_emptygroups, 
				listheader, listfooter, 
				groupheader, groupfooter, 
				before_members, member, after_members,
				db_criteria, pagebreak_after, pagebreak_item, sortby
			FROM 
				$this->table_list_templates"
		);
		while ($row = $res->fetch_assoc()) {
			$identifier = $row['identifier'];
			$d = ($identifier == $this->visning) ? " selected='selected'" : "";
			$title = $row['title'];
			$options .= "<option value='$identifier'$d>$title</option>";
			if ($identifier == $this->visning) 
				$this->current_template = $row;
		}

		$output .= '
			<p class="hidefromprint">
			Visning:
			<select name="mlvisning" onchange=\'location = "'.($this->showHiddenGroups ? $this->generateURL("showhidden=$this->showHiddenGroups")."&amp;" : $this->generateURL('').'?').'mlvisning="+this.options[this.selectedIndex].value;\'>
				'.$options.'
			</select>
		
		';
		/*
						<option value='normal' ".(($this->visning == "normal")?"selected":"").">Normal</option>
				<option value='bilder' ".(($this->visning == "bilder")?"selected":"").">Bilder</option>
				<option value='epost' ".(($this->visning == "tabell1")?"selected":"").">E-postliste</option>
				<option value='kontaktinfo' ".(($this->visning == "tabell2")?"selected":"").">Kontaktinformasjon</option>
				<option value='foresatt' ".(($this->visning == "tabell3")?"selected":"").">Foresatt-relasjoner</option>
				<option value='brukerinfo' ".(($this->visning == "tabell4")?"selected":"").">Bruker-tabell</option>
				<option value='rettigheter' ".(($this->visning == "tabell5")?"selected":"").">Rettighets-oversikt</option>
				<option value='betalende' ".(($this->visning == "tabell5")?"selected":"").">Betalende medlemmer</option>
*/
		
		if ($this->allow_viewhiddengroups){
			$output .= " Vis skjulte grupper: ";
			$output .= "
				<select name='showhidden' onchange=\"location = '".$this->generateURL("mlvisning=$this->visning")."&amp;showhidden='+this.options[this.selectedIndex].value\">
					<option value='1'".(($this->showHiddenGroups)?" selected=\"selected\"":"").">Ja</option>
					<option value='0'".((!$this->showHiddenGroups)?" selected=\"selected\"":"").">Nei</option>
				</select>
			";
		} else {
			$output .= " <i> Logg inn for flere valg</i>";
		}
		

		$output .= "</p>\n";
		$output .= "\n";
		
		// Online users:
		$res = $this->query("SELECT memberid FROM $this->table_onlineusers WHERE sessionid != '' ORDER BY timestamp DESC");
		$onlineusers = array();
		while($record = $res->fetch_assoc()){
			array_push($onlineusers,$record['memberid']);
		}
		
		if (empty($this->current_template)) {
			return $this->notSoFatalError("Malen eksisterer ikke.");
		}
		
		$res = $this->query("SELECT sid, description FROM $this->table_membershiptypes");
		$this->mtypedescs = array();
		while ($row = $res->fetch_assoc()) {
			$this->mtypedescs[$row['sid']] = $row['description'];
		}

		
		$theList = stripslashes($this->current_template['listheader']);

		$classNo = 1;
		
		$membersprinted = array();
		$this->memberCount = array('LE' => 0, 'RO' => 0, 'SP' => 0, 'SM' => 0);
		if (!empty($this->current_template['sortby'])) {
			
			if (empty($this->current_template['db_criteria'])) {
				$members = $this->members;
			} else {
				$db_criteria = $this->current_template['db_criteria'];
				$res = $this->query("SELECT ident FROM $this->table_memberlist WHERE $db_criteria");
				$members = array();
				while ($row = $res->fetch_assoc()) {
					$ident = $row['ident'];
					if ($this->isUser($ident) && count($this->members[$ident]->memberof) > 0) {
						$members[] = $this->members[$ident];
					}
				}
			}
			if ($this->current_template['sortby'] == 'lastname') {
				usort($members, array("memberlist_actions", "sortMembersByLastname"));
			} else if ($this->current_template['sortby'] == 'firstname') {
				usort($members, array("memberlist_actions", "sortMembersByFirstname"));			
			} else if ($this->current_template['sortby'] == 'address_id') {
				usort($members, array("memberlist_actions", "sortMembersByAddressId"));			
			} else {
				$this->notSoFatalError('Unknown sort criteria '.$this->current_template['sortby'].' (in printMemberList)');
			}
			foreach ($members as $m){
				if (count($m->memberof) > 0) {
					$theList .= $this->printMemberListMember($m->ident,0,1,1,0);
					$membersprinted[] = $m->ident;
					if (!empty($this->current_template['pagebreak_after'])) {
						$t = count($membersprinted)/$this->current_template['pagebreak_after'];
						if ($t == round($t)) $theList .= $this->current_template['pagebreak_item'];
					}
				}
			}
			
			if ($this->current_template['identifier'] == "nsf") {
				$tmp = array();
				foreach ($this->memberCount as $code => $count) {
					$tmp[] = $count.' '.strtolower($this->getCategoryByAbbr($code))." ($code)";
				}
				$tmp = "<p>Totalt ".implode(', ',$tmp)."</p>";
				$theList = $tmp.$theList;
			}
		} else {
			
			foreach ($this->groups as $g) {
				if ($g->parent == 0) {
					list($tmp,$membersprinted) = $this->print_group($g->id,0,$membersprinted);
					$theList .= $tmp;
				}
			}
			
		}	
		$theList .= $this->current_template['listfooter'];
		
		$theList .= "<p>&nbsp;</p>";
		
		$output .= $theList;
		return $output;
	}
	

	function activateUserAccount(){
		global $login;
		
		$unique = $this->activateNewUserStr;		
		$u = $login->getUserIdFromUniqueString($unique);
		if ($u === false){
			return $this->notSoFatalError("Fant ikke noen brukerkonto tilknyttet denne adressen. Er du sikker på at du fikk med hele adressen?");
		}

		if (!$login->hasUsername($u)) {
			return $this->notSoFatalError("Fant ikke noen brukerkonto tilknyttet denne adressen. Er du sikker på at du fikk med hele adressen?");
		}
		
		if ($login->hasPassword($u)) {
			return $this->notSoFatalError("Denne kontoen er allerede aktivert.");
		}

		$url_post = $this->generateURL('action=assignUserPassword');
		
		$errors = '';
		if (isset($_SESSION['errors'])) {
			foreach ($_SESSION['errors'] as $err) {
				$errors .= $this->notSoFatalError($this->errorMessages[$err]);
			}
			unset($_SESSION['errors']);
		}
		
		return '
			<h3>Velkommen '.$this->members[$u]->fullname.'!</h3>
			<form method="post" action="'.$url_post.'">
				<p>
					Velkommen til '.$this->site_name.' sin internettportal! 
					For å kunne logge inn må du lage deg ditt eget passord. 
					Velg fritt, så lenge du holder deg mellom '.$login->getMinPwdLen().' og '.$login->getMaxPwdLen().' tegn.
				</p>
				'.$errors.'
				<table cellpadding="0" cellspacing="0">
					<tr><td>Ditt nye passord:</td><td><input type="password" size="30" name="nyttpassord1" /></td></tr>
					<tr><td>Gjenta ditt nye passord:</td><td><input type="password" size="30" name="nyttpassord2" /></td></tr>
				</table>
				<br />
				<input type="submit" value="Lagre" />
			</form>
		';
	}

	function assignUserPassword(){
		global $login;

		$unique = $this->activateNewUserStr;		

		$id = $login->getUserIdFromUniqueString($unique);

		if ($id === false){
			return $this->notSoFatalError("Fant ikke noen brukerkonto tilknyttet denne adressen. Er du sikker på at du fikk med hele adressen?");
		}
		
		if ($login->hasPassword($id)) {
			return $this->notSoFatalError("Denne kontoen ($id) er allerede aktivert (1).");
		}

		// Initialize
		if ($login->isLoggedIn()){ 
			$login->logout();
		}

		if ($_POST["nyttpassord1"] <> $_POST["nyttpassord2"]){
			$_SESSION['errors'] = 'Du skrev ikke passordet likt begge gangene.'; 
			$this->redirect($this->generateURL('',true));
		}
		
		$errors = $login->setPassword($id,$_POST["nyttpassord1"]);
		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors); 
			$this->redirect($this->generateURL('',true));
		}

		// Activitylog
		$this->addToActivityLog("lagret passord / aktiverte brukerkonto for ".$this->makeMemberLink($id));
		$this->logEvent('ACCOUNT_ACTIVATED', $id);
		
		$this->redirect($this->generateCoolURL('/','action=userPasswordAssigned'));

	}

	function userPasswordAssigned(){
		if (!$this->isLoggedIn()){
			return "
				<h3>Passord lagret</h3>
				<p>
					Nå kan du prøve å logge inn med brukernavnet du fikk på e-post og passordet du nettopp laget.
				</p>
			";
		} else {
			return '
				<h3>Gratulerer, du er nå logget inn!</h3>
			<p>
			En av de første tingene du bør gjøre på '.$this->server_name.', er å oppdatere 
			medlemsprofilen din med korrekte opplysninger. Telefonnr. og adresse er det viktig at du legger
			inn, så vi kan holde kontakten med deg. Kontaktinformasjon blir kun vist for innloggede brukere.
			</p>
			<p>
				<a href="'.$this->getMemberUrl($this->login_identifier).'?action=editUserProfile">Trykk her for å oppdatere medlemsprofilen din</a>
			</p>
			';			
		}
	}

	/*******************************************************************************************
		 User actions                                                       
		 **************************************************************************************/
	
	function viewUserProfile(){
		global $login;
		
		$uid = $this->current_medlem;
		$u = $this->getUserData($uid, 'All');
		
		$output = "";

		$importantinfo = "";
		if (count($u['ActiveMemberships']) == 0){
			$importantinfo = str_replace("%warningimage%",$this->image_dir."warning.gif",$this->nomemberships_template);
			$memberof = "<i>Denne personen har for tiden ingen aktive medlemsskap.</i>";
		}
		
		
		$memberof = "lalala";
		/*
			$memberoftmp = array();
			foreach ($u->memberof as $g){
				if ($this->isLoggedIn()) {
					$memberoftmp[] = call_user_func($this->make_grouplink,$g);
				} else {
					$grp = call_user_func($this->lookup_group,$g);
					$memberoftmp[] = $grp->caption;
				}
			}
			$memberof = $memberoftmp[0];
			if (count($memberoftmp) > 1) {
				for ($i = 1; $i < count($memberoftmp)-1; $i++) {
					$memberof .= ", ".$memberoftmp[$i];
				}
				$memberof .= " og ".$memberoftmp[count($memberoftmp)-1];
			}
		}
		*/
		
		
		if ($this->isLoggedIn()) {
			$output .= "<p class='headerLinks'>";
			
			if ($this->allow_editprofile)
				$output .= '  <a href="'.$this->generateURL('action=editUserProfile').'" class="edit">Rediger brukerprofil</a>';
				
			if ($this->allow_viewmemberdetails) 
				$output .= '  <a href="'.$this->generateURL('action=userOverview').'" class="edit">Rediger brukerinnstillinger</a>';
			
			$output .= '
				</p>
			';
		}
		
		/* Verv */
		$verv = array();
		$shortVerv = array();
		if (count($u['Verv']) > 0) {
			foreach ($u['Verv'] as $v) {
				$r1a   = array(); 		$r2a   = array();
				$r1a[] = "%post%";		$r2a[] = '<a href="'.$v['Url'].'">'.$v['Title'].'</a>';
				$r1a[] = "%from%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$v['StartDate']).'">'.strftime("%B %Y",$v['StartDate']).'</abbr>';
				$r1a[] = "%to%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$v['EndDate']).'">'.strftime("%B %Y",$v['EndDate']).'</abbr>';
				//$r1a[] = "%group%"; 	$r2a[] = $v['group_caption'];
				if ($v['EndDate'] == 0){
					$verv[] = "<li class='star'>".str_replace($r1a,$r2a,$this->label_post_since)."</li>\n";
					$shortVerv[] = $v['Title'];
				} else {
					$verv[] = "<li class='star'>".str_replace($r1a,$r2a,$this->label_post_fromto)."</li>\n";
				}
			}
		}

/*		$shortVerv = implode(",",$shortVerv);
		$shortVerv = $profile->tittel;
		if (strlen($shortVerv) > 0) $shortVerv = $shortVerv . ". ";		


		/**** VERV ****/
		/*
		$verv = array();
		$shortVerv = array();
		$tv = $this->table_verv;
		$th = $this->table_vervhistorie;
		$tg = $this->table_groups;
		$rs = $this->query("SELECT 
			$tv.caption,$tv.slug,$th.startdate, $th.enddate, $th.gruppe as group_id, $tg.caption as group_caption 
			FROM $tv,$th 
			LEFT JOIN $tg ON $tg.id=$th.gruppe
			WHERE $th.person=".$profile->ident." AND $th.verv=$tv.id");
		if ($rs->num_rows > 0){
			//$verv .= $this->label_posts.":\n";
			while ($row = $rs->fetch_assoc()){
				$verv_url = '/verv/'.$row['slug'];
				if (!empty($row['group_id'])) {
					$g = $this->getGroupById($row['group_id']);
					$verv_url .= '/'.$g->slug;
				}
				$row['startdate'] = strtotime($row['startdate']);
				$row['enddate'] = ($row['enddate']=='0000-00-00') ? 0 : strtotime($row['enddate']);
				$r1a   = array(); 		$r2a   = array();
				$r1a[] = "%post%";		$r2a[] = '<a href="'.$verv_url.'">'.$row['caption'].'</a>';
				$r1a[] = "%from%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$row['startdate']).'">'.strftime("%B %Y",$row['startdate']).'</abbr>';
				$r1a[] = "%to%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$row['enddate']).'">'.strftime("%B %Y",$row['enddate']).'</abbr>';
				$r1a[] = "%group%"; 	$r2a[] = $row['group_caption'];
				if ($row['enddate'] == 0){
					$list_item = "<li class='star'>".str_replace($r1a,$r2a,$this->label_post_since)."</li>\n";
					$shortVerv[] = $row['caption'];
				} else {
					$list_item = "<li class='star'>".str_replace($r1a,$r2a,$this->label_post_fromto)."</li>\n";
				}
				$verv[] = array(
					'row' => $row,
					'list_item' => $list_item,
					'printed' => false
				);
			}
		}	*/

/*		$shortVerv = implode(",",$shortVerv);
		$shortVerv = $profile->tittel;
		if (strlen($shortVerv) > 0) $shortVerv = $shortVerv . ". ";
*/		


		
		/* Group memberships */
		$membershipStart = time(); // Første tilknytning til gruppen
		$medlemskap = array();
		foreach ($u['Memberships'] as $m) {
			$group_verv = array();
			foreach ($u['Verv'] as $v) {
				if (isset($v['GroupId']) && ($v['GroupId'] == $m['GroupId'])) {
					if ((($v['StartDate']) >= $m['StartDate']-86400) && ($v['EndDate'] <= ($m['EndDate']+86400) || $row['EndDate'] == 0)) {
						$r1a   = array(); 		$r2a   = array();
						$r1a[] = "%post%";		$r2a[] = '<a href="'.$v['Url'].'">'.$v['Title'].'</a>';
						$r1a[] = "%from%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$v['StartDate']).'">'.strftime("%B %Y",$row['StartDate']).'</abbr>';
						$r1a[] = "%to%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$v['EndDate']).'">'.strftime("%B %Y",$row['EndDate']).'</abbr>';
						//$r1a[] = "%group%"; 	$r2a[] = $g['Caption'];
						if ($row['enddate'] == 0){
							$group_verv[] = "<li class='star'>".str_replace($r1a,$r2a,$this->label_post_since)."</li>\n";
							$shortVerv[] = $v['Title'];
						} else {
							$group_verv[] = "<li class='star'>".str_replace($r1a,$r2a,$this->label_post_fromto)."</li>\n";
						}

						$group_verv[] = '<li class="star">'.str_replace($r1a,$r2a,$this->label_post_fromto)."</li>\n";
					}
				}
			}
			$verv_item = '';
			if (count($group_verv) > 0) {
				$verv_item = '<ul class="custom_icons">'.implode(" ",$group_verv).'</ul>';
			}
			if ($m['EndDate'] == 0){
				$r1a[] = "%from%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$m['StartDate']).'">'.strftime("%Y",$m['StartDate']).'</abbr>';
				$medlemskap[] = "<li class='group'>".str_replace($r1a,$r2a,$this->label_memberof_since).$verv_item."</li>\n";
			} else {
				$r1a[] = "%from%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$m['StartDate']).'">'.strftime("%Y",$m['StartDate']).'</abbr>';
				$r1a[] = "%to%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$m['EndDate']).'">'.strftime("%Y",$m['EndDate']).'</abbr>';
				$medlemskap[] = "<li class='group'>".str_replace($r1a,$r2a,$this->label_memberof_fromto).$verv_item."</li>\n";
			}
		}
		
		/*
		while ($row = $rs->fetch_assoc()){
			$row['startdate'] = strtotime($row['startdate']);
			$row['enddate'] = ($row['enddate']=='0000-00-00') ? 0 : strtotime($row['enddate']);

			$r1a = array(); $r2a = array();
			if ($this->isLoggedIn()) {
				$r1a[] = "%group%";	$r2a[] = call_user_func($this->make_grouplink,$row['group_id']);
			} else {
				$r1a[] = "%group%";	$grp = call_user_func($this->lookup_group,$row['group_id']); $r2a[] = $grp->caption;
			}
			if ($row['startdate'] < $membershipStart) $membershipStart = $row['startdate'];
			
			$group_verv = array();
			foreach ($verv as &$v) {
				if ($v['row']['group_id'] == $row['group_id']) {
					if ((($v['row']['startdate']) >= $row['startdate']-86400) && ($v['row']['enddate'] <= ($row['enddate']+86400) || $row['enddate']=='0')) {
						$group_verv[] = $v['list_item'];
						$v['printed'] = true;
					}
				}
			}
			$verv_item = '';
			if (count($group_verv) > 0) {
				$verv_item = '<ul class="custom_icons">'.implode(" ",$group_verv).'</ul>';
			}
			
			if ($row['enddate'] == 0){
				$r1a[] = "%from%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$row['startdate']).'">'.strftime("%Y",$row['startdate']).'</abbr>';
				$medlemskap[] = "<li class='group'>".str_replace($r1a,$r2a,$this->label_memberof_since).$verv_item."</li>\n";
			} else {
				$r1a[] = "%from%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$row['startdate']).'">'.strftime("%Y",$row['startdate']).'</abbr>';
				$r1a[] = "%to%"; 		$r2a[] = '<abbr title="'.strftime("%d.%m.%Y",$row['enddate']).'">'.strftime("%Y",$row['enddate']).'</abbr>';
				$medlemskap[] = "<li class='group'>".str_replace($r1a,$r2a,$this->label_memberof_fromto).$verv_item."</li>\n";
			}
		}*/
		
		$medlemskapOgVerv = "<ul class='custom_icons'>\n";
		$medlemskapOgVerv .= implode($verv);
		$medlemskapOgVerv .= implode($medlemskap);		
		$medlemskapOgVerv .= "</ul>\n";

		$membershipLength = date("Y",$membershipStart);

		$res = $this->query("SELECT body FROM $this->table_memberlistlocal WHERE id='$uid' AND lang='$this->preferred_lang'");
		if ($res->num_rows == 1) {
			$row = $res->fetch_row(); 
			$notes = stripslashes($row[0]);
		} else {
			$notes = "";
		}

		$res = $this->query("SELECT count(id) FROM $this->table_news WHERE creator='$uid'");
		$row = $res->fetch_row(); 
		$news_count = $row[0];

		$res = $this->query("SELECT count(id) FROM $this->table_comments WHERE author_id='$uid'");
		$row = $res->fetch_row(); 
		$comment_count = $row[0];
		
		$res = $this->query("SELECT count(id) FROM $this->table_forumposts WHERE author='$uid'");
		$row = $res->fetch_row(); 
		$forumpost_count = $row[0];

		$res = $this->query("SELECT count(id) FROM bg_imgarchive_files WHERE uploadedby='$uid'");
		$row = $res->fetch_row(); 
		$photo_count = $row[0];
		
		if ($this->use_wordbox) {
			$rs = $this->query("SELECT count(id) FROM $this->table_wordbox WHERE author='$uid'");
			$shout_count = $rs->fetch_row(); $shout_count = $shout_count[0];
		} else {
			$shout_count = "N/A";
		}
				
		$res = $this->query("SELECT sid, description FROM $this->table_membershiptypes");
		$mtypedescs = array();
		while ($row = $res->fetch_assoc()) {
			$mtypedescs[$row['sid']] = $row['description'];
		}
		
		
		/*
		if (in_array($u->memberstatus, $mtypedescs)) 
			$membershiptype = $mtypedescs[$profile->memberstatus];
		else
			$membershiptype = "<span style='color:#f00;'>Ukjent</span>";

		*/

		if ($this->isLoggedIn()) {

			$home_address = ($u['Street'] == "") 
				? '<em>Mangler adresse</em>' 
				: $u['Street'].' '.$u['StreetNo'].'<br />'.$u['PostCode'].' '.$u['City'];
			if ($u['Country'] != 'no') {
				if (!empty($u['State'])) $home_address .= '<br />'.$u['State'].', '.$this->countries[strtoupper($u['Country'])];
				else $home_address .= '<br />'.$this->countries[strtoupper($u['Country'])];
			}
			if (empty($u['HomePhone']) && empty($u['CellPhone'])) $phone = '<em>Mangler telefonnr.</em>';
			else if (empty($u['HomePhone'])) $phone = $u['CellPhone'];
			else if (empty($u['CellPhone'])) $phone = $u['HomePhone'];
			else $phone = $u['HomePhone'].' / '.$u['CellPhone'];
			$email = empty($u['Email']) ? 
				'<em>Mangler epost</em>' : 
				'<a href="%sendmsgurl%">'.$u['Email'].'</a>';
		} else {
			$home_address = '<em>Vises kun for innloggede brukere</em>'; 
			$phone = '<em>Vises kun for innloggede brukere</em>'; 
			$email = '<a href="%sendmsgurl%">Send melding</a>';	
		}
			$www = empty($u['Webpage']) ? '' : 
				"<div style='background:url(/images/icns/world.png) no-repeat top left;padding:2px 20px;'>
					<a href=\"".$u['Webpage']."\">".$u['Webpage']."</a>
				</div>";

			$activity = array();
			if ($news_count > 0) $activity[] = "$news_count nyheter"; 
			if ($forumpost_count > 0) $activity[] = "$forumpost_count foruminnlegg"; 
			if ($comment_count > 0) $activity[] = "$comment_count kommentarer"; 
			if ($shout_count > 0) $activity[] = "$shout_count snikksnakkmeldinger"; 
			if (count($activity) > 0) {
				if (count($activity) > 1) {
					$last = array_pop($activity);
					$activity = "Har skrevet ".implode($activity,', ').' og '.$last;
				} else {
					$activity = "Har skrevet ".$activity[0];
				}
				if ($photo_count > 0) $activity .= ", og lastet opp $photo_count bilder til bildearkivet";
				$activity .= '.';
			} else {
				if ($photo_count > 0) $activity = "Har lastet opp $photo_count bilder til bildearkivet";
				else $activity = "Har ikke vært aktiv på nettsiden enda";			
			}

			
			$lastlogin = $login->getLoginTime($uid);
			$ldiff = floor((mktime(23,59,59)-$lastlogin)/86400);
			if ($ldiff < 1) $ldiff = 'i dag';
			else if ($ldiff == 1) $ldiff = 'i går';
			else if ($ldiff > 60) $ldiff = 'for '.round($ldiff/30.5).' måneder siden';
			else $ldiff = 'for '.$ldiff.' dager siden';
			if (empty($lastlogin)) 
				$llogin = "<div style='background:url(/images/icns/error.png) no-repeat top left;padding:2px 20px;'>Har aldri logget inn</div>";
			else
				$llogin = "<div style='background:url(/images/icns/tick.png) no-repeat top left;padding:2px 20px;'>Sist innlogget $ldiff.</div>";

			$contactinfo = "
				<div style='float:left;padding-top:10px;width:350px;'>
					<div style='background:url(/images/icns/house.png) no-repeat top left;padding:2px 20px;'>$home_address</div>
					<div style='background:url(/images/icns/phone.png) no-repeat top left;padding:2px 20px;'>$phone</div>
					<div style='background:url(/images/icns/email.png) no-repeat top left;padding:2px 20px;'>$email</div>
					$www
					$llogin
					<div style='background:url(/images/icns/zoom.png) no-repeat top left;padding:2px 20px;'>$activity</div>
				</div>
			";

/*

		<table>
			<tr><td valign="top">%label_address%: </td><td>%address%</tr>
			<tr><td valign="top">%label_homephone%: </td><td>%homephone%</tr>
			<tr><td valign="top">%label_cellular%: </td><td>%cellular%</tr>
			<tr><td valign="top">%label_email%: </td><td>%email%</tr>
		</table>
*/		
		
		$fullname = $u['FirstName'].' '.$u['MiddleName'].' '.$u['LastName'];
		switch (strtolower($u['Title'])) {
			case 'pensjonert':
				$title_fullname = "$fullname (pensjonert speider)";
				break;
			case 'foresatt':
				$title_fullname = "$fullname (foresatt)";
				break;
			default:
				$title_fullname = $u['Title']." $fullname";			
		}
		
		$this->document_title = $fullname;
		$r1a = array();							$r2a = array();
		$r1a[] = '%id%';						$r2a[] = $uid;
		$r1a[] = '%contactinfo%';				$r2a[] = $contactinfo; 
		$r1a[] = '%firstname%';					$r2a[] = $u['FirstName'];
		$r1a[] = '%lastname%';					$r2a[] = $u['LastName'];
		$r1a[] = '%fullname%';					$r2a[] = $fullname;
		$r1a[] = '%title_fullname%';			$r2a[] = $title_fullname;
		$r1a[] = '%nickname%';					$r2a[] = $u['NickName'];
		//$r1a[] = '%address_id%';				$r2a[] = $profile->address_id;
		$r1a[] = '%mapurl%';					$r2a[] = str_replace($r1a, $r2a, $this->mapurl_template);
		$r1a[] = '%email%';						$r2a[] = empty($u['Email']) ? "<em>Ikke oppgitt</em>" : $u['Email'];
		$r1a[] = '%birthday%';					$r2a[] = ($this->validDate($u['Birthday']) ? strftime('%d.%m.%Y',$u['Birthday']) : '<em>Ikke oppgitt</em>');
		$r1a[] = '%alder%';						$r2a[] = ($this->validDate($u['Birthday']) ? strftime("%Y",time()-$u['Birthday'])-1970 : '<em>Ikke oppgitt</em>');
		$r1a[] = '%homepage%';					$r2a[] = ($u['Webpage'] != "") ? '<a href="'.$u['Webpage'].'" target="blank">Klikk for å åpne</a>' : '';
		$r1a[] = '%notes%';						$r2a[] = $notes;
		$r1a[] = '%src%';						$r2a[] = $this->getProfileImage($uid,'small');
		$r1a[] = '%imagewidth%';				$r2a[] = $this->profile_image_width;
		$r1a[] = '%imageheight%';				$r2a[] = $this->profile_image_height;
		$r1a[] = '%sendmsgurl%';				$r2a[] = $this->messageUrl."?recipients=".$uid;
		$r1a[] = '%memberof%';					$r2a[] = $memberof;
		$r1a[] = '%importantinfo%';				$r2a[] = $importantinfo;

		$r1a[] = '%label_memberprofile%';		$r2a[] = $this->label_memberprofile;		
		$r1a[] = '%label_contactinfo%';			$r2a[] = $this->label_contactinfo;		
		$r1a[] = '%label_address%';				$r2a[] = $this->label_address;		
		$r1a[] = '%label_homephone%';			$r2a[] = $this->label_homephone;		
		$r1a[] = '%label_cellular%';			$r2a[] = $this->label_cellular;		
		$r1a[] = '%label_onlyforloggedin%';		$r2a[] = $this->label_onlyforloggedin;		

		$r1a[] = '%label_name%';				$r2a[] = $this->label_name;		
		$r1a[] = '%label_nick%';				$r2a[] = $this->label_nick;
		$r1a[] = '%label_birthday%';			$r2a[] = $this->label_birthday;
		$r1a[] = '%label_homepage%';			$r2a[] = $this->label_homepage;
		$r1a[] = '%label_email%';				$r2a[] = $this->label_email;
		$r1a[] = '%label_lastloggedon%';		$r2a[] = $this->label_lastloggedon;
		$r1a[] = '%label_messagesonforum%';		$r2a[] = $this->label_messagesonforum;
		$r1a[] = '%label_messagesonwordbox%';	$r2a[] = $this->label_messagesonwordbox;
		$r1a[] = '%label_memberof%';			$r2a[] = $this->label_memberof;
		$r1a[] = '%membership_length%';			$r2a[] = $membershipLength;		
		$r1a[] = '%image_dir%';					$r2a[] = $this->image_dir;
//		$r1a[] = '%imgarchive_imgs%';			$r2a[] = $i_images;
		//$r1a[] = '%membershiptype%';			$r2a[] = $membershiptype;


/*
		%image%
		 <br />
		<b>%fullname%</b> (<em>%nickname%</em>)<br />
		%verv%<br />
		%label_memberof% %memberof%.<br />
		Tilknyttet gruppen siden %membership_length%.
		<!--
		<b>%label_birthday%:</b> %birthday%<br />
		<b>%label_homepage%:</b> %homepage%<br />
		<b>%label_lastloggedon%:</b> %lastlogin%<br />
		<b>%label_messagesonforum%:</b> %forumposts%<br />
		-->
		Var sist logget inn på gruppens nettsider %lastlogin%. Har skrevet %shouts% snikksnakkmeldinger og %forumposts% innlegg på forumet.
<!--		<b>%label_messagesonwordbox%:</b> %shouts%<br />
		<b>%label_memberof%:</b> %memberof%<br />
	-->	
	<br /><br />
		<img src="%image_dir%mail.gif" alt="Epost" /> <a href="%sendmsgurl%">Send epost</a>
		*/
		
		$updates = '';
		$events = $this->_eventlog->getLatestUserEvents($uid, 10); # base
		$cday = '';
		foreach ($events as $event) {
			if (strftime('%Y',$event['timestamp']) != strftime('%Y',time())) 
				$eday = strftime('%e. %B %Y',$event['timestamp']);
			else
				$eday = strftime('%e. %B',$event['timestamp']);
			if ($cday != $eday) {
				$cday = $eday;
				$updates .= '<div style="font-size:10px;font-weight:bold;padding:3px;color:#686;">'.strftime('%e. %B %Y',$event['timestamp']).'</div>';
			}
			$updates .= '<div style="background:url(/images/icns/'.$event['icon'].'.png) top left no-repeat;padding: 1px 3px 3px 20px; margin:2px;">
			'.$event['text'].'
			</div>';
		}

		$template = '
		%importantinfo%
		
		<h3>%title_fullname%</h3>
		Tilknyttet gruppen siden %membership_length%.<br />

		<div style="float:left; width: 130px;">
			<div class="alpha-shadow noframe">
  				<div class="inner_div">
					<img src="%src%" style="width: %imagewidth%px; height: %imageheight%px;" alt="%firstname% %lastname%" />
				</div>
			</div>
		</div>
		%contactinfo%

		

		<!--
		&nbsp;<br />
		%label_memberof% %memberof%.<br />
			<b>%label_birthday%:</b> %birthday%<br />
			<b>%label_homepage%:</b> %homepage%<br />
			<b>%label_lastloggedon%:</b> %lastlogin%<br />
			<b>%label_messagesonforum%:</b> %forumposts%<br />
		%lastlogin%. Har skrevet %shouts% snikksnakkmeldinger og %forumposts% innlegg på forumet.
			<b>%label_messagesonwordbox%:</b> %shouts%<br />
			<b>%label_memberof%:</b> %memberof%<br />
		-->	

		<div style="clear:both; height:1px;"><!-- --></div>

		<div padding-top:10px;">%notes%</div>
		
		';

		$output .= str_replace($r1a, $r2a, $template);


		$output .= "<div style='clear:both;'><!-- --></div>";
		$output .= "<table><tr><td width='50%' valign='top'>";

			$output .= "<h4>Oppdateringer:</h4>\n";
			if ($this->isLoggedIn()) {	
				$output .= $updates;
			} else {
				$output .= "<em>Vises kun for innloggede brukere</em>";
			}

		$output .= "</td><td width='50%' valign='top'>";
		if ($this->use_imagearchive) {
			$this->initializeImageArchive();
		 	$this->iarchive_instance->str_before_imagethumbs = '<h4>Fra bildearkivet:</h4><div>';
			$this->iarchive_instance->str_after_imagethumbs = '</div>
				<p style="clear:both;padding-top:30px;"><a href="'.$this->generateURL('action=viewUserPhotos').'" class="icn" style="background-image:url(/images/icns/photos.png);">Vis alle bilder med '.$u['FirstName'].' (Totalt %count% stk.)</a></p>';
		/* 	$this->iarchive_instance->str_before_imagethumbs = '<div style="float:right; width:170px; border-left:1px solid #ccc; padding-left:5px;margin-left:5px; margin-bottom:10px;"><h4 style="margin:0px;padding:0px; text-align:center;">Fra bildearkivet:</h4><div>
		 	';
			$this->iarchive_instance->str_after_imagethumbs = '</div><p style="text-align:center; font-size:80%"><a href="'.$this->generateURL("images").'">Vis alle bilder med '.$profile->firstname.' (Totalt %count% stk.)</a></p></div>
			';
			*/
			$i_images = $this->iarchive_instance->getRandomMemberImages($uid, 3);
		} else {
			$i_images = "";
		}
		$output .= $i_images;
		$output .= "</td></tr></table>";
		$output .= "<div style='clear:both;'><!-- --></div>";
		
		
		
		$output .= "<h4>$this->label_membershipsandposts:</h4>\n";
		$output .= $medlemskapOgVerv;
		
		$foresatte = $this->foresatteTil($uid);
		if (count($foresatte) > 0){
			$output .= "
				<h4>Foresatt(e):</h4>
			";
			
			if ($this->isLoggedIn()){
				$output .= "
					<ul class='custom_icons'>
				";
				foreach ($foresatte as $f){
					$output .= '<li class="annet"><a href="'.$this->getMemberUrl($f).'">'.$this->members[$f]->fullname.'</a></li>';
				}
				$output .= "</ul>";
			} else {
				$output .= str_replace('%label_onlyforloggedin%', $this->label_onlyforloggedin, $this->hidecontactinfo_template);
			}
		}
		

	
				
		$foresatte = $this->foresattFor($uid);
		if (count($foresatte) > 0){
			$output .= "
				<h4>Foresatt for:</h4>
			";
			if ($this->isLoggedIn()){
				$output .= "
					<ul class='custom_icons'>
				";
				foreach ($foresatte as $f){
					$output .= '<li class="annet"><a href="'.$this->getMemberUrl($f).'">'.$this->members[$f]->fullname.'</a></li>';
				}
				$output .= "</ul>";
			} else {
				$output .= str_replace('%label_onlyforloggedin%',$this->label_onlyforloggedin,$this->hidecontactinfo_template);
			}
		}

		
		
		return $output;
	}
	
	function viewUserPhotos() {

		call_user_func(
			$this->add_to_breadcrumb,
			'<a href="'.$this->generateURL('action=viewUserPhotos').'">Bilder</a>'
		);
		
		$id = $this->current_medlem;
	
		if (!$this->isLoggedIn()) return $this->permissionDenied();

		$member = $this->members[$id];
		$this->document_title = 'Bilder av '.$member->firstname;
		if ($this->use_imagearchive) {
			$this->initializeImageArchive();
		 	$this->iarchive_instance->str_before_imagethumbs = '<h3>Bilder av '.$member->firstname.' (%count% stk.)</h3><p><a href="'.$this->generateURL('').'">Tilbake til medlemsprofil</a></p><div>
		 	';
			$this->iarchive_instance->str_after_imagethumbs = '</div>';
			$i_images = $this->iarchive_instance->getRandomMemberImages($id, 999);
		} else {
			$i_images = "";
		}
				
		return '
			<div style="float:right; width: '.($this->profile_image_width/2+20).'px;">
				<div class="alpha-shadow noframe">
					<div class="inner_div">
						<img src="'.$this->getProfileImage($id,'small').'" style="width: '.($this->profile_image_width/2).'px; height: '.($this->profile_image_height/2).'px;" alt="'.$member->fullname.'" />
					</div>
				</div>
			</div>
			'.$i_images.'
			<div style="clear:both;">&nbsp;&nbsp;&nbsp;</div>
		';		

	}
	
	function editUserProfile(){
	
		global $login;
		
		$id = $this->current_medlem;
	
		if (!is_numeric($id)){ $this->fatalError("Ugyldig inn-data!"); }
		if (!$this->isUser($id)) return $this->noSoFatalError("Brukeren ".strip_tags($id)." eksisterer ikke!");
		if (!isset($this->login_identifier)) return $this->permissionDenied();
		if (!$this->allow_editprofile) return $this->permissionDenied();
		
		// Check if user image directory exists:
		$this->checkUserImageDir($id);
		
		$output = "";

		$profile = $this->members[$id];
				
		$res = $this->query("SELECT body FROM $this->table_memberlistlocal WHERE id='$id' AND lang='$this->preferred_lang'");
		if ($res->num_rows == 1) {
			$row = $res->fetch_row(); 
			$notes = htmlspecialchars(stripslashes($row[0]));
		} else {
			$notes = "";
		}		
		$currentyear = date("Y",time());
		$yearrange = ($currentyear - 90)."-".($currentyear-4);

		$firstname		= $profile->firstname;
		$middlename		= $profile->middlename;
		$lastname		= $profile->lastname;
		$street			= $profile->street;
		$streetno		= $profile->streetno;
		$postno			= $profile->postno;
		$city			= $profile->city;
		$state			= $profile->state;
		$country		= strtoupper($profile->country);
		$homephone		= $profile->homephone;
		$cellular		= $profile->cellular;
		$nickname		= $profile->nickname;
		$birthday		= $profile->bday;
		$email			= $profile->email;
		$homepage		= $profile->homepage;
		$notes			= $notes;
		$slug			= $profile->slug;
		
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
			$output .= $this->notSoFatalError($errstr,array('logError'=>false,'customHeader'=>'Profilen ble ikke lagret fordi:'));

			$postdata = $_SESSION['postdata'];
			$firstname = $postdata['firstname'];
			$middlename = $postdata['middlename'];
			$lastname = $postdata['lastname'];
			$street = $postdata['street'];
			$streetno = $postdata['streetno'];
			$postno = $postdata['postno'];
			$city = $postdata['city'];
			$state = $postdata['state'];
			$country = $postdata['country'];
			$homephone = $postdata['homephone'];
			$cellular = $postdata['cellular'];
			$nickname = $postdata['nickname'];			
			$birthday = $postdata['birthday'];
			$bday_unix = strtotime($birthday);
			if ($bday_unix < strtotime('1900-01-01')) $bday_unix = 0;
			$birthday = $bday_unix;

			$email = $postdata['email'];
			$homepage = $postdata['homepage'];
			$notes = $postdata['notes'];
			$slug = $postdata['slug'];
			
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);
			
		}
		
		$born_str = $this->validDate($birthday) ? strftime('%e. %B %Y',$birthday) : '<em>Ikke oppgitt</em>';
		$born_code = $this->makeDateField("birthday", $birthday, false);
		$born_date_js = $this->validDate($birthday) ? strftime('{ day:%e, month:%m, year:%Y }',$birthday) : '0';
		$max_date_js = strftime('%m/%d/%Y',time()-5*365*24*3600);
		$born_code .= 'Tips: trykk på årstallet for å skrive inn et annet år.';
			
		$beholdchecked = ($profile->profilbilde != "") ? " checked='checked'" : "";
		$nyttchecked = ($profile->profilbilde != "") ? "" : " checked='checked'";
		
		$_SESSION['CKFinder_UserRole'] = "user$id";
		
		$profilbilde_code = '
							<input type="hidden" name="profile_image" id="profile_image" value="" />
							<a href="#" class="bildevelgerlink" onclick="BrowseServer1(); return false;" title="Trykk for å velge bilde">
								<table cellspacing="0" cellpadding="0" width="140" height="140"><tr><td valign="middle" align="center">
									<span id="profilbildespan">
										<img src="'.$this->getProfileImage($id).'" border="0" alt="Velg bilde" style="margin:5px;" />
									</span>
								</td></tr></table>
							</a>
		';
		$forumbilde_code = '
							<input type="hidden" name="forum_image" id="forum_image" value="" />
							<a href="#" class="bildevelgerlink" onclick="BrowseServer2(); return false;" title="Trykk for å velge bilde">
								<table cellspacing="0" cellpadding="0" width="140" height="140"><tr><td valign="middle" align="center">
									<span id="forumbildespan">
										<img src="'.$this->getForumImage($id).'" border="0" alt="Velg bilde" style="margin:5px;" />
									</span>
								</td></tr></table>
							</a>
		';

/*		$beholdchecked = ($profile->forumbilde != "") ? " checked='checked'" : "";
		$nyttchecked = ($profile->forumbilde != "") ? "" : " checked='checked'";
		$gammeltbilde = '<img src="'.$this->getForumImage($id).'" style="width:50px; height: 50px; margin: 3px; margin-right: 6px; border: 0px;" alt="Forumbilde" />';
		$forumbilde_code = "
				<table cellpadding='0' cellspacing='0'>
					<tr>
						<td valign='top'>
							$gammeltbilde
						</td><td valign='top'>
							Last opp et nytt:<br />
							<input name='forumbilde' type='file' />
						</td>
					</tr>
				</table>
		";*/
		
		$editpwdcode = '
						<p>
							Hvis du vil endre passord, skriver du inn et nytt passord her.
							Passordet må være på mellom '.$login->getMinPwdLen().' og '.$login->getMaxPwdLen().' tegn.
						</p>
						<table>
							<tr><td style="text-align:right;">Nytt passord: </td><td><input name="nyttpassord1" type="password" /> </td></tr>
							<tr><td style="text-align:right;">Gjenta nytt passord: </td><td><input name="nyttpassord2" type="password" /></td></tr>
						</table>
		';
		
		$countrylist = '';
		foreach ($this->countries as $c => $n) {
			$countrylist .= ($country == $c) ?  
				"\t\t\t<option value=\"$c\" selected=\"selected\">$n</option>\n" : "\t\t\t<option value=\"$c\">$n</option>\n";
		}
			
		$memberships = $this->membershipOverview();
		$memberships = $memberships['html'];
		$post_url  = $this->generateURL('action=saveUserProfile');
		
		$r1a = array(); 						$r2a = array();
		$r1a[] = '%editpassword%';				$r2a[] = $this->allow_editpassword ? $editpwdcode : '<em>'.$this->label_pwdChangeNotAllowed.'</em>';
	
		$r1a[] = '%id%';						$r2a[] = $id;
		$r1a[] = '%postUrl%';					$r2a[] = $post_url;
		$r1a[] = '%imagedir%';					$r2a[] = $this->image_dir;
		$r1a[] = '%fckPath%';					$r2a[] = $this->pathToFCKeditor;

		$r1a[] = '%fullname%';					$r2a[] = $profile->fullname;
		$r1a[] = '%firstname%';					$r2a[] = $firstname;
		$r1a[] = '%middlename%';				$r2a[] = $middlename;
		$r1a[] = '%lastname%';					$r2a[] = $lastname;
		$r1a[] = '%street%';					$r2a[] = $street;
		$r1a[] = '%streetno%';					$r2a[] = $streetno;
		$r1a[] = '%postno%';					$r2a[] = $postno;
		$r1a[] = '%city%';						$r2a[] = $city;
		$r1a[] = '%homephone%';					$r2a[] = $homephone;
		$r1a[] = '%cellular%';					$r2a[] = $cellular;
		$r1a[] = '%email%';						$r2a[] = $email;
		$r1a[] = '%birthday%';					$r2a[] = $born_code;
		$r1a[] = '%born_date_js%';				$r2a[] = $born_date_js;
		$r1a[] = '%max_date_js%';				$r2a[] = $max_date_js;
		$r1a[] = '%state%';						$r2a[] = $state;
		$r1a[] = '%countrylist%';				$r2a[] = $countrylist;
		$r1a[] = '%disp_norway%';				$r2a[] = ($country=='NO')?'block':'none';
		$r1a[] = '%disp_foreign%';				$r2a[] = ($country=='NO')?'none':'block';
		
		$r1a[] = '%homepage%';					$r2a[] = $homepage;		
		$r1a[] = '%nickname%';					$r2a[] = $nickname;
		$r1a[] = '%userimage%';					$r2a[] = $profilbilde_code;
		$r1a[] = '%forumavatar%';				$r2a[] = $forumbilde_code;
		$r1a[] = '%aboutme%';					$r2a[] = $notes;		
		$r1a[] = '%localFlag%';					$r2a[] = $this->local_flag;

		$r1a[] = '%memberships%';				$r2a[] = $memberships;

		$r1a[] = '%userFilesDir%';				$r2a[] = '/'.$this->userFilesDir."Medlemsfiler/$id/";
		$r1a[] = '%smallThumbsDir%';			$r2a[] = '/'.$this->userFilesDir."Medlemsfiler/$id/_thumbs140/";

		$r1a[] = '%adressPrefix%';				$r2a[] = "http://".$this->server_name."/medlemmer/";
		$r1a[] = '%slug%';						$r2a[] = $slug;

		$r1a[] = "%ckfinder_uri%";				$r2a[]  = LIB_CKFINDER_URI;
		$r1a[] = "%ckeditor_uri%";				$r2a[]  = LIB_CKEDITOR_URI;

		$template = file_get_contents($this->template_dir.$this->template_editprofileform);		
		$output .= str_replace($r1a, $r2a, $template);

		return $output;

	}

	function saveUserProfile(){
		global $login;
		
		$id = $this->current_medlem;
			
		$profile = $this->members[$id];

		if (!$this->allow_editprofile) return $this->permissionDenied();		
		
		/** VALIDATE **/

		$errors = array();
		if (empty($_POST['firstname'])) array_push($errors,"empty_firstname");
		if (empty($_POST['lastname'])) array_push($errors,"empty_lastname");
		
		if ($login->hasUsername($id)) {
			if ((!empty($profile->email)) && (empty($_POST['email']))) array_push($errors,"empty_email");
			if (!empty($_POST['email']) && !$this->isValidEmail($_POST['email'])) array_push($errors,"invalid_email");
		}
		
		if (!empty($_POST['nyttpassord1']) || !empty($_POST['nyttpassord2'])) {
			if ($_POST['nyttpassord1'] <> $_POST['nyttpassord2']){
				array_push($errors,"pwd_notrepeated");
			}
		}
		
		if (intval($_POST['birthday_year']) < 1900) {
			$birthday = '0000-00-00';
		} else { 
			$birthday = $_POST['birthday_year'].'-'.$_POST['birthday_month'].'-'.$_POST['birthday_day'];
			if (strlen($birthday) != 10) $birthday = '0000-00-00';
		}
		$_POST['birthday'] = $birthday;
		
		if ((!empty($_POST['postno'])) && (!is_numeric($_POST['postno']))) array_push($errors,"notint_postno");				
		if ((!empty($_POST['homephone'])) && (preg_match("/[^0-9\+]/",$_POST['homephone']))) array_push($errors,"invalid_homephone");
		if ((!empty($_POST['cellular'])) && (preg_match("/[^0-9\+]/",$_POST['cellular']))) array_push($errors,"invalid_cellular");

		if (isset($_POST['slug']) && !empty($_POST['slug'])){ 
			$slug = addslashes($_POST['slug']);
			$res1 = $this->query("SELECT id FROM $this->table_groups WHERE slug='$slug'");
			$res2 = $this->query("SELECT ident FROM $this->table_memberlist WHERE slug='$slug' AND ident!='$id'");
			if ($res1->num_rows > 0 || $res2->num_rows > 0) array_push($errors,"slug_notunique");
			else if (preg_match("/[^a-z0-9_-]/",$slug)) array_push($errors,"slug_contain_specials");
			else if (in_array($slug,$this->reserved_slugs)) array_push($errors,"slug_reserved");
		}
				
		$updates = array();

		$old = trim($this->members[$id]->firstname.' '.$this->members[$id]->middlename.' '.$this->members[$id]->lastname,' ,');
		$new = trim($_POST['firstname'].' '.$_POST['middlename'].' '.$_POST['lastname'],' ,');
		if ($old != $new) {
			$this->logEvent('USER_PROFILE_NAME_UPDATED', $id, 0, $old, $new);
			$updates['navn'] = true;
		}
		
		$old = $this->members[$id]->street.' '.$this->members[$id]->streetno.', '.$this->members[$id]->postno.' '.$this->members[$id]->city;
			if (!empty($this->members[$id]->state)) $old .= ', '.$this->members[$id]->state;
			if ($this->members[$id]->country != 'no') $old .= ', '.$this->countries[strtoupper($this->members[$id]->country)];
			$old = trim($old,', ');
		$new = $_POST['street'].' '.$_POST['streetno'].', '.$_POST['postno'].' '.$_POST['city'];
			if (!empty($_POST['state'])) $new .= ', '.$_POST['state'];
			if (strtolower($_POST['country']) != 'no') $new .= ', '.$this->countries[strtoupper($_POST['country'])];
			$new = trim($new,', ');
		if ($old != $new) {
			$updates['hjemmeadresse'] = true;
			if (empty($old)) $this->logEvent('USER_PROFILE_ADDRESS_ADDED', $id, 0, $new);
			else $this->logEvent('USER_PROFILE_ADDRESS_UPDATED', $id, 0, $old, $new);
		}

		$old = trim($this->members[$id]->homephone);
		$new = trim($_POST['homephone']);
		if ($old != $new) {
			$updates['telefonnumre'] = true;
			if (empty($old)) $this->logEvent('USER_PROFILE_PHONE_ADDED', $id, 0, $new);
			else if (empty($new)) $this->logEvent('USER_PROFILE_PHONE_REMOVED', $id, 0, $old);
			else $this->logEvent('USER_PROFILE_PHONE_UPDATED', $id, 0, $old, $new);	
		}

		$old = trim($this->members[$id]->cellular);
		$new = trim($_POST['cellular']);
		if ($old != $new) {
			$updates['telefonnumre'] = true;
			if (empty($old)) $this->logEvent('USER_PROFILE_PHONE_ADDED', $id, 0, $new);
			else if (empty($new)) $this->logEvent('USER_PROFILE_PHONE_REMOVED', $id, 0, $old);
			else $this->logEvent('USER_PROFILE_PHONE_UPDATED', $id, 0, $old, $new);
		}

		$old = $this->validDate($this->members[$id]->bday) ? strftime('%Y-%m-%d',$this->members[$id]->bday) : '0000-00-00';
		$new = $_POST['birthday'];
		if ($old != $new) $updates['fødselsdato'] = true;

		$old = $this->members[$id]->email;
		$new = $_POST['email'];
		if ($old != $new) {
			$this->logEvent('USER_PROFILE_EMAIL_UPDATED', $id, 0, $old, $new);
			$updates['epostadresse'] = true;
		}
		if (isset($_POST['slug'])) {
			$old = $this->members[$id]->slug;
			$new = $_POST['slug'];
			if ($old != $new) $updates['profil-URL'] = true;
		}

		if (isset($_POST['nickname'])) {
			$old = $this->members[$id]->nickname;
			$new = $_POST['nickname'];
			if ($old != $new) {
				$updates['kallenavn'] = true;
			}
		}
		if (isset($_POST['homepage'])) {
			$old = $this->members[$id]->homepage;
			$new = $_POST['homepage'];
			if ($old != $new) {
				$updates['nettside'] = true;
				if (empty($old)) $this->logEvent('USER_PROFILE_WWW_ADDED', $id, 0, $new);
				else if (empty($new)) $this->logEvent('USER_PROFILE_WWW_REMOVED', $id, 0, $old);
				else $this->logEvent('USER_PROFILE_WWW_UPDATED', $id, 0, $old, $new);
			}
		}
		if (isset($_POST['notes'])) {
			$res = $this->query("SELECT body FROM $this->table_memberlistlocal WHERE id='$id' AND lang='$this->preferred_lang'");
			if ($res->num_rows == 1) {
				$row = $res->fetch_row(); 
				$old = stripslashes($row[0]);
			} else {
				$old = "";
			}
			$old = trim(strip_tags($old));
			$old = str_replace(array("\r","\n"," "),array("","",""),$old); 
			$new = trim(strip_tags($_POST['notes']));
			$new = str_replace(array("\r","\n"," "),array("","",""),$new); 
			if ($old != $new) {
				$updates['beskrivelse'] = true;
			}
		}
		
		$query = array();
		
		// Lastet opp nytt profilbilde?	
		$profileimage = '';
		if (isset($_POST['profile_image']) && !empty($_POST['profile_image'])) {
			$profileimage = urldecode(strip_tags($_POST['profile_image']));
			if (ROOT_DIR != '') $profileimage = substr($profileimage,strlen(ROOT_DIR));
			$dir = '/'.$this->userFilesDir.$this->memberImagesDir.$id.'/';
			$profileimage = substr($profileimage,strlen($dir));
			$fullpath = $this->path_to_www.$dir.$profileimage;
			$thumbpath = $dir.'_thumbs140/'.$profileimage;
			if (!file_exists($fullpath)) {
				$this->fatalError("Bildet $fullpath finnes ikke!");
			}
			$updates['profilbilde'] = true;	
			$query[] = "action=cropProfileImage";
			$this->logEvent('USER_PROFILE_PHOTO_UPDATED', $id, 0, $thumbpath);
		}
		
		// Lastet opp nytt forumbilde?	
		$forumimage = '';
		if (isset($_POST['forum_image']) && !empty($_POST['forum_image'])) {
			$forumimage = urldecode(strip_tags($_POST['forum_image']));
			if (ROOT_DIR != '') $forumimage = substr($forumimage,strlen(ROOT_DIR));
			$dir = '/'.$this->userFilesDir.$this->memberImagesDir.$id.'/';
			$forumimage = substr($forumimage,strlen($dir));
			$fullpath = $this->path_to_www.$dir.$forumimage;
			$thumbpath = $dir.'_thumbs140/'.$forumimage;
			if (!file_exists($fullpath)) {
				$this->fatalError("Bildet $fullpath finnes ikke!");
			}

			$this->logEvent('USER_FORUM_AVATAR_UPDATED', $id, 0, $thumbpath);
			$updates['forumavatar'] = true;	
			if (empty($query)) {
				$query[] = "action=cropForumImage";
			} else {
				$query[] = "cropForumImage";			
			}
		}
		
		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$this->redirect($this->generateURL('action=editUserProfile',true),"Profilen ble ikke lagret pga. en eller flere feil. Sjekk verdien/-e i feltet/-ene markert med rødt og prøv å lagre igjen.");
		}
		
		
		// Process and escape input data
		$newData = array(
			'firstname' => strip_tags($_POST['firstname']),
			'middlename' => strip_tags($_POST['middlename']),
			'lastname' => strip_tags($_POST['lastname']),
			'email' => strip_tags($_POST['email']),
			'street' => strip_tags($_POST['street']),
			'streetno' => strip_tags($_POST['streetno']),
			'postno' => strip_tags($_POST['postno']),
			'city' => strip_tags($_POST['city']),
			'state' => strip_tags($_POST['state']),
			'country' => strtolower(strip_tags($_POST['country'])),
			'homephone' => strip_tags($_POST['homephone']),
			'cellular' => strip_tags($_POST['cellular']),
			'bday' => strip_tags($_POST['birthday'])
		);
		if (isset($_POST['nickname'])) $newData['nickname'] = strip_tags($_POST['nickname']);
		if (isset($_POST['homepage'])) $newData['homepage'] = strip_tags($_POST['homepage']);
		if (isset($_POST['slug'])) $newData['slug'] = strip_tags($_POST['slug']);
		if (!empty($profileimage)) $newData['profilbilde'] = $profileimage;
		if (!empty($forumimage)) $newData['forumbilde'] = $forumimage;
		$this->updateProfile($id,$newData);
				
		if (isset($_POST['notes'])) $this->updateProfileNotes($id,$_POST['notes']);				
		
		if (!empty($updates)) {
			$keys = array_keys($updates);
			$lastupdt = array_pop($keys);
			$update_keys = (count($keys) > 0) ? implode(", ",$keys)." og ".$lastupdt : $lastupdt;
			if ($this->login_identifier == $id) {
				$this->addToActivityLog("oppdaterte $update_keys i <a href=\"".$this->generateURL('')."\">medlemsprofilen sin</a>",true);
			} else {
				$this->addToActivityLog("oppdaterte $update_keys i <a href=\"".$this->generateURL('')."\">".$this->members[$id]->fullname."s medlemsprofil</a>",true);
			}
		}

		 
		if ($this->allow_editpassword) {
				 
			if (!empty($_POST['nyttpassord1']) && !empty($_POST['nyttpassord2'])) {
				/*
				if (strlen($_POST['nyttpassord1']) < 4){
					header('Location: index.php?s=0030&meldingstype=error&melding='.urlencode('Passordet m&aring; best&aring; av mellom 4 og 15 tegn! Pr&oslash;v igjen.')."\n\n"); 
					exit;
				}
				if (strlen($_POST['nyttpassord1']) > 15){
					header('Location: index.php?s=0030&meldingstype=error&melding='.urlencode('Passordet m&aring; best&aring; av mellom 4 og 15 tegn! Pr&oslash;v igjen.')."\n\n"); 
					exit;
				}
				if ($_POST['nyttpassord1'] <> $_POST['nyttpassord2']){
					header('Location: index.php?s=0030&meldingstype=error&melding='.urlencode('Du har ikke gjentatt det nye passordet korrekt!<br>&nbsp;&nbsp;Pr&oslash;v igjen.')."\n\n"); 
					exit;
				}
				*/
				// Krypter passord
				$np1 = $_POST['nyttpassord1'];
				$np2 = $_POST['nyttpassord2'];
				if ($np1 == $np2) {

					$errors = $login->setPassword($id,$np1);
					if (count($errors) > 0) {

						$_SESSION['errors'] = array_unique($errors);
						$_SESSION['postdata'] = $_POST;
						$this->redirect($this->generateURL('action=editUserProfile',true),"Passordet ble ikke endret på grunn av følgende:");

					}
					
				}
			}
		}

		$res = $this->query("SELECT slug FROM $this->table_memberlist WHERE ident='$id'");
		$row = $res->fetch_assoc();
		$slug = $row['slug'];
		if (empty($slug)) $slug = "medlemmer/$id";
		
		if (empty($query)) {
			$this->redirect($this->generateCoolURL("/$slug"), "Profilen er lagret!");		
		} else {
			$this->redirect($this->generateCoolURL("/$slug",$query));	
		}
		
	}
	

	function cropProfileImage() {
	
		if (!$this->allow_editprofile) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		
		$this->checkUserImageDir($user_id);
		$this->initializeImagesInstance();

		$q = array('action=cropProfileImageDo');
		if (isset($_GET['cropForumImage'])) $q[] = "cropForumImage";

        $img = $this->getProfileImage($user_id,'original');
        $img_id = $this->imginstance->getImageId($img);

        if ($img_id == 0) {
            return '
                <h3>Vi beklager</h3>
                <p>
                    Det ser ut som du har forsøkt å laste opp et bilde, men at noe gikk 
                    galt underveis. Forsøk gjerne på nytt, og ta kontakt med webmaster
                    dersom problemet gjentar seg.
                    </p>'.
                    $this->notSoFatalError("Bildet ".$img." ble ikke funnet i databasen.");
        }

		// Aspect ratio: 105/140 = .75
		return '
			<h3>Beskjær opplastet profilbilde</h3>
			<form method="post" action="'.$this->generateURL($q).'">
			'.$this->imginstance->outputCropForm($img_id, .75).'
			</form>
		';	
	}

	function cropProfileImageDo() {
	
		if (!$this->allow_editprofile) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		
		if (!isset($_POST['crop_x']) || !isset($_POST['crop_x'])) $this->fatalError('invalid input .1');
		if (!isset($_POST['crop_y']) || !isset($_POST['crop_y'])) $this->fatalError('invalid input .2');
		if (!isset($_POST['crop_width']) || !isset($_POST['crop_width'])) $this->fatalError('invalid input .3');
		if (!isset($_POST['crop_height']) || !isset($_POST['crop_height'])) $this->fatalError('invalid input .4');
		
		$this->initializeImagesInstance();
		$img_id = $this->imginstance->getImageId($this->getProfileImage($user_id,'original'));
		$relpath = explode('/',$this->imginstance->getRelativePathToImage($img_id));
		$fileName = array_pop($relpath);
		array_shift($relpath); array_shift($relpath);
		$dir = implode('/',$relpath);
		
		$original_path = $this->getPathToImage($user_id,$dir,$fileName);
		$medium_path = $this->getPathToImage($user_id,$dir,$fileName,'medium');
		$small_path = $this->getPathToImage($user_id,$dir,$fileName,'small');

		$this->imginstance->cropImage($original_path, $_POST['crop_x'], $_POST['crop_y'], $_POST['crop_width'], $_POST['crop_height']);
		//$this->imginstance->resizeImage($original_path,$this->profile_image_width,$this->profile_image_height);
		$this->imginstance->updateDatabaseInfo($img_id);
		
		ThumbnailService::createThumb($original_path, $medium_path, 490, 490);
		ThumbnailService::createThumb($original_path, $small_path, 140, 140);

		//$this->imginstance->createThumbnail($img_id,true,100,100,"_thumb100");
		//$this->imginstance->createThumbnail($img_id,true,500,-1,"_thumb490");
		
		if (isset($_GET['cropForumImage'])) {
			$this->redirect($this->generateURL('action=cropForumImage',true));
		} else {
			$this->redirect($this->generateURL('',true),"Profilen er lagret. Det kan være du må laste siden på nytt for å se det nye bildet du har lastet opp!");
		}
		
	}

	function cropForumImage() {
	
		if (!$this->allow_editprofile) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];
	
		$this->checkUserImageDir($user_id);
		$this->initializeImagesInstance();

		$img_id = $this->imginstance->getImageId($this->getForumImage($user_id,'original'));

		// Aspect ratio: 100/100 = 1
		return '
			<h3>Beskjær opplastet forumbilde</h3>
			<form method="post" action="'.$this->generateURL('action=cropForumImageDo').'">
			'.$this->imginstance->outputCropForm($img_id, 1).'
			</form>
		';			
	}
	
	function cropForumImageDo() {
	
		if (!$this->allow_editprofile) return $this->permissionDenied();
		$user_id = $this->current_medlem;
				
		if (!isset($_POST['crop_x']) || !isset($_POST['crop_x'])) $this->fatalError('invalid input .1');
		if (!isset($_POST['crop_y']) || !isset($_POST['crop_y'])) $this->fatalError('invalid input .2');
		if (!isset($_POST['crop_width']) || !isset($_POST['crop_width'])) $this->fatalError('invalid input .3');
		if (!isset($_POST['crop_height']) || !isset($_POST['crop_height'])) $this->fatalError('invalid input .4');
		
		$this->initializeImagesInstance();
		$img_id = $this->imginstance->getImageId($this->getForumImage($user_id,'original'));
		$relpath = explode('/',$this->imginstance->getRelativePathToImage($img_id));
		$fileName = array_pop($relpath);
		array_shift($relpath); array_shift($relpath);
		$dir = implode('/',$relpath);

		$original_path = $this->getPathToImage($user_id,$dir,$fileName);
		$medium_path = $this->getPathToImage($user_id,$dir,$fileName,'medium');
		$small_path = $this->getPathToImage($user_id,$dir,$fileName,'small');

		$this->imginstance->cropImage($original_path, $_POST['crop_x'], $_POST['crop_y'], $_POST['crop_width'], $_POST['crop_height']);
		//$this->imginstance->resizeImage($img_filename,$this->forum_image_width,$this->forum_image_height);
		$this->imginstance->updateDatabaseInfo($img_id);

		ThumbnailService::createThumb($original_path, $medium_path, 490, 490);
		ThumbnailService::createThumb($original_path, $small_path, 140, 140);

		//$this->imginstance->createThumbnail($img_id,true,100,100,"_thumb100");
		//$this->imginstance->createThumbnail($img_id,true,500,-1,"_thumb490");
		
		$this->redirect($this->generateURL('',true),"Profilen er lagret. Det kan være du må laste siden på nytt for å se det nye bildet du har lastet opp!");
		
	}
	
	/* 
	//Flytter gruppen $gruppe en posisjon opp
	function moveGroupUp($gruppe){

		if (!$this->allow_addgroup) return $this->permissionDenied();

		if (!$this->isGroup($gruppe)){ $this->fatalError("Gruppen eksisterer ikke!"); }
		$pos = $this->groups[$gruppe]->position;
		if ($pos == 1){
			$this->fatalError("Du kan ikke flytte den øverste gruppen lenger opp!");
		}
		$pos1 = $pos-1;
		$res = $this->query("SELECT id FROM $this->table_groups WHERE position='$pos1'");
		$row = $res->fetch_array();
		$pid = $row[0];

		$this->query("UPDATE $this->table_groups SET position='$pos' WHERE id='$pid'");
		$this->query("UPDATE $this->table_groups SET position='$pos1' WHERE id='$gruppe'");

		$this->addToActivityLog("Flyttet gruppen ".$this->groups[$gruppe]->caption." et hakk opp.");
		
		return "";
	}

	// Flytter gruppen $gruppe en posisjon opp 
	function moveGroupDown($gruppe){

		if (!$this->allow_addgroup) return $this->permissionDenied();

		if (!$this->isGroup($gruppe)){ ErrorMessageAndExit("Gruppen eksisterer ikke!"); }
		$pos = $this->groups[$gruppe]->position;
		$res = $this->query("SELECT MAX(position) FROM $this->table_groups");
		$row = $res->fetch_array();
		$max_position = $row[0];
		if ($pos == $max_position){
			$this->fatalError("Du kan ikke flytte den nederste gruppen lenger ned!");
		}
		$pos1 = $pos+1;
		$res = $this->query("SELECT id FROM $this->table_groups WHERE position='$pos1'");
		$row = $res->fetch_array();
		$pid = $row[0];

		$this->query("UPDATE $this->table_groups SET position='$pos' WHERE id='$pid'");
		$this->query("UPDATE $this->table_groups SET position='$pos1' WHERE id='$gruppe'");

		$this->addToActivityLog("Flyttet gruppen ".$this->groups[$gruppe]->caption." et hakk ned.");

		return "";
	}*/


	/*
	function deleteGroup($id){
		global $db;

		if (!$this->allow_deletegroup){
			print $this->permissionDenied();
			return 0;
		}

		$db->query("DELETE FROM $this->table_groups WHERE id='$id'");
		$this->addToActivityLog("slettet gruppen ".$this->groups[$id]->caption);
	}*/

	function deleteUser(){
		
		if (!$this->allow_deletemember) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!($this->myndighet_i($m->memberof))){ $this->fatalError("Manglende rettigheter til å slette brukeren!"); }
		$r1a   = array();			$r2a   = array();
		$r1a[] = "%id%";			$r2a[] = $user_id;
		$r1a[] = "%name%";			$r2a[] = $user_obj->fullname;
		$r1a[] = "%referer%";		$r2a[] = $_SERVER['HTTP_REFERER'];
		$r1a[] = "%posturl%";		$r2a[] = $this->generateURL('action=deleteUserDo');
		return str_replace($r1a, $r2a, $this->deleteuser_template);
	}

	function deleteUserDo(){

		if (!$this->allow_deletemember) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!($this->myndighet_i($m->memberof))){ $this->fatalError("Manglende rettigheter til å slette brukeren!"); }

		$this->query("DELETE FROM $this->table_guardians WHERE medlem=$user_id");
		$this->query("DELETE FROM $this->table_guardians WHERE foresatt=$user_id");
		$this->addToActivityLog("slettet foresattrelasjoner for ".$user_obj->fullname);

		$this->query("DELETE FROM $this->table_group_memberships WHERE bruker=$user_id");
		$this->addToActivityLog("slettet medlemskap for ".$user_obj->fullname);

		$this->query("DELETE FROM $this->table_memberlist WHERE ident=$user_id");
		$this->addToActivityLog("slettet medlemmet ".$user_obj->fullname);
		
		$this->redirect($this->generateCoolURL("/"));

	}
	

	function resendWelcomeMail(){
		global $login;
		
		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if ($this->isLoggedIn()) {
			if (!$this->allow_addmember) return $this->permissionDenied();
		}

		if ($login->hasPassword($user_id)) {
			return $this->notSoFatalError("Brukeren har allerede opprettet passord");
		}

		$r1a = array(); $r2a = array();
		$r1a[] = "%userid%";		$r2a[] = $user_id;
		$r1a[] = "%name%";			$r2a[] = $user_obj->fullname;
		$r1a[] = "%referer%";		$r2a[] = $_SERVER['HTTP_REFERER'];
		$r1a[] = "%posturl%";		$r2a[] = $this->generateURL('action=resendWelcomeMailDo');
		if ($this->isLoggedIn()) {
			$r1a[] = "%mail_preview%";	$r2a[] = $this->sendWelcomeMail($user_id,true,true);
		} else {
			$r1a[] = "%mail_preview%";	$r2a[] = "Ved å trykke \"Send e-post\" sendes en e-post til din e-postadresse\nmed instruksjoner for å opprette ditt eget passord.\nDette er nødvendig for å bekrefte din identitet.";		
		}
		return str_replace($r1a, $r2a, $this->resendwelcomemail_template);
	}
	
	function resendWelcomeMailDo(){
		global $login;

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];
		
		if ($login->hasPassword($user_id)) {
			if ($this->isLoggedIn()) {
				$this->redirect($this->generateURL('action=userOverview'),'Brukeren har allerede opprettet passord.');
			} else {
				$this->redirect('/','Du har allerede opprettet passord.');
			}		
		}
	
		if ($this->isLoggedIn()) {
			if (!$this->allow_addmember) return $this->permissionDenied();
		}
	
		$email = $this->sendWelcomeMail($user_id,true);

		if ($this->isLoggedIn()) {
			$this->redirect($this->generateURL('action=userOverview',true),"Registrerings-epost er sendt til brukeren.");
		} else {
			$this->redirect('/',"En e-post er nå sendt til ".$email." med videre instruksjoner.");		
		}
				

	}
	
	function sendLoginDetails(){
	
		if (!$this->allow_editprofile) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];
		
		return '
			<h3>'.$user_obj->fullname.': Send innloggingsdetaljer</h3>
			<form method="post action="'.$this->generateURL('action=sendLoginDetailsDo').'">
				Vil du sende medlemmets innlogginsdetaljer til '.$user_obj->email.'?
				<br /><br />
			
				<input type="button" value="Avbryt" onclick="window.location=\''.$this->generateURL('action=userOverview',true).'\'" /> 
				<input type="submit" value="Send" />
			</form>
		';
		
	}
	
	function sendLoginDetailsDo(){
		global $login;

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!$this->allow_editprofile) return $this->permissionDenied();
		if (!$login->sendLoginDetails($user_id)) {
			$this->redirect($url_back,'Medlemmet har ikke opprettet passord!','error');
		}
		$this->addToActivityLog("sendte innloggingsdetaljer til ".$this->makeMemberLink($user_id));
		$this->redirect($this->generateURL('action=userOverview',true),"Innlogginsdetaljer ble sendt til medlemmet!");		
	}
	
	function editMembershipType() {
		if (!$this->allow_editmembershiptype) return $this->permissionDenied();
		
		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		$inputs = "";
		$res = $this->query("SELECT sid, description FROM $this->table_membershiptypes");
		while ($row = $res->fetch_assoc()) {
			$ms = $row['sid'];
			$description = $row['description'];
			if (!empty($ms)) {
				$d = ($ms == $user_obj->memberstatus) ? " checked='checked'" : "";
				$inputs .= " 
					<input type='radio' name='medlemsstatus' id='medlemsstatus_$ms' value='$ms'$d />
					<label for='medlemsstatus_$ms'>$description</label><br />
				";
			}
		}
		
		return '
			<h3>'.$user_obj->fullname.': Medlemskapsstatus</h3>
			<form method="post" action="'.$this->generateURL('action=saveMembershipType').'">
				<p>
					'.$inputs.'
				</p>
				<p>
					<input type="button" value="Avbryt" onclick="window.location=\''.$this->generateURL('action=userOverview',true).'\'" /> 
					<input type="submit" value="Lagre" />
				</p>
			</form>
		';
	}
	
	function saveMembershipType(){

		if (!$this->allow_editmembershiptype) return $this->permissionDenied();

		if (!isset($_POST['medlemsstatus'])) $this->fatalError("invalid input!");
		$ms = $_POST['medlemsstatus'];
		
		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		$oldms = $user_obj->memberstatus;
		if ($oldms != $ms){
			$this->query("UPDATE $this->table_memberlist SET memberstatus=\"$ms\" WHERE ident=".$user_id." LIMIT 1");
			$this->addToActivityLog("endret medlemskapsstatus for ".$user_obj->fullname." fra $oldms til $ms.");
		}
		$this->redirect($this->generateURL('action=userOverview',true));
	}

	function editUserRights(){

		if (!$this->allow_editrights) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		$inputs = "";					
		$res = $this->query("SELECT level, shortdesc, longdesc FROM $this->table_rights ORDER BY level");
		while ($row = $res->fetch_assoc()) {
			$level = $row['level'];
			$shortdesc = stripslashes($row['shortdesc']);
			$d =  ($level == $user_obj->rights) ? " checked='checked'" : "";
			$inputs .= " 
				<input type='radio' name='rettigheter' id='rettigheter$level' value='$level'$d />
				<label for='rettigheter$level'>$level ($shortdesc)</label><br />
			";
		}
		
		return '
			<h3>'.$user_obj->fullname.': Tilgangsnivå</h3>
			<form method="post" action="'.$this->generateURL('action=saveUserRights').'">
				<p>
					'.$inputs.'
				</p>
				<p>
					<input type="button" value="Avbryt" onclick="window.location=\''.$this->generateURL('action=userOverview',true).'\'" /> 
					<input type="submit" value="Lagre" />
				</p>
			</form>
		';
	}

	function saveUserRights(){

		if (!$this->allow_editrights) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!isset($_POST['rettigheter'])) $this->fatalError("invalid input!");
		$rights = $_POST['rettigheter'];
		if (!is_numeric($rights)){ $this->fatalError("invalid input .2"); }
		$oldrights = $user_obj->rights;
		if ($oldrights != $rights){
			$this->query("UPDATE $this->table_memberlist SET rights='$rights' WHERE ident='$user_id' LIMIT 1");
			$this->addToActivityLog("endret tilgangsnivå for ".$user_obj->fullname." fra $oldrights til $rights.");
		}
		$this->redirect($this->generateURL('action=userOverview',true));
	}

	function fixRights(){

		if (!$this->allow_editrights) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!($this->myndighet_i($user_obj->memberof))){ 
			$this->fatalError("Manglende rettigheter til å arbeide med denne brukeren"); 
		}
		$r1a = array(); $r2a = array();
		$r1a[] = "%userid%";		$r2a[] = $user_id;
		$r1a[] = "%name%";			$r2a[] = $user_obj->fullname;
		$r1a[] = "%referer%";		$r2a[] = $_SERVER['HTTP_REFERER'];
		$r1a[] = "%posturl%";		$r2a[] = $this->generateURL('action=fixRightsDo');
		return str_replace($r1a, $r2a, $this->fixrights_template);

	}

	function fixRightsDo(){

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!$this->allow_editrights){
			$this->permissionDenied();
			return 0;
		}
		
		if (!($this->myndighet_i($user_obj->memberof))){ 
			$this->fatalError("Manglende rettigheter til å arbeide med denne brukeren"); 
		}
	
		$this->resetRights($user_id);
		
		// Redirect
		$this->redirect($this->generateURL('action=userOverview',true));

	}

	function editUserTitle(){

		if (!$this->allow_edittitles){
			$this->permissionDenied();
			return 0;
		}

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		$kat = array();
		foreach ($user_obj->memberof as $g){
			if (!in_array($this->groups[$g]->kategori,$kat)) $kat[] = $this->groups[$g]->kategori;
		}
		$whereQ = "kategori='".$kat[0]."'";
		for ($i = 1; $i < count($kat); $i++) 
			$whereQ .= " OR kategori='".$kat[$i]."'";
		
		$res = $this->query("
			SELECT 
				id, 
				tittel, 
				classname,
				kategori
			FROM 
				$this->table_rang 
			WHERE 
				($whereQ)
			ORDER BY
				position
			"
		);
		$selectList = "";
		while ($row = $res->fetch_assoc()){
			$id = $row['id'];
			$caption = stripslashes($row['tittel']);
			$selectList .= "<div><input type='radio' name='titulering' id='titulering$id' value='$id' ".(($row['tittel']==$user_obj->tittel)?"checked='checked'":"")." />
				<label for='titulering$id'>$caption</label></div>";

		}
		$selectList .= "<div><input type='radio' name='titulering' id='nytittel' value='nytittel' />
				<input type='text' name='nytittelvalue' size='30'/></div>";
		
		return '
			<h3>'.$user_obj->fullname.': Rolle</h3>
			<form method="post" action="'.$this->generateURL('action=saveUserTitle').'">
				'.$selectList.'
				<br />
				<input type="button" value="Avbryt" onclick="window.location=\''.$this->generateURL('action=userOverview').'\'" /> 
				<input type="submit" value="Lagre" />
			</form>
		';
	}

	function saveUserTitle(){

		if (!$this->allow_edittitles) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!isset($_POST['titulering'])) $this->fatalError("invalid input!");
		$rang = addslashes($_POST['titulering']);
		
		if ($rang == 'nytittel'){
			$nytittel = addslashes($_POST['nytittelvalue']);
			
			$kat = array();
			foreach ($user_obj->memberof as $g){
				if (!in_array($this->groups[$g]->kategori,$kat)) $kat[] = $this->groups[$g]->kategori;
			}
			$firstkat = $kat[0];
			
			$res = $this->query("
				SELECT MAX(position) FROM $this->table_rang WHERE kategori='$firstkat'"
			);
			$row = $res->fetch_row();
			$nypos = $row[0] + 1;
			$this->query("
				UPDATE $this->table_rang SET position=position+1 WHERE position>=$nypos"
			);
			$this->query("
				INSERT INTO $this->table_rang
					(classname,tittel,kategori,position)
				VALUES 
					('".$this->default_rang_classname."','$nytittel','$firstkat',$nypos)"
			);
			$rang = $this->insert_id();
		}
		if (!is_numeric($rang)){ $this->fatalError("invalid input .2"); }

		$this->setRang($member, $rang);
		$this->redirect($this->generateURL('action=userOverview',true),"Rolle for medlemmet ble lagret");
	}

	function userOverview(){
		global $login;

		if (!$this->allow_viewmemberdetails) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!$this->myndighet_i($user_obj->memberof)) return $this->permissionDenied(); 

		$username = $login->getUsername($user_id);
		$hasUser = ($username != false);
		$hasPass = $login->hasPassword($user_id);
		$loginTime = $login->getLoginTime($user_id);
		
		if ($hasUser) {
			$info_username = $login->getUsername($user_id);
		} else {
			$info_username = "<em>ikke tildelt</em>";
		}

		$info_memberof = "";
		if (count($user_obj->memberof) == 0){ 
			$info_memberof = "<i>Ingen grupper</i>";
		} else {
			foreach ($user_obj->memberof as $g){
				$info_memberof .= '<img src="'.$this->image_dir.'group5.gif" border="0" /> '.call_user_func($this->make_grouplink,$g).'<br />';
			}
		}
		
		$groupListSelect = "<select name='nygruppe' class='ProfileEdit'>";
		foreach ($this->groups as $g){
			$groupListSelect .= "<option value='".$g->id."'>".$g->caption."</option>";
		}
		$groupListSelect .= "</select>\n";
		
		$info_pwd = $hasPass ? "Ja" : "Nei";
		$info_profile = (($user_obj->profilecreated == 1) ? "Opprettet" : "Ikke opprettet");
		$info_tittel = $user_obj->tittel;
	
		$vervobj = $this->vervredigeringInstance();
		$verv = $vervobj->vervForMember($user_id);
		$info_verv = ((count($verv) > 0) ? implode("<br />",$verv) : "<i>Ingen verv</i>");
		unset($vervobj);

		$info_foresatte = "";
		$foresatte = $this->foresatteTil($user_id);
		foreach ($foresatte as $f){
			$info_foresatte .= '<div style="background:url('.$this->image_dir.'person.gif) left no-repeat;padding-left:14px;"><a href="'.$this->generateCoolURL("/medlemmer/$f").'">'.$this->members[$f]->fullname.'</a></div>';	
		}
		if (empty($info_foresatte)) $info_foresatte = "<i>Ingen</i>";

		$info_foresattil = "";
		$barn = $this->foresattFor($user_id);
		foreach ($barn as $f){
			$info_foresattil .= '<div style="background:url('.$this->image_dir.'person.gif) left no-repeat;padding-left:14px;"><a href="'.$this->generateCoolURL("/medlemmer/$f").'">'.$this->members[$f]->fullname.'</a></div>';	
		}
		if (empty($info_foresattil)) $info_foresattil = "<i>Ingen</i>";
		
		if (!$hasUser) {
			$info_membershiptype = "<em>ikke bruker</em>";
		} else {
			$res2 = $this->query("SELECT description FROM $this->table_membershiptypes WHERE sid='$user_obj->memberstatus'");
			if ($res2->num_rows == 0) $info_membershiptype = "<em>Ukjent</em>";
			else {
				$row2 = $res2->fetch_assoc();
				$info_membershiptype = $row2['description'];
			}
		}
				
		if (count($user_obj->memberof) > 0) {
			$kat = $this->groups[$user_obj->memberof[0]]->kategori;
			if ($kat == 'SM' || $kat == 'SP') {
			
			} else {
				$this->allow_editforesatte = false;
			}
		} else {
			$this->allow_editforesatte = false;		
		}
		
		if ($this->login_identifier != $user_id){
			$selectBox = $this->generateGroupSelectBox("nygruppe",!$this->allow_moveuserstononpatruljer,$user_obj->memberof);
		
			$memberof_admin = '
				<form method="post" action="'.$this->generateURL('action=moveUser').'">
					'.$selectBox.' <input type="submit" value="Flytt" />
				</form>

				<form method="post" action="'.$this->generateURL('action=stopAllMemberships').'">
					<input type="submit" value="Avslutt medlemskap" />
				</form>
			';
		} else {
			$memberof_admin = "";
		}
	    
		$output = "<h3>Brukerinnstillinger for ".$user_obj->fullname."</h3>";
        call_user_func(
            $this->add_to_breadcrumb,
            '<a href="'.$this->generateURL('edituser').'">Brukerinnstillinger</a>'
        );

		if ($this->allow_editprofile) {
	
			$success = true;
				
			if (count($user_obj->memberof) == 0) {
				$success = false;
				if ($hasUser) {
					$output .= $this->infoMessage('Denne personen har brukernavnet <strong>'.$username.'</strong>, 
						men dette kan ikke lenger benyttes til å logge inn, fordi personen ikke er 
						medlem av noen grupper. Du bør enten melde personen inn igjen eller frigjøre 
						brukernavnet så andre kan benytte det.<br /><br /> 
						<a href="'.$this->generateURL('action=releaseUsername').'">Frigjør brukernavn</a>', 2);
				} else {
					$output .= $this->infoMessage('Denne personen har ikke lenger noen tilknytning til gruppen.
						Dersom personen igjen tilknyttes gruppen kan du melde personen inn i en gruppe
						i <a href="'.$this->generateURL('action=editUserProfile').'">personens medlemsprofil</a>. Deretter kan 
						personen tildeles brukernavn igjen.', 2);				
				}
			} else {
				if (!$hasUser) {
					$success = false;
					$output .= $this->infoMessage('Denne personen har tilknytning til gruppen, men har ikke
						fått tildelt et brukernavn til å logge inn på gruppens nettsider med.<br /><br /> 
						<a href="'.$this->generateURL('action=assignUsername').'">Tildel brukernavn</a>', 2);
				}
			}
			if ($hasUser && !$hasPass) {
				$success = false;
				$output .= $this->infoMessage('Denne personen har fått tildelt et brukernavn, men har ikke
					laget sitt eget passord. Du bør sjekke at e-postadressen 
					<strong>'.$user_obj->email.'</strong> er i bruk, og deretter sende registreringsmailen
					med instruksjoner om hvordan man lager passord på nytt:<br /><br /> 
					<a href="'.$this->generateURL('action=resendWelcomeMail').'">Send registreringsmail på nytt</a>', 2);
			}
			if (empty($user_obj->firstname) || empty($user_obj->lastname) || empty($user_obj->street) || empty($user_obj->streetno) || empty($user_obj->postno) || empty($user_obj->city) || (empty($user_obj->homephone) && empty($user_obj->cellular)) ) {
				$output .= $this->infoMessage('Kontaktopplysningene til denne personen er mangelfulle. 
					Du kan oppdatere de i personens medlemsprofil:<br /><br /> 
					<a href="'.$this->generateURL('action=editUserProfile').'">Rediger medlemsprofil</a>', 2);
			}
			if ($this->allow_editforesatte && !count($foresatte)) {
				$output .= $this->infoMessage('Denne personen er speider eller småspeider, men
					har ingen registrerte foresatte. Alle speidere og småspeidere
					bør være registrert med minst én foresatt.<br /><br /> 
					<a href="'.$this->generateURL('action=viewGuardians').'">Legg til en foresatt</a>', 2);
			}
			if ($hasUser && $hasPass) {
				if (empty($loginTime)) {
					$success = false;
					$output .= $this->infoMessage('Denne personen har <strong>aldri</strong> logget 
						inn på nettsiden. Du bør kanskje sjekke om personen har glemt sine 
						innloggingsopplysninger.<br /><br />
						<a href="'.$this->generateURL('action=sendLoginDetails').'">Send innloggingsopplysninger</a>', 2);				
				} else if ((time()-$loginTime) > 86400*30*6) {
					$months = round((time()-$loginTime)/(86400*30.5));
					$output .= $this->infoMessage('Denne personen har ikke logget inn på nettsiden på
						over <strong>'.$months.' måneder</strong>. Du bør kanskje sjekke om personen
						har glemt sine innloggingsopplysninger.<br /><br />
						<a href="'.$this->generateURL('action=sendLoginDetails').'">Send innloggingsopplysninger</a>', 1);
				}
			}
			if ($success) {
				$output .= $this->infoMessage('Denne personen har fått tildelt brukernavn, aktivert det og 
					logget inn på nettsiden.', 4);
            }
			if ($this->login_identifier == $user_id){
                $output .= $this->infoMessage('
                    Dette er dine egne brukerinnstillinger. Det er noen begrensninger i hvilke 
                    innstillinger du kan endre for deg selv, siden brukerinnstillinger først og
                    fremst er et verktøy for å administrere andre brukere.
                ',3);
            }
		}
		/*	(($this->allow_editprofile && (count($m->memberof) == 0) && !empty($m->username)) ? "<a href='$url_releaseusername'>Frigjør brukernavn</a>" : "").
			(($this->allow_addmember && empty($m->username)) ? "<a href='$url_assignusername'>Tildel brukernavn</a>" : "").
			(($this->allow_addmember && !empty($m->password) && $m->password != "NOT_SET") ? "<a href='$url_sendlogindetails'>Send innloggingsopplysninger</a>" : "&nbsp;").
*/

		$output .= '
		
		<fieldset><legend><strong>Innlogging</strong></legend>
			
			'.($hasUser?'<div style="background:url(/images/icns/accept.png) left no-repeat;padding-left:20px;margin:2px;">Har brukernavn</div>'
				:'<div style="background:url(/images/icns/error.png) left no-repeat;padding-left:20px;margin:2px;">Har ikke brukernavn</div>').'
			'.($hasPass?'<div style="background:url(/images/icns/accept.png) left no-repeat;padding-left:20px;margin:2px;">Har laget passord</div>'
				:'<div style="background:url(/images/icns/error.png) left no-repeat;padding-left:20px;margin:2px;">Har ikke laget passord</div>').'
			<!--<input type="checkbox">Tillatt innlogging-->
			
		</fieldset>
		
		
		<fieldset><legend><strong>Medlemskap</strong></legend>
			Personen er medlem av: 
			<div style="padding:10px;">'.$info_memberof.'</div>';
		if ($this->allow_editmemberships) {
			if ($this->login_identifier != $user_id){
				$output .= '
					Flytt personen til en ny gruppe:<div style="font-size:80%;padding:10px;">(avslutter alle nåværende gruppemedlemskap)</div>
					<form method="post" action="'.$this->generateURL('action=moveUser').'" style="padding-left:10px;padding-bottom:10px;">
						'.$selectBox.' <input type="submit" value="Flytt" />
					</form>
					Avslutt medlemskap i 18. Bergen:<div style="font-size:80%;padding:10px;">
					Dette frigjør også personens brukernavn! Hvis du ønsker at personen fortsatt skal 
					kunne logge inn, må du i stedet flytte han/hun til gruppen «Permitterte og 
					pensjonerte speidere».</div>
					<form method="post" action="'.$this->generateURL('action=stopAllMemberships').'" style="padding-left:10px; padding-bottom:10px;">
						<input type="submit" value="Avslutt alle medlemskap" />
					</form>
					Andre endringer:
					<div style="font-size:80%;padding:10px;">
					Ønsker du å gjøre andre medlemskapsendringer, f.eks. legge til et 
					nytt medlemskap i en gruppe uten å stoppe de nåværende, kan du gjøre
					dette under «Medlemskap» på <a href="'.$this->generateURL('action=editUserProfile').'">personens brukerprofil</a>.
					</div>
				';
			}
		}
		$output .= '
		</fieldset>
		<fieldset><legend><strong>Relasjoner</strong></legend>
			<table width="100%"><tr>
				<td valign="top" width="50%">
					Foresatte'.($this->allow_editforesatte ? ' (<a href="'.$this->generateURL('action=viewGuardians').'">Administrer</a>)':'').':
					<div style="padding:10px;">'.$info_foresatte.'</div>
				</td><td valign="top">
					Foresatt for:
					<div style="padding:10px;">'.$info_foresattil.'</div>
				</td>
			</tr></table>
		</fieldset>
		<fieldset><legend><strong>Annet</strong></legend>
			<table width="100%">
				<tr>
					<td align="right">Brukernavn: </td>
					<td>'.$info_username.' '.(($this->allow_addmember && $hasPass) ? '(<a href="'.$this->generateURL('action=sendLoginDetails').'">Send innloggingsopplysninger</a>)' : '').'</td>
				</tr>
				<tr>
					<td align="right">Medlemskapstype: </td>
					<td>'.(($hasUser && $this->allow_editmembershiptype) ? '<a href="'.$this->generateURL('action=editMembershipType').'" title="Klikk for å redigere">'.$info_membershiptype.'</a>' : $info_membershiptype).'</td>
					<td></td>
				</tr>
				<tr>
					<td valign="top" align="right">Rolle:</td>
					<td valign="top">'.($this->allow_edittitles ? '<a href="'.$this->generateURL('action=editUserTitle').'" title="Klikk for å redigere">'.$info_tittel.'</a>' : $info_tittel).'</td>
					<td valign="top"></td>
				</tr>
				<tr>
					<td valign="top" align="right">Tilgangsnivå: </td>
					<td valign="top">'.($this->allow_editrights ? '<a href="'.$this->generateURL('action=editUserRights').'" title="Klikk for å redigere">'.$user_obj->rights.'</a>' : $user_obj->rights).'</td>
				</tr>
			</table>
		</fieldset>
		';
		return $output;
	}
	
	function memberAdded(){

		if (!$this->allow_addmember) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		$kat = $this->groups[$user_obj->memberof[0]]->kategori;
		$pg = 0;
		foreach ($this->groups as $g) {
			if ($g->kategori == "FO") $pg = $g->id;
		}
		
		$isForesatt = $this->isForesatt($user_id);
		return '
			<h3>Personen er lagt til i medlemsregisteret</h3>
			<p>
				Har '.$m->fullname.' en e-post-adresse?<br />
				Isåfall bør du opprette en brukerkonto, så 
				han/hun kan logge inn gruppens nettsider.
			</p>
			'.($isForesatt ? '' : '
			<p>
				Hvis ikke kan du registrere en foresatt (f.eks. far eller mor) som har e-post-adresse isteden.
			</p>
			').'
			<ul>
				<li>
					<a href="'.$this->generateURL('action=assignUsername').'">
						Opprette brukerkonto for '.$user_obj->fullname.'
					</a>
				</li>
				'.($isForesatt ? '' : '
					<li>
						<a href="'.$this->generateCoolURL("/grupper/".$pg,"action=addMember&foresattil=$user_id").'">
							Registrere ny foresatt til '.$user_obj->fullname.'
						</a>
					</li>
				').'
			</ul>			
		';
	}
	
	function releaseUsername(){
		global $login;

		if (!$this->allow_editprofile) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];
		
		if (!$login->hasUsername($user_id))
			return $this->notSoFatalError("Brukernavnet er allerede frigjort!");
		
		if (count($user_obj->memberof) > 0) {
			return $this->notSoFatalError("For å frigjøre brukernavnet må du først avslutte alle medlemmets medlemskap. Medlemmet er fremdeles medlem av ".$this->listMemberships($user_id).".");
		}
		
		return '
			<h3>'.$user_obj->fullname.': Frigjør brukernavn</h3>
			<form method="post" action="'.$this->generateURL('releaseUsernameDo').'">
				Bekreft frigjøring av brukernavn.
				<br /><br />
				<input type="submit" value="Bekreft" />
			</form>

		';
	}
	
	function releaseUsernameDo($redirect = true, $silent = false) {
		global $login;
		if (!$this->allow_editprofile) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!$this->isUser($user_id)) return $this->permissionDenied();
		if (count($user_obj->memberof) > 0) return $this->permissionDenied();
		
		$username = $login->getUsername($user_id);
		$login->releaseUsername($user_id);
		if (!$silent) $this->addToActivityLog("frigjorde brukernavnet til ".$user_obj->fullname." (".$username.")");		
		if ($redirect) $this->redirect($this->generateUrl('action=userOverview'), 'Frigjorde brukernavnet');	
	}

	function assignUsername(){
		global $login;

		if (!$this->allow_addmember) return $this->permissionDenied();
		
		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];
		
		if ($login->hasUsername($user_id)) { 
			return $this->notSoFatalError("Medlemmet er allerede tilknyttet en brukerkonto!");
		}

		$email = $user_obj->email;
		$username = '';
		
		if (isset($_SESSION['errors'])){		
			$errors = $_SESSION['errors'];
			$postdata = $_SESSION['postdata'];

			$email = $postdata['email'];
			$username = $postdata['username'];
			
			$errorS = "";
			foreach ($errors as $e){
				$errorS .= "<div>".$this->errorMessages[$e]."</div>";
			}

			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);
		}

		$output = '
			<h3>'.$user_obj->fullname.': Opprett brukerkonto</h3>';
		if (isset($errorS)) $output .= $this->notSoFatalError($errorS);
		$output .= '
			<form method="post" action="'.$this->generateURL('action=saveUsername').'">
				<table>
					<tr><td>E-post: </td><td><input type="text" name="email" value="'.$email.'" size="30" /> *</td></tr>
					<tr><td>Brukernavn: </td><td><input type="text" name="username" value="'.$username.'" size="30" /> * </td></tr>
				</table>
				<br />
				Begge feltene <i>må</i> fylles inn. <br /><br />
				Brukernavnet må du finne på selv. Det kan f.eks. være medlemmets fornavn eller lignende. 
				Brukernavnet kan kun inneholde tegnene a-z og 0-9 og må bestå av mellom 3 og 10 bokstaver. <br />
				<br /><br />
				<input type="submit" value="Lagre" />
			</form>
		';
		return $output;
	}
	
	function saveUsername(){
		global $login;
		
		if (!$this->allow_addmember) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if ($login->hasUsername($user_id)) 
			return $this->notSoFatalError("Medlemmet er allerede tilknyttet en brukerkonto!");
		
		$errors = array();
		$username = "";
		if ((!isset($_POST["email"])) || empty($_POST["email"])){
			array_push($errors,"no_email_entered"); 
			$email = "";
		} else {
			$email = addslashes($_POST['email']);
			if ((!isset($_POST["username"])) || empty($_POST["username"])){
				array_push($errors,"no_username_entered"); 
			} else { 
				$username = $_POST["username"];
				$errors = $login->assignUsername($user_id, $username);
			}
		}
		if (count($errors) > 0){
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$this->redirect($this->generateURL('action=assignUsername',true));
		}

		$this->query("
			UPDATE $this->table_memberlist
			SET email=\"$email\"
			WHERE ident=$user_id"
		);

		$this->addToActivityLog("opprettet brukerkonto for <a href=\"".$this->generateURL('')."\">$user_obj->fullname</a>");
		$this->logEvent('ACCOUNT_CREATED', $user_id);
		
		$this->reloadMemberlist(); // Last inn endringer

		// Send velkomstmail
		$this->sendWelcomeMail($user_id,false);
		
		$this->redirect($this->generateURL('action=userOverview',true),'En epost har nå blitt sendt til medlemmet med instruksjoner for å logge inn på websiden.');
	}

	function viewGuardians(){

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		if (!$this->allow_viewforesatte) return $this->permissionDenied();

		//$kat = $this->groups[$m->memberof[0]]->kategori;
		$pg = 0;
		foreach ($this->groups as $g) {
			if ($g->kategori == "FO") $pg = $g->id;
		}	
		
		$foresatte = $this->foresatteTil($user_id);
		
		$foresatteList = '';
		foreach ($foresatte as $fi){
			$f = $this->members[$fi];
			$url_f = $this->generateCoolURL("/medlemmer/$fi");
			$url_fjern = $this->generateURL(array("action=removeGuardian","guardian_id=$fi"));
			$foresatteList .= '<li class="'.$f->classname.'"><a href="'.$url_f.'">'.$f->fullname.'</a>'.($this->allow_editforesatte ? ' [<a href="'.$url_fjern.'">Fjern som foresatt</a>]':'').'</li>';
		}
		
		$output = '
			<h3>'.$user_obj->fullname.': Foresatte</h3>
			<ul class="custom_icons">
				'.$foresatteList.'
			</ul>
		';
		if ($this->allow_editforesatte){
			$output .= '
				<img src="'.$this->image_dir.'medlemsliste/newuser.gif" alt="User" /> 
				<a href="'.$this->generateURL(array('action=addMember','foresattil='.$user_id)).'">Legg til nytt medlem som foresatt</a><br />
				
				<img src="'.$this->image_dir.'medlemsliste/newuser.gif" alt="User" /> 
				<a href="'.$this->generateURL("action=addGuardian").'">Legg til eksisterende medlem som foresatt</a>
			';
		}
		return $output;
	}

	function removeGuardian(){

		if (!$this->allow_editforesatte) return $this->permissionDenied();
		
		$guardian_id = intval($_GET['guardian_id']);
		$user_id = $this->current_medlem;
		
		$this->deregisterGuardian($user_id, $guardian_id);

		$this->redirect($this->generateURL('action=viewGuardians'));
	}

	function addGuardian(){

		if (!$this->allow_editforesatte) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];

		$fg = array();
		foreach ($this->groups as $g) {
			if ($g->kategori == "FO") $fg[] = $g->id;
		}
		
		$options_list =  '<option value="0">Velg person:</option>';
		foreach ($fg as $f){
			$fobj = array();
			foreach ($this->groups[$f]->members as $fi){
				$f_me = $this->members[$fi];
				$fobj[$f_me->ident] = $f_me->fullname;
			}
			asort($fobj); // Sort an array and maintain index association
			$options_list .= "<optgroup label='".$this->groups[$f]->caption."'>\n";
			foreach ($fobj as $ident => $fullname){
				$options_list .= "<option value='$ident'>$fullname</option>\n";
			}
			$options_list .= "</optgroup>\n";
		}
		return '
			<h3>'.$user_obj->fullname.': Legg til foresatt</h3>
			<form method="post" action="'.$this->generateURL('action=addGuardianDo').'">
				<select name="nyforesatt">
					'.$options_list.'
				</select>
				<br /><br />
				<input type="button" value="Avbryt" onclick=\'window.location="'.$this->generateURL('action=userOverview').'"\' /> 
				<input type="submit" value="Legg til" />
			</form>
		';
	}

	function addGuardianDo(){

		if (!$this->allow_editforesatte) return $this->permissionDenied();

		$user_id = $this->current_medlem;
		$user_obj = $this->members[$user_id];
		
		$this->registerGuardian($user_id, $guardian_id);
		$this->redirect($this->generateURL('action=viewGuardians'));
		
	}

	/* 
	############################################################################################################################

		GROUP STUFF

	############################################################################################################################
	*/

	function newMemberForm($gruppe){

		if (!$this->allow_addmember) return $this->permissionDenied();

		$g = $this->groups[$gruppe];
		$url_post = $this->generateURL(array("noprint=true","savenewmember"));
		$url_back = $this->generateURL("");
		$foresattil = -1;
		$erforesatt = false;
		if (isset($_GET['foresattil'])){
			if ($this->isUser($_GET['foresattil'])){
				$erforesatt = true;
				$foresattil = $this->members[$_GET['foresattil']];
			}
		}
		return '
			<h3>Legg til medlem i '.$g->caption.'</h3>
			<form method="post" action="'.$url_post.'">
				<table>
					'.($erforesatt ? "<tr><td>Foresatt til: </td><td>".$foresattil->fullname."<input type='hidden' name='foresattil' value='".$foresattil->ident."' /></td></tr>" : "").'
					<tr><td>Fornavn: </td><td><input type="text" name="firstname" value="" size="30" /> *</td></tr>
					<tr><td>Mellomnavn: </td><td><input type="text" name="middlename" value="" size="30" /></td></tr>
					<tr><td>Etternavn: </td><td><input type="text" name="lastname" value="" size="30" /> *</td></tr>
				</table>
				<br />
				<i>Felt merket med * må fylles inn.</i>
				<br /><br />
				<input type="button" value="Avbryt" onclick=\'window.location="'.$url_back.'";\' /> 
				<input type="submit" value="Legg til" />
			</form>
		';
	}

	function newMember($gruppe){
		
		if (!$this->allow_addmember) return $this->permissionDenied();

		if (!isset($_POST["firstname"])) $this->fatalError("Du må fylle inn fornavn!");
		$firstname = addslashes(trim(strip_tags($_POST["firstname"])));
		if (empty($firstname)) $this->fatalError("Du må fylle inn fornavn!");
		
		//if (!isset($_POST["lastname"])) $this->fatalError("Du må fylle inn etternavn!");
		$lastname = addslashes(trim(strip_tags($_POST["lastname"])));
		//if (empty($lastname)) $this->fatalError("Du må fylle inn etternavn!");
		
		if ((!isset($_POST["middlename"])) || empty($_POST["middlename"])){
			$middlename = "";
		} else {
			$middlename = addslashes($_POST["middlename"]);
		}
		
		$ident = $this->addMember($firstname,$middlename,$lastname,$gruppe);
		
		if (isset($_POST['foresattil'])){
			$this->registerGuardian($_POST['foresattil'], $ident);
		}
		
		$this->redirect($this->generateCoolURL("/medlemmer/$ident","memberadded"));

	}

	function editPeffForm($gruppe){

		if (!$this->allow_editpeff) return $this->permissionDenied();

		$g = $this->groups[$gruppe];

		if ($g->kategori != "SP" && $g->kategori != "RO"){
			return $this->notSoFatalError("Gruppen er ikke en patrulje. Du kan kun endre peff for patruljer.");
		}

		$url_post = $this->generateURL(array("noprint=true","savepeff"));
		$url_back = $this->generateURL("");

		$peff = $this->peff_i($gruppe);
		if ($peff != -1){
			$current_peff = "Nåværende peff: ".$this->members[$peff]->fullname;
		} else {
			$current_peff = "Denne gruppen har ingen registrert nåværende peff";
		}

		$options_list =  "<option value=\"0\">Velg person:</option>\n";
		foreach ($g->members as $mi){
			$m = $this->members[$mi];
			$options_list .= "<option value='$m->ident' ".(($peff==$m->ident)?"selected='selected'":"").">$m->fullname</option>\n";
		}

		return '
			<h3>Utnevn ny peff i '.$g->caption.'</h3>
			<form method="post" action="'.$url_post.'">
				<p>
					'.$current_peff.'
				</p>
				Ny peff:
				<select name="nypeff">
					'.$options_list.'
				</select>
				<p>
					<input type="button" value="Avbryt" onclick=\'window.location="'.$url_back.'"\' /> 
					<input type="submit" value="Utnevn ny peff" />
				</p>
			</form>
		';
	}
	
	function getRangIdFromTitle($title) {
		$res = $this->query(
			"SELECT id FROM $this->table_rang WHERE tittel=\"".addslashes($title)."\""
		);
		if ($res->num_rows != 1) return false;
		$row = $res->fetch_row();
		return $row[0];
	
	}

	function savePeff($gruppe){

		if (!$this->allow_editpeff) return $this->permissionDenied();

		$g = $this->groups[$gruppe];

		$vervObj = $this->vervredigeringInstance();
		$nypeff = $_POST['nypeff'];
		$peffverv = $vervObj->getVervBySlug('peff');
		$assverv = $vervObj->getVervBySlug('ass');

		$peffrang = $this->getRangIdFromTitle('Peff');
		$assrang = $this->getRangIdFromTitle('Ass');
		
		$gammelpeff = $this->peff_i($gruppe);
		$gammelass = $this->ass_i($gruppe);
		$forerpatrulje = $this->forerpatruljeid;
		
		if (!is_numeric($peffverv)) $this->fatalError("Site-setting peffverv not correctly set. Please correct.");
		if (!is_numeric($assverv)) $this->fatalError("Site-setting assverv not correctly set. Please correct.");
		if (!is_numeric($peffrang)) $this->fatalError("Site-setting peffrangid not correctly set. Please correct.");
		if (!is_numeric($assrang)) $this->fatalError("Site-setting assrangid not correctly set. Please correct.");
		if (!$this->isUser($nypeff)) $this->fatalError("Invalid input .9");
		if (!$this->isgroup($forerpatrulje)) $this->fatalError("Fant ikke id for førerpatruljen. Sjekk sideinnstillingene.");
		if ($gammelpeff == $nypeff)	$this->fatalError("Du kan ikke utnevne en eksisterende peff på nytt!");

		if ($gammelpeff != -1){											// Hvis gammelpeff eksisterer
			$vervObj->stoppPeffVerv($gammelpeff,$gruppe);					// Stopp peffverv
			$this->stopMembership(0,$gammelpeff,$forerpatrulje);			// Avslutt medlemskap i Førerpatruljen for gammelpeff
			$this->reloadMemberlist();
			$this->resetRights($gammelpeff);								// Fjern peffrettigheter
		}
		if ($gammelass == $nypeff){										// Hvis det er assen som går over til å bli peff
			$vervObj->stoppAssVerv($gammelass,$gruppe);						// Stopp assverv
		} else {														// Hvis ikke...
			$this->startMembership($nypeff,$forerpatrulje);					// Start medlemskap i Førerpatruljen for nypeff
		}

		$this->setRights($nypeff,3);									// Sett peffrettigheter
		$this->setRang($nypeff,$peffrang);										// Sett peffrang
		$vervObj->startPeffVerv($nypeff,$gruppe);						// Start patruljeførerverv
		
		$this->redirect($this->generateURL(""));
	}

	function editAssForm($gruppe){

		if (!$this->allow_editass) return $this->permissionDenied();

		$g = $this->groups[$gruppe];

		if ($g->kategori != "SP" && $g->kategori != "RO") return $this->notSoFatalError("Gruppen er ikke en patrulje. Du kan kun endre ass for patruljer.");

		$url_post = $this->generateURL(array("noprint=true","saveass"));
		$url_back = $this->generateURL("");

		$ass = $this->ass_i($gruppe);
		if ($ass != -1){
			$current_ass = "Nåværende ass: ".$this->members[$ass]->fullname;
		} else {
			$current_ass = "Denne gruppen har ingen registrert nåværende ass";
		}

		$options_list =  "<option value=\"0\">Velg person:</option>\n";
		foreach ($g->members as $mi){
			$m = $this->members[$mi];
			$options_list .= "<option value='$m->ident' ".(($ass==$m->ident)?"selected='selected'":"").">$m->fullname</option>\n";
		}

		return '
			<h3>Utnevn ny ass i '.$g->caption.'</h3>
			<form method="post" action="'.$url_post.'">
				<p>
					'.$current_ass.'
				</p>
				Ny ass:
				<select name="nyass">
					'.$options_list.'
				</select>
				<p>
					<input type="button" value="Avbryt" onclick=\'window.location="$url_back"\' /> 
					<input type="submit" value="Utnevn ny ass" />
				</p>
			</form>
		';
	}

	function saveAss($gruppe){

		if (!$this->allow_editass) return $this->permissionDenied();

		$g = $this->groups[$gruppe];

		$vervObj = $this->vervredigeringInstance();
		$nyass = $_POST['nyass'];

		$peffverv = $vervObj->getVervBySlug('peff');
		$assverv = $vervObj->getVervBySlug('ass');

		$peffrang = $this->getRangIdFromTitle('Peff');
		$assrang = $this->getRangIdFromTitle('Ass');

		$gammelpeff = $this->peff_i($gruppe);
		$gammelass = $this->ass_i($gruppe);
		$forerpatrulje = $this->forerpatruljeid;
		
		if (!is_numeric($peffverv)) $this->fatalError("Site-setting peffverv not correctly set. Please correct.");
		if (!is_numeric($assverv)) $this->fatalError("Site-setting assverv not correctly set. Please correct.");
		if (!is_numeric($peffrang)) $this->fatalError("Site-setting peffrangid not correctly set. Please correct.");
		if (!is_numeric($assrang)) $this->fatalError("Site-setting assrangid not correctly set. Please correct.");
		if (!$this->isUser($nyass)) $this->fatalError("Invalid input .9");
		if (!$this->isgroup($forerpatrulje)) $this->fatalError("Fant ikke id for førerpatruljen. Sjekk sideinnstillingene.");
		if ($gammelass == $nyass) $this->fatalError("Du kan ikke utnevne en eksisterende ass på nytt!");
		
		if ($gammelass != -1){											// Hvis gammelass eksisterer
			$vervObj->stoppAssVerv($gammelass,$gruppe);						// Stopp assverv
			$this->stopMembership(0,$gammelass,$forerpatrulje);			// Avslutt medlemskap i Førerpatruljen for gammelass
			$this->reloadMemberlist();
			$this->resetRights($gammelass);									// Fjern assrettigheter
		}
		if ($gammelpeff == $nyass){										// Hvis det er peffen som går over til å bli ass
			$vervObj->stoppPeffVerv($gammelpeff,$gruppe);					// Stopp peffverv
		} else {														// Hvis ikke...
			$this->startMembership($nyass,$forerpatrulje);					// Start medlemskap i Førerpatruljen for nyass
		}

		$this->setRights($nyass,3);										// Sett assrettigheter
		$this->setRang($nyass,$assrang);								// Sett assrang
		$vervObj->startAssVerv($nyass,$gruppe);							// Start assistentførerverv

		$this->redirect($this->generateURL(""));
	}
	
	function sortGroups($a,$b){
		if ($a->position == $b->position) {
	     return 0;
	    }
	    return ($a->position < $b->position) ? -1 : 1;
	}
	
	/*
	function editGroupPositionsForm(){

		if (!$this->allow_addgroup) return $this->permissionDenied();

		usort($this->groups,array("memberlist","sortGroups"));
		
		$optionsList = "";
		for ($i = 0; $i < count($this->groups); $i++){
			$optionsList .= "<option value=\"".$this->groups[$i]->id."\">".$this->groups[$i]->caption."</option>";
		}
		return '
			<h3>Endre grupperekkefølge</h3>
			<p>
				Velg en gruppe, trykk deretter flytt opp eller flytt ned for å bevege gruppen opp eller ned på posisjonslisten. 
				<br /><br />
				<b>NB!</b> Trykk <i>kun en gang</i> før endringene vises.
			</p>
			<form action="'.$this->generateURL(array("noprint=true","savegrouppositions")).'" method="post" name="frm">
				<select size="8" name="gid" style="width:250px;">
					'.$optionsList.'
				</select>
				<br />
				<input type="submit" name="opp" value="Flytt opp" />
				<input type="submit" name="ned" value="Flytt ned" />
			</form>
			<p>
				<a href="'.$this->generateURL("").'">Tilbake</a>
			</p>
		';
	}

	function saveGroupPositions(){

		if (!$this->allow_addgroup) return $this->permissionDenied();

		if (isset($_POST["opp"])){
			$res = $this->moveGroupUp($_POST['gid']);
		} else if (isset($_POST["ned"])){
			$res = $this->moveGroupDown($_POST['gid']);
		}
		if (strlen($res)>0) 
			$this->redirect($this->generateURL("editgrouppositions"),"Det oppstod en feil"); 
		else
			$this->redirect($this->generateURL("editgrouppositions")); 
	}
	*/
	
	function addGroupForm(){

		if (!$this->allow_addgroup) return $this->permissionDenied();
		
		$gruppenavn = "";
		$slug = "";
		$skjulgruppe = "";
		$def_rights = -1;
		$def_rang = -1;
		$def_kat = -1;
		$parent_group = -1;
		
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
			return $this->notSoFatalError($errstr,array('logError'=>false,'customHeader'=>'Gruppen ble ikke opprettet fordi:'));
			
			$postdata = $_SESSION['postdata'];
			$gruppenavn = $postdata['gruppenavn'];
			$slug = $postdata['slug'];
			$def_rights = $postdata['def_rights'];
			$def_rang = $postdata['def_rang'];
			$def_kat = $postdata['kategori'];
			$parent_group = $postdata['parent_group'];
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);
			
		}
		
		
		$res = $this->query("
			SELECT 
				id, 
				tittel, 
				classname,
				kategori
			FROM 
				$this->table_rang 
			ORDER BY
				position
			"
		);
		$titulering = "<select name='def_rang'>
			<option value='0'>Ikke sett</option>";
		while ($row = $res->fetch_assoc()){
			$id = $row['id'];
			$caption = stripslashes($row['tittel']);
			$titulering .= "<option value='$id' ".(($row['id']==$def_rang)?"selected='selected'":"").">$caption</option>";
		}
		$titulering .= "</select>";
				
		$tilgang = "<select name='def_rights'>
			<option value='0'>Ikke sett</option>";
		$res = $this->query("SELECT level, shortdesc, longdesc FROM $this->table_rights ORDER BY level");
		while ($row = $res->fetch_assoc()) {
			$level = $row['level'];
			$shortdesc = stripslashes($row['shortdesc']);
			$tilgang .= "<option value='$level' ".(($level==$def_rights)?"selected='selected'":"").">$level: $shortdesc</option>";
		}
		$tilgang .= "</select>";
		
		$grouplist = $this->generateGroupSelectBox("parent_group",false,array(),$parent_group,true);

		/*** KATEGORIER ***/

		$res = $this->query("
			SELECT id, caption
			FROM $this->table_groupcats"
		);
		$kategorier = "<select name='kategori' id='kategori' onchange='sjekkKat();'>\n";
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$caption = $row['caption'];
			$kategorier .= "<option value='$id'>($caption ($id)</option>";
		}
		$kategorier .= "</select>";

		

		$url_savegroupsettings = $this->generateURL(array("doaddgroup","noprint=true"));

		return '
			<h3>Opprett ny gruppe</h3>
			<form method="post" action="'.$url_savegroupsettings.'">
				<table class="skjema">
					<tr>
						<td>Navn på ny gruppe: </td>
						<td>Undergruppe av:</td>
					</tr><tr>
						<td><input type="text" name="gruppenavn" value="'.$gruppenavn.'" size="30" /></td>
						<td>'.$grouplist.'</td>
					</tr><tr>
				</table>
				<table class="skjema">
					<tr>
					<td>Adresse: </td>
					</tr><tr>
						<td>
							http://'.$_SERVER["SERVER_NAME"].'/'.$this->coolUrlPrefix.'/ <input type="text" name="slug" value="'.$slug.'" size="20" />
						</td>
					</tr>
				</table>
				<table class="skjema">
					<tr>
						<td>Kategori: </td>
					</tr><tr>
						<td>
							'.$kategorier.'
						</td>
					</tr>
				</table>				
				
				<h3>Innstillinger for nye medlemmer</h3>
				
				<p>Medlemmer som meldes inn i denne gruppen vil automatisk få disse innstillingene:</p>
				<table class="skjema">
					<tr>
						<td>Tilgangsnivå:</td>
						<td>Rolle:</td>
					</tr><tr>
						<td>'.$tilgang.'</td>
						<td>'.$titulering.'</td>
					</tr>
				</table>
				<br />
				<input type="submit" value="Lagre" />
			</form>
		';

	}

	// Lagre gruppeinnstillinger
	function addGroup(){
		
		if (!$this->allow_addgroup) return $this->permissionDenied();
		
		
		/* Validate Input BEGIN */
		$errors = array();
		if (empty($_POST['gruppenavn'])){ 
			array_push($errors,"empty_title"); 
			$tittel = "";
		} else {
			$tittel = addslashes($_POST['gruppenavn']);
			$res = $this->query("SELECT id FROM $this->table_groups WHERE caption='$tittel'");
			if ($res->num_rows > 0){ array_push($errors,"title_notunique"); } 
		}
		if (empty($_POST['slug'])){ 
			array_push($errors,"empty_slug"); 
			$slug = "";
		} else {
			$slug = addslashes($_POST['slug']);
			$res = $this->query("SELECT id FROM $this->table_groups WHERE slug='$slug'");
			if ($res->num_rows > 0){ array_push($errors,"slug_notunique"); } 
			if (preg_match("/[^a-z0-9_-]/",$slug)){
				array_push($errors,"slug_contain_specials");
			}
			if (in_array($slug,$this->reserved_slugs)){
				array_push($errors,"slug_reserved");
			}
		}
		$parent_group = $_POST['parent_group'];
		if (!is_numeric($parent_group)) $this->fatalError("invalid input .762");
		$def_rights = $_POST['def_rights'];
		if (!is_numeric($def_rights)) $this->fatalError("invalid input .763");
		$def_rang = $_POST['def_rang'];
		if (!is_numeric($def_rang)) $this->fatalError("invalid input .764");
		
		$kategori = addslashes($_POST['kategori']);
		
		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$this->redirect($this->generateURL("addgroup"),"Gruppen ble ikke opprettet pga. en eller flere feil.");
		}
		/* Validate Input END */

		$res = $this->query("SELECT MAX(position) FROM $this->table_groups WHERE parent='$parent_group'");
		$row = $res->fetch_row();
		$pos = $row[0]+1;

		$this->query("INSERT INTO $this->table_groups 
			(parent,position,caption,visgruppe,defaultrights,defaultrang,slug,kategori)
			VALUES
			('$parent_group','$pos','$tittel','1','$def_rights','$def_rang','$slug','$kategori')"
		);
		$id = $this->insert_id();

		$this->logEvent('GROUP_CREATED', 0, $id);

		// Redirect
		$this->redirect($this->generateCoolURL("/$slug"),"Gruppen ble opprettet");
	}
	
	
	function printGroupHistoryXML($id) {
		$gruppe = $this->groups[$id];
		header("Content-Type: text/xml; charset=utf-8");
		print "<data>\n";
		$res = $this->query("SELECT bruker,startdate,enddate FROM $this->table_group_memberships WHERE gruppe='$id'");
		while ($row = $res->fetch_assoc()){
			$start = date("M j Y H:i:s",strtotime($row['startdate']));
			$end = ($row['enddate'] == '0000-00-00') ? date("M j Y H:i:s",time()) : date("M j Y H:i:s",strtotime($row['enddate']));
			
			if ($this->isUser($row['bruker'])){
				$title = $this->members[$row['bruker']]->firstname;
				$fulltitle = $this->members[$row['bruker']]->fullname;
			} else {
				$title = "Ukjent";
				$fulltitle = "Ukjent";
			}
			$img_user = ROOT_DIR.$this->getProfileImage($row['bruker']);
			print "
				<event
					start=\"$start GMT\"
					end=\"$end GMT\"
					title=\"$title\"
					image=\"$img_user\"
				>
				$fulltitle&lt;br /&gt;
				&lt;a href=\"".$this->generateCoolURL("/medlemmer/".$row['bruker'])."\" target=\"_top\"&gt;Vis medlemsprofil&lt;/a&gt;
				</event>
			";
		}		
		print "</data>";
		exit();
	}
	
	function printGroupHistoryIframe($id) {
		print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
 <html>
   <head>
     <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
     <script src="http://api.simile-widgets.org/timeline/2.3.1/timeline-api.js" type="text/javascript"></script>
     <style type="text/css">
     body {
     	margin:0px;
     	padding:0px;
     	font-size:8pt;
        font-family: Trebuchet MS, Helvetica, Arial, sans serif;
     }
     .timeline-default {
    font-family: Trebuchet MS, Helvetica, Arial, sans serif;
    font-size: 8pt;
    border: 1px solid #aaa;
}
</style>
   </head>
   <body onload="makeTimeline()" onresize="onResize()">

	<div id="t1" class="timeline-default" style="height: 400px; border: 1px solid #aaa;"></div>			
	<p>Timeline version <span id="tl_ver"></span>.</p>
						

			<script type="text/javascript">
			//<![CDATA[
			
				Timeline.writeVersion("tl_ver");
			
				var tl;
				
				function makeTimeline() {
					var eventSource = new Timeline.DefaultEventSource();
					
					var theme = Timeline.ClassicTheme.create();
					theme.event.bubble.width = 350;
					theme.event.bubble.height = 150;
					
					var bandInfos = [
						Timeline.createBandInfo({
							width:          "100%", 
							intervalUnit:   Timeline.DateTime.YEAR, 
							intervalPixels: 50,
							eventSource:    eventSource,
							date:           "Jun 28 2006 00:00:00 GMT",
							theme: theme
						})
					];
					
					tl = Timeline.create(document.getElementById("t1"), bandInfos); //, Timeline.HORIZONTAL);
					
					Timeline.loadXML("'.ROOT_DIR.$this->generateURL(array("noprint=true","history_xml"),true).'", function(xml, url) { 
						eventSource.loadXML(xml, url); 
					});
				}
				
				var resizeTimerID = null;
				function onResize() {
					if (resizeTimerID == null) {
						resizeTimerID = window.setTimeout(function() {
							resizeTimerID = null;
							tl.layout();
						}, 500);
					}
				}
				window.onload = makeTimeline;
				//YAHOO.util.Event.onDOMReady(makeTimeline); 
				
    		//]]>
			</script>	
   	
   </body>
</html>';	
	}
	
	function printGroupHistory($id) {
	
		$gruppe = $this->groups[$id];
		
		$res = $this->query("SELECT COUNT(DISTINCT bruker) as member_count FROM $this->table_group_memberships WHERE gruppe='$id'");
		$row = $res->fetch_assoc();
		$member_count = $row['member_count'];

		$res = $this->query("SELECT bruker,startdate FROM $this->table_group_memberships WHERE gruppe='$id' ORDER BY startdate LIMIT 1");
		$first = $res->fetch_assoc();
		$oldest = date("Y",strtotime($first['startdate']));

		return '
			<h3>'.$gruppe->caption.': Historie</h3>
			<p>
				Totalt '.$member_count.' personer er eller har vært medlem i '.$gruppe->caption.'.
				Første registrerte medlem ble med i '.$oldest.'.
			</p>
			<p>
				Er du eller har du vært medlem i '.$gruppe->caption.'?
				<ul>
				<li>Hvis du har bruker på 18bergen.org, kan du legge deg til under "Medlemskap"
					på <a href="/oppdater-min-info/">din medlemsprofil</a>.</li>
				<li>Hvis du ikke har bruker, er du velkommen til å 
					<a href="/registrering/">registrere deg</a>.</li>
				</ul>
			</p>
			<p>
				Du kan trykke og dra tidslinjen under bakover i tid for å se hvem som har 
				vært medlem tidligere. Oversikten er mangelfull.
			</p>
			
			<iframe id="myiframe" style="border:none;" width="490" height="450" src="'.$this->generateUrl(array('noprint=true','history_iframe')).'"></iframe>

			<p>
				<a href="'.$this->generateURL("").'" style="font-weight:bold;">Tilbake</a>
			</p>

		';
		
	
	}

	// Vis gruppe-side
	function viewGroup(){
	
		$group_id = $this->current_gruppe;
		$g = $this->getGroupById($group_id);	

		$output = "";		
		
		$uids = $g['Users'];
		$users = $this->getUserData($uids,array('Guardians','ProfileUrl','Title','ProfilePicture','FirstName'));
		
		$parents = array();
		foreach ($users as $u) {
			foreach ($u['Guardians'] as $uid) {
				$parents[] = $uid;
			}
		}
		$parents = array_unique($parents);
		$people_and_parents = array_merge($uids, $parents);
				
		$mailUrlMembers = $this->messageUrl."?recipients=".implode(",",$uids);		
		$mailUrlParents = $this->messageUrl."?recipients=".implode(",",$parents);		
		$mailUrlAll = $this->messageUrl."?recipients=".implode(",",$people_and_parents);		


		$caption = $g['Caption'];
		if ($g['Category'] == "SP") $caption = 'Patruljen '.$caption;
		else if ($g['Category'] == "RO") $caption = 'Roverpatruljen '.$caption;


				
		$output .= '
			<h3>'.$caption.'</h3>
			
			<p class="hidefromprint">';
				if (!empty($g['GroupPages'])) {
					$output .= '
					<a href="'.$g['GroupPages'].'" class="icn" 
						style="background-image:url(/images/icns/world.png)">Patruljesider</a>
					';
				}
				$output .= '
				<a href="'.$this->generateURL('action=viewTimeline').'" class="icn" 
					style="background-image:url(/images/icns/hourglass.png)">Tidslinje</a>
				<a href="'.$this->generateURL('action=editGroupSettings').'" class="icn" 
					style="background-image:url(/images/icns/wrench_orange.png)">Innstillinger</a>
				';
				if ($g['Category'] == "SP" || $g['Category'] == "RO"){				
					if ($this->allow_editpeff || $this->allow_editass){
					$output .= '
					<a href="'.$this->generateURL('action=utnevnPeff').'" class="icn" 
						style="background-image:url(/images/icns/star.png)">Utnevn ny peff</a>
					<a href="'.$this->generateURL('action=utnevnAss').'" class="icn" 
						style="background-image:url(/images/icns/star.png)">Utnevn ny ass</a>
					';
				}}
				if ($this->allow_addmember){
					$output .= '
						<a href="'.$this->generateURL('action=addMember').'" class="icn" 
							style="background-image:url(/images/icns/user_add.png)">Legg til nytt medlem</a>
					';
				}
				$output .= '
			</p>
			<img src="/images/mail.gif" alt="Epost" /> Send mail til 
			<a href="'.$mailUrlMembers.'">medlemmer</a>';
			if (count($parents) > 0) {
				$output .= ', <a href="'.$mailUrlParents.'">foresatte</a> eller <a href="'.$mailUrlAll.'">medlemmer og foresatte</a>';
			}
			$output .= '			
			<ul>				
		';
		
		$lang = $this->preferred_lang;
		$output .= '
		</ul>
		';
		$membercount = count($uids);
		$output .= "<h4>$caption har for tiden $membercount medlemmer:</h4>";

		if (isset($_GET['errors'])){
			$errors = explode(",",$_GET['errors']);
			$errorS = "";
			foreach ($errors as $e){
				$errorS .= "<div>".$this->errorMessages[$e]."</div>";
			}
			$output .= $this->notSoFatalError($errorS);
		}
		
		$output .= "
			<ul class='memberlist_imageview' style='padding-left:15px;'>
		";
		
		foreach ($users as $u){			
			$url_profile = $u['ProfileUrl'];
			$verv = "<br /><span style=\"font-size: smaller;\">(".$u['Title'].")</span>";
			$output .= '				
				<li class="bildegalleri">

					<div class="alpha-shadow noframe">
						<div class="inner_div">
							'.($this->isLoggedIn() ? '<a href="'.$url_profile.'">':'').
							'<img src="'.$u['ProfilePicture']['SmallThumb'].'" style="width: 105px; height: 140px;" alt="'.$u['FirstName'].'" />'.
							($this->isLoggedIn() ? '</a>':'').'
						</div>
					</div>
					<div style="clear:both;font-size:small; text-align: center; width: 120px; height: 44px;">'.$u['FirstName'].$verv.'</div>
					
				</li>
			';
		}
		$output .= "
			</ul>
		";
		
		
		// Nyhetsbrev:
		$output .= "<div style='clear:both;'><!-- --></div>";
		$output .= "Medlemmene av denne gruppen mottar følgende nyhetsbrev:<br />";
		$cnt = 0;
		$res = $this->query("SELECT 
				$this->table_pages.fullslug as uri,
				$this->table_pageoptions.value as groups,
				$this->table_pagelabels.value as caption 
			FROM 
				$this->table_pages,$this->table_pageoptions,$this->table_pagelabels 
			WHERE 
				$this->table_pages.id=$this->table_pageoptions.page
				AND
				$this->table_pageoptions.name='recipient_groups'
				AND
				$this->table_pagelabels.page=$this->table_pageoptions.page
				AND
				$this->table_pagelabels.lang='$lang'
			"
		);
		while ($row = $res->fetch_assoc()) {
			$tmp = $g;
			do {
				if (in_array($tmp['Id'],explode(",",$row['groups']))) {
					$cnt++;
					$output .= '<img src="'.$this->image_dir.'mail.gif" alt="Mail" /> <a href="/'.$row['uri'].'">'.$row['caption'].'</a><br />';
					break;
				}
				if ($tmp['ParentGroup'] == 0) break;
				$tmp = $this->getGroupById($tmp['ParentGroup']);				

			} while ($tmp['ParentGroup'] != 0);
		}
		if ($cnt == 0) $output .= "<em>Ingen</em>";

		return $output;
		
	}
	
	function editGroupSettings() {

		$group_id = $this->current_gruppe;
		$g = $this->getGroupById($group_id);	

		if (!$this->allow_editgroupsettings){
			return $this->permissionDenied();
		}
		$output .= "<h4>Gruppeinnstillinger:</h4>";
		
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
			$output .= $this->notSoFatalError($errstr,array('logError'=>false,'customHeader'=>'Gruppeinnstillingene ble ikke lagret fordi:'));
						
			unset($_SESSION['errors']);
			unset($_SESSION['postdata']);
			
		}
		
		$url_savegroupsettings = $this->generateURL(array("savegroupsettings","noprint=true"));
		if ($g['Visible'] == 0){ $skjulgruppe = "checked=\"checked\""; } else { $skjulgruppe = ""; }
		$gruppenavn = $g['Caption'];
		$slug = $g['Slug'];
		$gruppesider = $g['GroupPages'];
		
		/*** TITULERING ***/
		
		$res = $this->query("
			SELECT 
				id, 
				tittel, 
				classname,
				kategori
			FROM 
				$this->table_rang 
			ORDER BY
				position
			"
		);
		$titulering = "<select name='def_rang'>
			<option value='0'>Ikke sett</option>";
		while ($row = $res->fetch_assoc()){
			$id = $row['id'];
			$caption = stripslashes($row['tittel']);
			$titulering .= "<option value='$id' ".(($row['id']==$g['DefaultRang'])?"selected='selected'":"").">$caption</option>";
		}
		$titulering .= "</select>";
		
		/*** TILGANG ***/
				
		$tilgang = "<select name='def_rights'>
			<option value='0'>Ikke sett</option>";
		$res = $this->query("SELECT level, shortdesc, longdesc FROM $this->table_rights ORDER BY level");
		while ($row = $res->fetch_assoc()) {
			$level = $row['level'];
			$shortdesc = stripslashes($row['shortdesc']);
			$tilgang .= "<option value='$level' ".(($level==$g['DefaultRights'])?"selected='selected'":"").">$level: $shortdesc</option>";
		}
		$tilgang .= "</select>";
	
		/*** PARENT GROUP LIST ***/

		$grouplist = $this->generateGroupSelectBox("parent_group",false,array(),$g['ParentGroup'],true);
		
		/*** KATEGORIER ***/

		$res = $this->query("
			SELECT id, caption
			FROM $this->table_groupcats"
		);
		$kategorier = "<select name='kategori' id='kategori'>\n";
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$caption = $row['caption'];
			$kategorier .= "<option value='$id' ".(($id==$g['Category'])?"selected='selected'":"").">$caption ($id)</option>";
		}
		$kategorier .= "</select>";
					
		/*** PRINT FORM ***/
		
		if ($g['Category'] != "FO") {
			$foresattstyle = "display:none";
		} else {
			$foresattstyle = "";
		}

		$output .= "
			<form method='post' action='$url_savegroupsettings'>
				<table class='skjema'>
					<tr>
						<td>Navn: </td>
						<td><input type=\"text\" name=\"gruppenavn\" value=\"$gruppenavn\" size=\"40\" /></td>
					</tr><tr>
						<td>Adresse: </td>
						<td>http://".$_SERVER['SERVER_NAME']."/".$this->coolUrlPrefix."/ <input type=\"text\" name=\"slug\" value=\"$slug\" size=\"20\" /></td>
					</tr><tr>
						<td>Nettsider: </td>
						<td>http://".$_SERVER['SERVER_NAME']." <input type=\"text\" name=\"gruppesider\" value=\"$gruppesider\" size=\"20\" /></td>
					</tr><tr>
						<td>Undergruppe av: </td>
						<td>$grouplist</td>
					</tr><tr>
						<td>Kategori:</td>
						<td>$kategorier</td>
					</tr><tr>
						<td>Skjul gruppe:</td>
						<td><input type=\"checkbox\" id=\"skjulgruppe\" name=\"skjulgruppe\" $skjulgruppe /></td>
					</tr><tr>
						<td>Default tilgangsnivå:</td>
						<td>$tilgang</td>
					</tr><tr>
						<td>Default rolle:</td>
						<td>$titulering</td>
					</tr>
				</table>
				<br />
				<input type=\"submit\" value=\"Lagre\" />
			</form>
		";		
		
		return $output;
	}

	// Lagre gruppeinnstillinger
	function saveGroupSettings($id){

		if (!$this->allow_editgroupsettings){
			$this->permissionDenied();
			return 0;
		}
		
		$data = array();
		
		/* Validate Input BEGIN */
		$errors = array();
		if (empty($_POST['gruppenavn'])){ 
			array_push($errors,"empty_title"); 
		} else {
			$data['caption'] = $tittel = addslashes($_POST['gruppenavn']);
			$res = $this->query("SELECT id FROM $this->table_groups WHERE caption='$tittel' AND id!=id");
			if ($res->num_rows > 0){ array_push($errors,"title_notunique"); } 
		}
		if (empty($_POST['slug'])){ 
			array_push($errors,"empty_slug"); 
		} else {
			$data['slug'] = $slug = addslashes($_POST['slug']);
			$res = $this->query("SELECT id FROM $this->table_groups WHERE slug='$slug' AND id!=$id");
			if ($res->num_rows > 0){ array_push($errors,"slug_notunique"); } 
			if (preg_match("/[^a-z0-9_-]/",$slug)){
				array_push($errors,"slug_contain_specials");
			}
			if (in_array($slug,$this->reserved_slugs)){
				array_push($errors,"slug_reserved");
			}
		}
		$data['gruppesider'] = $_POST['gruppesider'];
		$data['parent'] = $_POST['parent_group'];
		if (!is_numeric($data['parent'])) $this->fatalError("invalid input .762");
		$data['defaultrights'] = $_POST['def_rights'];
		if (!is_numeric($data['defaultrights'])) $this->fatalError("invalid input .763");
		$data['defaultrang'] = $_POST['def_rang'];
		if (!is_numeric($data['defaultrang'])) $this->fatalError("invalid input .764");
		$data['visgruppe'] = (isset($_POST['skjulgruppe']) && ($_POST['skjulgruppe'] == 'on')) ? 0 : 1;
		if (!is_numeric($data['visgruppe'])) $this->fatalError("invalid input .765");
		
		$data['kategori'] = addslashes($_POST['kategori']);
		if (count($errors) > 0) {
			$_SESSION['errors'] = array_unique($errors);
			$_SESSION['postdata'] = $_POST;
			$this->redirect($this->generateURL(""),"Gruppeinnstillingene ble ikke lagret pga. en eller flere feil.");
		}
		/* Validate Input END */

		if ($data['parent'] != $this->groups[$id]->parent) {
			$res = $this->query("SELECT MAX(position) FROM $this->table_groups WHERE parent='".$data['parent']."'");
			$row = $res->fetch_row();
			$data['position'] = $row[0]+1;
		}
		
		$this->setGroupOptions($id, $data);
		
		// Redirect
		$this->redirect($this->generateCoolURL("/$slug"),"Gruppeinnstillingene ble lagret!");
	}


	/* 
	############################################################################################################################

		MEMBERSHIP STUFF

	############################################################################################################################
	*/	

	function startMembership($user_id, $group_id, $silent = false){

		if (!$this->allow_editmemberships){
			$this->permissionDenied();
			return 0;
		}
		$user_id = intval($user_id);
		$group_id = intval($group_id);
		if (!$this->isGroup($group_id)) $this->fatalError("Gruppen eksisterer ikke!");
		if (!$this->isUser($user_id)) $this->fatalError("Brukeren eksisterer ikke!");
		$this->query("INSERT INTO $this->table_group_memberships (bruker,gruppe,startdate) ".
			"VALUES ($user_id,$group_id,CURDATE());");
		$lastid = $this->insert_id();
		if (!$silent) {
			$this->addToActivityLog("startet medlemskap i ".$this->groups[$group_id]->caption." for ".$this->makeMemberLink($user_id).".");
			$this->logEvent('USER_JOINED_GROUP', $user_id, $group_id);
		}
		if ($this->members[$user_id]->rights < $this->groups[$group_id]->defaultrights){
			$this->setRights($user_id,$this->groups[$group_id]->defaultrights);
		}
		
		if ($this->groups[$group_id]->defaultrang > 0){
			$this->setRang($user_id,$this->groups[$group_id]->defaultrang);
		}

		return $lastid;
	}

	function stopMembership($membership_id = 0, $user_id = 0, $group_id = 0, $silent = false){

		if (!$this->allow_editmemberships){
			print $this->permissionDenied();
			exit();
		}
		
		$membership_id = intval($membership_id);
		$user_id = intval($user_id);
		$group_id = intval($group_id);
		
		if ($membership_id == 0){	// (A) Stop the membership of $user_id in $group_id
			if ($user_id == 0 || $group_id == 0){ 
				$this->fatalError("Fant ikke medlemskapet (1)!"); 
			}
			$res = $this->query("SELECT caption FROM $this->table_groups WHERE id=".$group_id);
			if ($res->num_rows != 1){ $this->fatalError("Fant ikke gruppen!"); }
			$row = $res->fetch_assoc();
			$group_name = $row['caption'];
			$this->query("UPDATE $this->table_group_memberships SET enddate=CURDATE() WHERE bruker=$user_id AND gruppe=$group_id AND enddate='0000-00-00'");
            if ($this->affected_rows() != 1) {
                //$this->fatalError("Klarte ikke å avslutte medlemskap i $group_name!");
                // Medlemsskapet kan allerede være avsluttet....
            }
			if (!$silent) {
				$this->addToActivityLog("avsluttet medlemskap i $group_name for ".$this->makeMemberLink($user_id).".");
				$this->logEvent('USER_LEFT_GROUP', $user_id, $group_id);
			}
		} else { 					// (B) Stop the membership $membership_id
			$res = $this->query("SELECT bruker,gruppe FROM $this->table_group_memberships WHERE id=$membership_id");
			if ($res->num_rows != 1){ $this->fatalError("Fant ikke medlemskapet!"); }
			$row = $res->fetch_assoc();
			$user_id = intval($row['bruker']);
			$group_id = intval($row['gruppe']);
			$res = $this->query("SELECT caption FROM $this->table_groups WHERE id=$group_id");
			$row = $res->fetch_assoc();
			$group_name = $row['caption'];
			$this->query("UPDATE $this->table_group_memberships SET enddate=CURDATE() WHERE id=$membership_id");
			if (!$silent) {
				$this->addToActivityLog("avsluttet medlemskap i $group_name for ".$this->makeMemberLink($user_id).".");
				$this->logEvent('USER_LEFT_GROUP', $user_id, $group_id);
			}
		}
		$isLeader = $this->isGroupLeader($user_id);
		if ($isLeader != 0){
			if ($isLeader['group'] == $group_id){
				$vervObj = $this->vervredigeringInstance();
				if ($isLeader['tittel'] == "peff"){
					$vervObj->stoppPeffVerv($user_id, $group_id);
				} else if ($isLeader['tittel'] == "ass"){
					$vervObj->stoppAssVerv($user_id, $group_id);
				}
			}
		}
	}

	function fetchMembershipDetails($id){
		$res = $this->query(
			"SELECT 
				$this->table_groups.id as groupid, 
				$this->table_groups.caption as groupcaption, 
				$this->table_group_memberships.startdate as membershipstart, 
				$this->table_group_memberships.enddate as membershipend
			FROM 
				$this->table_group_memberships, $this->table_groups 
			WHERE $this->table_group_memberships.id='$id'
				AND $this->table_group_memberships.gruppe=$this->table_groups.id"
		);
		if ($res->num_rows != 1){
			$this->fatalError("Ugyldig inn-data!");
		}
		$row = $res->fetch_assoc();
		$row['membershipstart'] = strtotime($row['membershipstart']);
		$row['membershipend'] = ($row['membershipend']=='0000-00-00') ? 0 : strtotime($row['membershipend']);
		return $row;
	}

	function validateMembershipRelation($member,$id){
		// Sjekk at vi har fått en gyldig id, og at medlemskapet tilhører brukeren vi har klarert redigering for
		$res = $this->query("SELECT bruker,gruppe FROM $this->table_group_memberships WHERE id='$id'");
		if ($res->num_rows != 1) return false;
		$row = $res->fetch_assoc();
		if ($row['bruker'] != $this->current_medlem) return false;
		return true;
	}

	function groupCaptionFromMembership($id){
		$rs = $this->query("SELECT gruppe FROM $this->table_group_memberships WHERE id='$id'");
		if ($rs->num_rows != 1) $this->fatalError("Ugyldig inn-data!");
		$row = $rs->fetch_assoc();
		return $this->groups[$row['gruppe']]->caption;
	}


	function stopAllMembershipsForm($member){

		if (!$this->allow_editmemberships) return $this->permissionDenied();

		$url_post = $this->generateURL(array("noprint=true","membershipsave=stoppalle"));
		$url_back = $this->generateURL(array("editmemberships"));
		$m = $this->members[$member];
		
		return '
			<h3>'.$m->fullname.': Medlemskap</h3>
			<form method="post" action="'.$url_post.'">
				Er du sikker på at du vil avslutte alle medlemskap for '.$m->fullname.'?<br /><br />

				Uten noen aktive medlemskap vil ikke '.$m->fullname.' kunne logge inn på troppsportalen. 
				Om du vil opprettholde denne muligheten, kan du isteden flytte medlemmet til gruppen 
				"Pensjonerte speidere".<br /><br />

				<input type="submit" value="    Ja    " /> 
				<input type="button" value="    Nei    " onclick="window.location=\''.$url_back.'\'" />
			</form>
		';
	}

	function stopAllMemberships($user_id){

		if (!$this->allow_editmemberships) return $this->permissionDenied();

		$m = $this->members[$user_id];

		// Stopp alle patruljeverv (peff, ass) hvis eksisterende
		$vervObj = $this->vervredigeringInstance();
		$id = $vervObj->stoppAllePatruljeVerv($user_id);
		unset($vervObj);

		// Stopp alle eksisterende medlemskap
		foreach ($m->memberof as $i){
			$this->stopMembership(0, $user_id, $i);
		}
		$this->reloadMemberlist();

		// Tilbakestill rang og rettigheter til default
		$this->resetRights($user_id, true);
		
		// Frigjør brukernavn
		$this->releaseUsernameDo(false,true);

		$this->addToActivityLog("avsluttet alle medlemskap og frigjorde brukernavnet til ".$this->makeMemberLink($user_id).".");

		$this->redirect($this->generateUrl("edituser",true),"Alle medlemskap stoppet og brukernavn frigjort for dette medlemmet.");
		
	}

	function deleteMembershipForm($member,$id){

		if (!$this->allow_editmemberships) return $this->permissionDenied();

		if (!is_numeric($id)) $this->fatalError("Invalid input 9.1");
		$url_post = $this->generateURL(array("noprint=true","membershipsave=slett"));
		$url_back = $this->generateURL(array("editmemberships"));
		
		if (!$this->validateMembershipRelation($member,$id)){ 
			return $this->notSoFatalError("Medlemskapet eksisterer ikke, eller det tilhører en annen bruker enn gitt."); 
		}

		return '
			<h3>'.$this->members[$member]->fullname.': Medlemskap</h3>
			<form method="post" action="'.$url_post.'">
				<input type="hidden" name="membership_id" value="'.$id.'" />
				Er du sikker på at du vil SLETTE medlemskapet i <strong>'.$this->groupCaptionFromMembership($id).'</strong>? 
				Om du kun ønsker å avslutte et medlemskap, velger du isteden "Avslutte medlemskap". 
				Slette brukes kun hvis et medlemskap er lagt inn ved en feil.<br /><br />
				
				<input type="submit" value="     Ja      " /> 
				<input type="button" value="     Nei      " onclick="window.location=\'$url_back\'" />
			</form>
		';
	}

	function deleteMembershipFromPOST(){

		if (!$this->allow_editmemberships) return $this->permissionDenied();

		$id = $_POST['membership_id'];
		if (!is_numeric($id)) $this->fatalError("Invalid input 2.1");
		if (!$this->validateMembershipRelation($this->current_medlem,$id)){ 
			return $this->notSoFatalError("Medlemskapet eksisterer ikke, eller det tilhører en annen bruker enn gitt."); 
		}
		$this->deleteMembership($id);
		$this->redirect($this->generateURL("editmemberships",true));
	}

	function deleteMembership($id){

		if (!$this->allow_editmemberships) return $this->permissionDenied();
		
		// Sjekk at vi har fått en gyldig id, og at medlemskapet tilhører brukeren vi har klarert redigering for
		$res = $this->query("SELECT bruker,gruppe FROM $this->table_group_memberships WHERE id='$id'");
		if ($res->num_rows != 1) $this->fatalError("Ugyldig inn-data 7.1!");
		$row = $res->fetch_assoc();
		if ($row['bruker'] != $this->current_medlem) $this->fatalError("Ugyldig inn-data 7.2!");
		
		$gruppenavn = $this->groupCaptionFromMembership($id);
		$medlem = $this->members[$this->current_medlem]->fullname;

		// Stopp alle patruljeverv i gruppen (peff, ass) hvis eksisterende
		$vervObj = $this->vervredigeringInstance();
		$vervObj->stoppAllePatruljeVerv($this->current_medlem,$row['gruppe']);
		unset($vervObj);

		$this->query("DELETE FROM $this->table_group_memberships WHERE id='$id'");
		$this->addToActivityLog("slettet medlemskap i $gruppenavn for ".$this->makeMemberLink($medlem).".");
	}

	function moveUser(){
		
		if (!$this->allow_addcurrentmembership) return $this->permissionDenied();

		$user_id = $this->current_medlem;

		if (!isset($_POST['nygruppe'])){ $this->fatalError("Ingen gruppe spesifisert!"); }
		if (!$this->isGroup($_POST['nygruppe'])){ $this->fatalError("Gruppen eksisterer ikke!"); }
		$ny_gruppe = $_POST['nygruppe'];

		$url_post = $this->generateURL('action=moveUserDo');
		$url_back = $this->generateURL('action=userOverview'); 

		return '
			<h3>'.$this->members[$user_id]->fullname.': Medlemskap</h3>
			<form method="post" action="'.$url_post.'">
				<input type="hidden" name="nygruppe" value="'.$ny_gruppe.'" />
				Er du sikker på at du vil flytte <strong>'.$this->members[$user_id]->fullname.'</strong> til 
				<strong>'.$this->groups[$ny_gruppe]->caption.'</strong>?<br /> 
				Nåværende medlemskap i <strong>'.$this->listMemberships($user_id).'</strong> blir avsluttet.<br /><br />
				
				<input type="submit" value="     Ja      " /> 
				<input type="button" value="     Nei      " onclick=\'window.location="'.$url_back.'"\' /> 
			</form>
		';
	
	}
	
	function moveUserDo(){

		if (!$this->allow_addcurrentmembership) return $this->permissionDenied();

		$user_id = $this->current_medlem;

		if (!$this->isGroup($_POST['nygruppe'])){ $this->fatalError("Gruppen eksisterer ikke!"); }
		$ny_gruppe = $_POST['nygruppe'];
		$grpcaption = $this->groups[$ny_gruppe]->caption;
		
		$m = $this->members[$user_id];	
		$memofstr = array();
		foreach ($m->memberof as $i){
			$memofstr[] = $this->groups[$i]->caption;
			$this->stopMembership(0, $m->ident, $i);
		}
		$memofstr = implode(", ",$memofstr);
		$this->startMembership($m->ident,$ny_gruppe);
		$this->reloadMemberlist();
		$this->resetRights($m->ident, true);
		$this->resetMemberStatus($m->ident, true);
		
		$this->addToActivityLog("flyttet ".$this->makeMemberLink($user_id)." fra ".$memofstr." til ".$grpcaption);

		$this->redirect($this->generateURL('action=userOverview'),"Medlemmet ble flyttet til $grpcaption!");
	}

	
	/* 
	############################################################################################################################

		AJAX MEMBERSHIP EDITING:
		
			makeNewMemberShipForm($current)
			makeNewMembership($current)
			makeEditMembershipForm($id)
			makeEditMembership($id)
			makeStopMembershipForm($id);
			makeStopMembership($id);
			makeDeleteMembershipForm($id);
			makeDeleteMembership($id);
			printMembershipsList();
			makeMembershipsList($current, $action = "", $mid = -1);
			membershipOverview();

	############################################################################################################################
	*/
	
	function makeNewMemberShipForm($current) {
		$member = $this->current_medlem;
		$grpList = "<select name='ny_gruppe' id='ny_gruppe'>\n";
		foreach ($this->groups as $grp){		
			$add = true;
			if (($current) && (in_array($grp->id,$this->members[$member]->memberof))) $add = false;
			if ($add) {
				$grpList .= "<option value='".$grp->id."' />$grp->caption</option>\n";
			}
		}
		$grpList .= "</select>\n";
		
		$post_js = "nytt_medlemsskap($current); return false;";
		$cancel_js = "avbryt_endringer($current); return false;";
			
		return "
			<li>
					".($current ? "Nytt" : "Tidligere")." medlemskap i $grpList <br />
					<input type=\"button\" value=\"Avbryt\" onclick=\"$cancel_js\" /> 
					<input type=\"button\" value=\"Ok\" onclick=\"$post_js\" />
			</li>
		";
	}
	
	function makeNewMembership($current) {
		$member = $this->current_medlem;
		$gruppe = $_POST['ny_gruppe'];
		if (!is_numeric($gruppe)) $this->fatalError("Invalid input 6.1");
		if (!$this->isGroup($gruppe)) $this->fatalError("Invalid input 6.2");
		$g = $this->groups[$gruppe];

		if ($current) {
			if (!$this->allow_addcurrentmembership){
				$this->permissionDenied();
				exit();
			}
			$id = $this->startMembership($member, $gruppe);
		} else {
			$id = $this->startMembership($member, $gruppe, true);
			$this->stopMembership($id, 0, 0, true);	
		}
		return $id;
	}
	
	function makeEditMembershipForm($id) {
		$member = $this->current_medlem;
		if (!$this->validateMembershipRelation($member,$id)){ 
			return array('htmlcode' => $this->notSoFatalError("Medlemskapet eksisterer ikke, eller det tilhører en annen bruker enn gitt.")); 
		}
		
		$details = $this->fetchMembershipDetails($id);
		$current = empty($details['membershipend']) ? 1 : 0;
		
		$post_js = "endre_medlemsskap($id,$current); return false;";
		$cancel_js = "avbryt_endringer($current); return false;";
		
		$toReturn = array();
		
		
		if ($current) {
			
			$str = "Medlem av <strong>".$details['groupcaption']."</strong> siden ".
				$this->makeDateField("innmeldingsdato",$details['membershipstart']);
			
			$toReturn['innmelding'] = $details['membershipstart'];
			
		} else {
		
			$str = "Medlem av <strong>".$details['groupcaption']."</strong>
				fra ".$this->makeDateField("innmeldingsdato",$details['membershipstart'])."
				til ".$this->makeDateField("utmeldingsdato",$details['membershipend']).".
			";

			$toReturn['innmelding'] = $details['membershipstart'];		
			$toReturn['utmelding'] = $details['membershipend'];
		
		}
		// <form method=\"post\" onsubmit=\"$post_js\"></form>
		$toReturn['htmlcode'] = "
			<li>
				$str<br />
				<input type='button' value='Avbryt' onclick=\"$cancel_js\" /> 
				<input type='button' value='Ok' onclick=\"$post_js\" />
			</li>
		";
		
		return $toReturn;
	}

	function makeEditMembership($id){

		if (!$this->allow_editmemberships) return $this->permissionDenied();
		
		
		if (!is_numeric($id)) $this->fatalError("Invalid input 1.1");
		if (!$this->validateMembershipRelation($this->current_medlem,$id)){ 
			return $this->notSoFatalError("Medlemskapet eksisterer ikke, eller det tilhører en annen bruker enn gitt."); 
		}
		$details = $this->fetchMembershipDetails($id);
		$current = empty($details['membershipend']);
		
		$dmy = array($_POST['innmeldingsdato_day'],$_POST['innmeldingsdato_month'],$_POST['innmeldingsdato_year']);
		$fra = $dmy[2].'-'.$dmy[1].'-'.$dmy[0];
		$fra_unix = strtotime($fra);
		if ($fra_unix >= time()){
			$this->fatalError("Medlemskapet kan ikke begynne i fremtid!");
		}
		
		if (isset($_POST['utmeldingsdato_day'])) {

			$dmy = array($_POST['utmeldingsdato_day'],$_POST['utmeldingsdato_month'],$_POST['utmeldingsdato_year']);

			$til = $dmy[2].'-'.$dmy[1].'-'.$dmy[0];
			$til_unix = strtotime($til);
			if ($til_unix >= time()){
				$this->fatalError("Medlemskapet kan ikke slutte i fremtid!");
			}
			if ($til_unix < $fra_unix){
				$this->fatalError("Medlemskapet kan ikke slutte før det begynner!");
			}
			
			$this->query("UPDATE $this->table_group_memberships SET startdate='$fra', enddate='$til' WHERE id='$id'");
		
		} else {
		
			$this->query("UPDATE $this->table_group_memberships SET startdate='$fra' WHERE id='$id'");
			
		}
	}
	
	function makeStopMembershipForm($id) {

		$member = $this->current_medlem;
		if (!$this->validateMembershipRelation($member,$id)){ 
			return $this->notSoFatalError("Medlemskapet eksisterer ikke, eller det tilhører en annen bruker enn gitt."); 
		}

		$details = $this->fetchMembershipDetails($id);
		$current = empty($details['membershipend']) ? 1 : 0;
		
		$post_js = "stopp_medlemsskap($id,$current); return false;";
		$cancel_js = "avbryt_endringer($current); return false;";
		
		if ((count($this->members[$member]->memberof) <= 1) && ($member = $this->login_identifier)) {
			return '
				<li>
						Beklager, du kan ikke stoppe ditt eneste aktive medlemskap!
						<input type="button" value="Avbryt" onclick="'.$cancel_js.'" /> 
				</li>
			';	
		
		} else {
			return '
				<li>
						Er du sikker på at du vil stoppe det nåværende medlemskapet i <strong>'.$details['groupcaption'].'</strong>?<br />
						<input type="button" value="Avbryt" onclick="'.$cancel_js.'" /> 
						<input type="button" value="Stopp medlemskapet" onclick="'.$post_js.'" />
				</li>
			';	
		}
	}
	
	function makeStopMembership($id) {

		$medlem = $this->current_medlem;
		
		$this->stopMembership($id);

		/*if ($id == -1){
			$this->query("UPDATE $this->table_group_memberships SET enddate=CURDATE() WHERE bruker=$medlem AND til=0");
			$this->addToActivityLog("avsluttet alle medlemskap for ".$this->members[$medlem]->fullname.".");
		} else {
			if (!is_numeric($id)) $this->fatalError("membershipid not int");
			$res = $this->query("SELECT bruker,gruppe,enddate FROM $this->table_group_memberships WHERE id='$id'");
			if ($res->num_rows != 1){ $this->fatalError("Fant ikke medlemskapet!"); }
			$row = $res->fetch_assoc();
			$medlem = $row['bruker'];
			$gruppe = $this->groups[$row['gruppe']]->caption;
			if ($row['enddate'] != '0000-00-00') {
				$this->fatalError("Medlemskapet i $gruppe er allerede avsluttet.");
			}
			$this->query("UPDATE $this->table_group_memberships SET enddate=CURDATE() WHERE id=$id");
			$this->addToActivityLog("avsluttet medlemskap i $gruppe for ".$this->members[$medlem]->fullname.".");
		}
		
		$isLeader = $this->isGroupLeader($medlem);
		if ($isLeader != 0){
			if (isset($gruppe)) {
				if ($isLeader['group'] == $gruppe){
					$vervObj = $this->vervredigeringInstance();
					if ($isLeader['tittel'] == "peff"){
						$vervObj->stoppPeffVerv($medlem, $gruppe);
					} else if ($isLeader['tittel'] == "ass"){
						$vervObj->stoppAssVerv($medlem, $gruppe);
					}
				}
			} else {
				$vervObj = $this->vervredigeringInstance();
				$vervObj->stoppAllePatruljeVerv($medlem);
			}
		}*/
		
	}
	
	function makeDeleteMembershipForm($id) {
	
		$details = $this->fetchMembershipDetails($id);
		$current = empty($details['membershipend']) ? 1 : 0;
		
		$post_js = "slett_medlemsskap($id,$current); return false;";
		$cancel_js = "avbryt_endringer($current); return false;";

		if (empty($details['membershipend'])) 
			$this->fatalError("Du kan ikke slette nåværende medlemskap");
		
		return "	
			<li>
					Er du sikker på at du vil SLETTE dette tidligere medlemskap i <strong>".$details['groupcaption']."</strong> fra historien?<br />
					<input type='button' value='Avbryt' onclick=\"$cancel_js\" /> 
					<input type='button' value='Slett' onclick=\"$post_js\" />
			</li>
		";		
		
	}
	
	function makeDeleteMembership($id) {

		// Sjekk at vi har fått en gyldig id, og at medlemskapet tilhører brukeren vi har klarert redigering for
		$res = $this->query("SELECT bruker,gruppe,enddate FROM $this->table_group_memberships WHERE id='$id'");
		if ($res->num_rows != 1) $this->fatalError("Ugyldig inn-data 7.1!");
		$row = $res->fetch_assoc();
		if ($row['bruker'] != $this->current_medlem) $this->fatalError("Ugyldig inn-data 7.2!");
		if ($row['enddate'] == '0000-00-00') $this->fatalError("Du kan ikke slette nåværende medlemskap");
		
		$gruppe = $this->groups[$row['gruppe']]->caption;
		$medlem = $this->members[$this->current_medlem]->fullname;

		$this->query("DELETE FROM $this->table_group_memberships WHERE id='$id'");
		$this->addToActivityLog("slettet medlemskap i $gruppe for $medlem.");
		
	}
	
	function ajaxEditMemberships() {
		$member = $this->current_medlem;
		$a = "";
		$id = -1;
		
		if (isset($_POST['medlemsskap']) && is_numeric($_POST['medlemsskap'])) {
			$id = $_POST['medlemsskap'];
			$res = $this->query("SELECT enddate FROM $this->table_group_memberships WHERE id=$id");
			if ($res->num_rows != 1) $this->fatalError("invalid input .98");
			$row = $res->fetch_assoc();
			$current = ($row['enddate'] == '0000-00-00');
		} else {
			
		}
		if (isset($_POST['m_action'])) {
			switch($_POST['m_action']) {

				case 'nyttgammelt_medlemsskap_query':
					$a = 'nyttgammelt';
					break;

				case 'nytt_medlemsskap_query':
					$a = 'nytt';
					break;
				case 'nyttgammelt_medlemsskap':
					$a = "endre";
					$id = $this->makeNewMembership(false);
					break;
				case 'nytt_medlemsskap':
					$a = "endre";
					$id = $this->makeNewMembership(true);
					break;

				case 'endre_medlemsskap_query':
					$a = 'endre';
					break;
				case 'endre_medlemsskap':
					$this->makeEditMembership($id);
					break;

				case 'stopp_medlemsskap_query':
					$a = 'stopp';
					break;
				case 'stopp_medlemsskap':
					$this->makeStopMembership($id);
					break;

				case 'slett_medlemsskap_query':
					$a = 'slett';
					break;
				case 'slett_medlemsskap':
					$this->makeDeleteMembership($id);
					break;

				default:
					$this->fatalError("Ukjent handling");
			}
		}
		header("Content-Type: text/html; charset=utf-8"); 
		print json_encode($this->makeMembershipsList($a, $id));
		exit();
	}
	
	function makeMembershipsList($action = "", $mid = -1) {

		$member = $this->current_medlem;

		$returnArray = array();
		$toReturn = '
			<p><strong>Nåværende medlemskap</strong></p>
			<div id="current_memberships">
				<ul class="membershipedit">
			';

		$res = $this->query("SELECT 
				$this->table_groups.caption, 
				$this->table_group_memberships.id,
				$this->table_group_memberships.startdate, 
				$this->table_group_memberships.enddate
			FROM 
				$this->table_group_memberships, $this->table_groups 
			WHERE
				$this->table_group_memberships.bruker='$member'
				AND $this->table_group_memberships.gruppe=$this->table_groups.id 
				AND $this->table_group_memberships.enddate='0000-00-00'
			ORDER BY 
				$this->table_group_memberships.startdate"
		);
		
		while ($row = $res->fetch_assoc()){		
			$id = $row['id'];
			if ($action == 'endre' && $id == $mid) {
				$tmp = $this->makeEditMembershipForm($id);
				$toReturn .= $tmp['htmlcode'];
				$returnArray['innmelding'] = array(
					'day' => intval(strftime('%e', $tmp['innmelding'])),
					'month' => intval(strftime('%m', $tmp['innmelding'])),
					'year' => intval(strftime('%Y', $tmp['innmelding']))
				);
			} else if ($action == 'stopp' && $id == $mid) {
				$toReturn .= $this->makeStopMembershipForm($id);				
			} else if ($action == 'slett' && $id == $mid) {
				$toReturn .= $this->makeDeleteMembershipForm($id);
			} else {			
				$toReturn .= "
					<li>
						Medlem av ".$row['caption']." siden ".date("d.m.Y",strtotime($row['startdate'])).".
						<div style='font-size:8pt;'>
							<a href='#' onclick='endre_medlemsskap_query($id,true); return false;'>Endre innmeldingsdato</a> |
							<a href='#' onclick='stopp_medlemsskap_query($id,true); return false;'>Avslutt medlemskap</a>
						</div>
					</li>
				";
			}
		}

		if ($action == 'nytt') {
			$toReturn .= $this->makeNewMemberShipForm(true);			
		}
		
		$toReturn .= '
			</ul>
			</div>
			<p><strong>Tidligere medlemskap</strong></p>
			<div id="previous_memberships">
				<ul class="membershipedit">
		';

		$res = $this->query("SELECT 
				$this->table_groups.caption, 
				$this->table_group_memberships.id,
				$this->table_group_memberships.startdate, 
				$this->table_group_memberships.enddate
			FROM 
				$this->table_group_memberships, $this->table_groups 
			WHERE
				$this->table_group_memberships.bruker='$member'
				AND $this->table_group_memberships.gruppe=$this->table_groups.id 
				AND $this->table_group_memberships.enddate!='0000-00-00' 
			ORDER BY 
				$this->table_group_memberships.startdate"
		);
		
		while ($row = $res->fetch_assoc()){		
			$id = $row['id'];
			if ($action == 'endre' && $id == $mid) {
				$tmp = $this->makeEditMembershipForm($id);
				$toReturn .= $tmp['htmlcode'];
				$returnArray['innmelding'] = array(
					'day' => intval(strftime('%e', $tmp['innmelding'])),
					'month' => intval(strftime('%m', $tmp['innmelding'])),
					'year' => intval(strftime('%Y', $tmp['innmelding']))
				);
				$returnArray['utmelding'] = array(
					'day' => intval(strftime('%e', $tmp['utmelding'])),
					'month' => intval(strftime('%m', $tmp['utmelding'])),
					'year' => intval(strftime('%Y', $tmp['utmelding']))
				);
			} else if ($action == 'stopp' && $id == $mid) {
				$toReturn .= $this->makeStopMembershipForm($id);				
			} else if ($action == 'slett' && $id == $mid) {
				$toReturn .= $this->makeDeleteMembershipForm($id);
			} else {			
				$toReturn .= "
					<li>
						Medlem av ".$row['caption']." fra ".date("d.m.Y",strtotime($row['startdate']))." til ".date("d.m.Y",strtotime($row['enddate'])).".
						<div style='font-size:8pt;'>
							<a href='#' onclick='endre_medlemsskap_query($id,false); return false;'>Endre inn-/utmeldingsdato</a> |
							<a href='#' onclick='slett_medlemsskap_query($id,false); return false;'>Slett medlemskap</a>
						</div>
					</li>
				";
			}
		}

		if ($action == 'nyttgammelt') {
			$toReturn .= $this->makeNewMemberShipForm(false);			
		}
		
		$toReturn .= '
			</ul>
			</div>
		';

		$returnArray['htmlcode'] = $toReturn;
		return $returnArray;
	}

	function membershipOverview(){
	
		$member = $this->current_medlem;

		if (!$this->allow_editmemberships) return $this->permissionDenied();

		$toReturn = '<a href="#" onclick="nytt_medlemsskap_query(false); return false;">Legg til tidligere medlemskap</a>';
		if ($this->allow_addcurrentmembership){
			$toReturn .= " | <a href='#' onclick='nytt_medlemsskap_query(true); return false;'>Legg til nåværende medlemskap</a>\n";
		}
		$toReturn .= '<span id="ajaxIndicator" style="float:right;padding:5px;visibility:hidden;"><img src="/images/indicators/indicator8.gif" alt="Working" /></span>';
		
		$mList = $this->makeMembershipsList();
		$mList = $mList['htmlcode'];
		$toReturn .= '<div id="membership_list">'.$mList.'</div>';
		
		$url = $this->generateURL('action=ajaxEditMemberships',true);
		$max_date_js = strftime('%m/%d/%Y',time());
		$toReturn .= '
			<script type="text/javascript">
		    //<![CDATA[	
				
				
				function vent_litt(current) {
					var target = "membership_list";
					//$(target).innerHTML = \'<ul><li>Et øyeblikk...<br /><img src="'.$this->image_dir.'progressbar1.gif" alt="Progressbar" style="width:100px; height: 9px;" /></li></ul>\';
				}
				
				function json_membershiplist_update(pars) {
					$("ajaxIndicator").style.visibility = "visible";
					var url = "'.$url.'";
					YAHOO.util.Connect.asyncRequest("POST",url, { success: function(o){	
						try {
							$("ajaxIndicator").style.visibility = "hidden";
							var json = YAHOO.lang.JSON.parse(o.responseText);
							console.log(json);
							$("membership_list").innerHTML = json.htmlcode;
							
							if (json.innmelding) {
								(new BG18.datePicker("innmeldingsdato", { selectedDate: json.innmelding, maxDate: "'.$max_date_js.'" })).init();
							}
							if (json.utmelding) {
								(new BG18.datePicker("utmeldingsdato", { selectedDate: json.utmelding, maxDate: "'.$max_date_js.'" })).init();
							}
							
						} catch (x) {
							alert("JSON Parse failed!");
							$("membership_list").innerHTML = o.responseText;
						}
					}}, pars);
				}
				
				function avbryt_endringer(current) {
					json_membershiplist_update("");
				}
				
				function endre_medlemsskap_query(medlemsskap, current) {
					json_membershiplist_update("m_action=endre_medlemsskap_query&medlemsskap="+medlemsskap);
				}

				function endre_medlemsskap(medlemsskap, current) {
					var pars = "m_action=endre_medlemsskap&medlemsskap="+medlemsskap;					
					pars += "&innmeldingsdato_day="+$("innmeldingsdato_day").value+"&innmeldingsdato_month="+$("innmeldingsdato_month").value+"&innmeldingsdato_year="+$("innmeldingsdato_year").value;
					if (!current) pars += pars += "&utmeldingsdato_day="+$("utmeldingsdato_day").value+"&utmeldingsdato_month="+$("utmeldingsdato_month").value+"&utmeldingsdato_year="+$("utmeldingsdato_year").value;
					json_membershiplist_update(pars);
				}
				
				function stopp_medlemsskap_query(medlemsskap, current) {
					json_membershiplist_update("m_action=stopp_medlemsskap_query&medlemsskap="+medlemsskap);
				}

				function stopp_medlemsskap(medlemsskap, current) {
					json_membershiplist_update("m_action=stopp_medlemsskap&medlemsskap="+medlemsskap);
				}
				
				function slett_medlemsskap_query(medlemsskap, current) {
					json_membershiplist_update("m_action=slett_medlemsskap_query&medlemsskap="+medlemsskap);
				}
				function slett_medlemsskap(medlemsskap, current) {
					json_membershiplist_update("m_action=slett_medlemsskap&medlemsskap="+medlemsskap);
				}
				
				function nytt_medlemsskap_query(current) {
					if (current) 
						json_membershiplist_update("m_action=nytt_medlemsskap_query");
					else
						json_membershiplist_update("m_action=nyttgammelt_medlemsskap_query");
				}
								
				function nytt_medlemsskap(current) {
					if (current) 
						json_membershiplist_update("m_action=nytt_medlemsskap&"+Form.Element.serialize("ny_gruppe"));
					else
						json_membershiplist_update("m_action=nyttgammelt_medlemsskap&"+Form.Element.serialize("ny_gruppe"));
				}
				
			//]]>
			</script>
		';

		return array(
			'html' => $toReturn
		);
		
	}
	
	/* 
	############################################################################################################################

		METHODS WITH NO USER INTERACTION

	############################################################################################################################
	*/

	function addMember($firstname, $middlename, $lastname, $grupper) {
		global $login;

		if (!$this->allow_addmember) return $this->permissionDenied();
			 		
		if (is_array($grupper)) $first_grp = $grupper[0]; else $first_grp = $grupper;
		$def_rights = $this->groups[$first_grp]->defaultrights;
		$def_rang = $this->groups[$first_grp]->defaultrang;

		// Save data to database
		$this->query("
			INSERT INTO $this->table_memberlist 
				(firstname, middlename, lastname, rang, rights) 
			VALUES 
				(\"".addslashes($firstname)."\", \"".addslashes($middlename)."\", 
				\"".addslashes($lastname)."\", '$def_rang', '$def_rights')"
		);
		$user_id = $this->insert_id();
		$login->addUser($user_id);

		$this->reloadMemberlist();					// Last inn endringer

		$this->addToActivityLog("la til et nytt medlem: ".$this->makeMemberLink($user_id).".");

		$this->logEvent('USER_REGISTERED', $user_id);

		if (is_array($grupper)) {
			foreach ($grupper as $gruppe)
				$this->startMembership($user_id,$gruppe);		// Start gruppemedlemskap		
		} else {
			$this->startMembership($user_id,$grupper);		// Start gruppemedlemskap
		}
		
		$this->reloadMemberlist();					// Last inn endringer
		
		$this->resetMemberStatus($user_id, true);

		$this->reloadMemberlist();					// Last inn endringer
		
		return $user_id;
	}
		
	function updateProfile($ident, $data) {
	
		if (!$this->allow_editprofile) return $this->permissionDenied();
		
		if (!$this->isUser($ident)) return $this->notSoFatalError("Kunne ikke oppdatere profil. Brukeren $ident eksisterer ikke.");
	
		$query = "UPDATE $this->table_memberlist SET lastupdate='".time()."', profilecreated=1";
		
		if (isset($data["firstname"])) $query .= ', firstname="'.addslashes($data["firstname"]).'"';
		if (isset($data["middlename"])) $query .= ', middlename="'.addslashes($data["middlename"]).'"';
		if (isset($data["lastname"])) $query .= ', lastname="'.addslashes($data["lastname"]).'"';
		if (isset($data["nickname"])) $query .= ', nickname="'.addslashes($data["nickname"]).'"';
		if (isset($data["email"])) $query .= ', email="'.addslashes($data["email"]).'"';
		if (isset($data["birthday"])) $query .= ', bday="'.addslashes(strftime("%Y-%m-%d",$data["birthday"])).'"'; // birthday is deprecated
		if (isset($data["bday"])) $query .= ', bday="'.addslashes($data["bday"]).'"';
		if (isset($data["homepage"])) $query .= ', homepage="'.addslashes($data["homepage"]).'"';
		if (isset($data["street"])) $query .= ', street="'.addslashes($data["street"]).'"';
		if (isset($data["state"])) $query .= ', state="'.addslashes($data["state"]).'"';
		if (isset($data["country"])) $query .= ', country="'.addslashes($data["country"]).'"';
		if (isset($data["slug"])) $query .= ', slug="'.addslashes($data["slug"]).'"';
		if (isset($data["memberstatus"])) $query .= ', memberstatus="'.addslashes($data["memberstatus"]).'"';
		if (isset($data["address_id"])) $query .= ', address_id="'.addslashes($data["address_id"]).'"';
		if (isset($data["profilbilde"])) $query .= ', profilbilde="'.addslashes($data["profilbilde"]).'"';
		if (isset($data["forumbilde"])) $query .= ', forumbilde="'.addslashes($data["forumbilde"]).'"';
		
		if (isset($data['streetno']))
			$query .= empty($data['streetno']) ? ", streetno=''" : ", streetno='".addslashes($data['streetno'])."'";
		if (isset($data['postno']))
			$query .= empty($data['postno']) ? ", postno=''" : ", postno='".addslashes($data['postno'])."'";
		if (isset($data['city'])) $query .= ", city='".addslashes($data['city'])."'";
		if (isset($data['homephone']))
			$query .= empty($data['homephone']) ? ", homephone=''" : ", homephone='".addslashes($data['homephone'])."'";
		if (isset($data['cellular']))
			$query .= empty($data['cellular']) ? ", cellular=''" : ", cellular='".addslashes($data['cellular'])."'";
		
		$query .= " WHERE ident='$ident'";

		$this->query($query);
		
	}
	
	function updateProfileNotes($id, $notes) {
	
		$res = $this->query("SELECT id,lang 
			FROM $this->table_memberlistlocal 
			WHERE lang='$this->preferred_lang' AND id='$id'"
		);
		$notes = addslashes($notes);
		if ($res->num_rows == 1) {
			$this->query("UPDATE $this->table_memberlistlocal SET
				body=\"$notes\"
				WHERE id='$id' AND lang='$this->preferred_lang'"
			);
		} else {
			$this->query("INSERT INTO $this->table_memberlistlocal 
				(id,lang,body) 
				VALUES ('$id','$this->preferred_lang',\"$notes\")"
			);
		}
	}
	
	function deregisterGuardian($child, $guardian) {
		if (!$this->allow_editforesatte){
			$this->permissionDenied();
			return 0;
		}
		if (!$this->isUser($child)) $this->fatalError("User $child not found");
		if (!$this->isUser($guardian)) $this->fatalError("User $guardian not found");

		$foresatte = $this->foresatteTil($child);
		if (in_array($guardian,$foresatte)) {
			$this->query("DELETE FROM $this->table_guardians WHERE medlem='$child' AND foresatt='$guardian' LIMIT 1");	
			$this->addToActivityLog("Fjernet foresatt, ".$this->members[$guardian]->fullname.", for medlemmet ".$this->members[$child]->fullname);
			$this->logEvent('CHILD_REMOVED', $guardian, 0, $child);
			$this->logEvent('PARENT_REMOVED', $child, 0, $guardian);
		}
		
	}
	
	function registerGuardian($child, $guardian) {
		if (!$this->allow_editforesatte){
			$this->permissionDenied();
			return 0;
		}
		if (!$this->isUser($child)) return false;
		if (!$this->isUser($guardian)) return false;
		
		$memberofparentgroup = false;
		foreach ($this->members[$guardian]->memberof as $g) {
			if ($this->isParentGroup($g)) $memberofparentgroup = true;
		}
		if (!$memberofparentgroup) {
			$this->notSoFatalError("$guardian kunne ikke legges til som foresatt til $child fordi $guardian ikke er medlem av noen foreldregruppe!");
			return false;
		}

		$foresatte = $this->foresatteTil($child);
		if (in_array($guardian,$foresatte)) $this->fatalError("Personen du valgte er allerede registrert som foresatt.");
	
		$this->query("INSERT INTO $this->table_guardians (medlem,foresatt) VALUES ('$child','$guardian')");	

		// Activitylog
		$this->addToActivityLog("registrerte ".$this->makeMemberLink($guardian)." som foresatt til ".$this->makeMemberLink($child));
		$this->logEvent('CHILD_ADDED', $guardian, 0, $child);
		$this->logEvent('PARENT_ADDED', $child, 0, $guardian);

	}
	
	function isParentGroup($group) {
		return ($this->groups[$group]->kategori == "FO");
	}
	
	
		
}


?>
