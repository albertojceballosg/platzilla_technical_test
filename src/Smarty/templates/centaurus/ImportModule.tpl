{strip}
<div class="row">
	<div class="col-lg-12">
		<h1>Importar {$MODULE_NAME|@getTranslatedString:$MODULE_NAME}</h1>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2>Plantilla</h2>
			</header>
			<div class="main-box-body clearfix">
				<p>Procedimiento:</p>
				<ol>
					<li>Pincha en el botón Descargar plantilla, y guárdala en tu equipo</li>
					<li>Abre la plantilla en Microsoft &copy; Excel, introduce la información de cada columna, una fila por cada registro que quieres importar a partir de la segunda fila</li>
					<li>Guarda el archivo</li>
					<li>Arrastra el archivo al recuadro de abajo o pincha allí e indica el archivo que quieres importar</li>
					<li>Pincha el botón Importar</li>
				</ol>
				<p><strong>NOTA:</strong> Modificar la estructura de la plantilla hará que el proceso de importación falle. No agregues o elimines columnas, ni alteres de ninguna forma el contenido de la primera fila.</p>
				<a href="index.php?module={$MODULE_NAME}&action=ExportModule&template=true&Ajax=true" class="btn btn-primary">Descargar plantilla</a>
			</div>
		</div>
	</div>
</div>
<form method="post" action="index.php" enctype="multipart/form-data" onsubmit="return AttachmentsUtils.validateForm (this);">
	<input type="hidden" name="module" value="{$MODULE_NAME}" />
	<input type="hidden" name="action" value="ImportModule" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<h2>Archivo</h2>
				</header>
				<div class="main-box-body clearfix attachments-field">
					<div class="col-xs-12 drop-zone" style="background-color: #ffffff; border: 1px dashed #DDDDDD; height: 34px; line-height: 34px; position: relative; text-align: center;">
						<input type="file" onchange="AttachmentsUtils.addAttachment (event || window.event);" style="bottom: 0; cursor: pointer; left: 0; opacity: 0; position: absolute; top: 0; width: 100%;" />
						<span class="title">Arrastra archivos a importar o haz clic aquí (Máximo {$MAX_FILE_SIZE} MB)</span>
					</div>
					<ul class="col-xs-12 attachments-container" style="list-style: none; margin-bottom: 0; margin-top: 3px;" data-field-name="importfile" data-maximum-file-size="{$MAX_FILE_SIZE}" data-allowed-mimetypes="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv"></ul>
					<div class="col-xs-12" style="margin-top: 1em; text-align: right;">
						<button type="submit" class="btn btn-success">Importar</button>
						<a href="index.php?module={$MODULE_NAME}&action=index" class="btn btn-warning" style="margin-left: 5px;">{'LBL_CANCEL_BUTTON_LABEL'|@getTranslatedString:$MODULE_NAME}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/html" id="attachment-template">
	<li class="col-md-3 attachment" style="border: 1px solid #DDDDDD; margin-bottom: 3px; position: relative; width: 100%;">
		<button type="button" class="btn btn-close" onclick="AttachmentsUtils.deleteAttachment (this);" style="background-color: transparent; border: 0; bottom: 0; line-height: 1; right: 0; padding: 0 5px 2px 5px; position: absolute; text-transform: uppercase; z-index: 1000;">X</button>
		<div class="attachment-container">
			<span class="attachment-name"></span><span class="attachment-size"></span>
		</div>
		<input type="hidden" class="attachment-data" />
		<input type="hidden" class="attachment-filename" />
	</li>
</script>
<script type="text/javascript" src="include/js/attachments-utils.js"></script>
<script type="text/javascript">
(function (jQuery) {
	window.AttachmentsUtils.validateForm = function (formElement) {
		var form = jQuery (formElement),
			fields;

		fields = form.find ('.attachments-container > .attachment');
		if (fields.length === 0) {
			alert ('No has seleccionado el archivo a importar');
			return false;
		}

		VtigerJS_DialogBox.block ();
		return true;
	};
} (jQuery));
</script>
{/strip}