function asignaValorCampo(id, valor) {
  jQuery("#" + id).val(valor);
}
function submitForm() {
  jQuery("#formCalendar").submit();
}
function setServerName(card) {
  var host = jQuery("#new-server-fqdn").val();
  var name = jQuery("#new-server-name").val();
  var displayName = host;
  if (name) {
    displayName = name + " (" + host + ")";
  }
  card.wizard.setSubtitle(displayName);
  card.wizard.el.find(".create-server-name").text(displayName);
}
function ValidarFechaInicio(el) {
  var valor = el.val();
  var ret = {
    status: true,
  };
  if (!valor) {
    ret.status = false;
    ret.msg = "Especifique la fecha de Inicio";
  }
  return ret;
}
function ValidarAsunto(el) {
  var valor = el.val();
  var ret = {
    status: true,
  };
  if (!valor) {
    ret.status = false;
    ret.msg = "Especifique el Asunto";
  }
  return ret;
}
function ValidarFechaFin(el) {
  var valor = el.val();
  var ret = {
    status: true,
  };
  if (!valor) {
    ret.status = false;
    ret.msg = "Especifique la fecha de Finalización";
  }
  if (!comparaFechas("datepickerDate", "datepickerDateEnd")) {
    ret.status = false;
    ret.msg = "La fecha de Finalización debe ser mayor a la fecha de Inicio";
  }
  return ret;
}
function inicializarCampo(campo, valor) {
  jQuery("#" + campo).val(valor);
}
function comparaFechas(datepickerDate, datepickerDateEnd) {
  datepickerDate = jQuery("#" + datepickerDate).val();
  datepickerDateEnd = jQuery("#" + datepickerDateEnd).val();
  var fechaInicio = datepickerDate.split("-");
  var year_start_date = fechaInicio[2];
  var month_start_date = fechaInicio[1] - 1;
  var day_start_date = fechaInicio[0];
  var horaInicio = jQuery("#timepicker").val();
  horaInicio = horaInicio.split(":");
  var horaIni = horaInicio[0];
  var minutoIni = horaInicio[1];
  var segundosIni = horaInicio[2];
  var horaFinal = jQuery("#timepickerEnd").val();
  horaFinal = horaFinal.split(":");
  var horaFin = horaFinal[0];
  var minutoFin = horaFinal[1];
  var segundosFin = horaFinal[2];
  var fechaFin = datepickerDateEnd.split("-");
  var year_end_date = fechaFin[2];
  var month_end_date = fechaFin[1] - 1;
  var day_end_date = fechaFin[0];
  var date1 = new Date();
  var date2 = new Date();
  date1.setYear(year_start_date);
  date1.setMonth(month_start_date);
  date1.setDate(day_start_date);
  date1.setHours(horaIni);
  date1.setMinutes(minutoIni);
  date1.setSeconds(segundosIni);
  date2.setYear(year_end_date);
  date2.setMonth(month_end_date);
  date2.setDate(day_end_date);
  date2.setHours(horaFin);
  date2.setMinutes(minutoFin);
  date1.setSeconds(segundosFin);
  return date1 <= date2;
}
function addEventoCalendar(datepickerDate, datepickerDateEnd, subject) {
  var fechaInicio = datepickerDate.split("-");
  var year_start_date = fechaInicio[2];
  var month_start_date = fechaInicio[1] - 1;
  var day_start_date = fechaInicio[0];
  var fechaFin = datepickerDateEnd.split("-");
  var year_end_date = fechaFin[2];
  var month_end_date = fechaFin[1] - 1;
  var day_end_date = fechaFin[0];
  jQuery("#calendar").fullCalendar(
    "renderEvent",
    {
      title: subject,
      start: new Date(year_start_date, month_start_date, day_start_date),
      end: new Date(year_end_date, month_end_date, day_end_date),
      allDay: false,
    },
    true
  );
}
function clearForm() {
  jQuery("#subject").val("");
  jQuery("#description").val("");
  jQuery("#location").val("");
}
function toggleAssignType(currType) {
  if (currType == "U") {
    jQuery("#assigned_user").css("display", "block");
    jQuery("#assign_team").css("display", "none");
  } else {
    jQuery("#assigned_user").css("display", "none");
    jQuery("#assign_team").css("display", "block");
  }
}
function incUser(avail_users, sel_users) {
  var availListObj = getObj(avail_users),
    selectedColumnsObj = getObj(sel_users);

  for (var i = 0; i < selectedColumnsObj.length; i++) {
    selectedColumnsObj.options[i].selected = false;
  }
  for (i = 0; i < availListObj.length; i++) {
    if (availListObj.options[i].selected == true) {
      var rowFound = false;
      var existingObj = null;
      for (var j = 0; j < selectedColumnsObj.length; j++) {
        if (
          selectedColumnsObj.options[j].value == availListObj.options[i].value
        ) {
          rowFound = true;
          existingObj = selectedColumnsObj.options[j];
          break;
        }
      }
      if (rowFound != true) {
        var newColObj = document.createElement("OPTION");
        newColObj.value = availListObj.options[i].value;
        if (browser_ie) {
          newColObj.innerText = availListObj.options[i].innerText;
        } else if (browser_nn4 || browser_nn6) {
          newColObj.text = availListObj.options[i].text;
        }
        selectedColumnsObj.appendChild(newColObj);
        availListObj.options[i].selected = false;
        newColObj.selected = true;
        rowFound = false;
      } else if (existingObj != null) {
        existingObj.selected = true;
      }
    }
  }
}
function rmvUser(sel_users) {
  var selectedColumnsObj = getObj(sel_users);
  var selectlength = selectedColumnsObj.options.length;
  for (var i = 0; i <= selectlength; i++) {
    if (selectedColumnsObj.options.selectedIndex >= 0) {
      selectedColumnsObj.remove(selectedColumnsObj.options.selectedIndex);
    }
  }
}
(function (jQuery) {
  var totalNewRows = 0,
    relatedRowTemplate = jQuery("#relatedrowtemplate").html();

  var addRow = function (buttonElement) {
    var button = jQuery(buttonElement),
      tableBody = button.closest(".table-responsive").find("table > tbody"),
      template;
    totalNewRows += 1;
    template = jQuery(
      relatedRowTemplate.replace(new RegExp("__ID__", "g"), totalNewRows * -1)
    );
    tableBody.append(template);
  };

  var clearFields = function (valueFieldId) {
    jQuery("#" + valueFieldId + "_display").val("");
    jQuery("#" + valueFieldId).val("");
  };

  var deleteRow = function (buttonElement) {
    if (
      !confirm("¿Estás seguro que quieres eliminar la relación seleccionada?")
    ) {
      return;
    }

    jQuery(buttonElement).closest("tr").remove();
  };

  var openPopup = function (buttonElement, relatedCrmFieldId) {
    var button = jQuery(buttonElement),
      moduleNameElement = button.closest("tr").find(".modulename"),
      moduleName = moduleNameElement.val(),
      arguments;

    if (
      moduleName === undefined ||
      moduleName === null ||
      moduleName.trim() === ""
    ) {
      alert("Selecciona el módulo");
      moduleNameElement.focus();
      return false;
    }

    arguments = [
      "module=" + encodeURIComponent(moduleName),
      "action=Popup",
      "html=Popup_picker",
      "form=vtlibPopupView",
      "forfield=" + encodeURIComponent(relatedCrmFieldId),
    ];

    return window.open(
      "index.php?" + arguments.join("&"),
      "popup",
      "width=640,height=602,resizable=0,scrollbars=1,top=150,left=200"
    );
  };

  var openModal = function (buttonElement) {
    var button = jQuery(buttonElement),
      relatedRecordRow = button.closest(".related-record"),
      moduleNameElement = relatedRecordRow.find(".modulename"),
      displayFieldId = relatedRecordRow.find(".display-field").attr("id"),
      dataFieldId = relatedRecordRow.find(".data-field").attr("id"),
      moduleName = moduleNameElement.val(),
      moduleLabel = moduleNameElement.find("option:selected").text();

    if (
      moduleName === undefined ||
      moduleName === null ||
      moduleName.trim() === ""
    ) {
      alert("Selecciona el módulo");
      moduleNameElement.focus();
      return false;
    }

    button.attr("data-current-module", "Calendar");
    button.attr("data-display-field-id", displayFieldId);
    button.attr("data-field-id", dataFieldId);
    button.attr("data-referenced-module", moduleName);
    button.attr("data-title", moduleLabel);

    RelatedModuleModalUtils.openModal(buttonElement);
  };

  var viewModule = function (e, obj, module, id) {
    var objA = jQuery(obj),
      btnViews = jQuery("#btn-" + id),
      btnGroup = jQuery("#views-btn-group-" + id),
      moduleRows = objA.parent().parent(),
      viewRows = jQuery("#rules-" + id + " li"),
      activeClass = module + "-" + id;

    // Mostrar el botón de vistas si estaba oculto (cuando no había módulo por defecto)
    if (btnGroup.length && btnGroup.css("display") === "none") {
      btnGroup.css("display", "");
    }

    btnViews.html(
      "Vistas de " + objA.html() + '&nbsp;<span class="caret"></span>'
    );
    jQuery("." + activeClass).removeClass("hide");

    viewRows.each(function () {
      var li = jQuery(this);
      if (!li.hasClass(activeClass)) {
        li.addClass("hide");
      }
    });

    moduleRows.find("li").each(function () {
      var li = jQuery(this);
      li.removeClass("active");
    });
    objA.parent().addClass("active");
    //btnViews.trigger('click');
    console.log(btnViews.parent());
    e.preventDefault();
    btnViews.parent().addClass("open");
  };

  window.ActivityUtils = {
    addRow: addRow,
    clearFields: clearFields,
    deleteRow: deleteRow,
    openModal: openModal,
    viewModule: viewModule,
  };
})(jQuery);
