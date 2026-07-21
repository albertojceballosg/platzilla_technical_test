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



							{if $detailL2.habilitado neq '0'}
								<ul class="submenu" style="list-style-type:none !important;">
								{foreach key=maintabsL3 item=detailL3 from=$detailL2.elementos} {* INICIANDO NIVEL 3 *}
									<li>
									{if $detailL3.name eq 'Reports1'} {* desglose de menu comercial *}
										{*<a class="dropdown-toggle" href="{if $detailL3.tieneHijos eq '1' || $detailL3.habilitado eq '0'}#{else}{$PREFIJO_URL}index.php?module=Reports&action={$detailL3.moduleaction}&parenttab={$detailL4.parentabName}&type=comercial{/if}" {if $detailL3.tieneHijos eq '0' && $detailL3.habilitado neq '0'} onclick="window.location='{$PREFIJO_URL}index.php?module=Reports&action={$detailL3.moduleaction}&parenttab={$detailL4.parentabName}&type=comercial'" {/if}>*}
										<a class="dropdown-toggle" href="{if $detailL3.tieneHijos eq '1' || $detailL3.habilitado eq '0'}#{else}{$PREFIJO_URL}module-Reports-action-{$detailL3.moduleaction}-parenttab-{$detailL4.parentabName}-type-comercial{/if}" {if $detailL3.tieneHijos eq '0' && $detailL3.habilitado neq '0'} onclick="window.location='{$PREFIJO_URL}module-Reports-action-{$detailL3.moduleaction}-parenttab-{$detailL4.parentabName}-type-comercial'" {/if}>
											{if $detailL3.habilitado eq '0'}<i class="fa fa-lock" style="margin-right:5px;"></i>{else}<i class="fa fa-circle" style="font-size:0.3em;margin-right:5px;"></i>{/if}<span>{if $detailL3.tieneHijos eq '1'} {$detailL3.name} ({$detailL3.count_registro}) {else} {$detailL3.label}  ({$detailL3.count_registro}){/if} </span> {* NOMBRE NIVEL 3 *}
											{if $detailL3.tieneHijos eq '1'}<i class="fa fa-chevron-circle-right drop-icon"></i>{/if}
										</a>
									{/if}
									{if $detailL3.name eq 'Reports2'} {* desglose de menu postventa *}
										{*<a class="dropdown-toggle" href="{if $detailL3.tieneHijos eq '1' || $detailL3.habilitado eq '0'}#{else}{$PREFIJO_URL}index.php?module=Reports&action={$detailL3.moduleaction}&parenttab={$detailL4.parentabName}&type=postventa{/if}" {if $detailL3.tieneHijos eq '0' && $detailL3.habilitado neq '0'} onclick="window.location='{$PREFIJO_URL}index.php?module=Reports&action={$detailL3.moduleaction}&parenttab={$detailL4.parentabName}&type=postventa'" {/if}>*}
										<a class="dropdown-toggle" href="{if $detailL3.tieneHijos eq '1' || $detailL3.habilitado eq '0'}#{else}{$PREFIJO_URL}module-Reports-action-{$detailL3.moduleaction}-parenttab-{$detailL4.parentabName}-type=postventa{/if}" {if $detailL3.tieneHijos eq '0' && $detailL3.habilitado neq '0'} onclick="window.location='{$PREFIJO_URL}module-Reports-action-{$detailL3.moduleaction}-parenttab-{$detailL4.parentabName}-type-postventa'" {/if}>
											{if $detailL3.habilitado eq '0'}<i class="fa fa-lock" style="margin-right:5px;"></i>{else}<i class="fa fa-circle" style="font-size:0.3em;margin-right:5px;"></i>{/if}<span>{if $detailL3.tieneHijos eq '1'} {$detailL3.name} ({$detailL3.count_registro}) {else} {$detailL3.label}  ({$detailL3.count_registro}){/if} </span> {* NOMBRE NIVEL 3 *}
											{if $detailL3.tieneHijos eq '1'}<i class="fa fa-chevron-circle-right drop-icon"></i>{/if}
										</a>
									{/if}
									{if $detailL3.name neq 'Reports1' && $detailL3.name neq 'Reports2'} {* desglose de menu general *}
										{*<a class="dropdown-toggle" href="{if $detailL3.tieneHijos eq '1' || $detailL3.habilitado eq '0'}#{else}index.php?module={$detailL3.name}&action={$detailL3.moduleaction}&parenttab={$detailL4.parentabName}{/if}" {if $detailL3.tieneHijos eq '0' && $detailL3.habilitado neq '0'} onclick="window.location='{$PREFIJO_URL}index.php?module={$detailL3.name}&action={$detailL3.moduleaction}&parenttab={$detailL4.parentabName}'" {/if}>*}
										<a class="dropdown-toggle" href="{if $detailL3.tieneHijos eq '1' || $detailL3.habilitado eq '0'}#{else}index.php?module-{$detailL3.name}-action-{$detailL3.moduleaction}-parenttab-{$detailL4.parentabName}{/if}" {if $detailL3.tieneHijos eq '0' && $detailL3.habilitado neq '0'} onclick="window.location='{$PREFIJO_URL}module-{$detailL3.name}-action-{$detailL3.moduleaction}-parenttab-{$detailL4.parentabName}'" {/if}>
											{if $detailL3.habilitado eq '0'}<i class="fa fa-lock" style="margin-right:5px;"></i>{else}<i class="fa fa-circle" style="font-size:0.3em;margin-right:5px;"></i>{/if}<span>{if $detailL3.tieneHijos eq '1'} {$detailL3.name} ({$detailL3.count_registro}) {else} {$detailL3.label}  ({$detailL3.count_registro}){/if} </span> {* NOMBRE NIVEL 3 *}
											{if $detailL3.tieneHijos eq '1'}<i class="fa fa-chevron-circle-right drop-icon" style="position:relative;horizontal-align: right;margin-left:75px;margin-right:0px;"></i>{/if}
										</a>

									{/if}

										{* INICIANDO NIVEL 4 *}
										{if $detailL3.tieneHijos eq '1'}
										<ul class="submenu nav-pills nav-stacked" style="position:relative; padding-left: 30px; list-style-type:none !important;">
											{foreach key=maintabsL4 item=detailL4 from=$detailL3.elementos} {* INICIANDO NIVEL 4 *}
											<li>
												{*<a href="{$PREFIJO_URL}index.php?module={$detailL4.name}&action={$detailL4.moduleaction}&parenttab={$detailL4.parentabName}">*}
												<a href="{$PREFIJO_URL}module-{$detailL4.name}-action-{$detailL4.moduleaction}-parenttab-{$detailL4.parentabName}">
													{if $detailL4.habilitado eq '0'}<i class="fa fa-lock" style="margin-right:5px;"></i>{else}<i class="fa fa-circle" style="font-size:0.3em;margin-right:5px;"></i>{/if}
													<span> {$detailL4.label}({$detailL4.count_registro}) </span> {* NOMBRE NIVEL 4 *}
												</a>
											</li>

											{/foreach}  {* FIN NIVEL 4 *}
										</ul>
										{/if}


										{* FIN NIVEL 3 *}
									</li>
								{/foreach}  {* FIN NIVEL 3 *}
								</ul>


									{else}
										{if $detailL2.tieneHijos eq '0' && $detailL2.habilitado neq '0' && $detailL2.moduleaction neq ''}

											<a class="dropdown-toggle" href="#" {if $detailL2.habilitado eq '0'}onclick="window.location='index.php?module=store&action=index&app={$detailL2.name}'"{else} {if $detailL2.tieneHijos eq '0'} onclick="window.location='index.php?module={$detailL2.name}&action=index'"{/if} {/if}>
												{if $detailL2.habilitado eq '0'}<i class="fa fa-lock" style="margin-right:5px;"></i>{/if}
												<span >{if $detailL2.label eq ''} {$detailL2.name}  {else}  {$detailL2.label}  {/if}</span> {* NOMBRE NIVEL 2 *}
												{if $detailL2.habilitado neq '0' && $detailL2.tieneHijos eq '1'}
												<i class="fa fa-chevron-circle-right drop-icon"></i>
												{/if}
											</a>
										{/if}
								{/if}   <!-- -->

							{else}
									{if $detailL2.tieneHijos eq '0' && $detailL2.habilitado neq '0' && $detailL2.moduleaction neq ''}


											<a  href="#" {if $detailL2.habilitado eq '0'}onclick="window.location='index.php?module=store&action=index&app={$detailL2.name}'"{else} {if $detailL2.tieneHijos eq '0'} onclick="window.location='index.php?module={$detailL2.name}&action=index'"{/if} {/if}>
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