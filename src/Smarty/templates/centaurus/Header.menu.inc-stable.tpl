{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * Edited by Timemanagement.
   * Developer  MA 2015.06.26 | EV - 2015.05.26
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<section id="col-left" class="col-left-nano">
	<div id="col-left-inner" class="col-left-nano-content">
		{* no me gusta esto de mostrar el usurio
		<div id="user-left-box" class="clearfix hidden-sm hidden-xs">
			<img alt="" src="themes/centaurus/img/samples/avatar.png" />
			<div class="user-box">
				<span class="name" style="  font-size: 1em;">
					Bienvenido<br/>
					{$USER}
				</span>
				<span class="status">
					<i class="fa fa-circle"></i> Online
				</span>
			</div>
		</div>
		*}
		<div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">
			<ul class="nav nav-pills nav-stacked">


				{foreach key=maintabs item=detail from=$HEADERS}
				<li id="li-{$detail.name}">
					<a class="dropdown-toggle" href="#" {if $detail.color}style="background:{$detail.color}"{/if}>
						<i class="fa {$detail.iconclass}"></i>
						<span>{$detail.name}  </span>
						{if $detail.tieneHijos eq '1'}<i class="fa fa-chevron-circle-right drop-icon"></i>{/if}
					</a>
					{if $detail.tieneHijos eq '1'}
					<ul class="submenu">

					{foreach key=maintabsL2 item=detailL2 from=$detail.elementos} {* INICIANDO NIVEL 2 *}
						<li>

							{if $detailL2.tieneHijos eq '1' && $detailL2.elementos neq ''}
								<a class="dropdown-toggle" href="#"
									{if $detailL2.habilitado eq '0'}
										onclick="window.location='{$PREFIJO_URL}module-store-action-landpage-app-{$detailL2.name}'"
										{else} {if $detailL2.tieneHijos eq '0'} onclick="window.location='{$PREFIJO_URL}module-{$detailL2.name}-action-index'"{/if} {/if}>
								{if $detailL2.habilitado eq '0'}{if $BRIEFING neq 'true'}<i class="fa fa-lock" style="margin-right:5px;"></i>{/if}{/if}
								<span >{if $detailL2.label eq ''} {$detailL2.name}  {else}  {$detailL2.label}  {/if}</span> {* NOMBRE NIVEL 2 *}
								{if $detailL2.habilitado neq '0' && $detailL2.tieneHijos eq '1'}
								<i class="fa fa-chevron-circle-right drop-icon"></i>
								{/if}
							</a>





							{else}
									{if $detailL2.tieneHijos eq '0' && $detailL2.habilitado neq '0' && $detailL2.moduleaction neq ''}


											<a class="dropdown-toggle"  href="#" {if $detailL2.habilitado eq '0'}onclick="window.location='index.php?module=store&action=index&app={$detailL2.name}'"{else} {if $detailL2.tieneHijos eq '0'} onclick="window.location='index.php?module={$detailL2.name}&action=index'"{/if} {/if}>
												{if $detailL2.habilitado eq '0'}<i class="fa fa-lock" style="margin-right:5px;"></i>{/if}

												{if $detailL2.count_registro neq 'null'}

												<div style="float:right;border-radius: 50%; width: 18px;height:18px;margin:10px 10px 0 0 !important;text-align:center;display:inline-block;line-height:20px;font-size:9px"> <i class="fa fa-plus"></i>  </div>

												<div style="float:left;background-color:{$detailL2.color};border-radius: 50%; width: 18px;height:18px;margin:10px 10px 0 0 !important;text-align:center;display:inline-block;line-height:20px;font-size:9px"> {$detailL2.count_registro} </div>
												{/if}

												<span style="padding-left:10px;margin-left: -10px;" >{if $detailL2.label eq ''} {$detailL2.name} {else}  {$detailL2.label} {/if}</span>   {* NOMBRE NIVEL 2 *}


											</a>




										{/if}

							{/if}

						</li>
					{/foreach}  {* FIN NIVEL 2 *}
					</ul>
					{/if}
				</li>
				{/foreach} {* FIN NIVEL 1 *}




			</ul>
		</div>
	</div>

</section>