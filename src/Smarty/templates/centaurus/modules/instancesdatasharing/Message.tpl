{strip}
{extends file="base/boilerplate.tpl"}
{block name="title"}Invitación a compartir contenido{/block}
{block name="css"}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
<link rel="stylesheet" href="themes/centaurus/css/compiled/store.css" />
<link rel="stylesheet" href="themes/centaurus/css/compiled/section/calculator.css" />
<link rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css" />
{/block}
{block name="body"}
<div class="row invitation">
	<div class="col-xs-12">
		<div id="login-box">
			<header class="main-box-header clearfix" id="login-header">
				<div id="login-logo">
					<a href="http://www.platzilla.com"><img src="themes/centaurus/img/logo-platzilla-vert.png" class="img-responsive" alt="Platzilla" /></a>
				</div>
				<div style="background: white;">
					<hr class="linea">
				</div>
			</header>
			<div id="login-box-inner">
				<h2>{$TITLE}</h2>
				<p>{$MESSAGE}</p>
			</div>
		</div>
	</div>
</div>
{/block}
{/strip}