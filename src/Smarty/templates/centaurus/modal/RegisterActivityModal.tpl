{strip}
<script type="text/html" id="record-activity-modal-template">
<div class="modal fade" id="record-activity-modal" tabindex="-1" role="dialog" aria-hidden="false">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form method="post" action="index.php" onsubmit="RecordActivityUtils.saveActivities (this); return false;">
				<input type="hidden" name="module" value="Home" />
				<input type="hidden" name="action" value="RecordActivity" />
				<input type="hidden" name="Ajax" value="true" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title">Registrar actividad</h4>
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table class="table">
							<thead>
							<tr>
								<th class="text-center" style="width: 25%;">Actividad</th>
								<th class="text-center" style="width: 20%;">Inicio</th>
								<th class="text-center" style="width: 20%;">Fin</th>
								<th class="text-center" style="width: 30%;">¿Relacionar con registro?</th>
								<th class="text-center" style="width: 5%;">&nbsp;</th>
							</tr>
							</thead>
							<tbody></tbody>
							<tfoot>
							<tr>
								<td colspan="5" class="text-center">
									<button type="button" class="btn btn-default" onclick="RecordActivityUtils.addRow (this);"><i class="fa fa-plus"></i></button>
								</td>
							</tr>
							</tfoot>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Guardar</button>
				</div>
			</form>
		</div>
	</div>
</div>
</script>
<script type="text/html" id="record-activity-modal-row-template">
<tr class="activity-row">
	<td style="vertical-align: top;">
		<input type="text" class="form-control activity-name" placeholder="Nueva tarea..." style="margin-bottom: 0.5em;" />
		<textarea class="form-control activity-comment" rows="1" placeholder="Comentario"></textarea>
	</td>
	<td style="vertical-align: top;">
		<div class="input-group" style="margin-bottom: 0.5em;">
			<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
			<input type="text" class="form-control activity-start-date" placeholder="" />
		</div>
		<div class="input-group bootstrap-timepicker timepicker">
			<input type="text" class="form-control activity-start-time" placeholder="" />
			<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
		</div>
	</td>
	<td style="vertical-align: top;">
		<div class="input-group" style="margin-bottom: 0.5em;">
			<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
			<input type="text" class="form-control activity-end-date" placeholder="" />
		</div>
		<div class="input-group bootstrap-timepicker timepicker">
			<input type="text" class="form-control activity-end-time" placeholder="" />
			<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
		</div>
	</td>
	<td style="vertical-align: top;">
		<select class="form-control activity-related-module-name" title="" style="margin-bottom: 0.5em;">
			<option value="">Selecciona el módulo</option>
{if (!empty ($AVAILABLE_MODULES))}
	{foreach $AVAILABLE_MODULES as $availableModule}
			<option value="{$availableModule->getName ()}">{$availableModule->getLabel ()}</option>
	{/foreach}
{/if}
		</select>
		<div class="form-group field-container" style="margin-bottom: 0;">
			<div class="input-group">
				<input type="hidden" class="for-filter data-field relatedcrmid" />
				<input type="text" class="form-control placeholderStyle input-readonly b-right display-field" readonly="readonly" placeholder="Registro relacionado" />
				<div class="input-group-addon" onclick="return RecordActivityUtils.openRelatedModuleModal (this);"><i class="fa fa-plus-circle"></i></div>
				<div class="input-group-addon" onclick="return RecordActivityUtils.clearRelatedModuleFields (this);"><i class="fa fa-eraser"></i></div>
			</div>
		</div>
	</td>
	<td style="vertical-align: top;">
		<button type="button" class="btn btn-danger" onclick="RecordActivityUtils.deleteRow (this);"><i class="fa fa-trash-o"></i></button>
	</td>
</tr>
</script>
<script type="text/javascript" src="modules/Home/record-activity-utils.js?v=1.2"></script>
{/strip}