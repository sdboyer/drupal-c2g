<?php
// $Id: index.php,v 1.84 2005/05/21 18:33:58 dries Exp $

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * The routines here dispatch control to the appropriate handler, which then
 * prints the appropriate page.
 */

include_once 'includes/bootstrap.inc';
drupal_page_header();
include_once 'includes/common.inc';

fix_gpc_magic();
fix_checkboxes();

$return = menu_execute_active_handler();
switch ($return) {
  case MENU_NOT_FOUND:
    drupal_not_found();
    break;
  case MENU_ACCESS_DENIED:
    drupal_access_denied();
    break;
  default:
    if (!empty($return)) {
      print theme('page', $return);
    }
    break;
}

drupal_page_footer();

?>
