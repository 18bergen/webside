
function AjaxSaveField(field, target, url){
	var pars = field+"="+escape($F(field));
	var myAjax = new Ajax.Updater(target, url, {method: "post", parameters: pars, asynchronous:true} );
  	// places result text in the browser object with the id 'mydiv': <div id='mydiv'></div>
}

function AjaxRequestData(target, url){
	var myAjax = new Ajax.Updater(target, url, {asynchronous:true} );
  	// places result text in the browser object with the id 'mydiv': <div id='mydiv'></div>
}

function AjaxFormSubmit(target_div, url, form) {
	/* Ajax.Updater can be used to submit a form and insert the results into the page, all without a refresh. 
	   This works for all form elements except files. */
	var myAjax = new Ajax.Updater(target_div, url, {asynchronous:true, parameters:Form.serialize(form)});
}



function validateField(field, target, url){
	var pars = field+"="+escape($F(field));
	var success = function(t){ feedbackReceived(t, target); }
	var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});
}
function trim(str) {
   return str.replace(/^\s*|\s*$/g,"");
}
function stripHTML(str){
	var re= /<\S[^><]*>/g
	return str.replace(re, "")
}
function feedbackReceived(t, obj){
	// getObject and setText are functions in objectbasics.js

	var oldText = trim(stripHTML(getObject(obj).innerHTML.toLowerCase()));
	var newText = trim(stripHTML(t.responseText.toLowerCase()));
	
	if (newText == "ok" && oldText != "" && oldText != "ok") {
		new Effect.SlideUp(obj, {
			duration:.3, 
			queue: "end",
			afterFinish: function (obj2) {
				var s = getStyleObject(obj);
				s.display = "none";
				setText(obj,""); 
            }
        });	

	} else if (oldText == "" || oldText == "ok") {
		
		setText(obj,t.responseText); 
		//getStyleObject(obj).display = "block";
		new Effect.SlideDown(obj,{duration:.3});
		//Effect.toggle(obj, 'slide');

	} else if (oldText != newText) {
		new Effect.SlideUp(obj, {
			duration:.3, 
			queue: "end",
			afterFinish: function (obj2) {
            	setText(obj,t.responseText); 
            	new Effect.SlideDown(obj, {
					duration:.3,
					queue: "end"
				});
            }
        });	
	}
}
function perm_select(self, extras) {
	var i = self.options[self.selectedIndex].value;
	if (i == 'loggedin') {
		changeObjectVisibility(extras, 'visible');
	} else {
		changeObjectVisibility(extras, 'hidden');
	}
}
function showOptions(obj) {
	getStyleObject(obj).display = "block";
}
function hideOptions(obj) {
	getStyleObject(obj).display = "none";
}

