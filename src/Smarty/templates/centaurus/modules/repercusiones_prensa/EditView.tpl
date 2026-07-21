<link rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/centaurus/css/libs/daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/centaurus/css/libs/font-awesome.min.css" type="text/css" />
<style type="text/css">
	{literal}
		.drop-zone {
			background-color: #ffffff;
			border: 1px dashed;
			height: 5em;
			position: relative;
			text-align: center;
		}

		.drop-zone>input[type='file'] {
			bottom: 0;
			cursor: pointer;
			left: 0;
			opacity: 0;
			position: absolute;
			top: 0;
			width: 100%;
		}

		.drop-zone>.title {
			line-height: 4.75em;
		}

		.attachments-container {
			list-style: none;
			margin-top: 10px;
			padding: 0;
		}

		.attachments-container>.attachment {
			border: 1px solid #DDDDDD;
			display: inline-block;
			padding-bottom: 20px;
			padding-top: 5px;
			position: relative;
			width: 25%;
		}

		.attachments-container>.attachment>.btn-close {
			background-color: transparent;
			border: 0;
			bottom: 0;
			line-height: 1;
			right: 0;
			padding: 0 5px 2px 5px;
			position: absolute;
			text-transform: uppercase;
			z-index: 1000;
		}

		.attachments-container>.attachment>.name {
			background-color: #FFFFFF;
			bottom: 0;
			left: 0;
			margin: 0;
			position: absolute;
			right: 0;
			text-align: center;
			z-index: 999;
		}

		.attachments-container>.attachment>a>.image-container {
			height: 0;
			padding-bottom: 100%;
			position: relative;
		}

		.attachments-container>.attachment>a>.image-container>.image {
			left: 50%;
			max-height: 100%;
			max-width: 100%;
			position: absolute;
			top: 50%;
			transform: translate(-50%, -50%);
		}

		.image-viewer {
			background-color: rgba(0, 0, 0, 0.9);
			height: 100%;
			left: 0;
			overflow: auto;
			padding-top: 100px;
			position: fixed;
			top: 0;
			width: 100%;
			z-index: 10000;
		}

		.image-viewer>.viewer-content-container {
			display: block;
			margin: auto;
			max-width: 700px;
			text-align: center;
			width: 80%;
		}

		.image-viewer>.viewer-content-container>.viewer-content {
			max-width: 100%;
		}

		.image-viewer>.viewer-caption {
			color: #ccc;
			display: block;
			height: 150px;
			padding: 10px 0;
			margin: auto;
			max-width: 700px;
			text-align: center;
			width: 80%;
		}

		.image-viewer>.viewer-close {
			color: #f1f1f1;
			font-size: 40px;
			font-weight: bold;
			position: absolute;
			right: 35px;
			top: 15px;
		}

		.image-viewer>.viewer-close:hover,
		.image-viewer>.viewer-close:focus {
			color: #bbb;
			cursor: pointer;
			text-decoration: none;
		}

	{/literal}
</style>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/daterangepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
{assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}
{if $APP.$SINGLE_MOD}
	{assign var="SINGLE_MOD_LABEL" value=$APP.SINGLE_MOD}
{else}
	{assign var="SINGLE_MOD_LABEL" value=$SINGLE_MOD}
{/if}
<div class="row">
	<div class="col-xs-12">
		<h1 id="title-view">
			<a
				href="index.php?action=ListView&module={$MODULE}&parenttab={$CATEGORY}">{$SINGLE_MOD|@getTranslatedString:$MODULE}</a>
		</h1>
	</div>
