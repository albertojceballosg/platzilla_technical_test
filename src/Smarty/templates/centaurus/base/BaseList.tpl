{block name="css"}{/block}
{block name="js"}{/block}
{block name="first-content"}{/block}
<style type="text/css">
	/* The switch - the box around the slider */
	.switch {
		display: inline-block;
		margin-left: 20px;
		height: 20px;
		position: relative;
		width:    45px;
	}
	/* Hide default HTML checkbox */
	.switch input {
		opacity: 0;
		width:   0;
		height:  0;
	}
	/* The slider */
	.slider {
		background-color: #2196F3;
		position:           absolute;
		cursor:             pointer;
		top:                0;
		left:               0;
		right:              0;
		bottom:             0;
	}
	.slider:before {
		position:           absolute;
		content:            "";
		height:             14px;
		width:              14px;
		left:               4px;
		bottom:             3px;
		background-color:   white;
		-webkit-transition: .4s;
		transition:         .4s;
	}
	/* Rounded sliders */
	.slider.round {
		border-radius: 34px;
	}
	.slider.round:before {
		border-radius: 50%;
	}
	.main-box {
		box-shadow:    0px 0px 0px 0 #FFFFFF !important;
		border-radius: 0px !important;
	}
	.base-list-container {
		background-color: #ffffff;
		margin:           0px -13px!important;
		border-top:       1px solid #D8D8D8 !important;
		height:           auto;
		min-height:       1100px !important;
	}

	@media (min-width: 1280px) and (max-width: 1400px) {
		.list-view-filter {
			margin-left: -25px !important;
		}
	}
	@media (min-width: 1440px) and (max-width: 1800px) {
		.list-view-filter {
			margin-left: -30px !important;
		}
	}
	.flex-container {
		display: flex;
		align-items: stretch;
		flex-direction: row;
		flex-wrap: nowrap;
	}

	.flex-container > div {
		margin: 0 0.15em 0 0.15em;
		text-align:left;
	}
