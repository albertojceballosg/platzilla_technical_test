{strip}
{if (isset ($VIDEO))}
	{assign var='videoId' value=$VIDEO.id}
	{assign var='videoTitle' value=$VIDEO.title}
	{assign var='videoUrl' value=$VIDEO.url}
	{assign var='videoUrlIframe' value=$VIDEO.urliframe}
	{assign var='videoTags' value=$VIDEO.tags}
{else}
	{assign var='videoId' value=null}
	{assign var='videoTitle' value=null}
	{assign var='videoUrl' value=null}
	{assign var='videoUrlIframe' value=null}
	{assign var='videoTags' value=null}
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
<form method="post" action="index.php" onsubmit="return HelpSettingsUtils.validateVideo (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveHelpVideo" />
	<input type="hidden" name="Ajax" value="true" />
{if (isset ($videoId))}
	<input type="hidden" name="record" value="{$videoId}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=videos">Ayuda - Tutorial</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=videos" class="btn btn-warning">Cancelar</a>
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
	<div class="row video">
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
									<input type="text" id="title" name="title" value="{$videoTitle}" maxlength="255" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="url">URL <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="url" name="url" value="{$videoUrl}" maxlength="2048" class="form-control" onchange="HelpSettingsUtils.setVideoPreview (this);" />
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="tags">Etiquetas de búsqueda separadas por comas <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<textarea id="tags" name="tags" class="form-control">{$videoTags}</textarea>
								</div>
							</div>
						</div>
					</div>
					<div id="preview" class="row{if (!isset ($videoUrlIframe))} hidden{/if}">
						<div class="col-xs-12">
							<label for="preview">Vista preliminar</label>
						</div>
						<div class="col-xs-12">
							<iframe id="ytplayer" src="{if (isset ($videoUrlIframe))}{$videoUrlIframe}{else}about:blank{/if}" frameborder="0" width="640" height="360"></iframe>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="modules/Settings/help-settings.js"></script>
{/strip}