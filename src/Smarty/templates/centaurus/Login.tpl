{strip}
<!doctype html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="msapplication-TileColor" content="#ffffff" />
	<meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png" />
	<meta name="theme-color" content="#ffffff" />
	<title>Platzilla</title>
	<link rel="manifest" href="favicon/manifest.json">
	<link type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400" rel="stylesheet">
	<link type="text/css" href="themes/centaurus/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
	<link type="text/css" href="themes/centaurus/css/compiled/login.css?v=1.1" rel="stylesheet" />
	<link type="image/png" href="favicon/apple-icon-57x57.png" rel="apple-touch-icon" sizes="57x57" />
	<link type="image/png" href="favicon/apple-icon-60x60.png" rel="apple-touch-icon" sizes="60x60" />
	<link type="image/png" href="favicon/apple-icon-72x72.png" rel="apple-touch-icon" sizes="72x72" />
	<link type="image/png" href="favicon/apple-icon-76x76.png" rel="apple-touch-icon" sizes="76x76" />
	<link type="image/png" href="favicon/apple-icon-114x114.png" rel="apple-touch-icon" sizes="114x114" />
	<link type="image/png" href="favicon/apple-icon-120x120.png" rel="apple-touch-icon" sizes="120x120" />
	<link type="image/png" href="favicon/apple-icon-144x144.png" rel="apple-touch-icon" sizes="144x144" />
	<link type="image/png" href="favicon/apple-icon-152x152.png" rel="apple-touch-icon" sizes="152x152" />
	<link type="image/png" href="favicon/apple-icon-180x180.png" rel="apple-touch-icon" sizes="180x180" />
	<link type="image/png" href="favicon/android-icon-192x192.png" rel="icon" sizes="192x192" />
	<link type="image/png" href="favicon/favicon-16x16.png" rel="icon" sizes="16x16" />
	<link type="image/png" href="favicon/favicon-32x32.png" rel="icon" sizes="32x32" />
	<link type="image/png" href="favicon/favicon-96x96.png" rel="icon" sizes="96x96" />
	<!--[if lt IE 9]>
	<script type="text/javascript" src="themes/centaurus/js/html5shiv.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/respond.min.js"></script>
	<![endif]-->
	<script type="text/javascript" src="themes/centaurus/js/jquery.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap.js"></script>
	<style>
		#footer-bar {
			bottom:       0;
			font-size:    0.75em;
			height:       36px;
			line-height:  36px;
			margin-left:  8px;
			margin-right: 8px;
			position:     fixed!important;
			width:        100%;
		}
	</style>
