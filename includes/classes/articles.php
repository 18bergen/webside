<?
/* DEPRECATED :
class articles extends base {

	function printList(){
		global $db;
		$Result = $db->query("SELECT id,subject FROM artikler ORDER by postdate DESC");
		print("  <ul>\n");
		while ($article = $Result->fetch_assoc()){
			print("      <li><a href=\"index.php?s=0040&amp;i=".$article['id']."\">".stripslashes($article['subject'])."</a><br /></li>\n");
		}
		print("  </ul>\n");
	}

	function fetchArticle($article_no){
		global $db;
		if (!is_numeric($article_no)){ ErrorMessageAndExit("incorrect input"); }
		$Result = $db->query("SELECT id,subject,creator,body FROM artikler WHERE id='".addslashes($article_no)."'");
		if ($Result->num_rows != 1) ErrorMessageAndExit("Artikkelen finnes ikke!");
		$article = $Result->fetch_assoc();
		return $article;		
	}

	function printArticle($article){
		print("<h1>".stripslashes($article['subject'])."</h1>\n");
		print("  <p>\n");
		print("  ".stripslashes($article['body'])."\n");
		print("  </p>\n");
		print("  <p>");
		if (($this->isLoggedIn()) && ($login->ident == $article['creator'])){ 
			print(
				"<a href=\"index.php?s=0040&amp;i=".$article['id']."&amp;edit=1\">Rediger artikkelen</a> | ".
				"<a href=\"index.php?s=0040&amp;i=".$article['id']."&amp;delete=1\">Slett artikkelen</a> | "
			); 
		}
		print("<a href=\"index.php?s=0040\">Tilbake til artikler</a></p>\n");
	}

	function printEditForm($article_no = -1){
		global $db, $login;
		if (!is_numeric($article_no)){ ErrorMessageAndExit("incorrect input"); }
		$login->checkAccess(0);
		print("<p>\n");
		print("<form method=\"post\" action=\"index.php?s=0040&amp;noprint=true\">\n");
		if ($article_no != -1){	
			$Result = $db->query("SELECT id,subject,creator,body FROM artikler WHERE id='".addslashes($_GET['i'])."'");
			if ($Result->num_rows != 1) ErrorMessageAndExit("Artikkelen finnes ikke!");
			$article = $Result->fetch_assoc();
			if ($article['creator'] != $login->ident) ErrorMessageAndExit("Du er ikke skribent av denne artikkelen!");
			$subject = stripslashes($article['subject']);
			$body = stripslashes(str_replace("<br />","\r\n",$article['body']));
			print("<h1>Rediger artikkel</h1>\n");
			print("<input type=\"hidden\" name=\"i\" value=\"".$article_no."\" />\n");
		} else {
			$subject = "";
			$body = "";
			print("<h1>Ny artikkel</h1>\n");
		}
		print "
			<table>
			<tr><td>Tittel: </td><td><input type=\"text\" name=\"subject\" size=\"40\" value=\"$subject\" /></td></tr>
			</table><br />
			Artikkel: <br />
			<textarea name=\"body\" cols=\"50\" rows=\"20\">$body</textarea><br />
			<input type=\"checkbox\" name=\"parsebrs\" id=\"parsebrs\" checked=\"checked\" />
			<label for=\"parsebrs\">Bevar linjeskift (\\n -&gt; &lt;br /&gt;)</label>
			<br /><br />
			<input type=\"submit\" value=\"Lagre artikkel\" />
			</form>
			</p>
			<p>
			Tips: Om du skal skrive en lengre tekst, anbefaler vi at du skriver i f.eks. Word, og deretter kopierer og limer teksten inn her, slik at du ikke mister alt om noe går galt.
			</p>
		";
	}

	function saveArticle(){
		global $db, $login;
		$login->checkAccess(0);
		$creator = addslashes($login->ident);
		$subject = addslashes(strip_tags($_POST['subject']));
		if ((isset($_POST["parsebrs"])) && ($_POST["parsebrs"] == "on")){
			$body = addslashes(str_replace("\r\n","<br />",$_POST['body']));
		} else {
			$body = $_POST['body'];
		}
		$postdate = time();
		if ((isset($_POST['i'])) && (is_numeric($_POST['i']))){
			$Result = $db->query("SELECT creator FROM artikler WHERE id='".$_POST['i']."'");
			$Row = $Result->fetch_assoc();
			if ($Row['creator'] != $login->ident) ErrorMessageAndExit("Du er ikke skribent av denne artikkelen!");
			$db->query("UPDATE artikler SET subject='$subject', body='$body' WHERE id='".$_POST['i']."'");
			$hdr = "Artikkelen er lagret";
			$msg = urlencode("Endringene i artikkelen din er lagret. Du vil nå bli sendt tilbake til artikkelsiden om få sekunder. Om du ikke ser endringene i artikkelen din, trykk Oppdater i verktøylinjen.");
		} else {
			$db->query("INSERT INTO artikler (creator,postdate,subject,body) VALUES ('$creator','$postdate','$subject','$body')");
			$hdr = "Artikkelen er lagret";
			$msg = urlencode("Artikkelen din er opprettet. Du vil nå bli sendt tilbake til artikkelsiden om få sekunder. Om du ikke ser artikkelen din, trykk Oppdater i verktøylinjen.");
		}
		$url = urlencode("index.php?s=0040");
		header("Location: index.php?s=0043&hdr=$hdr&msg=$msg&url=$url\n\n"); 
		exit;
	}

	function deleteArticle($article_no){
		global $db, $login;
		$login->checkAccess(0);
		if (!is_numeric($article_no)) ErrorMessageAndExit("Invalid input!");
		if (isset($_GET['delconf'])){
			$Result = $db->query("SELECT creator FROM artikler WHERE id='$article_no'");
			if ($Result->num_rows != 1) ErrorMessageAndExit("Artikkelen eksisterer ikke!");
			$Row = $Result->fetch_assoc();
			if ($Row['creator'] != $login->ident) ErrorMessageAndExit("Du er ikke skribent av denne artikkelen!");
			$db->query("DELETE FROM artikler WHERE id='$article_no'");
			$hdr = "Artikkelen er slettet";
			$msg = urlencode("Artikkelen er slettet! Du vil nå bli sendt tilbake til artikkelsiden om få sekunder. Om du ikke ser endringene i artikkelen din, trykk Oppdater i verktøylinjen.");
			$url = urlencode("index.php?s=0040");
			header("Location: index.php?s=0043&hdr=$hdr&msg=$msg&url=$url\n\n"); 
		} else {
			$Result = $db->query("SELECT subject,creator FROM artikler WHERE id='$article_no'");
			if ($Result->num_rows != 1) ErrorMessageAndExit("Artikkelen eksisterer ikke!");
			$Row = $Result->fetch_assoc();
			if ($Row['creator'] != $login->ident) ErrorMessageAndExit("Du er ikke skribent av denne artikkelen!");
			print("<h1>Bekreft</h1><p>Er du sikkert på at du vil slette artikkelen \"".$Row['subject']."\"?</p>");
			print("<form method='post' action='index.php?s=0040&delete=1&amp;noprint=true&amp;i=$article_no&amp;delconf=true'>\n");
			print("<input type='submit' value='     Ja      ' /> <input type='button' value='     Nei      ' onclick=\"window.location='".$_SERVER['HTTP_REFERER']."'\"/>\n");
			print("</form>");
		}
	}


}
*/
?>