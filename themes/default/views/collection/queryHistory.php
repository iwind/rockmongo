<?php if (isset($error)):?> 
<p class="error"><?php h($error);?></p>
<?php endif; ?>

<div class="no_history" style="<?php if(!empty($logs)): ?>display:none<?php endif; ?>">
	<?php hm("noqueryhistory"); ?>
</div>

<div class="has_history" style="<?php if(empty($logs)): ?>display:none<?php endif; ?>">
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
</div>