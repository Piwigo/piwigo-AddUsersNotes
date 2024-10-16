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
  global $template;
  $template->assign(
    array(
	 'USERNOTES_PATH2'=> get_root_url().USERNOTES_PATH,
    )
  );
}

add_event_handler('loc_begin_admin_page', 'usernotes_add_column');
function usernotes_add_column(){
  global $template;
	$template->set_prefilter('user_list', 'usernotes_add_column_prefilter');
}

function usernotes_add_column_prefilter($content){
  // add js link
  $search = '<div class="selection-mode-group-manager" style="right:30px">';
  $replace = '{combine_script id="jquery.usersnotes" load=\'footer\' path="$USERNOTES_PATH2/js/usersnotes.js"}';
  $content = str_replace($search, $replace.$search, $content);
	
	
	// add "?" next to username for usernotes
  $search = '<span><!-- name --></span>';

  $replace = '<i class="user-notes-icon icon-info-circled-1 tiptip"> </i>';

  $content = str_replace($search, $search.$replace, $content);

  // add the "Notes" column in the user table
  // $search = '<!-- groups -->
  //      <div class="user-header-col user-header-groups">
  //      <span>{\'Groups\'|@translate}</span>
  //    </div>';
  // $content = str_replace($search, $search.'<!-- Notes -->
  //   <div class="user-header-col user-header-usernotes">
  //      <span>{\'Notes\'|@translate}</th></span>
  //   </div>'
	// , $content);

  // add the "Notes" 
  // $search = '<div class="user-col user-container-groups">
  //     <!-- groups -->
  //   </div>';
  // $replace = '    <div class="user-col user-container-usernotes">
  //     <span><!-- usernotes --></span>
  //   </div>';
  // $content = str_replace($search, $search.$replace, $content);

  // add the "Notes" field in user profile form
  $search = '<span class="user-property-register"><!-- Registered date XX/XX/XXXX --></span>
            <span class="icon-calendar"></span>
            <span class="user-property-last-visit"><!-- Last Visit date XX/XX/XXXX --></span>
          </div>';
  $replace = '<p class="user-property-label usernotes-label-edit">{\'Notes\'|@translate}<span class="edit-usernotes icon-pencil"></span></p>
		  <div class="user-property-usernotes">
            <span class="usernotes-title"><!-- usernotes --></span>
          </div>
		  <div class="user-property-usernotes-change">
            <div class="summary-input-container">
              <input class="usernotes-property-input user-property-input-usernotes" value="" placeholder="{\'Notes\'|@translate}" />
            </div>
            <span class="icon-ok edit-usernotes-validate"></span>
            <span class="icon-cancel-circled edit-usernotes-cancel"></span>
          </div>
		  ';
  $content = str_replace($search, $search.$replace, $content);
  
  //css
    $search = '</style>';
  $replace = '
  .user-property-usernotes-change {
    justify-content:center;
    align-items:center;
    display:none;
    margin-bottom:25px;
  }
  
  .user-property-usernotes {
    margin-bottom:34px;
    height:30px;
    width: 100%;
    padding: 8px;
    background-color: #F3F3F3;
    padding-bottom: 16px;
}

.edit-usernotes-validate {
    display: block;
    margin: auto 5px;
    cursor: pointer;
    background-color: #ffa744;
    color: #3c3c3c;
    font-size: 17px;
    font-weight: 700;
    padding: 7px;
}

.edit-usernotes-validate:hover {
    background-color: #f70;
    color: #000;
    cursor: pointer;
}
.edit-usernotes {
    font-size:1.4em;
    cursor:pointer;
    color: #A4A4A4 !important;
}
.edit-usernotes-cancel {
    cursor:pointer;
    font-size:22px;
    padding-top: 4px;
}
.usernotes-property-input {
    width: 100%;
    box-sizing:border-box;
    font-size:1.1em;
    padding:8px 16px;
    border:none;
}
.edit-usernotes-title {
    font-size:1.4em;
}
.usernotes-title {
  color: #353535;
  display: flex;
  justify-content: left;

  --line-height: 1.4;
  --num-lines: 2;
  line-height: var(--line-height);
  display: block;
  height: calc(1em * var(--line-height) * var(--num-lines));
  display: -webkit-box;
  -webkit-line-clamp: var(--num-lines);
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 5px;
}
.usernotes-property-input.user-property-input-usernotes {
    border: solid 2px #ffa744;
    padding: 9px;
}

.user-header-usernotes{
  width: 20%;
  max-width: 195px;
}
.user-container-usernotes {
  width: 20%;
  max-width: 195px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.user-container-usernotes span {
  max-width: 100%;

  overflow: hidden;
  text-overflow: ellipsis;
}
.usernotes-label-edit {
  align-self: flex-start;
}

.user-property-register-visit {
  margin-top: -15px;
}
		  ';
  $content = str_replace($search, $replace.$search, $content);
    
  return $content;
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
