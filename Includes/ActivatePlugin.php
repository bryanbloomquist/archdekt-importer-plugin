<?php

class ActivatePlugin
{
  public static function activate_archidekt_importer()
  {
    if (post_type_exists('deck')) {
      wp_die('Sorry, Archidekt Importer plugin could not be activated. A Custom Post Type named "decks" already exists.<br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
    }
  }
}
