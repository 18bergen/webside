<?php
function strip_bbcode($str,$bildegruppe = "general", $patrulje = ""){

	// Lenke
	$pattern = "/\[URL=(.+?)\](.+?)\[\/URL\]/i";
	$replacement = "\\2";
	$str = preg_replace("$pattern","$replacement",$str);

	// Bilde
	$pattern = "/\[IMAGE=([0-9]+?)\]/i";
	$replacement = "";
	$str = preg_replace($pattern,$replacement,$str);

	// Flytende bilde
	$pattern = "/\[IMAGE=([0-9]+?) FLOAT=(right|left)\]/i";
	$replacement = "";
	$str = preg_replace($pattern,$replacement,$str);
	
	// Fet tekst
	$pattern = "/\[B\](.+?)\[\/B\]/i";
	$replacement = "\\1";
	$str = preg_replace($pattern,$replacement,$str);

	// Skjev tekst
	$pattern = "/\[I\](.+?)\[\/I\]/i";
	$replacement = "\\1";
	$str = preg_replace($pattern,$replacement,$str);

	// Understreket tekst
	$pattern = "/\[U\](.+?)\[\/U\]/i";
	$replacement = "\\1";
	$str = preg_replace($pattern,$replacement,$str);

	// Overskrift
	$pattern = "/\[HEADER\](.+?)\[\/HEADER\]/i";
	$replacement = "\\1";
	$str = preg_replace($pattern,$replacement,$str);

	return $str;
}

function lagSitat($matches){
	global $memberdb;
    return "
    <div class=\"sitat1\">
    	<div class=\"tittel\">
    		".$memberdb->getMemberById($matches[2])->fullname." skrev:
    	</div>
    	<div class=\"innhold\">
    		".$matches[4]."
    	</div>
    </div>
   	";	
}
function lagSitat2($matches){
	global $memberdb;
    return "
    	<div class=\"sitat2\">
    		<div class=\"tittel\">
    			".$memberdb->getMemberById($matches[2])->fullname." skrev:
    		</div>
    		<div class=\"innhold\">
    			".$matches[4]."
    		</div>
    	</div>
    ";	
}
function lagSitat3($matches){
	global $memberdb;
    return "
    		<div class=\"sitat3\">
    			<div class=\"tittel\">
    				".$memberdb->getMemberById($matches[2])->fullname." skrev:
    			</div>
    			<div class=\"innhold\">
    				".$matches[4]."
    			</div>
    		</div>
    ";	
}
function lagMedlemsLink($matches, $customText = "", $customQuery = "") {
	global $memberdb, $login;
	$id = $matches[2];
	if (!$memberdb->isUser($id)) {
		ErrorMessage("Kunne ikke hente medlemsdata for medlem med id $id. Medlemmet eksisterer ikke.");
		return false;
	}
	$m = $memberdb->getMemberById($id);
	if (empty($m->slug)) $url = ROOT_DIR."/medlemsliste/medlemmer/$m->ident";
	else $url = ROOT_DIR."/medlemsliste/$m->slug";
	if ($customQuery != "") $url = "$url?$customQuery";
	$name = $m->firstname;
	//$name = ($m->show_fullname || !empty($login->ident)) ? $m->fullname : $m->firstname;
	if ($customText == "") return "<a href='$url'>$name</a>";
	else return "<a href='$url'>$customText</a>";
}

function lagMedlemsLinkMedTittel($matches){
	global $memberdb;
    return "<div style='border: 1px solid #888888; background:#FFFFFF; float: right; width: 90px; padding: 3px; margin: 5px; text-align: center; color: 666666;'>".$matches[3]."<br />".
	"<img src=\"fetchfile.php?src=medl".$matches[2]."&amp;fileext=".$memberdb->getMemberById($matches[2])->profilbilde."&amp;group=medlemmer\" style=\"width:60px;\" /><br />".
	"<a href='index.php?s=0012&amp;viewprofile=".$matches[2]."'>".$memberdb->getMemberById($matches[2])->fullname."</a></div>";	
}

