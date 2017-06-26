<?php
class cms_basic extends base {
	
	var $preferred_lang = "NA";

	var $_pageId = 0;
	var $_classId = 0;
	var $_wideMode = false;
	var $page_title = '';
	var $current_path;
	var $current_class;
	var $current_instance;
	var $pagenotfound = false;

	var $rss_url = '';
	
	var $table_rights = "rights";
	var $table_changelog = "changelog";
	var $table_languages = "languages";

	var $table_pages = "cms_pages";
	var $table_globaloptions = "cms_globaloptions";
	var $table_classoptions = "cms_classoptions";
	var $table_pageoptions = "cms_pageoptions";
	var $table_useroptions = "cms_useroptions";

	public $table_images = "images";
	public $table_pagepermissions = "cms_pagepermissions";
	public $table_classpermissions = "cms_classpermissions";
	public $table_classes = "cms_classes";
	public $table_classlabels = "cms_classlabels";
	public $table_pagelabels = "cms_pagelabels";
	
	/* Callbacks */

	var $lookup_group;
	var $lookup_member;
	var $lookup_memberimage;
	var $lookup_webmaster;
	
	var $allow_edit = false;
	var $listgroups_function;
	var $htmleditdir;
	var $login_identifier = 0;

	var $webmaster_ident = -1;
	var $js_libs = array();
	
	var $debug;
	
	var $_errors = '';
		
	var $_templatePageNotFound = '
		<div class="alpha-shadow" style="float:right;"><div class="inner_div">
			<img src="%imagedir%scream.gif" alt="Scream" style="width: 100px;" />
		</div></div>
		<h1>Skrik!</h1>
		<p>
		  Den etterspurte siden ble ikke funnet!
		</p>
		<p>
			%url%
		</p>';

	
	var $template_filelisting = "
			<div class='filelisting'>
				%files%
			</div>
		";
	var $template_filelisting_file = "	
				<div class='file%classno%' style='background-image: url(%icon%)'%extras%\" >
					<a href='%url%' class='title'>%name%</a> <em>(%class%)</em>
					<div class='description'>%description%</div>
				</div>
                ";

    private $_templateService;
    public function setTemplateService($t) { $this->_templateService = $t; }

    function setAllowIndexing($l) { 
        if (isset($this->_templateService)) {
            $this->_templateService->setAllowIndexing($l);
        }
    }

	function checkIfSpecialUrl() {
		if (
			(count($this->coolUrlSplitted) > 0) 
			&& ($this->coolUrlSplitted[0] == "oppdater-min-info")
			&& (!isset($_GET['editpage']))
			&& (!isset($_GET['noprint']))
		) {
			if (!empty($this->login_identifier)) {
				$this->redirect("/medlemsliste/medlemmer/".$this->login_identifier."/?editprofile");
			}
		}
	
	}
	
	function getErrors() { return $this->_errors; }
	function setPageId($i) { $this->_pageId = intval($i); }
	function getPageId() { return $this->_pageId; }
	function setClassId($i) { $this->_classId = intval($i); }
	function isWideMode() { return $this->_wideMode; }

