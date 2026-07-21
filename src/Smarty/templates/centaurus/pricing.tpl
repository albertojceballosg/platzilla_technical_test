<form name="payment" method="POST" action="index.php" id="payment">
	<input type="hidden" name="action" value="payment"/>
	<input type="hidden" name="module" value="Users"/>
	<input type="hidden" name="amount" id="amount" value=""/>
	<div class="col-lg-12" style="width:100%">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2>En {$APPNAME}, hay un plan para cualquier tamaño de empresa</h2>
			</header>
			
			<div class="main-box-body clearfix">
				<div class="col-md-3 col-sm-6 col-xs-12 pricing-package simple">
					<div class="pricing-package-inner">
						<div class="package-header">
							<span class="stars center-block">
								<i class="fa fa-star"></i>
							</span>
							<h3>Básico</h3>
						</div>
						<div class="package-content" style="min-height:500px">
							<div class="package-price">
								12€
							</div>
							<div style="font-size:85%">
							<div style="text-align:center">/usuario/mes<br/>(pagado anualmente)</div>
							<div style="text-align:center">15€/usuario/mes (facturado mensualmente)</div>
							</div>
							<ul class="package-top-features">
								<li class="has-feature">Administración de eventos.</li>
								<li class="has-feature">Administración de tareas.</li>
								<li class="has-feature">Administración de contactos, prospectos y oportunidades.</li>
								<li class="has-feature">Administración de proyectos.</li>
								<li class="has-feature">Facturación y postventa.</li>
							</ul>
							<ul class="package-features">
								<li class="has-feature">Envío de emails masivos (500 por día)</li>
								<li class="has-feature">25.000 registros</li>
								<li class="has-feature">1 GB de almacenamiento</li>
								<li class="has-feature">Límite de 5.000 filas para importación de archivos</li>
								<li class="has-feature">Soporte por email</li>
							</ul>
							<br/>
						</div>
					</div>
					{if $BRIEFING eq ''}
					<div class="package-footer">
						<button class="btn btn-success" onclick="jQuery('#amount').val(1);jQuery('#payment').submit();">
							<span class="fa fa-shopping-cart fa-lg"></span>Seleccionar<br/>plan
						</button>
					</div>
					<div class="package-footer">
						<button class="btn btn-warning" onclick="jQuery('#amount').val(2);jQuery('#payment').submit();">
							<span class="fa fa-shopping-cart fa-lg"></span>Seleccionar<br/>plan mensual
						</button>
					</div>
					{/if}
				</div>
				
				<div class="col-md-3 col-sm-6 col-xs-12 pricing-package simple">
					<div class="pricing-package-inner">
						<div class="package-header green-bg">
							<span class="stars center-block">
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
							</span>
							<h3>Plus</h3>
						</div>
						<div class="package-content" style="min-height:500px">
							<div class="package-price">
								29€<span class="package-month">/usuario/mes</span>
							</div>
							<div style="font-size:85%">
							<div style="text-align:center">/usuario/mes<br/>(pagado anualmente)</div>
							<div style="text-align:center">35€/usuario/mes (facturado mensualmente)</div>
							</div>
							<ul class="package-top-features">
								<li class="has-feature">Administración de eventos.</li>
								<li class="has-feature">Administración de tareas.</li>
								<li class="has-feature">Administración de contactos, prospectos y oportunidades.</li>
								<li class="has-feature">Administración de proyectos.</li>
								<li class="has-feature">Facturación y postventa.</li>
							</ul>
							<ul class="package-features">
								<li class="has-feature">Envío de emails masivos (2.500 por día)</li>
								<li class="has-feature">100.000 registros</li>
								<li class="has-feature">10 GB de almacenamiento</li>
								<li class="has-feature">Límite de 25.000 filas para importación de archivos</li>
								<li class="has-feature">Soporte por email prioritario</li>
							</ul>
							<br/>
						</div>
					</div>
					{if $BRIEFING eq ''}
 					<div class="package-footer">
						<button class="btn btn-success" onclick="jQuery('#amount').val(3);jQuery('#payment').submit();">
							<span class="fa fa-shopping-cart fa-lg"></span>Seleccionar<br/>plan
						</button>
					</div>
					<div class="package-footer">
						<button class="btn btn-warning" onclick="jQuery('#amount').val(4);jQuery('#payment').submit();">
							<span class="fa fa-shopping-cart fa-lg"></span>Seleccionar<br/>plan mensual
						</button>
					</div>
					{/if}
				</div>
				
				<div class="col-md-3 col-sm-6 col-xs-12 pricing-package simple">
					<div class="pricing-package-inner">
						<div class="pricing-star">Más<br>popular</div>
						<div class="package-header yellow-bg">
							<span class="stars center-block">
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
							</span>
							<h3>Profesional</h3>
						</div>
						<div class="package-content" style="min-height:500px">
							<div class="package-price">
								49€<span class="package-month">/usuario/mes</span>
							</div>
							<div style="font-size:85%">
							<div style="text-align:center">/usuario/mes<br/>(pagado anualmente)</div>
							<div style="text-align:center">55€/usuario/mes (facturado mensualmente)</div>
							</div>
							<ul class="package-top-features">
								<li class="has-feature">Administración de eventos.</li>
								<li class="has-feature">Administración de tareas.</li>
								<li class="has-feature">Administración de contactos, prospectos y oportunidades.</li>
								<li class="has-feature">Administración de proyectos.</li>
								<li class="has-feature">Facturación y postventa.</li>
							</ul>
							<ul class="package-features">
								<li class="has-feature">Envío de emails masivos (5.000 por día)</li>
								<li class="has-feature">250.000 registros</li>
								<li class="has-feature">100 GB de almacenamiento</li>
								<li class="has-feature">Límite de 50.000 filas para importación de archivos</li>
								<li class="has-feature">Soporte por email prioritario</li>
							</ul>
						</div>
					</div>
					{if $BRIEFING eq ''}
					<div class="package-footer">
						<button class="btn btn-success" onclick="jQuery('#amount').val(5);jQuery('#payment').submit();">
							<span class="fa fa-shopping-cart fa-lg"></span>Seleccionar<br/>plan
						</button>
					</div>
					<div class="package-footer">
						<button class="btn btn-warning" onclick="jQuery('#amount').val(6);jQuery('#payment').submit();">
							<span class="fa fa-shopping-cart fa-lg"></span>Seleccionar<br/>plan mensual
						</button>
					</div>
					{/if}
				</div>
				
				<div class="col-md-3 col-sm-6 col-xs-12 pricing-package simple">
					<div class="pricing-package-inner">
						<div class="package-header purple-bg">
							<span class="stars center-block">
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
								<i class="fa fa-star"></i>
							</span>
							<h3>Premium</h3>
						</div>
						<div class="package-content" style="min-height:500px">
							<div class="package-price">
								99€<span class="package-month">/usuario/mes</span>
							</div>
							<div style="font-size:85%">
							<div style="text-align:center">/usuario/mes<br/>(pagado anualmente)</div>
							<div style="text-align:center">129€/usuario/mes (facturado mensualmente)</div>
							</div>
							<ul class="package-top-features">
								<li class="has-feature">Administración de eventos.</li>
								<li class="has-feature">Administración de tareas.</li>
								<li class="has-feature">Administración de contactos, prospectos y oportunidades.</li>
								<li class="has-feature">Administración de proyectos.</li>
								<li class="has-feature">Facturación y postventa.</li>
							</ul>
							<ul class="package-features">
								<li class="has-feature">Envío de emails masivos (10.000 por día)</li>
								<li class="has-feature">Registros ilimitados</li>
								<li class="has-feature">250 GB de almacenamiento</li>
								<li class="has-feature">Límite de 50.000 filas para importación de archivos</li>
								<li class="has-feature">Soporte por email prioritario</li>
							</ul>
						</div>
					</div>
					{if $BRIEFING eq ''}
					<div class="package-footer">
						<button class="btn btn-success" onclick="jQuery('#amount').val(7);jQuery('#payment').submit();">
							<span class="fa fa-shopping-cart fa-lg"></span>Seleccionar<br/>plan
						</button>
					</div>
					<div class="package-footer">
						<button class="btn btn-warning" onclick="jQuery('#amount').val(8);jQuery('#payment').submit();">
							<span class="fa fa-shopping-cart fa-lg"></span>Seleccionar<br/>plan mensual
						</button>
					</div>
					{/if}
				</div>
				<!--
				<div class="col-md-2 col-sm-4 col-xs-12 pricing-package simple" style="width:20%">
					<div class="pricing-package-inner">
						<div class="package-header red-bg">
							<span class="stars center-block">&nbsp;
							</span>
							<h3>Gratis</h3>
						</div>
						<div class="package-content" style="min-height:750px">
							<div class="package-price">
								<span class="package-month">Para siempre</span>
							</div>
							<div style="font-size:85%">
							<div style="text-align:center">(hasta 2 usuarios)</div>
							<div style="text-align:center">&nbsp;<br/><br/><br/></div>
							</div>
							<ul class="package-top-features">
								<li class="has-feature">Administración de eventos.</li>
								<li class="has-feature">Administración de tareas.</li>
								<li class="has-feature">Administración de contactos, prospectos y oportunidades.</li>
								<li class="has-feature"><br/><br/></li>
								<li class="has-feature"><br/></li>
							</ul>
							<ul class="package-features">
								<li class="has-feature">Envío de emails masivos (10 por día)</li>
								<li class="has-feature">1000 registros</li>
								<li class="has-feature">200 MB de almacenamiento</li>
								<li class="has-feature">Comunidad de soporte</li>
							</ul>
						</div>
					</div>
					{if $BRIEFING eq ''}
					<div class="package-footer">
						<button class="btn btn-success">
							<span class="fa fa-shopping-cart fa-lg"></span>Seleccionar<br/>plan
						</button>
					</div>
					{/if}
				</div>
				-->
			</div>
			
		</div>
	</div>
