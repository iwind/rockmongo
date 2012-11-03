/*
 * highlight collection in leftbar
 */
window.parent.frames["left"].highlightCollection(currentCollection, currentRecordsCount);

/*
 * show operation buttons for one row 
 */
function showOperationButtons(id) {
	var row = $("#object_" + id);
	row.css("background-color", "#eeefff");
	//row.css("border", "2px #ccc solid");
}

//hide operation buttons for one row
function hideOperationButtons(id) {
	var row = $("#object_" + id);
	row.css("background-color", "#fff");
	row.css("border", "0");
}

/** expand text area **/
function expandText(id) {
	var text = $("#text_" +id);
	if (text.attr("expand") == "true") {
		text.css("height", "");
		text.css("max-height", 150);
		text.attr("expand", "false");
		$("#expand_" + id).html("Expand");
	}
	else {
		text.css("height", text[0].scrollHeight);
		text.css("max-height", text[0].scrollHeight);
		text.attr("expand", "true");
		$("#expand_" + id).html("Collapse");
	}
}

//change command - findAll, modify, remove
function changeCommand(select) {
	//newobj input box
	var value = select.value;
	if (value == "modify") {
		$("#newobjInput").show();
	}
	else {
		$("#newobjInput").hide();
	}
	
	//limit input box
	if (value == "findAll") {
		$("#limitLabel").show();
		$("#pageSetLabel").show();
		$("#fieldsAndHints").show();
	}
	else {
		$("#limitLabel").hide();
		$("#pageSetLabel").hide();
		$("#fieldsAndHints").hide();
	}
}

//switch html and text
function changeText(id) {
	var textDiv = $("#text_" + id);
	var fieldDiv = $("#field_" + id);
	if (textDiv.is(":visible")) {
		textDiv.hide();
		fieldDiv.show();
	}
	else {
		textDiv.show();
		fieldDiv.hide();
	}
}

//explain query
function explainQuery(form) {
	var params = $(form).serialize().replace(/\baction=[\w\.]+/, "");
	jQuery.ajax({
		data: params,
		success: function (data, textStatus, request) {
			$("#records").html(data);
		},
		url:explainURL,
		type:"POST"
	});
}

/** show more menus **/
function showMoreMenus(link) {
	var obj = $(link);
	setManualPosition(".menu", obj.position().left, obj.position().top + obj.height() + 4);
}

/** show manual links **/
function setManualPosition(className, x, y) {
	if ($(className).is(":visible")) {
		$(className).hide();
	}
	else {
		var s = '$("' + className + '").show().css("left", ' + x + ' - 2)';
		if (y) {
			s += '.css("top", ' + y + ')';
		}
		window.setTimeout(s, 100);
		$(className).find("a").click(function () {
			hideMenus();
		});
	}
}
 
/** hide menus **/
function hideMenus() {
	$(".menu").hide();
}

/**
 * display query fields 
 */
function showQueryFields(link) {
	var fields = $("#query_fields_list");
	fields.show();
	fields.css("left", $(link).position().left);
	fields.css("top", $(link).position().top + $(link).height() + 3);
}

/**
 * close query fields div
 */
function closeQueryFields() {
	$("#query_fields_list").hide();
	$("#query_fields_count").html(countChecked("query_fields[]"));
}

/**
 * show query hints div 
 */
function showQueryHints(link) {
	var fields = $("#query_hints_list");
	fields.show();
	fields.css("left", $(link).position().left);
	fields.css("top", $(link).position().top + $(link).height() + 3);
}

/**
 * close query hints
 */
function closeQueryHints() {
	$("#query_hints_list").hide();
	$("#query_hints_count").html($(".query_hints:checked").length);
}

/**
 * count checked boxes
 */
function countChecked(name) {
	var boxes = document.getElementsByName(name);
	var count = 0;
	for (var i = 0; i < boxes.length; i ++) {
		if (boxes[i].checked) {
			count ++;
		}
	}
	return count;
}

/**
 * display operations you can apply to a field
 */
