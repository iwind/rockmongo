<script language="javascript">
function clickUniqueKey(box) {
	if (box.checked) {
		$("#duplicate_tr").show();
	}
	else {
		$("#duplicate_tr").hide();
	}
}

function addNewField() {
	$("#fields").append("<p style=\"margin:0;padding:0\"><input type=\"text\" name=\"field[]\" size=\"30\"/> <select name=\"order[]\"><option value=\"asc\">ASC</option><option value=\"desc\">DESC</option></select> <input type=\"button\" value=\"+\" onclick=\"addNewField()\"/><input type=\"button\" value=\"-\" onclick=\"removeNewField(this)\"/></p>");
}

function removeNewField(btn) {
	$(btn).parent().remove();
}
</script>

<h4><?php h($db);?> &raquo; <a href="<?php 
				h(url("collection", array( 
					"db" => $db, 
					"collection" => $collection
				)));
			?>"><?php h($collection);?></a> &raquo; <a href="<?php 
				h(url("collectionIndexes", array( 
					"db" => $db, 
					"collection" => $collection
				)));
			?>">Indexes</a> &raquo; Create</h4>
			
<?php if(isset($message)): ?>
<p class="error"><?php h($message);?></p>
<?php endif; ?>
			
<form method="post">
<table width="600">
	<tr>
		<td width="130">Name</td>
		<td><input type="text" name="name"/></td>
	</tr>
	<tr>
		<td valign="top">Fields</td>
		<td><div id="fields"><input type="text" name="field[]" size="30"/> <select name="order[]"><option value="asc">ASC</option><option value="desc">DESC</option></select> <input type="button" value="+" onclick="addNewField()"/></div></td>
	</tr>
	<tr>
		<td>Unique?</td>
		<td><input type="checkbox" name="is_unique" value="1" onclick="clickUniqueKey(this)"/></td>
	</tr>
	<tr id="duplicate_tr" style="display:none">
		<td>Remove duplicates?</td>
		<td><input type="checkbox" name="drop_duplicate" value="1"/></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="Create"/></td>
	</tr>
</table>
</form>