<?php

namespace ArchidektImporter\Includes;

if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ViewDecksData extends \WP_List_Table
{
  private $table_data;

  public static function addTablePage()
  {
    global $deckTablePage;

    $deckTablePage = add_menu_page(
      'View Decks Table',
      'View Decks Table',
      'manage_options',
      'view-decks-table',
      [self::class, 'viewDecksTablePageContent'],
      'dashicons-editor-table',
      25
    );
  }

  public static function viewDecksTablePageContent()
  {
    $deckTable = new ViewDecksData();
    echo '<div class="wrap deck-table-wrap"><h2>View Deck Data</h2>';
    $deckTable->prepare_items();
    $deckTable->display();
    echo '</div>';
  }

  public static function setColumns()
  {
    $columns = [
      'deck_name'     => 'Deck Name',
      'commander'     => 'Commander',
      'partner'       => 'Partner/Background',
      'identity'      => 'Color Identity',
      'salt_sum'      => 'Salt Sum',
      'deck_price'    => 'Deck Value',
      'total_mana'    => 'Total Mana Value',
      'average_mana'  => 'Average Mana Value',
      'battles'       => 'Battles',
      'planeswalkers' => 'Planeswalkers',
      'creatures'     => 'Creatures',
      'sorceries'     => 'Sorceries',
      'instants'      => 'Instants',
      'artifacts'     => 'Artifacts',
      'enchantments'  => 'Enchantments',
      'lands'         => 'Lands'
    ];

    return $columns;
  }

  public function prepare_items()
  {
    $table_data = self::getTableData();

    $columns  = self::setColumns();
    $hidden   = [];
    $sortable = self::setSortableColumns();
    $primary  = 'deck_name';

    $this->_column_headers = [$columns, $hidden, $sortable, $primary];

    usort($table_data, [self::class, 'usortReorder']);

    $this->items = $table_data;
  }

  public function getTableData()
  {
    global $wpdb;

    // Fetch posts of the 'deck' custom post type
    $query = "
        SELECT p.ID, p.post_title AS deck_name, pm.meta_key, pm.meta_value
        FROM {$wpdb->posts} AS p
        LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
        WHERE p.post_type = 'deck'
          AND p.post_status = 'publish'
    ";

    // Get raw results
    $raw_results = $wpdb->get_results($query);

    // Organize data by post ID
    $decks = [];
    foreach ($raw_results as $row) {
      $post_id = $row->ID;
      $meta_key = $row->meta_key;
      $meta_value = $row->meta_value;

      if (!isset($decks[$post_id])) {
        $decks[$post_id] = [
          'deck_name'     => $row->deck_name,
          'commander'     => '',
          'partner'       => '',
          'identity'      => '',
          'salt_sum'      => 0,
          'deck_price'    => 0,
          'total_mana'    => 0,
          'average_mana'  => 0,
          'battles'       => 0,
          'planeswalkers' => 0,
          'creatures'     => 0,
          'sorceries'     => 0,
          'instants'      => 0,
          'artifacts'     => 0,
          'enchantments'  => 0,
          'lands'         => 0,
        ];
      }

      // Assign meta values to specific columns
      if (array_key_exists($meta_key, $decks[$post_id])) {
        $decks[$post_id][$meta_key] = $meta_value;
      }
    }

    // Convert the associative array to objects for compatibility
    $data = array_map(function ($deck) {
      return (object) $deck;
    }, $decks);

    return $data;
  }

  public function columnDefault($item, $column_name)
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
      default:
        return $item->$column_name;
    }
  }

  public function setSortableColumns()
  {
    $sortable_columns = [
      'deck_name'     => ['deck_name', false],
      'commander'     => ['commander', false],
      'partner'       => ['partner', false],
      'identity'      => ['identity', false],
      'salt_sum'      => ['salt_sum', true],
      'deck_price'    => ['deck_price', true],
      'total_mana'    => ['total_mana', true],
      'average_mana'  => ['average_mana', true],
      'battles'       => ['battles', true],
      'planeswalkers' => ['planeswalkers', true],
      'creatures'     => ['creatures', true],
      'sorceries'     => ['sorceries', true],
      'instants'      => ['instants', true],
      'artifacts'     => ['artifacts', true],
      'enchantments'  => ['enchantments', true],
      'lands'         => ['lands', true]
    ];

    return $sortable_columns;
  }

  public function usortReorder($a, $b)
  {
    $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'deck_name';
    $order   = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

    $result = strcmp($a->$orderby, $b->$orderby);

    return ($order === 'asc') ? $result : -$result;
  }
}

add_action('admin_menu', [ViewDecksData::class, 'addTablePage']);
