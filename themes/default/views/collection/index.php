<!-- Global configuration -->
<script language="javascript">
var explainURL = "<?php h(url("collection.explainQuery"));?>";
var currentURL = "<?php h($_SERVER["REQUEST_URI"]); ?>";
var currentDb = "<?php h($db);?>";
var currentCollection = "<?php h($collection); ?>";
var currentFormat = "<?php h(x("format")); ?>";
var currentRecordsCount = <?php echo $recordsCount;?>;
var currentFields = new Array();
<?php foreach ($nativeFields as $field): if($field == "_id") {continue;}  ?>
currentFields.push("<?php h(addslashes($field));?>");
<?php endforeach;?>
</script>

<!-- Import resources -->
<script language="javascript" src="js/collection.js?v=<?php h(filemtime("js/collection.js")) ?>"></script>
<script language="javascript" src="js/jquery-ui-1.8.4.custom.min.js"></script>
<link rel="stylesheet" href="<?php render_theme_path() ?>/css/collection.css" media="all"/>
<link rel="stylesheet" href="<?php render_theme_path() ?>/css/jquery-ui-1.8.4.smoothness.css" media="all"/>

<a name="page_top"></a>

<!-- Navigation & Menu -->
<h3><?php render_navigation($db, $collection, false);?></h3>
<div class="operation">
	<strong><?php hm("query"); ?></strong>[<a href="<?php h($arrayLink);?>" <?php if(x("format")=="array"):?>style="text-decoration:underline"<?php endif;?>>Array</a>|<a href="<?php h($jsonLink); ?>" <?php if(x("format")!="array"):?>style="text-decoration:underline"<?php endif;?>>JSON</a></a>] |  <?php if ($_logQuery): ?><a href="#" onclick="showQueryHistory();return false;">History</a> | <?php endif;?>
	<a href="<?php h($_SERVER["REQUEST_URI"]);?>"><?php hm("refresh"); ?></a> |
	<?php render_collection_menu($db, $collection) ?>
</div>

