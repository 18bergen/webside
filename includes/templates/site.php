<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">	
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nb" lang="nb">
<head>
  <title>%document_title%</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  %meta%
  
  <!-- CSS: -->
  <link rel="stylesheet" type="text/css" href="/stylesheets/basic.css" media="screen,print,projection,tv,handheld" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/main.css" media="screen,projection,tv" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/print.css" media="print" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/calendar.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/tabcontent.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/image-crop.css" />
  <link rel="stylesheet" type="text/css" href="/libs/NiftyCube/niftyCorners.css" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/slideshow.css" media="screen" />
  <link rel="stylesheet" type="text/css" href="/stylesheets/lightbox.css" />
  <!--[if gte ie 5.5000]>
    <link rel="stylesheet" type="text/css" href="/stylesheets/ie.css" />
  <![endif]-->
    
    <!--[if lt IE 7]>
    <![endif]-->

  
  <!-- RSS -->
  %our_rss_feed%


  <!-- JAVASCRIPT: -->
  <script type="text/javascript"> var site_rootdir = ""; </script>
  <script type="text/javascript" src="/jscript/firebug/firebug.js"></script>
  <script type="text/javascript" src="/libs/NiftyCube/niftycube.js"></script>

  <script type="text/javascript" src="/libs/scriptaculous-js-1.8.3/lib/prototype.js"></script>
  <script type="text/javascript" src="/libs/scriptaculous-js-1.8.3/src/scriptaculous.js?load=effects,dragdrop,controls"></script>  
  
  <!-- /yui/   or   /yui/  -->  
  <!-- 
  	Unfortunately, using YUILoader 2.8 for loading everything does not work very well.
  	http://yuilibrary.com/forum/viewtopic.php?p=3199
  -->
  <script type="text/javascript" src="%yui_uri%build/yuiloader-dom-event/yuiloader-dom-event.js"></script>
  <script type="text/javascript" src="%yui_uri%build/container/container-min.js"></script>
  <link rel="stylesheet" type="text/css" href="%yui_uri%build/assets/skins/sam/skin.css" />
  
  <!--
  <script type="text/javascript" src="/yui/build/animation/animation-min.js"></script>
  <script type="text/javascript" src="/yui/build/connection/connection-min.js"></script>
  <script type="text/javascript" src="/yui/build/dragdrop/dragdrop-min.js"></script>
  <script type="text/javascript" src="/yui/build/element/element-min.js"></script>
  <script type="text/javascript" src="/yui/build/container/container-min.js"></script>
  <script type="text/javascript" src="/yui/build/button/button-min.js"></script>
  <script type="text/javascript" src="/yui/build/calendar/calendar-min.js"></script>
  <script type="text/javascript" src="/yui/build/json/json-min.js"></script>
  <script type="text/javascript" src="/yui/build/datasource/datasource-min.js"></script>
  <script type="text/javascript" src="/yui/build/autocomplete/autocomplete-min.js"></script>
  
  --> 

  <script type="text/javascript" src="/jscript/bergenvs.js"></script>
  <script type="text/javascript" src="/jscript/org.18bergen/base.js"></script>
    
  <!-- WE SHOULD ACTUALLY LOAD THESE ONLY IF LOGGED-IN -->
	  <script type="text/javascript" src="%ckeditor_uri%ckeditor.js"></script>
	  <script type="text/javascript" src="%ckfinder_uri%ckfinder.js"></script>
	  <script type="text/javascript" src="/jscript/tabcontent.js">
	   /***********************************************
		* Tab Content script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
		* This notice MUST stay intact for legal use
		* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
		***********************************************/
	  </script>

  
  %additional_scripts%
  
  <link rel="shortcut icon" href="/favicon.ico" />

