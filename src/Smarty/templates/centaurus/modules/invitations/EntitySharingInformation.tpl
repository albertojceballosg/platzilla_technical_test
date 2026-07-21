{extends file="base/TopNavigation.tpl"}
{block name="css"}
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
	<link rel="stylesheet" href="themes/centaurus/css/compiled/store.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/section/calculator.css">
	<style type="text/css">
		.nextBtn {
			width: 100%;
		}
	</style>
{/block}
{block name="body-content"}
	<div class="row">
		<div class="col-xs-12">
			<div id="login-box">
				<header class="main-box-header clearfix" id="login-header">
					<div id="login-logo">
						<a href="http://www.platzilla.com"><img alt="" src="themes/centaurus/img/logo-platzilla-vert.png"></a>
					</div>
					<div style="background: white;">
						<hr class="linea">
					</div>
				</header>
				<div id="login-box-inner">
					<h2>Has recibido una invitación a compartir entidad de Platzilla. Esto significa que:</h2>
					<ul>
						<li>Blah, Blah Blah Blah</li>
						<li>Blah, Blah Blah Blah</li>
						<li>Blah, Blah Blah Blah</li>
						<li>Blah, Blah Blah Blah</li>
						<li>Blah, Blah Blah Blah</li>
					</ul>
					<form role="form" action="index.php?module=store&action=createInstanceFromInvitation" method="post" name="signup-form">
						<input type="hidden" name="invitation" value="{$INVITATION}" />
						<input type="hidden" name="instance" value="{$INSTANCE}" />
						<button type="submit" class="btn btn-success nextBtn" data-current-step="step-2">Estoy de acuerdo. Proceder
							<span class="fa fa-arrow-right"></span>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
{/block}