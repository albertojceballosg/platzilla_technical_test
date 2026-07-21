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
		box-shadow:    0 0 0 0 #FFFFFF !important;
		border-radius: 0 !important;
	}
	.base-list-container {
		background-color: #ffffff;
		margin:           0 -13px!important;
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
		<div class="main-box clearfix" style="margin-top: 1px">
			<div class="main-box-header clearfix" style="display:block">
				<div class="row">
					<div class="col-md-6">
						<div class="btn-group" style="margin-right:0;">
                            {* LIST-VIEW-GRAPHIC *}
                            {*if $STATUS_BUTTONS['graphic']*}
							<a data-toggle="tab" href="#VIEW-TASK-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;"
							   title="Tareas"
							   onclick="HomeUtils.activeTaskTab (event, '{$TAB_HOME_ID}', '{$MODULE}', 'VIEW-TASK','{$TAB_GROUP}')"
							   data-toggle="tab"><i class="fa fa-check-square" aria-hidden="true"></i></a>
                            {*/if*}
							<button id="LIST-TASK-{$TAB_HOME_ID}" type="button" class="btn btn-primary" style=" font-size: 15px!important;"
									title="Listado de registros"><i class="fa fa-list-ul"></i></button>
                            {* LIST-VIEW-KANBAN-VIEW *}
                            {*if $STATUS_BUTTONS['kanban']*}
							<a data-toggle="tab" href="#VIEW-KANBAN-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;"
							   title="Vista kanban"
							   onclick="HomeUtils.activeTab (event, '{$TAB_HOME_ID}', '{$MODULE}', 'VIEW-KANBAN','{$TAB_GROUP}')"
							   data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
                            {*/if*}
                            {* LIST-VIEW-CALENDAR *}
                            {*if $STATUS_BUTTONS['calendar']*}
							<a data-toggle="tab" href="#VIEW-CALENDAR-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;"
							   title="vista calendario"
							   onclick="HomeUtils.activeTab (event, '{$TAB_HOME_ID}', '{$MODULE}', 'VIEW-CALENDAR','{$TAB_GROUP}')"
							   data-toggle="tab"><i class="fa fa-calendar"></i></a>
                            {*/if*}
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group col-md-12 pull-left" style="display: none">
                            {* combobox filtros *}
							<div class="input-group col-lg-4 col-md-4 col-xs-4">
								<div class="input-group-btn">
									<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="height: 34px;"><i class="fa fa-filter"></i><span class="caret"></span></button>
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
								<select name="viewname" id="viewname-{$TAB_HOME_ID}"  data-record-tab-id="{$TAB_HOME_ID}" class="form-control" onchange="HomeUtils.showRecordCustomView(this,'{$MODULE}','{$CATEGORY}')" title="">
                                        {$CUSTOMVIEW_OPTION}
								</select> {*style="width: 20em !important;" *}
							</div>
                            {* /combobox filtros *}
						</div>
					</div>
					<div class="col-md-6" style="padding-right: 0">
						<div style="display: none">
                        {block name="header-buttons-row-two"}{/block}
						</div>
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
			<form name="massdelete" method="POST" id="massdelete-{$TAB_HOME_ID}" onsubmit="VtigerJS_DialogBox.block();">
{block name="input-form"}
				<input name='search_url' id="search_url" type='hidden' value='{$SEARCH_URL}'>
				<input name="idlist" id="idlist" type="hidden">
				<input name="change_owner" type="hidden">
				<input name="change_status" type="hidden">
				<input name="action" type="hidden">
				<input name="where_export" type="hidden" value="{php} echo to_html($_SESSION['export_where']);{/php}">
				<input name="step" type="hidden">
				<input name="excludedRecords" type="hidden" id="excludedRecords-{$TAB_HOME_ID}" value="">
				<input name="numOfRows" id="numOfRows" type="hidden" value="">
				<input name="allids" type="hidden" id="allids" value="{$ALLIDS}">
				<input name="selectedboxes" id="selectedboxes-{$TAB_HOME_ID}" type="hidden" value="{$SELECTEDIDS}">
				<input name="allselectedboxes" id="allselectedboxes-{$TAB_HOME_ID}" type="hidden" value="{$ALLSELECTEDIDS}">
				<input name="current_page_boxes" id="current_page_boxes" type="hidden" value="{$CURRENT_PAGE_BOXES}">
{/block}
				<div class="main-box-body clearfix">
					<div class="table-responsive" style="overflow-y: visible;">
						<table class="table" id="table_list" name="table_list">
							<tr class="table-title">
{*block name="thead-item"*}
	{foreach name="listviewforeach" item=header from=$LISTHEADER}
		{if not $smarty.foreach.listviewforeach.last}
								<th aria-controls="table_list">
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
			<td class="module-list-view-td" {if $entity.records|@count eq $cant }nowrap align="right"{/if} {if $backgcolor}style="background-color:#{$backgcolor};{$style}"{/if}>
                {if $rec_key eq 'modal-detail-row'}
                    {$data|regex_replace:"/modal-detail-row/":"modal-detail-row-{$TAB_HOME_ID}"|unescape:"html"}
				{else}
                    {$data}
                {/if}
			</td>
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
					<div class="col-md-6"  style="margin-top: -.25em; margin-right:-.2em;">{$NAVIGATION|regex_replace:"/getListViewEntries_js/":"HomeUtils.getListViewEntries"|regex_replace:"/\);/":",'{$TAB_HOME_ID}');"|unescape:"html"}</div>
				</div>
			</header>
{block name="last-content"}{/block}
{block name="scripts"}{/block}
{block name="extra-content"}{/block}
		</div>
	</div>
</div>
</div>