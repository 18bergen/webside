<?

class html_page extends base {
	
	var $page_title;
	
	var $table_html = "static_pages";	
	
	var $pageid;
	var $allow_write = false;
	var $memberlookup_function;
	var $webmasterlookup_function;
	var $listgroups_function;
	var $htmleditdir;
	var $login_identifier = 0;
	
	var $CKeditorWidth = 480;
	var $CKeditorHeight = 500;

	var $current_path;
	
	function html_page() {
		$this->table_html = DBPREFIX.$this->table_html;
	}
	
	function initialize() {
		$this->initialize_base();	
	}
	
	function sitemapListAllPages(){
		$urls = array();
		
		$res = $this->query("SELECT lastmodified FROM $this->table_html WHERE id='$this->page_id' AND lang='$this->preferred_lang'");
		$row = $res->fetch_assoc();
		$urls[] = array(
				'loc' => $this->generateURL(""),
				'changefreq' => 'weekly',
				'lastmod' => $row['lastmodified']
			);
		
		return $urls;
	}

	function run(){
		$this->initialize();
	
		array_push($this->getvars, 'action','editpage');
		
		$res = $this->query("SELECT content FROM $this->table_html WHERE id='$this->page_id' AND lang='$this->preferred_lang'");
		if ($res->num_rows < 1){ 	
			$this->query("INSERT INTO $this->table_html (id,lang,format) VALUES
				('$this->page_id','$this->preferred_lang','html')"
			); 
		}

		/* 
		##	Determine and execute requested action
		*/
		
		$actions = array(
			'editPage','savePage','ajaxSaveBackup','selectVersionToEdit','viewPage'
		);
		$action = isset($_GET['action']) ? $_GET['action'] : '';
		if (isset($_GET['editpage'])) $action = 'editPage'; // for backwards compability
		if (!in_array($action,$actions)) $action = 'viewPage';
		return call_user_func(array($this,$action));
/*	
		if (isset($_GET['editpage'])){
			$this->action = 'editpage';
		} else if (isset($_GET['savepage'])){
			$this->action = 'savepage';
		} else if (isset($_GET['savebackup'])){
			$this->action = 'savebackup';
		} else if (isset($_GET['selectVersionToEdit'])){
			$this->action = 'selectVersionToEdit';			
		} else {
			$this->action = 'viewpage';
		}
		
		switch ($this->action){
			case 'editpage':
				return $this->printEditPageForm();
				break;
			case 'savebackup':
				return $this->saveBackup();
				break;
			case 'savepage':
				return $this->savePage();
				break;
			case 'selectVersionToEdit':
				return $this->selectVersionToEdit();
				break;
			case 'viewpage':
				return $this->printPage();
				break;
		}*/

	}
	
	/* #################################################### PRINT PAGE ####################################################################*/

	function viewPage(){
		if (!is_numeric($this->page_id)){ 
			return $this->notSoFatalError("Det ble ikke spesifisert noen side-id.");
		}
		
		$output = "";
		
		if ($this->allow_write) {
			$output .= $this->make_editlink($this->generateURL("editpage"), "Rediger side");
		}

		
		$res = $this->query("SELECT lastmodified,updatedby,content FROM $this->table_html WHERE id='$this->page_id' AND lang='$this->preferred_lang'");
		if ($res->num_rows < 1){ 
			$output .= "<h1>Siden finnes ikke</h1>";
			return $output; 
		}
		$row = $res->fetch_assoc(); 

		
		$output .= "<div class='html_page'>";
		$output .= stripslashes($row['content']);
		$output .= "</div>";

		$output .= "<div style='margin-top:50px;font-style:italic; margin-right:20px; text-align:right; color: #aaa; font-size:9px;'>";
		if (!empty($row['updatedby'])) {
			$u = call_user_func($this->lookup_member,$row['updatedby']);
			$usr = (empty($this->login->identifier)) ? $u->firstname : call_user_func($this->make_memberlink,$u->ident);
			$output .= "Sist oppdatert den ".strftime("%e. %B %Y",$row['lastmodified'])." av ".$usr;
		}
		$output .= "</div>";
		
		return $output;
	}

