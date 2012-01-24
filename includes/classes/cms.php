<?

class cms extends base {

	var $getvars = array("dynpage",
			"newpage","savenewpage",
			"errors","tittel","slug","class","mappe","tittel","slug",
			"current_image","newdir", "savenewdir", "newimg","savenewimg","cropimage","docropimage",
			"cms_replaceimage","cms_doreplaceimage","cms_editimagetitle","cms_saveimagetitle","cms_deleteimage","cms_dodeleteimage",
			"editpagesettings","savepagesettings",
			"pageaccess","savepageaccess",
			"renamepage","dorenamepage",
			"movepage","domovepage",
			"deletepage","dodeletepage",
			"validate_pagesetting",
			"fixthumbs","dofixthumbs"
	);
	
	var $page_title;
	var $pagenotfound = false;
	var $root_dir;
	
	var $allow_editpermissions = false;
	var $allow_uploadimages = false;

	var $allow_editownimages = false;
	var $allow_editothersimages = false;
	var $allow_deleteownimages = false;
	var $allow_deleteothersimages = false;

	var $table_rights = "rights";
	var $table_pages = "cms_pages";
	var $table_languages = "cms_languages";
	var $table_images = "images";
	var $table_changelog = "log_changes";
	
	var $table_useroptions = "cms_useroptions";
	var $table_pageoptions = "cms_pageoptions";
	var $table_classoptions = "cms_classoptions";
	var $table_globaloptions = "cms_globaloptions";
	
	var $table_pagepermissions = "cms_pagepermissions";
	var $table_classpermissions = "cms_classpermissions";
	
	var $table_classes = "cms_classes";
	var $table_classlabels = "cms_classlabels";
	var $table_pagelabels = "cms_pagelabels";
	
	
	/* Callbacks */

	var $lookup_group;
	var $lookup_member;
	var $lookup_webmaster;
	
	var $pageid;
	var $allow_edit = false;
	var $htmleditdir;
	var $login_identifier = 0;
	var $rightslabels;

	var $reserved_slugs = array(
		
	);
	
	var $errorMessages = array(
		'empty_title' => 'Du må fylle ut noe i tittelfeltet',
		'empty_slug' => 'Du må fylle ut noe i adressefeltet',
		'slug_contain_specials' => 'Adressefeltet inneholder "ulovlige" tegn. Kun a-z, 0-9, bindestrek og nedestrek er tillatt. Kun små bokstaver.',
		'slug_notunique' => 'Verdien i adressefeltet er ikke unik. Det finnes allerede en side med denne verdien. Finn på en ny.',
		'title_notunique' => 'Verdien i tittelfeltet er ikke unik. Det finnes allerede en side med denne verdien. Finn på en ny.',
		'slug_reserved' => 'Verdien i adressefeltet er reservert for troppsportalen og kan ikke benyttes. Finn på en ny.',
		'invalid_folder' => 'Verdien i mappefeltet er ugyldig.',
		'permission_denied' => 'Du har ikke tilgang til å flytte siden til denne mappen. Velg en annen mappe.'
	);

	var $current_dir;
	var $current_path;
	
	var $template_filelisting = "
			<div class='filelisting'>
				%files%
			</div>
		";
	var $template_filelisting_file = '	
				<div class="file%classno%" style="background-image: url(%icon%)"%extras%" >
					<a href="%url%" class="title">%name%</a> <em>(%class%)</em>
					<div class="options" id="options_%id%">%options%</div>
				</div>
		';
	var $template_filelisting_dir = '	
				<div class="dir%classno%" style="background-image: url(%icon%)"%extras%">
					<a href="%url%" class="title">%name%</a>
					<div class="options" id="options_%id%">%options%</div>
				</div>
		';
	
	var $template_newpage_form = '
	
			<script type="text/javascript"><!--
				function generateSlug(){
					tittel = getObject("dyn_tittel").value;
					//alert(tittel);
					tittel = tittel.toLowerCase();
			        re = /\$|,|@|#|~|`|\%|\*|\^|\&|\(|\)|\+|\=|\[|\]|\[|\}|\{|\;|\:|\'|\"|\<|\>|\?|\||\\|\!|\$|\./g;
					tittel = tittel.replace(re, "");
					tittel = tittel.replace(/ø/g, "o");
					tittel = tittel.replace(/å/g, "a");
					tittel = tittel.replace(/æ/g, "ae");
					tittel = tittel.replace(/ /g, "-");
					getObject("dyn_addresse").value = tittel;
				}

			//--></script>
			
			<h2>Opprett ny side</h2>
			%errormessages%
			<form method="post" action="%posturl%">
				<table>
					<tr>
						<td>Tittel: </td>
						<td><input type="text" name="dyn_tittel" id="dyn_tittel" value="%title%" style="width: 300px;" /></td>
					</tr><tr>
						<td>Adresse: </td>
						<td><input type="text" name="dyn_addresse" id="dyn_addresse" value="%slug%" style="width: 300px;" /> <input type="button" value="Lag fra tittel" onclick="generateSlug();" /></td>
					</tr><tr>
						<td>Type: </td>
						<td>%class%</td>
					</tr>
				</table>
				<br />
				<input type="button" value="Avbryt" onclick=\"window.location="%backurl%"\" /> <input type="submit" value="Lagre" />
			</form>
			<h2>Tips og hjelp</h2>
			<ul>
				<li>I feltet <i>adresse</i> fyller du inn sidens unike navn, slik den vil bli representert i adresselinjen i nettleseren.
				Fyller du f.eks. inn \"min-egen-side\", blir sidens adresse \"http://%%/min-egen-side\". Kun tegnene a-z (små bokstaver), 0-9, bindestrek (-) og nedestrek (_) tillates. Knappen \"Lag fra tittel\" vil i mange tilfeller gi et bra, men ikke alltid (f.eks. om tittelen inneholder mange spesialtegn)<br />&nbsp;</li>
				<li>I feltet <i>format</i> velger du hva slags redigeringsplattform du ønsker. HTML er standardvalget, som gir deg en redigeringsplattform som prøver å minne om en vanlig tekstbehandler. Om du er usikker, velger du dette. Merk at du også har muligheten til å skrive egen html-kode om du behersker dette. Velger du BBCode, kan du bruke den samme redigerinssyntaksen som du bruker på f.eks. forum, nyheter, etc. Denne forklares på hjelpesidene.</li>
			</ul>
		';
		
	var $template_deletepage_form = '
			<h2>%page_header%</h2>
			<form action="%posturl%" method="post">
				<p>
					%paragraph%
				</p>
				<p>
					<input type="button" value="    Nei    " onclick="window.location=\'%url_no%\'" /> 
					<input type="submit" value="    Ja    " />
				</p>
			</form>
		';
		
	var $webmaster_ident = -1;
	
	function cms() {
		$this->table_rights = DBPREFIX.$this->table_rights;
		$this->table_pages = DBPREFIX.$this->table_pages;
		$this->table_images = DBPREFIX.$this->table_images;
		$this->table_changelog = DBPREFIX.$this->table_changelog;
		$this->table_classes = DBPREFIX.$this->table_classes;
		$this->table_languages = DBPREFIX.$this->table_languages;
		
		$this->table_useroptions = DBPREFIX.$this->table_useroptions;
		$this->table_pageoptions = DBPREFIX.$this->table_pageoptions;
		$this->table_classoptions = DBPREFIX.$this->table_classoptions;
		$this->table_globaloptions = DBPREFIX.$this->table_globaloptions;
		
		$this->table_pagepermissions = DBPREFIX.$this->table_pagepermissions;
		$this->table_classpermissions = DBPREFIX.$this->table_classpermissions;
		
		$this->table_classlabels = DBPREFIX.$this->table_classlabels;
		$this->table_pagelabels = DBPREFIX.$this->table_pagelabels;
	}
	
	function initialize($silent = true){

		
		$this->action = "";
		
		$wLookup = $this->lookup_webmaster;
		$this->webmaster_ident = $wLookup();
		$this->webmaster_ident = $this->webmaster_ident->ident;
		
		/*
		print $this->coolUrlPrefix;
		print "<br />";
		print $_SERVER['SCRIPT_URL'];
		print "<br />";
		print strpos($_SERVER['SCRIPT_URL'],$this->coolUrlPrefix);
		*/


		if (!strpos($_SERVER['REQUEST_URI'],$this->coolUrlPrefix)){
			$this->action = 'viewpage';
			$tmp = $this->coolUrlPrefix;
			$this->coolUrlPrefix = "";
			$this->initialize_base();
			$this->coolUrlPrefix = $tmp;
		} else {
			$this->initialize_base();
		}

		if (!isset($_GET['noprint'])){
			// DEBUG: print_r($this->coolUrlSplitted);
		}
		
		$this->breadcrumb = array('<a href="'.$this->generateCoolUrl('/').'"><b>Hjem</b></a>');

		$this->current_dir = $this->root_dir; // rot-mappen
		$this->current_path = "";
		if (count($this->coolUrlSplitted) > 0){
			foreach ($this->coolUrlSplitted as $s){
				$res = $this->query("SELECT 
						$this->table_pages.id, 
						$this->table_classes.shortname,
						$this->table_pagelabels.value as header
					FROM 
						($this->table_classes,
						$this->table_pages)
					LEFT JOIN
						$this->table_pagelabels
					ON 
						$this->table_pagelabels.page=$this->table_pages.id
						AND $this->table_pagelabels.label='page_header'
						AND $this->table_pagelabels.lang='$this->preferred_lang'
					WHERE 
						$this->table_pages.pageslug='".addslashes($s)."'
							AND 
						$this->table_pages.parent='$this->current_dir'
							AND
						$this->table_pages.class=$this->table_classes.id"
				);
				if ($res->num_rows != 1){
					$this->page_title = "Siden finnes ikke";
					$this->pagenotfound = true;
					return;
				}
				$row = $res->fetch_assoc();
				$this->current_path .= "/$s";
				$this->current_dir = $row['id'];
				$header = stripslashes($row['header']);
				if (empty($header)) $header = "Side uten navn";
				if ($row['shortname'] == 'dir'){
					$this->breadcrumb[] = '<a href="'.$this->generateCoolUrl($this->current_path.'/').'">'.$header.'</a>';
				} else {
					$this->breadcrumb[] = '<a href="'.$this->generateCoolUrl($this->current_path.'/').'">'.$header.'</a>';
				}
				if ($row['shortname'] != "dir") break;
			}
		}
				
		$this->page_title = "Innholdssystem";
		
		if (isset($_GET['current_image']) && is_numeric($_GET['current_image'])){
			$this->current_image = $_GET['current_image']; 
		}
		
		if (isset($_FILES['bildefil']) && ($_FILES['bildefil']['error'] == '0')) {
			$this->action = 'savenewimg';
		}
		
		if (isset($_GET['cms_saveimagetitle'])){
			$this->action = 'saveimagetitle';
		} else if (isset($_GET['cms_editimagetitle'])){
			$this->action = 'editimagetitle';
		} else if (isset($_GET['cms_saveimagetitle'])){
			$this->action = 'saveimagetitle';
		} else if (isset($_GET['cms_replaceimage'])){
			$this->action = 'replaceimage';		
		} else if (isset($_GET['cms_doreplaceimage'])){
			$this->action = 'doreplaceimage';
		} else if (isset($_GET['cms_deleteimage'])){
			$this->action = 'deleteimage';	
		} else if (isset($_GET['cms_dodeleteimage'])){
			$this->action = 'dodeleteimage';	
					
		} else if (isset($_GET['newpage'])){ 
			$this->action = 'newpage';
		} else if (isset($_GET['savenewpage'])){ 
			$this->action = 'savenewpage';
			
		} else if (isset($_GET['newdir'])){ 
			$this->action = 'newdir';
		} else if (isset($_GET['savenewdir'])){ 
			$this->action = 'savenewdir';
			
		} else if (isset($_GET['newimg'])){ 
			$this->action = 'newimg';
		} else if (isset($_GET['savenewimg'])){ 
			$this->action = 'savenewimg';
		} else if (isset($_GET['cropimage'])) {
			$this->action = 'cropimage';
		} else if (isset($_GET['docropimage'])) {
			$this->action = 'docropimage';
		
		} else if (isset($_GET['editpagesettings'])){
			$this->action = 'editpagesettings';
		} else if (isset($_GET['savepagesettings'])){
			$this->action = 'savepagesettings';
			
		} else if (isset($_GET['pageaccess'])){
			$this->action = 'pageaccess';
		} else if (isset($_GET['savepageaccess'])){
			$this->action = 'savepageaccess';

		} else if (isset($_GET['renamepage'])){
			$this->action = 'renamepage';
		} else if (isset($_GET['dorenamepage'])){
			$this->action = 'dorenamepage';
			
		} else if (isset($_GET['movepage'])){
			$this->action = 'movepage';
		} else if (isset($_GET['domovepage'])){
			$this->action = 'domovepage';

		} else if (isset($_GET['deletepage'])){
			$this->action = 'deletepage';
		} else if (isset($_GET['dodeletepage'])){
			$this->action = 'dodeletepage';
		
		} else if (isset($_GET['validate_pagesetting'])) {
			$this->action = 'validate_pagesetting';

		} else if (isset($_GET['fixthumbs'])) {
			$this->action = 'fixthumbs';
		} else if (isset($_GET['dofixthumbs'])) {
			$this->action = 'dofixthumbs';
		}
		
	}

