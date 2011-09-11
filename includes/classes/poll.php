<?

class poll extends base {

	var $getvars = array("poll_id","new_poll","save_poll");

	/* Constants */
	var $polls;
	var $activePoll;
	
	var $table_polls = "polls";
	var $table_members = "members";
	
	var $bar_grey = "bar_gray.gif";
	var $bar_left = "bar_left.gif";
	var $bar_main = "bar_main.gif";
	var $bar_separator = "bar_separator.gif";

	var $allow_createnewpoll = false;
	
	/* Constructor */
	function poll(){
		$this->table_polls = DBPREFIX.$this->table_polls;
		$this->table_members = DBPREFIX.$this->table_members;
	}
	
	function initialize() {
		$this->initialize_base();
		$this->alreadyVoted = true;
		if ($this->isLoggedIn()) {
			$member = call_user_func($this->lookup_member,$this->login_identifier);
			$this->alreadyVoted = $member->voted;
		}		
		if (isset($_GET['poll_id'])){
			$this->activePoll = $_GET['poll_id'];	
			if (!is_numeric($this->activePoll)){ $this->fatalError("incorrect input"); }
		}
	}

	function run(){
		$this->initialize();
		$this->listPolls();
		if (isset($_GET['new_poll'])){
			return $this->newPollForm();
		} else if (isset($_GET['save_poll'])){
			return $this->saveNewPoll();
		} else if (isset($_POST["castvote"])){
			return $this->castVote();
		} else {
			return $this->outputAllPolls();
		}
	}

	function listPolls(){
		$this->polls = Array();
		$res = $this->query("SELECT * FROM $this->table_polls ORDER BY -startdate");
		while ($row = $res->fetch_assoc()){
			if (empty($this->activePoll) && ($row['active'])){
				$this->activePoll = $row['id'];
			}
			foreach ($row as $n => $v){
				$this->polls[$row['id']]->$n = stripslashes($v);
			}
		}
	}

	function castVote(){
		if (!isset($_POST["answer"])) $this->fatalError("Du må velge et svar.");
		$answer = $_POST["answer"];
		$this->vote($answer);
		header("Location: ".$_SERVER['HTTP_REFERER']); 
	}

	function vote($answer){
		if (!$this->isLoggedIn()) $this->fatalError("Ingen tilgang!");

		$member = call_user_func($this->lookup_member,$this->login_identifier);
		if ($member->voted) $this->fatalError("Du har allerede stemt!");

		$Result = $this->query("SELECT id FROM $this->table_polls WHERE active=1");
		if ($Result->num_rows != 1){
			$this->notSoFatalError("Antall aktive polls != 1");
			return 0;
		}
		if (!is_numeric($answer)){ $this->fatalError("incorrect input"); }

		// Update databases
		$this->query("UPDATE $this->table_polls SET result".$answer."=result".$answer."+1 WHERE active=1");
		$this->query("UPDATE $this->table_members SET voted=1 WHERE ident=".$member->ident);

	}

	function newPollFromPost(){
		if (!$this->isLoggedIn()) $this->fatalError("Ingen tilgang!");
		$member = call_user_func($this->lookup_member,$this->login_identifier);
		
		// Deaktiver gammel poll
		$this->query("UPDATE $this->table_polls SET active=0, enddate=".time()." WHERE active=1");
		$this->query("UPDATE $this->table_members SET voted=0");

		// Lagre ny poll
		$this->query("INSERT INTO $this->table_polls (active,startdate,creator,question,answer1,answer2,answer3,answer4,answer5,answer6,answer7,answer8,answer9,answer10) ".
			"VALUES (1,".time().",'".$member->ident."','".
			addslashes(strip_tags($_POST['quest']))."','".
			addslashes(strip_tags($_POST['ans1']))."','".
			addslashes(strip_tags($_POST['ans2']))."','".
			addslashes(strip_tags($_POST['ans3']))."','".
			addslashes(strip_tags($_POST['ans4']))."','".
			addslashes(strip_tags($_POST['ans5']))."','".
			addslashes(strip_tags($_POST['ans6']))."','".
			addslashes(strip_tags($_POST['ans7']))."','".
			addslashes(strip_tags($_POST['ans8']))."','".
			addslashes(strip_tags($_POST['ans9']))."','".
			addslashes(strip_tags($_POST['ans10']))."')"
		);
		
		$this->addToActivityLog("startet en ny <a href=\"".$this->generateCoolURL("")."\">avstemming</a>.",false,"major");
		$this->addToChangeLog("Startet ny <a href=\"".$this->generateCoolURL("")."\">avstemming</a>");
	}