function showFieldOperations(link, field) {
	var link = $(link);
	var menu = $("#field_menu");
	
	setManualPosition("#field_menu", link.position().left, link.position().top + link.height());
	var parent = link.parent();
	while (!parent.is(".record_row")) {
		parent = parent.parent();
	}
	
	//should show "Hide" and "Show"
	if (field == "_id") {//if we are _id, do nothing
		menu.children().hide();
		menu.find(".field_op_query").show();
		menu.find(".field_op_query span").show();
		menu.find(".field_op_indexes").show();
		menu.find(".field_op_sort").show();
	}
	else {
		menu.children().show();
		var queryFields = $("[name='query_fields[]']");
		queryFields.each(function (){
			var box = $(this);
			if (box.val() == field) {
				if (box.is(":checked")) {
					menu.find(".field_op_show")	.hide();
				}
				else {
					menu.find(".field_op_show")	.show();
				}
				return false;
			}
		});
		menu.find(".field_op_hide_show_seperator").show();
		menu.find(".field_op_hide")	.show();
	}
	
	var links = menu.find("a");
	links.attr("record_id", parent.attr("record_id"));
	links.attr("field", field);
	links.attr("record_index", parent.attr("record_index"));
}

/**
 * init menu on field
 */
function initFieldMenu() {
	//record rows
	$(".record_row font").dblclick(function(){
		return false;
	});
	$(".record_row span").dblclick(function(){
		return false;
	});
	$(".record_row span.field").click(function (){
		$(".menu_arrow").remove();
		$(".record_row span.field").css("background-color", "");
		var span = $(this);
		span.css("background-color", "#ccc");
		span.after("<a href=\"#\" style=\"font-size:11px;\" title=\"Field Operations\" onclick=\"showFieldOperations(this,'" + span.attr("field") + "');return false\" class=\"menu_arrow\">▼</a>");
	});
}

/**
 * init menu on data 
 */
function initDataMenu() {
	$(".no_string_var").click(function () {
		var font = $(this);
		var text = font.text();
		$(".menu_arrow").remove();
		if (text.match(/^[\d\.]+$/)) {
			font.after("<a href=\"#\" style=\"font-size:11px;\" title=\"View AS\" onclick=\"showDataMenu(this,'" + text + "');return false\" class=\"menu_arrow\">▼</a>");
		}
	});
}

function showDataMenu(link, text) {
	var link = $(link);
	
	var menu = [];
	var width = 100;
	if (text.match(/^[\d\.]+$/)) {
		var n = parseFloat(text);
		menu.push("ToK: " + (Math.round(n/1024*100)/100) + "K");
		menu.push("ToM: " + (Math.round(n/1024/1024*100)/100) + "M");
		menu.push("ToG: " + (Math.round(n/1024/1024/1024*100)/100) + "G");
		
		//date
		if (text.length >= 10) {
			var date =  new Date();
			date.setTime(n * 1000);
			menu.push("ToDate: " + date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() + " " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds() );
			width = 200;
		}
	}
	if (menu.length > 0) {
		$("#data_menu").remove();
		var div = "<div class=\"menu\" id=\"data_menu\">";
		for (var i = 0; i < menu.length; i ++) {
			div += menu[i] + "<br/>";
		}
		div += "</div>";
		$(document.body).append(div)[0];
		var dataMenu = $("#data_menu");
		dataMenu.css("left", link.position().left + 10);
		dataMenu.css("top", link.position().top);
		dataMenu.css("width", width);
		setTimeout(function () { $("#data_menu").show() }, 100);
	}
}

/** init the page **/
$(function () {
	$(document).click(function (e) { 
		hideMenus();
		$("#field_menu").hide();
		if (e.target.tagName == "DIV" || e.target.tagName == "P") {
			closeQueryFields();
			closeQueryHints();
		}
	});
	
	//query form
	$(".field_orders").find("input[name='field[]']").autocomplete({ 
		source:currentFields, 
		delay:100 
	});
	
	initFieldMenu();
	initDataMenu();
});

/*
 * refresh one record
 */
function refreshRecord(id, index) {
	var text = $("#text_" + index);
	var field = $("#field_" + index);
	text.text("loading ...");
	
	var params = { 
		"id": id, 
		"db":currentDb, 
		"collection":currentCollection, 
		"format":currentFormat
	};
	
	var queryFields = $("input[name='query_fields[]']:checked");
	for (var i = 0; i < queryFields.length; i ++) {
		params["query_fields[" + i + "]"] = $(queryFields[i]).val();
	}
	
	jQuery.ajax({
		type: "POST",
		url: "index.php?action=collection.record",
		data: params,
		success: function (resp) {
			if (resp.code == 200) {
				text.html(resp.html);
				field.find("textarea").val(resp.data);
				initFieldMenu();
				initDataMenu();
			}
			else {
				text.text(resp.message);
			}
		},
		dataType: "json"
	});
}

