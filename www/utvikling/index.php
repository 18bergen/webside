<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> 18. Bergen </title>
<link rel="stylesheet" type="text/css" href="http://www.18bergen.org/stylesheets/basic.css" media="screen,projection,tv" />
</head>
<body>
<div style='margin:60px;max-width:650px;background:white;border:1px solid #ecc;padding:20px;'>
<h2>18. Bergens utviklerside</h2>
<h3>Melde fra om feil? Forslag til endringer?</h3>
<p>
  Du kan melde fra om feil på nettsiden eller komme med 
  forslag til nye funksjoner eller endringer på <em>bugtrackeren</em> vår,
  <a href="/thebuggenie">The Bug Genie</a>. Du kan dessverre
  ikke bruke den vanlige brukerkontoen din fra 18bergen.org her,
  men må registrere deg på nytt.
</p>
<h3>Hva skjuler seg i kulissene?</h3>
<p>
  I kulissene til <a href="http://www.18bergen.org">18bergen.org</a> ligger 
  et egenutviklet og delvis lokalisert content managing system (CMS) skrevet 
  i <abbr title="PHP: Hypertext Preprocessor">PHP</abbr> og basert på 
  MySQL datalagring. Systemet ble påbegynt for en god del år siden, da det
  ikke akkurat florerte av gode, gratis CMS. Mens det i dag finnes flere gode
  CMS, er fordelen med vårt egenutviklede systemet at 
  det er mer tilpasset en speidernettside og ikke like overlesset med 
  abstraksjonslag og funksjonalitet som i mer generelle CMS som skal passe til alt.
  Systemets ulempe er at implementering av ny funksjonalitet tar relativt lang
  tid fordi så mye kode må skrives fra scratch.
  Systemet bygger i dag på objektorientert kode for PHP 5.
</p>
<p>Ellers bruker vi</p>
<ul>
 <li>javascript-bibliotekene <a href="http://developer.yahoo.com/yui/2">YUI 2</a> og
  <a href="http://script.aculo.us/">script.aculo.us</a></li>
 <li><a href="http://ckeditor.com/">CKEditor 3</a> + <a href="http://ckfinder.com">CKFinder 2</a>
  for WYSIWYG-redigering og bildenavigering/-opplasting.</li>
 <li><a href="http://jupload.biz/">JUpload</a> for opplasting av bilder til bildearkivet. 
  Planen er å gå over til en Flash-basert løsning.</li>
 <li>ikonsettet «Silk» fra <a href="http://famfamfam.com/lab/icons/silk/">famfamfam</a> (versjon 1.3).</li>
 <li><a href="http://www.html.it/articoli/niftycube/">NiftyCube</a> for avrundede hjørner (inntil alle
 nettlesere får innebygget støtte for dette)</a>
 <li><a href="http://simplepie.org/">SimplePie</a> for å hente RSS</li>
 <li><a href="https://swiftmailer.symfony.com/">SwiftMailer</a> (tidligere htmlMimeMail5) for å sende 
 epost som både inneholder HTML og ren tekst versjoner.</li>
 <li>små script: <a href="http://couloir.org/js_slideshow/">Slideshow</a> av Scott Upton, 
 <a href="http://www.dhtmlgoodies.com/index.html?whichScript=image-crop">bildebeskjæring</a> 
 av Alf Magne Kalleland (DHTMLGoodies).</li>
</ul>
<h3>Kan jeg se på koden?</h3>
<p>
  Ja, vi benytter versjonskontrollsystemet <a href="http://git-scm.com/">Git</a>, og koden ligger åpent ute på GitHub:
  <a href="https://github.com/18bergen/webside">github.com/18bergen/webside</a>.
  Det ligger imidlertid ikke noen beskrivelse av databasen der. 
  Bare ta kontakt hvis du er interessert i dette for å sette opp en lokal kopi av nettsiden.
<p>
</div>
</body>
</html>
