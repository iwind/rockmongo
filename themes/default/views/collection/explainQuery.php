<?php if(isset($error)):?>
<p class="error"><?php h($error);?></p>
<?php endif;?>

<?php if(isset($ret)): ?>
<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
<?php hm("responseserver"); ?>
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>
<?php endif;?>