//###############field operations#####################

function switchDataType(div, type) {
	div.find("[name='data_type']").val(type);
	div.find("select[name='format']").hide();
	
	div.find(".value").hide();
	div.find(".bool_value").hide();
	div.find(".double_value").hide();
	div.find(".integer_value").hide();
	div.find(".long_value").hide();
	div.find(".mixed_value").hide();
	
	switch(type) {
		case "integer":
			div.find(".integer_value").show();
			break;
		case "long":
			div.find(".long_value").show();
			break;
		case "float":
		case "double":
			div.find(".double_value").show();
			break;
		case "string":
			div.find(".value").show();
			break;
		case "boolean":
			div.find(".bool_value").show();
			break;
		case "null":
			break;
		case "mixed":
			div.find("select[name='format']").show();
			div.find(".mixed_value").show();
			break;
	}
}

function setValueWithData(div, data) {
	var dataType = data.type;
	var value = data.value;
	
	switch(dataType) {
		case "integer":
			div.find("[name='integer_value']").val(value);
			break;
		case "long":
			div.find("[name='long_value']").val(value);
			break;
		case "float":
		case "double":
			div.find("[name='double_value']").val(value);
			break;
		case "string":
			div.find("[name='value']").val(value);
			break;
		case "boolean":
			div.find("[name='bool_value']").val(value ? "true" : "false");
			break;
		case "null":
			break;
		case "mixed":
			div.find("[name='mixed_value']").val(data.represent);
			break;
	}
}

function switchDataTypes(div) {
	var div = $(div);
	var dataType = div.find("[name='data_type']");
	switchDataType(div, dataType.val());
	dataType.change(function (){
		switchDataType(div, this.value);
	});
	
}

function escapeRegexp(pattern) {
	pattern = pattern.replace(/\./, "\\.");
	return pattern;
}

function fieldOpHide(link) {
	var link = $(link);
	var field = link.attr("field");
	var checkedCount = 0;
	$("[name='query_fields[]']").each(function (){
		var box = $(this);
		if (box.is(":checked")) {
			checkedCount ++;
		}
	});
	var subFieldPattern = new RegExp("^" + escapeRegexp(field) + "\.");
	if (checkedCount == 0) {
		$("[name='query_fields[]']").each(function (){
			var box = $(this);
			if (box.val() != field && !box.val().match(subFieldPattern)) {
				box.attr("checked", "checked");
			}
		});
	}
	else {
		$("[name='query_fields[]']").each(function (){
			var box = $(this);
			if (box.val() == field || box.val().match(subFieldPattern)) {
				box.attr("checked", "");
			}
		});
	}
	closeQueryFields();
	$("#query_form").submit();
}

function fieldOpShow(link) {
	var link = $(link);
	var field = link.attr("field");
	$("[name='query_fields[]']").each(function (){
		var box = $(this);
		if (box.val() == field) {
			box.attr("checked", "checked");
		}
	});
	closeQueryFields();
	$("#query_form").submit();
}

function fieldOpQuery(link) {
	var link = $(link);
	var id = link.attr("record_id");
	var field = link.attr("field");
	fieldOpLoad(field, id, function (data) {
		var buttons = {};
		var div = $("#field_dialog_query");
		var dataFormat = data.format;
		if (dataFormat == "json") {
			div.find("[name='field_criteria']").val('{\n\t"' + field + '": ' + data.represent + '\n}');
		}
		else {
			div.find("[name='field_criteria']").val('array(\n\t"' + field + '" => ' + data.represent + ',\n);');
		}
		buttons["Query"] = function (){
			$("textarea[name='criteria']").val(div.find("[name='field_criteria']").val());
			$("#query_form").find("input[name='format']").val(div.find("select[name='format']").val());
			$("#query_form").submit();
			$(this).dialog("close");
		}
		buttons["Cancel"] = function() {
			$(this).dialog("close");
		};
		
		div.dialog({
			"modal": true,
			"title": "Query on field \"" + field + "\"",
			"buttons":buttons,
			"width": 420
		});
	});
}

