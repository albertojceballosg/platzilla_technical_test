{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-green{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2e{/block}
{block name="detail-title"}Tareas para comenzar: signar tareas al equipo y a miembros del equipo{/block}
{block name="video-url"}https://player.vimeo.com/video/312914357{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-green.png');">
		Asigna tareas generales o específicas a equipos o a colaboradores particulares
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-green.png');">
		Delega la gestión de casos especiales: oportunidades de venta, facturas, proyectos...
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-green.png');">
		Haz seguimiento al cumplimiento y ejecución de tareas para dar feedback
	</li>
</ul>
{/block}