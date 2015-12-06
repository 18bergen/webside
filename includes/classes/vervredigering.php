<?php
class vervredigering extends base {

	var $getvars = array("vervskifte","vervhistorie","edittypes","delconf","moveup","movedown","verv","endreverv");

	var $thelist;
	var $history;
	
	var $allow_editverv = false;
	
	var $table_verv = "verv";
	var $table_verv_history = "vervhistorie";
	
	var $rights_needed;

	var $pathToJpGraph;
	
	function vervredigering() {
		$this->table_verv = DBPREFIX.$this->table_verv;
		$this->table_verv_history = DBPREFIX.$this->table_verv_history;
	}
	
	function initialize() {
		$this->initialize_base();
	}
	
	function run(){
		$this->initialize();
		if (isset($_GET['vervskifte'])){
			return $this->lagreVerv();
		} else if (isset($_GET['vervhistorie'])){
			return $this->vervHistory($_GET['vervhistorie']);
		} else if (isset($_GET['edittypes'])){
			return $this->editVervTyperForm();
		} else if (isset($_POST['whattodo'])){
			return $this->lagreVervTyper($_POST['whattodo']);
		} else if (isset($_GET['endreverv']) && isset($_GET['verv'])){
			return $this->endreVerv($_GET['verv'],$_GET['endreverv']);
		} else if (isset($_POST['lagre'])){
			return $this->lagreVervHistorie();
		} else if (count($this->coolUrlSplitted) > 0) {
			if (count($this->coolUrlSplitted) > 1) {
				return $this->vervHistory($this->coolUrlSplitted[0],$this->coolUrlSplitted[1]);
			} else {
				return $this->vervHistory($this->coolUrlSplitted[0]);
			}
		} else {
			return $this->vervForm();
		}

	}
	
	function getVervBySlug($vervName) {
		global $db;
		$res = $db->query("SELECT id FROM $this->table_verv WHERE slug=\"".addslashes($vervName)."\"");
		if ($res->num_rows != 1) return false;
		$row = $res->fetch_row();
		return $row[0];
	}

	function stoppAllePatruljeVerv($medlem, $gruppe=0){
		global $db,$memberdb, $eventLog;
		$peffverv = $this->getVervBySlug('peff');
		$assverv = $this->getVervBySlug('ass');
		$res = $db->query(
			"SELECT COUNT(*) FROM $this->table_verv_history
			WHERE 
				(verv='$peffverv' OR verv='$assverv')
				AND person='$medlem'
				AND enddate='0000-00-00'".
				(($gruppe == 0) ? "" : " AND gruppe='$gruppe'")
		);
		$row = $res->fetch_row();
		if ($row[0] > 0){
			$db->query(
				"UPDATE $this->table_verv_history
				SET 
					enddate=CURDATE()
				WHERE 
					(verv='$peffverv' OR verv='$assverv')
					AND person='$medlem'
					AND enddate='0000-00-00'".
					(($gruppe == 0) ? "" : " AND gruppe='$gruppe'")
			);
			$eventLog->addToActivityLog("Avsluttet patruljeverv for ".$memberdb->getMemberById($medlem)->fullname.".");
		}
	}

	function startPeffVerv($medlem, $gruppe){
		global $db,$memberdb, $eventLog;
		$peffverv = $this->getVervBySlug('peff');
		$db->query("INSERT INTO $this->table_verv_history (verv,startdate,gruppe,person) VALUES ($peffverv,CURDATE(),$gruppe,$medlem)");
		$eventLog->addToActivityLog("Startet patruljeførerverv i vervhistorien for ".$memberdb->getMemberById($medlem)->fullname.".");
	}

	function startAssVerv($medlem, $gruppe){
		global $db,$memberdb, $eventLog;
		$assverv = $this->getVervBySlug('ass');
		$db->query("INSERT INTO $this->table_verv_history (verv,startdate,gruppe,person) VALUES ($assverv,CURDATE(),$gruppe,$medlem)");
		$eventLog->addToActivityLog("Startet patruljeassistentverv i vervhistorien for ".$memberdb->getMemberById($medlem)->fullname.".");
	}

