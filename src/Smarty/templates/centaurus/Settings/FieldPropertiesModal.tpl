{strip}
<style type="text/css">
	.hideToMe {
		display: none;
	}
	#field-properties-modal {
		top:     0;
		z-index: 950;
	}
	#field-properties-modal .modal-body {
		max-height: 70vh;
		min-height: 70vh;
		overflow-x: hidden;
		overflow-y: auto;
	}
	#field-properties-modal .dependency,
	#field-properties-modal .picklist-value,
	#field-properties-modal .pipeline-value {
		margin-bottom: 5px;
		margin-top:    5px;
	}
	#field-properties-modal .dependency .hidden-fields,
	#field-properties-modal .dependency .visible-fields,
	#field-properties-modal .dependency .available-fields,
	#field-properties-modal .picklist-value .visible-roles,
	#field-properties-modal .picklist-value .hidden-roles {
		height: 8em;
	}
	#field-properties-modal .vertical-group {
		margin-top: 1.5em;
	}
	#field-properties-modal .dependency .available-fields {
		display:        inline-block;
		vertical-align: middle;
		width:          70%;
	}
	#field-properties-modal .dependency .available-fields > optgroup > option {
		padding-left: 5px;
	}
	#field-properties-modal .dependency .vertical-group {
		display: inline-block;
		margin-top: 0;
		vertical-align: middle;
	}
	#field-properties-modal .dependency .vertical-group.left {
		margin-right: 0.5em;
	}
	#field-properties-modal .dependency .vertical-group.right {
		margin-left: 0.5em;
	}
	#field-properties-modal .btn.btn-icon {
		font-size:   14px;
		height:      27px;
		line-height: 27px;
		margin:      5px auto;
		padding:     0;
		text-align:  center;
		width:       27px;
	}
	.action-summary {
		width:100%;
		vertical-align: top;
	}
	.calculated-list {
		max-height: 550px;
		overflow-y: auto;
		font-size: .8em
	}
	.calculated-list a {
		padding: 4px 6px !important;
		margin: 1px;
	}
