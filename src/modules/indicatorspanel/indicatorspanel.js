(function (jQuery) {
  // private properties
  var _months = [],
    _forceDelete = false;

  // private methods
  var _deleteRows = function (id, exception) {
    var targetMonth = jQuery(".target_month");
    _forceDelete = true;

    targetMonth.each(function (index, obj) {
      var month = jQuery(obj),
        monthId = month.attr("data-local-id"),
        tr = jQuery(".template-month-" + monthId);
      if (jQuery.inArray(month.val(), exception) === -1) {
        tr.find("button").trigger("click");
      }
    });
    _forceDelete = false;
  };

  var _getWeekData = function (obj, month) {
    var arguments = {
      module: "indicatorspanel",
      fldmodule: "indicatorspanel",
      action: "AjaxBoxScore",
      function: "GET-WEEK-DATA",
      month: month,
      Ajax: true,
    };
    obj.html(
      '<td colspan="3" class="text-center" style="padding: 2px !important;"><i class="fa fa-spinner fa-spin fa-fw"></i></td>'
    );
    jQuery.post("index.php", arguments, function (data) {
      var message, data;
      try {
        message = JSON.parse(JSON.stringify(data));
        if (message.error !== "OK") {
          throw message.error;
        } else {
          obj.empty();
          obj.html(message.html);
        }
      } catch (e) {
        alert(e);
      }
    });
  };

  var _validateFields = function (obj, validate) {
    if (
      obj.val() === "undefined" ||
      obj.val() == null ||
      obj.val() === "" ||
      obj.val() === undefined
    ) {
      obj.parent().addClass("has-error");
      return false;
    } else {
      obj.parent().removeClass("has-error");
    }
    return validate;
  };

  var _validateIndicator = function (id) {
    var boxScore = jQuery("#box_score-" + id),
      description = jQuery("#description-" + id),
      marginAccordingTarget = jQuery("#margin_according_target-" + id),
      marginCloseTarget = jQuery("#margin_close_target-" + id),
      dataSource = jQuery("#data_source-" + id),
      fldModule = jQuery("#fldmodule-" + id),
      calculateField = jQuery("#calculateField-" + id),
      calculationEngine = jQuery("#calculationEngine-" + id),
      validate = true;
    validate = _validateFields(boxScore, validate);
    validate = _validateFields(description, validate);
    validate = _validateFields(marginAccordingTarget, validate);
    validate = _validateFields(marginCloseTarget, validate);
    validate = _validateFields(dataSource, validate);
    if (dataSource.val() !== "") {
      if (dataSource.val() === "1") {
        validate = _validateFields(fldModule, validate);
        validate = _validateFields(calculateField, validate);
      } else if (dataSource.val() === "2") {
        validate = _validateFields(calculationEngine, validate);
      }
    }

    if (_validateRages(id, validate) && validate) {
      return true;
    }

    return validate;
  };

  var _validateMonth = function () {
    var targetMonth = jQuery(".target_month");
    _months = [];
    targetMonth.each(function (index, obj) {
      var month = jQuery(obj);
      _months.push(month.val());
    });
  };

  var _validateRages = function (id) {
    var tBody = jQuery("#body-range-" + id),
      targetMonth = jQuery(".target_month"),
      objetivo = jQuery(".objetive"),
      validate = true;

    if (tBody.find(".target_month").length === 0) {
      alert("Debes agregar al menos un rango de objetivos.");
      return false;
    } else {
      targetMonth.each(function (index, obj) {
        var month = jQuery(obj);
        validate = _validateFields(month);
      });
      objetivo.each(function (index, obj) {
        var thisObjetive = jQuery(obj);
        validate = _validateFields(thisObjetive);
      });
    }
  };

  // public methods
  var addRowToTable = function (obj, id) {
    var row = jQuery("#body-range-" + id),
      btn = jQuery(obj),
      sequence = btn.attr("data-sequence"),
      templateName = jQuery("#objetive_scale-" + id).val() + "_TEMPLATE-" + id,
      rowId = Math.floor(Math.random() * 500) + 1,
      templeRow = jQuery(
        jQuery("#" + templateName)
          .html()
          .replace(/__ID__/g, rowId)
      );

    if (sequence === "0") {
      row.empty();
    }
    row.append(templeRow);
    sequence = parseInt(sequence) + 1;
    btn.attr("data-sequence", sequence.toString());
  };

  var delRowWeekToTable = function (obj, tr, id) {
    var tBody = jQuery("#body-range-" + id);

    if (!_forceDelete) {
      if (
        !confirm("¿Estás seguro que quieres eliminar el elemento seleccionado?")
      ) {
        return;
      }
    }
    tBody.find("." + tr).remove();
    _validateMonth();
  };

  var selectedDataSource = function (obj, id) {
    var calculateFieldSelect = jQuery("#calculateField-" + id),
      calculatedFieldRow = jQuery("#calculated-field-row-" + id),
      calculationEngineRow = jQuery("#calculated-system-row"),
      calculationEngineSelect = jQuery("#calculationEngine-" + id),
      dataSource = jQuery(obj).val(),
      forModule = jQuery("#fldmodule-" + id),
      forModuleRow = jQuery("#box_score-row");

    if (dataSource === "0") {
      forModuleRow.addClass("hide");
      forModule.val("");
      calculatedFieldRow.addClass("hide");
      calculateFieldSelect.val("");
      calculationEngineRow.addClass("hide");
      calculationEngineSelect.val("");
    } else if (dataSource === "1") {
      forModuleRow.removeClass("hide");
      forModule.focus();
      calculatedFieldRow.addClass("hide");
      calculateFieldSelect.val("");
      calculationEngineRow.addClass("hide");
      calculationEngineSelect.val("");
    } else if (dataSource === "2") {
      calculationEngineRow.removeClass("hide");
      calculationEngineSelect.focus();
      forModuleRow.addClass("hide");
      forModule.val("");
      calculatedFieldRow.addClass("hide");
      calculateFieldSelect.val("");
    } else {
      forModuleRow.addClass("hide");
      forModule.val("");
      calculatedFieldRow.addClass("hide");
      calculateFieldSelect.val("");
      calculationEngineRow.addClass("hide");
      calculationEngineSelect.val("");
    }
  };

  var selectedRange = function (obj, id) {
    var range = jQuery(obj),
      addButton = jQuery("#bs-add-row-table-" + id),
      objetiveScale = jQuery("#objetive_scale-" + id),
      tFoot = jQuery("#tfoot-" + id),
      row = jQuery("#body-range-" + id),
      rows = tFoot.find("button").attr("data-sequence"),
      headerTitle = jQuery("#column-" + id);
    addButton.removeAttr("disabled");
    _months = [];
    if (range.is(":checked")) {
      objetiveScale.val("MONTH");
      headerTitle.text("MES DE APLICACIÓN");
    } else {
      objetiveScale.val("WEEK");
      headerTitle.text("SEMANA DE APLICACIÓN");
    }
    if (rows !== "0") {
      row.empty();
      tFoot.find("button").attr("data-sequence", 0);
    }
  };

  var selectedModule = function (obj, id) {
    var module = jQuery(obj).val(),
      calculateFieldSelect = jQuery("#calculateField-" + id),
      calculatedFieldRow = jQuery("#calculated-field-row-" + id),
      errorText = jQuery("#calculateField-help");

    errorText.html("");
    if (module !== "") {
      if (!calculatedFieldRow.hasClass("hide")) {
        calculatedFieldRow.addClass("hide");
      }
      new Ajax.Request("index.php", {
        queue: { position: "end", scope: "command" },
        method: "post",
        postBody:
          "module=indicatorspanel&action=indicatorspanelAjax&file=AjaxBoxScore&fldmodule=" +
          module +
          "&function=getFields",
        onComplete: function (response) {
          if (response.responseText != "") {
            var fields = JSON.parse(response.responseText);
            calculateFieldSelect.empty();
            calculateFieldSelect.append(
              jQuery("<option>", {
                value: "",
                text: "Seleccione...",
              })
            );
            jQuery.each(fields, function (i, field) {
              calculateFieldSelect.append(
                jQuery("<option>", {
                  value: field.fieldname,
                  text: field.fieldlabel,
                })
              );
            });
            calculatedFieldRow.removeClass("hide");
            calculateFieldSelect.focus();
          } else {
            errorText.html(response.responseText);
          }
        },
      });
    }
  };

  var monthOfApplication = function (obj, id, type) {
    var monthSelect = jQuery(obj),
      localId = monthSelect.attr("data-local-id"),
      addButton = jQuery("#bs-add-row-table-" + id);

    addButton.removeAttr("disabled");
    if (monthSelect.val() === "all") {
      addButton.attr("disabled", "disabled");
      _deleteRows(id, ["all"]);
      _months = [];
      return false;
    }

    if (monthSelect.val() !== "") {
      if (jQuery.inArray(monthSelect.val(), _months) !== -1) {
        alert("El mes seleccionado ya ha sido agregado");
        monthSelect.val("");
        return false;
      } else {
        _months.push(monthSelect.val());
      }
      if (type === "week") {
        var trWeek = jQuery("#tr-" + localId);
        _getWeekData(trWeek, monthSelect.val());
      }
    }
  };

  var saveIndicator = function (obj, id) {
    var button = jQuery(obj),
      form = jQuery("#bs-form-" + id),
      validate = _validateIndicator(id);

    if (validate) {
      button.attr("disabled", "disabled");
      form.submit();
    }
  };

  window.IndicatorUtils = {
    addRowToTable: addRowToTable,
    delRowWeekToTable: delRowWeekToTable,
    monthOfApplication: monthOfApplication,
    saveIndicator: saveIndicator,
    selectedDataSource: selectedDataSource,
    selectedRange: selectedRange,
    selectedModule: selectedModule,
  };

  var onDocumentReadyHandler = function () {};
  jQuery(document).ready(onDocumentReadyHandler);
})(jQuery);

