<h4><?php h($db);?> &raquo; <a href="<?php 
				h(url("collection", array( 
					"db" => $db, 
					"collection" => $collection
				)));
			?>"><?php h($collection);?></a> &raquo; Indexes</h4>

<div class="operation"><a href="<?php 
				h(url("createIndex", array( 
					"db" => $db, 
					"collection" => $collection
				)));
			?>">Create</a></div>

<table width="600" cellpadding="2" cellspacing="1" bgcolor="#cccccc">
	<tr bgcolor="#eeefff" align="center">
		<td width="150">Name</td>
		<td>Key</td>
		<td>Unique</td>
		<td width="100">Operation</td>
	</tr>
	<?php foreach ($indexes as $index): ?>
	<tr bgcolor="#ffffff">
		<td valign="top" align="center"><?php h($index["name"]);?></td>
		<td><?php h($index["data"]);?></td>
		<td align="center" valign="top"><?php if (isset($index["unique"]) && $index["unique"]):?>Y<?php endif;?></td>
		<td align="center" valign="top"><a href="<?php 
				h(url("deleteIndex", array( 
					"db" => $db, 
					"collection" => $collection, 
					"index" =>  $index["name"]
				)));
			?>" onclick="return window.confirm('Are you sure to drop the index \'<?php h($index["name"]);?>\'?');">Drop</a></td>
	</tr>
	<?php endforeach; ?>
</table>