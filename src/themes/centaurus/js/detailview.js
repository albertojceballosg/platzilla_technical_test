function setCoOrdinate(elemId) {
  var oBtnObj = document.getElementById(elemId);
  var tagName = document.getElementById("lstRecordLayout");
  var leftpos = 0;
  var toppos = 0;
  var aTag = oBtnObj;
  do {
    leftpos += aTag.offsetLeft;
    toppos += aTag.offsetTop;
  } while ((aTag = aTag.offsetParent));
  tagName.style.top = toppos + 20 + "px";
  tagName.style.left = leftpos - 276 + "px";
}
function getListOfRecords(obj, sModule, iId, sParentTab) {
  new Ajax.Request("index.php", {
    queue: { position: "end", scope: "command" },
    method: "post",
    postBody:
      "module=Users&action=getListOfRecords&ajax=true&CurModule=" +
      sModule +
      "&CurRecordId=" +
      iId +
      "&CurParentTab=" +
      sParentTab,
    onComplete: function (response) {
      $("lstRecordLayout").innerHTML = response.responseText;
      var Lay = "lstRecordLayout";
      var tagName = document.getElementById(Lay);
      var leftSide = findPosX(obj);
      var topSide = findPosY(obj);
      var maxW = tagName.style.width;
      var widthM = maxW.substring(0, maxW.length - 2);
      var getVal = parseInt(leftSide) + parseInt(widthM);
      if (getVal > document.body.clientWidth) {
        leftSide = parseInt(leftSide) - parseInt(widthM);
        tagName.style.left = leftSide + 230 + "px";
        tagName.style.top = topSide + 20 + "px";
      } else {
        tagName.style.left = leftSide + 230 + "px";
      }
      setCoOrdinate(obj.id);
      tagName.style.display = "block";
      tagName.style.visibility = "visible";
    },
  });
}

