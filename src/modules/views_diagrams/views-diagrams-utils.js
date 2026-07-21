(function (jQuery) {
  //private var

  //public method
  var selectModule = function (obj, id) {
    var moduleName = jQuery(obj).val(),
      form = jQuery("#form-views-diagrams-" + id),
      loading = jQuery("#loading-" + id);

    if (moduleName !== "") {
      loading.removeClass("hide");
      form.submit();
    }
  };

  var selectedDiagramUser = function (e, obj, id) {
    var allList = jQuery("#vies-diagram-user-" + id + " li"),
      btn = jQuery("#btn-group-user-" + id),
      list = jQuery(obj).parent(),
      userId = jQuery(obj).attr("rel"),
      helpText = jQuery("#help-user-" + id),
      faClass = btn.find("i").eq(0),
      inviteesId = jQuery("#inviteesid-" + id),
      userSelected = [],
      usersIds = [],
      found = 0,
      infoText = "",
      invitees = "";

    if (list.hasClass("active")) {
      list.removeClass("active");
    } else {
      list.addClass("active");
    }

    faClass.css("color", "");
    faClass.removeClass("fa-user");
    faClass.removeClass("fa-users");
    allList.each(function () {
      var li = jQuery(this),
        userId = li.children("a").attr("rel");
      if (li.hasClass("active") && userId !== "undefined") {
        userSelected.push(li.find("a").eq(0).attr("title"));
        usersIds.push(userId);
        found += 1;
      }
    });
    //helpText = jQuery ('#help-user-' + id),
    if (found === 0) {
      faClass.addClass("fa-user");
      faClass.css("color", "#cccccc");
      helpText.html("");
      btn.removeClass("btn-primary");
      btn.addClass("btn-default");
    } else if (found === 1) {
      faClass.addClass("fa-user");
      faClass.css("color", "");
      helpText.html("<b>Usuario:</b>&nbsp;" + userSelected.join(","));
      btn.removeClass("btn-default");
      btn.addClass("btn-primary");
    } else {
      faClass.addClass("fa-users");
      faClass.css("color", "");
      helpText.html("<b>Usuarios:</b>&nbsp;" + userSelected.join(","));
      btn.removeClass("btn-default");
      btn.addClass("btn-primary");
    }
    invitees = usersIds.join(", ");
    inviteesId.val(invitees);
    e.preventDefault();
  };

  window.ViewsDiagramsUtls = {
    selectedDiagramUser: selectedDiagramUser,
    selectModule: selectModule,
  };

  var onDocumentReadyHandler = function () {};
  jQuery(document).ready(onDocumentReadyHandler);
})(jQuery);