	function initialize(){
	
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
		
		$this->action = "";
		
		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		if (!strpos($request_uri,$this->coolUrlPrefix)){
			$this->action = 'viewpage';
			$tmp = $this->coolUrlPrefix;
			$this->coolUrlPrefix = "";
			$this->initialize_base();
			$this->coolUrlPrefix = $tmp;
		} else {
			$this->initialize_base();
		}

		if (!isset($_GET['noprint'])){
			// DEBUG: 
			//print_r($this->coolUrlSplitted);
		}
		
		
		$this->breadcrumb = array('<a href="'.$this->generateCoolUrl("/").'">Hjem</a>');

		$this->setPageId(1); // rot-mappen
		$this->current_path = "";	

		if (count($this->coolUrlSplitted) > 0){

			foreach ($this->coolUrlSplitted as $s){
				$res = $this->query("SELECT 
						$this->table_pages.id, 
						$this->table_classes.shortname,
						$this->table_classes.js_libs,
						$this->table_pagelabels.value as header
					FROM 
						$this->table_classes,
						$this->table_pages
					LEFT JOIN
						$this->table_pagelabels
					ON 
						($this->table_pagelabels.label='page_header'
						AND $this->table_pagelabels.lang='$this->preferred_lang'
						AND $this->table_pagelabels.page=$this->table_pages.id)
					WHERE 
						$this->table_pages.pageslug='".addslashes($s)."'
							AND 
						$this->table_pages.parent='$this->_pageId'
							AND
						$this->table_pages.class=$this->table_classes.id"
				);
				if ($res->num_rows != 1){
					$this->page_title = "Siden finnes ikke";
					$this->pagenotfound = true;
					$_SESSION['urlnotfound'] = "/".join("/",$this->coolUrlSplitted);
					$this->setPageId(0);
					$this->setClassId(0);
					return;
				}
				
				$row = $res->fetch_assoc();
				$this->current_path .= "/$s";
				$this->setPageId($row['id']);
				$this->current_class = $row['shortname'];
				$this->js_libs = explode(",",$row['js_libs']);
				$header = stripslashes($row['header']);
				if (empty($header)) $header = "Side uten navn";
				
				if ($row['shortname'] == 'dir'){
					$this->breadcrumb[] = '<a href="'.$this->generateCoolUrl($this->current_path."/").'">'.$header.'</a>';
				} else {
					$this->breadcrumb[] = '<a href="'.$this->generateCoolUrl($this->current_path."/").'">'.$header.'</a>';
				}
				if ($row['shortname'] != "dir") break;
			}
			$this->page_title = $header;
			
		} else {
	
			$res = $this->query("SELECT 
					$this->table_pages.id, 
					$this->table_classes.shortname,
					$this->table_classes.js_libs,
					$this->table_pagelabels.value as header
				FROM 
					$this->table_classes,
					$this->table_pages
				LEFT JOIN
					$this->table_pagelabels
				ON 
					($this->table_pagelabels.label='page_header'
					AND $this->table_pagelabels.lang='$this->preferred_lang'
					AND $this->table_pagelabels.page=$this->table_pages.id)
				WHERE 
					$this->table_pages.pageslug='start'
						AND 
					$this->table_pages.parent='$this->_pageId'
						AND
					$this->table_pages.class=$this->table_classes.id"
			);
			if ($res->num_rows != 1){
				$this->page_title = "Siden finnes ikke";
				$this->pagenotfound = true;
				$_SESSION['urlnotfound'] = "/".join("/",$this->coolUrlSplitted);
				return;
			}
			
			$row = $res->fetch_assoc();
			// $this->current_path .= "/$s";
			$this->setPageId($row['id']);
			$this->current_class = $row['shortname'];
			$this->js_libs = explode(",",$row['js_libs']);
			$header = stripslashes($row['header']);
			if (empty($header)) $header = "Side uten navn";
			/*
			if ($row['shortname'] == 'dir'){
				$this->breadcrumb[] = "<a href='".$this->generateCoolUrl($this->current_path."/")."'>$header</a>";
			} else {
				$this->breadcrumb[] = "<a href='".$this->generateCoolUrl($this->current_path."/")."'>$header</a>";
			}*/
	
			$this->page_title = $header;
	
		}
	
		
		if (isset($_GET['current_image']) && is_numeric($_GET['current_image'])){
			$this->current_image = $_GET['current_image']; 
		}
		
	}

	function run(){
		
		if ($this->pagenotfound){		
			$r1a = array();					$r2a = array();
			$r1a[1]  = "%imagedir%";		$r2a[1]  = $this->image_dir;
			$r1a[2]  = "%url%";				$r2a[2]  = htmlspecialchars($_SESSION['urlnotfound']);						
			return str_replace($r1a, $r2a, $this->_templatePageNotFound);
		}
		
		$wLookup = $this->lookup_webmaster;
		$this->webmaster_ident = $wLookup();
		$this->webmaster_ident = $this->webmaster_ident->ident;
		
		$thePage = $this->printPage();
		return $thePage;

	}
	
