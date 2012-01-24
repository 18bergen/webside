<?

class debug extends base {

	var $getvars = array("setfixed");
	var $wideMode = true;

	function initialize() {
		$this->initialize_base();
	}
	
	function run() {
		global $memberdb,$db;
		
		$this->initialize();
	
		if (isset($_GET['setfixed']) && is_numeric($_GET['setfixed'])) {
			$id = $_GET['setfixed'];
			$res = $this->query("SELECT msg FROM ".DBPREFIX."log_error WHERE id='$id'");
			$row = $res->fetch_assoc();
			$msg = $row['msg'];
			$res = $this->query("UPDATE ".DBPREFIX."log_error SET fixed='1' WHERE msg=\"$msg\"");
			$this->redirect($this->generateURL(""));
		}

		$output = "<style type=\"text/css\"><!--
		.smallcode pre {
			padding: 0px;
			margin: 0px;
		}
		.smallcode del {
			padding: 0px;
			margin: 0px;
		}
		.smallcode {
			font-family : 'Courier New';
			font-size   : 12px;
			color       : #000000;
			padding: 2px;
			margin:2px;
		}
		//--></style>
		";

/*
		<p>
			Current includefile path: <strong>".__FILE__."</strong><br />
			Current includefile dirname: <strong>".dirname(__FILE__)."</strong><br />
			Current line: <strong>".__LINE__."</strong><br />
			Current path: <strong>".realpath("./")."</strong><br />
		</p>
*/
		
		$output .= "
		<!--position: absolute; left: 0px; top: 0px; z-index:1000; background:#FFFFFF; width: 4000px; height: 6000px; -->
		<div>
		

		<h2>Server</h2>
		<p>
		".sprintf("<pre>Server software: %s\nPHP version: %s\nMySQL version: %s\nPath: %s\n</pre>",
			$_SERVER['SERVER_SOFTWARE'],phpversion(),$db->server_info,realpath("../"))."
		</p>

		<h2>MySQL</h2>
		<p>
		".sprintf("<pre>Connected to %s\nClient library version: %s\nProtocol version: %s\nCharacter set is %s\n</pre>",
			$db->host_info,$db->get_client_info(),$db->protocol_version,$db->character_set_name())."
		</p>		
		";

		
		$q1 = "SELECT id,fixed,count(id),max(timestamp) as timestamp,user,msg,page,referer,browser FROM ".DBPREFIX."log_error WHERE 1=1";
		$q2 = "SELECT id,fixed,count(id),max(timestamp) as timestamp,user,msg,page,referer,browser FROM ".DBPREFIX."log_error WHERE 1=2";
		$res = $this->query("SELECT text FROM ".DBPREFIX."knownbots");
		while ($row = $res->fetch_assoc()) {
			$q1 .= " AND browser NOT LIKE '%".$row['text']."%'";
			$q2 .= " OR browser LIKE '%".$row['text']."%'";
		}
		$q1 .= " GROUP BY msg ORDER BY id DESC LIMIT 50";
		$q2 .= " GROUP BY msg ORDER BY id DESC LIMIT 50";
		
			
		$output .= "<h2>Siste 50 fra errorlog (bruker)</h2>";
		$res = $this->query($q1);
		while ($Row = $res->fetch_assoc()){
			$output .= "<p style='background:white;font-size:10px;margin-top:5px;padding:3px;'>".$Row["timestamp"]." (gjentatt ".$Row["count(id)"]." ganger) av ".(!empty($Row["user"]) ? call_user_func($this->make_memberlink,$Row['user']) : "gjest")."<br />\n".
			"  <strong>".$Row["msg"]."</strong><br />\n".
			"  Side: ".htmlentities($Row["page"])."<br />\n".
			"  <span style='color:#666;'>Referer: ".htmlentities($Row["referer"])."</span><br />\n".
			"  <span style='color:#666;'>Browser: ".$Row["browser"]."</span></p>";			
		}
		$res->close();
		
		
		$output .= "<h2>Siste 50 fra errorlog (bots)</h2>";
		$res = $this->query($q2);
		while ($Row = $res->fetch_assoc()){
			$output .= "<p style='background:white;font-size:10px;margin-top:5px;padding:3px;'>".$Row["timestamp"]." (gjentatt ".$Row["count(id)"]." ganger) av ".(!empty($Row["user"]) ? call_user_func($this->make_memberlink,$Row['user']) : "gjest")."<br />\n".
			"  <strong>".$Row["msg"]."</strong><br />\n".
			"  Side: ".$Row["page"]."<br />\n".
			"  <span style='color:#666;'>Referer: ".$Row["referer"]."</span><br />\n".
			"  <span style='color:#666;'>Browser: ".$Row["browser"]."</span></p>";			
		}
		$res->close();
			
		
		$debugitems = array(array("Session variables", $_SESSION),
							array("Cookies", $_COOKIE),
							array("Environment variables", $_ENV),
							//array("Globals", $GLOBALS),
							array("Server", $_SERVER)
							);
		for ($i = 0; $i < count($debugitems); $i++){
			$output .= "<h2>".$debugitems[$i][0]."</h2>\n";
			$output .= "<p class='smallcode'>\n";
			foreach ($debugitems[$i][1] as $tname => $tvalue){
				if (is_string($tvalue)) $tvalue = strip_tags($tvalue);
				$output .= "&nbsp;&nbsp;<nobr>".$tname." = ".$tvalue."</nobr><br />\n";
			}
			$output .= "</p>";
		}
				
		$output .="</div>";
		
		return $output;
		
	}
}
?>
