{strip}
	{assign var='viewColumns' value=$VIEW_DATA.columns}
	{if (!empty ($VIEW_DATA.orderby))}
		{assign var='sortBy' value=key($VIEW_DATA.orderby)}
		{assign var='sortOrder' value=current($VIEW_DATA.orderby)}
	{else}
		{assign var='sortBy' value=null}
		{assign var='sortOrder' value=null}
	{/if}
	{if (!empty ($RETURN_ACTION))}
		{assign var='returnAction' value=$RETURN_ACTION}
	{else}
		{assign var='returnAction' value='ListView'}
	{/if}
	{if (!empty ($RETURN_MODULE))}
		{assign var='returnModule' value=$RETURN_MODULE}
	{else}
		{assign var='returnModule' value=$MODULE}
	{/if}
	<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" />
	<div class="main-box clearfix" {if $TAB_HOME_ID neq NULL}style="margin-top: 0" {/if}>
		<div class="main-box-header clearfix">
			<div id="list-view-header-columns-{$idActivity}" class="row">
				{block name="header-columns"}
					<div class="col-md-3 list-control">
						{if ($ALLOW_MASS_ACTIONS)}
							<div class="btn-general pull-left btn-control">
								<div class="btn-group">
									<button type="button" class="btn btn-default" onclick="return massDelete ('{$MODULE}');"
										title="{$APP.LBL_TRASH_ALL}" style="height: 34px;"><i class="fa fa-trash-o"></i></button>
									<button type="button" class="btn btn-default"
										onclick="MassActionsUtils.openMassEditModal ('{$MODULE}', undefined);"
										title="Edición masiva" style="height: 34px; margin-left: 2px;"><i
											class="fa fa-pencil"></i></button>
									<button type="button" class="btn btn-default"
										onclick="MassActionsUtils.openMassMailModal ('{$MODULE}');" title="Mail masivo"
										style="height: 34px; margin-left: 2px;"><i class="fa fa-envelope"></i></button>
									<button type="button" class="btn btn-default"
										onclick="DataSharingUtils.openMassSharingModal ('{$MODULE}');" title="Compartir"
										style="height: 34px; margin-left: 2px;"><i class="fa fa-share"></i></button>
									{if ($IS_RELATED_TO_CALENDAR)}
										<button type="button" class="btn btn-default"
											onclick="MassActionsUtils.createActivity ('{$MODULE}', '{$VIEWID}');"
											title="{$APP.LBL_CALENDAR_ALL}" style="height: 34px; margin-left: 2px;"><i
												class="fa fa-calendar"></i></button>
									{/if}
								</div>
							</div>
						{/if}
					</div>
					{if (!empty ($ACTIVE_APPLICATIONS)) && (count ($ACTIVE_APPLICATIONS) > 1) && ($APPLICATION_VIEWS_ENABLED)}
						<div id="baseList-profileids" class="col-md-3 list-control">
							<form action="index.php" method="get" class="form">
								<input type="hidden" name="module" value="{$MODULE}" />
								<input type="hidden" name="action" value="ListView" />
								<select id="profileids" name="profileids" class="form-control" onchange="this.form.submit ();"
									title="Vista por aplicación">
									<option value="">Vista por aplicación</option>
									{foreach $ACTIVE_APPLICATIONS as $application}
										<option value="{$application.app_profile}"
											{if (!empty ($PROFILE_IDS)) && (in_array ($application.app_profile, $PROFILE_IDS))}
											selected="selected" {/if}>{$application.app_name}</option>
									{/foreach}
								</select>
							</form>
						</div>
					{/if}
					<div class="col-md-3 list-control">
						<div class="form-group" style="margin-left: 1px !important;">
							<div class="input-group">
								<div class="input-group-btn">
									<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
										style="height: 34px;"><i class="fa fa-filter"></i><span class="caret"></span></button>
									<ul class="dropdown-menu" role="menu">
										<li>
											<a href="index.php?module={$MODULE}&action=CustomView">{$APP.LNK_CV_CREATEVIEW}</a>
										</li>
										{if (in_array (DataViewUtils::PERMISSION_CAN_EDIT, $VIEW_PERMISSIONS))}
											<li>
												<a href="#"
													onclick="DataViewUtils.editView (this); return false;">{$APP.LNK_CV_EDIT}</a>
											</li>
										{/if}
										{if (in_array (DataViewUtils::PERMISSION_CAN_DELETE, $VIEW_PERMISSIONS))}
											<li>
												<form action="index.php" method="post"
													onsubmit="return confirm ('¿Estás seguro que quieres eliminar la vista seleccionada?');">
													<input type="hidden" name="module" value="CustomView" />
													<input type="hidden" name="action" value="Delete" />
													<input type="hidden" name="dmodule" value="{$MODULE}" />
													<input type="hidden" name="record" value="{$VIEW->getId ()}" />
													<input type="hidden" name="return_action" value="{$returnAction}" />
													<input type="hidden" name="return_module" value="{$returnModule}" />
													<button type="submit" class="submit-link">{$APP.LNK_CV_DELETE}</button>
												</form>
											</li>
										{/if}
									</ul>
								</div>
								<select name="viewname" id="viewname" class="form-control" data-module-name="{$MODULE}"
									onchange="DataViewUtils.openView (this, '{$TAB_NAME}');" title="">
									{if (!empty ($AVAILABLE_VIEWS))}
										<optgroup label="Filtros">
											{foreach $AVAILABLE_VIEWS as $availableView}
												<option value="{$availableView->getId ()}"
													{if ($availableView->getId () == $VIEW->getId ())} selected="selected" {/if}
													data-view-type="REGULAR">
													{if ($availableView->getName () != 'All')}{$availableView->getName ()}
													{else}Filtro
													estándar{/if}</option>
											{/foreach}
										</optgroup>
									{/if}
									{if $KANBAN_LIST neq NULL }
										<optgroup label="Kanban">
											{foreach $KANBAN_LIST as $kanban}
												<option value="{$kanban.kanbanviewid}" data-field-name="{$kanban.fieldname}"
													data-view-type="KANBAN">{$kanban.label}</option>
											{/foreach}
										</optgroup>
									{/if}
								</select>
							</div>
						</div>
					</div>
				{/block}
			</div>
		</div>
		<div class="main-box-body clearfix">
			<div class="table-responsive" style="overflow-y: visible;">
				<table id="table_list" class="table">
					<thead>
						<tr class="table-title">
							{if ($ALLOW_MASS_ACTIONS)}
								<th>
									<div class="checkbox-nice checkbox-inline">
										<input type="checkbox" id="select-all-records" placeholder=""
											onclick="toggleSelect_ListView(this.checked, 'selectedrecords[]', {if $IS_HOME_TAB}'{$TAB_HOME_ID}'{else}''{/if});" />
										<label for="select-all-records"></label>
									</div>
								</th>
							{/if}
							{block name="thead-item"}
								{foreach $viewColumns as $viewColumn}
									{if $viewColumn.fieldname eq 'modulename' && !$HAS_RELATED}
										{continue}
									{/if}
									<th>
										<!-- {$RELATED_MODULE} -->
										<div style="display: inline-flex;">
											<div class="title-overflow">
												{if $viewColumn.fieldname eq 'modulename' && empty($viewColumn.uitype)}
													{$viewColumn.fieldlabel}
												{else}
													<a href="javascript:;" class="title-link"
														onclick="DataViewUtils.openPage ('{$MODULE}', {intval($VIEW_DATA.page)}, '{$viewColumn.fieldname}', '{if ($sortBy != $viewColumn.fieldname) || ($sortOrder == 'DESC')}ASC{else}DESC{/if}', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');">
														<span>{$viewColumn.fieldlabel}</span>
														{if ($sortBy != $viewColumn.fieldname) || ($sortOrder == 'DESC')}
															<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;"></i>
														{else}
															<i class="fa fa-caret-down" aria-hidden="true" style="margin-left:.5em;"></i>
														{/if}
													</a>
												{/if}
											</div>
										</div>
									</th>
								{/foreach}
								<th class="col-actions"></th>
							{/block}
						</tr>
					</thead>
					<tbody>
						{foreach $VIEW_DATA.records as $record}
							<tr class="{{$record.eventstatus}|strtolower|replace:' ':'-'}"
								style="background-color: {$record.color}">
								{if ($ALLOW_MASS_ACTIONS)}
									<td>
										<div class="checkbox-nice checkbox-inline">
											<input type="checkbox" id="record-{$record.crmid}" name="selectedrecords[]"
												value="{$record.crmid}" class="view-item"
												onclick="check_object(this, {if $IS_HOME_TAB}'{$TAB_HOME_ID}'{else}''{/if});" />
											<label for="record-{$record.crmid}"></label>
										</div>
									</td>
								{/if}
								{block name="tbody-item"}
									{foreach $viewColumns as $index => $viewColumn}
										<td>
											{if ((empty ($VIEW_DATA.entityidentifier)) && ($index === 0)) || ($viewColumn.fieldname == $VIEW_DATA.entityidentifier)}
												<a
													href="index.php?module={$MODULE}&action=DetailView&record={$record.crmid}">{$record[$viewColumn.fieldname]}</a>
											{else}
												{$record[$viewColumn.fieldname]}
											{/if}
										</td>
									{/foreach}
								{/block}
								<td class="col-actions">
									{block name="row-actions"}
										<table style="width: 100%; border: hidden">
											<tr>
												{if $record['tab_name'] neq NULL && $record['related_id'] neq NULL}
													<td style="width: 8%">
														<a data-width="950" data-toggle="lightbox" data-parent=""
															data-gallery="remoteload" data-title="{*Reporte sobre una actividad*}"
															href="index.php?module=grid_view&action=EditActivityReport&record={$record['related_id']}&formodule={$record['tab_name']}&Ajax=true"><span
																class="icon icon-02-iconos-chat"></span>
														</a>
													</td>
												{/if}
												<td style="width: 8%">
													<a href="index.php?module={$MODULE}&action=EditView&record={$record.crmid}&return_module={$returnModule}&return_action={$returnAction}&tab={$TAB_NAME}&return_viewname={$VIEW->getId ()}"
														class="btn btn-link" style="padding-left: 7px; padding-right: 7px;"
														title="Editar tarea"><i class="fa fa-pencil"></i></a>
												</td>
												<td style="width: 8%">
													{if $record['eventstatus'] eq 'Planned'}
														<form action="index.php" method="post" class="form-inline"
															onsubmit="return confirm ('¿Estás seguro que quieres eliminar el registro seleccionado?');">
															<input type="hidden" name="module" value="{$MODULE}" />
															<input type="hidden" name="action" value="Delete" />
															<input type="hidden" name="record" value="{$record.crmid}" />
															<input type="hidden" name="return_action"
																value="{$returnAction}&tab={$TAB_NAME}" />
															<input type="hidden" name="return_module" value="{$returnModule}" />
															<input type="hidden" name="Ajax" value="true" />
															<button title="Eliminar tarea" type="submit" class="btn btn-link"
																style="padding-left: 7px; padding-right: 7px;"><i
																	class="fa fa-trash-o"></i></button>
														</form>
													{/if}
												</td>
												{if ($record['eventstatus'] != 'Held')}
													<td style="width: 8%">
														<form action="index.php" method="post" class="form-inline"
															onsubmit="return confirm ('¿Estás seguro que quieres finalizar la tarea seleccionada?');">
															<input type="hidden" name="module" value="Calendar" />
															<input type="hidden" name="action" value="FinishTask" />
															<input type="hidden" name="record" value="{$record.crmid}" />
															<input type="hidden" name="return_action"
																value="{$returnAction}&tab={$TAB_NAME}" />
															<input type="hidden" name="return_module" value="{$returnModule}" />
															<input type="hidden" name="Ajax" value="true" />
															<button title="Finalizar tarea" type="submit" class="btn btn-link"
																style="padding-left: 7px; padding-right: 7px;"><i
																	class="fa fa-check"></i></button>
														</form>
													</td>
												{/if}
											</tr>
										</table>
									{/block}
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
		<header class="btn-footer main-box-header clearfix">
			<div class="row">
				<div class="filter-block col-md-6 pull-left">
					{if ($VIEW_DATA.totalRecords > 0)}
						Mostrando registros {($VIEW_DATA.startRecord + 1)} - {$VIEW_DATA.endRecord} de {$VIEW_DATA.totalRecords}
					{else}
						Mostrando 0 registros
					{/if}
				</div>
				<div class="col-md-6" style="margin-top: -.25em; margin-right:-.2em;">
					<ul class="pagination pull-right">
						<li{if ($VIEW_DATA.totalPages <= 1)} class="disabled" {/if}><button type="button"
								onclick="DataViewUtils.openPage ('{$MODULE}', 1, '{if (!empty ($sortBy))}{$sortBy}{/if}', '{if (!empty ($sortOrder))}{$sortOrder}{/if}', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');"><i
									class="fa fa-step-backward"></i></button></li>
							<li{if ($VIEW_DATA.page <= 1)} class="disabled" {/if}><button type="button"
									onclick="DataViewUtils.openPage ('{$MODULE}', {$VIEW_DATA.page - 1}, '{if (!empty ($sortBy))}{$sortBy}{/if}', '{if (!empty ($sortOrder))}{$sortOrder}{/if}', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');"><i
										class="fa fa-chevron-left"></i></button></li>
								<li>
									<span class="pagination-search">
										<input type="text" id="pagenum" name="pagenum" class="form-control"
											value="{$VIEW_DATA.page}" data-actual-page="{$VIEW_DATA.page}"
											data-total-pages="{$VIEW_DATA.totalPages}" placeholder=""
											onchange="DataViewUtils.openSelectedPage (this, '{$MODULE}', '{if (!empty ($sortBy))}{$sortBy}{/if}', '{if (!empty ($sortOrder))}{$sortOrder}{/if}', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');" />
										de {if (!empty ($VIEW_DATA.totalPages))}{$VIEW_DATA.totalPages}{else}1{/if}
									</span>
								</li>
								<li{if ($VIEW_DATA.page >= $VIEW_DATA.totalPages)} class="disabled" {/if}><button
										type="button"
										onclick="DataViewUtils.openPage ('{$MODULE}', {$VIEW_DATA.page + 1}, '{if (!empty ($sortBy))}{$sortBy}{/if}', '{if (!empty ($sortOrder))}{$sortOrder}{/if}', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');"><i
											class="fa fa-chevron-right"></i></button></li>
									<li{if ($VIEW_DATA.page >= $VIEW_DATA.totalPages)} class="disabled" {/if}><button
											type="button"
											onclick="DataViewUtils.openPage ('{$MODULE}', {$VIEW_DATA.totalPages}, '{if (!empty ($sortBy))}{$sortBy}{/if}', '{if (!empty ($sortOrder))}{$sortOrder}{/if}', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');"><i
												class="fa fa-step-forward"></i></button></li>
					</ul>
				</div>
			</div>
		</header>
	</div>
	<script type="text/javascript" src="modules/Home/home-utils.js?v=1.0"></script>
	<!-- {$TAB_NAME} -->
	<script type="text/javascript">
		jQuery(document).ready(function() {
		jQuery ('.list-view-filter-date-{$idActivity}.date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 }).on ('changeDate', function () {
		DataViewUtils.openPage ('{$MODULE}', 1, '', '', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');
		}).on('clearDate', function() {
		DataViewUtils.openPage ('{$MODULE}', 1, '', '', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');
		});
		jQuery ('select.list-view-filter-{$idActivity}').change (function () {
		DataViewUtils.openPage ('{$MODULE}', 1, '', '', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');
		});

		jQuery ('#period-dates-{$idActivity}').change (function () {
		var today = new Date(),
			date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate(),
			endDate = jQuery(this).parent().parent().find('#end-date-home'),
			stardDate = jQuery(this).parent().parent().find('#start-date-home');
		if (jQuery(this).val() !== '') {
			endDate.val(date);
			stardDate.val(jQuery(this).val());
		} else {
			endDate.val('');
			stardDate.val('');
		}
		DataViewUtils.openPage('{$MODULE}', 1, '', '', '{$RELATED_MODULE}', '{$idActivity}', '{$TAB_NAME}');
		});
		HomeUtils.setDefaultIdView('{$VIEW->getId ()}', '{$idActivity}');
		});
	</script>
{/strip}