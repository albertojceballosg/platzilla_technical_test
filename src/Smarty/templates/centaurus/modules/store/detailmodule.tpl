<script>
msg = '<form action="index.php" method="POST"><input type="hidden" name="tabname" value="{$listModules[0].cf_961}"><input type="hidden" name="module" value="{$MODULE}"><input type="hidden" name="action" value="addModule">{$MOD.LBL_DESEA_AGREGAR_ESTE_MODULO_A_SU_CUENTA}<br/>';
msg+= '<input type="submit" value="{$APP.LBL_YES}"> <input type="button" value="{$APP.LBL_NO}" onclick="cierraidUI(\'Mensajes\');"></form>';
</script>

<div class="post-header clearfix biggermast" >
	<div>
		<h1>
			<a href="index.php?module=store&action=index">{$MOD.MODULES_AVALIABLES}</a>
		</h1>
	</div>
</div>

<div class="main-box clearfix profile-box-stats">
<div class="main-box-body clearfix">
<div class="profile-box-header green-bg clearfix">
	<h2>{$listModules[0].titulo}</h2>
	<div class="job-position">
		{$listModules[0].categoria}
	</div>
	<img src="{$_URLTOBACK}{$listModules[0].path}{$listModules[0].screenshot}_{$listModules[0].image}" alt="" class="profile-img img-responsive">
</div>
<div class="profile-box-footer clearfix">
	<a href="#">
		<span class="label" style="width:40px">{$listModules[0].introduccion}</span>
	</a>
	<a href="#">
		<span class="label">
				<form method="post" name="freetrial" id="freetrial" action="index.php?action=freetrial">
					<input type="hidden" name="app" value="{$smarty.request.app}"/>
					<buttom onclick="showAlertMessage(msg);" class="btn btn-warning fancybox iframe" style="margin-left: 0px;">Agregue a su cuenta</button>
				</form>
		</span>
	</a>
	<a href="#">
		<span class="label">
				<buttom class="btn btn-info fancybox iframe" style="margin-left: 0px;">Demo ONLINE</button>
		</span>
	</a>
</div>
</div>
</div>
