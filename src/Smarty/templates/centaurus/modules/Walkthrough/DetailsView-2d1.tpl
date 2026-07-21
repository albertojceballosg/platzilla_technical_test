{extends file='modules/Walkthrough/SubDetailsTwoRowsView.tpl'}
{block name="bg-color-class"}walkthrough-box-yellow{/block}
{block name="link-back"}index.php?module=Walkthrough&action=DetailsView&page=2d{/block}
{block name="detail-title"}Tareas para comenzar: controlar el acceso a la información{/block}
{block name="video-url"}https://player.vimeo.com/video/312025404{/block}
{block name="step-by-step"}
<ul class="step-by-step">
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-yellow.png');">
		Verifica los perfiles y roles, según las necesidades de control y de acceso que tengas.
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-yellow.png');">
		Crea los usuarios que administranla información y asígnales un rol.
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-yellow.png');">
		Verifica los privilegios de acceso para los roles
	</li>
	<li class="step" style="background-image: url('/themes/images/walkthrough/i-check-yellow.png');">
		Crea grupos de trabajo para asignar tareas y actividades, cuando aplique
	</li>
</ul>
{/block}
{block name="faq"}
<ul class="faq">
	<li class="question">¿Qué es un usuario en Platzilla?</li>
	<li class="answer">
		El registro y la actualización de la información debe ser realizada por personas responsables de las tareas correspondientes, en las diversas áreas. Para identificar quién registra la información, es necesario tener usuarios identificados de manera única. Es un elemento clave para tener el control de la información.
		<br /><br />
		En Platzilla cada persona que accede a la plataforma debe tener asignado un usuario con contraseña. De esta forma, queda una traza de su actividad en los diversos módulos a los que tiene acceso.
	</li>
	<li class="question">¿Qué son los roles en Platzilla?</li>
	<li class="answer">
		En general, rol es una función que alguien o algo desempeña. En el caso de Platzilla, los roles agrupan la información de los perfiles. A un rol se le puede asignar uno o más perfiles, lo que establece restricciones o límites de lo que puede hacer el rol.
		<br /><br />
		Con los roles podemos controlar el acceso a la información de los usuarios. De hecho, a todo usuario se le asigna un rol.
	</li>
	<li class="question">¿Para qué se definen privilegios de acceso?</li>
	<li class="answer">
		Los privilegios de acceso permiten al administrador de cualquier aplicación, establecer el nivel de "autoridad" que tendrá un usuario para ver o afectar la información a la que tiene acceso. Los privilegios de acceso brindan a cada usuario una vista diferente de la información.
		<br /><br />
		Por supuesto, siempre hay un usuario con el acceso completo, que suele ser denominado "administrador".
	</li>
	<li class="question">¿Por qué crear grupos en Platzilla?</li>
	<li class="answer">
		Cuando asignas tareas a usuarios, puede ser necesario involucrar varias personas. Para facilitar la gestión de tareas y actividades, Platzilla te permite crear grupos. Puedes crear grupos por área, por proyectos u otro criterio, según tus necesidades de control de la información.
	</li>
</ul>
{/block}