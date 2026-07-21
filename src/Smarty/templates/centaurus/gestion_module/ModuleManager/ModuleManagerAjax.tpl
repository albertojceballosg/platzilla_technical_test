<script src="include/jquery/jquery.tablednd.js"></script>
<script type="text/javascript">
{literal}
function vtlib_modulemanager_toggleTab(shownode, hidenode, highlighttab, dehighlighttab) {
	if($(shownode)) $(shownode).show();
	if($(hidenode)) $(hidenode).hide();
	if($(highlighttab)) { $(highlighttab).addClassName('dvtSelectedCell'); $(highlighttab).removeClassName('dvtUnSelectedCell'); }
	if($(dehighlighttab)) { $(dehighlighttab).addClassName('dvtUnSelectedCell'); $(dehighlighttab).removeClassName('dvtSelectedCell'); }
}
{/literal}

function hacerCombinable(estado,module)
{ldelim}
			new Ajax.Request('index.php', {ldelim}
				method: 'post',
				postBody: 'module=gestion_module&action=ActivityAjax&modulerel='+module+'&estado='+estado+'&funcion=hacerCombinable&Ajax=true',
				onComplete: function(response) {ldelim}
				{rdelim}
			{rdelim});
{rdelim}
</script>

<button class="md-trigger btn btn-primary mrg-b-lg" data-modal="{$ID_DLG_CREACION_MODULOS}">{$APP.LBL_CREATE_MODULE}</button>
<a class="md-trigger btn btn-primary mrg-b-lg" href="index.php?module=Settings&action=ModuleDuplicator&return_module=gestion_module">{$APP.LBL_DUPLICATE_MODULE}</a>

<!--button class="md-trigger btn btn-warning mrg-b-lg" data-modal="{$ID_DLG_ADMIN_PARENT_MODULES}">{$APP.LBL_ADMIN_PARENT_MODULES}</button-->
<br/>
<br/>
<div class="main-box-body clearfix" style="width:100%">
			<div class="col-lg-12" id="tab-custom">
				<!-- Custom Modules -->
				<table class="table">
					<tr>
					</tr>

				{assign var="totalCustomModules" value="0"}

				{foreach key=modulename item=modinfo from=$TOGGLE_MODINFO}
				{if $modinfo.customized eq true}
					{assign var="totalCustomModules" value=$totalCustomModules+1}

					{assign var="modulelabel" value=$modulename}
					{if $APP.$modulename}{assign var="modulelabel" value=$APP.$modulename}{/if}
					<tr>
						<td class="cellText small" width="20px"><img src="{'uparrow.gif'|@vtiger_imageurl:$THEME}" border="0"></td>
						<td class="cellLabel small">{$modulelabel}</td>
						{*
						<td class="cellText small" width="15px" align=center>
							<a href="index.php?module=gestion_module&action=ModuleManager&module_update=Step1&src_module={$modulename}&parenttab=gestion_module">
							<i class="fa fa-refresh fa-fw fa-lg yellow"></i>
							</a>
						</td>
						*}
						<td class="cellText small" width="15px" align=center>
						{if $modinfo.presence eq 0}
							<a href="javascript:void(0);" onclick="vtlib_toggleModule('{$modulename}', 'module_disable');">
							<i class="fa fa-check-circle fa-fw fa-lg green"></i>
							</a>
						{else}
							<a href="javascript:void(0);" onclick="vtlib_toggleModule('{$modulename}', 'module_enable');">
							<i class="fa fa-times-circle fa-fw fa-lg red"></i>
							</a>
						{/if}
						</td>
						<td class="cellText small" width="15px" align=center>
							{if $modulename eq 'Calendar' || $modulename eq 'Home'}
								<img src="{'menuDnArrow.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle">
							{else}
								<a href="index.php?modules=gestion_module&action=ModuleManagerExport&module_export={$modulename}">
								<i class="fa fa-download fa-fw fa-lg purple"></i>
								</a>
							{/if}
						</td>
						<td class="cellText small" width="15px" align=center>
							{if $modinfo.presence eq 0 && $modinfo.hassettings}
							<a href="index.php?module=gestion_module&action=ModuleManager&module_settings=true&formodule={$modulename}&parenttab=gestion_module">
							<i class="fa fa-gears fa-fw fa-lg emerald"></i>
							</a>
							{elseif $modinfo.hassettings eq false}&nbsp;
							{/if}
						</td>
						<td class="cellText small" width="15px" align=center>
						{if $modinfo.presence eq 0 and $modinfo.isplatzilla eq 0 }
							<a href="index.php?module=Settings&action=eliminarModulo&module_settings=true&formodule={$modulename}&parenttab=Settings&return_module=gestion_module">
							<i class="fa fa-trash-o fa-fw fa-lg red"></i>
							</a>
						{/if}
						</td>
						{*
						<td class="cellText small" width="15px" align=center>
							{if $modinfo.combinable eq "1"}
								{assign var="checked" value="checked"}
							{else}
								{assign var="checked" value=""}
							{/if}
							<input type="checkbox" onclick="if (this.checked) hacerCombinable(true,'{$modulename}'); else hacerCombinable(false,'{$modulename}');" {$checked}/>
						</td>
						*}
					</tr>
				{/if}
				{/foreach}
				{if $totalCustomModules eq 0}
					<tr>
						<td class="cellLabel small" colspan=4><b>{$MOD.VTLIB_LBL_MODULE_MANAGER_NOMODULES}</b></td>
					</tr>
				{/if}
				</table>

			</div>

</div>


{$DLG_CREACION_MODULOS}
