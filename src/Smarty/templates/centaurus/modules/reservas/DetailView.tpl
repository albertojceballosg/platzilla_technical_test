<script type="text/javascript" src="include/js/dtlviewajax.js"></script>
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
{if (isset ($RELATED_USERS))}
			<div class="row block-container" id="block_2">
				<div class="col-xs-12">
					<div class="main-box">
						<header class="title-section main-box-header">
							<h2 class="col-md-10">Usuarios</h2>
						</header>
						<div class="main-box-body" id="tblUsuarios">
							<div class="table-responsive">
								<table class="table users">
									<thead>
									<tr>
										<th class="col-code">Código</th>
										<th class="col-name">Nombres y apellidos</th>
										<th class="col-phone">Teléfono</th>
										<th class="col-email">Email</th>
									</tr>
									</thead>
									<tbody>
	{foreach $RELATED_USERS as $user}
										<tr>
											<td class="col-code">
												<span class="code">{$user.cod_usuarios_col}</span>
											</td>
											<td class="col-name">{$user.nombres} {$user.apellidos}</td>
											<td class="col-phone">{$user.telf}</td>
											<td class="col-email">{$user.correo_electronico}</td>
										</tr>
	{/foreach}
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
{/if}
{if (isset ($RELATED_LOCATIONS))}
			<div class="row block-container" id="block_3">
				<div class="col-xs-12">
					<div class="main-box">
						<header class="title-section main-box-header">
							<h2 class="col-md-10">Espacios</h2>
						</header>
						<div class="main-box-body" id="tblEspacios">
							<div class="table-responsive">
								<table class="table locations">
									<thead>
									<tr>
										<th class="col-code">Código</th>
										<th class="col-number">Número</th>
										<th class="col-type">Tipo</th>
										<th class="col-location">Centro</th>
									</tr>
									</thead>
									<tbody>
	{foreach $RELATED_LOCATIONS as $location}
										<tr>
											<td class="col-code">
												<span class="code">{$location.cod_espacios}</span>
											</td>
											<td class="col-number">{$location.numero_}</td>
											<td class="col-type">{$location.tipo_area}</td>
											<td class="col-location">{$location.centro}</td>
										</tr>
	{/foreach}
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
{/if}
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
	var fieldname = new Array ({$VALIDATION_DATA_FIELDNAME});
	var fieldlabel = new Array ({$VALIDATION_DATA_FIELDLABEL});
	var fielddatatype = new Array ({$VALIDATION_DATA_FIELDDATATYPE});
</script>