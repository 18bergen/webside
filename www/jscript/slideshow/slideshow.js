// -----------------------------------------------------------------------------------
// 
// This page coded by Scott Upton
// http://www.uptonic.com | http://www.couloir.org
//
// This work is licensed under a Creative Commons License
// Attribution-ShareAlike 2.0
// http://creativecommons.org/licenses/by-sa/2.0/
//
// Associated APIs copyright their respective owners
//
// -----------------------------------------------------------------------------------
// --- version date: 11/28/05 --------------------------------------------------------




// Number of photos in this gallery
var photoNum = photoArray.length;

/*--------------------------------------------------------------------------*/

// Additional methods for Element added by SU, Couloir
Object.extend(Element, {
	getWidth: function(element) {
   	element = $(element);
   	return element.offsetWidth; 
	},
	setWidth: function(element,w) {
   	element = $(element);
    	element.style.width = w +"px";
	},
	setHeight: function(element,h) {
   	element = $(element);
    	element.style.height = h +"px";
	},
	setSrc: function(element,src) {
    	element = $(element);
    	element.src = src; 
	},
	setHref: function(element,href) {
    	element = $(element);
    	element.href = href; 
	},
	setInnerHTML: function(element,content) {
		element = $(element);
		element.innerHTML = content;
	}
});

/*--------------------------------------------------------------------------*/

var Slideshow = Class.create();

Slideshow.prototype = {
	initialize: function(photoId) {
		this.photoId = photoId;
		this.photo = 'Photo';
		this.photoBox = 'ImageContainer';
		this.prevLink = 'PrevLink';
		this.nextLink = 'NextLink';
		this.captionBox = 'CaptionContainer';
		this.caption = 'Caption';
		this.counter = 'Counter';
		this.loader = 'Loading';
	},
	getCurrentSize: function() {
		// Get current height and width, subtracting CSS border size
		this.wCur = Element.getWidth(this.photoBox) ;
		this.hCur = Element.getHeight(this.photoBox) ;
	},
	getNewSize: function() {
		// Get current height and width
		this.wNew = photoArray[photoId][1];
		this.hNew = photoArray[photoId][2];
	},
	getScaleFactor: function() {
		this.getCurrentSize();
		this.getNewSize();
		//alert(this.wCur + " -> " + this.wNew);
		// Scalars based on change from old to new
		this.xScale = (this.wNew / this.wCur) * 100;
		this.yScale = (this.hNew / this.hCur) * 100;
	},
	setNewPhotoParams: function() {
		// Set source of new image
		Element.setSrc(this.photo,photoDir + photoArray[photoId][0]);
		// Set anchor for bookmarking
		Element.setHref(this.prevLink, "#" + (photoId+1));
		Element.setHref(this.nextLink, "#" + (photoId+1));
	},
	setPhotoCaption: function() {
		// Add caption from gallery array
		Element.setInnerHTML(this.caption,photoArray[photoId][3]);
		Element.setInnerHTML(this.counter,((photoId+1)+'/'+photoNum));
	},
	resizePhotoBox: function() {
		this.getScaleFactor();
		if ((this.wCur-borderSize) != this.wNew)
			new Effect.Scale(this.photoBox, this.xScale, {scaleY: false, delay: 0.5, duration: 0.3});
		if ((this.hCur-borderSize) != this.hNew)
			new Effect.Scale(this.photoBox, this.yScale, {scaleX: false, duration: 0.3, queue: 'front'});
		// Dynamically resize caption box as well
		Element.setWidth(this.captionBox,this.wNew-(-borderSize));
	},
	showPhoto: function(){
		new Effect.Fade(this.loader, {delay: 0.5, duration: 0.3});
		// Workaround for problems calling object method "afterFinish"
		new Effect.Appear(this.photo, {duration: 0.5, queue: 'end', afterFinish: function(){ 
			//Element.show('CaptionContainer');
			Element.show('PrevLink');
			Element.show('NextLink');
			new Effect.Appear('CaptionContainer', {duration: 0.3});
		}});
	},
	nextPhoto: function(){
		// Figure out which photo is next
		(photoId == (photoArray.length - 1)) ? photoId = 0 : photoId++;
		this.initSwap();
	},
	prevPhoto: function(){
		// Figure out which photo is previous
		(photoId == 0) ? photoId = photoArray.length - 1 : photoId--;
		this.initSwap();
	},
	initSwap: function() {
		// Begin by hiding main elements
		Element.show(this.loader);
		Element.hide(this.photo);
		Element.hide(this.captionBox);
		Element.hide(this.prevLink);
		Element.hide(this.nextLink);
		// Set new dimensions and source, then resize
		this.setNewPhotoParams();
		this.resizePhotoBox();
		this.setPhotoCaption();
	}
}

/*--------------------------------------------------------------------------*/

// Establish CSS-driven events via Behaviour script
var myrules = {
	'#Photo' : function(element){
		element.onload = function(){
			var myPhoto = new Slideshow(photoId);
			myPhoto.showPhoto();
		}
	},
	'#PrevLink' : function(element){
		element.onmouseover = function(){
			//soundManager.play('beep');
		}
		element.onclick = function(){
			var myPhoto = new Slideshow(photoId);
			myPhoto.prevPhoto();
			soundManager.play('beep');
		}
	},
	'#NextLink' : function(element){
		element.onmouseover = function(){
			//soundManager.play('beep');
		}
		element.onclick = function(){
			var myPhoto = new Slideshow(photoId);
			myPhoto.nextPhoto();
			soundManager.play('beep');
		}
	},
	a : function(element){
		element.onfocus = function(){
			this.blur();
		}
	}
};

// Add window.onload event to initialize
Behaviour.addLoadEvent(init);
Behaviour.apply();
function init() {
	var myPhoto = new Slideshow(photoId);
	myPhoto.initSwap();
	soundManagerInit();
}