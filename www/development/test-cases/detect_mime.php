<?php
$finfo = new finfo(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
if ($finfo === false) {
	print "failed";
} else {
	print $finfo->file('detect_mime.php');
}
?>