</style>
<script type="text/html" id="field-properties-modal-template">
	<div id="field-properties-modal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Propiedades del campo <span id="field-name"></span></h4>
				</div>
				<div class="modal-body">
					<input type="hidden" name="fieldname" />
					<input type="hidden" name="modulename" value="{$MODULE}" />
					<input type="hidden" id="calculatedSystemId"  name="calculatedSystemId">
					<div class="panel-group" id="field-properties">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#field-properties" href="#basic-properties">Propiedades básicas</a>
								</h4>
							</div>
							<div id="basic-properties" class="panel-collapse collapse in">
								<div class="panel-body">
									<div class="col-xs-12 col-md-3">
										<div class="form-group checkbox-nice">
											<input type="checkbox" id="ismandatory" />
											<label for="ismandatory">{$MOD.LBL_MANDATORY_FIELD}</label>
										</div>
										<div class="form-group checkbox-nice">
											<input type="checkbox" id="presence" />
											<label for="presence">{$MOD.LBL_ACTIVE}</label>
										</div>
									</div>
									<div class="col-xs-12 col-md-9">
										<div class="row form-group">
											<div class="col-xs-12 col-md-3 text-right">
												<label for="default-value" style="height: 34px; padding: 6px 0;">{$MOD.LBL_DEFAULT_VALUE}</label>
											</div>
											<div class="col-xs-12 col-md-9">
												<input type="text" id="default-value" class="form-control" />
											</div>
										</div>
										<div id="field-length-container" class="row form-group" style="display: none;">
											<div class="col-xs-12 col-md-3 text-right">
												<label for="field-length" style="height: 34px; padding: 6px 0;">Tamaño</label>
											</div>
											<div class="col-xs-12 col-md-2">
												<input type="number" id="field-length" class="form-control" />
											</div>
										</div>
										<div id="field-precision-container" class="row form-group " style="display: none;">
											<div class="col-xs-12 col-md-3 text-right">
												<label for="field-precision" style="height: 34px; padding: 6px 0;">Precisión</label>
											</div>
											<div class="col-xs-12 col-md-2">
												<input type="number" id="field-precision" class="form-control" />
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#field-properties" href="#validation-properties">Validaciones</a>
								</h4>
							</div>
							<div id="validation-properties" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="form-group col-xs-4 checkbox-nice">
										<input type="checkbox" id="unique" />
										<label for="unique">Valor no repetible</label>
									</div>
									<div class="form-group col-xs-4 hidden number-validation">
										<input type="text" id="initial-value" class="form-control" placeholder="{$MOD.LBL_INITIAL_VALUE}" />
									</div>
									<div class="form-group col-xs-4 hidden number-validation">
										<input type="text" id="maximum-value" class="form-control" placeholder="{$MOD.LBL_MAXIMUM_VALUE}" />
									</div>
									<div class="form-group col-xs-4 hidden date-validation">
										<label for="initial-date">Fecha mínima</label>
										<select id="initial-date-select" class="form-control" style="width: 100%;" title="Fecha mínima" onchange="FieldPropertiesUtils.setDateValidationFields (this);">
											<option value=""></option>
											<option value="today">Fecha actual</option>
											<option value="custom">Otra</option>
										</select>
										<div class="row custom-date-group" style="display: none;">
											<div class="col-xs-12">
												<div class="input-group" style="width: 100%;">
													<div class="input-group-addon"><i class="fa fa-calendar"></i></div>
													<input id="initial-date" class="form-control pull-right date" size="11" maxlength="18" readonly="readonly" type="text" placeholder="Fecha mínima" />
												</div>
											</div>
										</div>
									</div>
									<div class="form-group col-xs-4 hidden date-validation">
										<label for="maximum-date">Fecha máxima</label>
										<select id="maximum-date-select" class="form-control" style="width: 100%;" title="Fecha máxima" onchange="FieldPropertiesUtils.setDateValidationFields (this);">
											<option value=""></option>
											<option value="today">Fecha actual</option>
											<option value="custom">Otra</option>
										</select>
										<div class="row custom-date-group" style="display: none;">
											<div class="col-xs-12">
												<div class="input-group" style="width: 100%;">
													<div class="input-group-addon"><i class="fa fa-calendar"></i></div>
													<input id="maximum-date" class="form-control pull-right date" size="11" maxlength="18" readonly="readonly" type="text" placeholder="Fecha máxima" />
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#field-properties" href="#module-references-properties">Importar valores</a>
								</h4>
							</div>
							<div id="module-references-properties" class="panel-collapse collapse">
								<div class="panel-body">
{if (!empty ($ENTITY_MODULES))}
									<div class="col-xs-12" style="display: none;">
										<select id="module-reference" class="form-control" title="Módulo relacionado" onchange="FieldPropertiesUtils.setModuleReferenceRelationships (this);">
											<option value=""></option>
	{foreach $ENTITY_MODULES as $module}
											<option value="{$module.name}">{$module.label}</option>
	{/foreach}
										</select>
									</div>
{/if}
									<div class="col-xs-12">
										<div class="table-responsive">
											<table class="table" style="margin-bottom: 0;">
												<thead>
												<tr>
													<th class="col-xs-5">Importar el valor del campo</th>
													<th class="col-xs-5">al campo</th>
													<th class="col-xs-1"></th>
													<th class="col-xs-1"></th>
												</tr>
												</thead>
												<tbody id="relationships"></tbody>
												<tfoot>
												<tr class="action-bar">
													<td colspan="4" style="padding: 0;">
														<button type="button" class="btn btn-primary btn-icon center-block" onclick="FieldPropertiesUtils.addModuleReferenceRelationship ();"><i class="fa fa-plus"></i></button>
													</td>
												</tr>
												</tfoot>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#field-properties" href="#module-references-filters">Filtros</a>
								</h4>
							</div>
							<div id="module-references-filters" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="col-xs-12">
										<div class="table-responsive">
											<table class="table" style="margin-bottom: 0;">
												<thead>
												<tr>
													<th class="col-xs-4">Donde el contenido del campo <span class="target-module-label"></span></th>
													<th class="col-xs-2">sea</th>
													<th class="col-xs-4">Al contenido del campo / valor</th>
													<th class="col-xs-1"></th>
													<th class="col-xs-1"></th>
												</tr>
												</thead>
												<tbody id="reference-filters"></tbody>
												<tfoot>
												<tr class="action-bar">
													<td colspan="5" style="padding: 0;">
														<button type="button" class="btn btn-primary btn-icon center-block" onclick="FieldPropertiesUtils.addModuleReferenceFilter ();"><i class="fa fa-plus"></i></button>
													</td>
												</tr>
												</tfoot>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#field-properties" href="#picklist-values-properties">Valores</a>
								</h4>
							</div>
							<div id="picklist-values-properties" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="table-responsive">
										<table class="table" style="margin-bottom: 0;">
											<thead>
											<tr>
												<th class="col-xs-4">Valor</th>
												<th class="col-xs-3">Disponible para</th>
												<th class="col-xs-1"></th>
												<th class="col-xs-3">No disponible para</th>
												<th class="col-xs-1"></th>
											</tr>
											</thead>
											<tbody id="picklist-values"></tbody>
											<tfoot>
											<tr class="action-bar">
												<td colspan="5" style="padding: 0;">
													<button type="button" class="btn btn-primary btn-icon center-block add-value-button" onclick="FieldPropertiesUtils.addPicklistValue ();"><i class="fa fa-plus"></i></button>
												</td>
											</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#field-properties" href="#pipeline-values-properties">Valores</a>
								</h4>
							</div>
							<div id="pipeline-values-properties" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="table-responsive">
										<table class="table" style="margin-bottom: 0;">
											<thead>
											<tr>
												<th class="col-xs-11">Valor</th>
												<th class="col-xs-1"></th>
											</tr>
											</thead>
											<tbody id="pipeline-values"></tbody>
											<tfoot>
											<tr class="action-bar">
												<td colspan="2" style="padding: 0;">
													<button type="button" class="btn btn-primary btn-icon center-block add-value-button" onclick="FieldPropertiesUtils.addPipelineValue ();"><i class="fa fa-plus"></i></button>
												</td>
											</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#field-properties" href="#dependencies-properties">Dependencias</a>
								</h4>
							</div>
							<div id="dependencies-properties" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="table-responsive">
										<table class="table">
											<thead>
											<tr>
												<th class="col-xs-2">Valor</th>
												<th class="col-xs-3">Mostrar</th>
												<th class="col-xs-4">No modificar</th>
												<th class="col-xs-3">Ocultar</th>
											</tr>
											</thead>
											<tbody id="dependencies"></tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#field-properties" href="#calculation">Cálculo del sistema</a>
								</h4>
							</div>
							<div id="calculation" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="form-group col-xs-4 checkbox-nice">
										<input class="form-control input-sm search_Calculated" type="text" placeholder="Buscar cálculo" oninput="FieldPropertiesUtils.searchCalculated(this)">
									</div>
									<div class="form-group col-xs-9 checkbox-nice">
									<div class="list-group calculated-list">
										<a id="calculate-template" href="javascript: void(0);" rel="@" title="calculated template" class="list-group-item hide" onclick="FieldPropertiesUtils.setCalculatedSystem(this)">template</a>
									</div>
									</div>
									<div class="form-group col-xs-9 checkbox-nice">
										<p style="text-align: left">¿No encuentras el cálculo que necesitas? <a href="?module=calculated_fields&action=index&tab=system" title="Crear cálculo" target="_blank">Crea tu propio cálculo</a></p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary non-grid-stuff" onclick="FieldPropertiesUtils.saveProperties ()">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="relationship-template">
	<tr class="relationship">
		<td class="col-xs-5">
			<select class="form-control referenced-module-fields" title="Copiar el valor del campo"></select>
		</td>
		<td class="col-xs-5">
			<select class="form-control module-fields" title="En el campo"></select>
		</td>
		<td class="col-xs-1">
			<button type="button" class="btn btn-danger btn-icon center-block" onclick="FieldPropertiesUtils.deleteModuleReferenceRelationship (this);"><i class="fa fa-trash-o"></i></button>
		</td>
		<td class="col-xs-1"></td>
	</tr>
