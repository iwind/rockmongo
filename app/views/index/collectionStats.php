<h4><?php h($db);?> &raquo; <a href="<?php h(url("collection", array( "db"=>$db,"collection"=>$collection )));?>"><?php h($collection)?></a> &raquo; Statistics</h4>

<table bgcolor="#cccccc" width="600" cellpadding="2" cellspacing="1">
<?php foreach ($stats as $name => $stat):?>
	<tr bgcolor="#ffffff">
		<td width="150" bgcolor="#fffeee"><?php h($name);?></td>
		<td><?php 
			if (is_array($stat)) {
				h("<xmp>" . var_export($stat, true) . "</xmp>"); 
			}
			else {
				if (in_array($name, array( "size", "storageSize", "lastExtentSize", "totalIndexSize" ))) {
					$stat = round($stat/1024/1024, 2) . "m";
				}
				h($stat);
			}
			?></td>
	</tr>
<?php endforeach; ?>
</table>
