{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-red{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2b{/block}
{block name="detail-title"}Tareas para comenzar: registrar información en los módulos de Platzilla{/block}
{block name="video-url"}https://player.vimeo.com/video/311984114{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-red.png');">
		Registra tu información básica para operar (Entradas) con el botón "Crear" de cada módulo: Artículos, Clientes, Contactos, Pedidos, Prospectos, Proveedores…
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-red.png');">
		Registra tu información para planificar con el botón "Crear" de cada módulo: Oportunidades, Planes de servicios, Proyectos...
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-red.png');">
		Registra tu información para ejecutar procesos con el botón "Crear" de cada módulo: Compras, Cotizaciones, Facturas, Incidencias, Trabajos, Ventas...
	</li>
</ul>
{/block}