function bilde($matches){
	global $db;
	$res = $db->query("SELECT extension,parent FROM ".DBPREFIX."images where id='".addslashes($matches[1])."'");
	$row = $res->fetch_assoc();
	$dir_id = $row['parent'];
	$res2 = $db->query("SELECT fullslug FROM ".DBPREFIX."cms_pages WHERE id='$dir_id'");
	$row2 = $res2->fetch_assoc();
	$mappe = $row2['fullslug'];

	$img_dir = ROOT_DIR."/uimages/".$mappe."/";
	
	if (count($matches) == 4){
		if (is_numeric($matches[2])){
			return "<img src=\"".$img_dir."image".$matches[1].".".$row['extension']."\" style=\"margin:4px; border:1px solid #000000; width:".$matches[2]."px; float:".$matches[3].";\" />";
		} else {
			return "<img src=\"".$img_dir."image".$matches[1].".".$row['extension']."\" style=\"margin:4px; border:1px solid #000000; float:".$matches[2]."; width:".$matches[3]."px;\" />";
		}

	} else if (count($matches) == 3){
		if (is_numeric($matches[2])){
			return "<img src=\"".$img_dir."image".$matches[1].".".$row['extension']."\" style=\"margin:4px; border:1px solid #000000; width:".$matches[2]."px;\" />";
		} else {
			return "<img src=\"".$img_dir."image".$matches[1].".".$row['extension']."\" style=\"margin:4px; border:1px solid #000000; float:".$matches[2].";\" />";
		}

	} else if (count($matches) == 2){
		return "<img src=\"".$img_dir."image".$matches[1].".".$row['extension']."\" style=\"margin:4px; border:1px solid #000000;\" />";
	}

}

