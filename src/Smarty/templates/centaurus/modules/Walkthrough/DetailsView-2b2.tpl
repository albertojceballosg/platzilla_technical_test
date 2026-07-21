{extends file='modules/Walkthrough/SubDetailsTwoRowsView.tpl'}
{block name="bg-color-class"}walkthrough-box-red{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2b{/block}
{block name="detail-title"}Tareas para comenzar: organizar y priorizar la información{/block}
{block name="video-url"}https://player.vimeo.com/video/311978972{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-1-red.png');">
		Asigna "estados" a las incidencias para hacer seguimiento, hasta su cierre o resolución
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-2-red.png');">
		Crea filtros personalizados para tener vistas con información por diversos criterios
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-3-red.png');">
		Aplica diversos tipos de filtros y resalte la información clave con colores
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-4-red.png');">
		Comparte los filtros con tu equipo de trabajo o mantengalos como privados
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-5-red.png');">
		Ordena la información haciendo clic en la columna, en las vistas disponibles
	</li>
</ul>
{/block}
{block name="faq"}
<ul class="faq">
	<li class="question">¿Qué son los filtros en Platzilla?</li>
	<li class="answer">
		Un filtro en los módulos de Platzilla en una funcionalidad que nos permite crear vistas de la información del módulo, aplicando criterios para mostrar listas específicas de registros. Con esto, puedes organizar y priorizar la información.
		<br /><br />
		Por ejemplo, si de los 5.000 clientes registrados, necesitas ver solo los que tienen "estatus = cliente estrella", con un filtro tendrás una vista de los principales clientes. Luego, sobre esta lista podrás realizar diversas operaciones; solo para los registros filtrados.
	</li>
	<li class="question">¿Cuántos filtros puedo crear para organizar la información?</li>
	<li class="answer">
		En todos los módulos de Platzilla puedes crear tanto filtros como necesites. Pueden ser propios o compartidos con los restantes colaboradores con acceso a la información.
	</li>
	<li class="question">¿Qué es una vista Kanban y para qué me sirve?</li>
	<li class="answer">
		En Platzilla, las vistas Kanban nos permiten tener un panorama rápido de la información de un módulo, en un "tablero" con columnas. Aplica para los casos en que tengamos fases (como en los proyectos o las oportunidades) y estados (como en las incidencias reportadas por clientes). El Kanban facilita la visualización de flujos o transiciones de la información.
		<br /><br />
		Por ejemplo: una Incidencia puede pasar por diversos estados hasta ser "resuelta". Un proyecto puede pasar por etapas, como "iniciado", "en proceso", "finalizado" y "cerrado".
	</li>
	<li class="question">¿Para qué puedo utilizar el filtro con colores?</li>
	<li class="answer">
		Los filtros con colores nos facilita destacar registros en las vistas tipo listas, para tener una mejor visualización de la información. Por ejemplo, en una vista tipo lista de las oportunidades que están siendo gestionadas, podemos resaltar con color verde aquellas que tengan un "valor > 1.000", y con color amarillo las que tengan un "valor < 100".
	</li>
</ul>
{/block}