<div class="operation">
	<a href="<?php h(url("server")); ?>" class="current"><?php hm("server"); ?></a> | 
	<a href="<?php h(url("status")); ?>"><?php hm("status"); ?></a> | 
	<a href="<?php h(url("databases")); ?>"><?php hm("databases"); ?></a> |
	<a href="<?php h(url("processlist")); ?>"><?php hm("processlist"); ?></a> |
	<a href="<?php h(url("command")); ?>"><?php hm("command"); ?></a> |
	<a href="<?php h(url("execute")); ?>"><?php hm("execute"); ?></a> |
	<a href="<?php h(url("replication")); ?>"><?php hm("master_slave"); ?></a> 
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Command Line(db.serverCmdLineOpts())</th>
	</tr>
	<tr bgcolor="#fffeee">
		<td colspan="2"><?php h($commandLine);?></td>
	</tr>
</table>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Connection</th>
	</tr>
	<?php foreach ($connections as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" ><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Web <?php hm("server"); ?></th>
	</tr>
	<?php foreach ($webServers as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" ><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="3">Directives</th>
	</tr>
	<tr bgcolor="#fffeee">
		<th>Directive</th>
		<th>Global Value</th>
		<th>Local Value</th>
	</tr>
	<?php foreach ($directives as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="200" ><?php h($param);?></td>
		<td><?php h($value["global_value"]);?></td>
		<td><?php h($value["local_value"]);?></td>
	</tr>
	<?php endforeach; ?>
</table>

<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Build Information ({buildinfo:1})</th>
	</tr>
	<?php foreach ($buildInfos as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>

<div class="gap"></div>