<!-- Query box -->
<div class="query">
<form method="get" id="query_form">
<input type="hidden" name="db" value="<?php h_escape($db);?>"/>
<input type="hidden" name="collection" value="<?php h_escape($collection);?>"/>
<input type="hidden" name="action" value="<?php h_escape(x("action"));?>"/>
<input type="hidden" name="format" value="<?php h_escape(x("format")); ?>"/>
<table>
	<tr>
		<td valign="top">
			<textarea name="criteria" rows="5" cols="70" style="height:100px"><?php h(x("criteria"));?></textarea><br/>
			<div id="newobjInput" <?php if (x("command") !="modify"):?>style="display:none"<?php endif;?>>
				New Object(see <a href="http://www.mongodb.org/display/DOCS/Updating" target="_blank">Updating</a> operators):<br/>
				<textarea name="newobj" rows="5" cols="70"><?php h(x("newobj"));?></textarea>
			</div>
		</td>
		<td valign="top" class="field_orders">
			<!-- fields will be used in sorting -->
			<p><input type="text" name="field[]" value="<?php h_escape(rock_array_get(x("field"),0));?>" /> <select name="order[]"><option value="asc" <?php if (rock_array_get(x("order"),0)=="asc"):?>selected="selected"<?php endif;?>>ASC</option><option value="desc" <?php if (rock_array_get(x("order"),0)=="desc"):?>selected="selected"<?php endif;?>>DESC</option></select></p>
			<p><input type="text" name="field[]" value="<?php h_escape(rock_array_get(x("field"),1));?>" /> <select name="order[]"><option value="asc" <?php if (rock_array_get(x("order"),1)=="asc"):?>selected="selected"<?php endif;?>>ASC</option><option value="desc" <?php if (rock_array_get(x("order"),1)=="desc"):?>selected="selected"<?php endif;?>>DESC</option></select></p>
			<p><input type="text" name="field[]" value="<?php h_escape(rock_array_get(x("field"),2));?>" /> <select name="order[]"><option value="asc" <?php if (rock_array_get(x("order"),2)=="asc"):?>selected="selected"<?php endif;?>>ASC</option><option value="desc" <?php if (rock_array_get(x("order"),2)=="desc"):?>selected="selected"<?php endif;?>>DESC</option></select></p>
			<p><input type="text" name="field[]" value="<?php h_escape(rock_array_get(x("field"),3));?>" /> <select name="order[]"><option value="asc" <?php if (rock_array_get(x("order"),3)=="asc"):?>selected="selected"<?php endif;?>>ASC</option><option value="desc" <?php if (rock_array_get(x("order"),3)=="desc"):?>selected="selected"<?php endif;?>>DESC</option></select> </p>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<!-- query fields and hints -->
			<span id="fieldsAndHints" <?php if (x("command") !="findAll"):?>style="display:none"<?php endif;?>>
			<?php if(!empty($nativeFields)):?>
				<a href="#" onclick="showQueryFields(this);return false;" title="Choose fields to display">Fields(<span id="query_fields_count"><?php h(count($queryFields));?></span>) <span style="font-size:11px">▼</span></a> |
				<a href="#" onclick="showQueryHints(this);return false;" title="Choose indexes will be used in query">Hints(<span id="query_hints_count"><?php h(count($queryHints));?></span>)  <span style="font-size:11px">▼</span></a> |
			<?php endif; ?>
			</span>
			<!-- end query fields and hints -->

			<label id="limitLabel" <?php if (x("command") !="findAll"):?>style="display:none"<?php endif;?>><?php hm("limit"); ?>:<input type="text" name="limit" size="5" value="<?php h(xi("limit"));?>"/> |</label>
			<span id="pageSetLabel" <?php if (x("command") !="findAll"):?>style="display:none"<?php endif;?>>
			<select name="pagesize" title="<?php hm("rows_per_page"); ?>">
			<?php foreach (array(10, 15, 20, 30, 50, 100, 200) as $pagesize):?>
				<option value="<?php h($pagesize);?>" <?php if(x("pagesize")==$pagesize):?>selected="selected"<?php endif;?>>Rows:<?php h($pagesize);?></option>
			<?php endforeach;?>
			</select> |</span>
			<?php hm("action"); ?>:
			<select name="command" onchange="changeCommand(this)">
				<option value="findAll" <?php if(x("command")=="findAll"):?>selected="selected"<?php endif;?>>findAll</option>
				<option value="remove" <?php if(x("command")=="remove"):?>selected="selected"<?php endif;?>>remove</option>
				<option value="modify" <?php if(x("command")=="modify"):?>selected="selected"<?php endif;?>>modify</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="submit" value="<?php hm("submit_query"); ?>"/>
			<input type="button" value="<?php hm("explain"); ?>" onclick="explainQuery(this.form)" />
			<input type="button" value="<?php hm("clear_conditions"); ?>" onclick="window.location='<?php h(url("collection.index", array( "db"=>$db, "collection" => $collection, "format" => xn("format") ))); ?>'"/>
			[<a href="http://rockmongo.com/wiki/queryExamples?lang=en_us" target="_blank">Query Examples</a>]
			<?php if(isset($cost)):?>Cost <?php h(round($cost, 6));?>s<?php endif;?>
			<?php if(isset($message)):?><p class="error"><?php h($message);?></p><?php endif;?></td>
	</tr>
</table>

<!-- float divs (for fields and hints) -->
<div id="query_fields_list" class="fieldsmenu">
	<div align="right" style="padding-right:10px;background:#999"><a href="#" onclick="closeQueryFields();return false;" title="Click to close"><img src="<?php render_theme_path() ?>/images/accept.png" width="14"/></a></div>
	<ul>
	<?php foreach ($nativeFields as $field): if($field == "_id") {continue;}  ?>
		<li><label>
			<input type="checkbox" name="query_fields[]" value="<?php h($field); ?>"
				<?php if(in_array($field,$queryFields)||$field=="_id"): ?>checked="checked"<?php endif;?>
				<?php if($field=="_id"): ?>disabled="disabled"<?php endif?>
			/> <?php h($field); ?></label></li>
	<?php endforeach; ?>
	</ul>
</div>
<div id="query_hints_list" class="fieldsmenu">
	<div align="right" style="padding-right:10px;background:#999"><a href="#" onclick="closeQueryHints();return false;" title="Click to close"><img src="<?php render_theme_path() ?>/images/accept.png" width="14"/></a></div>
	<ul>
	<?php foreach ($indexFields as $index => $field):?>
		<li title="<?php h(htmlspecialchars($field["keystring"])); ?>"><label><input type="checkbox" name="query_hints[<?php h($index); ?>]" value="<?php h($field["name"]); ?>" <?php if(in_array($field["name"],$queryHints)): ?>checked="checked"<?php endif;?> class="query_hints" /> <?php h($field["name"]); ?></label></li>
	<?php endforeach; ?>
	</ul>
