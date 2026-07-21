{strip}
{if (isset ($ARTICLE))}
	{assign var='articleId' value=$ARTICLE.id}
	{assign var='articleCategory' value=$ARTICLE.category}
	{assign var='articleTitle' value=$ARTICLE.title}
	{assign var='articleUrl' value=$ARTICLE.url}
	{assign var='articleTags' value=$ARTICLE.tags}
{else}
	{assign var='articleId' value=null}
	{assign var='articleCategory' value=null}
	{assign var='articleTitle' value=null}
	{assign var='articleUrl' value=null}
	{assign var='articleTags' value=null}
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
<form method="post" action="index.php" onsubmit="return HelpSettingsUtils.validateArticle (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveHelpArticle" />
	<input type="hidden" name="Ajax" value="true" />
{if (isset ($articleId))}
	<input type="hidden" name="record" value="{$articleId}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=articles">Ayuda - Caso de uso</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=articles" class="btn btn-warning">Cancelar</a>
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
										<option value="Agencias"{if ($articleCategory == 'Agencias')} selected="selected"{/if}>Agencias</option>
										<option value="Manufactura y producción"{if ($articleCategory == 'Manufactura y producción')} selected="selected"{/if}>Manufactura y producción</option>
										<option value="Servicios a consumidores"{if ($articleCategory == 'Servicios a consumidores')} selected="selected"{/if}>Servicios a consumidores</option>
										<option value="Servicio a empresas"{if ($articleCategory == 'Servicio a empresas')} selected="selected"{/if}>Servicios a empresas</option>
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
									<input type="text" id="title" name="title" value="{$articleTitle}" maxlength="255" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="url">URL <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="url" name="url" value="{$articleUrl}" maxlength="2048" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<label for="tags">Etiquetas de búsqueda separadas por comas <span class="required">*</span></label>
						</div>
						<div class="col-xs-12">
							<div class="form-group field-container">
								<div class="input-group" style="width: 100%;">
									<textarea id="tags" name="tags" class="form-control">{$articleTags}</textarea>
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