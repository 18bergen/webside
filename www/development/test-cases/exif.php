<?php
date_default_timezone_set('Europe/Oslo');

$f = '../userfiles/images/Bildearkiv/tropp/2009/epler-og-sitroner/i03581.jpg';

$exif = exif_read_data($f, 'IFD0');
if ($exif===false) {
	print "No header data found.<br />\n";
} else {
	$exif = exif_read_data($f,0,true);
	if (isset($exif['EXIF']['DateTimeOriginal'])) {
		$dateStr = strftime('%F %T',strtotime($exif['EXIF']['DateTimeOriginal']));
		print "Found EXIF.DateTimeOriginal: ".$dateStr."<br />\n";
	}
	if (isset($exif['EXIF']['DateTimeDigitized'])) {
		$dateStr = strftime('%F %T',strtotime($exif['EXIF']['DateTimeDigitized']));
		print "Found EXIF.DateTimeDigitized: ".$dateStr."<br />\n";
	}
	if (isset($exif['IFD0']['DateTime'])) {
		print "Found IFD0.DateTime: ".$exif['IFD0']['DateTime']."<br />\n";
	}
	if (isset($exif['FILE']['FileDateTime'])) {
		$dateStr = strftime('%F %T',$exif['FILE']['FileDateTime']);
		print "Found FILE.FileDateTime: ".$dateStr."<br />\n";
	}
	$dateStr = strftime('%F %T',filemtime($f));
	print "filemtime: ".$dateStr."<br />\n";
	

	print "<br />\n";
	foreach ($exif as $key => $section) {
		foreach ($section as $name => $val) {
			echo "$key.$name: $val<br />\n";
		}
	}
}

?>