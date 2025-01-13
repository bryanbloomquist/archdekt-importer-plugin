<?php

namespace ArchidektImporter\Includes;

class ProcessIncomingDeck
{
    /**
     * Sort deck by primary card type
     */

    public static function sortCardsByType($deck_data)
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


    /**
     * Get the remaining deck data
     */

    public static function calculateMiscValues($deck)
    {
        $saltSum   = 0;
        $price     = 0;
        $manaValue = 0;

        foreach ($deck['cards'] as $card) {
            $saltSum += $card['card']['oracleCard']['salt'];
            $price   += $card['card']['prices']['ck'];
            if ($card['categories'][0] !== 'Commander') {
                $manaValue += $card['card']['oracleCard']['cmc'];
            }
        }

        return [
            'salt_sum'   => $saltSum,
            'price'      => $price,
            'mana_value' => $manaValue
        ];
    }

    /**
     * Get the number of cards per type (some cards allow duplicates of it to be in the same deck)
     */

    public static function countCardType($cards)
    {
        $card_count = 0;
        foreach ($cards as $card) {
            $card_count += $card['quantity'];
        }
        return $card_count;
    }
}
