<?php
// $Id: update.php,v 1.160 2005/12/06 09:25:03 dries Exp $

/**
 * @file
 * Administrative page for handling updates from one Drupal version to another.
 *
 * Point your browser to "http://www.site.com/update.php" and follow the
 * instructions.
 *
 * If you are not logged in as administrator, you will need to modify the access
 * check statement below. Change the TRUE into a FALSE to disable the access
 * check. After finishing the upgrade, be sure to open this file and change the
 * FALSE back into a TRUE!
 */

// Enforce access checking?
$access_check = TRUE;


define('SCHEMA', 0);
define('SCHEMA_MIN', 1);

/**
 * Includes install files.
 */
function update_include_install_files() {
  // The system module (Drupal core) is currently a special case
  include_once './database/updates.inc';

  foreach (module_list() as $module) {
    $install_file = './'. drupal_get_path('module', $module) .'/'. $module .'.install';
    if (is_file($install_file)) {
      include_once $install_file;
    }
  }
}

function update_sql($sql) {
  $result = db_query($sql);
  return array('success' => $result !== FALSE, 'query' => check_plain($sql));
}

/**
 * Adds a column to a database. Uses syntax appropriate for PostgreSQL.
 * Saves result of SQL commands in $ret array.
 *
 * Note: when you add a column with NOT NULL and you are not sure if there are
 * rows in table already, you MUST also add DEFAULT. Otherwise PostgreSQL won't
 * work if the table is not empty. If NOT NULL and DEFAULT is set the
 * PostgreSQL version will set values of the added column in old rows to the
 * DEFAULT value.
 *
 * @param $ret
 *  Array to which results will be added.
 * @param $table
 *  Name of the table, without {}
 * @param $column
 *  Name of the column
 * @param $type
 *  Type of column
 * @param $attributes
 *  Additional optional attributes. Recognized atributes:
 *    - not null    => TRUE/FALSE
 *    - default     => NULL/FALSE/value (with or without '', it wont' be added)
 * @return
 *  nothing, but modifies $ret parametr.
 */
function db_add_column(&$ret, $table, $column, $type, $attributes = array()) {
  if (array_key_exists('not null', $attributes) and $attributes['not null']) {
    $not_null = 'NOT NULL';
  }
  if (array_key_exists('default', $attributes)) {
    if (is_null($attributes['default'])) {
      $default_val = 'NULL';
      $default = 'default NULL';
    }
    elseif ($attributes['default'] === FALSE) {
      $default = '';
    }
    else {
      $default_val = "$attributes[default]";
      $default = "default $attributes[default]";
    }
  }

  $ret[] = update_sql("ALTER TABLE {". $table ."} ADD $column $type");
  if ($default) { $ret[] = update_sql("ALTER TABLE {". $table ."} ALTER $column SET $default"); }
  if ($not_null) {
    if ($default) { $ret[] = update_sql("UPDATE {". $table ."} SET $column = $default_val"); }
    $ret[] = update_sql("ALTER TABLE {". $table ."} ALTER $column SET NOT NULL");
  }
}

/**
 * Changes a column definition. Uses syntax appropriate for PostgreSQL.
 * Saves result of SQL commands in $ret array.
 *
 * @param $ret
 *  Array to which results will be added.
 * @param $table
 *  Name of the table, without {}
 * @param $column
 *  Name of the column to change
 * @param $column_new
 *  New name for the column (set to the same as $column if you don't want to change the name)
 * @param $type
 *  Type of column
 * @param $attributes
 *  Additional optional attributes. Recognized atributes:
 *    - not null    => TRUE/FALSE
 *    - default     => NULL/FALSE/value (with or without '', it wont' be added)
 * @return
 *  nothing, but modifies $ret parametr.
 */
