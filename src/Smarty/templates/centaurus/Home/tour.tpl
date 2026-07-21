{*
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/ *}

<!-- [TT11207] Ajustes Página Tours Platzilla - 08/07/16 - Johana Romero Página que muestra la vista del tour -->

<link rel="stylesheet" href="themes/centaurus/css/libs/timeline.css">
<link rel="stylesheet" href="themes/centaurus/css/compiled/platzilla-icons.css">
<link rel="stylesheet" href="themes/centaurus/css/compiled/custom.css">

<script src="modules/Home/Homestuff.js"></script>

<div class="container" >	
	<div class="row">
		<div class="col-lg-7">
			<h1>Tour</h1>
			<div class="main-box infographic-box pz-message">
				<span class="glyphicon glyphicon-remove close"></span>
				<i class="fa">
				  <img src="storage/logos/platzi-head.png" alt="">
				</i>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Iste alias doloremque eos quam, voluptatum non aut culpa!</p>
			</div>
		</div>

		<div class="col-lg-4 col-lg-offset-1">
			<div class="main-box infographic-box">
				<div class="management-progress">
					<div class="rocket">
						<img src="storage/logos/rocket-5.png" height="98px" alt="">
					</div>
					<div class="info">
					<h3>Control de gestión alcanzado</h3>
					<strong>53%</strong>
					</div>
				</div>
			</div>
		</div>
	</div>

	{*
	{if $IS_ADMIN}
		<div class="row">
			<div class="col-lg-12">
			  <a href="index.php?module=Settings&action=CreateApp" class="btn btn-success btn-add-app pull-right">Agregar Aplicación</a>
			</div>
		</div>
	{/if}
	*}
	<div class="row">
		<div class="col-lg-12">
		  <a href="#" class="btn btn-success btn-add-app pull-right">Agregar Aplicación</a>
		</div>
	</div>
								
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<div class="tabs-wrapper tabs-no-header">
					<ul class="nav nav-tabs">
					{foreach key=keyA item=aplicacion from=$APLICACIONES name=blk}   						
						{assign var=count value=$smarty.foreach.blk.iteration}
						<li id="{$aplicacion.appid}" class="{if $count eq '1'}active{/if}">
							<a href="#tab-{$aplicacion.app_code}" data-toggle="tab" onclick="getInfo({$count})">{$aplicacion.app_name}</a>
						</li>
						<!-- <span class="label">Nuevo</span>-->
					{/foreach}						
					</ul>
					<div class="tab-content tab-content-body">
						{foreach key=keyA item=aplicacion from=$APLICACIONES name=blk} 
						{assign var=count value=$smarty.foreach.blk.iteration}  	
						{assign var=count_m value=0}
						{assign var=cant_divs value=0}   
						{assign var=last_key value=0}
						{foreach key=keyM item=modulo from=$MODULOS}        
						    {if $modulo.config_applicationsid eq $aplicacion.appid}        
						        {assign var=count_m value=$count_m+1}         
						    {else}
						    	{assign var=last_key value=$keyM}
						    {/if}								

						{/foreach}
						
						<div class="tab-pane fade {if $count eq '1'}in active{/if}" id="tab-{$aplicacion.app_code}">
							<div class="container-fluid app-tour-panel">	
								<!--<a href="#" class="btn-next">Siguiente <i class="fa fa-arrow-right"></i></a>-->
								
								<div class="row" >
									<div class="col-md-offset-1 col-md-10">
									    <div class="row">
									    	<div class="app-detail">
										        <img src="storage/appsimages/crm-funnel.png" alt="">
										        <p>Haz seguimiento de potenciales clientes y negociaciones. CRM-Fácil te ayuda a resolver los atascos.</p>
										    </div>											    
											{if $count_m <= 4}    
											    {foreach key=keyM item=modulo from=$MODULOS}       
											        {if $modulo.config_applicationsid eq $aplicacion.appid}                          
											            <div class="steps-wrapper" >
											                <div class="row">
											                    <div class="col-lg-12">
											                        <div class="main-box infographic-box">
											                            <div class="row">
											                                <div class="col-md-7">
											                                    <i class=" fa fa-users"></i>
											                                    <div class="step-detail">
											                                        <h4>{$modulo.tablabel}</h4>
											                                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatem eveniet pariatur hic quam optio accusamus illo, numquam perferendis, repellat accusantium deserunt, expedita, aliquam natus dolorum. Ducimus cumque impedit itaque at!</p>
											                                    </div>
											                                </div>
											                                <div class="col-md-3">
											                                    <a class="action-button clearfix" href="index.php?module={$modulo.name}&action=EditView&return_action=DetailView&parenttab=">
											                                        <i class="fa fa-plus"></i>
											                                        <br/>
											                                        <strong>
											                                          Crear
											                                        </strong>
											                                    </a>
											                                </div>
											                            </div>
											                        </div>
											                    </div>
											                </div>                                          
											            </div>
											        {/if}                                           
											    {/foreach}
											{else}														
												<script type="text/javascript"> 
												  getInfo({$last_key}+1);
												  
												</script>  		
												
												{if $count_m mod 4 == 0}
													{assign var=cant_divs value=1}
												{else}										
													{math assign=cant_divs equation="(x % y) + 1" x=$count_m y=4}
												{/if}								
											    {include file="Home/modulos.tpl"}	
											{/if}										 
										</div>
									</div>
								</div>								
							</div>						
						</div>						
						{/foreach}		
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
