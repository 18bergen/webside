class referat extends base {
	
	var $page_title;
	
	var $table_pages = "dynamiskesider";	
	
	var $pageid;
	var $allow_write = false;
	var $memberlookup_function;
	var $webmasterlookup_function;
	var $listgroups_function;
	var $htmleditdir;
	var $login_identifier = 0;
	var $imageuploaddir;
	var $newsimagedir;

	var $current_path;

	function run(){
	
		$this->initialize_base();
	
		array_push($this->getvars,"editpage","savepage","savebackup","selectVersionToEdit");

		$res = $this->query("SELECT content FROM $this->table_pages WHERE id='$this->page_id'");
		if ($res->num_rows < 1){ 	
			$this->query("INSERT INTO $this->table_pages (id) VALUES
				('$this->page_id')"
			); 
		}
	
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
				$this->printEditPageForm();
				break;
			case 'savebackup':
				$this->saveBackup();
				break;
			case 'savepage':
				$this->savePage();
				break;
			case 'selectVersionToEdit':
				$this->selectVersionToEdit();
				break;
			case 'viewpage':
				$this->printPage();
				break;
		}

	}
	
	/* #################################################### PRINT PAGE ####################################################################*/

	function printPage(){
		if (!is_numeric($this->page_id)){ 
			$this->notSoFatalError("Det ble ikke spesifisert noen side-id.");
			return 0;
		}
		
		$res = $this->query("SELECT content FROM $this->table_pages WHERE id='$this->page_id'");
		if ($res->num_rows < 1){ 
			$res = $this->query("SELECT content FROM $this->table_pages WHERE id='$this->page_id'");
			if ($res->num_rows < 1){ 
				print "<h2>Siden finnes ikke</h2>";
				return; 
			}
		}
		$row = $res->fetch_assoc(); 
		print stripslashes($row['content']);
		
		if ($this->allow_write) print "<hr /><a href='".$this->generateURL("editpage")."'>Endre</a>";
		
		mysql_free_result($res);
	}

	/* #################################################### EDIT PAGE ####################################################################*/


	function selectVersionToEdit() {
		if (!isset($_POST['version'])) {
			$this->redirect($this->generateURL("editpage"),"Du må velge en versjon!");
		}
		$id = $this->page_id;
		if (!is_numeric($id)){ $this->fatalError("incorrect input"); }
		if (!$this->allow_write){ $this->permissionDenied(); return; }
		
		if ($_POST['version'] == 'backup') {
			$res = $this->query("UPDATE $this->table_pages 
				SET 
					content = temp_content,
					temp_timestamp=0, temp_content='', temp_savedby='' 
				WHERE id='$id'"
			);
		} else if ($_POST['version'] == 'original') {
			$res = $this->query("UPDATE $this->table_pages 
				SET 
					temp_timestamp=0, temp_content='', temp_savedby='' 
				WHERE id='$id'"
			);
		}
		$this->redirect($this->generateURL("editpage"));
		exit();
	}
	
	function printEditPageForm(){
		
		$id = $this->page_id;
		
		if (!is_numeric($id)){ $this->fatalError("incorrect input"); }
		if (!$this->allow_write){ $this->permissionDenied(); return; }
		$res = $this->query("SELECT 
				$this->table_pages.content, 
				$this->table_pages.lockedby, 
				$this->table_pages.locktimestamp, 
				$this->table_pages.lastmodified, 
				$this->table_pages.temp_content,
				$this->table_pages.temp_timestamp, 
				$this->table_pages.temp_savedby
			FROM 
				$this->table_pages 
			WHERE $this->table_pages.id='$id'"
		);
		
		if ($res->num_rows == 0){ $this->notSoFatalError("The page doesn't exist!"); return 0; }
		$pageVersions = array();
		$newerBackupExists = false;
		
		while ($row = $res->fetch_assoc()) {
			
			$pageVersions[] = $row;
			
			if ($row['lockedby'] > 0 && $row['lockedby'] != $this->login_identifier) {
				if ($row['locktimestamp'] < time()-3600) {
					// Opphever låsen, som er for gammel.
					$res = $this->query("UPDATE $this->table_pages SET lockedby=0, locktimestamp=0 WHERE id='$id'");
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
					$res = $this->query("UPDATE $this->table_pages SET temp_timestamp=0, temp_content='', temp_savedby='' WHERE id='$id'");
				}
				
				if ($row['temp_savedby'] == $this->login_identifier) {
				
					$newerBackupExists = true;
				}
			}
			
		}
		
		if ($newerBackupExists) {
			$lastmodified = (empty($row['last_modified'])) ? "<i>Aldri lagret</i>" : date("d. m Y, H:i",$row['lastmodified']);
					$this->infoMessage("

					Det eksisterer en automatisk lagret backup som er nyere enn sist lagrede versjon av siden. <br />
					<ul>
						<li>Backupen ble lagret: ".date("d. m Y, H:i",$row['temp_timestamp'])."</li>
						<li>Sist ordinære lagring: $lastmodified</li>
					</ul>
					Velg hvilken versjon du vil jobbe videre med:
					<br />&nbsp;
					<form method='post' action='".$this->generateURL(array("noprint=true","selectVersionToEdit"))."'>
						<input type='radio' name='version' value='backup' />Jobb videre med backup-versjonen.<br />
						<input type='radio' name='version' value='original' />Slett backupen og jobb videre med originalen.<br />
						<br />
						<input type='submit' value='Fortsett' />
					</form>		
				");
				
				//print stripslashes($row['content']);
			
			$tabs = "";
		$divs = "";
		for ($i = 1; $i <= count($pageVersions); $i++) {
			$p = $pageVersions[$i-1];
			$content = stripslashes($pageVersions[$i-1]['content']);
			$tempcontent = stripslashes($pageVersions[$i-1]['temp_content']);
			if ($tabs == "")
				$tabs .= "
						<li class='selected'><a href='#' rel='tcontent1_$i'>Backup</a></li>
						<li><a href='#' rel='tcontent2_$i'>Original</a></li>";
			else
				$tabs .= "
						<li><a href='#' rel='tcontent1_$i'>Backup</a></li>
						<li><a href='#' rel='tcontent2_$i'>Original</a></li>";
			$divs .= "
					<div id='tcontent1_$i' class='tabcontent'>
						$tempcontent
					</div>
					<div id='tcontent2_$i' class='tabcontent'>
						$content
					</div>";
			
		}
			
				print '
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
		//Start Tab Content script for UL with id="maintab" Separate multiple ids each with a comma.
		initializetabcontent("maintab")
	</script>

				
			';			
			return;
		}
		
		
		// Opdater pagelock
		$res = $this->query("UPDATE $this->table_pages 
			SET lockedby='$this->login_identifier', locktimestamp='".time()."' 
			WHERE id='$id'"
		);
		
		$posturl = ($this->useCoolUrls ?
				$this->generateURL(array("noprint=true","savepage")) :
				$this->generateURL(array("noprint=true","dynpage=$id","savepage"))
		);
		
		
		$tabs = "";
		$divs = "";
		for ($i = 1; $i <= count($pageVersions); $i++) {
			$p = $pageVersions[$i-1];
			$content = stripslashes($pageVersions[$i-1]['content']);
			$content = htmlentities($content);
			if ($tabs == "")
				$tabs .= "
						<li class='selected'><a href='#' rel='tcontent$i'>Referat</a></li>";
			else
				$tabs .= "
						<li><a href='#' rel='tcontent$i'>Referat</a></li>";
			$divs .= "
					<div id='tcontent$i' class='tabcontent'>
						<textarea name='content' id='content' style='width:490px; height:600px;'>$content</textarea>
					</div>";
			
		}
		
		print "
			<form action='$posturl' method='post' onsubmit='stopAutoBackup();'>
				<p>
					<input type='submit' name='lagre1' value='    Lagre og fortsett redigering    ' /> 
					<input type='submit' name='lagre2' value='    Lagre og vis siden    ' />
				</p>

				<ul id='maintab' class='shadetabs'>				
					$tabs
				</ul>
				<div class='tabcontentstyle'>
					$divs
				</div>
				<script type='text/javascript'>
					//Start Tab Content script for UL with id='maintab' Separate multiple ids each with a comma.
					initializetabcontent('maintab')
				</script>			
				<div id='backupstatus'>Automatisk backup blir ikke tatt (FCKEditor er ikke aktiv).</div>
			</form>
		";

		//".str_replace("&","&amp;",stripslashes($row['content']))."

		$this->setDefaultCKEditorOptions();
		
		$backupUrl = $this->generateURL(array("noprint=true","savebackup"),true);

print "

<script type='text/javascript'><!--

window.onload = function() {
";
for ($i = 1; $i <= count($pageVersions); $i++) {
	print "
	var oFCKeditor = new FCKeditor( 'content' );
	oFCKeditor.BasePath	= '".ROOT_DIR.$this->pathToFCKeditor."';
	oFCKeditor.ToolbarSet = 'BergenVS' ;
	oFCKeditor.Height = $this->FCKeditorHeight;
	oFCKeditor.ReplaceTextarea();
	";
}
print "
}

function FCKeditor_OnComplete( editorInstance ) {
	// Ta backup hvert 90de sekund
	setText('backupstatus', 'Editor innlastet! Backup blir tatt hvert 30de sekund.');
	backupInterval = setInterval(autoBackup, 30000, 'content', 'backupstatus', '$backupUrl');	
}
oldPars = -1;
function autoBackup(editor, target, url){
  	
  	
  	var pars = new Array();
  	";
  	for ($i = 1; $i <= count($pageVersions); $i++) {
		print "
  		  	var oEditor = FCKeditorAPI.GetInstance( 'content' ) ;
  			var xhtml = oEditor.GetXHTML( false ); 
 			pars.push('content='+escape(xhtml));
		";
	}
	print "
	pars = pars.join('&');
	var success = function(t){ 
		setText(target,t.responseText);
	}
	if (pars != oldPars) {
		setText(target, 'Lagrer backup...');
		var myAjax = new Ajax.Request(url, {method: 'post', parameters: pars, onSuccess:success});
		oldPars = pars;
	}
}

function stopAutoBackup() {
	clearInterval(backupInterval);
}


//--></script>

";

	
	}

	function savePage(){
		
		$now = time();
		
		$id = $this->page_id;
		if (!is_numeric($id)){ $this->fatalError("incorrect input"); }
		if (!$this->allow_write){ $this->permissionDenied(); return; }
		$res = $this->query("SELECT 
				content, lockedby, locktimestamp, lastmodified, temp_content, temp_timestamp, temp_savedby 
			FROM $this->table_pages 
			WHERE id='$id'"
		);
		
		if ($res->num_rows == 0){ $this->notSoFatalError("The page doesn't exist!"); return 0; }
		$row = $res->fetch_assoc();
		
		if ($row['lockedby'] > 0 && $row['lockedby'] != $this->login_identifier) {
			if ($row['locktimestamp'] < time()-3600) {
				// Opphever låsen, som er for gammel.
				$res = $this->query("UPDATE $this->table_pages SET lockedby=0, locktimestamp=0 WHERE id='$id'");
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
		
		$res = $this->query("UPDATE $this->table_pages 
			SET lockedby='$this->login_identifier', locktimestamp='".time()."' 
			WHERE id='$id'"
		);

		foreach ($_POST as $n => $v) {
			$n = explode("_",$n);
			if (count($n) == 2 && $n[0] == "content" && is_numeric($n[1])) {
				$content = str_replace("\r\n","\n",$v);  // windows linjebrekk -> unix linjebrekk
				$content = str_replace("\r","\n",$v);    // mac linjebrekk -> unix linjebrekk
				$content = addslashes($v);		
				$this->query("UPDATE $this->table_pages 
					SET 
						content='$content', 
						lastmodified='$now',
						updatedby='$this->login_identifier'
					WHERE id='$id'"
				);
			}
		}
		
		$this->addToActivityLog("Oppdaterte siden '$this->page_header' ($id)");
		

		
//		$this->addToChangeLog("\"<a href=\"$page_url\">$tittel</a>\" oppdatert");
		
		if (isset($_POST['lagre2'])){
			
			// Cancel lock
			$this->query("UPDATE $this->table_pages 
				SET 
					lockedby=0, locktimestamp=0
				WHERE id='$id'"
			);	
			
			// Redirect
			$this->redirect($this->generateURL(""), "Siden ble lagret");
		} else {
			$this->redirect($this->generateURL("editpage"), "Siden ble lagret");
		}
		exit();
	}
	
	function saveBackup(){
		
		$id = $this->page_id;
		if (!is_numeric($id)){ $this->fatalError("incorrect input"); }
		if (!$this->allow_write){ $this->permissionDenied(); return; }
		$now = time();
		
		$res = $this->query("UPDATE $this->table_pages 
			SET lockedby='$this->login_identifier', locktimestamp='$now' 
			WHERE id='$id'"
		);
		
		foreach ($_POST as $n => $v) {
			$n = explode("_",$n);
			if (count($n) == 2 && $n[0] == "content" && is_numeric($n[1])) {
				$content = str_replace("\r\n","\n",$v);  // windows linjebrekk -> unix linjebrekk
				$content = str_replace("\r","\n",$v);    // mac linjebrekk -> unix linjebrekk
				$content = addslashes($v);		
				$this->query("UPDATE $this->table_pages 
					SET 
						temp_content='$content', 
						temp_timestamp='$now',
						temp_savedby='$this->login_identifier'
					WHERE id='$id'"
				);
			}
		}
		
		// Make UNIX-linebreaks
		print "Backup ble sist lagret kl. ".date("H:i:s",$now).".";
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
			
			