</style>
{math equation='x - y' x=12 y=$STATUS_TOTAL_BUTTONS assign='col'}
<div class="container-fluid base-list-container">
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<div class="main-box-header clearfix">
				<div class="row">
					<div class="col-md-8">
						<div class="form-group col-md-12 pull-left {if (!isset($IS_HOME_TAB)) && ((true) ||($GRAPHS neq NULL))}list-view-filter{/if}" style="margin-bottom: 5px!important;{if isset($IS_HOME_TAB)}display: none{/if}">
                            {* combobox filtros *}
							<div class="btn-group" style="margin-left: 10px;">
								<button type="button" class="btn btn-primary" style=" font-size: 15px!important;vertical_align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;" title="Listado de registros"><i class="fa fa-list-ul"></i></button>
                                {* LIST-VIEW-KANBAN-VIEW *}
                                {if $STATUS_BUTTONS['kanban']}
									<a data-toggle="tab" href="#LIST-VIEW-KANBAN-VIEW" class="btn btn-default" style=" font-size: 15px!important;vertical_align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
									   title="Vista kanban de registos"
									   onclick="ListViewTabUtils.activeKanbanTab (event)"
									   data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
                                {/if}
                                {* LIST-VIEW-BOX-SCORE *}
                                {if $STATUS_BUTTONS['boxscore']}
									<a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE" class="btn btn-default" style=" font-size: 15px!important;vertical_align:middle; display: none;height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
									   title="Indicadores de gestión"
									   onclick="ListViewTabUtils.activeBoxScoreTab (event)"
									   data-toggle="tab"><i class="fa fa-heart-o"></i></a>
                                {/if}
                                {* LIST-VIEW-GRAPHIC *}
                                {if $STATUS_BUTTONS['graphic']}
									<a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default hide" style=" font-size: 15px!important; display: none;height: 2.3em;margin-right:0.05em;margin-left:0.05em;"
									   title="Gráficos"
									   onclick="ListViewTabUtils.activeGraphicTab (event)"
									   data-toggle="tab"><i class="fa fa-bar-chart-o"></i></a>
                                {/if}
                                {* report *}
                                {if $STATUS_BUTTONS['report']}
									<a data-toggle="tab" href="#LIST-VIEW-REPORT" class="btn btn-default hide" style=" font-size: 15px!important;vertical_align:middle; display: none;height: 2.3em;margin-right:0.05em;margin-left:0.05em;"
									   title="Informes"
									   onclick="ListViewTabUtils.activeReportTab (event)"
									   data-toggle="tab"><i class="fa fa-file" aria-hidden="true"></i></a>
                                {/if}
                                {* LIST-VIEW-CALENDAR *}
                                {if $STATUS_BUTTONS['calendar']}
									<a data-toggle="tab" href="#LIST-VIEW-CALENDAR" class="btn btn-default" style=" font-size: 15px!important;vertical_align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
									   title="Vista calendario"
									   onclick="ListViewTabUtils.activeCalendarTab (event)"
									   data-toggle="tab"><i class="fa fa-calendar"></i></a>
                                {/if}
                                {* Kanban-task *}
                                {if $STATUS_BUTTONS['task']}
									<a data-toggle="tab" href="#LIST-VIEW-KANBAN-TASK-VIEW" class="btn btn-default" style=" font-size: 15px!important;vertical_align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
									   title="Vista kanban de tareas"
									   onclick="ListViewTabUtils.activeKanbanTaskTab (event)"
									   data-toggle="tab"><i class="bi bi-kanban-fill"></i></a>
                                {/if}
                                {* Gantt Module View *}
                                {if isset($STATUS_BUTTONS.gantt) && $STATUS_BUTTONS.gantt}
									<a data-toggle="tab" href="#LIST-VIEW-GANTT-MODULE" id="gantt-module-tab" class="btn btn-default list-view-tab" style=" font-size: 15px!important;vertical_align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
									   title="Vista Gantt"
									   onclick="ListViewGanttModuleUtils.activeGanttModuleTab('{$MODULE}')"
									   data-toggle="tab"><span class="glyphicon glyphicon-indent-left"></span></a>
                                {/if}
								{if $AVAILABLE_USERS_FILTER neq NULL}
								<div class="btn-group">
									<button id="listview-btn-group-{$idListView}" type="button"
											class="btn btn-default dropdown-toggle"
											style="font-size: 15px!important;vertical_align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
											data-toggle="dropdown">
										<i class="fa fa-user" aria-hidden="true"></i>&nbsp;
                                        <span class="caret"></span>
									</button>
									<ul id="listview-user-search-{$idListView}" class="dropdown-menu scroll-user-menu" role="menu">
										<li><a href="index.php?module=panelusuarios&action=EditView" title="Registrar un usuario" ><i class="fa fa-plus-circle" style="padding-right: 0"></i>Nuevo usuario</a></li>
										<li class="divider"></li>
										<li><a href="#" title="Seleccionar todos los usuarios" onclick="ListViewTabUtils.selectedAllUser (event, this, '{$idListView}')">
												<i class="fa fa-check-square" aria-hidden="true"></i>Todos los usuarios
											</a>
										</li>
                                        <li><a href="#" title="Filtrar por usuarios"
                                               onclick="ListViewTabUtils.searchUsers(this, '{$MODULE}', '{$idListView}')">
                                                <i class="fa fa-search" aria-hidden="true"></i>Filtrar por usuarios
                                            </a>
                                        </li>
                                        <li class="divider"></li>
										{foreach $AVAILABLE_USERS_FILTER as $id => $user}
										<li>
											<a href="#" title="{$user['name']}" rel="{{$id}}" onclick="ListViewTabUtils.selectedUser (event, this, '{$idListView}')">
												<img  class="img-circle" style="width: 60%; height: 60%" data-src="{$user['avatar']}" alt="{$user['name']}" src="{$user['avatar']}">
											</a>
										</li>
                                        {/foreach}
									</ul>
								</div>
								{/if}
								<div class="input-group">
									<div class="input-group-btn">
										<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="height: 2.3em;vertical_align:middle; margin-left: 0.05em;"><i class="fa fa-filter"></i><span class="caret"></span></button>
										<ul class="dropdown-menu" role="menu">
											<li>
												<a href="index.php?module={$MODULE}&action=CustomView&parenttab={$CATEGORY}">    {$APP.LNK_CV_CREATEVIEW}</a>
											</li>
                                            {if $CV_EDIT_PERMIT eq 'yes'}
												<li>
													<a href="index.php?module={$MODULE}&action=CustomView&record={$VIEWID}&parenttab={$CATEGORY}">{$APP.LNK_CV_EDIT}</a>
												</li>
                                            {/if}
                                            {if $CV_DELETE_PERMIT eq 'yes'}
												<li>
													<a href="javascript:confirmdelete('index.php?module=CustomView&action=Delete&dmodule={$MODULE}&record={$VIEWID}&parenttab={$CATEGORY}')">{$APP.LNK_CV_DELETE}</a>
												</li>
                                            {/if}
										</ul>
									</div>
									<select name="viewname" id="viewname" class="form-control" onchange="showDefaultCustomView(this,'{$MODULE}','{$CATEGORY}')" title="">
                                        {$CUSTOMVIEW_OPTION}
                                        {if $KANBAN_LIST neq NULL && false}
											<optgroup label="Kanban">
                                            {foreach $KANBAN_LIST as $kanban}
												<option value="{$kanban.kanbanviewid}" fieldname="{$kanban.fieldname}">{$kanban.label}</option>
												</optgroup>
                                            {/foreach}
                                        {/if}
									</select> {*style="width: 20em !important;" *}
								</div>
							</div>
                            {* /combobox filtros *}
						</div>
					</div>
					<div class="col-md-4" style="padding-right: 0">
                        {block name="header-buttons-row-two"}{/block}
					</div>
{if (!empty ($ACTIVE_APPLICATIONS)) && (count ($ACTIVE_APPLICATIONS) > 1) && ($APPLICATION_VIEWS_ENABLED)}
					<div id="baseList-profileids" class="col-md-3">
						<form action="index.php" method="get" class="form">
							<input type="hidden" name="module" value="{$MODULE}" />
							<input type="hidden" name="action" value="ListView" />
							<select id="profileids" name="profileids" class="form-control" onchange="this.form.submit ();" title="Vista por aplicación">
								<option value="">Vista por aplicación</option>
	{foreach $ACTIVE_APPLICATIONS as $application}
								<option value="{$application.app_profile}"{if (!empty ($PROFILE_IDS)) && (in_array ($application.app_profile, $PROFILE_IDS))} selected="selected"{/if}>{$application.app_name}</option>
	{/foreach}
							</select>
						</form>
					</div>
{/if}
					{* <div class="col-md-3">  combobox filtros </div> *}
				</div>
			</div>
			<form name="massdelete" method="POST" id="massdelete" onsubmit="VtigerJS_DialogBox.block();">
{block name="input-form"}
				<input name="module" id="curmodule" type="hidden" value="{$MODULE}">
				<input name="maxrecords" id="maxrecords" type="hidden" value="{$MAX_RECORDS}">
				<input name='search_url' id="search_url" type='hidden' value='{$SEARCH_URL}'>
				<input name="idlist" id="idlist" type="hidden">
				<input name="change_owner" type="hidden">
				<input name="change_status" type="hidden">
				<input name="action" type="hidden">
				<input name="where_export" type="hidden" value="{php} echo to_html($_SESSION['export_where']);{/php}">
				<input name="step" type="hidden">
				<input name="excludedRecords" type="hidden" id="excludedRecords" value="">
				<input name="numOfRows" id="numOfRows" type="hidden" value="">
				<input name="allids" type="hidden" id="allids" value="{$ALLIDS}">
				<input name="selectedboxes" id="selectedboxes" type="hidden" value="{$SELECTEDIDS}">
				<input name="allselectedboxes" id="allselectedboxes" type="hidden" value="{$ALLSELECTEDIDS}">
				<input name="current_page_boxes" id="current_page_boxes" type="hidden" value="{$CURRENT_PAGE_BOXES}">
{/block}
				<div class="main-box-body clearfix">
					<div class="table-responsive" style="overflow-y: visible;">
						<table class="table" id="table_list" name="table_list">
							<tr class="table-title">
{*block name="thead-item"*}
	{foreach name="listviewforeach" item=header from=$LISTHEADER}
		{if not $smarty.foreach.listviewforeach.last}
								<th aria-controls="table_list" {if !empty($header|strpos:"smownerid")}style="width: 10%"{/if}>
									<div style="display:inline-flex;">{$header}</div>
								</th>
		{else}
								<th aria-controls="table_list"></th>
		{/if}
	{/foreach}
{block name="thead-item"}
{/block}
							</tr>
							<tr>{block name="tbody"}{/block}</tr>
{foreach item=entity key=entity_id from=$LISTENTITY}
							<tr bgcolor="{$entity.color}" onMouseOver="this.className='{$entity.onmouseover}'" onMouseOut="this.className='{$entity.onmouseout}'" id="row_{$entity_id}">
	{*block name="tbody-item"*}
								{*$entity.isRemovable|var_dump*}
		{assign var="cant" value="0"}
		{foreach item=data key=rec_key from=$entity.records}
			{assign var="cant" value=$cant+1}
			{* vtlib customization: Trigger events on listview cell *}
			{assign var="backgcolor" value=""}
			{assign var="style" value=""}
			{if $entity.fields_attributes[$rec_key].bgcolor}
				{assign var="backgcolor" value=$entity.fields_attributes[$rec_key].color}
				{assign var="style" value=$entity.fields_attributes[$rec_key].style}
			{/if}
								<td class="module-list-view-td" {if $entity.records|@count eq $cant }nowrap align="right"{/if} {if $backgcolor}style="background-color:#{$backgcolor};{$style}"{/if}>{$data}</td>
		{/foreach}
	{block name="tbody-item"}
	{/block}
							</tr>
{/foreach}
						</table>
					</div>
				</div>
			</form>
			<header class="btn-footer main-box-header clearfix">
				<div class="row">
					<div class="filter-block col-md-6 pull-left">{$recordListRange}</div>
					<div class="col-md-6" style="margin-top: -.25em; margin-right:-.2em;">{$NAVIGATION}</div>
				</div>
			</header>
{block name="last-content"}{/block}
{block name="scripts"}{/block}
{block name="extra-content"}{/block}
		</div>
	</div>
</div>
</div>
{* Incluir JavaScript para vista Gantt de módulos - FUERA de los bloques para no romper herencia *}
{if isset($STATUS_BUTTONS.gantt) && $STATUS_BUTTONS.gantt}
<script type="text/javascript" src="include/js/ListViewGanttModule.js"></script>
{/if}