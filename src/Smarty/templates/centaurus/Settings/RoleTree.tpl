{strip}
<style type="text/css">
	a.x {
		color:           black;
		text-align:      center;
		text-decoration: none;
		padding:         5px;
		font-weight:     bold;
	}
	a.x:hover {
		color:           #333333;
		text-decoration: underline;
		font-weight:     bold;
	}
</style>
{foreach $ROLES as $roleId => $children}
<ol class="dd-list" id="{$roleId}">
	<li class="dd-item dd-item-list">
	{if ($ROLE_DETAILS[$roleId][1] == 0)}
		<div class="dd-handle-list"><i class="fa fa-institution"></i></div>
		<div class="dd-handle">
			{$ROLE_DETAILS[$roleId][0]}
			<div class="nested-links">
				<span class="badge badge-primary">
					<a href="index.php?module=Settings&action=RoleEditView&parenttab=Settings&parentroleid={$roleId}"><i class="glyphicon glyphicon-plus"></i></a>
				</span>
			</div>
		</div>
	{else}
		<div class="dd-handle-list"><i class="fa fa-user"></i></div>
		<div class="dd-handle{if ($ROLE_DETAILS[$roleId][1] == '1')} green-bg txt-white-hover{elseif ($ROLE_DETAILS[$roleId][1] == '2')} yellow-bg txt-white-hover{elseif ($ROLE_DETAILS[$roleId][1] == '3')} emerald-bg txt-white-hover{elseif ($ROLE_DETAILS[$roleId][1] == '4')} white-bg txt-white-hover{/if}">
			<b style="font-weight: bold; margin: 0; padding: 0; cursor:pointer;">
				<img src="{'minus.gif'|@vtiger_imageurl: $THEME}" id="img_{$roleId}" border="0" alt="{$APP.LBL_EXPAND_COLLAPSE}" title="{$APP.LBL_EXPAND_COLLAPSE}" align="absmiddle" onClick="showhide('{$ROLE_DETAILS[$roleId][2]}', 'img_{$roleId}')" style="cursor: pointer;">
			</b>
			<a href="index.php?module=Settings&action=RoleDetailView&roleid={$roleId}&parenttab=Settings" class="x" id="user_{$roleId}" onclick="put_child_ID ('user_{$roleId}');">{$ROLE_DETAILS[$roleId][0]}</a>
			<div class="nested-links">
				<span class="badge badge-primary">
					<a href="index.php?module=Settings&action=RoleEditView&parentroleid={$roleId}&parenttab=Settings"><i class="glyphicon glyphicon-plus"></i>1</a>
				</span>
				<span class="badge badge-info">
					<a href="index.php?module=Settings&action=RoleEditView&roleid={$roleId}&parenttab=Settings"><i class="glyphicon glyphicon-pencil"></i></a>
				</span>
		{if ($roleId != 'H1') && ($roleId != 'H2')}
				<span class="badge badge-danger">
					<a href="index.php?module=Settings&action=RoleDeleteStep1&parenttab=Settings&roleid={$roleId}"><i class="glyphicon glyphicon-remove"></i></a>
				</span>
		{/if}
			</div>
		</div>
	{/if}
	</li>
	{if (count ($children) > 0)}
		{include file="Settings/RoleTree.tpl" ROLES=$children}
	{/if}
</ol>
{/foreach}
{/strip}