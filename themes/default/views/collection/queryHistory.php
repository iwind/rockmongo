<?php if(empty($logs)): ?>
	<?php hm("noqueryhistory"); ?>
<?php else: ?>
<table width="100%">
	<?php foreach ($logs as $log):?>
	<tr>
		<td valign="top">
			<div style="background-color:#efefef;margin-bottom:10px">
				<div style="border-bottom:1px #ccc solid"><?php echo $log["time"]; ?> [<a href="index.php?<?php echo $log["query"]; ?>"><?php hm("requery"); ?></a>]</div>
				<div><?php echo $log["params"]["criteria"]; ?></div>
			</div>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
<?php endif;?>