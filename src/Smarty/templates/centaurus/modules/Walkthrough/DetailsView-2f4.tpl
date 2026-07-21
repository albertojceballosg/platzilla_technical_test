{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-blue{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2f{/block}
{block name="detail-title"}Tareas para comenzar: automatizar tareas repetitivas para agilizar la gestión{/block}
{block name="video-url"}https://player.vimeo.com/video/312087428{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-blue.png');">
		Define la tarea que necesitas automatizar en los módulos. Por ejemplo, crear una factura desde el módulo Trabajos.
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-blue.png');">
		Crea la tarea oculta o en segundo plano (configuración - motor de tareas ocultas).
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-blue.png');">
		Asocia la tarea oculta con un botón personalizado, en el módulo correspondiente, para que esté disponible si las condiciones se cumplen
	</li>
</ul>
{/block}