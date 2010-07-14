
<script language="javascript">
function showOperationButtons(id) {
	$("#operate_" + id).show();
	$("#object_" + id).css("background-color", "#eeefff");
	$("#object_" + id).css("border", "2px #ccc solid");
}

function hideOperationButtons(id) {
	$("#operate_" + id).hide();
	$("#object_" + id).css("background-color", "#fff");
	$("#object_" + id).css("border", "0");
}

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
	}
	else {
		$("#limitLabel").hide();
	}
}
</script>

<script language="javascript">

</script>

<h4><?php h($db);?> &raquo; <?php h($collection);?></h4>

<div class="operation"><a href="#">Query</a> | <a href="<?php h(url("createRow", xn())); ?>">Insert</a> | <a href="<?php h($_SERVER["REQUEST_URI"]);?>">Refresh</a> | <a href="<?php h(url("collectionIndexes", xn())); ?>">Indexes</a> | <a href="<?php h(url("collectionStats", xn())); ?>">Statistics</a> | <a href="<?php h(url("clearRows", xn())); ?>" onclick="return window.confirm('Are you sure to delete all records in collection \'<?php h($collection);?>\'?');">Clear</a> | <a href="<?php h(url("removeCollection", xn())); ?>" onclick="return window.confirm('Are you sure to remove collection \'<?php h($collection);?>\'?');">Remove</a> </div>

<div class="query">
<form method="get">
<input type="hidden" name="db" value="<?php h($db);?>"/>
<input type="hidden" name="collection" value="<?php h($collection);?>"/>
<input type="hidden" name="action" value="<?php h(x("action"));?>"/>
<table>
	<tr>
		<td valign="top">
			<textarea name="criteria" rows="5" cols="70"><?php h(x("criteria"));?></textarea><br/>
			<div id="newobjInput" <?php if (x("command") !="modify"):?>style="display:none"<?php endif;?>>
				New Object(<strong style="color:red">warning:should be an array, and only works in some higher<br/> version client API</strong>):<br/>
				<textarea name="newobj" rows="5" cols="70"><?php h(x("newobj"));?></textarea>
			</div>
		</td>
		<td valign="top" class="field_orders">
			<p><input type="text" name="field[]" value="<?php h(rock_array_get(x("field"),0));?>" /> <select name="order[]"><option value="asc" <?php if (rock_array_get(x("order"),0)=="asc"):?>selected="selected"<?php endif;?>>ASC</option><option value="desc" <?php if (rock_array_get(x("order"),0)=="desc"):?>selected="selected"<?php endif;?>>DESC</option></select></p>
			<p><input type="text" name="field[]" value="<?php h(rock_array_get(x("field"),1));?>" /> <select name="order[]"><option value="asc" <?php if (rock_array_get(x("order"),1)=="asc"):?>selected="selected"<?php endif;?>>ASC</option><option value="desc" <?php if (rock_array_get(x("order"),1)=="desc"):?>selected="selected"<?php endif;?>>DESC</option></select></p>
			<p><input type="text" name="field[]" value="<?php h(rock_array_get(x("field"),2));?>" /> <select name="order[]"><option value="asc" <?php if (rock_array_get(x("order"),2)=="asc"):?>selected="selected"<?php endif;?>>ASC</option><option value="desc" <?php if (rock_array_get(x("order"),2)=="desc"):?>selected="selected"<?php endif;?>>DESC</option></select></p>
			<p><input type="text" name="field[]" value="<?php h(rock_array_get(x("field"),3));?>" /> <select name="order[]"><option value="asc" <?php if (rock_array_get(x("order"),3)=="asc"):?>selected="selected"<?php endif;?>>ASC</option><option value="desc" <?php if (rock_array_get(x("order"),3)=="desc"):?>selected="selected"<?php endif;?>>DESC</option></select></p>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label id="limitLabel" <?php if (x("command") !="findAll"):?>style="display:none"<?php endif;?>>Limit:<input type="text" name="limit" size="7" value="<?php h(xi("limit"));?>"/></label>
			<select name="command" onchange="changeCommand(this)">
				<option value="findAll" <?php if(x("command")=="findAll"):?>selected="selected"<?php endif;?>>findAll</option>
				<option value="remove" <?php if(x("command")=="remove"):?>selected="selected"<?php endif;?>>remove</option>
				<option value="modify" <?php if(x("command")=="modify"):?>selected="selected"<?php endif;?>>modify</option>
			</select>
			<input type="submit" value="Submit Query"/> <input type="button" value="Clear Conditions" onclick="window.location='<?php h(url("collection", array( "db"=>$db, "collection" => $collection ))); ?>'"/>
			<?php if(isset($cost)):?>Cost <?php h(round($cost, 6));?>s<?php endif;?>
			<?php if(isset($message)):?><p class="error"><?php h($message);?></p><?php endif;?>
		</td>
	</tr>
</table>
</form>
</div>

<?php if(!isset($page) || $page->total() == 0):?>
	<?php if (x("command") != "findAll"):?>
		<p><?php if (isset($count)): h($count);?> rows may be affected.<?php endif;?></p>
	<?php else:?>
		<p>No records is found.</p>
	<?php endif;?>
<?php else: ?>
	<p class="page"><?php h($page); ?> (<?php h($page->offset());?>/<?php h($page->total());?>)</p>
	
	<?php foreach ($rows as $index => $row):?>
	<div style="border:2px #ccc solid;margin-bottom:5px;" onmouseover="showOperationButtons('<?php h($row["_id"]);?>')" onmouseout="hideOperationButtons('<?php h($row["_id"]);?>')">
		<table width="100%" border="0" id="object_<?php h($row["_id"]);?>">
			<tr>
				<td valign="top" width="50">#<?php echo $page->offset() + $index; ?></td>
				<td>
				<div style="display:none;" class="operation" id="operate_<?php h($row["_id"]);?>">
					<a href="<?php echo url("modifyRow", array( 
						"db" => $db, 
						"collection" => $collection, 
						"id" => $row["_id"],
						"uri" => $_SERVER["REQUEST_URI"]
					)); ?>">Update</a>
				
					<a href="<?php echo url("deleteRow", array( 
						"db" => $db, 
						"collection" => $collection, 
						"id" => $row["_id"],
						"uri" => $_SERVER["REQUEST_URI"]
					)); 
					?>" onclick="return window.confirm('Are you sure to delete the row #<?php echo $page->offset() + $index; ?>?');">Delete</a> 
					
					<a href="<?php h(url("createRow", array( 
						"db" => $db, 
						"collection" => $collection, 
						"id" => $row["_id"],
						"uri" => $_SERVER["REQUEST_URI"]
					))); ?>">Duplicate</a>
				</div>	
				<?php h($row["data"]); ?>
			</td>
			</tr>
		</table>
	</div>
	<?php endforeach; ?>


	<p class="page"><?php h($page); ?></p>
<?php endif;?>