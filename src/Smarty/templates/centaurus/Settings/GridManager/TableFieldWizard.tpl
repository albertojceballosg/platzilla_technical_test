{strip}
{math equation= rand() assign= "idFieldTable"}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/bootstrap-wizard.css" />
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/section/bootstrap-wizard_custom.css" />
<link rel="stylesheet" type="text/css" href="modules/notifications/notifications.css" />

<script type="text/html" id="{$TF_TEMPLATE}">
<div id="table-field-wizard-{$idFieldTable}" class="wizard" data-title="Crear campo tipo tabla">
	<h1>{$MOD['notifications']}</h1>
    {* definir tabla y ubicacion*}
	<div id="start-section-{$idFieldTable}"   class="wizard-card" data-cardname="start">
		<input type="hidden" name="module" value="Settings" />
		<input type="hidden" name="action" value="CreateFieldTable" />
		<input type="hidden" name="datasource" value="wizard" />
		<input type="hidden" name="Ajax" value="true" />
		<input type="hidden" id="idFieldTable" value="{$idFieldTable}" />
		<input type="hidden" id="modulename-{$idFieldTable}" name="modulename" value="" />
		<h3 class="hide-element">{$MOD['NAV_START']}</h3>
		<h4 class="hidden-md hidden-lg">{$MOD['NAV_START']}</h4>
		<div class="row wizard-input-section data-section">
			<div class="form-group" style="margin-bottom: 5px;" id="dv-tabla-name-{$idFieldTable}>
				<label for="block-label">Nombre de la tabla</label>
				<input type="text" id="tabla-name-{$idFieldTable}" name="table_name" class="form-control"  title="Nombre de la tabla"/>
				<span id="sp-tabla-name-{$idFieldTable}" class="help-block" style="color: red"></span>
			</div>
			<div class="form-group" style="margin-bottom: 5px;" id="dv-block-label-{$idFieldTable}">
				<label for="block-label">Nombre del bloque donde se ubicará la tabla</label>
				<input type="text" id="block-label-{$idFieldTable}" name="block_name" class="form-control" title="Nombre del bloque"  />
				<span id="sp-block-label-{$idFieldTable}" class="help-block" style="color: red"></span>
			</div>
			<div class="form-group" style="margin-bottom: 5px;" id="dv-block-sequence-{$idFieldTable}">
				<label for="block-sequence">Posición del bloque</label>
				<select id="block-sequence-{$idFieldTable}" name="sequence" class="form-control">
                    {foreach $blocks as $block}
						{if $MOD[$block->getLabel ()] neq NULL}
							<option value="{$block->getSequence ()}">Antes de {$MOD[$block->getLabel ()]}</option>
						{else}
							<option value="{$block->getSequence ()}">Antes de {$block->getLabel ()}</option>
						{/if}
                    {/foreach}
					<option value="-1">(Último)</option>
				</select>
				<span id="sp-block-sequence-{$idFieldTable}" class="help-block" style="color: red"></span>
			</div>
		</div>
	</div>
{* columnas de la tabla *}
	<div id="step-1-section-{$idFieldTable}" class="wizard-card" data-cardname="step-1">
		<h3 class="hide-element">{$MOD['NAV_STEP1']}</h3>
		<h4 class="hidden-md hidden-lg">{$MOD['NAV_STEP1']}</h4>
		<div class="wizard-input-section data-section">
			<div id="block-fields-0" class="table-responsive block-fields" data-id="0">
				<h4 id="block-fields-{$idFieldTable}"></h4>
				<table id="step-1-table-{$idFieldTable}" class="table">
					<thead>
					<tr>
						<th class="field-name-cell">Nombre</th>
						<th class="field-label-cell">Etiqueta</th>
						<th class="field-type-cell">Tipo</th>
						<th class="field-options-cell">Opciones</th>
						<th class="actions-cell"></th>
					</tr>
					</thead>
					<tbody id="group-table-fields-{$idFieldTable}">
					<tr id="field-0-0" class="field" data-id="0">
						<td>
							<input type="text" name="moduledata[blocks][0][fields][0][name]" class="form-control field-name" maxlength="30" placeholder="Nombre" onkeyup="TableFieldUtils.normalizeFieldContents (this);" />
						</td>
						<td>
							<input type="text" name="moduledata[blocks][0][fields][0][label]" class="form-control field-label" value="{$FIELD_LABEL}" maxlength="255" placeholder="Etiqueta" />
						</td>
						<td>
							<select name="moduledata[blocks][0][fields][0][type]" class="form-control field-type" title="Tipo" onchange="TableFieldUtils.setFieldType (this);">
                                {if (!empty ($AVAILABLE_FIELD_TYPES))}
									<optgroup label="Texto">
                                        {foreach $AVAILABLE_FIELD_TYPES.text as $type}
											<option value="{$type.value}">{$type.text}</option>
                                        {/foreach}
									</optgroup>
									<optgroup label="Numéricos">
                                        {foreach $AVAILABLE_FIELD_TYPES.number as $type}
											<option value="{$type.value}">{$type.text}</option>
                                        {/foreach}
									</optgroup>
									<optgroup label="Fecha">
                                        {foreach $AVAILABLE_FIELD_TYPES.date as $type}
											<option value="{$type.value}">{$type.text}</option>
                                        {/foreach}
									</optgroup>
									<optgroup label="Selección">
                                        {foreach $AVAILABLE_FIELD_TYPES.selection as $type}
                                            {if in_array($type.value, array(33))}
                                                {continue}
                                            {/if}
											<option value="{$type.value}">{$type.text}</option>
                                        {/foreach}
									</optgroup>
									{*
									<optgroup label="Medios">
                                        {foreach $AVAILABLE_FIELD_TYPES.media as $type}
											<option value="{$type.value}">{$type.text}</option>
                                        {/foreach}
									</optgroup>
									*}
									<optgroup label="Avanzados">
                                        {foreach $AVAILABLE_FIELD_TYPES.advanced as $type}
											{if in_array($type.value, array(8192, 2204,2206))}
												{continue}
											{/if}
											<option value="{$type.value}">{$type.text}</option>
                                        {/foreach}
									</optgroup>
                                {/if}
							</select>
						</td>
						<td class="field-properties">
							<input type="text" name="moduledata[blocks][0][fields][0][length]" class="form-control field-length" maxlength="5" placeholder="Longitud" />
							<input type="text" name="moduledata[blocks][0][fields][0][precision]" class="form-control field-precision" maxlength="5" placeholder="Precisión" style="display: none;" />
							<textarea name="moduledata[blocks][0][fields][0][picklistvalues]" class="form-control field-picklist-values" rows="3" placeholder="Valores" style="display: none; resize: "></textarea>
							<select name="moduledata[blocks][0][fields][0][referencedmodulename]" class="form-control field-referenced-module-name" title="Módulo" style="display: none;">
								<option value="">Selecciona el módulo</option>
                                {if (!empty ($AVAILABLE_ENTITY_TYPE_MODULES))}
                                    {foreach $AVAILABLE_ENTITY_TYPE_MODULES as $module}
										<option value="{$module->getName ()}">{$module->getLabel ()}</option>
                                    {/foreach}
                                {/if}
							</select>
							<select name="moduledata[blocks][0][fields][0][globalpicklist]" class="form-control field-global-picklist" title="Campo especial" style="display: none;" onchange="TableFieldUtils.setGlobalPicklistFieldName (this);">
								<option value="">Selecciona el campo</option>
                                {if (!empty ($AVAILABLE_GLOBAL_PICKLISTS))}
                                    {foreach $AVAILABLE_GLOBAL_PICKLISTS as $picklist}
										<option value="{$picklist->getName ()}"{if ($GLOBAL_PICKLIST == $picklist->getName ())} selected="selected"{/if}>{$picklist->getLabel ()}</option>
                                    {/foreach}
                                {/if}
							</select>
						</td>
						<td class="text-center">
							<button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteField (this);"><i class="fa fa-trash-o"></i></button>
						</td>
					</tr>
					</tbody>
					<tfoot>
					<tr>
						<td colspan="5" class="text-center">
							</i></span><button type="button" rel="0" class="btn btn-primary" onclick="TableFieldUtils.addField (this);"><i class="fa fa-plus"></i></button>
						</td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
{* Acciones de campos *}
	<div id="step-2-section-{$idFieldTable}" class="wizard-card" data-cardname="step-2">
		<h3 class="hide-element">{$MOD['NAV_STEP2']}</h3>
		<h4 class="hidden-md hidden-lg">{$MOD['NAV_STEP2']}</h4>
		<div class="wizard-input-section data-section" style="height: 100%">
			{* Nav tabs *}
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active">
					<a href="#linkages-import-{$idFieldTable}" aria-controls="linkages-import-{$idFieldTable}" role="tab" data-toggle="tab">Importar Valores</a>
				</li>
				<li role="presentation">
					<a href="#linkages-list-{$idFieldTable}" aria-controls="linkages-list-{$idFieldTable}" role="tab" data-toggle="tab">Vincular listas</a>
				<li role="presentation">
				<li role="presentation">
					<a href="#activations-{$idFieldTable}" aria-controls="activations-{$idFieldTable}" role="tab" data-toggle="tab">Activaciones</a></li>
				<li role="presentation">
					<a href="#operations-math-row-{$idFieldTable}" aria-controls="operations-math-row-{$idFieldTable}" role="tab" data-toggle="tab">Operaciones</a></li>
				<li role="presentation">
					<a href="#resume-row-{$idFieldTable}" aria-controls="resume-row-{$idFieldTable}" role="tab" data-toggle="tab">Fila resumen</a></li>
			</ul>
			{* Tab panels *}
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active" id="linkages-import-{$idFieldTable}">
					<div class="panel-group" id="group-linkages-import-{$idFieldTable}">
						<div class="alert alert-info" style="margin-top: 1.5em">No se han encontrado campos de referencia a módulo</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="linkages-list-{$idFieldTable}">
					<div class="alert alert-info" style="margin-top: 1.5em">No se han encontrado campos listas</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="activations-{$idFieldTable}">
					{* Activacion... *}
					<div class="panel-group" id="group-checkbox-activation-{$idFieldTable}">
						<div class="alert alert-info" style="margin-top: 1.5em">No se han encontrado campos tipo check box</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="operations-math-row-{$idFieldTable}">
					<div class="panel-group" id="group-operations-math-{$idFieldTable}">
						<div class="alert alert-info" style="margin-top: 1.5em">No se han encontrado campos tipo número</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="resume-row-{$idFieldTable}">
                    {include file='Settings/GridManager/TableFieldActions/MainSummaryRow.tpl'}
				</div>
			</div>
		</div>
	</div>
	<div id="step-3-section-{$idFieldTable}" class="wizard-card" data-cardname="step-3">
		<h3 class="hide-element">{$MOD['NAV_STEP3']}</h3>
		<div class="wizard-input-section data-section">
            {include file='Settings/GridManager/TableFieldActions/TableFieldAppearance.tpl'}
		</div>
	</div>

	<div class="wizard-failure text-center">
		<h4><strong style="color: #880000;">Error!</strong>: Se ha presentado un error al guardar el campo tabla</h4>
		<p class="message"></p>
	</div>
	<div class="wizard-loading text-center">
		<h4><strong>Por favor espera</strong></h4>
		<p>Estamos creando la tabla. Por favor espera unos instantes y por favor no cierres esta ventana</p>
		<img src="themes/images/loading.gif" class="img-responsive" style="display: inline-block;" />
	</div>
	<div class="wizard-success text-center">
		<h4><strong style="color: #008800;">Listo!</strong>: La tabla ha sido creada con éxito</h4>
		<button type="button" class="btn btn-default" style="margin-left: 5px;" onclick="TableFieldUtils.closeCreatorWizard ();">Terminar</button>
	</div>
