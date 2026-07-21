{strip}
<div class="condition-group-container">
<div class="condition-group list-group" data-id="{$GROUP_ID}">
	<div class="condition-group-header list-group-item">
		<div class="row">
			<div class="col-xs-4">Columna</div>
			<div class="col-xs-2">Operador</div>
			<div class="col-xs-4">Valor</div>
			<div class="col-xs-1"></div>
			<div class="col-xs-1 text-right">
				<button type="button" class="btn btn-link" onclick="ReportWizardUtils.deleteConditionGroup (this);" title="Eliminar grupo de condiciones"><i class="fa fa-trash-o"></i></button>
			</div>
		</div>
	</div>
	<div class="condition-group-body list-group-item">
		<ul class="list-group conditions"></ul>
	</div>
	<div class="condition-group-footer list-group-item">
		<div class="row text-center">
			<button type="button" class="btn btn-link" onclick="ReportWizardUtils.addCondition (this);" title="Agregar condición"><i class="fa fa-plus"></i></button>
		</div>
	</div>
</div>
<div class="condition-group-glue">
	<select class="form-control glue hidden" title="" disabled="disabled">
		<option value="and">y</option>
		<option value="or">o</option>
	</select>
</div>
</div>
{/strip}