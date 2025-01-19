<?php

namespace ArchidektImporter\Includes\ImportDeckData;

/**
 * Class ImportArchidektData
 * This class handles the functionality for importing deck data from Archidekt
 */
class ImportArchidektData
{

	/**
	 * Fetch deck data from Archidekt API
	 */
	public static function import_deck_data($deck_id)
	{
		$api_url = "https://archidekt.com/api/decks/" . $deck_id . "/";

		$api_args = array(
			'headers' => array(
				'Accept' => 'application/json'
			)
		);

		$response = wp_remote_get($api_url, $api_args);

		if (is_wp_error($response)) {
			return false;
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		return $data;
	}
}
