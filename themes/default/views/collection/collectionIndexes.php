<h3><?php render_navigation($db,$collection,false); ?> &raquo; <?php hm("indexes");?> [<a href="<?php h($_SERVER["REQUEST_URI"]);?>"><?php hm("refresh"); ?></a>]</h3>

<div class="operation"><a href="<?php 
				h(url("collection.createIndex", array( 
					"db" => $db, 
					"collection" => $collection
				)));
			?>"><?php hm("createindex"); ?></a></div>

<table width="600" cellpadding="2" cellspacing="1" bgcolor="#cccccc">
	<tr bgcolor="#eeefff" align="center">
		<td width="150"><?php hm("name"); ?></td>
		<td><?php hm("key"); ?></td>
		<td><?php hm("unique"); ?></td>
		<td width="100"><?php hm("operation"); ?></td>
	</tr>
	<?php foreach ($indexes as $index): ?>
	<tr bgcolor="#ffffff">
		<td valign="top" align="center"><?php h($index["name"]);?></td>
		<td><?php h($index["data"]);?></td>
		<td align="center" valign="top"><?php if (isset($index["unique"]) && $index["unique"]):?>Y<?php endif;?></td>
		<td align="center" valign="top"><?php if (!isset($index["key"]["_id"]) || count($index["key"]) != 1):?> <a href="<?php 
				h(url("collection.deleteIndex", array( 
					"db" => $db, 
					"collection" => $collection, 
					"index" =>  $index["name"]
				)));
			?>" onclick="return window.confirm('<?php hm("warningindex"); ?> \'<?php h($index["name"]);?>\'?');"><?php hm("drop"); ?></a><?php endif;?></td>
	</tr>
	<?php endforeach; ?>
</table>