</form>
{if $BRIEFING eq ''}
{else}
	<div style="text-align:center">
	<a href="./module-Users-action-signin">
	<button class="btn btn-success btn-lg">
		<span class="fa fa-shopping-cart fa-lg"></span> Haz una prueba por 15 días
	</button>
	</a>
	</div>
	<div class="pricing-package">
		<h4></h4>
		<div class="alert alert-info" style="text-align:center;font-size:120%">
			<i class="fa fa-info-circle fa-fw fa-lg"></i>
			<strong>¡Tranquilo!</strong>, no te pediremos datos de tu tarjeta de crédito.
		</div>
			
		<h3>¿Qué incluye?</h3>
		<ul class="package-top-features">
			<li class="has-feature">Organización de todos los contactos que necesites.</li>
			<li>Informes sobre cómo avanza tu empresa o negocio.</li>
			<li>Red social corporativa, que apoya el flujo de trabajo.</li>
			<li>Sin límites en las negociaciones.</li>
			<li>Asignación de tareas a otros usuarios y seguimiento, a través de un calendario.</li>
			<li>Configuración y categorización de las tareas, según el proceso de venta de tu empresa. Por ejemplo: presentaciones, llamadas, emails.</li>
			<li>Gestión de campañas comerciales.</li>
			<li>Facilidades para configurar campos de información en cada una de tus listas de seguimiento.</li>
			<li>Gestión de tickets de postventa.</li>
			<li>Correo electrónico.</li>
			<li>Repositorio de documentos, contratos, órdenes de venta, y mucho más.</li>
			<li>Resúmenes mensuales, trimestrales y anuales.</li>
			<li>Control de vencimientos: qué órdenes de compra y facturas han vencido y no han sido pagadas/cobradas.</li>
			<li>Backup automatizado de toda la información diariamente.</li>
			<li>Acceso web a la herramienta las 24 horas del día.</li>
			<li>Facilidades para importar y exportar informes.</li>
			<li>Creación de grupos para gestionar acciones comerciales o de postventa por equipos o departamentos.</li>
		</ul>
	</div>

{/if}