/**
 * create new field
 * 
 * @param string link fire link
 * @param string id record id
 * @param string field field name
 * @param integer recordIndex record index
 */
function fieldOpNew(link, id, field, recordIndex) {
	if (link) {
		var link = $(link);
		var id = link.attr("record_id");
		var field = link.attr("field");
		var recordIndex = link.attr("record_index");
	}
	var buttons = {};
	var div = $("#field_dialog_new");
	switchDataTypes(div);
	if (typeof(field)!="undefined" && field.length > 0) {
		div.find("input[name='newname']").val(field + ".");
	}
	else {
		div.find("input[name='newname']").val("");
	}
	if (id) {
		buttons["Apply"] = function () {
			jQuery.ajax({
				data: { 
					"id":id, 
					"db":currentDb, 
					"collection":currentCollection, 
					"newname": div.find("input[name='newname']").val(),
					"keep":div.find("input[name='keep']:checked").val(),
					"data_type":div.find("[name='data_type']").val(),
					"value":div.find("[name='value']").val(),
					"integer_value":div.find("[name='integer_value']").val(),
					"long_value":div.find("[name='long_value']").val(),
					"double_value":div.find("[name='double_value']").val(),
					"bool_value":div.find("[name='bool_value']").val(),
					"mixed_value":div.find("[name='mixed_value']").val(),
					"format": div.find("[name='format']").val()
				},
				success: function (data, textStatus, request) {
					if (data.code != 200) {
						alert(data.message);
					}
					else {
						div.dialog("close");
						if (typeof(recordIndex) != "undefined") {
							refreshRecord(id, recordIndex);
						}
						else {
							window.location.reload();
						}
					}
				},
				url:"index.php?action=field.new",
				type:"POST",
				dataType:"json"
			});
		};
	}
	buttons["Apply to all"] = function (){
		if (!window.confirm("The changes will be applied to all records")) {
			return;
		}
		jQuery.ajax({
			data: { 
				"id":"", 
				"db":currentDb, 
				"collection":currentCollection,  
				"newname": div.find("input[name='newname']").val(),
				"keep":div.find("input[name='keep']:checked").val(),
				"data_type":div.find("[name='data_type']").val(),
				"value":div.find("[name='value']").val(),
				"integer_value":div.find("[name='integer_value']").val(),
				"long_value":div.find("[name='long_value']").val(),
				"double_value":div.find("[name='double_value']").val(),
				"bool_value":div.find("[name='bool_value']").val(),
				"mixed_value":div.find("[name='mixed_value']").val(),
				"format": div.find("[name='format']").val()
			},
			success: function (data, textStatus, request) {
				if (data.code != 200) {
					alert(data.message);
				}
				else {
					div.dialog("close");
					window.location.reload();
				}
			},
			url:"index.php?action=field.new",
			type:"POST",
			dataType:"json"
		});
	}
	buttons["Cancel"] = function() {
		$(this).dialog("close");
	};
	
	div.dialog({
		"modal": true,
		"title": "Add new field",
		"buttons":buttons,
		"width": 450
	});
}

/**
 * load field data
 */
function fieldOpLoad(field, id, func) {
	jQuery.ajax({
		data: { 
			"id":id, 
			"db":currentDb, 
			"collection":currentCollection, 
			"field": field
		},
		success: function (data, textStatus, request) {
			if (data.code != 200) {
				alert(data.message);
			}
			else {
				func(data);
			}
		},
		url:"index.php?action=field.load",
		type:"POST",
		dataType:"json"
	});
}

/**
 * update a field
 * 
 * @param object link menu link
 * @param mixed id record id
 * @param string field field name
 * @param integer recordIndex record index
 */
