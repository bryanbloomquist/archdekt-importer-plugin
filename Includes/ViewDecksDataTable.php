<?php

namespace ArchidektImporter\Includes;

/**
 * Check if the WP_List_Table class exists, if not, include it
 */
if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class ViewDecksDataTable
 * This class extends the WP_List_Table class to display a custom table in the WordPress Admin area
 */
class ViewDecksDataTable extends \WP_List_Table
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

    $deckTablePage = add_menu_page(
      'View Decks Data',
      'View Decks Data',
      'manage_options',
      'view-decks-table',
      [self::class, 'view_decks_data_table'],
      'dashicons-editor-table',
      25
    );
  }

  /**
   * Display the content of the table page
   */
  public static function view_decks_data_table()
  {
    $deckTable = new ViewDecksDataTable();

    echo '<div class="wrap deck-table-wrap"><h2>View Deck Data</h2>';
    $deckTable->prepare_table_items();
    $deckTable->display();
    echo '</div>';
  }

  /**
   * Define the columns for the table
   */
  public static function define_table_columns()
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

  /**
   * Prepare the items for the table
   */
  public function prepare_table_items()
  {
    $table_data = self::retrieve_table_data();
    $columns    = self::define_table_columns();
    $hidden     = [];
    $sortable   = self::set_sortable_columns();
    $primary    = 'deck_name';

    $this->_column_headers = [$columns, $hidden, $sortable, $primary];

    usort($table_data, [self::class, 'usort_reorder']);

    $this->items = $table_data;
  }

  /**
   * Retrieve the data for the table
   */
  public function retrieve_table_data()
  {
    global $wpdb;

    $query = "
        SELECT p.ID, p.post_title AS deck_name, pm.meta_key, pm.meta_value
        FROM {$wpdb->posts} AS p
        LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
        WHERE p.post_type = 'deck'
          AND p.post_status = 'publish'
    ";

    $raw_results = $wpdb->get_results($query);
    $decks = [];

    foreach ($raw_results as $row) {
      $post_id = $row->ID;
      $meta_key = $row->meta_key;
      $meta_value = $row->meta_value;
      $deck_id = get_post_meta($post_id, 'deck_id', true);

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

      if (array_key_exists($meta_key, $decks[$post_id])) {
        if ($meta_key === 'deck_name') {
          $decks[$post_id][$meta_key] = '<span class="sort-by"' . $meta_value . '></span><a href="https://archidekt.com/decks/' . $deck_id . '" target="_blank" rel="noopener noreferrer">' . $meta_value . '</a>';
        } elseif ($meta_key === 'salt_sum') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(69,69,69,' . ($meta_value / 50) . '); color: #fff;">' . $meta_value . '</span>';
        } elseif ($meta_key === 'deck_price') {
          $meta_value = ltrim($meta_value, '$');
          $decks[$post_id][$meta_key] = '<span class="sort-by">' . $meta_value . '</span><span class="gradient" style="background-color: rgba(133,187,101,' . ($meta_value / 500) . ');">' . '$' . $meta_value . '</span>';
        } elseif ($meta_key === 'total_mana') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(75,0,115,' . (($meta_value - 100) / 200) . '); color: #fff;">' . $meta_value . '</span>';
        } elseif ($meta_key === 'average_mana') {
          $sort_value = $meta_value * 100;
          $decks[$post_id][$meta_key] = '<span class="sort-by">' . $sort_value . '</span><span class="gradient" style="background-color: rgba(245,105,115,' . ($sort_value / 450) . ');">' . $meta_value . '</span>';
        } elseif ($meta_key === 'battles') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(255,105,5,' . (($meta_value + 1) / 5) . ');">' . $meta_value . '</span>';
        } elseif ($meta_key === 'planeswalkers') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(175,105,237,' . (($meta_value + 1) / 5) . ');">' . $meta_value . '</span>';
        } elseif ($meta_key === 'creatures') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(255,215,0,' . ($meta_value / 35) . ');">' . $meta_value . '</span>';
        } elseif ($meta_key === 'sorceries') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(55,110,255,' . ($meta_value / 20) . ');">' . $meta_value . '</span>';
        } elseif ($meta_key === 'instants') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(210,40,60,' . ($meta_value / 20) . ');">' . $meta_value . '</span>';
        } elseif ($meta_key === 'artifacts') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(185,115,55,' . ($meta_value / 15) . ');">' . $meta_value . '</span>';
        } elseif ($meta_key === 'enchantments') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(255,105,185,' . ($meta_value / 15) . ');">' . $meta_value . '</span>';
        } elseif ($meta_key === 'lands') {
          $decks[$post_id][$meta_key] = '<span class="gradient" style="background-color: rgba(45,155,0,' . (($meta_value - 30) / 10) . ');">' . $meta_value . '</span>';
        } else {
          $decks[$post_id][$meta_key] = $meta_value;
        }
      }
    }

    $data = array_map(function ($deck) {
      return (object) $deck;
    }, $decks);

    return $data;
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
   * Define sortable columns
   */
  public function set_sortable_columns()
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

add_action('admin_menu', [ViewDecksDataTable::class, 'add_view_decks_data_page']);
