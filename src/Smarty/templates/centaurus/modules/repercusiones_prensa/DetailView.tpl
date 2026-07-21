<script type="text/javascript" src="include/js/dtlviewajax.js"></script>
<style type="text/css">
{literal}
	.attachments-container {
		list-style: none;
		margin-top: 10px;
		padding:    0;
	}
	.attachments-container > .attachment {
		border:         1px solid #DDDDDD;
		display:        inline-block;
		padding-bottom: 20px;
		padding-top:    5px;
		position:       relative;
		width:          25%;
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
{include file='Buttons_List.tpl'}
<div class="tabs-wrapper">
	<ul class="nav nav-tabs">
		<li class="active">
			<a data-toggle="tab" href="#tab-detail">{$APP.LBL_INFORMATION}</a>
		</li>
{if isset($COL_ACCIONES) && $COL_ACCIONES neq 'false'}
	{include file='DetailViewActions.tpl'}
{/if}
{if !empty($IS_REL_LIST)}
		<li class="dropdown">
			<a class="dropdown-toggle" href="#" data-toggle="dropdown">{$APP.LBL_MORE} {$APP.LBL_INFORMATION}
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu" role="menu">
	{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
				<li><a role="menuitem" tabindex="-1" href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}&platdb={$PLATDB}">{$_RELATED_MODULE|@getTranslatedString:$MODULE}</a></li>
	{/foreach}
			</ul>
		</li>
{/if}
	</ul>
	<div id="tab-detail" class="tab-pane fade in active">
		<form action="index.php" method="post" name="DetailView" id="form">
{include file='DetailViewHidden.tpl'}
{foreach key=header item=detail from=$BLOCKS}
	{if $header eq $MOD.LBL_COMMENTS || $header eq $MOD.LBL_COMMENT_INFORMATION}
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box">
						<header class="title-section main-box-header clearfix">
							<h2>{$MOD.LBL_COMMENT_INFORMATION}</h2>
						</header>
						<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
							{$COMMENT_BLOCK}
						</div>
					</div>
				</div>
			</div>
	{else}
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box">
						<header class="title-section main-box-header clearfix">
							<h2>{$header}</h2>
						</header>
						<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
		{assign var=detailD value=$detail}
		{foreach item=detail from=$detailD}
			{foreach key=label item=data from=$detail}
				{assign var=keyid value=$data.ui}
				{assign var=keyval value=$data.value}
				{assign var=keytblname value=$data.tablename}
				{assign var=keyfldname value=$data.fldname}
				{assign var=keyfldid value=$data.fldid}
				{assign var=keyoptions value=$data.options}
				{assign var=keysecid value=$data.secid}
				{assign var=keyseclink value=$data.link}
				{assign var=keycursymb value=$data.cursymb}
				{assign var=keysalut value=$data.salut}
				{assign var=keyaccess value=$data.notaccess}
				{assign var=keycntimage value=$data.cntimage}
				{assign var=keyadmin value=$data.isadmin}
				{assign var=display_type value=$data.displaytype}
				{assign var=_readonly value=$data.readonly}
				{if $label ne ''}
					{if $keycntimage ne ''}
								<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin} />{$keycntimage}
					{elseif $keyid eq '14'}
								<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin} />
					{/if}
					{if $EDIT_PERMISSION eq 'yes' && $display_type neq '2' && $_readonly eq '0'}
						{if !empty($DETAILVIEW_AJAX_EDIT) }
							{include file="DetailViewUI.tpl"}
						{else}
							{include file="DetailViewFields.tpl"}
						{/if}
					{else}
						{include file="DetailViewFields.tpl"}
					{/if}
				{/if}
			{/foreach}
		{/foreach}
						</div>
					</div>
				</div>
			</div>
	{/if}
{/foreach}
{if (!empty ($RELATED_NOTES))}
			<div class="row block-container" id="block_attachments">
				<div class="col-xs-12">
					<div class="main-box">
						<header class="title-section main-box-header">
							<h2 class="col-md-10">Anexos</h2>
						</header>
						<div class="main-box-body" id="tblAnexos">
{strip}
								<ul class="attachments-container">
	{foreach $RELATED_NOTES as $note}
									<li id="attachment-{$note.attachmentsid}" class="attachment">
										<a href="#" onclick="MassCreateUtils.viewImage (this); return false;">
											<div class="image-container">
												<img src="index.php?module=uploads&action=downloadfile&entityid={$ID}&fileid={$note.attachmentsid}" alt="{$note.title}" class="image" />
											</div>
										</a>
										<p class="name">{$note.filename}</p>
										<input type="hidden" class="field-attachment-data" value="index.php?module=uploads&action=downloadfile&entityid={$ID}&fileid={$note.attachmentsid}" />
									</li>
	{/foreach}
								</ul>
{/strip}
						</div>
					</div>
				</div>
			</div>
{/if}
		</form>
	</div>
</div>
<script type="text/html" id="image-viewer-template">
	<div class="image-viewer">
		<span class="viewer-close" onclick="MassCreateUtils.closeImageViewer (this);">&times;</span>
		<div class="viewer-content-container">
			<img class="viewer-content" src="#" />
		</div>
		<div class="viewer-caption"></div>
	</div>
</script>
<script type="text/javascript" src="modules/repercusiones_prensa/mass-create.js"></script>
<script type="text/javascript">
	var fieldname = new Array ({$VALIDATION_DATA_FIELDNAME});
	var fieldlabel = new Array ({$VALIDATION_DATA_FIELDLABEL});
	var fielddatatype = new Array ({$VALIDATION_DATA_FIELDDATATYPE});
</script>