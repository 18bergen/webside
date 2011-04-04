  <script type="text/javascript">
//onerror = handleErrors

var msg = null
function handleErrors(errorMessage, url, line)
	{
	<? 
		$webmaster = $memberdb->userByTask('webmaster');
	if ($webmaster->ident == $login->ident){	
	?>
	msg = "There was an error on this page.\n\n";
	msg += "An internal programming error may keep\n";
	msg += "this page from displaying properly.\n";
	msg += "Click OK to continue.\n\n";
	msg += "Error message: " + errorMessage + "\n";
	msg += "URL: " + url + "\n";
	msg += "Line #: " + line;
	alert(msg);
	<?
	}
	?>
	return true
	}
	</script>