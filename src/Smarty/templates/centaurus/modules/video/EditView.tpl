{extends file="base/EditView.tpl"}

{block name="css"}

<!-- libraries -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nanoscroller.css" />
<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/dropzone.css">
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/magnific-popup.css">
{*
<link href="modules/video/html5player/video-js.css" rel="stylesheet" type="text/css">
<script src="modules/video/html5player/video.js"></script>
*}
{/block}
									
{block name="content"}
<div class="row" id="ListViewContents">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="title-section main-box-header clearfix">
				<h2>Video</h2>
			</header>
			
			<div class="main-box-body clearfix">
				{if !$RECORD}
				<div class="row">
					<div class="col-lg-12">
						<div class="main-box-body">
							
							<div style="margin-top: -1em;margin-bottom: 1em;">
								<small>Tamaño maximo (150M)</small>
							</div>

							<div id="mydropzone">
								<form id="dupload" class="dropzone dz-clickable" enctype="multipart/form-data" style="min-height: 200px;">
								
									<div class="dz-default dz-message">
										<span>Drop files here to upload</span>
									</div>
								
								</form>
							</div>
						</div>
					</div>
				</div>
				{/if}
				<form action="index.php?module=video&action=Save" id="videoform" name="videoform" method="post" >
					{if $RECORD}
					<input type="hidden" name="record" value="{$RECORD}"/>	
					{else}
					<input type="hidden" name="imgid" id="imgid" value="0"/>	
					{/if}

					<div class="row">
						<div class="col-xs-12">							
							<div class="col-xs-1">
								<div class="label-input">
									<label for="titulo"><h4>T&iacute;tulo</h4></label>
								</div>
							</div>						
							<div class="col-lg-11">
								<div class="form-group col-md-12">
									<input type="text" class="form-control" id="titulo" name="titulo" value="{$VIDEO.titulo}">
									<span class="help-block"></span>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12">
							<div class="col-xs-1">
								<div class="label-input">
									<label for="description"><h4>Descripci&oacute;n</h4></label>
								</div>
							</div>						
							<div class="col-lg-11">
								<div class="form-group col-md-12">
									<textarea class="form-control" id="description" name="description" rows="3">{$VIDEO.description}</textarea>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

{/block}

{block name="buttons-bar"}
	<input value="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success" type="button" onclick="jQuery('#videoform').submit();return false;">
	<input value="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-default" onclick="window.history.back()" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}  " >
{/block}

{block name="js" append}

<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/dropzone.js"></script>

{/block}

{block name="scripts" append}

<script type="text/javascript" language="javascript">
{literal}
jQuery(function() {
	Dropzone.options.dupload = { // The camelized version of the ID of the form element
		// The configuration we've talked about above
		paramName: "file",
		maxFilesize: 100,
		acceptedFiles:"video/*",
		maxFiles: 1,
		url: 'index.php?module=video&action=Save',
		complete: function(file) {
			//console.log(file);
			//console.log(file.xhr.responseText);
			response=file.xhr.responseText.split('::');
			if(response[0]=='success')
				jQuery('#imgid').val(response[1])
		},	
	}	
});
{/literal}
</script>

{/block}