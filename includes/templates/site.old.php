<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nb" lang="nb">
<head>
  <title>%document_title%</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  %meta%
  
  <!-- CSS: -->
  <link rel="stylesheet" type="text/css" href="/stylesheets/basic.css" media="screen,projection,tv,handheld" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/main.css" media="screen,projection,tv" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/print.css" media="print" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/calendar.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/tabcontent.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/image-crop.css" />
  <link rel="stylesheet" type="text/css" href="/NiftyCube/niftyCorners.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/slideshow.css" media="screen" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/lightbox.css" />
  <!--[if gte ie 5.5000]>
  <link rel="stylesheet" type="text/css" href="/stylesheets/ie.css" />
  <![endif]-->
  
  <!-- RSS -->
  <link rel="alternate" type="application/rss+xml" title="RSS" href="/nyheter/rss/" />


  <!-- JAVASCRIPT: -->
  <script type="text/javascript"> var site_rootdir = ""; </script>
  <script type="text/javascript" src="/jscript/firebug/firebug.js"></script>
  <script type="text/javascript" src="/NiftyCube/niftycube.js"></script>

  <script type="text/javascript" src="/jscript/scriptaculous-js-1.8.2/lib/prototype.js"></script>
  <script type="text/javascript" src="/jscript/scriptaculous-js-1.8.2/src/scriptaculous.js?load=effects,dragdrop,controls"></script>  
  
  <!-- http://yui.yahooapis.com/2.7.0/   or   /yui/  -->  
  <link rel="stylesheet" type="text/css" href="/yui/build/container/assets/skins/sam/container.css">
  <link rel="stylesheet" type="text/css" href="/yui/build/button/assets/skins/sam/button.css">
  <script type="text/javascript" src="/yui/build/yahoo-dom-event/yahoo-dom-event.js"></script>
  <script type="text/javascript" src="/yui/build/animation/animation-min.js"></script>
  <script type="text/javascript" src="/yui/build/connection/connection-min.js"></script>
  <script type="text/javascript" src="/yui/build/dragdrop/dragdrop-min.js"></script>
  <script type="text/javascript" src="/yui/build/element/element-min.js"></script>
  <script type="text/javascript" src="/yui/build/button/button-min.js"></script>
  <script type="text/javascript" src="/yui/build/container/container-min.js"></script>
  
  <script type="text/javascript" src="/jscript/tabcontent.js">
   /***********************************************
    * Tab Content script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
    * This notice MUST stay intact for legal use
    * Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
    ***********************************************/
  </script>

  <script type="text/javascript" src="/jscript/bergenvs.js"></script>
  <script type="text/javascript" src="/fckeditor/fckeditor.js"></script>
  
  %additional_scripts%
  
  <link rel="shortcut icon" href="/favicon.ico" />

</head>
<body class="yui-skin-sam">

<div id='holditall'>

<!-- Ikke slett denne! Brukes av calendarpopup -->
<div id="testdiv1" style="position:absolute;visibility:hidden;background-color:white;background-color:white;"></div>

<div id='container'>

<div id='header'>
	
	<div id='langbar'>
		%language_bar%		
	</div>
	
	<a href="/" style="display:block; position:absolute; top:0px; left:0px; width:500px;height:120px;"></a>

	<h1>18. Bergen speidergruppe</h1>

</div>

<!-- Begin container3 -->
<div id='container3'>


<!-- Begin content -->

<div id='content' style='float:left;'>
	<div id='content_sub'>	
		<script type="text/javascript">
			function skjulInfoMelding() {
				Effect.DropOut('infomessage');
			}
		</script>
		
		%content%
		
		<p id='breadcrumb'></p>
	</div>
	<div style='clear:both; height:1px;'><!-- --></div>
</div>
<!-- End content -->



<!-- Begin right column -->
<div id="hoyrekolonne">
	
	%notifications%