	function outputPoll($id = -1, $vistidligere = true){
		$output = "";
		$this->initialize_base();
		if ($id == -1){
			$Result = $this->query("SELECT * FROM $this->table_polls WHERE active=1");
		} else {
			if (!is_numeric($id)){ $this->fatalError("incorrect input"); }
			$Result = $this->query("SELECT * FROM $this->table_polls WHERE id=$id");
		}
		$url_tidl = $this->generateCoolURL("");
		if ($Result->num_rows != 1){
			$this->notSoFatalError("Antall aktive polls != 1");
			if ($vistidligere == true){
				$output .= "<a href=\"$url_tidl\" class=\"white\">Tidligere avstemninger</a>\n";
			}
			return $output;
		} else {
			$Row = $Result->fetch_assoc();
			if ($this->alreadyVoted){
				
				$output .= parse_bbcode(stripslashes($Row["question"]))."<br /><br />\n<table cellpadding=\"0\" cellspacing=\"0\">\n";
				$total = 0;
				for ($i = 1; $i <= 10; $i++){
					$total = $total + $Row["result".$i]; 
				}
				for ($i = 1; $i <= 10; $i++){
					if ($Row["answer".$i] != ""){
						$totalWidth = 80;
						if ($Row["result".$i] == 0){
							$img1w = 0;
							$img2w = $totalWidth;
							$percent = 0;
						} else {
							$percent = $Row["result".$i]/$total*100;;
							$img1w = round($percent/100*$totalWidth);
							$img2w = round((100-$percent)/100*$totalWidth);
						}
						if ($img1w == 0){
							$output .= "  <tr><td class=\"smalltext\">".stripslashes($Row["answer".$i]).": </td><td><div class=\"progressbar\"><img src=\"$this->image_dir$this->bar_grey\" width=\"83\" height=\"10\" alt=\"\" /></div></td></tr>\n";
						} else {
							$output .= "  <tr><td class=\"smalltext\">".stripslashes($Row["answer".$i]).": </td><td class='nowrap'><div class=\"progressbar\"><img src=\"$this->image_dir$this->bar_left\" alt=\"\" width=\"1\" height=\"10\" /><img src=\"$this->image_dir$this->bar_main\" width=\"$img1w\" height=\"10\" alt=\"\" /><img src=\"$this->image_dir$this->bar_separator\" alt=\"\" width=\"2\" height=\"10\" />";
							if ($img2w != 0) $output .= "<img src=\"$this->image_dir$this->bar_grey\" alt=\"\" width=\"$img2w\" height=\"10\" />";
						$output .= "</div></td></tr>\n";
						}
					}
				}
				$output .= "</table>\n";
				$output .= "<div style='margin-top: 10px;' class='smalltext'>Antall stemmer: $total";
				if ($vistidligere == true){
					$output .= " | <a href=\"$url_tidl\" class=\"white\">Tidligere avstemninger</a>\n";
				} else {
					$output .= "<br /><br />\n";
				}
				$output .= "</div>";
			} else {
				$output .= "<form method=\"post\" action=\"".$this->generateURL("noprint=true")."\" style=\"margin-top:0px;\">\n";
				$output .= "<input type=\"hidden\" name=\"castvote\" value=\"1\" />\n";
				$output .= parse_bbcode(stripslashes($Row["question"]))."<br />\n";
				for ($i = 1; $i <= 10; $i++){
					$ans = stripslashes($Row["answer".$i]);
					if ($ans != ""){
						$output .= "<input type=\"radio\" name=\"answer\" value=\"$i\" id=\"ans$i\" /><label for=\"ans$i\">$ans</label><br />\n";
					}
				}	
				$output .= "<input type=\"submit\" value=\"  Stem  \" class=\"smallbutton\" /> ( <a href=\"$url_tidl\" class=\"smalltext\">Vis tidligere avstemninger</a> )<br />\n";
				$output .= "</form>\n";
			}
			return $output;
		}
	}

	function outputAllPolls(){
		$output = "";
		if ($this->allow_createnewpoll){
			$output .= '<p class="headerLinks"><a href="'.$this->generateURL("new_poll").'" class="add">Start en ny avstemming</a></p>';
		}
		$output .= "<ul>";
		foreach ($this->polls as $cPoll){
			if ($cPoll->id == $this->activePoll){
				$output .= "<li>";
				$output .= $this->outputPoll($cPoll->id, false);
				$output .= "</li>";
			} else {
				$output .= "<li><div><a href=\"".$this->generateURL("poll_id=$cPoll->id")."\">$cPoll->question</a></div></li>\n";
			}
		}
		$output .= "</ul>";
		unset($pollObj);
		return $output;
	}

	function newPollForm(){
		$output = "";
		if (!$this->allow_createnewpoll) return $this->permissionDenied();

		$output .= "
			<h2>Opprett ny avstemning</h2>
			<p>
				Her kan du opprette en ny avstemning. Skriv inn et spørsmål og fyll ut så mange svaralternativer du ønsker (maks 10). Om du vil bruke mindre enn ti svaralternativer, lar du de resterende feltene stå blanke.
			</p>
			<form method='post' action=\"".$this->generateURL(array("noprint=true","save_poll"))."\">
				<div style=\"text-align:right; width:420px;\">
					Spørsmål: <input type=\"text\" name=\"quest\" size=\"40\" class=\"ProfileEdit\" /><br />
					";
					for ($i = 1; $i <= 10; $i++){
						$output .= "Svaralternativ $i: <input type=\"text\" name=\"ans$i\" size=\"40\" class=\"ProfileEdit\" /><br />";
					}
					$output .= "
				</div><br />
				<input type=\"submit\" value=\"Lagre avstemning\" />
			</form>
		";
		return $output;
	}

	function saveNewPoll(){
		if (!$this->allow_createnewpoll) return $this->permissionDenied();
		$this->newPollFromPost();
		$this->redirect($this->generateURL(""),"Avstemningen er opprettet!");
	}

}

?>