</div>
<!-- end float divs -->

</form>
</div>

<!-- Records in collection -->
<div id="records">
	<?php if(!isset($page) || $page->total() == 0):?>
		<?php if (x("command") != "findAll"):?>
			<p><?php if (isset($count)): h($count);?> rows may be affected.<?php endif;?></p>
		<?php else:?>
			<p>No records is found.</p>
		<?php endif;?>
	<?php else: ?>
		<p class="page"><?php h($page); ?> (<?php h(min($page->total(), $page->offset()+$page->size()));?>/<?php h($page->total());?>)</p>

		<!-- list all records -->
		<?php foreach ($rows as $index => $row):?>
		<div style="border:2px #ccc solid;margin-bottom:5px;" onmouseover="showOperationButtons('<?php h($index);?>')" onmouseout="hideOperationButtons('<?php h($index);?>')" class="record" <?php if(MCollection::isFile($row)): ?>r-is-file="yes" r-file-name="<?php h($row["filename"]) ?>"<?php endif; ?>>
			<table width="100%" border="0" id="object_<?php h($index);?>">
				<tr>
					<td valign="top" width="50">#<?php echo $page->total() - $page->offset() - $index; ?></td>
					<td valign="top">
					<!-- operations on record -->
					<div class="operation" id="operate_<?php h($index);?>">
						<!-- you can modify row only when _id is not empty -->
							<?php if($row["can_modify"]):?>
								<a href="<?php echo url("collection.modifyRow", array(
									"db" => $db,
									"collection" => $collection,
									"id" => rock_id_string($row["_id"]),
									"uri" => $_SERVER["REQUEST_URI"]
								)); ?>"><?php hm("update"); ?></a> |
							<?php endif; ?>

							<?php if($row["can_delete"]): ?>
								<a href="<?php echo url("collection.deleteRow", array(
									"db" => $db,
									"collection" => $collection,
									"id" => rock_id_string($row["_id"]),
									"uri" => $_SERVER["REQUEST_URI"]
								));
								?>" onclick="return window.confirm('Are you sure to delete the row #<?php echo $page->total() - $page->offset() - $index; ?>?');"><?php hm("delete"); ?></a> |
							<?php else: ?>
								<a href="#" class="disabled" onclick="return false;"><?php echo hm("delete"); ?></a> |
							<?php endif; ?>

							<?php if ($row["can_add_field"]): ?>
								<a href="#" onclick="fieldOpNew(null,'<?php h(rock_id_string($row["_id"])); ?>','',<?php h($index); ?>);return false;"><?php echo hm("new_field"); ?></a> |
							<?php else: ?>
								<a href="#" class="disabled" onclick="return false;"><?php echo hm("new_field"); ?></a> |
							<?php endif; ?>

							<?php if ($row["can_duplicate"]): ?>
								<a href="<?php h(url("collection.createRow", array(
									"db" => $db,
									"collection" => $collection,
									"id" => rock_id_string($row["_id"]),
									"uri" => $_SERVER["REQUEST_URI"]
								))); ?>"><?php hm("duplicate"); ?></a> |
							<?php endif; ?>
							<?php if ($row["can_refresh"]): ?>
								<a href="#" onclick="refreshRecord('<?php h(rock_id_string($row["_id"])) ?>',<?php h($index); ?>);return false;"><?php hm("refresh"); ?></a> |
							<?php endif; ?>
						<!-- render operation menu -->
						<?php render_doc_menu($db, $collection, isset($row["_id"]) ? rock_id_string($row["_id"]) : 0, $index) ?>

						<!-- for gridfs -->
						<?php if(MCollection::isFile($row)):?>
						| GridFS: <a href="<?php
						h(url("collection.downloadFile", array(
							"db" => $db,
							"collection" => $collection,
							"id" => rock_id_string($row["_id"]),
						)));
						?>">Download</a> <a href="<?php
						$criteria = null;
						if ($this->last_format == "json") {
							$criteria = '{
	"files_id": ' . (($row["_id"] instanceof MongoId) ? "ObjectId(\"" . addslashes($row["_id"]->__toString()) . "\")" : "\"" . addslashes($row["_id"]) . "\"") . '
}';
						}
						else {
							$criteria = 'array(
	"files_id" => ' . (($row["_id"] instanceof MongoId) ? "new MongoId(\"" . addslashes($row["_id"]->__toString()) . "\")" : "\"" . addslashes($row["_id"]) . "\"") . '
)';
						}
						h(url("collection.index", array(
							"db" => $db,
							"collection" => MCollection::chunksCollection($collection),
							"criteria" => $criteria)));
						?>">Chunks</a>
						<?php endif;?>
					</div>

					<!-- display record -->
					<div id="text_<?php h($index);?>" style="max-height:150px;overflow-y:hidden;width:99%;" ondblclick="expandText('<?php h($index);?>');" class="record_row" record_id="<?php if(isset($row["_id"])){h(rock_id_string($row["_id"]));} ?>" record_index="<?php h($index); ?>">

						<?php h($row["data"]); ?>
					</div>

					<!-- switch to text so we can copy it easieer -->
					<div id="field_<?php h($index);?>" style="display:none;max-height:150px;overflow-y:auto"><textarea rows="7" cols="60" ondblclick="this.select()" title="Double click to select all"><?php h($row["text"]);?></textarea></div>

					<div align="right" style="margin-top:-14px"><a href="#page_top">TOP</a></div>
				</td>
				</tr>
			</table>
		</div>
		<?php endforeach; ?>

		<p class="page"><?php h($page); ?></p>
	<?php endif;?>
