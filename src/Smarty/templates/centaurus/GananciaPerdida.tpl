

<div class="row">
	<div class="col-lg-12">	
		<div class="main-box">
			<div class="main-box-header clearfix">

<div class="">
<form action="index.php?module={$MODULE}&action=index" method="post" name="form" >
	<table class="searchUIBasic small"  align="center" border="0" cellpadding="5" cellspacing="0" width="100%">
	    <tbody>
	    	<tr>
	    	<td class="small" align="left" nowrap="">
				<span class="moduleName">Buscar</span>
			</td>
			<td align="right">Año &nbsp;</td>
	        <td>
	            <select class="form-control sm" name="year">
	                <option value="2015">2015</option>
	            </select>
	        </td>
	        <td align="right">Período &nbsp;</td>
	        <td>
	            <select class="form-control sm" name="period">
                {foreach from=$PERIODS item=period key=key}
		            <option value="{$key}" {if $SELECTEDPERIOD eq $key} selected {/if} >{$period}</option>
		      	{/foreach}
	            </select>
	        </td>
	        <td align="right"><input class="btn btn-primary sm" type="submit" value="Filtrar"> </td>
	        </tr>
	    </tbody>
	    
	</table><!-- /.table -->
</form>

</div>


</div>
</div>
</div>
</div>



<div class="row">
	<div class="col-lg-12">	
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h1>Estado de Ganancias y Pérdidas</h1>
			</header>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
				<table align="center" class="table ">
					<thead>
					<tr>
						<td class="tituloCuenta">1- Ventas y otros ingresos</td>
						<td></td>
					</tr>
					</thead>
					<tbody>
					{foreach from=$INGRESOS item=ingreso}
					<tr>
			            <td>   {$ingreso.cuentacontable}. {$ingreso.nombre}</td>
			            <td class="number">{$ingreso.total}</td>
			        </tr>
			      	{/foreach}
			      	</tbody>
			      	<tr>
						<td class="titulo-total">TOTAL Ventas y otros ingresos</td>
						<td class="total">{$totalIngresos}</td>
					</tr>
				</table>
				</div>


				
				<br><br>
				<div class="table-responsive">
				<table align="center" class="table ">
					<thead>
					<tr>
						<td class="tituloCuenta">2- Compras y Otros Gastos</td>
						<td></td>
					</tr>
					</thead>
					<tbody>
					{foreach from=$GASTOS item=gasto}
					<tr>
			            <td>   {$gasto.cuentacontable}. {$gasto.nombre}</td>
			            <td class="number">{$gasto.total}</td>
			        </tr>
			      	{/foreach}
			      	</tbody>
			      	<tr>
						<td class="titulo-total">TOTAL Compras y Otros Gastos</td>
						<td class="total">{$totalGastos}</td>
					</tr>		
				</table>
				</div>
			</div>
		</div>
	</div>
</div>



