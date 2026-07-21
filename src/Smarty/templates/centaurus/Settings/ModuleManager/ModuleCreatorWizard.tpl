{strip}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/bootstrap-wizard.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/section/bootstrap-wizard_custom.css" />
<link rel="stylesheet" type="text/css" href="modules/Settings/module-creator-wizard.css" />
<script type="text/html" id="module-creator-wizard-template">
<div id="module-creator-wizard" class="wizard" data-title="Crear módulo">
	<h1>Crear nuevo módulo</h1>
	<div class="wizard-card" data-cardname="basic">
		<input type="hidden" name="module" value="Settings" />
		<input type="hidden" name="action" value="CreateModule" />
		<input type="hidden" name="Ajax" value="true" />
		<h3 class="hide-element">Información básica</h3>
		<h4 class="hidden-md hidden-lg">Información básica</h4>
		<div class="row wizard-input-section">
			<div class="form-group col-xs-12 col-md-4">
				<label for="module-name">Código <span class="required">*</span></label>
				<input type="text" id="module-name" name="moduledata[name]" class="form-control" maxlength="25" onkeyup="ModuleManager.normalizeFieldContents (this);" />
			</div>
			<div class="form-group col-xs-12 col-md-8">
				<label for="module-label">Nombre <span class="required">*</span></label>
				<input type="text" id="module-label" name="moduledata[label]" class="form-control" maxlength="64" />
			</div>
			<div class="form-group col-xs-12 col-md-4">
				<label for="module-type">Tipo <span class="required">*</span></label>
				<select id="module-type" name="moduledata[type]" class="form-control" onchange="ModuleManager.setModuleType (this);">
					<option value="{Module::TYPE_USER}" selected="selected">De campos</option>
					<option value="{Module::TYPE_TOOL}">Simple</option>
				</select>
			</div>
			<div class="form-group col-xs-12 col-md-4">
				<label for="module-location">Ubicación <span class="required">*</span></label>
				<select id="module-location" name="moduledata[location]" class="form-control" onchange="ModuleManager.setModuleLocation (this);">
					<option value="menu" selected="selected">Menú</option>
					<option value="settings">Configuración</option>
				</select>
			</div>
			<div id="menu-label-container" class="form-group col-xs-12 col-md-4">
				<label for="menu-label">Menú <span class="required">*</span></label>
				<select id="menu-label" name="moduledata[menu]" class="form-control">
					<option value=""></option>
{if (!empty ($AVAILABLE_MENUS))}
	{foreach $AVAILABLE_MENUS as $menu}
					<option value="{$menu}">{$menu}</option>
	{/foreach}
{/if}
				</select>
			</div>
		</div>
	</div>
	<div id="blocks" class="wizard-card" data-cardname="blocks">
		<h3 class="hide-element">Bloques</h3>
		<h4 class="hidden-md hidden-lg">Bloques</h4>
		<div class="row wizard-input-section">
			<div class="table-responsive">
				<table class="table">
					<thead>
					<tr>
						<th class="block-name-cell">Nombre</th>
						<th class="block-visibility-cell">Visibilidad</th>
						<th class="actions-cell"></th>
					</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
					<tr>
						<td colspan="3" class="text-center">
							<button type="button" class="btn btn-primary" onclick="ModuleManager.addBlock (this);"><i class="fa fa-plus"></i></button>
						</td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	<div id="fields" class="wizard-card" data-cardname="fields">
		<h3 class="hide-element">Campos</h3>
		<h4 class="hidden-md hidden-lg">Campos</h4>
		<div class="row wizard-input-section"></div>
	</div>
	<div id="view-columns" class="wizard-card" data-cardname="view-columns">
		<h3 class="hide-element">Vista por defecto</h3>
		<h4 class="hidden-md hidden-lg">Vista por defecto</h4>
		<div class="row wizard-input-section">
			<div class="table-responsive">
				<table class="table">
					<thead>
					<tr>
						<th class="view-column-name-cell">Columnas</th>
						<th class="actions-cell"></th>
					</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
					<tr>
						<td colspan="2" class="text-center">
							<button type="button" class="btn btn-primary" onclick="ModuleManager.addViewColumn (this);"><i class="fa fa-plus"></i></button>
						</td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	<div id="advanced" class="wizard-card" data-cardname="advanced">
		<h3 class="hide-element">Propiedades avanzadas</h3>
		<h4 class="hidden-md hidden-lg">Propiedades avanzadas</h4>
		<div id="entity-identifier" class="row wizard-input-section">
			<div class="form-group col-xs-12">
				<label for="entity-identifier-name">Campo identificador del módulo <span class="required">*</span></label>
				<select id="entity-identifier-name" name="moduledata[entityidentifier]" class="form-control"></select>
			</div>
		</div>
		<h5>Listas relacionadas</h5>
		<div id="related-lists" class="row wizard-input-section">
			<div class="table-responsive">
				<table class="table">
					<thead>
					<tr>
						<th class="related-list-label-cell">Etiqueta</th>
						<th class="related-list-module-name-cell">Módulo</th>
						<th class="related-list-actions-cell">Acciones</th>
						<th class="actions-cell"></th>
					</tr>
					</thead>
					<tbody></tbody>
					<tfoot>
					<tr>
						<td colspan="4" class="text-center">
							<button type="button" class="btn btn-primary" onclick="ModuleManager.addRelatedList (this);"><i class="fa fa-plus"></i></button>
						</td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	<div id="ready" class="wizard-card" data-cardname="ready">
		<h3 class="hide-element">Crear</h3>
		<h4 class="hidden-md hidden-lg">Crear</h4>
		<div class="row wizard-input-section">
			<p>Estamos listos para crear el módulo con toda la información que suministraste.</p>
			<p>Puedes volver y revisar la información antes de continuar. Cuando estés seguro, presiona el botón Crear.</p>
			<p>Nos tomará unos segundos. Por favor no cierres esta ventana mientras culmina el proceso.</p>
		</div>
	</div>
	<div class="wizard-failure text-center">
		<h4><strong style="color: #880000;">Error!</strong>: Se ha presentado un error al generar el módulo</h4>
		<p class="message"></p>
		<button type="button" class="btn btn-primary" onclick="ModuleManager.restartModuleCreatorModal ();">Volver al inicio</button>
	</div>
	<div class="wizard-loading text-center">
		<h4><strong>Por favor espera</strong></h4>
		<p>Estamos creando tu módulo. Por favor espera unos instantes y por favor no cierres esta ventana</p>
		<img src="themes/images/loading.gif" class="img-responsive" style="display: inline-block;" />
	</div>
	<div class="wizard-success text-center">
		<h4><strong style="color: #008800;">Listo!</strong>: Se ha creado el módulo <strong><span class="module-label"></span></strong></h4>
		<a href="javascript:;" class="btn btn-primary module-link">Ir al módulo</a>
		<button type="button" class="btn btn-default" style="margin-left: 5px;" onclick="ModuleManager.closeModuleCreatorModal ();">Terminar</button>
	</div>
