{strip}
{if (isset ($NOTIFICATION))}
    {assign var='notificationModuleFilter' value=$NOTIFICATION->getFilter()->getModuleFilter ()}
    {assign var='notificationScope' value=$NOTIFICATION->getScope ()}
	{assign var='notificationContents' value=$NOTIFICATION->getContents ()}
	{assign var='notificationDescription' value=$NOTIFICATION->getDescription ()}
	{assign var='notificationEvent' value=$NOTIFICATION->getEvent ()}
	{assign var='notificationEventParameter' value=$NOTIFICATION->getEventParameter ()}
	{assign var='notificationModuleNames' value=$NOTIFICATION->getModuleNames ()}
	{assign var='notificationName' value=$NOTIFICATION->getName ()}
	{assign var='notificationStatus' value=$NOTIFICATION->getStatus ()}
	{assign var='notificationType' value=$NOTIFICATION->getStyle ()}
    {assign var='notificationUsersFilter' value=$NOTIFICATION->getFilter()->getUsersFilter ()}
    {assign var='notificationView' value=$NOTIFICATION->getView ()}
    {assign var='notificationAction' value=$NOTIFICATION->getAction ()}
    {assign var='notificationModal' value=$NOTIFICATION->getModal ()}
    {assign var='notificationEmail' value=$NOTIFICATION->getSendByEmail ()}
    {if $notificationModal neq NULL}
        {if $notificationModal->getCustomButton () neq NULL}
            {assign var='buttonsSelected' value=$notificationModal->getButtonLinks ()}
        {else}
            {assign var='buttonsSelected' value=NULL}
        {/if}
        {if $notificationModal->getInputText () neq NULL}
            {assign var='inputText' value=$notificationModal->getInputText ()}
        {else}
            {assign var='inputText' value="<p style='text-align:center'>Escriba el mensaje de entrada aquí</p>"}
        {/if}
        {if $notificationModal->getExitText () neq NULL}
            {assign var='exitText' value=$notificationModal->getExitText ()}
        {else}
            {assign var='exitText' value="<p style='text-align:center'>Escriba el mensaje de despedida aquí</p>"}
        {/if}
    {else}
        {assign var='inputText' value="<p style='text-align:center'>Escriba el mensaje de entrada aquí</p>"}
        {assign var='exitText' value="<p style='text-align:center'>Escriba el mensaje de despedida aquí</p>"}
    {/if}
    {if preg_match("/__COLLAPSE_IN__/", $notificationContents)}
        {assign var='notificationstyle' value = 'EXPANDABLE'}
	{else}
        {assign var='notificationstyle' value = 'SIMPLE'}
    {/if}

    {assign var='notificationArryFilter' value=$NOTIFICATION->getFilter()->getAdvancedFilter ()}
    {assign var='notificationColumnPeriod' value=$NOTIFICATION->getFilter()->getColumnPeriod ()}
    {assign var='notificationFilterPeriod' value=$NOTIFICATION->getFilter()->getFilterPeriod ()}
    {assign var='notificationStandardFilter' value=$NOTIFICATION->getFilter()->getStandardFilter ()|json_decode:true}

{else}
	{assign var='notificationContents' value=null}
	{assign var='notificationDescription' value=null}
	{assign var='notificationEvent' value=null}
	{assign var='notificationEventParameter' value=null}
	{assign var='notificationModuleNames' value=array()}
	{assign var='notificationName' value=null}
	{assign var='notificationStatus' value=null}
	{assign var='notificationType' value=null}
    {assign var='inputText' value="<p style='text-align:center'>Escriba el mensaje de entrada aquí</p>"}
    {assign var='exitText' value="<p style='text-align:center'>Escriba el mensaje de despedida aquí</p>"}
    {assign var='buttonsSelected' value=NULL}
    {assign var='notificationEmail' value=NULL}
    {assign var='notificationView' value=null}
    {assign var='notificationAction' value=null}
    {assign var='notificationstyle' value='SIMPLE'}
    {assign var='notificationModuleFilter' value=null}
    {assign var='notificationScope' value=null}
    {assign var='notificationUsersFilter' value=null}
    {assign var='notificationModal' value=null}
    {assign var='notificationArryFilter' value=array()}
    {assign var='notificationColumnPeriod' value=null}
    {assign var='notificationFilterPeriod' value=null}
    {assign var='notificationStandardFilter' value=array()}

