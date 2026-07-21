(function (jQuery) {
  var wizard,
    availableData = null,
    locked = 0,
    selectedModuleName = null,
    selectedRelatedModuleNames = null,
    selectedReportType = null,
    selectedReportId = null,
    selectedReportData = null,
    totalConditionGroups = -1,
    tabInPage = "",
    idModalWizard = "",
    isSaveModal = false,
    reloadData = true;

  // Private functions
  var getAvailableData = function () {
    var moduleName = jQuery("#modulename" + idModalWizard).val(),
      relatedModuleOptions,
      relatedModuleNames,
      reportType,
      arguments,
      i,
      n;

    if (
      moduleName === undefined ||
      moduleName === null ||
      moduleName.trim() === ""
    ) {
      return;
    }

    reportType = jQuery("#reporttype" + idModalWizard).val();
    relatedModuleOptions = jQuery("#relatedmodulenames" + idModalWizard).find(
      "option:selected",
    );
    relatedModuleNames = [];
    n = relatedModuleOptions.length;
    for (i = 0; i < n; i += 1) {
      relatedModuleNames.push(jQuery(relatedModuleOptions[i]).val());
    }

    if (
      moduleName === selectedModuleName &&
      reportType === selectedReportType &&
      jQuery.isArray(selectedRelatedModuleNames) &&
      relatedModuleOptions.length === selectedRelatedModuleNames.length &&
      relatedModuleNames.sort().toString() ===
        selectedRelatedModuleNames.sort().toString()
    ) {
      return;
    }

    arguments = [
      "module=Reports",
      "action=GetAvailableData",
      "Ajax=true",
      "modulename=" + encodeURIComponent(moduleName),
    ];
    if (relatedModuleNames.length > 0) {
      n = relatedModuleNames.length;
      for (i = 0; i < n; i += 1) {
        arguments.push("relatedmodulenames[]=" + relatedModuleNames[i]);
      }
    }

    jQuery
      .ajax("index.php", {
        data: arguments.join("&"),
        dataType: "json",
        method: "get",
      })
      .done(function (response) {
        var relatedModules = jQuery("#relatedmodulenames" + idModalWizard),
          relatedModuleOptions = relatedModules.find("option:selected"),
          n = relatedModuleOptions.length,
          i;

        selectedModuleName = jQuery("#modulename" + idModalWizard).val();
        selectedReportType = jQuery("#reporttype" + idModalWizard).val();
        if (n > 0) {
          selectedRelatedModuleNames = [];
          for (i = 0; i < n; i += 1) {
            selectedRelatedModuleNames.push(
              jQuery(relatedModuleOptions[i]).val(),
            );
          }
        } else {
          selectedRelatedModuleNames = null;
        }

        if (response && !jQuery.isEmptyObject(response)) {
          availableData = response;
          setColumns(response["availablecolumns"]);
          setGroupings(response["availablecolumns"]);
          setTotalColumns(response["totalcolumns"]);
          setStandardFilterColumns(response["standardfiltercolumns"]);
          setAdvancedFilterColumns(response["availablecolumns"]);
          setSelectedColumns();
        } else {
          availableData = null;
          setColumns(null);
          setGroupings(null);
          setTotalColumns(null);
          setStandardFilterColumns(null);
          setAdvancedFilterColumns(null);
        }
      })
      .fail(function (jQueryResponse) {
        alert(jQueryResponse.responseText);
      });
  };

  var getConditionId = function (conditionGroup) {
    var conditions = conditionGroup.find(".condition"),
      n = conditions.length,
      conditionId = 0,
      condition,
      i;
    if (n > 0) {
      for (i = 0; i < n; i += 1) {
        condition = jQuery(conditions[i]);
        if (parseInt(condition.attr("data-id")) < conditionId) {
          conditionId = parseInt(condition.attr("data-id"));
        }
      }
    }
    return conditionId - 1;
  };

  var getOptGroup = function (select, optGroupClass, optGroupLabel) {
    var optGroups = select.find("." + optGroupClass),
      optGroup;
    if (optGroups.length === 0) {
      select.append(
        '<optgroup label="' +
          optGroupLabel +
          '" class="' +
          optGroupClass +
          '"></optgroup>',
      );
      optGroup = select.find("." + optGroupClass);
    } else {
      optGroup = jQuery(optGroups[0]);
    }
    return optGroup;
  };

  var init = function () {
    wizard = jQuery("#report-wizard").wizard({
      backdrop: "static",
      showCancel: true,
      width: "1024",
      buttons: {
        cancelText: "Cancelar",
        nextText: "Siguiente →",
        backText: "← Atrás",
        submitText: "Guardar",
        submittingText: "Guardando...",
      },
    });
    wizard.cards["general"].on("validate", validateGeneralCard);
    wizard.cards["columns"]
      .on("validate", validateColumnsCard)
      .on("selected", getAvailableData);
    wizard.cards["filters"].on("validate", validateFiltersCard);
    wizard.cards["sharing"].on("validate", validateSharingCard);
    wizard.cards["schedule"].on("validate", validateScheduleCard);
    wizard.on("submit", submitWizard);
    wizard.on("closed", function () {
      if (isSaveModal) {
        if (tabInPage === "") {
          window.location.reload();
        } else {
          var myUrl = location.href;
          if (myUrl.indexOf("&tab") === -1) {
            location.href = myUrl + tabInPage;
          } else {
            window.location.reload();
          }
        }
      } else {
        var shareMembers = jQuery("#share-selected-members" + idModalWizard),
          visibility = jQuery("#visibility" + idModalWizard),
          btnReomveMembers = jQuery("#sharedwith").find("button").eq(1);
        jQuery("#availablecolumns" + idModalWizard).empty();
        jQuery("#selectedcolumns" + idModalWizard).empty();
        jQuery(".condition-groups").empty();
        jQuery("#standardfiltercolumn" + idModalWizard).val("");
        jQuery("#standardfilterperiod" + idModalWizard).val("");
        if (visibility.val() === "Shared") {
          shareMembers.find("option").prop("selected", true);
          removeMembers(btnReomveMembers);
          shareMembers.empty();
          visibility.val("");
          onChangeVisibilityHandler(shareMembers);
        }
      }
    });

    jQuery(".date-field").datepicker({
      format: "yyyy-mm-dd",
      language: "es",
      weekStart: 1,
    });
    jQuery(".time-field").timepicker({
      disableFocus: false,
      minuteStep: 5,
      showMeridian: false,
      showWidget: true,
    });
    
    // Event handler delegado para botones de eliminar campos de agrupación
    jQuery(document).on('click', '.remove-grouping-field', function() {
      removeGroupingField(this);
    });
  };

  var setAdvancedFilterColumn = function (columns, element) {
    var filterColumnLabel,
      optionsGroup,
      fieldKey,
      selectedColumnns = jQuery("#selectedcolumns" + idModalWizard);

    element.empty();
    if (!columns) {
      return;
    }

    element.append('<option value=""></option>');
    for (filterColumnLabel in columns) {
      if (!columns.hasOwnProperty(filterColumnLabel)) {
        continue;
      }
      element.append('<optgroup label="' + filterColumnLabel + '"></optgroup>');
      optionsGroup = element.find(
        'optgroup[label="' + filterColumnLabel + '"]',
      );
      for (fieldKey in columns[filterColumnLabel]) {
        if (!columns[filterColumnLabel].hasOwnProperty(fieldKey)) {
          continue;
        }
        columnParts = fieldKey.split(":");
        if (columnParts[0] === "vtiger_subfields_values") {
          swContinue = true;
          selectedColumnns.find("option").each(function () {
            if (jQuery(this).val() === fieldKey) {
              swContinue = false;
            }
          });
          if (swContinue) {
            continue;
          }
        }
        optionsGroup.append(
          '<option value="' +
            fieldKey +
            '">' +
            columns[filterColumnLabel][fieldKey] +
            "</option>",
        );
      }
    }
  };

  var setAdvancedFilterColumns = function (columns) {
    var filterColumns = jQuery(".advanced-filter-column"),
      filterColumnLabel,
      optionsGroup,
      fieldKey;

    filterColumns.empty();
    if (!columns) {
      return;
    }

    filterColumns.append('<option value=""></option>');
    for (filterColumnLabel in columns) {
      if (!columns.hasOwnProperty(filterColumnLabel)) {
        continue;
      }
      filterColumns.append(
        '<optgroup label="' + filterColumnLabel + '"></optgroup>',
      );
      optionsGroup = filterColumns.find(
        'optgroup[label="' + filterColumnLabel + '"]',
      );
      for (fieldKey in columns[filterColumnLabel]) {
        if (!columns[filterColumnLabel].hasOwnProperty(fieldKey)) {
          continue;
        }
        optionsGroup.append(
          '<option value="' +
            fieldKey +
            '">' +
            columns[filterColumnLabel][fieldKey] +
            "</option>",
        );
      }
    }
  };

  var setApplicationCodes = function (applicationCodes) {
    var select = jQuery("#applicationcodes" + idModalWizard),
      options = select.find("option"),
      option,
      i,
      n;

    if (
      applicationCodes === undefined ||
      applicationCodes === null ||
      !jQuery.isArray(applicationCodes) ||
      applicationCodes.length === 0
    ) {
      return;
    }

    n = options.length;
    for (i = 0; i < n; i += 1) {
      option = jQuery(options[i]);
      option.prop(
        "selected",
        jQuery.inArray(option.val(), applicationCodes) !== -1,
      );
    }
  };

  var setColumns = function (columns) {
    var availableColumns = jQuery("#availablecolumns" + idModalWizard),
      availableColumnModuleLabel,
      optionsGroup,
      fieldKey;

    availableColumns.empty();
    jQuery("#selectedcolumns" + idModalWizard).empty();

    if (!columns) {
      return;
    }

    for (availableColumnModuleLabel in columns) {
      if (!columns.hasOwnProperty(availableColumnModuleLabel)) {
        continue;
      }
      availableColumns.append(
        '<optgroup label="' + availableColumnModuleLabel + '"></optgroup>',
      );
      optionsGroup = availableColumns.find(
        'optgroup[label="' + availableColumnModuleLabel + '"]',
      );
      for (fieldKey in columns[availableColumnModuleLabel]) {
        if (!columns[availableColumnModuleLabel].hasOwnProperty(fieldKey)) {
          continue;
        }
        optionsGroup.append(
          '<option value="' +
            fieldKey +
            '">' +
            columns[availableColumnModuleLabel][fieldKey] +
            "</option>",
        );
      }
    }
  };

  var setGroupings = function (columns) {
    // Esta función ya no se usa directamente, se llama updateGroupingOptions
    updateGroupingOptions();
  };

  var updateGroupingOptions = function () {
    var groupings = jQuery(".grouping-columns"),
      selectedColumnsOptions = jQuery("#selectedcolumns" + idModalWizard).find("option"),
      i, n, optionValue, optionText, currentValues = [];

    // Guardar valores actuales seleccionados
    groupings.each(function() {
      var val = jQuery(this).val();
      if (val && val !== '') {
        currentValues.push(val);
      }
    });

    // Limpiar todos los dropdowns de agrupación
    groupings.empty().append('<option value="">Ninguno</option>');

    // Poblar con las columnas seleccionadas
    n = selectedColumnsOptions.length;
    for (i = 0; i < n; i += 1) {
      optionValue = jQuery(selectedColumnsOptions[i]).val();
      optionText = jQuery(selectedColumnsOptions[i]).text();
      groupings.append(
        '<option value="' + optionValue + '">' + optionText + '</option>'
      );
    }

    // Restaurar valores seleccionados si aún existen
    groupings.each(function(index) {
      if (currentValues[index]) {
        var option = jQuery(this).find('option[value="' + currentValues[index] + '"]');
        if (option.length > 0) {
          jQuery(this).val(currentValues[index]);
        }
      }
    });
  };

  var setSelectedColumns = function () {
    var i, j, m, n, group, condition;
    if (!selectedReportData) {
      return;
    }

    if (jQuery.isArray(selectedReportData["columns"])) {
      n = selectedReportData["columns"].length;
      for (i = 0; i < n; i += 1) {
        jQuery("#availablecolumns" + idModalWizard)
          .find('option[value="' + selectedReportData["columns"][i] + '"]')
          .attr("selected", "selected");
        addColumn();
      }
    }
    // Cargar campos de agrupación dinámicos (Group1-Group10)
    // Primero cargar los 3 primeros campos (compatibilidad legacy)
    if (selectedReportData["firstgrouping"]) {
      jQuery("#Group1" + idModalWizard).val(
        selectedReportData["firstgrouping"],
      );
    }
    if (selectedReportData["firstsorting"]) {
      jQuery("#Sort1" + idModalWizard).val(selectedReportData["firstsorting"]);
    }
    if (selectedReportData["secondgrouping"]) {
      jQuery("#Group2" + idModalWizard).val(
        selectedReportData["secondgrouping"],
      );
    }
    if (selectedReportData["secondsorting"]) {
      jQuery("#Sort2" + idModalWizard).val(selectedReportData["secondsorting"]);
    }
    if (selectedReportData["thirdgrouping"]) {
      jQuery("#Group3" + idModalWizard).val(
        selectedReportData["thirdgrouping"],
      );
    }
    if (selectedReportData["thirdsorting"]) {
      jQuery("#Sort3" + idModalWizard).val(selectedReportData["thirdsorting"]);
    }
    
    // Cargar campos de agrupación adicionales (4-10) si existen
    var tbody = jQuery('#grouping-fields-body');
    var currentRows = tbody.find('tr').length;
    
    for (var groupIndex = 4; groupIndex <= 10; groupIndex++) {
      var groupKey = 'group' + groupIndex;
      var sortKey = 'sort' + groupIndex;
      
      // Verificar si existe este campo de agrupación en los datos
      if (selectedReportData[groupKey] && selectedReportData[groupKey] !== 'none') {
        // Si la fila no existe, crearla
        if (groupIndex > currentRows) {
          addGroupingField();
          currentRows++;
        }
        
        // Establecer los valores
        jQuery("#Group" + groupIndex + idModalWizard).val(selectedReportData[groupKey]);
        if (selectedReportData[sortKey]) {
          jQuery("#Sort" + groupIndex + idModalWizard).val(selectedReportData[sortKey]);
        }
      }
    }

    if (jQuery.isArray(selectedReportData["totalcolumns"])) {
      n = selectedReportData["totalcolumns"].length;
      for (i = 0; i < n; i += 1) {
        jQuery(
          '.total-column[value="' +
            selectedReportData["totalcolumns"][i] +
            '"]',
        ).attr("checked", "checked");
      }
    }

    if (selectedReportData["standardfilter"]) {
      jQuery("#standardfiltercolumn" + idModalWizard).val(
        selectedReportData["standardfilter"]["column"],
      );
      jQuery("#standardfilterperiod" + idModalWizard).val(
        selectedReportData["standardfilter"]["period"],
      );
      jQuery("#standardfilterfrom" + idModalWizard).val(
        selectedReportData["standardfilter"]["from"],
      );
      jQuery("#standardfilterto" + idModalWizard).val(
        selectedReportData["standardfilter"]["to"],
      );
    }
    if (jQuery.isArray(selectedReportData["advancedfilters"])) {
      n = selectedReportData["advancedfilters"].length;
      for (i = 0; i < n; i += 1) {
        addConditionGroup(selectedReportData["advancedfilters"][i]["groupid"]);
        group = jQuery(
          '.condition-group[data-id="' +
            selectedReportData["advancedfilters"][i]["groupid"] +
            '"]',
        );
        group.find(".conditions").empty();
        m = selectedReportData["advancedfilters"][i]["conditions"].length;
        for (j = 0; j < m; j += 1) {
          addCondition(group.find(".condition-group-footer .btn"));
          condition = group.find(".condition:last");
          condition
            .find(".advanced-filter-column")
            .val(
              selectedReportData["advancedfilters"][i]["conditions"][j][
                "columnname"
              ],
            );
          condition
            .find(".operator")
            .val(
              selectedReportData["advancedfilters"][i]["conditions"][j][
                "operator"
              ],
            );
          condition
            .find(".value")
            .val(
              selectedReportData["advancedfilters"][i]["conditions"][j][
                "value"
              ],
            );
          if (
            selectedReportData["advancedfilters"][i]["conditions"][j]["glue"]
          ) {
            condition
              .find(".glue")
              .val(
                selectedReportData["advancedfilters"][i]["conditions"][j][
                  "glue"
                ],
              );
          }
        }
        if (selectedReportData["advancedfilters"][i]["glue"]) {
          group
            .next(".condition-group-glue")
            .find(".glue")
            .val(selectedReportData["advancedfilters"][i]["glue"]);
        }
      }
    }

    if (selectedReportData["visibility"]) {
      jQuery("#visibility" + idModalWizard)
        .val(selectedReportData["visibility"])
        .trigger("change");
    }
    if (selectedReportData["sharewith"]) {
      n = selectedReportData["sharewith"].length;
      for (i = 0; i < n; i += 1) {
        jQuery("#share-available-members" + idModalWizard)
          .find('option[value="' + selectedReportData["sharewith"][i] + '"]')
          .attr("selected", "selected");
      }
      addMembers(jQuery("#sharedwith").find(".columns-actions .btn:first"));
    }

    if (selectedReportData["schedule"]) {
      jQuery("#scheduled" + idModalWizard)
        .val("yes")
        .trigger("change");
      jQuery("#schedule-frequency")
        .val(selectedReportData["schedule"]["frequency"])
        .trigger("change");
      jQuery("#schedule-weekday").val(
        selectedReportData["schedule"]["weekday"],
      );
      jQuery("#schedule-day").val(selectedReportData["schedule"]["day"]);
      jQuery("#schedule-month").val(selectedReportData["schedule"]["month"]);
      jQuery("#schedule-time").val(selectedReportData["schedule"]["time"]);
      jQuery("#schedule-format").val(selectedReportData["schedule"]["format"]);

      n = selectedReportData["schedule"]["recipients"].length;
      for (i = 0; i < n; i += 1) {
        jQuery("#schedule-sendto-available-members")
          .find(
            'option[value="' +
              selectedReportData["schedule"]["recipients"][i] +
              '"]',
          )
          .attr("selected", "selected");
      }
      addMembers(
        jQuery("#schedule-sendto").find(".columns-actions .btn:first"),
      );
    }
  };

  var setSelectedReportData = function (report) {
    jQuery("#folderid" + idModalWizard).val(report["folderid"]);
    jQuery("#reporttype" + idModalWizard)
      .val(report["type"])
      .trigger("change");
    jQuery("#reportname" + idModalWizard).val(report["name"]);
    jQuery("#reportdescription" + idModalWizard).val(report["description"]);
    jQuery("#modulename" + idModalWizard)
      .val(report["modulename"])
      .trigger("change");
    locked = report["locked"];
    setRelatedModules(
      "#modulename" + idModalWizard,
      report["relatedmodulenames"],
    );
    setApplicationCodes(report["applicationcodes"]);
  };

  var setStandardFilterColumns = function (columns) {
    var standardFilterColumns = jQuery("#standardfiltercolumn" + idModalWizard),
      standardFilterColumnModuleLabel,
      optionsGroup,
      fieldKey;

    standardFilterColumns.empty();
    if (!columns) {
      return;
    }
    standardFilterColumns.append('<option value=""></option>');
    for (standardFilterColumnModuleLabel in columns) {
      if (!columns.hasOwnProperty(standardFilterColumnModuleLabel)) {
        continue;
      }
      standardFilterColumns.append(
        '<optgroup label="' + standardFilterColumnModuleLabel + '"></optgroup>',
      );
      optionsGroup = standardFilterColumns.find(
        'optgroup[label="' + standardFilterColumnModuleLabel + '"]',
      );
      for (fieldKey in columns[standardFilterColumnModuleLabel]) {
        if (
          !columns[standardFilterColumnModuleLabel].hasOwnProperty(fieldKey)
        ) {
          continue;
        }
        optionsGroup.append(
          '<option value="' +
            fieldKey +
            '">' +
            columns[standardFilterColumnModuleLabel][fieldKey] +
            "</option>",
        );
      }
    }
  };

  var setTotalColumns = function (columns) {
    var totalsTable = jQuery("#totals").find("table.table > tbody"),
      moduleLabel,
      fieldLabel,
      row,
      i,
      n;

    totalsTable.empty();
    if (!columns || jQuery.isEmptyObject(columns)) {
      totalsTable.append(
        '<tr><td colspan="5" class="text-center">No hay columnas disponibles para totalizar</td></tr>',
      );
      return;
    }

    for (moduleLabel in columns) {
      if (!columns.hasOwnProperty(moduleLabel)) {
        continue;
      }
      for (fieldLabel in columns[moduleLabel]) {
        if (!columns[moduleLabel].hasOwnProperty(fieldLabel)) {
          continue;
        }
        row = jQuery(
          "<tr><td>" + moduleLabel + " - " + fieldLabel + "</td></tr>",
        );
        n = columns[moduleLabel][fieldLabel].length;
        for (i = 0; i < n; i += 1) {
          row.append(
            '<td class="text-center"><input type="checkbox" value="' +
              columns[moduleLabel][fieldLabel][i] +
              '" class="total-column" placeholder="" /></td>',
          );
        }
        totalsTable.append(row);
      }
    }
  };

  var submitWizard = function () {
    var applicationCodes = jQuery("#applicationcodes" + idModalWizard).val(),
      folderId = jQuery("#folderid" + idModalWizard).val(),
      isScheduled = jQuery("#scheduled" + idModalWizard).val(),
      moduleName = jQuery("#modulename" + idModalWizard).val(),
      reportName = jQuery("#reportname" + idModalWizard).val(),
      reportType = jQuery("#reporttype" + idModalWizard).val(),
      reportDescription = jQuery("#reportdescription" + idModalWizard).val(),
      scheduleDay = jQuery("#schedule-day" + idModalWizard).val(),
      scheduleFormat = jQuery("#schedule-format" + idModalWizard).val(),
      scheduleFrequency = jQuery("#schedule-frequency" + idModalWizard).val(),
      scheduleMonth = jQuery("#schedule-month" + idModalWizard).val(),
      scheduleTime = jQuery("#schedule-time" + idModalWizard).val(),
      scheduleWeekday = jQuery("#schedule-weekday" + idModalWizard).val(),
      standardFilterColumn = jQuery(
        "#standardfiltercolumn" + idModalWizard,
      ).val(),
      standardFilterFrom = jQuery("#standardfilterfrom" + idModalWizard).val(),
      standardFilterPeriod = jQuery(
        "#standardfilterperiod" + idModalWizard,
      ).val(),
      standardFilterTo = jQuery("#standardfilterto" + idModalWizard).val(),
      visibility = jQuery("#visibility" + idModalWizard).val(),
      advancedFiltersConditionGroups = jQuery(".condition-group"),
      relatedModuleOptions = jQuery("#relatedmodulenames" + idModalWizard).find(
        "option:selected",
      ),
      selectedColumnsOptions = jQuery("#selectedcolumns" + idModalWizard).find(
        "option",
      ),
      selectedTotalColumns = jQuery(".total-column:checked"),
      selectedScheduleMembersOptions =
        isScheduled === "yes"
          ? jQuery("#schedule-sendto-selected-members" + idModalWizard).find(
              "option",
            )
          : [],
      selectedSharingMembersOptions =
        visibility === "Shared"
          ? jQuery("#share-selected-members" + idModalWizard).find("option")
          : [],
      advancedFiltersConditionGroup,
      advancedFiltersConditions,
      advancedFiltersCondition,
      arguments,
      i,
      j,
      m,
      n,
      groupId,
      conditionId;

    arguments = [
      "module=Reports",
      "action=Save",
      "Ajax=true",
      "description=" + encodeURIComponent(reportDescription),
      "folderid=" + encodeURIComponent(folderId),
      "modulename=" + encodeURIComponent(moduleName),
      "name=" + encodeURIComponent(reportName),
      "type=" + encodeURIComponent(reportType),
      "locked=" + locked,
    ];

    n = applicationCodes.length;
    for (i = 0; i < n; i += 1) {
      arguments.push(
        "applicationcodes[]=" + encodeURIComponent(applicationCodes[i]),
      );
    }

    if (selectedReportId) {
      arguments.push("record=" + encodeURIComponent(selectedReportId));
    }

    if (reportType === "summary") {
      for (i = 1; i <= 10; i++) {
        var groupField = jQuery('#Group' + i + idModalWizard).val();
        var sortField = jQuery('#Sort' + i + idModalWizard).val();
        if (groupField && groupField.trim() !== '' && groupField !== 'none') {
          arguments.push('Group' + i + '=' + encodeURIComponent(groupField));
          arguments.push('Sort' + i + '=' + encodeURIComponent(sortField || 'Ascending'));
        }
      }
    }

    if (standardFilterColumn.trim() !== "") {
      arguments.push(
        "standardfiltercolumn=" + encodeURIComponent(standardFilterColumn),
      );
      arguments.push(
        "standardfilterfrom=" + encodeURIComponent(standardFilterFrom),
      );
      arguments.push(
        "standardfilterperiod=" + encodeURIComponent(standardFilterPeriod),
      );
      arguments.push(
        "standardfilterto=" + encodeURIComponent(standardFilterTo),
      );
    }

    n = relatedModuleOptions.length;
    for (i = 0; i < n; i += 1) {
      arguments.push(
        "relatedmodulenames[]=" +
          encodeURIComponent(jQuery(relatedModuleOptions[i]).val()),
      );
    }

    n = selectedColumnsOptions.length;
    for (i = 0; i < n; i += 1) {
      arguments.push(
        "columns[]=" +
          encodeURIComponent(jQuery(selectedColumnsOptions[i]).val()),
      );
    }

    n = selectedTotalColumns.length;
    for (i = 0; i < n; i += 1) {
      arguments.push(
        "totalcolumns[]=" +
          encodeURIComponent(jQuery(selectedTotalColumns[i]).val()),
      );
    }

    n = advancedFiltersConditionGroups.length;
    for (i = 0; i < n; i += 1) {
      advancedFiltersConditionGroup = jQuery(advancedFiltersConditionGroups[i]);
      groupId = advancedFiltersConditionGroup.attr("data-id");
      advancedFiltersConditions =
        advancedFiltersConditionGroup.find(".condition");
      m = advancedFiltersConditions.length;
      for (j = 0; j < m; j += 1) {
        advancedFiltersCondition = jQuery(advancedFiltersConditions[j]);
        conditionId = advancedFiltersCondition.attr("data-id");
        arguments.push(
          "advancedfilters[" +
            groupId +
            "][conditions][" +
            conditionId +
            "][columnname]=" +
            encodeURIComponent(
              advancedFiltersCondition.find(".advanced-filter-column").val(),
            ),
        );
        arguments.push(
          "advancedfilters[" +
            groupId +
            "][conditions][" +
            conditionId +
            "][operator]=" +
            encodeURIComponent(
              advancedFiltersCondition.find(".operator").val(),
            ),
        );
        arguments.push(
          "advancedfilters[" +
            groupId +
            "][conditions][" +
            conditionId +
            "][value]=" +
            encodeURIComponent(advancedFiltersCondition.find(".value").val()),
        );
        arguments.push(
          "advancedfilters[" +
            groupId +
            "][conditions][" +
            conditionId +
            "][glue]=" +
            encodeURIComponent(advancedFiltersCondition.find(".glue").val()),
        );
      }
      arguments.push(
        "advancedfilters[" +
          groupId +
          "][glue]=" +
          encodeURIComponent(
            advancedFiltersConditionGroup
              .next(".condition-group-glue")
              .find(".glue")
              .val(),
          ),
      );
    }

    if (visibility === "Shared") {
      arguments.push("visibility=" + encodeURIComponent(visibility));
      n = selectedSharingMembersOptions.length;
      for (i = 0; i < n; i += 1) {
        arguments.push(
          "sharewith[]=" +
            encodeURIComponent(jQuery(selectedSharingMembersOptions[i]).val()),
        );
      }
    }

    if (isScheduled === "yes") {
      arguments.push("isscheduled=true");
      arguments.push("scheduleformat=" + encodeURIComponent(scheduleFormat));
      arguments.push(
        "schedulefrequency=" + encodeURIComponent(scheduleFrequency),
      );
      arguments.push("scheduletime=" + encodeURIComponent(scheduleTime));
      if (scheduleFrequency === "3" || scheduleFrequency === "4") {
        arguments.push(
          "scheduleweekday=" + encodeURIComponent(scheduleWeekday),
        );
      } else if (scheduleFrequency === "5") {
        arguments.push("scheduleday=" + encodeURIComponent(scheduleDay));
      } else if (scheduleFrequency === "6") {
        arguments.push("scheduleday=" + encodeURIComponent(scheduleDay));
        arguments.push("schedulemonth=" + encodeURIComponent(scheduleMonth));
      }

      n = selectedScheduleMembersOptions.length;
      for (i = 0; i < n; i += 1) {
        arguments.push(
          "schedulerecipients[]=" +
            encodeURIComponent(jQuery(selectedScheduleMembersOptions[i]).val()),
        );
      }
    }

    jQuery
      .ajax("index.php", {
        data: arguments.join("&"),
        dataType: "text",
        method: "post",
      })
      .done(function () {
        wizard.submitSuccess();
        wizard.hideButtons();
        isSaveModal = true;
      })
      .fail(function (jQueryResponse) {
        wizard.submitError();
        wizard.hideButtons();
      });
  };

  var validateColumnsCard = function (card) {
    var field = card.el.find("#selectedcolumns" + idModalWizard),
      options = field.find("option");

    card.wizard.hidePopovers();
    if (options.length === 0) {
      card.wizard.errorPopover(field, "Selecciona las columnas del informe");
      return false;
    }
    return true;
  };

  var validateFiltersCard = function (card) {
    var field,
      value,
      groups,
      group,
      conditions,
      condition,
      i,
      j,
      m,
      n,
      columnParts,
      column;
    var selectedColumnns = jQuery("#selectedcolumns" + idModalWizard),
      hasGridField = false;

    card.wizard.hidePopovers();

    field = card.el.find("#standardfiltercolumn" + idModalWizard);
    value = field.val();
    if (value && value.trim() !== "") {
      field = card.el.find("#standardfilterperiod" + idModalWizard);
      value = field.val();
      if (value === undefined || value === null || value.trim() === "") {
        card.wizard.errorPopover(field, "Selecciona el período");
        return false;
      }
    }

    groups = card.el.find("#advancedfilters .condition-group");
    if (groups.length === 0) {
      return true;
    }

    n = groups.length;
    for (i = 0; i < n; i += 1) {
      group = jQuery(groups[i]);
      conditions = group.find(".condition");
      if (conditions.length === 0) {
        card.wizard.errorPopover(
          group,
          "El grupo de condiciones no puede estar vacío",
        );
        return false;
      }

      m = conditions.length;
      for (j = 0; j < m; j += 1) {
        hasGridField = false;
        condition = jQuery(conditions[j]);

        field = condition.find(".advanced-filter-column");
        value = field.val();
        columnParts = value.split(":");
        if (value === undefined || value === null || value.trim() === "") {
          card.wizard.errorPopover(field, "Selecciona la columna");
          return false;
        } else if (columnParts[0] === "vtiger_subfields_values") {
          selectedColumnns.find("option").each(function () {
            if (jQuery(this).val() === value) {
              hasGridField = true;
            }
          });
          if (!hasGridField) {
            column = columnParts[1].split("@");
            card.wizard.errorPopover(
              field,
              "Columna tabla '" + column[0] + "' no seleccionada",
            );
            return false;
          }
        }

        field = condition.find(".operator");
        value = field.val();
        if (value === undefined || value === null || value.trim() === "") {
          card.wizard.errorPopover(
            field,
            "Selecciona el operador de comparación",
          );
          return false;
        }
      }
    }

    return true;
  };

  var validateGeneralCard = function (card) {
    var field, value;

    card.wizard.hidePopovers();

    field = card.el.find("#folderid" + idModalWizard);
    value = field.val();
    if (value === undefined || value === null || value.trim() === "") {
      card.wizard.errorPopover(field, "Selecciona la carpeta");
      return false;
    }

    field = card.el.find("#reportname" + idModalWizard);
    value = field.val();
    if (value === undefined || value === null || value.trim() === "") {
      card.wizard.errorPopover(field, "Introduce el nombre del reporte");
      return false;
    }

    field = card.el.find("#modulename" + idModalWizard);
    value = field.val();
    if (value === undefined || value === null || value.trim() === "") {
      card.wizard.errorPopover(field, "Selecciona el módulo");
      return false;
    }

    if (card.el.find("#is-instance").length > 0) {
      field = card.el.find("#applicationcodes" + idModalWizard);
      value = field.val();
      if (value === undefined || value === null || value.trim() === "") {
        card.wizard.errorPopover(field, "Selecciona las aplicaciones");
        return false;
      }
    }

    return true;
  };

  var validateScheduleCard = function (card) {
    var frequency, field, value;

    card.wizard.hidePopovers();

    field = card.el.find("#scheduled" + idModalWizard);
    value = field.val();
    if (value === "no") {
      return true;
    }

    field = card.el.find("#schedule-frequency" + idModalWizard);
    frequency = field.val();
    if (
      frequency === undefined ||
      frequency === null ||
      frequency.trim() === ""
    ) {
      card.wizard.errorPopover(field, "Selecciona la frecuencia de envío");
      return false;
    }

    if (frequency === "3" || frequency === "4") {
      field = card.el.find("#schedule-weekday" + idModalWizard);
      value = field.val();
      if (value === undefined || value === null || value.trim() === "") {
        card.wizard.errorPopover(field, "Selecciona el día de la semana");
        return false;
      }
    }

    if (frequency === "5" || frequency === "6") {
      field = card.el.find("#schedule-day" + idModalWizard);
      value = field.val();
      if (value === undefined || value === null || value.trim() === "") {
        card.wizard.errorPopover(field, "Selecciona el día de envío");
        return false;
      }
    }

    if (frequency === "6") {
      field = card.el.find("#schedule-month" + idModalWizard);
      value = field.val();
      if (value === undefined || value === null || value.trim() === "") {
        card.wizard.errorPopover(field, "Selecciona el mes de envío");
        return false;
      }
    }

    field = card.el.find("#schedule-time" + idModalWizard);
    value = field.val();
    if (value === undefined || value === null || value.trim() === "") {
      card.wizard.errorPopover(field, "Selecciona la hora de envío");
      return false;
    }

    field = card.el.find("#schedule-sendto-selected-members" + idModalWizard);
    value = field.find("option");
    if (value.length === 0) {
      card.wizard.errorPopover(
        field,
        "Selecciona los destinatarios del informe",
      );
      return false;
    }

    return true;
  };

  var validateSharingCard = function (card) {
    var field, value;

    card.wizard.hidePopovers();

    field = card.el.find("#visibility" + idModalWizard);
    value = field.val();
    if (value !== "Shared") {
      return true;
    }

    field = card.el.find("#share-selected-members" + idModalWizard);
    value = field.find("option");
    if (value.length === 0) {
      card.wizard.errorPopover(
        field,
        "Selecciona los miembros que podrán acceder al informe",
      );
      return false;
    }

    return true;
  };

  // Public functions

  var addColumn = function () {
    var availableColumns = jQuery("#availablecolumns" + idModalWizard),
      selectedColumns = jQuery("#selectedcolumns" + idModalWizard),
      availableOptions = availableColumns.find("option:selected"),
      availableOption,
      i,
      n;

    if (availableOptions.length === 0) {
      return;
    }

    n = availableOptions.length;
    for (i = 0; i < n; i += 1) {
      availableOption = jQuery(availableOptions[i]);
      if (availableOption.attr("style")) {
        continue;
      }
      selectedColumns.append(
        '<option value="' +
          availableOption.val() +
          '">' +
          availableOption.text() +
          " (" +
          availableOption.closest("optgroup").attr("label") +
          ")</option>",
      );
      availableOption.removeAttr("selected").hide();
    }
    updateGroupingOptions();
  };

  var addColumns = function () {
    var availableColumns = jQuery("#availablecolumns" + idModalWizard),
      selectedColumns = jQuery("#selectedcolumns" + idModalWizard),
      availableOptions = availableColumns.find("option:visible"),
      availableOption,
      i,
      n;

    if (availableOptions.length === 0) {
      return;
    }

    n = availableOptions.length;
    for (i = 0; i < n; i += 1) {
      availableOption = jQuery(availableOptions[i]);
      selectedColumns.append(
        '<option value="' +
          availableOption.val() +
          '">' +
          availableOption.text() +
          " (" +
          availableOption.closest("optgroup").attr("label") +
          ")</option>",
      );
      availableOption.removeAttr("selected").hide();
    }
    updateGroupingOptions();
  };

  var addCondition = function (buttonElement) {
    var conditionGroup = jQuery(buttonElement).closest(".condition-group"),
      conditionGroupId = conditionGroup.attr("data-id"),
      conditions = conditionGroup.find(".conditions"),
      conditionId = getConditionId(conditionGroup),
      conditionTemplateHtml = jQuery("#condition-template")
        .html()
        .replace(/__GROUP_ID__/g, conditionGroupId)
        .replace(/__CONDITION_ID__/g, conditionId),
      conditionTemplate = jQuery(conditionTemplateHtml);

    setAdvancedFilterColumn(
      availableData["availablecolumns"],
      conditionTemplate.find(".advanced-filter-column"),
    );
    conditions.find(".glue:last").removeClass("hidden").removeAttr("disabled");
    conditions.append(conditionTemplate);
  };

  var addConditionGroup = function (groupId) {
    var conditionGroups = jQuery(".condition-groups"),
      key = groupId ? groupId : totalConditionGroups,
      conditionGroupTemplate = jQuery(
        jQuery("#condition-group-template")
          .html()
          .replace(/__GROUP_ID__/g, key),
      ),
      conditionTemplateHtml = jQuery("#condition-template")
        .html()
        .replace(/__GROUP_ID__/g, key)
        .replace(/__CONDITION_ID__/g, -1),
      conditionTemplate = jQuery(conditionTemplateHtml);

    setAdvancedFilterColumn(
      availableData["availablecolumns"],
      conditionTemplate.find(".advanced-filter-column"),
    );
    conditionGroupTemplate.find(".conditions").append(conditionTemplate);
    conditionGroups
      .find(".condition-group-glue:last > .glue")
      .removeClass("hidden")
      .removeAttr("disabled");
    conditionGroups.append(conditionGroupTemplate);
    if (!groupId) {
      totalConditionGroups -= 1;
    }
  };

  var addMembers = function (button) {
    var section = jQuery(button).closest(".members"),
      available = section.find(".available-members"),
      members = section.find(".selected-members"),
      options = available.find("option:selected"),
      i,
      n,
      element,
      type,
      value,
      dummy,
      optGroup;

    if (options.length === 0) {
      return;
    }

    n = options.length - 1;
    for (i = n; i >= 0; i -= 1) {
      element = jQuery(options[i]);
      dummy = element.val().split("::");
      type = dummy[0];
      value = dummy[1];
      if (type === "group") {
        optGroup = getOptGroup(members, "groups", "Grupos");
      } else if (type === "role") {
        optGroup = getOptGroup(members, "roles", "Roles");
      } else if (type === "rs") {
        optGroup = getOptGroup(members, "rs", "Roles y subordinados");
      } else {
        optGroup = getOptGroup(members, "users", "Usuarios");
      }
      optGroup.append(element);
      element.removeAttr("selected");
    }
  };

  var deleteColumn = function () {
    var availableColumns = jQuery("#availablecolumns" + idModalWizard),
      selectedColumns = jQuery("#selectedcolumns" + idModalWizard),
      selectedOptions = selectedColumns.find("option:selected"),
      selectedOption,
      i,
      n;

    if (selectedOptions.length === 0) {
      return;
    }

    n = selectedOptions.length;
    for (i = 0; i < n; i += 1) {
      selectedOption = jQuery(selectedOptions[i]);
      availableColumns
        .find('option[value="' + selectedOption.val() + '"]')
        .show();
      selectedOption.remove();
    }
    updateGroupingOptions();
  };

  var deleteColumns = function () {
    var availableColumns = jQuery("#availablecolumns" + idModalWizard),
      selectedColumns = jQuery("#selectedcolumns" + idModalWizard),
      availableOptions = availableColumns.find("option:hidden"),
      selectedOptions = selectedColumns.find("option");

    availableOptions.show();
    selectedOptions.remove();
    updateGroupingOptions();
  };

  var deleteCondition = function (buttonElement) {
    var button = jQuery(buttonElement),
      conditionGroup = button.closest(".condition-group"),
      condition = button.closest(".condition");
    if (!confirm("¿Estás seguro de borrar la condición seleccionada?")) {
      return;
    }
    condition.remove();
    conditionGroup
      .find(".glue:last")
      .addClass("hidden")
      .attr("disabled", "disabled");
  };

  var deleteConditionGroup = function (buttonElement) {
    var conditionGroup = jQuery(buttonElement).closest(
      ".condition-group-container",
    );
    if (
      !confirm("¿Estás seguro de borrar el grupo de condiciones seleccionado?")
    ) {
      return;
    }
    conditionGroup.remove();
    jQuery(".condition-groups")
      .find(".condition-group-glue:last > .glue")
      .addClass("hidden")
      .attr("disabled", "disabled");
  };

  var filterApplications = function (selectElement) {
    var select = jQuery(selectElement),
      moduleName = select.val(),
      applications = select
        .closest(".wizard-input-section")
        .find("#applicationcodes" + idModalWizard),
      applicationOptions = applications.find("option"),
      i,
      applicationOption,
      applicationModuleNames;

    if (applicationOptions.length === 0) {
      return;
    }

    for (i = 0; i < applicationOptions.length; i += 1) {
      applicationOption = jQuery(applicationOptions[i]);
      applicationModuleNames = applicationOption
        .attr("data-modules")
        .split(", ")
        .filter(function (x) {
          return x !== null && x !== undefined && x.trim() !== "";
        });
      if (
        moduleName === null ||
        moduleName === undefined ||
        moduleName.trim() === "" ||
        jQuery.inArray(moduleName, applicationModuleNames) === -1
      ) {
        applicationOption.addClass("hidden").prop("selected", false);
      } else {
        applicationOption.removeClass("hidden");
      }
    }
  };

  var moveColumnsDown = function () {
    var selectedColumns = jQuery("#selectedcolumns" + idModalWizard),
      selectedOptions = selectedColumns.find("option:selected");
    selectedOptions.last().next().after(selectedOptions);
  };

  var moveColumnsUp = function () {
    var selectedColumns = jQuery("#selectedcolumns" + idModalWizard),
      selectedOptions = selectedColumns.find("option:selected");
    selectedOptions.first().prev().before(selectedOptions);
  };

  var onChangeAdvancedFilterColumnHandler = function (select) {
    var column = jQuery(select).find("option:selected"),
      columnName = column.val(),
      columnData = columnName ? columnName.split(":") : null,
      columnType = columnData ? columnData[columnData.length - 1] : "V";
    if (jQuery.inArray(columnType, ["E", "V"]) !== -1) {
      column
        .closest(".condition")
        .find('.operator > option[data-type="text"]')
        .show();
      column
        .closest(".condition")
        .find('.operator > option[data-type="number"]')
        .hide();
    } else if (columnType === "C") {
      column
        .closest(".condition")
        .find('.operator > option[data-type="text"]')
        .hide();
      column
        .closest(".condition")
        .find('.operator > option[data-type="number"]')
        .hide();
    } else {
      column
        .closest(".condition")
        .find('.operator > option[data-type="text"]')
        .hide();
      column
        .closest(".condition")
        .find('.operator > option[data-type="number"]')
        .show();
    }
  };

  var onChangeReportTypeHandler = function (select) {
    var reportType = jQuery(select).val();
    if (reportType === "summary") {
      jQuery("#groupings-summary").show();
      jQuery("#groupings-tabular").hide();
    } else {
      jQuery("#groupings-summary").hide();
      jQuery("#groupings-tabular").show();
    }
  };

  var addGroupingField = function () {
    var tbody = jQuery('#grouping-fields-body');
    var currentRows = tbody.find('tr').length;
    var maxRows = 10;
    
    if (currentRows >= maxRows) {
      alert('No se pueden agregar más de ' + maxRows + ' campos de agrupación');
      return;
    }
    
    var newIndex = currentRows + 1;
    var newRow = jQuery('<tr data-grouping-index="' + newIndex + '"></tr>');
    
    // Columna de campo de agrupación
    var groupCell = jQuery('<td class="col-xs-12 col-md-8"></td>');
    var groupSelect = jQuery('<select id="Group' + newIndex + idModalWizard + '" name="Group' + newIndex + '" class="form-control grouping-columns" title="Agrupar por"></select>');
    
    // Poblar con las columnas seleccionadas
    var selectedColumnsOptions = jQuery("#selectedcolumns" + idModalWizard).find("option");
    groupSelect.append('<option value="">Ninguno</option>');
    selectedColumnsOptions.each(function() {
      groupSelect.append('<option value="' + jQuery(this).val() + '">' + jQuery(this).text() + '</option>');
    });
    
    groupCell.append(groupSelect);
    newRow.append(groupCell);
    
    // Columna de orden
    var sortCell = jQuery('<td class="col-xs-12 col-md-3"></td>');
    var sortSelect = jQuery('<select id="Sort' + newIndex + idModalWizard + '" name="Sort' + newIndex + '" class="form-control" title="Orden"></select>');
    sortSelect.append('<option value="Ascending">Ascendente</option>');
    sortSelect.append('<option value="Descending">Descendente</option>');
    sortCell.append(sortSelect);
    newRow.append(sortCell);
    
    // Columna de botón eliminar
    var actionCell = jQuery('<td class="col-xs-12 col-md-1"></td>');
    var deleteBtn = jQuery('<button type="button" class="btn btn-sm btn-danger remove-grouping-field" title="Eliminar campo"><i class="fa fa-trash-o"></i></button>');
    actionCell.append(deleteBtn);
    newRow.append(actionCell);
    
    tbody.append(newRow);
  };

  var removeGroupingField = function (button) {
    var row = jQuery(button).closest('tr');
    var tbody = jQuery('#grouping-fields-body');
    var currentRows = tbody.find('tr').length;
    
    if (currentRows <= 1) {
      alert('Debe mantener al menos un campo de agrupación');
      return;
    }
    
    row.remove();
    
    // Renumerar las filas restantes
    tbody.find('tr').each(function(index) {
      var newIndex = index + 1;
      jQuery(this).attr('data-grouping-index', newIndex);
      
      // Actualizar IDs de los selects
      var groupSelect = jQuery(this).find('.grouping-columns');
      var sortSelect = jQuery(this).find('select[title="Orden"]');
      
      groupSelect.attr('id', 'Group' + newIndex + idModalWizard);
      groupSelect.attr('name', 'Group' + newIndex);
      sortSelect.attr('id', 'Sort' + newIndex + idModalWizard);
      sortSelect.attr('name', 'Sort' + newIndex);
    });
  };

  var onChangeScheduleHandler = function (select) {
    var scheduled = jQuery(select).val(),
      isScheduled = scheduled === "yes";
    if (isScheduled) {
      jQuery("#schedule-data").show();
      jQuery("#schedule-format-container").show();
      jQuery("#schedule-sendto").show();
    } else {
      jQuery("#schedule-data").hide();
      jQuery("#schedule-format-container").hide();
      jQuery("#schedule-sendto").hide();
    }
  };

  var onChangeScheduleFrequencyHandler = function (select) {
    var frequency = jQuery(select).val(),
      weekdayContainer = jQuery("#schedule-weekday-container"),
      dayContainer = jQuery("#schedule-day-container"),
      monthContainer = jQuery("#schedule-month-container"),
      timeContainer = jQuery("#schedule-time-container");

    switch (frequency) {
      case "2":
        weekdayContainer.hide();
        dayContainer.hide();
        monthContainer.hide();
        timeContainer.show();
        break;
      case "3":
      case "4":
        weekdayContainer.show();
        dayContainer.hide();
        monthContainer.hide();
        timeContainer.show();
        break;
      case "5":
        weekdayContainer.hide();
        dayContainer.show();
        monthContainer.hide();
        timeContainer.show();
        break;
      case "6":
        weekdayContainer.hide();
        dayContainer.show();
        monthContainer.show();
        timeContainer.show();
        break;
      default:
        weekdayContainer.hide();
        dayContainer.hide();
        monthContainer.hide();
        timeContainer.hide();
        break;
    }
  };

  var onChangeStandardFilterPeriodHandler = function (select) {
    var period = jQuery(select).val(),
      fromElement = jQuery("#standardfilterfrom" + idModalWizard),
      toElement = jQuery("#standardfilterto" + idModalWizard),
      today = new Date(),
      from,
      to,
      monday,
      friday,
      dummy,
      quarter;
    if (period === "custom" || period === "") {
      fromElement.datepicker("update", today);
      toElement.datepicker("update", today);
      fromElement.val("");
      toElement.val("");
    } else {
      from = new Date();
      to = new Date();
      switch (period) {
        case "today":
          break;
        case "yesterday":
          from.setDate(today.getDate() - 1);
          to.setDate(today.getDate() - 1);
          break;
        case "tomorrow":
          from.setDate(today.getDate() + 1);
          to.setDate(today.getDate() + 1);
          break;
        case "thisweek":
          monday = today.getDate() - today.getDay() + 1;
          friday = monday + 4;
          from.setDate(monday);
          to.setDate(friday);
          break;
        case "lastweek":
          monday = today.getDate() - today.getDay() + 1 - 7;
          friday = monday + 4;
          from.setDate(monday);
          to.setDate(friday);
          break;
        case "nextweek":
          monday = today.getDate() - today.getDay() + 1 + 7;
          friday = monday + 4;
          from.setDate(monday);
          to.setDate(friday);
          break;
        case "thismonth":
          from = new Date(today.getFullYear(), today.getMonth(), 1);
          to = new Date(today.getFullYear(), today.getMonth() + 1, 0);
          break;
        case "lastmonth":
          from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
          to = new Date(today.getFullYear(), today.getMonth(), 0);
          break;
        case "nextmonth":
          from = new Date(today.getFullYear(), today.getMonth() + 1, 1);
          to = new Date(today.getFullYear(), today.getMonth() + 2, 0);
          break;
        case "next30days":
          from.setDate(today.getDate());
          to.setDate(today.getDate() + 30);
          break;
        case "next60days":
          from.setDate(today.getDate());
          to.setDate(today.getDate() + 60);
          break;
        case "next90days":
          from.setDate(today.getDate());
          to.setDate(today.getDate() + 90);
          break;
        case "next120days":
          from.setDate(today.getDate());
          to.setDate(today.getDate() + 90);
          break;
        case "last7days":
          from.setDate(today.getDate() - 7);
          to.setDate(today.getDate());
          break;
        case "last30days":
          from.setDate(today.getDate() - 30);
          to.setDate(today.getDate());
          break;
        case "last60days":
          from.setDate(today.getDate() - 60);
          to.setDate(today.getDate());
          break;
        case "last90days":
          from.setDate(today.getDate() - 90);
          to.setDate(today.getDate());
          break;
        case "last120days":
          from.setDate(today.getDate() - 90);
          to.setDate(today.getDate());
          break;
        case "thisfy":
          from = new Date(today.getFullYear(), 0, 1);
          to = new Date(today.getFullYear(), 11, 31);
          break;
        case "prevfy":
          from = new Date(today.getFullYear() - 1, 0, 1);
          to = new Date(today.getFullYear() - 1, 11, 31);
          break;
        case "nextfy":
          from = new Date(today.getFullYear() + 1, 0, 1);
          to = new Date(today.getFullYear() + 1, 11, 31);
          break;
        case "nextfq":
          dummy = Math.floor(today.getMonth() / 3) + 1;
          quarter = dummy > 4 ? dummy - 4 : dummy;
          if (quarter === 1) {
            from = new Date(today.getFullYear() + 1, 0, 1);
            to = new Date(today.getFullYear() + 1, 2, 31);
          } else if (quarter === 2) {
            from = new Date(today.getFullYear(), 3, 1);
            to = new Date(today.getFullYear(), 5, 30);
          } else if (quarter === 3) {
            from = new Date(today.getFullYear(), 6, 1);
            to = new Date(today.getFullYear(), 8, 30);
          } else {
            from = new Date(today.getFullYear(), 9, 1);
            to = new Date(today.getFullYear(), 11, 31);
          }
          break;
        case "prevfq":
          dummy = Math.floor(today.getMonth() / 3) + 1;
          quarter = dummy > 4 ? dummy - 4 : dummy;
          if (quarter === 1) {
            from = new Date(today.getFullYear(), 0, 1);
            to = new Date(today.getFullYear(), 2, 31);
          } else if (quarter === 2) {
            from = new Date(today.getFullYear(), 3, 1);
            to = new Date(today.getFullYear(), 5, 30);
          } else if (quarter === 3) {
            from = new Date(today.getFullYear(), 6, 1);
            to = new Date(today.getFullYear(), 8, 30);
          } else {
            from = new Date(today.getFullYear() - 1, 9, 1);
            to = new Date(today.getFullYear() - 1, 11, 31);
          }
          break;
        case "thisfq":
          dummy = Math.floor(today.getMonth() / 3) + 1;
          quarter = dummy > 4 ? dummy - 4 : dummy;
          if (quarter === 1) {
            from = new Date(today.getFullYear(), 0, 1);
            to = new Date(today.getFullYear(), 2, 31);
          } else if (quarter === 2) {
            from = new Date(today.getFullYear(), 3, 1);
            to = new Date(today.getFullYear(), 5, 30);
          } else if (quarter === 3) {
            from = new Date(today.getFullYear(), 6, 1);
            to = new Date(today.getFullYear(), 8, 30);
          } else {
            from = new Date(today.getFullYear(), 9, 1);
            to = new Date(today.getFullYear(), 11, 31);
          }
          break;
        default:
          from = null;
          to = null;
          break;
      }
      fromElement.datepicker("update", from);
      toElement.datepicker("update", to);
    }
  };

  var onChangeVisibilityHandler = function (select) {
    var visibility = jQuery(select).val();
    if (visibility !== "Shared") {
      jQuery("#sharedwith").hide();
    } else {
      jQuery("#sharedwith").show();
    }
  };

  var removeMembers = function (button) {
    var section = jQuery(button).closest(".members"),
      available = section.find(".available-members"),
      members = section.find(".selected-members"),
      options = members.find("option:selected"),
      i,
      n,
      element,
      type,
      value,
      dummy,
      optGroup;

    if (options.length === 0) {
      return;
    }

    n = options.length - 1;
    for (i = n; i >= 0; i -= 1) {
      element = jQuery(options[i]);
      dummy = element.val().split("::");
      type = dummy[0];
      value = dummy[1];
      if (type === "group") {
        optGroup = getOptGroup(available, "groups", "Grupos");
      } else if (type === "role") {
        optGroup = getOptGroup(available, "roles", "Roles");
      } else if (type === "rs") {
        optGroup = getOptGroup(available, "rs", "Roles y subordinados");
      } else {
        optGroup = getOptGroup(available, "users", "Usuarios");
      }
      optGroup.append(element);
      element.removeAttr("selected");
    }
  };

  var setRelatedModules = function (select, selectedRelatedModules) {
    var moduleName = jQuery(select).val(),
      relatedModules = jQuery("#relatedmodulenames" + idModalWizard),
      arguments;

    relatedModules.empty();
    if (
      moduleName === undefined ||
      moduleName === null ||
      moduleName.trim() === ""
    ) {
      return;
    }

    arguments = [
      "module=Reports",
      "action=GetRelatedModules",
      "Ajax=true",
      "modulename=" + encodeURIComponent(moduleName),
    ];
    jQuery
      .ajax("index.php", {
        data: arguments.join("&"),
        dataType: "json",
        method: "get",
      })
      .done(function (response) {
        var relatedModulesSection = jQuery("#relatedmodules"),
          relatedModuleNames = jQuery("#relatedmodulenames" + idModalWizard),
          relatedModuleName,
          option;
        if (!response || jQuery.isEmptyObject(response)) {
          relatedModulesSection.hide();
          return;
        }

        relatedModulesSection.show();
        for (relatedModuleName in response) {
          if (!response.hasOwnProperty(relatedModuleName)) {
            continue;
          }
          option = jQuery("<option></option>");
          option.val(relatedModuleName);
          option.text(response[relatedModuleName]["tablabel"]);
          if (
            jQuery.isArray(selectedRelatedModules) &&
            jQuery.inArray(relatedModuleName, selectedRelatedModules) !== -1
          ) {
            option.attr("selected", "selected");
          }
          relatedModuleNames.append(option);
        }
      })
      .fail(function (jQueryResponse) {
        alert(jQueryResponse.responseText);
      });
  };

  var setCurrentTab = function (tab) {
    tabInPage = "&tab=" + tab;
  };

  var show = function (folderId, reportId) {
    var arguments;
    if (reportId) {
      arguments = [
        "module=Reports",
        "action=GetReportData",
        "Ajax=true",
        "record=" + encodeURIComponent(reportId),
      ];
      jQuery
        .ajax("index.php", {
          data: arguments.join("&"),
          dataType: "json",
          method: "get",
        })
        .done(function (response) {
          selectedReportId = reportId;
          selectedReportData = response;
          setSelectedReportData(response);
          try {
            wizard.show();
          } catch (e) {
            idModalWizard = document
              .getElementById("report-wizard-tab")
              .getAttribute("data-id-modal");
            if (idModalWizard === undefined) {
              idModalWizard = "";
            }
            init();
            if (reloadData) {
              reloadData = false;
              show(folderId, reportId);
            } else {
              alert("Uoops! error al cargar los datos del reporte");
            }
          }
        })
        .fail(function (jQueryResponse) {
          alert(jQueryResponse.responseText);
        });
    } else {
      jQuery("#folderid" + idModalWizard).val(folderId);
      selectedReportId = null;
      selectedReportData = null;
      wizard.show();
    }
  };

  window.ReportWizardUtils = {
    addColumn: addColumn,
    addColumns: addColumns,
    addCondition: addCondition,
    addConditionGroup: addConditionGroup,
    addGroupingField: addGroupingField,
    addMembers: addMembers,
    deleteColumn: deleteColumn,
    deleteColumns: deleteColumns,
    deleteCondition: deleteCondition,
    deleteConditionGroup: deleteConditionGroup,
    filterApplications: filterApplications,
    moveColumnsDown: moveColumnsDown,
    moveColumnsUp: moveColumnsUp,
    onChangeAdvancedFilterColumnHandler: onChangeAdvancedFilterColumnHandler,
    onChangeReportTypeHandler: onChangeReportTypeHandler,
    onChangeScheduleHandler: onChangeScheduleHandler,
    onChangeScheduleFrequencyHandler: onChangeScheduleFrequencyHandler,
    onChangeStandardFilterPeriodHandler: onChangeStandardFilterPeriodHandler,
    onChangeVisibilityHandler: onChangeVisibilityHandler,
    removeGroupingField: removeGroupingField,
    removeMembers: removeMembers,
    setRelatedModules: setRelatedModules,
    setCurrentTab: setCurrentTab,
    show: show,
  };

  jQuery(document).ready(function () {
    idModalWizard = document
      .getElementById("report-wizard-tab")
      .getAttribute("data-id-modal");
    if (idModalWizard === undefined) {
      idModalWizard = "";
    }
    init();
  });
})(jQuery);
