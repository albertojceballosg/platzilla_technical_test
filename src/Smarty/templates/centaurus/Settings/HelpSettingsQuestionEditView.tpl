{strip}
{if (isset ($QUESTION))}
	{assign var='questionId' value=$QUESTION.id}
	{assign var='questionApplicationCode' value=$QUESTION.applicationcode}
	{assign var='questionDescription' value=$QUESTION.description}
	{assign var='questionModuleName' value=$QUESTION.modulename}
	{assign var='questionFieldName' value=$QUESTION.fieldname}
	{assign var='questionTitle' value=$QUESTION.title}
	{assign var='questionTags' value=$QUESTION.tags}
{else}
	{assign var='questionId' value=null}
	{assign var='questionApplicationCode' value=null}
	{assign var='questionDescription' value=null}
	{assign var='questionModuleName' value=null}
	{assign var='questionFieldName' value=null}
	{assign var='questionTitle' value=null}
	{assign var='questionTags' value=null}
{/if}
<style type="text/css">
	.required {
		color: #FF0000;
	}
	.action-bar .btn {
		margin-left: 5px;
	}
	label {
		font-size: 1em;
	}
</style>
<form method="post" action="index.php" onsubmit="return HelpSettingsUtils.validateQuestion (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveHelpQuestion" />
	<input type="hidden" name="Ajax" value="true" />
{if (isset ($questionId))}
	<input type="hidden" name="record" value="{$questionId}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=questions">Ayuda - Pregunta frecuente</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=questions" class="btn btn-warning">Cancelar</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Información general</h2>
				</header>
				<div class="main-box-body">
					<div class="row">
						<div class="col-xs-12">
							<label for="application">{$MOD.LBL_APP} <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<select id="application" name="applicationcode" class="form-control" onchange="HelpSettingsUtils.setModuleOptions (this);">
										<option value="">Selecciona</option>
{foreach $APPLICATIONS as $application}
										<option value="{$application.app_code}"{if ($application.app_code == $questionApplicationCode)} selected="selected"{/if}>{$application.app_name}</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="title">Pregunta <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="title" name="title" value="{$questionTitle}" maxlength="255" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="description">Respuesta <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<textarea id="description" name="description" class="form-control">{$questionDescription}</textarea>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<label for="modulename">{$MOD.LBL_RELATED_MODULE}</label>
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<select name="modulename" class="form-control no-resize module-name" onchange="HelpSettingsUtils.setFieldOptions (this);" title="">
										<option value="">Selecciona</option>
{if (isset ($questionApplicationCode)) && (isset ($APPLICATIONS[$questionApplicationCode]))}
	{foreach $APPLICATIONS[$questionApplicationCode].modules as $moduleName => $moduleData}
										<option value="{$moduleName}"{if ($questionModuleName == $moduleName)} selected="selected"{/if}>{$moduleData.tablabel} ({$moduleName})</option>
	{/foreach}
{/if}
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<label for="fieldname">{$MOD.LBL_RELATED_FIELD}</label>
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<select name="fieldname" class="form-control no-resize field-name" title="">
										<option value="">Selecciona</option>
{if (isset ($questionApplicationCode)) && (isset ($APPLICATIONS[$questionApplicationCode].modules)) && (isset ($APPLICATIONS[$questionApplicationCode].modules[$questionModuleName]))}
	{foreach $APPLICATIONS[$questionApplicationCode].modules[$questionModuleName].fields as $fieldName => $fieldData}
		<option value="{$fieldName}"{if ($questionFieldName == $fieldName)} selected="selected"{/if}>{$fieldData.fieldlabel}</option>
	{/foreach}
{/if}
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="tags">Etiquetas de búsqueda separadas por comas <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<textarea id="tags" name="tags" class="form-control">{$questionTags}</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="modules/Settings/help-settings.js"></script>
<script type="text/javascript">
	HelpSettingsUtils.init ({$APPLICATIONS|@json_encode});
</script>
{/strip}