{extends file='base/BaseList.tpl'}
{block name="css"}
	<link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css" />
{/block}
{block name='js'}
	{if (!empty ($smarty.request.ajax))}
		&#&#&#{$ERROR}&#&#&#
	{/if}
	<script type="text/javascript" src="include/js/ListView.js?v=1.6"></script>
	<script type="text/javascript" src="include/js/Mail.js"></script>
{/block}
{block name='first-content'}
	{if (!$CAN_CREATE_RECORDS)}
		<div class="row">
			<div class="alert alert-danger">
				<span><strong>Advertencia: </strong> El módulo está suscrito en modo de pruebas. Has llegado al límite de
					registros que puedes crear en este modo.</span>
				{if ($IS_ADMIN)}
					<span>Te invitamos a actualizar <a
							href="index.php?module=Home&action=ViewSubscriptionDetails&tab=subscription">tu suscripción</a></span>
				{/if}
			</div>
		</div>
	{/if}
{/block}
{block name='scripts'}
	<script type="text/javascript">
		function viewSearch() {
			var divSearch = jQuery('#divsearch');

			if (!divSearch.is(':visible')) {
				jQuery('#imgsearch').removeClass('fa-search-plus').addClass("fa-search-minus");
				divSearch.show();
			} else {
				jQuery('#imgsearch').removeClass('fa-search-minus').addClass('fa-search-plus');
				divSearch.hide();
			}
		}

		function messageConstruction() {
			alert('Funcionalidad En construcci\u00f3n');
		}
	</script>
	<script type="text/html" id="instances-data-sharing-share-modal-template">
		{include file='modules/instancesdatasharing/ShareModal.tpl'}
	</script>
	<script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
	{if ($IS_REL_ACTIVITIES == '1')}
		{include file='CreateTaskWizard.tpl'}
	{/if}
{/block}
{block name="header-buttons-row-two" prepend}
	<div class="btn-general pull-right btn-control" style="margin-right: 0">
		<div class="col-md-12" style="padding-right: 0">
			<div class="btn-group" style="margin-right:0;">
				<div style="display: inline-block; float: left; padding-right: 20px">
					<h4 style="font-weight: bold; color: #cccccc">Vista de lista</h4>
				</div>
				{*
			<button type="button" class="btn btn-primary" style=" font-size: 15px!important;" title="Listado de registros"><i class="fa fa-list-ul"></i></button>
			*}
				{* LIST-VIEW-KANBAN-VIEW *}
				{if $STATUS_BUTTONS['kanban'] && false}
					<a data-toggle="tab" href="#LIST-VIEW-KANBAN-VIEW" class="btn btn-default"
						style=" font-size: 15px!important;" title="Vista kanban"
						onclick="ListViewTabUtils.activeKanbanTab (event)" data-toggle="tab"><i class="fa fa-trello"
							aria-hidden="true"></i></a>
				{/if}
				{* LIST-VIEW-BOX-SCORE *}
				{if $STATUS_BUTTONS['boxscore'] && false}
					<a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE" class="btn btn-default" style=" font-size: 15px!important;"
						title="Indicadores de gestión" onclick="ListViewTabUtils.activeBoxScoreTab (event)" data-toggle="tab"><i
							class="fa fa-heart-o"></i></a>
				{/if}
				{* LIST-VIEW-GRAPHIC *}
				{if $STATUS_BUTTONS['graphic'] && false}
					<a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default"
						style=" font-size: 15px!important; vertical-align:middle; height: 2.3em; margin-right:0.05em;margin-left:0.05em;"
						title="Gráficos" onclick="ListViewTabUtils.activeGraphicTab (event)" data-toggle="tab"><i
							class="fa fa-bar-chart-o"></i></a>
				{/if}
				{* report *}
				{if $STATUS_BUTTONS['report'] && false}
					<a data-toggle="tab" href="#LIST-VIEW-REPORT" class="btn btn-default"
						style=" font-size: 15px!important; height: 2.3em; margin-right:0.05em;margin-left:0.05em;"
						title="Informes" onclick="ListViewTabUtils.activeReportTab (event)" data-toggle="tab"><i
							class="fa fa-file" aria-hidden="true"></i></a>
				{/if}
				{* LIST-VIEW-CALENDAR *}
				{if $STATUS_BUTTONS['calendar'] && false}
					<a data-toggle="tab" href="#LIST-VIEW-CALENDAR" class="btn btn-default"
						style=" font-size: 15px!important; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
						title="vista calendario" onclick="ListViewTabUtils.activeCalendarTab (event)" data-toggle="tab"><i
							class="fa fa-calendar"></i></a>
				{/if}
				<button type="button" class="btn btn-default dropdown-toggle dropdown-toggle-ext"
					style=" font-size: 15px!important;" data-toggle="dropdown">
					&nbsp;<i class="fa fa-bolt{*fa-tags*}" aria-hidden="true" {*style="margin-top: .17em"*}></i>&nbsp;<span
						class="caret"></span>
				</button>
				<ul class="dropdown-menu pull-right" role="menu">
					<li><a href="#" onclick="return massDelete ('{$MODULE}');" title="{$APP.LBL_TRASH_ALL}"><i
								class="fa fa-trash-o"></i>Eliminar registros</a></li>
					<li><a href="#" onclick="MassActionsUtils.openMassEditModal ('{$MODULE}', undefined);"
							title="Edición masiva"><i class="fa fa-pencil"></i>Editar registros</a></li>
					<li><a href="#" onclick="MassActionsUtils.openMassMailModal ('{$MODULE}');" title="Mail masivo"><i
								class="fa fa-envelope"></i>Enviar email masivo</a></li>
					<li><a href="#" onclick="DataSharingUtils.openMassSharingModal ('{$MODULE}');" title="Compartir"><i
								class="fa fa-share"></i>Compartir registros</a></li>
					{if ($IS_REL_ACTIVITIES == '1')}
						<li><a href="#" onclick="CalendarWizard.open ('{$MODULE}', '', 'inRow')"
								title="Registar en calendario"><i class="fa fa-calendar"></i>Registrar en calendario</a></li>
					{/if}
				</ul>
			</div>
		</div>
		{if false}
			<div class="btn-group">
				<button type="button" class="btn btn-default" onclick="return massDelete ('{$MODULE}');"
					title="{$APP.LBL_TRASH_ALL}" style="height: 34px;"><i class="fa fa-trash-o"></i></button>
				<button type="button" class="btn btn-default" onclick="MassActionsUtils.openMassEditModal ('{$MODULE}');"
					title="Edición masiva" style="height: 34px; margin-left: 2px;"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-default" onclick="MassActionsUtils.openMassMailModal ('{$MODULE}');"
					title="Mail masivo" style="height: 34px; margin-left: 2px;"><i class="fa fa-envelope"></i></button>
				<button type="button" class="btn btn-default" onclick="DataSharingUtils.openMassSharingModal ('{$MODULE}');"
					title="Compartir" style="height: 34px; margin-left: 2px;"><i class="fa fa-share"></i></button>
				{if ($IS_REL_ACTIVITIES == '1')}
					{*<button type="button" class="btn btn-default" onclick="MassActionsUtils.createActivity ('{$MODULE}', '{$VIEWID}');" title="{$APP.LBL_CALENDAR_ALL}" style="height: 34px; margin-left: 2px;"><i class="fa fa-calendar"></i></button>*}
					<button type="button" class="btn btn-default" onclick="CalendarWizard.open ('{$MODULE}', '', '')"
						style="height: 34px; margin-left: 2px;"><i class="fa fa-calendar"></i></button>
				{/if}
			</div>
		{/if}
	</div>
{/block}
{block name="input-form" append}
	<input type="hidden" name="totalrecords" value="{$TOTALRECORD}" />
{/block}
{block name="thead-item" prepend}
	<th class="lvtCol" style="vertical-align: center; padding-bottom: 12px;padding-top: 3px">
		<div class="checkbox-nice checkbox-inline" style="vertical-align: center">
			<input name="selectall" id="selectCurrentPageRec"
				onclick="toggleSelect_ListView (this.checked, 'selectedrecords[]');" type="checkbox" />
			<label for="selectCurrentPageRec"></label>
		</div>
	</th>
{/block}
{block name="tbody" prepend}
	<td id="linkForSelectAll" class="linkForSelectAll" style="display: none;" colspan="15">
		<a href="#">
			<span id="selectAllRec" class="selectall" style="display:inline;"
				onClick="toggleSelectAll_Records('{$MODULE}',true,'selectedrecords[]')">{$APP.LBL_SELECT_ALL}
				<span id="count"></span> {$APP.LBL_RECORDS_IN} {$MODULE|@getTranslatedString:$MODULE}
			</span>
		</a>
		<a href="#">
			<span id="deSelectAllRec" class="selectall" style="display:none;"
				onClick="toggleSelectAll_Records('{$MODULE}',false,'selectedrecords[]')">{$APP.LBL_DESELECT_ALL}
				{$MODULE|@getTranslatedString:$MODULE}</span>
		</a>
	</td>
{/block}
{block name="tbody-item" prepend}
	<td width="2%" style="vertical-align: center; padding-bottom: 12px;padding-top: 3px">
		<!-- wa 26/03 -->
		<div class="checkbox-nice checkbox-inline">
			<input type="checkbox" id="{$entity_id}"
				data-removable="{if $entity.isRemovable}{$entity.isRemovable}{else}1{/if}" name="selectedrecords[]"
				value="{$entity_id}" class="view-item" onClick="check_object(this)">
			<label for="{$entity_id}"></label>
		</div>
	</td>
{/block}
{block name="last-content" append}
	{$SELECT_SCRIPT}
{/block}