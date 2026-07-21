{extends file='base/BaseHomeListView.tpl'}
{block name="css"}
<link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css" />
{/block}
{block name='js'}
{if (!empty ($smarty.request.ajax)) && false}
&#&#&#{$ERROR}&#&#&#
{/if}
<script type="text/javascript" src="include/js/ListView.js"></script>
<script type="text/javascript" src="include/js/Mail.js"></script>
{/block}
{block name='first-content'}{/block}
{block name='scripts'}
<script type="text/javascript">
function viewSearch () {
	var divSearch = jQuery ('#divsearch');

	if (!divSearch.is (':visible')) {
		jQuery ('#imgsearch').removeClass ('fa-search-plus').addClass("fa-search-minus");
		divSearch.show ();
	} else {
		jQuery ('#imgsearch').removeClass ('fa-search-minus').addClass ('fa-search-plus');
		divSearch.hide ();
	}
}
function messageConstruction () {
	alert ('Funcionalidad En construcci\u00f3n');
}
</script>
<script type="text/html" id="instances-data-sharing-share-modal-template">
{include file='modules/instancesdatasharing/ShareModal.tpl'}
</script>
<script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
	<script type="text/javascript" src=	"modules/Home/home-utils.js"></script>
{if ($IS_REL_ACTIVITIES == '1')}
    {include file='CreateTaskWizard.tpl'}
{/if}
{/block}
{block name="header-buttons-row-two" prepend}
<div class="btn-general pull-right btn-control" style="margin-right: 0">
	<div class="col-md-12" style="padding-right: 0">
		<div class="btn-group" style="margin-right:0;">
			<button type="button" class="btn btn-default dropdown-toggle dropdown-toggle-ext" style=" font-size: 15px!important;" data-toggle="dropdown">
				&nbsp;<i class="fa fa-bolt{*fa-tags*}" aria-hidden="true" {*style="margin-top: .17em"*}></i>&nbsp;<span class="caret"></span>
			</button>
			<button type="button" class="btn btn-default dropdown-toggle dropdown-toggle-ext" style=" font-size: 15px!important;" data-toggle="dropdown">
				&nbsp;<i class="fa fa-bolt{*fa-tags*}" aria-hidden="true" {*style="margin-top: .17em"*}></i>&nbsp;<span class="caret"></span>
			</button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li><a href="#" onclick="HomeUtils.massDelete (this, event, '{$MODULE}', '{$TAB_HOME_ID}');" title="{$APP.LBL_TRASH_ALL}"><i class="fa fa-trash-o"></i>Eliminar registros</a></li>
				<li><a href="#" onclick="MassActionsUtils.openMassEditModal ('{$MODULE}','{$TAB_HOME_ID}');" title="Edición masiva"><i class="fa fa-pencil"></i>Editar registros</a></li>
				<li><a href="#" onclick="MassActionsUtils.openMassMailModal ('{$MODULE}','{$TAB_HOME_ID}');" title="Mail masivo" ><i class="fa fa-envelope"></i>Enviar email masivo</a></li>
				<li><a href="#" onclick="DataSharingUtils.openMassSharingModal ('{$MODULE}','{$TAB_HOME_ID}');" title="Compartir" ><i class="fa fa-share"></i>Compartir registros</a></li>
                {if ($IS_REL_ACTIVITIES == '1')}
				<li><a href="#"  onclick="CalendarWizard.open ('{$MODULE}', '', 'inRow')" title="Registar en calendario"><i class="fa fa-calendar"></i>Registrar en calendario</a></li>
                {/if}
			</ul>

		</div>
	</div>
</div>
{/block}
{block name="input-form" append}
<input type="hidden" name="totalrecords" value="{$TOTALRECORD}" />
{/block}
{block name="thead-item" prepend}
<th class="lvtCol" style="vertical-align: center; padding-bottom: 12px;padding-top: 3px;display: none">
	<div class="checkbox-nice checkbox-inline" style="vertical-align: center">
		<input  type="checkbox" name="selectall" id="selectCurrentPageRec-{$TAB_HOME_ID}" data-home-tab-id="{$TAB_HOME_ID}" onclick="HomeUtils.toggleSelectListView (this);">
		<label for="selectCurrentPageRec-{$TAB_HOME_ID}"></label>
	</div>
</th>
{/block}
{block name="tbody" prepend}
<td id="linkForSelectAll" class="linkForSelectAll" style="display: none;" colspan="15">
	<a href="#">
		<span id="selectAllRec" class="selectall" style="display:inline;" onClick="toggleSelectAll_Records('{$MODULE}',true,'selected_id')">{$APP.LBL_SELECT_ALL}
			<span id="count"></span> {$APP.LBL_RECORDS_IN} {$MODULE|@getTranslatedString:$MODULE}
		</span>
	</a>
	<a href="#">
		<span id="deSelectAllRec" class="selectall" style="display:none;" onClick="toggleSelectAll_Records('{$MODULE}',false,'selected_id')">{$APP.LBL_DESELECT_ALL} {$MODULE|@getTranslatedString:$MODULE}</span>
	</a>
</td>
{/block}
{block name="tbody-item" prepend}
<td width="2%" style="vertical-align: center; padding-bottom: 12px;padding-top: 3px;display: none">
	<div class="checkbox-nice checkbox-inline"">
		<input type="checkbox" id="{$entity_id}"   data-home-tab-id="{$TAB_HOME_ID}"   name="selected_id-{$TAB_HOME_ID}" value="{$entity_id}" class="view-item home-tab-check-{$TAB_HOME_ID}" onClick="HomeUtils.checkObject(this)">
		<label for="{$entity_id}"></label>
	</div>
</td>
{/block}
{block name="last-content" append}
{$SELECT_SCRIPT}
{/block}