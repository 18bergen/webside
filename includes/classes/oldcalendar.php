<?
/* DEPRECATED
class calendar {

	var $sideNr;
	var $eventsPerPage;
	var $onlyShowWhereInvited;
	var $totalEvents;
	var $totalEventsAvaible;
	var $startAt;
	var $endAt;


	function calendar(){
		global $db, $login, $valg, $memberdb;
		if ($login->loggedin == true){
			$this->eventsPerPage = $valg->itemsperpagecal;
			$this->onlyShowWhereInvited = true;

			$res = $db->query("SELECT COUNT(*) FROM kalender");
			$row = $res->fetch_array();
			$this->totalEvents = $row[0];
			
			$regexp = $memberdb->createMemberOfRegExp($login->ident);

			$res = $db->query("SELECT COUNT(*) ".
				"FROM kalender ".
				"WHERE kalender.invited REGEXP '$regexp'"
			);
			$row = $res->fetch_array();
			$this->totalEventsAvaible = $row[0];
		}
	}

	function fetchEvents($pageNo = -1){
		global $login, $db, $memberdb;
		$regexp = $memberdb->createMemberOfRegExp($login->ident);
		if ($pageNo != -1){
			$this->startAt = $pageNo;
		} else {
			if ($this->onlyShowWhereInvited == true){
				$rs = $db->query("SELECT COUNT(*) ".
					"FROM kalender ".
					"WHERE kalender.enddate < ".time()." ".
						"AND kalender.invited REGEXP '$regexp'"
				);
			} else {
				$rs = $db->query("SELECT COUNT(*) FROM kalender WHERE enddate < ".time());				
			}
			$rf = $rs->fetch_array();
			$this->startAt = $rf[0];
		}
		$this->endAt = $this->startAt + 10; 
		if ($this->onlyShowWhereInvited == true){
			if ($this->endAt > $this->totalEventsAvaible){
				$this->endAt = $this->totalEventsAvaible;
			}
		} else {
			if ($this->endAt > $this->totalEvents){
				$this->endAt = $this->totalEvents;
			}

		}
		if ($this->startAt < 0){ 
			$this->eventsPerPage = $this->eventsPerPage + $this->startAt; 
			$this->startAt = 0; 
		}
		
		$regexp = $memberdb->createMemberOfRegExp($login->ident);

		if ($this->onlyShowWhereInvited == true){
			$res = $db->query("SELECT organizers.image, organizers.caption, kalender.id, kalender.name, kalender.startdate, kalender.enddate, kalender.creator, kalender.location, kalender.organizer, kalender.invited, kalender.category, kalender.description ".
				"FROM kalender, organizers ".
				"WHERE kalender.invited REGEXP '$regexp' ".	
					"AND organizers.id=kalender.organizer ".
				"ORDER BY startdate ".
				"LIMIT $this->startAt, $this->eventsPerPage"
			);
		} else {
			$res = $db->query("SELECT organizers.image, organizers.caption, kalender.id, kalender.name, kalender.startdate, kalender.enddate, ".
				"kalender.creator, kalender.location, kalender.organizer, kalender.invited, kalender.category, kalender.description ".
				"FROM kalender, organizers ".
				"WHERE kalender.organizer=organizers.id ".
				"ORDER BY startdate ".
				"LIMIT $this->startAt, $this->eventsPerPage"
			);
		}
		$this->currentPage = ceil($this->endAt/$this->eventsPerPage);
		if ($this->onlyShowWhereInvited == true){
			$this->totalPages = ceil($this->totalEventsAvaible/$this->eventsPerPage);
		} else {
			$this->totalPages = ceil($this->totalEvents/$this->eventsPerPage);
		}
		$this->cache = Array();
		while ($row = $res->fetch_assoc()){
			array_push($this->cache,$row);
		}
	}

	function printEvents(){
		global $db;
		$cNo = "1";
		for ($i = 0; $i < count($this->cache); $i++){
			$RecordData = $this->cache[$i];
			$startdato = $RecordData["startdate"];
			$sluttdato = $RecordData["enddate"];
			if ($cNo == "2"){ $cNo = "1"; } else { $cNo = "2"; }
	 
			print("  <div class='calendarItem$cNo'>\n");
			$Result = $db->query("SELECT * FROM organizers WHERE id=".$RecordData["organizer"]);
			$orgobj = $Result->fetch_assoc();
			if ($orgobj["image"] == ""){
				print("<img src='images/merke4.gif' alt='Arrrangeres av ".$orgobj["caption"]."' width='13' height='13' />");
			} else {
				print("<img src='images/".$orgobj["image"]."' alt='Arrrangeres av ".$orgobj["caption"]."' width='13' height='13' />\n");
			}
			print("<a href='index.php?s=0008&amp;m=" . $RecordData['id'] . "' class='calendarSubject'>");
			print(urldecode($RecordData["name"]) . "</a>\n");
			if (date("d.m.Y",$startdato) == date("d.m.Y",$sluttdato)){
				print(" <div class='calendarDate'>(" . date("d.m.Y",$startdato) . ")</div>\n");
			} else {
				print(" <div class='calendarDate'>(" . date("d.m.Y",$startdato) . " - ". date("d.m.Y",$sluttdato) . ")</div>\n");
			}
			print("<br />\n");
			$body = stripslashes($RecordData["description"]);
			if (strlen($body)>100){ 
				$body = substr($body,0,97);
				// Reg exp for å fjerne eventuelle uavsluttede tager som blir hengende igjen etter
				// forkortingen av teksten
				$body = preg_replace("/".
					"\<".             // Starter med "<"
					"[^\>]".          // Matcher alle tegn bortsett fra ">"
					"*".              // Matcher 0 eller flere tegn
					"\z".             // Matcher slutten av teksten
					"/i",             // Kan matche flere ganger (denne er kanskje unødvendig)
					"",       // Erstatter matchet tekst med ingenting :)
					$body);
				$body = $body." ...";
			}
			print($body);
			unset($body);
			print("  </div>\n");
		}
	}

	function printEventsCompact(){
		global $db, $login;
		$cNo = "1";
		for ($i = 0; $i < count($this->cache); $i++){
			$RecordData = $this->cache[$i];
		

			$startdato = $RecordData["startdate"];
			$sluttdato = $RecordData["enddate"];
			if ($cNo == "2"){ $cNo = "1"; } else { $cNo = "2"; }
	 

			print("  <div class='calendarItem".$cNo."Mini'>\n");
			print("<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr><td width=\"16px\">");
			if ($RecordData["image"] == ""){
				print("<img src='images/merke4.gif' alt='Arrrangeres av ".$RecordData["caption"]."' width='13' height='13' />");
			} else {
				print("<img src='images/".$RecordData["image"]."' alt='Arrrangeres av ".$RecordData["caption"]."' width='13' height='13' />\n");
			}
			print("</td><td><a href='index.php?s=0008&amp;m=" . $RecordData['id'] . "' class='calendarSubjectMini'>");
			$bdy = stripslashes($RecordData["name"]);
			$table        = array_flip(get_html_translation_table(HTML_ENTITIES));
			$bdy    = strtr($bdy, $table);
			if (strlen($bdy) > 16) $bdy = substr($bdy,0,13)."...";
			$bdy = htmlentities($bdy);
			print($bdy."</a>\n");
			print("</td><td class='calendarDateMini'>");
			if (date("d.m.Y",$startdato) == date("d.m.Y",$sluttdato)){
				print(" (" . date("d.m.Y",$startdato) . ")");
			} else {
				print(" (" . date("d.m.Y",$startdato) . " - ...)");
			}
			print("    </td></tr></table>\n");
			print("  </div>\n");
		}
	}

	function printFooter(){
		global $db;
		if ($this->totalEvents == 0){
			print("<table width='100%'><tr>\n");
			print("  <td width='33%' align='left'>Forrige side</td>\n");
			print("  <td width='33%' align='center'>Side 1 av 1</td>\n");
			print("  <td width='33%' align='right'>Neste side</td>\n");
			print("</tr></table>\n");
		} else {
			if ($this->onlyShowWhereInvited == false){ 
				$urlsuffix = "&amp;showall=true"; 
			} else { 
				$urlsuffix = ""; 
			}
			if ($this->startAt > 0){ 
				$stan = $this->startAt-$this->eventsPerPage;
				//if ($stan < 0) $stan = 0;
				$bprev = "<a href='index.php?s=0008&amp;p=$stan$urlsuffix'>"; 
				$aprev = "</a>"; 
			} else { 
				$bprev = ""; 
				$aprev = ""; 
			}
			if ($this->onlyShowWhereInvited == true){
				if (($this->startAt+$this->eventsPerPage) < $this->totalEventsAvaible){ 
					$stan = $this->startAt + $this->eventsPerPage;
					$bnext = "<a href='index.php?s=0008&amp;p=$stan$urlsuffix'>"; 
					$anext = "</a>";
				} else {
					$bnext = ""; 
					$anext = ""; 
				}
			} else {
				if (($this->startAt+$this->eventsPerPage) < $this->totalEvents){ 
					$stan = $this->startAt + $this->eventsPerPage;
					$bnext = "<a href='index.php?s=0008&amp;p=$stan$urlsuffix'>"; 
					$anext = "</a>";
				} else {
					$bnext = ""; 
					$anext = ""; 
				}
			}
			print("<br />\n");
			print("<table width='100%'><tr>\n");
			print("  <td width='33%' align='left'>".$bprev."Forrige side".$aprev."</td>\n");
			if ($this->startAt == $this->endAt){
				print("  <td width='33%' align='center'>[ empty page ]</td>\n");
			} else {
				print("  <td width='33%' align='center'>[ ".($this->startAt+1)." - ".$this->endAt." ]</td>\n");
			}
			print("  <td width='33%' align='right'>".$bnext."Neste side".$anext."</td>\n");
			print("</tr></table>\n");
		}

		print("<br /><br />\n");
		print("Ikonforklaring:<br />");
		$Result = $db->query("SELECT * FROM organizers GROUP BY image");
		while ($Row = $Result->fetch_assoc()){
			if ($Row["image"] != ""){
				$Result2 = $db->query("SELECT * FROM organizers WHERE image='".$Row["image"]."'");
				$orgs = array();
				while ($Row2 = $Result2->fetch_assoc()){
					array_push($orgs,$Row2["caption"]);
				}
				$orgs = implode(", ",$orgs);
				print("<img src='images/".$Row["image"]."' alt='' width='13' height='13' /> Arrangeres av $orgs<br />");
			}
		}
		print("<img src='images/merke4.gif' alt='' width='13' height='13' /> Arrangeres av andre/ukjent<br />");
	}

	function printEvent($id){
		global $db, $login, $months;
		if (!is_numeric($id)){ 
			ErrorMessageAndExit("invalid post id!"); 
		}
		$Result = $db->query("SELECT * FROM kalender WHERE id=$id");
		if ($Result->num_rows == 0){
			ErrorMessageAndExit("Ukjent kalender-post!");
		}
		$Record = $Result->fetch_assoc();
		print("<hr size=1 noshade style=\"color: '#DDDDDD'\"><b>" . urldecode($Record["name"]) . "</b>");
		print("<br />\n<br />\n");

		$Result = $db->query("SELECT * FROM bildearkiv WHERE calobj=$id ORDER BY parentdir DESC LIMIT 1");
		if ($Result->num_rows > 0){
			print("<div style=\"float:right; width: 210px;\">\n");
			$archive = $Result->fetch_assoc();
			print("<a href=\"index.php?s=0005&amp;dir=".$archive['id']."\"><img src=\"bildearkiv".$archive['directory'].$archive['thumbnail']."\" width=\"200\" height=\"150\" class=\"BorderLink\" name=\"d".$archive['id']."\" alt=\"".$archive['caption']."\" /></a>\n");
			print("</div>\n");
		}

		$startday = date("d",$Record["startdate"]);
		$endday = date("d",$Record["enddate"]);
		$startmonth = $months[date("n",$Record["startdate"])-1];
		$endmonth = $months[date("n",$Record["enddate"])-1];	
		$starttime = date("H:i",$Record["startdate"]);
		$endtime = date("H:i",$Record["enddate"]);
		if ($startmonth == $endmonth){
			if ($startday == $endday){
				$datestr = $startday.". ".strtolower($startmonth);
			} else {
				$datestr = $startday.". til ".$endday.". ".strtolower($startmonth);
			}
		} else {
			$datestr = $startday.". ".strtolower($startmonth)." til ".$enddate.". ".strtolower($endmonth);
		}
		$datestr .= " ".date("Y",$Record["startdate"]);
		if ($starttime == "01:00") $starttime = "Ukjent";
		if ($endtime == "01:00") $endtime = "Ukjent";
		print("Dato: ".$datestr."<br />\n");
		print("Oppmøte: ".$starttime."<br />\n");
		print("Ferdig: ".$endtime."<br />\n");
		$Result = $db->query("SELECT * FROM organizers WHERE id=".$Record["organizer"]);
		if ($Result->num_rows == 0){
			print("Arrangør: Arrangøren finnes ikke i databasen (id = ".$Record["organizer"].")<br />\n");
		} else {
			$Row = $Result->fetch_assoc();
			print("Arrangør: ".$Row["caption"]."<br />\n");
		}

		$Result = $db->query("SELECT caption,id FROM locations WHERE id=".$Record["location"]);
		if ($Result->num_rows == 0){
			print("Sted: Stedet finnes ikke i databasen (id = ".$Record["location"].")<br />\n");
		} else {
			$Row = $Result->fetch_assoc();
			print("Sted: ".$Row["caption"]."<br />\n");
		}
		print("<br />\n");
		print(stripslashes($Record["description"])."<br />\n<br />\n");
		print("\n");
		$ingenreserveringer = true;
		$Result = $db->query("SELECT id,respgroup FROM reserveringer WHERE calendarobj=".$id." GROUP BY id");
		if ($Result->num_rows > 0){
			while ($Reservering = $Result->fetch_assoc()){
				$Result2 = $db->query("SELECT item FROM reserveringer WHERE calendarobj=$id AND id=".$Reservering["id"]." GROUP BY item");
				while ($Row2 = $Result2->fetch_assoc()){
					$Result3 = $db->query("SELECT status FROM reserveringer WHERE calendarobj=$id AND id=".$Reservering["id"]." AND item=".$Row2["item"]);
					$antall = $Result3->num_rows;
					$godkjent = 0;
					$status = "";
					while ($Row3 = $Result3->fetch_assoc()){
						if ($Row3["status"] == "waiting"){ $status = "waiting"; $godkjent = -1; }
						if ($Row3["status"] == "godkjent") $godkjent++;
					}
					$Result3 = $db->query("SELECT * FROM utstyr WHERE id=".$Row2["item"]);
					$utstyr = $Result3->fetch_assoc();
					if ($status == "waiting"){
						$status = "$antall stk. ".$utstyr["caption"]." reservert (venter på behandling) av ";
					} else {
						$status = "$godkjent stk. ".$utstyr["caption"]." reservert av ";
					}
					if ($godkjent != 0){
						if ($ingenreserveringer == true){
							print("<p>Følgende utstyr er reservert for denne hendelsen:\n<ul>\n");
							$ingenreserveringer = false;
						}
						$grp = findGroup($Reservering["respgroup"]);
						print("      <li style='font-family: Tahoma; font-size: 11px;'>$status ".$grp->CAPTION." (<a href=\"index.php?s=0071&amp;maximized=".$Reservering["id"]."\">#".$Reservering["id"]."</a>)</li>\n");
					}
				}
			}
		}
		if ($ingenreserveringer == false){
			print("</ul>\n</p>\n");
		}
		print("<hr size=1 noshade style=\"color: '#DDDDDD'\">\n");
		print("<a href='index.php?s=0025'>Legg til kommentar</a> | <a href='index.php?s=0008'>Tilbake til kalenderen</a> | <a href='index.php?s=0084&amp;pid=$id&amp;noprint=true'>Lagre som VCS</a><br />\n");
		if ($login->rights > 0){
			print("<a href='index.php?s=0010&amp;pid=$id'>Endre hendelse</a> | <a href='index.php?s=0044&amp;pid=$id'>Slett hendelse</a>");
		}
	}

}
*/
?>