function loadIndicators(element) {
  var viewSelect = jQuery("#dinamicViewScale").val();
  var monthSelect = jQuery("#dinamicMonthsearch").val();

  if (viewSelect == "" && jQuery("#viewScale").val() == "") {
    viewSelect = "Month";
  } else {
    if (jQuery("#newblock").val() === "reload") {
      viewSelect = jQuery("#viewScale").val();
    } else {
      viewSelect = "Month";
    }
  }

  if (monthSelect == "") {
    var date = new Date();
    var m;
    m = date.getMonth() + 1;
    if (m < 10) {
      monthSelect = "0" + m;
    } else {
      monthSelect = m;
    }
  }

  if (
    jQuery("#newblock").val() === "reload" ||
    element.className.indexOf("active") == -1
  ) {
    jQuery("div .loadIndicatorstabs").html("");
    var code_aplication = element.id;
    code_aplication = code_aplication.split("--")[1];

    var param =
      "codeApp=" +
      code_aplication +
      "&monthsearch=" +
      monthSelect +
      "&viewScale=" +
      viewSelect;
    var url = "";

    if (code_aplication == "all") {
      url =
        "action=indicatorspanelAjax&module=indicatorspanel&file=allAppDetailView&ajax=true&" +
        param;
    } else {
      url =
        "action=indicatorspanelAjax&module=indicatorspanel&file=DetailView&ajax=true&" +
        param;
    }

    new Ajax.Request("index.php", {
      queue: { position: "end", scope: "command" },
      method: "post",
      postBody: url,
      onComplete: function (response) {
        jQuery("#tab-" + code_aplication).html("");
        jQuery("#tab-" + code_aplication).html(response.responseText);

        jQuery("#newblock").val("");
        jQuery("#dinamicMonthsearch").val("");
        jQuery("#dinamicViewScale").val("");

        var monthSearch = jQuery("#monthsearch");
        if (monthSearch.val() == "") {
          var date = new Date();
          var m;
          m = date.getMonth() + 1;
          if (m < 10) {
            monthSearch.val("0" + m);
          } else {
            monthSearch.val(m);
          }
        }

        var view = jQuery("#viewScale");
        if (view.val() == "") {
          view.val("Month");
        }
      },
    });
  }
}

