I kulissene til 18bergen.org ligger et egenutviklet og delvis lokalisert 
content managing system (CMS) skrevet i PHP og basert på MySQL datalagring. 
Systemet ble påbegynt for en god del år siden, da det ikke akkurat florerte 
av gode, gratis CMS. Mens det i dag finnes flere gode CMS, er fordelen med 
vårt egenutviklede systemet at det er mer tilpasset en speidernettside og 
ikke like overlesset med abstraksjonslag og funksjonalitet som i mer 
generelle CMS som skal passe til alt. Systemets ulempe er at implementering 
av ny funksjonalitet tar relativt lang tid fordi så mye kode må skrives 
fra scratch. Systemet bygger i dag på objektorientert kode for PHP 5. 

Ellers bruker vi

 - javascript-bibliotekene [YUI 2](http://developer.yahoo.com/yui/2) og 
  [script.aculo.us](http://script.aculo.us/).
 - [CKEditor 3](http://ckeditor.com/) + [CKFinder 2](http://ckfinder.com)
  for WYSIWYG-redigering og bildenavigering/-opplasting.
 - [JUpload](http://jupload.biz) for opplasting av bilder til bildearkivet.
   Planen er å gå over til en løsning basert på Flash eller HTML 5 etterhvert.
 - ikonsettet «Silk» fra [famfamfam](http://famfamfam.com/lab/icons/silk/) (versjon 1.3).
 - [NiftyCube](http://www.html.it/articoli/niftycube/) for avrundede hjørner (inntil alle
    nettlesere får innebygget støtte for dette)
 - [SimplePie](http://simplepie.org) for å hente RSS.
 - [SquirrelMail](http://squirrelmail.org) for å hente RSS.
 - [RMail](http://www.phpguru.org/static/Rmail) (tidligere htmlMimeMail5) for å sende
   epost som både inneholder HTML og ren tekst versjoner.
 - forskjellige små script: 
   [Slideshow](http://couloir.org/js_slideshow) av Scott Upton,
   [bildebeskjæring](http://www.dhtmlgoodies.com/index.html?whichScript=image-crop) av
   Alf Magne Kalleland (DHTMLGoodies).
