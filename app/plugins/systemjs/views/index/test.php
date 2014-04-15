<h3><?php render_navigation($db, $collection) ?> &raquo; Test Function '<?php h($func["_id"]) ?>'</h3>

<select name="args" onchange="window.location='<?php render_url("test", array( "db" => $db, "collection" => $collection, "docId" => xn("docId") )) ?>&args=' + this.value">
<?php for($i=0; $i<30; $i++): ?>
<option value="<?php h($i) ?>" <?php if($args==$i): ?>selected="selected"<?php endif; ?> ><?php h($i) ?></option>
<?php endfor; ?>
</select>

<form method="post">
<?php for ($i=0; $i < $args; $i ++): ?>
Arguments[<?php h($i) ?>] <?php render_select_data_types("type[{$i}]", isset($types[$i]) ? $types[$i] : null) ?>:<br/>
<textarea rows="5" cols="60" name="param[]"><?php if(isset($params[$i])): ?><?php h(htmlspecialchars($params[$i])) ?><?php endif; ?></textarea><br/>
<?php endfor; ?>
<input type="submit" value="Run Test"/>
</form>

<?php if(isset($ret)): ?>
<?php render_server_response($ret) ?>
<?php endif; ?>