function saveBlock() {
  var colorbase = jQuery("#colorbase").val();
  var colordegrade = jQuery("#colordegrade").val();
  var record = jQuery("#record").val();
  var appcode = jQuery("#appcode").val();
  var view = jQuery("#viewScale").val();
  var type = jQuery("#type").val();

  if (colorbase == "" || colordegrade == "") {
    alert(alert_arr.COLORBASE_NO_EMPTY);
  } else if (appcode == "all" && type == "" && record == "") {
    alert(alert_arr.SELECT_APP_BLOCK);
  } else {
    new Ajax.Request("index.php", {
      queue: { position: "end", scope: "command" },
      method: "post",
      postBody:
        "module=indicatorspanel&action=indicatorspanelAjax&file=SaveBlockBoxScore&colorbase=" +
        colorbase +
        "&colordegrade=" +
        colordegrade +
        "&record=" +
        record +
        "&type=" +
        type,
      onComplete: function (response) {

        if (response.responseText == "success") {
          if (type != "") {
            alert(alert_arr.SAVE_EDIT_BLOCK);
          } else {
            alert(alert_arr.SAVE_BLOCK);
          }

          jQuery("#crearblock").removeClass("in").hide();
          jQuery(".md-overlay").css({ opacity: 0.0, visibility: "hidden" });
          jQuery("#newblock").val("reload");
          jQuery("#dinamicViewScale").val(view);
          var obj = jQuery("#li--" + appcode);
          obj.click();
        } else {
          alert(alert_arr.ERROR);
        }
      },
    });
  }
}

