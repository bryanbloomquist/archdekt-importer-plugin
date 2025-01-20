<?php

namespace ArchidektImporter\Includes\DecksPostType;

use DateTime;

/**
 * Class DecksPostPage
 * This class displays the Decks post type page
 */
class DecksPostPage
{

	/**
	 * Add Meta Box to Decks page sidebar to display archidekt deck id
	 */
	public static function add_deck_info_sidebar_meta_box(): void
	{
		add_meta_box(
			'deck-id-meta-box',
			'Archidekt Information',
			[self::class, 'display_deck_info_sidebar_meta_box'],
			'deck',
			'side',
			'default',
		);
	}

	/**
	 * Display Deck Information Meta Box
	 */
	public static function display_deck_info_sidebar_meta_box($post): void
	{
		$deck_id = get_post_meta($post->ID, 'deck_id', true);
		$deck_data = get_post_meta($post->ID, 'deck_data', true);
		$deck_url = get_post_meta($post->ID, 'deck_url', true);
		$deck_name = $deck_data['name'];
		$last_updated = new DateTime($deck_data['updatedAt']);
		$last_updated = $last_updated->format('F j, Y, g:i a');

		echo '<p><strong>Deck ID:</strong> ' . $deck_id . '</p>';
		echo '<p><strong>Updated:</strong> ' . $last_updated . '</p>';
		echo '<p><strong>View Deck :</strong> <a href="' . $deck_url . '" target="_blank">' . $deck_name . ' </a></p>';
	}
}

add_action('add_meta_boxes', [DecksPostPage::class, 'add_deck_info_sidebar_meta_box']);
