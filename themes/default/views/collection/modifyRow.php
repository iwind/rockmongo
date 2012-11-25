<script language="javascript">
/**
 * switch format between json and array
 */
function switchFormat(select) {
	$.ajax({
		"data": { "data": $("#row_data").val(), "format":select.value },
		"url": "index.php?action=collection.switchFormat&db=<?php h($db); ?>&collection=<?php h($collection); ?>",
		"type": "POST",
		"dataType": "json",
		"success": function (resp) {
			$("#row_data").val(resp.data);
		}
	});
}
</script>

<h3><?php render_navigation($db,$collection,false); ?> &raquo; <?php hm("modifyrow"); ?> '<?php h_escape($row["_id"]);?>' [<a href="<?php h($_SERVER['REQUEST_URI']);?>"><?php hm("refresh"); ?></a>]</h3>

<?php if (isset($message)): ?>
<p class="message">
<?php h($message);?>
</p>
<?php endif;?>
<?php if (isset($error)): ?>
<p class="error">
<?php h($error);?>
</p>
<?php endif;?>

<form method="post">
<?php hm("format"); ?>:<br/>
<select name="format" onchange="switchFormat(this)">
<option value="array" <?php if($last_format=="array"): ?>selected="selected"<?php endif; ?>>Array</option>
<option value="json" <?php if($last_format=="json"): ?>selected="selected"<?php endif; ?>>JSON</option>
</select><br/>
_id:<br/>
<input type="text" readonly value="<?php h_escape($row["_id"]);?>" size="72"/>
<br/>
<?php hm("data"); ?>:<br/>
<textarea rows="35" cols="70" name="data" id="row_data"><?php h($data); ?></textarea><br/>
<input type="submit" value="<?php hm("save"); ?>"/> <input type="button" value="<?php hm("back"); ?>" onclick="window.location='<?php h(xn("uri"));?>'"/>
</form>