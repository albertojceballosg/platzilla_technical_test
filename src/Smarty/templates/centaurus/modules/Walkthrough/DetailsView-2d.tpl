{extends file='modules/Walkthrough/DetailsView.tpl'}
{block name="bg-color-class"}walkthrough-box-yellow{/block}
{block name="detail-title"}Tener información precisa sobre lo que ocurre en tu negocio{/block}
{block name="icon-file-name"}i-information-icon.png{/block}
{block name="diagram-title"}Supervisa y controla tu empresa de manera fácil con unos pocos clics{/block}
{block name="diagram-file-name"}p-2d-diagram.png{/block}
{block name="video-url"}https://player.vimeo.com/video/312028520{/block}
{block name="faq"}
<ul class="faq">
	<li class="question">¿Por qué es importante el control?</li>
	<li class="answer">
		Es común que las micro, pequeñas y medianas empresas no tengan un sistema de control formal de la gestión. Con Platzilla puedes tener el control de lo que ocurre en tu empresa a través de diversas funcionalidades. Esto te garantizará saber qué está ocurriendo y tener datos sobre la evolución de las áreas. Es claro que para este propósito, el registro de información debe ser continuo y de calidad.
		<br /><br />
		Si controlas puedes mejorar la eficiencia de la gestión de la empresa y dar un buen uso a los recursos.
	</li>
	<li class="question">¿Qué puedo hacer para controlar mi empresa?</li>
	<li class="answer">
		Para controlar la empresa, sobre todo en sus primeras etapas, puedes crear algunas prácticas básicas para auditar el registro y la evolución de la información clave y hacer seguimiento al cumplimiento de tareas
		<br /><br />
		También puedes generar informes y gráficos que den cuenta de información relacionada con ventas, facturación, evolución de clientes principales, atención de incidencias, situación del inventario, etc. Al realizar comparaciones por períodos, podrás evaluar el resultado de las acciones aplicadas.
	</li>
	<li class="question">¿Qué herramientas puedo utilizar para un buen control?</li>
	<li class="answer">
		En el caso de la plataforma Platzilla, puedes hacer uso del histórico de cambios en los registros, las vistas de tareas y calendarios generales y personalizados, elaboracion de gráficos por diversos criterios e informes detallados.
		<br /><br />
		Además, puedes crear métricas e indicadores por áreas para evaluar las mejoras aplicadas y el logro de las metas.
	</li>
</ul>
{/block}
{block name="tasks"}
<ul class="tasks">
	<li class="task">
		<span class="marker" style="background-image: url('/themes/images/walkthrough/i-check-yellow.png');" aria-hidden="true"></span>
		Controlar el acceso a la información
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2d1" class="link walkthrough-box-green">Ver cómo</a>
	</li>
	<li class="task">
		<span class="marker" style="background-image: url('/themes/images/walkthrough/i-check-yellow.png');" aria-hidden="true"></span>
		Auditar la actualización de la información
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2d2" class="link walkthrough-box-blue">Ver cómo</a>
	</li>
	<li class="task">
		<span class="marker" style="background-image: url('/themes/images/walkthrough/i-check-yellow.png');" aria-hidden="true"></span>
		Supervisar el estado de las tareas asignadas
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2d3" class="link walkthrough-box-purple">Ver cómo</a>
	</li>
	<li class="task">
		<span class="marker" style="background-image: url('/themes/images/walkthrough/i-check-yellow.png');" aria-hidden="true"></span>
		Crear y analizar datos específicos y consolidados
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2d4" class="link walkthrough-box-red">Ver cómo</a>
	</li>
</ul>
{/block}