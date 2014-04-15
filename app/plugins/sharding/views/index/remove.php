<div class="operation">
	<?php render_server_menu("@sharding.index.index"); ?>
</div>

<?php if(isset($ret)):?>
<?php render_server_response($ret) ?>
<?php endif; ?>

<input type="button" value="Back to List" onclick="window.location='<?php render_url("index") ?>'"/>