</div>

<!-- field menu -->
<div id="field_menu">
	<a href="#" onclick="fieldOpUpdate(this);return false;" class="field_op_update">Update</a>
	<a href="#" onclick="fieldOpQuery(this);return false;" class="field_op_query">Query</a>
	<a href="#" onclick="fieldOpSort(this, 'asc');return false;" class="field_op_sort">SortASC</a>
	<a href="#" onclick="fieldOpSort(this, 'desc');return false;" class="field_op_sort">SortDESC</a>
	<span>------</span>
	<a href="#" onclick="fieldOpRename(this);return false;" class="field_op_rename">Rename</a>
	<a href="#" onclick="fieldOpRemove(this);return false;" class="field_op_remove">Remove</a>
	<a href="#" onclick="fieldOpClear(this);return false;" class="field_op_clear">Clear</a>
	<?php if($canAddField): ?>
	<span>------</span>
	<a href="#" onclick="fieldOpNew(this);return false;" class="field_op_new">New</a>
	<?php endif; ?>
	<span>------</span>
	<a href="#" onclick="fieldOpIndexes(this);return false;" class="field_op_indexes">Indexes</a>
	<span class="field_op_hide_show_seperator">------</span>
	<a href="#" onclick="fieldOpHide(this);return false;" class="field_op_hide">Hide</a>
	<a href="#" onclick="fieldOpShow(this);return false;" class="field_op_show">Show</a>
</div>


<!-- dialogs goes below -->
<div id="field_dialog_remove" style="display:none">
Are you sure to remove field "<span class="dialog_field"></span>"?
</div>

<div id="field_dialog_clear" style="display:none">
Are you sure to set field "<span class="dialog_field"></span>" to NULL?
</div>

<div id="field_dialog_rename" style="display:none">
<table>
	<tr>
		<td>New Name:</td>
		<td><input type="text" name="newname" value="" size="22"/></td>
	</tr>
	<tr>
		<td>Keep exists:</td>
		<td><label><input type="checkbox" name="keep" value="1" checked="checked"/></label></td>
	</tr>
</table>
</div>

<!-- create new field -->
<div id="field_dialog_new" style="display:none">
<table>
	<tr>
		<td>Field name:</td>
		<td><input type="text" name="newname" size="22"/></td>
	</tr>
	<tr>
		<td nowrap>Keep exists:</td>
		<td><label><input type="checkbox" name="keep" value="1" checked="checked"/></label></td>
	</tr>
	<tr>
		<td>Data Type:</td>
		<td><?php render_select_data_types("data_type", "string"); ?> <select name="format" onchange="switchDataFormat('#field_dialog_new textarea[name=\'mixed_value\']',this)" style="display:none">
<option value="array" <?php if($last_format=="array"): ?>selected="selected"<?php endif; ?>>Array</option>
<option value="json" <?php if($last_format=="json"): ?>selected="selected"<?php endif; ?>>JSON</option>
</select></td>
	</tr>
	<tr class="value">
		<td valign="top">Value:</td>
		<td><textarea name="value" rows="10" cols="50"></textarea></td>
	</tr>
	<tr class="bool_value">
		<td valign="top">Value:</td>
		<td><select name="bool_value"><option value="true">True</option><option value="false">False</option></select></td>
	</tr>
	<tr class="integer_value">
		<td valign="top">Value:</td>
		<td><input type="text" name="integer_value"/></td>
	</tr>
	<tr class="long_value">
		<td valign="top">Value:</td>
		<td><input type="text" name="long_value"/></td>
	</tr>
	<tr class="double_value">
		<td valign="top">Value:</td>
		<td><input type="text" name="double_value"/></td>
	</tr>
	<tr class="mixed_value">
		<td valign="top">Value:</td>
		<td><textarea name="mixed_value" rows="10" cols="50"><?php if($last_format=="array"): ?>array(

)<?php else: ?>{

}<?php endif; ?></textarea><br/> * An array or object</td>
	</tr>
