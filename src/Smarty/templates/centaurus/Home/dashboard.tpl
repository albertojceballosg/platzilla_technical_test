<link rel="stylesheet" href="themes/{$THEME}/css/libs/datepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" type="text/css" />
<style>
	.img img {
		width:  105px;
		height: 105px;
	}
	.my-font {
		font-size: 10px;
	}
	.bg {
		background-clip: padding-box;
		border-radius:   50%;
		border:          5px solid #fff;
		left:            0;
		margin-left:     auto;
		margin-right:    auto;
		right:           0;
	}
	.profile-box-contact .profile-box-header {
		background-clip: padding-box;
		border-radius:   3px 3px 0 0;
		color:           #fff;
		padding:         10px 4px;
	}
	.profile-box-contact .profile-box-footer a {
		color:      #ffffff;
		display:    block;
		float:      none;
		padding:    5px 5px;
		text-align: center;
		width:      100%;
	}
</style>
{if (!empty ($NOTIFICATIONS))}
	{foreach $NOTIFICATIONS as $index => $notification}
        {if $index >= 1}
            {$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":'hidden'|unescape:"html"}
        {else}
            {$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":''|unescape:"html"}
        {/if}

	{/foreach}
<script type="text/javascript">
	(function (jQuery) {
		jQuery ('.notification').on ('closed.bs.alert', function () {
			var notificationId = jQuery (this).attr ('data-id'),
				arguments      = [
					'module=notifications',
					'action=Disable',
					'record=' + encodeURIComponent (notificationId),
					'Ajax=true'
				];
			jQuery.ajax ('index.php', {
				data:     arguments.join ('&'),
				dataType: 'text',
				method:   'post'
			}).done (function () {
				jQuery ('.notification.hidden:first').removeClass ('hidden');
			});
		});
	} (jQuery));
</script>
{/if}
<div class="row">
	<div class="col-lg-12">
		<h1>Actividad de {$COMPANY.organizationname}</h1>
	</div>
</div>
<div class="row">
{foreach $USERS as $user}
	<div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">
		<div class="main-box clearfix profile-box-contact">
			<div class="main-box-body clearfix">
				<div class="profile-box-header gray-bg clearfix">
					<div class="row img">
						<img src="{if $user.profileimage}{$user.profileimage}{else}themes/centaurus/img/photo.png{/if}" class="img-responsive bg">
					</div>
					<div class="row">
						<h5 class="text-center">{$user.first_name}</h5>
						<h5 class="text-center">{$user.last_name}</h5>
					</div>
				</div>
				<div class="profile-box-footer">
					<div class="row">
						<div class="col-xs-offset-1 col-xs-10">
							<a href="#anchor" class="btn btn-primary btn-block my-font" onclick="massDelete('{$user.last_name}');">ACTIVIDADES</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/foreach}
</div>
<a name="anchor"></a>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box no-header clearfix">
			<div class="main-box-body clearfix">
				<div class="row">
					<div class="form-group col-xs-12 col-md-5">
						<label for="from">Desde:</label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input class="form-control" id="from" name="from" type="text">
						</div>
					</div>
					<div class="form-group col-xs-12 col-md-5">
						<label for="to">Hasta:</label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input class="form-control" id="to" name="to" type="text">
						</div>
					</div>
					<div class="form-group col-xs-12 col-md-2">
						<label for="search">&nbsp;</label>
						<div class="input-group">
							<button type="button" class="btn btn-primary btn-block" id="search" onclick="filterByDate();">Buscar</button>
						</div>
					</div>
				</div>
				<div class="table-responsive">
					<table class="table user-list table-hover" id="example">
						<thead>
							<tr>
								<th><span>Usuario</span></th>
								<th><span>Fecha de Registro</span></th>
								<th><span>Asunto</span></th>
								<th><span>Tipo de Actividad</span></th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody id="myTbody">
							{foreach $ACTIVITIES as $activity}
								<tr>
									<td id="user_{$activity@iteration}">
										<a href="index.php?module=Calendar&action=DetailView&record={$activity.activityid}">{$activity.first_name}&nbsp;{$activity.last_name}</a>
									</td>
									<td>{$activity.date_start}</td>
									<td id="subject_{$activity@iteration}">{$activity.subject}</td>
									<td id="tipo_{$activity@iteration}">
										<span class="label {if $activity.activitytype == 'Actividad'}label-success{elseif $activity.activitytype == 'Reunión'}label-warning{else}label-info{/if}">{$activity.activitytype}</span>
									</td>
									<td id="buttons_{$activity@iteration}" style="width: 20%;">
										{if $activity.activitytype != 'Eliminado'}
											<a class="table-link" href="index.php?module=Calendar&amp;action=EditView&amp;record={$activity.activityid}&amp;return_module=Home&amp;return_action=index">
												<span class="fa-stack">
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
												</span>
											</a>
											<a class="table-link danger" href='javascript:confirmdelete("index.php?module=Calendar&amp;action=Delete&amp;record={$activity.activityid}&amp;return_module=Home&amp;return_action=index")'>
												<span class="fa-stack">
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
												</span>
											</a>
										{/if}
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="viewRecord" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Editar Registro</h4>
			</div>
			<div class="modal-body">
				<form role="form">
					<div class="form-group hide">
						<label for="row">Fila:</label>
						<input type="text" class="form-control" id="row">
					</div>
					<div class="form-group">
						<label for="usersModal">Asociado a:</label>
						<select class="form-control" id="usersModal">
{foreach $USERS as $user}
							<option value="{$user.first_name} {$user.last_name}">{$user.first_name} {$user.last_name}</option>
{/foreach}
						</select>
					</div>
					<div class="form-group">
						<label for="subjectModal">Asunto:</label>
						<input type="text" class="form-control" id="subjectModal">
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" onclick="editRecord();">Guardar Cambios</button>
			</div>
		</div>
	</div>
</div>
{literal}
<script>
	jQuery(document).ready (function () {
		// Función para inicializar datepickers con idioma dinámico
		function initDatepickers() {
			var userLang = '{/literal}{php}echo isset($_SESSION['authenticated_user_language']) ? $_SESSION['authenticated_user_language'] : 'es_es';{/php}{literal}';
			var theme = '{/literal}{$THEME}{literal}';
			
			function initDatePicker(fieldId, format) {
				jQuery('#' + fieldId).datepicker({
					format: format,
					language: userLang,
					weekStart: 1
				});
			}
			
			// Si el idioma ya está cargado, inicializar inmediatamente
			if (jQuery.fn.datepicker.dates && jQuery.fn.datepicker.dates[userLang]) {
				initDatePicker('from', 'dd-mm-yyyy');
				initDatePicker('to', 'dd-mm-yyyy');
			} else {
				// Cargar el archivo de idioma
				var langFile = 'themes/' + theme + '/js/bootstrap-datepicker.' + userLang + '.js';
				
				jQuery.getScript(langFile)
					.done(function() {
						initDatePicker('from', 'dd-mm-yyyy');
						initDatePicker('to', 'dd-mm-yyyy');
					})
					.fail(function() {
						// Intentar con español
						jQuery.getScript('themes/' + theme + '/js/bootstrap-datepicker.es.js')
							.done(function() {
								userLang = 'es';
								initDatePicker('from', 'dd-mm-yyyy');
								initDatePicker('to', 'dd-mm-yyyy');
							})
							.fail(function() {
								// Si todo falla, inicializar sin idioma específico
								initDatePicker('from', 'dd-mm-yyyy');
								initDatePicker('to', 'dd-mm-yyyy');
							});
					});
			}
		}
		
		initDatepickers();
	});
	function editModal(id,row) {
		var activities = {/literal}{$ACTIVITIES|@json_encode}{literal};
		for (var i in activities) {
			if (activities.hasOwnProperty(i)) {
				if (activities[i].activityid == id) {
					var name = activities[i].first_name + ' ' + activities[i].last_name;
					jQuery ('#usersModal').val(name);
					jQuery ('#subjectModal').val(activities[i].subject);
				}
			}
		}
		jQuery('#row').val(row);
		jQuery('#viewRecord').modal('show');
	}
	function deleteRecord(id,row) {
		if(confirm(alert_arr.ARE_YOU_SURE_YOU_WANT_TO_DELETE)) {
			jQuery('#tipo_' + row).empty();
			jQuery('#buttons_' + row).empty();
			jQuery('#tipo_' + row).append('<span class="label label-danger">Eliminado</span>');
		}
	}
	function editRecord() {
		var row          = jQuery('#row').val();
		var usersModal   = jQuery('#usersModal').val();
		var subjectModal = jQuery('#subjectModal').val();
		var users        = {/literal}{$USERS|@json_encode}{literal};
		jQuery('#user_' + row).empty();
		jQuery('#subject_' + row).empty();
		jQuery('#user_' + row).append('<a href="#">' + usersModal + '</a>');
		jQuery('#subject_' + row).append(subjectModal);
		jQuery('#viewRecord').modal('hide');
	}
	function massDelete(param) {
		jQuery('#myTbody').empty();
		this.fillTable(param);
	}
	function fillTable(param) {
		var activities = {/literal}{$ACTIVITIES|@json_encode}{literal};
		for (var i in activities) {
			if (activities.hasOwnProperty(i)) {
				if (activities[i].last_name == param) {
					if (activities[i].activitytype == 'Actividad'){
						var labelClass = 'label-success';
					} else if (activities[i].activitytype == 'Reunión') {
						var labelClass = 'label-warning';
					} else {
						var labelClass = 'label-info';
					}
					jQuery('#myTbody').append('<tr>' +
						'<td id="user_' + i + '">' +
							'<a href="#">' + activities[i].first_name + ' ' + activities[i].last_name + '</a>' +
						'</td>' +
						'<td>' + activities[i].date_start + '</td>' +
						'<td id="subject_' + i + '">' + activities[i].subject + '</td>' +
						'<td id="tipo_' + i + '">' +
							'<span class="label ' + labelClass + '">' + activities[i].activitytype + '</span>' +
						'</td>' +
						'<td>' +
							'<a href="#">' + activities[i].activityid + '</a>' +
						'</td>' +
						'<td id="buttons_' + i + '" style="width: 20%;">' +
							'<a class="table-link" onclick="editModal(' + activities[i].activityid + ', ' + i + ')">' +
								'<span class="fa-stack">' +
									'<i class="fa fa-square fa-stack-2x"></i>' +
									'<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>' +
								'</span>' +
							'</a>' +
							'<a class="table-link danger" onclick="deleteRecord(' + activities[i].activityid + ', ' + i + ')">' +
								'<span class="fa-stack">' +
									'<i class="fa fa-square fa-stack-2x"></i>' +
									'<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>' +
								'</span>' +
							'</a>' +
						'</td>' +
					'</tr>');
				}
			}
		}
	}
	function filterByDate() {
		var activities        = {/literal}{$ACTIVITIES|@json_encode}{literal};
		var dateFrom          = jQuery ('#from').val();
		var dateTo            = jQuery ('#to').val();
		if (!dateFrom) {
			alert("Para realizar la búsqueda es necesaria una fecha inicial.");
			return null;
		} else {
			var dateFromFormatted = moment(dateFrom,'DD-MM-YYYY').format('YYYY-MM-DD');
		}
		if (!dateTo) {
			alert("Para realizar la búsqueda es necesaria una fecha final.");
			return null;
		} else {
			var dateToFormatted = moment(dateTo,'DD-MM-YYYY').format('YYYY-MM-DD');
		}
		if (dateFromFormatted >= dateToFormatted) {
			alert("La fecha inicial debe ser anterior a la fecha final.");
			return null;
		}
		jQuery('#myTbody').empty();
		for (var i in activities) {
			if (activities.hasOwnProperty(i)) {
				if (activities[i].date_start >= dateFromFormatted && activities[i].date_start <= dateToFormatted) {
					if (activities[i].activitytype == 'Actividad'){
						var labelClass = 'label-success';
					} else if (activities[i].activitytype == 'Reunión') {
						var labelClass = 'label-warning';
					} else {
						var labelClass = 'label-info';
					}
					jQuery('#myTbody').append('<tr>' +
						'<td id="user_' + i + '">' +
							'<a href="#">' + activities[i].first_name + ' ' + activities[i].last_name + '</a>' +
						'</td>' +
						'<td>' + activities[i].date_start + '</td>' +
						'<td id="subject_' + i + '">' + activities[i].subject + '</td>' +
						'<td id="tipo_' + i + '">' +
							'<span class="label ' + labelClass + '">' + activities[i].activitytype + '</span>' +
						'</td>' +
						'<td>' +
							'<a href="#">' + activities[i].activityid + '</a>' +
						'</td>' +
						'<td id="buttons_' + i + '" style="width: 20%;">' +
							'<a class="table-link" onclick="editModal(' + activities[i].activityid + ', ' + i + ')">' +
								'<span class="fa-stack">' +
									'<i class="fa fa-square fa-stack-2x"></i>' +
									'<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>' +
								'</span>' +
							'</a>' +
							'<a class="table-link danger" onclick="deleteRecord(' + activities[i].activityid + ', ' + i + ')">' +
								'<span class="fa-stack">' +
									'<i class="fa fa-square fa-stack-2x"></i>' +
									'<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>' +
								'</span>' +
							'</a>' +
						'</td>' +
					'</tr>');
				}
			}
		}
	}
</script>
{/literal}
<script src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<script src="themes/{$THEME}/js/moment.js"></script>