{/if}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/wizard.css" />
<style type="text/css">
	label {
		font-size: 1.11em;
		font-weight: 300;
	}
	.btn {
		margin-left: 5px;
	}
	.main-box > .main-box-header {
		padding-bottom: 2px;
		padding-top: 2px;
	}
	.required {
		color: #ff0000;
	}
	.wizard .actions a {
		margin-right: 5px !important;
		font-size: 14px !important;
		line-height: 20px !important;
	}
	.wizard .steps li {
		font-size: 14px !important;
		padding-left: 11px !important;
		padding-right: 5px !important;

	}
	.wizard .steps span {
		margin-left: 5px !important;
	}
	.step-pane {
		display: none;
	}
	.step-pane.active {
		display: block;
	}
	.wizard .steps li {
		cursor: pointer;
		position: relative;
		background: #f5f5f5;
		border-right: 1px solid #ddd;
	}
	.wizard .steps li:first-child {
		border-left: 1px solid #ddd;
	}
	.wizard .steps li.complete {
		color: #5cb85c;
		background: #d4edda;
	}
	.wizard .steps li.complete .badge {
		background-color: #5cb85c;
	}
	.wizard .steps li.active {
		background: #cce7ff;
		color: #337ab7;
	}
	.wizard .steps li.active .badge {
		background-color: #337ab7;
	}
	.wizard .steps li .chevron {
		position: absolute;
		right: -10px;
		top: 50%;
		transform: translateY(-50%);
		width: 0;
		height: 0;
		border-left: 10px solid #f5f5f5;
		border-top: 20px solid transparent;
		border-bottom: 20px solid transparent;
		z-index: 1;
	}
	.wizard .steps li.active .chevron {
		border-left-color: #cce7ff;
	}
	.wizard .steps li.complete .chevron {
		border-left-color: #d4edda;
	}
	/* Clean wizard - let bootstrap-wizard.js handle everything - v2.0 */