</div>
</script>
<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-wizard.js"></script>
<script type="text/html" id="module-creator-wizard-field-template">
    {include file='Settings/GridManager/TableCreatorWizardField.tpl'}
</script>
	<script type="text/html" id="linkages-import-template">
        {include file='Settings/GridManager/TableFieldActions/ModuleLinkages.tpl'}
	</script>
	<script type="text/html" id="checkbox-action-template">
        {include file='Settings/GridManager/TableFieldActions/MainCheckBoxActions.tpl'}
	</script>
	<script type="text/html" id="operation-math-template">
        {include file='Settings/GridManager/TableFieldActions/MainOperationRow.tpl'}
	</script>
	<script type="text/html" id="field-linkages-template">
		<tr id="import-field-__ID_LINKAGE__-__ID_FIELD__" class="field" data-id="__ID_FIELD__">
			<td>
				<select name="linkages[modulefield][__ID_LINKAGE__]" class="form-control linkage-related-module" title="Campo para importar">
				</select>
			</td>
			<td>
				<select name="linkages[tablefield][__ID_LINKAGE__]" class="form-control linkage-related-module" title="Columna destino">
				</select>
			</td>
			<td class="text-center">
				<button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteRowLinkage (this);"><i class="fa fa-trash-o"></i></button>
			</td>
		</tr>
	</script>
{/strip}