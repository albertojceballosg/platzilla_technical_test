{strip}
{* Favicon *}
<link type="image/x-icon" href="themes/{$THEME}/favicon.png" rel="shortcut icon" />
{* google font libraries *}
<link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css' />
{* bootstrap *}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/store/css/bootstrap/bootstrap.min.css" />
{* global styles *}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/store/css/compiled/theme_styles.css" />
{* this page specific styles *}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/store/css/style.css" />
<link rel="stylesheet" type="text/css" href="modules/store/store.css" />
{literal}
<style type="text/css" media="screen">
	.error {
		color:     red;
		font-size: 10px;
		height:    12px;
	}
	#footer-bar {
		right: 15px;
	}
</style>
{/literal}
<div class="container">
	<div class="row title-content">
		<div class="col-xs-12">
			<h1><strong>Zona de Aplicaciones</strong></h1>
		</div>
	</div>
	<div class="main-box clearfix">
		<div class="tabs-wrapper">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-disponibles" data-toggle="tab">Aplicaciones disponibles</a></li>
				<li><a href="#tab-contratadas" data-toggle="tab">Aplicaciones en uso</a></li>
			</ul>
{assign var='colors' value=array('yellow', 'green', 'blue', 'red')}
			<div class="tab-content col-xs-12 clearfix">
				<div class="tab-pane fade in active" id="tab-disponibles">
					<div class="row">
						<div class="col-xs-12">
							<h2 class="title-description">Expande tu plataforma según las necesidades de tu empresa agregando aplicaciones de nuestro catálogo</h2>
{foreach $CATEGORIES as $category}
	{assign var='color' value=$colors[($category@index % 4)]}
	{if (empty ($AVAILABLE_APPLICATIONS[$category.name]))}
		{continue}
	{/if}
							<div class="app-container-box app-container-{$color} app-container-bottom first">
								<div class="app-container-{$color} app-container-bottom">
									<div class="bg-{$color} app-container-label">
										<span class="label">{$category.name}</span>
									</div>
								</div>
								<div class="tab-app clearfix">
									<div class="row apps-wrapper">
	{foreach $AVAILABLE_APPLICATIONS[$category.name] as $index => $application}
										<div class="col-xs-12 col-md-4 app">
											<div class="row">
												<div class="col-xs-5 text-center ">
													<div class="app-icon">
														<img src="{$APPSIMAGE_PATH}/{$application->getCode ()}.png" alt="{$application->getName ()}" class="img-circle" />
													</div>
												</div>
												<div class="col-xs-7">
													<h2 class="app-title">{$application->getName ()}</h2>
													<p class="app-description">{$application->getDescription ()}</p>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-12 text-center">
													<button type="button" class="btn btn-primary" onclick="StoreUtils.addApplication ('{$application->getCode ()}')">
														<strong>AÑADIR</strong>
													</button>
												</div>
											</div>
										</div>
	{/foreach}
									</div>
								</div>
							</div>
{/foreach}
						</div>
					</div>
				</div>
				<div class="tab-pane fade in" id="tab-contratadas">
					<div class="row">
						<div class="col-xs-12">
							<h2 class="title-description">Acá encontrarás la lista de aplicaciones que tienes contratadas</h2>
{foreach $CATEGORIES as $category}
	{assign var='color' value=$colors[($category@index % 4)]}
	{if (empty ($INSTALLED_APPLICATIONS[$category.name]))}
		{continue}
	{/if}
							<div class="app-container-box app-container-{$color} app-container-bottom first">
								<div class="app-container-{$color} app-container-bottom">
									<div class="bg-{$color} app-container-label">
										<span class="label">{$category.name}</span>
									</div>
								</div>
								<div class="tab-app clearfix">
									<div class="row apps-wrapper">
	{foreach $INSTALLED_APPLICATIONS[$category.name] as $application}
										<div class="col-xs-12 col-md-4 app">
											<div class="row">
												<div class="col-xs-5 text-center ">
													<div class="app-icon">
														<img src="{$APPSIMAGE_PATH}/{$application->getCode ()}.png" alt="{$application->getName ()}" class="img-circle" />
													</div>
												</div>
												<div class="col-xs-7">
													<h2 class="app-title">{$application->getName ()}</h2>
													<p class="app-description">{$application->getDescription ()}</p>
												</div>
											</div>
										</div>
	{/foreach}
									</div>
								</div>
							</div>
{/foreach}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="modules/store/store.js"></script>
{/strip}