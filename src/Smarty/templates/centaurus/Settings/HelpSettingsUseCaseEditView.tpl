{strip}
{if (isset ($USE_CASE))}
	{assign var='useCaseId' value=$USE_CASE.id}
	{assign var='useCaseApplicationCodes' value=$USE_CASE.applicationcodes}
	{assign var='useCaseCategory' value=$USE_CASE.category}
	{assign var='useCaseTitle' value=$USE_CASE.title}
	{assign var='useCaseUrl' value=$USE_CASE.url}
	{assign var='useCaseTags' value=$USE_CASE.tags}
{else}
	{assign var='useCaseId' value=null}
	{assign var='useCaseApplicationCodes' value=null}
	{assign var='useCaseCategory' value=null}
	{assign var='useCaseTitle' value=null}
	{assign var='useCaseUrl' value=null}
	{assign var='useCaseTags' value=null}
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
<form method="post" action="index.php" onsubmit="return HelpSettingsUtils.validateUseCase (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveHelpUseCase" />
	<input type="hidden" name="Ajax" value="true" />
{if (isset ($useCaseId))}
	<input type="hidden" name="record" value="{$useCaseId}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=usecases">Ayuda - Caso de uso</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=usecases" class="btn btn-warning">Cancelar</a>
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
							<label for="category">Categoría <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<select id="category" name="category" class="form-control">
										<option value="">Seleccione</option>
										<option value="Agencias"{if ($useCaseCategory == 'Agencias')} selected="selected"{/if}>Agencias</option>
										<option value="Manufactura y producción"{if ($useCaseCategory == 'Manufactura y producción')} selected="selected"{/if}>Manufactura y producción</option>
										<option value="Servicios a consumidores"{if ($useCaseCategory == 'Servicios a consumidores')} selected="selected"{/if}>Servicios a consumidores</option>
										<option value="Servicio a empresas"{if ($useCaseCategory == 'Servicio a empresas')} selected="selected"{/if}>Servicios a empresas</option>
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
									<input type="text" id="title" name="title" value="{$useCaseTitle}" maxlength="255" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="url">URL <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="url" name="url" value="{$useCaseUrl}" maxlength="2048" class="form-control" />
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
										<option value="{$applicationCode}"{if (is_array ($useCaseApplicationCodes)) && (in_array ($applicationCode, $useCaseApplicationCodes))} selected="selected"{/if}>{$applicationData.app_name}</option>
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
									<textarea id="tags" name="tags" class="form-control">{$useCaseTags}</textarea>
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
{/strip}