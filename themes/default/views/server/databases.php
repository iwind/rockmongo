<div class="operation">
	<?php render_server_menu("databases"); ?>
</div>

 <a href="<?php h(url("server.createDatabase")); ?>"><?php hm("create_database"); ?></a>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th><?php hm("name"); ?></th>
		<th><?php hm("size"); ?></th>
		<th nowrap><?php hm("storagesize"); ?></th>
		<th nowrap><?php hm("datasize"); ?></th>
		<th nowrap><?php hm("indexsize"); ?></th>
		<th><?php hm("collections"); ?></th>
		<th><?php hm("objects"); ?></th>
	</tr>
	<?php foreach ($dbs as $db):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><a href="<?php h(url("db.index", array("db"=>$db["name"]))); ?>"><?php h($db["name"]);?></a></td>
		<td width="80"><?php h($db["diskSize"]);?></td>
		<td width="80"><?php h($db["storageSize"]);?></td>
		<td width="80"><?php h($db["dataSize"]);?></td>
		<td width="80"><?php h($db["indexSize"]);?></td>
		<td width="80"><?php h($db["collections"]);?></td>
		<td><?php h($db["objects"]);?></td>
	</tr>
	<?php endforeach; ?>
</table>