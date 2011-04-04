/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	//config.uiColor = '#A2C980';
	
	config.docType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' ;

    // config.scayt_autoStartup = false;

	config.LinkBrowser = false;
	
	config.toolbarCanCollapse = false;
	
	config.emailProtection = 'encode' ;
	config.contentsCss = '/stylesheets/basic.css' ;
	
	config.format_tags = 'p;h3' ;

	config.smiley_path   = '/images/ckeditor_smileys/' ;
	config.smiley_images = ['regular_smile.gif','sad_smile.gif','wink_smile.gif','teeth_smile.gif','confused_smile.gif','tounge_smile.gif','embaressed_smile.gif','omg_smile.gif','whatchutalkingabout_smile.gif','angry_smile.gif','angel_smile.gif','shades_smile.gif','devil_smile.gif','cry_smile.gif','lightbulb.gif','thumbs_down.gif','thumbs_up.gif','heart.gif','broken_heart.gif','kiss.gif','envelope.gif','ver_overskyet.gif','ver_regn.gif','ver_sol.gif','ver_tordenver.gif','ver_vekslende_opphold.gif','ver_vekslende_regnbyger.gif','date.gif','camera3.gif','goldstar1.gif','silverstar.gif','scoutlogo2.gif','telt.gif','notice.gif','check-2.gif'] ;
	
	config.toolbar_Full = [
		['Source','-','Save','NewPage','Preview','-','Templates'],
		['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
		['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
		'/',
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink','Anchor'],
		['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
		'/',
		['Styles','Format','Font','FontSize'],
		['TextColor','BGColor'],
		['Maximize', 'ShowBlocks','-','About']
	];
	
	config.toolbar_BergenVS = [
		['Source'],
		['Undo','Redo'],
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList'],
		['Link','Unlink'],['About'], '/',
		['Image','Flash','Table','Smiley','SpecialChar'],
		['JustifyLeft','JustifyCenter','JustifyRight'],
		['Format','FontSize']
	];
	
	config.toolbar_SimpleBergenVS = [
		['Source','-','PasteText','PasteFromWord','Bold','Italic','JustifyLeft','JustifyCenter','JustifyRight','Link'],'/',
		['BulletedList','Table','Smiley','SpecialChar'], 
		['Image','Flash'], 
		['Format','FontSize'],['About']
	];
	
	config.toolbar_VerySimpleBergenVS = [
		['Source','-','Bold','Italic','JustifyLeft','JustifyCenter','JustifyRight','Link','Image','Table']
	];
	
};
