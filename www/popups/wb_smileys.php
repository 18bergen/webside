<?php echo "<?xml version=\"1.0\" encoding=\"utf8\"?".">"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Smileys</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
<script language="JavaScript"><!--
function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function MM_setTextOfTextfield(objName,x,newText) { //v3.0
  
}

function x() { return; }

function insertEmoticon(addSmilie) {
    var addSmilie; var revisedMessage;
    var currentMessage = window.opener.document.wordboxform.snikksnakkform_text.value;
    revisedMessage = currentMessage+addSmilie;
    window.opener.document.wordboxform.snikksnakkform_text.value=revisedMessage;
    window.opener.document.wordboxform.snikksnakkform_text.focus();
}
//-->
</script>
</head>
<body style='background: #EDF0ED; font-family: Tahoma; font-size:12px;'>
Klikk for å sette inn:<br>
<br>
<?php
	function makeSmileysTable($insertFunction) {
		$c = "
		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
		  <tr align=\"center\" valign=\"bottom\"> 
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':)'); return false;\"><img src=\"/images/smileys/smiley.gif\" alt=':)' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':('); return false;\"><img src=\"/images/smileys/sad.gif\" alt=':(' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':P'); return false;\"><img src=\"/images/smileys/tongue.gif\" alt=':P' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction('B)'); return false;\"><img src=\"/images/smileys/cool.gif\" alt='B)' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(';)'); return false;\"><img src=\"/images/smileys/wink.gif\" alt=';)' border=\"0\"></a></td>
			<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':D'); return false;\"><img src=\"/images/smileys/biggrin.gif\" alt=':D' border=\"0\"></a></td>
		  	";
		  	
		$smileys = array(
			array('rolleyes','laugh','lol','dozey','glasses','surprised','thinking','uhoh','pleased','huh'),
			
			array('eek','yes','no','upset','confused','sigh','shy','shocked',
				'bandana','grin','nono','scout','klapp','thumbup','engel','speechless'),
				
			array('stunned','sick','smug','party','pirate','sleep','book','bow','builder','chef',
				'dizzy','ears','idea','mad','elf','juggle'),

			array('baby',
			'cyclist','policeman','dead','santa','cowboy','curtain',
			'deal','goofy','sunny','heart','bulb','tup','tdown','balloon','computer')
				
		);
		$first = true;
		foreach ($smileys as $r) {
			if ($first) $first = false;
			else $c .= "		<tr align=\"center\" valign=\"bottom\">\n";
			foreach ($r as $s) {
				$c .= "		<td width=\"6.25%\"><a href=\"#\" onclick=\"$insertFunction(':$s:'); return false;\"><img src=\"/images/smileys/$s.gif\" alt=':$s:' border=\"0\"></a></td>\n";
			}
			$c .= "		</tr>\n";
		}
		$c .= "
		</table>
		";
		return $c;
	}
	
	print makeSmileysTable("insertEmoticon");
?>

</body>
</html>