</div>
<form name="EditView" method="post" action="index.php" enctype="multipart/form-data"
	onsubmit="VtigerJS_DialogBox.block(); return false;" role="form" class="repercussion"
	data-id="{if (isset ($ID))}{$ID}{else}0{/if}">
	<input type="hidden" name="pagenumber" value="{$smarty.request.start|@vtlib_purify}">
	<input type="hidden" name="module" value="{$MODULE}">
	<input type="hidden" name="record" value="{$ID}">
	<input type="hidden" name="mode" value="{$MODE}">
	<input type="hidden" name="action">
	<input type="hidden" name="parenttab" value="{$CATEGORY}">
	<input type="hidden" name="return_module" value="{$RETURN_MODULE}">
	<input type="hidden" name="return_id" value="{$RETURN_ID}">
	<input type="hidden" name="return_action" value="{$RETURN_ACTION}">
	<input type="hidden" name="return_viewname" value="{$RETURN_VIEWNAME}">
	<input type="hidden" name="createmode" value="{$CREATEMODE}" />
	{if $smarty.request.frontendsid}
		<input type="hidden" name="frontendsid" value="{$smarty.request.frontendsid}" />
	{/if}
	{if $PLATDB neq ''}
		<input type="hidden" name="platdb" value="{$PLATDB}" />
	{/if}
	{foreach key=header item=data from=$BLOCKS name=block}
		<div class="row block-container" id="block_{$smarty.foreach.block.iteration}">
			<div class="col-xs-12">
				<div class="main-box">
					<header class="title-section main-box-header clearfix">
						<h2>{$header}</h2>
					</header>
					<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
						{include file="DisplayFields.tpl"}
					</div>
				</div>
			</div>
		</div>
	{/foreach}
	<div class="row block-container" id="block_2">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="title-section main-box-header">
					<h2 class="col-md-10">Anexos</h2>
				</header>
				<div class="main-box-body" id="tblAnexos">
					<div class="drop-zone">
						<input type="file" multiple="multiple"
							onchange="MassCreateUtils.addAttachments (event || window.event);" />
						<span class="title">Arrastra imágenes aquí o haz clic</span>
					</div>
					{strip}
						<ul class="attachments-container">
							{if (!empty ($RELATED_NOTES))}
								{foreach $RELATED_NOTES as $note}
									<li id="attachment-{$note.attachmentsid}" class="attachment">
										<button type="button" class="btn btn-close"
											onclick="MassCreateUtils.deleteAttachment (this);">X</button>
										<a href="#" onclick="MassCreateUtils.viewImage (this); return false;">
											<div class="image-container">
												<img src="index.php?module=uploads&action=downloadfile&entityid={$ID}&fileid={$note.attachmentsid}"
													alt="{$note.title}" class="image" />
											</div>
										</a>
										<p class="name">{$note.filename}</p>
										<input type="hidden" class="field-attachment-data"
											value="index.php?module=uploads&action=downloadfile&entityid={$ID}&fileid={$note.attachmentsid}" />
										<input type="hidden" name="attachments[old][]" class="field-attachment-filename"
											value="{$note.attachmentsid}" />
									</li>
								{/foreach}
							{/if}
						</ul>
					{/strip}
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix" style="height:25px; margin-bottom: 16px;"></div>
	<div class="row">
		<div id="fixed-btns-bar" style="display:block">
			<div class="container">
				<div class="row">
					<div class="col-xs-12" style="padding:25px;height: 75px;">
						{if $MODE eq 'edit'}
							<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
								class="btn btn-success"
								onclick="this.form.action.value='Save'; if(formValidate()) {ldelim}  validationCheckFields('{$MODULE}',this.form); {rdelim} "
								type="button" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  ">
						{else}
							<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
								class="btn btn-success"
								onclick="this.form.action.value='Save';  if(formValidate()){ldelim} validationCheckFields('{$MODULE}',this.form);{rdelim}"
								type="button" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  ">
						{/if}
						{if $POPUPCREATE neq 'create'}
							<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}"
								class="btn btn-default" onclick="window.history.back()" type="button" name="button"
								value="{$APP.LBL_CANCEL_BUTTON_LABEL}  ">
						{else}
							<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}"
								class="btn btn-default" onclick="window.close();" type="button" name="button"
								value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  ">
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
	<input name='search_url' id="search_url" type='hidden' value='{$SEARCH}'>
</form>
<script type="text/html" id="attachment-template">
	<li id="attachment-__attachment-id__" class="attachment">
		<button type="button" class="btn btn-close" onclick="MassCreateUtils.deleteAttachment (this);">X</button>
		<a href="#" onclick="MassCreateUtils.viewImage (this); return false;">
			<div class="image-container">
				<canvas class="image"></canvas>
			</div>
		</a>
		<p class="name"></p>
		<input type="hidden" name="attachments[new][data][]" class="field-attachment-data" />
		<input type="hidden" name="attachments[new][filename][]" class="field-attachment-filename" />
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
<script type="text/javascript" src="modules/Settings/fieldValidationsAjax.js"></script>
<script type="text/javascript" src="modules/repercusiones_prensa/mass-create.js"></script>
{if $PICKIST_DEPENDENCY_DATASOURCE != ''}
	<script type="text/javascript" src="include/js/FieldDependencies.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			(new FieldDependencies({$PICKIST_DEPENDENCY_DATASOURCE})).init();
		});
	</script>
{/if}
<script type="text/javascript">
	{literal}
		var fieldname = [{/literal}{$VALIDATION_DATA_FIELDNAME}{literal}];
		var fieldlabel = [{/literal}{$VALIDATION_DATA_FIELDLABEL}{literal}];
		var fielddatatype = [{/literal}{$VALIDATION_DATA_FIELDDATATYPE}{literal}];
		var ProductImages = [];
		var count = 0;

		function delRowEmt(imagename) {
			ProductImages[count++] = imagename;
		}

		function displaydeleted() {
			var imagelists = '';
			for (var x = 0; x < ProductImages.length; x++) {
				imagelists += ProductImages[x] + '###';
			}

			if (imagelists != '') {
				document.EditView.imagelist.value = imagelists;
			}
		}

		function openPopup() {
			window.open("index.php?module=Users&action=UsersAjax&file=RolePopup&parenttab=Settings", "roles_popup_window",
				"height=425,width=640,toolbar=no,menubar=no,dependent=yes,resizable =no");
		}
	{/literal}
</script>