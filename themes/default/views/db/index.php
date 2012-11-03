<script language="javascript">

/** show more menus **/
function showMoreMenus(link) {
	var obj = $(link);
	setManualPosition(".menu", obj.position().left, obj.position().top + obj.height() + 4);
}


/** show manual links **/
function setManualPosition(className, x, y) {
	if ($(className).is(":visible")) {
		$(className).hide();
	}
	else {
		window.setTimeout(function () {
			$(className).show();
			$(className).css("left", x);
			$(className).css("top", y)
		}, 100);
		$(className).find("a").click(function () {
			hideMenus();
		});
	}
}
 
/** hide menus **/
function hideMenus() {
	$(".menu").hide();
}

$(function () {
	$(document).click(hideMenus);
});
</script>

<h3><?php render_navigation($db); ?></h3>

<div class="operation">
	<?php render_db_menu($db) ?>
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<?php foreach ($stats as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>