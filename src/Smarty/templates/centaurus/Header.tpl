{extends file="NoHeader-NoMenu.tpl"} <!-- Hereda la estructura de la plantilla base-->

<!-- Inicio Definición de cabecera de plantilla -->
{block name="cabecera"}

  {include file="Header.navbar.inc.tpl"}

  {if $MODULE_NAME eq 'Users' && $ACTION_NAME neq 'Logout'}
    {assign var="NAV_SMALL" value=" nav-small"}
  {/if}
{/block}
<!-- Fin Definición de cabecera de plantilla -->