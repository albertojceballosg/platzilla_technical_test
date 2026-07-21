<div style="opacity: 1;" class="row">
	<div class="col-lg-12">
		<div class="row">
			<div class="col-lg-12">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS} </a>
					</li>
					<li class="active">
						<span><a href="index.php?module=Settings&action=listroles&parenttab=Settings">{$CMOD.LBL_ROLES}</a></span>
					</li>
					<li>
						<b>{$ROLE_NAME}</b>
					</li>
				</ol>
				<h1>Perfil</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box no-header clearfix">
					<div class="main-box-body clearfix">
						<div class="row">
							<div class="col-md-6">
								<b> {$ROLE_NAME} </b><br>
								{$CMOD.LBL_VIEWING} {$CMOD.LBL_PROPERTIES} &quot;{$ROLE_NAME}&quot; {$MOD.LBL_LIST_CONTACT_ROLE}
							</div>
							<div class="col-md-6 text-right">
								<form id="form" name="roleView" action="index.php" method="post" onsubmit="VtigerJS_DialogBox.block ();">
									<input value="{$APP.LBL_EDIT_BUTTON_LABEL}" title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="btn btn-primary" type="submit" name="Edit" />
									<input type="hidden" name="module" value="Settings" />
									<input type="hidden" name="action" value="createrole" />
									<input type="hidden" name="parenttab" value="Settings" />
									<input type="hidden" name="returnaction" value="RoleDetailView" />
									<input type="hidden" name="roleid" value="{$ROLEID}" />
									<input type="hidden" name="mode" value="edit" />
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<div class="tabs-wrapper tabs-no-header">
					<ul class="nav nav-tabs">
						<li class="active"><a href="#tab-todo" data-toggle="tab">Información</a></li>
						<li class=""><a href="#tab-users" data-toggle="tab">{$CMOD.LBL_ASSOCIATED_USERS}</a></li>
						<li class=""><a href="#tab-perfiles" data-toggle="tab">{$CMOD.LBL_ASSOCIATED_PROFILES}</a></li>
					</ul>
					<div class="tab-content tab-content-body clearfix">
						<div class="tab-pane fade" id="tab-users">
							<ul class="widget-users row">
{if ($ROLEINFO.userinfo[0] != '')}
	{foreach $ROLEINFO.userinfo as $elements}
								<li class="col-md-6">
									<div class="img">
		{if ($elements[2] == '')}
										<div class="text-center"><i class="fa fa-user"></i></div>
		{else}
										<img src="{$elements[2]}" style="width: 50px" alt="" />
		{/if}
									</div>
									<div class="details">
										<div class="name">
											<a href="#"><a href="index.php?module=Users&action=DetailView&parenttab=Settings&record={$elements[0]}">{$elements[1]} </a></a>
										</div>
										<div class="type">
											<span class="label{if ($elements[3] == 'Active')} label-success{else} label-danger{/if}">{$elements[3]}</span>
										</div>
									</div>
								</li>
	{/foreach}
{/if}
							</ul>
						</div>
						<div class="tab-pane fade" id="tab-perfiles">
							<ul class="widget-products">
{foreach $ROLEINFO.profileinfo as $elements}
								<li class="clearfix">
									<div class="name">
										<i class="fa fa-users"></i>
										<a href="index.php?module=Settings&action=profilePrivileges&parenttab=Settings&profileid={$elements[0]}&mode=view">{$elements[1]}</a>
									</div>
								</li>
{/foreach}
							</ul>
						</div>
						<div class="tab-pane fade active in" id="tab-todo">
							<ul class="widget-todo">
								<li class="clearfix">
									<div class="name">
										{$CMOD.LBL_ROLE_NAME} <span class="label label-success">{$ROLE_NAME}</span>
									</div>
								</li>
								<li class="clearfix">
									<div class="name">
										{$CMOD.LBL_REPORTS_TO} <span class="label label-warning">{$PARENTNAME}</span>
									</div>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="include/js/smoothscroll.js"></script>
