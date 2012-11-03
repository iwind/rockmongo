<h3><a href="<?php h(url("server.processlist"));?>"><?php hm("processlist"); ?></a> &raquo; <?php hm("killoperation"); ?> '<?php h(x("opid"));?>'</h3>

<?php if(isset($ret)):?>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
<?php hm("responseserver"); ?>
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>
<?php endif; ?>