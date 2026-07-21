CKEDITOR.plugins.add (
	'emailtemplatevariables',
	{
		init: function (editor) {
			editor.addCommand (
				'insertVariable',
				{
					exec: function (editor) {
						editor.insertHtml ('&nbsp;<var>NOMBRE_DE_VARIABLE</var>&nbsp;');
					}
				}
			);
			editor.ui.addButton (
				'EmailTemplateVariables',
				{
					label:   'Insertar variable',
					command: 'insertVariable',
					icon: this.path + 'images/anchor.gif'
				});
		}
	}
);