function callAddEditIndicators(
  module,
  type,
  accountid,
  monthsearch,
  app,
  dataid,
  mode
) {
  jQuery(".md-overlay").css({ opacity: 1, visibility: "visible" });
  var view = jQuery("#viewScale").val();
  if (dataid != "") {
    url =
      "module=" +
      module +
      "&action=indicatorspanelAjax&ajax=true&file=EditViewBox&type=" +
      type +
      "&account_id=" +
      accountid +
      "&monthsearch=" +
      monthsearch +
      "&app=" +
      app +
      "&dataid=" +
      dataid +
      "&record=" +
      accountid +
      "&mode=" +
      mode +
      "&viewScale=" +
      view;
  } else {
    url =
      "module=" +
      module +
      "&action=indicatorspanelAjax&ajax=true&file=EditViewBox&type=" +
      type +
      "&account_id=" +
      accountid +
      "&monthsearch=" +
      monthsearch +
      "&app=" +
      app +
      "&mode=" +
      mode +
      "&viewScale=" +
      view;
  }
  new Ajax.Request("index.php", {
    queue: { position: "end", scope: "command" },
    method: "post",
    postBody: url,
    onComplete: function (response) {

      var modal = jQuery("#addIndicators");

      modal.html(response.responseText);
      modal.addClass("md-show");

      // Forzar z-index y display para asegurar visibilidad
      modal.css({
        "z-index": "9999",
        display: "block",
        visibility: "visible",
      });


      // Verificar overlay
      var overlay = jQuery(".md-overlay");

      // Crear overlay si no existe
      if (overlay.length === 0) {
        jQuery("body").append('<div class="md-overlay"></div>');
        overlay = jQuery(".md-overlay");
      }

      // Configurar overlay
      overlay.css({
        position: "fixed",
        top: "0",
        left: "0",
        width: "100%",
        height: "100%",
        background: "rgba(0, 0, 0, 0.5)",
        "z-index": "9998",
        display: "block",
        opacity: "1",
        visibility: "visible",
      });


      // Centrar modal en el viewport
      modal.css({
        position: "fixed",
        top: "50%",
        left: "50%",
        transform: "translate(-50%, -50%)",
        "max-height": "90vh",
        "overflow-y": "auto"
      });

      // Forzar que el overlay esté detrás de la modal
      overlay.css("z-index", "9998");
    },
  });
}

function deleteOtherOperation(trdelete) {
  jQuery("#" + trdelete).remove();
}

