{extends file="base/box-frame.tpl"}
{block name="header-logo"}
<img alt="" src="themes/centaurus/img/logo-platzilla-vert.png">
{/block}
{block name="box-title"}
<h1>Ingreso</h1>
{/block}
{block name="box-form"}
<form role="form" action="index.php" method="post" name="DetailView" id="form">
	<input type="hidden" name="module" value="Users" />
	<input type="hidden" name="action" value="Authenticate" />
	<input type="hidden" name="impersonationtoken" value="{$IMPERSONATION_TOKEN}" />
	<div class="input-group col-xs-12">
		<input name="instancecode" class="form-control login-form" placeholder="Código de la instancia" type="text" />
	</div>
	<div class="row">
		<div class="col-xs-12">
			<input type="submit" class="btn btn-success col-xs-12" value="Entrar">
		</div>
	</div>
</form>
{/block}
{block name="footer"}
	{include file="base/Footer.tpl"}
{/block}