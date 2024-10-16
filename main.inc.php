<?php
/*
Plugin Name: Add Users Notes
Version: auto
Description: Adds admin notes to users profiles
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=580
Author: ddtddt
Author URI: 
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

if (basename(dirname(__FILE__)) != 'AddUsersNotes')
{
  add_event_handler('init', 'usernotes_error');
  function usernotes_error()
  {
    global $page;
    $page['errors'][] = 'AddUsersNotes folder name is incorrect, uninstall the plugin and rename it to "AddUsersNotes"';
  }
  return;
}

global $prefixeTable;

// +-----------------------------------------------------------------------+
// | Define plugin constants                                               |
// +-----------------------------------------------------------------------+

define('USERNOTES_ID', basename(dirname(__FILE__)));
define('USERNOTES_PATH', PHPWG_PLUGINS_PATH.USERNOTES_ID.'/');

// init the plugin
add_event_handler('init', 'usernotes_init');

/**
 * plugin initialization
 *   - check for upgrades
 *   - load language
 */
function usernotes_init(){
  load_language('plugin.lang', USERNOTES_PATH);
}

// new tab in users modal
add_event_handler('loc_end_admin', 'usernotes_add_tab_users_modal');
function usernotes_add_tab_users_modal()
{
  global $template, $page;

  if ('user_list' === $page['page'])
  {
    $template->set_filename('usersnotes', realpath(USERNOTES_PATH . 'template/usersnotes.tpl'));
    $template->assign(array(
      'USERNOTES_PATH' => USERNOTES_PATH,
    ));
    $template->parse('usersnotes');
  }
}

add_event_handler('ws_invoke_allowed', 'usernotes_ws_users_setInfo', EVENT_HANDLER_PRIORITY_NEUTRAL, 3);
function usernotes_ws_users_setInfo($res, $methodName, $params){
  if ($methodName != 'pwg.users.setInfo'){
    return $res;
  }
  if (!isset($_POST['usernotes'])){
    return $res;
  }
  if (count($params['user_id']) == 0){
    return $res;
  }
  
  $updates = array();

  foreach ($params['user_id'] as $user_id){
    $updates[] = array(
      'user_id' => $user_id,
      'usernotes' => $_POST['usernotes'],
    );
  }
  if (count($updates) > 0){
    mass_updates(
      USER_INFOS_TABLE,
      array(
        'primary' => array('user_id'),
        'update'  => array('usernotes')
      ),
      $updates
    );
  }
  return $res;
}

add_event_handler('ws_users_getList', 'usernotes_ws_users_getList', EVENT_HANDLER_PRIORITY_NEUTRAL, 1);
function usernotes_ws_users_getList($users){
  $user_ids = array();
  foreach ($users as $user_id => $user){
    $user_ids[] = $user_id;
  }
  if (count($user_ids) == 0){
    return $users;
  }
  $query = '
    SELECT
      user_id,
      usernotes
    FROM '.USER_INFOS_TABLE.'
      WHERE user_id IN ('.implode(',', $user_ids).')
  ;';
  $result = pwg_query($query);
  while ($row = pwg_db_fetch_assoc($result)){
    $users[$row['user_id']]['usernotes'] = $row['usernotes'];
  }
  return $users;
}

?>
