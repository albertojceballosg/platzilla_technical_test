{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-blue{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2f{/block}
{block name="detail-title"}Tareas para comenzar: registrar proyectos, trabajos y servicios{/block}
{block name="video-url"}https://player.vimeo.com/video/312362534{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-blue.png');">
		Define los atributos o valores de los proyectos, trabajos y servicios con el cliente
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-blue.png');">
		Registra cada caso con el mayor detalle posible, para tener registros de calidad
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-blue.png');">
		Personaliza los módulos si requieres nuevos campos para asegurar la identificación completa de los proyectos, trabajos y servicios
	</li>
</ul>
{/block}