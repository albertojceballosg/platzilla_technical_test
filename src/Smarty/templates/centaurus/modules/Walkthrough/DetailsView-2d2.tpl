{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-yellow{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2d{/block}
{block name="detail-title"}Tareas para comenzar: auditar el registro y la actualización de la información{/block}
{block name="video-url"}https://player.vimeo.com/video/312607660{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-yellow.png');">
		Haz seguimiento a registros de información específicos, mediante el histórico de cambios
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-yellow.png');">
		Crea filtros para obtener detalles de cambios por períodos o de un atributo de un registro
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-yellow.png');">
		Supervisa los mensajes asociados a los registros en los diferentes módulos
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-yellow.png');">
		Haz seguimiento a los comentarios y documentos adjuntos a registros relevantes
	</li>
</ul>
{/block}