function validateIndicator(oform) {
  var boxscore = jQuery("#box_score");
  if (
    boxscore.val() == "undefined" ||
    boxscore.val() == null ||
    boxscore.val() == ""
  ) {
    alert(alert_arr.SAVE_NAME_INDICATORS);
    boxscore.focus();

    return false;
  }

  // Validation of the target month
  var val = true,
    mesObjetivo = jQuery("#bodyObjtable").find(".targetmonth");
  mesObjetivo.each(function (index, element) {
    var filter0 = jQuery(element).val();
    if (filter0 != "") {
      if (filter0 != "") {
        mesObjetivo.each(function (c, element1) {
          var filter1 = jQuery(element1).val();
          if (filter1 != "") {
            if (c >= index + 1 && filter1 != "") {
              if (filter0 == filter1) {
                alert(alert_arr.REPETEAT_MESOBJECTIVE);
                jQuery(element1).focus();
                val = false;
                return false;
              }
            }
          } else {
            alert(alert_arr.SAVE_MESOBJECTIVE);
            jQuery(element1).focus();
            val = false;
            return false;
          }
        });
      }
      if (val == false) {
        return false;
      }
    } else {
      alert(alert_arr.SAVE_MESOBJECTIVE);
      jQuery(element).focus();
      val = false;
      return false;
    }
  });

  if (val == false) {
    return false;
  }
  //****************************************************************
  //Validation of the objective value
  //****************************************************************
  mesObjetivo = jQuery("#bodyObjtable").find(".objetive");
  mesObjetivo.each(function (index, element) {
    var filter0 = jQuery(element).val();

    if (filter0 == "") {
      alert(alert_arr.SAVE_OBJECTIVE);
      jQuery(element).focus();
      val = false;
      return false;
    }
  });

  if (val == false) {
    return false;
  }

  //****************************************************************

  var dao0 = jQuery("#dao_inf_0");
  if (dao0.val() == "undefined" || dao0.val() == null || dao0.val() == "") {
    alert(alert_arr.SAVE_CUMPL);
    dao0.focus();

    return false;
  }

  var dao1 = jQuery("#dao_inf_1");
  if (dao1.val() == "undefined" || dao1.val() == null || dao1.val() == "") {
    alert(alert_arr.SAVE_CUMPL);
    dao1.focus();

    return false;
  }

  var tipo0 = jQuery("#type_dao_inf_0");
  var tipo1 = jQuery("#type_dao_inf_1");
  if (jQuery("#record").val() != "") {
    if (
      (dao0.val().indexOf("%") > 0 && tipo0.val() != "%") ||
      (dao0.val().indexOf("%") == -1 && tipo0.val() == "%")
    ) {
      alert(alert_arr.FORMAT_PORCENTUAL);
      dao0.focus();

      return false;
    }

    if (
      (dao1.val().indexOf("%") > 0 && tipo1.val() != "%") ||
      (dao1.val().indexOf("%") == -1 && tipo1.val() == "%")
    ) {
      alert(alert_arr.FORMAT_PORCENTUAL);
      dao1.focus();

      return false;
    }
  } else {
    //Validating that the values of the range are% or numbers
    if (dao0.val().indexOf("%") > 0 && dao1.val().indexOf("%") == -1) {
      alert(alert_arr.FORMAT_PORCENTUAL_VAL);
      dao1.focus();

      return false;
    }

    if (dao1.val().indexOf("%") > 0 && dao0.val().indexOf("%") == -1) {
      alert(alert_arr.FORMAT_PORCENTUAL_VAL);
      dao0.focus();

      return false;
    }

    if (dao0.val().indexOf("%") > 0) {
      tipo0.val("%");
    }

    if (dao1.val().indexOf("%") > 0) {
      tipo1.val("%");
    }
  }

  var dataSource = jQuery("#data_source"),
    forModule = jQuery("#fldmodule"),
    calculateFieldSelect = jQuery("#calculateField");
  if (dataSource.val() === "") {
    alert("Seleccione una fuente de datos");
    dataSource.focus();
    return false;
  } else if (dataSource.val() == 1 && forModule.val() === "") {
    alert("Seleccione un módulo fuente");
    forModule.focus();
    return false;
  } else if (
    dataSource.val() == 1 &&
    forModule.val() !== "" &&
    calculateFieldSelect.val() === ""
  ) {
    alert("Seleccione un campo con cálculo");
    calculateFieldSelect.focus();
    return false;
  }

  jQuery("#create_indicator").val("1");
  return true;
}