</head>
<body class="yui-skin-sam">

 <script type="text/javascript">
    var loader = new YAHOO.util.YUILoader({
		base: "%yui_uri%build/",
		loadOptional: true,
		onSuccess: function(o) { onYuiLoaderComplete(); },
		onFailure: function(o) {
			console.warn("YUI LOADER FAILED: "+o.msg);
		}
	});
 </script>

 <!-- Ikke slett denne! Brukes av calendarpopup -->
 <div id="testdiv1" style="position:absolute;visibility:hidden;background-color:white;background-color:white;"></div>
 
 %webmaster_tools%
 
 <div id='header'>
	<div style="position:absolute;left:31px;top:8px;"><a href="/"><img src="/images/logo.png" alt="18. Bergens banner" border="0" /></a></div>
	<map name="map1" id="map1">
	   <area shape="rect" href="/" coords="50,20,200,117" alt="Gå til forsiden" title="Gå til forsiden" />
	   <area shape="rect" href="http://www.hordalandsspeiderne.no/" coords="715,20,810,100" alt="Hordaland Krins" title="Besøk Hordaland Krins" />
	   <area shape="rect" href="http://www.speiding.no/" coords="835,20,930,100" alt="Norges Speiderforbund" title="Besøk Norges Speiderforbund" />
	</map>
	<map name="map2" id="map2">
	   <area shape="rect" href="/" coords="80,0,180,54" alt="Gå til forsiden" title="Gå til forsiden" />
	</map>
			
	<img src="/_design/header/header8_01.png" width="940" height="117" border="0" alt="" usemap="#map1" style="float:left;" />
	<img src="/_design/header/header8_02.png" width="292" height="54" border="0" alt="" style="float:left;" usemap="#map2" />
	
	<div style="position:absolute;left:700px;top:118px;z-index:1;">
	  <a href="/tropp/konkurranser/patruljekonkurranse-2011"><img src="/_design/banners/patruljekonk2011.png" alt="Patruljekonk!" style="border:none;" /></a>
	</div>
	<div style="position:absolute;left:605px;top:58px;z-index:2;">
      <a href="/nyheter/387"><img src="/_design/banners/tastetrykk2010.png" alt="" style="border:none;" /></a>
    </div>
	<div style="background:url(/_design/header/header8_03.png);float:left;width:648px;height:53px;padding-top:1px;">
		<div id="infofelt">
			<p align="center">
				<!--
				<a class="jubileum" href="/tropp/terminliste/2009/80-ars-jubileum" title="Les mer om vårt 80-års jubileum den 5. september" style="display:block;">
					<img src="/_design/banners/jubileumsbanner.png" alt="80-års jubileum" style="border:none;" />
				</a>
				-->
	
				<!--
				<a href="/tropp/terminliste/2009/landsleir" title="Landsleir 2009" style="display:block;">
					<img src="/_design/banners/utopia.png" alt="Landsleir Utopia 09" style="padding-top:3px;width:100px;border:none;" />
				</a>
				-->
				<!--
				<a href="/tropp/konkurranser/patruljekonkurranse-2010" title="Følg med på patruljekonk 2010!" style="display:block;">
					<img src="/_design/banners/patruljekonk2010.png" alt="Patruljekonk!" style="border:none;" />
				</a>
				-->
			</p>		
		</div>
		
		<div id="loginfelt">
			%field_login%
			%notifications%
		</div>
	</div>
	<div style="background:url(/_design/header/header8_04.png);float:left;width:940px;height:19px;">
		<div id='language_bar'>
			%language_bar%		
		</div>
		<div id='breadcrumb'>
			%breadcrumb%
		</div>
	</div>
	
 </div>
	
 %infomsg%
  
  <!--
  /* LAYOUT DEBUG: 
  <div style="width:1000px;background:#fff;margin:auto;text-align:center;">
  <div style="width:940px;background:#ff4;margin:auto;text-align:center;">  
  Page width: 940px
  <table cellpadding="0" cellspacing="0">
  <tr><td style="width:160px;background:#77f;">Left col: 160px</td>
  <td style="width:540px;background:#ccf;">Main col: 540px</td>
  <td style="width:240px;background:#77f;">Right col: 240px</td>
  </td></tr></table>
  </div>
  </div>
  -->
  
 
  <!--[if lt IE 7]>
  <div style='border: 1px solid #F7941D; background: #FEEFDA; text-align: center; clear: both; height: 75px; position: relative;width:700px;margin:20px auto 5px auto;'>
    <div style='position: absolute; right: 3px; top: 3px; font-family: courier new; font-weight: bold;'><a href='#' onclick='javascript:this.parentNode.parentNode.style.display="none"; return false;'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-cornerx.jpg' style='border: none;' alt='Close this notice'/></a></div>
    <div style='width: 640px; margin: 0 auto; text-align: left; padding: 0; overflow: hidden; color: black;'>
      <div style='width: 75px; float: left;'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-warning.jpg' alt='Warning!'/></div>
      <div style='width: 275px; float: left; font-family: Arial, sans-serif;'>
        <div style='font-size: 14px; font-weight: bold; margin-top: 12px;'>Du benytter en foreldet nettleser</div>
        <div style='font-size: 12px; margin-top: 6px; line-height: 12px;'>For en bedre opplevelse av denne nettsiden, oppgrader til en moderne nettleser.</div>
      </div>
      <div style='width: 75px; float: left;'><a href='http://www.firefox.com' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-firefox.jpg' style='border: none;' alt='Get Firefox 3.5'/></a></div>
      <div style='width: 75px; float: left;'><a href='http://www.browserforthebetter.com/download.html' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-ie8.jpg' style='border: none;' alt='Get Internet Explorer 8'/></a></div>
      <div style='width: 73px; float: left;'><a href='http://www.apple.com/safari/download/' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-safari.jpg' style='border: none;' alt='Get Safari 4'/></a></div>
      <div style='float: left;'><a href='http://www.google.com/chrome' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-chrome.jpg' style='border: none;' alt='Get Google Chrome'/></a></div>
    </div>
  </div>
  <![endif]-->


 <div id="container1">
	<div id="container2">
		%main_content%
	</div>
  
  	<div id="left_col" class="column">
  		
  		<div class="col_above"></div>
  		<div class="inner_col">
			<a href="/sponsorstafett" style="display:block;font-size:10px;letter-spacing:.1em;text-align:center;padding:4px;background-image:url(/images/icns/award_star_gold_2.png);background-repeat:no-repeat;background-position:5px 55%">Sponsorstafett!</a>
			%menu%
		</div>
		<div class="col_below"></div>
		
		<p align="center" style="padding-top:6px;">
		  <a href="http://www.sortere.no/"><img src="/images/sortere.png" alt="Sortere.no" border="0" /></a>
		</p>
		
	  </div>

 </div>

 <div id="footer">
	© 18. Bergen speidergruppe. 
	<span class="hidefromprint"><a href="/kontakt">Kontakt oss</a>.</span>
	<span class="showonprintonly">Skrevet ut %timestamp%.</span>
 </div>

 %analytics%

</body>
</html>

<!-- This page was rendered in a mere %render_time% secs -->
