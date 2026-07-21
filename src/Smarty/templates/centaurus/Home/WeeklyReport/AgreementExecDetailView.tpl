{strip}
    <div class="row">
        {if $DATA_FIELD neq NULL}
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                <div class="table-responsive">
                    <table id="agreements_table" class="table table-bordered tablegridvalidate">
                        <thead>
                        <tr valign="top" style="background-color: #efefef">
                            <td class="text-center" width="6%" style="">
                                <span style="">&nbsp;</span>
                            </td>
                            <td class="text-left" width="42%" style="">
                                <span style="">Campo</span>
                            </td>
                            <td class="text-left" width="50%" style="">
                                <span style="">Valor</span>
                            </td>
                        </tr>
                        </thead>
                        <tbody id="tbody-otra_informacion-13369" rowtotal="0">
                        {foreach $DATA_FIELD as $key => $value}
                            {if ($value eq NULL) || (in_array($key, $EXCLUDE_FIELDS))}{continue}{/if}
                            <tr valign="top">
                                <td class="text-center" width="6%" style="">
                                    <span style="">&nbsp;</span>
                                </td>
                                <td class="text-left" width="42%" style="">
                                    <span style="">{if isset($DATA_FIELD_LABELS[$key]) && ($DATA_FIELD_LABELS[$key] neq NULL)}{$DATA_FIELD_LABELS[$key]}{else}{$key}{/if}</span>
                                </td>
                                <td class="text-left" width="50%" style="">
                                    <span style="">{$value}</span>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot id="tfoot-13369">
                        </tfoot>
                    </table>
                </div>
            </div>
        {else}
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                <div class="alert alert-danger">
                    <strong>Error!</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
    </div>
{/strip}