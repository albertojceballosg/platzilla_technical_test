{strip}
<link type="text/css" rel="stylesheet" href="modules/Walkthrough/Walkthrough.css?v=1.1" />
<div class="row walkthrough-detail-page">
	<h1 class="text-center title {block name="bg-color-class"}{/block}">
		<a href="index.php?module=Walkthrough&action=index" class="link-back"><img src="themes/images/walkthrough/i-back.png" /></a>
		<span>{block name="detail-title"}{/block}</span>
		<img src="themes/images/walkthrough/{block name="icon-file-name"}{/block}" class="icon" />
	</h1>
	<div class="walkthrough-detail">
		<div class="col-xs-12 col-md-8">
			<div class="diagram-container">
				<h2 class="diagram-title">{block name="diagram-title"}{/block}</h2>
				<p class="diagram-subtitle">{block name="diagram-subtitle"}{/block}</p>
				<img src="themes/images/walkthrough/{block name="diagram-file-name"}{/block}" class="diagram img-responsive" />
			</div>
		</div>
		<div class="col-xs-12 col-md-4">
			<div class="video-container">
				<h2 class="video-title">Mira el video explicativo...</h2>
				<div class="embed-responsive embed-responsive-16by9" data-vimeo-url="{block name="video-url"}{/block}"></div>
			</div>
		</div>
		<div class="col-xs-12 col-md-4">
			<div class="faq-container">
				<h2 class="faq-title">Preguntas frecuentes:</h2>
				{block name="faq"}{/block}
			</div>
		</div>
		<div class="col-xs-12 col-md-8">
			<div class="tasks-container">
				<h2 class="tasks-title">Tareas para comenzar:</h2>
				{block name="tasks"}{/block}
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
{/strip}