</head>
<body>
	<main class="container-fluid" style="padding: 0;">
{if (isset ($MESSAGE))}
		<div class="alert alert-dismissible alert-{if (!$IS_ERROR)}success{else}danger{/if}">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<i class="fa fa-{if (!$IS_ERROR)}check{else}times{/if}-circle fa-fw fa-lg"></i>
			{$MESSAGE}
		</div>
{/if}
		<div id="home-carousel" class="carousel slide" data-ride="carousel" data-interval="false" data-keyboard="false">
			<div class="carousel-inner">
				<div class="item active">
					<div class="col-xs-12 col-md-6 item-content">
						<img src="themes/centaurus/img/logo-platzilla-vert.png" alt="Platzilla" class="logo" />
						<div class="content">
							<h1 class="title">Emprende y Gestiona como un experto</h1>
							<p class="text"><a href="register.php" class="button">Regístrate gratis</a></p>
							<p class="text">¿Ya te has unido a la plataforma Platzilla? <button type="button" class="link" onclick="LoginUtils.showLoginForm (this);">Ingresa</button></p>
						</div>
						<div class="form-container"></div>
					</div>
					<div class="col-xs-12 col-md-6" style="padding: 0;">
						<img src="themes/centaurus/img/home-slide-01.png?v=1.1" class="img-responsive" alt="La gestión fácil en tu empresa" />
					</div>
				</div>
				<div class="item">
					<div class="col-xs-12 col-md-6 item-content">
						<img src="themes/centaurus/img/logo-platzilla-vert.png" alt="Platzilla" class="logo" />
						<div class="content">
							<h1 class="title">Las necesidades de tu empresa en un mismo sitio</h1>
							<p class="text">Platzilla ofrece las necesidades que tu empresa necesita, con la ventaja que todo está integrado sin tener que depender de muchas aplicaciones o servicios. Con el paso del tiempo irás descubriendo el poder de la integración y del tiempo que te va ahorrando trabajar de un modo colaborativo e integrado en Platzilla.</p>
							<p class="text">¡Descúbrelo!</p>
							<p class="text"><a href="register.php" class="button">Regístrate gratis</a></p>
							<p class="text">¿Ya te has unido a la plataforma Platzilla? <button type="button" class="link" onclick="LoginUtils.showLoginForm (this);">Ingresa</button></p>
						</div>
						<div class="form-container"></div>
					</div>
					<div class="col-xs-12 col-md-6" style="padding: 0;">
						<img src="themes/centaurus/img/home-slide-02.png" class="img-responsive" alt="Las necesidades de tu empresa en un mismo sitio" />
					</div>
				</div>
				<div class="item">
					<div class="col-xs-12 col-md-6 item-content">
						<img src="themes/centaurus/img/logo-platzilla-vert.png" alt="Platzilla" class="logo" />
						<div class="content">
							<h1 class="title">¿Qué puedes hacer con Platzilla?</h1>
							<ul class="list">
								<li class="list-item">Registrar, organizar y clasifcar a tus <strong>clientes</strong></li>
								<li class="list-item">Almacenar diversos <strong>contactos</strong></li>
								<li class="list-item">Generar <strong>contratos de servicio</strong></li>
								<li class="list-item">Crear <strong>cotizaciones</strong></li>
								<li class="list-item">Realizar <strong>facturas</strong></li>
								<li class="list-item">Crear y hacer seguimiento a <strong>facturas</strong></li>
								<li class="list-item">Gestionar las <strong>tareas</strong> tuyas y en equipo</li>
								<li class="list-item">Y muchas cosas más</li>
							</ul>
							<p class="text"><a href="register.php" class="button">Regístrate gratis</a></p>
							<p class="text">¿Ya te has unido a la plataforma Platzilla? <button type="button" class="link" onclick="LoginUtils.showLoginForm (this);">Ingresa</button></p>
						</div>
						<div class="form-container"></div>
					</div>
					<div class="col-xs-12 col-md-6" style="padding: 0;">
						<img src="themes/centaurus/img/home-slide-03.png" class="img-responsive" alt="¿Qué puedes hacer con Platzilla?" />
					</div>
				</div>
				<div class="item">
					<div class="col-xs-12 col-md-6 item-content">
						<img src="themes/centaurus/img/logo-platzilla-vert.png" alt="Platzilla" class="logo" />
						<div class="content">
							<h1 class="title">Primeros pasos</h1>
							<p class="text">Si deseas descubrirlo, date de alta. El alta es gratuita y puedes usar todas las aplicaciones disponibles en la plataforma. Así podrás elegir qué usar o usar al completo una o varias aplicaciones según vaya creciendo tu empresa.</p>
							<p class="text">¡Descubre qué es Platzilla!</p>
							<p class="text"><a href="register.php" class="button">Regístrate gratis</a></p>
							<p class="text">¿Ya te has unido a la plataforma Platzilla? <button type="button" class="link" onclick="LoginUtils.showLoginForm (this);">Ingresa</button></p>
						</div>
						<div class="form-container"></div>
					</div>
					<div class="col-xs-12 col-md-6" style="padding: 0;">
						<img src="themes/centaurus/img/home-slide-04.png" class="img-responsive" alt="Primeros pasos" />
					</div>
				</div>
			</div>
			<div class="carousel-controls">
				<a class="carousel-control" href="#home-carousel" data-slide="prev">
					<span class="glyphicon glyphicon-chevron-up"></span>
					<span class="sr-only">Anterior</span>
				</a>
				<ol class="carousel-indicators">
					<li class="carousel-indicator active" data-target="#home-carousel" data-slide-to="0"></li>
					<li class="carousel-indicator" data-target="#home-carousel" data-slide-to="1"></li>
					<li class="carousel-indicator" data-target="#home-carousel" data-slide-to="2"></li>
					<li class="carousel-indicator" data-target="#home-carousel" data-slide-to="3"></li>
				</ol>
				<a class="carousel-control" href="#home-carousel" data-slide="next">
					<span class="glyphicon glyphicon-chevron-down"></span>
					<span class="sr-only">Siguiente</span>
				</a>
			</div>
		</div>
	</main>
	<footer id="footer-bar" class="row" style="position: absolute; bottom: 0; left: 15px">
		<p id="footer-copyright" class="col-xs-12">
			<span>&copy; 2004-{php}echo date('Y');{/php} <a href="http://www.gestionar-facil.com/que-es-platzilla/ " target="_blank">Platzilla.com</a></span>&nbsp;-&nbsp;
			<span><a href="/politica-de-privacidad.html" target="_blank">Política de privacidad</a></span>&nbsp;-&nbsp;
			<span><a href="/politica-de-cookies.html" target="_blank">Política de cookies</a></span>&nbsp;-&nbsp;
			<span><a href="/terminos-de-servicio.html" target="_blank">Términos de servicio</a></span>
		</p>
	</footer>
	<script type="text/html" id="login-form-template">
		<div class="col-xs-9 col-xs-push-1 col-md-10 col-md-push-1">
			<h1 class="title text-center">Introduce tus credenciales</h1>
			<form action="index.php" method="post" class="row" role="form">
				<input type="hidden" name="module" value="Users" />
				<input type="hidden" name="action" value="Authenticate" />
				<input type="hidden" name="return_module" value="Users" />
				<input type="hidden" name="return_action" value="Login" />
				<input type="text" name="user_name" class="form-control col-xs-12" placeholder="Usuario" required="required" />
				<input type="password" name="user_password" class="form-control col-xs-12" placeholder="Contraseña" required="required" />
				<button type="submit" class="button col-xs-12">Ingresar</button>
			</form>
			<p class="text text-center">¿Olvidaste tu contraseña? <a href="reset-password.php" class="link" target="_blank">Reestablécela</a></p>
		</div>
	</script>
	<script type="text/javascript" src="include/js/login-utils.js?v=1.2"></script>
</body>
</html>
{/strip}