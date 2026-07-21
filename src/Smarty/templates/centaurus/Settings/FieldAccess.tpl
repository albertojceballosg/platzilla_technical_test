{strip}
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript" src="include/js/menu.js"></script>
<form action="index.php" method="post" name="new" id="form" onsubmit="VtigerJS_DialogBox.block ();">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="parenttab" value="Settings" />
	<input type="hidden" name="fld_module" id="fld_module" value="{$DEF_MODULE}" />
	<input type="hidden" name="action" value="{if ($MODE != 'view')}UpdateDefaultFieldLevelAccess{else}EditDefOrgFieldLevelAccess{/if}" />
	<div class="col-lg-12">
		<div class="row">
			<div class="col-lg-12">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
					<li>{$MOD.LBL_FIELDS_ACCESS}</li>
				</ol>
			</div>
		</div>
	</div>
	<div class="col-lg-12">
		<div class="row">
			<div class="main-box no-header clearfix" style="">
				<div class="col-lg-6 pull-left">
					<h2>{$MOD.LBL_SHARING_FIELDS_DESCRIPTION}</h2>
					<label for="screen">{$CMOD.LBL_SELECT_SCREEN}</label>
					<select name="Screen" id="screen" class="form-control" style="width:30%; margin-bottom: 20px;" onChange="changemodules (this);">
{foreach $FIELD_INFO as $moduleName => $moduleLabel}
						<option value="{$moduleName}"{if ($moduleName == $DEF_MODULE)} selected="selected"{/if}>{$moduleLabel}</option>
{/foreach}
					</select>
				</div>
				<div class="col-lg-3 pull-right">
{if ($MODE != 'edit')}
					<input name="Edit" type="submit" class="btn btn-primary pull-right" value="{$APP.LBL_EDIT_BUTTON}" style="margin-left: 10px;" />
{else}
					<input title="save" accessKey="S" class="btn btn-primary pull-right" type="submit" name="Save" value="{$APP.LBL_SAVE_LABEL}" style="margin-left: 10px;" />
					<input name="Cancel" value=" {$APP.LBL_CANCEL_BUTTON_LABEL} " class="btn btn-cancel pull-right" type="button" onClick="window.history.back ();" style="margin-left: 10px;" />
{/if}
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<div class="main-box-body clearfix">
{foreach $FIELD_LISTS as $module => $info}
	{foreach $FIELD_INFO as $moduleName => $moduleLabel}
		{if ($moduleName == $module)}
			{assign var='MODULE_NAME' value=$moduleLabel}
			{break}
		{/if}
	{/foreach}
					<div id="{$module}_fields" style="display: {if ($module == $DEF_MODULE)}block{else}none{/if};">
						<table cellspacing="0" cellpadding="5" width="100%" class="table-responsive">
							<tr>
								<td colspan="2" valign="top" nowrap>
									<b>{$CMOD.LBL_FIELDS_AVLBL} {$MODULE_NAME}</b>
								</td>
							</tr>
							<tr>
								<td valign="top" width="25%">
									<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table-responsive">
	{foreach $info as $elements}
										<tr>
		{foreach $elements as $elementinfo}
											<td class="prvPrfTexture" style="width: 20px;">&nbsp;</td>
											<td width="5%" id="{$module@iteration}_{$elements@iteration}_{$elementinfo@iteration}">{$elementinfo.1}</td>
											<td width="25%" nowrap onMouseOver="this.className='prvPrfHoverOn'; $('{$module@iteration}_{$elements@iteration}_{$elementinfo@iteration}').className='prvPrfHoverOn'" onMouseOut="this.className='prvPrfHoverOff'; $('{$module@iteration}_{$elements@iteration}_{$elementinfo@iteration}').className='prvPrfHoverOff'">{$elementinfo.0}</td>
		{/foreach}
										</tr>
	{/foreach}
									</table>
								</td>
							</tr>
						</table>
					</div>
{/foreach}
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
	var def_field = '{$DEF_MODULE}_fields';
{literal}
	function changemodules (selectmodule) {
		hide (def_field);
		var module = selectmodule.options[ selectmodule.options.selectedIndex ].value;
		document.getElementById ('fld_module').value = module;
		def_field = module + '_fields';
		show (def_field);
	}
</script>
{/literal}
{/strip}