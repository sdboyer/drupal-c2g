<?php
// $Id: admin.php,v 1.57 2002/12/28 17:08:02 dries Exp $

include_once "includes/common.inc";

function status($message) {
  if ($message) {
    return "<b>Status:</b> $message<hr />\n";
  }
}

function admin_page($mod) {
  global $user;

 ?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1- transitional.dtd">
  <html>
   <head>
    <title><?php echo variable_get("site_name", "drupal") . " " . t("administration pages"); ?></title>
    <link rel="stylesheet" type=text/css media="screen" href="misc/admin.css" />
   </head>
   <body>
    <?php

      module_invoke_all("link", "admin");

      /*
      ** Menu:
      */

      print "<div id=\"menu\">";
      echo "<h1><a href=\"index.php\">".variable_get("site_name", "drupal")."</a></ h1>";
      print menu_tree() ;

      print "</div>";

      /*
      ** Body:
      */

      print "<div id=\"main\">";
      print "<a href=\"index.php\"><img align=\"right\" src=\"misc/druplicon-small.gif\" alt=\"Druplicon - Drupal logo\" border=\"0\" /></a>";

      if ($path = menu_path()) {
        print "<h2>". la(t("Administration")) ." &gt; $path</h2>";
      }
      else {
        print "<h2>". t("Administration") ."</h2>";
      }

      if ($menu = menu_menu()) {
        print "$menu<br />";
      }

      print "<br /><hr /><br />";

      if ($help = menu_help()) {
        print "<small>$help</small><br /><br />";
      }


      module_invoke($mod, "admin");
      print "</div>";

      db_query("DELETE FROM menu");
    ?>
  </body>
</html>
 <?php
}

if (user_access("access administration pages")) {
  page_header();
  admin_page($mod);
  page_footer();
}
else {
  print message_access();
}

?>
