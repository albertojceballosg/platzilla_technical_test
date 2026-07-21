{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-orange{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2c{/block}
{block name="detail-title"}Tareas para comenzar: registrar y clasificar a los clientes{/block}
{block name="video-url"}https://player.vimeo.com/video/311998180{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-orange.png');">
		Ingresa los datos de tus clientes, clasifícalos y agregue comentarios o información relevante
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-orange.png');">
		Crea segmentos de clientes para que dirijas mensajes e información según sus intereses
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-orange.png');">
		Personaliza, si lo requieres, el módulo Clientes para actualizar la información en función de tus necesidades específicas
	</li>
</ul>
{/block}