</script>
<script type="text/html" id="reference-filter-template">
	<tr class="filter">
		<td class="col-xs-4">
			<select class="form-control module-fields" title="Campo"></select>
		</td>
		<td class="col-xs-2">
			<select class="form-control comparator" title="Operador">
				<option value=""></option>
				<option value="EQUALS">igual a</option>
				<option value="NOT_EQUALS">diferente a</option>
			</select>
		</td>
		<td class="col-xs-4 filter-target">
			<label class="radio-inline"><input type="radio" name="filtertype" value="SOURCE FIELD" class="filter-type" style="margin-top: 0;" onclick="FieldPropertiesUtils.setModuleReferenceFilterType (this);" />Campo de {$MODULE_LABEL}</label>
			<label class="radio-inline"><input type="radio" name="filtertype" value="LITERAL" class="filter-type" style="margin-top: 0;" onclick="FieldPropertiesUtils.setModuleReferenceFilterType (this);" />Valor</label>
			<select class="form-control filter-fields" title="Campo" style="display: none; margin-top: 5px;"></select>
			<input type="text" class="form-control filter-value" placeholder="Valor" style="display: none; margin-top: 5px;" />
		</td>
		<td class="col-xs-1">
			<select class="form-control operator" title="" style="display: none;" disabled="disabled">
				<option value="OR">o</option>
				<option value="AND">y</option>
			</select>
		</td>
		<td class="col-xs-1">
			<button type="button" class="btn btn-danger btn-icon center-block" onclick="FieldPropertiesUtils.deleteModuleReferenceFilter (this);"><i class="fa fa-trash-o"></i></button>
		</td>
	</tr>
