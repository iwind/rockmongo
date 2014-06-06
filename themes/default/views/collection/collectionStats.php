<h3><?php render_navigation($db,$collection,false); ?> &raquo; <?php hm("statistics"); ?> [<a href="<?php h($_SERVER["REQUEST_URI"]);?>"><?php hm("refresh"); ?></a>]</h3>

<table bgcolor="#cccccc" width="600" cellpadding="2" cellspacing="1">
	<tr bgcolor="#cfffff">
		<td colspan="2">db.<?php h($collection); ?>.stats()</td>
	</tr>
<?php foreach ($stats as $name => $stat):?>
	<tr bgcolor="#ffffff">
		<td width="150" bgcolor="#fffeee" valign="top"><?php h($name);?></td>
		<td><?php
			if (is_array($stat)) {
				h("<xmp>" . var_export($stat, true) . "</xmp>");
			}
			else {
				if (in_array($name, array( "size", "storageSize", "lastExtentSize", "totalIndexSize", "avgObjSize" ))) {
					$stat = "<span title=\"{$stat}bytes\">" . r_human_bytes($stat) . "</span>";
				}
				h($stat);
			}
			?></td>
	</tr>
<?php endforeach; ?>
<?php if(!empty($top)):?>
 	<tr bgcolor="#cfffff">
		<td colspan="2">{top:1}</td>
	</tr>
	<?php foreach ($top as $name => $stat):?>
		<tr bgcolor="#ffffff">
			<td width="150" bgcolor="#fffeee" valign="top"><?php h($name);?></td>
			<td><?php
				h($stat);
				?></td>
		</tr>
	<?php endforeach; ?>
<?php endif;?>
</table>
