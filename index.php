<?php
// $Id: index.php,v 1.66 2003/08/12 15:57:16 dries Exp $

include_once "includes/common.inc";

if (!empty($_GET["q"])) {
  if (module_exist("node") && $path = node_get_alias($_GET["q"])) {
    $_GET["q"] = $path;
  }
}
else {
  $_GET["q"] = variable_get("site_frontpage", "node");
}

$mod = arg(0);

drupal_page_header();

if (isset($mod) && module_hook($mod, "page")) {
  module_invoke($mod, "page");
}
else {
  check_php_setting("magic_quotes_gpc", 0);

  if (module_hook(variable_get("site_frontpage", "node"), "page")) {
    module_invoke(variable_get("site_frontpage", "node"), "page");
  }
  else {
    theme("header");
    theme("footer");
  }
}

drupal_page_footer();

?>
