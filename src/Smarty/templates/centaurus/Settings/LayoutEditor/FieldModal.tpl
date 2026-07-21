{strip}
{if (isset ($MODULE))}
	{assign var='moduleName' value=$MODULE->getName ()}
{else}
	{assign var='moduleName' value=null}
{/if}
<script type="text/html" id="field-modal-template">
	<div class="modal fade" id="field-modal" tabindex="-1" role="dialog" aria-hidden="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<form action="index.php" method="post" onsubmit="FieldUtils.saveField (this); return false;" autocomplete="off">
					<input type="hidden" name="module" value="Settings" />
					<input type="hidden" name="action" value="SaveField" />
					<input type="hidden" name="modulename" value="{$moduleName}" />
					<input type="hidden" name="blockid" />
					<input type="hidden" name="Ajax" value="true" />
					<div class="modal-header">
						<button id="field-utils-close" type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Crear campo</h4>
						<span id="field-utils-help-block" class="help-block" style="color: red"></span>
					</div>
					<div class="row modal-body field-container">
						<ul class="col-xs-12 col-md-6 field-types">
{foreach $FIELD_TYPE_OPTIONS as $fieldType}
	{if (in_array ($fieldType.value, array (FieldInterface::UI_TYPE_CODE)))}{continue}{/if}
							<li class="field-type" onclick="FieldUtils.setSelectedFieldType (this, {$fieldType.value});">
								<i class="fa {$fieldType.icon} fa-fw fa-ed" aria-hidden="true"></i>&nbsp;{$fieldType.text}
							</li>
{/foreach}
						</ul>
						<div class="col-xs-12 col-md-6 field-definitions">
							<input type="hidden" id="field-type" name="uitype" />
							<div style="display: none" class="form-group field-definition" data-types="[ {FieldInterface::UI_TYPE_ATTACHMENTS}, {FieldInterface::UI_TYPE_CALCULATED_LINK}, {FieldInterface::UI_TYPE_CHECKBOX}, {FieldInterface::UI_TYPE_CURRENCY}, {FieldInterface::UI_TYPE_DATE}, {FieldInterface::UI_TYPE_EMAIL}, {FieldInterface::UI_TYPE_IMAGE_DISPLAY}, {FieldInterface::UI_TYPE_MODULE_REFERENCE}, {FieldInterface::UI_TYPE_MULTI_SELECT}, {FieldInterface::UI_TYPE_NUMBER}, {FieldInterface::UI_TYPE_PERCENTAGE}, {FieldInterface::UI_TYPE_PHONE}, {FieldInterface::UI_TYPE_PICKLIST}, {FieldInterface::UI_TYPE_PIPELINE}, {FieldInterface::UI_TYPE_TEXT}, {FieldInterface::UI_TYPE_TEXTAREA}, {FieldInterface::UI_TYPE_URL}, {FieldInterface::UI_TYPE_VIDEO}, {FieldInterface::UI_TYPE_APP} ]">
								<label for="field-name">Código</label>
								<input type="text" id="field-name" name="name" class="form-control" disabled="disabled" maxlength="30" onkeydown="FieldUtils.normalizeFieldContents (this, event);" />
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_ATTACHMENTS}, {FieldInterface::UI_TYPE_CALCULATED_LINK}, {FieldInterface::UI_TYPE_CHECKBOX}, {FieldInterface::UI_TYPE_CURRENCY}, {FieldInterface::UI_TYPE_DATE}, {FieldInterface::UI_TYPE_EMAIL}, {FieldInterface::UI_TYPE_GLOBAL_PICKLIST}, {FieldInterface::UI_TYPE_IMAGE_DISPLAY}, {FieldInterface::UI_TYPE_MODULE_REFERENCE}, {FieldInterface::UI_TYPE_MULTI_SELECT}, {FieldInterface::UI_TYPE_NUMBER}, {FieldInterface::UI_TYPE_PERCENTAGE}, {FieldInterface::UI_TYPE_PHONE}, {FieldInterface::UI_TYPE_PICKLIST}, {FieldInterface::UI_TYPE_PIPELINE}, {FieldInterface::UI_TYPE_TEXT}, {FieldInterface::UI_TYPE_TEXTAREA}, {FieldInterface::UI_TYPE_URL}, {FieldInterface::UI_TYPE_VIDEO}, {FieldInterface::UI_TYPE_APP} ]">
								<label for="field-label">Nombre</label>
								<input autocomplete="off" type="text" id="field-label" name="label" class="form-control" disabled="disabled" maxlength="30" onkeydown="FieldUtils.normalizeFieldContents (this, event);"/>
								<span class="help-block"><small>Solo admite letras/numeros y los símbolos - y _<br/>La longitud es de 30 Caracteres </small></span>
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_TEXT} ]">
								<label for="field-length">Extensión</label>
								<input type="number" id="field-length" name="length" class="form-control" disabled="disabled" min="1" max="255" onkeyup="FieldUtils.normalizeFieldLength (this, event);"/>
								<span class="help-block"><small>Solo admite números, máximo valor 255</small></span>
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_CURRENCY}, {FieldInterface::UI_TYPE_NUMBER} ]">
								<label for="field-precision">Decimales</label>
								<input type="number" id="field-precision" name="precision" class="form-control" disabled="disabled" min="1" max="10" />
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_EMAIL}, {FieldInterface::UI_TYPE_PHONE}, {FieldInterface::UI_TYPE_TEXT}, {FieldInterface::UI_TYPE_URL} ]">
								<label for="field-unique">Valor único</label>
								<select id="field-unique" name="unique" class="form-control" disabled="disabled">
									<option value="0">No</option>
									<option value="1">Sí</option>
								</select>
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_DATE} ]">
								<label for="field-default-date">Fecha por defecto</label>
								<input type="text" id="field-default-date" name="defaultdate" class="form-control" disabled="disabled" placeholder="Ej: TODAY, TODAY+5, CURRENT_DATE-3, 2025-12-31" />
								<span class="help-block">
									<small>
										<strong>Ejemplos válidos:</strong><br>
										• <code>TODAY</code> o <code>CURRENT_DATE</code> = Fecha actual<br>
										• <code>TODAY+5</code> = Fecha actual + 5 días<br>
										• <code>TODAY-3</code> = Fecha actual - 3 días<br>
										• <code>2025-12-31</code> = Fecha fija específica<br>
										• Vacío = Fecha actual automáticamente
									</small>
								</span>
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_MULTI_SELECT}, {FieldInterface::UI_TYPE_PICKLIST}, {FieldInterface::UI_TYPE_PIPELINE} ]">
								<label for="field-picklist-values">Valores de la lista</label>
								<textarea id="field-picklist-values" name="picklistvalues" class="form-control" disabled="disabled"></textarea>
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_MODULE_REFERENCE} ]">
								<label for="field-referenced-module-name">Referencia a módulo</label>
								<select id="field-referenced-module-name" name="referencedmodulename" class="form-control" disabled="disabled">
									<option value=""></option>
{foreach $AVAILABLE_ENTITY_MODULES as $module}
									<option value="{$module.name}">{$module.label}</option>
{/foreach}
								</select>
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_GLOBAL_PICKLIST} ]">
								<label for="field-global-picklist-name">Campo de lista especial</label>
{if (!empty ($AVAILABLE_GLOBAL_PICKLISTS))}
								<select id="field-global-picklist-name" name="globalpicklistname" class="form-control" disabled="disabled">
									<option value=""></option>
	{foreach $AVAILABLE_GLOBAL_PICKLISTS as $picklist}
									<option value="{$picklist->getName ()}">{$picklist->getLabel ()}</option>
	{/foreach}
								</select>
{/if}
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_CALCULATED_LINK} ]">
								<label for="field-calculation-name">Cálculo</label>
{if (!empty ($CALCULATED_SYSTEMS))}
								<select id="field-calculation-name" name="calculationname" class="form-control" disabled="disabled">
									<option value=""></option>
	{foreach $CALCULATED_SYSTEMS as $calculatedSystem}
									<option value="{$calculatedSystem->getCalculationName ()}">{$calculatedSystem->getName ()}</option>
	{/foreach}
								</select>
{/if}
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_APP} ]">
								<label for="field-calculation-name">{$MOD['LBL_HANDLER_CLASS']}</label>
								<input type="text" id="handler-class-length" name="handlerclass" class="form-control" disabled="disabled" min="1" max="255"
									   onkeydown="FieldUtils.normalizeFieldAlphabets (this, event);"/>
								<span class="help-block"><small>No admite numeros ni caracteres especiales</small></span>
							</div>
							<div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_APP} ]">
								<label for="field-calculation-name">{$MOD['LBL_HANDLER_METHOD']}</label>
								<input type="text" id="handlermethod" name="handlermethod" class="form-control" disabled="disabled" min="1" max="255"
									   onkeydown="FieldUtils.normalizeFieldAlphabets (this, event);"/>
								<span class="help-block"><small>No admite numeros ni caracteres especiales</small></span>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="field-utils-submmit" type="submit" class="btn btn-primary">Guardar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</script>
{/strip}