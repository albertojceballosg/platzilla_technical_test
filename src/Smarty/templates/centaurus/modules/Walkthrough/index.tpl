{strip}
<link type="text/css" rel="stylesheet" href="modules/Walkthrough/Walkthrough.css?v=1.1" />
<div class="row walkthrough-page">
	<h1 class="col-xs-12 text-center title">Algunas formas en las que puedes empezar a usar Platzilla</h1>
	<div class="col-xs-12 col-md-4 walkthrough-box-container">
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2a">
			<div class="walkthrough-box walkthrough-box-purple">
				<h2 class="title">Incrementar tus ventas</h2>
				<p class="subtitle">mejorando los procesos comerciales</p>
				<img src="themes/images/walkthrough/i-sales.png" class="icon" />
				<span class="link walkthrough-box-yellow">Saber más</span>
			</div>
		</a>
	</div>
	<div class="col-xs-12 col-md-4 walkthrough-box-container">
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2b">
			<div class="walkthrough-box walkthrough-box-red">
				<h2 class="title">Llevar la administración</h2>
				<p class="subtitle">registrando la información y asignando tareas</p>
				<img src="themes/images/walkthrough/i-administration.png" class="icon" />
				<span class="link walkthrough-box-green">Saber más</span>
			</div>
		</a>
	</div>
	<div class="col-xs-12 col-md-4 walkthrough-box-container">
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2c">
			<div class="walkthrough-box walkthrough-box-orange">
				<h2 class="title">Fidelizar a tus clientes</h2>
				<p class="subtitle">con un soporte de primera</p>
				<img src="themes/images/walkthrough/i-customers.png" class="icon" />
				<span class="link walkthrough-box-blue">Saber más</span>
			</div>
		</a>
	</div>
	<div class="col-xs-12 col-md-4 walkthrough-box-container">
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2d">
			<div class="walkthrough-box walkthrough-box-yellow">
				<h2 class="title">Tener información precisa</h2>
				<p class="subtitle">sobre lo que ocurre en tu negocio</p>
				<img src="themes/images/walkthrough/i-information.png" class="icon" />
				<span class="link walkthrough-box-purple">Saber más</span>
			</div>
		</a>
	</div>
	<div class="col-xs-12 col-md-4 walkthrough-box-container">
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2e">
			<div class="walkthrough-box walkthrough-box-green">
				<h2 class="title">Perfeccionar la comunicación</h2>
				<p class="subtitle">en tu equipo de trabajo</p>
				<img src="themes/images/walkthrough/i-communication.png" class="icon" />
				<span class="link walkthrough-box-red">Saber más</span>
			</div>
		</a>
	</div>
	<div class="col-xs-12 col-md-4 walkthrough-box-container">
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2f">
			<div class="walkthrough-box walkthrough-box-blue">
				<h2 class="title">Gestionar eficientemente</h2>
				<p class="subtitle">tus proyectos, trabajos y servicios</p>
				<img src="themes/images/walkthrough/i-management.png" class="icon" />
				<span class="link walkthrough-box-orange">Saber más</span>
			</div>
		</a>
	</div>
{if ($IS_GUEST_USER)}
	<div class="col-xs-12 text-center">
		<button class="btn btn-success">¿Estás listo para suscribirte?</button>
	</div>
{/if}
</div>
{if ($IS_FIRST_CONNECTION)}
{include file='modal/FirstConnectionModal.tpl'}
{/if}
{/strip}