<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

class AddUsersNotes_maintain extends PluginMaintain
{
  private $installed = false;

  function __construct($plugin_id)
  {
    parent::__construct($plugin_id);
  }

  function install($plugin_version, &$errors=array())
  {
    global $prefixeTable;
    
    // add a new column to existing table
    $result = pwg_query('SHOW COLUMNS FROM `'.USER_INFOS_TABLE.'` LIKE "usernotes";');
    if (!pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `' . USER_INFOS_TABLE . '` ADD `usernotes` VARCHAR(255) DEFAULT NULL;');
    }

    // if we find the old table, we copy notes
    $result = pwg_query('SHOW TABLES LIKE "'.$prefixeTable.'user_notes";');
    if (pwg_db_num_rows($result))
    {
      $query = '
UPDATE
    '.USER_INFOS_TABLE.' AS ui,
    '.$prefixeTable.'user_notes AS un
  SET ui.usernotes = un.note
  WHERE ui.user_id = un.user_id
    AND LENGTH(note) > 0
;';
      pwg_query($query);

      pwg_query('DROP TABLE '.$prefixeTable.'user_notes;');
    }
    
    $this->installed = true;
  }

  function activate($plugin_version, &$errors=array())
  {
    if (!$this->installed)
    {
      $this->install($plugin_version, $errors);
    }
  }

  function update($old_version, $new_version, &$errors=array())
  {
    $this->install($new_version, $errors);
  }
  
  function deactivate()
  {
  }

  function uninstall()
  {
    pwg_query('ALTER TABLE `'. USER_INFOS_TABLE .'` DROP `usernotes`;');
  }
}
?>