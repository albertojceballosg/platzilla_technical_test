{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-purple{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2a{/block}
{block name="detail-title"}Tareas para comenzar: gestionar tus pedidos y asignarles prioridades{/block}
{block name="video-url"}https://player.vimeo.com/video/311980765{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-purple.png');">
		Accede al módulo de Pedidos y haz clic en el botón "Crear pedidos"
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-purple.png');">
		Completa los campos y asigna una prioridad a tu pedido. Guarda los cambios
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-purple.png');">
		Seleccionamos la "Vista extendida de pedidos" y organizarlos por prioridad
	</li>
</ul>
{/block}