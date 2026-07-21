{extends file='modules/Walkthrough/SubDetailsOneRowView.tpl'}
{block name="bg-color-class"}walkthrough-box-orange{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2c{/block}
{block name="detail-title"}Tareas para comenzar: gestionar pedidos, ventas, facturas...{/block}
{block name="video-url"}https://player.vimeo.com/video/312021932{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-orange.png');">
		Ingresa los pedidos, las ventas, cotizaciones, facturas, trabajos que demande tu cliente
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-orange.png');">
		Establece prioridades para asegurar la atención oportuna, organizando y filtrando la información según el caso
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-orange.png');">
		Asigna los responsables para los casos que ameriten acompañamiento y atención personalizada del cliente
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-4-orange.png');">
		Haz seguimiento al cumplimiento de compromisos y comparta comentarios o da feedback a tu equipo de trabajo
	</li>
</ul>
{/block}