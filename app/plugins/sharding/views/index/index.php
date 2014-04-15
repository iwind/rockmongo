<div class="operation">
	<?php render_server_menu(); ?>
</div>

<a href="http://www.mongodb.org/display/DOCS/Configuring+Sharding" target="_blank">&raquo; Configuring Sharding</a> &nbsp; <a href="http://www.mongodb.org/display/DOCS/Sharding+Administration" target="_blank">&raquo; Sharding Administration</a> <br/>

<a href="<?php render_url("@sharding.index.add") ?>">[Add new shard]</a> <a href="<?php render_url("collection.index", array( "db"=>"config", "collection" => "chunks" )) ?>">[Chunks]</a>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<td>_id</td>
		<td>Host</td>
		<td>Max Size</td>
		<td>Status</td>
		<td></td>
	</tr>
	<?php foreach ($shards as $shard): ?>
	<tr bgcolor="#fffeee">
		<td><?php h($shard["_id"]) ?></td>
		<td><?php h($shard["host"]) ?></td>
		<td><?php if(isset($shard["maxSize"])):?><?php h($shard["maxSize"]) ?>M<?php endif; ?></td>
		<td><?php if(isset($shard["draining"])): ?>Draining<?php endif; ?></td>
		<td><a href="<?php render_url("remove", array( "host" => $shard["host"] )) ?>" onclick="return window.confirm('Are you sure to remove the sharding server?')">Remove</a></td>
	</tr>
	<?php endforeach; ?>
</table>