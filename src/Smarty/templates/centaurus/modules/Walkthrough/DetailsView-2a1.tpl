{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-purple{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2a{/block}
{block name="detail-title"}Tareas para comenzar: subir la información de tus prospectos{/block}
{block name="video-url"}https://player.vimeo.com/video/311971134{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-purple.png');">
		Accede al módulo de Prospectos y haz clic en la opción "Importar Prospectos"
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-purple.png');">
		Haz clic en "Descargar la plantilla" para obtener una hoja de cálculo editable
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-purple.png');">
		Rellena la hoja de cálculo editable con los datos de tus prospectos
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-4-purple.png');">
		Selecciona el documento con los datos desde tu PC y haz clic en "Importar"
	</li>
</ul>
{/block}