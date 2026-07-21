<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript" src="include/js/menu.js"></script>
<script type="text/javascript" src="include/js/picklist.js"></script>
<script type="text/javascript" src="include/scriptaculous/scriptaculous.js"></script>
<script type="text/javascript" src="modules/Home/Homestuff.js"></script>
{if $RETURN_MODULE neq ''}
<div class="row hide-div">
	<div class="col-lg-12">
		<ol class="breadcrumb">
			<li class="active">Volver a <a href="index.php?module={$RETURN_MODULE}&action=ListView"><h1>{$RETURN_MODULE|getTranslatedString:$RETURN_MODULE}</h1></li>
		</ol>
	</div>
</div>
{/if}
<div class="row hide-div">
	<div class="col-lg-12">
		<ol class="breadcrumb">
			<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
			<li class="active"><span>{$MOD.LBL_PICKLIST_EDITOR}</span></li>
		</ol>
	</div>
</div>
<div class="row hide-div">
	<div class="col-lg-12">
		<div class="main-box no-header">
			<div class="main-box-body clearfix">{$MOD.LBL_PICKLIST_DESCRIPTION}</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box no-header clearfix">
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table user-list table-stripped">
						<thead class="hide-div">
						<tr>
							<th><span>{$MOD.LBL_SELECT_MODULE}</span></th>
							<th>
								<select name="pickmodule" id="pickmodule" class="form-control" onChange="changeModule();" title="">
{foreach key=modulelabel item=module from=$MODULE_LISTS}
	{if $APP.$module}
		{assign var="modulelabel" value=$APP.$module}
	{/if}
	{* Si viene de un módulo se muestra solamente esa opción. *}
	{if $RETURN_MODULE neq ''}
		{if $RETURN_MODULE eq $module}
									<option value="{$module}" selected>{$modulelabel}</option>
		{/if}
		{* Si no viene de un módulo se muestran todas las opciones. *}
	{else}
		{if $MODULE eq $module}
									<option value="{$module}" selected>{$modulelabel}</option>
		{else}
									<option value="{$module}">{$modulelabel}</option>
		{/if}
	{/if}
{/foreach}
								</select>
							</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td colspan="2">
								<div id="picklist_datas">
{include file='modules/PickList/PickListContents.tpl'}
								</div>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="actiondiv" style="display: block; position: absolute;"></div>
<div id="editdiv" style="display: block; position: absolute; width: 510px;"></div>
<script type="text/javascript">
	window.addEventListener ('load', changeModule, false);
</script>
{if $RETURN_MODULE neq ''}
<script type="text/javascript">
	jQuery (function () {
		jQuery ('#pickmodule').trigger ('change');
	});
</script>
{/if}