function callDeleteIndicator(element) {
  if (!confirm(alert_arr.MESS_DELETE_INDICATOR)) {
    return false;
  }
  var rowid = element.id;
  jQuery
    .ajax({
      type: "POST",
      url: "index.php",
      data: {
        module: "indicatorspanel",
        action: "indicatorspanelAjax",
        file: "DeleteBox",
        record: rowid,
        delete: "true",
      },
    })
    .done(function (response) {
      jQuery("#row-" + rowid).fadeOut(function () {
        jQuery("#row-" + rowid).remove();
      });
    });
}

function callAddValues(module, type, record, monthsearch, app) {
  jQuery(".md-overlay").css({ opacity: 1, visibility: "visible" });
  var view = jQuery("#viewScale").val();
  var url =
    "module=" +
    module +
    "&action=indicatorspanelAjax&ajax=true&file=EditViewBoxValues&type=" +
    type +
    "&boxscoreid=" +
    record +
    "&monthsearch=" +
    monthsearch +
    "&app=" +
    app +
    "&viewScale=" +
    view;

  new Ajax.Request("index.php", {
    queue: { position: "end", scope: "command" },
    method: "post",
    postBody: url,
    onComplete: function (response) {
      jQuery("#addValues").html(response.responseText);
      jQuery("#addValues").addClass("md-show");
    },
  });
}

function callAddCalcules(
  module,
  type,
  accountid,
  monthsearch,
  app,
  mode,
  record
) {
  jQuery(".md-overlay").css({ opacity: 1, visibility: "visible" });
  var view = jQuery("#viewScale").val();
  var urecord = "";
  if (mode == "edit") {
    urecord = "&record=" + record;
  }
  var url =
    "module=" +
    module +
    "&action=indicatorspanelAjax&ajax=true&file=EditViewBoxCalc&type=" +
    type +
    "&account_id=" +
    accountid +
    "&monthsearch=" +
    monthsearch +
    "&app=" +
    app +
    "&viewScale=" +
    view +
    "&mode=" +
    mode +
    urecord;

  new Ajax.Request("index.php", {
    queue: { position: "end", scope: "command" },
    method: "post",
    postBody: url,
    onComplete: function (response) {
      jQuery("#addCalcules").html(response.responseText);
      jQuery("#addCalcules").addClass("md-show");
    },
  });
}

function validateIndicatorValues() {
  var validate = true;
  jQuery(".input-sm").each(function (i) {
    if (jQuery(this).val() != "") {
      if (
        jQuery(this).attr("objetive") != "" &&
        jQuery(this).attr("objetive") == "%"
      ) {
        if (jQuery(this).val().indexOf("%") == -1) {
          alert(alert_arr.FORMAT_PORCENTUAL_EDIT);
          jQuery(this).focus();
          validate = false;
          return false;
        }
      }
    }
  });

  if (validate) {
    jQuery("#edit_values_indicator").val("1");
    return true;
  } else {
    return false;
  }
}

function getIndicatorsMonths(monthSearch) {
  var date = new Date();
  var endDay = "";
  new Date(date.getFullYear(), date.getMonth() + 1, 0);
  var date_from = "";
  var date_to = "";
  var diaf = "";
  var month = [];
  month[0] = "01";
  month[1] = "02";
  month[2] = "03";
  month[3] = "04";
  month[4] = "05";
  month[5] = "06";
  month[6] = "07";
  month[7] = "08";
  month[8] = "09";
  month[9] = "10";
  month[10] = "11";
  month[11] = "12";

  if (
    jQuery("#" + monthSearch.id).val() == month[date.getMonth()] ||
    jQuery("#" + monthSearch.id).val() == ""
  ) {
    endDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    if (endDay.getDate() < 10) {
      diaf = "0" + endDay.getDate();
    } else {
      diaf = endDay.getDate();
    }
    date_from = date.getFullYear() + "-" + month[date.getMonth()] + "-" + "01";
    date_to = date.getFullYear() + "-" + month[date.getMonth()] + "-" + diaf;
  } else {
    endDay = new Date(
      date.getFullYear(),
      jQuery("#" + monthSearch.id).val() + 1,
      0
    );
    if (endDay.getDate() < 10) {
      diaf = "0" + endDay.getDate();
    } else {
      diaf = endDay.getDate();
    }
    date_from =
      date.getFullYear() +
      "-" +
      jQuery("#" + monthSearch.id).val() +
      "-" +
      "01";
    date_to =
      date.getFullYear() +
      "-" +
      jQuery("#" + monthSearch.id).val() +
      "-" +
      diaf;
  }
  jQuery("#date_from").val(date_from);
  jQuery("#date_to").val(date_to);

  var appcode = jQuery("#appcode").val();
  jQuery("#newblock").val("reload");
  jQuery("#dinamicMonthsearch").val(jQuery("#" + monthSearch.id).val());
  jQuery("#dinamicViewScale").val(jQuery("#viewScale").val());

  var obj = jQuery("#li--" + appcode);
  obj.click();
}