function db_change_column(&$ret, $table, $column, $column_new, $type, $attributes = array()) {
  if (array_key_exists('not null', $attributes) and $attributes['not null']) {
    $not_null = 'NOT NULL';
  }
  if (array_key_exists('default', $attributes)) {
    if (is_null($attributes['default'])) {
      $default_val = 'NULL';
      $default = 'default NULL';
    }
    elseif ($attributes['default'] === FALSE) {
      $default = '';
    }
    else {
      $default_val = "$attributes[default]";
      $default = "default $attributes[default]";
    }
  }

  $ret[] = update_sql("ALTER TABLE {". $table ."} RENAME $column TO ". $column ."_old");
  $ret[] = update_sql("ALTER TABLE {". $table ."} ADD $column_new $type");
  $ret[] = update_sql("UPDATE {". $table ."} SET $column_new = ". $column ."_old");
  if ($default) { $ret[] = update_sql("ALTER TABLE {". $table ."} ALTER $column_new SET $default"); }
  if ($not_null) { $ret[] = update_sql("ALTER TABLE {". $table ."} ALTER $column_new SET NOT NULL"); }
  // We don't drop columns for now
  // $ret[] = update_sql("ALTER TABLE {". $table ."} DROP ". $column ."_old");
}

/**
 * If the schema version for Drupal core is stored in the the variables table
 * (4.6.x and earlier) move it to the schema_version column of the system
 * table.
 *
 * This function may be removed when update 156 is removed, which is the last
 * update in the 4.6 to 4.7 migration.
 */
function update_fix_schema_version() {
  if ($update_start = variable_get('update_start', FALSE)) {
    // Some updates were made to the 4.6 branch and 4.7 branch. This sets
    // temporary variables to provent the updates from being executed twice and
    // throwing errors.
    switch ($update_start) {
      case '2005-04-14':
        variable_set('update_132_done', TRUE);
        break;

      case '2005-05-06':
        variable_set('update_132_done', TRUE);
        variable_set('update_135_done', TRUE);
        break;

      case '2005-05-07':
        variable_set('update_132_done', TRUE);
        variable_set('update_135_done', TRUE);
        variable_set('update_137_done', TRUE);
        break;

    }

    $sql_updates = array(
      '2004-10-31: first update since Drupal 4.5.0 release' => 110,
      '2004-11-07' => 111, '2004-11-15' => 112, '2004-11-28' => 113,
      '2004-12-05' => 114, '2005-01-07' => 115, '2005-01-14' => 116,
      '2005-01-18' => 117, '2005-01-19' => 118, '2005-01-20' => 119,
      '2005-01-25' => 120, '2005-01-26' => 121, '2005-01-27' => 122,
      '2005-01-28' => 123, '2005-02-11' => 124, '2005-02-23' => 125,
      '2005-03-03' => 126, '2005-03-18' => 127, '2005-03-21' => 128,
      // The following three updates were made on the 4.6 branch
      '2005-04-14' => 129, '2005-05-06' => 129, '2005-05-07' => 129,
      '2005-04-08: first update since Drupal 4.6.0 release' => 129,
      '2005-04-10' => 130, '2005-04-11' => 131, '2005-04-14' => 132,
      '2005-04-24' => 133, '2005-04-30' => 134, '2005-05-06' => 135,
      '2005-05-08' => 136, '2005-05-09' => 137, '2005-05-10' => 138,
      '2005-05-11' => 139, '2005-05-12' => 140, '2005-05-22' => 141,
      '2005-07-29' => 142, '2005-07-30' => 143, '2005-08-08' => 144,
      '2005-08-15' => 145, '2005-08-25' => 146, '2005-09-07' => 147,
      '2005-09-18' => 148, '2005-09-27' => 149, '2005-10-15' => 150,
      '2005-10-23' => 151, '2005-10-28' => 152, '2005-11-03' => 153,
      '2005-11-14' => 154, '2005-11-27' => 155, '2005-12-03' => 156,
    );

    switch ($GLOBALS['db_type']) {
      case 'pgsql':
        $ret = array();
        db_add_column($ret, 'system', 'schema_version', 'int2', array('not null' => TRUE));
        break;

      case 'mysql':
      case 'mysqli':
        db_query('ALTER TABLE {system} ADD schema_version smallint(2) unsigned not null');
        break;
    }

    update_set_installed_version('system', $sql_updates[$update_start]);
    variable_del('update_start');
  }
}

