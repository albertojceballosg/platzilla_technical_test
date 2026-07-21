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
<div id="div_indicadores">
  {foreach key=keyI item=aplicacion from=$APLICACIONES}      
  <div class="col-lg-11">    
      <div class="pz-section-title {$COLORES.$keyI}">
        {$aplicacion.app_name}
      </div>
    
  </div>

  <div class="col-lg-11">
    <div class="row">
      {foreach key=keyM item=modulo from=$MODULOS}
        {if $aplicacion.appid eq $modulo.config_applicationsid}
          <div class="col-lg-3">
            <div class="main-box infographic-box">
              <i class="fa fa-user {$COLORES.$keyM}"></i>
              <span class="value">{$modulo.cantidad}</span>
              <span class="headline">{$modulo.tablabel}</span>            
            </div>
          </div>
        {/if}
      {/foreach}
    </div>
  </div>   
  {/foreach}  
</div>
