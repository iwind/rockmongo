<script language="javascript">
/**
 * highlight current collection being viewed
 *
 * @param string name collection name
 */
function highlightCollection(name, count) {
	var collections = $(".collections");
	collections.find("li").each(function () {
		var a = $(this).find("a");
		if (a.attr("cname") == name) {
			if (count != undefined) {
				$(this).find(".count").html(count);
			}
			a.css("font-weight", "bold");
			a.css("color", "blue");
		}
		else {
			a.css("font-weight", "normal");
			a.css("color", "");
		}
	});
}

$(function () {
	//dbs click
	$(".dbs .db-box .db-btn").click(function () {
		var box = $(this).parent();
		var collectionsBox = box.find(".collections");
		if (collectionsBox.is(":visible") && collectionsBox.find("li").length > 0) {
			box.find(".collections").slideUp(100);
			return false;
		}

		window.parent.frames["right"].location = $(this).attr("r-target-url");
	});

	//collections click
	var collections = $(".collections");
	collections.find("li").each(function () {
		var li = $(this);
		var a = $(this).find("a");
		var name = a.attr("cname");

		li.click(function () {
			window.location.hash = "#" + name;
		});

		//highlight current selected
		if (window.location.hash == "#" + name) {
			a.css("font-weight", "bold");
			a.css("color", "blue");
		}
	});

	// will be used to redirect to db by name
	var template = '<?php h(url("db.index",array("db"=>'__DB__')));?>';
	$('#selector').submit(function(e) {
	    e.preventDefault();
	    if($('#db').val()) {
		window.parent.frames['right'].location=template.replace('__DB__', $('#db').val());
	    }
	});

	//search collection in box with keyword
	$(".r_search_box").keyup(function (event) {
		var value = $(this).val().trim()
			.replace(/[^\w\.]/g, "");
		var collectionNameRows = $(this).parents(".collections:first").find(".collection");
		if (event.which == 13) {
			if (collectionNameRows.length > 0) {
				var link = collections.find(".collection:visible .name_text").parent("a:first");
				if (link.length > 0) {
					highlightCollection(link.attr("cname"));
					link[0].click();
				}
				return;
			}
		}
		collectionNameRows.each(function (k, row) {
			var name = $(row).find("a:first").attr("cname");
			if (value.length == 0 || (new RegExp(value, "i")).test(name)) {
				$(row).show();
				var nameText = $(row).find(".name_text");
				nameText.html(name.replace(new RegExp("(" + value + ")", "i"), "<font color=\"red\">$1</font>"));
			}
			else {
				$(row).hide();
			}
		});
	});
});

</script>

<div class="leftbar-page">
	<div class="server-btn"><img src="<?php render_theme_path() ?>/images/server.png" align="absmiddle" width="14"/> <a href="<?php h(url("server.index"));?>" target="right"><?php hm("server"); ?></a></div>
	<div class="world-btn"><img src="<?php render_theme_path() ?>/images/world.png" align="absmiddle" width="14"/> <a href="<?php h(url("server.databases"));?>" target="right"><?php hm("overview"); ?></a></div>
	<div class="line"></div>
	<ul class="dbs">
		<?php foreach ($dbs as $db) : ?>
		<li class="db-box">
			<a class="db-btn" href="<?php echo $baseUrl;?>&db=<?php h($db["name"]);?>" <?php if ($db["name"] == x("db")): ?>style="font-weight:bold"<?php endif;?> r-target-url="<?php h(url("db.index",array("db"=>$db["name"])));?>"><img src="<?php render_theme_path() ?>/images/database.png" align="absmiddle" width="14"/> <?php echo $db["name"];?></a><?php if($db["collectionCount"]>0):?> (<?php h($db["collectionCount"]); ?>)<?php endif;?>
			<ul class="collections">
				<?php if($db["name"] == x("db")): ?>
					<?php if (!empty($tables)):?>
						<li><input type="text" class="r_search_box" placeholder="keyword"/></li>
						<?php foreach ($tables as $table => $count) :?>
						<li class="collection"><a href="<?php h(url("collection.index", array( "db" => $db["name"], "collection" => $table ))); ?>" target="right" cname="<?php h($table);?>"><img src="<?php render_theme_path() ?>/images/<?php echo r_get_collection_icon($table) ?>.png" width="14" align="absmiddle"/> <span class="name_text"><?php h($table);?></span></a> (<span class="count"><?php h($count);?></span>)</li>
						<?php endforeach; ?>
					<?php else:?>
						<li><?php hm("nocollections2"); ?></li>
					<?php endif;?>
				<?php endif; ?>
				<?php if ($db["name"] == x("db")):?>
				<li><a href="<?php h(url("db.newCollection", array( "db" => $db["name"] ))); ?>" target="right" title="<?php hm("createnewcollection"); ?>"><img src="<?php render_theme_path() ?>/images/add.png" width="14" align="absmiddle"/> <?php hm("create"); ?> &raquo;</a></li>
				<?php endif;?>
			</ul>
		</li>
		<?php endforeach; ?>
		<?php if($showDbSelector): ?>
		<li>
			<img src="<?php render_theme_path() ?>/images/database.png" align="absmiddle" width="14"/>
			<form id="selector" action="#" style="display:inline">
				<input type="text" name="db" id="db" />
				<input type="submit" value="<?php hm("selectdb"); ?>">
			</form>
		</li>
		<?php endif;?>
	</ul>
	<div style="height:40px"></div>
</div>
