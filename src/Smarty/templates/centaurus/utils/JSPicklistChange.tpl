{foreach $fields as $field => $row}
    {if $row['relationship'] eq NULL}
        {continue}
    {else}
        {assign var="picklistRelationship" value=$row['relationship']}
    {/if}
    {foreach $picklistRelationship as $relationship}
    {literal}
        <script type="text/javascript">
            {/literal}
            {if $MODE eq 'edit'}
            function init_{$relationship['mother']} () {
                var motherValue   =  jQuery ("#{$relationship['mother']}").val (),
                    daughterValue = jQuery('#{$relationship['daughter']}').val ();
                jQuery ("#{$relationship['mother']}").val ('');
                jQuery ('#{$relationship['mother']} option:selected').attr ('disabled', false);
                jQuery ("#{$relationship['mother']}").val (motherValue);
                onchange_{$relationship['mother']}(jQuery ("#{$relationship['mother']}"));
                jQuery('#{$relationship['daughter']}').val (daughterValue)
            }
            {else}
                jQuery ('#{$relationship['mother']} option:selected').attr ('disabled', false);
            {/if}
            function onchange_{$relationship['mother']}(obj) {
                var optionSelected  = jQuery (obj),
                    values          = JSON.parse (JSON.stringify ({$relationship['values']})),
                    daughter        = jQuery('#{$relationship['daughter']}'),
                    daughterOptions = daughter.find ('option');
                daughter.val('');

                if (optionSelected.val () !== '') {
                    jQuery.each (daughterOptions, function (index, option) {
                        var thisOption = jQuery (option);
                        if ((jQuery.inArray (thisOption.val (), values[optionSelected.val ()]) === -1)  && (thisOption.val () !== '')) {
                            thisOption.hide ();
                        } else {
                            thisOption.show ();
                        }
                    });
                } else {
                    jQuery.each (daughterOptions, function (index, option) {
                        jQuery (option).show ();
                    });
                }
            }
            {if $MODE eq 'edit'}
            init_{$relationship['mother']} ();
            {/if}
            {literal}
        </script>
    {/literal}
    {/foreach}
{/foreach}

