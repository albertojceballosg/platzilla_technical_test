{strip}
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" />
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<style type="text/css">
{literal}
	.repercussions > .panel > .panel-heading {
		border: 1px solid #DDDDDD;
		margin-bottom: 1em;
	}
	.repercussions > .panel > .panel-heading > a > .panel-title {
		display:    inline-block;
		min-height: 2em;
		width:      90%;
	}
	.panel.others > .panel-collapse > .panel-body {
		border: 0;
	}
	.panel.others h4 {
		font-size: 1em;
	}
	.drop-zone {
		background-color: #ffffff;
		border:           1px dashed;
		height:           5em;
		position:         relative;
		text-align:       center;
	}
	.drop-zone > input[type='file'] {
		bottom:   0;
		cursor:   pointer;
		left:     0;
		opacity:  0;
		position: absolute;
		top:      0;
		width:    100%;
	}
	.drop-zone > .title {
		line-height: 4.75em;
	}
	.attachments-container {
		list-style: none;
		margin-top: 10px;
	}
	.attachments-container > .attachment {
		border:         1px solid #DDDDDD;
		padding-bottom: 20px;
		padding-top:    5px;
		position:       relative;
	}
	.attachments-container > .attachment > .btn-close {
		background-color: transparent;
		border:           0;
		bottom:           0;
		line-height:      1;
		right:            0;
		padding:          0 5px 2px 5px;
		position:         absolute;
		text-transform:   uppercase;
		z-index:          1000;
	}
	.attachments-container > .attachment > .name {
		background-color: #FFFFFF;
		bottom:           0;
		left:             0;
		margin:           0;
		position:         absolute;
		right:            0;
		text-align:       center;
		z-index:          999;
	}
	.attachments-container > .attachment > a > .image-container {
		height:         0;
		padding-bottom: 100%;
		position:       relative;
	}
	.attachments-container > .attachment > a > .image-container > .image {
		left:       50%;
		max-height: 100%;
		max-width:  100%;
		position:   absolute;
		top:        50%;
		transform:  translate(-50%, -50%);
	}
	.action-bar {
		padding: 0 15px 10px 0;
	}
	.image-viewer {
		background-color: rgba(0, 0, 0, 0.9);
		height:           100%;
		left:             0;
		overflow:         auto;
		padding-top:      100px;
		position:         fixed;
		top:              0;
		width:            100%;
		z-index:          10000;
	}
	.image-viewer > .viewer-content-container {
		display:    block;
		margin:     auto;
		max-width:  700px;
		text-align: center;
		width:      80%;
	}
	.image-viewer > .viewer-content-container > .viewer-content {
		max-width: 100%;
	}
	.image-viewer > .viewer-caption {
		color:      #ccc;
		display:    block;
		height:     150px;
		padding:    10px 0;
		margin:     auto;
		max-width:  700px;
		text-align: center;
		width:      80%;
	}
	.image-viewer > .viewer-close {
		color:       #f1f1f1;
		font-size:   40px;
		font-weight: bold;
		position:    absolute;
		right:       35px;
		top:         15px;
	}
	.image-viewer > .viewer-close:hover,
	.image-viewer > .viewer-close:focus {
		color:           #bbb;
		cursor:          pointer;
		text-decoration: none;
	}
{/literal}
</style>
<div class="row">
	<div class="col-lg-12">
		<div class="col-lg-12 pull-left">
			<h1><a href="index.php?action=ListView&amp;module=repercusiones_prensa">Repercusiones de prensa</a></h1>
		</div>
	</div>
	<div class="col-lg-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h2 class="pull-left">Información general</h2>
				<div class="action-bar">
					<button type="button" class="btn pull-right" onclick="MassCreateUtils.addRepercussion (this);">Agregar</button>
				</div>
			</header>
			<div class="main-box-body clearfix">
				<form method="post" action="index.php" name="EditView" onsubmit="return MassCreateUtils.validateRepercussions ()">
					<input type="hidden" name="module" value="repercusiones_prensa" />
					<input type="hidden" name="action" value="MassCreateSave" />
					<div class="panel-group repercussions" id="repercussions">
{if (isset ($SELECTED_NEWS))}
	{foreach $SELECTED_NEWS as $news}
		{include file="modules/repercusiones_prensa/MassCreateDetails.tpl" ID=$news.rssnewsid DATE=$news.publicationdate MEDIA=$news.media|escape:'html' MEDIA_NAME=$news.nombre_de_la_entidad TITLE=$news.headline|escape:'html' URL=$news.url}
	{/foreach}
{else}
	{include file="modules/repercusiones_prensa/MassCreateDetails.tpl" ID=0 TITLE='' MEDIA='' URL=''}
{/if}
					</div>
					<div class="action-bar">
						<button type="submit" class="btn btn-info pull-right">Crear repercusiones</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/html" id="attachment-template">
	<li id="attachment-__attachment-id__" class="col-md-3 attachment">
		<button type="button" class="btn btn-close" onclick="MassCreateUtils.deleteAttachment (this);">X</button>
		<a href="#" onclick="MassCreateUtils.viewImage (this); return false;">
			<div class="image-container">
				<canvas class="image"></canvas>
			</div>
		</a>
		<p class="name"></p>
		<input type="hidden" name="attachments[__repercussion-id__][data][]" class="field-attachment-data" />
		<input type="hidden" name="attachments[__repercussion-id__][filename][]" class="field-attachment-filename" />
	</li>
</script>
<script type="text/html" id="image-viewer-template">
	<div class="image-viewer">
		<span class="viewer-close" onclick="MassCreateUtils.closeImageViewer (this);">&times;</span>
		<div class="viewer-content-container">
			<img class="viewer-content" src="#" />
		</div>
		<div class="viewer-caption"></div>
	</div>
</script>
<script type="text/html" id="repercussion-template">
{include file="modules/repercusiones_prensa/MassCreateDetails.tpl" ID='__repercussion-id__'}
</script>
<script type="text/javascript" src="modules/repercusiones_prensa/mass-create.js"></script>
{/strip}