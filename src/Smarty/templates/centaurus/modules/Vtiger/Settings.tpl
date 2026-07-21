{strip}
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-list-alt emerald-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS} </a></li>
					<li><a href="index.php?module=Settings&action=ModuleManager&parenttab=Settings">{$MOD.VTLIB_LBL_MODULE_MANAGER|upper}</a></li>
					<li class="active">{$MODULE_LBL|strtoupper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Opciones de configuración del módulo</td>
		</tr>
		</tbody>
	</table>
	<div style="float: left; padding-left: 10px; width: 100%;">
{foreach $MENU_ENTRIES as $menuEntry}
		<div class="main-box infographic-box" style="float: left; height: 150px; margin-left: 5px; max-height: 150px; max-width: 280px; width: 280px;">
			<a href="{$menuEntry.location}">
				<i class="{$menuEntry.image_src}"></i>
			</a>
			<span class="headline">
				<a href="{$menuEntry.location}"><b>{$menuEntry.label}</b></a>
			</span>
			<span class="headline">{$menuEntry.desc}</span>
		</div>
{/foreach}
	</div>
</div>
{/strip}