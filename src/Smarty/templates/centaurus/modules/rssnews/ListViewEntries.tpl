{extends file="base/BaseList.tpl"}
{block name ='js'}
{strip}
	{if ($smarty.request.ajax != '')}
		&#&#&#{$ERROR}&#&#&#
	{/if}
	<script type="text/javascript" src="include/js/ListView.js"></script>
	<script type="text/javascript" src="include/js/Mail.js"></script>
{/strip}
{/block}
{block name='scripts'}
{strip}
	<script type="text/javascript">
	{literal}
		function viewSearch () {
			var divSearch = jQuery ("#divsearch"),
					imgSearch = jQuery ("#imgsearch");
			if (!divSearch.is (':visible')) {
				imgSearch.removeClass ("fa-search-plus").addClass ("fa-search-minus");
				divSearch.show ();
			} else {
				imgSearch.removeClass ("fa-search-minus").addClass ("fa-search-plus");
				divSearch.hide ();
			}
		}

		function createMassRepercussions () {
			var selectedNews = jQuery ('input[name="selected_id"]:checked'),
				n = selectedNews.length,
				i, ids = [];
			if (n === 0) {
				alert ('Selecciona al menos una noticia');
				return;
			}

			for (i = 0; i < n; i += 1) {
				ids.push (selectedNews [i ].id);
			}
			window.open ('index.php?module=repercusiones_prensa&action=MassCreate&ids=' + ids.join (','), '_blank');
		}
	{/literal}
	</script>
{/strip}
{/block}
{block name='header-buttons-row-two' prepend}
{strip}
	<div class="btn-general pull-left col-lg-2 btn-control">
		<div class="btn-group">
			<a href="javascript:void(0)" onclick="return massDelete ('{$MODULE}');" title="{$APP.LBL_TRASH_ALL}" class="btn btn-default">
				<span class="fa fa-trash-o"></span>
			</a>
			<a href="javascript:void(0)" onclick="return createMassRepercussions ();" title="Crear repercusiones" class="btn btn-primary" style="margin: 0 5px;">
				<span class="fa fa-asterisk"></span>
			</a>
	{if $IS_REL_ACTIVITIES eq '1'}
			&nbsp;&nbsp;
			<a href="javascript:void(0)" onclick="return massActivities('{$MODULE}');" title="{$APP.LBL_CALENDAR_ALL}" class="btn btn-default" style="margin-left: 2px;">
				<span class="fa fa-calendar"></span>
			</a>
	{/if}
		</div>
	</div>
{/strip}
{/block}
{block name='input-form' append}
{strip}
	<input type="hidden" name="totalrecords" value="{$TOTALRECORD}" />
{/strip}
{/block}
{block name='thead-item' prepend}
{strip}
	<th class="lvtCol">
		<div class="checkbox-nice checkbox-inline">
			<input name="selectall" id="selectCurrentPageRec" onclick="toggleSelect_ListView (this.checked, 'selected_id')" type="checkbox">
			<label for="selectCurrentPageRec"></label>
		</div>
	</th>
{/strip}
{/block}
{block name='tbody' prepend}
{strip}
	<td id="linkForSelectAll" class="linkForSelectAll" style="display:none;" colspan=15>
		<a href="#">
			<span id="selectAllRec" class="selectall" style="display:inline;" onClick="toggleSelectAll_Records ('{$MODULE}', true, 'selected_id')">{$APP.LBL_SELECT_ALL}
				<span id="count"> </span>
				{$APP.LBL_RECORDS_IN} {$MODULE|@getTranslatedString:$MODULE}
			</span>
		</a>
		<a href="#">
			<span id="deSelectAllRec" class="selectall" style="display:none;" onClick="toggleSelectAll_Records('{$MODULE}',false,'selected_id')">{$APP.LBL_DESELECT_ALL} {$MODULE|@getTranslatedString:$MODULE}</span>
		</a>
	</td>
{/strip}
{/block}
{block name='tbody-item' prepend}
{strip}
	<td width="2%">
		<div class="checkbox-nice checkbox-inline">
			<input type="checkbox" name="selected_id" id="{$entity_id}" value='{$entity_id}' onClick="check_object (this)">
			<label for="{$entity_id}"></label>
		</div>
	</td>
{/strip}
{/block}
{block name='last-content' append}
{strip}
	{$SELECT_SCRIPT}
{/strip}
{/block}