	function run(){
	
		$this->initialize();
		if ($this->pagenotfound){		
			$r1a = array();					$r2a = array();
			$r1a[1]  = "%imagedir%";		$r2a[1]  = $this->image_dir;
			$r1a[2]  = "%url%";				$r2a[2]  = htmlspecialchars($_SESSION['urlnotfound']);		
			return str_replace($r1a, $r2a, $this->_templatePageNotFound);
		}
		
		$output = "";
		
		if ((!isset($_GET['noprint'])) && ($this->action != 'viewpage')){
			$output .= "<p>".implode(" > ",$this->breadcrumb)."</p>";
		}

		if (!empty($this->current_image)){
			$this->initializeImagesInstance();
			switch ($this->action){
				case 'saveimagetitle':
					$output .= $this->saveImageTitle();
					break;
				case 'editimagetitle':
					$output .= $this->editImageTitleForm();
					break;
				case 'saveimagetitle':
					$output .= $this->saveImageTitle();
					break;
				case 'replaceimage':
					$output .= $this->replaceImageForm();
					break;
				case 'doreplaceimage':
					$output .= $this->saveReplacedImage();
					break;
				case 'deleteimage':
					$output .= $this->deleteImageForm();
					break;
				case 'dodeleteimage':
					$output .= $this->doDeleteImage();
					break;
				case 'cropimage':
					$output .= $this->cropImageForm();
					break;
				case 'docropimage':
					$output .= $this->cropImage();
					break;
				default:
					$output .= $this->imageDetails();
			}
		} else {
			switch ($this->action){
				case 'newpage':
					$output .= $this->addNewPageForm();
					break;
				case 'savenewpage':
					$output .= $this->addNewPage();
					break;
				case 'newdir':
					$output .= $this->addNewDirForm();
					break;
				case 'savenewdir':
					$output .= $this->addNewDir();
					break;
				case 'newimg':
					$this->initializeImagesInstance();
					$output .= $this->newImageForm();
					break;
				case 'savenewimg':
					$this->initializeImagesInstance();
					$output .= $this->saveNewImage();
					break;
				case 'viewpage':
					$output .= $this->printPage();
					break;
				case 'editpagesettings':
					$output .= $this->printEditPageSettingsForm();
					break;
				case 'savepagesettings':
					$output .= $this->savePageSettings();
					break;
				case 'pageaccess':
					$output .= $this->printPageAccessForm();
					break;
				case 'savepageaccess':
					$output .= $this->savePageAccess();
					break;
				case 'renamepage':
					$output .= $this->printRenamePageForm();
					break;
				case 'dorenamepage':
					$output .= $this->renamePage();
					break;
				case 'movepage':
					$output .= $this->printMovePageForm();
					break;
				case 'domovepage':
					$output .= $this->movePage();
					break;
				case 'deletepage':
					$output .= $this->printDeletePageForm();
					break;
				case 'dodeletepage':
					$output .= $this->deletePage();
					break;
				case 'fixthumbs':
					$output .= $this->fixThumbsForm();
					break;
				case 'dofixthumbs':
					$output .= $this->fixThumbs();
					break;
				case 'validate_pagesetting':
					$output .= $this->validatePageSetting();
					print $output;
					exit();
				default:
					$output .= $this->listDir();
			}		
		}
		return $output;
	}
	
	function getPath($id) {
	
		$current_dir = 1; // rot-mappen
		$sti = array();
		while ($id != '1') {
			$res = $this->query("SELECT 
						$this->table_pages.pageslug, 
						$this->table_pages.parent 
					FROM 
						$this->table_pages
					WHERE 
						$this->table_pages.id='$id'"
			);
			$row = $res->fetch_assoc();
			$sti[] = $row['pageslug'];
			$id = $row['parent'];
		}
		$sti = array_reverse($sti);
		$sti = implode("/",$sti);
		$sti = "/".trim($sti,"/")."/";
		if ($sti == "//") $sti = "/";
		return $sti;
		
	}
	
	function getPageTitle($page, $lang = -1) {
		if ($lang == -1) $lang = $this->preferred_lang;
		$res = $this->query("SELECT
				$this->table_pagelabels.value as label
			FROM 
				$this->table_pagelabels
			WHERE $this->table_pagelabels.page='$page'
			AND $this->table_pagelabels.lang = '$lang'"
		);
		if ($res->num_rows) {
			$row = $res->fetch_assoc();
			return $row['label'];	
		} else {
			return "Untitled";
		}
	}
	
	/* ############################################### PERMISSION SYSTEM ################################################################*/

	
	/* Examples:
		is_allowed($task, $page)						// $user defaults to login_identifier
		is_allowed($task, $page, $user)	
	*/
	function is_allowed($arg1,$arg2,$arg3 = "DEFAULT"){
		if ($arg3 == "DEFAULT") {
			return $this->is_allowed_2($arg1,$arg2,$this->login_identifier);
		} else {
			return $this->is_allowed_2($arg1,$arg2,$arg3);
		}
	}
	
	function is_allowed_2($task,$page,$user){
		
		$debug = false;
		
		
		if ($debug) print "<br />Checking permission for $page... ";
		if (empty($page)) return false;

		$res = $this->query("SELECT owner,ownergroup FROM $this->table_pages WHERE id='$page'");
		$row = $res->fetch_assoc();	
		$ownergroup = $row['ownergroup'];
		$owner = $row['owner'];
		
		if ($owner == $this->login_identifier) {
			if ($debug) print "user is owner. access granted!";
			return true;
		}		
		if ($this->webmaster_ident > 0 && $this->webmaster_ident == $this->login_identifier) {
			if ($debug) print "user is webmaster. access granted!";
			return true;
		}
	
		$perm_name = ($task == "r") ? "allow_read" : "allow_write";
		
		$res = $this->query(
			"SELECT 
				$this->table_classpermissions.default_who,
				$this->table_classpermissions.default_rights,
				$this->table_classpermissions.default_groupowner,
				$this->table_pagepermissions.who,
				$this->table_pagepermissions.rights,
				$this->table_pagepermissions.groupowner
			FROM 
				$this->table_classpermissions
			LEFT JOIN 
				$this->table_pagepermissions
			ON
				$this->table_pagepermissions.page='$page'
					AND
				$this->table_pagepermissions.name=$this->table_classpermissions.name
			WHERE 
				$this->table_classpermissions.name='$perm_name'
			"
		);
		
	
		$row = $res->fetch_assoc();
		if (empty($row['who'])) {
			$who = stripslashes($row['default_who']);
			$rights = stripslashes($row['default_rights']);
			$group = (stripslashes($row['default_groupowner']) == "1");
		} else {
			$who = stripslashes($row['who']);
			$rights = stripslashes($row['rights']);
			$group = (stripslashes($row['groupowner']) == "1");			
		}
		switch ($who) {
			case "all":
				if ($debug) print "settings allows everybody. access granted!";
				return true;
			case "loggedin":
				if ($debug) print "settings allows loggedin users with rights: $rights, memberof: $group,$ownergroup. ";
				if (empty($this->login_identifier)) break;
				$member = call_user_func($this->lookup_member, $this->login_identifier);
				if ($member->rights < $rights) break;
				if ($group && !in_array($ownergroup,$member->memberof)) break; 
				if ($debug) print "access granted!";
				return true;
			case "webmaster":
				if ($debug) print "access-settings allows only webmaster. ";
				break;
		}
		if ($debug) print "access denied!";
		return false;
	}
	
	function inheritPermission($permissionName, $fromPage, $toPage) {
	
		$res = $this->query(
			"SELECT 
				$this->table_classpermissions.name,
				$this->table_classpermissions.default_who,
				$this->table_classpermissions.default_rights,
				$this->table_classpermissions.default_groupowner,
				$this->table_pagepermissions.who,
				$this->table_pagepermissions.rights,
				$this->table_pagepermissions.groupowner
			FROM 
				$this->table_classpermissions
			LEFT JOIN 
				$this->table_pagepermissions
			ON
				$this->table_pagepermissions.page='$fromPage'
					AND
				$this->table_pagepermissions.name=$this->table_classpermissions.name
			WHERE 
				$this->table_classpermissions.name='$permissionName'
			"
		);
		$row = $res->fetch_assoc();
		if (empty($row['who'])) {
			$who = $row['default_who'];
			$rights = $row['default_rights'];
			$group = $row['default_groupowner'];
		} else {
			$who = $row['who'];
			$rights = $row['rights'];
			$group = $row['groupowner'];			
		}	
		
		$res = $this->query(
			"SELECT who FROM $this->table_pagepermissions WHERE page='$toPage' AND name='$permissionName'"
		);
		if ($res->num_rows == 0) {
			$this->query(
				"INSERT INTO $this->table_pagepermissions (page,name,who,rights,groupowner)
				VALUES ('$toPage','$permissionName','$who','$rights','$group')"
			);		
		} else {
			$this->query(
				"UPDATE $this->table_pagepermissions SET
					who = '$who',
					rights = '$rights',
					groupowner = '$group'
				WHERE page='$toPage' AND name='$permissionName'"
			);
		}
		
	}
	
	
	/*
	function getPermissionLevel($usr,$grp){
		$ml = $this->lookup_member;
		if ($usr == 0){
			return 0;
		} else {
			$me = $ml($usr);
			if (($me->rights == 2) && (in_array($grp,$me->memberof))){
				return 5;
			} else if ($me->rights == 2){
				return 6;	
			} else if (($me->rights == 1) && (in_array($grp,$me->memberof))){
				return 3;	
			} else if ($me->rights == 1){
				return 4;	
			} else if (($me->rights == 0) && (in_array($grp,$me->memberof))){
				return 1;	
			} else if ($me->rights == 0){
				return 2;	
			} else {
				return 0;
			}
		}
	}
	
	*/

