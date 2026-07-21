{strip}
{if (isset ($TIP))}
	{assign var='tipId' value=$TIP.id}
	{assign var='tipTitle' value=$TIP.title}
	{assign var='tipDescription' value=$TIP.description}
	{assign var='tipTags' value=$TIP.tags}
{else}
	{assign var='tipId' value=null}
	{assign var='tipTitle' value=null}
	{assign var='tipDescription' value=null}
	{assign var='tipTags' value=null}
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
<form method="post" action="index.php" onsubmit="return HelpSettingsUtils.validateTip (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveHelpTip" />
	<input type="hidden" name="Ajax" value="true" />
{if (isset ($tipId))}
	<input type="hidden" name="record" value="{$tipId}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=tips">Ayuda - Tip</a></h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=tips" class="btn btn-warning">Cancelar</a>
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
							<label for="title">Título <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="title" name="title" value="{$tipTitle}" maxlength="255" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="description">Descripción <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<textarea id="description" name="description" class="form-control">{$tipDescription}</textarea>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="tags">Etiquetas de búsqueda separadas por comas <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<textarea id="tags" name="tags" class="form-control">{$tipTags}</textarea>
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