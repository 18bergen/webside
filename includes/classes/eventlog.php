<?

class eventLog {

	/* Constructor */
	function eventLog(){
		$this->queryLog = Array();
	}

	function addToErrorLog($str){
		global $db, $login;
		$msg = addslashes(htmlspecialchars($str));
		$uname = 0;
		if ($login) $uname = $login->getUserId();
		$page = $_SERVER["REQUEST_URI"];
		if ((isset($_SERVER['HTTP_REFERER'])) && ($_SERVER['HTTP_REFERER'] != "/")){
			$referer = addslashes($_SERVER['HTTP_REFERER']);
		} else {
			$referer = "No referer";
		}
		$browser = (isset($_SERVER['HTTP_USER_AGENT']) ? addslashes($_SERVER['HTTP_USER_AGENT']) : "Ukjent");
		$db->query("INSERT INTO ".DBPREFIX."log_error (user, msg, page, referer, browser) VALUES ($uname,\"$msg\",\"$page\",\"$referer\",\"$browser\")",false);
	}

	function addToActivityLog($str,$unique = false,$type="minor"){
		global $db, $login;
		$activity = addslashes($str);
		$uname = 0;
		if ($login) $uname = $login->getUserId();
		if ($unique) {
			$res = $db->query("SELECT id,activity FROM ".DBPREFIX."log_activity WHERE user=$uname AND activity=\"$activity\" AND timestamp > DATE_SUB(NOW(), INTERVAL 1 DAY)");
			if ($res->num_rows == 1) {
				$row = $res->fetch_assoc();
				$id = $row['id'];
				$db->query("UPDATE ".DBPREFIX."log_activity SET timestamp=NOW() WHERE id=$id");
				return;
			}
		}
		$page = addslashes("index.php?".$_SERVER["QUERY_STRING"]);
		$browser = (isset($_SERVER['HTTP_USER_AGENT']) ? addslashes($_SERVER['HTTP_USER_AGENT']) : "Ukjent");
		$db->query("INSERT INTO ".DBPREFIX."log_activity (user, activity, page, browser,type) VALUES (\"$uname\",\"$activity\",\"$page\",\"$browser\",\"$type\")");
	}

	function addToQueryLog($str){
		//array_push($this->queryLog,"querylog disabled");
		array_push($this->queryLog,$str);
	}

}

?>