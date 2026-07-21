jQuery(function () {
    var treeView = jQuery("#treeview").dxTreeView({
        items: documents,
        //width: 800,
        searchEnabled: true
    }).dxTreeView("instance");

    jQuery("#searchMode").dxSelectBox({
        items: ["contains", "startswith"],
        value: "contains",
        onValueChanged: function (data) {
            treeView.option("searchMode", data.value);
        }
    });
});