</style>
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=notifications&action=ListView&parenttab=Settings">{$MOD['notifications']}</a></h1>
		</div>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
			<div class="col-xs-12">
				<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
					<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
				</div>
			</div>
        {/if}
		<div class="col-md-12">
			<div class="main-box clearfix" style="margin-bottom: -1px;">
				<div class="wizard main-box-body clearfix" id="myWizard" style="margin-top: 15px;">
					<div class="wizard-inner">
						<ul id="steps" class="steps" style="z-index: 5">
							<li data-target="#step1" class="active">
								<span class="badge badge-primary">1</span>
								{$MOD['NAV_STEP1']}
								<span class="chevron"></span>
							</li>
							<li data-target="#step2">
								<span class="badge">2</span>
								{$MOD['NAV_STEP2']}
								<span class="chevron"></span>
							</li>
							<li data-target="#step3">
								<span class="badge">3</span>
								{$MOD['NAV_STEP3']}
								<span class="chevron"></span>
							</li>
						</ul>
						<div class="actions" style="z-index: 0;">
							<button id="wizard-save-notify" type="submit" class="btn btn-info">{$MOD['LBL_BTN_SAVE']}</button>
							<a href="index.php?module=notifications&action=ListView&parenttab=Settings" class="btn btn-warning">{$MOD['LBL_BTN_CANCEL']}</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<div>
				<form id="form-create-notify" method="post" action="index.php" onsubmit="return NotificationUtils.createNotification (this);">
					<input type="hidden" name="module" value="notifications" />
					<input type="hidden" name="action" value="Save" />
                    {if (isset ($RECORD))}
						<input type="hidden" name="record" value="{$RECORD}" />
                    {/if}
				<div class="row">
					<div id="step1" class="step-pane active">
						<fieldset>
							<legend style="display: none">{$MOD['NAV_STEP1']}</legend>
							<div class="col-xs-12">
							<div class="main-box">
								<div class="main-box-body">
									<div class="row data-section">

										<!-- Info box for notification types - appears when a type is selected -->
										<div id="notification-type-info" class="col-md-12" style="display: none; margin-bottom: 15px;">
											<div class="alert alert-info" style="margin-bottom: 0; padding: 10px 15px; border-left: 4px solid #5bc0de;">
												<i class="fa fa-info-circle"></i> <span id="notification-type-info-text"></span>
											</div>
										</div>

										<div class="col-md-12">
											<div class="row">
												<!-- Tipo (PRIMERO: decidir qué mostrar) -->
												<div id="dv-n-notification-type" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="notification-type">{$MOD['LBL_STYLE']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($AVAILABLE_STYLE))}
																<select id="notification-type" name="notificationtype" class="form-control" onchange="NotificationUtils.selectedType (this)">
																	<option value="">Seleccionar el tipo de notificación</option>
                                                                    {foreach $AVAILABLE_STYLE as $style}
																		<option value="{$style}"{if ($notificationType eq $style)} selected="selected"{/if}>{$MOD[$style]}</option>
                                                                    {/foreach}
																</select>
																<span id="sp-n-notification-type"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>
												<!-- Modulo (SEGUNDO: decidir dónde mostrar) -->
												<div id="dv-n-module-name" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="module-names">{$MOD['LBL_MODULE']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($AVAILABLE_MODULES))}
																<select id="module-name" name="modulename" class="form-control" onchange="NotificationUtils.getModuleColumns (this)">
																	<option value="">Seleccionar un módulo</option>
                                                                    {if !$IS_INSTANCE}
                                                                        <option value="Users" {if "Users" eq $notificationModuleFilter} selected="selected"{/if}>Todos los módulos</option>
                                                                    {/if}
                                                                    {foreach $AVAILABLE_MODULES as $module}
																		{if $module.name eq 'Home'}{continue}{/if}
																		<option value="{$module.name}"{if $module.name eq $notificationModuleFilter} selected="selected"{/if}>{$module.tablabel}</option>
                                                                    {/foreach}
																</select>
																<span id="sp-n-module-name"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="col-md-12">
											<div class="row">
												<!-- Para -->
												<div id="dv-n-notification-from" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="notification-form">{$MOD['LBL_FROM']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($AVAILABLE_FROM))}
																<select id="notification-from" name="notificationsfrom" class="form-control" onchange="NotificationUtils.setAmbit (this);">
																	<option value="">Seleccionar el ámbito</option>
                                                                    {foreach $AVAILABLE_FROM as $from}
																		{if $IS_INSTANCE && $from eq 'SYSTEM'} {continue}{/if}
																		<option value="{$from}"{if ($notificationScope == $from)} selected="selected"{/if}>{$MOD[$from]}</option>
                                                                    {/foreach}
																</select>
																<span id="sp-n-notification-from"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>
												<!-- Para -->
												<!-- Eventos -->
												<div id="dv-n-event"  class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="event">{$MOD['LBL_EVENT']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($AVAILABLE_EVENTS))}
																<select id="event" name="event" class="form-control" onchange="NotificationUtils.setEvent (this);">
																	<option value="">Seleccionar un evento</option>
                                                                    {foreach $AVAILABLE_EVENTS as $event}
																		<option value="{$event}"{if ($notificationEvent == $event)} selected="selected"{/if}>{$MOD[$event]}</option>
                                                                    {/foreach}
																</select>
																<span id="sp-n-event"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>
											</div>
											<!-- Eventos -->
										</div>
										<!-- Parámetros -->
										<div class="col-md-12">
											<div class="row">
												<!-- Espacio  -->
												<div class="col-md-6">
													<div class="col-md-4">

													</div>
													<div class="form-group col-md-8 field-container">

													</div>
												</div>
												<!-- Parámetro -->
												<div id="event-parameter-container" class="col-md-6"{if (empty ($notificationEvent)) || ($notificationEvent neq Notification::EVENT_TOTAL_RECORDS_REACHED)} style="display: none;{/if}">
													<div class="col-md-4">
														<div class="label-input">
															<label for="event-parameter">{$MOD['LBL_PARAMETER']} <span class="required">*</span></label>
														</div>
													</div>
													<div id="dv-n-event-parameter"  class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
															<input type="text" id="event-parameter" name="eventparameter" value="{$notificationEventParameter}" maxlength="50" class="form-control" />
															<span id="sp-n-event-parameter"  class="help-block"></span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<!-- Parámetros -->
									</div>
								</div>
							</div>
						</div>
					</fieldset>
					</div>
					<div id="step2" class="step-pane">
						<fieldset>  <!-- Condiciones -->
							<legend style="display: none">{$MOD['NAV_STEP2']}</legend>
							<div class="col-xs-12">
							<div class="main-box">
								<div class="main-box-body">
									<div class="row data-section">
										<div class="col-md-12">
											<!-- Tipos de Filtros -->
											<ul class="nav nav-tabs">
												<li class="active"><a href="#period" data-toggle="tab" >{$MOD['LBL_FILTER_PERIOD']}</a></li>
												<li class=""><a href="#advanced" data-toggle="tab" >{$MOD['LBL_FILTER_AVANCED']}</a></li>
											</ul>
											<div class="tab-content">
												<div class="tab-pane fade active in" id="period" style="padding: 12px 2px">
													<!-- Periodo -->
													<div class="row standard-filter" style="margin-top: 20px;">
														<div  id="dv-n-filter-column"  class="form-group col-xs-12 col-md-6 col-lg-3">
															<label for="filter-column">{$MOD['LBL_SELECT_COLUMN']}</label>
															<select id="filter-column" name="columnPeriod" class="form-control" >
																<option value="">Seleccionar un campo</option>
                                                                {if (isset ($FIELD_LIST)) && (!empty ($FIELD_LIST))}
                                                                    {foreach $FIELD_LIST as $field}
                                                                        {if in_array($field.typeofdata, array('D','DT'))}
																			<option value="{if $field.uitype eq 70}crm.{else}tq.{/if}{$field.fieldname}" {if (strpos($notificationColumnPeriod, $field.fieldname)) === false} {else}  selected="selected"{/if}>{$field.label}</option>
                                                                        {/if}
                                                                    {/foreach}
                                                                {/if}
															</select>
															<span id="sp-n-filter-column"  class="help-block"></span>
														</div>
														<div id="dv-n-filter-period"  class="form-group col-xs-12 col-md-6 col-lg-3">
															<label for="filter-period">{$MOD['LBL_SELECT_DURATION']}</label>
															<select id="filter-period" name="filterPeriod" class="form-control" onchange="NotificationUtils.setPeriod (this);">
																<option value="">Seleccionar el tiempo</option>
                                                                {foreach $AVAILABLE_PERIODS as $periodName => $periodLabel}
																	<option value="{$periodName}"{if ($notificationFilterPeriod eq $periodName)} selected="selected"{/if}>{$periodLabel}</option>
                                                                {/foreach}
															</select>
															<span id="sp-n-filter-period"  class="help-block"></span>
														</div>

														<div id="dv-n-filter-start-date"  class="form-group col-xs-12 col-md-6 col-lg-3 custom-filter-date"{if ($notificationFilterPeriod neq 'custom')} style="display: none;"{/if}>
															<label for="standard-filter-start-date">{$MOD['LBL_START_DATE']}</label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
																<input type="text" id="filter-start-date" name="standardfilter[startdate]" readonly  value="{if (isset ($notificationStandardFilter)) && (!empty ($notificationStandardFilter['startdate']))}{$notificationStandardFilter['startdate']}{/if}" class="form-control" placeholder="9999-99-99"{if ($notificationFilterPeriod neq 'custom')} disabled="disabled"{/if} />
															</div>
															<span id="sp-n-filter-start-date"  class="help-block"></span>
														</div>
														<div  id="dv-n-filter-end-date"    class="form-group col-xs-12 col-md-6 col-lg-3 custom-filter-date"{if ($notificationFilterPeriod neq 'custom')} style="display: none;"{/if}>
															<label for="standard-filter-end-date">{$MOD['LBL_END_DATE']}</label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
																<input type="text" id="filter-end-date" name="standardfilter[enddate]" readonly value="{if (isset ($notificationStandardFilter)) && (!empty ($notificationStandardFilter['enddate']))}{$notificationStandardFilter['enddate']}{/if}" class="form-control" placeholder="9999-99-99"{if ($notificationFilterPeriod neq 'custom')} disabled="disabled"{/if} />
															</div>
															<span id="sp-n-filter-end-date"  class="help-block"></span>
														</div>
													</div>
													<!-- /Periodo -->
												</div>

												<div class="tab-pane fade in" id="advanced" style="padding: 12px 2px">
													<!-- Filtros avanzados -->
                                                    {if !empty ($notificationArryFilter) && !empty($FIELD_LIST)}
                                                        {assign var=filters value=$notificationArryFilter|json_decode:true}
                                                        {assign var="totalGroup" value=$filters['filterGroupJoin']|@count}
                                                        {assign var="filterField" value=$filters['filterField']}
                                                        {assign var="filterOperator" value=$filters['filterOperator']}
                                                        {assign var="filterValue" value=$filters['filterValue']}
                                                        {assign var="filterJoin" value=$filters['filterJoin']}
                                                        {assign var="filterGroupJoin" value=$filters['filterGroupJoin']}
                                                        {assign var="indexGrupo" value=$filters['indexGrupo']}
                                                        {assign var="totalIndex" value=$filters['indexGrupo']|@count}
                                                        {assign var="star" value=1}
                                                        {assign var="indexJoin" value=-1}
                                                        {assign var=hasGroup value="true"}
                                                        {if ! empty($filters['filterField'])}
                                                            {include file="modules/notifications/filterNotifyEdit.tpl"}
                                                        {else}
                                                            {assign var="totalGroup" value=0}
                                                            {assign var="totalIndex" value=0}
                                                        {/if}
                                                    {else}
                                                        {assign var="totalGroup" value=0}
                                                        {assign var="totalIndex" value=0}
														{assign var=hasGroup value="false"}
                                                    {/if}
													<div class="action-bar text-center">
														<button type="button" class="btn btn-info" data-group="0"   onclick="NotificationUtils.addFilterGroup (this);" title="Agregar grupo de condiciones">
															<i class="fa fa-plus"></i></button>
													</div>
                                                    <script type="text/javascript" defer="defer">
                                                            totalFilterGroup = {($totalGroup + 1)};
                                                            totalFilterRow   = {($totalIndex + 1)};
                                                            hasGroup = {$hasGroup};
                                                    </script>
												</div>
												<!-- / Tipos de Filtros -->
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
					</fieldset>
					</div>
					<div id="step3" class="step-pane">
						<fieldset> <!-- Define la comunicación -->
							<legend style="display: none">{$MOD['NAV_STEP3']}</legend>
							<div class="col-xs-12">
							<div class="main-box">
								<div class="main-box-body">
									<div class="row data-section">
										<div class="col-md-12">
											<div class="row">
												<!-- Nombre -->
												<div id="dv-n-notification-name" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label class="" for="notification-name">{$MOD['LBL_NAME']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
															<input type="text" id="notification-name" name="notificationname" value="{$notificationName}" maxlength="100" class="form-control" />
															<span id="sp-n-notification-name"  class="help-block"></span>
														</div>
													</div>
												</div>
												<!-- Descripción -->
												<div id="dv-n-notification-description" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="notification-description">{$MOD['LBL_DESCRIPTION']}</label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
															<input type="text" id="notification-description" name="description" value="{$notificationDescription}" maxlength="100" class="form-control" />
															<span id="sp-n-notification-description"  class="help-block"></span>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="row">
												<!-- Users -->
												<div id="dv-n-notification-users" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="notification-channel">{$MOD['LBL_USERS']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($USERS))}
																<select id="notification-users" name="notificationusers[]" class="form-control notification-users" multiple="multiple">
																	<option value="0" {if in_array(0,$notificationUsersFilter)} selected="selected"{/if} >{$MOD['OPT_ALL']}</option>
                                                                    {foreach $USERS as $user}
																		<option value="{$user.id}"{if $notificationScope eq 'SYSTEM'}  {else if in_array(0,$notificationUsersFilter)} selected="selected"{/if}  >{$user.first_name}&nbsp;{$user.last_name}</option>
                                                                    {/foreach}
																</select>
																{literal}
																<script type="text/javascript">
																	jQuery(function () {
																		jQuery('#notification-users').on('focus mousedown', function () {
																			jQuery(this).find('option').prop('disabled', false);
																		});
																	});
																</script>
																{/literal}
																<span id="sp-n-notification-users"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>

												<!-- Status -->
												<div id="dv-n-notification-status" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="notification-status">{$MOD['LBL_STATUS']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($AVAILABLE_STATUSES))}
																<select id="notification-status" name="notificationstatus" class="form-control">
																	<option value="">Seleccionar el estatus</option>
                                                                    {foreach $AVAILABLE_STATUSES as $status}
																		<option value="{$status}"{if ($notificationStatus == $status)} selected="selected"{/if}>{$MOD[$status]}</option>
                                                                    {/foreach}
																</select>
																<span id="sp-n-notification-status"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12 notification-email">
											<div  class="row">
												<!-- Tipo de comunicación  -->
												<div id="dv-n-notification-type" class="col-md-6">
													<div class="col-md-4">

													</div>
													<div class="form-group col-md-8 field-container">

													</div>
												</div>
												<!-- Espacio -->
												<div class="col-md-6">
													<div class="col-md-4">

													</div>
													<div class="form-group col-md-8 field-container">

													</div>
												</div>
											</div>
										</div>
										<div id="notification-system" class="col-md-12 {if $notificationType neq 'NOTIFY'}hide{/if}">
											<div class="row">
												<!-- Ubicación -->
												<div id="dv-n-notification-veiw" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="notification-veiw">{$MOD['LBL_PLATZILLA_VIEW']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($AVAILABLE_VIEWS))}
																<select id="notification-veiw" name="notificationview" class="form-control">
																	<option value="">Seleccionar la ubicación</option>
                                                                    {foreach item=view from=$AVAILABLE_VIEWS}
																		<option value="{$view}" {if $notificationView eq $view}  selected="selected"{/if} >{$MOD[$view]}</option>
                                                                    {/foreach}
																</select>
																<span id="sp-n-notification-veiw"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>
											</div>
										</div>
										<!-- Módulos - Solo para tipo ALERT -->
										<div id="notification-alert-modules" class="col-md-12 {if $notificationType neq 'ALERT'}hide{/if}">
											<div class="row">
												<!--  Modulos -->
												<div id="dv-n-module-names" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="module-names">{$MOD['LBL_MODULES']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($AVAILABLE_MODULES))}
																<select id="module-names" name="modulenames[]" class="form-control" multiple="multiple">
                                                                    {foreach $AVAILABLE_MODULES as $module}
																		<option value="{$module.name}"{if (in_array ($module.name, $notificationModuleNames))} selected="selected"{/if}>{$module.tablabel}</option>
                                                                    {/foreach}
																</select>
																<span id="sp-n-module-names"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>
											</div>
										</div>
										<div id="notification-alert-style" class="col-md-12 {if ($notificationType eq 'MODAL' )}hide{/if}">
											<div class="row">
												<!-- accion style -->
												<div id="dv-n-notification-html" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="notification-html">{$MOD['LBL_NOTIFY']} <span class="required">*</span></label>
														</div>
													</div>
                                                    <div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
															{if (!empty ($MOD.ACTIONS_TYPE))}
																<select id="notification-html" name="notificationstyle" class="form-control" onchange="NotificationUtils.selectedStyle (this)">
																	<option value="">Seleccione el estilo de notificación</option>
																	{foreach from=$MOD.ACTIONS_TYPE key=k item=v}
																		<option value="{$k}" {if ($notificationType eq 'ALERT') && ($k neq 'SIMPLE')} disabled="disabled" {elseif $notificationstyle eq $k}selected="selected"{/if}  >{$v}</option>
																	{/foreach}
																</select>
																<span id="sp-n-notification-html"  class="help-block"></span>
															{/if}
														</div>
													</div>
												</div>
												<!-- Motivo estilo action-->
												<div  id="dv-n-notification-action"  class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="notification-action">{$MOD['LBL_ACTION']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($AVAILABLE_FROM))}
																<select id="notification-action" name="notificationsaction" class="form-control" onchange="NotificationUtils.action (this)">
                                                                    {foreach $AVAILABLE_ACTIONS as $action}
																		<option value="{$action}"{if ($notificationAction == $action)} selected="selected"{/if}>{$MOD[$action]}</option>
                                                                    {/foreach}
																</select>
																<span id="sp-n-notification-action"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>
											</div>
										</div>
										<!--  Notify type Modal -->
										<div id="notification-modal" class="col-md-12 {if ($notificationType neq 'MODAL' )}hide{/if}">
											<div class="row">
												<!-- accion Segundo plano -->
												<div id="dv-n-custom-button" class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="custombutton">{$MOD['LBL_CUSTOM_BUTTON']}</label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($CUSTOMBUTTONS))}
																<select id="custom-button" name="custombuttons[]" class="form-control" title="Seleccione un máximo de cuatro botones. Mantén Ctrl (Windows) o Cmd (Mac) para seleccionar múltiples opciones" multiple="multiple" onchange="NotificationUtils.setButton (this)" size="6">
																		<option value="">-- Ninguno --</option>
                                                                    {foreach $CUSTOMBUTTONS as $row}
                                                                        {assign var='isSelected' value=""}
                                                                        {if $buttonsSelected neq NULL}
                                                                            {foreach $buttonsSelected as $button}
																				{if ($row.link eq $button.link) &&
																				($row.style eq $button.style) &&
																				($row.label eq $button.label)}
																					{$isSelected = "selected='selected'"}
																				{/if}
                                                                            {/foreach}
                                                                        {/if}
																		<option value="{$row.custombuttonid}" {$isSelected} data-module="{$row.module}"  data-style="{$row.style}">{$row.label}</option>
                                                                    {/foreach}
																</select>
                                                            {/if}
														</div>
													</div>
												</div>
												<!-- Texto del modal-->
												<div  id="dv-n-notification-action"  class="col-md-6">
													<div class="col-md-4">
														<div class="label-input">
															<label for="notification-veiw">{$MOD['LBL_MODAL_TEXT']} <span class="required">*</span></label>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<div class="input-group" style="width: 100%;">
                                                            {if (!empty ($MOD['AVAILABLE_MODAL_TEXT']))}
																<select id="modal-text" name="modaltext" class="form-control" onchange="NotificationUtils.setModalText(this)">
                                                                    {foreach  key=key item=text from=$MOD['AVAILABLE_MODAL_TEXT']}
																		<option value="{$key}" >{$text}</option>
                                                                    {/foreach}
																</select>
																<span id="sp-n-notification-veiw"  class="help-block"></span>
                                                            {/if}
														</div>
													</div>
												</div>
											</div>
										</div>
                                        <!-- Correo al panel de mensajes  {if ($notificationType eq 'MODAL' )}hide{/if} --->
                                        <div id="notification-email" class="col-md-12">
                                            <div class="row">
                                                <!-- Botón actualizar contenido -->
                                                <div id="dv-n-notification-html" class="col-md-6">
                                                    <div class="col-md-4">
                                                        <div class="label-input">
                                                            &nbsp;
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-8 field-container">
                                                        <span id="sp-custom-button" class="help-block">
                                                            <button type="button" title="Clic para ver el modal en el campo contenido" class="btn btn-link" onclick="NotificationUtils.setModalContenet()">Actualizar el campo de contenido</button>
                                                        </span>
                                                    </div>
                                                </div>
                                                <!-- checkbox -->
                                                <div  id="dv-n-notification-email"  class="col-md-6">
                                                    <div class="col-md-4">
                                                        <div class="label-input">
                                                           {* <label for="notification-action">{$MOD['LBL_ACTION']}</label>  *}
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-8 field-container">
                                                        <div class="input-group" style="width: 100%;">
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" id="sendbyemail" {if $notificationEmail eq 'ACTIVE'}checked {/if}  name="sendByEmail" value="ACTIVE">Enviar al panel de mensajes
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
										<!-- Contenido -->
										<div id="dv-n-contents"  class="form-group col-md-12 field-container">
											<label for="contents">{$MOD['LBL_CONTENT']} <span class="required">*</span></label>
											<div class="input-group" style="width: 100%;">
												<span id="sp-n-contents"  class="help-block"></span>
												<textarea id="contents" name="contents" class="form-control">{if (isset ($notificationContents))}{$notificationContents}{/if}</textarea>
                                            </div>
										</div>
										<div style="display: none">
											<input type="hidden" id="modal-imput-text"  name="modalInputText" value="{$inputText|replace:"\"":"'"}">
											<input type="hidden" id="modal-exit-text"  name="modalExitText" value="{$exitText|replace:"\"":"'"}">
										</div>
									</div>
								</div>
							</div>
						</div>
					</fieldset>
					</div>
				</div>
				</form>
			</div>
		</div>
	</div>
<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="modules/notifications/notifications.js"></script>
	<script type="text/html" id="condition-template">
        {include file="modules/notifications/filterNotify.tpl"}
	</script>
	<script type="text/html" id="condition-group-template">
        {include file="modules/notifications/filterGroupNotify.tpl"}
	</script>
	<script type="text/javascript" defer="defer">
        jQuery (document).ready (function () {
            totalFilterGroup = {($totalGroup + 1)};
            totalFilterRow   = {($totalIndex + 1)};
            hasGroup         = {$hasGroup};
            checkInstance = NotificationUtils.init('contents');
        });
	</script>
    {include file='modules/notifications/alertTemplates.tpl'}
{/strip}