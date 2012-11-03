<div class="operation">
	<?php render_server_menu("processlist"); ?>
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="10"><?php hm("processlist"); ?> (db.$cmd.sys.inprog.find({$all:1}))</th>
	</tr>
	<tr bgcolor="#cfffff">
		<?php foreach (array(
			"id" =>"ID", 
			"desc" => "Description", 
			"client" => "Client", 
			"active" => "Active",
			"lockType" => "LockType",
			"waitingForLock" => "Waiting",
			"secs_running" => "SecsRunning",
			"op" => "Operation",
			"ns" => "NameSpace"
			) as $param => $desc):?>
		<td><?php h($desc); ?></td>
		<?php endforeach; ?>
	</tr>
	<?php foreach ($progs as $prog):?>
	<tr bgcolor="#fffeee">
		<?php foreach (array(
			"opid" =>"ID", 
			"desc" => "Description", 
			"client" => "Client", 
			"active" => "Active",
			"lockType" => "LockType",
			"waitingForLock" => "Waiting",
			"secs_running" => "SecsRunning",
			"op" => "Operation",
			"ns" => "NameSpace",
			) as $param => $desc):?>
		<td valign="top" <?php if(isset($prog["query"])&&$param=="opid"): ?>rowspan="2"<?php endif; ?>>
			<?php if(isset($prog[$param])):?>
				<?php if($param=="opid"):?>
					<?php h($prog["opid"]);?><?php if($prog["opid"]>1):?> [<a href="<?php h(url("server.killOp", array("opid"=>$prog["opid"]))); ?>" onclick="return window.confirm('<?php hm("killop"); ?> \'<?php h($prog["opid"]);?>\'?')"><?php hm("kill"); ?></a>]<?php endif;?>
				<?php
				else:
					if($prog[$param] == "(NONE)" ||$prog[$param] == "none"){}else{ h($prog[$param]); }
				endif;
				?>
			<?php endif; ?>
		</td>	
		<?php endforeach;?>
	</tr>
	<?php if(isset($prog["query"])):?>
	<tr bgcolor="#fffeee">
		<td colspan="10"><strong><?php hm("query"); ?></strong><br/>-----<br/><?php h($prog["query"]); ?></td>
	</tr>
	<?php endif; ?>
	<?php endforeach; ?>
</table>