<div id="email-box" class="clearfix">
	<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;">
					<i class="fa fa-cubes green-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
					<li>{$MOD.CONFIG_APPS}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_CONFIG_APPS_DESCRIPTION}</td>
		</tr>
	</table>
{if (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<br />
				<div class="pull-right" style="margin-right: 20px;">
					<a class="btn btn-info" href="index.php?module=Settings&action=CategoryApps">{$MOD.LBL_CATEGORYAPPS_BUTTON_LABEL}</a>
					<a class="btn btn-primary" href="index.php?module=Settings&action=CreateApp">{$MOD.LBL_CREATE_BUTTON_LABEL}</a>
					<a class="btn btn-primary" href="index.php?module=Settings&action=AppDuplicator">{$MOD.LBL_DUPLICATE_BUTTON_LABEL}</a>
				</div>
				<br />
				<div class="main-box-body clearfix">
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table-responsive">
						<tr>
							<th><h2>{$MOD.CONFIG_APPS_TITLE}</h2></th>
							<td align="right">&nbsp;</td>
						</tr>
					</table>
					<br />
					<div id="appscontents">
{include file='Settings/ConfigAppsContents.tpl'}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
