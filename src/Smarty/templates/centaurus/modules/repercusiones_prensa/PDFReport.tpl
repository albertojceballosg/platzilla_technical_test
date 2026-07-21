{strip}
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<title>Informe de repercusiones para {$CUSTOMER.nombre_de_la_entidad}</title>
	<style type="text/css">
		@page {
			padding: 0;
			size:    auto;
		}
		@page cover {
			/*noinspection CssUnknownProperty*/
			footer: cover_footer;
			margin: 75mm 10mm 75mm 10mm;
		}
		@page index {
			/*noinspection CssUnknownProperty*/
			footer: index_footer;
			margin: 20mm 10mm 40mm 10mm;
		}
		body {
			font-family: Verdana, Geneva, sans-serif;
			font-size:   14px;
			margin:      0;
			padding:     0;
		}
		.cover {
			page: cover;
		}
		.index {
			page: index;
		}
	</style>
</head>
<body>
{if (!empty ($ADD_COVER))}
	<htmlpagefooter name="cover_footer">
		<div style="padding-bottom: 30px;">
			<p style="text-align: center; width: 100%;">Un servicio de:</p>
			<div style="margin: 0 auto; width: 90px;">
				<img src="{$LOGO_URI}" alt="Logo" style="max-width: 90px;" />
			</div>
		</div>
	</htmlpagefooter>
	<div class="cover" style="text-align: center;">
		<h1 style="font-size: 24px; font-weight: bold; text-align: center;">INFORME DE REPERCUSIONES</h1>
	{if (!empty ($CUSTOMER.logourl))}
		<img src="{$CUSTOMER.logourl}" style="margin-left: 5px; margin-top: 4px; max-width: 370px;" />
	{else}
		<h1 style="font-size: 24px; font-weight: bold; text-align: center;">{$CUSTOMER.nombre_de_la_entidad}</h1>
	{/if}
	</div>
{/if}
{if ((!empty ($ADD_INDEX)) || (!empty ($ONLY_INDEX)))}
	<htmlpagefooter name="index_footer">
		<div style="padding-bottom: 30px;">
			<div style="margin: 0 auto; width: 45px;">
				<img src="{$LOGO_URI}" alt="Logo" style="max-width: 45px;" />
			</div>
		</div>
	</htmlpagefooter>
	<div class="index">
		<h2 style="font-size: 24px; font-weight: bold; text-align: center;">Índice</h2>
		<table style="border-spacing: 0; border-collapse: collapse; width: 100%;">
			<thead>
			<tr>
				<th style="background-color: #eaf2fd; font-weight: normal; padding: 5px; width: 25%;">Medio</th>
				<th style="background-color: #eaf2fd; font-weight: normal; padding: 5px;">Titular</th>
				<th style="background-color: #eaf2fd; font-weight: normal; padding: 5px; width: 15%;">Fecha</th>
			</tr>
			</thead>
			<tbody>
	{foreach $REPERCUSSIONS as $repercussion}
			<tr>
				<td style="padding: 5px;">{$repercussion.medio}</td>
				<td style="padding: 5px;">{$repercussion.titular}</td>
				<td style="padding: 5px; text-align: center;">{$repercussion.fecha|date_format: 'd/m/Y'}</td>
			</tr>
	{/foreach}
			</tbody>
		</table>
	</div>
{/if}
{if (empty ($ONLY_INDEX))}
	{assign var='currentPage' value=1}
	{foreach $REPERCUSSIONS as $repercussion}
		{assign var='page' value=1}
		{assign var='totalPages' value=count ($repercussion.attachments)}
		{if (empty ($repercussion.tinyurl))}
			{assign var='tinyurl' value=null}
		{else}
			{assign var='tinyurl' value=$repercussion.tinyurl}
		{/if}
		{foreach $repercussion.attachments as $attachment}
	<style type="text/css">
		@page repercussion-{$currentPage} {
			/*noinspection CssUnknownProperty*/
			header: repercussion_header_{$currentPage};
			/*noinspection CssUnknownProperty*/
			footer: repercussion_footer_{$currentPage};
			margin: 37mm 8mm 33mm 8mm;
		}
		#repercussion-{$currentPage} {
			page: repercussion-{$currentPage};
		}
	</style>
	<htmlpageheader name="repercussion_header_{$currentPage}">
		<table style="border-spacing: 0; border-collapse: separate; padding-top: 6mm; width: 100%;">
			<tbody>
			<tr>
				<td style="border: 1px solid #000000; padding: 10px; width: 120px;">
					<img src="{$LOGO_URI}" alt="Logo" style="max-width: 120px;" /></td>
				<td style="width: 1px;"></td>
				<td style="border: 1px solid #000000; text-align: center;">
					<h2 style="font-size: 30px; font-weight: bold;">{$repercussion.medio}</h2>
					<br />
					<p style="font-weight: bold;">{$repercussion.fecha|date_format: 'd/m/Y'}</p>
				</td>
			</tr>
		</table>
	</htmlpageheader>
	<htmlpagefooter name="repercussion_footer_{$currentPage}">
		<div style="padding-bottom: 38px;">
			<div style="border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000;">
				<p style="text-align: center; width: 100%;">{if (!empty ($tinyurl))}<a href="{$tinyurl}">{$tinyurl}</a>{/if}</p>
				<div style="margin: 0 auto; text-align: center; width: 75%;">
			{assign var='area' value=floatval ($repercussion.superficie_)}
			{if (!empty ($area))}
					&nbsp;&nbsp;
					<span>
						<strong>Ocupación:</strong> {$area}%
					</span>
					&nbsp;&nbsp;
			{/if}
			{if ($repercussion.coste_publicitar > 0)}
				{if ($repercussion.coste_publicitar == round ($repercussion.coste_publicitar))}
					{assign var='decimals' value=0}
				{else}
					{assign var='decimals' value=2}
				{/if}
					&nbsp;&nbsp;
					<span>
						<strong>Coste Publicitario Equiv.:</strong> {$repercussion.coste_publicitar|number_format:$decimals:',':'.'} €
					</span>
					&nbsp;&nbsp;
			{/if}
			{if ($totalPages > 1)}
					&nbsp;&nbsp;
					<span>
						<strong>Página</strong> [{$page} / {$totalPages}]
					</span>
					&nbsp;&nbsp;
			{/if}
				</div>
				<div style="margin: 0 auto; width: 45px;">
					<img src="{$LOGO_URI}" alt="Logo" style="max-width: 45px;" />
				</div>
			</div>
		</div>
	</htmlpagefooter>
	<div id="repercussion-{$currentPage}" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-top: 1px solid #000000; height: 100%;">
		<div style="text-align: center; width: 100%;">
			<img src="{$attachment.url}" alt="{$repercussion.titular}" />
		</div>
	</div>
			{assign var='page' value=$page + 1}
			{assign var='currentPage' value=$currentPage + 1}
		{/foreach}
	{/foreach}
{/if}
</body>
</html>
{/strip}