{strip}
<div class="row">
	<div class="col-xs-8">
		<h1><a href="index.php?module=matriz_de_certificacion&action=index">Matriz de Certificación</a></h1>
	</div>
	<div class="col-xs-4" style="text-align: right; text-decoration: none">
		<a href="index.php?module=matriz_de_certificacion&action=DetallesMatriz" class="btn btn-success pull-right" style="margin-left:.5em; margin-right: 0;">Detalles</a>
		<a href="index.php?module=matriz_de_certificacion&action=EditView" class="btn btn-warning pull-right" style="margin-left:.5em; margin-right: 0;"><span class="fa fa-pencil"></span> Editar</a>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h2 class="pull-left">Certificación Cursos/Usuarios</h2>
			</header>
			<div class="main-box-body clearfix">
				<table class="table">
					<thead>
					<tr>
						<th></th>
{foreach key=key item=u from=$USR }
						<th>{{$u.nombres}}</th>
{/foreach}
					</tr>
					</thead>
					<tbody>
{$i=0}
{foreach key=key item=ma from=$MATRIZ}
					<tr>
						<td><b>{{$TIT[$i].titulo}}</b></td>
	{$i=$i+1}
	{foreach $ma as $m}
		{if $m eq 0}
						<td></td>
		{else}
						<td style="text-align: center"><i class="glyphicon glyphicon-ok"></i></td>
		{/if}
	{/foreach}
					</tr>
{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
{/strip}