function parse_bbcode($str,$bildegruppe = "general", $patrulje = ""){


	// Linebreaks
	$str = str_replace("\r\n","\n",$str);
	$str = str_replace("\r","\n",$str);

	// URL: Lokal link, åpnes i samme vindu
	$pattern = "/\[URL=https?:\/\/(www\.)?18bergen\.org\/(.+?)\](.+?)\[\/URL\]/i";
	$replacement = "<a href=\"https://www.18bergen.org/\\2\">\\3</a>";
	$str = preg_replace("$pattern","$replacement",$str);

	// URL: Ekstern link, åpnes i nytt vindu
	$pattern = "/\[URL=(.+?)\](.+?)\[\/URL\]/i";
	$replacement = "<a href=\"\\1\" target=\"_blank\">\\2</a>";
	$str = preg_replace("$pattern","$replacement",$str);




	// Flytende bilde med fastsatt bredde
	$pattern = "/\[IMAGE=([0-9]+?) WIDTH=([0-9]+?) FLOAT=(right|left)\]/i";
	$str = preg_replace_callback($pattern,"bilde",$str);

	// Flytende bilde med fastsatt bredde
	$pattern = "/\[IMAGE=([0-9]+?) FLOAT=(right|left) WIDTH=([0-9]+?)\]/i";
	$str = preg_replace_callback($pattern,"bilde",$str);

	// Bilde med fastsatt bredde
	$pattern = "/\[IMAGE=([0-9]+?) WIDTH=([0-9]+?)\]/i";
	$str = preg_replace_callback($pattern,"bilde",$str);

	// Flytende bilde
	$pattern = "/\[IMAGE=([0-9]+?) FLOAT=(right|left)\]/i";
	$str = preg_replace_callback($pattern,"bilde",$str);
	
	// Bilde
	$pattern = "/\[IMAGE=([0-9]+?)\]/i";
	$str = preg_replace_callback($pattern,"bilde",$str);
	


	// Eksternt bilde
	$pattern = "/\[IMG\](.+?)\[\/IMG\]/iU";
	$replacement = "<div style='width:350px;overflow:auto; border: 1px solid #ccc;'><img src='\\1' /></div>";
	$str = preg_replace($pattern,$replacement,$str);


	// Fet tekst
	$pattern = "/\[B\](.+?)\[\/B\]/iU";
	$replacement = "<b>\\1</b>";
	$str = preg_replace($pattern,$replacement,$str);

	// Skjev tekst
	$pattern = "/\[I\](.+?)\[\/I\]/i";
	$replacement = "<i>\\1</i>";
	$str = preg_replace($pattern,$replacement,$str);

	// Understreket tekst
	$pattern = "/\[U\](.+?)\[\/U\]/iU";
	$replacement = "<u>\\1</u>";
	$str = preg_replace($pattern,$replacement,$str);

	// Header
	$pattern = "/".
		"(\n){0,2}".				// Up to two linebreaks
		"\[HEADER\]".				// Matches [header]
		"(\n){0,2}".				// Up to two linebreaks
		"(.+?)".					// The content
		"(\n){0,2}".				// Up to two linebreaks
		"\[\/HEADER\]".				// Matches [/header]
		"(\n){0,2}".				// Up to two linebreaks
		"/is";						
		// i:letters in the pattern match both upper and lower case letters. 
		// s: a dot metacharacter in the pattern matches all characters, including newlines. Without it, newlines are excluded.
	$replacement = "<h2>\\3</h2>";
	$str = preg_replace($pattern,$replacement,$str);

	// Code
	$pattern = "/".
		"(\n)?".				// Up to two linebreaks
		"\[CODE\]".					// Matches [code]
		"(\n)?".				// Up to two linebreaks
		"(.+?)".					// The content
		"(\n)?".				// Up to two linebreaks
		"\[\/CODE\]".				// Matches [/code]
		"(\n)?".				// Up to two linebreaks
		"/is";						
		// i:letters in the pattern match both upper and lower case letters. 
		// s: a dot metacharacter in the pattern matches all characters, including newlines. Without it, newlines are excluded.
	$replacement = "<div class='code'><pre>\\3</pre></div>";
	$str = preg_replace($pattern,$replacement,$str);

	// Alignment
	$pattern = "/".
		"(\n){0,2}".				// Up to two linebreaks
		"\[ALIGN=([a-zA-Z]+)\]".	// Matches [align=a-zA-Z]
		"(\n){0,2}".				// Up to two linebreaks
		"(.+?)".					// The content
		"(\n){0,2}".				// Up to two linebreaks
		"\[\/ALIGN\]".				// Matches [/align]
		"(\n){0,2}".				// Up to two linebreaks
		"/is";						
		// i:letters in the pattern match both upper and lower case letters. 
		// s: a dot metacharacter in the pattern matches all characters, including newlines. Without it, newlines are excluded.
	$replacement = "<p style='text-align:\\2;'>\\4</p>";
	$str = preg_replace($pattern,$replacement,$str);

	
	// Member link
	$pattern = "/".
		"(\n)?".				// Up to two linebreaks
		"\[MEMBER=([0-9]+)\]".		// Matches [align=0-9]
		"(\n)?".				// Up to two linebreaks
		"/is";						
		// i:letters in the pattern match both upper and lower case letters. 
		// s: a dot metacharacter in the pattern matches all characters, including newlines. Without it, newlines are excluded.
	$str = preg_replace_callback($pattern,"lagMedlemsLink",$str);

	
	$str = str_replace("\n","<br />\n",$str);
	
	// Removes double linebreaks in <pre>
	$pattern = "/".
		"<pre>".				// Matches "<pre>"
		"(.*)".					// Matches all characters, 0 or more times
		"(<br \/>)".			// Matches "<br />"
		"(.*)".					// Matches all characters, 0 or more times
		"<\/pre>".				// Matches "</pre>"
		"/is";						
		// i: letters in the pattern match both upper and lower case letters. 
		// s: a dot metacharacter in the pattern matches all characters, including newlines. Without it, newlines are excluded.
	$replacement = "<pre>\\1\\3</pre>";
	$str = preg_replace($pattern,$replacement,$str);
	
	// Removes double linebreaks in <pre>
	$pattern = "/".
		"<pre>".				// Matches "<pre>"
		"(.*)".					// Matches all characters, 0 or more times
		"(<br \/>)".			// Matches "<br />"
		"(.*)".					// Matches all characters, 0 or more times
		"<\/pre>".				// Matches "</pre>"
		"/is";						
		// i: letters in the pattern match both upper and lower case letters. 
		// s: a dot metacharacter in the pattern matches all characters, including newlines. Without it, newlines are excluded.
	$replacement = "<pre>\\1\\3</pre>";
	$str2 = "";
	while ($str2 != $str) {
		$str2 = $str;
		$str = preg_replace($pattern,$replacement,$str2);
	}
	
	// Quote
	$pattern = "/".
		"(\n)?".				// Up to two linebreaks
		"\[QUOTE=([0-9]+)\]".		// Matches [align=a-zA-Z]
		"(\n)?".				// Up to two linebreaks
		"(.+?)".					// The content
		"(\n)?".				// Up to two linebreaks
		"\[\/QUOTE\]".				// Matches [/align]
		"(\n)?".				// Up to two linebreaks
		"/is";						
		// i:letters in the pattern match both upper and lower case letters. 
		// s: a dot metacharacter in the pattern matches all characters, including newlines. Without it, newlines are excluded.
	$str = preg_replace_callback($pattern,"lagSitat",$str);
	$str = preg_replace_callback($pattern,"lagSitat2",$str);
	$str = preg_replace_callback($pattern,"lagSitat3",$str);


	return $str;

}
?>
