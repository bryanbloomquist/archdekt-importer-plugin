<?php

namespace ArchidektImporter\Includes\ImportDeckData;

/**
 * Class GetColorIdentity
 * Get the named color identity and the color symbols
 */
class GetColorIdentity
{

	/**
	 * Get the named color identity and the color symbols
	 */
	public static function get_color_identity_array($colors_array)
	{

		$color_identity = '';

		$color_symbols = '';

		$colorless_mana_symbol = '<span class="mana-colorless"><span class="path1"></span><span class="path2"></span></span>';
		$white_mana_symbol     = '<span class="mana-white"><span class="path1"></span><span class="path2"></span></span>';
		$blue_mana_symbol      = '<span class="mana-blue"><span class="path1"></span><span class="path2"></span></span>';
		$black_mana_symbol     = '<span class="mana-black"><span class="path1"></span><span class="path2"></span></span>';
		$red_mana_symbol       = '<span class="mana-red"><span class="path1"></span><span class="path2"></span></span>';
		$green_mana_symbol     = '<span class="mana-green"><span class="path1"></span><span class="path2"></span></span>';

		if (empty($colors_array)) {
			$color_identity = '00 Colorless';
			$color_symbols  = $colorless_mana_symbol;
		} elseif (count($colors_array) === 1) {
			if (in_array('White', $colors_array)) {
				$color_identity = '01 Mono-White';
				$color_symbols  = $white_mana_symbol;
			} elseif (in_array('Blue', $colors_array)) {
				$color_identity = '02 Mono-Blue';
				$color_symbols  = $blue_mana_symbol;
			} elseif (in_array('Black', $colors_array)) {
				$color_identity = '03 Mono-Black';
				$color_symbols  = $black_mana_symbol;
			} elseif (in_array('Red', $colors_array)) {
				$color_identity = '04 Mono-Red';
				$color_symbols  = $red_mana_symbol;
			} elseif (in_array('Green', $colors_array)) {
				$color_identity = '05 Mono-Green';
				$color_symbols  = $green_mana_symbol;
			}
		} elseif (count($colors_array) === 2) {
			if (in_array('White', $colors_array) && in_array('Blue', $colors_array)) {
				$color_identity = '06 Azorius';
				$color_symbols  = $white_mana_symbol . $blue_mana_symbol;
			} elseif (in_array('White', $colors_array) && in_array('Black', $colors_array)) {
				$color_identity = '07 Orzhov';
				$color_symbols  = $white_mana_symbol . $black_mana_symbol;
			} elseif (in_array('Blue', $colors_array) && in_array('Black', $colors_array)) {
				$color_identity = '08 Dimir';
				$color_symbols  = $blue_mana_symbol . $black_mana_symbol;
			} elseif (in_array('Blue', $colors_array) && in_array('Red', $colors_array)) {
				$color_identity = '09 Izzet';
				$color_symbols  = $blue_mana_symbol . $red_mana_symbol;
			} elseif (in_array('Black', $colors_array) && in_array('Red', $colors_array)) {
				$color_identity = '10 Rakdos';
				$color_symbols  = $black_mana_symbol . $red_mana_symbol;
			} elseif (in_array('Black', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '11 Golgari';
				$color_symbols  = $black_mana_symbol . $green_mana_symbol;
			} elseif (in_array('Red', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '12 Gruul';
				$color_symbols  = $red_mana_symbol . $green_mana_symbol;
			} elseif (in_array('White', $colors_array) && in_array('Red', $colors_array)) {
				$color_identity = '13 Boros';
				$color_symbols  = $red_mana_symbol . $white_mana_symbol;
			} elseif (in_array('White', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '14 Selesnya';
				$color_symbols  = $green_mana_symbol . $white_mana_symbol;
			} elseif (in_array('Blue', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '15 Simic';
				$color_symbols  = $green_mana_symbol . $blue_mana_symbol;
			}
		} elseif (count($colors_array) === 3) {
			if (in_array('White', $colors_array) && in_array('Blue', $colors_array) && in_array('Black', $colors_array)) {
				$color_identity = '16 Esper';
				$color_symbols  = $white_mana_symbol . $blue_mana_symbol . $black_mana_symbol;
			} elseif (in_array('Blue', $colors_array) && in_array('Black', $colors_array) && in_array('Red', $colors_array)) {
				$color_identity = '17 Grixis';
				$color_symbols  = $blue_mana_symbol . $black_mana_symbol . $red_mana_symbol;
			} elseif (in_array('Black', $colors_array) && in_array('Red', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '18 Jund';
				$color_symbols  = $black_mana_symbol . $red_mana_symbol . $green_mana_symbol;
			} elseif (in_array('White', $colors_array) && in_array('Red', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '19 Naya';
				$color_symbols  = $red_mana_symbol . $green_mana_symbol . $white_mana_symbol;
			} elseif (in_array('White', $colors_array) && in_array('Blue', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '20 Bant';
				$color_symbols  = $green_mana_symbol . $white_mana_symbol . $blue_mana_symbol;
			} elseif (in_array('White', $colors_array) && in_array('Black', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '21 Abzan';
				$color_symbols  = $white_mana_symbol . $black_mana_symbol . $green_mana_symbol;
			} elseif (in_array('White', $colors_array) && in_array('Blue', $colors_array) && in_array('Red', $colors_array)) {
				$color_identity = '22 Jeskai';
				$color_symbols  = $blue_mana_symbol . $red_mana_symbol . $white_mana_symbol;
			} elseif (in_array('Blue', $colors_array) && in_array('Black', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '23 Sultai';
				$color_symbols  = $black_mana_symbol . $green_mana_symbol . $blue_mana_symbol;
			} elseif (in_array('White', $colors_array) && in_array('Black', $colors_array) && in_array('Red', $colors_array)) {
				$color_identity = '24 Mardu';
				$color_symbols  = $red_mana_symbol . $white_mana_symbol . $black_mana_symbol;
			} elseif (in_array('Blue', $colors_array) && in_array('Red', $colors_array) && in_array('Green', $colors_array)) {
				$color_identity = '25 Temur';
				$color_symbols  = $green_mana_symbol . $blue_mana_symbol . $red_mana_symbol;
			}
		} elseif (count($colors_array) === 4) {
			if (!in_array('Green', $colors_array)) {
				$color_identity = '26 Yore-Tiller';
				$color_symbols  = $white_mana_symbol . $blue_mana_symbol . $black_mana_symbol . $red_mana_symbol;
			} elseif (!in_array('Red', $colors_array)) {
				$color_identity = '27 Glint-Eye';
				$color_symbols  = $green_mana_symbol . $white_mana_symbol . $blue_mana_symbol . $black_mana_symbol;
			} elseif (!in_array('Black', $colors_array)) {
				$color_identity = '28 Dune-Brood';
				$color_symbols  = $red_mana_symbol . $green_mana_symbol . $white_mana_symbol . $blue_mana_symbol;
			} elseif (!in_array('Blue', $colors_array)) {
				$color_identity = '29 Ink-Treader';
				$color_symbols  = $black_mana_symbol . $red_mana_symbol . $green_mana_symbol . $white_mana_symbol;
			} elseif (!in_array('White', $colors_array)) {
				$color_identity = '30 Witch-Maw';
				$color_symbols  = $blue_mana_symbol . $black_mana_symbol . $red_mana_symbol . $green_mana_symbol;
			}
		} elseif (count($colors_array) === 5) {
			$color_identity = '31 Five-Color';
			$color_symbols  = $white_mana_symbol . $blue_mana_symbol . $black_mana_symbol . $red_mana_symbol . $green_mana_symbol;
		}

		return ['color_identity' => $color_identity, 'color_symbols' => $color_symbols];
	}
}
