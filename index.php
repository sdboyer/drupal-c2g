<?php
// $Id: index.php,v 1.75 2003/11/20 21:51:22 dries Exp $

include_once "includes/bootstrap.inc";
drupal_page_header();
include_once "includes/common.inc";

fix_gpc_magic();

menu_build("system");

if (menu_active_handler_exists()) {
  menu_execute_active_handler();
}
else {
  print theme("header");
  print theme("footer");
}

drupal_page_footer();

?>
