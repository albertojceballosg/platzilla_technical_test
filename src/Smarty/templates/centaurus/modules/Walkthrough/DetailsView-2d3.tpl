{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-yellow{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2d{/block}
{block name="detail-title"}Tareas para comenzar: supervisar el estado de las tareas asignadas{/block}
{block name="video-url"}https://player.vimeo.com/video/312636031{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-yellow.png');">
		Supervisa el estado de las tareas asignadas a colaboradores y equipos de trabajo
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-yellow.png');">
		Filtra las tareas por diversos criterios para tener a un clic listas que faciliten el seguimiento
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-yellow.png');">
		Haz seguimiento al cumplimiento de actividades a través de los calendarios
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-yellow.png');">
		Crea vistas calendario de la información en módulos, para conocer el avance
	</li>
</ul>
{/block}