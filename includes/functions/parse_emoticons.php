<?php

function parse_emoticons ($text) {
	$imagedir = '/images/smileys';

    $search = array(); $replace = array();
	
	$smileys = array(
		'eek','rolleyes','mad','confused','sigh','yes','no','sleep','upset','shy','none',
		'laugh','lol','dead',
		'bandana','thumbup','tup','tdown','baby','balloon','book',
		'bow','builder','bulb','chef',
		'computer','cool','cowboy','curtain','cyclist','deal',
		'dizzy','dozey','ears','elf','glasses','scout',
		'goofy','grin','huh','idea','juggle','nono','heart',
		'party','pirate','pleased','policeman',
		'santa','shocked','sick','klapp','engel',
		'smiley','smug','speechless','stunned','sunny','surprised',
		'thinking','tongue','uhoh'
	);
   
    foreach ($smileys as $s) {
    	$search[] = ":$s:"; $replace[] = "<img src=\"$imagedir/$s.gif\" alt=\"\" />";
    }
    
    $search[] = ":)"; $replace[] = "<img src=\"$imagedir/smiley.gif\" alt=\"\" />";
    $search[] = ":("; $replace[] = "<img src=\"$imagedir/sad.gif\" alt=\"\" />";
    $search[] = ";)"; $replace[] = "<img src=\"$imagedir/wink.gif\" alt=\"\" />";
    $search[] = ":-)"; $replace[] = "<img src=\"$imagedir/smile.gif\" alt=\"\" />";
    $search[] = ":-("; $replace[] = "<img src=\"$imagedir/sad.gif\" alt=\"\" />";
    $search[] = ";-)"; $replace[] = "<img src=\"$imagedir/smilewinkgrin.gif\" alt=\"\" />";
    $search[] = ":-|"; $replace[] = "<img src=\"$imagedir/none.gif\" alt=\"\" />";
    $search[] = ":0"; $replace[] = "<img src=\"$imagedir/eek.gif\" alt=\"\" />";
    $search[] = "B)"; $replace[] = "<img src=\"$imagedir/cool.gif\" alt=\"\" />";
    $search[] = ":D"; $replace[] = "<img src=\"$imagedir/biggrin.gif\" alt=\"\" />";
    $search[] = ":P"; $replace[] = "<img src=\"$imagedir/tongue.gif\" alt=\"\" />";
    $search[] = "B-)"; $replace[] = "<img src=\"$imagedir/cool.gif\" alt=\"\" />";
    $search[] = ":-D"; $replace[] = "<img src=\"$imagedir/biggrin.gif\" alt=\"\" />";
    $search[] = ":-P"; $replace[] = "<img src=\"$imagedir/bigrazz.gif\" alt=\"\" />";
    $search[] = ":O"; $replace[] = "<img src=\"$imagedir/eek.gif\" alt=\"\" />";
    $search[] = "b)"; $replace[] = "<img src=\"$imagedir/cool.gif\" alt=\"\" />";
    $search[] = ":d"; $replace[] = "<img src=\"$imagedir/biggrin.gif\" alt=\"\" />";
    $search[] = ":p"; $replace[] = "<img src=\"$imagedir/tongue.gif\" alt=\"\" />";
    $search[] = "b-)"; $replace[] = "<img src=\"$imagedir/cool.gif\" alt=\"\" />";
    $search[] = ":-d"; $replace[] = "<img src=\"$imagedir/biggrin.gif\" alt=\"\" />";
    $search[] = ":-p"; $replace[] = "<img src=\"$imagedir/bigrazz.gif\" alt=\"\" />";
    $search[] = ":-b"; $replace[] = "<img src=\"$imagedir/bigrazz.gif\" alt=\"\" />";
    $search[] = ":o"; $replace[] = "<img src=\"$imagedir/eek.gif\" alt=\"\" />";
 
	$text = str_ireplace($search,$replace,$text); // case-insensitive

    return $text;
}
?>
