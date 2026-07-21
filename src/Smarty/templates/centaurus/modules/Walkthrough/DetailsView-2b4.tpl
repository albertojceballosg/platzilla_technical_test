{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-red{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2b{/block}
{block name="detail-title"}Tareas para comenzar: realizar seguimiento y evaluar resultados{/block}
{block name="video-url"}https://player.vimeo.com/video/311991503{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-red.png');">
		Haz seguimiento del estado de la información con las distintas vistas en los módulos
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-red.png');">
		Controla el histórico de los registros, los mensajes y comentarios asociados
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-red.png');">
		Interactúa con tus colaboradores dejando inquietudes sobre la información
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-4-red.png');">
		Obtén informes gráficos para que dispongas de diversas vistas consolidadas de la información
	</li>
</ul>
{/block}