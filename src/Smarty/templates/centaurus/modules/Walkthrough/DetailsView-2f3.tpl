{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-blue{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2f{/block}
{block name="detail-title"}Tareas para comenzar: agendar reuniones, y si es oportuno, incluir al cliente{/block}
{block name="video-url"}https://player.vimeo.com/video/312086380{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-blue.png');">
		Crea o agenda reuniones ordinarias para hacer seguimiento a la ejecución de servicios, trabajos y proyectos
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-blue.png');">
		Invita al cliente, cuando sea oportuno, para que esté al tanto de la ejecución.
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-blue.png');">
		Registra los acuerdos y compromisos como parte del registro correspondiente. Es clave tener una traza completa de su evolución
	</li>
</ul>
{/block}