	function stoppPeffVerv($medlem, $gruppe){
		global $db,$memberdb, $eventLog;
		$peffverv = $this->getVervBySlug('peff');
		$db->query(
			"UPDATE $this->table_verv_history ".
			"SET enddate=CURDATE() ".
			"WHERE $this->table_verv_history.verv=$peffverv ".
				"AND $this->table_verv_history.gruppe=$gruppe ".
				"AND $this->table_verv_history.enddate='0000-00-00'"
		);
		$eventLog->addToActivityLog("Avsluttet patruljeførerverv i vervhistorien for ".$memberdb->getMemberById($medlem)->fullname.".");
	}

	function stoppAssVerv($medlem, $gruppe){
		global $db,$memberdb, $eventLog;
		$assverv = $this->getVervBySlug('ass');
		$db->query(
			"UPDATE $this->table_verv_history ".
			"SET enddate=CURDATE() ".
			"WHERE $this->table_verv_history.verv=$assverv ".
				"AND $this->table_verv_history.gruppe=$gruppe ".
				"AND $this->table_verv_history.enddate='0000-00-00'"
		);
		$eventLog->addToActivityLog("Avsluttet patruljeassistentverv i vervhistorien for ".$memberdb->getMemberById($medlem)->fullname.".");
	}

	function opprettNyttVerv(){
		global $db, $eventLog;
		if (!$this->allow_editverv) return $this->permissionDenied();
		$eventLog->addToActivityLog("Opprettet nytt verv");
		$res = $db->query("SELECT MAX(position) FROM $this->table_verv");
		$row = $res->fetch_row();
		$position = $row[0]+1;
		$db->query("INSERT INTO $this->table_verv (caption,type,position) VALUES ('Verv uten navn','gruppe',$position)");
		return $this->insert_id();
	}

	function oppdaterVerv($id,$member){
		global $db;
		if (!$this->allow_editverv) return $this->permissionDenied();
		if (!is_numeric($id)){ $this->fatalError("incorrect input (oppdaterVerv.id)"); }
		if (!is_numeric($member)){ $this->fatalError("incorrect input (oppdaterVerv.member)"); }
		$db->query("UPDATE $this->table_verv_history SET enddate=CURDATE() WHERE verv=$id AND enddate='0000-00-00'");
		if ($member != 0){
			$db->query("INSERT INTO $this->table_verv_history (verv,startdate,person) VALUES ($id,CURDATE(),$member)");
		}
	}
	
	function slettVerv($id){
		global $db;
		if (!$this->allow_editverv) return $this->permissionDenied();
		if (!is_numeric($id)){ $this->fatalError("incorrect input (slettVerv.id)"); }
		$res = $db->query("SELECT caption,position FROM $this->table_verv WHERE id=$id");
		$row = $res->fetch_assoc();
		$caption = $row['caption'];
		$verv_pos = $row['position'];
		$db->query("DELETE FROM $this->table_verv WHERE id='$id'");
		$db->query("DELETE FROM $this->table_verv_history WHERE verv='$id'");
		$db->query("UPDATE $this->table_verv SET position=position-1 WHERE position > $verv_pos");
		$this->addToActivityLog("Slettet vervet $caption");
	}