function fieldOpUpdate(link, id, field, recordIndex) {
	if (link) {
		var link = $(link);
		var id = link.attr("record_id");
		var field = link.attr("field");
		var recordIndex = link.attr("record_index");
	}
	fieldOpLoad(field, id, function (data) {
		var buttons = {};
		var div = $("#field_dialog_update");
		switchDataTypes(div);
		switchDataType(div, data.type);
		setValueWithData(div, data);
		div.find("input[name='newname']").val(field);
		if (id) {
			buttons["Apply"] = function () {
				jQuery.ajax({
					data: { 
						"id":id, 
						"db":currentDb, 
						"collection":currentCollection, 
						"newname": div.find("input[name='newname']").val(),
						"data_type":div.find("[name='data_type']").val(),
						"value":div.find("[name='value']").val(),
						"integer_value":div.find("[name='integer_value']").val(),
						"long_value":div.find("[name='long_value']").val(),
						"double_value":div.find("[name='double_value']").val(),
						"bool_value":div.find("[name='bool_value']").val(),
						"mixed_value":div.find("[name='mixed_value']").val(),
						"format": div.find("[name='format']").val()
					},
					success: function (data, textStatus, request) {
						if (data.code != 200) {
							alert(data.message);
						}
						else {
							div.dialog("close");
							if (typeof(recordIndex) != "undefined") {
								refreshRecord(id, recordIndex);
							}
							else {
								window.location.reload();
							}
						}
					},
					url:"index.php?action=field.update",
					type:"POST",
					dataType:"json"
				});
			};
		}
		buttons["Apply to all"] = function (){
			if (!window.confirm("The changes will be applied to all records")) {
				return;
			}
			jQuery.ajax({
				data: { 
					"id":"", 
					"db":currentDb, 
					"collection":currentCollection,  
					"newname": div.find("input[name='newname']").val(),
					"data_type":div.find("[name='data_type']").val(),
					"value":div.find("[name='value']").val(),
					"integer_value":div.find("[name='integer_value']").val(),
					"long_value":div.find("[name='long_value']").val(),
					"double_value":div.find("[name='double_value']").val(),
					"bool_value":div.find("[name='bool_value']").val(),
					"mixed_value":div.find("[name='mixed_value']").val(),
					"format": div.find("[name='format']").val()
				},
				success: function (data, textStatus, request) {
					if (data.code != 200) {
						alert(data.message);
					}
					else {
						div.dialog("close");
						window.location.reload();
					}
				},
				url:"index.php?action=field.update",
				type:"POST",
				dataType:"json"
			});
		}
		buttons["Cancel"] = function() {
			$(this).dialog("close");
		};
		
		div.dialog({
			"modal": true,
			"title": "Modify field \"" + field + "\" value",
			"buttons":buttons,
			"width": 450
		});
	});
}

function fieldOpRename(link) {
	var link = $(link);
	var field = link.attr("field");
	var id = link.attr("record_id");
	var recordIndex = link.attr("record_index");
	$(".dialog_field").html(field);
	var div = $("#field_dialog_rename");
	var buttons = {};
	if (id) {
		buttons["Apply"] = function () {
			jQuery.ajax({
				data: { 
					"id":id, 
					"field":field, 
					"db":currentDb, 
					"collection":currentCollection, 
					"newname": div.find("input[name='newname']").val(),
					"keep": div.find("input[name='keep']:checked").val()
				},
				success: function (data, textStatus, request) {
					if (data.code != 200) {
						alert(data.message);
					}
					else {
						div.dialog("close");
						if (typeof(recordIndex) != "undefined") {
							refreshRecord(id, recordIndex);
						}
						else {
							window.location.reload();
						}
					}
				},
				url:"index.php?action=field.rename",
				type:"POST",
				dataType:"json"
			});
		};
	}
	buttons["Apply to all"] = function (){
		if (!window.confirm("The changes will be applied to all records")) {
			return;
		}
		jQuery.ajax({
			data: { 
				"id":"", 
				"field":field, 
				"db":currentDb, 
				"collection":currentCollection,  
				"newname": div.find("input[name='newname']").val(),
				"keep":div.find("input[name='keep']:checked").val()
			},
			success: function (data, textStatus, request) {
				if (data.code != 200) {
					alert(data.message);
				}
				else {
					div.dialog("close");
					window.location.reload();
				}
			},
			url:"index.php?action=field.rename",
			type:"POST",
			dataType:"json"
		});
	}
	buttons["Cancel"] = function() {
		$(this).dialog("close");
	};
	
	div.dialog({
		"modal": true,
		"title": "Rename field \"" + field + "\"",
		"buttons":buttons,
		"width": 420
	});
}