</script>
<script type="text/html" id="dependency-template">
	<tr class="dependency">
		<td>
			<input type="hidden" class="picklist-value" />
			<textarea class="form-control picklist-label" readonly="readonly" placeholder="" rows="4"></textarea>
		</td>
		<td>
			<select class="form-control visible-fields" title="" multiple="multiple"></select>
		</td>
		<td>
			<div class="vertical-group left">
				<button type="button" class="btn btn-primary btn-icon center-block" onclick="FieldPropertiesUtils.showDependencyFields (this);"><i class="fa fa-angle-left"></i></button>
				<button type="button" class="btn btn-warning btn-icon center-block" onclick="FieldPropertiesUtils.removeVisibleDependencyFields (this);"><i class="fa fa-angle-right"></i></button>
			</div>
			<select class="form-control available-fields" title="" multiple="multiple">
				<optgroup class="optional-fields" label="Campos opcionales"></optgroup>
				<optgroup class="mandatory-fields" label="Campos obligatorios"></optgroup>
			</select>
			<div class="vertical-group right">
				<button type="button" class="btn btn-warning btn-icon center-block" onclick="FieldPropertiesUtils.hideDependencyFields (this);"><i class="fa fa-angle-right"></i></button>
				<button type="button" class="btn btn-primary btn-icon center-block" onclick="FieldPropertiesUtils.removeHiddenDependencyFields (this);"><i class="fa fa-angle-left"></i></button>
			</div>
		</td>
		<td>
			<select class="form-control hidden-fields" title="" multiple="multiple"></select>
		</td>
	</tr>
</script>
<script type="text/html" id="picklist-value-template">
	<tr class="picklist-value">
		<td class="col-xs-4">
			<input type="hidden" class="picklist-value-id" />
			<input type="text" class="form-control picklist-label" placeholder="" onchange="FieldPropertiesUtils.setPicklistDependencyLabel (this);" />
		</td>
		<td class="col-xs-3">
			<select class="form-control visible-roles" title="" multiple="multiple"></select>
		</td>
		<td class="col-xs-1">
			<div class="vertical-group">
				<button type="button" class="btn btn-warning btn-icon center-block hide-value-button" onclick="FieldPropertiesUtils.hidePicklistValues (this);"><i class="fa fa-angle-right"></i></button>
				<button type="button" class="btn btn-primary btn-icon center-block show-value-button" onclick="FieldPropertiesUtils.showPicklistValues (this);"><i class="fa fa-angle-left"></i></button>
			</div>
		</td>
		<td class="col-xs-3">
			<select class="form-control hidden-roles" title="" multiple="multiple"></select>
		</td>
		<td class="col-xs-1">
			<div class="vertical-group">
				<button type="button" class="btn btn-danger btn-icon center-block delete-value-button" onclick="FieldPropertiesUtils.deletePicklistValue (this);"><i class="fa fa-trash-o"></i></button>
			</div>
		</td>
	</tr>
</script>
<script type="text/html" id="pipeline-value-template">
	<tr class="pipeline-value">
		<td class="col-xs-11">
			<input type="text" class="form-control pipeline-label" placeholder="" onchange="FieldPropertiesUtils.setPipelineDependencyLabel (this);" />
		</td>
		<td class="col-xs-1">
			<button type="button" class="btn btn-danger btn-icon center-block delete-value-button" onclick="FieldPropertiesUtils.deletePipelineValue (this);"><i class="fa fa-trash-o"></i></button>
		</td>
	</tr>
</script>
<script type="text/javascript" src="modules/Settings/field-properties.js"></script>
{/strip}