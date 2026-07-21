<div class="row" style="margin: 6px">
    <div class="col-md-4">
        <button type="button" class="btn btn-primary btn-reg-modal"
                data-current-module=""
                data-display-field-id="valSelected"
                data-field-id="idSelected"
                data-referenced-module="{$MODULE_NAME}"
                data-title="Seleccionar {$MODULE_NAME}"
                onclick="RelatedModuleModalUtils.openModal (this);">Seleccionar un registro
        </button>
    </div>
    <div class="col-md-8">
        <h4 id="selectedRgister" class="pull-left bloc-name"></h4>
    </div>
</div>


<table class="table table-hover table-bordered table-responsive" style="margin-top: 6px">
    <thead>
    <tr>
        {foreach from=$SUB_CAMPOS_GRID key=k item=v}
            {if $v.uitype neq 10 && $v.uitype neq 56 && $v.uitype neq 15 }
                <th style="text-align: center">{$v.label}</th>
            {/if}
        {/foreach}
    </tr>
    </thead>
    <tbody>
    <tr id="column-to-select">
        {foreach from=$SUB_CAMPOS_GRID key=k item=v}
            {if $v.uitype neq 10 && $v.uitype neq 56 && $v.uitype neq 15 }
                <td style="text-align: center"><input class="column-select"
                                                      data-info="{$v.label}@{$v.name}@{$v.subfieldsid}" type="checkbox"
                                                      value="{$v.subfieldsid}" name="column[]">&nbsp;{$v.label}</td>
            {/if}
        {/foreach}
    </tr>
    </tbody>
</table>
<script language="JavaScript">
    jQuery('.column-select').click(function (e) {
        gridSelectedField = [];
        jQuery('#column-to-select input:checked').each(function () {

            if (GridPropertiesUtils.isSetGridObjectProperties()) {
                if (jQuery('#idEditSelected').val() != '') {
                    GridPropertiesUtils.setGridEditImportColumn(jQuery(this).attr('data-info'));
                } else {
                    jQuery(this).prop('checked', false);
                    alert('Seleccione un registro');
                    return false;
                }
            } else {
                gridSelectedField.push(jQuery(this).attr('data-info'));
            }
        });
    })
    jQuery(document).on("relatedModuleRecordSelected", function () {
        if (GridPropertiesUtils.isSetGridObjectProperties()) {
            jQuery('#selectedRgister').html('Registro seleccionado: ' + jQuery('#valEditSelected').val())
        } else {
            jQuery('#selectedRgister').html('Registro seleccionado: ' + jQuery('#valSelected').val())
        }

    });
</script>

