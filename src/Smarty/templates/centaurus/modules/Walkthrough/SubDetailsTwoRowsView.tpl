{strip}
<link type="text/css" rel="stylesheet" href="modules/Walkthrough/Walkthrough.css" />
<div class="row walkthrough-subdetail-page">
	<h1 class="text-center title {block name="bg-color-class"}{/block}">
		<a href="{block name="link-back"}{/block}" class="link-back"><img src="themes/images/walkthrough/i-back.png" /></a>
		<span>{block name="detail-title"}{/block}</span>
		<img src="themes/images/walkthrough/i-check-white.png" class="icon" />
	</h1>
	<div class="walkthrough-subdetail two-rows">
		<div class="cell-container">
			<div class="col-xs-12 col-md-4 video-container">
				<div class="embed-responsive embed-responsive-16by9 video" data-vimeo-url="{block name="video-url"}{/block}"></div>
			</div>
			<div class="col-xs-12 col-md-8 step-by-step-container">
				<h2 class="step-by-step-title">Paso a paso:</h2>
				{block name="step-by-step"}{/block}
			</div>
		</div>
		<div class="cell-container">
			<div class="col-xs-12 faq-container">
				<h2 class="faq-title">Preguntas frecuentes:</h2>
				{block name="faq"}{/block}
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
{/strip}