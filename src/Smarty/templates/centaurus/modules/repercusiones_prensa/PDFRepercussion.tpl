<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<title>{$REPERCUSSION.cod_repercusione} - {$REPERCUSSION.titular}</title>
	<style type="text/css">
		@page {
			padding: 0;
			size:    auto;
		}
		body {
			font-family: Verdana, Geneva, sans-serif;
			font-size:   14px;
			margin:      0;
			padding:     0;
		}
		.repercussion {
			page: repercussion;
		}
	</style>
</head>
<body>
	<htmlpageheader name="repercussion_header">
		<table style="border-spacing: 0; border-collapse: separate; padding-top: 6mm; width: 100%;">
			<tbody>
			<tr>
				<td style="border: 1px solid #000000; padding: 10px; width: 120px;"><img src="{$LOGO_URI}" alt="Logo" style="max-width: 120px;" /></td>
				<td style="width: 1px;"></td>
				<td style="border: 1px solid #000000; text-align: center;">
					<h2 style="font-size: 30px; font-weight: bold;">{$REPERCUSSION.medio}</h2>
					<br />
					<p style="font-weight: bold;">{$REPERCUSSION.fecha|date_format: 'd/m/Y'}</p>
				</td>
			</tr>
		</table>
	</htmlpageheader>
{assign var='page' value=1}
{assign var='totalPages' value=count ($REPERCUSSION.attachments)}
{if (empty ($REPERCUSSION.tinyurl))}
	{assign var='tinyurl' value=null}
{else}
	{assign var='tinyurl' value=$REPERCUSSION.tinyurl}
{/if}
{foreach $REPERCUSSION.attachments as $attachment}
	<style type="text/css">
		@page repercussion-{$page} {
			/*noinspection CssUnknownProperty*/
			header: repercussion_header;
			/*noinspection CssUnknownProperty*/
			footer: repercussion_footer_{$page};
			margin: 37mm 8mm 33mm 8mm;
		}
		#repercussion-{$page} {
			page: repercussion-{$page};
		}
	</style>
	<htmlpagefooter name="repercussion_footer_{$page}">
		<div style="padding-bottom: 38px;">
			<div style="border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000;">
				<p style="text-align: center; width: 100%;">{if (!empty ($tinyurl))}<a href="{$tinyurl}">{$tinyurl}</a>{/if}</p>
				<div style="margin: 0 auto; text-align: center; width: 100%;">
	{assign var='area' value=floatval ($REPERCUSSION.superficie_)}
	{if (!empty ($area))}
					&nbsp;&nbsp;
					<span>
						<strong>Ocupación:</strong> {$area}%
					</span>
					&nbsp;&nbsp;
	{/if}
	{if ($REPERCUSSION.coste_publicitar > 0)}
		{if ($REPERCUSSION.coste_publicitar == round ($REPERCUSSION.coste_publicitar))}
			{assign var='decimals' value=0}
		{else}
			{assign var='decimals' value=2}
		{/if}
					&nbsp;&nbsp;
					<span>
						<strong>Coste Publicitario Equiv.:</strong> {$REPERCUSSION.coste_publicitar|number_format:$decimals:',':'.'} €
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
	<div id="repercussion-{$page}" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-top: 1px solid #000000; height: 100%;">
		<div style="text-align: center; width: 100%;">
			<img src="{$attachment.url}" alt="{$REPERCUSSION.titular}" />
		</div>
	</div>
	{assign var='page' value=$page + 1}
{/foreach}
</body>
</html>