function fieldOpRemove(link) {
	var link = $(link);
	var field = link.attr("field");
	var id = link.attr("record_id");
	var recordIndex = link.attr("record_index");
	var div = $("#field_dialog_remove");
	$(".dialog_field").html(field);
	var buttons = {};
	if (id) {
		buttons["Apply"] = function () {
			jQuery.ajax({
				data: { "id":id, "field":field, "db":currentDb, "collection":currentCollection },
				success: function (data, textStatus, request) {
					div.dialog("close");
					if (typeof(recordIndex) != "undefined") {
						refreshRecord(id, recordIndex);	
					}
					else {
						window.location.reload();
					}
				},
				url:"index.php?action=field.remove",
				type:"POST"
			});
		};
	}
	buttons["Apply to all"] = function (){
		if (!window.confirm("The changes will be applied to all records")) {
			return;
		}
		jQuery.ajax({
			data: { "id":"", "field":field, "db":currentDb, "collection":currentCollection },
			success: function (data, textStatus, request) {
				div.dialog("close");
				window.location.reload();
			},
			url:"index.php?action=field.remove",
			type:"POST"
		});
	}
	buttons["Cancel"] = function() {
		$(this).dialog("close");
	};
	
	div.dialog({
		"modal": true,
		"title": "Remove field \"" + field + "\"",
		"buttons":buttons,
		"width": 420
	});
}

function fieldOpClear(link) {
	var link = $(link);
	var field = link.attr("field");
	var id = link.attr("record_id");
	var recordIndex = link.attr("record_index");
	var div = $("#field_dialog_clear");
	$(".dialog_field").html(field);
	var buttons = {};
	if (id) {
		buttons["Apply"] = function () {
			jQuery.ajax({
				data: { "id":id, "field":field, "db":currentDb, "collection":currentCollection },
				success: function (data, textStatus, request) {
					div.dialog("close");
					if (typeof(recordIndex) != "undefined") {
						refreshRecord(id, recordIndex);	
					}
					else {
						window.location.reload();
					}
				},
				url:"index.php?action=field.clear",
				type:"POST"
			});
		};
	}
	buttons["Apply to all"] = function (){
		if (!window.confirm("The changes will be applied to all records")) {
			return;
		}
		jQuery.ajax({
			data: { "id":"", "field":field, "db":currentDb, "collection":currentCollection },
			success: function (data, textStatus, request) {
				div.dialog("close");
				window.location.reload();
			},
			url:"index.php?action=field.clear",
			type:"POST"
		});
	}
	buttons["Cancel"] = function() {
		$(this).dialog("close");
	};
	
	div.dialog({
		"modal": true,
		"title": "Clear field \"" + field + "\"",
		"buttons":buttons,
		"width": 420
	});
}

function fieldOpIndexes(link) {
	var link = $(link);
	var field = link.attr("field");
	var div = $("#field_dialog_indexes");
	div.find(".dialog_field").html(field);
	div.find(".first_field").val(field);
	div.find("#fields").html("");
	div.find("[name='name']").val(field);
	div.find("[name='field[]']").autocomplete({ source: currentFields, delay:100 });
	jQuery.ajax({
		data: {"field": field, "db":currentDb, "collection":currentCollection },
		success: function (data, textStatus, request) {
			var indexesBody = div.find(".indexes");
			indexesBody.html("");
			if (data.indexes.length == 0) {
				div.find(".indexes_table").hide();
			}
			else {
				div.find(".indexes_table").show();
				for (var i in data.indexes) {
					var index = data.indexes[i];
					indexesBody.append('<tr bgcolor="#ffffff"><td valign="top">' + index.name + '</td><td valign="top">' + index.key + '</td></tr>');
				}
			}
			
			var buttons = {};
			buttons["Create"] = function () {
				//parameters
				var params = {};
				params["db"] = currentDb;
				params["collection"] = currentCollection;
				params["name"] = div.find("[name='name']").val();
				params["is_unique"] = div.find("[name='is_unique']:checked").val();
				params["drop_duplicate"] = div.find("[name='drop_duplicate']:checked").val();
				
				var fields = div.find("[name='field[]']");
				for (var i = 0; i < fields.length; i ++) {
					params["field[" + i + "]"] = $(fields[i]).val();
				}
				
				var orders = div.find("[name='order[]']");
				for (var i = 0; i < orders.length; i ++) {
					params["order[" + i + "]"] = $(orders[i]).val();
				}
				
				jQuery.ajax({
					url: "index.php?action=field.createIndex",
					type: "POST",
					dataType: "json",
					data: params,
					success: function (resp) {
						if (resp.code != 200) {
							alert(resp.message);
						}
						else {
							setTimeout(function () { fieldOpIndexes(link); }, 300)//reload data
						}
					}
				});
			};
			buttons["Cancel"] = function() {
				$(this).dialog("close");
			};
			div.dialog({
				"modal": true,
				"title": "Indexes on field \"" + field + "\"",
				"buttons":buttons,
				"width": 450
			});
		},
		url: "index.php?action=field.indexes",
		type: "POST",
		dataType: "json"
	});
}