/**
 * System update 130 changes the sessions table, which breaks the update
 * script's ability to use session variables. This changes the table
 * appropriately.
 *
 * This code, including the 'update_sessions_fixed' variable, may be removed
 * when update 130 is removed. It is part of the Drupal 4.6 to 4.7 migration.
 */
function update_fix_sessions() {
  $ret = array();

  if (update_get_installed_version('system') < 130 && !variable_get('update_sessions_fixed', FALSE)) {
    if ($GLOBALS['db_type'] == 'mysql') {
      db_query("ALTER TABLE {sessions} ADD cache int(11) NOT NULL default '0' AFTER timestamp");
    }
    elseif ($GLOBALS['db_type'] == 'pgsql') {
      db_add_column($ret, 'sessions', 'cache', 'int', array('default' => 0, 'not null' => TRUE));
    }

    variable_set('update_sessions_fixed', TRUE);
  }
}

/**
 * System update 142 changes the watchdog table, which breaks the update
 * script's ability to use logging. This changes the table appropriately.
 *
 * This code, including the 'update_watchdog_fixed' variable,  may be removed
 * when update 142 is removed. It is part of the Drupal 4.6 to 4.7 migration.
 */
function update_fix_watchdog() {
  if (update_get_installed_version('system') < 142 && !variable_get('update_watchdog_fixed', FALSE)) {
    switch ($GLOBALS['db_type']) {
      case 'pgsql':
        db_add_column($ret, 'watchdog', 'referer', 'varchar(128)', array('not null' => TRUE, 'default' => "''"));
        break;
      case 'mysql':
      case 'mysqli':
        $ret[] = db_query("ALTER TABLE {watchdog} ADD COLUMN referer varchar(128) NOT NULL");
        break;
    }

    variable_set('update_watchdog_fixed', TRUE);
  }
}

function update_data($module, $number) {
  $ret = module_invoke($module, 'update_'. $number);

  // Save the query and results for display by update_finished_page().
  if (!isset($_SESSION['update_results'])) {
    $_SESSION['update_results'] = array();
  }
  else if (!isset($_SESSION['update_results'][$module])) {
    $_SESSION['update_results'][$module] = array();
  }
  $_SESSION['update_results'][$module][$number] = $ret;

  // Update the installed version
  update_set_installed_version($module, $number);
}

/**
 * Returns an array of availiable schema versions for a module.
 *
 * @param $module
 *   A module name.
 * @return
 *   If the module has updates, an array of available updates. Otherwise,
 *   FALSE.
 */
function update_get_versions($module) {
  if (!($max = module_invoke($module, 'version', SCHEMA))) {
    return FALSE;
  }
  if (!($min = module_invoke($module, 'version', SCHEMA_MIN))) {
    $min = 1;
  }
  return range($min, $max);
}

/**
 * Returns the currently installed schema version for a module.
 *
 * @param $module
 *   A module name.
 * @return
 *   The currently installed schema version.
 */
function update_get_installed_version($module, $reset = FALSE) {
  static $versions;

  if ($reset) {
    unset($versions);
  }

  if (!$versions) {
    $versions = array();
    $result = db_query("SELECT name, schema_version FROM {system} WHERE type = 'module'");
    while ($row = db_fetch_object($result)) {
      $versions[$row->name] = $row->schema_version;
    }
  }

  return $versions[$module];
}

/**
 * Update the installed version information for a module.
 *
 * @param $module
 *   A module name.
 * @param $version
 *   The new schema version.
 */
function update_set_installed_version($module, $version) {
  db_query("UPDATE {system} SET schema_version = %d WHERE name = '%s'", $version, $module);
}

