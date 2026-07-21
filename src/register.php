<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Registro - Platzilla Management</title>
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/font-awesome.css" />
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/theme_styles.css?v=1.2" />
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/theme_custom.css?v=1.1" />
	<link rel="stylesheet" type="text/css" href="modules/Walkthrough/Walkthrough.css?v=1.1" />
	<style type="text/css">
		#bell-num {
			padding:               7px 9px;
			background:            #CC0000;
			color:                 #FFFFFF;
			font-weight:           bold;
			margin-left:           2px;
			border-radius:         12px;
			-moz-border-radius:    12px;
			-webkit-border-radius: 12px;
			position:              absolute;
			margin-top:            -35px;
			font-size:             11px;
		}
		.navbar-nav > li > a {
			padding: 15px;
		}
		.modal-backdrop {
			bottom:  0;
			left:    0;
			right:   0;
			top:     0;
			z-index: 1039;
		}
		.missing {
			border-color: #990000 !important;
		}
		@media (max-width: 768px) {
			#logo {
				width:   50px;
				padding: 8px 5px;
			}
			#logo img {
				width:  35px;
				height: 30px;
			}
			.navbar-nav .profile-dropdown .dropdown-menu {
				left: -214%;
			}
		}
		@media (max-width: 400px) {
			.navbar-nav .profile-dropdown .dropdown-menu {
				left: -395%;
			}
		}
	</style>
	<link rel="apple-touch-icon" sizes="57x57" href="favicon/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="favicon/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="favicon/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="favicon/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="favicon/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="favicon/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="favicon/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="favicon/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192" href="favicon/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="favicon/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
	<link rel="manifest" href="favicon/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400" />
	<link rel="stylesheet" type="text/css" href="modules/Walkthrough/Walkthrough.css" />
	<!--[if lt IE 9]>
	<script type="text/javascript" src="themes/centaurus/js/html5shiv.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/respond.min.js"></script>
	<![endif]-->
	<script type="text/javascript" src="themes/centaurus/js/jquery.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap.js"></script>
