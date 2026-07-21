{strip}
	{if (isset ($MODULE))}
		{assign var='moduleName' value=$MODULE->getName ()}
	{else}
		{assign var='moduleName' value=null}
	{/if}
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
						<input type="hidden" name="modulename" value="{$moduleName}" />
						<input type="hidden" id="calculatedSystemId" name="calculatedSystemId">
						<div class="panel-group" id="field-properties">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#basic-properties">Propiedades
											básicas</a>
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
													<label for="default-value"
														style="height: 34px; padding: 6px 0;">{$MOD.LBL_DEFAULT_VALUE}</label>
												</div>
												<div class="col-xs-12 col-md-9">
													<input type="text" id="default-value" class="form-control" />
													<span id="date-default-help" class="help-block" style="display: none;">
														<small>
															<strong>Para campos de fecha, use:</strong><br>
															• <code>TODAY</code> o <code>CURRENT_DATE</code> = Fecha actual<br>
															• <code>TODAY+5</code> = Fecha actual + 5 días<br>
															• <code>TODAY-3</code> = Fecha actual - 3 días<br>
															• <code>2025-12-31</code> = Fecha fija
														</small>
													</span>
												</div>
											</div>
											<div id="field-length-container" class="row form-group" style="display: none;">
												<div class="col-xs-12 col-md-3 text-right">
													<label for="field-length"
														style="height: 34px; padding: 6px 0;">Tamaño</label>
												</div>
												<div class="col-xs-12 col-md-2">
													<input type="number" id="field-length" class="form-control" />
												</div>
											</div>
											<div id="field-precision-container" class="row form-group " style="display: none;">
												<div class="col-xs-12 col-md-3 text-right">
													<label for="field-precision"
														style="height: 34px; padding: 6px 0;">Precisión</label>
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
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#validation-properties">Validaciones</a>
									</h4>
								</div>
								<div id="validation-properties" class="panel-collapse collapse in">
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
											<select id="initial-date-select" class="form-control" style="width: 100%;"
												title="Fecha mínima"
												onchange="FieldPropertiesUtils.setDateValidationFields (this);">
												<option value=""></option>
												<option value="today">Fecha actual</option>
												<option value="custom">Otra</option>
											</select>
											<div class="row custom-date-group" style="display: none;">
												<div class="col-xs-12">
													<div class="input-group" style="width: 100%;">
														<div class="input-group-addon"><i class="fa fa-calendar"></i></div>
														<input id="initial-date" class="form-control pull-right date" size="11"
															maxlength="18" readonly="readonly" type="text"
															placeholder="Fecha mínima" />
													</div>
												</div>
											</div>
										</div>
										<div class="form-group col-xs-4 hidden date-validation">
											<label for="maximum-date">Fecha máxima</label>
											<select id="maximum-date-select" class="form-control" style="width: 100%;"
												title="Fecha máxima"
												onchange="FieldPropertiesUtils.setDateValidationFields (this);">
												<option value=""></option>
												<option value="today">Fecha actual</option>
												<option value="custom">Otra</option>
											</select>
											<div class="row custom-date-group" style="display: none;">
												<div class="col-xs-12">
													<div class="input-group" style="width: 100%;">
														<div class="input-group-addon"><i class="fa fa-calendar"></i></div>
														<input id="maximum-date" class="form-control pull-right date" size="11"
															maxlength="18" readonly="readonly" type="text"
															placeholder="Fecha máxima" />
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
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#module-references-properties">Importar
											valores</a>
									</h4>
								</div>
								<div id="module-references-properties" class="panel-collapse collapse in">
									<div class="panel-body">
										{if (!empty ($AVAILABLE_ENTITY_MODULES))}
											<div class="col-xs-12" style="display: none;">
												<select id="module-reference" class="form-control" title="Módulo relacionado"
													onchange="FieldPropertiesUtils.setModuleReferenceRelationships (this);">
													<option value=""></option>
													{foreach $AVAILABLE_ENTITY_MODULES as $module}
														<option value="{$module.name}">{$module.label}
														</option>
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
																<button type="button"
																	class="btn btn-primary btn-icon center-block"
																	onclick="FieldPropertiesUtils.addModuleReferenceRelationship ();"><i
																		class="fa fa-plus"></i></button>
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
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#module-references-filters">Filtros</a>
									</h4>
								</div>
								<div id="module-references-filters" class="panel-collapse collapse in">
									<div class="panel-body">
										<div class="col-xs-12">
											<div class="table-responsive">
												<table class="table" style="margin-bottom: 0;">
													<thead>
														<tr>
															<th class="col-xs-4">Donde el contenido del campo <span
																	class="target-module-label"></span></th>
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
																<button type="button"
																	class="btn btn-primary btn-icon center-block"
																	onclick="FieldPropertiesUtils.addModuleReferenceFilter ();"><i
																		class="fa fa-plus"></i></button>
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
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#picklist-values-properties">Valores</a>
									</h4>
								</div>
								<div id="picklist-values-properties" class="panel-collapse collapse in">
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table" style="margin-bottom: 0;">
												<thead>
													<tr>
														<th class="col-xs-3">Valor</th>
														<th class="col-xs-3">Disponible para</th>
														<th class="col-xs-1"></th>
														<th class="col-xs-3">No disponible para</th>
														<th class="col-xs-2"></th>
													</tr>
												</thead>
												<tbody id="picklist-values"></tbody>
												<tfoot>
													<tr class="action-bar">
														<td colspan="5" style="padding: 0;">
															<button type="button"
																class="btn btn-primary btn-icon center-block add-value-button"
																onclick="FieldPropertiesUtils.addPicklistValue ();"><i
																	class="fa fa-plus"></i></button>
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
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#pipeline-values-properties">Valores</a>
									</h4>
								</div>
								<div id="pipeline-values-properties" class="panel-collapse collapse in">
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table" style="margin-bottom: 0;">
												<thead>
													<tr>
														<th class="col-xs-11" style="width: 60%">Valor</th>
														<th class="col-xs-1" style="width: 40%"></th>
													</tr>
												</thead>
												<tbody id="pipeline-values"></tbody>
												<tfoot>
													<tr class="action-bar">
														<td colspan="2" style="padding: 0;">
															<button type="button"
																class="btn btn-primary btn-icon center-block add-value-button"
																onclick="FieldPropertiesUtils.addPipelineValue ();"><i
																	class="fa fa-plus"></i></button>
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
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#dependencies-properties">Dependencias</a>
									</h4>
								</div>
								<div id="dependencies-properties" class="panel-collapse collapse in">
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
										<a data-toggle="collapse" data-parent="#field-properties" href="#calculation">Cálculo
											del sistema</a>
									</h4>
								</div>
								<div id="calculation" class="panel-collapse collapse in">
									<div class="panel-body">
										<div class="form-group col-xs-4 checkbox-nice">
											<input class="form-control input-sm search_Calculated" type="text"
												placeholder="Buscar cálculo"
												oninput="FieldPropertiesUtils.searchCalculated(this)">
										</div>
										<div class="form-group col-xs-9 checkbox-nice">
											<div class="list-group calculated-list">
												<a id="calculate-template" href="javascript: void(0);" rel="@"
													title="calculated template" class="list-group-item hide"
													onclick="FieldPropertiesUtils.setCalculatedSystem(this)">template</a>
											</div>
										</div>
										<div class="form-group col-xs-9 checkbox-nice">
											<p style="text-align: left">¿No encuentras el cálculo que necesitas? <a
													href="?module=calculated_fields&action=index&tab=system"
													title="Crear cálculo" target="_blank">Crea tu propio cálculo</a></p>
										</div>
									</div>
								</div>
							</div>
							{* panel visibility *}
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#visibility-values-properties">Visibilidad del campo&nbsp;<span
												id="visibility-field-name"></span></a>
									</h4>
								</div>
								<div id="visibility-values-properties" class="panel-collapse collapse in">
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table" style="margin-bottom: 0;">
												<thead>
													<tr>
														<th class="col-xs-1"></th>
														<th class="col-xs-5">Mostrar en perfil</th>
														<th class="col-xs-1"></th>
														<th class="col-xs-5">Ocultar en perfil</th>
														{*<th class="col-xs-2"></th>*}
													</tr>
												</thead>
												<tbody id="visibility-values"></tbody>
												<tfoot>
													<tr class="action-bar">
														<td colspan="5" style="padding: 0;">
															&nbsp;
														</td>
													</tr>
												</tfoot>
											</table>
										</div>
									</div>
								</div>
							</div>
							{* / panel visibility *}
							{* panel relationship *}
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#picklist-relationship">Relación entre
											campos listas</a>
									</h4>
								</div>
								<div id="picklist-relationship" class="panel-collapse collapse in">
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table">
												<thead>
													<tr>
														<td colspan="4">
															<table class="table">
																<tr>
																	<td class="col-xs-4">
																		<input type="text"
																			class="form-control available-mother" readonly
																			id="picklistMotherLabel" value="">
																		<input type="hidden" id="relationship-name">
																	</td>
																	<td class="col-xs-2" style="vertical-align: center">
																		<h1 style="text-align: center"><span class="glyphicon glyphicon-transfer"></span>
																		</h1>
																	</td>
																	<td class="col-xs-4">
																		<select class="form-control" name="daughterpicklist"
																			id="available-daughter"
																			title="Listas hijas disponibles"
																			onchange="FieldPropertiesUtils.setPicklistDaughter (this);">
																		</select>
																	</td>
																	<td class="col-xs-2" style="vertical-align: center">
																		<button class="btn btn-danger" type="button"
																			title="Eliminar relación entre campos listas"
																			onclick="FieldPropertiesUtils.deleteRelationship (this);"><i
																				class="fa fa-trash-o"></i></button>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
													<tr>
														<th class="col-xs-3">Valor</th>
														<th class="col-xs-4">Mostrar</th>
														<th class="col-xs-1">&nbsp;</th>
														<th class="col-xs-4">Ocultar</th>
													</tr>
												</thead>
												<tbody id="relationship-tbody">
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
							{* panel relationship *}
							{* panel picklist-pipeline relationship *}
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#field-properties"
											href="#picklist-pipeline-relationship">Relación Picklist → Pipeline</a>
									</h4>
								</div>
								<div id="picklist-pipeline-relationship" class="panel-collapse collapse in">
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table">
												<thead>
													<tr>
														<td colspan="4">
															<table class="table">
																<tr>
																	<td class="col-xs-4">
																		<input type="text"
																			class="form-control available-mother" readonly
																			id="picklistMotherPipelineLabel" value="">
																		<input type="hidden" id="pipeline-relationship-name">
																	</td>
																	<td class="col-xs-2" style="vertical-align: center">
																		<h1 style="text-align: center"><span
																				class="glyphicon glyphicon-transfer"></span>
																		</h1>
																	</td>
																	<td class="col-xs-4">
																		<select class="form-control" name="pipelinefield"
																			id="available-pipeline"
																			title="Campos Pipeline disponibles"
																			onchange="FieldPropertiesUtils.setPipelineDaughter (this);">
																		</select>
																	</td>
																	<td class="col-xs-2" style="vertical-align: center">
																		<button class="btn btn-danger" type="button"
																			title="Eliminar relación Picklist → Pipeline"
																			onclick="FieldPropertiesUtils.deletePipelineRelationship (this);"><i
																				class="fa fa-trash-o"></i></button>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
													<tr>
														<th class="col-xs-3">Valor Picklist</th>
														<th class="col-xs-4">Mostrar</th>
														<th class="col-xs-1">&nbsp;</th>
														<th class="col-xs-4">Ocultar</th>
													</tr>
												</thead>
												<tbody id="pipeline-relationship-tbody">
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
							{* / panel picklist-pipeline relationship *}
						</div>
					</div>
					<div class="modal-footer">
						<button id="btn-field-properties-save" type="button"
							class="btn btn-primary non-grid-stuff"
							onclick="FieldPropertiesUtils.saveProperties (this)">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
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
				<button type="button" class="btn btn-danger btn-icon center-block"
					onclick="FieldPropertiesUtils.deleteModuleReferenceRelationship (this);"><i
						class="fa fa-trash-o"></i></button>
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
				<label class="radio-inline"><input type="radio" name="filtertype" value="SOURCE FIELD" class="filter-type"
						style="margin-top: 0;" onclick="FieldPropertiesUtils.setModuleReferenceFilterType (this);" />Campo de
					{$MODULE_LABEL}</label>
				<label class="radio-inline"><input type="radio" name="filtertype" value="LITERAL" class="filter-type"
						style="margin-top: 0;"
						onclick="FieldPropertiesUtils.setModuleReferenceFilterType (this);" />Valor</label>
				<select class="form-control filter-fields" title="Campo" style="display: none; margin-top: 5px;"></select>
				<input type="text" class="form-control filter-value" placeholder="Valor"
					style="display: none; margin-top: 5px;" />
			</td>
			<td class="col-xs-1">
				<select class="form-control operator" title="" style="display: none;" disabled="disabled">
					<option value="OR">o</option>
					<option value="AND">y</option>
				</select>
			</td>
			<td class="col-xs-1">
				<button type="button" class="btn btn-danger btn-icon center-block"
					onclick="FieldPropertiesUtils.deleteModuleReferenceFilter (this);"><i class="fa fa-trash-o"></i></button>
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
					<button type="button" class="btn btn-primary btn-icon center-block"
						onclick="FieldPropertiesUtils.showDependencyFields (this);"><i class="fa fa-angle-left"></i></button>
					<button type="button" class="btn btn-warning btn-icon center-block"
						onclick="FieldPropertiesUtils.removeVisibleDependencyFields (this);"><i
							class="fa fa-angle-right"></i></button>
				</div>
				<select class="form-control available-fields" title="" multiple="multiple">
					<optgroup class="optional-fields" label="Campos opcionales"></optgroup>
					<optgroup class="mandatory-fields" label="Campos obligatorios"></optgroup>
				</select>
				<div class="vertical-group right">
					<button type="button" class="btn btn-warning btn-icon center-block"
						onclick="FieldPropertiesUtils.hideDependencyFields (this);"><i class="fa fa-angle-right"></i></button>
					<button type="button" class="btn btn-primary btn-icon center-block"
						onclick="FieldPropertiesUtils.removeHiddenDependencyFields (this);"><i
							class="fa fa-angle-left"></i></button>
				</div>
			</td>
			<td>
				<select class="form-control hidden-fields" title="" multiple="multiple"></select>
			</td>
		</tr>
	</script>
	<script type="text/html" id="picklist-value-template">
		<tr class="picklist-value">
			<td class="col-xs-3">
				<input type="hidden" class="picklist-value-id" />
				<input type="hidden" class="picklist-seq" />
				<input type="text" class="form-control picklist-label" placeholder=""
					onchange="FieldPropertiesUtils.setPicklistDependencyLabel (this);" />
			</td>
			<td class="col-xs-3">
				<select class="form-control visible-roles" title="" multiple="multiple"></select>
			</td>
			<td class="col-xs-1">
				<div class="vertical-group">
					<button type="button" class="btn btn-warning btn-icon center-block hide-value-button"
						onclick="FieldPropertiesUtils.hidePicklistValues (this);"><i class="fa fa-angle-right"></i></button>
					<button type="button" class="btn btn-primary btn-icon center-block show-value-button"
						onclick="FieldPropertiesUtils.showPicklistValues (this);"><i class="fa fa-angle-left"></i></button>
				</div>
			</td>
			<td class="col-xs-3">
				<select class="form-control hidden-roles" title="" multiple="multiple"></select>
			</td>
			<td class="col-xs-3">
				<div class="center-block" style="display: inline">
					<button type="button" class="btn btn-xs btn-danger delete-value-button"
						onclick="FieldPropertiesUtils.deletePicklistValue (this);"><i class="fa fa-trash-o"></i></button>&nbsp;
					<button type="button" class="btn btn-primary btn-xs"
						onclick="FieldPropertiesUtils.movePickListRowUp (this)"><i class="fa fa-arrow-up"
							aria-hidden="true"></i></button>&nbsp;
					<button type="button" class="btn btn-danger btn-xs"
						onclick="FieldPropertiesUtils.movePickListRowDown (this)"><i class="fa fa-arrow-down"
							aria-hidden="true"></i></button>
				</div>
			</td>
		</tr>
	</script>
	<script type="text/html" id="pipeline-value-template">
		<tr class="pipeline-value">
			<td class="col-xs-11" style="width: 70%">
				<input type="text" class="form-control pipeline-label" placeholder=""
					onchange="FieldPropertiesUtils.setPipelineDependencyLabel (this);" />
			</td>
			<td class="col-xs-1 center-block " style="width: 30%">
				<button type="button" class="btn btn-danger btn-icon delete-value-button"
					onclick="FieldPropertiesUtils.deletePipelineValue (this);"><i class="fa fa-trash-o"></i></button>
				<button type="button" class="btn btn-primary btn-xs" onclick="FieldPropertiesUtils.movePipeLineRowUp (this)"><i
						class="fa fa-arrow-up" aria-hidden="true"></i></button>
				<button type="button" class="btn btn-danger btn-xs"
					onclick="FieldPropertiesUtils.movePipeLineRowDown (this)"><i class="fa fa-arrow-down"
						aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
	<script type="text/html" id="fields-visibility-template">
		<tr class="visibility-value">
			<td class="col-xs-1">
				<input type="hidden" class="visibility-profile-id" />
			</td>
			<td class="col-xs-5">
				<select id="visibleprofiles" class="form-control visible-profiles" title="" multiple="multiple"
					name="visibleprofiles"></select>
			</td>
			<td class="col-xs-1">
				<div class="vertical-group">
					<button type="button" class="btn btn-warning btn-icon center-block hide-value-button"
						onclick="FieldPropertiesUtils.hideInPrifile (this);"><i class="fa fa-angle-right"></i></button>
					<button type="button" class="btn btn-primary btn-icon center-block show-value-button"
						onclick="FieldPropertiesUtils.showInPrifile (this);"><i class="fa fa-angle-left"></i></button>
				</div>
			</td>
			<td class="col-xs-5">
				<select id="hiddenprofiles" class="form-control hidden-profiles" title="" multiple="multiple"
					name="hiddenprofiles"></select>
			</td>
		</tr>
	</script>
	<script type="text/html" id="fields-picklist-relationship-template">
		<tr class="tr-picklist-relationship">
			<td>
				<input type="hidden" class="picklist-mother-value" name="motherpicklistid[]" />
				<textarea class="form-control picklist-mother-label" readonly="readonly" placeholder="" rows="4"></textarea>
			</td>
			<td>
				<select class="form-control picklist-daughter-values" name="selecteddaughteroptions[]" title=""
					multiple="multiple">
				</select>
			</td>
			<td>
				<div class="vertical-group right">
					<button type="button" class="btn btn-warning btn-icon center-block"
						onclick="FieldPropertiesUtils.hiddenOptionDaughter (this);"><i class="fa fa-angle-right"></i></button>
					<button type="button" class="btn btn-primary btn-icon center-block"
						onclick="FieldPropertiesUtils.showOptionDaughter (this);"><i class="fa fa-angle-left"></i></button>
				</div>
			</td>
			<td>
				<select class="form-control picklist-daughter-hidden-values" title="" multiple="multiple"></select>
			</td>
		</tr>
	</script>
	<script type="text/html" id="fields-picklist-pipeline-relationship-template">
		<tr class="tr-picklist-pipeline-relationship">
			<td>
				<input type="hidden" class="picklist-mother-value" name="motherpicklistid[]" />
				<textarea class="form-control picklist-mother-label" readonly="readonly" placeholder="" rows="4"></textarea>
			</td>
			<td>
				<select class="form-control pipeline-values" name="selectedpipelinevalues[]" title="" multiple="multiple">
				</select>
			</td>
			<td>
				<div class="vertical-group right">
					<button type="button" class="btn btn-warning btn-icon center-block"
						onclick="FieldPropertiesUtils.hiddenPipelineValue (this);"><i class="fa fa-angle-right"></i></button>
					<button type="button" class="btn btn-primary btn-icon center-block"
						onclick="FieldPropertiesUtils.showPipelineValue (this);"><i class="fa fa-angle-left"></i></button>
				</div>
			</td>
			<td>
				<select class="form-control pipeline-hidden-values" title="" multiple="multiple"></select>
			</td>
		</tr>
	</script>
{/strip}