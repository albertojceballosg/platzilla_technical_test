{strip}
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<div style="opacity: 1;" class="row">
	<div class="col-lg-12">
		<div class="row">
			<div class="col-lg-12">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
					<li class="active">
						<a href="index.php?module=Settings&action=ListProfiles&parenttab=Settings">{$MOD.LBL_PROFILES}</a>
					</li>
				</ol>
				<h1>{$MOD.LBL_PROFILES}</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box no-header clearfix">
					<div class="main-box-body clearfix">
						<div class="row">
							<div class="col-md-9">{$MOD.LBL_PROFILE_DESCRIPTION}</div>
							<div class="col-md-3 text-right">
								<form action="index.php" method="post" name="new" id="form" onsubmit="VtigerJS_DialogBox.block ();">
									<input type="hidden" name="module" value="Users" />
									<input type="hidden" name="mode" value="create" />
									<input type="hidden" name="action" value="CreateProfile" />
									<input type="hidden" name="parenttab" value="Settings" />
									<input type="submit" value="{$CMOD.LBL_NEW_PROFILE}" title="{$CMOD.LBL_NEW_PROFILE}" class="btn btn-primary" />
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<div class="main-box clearfix">
					<header class="main-box-header clearfix">
						<h2></h2>
					</header>
					<div class="main-box-body clearfix">
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
								<tr>
									<th class="text-center"><span></span></th>
									<th class="text-left"><span>{$LIST_HEADER.2}</span></th>
									<th class="text-left"><span>{$LIST_HEADER.3}</span></th>
									<th><span>{$LIST_HEADER.1}</span></th>
								</tr>
								</thead>
								<tbody>
{foreach $LIST_ENTRIES as $listvalues}
								<tr>
									<td class="text-center"><span><i class="fa fa-users"></i></span></td>
									<td>
										<a href="index.php?module=Settings&action=profilePrivileges&mode=view&parenttab=Settings&profileid={$listvalues.profileid}"><b>{$listvalues.profilename}</b></a>
									</td>
									<td>{$listvalues.description}</td>
									<td>
										<a href="index.php?module=Settings&action=profilePrivileges&return_action=ListProfiles&parenttab=Settings&mode=edit&profileid={$listvalues.profileid}" class="table-link" title="Editar">
											<span class="fa-stack">
												<i class="fa fa-square fa-stack-2x"></i>
												<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
											</span>
										</a>
	{if ($listvalues.del_permission == 'yes')}
										<a href="#" onclick="DeleteProfile (this,'{$listvalues.profileid}')" title="{$APP.LBL_DELETE_BUTTON}" class="table-link danger">
											<span class="fa-stack">
												<i class="fa fa-square fa-stack-2x"></i>
												<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
											</span>
										</a>
	{/if}
									</td>
								</tr>
{/foreach}
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="tempdiv" style="display: block; position: absolute; left: 350px; top: 200px;"></div>
<script>
{literal}
	function DeleteProfile (obj, profileid) {
		$ ('status').style.display = 'inline';
		new Ajax.Request (
			'index.php',
			{
				queue: {
					position: 'end',
					scope: 'command'
				},
				method:       'post',
				postBody:     'module=Users&action=UsersAjax&file=ProfileDeleteStep1&profileid=' + profileid,
				onComplete:   function (response) {
					$ ('status').style.display = 'none';
					$ ('tempdiv').innerHTML = response.responseText;
					fnvshobj (obj, 'tempdiv');
				}
			}
		);
	}
{/literal}
</script>
{/strip}