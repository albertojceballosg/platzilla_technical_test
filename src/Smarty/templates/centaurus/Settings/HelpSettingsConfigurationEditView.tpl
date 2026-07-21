{strip}
{if (isset ($CONFIGURATION))}
	{assign var='tutorialId' value=$CONFIGURATION.tutorialid}
	{assign var='tutorialBlockName' value=$CONFIGURATION.blockname}
	{assign var='tutorialSectionName' value=$CONFIGURATION.sectionname}
	{assign var='tutorialTabName' value=$CONFIGURATION.tabname}
	{assign var='tutorialTitle' value=$CONFIGURATION.title}
	{assign var='tutorialType' value=$CONFIGURATION.tutorialtype}
	{assign var='tutorialUrl' value=$CONFIGURATION.url}
	{assign var='tutorialUrlIframe' value=$CONFIGURATION.urliframe}
{else}
	{assign var='tutorialId' value=null}
	{assign var='tutorialBlockName' value=null}
	{assign var='tutorialSectionName' value=null}
	{assign var='tutorialTabName' value=null}
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
<form method="post" action="index.php" onsubmit="return HelpSettingsUtils.validateConfiguration (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveHelpConfiguration" />
	<input type="hidden" name="Ajax" value="true" />
{if (isset ($tutorialId))}
	<input type="hidden" name="record" value="{$tutorialId}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=configuration">Ayuda - Configuración</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=configuration" class="btn btn-warning">Cancelar</a>
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
							<label for="block-name">Bloque <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<input type="hidden" name="sectionname" value="{$tutorialSectionName}" />
									<input type="hidden" name="tabname" value="{$tutorialTabName}" />
									<select id="block-name" name="blockname" class="form-control" onchange="HelpSettingsUtils.setSectionAndTabNames (this);">
										<option value="">Selecciona</option>
{if (!empty ($AVAILABLE_SECTION_NAMES)) && (!empty ($AVAILABLE_BLOCK_NAMES))}
	{foreach $AVAILABLE_SECTION_NAMES AS $sectionName => $sectionLabel}
		{assign var='tabNames' value=array_keys($AVAILABLE_BLOCK_NAMES[$sectionName])}
		{foreach $tabNames as $tabName}
										<optgroup label="{$sectionLabel} - {$tabName}">
				{foreach $AVAILABLE_BLOCK_NAMES[$sectionName][$tabName] as $blockName => $blockLabel}
											<option value="{$blockName}"{if ($blockName == $tutorialBlockName)} selected="selected"{/if} data-section-name="{$sectionName}" data-tab-name="{$tabName}">{$blockLabel}</option>
				{/foreach}
										</optgroup>
		{/foreach}
	{/foreach}
{/if}
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="type">Tipo <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<select id="type" name="type" class="form-control">
										<option value="">Selecciona</option>
										<option value="ARTICLE"{if ($tutorialType == 'ARTICLE')} selected="selected"{/if}>Artículo</option>
										<option value="VIDEO"{if ($tutorialType == 'VIDEO')} selected="selected"{/if}>Video</option>
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