</div>
</script>
<script type="text/html" id="module-creator-wizard-block-template">
<tr id="block-__BLOCK_ID__" class="block" data-id="__BLOCK_ID__">
	<td>
		<input type="text" name="moduledata[blocks][__BLOCK_ID__][label]" class="form-control block-label" placeholder="Nombre" />
	</td>
	<td>
		<select name="moduledata[blocks][__BLOCK_ID__][visibility]" class="form-control block-visibility" title="Visibilidad">
			<option value="{Block::VISIBILITY_VISIBLE}" selected="selected">Visible</option>
			<option value="{Block::VISIBILITY_HIDDEN}">Oculto</option>
		</select>
	</td>
	<td>
		<button type="button" class="btn btn-danger" onclick="ModuleManager.deleteBlock (this);"><i class="fa fa-trash-o"></i></button>
	</td>
</tr>
</script>
<script type="text/html" id="module-creator-wizard-block-fields-template">
<div id="block-fields-__BLOCK_ID__" class="table-responsive block-fields" data-id="__BLOCK_ID__">
	<h4>Campos del bloque __BLOCK_LABEL__</h4>
	<table class="table">
		<thead>
		<tr>
			<th class="field-name-cell">Nombre</th>
			<th class="field-label-cell">Etiqueta</th>
			<th class="field-type-cell">Tipo</th>
			<th class="field-options-cell">Opciones</th>
			<th class="actions-cell"></th>
		</tr>
		</thead>
		<tbody>
{include file='Settings/ModuleManager/ModuleCreatorWizardField.tpl'}
		</tbody>
		<tfoot>
		<tr>
			<td colspan="5" class="text-center">
				<button type="button" class="btn btn-primary" onclick="ModuleManager.addField (this);"><i class="fa fa-plus"></i></button>
			</td>
		</tr>
		</tfoot>
	</table>
</div>
</script>
<script type="text/html" id="module-creator-wizard-field-template">
{include file='Settings/ModuleManager/ModuleCreatorWizardField.tpl'}
</script>
<script type="text/html" id="module-creator-wizard-view-column-template">
<tr class="view-column">
	<td>
		<select name="moduledata[viewcolumns][]" class="form-control view-column-name" title="Columna">__COLUMNS__</select>
	</td>
	<td>
		<button type="button" class="btn btn-danger" onclick="ModuleManager.deleteViewColumn (this);"><i class="fa fa-trash-o"></i></button>
	</td>
</tr>
</script>
<script type="text/html" id="module-creator-wizard-related-list-template">
<tr id="related-list-__RELATED_LIST_ID__" class="related-list">
	<td>
		<input type="text" name="moduledata[relatedlists][__RELATED_LIST_ID__][label]" class="form-control related-list-label" placeholder="Etiqueta" />
	</td>
	<td>
		<select name="moduledata[relatedlists][__RELATED_LIST_ID__][modulename]" class="form-control related-list-module-name" title="Módulo">
			<option value="">Selecciona el módulo</option>
{if (!empty ($AVAILABLE_ENTITY_TYPE_MODULES))}
	{foreach $AVAILABLE_ENTITY_TYPE_MODULES as $module}
			<option value="{$module->getName ()}">{$module->getLabel ()}</option>
	{/foreach}
{/if}
		</select>
	</td>
	<td>
		<div class="checkbox">
			<label><input type="checkbox" name="moduledata[relatedlists][__RELATED_LIST_ID__][actions][]" value="{ModuleRelationship::ACTION_SELECT}" class="form-control related-list-action">Seleccionar</label>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" name="moduledata[relatedlists][__RELATED_LIST_ID__][actions][]" value="{ModuleRelationship::ACTION_ADD}" class="form-control related-list-action">Insertar</label>
		</div>
	</td>
	<td>
		<button type="button" class="btn btn-danger" onclick="ModuleManager.deleteRelatedList (this);"><i class="fa fa-trash-o"></i></button>
	</td>
</tr>
</script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-wizard.js"></script>
{/strip}