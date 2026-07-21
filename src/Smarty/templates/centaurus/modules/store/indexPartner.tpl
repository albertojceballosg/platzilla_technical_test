<link rel="stylesheet" href="themes/listapplications.css" />
<div class="menuwrap">
	<div id="menu-under-header" class="menu-navigation navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<div class="nav-collapse above-header-nav-collapse pull-right">
					<div class="menu-header-container pull-right">
						<ul id="menu-main-navigation" class="nav nav-menu pull-right menu">
								<li class=" menu-item menu-item-type-post_type menu-item-object-page post-title entry-title"  style="font-size: 26px;">
									<a href="index.php?module=store&action=index&parenttab={$PARENT_TAB}" class="">Aplicaciones</a>
								</li>
								<li class=" menu-item menu-item-type-post_type menu-item-object-page post-title entry-title" style="font-size: 26px;">
									<a href="index.php?module=store&action=modules&parenttab={$PARENT_TAB}" class="">M&oacute;dulos</a>
								</li>

						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="post-header clearfix biggermast" style="background-image:url(http://empresafacil.platzilla.com/empresafacil/storage/2014/April/week5/38381_appsHeader1280.jpg);background-size: cover;height:80px;">
	<div class="title-wrap clearfix" style="padding-left: 132px;">
		<h1 class="post-title entry-title">
			Plataforma Base
		</h1>
	</div>
</div>
<section id="applications" class="row mtop-thirty box-large border-shadowgrey">
			<!--
			<div class="oe_centeralign oe_websiteonly">
				<a href="javascript:void(0)" onclick="jQuery('#{$ID_DLG_CREACION_APLICACION}').slideDown(OpenClosecortina());" class="btn btn-warning fancybox iframe" style="margin-left: 0px;">{$APP.LBL_CREATE_APLICATION}</a>
            </div>
			-->
				<div class="oe_demo oe_picture oe_screenshot">
                <div style="float:left">

					<a href="http://demoplat.platzilla.com" target="_demo">
					<img style="height:185px;/*width:360px*/" src="empresafacil/storage/2014/July/week3/40768_demoplatzilla.png">
					</a>
				</div>
				<div style="float:left">
				<p class="oe_mt32" style="float: left;margin-left: 50px;width:400px;">
					<p style="color: rgb(0, 0, 0); font-family: Arial, Helvetica, sans-serif; font-size: 11px;">
	Usuario: admin</p>
<p style="color: rgb(0, 0, 0); font-family: Arial, Helvetica, sans-serif; font-size: 11px;">
	Clave: admin</p>
				</p>
				<div class="oe_demo_footer oe_centeralign">Demo Online</div>
				</div>
				</div>

            </div>
        </div>
</section>


<div class="post-header clearfix biggermast" >
	<div class="title-wrap clearfix" style="padding-left: 132px;">
		<h1 class="post-title entry-title">
			{$MOD.APPLICATIONS_SATELLITE}
		</h1>
	</div>
</div>
<section id="listapplications" class="row mtop-thirty box-large border-shadowgrey">
		<!--h2 class="oe_slogan">Platzilla Apps</h2>
		<h3 class="oe_slogan">Search Apps &amp; Modules</h3-->
		<ul class="ch-grid">
			{foreach from=$listApplications item=app}
			<li>
				<div class="ch-item" style="background-image: url({$_URLTOBACK}{$app.path}{$app.app_icon}_{$app.image});" onclick="window.location.href='index.php?module={$MODULE}&action=Detail&record={$app.aplicationsid}';">
					<div class="ch-info">
						<h3>{$app.short_name}</h3>
						<p>
							{$app.name}
							<a href="index.php?module={$MODULE}&action=Detail&record={$app.aplicationsid}">Ver Detalles</a></p>
					</div>
				</div>
			</li>
			{/foreach}
		</ul>
</section>

<!--
<div class="post-header clearfix biggermast" style="background-image:url(http://empresafacil.platzilla.com/empresafacil/storage/2014/April/week5/38381_appsHeader1280.jpg);background-size: cover;height:80px;">
	<div class="title-wrap clearfix" style="padding-left: 132px;">
		<h1 class="post-title entry-title">
			{$MOD.APPLICATIONS_EASIES}
		</h1>
	</div>
</div>
<section id="listapplications2" class="row mtop-thirty box-large border-shadowgrey">
		<ul class="ch-grid">
			{foreach from=$listApplications2 item=app}
			<li>
				<div class="ch-item" style="background-image: url({$_URLTOBACK}{$app.path}{$app.app_icon}_{$app.image});" onclick="window.location.href='index.php?module={$MODULE}&action=Detail&record={$app.aplicationsid}';">
					<div class="ch-info">
						<h3>{$app.short_name}</h3>
						<p>
							{$app.name}
							<a href="index.php?module={$MODULE}&action=Detail&record={$app.aplicationsid}">Ver Detalles</a></p>
					</div>
				</div>
			</li>
			{/foreach}
		</ul>
</section>

-->