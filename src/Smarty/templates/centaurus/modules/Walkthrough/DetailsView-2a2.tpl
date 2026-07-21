{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-purple{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2a{/block}
{block name="detail-title"}Tareas para comenzar: registrar oportunidades de venta{/block}
{block name="video-url"}https://player.vimeo.com/video/311972491{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-purple.png');">
		Ingresa al módulo de Oportunidades y haz clic en "Crear Oportunidades"
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-purple.png');">
		Selecciona la "Fase de venta" de la oportunidad
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-purple.png');">
		Rellena el resto de los campos del formulario
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-4-purple.png');">
		Haz clic en "Guardar"
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-purple.png');">
		También puedes hacer uso de la funcionalidad "Importar Oportunidades", de forma similar a como se <a href="index.php?module=Walkthrough&action=DetailsView&page=2a1" class="walkthrough-text-purple">importan los prospectos</a>
	</li>
</ul>
{/block}