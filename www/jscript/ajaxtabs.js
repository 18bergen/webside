
function ajaxpage(url, containerid, targetobj){
	var ullist=targetobj.parentNode.parentNode.getElementsByTagName("li")
	for (var i=0; i<ullist.length; i++)
		ullist[i].className=""  //deselect all tabs

	targetobj.parentNode.className="selected"  //highlight currently clicked on tab
	
	clickedTab(url);
	
/*
	if (url.indexOf("#default")!=-1){ //if simply show default content within container (verus fetch it via ajax)
		document.getElementById(containerid).innerHTML=defaultcontentarray[containerid]
	}
	*/
	return
}

/*
function savedefaultcontent(contentid){// save default ajax tab content
	if (typeof defaultcontentarray[contentid]=="undefined") //if default content hasn't already been saved
		defaultcontentarray[contentid]=document.getElementById(contentid).innerHTML
}
*/

function startajaxtabs(){
	for (var i=0; i<arguments.length; i++){ //loop through passed UL ids
		var ulobj=document.getElementById(arguments[i])
		var ulist=ulobj.getElementsByTagName("li") //array containing the LI elements within UL
		for (var x=0; x<ulist.length; x++){ //loop through each LI element
			var ulistlink=ulist[x].getElementsByTagName("a")[0]
			if (ulistlink.getAttribute("rel")){
				var modifiedurl=ulistlink.getAttribute("href").replace(/^http:\/\/[^\/]+\//i, "http://"+window.location.hostname+"/")
				ulistlink.setAttribute("href", modifiedurl) //replace URL's root domain with dynamic root domain, for ajax security sake
				//savedefaultcontent(ulistlink.getAttribute("rel")) //save default ajax tab content
				ulistlink.onclick=function(){
					ajaxpage(this.getAttribute("rel"), this.getAttribute("rel"), this)
					//loadobjs(this.getAttribute("rev"))
					return false
				}
				if (ulist[x].className=="selected"){
					ajaxpage(ulistlink.getAttribute("href"), ulistlink.getAttribute("rel"), ulistlink) //auto load currenly selected tab content
					//loadobjs(ulistlink.getAttribute("rev")) //auto load any accompanying .js and .css files
				}
			}
		}
	}
}

