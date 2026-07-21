{* Scripts y funciones JS específicas de ListView *}
{block name="listview-scripts"}
    <script type="text/javascript">
    var typeofdata = {
        'C': [ 'e', 'n' ],
        'D': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
        'DT': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
        'E': [ 'e', 'n', 's', 'ew', 'c', 'k' ],
        'I': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
        'N': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
        'NN': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
        'T': [ 'e', 'n', 'l', 'g', 'm', 'h' ],
        'V': [ 'e', 'n', 's', 'ew', 'c', 'k' ]
    };
    var fLabels = {
        'c':  "{$APP.contains}",
        'e':  "{$APP.is}",
        'ew': "{$APP.ends_with}",
        'g':  "{$APP.greater_than}",
        'h':  "{$APP.greater_or_equal}",
        'k':  "{$APP.does_not_contains}",
        'l':  "{$APP.less_than}",
        'm':  "{$APP.less_or_equal}",
        'n':  "{$APP.is_not}",
        's':  "{$APP.begins_with}"
    };
    var noneLabel;
    function trimfValues (value) {
        var string_array = value.split (":");
        return string_array[ 4 ];
    }
    function updatefOptions (sel, opSelName) {
        var selObj = document.getElementById (opSelName),
            fieldtype = null,
            currOption = selObj.options[ selObj.selectedIndex ],
            currField = sel.options[ sel.selectedIndex ],
            ops, nMaxVal, nLoop;
        if (currField.value != null && currField.value.length != 0) {
            fieldtype = trimfValues (currField.value);
            fieldtype = fieldtype.replace (/\\'/g, '');
            ops = typeofdata[ fieldtype ];
            if (ops != null) {
                nMaxVal = selObj.length;
                for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
                    selObj.remove (0);
                }
                for (var i = 0; i < ops.length; i++) {
                    var label = fLabels[ ops[ i ] ];
                    if (label == null) {
                        continue;
                    }
                    var option = new Option (fLabels[ ops[ i ] ], ops[ i ]);
                    selObj.options[ i ] = option;
                    if (currOption != null && currOption.value == option.value) {
                        option.selected = true;
                    }
                }
            }
        } else {
            nMaxVal = selObj.length;
            for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
                selObj.remove (0);
            }
            selObj.options[ 0 ] = new Option ('None', '');
            if (currField.value == '') {
                selObj.options[ 0 ].selected = true;
            }
        }
    }
    </script>
{/block}
