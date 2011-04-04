<?

class counter {

	function updateCounter(){
		global $db;

		// Check IP address
		$ip = $_SERVER['REMOTE_ADDR'];
		$res = $db->query("SELECT * FROM ipliste WHERE ip='$ip'");
		if ($res->num_rows != 0){
			$db->query("UPDATE ipliste SET visits=visits+1 WHERE ip='$ip'");
		} else {
			$db->query("INSERT INTO ipliste (ip,visits,date) VALUES ('$ip', 1, '".time()."')");
			$db->query("UPDATE counter SET value=value+1 WHERE name='visitors'");
		}
		$db->query("UPDATE counter SET value=value+1 WHERE name='hits'");
	}
	
	function ouputCounterValue(){
		global $db;
		$res = $db->query("SELECT name,value FROM counter");
		while ($row = $res->fetch_assoc()){
			if ($row['name'] == "hits") $hits = $row['value'];
			if ($row['name'] == "visitors") $visitors = $row['value'];
		}
		print("<h3 class=\"small\">Besøksstatistikk</h3>\n");
		print("$visitors unike besøkende har vært innom. $hits sider har blitt vist. ");

	}

}

?>