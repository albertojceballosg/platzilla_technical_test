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

<!-- [TT11207] Ajustes Página Tours Platzilla - 12/07/16 - Johana Romero - Vista de los modulos paginados -->
<ul class="pagination" >    
    {section name=foo start=1 loop=$cant_divs+1 step=1}        
    {if $smarty.section.foo.index eq 1}          
        {assign var=index_limit value=1}  
    {else}        
        {assign var=index_limit value=$smarty.section.foo.index+2}          
    {/if}
    <li><a href="#" onclick="getInfo({$index_limit});"> {$smarty.section.foo.index}</a> </li>
    {/section} 
</ul>       
<div id="test">
{foreach key=keyI item=aplicacion from=$APLICACIONES}      
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
{/foreach}
</div>                                                

