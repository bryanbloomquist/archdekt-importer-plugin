<?php

namespace ArchidektImporter\Includes\ImportDeckData;

use ArchidektImporter\Includes\ImportDeckData\GetColorIdentity as GetColorIdentity;

/**
 * Class ProcessImportedData
 * This class processes the incoming deck data
 */
class ProcessImportedData
{

	/**
	 * Sort deck by primary card type
	 */
	public static function sort_cards_by_type($deck_data)
	{
		$sorted_deck_data = [
			'Commander'    => [],
			'Planeswalker' => [],
			'Battle'       => [],
			'Creature'     => [],
			'Sorcery'      => [],
			'Instant'      => [],
			'Artifact'     => [],
			'Enchantment'  => [],
			'Land'         => [],
			'Sideboard'    => [],
			'Maybeboard'   => []
		];

		foreach ($deck_data['cards'] as $card) {
			$category = $card['categories'][0];
			$sorted_deck_data[$category][] = $card;
		}

		return $sorted_deck_data;
	}

	/**
	 * Get the color identity of the deck
	 */
	public static function get_color_identity($commanders)
	{
		$colors_array = [];
		$colors_string = '';

		foreach ($commanders as $commander) {
			$card_colors = $commander['card']['oracleCard']['colorIdentity'];
			foreach ($card_colors as $color) {
				if (!in_array($color, $colors_array)) {
					$colors_array[] = $color;
				}
			}
		}

		$color_data_array = GetColorIdentity::get_color_identity_array($colors_array);
		$color_identity   = $color_data_array['color_identity'];
		$color_symbols    = $color_data_array['color_symbols'];

		$colors_string .= '<span class="gradient ' . strtolower($color_identity) . '">';
		$colors_string .= '<span class="mana-symbols">';
		$colors_string .= $color_symbols;
		$colors_string .= '</span></span>';

		return $colors_string;
	}

	/**
	 * Get the remaining deck data
	 */
	public static function calculate_misc_values($deck)
	{
		$salt_sum   = 0;
		$price      = 0;
		$mana_value = 0;

		foreach ($deck['cards'] as $card) {
			for ($i = 0; $i < $card['quantity']; $i++) {
				$salt_sum += $card['card']['oracleCard']['salt'];
				$price += $card['card']['prices']['ck'];
				if ($card['categories'][0] !== 'Commander') {
					$mana_value += $card['card']['oracleCard']['cmc'];
				}
			}
		}

		return [
			'salt_sum'   => $salt_sum,
			'price'      => $price,
			'mana_value' => $mana_value
		];
	}

	/**
	 * Get the number of cards per type (some cards allow duplicates of it to be in the same deck)
	 */
	public static function count_card_type($cards)
	{
		$card_count = 0;
		foreach ($cards as $card) {
			$card_count += $card['quantity'];
		}
		return $card_count;
	}
}