	/* #################################################### EDIT PAGE ####################################################################*/


	function selectVersionToEdit() {
		if (!isset($_POST['version'])) {
			$this->redirect($this->generateURL('action=editPage'),"Du må velge en versjon!");
		}
		$id = $this->page_id;
		if (!is_numeric($id)){ $this->fatalError("incorrect input"); }
		if (!$this->allow_write){ return $this->permissionDenied(); }

		if ($_POST['version'] == 'backup') {
			$res = $this->query("UPDATE $this->table_html 
				SET 
					content = temp_content,
					temp_timestamp=0, temp_content='', temp_savedby='' 
				WHERE id='$id'"
			);
		} else if ($_POST['version'] == 'original') {
			$res = $this->query("UPDATE $this->table_html 
				SET 
					temp_timestamp=0, temp_content='', temp_savedby='' 
				WHERE id='$id'"
			);
		}
		$this->redirect($this->generateURL('action=editPage'));
	}
	
	function editPage(){
		
		$id = $this->page_id;
		$output = "";
		
		if (!is_numeric($id)) return $this->fatalError("incorrect input");
		if (!$this->allow_write) return $this->permissionDenied();

		$res = $this->query("SELECT 
				$this->table_html.lang, 
				$this->table_html.content, 
				$this->table_html.lockedby, 
				$this->table_html.locktimestamp, 
				$this->table_html.lastmodified, 
				$this->table_html.temp_content,
				$this->table_html.temp_timestamp, 
				$this->table_html.temp_savedby,
				$this->table_languages.fullname
			FROM 
				$this->table_html, $this->table_languages 
			WHERE $this->table_html.id='$id'
			AND $this->table_html.lang=$this->table_languages.code"
		);
		
		if ($res->num_rows < 1){ return $this->notSoFatalError("Siden finnes ikke!"); }
		$pageVersions = array();
		$newerBackupExists = false;
		
		while ($row = $res->fetch_assoc()) {
			
			$pageVersions[] = $row;
			
			if ($row['lockedby'] > 0 && $row['lockedby'] != $this->login_identifier) {
				if ($row['locktimestamp'] < time()-3600) {
					// Opphever låsen, som er for gammel.
					$this->query("UPDATE $this->table_html SET lockedby=0, locktimestamp=0 WHERE id='$id'");
				} else {
					$mf = $this->lookup_member;
					$m = $mf($row['lockedby']);
					$this->notSoFatalError("Denne siden redigeres av $m->fullname og er derfor låst for redigering av andre.
						Siden blir åpnet for redigering igjen når $m->firstname avslutter redigeringen. Dersom $m->firstname
						glemmer å avslutte redigeringen på korrekt måte blir siden uansett åpnet igjen for redigering 
						en time etter vi mottok siste livstegn fra maskinen til $m->firstname. Akkurat nå gjenstår
						".ceil(($row['locktimestamp'] + 3600 - time())/60)." minutt(er).");
					return;
				}
			}
			
					
			if ($row['temp_timestamp'] > $row['lastmodified'] && !empty($row['temp_content'])) {
				$temp_age = time() - $row['temp_timestamp'];
				
				// Hvis backupen er over et døgn er det nok bare å slette den..
				if ($temp_age > 24*3600) {
					$this->query("UPDATE $this->table_html SET temp_timestamp=0, temp_content='', temp_savedby='' WHERE id='$id'");
				}
				
				if ($row['temp_savedby'] == $this->login_identifier) {
				
					$newerBackupExists = true;
				}
			}
			
		}
		
		if ($newerBackupExists) {
			
			$lastmodified = (empty($pageVersions[0]['lastmodified'])) ? "<i>Aldri lagret</i>" : date("d. m Y, H:i",$pageVersions[0]['lastmodified']);
			$output .= $this->infoMessage('

				Det eksisterer en automatisk lagret backup som er nyere enn sist lagrede versjon av siden. <br />
				<ul>
					<li>Backupen ble lagret: '.date('d. m Y, H:i',$pageVersions[0]["temp_timestamp"]).'</li>
					<li>Sist ordinære lagring: '.$lastmodified.'</li>
				</ul>
				Velg hvilken versjon du vil jobbe videre med:
				<br />&nbsp;
				<form method="post" action="'.$this->generateURL('action=selectVersionToEdit').'">
					<input type="radio" name="version" value="backup" />Jobb videre med backup-versjonen.<br />
					<input type="radio" name="version" value="original" />Slett backupen og jobb videre med originalen.<br />
					<br />
					<input type="submit" value="Fortsett" />
				</form>		
			');
				
			
			$tabs = "";
			$divs = "";
			for ($i = 1; $i <= count($pageVersions); $i++) {
				$p = $pageVersions[$i-1];
				$content = stripslashes($pageVersions[$i-1]['content']);
				$tempcontent = stripslashes($pageVersions[$i-1]['temp_content']);
				if ($tabs == "")
					$tabs .= "
							<li class='selected'><a href='#' rel='tcontent1_$i'>Backup ".stripslashes($p['fullname'])."</a></li>
							<li><a href='#' rel='tcontent2_$i'>Original ".stripslashes($p['fullname'])."</a></li>";
				else
					$tabs .= "
							<li><a href='#' rel='tcontent1_$i'>Backup ".stripslashes($p['fullname'])."</a></li>
							<li><a href='#' rel='tcontent2_$i'>Original ".stripslashes($p['fullname'])."</a></li>";
				$divs .= "
						<div id='tcontent1_$i' class='tabcontent'>
							$tempcontent
						</div>
						<div id='tcontent2_$i' class='tabcontent'>
							$content
						</div>";
				
			}
			
			$output .= '
				&nbsp;<br />
				<p>
					Her kan du sammenligne de ulike versjonene:
				</p>
				<ul id="maintab" class="shadetabs">
					'.$tabs.'
				</ul>
			
				<div class="tabcontentstyle">
			
					'.$divs.'
					
				</div>
			
				<script type="text/javascript">
				//<![CDATA[
				
					function onTabChange(newTab) {
						// ignore
					}
					
					//Start Tab Content script for UL with id="maintab" Separate multiple ids each with a comma.
					initializetabcontent("maintab")
				
				//]]>
				</script>
			';			
			return $output;
		}
		
		
		// Opdater pagelock
		$this->query("UPDATE $this->table_html 
			SET lockedby='$this->login_identifier', locktimestamp='".time()."' 
			WHERE id='$id'"
		);
		
		$posturl = $this->generateURL('action=savePage');		
		
		$tabs = "";
		$divs = "";
		for ($i = 1; $i <= count($pageVersions); $i++) {
			$p = $pageVersions[$i-1];
			$content = stripslashes($pageVersions[$i-1]['content']);
			$content = htmlspecialchars($content);
			$lang = $pageVersions[$i-1]['lang'];
			if ($tabs == "")
				$tabs .= "
						<li class='selected'><a href='#' rel='tcontent$i'>".stripslashes($p['fullname'])."</a></li>";
			else
				$tabs .= "
						<li><a href='#' rel='tcontent$i'>".stripslashes($p['fullname'])."</a></li>";
			$divs .= "
					<div id='tcontent$i' class='tabcontent'>
						<textarea name='content_$lang' id='content_$lang' rows='20' cols='60' style='width:".$this->CKeditorWidth."px; height:".$this->CKeditorHeight."px;'>$content</textarea>
					</div>";
			
		}
		
		$output .= '
			<form action="'.$posturl.'" method="post" onsubmit="autoBackup0.stop();">
				<p>
					<input type="submit" name="lagre1" value="    Lagre og fortsett redigering    " /> 
					<input type="submit" name="lagre2" value="    Lagre og vis siden    " />
				</p>
				
				<div id="editorprogress">
					Et øyeblikk... <img src="'.$this->image_dir.'progressbar1.gif" alt="Progressbar" />
				
				</div>
				<div style="display:none;" id="editoruber">

				<ul id="maintab" class="shadetabs">				
					'.$tabs.'
				</ul>
				<div class="tabcontentstyle">
					'.$divs.'
				</div>
				</div>
				<script type="text/javascript">
				//<![CDATA[
					
					var useFckEditor = true;
	
					function onTabChange(newTab) {
						
					}

				//]]>
				</script>			
				<div id="backupstatus">Automatisk backup blir ikke tatt (CKEditor er ikke aktiv).</div>
			</form>
		';
		//print_r($_SESSION);
		//".str_replace("&","&amp;",stripslashes($row['content']))."

		$this->setDefaultCKEditorOptions();
		
		if (strpos($this->fullslug,"patruljer/bever") === 0)
			$defaultUploadPath = "/patruljer/bever";
		else if (strpos($this->fullslug,"patruljer/orn") === 0)
			$defaultUploadPath = "/patruljer/orn";
		else if (strpos($this->fullslug,"patruljer/elg") === 0)
			$defaultUploadPath = "/patruljer/elg";
		else if (strpos($this->fullslug,"patruljer/falk") === 0)
			$defaultUploadPath = "/patruljer/falk";
		else if (strpos($this->fullslug,"patruljer/hjort") === 0)
			$defaultUploadPath = "/patruljer/hjort";
		else if (strpos($this->fullslug,"patruljer/gbp") === 0)
			$defaultUploadPath = "/patruljer/gbp";
		else if (strpos($this->fullslug,"bli-speider") === 0)
			$defaultUploadPath = "/bli-speider";
		else 
			$defaultUploadPath = "/Diverse";

		$backupUrl = $this->generateURL('action=ajaxSaveBackup');

$output .= '

<script type="text/javascript"><!--

var CKsToLoad = '.count($pageVersions).';
var CKsLoaded = 0;
var CKeditors = [];

var autoBackup0 = new AutoBackup("'.$backupUrl.'"); 

function initCKeditors() {

	if (CKEDITOR.env.isCompatible) {

	';
	for ($i = 1; $i <= count($pageVersions); $i++) {
	$lang = $pageVersions[$i-1]['lang'];
	$output .= '
		
		CKeditors['.$i.'] = CKEDITOR.replace( "content_'.$lang.'", { 
			customConfig : "'.LIB_CKEDITOR_URI.'config_18bergen.js",
			toolbar: "BergenVS",
			height: '.$this->CKeditorHeight.',
			width: '.$this->CKeditorWidth.',
			resize_minWidth: '.$this->CKeditorWidth.',
			resize_maxWidth: '.$this->CKeditorWidth.', // disables horizontal scrolling
			filebrowserBrowseUrl: "'.LIB_CKFINDER_URI.'ckfinder.html?type=Vedlegg&start=Vedlegg:'.$defaultUploadPath.'&rlf=0",
			filebrowserUploadUrl: "'.LIB_CKFINDER_URI.'core/connector/php/connector.php?command=QuickUpload&type=Vedlegg&currentFolder='.$defaultUploadPath.'/",
			filebrowserImageBrowseUrl: "'.LIB_CKFINDER_URI.'ckfinder.html?type=Bilder&start=Bilder:'.$defaultUploadPath.'&rlf=0&dts=1",
			filebrowserImageUploadUrl: "'.LIB_CKFINDER_URI.'core/connector/php/connector.php?command=QuickUpload&type=Bilder&currentFolder='.$defaultUploadPath.'/",
			filebrowserFlashBrowseUrl: "'.LIB_CKFINDER_URI.'ckfinder.html?type=Flash&start=Flash:'.$defaultUploadPath.'&rlf=0",
			filebrowserFlashUploadUrl: "'.LIB_CKFINDER_URI.'core/connector/php/connector.php?command=QuickUpload&type=Flash&currentFolder='.$defaultUploadPath.'/",
			on: {
				"instanceReady": function(e) {
					// alert("instance '.$lang.' ready");
					CKeditorLoaded("$lang");
				}
			}
		});
		
		CKFinder.setupCKEditor( CKeditors['.$i.'], {
			basePath: "'.LIB_CKFINDER_URI.'"
		}, "Bilder");		
		
		autoBackup0.addEditor("'.$lang.'",CKeditors['.$i.']);

	';
	}
	$output .= '
	} else {

		$("#editorprogress").hide()
		$("#editoruber").show();

		//Start Tab Content script for UL with id="maintab" Separate multiple ids each with a comma.
		initializetabcontent("maintab")
	}
}

function CKeditorLoaded( lang ) {

	// Ta backup hvert 90de sekund
	CKsLoaded++;
	if (CKsLoaded == CKsToLoad) {
		$("#editorprogress").hide();
		$("#editoruber").show();

		//Start Tab Content script for UL with id="maintab" Separate multiple ids each with a comma.
		initializetabcontent("maintab")
		
		autoBackup0.start();	
	}
}

$(document).ready(function() {
	initCKeditors();
});

//--></script>

';
		
		return $output;
	
	}

	function savePage(){
		
		$now = time();
		
		$id = $this->page_id;
		if (!is_numeric($id)){ $this->fatalError("incorrect input"); }
		if (!$this->allow_write) return $this->permissionDenied();
		$res = $this->query("SELECT 
				lang, content, lockedby, locktimestamp, lastmodified, temp_content, temp_timestamp, temp_savedby 
			FROM $this->table_html 
			WHERE id='$id'"
		);
		
		if ($res->num_rows == 0) return $this->notSoFatalError("Siden finnes ikke!");
		$row = $res->fetch_assoc();
		
		if ($row['lockedby'] > 0 && $row['lockedby'] != $this->login_identifier) {
			if ($row['locktimestamp'] < time()-3600) {
				// Opphever låsen, som er for gammel.
				$res = $this->query("UPDATE $this->table_html SET lockedby=0, locktimestamp=0 WHERE id='$id'");
			} else {
				$mf = $this->lookup_member;
				$m = $mf($row['lockedby']);
				return $this->notSoFatalError("Denne siden redigeres av $m->fullname og er derfor låst for redigering av andre.
					Siden blir åpnet for redigering igjen når $m->firstname avslutter redigeringen. Dersom $m->firstname
					glemmer å avslutte redigeringen på korrekt måte blir siden uansett åpnet igjen for redigering 
					en time etter vi mottok siste livstegn fra maskinen til $m->firstname. Akkurat nå gjenstår
					".ceil(($row['locktimestamp'] + 3600 - time())/60)." minutt(er).");
			}
		}
		
		$res = $this->query("UPDATE $this->table_html 
			SET lockedby='$this->login_identifier', locktimestamp='".time()."' 
			WHERE id='$id'"
		);

		foreach ($_POST as $n => $v) {
			$n = explode("_",$n);
            if (count($n) == 2 && $n[0] == "content" && isset($n[1])) {
                $content = $v;
				$content = str_replace("\r\n","\n",$content);  // windows linjebrekk -> unix linjebrekk
                $content = str_replace("\r","\n",$content);    // mac linjebrekk -> unix linjebrekk
                $content = str_replace('"http://www.18bergen.org/','"/',$content); // make links domain-invariant
                $content = str_replace('"http://www.18bergen.no/','"/',$content);  // make links domain-invariant
				$content = addslashes($content);		
				$updres = $this->query('UPDATE '.$this->table_html.' 
					SET 
						content="'.$content.'", 
						lastmodified="'.$now.'",
						updatedby="'.$this->login_identifier.'"
					WHERE id="'.$id.'" AND lang="'.addslashes($n[1]).'"'
				);
				if ($this->affected_rows() != 1) {
					print $this->notSoFatalError("Tabellen kunne ikke oppdateres");
					print "<pre>";
					print_r($_POST);
					print "</pre>";
					exit();
				}
			}
		}
		
		$this->addToActivityLog('oppdaterte siden <a href="'.$this->generateURL("").'">'.$this->header.'</a>',true,'update');
		
		if (isset($_POST['lagre2'])){
			
			// Cancel lock
			$this->query("UPDATE $this->table_html 
				SET 
					lockedby=0, locktimestamp=0
				WHERE id='$id'"
			);	
			
			// Redirect
			$this->redirect($this->generateURL(''), "Siden ble lagret");
		} else {
			$this->redirect($this->generateURL('action=editPage'), "Siden ble lagret");
		}
	}
	
	function ajaxSaveBackup(){
		
		$id = $this->page_id;
		if (!is_numeric($id)){ $this->fatalError("incorrect input"); }
		if (!$this->allow_write){ $this->permissionDenied(); return; }
		$now = time();
		
		$res = $this->query("UPDATE $this->table_html 
			SET lockedby='$this->login_identifier', locktimestamp='$now' 
			WHERE id='$id'"
		);
		
		$data = $_POST['data'];

		$langs = array();
		foreach ($data as $d) {
			$content = $d['content'];
			$lang = $d['lang'];
			$content = str_replace("\r\n","\n",$content);  // windows linjebrekk -> unix linjebrekk
			$content = str_replace("\r","\n",$content);    // mac linjebrekk -> unix linjebrekk
			$content = addslashes($content);
			$this->query('UPDATE '.$this->table_html.' 
				SET 
					temp_content="'.$content.'", 
					temp_timestamp="'.$now.'",
					temp_savedby="'.$this->login_identifier.'"
				WHERE id="'.$id.'" AND lang="'.addslashes($lang).'"'
			);
			if ($this->affected_rows() == 1) {
				$langs[] = $lang;
			}
		}
		
		header("Content-Type: application/json; charset=utf-8");
		print json_encode(array('error' => '0', 'backupsDone' => $langs));
		exit();
	}
	
}

/*

/*
		
			<script type='text/javascript'><!-- // Globals for HTMLArea
				_editor_url = '$this->htmleditdir';
				_editor_lang = 'no';
			//--></script>
			<script type='text/javascript' src='$site_rootdir/htmlarea/htmlarea.php'></script>
			*/
/*
			print "<script type='text/javascript' defer='1'><!--

				var config = new HTMLArea.Config(); // create a new configuration object
													// having all the default values
				config.toolbar = [
					
					[ 'fontname', 'space',
					  'fontsize', 'space',
					  'formatblock', 'space',
					  'bold', 'italic', 'underline', 'separator',
					  'copy', 'cut', 'paste', 'separator', 'undo', 'redo' ],

					[ 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', 'separator',
					  'insertorderedlist', 'insertunorderedlist', 'outdent', 'indent', 'separator',
					  'forecolor', 'textindicator', 'separator',
					  'inserthorizontalrule', 'createlink', 'insertimage', 'inserttable', 'htmlmode',
					  'separator', 'showhelp', 'about' ]
					
				];
				
				config.pageStyle = 'body { font-family: sans-serif; font-size: 12px; margin: 0px; margin-bottom: 10px; background: #EDF0ED; }';

				config.height = '500px';
				config.width = '510px';
				// the following sets a style for the page body (black text on yellow page)
				// and makes all paragraphs be bold by default

				// the following replaces the textarea with the given id with a new
				// HTMLArea object having the specified configuration
				HTMLArea.replace('contenteditfield',config);
			--></script>
			";*/
			
			

?>
