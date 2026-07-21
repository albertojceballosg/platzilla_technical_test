{strip}
<style type="text/css">
{literal}
	label {
		font-size: 1.11em;
		font-weight: 300;
	}
	.btn {
		margin-left: 5px;
	}
	.main-box > .main-box-header {
		padding-bottom: 20px;
		padding-top: 20px;
	}
	.cke_button_insertVariable .cke_label {
		display: inline !important;
	}
	.col-actions {
		text-align: center;
		width:      80px;
	}
	.drop-zone {
		background-color: #ffffff;
		border:           1px dashed;
		height:           5em;
		position:         relative;
		text-align:       center;
	}
	.drop-zone > input[type='file'] {
		bottom:   0;
		cursor:   pointer;
		left:     0;
		opacity:  0;
		position: absolute;
		top:      0;
		width:    100%;
	}
	.drop-zone > .title {
		line-height: 4.75em;
	}
	.attachments-container {
		list-style: none;
		margin-top: 10px;
	}
	.attachments-container > .attachment {
		border:         1px solid #DDDDDD;
		margin-bottom:  5px;
		position:       relative;
	}
	.attachments-container > .attachment > .btn-close {
		background-color: transparent;
		border:           0;
		bottom:           0;
		line-height:      1;
		right:            0;
		padding:          0 5px 2px 5px;
		position:         absolute;
		text-transform:   uppercase;
		z-index:          1;
	}
{/literal}
</style>
<div class="row">
	<div class="col-xs-12">
		<h1><a href="index.php?module=emailmanager&action=TemplateListView&parenttab=Settings">Plantilla de correo</a></h1>
	</div>
</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
<div class="row">
	<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
		<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
</div>
{/if}
<form method="post" action="index.php" name="EditView" onsubmit="return EmailManagerUtils.validateTemplate (this);">
	<input type="hidden" name="module" value="emailmanager" />
	<input type="hidden" name="action" value="SaveTemplate" />
{if (isset ($RECORD))}
	<input type="hidden" name="record" value="{$RECORD}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Información general</h2>
					<div class="action-bar pull-right">
						<button type="submit" class="btn btn-info">Guardar</button>
						<a href="index.php?module=emailmanager&action=TemplateListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
					</div>
				</header>
				<div class="main-box-body">
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="templatename">Nombre <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="templatename" name="templatename" value="{if (isset ($TEMPLATE))}{$TEMPLATE.templatename}{/if}" maxlength="255" class="form-control templatename"{if ($LOCK)} readonly="readonly"{/if} />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="language">Idioma <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="language" name="language" class="form-control language">
{foreach $AVAILABLE_LANGUAGES as $language}
										<option value="{$language}"{if (isset ($TEMPLATE)) && ($TEMPLATE.language == $language)} selected="selected"{/if}>{$MOD[$language]}</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group col-md-8 field-container checkbox-nice col-md-offset-4">
								<input type="checkbox" id="adddefaultheader" name="adddefaultheader" value="Y"{if (!empty ($TEMPLATE.adddefaultheader))} checked="checked"{/if} />
								<label for="adddefaultheader">Agregar encabezado por defecto</label>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group col-md-8 field-container checkbox-nice col-md-offset-4">
								<input type="checkbox" id="adddefaultfooter" name="adddefaultfooter" value="Y"{if (!empty ($TEMPLATE.adddefaultfooter))} checked="checked"{/if} />
								<label for="adddefaultfooter">Agregar pie de página por defecto</label>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-12 field-container">
							<label for="subject">Asunto <span class="required">*</span></label>
							<div class="input-group" style="width: 100%;">
								<textarea id="subject" name="subject" class="form-control subject">{if (isset ($TEMPLATE))}{$TEMPLATE.subject}{/if}</textarea>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-12 field-container">
							<label for="body">Contenido <span class="required">*</span></label>
							<div class="input-group" style="width: 100%;">
								<textarea id="body" name="body" class="form-control body">{if (isset ($TEMPLATE))}{$TEMPLATE.body}{/if}</textarea>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<label>Anexos</label>
						</div>
						<div class="col-md-12 drop-zone">
							<input type="file" multiple="multiple" onchange="EmailManagerUtils.addTemplateAttachments (event || window.event);" />
							<span class="title">Arrastra ficheros aquí o haz clic</span>
						</div>
						<ul class="col-md-12 attachments-container">
{if (isset ($TEMPLATE.attachments))}
	{foreach $TEMPLATE.attachments as $attachment}
							<li class="col-md-12 attachment">
								<button type="button" class="btn btn-close" onclick="EmailManagerUtils.deleteTemplateAttachment (this);">X</button>
								<a href="index.php?module=emailmanager&action=DownloadTemplateAttachment&Ajax=true&record={$TEMPLATE.templateid}&filename={$attachment|urlencode}" title="Descargar {$attachment}">{$attachment}</a>
								<input type="hidden" name="attachments[old][filename][]" class="field-attachment-filename" value="{$attachment}" />
							</li>
	{/foreach}
{/if}
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/html" id="attachment-template">
	<li class="col-md-12 attachment">
		<button type="button" class="btn btn-close" onclick="EmailManagerUtils.deleteTemplateAttachment (this);">X</button>
		<a href="#" class="name"></a>
		<input type="hidden" name="attachments[new][data][]" class="attachment-data" />
		<input type="hidden" name="attachments[new][filename][]" class="attachment-filename" />
	</li>
</script>
<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="modules/emailmanager/emailmanager.js"></script>
<script type="text/javascript" defer="defer">
{literal}
	EmailManagerUtils.loadTemplateSubjectEditor ('subject');
	EmailManagerUtils.loadTemplateBodyEditor ('body');
{/literal}
</script>
{/strip}