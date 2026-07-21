{strip}
        <td class="td-summary-action" valign="top" style="padding: 0px">
            <table class="table table-condensed" width="85%">
               <tr>
                   <td align="center" width="10%">
                       <label>
                           <input type="checkbox" class="summary-check" value=""   onclick="AddGridFieldsUtils.summarySelection(this)">
                       </label>
                       <input type="hidden" name="summaryField[]" value="false">
                   </td>
                   <td align="center" width="85%">

                       <select  class="form-control" id="summaryActionField" name="summaryActionField[]" onchange="AddGridFieldsUtils.summaryActionSelection(this)" >
                           <option value="">Acción</option>
                           <option value="sum">Suma</option>
                           <option value="sys">Calculo del sistema</option>
                       </select>

                   </td>
               </tr>
                <tr>
                    <td align="center" width="10%"></td>
                    <td  align="center" width="85%">
                        <div class="calculated pul-right hide">
                            {if $CALCULATED_SYSTEM|@count gt 0}
                                <select class="form-control calculated-list" id="calculatedSystem"  name="calculatedSystemId[]" style="margin-top: 6px"  >
                                    <option value="">Seleccionar Calculo</option>
                                    {foreach from=$CALCULATED_SYSTEM key=k item=v}
                                        <option value="{$v->getId ()}">{$v->getName ()}</option>
                                    {/foreach}
                                </select>
                            {else}
                                <div class="pull-left" style="color: #ff2222"><br/>No se encontraron <br>cálculos definidos</div>
                            {/if}
                        </div>
                    </td>
                </tr>
            </table>


        </td>
{/strip}