	/* #################################################### LIST PAGES ####################################################################*/
	
	function listDir(){
		
		$output = "";
		if ($this->is_allowed("w",$this->current_dir)){
			$output .= '<p class="headerLinks">
					<a href="'.$this->generateURL('newdir').'" class="icn" style="background-image:url(/images/icns/folder_add.png);">Ny mappe</a>
					<a href="'.$this->generateURL('newpage').'" class="icn" style="background-image:url(/images/icns/page_white_add.png);">Ny side</a>
					<a href="'.$this->generateURL('newimg').'" class="icn" style="background-image:url(/images/icns/image_add.png);">Last opp bilde</a>
					<a href="'.$this->generateURL('fixthumbs').'" class="icn" style="background-image:url(/images/icns/photos.png);">Lag thumbs</a>
				</p>
			';
		}
		if ($this->is_allowed("r",$this->current_dir)){
			$lp = $this->listPages();
			$li = $this->listImages();
			if (($lp == "empty") && ($li == "empty")){
				$output .= "<p><i>Mappen er tom</i></p>";
			}
			if ($lp != 'empty') $output .= $lp;
			if ($li != 'empty') $output .= $li;
		} else {
			return $this->permissionDenied();
		}
		return $output;
	}
	
	

	
	function listPages(){
		
		$lang = $this->preferred_lang;
		
		$res = $this->query("SELECT 
				$this->table_pages.id,
				$this->table_pages.lastmodified,
				$this->table_pages.owner,
				$this->table_pages.ownergroup,
				$this->table_pages.pageslug,
				$this->table_pages.description,
				$this->table_classes.shortname,
				$this->table_classes.friendlyname,
				$this->table_classes.icon,
				$this->table_pagelabels.value as header
			FROM 
				$this->table_classes,
				$this->table_pages
			LEFT JOIN
				$this->table_pagelabels
				ON
				$this->table_pagelabels.page=$this->table_pages.id
				AND
				$this->table_pagelabels.label='page_header'
				AND
				$this->table_pagelabels.lang='$lang'
			WHERE 
				$this->table_pages.parent='".$this->current_dir."'
					AND
				$this->table_pages.class=$this->table_classes.id
			ORDER BY 
				$this->table_pages.class,
				$this->table_pagelabels.value"
		);
		if ($res->num_rows == 0) return "empty";
		$classNo = 1;
		$outp = "";
		$i = 0;
		while ($row = $res->fetch_assoc()){
			$i++;
			$classNo = !$classNo;
			
			$cpath = rtrim($this->getPath($this->root_dir),"/"). $this->current_path;
			
			if ($row['shortname'] == "dir"){
				$url_page = ($this->useCoolUrls ?
					$this->generateCoolURL($this->current_path . "/" . $row['pageslug']."/") :
					$this->generateURL('dyndir='.$row['id'])						
				);
			} else if ($row['pageslug'] == "start") {
				$url_page = ($this->useCoolUrls ?
					$cpath."/" :
					$this->generateURL('dynpage='.$row['id'])						
				);
			} else {
				$url_page = ($this->useCoolUrls ?
					$cpath."/".$row['pageslug']."/" :
					$this->generateURL('dynpage='.$row['id'])						
				);
			}
			$url_editpage = ($this->useCoolUrls ?
				"$url_page?editpage" :
				"$url_page&amp;editpage"				
			);
			$url_settings = $this->generateCoolURL($this->current_path . "/" . $row['pageslug']."/","editpagesettings");
			$url_rename = $this->generateCoolURL($this->current_path . "/" . $row['pageslug']."/","renamepage");
			$url_move = $this->generateCoolURL($this->current_path . "/" . $row['pageslug']."/","movepage");
			$url_delete = $this->generateCoolURL($this->current_path . "/" . $row['pageslug']."/","deletepage");
			$url_access = $this->generateCoolURL($this->current_path . "/" . $row['pageslug']."/","pageaccess");

			if (!empty($this->login_identifier)){
				if ($this->is_allowed("w",$row['id'])){
					$options = '<div class="smalltext" style="padding:3px;">'.
									(($row["shortname"] == 'html' || $row["shortname"] == 'bbcode') ? '<a href="'.$url_editpage.'" class="icn" style="background-image:url(/images/icns/page_white_edit.png);">Rediger side</a> ' : ''). 
									(($row["shortname"] != 'html' && $row["shortname"] != 'bbcode') ? '<a href="'.$url_settings.'" class="icn" style="background-image:url(/images/icns/page_white_wrench.png);">Innstillinger</a> ' : '').
									'<a href="'.$url_access.'" class="icn" style="background-image:url(/images/icns/page_white_key.png);">Rediger tilgang</a> 
									<a href="'.$url_rename.'" class="icn" style="background-image:url(/images/icns/textfield_rename.png);">Gi ny tittel</a> 
									<a href="'.$url_move.'" class="icn" style="background-image:url(/images/icns/page_white_edit.png);">Flytt</a>
									<a href="'.$url_delete.'" class="icn" style="background-image:url(/images/icns/page_white_delete.png);">Slett</a> 
									<div class="permissions">'.$row["owner"].' : '.$row["ownergroup"].'</div>
								</div>';
							
				} else {
					$options = " (Kun les)";
				}
			}
			if ($this->action == 'viewpage') {
				$options = "<p>".stripslashes($row['description'])."</p>";
			}
			
			$header = stripslashes($row['header']);
			if (empty($header)) $header = stripslashes($row['friendlyname'])." uten navn"; 
			
			$r1a = array();					$r2a = array();
			$r1a[1]  = "%icon%";			$r2a[1]  = $this->image_dir.stripslashes($row['icon']);
			$r1a[2]  = "%url%";				$r2a[2]  = $url_page;
			$r1a[3]  = "%name%";			$r2a[3]  = ($row['pageslug'] == "start") ? "<strong>$header</strong>" : $header;
			$r1a[4]  = "%class%";			$r2a[4]  = stripslashes($row['friendlyname']);
			$r1a[5]  = "%classno%";			$r2a[5]  = $classNo+1;
			$r1a[6]  = "%options%";			$r2a[6]  = $options;	
			$r1a[7]  = "%id%";				$r2a[7]  = $i;			
			$r1a[8]  = "%extras%";			$r2a[8]  = ($this->action == 'viewpage') ? "": " onmouseover=\"showOptions('options_$i')\" onmouseout=\"hideOptions('options_$i')";	
						
			if ($row['shortname'] == "dir") 
				$outp .= str_replace($r1a, $r2a, $this->template_filelisting_dir);
			else
				$outp .= str_replace($r1a, $r2a, $this->template_filelisting_file);
				
			if ($this->action != 'viewpage') {
				$outp .= "<script type='text/javascript'><!--
						getStyleObject('options_$i').display = 'none';
					--></script>";
			}

		}
		$r1a = array();					$r2a = array();
		$r1a[1]  = "%files%";			$r2a[1]  = $outp;
		return str_replace($r1a, $r2a, $this->template_filelisting);

	}
	
	function getThumbDimensions($oldWidth,$oldHeight,$newWidth,$newHeight){
		if (($oldWidth == 0) || ($oldHeight == 0)){
			return array(0,0);
		}
		$xScale = $newWidth/$oldWidth;
		$yScale = $newHeight/$oldHeight;
		$scalePercent = (($xScale < $yScale) ? $xScale : $yScale);
		$newWidth = $oldWidth*$scalePercent;
		$newHeight = $oldHeight*$scalePercent;
		return array($newWidth,$newHeight);
	}

	function listImages(){
		$res = $this->query("SELECT 
				id,caption,width,height,extension,filename
			FROM $this->table_images
			WHERE parent='".$this->current_dir."'
			ORDER BY id DESC"
		);
		$output = "";
		$rowCount = $res->num_rows;
		if ($rowCount == 0) return "empty";
		$output .= "
		
			<p class='imagethumbs'>\n";
		$classNo = 1;
		
		$this->initializeImagesInstance();
		$virtual_image_dir1 = $this->imginstance->getRelativePathToDir($this->current_dir,1); // version 1 (deprecated)
		$virtual_image_dir2 = $this->imginstance->getRelativePathToDir($this->current_dir,2); // version 2
		$virtual_thumb100_dir = '/'.$this->userFilesDir.'_thumbs140/'.$virtual_image_dir2;
		$real_thumb100_dir = BG_WWW_PATH.$this->userFilesDir.'_thumbs140/'.$virtual_image_dir2;
		$image_dir = $this->imginstance->getFullPathToDir($this->current_dir);
		
		// DEBUG: 
		// print $image_dir." - ".$virtual_image_dir;
		
		while ($row = $res->fetch_assoc()){

			$f = stripslashes($row['filename']);
			if (empty($f)) {
				$v_src = $virtual_image_dir1."image".$row['id']."_thumb140.".$row['extension'];
				$r_src = $image_dir."image".$row['id']."_thumb140.".$row['extension'];
			} else { 
				$v_src = $virtual_thumb100_dir.$f;
				$r_src = $real_thumb100_dir.$f;
			}
			if (file_exists($r_src)) {
				list($width, $height, $type, $attr) = getimagesize($r_src);
				$ml = round((140-$width)/2+1)."px";
				$mt = round((140-$height)/2+1)."px";
				$src = $v_src;
			} else {
				$ml = "20px";
				$mt = "20px";
				$src = $this->image_dir."notfound100.jpg";
			}
			$caption = stripslashes($row['caption']);
			$output .= '				<a href="'.$this->generateURL('current_image='.$row["id"]).'" title="'.$caption.'"><img src="'.$src.'" style="margin-top:'.$mt.'; margin-left:'.$ml.';" /></a>
			';
		}
		$output .= "<div style='clear:both;'></div>";
		return $output;
	}
	
	
	/* ############################################### NEW DIRECTORY ####################################################################*/

