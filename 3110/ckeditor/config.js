/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

/* https://ckeditor.com/latest/samples/toolbarconfigurator/index.html#basic */

/*
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
};
*/

/*
CKEDITOR.editorConfig = function( config ) {
	config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		'/',
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	];
};
*/

CKEDITOR.editorConfig = function( config ) {

	// Toolbar configuration generated automatically by the editor based on config.toolbarGroups.
	config.toolbar = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source' ] },
		{ name: 'clipboard', groups: [ 'undo', 'clipboard' ], items: [ 'Undo', 'Redo' ] },
		{ name: 'basicstyles', groups: [ 'basicstyles' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting' ] },
		{ name: 'paragraph', groups: [ 'align', 'bidi' ], items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'insert', items: [ 'Link', 'Image', 'Table' ] },
		{ name: 'tools', items: [ 'Maximize', 'RemoveFormat' ] }
	];

	/*
	config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'undo', 'clipboard' ] },

		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'others', groups: [ 'others' ] }
		
	];
	*/

	config.removeButtons = 'About,Flash,Anchor,SpecialChar,PageBreak,Smiley,Language,BidiRtl,BidiLtr,CreateDiv,Blockquote,CopyFormatting,Form,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Scayt,SelectAll,Find,Replace,Templates,Print,Preview,NewPage,Save,Styles,Format,Font,Checkbox';

	config.removeDialogTabs = 'image:advanced;link:advanced';

	//config.disallowedContent = '*{*}'; // All styles disallowed
	//config.disallowedContent = 'table[style]{*}; tr[style]{*}; td[style]{*};';
	//config.disallowedContent = '*[style]{*};';

	//config.disallowedContent = 'script; *[on*]';
	//config.disallowedContent = 'table[style]{*}; tr[style]{*}; td[style]{*}; span[style]{*};'

	config.disallowedContent = 'table[style]{*}; tr[style]{*}; td[style]{width*, vertical-align*, background*, color*, padding*, margin*, font*, align*}; span[style]{*}; p[style]{margin*, padding*};'

	//disallowedContent : 'table {width*}; tr {width*}; td {width*};'

	//border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; vertical-align:top; width:142px"

};