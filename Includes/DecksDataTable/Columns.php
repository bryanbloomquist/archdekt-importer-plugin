<?php

namespace ArchidektImporter\Includes\DecksDataTable;

/**
 * Class Columns
 * This class extends the WP_List_Table class to display a custom table in the WordPress Admin area
 */
class Columns
{

  /**
   * Define the columns for the table
   */
  public static function define_table_columns()
  {
    $columns = [
      'deck_name'     => 'Deck Name',
      'identity'      => 'Identity',
      'commander'     => 'Commander',
      'partner'       => 'Partner/Background',
      'has_win'       => 'Winner',
      'power_rank'    => 'Power Rank',
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
   * Define sortable columns
   */
  public static function set_sortable_columns()
  {
    $sortable_columns = [
      'deck_name'     => ['deck_name', false],
      'identity'      => ['identity', false],
      'commander'     => ['commander', false],
      'partner'       => ['partner', false],
      'has_win'       => ['has_win', true],
      'power_rank'    => ['power_rank', true],
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
}
