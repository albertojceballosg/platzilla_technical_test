{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
    <div class="row">
        <div class="alert alert-danger">
            <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    </div>
{/if}
<div class="row">
    {if $TASK neq NULL}
        {*$TASK|var_dump*}
    <div class="col-md-12">
        <p class="text-left small" style="margin-bottom: 1px; padding-bottom: 1px; font-weight: bold">Trabajo:&nbsp;<small>{$TASK['cod_orden_de_tra']}</small></p>
        <p class="text-justify small">{$TASK['titulo']}</p>
    </div>
    {if {$TASK['descripcion']} neq NULL}
    <div class="col-md-12">
        <p class="text-left small" style="margin-bottom: 1px; padding-bottom: 1px; font-weight: bold">Descripción:</p>
        {$TASK['descripcion']}
    </div>
    {/if}
    <div class="col-md-12">
        <table class="table table-striped">
            <tbody>
            <tr>
                <td>&nbsp;</td>
                <td>Tipo de acividad:</td>
                <td>{$MOD[$TASK['tipo_dactividad']]}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Estado:</td>
                <td>{$TASK['estado_de_la_orden']}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Fecha prevista de inicio:</td>
                <td>{$TASK['fecha_prevista']}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Avance del trabajo (%):</td>
                <td>{$TASK['overall_progress_perc']}
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    {/if}
    <div class="col-md-12">
        &nbsp;
    </div>
</div>