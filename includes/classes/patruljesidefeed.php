<?

class patruljesidefeed {

	function inttonicestr($i){
		if ($i == 1) return "en";
		if ($i == 2) return "to";
		if ($i == 3) return "tre";
		if ($i == 4) return "fire";
		if ($i == 5) return "fem";
		if ($i == 6) return "seks";
		if ($i == 7) return "syv";
		if ($i == 8) return "åtte";
		if ($i == 9) return "ni";
		if ($i == 10) return "ti";
	}
	
	function printPatuljer(){
		global $db, $login, $memberdb, $months;
		print("<h2>Om patruljene</h2>\n");
		print("18. Bergen V-S har for tiden tre patruljer og en roverpatrulje. ");
		print("Klikk på patruljene for å se hva patruljeledelsen har skrevet om patruljen sin.");
		print("<ul>\n");
		foreach ($memberdb->patruljer as $patrulje){
			$res = $db->query("SELECT id FROM patruljesider WHERE gruppe='".$patrulje->id."' AND isindex=1");
			$row = $res->fetch_assoc();
			$res2 = $db->query("SELECT MAX(lastupdated) FROM patruljesider WHERE gruppe='".$patrulje->id."'");
			$row2 = $res2->fetch_array();	
			if ($res->num_rows == 0){
				print("<li>".$patrulje->caption."</li>\n");
			} else {
				$sopd = date("d. ",$row2[0]);
				$sopd = $sopd . strtolower($months[date("n",$row2[0])-1]);
				print("<li><a href=\"index.php?s=0003&amp;p=".$row["id"]."\">".stripslashes($patrulje->caption)."</a> (Sist oppdatert $sopd)</li>\n");
			}
		}
		print("</ul>\n");
	}

	function fetchPage($p){
		global $db, $memberdb;
		$currentPage = addslashes($p);
		if (!is_numeric($currentPage)){
			ErrorMessageAndExit("Invalid in-data!");
		}
		$Result = $db->query("SELECT caption,gruppe,mime,content FROM patruljesider WHERE id='$currentPage'");
		$row = $Result->fetch_assoc();
		if ($row['gruppe'] == "dummy"){
			$grpcaption = "Dummy";
		} else {
			$gruppe = $memberdb->getGroupById($row['gruppe']);
			$grpcaption = $gruppe->caption;
		}
		$row['grpcaption'] = $grpcaption;
		$this->printPage($row);
	}


	function printPage($data){
		if ($data['mime'] == 'text/html'){
			print("<h2>".$data['grpcaption']."</h2>\n");
			print("<h3>".stripslashes($data['caption'])."</h3>\n");
			print(stripslashes($data['content']));
		} else if ($data['mime'] == 'db/gb'){
			print("<h2>".$data['grpcaption']."</h2>\n");
			print("<h3>".stripslashes($data['caption'])."</h3>\n");
			print("<a href='index.php?s=0006&amp;g='".$data['gruppe']."'>Klikk her for å gå til denne gjesteboken</a>\n");
		}
	}


}

?>