</table>
</div>

<!-- update field data -->
<div id="field_dialog_update" style="display:none">
<table>
	<tr>
		<td>Field name:</td>
		<td><input type="text" name="newname" size="22" disabled="disabled"/></td>
	</tr>
	<tr>
		<td>Data Type:</td>
		<td><?php render_select_data_types("data_type", "integer"); ?> <select name="format" onchange="switchDataFormat('#field_dialog_update textarea[name=\'mixed_value\']',this)" style="display:none">
<option value="array" <?php if($last_format=="array"): ?>selected="selected"<?php endif; ?>>Array</option>
<option value="json" <?php if($last_format=="json"): ?>selected="selected"<?php endif; ?>>JSON</option>
</select></td>
	</tr>
	<tr class="value">
		<td valign="top">Value:</td>
		<td><textarea name="value" rows="10" cols="50"></textarea></td>
	</tr>
	<tr class="bool_value">
		<td valign="top">Value:</td>
		<td><select name="bool_value"><option value="true">True</option><option value="false">False</option></select></td>
	</tr>
	<tr class="integer_value">
		<td valign="top">Value:</td>
		<td><input type="text" name="integer_value"/></td>
	</tr>
	<tr class="long_value">
		<td valign="top">Value:</td>
		<td><input type="text" name="long_value"/></td>
	</tr>
	<tr class="double_value">
		<td valign="top">Value:</td>
		<td><input type="text" name="double_value"/></td>
	</tr>
	<tr class="mixed_value">
		<td valign="top">Value:</td>
		<td><textarea name="mixed_value" rows="10" cols="50"><?php if($last_format=="array"): ?>array(

)<?php else: ?>{

}<?php endif; ?></textarea><br/> * An array or object</td>
	</tr>
</table>
</div>

<!-- Query field dialog -->
<div id="field_dialog_query" style="display:none">
<table>
	<tr>
		<td valign="top">Criteria <select name="format" onchange="switchDataFormat('#field_dialog_query textarea[name=\'field_criteria\']',this)">
<option value="array" <?php if($last_format=="array"): ?>selected="selected"<?php endif; ?>>Array</option>
<option value="json" <?php if($last_format=="json"): ?>selected="selected"<?php endif; ?>>JSON</option>
</select></td>
	</tr>
	<tr>
		<td>
			<textarea name="field_criteria" rows="10" cols="50"></textarea>
		</td>
	</tr>
</table>
</div>

<!-- Field related indexes dialog -->
<div id="field_dialog_indexes" style="display:none">
<table bgcolor="#cccccc" width="400" cellpadding="2" cellspacing="1" class="indexes_table">
	<tr>
		<td colspan="2">Indexes contains <span class="dialog_field"></span></td>
	</tr>
	<tbody class="indexes"></tbody>
</table>
<br/>
<table bgcolor="#cccccc" width="400" cellpadding="2" cellspacing="1">
	<tr>
		<td colspan="2">Create new Index</td>
	</tr>
	<tr bgcolor="#ffffff">
		<td width="100">Name</td>
		<td><input type="text" name="name"/></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td valign="top">Fields</td>
		<td><input type="text" name="field[]" class="first_field"/> <select name="order[]"><option value="asc">ASC</option><option value="desc">DESC</option></select> <input type="button" value="+" onclick="addNewField()"/><div id="fields"></div></td>
	</tr>
	<tr bgcolor="#ffffff">
		<td>Unique?</td>
		<td><input type="checkbox" name="is_unique" value="1" onclick="clickUniqueKey(this)"/></td>
	</tr>
	<tr id="duplicate_tr" style="display:none" bgcolor="#ffffff">
		<td>Remove duplicates?</td>
		<td><input type="checkbox" name="drop_duplicate" value="1"/></td>
	</tr>
</table>
</div>

<!-- Show query history dialog -->
<div id="field_dialog_history" style="display:none">

</div>