	function slettFraVervHistorie($id){
		global $db, $memberdb, $eventLog;
		if (!$this->allow_editverv) return $this->permissionDenied();
		if (!is_numeric($id)){ ErrorMessageAndExit("incorrect input (oppdaterVerv.id)"); }
		$v = $this->table_verv;
		$vh = $this->table_verv_history;
		$res = $db->query("SELECT $v.id, $v.caption, $vh.person, $vh.startdate, $vh.enddate 
			FROM $v, $vh
			WHERE $vh.id=$id AND $vh.verv=$v.id");
		$row = $res->fetch_assoc();
		$m = $memberdb->getMemberById($row['person']);
		$db->query("DELETE FROM $this->table_verv_history WHERE id=$id");
		$eventLog->addToActivityLog("Slettet fra vervhistorie: $m->fullname som ".$row['caption'].
			" fra ".$row['startdate']." til ".$row['enddate']);
		return $row['id'];
	}

	function listGruppeVerv(){
		global $db;
		$this->thelist = array();
		$v = $this->table_verv;
		$vh = $this->table_verv_history;
		$res = $db->query("SELECT $v.caption, $v.id, $v.slug, $vh.person
			FROM $v 
			LEFT JOIN $vh ON $v.id=$vh.verv AND $vh.enddate='0000-00-00' 
			WHERE $v.type='gruppe'
			GROUP BY $v.id
			ORDER BY $v.position"
		);
		while ($row = $res->fetch_assoc()){
			foreach ($row as $n => $v){
				$this->thelist[$row['id']]->$n = $v;
			}
			if (empty($this->thelist[$row['id']]->person)) $this->thelist[$row['id']]->person = 0;
		}
	}

	function printList(){
		global $memberdb;
		$output = "<table>\n";
		foreach ($this->thelist as $aVerv){
			$output .= "  <tr><td>\n";
			$output .= "    <a href=\"".$this->generateCoolURL("/".$aVerv->slug)."\">".$aVerv->caption."</a>:\n";
			$output .= "  </td><td>\n";
			if ($this->allow_editverv) {
				$output .= "    <select name=\"verv".$aVerv->id."\">\n";
				$output .= "      <option value=\"0\">&nbsp;&nbsp;( Ingen )</option>\n";
				foreach ($memberdb->members as $m){
					if ($m->rights >= $this->rights_needed){
						if ($aVerv->person == $m->ident){
							$output .= "      <option value=\"".$m->ident."\" selected=\"selected\">".$m->fullname."</option>\n";
						} else {
							$output .= "      <option value=\"".$m->ident."\">".$m->fullname."</option>\n";
						}
					}
				}
				$output .= "    </select>\n";
			} else {
				$m = $memberdb->getMemberById($aVerv->person);
				$output .= $m->fullname;
			}
			$output .= "  </td></tr>\n\n";
		}
		$output .= "</table>\n";
		return $output;
	}

	function fetchHistory($id,$gruppe=null){
		global $db;
		if (!is_numeric($id)){ ErrorMessageAndExit("incorrect input (oppdaterVerv.id)"); }
		$this->history = array();
		$v = $this->table_verv;
		$vh = $this->table_verv_history;
		if (isset($gruppe)) {
			$res = $db->query("SELECT 
				$v.caption, $v.type, $vh.id, $vh.startdate, $vh.enddate, $vh.person
				FROM $v,$vh
				WHERE $v.id=$id AND $vh.verv=$v.id AND $vh.gruppe=$gruppe->id
				ORDER BY $v.id,$vh.startdate DESC");
		} else {
			$res = $db->query("SELECT 
				$v.caption, $v.type, $vh.id, $vh.startdate, $vh.enddate, $vh.person
				FROM $v,$vh
				WHERE $v.id=$id AND $vh.verv=$v.id 
				ORDER BY $v.id,$vh.startdate DESC");		
		}
		while ($row = $res->fetch_assoc()){
			foreach ($row as $n => $v){
				$this->history[$row['id']]->$n = $v;
			}
		}
	}

	function vervForMember($id){
		global $db;
		$v = $this->table_verv;
		$vh = $this->table_verv_history;
		$res = $db->query("SELECT $v.caption 
			FROM $v,$vh
			WHERE $vh.person='".$id."' 
				AND $vh.verv=$v.id
				AND $vh.enddate='0000-00-00'");
		
		$output = array();
		while ($row = $res->fetch_assoc()){
			$output[] = $row['caption'];
		}
		return $output;
	}
	
	function lagreVerv(){
		// Oppdater gruppeverv
		if (!$this->allow_editverv) return $this->permissionDenied();
		$this->listGruppeVerv();
		foreach ($this->thelist as $v){
			if ($_POST["verv".$v->id] != "-1"){
				if ($v->person != $_POST["verv".$v->id]){
					$this->oppdaterVerv($v->id, $_POST["verv".$v->id]);
				}
			}
		}
		// Redirect
		$this->redirect($this->generateURL(""),"Lagret"); 
	}

	function vervForm(){

		$this->listGruppeVerv();

		$output = '';
		if ($this->allow_editverv) $output .= "
			<a href=\"".$this->generateUrl("edittypes")."\">Rediger vervtyper</a> 
		";
		if ($this->allow_editverv) {
			$output .= "
				<form method=\"post\" action=\"".$this->generateUrl(array("noprint=true","vervskifte"))."\">
					<h2>Gruppeverv:</h2>
					<table width='100%'>
				".$this->printList()."
					<p>
						<input type=\"submit\" value=\"Lagre endringer\" />
					</p>
					<p>Tips: Finner du ikke personen du leter etter? Sjekk at personens rettighetsnivå stemmer! For å inneha et gruppeverv, må man ha rettighetsnivå ".$this->rights_needed.".</p>
					<p>&nbsp;</p>
				</form>
			";
		} else {
			$output .= $this->printList();
		}
		return $output;
	}
	

	function vervHistory($id,$gruppe=''){
		global $memberdb;
		$g = null;
		if (is_numeric($id)) {
			$res = $this->query(
				"SELECT id, caption, slug FROM $this->table_verv WHERE id='$id'"
			);
		} else {
			if (!empty($gruppe)) {
				$g = $memberdb->getGroupBySlug($gruppe);
				if ($g == false) {
					return "Gruppen finnes ikke";
				}
			}
			$res = $this->query(
				"SELECT id, caption, slug FROM $this->table_verv WHERE slug=\"".addslashes($id)."\""
			);
		}
		if ($res->num_rows != 1) return $this->notSoFatalError("Vervet eksisterer ikke");
		$row = $res->fetch_assoc();
		$id = $row['id'];
		call_user_func($this->add_to_breadcrumb,"<a href=\"".$this->generateCoolUrl("/".$row['slug'])."\">".$row['caption']."</a>");
		$caption = $row['caption'];
		if (isset($g)) $caption .= ' i '.$g->caption;
		$output = "
			<h2>$caption</h2>
		";
	
		$this->fetchHistory($id,$g);
		$output .= "<table cellpadding='4'>\n";

		$no = 0;
		foreach ($this->history as $hItem){
			$m = $memberdb->getMemberById($hItem->person);
			$m_img = $memberdb->getProfileImage($hItem->person);
			$m_link = call_user_func($this->make_memberlink, $hItem->person,$m->fullname);
			if ($hItem->enddate == '0000-00-00'){
				$output .= "<tr><td><img src=\"".$m_img."\" valign='top' style='width:80px; padding-right:5px;' /></td><td valign='top'>
					$m_link<br />siden ".strftime("%e. %B %Y",strtotime($hItem->startdate))."<br />";
				if ($this->allow_editverv) {
					$output .= "
						<div style='font-size:8pt;'>
							<a href=\"".$this->generateUrl(array("verv=".$hItem->id,"endreverv=innm"))."\">Endre tidsrom</a> | 
							<a href=\"".$this->generateUrl(array("verv=".$hItem->id,"endreverv=slett"))."\">Slett fra vervhistorie</a> 
						</div>";
				}
				$output .= "</td></tr>";
			} else {
				
				/*
				$bar = new GanttBar ($no,$m->fullname, date("Y-m-d",$hItem->startdate), date("Y-m-d",$hItem->enddate)); 
				$bar->SetPattern(BAND_RDIAG, '#EDF0ED'); $bar ->SetFillColor ('#444444');
				$bar->SetShadow();
				$bar->SetColor('black');
				$bar->title->SetFont(FF_ARIAL,FS_NORMAL,10);
				$graph->Add( $bar); 
				*/
				$output .= "<tr><td><img src=\"".$m_img."\" valign='top' style='width:80px; padding-right:5px;' /></td><td valign='top'>
					$m_link<br />fra ".strftime("%e. %B %Y",strtotime($hItem->startdate)).
					" til ".strftime("%e. %B %Y",strtotime($hItem->enddate))."<br />";
				if ($this->allow_editverv) {				
					$output .= "
						<div style='font-size:8pt;'>
							<a href=\"".$this->generateUrl(array("verv=".$hItem->id,"endreverv=innutm"))."\">Endre tidsrom</a> | 
							<a href=\"".$this->generateUrl(array("verv=".$hItem->id,"endreverv=slett"))."\">Slett fra vervhistorie</a> 
						</div>";
				}
				$output .= "</td></tr>";
			}
			$no++;
		}
		$output .= "</table>\n";
		
		return $output;
	}

	function editVervTyperForm(){
		global $db;

		if (!$this->allow_editverv) return $this->permissionDenied();

		$output = '
			<h2>Rediger vervtyper</h2>
			<form name="form1" method="post" action="'.$this->generateURL('noprint=true').'">
				<input type="hidden" name="whattodo" value="lagreverv" />
				<input type="hidden" name="vervid" value="NOTSET" />
				<table>
		';
		$Result = $db->query("SELECT id,caption,beskrivelse,type,synlighet FROM $this->table_verv ORDER BY position");
		while ($Row = $Result->fetch_assoc()){
			$output .= "<tr><td valign='top'><input type=\"text\" value=\"".$Row["id"]."\" size=\"5\" disabled=\"disabled\" /> </td><td>";
			$output .= "
				<div><input type=\"text\" name=\"verv".$Row['id']."\" value=\"".stripslashes($Row['caption'])."\" style=\"width:288px;\" /></div>
				<div><textarea name=\"vervdesc".$Row['id']."\" style=\"width:290px; height: 50px;\">".stripslashes($Row['beskrivelse'])."</textarea></div><div>";
			if ($Row['type'] == "patrulje"){
				$output .= "<input type=\"radio\" name=\"vervselector".$Row['id']."\" id=\"vervselector".$Row['id']."1\" value='gruppe' /><label for=\"vervselector".$Row['id']."1\">Gruppeverv</label> \n";
				$output .= "<input type=\"radio\" name=\"vervselector".$Row['id']."\" id=\"vervselector".$Row['id']."2\" value='patrulje' checked='checked' /><label for=\"vervselector".$Row['id']."2\">Patruljeverv</label> \n";
			} else {
				$output .= "<input type=\"radio\" name=\"vervselector".$Row['id']."\" id=\"vervselector".$Row['id']."1\" value='gruppe' checked='checked' /><label for=\"vervselector".$Row['id']."1\">Gruppeverv</label> \n";
				$output .= "<input type=\"radio\" name=\"vervselector".$Row['id']."\" id=\"vervselector".$Row['id']."2\" value='patrulje' /><label for=\"vervselector".$Row['id']."2\">Patruljeverv</label> \n";
			}
			$checked = ($Row['synlighet'] ? "checked='checked'" : "");
			$output .= "</div><div><input type=\"checkbox\" name=\"synlighet".$Row['id']."\" id=\"synlighet".$Row['id']."\" $checked />
				<label for=\"kontakt".$Row['id']."\">Vis på siden \"Kontakt oss\"</label></div> \n";
			$output .= "</td><td valign='top'> 
				
				<input type=\"button\" value=\"Slett verv\" onclick=\"document.form1.whattodo.value='slettverv';document.form1.vervid.value='".$Row['id']."';document.form1.submit();\" />
					<br />
				<input type=\"button\" value=\"Flytt opp\" onclick=\"document.form1.whattodo.value='moveup';document.form1.vervid.value='".$Row['id']."';document.form1.submit();\" />
					<br />
				<input type=\"button\" value=\"Flytt ned\" onclick=\"document.form1.whattodo.value='movedown';document.form1.vervid.value='".$Row['id']."';document.form1.submit();\" />
				
				</td></tr>\n";
		}
		$output .= "</table><br />
			<input type=\"button\" value=\"Nytt verv\" onclick=\"document.form1.whattodo.value='nyttverv';document.form1.submit();\" />
			<input type=\"submit\" value=\"Lagre vervendringer\" />
			</form>
			<p>NB! Denne siden krever at JavaScript er påslått!</p>
			<p><a href=\"".$this->generateUrl("")."\">Tilbake til verv</a></p>
		";

		call_user_func($this->add_to_breadcrumb,"<a href=\"".$this->generateCoolUrl("/","edittypes")."\">Redigere vervtyper</a>");

		return $output;

	}

	function lagreVervTyper($whatToDo){
		global $db;

		if (!$this->allow_editverv) return $this->permissionDenied();

		if($whatToDo == "NOTSET"){
			$this->fatalError("Du må aktivere JavaScript for at denne siden skal virke! Om du har aktivert JavaScript og ikke får denne siden til å virke, ta kontakt med webmaster!");
		} else {
			$redir = true;
			
			if ($whatToDo != "slettverv"){
				$Result = $db->query("SELECT id, caption FROM $this->table_verv");
				while ($Row = $Result->fetch_assoc()){
					$synlighet = ((isset($_POST['synlighet'.$Row['id']]) && ($_POST['synlighet'.$Row['id']] == "on")) ? 1 : 0 );
					$db->query("UPDATE $this->table_verv 
						SET 
							caption='".addslashes(strip_tags($_POST['verv'.$Row['id']]))."', 
							beskrivelse='".addslashes(strip_tags($_POST['vervdesc'.$Row['id']]))."', 
							type='".addslashes(strip_tags($_POST['vervselector'.$Row['id']]))."',
							synlighet='$synlighet'
						WHERE id=".$Row['id']
					);
				}
			}

			if($whatToDo == "nyttverv"){
				$id = $this->opprettNyttVerv();
			}

			if($whatToDo == "moveup"){
				if (!is_numeric($_POST["vervid"])){ $this->fatalError("incorrect input"); }
				$id = $_POST["vervid"];
				$res = $db->query("SELECT position FROM $this->table_verv WHERE id='$id'");
				if ($res->num_rows != 1) $this->fatalError("invalid row count (".$res->num_rows.") returned");
				$row = $res->fetch_assoc();
				$old_p = $row['position'];
				$new_p = $old_p - 1;
				if ($new_p > 0){
					$db->query("UPDATE $this->table_verv SET position='$old_p' WHERE position='$new_p' LIMIT 1");
					$db->query("UPDATE $this->table_verv SET position='$new_p' WHERE id='$id' LIMIT 1");
				}
			}

			if($whatToDo == "movedown"){
				if (!is_numeric($_POST["vervid"])){ $this->fatalError("incorrect input"); }
				$id = $_POST["vervid"];
				$res = $db->query("SELECT position FROM $this->table_verv WHERE id='$id'");
				if ($res->num_rows != 1) $this->fatalError("invalid row count (".$res->num_rows.") returned");
				$row = $res->fetch_assoc();
				$old_p = $row['position'];
				$new_p = $old_p + 1;

				$res = $db->query("SELECT MAX(position) FROM $this->table_verv");
				if ($res->num_rows != 1) $this->fatalError("invalid row count (".$res->num_rows.") returned");
				$row = $res->fetch_row();
				
				if ($new_p < ($row[0]+1)){
					$db->query("UPDATE $this->table_verv SET position='$old_p' WHERE position='$new_p' LIMIT 1");
					$db->query("UPDATE $this->table_verv SET position='$new_p' WHERE id='$id' LIMIT 1");
				}
			}
			
			if ($whatToDo == "slettverv"){
				if (isset($_GET['delconf'])){
					$this->slettVerv($_POST['vervid']);
				} else {
					if (!is_numeric($_POST["vervid"])){ $this->fatalError("incorrect input"); }
					$Result = $db->query("SELECT caption FROM $this->table_verv WHERE id='".$_POST["vervid"]."'");
					$Row = $Result->fetch_assoc();
					if (!is_array($Row)) $this->fatalError("Vervet eksisterer ikke!"); 
					print("
						
						<h2>Bekreft sletting</h2>
						<p>
							Er du sikkert på at du vil slette vervet \"".stripslashes($Row["caption"])."\"?
						</p>
						<form method='post' action='".$this->generateUrl(array("noprint=true","delconf=true"))."'>
							<input type='hidden' name='whattodo' value='slettverv' />
							<input type='hidden' name='vervid' value='".$_POST["vervid"]."' />
							<input type='submit' value='     Ja      ' /> 
							<input type='button' value='     Nei      ' onclick=\"window.location='".$_SERVER['HTTP_REFERER']."'\"/>
						</form>
					"); 
					$redir = false;
				}
			}

			if ($redir == true){
				$this->redirect($this->generateUrl("edittypes"),"Lagret");
			}
		}
	}
	
	function endreVerv($vervId,$action){
		global $db,$memberdb;

		if (!$this->allow_editverv) return $this->permissionDenied();
		
		$output = "";
		if (!in_array($action,array('innm','innutm','stopp','slett'))) $this->fatalError("Invalid input .2");
		if (!is_numeric($vervId)) $this->fatalError("Invalid input .3");
		$v = $this->table_verv;
		$vh = $this->table_verv_history;
		$res = $db->query(
			"SELECT
				$v.caption, $vh.person, $vh.startdate, $vh.enddate 
			FROM 
				$v, $vh 
			WHERE 
				$vh.id='$vervId' 
			AND $vh.verv=$v.id"
		);
		if ($res->num_rows != 1){
			$this->fatalError("Invalid input .4");
		}
		$row = $res->fetch_assoc();

		$mm = $memberdb->getMemberById($row['person']);
		// print("<h1>Verv</h1>\n");

		$vervCaption = stripslashes($row['caption']);

		$startdate_unix = strtotime($row['startdate']);
		$startdate_code = $this->makeDateField("startdate", $startdate_unix, false);
		$startdate_js = strftime('{ day:%e, month:%m, year:%Y }',$startdate_unix);
		$maxdate_js = strftime('%m/%d/%Y',time());

		if ($action == "innm"){

		$js_code = '<script type="text/javascript">
		    //<![CDATA[	

				$(document).ready(function() {
					(new DatePicker("startdate", { maxDate: "'.$maxdate_js.'" } )).init();
				});
			
			//]]>
			</script> Tips: trykk på årstallet for å skrive inn et annet år.';
			
			$output .= "
				<h2>$mm->fullname</h2>
				<form method='post' name='membershipform' action=\"".$this->generateUrl("noprint=true")."\">
					<input type=\"hidden\" name=\"vno\" value=\"$vervId\" />
					<input type=\"hidden\" name=\"lagre\" value=\"innmelding\" />
					$vervCaption siden $startdate_code<br />
					<br />
					$js_code<br /><br />
					<input type=\"submit\" value=\"Lagre\" />
				</form>
			";

		} else if ($action == "innutm"){		

			$enddate_unix = strtotime($row['enddate']);
			$enddate_code = $this->makeDateField("enddate", $enddate_unix, false);
			$enddate_js = strftime('{ day:%e, month:%m, year:%Y }',$enddate_unix);
			
			$js_code = '<script type="text/javascript">
		    //<![CDATA[	

				$(document).ready(function() {
					(new DatePicker("startdate", { maxDate: "'.$maxdate_js.'" } )).init();
					(new DatePicker("enddate", { maxDate: "'.$maxdate_js.'" } )).init();
				});
						
			//]]>
			</script> Tips: trykk på årstallet for å skrive inn et annet år.';
			
			$output .= "
				<h2>$mm->fullname</h2>
				<form method='post' name='membershipform' action=\"".$this->generateUrl("noprint=true")."\">
					<input type=\"hidden\" name=\"vno\" value=\"$vervId\" />
					<input type=\"hidden\" name=\"lagre\" value=\"innutmelding\" />
					$vervCaption fra $startdate_code til $enddate_code<br />
					<br />
					$js_code
					<br /><br />
					<input type=\"submit\" value=\"Lagre\" />
				</form>
			";
		
		} else if ($action == "slett"){

			//$current = date("Y",time());

			$output .= "
				<form method='post' action=\"".$this->generateUrl("noprint=true")."\">
					<input type=\"hidden\" name=\"vno\" value=\"$vervId\" />
					<input type=\"hidden\" name=\"lagre\" value=\"slett\" />
					Er du sikker på at du vil SLETTE ".$mm->firstname."s tid som ".$row['caption']." fra vervhistorien? 
					Dette skal bare gjøres om den er lagt inn ved en feil!<br />
					<br />
					<input type='submit' value='     Ja      ' /> 
					<input type='button' value='     Nei      ' onclick=\"window.location='".$_SERVER['HTTP_REFERER']."'\"/>
				</form>
			";

		}
		return $output;

	}	

	function generateTimeStamp($d,$m,$y,$h,$i){
	// 'dd.mm.yyyy.hh.mm'
		$sd = array($d,$m,$y); 
		$st = array($h,$i);
		$date = $sd[2]."-".$sd[1]."-".$sd[0];
		$time = $st[0].":".$st[1].":00";
		$timestamp = strtotime ("$date $time"); 
		return $timestamp;
	}

	function lagreVervHistorie(){
		global $db;

		if (!$this->allow_editverv) return $this->permissionDenied();
		
		if (!is_numeric($_POST['vno'])) $this->fatalError("Ugyldig inn-data!");
		$vno = $_POST['vno'];
		$rs = $db->query("SELECT verv FROM $this->table_verv_history WHERE id='$vno'");
		$row = $rs->fetch_assoc();
		$v = $row['verv'];

		if ($_POST['lagre'] == "innmelding"){

			$fra = addslashes($_POST['startdate']);
			$fra_unix = strtotime($fra);
			
			if ($fra_unix > time()) $this->fatalError("Vervet kan ikke slutte i fremtiden.");
			$rs = $db->query("UPDATE $this->table_verv_history SET startdate='$fra' WHERE id='$vno'");

		} else if ($_POST['lagre'] == "innutmelding"){

			$fra = addslashes($_POST['startdate']);
			$fra_unix = strtotime($fra);
			$til = addslashes($_POST['enddate']);
			$til_unix = strtotime($til);
			
			if ($til_unix > time()) $this->fatalError("Vervet kan ikke slutte i fremtid!");
			if ($til < $fra) $this->fatalError("Vervet kan ikke slutte før det begynner!");

			$rs = $db->query("UPDATE $this->table_verv_history SET startdate='$fra', enddate='$til' WHERE id='$vno'");
	
		} else if ($_POST['lagre'] == "slett"){
		
			$this->slettFraVervHistorie($vno);
	
		} 

		if (count($this->coolUrlSplitted) > 0)
			$this->redirect($this->generateUrl(""),"Lagret"); 
		else
			$this->redirect($this->generateUrl("history=$v"),"Lagret"); 

	}

}

?>