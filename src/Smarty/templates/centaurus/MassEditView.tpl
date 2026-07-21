{strip}
<link type="text/css" href="themes/{$THEME}/css/libs/datepicker.css" rel="stylesheet" />
<link type="text/css" href="themes/{$THEME}/css/libs/daterangepicker.css" rel="stylesheet" />
<link type="text/css" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/pipeline.min.css" />
<style type="text/css">
	#related-module-records {
		top:     0;
		z-index: 3000;
	}
	#related-module-records .modal-body {
		max-height: 70vh;
		min-height: 70vh;
		overflow-x: hidden;
		overflow-y: auto;
	}
	#related-module-records #search {
		margin-bottom: 0.5em;
	}
	#related-module-records .radio {
		display:      block;
		padding-left: 15px;
	}
</style>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/moment.min.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/daterangepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="modules/Settings/fieldValidationsAjax.js"></script>
{assign var="MODULELABEL" value=$MODULE|@getTranslatedString: $MODULE}
{$ERROR_MESSAGE}
{if ($MODULE == 'Contacts')}
<input type="hidden" name="activity_mode" value="{$ACTIVITY_MODE}" />
<input type="hidden" name="opportunity_id" value="{$OPPORTUNITY_ID}" />
<input type="hidden" name="contact_role" />
<input type="hidden" name="case_id" value="{$CASE_ID}" />
<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
<input type="hidden" name="campaignid" value="{$campaignid}" />
<input type="hidden" name="date_create" value="{$date_create}" />
{elseif $MODULE == 'Calendar'}
<input type="hidden" name="activity_mode" value="{$ACTIVITY_MODE}" />
<input type="hidden" name="product_id" value="{$PRODUCTID}" />
{elseif $MODULE eq 'Documents'}
<input type="hidden" name="max_file_size" value="{$MAX_FILE_SIZE}" />
<input type="hidden" name="form" />
<input type="hidden" name="email_id" value="{$EMAILID}" />
<input type="hidden" name="ticket_id" value="{$TICKETID}" />
<input type="hidden" name="fileid" value="{$FILEID}" />
<input type="hidden" name="old_id" value="{$OLD_ID}" />
<input type="hidden" name="parentid" value="{$PARENTID}" />
{/if}
{if (!empty ($BLOCKS))}
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
{else}
	<div class="alert alert-info">
		<strong>Información: </strong> El módulo no tiene campos habilitados para edición masiva
	</div>
{/if}
{if ($MODULE == 'Documents')}
<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
	var textAreaName = 'notecontent',
		oCKeditor;
	CKEDITOR.replace (textAreaName, {
		extraPlugins: 'uicolor',
		uiColor:      '#dfdff1',
		height:       '200',
		width:        '800'
	});
	oCKeditor = CKEDITOR.instances[ textAreaName ];
</script>
{/if}
<script type="text/javascript">
	var fieldname = [{$VALIDATION_DATA_FIELDNAME}],
		fieldlabel = [{$VALIDATION_DATA_FIELDLABEL}],
		fielddatatype = [{$VALIDATION_DATA_FIELDDATATYPE}],
		ProductImages = [],
		count = 0;

	function delRowEmt (imagename) {
		ProductImages[ count++ ] = imagename;
	}

	function displaydeleted () {
		var imagelists = '';
		for (var x = 0; x < ProductImages.length; x++) {
			imagelists += ProductImages[ x ] + '###';
		}
		if (imagelists != '') {
			document['EditView']['imagelist'].value = imagelists;
		}
	}
</script>
{if $PICKIST_DEPENDENCY_DATASOURCE neq ''}
<script type="text/javascript" src="include/js/FieldDependencies.js"></script>
<script type="text/javascript">
	jQuery (document).ready (function () { (new FieldDependencies ({$PICKIST_DEPENDENCY_DATASOURCE})).init (); });
</script>
{/if}
{/strip}