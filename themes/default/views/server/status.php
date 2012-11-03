<div class="operation">
	<?php render_server_menu("status"); ?>
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2"><?php hm("server_status"); ?> ({serverStatus:1})</th>
	</tr>
	<?php foreach ($status as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