function getIndicatorsView(view) {
  var appcode = jQuery("#appcode").val();
  jQuery("#newblock").val("reload");
  jQuery("#dinamicMonthsearch").val(jQuery("#monthsearch").val());
  jQuery("#dinamicViewScale").val(jQuery("#viewScale").val());

  var obj = jQuery("#li--" + appcode);
  obj.click();
}

function validateCalculate() {
  var validate = true,
    contentBody = jQuery("#content-body");
  contentBody.find(".selectboxscore").each(function () {
    if (jQuery(this).val() == "" || jQuery(this).val() == "undefined") {
      alert(alert_arr.SELECT_ELEMENT_INDICATOR);
      jQuery(this).focus();
      validate = false;
      return false;
    }
  });

  if (validate == false) {
    return false;
  }

  contentBody.find(".selectoperation").each(function () {
    if (jQuery(this).val() == "" || jQuery(this).val() == "undefined") {
      alert(alert_arr.SELECT_ELEMENT_OPERATION);
      jQuery(this).focus();
      validate = false;
      return false;
    }
  });

  return validate != false;
}

function deleteCalc(id) {
  var idop = jQuery("#idoperation" + id).attr("idop");
  new Ajax.Request("index.php", {
    queue: { position: "end", scope: "command" },
    method: "post",
    postBody:
      "module=indicatorspanel&action=indicatorspanelAjax&file=DeleteFieldBoxScore&recordop=" +
      idop,
    onComplete: function (response) {

      if (response.responseText == "delete_on") {
        alert(alert_arr.delete_on);
        var appcode = jQuery("#appcode").val();
        jQuery("#newblock").val("reload");
        jQuery("#dinamicMonthsearch").val(jQuery("#monthsearch").val());
        jQuery("#dinamicViewScale").val(jQuery("#viewScale").val());

        var obj = jQuery("#li--" + appcode);
        obj.click();
      } else {
        alert(response.responseText);
      }
    },
  });
}

function callDeleteBlock(idBlock, numBox, numCalc) {
  var record = jQuery("#record").val();
  var appcode = jQuery("#appcode").val();
  var view = jQuery("#viewScale").val();

  var deleteBlock = false;
  if (numBox > 0 || numCalc > 0) {
    if (confirm(alert_arr.DELETE_BLOCK)) {
      deleteBlock = true;
    }
  } else {
    deleteBlock = true;
  }

  if (deleteBlock) {
    new Ajax.Request("index.php", {
      queue: { position: "end", scope: "command" },
      method: "post",
      postBody:
        "module=indicatorspanel&action=indicatorspanelAjax&file=SaveBlockBoxScore&mode=delete" +
        "&record=" +
        record +
        "&type=" +
        idBlock,
      onComplete: function (response) {
        if (response.responseText == "success") {
          jQuery("#crearblock").removeClass("in").hide();
          jQuery(".md-overlay").css({ opacity: 0.0, visibility: "hidden" });
          jQuery("#newblock").val("reload");
          jQuery("#dinamicViewScale").val(view);
          var obj = jQuery("#li--" + appcode);
          obj.click();
        } else {
          alert(alert_arr.ERROR);
        }
      },
    });
  }
}

function selectAllOperator() {
  var operator = jQuery("#operator_0").val();
  if (jQuery("#targetmonth_0").val() == "all") {
    jQuery("#all_operator").val(operator);
  }
}
