{assign var='tabs' value=$OPERATING_TABS}
<section id="col-left" class="col-left-nano">
	<div id="col-left-inner" class="col-left-nano-content">
		<div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">
			<ul class="nav nav-pills nav-stacked">
				<li class="hidden-xs">
					<a href="index.php" class="dropdown-toggle" style="padding: 3px 0 48px 8px;" onclick="window.location.href='index.php';">
						<img src="/test/logo/platzi-logo-for-mobile.png" style="display: inline-block; max-width: 40px; padding: 5px; vertical-align: top;" />
						<img src="/test/logo/platzilla-logo.png" style="display: inline-block; max-width: 100px; padding: 15px 10px 10px 3px; vertical-align: top;" />
					</a>
				</li>
{if $OPERATING_MODE_BTN['hide-tab']}
    {foreach $tabs as $tab}
        {assign var='contentTab' value=$tab->getModesContent()}
        {assign var='spanClass' value='hide'}
		{if $tab->getIconPath() neq NULL}
            {assign var='icon' value=$tab->getIconPath()}
		{else}
            {assign var='icon' value='<i class="fa fa-play"></i>'}
		{/if}

		{if $MODULE_NAME eq 'Home'}
            {assign var='toggle' value='data-toggle="tab"'}
            {assign var='hrefTab' value="#"|cat:$contentTab->getName()}
		{else}
            {assign var='toggle' value=''}
            {assign var='hrefTab' value="index.php?module=Home&action=index&tab="|cat:$contentTab->getName()}
		{/if}
		<li{if ($HOME_TAB eq $contentTab->getName())} class="active"{/if}><a {$toggle}{*data-toggle="tab"*} href="{$hrefTab}" title="{$contentTab->getLabel()}"><p class="text-left;">{$icon}&nbsp;&nbsp;<small>{$contentTab->getLabel()}</small></p></a></li>
    {/foreach}
{else}
{foreach key=maintabs item=detail from=$HEADERS}
	{if !(in_array($detail.id, $HEADER_OPER_MODE))}
        {continue}
	{/if}
	{if $detail.tieneHijos neq '1'}
		{continue}
	{/if}
				<li id="li-{$detail.name}">
					<a class="dropdown-toggle" href="#" {if $detail.color}style="background:{$detail.color}"{/if}>
						<i class="{*fa*} {$detail.iconclass}"></i>
						<span>{$detail.name}  </span>
	{if $detail.tieneHijos eq '1'}
						<i class="fa fa-chevron-circle-right drop-icon"></i>
	{/if}
					</a>
	{if $detail.tieneHijos eq '1'}
					<ul class="submenu">
		{foreach key=maintabsL2 item=detailL2 from=$detail.elementos} {* INICIANDO NIVEL 2 *}
		{if $detailL2.name eq NULL}{continue}{/if}
						<li>
			{if $detailL2.tieneHijos eq '1' && $detailL2.elementos neq ''}
							<a class="dropdown-toggle" href="#"{if $detailL2.habilitado eq '0'} onclick="window.location='{$PREFIJO_URL}module-store-action-landpage-app-{$detailL2.name}'"{else} {if $detailL2.tieneHijos eq '0'} onclick="window.location='{$PREFIJO_URL}module-{$detailL2.name}-action-index'"{/if} {/if}>
				{if $detailL2.habilitado eq '0'}
					{if $BRIEFING neq 'true'}
								<i class="fa fa-lock" style="margin-right:5px;"></i>
					{/if}
				{/if}
								<span>{if $detailL2.label eq ''} {$detailL2.name}  {else}  {$detailL2.label}  {/if}</span>
				{if $detailL2.habilitado neq '0' && $detailL2.tieneHijos eq '1'}
								<i class="fa fa-chevron-circle-right drop-icon"></i>
				{/if}
							</a>
			{else}
				{if $detailL2.tieneHijos eq '0' && $detailL2.habilitado neq '0' && $detailL2.moduleaction neq ''}
					{if $detailL2.esmodulodecampos neq 'null' && false }
							<div class="crearregistro-menu" style="">
							</div>
					{/if}
							<a class="a-menu" style="width: 100%" href="index.php?module={$detailL2.name}&action=index">
					{if $detailL2.count_registro neq 'null'}
								<div class="contadorregistros-menu" style="background-color:{$detailL2.color};"> {$detailL2.count_registro} </div>
					{/if}
								<span class="nombremodulo-menu" style="">{if $detailL2.label eq ''} {$detailL2.name} {else}  {$detailL2.label} {/if}</span> {* NOMBRE NIVEL 2 *}
							</a>
				{/if}
			{/if}
						</li>
		{/foreach}
					</ul>
	{/if}
				</li>
{/foreach}
{/if}

			</ul>
		</div>
	</div>
	<div class="col-left-nano-content hidden-xs hidden-sm" style="bottom: 0; left: 0; position: absolute; right: 0;">
		<div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav">
			<ul class="nav nav-pills nav-stacked">
				<li>
					<a class="dropdown-toggle center-block" href="#" style="padding: 3px 0 48px 8px;" >
                        <img src="/themes/images/operating-mode.png" style="display: inline-block; max-width: 40px; padding: 5px; vertical-align: center; margin-left: 5px"/>
						{*<i class="fa fa-power-off"></i>*}
						<span style="vertical-align: bottom;margin: 0 12px;">{$DEFAULT_OPERATING}</span>
							<i class="fa fa-chevron-circle-right drop-icon"></i>
					</a>
					<!-- {$OPERATING_MODE_BTN['btn-class']} -->
					<ul class="submenu" role="menu">
                        {foreach $AVAIABLE_OPERATING_MODES as $operatingMode}
							<li><a rel="{$operatingMode->getOperatingModeName()}@{$MODULE_NAME}" {if $operatingMode->getLabel() neq $DEFAULT_OPERATING}
									href="#"
									id="platzilla_operating_mode"
								   onclick="OperatingModesUtils.changeOperating(this, event)"{/if}>
                                    {if $operatingMode->getLabel() eq $DEFAULT_OPERATING}
										<strong>{$operatingMode->getLabel()}</strong>
                                    {else}
                                        {$operatingMode->getLabel()}
                                    {/if}
								</a></li>
                        {/foreach}
					</ul>
				</li>
				<li>
					<a href="index.php?module=Users&amp;action=Logout" onclick="return confirm ('¿Estás seguro que quieres cerrar la sesión?');"><i class="fa fa-power-off"></i><span>Salir</span></a>
				</li>
			</ul>
		</div>
	</div>
</section>