<?php
class commonfunctions {

	function printConfirmationForm($text, $page, $extraInformation = ""){
		if (strlen($page) <> 4) ErrorMessageAndReturn("sideID innehar feil format!");
		print("<form method='post' action='index.php?s=$page&amp;noprint=true'>\n");
		print("<input type='hidden' name='confirmed' value='1' />");
		print("<input type='hidden' name='confirmedData' value='$extraInformation' />");
		print("<h1>Bekreft</h1>\n");
		print("<p>$text</p>\n");
		print("<input type='submit' value='    Ja    ' /> <input type='button' value='    Nei    ' onclick='window.location=\"".$_SERVER['HTTP_REFERER']."\"' />\n");
		print("</form>\n");
	}

	function br2nl($data) {
		return preg_replace( '!<br.*>!iU', "\n", $data );
	}

	function toSiffer($t){
		if ($t < 10) $t = "0".$t;
		return $t;
	}

}

?>