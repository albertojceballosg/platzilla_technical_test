{strip}
	<script type="text/javascript">
		(function() {
			var smartyNumberFormat = '{$NUMBERING_FORMAT|default:'AMERICAN_FORMAT'}';
			var smartyDateFormat = '{$USER_DATE_FORMAT|default:'yyyy-mm-dd'}';
			window.gUserNumberFormat = smartyNumberFormat;
			window.gUserDateFormat = window.gUserDateFormat || smartyDateFormat;
		})();
	</script>
	{math equation= rand() assign= "idCalendar"}
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/select2.css" type="text/css" />
	<style type="text/css">
		@media (max-width: 768px) {
			.outside-width-height {
				height: 20%;
				margin-bottom: 5px;
				width: 100%;
			}

			.inside-width {
				border-radius: 0 0 0 0;
				width: 100%;
			}

			.caret {
				left: 90%;
				position: absolute;
				top: 45%;
			}

			.btn {
				border-radius: 0 0 0 0;
			}

			.wizard-cancel {
				font-size: 10px;
				padding: 2px 2px;
			}

			.wizard-back {
				font-size: 10px;
				padding: 2px 2px;
			}

			.wizard-next {
				font-size: 10px;
				padding: 2px 2px;
			}

			.wizard-buttons-container {
				padding: 5px;
			}
		}
	</style>
	{if (isset ($MESSAGE))}
		<div class="alert alert-{if (!$IS_ERROR)}success{else}danger{/if}">
			<i class="fa fa-{if (!$IS_ERROR)}check{else}times{/if}-circle fa-fw fa-lg"></i>
			<strong>{if (!$IS_ERROR)}Listo{else}Error{/if}!</strong> {$MESSAGE}
		</div>
	{/if}
	{if (!empty ($NOTIFICATIONS))}
		{foreach $NOTIFICATIONS as $index => $notification}
			<div class="alert alert-dismissable notification{if ($index > 1)} hidden{/if}" data-id="{$notification->getId ()}"
				style="background-color: #ffffff;">
				<button type="button" class="close notification-close" data-dismiss="alert" aria-label="close">&times;</button>
				<div>{$notification->getContents ()|unescape:"html"}</div>
			</div>
		{/foreach}
		<script type="text/javascript">
			(function(jQuery) {
				jQuery('.notification').on('closed.bs.alert', function() {
					var notificationId = jQuery(this).attr('data-id'),
						arguments = [
							'module=notifications',
							'action=Disable',
							'record=' + encodeURIComponent(notificationId),
							'Ajax=true'
						];
					jQuery.ajax('index.php', {
						data: arguments.join('&'),
						dataType: 'json',
						method: 'post'
					}).done(function() {
						jQuery('.notification.hidden:first').removeClass('hidden');
					});
				});
			}(jQuery));
		</script>
	{/if}
	<div class="col-lg-12">
		<div class="row">
			<div class="col-lg-12">
				<h1>{$APP.Calendar}</h1>
			</div>
		</div>
		<div class="main-box no-header clearfix">
			<div class="main-box-body clearfix pull-right">
				<form action="index.php" method="get" name="formCalendar" id="formCalendar">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="index" />
					<input type="hidden" id="activity_type" name="activity_type" value="{$ACTIVITYTYPESELECTED}" />
					<input type="hidden" id="userid" name="userid" value="{$USERIDSELECTED}" />
					<input type="hidden" id="user" name="user" value="{$USERSELECTED}" />
				</form>
				<div class="row">
					<div class="btn-group outside-width-height">
						<button type="button" class="btn btn-primary btn-sm inside-width" style="margin-left: 5px;"
							data-toggle="dropdown">{if $ACTIVITYTYPESELECTED eq ''}Tipo de
							Tarea{else}{$ACTIVITYTYPE.$ACTIVITYTYPESELECTED}
							{/if} <span class="caret"></span></button>
						<ul class="dropdown-menu inside-width" role="menu">
							{if $ACTIVITYTYPESELECTED neq '' }
								<li><a href="index.php?module={$MODULE}&action=index"> Todos </a></li>
							{/if}
							{foreach key=type item=typeAct from=$ACTIVITYTYPE}
								<li><a onclick="asignaValorCampo('activity_type','{$type}');submitForm();">{$typeAct}</a></li>
							{/foreach}
						</ul>
					</div>
					<div class="btn-group outside-width-height">
						<button type="button" class="btn btn-primary btn-sm inside-width" style="margin-left: 5px;"
							data-toggle="dropdown">{if $USERSELECTED eq ''}Usuarios{else}{$USERSELECTED}{/if} <span
								class="caret"></span></button>
						<ul class="dropdown-menu inside-width" role="menu">
							{if $USERSELECTED neq '' }
								<li><a href="index.php?module={$MODULE}&action=index"> Todos </a></li>
							{/if}
							{foreach $AVAILABLE_USERS as $userId => $userData}
								<li>
									<a
										onclick="asignaValorCampo('userid','{$userId}');asignaValorCampo('user','{$userData.name}');submitForm();">{$userData.name}</a>
								</li>
							{/foreach}
						</ul>
					</div>&nbsp;
					{if (isset ($CALENDAR_VIEWS)) && ($CALENDAR_VIEWS neq NULL)}
						{assign var="viewsModule" value=array_keys($CALENDAR_VIEWS)}
						{* Si estamos en Calendar y existe Calendar en las vistas, usarlo como default *}
						{if $MODULE eq 'Calendar' && isset($CALENDAR_VIEWS['Calendar'])}
							{assign var="defaultModule" value='Calendar'}
						{elseif $MODULE eq 'Calendar'}
							{* Estamos en Calendar pero no hay vistas para Calendar - no mostrar ningún módulo por defecto *}
							{assign var="defaultModule" value=''}
						{else}
							{assign var="defaultModule" value=$viewsModule[0]}
						{/if}
						{if $defaultModule neq ''}
							{assign var="moduleLabel" value=$CALENDAR_VIEWS[$defaultModule][0]['tablabel']}
						{else}
							{assign var="moduleLabel" value=''}
						{/if}
						<div class="btn-group" style="margin-right: 5px;">
							<button type="button" class="btn btn-default btn-sm" data-toggle="dropdown">Calendarios por
								módulos&nbsp;<span class="caret"></span></button>
							<ul class="dropdown-menu" role="menu">
								{foreach $CALENDAR_VIEWS as $keyModule => $views}
									<li class="{if $keyModule eq $defaultModule}active{/if}">
										<a href="#"
											onclick="ActivityUtils.viewModule (event, this,'{$keyModule}', '{$idCalendar}')">{$views[0]['tablabel']}</a>
									</li>
								{/foreach}
							</ul>
						</div>
						<div class="btn-group" style="margin-right: 5px;{if $defaultModule eq ''} display: none;{/if}"
							id="views-btn-group-{$idCalendar}">
							<button id="btn-{$idCalendar}" type="button" class="btn btn-info btn-sm" data-toggle="dropdown">
								Vistas de {$moduleLabel}&nbsp;<span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu" id="rules-{$idCalendar}">
								{foreach $CALENDAR_VIEWS as $keyModule => $views}
									{foreach $views as $view}
										<li
											class="divider {$keyModule}-{$idCalendar} {if $defaultModule eq '' || $keyModule neq $defaultModule}hide{/if}">
										</li>
										<li class="list-btn-header {$keyModule}-{$idCalendar} {if $defaultModule eq '' || $keyModule neq $defaultModule}hide{/if}"
											title="{$view['tablabel']}" style="text-align: center!important;">
											<small>{$view['label']}</small>
										</li>
										<li
											class="divider {$keyModule}-{$idCalendar} {if $defaultModule eq '' || $keyModule neq $defaultModule}hide{/if}">
										</li>
										<li
											class="{$keyModule}-{$idCalendar} {if $defaultModule eq '' || $keyModule neq $defaultModule}hide{/if}">
											<a href="index.php?module={$keyModule}&amp;action=CalendarView&amp;record={$view['calendarviewid']}"
												title="Todos los registros sin reglas">Todos los registros de&nbsp;{$view['label']}</a>
										</li>
										{foreach $view['rules'] as $rule}
											<li
												class="{$keyModule}-{$idCalendar} {if $defaultModule eq '' || $keyModule neq $defaultModule}hide{/if}">
												<a href="index.php?module={$keyModule}&amp;action=CalendarView&amp;record={$view['calendarviewid']}&amp;rule={$rule.ruleId}"
													title="{$rule.title}">{$rule.option}</a>
											</li>
										{/foreach}
									{/foreach}
								{/foreach}
							</ul>
						</div>
					{/if}
					{* Botón "Lista de Tareas" ocultado - redirige al Home que ya es accesible de otras formas *}
					{*<a href="index.php?module=Home&action=index&tab=ACTIVITY_REPORT" id="" class="btn btn-default btn-sm outside-width-height" style="margin-left: 5px;">Lista de Tareas</a>&nbsp;*}
					{*<button id="open-wizard" class="btn btn-primary btn-sm outside-width-height" style="margin-left: 5px;" onclick="CalendarWizard.open ();">Crear Tarea</button>*}
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="main-box">
				<div class="main-box-body clearfix">
					<div class="fc fc-ltr" id="calendar-{$idCalendar}"></div>
				</div>
			</div>
		</div>
	</div>
	{assign var="lastId" value=null}
	{foreach item=tarea key=count from=$DATA}
		{if $tarea.activityid eq $lastId} {continue}{else}{assign var="lastId" value=$tarea.activityid}{/if}

		<div class="modal fade" id="myModal-{$tarea.activityid}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
			aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">{$tarea.activitytype}</h4>
					</div>
					<div class="modal-body">
						<div class="row form-group" style="margin-bottom: 10px;">
							<div class="col-xs-12 col-md-3 text-right" style="padding-bottom: 6px; padding-top: 6px;">Asunto:
							</div>
							<div class="col-xs-12 col-md-9">
								<span class="form-control">{$tarea.subject}</span>
							</div>
						</div>
						{if (!empty ($tarea.description))}
							<div class="row form-group" style="margin-bottom: 10px;">
								<div class="col-xs-12 col-md-3 text-right" style="padding-bottom: 6px; padding-top: 6px;">
									Descripción:</div>
								<div class="col-xs-12 col-md-9">
									<span class="form-control">{$tarea.description}</span>
								</div>
							</div>
						{/if}
						<div class="row form-group" style="margin-bottom: 10px;">
							<div class="col-xs-12 col-md-3 text-right" style="padding-bottom: 6px; padding-top: 6px;">Tipo de
								actividad:</div>
							<div class="col-xs-12 col-md-9" style="padding-bottom: 6px; padding-top: 6px;">
								<span class="label {$tarea.class_name}" style="font-size: 1.5rem;">{$tarea.activitytype}</span>
							</div>
						</div>
						<div class="row form-group" style="margin-bottom: 10px;">
							<div class="col-xs-12 col-md-3 text-right" style="padding-bottom: 6px; padding-top: 6px;">Inicio:
							</div>
							<div class="col-xs-12 col-md-4">
								<span class="form-control">{$tarea.date_start|date_format: 'd/m/Y'}
									{$tarea.time_start|date_format: 'h:i:s a'}</span>
							</div>
							<div class="col-xs-12 col-md-1 text-right" style="padding-bottom: 6px; padding-top: 6px;">Fin:</div>
							<div class="col-xs-12 col-md-4">
								<span class="form-control">{$tarea.due_date|date_format: 'd/m/Y'}
									{$tarea.time_end|date_format: 'h:i:s a'}</span>
							</div>
						</div>
						<div class="row form-group" style="margin-bottom: 10px;">
							<div class="col-xs-12 col-md-3 text-right" style="padding-bottom: 6px; padding-top: 6px;">Creado
								por:</div>
							<div class="col-xs-12 col-md-9">
								<span class="form-control">{$tarea.creatorfirstname} {$tarea.creatorlastname}</span>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-xs-12 col-md-3 text-right" style="padding-bottom: 6px; padding-top: 6px;">Asignado
								a:</div>
							<div class="col-xs-12 col-md-9">
								<span class="form-control">{if (!empty ($tarea.last_name))}{$tarea.first_name}
									{$tarea.last_name}{else}{$tarea.groupname}
									{/if}</span>
							</div>
						</div>
						{assign var="urlRedirect" value=null}
						{if (!empty ($tarea.relatedentities))}
							{assign var="urlRedirect" value=null}
							<div class="form-group">
								<h4>Entidades relacionadas</h4>
								<div class="table-responsive" style="max-height: 10em;">
									<table class="table">
										<tbody>
											{foreach $tarea.relatedentities as $relatedEntity}
												<tr>
													<td>{$relatedEntity.tablabel}</td>
													<td>{$relatedEntity.label_entity}</td>
													{assign var="urlRedirect" value=$relatedEntity.url_redirect}
													{assign var="nameRedirect" value='Esta pagina sera redirigida a: '|cat:$relatedEntity.tablabel|cat:' '|cat:$relatedEntity.tablabel}
												</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						{/if}
						<p class="text-right">
							{* eliminada la opcion de editar y eliminar por Solicitud de David.
					<a href="index.php?module=Calendar&action=EditView&record={$tarea.activityid}&return_module=Calendar&return_action=index"><i class="fa fa-edit" style="padding-right: 20px;"></i></a>
					<a href='javascript:confirmdelete("index.php?module=Calendar&action=Delete&record={$tarea.activityid}&return_module=Calendar&return_action=index")'><i class="fa fa-trash-o" style="padding-right: 5px;"></i></a>
					*}
						</p>
					</div>
				</div>
			</div>
			{if (!empty ($tarea.relatedentities) && !empty ($urlRedirect))}
				<script type="text/javascript">
					{literal}
						jQuery ('#myModal-{/literal}{$tarea.activityid}{literal}').on('show.bs.modal', function (e) {
						window.open('{/literal}{$urlRedirect}{literal}', '_blank');
						jQuery(this).hide();
						});
					{/literal}
				</script>
			{/if}
		</div>
	{/foreach}
	<div class="modal fade" id="activity-modal-{$idCalendar}" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document"
			style="width: {$MODAL_DIMENSIONS['width']|default:'850px'}; max-width: 95%;">
			<div class="modal-content" style="height: {$MODAL_DIMENSIONS['height']|default:'auto'};">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">{if $FLMODULE eq 'orden_de_trabajo'}Nueva tarea{else}Nueva Acción{/if}</h4>
				</div>
				<div class="modal-body">
					{include file='Home/ActionTabs/ActivityModal.tpl'}
				</div>
				<div class="modal-footer">
					<input type="button" value="Guardar" id="task-create-btn-{$idCalendar}"
						class="btn btn-primary activity-modal-btn add_button">
					<button type="button" class="btn btn-default activity-modal-btn" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
	{*$DATA[0]|var_dump*}
	{*
<script type="text/javascript" src="themes/{$THEME}/js/jquery-ui.custom.min.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/fullcalendar.js"></script>*}
	<script type="text/javascript" src="themes/{$THEME}/js/select2.min.js"></script>
	<script type="text/javascript" src="modules/Calendar/Calendar.js"></script>
	<script type="text/javascript" src="include/js/related-module-modal.js?v=1.1"></script>
	<script type="text/javascript">
		{literal}
			jQuery(document).ready(function() {
				CalendarManager.init({
					currentModule: '{/literal}{$MODULE}{literal}',
					currentViewId: '{/literal}{$idCalendar}{literal}',
					type: '{/literal}{$CALENDAR_TYPE}{literal}',
					currentLangCode: 'es',
					events: {/literal}{$DATA|json_encode}{literal}

				});
			});
		{/literal}
	</script>
	{include file='CreateTaskWizard.tpl'}
{/strip}