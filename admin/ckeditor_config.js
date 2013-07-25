/**
 * This file has been modified from the one included in the standard CKEditor download.
 * It uses lists of items instead of toolbar groups, to cut out the unnecessary ones, and arranges
 * them differently than the default editor does.
 * 
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbar/*Groups*/ = [
		{ name: 'tools', items : [ 'Maximize', 'ShowBlocks','-' ] },// About
		{ name: 'document', items : [ 'Source'/*,'-','Save','NewPage','DocProps','Preview','Print','-','Templates'*/ ] },
		{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
		{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 
			'HiddenField' ] },
		//'/',
		//{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
		//'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
		'/',
		{ name: 'styles', items : [ 'Format' ] },
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','Outdent','Indent','Blockquote' ] },
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript' ] },// RemoveFormat
		{ name: 'remove', items : [ 'RemoveFormat' ] },
		//{ name: 'colors', items : [ 'TextColor','BGColor' ] },
		{ name: 'insert', items : [ /*'Image','Flash','Table',*/'HorizontalRule','Smiley','SpecialChar'/*,'PageBreak','Iframe'*/ ] },
		{ name: 'links', items : [ 'Link','Unlink' ] },// Anchor
		{ name: 'image', items : [ 'Image' ] }
	];

	// Remove some buttons, provided by the standard plugins, which we don't
	// need to have in the Standard(s) toolbar.
	//config.removeButtons = 'Underline,Subscript,Superscript';
	
	// specify upload URL
	config.filebrowserUploadUrl = '../../admin/ckupload.php';

	// Se the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Make dialogs simpler.
	config.removeDialogTabs = 'image:advanced;link:advanced';
};
