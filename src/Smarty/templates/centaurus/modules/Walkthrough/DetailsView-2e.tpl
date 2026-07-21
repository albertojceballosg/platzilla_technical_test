{extends file='modules/Walkthrough/DetailsView.tpl'}
{block name="bg-color-class"}walkthrough-box-green{/block}
{block name="detail-title"}Perfeccionar la comunicación en tu equipo de trabajo{/block}
{block name="icon-file-name"}i-communication-icon.png{/block}
{block name="diagram-title"}Promueve la comunicación efectiva y el trabajo colaborativo{/block}
{block name="diagram-file-name"}p-2e-diagram.png{/block}
{block name="video-url"}https://player.vimeo.com/video/312030456{/block}
{block name="faq"}
<ul class="faq">
	<li class="question">¿Cómo dar un feedback de calidad a mi equipo?</li>
	<li class="answer">
		Un feedback de calidad es aquel que busca determinar brechas sin culpar o emitir juicios. Para dar un buen feedback debe resaltarse lo bien hecho, luego hacer referencia a las deficiencias o errores detectados y, por último, establecer compromisos para alcanzar la meta o el logro esperado. Exponer el por qué, cuando sea pertinente, es clave para fomentar la motivación.
	</li>
	<li class="question">¿Qué es comunicación efectiva?</li>
	<li class="answer">
		Existe una comunicación efectiva cuando el receptor del mensaje entiende o "decodifica" correctamente lo que el emisor está comunicando. Ambos deben asegurar la comprensión del lenguaje.
		<br /><br />
		Un forma básica de verificar si el mensaje está siendo interpretado como esperamos, es que el receptor repita con sus palabras o parafrasee lo entendido. Esto ayuda a alinear a los interlocutores y lograr así una comunicación efectiva.
	</li>
	<li class="question">¿Qué es trabajo colaborativo?</li>
	<li class="answer">
		En el trabajo colaborativo se da una participación proactiva en la resolución de un problema o en la realización de una actividad, en la que los responsables comparten y crean conocimientos, al intercambiar ideas y generar discusiones. El resultado no es la simple suma del trabajo individual, sino que es el producto del esfuerzo compartido. En el trabajo colaborativo se produce sinergia.
		<br /><br />
		El trabajo colaborativo supone retos importantes, en el que las habilidades blandas son esenciales. Por ejemplo, la comunicación e integración con el equipo; aceptar ideas cuando superen a las nuestras; la capacidad de mantener una interacción productiva y saber manejar conflictos, cuando surjan.
	</li>
	<li class="question">¿Trabajo colaborativo es lo mismo que trabajo en grupo?</li>
	<li class="answer">
		No, el trabajo colaborativo se entiende como un esfuerzo que genera sinergia. Esto es, la suma del rendimiento de todos los miembros no da como resultado el 100 %, sino que alcanza un nivel "óptimo" mayor al 100 %. El compromiso es superior que en el trabajo en grupo, por cuanto este se entienda más como la división de tareas para alcanzar un propósito.
	</li>
</ul>
{/block}
{block name="tasks"}
<ul class="tasks">
	<li class="task">
		<span class="marker" style="background-image: url('/themes/images/walkthrough/i-check-green.png');" aria-hidden="true"></span>
		Registrar a tu equipo en Platzila
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2e1" class="link walkthrough-box-blue">Ver cómo</a>
	</li>
	<li class="task">
		<span class="marker" style="background-image: url('/themes/images/walkthrough/i-check-green.png');" aria-hidden="true"></span>
		Asignar tareas al equipo y a miembros del equipo
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2e2" class="link walkthrough-box-purple">Ver cómo</a>
	</li>
	<li class="task">
		<span class="marker" style="background-image: url('/themes/images/walkthrough/i-check-green.png');" aria-hidden="true"></span>
		Fomentar la comunicación y colaboración
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2e3" class="link walkthrough-box-red">Ver cómo</a>
	</li>
	<li class="task">
		<span class="marker" style="background-image: url('/themes/images/walkthrough/i-check-green.png');" aria-hidden="true"></span>
		Dar feedback de calidad para mejorar continuamente
		<a href="index.php?module=Walkthrough&action=DetailsView&page=2e4" class="link walkthrough-box-orange">Ver cómo</a>
	</li>
</ul>
{/block}