/**
 * show more text
 */
function fieldOpMore(field, id) {
	fieldOpUpdate(null, id, field)
}

/**
 * sort by a field
 */
function fieldOpSort(link, order) {
	var link = $(link);
	var field = link.attr("field");
	var url = window.location.toString();
	var pieces = url.split("?", 2);
	
	if (pieces.length == 1) {
		window.location = url + "&field[]=" + field + "&order[]=" + order;
	}
	else {
		var params = pieces[1].split("&");
		var newQuery = "";
		var fieldIndex = 0;
		var orderIndex = 0;
		var theFieldIndex = 0;//field to be operated
		for (var i in params) {
			var param = params[i];
			var kv = param.split("=", 2);
			if (kv.length == 2) {
				if (decodeURI(kv[0]).match(new RegExp("^field\\[\\d*\\]$"))) {
					fieldIndex ++;
					if (kv[1] != field) {
						newQuery += "&field[]=" + kv[1];
					}
					else {
						theFieldIndex = fieldIndex;
					}
				}
				else if (decodeURI(kv[0]).match(new RegExp("^order\\[\\d*\\]$"))) {
					orderIndex ++;
					if (orderIndex != theFieldIndex) {
						newQuery += "&order[]=" + kv[1];
					}
				}
				else {
					newQuery += "&" + param;
				}
			}
		}
		
		window.location = pieces[0] + "?" + "field[]=" + field + "&order[]=" + order + newQuery;
	}
}

function addNewField() {
	$("#fields").append("<p style=\"margin:0;padding:0\"><input type=\"text\" name=\"field[]\"/> <select name=\"order[]\"><option value=\"asc\">ASC</option><option value=\"desc\">DESC</option></select> <input type=\"button\" value=\"+\" onclick=\"addNewField()\"/><input type=\"button\" value=\"-\" onclick=\"removeNewField(this)\"/></p>");
	$("#fields").find("[name='field[]']").autocomplete({ source: currentFields, delay:100 });
}

function removeNewField(btn) {
	$(btn).parent().remove();
}

function clickUniqueKey(box) {
	if (box.checked) {
		$("#duplicate_tr").show();
	}
	else {
		$("#duplicate_tr").hide();
	}
}

/**
 * show query history when log_query feature is on
 */
function showQueryHistory() {
	var div = $("#field_dialog_history");
	jQuery.ajax({
		"data": { "db":currentDb, "collection":currentCollection },
		"dataType": "html",
		"url": "index.php?action=collection.queryHistory",
		"success": function (data) {
			div.html(data);
			div.dialog({
				"modal": true,
				"title": "Query History",
				//"buttons":buttons,
				"width": 450
			});
		}
		
	});
	
}

/**
 * switch format between json and array
 * 
 * @param object input data input element
 * @param object select select element
 */
function switchDataFormat(input, select) {
	$.ajax({
		"data": { "data": $(input).val(), "format":select.value },
		"url": "index.php?action=collection.switchFormat&db=" + currentDb + "&collection=" + currentCollection,
		"type": "POST",
		"dataType": "json",
		"success": function (resp) {
			$(input).val(resp.data);
		}
	});
}

/**
 * Show more document operations
 * @param object button "more" button
 * @param integer index document index
 */
function showMoreDocMenus(button, index) {
	var obj = $(button);
	var className = ".doc_menu_" + index;
	var x = obj.position().left;
	var y = obj.position().top + obj.height() + 4;
	if ($(className).is(":visible")) {
		$(className).hide();
	}
	else {
		window.setTimeout(function () {
			$(".doc_menu").hide();
			$(className).show();
			$(className).css("left", x);
			$(className).css("top", y)
		}, 100);
		$(className).find("a").click(function () {
			showMoreDocMenus(button, index);
		});
	}
}
$(function () {
	$(document).click(function () {
		$(".doc_menu").hide();
	});
});