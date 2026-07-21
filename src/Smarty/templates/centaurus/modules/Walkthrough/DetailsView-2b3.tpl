{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-red{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2b{/block}
{block name="detail-title"}Tareas para comenzar: gestionar tus pedidos y asignarles prioridades{/block}
{block name="video-url"}https://player.vimeo.com/video/311990003{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-red.png');">
		Accede al módulo de Pedidos y haz clic en el botón "Crear pedidos"
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-red.png');">
		Completa los campos y asigna una prioridad a tu pedido. Guarda los cambios
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-red.png');">
		Seleccionamos la "Vista extendida de pedidos" y organizarlos por prioridad
	</li>
</ul>
{/block}