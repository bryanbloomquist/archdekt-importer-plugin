<?php

namespace ArchidektImporter\Includes\DecksDataTable;

/**
 * Class RetrieveData
 * This class extends the WP_List_Table class to display a custom table in the WordPress Admin area
 */
class RetrieveData
{

  /**
   * Retrieve the data for the table
   */
  public static function retrieve_table_data()
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
          // get the admin url for the deck
          $deck_url = get_edit_post_link($post_id, 'url');
          $decks[$post_id][$meta_key] = '<span class="sort-by"' . $meta_value . '></span><a href="' . $deck_url . '">' . $meta_value . '</a><a href="https://archidekt.com/decks/' . $deck_id . '" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-external"></span></a>';
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
}
