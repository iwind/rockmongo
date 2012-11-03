<h3><?php render_navigation($db,$collection,false); ?> &raquo; <?php hm("validate"); ?></h3>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
<?php hm("responseserver"); ?>
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>