</head>
<body class="pace-done">
	<div class="page-wrap">
		<header class="navbar nav-small" id="header-navbar">
			<div class="container" style="padding-left: 0;">
				<div class="visible-xs hidden-sm hidden-md hidden-lg"><a id="logo" class="navbar-brand" style=""><img src="/test/logo/platzi-logo-for-mobile.png"></a></div>
				<div>
					<button class="navbar-toggle" data-target=".navbar-ex1-collapse" data-toggle="collapse" type="button"><span class="sr-only">Toggle navigation</span><span class="fa fa-bars"></span></button>
					<div class="nav-no-collapse navbar-left pull-left hidden-sm hidden-xs">
						<ul class="nav navbar-nav pull-left">
							<li><a class="btn" id="make-small-nav"><i class="fa fa-bars"></i></a></li>
							<li><a class="btn" id="status" style="display: none;"><i class="fa fa-spinner fa-spin"></i></a></li>
						</ul>
					</div>
					<div class="hidden-xs hidden-sm pull-left"><a href="index.php?module=Home&action=ViewSubscriptionDetails"><p class="text-warning" style="font-size: 14px; font-weight: 300; line-height: 50px; margin: 0;">Te quedan 14 días de prueba</p></a></div>
					<div class="nav-no-collapse hidden-xs hidden-sm col-md-push-1 col-md-4">
						<form role="search" name="UnifiedSearch" method="get" action="index.php" style="margin: 8px 0 0 0;" onsubmit="VtigerJS_DialogBox.block();">
							<div class="form-group" style="margin: 0;"><input type="hidden" name="action" value="UnifiedSearch" style="margin: 0;"><input type="hidden" name="module" value="Home" style="margin: 0;"><input type="hidden" name="parenttab" value="Settings" style="margin: 0;"><input type="hidden" name="search_onlyin" value="--USESELECTED--" style="margin: 0;"><input type="text" name="query_string" class="form-control" placeholder="Buscar"></div>
						</form>
					</div>
					<div class="nav-no-collapse navbar-right pull-right" id="header-nav">
						<ul class="nav navbar-nav pull-right">
							<li id="nav-bell"><a href="index.php?action=notificationAjax&module=notification_center&file=notificationModal&Ajax=true" class="btn" data-title="Centro de Notificaciones" data-width="850" data-toggle="lightbox" data-parent="" data-gallery="remoteload"><i class="fa fa-bell"></i><span class="hide" id="bell-num" style="color: white; background-color: red;font-size: 0.6em; padding: 0 2px;position: relative; top: -10px"></span></a></li>
							<li class="dropdown"><a href="#" class="dropdown-toggle btn" data-toggle="dropdown"><i class="fa fa-plus"></i></a>
								<ul class="dropdown-menu" style="max-height: 90vh; overflow-x: hidden; overflow-y: auto;">
									<li><a href="index.php?module=todotasks&action=EditView"><i class="fa fa-plus-circle"></i>Crear Actividad</a></li>
									<li><a href="index.php?module=almacenes&action=EditView"><i class="fa fa-plus-circle"></i>Crear Almacenes</a></li>
									<li><a href="index.php?module=articulos&action=EditView"><i class="fa fa-plus-circle"></i>Crear Artículos</a></li>
									<li><a href="index.php?module=clientes&action=EditView"><i class="fa fa-plus-circle"></i>Crear Clientes</a></li>
									<li><a href="index.php?module=orden_de_compra&action=EditView"><i class="fa fa-plus-circle"></i>Crear Compras</a></li>
									<li><a href="index.php?module=contactos&action=EditView"><i class="fa fa-plus-circle"></i>Crear Contacto</a></li>
									<li><a href="index.php?module=contratos_de_servicio&action=EditView"><i class="fa fa-plus-circle"></i>Crear Contratos</a></li>
									<li><a href="index.php?module=presupuestos_cotizacion&action=EditView"><i class="fa fa-plus-circle"></i>Crear Cotizaciones</a></li>
									<li><a href="index.php?module=Documents&action=EditView"><i class="fa fa-plus-circle"></i>Crear Documento</a></li>
									<li><a href="index.php?module=equipamientos&action=EditView"><i class="fa fa-plus-circle"></i>Crear Equipamientos</a></li>
									<li><a href="index.php?module=etapas_proyecto&action=EditView"><i class="fa fa-plus-circle"></i>Crear Etapas Proyecto</a></li>
									<li><a href="index.php?module=facturas&action=EditView"><i class="fa fa-plus-circle"></i>Crear Factura</a></li>
									<li><a href="index.php?module=impuestos&action=EditView"><i class="fa fa-plus-circle"></i>Crear Impuestos</a></li>
									<li><a href="index.php?module=incidencias&action=EditView"><i class="fa fa-plus-circle"></i>Crear Incidencias</a></li>
									<li><a href="index.php?module=oportunidades&action=EditView"><i class="fa fa-plus-circle"></i>Crear Oportunidades </a></li>
									<li><a href="index.php?module=pedidos&action=EditView"><i class="fa fa-plus-circle"></i>Crear Pedidos</a></li>
									<li><a href="index.php?module=plan_de_mantenimiento&action=EditView"><i class="fa fa-plus-circle"></i>Crear Plan de servicios</a></li>
									<li><a href="index.php?module=preguntas_frecuentes&action=EditView"><i class="fa fa-plus-circle"></i>Crear Preguntas Frecuentes</a></li>
									<li><a href="index.php?module=potenciales_clientes&action=EditView"><i class="fa fa-plus-circle"></i>Crear Prospectos</a></li>
									<li><a href="index.php?module=proveedores&action=EditView"><i class="fa fa-plus-circle"></i>Crear Proveedores</a></li>
									<li><a href="index.php?module=proyectos&action=EditView"><i class="fa fa-plus-circle"></i>Crear Proyectos</a></li>
									<li><a href="index.php?module=Calendar&action=EditView"><i class="fa fa-plus-circle"></i>Crear Tarea</a></li>
									<li><a href="index.php?module=orden_de_trabajo&action=EditView"><i class="fa fa-plus-circle"></i>Crear Trabajos</a></li>
									<li><a href="index.php?module=orden_de_venta&action=EditView"><i class="fa fa-plus-circle"></i>Crear Ventas</a></li>
								</ul>
							</li>
							<li class="dropdown profile-dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><img src="themes/centaurus/img/photo.png" alt="" style="background-color: #ACACAC;" /><span class="hidden-xs" style="min-width: 3em;">Usuario</span><i class="fa fa-chevron-circle-down hidden-xs"></i></a>
								<ul class="dropdown-menu">
									<li><a href="index.php?module=Settings&action=index&parenttab=Settings"><i class="fa fa-cog"></i>Configuración</a></li>
									<li><a href="index.php?module=Home&action=CustomerView"><i class="fa fa-files-o"></i>Mi usuario</a></li>
									<li><a href="index.php?module=Home&action=ViewSubscriptionDetails"><i class="fa fa-briefcase"></i>Mi suscripción</a></li>
									<li role="separator" class="divider hidden-md hidden-lg"></li>
									<li><a href="index.php?module=Home&action=ViewSubscriptionDetails" class="hidden-md hidden-lg" style="padding-left: 17px;"><span class="text-warning">Te quedan 14 días de prueba</span></a></li>
									<li role="separator" class="divider"></li>
									<li><a href="index.php?module=Users&action=Logout"><i class="fa fa-power-off"></i>Salir</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</header>
		<div id="page-wrapper" class="container nav-small">
			<div>
				<div class="row fix-h">
					<div id="nav-col">
						<section id="col-left" class="col-left-nano">
							<div id="col-left-inner" class="col-left-nano-content">
								<div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">
									<ul class="nav nav-pills nav-stacked">
										<li class="hidden-xs">
											<a href="index.php" class="dropdown-toggle" style="padding: 3px 0 48px 8px;" onclick="window.location.href='index.php';">
												<img src="/test/logo/platzi-logo-for-mobile.png" style="display: inline-block; max-width: 40px; padding: 5px; vertical-align: top;" />
												<img src="/test/logo/platzilla-logo.png" style="display: inline-block; max-width: 100px; padding: 15px 10px 10px 3px; vertical-align: top;" />
											</a>
										</li>
										<li id="li-Entradas">
											<a class="dropdown-toggle" href="#">
												<i class="fa fa-edit"></i>
												<span>Entradas  </span>
												<i class="fa fa-chevron-circle-right drop-icon"></i>
											</a>
											<ul class="submenu">
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=articulos&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=articulos&action=index">
														<span class="nombremodulo-menu" style="">  Art&iacute;culos </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=clientes&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=clientes&action=index">
														<span class="nombremodulo-menu" style="">  Clientes </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=contactos&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=contactos&action=index">
														<span class="nombremodulo-menu" style="">  Contactos </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=contratos_de_servicio&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=contratos_de_servicio&action=index">
														<span class="nombremodulo-menu" style="">  Contratos </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=equipamientos&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=equipamientos&action=index">
														<span class="nombremodulo-menu" style="">  Equipamientos </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=pedidos&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=pedidos&action=index">
														<span class="nombremodulo-menu" style="">  Pedidos </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=preguntas_frecuentes&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=preguntas_frecuentes&action=index">
														<span class="nombremodulo-menu" style="">  Preguntas Frecuentes </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=potenciales_clientes&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=potenciales_clientes&action=index">
														<span class="nombremodulo-menu" style="">  Prospectos </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=proveedores&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=proveedores&action=index">
														<span class="nombremodulo-menu" style="">  Proveedores </span>
													</a>
												</li>
											</ul>
										</li>
										<li id="li-Planificaci&oacute;n">
											<a class="dropdown-toggle" href="#">
												<i class="fa fa-list-ul"></i>
												<span>Planificaci&oacute;n  </span>
												<i class="fa fa-chevron-circle-right drop-icon"></i>
											</a>
											<ul class="submenu">
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=oportunidades&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=oportunidades&action=index">
														<span class="nombremodulo-menu" style="">  Oportunidades  </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=plan_de_mantenimiento&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=plan_de_mantenimiento&action=index">
														<span class="nombremodulo-menu" style="">  Plan de servicios </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=proyectos&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=proyectos&action=index">
														<span class="nombremodulo-menu" style="">  Proyectos </span>
													</a>
												</li>
											</ul>
										</li>
										<li id="li-Ejecuci&oacute;n">
											<a class="dropdown-toggle" href="#">
												<i class="fa fa-play"></i>
												<span>Ejecuci&oacute;n  </span>
												<i class="fa fa-chevron-circle-right drop-icon"></i>
											</a>
											<ul class="submenu">
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=orden_de_compra&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=orden_de_compra&action=index">
														<span class="nombremodulo-menu" style="">  Compras </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=presupuestos_cotizacion&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=presupuestos_cotizacion&action=index">
														<span class="nombremodulo-menu" style="">  Cotizaciones </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=facturas&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=facturas&action=index">
														<span class="nombremodulo-menu" style="">  Factura </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=incidencias&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=incidencias&action=index">
														<span class="nombremodulo-menu" style="">  Incidencias </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=orden_de_trabajo&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=orden_de_trabajo&action=index">
														<span class="nombremodulo-menu" style="">  Trabajos </span>
													</a>
												</li>
												<li>
													<div class="crearregistro-menu" style="">
														<a href="index.php?module=orden_de_venta&action=EditView"><i class="fa fa-plus"></i></a>
													</div>
													<a class="a-menu" style="" href="index.php?module=orden_de_venta&action=index">
														<span class="nombremodulo-menu" style="">  Ventas </span>
													</a>
												</li>
											</ul>
										</li>
										<li id="li-Revisi&oacute;n">
											<a class="dropdown-toggle" href="#">
												<i class="fa fa-check"></i>
												<span>Revisi&oacute;n  </span>
												<i class="fa fa-chevron-circle-right drop-icon"></i>
											</a>
											<ul class="submenu">
												<li>
													<a class="a-menu" style="" href="index.php?module=graficosgenerales&action=index">
														<span class="nombremodulo-menu" style="">  Gr&aacute;ficos </span>
													</a>
												</li>
												<li>
													<a class="a-menu" style="" href="index.php?module=Reports&action=index">
														<span class="nombremodulo-menu" style="">  Informes </span>
													</a>
												</li>
											</ul>
										</li>
									</ul>
								</div>
							</div>
							<div class="col-left-nano-content hidden-xs hidden-sm" style="bottom: 0; left: 0; position: absolute; right: 0;">
								<div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">
									<ul class="nav nav-pills nav-stacked">
										<li>
											<a href="#" class="dropdown-toggle" onclick="return HelpUtils.showHelp ('Walkthrough');"><i class="fa fa-question-circle"></i><span>Ayuda</span></a>
										<li>
										<li>
											<a href="index.php?module=Walkthrough&action=index" style="padding-left: 15px;">
												<div class="walkthrough-icon"><i class="square"></i><i class="square"></i><i class="square"></i><i class="square"></i><i class="square"></i><i class="square"></i></div>
												<span>Guía de uso</span></a>
										</li>
										<li>
											<a href="index.php?module=Users&amp;action=Logout" onclick="return confirm ('¿Estás seguro que quieres cerrar la sesión?');"><i class="fa fa-power-off"></i><span>Salir</span></a>
										</li>
									</ul>
								</div>
							</div>
						</section>
					</div>
					<div id="content-wrapper">
						<div class="row" style="margin-bottom: 4em;">
							<div class="col-lg-12">
								<div class="row walkthrough-page"><h1 class="col-xs-12 text-center title">Algunas formas en las que puedes empezar a usar Platzilla</h1>
									<div class="col-xs-12 col-md-4 walkthrough-box-container"><a href="index.php?module=Walkthrough&action=DetailsView&page=2a">
										<div class="walkthrough-box walkthrough-box-purple"><h2 class="title">Incrementar tus ventas</h2>
											<p class="subtitle">mejorando los procesos comerciales</p><img src="themes/images/walkthrough/i-sales.png" class="icon" /><span class="link walkthrough-box-yellow">Saber más</span></div>
									</a></div>
									<div class="col-xs-12 col-md-4 walkthrough-box-container"><a href="index.php?module=Walkthrough&action=DetailsView&page=2b">
										<div class="walkthrough-box walkthrough-box-red"><h2 class="title">Llevar la administración</h2>
											<p class="subtitle">registrando la información y asignando tareas</p><img src="themes/images/walkthrough/i-administration.png" class="icon" /><span class="link walkthrough-box-green">Saber más</span></div>
									</a></div>
									<div class="col-xs-12 col-md-4 walkthrough-box-container"><a href="index.php?module=Walkthrough&action=DetailsView&page=2c">
										<div class="walkthrough-box walkthrough-box-orange"><h2 class="title">Fidelizar a tus clientes</h2>
											<p class="subtitle">con un soporte de primera</p><img src="themes/images/walkthrough/i-customers.png" class="icon" /><span class="link walkthrough-box-blue">Saber más</span></div>
									</a></div>
									<div class="col-xs-12 col-md-4 walkthrough-box-container"><a href="index.php?module=Walkthrough&action=DetailsView&page=2d">
										<div class="walkthrough-box walkthrough-box-yellow"><h2 class="title">Tener información precisa</h2>
											<p class="subtitle">sobre lo que ocurre en tu negocio</p><img src="themes/images/walkthrough/i-information.png" class="icon" /><span class="link walkthrough-box-purple">Saber más</span></div>
									</a></div>
									<div class="col-xs-12 col-md-4 walkthrough-box-container"><a href="index.php?module=Walkthrough&action=DetailsView&page=2e">
										<div class="walkthrough-box walkthrough-box-green"><h2 class="title">Perfeccionar la comunicación</h2>
											<p class="subtitle">en tu equipo de trabajo</p><img src="themes/images/walkthrough/i-communication.png" class="icon" /><span class="link walkthrough-box-red">Saber más</span></div>
									</a></div>
									<div class="col-xs-12 col-md-4 walkthrough-box-container"><a href="index.php?module=Walkthrough&action=DetailsView&page=2f">
										<div class="walkthrough-box walkthrough-box-blue"><h2 class="title">Gestionar eficientemente</h2>
											<p class="subtitle">tus proyectos, trabajos y servicios</p><img src="themes/images/walkthrough/i-management.png" class="icon" /><span class="link walkthrough-box-orange">Saber más</span></div>
									</a></div>
								</div>
								<script type="text/html" id="first-connection-modal-template">
									<?php include ('register_new_form.php'); ?>
								</script>
								<script type="text/javascript">
									(function (jQuery) {
										var source = '<?php echo isset ($_GET ['source']) ? $_GET ['source'] : 'Site'; ?>',
											selectedProfile = null,
											modal = null;
										var destroyModal = function () {
											jQuery (this).remove ();
											modal = null;
											selectedProfile = null;
										};
                                        
                                        var validateForm = function() {
                                            var form            = jQuery('#reg_form'),
                                                validEmailRegex = new RegExp("([!#-'*+/-9=?A-Z^-~-]+(\.[!#-'*+/-9=?A-Z^-~-]+)*|\"\(\[\]!#-[^-~ \t]|(\\[\t -~]))+\")@([!#-'*+/-9=?A-Z^-~-]+(\.[!#-'*+/-9=?A-Z^-~-]+)*|\[[\t -Z^-~]*])"),
                                                phoneNumber     =  /^(\([0-9]{3}\)|[0-9]{3})[\s\-]?[\0-9]{3}[\s\-]?[\0-9]{3}[\s\-]?[0-9]{4}$/;
                                            var valid = true;
                                            form.find('button').attr('disabled', false);
                                            form.find('input').each(function() {
                                                if (jQuery(this).val() === '') {
                                                    valid = false;
                                                    jQuery(this).parent().addClass('has-error');
                                                } else {
                                                    if(jQuery(this).attr('name') === 'email') {
                                                        if(! validEmailRegex.test(jQuery(this).val())) {
                                                            valid = false;
                                                            jQuery(this).parent().addClass('has-error');
                                                        } else {
                                                            jQuery(this).parent().removeClass('has-error');
                                                        }
                                                    } else if (jQuery(this).attr('name') === 'phone') {
                                                        if(!phoneNumber.test(jQuery(this).val())) {
                                                            valid = false;
                                                            jQuery(this).parent().addClass('has-error');
                                                        } else {
                                                            jQuery(this).parent().removeClass('has-error');
                                                        }
                                                    } else {
                                                        jQuery(this).parent().removeClass('has-error');
                                                    }
                                                }
                                            });
                                            if (valid) {
                                                form.find('button').attr('disabled', true);
                                            } else {
                                                form.find('button').attr('disabled', false);
                                            }
                                            return valid;
                                        };
                                        
                                        var getCostumer = function(obj) {
                                            var form = jQuery('#reg_form'),
                                                info = jQuery('#reg_error');
                                            if (validateForm()) {
                                                arguments = form.serialize();
                                                jQuery.post('index.php', arguments, function (data) {
                                                    try {
                                                        console.log(data);
                                                       if (data !== 'OK') {
                                                           throw data;
                                                       } else {
                                                           alert('Gracias por registrarte, sus datos han sido enviados con éxito');
                                                           var url = location.href.split('register.php')[0];
                                                           location.href = url;
                                                       }
                                                    } catch (e) {
                                                        alert(e);
                                                        form.find('button').attr('disabled', false);
                                                    }
                                                });
                                            }
                                        }
                                        
                                        
										var openFinalConfigurationPage = function () {
											var footer = modal.find ('.modal-footer');
											footer.find ('#back').off ('click').hide ();
											footer.find ('#forward').off ('click').hide ();
											footer.find ('#done').off ('click').on ('click', validateFinalConfiguration).show ();
											modal.find ('.modal-footer #page-number').text ('1');
											modal.find ('.page').hide ();
											modal.find ('#page-final-configuration').show ();
                                            selectedProfile = 'Emprendedor';
										};
										var openGoodByePage = function () {
											var footer = modal.find ('.modal-footer');
											footer.find ('#page-number').text ('2');
											footer.find ('#back').off ('click').on ('click', openUserTypePage).show ();
											footer.find ('#forward').off ('click').hide ();
											footer.find ('#done').off ('click').on ('click', submitData).show ();
											modal.find ('.page').hide ();
											modal.find ('#page-goodbye').show ();
										};
										var openUserTypePage = function () {
											var footer = modal.find ('.modal-footer');
											selectedProfile = null;
											footer.find ('#back').off ('click').on ('click', openFinalConfigurationPage).show ();
											footer.find ('#forward').off ('click').hide ();
											footer.find ('#done').off ('click').hide ();
											modal.find ('.modal-footer #page-number').text ('2');
											modal.find ('.page').hide ();
											modal.find ('#page-user-type').show ();
										};
										var setBigCompanyProfile = function () {
											selectedProfile = 'Miembro de una empresa grande';
											openGoodByePage ();
										};
										var setEntrepreneurProfile = function () {
											selectedProfile = 'Emprendedor';
											openGoodByePage ();
										};
										var setMicroBusinessmanProfile = function () {
											selectedProfile = 'Microempresario';
											openGoodByePage ();
										};
										var setSMEProfile = function () {
											selectedProfile = 'Miembro de una PYME en crecimiento';
											openGoodByePage ();
										};
										var validateFinalConfiguration = function () {
											var field, value;
											field = modal.find ('#email');
											value = field.val ();
											if ((value === undefined) || (value === null) || (value.trim () === '')) {
												field.addClass ('missing');
												field.focus ();
												return;
											} else {
												field.removeClass ('missing');
											}
											field = modal.find ('#first-name');
											value = field.val ();
											if ((value === undefined) || (value === null) || (value.trim () === '')) {
												field.addClass ('missing');
												field.focus ();
												return;
											} else {
												field.removeClass ('missing');
											}
											field = modal.find ('#last-name');
											value = field.val ();
											if ((value === undefined) || (value === null) || (value.trim () === '')) {
												field.addClass ('missing');
												field.focus ();
												return;
											} else {
												field.removeClass ('missing');
											}
											field = modal.find ('#password');
											value = field.val ();
											if ((value === undefined) || (value === null) || (value.trim () === '')) {
												field.addClass ('missing');
												field.focus ();
												return;
											} else {
												field.removeClass ('missing');
											}
                                            submitData ();
											//openUserTypePage ();
										};
										var openModal = function () {
											var modalTemplate = jQuery ('#first-connection-modal-template');
											modal = jQuery (modalTemplate.html ());
											modal.find ('#link-entrepreneur').click (setEntrepreneurProfile);
											modal.find ('#link-microbusinessman').click (setMicroBusinessmanProfile);
											modal.find ('#link-sme').click (setSMEProfile);
											modal.find ('#link-bigcompany').click (setBigCompanyProfile);
											openFinalConfigurationPage ();
											modal.modal ({ backdrop: 'static', keyboard: false }).on ('hidden.bs.modal', destroyModal);
										};
										var submitData = function () {
											var arguments = [
												'module=store',
												'action=CreateInstance',
												'email=' + encodeURIComponent (modal.find ('#email').val ()),
												'firstname=' + encodeURIComponent (modal.find ('#first-name').val ()),
												'lastname=' + encodeURIComponent (modal.find ('#last-name').val ()),
												'password=' + encodeURIComponent (modal.find ('#password').val ()),
												'profile=' + encodeURIComponent (selectedProfile),
												'source=' + encodeURIComponent (source),
												'Ajax=true'
											];
											modal.find ('button').prop ('disabled', true);
											jQuery.ajax ('index.php', {
												data: arguments.join ('&'),
												dataType: 'json',
												method: 'post'
											}).done (function () {
												modal.modal ('hide');
												window.location.href = '/index.php';
											}).fail (function (jQueryResponse) {
												var message;

												try {
													message = JSON.parse (jQueryResponse.responseText);
												} catch (e) {
													message = 'Se ha presentado un error inesperado. Intenta más tarde';
													console.error (jQueryResponse);
												}
												alert (message);
												modal.find ('button').prop ('disabled', false);
											});
										};
                                       
                                        window.RegisterUtils = {
                                            getCostumer: getCostumer
                                        }
										jQuery (document).ready (openModal);
									} (jQuery));
								</script>
							</div>
						</div>
						<div class="clearfix" style="height: 38px;"></div>
						<footer id="footer-bar" class="row">
							<p id="footer-copyright" class="col-xs-12">
								<span>&copy; 2004-2021 <a href="http://www.gestionar-facil.com/que-es-platzilla/ " target="_blank">Platzilla.com</a></span> -
								<span><a href="/politica-de-privacidad.html" target="_blank">Política de privacidad</a></span> -
								<span><a href="/politica-de-cookies.html" target="_blank">Política de cookies</a></span> -
								<span><a href="/terminos-de-servicio.html" target="_blank">Términos de servicio</a></span>
							</p>
						</footer>
					</div>
					<div id="ascrail2001" class="nicescroll-rails" style="width: 3px; z-index: 1000; cursor: default; position: absolute; top: 74px; left: 237px; height: 341px; opacity: 0;">
						<div style="position: relative; top: 0; float: right; width: 3px; height: 339px; background-color: rgb(31, 181, 173); border: 0 solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 0;"></div>
					</div>
					<div id="ascrail2001-hr" class="nicescroll-rails" style="height: 3px; z-index: 1000; top: 412px; left: 0; position: absolute; cursor: default; display: none; width: 237px; opacity: 0;">
						<div style="position: relative; top: 0; height: 3px; width: 240px; background-color: rgb(31, 181, 173); border: 0 solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 0;"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
