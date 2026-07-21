{extends file="Header.tpl"} <!-- Hereda la estructura de la sub-plantilla -->

<!-- Inicio Definición de menú lateral de plantilla -->
{block name="menu-lateral"}
  <div id="page-wrapper" class="container{$NAV_SMALL}">
    <div class="row"> 
      <div id="nav-col">
        {* include menu navigation *}
        {include file="Header.menu.inc.tpl"}
      </div>
          
      <div id="content-wrapper">
        <!--div class="alert alert-warning" id="status" style="width: 100%;position: fixed;z-index: 9999;text-align: center;display:none;">
        <i class="fa fa-spinner fa-spin"></i> Aguarde un momento por favor... </div-->
        <div class="row">
          <div class="col-lg-12">
{/block}
<!-- Fin Definición de menú lateral de plantilla -->