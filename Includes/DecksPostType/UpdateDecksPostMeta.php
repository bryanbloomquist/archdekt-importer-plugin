<?php

namespace ArchidektImporter\Includes\DecksPostType;

use ArchidektImporter\Includes\ImportDeckData\ProcessImportedData as ProcessImportedData;

/**
 * Class UpdateDeckPostMeta
 * This class creates and updates post meta for deck post type
 */
class UpdateDecksPostMeta
{

  /**
   * Create a new deck post
   */
  public static function create_new_deck_post($deck_id, $deck_data)
  {
    $deck_post_id = wp_insert_post([
      'post_title'  => $deck_data['name'],
      'post_type'   => 'deck',
      'post_status' => 'publish'
    ]);
    self::update_deck_post_meta($deck_id, $deck_data, $deck_post_id);
  }

  /**
   * Update deck post meta
   */
  public static function update_deck_post_meta($deck_id, $deck_data, $deck_post_id)
  {
    $sorted_deck     = ProcessImportedData::sort_cards_by_type($deck_data);
    $deck_name       = $deck_data['name'];
    $deck_url        = "https://archidekt.com/decks/{$deck_id}";
    $commander       = $sorted_deck['Commander'][0]['card']['oracleCard']['name'];
    $partner         = $sorted_deck['Commander'][1]['card']['oracleCard']['name'];
    $identity        = ProcessImportedData::get_color_identity($sorted_deck['Commander']);
    $misc_values     = ProcessImportedData::calculate_misc_values($deck_data);
    $salt_sum        = $misc_values['salt_sum'];
    $deck_price      = "$" . number_format($misc_values['price'], 2);
    $total_mana      = $misc_values['mana_value'];
    $battles         = ProcessImportedData::count_card_type($sorted_deck['Battle']);
    $planeswalkers   = ProcessImportedData::count_card_type($sorted_deck['Planeswalker']);
    $creatures       = ProcessImportedData::count_card_type($sorted_deck['Creature']);
    $sorceries       = ProcessImportedData::count_card_type($sorted_deck['Sorcery']);
    $instants        = ProcessImportedData::count_card_type($sorted_deck['Instant']);
    $artifacts       = ProcessImportedData::count_card_type($sorted_deck['Artifact']);
    $enchantments    = ProcessImportedData::count_card_type($sorted_deck['Enchantment']);
    $lands           = ProcessImportedData::count_card_type($sorted_deck['Land']);
    $non_lands       = ($partner ? 98 : 99) - $lands;
    $average_mana    = number_format(($total_mana / ($non_lands)), 2);

    self::fetch_featured_image($sorted_deck['Commander'][0], $deck_post_id);

    wp_update_post(['ID' => $deck_post_id, 'post_title' => $deck_name]);
    update_post_meta($deck_post_id, 'deck_id', $deck_id);
    update_post_meta($deck_post_id, 'deck_name', $deck_data['name']);
    update_post_meta($deck_post_id, 'deck_data', $deck_data);
    update_post_meta($deck_post_id, 'deck_url', $deck_url);
    update_post_meta($deck_post_id, 'commander', $commander);
    update_post_meta($deck_post_id, 'partner', $partner);
    update_post_meta($deck_post_id, 'identity', $identity);
    update_post_meta($deck_post_id, 'salt_sum', $salt_sum);
    update_post_meta($deck_post_id, 'deck_price', $deck_price);
    update_post_meta($deck_post_id, 'total_mana', $total_mana);
    update_post_meta($deck_post_id, 'average_mana', $average_mana);
    update_post_meta($deck_post_id, 'battles', $battles);
    update_post_meta($deck_post_id, 'planeswalkers', $planeswalkers);
    update_post_meta($deck_post_id, 'creatures', $creatures);
    update_post_meta($deck_post_id, 'sorceries', $sorceries);
    update_post_meta($deck_post_id, 'instants', $instants);
    update_post_meta($deck_post_id, 'artifacts', $artifacts);
    update_post_meta($deck_post_id, 'enchantments', $enchantments);
    update_post_meta($deck_post_id, 'lands', $lands);
  }

  /**
   * Fetch Commander Card Image and import to media library, then set as featured image
   */
  public static function fetch_featured_image($commander, $deck_post_id)
  {
    // Check if featured image for deck post is already set
    $featured_image = get_the_post_thumbnail_url($deck_post_id, 'full');
    // Get the scryfall unique identifier for the commander card
    $commander_uid  = $commander['card']['uid'];

    // If there is a featured image, check to see if matches the commander uid
    if ($featured_image) {
      $featured_image_name = basename($featured_image, '.jpg');
      if ($featured_image_name == $commander_uid) {
        // If it matches, there is not need to update the image
        return;
      }
    }

    // Fetch the commander card image data from Scryfall's API and decode the JSON response
    $scryfall_url   = "https://api.scryfall.com/cards/" . $commander_uid;
    $response       = wp_remote_get($scryfall_url);
    if (is_wp_error($response)) {
      return false;
    }
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Get the image URL from the data
    if ($data['card_faces']) {
      $image_url = $data['card_faces'][0]['image_uris']['normal'];
    } else {
      $image_url = $data['image_uris']['normal'];
    }

    // Define the image name using the commander uid
    $image_name = $commander_uid . '.jpg';

    // Get the WordPress upload directory
    $upload_dir       = wp_upload_dir();
    // Fetch the image data from the URL
    $image_data       = file_get_contents($image_url);
    // Generate a unique filename for the image
    $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name);
    $filename         = basename($unique_file_name);

    // Determine the file path to save the image
    if (wp_mkdir_p($upload_dir['path'])) {
      $file = $upload_dir['path'] . '/' . $filename;
    } else {
      $file = $upload_dir['basedir'] . '/' . $filename;
    }
    // Save the image data to the file
    file_put_contents($file, $image_data);

    // Get the file type of the image
    $wp_filetype = wp_check_filetype($filename, null);

    // Prepare an array of attachment data to insert into the media library
    $attachment = array(
      'post_mime_type' => $wp_filetype['type'],
      'post_title'     => sanitize_file_name($filename),
      'post_content'   => '',
      'post_status'    => 'inherit'
    );

    // Insert the image into the media library
    $attach_id = wp_insert_attachment($attachment, $file, $deck_post_id);

    // Include the image.php file for generating attachment metadata
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Generate the attachment metadata
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);

    // Update the attachment metadata in the database
    wp_update_attachment_metadata($attach_id, $attach_data);

    // Set the uploaded image as the featured image for the deck
    set_post_thumbnail($deck_post_id, $attach_id);
  }
}