function update_selection_page() {
  $output = '<p>'. t('The version of Drupal you are updating from has been automatically detected. You can select a different version, but you should not need to.') .'</p>';
  $output .= '<p>'. t('Click Update to start the update process.') .'</p>';

  $form = array();
  $form['start'] = array(
    '#tree' => TRUE,
    '#type' => 'fieldset',
    '#title' => 'Select versions',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  foreach (module_list() as $module) {
    if (module_hook($module, 'version')) {
      $updates = drupal_map_assoc(update_get_versions($module));
      $updates[] = 'No updates available';

      $form['start'][$module] = array(
        '#type' => 'select',
        '#title' => t('%name module', array('%name' => $module)),
        '#default_value' => array_search(update_get_installed_version($module), $updates) + 1,
        '#options' => $updates,
      );
    }
  }

  $form['has_js'] = array(
    '#type' => 'hidden',
    '#default_value' => FALSE
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Update')
  );

  drupal_set_title('Drupal database update');
  drupal_add_js('misc/update.js');
  $output .= drupal_get_form('update_script_selection_form', $form);

  return $output;
}

function update_update_page() {
  // Set the installed version so updates start at the correct place.
  $_SESSION['update_remaining'] = array();
  foreach ($_POST['edit']['start'] as $module => $version) {
    update_set_installed_version($module, $version - 1);
    $max_version = max(update_get_versions($module));
    if ($version <= $max_version) {
      foreach (range($version, $max_version) as $update) {
        $_SESSION['update_remaining'][] = array('module' => $module, 'version' => $update);
      }
    }
  }
  // Keep track of total number of updates
  $_SESSION['update_total'] = count($_SESSION['update_remaining']);

  if ($_POST['edit']['has_js']) {
    return update_progress_page();
  }
  else {
    return update_progress_page_nojs();
  }
}

function update_progress_page() {
  drupal_add_js('misc/progress.js');
  drupal_add_js('misc/update.js');

  drupal_set_title('Updating');
  $output = '<div id="progress"></div>';
  $output .= '<p>Updating your site will take a few seconds.</p>';
  return $output;
}

/**
 * Perform updates for one second or until finished.
 *
 * @return
 *   An array indicating the status after doing updates. The first element is
 *   the overall percent finished. The second element is a status message.
 */
function update_do_updates() {
  foreach ($_SESSION['update_remaining'] as $key => $update) {
    update_data($update['module'], $update['version']);
    unset($_SESSION['update_remaining'][$key]);
    if (timer_read('page') > 1000) {
      break;
    }
  }

  if ($_SESSION['update_total']) {
    $percent = floor(($_SESSION['update_total'] - count($_SESSION['update_remaining'])) / $_SESSION['update_total'] * 100);
  }
  else {
    $percent = 100;
  }
  return array($percent, 'Updating '. $update['module'] .' module');
}

function update_do_update_page() {
  global $conf;

  // HTTP Post required
  if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    drupal_set_message('HTTP Post is required.', 'error');
    drupal_set_title('Error');
    return '';
  }

  // Any errors which happen would cause the result to not be parsed properly,
  // so we need to supporess them. All errors are still logged.
  if (!isset($conf['error_level'])) {
    $conf['error_level'] = 0;
  }
  ini_set('display_errors', FALSE);

  print implode('|', update_do_updates());
}

function update_progress_page_nojs() {
  $new_op = 'do_update_nojs';
  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    list($percent, $message) = update_do_updates();
    if ($percent == 100) {
      $new_op = 'finished';
    }
  }
  else {
    // This is the first page so return some output immediately.
    $percent = 0;
    $message = 'Starting updates...';
  }

  drupal_set_html_head('<meta http-equiv="Refresh" content="0; URL=update.php?op='. $new_op .'">');
  drupal_set_title('Updating');
  $output = theme('progress_bar', $percent, $message);
  $output .= '<p>Updating your site will take a few seconds.</p>';

  return $output;
}