(function (jQuery) {
  var activeGroup = "",
    moduleName = "",
    idHelper = "help_modulo_detailview",
    lastGroupId = 0,
    btnTabs = [
      "detail-view-btn-tab",
      "related-list-btn-tab",
      "graphic-view-btn-tab",
      "history-view-btn-tab",
    ];

  // private method
  var setActiveButton = function (selectedBotton) {
    var idSelected = selectedBotton;
    btnTabs.forEach(function (element, index) {
      var btn = jQuery("#" + element);
      if (element !== idSelected) {
        if (btn.hasClass("btn-primary")) {
          btn.addClass("btn-default");
          btn.removeClass("btn-primary");
        }
      } else if (btn.hasClass("btn-default")) {
        btn.addClass("btn-primary");
        btn.removeClass("btn-default");
      }
    });
  };

  var clearTaskCreator = function (id) {
    var priorities = jQuery("#detailview-task-priority-" + id + " li"),
      statues = jQuery("#detailview-task-status-" + id + " li"),
      groups = jQuery("#detailview-task-categories-" + id + " li"),
      groupName = jQuery("#categoryname-" + id),
      users = jQuery("#detailview-task-user-" + id + " li"),
      importance = jQuery("#detailview-task-importance-" + id + " li"),
      faClass = "",
      btnPriority = jQuery("#btn-group-priority-" + id),
      btnStatus = jQuery("#btn-group-task-status-" + id),
      btnGroup = jQuery("#btn-group-task-categories-" + id),
      btnUser = jQuery("#btn-group-user-" + id),
      btnImport = jQuery("#btn-group-importance-" + id),
      btnCreator = jQuery("#task-create-btn-" + id),
      infoUser = jQuery("#help-user-" + id).html("");

    jQuery("#main_input_box-" + id + ' input[name="taskname"]').val("");
    jQuery("#main_input_box-" + id + ' input[name="date_start"]').val("");
    jQuery("#main_input_box-" + id + ' input[name="due_date"]').val("");
    groupName.val("");
    groupName.addClass("hide");

    priorities.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        li.removeClass("active");
      }
    });
    faClass = btnPriority.find("i").eq(0);
    faClass.removeClass("fa-sort-asc");
    faClass.removeClass("fa-sort-desc");
    faClass.removeClass("fa-sort");
    faClass.addClass("fa-sort");
    btnPriority.removeClass("btn-primary");
    btnPriority.addClass("btn-default");

    statues.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        li.removeClass("active");
      }
    });
    faClass = btnStatus.find("i").eq(0);
    faClass.removeClass("fa-check");
    faClass.removeClass("fa-cogs");
    faClass.removeClass("fa-calendar-o");
    faClass.addClass("fa-exchange");
    btnStatus.removeClass("btn-primary");
    btnStatus.addClass("btn-default");

    groups.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        li.removeClass("active");
      }
    });
    faClass = btnGroup.find("i").eq(0);
    faClass.css("color", "#cccccc");
    btnGroup.removeClass("btn-primary");
    btnGroup.addClass("btn-default");

    users.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        li.removeClass("active");
      }
    });
    faClass = btnUser.find("i").eq(0);
    faClass.removeClass("fa-user");
    faClass.removeClass("fa-users");
    faClass.addClass("fa-user");
    faClass.css("color", "#cccccc");
    btnUser.removeClass("btn-primary");
    btnUser.addClass("btn-default");

    importance.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        li.removeClass("active");
      }
    });
    faClass = btnImport.find("i").eq(0);
    faClass.removeClass("fa-arrow-up");
    faClass.removeClass("fa-arrow-down");
    faClass.addClass("fa-exclamation-triangle");
    btnImport.removeClass("btn-primary");
    btnImport.addClass("btn-default");

    btnCreator.removeClass("btn-primary");
    btnCreator.addClass("btn-default");
  };

  var setTaskView = function (id, groupSelected, idRow, idCategory, data) {
    var currentUser = jQuery("#user-name-" + id).val(),
      taskRow,
      taskListGroup,
      isNewGroup = false;
    if (groupSelected !== 0) {
      isNewGroup = true;
      jQuery('div[id ^= "tasks-group-"]').each(function () {
        var dummy = jQuery(this).attr("id").split("-");
        if (dummy[2] == idCategory) {
          isNewGroup = false;
        }
      });
    }
    if (groupSelected === 0 || isNewGroup) {
      //console.log('task-group-template');
      taskListGroup = jQuery("#task-tab-" + id);
      taskRow = jQuery("#task-group-template-" + id)
        .html()
        .replace(/__ID__/g, idRow)
        .replace(/__CATEGORYID__/g, idCategory)
        .replace(/__CATEGORYNAME__/g, data[0])
        .replace(/__SUBJECT__/g, data[1])
        .replace(/__START_DATE__/g, data[2])
        .replace(/__DUE_DATE__/g, data[3])
        .replace(/__PRIORYTT__/g, data[4])
        .replace(/__STATUS__/g, data[5])
        .replace(/__USER__/g, currentUser + "," + data[6])
        .replace(/__DESCRIPTION__/g, data[7])
        .replace(/__START_TIME__/g, data[8])
        .replace(/__DUE_TIME__/g, data[9])
        .replace(/__IMPORTANCE__/g, data[10])
        .replace(/__ESTIMATED_TIME__/g, data[11])
        .replace(/__ESTIMATED_COST__/g, data[12]);
    } else {
      //console.log('task-row-template');
      taskListGroup = jQuery("#list-tasks-group-" + idCategory);
      taskRow = jQuery("#task-row-template-" + id)
        .html()
        .replace(/__ID__/g, idRow)
        .replace(/__SUBJECT__/g, data[1])
        .replace(/__START_DATE__/g, data[2])
        .replace(/__DUE_DATE__/g, data[3])
        .replace(/__PRIORYTT__/g, data[4])
        .replace(/__STATUS__/g, data[5])
        .replace(/__USER__/g, currentUser + "," + data[6])
        .replace(/__DESCRIPTION__/g, data[7])
        .replace(/__START_TIME__/g, data[8])
        .replace(/__DUE_TIME__/g, data[9])
        .replace(/__IMPORTANCE__/g, data[10])
        .replace(/__ESTIMATED_TIME__/g, data[11])
        .replace(/__ESTIMATED_COST__/g, data[12]);
    }
    taskListGroup.append(taskRow);
    if (groupSelected === 0 || groupSelected == lastGroupId) {
      lastGroupId = idCategory;
      jQuery('ul[id ^= "detailview-task-categories-"]').each(function () {
        var liRow,
          dummy = jQuery(this).attr("id").split("-");
        liRow = jQuery("#category-row-template-" + id)
          .html()
          .replace(/__ID__/g, dummy[3])
          .replace(/__TITLE__/g, data[0])
          .replace(/__CATEGORYID__/g, idCategory);

        jQuery(this).append(liRow);
      });
    }
    if (data[12] !== "") {
      jQuery("#activitytype-" + idRow).val(data[12]);
    }
  };

  var setHelp = function (viewSelected) {
    var helperIcon = jQuery("a[id ^= " + idHelper + "]"),
      partner = "";
    if (viewSelected !== "" && viewSelected !== undefined) {
      if (viewSelected === btnTabs[1]) {
        partner = "_related_list";
      } else if (viewSelected == btnTabs[2]) {
        partner = "_graphic";
      }
    }
    helperIcon.attr("id", idHelper + partner);
  };

  var updateTaskRow = function (id, user, priority, status, group, importance) {
    var faClass = "",
      priorities = jQuery("#detailview-task-priority-" + id + " li"),
      statues = jQuery("#detailview-task-status-" + id + " li"),
      groups = jQuery("#detailview-task-categories-" + id + " li"),
      users = jQuery("#detailview-task-user-" + id + " li"),
      importan = jQuery("#detailview-task-importance-" + id + " li"),
      btnPriority = jQuery("#btn-group-priority-" + id),
      btnImportan = jQuery("#btn-group-importance-" + id),
      btnStatus = jQuery("#btn-group-task-status-" + id),
      btnGroup = jQuery("#btn-group-task-categories-" + id),
      btnUser = jQuery("#btn-group-user-" + id),
      currentUser = jQuery("#user-name-" + id).val();

    faClass = btnPriority.find("i").eq(0);
    faClass.removeClass("fa-sort");
    priorities.each(function () {
      var li = jQuery(this),
        p = li.children("a").attr("rel");
      if (p === priority) {
        li.addClass("active");
        if (priority === "Alto") {
          faClass.addClass("fa-sort-asc");
        } else if (priority === "Bajo") {
          faClass.addClass(" fa-sort-desc");
        } else {
          faClass.addClass("fa-sort");
        }
      }
    });

    faClass = btnImportan.find("i").eq(0);
    faClass.removeClass("fa-exclamation-triangle");
    importan.each(function () {
      var li = jQuery(this),
        p = li.children("a").attr("rel");
      if (p === importance) {
        li.addClass("active");
        if (importance === "HIGH") {
          faClass.addClass("fa-arrow-up");
        } else if (importance === "LOW") {
          faClass.addClass("fa-arrow-down");
        } else {
          faClass.addClass("fa-exclamation-triangle");
        }
      }
    });

    faClass = btnStatus.find("i").eq(0);
    faClass.removeClass("fa-exchange");
    statues.each(function () {
      var li = jQuery(this),
        s = li.children("a").attr("rel");
      if (s === status) {
        li.addClass("active");
        if (status === "Held") {
          faClass.addClass("fa-check");
        } else if (status === "Not Held") {
          faClass.addClass("fa-cogs");
        } else if (status === "Planned") {
          faClass.addClass("fa-calendar-o");
        } else {
          faClass.addClass("fa-exchange");
        }
      }
    });

    faClass = btnUser.find("i").eq(0);
    faClass.css("color", "");
    faClass.removeClass("fa-user");
    users.each(function () {
      var li = jQuery(this),
        u = li.children("a").attr("rel");
      if (jQuery.inArray(u, user) !== -1) {
        li.addClass("active");
      }
    });

    if (user.length === 0) {
      faClass.addClass("fa-user");
      faClass.css("color", "#cccccc");
      btnUser.removeClass("btn-primary");
      btnUser.addClass("btn-default");
    } else if (user.length === 1) {
      faClass.addClass("fa-user");
      faClass.css("color", "");
      btnUser.removeClass("btn-default");
      btnUser.addClass("btn-primary");
    } else {
      faClass.addClass("fa-users");
      faClass.css("color", "");
      btnUser.removeClass("btn-default");
      btnUser.addClass("btn-primary");
    }

    groups.each(function () {
      var li = jQuery(this),
        g = li.children("a").attr("rel");
      if (g == group) {
        li.addClass("active");
      }
    });
  };

  var readyToSave = function (id) {
    var createBtn = jQuery("#task-create-btn-" + id),
      taskTitle = jQuery("#taskname-" + id).val(),
      dueDate = jQuery("#due-date-" + id).val(),
      priorities = jQuery("#detailview-task-priority-" + id + " li"),
      statues = jQuery("#detailview-task-status-" + id + " li"),
      groups = jQuery("#detailview-task-categories-" + id + " li"),
      importance = jQuery("#detailview-task-importance-" + id + " li"),
      timeEstimated = jQuery("#estimated_time-" + id).val(),
      hasPriorities = false,
      hasImportance = false,
      hasStatus = false,
      hasGruop = false;

    groups.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        hasGruop = true;
      }
    });
    priorities.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        hasPriorities = true;
      }
    });
    if (statues.length > 0) {
      statues.each(function () {
        var li = jQuery(this);
        if (li.hasClass("active")) {
          hasStatus = true;
        }
      });
    } else {
      hasStatus = true;
    }
    importance.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        hasImportance = true;
      }
    });

    if (
      taskTitle !== "" &&
      dueDate !== "" &&
      timeEstimated !== "" &&
      hasStatus &&
      hasGruop &&
      hasPriorities &&
      hasImportance
    ) {
      createBtn.removeClass("btn-default");
      createBtn.addClass("btn-primary");
    } else {
      createBtn.removeClass("btn-primary");
      createBtn.addClass("btn-default");
    }
  };

  // public method
  var activeDetailViewTab = function (e) {
    setActiveButton(btnTabs[0]);
    setHelp(btnTabs[0]);
    e.preventDefault();
  };

  var activeGraphicTab = function (e) {
    setActiveButton(btnTabs[2]);
    setHelp(btnTabs[2]);
    e.preventDefault();
  };

  var activeHistoryTab = function (e) {
    setActiveButton(btnTabs[3]);
  };

  var activeJobTab = function (event, moduleName, idTab, recordId) {
    var arguments = {
        module: moduleName,
        action: "AjaxDetailViewUtils",
        flmodule: moduleName,
        function: "VIEW-JOBS",
        tabid: idTab,
        record: recordId,
        Ajax: true,
      },
      content = jQuery("#tab-jobs-list-" + idTab),
      mainTab = jQuery("#detal-view-group-tab");
    if (content.contents().length <= 3 && moduleName !== "") {
      jQuery.post("index.php", arguments, function (data) {
        var message;
        try {
          message = JSON.parse(JSON.stringify(data));
          if (message.error !== "OK") {
            throw message.error;
          } else {
            content.html(message.html);
          }
        } catch (e) {
          console.error('[activeJobTab] Excepción:', e);
          if (e === undefined) {
            alert(
              "¡Uoooops! Esto es un poco embarazoso, pero ha ocurrido un pequeño error",
            );
            mainTab.find("#detail-view-btn-tab").trigger("click");
          } else {
            alert(e);
          }
        }
      }).fail(function(xhr, status, error) {
        console.error('[activeJobTab] Error en la solicitud AJAX:', {
          status: status,
          error: error,
          responseText: xhr.responseText
        });
      });
    }
  };

  var activeRelatedListTab = function (e) {
    setActiveButton(btnTabs[1]);
    setHelp(btnTabs[1]);
    e.preventDefault();
  };

  var activeReportsTab = function (e, id, moduleName) {
    var content = jQuery("#tab-metrics-reports-" + id),
      arguments = {
        module: moduleName,
        action: "AjaxListViewUtils",
        function: "VIEW-REPORT",
        requestview: "DETALVIEW",
        hometabid: id,
        Ajax: true,
      };
    if (content.contents().length <= 3 && moduleName !== "") {
      jQuery.post("index.php", arguments, function (data) {
        var message;
        try {
          message = JSON.parse(JSON.stringify(data));
          if (message.error !== "OK") {
            throw message.error;
          } else {
            content.html(message.html);
          }
        } catch (e) {
          alert(e);
          window.location.reload();
        }
      });
    }
    e.preventDefault();
  };

  var activeTaskTab = function (event, moduleName, idTab, recordId) {
    var arguments = {
        module: moduleName,
        action: "AjaxDetailViewUtils",
        flmodule: moduleName,
        function: "VIEW-TASK",
        tabid: idTab,
        record: recordId,
        Ajax: true,
      },
      content = jQuery("#task-list-" + idTab),
      mainTab = jQuery("#detal-view-group-tab");
    if (content.contents().length <= 3 && moduleName !== "") {
      jQuery.post("index.php", arguments, function (data) {
        var message;
        try {
          message = JSON.parse(JSON.stringify(data));
          if (message.error !== "OK") {
            console.error('[activeTaskTab] Error en respuesta:', message.error);
            throw message.error;
          } else {
            // Limpieza previa de modales y overlays antes de insertar el nuevo HTML de la pestaña Acciones
            jQuery(".modal:not(#sjv-modal), .modal-backdrop").remove();
            content.html(message.html);
          }
        } catch (e) {
          console.error('[activeTaskTab] Excepción:', e);
          if (e === undefined) {
            alert(
              "¡Uoooops! Esto es un poco embarazoso, pero ha ocurrido un pequeño error",
            );
            mainTab.find("#detail-view-btn-tab").trigger("click");
          } else {
            alert(e);
          }
        }
      }).fail(function(xhr, status, error) {
        console.error('[activeTaskTab] Error en la solicitud AJAX:', {
          status: status,
          error: error,
          responseText: xhr.responseText
        });
      });
    }
  };

  var editTask = function (idList) {
    var viewList = jQuery("#task-view-" + idList),
      formList = jQuery("#task-form-" + idList),
      groups = jQuery("#detailview-task-categories-" + idList + " li");

    groups.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        activeGroup = li.children("a").attr("rel");
      }
    });
    jQuery("li[id ^= task-view-]").removeClass("list-form");
    jQuery("li[id ^= task-form-]").addClass("list-form");

    viewList.addClass("list-form");
    formList.removeClass("list-form");
  };

  var cancelEditTask = function () {
    jQuery("li[id ^= task-view-]").removeClass("list-form");
    jQuery("li[id ^= task-form-]").addClass("list-form");
  };

  var setCompleted = function (id) {
    var taskRow = jQuery("#task-view-" + id),
      titleTask = taskRow.find("h4").eq(0),
      arguments,
      statues = jQuery("#detailview-task-status-" + id + " li"),
      statusName = jQuery("#statusname-" + id),
      ask = "¿Tarea realizada?";

    if (titleTask.hasClass("completed_item")) {
      ask = "¿La tarea no esta completada?";
      if (confirm(ask)) {
        arguments = {
          module: "Calendar",
          action: "FinishTask",
          function: "TASK_FROM_MODULE",
          record: id,
          progress: 0,
          eventstatus: "Not Held",
          Ajax: true,
        };
        jQuery.post("index.php", arguments, function (data) {
          var message;
          try {
            message = JSON.parse(JSON.stringify(data));
            if (message.error !== "OK") {
              throw message.error;
            } else {
              titleTask.removeClass("completed_item");
              statues.each(function () {
                var li = jQuery(this),
                  s = li.children("a").attr("rel");
                if (s === "Not Held") {
                  li.addClass("active");
                  statusName.html(li.children("a").attr("title"));
                }
              });
            }
          } catch (e) {
            alert(e);
          }
        });
      }
    } else {
      if (confirm(ask)) {
        arguments = {
          module: "Calendar",
          action: "FinishTask",
          function: "TASK_FROM_MODULE",
          record: id,
          Ajax: true,
        };
        jQuery.post("index.php", arguments, function (data) {
          var message;
          try {
            message = JSON.parse(JSON.stringify(data));
            if (message.error !== "OK") {
              throw message.error;
            } else {
              statues.each(function () {
                var li = jQuery(this),
                  s = li.children("a").attr("rel");
                if (s === "Held") {
                  li.addClass("active");
                  statusName.html(li.children("a").attr("title"));
                }
              });
              titleTask.addClass("completed_item");
            }
          } catch (e) {
            alert(e);
          }
        });
      }
    }
  };

  var createTask = function (obj, id, mode) {
    var btn = jQuery(obj),
      form = jQuery("#main_input_box-" + id),
      help = jQuery("#help-" + id),
      arguments = form.serialize() + "&ajax=1",
      argumentArr = arguments.split("&"),
      dataTask = form.serializeArray(),
      priorities = jQuery("#detailview-task-priority-" + id + " li"),
      statues = jQuery("#detailview-task-status-" + id + " li"),
      users = jQuery("#detailview-task-user-" + id + " li"),
      groups = jQuery("#detailview-task-categories-" + id + " li"),
      importance = jQuery("#detailview-task-importance-" + id + " li"),
      idRow = Math.floor(Math.random() * 100000 + 1),
      idCategory,
      taskRow,
      info = mode === "UPDATE" ? "actualizar" : "crear",
      taskListGroup = jQuery("#list-tasks-group-" + id),
      newGroup = jQuery("#categoryname-" + id).val(),
      estimatedTime = jQuery("#estimated_time-" + id).val(),
      estimatedCost = jQuery("#estimated_cost-" + id).val(),
      activityType = jQuery("#activitytype-" + id).val(),
      groupSelected = 0,
      groupName = "",
      userSelected = [],
      prioritySelected = "",
      importSelected = "",
      importTxSelected = "",
      statusName = "",
      statusSelected = "",
      description = "",
      startDate = "",
      startTime = "",
      dueDate = "",
      dueTime = "",
      userName = "",
      subject = "";
    btn.attr("disabled", true);
    //console.log('Buscando categorías con selector: #detailview-task-categories-' + id + ' li');
    //console.log('Elementos encontrados:', groups.length);
    groups.each(function () {
      var li = jQuery(this);
      //console.log('Elemento categories:', li, 'hasClass active:', li.hasClass ('active'));
      if (li.hasClass("active")) {
        groupSelected = parseInt(li.children("a").attr("rel"));
        argumentArr.push("categoryid=" + encodeURIComponent(groupSelected));
        if (groupSelected === 0) {
          dataTask.push({ name: "category_name", value: newGroup });
          argumentArr.push("category_name=" + encodeURIComponent(newGroup));
          groupName = newGroup;
        } else {
          dataTask.push({
            name: "category_name",
            value: li.children("a").attr("title"),
          });
          groupName = li.children("a").attr("title");
          argumentArr.push("category_name=" + encodeURIComponent(groupName));
        }
      }
    });
    // Si no se encontró categoría activa en modo UPDATE, leer del campo hidden
    if (mode === "UPDATE" && groupName === "") {
      var hiddenCategory = jQuery("#categoryid-" + id).val();
      //console.log('Campo hidden categoryid-' + id + ':', hiddenCategory);
      if (
        hiddenCategory !== undefined &&
        hiddenCategory !== null &&
        hiddenCategory !== ""
      ) {
        groupSelected = parseInt(hiddenCategory);
        argumentArr.push("categoryid=" + encodeURIComponent(groupSelected));
        // Buscar el nombre de la categoría en los elementos del dropdown
        groups.each(function () {
          var li = jQuery(this);
          var rel = li.children("a").attr("rel");
          if (rel == hiddenCategory) {
            groupName = li.children("a").attr("title");
            dataTask.push({ name: "category_name", value: groupName });
            argumentArr.push("category_name=" + encodeURIComponent(groupName));
          }
        });
      }
    }
    //console.log('Buscando prioridad con selector: #detailview-task-priority-' + id + ' li');
    //console.log('Elementos encontrados:', priorities.length);
    priorities.each(function () {
      var li = jQuery(this);
      //console.log('Elemento priority:', li, 'hasClass active:', li.hasClass('active'));
      if (li.hasClass("active")) {
        prioritySelected = li.children("a").attr("rel");
        argumentArr.push(
          "taskpriority=" + encodeURIComponent(prioritySelected),
        );
        dataTask.push({
          name: "priorityname",
          value: li.children("a").attr("title"),
        });
      }
    });
    // Si no se encontró prioridad activa en modo UPDATE, leer del campo hidden
    if (mode === "UPDATE" && prioritySelected === "") {
      var hiddenPriority = jQuery("#taskpriority-" + id).val();
      //console.log('Campo hidden taskpriority-' + id + ':', hiddenPriority);
      if (
        hiddenPriority !== undefined &&
        hiddenPriority !== null &&
        hiddenPriority !== ""
      ) {
        prioritySelected = hiddenPriority;
        argumentArr.push(
          "taskpriority=" + encodeURIComponent(prioritySelected),
        );
        // Buscar el nombre de la prioridad en los elementos del dropdown
        priorities.each(function () {
          var li = jQuery(this);
          var rel = li.children("a").attr("rel");
          if (rel == hiddenPriority) {
            dataTask.push({
              name: "priorityname",
              value: li.children("a").attr("title"),
            });
          }
        });
      }
    }
    //console.log('Buscando importancia con selector: #detailview-task-importance-' + id + ' li');
    //console.log('Elementos encontrados:', importance.length);
    importance.each(function () {
      var li = jQuery(this);
      //console.log('Elemento importance:', li, 'hasClass active:', li.hasClass('active'));
      if (li.hasClass("active")) {
        importSelected = li.children("a").attr("rel");
        argumentArr.push("taskImport=" + encodeURIComponent(importSelected));
        dataTask.push({
          name: "importname",
          value: li.children("a").attr("title"),
        });
        importTxSelected = li.children("a").html();
      }
    });
    // Si no se encontró importancia activa en modo UPDATE, leer del campo hidden
    if (mode === "UPDATE" && importSelected === "") {
      var hiddenImportance = jQuery("#taskImport-" + id).val();
      //console.log('Campo hidden taskImport-' + id + ':', hiddenImportance);
      if (
        hiddenImportance !== undefined &&
        hiddenImportance !== null &&
        hiddenImportance !== ""
      ) {
        importSelected = hiddenImportance;
        argumentArr.push("taskImport=" + encodeURIComponent(importSelected));
        // Buscar el nombre de la importancia en los elementos del dropdown
        importance.each(function () {
          var li = jQuery(this);
          var rel = li.children("a").attr("rel");
          if (rel == hiddenImportance) {
            dataTask.push({
              name: "importname",
              value: li.children("a").attr("title"),
            });
            importTxSelected = li.children("a").html();
          }
        });
      }
    }
    if (mode === "UPDATE") {
      //console.log('Buscando estado con selector: #detailview-task-status-' + id + ' li');
      //console.log('Elementos encontrados:', statues.length);
      statues.each(function () {
        var li = jQuery(this);
        //console.log('Elemento status:', li, 'hasClass active:', li.hasClass('active'));
        if (li.hasClass("active")) {
          statusSelected = li.children("a").attr("rel");
          statusName = li.children("a").attr("title");
          argumentArr.push("eventstatus=" + encodeURIComponent(statusSelected));
          dataTask.push({
            name: "statusname",
            value: li.children("a").attr("title"),
          });
        }
      });
      // Si no se encontró estado activo en modo UPDATE, leer del campo hidden
      if (statusSelected === "") {
        var hiddenStatus = jQuery("#eventstatus-" + id).val();
        //console.log('Campo hidden eventstatus-' + id + ':', hiddenStatus);
        if (
          hiddenStatus !== undefined &&
          hiddenStatus !== null &&
          hiddenStatus !== ""
        ) {
          statusSelected = hiddenStatus;
          argumentArr.push("eventstatus=" + encodeURIComponent(statusSelected));
          // Buscar el nombre del estado en los elementos del dropdown
          statues.each(function () {
            var li = jQuery(this);
            var rel = li.children("a").attr("rel");
            if (rel == hiddenStatus) {
              statusName = li.children("a").attr("title");
              dataTask.push({ name: "statusname", value: statusName });
            }
          });
        }
      }
    } else {
      statusSelected = "Planned";
      statusName = "Planeado";
      argumentArr.push("eventstatus=" + encodeURIComponent("Planned"));
      dataTask.push({ name: "statusname", value: "Planeado" });
    }
    users.each(function () {
      var li = jQuery(this);
      if (li.hasClass("active")) {
        userSelected.push(li.children("a").attr("rel"));
        userName = li.children("a").attr("title");
        dataTask.push({
          name: "username",
          value: li.children("a").attr("title"),
        });
      }
    });

    argumentArr.push(
      "inviteesid=" + encodeURIComponent(userSelected.join(";")),
    );
    jQuery.each(dataTask, function (i, field) {
      if (field.name === "taskname") {
        var dummy = field.value.split(";");
        subject = dummy[0].trim();
        try {
          subject = dummy[0].trim();
          argumentArr.push("subject=" + encodeURIComponent(subject));
          description = dummy[1].trim();
          argumentArr.push("description=" + encodeURIComponent(description));
        } catch (e) {
          description = "";
          argumentArr.push("description=" + encodeURIComponent(description));
        }
      } else if (field.name === "subject") {
        subject = field.value;
      } else if (field.name === "description") {
        description = field.value;
      } else if (field.name === "date_start") {
        startDate = field.value;
      } else if (field.name === "due_date") {
        dueDate = field.value;
      } else if (field.name === "time_start") {
        startTime = field.value;
      } else if (field.name === "time_end") {
        dueTime = field.value;
      }
    });
    //(dueDate === '') ||
    // En modo UPDATE, no requerir prioritySelected, statusSelected y groupName si están vacíos
    // ya que estos valores ya deberían estar en el registro que se está actualizando
    var requiredFields = [
      subject === "",
      importSelected === "",
      estimatedTime === "",
    ];
    if (mode !== "UPDATE") {
      // En modo CREATE, requerir todos los campos
      requiredFields.push(
        prioritySelected === "",
        statusSelected === "",
        groupName === "",
      );
    }
    if (
      requiredFields.some(function (v) {
        return v;
      })
    ) {
      alert("Uoops! faltan datos para " + info + " la tarea");
      btn.removeAttr("disabled");
      return;
    }

    jQuery.post("index.php", argumentArr.join("&"), function (data) {
      var message, data;
      try {
        message = JSON.parse(JSON.stringify(data));
        if (message.error !== "OK") {
          throw message.error;
        } else {
          idRow = message.activityId;
          idCategory = message.categoryId;
          // Usar datos formateados del backend si están disponibles
          var formattedEstimatedTime =
            message.formatted_estimated_time || estimatedTime;
          var formattedEstimatedCost =
            message.formatted_estimated_cost || estimatedCost;

          if (mode === "CREATE") {
            data = [
              groupName,
              subject,
              startDate,
              dueDate,
              prioritySelected,
              statusName,
              userName,
              description,
              startTime,
              dueTime,
              importTxSelected,
              formattedEstimatedTime,
              formattedEstimatedCost,
              activityType,
            ];
            setTaskView(id, groupSelected, idRow, idCategory, data);
            updateTaskRow(
              idRow,
              userSelected,
              prioritySelected,
              statusSelected,
              idCategory,
              importSelected,
            );
            jQuery("#main-" + id)
              .find(".datepickerDate-" + id)
              .datepicker({
                format: "yyyy-mm-dd",
                language: "es",
                weekStart: 1,
              });
            clearTaskCreator(id);
          } else {
            help
              .html("Se ha actualizado con éxito!")
              .fadeOut(4000, function () {
                jQuery(this).html("").fadeIn("fast");
              });
            if (parseInt(activeGroup) !== groupSelected) {
              var idMain = jQuery(obj).attr("data-id-main"),
                taskViewList = jQuery("#task-view-" + id),
                taskViewFrom = jQuery("#task-form-" + id);

              data = [
                groupName,
                subject,
                startDate,
                dueDate,
                prioritySelected,
                statusName,
                userName,
                description,
                startTime,
                dueTime,
                importTxSelected,
                formattedEstimatedTime,
                formattedEstimatedCost,
                activityType,
              ];
              setTaskView(idMain, groupSelected, idRow, idCategory, data);
              updateTaskRow(
                idRow,
                userSelected,
                prioritySelected,
                statusSelected,
                groupSelected,
                importSelected,
              );
              jQuery("#main-" + idMain)
                .find(".datepickerDate-" + idMain)
                .datepicker({
                  format: "yyyy-mm-dd",
                  language: "es",
                  weekStart: 1,
                });
              if (taskViewList.parent().children().length <= 2) {
                taskViewList.parent().parent().remove();
              } else {
                taskViewList.remove();
                taskViewFrom.remove();
              }
            } else {
              var dateStart = "",
                timeStart = "",
                objDateTimeStart = jQuery("#date_start-dt-" + id),
                dateEnd = "",
                timeEnd = "",
                objDateTimeEnd = jQuery("#due_date-dt-" + id),
                objUserName = jQuery("#username-dt-" + id),
                timeEstimated = jQuery("#time_estimated-dt-" + id),
                costEstimated = jQuery("#cost_estimated-dt-" + id),
                actualUser = jQuery("#user-name-" + id).val(),
                totalUsers = "";
              jQuery.each(dataTask, function (i, field) {
                if (
                  field.name === "relatedcrmids[]" ||
                  field.name === "activitytype"
                ) {
                  return true;
                }
                var objList = jQuery("#" + field.name + "-" + id);
                if (field.name === "date_start") {
                  dateStart = field.value;
                  // objList.html('<i class="fa fa-calendar"></i>&nbsp;' + field.value)
                } else if (field.name === "time_start") {
                  if (activityType === "Assignment") {
                    timeStart = "";
                  } else {
                    timeStart = field.value;
                  }
                } else if (field.name === "due_date") {
                  if (activityType !== "Assignment") {
                    dateEnd = dateStart;
                  } else {
                    dateEnd = field.value;
                  }
                } else if (field.name === "estimated_time") {
                  timeEstimated.html(field.value + "&nbsp;Horas");
                } else if (field.name === "estimated_cost") {
                  costEstimated.html(field.value);
                } else if (field.name === "username") {
                  if (totalUsers === "") {
                    totalUsers = field.value;
                  } else {
                    totalUsers += ", " + field.value;
                  }
                  objList.html(
                    '&nbsp;<i class="fa fa-user" aria-hidden="true"></i>' +
                      field.value,
                  );
                } else if (field.name === "subject") {
                  objList.html(field.value);
                  if (statusSelected === "Held") {
                    objList.addClass("completed_item");
                  } else {
                    objList.removeClass("completed_item");
                  }
                } else {
                  objList.html(field.value);
                }
              });
              objDateTimeStart.html(
                '<i class="fa fa-calendar"></i>&nbsp;' +
                  dateStart +
                  "&nbsp;" +
                  timeStart,
              );
              objDateTimeEnd.html(dateEnd);
              objUserName.html(
                '&nbsp;<i class="fa fa-user" aria-hidden="true"></i>&nbsp;' +
                  actualUser +
                  ", " +
                  totalUsers,
              );
              jQuery("#task-cancel-edit-" + id).trigger("click");
            }
          }
          btn.removeAttr("disabled");
        }
      } catch (e) {
        alert(e);
        help.html("");
      }
    });
  };

  var deleteTaskRow = function (domId, crmid) {
    var taskRow = jQuery("#task-view-" + domId),
      arguments,
      ask = "¿Eliminar tarea?",
      list = taskRow.parent();

    // Si solo se pasa un parámetro, asumir que es el crmid y buscar por atributo data-crmid
    if (crmid === undefined) {
      crmid = domId;
      taskRow = jQuery('[data-crmid="' + crmid + '"]');
    }

    if (confirm(ask)) {
      arguments = {
        module: "Calendar",
        action: "Delete",
        function: "TASK_FROM_MODULE",
        record: crmid,
        Ajax: true,
      };
      jQuery.post("index.php", arguments, function (data) {
        var message;
        try {
          message = JSON.parse(JSON.stringify(data));
          if (message.error !== "OK") {
            throw message.error;
          } else {
            taskRow.remove();
            if (list.children().length <= 2) {
              list.parent().remove();
            }
          }
        } catch (e) {
          alert(e);
        }
      });
    }
  };

  var setExtendedTask = function (obj, id) {
    var btn = jQuery(obj),
      modo = btn.attr("data-action"),
      faClass = btn.find("i").eq(0),
      extendedObj = jQuery(".extended-task-" + id),
      timeStar = jQuery("#time_start-" + id),
      timeEnd = jQuery("#time_end-" + id);

    if (modo === "EXTENDED") {
      extendedObj.removeClass("hide");
      faClass.removeClass("fa-plus");
      faClass.addClass("fa-minus");
      btn.attr("data-action", "CONTRACT");
      extendedObj.find("input").eq(0).removeAttr("disabled");
      timeStar.removeAttr("disabled");
      timeEnd.removeAttr("disabled");
    } else {
      extendedObj.addClass("hide");
      faClass.removeClass("fa-minus");
      faClass.addClass("fa-plus");
      btn.attr("data-action", "EXTENDED");
      extendedObj.find("input").eq(0).attr("disabled", true);
      timeStar.attr("disabled", true);
      timeEnd.attr("disabled", true);
    }
  };

  var selectedActivityTypes = function (obj, id) {
    var type = jQuery(obj),
      startDay = jQuery("#date_start-" + id),
      endDay = jQuery("#due-date-" + id),
      endDayBlock = endDay.parent(),
      estimatedTime = jQuery("#estimated_time-" + id),
      startTime = jQuery("#start_time-" + id),
      timeBlock = startTime.parent(),
      today = jQuery("#today-" + id).val(),
      tomorrow = jQuery("#tomorrow-" + id).val();

    startDay.val(today);
    startDay.datepicker({ setDate: new Date(today) });
    startTime.val("09:10:00");
    endDayBlock.removeClass("hide");
    timeBlock.removeClass("hide");
    //console.log(startTime.val());
    if (type.val() === "Assignment") {
      timeBlock.addClass("hide");
      startTime.val("00:00:00");
      endDay.val(tomorrow);
      endDay.datepicker({ setDate: tomorrow });
      estimatedTime.val("0.50");
    } else if (type.val() === "Call") {
      endDayBlock.addClass("hide");
      startTime.val("09:00:00");
      estimatedTime.val(0.1);
      endDay.val(today);
      endDay.datepicker({ setDate: new Date(today) });
    } else if (type.val() === "Meeting") {
      endDay.val(today);
      endDay.datepicker({ setDate: new Date(today) });
      endDayBlock.addClass("hide");
      estimatedTime.val(0.75);
    } else {
      timeBlock.addClass("hide");
      startTime.val("00:00:00");
      endDay.val(tomorrow);
      endDay.datepicker({ setDate: tomorrow });
      estimatedTime.val(0.5);
    }
  };

  var selectedImportance = function (e, obj, id) {
    var allList = jQuery("#detailview-task-importance-" + id + " li"),
      btn = jQuery("#btn-group-importance-" + id),
      list = jQuery(obj).parent(),
      importance = jQuery(obj).attr("rel"),
      faClass = btn.find("i").eq(0),
      infoText = "",
      found = false;

    allList.each(function () {
      var li = jQuery(this);
      if (li.children("a").attr("rel") !== importance) {
        li.removeClass("active");
      }
    });

    if (list.hasClass("active")) {
      list.removeClass("active");
    } else {
      list.addClass("active");
    }

    faClass.removeClass("fa-exclamation-triangle");
    faClass.removeClass("fa-arrow-up");
    faClass.removeClass("fa-arrow-down");

    allList.each(function () {
      var li = jQuery(this),
        cat = li.children("a").attr("rel");
      if (li.hasClass("active") && cat !== "undefined") {
        if (cat === "HIGH") {
          faClass.addClass("fa-arrow-up");
          infoText = "Importancia: Alta";
          found = true;
        } else if (cat === "LOW") {
          infoText = "Importancia: Baja";
          faClass.addClass("fa-arrow-down");
          found = true;
        } else {
          faClass.addClass("fa-exclamation-triangle");
          infoText = "Importancia de la tarea";
          found = true;
        }
      }
    });
    if (!found) {
      faClass.removeClass("fa-arrow-up");
      faClass.removeClass("fa-arrow-down");
      faClass.addClass("fa-exclamation-triangle");
      btn.removeClass("btn-primary");
      btn.addClass("btn-default");
      infoText = "Importancia de la tarea";
    } else {
      btn.removeClass("btn-default");
      btn.addClass("btn-primary");
    }
    btn.attr("title", infoText);
    e.preventDefault();
    readyToSave(id);
    //e.stopPropagation ()
    e.preventDefault();
  };

  var setCategory = function (e, obj, id) {
    var allList = jQuery("#detailview-task-categories-" + id + " li"),
      btn = jQuery("#btn-group-task-categories-" + id),
      list = jQuery(obj).parent(),
      category = jQuery(obj).attr("rel"),
      categoryName = jQuery("#categoryname-" + id),
      faClass = btn.find("i").eq(0),
      infoText = "Ubicación de la tarea",
      found = false;

    allList.each(function () {
      var li = jQuery(this);
      if (li.children("a").attr("rel") !== category) {
        li.removeClass("active");
        if (li.children("a").attr("rel") == 0) {
          categoryName.val("");
          categoryName.addClass("hide");
        }
      }
    });

    if (list.hasClass("active")) {
      list.removeClass("active");
      if (category == 0) {
        categoryName.val("");
        categoryName.addClass("hide");
      }
    } else {
      list.addClass("active");
      if (category == 0) {
        categoryName.val("");
        categoryName.removeClass("hide");
      }
    }

    faClass.css("color", "");

    allList.each(function () {
      var li = jQuery(this),
        cat = li.children("a").attr("rel");
      if (li.hasClass("active") && cat !== "undefined") {
        infoText = "Grupo: " + li.find("a").eq(0).attr("title");
        found = true;
      }
    });
    if (!found) {
      faClass.css("color", "#cccccc");
      btn.removeClass("btn-primary");
      btn.addClass("btn-default");
    } else {
      btn.removeClass("btn-default");
      btn.addClass("btn-primary");
    }
    btn.attr("title", infoText);
    readyToSave(id);
    e.preventDefault();
    // e.stopPropagation ()
  };

  var selectedStatus = function (e, obj, id, myStatus) {
    var allList = jQuery("#detailview-task-status-" + id + " li"),
      btn = jQuery("#btn-group-task-status-" + id),
      list = jQuery(obj).parent(),
      status = jQuery(obj).attr("rel"),
      faClass = btn.find("i").eq(0),
      infoText = "",
      found = 0;

    if (status === "Not Held") {
      if (myStatus === "Planned") {
        alert(
          "Imposible cambiar estado, aun no has creado el primer reporte de tareas",
        );
        e.preventDefault();
        return;
      }
    } else if (myStatus === "Not Held" && status === "Planned") {
      e.preventDefault();
      return;
    }
    allList.each(function () {
      var li = jQuery(this);
      if (li.children("a").attr("rel") !== status) {
        li.removeClass("active");
      }
    });

    if (list.hasClass("active")) {
      list.removeClass("active");
    } else {
      list.addClass("active");
    }

    faClass.removeClass("fa-check");
    faClass.removeClass("fa-cogs");
    faClass.removeClass("fa-exchange");
    faClass.removeClass("fa-calendar-o");

    allList.each(function () {
      var li = jQuery(this),
        cat = li.children("a").attr("rel");
      if (li.hasClass("active") && cat !== "undefined") {
        if (cat === "Held") {
          infoText = "Estado: Realizado";
          faClass.addClass("fa-check");
          found = true;
        } else if (cat === "Not Held") {
          infoText = "Estado: Pendiente";
          faClass.addClass("fa-cogs");
          found = true;
        } else if (cat === "Planned") {
          infoText = "Estado: Planeado";
          faClass.addClass("fa-calendar-o");
          found = true;
        } else {
          infoText = "Estado de la tarea";
          faClass.addClass("fa-exchange");
          found = true;
        }
      }
    });
    if (!found) {
      faClass.removeClass("fa-check");
      faClass.removeClass("fa-cogs");
      faClass.removeClass("fa-calendar-o");
      faClass.addClass("fa-exchange");
      btn.removeClass("btn-primary");
      btn.addClass("btn-default");
      infoText = "Estado de la tarea";
    } else {
      btn.removeClass("btn-default");
      btn.addClass("btn-primary");
    }
    btn.attr("title", infoText);
    readyToSave(id);

    e.preventDefault();
    // e.stopPropagation ()
  };

  var selectedPriority = function (e, obj, id) {
    var allList = jQuery("#detailview-task-priority-" + id + " li"),
      btn = jQuery("#btn-group-priority-" + id),
      list = jQuery(obj).parent(),
      priority = jQuery(obj).attr("rel"),
      helpText = jQuery("#help-user-" + id),
      faClass = btn.find("i").eq(0),
      infoText = "",
      found = false;

    allList.each(function () {
      var li = jQuery(this);
      if (li.children("a").attr("rel") !== priority) {
        li.removeClass("active");
      }
    });

    if (list.hasClass("active")) {
      list.removeClass("active");
    } else {
      list.addClass("active");
    }

    faClass.removeClass("fa-sort");
    faClass.removeClass("fa-sort-asc");
    faClass.removeClass("fa-sort-dec");
    faClass.removeClass("fa-sort");

    allList.each(function () {
      var li = jQuery(this),
        cat = li.children("a").attr("rel");
      if (li.hasClass("active") && cat !== "undefined") {
        if (cat === "Alto") {
          faClass.addClass("fa-sort-asc");
          infoText = "Prioridad: Alta";
          found = true;
        } else if (cat === "Bajo") {
          infoText = "Prioridad: Baja";
          faClass.addClass("fa-sort-desc");
          found = true;
        } else {
          faClass.addClass("fa-sort");
          infoText = "Prioridad de la tarea";
          found = true;
        }
      }
    });
    if (!found) {
      faClass.removeClass("fa-sort-asc");
      faClass.removeClass("fa-sort-dec");
      faClass.removeClass("fa-sort");
      faClass.addClass("fa-sort");
      btn.removeClass("btn-primary");
      btn.addClass("btn-default");
      infoText = "Prioridad de la tarea";
    } else {
      btn.removeClass("btn-default");
      btn.addClass("btn-primary");
    }
    btn.attr("title", infoText);
    e.preventDefault();
    readyToSave(id);
    //e.stopPropagation ()
  };

  var selectedUser = function (e, obj, id) {
    var allList = jQuery("#detailview-task-user-" + id + " li"),
      btn = jQuery("#btn-group-user-" + id),
      list = jQuery(obj).parent(),
      helpText = jQuery("#help-user-" + id),
      userId = jQuery(obj).attr("rel"),
      faClass = btn.find("i").eq(0),
      userSelected = [],
      found = 0,
      infoText = "";

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
        found += 1;
      }
    });
    if (found === 0) {
      faClass.addClass("fa-user");
      faClass.css("color", "#cccccc");
      helpText.html("");
      btn.removeClass("btn-primary");
      btn.addClass("btn-default");
    } else if (found === 1) {
      faClass.addClass("fa-user");
      faClass.css("color", "");
      helpText.html("<b>Usuario invitado:</b>&nbsp;" + userSelected.join(","));
      btn.removeClass("btn-default");
      btn.addClass("btn-primary");
    } else {
      faClass.addClass("fa-users");
      faClass.css("color", "");
      helpText.html(
        "<b>Usuarios invitados:</b>&nbsp;" + userSelected.join(","),
      );
      btn.removeClass("btn-default");
      btn.addClass("btn-primary");
    }

    e.preventDefault();
    //e.stopPropagation ()
  };

  var normalizeEstimatedTime = function (fieldElement, e, id) {
    var fieldLength = jQuery(fieldElement);
    //console.log(e.keyCode);

    // Solo permitir caracteres válidos para números
    if (
      e.ctrlKey === true ||
      e.metaKey === true ||
      e.keyCode === 16 ||
      (e.keyCode <= 47 && e.keyCode !== 8) ||
      (e.keyCode >= 58 &&
        e.keyCode !== 190 &&
        e.keyCode !== 188 &&
        e.keyCode !== 110)
    ) {
      e.preventDefault();
    }

    // NO hacer nada más - dejar que el formateo general.js maneje el blur
    if (id !== "") {
      readyToSave(id);
    }
  };

  var taskGroupStatus = function (e, obj, id) {
    var group = jQuery(obj),
      taskGroup = group.parent().children("ol"),
      groupStatus = group.attr("data-status");
    if (groupStatus === "visible") {
      taskGroup.addClass("hide");
      group.attr("data-status", "hidden");
      group.attr("title", "Mostrar tareas");
    } else {
      taskGroup.removeClass("hide");
      group.attr("data-status", "visible");
      group.attr("title", "Ocultar tareas");
    }

    // e.preventDefault ();
  };

  window.DetailViewTabUtils = {
    activeDetailViewTab: activeDetailViewTab,
    activeGraphicTab: activeGraphicTab,
    activeHistoryTab: activeHistoryTab,
    activeJobTab: activeJobTab,
    activeRelatedListTab: activeRelatedListTab,
    activeReportsTab: activeReportsTab,
    activeTaskTab: activeTaskTab,
    cancelEditTask: cancelEditTask,
    setCompleted: setCompleted,
    createTask: createTask,
    deleteTaskRow: deleteTaskRow,
    editTask: editTask,
    readyToSave: readyToSave,
    setExtendedTask: setExtendedTask,
    selectedActivityTypes: selectedActivityTypes,
    selectedImportance: selectedImportance,
    setCategory: setCategory,
    selectedStatus: selectedStatus,
    selectedPriority: selectedPriority,
    selectedUser: selectedUser,
    normalizeEstimatedTime: normalizeEstimatedTime,
    taskGroupStatus: taskGroupStatus,
  };

  var onDocumentReadyHandler = function () {
    var activeTab = jQuery("#detal-view-group-tab").attr("data-tab");

    if (activeTab !== "" || activeTab !== "detail") {
      if (activeTab === "related_list") {
        setHelp(btnTabs[1]);
      } else if (activeTab === "control_panel") {
        setHelp(btnTabs[2]);
      } else if (activeTab === "task-list") {
        jQuery("#task-list-btn-tab").trigger("click");
      }
    }
  };
  // Manejador para cargar la modal de tarea/acción bajo demanda
  jQuery(document).on("click", ".open-precreated-task-modal", function (e) {
    e.preventDefault();
    var $btn = jQuery(this); // Usar SIEMPRE objeto jQuery
    var taskId = $btn.data("taskid");
    var module = $btn.data("module");
    // Limpieza previa de modales y overlays
    jQuery(".modal, .modal-backdrop").remove();
    // Aquí termina la lógica de apertura/cierre de modales

    // Limpieza global de overlays y modales residuales al cerrarse cualquier modal
    jQuery(document).on("hidden.bs.modal", function () {
      jQuery(".modal-backdrop, .md-overlay, .fade-precreated-task").remove();
      jQuery(".modal").remove();
    });
    jQuery.get(
      "index.php",
      {
        module: module,
        action: "AjaxDetailViewUtils",
        function: "GET-PRECREATED-TASK-MODAL",
        task_id: taskId,
        Ajax: true,
      },
      function (modalHtml) {
        jQuery("body").append(modalHtml);
        jQuery(".modal:last").modal("show");
      },
    );
  });
  jQuery(document).ready(onDocumentReadyHandler);
})(jQuery);
