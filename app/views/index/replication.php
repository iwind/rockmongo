<div class="operation">
	<a href="<?php h(url("server")); ?>"><?php hm("server"); ?></a> | 
	<a href="<?php h(url("status")); ?>"><?php hm("status"); ?></a> | 
	<a href="<?php h(url("databases")); ?>"><?php hm("databases"); ?></a> |
	<a href="<?php h(url("processlist")); ?>"><?php hm("processlist"); ?></a> |
	<a href="<?php h(url("command")); ?>"><?php hm("command"); ?></a> |
	<a href="<?php h(url("execute")); ?>"><?php hm("execute"); ?></a> |
	<a href="<?php h(url("replication")); ?>" class="current"><?php hm("master_slave"); ?></a> 
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Replication Status (db.getReplicationInfo())</th>
	</tr>
	<?php foreach ($status as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>

<?php if(!empty($me)): ?>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Me (<a href="<?php h(url("collection", array( "db" => "local", "collection" => "me" ))); ?>">local.me</a>)</th>
	</tr>
	<?php foreach ($me as $param => $value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if(!empty($slaves)): ?>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Slaves (<a href="<?php h(url("collection", array( "db" => "local", "collection" => "slaves" ))); ?>">local.slaves</a>)</th>
	</tr>
	<?php foreach ($slaves as $slave):?>
	<tr bgcolor="#cfffff">
		<td colspan="2"><?php h($slave["_id"]); ?></td>
	</tr>
		<?php foreach ($slave as $param => $value):?>
		<tr bgcolor="#fffeee">
			<td width="120" valign="top"><?php h($param);?></td>
			<td><?php h($value);?></td>
		</tr>
		<?php endforeach; ?>
	<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if(!empty($masters)): ?>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Masters (<a href="<?php h(url("collection", array( "db" => "local", "collection" => "sources" ))); ?>">local.sources</a>)</th>
	</tr>
	<?php foreach ($masters as $master):?>
	<tr bgcolor="#cfffff">
		<td colspan="2"><?php h($master["_id"]); ?></td>
	</tr>
		<?php foreach ($master as $param => $value):?>
		<tr bgcolor="#fffeee">
			<td width="120" valign="top"><?php h($param);?></td>
			<td><?php h($value);?></td>
		</tr>
		<?php endforeach; ?>
	<?php endforeach; ?>
</table>
<?php endif; ?>