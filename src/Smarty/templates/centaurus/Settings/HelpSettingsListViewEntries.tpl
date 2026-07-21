{strip}
<style type="text/css">
	.action {
		display:    inline-block;
		list-style: none;
	}
	.action .btn {
		font-size:   14px;
		height:      27px;
		line-height: 27px;
		margin:      0 5px 0 0;
		padding:     0;
		text-align:  center;
		width:       27px;
	}
</style>
<table width="100%" cellpadding="5" cellspacing="0" class="table table-striped table-hover">
	<thead>
	<tr>
		<th class="text-left">{$MOD.LBL_APP}</th>
		<th class="text-left" width="9%">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
	</tr>
	</thead>
	<tbody>
{foreach $AYUDA_LIST as $ayuda}
	<tr>
		<td>
			<a href="index.php?module=Settings&action=HelpSettingsDetailView&record={$ayuda.id_ayuda}&parenttab=Settings">{$ayuda.app_name}</a>
		</td>
		<td>
			<ul class="actions">
				<li class="action">
					<a href="index.php?module=Settings&action=HelpSettingsEditView&record={$ayuda.id_ayuda}&parenttab=Settings" class="btn btn-primary" title="{$APP.LBL_EDIT_BUTTON_LABEL}">
						<i class="fa fa-pencil"></i>
					</a>
				</li>
				<li class="action">
					<a href="javascript:confirmdelete('index.php?module=Settings&action=HelpSettingsDelete&record={$ayuda.id_ayuda}')" class="btn btn-danger" title="{$APP.LBL_DELETE_BUTTON_LABEL}">
						<i class="fa fa-trash-o"></i>
					</a>
				</li>
			</ul>
		</td>
	</tr>
{/foreach}
	</tbody>
</table>
{/strip}