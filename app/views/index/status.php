<div class="operation">
	<a href="<?php h(url("server")); ?>"><?php hm("server"); ?></a> | 
	<a href="<?php h(url("status")); ?>" class="current"><?php hm("status"); ?></a> | 
	<a href="<?php h(url("databases")); ?>"><?php hm("databases"); ?></a> |
	<a href="<?php h(url("processlist")); ?>"><?php hm("processlist"); ?></a> |
	<a href="<?php h(url("command")); ?>"><?php hm("command"); ?></a> |
	<a href="<?php h(url("execute")); ?>"><?php hm("execute"); ?></a> |
	<a href="<?php h(url("replication")); ?>"><?php hm("master_slave"); ?></a> 
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Server Status ({serverStatus:1})</th>
	</tr>
	<?php foreach ($status as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