function update_finished_page() {
  drupal_set_title('Drupal database update');
  // NOTE: we can't use l() here because the URL would point to 'update.php?q=admin'.
  $links[] = '<a href="">main page</a>';
  $links[] = '<a href="?q=admin">administration pages</a>';
  $output = '<p>Updates were attempted. If you see no failures below, you may proceed happily to the <a href="?q=admin">administration pages</a>. Otherwise, you may need to update your database manually. All errors have been <a href="?q=admin/logs">logged</a>.</p>';
  if ($GLOBALS['access_check'] == FALSE) {
    $output .= "<p><strong>Reminder: don't forget to set the <code>\$access_check</code> value at the top of <code>update.php</code> back to <code>TRUE</code>.</strong>";
  }
  $output .= theme('item_list', $links);

  // Output a list of queries executed
  if ($_SESSION['update_results']) {
    $output .= '<div id="update-results">';
    $output .= '<h2>The following queries were executed</h2>';
    foreach ($_SESSION['update_results'] as $module => $updates) {
      $output .= '<h3>'. $module .' module</h3>';
      foreach ($updates as $number => $queries) {
        $output .= '<h4>Update #'. $number .'</h4>';
        $output .= '<ul>';
        foreach ($queries as $query) {
          if ($query['success']) {
            $output .= '<li class="success">'. $query['query'] .'</li>';
          }
          else {
            $output .= '<li class="failure"><strong>Failed:</strong> '. $query['query'] .'</li>';
          }
        }
        if (!count($queries)) {
          $output .= '<li class="none">No queries</li>';
        }
        $output .= '</ul>';
      }
    }
    $output .= '</div>';
    unset($_SESSION['update_results']);
  }

  return $output;
}

function update_info_page() {
  drupal_set_title('Drupal database update');
  $output = "<ol>\n";
  $output .= "<li>Use this script to <strong>upgrade an existing Drupal installation</strong>. You don't need this script when installing Drupal from scratch.</li>";
  $output .= "<li>Before doing anything, backup your database. This process will change your database and its values, and some things might get lost.</li>\n";
  $output .= "<li>Update your Drupal sources, check the notes below and <a href=\"update.php?op=selection\">run the database upgrade script</a>. Don't upgrade your database twice as it may cause problems.</li>\n";
  $output .= "<li>Go through the various administration pages to change the existing and new settings to your liking.</li>\n";
  $output .= "</ol>";
  $output .= '<p>For more help, see the <a href="http://drupal.org/node/258">Installation and upgrading handbook</a>. If you are unsure what these terms mean you should probably contact your hosting provider.</p>';
  return $output;
}

function update_access_denied_page() {
  drupal_set_title('Access denied');
  return '<p>Access denied. You are not authorized to access this page. Please log in as the admin user (the first user you created). If you cannot log in, you will have to edit <code>update.php</code> to bypass this access check. To do this:</p>
<ol>
 <li>With a text editor find the update.php file on your system. It should be in the main Drupal directory that you installed all the files into.</li>
 <li>There is a line near top of update.php that says <code>$access_check = TRUE;</code>. Change it to <code>$access_check = FALSE;</code>.</li>
 <li>As soon as the script is done, you must change the update.php script back to its original form to <code>$access_check = TRUE;</code>.</li>
 <li>To avoid having this problem in future, remember to log in to your website as the admin user (the user you first created) before you backup your database at the beginning of the update process.</li>
</ol>';
}

include_once './includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
drupal_maintenance_theme();

// Access check:
if (($access_check == FALSE) || ($user->uid == 1)) {
  update_include_install_files();

  update_fix_schema_version();
  update_fix_sessions();
  update_fix_watchdog();

  $op = isset($_REQUEST['op']) ? $_REQUEST['op'] : '';
  switch ($op) {
    case 'Update':
      $output = update_update_page();
      break;

    case 'finished':
      $output = update_finished_page();
      break;

    case 'do_update':
      $output = update_do_update_page();
      break;

    case 'do_update_nojs':
      $output = update_progress_page_nojs();
      break;

    case 'selection':
      $output = update_selection_page();
      break;

    default:
      $output = update_info_page();
      break;
  }
}
else {
  $output = update_access_denied_page();
}

if (isset($output)) {
  print theme('maintenance_page', $output);
}
