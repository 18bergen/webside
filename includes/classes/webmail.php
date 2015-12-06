<?php
class webmail extends base {

	function run() {
		return file_get_contents("../includes/templates/webmail.html");
	}
	
}

?>