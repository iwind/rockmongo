<h3><a href="<?php h(url("server.databases"));?>"><?php hm("databases"); ?></a> &raquo; <?php hm("create_database");?></h3>


<?php if(isset($error)):?>
<p class="error"><?php h($error);?></p>
<?php endif;?>
<?php if(isset($message)):?>
<p class="message"><?php h($message);?></p>
<?php endif;?>

<?php if (!empty($_POST)):?>
<script language="javascript">
window.parent.frames["left"].location.reload();
</script>
<?php endif;?>

<form method="post">
<?php hm("name"); ?>:<br/>
<input type="text" name="name" value="<?php h_escape(x("name"));?>"/><br/>
<input type="submit" value="<?php hm("create"); ?>" />
</form>