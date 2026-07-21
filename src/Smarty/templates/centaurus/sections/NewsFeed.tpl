<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/news_feed.css" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/datepicker.css" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" />
<div id="history-bar" class="right-sidebar" style="display: none;">
	<div class="right-stat-bar" tabindex="5001" style="overflow: hidden;">
		<ul class="right-side-accordion">
			<li class="widget-collapsible">
				<div class="fixed-clear"></div>
				<button id="refresh-activity" href="#" class="head btn btn-link widget-head active clearfix" style="color: #3498db; font-weight: 300; border-radius: 3px; background: white; position: fixed; top: 65px;">
					<span class="pull-left" style="text-transform: none;"><i class="fa fa-history" style="margin-right: .5em;"></i>Actividad Reciente</span>
					<span class="pull-right widget-collapse"><i class="ico-minus"></i></span>
				</button>
				&nbsp;
				<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#recordingTimes" style="position: fixed; top: 116px; font-size: 10px;">Registrar Tiempos</button>
				<div id="cargando"><i class="fa fa-spinner fa-spin"></i></div>
				<div id="nf-no-activity"><h5>- No hay actividades -</h5></div>
				<ul id="news-feed" class="widget-container">
				</ul>
			</li>
		</ul>
	</div>
</div>
<div class="modal fade" id="recordingTimes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">¿Quiere registrar su actividad de hoy? Cuéntenos un poco…</h4>
			</div>
			<div class="modal-body">
				<div class="table-responsive">
					<table class="table" id="recordTable">
						<thead>
							<tr>
								<th class="text-center" style="width: 20%;">Actividad</th>
								<th class="text-center" style="width: 20%;">Comentario</th>
								<th class="text-center" style="width: 40%;" colspan="2">Relacionado con</th>
								<th class="text-center" style="width: 15%;">N° de horas</th>
								<th class="text-center" style="width: 5%;">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div class="input-group">
										<div class="input-group-btn">
											<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
											<ul class="dropdown-menu">
{foreach $ACTIVITIES as $activity}
												<li onclick="getValue(this.textContent, 0);"><a href="#">{$activity.name}</a></li>
{/foreach}
											</ul>
										</div>
										<input class="form-control" id="activity_0" type="text" placeholder="Nueva tarea...">
									</div>
								</td>
								<td><textarea class="form-control" rows="1" id="comment_0" placeholder=""></textarea></td>
								<td>
									<select class="form-control" id="relatedTo_0" title="">
										<option value="">Seleccione</option>
{if (!empty ($RELATED_MODULES))}
	{foreach $RELATED_MODULES as $relatedModule}
										<option value="{$relatedModule.name}">{$relatedModule.name}</option>
	{/foreach}
{/if}
									</select>
								</td>
								<td>
									<div class="form-group field-container" style="margin-bottom: 0;">
										<div class="input-group">
											<input id="relatedSpecific_0" type="hidden" class="for-filter relatedcrmid" />
											<input id="relatedSpecific_0_display" readonly="readonly" type="text" class="form-control placeholderStyle input-readonly b-right" placeholder="" />
											<div class="input-group-addon" onclick="return NewsFeedUtils.openPopup (this, 0);"><i class="fa fa-plus-circle"></i></div>
											<div class="input-group-addon" onclick="return NewsFeedUtils.clearFields (0);"><i class="fa fa-eraser"></i></div>
										</div>
									</div>
								</td>
								<td>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
										<input type="text" id="maskedTime_0" class="form-control maskedTimeCustomized" placeholder="" />
									</div>
								</td>
								<td>
									<button type="button" class="btn btn-info" onclick="addRow();">
										<i class="fa fa-plus-circle" aria-hidden="true"></i>
									</button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				<button type="button" class="btn btn-primary" onclick="saveChanges();">Guardar Cambios</button>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function(){
		jQuery("#maskedTime_0").mask("99:99:99");
	});
	function addRow() {
		var activities     = {$ACTIVITIES|@json_encode};
		var relatedModules = {$RELATED_MODULES|@json_encode};
		var numRow         = jQuery("#recordTable >tbody >tr:visible:gt(0)").length + 1;
		jQuery("#recordTable").append(
			jQuery('<tr>').append(
				jQuery('<td>').append('<div class="input-group">' +
					'<div class="input-group-btn">' +
						'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>' +
						'<ul class="dropdown-menu" id="dropdown_' + numRow + '">' +
						'</ul>' +
					'</div>' +
					'<input class="form-control" id="activity_' + numRow + '" type="text" placeholder="Nueva tarea...">' +
				'</div>'),
				jQuery('<td>').append('<textarea class="form-control" rows="1" id="comment_' + numRow + '"></textarea>'),
				jQuery('<td>').append('<select class="form-control" id="relatedTo_' + numRow + '">' +
					'<option value="">Seleccione</option>' +
				'</select>'),
				jQuery('<td>').append('<div class="form-group field-container" style="margin-bottom: 0;">' +
					'<div class="input-group">' +
						'<input id="relatedSpecific_' + numRow + '" type="hidden" class="for-filter relatedcrmid">' +
						'<input id="relatedSpecific_' + numRow + '_display" readonly="readonly" type="text" class="form-control placeholderStyle input-readonly b-right">' +
						'<div class="input-group-addon" onclick="return NewsFeedUtils.openPopup (this, ' + numRow + ');">' +
							'<i class="fa fa-plus-circle"></i>' +
						'</div>' +
						'<div class="input-group-addon" onclick="return NewsFeedUtils.clearFields (' + numRow + ');">' +
							'<i class="fa fa-eraser"></i>' +
						'</div>' +
					'</div>' +
				'</div>'),
				jQuery('<td>').append('<div class="input-group">' +
					'<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>' +
					'<input type="text" class="form-control" id="maskedTime_' + numRow + '" class="maskedTimeCustomized">' +
				'</div>'),
				jQuery('<td>').append('<a href="javascript:void(0);" class="btn btn-danger" onclick="jQuery(this).parent().parent().remove();">' +
					'<i class="fa fa-times-circle"></i>')
			)
		);
		jQuery("#maskedTime_" + numRow).mask("99:99:99");
		for (var i in relatedModules) {
			if (relatedModules.hasOwnProperty(i)) {
				jQuery("#relatedTo_" + numRow).append('<option value="' + relatedModules[i].name + '">' + relatedModules[i].name + '</option>');
			}
		}
		for (var i in activities) {
			if (activities.hasOwnProperty(i)) {
				jQuery("#dropdown_" + numRow).append('<li onclick="getValue(this.textContent, ' + numRow + ');"><a href="#">' + activities[i].name + '</a></li>');
			}
		}
	}
	function saveChanges() {
		var numRow = jQuery("#recordTable >tbody >tr:visible:gt(0)").length + 1;
		var data = [];
		for (i = 0; i < numRow; i++) {
			var activity        = jQuery("#activity_" + i).val();
			var comment         = jQuery("#comment_" + i).val();
			var relatedTo       = jQuery("#relatedTo_" + i).val();
			var relatedSpecific = jQuery("#relatedSpecific_" + i).val();
			var maskedTime      = jQuery("#maskedTime_" + i).val();
			var dataIntern      = [activity, comment, relatedTo, relatedSpecific, maskedTime];
			data.push(dataIntern);
			if (!activity) {
				alert("Debe indicar una actividad.");
				return false;
			}
			if (!comment) {
				alert("Debe indicar un comentario.");
				return false;
			}
			if (!relatedTo) {
				alert("Debe indicar un registro relacionado.");
				return false;
			}
			if (!maskedTime) {
				alert("Debe indicar un número de horas.");
				return false;
			}
		}
		jQuery.post("index.php?module=Home&action=recordingTimes&Ajax=true", {
			data: data
		});
		jQuery('#recordingTimes').modal('hide');
	}
	function getValue(value, id){
		jQuery('#activity_' + id).val(value);
	}
	function openPopup (buttonElement, row) {
		var button = jQuery (buttonElement),
			moduleNameElement = jQuery ('#relatedTo_' + row),
			moduleName = moduleNameElement.val (),
			relatedCrmFieldId = "relatedSpecific_" + row,
			relatedCrmDisplayFieldId = "relatedSpecific_" + row + '_display';

		if ((moduleName === undefined) || (moduleName === null) || (moduleName.trim () === '')) {
			alert ('Selecciona el módulo');
			moduleNameElement.focus ();
			return false;
		}

		button.attr ('data-current-module', '');
		button.attr ('data-display-field-id', relatedCrmDisplayFieldId);
		button.attr ('data-field-id', relatedCrmFieldId);
		button.attr ('data-referenced-module', moduleName);
		button.attr ('data-title', 'Relacionado con');

		RelatedModuleModalUtils.openModal (buttonElement);
	}
	function clearFields (row) {
		jQuery ('#relatedSpecific_' + row).val('');
		jQuery ('#relatedSpecific_' + row + '_display').val('');
	}
	window.NewsFeedUtils = {
		clearFields: clearFields,
		openPopup:   openPopup
	};
</script>
<script src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<script src="themes/{$THEME}/js/moment.js"></script>