	function addNewDirForm(){
		if (!$this->is_allowed("w",$this->current_dir)) return $this->permissionDenied();

		$url_post = $this->generateURL(array("noprint=true","savenewdir"));
		$url_back = $this->generateURL("");

		if (isset($_GET['errors'])){
			$errors = explode(",",$_GET['errors']);
			$errorS = "";
			foreach ($errors as $e){
				$errorS .= "<div>".$this->errorMessages[$e]."</div>";
			}
		}

		$def_tittel = (isset($_GET['tittel']) ? $_GET['tittel'] : "");
		$def_addresse = (isset($_GET['slug']) ? $_GET['slug'] : "");
		$def_bb = ((isset($_GET['format']) && ($_GET['format'] == 'bbcode')) ? "checked='checked'" : "");
		$def_html = (empty($def_bb) ? "checked='checked'" : "");
		return '
			<script type="text/javascript"><!--
				function generateSlug(){
					tittel = getObject("dyn_tittel").value;
					//alert(tittel);
					tittel = tittel.toLowerCase();
			        re = /\$|,|@|#|~|`|\%|\*|\^|\&|\(|\)|\+|\=|\[|\]|\[|\}|\{|\;|\:|\'|\"|\<|\>|\?|\||\\|\!|\$|\./g;
					tittel = tittel.replace(re, "");
					tittel = tittel.replace(/ø/g, "o");
					tittel = tittel.replace(/å/g, "a");
					tittel = tittel.replace(/æ/g, "ae");
					tittel = tittel.replace(/ /g, "-");
					getObject("dyn_addresse").value = tittel;
				}

			//--></script>
		
			<h2>Opprett ny mappe</h2>'.
			(isset($errorS) ? $this->notSoFatalError($errorS) : '').
			'
			<form method="post" action="'.$url_post.'">
				<table>
					<tr>
						<td>Tittel: </td>
						<td><input type="text" name="dyn_tittel" id="dyn_tittel" value="'.$def_tittel.'" style="width: 300px;" /></td>
					</tr><tr>
						<td>Adresse: </td>
						<td><input type="text" name="dyn_addresse" id="dyn_addresse" value="'.$def_addresse.'" style="width: 300px;" /> <input type="button" value="Lag fra tittel" onclick="generateSlug();" /></td>
					</tr>
				</table>
				<br />
				<input type="button" value="Avbryt" onclick=\'window.location="'.$url_back.'"\' /> 
				<input type="submit" value="Lagre" />
			</form>
			<h2>Tips og hjelp</h2>
			<ul>
				<li>I feltet <i>adresse</i> fyller du inn sidens unike navn, slik den vil bli 
				representert i adresselinjen i nettleseren. Fyller du f.eks. inn "min-egen-mappe", 
				blir mappens adresse "http://'.$_SERVER['SERVER_NAME'].'/min-egen-mappe". Kun tegnene 
				a-z (små bokstaver), 0-9, bindestrek (-) og nedestrek (_) tillates. Knappen 
				«Lag fra tittel» vil i mange tilfeller gi et bra resultat, men ikke alltid (f.eks.
				om tittelen inneholder mange spesialtegn)<br />&nbsp;</li>
			</ul>
		';

	}

	function addNewDir(){
		$page = $this->current_dir;
		if (!$this->is_allowed("w",$page)){
			$this->permissionDenied();
			return 0;
		}
		$ml = $this->lookup_member;
		$me = $ml($this->login_identifier);
		$gr = $me->memberof[0];

		/* Validate Input BEGIN */
		$errors = array();
		if (empty($_POST['dyn_addresse'])){ 
			array_push($errors,"empty_slug"); 
			$slug = "";
		} else {
			$slug = addslashes($_POST['dyn_addresse']);
			$res = $this->query("SELECT id FROM $this->table_pages WHERE pageslug='$slug' AND parent='$page'");
			if ($res->num_rows > 0){ array_push($errors,"slug_notunique"); } 
			if (preg_match("/[^a-z0-9_-]/",$slug)){
				array_push($errors,"slug_contain_specials");
			}
			if (in_array($slug,$this->reserved_slugs)){
				array_push($errors,"slug_reserved");
			}
		}
		if (empty($_POST['dyn_tittel'])){ 
			array_push($errors,"empty_title"); 
			$tittel = "";
		} else {
			$tittel = addslashes($_POST['dyn_tittel']);
		}
		if (count($errors) > 0){
			$this->redirect($this->generateURL(array(
				"newdir",
				"errors=".implode(",",$errors),
				"tittel=".urlencode($tittel),
				"slug=".urlencode($slug)
			),true));
		}
		/* Validate Input END */
		
		$res = $this->query(
			"SELECT owner,ownergroup FROM $this->table_pages WHERE id='$page'"
		);
		$row = $res->fetch_assoc();
		$owner = $row['owner'];
		$ownergroup = $row['ownergroup'];
				
		$this->query("INSERT INTO $this->table_pages
			(created, lastmodified, owner, ownergroup, pageslug, class, parent)
			VALUES 
			('".time()."', '".time()."', '$owner','$ownergroup','$slug','1','$page')"
		);
		$id = $this->insert_id();
		
		$this->inheritPermission('allow_read',$page,$id);
		$this->inheritPermission('allow_write',$page,$id);
		
		$this->query("INSERT INTO $this->table_pagelabels
			(page,lang,label,value,multiline)
			VALUES 
			('$id','$this->preferred_lang','page_header','$tittel',0)"
		);
		
		$this->updatePageSlug($id, $slug);

		$this->redirect($this->generateURL(""));
	}

	/* #################################################### NEW PAGE ####################################################################*/

	function addNewPageForm(){
		if (!$this->is_allowed("w",$this->current_dir)) return $this->permissionDenied();
		
		$url_post = $this->generateURL(array("noprint=true","savenewpage"));
		$url_back = $this->generateURL("");

		if (isset($_GET['errors'])){
			$errors = explode(",",$_GET['errors']);
			$errorS = "";
			foreach ($errors as $e){
				$errorS .= "<div>".$this->errorMessages[$e]."</div>";
			}
		}

		$def_tittel = (isset($_GET['tittel']) ? $_GET['tittel'] : "");
		$def_addresse = (isset($_GET['slug']) ? $_GET['slug'] : "");
		$def_class = (isset($_GET['class']) ? $_GET['class'] : 1);
		$class_box = "<select name='class_select' class='class_select'>";
		$res = $this->query("SELECT 
				id, friendlyname 
			FROM $this->table_classes
			WHERE visible = '1'"
		);
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$val = stripslashes($row['friendlyname']);
			$class_box .= "<option value='$id'".(($def_class == $id) ? " selected='selected'":"").">$val</option>";
		}
		$class_box .= "</select>";
		$error_messages = (isset($_GET['errors']) ? $this->notSoFatalError($errorS) : "");
		
		$r1a = array();					$r2a = array();
		$r1a[]  = "%class%";			$r2a[]  = $class_box;
		$r1a[]  = "%errormessages%";	$r2a[]  = $error_messages;
		$r1a[]  = "%posturl%";			$r2a[]  = $url_post;
		$r1a[]  = "%backurl%";			$r2a[]  = $url_back;
		$r1a[]  = "%title%";			$r2a[]  = $def_tittel;
		$r1a[]  = "%slug%";				$r2a[]  = $def_addresse;
		$r1a[]  = "%server_name%";		$r2a[]  = $this->server_name;
			
