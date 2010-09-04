<h3><a href="<?php h(url("databases"));?>"><?php hm("databases"); ?></a> &raquo; Drop Database</h3>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
Response from server:
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>

<p>
	<input type="button" value="Go to databases" onclick="window.location='<?php h(url("databases")); ?>'"/>
</p>

<script language="javascript">
window.parent.frames["left"].location = "<?php h(url("dbs"));?>";
</script>