	function getDocumentTitle() {
		if (substr($_SERVER['REQUEST_URI'],strlen(ROOT_DIR)) == '/') 
			return "18. Bergen";
		else
			return $this->page_title.' – '."18. Bergen";
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
				if ($group && ($member->rights == $rights) && !in_array($ownergroup,$member->memberof)) break; 
				if ($debug) print "access granted!";
				return true;
			case "webmaster":
				if ($debug) print "access-settings allows only webmaster. ";
				break;
		}
		if ($debug) print "access denied!";
		return false;
	}
	
	function getPermissionLevel($usr,$grp){
		if ($usr == 0){
			return 0;
		} else {
			$me = call_user_func($this->lookup_member,$usr);
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
	
	/* #################################################### PRINT PAGE ####################################################################*/
	
	function initializePage() {

		$id = $this->_pageId;
		
		if (!intval($id)) {
		
			// Page not found
			$r1a = array();					$r2a = array();
			$r1a[1]  = "%imagedir%";		$r2a[1]  = $this->image_dir;
			$r1a[2]  = "%url%";				$r2a[2]  = htmlspecialchars($_SESSION['urlnotfound']);						
			$this->_errors .= str_replace($r1a, $r2a, $this->_templatePageNotFound);			
			return false;
		}
			
		$res = $this->query(
			"SELECT class, parent, owner, ownergroup, debug, print_header, print_breadcrumb, fullslug 
			FROM $this->table_pages WHERE id='$id'"
		);
		
		if ($res->num_rows < 1){ 
			$this->_errors .= "<h1>Siden eller mappen finnes ikke</h1>
			<p>
				Siden du etterspurte ble ikke funnet. Ikke godt å si hvor den har tatt veien, men 
				webmaster kan kanskje svare om du spør. 
			</p>";
			return false;
		}
		
		$row = $res->fetch_assoc();
		$this->setClassId($row['class']);
		
		if ($this->_classId == 1){		// If the current page is a directory
		
			// Check if there is a 'start' page
			$res = $this->query(
				"SELECT id, class, parent, owner, ownergroup, debug, print_header, 
				print_breadcrumb, fullslug
				FROM $this->table_pages WHERE parent='$id' AND pageslug='start'"
			);
			if ($res->num_rows != 1){	
				// No 'start' page found. We will show a directory listing.
				return true;
			}
			$row = $res->fetch_assoc();
			$id = $row['id'];
			$this->setPageId($id);
			$this->setClassId($row['class']);
		}
		
		// DEBUG: print "init page: $id, class id: $this->_classId ";
		
		$parent = $row['parent'];
		$owner = $row['owner'];
		$ownergroup = $row['ownergroup'];
		$this->print_header = $row['print_header'];
		$print_breadcrumb = $row['print_breadcrumb'];
		$fullslug = $row['fullslug'];
				
		$this->debug = $debug = ($row['debug'] == '1');				
		if ($debug) print "<pre style='font-size:12px; border: 1px dotted #888888; padding: 10px; background: #ffffff;'>\n";
		
		$res = $this->query("SELECT 
				filename, dependencies, js_libs, classname, shortname				
			FROM
				$this->table_classes
			WHERE
				$this->table_classes.id = '$this->_classId'"
		);
		$classInfo = $res->fetch_assoc();
		if ($debug) print "Filename: ".$classInfo['filename']."\nDependencies: ".$classInfo['dependencies']."\n";
		$this->current_class = $classInfo['shortname'];
		$this->js_libs = explode(",",$classInfo['js_libs']);
		// Load necessary files
		$classname = $classInfo['classname'];
		$filename = $classInfo['filename'];
		if (!empty($classInfo['dependencies'])){
			$dependencies = explode(",",$classInfo['dependencies']);
			foreach ($dependencies as $d) {
				require_once($d);
			}
		}
		require_once($filename);

	/***** Create class instance *****/

		$this->current_instance = new $classname();
		$this->current_instance->print_breadcrumb = $print_breadcrumb;
		
	/***** Set options, labels and permissions ******/
		
		if (!$this->prepareClassInstance($this->current_instance, $this->_pageId, $this->_classId, $parent, $owner, $ownergroup, $fullslug)) {
			// Page initialization failed.
			return false;
		}
		if (isset($this->current_instance->wideMode) && ($this->current_instance->wideMode)) {
			$this->_wideMode = true;
		}

		if ($debug) print "</pre>";
		
		return true;
	}
	
	function getRssUrl() {
		if (isset($this->current_instance)) 
			return $this->current_instance->getRssUrl();
		else 
			return '';
	}	
	
	
	function printPage(){
		$output = "";
		$breadcrumb = "";
		$output .= "\n\n<!-- ##### Page id: ".$this->_pageId.", class id: ".$this->_classId." ##### -->\n\n";
		if ($this->current_class == 'dir') {
			$output .= $this->listDir();
		} else {
			if (!$this->current_instance->allow_read) {
				$output .= $this->permissionDenied();
			} else {
				$output .= "<div id='edit_page_div' style='float:right'></div>";
				if ($this->print_header) $output .= "<h1>".$this->current_instance->header."</h1>\n";				
				$output .= $this->current_instance->run();
				if (isset($this->current_instance->document_title) && !empty($this->current_instance->document_title))
					$this->page_title .= ': '. $this->current_instance->document_title;
			}		
		}
		return $output;
	}
	
	function getBreadCrumb() {
		$breadcrumb = "";
		if ($this->current_class == 'dir') {	
			$breadcrumb = "Du er her: ".implode(" > ",$this->breadcrumb)."";
		} else {
			if ($this->current_instance && $this->current_instance->print_breadcrumb) {
				$breadcrumb = 'Du er her: '.implode(" > ",$this->breadcrumb);
			}		
		}
		return $breadcrumb;	
	}
	
	/* ######################################## OUTPUT DIRECTORY LISTING ################################################################*/
	
	function listDir() {
		$header = $this->getHeader($this->_pageId);
		$output = "
			<h1>$header</h1>
		";
		$lp = $this->listPages();
		if ($lp == "empty") {
			$output .= "<p><i>Denne mappen er tom eller den inneholder bare bilder.</i></p>";
		} else if ($lp == "hidden") {
			if ($this->isLoggedIn()) {
				$output .= "<p><i>Beklager, denne mappen inneholder kun skjulte elementer.</i></p>";
			} else {
				$output = innlogging::printNoAccessNotLoggedInDlg();
			}
		} else {
			$output .= $lp;
		}
		/*
		$li = $this->listImages();
		$li = "empty";
		if (($lp == "empty") && ($li == "empty")){
			print "<p><i>Mappen er tom</i></p>";
		}
		*/
		//$this->notSoFatalError("Denne mappen har ingen index-side.");
		return $output;
	}
	
	function listPages(){
		
		$lang = $this->preferred_lang;
		
		$output = "";
		
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
				$this->table_classes, $this->table_pages
			LEFT JOIN
				$this->table_pagelabels
				ON
				$this->table_pagelabels.page=$this->table_pages.id
				AND
				$this->table_pagelabels.label='page_header'
				AND
				$this->table_pagelabels.lang='$lang'
			WHERE 
				$this->table_pages.parent='".$this->_pageId."'
					AND
				$this->table_pages.class=$this->table_classes.id
			ORDER BY 
				$this->table_pages.class,
				$this->table_pagelabels.value"
		);
		if ($res->num_rows == 0) return "empty";
		$groups = call_user_func($this->list_groups);
		$classNo = 1;
		$i = 0;
		while ($row = $res->fetch_assoc()){
			if ($this->is_allowed("r",$row['id'])) {
				$i++;
				$classNo = !$classNo;
				if ($row['shortname'] == "dir"){
					$url_page = ($this->useCoolUrls ?
						$this->generateCoolURL($this->current_path . "/" . $row['pageslug']."/") :
						$this->generateURL('dyndir='.$row['id'])						
					);
				} else if ($row['pageslug'] == "start") {
					$url_page = ($this->useCoolUrls ?
						ROOT_DIR.$this->current_path."/" :
						$this->generateURL('dynpage='.$row['id'])						
					);
				} else {
					$url_page = ($this->useCoolUrls ?
						ROOT_DIR.$this->current_path."/".$row['pageslug']."/" :
						$this->generateURL('dynpage='.$row['id'])						
					);
				}
				
				$description = "<p>".stripslashes($row['description'])."</p>";
				
				$header = stripslashes($row['header']);
				if (empty($header)) $header = stripslashes($row['friendlyname'])." uten navn"; 
				
				$r1a = array();					$r2a = array();
				$r1a[1]  = "%icon%";			$r2a[1]  = $this->image_dir.stripslashes($row['icon']);
				$r1a[2]  = "%url%";				$r2a[2]  = $url_page;
				$r1a[3]  = "%name%";			$r2a[3]  = ($row['pageslug'] == "start") ? "<strong>$header</strong>" : $header;
				$r1a[4]  = "%class%";			$r2a[4]  = stripslashes($row['friendlyname']);
				$r1a[5]  = "%classno%";			$r2a[5]  = $classNo+1;
				$r1a[6]  = "%description%";		$r2a[6]  = $description;	
				$r1a[7]  = "%id%";				$r2a[7]  = $i;			
				$r1a[8]  = "%extras%";			$r2a[8]  = ($this->action == 'viewpage') ? "": " onmouseover=\"showOptions('options_$i')\" onmouseout=\"hideOptions('options_$i')";	
							
				$output .= str_replace($r1a, $r2a, $this->template_filelisting_file);
					
				if ($this->action != 'viewpage') {
					$output .= "<script type='text/javascript'><!--
							getStyleObject('options_$i').display = 'none';
						--></script>";
				}
			}

		}
		if ($i == 0) return "hidden";
		$r1a = array();					$r2a = array();
		$r1a[1]  = "%files%";			$r2a[1]  = $output;
		return str_replace($r1a, $r2a, $this->template_filelisting);
	}
	
	/* ######################################## GENERATE PAGE LIST SELECT BOX ################################################################*/
	
	function allPagesDropDown($name, $defaultValue, $permission = "r") {
		$d = "<select name='$name' id='$name' class='textbox'>";
		$d .= $this->recursiveListing(0, "", "", $defaultValue, $permission);
		$d .= "</select>";
		return $d;
	}
	
	function recursiveListing($dir_id, $parenthesis, $pageList, $defaultVal, $permission = "r"){

		$res = $this->query("SELECT 
					$this->table_pages.id,
					$this->table_pages.owner,
					$this->table_pages.ownergroup,
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
				ORDER BY $this->table_pages.id"
			);
		while ($row = $res->fetch_assoc()){
				
			$url = "/bergenvs/".(empty($whereWeAre) ? "" : implode("/",$whereWeAre)."/" ).$row['pageslug']."/";
			if ($this->is_allowed($permission,$row['id'])){
				$pageList .= "<option value='".$row["id"]."' ".(($defaultVal == $row['id']) ? "selected='selected'" : "").">$parenthesis".stripslashes($row["header"])."</option>\n				";	
			}			
			if ($row['class'] == '1') {
				$whereWeAre[] = $row['pageslug'];
				$pageList = $this->recursiveListing($row['id'],"$parenthesis &nbsp;&nbsp;&nbsp;",$pageList, $defaultVal, $permission);
				array_pop($whereWeAre);
			}
		}
		return $pageList;
	}
	
	/* ######################################## METHODS AVAIBLE TO THE PAGES ################################################################*/
	
	function prepareClassInstance(&$instance, $page_id = -1, $class_id = -1, $parent = -1, $owner = -1, $ownergroup = -1, $fullslug = "") {
		if ($class_id == -1 || $parent == -1) {
			$res = $this->query(
				"SELECT class, parent, owner, ownergroup, fullslug FROM $this->table_pages WHERE id='$page_id'"
			);
			$row = $res->fetch_assoc();
			$class_id = intval($row['class']);
			$parent = $row['parent'];
			$owner = $row['owner'];
			$ownergroup = $row['ownergroup'];
			$fullslug = $row['fullslug'];
		}
		if ($this->debug) print "fullslug = $fullslug\n";

		$e = strlen($fullslug)-6;
		if (strrpos($fullslug,'/start') == $e) {
			$fullslug = substr($fullslug,0,$e);
		}
		$instance->fullslug = $fullslug;

		if ($page_id == -1) {
			/*  The class is not to be prepared for output, 
				just for using generic utility functions
			*/
			$instance->header = "n/a";
			if (!$this->setGenericOptions($instance, $class_id, $page_id, $parent)) return false;
			if (!$this->setGlobalOptions($instance)) return false;

		} else {
			$instance->header = $this->getHeader($page_id);
			if (!$this->setGenericOptions($instance, $class_id, $page_id, $parent)) return false;
			if (!$this->setGlobalOptions($instance)) return false;
			if (!$this->setClassSpecificOptions($instance, $class_id, $page_id)) return false;
			if (!$this->setPermissions($instance, $class_id, $page_id, $ownergroup)) return false;
			if (!$this->setLabels($instance, $class_id)) return false;
		}
		return true;
	}
	
	function getHeader($page_id = 0) {
	
		if (empty($page_id)) $page_id = $this->_pageId; 
	
		// Set header in preffered language
		$res = $this->query("SELECT 
				$this->table_pagelabels.value as header
			FROM 
				$this->table_pages 
			LEFT JOIN
				$this->table_pagelabels
				ON $this->table_pagelabels.page='$page_id'
				AND $this->table_pagelabels.label='page_header'
				AND $this->table_pagelabels.lang='$this->preferred_lang'
			WHERE 
				$this->table_pages.id='$page_id'"
		);
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			if (!empty($row['header'])) {
				return stripslashes($row['header']);
			}
		}				
		
		// Set header in default language
		$res = $this->query("SELECT 
				$this->table_pagelabels.value as header
			FROM 
				$this->table_pages 
			LEFT JOIN
				$this->table_pagelabels
				ON $this->table_pagelabels.page='$page_id'
				AND $this->table_pagelabels.label='page_header'
				AND $this->table_pagelabels.lang='$this->default_lang'
			WHERE 
				$this->table_pages.id='$page_id'"
		);
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			if (!empty($row['header'])) {
				return stripslashes($row['header']);
			}
		}
	
		// No header found at all. Sets some default name
		return "Untitled page";
	}
	
	function getPageOptionValue($page_id, $option_name, $class_id = -1) {
		if ($class_id == -1) {
			$res = $this->query(
				"SELECT class FROM $this->table_pages WHERE id='$page_id'"
			);
			$row = $res->fetch_assoc();
			$class_id = intval($row['class']);
		}
		
		$res = $this->query(
			"SELECT 
				$this->table_classoptions.name,
				$this->table_classoptions.defaultvalue as class_value,
				$this->table_classoptions.prefix,
				$this->table_pageoptions.value as page_value
			FROM 
				$this->table_classoptions
			LEFT JOIN 
				$this->table_pageoptions
					ON
				$this->table_pageoptions.page='$page_id'
					AND
				$this->table_pageoptions.name=$this->table_classoptions.name
			WHERE 
				$this->table_classoptions.class = '$class_id'
				AND $this->table_classoptions.name = \"".addslashes($option_name)."\"
			"
		);
		if ($res->num_rows != 1) {
			$this->fatalError("feil antall rader returnert (cms_basic getPageOptionValue)");
		}
		$row = $res->fetch_assoc();
		$name = stripslashes($row['name']);
		$prefix = stripslashes($row['prefix']);
		if ($row['page_value'] != '') 
			$value = stripslashes($row['page_value']);
		else
			$value = stripslashes($row['class_value']);

		return $prefix.$value;
	}
	
	function setClassSpecificOptions(&$instance, $class_id, $page_id) {
		
		if ($this->debug) print "\n";
		
		$user_id = $this->login_identifier;

		$res = $this->query(
			"SELECT 
				$this->table_classoptions.name,
				$this->table_classoptions.defaultvalue as class_value,
				$this->table_classoptions.prefix,
				$this->table_classoptions.required,
				$this->table_pageoptions.value as page_value,
				$this->table_useroptions.value as user_value
			FROM 
				$this->table_classoptions
			LEFT JOIN 
				$this->table_pageoptions
					ON
				$this->table_pageoptions.page='$page_id'
					AND
				$this->table_pageoptions.name=$this->table_classoptions.name
			LEFT JOIN 
				$this->table_useroptions
					ON
				$this->table_useroptions.page='$page_id'
					AND
				$this->table_useroptions.user='$user_id'
					AND
				$this->table_useroptions.name=$this->table_classoptions.name
			WHERE 
				$this->table_classoptions.class = '$class_id'
			"
		);		
		while ($row = $res->fetch_assoc()) {
			$name = stripslashes($row['name']);
			$prefix = stripslashes($row['prefix']);
			if ($row['user_value'] != '')
				$value = stripslashes($row['user_value']);			
			else if ($row['page_value'] != '') 
				$value = stripslashes($row['page_value']);
			else
				$value = stripslashes($row['class_value']);
			if ($value == '' && $row['required'] == '1') {
				$this->_errors .= $this->notSoFatalError("Denne siden (".$instance->fullslug.") er ikke ferdig satt opp. <br />
					Innstillingen '".stripslashes($row['name'])."' mangler en verdi.");
				return false;
			}
			// DEBUG: print "(class $class_id)->$name = $value<br />\n";
			$instance->$name = $prefix.$value;
			if ($this->debug) print "$name = $prefix$value\n";
		}
		return true;
		
	}
	
	function setGenericOptions(&$instance, $class_id = -1, $page_id = -1, $parent = -1) {
		
		$preferredLang = $_SESSION['lang'];
		
		$f = explode("/",$_SERVER['SCRIPT_FILENAME']);
		array_pop($f);
		$path_to_www = implode("/",$f);
		array_pop($f);
		$path_to_includes = implode("/",$f)."/includes";
		$path_to_classes = implode("/",$f)."/includes/classes";

		#$instance->cms = $this;
        $instance->setDbLink($this->getDbLink());
        $instance->setCMS($this);
		$instance->useCoolUrls = true;
		$instance->coolUrlPrefix = trim($this->current_path,"/"); 
		if ($this->debug) print "\ncoolUrlPrefix = $instance->coolUrlPrefix\n";
		$instance->table_languages = $this->table_languages;
		$instance->default_lang = $this->default_lang;
		$instance->image_dir = '/images/';
		$instance->class_id = $class_id;
		$instance->preferred_lang = $preferredLang;
		$instance->local_flag = "<img src=\"".$instance->image_dir."flags/".$_SESSION['lang'].".gif\" alt=\"".$_SESSION['lang']."\" style=\"border: 1px solid #666;\" />";
		$instance->path_to_www = $path_to_www;
		$instance->path_to_includes = $path_to_includes;
		$instance->path_to_classes = $path_to_classes;
		if (!empty($this->login_identifier)) $instance->login_identifier = $this->login_identifier;


		if ($this->debug) print "preferred_lang = $instance->preferred_lang\n";
		if ($this->debug) print "image_dir = $instance->image_dir\n";
		if ($this->debug) print "path_to_www = $instance->path_to_www\n";
		if ($this->debug) print "path_to_includes = $instance->path_to_includes\n";
		if ($this->debug) print "path_to_classes = $instance->path_to_classes\n";
		if ($this->debug) print "page_id = $page_id\n";
		if ($this->debug) print "parent_dir = $parent\n";
		
		$instance->get_useroptions = $this->get_useroptions;
		$instance->lookup_member = $this->lookup_member;
		$instance->lookup_memberimage = $this->lookup_memberimage;
		$instance->lookup_forumimage = $this->lookup_forumimage;
		$instance->lookup_group = $this->lookup_group;
		$instance->lookup_webmaster = $this->lookup_webmaster;
		$instance->list_groups = $this->list_groups;
		$instance->list_members = $this->list_members;
		$instance->add_to_breadcrumb = $this->add_to_breadcrumb;
		$instance->isValidEmail = $this->isValidEmail;
		$instance->make_memberlink = $this->make_memberlink;
		$instance->make_grouplink = $this->make_grouplink;
		$instance->prepare_classinstance = $this->prepare_classinstance;
		$instance->is_allowed = "is_allowed";
		$instance->table_useroptions = DBPREFIX."cms_useroptions";
		$instance->table_pages = $this->table_pages;
		$instance->table_pagelabels = $this->table_pagelabels;
		$instance->table_classes = $this->table_classes;
		$instance->table_classlabels = $this->table_classlabels;
		
		$instance->page_id = $page_id;
		$instance->parent_dir = $parent;
		
		$instance->error_function = $this->error_function; // base
		$instance->eventlog_function = $this->eventlog_function; // base
		$instance->errorlog_function = $this->errorlog_function; // base
		$instance->permission_denied_function = $this->permission_denied_function; // base
		
		return true;
	}
	
	function setGlobalOptions(&$instance) {
		if ($this->debug) print "\n";

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
			if ($value == "" && $row['required'] == '1') {
				$this->_errors .= $this->notSoFatalError("Denne siden er ikke ferdig satt opp. <br />
					Den globale nnstillingen '$name' mangler en verdi.");
				return false;
			}
			//print $classname."->".$name." = ".$value."\n";
			$instance->$name = $prefix.$value;
			if ($this->debug) print "$name = $prefix$value\n";
		}
		return true;
	}
	
	function setPermissions(&$instance, $class_id, $page_id, $ownergroup) {

		$res = $this->query(
			"SELECT 
				$this->table_classpermissions.name as cname,
				$this->table_classpermissions.default_who,
				$this->table_classpermissions.default_rights,
				$this->table_classpermissions.default_groupowner,
				$this->table_pagepermissions.name as pname,
				$this->table_pagepermissions.who,
				$this->table_pagepermissions.rights,
				$this->table_pagepermissions.groupowner
			FROM 
				$this->table_classpermissions
			LEFT JOIN 
				$this->table_pagepermissions
			ON
				$this->table_pagepermissions.page='$page_id'
					AND
				$this->table_pagepermissions.name=$this->table_classpermissions.name
			WHERE 
				$this->table_classpermissions.class = '$class_id'
				OR
				$this->table_classpermissions.class = '0'				
			"
		);
		$wLookup = $this->lookup_webmaster;
		if (!empty($wLookup)) {
			$webmaster = $wLookup();
		} else {
			$webmaster = -1;
		}
		if ($this->debug) print "\n";
		if (!empty($this->login_identifier)) {
			$member = call_user_func($this->lookup_member, $this->login_identifier);
		}
		while ($row = $res->fetch_assoc()) {
			if (empty($row['pname'])) {
				$name = stripslashes($row['cname']);
				$who = stripslashes($row['default_who']);
				$rights = stripslashes($row['default_rights']);
				$group = (stripslashes($row['default_groupowner']) == "1");
			} else {
				$name = stripslashes($row['pname']);
				$who = stripslashes($row['who']);
				$rights = stripslashes($row['rights']);
				$group = (stripslashes($row['groupowner']) == "1");			
            }
			switch ($who) {
				case "all":
					if ($this->debug) print "$name = true\n";
					$instance->$name = true;
					break;
				case "loggedin":
					if (empty($this->login_identifier)) break;
					if ($webmaster->ident == $this->login_identifier){
						if ($this->debug) print "$name = true\n";
						$instance->$name = true;
						break;
					}
                    if ($member->rights < $rights) break;
                    if ($group && ($member->rights == $rights) && !in_array($ownergroup,$member->memberof)) break;
					if ($this->debug) print "$name = true\n";
					$instance->$name = true;
					break;
				case "webmaster":
					if (empty($this->login_identifier)) break;
					if ($webmaster->ident != $this->login_identifier) break;
					if ($this->debug) print "$name = true\n";
					$instance->$name = true;
					break;				
			}
		}
		return true;
	}
	
	function setLabels(&$instance, $class_id) {

		$res = $this->query("SELECT 
				label, value
			FROM
				$this->table_classlabels
			WHERE
				(class = '$class_id' OR class = '0') 
				AND lang = '$this->default_lang'
				"
		);
		while ($row = $res->fetch_assoc()) {
			$label = "label_".stripslashes($row['label']);
			$value = stripslashes($row['value']);			
			$instance->$label = $value;
			/* DEBUG:
			if ($class_id == 16) {
				print "$label = $value<br />";
			}
			*/
		}
		$res = $this->query("SELECT 
				label, value
			FROM
				$this->table_classlabels
			WHERE
				(class = '$class_id' OR class = '0') 
				AND lang = '$this->preferred_lang'
				"
		);
		while ($row = $res->fetch_assoc()) {
			$label = "label_".stripslashes($row['label']);
			$value = stripslashes($row['value']);			
			$instance->$label = $value;
		}
		return true;
	}
	
	function getUserOptions($instance, $option_name, $users = array()) {
	
		$class_id = $instance->class_id;
		$page_id = $instance->page_id;
		
		$res = $this->query(
			"SELECT 
				$this->table_classoptions.defaultvalue as class_value,
				$this->table_pageoptions.value as page_value
			FROM 
				$this->table_classoptions
			LEFT JOIN 
				$this->table_pageoptions
				ON $this->table_pageoptions.page='$page_id'
				AND $this->table_pageoptions.name=$this->table_classoptions.name
			WHERE 
				$this->table_classoptions.class = '$class_id'
				AND $this->table_classoptions.name='$option_name'
			"
		);
		$row = $res->fetch_assoc();
		$opts = array();
		if (empty($users)) {
			$usersObj = call_user_func($this->list_members);
			foreach ($usersObj as $i => $o) $users[] = $o->ident;
		}
		if (empty($row['page_value']))
			for ($i = 0; $i < count($users); $i++) $opts[$users[$i]] = stripslashes($row['class_value']);
		else
			for ($i = 0; $i < count($users); $i++) $opts[$users[$i]] = stripslashes($row['page_value']);
		
		$users = implode(",",$users);
		$res = $this->query(
			"SELECT 
				$this->table_useroptions.user,
				$this->table_useroptions.value
			FROM 
				$this->table_useroptions
			WHERE 
				$this->table_useroptions.page='$page_id'
				AND $this->table_useroptions.name='$option_name'
				AND $this->table_useroptions.user IN ($users)
			"
		);
		while ($row = $res->fetch_assoc()) {
			$opts[$row['user']] = stripslashes($row['value']);
		}
		
		return $opts;
	}
	
	function addToBreadcrumb($str) {
		$this->breadcrumb[] = $str;
	}
	
	function sitemapReadDir($c,&$handle,&$xml) {

		$lang = 1;

		$res = $this->query("SELECT 
				$this->table_pages.id as page_id,
				$this->table_pages.class as class_id,
				$this->table_pages.lastmodified,
				$this->table_pages.fullslug AS slug,
				$this->table_classes.classname,
				$this->table_classes.filename,
				$this->table_classes.dependencies,
				$this->table_classes.shortname,
				$this->table_classes.friendlyname,
				$this->table_pagelabels.value AS pagetitle
			FROM 
				($this->table_classes, $this->table_pages)
			LEFT JOIN
				$this->table_pagelabels
			ON
				($this->table_pagelabels.page=$this->table_pages.id
				AND
				$this->table_pagelabels.label='page_header'
				AND
				$this->table_pagelabels.lang='$lang')
			WHERE 
				$this->table_pages.parent='".$c."'
					AND
				$this->table_pages.class=$this->table_classes.id
			ORDER BY 
				$this->table_pages.class,
				$this->table_pagelabels.value"
		);
		while ($row = $res->fetch_assoc()) {
			$page_id = intval($row['page_id']);
			$class_id = intval($row['class_id']);
			
			$res2 = $this->query(
				"SELECT 
					$this->table_classpermissions.default_who,
					$this->table_pagepermissions.who
				FROM 
					$this->table_classpermissions
				LEFT JOIN 
					$this->table_pagepermissions
				ON
					$this->table_pagepermissions.page='$page_id'
						AND
					$this->table_pagepermissions.name=$this->table_classpermissions.name
				WHERE 
					$this->table_classpermissions.name='allow_read' AND
					($this->table_classpermissions.class = '$class_id'
					OR
					$this->table_classpermissions.class = '0')			
				"
			);
			$row2 = $res2->fetch_assoc();
			if (isset($row2['who'])) $who = $row2['who'];
			else $who = $row2['default_who'];
			
			if ($who != 'all') {
				print " [X] Permission denied for ".$row['slug']."\n";
			}
			if ($who == 'all') {
				
				$l = "/".$row['slug'];
				$obj = array(
					'loc' => $l,
					'pri' => 1.0,
					'changefreq' => 'always',
					'lastmod' => time()
				);
				
				switch ($row['shortname']) {
					
					case 'dir':
						$this->sitemapAddUrl($obj,$handle,$xml);
						$this->sitemapReadDir($row['page_id'],$handle,$xml);
						break;
						
					case 'html':
						$this->sitemapReadClass($row['classname'],$row['dependencies'],$row['filename'],$row['page_id'],$handle,$xml);
						break;
					
					case 'noteboard':
					case 'article_collection':
					case 'calendar':
					case 'log':
						$this->sitemapAddUrl($obj,$handle,$xml);
						$this->sitemapReadClass($row['classname'],$row['dependencies'],$row['filename'],$row['page_id'],$handle,$xml);
						break;
						
					default: 
						$this->sitemapAddUrl($obj,$handle,$xml);
						break;
					
				}
				
			}
			
		}
		$res->close();
	
	}
	
	function sitemapReadClass($classname,$dependencies,$filename,$page_id,&$handle,&$xml) {
		
		//print "Class: $classname, filename: $filename, page_id: $page_id\n";
		if (!empty($dependencies)){
			$dependencies = explode(",",$dependencies);
			foreach ($dependencies as $d) {
				require_once($d);
			}
		}
		require_once($filename);

		$ci = new $classname();
		$this->prepareClassInstance($ci, $page_id);
		$ci->initialize();
		if ($ci->allow_read) {
			$urls = $ci->sitemapListAllPages();
		} else {
			$urls = array();
			print " [X] Permission denied for page:".$page_id."\n";
		}
		unset($ci);
		
		foreach ($urls as $u) {
			$this->sitemapAddUrl($u,$handle,$xml);
		}
		
	}
	
	function sitemapAddUrl($obj,&$handle,&$xml) {

		$this->totalUrlsWritten++;
		$xml .= "\n\t<url>\n";
		$xml .= "\t\t<loc>".htmlspecialchars('https://'.$_SERVER['SERVER_NAME'].ROOT_DIR.$obj['loc'])."</loc>";
		if (isset($obj['lastmod'])) {
			if (is_numeric($obj['lastmod'])) {
				$xml .= "\n\t\t<lastmod>".date("Y-m-d",$obj['lastmod'])."</lastmod>";
			} else {
				$xml .= "\n\t\t<lastmod>".$obj['lastmod']."</lastmod>";			
			}
		}
		if (isset($obj['changefreq'])) {
			$xml .= "\n\t\t<changefreq>".$obj['changefreq']."</changefreq>";
		}
		if (isset($obj['pri'])) {
			$xml .= "\n\t\t<priority>".$obj['pri']."</priority>";
		}
		$xml .= "\n\t</url>\n";
		$len = strlen($xml);
		if ($len > 32768) {
			echo "   Wrote ".fwrite($handle, $xml)." bytes\n";
			$xml = "";
		}
	}
	
	function generateSitemap() {

		$this->setPageId(1); // rot-mappen
		$this->current_path = "";	
		
		$this->totalUrlsWritten = 0;
		$filename = 'www/sitemap.tmp';
		if (is_writable($filename)) {
		   if (!$handle = fopen($filename, 'w')) {
				 echo "   [ERROR] Cannot open file ($filename)\n";
				 exit();
		   }
		   //echo "   File handle: $filename\n";
		   //print_r($_SERVER);
		   $sitemapXml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemaps/0.9
	http://www.sitemaps.org/schemas/sitemaps/sitemap.xsd"
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';		  
		  
			$this->sitemapReadDir(1,$handle,$sitemapXml);
			
			$sitemapXml .= '
</urlset>
';
			echo "   Wrote ".fwrite($handle, $sitemapXml)." bytes\n";
			$xml = "";

		   fclose($handle);
		
		} else {
		   echo "   [ERROR] The file $filename is not writable\n";
		}
		echo "   Operation complete. ".$this->totalUrlsWritten." urls added.\n";

	}
	
	
}

?>
