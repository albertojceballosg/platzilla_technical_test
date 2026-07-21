{strip}
<style>
	.cortar {
		width:         200px;
		white-space:   nowrap;
		overflow:      hidden;
		text-overflow: ellipsis;

	}
	.cortar:hover {
		width:       auto;
		white-space: initial;
		overflow:    visible;
		cursor:      pointer;
	}
</style>
<div class="row">
	<div class="col-xs-8">
		<h1><a href="index.php?module=matriz_de_certifica_cpu&action=index">Matriz de Certificación Curso/Prueba/Usuario</a></h1>
	</div>
	<div class="col-xs-4" style="text-align: right; text-decoration: none">
		<a href="index.php?module=matriz_de_certificacion&action=index" class="btn btn-success pull-right" style="margin-left:.5em; margin-right: 0;">Matriz de Certificación</a>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h2 class="pull-left">Certificación Cursos/Prueba/Usuarios</h2>
			</header>
			<div class="main-box-body clearfix">
				<table class="table">
					<thead>
					<tr>
						<th></th>
{foreach key=key item=us from=$USER}
						<th>{$us.nombres}</th>
{/foreach}
					</tr>
					</thead>
					<tbody>
{foreach key=key item=ma from=$MATRIZ}
					<tr>
						<td>{$key}</td>
	{foreach $ma as $m}
		{if $m eq 'null'}
						<td></td>
		{else}
						<td style="text-align: left">
			{foreach $m as $p}
							<p class="cortar">
								{$p['estado']|replace:"Aprobado":"<i class='fa fa-thumbs-o-up'></i>"|replace:"Aplazado":"<i class='fa fa-thumbs-o-down'></i>"}
								{$p['puntaje_total']}
								{$p['tituloprueba']}
							</p>
			{/foreach}
								</td>
		{/if}
	{/foreach}
					</tr>
{/foreach}
					</tbody>
				</table>
				<p>Aprobado => <i class='fa fa-thumbs-o-up'></i></p>
				<p>No Aprobo => <i class='fa fa-thumbs-o-down'></i></p>
			</div>
		</div>
	</div>
</div>
{/strip}