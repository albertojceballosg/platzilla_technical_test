{extends file='modules/Walkthrough/SubDetailsTwoRowsView.tpl'}
{block name="bg-color-class"}walkthrough-box-orange{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2c{/block}
{block name="detail-title"}Tareas para comenzar: atender, registrar y resolver incidencias{/block}
{block name="video-url"}https://player.vimeo.com/video/312600638{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-orange.png');">
		Atiende a los clientes e ingresa las incidencias con el mayor detalle posible
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-orange.png');">
		Establece prioridades y asigna un responsable para atender cada incidencia hasta su cierre
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-orange.png');">
		Haz seguimiento al cierre de las incidencias, utiliza los gráficos para ver la evolución global
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-4-orange.png');">
		Comparte comentarios o da feedback a tu equipo de trabajo sobre las incidencias relevantes
	</li>
</ul>
{/block}
{block name="faq"}
<ul class="faq">
	<li class="question">¿Qué es una incidencia?</li>
	<li class="answer">
		Una incidencia puede entenderse como una situación que se produce en el transcurso de una transacción, y que repercute en ella, alterándola. Puede verse también como una interrupción en un servicio o una disminución de la calidad esperada en un producto.
		<br /><br />
		En el proceso de venta, es un hecho que afecta la calidad del producto o servicio, por lo que el cliente manifiesta una queja, hace un comentario o refleja una emoción. Las incidencias afectan la calidad y la imagen de la empresa.
	</li>
	<li class="question">¿Por qué es importante registrar incidencias?</li>
	<li class="answer">
		La atención de incidencias debe pasar por su registro, con independencia de si es resuelta de inmediato o no.
		<br /><br />
		Conocer las  incidencias, clasificarlas y evaluar el resultado de su proceso de atención y cierre, brinda información valiosa para todas las áreas de una empresa. Son fuente de planes de acción para la mejora continua y para la fidelización de clientes.
	</li>
	<li class="question">¿Quién debe conocer las incidencias, aparte del equipo comercial?</li>
	<li class="answer">
		Todo el equipo de la empresa debe conocer las incidencias que se registran, los pasos o las acciones para atenderlas, pues estas pueden ser fuente de mejora en todas las actividades ejecutadas por los colaboradores.
		<br /><br />
		Por ello, deben ser visibles para todos. Lo ideal es que estén registradas en aplicaciones que permitan controles de acceso para los usuarios.
	</li>
</ul>
{/block}