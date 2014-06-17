<?php if(empty($plugins)): ?>
<p>There are no plugins installed, [<a href="http://rockmongo.com/plugins" target="_blank">Go and download plugins here</a>] &nbsp; [<a href="http://rockmongo.com/wiki/pluginUsage?lang=en_us" target="_blank">How to install plugins?</a>] &nbsp; [<a href="http://rockmongo.com/wiki/pluginDevelop?lang=en_us" target="_blank">Develop New plugins</a>]</p>
<?php else: ?>
<div style="margin-top:20px"></div>
<p>[<a href="http://rockmongo.com/plugins" target="_blank">Go and download more plugins here</a>] &nbsp; [<a href="http://rockmongo.com/wiki/pluginUsage?lang=en_us" target="_blank">How to install plugins?</a>] &nbsp; [<a href="http://rockmongo.com/wiki/pluginDevelop?lang=en_us" target="_blank">Develop New plugins</a>]</p>

	<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="90%">
		<tr>
			<th>Dir</th>
			<th>Name</th>
			<th>Code</th>
			<th width="200">Author</th>
			<th width="300">Description</th>
			<th>Version</th>
			<th>Enabled</th>
		</tr>
		<?php foreach ($plugins as $plugin): ?>
		<tr bgcolor="#ffffff">
			<td align="center"><?php echo ($plugin["dir"]) ?></td>
			<td align="center"><?php if($plugin["url"]): ?><a href="<?php h($plugin["url"]) ?>" target="_blank"><?php endif; ?><?php echo ($plugin["name"]) ?><?php if($plugin["url"]): ?></a><?php endif; ?></td>
			<td align="center"><?php echo ($plugin["code"]) ?></td>
			<td style="word-break:break-all;" align="center"><?php echo htmlspecialchars($plugin["author"]) ?></td>
			<td style="word-break:break-all;"><?php echo nl2br($plugin["description"]) ?></td>
			<td align="center"><?php echo ($plugin["version"]) ?></td>
			<td align="center"><?php $plugin["enabled"] ? h("<span style=\"color:green\">Y</span>") : h("N") ?></td>
		</tr>
		<?php endforeach; ?>
	</table>


<?php endif; ?>