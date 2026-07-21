{strip}
<li class="condition list-group-item" data-id="{$CONDITION_ID}">
	<div class="row">
		<div class="col-xs-4">
			<select class="form-control advanced-filter-column" title="Columna" onchange="ReportWizardUtils.onChangeAdvancedFilterColumnHandler (this);"></select>
		</div>
		<div class="col-xs-3">
			<select class="form-control operator" title="Operador">
				<option value=""></option>
				<option value="e">igual a</option>
				<option value="n">diferente a</option>
				<option value="s" data-type="text">empieza con</option>
				<option value="ew" data-type="text">termina con</option>
				<option value="c" data-type="text">contiene</option>
				<option value="k" data-type="text">no contiene</option>
				<option value="l" data-type="number" style="display: none;">menor a</option>
				<option value="m" data-type="number" style="display: none;">menor o igual a</option>
				<option value="g" data-type="number" style="display: none;">mayor a</option>
				<option value="h" data-type="number" style="display: none;">mayor o igual a</option>
			</select>
		</div>
		<div class="col-xs-3">
			<input type="text" value="" class="form-control value" placeholder="" />
		</div>
		<div class="col-xs-1">
			<select class="form-control glue hidden" title="" disabled="disabled">
				<option value="and">y</option>
				<option value="or">o</option>
			</select>
		</div>
		<div class="col-xs-1 text-right">
			<button type="button" class="btn btn-link" onclick="ReportWizardUtils.deleteCondition (this);" title="Eliminar condición"><i class="fa fa-trash-o"></i></button>
		</div>
	</div>
</li>
{/strip}