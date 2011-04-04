<?php

  /******************************************************
    Who is online 1.0
    Written by Erik Holman at 19 September 2003
    Started at 12:00 and ended at 14:45

    This scripts tracks how many visitors there are
    online at the moment of visiting.

    ======= HOW TO WORK =======
    ===========================
    include somewhere in your page track( "action" [, $member_id] );
    There are some optional parameters.
    For example:
    
      include( "who.php" );
      track( "Visiting guestbook" , $_SESSION['member_id'] );
      // OR
      track( "Browsing forum" );
      // OR
      track( );
    
    ===========================
    ===========================
    BUG UPDATES
    22 September 2003
      - Always one visitor with no SESSIONID is online :S This one won't be shown
    ===========================
    ===========================
    SQL Query to create table
    CREATE TABLE myphp_who (
      who_sessid varchar(32) NOT NULL default '',
      who_mem bigint(20) NOT NULL default '0',
      who_what text NOT NULL,
      who_date varchar(12) NOT NULL default ''
    )
    ===========================

    Visit www.my-php.tk for more free PHP scripts.
  ******************************************************/

  $inactive_minutes = 5;    //in minutes   //The interval before a row will be deleted from
                                           //the database. (Timer for Inactivity)
  $default_action   = "Visiting Homepage"; //Default action when no action is given

  /******************************************************
    Please don't edit anything underneath this line :)
  ******************************************************/

function printWhosOnline(){
	global $db, $memberdb, $site_rootdir;
	$q1 = "SELECT * FROM ".DBPREFIX."onlineusers WHERE sessionid != ''";
	$res = $db->query("SELECT text FROM ".DBPREFIX."knownbots");
	while ($row = $res->fetch_assoc()) {
		$q1 .= " AND browser NOT LIKE '%".$row['text']."%'";
	}
	$q1 .= " ORDER BY timestamp DESC";
	
	$res = $db->query($q1);
    $visitors = array();
	$members = array();
    while($record = $res->fetch_object()){
    	if($record->memberid != 0){
			array_push($members,$record->memberid);
		} else {
			array_push($visitors,$record->ip);
		}
    }
    
	if (count($members) > 0){
		if (count($members) == 1){
			$buffer = "Akkurat nå er det ett medlem (";
		} else {
			$buffer = "Akkurat nå er det ".count($members)." medlemmer (";		
		}
		$i = 0;
		foreach ($members as $member){
			$i++;
			//$usr = $memberdb->getMemberById($member);
			$buffer .= $memberdb->makeMemberLink($member);
			//"<a href='$site_rootdir/medlemsliste/medlemmer/".$usr->ident."'>".$usr->firstname."</a>";
			if ($i != count($members)) $buffer .= ", ";
		}
		$buffer .= ")";
		if (count($visitors) > 0){
			$buffer .= " og ".count($visitors)." gjester";
		}
		$buffer .= " her.";
	} else {
		if (count($visitors) == 1){
			$buffer = "Akkurat nå er det bare deg her.";
		} else {
			$buffer = "Akkurat nå er det ".count($visitors)." gjester her.";
		}
	}
	//$buffer .= $bot;
	$buffer .= ' <a href="/hvemeronline">Mer info</a>';
    return $buffer;
}

