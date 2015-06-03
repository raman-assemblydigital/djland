<?php
/**
 * Created by PhpStorm.
 * User: Evan
 * Date: 5/6/2015
 * Time: 2:59 PM
 */
session_start();
require_once("headers/security_header.php");
require_once("headers/function_header.php");
require_once("headers/menu_header.php");

if( permission_level() >= $djland_permission_levels['administrator']){ ?>
    <html>
    <head>
        <meta name=ROBOTS content=\"NOINDEX, NOFOLLOW\">
        <meta charset="utf-8">
        <link rel=stylesheet href='css/style.css' type='text/css'>

        <title>DJLAND | Admin Tools</title>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="js/jquery.form.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
        <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
        <script type="text/javascript" src="js/admin.js"></script>
        <script>
            $(function() {
                $( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
            });
        </script>
    </head>
    <body class='wallpaper'>
    <?php
    print_menu();
    ?>
    <div class="wrapper">
        <h1>Administrator Tools</h1>
        
    </div>

    </body>
    </html>
<?php }else{
    header("Location: main.php");
}?>