<h3><?php render_navigation($db, $collection) ?> &raquo; Add new Function</h3>

<?php if(isset($error)): ?>
<p class="error"><?php h($error) ?></p>
<?php endif; ?>
<?php if(isset($message)): ?>
<p class="message"><?php h($message) ?></p>
<?php endif; ?>

<form method="post">
Function Name:<br/>
<input type="text" name="name" size="30" value="<?php h(htmlspecialchars($name)) ?>"/><br/>
Function Body:<br/>
<textarea rows="20" cols="60" name="body"><?php h(htmlspecialchars($body)) ?></textarea><br/>
<input type="submit" value="Add"/>
</form>

<?php if(isset($ret)): ?>
<?php render_server_response($ret) ?>
<?php endif; ?>