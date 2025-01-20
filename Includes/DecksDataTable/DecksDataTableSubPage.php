<?php

namespace ArchidektImporter\Includes\DecksDataTable;

use ArchidektImporter\Includes\DecksDataTable\RetrieveTableData as RetrieveTableData;
use ArchidektImporter\Includes\DecksDataTable\DecksTableColumns as DecksTableColumns;

/**
 * Check if the WP_List_Table class exists, if not, include it
 */
if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class DecksDataTablePage
 * This class extends the WP_List_Table class to display a custom table in the WordPress Admin area
 */
class DecksDataTablePage extends \WP_List_Table
{
  /**
   * The data for the table
   */
  private $table_data;

  /**
   * Add the table page to the WordPress admin menu
   */
  public static function add_view_decks_data_page()
  {
    global $deckTablePage;

    $deckTablePage = add_submenu_page(
      'edit.php?post_type=deck',
      'View Decks Data',
      'View Decks Data',
      'manage_options',
      'view-decks-table',
      [self::class, 'view_decks_data_table'],
      25
    );
  }

  /**
   * Display the content of the table page
   */
  public static function view_decks_data_table()
  {
    $deckTable = new DecksDataTablePage();

    echo '<div class="wrap deck-table-wrap"><h2>View Deck Data</h2>';
    $deckTable->prepare_table_items();
    $deckTable->display();
    echo '</div>';
  }

  /**
   * Prepare the items for the table
   */
  public function prepare_table_items()
  {
    $table_data = RetrieveTableData::retrieve_table_data();
    $columns    = DecksTableColumns::define_table_columns();
    $hidden     = [];
    $sortable   = DecksTableColumns::set_sortable_columns();
    $primary    = 'deck_name';

    $this->_column_headers = [$columns, $hidden, $sortable, $primary];

    usort($table_data, [self::class, 'usort_reorder']);

    $this->items = $table_data;
  }

  /**
   * Default column rendering
   */
  public function column_default($item, $column_name)
  {
    switch ($column_name) {
      case 'deck_name':
      case 'commander':
      case 'partner':
      case 'identity':
      case 'salt_sum':
      case 'deck_price':
      case 'total_mana':
      case 'average_mana':
      case 'battles':
      case 'planeswalkers':
      case 'creatures':
      case 'sorceries':
      case 'instants':
      case 'artifacts':
      case 'enchantments':
      case 'lands':
        return $item->$column_name;
      default:
        return print_r($item, true);
    }
  }

  /**
   * Sort the data
   */
  public function usort_reorder($a, $b)
  {
    $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'deck_name';
    $order   = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

    if (is_numeric($a->$orderby) && is_numeric($b->$orderby)) {
      $result = $a->$orderby - $b->$orderby;
    } else {
      $result = strcmp($a->$orderby, $b->$orderby);
    }

    return ($order === 'asc') ? $result : -$result;
  }
}

add_action('admin_menu', [DecksDataTablePage::class, 'add_view_decks_data_page']);