//In this function there will be displayed which members are online and 
//what they or your visitors are watching.
function who(){
	global $db, $memberdb;
	
	$q1 = "SELECT * FROM ".DBPREFIX."onlineusers WHERE sessionid != ''";
	$q2 = "SELECT * FROM ".DBPREFIX."onlineusers WHERE sessionid != '' AND (1=2";
	$res = $db->query("SELECT text FROM ".DBPREFIX."knownbots");
	while ($row = $res->fetch_assoc()) {
		$q1 .= " AND browser NOT LIKE '%".$row['text']."%'";
		$q2 .= " OR browser LIKE '%".$row['text']."%'";
	}
	$q1 .= " ORDER BY timestamp DESC";
	$q2 .= ") ORDER BY timestamp DESC";
	
	$res = $db->query($q1);
	$buffer = "";
	$output = "<h3>Folk</h3>";
	while($record = $res->fetch_object()){
		if(session_id() == $record->sessionid ){
			$you_pr = "<strong>";
			$you_sf = "</strong>";
      	} else {
			$you_pr = "";
			$you_sf = "";
      	}
      	$m_ago = date("i",(time()-$record->timestamp));
        $s_ago = date("s",(time()-$record->timestamp));
        if ($m_ago == 0) {
        	$str_ago = "$s_ago sekunder siden";
        } else {
        	$str_ago = "$m_ago min, $s_ago sek siden";         	
         }
      	if ($record->memberid != 0){
      		$buffer .= "
                  <li style='margin-bottom: 8px;'>
						$you_pr".$memberdb->getMemberById($record->memberid)->firstname."$you_sf<br />						
						Lastet \"$record->doingwhat\" for $str_ago<br />
						Nettleser: ".$record->browser."
                  </li>         
             ";
         } else {
   			$buffer .= "
					<li style='margin-bottom: 8px;'>
						".$you_pr."Gjest (".long2ip($record->ip).")".$you_sf."
						Lastet \"$record->doingwhat\" for $str_ago<br />
						Nettleser: ".$record->browser."
                    </li>   
             ";
		}
	}
    $output .= "
		<ul>
			$buffer
		</ul>
         
	";
	
	$res = $db->query($q2);
	$buffer = "";
	$output .= "<h3>Søkemotorer, etc..</h3>";
	while($record = $res->fetch_object()){
		if(session_id() == $record->sessionid ){
			$you_pr = "<strong>";
			$you_sf = "</strong>";
      	} else {
			$you_pr = "";
			$you_sf = "";
      	}
      	$m_ago = date("i",(time()-$record->timestamp));
        $s_ago = date("s",(time()-$record->timestamp));
        if ($m_ago == 0) {
        	$str_ago = "$s_ago sekunder siden";
        } else {
        	$str_ago = "$m_ago min, $s_ago sek siden";         	
         }
      	if ($record->memberid != 0){
      		$buffer .= "
                  <li style='margin-bottom: 8px;'>
						$you_pr".$memberdb->getMemberById($record->memberid)->firstname."$you_sf<br />						
						Lastet \"$record->doingwhat\" for $str_ago<br />
						Nettleser: ".$record->browser."
                  </li>         
             ";
         } else {
   			$buffer .= "
					<li style='margin-bottom: 8px;'>
						".$you_pr."Gjest (".long2ip($record->ip).")".$you_sf."
						Lastet \"$record->doingwhat\" for $str_ago<br />
						Nettleser: ".$record->browser."
                    </li>   
             ";
		}
	}
    $output .= "
		<ul>
			$buffer
		</ul>
         
	";
	return $output;
}

//The visit will be logged to track the visitor
function track($action = "" , $id = '0') {
    global $db,$inactive_minutes;
    
    $now = time();   // yyyymmddhhmm, ex. 200009021431 
    /*
    	Users who have been inactive for more than $inactive_minutes are removed
    */
    $timeago = time() - ($inactive_minutes*60);
    $db->query("DELETE FROM ".DBPREFIX."onlineusers WHERE timestamp < " . $timeago);
    if ($action == "") $action = "surfer rundt";
    $res =  $db->query("SELECT * FROM ".DBPREFIX."onlineusers WHERE sessionid='".session_id()."'");
    $ip = ip2long($_SERVER['REMOTE_ADDR']);
    $browser = (isset($_SERVER['HTTP_USER_AGENT']) ? addslashes($_SERVER['HTTP_USER_AGENT']) : "Ukjent");
	if($res->num_rows == 1){
     	$db->query("UPDATE ".DBPREFIX."onlineusers 
     		SET 
     			memberid='$id', 
     			doingwhat='".addslashes($action)."', 
     			timestamp='".time()."',
     			browser='$browser',
     			ip='$ip'
     		WHERE 
     			sessionid='".session_id()."'"
     	);
    } else {
    	$db->query("INSERT INTO ".DBPREFIX."onlineusers
        	(
        		sessionid,
        		memberid,
        		doingwhat,
        		timestamp,
        		browser,
        		ip
        	)
        	VALUES
        	(
                '".session_id()."', 
                '$id',
                '".addslashes($action)."',
                '".time()."',
                '$browser',
                '$ip'
            )"
         );
    }
}
 
?>