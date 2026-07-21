<div class="md-modal md-effect-1" id="modal-1">
		<div class="md-content">
			<div class="modal-header">
				<button class="md-close close">&times;</button>
				<h4 class="modal-title">{$MOD.LBL_DESEA_AGREGAR_ESTA_APP_A_SU_CUENTA}</h4>
			</div>
			<form action="index.php" method="POST">
				<input type="hidden" name="app" value="{$smarty.request.app}"/>
				<input type="hidden" name="tabname" value="{$listModules[0].cf_961}">
				<input type="hidden" name="module" value="{$MODULE}">
				<input type="hidden" name="action" value="addApp">
				<div class="modal-body">

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-default">{$APP.LBL_YES}</button>
					<button type="button" class="btn btn-warning" data-dismiss="modal" onclick="jQuery('#modal-1').removeClass('md-show')">{$APP.LBL_NO}</button>
				</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="post-header clearfix biggermast" >
	<div>
		<h1>
			<a href="index.php?module=store&action=index">{$MOD.APPLICATIONS_AVALIABLES}</a>
		</h1>
	</div>
</div>

<div class="main-box clearfix profile-box-stats">
<div class="main-box-body clearfix">
<div class="profile-box-header green-bg clearfix">
	<h2>{$listApplications[0].name}</h2>
	<img src="{$_URLTOBACK}{$listApplications[0].path}{$listApplications[0].app_icon}_{$listApplications[0].image}" alt="" class="profile-img img-responsive">
</div>
<div class="profile-box-footer clearfix">
	<p style="padding:20px;text-align:justify;">
		{$listApplications[0].description|nl2br}
		<br/>
		<br/>
		<span class="label">
			<button class="md-trigger btn btn-primary mrg-b-lg" data-modal="modal-1">Agregue a su cuenta</button>
		</span>
	</p>
</div>
</div>
</div>

<!-- this page specific scripts -->
<script src="themes/centaurus/js/modernizr.custom.js"></script>
<script src="themes/centaurus/js/classie.js"></script>
<script src="themes/centaurus/js/modalEffects.js"></script>
