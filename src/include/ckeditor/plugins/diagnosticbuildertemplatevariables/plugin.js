CKEDITOR.plugins.add (
	'diagnosticbuildertemplatevariables',
	{
		init: function (editor) {
			editor.addCommand (
				'insertVariable',
				{
					exec: function (editor) {
                        DiagnosticRerportBuilderUtls.getBuilderTemplateVariables(editor)
					}
				}
			);
			editor.ui.addButton (
				'Buildertemplatevariables',
				{
					label:   'Insertar variable',
					command: 'insertVariable',
					icon: this.path + 'images/anchor.gif'
				});
		}
	}
);