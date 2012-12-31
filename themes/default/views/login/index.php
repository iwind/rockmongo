<script type = "text/javascript">
	var showMore = <?php echo $moreOptions; ?>;

	$(function ()
	{
		showMore = !showMore;
		r_show_more_options($("#more_options_btn")[0]);

		//go to top window
		if (window.self != window.top)
		{
			if (window.parent.location.toString().match(/\?action=admin/))
			{
				window.parent.location.reload();
			}
		}
	});

	function r_show_more_options(btn)
	{
		if (!showMore)
		{
			$("#more_options").show();
			$(btn).html("Less &raquo;");
			showMore = 1;
			$("#show_more_options").val(1);
		}
		else
		{
			$("#more_options").hide();
			$(btn).html("More &raquo;");
			showMore = 0;
			$("#show_more_options").val(0);
		}
		return false;
	}
</script>

<article id = "content">
	<?php if (isset($message)): ?><p class = "error"><?php h($message); ?></p><?php endif; ?>
	<form method = "post" class = "box centred-form">
		<fieldset>
			<input type = "hidden" name = "more" id = "show_more_options" value = "<?php echo $moreOptions; ?>"/>
			<legend>RokMongo - Log in <i class = "icon-log-in"></i></legend>
			<!--(section.form-field.row>label.threecol+input[required="required"].ninecol.last)*4-->
			<section class = "form-field row">
				<div class = "control-label threecol">
					<label for = "host">Host:</label>
				</div>

				<div id = "host" class = "form-input ninecol last"><?php render_select_hosts("host", $hostIndex); ?></div>
			</section>
			<section class = "form-field row">
				<div class = "threecol control-label">
					<label for = "username">Username:</label>
				</div>
				<div class = "ninecol last form-input">
					<input id = "username" required = "required" type = "text" value = "<?php echo $username;?>" name = "username" placeholder = "admin" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{1,20}$"/>
				</div>

			</section>
			<section class = "form-field row">
				<div class = "threecol control-label">
					<label for = "password">Password:</label>
				</div>
				<div class = "ninecol last form-input">
					<input id = "password" required = "required" type = "password" name = "password" placeholder = "admin"/>
				</div>

			</section>
		</fieldset>
		<fieldset>
			<legend>Non-Admin Users</legend>
			<section class = "form-field row">
				<div class = "threecol control-label"><label for = "db-name">DB
					Name:</label></div>
				<div class = "form-input ninecol last">
					<input id = "db-name" name = "db" type = "text"/>
				</div>
			</section>
			<section class = "form-field row visuallyhidden">
				<div class = "control-label threecol">
					<label for = "language">Language:</label>
				</div>
				<div class = "ninecol last form-input">
					<select id = "language" name = "lang">
						<?php foreach ($languages as $code => $lang): ?>
						<option value = "<?php h($code);?>" <?php if (x("lang") == $code || (x("lang") == "" && __LANG__ == $code)): ?>selected="selected"<?php endif;?> ><?php h($lang); ?></option>
						<?php endforeach;?>
					</select>
				</div>

			</section>
			<section class = "form-field row visuallyhidden">
				<div class = "control-label threecol">
					<label for = "alive">Duration</label>
				</div>
				<div class = "form-input ninecol last">
					<select name = "expire" id = "alive"><?php foreach ($expires as $long => $name): ?>
						<option value = "<?php h($long);?>"><?php h($name);?></option>
						<?php endforeach;?>
					</select>
				</div>
			</section>
		</fieldset>
		<div class = "row centred form-actions">
			<button class = "btn btn-primary" type = "submit"><?php hm("loginandrock"); ?> <i class = "icon-login"></i>
			</button>
		</div>
		<aside class = "row"><?php hm("rockmongocredits") ?></aside>
	</form>
</article>

