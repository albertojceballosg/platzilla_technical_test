/*
 Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.filebrowserBrowseUrl = '/include/kcfinder/browse.php?opener=ckeditor&type=files';
	config.filebrowserImageBrowseUrl = '/include/kcfinder/browse.php?opener=ckeditor&type=images';
	config.filebrowserFlashBrowseUrl = '/include/kcfinder/browse.php?opener=ckeditor&type=flash';
	config.filebrowserUploadUrl = '/include/kcfinder/upload.php?opener=ckeditor&type=files';
	config.filebrowserImageUploadUrl = '/include/kcfinder/upload.php?opener=ckeditor&type=images';
	config.filebrowserFlashUploadUrl = '/include/kcfinder/upload.php?opener=ckeditor&type=flash';
    config.smiley_path= '/include/ckeditor/plugins/smiley/images_smiley/';
    config.smiley_images=[,'Onion--1.gif','Onion--2.gif', 'angel_smile.gif','angry_smile.gif','broken_heart.gif','confused_smile.gif','cry_smile.gif','devil_smile.gif','embaressed_smile.gif','embarrassed_smile.gif','envelope.gif','lightbulb.gif',
 'omg_smile.gif','regular_smile.gif','sad_smile.gif','shades_smile.gif','teeth_smile.gif','thumbs_down.gif',
 'thumbs_up.gif','tongue_smile.gif','tounge_smile.gif','whatchutalkingabout_smile.gif','wink_smile.gif'];
    config.smiley_descriptions=['', ':(', '', '', ':~', ':\'(', '', '', '', '', '', '', ':-O', ':-)', ':-(', '8-)', ':D', '', '', ':-P', ':|', ';-)'];
	CKEDITOR.config.toolbar_Vtiger =
		[
			[ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
			[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
			[ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
			[ 'Link', 'Unlink', 'Anchor' ],
			[ 'Source', '-', 'NewPage', 'Preview', 'Templates' ],
			'/',
			[ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Print', 'SpellChecker' ],
			[ 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat' ],
			[ 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor' ,'Smiley','UniversalKey'],
			'/',
			[ 'Styles', 'Format', 'Font', 'FontSize' ]
		];
	CKEDITOR.config.toolbar = 'Vtiger';
	CKEDITOR.config.height = '320';
};
