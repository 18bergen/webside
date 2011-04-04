<?
class changelog extends base {
	var $getvars = array();

	var $tablename;
	var $row_template = "<tr><td class='smalltext'>%date%: %text%</td></tr>";
	var $login_ident;
	var $itemstoprint = 60;
	var $typeFilter = "";
	var $elemStyle = "margin-left:12px;margin-right: 20px;";
	var $colorMajor = "#ff9";
	var $colorComment = "#fff";
	var $colorUpdate = "#CCF0CC";
	
	var $allow_modify = false;
	
	function initialize(){
		$this->tablename = DBPREFIX."log_changes";
		$this->initialize_base();
		if (isset($_POST) && isset($_POST['savechange'])){
			$this->saveChange();
		}
	}

	function run() {
		$this->initialize();
		$output = "<div style='border: 1px solid #999; padding: 10px; margin-bottom:10px'>
		Fargekoder: 
			<span style='background:".$this->colorMajor."'>Nytt innhold</span>
			<span style='background:".$this->colorUpdate."'>Oppdatert innhold</span>
			<span style='background:".$this->colorComment."'>Kommentar</span>
			</div>
		";
		$output .= $this->printActivity();
		
		/*
		print "<h3>Utviklingslogg:</h3>";
		if ($this->allow_modify) {
			$this->printForm();
		}
		print "<table>";
		*/
		//$this->printChanges($this->itemstoprint_full);
		//print "</table>";

		return $output;		
	}
	
	function printActivity($headers = true, $memberLinks = true) {	
		$output = "";
		if (empty($this->typeFilter)) {
			$res = $this->query("SELECT id,timestamp,user,activity,page,type FROM ".DBPREFIX."log_activity ORDER BY id DESC LIMIT ".$this->itemstoprint);
		} else {
			$res = $this->query("SELECT id,timestamp,user,activity,page,type FROM ".DBPREFIX."log_activity WHERE type=\"".$this->typeFilter."\" ORDER BY id DESC LIMIT ".$this->itemstoprint);			
		}
		//if (empty($this->login_identifier)){ print "<p>Logg inn for å vise aktivitetslogg</p>"; return; }
		$bgcol = "#FFFFFF";
		$oldDate = "";
		while ($row = $res->fetch_assoc()){
			if ($headers) {
				$curDate = ucfirst(strftime("%A %e. %B", strtotime($row["timestamp"])));
				if ($oldDate != $curDate) {
					if ($curDate == ucfirst(strftime("%A %e. %B",time()))) 
						$printDate = "I dag (".strftime("%A", strtotime($row["timestamp"])).")";
					else if ($curDate == ucfirst(strftime("%A %e. %B",time()-86400))) 
						$printDate = "I går (".strftime("%A", strtotime($row["timestamp"])).")";
					else 
						$printDate = $curDate;
					$output .= "<h4 style='border-bottom:1px solid #ccc; color: #777; margin:5px; 0px 5px 0px;'>$printDate</h4>";
					$oldDate = $curDate;
				}
			}
			if (!empty($this->typeFilter)) {
				$typest = "";
			} else {
				if ($row['type'] == 'major') 
					$typest = ' background: '.$this->colorMajor.'; padding: 2px 0px; margin-top: 1px;';
				else if ($row['type'] == 'comment') 
					$typest = ' background: '.$this->colorComment.'; padding: 2px 0px; margin-top: 1px;';
				else if ($row['type'] == 'update') 
					$typest = ' background: '.$this->colorUpdate.'; padding: 2px 0px; margin-top: 1px;';
				else 
					$typest = 'font-size:#555;';
			}
			
			if ($headers) $datetime = date("H:i:s",strtotime($row["timestamp"]));
			else $datetime = strftime("%e. %b",strtotime($row["timestamp"]));
			
			if (!empty($row["user"])) {
				if ($memberLinks) {
					$member = call_user_func($this->make_memberlink,$row['user']);
				} else { 
					$member = call_user_func($this->lookup_member,$row['user']);
					$member = $member->firstname;
				}
			} else {
				$member = "Gjest";
			}			 
			$output .= "<div class='smalltext' style='".$this->elemStyle."$typest'>".$datetime.": 
				".$member."
				".stripslashes($row["activity"]).
				"</div>
			";
		}
		return $output;
	}
	
	function saveChange(){
		if (!$this->allow_modify) $this->fatalError("not allowed");
		
		$this->query("INSERT INTO ".$this->tablename." 
			(changes,member) VALUES (
				\"".addslashes($_POST['change'])."\",
				".intval($this->login_ident)."
			)"
		);
		header("Location: ".$this->generateURL(""));
		exit();
	}
	
	function printChanges($limit = 5){
		$res = $this->query("SELECT changes FROM ".$this->tablename." ORDER BY timestamp DESC LIMIT $limit");
		$no = 0;
		while ($row = $res->fetch_assoc()){
			$no = !$no;
			$r1a = array();	$r2a = array();
			$r1a[0] = "%no%";			$r2a[0]  = $no+1;
			$r1a[1] = "%text%";			$r2a[1]  = stripslashes($row['changes']);
			$r1a[2] = "%datetime%";		$r2a[2]  = date("d.m.Y, H:i",strtotime($row['timestamp']));
			$r1a[3] = "%date%";			$r2a[3]  = date("d.m",strtotime($row['timestamp']));
			$outp = str_replace($r1a, $r2a, $this->row_template);
			print $outp;
		}
	}
	
	function printForm(){
		print "
			<form method='post' action='".$this->generateURL("noprint=true")."' />
				<input type='hidden' name='savechange' value='true' />
				<input type='text' name='change' style='width:430px' />
				<input type='submit' value='Legg til' />
			</form>
		";
	}
	
}
?>