<h1 style='display:none'>Sidefelt:</h1>

	<div style="text-align:center;padding:2px 10px 4px 10px;">
		<div id="landsleir" style="background-color:#fff;">
			<a href="/tropp/terminliste/?show_event=340" 
				title="Landsleir 2009" style="display:block;">
				<img src="/images/utopia.png" alt="Landsleir Utopia 09" style="width:100px;border:none;" />
			</a>
		</div>
	</div>
	<div style="padding:2px 10px 10px 10px;">
		<div id="sponsorstafett" style="background-color:#fff;">
		<a href="'.$site_rootdir.'/sponsorstafett/" style="display:block;letter-spacing:.1em;text-align:center;padding:4px;background-image:url('.$site_rootdir.'/images/icns/award_star_gold_2.png);background-repeat:no-repeat;background-position:10% 55%">Sponsorstafett!</a>
		</div>
	</div>

	<div id='snikksnakk_content'>
		<h2 id='snikksnakk_header' class='small'>Snikk snakk</h2>
		%wordbox%
	</div>
	
	<div id='poll'>
		<h2 class='small'>Ukens spørsmål</h2>
		%poll%
	</div>
	
	<div id="statistikk">
		<h2 class="small">Hvem er her nå?</h2>
		%whoisonline%
	</div>
	
	%rss%
	
	<div id="updates">
		<h2 class='small'>Nytt på 18bergen.org:</h2>
		<div id='siste_oppdateringer_content'>
		%updates%
		</div>
		<div class='smalltext'>
			Flere oppdateringer på <a href='$site_rootdir/aktivitet/'>Aktivitet-siden</a>
		</div>
	</div>

	<div id="bursdager">
		%birthdays%	
	</div>
	

</div> 
<!-- End right column -->



<div style='clear:both;'><!-- --></div>
</div>
<!-- End container3 -->


<!-- Begin left column -->
<div id='menu'>
		
	<div id="loginfelt">
		%field_login%
	</div>
	
	<div id="meny">
		%menu%
	</div>
	<!--
	<ul class='menu'>
		<li class='header'><strong>Søk</strong></li>
	</ul>
	
	<form action="" id="searchbox_002767782398704830568:nuljn-l7ugk" onsubmit="return false;" style="margin-top:10px;padding-left:24px;">
	  <div>
		<input type="text" name="q" size="15"/>
		<input type="submit" value="Søk"/>
	  </div>
	</form>
	<script type="text/javascript" src="http://www.google.com/coop/cse/brand?form=searchbox_002767782398704830568%3Anuljn-l7ugk&lang=no"></script>
	
	<div id="results_002767782398704830568:nuljn-l7ugk" style="display:none">
	  <div class="cse-closeResults">
		<a>&times; Lukk</a>
	  </div>
	  <div class="cse-resultsContainer"></div>
	</div>
	
	<style type="text/css">
	@import url(http://www.google.no/cse/api/overlay.css);
	</style>
	
	<script src="http://www.google.com/uds/api?file=uds.js&v=1.0&key=ABQIAAAAPkLG4zeQWO_17aANVOEZpRQsSWyh14MXlaZ8QiW3M4xpGTwU2xR8LEDmpg_jqY55gjqlB6oUyJeo-A&hl=no" type="text/javascript"></script>
	<script src="http://www.google.no/cse/api/overlay.js" type="text/javascript"></script>
	<script type="text/javascript">
	function OnLoad() {
	  new CSEOverlay("002767782398704830568:nuljn-l7ugk",
					 document.getElementById("searchbox_002767782398704830568:nuljn-l7ugk"),
					 document.getElementById("results_002767782398704830568:nuljn-l7ugk"));
	}
	GSearch.setOnLoadCallback(OnLoad);
	</script>
	-->
	
</div>
<!-- End left column -->

<div style='clear:both;'><!-- --></div>


</div>
<!-- End container -->



</div>

%analytics%

</body>
</html>

<!-- This page was rendered in a mere %render_time% secs -->
