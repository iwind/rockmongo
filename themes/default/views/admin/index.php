<html>
    <head>
        <title>RockMongo</title>
        <!-- Base Jquery -->
        <script language="javascript" type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
        <script language="javascript" type="text/javascript" src="js/jquery-ui-1.8.4.custom.min.js"></script>

        <!--JQuery Layout -->
        <script language="javascript" type="text/javascript" src="js/jquery.layout.min-1.3.0.js"></script>
        <link type="text/css" href="<?php render_theme_path() ?>/css/layout-default-1.3.0.css" rel="stylesheet" />
        <style type="text/css">
            * {font-size:12px; font-family:'Courier New', Arial}
            body {margin:0; padding:0}
            a { text-decoration:none; color:#004499; line-height:1.5 }

            .manual, .server-menu {
                float:right;
                margin-right:100px;
                margin-top:0px;
                background-color:#eee;
                border-left:1px #ccc solid;
                border-top:1px #ccc solid;
                border-right:2px #ccc solid;
                border-bottom:2px #ccc solid;
                padding-left:3px;
                position:absolute;
                display:none;
                width:100px;
            	z-index:10000;
            }
            
            #right-pane { padding-left:10px }
        </style>
        <script language="javascript">
            $(document).ready(function(){
                //---Init Layout
                var wsize=250;
                $('body').layout({
                    defaults: {
                        applyDefaultStyles: false

                    },
                    north :{
                        closable:true,
                        slidable:false
                    },
                    west: {
                        minSize: wsize,
                        maxSize: 2*wsize,
                        size: wsize,
                        resizable: true,
                        closable:true,
                        slidable:true
                    }

                });
            });

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
                $(".manual").hide();
                $(".server-menu").hide();
            }
        </script>
    </head>
    <body>
        <!-- top bar -->

        <div id="top-pane" class="ui-layout-north" style="overflow:hidden">
            <iframe src="<?php echo $topUrl; ?>" name="top" width="100%" frameborder="0" height="20" marginheight="0" scrolling="no"></iframe>
        </div>
        <div id="left-pane" class="ui-layout-west">
            <!-- left bar -->
            <iframe src="<?php echo $leftUrl; ?>" name="left" width="100%" height="100%" frameborder="0" scrolling="auto" marginheight="0"></iframe>
        </div>

        <div id="right-pane" class="ui-layout-center">
            <!-- right bar -->
            <iframe src="<?php echo $rightUrl; ?>" name="right" width="100%" height="100%" frameborder="0" marginheight="0" scrolling="auto"></iframe>
        </div>

        <!-- quick links -->
        <div class="manual">
            <?php render_manual_items() ?>
        </div>

        <!-- menu when "Tools" clicked -->
        <div class="server-menu" style="width:120px">
            <a href="<?php h(url("server.index")); ?>" target="right"><?php hm("server"); ?></a><br/>
            <a href="<?php h(url("server.status")); ?>" target="right"><?php hm("status"); ?></a> <br/>
            <a href="<?php h(url("server.databases")); ?>" target="right"><?php hm("databases"); ?></a> <a href="<?php h(url("server.createDatabase")); ?>" target="right" title="Create new Database">[+]</a> <br/>
            <a href="<?php h(url("server.processlist")); ?>" target="right"><?php hm("processlist"); ?></a> <br/>
            <a href="<?php h(url("server.command")); ?>" target="right"><?php hm("command"); ?></a> <br/>
            <a href="<?php h(url("server.execute")); ?>" target="right"><?php hm("execute"); ?></a> <br/>
            <a href="<?php h(url("server.replication")); ?>" target="right"><?php hm("master_slave"); ?></a>
        </div>

    </body>
</html>