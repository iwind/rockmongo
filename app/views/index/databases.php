<div class="operation">
	<a href="<?php h(url("server")); ?>"><?php hm("server"); ?></a> | 
	<a href="<?php h(url("status")); ?>"><?php hm("status"); ?></a> | 
	<a href="<?php h(url("databases")); ?>" class="current"><?php hm("databases"); ?></a> |
	<a href="<?php h(url("processlist")); ?>"><?php hm("processlist"); ?></a> |
	<a href="<?php h(url("command")); ?>"><?php hm("command"); ?></a> |
	<a href="<?php h(url("execute")); ?>"><?php hm("execute"); ?></a> |
	<a href="<?php h(url("replication")); ?>"><?php hm("master_slave"); ?></a> 
</div>

 <a href="<?php h(url("createDatabase")); ?>"><?php hm("create_database"); ?></a>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th>Name</th>
		<th>Size</th>
		<th>Storage<br/>Size</th>
		<th>Data<br/>Size</th>
		<th>Index<br/>Size</th>
		<th>Collections</th>
		<th>Objects</th>
	</tr>
	<?php foreach ($dbs as $db):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><a href="<?php h(url("db", array("db"=>$db["name"]))); ?>"><?php h($db["name"]);?></a></td>
		<td width="80"><?php h($db["diskSize"]);?></td>
		<td width="80"><?php h($db["storageSize"]);?></td>
		<td width="80"><?php h($db["dataSize"]);?></td>
		<td width="80"><?php h($db["indexSize"]);?></td>
		<td width="80"><?php h($db["collections"]);?></td>
		<td><?php h($db["objects"]);?></td>
	</tr>
	<?php endforeach; ?>
</table>
