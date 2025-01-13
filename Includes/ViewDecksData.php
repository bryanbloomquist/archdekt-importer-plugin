<?php

namespace ArchidektImporter\Includes;

class ViewDecksData
{
  /**
   * Create a new Admin page for viewing all decks data
   */
  public static function viewDecksDataPage()
  {
    add_menu_page(
      'View Decks Data',
      'View Decks Data',
      'manage_options',
      'view-decks-data',
      [self::class, 'viewDecksDataPageContent'],
      'dashicons-editor-table',
      25
    );
  }

  public static function viewDecksDataPageContent()
  {
    // sort by title
    $decks = get_posts(array(
      'post_type' => 'deck',
      'numberposts' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
    ));
?>
    <div class="wrap deck-table-wrap">
      <h2>View Deck Data</h2>
      <table class="wp-list-table widefat striped">
        <thead>
          <tr>
            <th>Deck Name</th>
            <th>Commander</th>
            <th>Partner/Background</th>
            <th>Color Identity</th>
            <th class="vert-text"><span class="rotate">Salt Sum<sup class="popover" title="The EDH Salt Score is a crowd sourced indicator of how salty cards make players in EDH. The data is aggregated by EDHREC. A card's Salt Score is on a scale of 0 to 4. The Salt sum to the total sum of all salt scores of all cards in your deck.">&#9432;</sup></span></th>
            <th class="vert-text"><span class="rotate">Deck Value</span></th>
            <th class="vert-text"><span class="rotate">Total Mana Value</span></th>
            <th class="vert-text"><span class="rotate">Avg Mana Value</span></th>
            <th class="vert-text"><span class="rotate">Battles</span></th>
            <th class="vert-text"><span class="rotate">Planeswalkers</span></th>
            <th class="vert-text"><span class="rotate">Creatures</span></th>
            <th class="vert-text"><span class="rotate">Sorceries</span></th>
            <th class="vert-text"><span class="rotate">Instants</span></th>
            <th class="vert-text"><span class="rotate">Artifacts</span></th>
            <th class="vert-text"><span class="rotate">Enchantments</span></th>
            <th class="vert-text"><span class="rotate">Lands</span></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($decks as $deck) :
            $deck_data = get_post_meta($deck->ID, 'archidekt_deck_data', true);
            echo self::fillTableRow($deck_data);
          endforeach; ?>
        </tbody>
      </table>
    </div>
<?php
  }

  public static function fillTableRow($deck)
  {
    $sorted = DecksPostType::sortDeckData($deck);

    $deck_name = $deck['name'];
    $deck_url  = "https://archidekt.com/decks/{$deck['id']}";
    $commander = $sorted['Commander'][0]['card']['oracleCard']['name'] ?? '';
    $partner   = $sorted['Commander'][1]['card']['oracleCard']['name'] ?? '';
    $identity  = self::getColorIdentity($sorted['Commander']);
    $values    = self::getMiscValues($deck);
    $nonLands  = ($partner ? '98' : '99') - count($sorted['Land']);

    $output  = '<tr>';
    $output .= '<td><a href="' . $deck_url . '" target="_blank" rel="noreferrer noopener">' . $deck_name . '</a></td>';
    $output .= '<td>' . $commander . '</td>';
    $output .= '<td>' . $partner . '</td>';
    $output .= '<td class="text-center">' . $identity . '</td>';
    $output .= '<td class="text-center">' . $values['saltSum'] . '</td>';
    $output .= '<td class="text-center">$' . number_format($values['price'], 2) . '</td>';
    $output .= '<td class="text-center">' . $values['manaValue'] . '</td>';
    $output .= '<td class="text-center">' . number_format($values['manaValue'] / $nonLands, 2) . '</td>';
    $output .= '<td class="text-center">' . count($sorted['Battle']) . '</td>';
    $output .= '<td class="text-center">' . count($sorted['Planeswalker']) . '</td>';
    $output .= '<td class="text-center">' . count($sorted['Creature']) . '</td>';
    $output .= '<td class="text-center">' . count($sorted['Sorcery']) . '</td>';
    $output .= '<td class="text-center">' . count($sorted['Instant']) . '</td>';
    $output .= '<td class="text-center">' . count($sorted['Artifact']) . '</td>';
    $output .= '<td class="text-center">' . count($sorted['Enchantment']) . '</td>';
    $output .= '<td class="text-center">' . count($sorted['Land']) . '</td>';
    $output .= '</tr>';

    return $output;
  }

  public static function getColorIdentity($commanders)
  {
    $colorsArray = [];
    $colorsString = '';

    foreach ($commanders as $commander) {
      $cardColors = $commander['card']['oracleCard']['colorIdentity'];
      foreach ($cardColors as $color) {
        if (!in_array($color, $colorsArray)) {
          $colorsArray[] = $color;
        }
      }
    }

    if (empty($colorsArray)) {
      $colorsString = '<span class="mana-colorless"><span class="path1"></span><span class="path2"></span></span>';;
    } else {
      foreach ($colorsArray as $color) {
        $colorsString .= '<span class="mana-' . strtolower($color) . '"><span class="path1"></span><span class="path2"></span></span>';
      }
    }

    return $colorsString;
  }

  public static function getSaltSum($deck)
  {
    $saltSum = 0;
    foreach ($deck['cards'] as $card) {
      $saltSum += $card['card']['oracleCard']['salt'];
    }

    return $saltSum;
  }

  public static function getMiscValues($deck)
  {
    $saltSum   = 0;
    $price     = 0;
    $manaValue = 0;

    foreach ($deck['cards'] as $card) {
      $saltSum   += $card['card']['oracleCard']['salt'];
      $price     += $card['card']['prices']['ck'];
      if ($card['categories'][0] !== 'Commander') {
        $manaValue += $card['card']['oracleCard']['cmc'];
      }
    }

    return [
      'saltSum' => $saltSum,
      'price' => $price,
      'manaValue' => $manaValue
    ];
  }
}

add_action('admin_menu', [ViewDecksData::class, 'viewDecksDataPage']);