		return str_replace($r1a, $r2a, $this->template_newpage_form);
	}
	
	function addNewPage(){
		$page = $this->current_dir;
		if (!$this->is_allowed("w",$page)) return $this->permissionDenied();

		$ml = $this->lookup_member;
		$me = $ml($this->login_identifier);
		$gr = $me->memberof[0];

		/* Validate Input BEGIN */
		$errors = array();
		if (empty($_POST['dyn_addresse'])){ 
			array_push($errors,"empty_slug"); 
			$slug = "";
		} else {
			$slug = addslashes($_POST['dyn_addresse']);
			$res = $this->query("SELECT id FROM $this->table_pages WHERE pageslug='$slug' AND parent='$page'");
			if ($res->num_rows > 0){ array_push($errors,"slug_notunique"); } 
			if (preg_match("/[^a-z0-9_-]/",$slug)){
				array_push($errors,"slug_contain_specials");
			}
			if (in_array($slug,$this->reserved_slugs)){
				array_push($errors,"slug_reserved");
			}
		}
		if (empty($_POST['dyn_tittel'])){ 
			array_push($errors,"empty_title"); 
			$tittel = "";
		} else {
			$tittel = addslashes($_POST['dyn_tittel']);
		}

		$class = addslashes($_POST['class_select']);
		if (count($errors) > 0){
			$this->redirect($this->generateURL(array(
				"newpage",
				"errors=".implode(",",$errors),
				"tittel=".urlencode($tittel),
				"slug=".urlencode($slug),
				"class=".urlencode($class)
			)));
		}
		/* Validate Input END */
		
		$res = $this->query(
			"SELECT owner, ownergroup FROM $this->table_pages WHERE id='$page'"
		);
		$row = $res->fetch_assoc();
		$owner = $row['owner'];
		$ownergroup = $row['ownergroup'];
		
		$this->query("INSERT INTO $this->table_pages
			(created, lastmodified, owner, ownergroup, pageslug, class, parent)
			VALUES 
			('".time()."', '".time()."', '$owner','$ownergroup','$slug','$class','$page')"
		);
		$id = $this->insert_id();
		
		$this->inheritPermission('allow_read',$page,$id);
		$this->inheritPermission('allow_write',$page,$id);
		
		$this->query("INSERT INTO $this->table_pagelabels
			(page,lang,label,value,multiline)
			VALUES 
			('$id','$this->preferred_lang','page_header','$tittel',0)"
		);
		
		$fullslug = $this->updatePageSlug($id, $slug);
		
		$page_url = "/".$fullslug."/";
		$this->addToChangeLog("\"<a href=\"$page_url\">$tittel</a>\" opprettet");		
		$this->addToActivityLog("opprettet siden <a href=\"$page_url\">$tittel</a>",false,"major");

		$this->redirect($this->generateURL(""), "Siden \"".stripslashes($tittel)."\" er opprettet" );
	}


	/* #################################################### RENAME PAGE ####################################################################*/	

	
	function printRenamePageForm() {
		
		$page = $this->current_dir;
		
		if (!is_numeric($page)){ $this->fatalError("incorrect input"); }
		$res = $this->query(
			"SELECT pageslug, print_header FROM $this->table_pages WHERE $this->table_pages.id='$page'"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("The page doesn't exist!"); 

		$row = $res->fetch_assoc();
		if (!$this->is_allowed("w",$page)) return $this->permissionDenied(); 

		$res2 = $this->query("SELECT
				$this->table_languages.code as lang_code,
				$this->table_languages.fullname as lang_name,
				$this->table_languages.flag as lang_flag,
				$this->table_pagelabels.id as label_id,
				$this->table_pagelabels.value as label
			FROM 
				$this->table_languages
			LEFT JOIN $this->table_pagelabels
			ON $this->table_pagelabels.page='$page'
			AND $this->table_pagelabels.lang = $this->table_languages.code"
		);
		$titles = array();
		$title_inputs = "";
		while ($row2 = $res2->fetch_assoc()) {
			$lang_code = $row2['lang_code'];
			$lang_name = $row2['lang_name'];
			$flag = $this->image_dir."flags/".$row2['lang_flag'];
			$val = stripslashes($row2['label']);
			if (isset($_SESSION['review_form'])){
				$val = $_SESSION['post_data']['title_'.$lang_code];
			}
			$title_inputs .= '
				<div style="border: 1px solid #555555; padding: 2px; margin: 2px;">
					<img src="'.$flag.'" alt="'.$lang_name.'" />
					<input type="text" name="title_'.$lang_code.'" value="'.$val.'" style="border:none; width: 330px;" />
				</div>
			';
		}
		
		$posturl = $this->generateURL(array("noprint=true","dorenamepage"));

		if (isset($_SESSION['review_form'])){
			$errorS = "";
			foreach ($_SESSION['review_form'] as $e){
				$errorS .= "<div>".$this->errorMessages[$e]."</div>";
			}
			$def_slug = $_SESSION['post_data']['pageslug'];
			$def_printheader = (isset($_SESSION['post_data']['print_header']) && ($_SESSION['post_data']['print_header'] == 'on')) ? ' checked="checked"' : "";
			
		} else {
			$def_slug = stripslashes($row['pageslug']);
			$def_printheader = ($row['print_header'] == true) ? ' checked="checked"' : "";
		}
		
		$output = '<form method="post" action="'.$posturl.'">
			<h2>Gi ny tittel:</h2>';
			if (isset($_SESSION["review_form"])) $output .= $this->notSoFatalError($errorS);
			$output .= '
			<table>
				<tr>
					<td valign="top"><strong>Tittel: </strong></td>
					<td>'.$title_inputs.'</td>
				</tr><tr>
					<td><strong>Adresse: </strong></td>
					<td><input type="text" name="pageslug" value="'.$def_slug.'" style="width: 300px;" /></td>
				</tr>
			</table>
			<p>
				<input type="checkbox" name="print_header" id="print_header"'.$def_printheader.'  />
				<label for="print_header">Vis overskrift</label>
			</p>
			<p>
				<input type="button" value="    Avbryt    " /> 
				<input type="submit" name="lagre2" value="    Lagre    " />
			</p>
		</form>';
		
		if (isset($_SESSION['review_form'])){
			unset($_SESSION['review_form']);
			unset($_SESSION['post_data']);
		}
		
		return $output;		
	}
	
	function renamePage(){

		$page = $this->current_dir;
		
		$updates = array();
		
		if (!is_numeric($page)){ $this->fatalError("incorrect input"); }
		$res = $this->query(
			"SELECT pageslug, parent FROM $this->table_pages WHERE id='$page'"
		);
		if ($res->num_rows != 1){ return $this->notSoFatalError("The page doesn't exist!"); }
		$row = $res->fetch_assoc();
		$page_parent = $row['parent'];
		$oldslug = $row['pageslug'];
		
		if (!$this->is_allowed("w",$page)) return $this->permissionDenied();

		$newslug = addslashes($_POST['pageslug']);
		
		if ($oldslug != $newslug) {
			$updates[] = "adresse";
		}
		
		/* Validate Input BEGIN */
		$errors = array();
		if (empty($newslug)){ 
			array_push($errors,"empty_slug"); 
			$newslug = "";
		} else {
			$res = $this->query(
				"SELECT id FROM $this->table_pages WHERE pageslug='$newslug' AND parent='$page_parent' AND (id != $page)"
			);
			if ($res->num_rows > 0){ array_push($errors,"slug_notunique"); } 
			if (preg_match("/[^a-z0-9_-]/",$newslug)){
				array_push($errors,"slug_contain_specials");
			}
			if (in_array($newslug,$this->reserved_slugs)){
				array_push($errors,"slug_reserved");
			}
		}
		if (count($errors) > 0){
			$_SESSION['review_form'] = $errors;
			$_SESSION['post_data'] = $_POST; 
			$this->redirect($this->generateURL("renamepage"));
		}
		/* Validate Input END */
		
		$res2 = $this->query("SELECT
				$this->table_languages.code as lang_code,
				$this->table_languages.fullname as lang_name,
				$this->table_languages.flag as lang_flag,
				$this->table_pagelabels.id as label_id,
				$this->table_pagelabels.value as label
			FROM 
				$this->table_languages
			LEFT JOIN $this->table_pagelabels
			ON $this->table_pagelabels.page='$page'
			AND $this->table_pagelabels.lang = $this->table_languages.code"
		);
		$titleUpdate = false;
		$page_title = "denne siden";
		$old_page_title = "ukjent";
		while ($row2 = $res2->fetch_assoc()) {
			$lang = $row2['lang_code'];
			$label = "page_header";
			$value = addslashes($_POST["title_$lang"]);
			if ($lang == $this->preferred_lang) {
				$old_page_title = $row2['label'];
				$page_title = $value;
			}
			if (empty($row2['label_id'])) {
				$this->query("INSERT INTO $this->table_pagelabels (page,lang,label,value) VALUES
					('$page','$lang','$label','$value')"
				);
				$titleUpdate = true;
			} else {
				$label_id = $row2['label_id'];
				if ($row2['label'] != $value) $titleUpdate = true;
				$this->query("UPDATE $this->table_pagelabels SET
					value = '$value' WHERE id='$label_id'"
				);
			}
		}
		if ($titleUpdate) $updates[] = "tittel";
		
		$this->updatePageSlug($page, $newslug);
		
		if (isset($_POST['print_header']) && ($_POST['print_header'] == 'on')) {
			$print_header = '1';
		} else {
			$print_header = '0';
		}
		$this->query("UPDATE $this->table_pages SET print_header='$print_header' WHERE id='$page'");

		$url = $this->generateCoolURL($this->getPath($page));
		$redir_url = $this->generateCoolURL($this->getPath($row['parent']));

		if (!empty($updates)) {
			$lastupdt = array_pop($updates);
			$updates = (count($updates) > 0) ? implode(", ",$updates)." og ".$lastupdt : $lastupdt;
			$this->addToActivityLog("omdøpte siden \"$old_page_title\" til \"<a href=\"$url\">$page_title</a>\"",true);
		}
		
		$this->redirect($redir_url,"Lagret navn og slug for siden.");

	}
	
	function updatePageSlug($page, $newslug){
			
		$this->query("UPDATE $this->table_pages 
			SET
				pageslug='$newslug'
			WHERE id='$page'"
		);
		$fullslug = trim($this->getPath($page),"/");
		$this->query("UPDATE $this->table_pages 
			SET
				fullslug='$fullslug'
			WHERE id='$page'"
		);
		
		$this->updatePageSlugChilds($page);
				
		return $fullslug;
	}
	
	function updatePageSlugChilds($page){			
		$res = $this->query("SELECT id FROM $this->table_pages WHERE parent='$page'");
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_assoc()) {
				$id = $row['id'];
				$fullslug = trim($this->getPath($id),"/");
				$this->query("UPDATE $this->table_pages 
					SET
						fullslug='$fullslug'
					WHERE id='$id'"
				);
				$this->updatePageSlugChilds($id);
			}		
		}
	}
	
	/* #################################################### MOVE PAGE ####################################################################*/
	
	function makePageDropDown($pageClass, $permission, $defaultVal = -1) {
		$res = $this->query("SELECT 
				$this->table_pages.id,
				$this->table_pages.fullslug,
				$this->table_pagelabels.value as header
			FROM 
				$this->table_pages 
			LEFT JOIN
				$this->table_pagelabels
				ON $this->table_pagelabels.page=$this->table_pages.id
				AND $this->table_pagelabels.label='page_header'
				AND $this->table_pagelabels.lang='$this->preferred_lang'
			WHERE 
				$this->table_pages.class='$pageClass'
			"
		);
		$pageList = "";
		while ($row = $res->fetch_assoc()){

			if ($this->is_allowed($permission,$row['id'])){
				$header = stripslashes($row["header"]);
				if (empty($header)) $header = "Uten navn";
				$fullslug = $row['fullslug'];
				$pageList .= "<option value='".$row["id"]."' ".(($defaultVal == $row['id']) ? "selected='selected'" : "").">$header ($fullslug)</option>\n				";	
			}			
		}
		return $pageList;	
	}
	
	function scanVDir($dir_id, $parenthesis, $folderList, $defaultVal, $permission = "r"){

		$res = $this->query("SELECT 
					$this->table_pages.id,
					$this->table_pages.class,
					$this->table_pages.pageslug,
					$this->table_pagelabels.value as header
				FROM 
					$this->table_pages 
				LEFT JOIN
					$this->table_pagelabels
					ON $this->table_pagelabels.page=$this->table_pages.id
					AND $this->table_pagelabels.label='page_header'
					AND $this->table_pagelabels.lang='$this->preferred_lang'
				WHERE 
					$this->table_pages.parent='$dir_id'
					AND $this->table_pages.class='1'
				"
			);
		while ($row = $res->fetch_assoc()){
				
			$url = "/bergenvs/".(empty($whereWeAre) ? "" : implode("/",$whereWeAre)."/" ).$row['pageslug']."/";
			if ($this->is_allowed($permission,$row['id'])){
				$header = stripslashes($row["header"]);
				if (empty($header)) $header = "Uten navn";
				$folderList .= "<option value='".$row["id"]."' ".(($defaultVal == $row['id']) ? "selected='selected'" : "").">$parenthesis$header</option>\n				";	
			}			
			if ($row['class'] == '1') {
				$whereWeAre[] = $row['pageslug'];
				$folderList = $this->scanVDir($row['id'],"$parenthesis &nbsp;&nbsp;&nbsp;",$folderList, $defaultVal, $permission);
				array_pop($whereWeAre);
			}
		}
		return $folderList;
	}
	
	function printMovePageForm() {
	
		$page = $this->current_dir;

		if (!is_numeric($this->current_dir)){ $this->fatalError("incorrect input"); }
		$res = $this->query(
			"SELECT parent FROM $this->table_pages WHERE $this->table_pages.id='$page'"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("The page doesn't exist!"); 

		$row = $res->fetch_assoc();
		if (!$this->is_allowed("w",$page)) return $this->permissionDenied(); 
		
		$posturl = $this->generateURL(array("noprint=true","domovepage"));

		if (isset($_GET['errors'])){
			$errors = explode(",",$_GET['errors']);
			$errorS = "";
			foreach ($errors as $e){
				$errorS .= "<div>".$this->errorMessages[$e]."</div>";
			}
			$def_folder = $_GET['mappe'];
		} else {
			$def_folder = $row['parent'];
		}

		$folderList = "
			<select name='folderlist'>
				".$this->scanVDir(0,"","",$def_folder,"w")."
			</select>
		";

		$output = '<form action="'.$posturl.'" method="post">
			<h2>Flytt side</h2>
			';
			if (isset($_GET['errors'])) $output .= $this->notSoFatalError($errorS);
			$output .= '
			<table>
				<tr>
					<td>Mappe: </td>
					<td>
						'.$folderList.'
					</td>
				</tr>
			</table>
			<p>
				<input type="button" value="    Avbryt    " /> 
				<input type="submit" name="lagre2" value="    Flytt    " />
			</p>
		</form>
		';
		return $output;
	}
	
	function movePage(){
	
		$page = $this->current_dir;

		if (!is_numeric($page)){ $this->fatalError("incorrect input"); }
		$res = $this->query(
			"SELECT class, parent, pageslug FROM $this->table_pages WHERE id='$page'"
		);
		if ($res->num_rows != 1){ $this->notSoFatalError("The page doesn't exist!"); return 0; }
		$row = $res->fetch_assoc();
		$from = $this->getPath($row['parent']);
		$pageslug = $row['pageslug'];
		$pageclass = intval($row['class']);
		
		if (!$this->is_allowed("w",$page)) return $this->permissionDenied();

		$newfolder = addslashes($_POST['folderlist']);
		
		/* Validate Input BEGIN */
		$errors = array();
		if (!is_numeric($newfolder)){
			array_push($errors,"invalid_folder");
		}
		if (!$this->is_allowed("w",$newfolder)){
			array_push($errors,"permission_denied");
		}

		if (count($errors) > 0){
			$this->redirect($this->generateURL(array(
				"movepage",
				"errors=".implode(",",$errors),
				"mappe=".$newfolder
			)));
		}
		/* Validate Input END */
		
		$to = $this->getPath($newfolder);	
		
		$f = explode("/",$_SERVER['SCRIPT_FILENAME']);
		array_pop($f);
		$image_dir = rtrim(implode("/",$f),"/").$this->pathToImages;
		
		$image_dir_old = $image_dir.trim($from,"/");
		$image_dir_new = $image_dir.trim($to,"/");
		
		if ($pageclass == 1) {
			// Dette er en mappe
			if (file_exists($image_dir_old)) {
				// Dette er en bildemappe
				rename($image_dir_old, $image_dir_new);			
				mkdir($image_dir_new) ;
			}
		}
		
		$this->query(
			"UPDATE $this->table_pages 
			SET parent='$newfolder'
			WHERE id='$page'"
		);
		
		$this->updatePageSlug($page, $pageslug);
		
		$url_page = $this->generateCoolURL($pageslug);
		$url_from = $this->generateCoolURL($from);
		$url_to = $this->generateCoolURL($to);
		$page_caption = $this->getPageTitle($page);
		$this->addToActivityLog("flyttet <a href=\"$url_page\">$page_caption</a> fra <a href=\"$url_from\">$from</a> til <a href=\"$url_to\">$to</a>");

		$tmp = explode("/",$this->current_path);
		array_pop($tmp);
		$tmp = implode("/",$tmp);


		$this->redirect($this->generateCoolURL($to),"Flyttet \"$page_caption\" fra $from til $to");
	}
	
	
	/* #################################################### EDIT PAGE PERMISSIONS ####################################################################*/
	
	function cacheRightsLabels() {
		$res = $this->query("SELECT level, shortdesc FROM $this->table_rights ORDER BY level");
		$this->rightslabels = array();
		while ($row = $res->fetch_assoc()) {
			$this->rightslabels[$row['level']] = stripslashes($row['shortdesc']);
		}
	}
	
	function makeRightsBox($name, $default = -1) {
		if (!isset($this->rightslabels)) $this->cacheRightsLabels();
		$r = "<select name='$name' class='textbox'>\n";
		foreach ($this->rightslabels as $i => $v) {
			$d = ($i == $default) ? " selected='selected'" : "";
			$r .= "<option value='$i'$d>$i</option>\n";
		}
		$r .= "</select>\n";
		return $r;	
	}
	
	function printPageAccessForm() {
		global $memberdb;
		
		$page = $this->current_dir;		
		if (!is_numeric($page)){ $this->fatalError("incorrect input"); }
		
		if (!$this->is_allowed("w",$page)) return $this->permissionDenied();
		
		$res = $this->query("SELECT 
				$this->table_pages.owner,
				$this->table_pages.ownergroup
			FROM 
				$this->table_pages
			WHERE 
				$this->table_pages.id='$page'"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("The page doesn't exist!"); 

		$row = $res->fetch_assoc();
	
		$posturl = $this->generateURL(array("noprint=true","savepageaccess"));
			
		$memberSelectBox = $memberdb->generateMemberSelectBox("eier", $row['owner']);
		$groupSelectBox = $memberdb->generateGroupSelectBox("eiergruppe",false,array(),$row['ownergroup']);
		
		$output = '

			<form method="post" action="'.$posturl.'">	
			
			<h2>Eierskap</h2>
			<p class="smalltext">
				<table>
					<tr><td>Eier: </td><td>'.$memberSelectBox.' *</td></tr>
					<tr><td>Eier-gruppe: </td><td>'.$groupSelectBox.'</td></tr>
				</table>
				* = Eier har alltid full tilgang til siden.
			</p>	
			
			<h2>Tilgang</h2>
			
		';
		
		$res = $this->query(
			"SELECT 
				$this->table_classpermissions.id as class_id,
				$this->table_classpermissions.name,
				$this->table_classpermissions.description,
				
				$this->table_classpermissions.default_who,
				$this->table_classpermissions.default_rights,
				$this->table_classpermissions.default_groupowner,
				
				$this->table_pagepermissions.id as id, 
				$this->table_pagepermissions.who,
				$this->table_pagepermissions.rights,
				$this->table_pagepermissions.groupowner
			FROM 
				($this->table_pages,
				$this->table_classpermissions)
			LEFT JOIN 
				$this->table_pagepermissions
			ON
				($this->table_pagepermissions.page=$this->table_pages.id
					AND
				$this->table_pagepermissions.name=$this->table_classpermissions.name)
			WHERE 
				$this->table_pages.id='$page'
					AND
				($this->table_classpermissions.class=$this->table_pages.class OR $this->table_classpermissions.class=0)
			ORDER BY
				$this->table_classpermissions.class,
				$this->table_classpermissions.id
			"
		);
		while ($row = $res->fetch_assoc()) {
			$class_id = stripslashes($row['class_id']);
			$name = stripslashes($row['name']);
			$description = stripslashes($row['description']);
			if ($row['id'] == "") {
				$def_webmaster = ($row['default_who'] == 'webmaster') ? " selected='selected'" : ""; 
				$def_all = ($row['default_who'] == 'all') ? " selected='selected'" : ""; 
				$def_loggedin = ($row['default_who'] == 'loggedin') ? " selected='selected'" : ""; 
				$def_rights = $row['default_rights'];
				$def_groupmem = ($row['default_groupowner'] == '1') ? " checked='checked'" : ""; 
			} else {
				$def_webmaster = ($row['who'] == 'webmaster') ? " selected='selected'" : ""; 
				$def_all = ($row['who'] == 'all') ? " selected='selected'" : ""; 
				$def_loggedin = ($row['who'] == 'loggedin') ? " selected='selected'" : ""; 
				$def_rights = $row['rights'];
				$def_groupmem = ($row['groupowner'] == '1') ? " checked='checked'" : ""; 
			}
			
			$output .= "<div class='pageoptions'>
						<div class='description'>$description: <em>($name)</em></div>
						<div class='editpermissions'>
							
							<select name='$name' id='$name' class='textbox' onchange=\"perm_select(this, 'extras_$name')\">
								<option value='webmaster'$def_webmaster>Kun eier og webmaster</option>
								<option value='loggedin'$def_loggedin>Innloggede</option>
								<option value='all'$def_all>Alle</option>
							</select>
							
							<span id='extras_$name'>
								Minste rettighetsnivå:
								".$this->makeRightsBox("rights_$name",$def_rights)."
								<input type='checkbox' name='group_$name' id='group_$name'$def_groupmem>
									<label for='group_$name'>og medlem av eiergruppe.</label>							
							</span>
							<script type='text/javascript'><!--
								perm_select(getObject('$name'), 'extras_$name')
							--></script>
							
						</div>
						<div id='feedback$class_id' style='display:none'></div>
						</div>";		
		}
		$output .= "
			
			<p>
				<input type='button' value='    Avbryt    ' /> <input type='submit' name='lagre2' value='    Lagre    ' />
			</p>
		</form>
		";
		
		return $output;
	}
	
	function savePageAccess(){

		$page = $this->current_dir;
		if (!is_numeric($page)){ $this->fatalError("incorrect input"); }
		
		if (!$this->is_allowed("w",$page)) return$this->permissionDenied();
		
		$res = $this->query(
			"SELECT 
				owner, ownergroup, class
			FROM $this->table_pages 
			WHERE id='$page'"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("The page doesn't exist!");
		$row = $res->fetch_assoc();
		$class = $row['class'];
		
		foreach ($_POST as $n => $v) {

			$res2 = $this->query(
				"SELECT 
					$this->table_classpermissions.id as class_id,
					$this->table_classpermissions.name
				FROM 
					$this->table_classpermissions
				WHERE 
					$this->table_classpermissions.name='".addslashes($n)."'
					AND
					($this->table_classpermissions.class='$class' OR $this->table_classpermissions.class='0')
				"
			);
			if ($res2->num_rows == 1) {
				$row2 = $res2->fetch_assoc();
				
				$res3 = $this->query(
					"SELECT 
						$this->table_pagepermissions.id,
						$this->table_pagepermissions.who,
						$this->table_pagepermissions.rights,
						$this->table_pagepermissions.groupowner
					FROM 
						$this->table_pagepermissions
					WHERE 
						$this->table_pagepermissions.name='".addslashes($n)."'
							AND
						$this->table_pagepermissions.page='$page'
					"
				);
				if ($res3->num_rows == 1) {
					$row3 = $res3->fetch_assoc();
					$pageoption_id = $row3['id'];
				} else {
					$pageoption_id = -1;
				}
								
				$name = addslashes($n);
				$who = addslashes($v);
				$rights = addslashes($_POST["rights_$n"]);
				$group = (isset($_POST["group_$n"]) && $_POST["group_$n"] == "on") ? "1" : "0";
				
				if ($pageoption_id == -1) {
					$this->query(
						"INSERT INTO $this->table_pagepermissions 
						(page,name,who,rights,groupowner) 
						VALUES ('$page','$name','$who','$rights','$group')"
					);
				} else {
					$this->query(
						"UPDATE $this->table_pagepermissions 
						SET who='$who',
							rights='$rights',
							groupowner='$group'
						WHERE id='$pageoption_id'"
					);
				}						
			}
		}
		
		$pagec = $this->getPageTitle($page);
		$this->addToActivityLog("oppdatert tilgangsinnstillinger for $pagec");
				
		$tmp = explode("/",$this->current_path);
		array_pop($tmp);
		$tmp = implode("/",$tmp);
		$url = ($this->useCoolUrls ?
			$this->generateCoolURL($tmp)."/" :
			$this->generateURL("dyndir=".$this->current_dir)
		);
		
		$owner = addslashes($_POST['eier']);
		$ownergroup = addslashes($_POST['eiergruppe']);
		
		if ($owner != $row['owner'] || $ownergroup != $row['ownergroup']){
			$this->query("UPDATE $this->table_pages 
				SET owner='$owner',
					ownergroup='$ownergroup'
				WHERE id='$page'"
			);
			$this->addToActivityLog("endret eierskap for $pagec");			
		}
	
		$this->redirect($url,"Innstillinger for eierskap og tilgang ble lagret.");

	}


	/* #################################################### EDIT PAGESETTINGS ####################################################################*/

	
	function printEditPageSettingsForm(){
		global $memberdb;
		$output = "";
	
		$page = $this->current_dir;
		
		if (!is_numeric($page)){ $this->fatalError("incorrect input"); }

		if (!$this->is_allowed("w",$page)) return $this->permissionDenied();

        $tc = $this->table_classoptions;
		$res = $this->query(
			"SELECT 
				$tc.id as class_id,
				$tc.name,
				$tc.section,
				$tc.datatype,
				$tc.defaultvalue,
				$tc.prefix,
				$tc.required,
				$tc.multiline,
				$tc.description,
				$tc.extras,
				$this->table_pageoptions.id as id, 
				$this->table_pageoptions.value
			FROM 
				($this->table_pages,
				$tc)
			LEFT JOIN 
				$this->table_pageoptions
			ON
				$this->table_pageoptions.page=$this->table_pages.id
					AND
				$this->table_pageoptions.name=$tc.name
			WHERE 
				$this->table_pages.id='$page'
					AND
				($tc.class=$this->table_pages.class OR $tc.class=0)
			ORDER BY
				$tc.section
			"
		);

		$posturl = $this->generateURL(array("noprint=true","savepagesettings"));

		$output .= '
			
			<form action="'.$posturl.'" method="post" name="settingsform">
			<h2>Sideinnstillinger</h2>
		';			
			
		if (isset($_GET['errors'])) $this->notSoFatalError($errorS);

		$currentSection = "";
		
		while ($row = $res->fetch_assoc()) {
			$class_id = stripslashes($row['class_id']);
			$name = stripslashes($row['name']);
			$value = stripslashes($row['value']);
			$description = stripslashes($row['description']);
			$datatype = stripslashes($row['datatype']);
			$multiline = $row['multiline'];
			if ($value == '') $value = stripslashes($row['defaultvalue']);
			$section = stripslashes($row['section']);
			if ($section != $currentSection) {
				if ($currentSection != "") print "</fieldset>\n";
				$output .= "<fieldset><legend>$section</legend>\n";
				$currentSection = $section;
			}
			$output .= "<div class='pageoptions'>
				<div class='description'>$description: <em>($name)</em></div>
				<div class='edit'>
			";
			switch ($datatype) {
				
				case 'int':
				case 'string':
					if ($multiline) {
						$output .= "<textarea name='$name' cols='60' rows='8' id='$name' class='textarea' wrap='off' onblur=\"validateField('$name','feedback$class_id','".$this->generateURL(array('validate_pagesetting','noprint=true'),true)."')\" />".htmlspecialchars($value)."</textarea>";
					} else {
						$output .= "<input type='text' name='$name' id='$name' value=\"".htmlspecialchars($value)."\" class='textbox' onblur=\"validateField('$name','feedback$class_id','".$this->generateURL(array('validate_pagesetting','noprint=true'),true)."')\" />";
					}
					break;
					
				case 'bool':
					$checked = ($value == '1') ? " checked='checked'" : "";
					$output .= "<input type='checkbox' name='$name' id='$name'$checked />";
					break;
				
				case 'db_table': 
					$output .= "<input type='text' name='$name' id='$name' value='$value' class='textbox' onblur=\"validateField('$name','feedback$class_id','".$this->generateURL(array('validate_pagesetting','noprint=true'),true)."')\" />";
					break;
					
				case 'cms_folder': 
					$output .= "
							<select name='$name' id='$name' class='textbox'>
								".$this->scanVDir(0,"","",$value,"r")."
							</select>
					";
					break;

				case 'cms_page': 
					$output .= "
							<select name='$name' id='$name' class='textbox'>
								".$this->makePageDropDown($row['extras'],"r",$value)."
							</select>
					";
					break;
				
				case 'user': 
					$output .= $memberdb->generateMemberSelectBox($name,$value);							
					break;					
				
				default:
					$output .= "Ukjent datatype: $datatype";
					break;
			}
			$output .= "</div>
				<div id='feedback$class_id' style='display:none'></div>
				</div>
			";
			
		}
		
		if ($currentSection != "") $output .= "</fieldset>\n";
		
		$output .= "
			<p>
				<input type='button' value='    Avbryt    ' /> 
				<input type='submit' name='lagre2' value='    Lagre    ' />
			</p>
		</form>
		";
		return $output;
	}

	function savePageSettings(){
	
		$page = $this->current_dir;

		if (!is_numeric($page)){ $this->fatalError("incorrect input"); }
		$res = $this->query("SELECT 
				pageslug, class
			FROM $this->table_pages 
			WHERE id='$page'"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("The page doesn't exist!");
		$row = $res->fetch_assoc();
		$class = $row['class'];

		if (!$this->is_allowed("w",$page)) return $this->permissionDenied();
		
		/* Validate & Save Input BEGIN */
		$errors = array();
		
		$res = $this->query(
				"SELECT 
					$this->table_classoptions.name,
					$this->table_classoptions.datatype,
					$this->table_classoptions.required,
					$this->table_classoptions.prefix,
					$this->table_classoptions.defaultvalue,
					$this->table_classoptions.extras,
					$this->table_pageoptions.id,
					$this->table_pageoptions.value
				FROM 
					$this->table_classoptions
				LEFT JOIN 
					$this->table_pageoptions
				ON 
					$this->table_pageoptions.page='$page'
				AND 
					$this->table_pageoptions.name=$this->table_classoptions.name
				WHERE 
					$this->table_classoptions.class='$class'
				"
		);			
		
		while ($row = $res->fetch_assoc()) {
			
			$saveCurrent = false;
			$defaultValue = stripslashes($row['defaultvalue']);
			$prefix = stripslashes($row['prefix']);
			$n = $row['name'];
			$v = isset($_POST[$n]) ? $_POST[$n] : "";	
			$pageoption_id = empty($row['id']) ? -1 : $row['id'];
			
			switch ($row['datatype']) {
					
				case 'bool':
					$v = ($v == 'on') ? '1' : '0';
					$saveCurrent = true;
					break;
				
				case 'string':
					
					if (empty($v) && $row['required'] == '1') {
						$errors[] = "
							<div>
								<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Du må fylle inn en verdi i feltet '$n'.
							</div>
						";
						break;
					}
					$saveCurrent = true;
					break;
					
				case 'cms_folder':
				case 'cms_page':
				case 'int':
				case 'user':
					
					if (empty($v)) {
						if ($row['required'] == '1') {
							$errors[] = "
								<div>
									<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Du må fylle inn en verdi i feltet '$n'.
								</div>
							";
							break;
						}
					} else if (!is_numeric($v)) {
						$errors[] = "
							<div>
								<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Verdien i feltet '$n' må være et tall!
							</div>
						";
						break;
					} else if ($v <= 0) {
						$errors[] = "
							<div>
								<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Verdien i feltet '$n' må må være større enn 0.
							</div>
						";
						break;
					}
					
					$saveCurrent = true;
					break;
					
				case 'db_table':
											
					if (empty($v) && $row['required'] == '1') {
						$errors[] = "
							<div>
								<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Du må fylle inn en verdi i feltet '$n'.
							</div>
						";
						break;
					}
					
					$tabname = $prefix.$v;
					
					$res3 = $this->query("SHOW TABLES LIKE '".addslashes($tabname)."'");
					if ($res3->num_rows == 0) {
						$constructor = stripslashes($row['extras']);
						if (empty($constructor)) {
							$errors[] = "
									<div class='feedback'>
										<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Du må skrive inn navnet på en eksisterende tabell i feltet '$n'.
									</div>
								";
							break;
						}
						
						$r1a = array();					$r2a = array();
						$r1a[1]  = "%tablename%";		$r2a[1]  = $tabname;
						$r1a[2]  = "%comment%";			$r2a[2]  = "Side-id: $page, klasse: ".$row['name'].", opprettet ".date("d. M Y",time()).".";
						$query = str_replace($r1a, $r2a, $constructor);
						
						if ($this->query($query,true,true)) {
							$this->addToActivityLog("opprettet tabell '$tabname' for side-id: $page, klasse: ".$row['name'].".");
							$saveCurrent = true;
						} else {
							$errors[] = "
								<div>
									<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Kunne ikke opprette tabellen '$tabname'. Sjekk at navnet ikke inneholder ulovlige tegn.
								</div>
							";
						}
						break;
					} else {
						$saveCurrent = true;
						break;
					}
					break;
			}		
			if ($saveCurrent) {
				if ($pageoption_id == -1) {
					if ($v != $defaultValue) {
						$this->query("INSERT INTO $this->table_pageoptions 
							(page,name,value) VALUES 
							('$page','".addslashes($n)."','".addslashes($v)."')"
						);
					}
				} else {
					if ($v != $defaultValue) {
						$this->query("UPDATE $this->table_pageoptions 
							SET value='".addslashes($v)."' WHERE id='$pageoption_id'");
					} else {
						$this->query("DELETE FROM $this->table_pageoptions WHERE id='$pageoption_id' LIMIT 1");						
					}
				}
			}
				
		}
		
		if (count($errors) > 0){
			$es = "";
			foreach ($errors as $e) {
				$es .= $e;
			}
			$es .= "<p>De andre verdiene (hvis noen) ble lagret.</p>";
			header("Content-Type: text/html; charset=utf-8");
			print $this->notSoFatalError($es);
			exit();
		}
		/* Validate & Save Input END */

		$tmp = explode("/",$this->current_path);
		array_pop($tmp);
		$tmp = implode("/",$tmp);


		$this->redirect($this->generateCoolURL($tmp)."/","Oppdaterte innstillinger for $page.");
	}

	function validatePageSetting() {

		header("Content-Type: text/html; charset=utf-8");
		
		foreach ($_POST as $n => $v) {
			$res = $this->query(
				"SELECT 
					$this->table_classoptions.name,
					$this->table_classoptions.datatype,
					$this->table_classoptions.required,
					$this->table_classoptions.prefix,
					$this->table_classoptions.extras
				FROM 
					$this->table_classoptions,
					$this->table_pages
				WHERE 
					$this->table_pages.id='$this->current_dir'
						AND
					$this->table_pages.class=$this->table_classoptions.class
						AND
					$this->table_classoptions.name='".addslashes($n)."'
				"
			);
						
			if ($res->num_rows == 1) {
				$row = $res->fetch_assoc();
				$prefix = stripslashes($row['prefix']);
				switch ($row['datatype']) {
					
					case 'string':
					case 'cms_folder':
					case 'cms_page':
						break;
						
					case 'int':
						
						if (empty($v)) {
							if ($row['required'] == '1') {
								print "
									<div class='feedback'>
									<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Du må fylle inn en verdi i dette feltet.
									</div>
									";
								return;						
							}
						} else if (!is_numeric($v)) {
							print "
								<div class='feedback'>
									<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Verdien må være et tall!
								</div>
							";
							return;			
						} else if ($v <= 0) {
							print "
								<div class='feedback'>
									<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Verdien må være større enn 0.
								</div>
							";
							return;			
						}
						break;
						
					case 'db_table':
					
						if (empty($v)) {
							if ($row['required'] == '1') {
								print "
									<div class='feedback'>
										<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Du må fylle inn en verdi i dette feltet.
									</div>
								";
								return;						
							} else {
								break;
							}
						}
						
						$tabname = $prefix.$v;
						
						$res = $this->query("SHOW TABLES LIKE '".addslashes($tabname)."'");
						if ($res->num_rows == 1) {
							print "
									<div class='feedback'>
										<img src='".$this->image_dir."notice.gif' style='vertical-align: middle; padding-right: 3px;' />Tabellen $tabname finnes allerede. Denne vil bli brukt.
									</div>
								";
								return;
						} else if (empty($row['extras'])) {
							print "
									<div class='feedback'>
										<img src='".$this->image_dir."false-1.gif' style='vertical-align: middle; padding-right: 3px;' />Du må skrive inn navnet på en eksisterende tabell.
									</div>
								";
								return;
						} else {
							print "
								<div class='feedback'>
									<img src='".$this->image_dir."notice.gif' style='vertical-align: middle; padding-right: 3px;' />Tabellen $tabname finnes ikke. Den vil bli opprettet når du lagrer denne verdien.
								</div>
								";
							return;
						}
						break;
				}				
				
			}
		}
		print "ok";
		exit();
		
		
							/*	
								
		
		print "<div class='feedback'>
									".$res->num_rows."Not found: "; print_r($_POST); print "
									</div>";
									*/
			return;

	}

	/* #################################################### DELETE PAGE ####################################################################*/


	function printDeletePageForm(){
		
		$page = $this->current_dir;
		
		if (!is_numeric($page)){ $this->fatalError("incorrect input"); }
		
		$res = $this->query("SELECT 
				$this->table_pages.fullslug,
				$this->table_classes.shortname
			FROM 
				$this->table_pages, $this->table_classes
			WHERE 
				$this->table_pages.id = '$page'
					AND
				$this->table_pages.class=$this->table_classes.id
			"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("The page doesn't exist!"); 
		$row = $res->fetch_assoc();
		
		if (!$this->is_allowed("w",$page)){
			$this->fatalError("Du har ikke tilgang til å slette denne siden");
		}

		if ($row['shortname'] == 'dir'){
			if (!is_numeric($this->current_dir)){ $this->fatalError("incorrect input"); }
			$res = $this->query("SELECT 
					id
				FROM $this->table_pages 
				WHERE parent='$this->current_dir'"
			);
			if ($res->num_rows > 0) return $this->notSoFatalError("Mappen er ikke tom. Du må først slette alle sidene i mappen før du kan slette selve mappen."); 

			$res = $this->query("SELECT 
					id
				FROM $this->table_images 
				WHERE parent='$this->current_dir'"
			);
			if ($res->num_rows > 0) return $this->notSoFatalError("Mappen er ikke tom. Du må først slette alle bildene i mappen før du kan slette selve mappen."); 

			$page_header = "Slett mappe";
			$paragraph = "Er du HELT sikker på at du ønsker å slette mappen \"".stripslashes($row['fullslug'])."\"?<br />
				Når du først har trykket \"Ja\" er det ingen angremulighet.";
		} else {
			$page_header = "Slett side";
			$paragraph = "Er du HELT sikker på at du ønsker å slette siden \"".stripslashes($row['fullslug'])."\"?<br />
				Når du først har trykket \"Ja\" er det ingen angremulighet.";
		}
		
		$tmp = explode("/",$this->current_path);
		array_pop($tmp);
		$tmp = implode("/",$tmp);
		
		$post_url = $this->generateURL(array("noprint=true","dodeletepage"));

		$r1a = array();					$r2a = array();
		$r1a[1]  = "%page_header%";		$r2a[1]  = $page_header;
		$r1a[2]  = "%paragraph%";		$r2a[2]  = $paragraph;
		$r1a[3]  = "%posturl%";			$r2a[3]  = $post_url;
		$r1a[4]  = "%url_no%";			$r2a[4]  = $this->generateCoolURL($tmp);
			
		return str_replace($r1a, $r2a, $this->template_deletepage_form);
	}

	function deletePage(){
		
		$page = $this->current_dir;
		if (!is_numeric($page)){ $this->fatalError("incorrect input"); }
		
		$res = $this->query("SELECT 
				$this->table_pages.fullslug,
				$this->table_classes.shortname,
				$this->table_classes.friendlyname
			FROM 
				$this->table_pages, $this->table_classes
			WHERE 
				$this->table_pages.id = '$page'
					AND
				$this->table_pages.class=$this->table_classes.id
			"
		);
		if ($res->num_rows != 1) return $this->notSoFatalError("The page doesn't exist!");
		$row = $res->fetch_assoc();
		
		if (!$this->is_allowed("w",$page)){
			$this->fatalError("Du har ikke tilgang til å slette denne siden");
		}

		if ($row['shortname'] == 'dir'){
			if (!is_numeric($this->current_dir)){ $this->fatalError("incorrect input"); }
			$res = $this->query("SELECT 
					id
				FROM $this->table_pages 
				WHERE parent='$this->current_dir'"
			);
			if ($res->num_rows > 0) return $this->notSoFatalError("Mappen er ikke tom. Du må først slette alle sidene i mappen før du kan slette selve mappen."); 

			$res = $this->query("SELECT 
					id
				FROM $this->table_images 
				WHERE parent='$this->current_dir'"
			);
			if ($res->num_rows > 0) return $this->notSoFatalError("Mappen er ikke tom. Du må først slette alle bildene i mappen før du kan slette selve mappen."); 
			
			$this->addToActivityLog("slettet mappen ".$row['fullslug']);
		} else {
			$this->addToActivityLog("slettet siden ".$row['fullslug']);
		}

		$this->query("DELETE FROM $this->table_pages WHERE id='".$this->current_dir."'");
		
		$tmp = explode("/",$this->current_path);
		array_pop($tmp);
		$tmp = implode("/",$tmp);

		$this->redirect($this->generateCoolURL($tmp)."/");
	}	
	
	function getDirOptionValue($name) {
		
		$page_id = $this->current_dir;
		
		$res = $this->query(
			"SELECT 
				$this->table_classoptions.defaultvalue,
				$this->table_classoptions.prefix,
				$this->table_pageoptions.value
			FROM 
				$this->table_classoptions
			LEFT JOIN 
				$this->table_pageoptions
			ON
				$this->table_pageoptions.page='$page_id'
					AND
				$this->table_pageoptions.name=$this->table_classoptions.name
			WHERE 
				$this->table_classoptions.class = '1'
				AND
				$this->table_classoptions.name='$name'				
			"
		);
		$row = $res->fetch_assoc();
		$prefix = stripslashes($row['prefix']);
		if (empty($row['value'])) 
			$value = stripslashes($row['defaultvalue']);
		else
			$value = stripslashes($row['value']);
		return $prefix.$value;

	}
	
	

	/* #################################################### IMAGE ACTIONS ####################################################################*/
	
	
	function newImageForm(){
		if (!$this->is_allowed("w",$this->current_dir)) return $this->permissionDenied();
		return $this->imginstance->uploadImageForm(false); // metode i images.php
	}
	
	function saveNewImage(){
		if (!$this->is_allowed("w",$this->current_dir)){ $this->permissionDenied(); return; }
		if (empty($_POST['bildenavn'])) $this->fatalError("Du må skrive inn en tittel på bildet");
		$tittel = $_POST['bildenavn'];
		$img_id = $this->imginstance->uploadImage('bildefil',$this->current_dir,$tittel);
		
		$this->makeThumbs($img_id);
			
		$forced_image_ratio = $this->getDirOptionValue('forced_image_ratio');
		
		//$this->addToActivityLog("lastet opp et nytt bilde: <a href=\"".$this->generateURL("current_image=$img_id")."\">$tittel</a>");
		
		if (!empty($forced_image_ratio)) {
			$this->redirect($this->generateURL(array("current_image=$img_id","cropimage")));			
		} else {
			$this->redirect($this->generateURL("current_image=$img_id"),"Bildet ble lastet opp!");	
		}		
		
	}
	
	function makeThumbs($img_id) {
		$this->imginstance->createThumbnail($img_id,true,100,100,"_thumb100");
		$this->imginstance->createThumbnail($img_id,true,500,-1,"_thumb490");
	}
	
	
	function replaceImageForm(){
		if (!$this->is_allowed("w",$this->current_dir)) return $this->permissionDenied(); 
		return $this->imginstance->printReplaceImageForm($this->current_image);
	}
	
	function saveReplacedImage(){
		if (!$this->is_allowed("w",$this->current_dir)){ $this->permissionDenied(); return; }
		$img_id = $this->imginstance->uploadImage('bildefil', $this->current_dir,'',true, $this->current_image);
		$this->makeThumbs($img_id);
		$forced_image_ratio = $this->getDirOptionValue('forced_image_ratio');
		if (!empty($forced_image_ratio)) {
			$this->redirect($this->generateURL(array("current_image=$img_id","cropimage")));			
		} else {
			$this->redirect($this->generateURL("current_image=$img_id"),"Bildet ble erstattet!");	
		}		

	}
	
	function editImageTitleForm(){
		if (!$this->is_allowed("w",$this->current_dir)){ $this->permissionDenied(); return; }
		return $this->imginstance->printImageTitleForm($this->current_image);
	}
	function saveImageTitle(){
		if (!$this->is_allowed("w",$this->current_dir)){ $this->permissionDenied(); return; }
		$this->imginstance->saveNewImageTitle($this->current_image);
		$this->redirect($this->generateURL('current_image='.$this->current_image),"Bildetittelen ble lagret.");	
	}
	function deleteImageForm(){
		if (!$this->is_allowed("w",$this->current_dir)){ $this->permissionDenied(); return; }
		return $this->imginstance->printDeleteImageForm($this->current_image);
	}
	function doDeleteImage(){
		if (!$this->is_allowed("w",$this->current_dir)){ $this->permissionDenied(); return; }
		$this->imginstance->deleteImage($this->current_image);
		$this->redirect($this->generateURL(""),"Bildet ble slettet.");	
	}
	function imageDetails(){
		if (!$this->is_allowed("r",$this->current_dir)){ $this->permissionDenied(); return; }
		return $this->imginstance->printImageDetails($this->current_image);
	}
	function cropImageForm() {
		
		if (!$this->is_allowed("w", $this->current_dir)) return $this->permissionDenied();
		
		$scaleImages = $this->getDirOptionValue('scale_images');
		$keep_proportions = $this->getDirOptionValue('keep_proportions');
		$max_width = $this->getDirOptionValue('max_width');
		$max_height = $this->getDirOptionValue('max_height');
		
		$forced_image_ratio = $this->getDirOptionValue('forced_image_ratio');
		if (empty($forced_image_ratio)) $forced_image_ratio = false;
		
		
		 // (Forklaring: 100/80 = 1.25)
		 return '
			<h2>Beskjær bilde</h2>
			<form method="post" action="'.$this->generateURL(array("noprint=true","current_image=$this->current_image","docropimage")).'">
			'.$this->imginstance->outputCropForm($this->current_image, $forced_image_ratio).'
			</form>
		';
	}
	
	function cropImage() {
	
		if (!$this->is_allowed("w", $this->current_dir)) return $this->permissionDenied();
		
		if (!isset($_POST['crop_x']) || !isset($_POST['crop_x'])) $this->fatalError('invalid input .1');
		if (!isset($_POST['crop_y']) || !isset($_POST['crop_y'])) $this->fatalError('invalid input .2');
		if (!isset($_POST['crop_width']) || !isset($_POST['crop_width'])) $this->fatalError('invalid input .3');
		if (!isset($_POST['crop_height']) || !isset($_POST['crop_height'])) $this->fatalError('invalid input .4');
		
		$img_id = $_GET['current_image'];
		$img_filename = $this->imginstance->getFullPathToImage($img_id);
		$this->imginstance->cropImage($img_filename, $_POST['crop_x'], $_POST['crop_y'], $_POST['crop_width'], $_POST['crop_height']);
		$this->imginstance->updateDatabaseInfo($img_id);		
		$this->makeThumbs($img_id);

		$this->redirect($this->generateURL("current_image=$img_id"),"Bildet er beskjært. Last siden på nytt dersom du ikke ser endringene.");
	}
	
	function fixThumbsForm() {
	
		if (!$this->is_allowed("w", $this->current_dir)) return $this->permissionDenied();
			
		return '
			<h2>Lag thumbs</h2>
			<p>
				Dersom miniatyrbildene er skadet eller slettet kan du opprette de på nytt her.
				Du trenger bare gjøre det dersom noe er galt! Merk at dette kan være en tidkrevende prosess!
			</p>
			<form method="post" action="'.$this->generateURL(array('noprint=true','dofixthumbs')).'">
				<input type="submit" value="Start" />
			</form>
		';
	
	}
	
	function fixThumbs() {

		if (!$this->is_allowed("w", $this->current_dir)){
			$this->permissionDenied();
			return;
		}
		
		$this->initializeImagesInstance();
		
		$res = $this->query("SELECT id FROM $this->table_images WHERE parent='$this->current_dir'");
		while ($row = $res->fetch_assoc()) {
			$img_id = $row['id'];
			$this->makeThumbs($img_id);		
		}
		
		$this->redirect($this->generateURL(""),"Operasjonen var vellykket!");
	}
	
}

?>
