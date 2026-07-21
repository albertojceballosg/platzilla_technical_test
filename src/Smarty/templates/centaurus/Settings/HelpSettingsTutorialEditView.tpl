{strip}
{if (isset ($TUTORIAL))}
	{assign var='tutorialId' value=$TUTORIAL.id}
	{assign var='tutorialApplicationCodes' value=$TUTORIAL.applicationcodes}
	{assign var='tutorialCategory' value=$TUTORIAL.category}
	{assign var='tutorialTags' value=$TUTORIAL.tags}
	{assign var='tutorialTitle' value=$TUTORIAL.title}
	{assign var='tutorialType' value=$TUTORIAL.tutorialtype}
	{assign var='tutorialUrl' value=$TUTORIAL.url}
	{assign var='tutorialUrlIframe' value=$TUTORIAL.urliframe}
{else}
	{assign var='tutorialId' value=null}
	{assign var='tutorialApplicationCodes' value=null}
	{assign var='tutorialCategory' value=null}
	{assign var='tutorialTags' value=null}
	{assign var='tutorialTitle' value=null}
	{assign var='tutorialType' value=null}
	{assign var='tutorialUrl' value=null}
	{assign var='tutorialUrlIframe' value=null}
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
<form method="post" action="index.php" onsubmit="return HelpSettingsUtils.validateTutorial (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveHelpTutorial" />
	<input type="hidden" name="Ajax" value="true" />
{if (isset ($tutorialId))}
	<input type="hidden" name="record" value="{$tutorialId}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=tutorials">Ayuda - Tutorial</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=tutorials" class="btn btn-warning">Cancelar</a>
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
							<label for="type">Tipo <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<select id="type" name="type" class="form-control">
										<option value="">Seleccione</option>
										<option value="ARTICLE"{if ($tutorialType == 'ARTICLE')} selected="selected"{/if}>Artículo</option>
										<option value="VIDEO"{if ($tutorialType == 'VIDEO')} selected="selected"{/if}>Video</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="category">Categoría <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<select id="category" name="category" class="form-control">
										<option value="">Seleccione</option>
										<option value="Agencias"{if ($tutorialCategory == 'Agencias')} selected="selected"{/if}>Agencias</option>
										<option value="Manufactura y producción"{if ($tutorialCategory == 'Manufactura y producción')} selected="selected"{/if}>Manufactura y producción</option>
										<option value="Servicios a consumidores"{if ($tutorialCategory == 'Servicios a consumidores')} selected="selected"{/if}>Servicios a consumidores</option>
										<option value="Servicio a empresas"{if ($tutorialCategory == 'Servicio a empresas')} selected="selected"{/if}>Servicios a empresas</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="title">Título <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="title" name="title" value="{$tutorialTitle}" maxlength="255" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="url">URL <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="url" name="url" value="{$tutorialUrl}" maxlength="2048" class="form-control" onchange="HelpSettingsUtils.setTutorialPreview (this);" />
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="application-codes">Aplicaciones <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<select id="application-codes" name="applicationcodes[]" class="form-control" multiple="multiple">
{if (!empty ($AVAILABLE_APPLICATIONS))}
	{foreach $AVAILABLE_APPLICATIONS as $applicationCode => $applicationData}
										<option value="{$applicationCode}"{if (is_array ($tutorialApplicationCodes)) && (in_array ($applicationCode, $tutorialApplicationCodes))} selected="selected"{/if}>{$applicationData.app_name}</option>
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
									<textarea id="tags" name="tags" class="form-control">{$tutorialTags}</textarea>
								</div>
							</div>
						</div>
					</div>
					<div id="preview" class="row{if (!isset ($tutorialUrlIframe))} hidden{/if}">
						<div class="col-xs-12">
							<label for="preview">Vista preliminar</label>
						</div>
						<div class="col-xs-12">
							<iframe id="ytplayer" src="{if (isset ($tutorialUrlIframe))}{$tutorialUrlIframe}{else}about:blank{/if}" frameborder="0" width="640" height="360"></iframe>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="modules/Settings/help-settings.js"></script>
{/strip}